<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php _e('AI Site Manager Settings', 'wp-ai-site-manager'); ?></h1>
    
    <h2 class="nav-tab-wrapper">
        <a href="#monitoring" class="nav-tab nav-tab-active"><?php _e('Monitoring', 'wp-ai-site-manager'); ?></a>
        <a href="#ai-tools" class="nav-tab"><?php _e('AI Tools', 'wp-ai-site-manager'); ?></a>
        <a href="#reports" class="nav-tab"><?php _e('Reports', 'wp-ai-site-manager'); ?></a>
        <a href="#status" class="nav-tab"><?php _e('Status', 'wp-ai-site-manager'); ?></a>
    </h2>
    
    <form method="post" action="">
        <?php wp_nonce_field('wp_aism_settings', 'wp_aism_nonce'); ?>
        
        <!-- Monitoring Tab -->
        <div id="monitoring" class="tab-content">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('File Scan Interval', 'wp-ai-site-manager'); ?></th>
                    <td>
                        <select name="scan_interval">
                            <?php foreach ($this->get_scan_intervals() as $value => $label): ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($this->get_option('scan_interval'), $value); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e('How often to scan files for changes', 'wp-ai-site-manager'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Alert Emails', 'wp-ai-site-manager'); ?></th>
                    <td>
                        <textarea name="alert_emails" rows="3" cols="50" class="regular-text"><?php echo esc_textarea($this->get_option('alert_emails')); ?></textarea>
                        <p class="description"><?php _e('Comma-separated list of email addresses to receive security alerts', 'wp-ai-site-manager'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('File Types to Monitor', 'wp-ai-site-manager'); ?></th>
                    <td>
                        <?php 
                        $selected_types = $this->get_option('file_types', array('php', 'js', 'css'));
                        foreach ($this->get_file_types() as $type => $label): 
                        ?>
                            <label style="display: inline-block; margin-right: 15px;">
                                <input type="checkbox" name="file_types[]" value="<?php echo esc_attr($type); ?>" 
                                       <?php checked(in_array($type, $selected_types)); ?>>
                                <?php echo esc_html($label); ?>
                            </label>
                        <?php endforeach; ?>
                        <p class="description"><?php _e('Select file types to monitor for changes', 'wp-ai-site-manager'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- AI Tools Tab -->
        <div id="ai-tools" class="tab-content" style="display: none;">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('OpenAI API Key', 'wp-ai-site-manager'); ?></th>
                    <td>
                        <input type="password" name="openai_api_key" value="<?php echo esc_attr($this->get_option('openai_api_key')); ?>" class="regular-text">
                        <button type="button" id="test-api-key" class="button button-secondary"><?php _e('Test API Key', 'wp-ai-site-manager'); ?></button>
                        <p class="description"><?php _e('Your OpenAI API key for AI content generation', 'wp-ai-site-manager'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Daily Usage Limits', 'wp-ai-site-manager'); ?></th>
                    <td>
                        <?php 
                        $limits = $this->get_option('ai_usage_limits', array());
                        foreach ($this->get_user_roles() as $role_key => $role_name): 
                            $limit = isset($limits[$role_key]) ? $limits[$role_key] : 25;
                        ?>
                            <label style="display: block; margin-bottom: 10px;">
                                <?php echo esc_html($role_name); ?>: 
                                <input type="number" name="ai_limit_<?php echo esc_attr($role_key); ?>" 
                                       value="<?php echo esc_attr($limit); ?>" min="0" max="1000" style="width: 80px;">
                                <?php _e('requests per day', 'wp-ai-site-manager'); ?>
                            </label>
                        <?php endforeach; ?>
                        <p class="description"><?php _e('Set daily limits for AI usage by user role', 'wp-ai-site-manager'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Reports Tab -->
        <div id="reports" class="tab-content" style="display: none;">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Report Frequency', 'wp-ai-site-manager'); ?></th>
                    <td>
                        <select name="report_frequency">
                            <?php foreach ($this->get_report_frequencies() as $value => $label): ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($this->get_option('report_frequency'), $value); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e('How often to generate and send site reports', 'wp-ai-site-manager'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Report Recipients', 'wp-ai-site-manager'); ?></th>
                    <td>
                        <textarea name="report_recipients" rows="3" cols="50" class="regular-text"><?php echo esc_textarea($this->get_option('report_recipients')); ?></textarea>
                        <p class="description"><?php _e('Comma-separated list of email addresses to receive reports', 'wp-ai-site-manager'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Test Reports', 'wp-ai-site-manager'); ?></th>
                    <td>
                        <button type="button" id="send-test-report" class="button button-secondary"><?php _e('Send Test Report', 'wp-ai-site-manager'); ?></button>
                        <p class="description"><?php _e('Send a test report to your email address', 'wp-ai-site-manager'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Status Tab -->
        <div id="status" class="tab-content" style="display: none;">
            <?php $status = $this->get_plugin_status(); ?>
            
            <h3><?php _e('Plugin Status', 'wp-ai-site-manager'); ?></h3>
            
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e('Component', 'wp-ai-site-manager'); ?></th>
                        <th><?php _e('Status', 'wp-ai-site-manager'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php _e('OpenAI API', 'wp-ai-site-manager'); ?></td>
                        <td>
                            <span class="dashicons <?php echo $status['openai']['configured'] ? 'dashicons-yes-alt' : 'dashicons-no-alt'; ?>"></span>
                            <?php echo esc_html($status['openai']['message']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e('File Scan Cron', 'wp-ai-site-manager'); ?></td>
                        <td>
                            <span class="dashicons <?php echo strpos($status['cron']['file_scan'], 'Scheduled') !== false ? 'dashicons-yes-alt' : 'dashicons-no-alt'; ?>"></span>
                            <?php echo esc_html($status['cron']['file_scan']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e('Daily Report Cron', 'wp-ai-site-manager'); ?></td>
                        <td>
                            <span class="dashicons <?php echo strpos($status['cron']['daily_report'], 'Scheduled') !== false ? 'dashicons-yes-alt' : 'dashicons-no-alt'; ?>"></span>
                            <?php echo esc_html($status['cron']['daily_report']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e('Logs Table', 'wp-ai-site-manager'); ?></td>
                        <td>
                            <span class="dashicons <?php echo strpos($status['database']['logs_table'], 'Exists') !== false ? 'dashicons-yes-alt' : 'dashicons-no-alt'; ?>"></span>
                            <?php echo esc_html($status['database']['logs_table']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e('File Hashes Table', 'wp-ai-site-manager'); ?></td>
                        <td>
                            <span class="dashicons <?php echo strpos($status['database']['hashes_table'], 'Exists') !== false ? 'dashicons-yes-alt' : 'dashicons-no-alt'; ?>"></span>
                            <?php echo esc_html($status['database']['hashes_table']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e('AI Usage Table', 'wp-ai-site-manager'); ?></td>
                        <td>
                            <span class="dashicons <?php echo strpos($status['database']['usage_table'], 'Exists') !== false ? 'dashicons-yes-alt' : 'dashicons-no-alt'; ?>"></span>
                            <?php echo esc_html($status['database']['usage_table']); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'wp-ai-site-manager'); ?>">
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab functionality
    $('.nav-tab').click(function(e) {
        e.preventDefault();
        
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        $('.tab-content').hide();
        $($(this).attr('href')).show();
    });
    
    // Test API key
    $('#test-api-key').click(function() {
        var apiKey = $('input[name="openai_api_key"]').val();
        if (!apiKey) {
            alert('<?php _e('Please enter an API key first', 'wp-ai-site-manager'); ?>');
            return;
        }
        
        $(this).prop('disabled', true).text('<?php _e('Testing...', 'wp-ai-site-manager'); ?>');
        
        $.post(ajaxurl, {
            action: 'wp_aism_test_openai',
            api_key: apiKey,
            nonce: '<?php echo wp_create_nonce('wp_aism_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                alert(response.data);
            } else {
                alert('<?php _e('Error: ', 'wp-ai-site-manager'); ?>' + response.data);
            }
        }).fail(function() {
            alert('<?php _e('Request failed', 'wp-ai-site-manager'); ?>');
        }).always(function() {
            $('#test-api-key').prop('disabled', false).text('<?php _e('Test API Key', 'wp-ai-site-manager'); ?>');
        });
    });
    
    // Send test report
    $('#send-test-report').click(function() {
        if (!confirm('<?php _e('Send a test report to your email address?', 'wp-ai-site-manager'); ?>')) {
            return;
        }
        
        $(this).prop('disabled', true).text('<?php _e('Sending...', 'wp-ai-site-manager'); ?>');
        
        $.post(ajaxurl, {
            action: 'wp_aism_send_test_report',
            nonce: '<?php echo wp_create_nonce('wp_aism_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                alert(response.data);
            } else {
                alert('<?php _e('Error: ', 'wp-ai-site-manager'); ?>' + response.data);
            }
        }).fail(function() {
            alert('<?php _e('Request failed', 'wp-ai-site-manager'); ?>');
        }).always(function() {
            $('#send-test-report').prop('disabled', false).text('<?php _e('Send Test Report', 'wp-ai-site-manager'); ?>');
        });
    });
});
</script>
