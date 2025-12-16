<?php
/**
 * Analytics Page Template
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Templates/Admin
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap shahi-legalops-suite-admin shahi-analytics-page">
    
    <!-- Page Header -->
    <div class="shahi-page-header">
        <div class="shahi-header-content">
            <h1 class="shahi-page-title">
                <span class="dashicons dashicons-chart-line"></span>
                <?php echo esc_html__('Analytics Dashboard', 'shahi-legalops-suite'); ?>
            </h1>
            <p class="shahi-page-description">
                <?php echo esc_html__('Track plugin performance, user behavior, and event history', 'shahi-legalops-suite'); ?>
            </p>
        </div>
        <div class="shahi-header-actions">
            <select class="shahi-date-range-selector" onchange="window.location.href='?page=shahi-legalops-suite-analytics&range='+this.value">
                <option value="24hours" <?php selected($date_range['range'], '24hours'); ?>><?php echo esc_html__('Last 24 Hours', 'shahi-legalops-suite'); ?></option>
                <option value="7days" <?php selected($date_range['range'], '7days'); ?>><?php echo esc_html__('Last 7 Days', 'shahi-legalops-suite'); ?></option>
                <option value="30days" <?php selected($date_range['range'], '30days'); ?>><?php echo esc_html__('Last 30 Days', 'shahi-legalops-suite'); ?></option>
                <option value="90days" <?php selected($date_range['range'], '90days'); ?>><?php echo esc_html__('Last 90 Days', 'shahi-legalops-suite'); ?></option>
                <option value="all" <?php selected($date_range['range'], 'all'); ?>><?php echo esc_html__('All Time', 'shahi-legalops-suite'); ?></option>
            </select>
            <button type="button" class="shahi-btn shahi-btn-secondary shahi-export-btn" data-format="csv">
                <span class="dashicons dashicons-download"></span>
                <?php echo esc_html__('Export CSV', 'shahi-legalops-suite'); ?>
            </button>
        </div>
    </div>

    <!-- Overview Statistics -->
    <div class="shahi-stats-grid">
        <div class="shahi-stat-card shahi-stat-primary">
            <div class="shahi-stat-icon">
                <span class="dashicons dashicons-chart-bar"></span>
            </div>
            <div class="shahi-stat-content">
                <h3 class="shahi-stat-title"><?php echo esc_html__('Total Events', 'shahi-legalops-suite'); ?></h3>
                <div class="shahi-stat-value">
                    <span class="shahi-stat-number"><?php echo esc_html(number_format($overview['total_events'])); ?></span>
                </div>
            </div>
        </div>

        <div class="shahi-stat-card shahi-stat-success">
            <div class="shahi-stat-icon">
                <span class="dashicons dashicons-groups"></span>
            </div>
            <div class="shahi-stat-content">
                <h3 class="shahi-stat-title"><?php echo esc_html__('Unique Users', 'shahi-legalops-suite'); ?></h3>
                <div class="shahi-stat-value">
                    <span class="shahi-stat-number"><?php echo esc_html(number_format($overview['unique_users'])); ?></span>
                </div>
            </div>
        </div>

        <div class="shahi-stat-card shahi-stat-accent">
            <div class="shahi-stat-icon">
                <span class="dashicons dashicons-calendar"></span>
            </div>
            <div class="shahi-stat-content">
                <h3 class="shahi-stat-title"><?php echo esc_html__('Avg. Per Day', 'shahi-legalops-suite'); ?></h3>
                <div class="shahi-stat-value">
                    <span class="shahi-stat-number"><?php echo esc_html(number_format($overview['avg_per_day'], 2)); ?></span>
                </div>
            </div>
        </div>

        <div class="shahi-stat-card shahi-stat-info">
            <div class="shahi-stat-icon">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="shahi-stat-content">
                <h3 class="shahi-stat-title"><?php echo esc_html__('Peak Hour', 'shahi-legalops-suite'); ?></h3>
                <div class="shahi-stat-value">
                    <span class="shahi-stat-number"><?php echo esc_html($overview['most_active_hour']); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Content Grid -->
    <div class="shahi-analytics-grid">
        
        <!-- Events Over Time Chart -->
        <div class="shahi-analytics-section shahi-chart-section">
            <div class="shahi-card">
                <div class="shahi-card-header">
                    <h2 class="shahi-card-title">
                        <span class="dashicons dashicons-chart-area"></span>
                        <?php echo esc_html__('Events Over Time', 'shahi-legalops-suite'); ?>
                    </h2>
                    <?php if (isset($chart_data['is_mock']) && $chart_data['is_mock']): ?>
                        <span class="shahi-badge shahi-badge-warning shahi-mock-badge" title="<?php echo esc_attr__('This chart displays mock data for demonstration purposes', 'shahi-legalops-suite'); ?>">
                            <?php echo esc_html__('Mock Data', 'shahi-legalops-suite'); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="shahi-card-body">
                    <div class="shahi-chart-container">
                        <canvas id="shahi-events-chart" 
                                data-labels="<?php echo esc_attr(wp_json_encode($chart_data['labels'])); ?>" 
                                data-values="<?php echo esc_attr(wp_json_encode($chart_data['data'])); ?>"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hourly Distribution Chart -->
        <div class="shahi-analytics-section shahi-chart-section">
            <div class="shahi-card">
                <div class="shahi-card-header">
                    <h2 class="shahi-card-title">
                        <span class="dashicons dashicons-chart-bar"></span>
                        <?php echo esc_html__('Hourly Distribution', 'shahi-legalops-suite'); ?>
                    </h2>
                    <?php if (isset($hourly_data['is_mock']) && $hourly_data['is_mock']): ?>
                        <span class="shahi-badge shahi-badge-warning shahi-mock-badge" title="<?php echo esc_attr__('This chart displays mock data for demonstration purposes', 'shahi-legalops-suite'); ?>">
                            <?php echo esc_html__('Mock Data', 'shahi-legalops-suite'); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="shahi-card-body">
                    <div class="shahi-chart-container">
                        <canvas id="shahi-hourly-chart" 
                                data-labels="<?php echo esc_attr(wp_json_encode($hourly_data['labels'])); ?>" 
                                data-values="<?php echo esc_attr(wp_json_encode($hourly_data['data'])); ?>"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- User Activity Pie Chart -->
        <div class="shahi-analytics-section">
            <div class="shahi-card">
                <div class="shahi-card-header">
                    <h2 class="shahi-card-title">
                        <span class="dashicons dashicons-chart-pie"></span>
                        <?php echo esc_html__('User Activity', 'shahi-legalops-suite'); ?>
                    </h2>
                    <?php if (isset($user_activity['is_mock']) && $user_activity['is_mock']): ?>
                        <span class="shahi-badge shahi-badge-warning shahi-mock-badge" title="<?php echo esc_attr__('This chart displays mock data for demonstration purposes', 'shahi-legalops-suite'); ?>">
                            <?php echo esc_html__('Mock Data', 'shahi-legalops-suite'); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="shahi-card-body">
                    <div class="shahi-chart-container shahi-pie-chart">
                        <canvas id="shahi-user-activity-chart" 
                                data-labels="<?php echo esc_attr(wp_json_encode($user_activity['labels'])); ?>" 
                                data-values="<?php echo esc_attr(wp_json_encode($user_activity['data'])); ?>"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Event Types Breakdown -->
        <div class="shahi-analytics-section">
            <div class="shahi-card">
                <div class="shahi-card-header">
                    <h2 class="shahi-card-title">
                        <span class="dashicons dashicons-list-view"></span>
                        <?php echo esc_html__('Event Types Breakdown', 'shahi-legalops-suite'); ?>
                    </h2>
                </div>
                <div class="shahi-card-body">
                    <?php if (!empty($event_types)): ?>
                        <div class="shahi-event-types">
                            <?php foreach ($event_types as $event): ?>
                                <div class="shahi-event-type-row">
                                    <div class="shahi-event-type-label"><?php echo esc_html($event['label']); ?></div>
                                    <div class="shahi-event-type-bar">
                                        <div class="shahi-progress-bar">
                                            <div class="shahi-progress-fill" style="width: <?php echo esc_attr($event['percentage']); ?>%;"></div>
                                        </div>
                                    </div>
                                    <div class="shahi-event-type-stats">
                                        <span class="shahi-event-count"><?php echo esc_html(number_format($event['count'])); ?></span>
                                        <span class="shahi-event-percentage"><?php echo esc_html($event['percentage']); ?>%</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="shahi-empty-state">
                            <span class="dashicons dashicons-info"></span>
                            <p><?php echo esc_html__('No event data available for the selected period.', 'shahi-legalops-suite'); ?></p>
                            <p class="shahi-mock-notice">
                                <span class="dashicons dashicons-warning"></span>
                                <?php echo esc_html__('Mock data is displayed in charts above for demonstration purposes.', 'shahi-legalops-suite'); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Events Table -->
        <div class="shahi-analytics-section shahi-full-width">
            <div class="shahi-card">
                <div class="shahi-card-header">
                    <h2 class="shahi-card-title">
                        <span class="dashicons dashicons-list-view"></span>
                        <?php echo esc_html__('Recent Events', 'shahi-legalops-suite'); ?>
                    </h2>
                </div>
                <div class="shahi-card-body">
                    <?php if (!empty($recent_events)): ?>
                        <div class="shahi-table-wrapper">
                            <table class="shahi-table">
                                <thead>
                                    <tr>
                                        <th><?php echo esc_html__('Event Type', 'shahi-legalops-suite'); ?></th>
                                        <th><?php echo esc_html__('User', 'shahi-legalops-suite'); ?></th>
                                        <th><?php echo esc_html__('Browser', 'shahi-legalops-suite'); ?></th>
                                        <th><?php echo esc_html__('IP Address', 'shahi-legalops-suite'); ?></th>
                                        <th><?php echo esc_html__('Time', 'shahi-legalops-suite'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_events as $event): ?>
                                        <tr>
                                            <td>
                                                <span class="shahi-badge shahi-badge-primary">
                                                    <?php echo esc_html($event['type_label']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo esc_html($event['user_display']); ?></td>
                                            <td><?php echo esc_html($event['browser']); ?></td>
                                            <td><code><?php echo esc_html($event['ip_address']); ?></code></td>
                                            <td><?php echo esc_html($event['time_ago']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="shahi-empty-state">
                            <span class="dashicons dashicons-info"></span>
                            <p><?php echo esc_html__('No events recorded for the selected period.', 'shahi-legalops-suite'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

</div>
