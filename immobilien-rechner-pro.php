<?php
/**
 * Plugin Name: Immobilien Rechner Pro V2
 * Plugin URI: https://github.com/AImitSK/immobilien-rechner-pro-v2
 * Description: Professionelles WordPress-Plugin für Immobilienbewertung, Mietwertberechnung und Verkaufen-vs-Vermieten-Vergleich. White-Label-Lösung für Immobilienmakler.
 * Version: 2.0.0
 * Author: Stefan Kühne
 * Author URI: https://sk-online-marketing.de
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: immobilien-rechner-pro
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * GitHub Plugin URI: AImitSK/immobilien-rechner-pro-v2
 * GitHub Branch: main
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('IRP_VERSION', '2.0.0');
define('IRP_GITHUB_REPO', 'AImitSK/immobilien-rechner-pro-v2');
define('IRP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('IRP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('IRP_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
final class Immobilien_Rechner_Pro {
    
    private static ?Immobilien_Rechner_Pro $instance = null;
    
    public static function instance(): Immobilien_Rechner_Pro {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    private function load_dependencies(): void {
        require_once IRP_PLUGIN_DIR . 'includes/class-activator.php';
        require_once IRP_PLUGIN_DIR . 'includes/class-deactivator.php';
        require_once IRP_PLUGIN_DIR . 'includes/class-assets.php';
        require_once IRP_PLUGIN_DIR . 'includes/class-shortcode.php';
        require_once IRP_PLUGIN_DIR . 'includes/class-rest-api.php';
        require_once IRP_PLUGIN_DIR . 'includes/class-calculator.php';
        require_once IRP_PLUGIN_DIR . 'includes/class-leads.php';
        require_once IRP_PLUGIN_DIR . 'includes/class-recaptcha.php';
        require_once IRP_PLUGIN_DIR . 'includes/class-email.php';
        require_once IRP_PLUGIN_DIR . 'includes/class-pdf-generator.php';
        require_once IRP_PLUGIN_DIR . 'includes/class-github-updater.php';
        require_once IRP_PLUGIN_DIR . 'includes/class-propstack.php';
        require_once IRP_PLUGIN_DIR . 'admin/class-admin.php';
    }
    
    private function init_hooks(): void {
        register_activation_hook(__FILE__, ['IRP_Activator', 'activate']);
        register_deactivation_hook(__FILE__, ['IRP_Deactivator', 'deactivate']);

        add_action('init', [$this, 'load_textdomain']);
        add_action('init', [$this, 'init_classes']);

        // Check for DB upgrades on admin init (handles plugin updates)
        add_action('admin_init', [$this, 'maybe_upgrade_db']);
    }

    /**
     * Check if DB upgrade is needed and run it
     */
    public function maybe_upgrade_db(): void {
        $db_version = get_option('irp_db_version', '1.0.0');
        if (version_compare($db_version, IRP_VERSION, '<')) {
            IRP_Activator::activate();
        }
    }
    
    public function load_textdomain(): void {
        load_plugin_textdomain(
            'immobilien-rechner-pro',
            false,
            dirname(IRP_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    public function init_classes(): void {
        new IRP_Assets();
        new IRP_Shortcode();
        new IRP_Rest_API();
        new IRP_GitHub_Updater();

        if (is_admin()) {
            new IRP_Admin();
        }
    }
}

/**
 * Initialize plugin
 */
function immobilien_rechner_pro(): Immobilien_Rechner_Pro {
    return Immobilien_Rechner_Pro::instance();
}

// Start the plugin
immobilien_rechner_pro();
