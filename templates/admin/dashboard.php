<?php
/**
 * Dashboard Page Template
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Templates/Admin
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap shahi-legalops-suite-admin shahi-dashboard-page">
    
    <!-- Page Header -->
    <div class="shahi-page-header">
        <div class="shahi-header-content">
            <h1 class="shahi-page-title">
                <span class="dashicons dashicons-dashboard"></span>
                <?php echo esc_html__('Dashboard', 'shahi-legalops-suite'); ?>
            </h1>
            <p class="shahi-page-description">
                <?php echo esc_html__('Welcome to ShahiLegalopsSuite - Your enterprise plugin foundation', 'shahi-legalops-suite'); ?>
            </p>
        </div>
        <div class="shahi-header-actions">
            <button type="button" class="shahi-btn shahi-btn-secondary" data-action="refresh">
                <span class="dashicons dashicons-update"></span>
                <?php echo esc_html__('Refresh Stats', 'shahi-legalops-suite'); ?>
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="shahi-stats-grid">
        <?php foreach ($stats as $stat): ?>
            <div class="shahi-stat-card shahi-stat-<?php echo esc_attr($stat['color']); ?>">
                <div class="shahi-stat-icon">
                    <span class="dashicons <?php echo esc_attr($stat['icon']); ?>"></span>
                </div>
                <div class="shahi-stat-content">
                    <h3 class="shahi-stat-title"><?php echo esc_html($stat['title']); ?></h3>
                    <div class="shahi-stat-value">
                        <?php if (isset($stat['is_time']) && $stat['is_time']): ?>
                            <span class="shahi-stat-number"><?php echo esc_html($stat['value']); ?></span>
                        <?php else: ?>
                            <span class="shahi-stat-number" data-value="<?php echo esc_attr($stat['value']); ?>">
                                <?php echo esc_html($stat['value']); ?>
                            </span>
                            <?php if (isset($stat['suffix'])): ?>
                                <span class="shahi-stat-suffix"><?php echo esc_html($stat['suffix']); ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php if (isset($stat['trend']) && $stat['trend']): ?>
                        <div class="shahi-stat-trend shahi-trend-up">
                            <span class="dashicons dashicons-arrow-up-alt"></span>
                            <?php echo esc_html($stat['trend']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Main Content Grid -->
    <div class="shahi-dashboard-grid">
        
        <!-- Quick Actions -->
        <div class="shahi-dashboard-section shahi-quick-actions-section">
            <div class="shahi-card">
                <div class="shahi-card-header">
                    <h2 class="shahi-card-title">
                        <span class="dashicons dashicons-admin-generic"></span>
                        <?php echo esc_html__('Quick Actions', 'shahi-legalops-suite'); ?>
                    </h2>
                </div>
                <div class="shahi-card-body">
                    <div class="shahi-quick-actions">
                        <?php foreach ($quick_actions as $action): ?>
                            <a href="<?php echo esc_url($action['url']); ?>" 
                               class="shahi-quick-action shahi-action-<?php echo esc_attr($action['color']); ?>">
                                <div class="shahi-action-icon">
                                    <span class="dashicons <?php echo esc_attr($action['icon']); ?>"></span>
                                </div>
                                <div class="shahi-action-content">
                                    <h3 class="shahi-action-title"><?php echo esc_html($action['title']); ?></h3>
                                    <p class="shahi-action-description"><?php echo esc_html($action['description']); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="shahi-dashboard-section shahi-activity-section">
            <div class="shahi-card">
                <div class="shahi-card-header">
                    <h2 class="shahi-card-title">
                        <span class="dashicons dashicons-clock"></span>
                        <?php echo esc_html__('Recent Activity', 'shahi-legalops-suite'); ?>
                    </h2>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=shahi-legalops-suite-analytics')); ?>" 
                       class="shahi-card-action">
                        <?php echo esc_html__('View All', 'shahi-legalops-suite'); ?>
                    </a>
                </div>
                <div class="shahi-card-body">
                    <?php if (!empty($recent_activity)): ?>
                        <div class="shahi-activity-feed">
                            <ul class="shahi-activity-list">
                                <?php foreach ($recent_activity as $activity): ?>
                                    <li class="shahi-activity-item">
                                        <div class="shahi-activity-icon">
                                            <span class="dashicons <?php echo esc_attr($activity['icon']); ?>"></span>
                                        </div>
                                        <div class="shahi-activity-content">
                                            <div class="shahi-activity-title"><?php echo esc_html($activity['title']); ?></div>
                                            <div class="shahi-activity-description"><?php echo esc_html($activity['description']); ?></div>
                                            <div class="shahi-activity-time"><?php echo esc_html($activity['time']); ?></div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="shahi-empty-state">
                            <span class="dashicons dashicons-info"></span>
                            <p><?php echo esc_html__('No recent activity to display.', 'shahi-legalops-suite'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Getting Started -->
        <div class="shahi-dashboard-section shahi-getting-started-section">
            <div class="shahi-card">
                <div class="shahi-card-header">
                    <h2 class="shahi-card-title">
                        <span class="dashicons dashicons-list-view"></span>
                        <?php echo esc_html__('Getting Started', 'shahi-legalops-suite'); ?>
                    </h2>
                </div>
                <div class="shahi-card-body">
                    <div class="shahi-checklist">
                        <?php foreach ($getting_started as $item): ?>
                            <div class="shahi-checklist-item <?php echo $item['completed'] ? 'shahi-completed' : ''; ?>">
                                <div class="shahi-checklist-checkbox">
                                    <?php if ($item['completed']): ?>
                                        <span class="dashicons dashicons-yes-alt"></span>
                                    <?php else: ?>
                                        <span class="dashicons dashicons-minus"></span>
                                    <?php endif; ?>
                                </div>
                                <div class="shahi-checklist-content">
                                    <h4 class="shahi-checklist-title"><?php echo esc_html($item['title']); ?></h4>
                                    <p class="shahi-checklist-description"><?php echo esc_html($item['description']); ?></p>
                                </div>
                                <div class="shahi-checklist-action">
                                    <a href="<?php echo esc_url($item['action_url']); ?>" 
                                       class="shahi-btn shahi-btn-sm shahi-btn-secondary <?php echo esc_attr($item['action_class']); ?>">
                                        <?php echo esc_html($item['action_text']); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support & Resources -->
        <div class="shahi-dashboard-section shahi-resources-section">
            <div class="shahi-card">
                <div class="shahi-card-header">
                    <h2 class="shahi-card-title">
                        <span class="dashicons dashicons-sos"></span>
                        <?php echo esc_html__('Support & Resources', 'shahi-legalops-suite'); ?>
                    </h2>
                </div>
                <div class="shahi-card-body">
                    <div class="shahi-resources-list">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=shahi-legalops-suite-support')); ?>" 
                           class="shahi-resource-link">
                            <span class="dashicons dashicons-book"></span>
                            <span><?php echo esc_html__('View Documentation', 'shahi-legalops-suite'); ?></span>
                        </a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=shahi-legalops-suite-support')); ?>" 
                           class="shahi-resource-link">
                            <span class="dashicons dashicons-video-alt3"></span>
                            <span><?php echo esc_html__('Watch Tutorials', 'shahi-legalops-suite'); ?></span>
                        </a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=shahi-legalops-suite-support')); ?>" 
                           class="shahi-resource-link">
                            <span class="dashicons dashicons-sos"></span>
                            <span><?php echo esc_html__('Get Support', 'shahi-legalops-suite'); ?></span>
                        </a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=shahi-legalops-suite-support#changelog')); ?>" 
                           class="shahi-resource-link">
                            <span class="dashicons dashicons-list-view"></span>
                            <span><?php echo esc_html__('View Changelog', 'shahi-legalops-suite'); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
