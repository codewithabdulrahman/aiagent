<?php
/**
 * Uninstall script for WP AI Site Manager
 * 
 * This file is executed when the plugin is deleted from WordPress
 * 
 * @package WP_AI_Site_Manager
 * @since 1.0.0
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('wp_aism_options');
delete_option('wp_aism_last_scan');
delete_option('wp_aism_last_report');

// Delete transients
delete_transient('wp_aism_last_alert_time');
delete_transient('wp_aism_alert_count');
delete_transient('wp_aism_last_scan_time');
delete_transient('wp_aism_fix_notice_dismissed');

// Clear scheduled cron jobs
wp_clear_scheduled_hook('wp_aism_file_scan');
wp_clear_scheduled_hook('wp_aism_daily_report');

// Drop custom database tables
global $wpdb;

$tables = array(
    $wpdb->prefix . 'wp_aism_logs',
    $wpdb->prefix . 'wp_aism_file_hashes',
    $wpdb->prefix . 'wp_aism_ai_usage'
);

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS $table");
}

// Clean up any remaining data
$wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'wp_aism_%'");
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'wp_aism_%'");

// Remove any custom capabilities
$roles = array('administrator', 'editor', 'author', 'contributor');
foreach ($roles as $role) {
    $role_obj = get_role($role);
    if ($role_obj) {
        $role_obj->remove_cap('wp_aism_manage_settings');
        $role_obj->remove_cap('wp_aism_view_logs');
        $role_obj->remove_cap('wp_aism_use_ai_tools');
    }
}
