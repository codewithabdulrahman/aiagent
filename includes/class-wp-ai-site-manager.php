<?php
/**
 * Main plugin class
 */
class WP_AI_Site_Manager {
    
    private $monitor;
    private $ai;
    private $reports;
    private $settings;
    private $ga4_report;
    
    public function __construct() {
        $this->monitor = new WP_AI_Site_Manager_Monitor();
        $this->ai = new WP_AI_Site_Manager_AI();
        $this->reports = new WP_AI_Site_Manager_Reports();
        $this->settings = new WP_AI_Site_Manager_Settings();
    }
    
    public function init() {
        // Initialize monitoring
        $this->monitor->init();
        
        // Initialize AI tools
        $this->ai->init();
        
        // Initialize reports
        $this->reports->init();
        
        // Initialize settings
        $this->settings->init();
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Add dashboard widget
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
        
        // Add admin footer chat bar
        add_action('admin_footer', array($this, 'add_admin_chat_bar'));
        
        // Enqueue admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Add cron hooks
        add_action('wp_aism_file_scan', array($this->monitor, 'scan_files'));
        add_action('wp_aism_daily_report', array($this->reports, 'generate_daily_report'));
        
        // Add WordPress activity hooks
        $this->add_activity_hooks();
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('AI Site Manager', 'wp-ai-site-manager'),
            __('AI Site Manager', 'wp-ai-site-manager'),
            'manage_options',
            'wp-ai-site-manager',
            array($this->settings, 'render_settings_page'),
            'dashicons-shield-alt',
            30
        );
        
        add_submenu_page(
            'wp-ai-site-manager',
            __('Settings', 'wp-ai-site-manager'),
            __('Settings', 'wp-ai-site-manager'),
            'manage_options',
            'wp-ai-site-manager',
            array($this->settings, 'render_settings_page')
        );
        
        add_submenu_page(
            'wp-ai-site-manager',
            __('Activity Logs', 'wp-ai-site-manager'),
            __('Activity Logs', 'wp-ai-site-manager'),
            'manage_options',
            'wp-ai-site-manager-logs',
            array($this->monitor, 'render_logs_page')
        );
        
        add_submenu_page(
            'wp-ai-site-manager',
            __('AI Tools', 'wp-ai-site-manager'),
            __('AI Tools', 'wp-ai-site-manager'),
            'edit_posts',
            'wp-ai-site-manager-ai',
            array($this->ai, 'render_ai_page')
        );

        // Add Google Analytics 4 Report submenu
        require_once dirname(__FILE__) . '/class-wp-ai-site-manager-ga4-report.php';
        $this->ga4_report = new WP_AI_Site_Manager_GA4_Report();
        add_submenu_page(
            'wp-ai-site-manager',
            __('Report', 'wp-ai-site-manager'),
            __('Report', 'wp-ai-site-manager'),
            'manage_options',
            'wp-ai-site-manager-ga4-report',
            array($this->ga4_report, 'render_page')
        );
        }

    /**
     * Render GA4 Report Page in admin
     */
    
    
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'wp_aism_dashboard_widget',
            __('AI Site Manager - Recent Activity', 'wp-ai-site-manager'),
            array($this->monitor, 'render_dashboard_widget')
        );
    }
    
    public function add_admin_chat_bar() {
        if (current_user_can('edit_posts')) {
            $this->ai->render_chat_bar();
        }
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wp-ai-site-manager') !== false || $hook === 'index.php') {
            wp_enqueue_script(
                'chart-js',
                'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js',
                array(),
                '3.9.1',
                true
            );
            
            wp_enqueue_script(
                'wp-aism-admin',
                WP_AISM_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery', 'chart-js'),
                WP_AISM_VERSION,
                true
            );
            
            wp_enqueue_style(
                'wp-aism-admin',
                WP_AISM_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                WP_AISM_VERSION
            );
            
            wp_localize_script('wp-aism-admin', 'wpAISM', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'adminUrl' => admin_url(),
                'nonce' => wp_create_nonce('wp_aism_nonce'),
                'realTimeUpdates' => true,
                'strings' => array(
                    'loading' => __('Loading...', 'wp-ai-site-manager'),
                    'error' => __('An error occurred', 'wp-ai-site-manager')
                )
            ));
        }
    }
    
    private function add_activity_hooks() {
        // Post actions
        add_action('save_post', array($this->   monitor, 'log_post_action'), 10, 3);
        add_action('delete_post', array($this->monitor, 'log_post_deletion'));
        
        // User actions
        add_action('wp_login', array($this->monitor, 'log_user_login'));
        add_action('wp_login_failed', array($this->monitor, 'log_login_failed'));
        add_action('user_register', array($this->monitor, 'log_user_registration'));
        add_action('set_user_role', array($this->monitor, 'log_role_change'), 10, 3);
        
        // Plugin/Theme actions
        add_action('activated_plugin', array($this->monitor, 'log_plugin_activation'));
        add_action('deactivated_plugin', array($this->monitor, 'log_plugin_deactivation'));
        add_action('switch_theme', array($this->monitor, 'log_theme_change'));
    }


    // ...existing code...
}
