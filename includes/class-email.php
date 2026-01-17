<?php
/**
 * Email functionality for sending result emails with PDF attachment
 */

if (!defined('ABSPATH')) {
    exit;
}

class IRP_Email {

    /**
     * Check if email sending is enabled
     *
     * @return bool
     */
    public static function is_enabled() {
        $settings = get_option('irp_email_settings', []);
        return !empty($settings['enabled']);
    }

    /**
     * Schedule email to be sent after response
     *
     * @param int $lead_id Lead ID
     * @return void
     */
    public static function schedule_after_response($lead_id) {
        if (!self::is_enabled()) {
            return;
        }

        // Send after response (user doesn't wait)
        register_shutdown_function([self::class, 'send_result_email'], $lead_id);
    }

    /**
     * Send result email with PDF attachment
     *
     * @param int $lead_id Lead ID
     * @return bool True on success, false on failure
     */
    public static function send_result_email($lead_id) {
        $leads = new IRP_Leads();
        $lead = $leads->get($lead_id);

        if (!$lead) {
            error_log('[IRP Email] Lead not found: ' . $lead_id);
            return false;
        }

        $calculation_data = $lead->calculation_data;

        if (is_string($calculation_data)) {
            $calculation_data = json_decode($calculation_data, true);
        } elseif (is_object($calculation_data)) {
            $calculation_data = json_decode(json_encode($calculation_data), true);
        }

        $lead_data = [
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'calculation_data' => $calculation_data,
        ];

        // Generate PDF if PDF generator exists
        $pdf_path = null;
        if (class_exists('IRP_PDF_Generator')) {
            $pdf_path = IRP_PDF_Generator::create($lead_data);
            if (!$pdf_path) {
                error_log('[IRP Email] PDF generation failed for lead: ' . $lead_id);
            }
        }

        // Build email
        $to = $lead_data['email'];
        $subject = self::parse_template(self::get_subject(), $lead_data);
        $body = self::build_email_body($lead_data);
        $headers = self::get_headers();
        $attachments = $pdf_path ? [$pdf_path] : [];

        // Send
        $sent = wp_mail($to, $subject, $body, $headers, $attachments);

        // Clean up PDF
        if ($pdf_path && file_exists($pdf_path)) {
            @unlink($pdf_path);
        }

        // Update lead status
        if ($sent) {
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'irp_leads',
                [
                    'email_sent' => 1,
                    'email_sent_at' => current_time('mysql'),
                ],
                ['id' => $lead_id],
                ['%d', '%s'],
                ['%d']
            );
            error_log('[IRP Email] Email sent successfully to: ' . $to);
        } else {
            error_log('[IRP Email] Failed to send email to: ' . $to);
        }

        return $sent;
    }

    /**
     * Get email headers
     *
     * @return array
     */
    private static function get_headers() {
        $settings = get_option('irp_email_settings', []);
        $branding = get_option('irp_settings', []);

        $from_name = !empty($settings['sender_name'])
            ? $settings['sender_name']
            : (!empty($branding['company_name']) ? $branding['company_name'] : get_bloginfo('name'));

        $from_email = !empty($settings['sender_email'])
            ? $settings['sender_email']
            : (!empty($branding['company_email']) ? $branding['company_email'] : get_option('admin_email'));

        return [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>',
        ];
    }

    /**
     * Get email subject
     *
     * @return string
     */
    private static function get_subject() {
        $settings = get_option('irp_email_settings', []);
        return !empty($settings['subject'])
            ? $settings['subject']
            : 'Ihre Immobilienbewertung - {property_type} in {city}';
    }

    /**
     * Get email content from settings or default
     *
     * @return string
     */
    private static function get_email_content() {
        $settings = get_option('irp_email_settings', []);

        if (!empty($settings['email_content'])) {
            return $settings['email_content'];
        }

        return "Guten Tag {name},\n\n" .
               "vielen Dank für Ihr Interesse an unserer Immobilienbewertung.\n\n" .
               "Anbei erhalten Sie Ihre persönliche Auswertung als PDF-Dokument.\n\n" .
               "Für Fragen stehen wir Ihnen gerne zur Verfügung.\n\n" .
               "Mit freundlichen Grüßen";
    }

    /**
     * Build email body with template
     *
     * @param array $data Lead data
     * @return string
     */
    private static function build_email_body($data) {
        $branding = get_option('irp_settings', []);

        $content = self::get_email_content();
        $content = self::parse_template($content, $data);

        // Convert newlines to <br> if content is plain text
        if (strpos($content, '<p>') === false && strpos($content, '<br') === false) {
            $content = nl2br($content);
        }

        // Build address string
        $address_parts = [];
        if (!empty($branding['company_street'])) {
            $address_parts[] = $branding['company_street'];
        }
        if (!empty($branding['company_zip']) || !empty($branding['company_city'])) {
            $address_parts[] = trim(($branding['company_zip'] ?? '') . ' ' . ($branding['company_city'] ?? ''));
        }
        $address = implode(', ', $address_parts);

        // Check if logo is SVG (not supported in email clients)
        $logo_url = $branding['company_logo'] ?? '';
        if (!empty($logo_url) && self::is_svg_url($logo_url)) {
            $logo_url = ''; // SVG not supported in email clients
            error_log('[IRP Email] SVG logo not supported in emails - logo skipped');
        }

        // Template variables
        $template_vars = [
            'content' => $content,
            'logo_url' => $logo_url,
            'company_name' => $branding['company_name'] ?? '',
            'company_name_2' => $branding['company_name_2'] ?? '',
            'company_name_3' => $branding['company_name_3'] ?? '',
            'company_address' => $address,
            'company_phone' => $branding['company_phone'] ?? '',
            'company_email' => $branding['company_email'] ?? '',
            'primary_color' => $branding['primary_color'] ?? '#2563eb',
        ];

        // Check if template file exists
        $template_path = IRP_PLUGIN_DIR . 'includes/templates/email.php';
        if (file_exists($template_path)) {
            ob_start();
            extract($template_vars);
            include $template_path;
            return ob_get_clean();
        }

        // Fallback: simple HTML
        return self::build_fallback_email($template_vars);
    }

    /**
     * Build fallback email if template doesn't exist
     *
     * @param array $vars Template variables
     * @return string
     */
    private static function build_fallback_email($vars) {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
';

        // Header with logo
        if (!empty($vars['logo_url'])) {
            $html .= '<div style="text-align: center; padding: 20px 0; border-bottom: 2px solid ' . esc_attr($vars['primary_color']) . ';">
                <img src="' . esc_url($vars['logo_url']) . '" alt="' . esc_attr($vars['company_name']) . '" style="max-height: 60px;">
            </div>';
        }

        // Content
        $html .= '<div style="padding: 30px 20px; line-height: 1.6;">' . $vars['content'] . '</div>';

        // Signature
        $html .= '<div style="padding: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;">';

        if (!empty($vars['company_name'])) {
            $html .= '<p style="margin: 0 0 5px;"><strong>' . esc_html($vars['company_name']) . '</strong></p>';
        }
        if (!empty($vars['company_name_2'])) {
            $html .= '<p style="margin: 0 0 5px;">' . esc_html($vars['company_name_2']) . '</p>';
        }
        if (!empty($vars['company_name_3'])) {
            $html .= '<p style="margin: 0 0 10px;">' . esc_html($vars['company_name_3']) . '</p>';
        }
        if (!empty($vars['company_address'])) {
            $html .= '<p style="margin: 0 0 10px;">' . esc_html($vars['company_address']) . '</p>';
        }
        if (!empty($vars['company_phone']) || !empty($vars['company_email'])) {
            $html .= '<p style="margin: 0;">';
            if (!empty($vars['company_phone'])) {
                $html .= 'Tel.: ' . esc_html($vars['company_phone']);
            }
            if (!empty($vars['company_phone']) && !empty($vars['company_email'])) {
                $html .= '<br>';
            }
            if (!empty($vars['company_email'])) {
                $html .= 'E-Mail: ' . esc_html($vars['company_email']);
            }
            $html .= '</p>';
        }

        $html .= '</div>';

        $html .= '</body></html>';

        return $html;
    }

    /**
     * Parse template with variables
     *
     * @param string $template Template string
     * @param array $data Lead data
     * @return string
     */
    private static function parse_template($template, $data) {
        $calc = isset($data['calculation_data']) && is_array($data['calculation_data'])
            ? $data['calculation_data']
            : [];
        $result = isset($calc['result']) && is_array($calc['result']) ? $calc['result'] : [];

        // Build replacement values
        $values = [
            'name' => isset($data['name']) ? $data['name'] : '',
            'email' => isset($data['email']) ? $data['email'] : '',
            'city' => isset($calc['city_name']) ? $calc['city_name'] : '',
            'property_type' => self::translate_type(isset($calc['property_type']) ? $calc['property_type'] : ''),
            'size' => isset($calc['size']) ? $calc['size'] : '',
            'condition' => self::translate_condition(isset($calc['condition']) ? $calc['condition'] : ''),
            'result_value' => self::format_rent($result),
        ];

        // First: Decode HTML entities that wp_editor might have created
        // &#123; = { and &#125; = }
        $template = str_replace(['&#123;', '&#125;'], ['{', '}'], $template);
        $template = str_replace(['&lcub;', '&rcub;'], ['{', '}'], $template);
        $template = html_entity_decode($template, ENT_QUOTES, 'UTF-8');

        // Replace placeholders
        foreach ($values as $key => $value) {
            // Standard replacement {placeholder}
            $template = str_replace('{' . $key . '}', $value, $template);

            // With spaces { placeholder }
            $template = preg_replace('/\{\s*' . preg_quote($key, '/') . '\s*\}/i', $value, $template);
        }

        return $template;
    }

    /**
     * Translate property type to German
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
     * Translate condition to German
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
     * Format rent value
     *
     * @param array $result Calculation result
     * @return string
     */
    private static function format_rent($result) {
        if (isset($result['monthly_rent']['estimate'])) {
            $rent = $result['monthly_rent']['estimate'];
        } elseif (isset($result['monthly_rent']) && is_numeric($result['monthly_rent'])) {
            $rent = $result['monthly_rent'];
        } else {
            return '-';
        }

        return number_format($rent, 0, ',', '.') . ' €/Monat';
    }

    /**
     * Check if URL points to an SVG file
     *
     * @param string $url URL to check
     * @return bool
     */
    private static function is_svg_url($url) {
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return false;
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return $ext === 'svg';
    }

    /**
     * Send test email
     *
     * @param string $to Email address
     * @return bool
     */
    public static function send_test_email($to) {
        // Mock lead data for test
        $test_data = [
            'name' => 'Max Mustermann',
            'email' => $to,
            'calculation_data' => [
                'property_type' => 'apartment',
                'size' => 85,
                'city_name' => 'Berlin',
                'condition' => 'good',
                'location_rating' => 4,
                'result' => [
                    'monthly_rent' => [
                        'estimate' => 1250,
                        'min' => 1180,
                        'max' => 1320,
                    ],
                    'price_per_sqm' => 14.70,
                ],
            ],
        ];

        $subject = self::parse_template(self::get_subject(), $test_data);
        $body = self::build_email_body($test_data);
        $headers = self::get_headers();

        return wp_mail($to, '[TEST] ' . $subject, $body, $headers);
    }
}
