# WP AI Site Manager Pro

A professional AI-powered WordPress security monitoring, content generation, and site management plugin.

## Features

### 🔒 Security & Monitoring
- **File Integrity Monitoring**: Tracks changes to critical WordPress files
- **Activity Logging**: Comprehensive logging of user actions, logins, and system changes
- **Real-time Alerts**: Email notifications for security events
- **Rate Limiting**: Prevents infinite email loops and excessive scanning

### 🤖 AI Content Tools
- **AI Chat Assistant**: Built-in chat interface for content help
- **Content Generation**: AI-powered content creation for posts and pages
- **Smart Prompts**: Context-aware content suggestions
- **Usage Tracking**: Monitor AI usage with role-based limits

### 📊 Reporting & Analytics
- **Daily Reports**: Automated security and activity summaries
- **Dashboard Widget**: Quick overview of recent activity
- **Export Capabilities**: Download logs and reports
- **Performance Metrics**: Track system health and usage

### ⚙️ Management Features
- **Plugin/Theme Monitoring**: Track activation/deactivation
- **User Activity Tracking**: Monitor user actions and role changes
- **Customizable Settings**: Configure monitoring preferences
- **Multi-site Support**: Manage multiple WordPress installations

## Installation

1. Upload the plugin files to `/wp-content/plugins/wp-ai-site-manager/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin settings under 'AI Site Manager' in the admin menu
4. Set up your OpenAI API key for AI features

## Configuration

### Basic Settings
- **Scan Interval**: Choose between daily, weekly, or custom intervals
- **Alert Emails**: Set email addresses for security notifications
- **File Types**: Select which file types to monitor
- **Monitoring Directories**: Choose which directories to scan

### AI Configuration
- **OpenAI API Key**: Required for AI content generation
- **Usage Limits**: Set daily limits per user role
- **Model Selection**: Choose AI models for different tasks

### Security Settings
- **Alert Thresholds**: Configure when to send alerts
- **Excluded Patterns**: Set patterns to ignore during scanning
- **Log Retention**: Configure how long to keep logs

## Usage

### Dashboard Widget
The dashboard widget provides a quick overview of:
- Recent security events
- User activity
- System health status
- Quick action buttons

### AI Tools
- **Chat Bar**: Access AI assistance from any admin page
- **Content Generation**: Generate content directly in the post editor
- **Meta Box**: AI tools sidebar in post editing

### Activity Logs
View detailed logs of:
- User actions
- Security events
- File changes
- System activities

## API Integration

### OpenAI API
The plugin integrates with OpenAI's API for:
- Content generation
- Chat assistance
- Smart suggestions
- Context-aware responses

### Webhook Support
Configure webhooks for:
- External monitoring systems
- Custom integrations
- Third-party services

## Troubleshooting

### Common Issues

#### Infinite Email Loops
If you experience excessive emails:
1. Use the "Fix Cron Jobs" button in the dashboard widget
2. Check your scan interval settings
3. Review excluded file patterns

#### AI Features Not Working
1. Verify your OpenAI API key is set
2. Check usage limits for your user role
3. Ensure the API key has sufficient credits

#### Performance Issues
1. Reduce scan frequency
2. Limit monitored directories
3. Adjust log retention settings

### Support
- Check the plugin settings for configuration help
- Review activity logs for error details
- Use the dashboard widget's quick actions

## Development

### Hooks and Filters
The plugin provides various hooks for customization:
- `wp_aism_can_use_advanced_monitoring`
- `wp_aism_can_use_unlimited_ai`
- `wp_aism_can_use_advanced_ai`

### Custom Integrations
Extend the plugin with:
- Custom monitoring rules
- Additional AI models
- Custom reporting formats
- Integration with external services

## Changelog

### Version 1.0.0
- Initial release
- File integrity monitoring
- AI content tools
- Activity logging
- Security alerts
- Dashboard integration

## License

GPL v2 or later

## Support

For support and feature requests, please visit our website or contact our support team.

---

**Note**: This plugin requires WordPress 5.0+ and PHP 7.4+.
