/**
 * Analytics Dashboard - JavaScript
 *
 * Premium analytics dashboard with real-time updates and interactive charts
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Assets/JS
 * @since      1.0.0
 */

(function($) {
    'use strict';

    /**
     * Analytics Dashboard Controller
     */
    const AnalyticsDashboard = {
        
        /**
         * Initialize
         */
        init: function() {
            this.initCharts();
            this.initSparklines();
            this.initEventHandlers();
            this.initRealtimeUpdates();
            this.animateKPICards();
        },
        
        /**
         * Initialize charts using Chart.js
         */
        initCharts: function() {
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js not loaded');
                return;
            }
            
            // Chart default configuration
            Chart.defaults.color = '#cbd5e1';
            Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';
            Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            
            this.initEventsOverTimeChart();
            this.initEventTypesChart();
            this.initHourlyChart();
        },
        
        /**
         * Events over time line chart
         */
        initEventsOverTimeChart: function() {
            const ctx = document.getElementById('events-over-time-chart');
            if (!ctx) return;
            
            const data = window.shahiAnalyticsDashboardData.chartsData.events_over_time;
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => d.label),
                    datasets: [
                        {
                            label: 'Events',
                            data: data.map(d => d.events),
                            borderColor: '#00d4ff',
                            backgroundColor: 'rgba(0, 212, 255, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#00d4ff',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                        },
                        {
                            label: 'Users',
                            data: data.map(d => d.users),
                            borderColor: '#7c3aed',
                            backgroundColor: 'rgba(124, 58, 237, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#7c3aed',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            titleColor: '#fff',
                            bodyColor: '#cbd5e1',
                            borderColor: 'rgba(0, 212, 255, 0.3)',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        },
        
        /**
         * Event types doughnut chart
         */
        initEventTypesChart: function() {
            const ctx = document.getElementById('event-types-chart');
            if (!ctx) return;
            
            const data = window.shahiAnalyticsDashboardData.chartsData.event_types_breakdown;
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(d => d.type),
                    datasets: [{
                        data: data.map(d => d.count),
                        backgroundColor: data.map(d => d.color),
                        borderWidth: 2,
                        borderColor: '#0f172a',
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            titleColor: '#fff',
                            bodyColor: '#cbd5e1',
                            borderColor: 'rgba(0, 212, 255, 0.3)',
                            borderWidth: 1,
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': ' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        },
        
        /**
         * Hourly distribution bar chart
         */
        initHourlyChart: function() {
            const ctx = document.getElementById('hourly-chart');
            if (!ctx) return;
            
            const data = window.shahiAnalyticsDashboardData.chartsData.hourly_distribution;
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(d => d.hour),
                    datasets: [{
                        label: 'Activity',
                        data: data.map(d => d.value),
                        backgroundColor: function(context) {
                            const chart = context.chart;
                            const {ctx, chartArea} = chart;
                            if (!chartArea) return;
                            
                            const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                            gradient.addColorStop(0, 'rgba(124, 58, 237, 0.4)');
                            gradient.addColorStop(1, 'rgba(0, 212, 255, 0.8)');
                            return gradient;
                        },
                        borderColor: '#00d4ff',
                        borderWidth: 1,
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            titleColor: '#fff',
                            bodyColor: '#cbd5e1',
                            borderColor: 'rgba(0, 212, 255, 0.3)',
                            borderWidth: 1,
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    return 'Activity: ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        },
        
        /**
         * Initialize sparklines for KPI cards
         */
        initSparklines: function() {
            if (typeof Chart === 'undefined') return;
            
            const trends = window.shahiAnalyticsDashboardData.trends;
            
            // Events sparkline
            this.createSparkline('sparkline-events', trends.map(t => t.events), '#00d4ff');
            
            // Users sparkline
            this.createSparkline('sparkline-users', trends.map(t => t.users), '#7c3aed');
            
            // Pageviews sparkline
            this.createSparkline('sparkline-pageviews', trends.map(t => t.pageviews), '#00ff88');
        },
        
        /**
         * Create a sparkline chart
         */
        createSparkline: function(elementId, data, color) {
            const canvas = document.getElementById(elementId);
            if (!canvas) return;
            
            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: new Array(data.length).fill(''),
                    datasets: [{
                        data: data,
                        borderColor: color,
                        backgroundColor: color + '20',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    },
                    scales: {
                        x: { display: false },
                        y: { display: false }
                    }
                }
            });
        },
        
        /**
         * Initialize event handlers
         */
        initEventHandlers: function() {
            // Date range buttons
            $('.range-btn').on('click', function() {
                const range = $(this).data('range');
                window.location.href = '?page=shahi-analytics-dashboard&range=' + range;
            });
            
            // Export button
            $('#export-analytics-btn').on('click', function() {
                AnalyticsDashboard.exportAnalytics();
            });
            
            // View all buttons
            $('#view-all-pages, #view-all-events').on('click', function() {
                AnalyticsDashboard.showNotification('Feature coming soon!', 'info');
            });
            
            // KPI card hover effects
            $('.kpi-card').on('mouseenter', function() {
                $(this).find('.kpi-icon').addClass('animated');
            }).on('mouseleave', function() {
                $(this).find('.kpi-icon').removeClass('animated');
            });
        },
        
        /**
         * Initialize real-time updates
         */
        initRealtimeUpdates: function() {
            // Update active users count every 5 seconds
            this.updateActiveUsers();
            setInterval(() => {
                this.updateActiveUsers();
            }, 5000);
        },
        
        /**
         * Update active users count
         */
        updateActiveUsers: function() {
            $.ajax({
                url: shahiAnalyticsDashboardConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shahi_get_realtime_stats',
                    nonce: shahiAnalyticsDashboardConfig.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#active-users-count').text(response.data.active_users);
                        
                        // Animate the update
                        $('#active-users-count').addClass('pulse');
                        setTimeout(() => {
                            $('#active-users-count').removeClass('pulse');
                        }, 300);
                    }
                }
            });
        },
        
        /**
         * Export analytics data
         */
        exportAnalytics: function() {
            this.showLoading();
            
            $.ajax({
                url: shahiAnalyticsDashboardConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shahi_export_analytics',
                    nonce: shahiAnalyticsDashboardConfig.nonce,
                    date_range: window.shahiAnalyticsDashboardData?.dateRange || 'last_30_days'
                },
                success: function(response) {
                    AnalyticsDashboard.hideLoading();
                    
                    if (response.success) {
                        AnalyticsDashboard.showNotification('Analytics exported successfully!', 'success');
                        
                        // Download would happen here
                        // window.location.href = response.data.url;
                    } else {
                        AnalyticsDashboard.showNotification(response.data.message || 'Export failed', 'error');
                    }
                },
                error: function() {
                    AnalyticsDashboard.hideLoading();
                    AnalyticsDashboard.showNotification('Export failed. Please try again.', 'error');
                }
            });
        },
        
        /**
         * Animate KPI cards on load
         */
        animateKPICards: function() {
            $('.kpi-card').each(function(index) {
                $(this).css({
                    opacity: 0,
                    transform: 'translateY(20px)'
                });
                
                setTimeout(() => {
                    $(this).css({
                        opacity: 1,
                        transform: 'translateY(0)',
                        transition: 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)'
                    });
                }, index * 100);
            });
            
            // Animate percentage bars
            setTimeout(() => {
                $('.event-percentage .percentage-bar').each(function() {
                    const percentage = $(this).css('--percentage') || '0%';
                    $(this).css('--percentage', '0%');
                    setTimeout(() => {
                        $(this).css('--percentage', percentage);
                    }, 100);
                });
                
                $('.device-bar').each(function() {
                    const percentage = $(this).css('--device-percentage') || '0%';
                    $(this).css('--device-percentage', '0%');
                    setTimeout(() => {
                        $(this).css('--device-percentage', percentage);
                    }, 100);
                });
            }, 800);
        },
        
        /**
         * Show loading overlay
         */
        showLoading: function() {
            $('#analytics-loading').fadeIn(200);
        },
        
        /**
         * Hide loading overlay
         */
        hideLoading: function() {
            $('#analytics-loading').fadeOut(200);
        },
        
        /**
         * Show notification
         */
        showNotification: function(message, type = 'info') {
            // Create notification element
            const notification = $('<div>', {
                class: 'analytics-notification analytics-notification-' + type,
                html: '<span class="notification-icon">' + this.getNotificationIcon(type) + '</span>' +
                      '<span class="notification-message">' + message + '</span>' +
                      '<button class="notification-close">×</button>'
            });
            
            // Add to body
            $('body').append(notification);
            
            // Show with animation
            setTimeout(() => {
                notification.addClass('show');
            }, 100);
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                this.hideNotification(notification);
            }, 5000);
            
            // Close button
            notification.find('.notification-close').on('click', () => {
                this.hideNotification(notification);
            });
        },
        
        /**
         * Hide notification
         */
        hideNotification: function(notification) {
            notification.removeClass('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        },
        
        /**
         * Get notification icon
         */
        getNotificationIcon: function(type) {
            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ℹ'
            };
            return icons[type] || icons.info;
        }
    };
    
    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        if ($('.shahi-analytics-dashboard-wrap').length) {
            AnalyticsDashboard.init();
        }
    });
    
})(jQuery);

/**
 * Notification Styles (inline since we're using JS to create them)
 */
const notificationStyles = `
    <style>
        .analytics-notification {
            position: fixed;
            top: 32px;
            right: 32px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.95) 100%);
            color: #fff;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: 400px;
            z-index: 10000;
            transform: translateX(500px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .analytics-notification.show {
            transform: translateX(0);
            opacity: 1;
        }
        
        .analytics-notification-success {
            border-left: 4px solid #00ff88;
        }
        
        .analytics-notification-error {
            border-left: 4px solid #ff4444;
        }
        
        .analytics-notification-warning {
            border-left: 4px solid #ffc107;
        }
        
        .analytics-notification-info {
            border-left: 4px solid #00d4ff;
        }
        
        .notification-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
        }
        
        .notification-message {
            flex: 1;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .notification-close {
            background: none;
            border: none;
            color: #cbd5e1;
            font-size: 24px;
            line-height: 1;
            cursor: pointer;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.3s ease;
        }
        
        .notification-close:hover {
            color: #fff;
        }
        
        #active-users-count.pulse {
            animation: countPulse 0.3s ease;
        }
        
        @keyframes countPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); color: #00ff88; }
        }
    </style>
`;

// Inject notification styles
document.head.insertAdjacentHTML('beforeend', notificationStyles);
