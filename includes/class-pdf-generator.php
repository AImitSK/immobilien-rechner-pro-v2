<?php
/**
 * PDF Generator using DOMPDF
 * Creates professional PDF documents with calculation results
 */

if (!defined('ABSPATH')) {
    exit;
}

class IRP_PDF_Generator {

    /**
     * Create PDF from lead data
     *
     * @param array $data Lead data with calculation_data
     * @return string|false Path to generated PDF or false on failure
     */
    public static function create(array $data) {
        // Load DOMPDF
        if (!self::load_dompdf()) {
            error_log('[IRP PDF] Failed to load DOMPDF');
            return false;
        }

        try {
            $html = self::build_html($data);

            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', false);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');

            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Save to temp file
            $upload_dir = wp_upload_dir();
            $pdf_dir = $upload_dir['basedir'] . '/irp-pdfs';

            if (!file_exists($pdf_dir)) {
                wp_mkdir_p($pdf_dir);
            }

            $filename = 'immobilienbewertung-' . time() . '-' . wp_rand(1000, 9999) . '.pdf';
            $filepath = $pdf_dir . '/' . $filename;

            file_put_contents($filepath, $dompdf->output());

            return $filepath;

        } catch (\Exception $e) {
            error_log('[IRP PDF] Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Load DOMPDF library
     *
     * @return bool
     */
    private static function load_dompdf() {
        $autoload = IRP_PLUGIN_DIR . 'vendor/autoload.php';

        if (!file_exists($autoload)) {
            return false;
        }

        require_once $autoload;

        return class_exists('Dompdf\Dompdf');
    }

    /**
     * Build HTML for PDF
     *
     * @param array $data Lead data
     * @return string
     */
    private static function build_html(array $data) {
        $branding = get_option('irp_settings', []);
        $calc = isset($data['calculation_data']) && is_array($data['calculation_data'])
            ? $data['calculation_data']
            : [];
        $result = isset($calc['result']) && is_array($calc['result']) ? $calc['result'] : [];

        // Prepare template variables
        $vars = [
            'logo_base64' => self::get_logo_base64($branding),
            'logo_width' => isset($branding['company_logo_width']) ? (int) $branding['company_logo_width'] : 150,
            'primary_color' => isset($branding['primary_color']) ? $branding['primary_color'] : '#2563eb',
            'company_name' => isset($branding['company_name']) ? $branding['company_name'] : '',
            'company_name_2' => isset($branding['company_name_2']) ? $branding['company_name_2'] : '',
            'company_name_3' => isset($branding['company_name_3']) ? $branding['company_name_3'] : '',
            'company_address' => self::build_address($branding),
            'company_phone' => isset($branding['company_phone']) ? $branding['company_phone'] : '',
            'company_email' => isset($branding['company_email']) ? $branding['company_email'] : '',
            'lead_name' => isset($data['name']) ? $data['name'] : '',
            'property_type' => self::translate_type(isset($calc['property_type']) ? $calc['property_type'] : ''),
            'property_size' => isset($calc['size']) ? $calc['size'] : '',
            'city_name' => isset($calc['city_name']) ? $calc['city_name'] : '',
            'address' => isset($calc['address']) ? $calc['address'] : '',
            'condition' => self::translate_condition(isset($calc['condition']) ? $calc['condition'] : ''),
            'location_rating' => isset($calc['location_rating']) ? (int) $calc['location_rating'] : 3,
            'monthly_rent' => self::format_currency(self::get_rent_value($result)),
            'rent_min' => self::format_currency(self::get_rent_min($result)),
            'rent_max' => self::format_currency(self::get_rent_max($result)),
            'price_per_sqm' => self::format_price_per_sqm($result),
            'features' => isset($calc['features']) ? self::translate_features($calc['features']) : [],
            'date' => date_i18n('d.m.Y'),
        ];

        // Build disclaimer with company name
        $vars['disclaimer'] = sprintf(
            'Diese Einschätzung beruht auf Ihren Angaben und sollte gemeinsam mit einem Immobilienexperten von %s überprüft werden.',
            !empty($vars['company_name']) ? $vars['company_name'] : 'uns'
        );

        // Check for template file
        $template_path = IRP_PLUGIN_DIR . 'includes/templates/pdf.php';
        if (file_exists($template_path)) {
            ob_start();
            extract($vars);
            include $template_path;
            return ob_get_clean();
        }

        // Fallback simple HTML
        return self::build_fallback_html($vars);
    }

    /**
     * Get logo as Base64 data URI
     *
     * @param array $branding Branding settings
     * @return string Base64 data URI or empty string
     */
    private static function get_logo_base64(array $branding) {
        if (empty($branding['company_logo'])) {
            return '';
        }

        $logo_url = $branding['company_logo'];

        // Convert URL to local path
        $upload_dir = wp_upload_dir();
        $logo_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $logo_url);

        // Check if file exists locally
        if (!file_exists($logo_path)) {
            // Try to download if it's an external URL
            $response = wp_remote_get($logo_url, ['timeout' => 5]);
            if (is_wp_error($response)) {
                error_log('[IRP PDF] Failed to download logo: ' . $response->get_error_message());
                return '';
            }
            $image_data = wp_remote_retrieve_body($response);
            $content_type = wp_remote_retrieve_header($response, 'content-type');
        } else {
            $image_data = file_get_contents($logo_path);
            $mime_type = wp_check_filetype($logo_path);
            $content_type = $mime_type['type'];
        }

        if (empty($image_data)) {
            return '';
        }

        // Check if it's an SVG - DOMPDF has limited SVG support
        if (self::is_svg($logo_path, $content_type)) {
            // Try to convert SVG to PNG if possible
            $png_data = self::convert_svg_to_png($logo_path, $image_data);
            if ($png_data) {
                return 'data:image/png;base64,' . base64_encode($png_data);
            }
            error_log('[IRP PDF] SVG logo could not be converted - logo skipped. Please use PNG or JPG format.');
            return '';
        }

        return 'data:' . $content_type . ';base64,' . base64_encode($image_data);
    }

    /**
     * Check if file is SVG
     *
     * @param string $path File path
     * @param string $content_type MIME type
     * @return bool
     */
    private static function is_svg($path, $content_type) {
        if (strpos($content_type, 'svg') !== false) {
            return true;
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return $ext === 'svg';
    }

    /**
     * Try to convert SVG to PNG using Imagick or GD
     *
     * @param string $path File path
     * @param string $svg_data SVG content
     * @return string|false PNG data or false on failure
     */
    private static function convert_svg_to_png($path, $svg_data) {
        // Try Imagick first (better SVG support)
        if (extension_loaded('imagick')) {
            try {
                $imagick = new \Imagick();
                $imagick->readImageBlob($svg_data);
                $imagick->setImageFormat('png');
                $imagick->setImageBackgroundColor('transparent');
                $imagick->resizeImage(300, 0, \Imagick::FILTER_LANCZOS, 1);
                $png_data = $imagick->getImageBlob();
                $imagick->clear();
                $imagick->destroy();
                return $png_data;
            } catch (\Exception $e) {
                error_log('[IRP PDF] Imagick SVG conversion failed: ' . $e->getMessage());
            }
        }

        // Note: GD doesn't support SVG natively
        return false;
    }

    /**
     * Build address string
     *
     * @param array $branding Branding settings
     * @return string
     */
    private static function build_address(array $branding) {
        $parts = [];

        if (!empty($branding['company_street'])) {
            $parts[] = $branding['company_street'];
        }

        $city_part = trim(
            (isset($branding['company_zip']) ? $branding['company_zip'] : '') . ' ' .
            (isset($branding['company_city']) ? $branding['company_city'] : '')
        );

        if (!empty($city_part)) {
            $parts[] = $city_part;
        }

        return implode(', ', $parts);
    }

    /**
     * Translate property type
     *
     * @param string $type Property type
     * @return string
     */
    private static function translate_type($type) {
        $types = [
            'apartment' => 'Wohnung',
            'house' => 'Haus',
            'commercial' => 'Gewerbe',
        ];
        return isset($types[$type]) ? $types[$type] : $type;
    }

    /**
     * Translate condition
     *
     * @param string $condition Condition
     * @return string
     */
    private static function translate_condition($condition) {
        $conditions = [
            'new' => 'Neubau',
            'renovated' => 'Renoviert',
            'good' => 'Gut',
            'needs_renovation' => 'Renovierungsbedürftig',
        ];
        return isset($conditions[$condition]) ? $conditions[$condition] : $condition;
    }

    /**
     * Translate features array
     *
     * @param array $features Feature keys
     * @return array Translated feature names
     */
    private static function translate_features($features) {
        if (!is_array($features) || empty($features)) {
            return [];
        }

        $translations = [
            'balcony' => 'Balkon',
            'terrace' => 'Terrasse',
            'garden' => 'Garten',
            'elevator' => 'Aufzug',
            'parking' => 'Stellplatz',
            'garage' => 'Garage',
            'cellar' => 'Keller',
            'fitted_kitchen' => 'Einbauküche',
            'floor_heating' => 'Fußbodenheizung',
            'guest_toilet' => 'Gäste-WC',
            'barrier_free' => 'Barrierefrei',
        ];

        $translated = [];
        foreach ($features as $feature) {
            $translated[] = isset($translations[$feature]) ? $translations[$feature] : $feature;
        }
        return $translated;
    }

    /**
     * Get rent value from result
     *
     * @param array $result Calculation result
     * @return float
     */
    private static function get_rent_value($result) {
        if (isset($result['monthly_rent']['estimate'])) {
            return (float) $result['monthly_rent']['estimate'];
        }
        if (isset($result['monthly_rent']) && is_numeric($result['monthly_rent'])) {
            return (float) $result['monthly_rent'];
        }
        return 0;
    }

    /**
     * Get rent min from result
     *
     * @param array $result Calculation result
     * @return float
     */
    private static function get_rent_min($result) {
        if (isset($result['monthly_rent']['min'])) {
            return (float) $result['monthly_rent']['min'];
        }
        return self::get_rent_value($result) * 0.9;
    }

    /**
     * Get rent max from result
     *
     * @param array $result Calculation result
     * @return float
     */
    private static function get_rent_max($result) {
        if (isset($result['monthly_rent']['max'])) {
            return (float) $result['monthly_rent']['max'];
        }
        return self::get_rent_value($result) * 1.1;
    }

    /**
     * Format currency
     *
     * @param float $value Value
     * @return string
     */
    private static function format_currency($value) {
        return number_format($value, 0, ',', '.') . ' €';
    }

    /**
     * Format price per sqm
     *
     * @param array $result Calculation result
     * @return string
     */
    private static function format_price_per_sqm($result) {
        if (isset($result['price_per_sqm'])) {
            return number_format((float) $result['price_per_sqm'], 2, ',', '.') . ' €/m²';
        }
        return '-';
    }

    /**
     * Build fallback HTML if template doesn't exist
     *
     * @param array $vars Template variables
     * @return string
     */
    private static function build_fallback_html(array $vars) {
        $html = '<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12pt; color: #333; margin: 0; padding: 40px; }
        .header { text-align: center; margin-bottom: 40px; }
        .logo { max-width: ' . $vars['logo_width'] . 'px; margin-bottom: 20px; }
        h1 { color: ' . $vars['primary_color'] . '; font-size: 24pt; margin-bottom: 10px; }
        .result-box { background: #f8fafc; border: 2px solid ' . $vars['primary_color'] . '; border-radius: 8px; padding: 30px; text-align: center; margin: 30px 0; }
        .result-value { font-size: 36pt; font-weight: bold; color: ' . $vars['primary_color'] . '; }
        .result-range { color: #666; margin-top: 10px; }
        .details { margin: 30px 0; }
        .details table { width: 100%; border-collapse: collapse; }
        .details td { padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .details td:first-child { color: #666; }
        .disclaimer { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 30px 0; font-size: 10pt; }
        .footer { position: fixed; bottom: 40px; left: 40px; right: 40px; text-align: center; font-size: 9pt; color: #666; border-top: 1px solid #e5e7eb; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">';

        if (!empty($vars['logo_base64'])) {
            $html .= '<img src="' . $vars['logo_base64'] . '" class="logo" alt="Logo">';
        }

        $html .= '<h1>Immobilienbewertung</h1>
        <p>' . esc_html($vars['property_type']) . ' in ' . esc_html($vars['city_name']) . '</p>
    </div>

    <div class="result-box">
        <div style="font-size: 14pt; color: #666; margin-bottom: 10px;">Geschätzte Monatsmiete</div>
        <div class="result-value">' . $vars['monthly_rent'] . '</div>
        <div class="result-range">Spanne: ' . $vars['rent_min'] . ' - ' . $vars['rent_max'] . '</div>
        <div style="margin-top: 15px; font-size: 11pt;">(' . $vars['price_per_sqm'] . ')</div>
    </div>

    <div class="details">
        <h3>Objektdaten</h3>
        <table>
            <tr><td>Objekttyp</td><td>' . esc_html($vars['property_type']) . '</td></tr>
            <tr><td>Wohnfläche</td><td>' . esc_html($vars['property_size']) . ' m²</td></tr>
            <tr><td>Standort</td><td>' . esc_html($vars['city_name']) . (!empty($vars['address']) ? ', ' . esc_html($vars['address']) : '') . '</td></tr>
            <tr><td>Zustand</td><td>' . esc_html($vars['condition']) . '</td></tr>
            <tr><td>Lagebewertung</td><td>' . $vars['location_rating'] . ' von 5</td></tr>
        </table>
    </div>

    <div class="disclaimer">
        <strong>Hinweis:</strong> ' . esc_html($vars['disclaimer']) . '
    </div>

    <div class="footer">
        <strong>' . esc_html($vars['company_name']) . '</strong>';

        if (!empty($vars['company_name_2'])) {
            $html .= ' | ' . esc_html($vars['company_name_2']);
        }

        $html .= '<br>';

        $contact = [];
        if (!empty($vars['company_address'])) {
            $contact[] = $vars['company_address'];
        }
        if (!empty($vars['company_phone'])) {
            $contact[] = 'Tel.: ' . $vars['company_phone'];
        }
        if (!empty($vars['company_email'])) {
            $contact[] = $vars['company_email'];
        }
        $html .= esc_html(implode(' | ', $contact));

        $html .= '<br><span style="color: #999;">Erstellt am ' . $vars['date'] . '</span>
    </div>
</body>
</html>';

        return $html;
    }
}
