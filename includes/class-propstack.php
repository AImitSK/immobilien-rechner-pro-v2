<?php
/**
 * Propstack CRM Integration
 *
 * Handles all communication with the Propstack API
 */

if (!defined('ABSPATH')) {
    exit;
}

class IRP_Propstack {

    /**
     * API Base URL
     */
    private const API_BASE_URL = 'https://api.propstack.de/v1';

    /**
     * Option name for settings
     */
    private const OPTION_NAME = 'irp_propstack_settings';

    /**
     * Get settings
     *
     * @return array
     */
    public static function get_settings(): array {
        return get_option(self::OPTION_NAME, [
            'enabled' => false,
            'api_key' => '',
            'default_broker_id' => null,
            'city_broker_mapping' => [],
            'contact_source_id' => null,
            'newsletter_enabled' => false,
            'newsletter_snippet_id' => null,
            'newsletter_broker_id' => null,
            'activity_enabled' => false,
            'activity_type_id' => null,
            'activity_create_task' => false,
            'activity_task_due_days' => 1,
        ]);
    }

    /**
     * Save settings
     *
     * @param array $settings Settings to save
     * @return bool
     */
    public static function save_settings(array $settings): bool {
        return update_option(self::OPTION_NAME, $settings);
    }

    /**
     * Check if integration is enabled
     *
     * @return bool
     */
    public static function is_enabled(): bool {
        $settings = self::get_settings();
        return !empty($settings['enabled']) && !empty($settings['api_key']);
    }

    /**
     * Get API key
     *
     * @return string
     */
    private static function get_api_key(): string {
        $settings = self::get_settings();
        return $settings['api_key'] ?? '';
    }

    /**
     * Make API request
     *
     * @param string $endpoint API endpoint (e.g., '/brokers')
     * @param string $method HTTP method
     * @param array|null $data Request data for POST/PUT
     * @param string|null $override_api_key Optional API key to use instead of saved one
     * @return array|WP_Error Response data or error
     */
    private static function api_request(string $endpoint, string $method = 'GET', ?array $data = null, ?string $override_api_key = null) {
        $api_key = $override_api_key ?? self::get_api_key();

        if (empty($api_key)) {
            return new \WP_Error('no_api_key', __('Kein API-Key konfiguriert.', 'immobilien-rechner-pro'));
        }

        $url = self::API_BASE_URL . $endpoint;

        $args = [
            'method' => $method,
            'timeout' => 30,
            'headers' => [
                'X-API-KEY' => $api_key,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ];

        if ($data !== null && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $args['body'] = wp_json_encode($data);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            error_log('[IRP Propstack] API Error: ' . $response->get_error_message());
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        if ($status_code >= 400) {
            // Propstack returns errors as array: {"errors": ["Not Authenticated"]}
            if (!empty($decoded['errors']) && is_array($decoded['errors'])) {
                $error_message = implode(', ', $decoded['errors']);
            } else {
                $error_message = $decoded['message'] ?? $decoded['error'] ?? __('Unbekannter API-Fehler', 'immobilien-rechner-pro');
            }
            error_log('[IRP Propstack] API Error ' . $status_code . ': ' . $error_message . ' | Response: ' . $body);
            return new \WP_Error('api_error', $error_message, ['status' => $status_code]);
        }

        return $decoded;
    }

    /**
     * Test API connection
     *
     * @param string|null $api_key Optional API key to test (uses saved key if not provided)
     * @return array ['success' => bool, 'message' => string]
     */
    public static function test_connection(?string $api_key = null): array {
        $result = self::api_request('/brokers', 'GET', null, $api_key);

        if (is_wp_error($result)) {
            return [
                'success' => false,
                'message' => $result->get_error_message(),
            ];
        }

        return [
            'success' => true,
            'message' => __('Verbindung erfolgreich!', 'immobilien-rechner-pro'),
        ];
    }

    /**
     * Get all brokers from Propstack
     *
     * @return array|WP_Error List of brokers or error
     */
    public static function get_brokers() {
        if (empty(self::get_api_key())) {
            return [];
        }

        $result = self::api_request('/brokers');

        if (is_wp_error($result)) {
            return $result;
        }

        // Normalize the response
        $brokers = [];
        $data = $result['data'] ?? $result;

        if (is_array($data)) {
            foreach ($data as $broker) {
                $brokers[] = [
                    'id' => $broker['id'] ?? 0,
                    'name' => trim(($broker['first_name'] ?? '') . ' ' . ($broker['last_name'] ?? '')),
                    'email' => $broker['email'] ?? '',
                ];
            }
        }

        return $brokers;
    }

    /**
     * Get contact sources from Propstack
     *
     * @return array|WP_Error List of contact sources or error
     */
    public static function get_contact_sources() {
        if (empty(self::get_api_key())) {
            return [];
        }

        $result = self::api_request('/contact_sources');

        if (is_wp_error($result)) {
            return $result;
        }

        $sources = [];
        $data = $result['data'] ?? $result;

        if (is_array($data)) {
            foreach ($data as $source) {
                $sources[] = [
                    'id' => $source['id'] ?? 0,
                    'name' => $source['name'] ?? '',
                ];
            }
        }

        return $sources;
    }

    /**
     * Get activity types from Propstack
     *
     * @return array|WP_Error List of activity types or error
     */
    public static function get_activity_types() {
        if (empty(self::get_api_key())) {
            return [];
        }

        $result = self::api_request('/activity_types');

        if (is_wp_error($result)) {
            return $result;
        }

        $types = [];
        $data = $result['data'] ?? $result;

        if (is_array($data)) {
            foreach ($data as $type) {
                $types[] = [
                    'id' => $type['id'] ?? 0,
                    'name' => $type['name'] ?? '',
                ];
            }
        }

        return $types;
    }

    /**
     * Create activity/task in Propstack
     *
     * @param int $contact_id Propstack contact ID
     * @param array $lead_data Lead data
     * @param int|null $broker_id Broker ID (optional)
     * @return int|WP_Error Activity ID or error
     */
    public static function create_activity(int $contact_id, array $lead_data, ?int $broker_id = null) {
        $settings = self::get_settings();

        if (empty($settings['activity_enabled'])) {
            return new \WP_Error('activity_disabled', __('Aktivitäten-Erstellung ist nicht aktiviert.', 'immobilien-rechner-pro'));
        }

        if (empty($settings['activity_type_id'])) {
            return new \WP_Error('no_activity_type', __('Kein Aktivitätstyp konfiguriert.', 'immobilien-rechner-pro'));
        }

        // Build activity title
        $mode = $lead_data['mode'] ?? 'rental';
        $mode_label = $mode === 'rental' ? 'Mietwertberechnung' : 'Verkaufen vs. Vermieten';
        $title = 'Anfrage über Immobilien-Rechner Pro - ' . $mode_label;

        // Build activity body (HTML)
        $body = self::build_activity_body($lead_data);

        // Build task data
        $task_data = [
            'task' => [
                'title' => $title,
                'note_type_id' => (int) $settings['activity_type_id'],
                'client_ids' => [$contact_id],
                'body' => $body,
            ],
        ];

        // Add broker if provided
        if ($broker_id) {
            $task_data['task']['broker_id'] = $broker_id;
        }

        // Create as reminder/task if enabled
        if (!empty($settings['activity_create_task'])) {
            $task_data['task']['is_reminder'] = true;
            $task_data['task']['done'] = false;

            // Calculate due date
            $due_days = (int) ($settings['activity_task_due_days'] ?? 1);
            $due_date = new \DateTime('now', new \DateTimeZone('Europe/Berlin'));
            $due_date->modify('+' . $due_days . ' weekday');
            $due_date->setTime(9, 0, 0);
            $task_data['task']['due_date'] = $due_date->format('c');
        }

        // Make API request
        $result = self::api_request('/tasks', 'POST', $task_data);

        if (is_wp_error($result)) {
            return $result;
        }

        return $result['id'] ?? 0;
    }

    /**
     * Build HTML body for activity
     *
     * @param array $lead_data Lead data
     * @return string HTML body
     */
    private static function build_activity_body(array $lead_data): string {
        $calc = $lead_data['calculation_data'] ?? [];
        $result = $calc['result'] ?? [];

        $html = '<div style="font-family: Arial, sans-serif;">';
        $html .= '<h3 style="color: #333; margin-bottom: 15px;">Anfrage über Immobilien-Rechner Pro</h3>';

        // Contact info
        $html .= '<p><strong>Kontakt:</strong> ' . esc_html($lead_data['name'] ?? '') . '</p>';
        if (!empty($lead_data['email'])) {
            $html .= '<p><strong>E-Mail:</strong> ' . esc_html($lead_data['email']) . '</p>';
        }
        if (!empty($lead_data['phone'])) {
            $html .= '<p><strong>Telefon:</strong> ' . esc_html($lead_data['phone']) . '</p>';
        }

        $html .= '<hr style="border: none; border-top: 1px solid #ddd; margin: 15px 0;">';
        $html .= '<h4 style="color: #555;">Berechnungsergebnis</h4>';

        // Mode
        $mode = $lead_data['mode'] ?? 'rental';
        $mode_labels = [
            'rental' => 'Mietwertberechnung',
            'comparison' => 'Verkaufen vs. Vermieten',
            'sale_value' => 'Verkaufswertberechnung',
        ];
        $html .= '<p><strong>Modus:</strong> ' . ($mode_labels[$mode] ?? $mode) . '</p>';

        // Property type
        $types = ['apartment' => 'Wohnung', 'house' => 'Haus', 'commercial' => 'Gewerbe', 'land' => 'Grundstück'];
        $property_type = $calc['property_type'] ?? '';
        if ($property_type) {
            $html .= '<p><strong>Objekttyp:</strong> ' . ($types[$property_type] ?? $property_type) . '</p>';
        }

        // Sale value specific fields
        if ($mode === 'sale_value') {
            // House type
            $house_types = [
                'single_family' => 'Einfamilienhaus',
                'multi_family' => 'Mehrfamilienhaus',
                'semi_detached' => 'Doppelhaushälfte',
                'townhouse_middle' => 'Mittelreihenhaus',
                'townhouse_end' => 'Endreihenhaus',
                'bungalow' => 'Bungalow',
            ];
            $house_type = $calc['house_type'] ?? '';
            if ($house_type) {
                $html .= '<p><strong>Haustyp:</strong> ' . ($house_types[$house_type] ?? $house_type) . '</p>';
            }

            // Land size
            $land_size = $calc['land_size'] ?? '';
            if ($land_size) {
                $html .= '<p><strong>Grundstück:</strong> ' . esc_html($land_size) . ' m²</p>';
            }

            // Living space
            $living_space = $calc['living_space'] ?? $calc['size'] ?? '';
            if ($living_space && $property_type !== 'land') {
                $html .= '<p><strong>Wohnfläche:</strong> ' . esc_html($living_space) . ' m²</p>';
            }

            // Build year
            $build_year = $calc['build_year'] ?? '';
            if ($build_year) {
                $html .= '<p><strong>Baujahr:</strong> ' . esc_html($build_year) . '</p>';
            }

            // Quality
            $qualities = ['simple' => 'Einfach', 'normal' => 'Normal', 'upscale' => 'Gehoben', 'luxury' => 'Luxuriös'];
            $quality = $calc['quality'] ?? '';
            if ($quality) {
                $html .= '<p><strong>Qualität:</strong> ' . ($qualities[$quality] ?? $quality) . '</p>';
            }

            // Address
            $street = $calc['street_address'] ?? '';
            if ($street) {
                $html .= '<p><strong>Adresse:</strong> ' . esc_html($street) . '</p>';
            }

            // City
            $city = $calc['property_location'] ?? $calc['city_name'] ?? $lead_data['property_location'] ?? '';
            $zip = $calc['zip_code'] ?? '';
            if ($city || $zip) {
                $html .= '<p><strong>Standort:</strong> ' . esc_html(trim($zip . ' ' . $city)) . '</p>';
            }

            // Sale intention
            $intentions = ['sell' => 'Verkaufen', 'buy' => 'Kaufen'];
            $intention = $calc['sale_intention'] ?? '';
            if ($intention) {
                $html .= '<p><strong>Absicht:</strong> ' . ($intentions[$intention] ?? $intention) . '</p>';
            }

            // Timeframe
            $timeframes = [
                'immediately' => 'Sofort',
                '3_months' => 'In 3 Monaten',
                '6_months' => 'In 6 Monaten',
                '12_months' => 'In 12 Monaten',
                'undecided' => 'Noch offen',
            ];
            $timeframe = $calc['timeframe'] ?? '';
            if ($timeframe) {
                $html .= '<p><strong>Zeitrahmen:</strong> ' . ($timeframes[$timeframe] ?? $timeframe) . '</p>';
            }

            // Results - Sale value
            $html .= '<hr style="border: none; border-top: 1px solid #ddd; margin: 15px 0;">';
            $html .= '<h4 style="color: #555;">Ergebnis</h4>';

            if (isset($result['price_estimate'])) {
                $html .= '<p style="font-size: 18px; color: #2563eb;"><strong>Geschätzter Verkaufswert:</strong> ' . number_format($result['price_estimate'], 0, ',', '.') . ' €</p>';
            }
            if (isset($result['price_min']) && isset($result['price_max'])) {
                $html .= '<p><strong>Preisspanne:</strong> ' . number_format($result['price_min'], 0, ',', '.') . ' - ' . number_format($result['price_max'], 0, ',', '.') . ' €</p>';
            }
        } else {
            // Rental/comparison mode - original logic
            $size = $calc['size'] ?? $lead_data['property_size'] ?? '';
            if ($size) {
                $html .= '<p><strong>Größe:</strong> ' . esc_html($size) . ' m²</p>';
            }

            $city = $calc['city_name'] ?? $lead_data['property_location'] ?? '';
            if ($city) {
                $html .= '<p><strong>Stadt:</strong> ' . esc_html($city) . '</p>';
            }

            $conditions = [
                'new' => 'Neubau',
                'renovated' => 'Renoviert',
                'good' => 'Gut',
                'needs_renovation' => 'Renovierungsbedürftig',
            ];
            $condition = $calc['condition'] ?? '';
            if ($condition) {
                $html .= '<p><strong>Zustand:</strong> ' . ($conditions[$condition] ?? $condition) . '</p>';
            }

            // Results
            $html .= '<hr style="border: none; border-top: 1px solid #ddd; margin: 15px 0;">';
            $html .= '<h4 style="color: #555;">Ergebnis</h4>';

            if (isset($result['monthly_rent'])) {
                $rent = $result['monthly_rent']['estimate'] ?? $result['monthly_rent'];
                if (is_numeric($rent)) {
                    $html .= '<p><strong>Geschätzte Miete:</strong> ' . number_format($rent, 0, ',', '.') . ' €/Monat</p>';
                }
            }

            if (isset($result['price_per_sqm'])) {
                $html .= '<p><strong>Preis pro m²:</strong> ' . number_format($result['price_per_sqm'], 2, ',', '.') . ' €</p>';
            }
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Create or update contact in Propstack
     *
     * @param array $lead_data Lead data from WordPress
     * @return int|WP_Error Propstack contact ID or error
     */
    public static function create_contact(array $lead_data) {
        $settings = self::get_settings();
        $email = $lead_data['email'] ?? '';

        // Check if contact already exists
        $existing_contact = self::find_contact_by_email($email);

        if ($existing_contact) {
            // Update existing contact - append new inquiry to description
            return self::update_contact_description($existing_contact, $lead_data);
        }

        // Determine broker ID based on city mapping
        $broker_id = self::get_broker_for_city($lead_data['city_id'] ?? '');

        // Build contact data
        // Note: Propstack uses 'home_cell' for mobile numbers, not 'phone'
        $contact_data = [
            'client' => [
                'email' => $email,
                'home_cell' => $lead_data['phone'] ?? '',
                'broker_id' => $broker_id,
                'description' => self::build_description($lead_data),
            ],
        ];

        // Parse name into first/last name
        $name_parts = self::parse_name($lead_data['name'] ?? '');
        $contact_data['client']['first_name'] = $name_parts['first_name'];
        $contact_data['client']['last_name'] = $name_parts['last_name'];

        // Add contact source if configured
        if (!empty($settings['contact_source_id'])) {
            $contact_data['client']['client_source_id'] = (int) $settings['contact_source_id'];
        }

        // Make API request
        $result = self::api_request('/contacts', 'POST', $contact_data);

        if (is_wp_error($result)) {
            return $result;
        }

        // Return the contact ID
        return $result['id'] ?? $result['data']['id'] ?? 0;
    }

    /**
     * Find contact by email
     *
     * @param string $email Email address
     * @return array|null Contact data or null if not found
     */
    private static function find_contact_by_email(string $email): ?array {
        if (empty($email)) {
            return null;
        }

        $result = self::api_request('/contacts?email=' . urlencode($email));

        if (is_wp_error($result)) {
            return null;
        }

        // API returns array of contacts (but without full data like description)
        if (is_array($result) && !empty($result[0]['id'])) {
            // Fetch full contact data including description
            $contact_id = $result[0]['id'];
            $full_contact = self::api_request('/contacts/' . $contact_id);

            if (!is_wp_error($full_contact)) {
                return $full_contact;
            }

            // Fallback to basic data if full fetch fails
            return $result[0];
        }

        return null;
    }

    /**
     * Update contact description with new inquiry
     *
     * @param array $existing_contact Existing contact data
     * @param array $lead_data New lead data
     * @return int|WP_Error Propstack contact ID or error
     */
    private static function update_contact_description(array $existing_contact, array $lead_data) {
        $contact_id = $existing_contact['id'];
        $old_description = $existing_contact['description'] ?? '';
        $new_inquiry = self::build_description($lead_data);

        // Append new inquiry to existing description
        $updated_description = $old_description;
        if (!empty($old_description)) {
            $updated_description .= "\n\n" . str_repeat('-', 40) . "\n\n";
        }
        $updated_description .= $new_inquiry;

        // Build update data - always update description and phone
        $update_data = [
            'client' => [
                'description' => $updated_description,
            ],
        ];

        // Update phone number if provided (in case it was missing before)
        if (!empty($lead_data['phone'])) {
            $update_data['client']['home_cell'] = $lead_data['phone'];
        }

        // Update contact
        $result = self::api_request('/contacts/' . $contact_id, 'PUT', $update_data);

        if (is_wp_error($result)) {
            return $result;
        }

        return $contact_id;
    }

    /**
     * Get broker ID for a city
     *
     * @param string $city_id City ID
     * @return int|null Broker ID or null
     */
    public static function get_broker_for_city(string $city_id): ?int {
        $settings = self::get_settings();
        $mapping = $settings['city_broker_mapping'] ?? [];

        // Check if city has assigned broker
        if (!empty($mapping[$city_id])) {
            return (int) $mapping[$city_id];
        }

        // Fallback to default broker
        if (!empty($settings['default_broker_id'])) {
            return (int) $settings['default_broker_id'];
        }

        return null;
    }

    /**
     * Parse full name into first and last name
     *
     * @param string $full_name Full name
     * @return array ['first_name' => string, 'last_name' => string]
     */
    private static function parse_name(string $full_name): array {
        $parts = explode(' ', trim($full_name), 2);

        return [
            'first_name' => $parts[0] ?? '',
            'last_name' => $parts[1] ?? '',
        ];
    }

    /**
     * Build description for Propstack contact
     *
     * @param array $lead_data Lead data
     * @return string
     */
    private static function build_description(array $lead_data): string {
        $calc = $lead_data['calculation_data'] ?? [];
        $result = $calc['result'] ?? [];

        $lines = [
            'Anfrage über Immobilien-Rechner Pro',
            '',
            '--- Berechnungsergebnis ---',
        ];

        // Mode
        $mode = $lead_data['mode'] ?? 'rental';
        $mode_labels = [
            'rental' => 'Mietwertberechnung',
            'comparison' => 'Verkaufen vs. Vermieten',
            'sale_value' => 'Verkaufswertberechnung',
        ];
        $lines[] = 'Modus: ' . ($mode_labels[$mode] ?? $mode);

        // Property type
        $types = ['apartment' => 'Wohnung', 'house' => 'Haus', 'commercial' => 'Gewerbe', 'land' => 'Grundstück'];
        $property_type = $calc['property_type'] ?? '';
        $lines[] = 'Objekttyp: ' . ($types[$property_type] ?? $property_type);

        // Sale value specific fields
        if ($mode === 'sale_value') {
            // House type
            $house_types = [
                'single_family' => 'Einfamilienhaus',
                'multi_family' => 'Mehrfamilienhaus',
                'semi_detached' => 'Doppelhaushälfte',
                'townhouse_middle' => 'Mittelreihenhaus',
                'townhouse_end' => 'Endreihenhaus',
                'bungalow' => 'Bungalow',
            ];
            $house_type = $calc['house_type'] ?? '';
            if ($house_type) {
                $lines[] = 'Haustyp: ' . ($house_types[$house_type] ?? $house_type);
            }

            // Land size
            $land_size = $calc['land_size'] ?? '';
            if ($land_size) {
                $lines[] = 'Grundstück: ' . $land_size . ' m²';
            }

            // Living space
            $living_space = $calc['living_space'] ?? $calc['size'] ?? '';
            if ($living_space && $property_type !== 'land') {
                $lines[] = 'Wohnfläche: ' . $living_space . ' m²';
            }

            // Build year
            $build_year = $calc['build_year'] ?? '';
            if ($build_year) {
                $lines[] = 'Baujahr: ' . $build_year;
            }

            // Quality
            $qualities = ['simple' => 'Einfach', 'normal' => 'Normal', 'upscale' => 'Gehoben', 'luxury' => 'Luxuriös'];
            $quality = $calc['quality'] ?? '';
            if ($quality) {
                $lines[] = 'Qualität: ' . ($qualities[$quality] ?? $quality);
            }

            // Address
            $street = $calc['street_address'] ?? '';
            if ($street) {
                $lines[] = 'Adresse: ' . $street;
            }

            // City
            $city = $calc['property_location'] ?? $calc['city_name'] ?? $lead_data['property_location'] ?? '';
            $zip = $calc['zip_code'] ?? '';
            if ($city || $zip) {
                $lines[] = 'Standort: ' . trim($zip . ' ' . $city);
            }

            // Sale intention
            $intentions = ['sell' => 'Verkaufen', 'buy' => 'Kaufen'];
            $intention = $calc['sale_intention'] ?? '';
            if ($intention) {
                $lines[] = 'Absicht: ' . ($intentions[$intention] ?? $intention);
            }

            // Timeframe
            $timeframes = [
                'immediately' => 'Sofort',
                '3_months' => 'In 3 Monaten',
                '6_months' => 'In 6 Monaten',
                '12_months' => 'In 12 Monaten',
                'undecided' => 'Noch offen',
            ];
            $timeframe = $calc['timeframe'] ?? '';
            if ($timeframe) {
                $lines[] = 'Zeitrahmen: ' . ($timeframes[$timeframe] ?? $timeframe);
            }

            // Result - Sale value
            $lines[] = '';
            if (isset($result['price_estimate'])) {
                $lines[] = 'Geschätzter Verkaufswert: ' . number_format($result['price_estimate'], 0, ',', '.') . ' €';
            }
            if (isset($result['price_min']) && isset($result['price_max'])) {
                $lines[] = 'Preisspanne: ' . number_format($result['price_min'], 0, ',', '.') . ' - ' . number_format($result['price_max'], 0, ',', '.') . ' €';
            }
        } else {
            // Rental/comparison mode - original logic
            $size = $calc['size'] ?? $lead_data['property_size'] ?? '';
            if ($size) {
                $lines[] = 'Größe: ' . $size . ' m²';
            }

            $city = $calc['city_name'] ?? $lead_data['property_location'] ?? '';
            if ($city) {
                $lines[] = 'Stadt: ' . $city;
            }

            $conditions = [
                'new' => 'Neubau',
                'renovated' => 'Renoviert',
                'good' => 'Gut',
                'needs_renovation' => 'Renovierungsbedürftig',
            ];
            $condition = $calc['condition'] ?? '';
            if ($condition) {
                $lines[] = 'Zustand: ' . ($conditions[$condition] ?? $condition);
            }

            // Result
            $lines[] = '';
            if (isset($result['monthly_rent'])) {
                $rent = $result['monthly_rent']['estimate'] ?? $result['monthly_rent'];
                if (is_numeric($rent)) {
                    $lines[] = 'Geschätzte Miete: ' . number_format($rent, 0, ',', '.') . ' €/Monat';
                }
            }

            if (isset($result['price_per_sqm'])) {
                $lines[] = 'Preis pro m²: ' . number_format($result['price_per_sqm'], 2, ',', '.') . ' €';
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Send newsletter double opt-in email via Propstack
     *
     * @param int $contact_id Propstack contact ID
     * @param string $email Email address
     * @return bool|WP_Error True on success, error otherwise
     */
    public static function send_newsletter_doi(int $contact_id, string $email) {
        $settings = self::get_settings();

        if (empty($settings['newsletter_enabled'])) {
            return new \WP_Error('newsletter_disabled', __('Newsletter-Integration ist nicht aktiviert.', 'immobilien-rechner-pro'));
        }

        if (empty($settings['newsletter_snippet_id'])) {
            return new \WP_Error('no_snippet', __('Kein Textbaustein für Newsletter-DOI konfiguriert.', 'immobilien-rechner-pro'));
        }

        $broker_id = $settings['newsletter_broker_id'] ?? $settings['default_broker_id'];

        if (empty($broker_id)) {
            return new \WP_Error('no_broker', __('Kein Absender für Newsletter-DOI konfiguriert.', 'immobilien-rechner-pro'));
        }

        $message_data = [
            'message' => [
                'broker_id' => (int) $broker_id,
                'contact_id' => $contact_id,
                'snippet_id' => (int) $settings['newsletter_snippet_id'],
                'to' => $email,
            ],
        ];

        $result = self::api_request('/messages', 'POST', $message_data);

        if (is_wp_error($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Sync a lead to Propstack
     *
     * @param int $lead_id WordPress lead ID
     * @param object|array $lead Lead data (optional, will be fetched if not provided)
     * @return bool|WP_Error True on success, error otherwise
     */
    public static function sync_lead(int $lead_id, $lead = null) {
        if (!self::is_enabled()) {
            return new \WP_Error('not_enabled', __('Propstack-Integration ist nicht aktiviert.', 'immobilien-rechner-pro'));
        }

        // Fetch lead if not provided
        if ($lead === null) {
            $leads = new IRP_Leads();
            $lead = $leads->get($lead_id);
        }

        if (!$lead) {
            return new \WP_Error('lead_not_found', __('Lead nicht gefunden.', 'immobilien-rechner-pro'));
        }

        // Skip if already synced
        if (!empty($lead->propstack_id)) {
            return true;
        }

        // Skip partial leads (no email yet)
        if (empty($lead->email)) {
            return new \WP_Error('incomplete_lead', __('Lead hat noch keine E-Mail-Adresse.', 'immobilien-rechner-pro'));
        }

        // Prepare lead data
        $calculation_data = $lead->calculation_data;
        if (is_string($calculation_data)) {
            $calculation_data = json_decode($calculation_data, true);
        }

        $lead_data = [
            'name' => $lead->name ?? '',
            'email' => $lead->email,
            'phone' => $lead->phone ?? '',
            'mode' => $lead->mode ?? 'rental',
            'city_id' => $calculation_data['city_id'] ?? '',
            'property_size' => $lead->property_size ?? $calculation_data['size'] ?? '',
            'property_location' => $lead->property_location ?? $calculation_data['city_name'] ?? '',
            'calculation_data' => $calculation_data,
        ];

        // Create contact in Propstack
        $propstack_id = self::create_contact($lead_data);

        global $wpdb;
        $table = $wpdb->prefix . 'irp_leads';

        if (is_wp_error($propstack_id)) {
            // Save error
            $wpdb->update(
                $table,
                [
                    'propstack_synced' => 0,
                    'propstack_error' => $propstack_id->get_error_message(),
                    'propstack_synced_at' => current_time('mysql'),
                ],
                ['id' => $lead_id],
                ['%d', '%s', '%s'],
                ['%d']
            );

            error_log('[IRP Propstack] Sync failed for lead ' . $lead_id . ': ' . $propstack_id->get_error_message());
            return $propstack_id;
        }

        // Validate propstack_id
        $propstack_id_int = (int) $propstack_id;
        if ($propstack_id_int <= 0) {
            return new \WP_Error('invalid_id', __('Ungültige Propstack-ID erhalten.', 'immobilien-rechner-pro'));
        }

        // Save success
        $synced_at = current_time('mysql');

        $wpdb->update(
            $table,
            [
                'propstack_id' => $propstack_id_int,
                'propstack_synced' => 1,
                'propstack_error' => '',
                'propstack_synced_at' => $synced_at,
            ],
            ['id' => $lead_id],
            ['%d', '%d', '%s', '%s'],
            ['%d']
        );

        // Create activity in Propstack if enabled
        $settings = self::get_settings();
        if (!empty($settings['activity_enabled'])) {
            $broker_id = self::get_broker_for_city($lead_data['city_id'] ?? '');
            $activity_result = self::create_activity($propstack_id_int, $lead_data, $broker_id);
            if (is_wp_error($activity_result)) {
                error_log('[IRP Propstack] Activity creation failed: ' . $activity_result->get_error_message());
            }
        }

        // Send newsletter DOI if consent given
        if (!empty($settings['newsletter_enabled']) && !empty($lead->newsletter_consent)) {
            $doi_result = self::send_newsletter_doi($propstack_id, $lead->email);
            if (is_wp_error($doi_result)) {
                error_log('[IRP Propstack] Newsletter DOI failed: ' . $doi_result->get_error_message());
            }
        }

        return true;
    }

    /**
     * Retry sync for a failed lead
     *
     * @param int $lead_id Lead ID
     * @return bool|WP_Error
     */
    public static function retry_sync(int $lead_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'irp_leads';

        // Reset sync status
        $wpdb->update(
            $table,
            [
                'propstack_synced' => 0,
                'propstack_error' => null,
            ],
            ['id' => $lead_id],
            ['%d', '%s'],
            ['%d']
        );

        return self::sync_lead($lead_id);
    }

    /**
     * Get sync status for display
     *
     * @param object $lead Lead object
     * @return array ['status' => string, 'label' => string, 'class' => string]
     */
    public static function get_sync_status($lead): array {
        if (!self::is_enabled()) {
            return [
                'status' => 'disabled',
                'label' => __('Nicht aktiv', 'immobilien-rechner-pro'),
                'class' => 'irp-status-disabled',
                'icon' => '⚫',
            ];
        }

        // Always fetch fresh data from DB to avoid cache issues
        global $wpdb;
        $table = $wpdb->prefix . 'irp_leads';
        $fresh_data = $wpdb->get_row($wpdb->prepare(
            "SELECT propstack_id, propstack_synced, propstack_error, status FROM {$table} WHERE id = %d",
            $lead->id
        ));

        if (!$fresh_data) {
            return [
                'status' => 'error',
                'label' => __('Lead nicht gefunden', 'immobilien-rechner-pro'),
                'class' => 'irp-status-error',
                'icon' => '❌',
            ];
        }

        if (!empty($fresh_data->propstack_id)) {
            return [
                'status' => 'synced',
                'label' => sprintf(__('Synchronisiert (ID: %d)', 'immobilien-rechner-pro'), $fresh_data->propstack_id),
                'class' => 'irp-status-success',
                'icon' => '✅',
            ];
        }

        if (!empty($fresh_data->propstack_error)) {
            return [
                'status' => 'error',
                'label' => $fresh_data->propstack_error,
                'class' => 'irp-status-error',
                'icon' => '❌',
            ];
        }

        if ($fresh_data->status === 'partial') {
            return [
                'status' => 'pending',
                'label' => __('Wartet auf Kontaktdaten', 'immobilien-rechner-pro'),
                'class' => 'irp-status-pending',
                'icon' => '⏳',
            ];
        }

        return [
            'status' => 'pending',
            'label' => __('Ausstehend', 'immobilien-rechner-pro'),
            'class' => 'irp-status-pending',
            'icon' => '⏳',
        ];
    }
}
