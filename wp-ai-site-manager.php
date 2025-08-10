<?php
/**
 * Plugin Name: WP AI Site Manager
 * Plugin URI: https://example.com/wp-ai-site-manager
 * Description: An AI-powered WordPress assistant that monitors site security and helps create content. Protects & Powers Your WordPress.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: wp-ai-site-manager
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WP_AISM_VERSION', '1.0.0');
define('WP_AISM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_AISM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_AISM_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once WP_AISM_PLUGIN_DIR . 'includes/class-wp-ai-site-manager.php';
require_once WP_AISM_PLUGIN_DIR . 'includes/class-wp-ai-site-manager-monitor.php';
require_once WP_AISM_PLUGIN_DIR . 'includes/class-wp-ai-site-manager-ai.php';
require_once WP_AISM_PLUGIN_DIR . 'includes/class-wp-ai-site-manager-reports.php';
require_once WP_AISM_PLUGIN_DIR . 'includes/class-wp-ai-site-manager-settings.php';

// Initialize the plugin
function wp_ai_site_manager_init() {
    $plugin = new WP_AI_Site_Manager();
    $plugin->init();
}
add_action('plugins_loaded', 'wp_ai_site_manager_init');

// Activation hook
register_activation_hook(__FILE__, 'wp_ai_site_manager_activate');
function wp_ai_site_manager_activate() {
    // Create database tables
    WP_AI_Site_Manager_Monitor::create_tables();
    WP_AI_Site_Manager_AI::create_usage_table();
    
    // Schedule cron jobs
    if (!wp_next_scheduled('wp_aism_file_scan')) {
        wp_schedule_event(time(), 'hourly', 'wp_aism_file_scan');
    }
    if (!wp_next_scheduled('wp_aism_daily_report')) {
        wp_schedule_event(time(), 'daily', 'wp_aism_daily_report');
    }
    
    // Set default options
    $default_options = array(
        'scan_interval' => 'hourly',
        'alert_emails' => get_option('admin_email'),
        'file_types' => array('php', 'js', 'css', 'html', 'txt'),
        'openai_api_key' => '',
        'ai_usage_limits' => array(
            'administrator' => 100,
            'editor' => 50,
            'author' => 25
        ),
        'report_frequency' => 'daily',
        'report_recipients' => get_option('admin_email')
    );
    add_option('wp_aism_options', $default_options);
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'wp_ai_site_manager_deactivate');
function wp_ai_site_manager_deactivate() {
    // Clear scheduled cron jobs
    wp_clear_scheduled_hook('wp_aism_file_scan');
    wp_clear_scheduled_hook('wp_aism_daily_report');
}
