<?php
/**
 * Admin Matrix & Data View
 */

if (!defined('ABSPATH')) {
    exit;
}

$condition_labels = [
    'new' => __('Neubau / Kernsaniert', 'immobilien-rechner-pro'),
    'renovated' => __('Renoviert', 'immobilien-rechner-pro'),
    'good' => __('Guter Zustand', 'immobilien-rechner-pro'),
    'needs_renovation' => __('Renovierungsbedürftig', 'immobilien-rechner-pro'),
];

$type_labels = [
    'apartment' => __('Wohnung', 'immobilien-rechner-pro'),
    'house' => __('Haus', 'immobilien-rechner-pro'),
    'commercial' => __('Gewerbe', 'immobilien-rechner-pro'),
];

$feature_labels = [
    'balcony' => __('Balkon', 'immobilien-rechner-pro'),
    'terrace' => __('Terrasse', 'immobilien-rechner-pro'),
    'garden' => __('Garten', 'immobilien-rechner-pro'),
    'elevator' => __('Aufzug', 'immobilien-rechner-pro'),
    'parking' => __('Stellplatz', 'immobilien-rechner-pro'),
    'garage' => __('Garage', 'immobilien-rechner-pro'),
    'cellar' => __('Keller', 'immobilien-rechner-pro'),
    'fitted_kitchen' => __('Einbauküche', 'immobilien-rechner-pro'),
    'floor_heating' => __('Fußbodenheizung', 'immobilien-rechner-pro'),
    'guest_toilet' => __('Gäste-WC', 'immobilien-rechner-pro'),
    'barrier_free' => __('Barrierefrei', 'immobilien-rechner-pro'),
];

$default_age_multipliers = [
    'before_1946' => ['name' => __('Altbau (bis 1945)', 'immobilien-rechner-pro'), 'multiplier' => 1.05, 'min_year' => null, 'max_year' => 1945],
    '1946_1959' => ['name' => __('Nachkriegsbau (1946-1959)', 'immobilien-rechner-pro'), 'multiplier' => 0.95, 'min_year' => 1946, 'max_year' => 1959],
    '1960_1979' => ['name' => __('60er/70er Jahre (1960-1979)', 'immobilien-rechner-pro'), 'multiplier' => 0.90, 'min_year' => 1960, 'max_year' => 1979],
    '1980_1989' => ['name' => __('80er Jahre (1980-1989)', 'immobilien-rechner-pro'), 'multiplier' => 0.95, 'min_year' => 1980, 'max_year' => 1989],
    '1990_1999' => ['name' => __('90er Jahre (1990-1999)', 'immobilien-rechner-pro'), 'multiplier' => 1.00, 'min_year' => 1990, 'max_year' => 1999],
    '2000_2014' => ['name' => __('2000er Jahre (2000-2014)', 'immobilien-rechner-pro'), 'multiplier' => 1.05, 'min_year' => 2000, 'max_year' => 2014],
    'from_2015' => ['name' => __('Neubau (ab 2015)', 'immobilien-rechner-pro'), 'multiplier' => 1.10, 'min_year' => 2015, 'max_year' => null],
];

// Get age multipliers from matrix (with defaults)
$age_multipliers = $matrix['age_multipliers'] ?? $default_age_multipliers;

$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'cities';

// Get cities from matrix
$cities = $matrix['cities'] ?? [];

// Get location ratings (with defaults)
$admin = new IRP_Admin();
$location_ratings = $matrix['location_ratings'] ?? $admin->get_default_location_ratings();
?>

<div class="wrap irp-admin-wrap irp-matrix-wrap">
    <h1><?php esc_html_e('Matrix & Daten', 'immobilien-rechner-pro'); ?></h1>

    <p class="description">
        <?php esc_html_e('Hier können Sie die Berechnungsgrundlagen für den Immobilien-Rechner anpassen.', 'immobilien-rechner-pro'); ?>
    </p>

    <nav class="nav-tab-wrapper irp-tabs">
        <a href="?page=irp-matrix&tab=cities" class="nav-tab <?php echo $active_tab === 'cities' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Städte', 'immobilien-rechner-pro'); ?>
        </a>
        <a href="?page=irp-matrix&tab=location" class="nav-tab <?php echo $active_tab === 'location' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Lage-Faktoren', 'immobilien-rechner-pro'); ?>
        </a>
        <a href="?page=irp-matrix&tab=multipliers" class="nav-tab <?php echo $active_tab === 'multipliers' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Multiplikatoren', 'immobilien-rechner-pro'); ?>
        </a>
        <a href="?page=irp-matrix&tab=features" class="nav-tab <?php echo $active_tab === 'features' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Ausstattung', 'immobilien-rechner-pro'); ?>
        </a>
        <a href="?page=irp-matrix&tab=global" class="nav-tab <?php echo $active_tab === 'global' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Globale Parameter', 'immobilien-rechner-pro'); ?>
        </a>
    </nav>

    <form method="post" action="options.php" class="irp-matrix-form">
        <?php settings_fields('irp_matrix_group'); ?>

        <!-- Tab: Städte -->
        <div class="irp-tab-content <?php echo $active_tab === 'cities' ? 'active' : ''; ?>" id="tab-cities">
            <div class="irp-settings-section">
                <h2><?php esc_html_e('Städte verwalten', 'immobilien-rechner-pro'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Legen Sie hier Städte an, für die der Rechner verwendet werden soll. Jede Stadt bekommt eine eindeutige ID für den Shortcode.', 'immobilien-rechner-pro'); ?>
                </p>

                <div class="irp-shortcode-hint">
                    <strong><?php esc_html_e('Shortcode-Verwendung:', 'immobilien-rechner-pro'); ?></strong>
                    <code>[immobilien_rechner city_id="STADT_ID"]</code>
                    <span class="description"><?php esc_html_e('Ohne city_id wird ein Dropdown mit allen Städten angezeigt.', 'immobilien-rechner-pro'); ?></span>
                </div>

                <table class="widefat irp-data-table irp-cities-table" id="irp-cities-table">
                    <thead>
                        <tr>
                            <th style="width: 120px;"><?php esc_html_e('Stadt-ID', 'immobilien-rechner-pro'); ?></th>
                            <th><?php esc_html_e('Name', 'immobilien-rechner-pro'); ?></th>
                            <th style="width: 130px;"><?php esc_html_e('Basis-Mietpreis', 'immobilien-rechner-pro'); ?></th>
                            <th style="width: 120px;"><?php esc_html_e('Degression', 'immobilien-rechner-pro'); ?></th>
                            <th style="width: 110px;"><?php esc_html_e('Vervielfältiger', 'immobilien-rechner-pro'); ?></th>
                            <th style="width: 70px;"><?php esc_html_e('Aktion', 'immobilien-rechner-pro'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="irp-cities-body">
                        <?php if (!empty($cities)) : ?>
                            <?php foreach ($cities as $index => $city) : ?>
                                <tr class="irp-city-row" data-index="<?php echo esc_attr($index); ?>">
                                    <td>
                                        <input type="text"
                                               name="irp_price_matrix[cities][<?php echo esc_attr($index); ?>][id]"
                                               value="<?php echo esc_attr($city['id'] ?? ''); ?>"
                                               class="regular-text irp-city-id"
                                               placeholder="z.B. muenchen"
                                               pattern="[a-z0-9_-]+"
                                               required>
                                    </td>
                                    <td>
                                        <input type="text"
                                               name="irp_price_matrix[cities][<?php echo esc_attr($index); ?>][name]"
                                               value="<?php echo esc_attr($city['name'] ?? ''); ?>"
                                               class="regular-text"
                                               placeholder="<?php esc_attr_e('Stadtname', 'immobilien-rechner-pro'); ?>"
                                               required>
                                    </td>
                                    <td>
                                        <input type="number"
                                               name="irp_price_matrix[cities][<?php echo esc_attr($index); ?>][base_price]"
                                               value="<?php echo esc_attr($city['base_price'] ?? 12.00); ?>"
                                               step="0.10"
                                               min="1"
                                               max="100"
                                               class="small-text"> €/m²
                                    </td>
                                    <td>
                                        <input type="number"
                                               name="irp_price_matrix[cities][<?php echo esc_attr($index); ?>][size_degression]"
                                               value="<?php echo esc_attr($city['size_degression'] ?? 0.20); ?>"
                                               step="0.01"
                                               min="0"
                                               max="0.5"
                                               class="small-text">
                                    </td>
                                    <td>
                                        <input type="number"
                                               name="irp_price_matrix[cities][<?php echo esc_attr($index); ?>][sale_factor]"
                                               value="<?php echo esc_attr($city['sale_factor'] ?? 25); ?>"
                                               step="0.5"
                                               min="5"
                                               max="60"
                                               class="small-text">
                                    </td>
                                    <td>
                                        <button type="button" class="button irp-remove-city" title="<?php esc_attr_e('Stadt entfernen', 'immobilien-rechner-pro'); ?>">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr class="irp-city-row" data-index="0">
                                <td>
                                    <input type="text"
                                           name="irp_price_matrix[cities][0][id]"
                                           value=""
                                           class="regular-text irp-city-id"
                                           placeholder="z.B. muenchen"
                                           pattern="[a-z0-9_-]+"
                                           required>
                                </td>
                                <td>
                                    <input type="text"
                                           name="irp_price_matrix[cities][0][name]"
                                           value=""
                                           class="regular-text"
                                           placeholder="<?php esc_attr_e('Stadtname', 'immobilien-rechner-pro'); ?>"
                                           required>
                                </td>
                                <td>
                                    <input type="number"
                                           name="irp_price_matrix[cities][0][base_price]"
                                           value="12.00"
                                           step="0.10"
                                           min="1"
                                           max="100"
                                           class="small-text"> €/m²
                                </td>
                                <td>
                                    <input type="number"
                                           name="irp_price_matrix[cities][0][size_degression]"
                                           value="0.20"
                                           step="0.01"
                                           min="0"
                                           max="0.5"
                                           class="small-text">
                                </td>
                                <td>
                                    <input type="number"
                                           name="irp_price_matrix[cities][0][sale_factor]"
                                           value="25"
                                           step="0.5"
                                           min="5"
                                           max="60"
                                           class="small-text">
                                </td>
                                <td>
                                    <button type="button" class="button irp-remove-city" title="<?php esc_attr_e('Stadt entfernen', 'immobilien-rechner-pro'); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6">
                                <button type="button" class="button button-secondary" id="irp-add-city">
                                    <span class="dashicons dashicons-plus-alt2"></span>
                                    <?php esc_html_e('Stadt hinzufügen', 'immobilien-rechner-pro'); ?>
                                </button>
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <div class="irp-city-info irp-info-box-formula">
                    <h4><?php esc_html_e('Berechnungsformel: Größendegression', 'immobilien-rechner-pro'); ?></h4>
                    <p><?php esc_html_e('Der Basis-Mietpreis bezieht sich auf eine 70 m² Referenzwohnung. Größere Wohnungen werden pro m² günstiger, kleinere teurer.', 'immobilien-rechner-pro'); ?></p>

                    <div class="irp-formula-box">
                        <code>m²-Preis = Basis-Preis × (70 / Fläche)<sup>Degression</sup></code>
                    </div>

                    <table class="irp-formula-example">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Fläche', 'immobilien-rechner-pro'); ?></th>
                                <th><?php esc_html_e('Faktor (α=0.20)', 'immobilien-rechner-pro'); ?></th>
                                <th><?php esc_html_e('Bei 10 €/m² Basis', 'immobilien-rechner-pro'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>35 m²</td>
                                <td class="irp-positive">× 1.15</td>
                                <td><strong>11.50 €/m²</strong></td>
                            </tr>
                            <tr>
                                <td>50 m²</td>
                                <td class="irp-positive">× 1.07</td>
                                <td><strong>10.70 €/m²</strong></td>
                            </tr>
                            <tr class="irp-highlight-row">
                                <td>70 m² <em>(Referenz)</em></td>
                                <td>× 1.00</td>
                                <td><strong>10.00 €/m²</strong></td>
                            </tr>
                            <tr>
                                <td>100 m²</td>
                                <td class="irp-negative">× 0.93</td>
                                <td><strong>9.30 €/m²</strong></td>
                            </tr>
                            <tr>
                                <td>140 m²</td>
                                <td class="irp-negative">× 0.87</td>
                                <td><strong>8.70 €/m²</strong></td>
                            </tr>
                            <tr>
                                <td>200 m²</td>
                                <td class="irp-negative">× 0.80</td>
                                <td><strong>8.00 €/m²</strong></td>
                            </tr>
                        </tbody>
                    </table>

                    <h4><?php esc_html_e('Weitere Hinweise:', 'immobilien-rechner-pro'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Degression 0.20 ist der Standardwert. Höhere Werte = stärkere Preisabnahme bei großen Wohnungen.', 'immobilien-rechner-pro'); ?></li>
                        <li><?php esc_html_e('Degression 0 = keine Größenanpassung (lineare Berechnung).', 'immobilien-rechner-pro'); ?></li>
                        <li><?php esc_html_e('Die Stadt-ID muss eindeutig sein (nur Kleinbuchstaben, Zahlen, Bindestriche, Unterstriche).', 'immobilien-rechner-pro'); ?></li>
                        <li><?php esc_html_e('Vervielfältiger: Anzahl Jahresnettokaltmieten = Kaufpreis. Bei 1.000 €/Monat und Faktor 25 → 300.000 € Kaufpreis.', 'immobilien-rechner-pro'); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Tab: Lage-Faktoren -->
        <div class="irp-tab-content <?php echo $active_tab === 'location' ? 'active' : ''; ?>" id="tab-location">
            <div class="irp-settings-section">
                <h2><?php esc_html_e('Lage-Faktoren', 'immobilien-rechner-pro'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Konfigurieren Sie die Multiplikatoren und Beschreibungen für die 5 Lage-Stufen. Die Bewertung fließt als Multiplikator in die Mietpreisberechnung ein.', 'immobilien-rechner-pro'); ?>
                </p>

                <table class="widefat irp-data-table irp-location-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;"><?php esc_html_e('Stufe', 'immobilien-rechner-pro'); ?></th>
                            <th style="width: 180px;"><?php esc_html_e('Bezeichnung', 'immobilien-rechner-pro'); ?></th>
                            <th style="width: 100px;"><?php esc_html_e('Faktor', 'immobilien-rechner-pro'); ?></th>
                            <th style="width: 100px;"><?php esc_html_e('Auswirkung', 'immobilien-rechner-pro'); ?></th>
                            <th><?php esc_html_e('Beschreibung', 'immobilien-rechner-pro'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($level = 1; $level <= 5; $level++) :
                            $rating = $location_ratings[$level] ?? [];
                            $multiplier = $rating['multiplier'] ?? 1.00;
                            $impact = ($multiplier - 1) * 100;
                            $stars = str_repeat('★', $level);
                        ?>
                            <tr class="irp-location-row" data-level="<?php echo esc_attr($level); ?>">
                                <td class="irp-location-level">
                                    <span class="irp-stars"><?php echo $stars; ?></span>
                                </td>
                                <td>
                                    <input type="text"
                                           name="irp_price_matrix[location_ratings][<?php echo esc_attr($level); ?>][name]"
                                           value="<?php echo esc_attr($rating['name'] ?? ''); ?>"
                                           class="regular-text"
                                           required>
                                </td>
                                <td>
                                    <input type="number"
                                           name="irp_price_matrix[location_ratings][<?php echo esc_attr($level); ?>][multiplier]"
                                           value="<?php echo esc_attr($multiplier); ?>"
                                           step="0.01"
                                           min="0.5"
                                           max="2"
                                           class="small-text irp-location-multiplier">
                                </td>
                                <td>
                                    <span class="<?php echo $impact >= 0 ? 'irp-positive' : 'irp-negative'; ?> irp-location-impact">
                                        <?php echo ($impact >= 0 ? '+' : '') . number_format($impact, 0) . '%'; ?>
                                    </span>
                                </td>
                                <td>
                                    <textarea name="irp_price_matrix[location_ratings][<?php echo esc_attr($level); ?>][description]"
                                              rows="3"
                                              class="large-text irp-location-description"
                                              placeholder="<?php esc_attr_e('Eine Zeile pro Punkt...', 'immobilien-rechner-pro'); ?>"><?php echo esc_textarea($rating['description'] ?? ''); ?></textarea>
                                    <p class="description"><?php esc_html_e('Jede Zeile wird als Aufzählungspunkt angezeigt.', 'immobilien-rechner-pro'); ?></p>
                                </td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>

                <div class="irp-info-box">
                    <h4><?php esc_html_e('Hinweise:', 'immobilien-rechner-pro'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Stufe 3 (Gute Lage) sollte den Faktor 1.00 haben - dies entspricht dem Basis-Mietpreis.', 'immobilien-rechner-pro'); ?></li>
                        <li><?php esc_html_e('Einfache Lagen (Stufe 1-2) haben niedrigere Faktoren, Premium-Lagen (Stufe 4-5) höhere.', 'immobilien-rechner-pro'); ?></li>
                        <li><?php esc_html_e('Die Beschreibungen werden dem Nutzer im Frontend angezeigt, wenn er eine Lage-Stufe auswählt.', 'immobilien-rechner-pro'); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Tab: Multiplikatoren -->
        <div class="irp-tab-content <?php echo $active_tab === 'multipliers' ? 'active' : ''; ?>" id="tab-multipliers">
            <div class="irp-settings-section">
                <h2><?php esc_html_e('Zustands-Multiplikatoren', 'immobilien-rechner-pro'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Diese Faktoren werden auf den Basis-Mietpreis angewendet. Ein Wert von 1.0 bedeutet keine Änderung, 1.25 bedeutet +25%.', 'immobilien-rechner-pro'); ?>
                </p>

                <table class="widefat irp-data-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Zustand', 'immobilien-rechner-pro'); ?></th>
                            <th><?php esc_html_e('Multiplikator', 'immobilien-rechner-pro'); ?></th>
                            <th><?php esc_html_e('Auswirkung', 'immobilien-rechner-pro'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($condition_labels as $key => $label) :
                            $multiplier = $matrix['condition_multipliers'][$key] ?? 1.00;
                            $impact = ($multiplier - 1) * 100;
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html($label); ?></strong></td>
                                <td>
                                    <input type="number"
                                           name="irp_price_matrix[condition_multipliers][<?php echo esc_attr($key); ?>]"
                                           value="<?php echo esc_attr($multiplier); ?>"
                                           step="0.01"
                                           min="0.5"
                                           max="2"
                                           class="small-text">
                                </td>
                                <td>
                                    <span class="<?php echo $impact >= 0 ? 'irp-positive' : 'irp-negative'; ?>">
                                        <?php echo ($impact >= 0 ? '+' : '') . number_format($impact, 0) . '%'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="irp-settings-section">
                <h2><?php esc_html_e('Objekttyp-Multiplikatoren', 'immobilien-rechner-pro'); ?></h2>

                <table class="widefat irp-data-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Objekttyp', 'immobilien-rechner-pro'); ?></th>
                            <th><?php esc_html_e('Multiplikator', 'immobilien-rechner-pro'); ?></th>
                            <th><?php esc_html_e('Auswirkung', 'immobilien-rechner-pro'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($type_labels as $key => $label) :
                            $multiplier = $matrix['type_multipliers'][$key] ?? 1.00;
                            $impact = ($multiplier - 1) * 100;
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html($label); ?></strong></td>
                                <td>
                                    <input type="number"
                                           name="irp_price_matrix[type_multipliers][<?php echo esc_attr($key); ?>]"
                                           value="<?php echo esc_attr($multiplier); ?>"
                                           step="0.01"
                                           min="0.5"
                                           max="2"
                                           class="small-text">
                                </td>
                                <td>
                                    <span class="<?php echo $impact >= 0 ? 'irp-positive' : 'irp-negative'; ?>">
                                        <?php echo ($impact >= 0 ? '+' : '') . number_format($impact, 0) . '%'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="irp-settings-section">
                <h2><?php esc_html_e('Baualters-Multiplikatoren', 'immobilien-rechner-pro'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Diese Faktoren werden basierend auf dem Baujahr der Immobilie angewendet. Die Einteilung entspricht den typischen Baualtersklassen deutscher Mietspiegel.', 'immobilien-rechner-pro'); ?>
                </p>

                <table class="widefat irp-data-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Baualtersklasse', 'immobilien-rechner-pro'); ?></th>
                            <th><?php esc_html_e('Zeitraum', 'immobilien-rechner-pro'); ?></th>
                            <th><?php esc_html_e('Multiplikator', 'immobilien-rechner-pro'); ?></th>
                            <th><?php esc_html_e('Auswirkung', 'immobilien-rechner-pro'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($age_multipliers as $key => $data) :
                            $multiplier = (float) ($data['multiplier'] ?? 1.00);
                            $impact = ($multiplier - 1) * 100;
                            $min_year = $data['min_year'] ?? null;
                            $max_year = $data['max_year'] ?? null;

                            // Format time range
                            if ($min_year === null) {
                                $time_range = sprintf(__('bis %d', 'immobilien-rechner-pro'), $max_year);
                            } elseif ($max_year === null) {
                                $time_range = sprintf(__('ab %d', 'immobilien-rechner-pro'), $min_year);
                            } else {
                                $time_range = $min_year . ' - ' . $max_year;
                            }
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html($data['name'] ?? $key); ?></strong></td>
                                <td><?php echo esc_html($time_range); ?></td>
                                <td>
                                    <input type="number"
                                           name="irp_price_matrix[age_multipliers][<?php echo esc_attr($key); ?>][multiplier]"
                                           value="<?php echo esc_attr($multiplier); ?>"
                                           step="0.01"
                                           min="0.5"
                                           max="2"
                                           class="small-text">
                                    <!-- Hidden fields to preserve name and year ranges -->
                                    <input type="hidden"
                                           name="irp_price_matrix[age_multipliers][<?php echo esc_attr($key); ?>][name]"
                                           value="<?php echo esc_attr($data['name'] ?? ''); ?>">
                                    <input type="hidden"
                                           name="irp_price_matrix[age_multipliers][<?php echo esc_attr($key); ?>][min_year]"
                                           value="<?php echo esc_attr($min_year ?? ''); ?>">
                                    <input type="hidden"
                                           name="irp_price_matrix[age_multipliers][<?php echo esc_attr($key); ?>][max_year]"
                                           value="<?php echo esc_attr($max_year ?? ''); ?>">
                                </td>
                                <td>
                                    <span class="<?php echo $impact >= 0 ? 'irp-positive' : 'irp-negative'; ?>">
                                        <?php echo ($impact >= 0 ? '+' : '') . number_format($impact, 0) . '%'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="irp-info-box">
                    <h4><?php esc_html_e('Hinweise zu Baualtersklassen:', 'immobilien-rechner-pro'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Altbauten (bis 1945) sind oft aufgrund von Charme (hohe Decken, Stuck) beliebt und können einen Aufschlag rechtfertigen.', 'immobilien-rechner-pro'); ?></li>
                        <li><?php esc_html_e('Bauten der 60er/70er Jahre haben oft niedrigere Standards und werden typischerweise mit Abschlägen bewertet.', 'immobilien-rechner-pro'); ?></li>
                        <li><?php esc_html_e('Die 90er Jahre (1990-1999) dienen als Referenz mit Faktor 1.00.', 'immobilien-rechner-pro'); ?></li>
                        <li><?php esc_html_e('Neubauten profitieren von modernen Energiestandards und Ausstattung.', 'immobilien-rechner-pro'); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Tab: Ausstattung -->
        <div class="irp-tab-content <?php echo $active_tab === 'features' ? 'active' : ''; ?>" id="tab-features">
            <div class="irp-settings-section">
                <h2><?php esc_html_e('Ausstattungs-Zuschläge', 'immobilien-rechner-pro'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Diese Beträge werden pro m² zum Basis-Mietpreis addiert, wenn das Merkmal vorhanden ist.', 'immobilien-rechner-pro'); ?>
                </p>

                <table class="widefat irp-data-table" id="irp-features-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Ausstattungsmerkmal', 'immobilien-rechner-pro'); ?></th>
                            <th><?php esc_html_e('Zuschlag (€/m²)', 'immobilien-rechner-pro'); ?></th>
                            <th><?php esc_html_e('Bei 70m² Wohnung', 'immobilien-rechner-pro'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feature_labels as $key => $label) :
                            $premium = $matrix['feature_premiums'][$key] ?? 0.00;
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html($label); ?></strong></td>
                                <td>
                                    <input type="number"
                                           name="irp_price_matrix[feature_premiums][<?php echo esc_attr($key); ?>]"
                                           value="<?php echo esc_attr($premium); ?>"
                                           step="0.05"
                                           min="0"
                                           max="10"
                                           class="small-text irp-feature-input"> €/m²
                                </td>
                                <td>
                                    <span class="irp-feature-result irp-positive">
                                        +<?php echo number_format($premium * 70, 0, ',', '.'); ?> €/Monat
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Globale Parameter -->
        <div class="irp-tab-content <?php echo $active_tab === 'global' ? 'active' : ''; ?>" id="tab-global">
            <div class="irp-settings-section">
                <h2><?php esc_html_e('Globale Berechnungsparameter', 'immobilien-rechner-pro'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Diese Parameter werden für die Vergleichsberechnung (Verkaufen vs. Vermieten) verwendet.', 'immobilien-rechner-pro'); ?>
                </p>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="interest_rate"><?php esc_html_e('Kapitalanlage-Zinssatz', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="number"
                                   id="interest_rate"
                                   name="irp_price_matrix[interest_rate]"
                                   value="<?php echo esc_attr($matrix['interest_rate'] ?? 3.0); ?>"
                                   step="0.1"
                                   min="0"
                                   max="15"
                                   class="small-text"> %
                            <p class="description">
                                <?php esc_html_e('Angenommener Zinssatz für alternative Kapitalanlage des Verkaufserlöses.', 'immobilien-rechner-pro'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="appreciation_rate"><?php esc_html_e('Wertsteigerung Immobilie', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="number"
                                   id="appreciation_rate"
                                   name="irp_price_matrix[appreciation_rate]"
                                   value="<?php echo esc_attr($matrix['appreciation_rate'] ?? 2.0); ?>"
                                   step="0.1"
                                   min="-5"
                                   max="15"
                                   class="small-text"> %
                            <p class="description">
                                <?php esc_html_e('Angenommene jährliche Wertsteigerung der Immobilie.', 'immobilien-rechner-pro'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="rent_increase_rate"><?php esc_html_e('Jährliche Mietsteigerung', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="number"
                                   id="rent_increase_rate"
                                   name="irp_price_matrix[rent_increase_rate]"
                                   value="<?php echo esc_attr($matrix['rent_increase_rate'] ?? 2.0); ?>"
                                   step="0.1"
                                   min="0"
                                   max="10"
                                   class="small-text"> %
                            <p class="description">
                                <?php esc_html_e('Angenommene jährliche Mietsteigerung für die Prognose.', 'immobilien-rechner-pro'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Hidden fields for preserving data across tabs -->
        <?php if ($active_tab !== 'cities') : ?>
            <?php foreach ($cities as $index => $city) : ?>
                <input type="hidden" name="irp_price_matrix[cities][<?php echo esc_attr($index); ?>][id]" value="<?php echo esc_attr($city['id'] ?? ''); ?>">
                <input type="hidden" name="irp_price_matrix[cities][<?php echo esc_attr($index); ?>][name]" value="<?php echo esc_attr($city['name'] ?? ''); ?>">
                <input type="hidden" name="irp_price_matrix[cities][<?php echo esc_attr($index); ?>][base_price]" value="<?php echo esc_attr($city['base_price'] ?? 12); ?>">
                <input type="hidden" name="irp_price_matrix[cities][<?php echo esc_attr($index); ?>][size_degression]" value="<?php echo esc_attr($city['size_degression'] ?? 0.20); ?>">
                <input type="hidden" name="irp_price_matrix[cities][<?php echo esc_attr($index); ?>][sale_factor]" value="<?php echo esc_attr($city['sale_factor'] ?? 25); ?>">
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($active_tab !== 'multipliers') : ?>
            <?php foreach ($matrix['condition_multipliers'] ?? [] as $key => $mult) : ?>
                <input type="hidden" name="irp_price_matrix[condition_multipliers][<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($mult); ?>">
            <?php endforeach; ?>
            <?php foreach ($matrix['type_multipliers'] ?? [] as $key => $mult) : ?>
                <input type="hidden" name="irp_price_matrix[type_multipliers][<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($mult); ?>">
            <?php endforeach; ?>
            <?php foreach ($age_multipliers as $key => $data) : ?>
                <input type="hidden" name="irp_price_matrix[age_multipliers][<?php echo esc_attr($key); ?>][name]" value="<?php echo esc_attr($data['name'] ?? ''); ?>">
                <input type="hidden" name="irp_price_matrix[age_multipliers][<?php echo esc_attr($key); ?>][multiplier]" value="<?php echo esc_attr($data['multiplier'] ?? 1.0); ?>">
                <input type="hidden" name="irp_price_matrix[age_multipliers][<?php echo esc_attr($key); ?>][min_year]" value="<?php echo esc_attr($data['min_year'] ?? ''); ?>">
                <input type="hidden" name="irp_price_matrix[age_multipliers][<?php echo esc_attr($key); ?>][max_year]" value="<?php echo esc_attr($data['max_year'] ?? ''); ?>">
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($active_tab !== 'features') : ?>
            <?php foreach ($matrix['feature_premiums'] ?? [] as $key => $premium) : ?>
                <input type="hidden" name="irp_price_matrix[feature_premiums][<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($premium); ?>">
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($active_tab !== 'location') : ?>
            <?php foreach ($location_ratings as $level => $rating) : ?>
                <input type="hidden" name="irp_price_matrix[location_ratings][<?php echo esc_attr($level); ?>][name]" value="<?php echo esc_attr($rating['name'] ?? ''); ?>">
                <input type="hidden" name="irp_price_matrix[location_ratings][<?php echo esc_attr($level); ?>][multiplier]" value="<?php echo esc_attr($rating['multiplier'] ?? 1.0); ?>">
                <input type="hidden" name="irp_price_matrix[location_ratings][<?php echo esc_attr($level); ?>][description]" value="<?php echo esc_attr($rating['description'] ?? ''); ?>">
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($active_tab !== 'global') : ?>
            <input type="hidden" name="irp_price_matrix[interest_rate]" value="<?php echo esc_attr($matrix['interest_rate'] ?? 3.0); ?>">
            <input type="hidden" name="irp_price_matrix[appreciation_rate]" value="<?php echo esc_attr($matrix['appreciation_rate'] ?? 2.0); ?>">
            <input type="hidden" name="irp_price_matrix[rent_increase_rate]" value="<?php echo esc_attr($matrix['rent_increase_rate'] ?? 2.0); ?>">
        <?php endif; ?>

        <?php submit_button(__('Änderungen speichern', 'immobilien-rechner-pro')); ?>
    </form>
</div>
