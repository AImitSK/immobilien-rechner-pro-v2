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
     * @param string $mode Optional mode override ('rental', 'comparison', 'sale_value')
     * @return string|false Path to generated PDF or false on failure
     */
    public static function create(array $data, $mode = null) {
        // Load DOMPDF
        if (!self::load_dompdf()) {
            error_log('[IRP PDF] Failed to load DOMPDF');
            return false;
        }

        try {
            // Determine mode from data if not provided
            if ($mode === null) {
                $calc = isset($data['calculation_data']) && is_array($data['calculation_data'])
                    ? $data['calculation_data']
                    : [];
                $mode = isset($calc['mode']) ? $calc['mode'] : 'rental';
            }

            // Build HTML based on mode
            if ($mode === 'sale_value') {
                $html = self::build_sale_value_html($data);
            } else {
                $html = self::build_html($data);
            }

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

            $filename_prefix = $mode === 'sale_value' ? 'verkaufswert' : 'immobilienbewertung';
            $filename = $filename_prefix . '-' . time() . '-' . wp_rand(1000, 9999) . '.pdf';
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

    /**
     * Build HTML for Sale Value PDF
     *
     * @param array $data Lead data
     * @return string
     */
    private static function build_sale_value_html(array $data) {
        $branding = get_option('irp_settings', []);
        $calc = isset($data['calculation_data']) && is_array($data['calculation_data'])
            ? $data['calculation_data']
            : [];
        $result = isset($calc['result']) && is_array($calc['result']) ? $calc['result'] : [];

        // Determine property type and calculation method
        $property_type = isset($calc['property_type']) ? $calc['property_type'] : 'house';
        $calculation_methods = [
            'apartment' => 'vergleichswertverfahren',
            'house' => 'sachwertverfahren',
            'land' => 'bodenwertverfahren',
        ];
        $calculation_method = isset($calculation_methods[$property_type]) ? $calculation_methods[$property_type] : 'sachwertverfahren';

        // Translate property type
        $property_type_labels = [
            'apartment' => 'Wohnung',
            'house' => 'Haus',
            'land' => 'Grundstück',
        ];
        $property_type_label = isset($property_type_labels[$property_type]) ? $property_type_labels[$property_type] : $property_type;

        // Translate house type
        $house_type_labels = [
            'single_family' => 'Einfamilienhaus',
            'multi_family' => 'Mehrfamilienhaus',
            'semi_detached' => 'Doppelhaushälfte',
            'townhouse_middle' => 'Mittelreihenhaus',
            'townhouse_end' => 'Endreihenhaus',
            'bungalow' => 'Bungalow',
        ];
        $house_type = isset($calc['house_type']) ? $calc['house_type'] : '';
        $house_type_label = isset($house_type_labels[$house_type]) ? $house_type_labels[$house_type] : '';

        // Translate quality
        $quality_labels = [
            'simple' => 'Einfach',
            'normal' => 'Normal',
            'upscale' => 'Gehoben',
            'luxury' => 'Luxuriös',
        ];
        $quality = isset($calc['quality']) ? $calc['quality'] : 'normal';
        $quality_label = isset($quality_labels[$quality]) ? $quality_labels[$quality] : $quality;

        // Translate modernization
        $modernization_labels = [
            '1-3_years' => 'Vor 1-3 Jahren',
            '4-9_years' => 'Vor 4-9 Jahren',
            '10-15_years' => 'Vor 10-15 Jahren',
            'over_15_years' => 'Vor mehr als 15 Jahren',
            'never' => 'Noch nie modernisiert',
        ];
        $modernization = isset($calc['modernization']) ? $calc['modernization'] : '';
        $modernization_label = isset($modernization_labels[$modernization]) ? $modernization_labels[$modernization] : '';

        // Get values from result
        $price_estimate = isset($result['price_estimate']) ? $result['price_estimate'] : 0;
        $price_min = isset($result['price_min']) ? $result['price_min'] : ($price_estimate * 0.95);
        $price_max = isset($result['price_max']) ? $result['price_max'] : ($price_estimate * 1.05);
        $land_value = isset($result['land_value']) ? $result['land_value'] : 0;
        $building_value = isset($result['building_value']) ? $result['building_value'] : 0;
        $features_value = isset($result['features_value']) ? $result['features_value'] : 0;
        $price_per_sqm_living = isset($result['price_per_sqm_living']) ? $result['price_per_sqm_living'] : 0;
        $price_per_sqm_land = isset($result['price_per_sqm_land']) ? $result['price_per_sqm_land'] : 0;

        // Get factors
        $factors = isset($result['factors']) ? $result['factors'] : [];
        $market_factor = isset($factors['market']) ? $factors['market'] : 1.0;
        $effective_build_year = isset($factors['effective_build_year']) ? $factors['effective_build_year'] : '';

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
            'property_type' => $property_type,
            'property_type_label' => $property_type_label,
            'living_space' => isset($calc['living_space']) ? $calc['living_space'] : (isset($calc['property_size']) ? $calc['property_size'] : ''),
            'land_size' => isset($calc['land_size']) ? $calc['land_size'] : '',
            'city_name' => isset($calc['city_name']) ? $calc['city_name'] : (isset($calc['property_location']) ? $calc['property_location'] : ''),
            'street_address' => isset($calc['street_address']) ? $calc['street_address'] : '',
            'house_type_label' => $house_type_label,
            'quality_label' => $quality_label,
            'build_year' => isset($calc['build_year']) ? $calc['build_year'] : '',
            'effective_build_year' => $effective_build_year,
            'modernization_label' => $modernization_label,
            'location_rating' => isset($calc['location_rating']) ? (int) $calc['location_rating'] : 3,
            'features' => isset($calc['features']) ? self::translate_sale_features($calc['features']) : [],
            'price_estimate' => self::format_currency($price_estimate),
            'price_min' => self::format_currency($price_min),
            'price_max' => self::format_currency($price_max),
            'price_per_sqm_living' => $price_per_sqm_living > 0 ? self::format_currency($price_per_sqm_living) : '',
            'price_per_sqm_land' => $price_per_sqm_land > 0 ? self::format_currency($price_per_sqm_land) : '',
            'land_value' => self::format_currency($land_value),
            'building_value' => self::format_currency($building_value),
            'features_value' => self::format_currency($features_value),
            'market_factor' => $market_factor,
            'calculation_method' => $calculation_method,
            'date' => date_i18n('d.m.Y'),
        ];

        // Build disclaimer
        $vars['disclaimer'] = 'Diese Schätzung dient nur zur Orientierung und ersetzt keine professionelle Immobilienbewertung. Der tatsächliche Verkaufspreis kann aufgrund individueller Objektmerkmale, aktueller Marktbedingungen und Verhandlungen abweichen. Für eine verbindliche Bewertung empfehlen wir die Konsultation eines Sachverständigen.';

        // Check for template file
        $template_path = IRP_PLUGIN_DIR . 'includes/templates/pdf-sale-value.php';
        if (file_exists($template_path)) {
            ob_start();
            extract($vars);
            include $template_path;
            return ob_get_clean();
        }

        // Fallback to simple HTML if template doesn't exist
        return self::build_sale_value_fallback_html($vars);
    }

    /**
     * Translate sale value features
     *
     * @param array $features Feature keys
     * @return array Translated feature names
     */
    private static function translate_sale_features($features) {
        if (!is_array($features) || empty($features)) {
            return [];
        }

        $translations = [
            // Exterior
            'balcony' => 'Balkon',
            'terrace' => 'Terrasse',
            'garden' => 'Garten',
            'garage' => 'Garage',
            'parking' => 'Stellplatz',
            'solar' => 'Solaranlage',
            // Interior
            'fitted_kitchen' => 'Einbauküche',
            'elevator' => 'Aufzug',
            'cellar' => 'Keller',
            'attic' => 'Dachboden',
            'fireplace' => 'Kamin',
            'parquet' => 'Parkettboden',
        ];

        $translated = [];
        foreach ($features as $feature) {
            $translated[] = isset($translations[$feature]) ? $translations[$feature] : $feature;
        }
        return $translated;
    }

    /**
     * Build fallback HTML for sale value PDF
     *
     * @param array $vars Template variables
     * @return string
     */
    private static function build_sale_value_fallback_html(array $vars) {
        $html = '<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12pt; color: #333; margin: 0; padding: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { max-width: ' . $vars['logo_width'] . 'px; margin-bottom: 20px; }
        h1 { color: ' . $vars['primary_color'] . '; font-size: 22pt; margin-bottom: 5px; }
        .subtitle { color: #666; font-size: 11pt; }
        .result-box { background: #f8fafc; border: 2px solid ' . $vars['primary_color'] . '; border-radius: 10px; padding: 25px; text-align: center; margin: 25px 0; }
        .result-range { font-size: 11pt; color: #374151; margin-bottom: 5px; }
        .result-value { font-size: 30pt; font-weight: bold; color: ' . $vars['primary_color'] . '; }
        .result-label { font-size: 10pt; color: #666; margin-top: 5px; }
        .breakdown { background: #f8fafc; border-radius: 6px; padding: 15px; margin: 20px 0; }
        .breakdown h3 { color: ' . $vars['primary_color'] . '; font-size: 11pt; margin: 0 0 10px 0; }
        .breakdown table { width: 100%; border-collapse: collapse; }
        .breakdown td { padding: 5px 0; font-size: 10pt; }
        .breakdown td:last-child { text-align: right; font-weight: 500; }
        .details { margin: 20px 0; }
        .details h3 { color: ' . $vars['primary_color'] . '; font-size: 11pt; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; }
        .details table { width: 100%; border-collapse: collapse; }
        .details td { padding: 6px 0; border-bottom: 1px solid #f3f4f6; font-size: 10pt; }
        .details td:first-child { color: #666; width: 35%; }
        .disclaimer { background: #fef9e7; border-left: 3px solid #f59e0b; padding: 12px; margin: 20px 0; font-size: 9pt; color: #78350f; }
        .footer { position: fixed; bottom: 30px; left: 40px; right: 40px; text-align: center; font-size: 8pt; color: #666; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">';

        if (!empty($vars['logo_base64'])) {
            $html .= '<img src="' . $vars['logo_base64'] . '" class="logo" alt="Logo">';
        }

        $html .= '<h1>Verkaufswert-Schätzung</h1>
        <div class="subtitle">' . esc_html($vars['property_type_label']) . ' in ' . esc_html($vars['city_name']) . '</div>
    </div>

    <div class="result-box">
        <div class="result-range">' . $vars['price_min'] . ' – ' . $vars['price_max'] . '</div>
        <div class="result-value">' . $vars['price_estimate'] . '</div>
        <div class="result-label">Mittelwert</div>
    </div>';

        // Show breakdown for houses
        if ($vars['property_type'] === 'house') {
            $html .= '
    <div class="breakdown">
        <h3>Wertermittlung</h3>
        <table>
            <tr><td>Grundstückswert</td><td>' . $vars['land_value'] . '</td></tr>
            <tr><td>Gebäudewert</td><td>' . $vars['building_value'] . '</td></tr>';
            if (!empty($vars['features_value']) && $vars['features_value'] !== '0 €') {
                $html .= '<tr><td>Ausstattung</td><td>+' . $vars['features_value'] . '</td></tr>';
            }
            $html .= '<tr><td>Marktanpassung</td><td>×' . number_format($vars['market_factor'], 2, ',', '.') . '</td></tr>
        </table>
    </div>';
        }

        $html .= '
    <div class="details">
        <h3>Objektdaten</h3>
        <table>
            <tr><td>Objekttyp</td><td>' . esc_html($vars['property_type_label']);
        if (!empty($vars['house_type_label'])) {
            $html .= ' (' . esc_html($vars['house_type_label']) . ')';
        }
        $html .= '</td></tr>';

        if (!empty($vars['living_space'])) {
            $html .= '<tr><td>Wohnfläche</td><td>' . esc_html($vars['living_space']) . ' m²</td></tr>';
        }
        if (!empty($vars['land_size'])) {
            $html .= '<tr><td>Grundstücksfläche</td><td>' . esc_html($vars['land_size']) . ' m²</td></tr>';
        }

        $html .= '<tr><td>Standort</td><td>' . esc_html($vars['city_name']);
        if (!empty($vars['street_address'])) {
            $html .= ', ' . esc_html($vars['street_address']);
        }
        $html .= '</td></tr>';

        if (!empty($vars['build_year'])) {
            $html .= '<tr><td>Baujahr</td><td>' . esc_html($vars['build_year']);
            if (!empty($vars['effective_build_year']) && $vars['effective_build_year'] != $vars['build_year']) {
                $html .= ' <span style="color: #666; font-size: 8pt;">(fiktiv: ' . esc_html($vars['effective_build_year']) . ')</span>';
            }
            $html .= '</td></tr>';
        }

        if (!empty($vars['quality_label'])) {
            $html .= '<tr><td>Bauqualität</td><td>' . esc_html($vars['quality_label']) . '</td></tr>';
        }

        $html .= '<tr><td>Lagebewertung</td><td>' . $vars['location_rating'] . ' von 5</td></tr>';

        if (!empty($vars['features'])) {
            $html .= '<tr><td>Ausstattung</td><td>' . esc_html(implode(', ', $vars['features'])) . '</td></tr>';
        }

        $html .= '
        </table>
    </div>

    <div class="disclaimer">
        <strong>Wichtiger Hinweis:</strong> ' . esc_html($vars['disclaimer']) . '
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
