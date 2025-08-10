<?php
/**
 * Plugin settings and configuration
 */
class WP_AI_Site_Manager_Settings {
    
    private $options;
    
    public function __construct() {
        $this->options = get_option('wp_aism_options', array());
    }
    
    public function init() {
        // Add AJAX handlers
        add_action('wp_ajax_wp_aism_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_wp_aism_test_openai', array($this, 'ajax_test_openai'));
        
        // Add settings link to plugins page
        add_filter('plugin_action_links_' . WP_AISM_PLUGIN_BASENAME, array($this, 'add_settings_link'));
    }
    
    public function render_settings_page() {
        if (isset($_POST['submit'])) {
            $this->save_settings();
        }
        
        $this->options = get_option('wp_aism_options', array());
        include WP_AISM_PLUGIN_DIR . 'templates/settings-page.php';
    }
    
    private function save_settings() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        check_admin_referer('wp_aism_settings', 'wp_aism_nonce');
        
        $options = array();
        
        // Monitoring settings
        $options['scan_interval'] = sanitize_text_field($_POST['scan_interval'] ?? 'hourly');
        $options['alert_emails'] = sanitize_textarea_field($_POST['alert_emails'] ?? '');
        $options['file_types'] = isset($_POST['file_types']) ? array_map('sanitize_text_field', $_POST['file_types']) : array('php', 'js', 'css');
        
        // AI settings
        $options['openai_api_key'] = sanitize_text_field($_POST['openai_api_key'] ?? '');
        $options['ai_usage_limits'] = array(
            'administrator' => intval($_POST['ai_limit_admin'] ?? 100),
            'editor' => intval($_POST['ai_limit_editor'] ?? 50),
            'author' => intval($_POST['ai_limit_author'] ?? 25),
            'contributor' => intval($_POST['ai_limit_contributor'] ?? 10)
        );
        
        // Report settings
        $options['report_frequency'] = sanitize_text_field($_POST['report_frequency'] ?? 'daily');
        $options['report_recipients'] = sanitize_textarea_field($_POST['report_recipients'] ?? '');
        
        // Update options
        update_option('wp_aism_options', $options);
        
        // Update cron schedules if scan interval changed
        $this->update_cron_schedules($options['scan_interval']);
        
        // Show success message
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 __('Settings saved successfully!', 'wp-ai-site-manager') . '</p></div>';
        });
        
        $this->options = $options;
    }
    
    private function update_cron_schedules($interval) {
        // Clear existing schedules
        wp_clear_scheduled_hook('wp_aism_file_scan');
        
        // Set new schedule
        if (!wp_next_scheduled('wp_aism_file_scan')) {
            wp_schedule_event(time(), $interval, 'wp_aism_file_scan');
        }
    }
    
    public function ajax_save_settings() {
        check_ajax_referer('wp_aism_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'wp-ai-site-manager'));
        }
        
        $this->save_settings();
        wp_send_json_success(__('Settings saved successfully', 'wp-ai-site-manager'));
    }
    
    public function ajax_test_openai() {
        check_ajax_referer('wp_aism_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'wp-ai-site-manager'));
        }
        
        $api_key = sanitize_text_field($_POST['api_key'] ?? '');
        
        if (empty($api_key)) {
            wp_send_json_error(__('API key is required', 'wp-ai-site-manager'));
        }
        
        // Test the API key with a simple request
        $url = 'https://api.openai.com/v1/models';
        $headers = array(
            'Authorization' => 'Bearer ' . $api_key
        );
        
        $response = wp_remote_get($url, array(
            'headers' => $headers,
            'timeout' => 15
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(__('Connection error: ', 'wp-ai-site-manager') . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            wp_send_json_error(__('API Error: ', 'wp-ai-site-manager') . $data['error']['message']);
        }
        
        if (isset($data['data'])) {
            wp_send_json_success(__('API key is valid! Connected to OpenAI successfully.', 'wp-ai-site-manager'));
        }
        
        wp_send_json_error(__('Unexpected response from OpenAI API', 'wp-ai-site-manager'));
    }
    
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=wp-ai-site-manager') . '">' . 
                        __('Settings', 'wp-ai-site-manager') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    public function get_option($key, $default = '') {
        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }
    
    public function get_scan_intervals() {
        return array(
            'hourly' => __('Hourly', 'wp-ai-site-manager'),
            'twicedaily' => __('Twice Daily', 'wp-ai-site-manager'),
            'daily' => __('Daily', 'wp-ai-site-manager')
        );
    }
    
    public function get_report_frequencies() {
        return array(
            'daily' => __('Daily', 'wp-ai-site-manager'),
            'weekly' => __('Weekly', 'wp-ai-site-manager'),
            'monthly' => __('Monthly', 'wp-ai-site-manager')
        );
    }
    
    public function get_file_types() {
        return array(
            'php' => 'PHP',
            'js' => 'JavaScript',
            'css' => 'CSS',
            'html' => 'HTML',
            'txt' => 'Text',
            'xml' => 'XML',
            'json' => 'JSON',
            'sql' => 'SQL'
        );
    }
    
    public function get_user_roles() {
        $roles = wp_roles()->get_names();
        $filtered_roles = array();
        
        // Only include roles that can edit posts
        foreach ($roles as $role_key => $role_name) {
            $role = get_role($role_key);
            if ($role && $role->has_cap('edit_posts')) {
                $filtered_roles[$role_key] = $role_name;
            }
        }
        
        return $filtered_roles;
    }
    
    public function validate_emails($emails_string) {
        $emails = array_filter(array_map('trim', explode(',', $emails_string)));
        $valid_emails = array();
        
        foreach ($emails as $email) {
            if (is_email($email)) {
                $valid_emails[] = $email;
            }
        }
        
        return implode(', ', $valid_emails);
    }
    
    public function get_plugin_status() {
        $status = array();
        
        // Check if OpenAI API key is configured
        $api_key = $this->get_option('openai_api_key');
        $status['openai'] = array(
            'configured' => !empty($api_key),
            'message' => !empty($api_key) ? __('OpenAI API configured', 'wp-ai-site-manager') : __('OpenAI API not configured', 'wp-ai-site-manager')
        );
        
        // Check if cron jobs are scheduled
        $file_scan_scheduled = wp_next_scheduled('wp_aism_file_scan');
        $daily_report_scheduled = wp_next_scheduled('wp_aism_daily_report');
        
        $status['cron'] = array(
            'file_scan' => $file_scan_scheduled ? __('Scheduled', 'wp-ai-site-manager') : __('Not scheduled', 'wp-ai-site-manager'),
            'daily_report' => $daily_report_scheduled ? __('Scheduled', 'wp-ai-site-manager') : __('Not scheduled', 'wp-ai-site-manager')
        );
        
        // Check database tables
        global $wpdb;
        $logs_table = $wpdb->prefix . 'wp_aism_logs';
        $hashes_table = $wpdb->prefix . 'wp_aism_file_hashes';
        $usage_table = $wpdb->prefix . 'wp_aism_ai_usage';
        
        $status['database'] = array(
            'logs_table' => $wpdb->get_var("SHOW TABLES LIKE '$logs_table'") ? __('Exists', 'wp-ai-site-manager') : __('Missing', 'wp-ai-site-manager'),
            'hashes_table' => $wpdb->get_var("SHOW TABLES LIKE '$hashes_table'") ? __('Exists', 'wp-ai-site-manager') : __('Missing', 'wp-ai-site-manager'),
            'usage_table' => $wpdb->get_var("SHOW TABLES LIKE '$usage_table'") ? __('Exists', 'wp-ai-site-manager') : __('Missing', 'wp-ai-site-manager')
        );
        
        return $status;
    }
}
