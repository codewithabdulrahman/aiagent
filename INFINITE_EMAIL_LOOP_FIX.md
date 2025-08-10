# Infinite Email Loop Fix

## Problem Description
The WP AI Site Manager plugin was experiencing infinite email loops due to:
1. **Excessive file scanning** (hourly cron jobs)
2. **No rate limiting** on email alerts
3. **Monitoring email-related files** that could trigger more alerts
4. **Recursive logging** without protection

## Solution Implemented

### 1. Rate Limiting
- **Email alerts**: Maximum 10 alerts per hour with 5-minute cooldown between alerts
- **File scanning**: Minimum 30 minutes between scans, changed from hourly to daily
- **Cron jobs**: Reduced from hourly to daily scanning

### 2. File Monitoring Protection
- **Excluded patterns**: Email, mail, cache, backup, and temporary files
- **File extensions**: Skip .tmp, .temp, .cache, .log, .bak, .backup files
- **Protected directories**: wp-content/cache/, wp-content/uploads/, wp-content/backup/

### 3. Error Handling
- **Try-catch blocks** around file scanning operations
- **Graceful degradation** when errors occur
- **Error logging** without triggering more alerts

### 4. Anti-Recursion Protection
- **Email action filtering**: Don't send alerts for email-related actions
- **Log action parameter**: Added `$send_alert` parameter to prevent recursive alerts
- **Transient-based tracking**: Use WordPress transients for rate limiting

## New Features

### Fix Cron Jobs Button
- **Location**: Dashboard widget quick actions
- **Function**: Clears excessive cron jobs and resets to daily scanning
- **Usage**: Click "Fix Cron Jobs" button in the dashboard widget

### Admin Notices
- **Information**: Shows when infinite email loop protection is enabled
- **Dismissible**: Can be dismissed and won't show again for 24 hours

## Configuration Changes

### Default Settings
- **Scan Interval**: Changed from "hourly" to "daily"
- **Alert Cooldown**: 5 minutes between alerts
- **Max Alerts**: 10 alerts per hour
- **Scan Cooldown**: 30 minutes minimum between scans

### Cron Jobs
- **File Scan**: `wp_aism_file_scan` - Daily instead of hourly
- **Daily Report**: `wp_aism_daily_report` - Remains daily

## Usage Instructions

### Immediate Fix
1. **Deactivate and reactivate** the plugin to clear old cron jobs
2. **Use "Fix Cron Jobs" button** in the dashboard widget
3. **Check settings** to ensure scan interval is set to "daily"

### Monitoring
- **Check logs** for any remaining excessive activity
- **Monitor email frequency** - should be much lower now
- **Review file scan logs** - should show daily scanning only

### Troubleshooting
- **Clear logs** if they're too large
- **Check cron jobs** using WP-Cron Control plugin
- **Monitor server resources** - file scanning should be much lighter

## Technical Details

### Transients Used
- `wp_aism_last_alert_time`: Tracks last email alert time
- `wp_aism_alert_count`: Counts alerts per hour
- `wp_aism_last_scan_time`: Tracks last file scan time
- `wp_aism_fix_notice_dismissed`: Tracks notice dismissal

### Database Changes
- **No schema changes** required
- **Existing data preserved**
- **New logging entries** for email actions and errors

### Performance Impact
- **Reduced server load** from less frequent scanning
- **Lower email volume** from rate limiting
- **Better error handling** prevents crashes

## Future Improvements
- **Configurable rate limits** in settings
- **Email digest** instead of individual alerts
- **Smart scanning** based on file change patterns
- **Webhook support** for external monitoring systems
