<?php
/**
 * Plugin Name: WP AI Site Manager Pro
 * Plugin URI: https://yourwebsite.com/wp-ai-site-manager
 * Description: Professional AI-powered WordPress security monitoring, content generation, and site management. Features file integrity monitoring, AI content tools, activity logging, automated reporting, and real-time security alerts.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * Author: Your Company Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-ai-site-manager
 * Domain Path: /languages
 * Network: false
 * 
 * @package WP_AI_Site_Manager
 * @version 1.0.0
 * @author Your Company Name
 * @copyright Copyright (c) 2024 Your Company Name
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include plugin information
require_once dirname(__FILE__) . '/plugin-info.php';

// Define plugin constants
define('WP_AISM_VERSION', WP_AISM_PLUGIN_VERSION);
define('WP_AISM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_AISM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_AISM_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once WP_AISM_PLUGIN_DIR . 'includes/class-wp-ai-site-manager.php';
require_once WP_AISM_PLUGIN_DIR . 'includes/class-wp-ai-site-manager-monitor.php';
require_once WP_AISM_PLUGIN_DIR . 'includes/class-wp-ai-site-manager-ai.php';
require_once WP_AISM_PLUGIN_DIR . 'includes/class-wp-ai-site-manager-reports.php';
require_once WP_AISM_PLUGIN_DIR . 'includes/class-wp-ai-site-manager-settings.php';
require_once WP_AISM_PLUGIN_DIR . 'includes/class-wp-ai-site-manager-features.php';
require_once WP_AISM_PLUGIN_DIR . 'includes/class-wp-ai-site-manager-upgrade-prompts.php';

// Initialize Freemius
require_once dirname(__FILE__) . '/freemius/start.php';

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
        wp_schedule_event(time(), 'daily', 'wp_aism_file_scan');
    }
    if (!wp_next_scheduled('wp_aism_daily_report')) {
        wp_schedule_event(time(), 'daily', 'wp_aism_daily_report');
    }
    
    // Set default options
    $default_options = array(
        'scan_interval' => 'daily',
        'alert_emails' => get_option('admin_email'),
        'file_types' => array('php'), // Free version: PHP only
        'openai_api_key' => '',
        'ai_usage_limits' => array(
            'administrator' => 10, // Free version: 10 requests per day
            'editor' => 10,
            'author' => 10,
            'contributor' => 5
        ),
        'report_frequency' => 'daily',
        'report_recipients' => get_option('admin_email'),
        'monitoring_directories' => array('wp-content/plugins'), // Free version: plugins only
        'max_log_retention' => 30, // Free version: 30 days
        'export_limit' => 100 // Free version: 100 records max
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
