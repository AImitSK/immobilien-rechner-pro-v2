<?php
/**
 * Plugin deactivation handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class IRP_Deactivator {
    
    public static function deactivate(): void {
        flush_rewrite_rules();
        // Note: We don't delete tables on deactivation
        // Tables are only removed on uninstall (see uninstall.php)
    }
}
