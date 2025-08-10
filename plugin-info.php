<?php
/**
 * Plugin Information File
 * 
 * @package WP_AI_Site_Manager
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin Information
 */
define('WP_AISM_PLUGIN_NAME', 'WP AI Site Manager Pro');
define('WP_AISM_PLUGIN_URI', 'https://yourwebsite.com/wp-ai-site-manager');
define('WP_AISM_PLUGIN_DESCRIPTION', 'Professional AI-powered WordPress security monitoring, content generation, and site management.');
define('WP_AISM_PLUGIN_VERSION', '1.0.0');
define('WP_AISM_PLUGIN_AUTHOR', 'Your Company Name');
define('WP_AISM_PLUGIN_AUTHOR_URI', 'https://yourwebsite.com');
define('WP_AISM_PLUGIN_LICENSE', 'GPL v2 or later');
define('WP_AISM_PLUGIN_LICENSE_URI', 'https://www.gnu.org/licenses/gpl-2.0.html');
define('WP_AISM_PLUGIN_TEXT_DOMAIN', 'wp-ai-site-manager');
define('WP_AISM_PLUGIN_DOMAIN_PATH', '/languages');
define('WP_AISM_PLUGIN_NETWORK', false);

/**
 * Requirements
 */
define('WP_AISM_REQUIRES_WP', '5.0');
define('WP_AISM_REQUIRES_PHP', '7.4');
define('WP_AISM_TESTED_WP', '6.8');

/**
 * Feature Flags
 */
define('WP_AISM_FEATURE_AI_TOOLS', true);
define('WP_AISM_FEATURE_FILE_MONITORING', true);
define('WP_AISM_FEATURE_ACTIVITY_LOGGING', true);
define('WP_AISM_FEATURE_SECURITY_ALERTS', true);
define('WP_AISM_FEATURE_DASHBOARD_WIDGET', true);
define('WP_AISM_FEATURE_REPORTS', true);
define('WP_AISM_FEATURE_FREEMIUS', true);

/**
 * API Configuration
 */
define('WP_AISM_OPENAI_API_URL', 'https://api.openai.com/v1');
define('WP_AISM_OPENAI_MODEL_DEFAULT', 'gpt-3.5-turbo');
define('WP_AISM_OPENAI_MODEL_ADVANCED', 'gpt-4');

/**
 * Default Settings
 */
define('WP_AISM_DEFAULT_SCAN_INTERVAL', 'daily');
define('WP_AISM_DEFAULT_MAX_ALERTS_PER_HOUR', 10);
define('WP_AISM_DEFAULT_ALERT_COOLDOWN', 300); // 5 minutes
define('WP_AISM_DEFAULT_SCAN_COOLDOWN', 1800); // 30 minutes
define('WP_AISM_DEFAULT_LOG_RETENTION', 30); // days
define('WP_AISM_DEFAULT_EXPORT_LIMIT', 100);

/**
 * Database Tables
 */
define('WP_AISM_TABLE_LOGS', 'wp_aism_logs');
define('WP_AISM_TABLE_FILE_HASHES', 'wp_aism_file_hashes');
define('WP_AISM_TABLE_AI_USAGE', 'wp_aism_ai_usage');

/**
 * Capabilities
 */
define('WP_AISM_CAP_MANAGE_SETTINGS', 'wp_aism_manage_settings');
define('WP_AISM_CAP_VIEW_LOGS', 'wp_aism_view_logs');
define('WP_AISM_CAP_USE_AI_TOOLS', 'wp_aism_use_ai_tools');
define('WP_AISM_CAP_EXPORT_DATA', 'wp_aism_export_data');
define('WP_AISM_CAP_VIEW_REPORTS', 'wp_aism_view_reports');
