<?php
/**
 * Module Dashboard Template
 *
 * Premium dashboard for managing modules with advanced UI/UX.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Templates/Admin
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap shahi-module-dashboard">
    
    <!-- Dashboard Header -->
    <div class="shahi-dashboard-header">
        <div class="shahi-header-content">
            <div class="shahi-header-text">
                <h1 class="shahi-page-title">
                    <span class="shahi-icon-badge">
                        <span class="dashicons dashicons-admin-plugins"></span>
                    </span>
                    <?php echo esc_html__('Module Dashboard', 'shahi-legalops-suite'); ?>
                </h1>
                <p class="shahi-page-subtitle">
                    <?php echo esc_html__('Manage and monitor all plugin modules with advanced controls and analytics', 'shahi-legalops-suite'); ?>
                </p>
            </div>
            <div class="shahi-header-actions">
                <button class="shahi-btn shahi-btn-gradient shahi-bulk-enable" data-action="enable">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php echo esc_html__('Enable All', 'shahi-legalops-suite'); ?>
                </button>
                <button class="shahi-btn shahi-btn-outline shahi-bulk-disable" data-action="disable">
                    <span class="dashicons dashicons-dismiss"></span>
                    <?php echo esc_html__('Disable All', 'shahi-legalops-suite'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="shahi-stats-container">
        <div class="shahi-stat-card shahi-stat-total">
            <div class="shahi-stat-icon">
                <span class="dashicons dashicons-screenoptions"></span>
            </div>
            <div class="shahi-stat-content">
                <div class="shahi-stat-value"><?php echo esc_html($stats['total']); ?></div>
                <div class="shahi-stat-label"><?php echo esc_html__('Total Modules', 'shahi-legalops-suite'); ?></div>
            </div>
            <div class="shahi-stat-glow"></div>
        </div>

        <div class="shahi-stat-card shahi-stat-active">
            <div class="shahi-stat-icon">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <div class="shahi-stat-content">
                <div class="shahi-stat-value"><?php echo esc_html($stats['active']); ?></div>
                <div class="shahi-stat-label"><?php echo esc_html__('Active Modules', 'shahi-legalops-suite'); ?></div>
            </div>
            <div class="shahi-stat-progress">
                <div class="shahi-stat-progress-bar" style="width: <?php echo esc_attr($stats['activation_rate']); ?>%"></div>
            </div>
            <div class="shahi-stat-glow"></div>
        </div>

        <div class="shahi-stat-card shahi-stat-inactive">
            <div class="shahi-stat-icon">
                <span class="dashicons dashicons-marker"></span>
            </div>
            <div class="shahi-stat-content">
                <div class="shahi-stat-value"><?php echo esc_html($stats['inactive']); ?></div>
                <div class="shahi-stat-label"><?php echo esc_html__('Inactive Modules', 'shahi-legalops-suite'); ?></div>
            </div>
            <div class="shahi-stat-glow"></div>
        </div>

        <div class="shahi-stat-card shahi-stat-performance">
            <div class="shahi-stat-icon">
                <span class="dashicons dashicons-performance"></span>
            </div>
            <div class="shahi-stat-content">
                <div class="shahi-stat-value"><?php echo esc_html($stats['avg_performance']); ?>%</div>
                <div class="shahi-stat-label"><?php echo esc_html__('Avg Performance', 'shahi-legalops-suite'); ?></div>
            </div>
            <div class="shahi-stat-progress">
                <div class="shahi-stat-progress-bar" style="width: <?php echo esc_attr($stats['avg_performance']); ?>%"></div>
            </div>
            <div class="shahi-stat-glow"></div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="shahi-controls-bar">
        <div class="shahi-search-wrapper">
            <span class="dashicons dashicons-search"></span>
            <input type="text" 
                   id="shahi-module-search" 
                   class="shahi-search-input" 
                   placeholder="<?php echo esc_attr__('Search modules...', 'shahi-legalops-suite'); ?>"
                   autocomplete="off">
            <span class="shahi-search-clear dashicons dashicons-no-alt" style="display: none;"></span>
        </div>
        
        <div class="shahi-filter-group">
            <button class="shahi-filter-btn active" data-filter="all">
                <?php echo esc_html__('All', 'shahi-legalops-suite'); ?>
                <span class="shahi-filter-count"><?php echo esc_html($stats['total']); ?></span>
            </button>
            <button class="shahi-filter-btn" data-filter="active">
                <?php echo esc_html__('Active', 'shahi-legalops-suite'); ?>
                <span class="shahi-filter-count"><?php echo esc_html($stats['active']); ?></span>
            </button>
            <button class="shahi-filter-btn" data-filter="inactive">
                <?php echo esc_html__('Inactive', 'shahi-legalops-suite'); ?>
                <span class="shahi-filter-count"><?php echo esc_html($stats['inactive']); ?></span>
            </button>
        </div>

        <div class="shahi-view-toggle">
            <button class="shahi-view-btn active" data-view="grid" title="<?php echo esc_attr__('Grid View', 'shahi-legalops-suite'); ?>">
                <span class="dashicons dashicons-grid-view"></span>
            </button>
            <button class="shahi-view-btn" data-view="list" title="<?php echo esc_attr__('List View', 'shahi-legalops-suite'); ?>">
                <span class="dashicons dashicons-list-view"></span>
            </button>
        </div>
    </div>

    <!-- Module Cards Grid -->
    <div class="shahi-modules-grid-premium" data-view="grid">
        <?php foreach ($modules as $module): ?>
            <div class="shahi-module-card-premium <?php echo esc_attr($module['status_class']); ?>" 
                 data-module="<?php echo esc_attr($module['slug']); ?>"
                 data-status="<?php echo $module['enabled'] ? 'active' : 'inactive'; ?>"
                 data-category="<?php echo esc_attr($module['category']); ?>"
                 data-priority="<?php echo esc_attr($module['priority']); ?>">
                
                <!-- Card Background Effects -->
                <div class="shahi-card-bg-effect"></div>
                <div class="shahi-card-glow"></div>
                
                <!-- Module Header -->
                <div class="shahi-module-card-header">
                    <div class="shahi-module-icon-wrapper">
                        <span class="dashicons dashicons-admin-plugins"></span>
                        <div class="shahi-icon-pulse"></div>
                    </div>
                    
                    <div class="shahi-module-meta">
                        <span class="shahi-module-category"><?php echo esc_html($module['category']); ?></span>
                        <span class="shahi-module-priority shahi-priority-<?php echo esc_attr($module['priority']); ?>">
                            <?php echo esc_html(ucfirst($module['priority'])); ?>
                        </span>
                    </div>
                </div>

                <!-- Module Content -->
                <div class="shahi-module-card-body">
                    <h3 class="shahi-module-title"><?php echo esc_html($module['name']); ?></h3>
                    <p class="shahi-module-description"><?php echo esc_html($module['description']); ?></p>
                    
                    <!-- Module Stats -->
                    <div class="shahi-module-stats">
                        <div class="shahi-stat-item">
                            <span class="dashicons dashicons-chart-bar"></span>
                            <span class="shahi-stat-value"><?php echo esc_html($module['usage_count']); ?></span>
                            <span class="shahi-stat-text"><?php echo esc_html__('Uses', 'shahi-legalops-suite'); ?></span>
                        </div>
                        <div class="shahi-stat-item">
                            <span class="dashicons dashicons-performance"></span>
                            <span class="shahi-stat-value"><?php echo esc_html($module['performance_score']); ?>%</span>
                            <span class="shahi-stat-text"><?php echo esc_html__('Performance', 'shahi-legalops-suite'); ?></span>
                        </div>
                    </div>

                    <!-- Dependencies -->
                    <?php if (!empty($module['dependencies'])): ?>
                        <div class="shahi-module-dependencies">
                            <span class="shahi-dependencies-label">
                                <span class="dashicons dashicons-networking"></span>
                                <?php echo esc_html__('Requires:', 'shahi-legalops-suite'); ?>
                            </span>
                            <div class="shahi-dependencies-list">
                                <?php foreach ($module['dependencies'] as $dep): ?>
                                    <span class="shahi-dependency-tag"><?php echo esc_html($dep); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Module Footer / Actions -->
                <div class="shahi-module-card-footer">
                    <!-- Toggle Switch -->
                    <label class="shahi-toggle-switch-premium">
                        <input type="checkbox" 
                               class="shahi-module-toggle-input"
                               data-module="<?php echo esc_attr($module['slug']); ?>"
                               <?php checked($module['enabled']); ?>>
                        <span class="shahi-toggle-slider">
                            <span class="shahi-toggle-icon shahi-toggle-icon-on">
                                <span class="dashicons dashicons-yes"></span>
                            </span>
                            <span class="shahi-toggle-icon shahi-toggle-icon-off">
                                <span class="dashicons dashicons-no"></span>
                            </span>
                        </span>
                    </label>
                    
                    <div class="shahi-module-status-badge">
                        <?php if ($module['enabled']): ?>
                            <span class="shahi-status-active">
                                <span class="shahi-status-dot"></span>
                                <?php echo esc_html__('Active', 'shahi-legalops-suite'); ?>
                            </span>
                        <?php else: ?>
                            <span class="shahi-status-inactive">
                                <span class="shahi-status-dot"></span>
                                <?php echo esc_html__('Inactive', 'shahi-legalops-suite'); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Actions -->
                    <div class="shahi-module-actions">
                        <button class="shahi-action-btn" title="<?php echo esc_attr__('Module Info', 'shahi-legalops-suite'); ?>">
                            <span class="dashicons dashicons-info-outline"></span>
                        </button>
                        <?php
                        // Prefer module-provided settings URL when available.
                        $settings_url = isset($module['settings_url']) ? $module['settings_url'] : '';

                        // Backward-compatible special case for Accessibility Scanner.
                        if (empty($settings_url) && ($module['slug'] ?? '') === 'accessibility-scanner') {
                            $settings_url = admin_url('admin.php?page=slos-accessibility-settings');
                        }
                        ?>
                        <?php if (!empty($settings_url)): ?>
                            <a href="<?php echo esc_url($settings_url); ?>" class="shahi-action-btn" title="<?php echo esc_attr__('Module Settings', 'shahi-legalops-suite'); ?>">
                                <span class="dashicons dashicons-admin-generic"></span>
                            </a>
                        <?php else: ?>
                            <button class="shahi-action-btn" title="<?php echo esc_attr__('Module Settings', 'shahi-legalops-suite'); ?>">
                                <span class="dashicons dashicons-admin-generic"></span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Status Indicator Border -->
                <div class="shahi-card-status-border"></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Empty State -->
    <div class="shahi-empty-state" style="display: none;">
        <div class="shahi-empty-icon">
            <span class="dashicons dashicons-search"></span>
        </div>
        <h3><?php echo esc_html__('No modules found', 'shahi-legalops-suite'); ?></h3>
        <p><?php echo esc_html__('Try adjusting your search or filter criteria', 'shahi-legalops-suite'); ?></p>
    </div>

    <!-- Loading Overlay -->
    <div class="shahi-loading-overlay" style="display: none;">
        <div class="shahi-spinner">
            <div class="shahi-spinner-ring"></div>
            <div class="shahi-spinner-ring"></div>
            <div class="shahi-spinner-ring"></div>
        </div>
    </div>

</div>
