<?php
/**
 * Handles loading of frontend and admin assets
 */

if (!defined('ABSPATH')) {
    exit;
}

class IRP_Assets {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'register_frontend_assets']);
    }
    
    public function register_frontend_assets(): void {
        // Only load if shortcode is present (optimization)
        global $post;
        if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'immobilien_rechner')) {
            return;
        }
        
        $this->enqueue_frontend_assets();
    }
    
    public function enqueue_frontend_assets(): void {
        $asset_file = IRP_PLUGIN_DIR . 'build/index.asset.php';

        if (file_exists($asset_file)) {
            $assets = include $asset_file;
            $dependencies = $assets['dependencies'] ?? [];
            $version = $assets['version'] ?? IRP_VERSION;
        } else {
            $dependencies = ['wp-element', 'wp-components', 'wp-i18n'];
            $version = IRP_VERSION;
        }

        // Load Google Maps API if API key is configured
        $settings = get_option('irp_settings', []);
        $google_maps_api_key = $settings['google_maps_api_key'] ?? '';

        if (!empty($google_maps_api_key)) {
            wp_enqueue_script(
                'google-maps',
                'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($google_maps_api_key) . '&libraries=places&callback=Function.prototype',
                [],
                null,
                true
            );
            $dependencies[] = 'google-maps';
        }

        wp_enqueue_script(
            'irp-calculator',
            IRP_PLUGIN_URL . 'build/index.js',
            $dependencies,
            $version,
            true
        );

        wp_enqueue_style(
            'irp-calculator',
            IRP_PLUGIN_URL . 'build/index.css',
            [],
            $version
        );
        
        // Get location ratings from matrix
        $matrix = get_option('irp_price_matrix', []);
        $admin = new IRP_Admin();
        $location_ratings = $matrix['location_ratings'] ?? $admin->get_default_location_ratings();

        wp_localize_script('irp-calculator', 'irpSettings', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('irp/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'pluginUrl' => IRP_PLUGIN_URL,
            'settings' => [
                'primaryColor' => $settings['primary_color'] ?? '#2563eb',
                'secondaryColor' => $settings['secondary_color'] ?? '#1e40af',
                'companyName' => $settings['company_name'] ?? '',
                'companyLogo' => $settings['company_logo'] ?? '',
                'requireConsent' => $settings['require_consent'] ?? true,
                'privacyPolicyUrl' => $settings['privacy_policy_url'] ?? '',
                'defaultMaintenanceRate' => $settings['default_maintenance_rate'] ?? 1.5,
                'defaultVacancyRate' => $settings['default_vacancy_rate'] ?? 3,
                'googleMapsApiKey' => $google_maps_api_key,
                'showMapInLocationStep' => !empty($settings['show_map_in_location_step']),
                'calculatorMaxWidth' => (int) ($settings['calculator_max_width'] ?? 680),
                'recaptchaSiteKey' => $settings['recaptcha_site_key'] ?? '',
                'gadsConversionId' => $settings['gads_conversion_id'] ?? '',
                'gadsPartialLabel' => $settings['gads_partial_label'] ?? '',
                'gadsCompleteLabel' => $settings['gads_complete_label'] ?? '',
            ],
            'locationRatings' => $location_ratings,
            'i18n' => [
                'currency' => '€',
                'locale' => get_locale(),
            ]
        ]);
    }
}
