<?php
/**
 * Accessibility Dashboard Template
 *
 * Premium dashboard for managing accessibility scans with advanced UI/UX.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Templates/Admin
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get real scan results from database
$scan_results = get_option('slos_last_scan_results', []);
$stats = get_option('slos_scan_statistics', []);

// If no scan results exist yet, prepare empty state
if (empty($scan_results)) {
    $scan_results = [];
}

// Prepare statistics with defaults
$total_pages = isset($stats['total_pages_scanned']) ? $stats['total_pages_scanned'] : 0;
$total_issues = isset($stats['total_issues']) ? $stats['total_issues'] : 0;
$total_critical = isset($stats['total_critical']) ? $stats['total_critical'] : 0;
$average_score = isset($stats['average_score']) ? $stats['average_score'] : 100;
?>

<div class="wrap shahi-module-dashboard">
    
    <!-- Dashboard Header -->
    <div class="shahi-dashboard-header">
        <div class="shahi-header-content">
            <div class="shahi-header-text">
                <h1 class="shahi-page-title">
                    <span class="shahi-icon-badge">
                        <span class="dashicons dashicons-universal-access"></span>
                    </span>
                    <?php echo esc_html__('Accessibility Dashboard', 'shahi-legalops-suite'); ?>
                </h1>
                <p class="shahi-page-subtitle">
                    <?php echo esc_html__('Monitor your website accessibility score, scan history, and compliance status', 'shahi-legalops-suite'); ?>
                </p>
            </div>
            <div class="shahi-header-actions">
                <button class="shahi-btn shahi-btn-gradient" id="slos-run-full-scan">
                    <span class="dashicons dashicons-search"></span>
                    <?php echo esc_html__('Run Full Scan', 'shahi-legalops-suite'); ?>
                </button>
                <button class="shahi-btn shahi-btn-outline" id="slos-generate-report">
                    <span class="dashicons dashicons-media-document"></span>
                    <?php echo esc_html__('Generate Report', 'shahi-legalops-suite'); ?>
                </button>
                <a href="<?php echo admin_url('admin.php?page=slos-accessibility-settings'); ?>" class="shahi-btn shahi-btn-outline">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php echo esc_html__('Configure Settings', 'shahi-legalops-suite'); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="shahi-stats-container">
        <div class="shahi-stat-card shahi-stat-performance">
            <div class="shahi-stat-icon">
                <span class="dashicons dashicons-chart-bar"></span>
            </div>
            <div class="shahi-stat-content">
                <div class="shahi-stat-value"><?php echo esc_html($average_score); ?>/100</div>
                <div class="shahi-stat-label"><?php echo esc_html__('Overall Accessibility Score', 'shahi-legalops-suite'); ?></div>
            </div>
            <div class="shahi-stat-progress">
                <div class="shahi-stat-progress-bar" style="width: <?php echo esc_attr($average_score); ?>%"></div>
            </div>
            <div class="shahi-stat-glow"></div>
        </div>

        <div class="shahi-stat-card shahi-stat-total">
            <div class="shahi-stat-icon">
                <span class="dashicons dashicons-warning"></span>
            </div>
            <div class="shahi-stat-content">
                <div class="shahi-stat-value"><?php echo esc_html($total_issues); ?></div>
                <div class="shahi-stat-label"><?php echo esc_html__('Total Issues Found', 'shahi-legalops-suite'); ?></div>
            </div>
            <div class="shahi-stat-glow"></div>
        </div>

        <div class="shahi-stat-card shahi-stat-inactive">
            <div class="shahi-stat-icon">
                <span class="dashicons dashicons-dismiss"></span>
            </div>
            <div class="shahi-stat-content">
                <div class="shahi-stat-value"><?php echo esc_html($total_critical); ?></div>
                <div class="shahi-stat-label"><?php echo esc_html__('Critical Issues', 'shahi-legalops-suite'); ?></div>
            </div>
            <div class="shahi-stat-glow"></div>
        </div>

        <div class="shahi-stat-card shahi-stat-active">
            <div class="shahi-stat-icon">
                <span class="dashicons dashicons-admin-page"></span>
            </div>
            <div class="shahi-stat-content">
                <div class="shahi-stat-value"><?php echo esc_html($total_pages); ?></div>
                <div class="shahi-stat-label"><?php echo esc_html__('Pages Scanned', 'shahi-legalops-suite'); ?></div>
            </div>
            <div class="shahi-stat-glow"></div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="shahi-controls-bar">
        <div class="shahi-search-wrapper">
            <span class="dashicons dashicons-search"></span>
            <input type="text" 
                   id="shahi-scan-search" 
                   class="shahi-search-input" 
                   placeholder="<?php echo esc_attr__('Search scanned pages...', 'shahi-legalops-suite'); ?>"
                   autocomplete="off">
            <span class="shahi-search-clear dashicons dashicons-no-alt" style="display: none;"></span>
        </div>
        
        <div class="shahi-filter-group">
            <button class="shahi-filter-btn active" data-filter="all">
                <?php echo esc_html__('All', 'shahi-legalops-suite'); ?>
                <span class="shahi-filter-count"><?php echo esc_html($total_pages); ?></span>
            </button>
            <button class="shahi-filter-btn" data-filter="critical">
                <?php echo esc_html__('Critical', 'shahi-legalops-suite'); ?>
                <span class="shahi-filter-count"><?php echo esc_html($total_critical); ?></span>
            </button>
            <button class="shahi-filter-btn" data-filter="warning">
                <?php echo esc_html__('Warnings', 'shahi-legalops-suite'); ?>
                <span class="shahi-filter-count"><?php echo esc_html($total_issues - $total_critical); ?></span>
            </button>
            <button class="shahi-filter-btn" data-filter="passed">
                <?php echo esc_html__('Passed', 'shahi-legalops-suite'); ?>
                <span class="shahi-filter-count"><?php 
                    $passed_count = 0;
                    foreach ($scan_results as $result) {
                        if ($result['status'] === 'passed') {
                            $passed_count++;
                        }
                    }
                    echo esc_html($passed_count);
                ?></span>
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

    <!-- Scan Results Grid -->
    <div class="shahi-modules-grid-premium" data-view="grid">
        <?php foreach ($scan_results as $result): ?>
            <div class="shahi-module-card-premium <?php echo esc_attr('shahi-status-' . $result['status']); ?>" 
                 data-post-id="<?php echo esc_attr($result['post_id']); ?>"
                 data-status="<?php echo esc_attr($result['status']); ?>"
                 data-score="<?php echo esc_attr($result['score']); ?>">
                
                <!-- Card Background Effects -->
                <div class="shahi-card-bg-effect"></div>
                <div class="shahi-card-glow"></div>
                
                <!-- Page Header -->
                <div class="shahi-module-card-header">
                    <div class="shahi-module-icon-wrapper">
                        <span class="dashicons dashicons-admin-page"></span>
                        <div class="shahi-icon-pulse"></div>
                    </div>
                    
                    <div class="shahi-module-meta">
                        <span class="shahi-module-category">GENERAL</span>
                        <?php if ($result['status'] === 'critical'): ?>
                            <span class="shahi-module-priority shahi-priority-high">CRITICAL</span>
                        <?php elseif ($result['status'] === 'warning'): ?>
                            <span class="shahi-module-priority shahi-priority-medium">WARNING</span>
                        <?php else: ?>
                            <span class="shahi-module-priority shahi-priority-low">PASSED</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Page Content -->
                <div class="shahi-module-card-body">
                    <h3 class="shahi-module-title"><?php echo esc_html($result['page']); ?></h3>
                    <p class="shahi-module-description">
                        <?php 
                        if ($result['critical_count'] > 0) {
                            printf(
                                esc_html__('Found %d critical and %d warning issues that need attention', 'shahi-legalops-suite'),
                                $result['critical_count'],
                                $result['issues_count'] - $result['critical_count']
                            );
                        } elseif ($result['issues_count'] > 0) {
                            printf(
                                esc_html__('Found %d accessibility warnings to review', 'shahi-legalops-suite'),
                                $result['issues_count']
                            );
                        } else {
                            echo esc_html__('All accessibility checks passed successfully', 'shahi-legalops-suite');
                        }
                        ?>
                    </p>
                    
                    <!-- Page Stats -->
                    <div class="shahi-module-stats">
                        <div class="shahi-stat-item">
                            <span class="dashicons dashicons-warning"></span>
                            <span class="shahi-stat-value"><?php echo esc_html($result['issues_count']); ?></span>
                            <span class="shahi-stat-text"><?php echo esc_html__('Issues', 'shahi-legalops-suite'); ?></span>
                        </div>
                        <div class="shahi-stat-item">
                            <span class="dashicons dashicons-performance"></span>
                            <span class="shahi-stat-value"><?php echo esc_html($result['score']); ?>%</span>
                            <span class="shahi-stat-text"><?php echo esc_html__('Score', 'shahi-legalops-suite'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- List View - Warning Badge (hidden in grid view) -->
                <div class="shahi-list-warning-badge">
                    <?php if ($result['status'] === 'critical'): ?>
                        <span class="shahi-list-priority critical">CRITICAL</span>
                    <?php elseif ($result['status'] === 'warning'): ?>
                        <span class="shahi-list-priority warning">WARNING</span>
                    <?php else: ?>
                        <span class="shahi-list-priority passed">PASSED</span>
                    <?php endif; ?>
                </div>

                <!-- List View - Issues Count (hidden in grid view) -->
                <div class="shahi-list-issues">
                    <span class="shahi-list-issues-value"><?php echo esc_html($result['issues_count']); ?></span>
                    <span class="shahi-list-issues-label"><?php echo esc_html__('Issues', 'shahi-legalops-suite'); ?></span>
                </div>

                <!-- List View - Score (hidden in grid view) -->
                <div class="shahi-list-score">
                    <span class="shahi-list-score-value"><?php echo esc_html($result['score']); ?>%</span>
                    <span class="shahi-list-score-label"><?php echo esc_html__('Score', 'shahi-legalops-suite'); ?></span>
                </div>

                <!-- Page Footer / Actions -->
                <div class="shahi-module-card-footer">
                    <!-- Toggle Switch for Auto-fix -->
                    <label class="shahi-toggle-switch-premium">
                        <input type="checkbox" 
                               class="shahi-autofix-toggle-input"
                               data-post-id="<?php echo esc_attr($result['post_id']); ?>"
                               <?php checked($result['autofix_enabled']); ?>>
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
                        <?php if ($result['autofix_enabled']): ?>
                            <span class="shahi-status-active">
                                <span class="shahi-status-dot"></span>
                                <?php echo esc_html__('Auto Fix', 'shahi-legalops-suite'); ?>
                            </span>
                        <?php else: ?>
                            <span class="shahi-status-inactive">
                                <span class="shahi-status-dot"></span>
                                <?php echo esc_html__('Manual Fix', 'shahi-legalops-suite'); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Actions -->
                    <div class="shahi-module-actions">
                        <button class="shahi-action-btn" 
                                data-post-id="<?php echo esc_attr($result['post_id']); ?>"
                                title="<?php echo esc_attr__('View Details', 'shahi-legalops-suite'); ?>">
                            <span class="dashicons dashicons-info-outline"></span>
                        </button>
                        <button class="shahi-action-btn" 
                                data-post-id="<?php echo esc_attr($result['post_id']); ?>"
                                title="<?php echo esc_attr__('Fix Issues', 'shahi-legalops-suite'); ?>">
                            <span class="dashicons dashicons-admin-tools"></span>
                        </button>
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
        <h3><?php echo esc_html__('No scan results found', 'shahi-legalops-suite'); ?></h3>
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

<script>
jQuery(document).ready(function($) {
    // Search functionality
    $('#shahi-scan-search').on('input', function() {
        var searchTerm = $(this).val().toLowerCase();
        var $clearBtn = $('.shahi-search-clear');
        
        if (searchTerm.length > 0) {
            $clearBtn.show();
        } else {
            $clearBtn.hide();
        }
        
        var visibleCount = 0;
        $('.shahi-module-card-premium').each(function() {
            var pageName = $(this).find('.shahi-module-title-premium').text().toLowerCase();
            if (pageName.includes(searchTerm)) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });
        
        if (visibleCount === 0) {
            $('.shahi-empty-state').show();
        } else {
            $('.shahi-empty-state').hide();
        }
    });
    
    $('.shahi-search-clear').on('click', function() {
        $('#shahi-scan-search').val('').trigger('input');
    });
    
    // Filter functionality
    $('.shahi-filter-btn').on('click', function() {
        $('.shahi-filter-btn').removeClass('active');
        $(this).addClass('active');
        
        var filter = $(this).data('filter');
        var visibleCount = 0;
        
        $('.shahi-module-card-premium').each(function() {
            if (filter === 'all' || $(this).data('status') === filter) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });
        
        if (visibleCount === 0) {
            $('.shahi-empty-state').show();
        } else {
            $('.shahi-empty-state').hide();
        }
    });
    
    // View toggle
    $('.shahi-view-btn').on('click', function() {
        $('.shahi-view-btn').removeClass('active');
        $(this).addClass('active');
        
        var view = $(this).data('view');
        $('.shahi-modules-grid-premium').attr('data-view', view);
    });
    
    // Auto-fix toggle
    $('.shahi-autofix-toggle-input').on('change', function() {
        var postId = $(this).data('post-id');
        var enabled = $(this).is(':checked');
        var $card = $(this).closest('.shahi-module-card-premium');
        var $statusBadge = $card.find('.shahi-module-status-badge');
        
        // Send AJAX request to save setting
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'slos_toggle_autofix',
                nonce: '<?php echo wp_create_nonce('slos_scanner_nonce'); ?>',
                post_id: postId,
                enabled: enabled
            },
            success: function(response) {
                if (response.success) {
                    // Update status badge text and class
                    if (enabled) {
                        $statusBadge.html('<span class="shahi-status-active"><span class="shahi-status-dot"></span>' + 
                            '<?php echo esc_js(__('Auto Fix', 'shahi-legalops-suite')); ?></span>');
                    } else {
                        $statusBadge.html('<span class="shahi-status-inactive"><span class="shahi-status-dot"></span>' + 
                            '<?php echo esc_js(__('Manual Fix', 'shahi-legalops-suite')); ?></span>');
                    }
                } else {
                    // Revert toggle on error
                    $(this).prop('checked', !enabled);
                    alert('Failed to update auto-fix setting: ' + response.data);
                }
            }.bind(this),
            error: function() {
                // Revert toggle on error
                $(this).prop('checked', !enabled);
                alert('An error occurred while saving the setting');
            }.bind(this)
        });
    });
    
    // Action buttons
    $('.shahi-action-btn').on('click', function() {
        var postId = $(this).data('post-id');
        var icon = $(this).find('.dashicons').attr('class');
        var $card = $(this).closest('.shahi-module-card-premium');
        
        if (icon.includes('info-outline')) {
            // Show issues details popup
            showIssuesPopup(postId, $card);
        } else if (icon.includes('admin-tools')) {
            // Show fix progress popup
            showFixProgressPopup(postId, $card);
        }
    });
    
    // Show issues details popup
    function showIssuesPopup(postId, $card) {
        // Get issue data from card
        var criticalCount = parseInt($card.find('.shahi-list-issues .critical').text()) || 0;
        var warningCount = parseInt($card.find('.shahi-list-issues .warning').text()) || 0;
        var passedCount = parseInt($card.find('.shahi-list-issues .passed').text()) || 0;
        
        var pageName = $card.find('.shahi-module-title-premium').text();
        
        // Show popup INSTANTLY with loading skeleton
        var loadingPopupHtml = `
            <div class="shahi-popup-overlay" id="shahi-issues-popup">
                <div class="shahi-popup-container">
                    <div class="shahi-popup-header">
                        <h2><span class="dashicons dashicons-info-outline"></span> Issues Found on ${pageName}</h2>
                        <button class="shahi-popup-close">&times;</button>
                    </div>
                    <div class="shahi-popup-body">
                        <div style="text-align:center;padding:40px;">
                            <span class="dashicons dashicons-update shahi-spin" style="font-size:48px;color:#00d4ff;"></span>
                            <p style="margin-top:16px;color:#6b7280;">Loading accessibility issues...</p>
                        </div>
                    </div>
                    <div class="shahi-popup-footer">
                        <button class="shahi-btn-secondary shahi-popup-close">Close</button>
                    </div>
                </div>
            </div>`;
        
        $('body').append(loadingPopupHtml);
        $('#shahi-issues-popup').fadeIn(200);
        
        // Close handler for loading state
        $('.shahi-popup-close').on('click', function() {
            $('#shahi-issues-popup').fadeOut(300, function() {
                $(this).remove();
            });
        });
        
        // Fetch real issues from server (async, non-blocking)
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'slos_get_page_issues',
                nonce: '<?php echo wp_create_nonce('slos_scanner_nonce'); ?>',
                post_id: postId
            },
            success: function(response) {
                // Remove loading popup, replace with real content
                $('#shahi-issues-popup').remove();
                
                if (!response.success) {
                    alert('Failed to load issues: ' + response.data);
                    return;
                }
                
                var issues = response.data.issues || [];
                
                var popupHtml = `
                    <div class="shahi-popup-overlay" id="shahi-issues-popup">
                        <div class="shahi-popup-container">
                            <div class="shahi-popup-header">
                                <h2><span class="dashicons dashicons-info-outline"></span> Issues Found on ${pageName}</h2>
                                <button class="shahi-popup-close">&times;</button>
                            </div>
                            <div class="shahi-popup-body">
                                <div class="shahi-issues-summary">
                                    <div class="shahi-summary-item critical">
                                        <span class="shahi-summary-count">${criticalCount}</span>
                                        <span class="shahi-summary-label">Critical</span>
                                    </div>
                                    <div class="shahi-summary-item warning">
                                        <span class="shahi-summary-count">${warningCount}</span>
                                        <span class="shahi-summary-label">Warnings</span>
                                    </div>
                                    <div class="shahi-summary-item passed">
                                        <span class="shahi-summary-count">${passedCount}</span>
                                        <span class="shahi-summary-label">Passed</span>
                                    </div>
                                </div>
                                <div class="shahi-issues-list">`;
                
                if (issues.length === 0) {
                    popupHtml += '<p style="text-align:center;color:#10b981;font-size:16px;">No issues found! 🎉</p>';
                } else {
                    issues.forEach(function(issue, index) {
                        popupHtml += `
                                    <div class="shahi-issue-item" data-severity="${issue.severity}" data-issue-type="${issue.type}">
                                        <div class="shahi-issue-info">
                                            <span class="shahi-issue-badge ${issue.severity}">${issue.severity}</span>
                                            <div class="shahi-issue-details">
                                                <strong>${issue.type}</strong>
                                                <code>${issue.element || 'N/A'}</code>
                                                <span class="shahi-issue-count">${issue.count} occurrence(s)</span>
                                            </div>
                                        </div>
                                        <button class="shahi-issue-fix-btn" data-issue-type="${issue.type}" data-post-id="${postId}">
                                            <span class="dashicons dashicons-admin-tools"></span> Fix
                                        </button>
                                    </div>`;
                    });
                }
                
                popupHtml += `
                                </div>
                            </div>
                            <div class="shahi-popup-footer">
                                <button class="shahi-btn-secondary shahi-popup-close">Close</button>
                                ${issues.length > 0 ? '<button class="shahi-btn-primary" id="shahi-fix-all-issues" data-post-id="' + postId + '"><span class="dashicons dashicons-admin-tools"></span> Fix All Issues</button>' : ''}
                            </div>
                        </div>
                    </div>`;
                
                $('body').append(popupHtml);
                
                // Close popup handlers
                $('.shahi-popup-close').on('click', function() {
                    $('#shahi-issues-popup').fadeOut(300, function() {
                        $(this).remove();
                    });
                });
                
                // Individual fix button
                $('.shahi-issue-fix-btn').on('click', function() {
                    var $btn = $(this);
                    var $item = $btn.closest('.shahi-issue-item');
                    var issueType = $btn.data('issue-type');
                    var fixPostId = $btn.data('post-id');
                    
                    $btn.prop('disabled', true).html('<span class="dashicons dashicons-update shahi-spin"></span> Fixing...');
                    
                    // Real AJAX call to fix issue
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'slos_fix_single_issue',
                            nonce: '<?php echo wp_create_nonce('slos_scanner_nonce'); ?>',
                            post_id: fixPostId,
                            issue_type: issueType
                        },
                        success: function(fixResponse) {
                            if (fixResponse.success) {
                                $item.addClass('shahi-issue-fixed');
                                $btn.html('<span class="dashicons dashicons-yes"></span> Fixed');
                                
                                // Update card counts
                                updateCardCounts(postId, $card);
                            } else {
                                $btn.prop('disabled', false).html('<span class="dashicons dashicons-admin-tools"></span> Fix');
                                alert('Fix failed: ' + fixResponse.data);
                            }
                        },
                        error: function() {
                            $btn.prop('disabled', false).html('<span class="dashicons dashicons-admin-tools"></span> Fix');
                            alert('An error occurred while fixing the issue');
                        }
                    });
                });
                
                // Fix all button
                $('#shahi-fix-all-issues').on('click', function() {
                    var fixAllPostId = $(this).data('post-id');
                    $('#shahi-issues-popup').fadeOut(300, function() {
                        $(this).remove();
                        showFixProgressPopup(fixAllPostId, $card);
                    });
                });
                
                $('#shahi-issues-popup').fadeIn(300);
            },
            error: function() {
                $('.shahi-popup-overlay').remove();
                alert('Failed to load issues');
            }
        });
    }
    
    // Show fix progress popup with animations
    function showFixProgressPopup(postId, $card) {
        // Show loading overlay and initiate fix
        var pageName = $card.find('.shahi-module-title-premium').text();
        var popupHtml = `
            <div class="shahi-popup-overlay" id="shahi-fix-popup">
                <div class="shahi-popup-container shahi-popup-fix">
                    <div class="shahi-popup-header">
                        <h2><span class="dashicons dashicons-admin-tools"></span> Fixing Issues on ${pageName}</h2>
                    </div>
                    <div class="shahi-popup-body">
                        <div class="shahi-fix-progress-container" id="shahi-fix-items-container">
                            <p style="text-align:center;color:#00d4ff;"><span class="dashicons dashicons-update shahi-spin" style="font-size:32px;"></span><br>Initializing fixes...</p>
                        </div>
                        <div class="shahi-overall-progress">
                            <div class="shahi-progress-bar">
                                <div class="shahi-progress-fill" style="width: 0%"></div>
                            </div>
                            <div class="shahi-progress-text">0 / 0 issues fixed</div>
                        </div>
                    </div>
                    <div class="shahi-popup-footer">
                        <button class="shahi-btn-secondary" id="shahi-cancel-fix" disabled>Cancel</button>
                        <button class="shahi-btn-primary" id="shahi-done-fix" style="display:none;">
                            <span class="dashicons dashicons-yes"></span> Done
                        </button>
                    </div>
                </div>
            </div>`;
        
        $('body').append(popupHtml);
        $('#shahi-fix-popup').fadeIn(300);
        
        // Send AJAX request to fix all issues
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'slos_fix_all_issues',
                nonce: '<?php echo wp_create_nonce('slos_scanner_nonce'); ?>',
                post_id: postId
            },
            success: function(response) {
                if (!response.success) {
                    $('#shahi-fix-items-container').html('<p style="text-align:center;color:#ef4444;">Fix failed: ' + response.data + '</p>');
                    $('#shahi-cancel-fix').text('Close').prop('disabled', false);
                    return;
                }
                
                var fixedIssues = response.data.details || [];
                var totalIssues = fixedIssues.length;
                
                // Build progress items
                var itemsHtml = '';
                fixedIssues.forEach(function(issue, index) {
                    itemsHtml += `
                        <div class="shahi-fix-item" data-index="${index}">
                            <div class="shahi-fix-icon">
                                <span class="dashicons dashicons-update shahi-spin"></span>
                            </div>
                            <div class="shahi-fix-text">${issue.type}</div>
                            <div class="shahi-fix-status">
                                <span class="shahi-status-pending">Pending...</span>
                            </div>
                        </div>`;
                });
                
                $('#shahi-fix-items-container').html(itemsHtml);
                $('.shahi-progress-text').text('0 / ' + totalIssues + ' issues fixed');
                
                // Animate fixes
                var currentIndex = 0;
                
                function animateNextFix() {
                    if (currentIndex >= totalIssues) {
                        // All done - update card and show completion
                        $('#shahi-done-fix').show();
                        $('#shahi-cancel-fix').hide();
                        updateCardCounts(postId, $card);
                        return;
                    }
                    
                    var $item = $('.shahi-fix-item[data-index="' + currentIndex + '"]');
                    $item.addClass('shahi-fixing');
                    $item.find('.shahi-status-pending').text('Fixing...');
                    
                    setTimeout(function() {
                        $item.removeClass('shahi-fixing').addClass('shahi-fixed');
                        $item.find('.shahi-fix-icon .dashicons')
                            .removeClass('dashicons-update shahi-spin')
                            .addClass('dashicons-yes');
                        $item.find('.shahi-status-pending')
                            .removeClass('shahi-status-pending')
                            .addClass('shahi-status-done')
                            .text('Fixed!');
                        
                        currentIndex++;
                        var progress = (currentIndex / totalIssues) * 100;
                        $('.shahi-progress-fill').css('width', progress + '%');
                        $('.shahi-progress-text').text(currentIndex + ' / ' + totalIssues + ' issues fixed');
                        
                        setTimeout(animateNextFix, 300);
                    }, 600 + Math.random() * 200);
                }
                
                setTimeout(animateNextFix, 500);
            },
            error: function() {
                $('#shahi-fix-items-container').html('<p style="text-align:center;color:#ef4444;">An error occurred while fixing issues</p>');
                $('#shahi-cancel-fix').text('Close').prop('disabled', false);
            }
        });
        
        // Done button handler
        $(document).on('click', '#shahi-done-fix', function() {
            $('#shahi-fix-popup').fadeOut(300, function() {
                $(this).remove();
                // Refresh the page to show updated results
                location.reload();
            });
        });
        
        // Cancel button handler
        $(document).on('click', '#shahi-cancel-fix', function() {
            $('#shahi-fix-popup').fadeOut(300, function() {
                $(this).remove();
            });
        });
    }
    
    // Update card counts after fixing
    function updateCardCounts(postId, $card) {
        // Fetch updated results from server
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'slos_get_page_issues',
                nonce: '<?php echo wp_create_nonce('slos_scanner_nonce'); ?>',
                post_id: postId
            },
            success: function(response) {
                if (response.success) {
                    var issues = response.data.issues || [];
                    var criticalCount = 0;
                    var warningCount = 0;
                    
                    issues.forEach(function(issue) {
                        if (issue.severity === 'critical') {
                            criticalCount += issue.count;
                        } else {
                            warningCount += issue.count;
                        }
                    });
                    
                    // Update card display
                    $card.find('.shahi-list-issues .critical').text(criticalCount);
                    $card.find('.shahi-list-issues .warning').text(warningCount);
                    
                    // Update score (simple calculation)
                    var score = 100 - (criticalCount * 5) - (warningCount * 2);
                    score = Math.max(0, Math.min(100, score));
                    $card.find('.shahi-list-score-value').text(score + '%');
                    
                    // Update status indicator
                    if (score >= 90) {
                        $card.attr('data-status', 'passed');
                    } else if (score >= 70) {
                        $card.attr('data-status', 'warning');
                    } else {
                        $card.attr('data-status', 'critical');
                    }
                }
            }
        });
    }
    
    // Action buttons (old code removed)
    /*
    $('.shahi-action-btn').on('click', function() {
        var page = $(this).data('page');
        var icon = $(this).find('.dashicons').attr('class');
        
        if (icon.includes('info-outline')) {
            console.log('View details for: ' + page);
            // Add modal or redirect logic
        } else if (icon.includes('admin-tools')) {
            console.log('Fix issues for: ' + page);
            // Add fix logic
        }
    });
    */
    
    // Header action buttons
    $('#slos-run-full-scan').on('click', function() {
        var nonce = '<?php echo wp_create_nonce('slos_scanner_nonce'); ?>';

        // Show popup INSTANTLY with loading state
        var loadingPopupHtml = `
        <div class="shahi-popup-overlay" id="shahi-fullscan-popup">
            <div class="shahi-popup-container shahi-popup-fix">
                <div class="shahi-popup-header">
                    <h2><span class="dashicons dashicons-update shahi-spin"></span> Preparing Full Scan</h2>
                </div>
                <div class="shahi-popup-body">
                    <div style="text-align:center;padding:60px 20px;">
                        <span class="dashicons dashicons-update shahi-spin" style="font-size:64px;color:#00d4ff;"></span>
                        <p style="margin-top:24px;color:#6b7280;font-size:16px;">Loading pages to scan...</p>
                    </div>
                </div>
                <div class="shahi-popup-footer">
                    <button class="shahi-btn-secondary" id="shahi-fullscan-cancel">Cancel</button>
                </div>
            </div>
        </div>`;

        $('body').append(loadingPopupHtml);
        $('#shahi-fullscan-popup').fadeIn(100);

        // Cancel handler for loading state
        var scanCancelled = false;
        $(document).on('click', '#shahi-fullscan-cancel', function() {
            scanCancelled = true;
            $('#shahi-fullscan-popup').fadeOut(200, function() {
                $(this).remove();
            });
        });

        // 1) Fetch all published posts/pages to scan (async, non-blocking)
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'slos_get_posts_to_scan',
                nonce: nonce
            },
            success: function(res) {
                if (scanCancelled) return;

                if (!res || !res.success || !Array.isArray(res.data) || res.data.length === 0) {
                    $('#shahi-fullscan-popup').remove();
                    alert('No published posts/pages found to scan.');
                    return;
                }

                var pages = res.data;
                var total = pages.length;
                var current = 0;

                // Replace loading content with scan UI
                $('#shahi-fullscan-popup .shahi-popup-header h2').html('<span class="dashicons dashicons-update shahi-spin"></span> Running Full Scan');
                $('#shahi-fullscan-popup .shahi-popup-body').html(`
                    <div class="shahi-overall-progress" style="margin-bottom:16px;">
                        <div class="shahi-progress-bar">
                            <div class="shahi-progress-fill" id="shahi-fullscan-progress" style="width:0%"></div>
                        </div>
                        <div class="shahi-progress-text" id="shahi-fullscan-progress-text">0 / ${total} pages scanned</div>
                    </div>
                    <div id="shahi-fullscan-list" class="shahi-issues-list" style="max-height:320px;overflow:auto;">
                    </div>
                `);
                $('#shahi-fullscan-popup .shahi-popup-footer').html(`
                    <button class="shahi-btn-secondary" id="shahi-fullscan-cancel" disabled>Cancel</button>
                    <button class="shahi-btn-primary" id="shahi-fullscan-done" style="display:none;"><span class="dashicons dashicons-yes"></span> Done</button>
                `);

                // Render initial list
                var $list = $('#shahi-fullscan-list');
                pages.forEach(function(p) {
                    $list.append(`
                        <div class="shahi-issue-item" data-post-id="${p.id}">
                            <div class="shahi-issue-info">
                                <span class="shahi-issue-badge warning">pending</span>
                                <div class="shahi-issue-details">
                                    <strong>${p.title || '(Untitled)'}</strong>
                                    <div class="shahi-issue-count">Waiting to scan...</div>
                                </div>
                            </div>
                            <div class="shahi-issue-meta" style="font-size:12px;color:#6b7280;">&nbsp;</div>
                        </div>`);
                });

                function updateProgress() {
                    var pct = total === 0 ? 0 : Math.round((completedCount / total) * 100);
                    $('#shahi-fullscan-progress').css('width', pct + '%');
                    var statusText = completedCount + ' / ' + total + ' pages scanned';
                    if (activeScanCount > 0) {
                        statusText += ' (' + activeScanCount + ' scanning now)';
                    }
                    $('#shahi-fullscan-progress-text').text(statusText);
                }

                function renderResult(p, scanRes) {
                    var $row = $list.find('[data-post-id="' + p.id + '"]');
                    if ($row.length === 0) return;

                    var issuesCount = scanRes.issues_count || 0;
                    var criticalCount = scanRes.critical_count || 0;
                    var badgeClass = 'passed';
                    var badgeLabel = 'passed';
                    if (criticalCount > 0) { badgeClass = 'critical'; badgeLabel = 'critical'; }
                    else if (issuesCount > 0) { badgeClass = 'warning'; badgeLabel = 'warning'; }

                    var issueTypes = scanRes.issue_types || [];

                    $row.find('.shahi-issue-badge')
                        .removeClass('warning critical passed')
                        .addClass(badgeClass)
                        .text(badgeLabel);
                    $row.find('.shahi-issue-count').text(issuesCount + ' issues (' + criticalCount + ' critical)');
                    $row.find('.shahi-issue-meta').text(issueTypes.length ? 'Checks: ' + issueTypes.join(', ') : 'No issues found');
                }

                // 2) Scan with CONCURRENCY (4 parallel scans for speed)
                var CONCURRENCY = 4;
                var activeScanCount = 0;
                var nextIndex = 0;
                var completedCount = 0;

                function scanNext() {
                    // Start new scans if we have capacity and pages remaining
                    while (activeScanCount < CONCURRENCY && nextIndex < total) {
                        if (scanCancelled) return;
                        
                        var pageIndex = nextIndex++;
                        var page = pages[pageIndex];
                        activeScanCount++;
                        
                        scanPage(page);
                    }
                    
                    // Check if all done
                    if (completedCount >= total && activeScanCount === 0) {
                        finalizeScan();
                    }
                }

                function scanPage(page) {
                    var $row = $list.find('[data-post-id="' + page.id + '"]');
                    $row.find('.shahi-issue-count').text('Scanning...');
                    $row.find('.shahi-issue-meta').text('Running checks');
                    $row.find('.shahi-issue-badge').removeClass('critical warning passed').addClass('warning').text('scanning');

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'slos_scan_single_post',
                            nonce: nonce,
                            post_id: page.id
                        },
                        success: function(scanRes) {
                            if (scanCancelled) return;
                            if (scanRes && scanRes.success && scanRes.data) {
                                renderResult(page, scanRes.data);
                            } else {
                                $row.find('.shahi-issue-count').text('Scan failed');
                                $row.find('.shahi-issue-meta').text('Error');
                                $row.find('.shahi-issue-badge').removeClass('critical warning passed').addClass('critical').text('error');
                            }
                        },
                        error: function() {
                            if (scanCancelled) return;
                            $row.find('.shahi-issue-count').text('Scan failed');
                            $row.find('.shahi-issue-meta').text('Network or server error');
                            $row.find('.shahi-issue-badge').removeClass('critical warning passed').addClass('critical').text('error');
                        },
                        complete: function() {
                            activeScanCount--;
                            completedCount++;
                            updateProgress();
                            scanNext(); // Trigger next batch
                        }
                    });
                }

                function finalizeScan() {
                    if (scanCancelled) return;
                    
                    // All scans complete - now consolidate ONCE
                    $list.prepend('<div class="shahi-issue-item"><div class="shahi-issue-info"><span class="shahi-issue-badge warning">consolidating</span><div class="shahi-issue-details"><strong>Finalizing results...</strong></div></div></div>');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'slos_consolidate_scan_results',
                            nonce: nonce
                        },
                        complete: function() {
                            $list.find('.shahi-issue-item:first').remove();
                            $('#shahi-fullscan-done').show();
                            $('#shahi-fullscan-cancel').prop('disabled', false).text('Close');
                            updateProgress();
                        }
                    });
                }

                // Start scanning with concurrency
                updateProgress();
                scanNext();

                // Cancel/Close handler - FIXED
                $(document).off('click', '#shahi-fullscan-cancel').on('click', '#shahi-fullscan-cancel', function() {
                    scanCancelled = true;
                    $('#shahi-fullscan-popup').fadeOut(200, function() {
                        $(this).remove();
                    });
                });

                // Done handler
                $(document).on('click', '#shahi-fullscan-done', function() {
                    $('#shahi-fullscan-popup').fadeOut(200, function() {
                        $(this).remove();
                        location.reload();
                    });
                });

                // Cancel/Close handler
                $(document).on('click', '#shahi-fullscan-cancel', function() {
                    $('#shahi-fullscan-popup').fadeOut(200, function() {
                        $(this).remove();
                    });
                });
            },
            error: function() {
                alert('Failed to initiate full scan.');
            }
        });
    });
    
    $('#slos-generate-report').on('click', function() {
        console.log('Generating accessibility report...');
        // Add report generation logic
    });
});
</script>
