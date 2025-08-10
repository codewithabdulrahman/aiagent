<?php
/**
 * Upgrade Prompts and Conversion Optimization
 * 
 * @package WP_AI_Site_Manager
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_AI_Site_Manager_Upgrade_Prompts {
    
    private $features;
    private $fs;
    
    public function __construct() {
        $this->features = new WP_AI_Site_Manager_Features();
        $this->fs = function_exists('wp_ai_site_manager_fs') ? wp_ai_site_manager_fs() : null;
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Show upgrade prompts at strategic locations
        add_action('admin_notices', array($this, 'show_ai_limit_notice'));
        add_action('admin_notices', array($this, 'show_monitoring_limit_notice'));
        add_action('admin_notices', array($this, 'show_trial_notice'));
        add_action('admin_notices', array($this, 'show_feature_upgrade_notice'));
        
        // Add upgrade prompts to admin pages
        add_action('wp_aism_after_dashboard_widget', array($this, 'show_dashboard_upgrade_prompt'));
        add_action('wp_aism_after_ai_page', array($this, 'show_ai_upgrade_prompt'));
        add_action('wp_aism_after_logs_page', array($this, 'show_logs_upgrade_prompt'));
        add_action('wp_aism_after_settings_page', array($this, 'show_settings_upgrade_prompt'));
        
        // Add upgrade prompts to AJAX responses
        add_filter('wp_aism_ai_response', array($this, 'add_ai_upgrade_prompt'));
        add_filter('wp_aism_export_response', array($this, 'add_export_upgrade_prompt'));
        
        // Add upgrade prompts to feature limits
        add_action('wp_aism_before_ai_generation', array($this, 'check_ai_limits'));
        add_action('wp_aism_before_export', array($this, 'check_export_limits'));
    }
    
    /**
     * Show AI usage limit notice
     */
    public function show_ai_limit_notice() {
        if ($this->features->is_feature_available('unlimited_ai')) {
            return; // Already premium
        }
        
        $current_usage = $this->get_current_ai_usage();
        $daily_limit = $this->features->get_ai_daily_limit();
        
        if ($current_usage >= ($daily_limit * 0.8)) { // Show when 80% of limit reached
            $upgrade_url = $this->features->get_upgrade_url('unlimited_ai');
            
            echo '<div class="notice notice-warning is-dismissible wp-aism-upgrade-notice">';
            echo '<p><strong>WP AI Site Manager:</strong> ';
            printf(__('You\'ve used %d/%d AI requests today. ', 'wp-ai-site-manager'), $current_usage, $daily_limit);
            echo '<a href="' . esc_url($upgrade_url) . '" class="button button-primary button-small">' . __('Upgrade to Pro', 'wp-ai-site-manager') . '</a> ';
            echo __('for unlimited AI access!</p>');
            echo '</div>';
        }
    }
    
    /**
     * Show monitoring limit notice
     */
    public function show_monitoring_limit_notice() {
        if ($this->features->is_feature_available('advanced_monitoring')) {
            return; // Already premium
        }
        
        // Show this notice occasionally to encourage upgrades
        $last_shown = get_transient('wp_aism_monitoring_notice_shown');
        if (!$last_shown && rand(1, 10) === 1) { // 10% chance to show
            $upgrade_url = $this->features->get_upgrade_url('advanced_monitoring');
            
            echo '<div class="notice notice-info is-dismissible wp-aism-upgrade-notice">';
            echo '<p><strong>WP AI Site Manager:</strong> ';
            echo __('Currently monitoring plugins only. ', 'wp-ai-site-manager');
            echo '<a href="' . esc_url($upgrade_url) . '" class="button button-primary button-small">' . __('Upgrade to Pro', 'wp-ai-site-manager') . '</a> ';
            echo __('to monitor all directories and file types!', 'wp-ai-site-manager') . '</p>';
            echo '</div>';
            
            set_transient('wp_aism_monitoring_notice_shown', true, DAY_IN_SECONDS);
        }
    }
    
    /**
     * Show trial notice
     */
    public function show_trial_notice() {
        if (!$this->features->is_trial()) {
            return;
        }
        
        $days_remaining = $this->features->get_trial_days_remaining();
        $upgrade_url = $this->features->get_upgrade_url();
        
        if ($days_remaining <= 3) {
            $notice_class = 'notice-error';
            $message = sprintf(__('Your trial expires in %d days!', 'wp-ai-site-manager'), $days_remaining);
        } elseif ($days_remaining <= 7) {
            $notice_class = 'notice-warning';
            $message = sprintf(__('Your trial expires in %d days.', 'wp-ai-site-manager'), $days_remaining);
        } else {
            $notice_class = 'notice-info';
            $message = sprintf(__('You have %d days left in your trial.', 'wp-ai-site-manager'), $days_remaining);
        }
        
        echo '<div class="notice ' . $notice_class . ' is-dismissible wp-aism-trial-notice">';
        echo '<p><strong>WP AI Site Manager Trial:</strong> ' . $message . ' ';
        echo '<a href="' . esc_url($upgrade_url) . '" class="button button-primary button-small">' . __('Upgrade Now', 'wp-ai-site-manager') . '</a></p>';
        echo '</div>';
    }
    
    /**
     * Show feature upgrade notice
     */
    public function show_feature_upgrade_notice() {
        if ($this->features->is_feature_available('advanced_monitoring')) {
            return; // Already premium
        }
        
        // Show this notice occasionally
        $last_shown = get_transient('wp_aism_feature_notice_shown');
        if (!$last_shown && rand(1, 15) === 1) { // 6.7% chance to show
            $upgrade_url = $this->features->get_upgrade_url();
            
            echo '<div class="notice notice-info is-dismissible wp-aism-upgrade-notice">';
            echo '<p><strong>WP AI Site Manager:</strong> ';
            echo __('Unlock advanced features like real-time monitoring, unlimited AI requests, and priority support. ', 'wp-ai-site-manager');
            echo '<a href="' . esc_url($upgrade_url) . '" class="button button-primary button-small">' . __('View Plans', 'wp-ai-site-manager') . '</a></p>';
            echo '</div>';
            
            set_transient('wp_aism_feature_notice_shown', true, DAY_IN_SECONDS * 2);
        }
    }
    
    /**
     * Show dashboard upgrade prompt
     */
    public function show_dashboard_upgrade_prompt() {
        if ($this->features->is_feature_available('advanced_monitoring')) {
            return; // Already premium
        }
        
        $upgrade_url = $this->features->get_upgrade_url();
        
        echo '<div class="wp-aism-upgrade-prompt dashboard-prompt">';
        echo '<div class="upgrade-prompt-content">';
        echo '<h3>' . __('🚀 Unlock Premium Features', 'wp-ai-site-manager') . '</h3>';
        echo '<p>' . __('Get advanced monitoring, unlimited AI requests, and priority support.', 'wp-ai-site-manager') . '</p>';
        echo '<div class="upgrade-features">';
        echo '<span class="feature">✓ Real-time monitoring</span>';
        echo '<span class="feature">✓ Unlimited AI requests</span>';
        echo '<span class="feature">✓ Priority support</span>';
        echo '</div>';
        echo '<a href="' . esc_url($upgrade_url) . '" class="button button-primary">' . __('Upgrade Now', 'wp-ai-site-manager') . '</a>';
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Show AI page upgrade prompt
     */
    public function show_ai_upgrade_prompt() {
        if ($this->features->is_feature_available('unlimited_ai')) {
            return; // Already premium
        }
        
        $upgrade_url = $this->features->get_upgrade_url('unlimited_ai');
        $current_usage = $this->get_current_ai_usage();
        $daily_limit = $this->features->get_ai_daily_limit();
        
        echo '<div class="wp-aism-upgrade-prompt ai-prompt">';
        echo '<div class="upgrade-prompt-content">';
        echo '<h3>' . __('🤖 AI Usage Limit Reached', 'wp-ai-site-manager') . '</h3>';
        echo '<p>' . sprintf(__('You\'ve used %d/%d AI requests today.', 'wp-ai-site-manager'), $current_usage, $daily_limit) . '</p>';
        echo '<div class="upgrade-benefits">';
        echo '<span class="benefit">✓ Unlimited AI requests</span>';
        echo '<span class="benefit">✓ Advanced AI models</span>';
        echo '<span class="benefit">✓ Bulk content generation</span>';
        echo '</div>';
        echo '<a href="' . esc_url($upgrade_url) . '" class="button button-primary">' . __('Upgrade to Pro', 'wp-ai-site-manager') . '</a>';
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Show logs page upgrade prompt
     */
    public function show_logs_upgrade_prompt() {
        if ($this->features->is_feature_available('unlimited_export')) {
            return; // Already premium
        }
        
        $upgrade_url = $this->features->get_upgrade_url('unlimited_export');
        
        echo '<div class="wp-aism-upgrade-prompt logs-prompt">';
        echo '<div class="upgrade-prompt-content">';
        echo '<h3>' . __('📊 Export More Data', 'wp-ai-site-manager') . '</h3>';
        echo '<p>' . __('Free version limited to 100 records. Upgrade for unlimited exports and advanced analytics.', 'wp-ai-site-manager') . '</p>';
        echo '<div class="upgrade-benefits">';
        echo '<span class="benefit">✓ Unlimited exports</span>';
        echo '<span class="benefit">✓ Advanced analytics</span>';
        echo '<span class="benefit">✓ Custom report templates</span>';
        echo '</div>';
        echo '<a href="' . esc_url($upgrade_url) . '" class="button button-primary">' . __('Upgrade Now', 'wp-ai-site-manager') . '</a>';
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Show settings page upgrade prompt
     */
    public function show_settings_upgrade_prompt() {
        if ($this->features->is_feature_available('advanced_monitoring')) {
            return; // Already premium
        }
        
        $upgrade_url = $this->features->get_upgrade_url('advanced_monitoring');
        
        echo '<div class="wp-aism-upgrade-prompt settings-prompt">';
        echo '<div class="upgrade-prompt-content">';
        echo '<h3>' . __('⚙️ Advanced Configuration', 'wp-ai-site-manager') . '</h3>';
        echo '<p>' . __('Unlock advanced monitoring options, custom alert rules, and real-time notifications.', 'wp-ai-site-manager') . '</p>';
        echo '<div class="upgrade-benefits">';
        echo '<span class="benefit">✓ Hourly scanning</span>';
        echo '<span class="benefit">✓ Custom alert rules</span>';
        echo '<span class="benefit">✓ Real-time notifications</span>';
        echo '</div>';
        echo '<a href="' . esc_url($upgrade_url) . '" class="button button-primary">' . __('Upgrade to Pro', 'wp-ai-site-manager') . '</a>';
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Add upgrade prompt to AI responses
     */
    public function add_ai_upgrade_prompt($response) {
        if ($this->features->is_feature_available('unlimited_ai')) {
            return $response; // Already premium
        }
        
        $current_usage = $this->get_current_ai_usage();
        $daily_limit = $this->features->get_ai_daily_limit();
        
        if ($current_usage >= ($daily_limit * 0.7)) { // Show when 70% of limit reached
            $upgrade_url = $this->features->get_upgrade_url('unlimited_ai');
            
            $response['upgrade_prompt'] = array(
                'message' => sprintf(__('You\'ve used %d/%d AI requests today. Upgrade for unlimited access!', 'wp-ai-site-manager'), $current_usage, $daily_limit),
                'upgrade_url' => $upgrade_url,
                'button_text' => __('Upgrade to Pro', 'wp-ai-site-manager')
            );
        }
        
        return $response;
    }
    
    /**
     * Add upgrade prompt to export responses
     */
    public function add_export_upgrade_prompt($response) {
        if ($this->features->is_feature_available('unlimited_export')) {
            return $response; // Already premium
        }
        
        $export_limit = $this->features->get_export_limit();
        $upgrade_url = $this->features->get_upgrade_url('unlimited_export');
        
        $response['upgrade_prompt'] = array(
            'message' => sprintf(__('Free version limited to %d records. Upgrade for unlimited exports!', 'wp-ai-site-manager'), $export_limit),
            'upgrade_url' => $upgrade_url,
            'button_text' => __('Upgrade Now', 'wp-ai-site-manager')
        );
        
        return $response;
    }
    
    /**
     * Check AI limits before generation
     */
    public function check_ai_limits() {
        if ($this->features->is_feature_available('unlimited_ai')) {
            return true; // No limits
        }
        
        $current_usage = $this->get_current_ai_usage();
        $daily_limit = $this->features->get_ai_daily_limit();
        
        if ($current_usage >= $daily_limit) {
            wp_die(
                sprintf(__('Daily AI limit reached (%d requests). Please upgrade to Pro for unlimited access.', 'wp-ai-site-manager'), $daily_limit),
                __('AI Limit Reached', 'wp-ai-site-manager'),
                array('response' => 429)
            );
        }
        
        return true;
    }
    
    /**
     * Check export limits before export
     */
    public function check_export_limits() {
        if ($this->features->is_feature_available('unlimited_export')) {
            return true; // No limits
        }
        
        $export_limit = $this->features->get_export_limit();
        
        // Check if export would exceed limit
        // Implementation depends on your export logic
        
        return true;
    }
    
    /**
     * Get current AI usage for current user
     */
    private function get_current_ai_usage() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_aism_ai_usage';
        $user_id = get_current_user_id();
        $today = date('Y-m-d');
        
        $usage = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND DATE(timestamp) = %s",
            $user_id,
            $today
        ));
        
        return intval($usage);
    }
    
    /**
     * Get upgrade statistics
     */
    public function get_upgrade_stats() {
        $stats = array(
            'current_plan' => $this->features->get_plan_name(),
            'plan_price' => $this->features->get_plan_price(),
            'is_trial' => $this->features->is_trial(),
            'trial_days_remaining' => $this->features->get_trial_days_remaining(),
            'upgrade_url' => $this->features->get_upgrade_url(),
            'feature_comparison' => $this->features->get_feature_comparison()
        );
        
        return $stats;
    }
}
