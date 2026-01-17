<?php
/**
 * Shortcode Generator View
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap irp-admin-wrap irp-shortcode-generator-wrap">
    <h1><?php esc_html_e('Shortcode Generator', 'immobilien-rechner-pro'); ?></h1>

    <p class="description">
        <?php esc_html_e('Konfigurieren Sie Ihren Shortcode und kopieren Sie ihn in eine beliebige Seite oder einen Beitrag.', 'immobilien-rechner-pro'); ?>
    </p>

    <div class="irp-generator-container">
        <div class="irp-generator-options">
            <div class="irp-settings-section">
                <h2><?php esc_html_e('Modus', 'immobilien-rechner-pro'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Welche Funktionen soll der Rechner anbieten?', 'immobilien-rechner-pro'); ?>
                </p>

                <div class="irp-radio-cards">
                    <label class="irp-radio-card">
                        <input type="radio" name="irp_mode" value="" checked>
                        <div class="irp-radio-card-content">
                            <span class="irp-radio-card-icon dashicons dashicons-menu-alt3"></span>
                            <strong><?php esc_html_e('Benutzer wählt', 'immobilien-rechner-pro'); ?></strong>
                            <span class="description"><?php esc_html_e('Mietwert und Vergleich zur Auswahl', 'immobilien-rechner-pro'); ?></span>
                        </div>
                    </label>

                    <label class="irp-radio-card">
                        <input type="radio" name="irp_mode" value="rental">
                        <div class="irp-radio-card-content">
                            <span class="irp-radio-card-icon dashicons dashicons-building"></span>
                            <strong><?php esc_html_e('Nur Mietwert', 'immobilien-rechner-pro'); ?></strong>
                            <span class="description"><?php esc_html_e('Mietwert-Berechnung', 'immobilien-rechner-pro'); ?></span>
                        </div>
                    </label>

                    <label class="irp-radio-card">
                        <input type="radio" name="irp_mode" value="comparison">
                        <div class="irp-radio-card-content">
                            <span class="irp-radio-card-icon dashicons dashicons-chart-bar"></span>
                            <strong><?php esc_html_e('Nur Vergleich', 'immobilien-rechner-pro'); ?></strong>
                            <span class="description"><?php esc_html_e('Verkaufen vs. Vermieten', 'immobilien-rechner-pro'); ?></span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="irp-settings-section">
                <h2><?php esc_html_e('Stadt', 'immobilien-rechner-pro'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Soll eine bestimmte Stadt vorausgewählt werden?', 'immobilien-rechner-pro'); ?>
                </p>

                <select name="irp_city_id" id="irp-city-select" class="regular-text">
                    <option value=""><?php esc_html_e('— Benutzer wählt aus Dropdown —', 'immobilien-rechner-pro'); ?></option>
                    <?php if (!empty($cities)) : ?>
                        <?php foreach ($cities as $city) : ?>
                            <option value="<?php echo esc_attr($city['id']); ?>">
                                <?php echo esc_html($city['name']); ?> (<?php echo esc_html($city['id']); ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>

                <?php if (empty($cities)) : ?>
                    <p class="irp-warning">
                        <span class="dashicons dashicons-warning"></span>
                        <?php
                        printf(
                            esc_html__('Keine Städte konfiguriert. %sStädte hinzufügen%s', 'immobilien-rechner-pro'),
                            '<a href="' . esc_url(admin_url('admin.php?page=irp-matrix&tab=cities')) . '">',
                            '</a>'
                        );
                        ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="irp-settings-section">
                <h2><?php esc_html_e('Design', 'immobilien-rechner-pro'); ?></h2>

                <table class="form-table irp-compact-table">
                    <tr>
                        <th scope="row">
                            <label for="irp-theme"><?php esc_html_e('Farbschema', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <select name="irp_theme" id="irp-theme">
                                <option value="light"><?php esc_html_e('Hell (Light)', 'immobilien-rechner-pro'); ?></option>
                                <option value="dark"><?php esc_html_e('Dunkel (Dark)', 'immobilien-rechner-pro'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?php esc_html_e('Branding anzeigen', 'immobilien-rechner-pro'); ?>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="irp_show_branding" id="irp-show-branding" value="true" checked>
                                <?php esc_html_e('Firmenlogo und -name anzeigen', 'immobilien-rechner-pro'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="irp-generator-output">
            <div class="irp-settings-section irp-shortcode-output-section">
                <h2><?php esc_html_e('Ihr Shortcode', 'immobilien-rechner-pro'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Kopieren Sie diesen Shortcode und fügen Sie ihn in Ihre Seite ein.', 'immobilien-rechner-pro'); ?>
                </p>

                <div class="irp-shortcode-box">
                    <code id="irp-generated-shortcode">[immobilien_rechner]</code>
                    <button type="button" class="button irp-copy-btn" id="irp-copy-shortcode" title="<?php esc_attr_e('In Zwischenablage kopieren', 'immobilien-rechner-pro'); ?>">
                        <span class="dashicons dashicons-clipboard"></span>
                        <span class="irp-copy-text"><?php esc_html_e('Kopieren', 'immobilien-rechner-pro'); ?></span>
                    </button>
                </div>

                <div class="irp-copy-success" id="irp-copy-success">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e('Shortcode kopiert!', 'immobilien-rechner-pro'); ?>
                </div>
            </div>

            <div class="irp-settings-section irp-preview-info">
                <h3><?php esc_html_e('Vorschau-Info', 'immobilien-rechner-pro'); ?></h3>
                <div id="irp-preview-details">
                    <ul>
                        <li id="irp-info-mode">
                            <span class="dashicons dashicons-visibility"></span>
                            <span><?php esc_html_e('Modus: Benutzer wählt', 'immobilien-rechner-pro'); ?></span>
                        </li>
                        <li id="irp-info-city">
                            <span class="dashicons dashicons-location"></span>
                            <span><?php esc_html_e('Stadt: Benutzer wählt', 'immobilien-rechner-pro'); ?></span>
                        </li>
                        <li id="irp-info-theme">
                            <span class="dashicons dashicons-admin-appearance"></span>
                            <span><?php esc_html_e('Theme: Hell', 'immobilien-rechner-pro'); ?></span>
                        </li>
                        <li id="irp-info-branding">
                            <span class="dashicons dashicons-admin-customizer"></span>
                            <span><?php esc_html_e('Branding: Sichtbar', 'immobilien-rechner-pro'); ?></span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="irp-settings-section irp-steps-preview">
                <h3><?php esc_html_e('Ablauf für Benutzer', 'immobilien-rechner-pro'); ?></h3>
                <div class="irp-steps-flow" id="irp-steps-flow">
                    <div class="irp-step-item" id="irp-step-mode">
                        <span class="irp-step-number">1</span>
                        <span class="irp-step-label"><?php esc_html_e('Modus wählen', 'immobilien-rechner-pro'); ?></span>
                    </div>
                    <div class="irp-step-arrow">→</div>
                    <div class="irp-step-item" id="irp-step-city">
                        <span class="irp-step-number">2</span>
                        <span class="irp-step-label"><?php esc_html_e('Stadt wählen', 'immobilien-rechner-pro'); ?></span>
                    </div>
                    <div class="irp-step-arrow">→</div>
                    <div class="irp-step-item">
                        <span class="irp-step-number">3</span>
                        <span class="irp-step-label"><?php esc_html_e('Daten eingeben', 'immobilien-rechner-pro'); ?></span>
                    </div>
                    <div class="irp-step-arrow">→</div>
                    <div class="irp-step-item">
                        <span class="irp-step-number">4</span>
                        <span class="irp-step-label"><?php esc_html_e('Ergebnis', 'immobilien-rechner-pro'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
