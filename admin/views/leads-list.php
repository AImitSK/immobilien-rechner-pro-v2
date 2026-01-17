<?php
/**
 * Admin Leads List View
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap irp-admin-wrap">
    <h1><?php esc_html_e('Leads', 'immobilien-rechner-pro'); ?></h1>

    <div class="irp-leads-toolbar">
        <form method="get" class="irp-filter-form">
            <input type="hidden" name="page" value="irp-leads">

            <select name="mode">
                <option value=""><?php esc_html_e('Alle Modi', 'immobilien-rechner-pro'); ?></option>
                <option value="rental" <?php selected($args['mode'], 'rental'); ?>><?php esc_html_e('Mietwert', 'immobilien-rechner-pro'); ?></option>
                <option value="comparison" <?php selected($args['mode'], 'comparison'); ?>><?php esc_html_e('Vergleich', 'immobilien-rechner-pro'); ?></option>
                <option value="sale_value" <?php selected($args['mode'], 'sale_value'); ?>><?php esc_html_e('Verkaufswert', 'immobilien-rechner-pro'); ?></option>
            </select>

            <select name="status">
                <option value=""><?php esc_html_e('Alle Status', 'immobilien-rechner-pro'); ?></option>
                <option value="complete" <?php selected($args['status'] ?? '', 'complete'); ?>><?php esc_html_e('Vollständig', 'immobilien-rechner-pro'); ?></option>
                <option value="partial" <?php selected($args['status'] ?? '', 'partial'); ?>><?php esc_html_e('Unvollständig', 'immobilien-rechner-pro'); ?></option>
            </select>

            <input type="search" name="s" value="<?php echo esc_attr($args['search']); ?>"
                   placeholder="<?php esc_attr_e('Suchen...', 'immobilien-rechner-pro'); ?>">

            <button type="submit" class="button"><?php esc_html_e('Filtern', 'immobilien-rechner-pro'); ?></button>
        </form>

        <div class="irp-toolbar-actions">
            <button type="button" class="button irp-delete-selected-btn" style="display: none;">
                <span class="dashicons dashicons-trash"></span>
                <?php esc_html_e('Ausgewählte löschen', 'immobilien-rechner-pro'); ?>
                <span class="irp-selected-count"></span>
            </button>
            <button type="button" class="button irp-export-btn">
                <span class="dashicons dashicons-download"></span>
                <?php esc_html_e('CSV exportieren', 'immobilien-rechner-pro'); ?>
            </button>
        </div>
    </div>

    <?php if (empty($leads['items'])) : ?>
        <div class="irp-no-leads">
            <p><?php esc_html_e('Keine Leads gefunden.', 'immobilien-rechner-pro'); ?></p>
        </div>
    <?php else : ?>
        <form id="irp-bulk-delete-form" method="post">
            <?php wp_nonce_field('irp_bulk_delete_leads'); ?>
            <input type="hidden" name="action" value="bulk_delete">
        </form>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th class="column-cb check-column">
                        <input type="checkbox" id="cb-select-all" title="<?php esc_attr_e('Alle auswählen', 'immobilien-rechner-pro'); ?>">
                    </th>
                    <th class="column-status"><?php esc_html_e('Status', 'immobilien-rechner-pro'); ?></th>
                    <th class="column-email-sent" title="<?php esc_attr_e('PDF-E-Mail versendet', 'immobilien-rechner-pro'); ?>">
                        <span class="dashicons dashicons-email-alt"></span>
                    </th>
                    <th class="column-propstack" title="<?php esc_attr_e('Propstack CRM Sync', 'immobilien-rechner-pro'); ?>">
                        <span class="dashicons dashicons-cloud-upload"></span>
                    </th>
                    <th class="column-name"><?php esc_html_e('Name', 'immobilien-rechner-pro'); ?></th>
                    <th class="column-email"><?php esc_html_e('E-Mail', 'immobilien-rechner-pro'); ?></th>
                    <th class="column-phone"><?php esc_html_e('Telefon', 'immobilien-rechner-pro'); ?></th>
                    <th class="column-mode"><?php esc_html_e('Modus', 'immobilien-rechner-pro'); ?></th>
                    <th class="column-date"><?php esc_html_e('Datum', 'immobilien-rechner-pro'); ?></th>
                    <th class="column-actions"><?php esc_html_e('Aktionen', 'immobilien-rechner-pro'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leads['items'] as $lead) :
                    $status = $lead->status ?? 'complete';
                    $status_label = $status === 'complete'
                        ? __('Vollständig', 'immobilien-rechner-pro')
                        : __('Unvollständig', 'immobilien-rechner-pro');
                ?>
                    <tr>
                        <td class="column-cb check-column">
                            <input type="checkbox" class="irp-lead-checkbox" value="<?php echo esc_attr($lead->id); ?>">
                        </td>
                        <td class="column-status">
                            <span class="irp-status-badge irp-status-<?php echo esc_attr($status); ?>">
                                <?php echo esc_html($status_label); ?>
                            </span>
                        </td>
                        <td class="column-email-sent">
                            <?php if (!empty($lead->email_sent)) : ?>
                                <span class="dashicons dashicons-yes-alt" style="color: #00a32a;" title="<?php echo esc_attr(sprintf(__('Gesendet am %s', 'immobilien-rechner-pro'), date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($lead->email_sent_at)))); ?>"></span>
                            <?php elseif ($status === 'complete') : ?>
                                <span class="dashicons dashicons-minus" style="color: #dba617;" title="<?php esc_attr_e('Nicht gesendet', 'immobilien-rechner-pro'); ?>"></span>
                            <?php else : ?>
                                <span class="dashicons dashicons-minus" style="color: #ccc;" title="<?php esc_attr_e('Lead unvollständig', 'immobilien-rechner-pro'); ?>"></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-propstack">
                            <?php
                            $propstack_status = IRP_Propstack::get_sync_status($lead);
                            ?>
                            <?php if ($propstack_status['status'] === 'synced') : ?>
                                <span class="dashicons dashicons-yes-alt" style="color: #00a32a;" title="<?php echo esc_attr($propstack_status['label']); ?>"></span>
                            <?php elseif ($propstack_status['status'] === 'error') : ?>
                                <span class="dashicons dashicons-warning" style="color: #d63638; cursor: pointer;"
                                      title="<?php echo esc_attr($propstack_status['label']); ?>"
                                      data-lead-id="<?php echo esc_attr($lead->id); ?>"
                                      class="propstack-retry-trigger"></span>
                            <?php elseif ($propstack_status['status'] === 'disabled') : ?>
                                <span class="dashicons dashicons-minus" style="color: #ccc;" title="<?php echo esc_attr($propstack_status['label']); ?>"></span>
                            <?php else : ?>
                                <span class="dashicons dashicons-clock" style="color: #dba617;" title="<?php echo esc_attr($propstack_status['label']); ?>"></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-name">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=irp-leads&lead=' . $lead->id)); ?>">
                                <strong><?php echo esc_html($lead->name ?: '—'); ?></strong>
                            </a>
                        </td>
                        <td class="column-email">
                            <a href="mailto:<?php echo esc_attr($lead->email); ?>">
                                <?php echo esc_html($lead->email); ?>
                            </a>
                        </td>
                        <td class="column-phone">
                            <?php if ($lead->phone) : ?>
                                <a href="tel:<?php echo esc_attr($lead->phone); ?>">
                                    <?php echo esc_html($lead->phone); ?>
                                </a>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td class="column-mode">
                            <span class="irp-badge irp-badge-<?php echo esc_attr($lead->mode); ?>">
                                <?php
                                $mode_labels = [
                                    'rental' => __('Mietwert', 'immobilien-rechner-pro'),
                                    'comparison' => __('Vergleich', 'immobilien-rechner-pro'),
                                    'sale_value' => __('Verkaufswert', 'immobilien-rechner-pro'),
                                ];
                                echo esc_html($mode_labels[$lead->mode] ?? $lead->mode);
                                ?>
                            </span>
                        </td>
                        <td class="column-date">
                            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($lead->created_at))); ?>
                        </td>
                        <td class="column-actions">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=irp-leads&lead=' . $lead->id)); ?>"
                               class="button button-small">
                                <?php esc_html_e('Ansehen', 'immobilien-rechner-pro'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($leads['pages'] > 1) : ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <span class="displaying-num">
                        <?php printf(
                            esc_html(_n('%s Eintrag', '%s Einträge', $leads['total'], 'immobilien-rechner-pro')),
                            number_format_i18n($leads['total'])
                        ); ?>
                    </span>
                    <span class="pagination-links">
                        <?php
                        echo paginate_links([
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total' => $leads['pages'],
                            'current' => $leads['current_page'],
                        ]);
                        ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
jQuery(function($) {
    // Checkbox handling
    var $selectAll = $('#cb-select-all');
    var $checkboxes = $('.irp-lead-checkbox');
    var $deleteBtn = $('.irp-delete-selected-btn');
    var $selectedCount = $('.irp-selected-count');

    function updateDeleteButton() {
        var count = $checkboxes.filter(':checked').length;
        if (count > 0) {
            $deleteBtn.show();
            $selectedCount.text('(' + count + ')');
        } else {
            $deleteBtn.hide();
        }
    }

    $selectAll.on('change', function() {
        $checkboxes.prop('checked', this.checked);
        updateDeleteButton();
    });

    $checkboxes.on('change', function() {
        var allChecked = $checkboxes.length === $checkboxes.filter(':checked').length;
        $selectAll.prop('checked', allChecked);
        updateDeleteButton();
    });

    // Bulk delete
    $deleteBtn.on('click', function() {
        var $checked = $checkboxes.filter(':checked');
        var count = $checked.length;

        if (count === 0) return;

        var message = count === 1
            ? '<?php echo esc_js(__('Möchten Sie diesen Lead wirklich löschen?', 'immobilien-rechner-pro')); ?>'
            : '<?php echo esc_js(__('Möchten Sie diese %d Leads wirklich löschen?', 'immobilien-rechner-pro')); ?>'.replace('%d', count);

        if (!confirm(message)) return;

        // Add selected IDs to the form and submit
        var $form = $('#irp-bulk-delete-form');
        $checked.each(function() {
            $form.append('<input type="hidden" name="lead_ids[]" value="' + $(this).val() + '">');
        });
        $form.submit();
    });

    // CSV Export
    $('.irp-export-btn').on('click', function() {
        var params = new URLSearchParams(window.location.search);
        var form = $('<form method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">');
        form.append('<input type="hidden" name="action" value="irp_export_leads">');
        form.append('<input type="hidden" name="nonce" value="<?php echo wp_create_nonce('irp_export_leads'); ?>">');
        form.append('<input type="hidden" name="mode" value="' + (params.get('mode') || '') + '">');
        form.appendTo('body').submit().remove();
    });

    // Propstack Retry
    $('.propstack-retry-trigger').on('click', function() {
        var $icon = $(this);
        var leadId = $icon.data('lead-id');

        if (!confirm('<?php echo esc_js(__('Lead erneut zu Propstack senden?', 'immobilien-rechner-pro')); ?>')) {
            return;
        }

        // Show loading
        $icon.removeClass('dashicons-warning').addClass('dashicons-update spin');

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
                    // Change to success icon
                    $icon.removeClass('dashicons-update spin')
                         .addClass('dashicons-yes-alt')
                         .css('color', '#00a32a')
                         .attr('title', response.data.message)
                         .off('click');
                } else {
                    // Change back to error icon
                    $icon.removeClass('dashicons-update spin')
                         .addClass('dashicons-warning')
                         .attr('title', response.data.message);
                    alert(response.data.message);
                }
            },
            error: function() {
                $icon.removeClass('dashicons-update spin').addClass('dashicons-warning');
                alert('<?php echo esc_js(__('Verbindungsfehler', 'immobilien-rechner-pro')); ?>');
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
.column-cb {
    width: 30px;
    padding: 8px 10px !important;
}
.column-propstack {
    width: 40px;
    text-align: center;
}
.irp-leads-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 10px;
}
.irp-toolbar-actions {
    display: flex;
    gap: 8px;
}
.irp-delete-selected-btn {
    color: #b32d2e;
    border-color: #b32d2e;
}
.irp-delete-selected-btn:hover {
    background: #b32d2e;
    color: #fff;
    border-color: #b32d2e;
}
.irp-delete-selected-btn .dashicons {
    margin-right: 3px;
}
.irp-selected-count {
    margin-left: 3px;
}
</style>
