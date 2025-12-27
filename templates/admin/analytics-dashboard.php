<?php
/**
 * Analytics Dashboard Template
 *
 * Premium analytics dashboard with real-time metrics and interactive charts
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Templates/Admin
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="shahi-analytics-dashboard-wrap">
    <!-- Header Section -->
    <div class="analytics-dashboard-header">
        <div class="header-content">
            <div class="header-left">
                <h1 class="dashboard-title">
                    <span class="title-icon">📊</span>
                    <?php _e('Analytics Dashboard', 'shahi-legalops-suite'); ?>
                    <span class="title-badge">PREMIUM</span>
                </h1>
                <p class="dashboard-subtitle"><?php _e('Comprehensive insights and performance metrics', 'shahi-legalops-suite'); ?></p>
            </div>
            <div class="header-right">
                <div class="realtime-indicator">
                    <span class="realtime-dot"></span>
                    <span class="realtime-text"><?php _e('Live', 'shahi-legalops-suite'); ?></span>
                    <span class="realtime-count" id="active-users-count">--</span>
                </div>
                <button type="button" class="btn btn-export" id="export-analytics-btn">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M17 13V17H3V13H1V17C1 18.1 1.9 19 3 19H17C18.1 19 19 18.1 19 17V13H17Z" fill="currentColor"/>
                        <path d="M10 15L14.5 10.5L13.09 9.09L11 11.17V1H9V11.17L6.91 9.09L5.5 10.5L10 15Z" fill="currentColor"/>
                    </svg>
                    <?php _e('Export', 'shahi-legalops-suite'); ?>
                </button>
            </div>
        </div>
        
        <!-- Date Range Selector -->
        <div class="date-range-selector">
            <div class="range-buttons">
                <button type="button" class="range-btn <?php echo $date_range['range'] === 'today' ? 'active' : ''; ?>" data-range="today">
                    <?php _e('Today', 'shahi-legalops-suite'); ?>
                </button>
                <button type="button" class="range-btn <?php echo $date_range['range'] === 'yesterday' ? 'active' : ''; ?>" data-range="yesterday">
                    <?php _e('Yesterday', 'shahi-legalops-suite'); ?>
                </button>
                <button type="button" class="range-btn <?php echo $date_range['range'] === '7days' ? 'active' : ''; ?>" data-range="7days">
                    <?php _e('7 Days', 'shahi-legalops-suite'); ?>
                </button>
                <button type="button" class="range-btn <?php echo $date_range['range'] === '30days' ? 'active' : ''; ?>" data-range="30days">
                    <?php _e('30 Days', 'shahi-legalops-suite'); ?>
                </button>
                <button type="button" class="range-btn <?php echo $date_range['range'] === '90days' ? 'active' : ''; ?>" data-range="90days">
                    <?php _e('90 Days', 'shahi-legalops-suite'); ?>
                </button>
                <button type="button" class="range-btn <?php echo $date_range['range'] === 'custom' ? 'active' : ''; ?>" data-range="custom">
                    📅 <?php _e('Custom', 'shahi-legalops-suite'); ?>
                </button>
            </div>
            <div class="current-range-display">
                <span class="range-icon">🗓️</span>
                <span class="range-text"><?php echo esc_html($date_range['label']); ?></span>
                <span class="range-dates">(<?php echo esc_html($date_range['start_formatted']); ?> - <?php echo esc_html($date_range['end_formatted']); ?>)</span>
            </div>
        </div>
    </div>

    <!-- KPI Cards Grid -->
    <div class="kpi-cards-grid">
        <!-- Total Events Card -->
        <div class="kpi-card kpi-card-primary" data-kpi="total_events">
            <div class="kpi-card-bg"></div>
            <div class="kpi-card-content">
                <div class="kpi-header">
                    <div class="kpi-icon kpi-icon-events">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="currentColor" opacity="0.3"/>
                            <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2"/>
                            <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="kpi-trend kpi-trend-<?php echo esc_attr($kpis['total_events']['trend']); ?>">
                        <span class="trend-icon"><?php echo $kpis['total_events']['trend'] === 'up' ? '↑' : ($kpis['total_events']['trend'] === 'down' ? '↓' : '→'); ?></span>
                        <span class="trend-value"><?php echo abs($kpis['total_events']['change']); ?>%</span>
                    </div>
                </div>
                <div class="kpi-body">
                    <div class="kpi-value"><?php echo number_format($kpis['total_events']['value']); ?></div>
                    <div class="kpi-label"><?php _e('Total Events', 'shahi-legalops-suite'); ?></div>
                </div>
                <div class="kpi-footer">
                    <div class="kpi-sparkline" id="sparkline-events"></div>
                    <div class="kpi-comparison">
                        <?php printf(__('Previous: %s', 'shahi-legalops-suite'), number_format($kpis['total_events']['previous'])); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unique Users Card -->
        <div class="kpi-card kpi-card-secondary" data-kpi="unique_users">
            <div class="kpi-card-bg"></div>
            <div class="kpi-card-content">
                <div class="kpi-header">
                    <div class="kpi-icon kpi-icon-users">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <circle cx="9" cy="7" r="4" fill="currentColor" opacity="0.3"/>
                            <path d="M15.5 7C15.5 9.21 13.71 11 11.5 11C11.39 11 11.29 11 11.19 10.99C12.48 10.29 13.3 8.91 13.3 7.3C13.3 5.69 12.48 4.31 11.19 3.61C11.29 3.61 11.39 3.6 11.5 3.6C13.71 3.6 15.5 5.39 15.5 7.6V7Z" fill="currentColor"/>
                            <path d="M1 18C1 15.34 4.33 13.2 8.5 13.2C12.67 13.2 16 15.34 16 18V20H1V18Z" fill="currentColor" opacity="0.3"/>
                            <path d="M15.03 13.63C16.76 14.33 18 15.5 18 16.8V20H23V18C23 15.92 19.43 14.37 15.03 13.63Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="kpi-trend kpi-trend-<?php echo esc_attr($kpis['unique_users']['trend']); ?>">
                        <span class="trend-icon"><?php echo $kpis['unique_users']['trend'] === 'up' ? '↑' : ($kpis['unique_users']['trend'] === 'down' ? '↓' : '→'); ?></span>
                        <span class="trend-value"><?php echo abs($kpis['unique_users']['change']); ?>%</span>
                    </div>
                </div>
                <div class="kpi-body">
                    <div class="kpi-value"><?php echo number_format($kpis['unique_users']['value']); ?></div>
                    <div class="kpi-label"><?php _e('Unique Users', 'shahi-legalops-suite'); ?></div>
                </div>
                <div class="kpi-footer">
                    <div class="kpi-sparkline" id="sparkline-users"></div>
                    <div class="kpi-comparison">
                        <?php printf(__('Previous: %s', 'shahi-legalops-suite'), number_format($kpis['unique_users']['previous'])); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Views Card -->
        <div class="kpi-card kpi-card-success" data-kpi="page_views">
            <div class="kpi-card-bg"></div>
            <div class="kpi-card-content">
                <div class="kpi-header">
                    <div class="kpi-icon kpi-icon-pages">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M13 9H18.5L13 3.5V9Z" fill="currentColor" opacity="0.3"/>
                            <path d="M6 2H14L20 8V20C20 21.1 19.1 22 18 22H6C4.9 22 4 21.1 4 20V4C4 2.9 4.9 2 6 2Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="kpi-trend kpi-trend-<?php echo esc_attr($kpis['page_views']['trend']); ?>">
                        <span class="trend-icon"><?php echo $kpis['page_views']['trend'] === 'up' ? '↑' : ($kpis['page_views']['trend'] === 'down' ? '↓' : '→'); ?></span>
                        <span class="trend-value"><?php echo abs($kpis['page_views']['change']); ?>%</span>
                    </div>
                </div>
                <div class="kpi-body">
                    <div class="kpi-value"><?php echo number_format($kpis['page_views']['value']); ?></div>
                    <div class="kpi-label"><?php _e('Page Views', 'shahi-legalops-suite'); ?></div>
                </div>
                <div class="kpi-footer">
                    <div class="kpi-sparkline" id="sparkline-pageviews"></div>
                    <div class="kpi-comparison">
                        <?php printf(__('Previous: %s', 'shahi-legalops-suite'), number_format($kpis['page_views']['previous'])); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Avg Session Duration Card -->
        <div class="kpi-card kpi-card-warning" data-kpi="avg_session_duration">
            <div class="kpi-card-bg"></div>
            <div class="kpi-card-content">
                <div class="kpi-header">
                    <div class="kpi-icon kpi-icon-time">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" fill="currentColor" opacity="0.3"/>
                            <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="kpi-trend kpi-trend-<?php echo esc_attr($kpis['avg_session_duration']['trend']); ?>">
                        <span class="trend-icon"><?php echo $kpis['avg_session_duration']['trend'] === 'up' ? '↑' : ($kpis['avg_session_duration']['trend'] === 'down' ? '↓' : '→'); ?></span>
                        <span class="trend-value"><?php echo abs($kpis['avg_session_duration']['change']); ?>%</span>
                    </div>
                </div>
                <div class="kpi-body">
                    <div class="kpi-value"><?php echo gmdate('i:s', $kpis['avg_session_duration']['value']); ?></div>
                    <div class="kpi-label"><?php _e('Avg. Duration', 'shahi-legalops-suite'); ?></div>
                </div>
                <div class="kpi-footer">
                    <div class="kpi-comparison">
                        <?php printf(__('Previous: %s', 'shahi-legalops-suite'), gmdate('i:s', $kpis['avg_session_duration']['previous'])); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bounce Rate Card -->
        <div class="kpi-card kpi-card-danger" data-kpi="bounce_rate">
            <div class="kpi-card-bg"></div>
            <div class="kpi-card-content">
                <div class="kpi-header">
                    <div class="kpi-icon kpi-icon-bounce">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM13 17H11V15H13V17ZM13 13H11V7H13V13Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="kpi-trend kpi-trend-<?php echo esc_attr($kpis['bounce_rate']['trend']); ?>">
                        <span class="trend-icon"><?php echo $kpis['bounce_rate']['trend'] === 'up' ? '↑' : ($kpis['bounce_rate']['trend'] === 'down' ? '↓' : '→'); ?></span>
                        <span class="trend-value"><?php echo abs($kpis['bounce_rate']['change']); ?>%</span>
                    </div>
                </div>
                <div class="kpi-body">
                    <div class="kpi-value"><?php echo number_format($kpis['bounce_rate']['value'], 1); ?>%</div>
                    <div class="kpi-label"><?php _e('Bounce Rate', 'shahi-legalops-suite'); ?></div>
                </div>
                <div class="kpi-footer">
                    <div class="kpi-comparison">
                        <?php printf(__('Previous: %s%%', 'shahi-legalops-suite'), number_format($kpis['bounce_rate']['previous'], 1)); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conversion Rate Card -->
        <div class="kpi-card kpi-card-info" data-kpi="conversion_rate">
            <div class="kpi-card-bg"></div>
            <div class="kpi-card-content">
                <div class="kpi-header">
                    <div class="kpi-icon kpi-icon-conversion">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M21 7L9 19L3.5 13.5L4.91 12.09L9 16.17L19.59 5.59L21 7Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="kpi-trend kpi-trend-<?php echo esc_attr($kpis['conversion_rate']['trend']); ?>">
                        <span class="trend-icon"><?php echo $kpis['conversion_rate']['trend'] === 'up' ? '↑' : ($kpis['conversion_rate']['trend'] === 'down' ? '↓' : '→'); ?></span>
                        <span class="trend-value"><?php echo abs($kpis['conversion_rate']['change']); ?>%</span>
                    </div>
                </div>
                <div class="kpi-body">
                    <div class="kpi-value"><?php echo number_format($kpis['conversion_rate']['value'], 1); ?>%</div>
                    <div class="kpi-label"><?php _e('Conversion Rate', 'shahi-legalops-suite'); ?></div>
                </div>
                <div class="kpi-footer">
                    <div class="kpi-comparison">
                        <?php printf(__('Previous: %s%%', 'shahi-legalops-suite'), number_format($kpis['conversion_rate']['previous'], 1)); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-grid">
        <!-- Events Over Time Chart -->
        <div class="chart-card chart-card-large">
            <div class="chart-card-header">
                <h3 class="chart-title"><?php _e('Events Over Time', 'shahi-legalops-suite'); ?></h3>
                <div class="chart-legend">
                    <span class="legend-item legend-events">
                        <span class="legend-dot"></span><?php _e('Events', 'shahi-legalops-suite'); ?>
                    </span>
                    <span class="legend-item legend-users">
                        <span class="legend-dot"></span><?php _e('Users', 'shahi-legalops-suite'); ?>
                    </span>
                </div>
            </div>
            <div class="chart-card-body">
                <canvas id="events-over-time-chart"></canvas>
            </div>
        </div>

        <!-- Event Types Breakdown -->
        <div class="chart-card chart-card-medium">
            <div class="chart-card-header">
                <h3 class="chart-title"><?php _e('Event Types', 'shahi-legalops-suite'); ?></h3>
            </div>
            <div class="chart-card-body">
                <canvas id="event-types-chart"></canvas>
            </div>
        </div>

        <!-- Hourly Distribution -->
        <div class="chart-card chart-card-medium">
            <div class="chart-card-header">
                <h3 class="chart-title"><?php _e('Hourly Activity', 'shahi-legalops-suite'); ?></h3>
            </div>
            <div class="chart-card-body">
                <canvas id="hourly-chart"></canvas>
            </div>
        </div>

        <!-- User Journey Funnel -->
        <div class="chart-card chart-card-large">
            <div class="chart-card-header">
                <h3 class="chart-title"><?php _e('User Journey Funnel', 'shahi-legalops-suite'); ?></h3>
            </div>
            <div class="chart-card-body">
                <div class="funnel-chart" id="user-journey-funnel">
                    <?php foreach ($charts_data['user_journey'] as $index => $step): ?>
                        <?php $width = ($step['users'] / $charts_data['user_journey'][0]['users']) * 100; ?>
                        <div class="funnel-step" style="--step-width: <?php echo $width; ?>%;">
                            <div class="funnel-step-bar">
                                <div class="funnel-step-label">
                                    <span class="step-name"><?php echo esc_html($step['step']); ?></span>
                                    <span class="step-count"><?php echo number_format($step['users']); ?> users</span>
                                </div>
                            </div>
                            <?php if ($index < count($charts_data['user_journey']) - 1): ?>
                                <div class="funnel-dropoff">
                                    <?php echo $step['dropoff']; ?>% dropoff
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables Section -->
    <div class="data-tables-grid">
        <!-- Top Pages Table -->
        <div class="data-table-card">
            <div class="data-table-header">
                <h3 class="table-title"><?php _e('Top Pages', 'shahi-legalops-suite'); ?></h3>
                <button type="button" class="btn-link" id="view-all-pages">
                    <?php _e('View All', 'shahi-legalops-suite'); ?> →
                </button>
            </div>
            <div class="data-table-body">
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th><?php _e('Page', 'shahi-legalops-suite'); ?></th>
                            <th><?php _e('Views', 'shahi-legalops-suite'); ?></th>
                            <th><?php _e('Trend', 'shahi-legalops-suite'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_pages as $page => $views): ?>
                            <tr>
                                <td class="page-name"><?php echo esc_html($page); ?></td>
                                <td class="page-views"><?php echo number_format($views); ?></td>
                                <td class="page-trend">
                                    <div class="mini-sparkline"></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Events Table -->
        <div class="data-table-card">
            <div class="data-table-header">
                <h3 class="table-title"><?php _e('Top Events', 'shahi-legalops-suite'); ?></h3>
                <button type="button" class="btn-link" id="view-all-events">
                    <?php _e('View All', 'shahi-legalops-suite'); ?> →
                </button>
            </div>
            <div class="data-table-body">
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th><?php _e('Event', 'shahi-legalops-suite'); ?></th>
                            <th><?php _e('Count', 'shahi-legalops-suite'); ?></th>
                            <th><?php _e('%', 'shahi-legalops-suite'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_events_count = array_sum($top_events);
                        foreach ($top_events as $event => $count): 
                            $percentage = ($count / $total_events_count) * 100;
                        ?>
                            <tr>
                                <td class="event-name"><?php echo esc_html(str_replace('_', ' ', ucwords($event))); ?></td>
                                <td class="event-count"><?php echo number_format($count); ?></td>
                                <td class="event-percentage">
                                    <div class="percentage-bar" style="--percentage: <?php echo $percentage; ?>%;">
                                        <?php echo number_format($percentage, 1); ?>%
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Geographic Data Table -->
        <div class="data-table-card">
            <div class="data-table-header">
                <h3 class="table-title"><?php _e('Geographic Distribution', 'shahi-legalops-suite'); ?></h3>
            </div>
            <div class="data-table-body">
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th><?php _e('Country', 'shahi-legalops-suite'); ?></th>
                            <th><?php _e('Users', 'shahi-legalops-suite'); ?></th>
                            <th><?php _e('Sessions', 'shahi-legalops-suite'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($geographic_data as $geo): ?>
                            <tr>
                                <td class="geo-country"><?php echo esc_html($geo['country']); ?></td>
                                <td class="geo-users"><?php echo number_format($geo['users']); ?></td>
                                <td class="geo-sessions"><?php echo number_format($geo['sessions']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Device Breakdown -->
        <div class="data-table-card">
            <div class="data-table-header">
                <h3 class="table-title"><?php _e('Device Breakdown', 'shahi-legalops-suite'); ?></h3>
            </div>
            <div class="data-table-body">
                <div class="device-breakdown-chart">
                    <?php foreach ($device_breakdown as $device): ?>
                        <div class="device-item">
                            <div class="device-info">
                                <span class="device-icon"><?php 
                                    echo $device['device'] === 'Desktop' ? '🖥️' : 
                                         ($device['device'] === 'Mobile' ? '📱' : '📲'); 
                                ?></span>
                                <span class="device-name"><?php echo esc_html($device['device']); ?></span>
                            </div>
                            <div class="device-stats">
                                <div class="device-bar" style="--device-percentage: <?php echo $device['percentage']; ?>%;">
                                    <span class="device-percentage"><?php echo $device['percentage']; ?>%</span>
                                </div>
                                <span class="device-count"><?php echo number_format($device['count']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Consent Analytics Section -->
        <div class="data-table-card consent-analytics-section">
            <div class="data-table-header">
                <h3 class="table-title">
                    <span class="title-icon">🔒</span>
                    <?php _e('Consent Analytics', 'shahi-legalops-suite'); ?>
                </h3>
                <div class="header-actions">
                    <button type="button" class="btn btn-refresh" id="refresh-consent-analytics">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M13.65 2.35C12.2 0.9 10.21 0 8 0C3.58 0 0.01 3.58 0.01 8C0.01 12.42 3.58 16 8 16C11.73 16 14.84 13.45 15.73 10H13.65C12.83 12.33 10.61 14 8 14C4.69 14 2 11.31 2 8C2 4.69 4.69 2 8 2C9.66 2 11.14 2.69 12.22 3.78L9 7H16V0L13.65 2.35Z" fill="currentColor"/>
                        </svg>
                        <?php _e('Refresh', 'shahi-legalops-suite'); ?>
                    </button>
                </div>
            </div>
            <div class="data-table-body">
                <!-- Consent KPIs -->
                <div class="consent-kpis-grid">
                    <div class="consent-kpi">
                        <div class="kpi-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="kpi-content">
                            <div class="kpi-value" id="consent-total"><?php echo number_format($consent_stats['total_consents']); ?></div>
                            <div class="kpi-label"><?php _e('Total Consents', 'shahi-legalops-suite'); ?></div>
                        </div>
                    </div>
                    <div class="consent-kpi">
                        <div class="kpi-icon kpi-icon-success">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="kpi-content">
                            <div class="kpi-value" id="consent-acceptance-rate"><?php echo number_format($consent_stats['acceptance_rate'], 1); ?>%</div>
                            <div class="kpi-label"><?php _e('Acceptance Rate', 'shahi-legalops-suite'); ?></div>
                        </div>
                    </div>
                    <div class="consent-kpi">
                        <div class="kpi-icon kpi-icon-warning">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="kpi-content">
                            <div class="kpi-value" id="consent-pending"><?php echo number_format($consent_stats['stats_by_status']['pending'] ?? 0); ?></div>
                            <div class="kpi-label"><?php _e('Pending', 'shahi-legalops-suite'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Consent Charts -->
                <div class="consent-charts-grid">
                    <!-- Consent Status Breakdown -->
                    <div class="consent-chart-container">
                        <h4 class="chart-subtitle"><?php _e('Status Distribution', 'shahi-legalops-suite'); ?></h4>
                        <div class="consent-status-breakdown">
                            <?php foreach ($consent_stats['stats_by_status'] as $status => $count): ?>
                                <?php 
                                    $percentage = $consent_stats['status_percentages'][$status] ?? 0;
                                    $status_class = 'status-' . esc_attr($status);
                                    $status_label = ucfirst($status);
                                ?>
                                <div class="consent-status-item <?php echo $status_class; ?>">
                                    <div class="status-info">
                                        <span class="status-label"><?php echo esc_html($status_label); ?></span>
                                        <span class="status-count"><?php echo number_format($count); ?></span>
                                    </div>
                                    <div class="status-bar-wrapper">
                                        <div class="status-bar" style="width: <?php echo $percentage; ?>%;">
                                            <span class="status-percentage"><?php echo $percentage; ?>%</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Consent Type Breakdown -->
                    <div class="consent-chart-container">
                        <h4 class="chart-subtitle"><?php _e('Purpose Distribution', 'shahi-legalops-suite'); ?></h4>
                        <div class="consent-type-breakdown">
                            <?php foreach ($consent_stats['stats_by_type'] as $type => $count): ?>
                                <?php 
                                    $percentage = $consent_stats['type_percentages'][$type] ?? 0;
                                    $type_class = 'type-' . esc_attr($type);
                                    $type_label = ucfirst($type);
                                    $type_icon = array(
                                        'necessary' => '⚙️',
                                        'analytics' => '📊',
                                        'marketing' => '📢',
                                        'preferences' => '🎨'
                                    );
                                ?>
                                <div class="consent-type-item <?php echo $type_class; ?>">
                                    <div class="type-info">
                                        <span class="type-icon"><?php echo $type_icon[$type] ?? '📋'; ?></span>
                                        <span class="type-label"><?php echo esc_html($type_label); ?></span>
                                    </div>
                                    <div class="type-stats">
                                        <span class="type-count"><?php echo number_format($count); ?></span>
                                        <span class="type-percentage"><?php echo $percentage; ?>%</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Consents -->
                <div class="recent-consents-section">
                    <h4 class="chart-subtitle"><?php _e('Recent Consents', 'shahi-legalops-suite'); ?></h4>
                    <div class="recent-consents-list">
                        <?php if (!empty($consent_stats['recent_consents'])): ?>
                            <?php foreach ($consent_stats['recent_consents'] as $consent): ?>
                                <div class="recent-consent-item">
                                    <div class="consent-item-header">
                                        <span class="consent-type-badge consent-type-<?php echo esc_attr($consent->type); ?>">
                                            <?php echo esc_html(ucfirst($consent->type)); ?>
                                        </span>
                                        <span class="consent-status-badge consent-status-<?php echo esc_attr($consent->status); ?>">
                                            <?php echo esc_html(ucfirst($consent->status)); ?>
                                        </span>
                                    </div>
                                    <div class="consent-item-meta">
                                        <span class="consent-date"><?php echo esc_html(date_i18n('M j, Y g:i A', strtotime($consent->created_at))); ?></span>
                                        <?php if ($consent->user_id): ?>
                                            <span class="consent-user"><?php printf(__('User ID: %d', 'shahi-legalops-suite'), $consent->user_id); ?></span>
                                        <?php else: ?>
                                            <span class="consent-user"><?php _e('Anonymous', 'shahi-legalops-suite'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-consents-message">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="currentColor" opacity="0.3"/>
                                </svg>
                                <p><?php _e('No recent consents recorded yet.', 'shahi-legalops-suite'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="analytics-loading-overlay" id="analytics-loading" style="display: none;">
        <div class="loading-spinner">
            <div class="spinner-ring"></div>
            <div class="spinner-ring"></div>
            <div class="spinner-ring"></div>
            <p class="loading-text"><?php _e('Loading analytics data...', 'shahi-legalops-suite'); ?></p>
        </div>
    </div>
</div>

<!-- Hidden Data for JavaScript -->
<script type="text/javascript">
    window.shahiAnalyticsDashboardData = {
        trends: <?php echo json_encode($trends); ?>,
        chartsData: <?php echo json_encode($charts_data); ?>,
        dateRange: <?php echo json_encode($date_range); ?>,
        consentStats: <?php echo json_encode($consent_stats); ?>,
        nonce: '<?php echo wp_create_nonce('shahi_analytics_dashboard'); ?>'
    };
</script>

