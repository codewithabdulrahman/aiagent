<?php
/**
 * Freemius SDK - Simplified Version
 * 
 * @package WP_AI_Site_Manager
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Mock Freemius functions for development
if (!function_exists('fs_dynamic_init')) {
    function fs_dynamic_init($config) {
        return new Mock_Freemius($config);
    }
}

class Mock_Freemius {
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
    }
    
    public function is_paying() {
        return false; // Free version
    }
    
    public function is_free_plan() {
        return true;
    }
    
    public function get_plan() {
        return null; // No plan
    }
    
    public function get_slug() {
        return $this->config['slug'];
    }
    
    public function get_id() {
        return $this->config['id'];
    }
}
