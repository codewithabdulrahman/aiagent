<?php
/**
 * Reports generation and delivery
 */
class WP_AI_Site_Manager_Reports {
    
    public function init() {
        // Add AJAX handlers
        add_action('wp_ajax_wp_aism_generate_report', array($this, 'ajax_generate_report'));
        add_action('wp_ajax_wp_aism_send_test_report', array($this, 'ajax_send_test_report'));
    }
    
    public function generate_daily_report() {
        $options = get_option('wp_aism_options', array());
        $recipients = isset($options['report_recipients']) ? $options['report_recipients'] : get_option('admin_email');
        
        if (empty($recipients)) {
            return;
        }
        
        $report_data = $this->gather_report_data();
        $report_html = $this->generate_report_html($report_data);
        $report_text = $this->generate_report_text($report_data);
        
        $subject = sprintf('[%s] Daily Site Report - %s', get_bloginfo('name'), current_time('Y-m-d'));
        
        // Send HTML email
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($recipients, $subject, $report_html, $headers);
        
        // Log report generation
        $this->log_report_generation('daily', $recipients);
    }
    
    public function generate_weekly_report() {
        $options = get_option('wp_aism_options', array());
        $recipients = isset($options['report_recipients']) ? $options['report_recipients'] : get_option('admin_email');
        
        if (empty($recipients)) {
            return;
        }
        
        $report_data = $this->gather_report_data('weekly');
        $report_html = $this->generate_report_html($report_data, 'weekly');
        $report_text = $this->generate_report_text($report_data, 'weekly');
        
        $subject = sprintf('[%s] Weekly Site Report - %s', get_bloginfo('name'), current_time('Y-m-d'));
        
        // Send HTML email
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($recipients, $subject, $report_html, $headers);
        
        // Log report generation
        $this->log_report_generation('weekly', $recipients);
    }
    
    private function gather_report_data($period = 'daily') {
        global $wpdb;
        
        $start_date = $period === 'weekly' ? 
            date('Y-m-d', strtotime('-7 days')) : 
            date('Y-m-d', strtotime('-1 day'));
        
        $end_date = date('Y-m-d');
        
        // Get activity logs
        $logs_table = $wpdb->prefix . 'wp_aism_logs';
        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $logs_table WHERE DATE(timestamp) BETWEEN %s AND %s ORDER BY timestamp DESC",
            $start_date,
            $end_date
        ));
        
        // Get file changes
        $hashes_table = $wpdb->prefix . 'wp_aism_file_hashes';
        $file_changes = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $hashes_table WHERE DATE(last_modified) BETWEEN %s AND %s",
            $start_date,
            $end_date
        ));
        
        // Get AI usage
        $usage_table = $wpdb->prefix . 'wp_aism_ai_usage';
        $ai_usage = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $usage_table WHERE DATE(timestamp) BETWEEN %s AND %s",
            $start_date,
            $end_date
        ));
        
        // Get WordPress stats
        $posts_count = wp_count_posts();
        $users_count = count_users();
        $comments_count = wp_count_comments();
        
        // Get security alerts
        $security_alerts = array_filter($logs, function($log) {
            return in_array($log->severity, array('warning', 'error'));
        });
        
        return array(
            'period' => $period,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'logs' => $logs,
            'file_changes' => $file_changes,
            'ai_usage' => $ai_usage,
            'posts_count' => $posts_count,
            'users_count' => $users_count,
            'comments_count' => $comments_count,
            'security_alerts' => $security_alerts,
            'total_actions' => count($logs),
            'total_file_changes' => count($file_changes),
            'total_ai_requests' => count($ai_usage)
        );
    }
    
    private function generate_report_html($data, $type = 'daily') {
        $period_text = $type === 'weekly' ? 'Weekly' : 'Daily';
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title><?php echo $period_text; ?> Site Report</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 800px; margin: 0 auto; padding: 20px; }
                .header { background: #0073aa; color: white; padding: 20px; text-align: center; border-radius: 5px; }
                .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
                .section h3 { color: #0073aa; margin-top: 0; }
                .metric { display: inline-block; margin: 10px; padding: 10px; background: #f9f9f9; border-radius: 3px; }
                .metric .number { font-size: 24px; font-weight: bold; color: #0073aa; }
                .metric .label { font-size: 12px; color: #666; }
                .alert { background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 3px; margin: 5px 0; }
                .alert.warning { background: #fff3cd; border-color: #ffeaa7; }
                .alert.error { background: #f8d7da; border-color: #f5c6cb; }
                .footer { text-align: center; margin-top: 30px; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1><?php echo $period_text; ?> Site Report</h1>
                    <p><?php echo get_bloginfo('name'); ?> - <?php echo $data['start_date']; ?> to <?php echo $data['end_date']; ?></p>
                </div>
                
                <div class="section">
                    <h3>📊 Activity Summary</h3>
                    <div class="metric">
                        <div class="number"><?php echo $data['total_actions']; ?></div>
                        <div class="label">Total Actions</div>
                    </div>
                    <div class="metric">
                        <div class="number"><?php echo $data['total_file_changes']; ?></div>
                        <div class="label">File Changes</div>
                    </div>
                    <div class="metric">
                        <div class="number"><?php echo $data['total_ai_requests']; ?></div>
                        <div class="label">AI Requests</div>
                    </div>
                </div>
                
                <?php if (!empty($data['security_alerts'])): ?>
                <div class="section">
                    <h3>⚠️ Security Alerts</h3>
                    <?php foreach (array_slice($data['security_alerts'], 0, 5) as $alert): ?>
                        <div class="alert <?php echo $alert->severity; ?>">
                            <strong><?php echo ucfirst($alert->action_type); ?></strong><br>
                            <?php echo $this->format_action_details($alert->action_details); ?><br>
                            <small>Time: <?php echo $alert->timestamp; ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="section">
                    <h3>📝 Content Activity</h3>
                    <div class="metric">
                        <div class="number"><?php echo $data['posts_count']->publish; ?></div>
                        <div class="label">Published Posts</div>
                    </div>
                    <div class="metric">
                        <div class="number"><?php echo $data['posts_count']->draft; ?></div>
                        <div class="label">Draft Posts</div>
                    </div>
                    <div class="metric">
                        <div class="number"><?php echo $data['comments_count']->total_comments; ?></div>
                        <div class="label">Total Comments</div>
                    </div>
                </div>
                
                <div class="section">
                    <h3>🤖 AI Content Generation</h3>
                    <p>AI tools were used <?php echo $data['total_ai_requests']; ?> times during this period.</p>
                    <?php if (!empty($data['ai_usage'])): ?>
                        <p>Recent AI activities:</p>
                        <ul>
                            <?php 
                            $recent_usage = array_slice($data['ai_usage'], 0, 3);
                            foreach ($recent_usage as $usage): 
                                $user = get_user_by('id', $usage->user_id);
                                $user_name = $user ? $user->display_name : 'Unknown User';
                            ?>
                                <li><?php echo $user_name; ?> used <?php echo $usage->action_type; ?> at <?php echo $usage->timestamp; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                
                <div class="section">
                    <h3>💡 Content Ideas from AI</h3>
                    <p>Here are some content ideas for your next posts:</p>
                    <ul>
                        <li>Create a "How-to" guide based on recent user questions</li>
                        <li>Write about trending topics in your industry</li>
                        <li>Share insights from your analytics data</li>
                        <li>Create a roundup of your best performing content</li>
                    </ul>
                </div>
                
                <div class="section">
                    <h3>🔒 Security Tip of the Day</h3>
                    <p><?php echo $this->get_security_tip(); ?></p>
                </div>
                
                <div class="footer">
                    <p>This report was generated automatically by WP AI Site Manager</p>
                    <p><a href="<?php echo admin_url('admin.php?page=wp-ai-site-manager'); ?>">View Full Dashboard</a></p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    private function generate_report_text($data, $type = 'daily') {
        $period_text = $type === 'weekly' ? 'Weekly' : 'Daily';
        
        $report = "{$period_text} Site Report for " . get_bloginfo('name') . "\n";
        $report .= "Period: {$data['start_date']} to {$data['end_date']}\n\n";
        
        $report .= "Activity Summary:\n";
        $report .= "- Total Actions: {$data['total_actions']}\n";
        $report .= "- File Changes: {$data['total_file_changes']}\n";
        $report .= "- AI Requests: {$data['total_ai_requests']}\n\n";
        
        if (!empty($data['security_alerts'])) {
            $report .= "Security Alerts:\n";
            foreach (array_slice($data['security_alerts'], 0, 5) as $alert) {
                $report .= "- {$alert->action_type}: " . $this->format_action_details($alert->action_details) . "\n";
            }
            $report .= "\n";
        }
        
        $report .= "Content Activity:\n";
        $report .= "- Published Posts: {$data['posts_count']->publish}\n";
        $report .= "- Draft Posts: {$data['posts_count']->draft}\n";
        $report .= "- Total Comments: {$data['comments_count']->total_comments}\n\n";
        
        $report .= "AI Content Generation:\n";
        $report .= "AI tools were used {$data['total_ai_requests']} times during this period.\n\n";
        
        $report .= "Content Ideas:\n";
        $report .= "- Create a 'How-to' guide based on recent user questions\n";
        $report .= "- Write about trending topics in your industry\n";
        $report .= "- Share insights from your analytics data\n";
        $report .= "- Create a roundup of your best performing content\n\n";
        
        $report .= "Security Tip: " . $this->get_security_tip() . "\n\n";
        
        $report .= "View full dashboard: " . admin_url('admin.php?page=wp-ai-site-manager') . "\n";
        $report .= "Generated by WP AI Site Manager";
        
        return $report;
    }
    
    private function format_action_details($details_json) {
        $details = json_decode($details_json, true);
        if (is_array($details)) {
            return implode(', ', array_map(function($key, $value) {
                return "$key: $value";
            }, array_keys($details), $details));
        }
        return $details_json;
    }
    
    private function get_security_tip() {
        $tips = array(
            'Regularly update your WordPress core, themes, and plugins to patch security vulnerabilities.',
            'Use strong, unique passwords and consider implementing two-factor authentication.',
            'Monitor your site for unusual file changes and user activities.',
            'Keep regular backups of your site and database.',
            'Use HTTPS to encrypt data transmission between your site and visitors.',
            'Limit login attempts and monitor failed login attempts.',
            'Review user roles and permissions regularly.',
            'Scan your site for malware and suspicious code.',
            'Use a security plugin to monitor and protect your site.',
            'Keep your server software and PHP version updated.'
        );
        
        return $tips[array_rand($tips)];
    }
    
    private function log_report_generation($type, $recipients) {
        // This could be logged to the main logs table if needed
        // For now, we'll just use WordPress's built-in logging
        error_log("WP AI Site Manager: Generated {$type} report and sent to " . implode(', ', (array)$recipients));
    }
    
    public function ajax_generate_report() {
        check_ajax_referer('wp_aism_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'wp-ai-site-manager'));
        }
        
        $type = sanitize_text_field($_POST['type'] ?? 'daily');
        
        if ($type === 'weekly') {
            $this->generate_weekly_report();
        } else {
            $this->generate_daily_report();
        }
        
        wp_send_json_success(__('Report generated and sent successfully', 'wp-ai-site-manager'));
    }
    
    public function ajax_send_test_report() {
        check_ajax_referer('wp_aism_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'wp-ai-site-manager'));
        }
        
        $current_user = wp_get_current_user();
        $test_email = $current_user->user_email;
        
        $report_data = $this->gather_report_data('daily');
        $report_html = $this->generate_report_html($report_data, 'daily');
        
        $subject = sprintf('[%s] Test Report - %s', get_bloginfo('name'), current_time('Y-m-d'));
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        $sent = wp_mail($test_email, $subject, $report_html, $headers);
        
        if ($sent) {
            wp_send_json_success(__('Test report sent successfully to your email', 'wp-ai-site-manager'));
        } else {
            wp_send_json_error(__('Failed to send test report', 'wp-ai-site-manager'));
        }
    }
}
