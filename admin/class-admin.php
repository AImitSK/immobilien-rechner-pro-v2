<?php
/**
 * Admin panel functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class IRP_Admin {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_irp_export_leads', [$this, 'ajax_export_leads']);
        add_action('wp_ajax_irp_send_test_email', [$this, 'ajax_send_test_email']);
        add_action('wp_ajax_irp_propstack_test', [$this, 'ajax_propstack_test']);
        add_action('wp_ajax_irp_propstack_save', [$this, 'ajax_propstack_save']);
        add_action('wp_ajax_irp_propstack_sync_lead', [$this, 'ajax_propstack_sync_lead']);
        add_action('wp_ajax_irp_propstack_refresh_activity_types', [$this, 'ajax_propstack_refresh_activity_types']);
    }
    
    public function add_admin_menu(): void {
        add_menu_page(
            __('Immobilien Rechner', 'immobilien-rechner-pro'),
            __('Immo Rechner', 'immobilien-rechner-pro'),
            'manage_options',
            'immobilien-rechner',
            [$this, 'render_dashboard'],
            'dashicons-calculator',
            30
        );
        
        add_submenu_page(
            'immobilien-rechner',
            __('Dashboard', 'immobilien-rechner-pro'),
            __('Dashboard', 'immobilien-rechner-pro'),
            'manage_options',
            'immobilien-rechner',
            [$this, 'render_dashboard']
        );
        
        add_submenu_page(
            'immobilien-rechner',
            __('Leads', 'immobilien-rechner-pro'),
            __('Leads', 'immobilien-rechner-pro'),
            'manage_options',
            'irp-leads',
            [$this, 'render_leads']
        );
        
        add_submenu_page(
            'immobilien-rechner',
            __('Matrix & Daten', 'immobilien-rechner-pro'),
            __('Matrix & Daten', 'immobilien-rechner-pro'),
            'manage_options',
            'irp-matrix',
            [$this, 'render_matrix']
        );

        add_submenu_page(
            'immobilien-rechner',
            __('Shortcode', 'immobilien-rechner-pro'),
            __('Shortcode', 'immobilien-rechner-pro'),
            'manage_options',
            'irp-shortcode',
            [$this, 'render_shortcode_generator']
        );

        add_submenu_page(
            'immobilien-rechner',
            __('Settings', 'immobilien-rechner-pro'),
            __('Settings', 'immobilien-rechner-pro'),
            'manage_options',
            'irp-settings',
            [$this, 'render_settings']
        );

        add_submenu_page(
            'immobilien-rechner',
            __('Integrationen', 'immobilien-rechner-pro'),
            __('Integrationen', 'immobilien-rechner-pro'),
            'manage_options',
            'irp-integrations',
            [$this, 'render_integrations']
        );
    }
    
    public function register_settings(): void {
        register_setting('irp_settings_group', 'irp_settings', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings'],
        ]);

        register_setting('irp_settings_group', 'irp_email_settings', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_email_settings'],
        ]);

        register_setting('irp_matrix_group', 'irp_price_matrix', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_price_matrix'],
        ]);
    }

    /**
     * Sanitize email settings
     *
     * @param array $input Input data
     * @return array Sanitized data
     */
    public function sanitize_email_settings($input) {
        // If no input or empty, return existing settings (prevents overwriting from other tabs)
        if (!is_array($input) || empty($input)) {
            return get_option('irp_email_settings', []);
        }

        // Merge with existing settings
        $existing = get_option('irp_email_settings', []);
        $sanitized = [];

        $sanitized['enabled'] = isset($input['enabled']) ? !empty($input['enabled']) : ($existing['enabled'] ?? false);
        $sanitized['sender_name'] = isset($input['sender_name']) ? sanitize_text_field($input['sender_name']) : ($existing['sender_name'] ?? '');
        $sanitized['sender_email'] = isset($input['sender_email']) ? sanitize_email($input['sender_email']) : ($existing['sender_email'] ?? '');
        $sanitized['subject'] = isset($input['subject']) ? sanitize_text_field($input['subject']) : ($existing['subject'] ?? 'Ihre Immobilienbewertung - {property_type} in {city}');
        $sanitized['email_content'] = isset($input['email_content']) ? wp_kses_post($input['email_content']) : ($existing['email_content'] ?? '');

        return $sanitized;
    }

    public function sanitize_price_matrix($input) {
        if (!is_array($input)) {
            $input = [];
        }
        $sanitized = [];

        // Cities (new structure)
        if (isset($input['cities']) && is_array($input['cities'])) {
            $sanitized['cities'] = [];
            foreach ($input['cities'] as $city) {
                if (empty($city['id']) || empty($city['name'])) {
                    continue; // Skip empty rows
                }
                $sanitized['cities'][] = [
                    'id' => sanitize_key($city['id']),
                    'name' => sanitize_text_field($city['name']),
                    'base_price' => max(1, (float) ($city['base_price'] ?? 12)),
                    'size_degression' => max(0, min(0.5, (float) ($city['size_degression'] ?? 0.20))),
                    'sale_factor' => max(5, (float) ($city['sale_factor'] ?? 25)),
                ];
            }
            // Re-index the array
            $sanitized['cities'] = array_values($sanitized['cities']);
        }

        // Condition multipliers
        if (isset($input['condition_multipliers']) && is_array($input['condition_multipliers'])) {
            foreach ($input['condition_multipliers'] as $condition => $multiplier) {
                $sanitized['condition_multipliers'][sanitize_text_field($condition)] = (float) $multiplier;
            }
        }

        // Property type multipliers
        if (isset($input['type_multipliers']) && is_array($input['type_multipliers'])) {
            foreach ($input['type_multipliers'] as $type => $multiplier) {
                $sanitized['type_multipliers'][sanitize_text_field($type)] = (float) $multiplier;
            }
        }

        // Feature premiums
        if (isset($input['feature_premiums']) && is_array($input['feature_premiums'])) {
            foreach ($input['feature_premiums'] as $feature => $premium) {
                $sanitized['feature_premiums'][sanitize_text_field($feature)] = (float) $premium;
            }
        }

        // Global calculation parameters
        $sanitized['interest_rate'] = (float) ($input['interest_rate'] ?? 3.0);
        $sanitized['appreciation_rate'] = (float) ($input['appreciation_rate'] ?? 2.0);
        $sanitized['rent_increase_rate'] = (float) ($input['rent_increase_rate'] ?? 2.0);

        // Location ratings
        if (isset($input['location_ratings']) && is_array($input['location_ratings'])) {
            $sanitized['location_ratings'] = [];
            foreach ($input['location_ratings'] as $level => $rating) {
                $level = (int) $level;
                if ($level >= 1 && $level <= 5) {
                    $sanitized['location_ratings'][$level] = [
                        'name' => sanitize_text_field($rating['name'] ?? ''),
                        'multiplier' => max(0.5, min(2.0, (float) ($rating['multiplier'] ?? 1.0))),
                        'description' => sanitize_textarea_field($rating['description'] ?? ''),
                    ];
                }
            }
        } else {
            // Use defaults if not set
            $sanitized['location_ratings'] = $this->get_default_location_ratings();
        }

        // Age multipliers (Baualtersklassen)
        if (isset($input['age_multipliers']) && is_array($input['age_multipliers'])) {
            $sanitized['age_multipliers'] = [];
            foreach ($input['age_multipliers'] as $key => $data) {
                $sanitized_key = sanitize_key($key);
                $sanitized['age_multipliers'][$sanitized_key] = [
                    'name' => sanitize_text_field($data['name'] ?? ''),
                    'multiplier' => max(0.5, min(2.0, (float) ($data['multiplier'] ?? 1.0))),
                    'min_year' => !empty($data['min_year']) ? (int) $data['min_year'] : null,
                    'max_year' => !empty($data['max_year']) ? (int) $data['max_year'] : null,
                ];
            }
        }

        return $sanitized;
    }
    
    public function sanitize_settings($input) {
        // If no input or empty, return existing settings (prevents overwriting from other tabs)
        if (!is_array($input) || empty($input)) {
            return get_option('irp_settings', []);
        }

        // Merge with existing settings
        $existing = get_option('irp_settings', []);
        $sanitized = $existing; // Start with existing values

        // Only update fields that were actually submitted
        if (isset($input['primary_color'])) {
            $sanitized['primary_color'] = sanitize_hex_color($input['primary_color']) ?: '#2563eb';
        }
        if (isset($input['secondary_color'])) {
            $sanitized['secondary_color'] = sanitize_hex_color($input['secondary_color']) ?: '#1e40af';
        }

        // Branding fields - only update if submitted
        if (array_key_exists('company_name', $input)) {
            $sanitized['company_name'] = sanitize_text_field($input['company_name']);
        }
        if (array_key_exists('company_name_2', $input)) {
            $sanitized['company_name_2'] = sanitize_text_field($input['company_name_2']);
        }
        if (array_key_exists('company_name_3', $input)) {
            $sanitized['company_name_3'] = sanitize_text_field($input['company_name_3']);
        }
        if (array_key_exists('company_logo', $input)) {
            $sanitized['company_logo'] = esc_url_raw($input['company_logo']);
        }
        if (array_key_exists('company_logo_width', $input)) {
            $sanitized['company_logo_width'] = max(50, min(300, (int) $input['company_logo_width']));
        }
        if (array_key_exists('company_street', $input)) {
            $sanitized['company_street'] = sanitize_text_field($input['company_street']);
        }
        if (array_key_exists('company_zip', $input)) {
            $sanitized['company_zip'] = sanitize_text_field($input['company_zip']);
        }
        if (array_key_exists('company_city', $input)) {
            $sanitized['company_city'] = sanitize_text_field($input['company_city']);
        }
        if (array_key_exists('company_phone', $input)) {
            $sanitized['company_phone'] = sanitize_text_field($input['company_phone']);
        }
        if (array_key_exists('company_email', $input)) {
            $sanitized['company_email'] = sanitize_email($input['company_email']);
        }

        // Calculator defaults - only update if submitted
        if (array_key_exists('default_maintenance_rate', $input)) {
            $sanitized['default_maintenance_rate'] = (float) $input['default_maintenance_rate'];
        }
        if (array_key_exists('default_vacancy_rate', $input)) {
            $sanitized['default_vacancy_rate'] = (float) $input['default_vacancy_rate'];
        }
        if (array_key_exists('default_broker_commission', $input)) {
            $sanitized['default_broker_commission'] = (float) $input['default_broker_commission'];
        }
        if (array_key_exists('calculator_max_width', $input)) {
            $sanitized['calculator_max_width'] = max(680, min(1200, (int) $input['calculator_max_width']));
        }

        // Checkboxes need special handling - only process if form section was submitted
        // We detect this by checking if any field from that section exists
        if (array_key_exists('primary_color', $input) || array_key_exists('calculator_max_width', $input)) {
            // General tab was submitted
            $sanitized['enable_pdf_export'] = !empty($input['enable_pdf_export']);
            $sanitized['require_consent'] = !empty($input['require_consent']);
        }
        if (array_key_exists('privacy_policy_url', $input)) {
            $sanitized['privacy_policy_url'] = esc_url_raw($input['privacy_policy_url']);
        }

        // Google Maps settings
        if (array_key_exists('google_maps_api_key', $input)) {
            $sanitized['google_maps_api_key'] = sanitize_text_field($input['google_maps_api_key']);
            $sanitized['show_map_in_location_step'] = !empty($input['show_map_in_location_step']);
        }

        // reCAPTCHA settings
        if (array_key_exists('recaptcha_site_key', $input)) {
            $sanitized['recaptcha_site_key'] = sanitize_text_field($input['recaptcha_site_key']);
        }
        if (array_key_exists('recaptcha_secret_key', $input)) {
            $sanitized['recaptcha_secret_key'] = sanitize_text_field($input['recaptcha_secret_key']);
        }
        if (array_key_exists('recaptcha_threshold', $input)) {
            $sanitized['recaptcha_threshold'] = max(0, min(1, (float) $input['recaptcha_threshold']));
        }

        // Google Ads Tracking settings
        if (array_key_exists('gads_conversion_id', $input)) {
            $sanitized['gads_conversion_id'] = sanitize_text_field($input['gads_conversion_id']);
        }
        if (array_key_exists('gads_partial_label', $input)) {
            $sanitized['gads_partial_label'] = sanitize_text_field($input['gads_partial_label']);
        }
        if (array_key_exists('gads_complete_label', $input)) {
            $sanitized['gads_complete_label'] = sanitize_text_field($input['gads_complete_label']);
        }

        return $sanitized;
    }
    
    public function enqueue_admin_assets(string $hook): void {
        if (strpos($hook, 'immobilien-rechner') === false && strpos($hook, 'irp-') === false) {
            return;
        }
        
        wp_enqueue_style(
            'irp-admin',
            IRP_PLUGIN_URL . 'admin/css/admin.css',
            [],
            IRP_VERSION
        );
        
        wp_enqueue_script(
            'irp-admin',
            IRP_PLUGIN_URL . 'admin/js/admin.js',
            ['jquery'],
            IRP_VERSION,
            true
        );

        // Localize script for translations and AJAX
        wp_localize_script('irp-admin', 'irpAdmin', [
            'nonce' => wp_create_nonce('irp_admin_nonce'),
            'i18n' => [
                'mediaTitle' => __('Logo auswählen', 'immobilien-rechner-pro'),
                'mediaButton' => __('Dieses Bild verwenden', 'immobilien-rechner-pro'),
            ],
        ]);

        // Media uploader for logo
        if (strpos($hook, 'irp-settings') !== false) {
            wp_enqueue_media();
        }
    }
    
    public function render_dashboard(): void {
        global $wpdb;
        
        $leads_table = $wpdb->prefix . 'irp_leads';
        $calculations_table = $wpdb->prefix . 'irp_calculations';
        
        // Get statistics
        $total_leads = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$leads_table}");
        $leads_this_month = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$leads_table} WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')"
        );
        $total_calculations = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$calculations_table}");
        $rental_calculations = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$calculations_table} WHERE mode = 'rental'"
        );
        $sale_value_calculations = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$calculations_table} WHERE mode = 'sale_value'"
        );

        // Recent leads
        $recent_leads = $wpdb->get_results(
            "SELECT * FROM {$leads_table} ORDER BY created_at DESC LIMIT 5"
        );
        
        include IRP_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    public function render_leads(): void {
        $leads_manager = new IRP_Leads();

        // Handle single delete action (from detail view)
        if (isset($_POST['action']) && $_POST['action'] === 'delete_lead' && wp_verify_nonce($_POST['_wpnonce'], 'irp_delete_lead')) {
            $leads_manager->delete((int) $_POST['lead_id']);
            wp_redirect(admin_url('admin.php?page=irp-leads&deleted=1'));
            exit;
        }

        // Handle bulk delete action
        if (isset($_POST['action']) && $_POST['action'] === 'bulk_delete' && wp_verify_nonce($_POST['_wpnonce'], 'irp_bulk_delete_leads')) {
            $lead_ids = array_map('intval', $_POST['lead_ids'] ?? []);
            $deleted = 0;
            foreach ($lead_ids as $lead_id) {
                if ($leads_manager->delete($lead_id)) {
                    $deleted++;
                }
            }
            wp_redirect(admin_url('admin.php?page=irp-leads&deleted=' . $deleted));
            exit;
        }

        // Show success message after delete
        if (isset($_GET['deleted'])) {
            $count = (int) $_GET['deleted'];
            if ($count === 1) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Lead wurde gelöscht.', 'immobilien-rechner-pro') . '</p></div>';
            } elseif ($count > 1) {
                echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(esc_html__('%d Leads wurden gelöscht.', 'immobilien-rechner-pro'), $count) . '</p></div>';
            }
        }

        // Handle single lead view
        if (isset($_GET['lead'])) {
            $lead = $leads_manager->get((int) $_GET['lead']);
            include IRP_PLUGIN_DIR . 'admin/views/lead-detail.php';
            return;
        }
        
        // Get filtered leads
        $args = [
            'page' => (int) ($_GET['paged'] ?? 1),
            'mode' => sanitize_text_field($_GET['mode'] ?? ''),
            'status' => sanitize_text_field($_GET['status'] ?? ''),
            'search' => sanitize_text_field($_GET['s'] ?? ''),
        ];

        $leads = $leads_manager->get_all($args);
        
        include IRP_PLUGIN_DIR . 'admin/views/leads-list.php';
    }
    
    public function render_matrix(): void {
        $matrix = get_option('irp_price_matrix', $this->get_default_matrix());
        include IRP_PLUGIN_DIR . 'admin/views/matrix.php';
    }

    public function render_shortcode_generator(): void {
        $matrix = get_option('irp_price_matrix', []);
        $cities = $matrix['cities'] ?? [];
        include IRP_PLUGIN_DIR . 'admin/views/shortcode-generator.php';
    }

    public function get_default_matrix(): array {
        return [
            'base_prices' => [
                '1' => 18.50,  // Berlin
                '2' => 16.00,  // Hamburg
                '3' => 11.50,  // Hannover
                '4' => 11.00,  // Düsseldorf
                '5' => 11.50,  // Köln/Bonn
                '6' => 13.50,  // Frankfurt
                '7' => 13.00,  // Stuttgart
                '8' => 19.00,  // München
                '9' => 10.00,  // Nürnberg
                '0' => 10.50,  // Leipzig/Dresden
            ],
            'condition_multipliers' => [
                'new' => 1.25,
                'renovated' => 1.10,
                'good' => 1.00,
                'needs_renovation' => 0.80,
            ],
            'type_multipliers' => [
                'apartment' => 1.00,
                'house' => 1.15,
                'commercial' => 0.85,
            ],
            'feature_premiums' => [
                'balcony' => 0.50,
                'terrace' => 0.75,
                'garden' => 1.00,
                'elevator' => 0.30,
                'parking' => 0.40,
                'garage' => 0.60,
                'cellar' => 0.20,
                'fitted_kitchen' => 0.50,
                'floor_heating' => 0.40,
                'guest_toilet' => 0.25,
                'barrier_free' => 0.30,
            ],
            'sale_factors' => [
                '1' => 30,  // Berlin
                '2' => 28,  // Hamburg
                '3' => 22,  // Hannover
                '4' => 23,  // Düsseldorf
                '5' => 24,  // Köln/Bonn
                '6' => 27,  // Frankfurt
                '7' => 26,  // Stuttgart
                '8' => 35,  // München
                '9' => 20,  // Nürnberg
                '0' => 21,  // Leipzig/Dresden
            ],
            'interest_rate' => 3.0,
            'appreciation_rate' => 2.0,
            'rent_increase_rate' => 2.0,
        ];
    }

    public function render_settings(): void {
        $settings = get_option('irp_settings', []);
        include IRP_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    public function ajax_export_leads(): void {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        check_ajax_referer('irp_export_leads', 'nonce');
        
        $leads_manager = new IRP_Leads();
        $csv = $leads_manager->export_csv([
            'mode' => sanitize_text_field($_POST['mode'] ?? ''),
            'date_from' => sanitize_text_field($_POST['date_from'] ?? ''),
            'date_to' => sanitize_text_field($_POST['date_to'] ?? ''),
        ]);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="leads-export-' . date('Y-m-d') . '.csv"');
        
        echo $csv;
        wp_die();
    }

    /**
     * Send test email via AJAX
     */
    public function ajax_send_test_email(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        check_ajax_referer('irp_admin_nonce', 'nonce');

        $email = sanitize_email($_POST['email'] ?? '');
        if (!is_email($email)) {
            wp_send_json_error(['message' => __('Bitte geben Sie eine gültige E-Mail-Adresse ein.', 'immobilien-rechner-pro')]);
        }

        $result = IRP_Email::send_test_email($email);

        if ($result) {
            wp_send_json_success(['message' => sprintf(__('Test-E-Mail wurde an %s gesendet.', 'immobilien-rechner-pro'), $email)]);
        } else {
            wp_send_json_error(['message' => __('E-Mail konnte nicht gesendet werden. Bitte überprüfen Sie Ihre SMTP-Einstellungen.', 'immobilien-rechner-pro')]);
        }
    }

    public function get_default_location_ratings(): array {
        return [
            1 => [
                'name' => __('Einfache Lage', 'immobilien-rechner-pro'),
                'multiplier' => 0.85,
                'description' => "Eingeschränkte Anbindung an öffentliche Verkehrsmittel\nWenig Infrastruktur in direkter Umgebung\nLärm durch Verkehr, Gewerbe oder Industrie\nEinfache Wohngegend",
            ],
            2 => [
                'name' => __('Normale Lage', 'immobilien-rechner-pro'),
                'multiplier' => 0.95,
                'description' => "Akzeptable Anbindung an öffentliche Verkehrsmittel\nGrundversorgung (Supermarkt) erreichbar\nDurchschnittliche Wohngegend\nMäßiger Geräuschpegel",
            ],
            3 => [
                'name' => __('Gute Lage', 'immobilien-rechner-pro'),
                'multiplier' => 1.00,
                'description' => "Gute Anbindung an öffentliche Verkehrsmittel\nEinkaufsmöglichkeiten und Schulen in der Nähe\nRuhige Wohngegend\nGepflegtes Umfeld",
            ],
            4 => [
                'name' => __('Sehr gute Lage', 'immobilien-rechner-pro'),
                'multiplier' => 1.10,
                'description' => "Sehr gute Verkehrsanbindung (ÖPNV und Straße)\nUmfangreiche Infrastruktur (Ärzte, Restaurants, Kultur)\nGrünflächen und Parks in der Nähe\nGehobene Wohngegend",
            ],
            5 => [
                'name' => __('Premium-Lage', 'immobilien-rechner-pro'),
                'multiplier' => 1.25,
                'description' => "Beste Verkehrsanbindung\nExklusive Nachbarschaft\nTop-Infrastruktur und Freizeitmöglichkeiten\nBesondere Lagevorteile (Seenähe, Altstadt, Villenviertel)",
            ],
        ];
    }

    /**
     * Render integrations page
     */
    public function render_integrations(): void {
        $propstack_settings = get_option('irp_propstack_settings', []);
        $matrix = get_option('irp_price_matrix', []);
        $cities = $matrix['cities'] ?? [];

        include IRP_PLUGIN_DIR . 'admin/views/integrations.php';
    }

    /**
     * Test Propstack connection via AJAX
     */
    public function ajax_propstack_test(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        check_ajax_referer('irp_admin_nonce', 'nonce');

        $api_key = sanitize_text_field($_POST['api_key'] ?? '');

        $result = IRP_Propstack::test_connection($api_key);

        if ($result['success']) {
            wp_send_json_success([
                'message' => $result['message'],
                'brokers' => $result['brokers'] ?? [],
            ]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    }

    /**
     * Save Propstack settings via AJAX
     */
    public function ajax_propstack_save(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        check_ajax_referer('irp_admin_nonce', 'nonce');

        // Get existing settings to preserve values not being updated
        $existing = get_option('irp_propstack_settings', []);

        $settings = [
            'enabled' => !empty($_POST['enabled']),
            'api_key' => sanitize_text_field($_POST['api_key'] ?? $existing['api_key'] ?? ''),
            'city_broker_mapping' => $existing['city_broker_mapping'] ?? [],
            'newsletter_broker_id' => sanitize_text_field($_POST['newsletter_broker_id'] ?? $existing['newsletter_broker_id'] ?? ''),
            'sync_newsletter_only' => !empty($_POST['sync_newsletter_only']),
            // Activity settings - preserve existing if not in POST
            'activity_enabled' => isset($_POST['activity_enabled']) ? !empty($_POST['activity_enabled']) : ($existing['activity_enabled'] ?? false),
            'activity_type_id' => isset($_POST['activity_type_id']) ? (int) $_POST['activity_type_id'] : ($existing['activity_type_id'] ?? null),
            'activity_create_task' => isset($_POST['activity_create_task']) ? !empty($_POST['activity_create_task']) : ($existing['activity_create_task'] ?? false),
            'activity_task_due_days' => isset($_POST['activity_task_due_days']) ? max(1, (int) $_POST['activity_task_due_days']) : ($existing['activity_task_due_days'] ?? 1),
        ];

        // Process broker mapping
        if (!empty($_POST['broker_mapping']) && is_array($_POST['broker_mapping'])) {
            $settings['city_broker_mapping'] = [];
            foreach ($_POST['broker_mapping'] as $city_id => $broker_id) {
                if (!empty($broker_id)) {
                    $settings['city_broker_mapping'][sanitize_key($city_id)] = (int) $broker_id;
                }
            }
        }

        update_option('irp_propstack_settings', $settings);

        wp_send_json_success(['message' => __('Einstellungen wurden gespeichert.', 'immobilien-rechner-pro')]);
    }

    /**
     * Refresh activity types from Propstack via AJAX
     */
    public function ajax_propstack_refresh_activity_types(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        check_ajax_referer('irp_admin_nonce', 'nonce');

        $activity_types = IRP_Propstack::get_activity_types();

        if (is_wp_error($activity_types)) {
            wp_send_json_error(['message' => $activity_types->get_error_message()]);
        }

        if (empty($activity_types)) {
            wp_send_json_error(['message' => __('Keine Aktivitätstypen gefunden.', 'immobilien-rechner-pro')]);
        }

        // Cache the activity types
        set_transient('irp_propstack_activity_types', $activity_types, HOUR_IN_SECONDS);

        wp_send_json_success([
            'message' => sprintf(__('%d Aktivitätstypen geladen.', 'immobilien-rechner-pro'), count($activity_types)),
            'types' => $activity_types,
        ]);
    }

    /**
     * Sync a single lead to Propstack via AJAX
     */
    public function ajax_propstack_sync_lead(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        check_ajax_referer('irp_admin_nonce', 'nonce');

        $lead_id = (int) ($_POST['lead_id'] ?? 0);
        if (!$lead_id) {
            wp_send_json_error(['message' => __('Ungültige Lead-ID.', 'immobilien-rechner-pro')]);
        }

        $result = IRP_Propstack::sync_lead($lead_id);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        } else {
            // Get updated lead to return propstack_id
            $leads = new IRP_Leads();
            $updated_lead = $leads->get($lead_id);
            wp_send_json_success([
                'message' => __('Erfolgreich synchronisiert!', 'immobilien-rechner-pro'),
                'propstack_id' => $updated_lead->propstack_id ?? null,
            ]);
        }
    }
}
