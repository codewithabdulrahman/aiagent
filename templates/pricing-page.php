<?php
/**
 * Pricing Page Template
 * 
 * @package WP_AI_Site_Manager
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$features = new WP_AI_Site_Manager_Features();
$upgrade_prompts = new WP_AI_Site_Manager_Upgrade_Prompts();
$upgrade_stats = $upgrade_prompts->get_upgrade_stats();
$feature_comparison = $features->get_feature_comparison();
?>

<div class="wrap wp-aism-pricing-page">
    <h1><?php _e('WP AI Site Manager - Choose Your Plan', 'wp-ai-site-manager'); ?></h1>
    
    <?php if ($upgrade_stats['is_trial']): ?>
        <div class="wp-aism-trial-banner">
            <div class="trial-content">
                <h2><?php _e('🎉 You\'re Currently on a Free Trial!', 'wp-ai-site-manager'); ?></h2>
                <p><?php printf(__('You have %d days left to experience all premium features. Upgrade now to continue enjoying unlimited access!', 'wp-ai-site-manager'), $upgrade_stats['trial_days_remaining']); ?></p>
                <a href="<?php echo esc_url($upgrade_stats['upgrade_url']); ?>" class="button button-primary button-large"><?php _e('Upgrade Now & Save', 'wp-ai-site-manager'); ?></a>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="wp-aism-pricing-intro">
        <h2><?php _e('Choose the Perfect Plan for Your Needs', 'wp-ai-site-manager'); ?></h2>
        <p><?php _e('From individual bloggers to enterprise agencies, we have a plan that fits your requirements and budget.', 'wp-ai-site-manager'); ?></p>
    </div>
    
    <div class="wp-aism-pricing-grid">
        <!-- Free Plan -->
        <div class="pricing-plan free-plan">
            <div class="plan-header">
                <h3><?php _e('Free', 'wp-ai-site-manager'); ?></h3>
                <div class="plan-price">
                    <span class="price">$0</span>
                    <span class="period"><?php _e('Forever', 'wp-ai-site-manager'); ?></span>
                </div>
                <p class="plan-description"><?php _e('Perfect for getting started with basic security monitoring', 'wp-ai-site-manager'); ?></p>
            </div>
            
            <div class="plan-features">
                <ul>
                    <li><?php _e('✓ Basic file monitoring (plugins only)', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ PHP file integrity checks', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Daily scanning', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ 10 AI requests per day', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Basic activity logging', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ 30-day log retention', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Community support', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ 1 site license', 'wp-ai-site-manager'); ?></li>
                </ul>
            </div>
            
            <div class="plan-action">
                <span class="current-plan-badge"><?php _e('Current Plan', 'wp-ai-site-manager'); ?></span>
            </div>
        </div>
        
        <!-- Personal Plan -->
        <div class="pricing-plan personal-plan featured">
            <div class="plan-badge"><?php _e('Most Popular', 'wp-ai-site-manager'); ?></div>
            <div class="plan-header">
                <h3><?php _e('Personal', 'wp-ai-site-manager'); ?></h3>
                <div class="plan-price">
                    <span class="price">$39</span>
                    <span class="period"><?php _e('/year', 'wp-ai-site-manager'); ?></span>
                </div>
                <p class="plan-description"><?php _e('Ideal for individual bloggers and developers', 'wp-ai-site-manager'); ?></p>
            </div>
            
            <div class="plan-features">
                <ul>
                    <li><?php _e('✓ Everything in Free', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Advanced file monitoring (all directories)', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ All file types (PHP, JS, CSS, HTML, TXT)', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Hourly + Real-time scanning', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Unlimited AI requests', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Advanced AI models', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ 1-year log retention', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Email support', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ 1 site license', 'wp-ai-site-manager'); ?></li>
                </ul>
            </div>
            
            <div class="plan-action">
                <?php if ($upgrade_stats['current_plan'] === 'personal'): ?>
                    <span class="current-plan-badge"><?php _e('Current Plan', 'wp-ai-site-manager'); ?></span>
                <?php else: ?>
                    <a href="<?php echo esc_url($features->get_upgrade_url('personal')); ?>" class="button button-primary button-large"><?php _e('Get Started', 'wp-ai-site-manager'); ?></a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Professional Plan -->
        <div class="pricing-plan professional-plan">
            <div class="plan-header">
                <h3><?php _e('Professional', 'wp-ai-site-manager'); ?></h3>
                <div class="plan-price">
                    <span class="price">$79</span>
                    <span class="period"><?php _e('/year', 'wp-ai-site-manager'); ?></span>
                </div>
                <p class="plan-description"><?php _e('Perfect for small agencies and growing businesses', 'wp-ai-site-manager'); ?></p>
            </div>
            
            <div class="plan-features">
                <ul>
                    <li><?php _e('✓ Everything in Personal', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Priority email support', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Advanced AI models (GPT-4, Claude)', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Bulk content generation', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Custom AI prompts', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Advanced analytics', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ 5 site licenses', 'wp-ai-site-manager'); ?></li>
                </ul>
            </div>
            
            <div class="plan-action">
                <?php if ($upgrade_stats['current_plan'] === 'professional'): ?>
                    <span class="current-plan-badge"><?php _e('Current Plan', 'wp-ai-site-manager'); ?></span>
                <?php else: ?>
                    <a href="<?php echo esc_url($features->get_upgrade_url('professional')); ?>" class="button button-primary button-large"><?php _e('Get Started', 'wp-ai-site-manager'); ?></a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Business Plan -->
        <div class="pricing-plan business-plan">
            <div class="plan-header">
                <h3><?php _e('Business', 'wp-ai-site-manager'); ?></h3>
                <div class="plan-price">
                    <span class="price">$149</span>
                    <span class="period"><?php _e('/year', 'wp-ai-site-manager'); ?></span>
                </div>
                <p class="plan-description"><?php _e('For growing agencies and businesses', 'wp-ai-site-manager'); ?></p>
            </div>
            
            <div class="plan-features">
                <ul>
                    <li><?php _e('✓ Everything in Professional', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Security scoring & assessment', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Custom integrations & webhooks', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Unlimited exports', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Custom report templates', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Advanced user management', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ 25 site licenses', 'wp-ai-site-manager'); ?></li>
                </ul>
            </div>
            
            <div class="plan-action">
                <?php if ($upgrade_stats['current_plan'] === 'business'): ?>
                    <span class="current-plan-badge"><?php _e('Current Plan', 'wp-ai-site-manager'); ?></span>
                <?php else: ?>
                    <a href="<?php echo esc_url($features->get_upgrade_url('business')); ?>" class="button button-primary button-large"><?php _e('Get Started', 'wp-ai-site-manager'); ?></a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Enterprise Plan -->
        <div class="pricing-plan enterprise-plan">
            <div class="plan-header">
                <h3><?php _e('Enterprise', 'wp-ai-site-manager'); ?></h3>
                <div class="plan-price">
                    <span class="price">$299</span>
                    <span class="period"><?php _e('/year', 'wp-ai-site-manager'); ?></span>
                </div>
                <p class="plan-description"><?php _e('For large agencies and enterprises', 'wp-ai-site-manager'); ?></p>
            </div>
            
            <div class="plan-features">
                <ul>
                    <li><?php _e('✓ Everything in Business', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ 24/7 priority support', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ White-label options', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Custom development', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Dedicated account manager', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Advanced security features', 'wp-ai-site-manager'); ?></li>
                    <li><?php _e('✓ Unlimited site licenses', 'wp-ai-site-manager'); ?></li>
                </ul>
            </div>
            
            <div class="plan-action">
                <?php if ($upgrade_stats['current_plan'] === 'enterprise'): ?>
                    <span class="current-plan-badge"><?php _e('Current Plan', 'wp-ai-site-manager'); ?></span>
                <?php else: ?>
                    <a href="<?php echo esc_url($features->get_upgrade_url('enterprise')); ?>" class="button button-primary button-large"><?php _e('Contact Sales', 'wp-ai-site-manager'); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Feature Comparison Table -->
    <div class="wp-aism-feature-comparison">
        <h2><?php _e('Feature Comparison', 'wp-ai-site-manager'); ?></h2>
        <div class="comparison-table-wrapper">
            <table class="wp-aism-comparison-table">
                <thead>
                    <tr>
                        <th><?php _e('Feature', 'wp-ai-site-manager'); ?></th>
                        <th><?php _e('Free', 'wp-ai-site-manager'); ?></th>
                        <th><?php _e('Personal', 'wp-ai-site-manager'); ?></th>
                        <th><?php _e('Professional', 'wp-ai-site-manager'); ?></th>
                        <th><?php _e('Business', 'wp-ai-site-manager'); ?></th>
                        <th><?php _e('Enterprise', 'wp-ai-site-manager'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feature_comparison as $feature_key => $feature_data): ?>
                        <tr>
                            <td class="feature-name"><?php echo esc_html($this->get_feature_display_name($feature_key)); ?></td>
                            <td><?php echo esc_html($feature_data['free']); ?></td>
                            <td><?php echo esc_html($feature_data['personal']); ?></td>
                            <td><?php echo esc_html($feature_data['professional']); ?></td>
                            <td><?php echo esc_html($feature_data['business']); ?></td>
                            <td><?php echo esc_html($feature_data['enterprise']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- FAQ Section -->
    <div class="wp-aism-faq-section">
        <h2><?php _e('Frequently Asked Questions', 'wp-ai-site-manager'); ?></h2>
        
        <div class="faq-item">
            <h3><?php _e('Can I upgrade or downgrade my plan?', 'wp-ai-site-manager'); ?></h3>
            <p><?php _e('Yes! You can upgrade your plan at any time. Downgrades take effect at the next billing cycle. All upgrades are prorated.', 'wp-ai-site-manager'); ?></p>
        </div>
        
        <div class="faq-item">
            <h3><?php _e('Is there a free trial?', 'wp-ai-site-manager'); ?></h3>
            <p><?php _e('Yes! All premium plans come with a 7-day free trial. No credit card required to start your trial.', 'wp-ai-site-manager'); ?></p>
        </div>
        
        <div class="faq-item">
            <h3><?php _e('What happens if I reach my AI usage limit?', 'wp-ai-site-manager'); ?></h3>
            <p><?php _e('Free users will see upgrade prompts when approaching limits. Premium users have unlimited AI requests.', 'wp-ai-site-manager'); ?></p>
        </div>
        
        <div class="faq-item">
            <h3><?php _e('Do you offer refunds?', 'wp-ai-site-manager'); ?></h3>
            <p><?php _e('We offer a 30-day money-back guarantee. If you\'re not satisfied, contact our support team for a full refund.', 'wp-ai-site-manager'); ?></p>
        </div>
        
        <div class="faq-item">
            <h3><?php _e('Can I use this on multiple sites?', 'wp-ai-site-manager'); ?></h3>
            <p><?php _e('Yes! Each plan includes multiple site licenses. Personal: 1 site, Professional: 5 sites, Business: 25 sites, Enterprise: Unlimited.', 'wp-ai-site-manager'); ?></p>
        </div>
    </div>
    
    <!-- Call to Action -->
    <div class="wp-aism-cta-section">
        <div class="cta-content">
            <h2><?php _e('Ready to Get Started?', 'wp-ai-site-manager'); ?></h2>
            <p><?php _e('Join thousands of WordPress users who trust WP AI Site Manager for their security and content needs.', 'wp-ai-site-manager'); ?></p>
            <div class="cta-buttons">
                <a href="<?php echo esc_url($features->get_upgrade_url()); ?>" class="button button-primary button-large"><?php _e('Start Free Trial', 'wp-ai-site-manager'); ?></a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-ai-site-manager')); ?>" class="button button-secondary button-large"><?php _e('Back to Dashboard', 'wp-ai-site-manager'); ?></a>
            </div>
        </div>
    </div>
</div>

<?php
/**
 * Helper function to get feature display names
 */
function get_feature_display_name($feature_key) {
    $feature_names = array(
        'file_monitoring' => __('File Monitoring', 'wp-ai-site-manager'),
        'ai_requests' => __('AI Requests', 'wp-ai-site-manager'),
        'scan_frequency' => __('Scan Frequency', 'wp-ai-site-manager'),
        'support' => __('Support', 'wp-ai-site-manager'),
        'sites' => __('Site Licenses', 'wp-ai-site-manager')
    );
    
    return isset($feature_names[$feature_key]) ? $feature_names[$feature_key] : ucfirst(str_replace('_', ' ', $feature_key));
}
?>
