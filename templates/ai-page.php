<?php
/**
 * AI Tools Page Template
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('AI Content Tools', 'wp-ai-site-manager'); ?></h1>
    
    <div class="wp-aism-ai-container">
        <!-- Usage Statistics -->
        <div class="wp-aism-usage-stats">
            <h2><?php _e('Usage Statistics', 'wp-ai-site-manager'); ?></h2>
            <div class="wp-aism-stats-grid">
                <div class="wp-aism-stat-card">
                    <h3><?php _e('Today', 'wp-ai-site-manager'); ?></h3>
                    <p class="stat-number"><?php echo esc_html($usage['today']); ?></p>
                    <p class="stat-label"><?php _e('Requests', 'wp-ai-site-manager'); ?></p>
                </div>
                <div class="wp-aism-stat-card">
                    <h3><?php _e('This Month', 'wp-ai-site-manager'); ?></h3>
                    <p class="stat-number"><?php echo esc_html($usage['month']); ?></p>
                    <p class="stat-label"><?php _e('Requests', 'wp-ai-site-manager'); ?></p>
                </div>
                <div class="wp-aism-stat-card">
                    <h3><?php _e('Daily Limit', 'wp-ai-site-manager'); ?></h3>
                    <p class="stat-number"><?php echo esc_html($usage['limit']); ?></p>
                    <p class="stat-label"><?php _e('Remaining', 'wp-ai-site-manager'); ?></p>
                </div>
            </div>
        </div>

        <!-- Content Generation Tools -->
        <div class="wp-aism-content-tools">
            <h2><?php _e('Content Generation', 'wp-ai-site-manager'); ?></h2>
            
            <div class="wp-aism-tool-section">
                <h3><?php _e('Blog Post Generator', 'wp-ai-site-manager'); ?></h3>
                <div class="wp-aism-form-group">
                    <label for="blog-topic"><?php _e('Topic or Title:', 'wp-ai-site-manager'); ?></label>
                    <input type="text" id="blog-topic" class="regular-text" placeholder="<?php esc_attr_e('Enter your blog topic...', 'wp-ai-site-manager'); ?>">
                </div>
                <div class="wp-aism-form-group">
                    <label for="blog-style"><?php _e('Writing Style:', 'wp-ai-site-manager'); ?></label>
                    <select id="blog-style">
                        <option value="professional"><?php _e('Professional', 'wp-ai-site-manager'); ?></option>
                        <option value="casual"><?php _e('Casual', 'wp-ai-site-manager'); ?></option>
                        <option value="creative"><?php _e('Creative', 'wp-ai-site-manager'); ?></option>
                        <option value="technical"><?php _e('Technical', 'wp-ai-site-manager'); ?></option>
                    </select>
                </div>
                <div class="wp-aism-form-group">
                    <label for="blog-length"><?php _e('Length:', 'wp-ai-site-manager'); ?></label>
                    <select id="blog-length">
                        <option value="short"><?php _e('Short (300-500 words)', 'wp-ai-site-manager'); ?></option>
                        <option value="medium"><?php _e('Medium (500-1000 words)', 'wp-ai-site-manager'); ?></option>
                        <option value="long"><?php _e('Long (1000+ words)', 'wp-ai-site-manager'); ?></option>
                    </select>
                </div>
                <button type="button" class="button button-primary wp-aism-generate-btn" data-type="blog">
                    <?php _e('Generate Blog Post', 'wp-ai-site-manager'); ?>
                </button>
            </div>

            <div class="wp-aism-tool-section">
                <h3><?php _e('SEO Meta Generator', 'wp-ai-site-manager'); ?></h3>
                <div class="wp-aism-form-group">
                    <label for="seo-content"><?php _e('Content Summary:', 'wp-ai-site-manager'); ?></label>
                    <textarea id="seo-content" rows="3" placeholder="<?php esc_attr_e('Briefly describe your content...', 'wp-ai-site-manager'); ?>"></textarea>
                </div>
                <div class="wp-aism-form-group">
                    <label for="seo-keywords"><?php _e('Target Keywords:', 'wp-ai-site-manager'); ?></label>
                    <input type="text" id="seo-keywords" class="regular-text" placeholder="<?php esc_attr_e('Enter target keywords...', 'wp-ai-site-manager'); ?>">
                </div>
                <button type="button" class="button button-primary wp-aism-generate-btn" data-type="seo">
                    <?php _e('Generate SEO Meta', 'wp-ai-site-manager'); ?>
                </button>
            </div>

            <div class="wp-aism-tool-section">
                <h3><?php _e('Content Rewriter', 'wp-ai-site-manager'); ?></h3>
                <div class="wp-aism-form-group">
                    <label for="rewrite-content"><?php _e('Content to Rewrite:', 'wp-ai-site-manager'); ?></label>
                    <textarea id="rewrite-content" rows="4" placeholder="<?php esc_attr_e('Paste your content here...', 'wp-ai-site-manager'); ?>"></textarea>
                </div>
                <div class="wp-aism-form-group">
                    <label for="rewrite-style"><?php _e('New Style:', 'wp-ai-site-manager'); ?></label>
                    <select id="rewrite-style">
                        <option value="simplify"><?php _e('Simplify', 'wp-ai-site-manager'); ?></option>
                        <option value="formalize"><?php _e('Make Formal', 'wp-ai-site-manager'); ?></option>
                        <option value="casualize"><?php _e('Make Casual', 'wp-ai-site-manager'); ?></option>
                        <option value="expand"><?php _e('Expand', 'wp-ai-site-manager'); ?></option>
                    </select>
                </div>
                <button type="button" class="button button-primary wp-aism-generate-btn" data-type="rewrite">
                    <?php _e('Rewrite Content', 'wp-ai-site-manager'); ?>
                </button>
            </div>
        </div>

        <!-- Generated Content Display -->
        <div class="wp-aism-generated-content" style="display: none;">
            <h2><?php _e('Generated Content', 'wp-ai-site-manager'); ?></h2>
            <div class="wp-aism-content-actions">
                <button type="button" class="button wp-aism-copy-btn"><?php _e('Copy to Clipboard', 'wp-ai-site-manager'); ?></button>
                <button type="button" class="button wp-aism-insert-btn"><?php _e('Insert into Editor', 'wp-ai-site-manager'); ?></button>
                <button type="button" class="button wp-aism-clear-btn"><?php _e('Clear', 'wp-ai-site-manager'); ?></button>
            </div>
            <div class="wp-aism-content-display">
                <div id="generated-content"></div>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div class="wp-aism-loading" style="display: none;">
            <div class="wp-aism-spinner"></div>
            <p><?php _e('Generating content...', 'wp-ai-site-manager'); ?></p>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('.wp-aism-generate-btn').on('click', function() {
        var type = $(this).data('type');
        var data = {};
        
        switch(type) {
            case 'blog':
                data = {
                    topic: $('#blog-topic').val(),
                    style: $('#blog-style').val(),
                    length: $('#blog-length').val()
                };
                break;
            case 'seo':
                data = {
                    content: $('#seo-content').val(),
                    keywords: $('#seo-keywords').val()
                };
                break;
            case 'rewrite':
                data = {
                    content: $('#rewrite-content').val(),
                    style: $('#rewrite-style').val()
                };
                break;
        }
        
        if (this.validateData(data)) {
            this.generateContent(type, data);
        }
    }.bind(this));
    
    $('.wp-aism-copy-btn').on('click', function() {
        var content = $('#generated-content').text();
        navigator.clipboard.writeText(content).then(function() {
            alert('<?php _e('Content copied to clipboard!', 'wp-ai-site-manager'); ?>');
        });
    });
    
    $('.wp-aism-clear-btn').on('click', function() {
        $('.wp-aism-generated-content').hide();
        $('#generated-content').empty();
    });
    
    this.validateData = function(data) {
        for (var key in data) {
            if (!data[key]) {
                alert('<?php _e('Please fill in all required fields.', 'wp-ai-site-manager'); ?>');
                return false;
            }
        }
        return true;
    };
    
    this.generateContent = function(type, data) {
        $('.wp-aism-loading').show();
        $('.wp-aism-generated-content').hide();
        
        $.ajax({
            url: wpAISM.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wp_aism_generate_content',
                nonce: wpAISM.nonce,
                type: type,
                data: data
            },
            success: function(response) {
                $('.wp-aism-loading').hide();
                if (response.success) {
                    $('#generated-content').html(response.data.content);
                    $('.wp-aism-generated-content').show();
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                $('.wp-aism-loading').hide();
                alert('<?php _e('An error occurred while generating content.', 'wp-ai-site-manager'); ?>');
            }
        });
    };
});
</script>
