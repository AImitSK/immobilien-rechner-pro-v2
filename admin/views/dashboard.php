<?php
/**
 * Admin Dashboard View
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap irp-admin-wrap">
    <h1><?php esc_html_e('Immobilien Rechner Pro', 'immobilien-rechner-pro'); ?></h1>

    <div class="irp-dashboard-cards">
        <div class="irp-card">
            <div class="irp-card-icon dashicons dashicons-groups"></div>
            <div class="irp-card-content">
                <span class="irp-card-value"><?php echo esc_html($total_leads); ?></span>
                <span class="irp-card-label"><?php esc_html_e('Leads gesamt', 'immobilien-rechner-pro'); ?></span>
            </div>
        </div>

        <div class="irp-card">
            <div class="irp-card-icon dashicons dashicons-calendar-alt"></div>
            <div class="irp-card-content">
                <span class="irp-card-value"><?php echo esc_html($leads_this_month); ?></span>
                <span class="irp-card-label"><?php esc_html_e('Leads diesen Monat', 'immobilien-rechner-pro'); ?></span>
            </div>
        </div>

        <div class="irp-card">
            <div class="irp-card-icon dashicons dashicons-calculator"></div>
            <div class="irp-card-content">
                <span class="irp-card-value"><?php echo esc_html($total_calculations); ?></span>
                <span class="irp-card-label"><?php esc_html_e('Berechnungen gesamt', 'immobilien-rechner-pro'); ?></span>
            </div>
        </div>

        <div class="irp-card">
            <div class="irp-card-icon dashicons dashicons-building"></div>
            <div class="irp-card-content">
                <span class="irp-card-value"><?php echo esc_html($rental_calculations); ?></span>
                <span class="irp-card-label"><?php esc_html_e('Mietwert-Berechnungen', 'immobilien-rechner-pro'); ?></span>
            </div>
        </div>
    </div>

    <div class="irp-dashboard-row">
        <div class="irp-dashboard-col">
            <div class="irp-panel">
                <h2><?php esc_html_e('Schnellstart', 'immobilien-rechner-pro'); ?></h2>
                <p><?php esc_html_e('Fügen Sie den Rechner mit folgendem Shortcode zu einer Seite oder einem Beitrag hinzu:', 'immobilien-rechner-pro'); ?></p>
                <code class="irp-shortcode">[immobilien_rechner]</code>

                <h3><?php esc_html_e('Shortcode-Optionen', 'immobilien-rechner-pro'); ?></h3>
                <table class="irp-shortcode-options">
                    <tr>
                        <td><code>mode=""</code></td>
                        <td><?php esc_html_e('Leer lassen für Modusauswahl, oder "rental" oder "comparison" setzen', 'immobilien-rechner-pro'); ?></td>
                    </tr>
                    <tr>
                        <td><code>theme="light"</code></td>
                        <td><?php esc_html_e('Auf "light" oder "dark" setzen', 'immobilien-rechner-pro'); ?></td>
                    </tr>
                    <tr>
                        <td><code>show_branding="true"</code></td>
                        <td><?php esc_html_e('Firmenbranding anzeigen oder ausblenden', 'immobilien-rechner-pro'); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="irp-dashboard-col">
            <div class="irp-panel">
                <h2><?php esc_html_e('Neueste Leads', 'immobilien-rechner-pro'); ?></h2>
                <?php if (empty($recent_leads)) : ?>
                    <p class="irp-no-data"><?php esc_html_e('Noch keine Leads. Fügen Sie den Rechner zu einer Seite hinzu, um Leads zu sammeln.', 'immobilien-rechner-pro'); ?></p>
                <?php else : ?>
                    <table class="irp-leads-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Kontakt', 'immobilien-rechner-pro'); ?></th>
                                <th><?php esc_html_e('Modus', 'immobilien-rechner-pro'); ?></th>
                                <th><?php esc_html_e('Datum', 'immobilien-rechner-pro'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_leads as $lead) : ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=irp-leads&lead=' . $lead->id)); ?>">
                                            <?php echo esc_html($lead->name ?: $lead->email); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="irp-badge irp-badge-<?php echo esc_attr($lead->mode); ?>">
                                            <?php echo $lead->mode === 'rental' ? esc_html__('Mietwert', 'immobilien-rechner-pro') : esc_html__('Vergleich', 'immobilien-rechner-pro'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($lead->created_at))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=irp-leads')); ?>" class="button">
                            <?php esc_html_e('Alle Leads anzeigen', 'immobilien-rechner-pro'); ?>
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
