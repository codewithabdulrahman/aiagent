<?php
/**
 * Activity Logs Page Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get logs data
$logs = $this->get_logs(1000); // Get more logs for filtering
$severity_colors = array(
    'info' => '#0073aa',
    'warning' => '#ffb900',
    'error' => '#dc3232'
);
?>

<div class="wrap">
    <h1><?php _e('Activity Logs', 'wp-ai-site-manager'); ?></h1>
    
    <div class="wp-aism-container">
        <!-- Filters -->
        <div class="wp-aism-section">
            <h2><?php _e('Filter Logs', 'wp-ai-site-manager'); ?></h2>
            <div class="wp-aism-filters">
                <select id="severity-filter">
                    <option value=""><?php _e('All Severities', 'wp-ai-site-manager'); ?></option>
                    <option value="info"><?php _e('Info', 'wp-ai-site-manager'); ?></option>
                    <option value="warning"><?php _e('Warning', 'wp-ai-site-manager'); ?></option>
                    <option value="error"><?php _e('Error', 'wp-ai-site-manager'); ?></option>
                </select>
                
                <select id="action-filter">
                    <option value=""><?php _e('All Actions', 'wp-ai-site-manager'); ?></option>
                    <?php
                    $action_types = array_unique(array_column($logs, 'action_type'));
                    foreach ($action_types as $action_type) {
                        echo '<option value="' . esc_attr($action_type) . '">' . esc_html(ucfirst(str_replace('_', ' ', $action_type))) . '</option>';
                    }
                    ?>
                </select>
                
                <input type="date" id="date-filter" placeholder="<?php _e('Filter by date', 'wp-ai-site-manager'); ?>">
                
                <button type="button" class="button" id="clear-filters"><?php _e('Clear Filters', 'wp-ai-site-manager'); ?></button>
            </div>
        </div>
        
        <!-- Logs Table -->
        <div class="wp-aism-section">
            <div class="wp-aism-logs-header">
                <h2><?php _e('Recent Activity', 'wp-ai-site-manager'); ?></h2>
                <div class="wp-aism-logs-actions">
                    <button type="button" class="button button-secondary" id="export-logs"><?php _e('Export CSV', 'wp-ai-site-manager'); ?></button>
                    <button type="button" class="button button-secondary" id="clear-logs"><?php _e('Clear All Logs', 'wp-ai-site-manager'); ?></button>
                </div>
            </div>
            
            <div class="wp-aism-logs-table-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Time', 'wp-ai-site-manager'); ?></th>
                            <th><?php _e('User', 'wp-ai-site-manager'); ?></th>
                            <th><?php _e('IP Address', 'wp-ai-site-manager'); ?></th>
                            <th><?php _e('Action', 'wp-ai-site-manager'); ?></th>
                            <th><?php _e('Details', 'wp-ai-site-manager'); ?></th>
                            <th><?php _e('Severity', 'wp-ai-site-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="logs-table-body">
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6"><?php _e('No logs found.', 'wp-ai-site-manager'); ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr class="log-row" 
                                    data-severity="<?php echo esc_attr($log->severity); ?>"
                                    data-action="<?php echo esc_attr($log->action_type); ?>"
                                    data-date="<?php echo esc_attr(date('Y-m-d', strtotime($log->timestamp))); ?>">
                                    
                                    <td>
                                        <span class="log-time" title="<?php echo esc_attr($log->timestamp); ?>">
                                            <?php echo esc_html(human_time_diff(strtotime($log->timestamp), current_time('timestamp'))); ?> <?php _e('ago', 'wp-ai-site-manager'); ?>
                                        </span>
                                        <br>
                                        <small><?php echo esc_html(date('Y-m-d H:i:s', strtotime($log->timestamp))); ?></small>
                                    </td>
                                    
                                    <td>
                                        <?php if ($log->user_id): ?>
                                            <?php 
                                            $user = get_user_by('id', $log->user_id);
                                            if ($user): ?>
                                                <strong><?php echo esc_html($user->display_name); ?></strong>
                                                <br>
                                                <small><?php echo esc_html($user->user_login); ?></small>
                                            <?php else: ?>
                                                <em><?php _e('User deleted', 'wp-ai-site-manager'); ?></em>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <em><?php _e('System', 'wp-ai-site-manager'); ?></em>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td>
                                        <code><?php echo esc_html($log->user_ip); ?></code>
                                    </td>
                                    
                                    <td>
                                        <span class="log-action">
                                            <?php echo esc_html(ucfirst(str_replace('_', ' ', $log->action_type))); ?>
                                        </span>
                                    </td>
                                    
                                    <td>
                                        <?php 
                                        $details = json_decode($log->action_details, true);
                                        if ($details && is_array($details)): ?>
                                            <div class="log-details">
                                                <?php foreach ($details as $key => $value): ?>
                                                    <div class="detail-item">
                                                        <strong><?php echo esc_html(ucfirst(str_replace('_', ' ', $key))); ?>:</strong>
                                                        <?php 
                                                        if (is_array($value)) {
                                                            echo esc_html(json_encode($value));
                                                        } else {
                                                            echo esc_html($value);
                                                        }
                                                        ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <em><?php _e('No details', 'wp-ai-site-manager'); ?></em>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td>
                                        <span class="log-severity severity-<?php echo esc_attr($log->severity); ?>"
                                              style="color: <?php echo esc_attr($severity_colors[$log->severity] ?? '#666'); ?>">
                                            <?php echo esc_html(ucfirst($log->severity)); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="wp-aism-pagination">
                <span class="wp-aism-pagination-info">
                    <?php printf(__('Showing %d of %d logs', 'wp-ai-site-manager'), count($logs), count($logs)); ?>
                </span>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Filter functionality
    function filterLogs() {
        var severity = $('#severity-filter').val();
        var action = $('#action-filter').val();
        var date = $('#date-filter').val();
        
        $('.log-row').each(function() {
            var $row = $(this);
            var show = true;
            
            if (severity && $row.data('severity') !== severity) {
                show = false;
            }
            
            if (action && $row.data('action') !== action) {
                show = false;
            }
            
            if (date && $row.data('date') !== date) {
                show = false;
            }
            
            $row.toggle(show);
        });
        
        updatePaginationInfo();
    }
    
    function updatePaginationInfo() {
        var visible = $('.log-row:visible').length;
        var total = $('.log-row').length;
        $('.wp-aism-pagination-info').text('Showing ' + visible + ' of ' + total + ' logs');
    }
    
    // Bind filter events
    $('#severity-filter, #action-filter, #date-filter').on('change', filterLogs);
    
    $('#clear-filters').on('click', function() {
        $('#severity-filter, #action-filter').val('');
        $('#date-filter').val('');
        $('.log-row').show();
        updatePaginationInfo();
    });
    
    // Export functionality
    $('#export-logs').on('click', function() {
        var csv = 'Time,User,IP Address,Action,Details,Severity\n';
        
        $('.log-row:visible').each(function() {
            var $row = $(this);
            var time = $row.find('.log-time').text();
            var user = $row.find('td:eq(1)').text().trim();
            var ip = $row.find('td:eq(2)').text().trim();
            var action = $row.find('.log-action').text();
            var details = $row.find('.log-details').text().trim() || 'No details';
            var severity = $row.find('.log-severity').text();
            
            csv += '"' + time + '","' + user + '","' + ip + '","' + action + '","' + details + '","' + severity + '"\n';
        });
        
        var blob = new Blob([csv], { type: 'text/csv' });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'wp-ai-site-manager-logs-' + new Date().toISOString().split('T')[0] + '.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    });
    
    // Clear logs functionality
    $('#clear-logs').on('click', function() {
        if (confirm('<?php _e('Are you sure you want to clear all logs? This action cannot be undone.', 'wp-ai-site-manager'); ?>')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_aism_clear_logs',
                    nonce: wpAISM.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('<?php _e('Error clearing logs', 'wp-ai-site-manager'); ?>');
                    }
                },
                error: function() {
                    alert('<?php _e('Error clearing logs', 'wp-ai-site-manager'); ?>');
                }
            });
        }
    });
    
    // Initialize
    updatePaginationInfo();
});
</script>

<style>
.wp-aism-filters {
    display: flex;
    gap: 15px;
    align-items: center;
    margin-bottom: 20px;
}

.wp-aism-filters select,
.wp-aism-filters input {
    min-width: 150px;
}

.wp-aism-logs-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.wp-aism-logs-actions {
    display: flex;
    gap: 10px;
}

.wp-aism-logs-table-container {
    overflow-x: auto;
}

.log-details {
    max-width: 300px;
}

.detail-item {
    margin-bottom: 5px;
    font-size: 12px;
}

.log-severity {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.5px;
}

.severity-info { color: #0073aa; }
.severity-warning { color: #ffb900; }
.severity-error { color: #dc3232; }

.wp-aism-pagination {
    margin-top: 20px;
    text-align: center;
    color: #666;
}

@media (max-width: 768px) {
    .wp-aism-filters {
        flex-direction: column;
        align-items: stretch;
    }
    
    .wp-aism-logs-header {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }
    
    .wp-aism-logs-actions {
        justify-content: center;
    }
}
</style>
