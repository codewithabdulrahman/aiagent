<?php
/**
 * AI Meta Box Template
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wp-aism-meta-box">
    <div class="wp-aism-quick-tools">
        <h4><?php _e('Quick AI Tools', 'wp-ai-site-manager'); ?></h4>
        
        <div class="wp-aism-tool-item">
            <label for="ai-title"><?php _e('Generate Title:', 'wp-ai-site-manager'); ?></label>
            <input type="text" id="ai-title" class="widefat" placeholder="<?php esc_attr_e('Enter topic or description...', 'wp-ai-site-manager'); ?>">
            <button type="button" class="button button-small wp-aism-generate-title">
                <?php _e('Generate', 'wp-ai-site-manager'); ?>
            </button>
        </div>
        
        <div class="wp-aism-tool-item">
            <label for="ai-excerpt"><?php _e('Generate Excerpt:', 'wp-ai-site-manager'); ?></label>
            <textarea id="ai-excerpt" rows="3" class="widefat" placeholder="<?php esc_attr_e('Enter content summary...', 'wp-ai-site-manager'); ?>"></textarea>
            <button type="button" class="button button-small wp-aism-generate-excerpt">
                <?php _e('Generate', 'wp-ai-site-manager'); ?>
            </button>
        </div>
        
        <div class="wp-aism-tool-item">
            <label for="ai-seo-desc"><?php _e('SEO Description:', 'wp-ai-site-manager'); ?></label>
            <textarea id="ai-seo-desc" rows="3" class="widefat" placeholder="<?php esc_attr_e('Enter content summary...', 'wp-ai-site-manager'); ?>"></textarea>
            <button type="button" class="button button-small wp-aism-generate-seo">
                <?php _e('Generate', 'wp-ai-site-manager'); ?>
            </button>
        </div>
        
        <div class="wp-aism-tool-item">
            <label for="ai-tags"><?php _e('Generate Tags:', 'wp-ai-site-manager'); ?></label>
            <input type="text" id="ai-tags" class="widefat" placeholder="<?php esc_attr_e('Enter content summary...', 'wp-ai-site-manager'); ?>">
            <button type="button" class="button button-small wp-aism-generate-tags">
                <?php _e('Generate', 'wp-ai-site-manager'); ?>
            </button>
        </div>
    </div>
    
    <div class="wp-aism-insert-actions">
        <button type="button" class="button button-primary wp-aism-insert-title">
            <?php _e('Insert Title', 'wp-ai-site-manager'); ?>
        </button>
        <button type="button" class="button button-primary wp-aism-insert-excerpt">
            <?php _e('Insert Excerpt', 'wp-ai-site-manager'); ?>
        </button>
    </div>
    
    <div class="wp-aism-loading" style="display: none;">
        <div class="wp-aism-spinner"></div>
        <span><?php _e('Generating...', 'wp-ai-site-manager'); ?></span>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Generate title
    $('.wp-aism-generate-title').on('click', function() {
        var topic = $('#ai-title').val().trim();
        if (!topic) {
            alert('<?php _e('Please enter a topic or description.', 'wp-ai-site-manager'); ?>');
            return;
        }
        
        generateContent('title', topic);
    });
    
    // Generate excerpt
    $('.wp-aism-generate-excerpt').on('click', function() {
        var summary = $('#ai-excerpt').val().trim();
        if (!summary) {
            alert('<?php _e('Please enter a content summary.', 'wp-ai-site-manager'); ?>');
            return;
        }
        
        generateContent('excerpt', summary);
    });
    
    // Generate SEO description
    $('.wp-aism-generate-seo').on('click', function() {
        var summary = $('#ai-seo-desc').val().trim();
        if (!summary) {
            alert('<?php _e('Please enter a content summary.', 'wp-ai-site-manager'); ?>');
            return;
        }
        
        generateContent('seo', summary);
    });
    
    // Generate tags
    $('.wp-aism-generate-tags').on('click', function() {
        var summary = $('#ai-tags').val().trim();
        if (!summary) {
            alert('<?php _e('Please enter a content summary.', 'wp-ai-site-manager'); ?>');
            return;
        }
        
        generateContent('tags', summary);
    });
    
    // Insert title
    $('.wp-aism-insert-title').on('click', function() {
        var title = $('#ai-title').val().trim();
        if (title) {
            $('#title').val(title);
            alert('<?php _e('Title inserted successfully!', 'wp-ai-site-manager'); ?>');
        }
    });
    
    // Insert excerpt
    $('.wp-aism-insert-excerpt').on('click', function() {
        var excerpt = $('#ai-excerpt').val().trim();
        if (excerpt) {
            $('#excerpt').val(excerpt);
            alert('<?php _e('Excerpt inserted successfully!', 'wp-ai-site-manager'); ?>');
        }
    });
    
    function generateContent(type, input) {
        $('.wp-aism-loading').show();
        
        var data = {};
        switch(type) {
            case 'title':
                data = { topic: input };
                break;
            case 'excerpt':
                data = { content: input };
                break;
            case 'seo':
                data = { content: input };
                break;
            case 'tags':
                data = { content: input };
                break;
        }
        
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
                    // Update the corresponding field
                    switch(type) {
                        case 'title':
                            $('#ai-title').val(response.data.content);
                            break;
                        case 'excerpt':
                            $('#ai-excerpt').val(response.data.content);
                            break;
                        case 'seo':
                            $('#ai-seo-desc').val(response.data.content);
                            break;
                        case 'tags':
                            $('#ai-tags').val(response.data.content);
                            break;
                    }
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                $('.wp-aism-loading').hide();
                alert('<?php _e('An error occurred while generating content.', 'wp-ai-site-manager'); ?>');
            }
        });
    }
});
</script>
