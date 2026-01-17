<?php
/**
 * Admin Settings View with Tabs
 */

if (!defined('ABSPATH')) {
    exit;
}

$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
$email_settings = get_option('irp_email_settings', []);
?>
<div class="wrap irp-admin-wrap">
    <h1><?php esc_html_e('Einstellungen', 'immobilien-rechner-pro'); ?></h1>

    <nav class="nav-tab-wrapper irp-nav-tabs">
        <a href="?page=irp-settings&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Allgemein', 'immobilien-rechner-pro'); ?>
        </a>
        <a href="?page=irp-settings&tab=branding" class="nav-tab <?php echo $active_tab === 'branding' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Branding & Kontakt', 'immobilien-rechner-pro'); ?>
        </a>
        <a href="?page=irp-settings&tab=email" class="nav-tab <?php echo $active_tab === 'email' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('E-Mail', 'immobilien-rechner-pro'); ?>
        </a>
        <a href="?page=irp-settings&tab=recaptcha" class="nav-tab <?php echo $active_tab === 'recaptcha' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('reCAPTCHA', 'immobilien-rechner-pro'); ?>
        </a>
        <a href="?page=irp-settings&tab=maps" class="nav-tab <?php echo $active_tab === 'maps' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Google Maps', 'immobilien-rechner-pro'); ?>
        </a>
        <a href="?page=irp-settings&tab=tracking" class="nav-tab <?php echo $active_tab === 'tracking' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Tracking', 'immobilien-rechner-pro'); ?>
        </a>
    </nav>

    <form method="post" action="options.php">
        <?php settings_fields('irp_settings_group'); ?>

        <?php if ($active_tab === 'general') : ?>
            <!-- TAB: Allgemein -->
            <div class="irp-settings-section">
                <h2><?php esc_html_e('Darstellung', 'immobilien-rechner-pro'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="calculator_max_width"><?php esc_html_e('Maximale Breite', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <?php $max_width = $settings['calculator_max_width'] ?? 680; ?>
                            <div class="irp-range-slider-container">
                                <input type="range" id="calculator_max_width" name="irp_settings[calculator_max_width]"
                                       value="<?php echo esc_attr($max_width); ?>"
                                       min="680" max="1200" step="20" class="irp-range-slider">
                                <output for="calculator_max_width" class="irp-range-value"><?php echo esc_html($max_width); ?>px</output>
                            </div>
                            <p class="description"><?php esc_html_e('Maximale Breite des Rechners (680px - 1200px).', 'immobilien-rechner-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="primary_color"><?php esc_html_e('Primärfarbe', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="color" id="primary_color" name="irp_settings[primary_color]"
                                   value="<?php echo esc_attr($settings['primary_color'] ?? '#2563eb'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="secondary_color"><?php esc_html_e('Sekundärfarbe', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="color" id="secondary_color" name="irp_settings[secondary_color]"
                                   value="<?php echo esc_attr($settings['secondary_color'] ?? '#1e40af'); ?>">
                        </td>
                    </tr>
                </table>
            </div>

            <div class="irp-settings-section">
                <h2><?php esc_html_e('Rechner-Standardwerte', 'immobilien-rechner-pro'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="default_maintenance_rate"><?php esc_html_e('Instandhaltungsrate (%)', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="default_maintenance_rate" name="irp_settings[default_maintenance_rate]"
                                   value="<?php echo esc_attr($settings['default_maintenance_rate'] ?? 1.5); ?>"
                                   step="0.1" min="0" max="10" class="small-text"> %
                            <p class="description"><?php esc_html_e('Jährliche Instandhaltungskosten als Prozentsatz des Immobilienwerts.', 'immobilien-rechner-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="default_vacancy_rate"><?php esc_html_e('Leerstandsrate (%)', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="default_vacancy_rate" name="irp_settings[default_vacancy_rate]"
                                   value="<?php echo esc_attr($settings['default_vacancy_rate'] ?? 3); ?>"
                                   step="0.5" min="0" max="20" class="small-text"> %
                            <p class="description"><?php esc_html_e('Erwartete Leerstandsrate für die Mieteinnahmen-Berechnung.', 'immobilien-rechner-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="default_broker_commission"><?php esc_html_e('Maklerprovision (%)', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="default_broker_commission" name="irp_settings[default_broker_commission]"
                                   value="<?php echo esc_attr($settings['default_broker_commission'] ?? 3.57); ?>"
                                   step="0.01" min="0" max="10" class="small-text"> %
                            <p class="description"><?php esc_html_e('Standard-Maklerprovision für Verkaufsberechnungen.', 'immobilien-rechner-pro'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="irp-settings-section">
                <h2><?php esc_html_e('Datenschutz & Einwilligung', 'immobilien-rechner-pro'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Einwilligung erforderlich', 'immobilien-rechner-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="irp_settings[require_consent]" value="1"
                                       <?php checked(!empty($settings['require_consent']), true); ?>>
                                <?php esc_html_e('Nutzer müssen der Datenschutzerklärung zustimmen, bevor sie Daten absenden können', 'immobilien-rechner-pro'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="privacy_policy_url"><?php esc_html_e('Datenschutzerklärung URL', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="url" id="privacy_policy_url" name="irp_settings[privacy_policy_url]"
                                   value="<?php echo esc_url($settings['privacy_policy_url'] ?? get_privacy_policy_url()); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>
            </div>

        <?php elseif ($active_tab === 'branding') : ?>
            <!-- TAB: Branding & Kontakt -->
            <div class="irp-settings-section">
                <h2><?php esc_html_e('Logo', 'immobilien-rechner-pro'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="company_logo"><?php esc_html_e('Firmenlogo', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="hidden" id="company_logo" name="irp_settings[company_logo]"
                                   value="<?php echo esc_url($settings['company_logo'] ?? ''); ?>">
                            <div class="irp-logo-preview">
                                <?php if (!empty($settings['company_logo'])) : ?>
                                    <img src="<?php echo esc_url($settings['company_logo']); ?>" alt="Logo" style="max-width: <?php echo esc_attr($settings['company_logo_width'] ?? 150); ?>px;">
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button irp-upload-logo">
                                <?php esc_html_e('Logo hochladen', 'immobilien-rechner-pro'); ?>
                            </button>
                            <button type="button" class="button irp-remove-logo" <?php echo empty($settings['company_logo']) ? 'style="display:none"' : ''; ?>>
                                <?php esc_html_e('Entfernen', 'immobilien-rechner-pro'); ?>
                            </button>
                            <?php
                            // Show SVG warning if logo is SVG
                            $logo_url = $settings['company_logo'] ?? '';
                            $is_svg = !empty($logo_url) && strtolower(pathinfo(parse_url($logo_url, PHP_URL_PATH), PATHINFO_EXTENSION)) === 'svg';
                            ?>
                            <div class="irp-svg-warning notice notice-warning inline" <?php echo !$is_svg ? 'style="display:none;"' : ''; ?>>
                                <p>
                                    <strong><?php esc_html_e('Hinweis:', 'immobilien-rechner-pro'); ?></strong>
                                    <?php esc_html_e('SVG-Logos werden in E-Mails nicht unterstützt und im PDF nur eingeschränkt. Bitte verwenden Sie PNG oder JPG für optimale Kompatibilität.', 'immobilien-rechner-pro'); ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="company_logo_width"><?php esc_html_e('Logo-Breite im PDF', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="company_logo_width" name="irp_settings[company_logo_width]"
                                   value="<?php echo esc_attr($settings['company_logo_width'] ?? 150); ?>"
                                   min="50" max="300" step="10" class="small-text"> px
                            <p class="description"><?php esc_html_e('Breite des Logos in PDF-Dokumenten (50-300 px).', 'immobilien-rechner-pro'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="irp-settings-section">
                <h2><?php esc_html_e('Firmendaten', 'immobilien-rechner-pro'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="company_name"><?php esc_html_e('Firmenname', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="company_name" name="irp_settings[company_name]"
                                   value="<?php echo esc_attr($settings['company_name'] ?? ''); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="company_name_2"><?php esc_html_e('Firmenname Zeile 2', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="company_name_2" name="irp_settings[company_name_2]"
                                   value="<?php echo esc_attr($settings['company_name_2'] ?? ''); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e('Optional: z.B. Rechtsform', 'immobilien-rechner-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="company_name_3"><?php esc_html_e('Firmenname Zeile 3', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="company_name_3" name="irp_settings[company_name_3]"
                                   value="<?php echo esc_attr($settings['company_name_3'] ?? ''); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e('Optional: z.B. weitere Gesellschaft', 'immobilien-rechner-pro'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="irp-settings-section">
                <h2><?php esc_html_e('Adresse & Kontakt', 'immobilien-rechner-pro'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="company_street"><?php esc_html_e('Straße', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="company_street" name="irp_settings[company_street]"
                                   value="<?php echo esc_attr($settings['company_street'] ?? ''); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="company_zip"><?php esc_html_e('PLZ / Ort', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="company_zip" name="irp_settings[company_zip]"
                                   value="<?php echo esc_attr($settings['company_zip'] ?? ''); ?>" class="small-text" style="width: 80px;">
                            <input type="text" id="company_city" name="irp_settings[company_city]"
                                   value="<?php echo esc_attr($settings['company_city'] ?? ''); ?>" class="regular-text" style="width: 250px;">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="company_phone"><?php esc_html_e('Telefon', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="tel" id="company_phone" name="irp_settings[company_phone]"
                                   value="<?php echo esc_attr($settings['company_phone'] ?? ''); ?>" class="regular-text"
                                   placeholder="+49 123 456789">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="company_email"><?php esc_html_e('E-Mail', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="email" id="company_email" name="irp_settings[company_email]"
                                   value="<?php echo esc_attr($settings['company_email'] ?? get_option('admin_email')); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e('Wird für Lead-Benachrichtigungen und im PDF-Footer verwendet.', 'immobilien-rechner-pro'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

        <?php elseif ($active_tab === 'email') : ?>
            <!-- TAB: E-Mail -->
            <div class="irp-settings-section">
                <h2><?php esc_html_e('E-Mail Einstellungen', 'immobilien-rechner-pro'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('E-Mail versenden', 'immobilien-rechner-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="irp_email_settings[enabled]" value="1"
                                       <?php checked(!empty($email_settings['enabled']), true); ?>>
                                <?php esc_html_e('E-Mail mit Auswertung automatisch an Lead versenden', 'immobilien-rechner-pro'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('Der Lead erhält eine E-Mail mit PDF-Anhang nach Abschluss der Berechnung.', 'immobilien-rechner-pro'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="irp-settings-section">
                <h2><?php esc_html_e('Absender', 'immobilien-rechner-pro'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="sender_name"><?php esc_html_e('Absender-Name', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="sender_name" name="irp_email_settings[sender_name]"
                                   value="<?php echo esc_attr($email_settings['sender_name'] ?? ''); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e('Wenn leer, wird der Firmenname verwendet.', 'immobilien-rechner-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="sender_email"><?php esc_html_e('Absender-E-Mail', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="email" id="sender_email" name="irp_email_settings[sender_email]"
                                   value="<?php echo esc_attr($email_settings['sender_email'] ?? ''); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e('Wenn leer, wird die Firmen-E-Mail verwendet.', 'immobilien-rechner-pro'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="irp-settings-section">
                <h2><?php esc_html_e('E-Mail Inhalt', 'immobilien-rechner-pro'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="email_subject"><?php esc_html_e('Betreff', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="email_subject" name="irp_email_settings[subject]"
                                   value="<?php echo esc_attr($email_settings['subject'] ?? 'Ihre Immobilienbewertung - {property_type} in {city}'); ?>" class="large-text">
                            <p class="description"><?php esc_html_e('Verfügbare Variablen: {name}, {city}, {property_type}, {size}, {result_value}', 'immobilien-rechner-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="email_content"><?php esc_html_e('E-Mail Text', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <?php
                            $default_content = "Guten Tag {name},\n\nvielen Dank für Ihr Interesse an unserer Immobilienbewertung.\n\nAnbei erhalten Sie Ihre persönliche Auswertung als PDF-Dokument.\n\nFür Fragen stehen wir Ihnen gerne zur Verfügung.\n\nMit freundlichen Grüßen";
                            $email_content = $email_settings['email_content'] ?? $default_content;
                            wp_editor($email_content, 'email_content', [
                                'textarea_name' => 'irp_email_settings[email_content]',
                                'textarea_rows' => 10,
                                'media_buttons' => false,
                                'teeny' => true,
                                'quicktags' => true,
                                'tinymce' => [
                                    'entity_encoding' => 'raw',
                                    'entities' => '',
                                    'verify_html' => false,
                                ],
                            ]);
                            ?>
                            <p class="description" style="margin-top: 10px;">
                                <?php esc_html_e('Die Signatur wird automatisch aus den Branding-Einstellungen generiert.', 'immobilien-rechner-pro'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="irp-info-box">
                <h4><?php esc_html_e('Verfügbare Variablen', 'immobilien-rechner-pro'); ?></h4>
                <ul>
                    <li><code>{name}</code> - <?php esc_html_e('Name des Leads', 'immobilien-rechner-pro'); ?></li>
                    <li><code>{city}</code> - <?php esc_html_e('Stadt der Immobilie', 'immobilien-rechner-pro'); ?></li>
                    <li><code>{property_type}</code> - <?php esc_html_e('Objekttyp (Wohnung, Haus, Gewerbe)', 'immobilien-rechner-pro'); ?></li>
                    <li><code>{size}</code> - <?php esc_html_e('Größe in m²', 'immobilien-rechner-pro'); ?></li>
                    <li><code>{result_value}</code> - <?php esc_html_e('Geschätzte Miete', 'immobilien-rechner-pro'); ?></li>
                </ul>
            </div>

            <div class="irp-settings-section">
                <h2><?php esc_html_e('Test-E-Mail', 'immobilien-rechner-pro'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="irp-test-email"><?php esc_html_e('E-Mail-Adresse', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="email" id="irp-test-email" class="regular-text"
                                   value="<?php echo esc_attr(get_option('admin_email')); ?>"
                                   placeholder="ihre@email.de">
                            <button type="button" id="irp-send-test-email" class="button button-secondary">
                                <?php esc_html_e('Test-E-Mail senden', 'immobilien-rechner-pro'); ?>
                            </button>
                            <div id="irp-test-email-result" class="notice inline" style="display: none; margin-top: 10px;"></div>
                            <p class="description"><?php esc_html_e('Sendet eine Test-E-Mail mit Beispieldaten an die angegebene Adresse.', 'immobilien-rechner-pro'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

        <?php elseif ($active_tab === 'recaptcha') : ?>
            <!-- TAB: reCAPTCHA -->
            <div class="irp-settings-section">
                <h2><?php esc_html_e('Spam-Schutz (reCAPTCHA v3)', 'immobilien-rechner-pro'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="recaptcha_site_key"><?php esc_html_e('Site Key', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="recaptcha_site_key" name="irp_settings[recaptcha_site_key]"
                                   value="<?php echo esc_attr($settings['recaptcha_site_key'] ?? ''); ?>" class="regular-text"
                                   placeholder="6Lc...">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="recaptcha_secret_key"><?php esc_html_e('Secret Key', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="password" id="recaptcha_secret_key" name="irp_settings[recaptcha_secret_key]"
                                   value="<?php echo esc_attr($settings['recaptcha_secret_key'] ?? ''); ?>" class="regular-text"
                                   placeholder="6Lc...">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="recaptcha_threshold"><?php esc_html_e('Mindest-Score', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="recaptcha_threshold" name="irp_settings[recaptcha_threshold]"
                                   value="<?php echo esc_attr($settings['recaptcha_threshold'] ?? 0.5); ?>"
                                   min="0" max="1" step="0.1" class="small-text">
                            <p class="description"><?php esc_html_e('0.0 = wahrscheinlich Bot, 1.0 = wahrscheinlich Mensch. Standard: 0.5', 'immobilien-rechner-pro'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="irp-info-box">
                <h4><?php esc_html_e('So erhalten Sie reCAPTCHA Keys:', 'immobilien-rechner-pro'); ?></h4>
                <ol>
                    <li><?php esc_html_e('Google reCAPTCHA Admin Console öffnen', 'immobilien-rechner-pro'); ?></li>
                    <li><?php esc_html_e('Neue Website registrieren', 'immobilien-rechner-pro'); ?></li>
                    <li><?php esc_html_e('Typ auswählen: reCAPTCHA v3', 'immobilien-rechner-pro'); ?></li>
                    <li><?php esc_html_e('Domain hinzufügen (z.B. ihre-website.de)', 'immobilien-rechner-pro'); ?></li>
                    <li><?php esc_html_e('Keys kopieren und hier einfügen', 'immobilien-rechner-pro'); ?></li>
                </ol>
                <p><a href="https://www.google.com/recaptcha/admin" target="_blank" rel="noopener">google.com/recaptcha/admin</a></p>
            </div>

        <?php elseif ($active_tab === 'maps') : ?>
            <!-- TAB: Google Maps -->
            <div class="irp-settings-section">
                <h2><?php esc_html_e('Google Maps Integration', 'immobilien-rechner-pro'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="google_maps_api_key"><?php esc_html_e('API Key', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="google_maps_api_key" name="irp_settings[google_maps_api_key]"
                                   value="<?php echo esc_attr($settings['google_maps_api_key'] ?? ''); ?>" class="regular-text"
                                   placeholder="AIzaSy...">
                            <p class="description"><?php esc_html_e('Wird für die Kartenanzeige und Adress-Autocomplete im Lage-Step benötigt.', 'immobilien-rechner-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Karte anzeigen', 'immobilien-rechner-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="irp_settings[show_map_in_location_step]" value="1"
                                       <?php checked(!empty($settings['show_map_in_location_step']), true); ?>>
                                <?php esc_html_e('Karte im Lage-Bewertungs-Step anzeigen', 'immobilien-rechner-pro'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="irp-info-box">
                <h4><?php esc_html_e('So erhalten Sie einen API-Key:', 'immobilien-rechner-pro'); ?></h4>
                <ol>
                    <li><?php esc_html_e('Google Cloud Console öffnen', 'immobilien-rechner-pro'); ?></li>
                    <li><?php esc_html_e('Neues Projekt erstellen oder vorhandenes auswählen', 'immobilien-rechner-pro'); ?></li>
                    <li><?php esc_html_e('APIs aktivieren:', 'immobilien-rechner-pro'); ?>
                        <ul>
                            <li>Maps JavaScript API</li>
                            <li>Places API</li>
                        </ul>
                    </li>
                    <li><?php esc_html_e('API-Key erstellen und hier einfügen', 'immobilien-rechner-pro'); ?></li>
                </ol>
                <p><a href="https://console.cloud.google.com/apis" target="_blank" rel="noopener">console.cloud.google.com</a></p>
            </div>

        <?php elseif ($active_tab === 'tracking') : ?>
            <!-- TAB: Tracking -->
            <div class="irp-settings-section">
                <h2><?php esc_html_e('Google Ads Conversion Tracking', 'immobilien-rechner-pro'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Tracken Sie Conversions in Google Ads, wenn Nutzer den Rechner verwenden.', 'immobilien-rechner-pro'); ?>
                </p>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="gads_conversion_id"><?php esc_html_e('Conversion-ID', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="gads_conversion_id" name="irp_settings[gads_conversion_id]"
                                   value="<?php echo esc_attr($settings['gads_conversion_id'] ?? ''); ?>" class="regular-text"
                                   placeholder="AW-123456789">
                            <p class="description"><?php esc_html_e('Die Conversion-ID aus Google Ads (Format: AW-XXXXXXXXX).', 'immobilien-rechner-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="gads_partial_label"><?php esc_html_e('Label: Anfrage gestartet', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="gads_partial_label" name="irp_settings[gads_partial_label]"
                                   value="<?php echo esc_attr($settings['gads_partial_label'] ?? ''); ?>" class="regular-text"
                                   placeholder="AbCdEfGhIjKl">
                            <p class="description"><?php esc_html_e('Conversion-Label für "Anfrage gestartet" (Berechnung durchgeführt, aber noch keine Kontaktdaten).', 'immobilien-rechner-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="gads_complete_label"><?php esc_html_e('Label: Anfrage abgeschlossen', 'immobilien-rechner-pro'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="gads_complete_label" name="irp_settings[gads_complete_label]"
                                   value="<?php echo esc_attr($settings['gads_complete_label'] ?? ''); ?>" class="regular-text"
                                   placeholder="MnOpQrStUvWx">
                            <p class="description"><?php esc_html_e('Conversion-Label für "Anfrage abgeschlossen" (Kontaktformular ausgefüllt).', 'immobilien-rechner-pro'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="irp-info-box">
                <h4><?php esc_html_e('So richten Sie Google Ads Conversions ein:', 'immobilien-rechner-pro'); ?></h4>
                <ol>
                    <li><?php esc_html_e('In Google Ads: Ziele → Conversions → Neue Conversion-Aktion', 'immobilien-rechner-pro'); ?></li>
                    <li><?php esc_html_e('Typ "Website" wählen und manuell einrichten', 'immobilien-rechner-pro'); ?></li>
                    <li><?php esc_html_e('Zwei Conversions erstellen:', 'immobilien-rechner-pro'); ?>
                        <ul>
                            <li><strong><?php esc_html_e('Anfrage gestartet', 'immobilien-rechner-pro'); ?></strong> - <?php esc_html_e('Kategorie: "Angebot anfordern"', 'immobilien-rechner-pro'); ?></li>
                            <li><strong><?php esc_html_e('Anfrage abgeschlossen', 'immobilien-rechner-pro'); ?></strong> - <?php esc_html_e('Kategorie: "Lead-Formular senden"', 'immobilien-rechner-pro'); ?></li>
                        </ul>
                    </li>
                    <li><?php esc_html_e('Conversion-ID und Labels hier eintragen', 'immobilien-rechner-pro'); ?></li>
                </ol>
            </div>

            <div class="irp-settings-section">
                <h2><?php esc_html_e('Google Tag Manager (Optional)', 'immobilien-rechner-pro'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Zusätzlich zu den Google Ads Conversions werden automatisch DataLayer-Events gefeuert:', 'immobilien-rechner-pro'); ?>
                </p>
                <table class="widefat" style="max-width: 600px; margin-top: 15px;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Event', 'immobilien-rechner-pro'); ?></th>
                            <th><?php esc_html_e('Auslöser', 'immobilien-rechner-pro'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>irp_partial_lead</code></td>
                            <td><?php esc_html_e('Berechnung durchgeführt', 'immobilien-rechner-pro'); ?></td>
                        </tr>
                        <tr>
                            <td><code>irp_complete_lead</code></td>
                            <td><?php esc_html_e('Kontaktformular abgeschickt', 'immobilien-rechner-pro'); ?></td>
                        </tr>
                    </tbody>
                </table>
                <p class="description" style="margin-top: 10px;">
                    <?php esc_html_e('Diese Events können im Google Tag Manager als Trigger verwendet werden.', 'immobilien-rechner-pro'); ?>
                </p>
            </div>

        <?php endif; ?>

        <?php submit_button(__('Änderungen speichern', 'immobilien-rechner-pro')); ?>
    </form>

    <!-- Plugin Updates Section (always visible) -->
    <div class="irp-settings-section irp-update-section" style="margin-top: 40px;">
        <h2><?php esc_html_e('Plugin-Updates', 'immobilien-rechner-pro'); ?></h2>

        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('Aktuelle Version', 'immobilien-rechner-pro'); ?></th>
                <td>
                    <strong id="irp-current-version"><?php echo esc_html(IRP_VERSION); ?></strong>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Update-Status', 'immobilien-rechner-pro'); ?></th>
                <td>
                    <div id="irp-update-status">
                        <span class="irp-update-status-text"><?php esc_html_e('Klicken Sie auf "Nach Updates suchen" um den Status zu prüfen.', 'immobilien-rechner-pro'); ?></span>
                    </div>
                    <div id="irp-update-result" style="display: none; margin-top: 15px;">
                        <div id="irp-update-message" class="notice inline" style="margin: 0;"></div>
                        <div id="irp-update-actions" style="margin-top: 10px;"></div>
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row"></th>
                <td>
                    <button type="button" id="irp-check-updates" class="button button-secondary">
                        <span class="dashicons dashicons-update" style="margin-top: 3px;"></span>
                        <?php esc_html_e('Nach Updates suchen', 'immobilien-rechner-pro'); ?>
                    </button>
                    <span id="irp-update-spinner" class="spinner" style="float: none; margin-top: 0;"></span>
                </td>
            </tr>
        </table>

        <div class="irp-info-box" style="margin-top: 20px;">
            <h4><?php esc_html_e('Automatische Updates', 'immobilien-rechner-pro'); ?></h4>
            <p><?php esc_html_e('Dieses Plugin prüft automatisch auf neue Versionen bei GitHub. Wenn ein Update verfügbar ist, erscheint eine Benachrichtigung unter Dashboard > Aktualisierungen.', 'immobilien-rechner-pro'); ?></p>
            <p>
                <a href="https://github.com/<?php echo esc_attr(IRP_GITHUB_REPO); ?>/releases" target="_blank" rel="noopener">
                    <?php esc_html_e('Alle Releases auf GitHub ansehen', 'immobilien-rechner-pro'); ?> →
                </a>
            </p>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Range slider value display
    $('.irp-range-slider').on('input', function() {
        $(this).next('.irp-range-value').text($(this).val() + 'px');
    });

    // Update check
    $('#irp-check-updates').on('click', function() {
        var $button = $(this);
        var $spinner = $('#irp-update-spinner');
        var $result = $('#irp-update-result');
        var $message = $('#irp-update-message');
        var $actions = $('#irp-update-actions');

        $button.prop('disabled', true);
        $spinner.addClass('is-active');
        $result.hide();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'irp_check_updates',
                nonce: irpAdmin.nonce
            },
            success: function(response) {
                $spinner.removeClass('is-active');
                $button.prop('disabled', false);
                $result.show();

                if (response.success) {
                    var data = response.data;

                    if (data.update_available) {
                        $message.removeClass('notice-info notice-success').addClass('notice-warning');
                        $message.html('<p><strong>' + data.message + '</strong></p>');
                        $actions.html(
                            '<a href="' + data.download_url + '" class="button button-primary">' +
                            '<?php esc_html_e('Jetzt aktualisieren', 'immobilien-rechner-pro'); ?>' +
                            '</a>'
                        );
                    } else {
                        $message.removeClass('notice-info notice-warning').addClass('notice-success');
                        $message.html('<p>' + data.message + '</p>');
                        $actions.html('');
                    }
                } else {
                    $message.removeClass('notice-success notice-warning').addClass('notice-error');
                    $message.html('<p>' + response.data.message + '</p>');
                    $actions.html('');
                }
            },
            error: function() {
                $spinner.removeClass('is-active');
                $button.prop('disabled', false);
                $result.show();
                $message.removeClass('notice-success notice-warning').addClass('notice-error');
                $message.html('<p><?php esc_html_e('Verbindungsfehler. Bitte versuchen Sie es erneut.', 'immobilien-rechner-pro'); ?></p>');
                $actions.html('');
            }
        });
    });
});
</script>
