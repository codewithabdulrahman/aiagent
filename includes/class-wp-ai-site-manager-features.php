<?php
/**
 * Feature Gating and Plan Management
 * 
 * @package WP_AI_Site_Manager
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_AI_Site_Manager_Features {
    
    private $fs;
    private $current_plan;
    private $is_premium;
    
    public function __construct() {
        $this->init_freemius();
        $this->init_hooks();
    }
    
    private function init_freemius() {
        if (function_exists('wp_ai_site_manager_fs')) {
            $this->fs = wp_ai_site_manager_fs();
            $this->is_premium = $this->fs->is_paying();
            $this->current_plan = $this->get_current_plan();
        } else {
            $this->fs = null;
            $this->is_premium = false;
            $this->current_plan = 'free';
        }
    }
    
    private function init_hooks() {
        // Add feature checks throughout the plugin
        add_filter('wp_aism_can_use_advanced_monitoring', array($this, 'can_use_advanced_monitoring'));
        add_filter('wp_aism_can_use_unlimited_ai', array($this, 'can_use_unlimited_ai'));
        add_filter('wp_aism_can_use_advanced_ai', array($this, 'can_use_advanced_ai'));
        add_filter('wp_aism_can_use_unlimited_export', array($this, 'can_use_unlimited_export'));
        add_filter('wp_aism_can_use_priority_support', array($this, 'can_use_priority_support'));
        add_filter('wp_aism_can_use_white_label', array($this, 'can_use_white_label'));
        add_filter('wp_aism_can_use_custom_integrations', array($this, 'can_use_custom_integrations'));
    }
    
    /**
     * Get current user's plan
     */
    private function get_current_plan() {
        if (!$this->fs) {
            return 'free';
        }
        
        if ($this->fs->is_free_plan()) {
            return 'free';
        }
        
        $plan = $this->fs->get_plan();
        if (!$plan) {
            return 'free';
        }
        
        switch ($plan->id) {
            case 1: // Personal Plan
                return 'personal';
            case 2: // Professional Plan
                return 'professional';
            case 3: // Business Plan
                return 'business';
            case 4: // Enterprise Plan
                return 'enterprise';
            default:
                return 'free';
        }
    }
    
    /**
     * Check if user can use advanced monitoring features
     */
    public function can_use_advanced_monitoring() {
        return $this->is_premium;
    }
    
    /**
     * Check if user can use unlimited AI requests
     */
    public function can_use_unlimited_ai() {
        return $this->is_premium;
    }
    
    /**
     * Check if user can use advanced AI models
     */
    public function can_use_advanced_ai() {
        return in_array($this->current_plan, array('professional', 'business', 'enterprise'));
    }
    
    /**
     * Check if user can export unlimited data
     */
    public function can_use_unlimited_export() {
        return in_array($this->current_plan, array('business', 'enterprise'));
    }
    
    /**
     * Check if user can use priority support
     */
    public function can_use_priority_support() {
        return in_array($this->current_plan, array('professional', 'business', 'enterprise'));
    }
    
    /**
     * Check if user can use white-label options
     */
    public function can_use_white_label() {
        return $this->current_plan === 'enterprise';
    }
    
    /**
     * Check if user can use custom integrations
     */
    public function can_use_custom_integrations() {
        return in_array($this->current_plan, array('business', 'enterprise'));
    }
    
    /**
     * Get monitoring directories based on plan
     */
    public function get_monitoring_directories() {
        if ($this->can_use_advanced_monitoring()) {
            return array(
                'wp-content',
                'wp-includes',
                'wp-admin',
                'root'
            );
        }
        
        // Free version: only plugins directory
        return array('wp-content/plugins');
    }
    
    /**
     * Get file types to monitor based on plan
     */
    public function get_monitored_file_types() {
        if ($this->can_use_advanced_monitoring()) {
            return array('php', 'js', 'css', 'html', 'txt', 'xml', 'json');
        }
        
        // Free version: PHP files only
        return array('php');
    }
    
    /**
     * Get AI daily limit based on plan
     */
    public function get_ai_daily_limit($user_role = 'administrator') {
        if ($this->can_use_unlimited_ai()) {
            return 1000; // Premium: 1000 requests per day
        }
        
        // Free version limits
        $free_limits = array(
            'administrator' => 10,
            'editor' => 10,
            'author' => 10,
            'contributor' => 5
        );
        
        return isset($free_limits[$user_role]) ? $free_limits[$user_role] : 5;
    }
    
    /**
     * Get scan frequency options based on plan
     */
    public function get_scan_frequency_options() {
        if ($this->can_use_advanced_monitoring()) {
            return array(
                'daily' => __('Daily', 'wp-ai-site-manager'),
                'hourly' => __('Hourly', 'wp-ai-site-manager'),
                'realtime' => __('Real-time', 'wp-ai-site-manager')
            );
        }
        
        // Free version: daily only
        return array(
            'daily' => __('Daily', 'wp-ai-site-manager')
        );
    }
    
    /**
     * Get log retention period based on plan
     */
    public function get_log_retention_days() {
        if ($this->can_use_advanced_monitoring()) {
            return 365; // Premium: 1 year
        }
        
        return 30; // Free version: 30 days
    }
    
    /**
     * Get export limit based on plan
     */
    public function get_export_limit() {
        if ($this->can_use_unlimited_export()) {
            return -1; // Unlimited
        }
        
        return 100; // Free version: 100 records max
    }
    
    /**
     * Get number of sites allowed based on plan
     */
    public function get_allowed_sites() {
        switch ($this->current_plan) {
            case 'personal':
                return 1;
            case 'professional':
                return 5;
            case 'business':
                return 25;
            case 'enterprise':
                return -1; // Unlimited
            default:
                return 1;
        }
    }
    
    /**
     * Check if feature is available
     */
    public function is_feature_available($feature) {
        switch ($feature) {
            case 'advanced_monitoring':
                return $this->can_use_advanced_monitoring();
            case 'unlimited_ai':
                return $this->can_use_unlimited_ai();
            case 'advanced_ai_models':
                return $this->can_use_advanced_ai();
            case 'unlimited_export':
                return $this->can_use_unlimited_export();
            case 'priority_support':
                return $this->can_use_priority_support();
            case 'white_label':
                return $this->can_use_white_label();
            case 'custom_integrations':
                return $this->can_use_custom_integrations();
            case 'real_time_monitoring':
                return $this->can_use_advanced_monitoring();
            case 'advanced_reports':
                return $this->can_use_advanced_monitoring();
            case 'security_scoring':
                return in_array($this->current_plan, array('business', 'enterprise'));
            default:
                return false;
        }
    }
    
    /**
     * Get upgrade URL for specific feature
     */
    public function get_upgrade_url($feature = '') {
        if (!$this->fs) {
            return '#';
        }
        
        if ($feature) {
            return $this->fs->get_upgrade_url('pro', $feature);
        }
        
        return $this->fs->get_upgrade_url();
    }
    
    /**
     * Get current plan name
     */
    public function get_plan_name() {
        switch ($this->current_plan) {
            case 'free':
                return __('Free', 'wp-ai-site-manager');
            case 'personal':
                return __('Personal', 'wp-ai-site-manager');
            case 'professional':
                return __('Professional', 'wp-ai-site-manager');
            case 'business':
                return __('Business', 'wp-ai-site-manager');
            case 'enterprise':
                return __('Enterprise', 'wp-ai-site-manager');
            default:
                return __('Free', 'wp-ai-site-manager');
        }
    }
    
    /**
     * Get plan price
     */
    public function get_plan_price() {
        switch ($this->current_plan) {
            case 'free':
                return __('Free', 'wp-ai-site-manager');
            case 'personal':
                return '$39/year';
            case 'professional':
                return '$79/year';
            case 'business':
                return '$149/year';
            case 'enterprise':
                return '$299/year';
            default:
                return __('Free', 'wp-ai-site-manager');
        }
    }
    
    /**
     * Check if user is on trial
     */
    public function is_trial() {
        if (!$this->fs) {
            return false;
        }
        
        return $this->fs->is_trial();
    }
    
    /**
     * Get trial days remaining
     */
    public function get_trial_days_remaining() {
        if (!$this->fs || !$this->is_trial()) {
            return 0;
        }
        
        return $this->fs->get_trial_days_remaining();
    }
    
    /**
     * Get feature comparison data
     */
    public function get_feature_comparison() {
        return array(
            'file_monitoring' => array(
                'free' => __('Basic (plugins only)', 'wp-ai-site-manager'),
                'personal' => __('Advanced (all directories)', 'wp-ai-site-manager'),
                'professional' => __('Advanced (all directories)', 'wp-ai-site-manager'),
                'business' => __('Advanced (all directories)', 'wp-ai-site-manager'),
                'enterprise' => __('Advanced (all directories)', 'wp-ai-site-manager')
            ),
            'ai_requests' => array(
                'free' => '10/day',
                'personal' => 'Unlimited',
                'professional' => 'Unlimited',
                'business' => 'Unlimited',
                'enterprise' => 'Unlimited'
            ),
            'scan_frequency' => array(
                'free' => __('Daily only', 'wp-ai-site-manager'),
                'personal' => __('Hourly + Real-time', 'wp-ai-site-manager'),
                'professional' => __('Hourly + Real-time', 'wp-ai-site-manager'),
                'business' => __('Hourly + Real-time', 'wp-ai-site-manager'),
                'enterprise' => __('Hourly + Real-time', 'wp-ai-site-manager')
            ),
            'support' => array(
                'free' => __('Community', 'wp-ai-site-manager'),
                'personal' => __('Email', 'wp-ai-site-manager'),
                'professional' => __('Priority Email', 'wp-ai-site-manager'),
                'business' => __('Priority Email', 'wp-ai-site-manager'),
                'enterprise' => __('24/7 Priority', 'wp-ai-site-manager')
            ),
            'sites' => array(
                'free' => '1',
                'personal' => '1',
                'professional' => '5',
                'business' => '25',
                'enterprise' => __('Unlimited', 'wp-ai-site-manager')
            )
        );
    }
}
