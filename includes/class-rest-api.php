<?php
/**
 * REST API endpoints for the calculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class IRP_Rest_API {
    
    private string $namespace = 'irp/v1';
    
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    public function register_routes(): void {
        // Calculate rental value
        register_rest_route($this->namespace, '/calculate/rental', [
            'methods' => 'POST',
            'callback' => [$this, 'calculate_rental'],
            'permission_callback' => '__return_true',
            'args' => $this->get_rental_args(),
        ]);
        
        // Calculate sell vs rent comparison
        register_rest_route($this->namespace, '/calculate/comparison', [
            'methods' => 'POST',
            'callback' => [$this, 'calculate_comparison'],
            'permission_callback' => '__return_true',
            'args' => $this->get_comparison_args(),
        ]);
        
        // Submit lead (legacy - full lead in one step)
        register_rest_route($this->namespace, '/leads', [
            'methods' => 'POST',
            'callback' => [$this, 'submit_lead'],
            'permission_callback' => '__return_true',
            'args' => $this->get_lead_args(),
        ]);

        // Create partial lead (property data only, no contact info)
        register_rest_route($this->namespace, '/leads/partial', [
            'methods' => 'POST',
            'callback' => [$this, 'create_partial_lead'],
            'permission_callback' => '__return_true',
            'args' => $this->get_partial_lead_args(),
        ]);

        // Complete a partial lead (add contact info)
        register_rest_route($this->namespace, '/leads/complete', [
            'methods' => 'POST',
            'callback' => [$this, 'complete_lead'],
            'permission_callback' => '__return_true',
            'args' => $this->get_complete_lead_args(),
        ]);
        
        // Get location suggestions (for autocomplete)
        register_rest_route($this->namespace, '/locations', [
            'methods' => 'GET',
            'callback' => [$this, 'get_locations'],
            'permission_callback' => '__return_true',
            'args' => [
                'search' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Get configured cities
        register_rest_route($this->namespace, '/cities', [
            'methods' => 'GET',
            'callback' => [$this, 'get_cities'],
            'permission_callback' => '__return_true',
        ]);
    }
    
    private function get_rental_args(): array {
        return [
            'property_type' => [
                'required' => true,
                'type' => 'string',
                'enum' => ['apartment', 'house', 'commercial'],
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'size' => [
                'required' => true,
                'type' => 'number',
                'minimum' => 10,
                'maximum' => 10000,
            ],
            'rooms' => [
                'required' => false,
                'type' => 'number',
                'minimum' => 1,
                'maximum' => 20,
            ],
            'city_id' => [
                'required' => false,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_key',
            ],
            'zip_code' => [
                'required' => false, // Not required when city_id is provided
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'location' => [
                'required' => false,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'condition' => [
                'required' => true,
                'type' => 'string',
                'enum' => ['new', 'renovated', 'good', 'needs_renovation'],
            ],
            'features' => [
                'required' => false,
                'type' => 'array',
                'items' => ['type' => 'string'],
                'default' => [],
            ],
            'year_built' => [
                'required' => false,
                'type' => 'integer',
                'minimum' => 1800,
                'maximum' => 2030,
            ],
            'location_rating' => [
                'required' => false,
                'type' => 'integer',
                'minimum' => 1,
                'maximum' => 5,
                'default' => 3, // "Gute Lage"
            ],
            'address' => [
                'required' => false,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ];
    }
    
    private function get_comparison_args(): array {
        return array_merge($this->get_rental_args(), [
            'property_value' => [
                'required' => true,
                'type' => 'number',
                'minimum' => 10000,
            ],
            'remaining_mortgage' => [
                'required' => false,
                'type' => 'number',
                'minimum' => 0,
                'default' => 0,
            ],
            'mortgage_rate' => [
                'required' => false,
                'type' => 'number',
                'minimum' => 0,
                'maximum' => 15,
                'default' => 3.5,
            ],
            'holding_period_years' => [
                'required' => false,
                'type' => 'integer',
                'minimum' => 0,
                'maximum' => 50,
            ],
            'expected_appreciation' => [
                'required' => false,
                'type' => 'number',
                'minimum' => -10,
                'maximum' => 20,
                'default' => 2,
            ],
        ]);
    }
    
    private function get_lead_args(): array {
        return [
            'name' => [
                'required' => false,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'email' => [
                'required' => true,
                'type' => 'string',
                'format' => 'email',
                'sanitize_callback' => 'sanitize_email',
            ],
            'phone' => [
                'required' => false,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'mode' => [
                'required' => true,
                'type' => 'string',
                'enum' => ['rental', 'comparison'],
            ],
            'calculation_data' => [
                'required' => false,
                'type' => 'object',
            ],
            'consent' => [
                'required' => true,
                'type' => 'boolean',
            ],
        ];
    }

    private function get_partial_lead_args(): array {
        return [
            'mode' => [
                'required' => true,
                'type' => 'string',
                'enum' => ['rental', 'comparison'],
            ],
            'property_type' => [
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'property_size' => [
                'required' => true,
                'type' => 'number',
            ],
            'city_id' => [
                'required' => false,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_key',
            ],
            'city_name' => [
                'required' => false,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'condition' => [
                'required' => false,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'location_rating' => [
                'required' => false,
                'type' => 'integer',
                'default' => 3,
            ],
            'features' => [
                'required' => false,
                'type' => 'array',
                'default' => [],
            ],
            'calculation_result' => [
                'required' => false,
                'type' => 'object',
            ],
        ];
    }

    private function get_complete_lead_args(): array {
        return [
            'lead_id' => [
                'required' => true,
                'type' => 'integer',
            ],
            'name' => [
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'email' => [
                'required' => true,
                'type' => 'string',
                'format' => 'email',
                'sanitize_callback' => 'sanitize_email',
            ],
            'phone' => [
                'required' => false,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'consent' => [
                'required' => true,
                'type' => 'boolean',
            ],
            'newsletter_consent' => [
                'required' => false,
                'type' => 'boolean',
                'default' => false,
            ],
            'recaptcha_token' => [
                'required' => false,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ];
    }
    
    public function calculate_rental(\WP_REST_Request $request): \WP_REST_Response {
        $calculator = new IRP_Calculator();
        $result = $calculator->calculate_rental_value($request->get_params());
        
        // Store calculation (anonymous)
        $this->store_calculation('rental', $request->get_params(), $result);
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $result,
        ]);
    }
    
    public function calculate_comparison(\WP_REST_Request $request): \WP_REST_Response {
        $calculator = new IRP_Calculator();
        $result = $calculator->calculate_comparison($request->get_params());
        
        // Store calculation (anonymous)
        $this->store_calculation('comparison', $request->get_params(), $result);
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $result,
        ]);
    }
    
    public function submit_lead(\WP_REST_Request $request): \WP_REST_Response {
        if (!$request->get_param('consent')) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Die Einwilligung ist erforderlich, um Ihre Daten zu übermitteln.', 'immobilien-rechner-pro'),
            ], 400);
        }
        
        $leads = new IRP_Leads();
        $lead_id = $leads->create($request->get_params());
        
        if (is_wp_error($lead_id)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => $lead_id->get_error_message(),
            ], 400);
        }
        
        // Send notification email to admin
        $leads->send_notification($lead_id);
        
        return new \WP_REST_Response([
            'success' => true,
            'message' => __('Vielen Dank! Wir werden uns in Kürze bei Ihnen melden.', 'immobilien-rechner-pro'),
            'lead_id' => $lead_id,
        ]);
    }

    /**
     * Create a partial lead (property data only, no contact info yet)
     */
    public function create_partial_lead(\WP_REST_Request $request): \WP_REST_Response {
        error_log('[IRP] create_partial_lead called');

        $leads = new IRP_Leads();
        $params = $request->get_params();

        error_log('[IRP] Params: ' . print_r($params, true));

        $lead_id = $leads->create_partial($params);

        error_log('[IRP] Lead ID result: ' . print_r($lead_id, true));

        if (is_wp_error($lead_id)) {
            error_log('[IRP] Error: ' . $lead_id->get_error_message());
            return new \WP_REST_Response([
                'success' => false,
                'message' => $lead_id->get_error_message(),
            ], 400);
        }

        error_log('[IRP] Success, returning lead_id: ' . $lead_id);

        return new \WP_REST_Response([
            'success' => true,
            'lead_id' => $lead_id,
            'message' => __('Daten gespeichert.', 'immobilien-rechner-pro'),
        ]);
    }

    /**
     * Complete a partial lead (add contact info)
     */
    public function complete_lead(\WP_REST_Request $request): \WP_REST_Response {
        // Verify reCAPTCHA if configured
        $recaptcha = new IRP_Recaptcha();
        $token = $request->get_param('recaptcha_token');

        if ($recaptcha->is_configured()) {
            $verification = $recaptcha->verify($token, 'submit_lead');

            if (!$verification['success']) {
                return new \WP_REST_Response([
                    'success' => false,
                    'message' => $verification['error'] ?? __('reCAPTCHA-Validierung fehlgeschlagen.', 'immobilien-rechner-pro'),
                ], 400);
            }
        }

        // Validate consent
        if (!$request->get_param('consent')) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Die Einwilligung ist erforderlich.', 'immobilien-rechner-pro'),
            ], 400);
        }

        $leads = new IRP_Leads();
        $lead_id = (int) $request->get_param('lead_id');

        // Check if lead exists and is partial
        $lead = $leads->get($lead_id);
        if (!$lead) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Lead nicht gefunden.', 'immobilien-rechner-pro'),
            ], 404);
        }

        if ($lead->status !== 'partial') {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Dieser Lead wurde bereits vervollständigt.', 'immobilien-rechner-pro'),
            ], 400);
        }

        // Complete the lead
        $result = $leads->complete($lead_id, [
            'name' => $request->get_param('name'),
            'email' => $request->get_param('email'),
            'phone' => $request->get_param('phone') ?? '',
            'consent' => $request->get_param('consent'),
            'newsletter_consent' => $request->get_param('newsletter_consent') ?? false,
            'recaptcha_score' => $verification['score'] ?? null,
        ]);

        if (is_wp_error($result)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => $result->get_error_message(),
            ], 400);
        }

        // Send notification email to admin
        $leads->send_notification($lead_id);

        // Schedule result email to lead (sent after response via shutdown function)
        IRP_Email::schedule_after_response($lead_id);

        // Sync lead to Propstack CRM
        if (IRP_Propstack::is_enabled()) {
            $propstack_result = IRP_Propstack::sync_lead($lead_id);
            if (is_wp_error($propstack_result)) {
                error_log('[IRP] Propstack sync failed: ' . $propstack_result->get_error_message());
            }
        }

        // Get updated lead with calculation data
        $updated_lead = $leads->get($lead_id);

        return new \WP_REST_Response([
            'success' => true,
            'message' => __('Vielen Dank! Ein Makler wird sich in Kürze bei Ihnen melden.', 'immobilien-rechner-pro'),
            'lead_id' => $lead_id,
            'calculation_data' => $updated_lead->calculation_data ?? null,
        ]);
    }

    public function get_locations(\WP_REST_Request $request): \WP_REST_Response {
        $search = $request->get_param('search');
        
        // This is a placeholder - in production, you'd integrate with a
        // geocoding API or use a local database of German cities/zip codes
        $locations = $this->search_locations($search);
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $locations,
        ]);
    }
    
    private function store_calculation(string $mode, array $input, array $result): void {
        global $wpdb;
        
        $session_id = $_COOKIE['irp_session'] ?? wp_generate_uuid4();
        
        $wpdb->insert(
            $wpdb->prefix . 'irp_calculations',
            [
                'session_id' => $session_id,
                'mode' => $mode,
                'input_data' => wp_json_encode($input),
                'result_data' => wp_json_encode($result),
            ],
            ['%s', '%s', '%s', '%s']
        );
    }
    
    private function search_locations(string $search): array {
        // Placeholder implementation - returns some German cities
        // In production, integrate with a proper geocoding service
        $cities = [
            ['zip' => '10115', 'city' => 'Berlin', 'state' => 'Berlin'],
            ['zip' => '80331', 'city' => 'München', 'state' => 'Bayern'],
            ['zip' => '20095', 'city' => 'Hamburg', 'state' => 'Hamburg'],
            ['zip' => '50667', 'city' => 'Köln', 'state' => 'Nordrhein-Westfalen'],
            ['zip' => '60311', 'city' => 'Frankfurt am Main', 'state' => 'Hessen'],
            ['zip' => '70173', 'city' => 'Stuttgart', 'state' => 'Baden-Württemberg'],
            ['zip' => '40213', 'city' => 'Düsseldorf', 'state' => 'Nordrhein-Westfalen'],
        ];

        $search_lower = strtolower($search);

        return array_filter($cities, function($city) use ($search_lower) {
            return str_contains(strtolower($city['city']), $search_lower) ||
                   str_starts_with($city['zip'], $search);
        });
    }

    /**
     * Get all configured cities
     */
    public function get_cities(\WP_REST_Request $request): \WP_REST_Response {
        $calculator = new IRP_Calculator();
        $cities = $calculator->get_cities();

        return new \WP_REST_Response([
            'success' => true,
            'data' => $cities,
        ]);
    }
}
