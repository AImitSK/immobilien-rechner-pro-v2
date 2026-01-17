<?php
/**
 * Email template for result emails
 *
 * Available variables:
 * - $content: Parsed email content
 * - $logo_url: Company logo URL
 * - $company_name: Primary company name
 * - $company_name_2: Secondary company name line
 * - $company_name_3: Third company name line
 * - $company_address: Formatted address string
 * - $company_phone: Phone number
 * - $company_email: Email address
 * - $primary_color: Brand primary color
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($company_name); ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #333333;
            background-color: #f5f5f5;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            text-align: center;
            padding: 30px 20px;
            border-bottom: 3px solid <?php echo esc_attr($primary_color); ?>;
        }
        .email-header img {
            max-height: 80px;
            max-width: 280px;
            height: auto;
        }
        .email-content {
            padding: 40px 30px;
        }
        .email-content p {
            margin: 0 0 16px 0;
        }
        .email-footer {
            padding: 25px 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
        }
        .email-footer p {
            margin: 0 0 5px 0;
        }
        .email-footer .company-name {
            font-weight: bold;
            color: #374151;
        }
        .email-footer .contact-line {
            margin-top: 10px;
        }
        @media only screen and (max-width: 600px) {
            .email-content {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f5f5f5;">
        <tr>
            <td align="center" style="padding: 20px 10px;">
                <table role="presentation" class="email-wrapper" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; max-width: 600px;">
                    <!-- Header -->
                    <?php if (!empty($logo_url)) : ?>
                    <tr>
                        <td class="email-header" style="text-align: center; padding: 30px 20px; border-bottom: 3px solid <?php echo esc_attr($primary_color); ?>;">
                            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($company_name); ?>" style="max-height: 80px; max-width: 280px; height: auto;">
                        </td>
                    </tr>
                    <?php endif; ?>

                    <!-- Content -->
                    <tr>
                        <td class="email-content" style="padding: 40px 30px; font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 1.6; color: #333333;">
                            <?php echo $content; ?>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="email-footer" style="padding: 25px 20px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 13px; color: #6b7280; line-height: 1.5;">
                            <?php if (!empty($company_name)) : ?>
                            <p class="company-name" style="margin: 0 0 5px 0; font-weight: bold; color: #374151;">
                                <?php echo esc_html($company_name); ?>
                            </p>
                            <?php endif; ?>

                            <?php if (!empty($company_name_2)) : ?>
                            <p style="margin: 0 0 5px 0;">
                                <?php echo esc_html($company_name_2); ?>
                            </p>
                            <?php endif; ?>

                            <?php if (!empty($company_name_3)) : ?>
                            <p style="margin: 0 0 5px 0;">
                                <?php echo esc_html($company_name_3); ?>
                            </p>
                            <?php endif; ?>

                            <?php if (!empty($company_address)) : ?>
                            <p style="margin: 0 0 5px 0;">
                                <?php echo esc_html($company_address); ?>
                            </p>
                            <?php endif; ?>

                            <?php if (!empty($company_phone) || !empty($company_email)) : ?>
                            <p class="contact-line" style="margin: 10px 0 0 0;">
                                <?php
                                $contact_parts = [];
                                if (!empty($company_phone)) {
                                    $contact_parts[] = 'Tel.: ' . esc_html($company_phone);
                                }
                                if (!empty($company_email)) {
                                    $contact_parts[] = 'E-Mail: ' . esc_html($company_email);
                                }
                                echo implode(' | ', $contact_parts);
                                ?>
                            </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
