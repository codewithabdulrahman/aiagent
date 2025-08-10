<?php
/**
 * Editor AI Button Template
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="wp-aism-editor-ai" class="wp-aism-editor-ai">
    <button type="button" class="button wp-aism-ai-button" id="wp-aism-ai-button">
        <span class="dashicons dashicons-admin-generic"></span>
        <?php _e('AI Assistant', 'wp-ai-site-manager'); ?>
    </button>
    
    <div class="wp-aism-ai-panel" style="display: none;">
        <div class="wp-aism-ai-panel-header">
            <h3><?php _e('AI Content Assistant', 'wp-ai-site-manager'); ?></h3>
            <button type="button" class="wp-aism-ai-close">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        
        <div class="wp-aism-ai-panel-content">
            <div class="wp-aism-ai-tabs">
                <button type="button" class="wp-aism-tab-button active" data-tab="generate">
                    <?php _e('Generate', 'wp-ai-site-manager'); ?>
                </button>
                <button type="button" class="wp-aism-tab-button" data-tab="improve">
                    <?php _e('Improve', 'wp-ai-site-manager'); ?>
                </button>
                <button type="button" class="wp-aism-tab-button" data-tab="translate">
                    <?php _e('Translate', 'wp-ai-site-manager'); ?>
                </button>
            </div>
            
            <div class="wp-aism-tab-content active" data-tab="generate">
                <div class="wp-aism-form-group">
                    <label for="ai-generate-prompt"><?php _e('What would you like to generate?', 'wp-ai-site-manager'); ?></label>
                    <textarea id="ai-generate-prompt" rows="3" placeholder="<?php esc_attr_e('Describe what you want to create...', 'wp-ai-site-manager'); ?>"></textarea>
                </div>
                <div class="wp-aism-form-group">
                    <label for="ai-generate-style"><?php _e('Style:', 'wp-ai-site-manager'); ?></label>
                    <select id="ai-generate-style">
                        <option value="professional"><?php _e('Professional', 'wp-ai-site-manager'); ?></option>
                        <option value="casual"><?php _e('Casual', 'wp-ai-site-manager'); ?></option>
                        <option value="creative"><?php _e('Creative', 'wp-ai-site-manager'); ?></option>
                        <option value="technical"><?php _e('Technical', 'wp-ai-site-manager'); ?></option>
                    </select>
                </div>
                <button type="button" class="button button-primary wp-aism-generate-content">
                    <?php _e('Generate Content', 'wp-ai-site-manager'); ?>
                </button>
            </div>
            
            <div class="wp-aism-tab-content" data-tab="improve">
                <div class="wp-aism-form-group">
                    <label for="ai-improve-content"><?php _e('Content to improve:', 'wp-ai-site-manager'); ?></label>
                    <textarea id="ai-improve-content" rows="4" placeholder="<?php esc_attr_e('Paste your content here...', 'wp-ai-site-manager'); ?>"></textarea>
                </div>
                <div class="wp-aism-form-group">
                    <label for="ai-improve-action"><?php _e('Improvement type:', 'wp-ai-site-manager'); ?></label>
                    <select id="ai-improve-action">
                        <option value="grammar"><?php _e('Fix grammar & spelling', 'wp-ai-site-manager'); ?></option>
                        <option value="clarity"><?php _e('Improve clarity', 'wp-ai-site-manager'); ?></option>
                        <option value="tone"><?php _e('Adjust tone', 'wp-ai-site-manager'); ?></option>
                        <option value="expand"><?php _e('Expand content', 'wp-ai-site-manager'); ?></option>
                        <option value="summarize"><?php _e('Summarize', 'wp-ai-site-manager'); ?></option>
                    </select>
                </div>
                <button type="button" class="button button-primary wp-aism-improve-content">
                    <?php _e('Improve Content', 'wp-ai-site-manager'); ?>
                </button>
            </div>
            
            <div class="wp-aism-tab-content" data-tab="translate">
                <div class="wp-aism-form-group">
                    <label for="ai-translate-content"><?php _e('Content to translate:', 'wp-ai-site-manager'); ?></label>
                    <textarea id="ai-translate-content" rows="4" placeholder="<?php esc_attr_e('Paste your content here...', 'wp-ai-site-manager'); ?>"></textarea>
                </div>
                <div class="wp-aism-form-group">
                    <label for="ai-translate-language"><?php _e('Target language:', 'wp-ai-site-manager'); ?></label>
                    <select id="ai-translate-language">
                        <option value="spanish"><?php _e('Spanish', 'wp-ai-site-manager'); ?></option>
                        <option value="french"><?php _e('French', 'wp-ai-site-manager'); ?></option>
                        <option value="german"><?php _e('German', 'wp-ai-site-manager'); ?></option>
                        <option value="italian"><?php _e('Italian', 'wp-ai-site-manager'); ?></option>
                        <option value="portuguese"><?php _e('Portuguese', 'wp-ai-site-manager'); ?></option>
                        <option value="chinese"><?php _e('Chinese', 'wp-ai-site-manager'); ?></option>
                        <option value="japanese"><?php _e('Japanese', 'wp-ai-site-manager'); ?></option>
                        <option value="korean"><?php _e('Korean', 'wp-ai-site-manager'); ?></option>
                    </select>
                </div>
                <button type="button" class="button button-primary wp-aism-translate-content">
                    <?php _e('Translate Content', 'wp-ai-site-manager'); ?>
                </button>
            </div>
        </div>
        
        <div class="wp-aism-ai-result" style="display: none;">
            <h4><?php _e('Generated Content', 'wp-ai-site-manager'); ?></h4>
            <div class="wp-aism-result-content"></div>
            <div class="wp-aism-result-actions">
                <button type="button" class="button wp-aism-insert-content">
                    <?php _e('Insert into Editor', 'wp-ai-site-manager'); ?>
                </button>
                <button type="button" class="button wp-aism-copy-content">
                    <?php _e('Copy to Clipboard', 'wp-ai-site-manager'); ?>
                </button>
                <button type="button" class="button wp-aism-clear-result">
                    <?php _e('Clear', 'wp-ai-site-manager'); ?>
                </button>
            </div>
        </div>
        
        <div class="wp-aism-loading" style="display: none;">
            <div class="wp-aism-spinner"></div>
            <span><?php _e('Processing...', 'wp-ai-site-manager'); ?></span>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Toggle AI panel
    $('#wp-aism-ai-button').on('click', function() {
        $('.wp-aism-ai-panel').toggle();
    });
    
    // Close AI panel
    $('.wp-aism-ai-close').on('click', function() {
        $('.wp-aism-ai-panel').hide();
    });
    
    // Tab switching
    $('.wp-aism-tab-button').on('click', function() {
        var tab = $(this).data('tab');
        
        // Update active tab button
        $('.wp-aism-tab-button').removeClass('active');
        $(this).addClass('active');
        
        // Update active tab content
        $('.wp-aism-tab-content').removeClass('active');
        $('[data-tab="' + tab + '"]').addClass('active');
    });
    
    // Generate content
    $('.wp-aism-generate-content').on('click', function() {
        var prompt = $('#ai-generate-prompt').val().trim();
        var style = $('#ai-generate-style').val();
        
        if (!prompt) {
            alert('<?php _e('Please enter a prompt.', 'wp-ai-site-manager'); ?>');
            return;
        }
        
        processAIRequest('generate', { prompt: prompt, style: style });
    });
    
    // Improve content
    $('.wp-aism-improve-content').on('click', function() {
        var content = $('#ai-improve-content').val().trim();
        var action = $('#ai-improve-action').val();
        
        if (!content) {
            alert('<?php _e('Please enter content to improve.', 'wp-ai-site-manager'); ?>');
            return;
        }
        
        processAIRequest('improve', { content: content, action: action });
    });
    
    // Translate content
    $('.wp-aism-translate-content').on('click', function() {
        var content = $('#ai-translate-content').val().trim();
        var language = $('#ai-translate-language').val();
        
        if (!content) {
            alert('<?php _e('Please enter content to translate.', 'wp-ai-site-manager'); ?>');
            return;
        }
        
        processAIRequest('translate', { content: content, language: language });
    });
    
    // Insert content into editor
    $('.wp-aism-insert-content').on('click', function() {
        var content = $('.wp-aism-result-content').text();
        if (content && typeof wp !== 'undefined' && wp.data && wp.data.dispatch) {
            // Gutenberg editor
            wp.data.dispatch('core/editor').insertBlocks(
                wp.blocks.createBlock('core/paragraph', { content: content })
            );
        } else if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor) {
            // Classic editor
            tinyMCE.activeEditor.execCommand('mceInsertContent', false, content);
        } else {
            // Fallback
            alert('<?php _e('Content inserted successfully!', 'wp-ai-site-manager'); ?>');
        }
    });
    
    // Copy content to clipboard
    $('.wp-aism-copy-content').on('click', function() {
        var content = $('.wp-aism-result-content').text();
        navigator.clipboard.writeText(content).then(function() {
            alert('<?php _e('Content copied to clipboard!', 'wp-ai-site-manager'); ?>');
        });
    });
    
    // Clear result
    $('.wp-aism-clear-result').on('click', function() {
        $('.wp-aism-ai-result').hide();
        $('.wp-aism-result-content').empty();
    });
    
    function processAIRequest(type, data) {
        $('.wp-aism-loading').show();
        $('.wp-aism-ai-result').hide();
        
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
                    $('.wp-aism-result-content').html(response.data.content);
                    $('.wp-aism-ai-result').show();
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                $('.wp-aism-loading').hide();
                alert('<?php _e('An error occurred while processing your request.', 'wp-ai-site-manager'); ?>');
            }
        });
    }
});
</script>
