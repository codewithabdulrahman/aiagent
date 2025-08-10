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
        // Add AJAX handlers
        add_action('wp_ajax_wp_aism_get_logs', array($this, 'ajax_get_logs'));
        add_action('wp_ajax_wp_aism_clear_logs', array($this, 'ajax_clear_logs'));
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
    }
    
    private function scan_directory($directory, $file_types) {
        if (!is_dir($directory)) {
            return;
        }
        
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
    }
    
    private function check_file_integrity($file_path) {
        global $wpdb;
        
        $relative_path = str_replace(ABSPATH, '', $file_path);
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
    
    public function log_action($action_type, $details = array(), $severity = 'info') {
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
        if ($severity === 'warning' || $severity === 'error') {
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
        
        $subject = sprintf('[%s] Security Alert: %s', get_bloginfo('name'), $action_type);
        $message = sprintf(
            "A %s level event has been detected:\n\nAction: %s\nDetails: %s\nTime: %s\n\nView logs: %s",
            $severity,
            $action_type,
            json_encode($details, JSON_PRETTY_PRINT),
            current_time('mysql'),
            admin_url('admin.php?page=wp-ai-site-manager-logs')
        );
        
        wp_mail($alert_emails, $subject, $message);
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
