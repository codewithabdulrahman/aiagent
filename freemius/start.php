<?php
/**
 * Freemius SDK Integration
 * 
 * @package WP_AI_Site_Manager
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include Freemius SDK
if (!function_exists('fs_dynamic_init')) {
    require_once dirname(__FILE__) . '/freemius.php';
}

// Initialize Freemius
if (!function_exists('wp_ai_site_manager_fs')) {
    function wp_ai_site_manager_fs() {
        global $wp_ai_site_manager_fs;
        
        if (!isset($wp_ai_site_manager_fs)) {
            $wp_ai_site_manager_fs = fs_dynamic_init(array(
                'id'                  => '12345', // Replace with your actual Freemius app ID
                'slug'                => 'wp-ai-site-manager',
                'type'                => 'plugin',
                'public_key'          => 'pk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', // Replace with your actual public key
                'is_premium'          => false,
                'is_premium_only'     => false,
                'has_addons'          => false,
                'has_paid_plans'      => true,
                'has_affiliation'     => 'selected',
                'menu'                => array(
                    'slug'           => 'wp-ai-site-manager',
                    'first-path'     => 'admin.php?page=wp-ai-site-manager',
                    'support'        => false,
                    'pricing'        => true,
                    'addons'         => false,
                    'contact'        => false,
                ),
                'is_live'            => true,
                'trial'               => array(
                    'days'          => 7,
                    'is_require_payment' => false,
                ),
            ));
        }
        
        return $wp_ai_site_manager_fs;
    }
    
    wp_ai_site_manager_fs();
}
