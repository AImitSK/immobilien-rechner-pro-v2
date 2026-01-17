<?php
/**
 * Shortcode handler for embedding the calculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class IRP_Shortcode {

    public function __construct() {
        add_shortcode('immobilien_rechner', [$this, 'render_calculator']);
    }

    /**
     * Render the calculator shortcode
     *
     * Usage:
     * - [immobilien_rechner] - User can choose mode and city from dropdown
     * - [immobilien_rechner mode="rental"] - Locks to rental mode
     * - [immobilien_rechner city_id="muenchen"] - Locks to a specific city, skips location step
     * - [immobilien_rechner mode="comparison" city_id="berlin"] - Both locked
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render_calculator(array $atts = []): string {
        $atts = shortcode_atts([
            'mode' => '', // Empty means user can choose, 'rental' or 'comparison' locks to that mode
            'city_id' => '', // If set, uses this city's values and skips location step
            'theme' => 'light',
            'show_branding' => 'true',
        ], $atts, 'immobilien_rechner');

        // Validate city_id if provided
        $city = null;
        if (!empty($atts['city_id'])) {
            $calculator = new IRP_Calculator();
            $city = $calculator->get_city_by_id(sanitize_key($atts['city_id']));

            // If city not found, clear the city_id
            if (!$city) {
                $atts['city_id'] = '';
            }
        }

        // Generate unique ID for multiple instances on same page
        $instance_id = 'irp-' . wp_generate_uuid4();

        // Build data attributes for React
        $data_attrs = [
            'data-instance-id' => $instance_id,
            'data-initial-mode' => esc_attr($atts['mode']),
            'data-city-id' => esc_attr($atts['city_id']),
            'data-city-name' => $city ? esc_attr($city['name']) : '',
            'data-theme' => esc_attr($atts['theme']),
            'data-show-branding' => esc_attr($atts['show_branding']),
        ];

        $data_string = '';
        foreach ($data_attrs as $key => $value) {
            $data_string .= sprintf(' %s="%s"', $key, $value);
        }

        // Ensure assets are loaded
        $assets = new IRP_Assets();
        $assets->enqueue_frontend_assets();

        return sprintf(
            '<div id="%s" class="irp-calculator-root"%s>
                <div class="irp-loading">
                    <div class="irp-loading-spinner"></div>
                    <p>%s</p>
                </div>
            </div>',
            esc_attr($instance_id),
            $data_string,
            esc_html__('Rechner wird geladen...', 'immobilien-rechner-pro')
        );
    }
}
