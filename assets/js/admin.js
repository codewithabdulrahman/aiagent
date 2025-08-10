/**
 * WP AI Site Manager - Admin JavaScript
 */

(function($) {
    'use strict';

    // Main plugin object
    var WPAISM = {
        
        init: function() {
            this.bindEvents();
            this.initTooltips();
            this.initCharts();
        },
        
        bindEvents: function() {
            // Global event handlers
            $(document).on('click', '.wp-aism-generate-btn', this.handleContentGeneration);
            $(document).on('click', '.wp-aism-copy-btn', this.handleCopyContent);
            $(document).on('click', '.wp-aism-insert-btn', this.handleInsertContent);
            $(document).on('click', '.wp-aism-clear-btn', this.handleClearContent);
            
            // Settings page events
            $(document).on('change', '#scan-interval', this.handleScanIntervalChange);
            $(document).on('click', '#test-api-connection', this.handleTestAPIConnection);
            $(document).on('click', '#export-logs', this.handleExportLogs);
            $(document).on('click', '#import-settings', this.handleImportSettings);
            
            // Dashboard widget events
            $(document).on('click', '.wp-aism-refresh-widget', this.handleRefreshWidget);
            $(document).on('click', '.wp-aism-view-all-logs', this.handleViewAllLogs);
            
            // Real-time monitoring
            this.initRealTimeMonitoring();
        },
        
        initTooltips: function() {
            // Initialize tooltips for better UX
            $('[data-tooltip]').each(function() {
                var $element = $(this);
                var tooltip = $element.attr('data-tooltip');
                
                $element.tooltip({
                    title: tooltip,
                    placement: 'top',
                    trigger: 'hover'
                });
            });
        },
        
        initCharts: function() {
            // Initialize charts if Chart.js is available
            if (typeof Chart !== 'undefined') {
                this.initActivityChart();
                this.initUsageChart();
            }
        },
        
        initActivityChart: function() {
            var ctx = document.getElementById('wp-aism-activity-chart');
            if (!ctx) return;
            
            var chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Site Activity',
                        data: [],
                        borderColor: '#0073aa',
                        backgroundColor: 'rgba(0, 115, 170, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Store chart reference for updates
            WPAISM.activityChart = chart;
        },
        
        initUsageChart: function() {
            var ctx = document.getElementById('wp-aism-usage-chart');
            if (!ctx) return;
            
            var chart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Used', 'Remaining'],
                    datasets: [{
                        data: [0, 100],
                        backgroundColor: ['#0073aa', '#e1e5e9']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
            
            // Store chart reference for updates
            WPAISM.usageChart = chart;
        },
        
        initRealTimeMonitoring: function() {
            // Set up real-time monitoring updates
            if (wpAISM && wpAISM.realTimeUpdates) {
                setInterval(function() {
                    WPAISM.updateRealTimeData();
                }, 30000); // Update every 30 seconds
            }
        },
        
        updateRealTimeData: function() {
            $.ajax({
                url: wpAISM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_aism_get_real_time_data',
                    nonce: wpAISM.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WPAISM.updateDashboardData(response.data);
                    }
                }
            });
        },
        
        updateDashboardData: function(data) {
            // Update dashboard widget with real-time data
            if (data.recentActivity) {
                WPAISM.updateActivityList(data.recentActivity);
            }
            
            if (data.usageStats) {
                WPAISM.updateUsageStats(data.usageStats);
            }
            
            if (data.systemStatus) {
                WPAISM.updateSystemStatus(data.systemStatus);
            }
        },
        
        updateActivityList: function(activities) {
            var $list = $('.wp-aism-activity-list');
            if (!$list.length) return;
            
            $list.empty();
            
            for (var i = 0; i < activities.length; i++) {
                var activity = activities[i];
                var severityClass = 'wp-aism-activity-severity-' + activity.severity;
                var timeAgo = WPAISM.timeAgo(activity.timestamp);
                
                var item = '<li class="wp-aism-activity-item">' +
                    '<div class="wp-aism-activity-details">' +
                        '<div class="wp-aism-activity-action">' + activity.action_type + '</div>' +
                        '<div class="wp-aism-activity-time">' + timeAgo + '</div>' +
                    '</div>' +
                    '<span class="wp-aism-activity-severity ' + severityClass + '">' + activity.severity + '</span>' +
                '</li>';
                
                $list.append(item);
            }
        },
        
        updateUsageStats: function(stats) {
            // Update usage statistics
            $('.wp-aism-stat-card .stat-number').each(function() {
                var $stat = $(this);
                var statType = $stat.closest('.wp-aism-stat-card').find('h3').text().toLowerCase();
                
                if (statType.includes('today') && stats.today !== undefined) {
                    $stat.text(stats.today);
                } else if (statType.includes('month') && stats.month !== undefined) {
                    $stat.text(stats.month);
                } else if (statType.includes('limit') && stats.limit !== undefined) {
                    $stat.text(stats.limit);
                }
            });
            
            // Update usage chart if available
            if (WPAISM.usageChart && stats.today !== undefined && stats.limit !== undefined) {
                var used = stats.today;
                var remaining = stats.limit - stats.today;
                
                WPAISM.usageChart.data.datasets[0].data = [used, remaining];
                WPAISM.usageChart.update();
            }
        },
        
        updateSystemStatus: function(status) {
            // Update system status indicators
            $('.wp-aism-system-status').each(function() {
                var $status = $(this);
                var statusType = $status.data('status-type');
                
                if (status[statusType]) {
                    $status.removeClass('status-error status-warning').addClass('status-ok');
                    $status.text('OK');
                } else {
                    $status.removeClass('status-ok status-warning').addClass('status-error');
                    $status.text('Error');
                }
            });
        },
        
        handleContentGeneration: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var type = $button.data('type');
            var data = {};
            
            // Collect form data based on type
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
                default:
                    data = $button.closest('form').serializeArray();
            }
            
            if (WPAISM.validateFormData(data)) {
                WPAISM.generateContent(type, data, $button);
            }
        },
        
        generateContent: function(type, data, $button) {
            var originalText = $button.text();
            $button.prop('disabled', true).text('Generating...');
            
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
                        WPAISM.showNotification('Content generated successfully!', 'success');
                    } else {
                        WPAISM.showNotification(response.data, 'error');
                    }
                },
                error: function() {
                    $('.wp-aism-loading').hide();
                    WPAISM.showNotification('An error occurred while generating content.', 'error');
                },
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        },
        
        handleCopyContent: function(e) {
            e.preventDefault();
            
            var content = $('#generated-content').text();
            if (content) {
                navigator.clipboard.writeText(content).then(function() {
                    WPAISM.showNotification('Content copied to clipboard!', 'success');
                }).catch(function() {
                    // Fallback for older browsers
                    var textArea = document.createElement('textarea');
                    textArea.value = content;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    WPAISM.showNotification('Content copied to clipboard!', 'success');
                });
            }
        },
        
        handleInsertContent: function(e) {
            e.preventDefault();
            
            var content = $('#generated-content').text();
            if (content) {
                // Try to insert into active editor
                if (typeof wp !== 'undefined' && wp.data && wp.data.dispatch) {
                    // Gutenberg editor
                    wp.data.dispatch('core/editor').insertBlocks(
                        wp.blocks.createBlock('core/paragraph', { content: content })
                    );
                } else if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor) {
                    // Classic editor
                    tinyMCE.activeEditor.execCommand('mceInsertContent', false, content);
                } else {
                    // Fallback - copy to clipboard
                    navigator.clipboard.writeText(content);
                }
                
                WPAISM.showNotification('Content inserted successfully!', 'success');
            }
        },
        
        handleClearContent: function(e) {
            e.preventDefault();
            
            $('.wp-aism-generated-content').hide();
            $('#generated-content').empty();
            
            // Clear form fields
            $('input[type="text"], textarea').val('');
            $('select').prop('selectedIndex', 0);
        },
        
        handleScanIntervalChange: function(e) {
            var interval = $(this).val();
            
            $.ajax({
                url: wpAISM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_aism_update_scan_interval',
                    nonce: wpAISM.nonce,
                    interval: interval
                },
                success: function(response) {
                    if (response.success) {
                        WPAISM.showNotification('Scan interval updated successfully!', 'success');
                    } else {
                        WPAISM.showNotification(response.data, 'error');
                    }
                }
            });
        },
        
        handleTestAPIConnection: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var originalText = $button.text();
            $button.prop('disabled', true).text('Testing...');
            
            $.ajax({
                url: wpAISM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_aism_test_api_connection',
                    nonce: wpAISM.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WPAISM.showNotification('API connection successful!', 'success');
                    } else {
                        WPAISM.showNotification(response.data, 'error');
                    }
                },
                error: function() {
                    WPAISM.showNotification('API connection failed.', 'error');
                },
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        },
        
        handleExportLogs: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var originalText = $button.text();
            $button.prop('disabled', true).text('Exporting...');
            
            $.ajax({
                url: wpAISM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_aism_export_logs',
                    nonce: wpAISM.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Create download link
                        var link = document.createElement('a');
                        link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(response.data.csv);
                        link.download = 'wp-aism-logs-' + new Date().toISOString().split('T')[0] + '.csv';
                        link.click();
                        
                        WPAISM.showNotification('Logs exported successfully!', 'success');
                    } else {
                        WPAISM.showNotification(response.data, 'error');
                    }
                },
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        },
        
        handleImportSettings: function(e) {
            e.preventDefault();
            
            var input = document.createElement('input');
            input.type = 'file';
            input.accept = '.json';
            
            input.onchange = function(e) {
                var file = e.target.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        try {
                            var settings = JSON.parse(e.target.result);
                            WPAISM.importSettings(settings);
                        } catch (error) {
                            WPAISM.showNotification('Invalid settings file.', 'error');
                        }
                    };
                    reader.readAsText(file);
                }
            };
            
            input.click();
        },
        
        importSettings: function(settings) {
            $.ajax({
                url: wpAISM.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_aism_import_settings',
                    nonce: wpAISM.nonce,
                    settings: JSON.stringify(settings)
                },
                success: function(response) {
                    if (response.success) {
                        WPAISM.showNotification('Settings imported successfully!', 'success');
                        location.reload();
                    } else {
                        WPAISM.showNotification(response.data, 'error');
                    }
                }
            });
        },
        
        handleRefreshWidget: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            $button.addClass('rotating');
            
            WPAISM.updateRealTimeData();
            
            setTimeout(function() {
                $button.removeClass('rotating');
            }, 1000);
        },
        
        handleViewAllLogs: function(e) {
            e.preventDefault();
            
            // Navigate to logs page
            window.location.href = wpAISM.adminUrl + 'admin.php?page=wp-ai-site-manager-logs';
        },
        
        validateFormData: function(data) {
            for (var key in data) {
                if (data[key] === '' || data[key] === null || data[key] === undefined) {
                    WPAISM.showNotification('Please fill in all required fields.', 'error');
                    return false;
                }
            }
            return true;
        },
        
        showNotification: function(message, type) {
            var notification = $('<div class="wp-aism-notification wp-aism-notification-' + type + '">' + message + '</div>');
            
            $('body').append(notification);
            
            notification.fadeIn().delay(3000).fadeOut(function() {
                $(this).remove();
            });
        },
        
        timeAgo: function(timestamp) {
            var now = new Date();
            var past = new Date(timestamp);
            var diff = Math.floor((now - past) / 1000);
            
            if (diff < 60) return 'Just now';
            if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
            if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
            if (diff < 2592000) return Math.floor(diff / 86400) + ' days ago';
            
            return past.toLocaleDateString();
        },
        
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        WPAISM.init();
    });
    
    // Make WPAISM available globally
    window.WPAISM = WPAISM;
    
})(jQuery);

// Additional utility functions
function wpAISMFormatBytes(bytes, decimals) {
    if (typeof decimals === 'undefined') decimals = 2;
    if (bytes === 0) return '0 Bytes';
    
    var k = 1024;
    var dm = decimals < 0 ? 0 : decimals;
    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    
    var i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

function wpAISMFormatDate(dateString) {
    var date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

function wpAISMConfirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}
