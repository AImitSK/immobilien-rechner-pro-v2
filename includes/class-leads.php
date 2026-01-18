<?php
/**
 * Lead management functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class IRP_Leads {
    
    private string $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'irp_leads';
    }
    
    /**
     * Create a new lead
     *
     * @param array $data Lead data
     * @return int|\WP_Error Lead ID on success, WP_Error on failure
     */
    public function create(array $data) {
        global $wpdb;
        
        // Validate email
        if (!is_email($data['email'])) {
            return new \WP_Error('invalid_email', __('Bitte geben Sie eine gültige E-Mail-Adresse an.', 'immobilien-rechner-pro'));
        }
        
        $insert_data = [
            'name' => sanitize_text_field($data['name'] ?? ''),
            'email' => sanitize_email($data['email']),
            'phone' => sanitize_text_field($data['phone'] ?? ''),
            'mode' => sanitize_text_field($data['mode']),
            'property_type' => sanitize_text_field($data['calculation_data']['property_type'] ?? ''),
            'property_size' => (float) ($data['calculation_data']['size'] ?? 0),
            'property_location' => sanitize_text_field($data['calculation_data']['location'] ?? ''),
            'zip_code' => sanitize_text_field($data['calculation_data']['zip_code'] ?? ''),
            'calculation_data' => wp_json_encode($data['calculation_data'] ?? []),
            'consent' => (int) $data['consent'],
            'source' => sanitize_text_field($data['source'] ?? 'calculator'),
        ];
        
        $result = $wpdb->insert(
            $this->table_name,
            $insert_data,
            ['%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%d', '%s']
        );
        
        if ($result === false) {
            return new \WP_Error('db_error', __('Lead-Daten konnten nicht gespeichert werden.', 'immobilien-rechner-pro'));
        }
        
        return $wpdb->insert_id;
    }

    /**
     * Create a partial lead (property data only, no contact info)
     *
     * @param array $data Lead data
     * @return int|\WP_Error Lead ID on success, WP_Error on failure
     */
    public function create_partial(array $data) {
        global $wpdb;

        error_log('[IRP Leads] create_partial called');

        $mode = sanitize_text_field($data['mode']);
        $is_sale_value = $mode === 'sale_value';

        // Build calculation_data based on mode
        $calculation_data = [
            'mode' => $mode,
            'property_type' => $data['property_type'] ?? '',
            'size' => $data['property_size'] ?? ($data['living_space'] ?? 0),
            'city_id' => $data['city_id'] ?? '',
            'city_name' => $data['city_name'] ?? ($data['property_location'] ?? ''),
            'address' => $data['address'] ?? ($data['street_address'] ?? ''),
            'condition' => $data['condition'] ?? '',
            'location_rating' => $data['location_rating'] ?? 3,
            'features' => $data['features'] ?? [],
            'result' => $data['calculation_result'] ?? null,
        ];

        // Add sale value specific fields
        if ($is_sale_value) {
            $calculation_data = array_merge($calculation_data, [
                'land_size' => $data['land_size'] ?? null,
                'living_space' => $data['living_space'] ?? ($data['property_size'] ?? null),
                'house_type' => $data['house_type'] ?? null,
                'build_year' => $data['build_year'] ?? null,
                'modernization' => $data['modernization'] ?? null,
                'quality' => $data['quality'] ?? null,
                'usage_type' => $data['usage_type'] ?? null,
                'sale_intention' => $data['sale_intention'] ?? null,
                'timeframe' => $data['timeframe'] ?? null,
                'street_address' => $data['street_address'] ?? null,
                'zip_code' => $data['zip_code'] ?? null,
                'property_location' => $data['property_location'] ?? ($data['city_name'] ?? null),
            ]);
        }

        $insert_data = [
            'email' => '', // Will be filled when completing
            'mode' => $mode,
            'property_type' => sanitize_text_field($data['property_type'] ?? ''),
            'property_size' => (float) ($data['property_size'] ?? ($data['living_space'] ?? 0)),
            'property_location' => sanitize_text_field($data['city_name'] ?? ($data['property_location'] ?? '')),
            'zip_code' => sanitize_text_field($data['zip_code'] ?? ''),
            'calculation_data' => wp_json_encode($calculation_data),
            'status' => 'partial',
            'ip_address' => $this->get_client_ip(),
            'source' => sanitize_text_field($data['source'] ?? 'calculator'),
        ];

        // Add sale value specific database fields
        if ($is_sale_value) {
            $insert_data['land_size'] = !empty($data['land_size']) ? (float) $data['land_size'] : null;
            $insert_data['house_type'] = !empty($data['house_type']) ? sanitize_text_field($data['house_type']) : null;
            $insert_data['build_year'] = !empty($data['build_year']) ? (int) $data['build_year'] : null;
            $insert_data['modernization'] = !empty($data['modernization']) ? sanitize_text_field($data['modernization']) : null;
            $insert_data['quality'] = !empty($data['quality']) ? sanitize_text_field($data['quality']) : null;
            $insert_data['usage_type'] = !empty($data['usage_type']) ? sanitize_text_field($data['usage_type']) : null;
            $insert_data['sale_intention'] = !empty($data['sale_intention']) ? sanitize_text_field($data['sale_intention']) : null;
            $insert_data['timeframe'] = !empty($data['timeframe']) ? sanitize_text_field($data['timeframe']) : null;
            $insert_data['street_address'] = !empty($data['street_address']) ? sanitize_text_field($data['street_address']) : null;
        }

        error_log('[IRP Leads] Insert data: ' . print_r($insert_data, true));

        $result = $wpdb->insert($this->table_name, $insert_data);

        error_log('[IRP Leads] Insert result: ' . var_export($result, true));

        if ($result === false) {
            error_log('[IRP Leads] DB Error: ' . $wpdb->last_error);
            return new \WP_Error('db_error', __('Daten konnten nicht gespeichert werden.', 'immobilien-rechner-pro'));
        }

        error_log('[IRP Leads] Insert ID: ' . $wpdb->insert_id);

        return $wpdb->insert_id;
    }

    /**
     * Complete a partial lead (add contact info)
     *
     * @param int $lead_id Lead ID
     * @param array $data Contact data
     * @return bool|\WP_Error True on success, WP_Error on failure
     */
    public function complete(int $lead_id, array $data) {
        global $wpdb;

        // Validate email
        if (!is_email($data['email'])) {
            return new \WP_Error('invalid_email', __('Bitte geben Sie eine gültige E-Mail-Adresse an.', 'immobilien-rechner-pro'));
        }

        $update_data = [
            'name' => sanitize_text_field($data['name']),
            'email' => sanitize_email($data['email']),
            'phone' => sanitize_text_field($data['phone'] ?? ''),
            'consent' => (int) $data['consent'],
            'newsletter_consent' => (int) ($data['newsletter_consent'] ?? 0),
            'status' => 'complete',
            'completed_at' => current_time('mysql'),
        ];

        if (isset($data['recaptcha_score'])) {
            $update_data['recaptcha_score'] = (float) $data['recaptcha_score'];
        }

        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            ['id' => $lead_id],
            ['%s', '%s', '%s', '%d', '%d', '%s', '%s', '%f'],
            ['%d']
        );

        if ($result === false) {
            return new \WP_Error('db_error', __('Lead konnte nicht aktualisiert werden.', 'immobilien-rechner-pro'));
        }

        return true;
    }

    /**
     * Get client IP address
     */
    private function get_client_ip(): string {
        $ip_keys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '';
    }

    /**
     * Get a single lead by ID
     */
    public function get(int $id): ?object {
        global $wpdb;
        
        $lead = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id)
        );
        
        if ($lead && $lead->calculation_data) {
            $lead->calculation_data = json_decode($lead->calculation_data, true);
        }
        
        return $lead;
    }
    
    /**
     * Get all leads with optional filtering and pagination
     */
    public function get_all(array $args = []): array {
        global $wpdb;
        
        $defaults = [
            'per_page' => 20,
            'page' => 1,
            'mode' => '',
            'status' => '',
            'search' => '',
            'date_from' => '',
            'date_to' => '',
            'orderby' => 'created_at',
            'order' => 'DESC',
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        // Always have at least one prepared parameter to avoid empty $values array
        $where = ['1=%d'];
        $values = [1];
        
        if (!empty($args['mode'])) {
            $where[] = 'mode = %s';
            $values[] = $args['mode'];
        }

        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        if (!empty($args['search'])) {
            // Sanitize and limit search input length to prevent abuse
            $search = sanitize_text_field($args['search']);
            $search = substr($search, 0, 100);
            $where[] = '(name LIKE %s OR email LIKE %s OR property_location LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $values[] = $search_term;
            $values[] = $search_term;
            $values[] = $search_term;
        }
        
        if (!empty($args['date_from'])) {
            $where[] = 'created_at >= %s';
            $values[] = $args['date_from'] . ' 00:00:00';
        }
        
        if (!empty($args['date_to'])) {
            $where[] = 'created_at <= %s';
            $values[] = $args['date_to'] . ' 23:59:59';
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Sanitize orderby and order
        $allowed_orderby = ['id', 'name', 'email', 'mode', 'created_at'];
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'created_at';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';
        
        $offset = ($args['page'] - 1) * $args['per_page'];
        
        // Get total count (always use prepare since $values is never empty)
        $count_sql = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_clause}";
        $count_sql = $wpdb->prepare($count_sql, $values);
        $total = (int) $wpdb->get_var($count_sql);
        
        // Get results
        $sql = "SELECT * FROM {$this->table_name} WHERE {$where_clause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $values[] = $args['per_page'];
        $values[] = $offset;
        
        $results = $wpdb->get_results($wpdb->prepare($sql, $values));
        
        // Decode JSON data
        foreach ($results as &$lead) {
            if ($lead->calculation_data) {
                $lead->calculation_data = json_decode($lead->calculation_data, true);
            }
        }
        
        return [
            'items' => $results,
            'total' => $total,
            'pages' => ceil($total / $args['per_page']),
            'current_page' => $args['page'],
        ];
    }
    
    /**
     * Delete a lead
     */
    public function delete(int $id): bool {
        global $wpdb;
        
        return (bool) $wpdb->delete($this->table_name, ['id' => $id], ['%d']);
    }
    
    /**
     * Send notification email to admin
     */
    public function send_notification(int $lead_id): bool {
        $lead = $this->get($lead_id);

        if (!$lead) {
            return false;
        }

        $settings = get_option('irp_settings', []);
        $to = $settings['company_email'] ?? get_option('admin_email');

        $type_labels = [
            'apartment' => __('Wohnung', 'immobilien-rechner-pro'),
            'house' => __('Haus', 'immobilien-rechner-pro'),
            'commercial' => __('Gewerbe', 'immobilien-rechner-pro'),
            'land' => __('Grundstück', 'immobilien-rechner-pro'),
        ];

        $mode_labels = [
            'rental' => __('Mietwert-Berechnung', 'immobilien-rechner-pro'),
            'comparison' => __('Verkauf vs. Vermietung', 'immobilien-rechner-pro'),
            'sale_value' => __('Verkaufswert-Berechnung', 'immobilien-rechner-pro'),
        ];

        $mode_label = $mode_labels[$lead->mode] ?? $lead->mode;

        $subject = sprintf(
            __('[Neuer Lead] %s - %s', 'immobilien-rechner-pro'),
            $mode_label,
            $lead->property_location ?: $lead->zip_code
        );

        $property_type_label = $type_labels[$lead->property_type] ?? ucfirst($lead->property_type ?: '-');

        // Newsletter status
        $newsletter_status = !empty($lead->newsletter_consent)
            ? __('Ja', 'immobilien-rechner-pro')
            : __('Nein', 'immobilien-rechner-pro');

        // Base message
        $message = sprintf(
            __("Neuer Lead vom Immobilien Rechner Pro\n" .
               "══════════════════════════════════════\n\n" .
               "KONTAKTDATEN\n" .
               "────────────────────────────────────\n" .
               "Name:       %s\n" .
               "E-Mail:     %s\n" .
               "Telefon:    %s\n" .
               "Newsletter: %s\n\n" .
               "IMMOBILIE\n" .
               "────────────────────────────────────\n" .
               "Modus:      %s\n" .
               "Objekttyp:  %s\n",
            'immobilien-rechner-pro'),
            $lead->name ?: '-',
            $lead->email,
            $lead->phone ?: '-',
            $newsletter_status,
            $mode_label,
            $property_type_label
        );

        // Add mode-specific fields
        if ($lead->mode === 'sale_value') {
            // Sale value specific info
            $calc_data = $lead->calculation_data ?? [];

            if (!empty($lead->land_size)) {
                $message .= sprintf("Grundstück: %s m²\n", $lead->land_size);
            }
            if (!empty($lead->property_size) && $lead->property_type !== 'land') {
                $message .= sprintf("Wohnfläche: %s m²\n", $lead->property_size);
            }
            if (!empty($lead->build_year)) {
                $message .= sprintf("Baujahr:    %s\n", $lead->build_year);
            }
            if (!empty($lead->quality)) {
                $quality_labels = ['simple' => 'Einfach', 'normal' => 'Normal', 'upscale' => 'Gehoben', 'luxury' => 'Luxuriös'];
                $message .= sprintf("Qualität:   %s\n", $quality_labels[$lead->quality] ?? $lead->quality);
            }
            if (!empty($lead->street_address)) {
                $message .= sprintf("Adresse:    %s\n", $lead->street_address);
            }
            $message .= sprintf("Standort:   %s %s\n", $lead->zip_code ?: '', $lead->property_location ?: '');

            // Sale intention and timeframe
            if (!empty($lead->sale_intention)) {
                $intention_labels = ['sell' => 'Verkaufen', 'buy' => 'Kaufen'];
                $message .= sprintf("Absicht:    %s\n", $intention_labels[$lead->sale_intention] ?? $lead->sale_intention);
            }
            if (!empty($lead->timeframe)) {
                $timeframe_labels = [
                    'immediately' => 'Sofort',
                    '3_months' => 'In 3 Monaten',
                    '6_months' => 'In 6 Monaten',
                    '12_months' => 'In 12 Monaten',
                    'undecided' => 'Noch offen',
                ];
                $message .= sprintf("Zeitrahmen: %s\n", $timeframe_labels[$lead->timeframe] ?? $lead->timeframe);
            }

            // Calculated value
            if (!empty($calc_data['result']['price_estimate'])) {
                $message .= sprintf("\nGeschätzter Wert: %s €\n",
                    number_format($calc_data['result']['price_estimate'], 0, ',', '.'));
            }
        } else {
            // Rental/comparison mode
            $message .= sprintf("Größe:      %s m²\n", $lead->property_size ?: '-');
            $message .= sprintf("Standort:   %s %s\n", $lead->zip_code ?: '', $lead->property_location ?: '');
        }

        $message .= sprintf(
            "\n────────────────────────────────────\n" .
            "Im Admin ansehen:\n%s",
            admin_url('admin.php?page=irp-leads&lead=' . $lead_id)
        );

        $headers = ['Content-Type: text/plain; charset=UTF-8'];

        if (!empty($settings['company_name'])) {
            $headers[] = 'From: ' . $settings['company_name'] . ' <' . $to . '>';
        }

        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Export leads to CSV
     */
    public function export_csv(array $args = []): string {
        $leads = $this->get_all(array_merge($args, ['per_page' => 9999]));
        
        $output = fopen('php://temp', 'r+');
        
        // Header row
        fputcsv($output, [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Mode',
            'Property Type',
            'Size (m²)',
            'ZIP Code',
            'Location',
            'Created At',
        ]);
        
        // Data rows
        foreach ($leads['items'] as $lead) {
            fputcsv($output, [
                $lead->id,
                $lead->name,
                $lead->email,
                $lead->phone,
                $lead->mode,
                $lead->property_type,
                $lead->property_size,
                $lead->zip_code,
                $lead->property_location,
                $lead->created_at,
            ]);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}
