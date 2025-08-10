<?php
/**
 * Plugin Activation Test
 * 
 * This file tests if the plugin can be loaded without errors
 * 
 * @package WP_AI_Site_Manager
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Test plugin activation
 */
function wp_aism_test_activation() {
    $errors = array();
    
    // Test if required classes can be instantiated
    try {
        $monitor = new WP_AI_Site_Manager_Monitor();
        $ai = new WP_AI_Site_Manager_AI();
        $reports = new WP_AI_Site_Manager_Reports();
        $settings = new WP_AI_Site_Manager_Settings();
        $features = new WP_AI_Site_Manager_Features();
    } catch (Exception $e) {
        $errors[] = 'Class instantiation failed: ' . $e->getMessage();
    }
    
    // Test if required constants are defined
    if (!defined('WP_AISM_VERSION')) {
        $errors[] = 'WP_AISM_VERSION constant not defined';
    }
    
    if (!defined('WP_AISM_PLUGIN_DIR')) {
        $errors[] = 'WP_AISM_PLUGIN_DIR constant not defined';
    }
    
    if (!defined('WP_AISM_PLUGIN_URL')) {
        $errors[] = 'WP_AISM_PLUGIN_URL constant not defined';
    }
    
    // Test if required files exist
    $required_files = array(
        'templates/settings-page.php',
        'templates/dashboard-widget.php',
        'templates/logs-page.php',
        'templates/ai-page.php',
        'assets/css/admin.css',
        'assets/js/admin.js'
    );
    
    foreach ($required_files as $file) {
        if (!file_exists(WP_AISM_PLUGIN_DIR . $file)) {
            $errors[] = 'Required file missing: ' . $file;
        }
    }
    
    // Test if database tables can be created
    try {
        WP_AI_Site_Manager_Monitor::create_tables();
        WP_AI_Site_Manager_AI::create_usage_table();
    } catch (Exception $e) {
        $errors[] = 'Database table creation failed: ' . $e->getMessage();
    }
    
    // Return results
    if (empty($errors)) {
        return array(
            'status' => 'success',
            'message' => 'Plugin activation test passed successfully',
            'errors' => array()
        );
    } else {
        return array(
            'status' => 'error',
            'message' => 'Plugin activation test failed',
            'errors' => $errors
        );
    }
}

/**
 * Run activation test
 */
if (isset($_GET['test_activation']) && current_user_can('manage_options')) {
    $result = wp_aism_test_activation();
    
    if ($result['status'] === 'success') {
        echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
        if (!empty($result['errors'])) {
            echo '<ul>';
            foreach ($result['errors'] as $error) {
                echo '<li>' . esc_html($error) . '</li>';
            }
            echo '</ul>';
        }
    }
}
