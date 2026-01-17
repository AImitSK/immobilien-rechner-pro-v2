<?php
/**
 * Uninstall script
 * 
 * This file runs when the plugin is deleted from WordPress admin.
 * It removes all plugin data from the database.
 */

// Exit if not called from WordPress uninstall
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove plugin options
delete_option('irp_settings');
delete_option('irp_db_version');

// Remove database tables
global $wpdb;

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}irp_leads");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}irp_calculations");

// Clear any cached data
wp_cache_flush();
