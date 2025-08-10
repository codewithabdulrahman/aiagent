<?php
/**
 * Admin Chat Bar Template
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="wp-aism-chat-bar" class="wp-aism-chat-bar">
    <div class="wp-aism-chat-toggle">
        <span class="dashicons dashicons-format-chat"></span>
        <span class="chat-label"><?php _e('AI Assistant', 'wp-ai-site-manager'); ?></span>
    </div>
    
    <div class="wp-aism-chat-panel" style="display: none;">
        <div class="wp-aism-chat-header">
            <h3><?php _e('AI Assistant', 'wp-ai-site-manager'); ?></h3>
            <button type="button" class="wp-aism-chat-close">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        
        <div class="wp-aism-chat-messages">
            <div class="wp-aism-chat-message wp-aism-ai-message">
                <div class="message-content">
                    <p><?php _e('Hello! I\'m your AI assistant. How can I help you today?', 'wp-ai-site-manager'); ?></p>
                    <p><?php _e('I can help with:', 'wp-ai-site-manager'); ?></p>
                    <ul>
                        <li><?php _e('Writing blog posts and content', 'wp-ai-site-manager'); ?></li>
                        <li><?php _e('Generating SEO meta descriptions', 'wp-ai-site-manager'); ?></li>
                        <li><?php _e('Rewriting and improving content', 'wp-ai-site-manager'); ?></li>
                        <li><?php _e('Answering questions about your site', 'wp-ai-site-manager'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="wp-aism-chat-input">
            <textarea id="wp-aism-chat-message" placeholder="<?php esc_attr_e('Type your message here...', 'wp-ai-site-manager'); ?>" rows="3"></textarea>
            <div class="wp-aism-chat-actions">
                <button type="button" class="button button-primary wp-aism-send-message">
                    <?php _e('Send', 'wp-ai-site-manager'); ?>
                </button>
                <button type="button" class="button wp-aism-clear-chat">
                    <?php _e('Clear', 'wp-ai-site-manager'); ?>
                </button>
            </div>
        </div>
        
        <div class="wp-aism-chat-loading" style="display: none;">
            <div class="wp-aism-spinner"></div>
            <span><?php _e('Thinking...', 'wp-ai-site-manager'); ?></span>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Toggle chat panel
    $('.wp-aism-chat-toggle').on('click', function() {
        $('.wp-aism-chat-panel').toggle();
        if ($('.wp-aism-chat-panel').is(':visible')) {
            $('#wp-aism-chat-message').focus();
        }
    });
    
    // Close chat panel
    $('.wp-aism-chat-close').on('click', function() {
        $('.wp-aism-chat-panel').hide();
    });
    
    // Send message
    $('.wp-aism-send-message').on('click', function() {
        sendChatMessage();
    });
    
    // Send message on Enter key (Shift+Enter for new line)
    $('#wp-aism-chat-message').on('keydown', function(e) {
        if (e.keyCode === 13 && !e.shiftKey) {
            e.preventDefault();
            sendChatMessage();
        }
    });
    
    // Clear chat
    $('.wp-aism-clear-chat').on('click', function() {
        $('.wp-aism-chat-messages').html(
            '<div class="wp-aism-chat-message wp-aism-ai-message">' +
            '<div class="message-content">' +
            '<p><?php _e('Hello! I\'m your AI assistant. How can I help you today?', 'wp-ai-site-manager'); ?></p>' +
            '<p><?php _e('I can help with:', 'wp-ai-site-manager'); ?></p>' +
            '<ul>' +
            '<li><?php _e('Writing blog posts and content', 'wp-ai-site-manager'); ?></li>' +
            '<li><?php _e('Generating SEO meta descriptions', 'wp-ai-site-manager'); ?></li>' +
            '<li><?php _e('Rewriting and improving content', 'wp-ai-site-manager'); ?></li>' +
            '<li><?php _e('Answering questions about your site', 'wp-ai-site-manager'); ?></li>' +
            '</ul>' +
            '</div>' +
            '</div>'
        );
    });
    
    function sendChatMessage() {
        var message = $('#wp-aism-chat-message').val().trim();
        if (!message) {
            return;
        }
        
        // Add user message to chat
        addChatMessage(message, 'user');
        $('#wp-aism-chat-message').val('');
        
        // Show loading
        $('.wp-aism-chat-loading').show();
        
        // Send to AI
        $.ajax({
            url: wpAISM.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wp_aism_ai_chat',
                nonce: wpAISM.nonce,
                message: message
            },
            success: function(response) {
                $('.wp-aism-chat-loading').hide();
                if (response.success) {
                    addChatMessage(response.data.response, 'ai');
                } else {
                    addChatMessage('<?php _e('Sorry, I encountered an error. Please try again.', 'wp-ai-site-manager'); ?>', 'ai error');
                }
            },
            error: function() {
                $('.wp-aism-chat-loading').hide();
                addChatMessage('<?php _e('Sorry, I encountered an error. Please try again.', 'wp-ai-site-manager'); ?>', 'ai error');
            }
        });
    }
    
    function addChatMessage(message, type) {
        var messageClass = 'wp-aism-chat-message wp-aism-' + type + '-message';
        if (type === 'ai' && message.includes('error')) {
            messageClass += ' error';
        }
        
        var messageHtml = '<div class="' + messageClass + '">' +
            '<div class="message-content">' +
            '<p>' + message.replace(/\n/g, '<br>') + '</p>' +
            '</div>' +
            '</div>';
        
        $('.wp-aism-chat-messages').append(messageHtml);
        
        // Scroll to bottom
        var chatMessages = $('.wp-aism-chat-messages');
        chatMessages.scrollTop(chatMessages[0].scrollHeight);
    }
});
</script>
