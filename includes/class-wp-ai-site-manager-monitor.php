<?php
/**
 * Monitoring and logging functionality
 */
class WP_AI_Site_Manager_Monitor {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wp_aism_logs';
    }
    
    public function init() {
        // Add hooks
        add_action('wp_aism_file_scan', array($this, 'scan_files'));
        add_action('wp_aism_daily_report', array($this, 'send_daily_report'));
        add_action('save_post', array($this, 'log_post_change'));
        add_action('delete_post', array($this, 'log_post_deletion'));
        add_action('wp_login', array($this, 'log_user_login'));
        add_action('wp_login_failed', array($this, 'log_login_failure'));
        add_action('user_register', array($this, 'log_user_registration'));
        add_action('set_user_role', array($this, 'log_role_change'));
        add_action('activated_plugin', array($this, 'log_plugin_activation'));
        add_action('deactivated_plugin', array($this, 'log_plugin_deactivation'));
        add_action('switch_theme', array($this, 'log_theme_change'));
        
        // Add AJAX handlers
        add_action('wp_ajax_wp_aism_get_logs', array($this, 'ajax_get_logs'));
        add_action('wp_ajax_wp_aism_clear_logs', array($this, 'ajax_clear_logs'));
        add_action('wp_ajax_wp_aism_clear_cron_jobs', array($this, 'ajax_clear_cron_jobs'));
        add_action('wp_ajax_wp_aism_dismiss_notice', array($this, 'ajax_dismiss_notice'));
        
        // Add admin notice about the fix
        add_action('admin_notices', array($this, 'show_fix_notice'));
        
        // Clear excessive cron jobs after WordPress is fully loaded
        add_action('init', array($this, 'clear_excessive_cron_jobs'));
    }
    
    public function show_fix_notice() {
        if (isset($_GET['page']) && strpos($_GET['page'], 'wp-ai-site-manager') !== false) {
            $notice_key = 'wp_aism_fix_notice_dismissed';
            if (!get_transient($notice_key)) {
                echo '<div class="notice notice-info is-dismissible">';
                echo '<p><strong>WP AI Site Manager:</strong> ';
                echo 'Infinite email loop protection has been enabled. File scanning is now limited to once daily with rate limiting on alerts. ';
                echo '<a href="#" id="dismiss-fix-notice">Dismiss</a></p>';
                echo '</div>';
                
                echo '<script>
                jQuery(document).ready(function($) {
                    $("#dismiss-fix-notice").on("click", function(e) {
                        e.preventDefault();
                        $.post(ajaxurl, {
                            action: "wp_aism_dismiss_notice",
                            nonce: wpAISM.nonce
                        });
                        $(this).closest(".notice").fadeOut();
                    });
                });
                </script>';
            }
        }
    }
    
    public function ajax_clear_cron_jobs() {
        check_ajax_referer('wp_aism_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'wp-ai-site-manager'));
        }
        
        // Force clear cron jobs (bypass transient check)
        $this->force_clear_excessive_cron_jobs();
        wp_send_json_success(__('Cron jobs cleared and reset successfully', 'wp-ai-site-manager'));
    }
    
    public function ajax_dismiss_notice() {
        check_ajax_referer('wp_aism_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'wp-ai-site-manager'));
        }
        
        set_transient('wp_aism_fix_notice_dismissed', true, 86400); // 24 hours
        wp_send_json_success();
    }
    
    public function clear_excessive_cron_jobs() {
        // Check if we've already run this recently to prevent multiple executions
        $last_run = get_transient('wp_aism_cron_clear_last_run');
        if ($last_run && (time() - $last_run) < 300) { // 5 minutes
            return;
        }
        
        // Ensure WordPress cron functions are available
        if (!function_exists('wp_next_scheduled') || !function_exists('wp_schedule_event')) {
            return; // Exit early if cron functions aren't available yet
        }
        
        // Clear any hourly file scan jobs that might be running
        // Use wp_get_scheduled_events if available (WordPress 5.1+), otherwise use alternative method
        if (function_exists('wp_get_scheduled_events')) {
            $scheduled_times = wp_get_scheduled_events('wp_aism_file_scan');
            if (!empty($scheduled_times)) {
                foreach ($scheduled_times as $timestamp => $event) {
                    if ($event['schedule'] === 'hourly') {
                        wp_unschedule_event($timestamp, 'wp_aism_file_scan');
                    }
                }
            }
        } else {
            // Alternative method for older WordPress versions
            // Clear all scheduled wp_aism_file_scan events and reschedule
            wp_clear_scheduled_hook('wp_aism_file_scan');
        }
        
        // Ensure only daily scanning is scheduled
        if (!wp_next_scheduled('wp_aism_file_scan')) {
            wp_schedule_event(time() + 3600, 'daily', 'wp_aism_file_scan'); // Start in 1 hour
        }
        
        // Mark that we've run this function
        set_transient('wp_aism_cron_clear_last_run', time(), 3600); // 1 hour
    }
    
    /**
     * Force clear excessive cron jobs (bypasses transient check)
     */
    private function force_clear_excessive_cron_jobs() {
        // Ensure WordPress cron functions are available
        if (!function_exists('wp_next_scheduled') || !function_exists('wp_schedule_event')) {
            return; // Exit early if cron functions aren't available yet
        }
        
        // Clear any hourly file scan jobs that might be running
        // Use wp_get_scheduled_events if available (WordPress 5.1+), otherwise use alternative method
        if (function_exists('wp_get_scheduled_events')) {
            $scheduled_times = wp_get_scheduled_events('wp_aism_file_scan');
            if (!empty($scheduled_times)) {
                foreach ($scheduled_times as $timestamp => $event) {
                    if ($event['schedule'] === 'hourly') {
                        wp_unschedule_event($timestamp, 'wp_aism_file_scan');
                    }
                }
            }
        } else {
            // Alternative method for older WordPress versions
            // Clear all scheduled wp_aism_file_scan events and reschedule
            wp_clear_scheduled_hook('wp_aism_file_scan');
        }
        
        // Ensure only daily scanning is scheduled
        if (!wp_next_scheduled('wp_aism_file_scan')) {
            wp_schedule_event(time() + 3600, 'daily', 'wp_aism_file_scan'); // Start in 1 hour
        }
        
        // Reset the transient to allow future automatic runs
        delete_transient('wp_aism_cron_clear_last_run');
    }
    
    /**
     * Send daily report
     */
    public function send_daily_report() {
        $options = get_option('wp_aism_options', array());
        $recipients = isset($options['report_recipients']) ? $options['report_recipients'] : get_option('admin_email');
        
        if (empty($recipients)) {
            return;
        }
        
        // Get today's logs
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_aism_logs';
        $today = date('Y-m-d');
        
        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE DATE(timestamp) = %s ORDER BY timestamp DESC LIMIT 100",
            $today
        ));
        
        if (empty($logs)) {
            return; // No logs to report
        }
        
        $subject = sprintf('[%s] WP AI Site Manager - Daily Report for %s', get_bloginfo('name'), $today);
        
        $message = "Daily Activity Report for {$today}\n\n";
        $message .= "Site: " . get_bloginfo('name') . "\n";
        $message .= "URL: " . get_bloginfo('url') . "\n\n";
        
        $message .= "Activity Summary:\n";
        $message .= "================\n\n";
        
        $activity_counts = array();
        foreach ($logs as $log) {
            $action = $log->action_type;
            if (!isset($activity_counts[$action])) {
                $activity_counts[$action] = 0;
            }
            $activity_counts[$action]++;
        }
        
        foreach ($activity_counts as $action => $count) {
            $message .= ucfirst(str_replace('_', ' ', $action)) . ": {$count}\n";
        }
        
        $message .= "\nDetailed Logs:\n";
        $message .= "==============\n\n";
        
        foreach ($logs as $log) {
            $time = date('H:i:s', strtotime($log->timestamp));
            $message .= "[{$time}] {$log->action_type}";
            if (!empty($log->action_details)) {
                $details = maybe_unserialize($log->action_details);
                if (is_array($details)) {
                    $message .= " - " . implode(', ', array_filter($details));
                } else {
                    $message .= " - " . $details;
                }
            }
            $message .= "\n";
        }
        
        $message .= "\n---\n";
        $message .= "This report was generated automatically by WP AI Site Manager.\n";
        $message .= "To modify report settings, visit the plugin settings page.";
        
        // Send email
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        wp_mail($recipients, $subject, $message, $headers);
        
        // Log the report being sent
        $this->log_action('daily_report_sent', array(
            'recipients' => $recipients,
            'log_count' => count($logs)
        ), 'info', false); // Don't send alert for this action
    }
    
    public static function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wp_aism_logs';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            user_id bigint(20) DEFAULT NULL,
            user_ip varchar(45) DEFAULT NULL,
            action_type varchar(50) NOT NULL,
            action_details text,
            severity varchar(20) DEFAULT 'info',
            PRIMARY KEY (id),
            KEY timestamp (timestamp),
            KEY action_type (action_type),
            KEY severity (severity)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Create file hashes table
        $hashes_table = $wpdb->prefix . 'wp_aism_file_hashes';
        $sql = "CREATE TABLE $hashes_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            file_path varchar(500) NOT NULL,
            file_hash varchar(64) NOT NULL,
            file_size bigint(20) DEFAULT NULL,
            file_perms int(11) DEFAULT NULL,
            last_modified datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY file_path (file_path),
            KEY file_hash (file_hash)
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    public function scan_files() {
        try {
            // Rate limiting: prevent scanning too frequently
            $last_scan_time = get_transient('wp_aism_last_scan_time');
            $current_time = time();
            
            if ($last_scan_time && ($current_time - $last_scan_time) < 1800) { // 30 minutes minimum between scans
                return;
            }
            
            $options = get_option('wp_aism_options', array());
            $file_types = isset($options['file_types']) ? $options['file_types'] : array('php', 'js', 'css');
            
            $wp_content_dir = WP_CONTENT_DIR;
            $wp_includes_dir = ABSPATH . 'wp-includes';
            
            $this->scan_directory($wp_content_dir, $file_types);
            $this->scan_directory($wp_includes_dir, $file_types);
            
            // Log scan completion
            $this->log_action('file_scan_complete', array(
                'scanned_directories' => array($wp_content_dir, $wp_includes_dir),
                'file_types' => $file_types
            ));
            
            // Update last scan time
            set_transient('wp_aism_last_scan_time', $current_time, 7200); // 2 hours
            update_option('wp_aism_last_scan', $current_time);
            
        } catch (Exception $e) {
            // Log any errors that occur during scanning
            $this->log_action('file_scan_error', array(
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ), 'error');
        }
    }
    
    private function scan_directory($directory, $file_types) {
        if (!is_dir($directory)) {
            return;
        }
        
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $extension = strtolower(pathinfo($file->getPathname(), PATHINFO_EXTENSION));
                    if (in_array($extension, $file_types)) {
                        $this->check_file_integrity($file->getPathname());
                    }
                }
            }
        } catch (Exception $e) {
            // Log any errors that occur during directory scanning
            $this->log_action('directory_scan_error', array(
                'directory' => $directory,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ), 'error');
        }
    }
    
    private function check_file_integrity($file_path) {
        global $wpdb;
        
        $relative_path = str_replace(ABSPATH, '', $file_path);
        
        // Skip email-related files to prevent infinite loops
        $skip_patterns = array(
            '/mail/',
            '/email/',
            '/wp-mail.php',
            '/wp-cron.php',
            '/wp-content/cache/',
            '/wp-content/uploads/',
            '/wp-content/backup/',
            '/wp-content/backups/',
            '/wp-content/debug.log',
            '/wp-content/error_log'
        );
        
        foreach ($skip_patterns as $pattern) {
            if (strpos($relative_path, $pattern) !== false) {
                return; // Skip this file
            }
        }
        
        // Skip temporary and cache files
        $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $skip_extensions = array('tmp', 'temp', 'cache', 'log', 'bak', 'backup');
        if (in_array($file_extension, $skip_extensions)) {
            return;
        }
        
        $file_hash = md5_file($file_path);
        $file_size = filesize($file_path);
        $file_perms = fileperms($file_path) & 0777;
        
        $hashes_table = $wpdb->prefix . 'wp_aism_file_hashes';
        
        // Check if file exists in database
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $hashes_table WHERE file_path = %s",
            $relative_path
        ));
        
        if ($existing) {
            // Check for changes
            if ($existing->file_hash !== $file_hash) {
                $this->log_action('file_modified', array(
                    'file_path' => $relative_path,
                    'old_hash' => $existing->file_hash,
                    'new_hash' => $file_hash,
                    'old_size' => $existing->file_size,
                    'new_size' => $file_size
                ), 'warning');
            }
            
            if ($existing->file_perms !== $file_perms) {
                $this->log_action('file_permissions_changed', array(
                    'file_path' => $relative_path,
                    'old_perms' => $existing->file_perms,
                    'new_perms' => $file_perms
                ), 'warning');
            }
            
            // Update database
            $wpdb->update(
                $hashes_table,
                array(
                    'file_hash' => $file_hash,
                    'file_size' => $file_size,
                    'file_perms' => $file_perms,
                    'last_modified' => current_time('mysql')
                ),
                array('file_path' => $relative_path)
            );
        } else {
            // New file
            $wpdb->insert(
                $hashes_table,
                array(
                    'file_path' => $relative_path,
                    'file_hash' => $file_hash,
                    'file_size' => $file_size,
                    'file_perms' => $file_perms,
                    'last_modified' => current_time('mysql')
                )
            );
            
            $this->log_action('new_file_detected', array(
                'file_path' => $relative_path,
                'file_size' => $file_size,
                'file_perms' => $file_perms
            ));
        }
    }
    
    public function log_action($action_type, $details = array(), $severity = 'info', $send_alert = true) {
        global $wpdb;
        
        $user_id = get_current_user_id();
        $user_ip = $this->get_user_ip();
        
        $wpdb->insert(
            $this->table_name,
            array(
                'user_id' => $user_id,
                'user_ip' => $user_ip,
                'action_type' => $action_type,
                'action_details' => json_encode($details),
                'severity' => $severity
            )
        );
        
        // Send alert for high severity events
        if ($send_alert && ($severity === 'warning' || $severity === 'error')) {
            $this->send_alert($action_type, $details, $severity);
        }
    }
    
    // WordPress activity logging methods
    public function log_post_action($post_id, $post, $update) {
        $action = $update ? 'post_updated' : 'post_created';
        $this->log_action($action, array(
            'post_id' => $post_id,
            'post_title' => $post->post_title,
            'post_type' => $post->post_type,
            'post_status' => $post->post_status
        ));
    }
    
    public function log_post_deletion($post_id) {
        $post = get_post($post_id);
        if ($post) {
            $this->log_action('post_deleted', array(
                'post_id' => $post_id,
                'post_title' => $post->post_title,
                'post_type' => $post->post_type
            ));
        }
    }
    
    public function log_user_login($user_login) {
        $user = get_user_by('login', $user_login);
        $this->log_action('user_login', array(
            'user_login' => $user_login,
            'user_id' => $user ? $user->ID : 0,
            'user_role' => $user ? implode(', ', $user->roles) : ''
        ));
    }
    
    public function log_login_failed($username) {
        $this->log_action('login_failed', array(
            'username' => $username
        ), 'warning');
    }
    
    public function log_user_registration($user_id) {
        $user = get_user_by('id', $user_id);
        $this->log_action('user_registered', array(
            'user_id' => $user_id,
            'user_login' => $user ? $user->user_login : '',
            'user_email' => $user ? $user->user_email : ''
        ));
    }
    
    public function log_role_change($user_id, $role, $old_roles) {
        $this->log_action('user_role_changed', array(
            'user_id' => $user_id,
            'old_roles' => $old_roles,
            'new_role' => $role
        ));
    }
    
    public function log_plugin_activation($plugin) {
        $this->log_action('plugin_activated', array(
            'plugin' => $plugin
        ));
    }
    
    public function log_plugin_deactivation($plugin) {
        $this->log_action('plugin_deactivated', array(
            'plugin' => $plugin
        ));
    }
    
    public function log_theme_change($new_name) {
        $this->log_action('theme_changed', array(
            'new_theme' => $new_name
        ));
    }
    
    private function get_user_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    private function send_alert($action_type, $details, $severity) {
        $options = get_option('wp_aism_options', array());
        $alert_emails = isset($options['alert_emails']) ? $options['alert_emails'] : get_option('admin_email');
        
        if (empty($alert_emails)) {
            return;
        }
        
        // Rate limiting: prevent sending too many emails in a short time
        $last_alert_time = get_transient('wp_aism_last_alert_time');
        $current_time = time();
        
        if ($last_alert_time && ($current_time - $last_alert_time) < 300) { // 5 minutes cooldown
            return;
        }
        
        // Prevent recursive alerts - don't send alerts for email-related actions
        if (in_array($action_type, array('email_sent', 'email_failed', 'mail_sent'))) {
            return;
        }
        
        // Check if we've sent too many alerts recently
        $alert_count = get_transient('wp_aism_alert_count');
        if ($alert_count && $alert_count > 10) { // Max 10 alerts per hour
            return;
        }
        
        $subject = sprintf('[%s] Security Alert: %s', get_bloginfo('name'), $action_type);
        $message = sprintf(
            "A %s level event has been detected:\n\nAction: %s\nDetails: %s\nTime: %s\n\nView logs: %s",
            $severity,
            $action_type,
            json_encode($details, JSON_PRETTY_PRINT),
            current_time('mysql'),
            admin_url('admin.php?page=wp-ai-site-manager-logs')
        );
        
        $sent = wp_mail($alert_emails, $subject, $message);
        
        if ($sent) {
            // Update rate limiting data
            set_transient('wp_aism_last_alert_time', $current_time, 3600); // 1 hour
            set_transient('wp_aism_alert_count', ($alert_count ? $alert_count + 1 : 1), 3600); // 1 hour
            
            // Log the email sending (but don't trigger another alert)
            $this->log_action('email_sent', array(
                'action_type' => $action_type,
                'severity' => $severity,
                'recipients' => $alert_emails
            ), 'info', false); // false = don't send alert for this
        }
    }
    
    public function render_logs_page() {
        $logs = $this->get_logs();
        include WP_AISM_PLUGIN_DIR . 'templates/logs-page.php';
    }
    
    public function render_dashboard_widget() {
        $recent_logs = $this->get_logs(10);
        include WP_AISM_PLUGIN_DIR . 'templates/dashboard-widget.php';
    }
    
    private function get_logs($limit = 100) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} ORDER BY timestamp DESC LIMIT %d",
            $limit
        ));
    }
    
    public function ajax_get_logs() {
        check_ajax_referer('wp_aism_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'wp-ai-site-manager'));
        }
        
        $logs = $this->get_logs();
        wp_send_json_success($logs);
    }
    
    public function ajax_clear_logs() {
        check_ajax_referer('wp_aism_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'wp-ai-site-manager'));
        }
        
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$this->table_name}");
        
        wp_send_json_success(__('Logs cleared successfully', 'wp-ai-site-manager'));
    }
}
