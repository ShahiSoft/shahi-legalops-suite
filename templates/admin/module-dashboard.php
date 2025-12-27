<?php
/**
 * Module Dashboard Template - Modern Design
 *
 * Ultra-modern module management interface with card-based layout,
 * enhanced visualizations, and smooth interactions.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Templates/Admin
 * @since      3.0.1
 * @version    1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap shahi-legalops-suite-admin shahi-modules-v3">
    
    <!-- ═══════════════════════════════════════════════════════════════════════
         TOP BAR
         ═══════════════════════════════════════════════════════════════════════ -->
    <div class="shahi-v3-topbar">
        <div class="shahi-v3-topbar-left">
            <div class="shahi-v3-brand">
                <span class="shahi-v3-brand-icon">🧩</span>
                <div class="shahi-v3-brand-text">
                    <span class="shahi-v3-brand-name">Modules</span>
                    <span class="shahi-v3-brand-tag"><?php echo esc_html($stats['active']); ?>/<?php echo esc_html($stats['total']); ?> Active</span>
                </div>
            </div>
            <div class="shahi-v3-breadcrumb">
                <span class="dashicons dashicons-admin-home"></span>
                <span class="shahi-v3-breadcrumb-text"><?php echo esc_html__('Module Management', 'shahi-legalops-suite'); ?></span>
            </div>
        </div>
        <div class="shahi-v3-topbar-right">
            <button type="button" class="shahi-v3-btn-icon shahi-bulk-enable" data-action="enable" title="<?php echo esc_attr__('Enable All', 'shahi-legalops-suite'); ?>">
                <span class="dashicons dashicons-yes-alt"></span>
            </button>
            <button type="button" class="shahi-v3-btn-icon shahi-bulk-disable" data-action="disable" title="<?php echo esc_attr__('Disable All', 'shahi-legalops-suite'); ?>">
                <span class="dashicons dashicons-dismiss"></span>
            </button>
            <button type="button" class="shahi-v3-btn-icon" data-action="refresh" title="<?php echo esc_attr__('Refresh', 'shahi-legalops-suite'); ?>">
                <span class="dashicons dashicons-update"></span>
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════
         HERO SECTION
         ═══════════════════════════════════════════════════════════════════════ -->
    <div class="shahi-v3-hero">
        <div class="shahi-v3-hero-bg"></div>
        <div class="shahi-v3-hero-content">
            <div class="shahi-v3-hero-main">
                <h1 class="shahi-v3-hero-title">
                    <?php echo esc_html__('Manage Your Modules', 'shahi-legalops-suite'); ?>
                </h1>
                <p class="shahi-v3-hero-subtitle">
                    <?php echo esc_html__('Enable, disable, and configure plugin modules to customize your experience.', 'shahi-legalops-suite'); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════
         STATS ROW
         ═══════════════════════════════════════════════════════════════════════ -->
    <div class="shahi-v3-stats-row">
        <div class="shahi-v3-stat-card">
            <div class="shahi-v3-stat-icon-wrap">
                <div class="shahi-v3-stat-icon">
                    <span class="dashicons dashicons-screenoptions"></span>
                </div>
            </div>
            <div class="shahi-v3-stat-content">
                <h3 class="shahi-v3-stat-label"><?php echo esc_html__('Total Modules', 'shahi-legalops-suite'); ?></h3>
                <div class="shahi-v3-stat-value">
                    <span class="shahi-v3-stat-number"><?php echo esc_html($stats['total']); ?></span>
                </div>
            </div>
        </div>

        <div class="shahi-v3-stat-card">
            <div class="shahi-v3-stat-icon-wrap">
                <div class="shahi-v3-stat-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="shahi-v3-stat-badge shahi-v3-trend-up">
                    <?php echo esc_html($stats['activation_rate']); ?>%
                </div>
            </div>
            <div class="shahi-v3-stat-content">
                <h3 class="shahi-v3-stat-label"><?php echo esc_html__('Active Modules', 'shahi-legalops-suite'); ?></h3>
                <div class="shahi-v3-stat-value">
                    <span class="shahi-v3-stat-number"><?php echo esc_html($stats['active']); ?></span>
                </div>
            </div>
        </div>

        <div class="shahi-v3-stat-card">
            <div class="shahi-v3-stat-icon-wrap">
                <div class="shahi-v3-stat-icon">
                    <span class="dashicons dashicons-marker"></span>
                </div>
            </div>
            <div class="shahi-v3-stat-content">
                <h3 class="shahi-v3-stat-label"><?php echo esc_html__('Inactive Modules', 'shahi-legalops-suite'); ?></h3>
                <div class="shahi-v3-stat-value">
                    <span class="shahi-v3-stat-number"><?php echo esc_html($stats['inactive']); ?></span>
                </div>
            </div>
        </div>

        <div class="shahi-v3-stat-card">
            <div class="shahi-v3-stat-icon-wrap">
                <div class="shahi-v3-stat-icon">
                    <span class="dashicons dashicons-performance"></span>
                </div>
            </div>
            <div class="shahi-v3-stat-content">
                <h3 class="shahi-v3-stat-label"><?php echo esc_html__('Avg Performance', 'shahi-legalops-suite'); ?></h3>
                <div class="shahi-v3-stat-value">
                    <span class="shahi-v3-stat-number"><?php echo esc_html($stats['avg_performance']); ?></span>
                    <span class="shahi-v3-stat-suffix">%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════
         FILTERS & SEARCH
         ═══════════════════════════════════════════════════════════════════════ -->
    <div class="shahi-v3-controls">
        <div class="shahi-v3-search-wrapper">
            <span class="dashicons dashicons-search"></span>
            <input type="text" 
                   id="shahi-module-search" 
                   class="shahi-v3-search-input" 
                   placeholder="<?php echo esc_attr__('Search modules...', 'shahi-legalops-suite'); ?>"
                   autocomplete="off">
            <span class="shahi-search-clear dashicons dashicons-no-alt" style="display: none;"></span>
        </div>
        
        <div class="shahi-v3-filter-group">
            <button class="shahi-v3-filter-btn active" data-filter="all">
                <?php echo esc_html__('All', 'shahi-legalops-suite'); ?>
                <span class="shahi-v3-filter-count"><?php echo esc_html($stats['total']); ?></span>
            </button>
            <button class="shahi-v3-filter-btn" data-filter="active">
                <?php echo esc_html__('Active', 'shahi-legalops-suite'); ?>
                <span class="shahi-v3-filter-count"><?php echo esc_html($stats['active']); ?></span>
            </button>
            <button class="shahi-v3-filter-btn" data-filter="inactive">
                <?php echo esc_html__('Inactive', 'shahi-legalops-suite'); ?>
                <span class="shahi-v3-filter-count"><?php echo esc_html($stats['inactive']); ?></span>
            </button>
        </div>

        <div class="shahi-v3-view-toggle">
            <button class="shahi-v3-view-btn active" data-view="grid" title="<?php echo esc_attr__('Grid View', 'shahi-legalops-suite'); ?>">
                <span class="dashicons dashicons-grid-view"></span>
            </button>
            <button class="shahi-v3-view-btn" data-view="list" title="<?php echo esc_attr__('List View', 'shahi-legalops-suite'); ?>">
                <span class="dashicons dashicons-list-view"></span>
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════
         MODULES GRID
         ═══════════════════════════════════════════════════════════════════════ -->
    <div class="shahi-v3-modules-container">
        <div class="shahi-v3-modules-grid" data-view="grid">
            <?php foreach ($modules as $module): ?>
                <div class="shahi-v3-module-card <?php echo $module['enabled'] ? 'active' : 'inactive'; ?>" 
                     data-module="<?php echo esc_attr($module['slug']); ?>"
                     data-status="<?php echo $module['enabled'] ? 'active' : 'inactive'; ?>"
                     data-category="<?php echo esc_attr($module['category']); ?>">
                    
                    <!-- Status Indicator -->
                    <div class="shahi-v3-module-status-bar"></div>
                    
                    <!-- Card Content -->
                    <div class="shahi-v3-module-content">
                        
                        <!-- Header -->
                        <div class="shahi-v3-module-header">
                            <div class="shahi-v3-module-icon-wrap">
                                <span class="dashicons dashicons-admin-plugins"></span>
                            </div>
                            <div class="shahi-v3-module-badges">
                                <span class="shahi-v3-module-category"><?php echo esc_html($module['category']); ?></span>
                                <?php if ($module['enabled']): ?>
                                    <span class="shahi-v3-module-status-badge active">
                                        <span class="shahi-v3-status-dot"></span>
                                        <?php echo esc_html__('Active', 'shahi-legalops-suite'); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="shahi-v3-module-status-badge inactive">
                                        <span class="shahi-v3-status-dot"></span>
                                        <?php echo esc_html__('Inactive', 'shahi-legalops-suite'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="shahi-v3-module-body">
                            <h3 class="shahi-v3-module-title"><?php echo esc_html($module['name']); ?></h3>
                            <p class="shahi-v3-module-desc"><?php echo esc_html($module['description']); ?></p>
                            
                            <!-- Mini Stats -->
                            <div class="shahi-v3-module-mini-stats">
                                <div class="shahi-v3-mini-stat-item">
                                    <span class="dashicons dashicons-chart-bar"></span>
                                    <span class="shahi-v3-mini-stat-value"><?php echo esc_html($module['usage_count']); ?></span>
                                    <span class="shahi-v3-mini-stat-label"><?php echo esc_html__('uses', 'shahi-legalops-suite'); ?></span>
                                </div>
                                <div class="shahi-v3-mini-stat-divider"></div>
                                <div class="shahi-v3-mini-stat-item">
                                    <span class="dashicons dashicons-performance"></span>
                                    <span class="shahi-v3-mini-stat-value"><?php echo esc_html($module['performance_score']); ?>%</span>
                                    <span class="shahi-v3-mini-stat-label"><?php echo esc_html__('performance', 'shahi-legalops-suite'); ?></span>
                                </div>
                            </div>

                            <!-- Dependencies -->
                            <?php if (!empty($module['dependencies'])): ?>
                                <div class="shahi-v3-module-deps">
                                    <span class="shahi-v3-deps-label">
                                        <span class="dashicons dashicons-networking"></span>
                                        <?php echo esc_html__('Requires:', 'shahi-legalops-suite'); ?>
                                    </span>
                                    <div class="shahi-v3-deps-list">
                                        <?php foreach ($module['dependencies'] as $dep): ?>
                                            <span class="shahi-v3-dep-tag"><?php echo esc_html($dep); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Footer / Actions -->
                        <div class="shahi-v3-module-footer">
                            <!-- Toggle Switch -->
                            <label class="shahi-v3-toggle-switch">
                                <input type="checkbox" 
                                       class="shahi-module-toggle-input"
                                       data-module="<?php echo esc_attr($module['slug']); ?>"
                                       <?php checked($module['enabled']); ?>>
                                <span class="shahi-v3-toggle-slider">
                                    <span class="shahi-v3-toggle-knob"></span>
                                </span>
                                <span class="shahi-v3-toggle-label">
                                    <?php echo $module['enabled'] ? esc_html__('Enabled', 'shahi-legalops-suite') : esc_html__('Disabled', 'shahi-legalops-suite'); ?>
                                </span>
                            </label>
                            
                            <!-- Action Buttons -->
                            <div class="shahi-v3-module-actions">
                                <?php
                                $settings_url = isset($module['settings_url']) ? $module['settings_url'] : '';
                                if (empty($settings_url) && ($module['slug'] ?? '') === 'accessibility-scanner') {
                                    $settings_url = admin_url('admin.php?page=slos-accessibility-settings');
                                }
                                ?>
                                <?php if (!empty($settings_url) && $module['enabled']): ?>
                                    <a href="<?php echo esc_url($settings_url); ?>" class="shahi-v3-action-btn" title="<?php echo esc_attr__('Settings', 'shahi-legalops-suite'); ?>">
                                        <span class="dashicons dashicons-admin-generic"></span>
                                    </a>
                                <?php endif; ?>
                                <button class="shahi-v3-action-btn" title="<?php echo esc_attr__('Info', 'shahi-legalops-suite'); ?>">
                                    <span class="dashicons dashicons-info-outline"></span>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Empty State -->
        <div class="shahi-v3-empty-state" style="display: none;">
            <span class="dashicons dashicons-search"></span>
            <h3><?php echo esc_html__('No modules found', 'shahi-legalops-suite'); ?></h3>
            <p><?php echo esc_html__('Try adjusting your search or filter criteria', 'shahi-legalops-suite'); ?></p>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="shahi-loading-overlay" style="display: none;">
        <div class="shahi-v3-spinner">
            <div class="shahi-v3-spinner-ring"></div>
            <div class="shahi-v3-spinner-ring"></div>
            <div class="shahi-v3-spinner-ring"></div>
        </div>
    </div>

</div>
