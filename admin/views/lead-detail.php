<?php
/**
 * Admin Lead Detail View
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!$lead) {
    echo '<div class="wrap"><p>' . esc_html__('Lead nicht gefunden.', 'immobilien-rechner-pro') . '</p></div>';
    return;
}

$type_labels = [
    'apartment' => __('Wohnung', 'immobilien-rechner-pro'),
    'house' => __('Haus', 'immobilien-rechner-pro'),
    'commercial' => __('Gewerbe', 'immobilien-rechner-pro'),
];
?>
<div class="wrap irp-admin-wrap">
    <h1>
        <a href="<?php echo esc_url(admin_url('admin.php?page=irp-leads')); ?>" class="page-title-action">
            &larr; <?php esc_html_e('Zurück zu Leads', 'immobilien-rechner-pro'); ?>
        </a>
        <?php esc_html_e('Lead-Details', 'immobilien-rechner-pro'); ?>
    </h1>

    <div class="irp-lead-detail">
        <div class="irp-lead-header">
            <div class="irp-lead-avatar">
                <?php echo get_avatar($lead->email, 80); ?>
            </div>
            <div class="irp-lead-info">
                <h2><?php echo esc_html($lead->name ?: $lead->email); ?></h2>
                <span class="irp-badge irp-badge-<?php echo esc_attr($lead->mode); ?>">
                    <?php
                    $mode_labels = [
                        'rental' => __('Mietwert-Berechnung', 'immobilien-rechner-pro'),
                        'comparison' => __('Verkauf vs. Vermietung', 'immobilien-rechner-pro'),
                        'sale_value' => __('Verkaufswert-Bewertung', 'immobilien-rechner-pro'),
                    ];
                    echo esc_html($mode_labels[$lead->mode] ?? $lead->mode);
                    ?>
                </span>
                <p class="irp-lead-date">
                    <?php printf(
                        esc_html__('Eingereicht am %s', 'immobilien-rechner-pro'),
                        date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($lead->created_at))
                    ); ?>
                </p>
            </div>
            <div class="irp-lead-actions">
                <a href="mailto:<?php echo esc_attr($lead->email); ?>" class="button button-primary">
                    <span class="dashicons dashicons-email"></span>
                    <?php esc_html_e('E-Mail senden', 'immobilien-rechner-pro'); ?>
                </a>
                <?php if ($lead->phone) : ?>
                    <a href="tel:<?php echo esc_attr($lead->phone); ?>" class="button">
                        <span class="dashicons dashicons-phone"></span>
                        <?php esc_html_e('Anrufen', 'immobilien-rechner-pro'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="irp-lead-panels">
            <div class="irp-panel">
                <h3><?php esc_html_e('Kontaktinformationen', 'immobilien-rechner-pro'); ?></h3>
                <table class="irp-detail-table">
                    <tr>
                        <th><?php esc_html_e('Name', 'immobilien-rechner-pro'); ?></th>
                        <td><?php echo esc_html($lead->name ?: '—'); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('E-Mail', 'immobilien-rechner-pro'); ?></th>
                        <td><a href="mailto:<?php echo esc_attr($lead->email); ?>"><?php echo esc_html($lead->email); ?></a></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Telefon', 'immobilien-rechner-pro'); ?></th>
                        <td>
                            <?php if ($lead->phone) : ?>
                                <a href="tel:<?php echo esc_attr($lead->phone); ?>"><?php echo esc_html($lead->phone); ?></a>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Einwilligung', 'immobilien-rechner-pro'); ?></th>
                        <td>
                            <?php if ($lead->consent) : ?>
                                <span class="irp-consent-yes">✓ <?php esc_html_e('Datenschutzerklärung akzeptiert', 'immobilien-rechner-pro'); ?></span>
                            <?php else : ?>
                                <span class="irp-consent-no">✗ <?php esc_html_e('Keine Einwilligung', 'immobilien-rechner-pro'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="irp-panel">
                <h3><?php esc_html_e('Objektinformationen', 'immobilien-rechner-pro'); ?></h3>
                <table class="irp-detail-table">
                    <tr>
                        <th><?php esc_html_e('Typ', 'immobilien-rechner-pro'); ?></th>
                        <td><?php echo esc_html($type_labels[$lead->property_type] ?? ucfirst($lead->property_type ?: '—')); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Größe', 'immobilien-rechner-pro'); ?></th>
                        <td><?php echo $lead->property_size ? esc_html($lead->property_size . ' m²') : '—'; ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Standort', 'immobilien-rechner-pro'); ?></th>
                        <td>
                            <?php
                            $location = array_filter([$lead->zip_code, $lead->property_location]);
                            echo esc_html(implode(' ', $location) ?: '—');
                            ?>
                        </td>
                    </tr>
                </table>
            </div>

            <?php
            $propstack_status = IRP_Propstack::get_sync_status($lead);
            ?>
            <div class="irp-panel">
                <h3>
                    <span class="dashicons dashicons-cloud-upload"></span>
                    <?php esc_html_e('Propstack CRM', 'immobilien-rechner-pro'); ?>
                </h3>
                <table class="irp-detail-table">
                    <tr>
                        <th><?php esc_html_e('Status', 'immobilien-rechner-pro'); ?></th>
                        <td>
                            <span class="<?php echo esc_attr($propstack_status['class']); ?>">
                                <?php echo esc_html($propstack_status['icon'] . ' ' . $propstack_status['label']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php if (!empty($lead->propstack_id)) : ?>
                    <tr>
                        <th><?php esc_html_e('Propstack ID', 'immobilien-rechner-pro'); ?></th>
                        <td><?php echo esc_html($lead->propstack_id); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($lead->propstack_synced_at)) : ?>
                    <tr>
                        <th><?php esc_html_e('Synchronisiert am', 'immobilien-rechner-pro'); ?></th>
                        <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($lead->propstack_synced_at))); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($propstack_status['status'] === 'error' || ($propstack_status['status'] === 'pending' && !empty($lead->email))) : ?>
                    <tr>
                        <th><?php esc_html_e('Aktion', 'immobilien-rechner-pro'); ?></th>
                        <td>
                            <button type="button" class="button button-secondary" id="propstack-retry-btn" data-lead-id="<?php echo esc_attr($lead->id); ?>">
                                <span class="dashicons dashicons-update"></span>
                                <?php echo $propstack_status['status'] === 'error'
                                    ? esc_html__('Erneut versuchen', 'immobilien-rechner-pro')
                                    : esc_html__('Jetzt synchronisieren', 'immobilien-rechner-pro'); ?>
                            </button>
                            <span id="propstack-retry-result"></span>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <?php if ($lead->calculation_data) : ?>
            <div class="irp-panel">
                <h3><?php esc_html_e('Berechnungsdaten', 'immobilien-rechner-pro'); ?></h3>
                <pre class="irp-json-display"><?php echo esc_html(json_encode($lead->calculation_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
            </div>
        <?php endif; ?>

        <div class="irp-lead-footer">
            <form method="post" onsubmit="return confirm('<?php esc_attr_e('Sind Sie sicher, dass Sie diesen Lead löschen möchten?', 'immobilien-rechner-pro'); ?>');">
                <?php wp_nonce_field('irp_delete_lead'); ?>
                <input type="hidden" name="action" value="delete_lead">
                <input type="hidden" name="lead_id" value="<?php echo esc_attr($lead->id); ?>">
                <button type="submit" class="button irp-delete-btn">
                    <span class="dashicons dashicons-trash"></span>
                    <?php esc_html_e('Lead löschen', 'immobilien-rechner-pro'); ?>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(function($) {
    $('#propstack-retry-btn').on('click', function() {
        var $btn = $(this);
        var $result = $('#propstack-retry-result');
        var leadId = $btn.data('lead-id');

        $btn.prop('disabled', true);
        $btn.find('.dashicons').addClass('spin');
        $result.html('<span class="spinner is-active" style="float: none;"></span>');

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'irp_propstack_sync_lead',
                nonce: irpAdmin.nonce,
                lead_id: leadId
            },
            success: function(response) {
                if (response.success) {
                    $result.html('<span style="color: #00a32a;">' + response.data.message + '</span>');
                    // Reload page to show updated status
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    $result.html('<span style="color: #d63638;">' + response.data.message + '</span>');
                    $btn.prop('disabled', false);
                    $btn.find('.dashicons').removeClass('spin');
                }
            },
            error: function() {
                $result.html('<span style="color: #d63638;"><?php echo esc_js(__('Verbindungsfehler', 'immobilien-rechner-pro')); ?></span>');
                $btn.prop('disabled', false);
                $btn.find('.dashicons').removeClass('spin');
            }
        });
    });
});
</script>
<style>
.dashicons.spin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    100% { transform: rotate(360deg); }
}
.irp-status-success { color: #00a32a; }
.irp-status-error { color: #d63638; }
.irp-status-pending { color: #dba617; }
.irp-status-disabled { color: #8c8f94; }
</style>
