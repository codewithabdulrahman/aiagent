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
    <div class="wp-aism-chat-panel floating-chat" style="display: none; position: fixed; bottom: 30px; right: 30px; z-index: 9999; width: 350px; max-width: 95vw; background: #fff; box-shadow: 0 8px 32px rgba(0,0,0,0.18); border-radius: 12px;">
        <div class="wp-aism-chat-header" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid #eee;">
            <h3 style="margin:0; font-size: 1.1em; font-weight: 600; color: #ffffffff;">AI Assistant</h3>
            <button type="button" class="wp-aism-sidebar-toggle" style="background:none;border:none;cursor:pointer;font-size:18px;width: 60%;display: flex;justify-content: flex-end;" title="Show previous chats">
                <span class="dashicons dashicons-menu"></span>
            </button>
            <button type="button" class="wp-aism-chat-close" style="background:none; border:none; cursor:pointer; font-size:18px; display: flex;">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div style="display:flex;">
            <div class="wp-aism-chat-main" style="flex:1;">
                <div class="wp-aism-chat-messages" style="max-height: 320px; overflow-y: auto; padding: 12px 16px;"></div>
                <div class="wp-aism-chat-input" style="padding: 12px 16px; border-top: 1px solid #eee;">
                    <textarea id="wp-aism-chat-message" placeholder="<?php esc_attr_e('Type your message here...', 'wp-ai-site-manager'); ?>" rows="3" style="width:100%; resize:vertical;"></textarea>
                    <div class="wp-aism-chat-actions" style="margin-top:8px; display:flex; gap:8px;">
                        <button type="button" class="button button-primary wp-aism-send-message">
                            <?php _e('Send', 'wp-ai-site-manager'); ?>
                        </button>
                        <button type="button" class="button wp-aism-clear-chat">
                            <?php _e('Clear', 'wp-ai-site-manager'); ?>
                        </button>
                        <div class="wp-aism-blog-actions" style="display:none; margin-left:auto; gap:4px;"></div>
                    </div>
                </div>
                <div class="wp-aism-chat-loading" style="display: none; text-align:center; padding:8px;">
                    <div class="wp-aism-spinner"></div>
                    <span><?php _e('Thinking...', 'wp-ai-site-manager'); ?></span>
                </div>
            </div>
            <div class="wp-aism-chat-sidebar" style="width:180px; background:#f7f7f7; border-left:1px solid #eee; display:none;">
                <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 16px; border-bottom:1px solid #eee;">
                    <span style="font-weight:600;">Previous Chats</span>
                    <button type="button" class="wp-aism-sidebar-close" style="background:none; border:none; cursor:pointer; font-size:18px;">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
                <div class="wp-aism-chat-sessions" style="overflow-y:auto; max-height:calc(100vh - 90px); padding:8px 0;"></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Unique localStorage key per page
    var chatStorageKey = 'aiagent_chat_' + window.location.pathname;
    var chatOpenedKey = 'aiagent_chat_opened_' + window.location.pathname;
    var chatSessionsKey = 'aiagent_chat_sessions';

    // Restore chat if opened on this page
    if (localStorage.getItem(chatOpenedKey) === '1') {
        $('.wp-aism-chat-panel').show();
        renderChatMessages();
    }

    // Sidebar logic
    function getChatSessions() {
        var sessions = localStorage.getItem(chatSessionsKey);
        if (!sessions) return [];
        try {
            return JSON.parse(sessions);
        } catch(e) {
            return [];
        }
    }

    function saveChatSession(session) {
        var sessions = getChatSessions();
        var idx = sessions.findIndex(function(s) { return s.key === session.key; });
        if (idx > -1) {
            sessions[idx] = session;
        } else {
            sessions.push(session);
        }
        localStorage.setItem(chatSessionsKey, JSON.stringify(sessions));
    }

    function renderChatSidebar() {
        var sessions = getChatSessions();
        var html = '';
        if (sessions.length === 0) {
            html = '<div style="padding:16px; color:#888;">No previous chats</div>';
        } else {
            sessions.forEach(function(session, idx) {
                html += '<div class="wp-aism-chat-session" data-key="' + session.key + '" style="padding:10px 16px; cursor:pointer; border-bottom:1px solid #eee;">' +
                    '<span style="font-size:14px;">' + (session.title || ('Chat #' + (idx+1))) + '</span>' +
                '</div>';
            });
        }
        $('.wp-aism-chat-sessions').html(html);
    }

    // Show sidebar inside chat panel on menu icon click
    $(document).on('click', '.wp-aism-sidebar-toggle', function() {
        $('.wp-aism-chat-sidebar').show();
    });
    // Hide sidebar
    $(document).on('click', '.wp-aism-sidebar-close', function() {
        $('.wp-aism-chat-sidebar').hide();
    });

    // Select chat session
    $(document).on('click', '.wp-aism-chat-session', function() {
        var key = $(this).data('key');
        chatStorageKey = key;
        localStorage.setItem(chatOpenedKey, '1');
        $('.wp-aism-chat-sidebar').hide();
        renderChatMessages();
        $('.wp-aism-chat-panel').show();
    });

    // Save current session on message send/clear
    function saveCurrentSession(title) {
        var session = {
            key: chatStorageKey,
            title: title || document.title,
            lastUsed: Date.now()
        };
        saveChatSession(session);
    }

    // Toggle chat panel
    $('.wp-aism-chat-toggle').on('click', function() {
        var isVisible = $('.wp-aism-chat-panel').is(':visible');
        $('.wp-aism-chat-panel').toggle();
        if (!isVisible) {
            localStorage.setItem(chatOpenedKey, '1');
            renderChatMessages();
            $('#wp-aism-chat-message').focus();
        } else {
            localStorage.removeItem(chatOpenedKey);
        }
    });

    // Close chat panel
    $('.wp-aism-chat-close').on('click', function() {
        $('.wp-aism-chat-panel').hide();
        localStorage.removeItem(chatOpenedKey);
    });

    // Send message
    $('.wp-aism-send-message').on('click', function() {
        sendChatMessage();
        saveCurrentSession();
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
        saveChatMessages([getWelcomeMessage()]);
        renderChatMessages();
        saveCurrentSession();
    });

    // Edit message
    $(document).on('click', '.wp-aism-chat-text', function() {
        var $msg = $(this).closest('.wp-aism-chat-message');
        var idx = $msg.data('idx');
        var messages = getChatMessages();
        if (messages[idx].type !== 'user') return;
        $('.wp-aism-chat-messages').data('editing-idx', idx);
        renderChatMessages();
        $msg.find('.wp-aism-edit-area').focus();
    });

    // Save edit
    $(document).on('click', '.wp-aism-save-edit', function() {
        var $msg = $(this).closest('.wp-aism-chat-message');
        var idx = $msg.data('idx');
        var newText = $msg.find('.wp-aism-edit-area').val().trim();
        if (!newText) return;
        var messages = getChatMessages();
        messages[idx].text = newText;
        messages[idx].edited = true;
        saveChatMessages(messages);
        $('.wp-aism-chat-messages').removeData('editing-idx');
        renderChatMessages();
        // Reprocess with AI if user message
        processAIMessage(newText, idx);
    });

    // Cancel edit
    $(document).on('click', '.wp-aism-cancel-edit', function() {
        $('.wp-aism-chat-messages').removeData('editing-idx');
        renderChatMessages();
    });

    function getWelcomeMessage() {
        return {
            text: '<?php echo esc_js(__('Hello! I\'m your AI assistant. How can I help you today?\nI can help with:\n• Writing blog posts and content\n• Generating SEO meta descriptions\n• Rewriting and improving content\n• Answering questions about your site', 'wp-ai-site-manager')); ?>',
            type: 'ai',
            edited: false
        };
    }

    function getChatMessages() {
        var msgs = localStorage.getItem(chatStorageKey);
        if (!msgs) return [getWelcomeMessage()];
        try {
            return JSON.parse(msgs);
        } catch(e) {
            return [getWelcomeMessage()];
        }
    }

    function saveChatMessages(msgs) {
        localStorage.setItem(chatStorageKey, JSON.stringify(msgs));
    }

    function renderChatMessages() {
        var messages = getChatMessages();
        var editingIdx = $('.wp-aism-chat-messages').data('editing-idx');
        var html = '';
        messages.forEach(function(msg, idx) {
            var messageClass = 'wp-aism-chat-message wp-aism-' + msg.type + '-message';
            if (msg.type === 'ai' && msg.text.includes('error')) messageClass += ' error';
            html += '<div class="' + messageClass + '" data-idx="' + idx + '">';
            html += '<div class="message-content">';
            if (msg.type === 'user' && editingIdx === idx) {
                html += '<textarea class="wp-aism-edit-area" style="width:90%;resize:vertical;">' + msg.text + '</textarea>';
                html += '<button class="wp-aism-save-edit button button-primary" style="margin-left:4px;">Save</button>';
                html += '<button class="wp-aism-cancel-edit button" style="margin-left:4px;">Cancel</button>';
            } else {
                html += '<p class="wp-aism-chat-text" style="cursor:' + (msg.type === 'user' ? 'pointer' : 'default') + ';">' + msg.text.replace(/\n/g, '<br>') + '</p>';
            }
            html += '</div>';
            html += '</div>';
        });
        $('.wp-aism-chat-messages').html(html);
        // Scroll to bottom
        var chatMessages = $('.wp-aism-chat-messages');
        chatMessages.scrollTop(chatMessages[0].scrollHeight);
        showBlogActions(messages);
    }

    function sendChatMessage() {
        var message = $('#wp-aism-chat-message').val().trim();
        if (!message) return;
        var messages = getChatMessages();
        messages.push({text: message, type: 'user', edited: false});
        saveChatMessages(messages);
        $('#wp-aism-chat-message').val('');
        renderChatMessages();
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
                var messages = getChatMessages();
                if (response.success) {
                    messages.push({text: response.data.response, type: 'ai', edited: false});
                } else {
                    messages.push({text: '<?php echo esc_js(__('Sorry, I encountered an error. Please try again.', 'wp-ai-site-manager')); ?>', type: 'ai', edited: false});
                }
                saveChatMessages(messages);
                renderChatMessages();
            },
            error: function() {
                $('.wp-aism-chat-loading').hide();
                var messages = getChatMessages();
                messages.push({text: '<?php echo esc_js(__('Sorry, I encountered an error. Please try again.', 'wp-ai-site-manager')); ?>', type: 'ai', edited: false});
                saveChatMessages(messages);
                renderChatMessages();
            }
        });
    }

    function processAIMessage(message, idx) {
        $('.wp-aism-chat-loading').show();
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
                var messages = getChatMessages();
                // Remove next AI message after edited user message
                if (messages[idx+1] && messages[idx+1].type === 'ai') {
                    messages.splice(idx+1, 1);
                }
                if (response.success) {
                    messages.splice(idx+1, 0, {text: response.data.response, type: 'ai', edited: false});
                } else {
                    messages.splice(idx+1, 0, {text: '<?php echo esc_js(__('Sorry, I encountered an error. Please try again.', 'wp-ai-site-manager')); ?>', type: 'ai', edited: false});
                }
                saveChatMessages(messages);
                renderChatMessages();
            },
            error: function() {
                $('.wp-aism-chat-loading').hide();
                var messages = getChatMessages();
                messages.splice(idx+1, 0, {text: '<?php echo esc_js(__('Sorry, I encountered an error. Please try again.', 'wp-ai-site-manager')); ?>', type: 'ai', edited: false});
                saveChatMessages(messages);
                renderChatMessages();
            }
        });
    }

    function showBlogActions(messages) {
        var lastMsg = messages[messages.length-1];
        var show = false;
        if (lastMsg && lastMsg.type === 'user') {
            var txt = lastMsg.text.toLowerCase();
            if (txt.includes('blog') || txt.includes('post') || txt.includes('content')) {
                show = true;
            }
        }
        var $actions = $('.wp-aism-blog-actions');
        if (show) {
            $actions.html('<button type="button" class="button">Draft</button>' +
                '<button type="button" class="button">Publish</button>' +
                '<button type="button" class="button">Schedule</button>');
            $actions.show();
        } else {
            $actions.hide();
        }
    }

    // Initial render
    if ($('.wp-aism-chat-panel').is(':visible')) {
        renderChatMessages();
    }
});
</script>
