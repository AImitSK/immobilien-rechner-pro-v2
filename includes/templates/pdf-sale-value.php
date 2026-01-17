<?php
/**
 * PDF template for sale value results
 *
 * Available variables:
 * - $logo_base64: Base64 encoded logo image
 * - $logo_width: Logo width in pixels
 * - $primary_color: Brand primary color
 * - $company_name, $company_name_2, $company_name_3: Company name lines
 * - $company_address, $company_phone, $company_email: Contact info
 * - $lead_name: Name of the lead
 * - $property_type: 'apartment', 'house', or 'land'
 * - $property_type_label: Translated property type
 * - $living_space: Living space in m² (for apartment/house)
 * - $land_size: Land size in m² (for house/land)
 * - $city_name: City name
 * - $street_address: Street address (optional)
 * - $house_type_label: House type name (for houses)
 * - $quality_label: Quality level name
 * - $build_year: Original build year
 * - $effective_build_year: Effective build year after modernization
 * - $modernization_label: Modernization description
 * - $location_rating: 1-5 rating
 * - $features: Array of translated feature names
 * - $price_estimate: Formatted price estimate
 * - $price_min, $price_max: Formatted price range
 * - $price_per_sqm_living: Price per sqm living space
 * - $price_per_sqm_land: Price per sqm land
 * - $land_value: Formatted land value (for houses)
 * - $building_value: Formatted building value (for houses)
 * - $features_value: Formatted features value
 * - $market_factor: Market adjustment factor
 * - $calculation_method: 'sachwertverfahren', 'vergleichswertverfahren', or 'bodenwertverfahren'
 * - $date: Current date
 * - $disclaimer: Disclaimer text
 */

if (!defined('ABSPATH')) {
    exit;
}

// Determine calculation method label
$method_labels = [
    'sachwertverfahren' => 'Sachwertverfahren',
    'vergleichswertverfahren' => 'Vergleichswertverfahren',
    'bodenwertverfahren' => 'Bodenwertverfahren',
];
$method_label = $method_labels[$calculation_method] ?? 'Wertermittlung';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #1f2937;
            background: #ffffff;
        }

        .page {
            padding: 25px 40px 70px 40px;
        }

        /* Header with Logo */
        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .logo {
            max-width: <?php echo (int) $logo_width; ?>px;
            max-height: 60px;
            margin-bottom: 8px;
        }

        .document-title {
            font-size: 22pt;
            font-weight: bold;
            color: <?php echo esc_attr($primary_color); ?>;
            margin: 8px 0 2px;
        }

        .document-subtitle {
            font-size: 11pt;
            color: #6b7280;
        }

        .calculation-method {
            font-size: 9pt;
            color: #9ca3af;
            margin-top: 3px;
        }

        /* Lead greeting */
        .greeting {
            font-size: 9pt;
            margin: 15px 0;
            color: #4b5563;
            line-height: 1.5;
        }

        /* Main result box */
        .result-container {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px solid <?php echo esc_attr($primary_color); ?>;
            border-radius: 10px;
            padding: 18px 25px;
            text-align: center;
            margin: 15px 0;
        }

        .result-label {
            font-size: 9pt;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .result-range {
            font-size: 11pt;
            color: #374151;
            margin: 5px 0;
        }

        .result-value {
            font-size: 30pt;
            font-weight: bold;
            color: <?php echo esc_attr($primary_color); ?>;
            margin: 5px 0;
        }

        .result-value-label {
            font-size: 9pt;
            color: #6b7280;
            margin-top: 3px;
        }

        /* Value breakdown section (for houses) */
        .breakdown-section {
            background: #f8fafc;
            border-radius: 8px;
            padding: 12px 15px;
            margin: 12px 0;
        }

        .breakdown-title {
            font-size: 10pt;
            font-weight: bold;
            color: <?php echo esc_attr($primary_color); ?>;
            margin-bottom: 8px;
        }

        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
        }

        .breakdown-table td {
            padding: 4px 0;
            font-size: 9pt;
        }

        .breakdown-table td:first-child {
            color: #6b7280;
        }

        .breakdown-table td:last-child {
            text-align: right;
            font-weight: 500;
            color: #1f2937;
        }

        .breakdown-table tr.total {
            border-top: 1px solid #e5e7eb;
        }

        .breakdown-table tr.total td {
            padding-top: 8px;
            font-weight: bold;
            color: <?php echo esc_attr($primary_color); ?>;
        }

        /* Key metrics */
        .metrics-grid {
            display: table;
            width: 100%;
            margin: 12px 0;
        }

        .metric-item {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 10px;
            background: #f0f9ff;
            border-radius: 6px;
        }

        .metric-item:first-child {
            margin-right: 5px;
        }

        .metric-value {
            font-size: 14pt;
            font-weight: bold;
            color: <?php echo esc_attr($primary_color); ?>;
        }

        .metric-label {
            font-size: 8pt;
            color: #6b7280;
            margin-top: 2px;
        }

        /* Property details */
        .details-section {
            margin: 15px 0 12px;
        }

        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: <?php echo esc_attr($primary_color); ?>;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e5e7eb;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-table tr {
            border-bottom: 1px solid #f3f4f6;
        }

        .details-table tr:last-child {
            border-bottom: none;
        }

        .details-table td {
            padding: 6px 0;
            vertical-align: top;
            font-size: 9pt;
        }

        .details-table td:first-child {
            color: #6b7280;
            width: 35%;
        }

        .details-table td:last-child {
            font-weight: 500;
            color: #1f2937;
        }

        /* Location rating stars */
        .rating-stars {
            color: #fbbf24;
            font-size: 11pt;
        }

        .rating-stars .empty {
            color: #d1d5db;
        }

        .rating-text {
            font-size: 9pt;
            color: #6b7280;
            margin-left: 5px;
        }

        /* Features list */
        .features-list {
            display: inline;
        }

        .feature-tag {
            display: inline-block;
            background: #f0f9ff;
            color: <?php echo esc_attr($primary_color); ?>;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8pt;
            margin: 1px 2px 1px 0;
        }

        /* Factors applied */
        .factors-section {
            background: #fafafa;
            border-radius: 6px;
            padding: 10px 15px;
            margin: 12px 0;
        }

        .factors-title {
            font-size: 9pt;
            font-weight: bold;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .factors-list {
            display: inline;
        }

        .factor-badge {
            display: inline-block;
            background: #e0e7ff;
            color: #3730a3;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 8pt;
            margin: 2px 3px 2px 0;
        }

        /* Disclaimer box */
        .disclaimer {
            background: #fef9e7;
            border-left: 3px solid #f59e0b;
            border-radius: 0 6px 6px 0;
            padding: 12px 15px;
            margin: 15px 0 0;
            font-size: 8pt;
            color: #78350f;
            line-height: 1.4;
        }

        .disclaimer strong {
            display: block;
            margin-bottom: 3px;
            font-size: 9pt;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f8fafc;
            border-top: 1px solid #e5e7eb;
            padding: 10px 40px;
            text-align: center;
            font-size: 8pt;
            color: #6b7280;
            line-height: 1.4;
        }

        .footer-company {
            font-weight: bold;
            color: #374151;
            font-size: 9pt;
        }

        .footer-contact {
            margin-top: 3px;
        }

        .footer-date {
            margin-top: 4px;
            color: #9ca3af;
            font-size: 7pt;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <?php if (!empty($logo_base64)) : ?>
                <img src="<?php echo $logo_base64; ?>" class="logo" alt="Logo">
            <?php endif; ?>
            <div class="document-title">Verkaufswert-Schätzung</div>
            <div class="document-subtitle"><?php echo esc_html($property_type_label); ?> in <?php echo esc_html($city_name); ?></div>
            <div class="calculation-method">Ermittelt nach dem <?php echo esc_html($method_label); ?></div>
        </div>

        <!-- Greeting -->
        <?php if (!empty($lead_name)) : ?>
            <div class="greeting">
                Sehr geehrte/r <?php echo esc_html($lead_name); ?>,<br>
                vielen Dank für Ihr Interesse an unserer Immobilienbewertung. Basierend auf Ihren Angaben und aktuellen Marktdaten haben wir den voraussichtlichen Verkaufswert Ihrer <?php echo esc_html(strtolower($property_type_label)); ?> ermittelt.
            </div>
        <?php endif; ?>

        <!-- Result Box -->
        <div class="result-container">
            <div class="result-label">Geschätzter Verkaufspreis</div>
            <div class="result-range"><?php echo $price_min; ?> – <?php echo $price_max; ?></div>
            <div class="result-value"><?php echo $price_estimate; ?></div>
            <div class="result-value-label">Mittelwert</div>
        </div>

        <?php if ($property_type === 'house') : ?>
        <!-- Value Breakdown for Houses -->
        <div class="breakdown-section">
            <div class="breakdown-title">Wertermittlung</div>
            <table class="breakdown-table">
                <tr>
                    <td>Grundstückswert</td>
                    <td><?php echo $land_value; ?></td>
                </tr>
                <tr>
                    <td>Gebäudewert</td>
                    <td><?php echo $building_value; ?></td>
                </tr>
                <?php if (!empty($features_value) && $features_value !== '0 €') : ?>
                <tr>
                    <td>Ausstattungszuschlag</td>
                    <td>+<?php echo $features_value; ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td>Marktanpassungsfaktor</td>
                    <td>×<?php echo number_format($market_factor, 2, ',', '.'); ?></td>
                </tr>
            </table>
        </div>
        <?php endif; ?>

        <!-- Key Metrics -->
        <table class="metrics-grid">
            <tr>
                <?php if (!empty($price_per_sqm_living)) : ?>
                <td class="metric-item">
                    <div class="metric-value"><?php echo $price_per_sqm_living; ?></div>
                    <div class="metric-label">pro m² Wohnfläche</div>
                </td>
                <?php endif; ?>
                <?php if (!empty($price_per_sqm_land)) : ?>
                <td class="metric-item">
                    <div class="metric-value"><?php echo $price_per_sqm_land; ?></div>
                    <div class="metric-label">pro m² Grundstück</div>
                </td>
                <?php endif; ?>
            </tr>
        </table>

        <!-- Property Details -->
        <div class="details-section">
            <div class="section-title">Ihre Angaben im Überblick</div>
            <table class="details-table">
                <tr>
                    <td>Objekttyp</td>
                    <td>
                        <?php echo esc_html($property_type_label); ?>
                        <?php if (!empty($house_type_label)) : ?>
                            (<?php echo esc_html($house_type_label); ?>)
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if (!empty($living_space)) : ?>
                <tr>
                    <td>Wohnfläche</td>
                    <td><?php echo esc_html($living_space); ?> m²</td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($land_size)) : ?>
                <tr>
                    <td>Grundstücksfläche</td>
                    <td><?php echo esc_html($land_size); ?> m²</td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td>Standort</td>
                    <td>
                        <?php echo esc_html($city_name); ?>
                        <?php if (!empty($street_address)) : ?>, <?php echo esc_html($street_address); ?><?php endif; ?>
                    </td>
                </tr>
                <?php if (!empty($build_year)) : ?>
                <tr>
                    <td>Baujahr</td>
                    <td>
                        <?php echo esc_html($build_year); ?>
                        <?php if (!empty($effective_build_year) && $effective_build_year != $build_year) : ?>
                            <span style="color: #6b7280; font-size: 8pt;">(fiktiv: <?php echo esc_html($effective_build_year); ?>)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($modernization_label)) : ?>
                <tr>
                    <td>Letzte Modernisierung</td>
                    <td><?php echo esc_html($modernization_label); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($quality_label)) : ?>
                <tr>
                    <td>Bauqualität</td>
                    <td><?php echo esc_html($quality_label); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td>Lagebewertung</td>
                    <td>
                        <span class="rating-stars"><?php
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $location_rating ? '★' : '<span class="empty">★</span>';
                            }
                        ?></span>
                        <span class="rating-text">(<?php echo $location_rating; ?> von 5)</span>
                    </td>
                </tr>
                <?php if (!empty($features)) : ?>
                <tr>
                    <td>Ausstattung</td>
                    <td>
                        <?php foreach ($features as $feature) : ?>
                            <span class="feature-tag"><?php echo esc_html($feature); ?></span>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Applied Factors -->
        <div class="factors-section">
            <div class="factors-title">Angewandte Bewertungsfaktoren</div>
            <div class="factors-list">
                <?php if (!empty($quality_label)) : ?>
                    <span class="factor-badge">Qualität: <?php echo esc_html($quality_label); ?></span>
                <?php endif; ?>
                <span class="factor-badge">Lage: <?php echo $location_rating; ?>/5</span>
                <?php if (!empty($house_type_label)) : ?>
                    <span class="factor-badge"><?php echo esc_html($house_type_label); ?></span>
                <?php endif; ?>
                <span class="factor-badge">Markt: ×<?php echo number_format($market_factor, 2, ',', '.'); ?></span>
            </div>
        </div>

        <!-- Disclaimer -->
        <div class="disclaimer">
            <strong>Wichtiger Hinweis</strong>
            <?php echo esc_html($disclaimer); ?>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-company">
            <?php echo esc_html($company_name); ?>
            <?php if (!empty($company_name_2)) : ?>
                · <?php echo esc_html($company_name_2); ?>
            <?php endif; ?>
            <?php if (!empty($company_name_3)) : ?>
                · <?php echo esc_html($company_name_3); ?>
            <?php endif; ?>
        </div>
        <div class="footer-contact">
            <?php
            $contact_parts = [];
            if (!empty($company_address)) {
                $contact_parts[] = $company_address;
            }
            if (!empty($company_phone)) {
                $contact_parts[] = 'Tel.: ' . $company_phone;
            }
            if (!empty($company_email)) {
                $contact_parts[] = $company_email;
            }
            echo esc_html(implode(' · ', $contact_parts));
            ?>
        </div>
        <div class="footer-date">
            Erstellt am <?php echo esc_html($date); ?>
        </div>
    </div>
</body>
</html>
