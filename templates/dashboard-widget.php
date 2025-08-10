<?php
/**
 * Dashboard Widget Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get recent logs
$recent_logs = $this->get_logs(10);
$severity_colors = array(
    'info' => '#0073aa',
    'warning' => '#ffb900',
    'error' => '#dc3232'
);

// Get quick stats
$total_logs = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
$today_logs = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$this->table_name} WHERE DATE(timestamp) = %s",
    current_time('Y-m-d')
));
$security_alerts = $wpdb->get_var(
    "SELECT COUNT(*) FROM {$this->table_name} WHERE severity IN ('warning', 'error')"
);
?>

<div class="wp-aism-dashboard-widget">
    <!-- Quick Stats -->
    <div class="wp-aism-stats-overview">
        <div class="wp-aism-stat-item">
            <span class="stat-number"><?php echo esc_html($total_logs); ?></span>
            <span class="stat-label"><?php _e('Total Logs', 'wp-ai-site-manager'); ?></span>
        </div>
        <div class="wp-aism-stat-item">
            <span class="stat-number"><?php echo esc_html($today_logs); ?></span>
            <span class="stat-label"><?php _e('Today', 'wp-ai-site-manager'); ?></span>
        </div>
        <div class="wp-aism-stat-item">
            <span class="stat-number"><?php echo esc_html($security_alerts); ?></span>
            <span class="stat-label"><?php _e('Alerts', 'wp-ai-site-manager'); ?></span>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="wp-aism-quick-actions">
        <a href="<?php echo admin_url('admin.php?page=wp-ai-site-manager-logs'); ?>" class="button button-secondary button-small">
            <span class="dashicons dashicons-list-view"></span>
            <?php _e('View All Logs', 'wp-ai-site-manager'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=wp-ai-site-manager'); ?>" class="button button-secondary button-small">
            <span class="dashicons dashicons-admin-settings"></span>
            <?php _e('Settings', 'wp-ai-site-manager'); ?>
        </a>
        <button type="button" class="button button-primary button-small" id="refresh-widget">
            <span class="dashicons dashicons-update"></span>
            <?php _e('Refresh', 'wp-ai-site-manager'); ?>
        </button>
        <button type="button" class="button button-warning button-small" id="clear-cron-jobs">
            <span class="dashicons dashicons-clock"></span>
            <?php _e('Fix Cron Jobs', 'wp-ai-site-manager'); ?>
        </button>
    </div>
    
    <!-- Recent Activity -->
    <div class="wp-aism-recent-activity">
        <h4><?php _e('Recent Activity', 'wp-ai-site-manager'); ?></h4>
        
        <?php if (empty($recent_logs)): ?>
            <p class="wp-aism-no-activity"><?php _e('No recent activity.', 'wp-ai-site-manager'); ?></p>
        <?php else: ?>
            <div class="wp-aism-activity-list">
                <?php foreach ($recent_logs as $log): ?>
                    <div class="wp-aism-activity-item severity-<?php echo esc_attr($log->severity); ?>">
                        <div class="activity-icon">
                            <?php
                            $icon = 'dashicons-admin-generic';
                            switch ($log->action_type) {
                                case 'user_login':
                                    $icon = 'dashicons-admin-users';
                                    break;
                                case 'post_created':
                                case 'post_updated':
                                case 'post_deleted':
                                    $icon = 'dashicons-admin-post';
                                    break;
                                case 'plugin_activated':
                                case 'plugin_deactivated':
                                    $icon = 'dashicons-admin-plugins';
                                    break;
                                case 'theme_changed':
                                    $icon = 'dashicons-admin-appearance';
                                    break;
                                case 'file_modified':
                                case 'file_permissions_changed':
                                    $icon = 'dashicons-admin-tools';
                                    break;
                            }
                            ?>
                            <span class="dashicons <?php echo esc_attr($icon); ?>"></span>
                        </div>
                        
                        <div class="activity-content">
                            <div class="activity-action">
                                <?php echo esc_html(ucfirst(str_replace('_', ' ', $log->action_type))); ?>
                            </div>
                            
                            <div class="activity-details">
                                <?php 
                                $details = json_decode($log->action_details, true);
                                if ($details && is_array($details)) {
                                    $detail_text = array();
                                    foreach ($details as $key => $value) {
                                        if (is_array($value)) {
                                            $detail_text[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . json_encode($value);
                                        } else {
                                            $detail_text[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
                                        }
                                    }
                                    echo esc_html(implode(', ', array_slice($detail_text, 0, 2)));
                                }
                                ?>
                            </div>
                            
                            <div class="activity-meta">
                                <span class="activity-time">
                                    <?php echo esc_html(human_time_diff(strtotime($log->timestamp), current_time('timestamp'))); ?> <?php _e('ago', 'wp-ai-site-manager'); ?>
                                </span>
                                
                                <?php if ($log->user_id): ?>
                                    <span class="activity-user">
                                        <?php 
                                        $user = get_user_by('id', $log->user_id);
                                        if ($user) {
                                            echo esc_html($user->display_name);
                                        }
                                        ?>
                                    </span>
                                <?php endif; ?>
                                
                                <span class="activity-severity severity-<?php echo esc_attr($log->severity); ?>"
                                      style="color: <?php echo esc_attr($severity_colors[$log->severity] ?? '#666'); ?>">
                                    <?php echo esc_html(ucfirst($log->severity)); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="wp-aism-view-more">
                <a href="<?php echo admin_url('admin.php?page=wp-ai-site-manager-logs'); ?>">
                    <?php _e('View all activity logs →', 'wp-ai-site-manager'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- System Status -->
    <div class="wp-aism-system-status">
        <h4><?php _e('System Status', 'wp-ai-site-manager'); ?></h4>
        <div class="status-items">
            <div class="status-item">
                <span class="status-label"><?php _e('File Monitoring:', 'wp-ai-site-manager'); ?></span>
                <span class="status-value status-ok">
                    <span class="dashicons dashicons-yes"></span>
                    <?php _e('Active', 'wp-ai-site-manager'); ?>
                </span>
            </div>
            <div class="status-item">
                <span class="status-label"><?php _e('AI Integration:', 'wp-ai-site-manager'); ?></span>
                <span class="status-value <?php echo get_option('wp_aism_options')['openai_api_key'] ? 'status-ok' : 'status-warning'; ?>">
                    <?php if (get_option('wp_aism_options')['openai_api_key']): ?>
                        <span class="dashicons dashicons-yes"></span>
                        <?php _e('Connected', 'wp-ai-site-manager'); ?>
                    <?php else: ?>
                        <span class="dashicons dashicons-warning"></span>
                        <?php _e('Not Configured', 'wp-ai-site-manager'); ?>
                    <?php endif; ?>
                </span>
            </div>
            <div class="status-item">
                <span class="status-label"><?php _e('Last Scan:', 'wp-ai-site-manager'); ?></span>
                <span class="status-value">
                    <?php 
                    $last_scan = get_option('wp_aism_last_scan');
                    if ($last_scan) {
                        echo esc_html(human_time_diff($last_scan, current_time('timestamp'))) . ' ' . __('ago', 'wp-ai-site-manager');
                    } else {
                        _e('Never', 'wp-ai-site-manager');
                    }
                    ?>
                </span>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Refresh widget functionality
    $('#refresh-widget').on('click', function() {
        var $button = $(this);
        var $icon = $button.find('.dashicons');
        
        // Add loading state
        $button.prop('disabled', true);
        $icon.addClass('wp-aism-spinning');
        
        // Refresh the widget content
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_aism_get_logs',
                nonce: wpAISM.nonce,
                limit: 10
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function() {
                // Remove loading state on error
                $button.prop('disabled', false);
                $icon.removeClass('wp-aism-spinning');
            }
        });
    });
    
    // Fix Cron Jobs functionality
    $('#clear-cron-jobs').on('click', function() {
        var $button = $(this);
        var $icon = $button.find('.dashicons');
        
        if (!confirm('<?php _e('This will clear any excessive cron jobs that might be causing the infinite email loop. Continue?', 'wp-ai-site-manager'); ?>')) {
            return;
        }
        
        // Add loading state
        $button.prop('disabled', true);
        $icon.addClass('wp-aism-spinning');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_aism_clear_cron_jobs',
                nonce: wpAISM.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Cron jobs cleared successfully! The infinite email loop should now be resolved.', 'wp-ai-site-manager'); ?>');
                    location.reload();
                } else {
                    alert('<?php _e('Error clearing cron jobs. Please try again.', 'wp-ai-site-manager'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error clearing cron jobs. Please try again.', 'wp-ai-site-manager'); ?>');
            },
            complete: function() {
                // Remove loading state
                $button.prop('disabled', false);
                $icon.removeClass('wp-aism-spinning');
            }
        });
    });
    
    // Auto-refresh every 5 minutes
    setInterval(function() {
        if ($('#wp_aism_dashboard_widget').is(':visible')) {
            $('#refresh-widget').click();
        }
    }, 300000); // 5 minutes
});
</script>

<style>
.wp-aism-dashboard-widget {
    padding: 0;
}

.wp-aism-stats-overview {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 20px;
    padding: 0 12px;
}

.wp-aism-stat-item {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e1e5e9;
}

.wp-aism-stat-item .stat-number {
    display: block;
    font-size: 24px;
    font-weight: 700;
    color: #0073aa;
    margin-bottom: 5px;
}

.wp-aism-stat-item .stat-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.wp-aism-quick-actions {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    padding: 0 12px;
    flex-wrap: wrap;
}

.wp-aism-quick-actions .button {
    display: flex;
    align-items: center;
    gap: 5px;
}

.wp-aism-quick-actions .button-warning {
    background: #ffb900;
    border-color: #ffb900;
    color: #23282d;
}

.wp-aism-quick-actions .button-warning:hover {
    background: #f7a700;
    border-color: #f7a700;
}

.wp-aism-recent-activity {
    margin-bottom: 20px;
    padding: 0 12px;
}

.wp-aism-recent-activity h4 {
    margin: 0 0 15px 0;
    font-size: 14px;
    font-weight: 600;
    color: #23282d;
}

.wp-aism-activity-list {
    max-height: 300px;
    overflow-y: auto;
}

.wp-aism-activity-item {
    display: flex;
    gap: 12px;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 8px;
    background: #f8f9fa;
    border-left: 4px solid #e1e5e9;
    transition: all 0.2s ease;
}

.wp-aism-activity-item:hover {
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.wp-aism-activity-item.severity-warning {
    border-left-color: #ffb900;
    background: #fffbf0;
}

.wp-aism-activity-item.severity-error {
    border-left-color: #dc3232;
    background: #fef7f1;
}

.activity-icon {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
}

.activity-content {
    flex: 1;
    min-width: 0;
}

.activity-action {
    font-weight: 600;
    color: #23282d;
    margin-bottom: 4px;
    font-size: 13px;
}

.activity-details {
    color: #666;
    font-size: 12px;
    margin-bottom: 6px;
    line-height: 1.4;
}

.activity-meta {
    display: flex;
    gap: 12px;
    align-items: center;
    font-size: 11px;
    color: #999;
}

.activity-time,
.activity-user,
.activity-severity {
    white-space: nowrap;
}

.activity-severity {
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.wp-aism-view-more {
    text-align: center;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e1e5e9;
}

.wp-aism-view-more a {
    color: #0073aa;
    text-decoration: none;
    font-size: 13px;
}

.wp-aism-view-more a:hover {
    text-decoration: underline;
}

.wp-aism-system-status {
    padding: 0 12px;
}

.wp-aism-system-status h4 {
    margin: 0 0 15px 0;
    font-size: 14px;
    font-weight: 600;
    color: #23282d;
}

.status-items {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.status-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 4px;
    font-size: 12px;
}

.status-label {
    color: #666;
}

.status-value {
    display: flex;
    align-items: center;
    gap: 5px;
    font-weight: 600;
}

.status-ok {
    color: #46b450;
}

.status-warning {
    color: #ffb900;
}

.wp-aism-no-activity {
    text-align: center;
    color: #666;
    font-style: italic;
    padding: 20px;
}

.wp-aism-spinning {
    animation: wp-aism-spin 1s linear infinite;
}

@keyframes wp-aism-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@media (max-width: 782px) {
    .wp-aism-stats-overview {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .wp-aism-quick-actions {
        flex-direction: column;
    }
    
    .wp-aism-quick-actions .button {
        justify-content: center;
    }
}
</style>
