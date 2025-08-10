<?php
/**
 * AI integration and content generation
 */
class WP_AI_Site_Manager_AI {
    
    private $api_key;
    private $usage_table;
    
    public function __construct() {
        global $wpdb;
        $this->usage_table = $wpdb->prefix . 'wp_aism_ai_usage';
        $this->api_key = $this->get_api_key();
    }
    
    public function init() {
        // Add AJAX handlers
        add_action('wp_ajax_wp_aism_ai_chat', array($this, 'ajax_ai_chat'));
        add_action('wp_ajax_wp_aism_generate_content', array($this, 'ajax_generate_content'));
        add_action('wp_ajax_wp_aism_get_usage', array($this, 'ajax_get_usage'));
        
        // Add editor integration
        add_action('add_meta_boxes', array($this, 'add_ai_meta_box'));
        add_action('admin_footer', array($this, 'add_editor_ai_button'));
    }
    
    private function get_api_key() {
        $options = get_option('wp_aism_options', array());
        return isset($options['openai_api_key']) ? $options['openai_api_key'] : '';
    }
    
    public function render_ai_page() {
        $usage = $this->get_user_usage();
        include WP_AISM_PLUGIN_DIR . 'templates/ai-page.php';
    }
    
    public function render_chat_bar() {
        include WP_AISM_PLUGIN_DIR . 'templates/chat-bar.php';
    }
    
    public function add_ai_meta_box() {
        $post_types = get_post_types(array('public' => true));
        foreach ($post_types as $post_type) {
            add_meta_box(
                'wp_aism_ai_tools',
                __('AI Content Tools', 'wp-ai-site-manager'),
                array($this, 'render_ai_meta_box'),
                $post_type,
                'side',
                'high'
            );
        }
    }
    
    public function render_ai_meta_box($post) {
        include WP_AISM_PLUGIN_DIR . 'templates/ai-meta-box.php';
    }
    
    public function add_editor_ai_button() {
        if (get_current_screen()->base === 'post') {
            include WP_AISM_PLUGIN_DIR . 'templates/editor-ai-button.php';
        }
    }
    
    public function ajax_ai_chat() {
        check_ajax_referer('wp_aism_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Insufficient permissions', 'wp-ai-site-manager'));
        }
        
        $message = sanitize_textarea_field($_POST['message']);
        if (empty($message)) {
            wp_send_json_error(__('Message is required', 'wp-ai-site-manager'));
        }
        
        // Check usage limits
        if (!$this->check_usage_limits()) {
            wp_send_json_error(__('Daily usage limit exceeded', 'wp-ai-site-manager'));
        }
        
        $response = $this->call_openai_api($message);
        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }
        
        // Log usage
        $this->log_usage('chat');
        
        wp_send_json_success(array(
            'response' => $response,
            'usage' => $this->get_user_usage()
        ));
    }
    
    public function ajax_generate_content() {
        check_ajax_referer('wp_aism_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Insufficient permissions', 'wp-ai-site-manager'));
        }
        
        $type = sanitize_text_field($_POST['type']);
        $prompt = sanitize_textarea_field($_POST['prompt']);
        $context = sanitize_textarea_field($_POST['context'] ?? '');
        
        if (empty($type) || empty($prompt)) {
            wp_send_json_error(__('Type and prompt are required', 'wp-ai-site-manager'));
        }
        
        // Check usage limits
        if (!$this->check_usage_limits()) {
            wp_send_json_error(__('Daily usage limit exceeded', 'wp-ai-site-manager'));
        }
        
        $full_prompt = $this->build_content_prompt($type, $prompt, $context);
        $response = $this->call_openai_api($full_prompt);
        
        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }
        
        // Log usage
        $this->log_usage('content_generation');
        
        wp_send_json_success(array(
            'content' => $response,
            'usage' => $this->get_user_usage()
        ));
    }
    
    public function ajax_get_usage() {
        check_ajax_referer('wp_aism_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Insufficient permissions', 'wp-ai-site-manager'));
        }
        
        wp_send_json_success($this->get_user_usage());
    }
    
    private function call_openai_api($prompt) {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', __('OpenAI API key not configured', 'wp-ai-site-manager'));
        }
        
        $url = 'https://api.openai.com/v1/chat/completions';
        $headers = array(
            'Authorization' => 'Bearer ' . $this->api_key,
            'Content-Type' => 'application/json'
        );
        
        $body = array(
            'model' => 'gpt-4o-mini',
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => 'You are a helpful WordPress content assistant. Provide clear, actionable responses.'
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => 1000,
            'temperature' => 0.7
        );
        
        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body' => json_encode($body),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            return new WP_Error('openai_error', $data['error']['message']);
        }
        
        if (isset($data['choices'][0]['message']['content'])) {
            return $data['choices'][0]['message']['content'];
        }
        
        return new WP_Error('invalid_response', __('Invalid response from OpenAI', 'wp-ai-site-manager'));
    }
    
    private function build_content_prompt($type, $prompt, $context = '') {
        $prompts = array(
            'blog_post' => "Write a blog post about: {$prompt}. " . 
                          ($context ? "Context: {$context}" : "") . 
                          " Make it engaging, informative, and SEO-friendly.",
            'product_description' => "Write a compelling product description for: {$prompt}. " . 
                                   ($context ? "Context: {$context}" : "") . 
                                   " Focus on benefits and features.",
            'meta_description' => "Write a compelling meta description for: {$prompt}. " . 
                                ($context ? "Context: {$context}" : "") . 
                                " Keep it under 160 characters and include relevant keywords.",
            'social_media' => "Write engaging social media content about: {$prompt}. " . 
                             ($context ? "Context: {$context}" : "") . 
                             " Make it shareable and platform-appropriate.",
            'email_subject' => "Write an attention-grabbing email subject line for: {$prompt}. " . 
                              ($context ? "Context: {$context}" : "") . 
                              " Keep it under 50 characters.",
            'summarize' => "Summarize the following content in a clear, concise way: {$prompt}",
            'rewrite' => "Rewrite the following content to improve clarity and engagement: {$prompt}"
        );
        
        return isset($prompts[$type]) ? $prompts[$type] : $prompt;
    }
    
    private function check_usage_limits() {
        $options = get_option('wp_aism_options', array());
        $limits = isset($options['ai_usage_limits']) ? $options['ai_usage_limits'] : array();
        
        $user_role = $this->get_user_role();
        $daily_limit = isset($limits[$user_role]) ? $limits[$user_role] : 25;
        
        $usage = $this->get_user_usage();
        return $usage['today'] < $daily_limit;
    }
    
    private function get_user_role() {
        $user = wp_get_current_user();
        $roles = $user->roles;
        return !empty($roles) ? $roles[0] : 'subscriber';
    }
    
    private function get_user_usage() {
        global $wpdb;
        
        $user_id = get_current_user_id();
        $today = current_time('Y-m-d');
        $month_start = current_time('Y-m-01');
        
        // Check if table exists, if not return default values
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->usage_table}'") != $this->usage_table) {
            $options = get_option('wp_aism_options', array());
            $limits = isset($options['ai_usage_limits']) ? $options['ai_usage_limits'] : array();
            $user_role = $this->get_user_role();
            $daily_limit = isset($limits[$user_role]) ? $limits[$user_role] : 25;
            
            return array(
                'today' => 0,
                'month' => 0,
                'limit' => intval($daily_limit)
            );
        }
        
        // Get today's usage
        $today_usage = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->usage_table} WHERE user_id = %d AND DATE(timestamp) = %s",
            $user_id,
            $today
        ));
        
        // Get this month's usage
        $month_usage = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->usage_table} WHERE user_id = %d AND DATE(timestamp) >= %s",
            $user_id,
            $month_start
        ));
        
        // Get user's daily limit
        $options = get_option('wp_aism_options', array());
        $limits = isset($options['ai_usage_limits']) ? $options['ai_usage_limits'] : array();
        $user_role = $this->get_user_role();
        $daily_limit = isset($limits[$user_role]) ? $limits[$user_role] : 25;
        
        return array(
            'today' => intval($today_usage),
            'month' => intval($month_usage),
            'limit' => intval($daily_limit)
        );
    }
    
    private function log_usage($action_type) {
        global $wpdb;
        
        $user_id = get_current_user_id();
        
        // Check if table exists before trying to insert
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->usage_table}'") != $this->usage_table) {
            return false;
        }
        
        $wpdb->insert(
            $this->usage_table,
            array(
                'user_id' => $user_id,
                'action_type' => $action_type,
                'timestamp' => current_time('mysql')
            )
        );
        
        return $wpdb->insert_id > 0;
    }
    
    public static function create_usage_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wp_aism_ai_usage';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            action_type varchar(50) NOT NULL,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY timestamp (timestamp)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
