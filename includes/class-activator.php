<?php
/**
 * Plugin activation handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class IRP_Activator {
    
    public static function activate(): void {
        self::create_tables();
        self::upgrade_database();
        self::set_default_options();
        flush_rewrite_rules();
    }

    /**
     * Upgrade database schema for existing installations
     */
    private static function upgrade_database(): void {
        global $wpdb;

        $leads_table = $wpdb->prefix . 'irp_leads';
        $current_version = get_option('irp_db_version', '1.0.0');

        // Check if new columns exist, if not add them
        $columns = $wpdb->get_col("DESCRIBE {$leads_table}", 0);

        if (!in_array('status', $columns)) {
            $wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN status varchar(20) NOT NULL DEFAULT 'complete' AFTER consent");
            $wpdb->query("ALTER TABLE {$leads_table} ADD INDEX idx_status (status)");
        }

        if (!in_array('newsletter_consent', $columns)) {
            $wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN newsletter_consent tinyint(1) NOT NULL DEFAULT 0 AFTER consent");
        }

        if (!in_array('recaptcha_score', $columns)) {
            $wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN recaptcha_score decimal(3,2) DEFAULT NULL AFTER status");
        }

        if (!in_array('ip_address', $columns)) {
            $wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN ip_address varchar(45) DEFAULT NULL AFTER recaptcha_score");
        }

        if (!in_array('completed_at', $columns)) {
            $wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN completed_at datetime DEFAULT NULL AFTER created_at");
        }

        if (!in_array('email_sent', $columns)) {
            $wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN email_sent tinyint(1) NOT NULL DEFAULT 0 AFTER completed_at");
        }

        if (!in_array('email_sent_at', $columns)) {
            $wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN email_sent_at datetime DEFAULT NULL AFTER email_sent");
        }

        // Propstack integration fields
        if (!in_array('propstack_id', $columns)) {
            $wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN propstack_id bigint(20) unsigned DEFAULT NULL AFTER email_sent_at");
        }

        if (!in_array('propstack_synced', $columns)) {
            $wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN propstack_synced tinyint(1) NOT NULL DEFAULT 0 AFTER propstack_id");
        }

        if (!in_array('propstack_error', $columns)) {
            $wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN propstack_error text DEFAULT NULL AFTER propstack_synced");
        }

        if (!in_array('propstack_synced_at', $columns)) {
            $wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN propstack_synced_at datetime DEFAULT NULL AFTER propstack_error");
        }

        // Sale value calculator fields (v2.0)
        if (!in_array('land_size', $columns)) {
            $wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN land_size decimal(10,2) DEFAULT NULL AFTER property_size");
        }

        if (!in_array('house_type', $columns)) {
            $wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN house_type varchar(50) DEFAULT NULL AFTER land_size");
        }

        if (!in_array('build_year', $columns)) {
            $wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN build_year int(4) DEFAULT NULL AFTER house_type");
        }

        if (!in_array('modernization', $columns)) {
            $wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN modernization varchar(50) DEFAULT NULL AFTER build_year");
        }

        if (!in_array('quality', $columns)) {
            $wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN quality varchar(50) DEFAULT NULL AFTER modernization");
        }

        if (!in_array('usage_type', $columns)) {
            $wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN usage_type varchar(50) DEFAULT NULL AFTER quality");
        }

        if (!in_array('sale_intention', $columns)) {
            $wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN sale_intention varchar(50) DEFAULT NULL AFTER usage_type");
        }

        if (!in_array('timeframe', $columns)) {
            $wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN timeframe varchar(50) DEFAULT NULL AFTER sale_intention");
        }

        if (!in_array('street_address', $columns)) {
            $wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN street_address varchar(255) DEFAULT NULL AFTER zip_code");
        }
    }
    
    private static function create_tables(): void {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $leads_table = $wpdb->prefix . 'irp_leads';
        $calculations_table = $wpdb->prefix . 'irp_calculations';
        
        $sql_leads = "CREATE TABLE $leads_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) DEFAULT NULL,
            email varchar(255) NOT NULL,
            phone varchar(50) DEFAULT NULL,
            mode varchar(20) NOT NULL DEFAULT 'rental',
            property_type varchar(50) DEFAULT NULL,
            property_size decimal(10,2) DEFAULT NULL,
            land_size decimal(10,2) DEFAULT NULL,
            house_type varchar(50) DEFAULT NULL,
            build_year int(4) DEFAULT NULL,
            modernization varchar(50) DEFAULT NULL,
            quality varchar(50) DEFAULT NULL,
            usage_type varchar(50) DEFAULT NULL,
            sale_intention varchar(50) DEFAULT NULL,
            timeframe varchar(50) DEFAULT NULL,
            property_location varchar(255) DEFAULT NULL,
            zip_code varchar(10) DEFAULT NULL,
            street_address varchar(255) DEFAULT NULL,
            calculation_data longtext DEFAULT NULL,
            consent tinyint(1) NOT NULL DEFAULT 0,
            newsletter_consent tinyint(1) NOT NULL DEFAULT 0,
            status varchar(20) NOT NULL DEFAULT 'partial',
            recaptcha_score decimal(3,2) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            source varchar(100) DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            completed_at datetime DEFAULT NULL,
            email_sent tinyint(1) NOT NULL DEFAULT 0,
            email_sent_at datetime DEFAULT NULL,
            propstack_id bigint(20) unsigned DEFAULT NULL,
            propstack_synced tinyint(1) NOT NULL DEFAULT 0,
            propstack_error text DEFAULT NULL,
            propstack_synced_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY email (email),
            KEY mode (mode),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        $sql_calculations = "CREATE TABLE $calculations_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            lead_id bigint(20) unsigned DEFAULT NULL,
            session_id varchar(64) NOT NULL,
            mode varchar(20) NOT NULL,
            input_data longtext NOT NULL,
            result_data longtext NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY lead_id (lead_id),
            KEY session_id (session_id),
            KEY mode (mode)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_leads);
        dbDelta($sql_calculations);
        
        update_option('irp_db_version', IRP_VERSION);
    }
    
    private static function set_default_options(): void {
        $defaults = [
            'irp_settings' => [
                'primary_color' => '#2563eb',
                'secondary_color' => '#1e40af',
                'company_name' => '',
                'company_logo' => '',
                'company_email' => get_option('admin_email'),
                'default_maintenance_rate' => 1.5,
                'default_vacancy_rate' => 3,
                'default_broker_commission' => 3.57,
                'enable_pdf_export' => false,
                'require_consent' => true,
                'privacy_policy_url' => get_privacy_policy_url(),
            ],
            'irp_price_matrix' => [
                'base_prices' => [
                    '0' => 10.50,  // Leipzig/Dresden
                    '1' => 18.50,  // Berlin
                    '2' => 16.00,  // Hamburg
                    '3' => 11.50,  // Hannover
                    '4' => 11.00,  // Düsseldorf
                    '5' => 11.50,  // Köln/Bonn
                    '6' => 13.50,  // Frankfurt
                    '7' => 13.00,  // Stuttgart
                    '8' => 19.00,  // München
                    '9' => 10.00,  // Nürnberg
                ],
                'condition_multipliers' => [
                    'new' => 1.25,
                    'renovated' => 1.10,
                    'good' => 1.00,
                    'needs_renovation' => 0.80,
                ],
                'type_multipliers' => [
                    'apartment' => 1.00,
                    'house' => 1.15,
                    'commercial' => 0.85,
                ],
                'feature_premiums' => [
                    'balcony' => 0.50,
                    'terrace' => 0.75,
                    'garden' => 1.00,
                    'elevator' => 0.30,
                    'parking' => 0.40,
                    'garage' => 0.60,
                    'cellar' => 0.20,
                    'fitted_kitchen' => 0.50,
                    'floor_heating' => 0.40,
                    'guest_toilet' => 0.25,
                    'barrier_free' => 0.30,
                ],
                'sale_factors' => [
                    '0' => 21,  // Leipzig/Dresden
                    '1' => 30,  // Berlin
                    '2' => 28,  // Hamburg
                    '3' => 22,  // Hannover
                    '4' => 23,  // Düsseldorf
                    '5' => 24,  // Köln/Bonn
                    '6' => 27,  // Frankfurt
                    '7' => 26,  // Stuttgart
                    '8' => 35,  // München
                    '9' => 20,  // Nürnberg
                ],
                'interest_rate' => 3.0,
                'appreciation_rate' => 2.0,
                'rent_increase_rate' => 2.0,
            ]
        ];

        foreach ($defaults as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }
}
