<?php
/**
 * Accessibility Scanner - Scan Results Template
 *
 * Displays scan results with statistics, filtering, and bulk actions.
 * Uses global styling system from admin-global.css and components.css.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Modules\AccessibilityScanner
 * @since      1.0.0
 *
 * @var array $scans Scan results data
 * @var array $stats Dashboard statistics
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap shahi-accessibility-scanner">
    
    <!-- Page Header -->
    <div class="shahi-dashboard-header">
        <h1 class="shahi-page-title">
            <span class="shahi-icon-badge">
                <span class="dashicons dashicons-universal-access-alt"></span>
            </span>
            <?php esc_html_e('Accessibility Scanner', 'shahi-legalops-suite'); ?>
        </h1>
        
        <div class="shahi-header-actions">
            <button type="button" class="shahi-btn shahi-btn-gradient" id="shahi-a11y-new-scan">
                <span class="dashicons dashicons-search"></span>
                <?php esc_html_e('New Scan', 'shahi-legalops-suite'); ?>
            </button>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="shahi-stats-container">
        <div class="shahi-stat-card">
            <div class="shahi-stat-icon">
                <span class="dashicons dashicons-chart-line"></span>
            </div>
            <div class="shahi-stat-content">
                <div class="shahi-stat-value"><?php echo esc_html($stats['avg_score'] ?? '0'); ?></div>
                <div class="shahi-stat-label"><?php esc_html_e('Average Score', 'shahi-legalops-suite'); ?></div>
            </div>
        </div>
        
        <div class="shahi-stat-card">
            <div class="shahi-stat-icon">
                <span class="dashicons dashicons-search"></span>
            </div>
            <div class="shahi-stat-content">
                <div class="shahi-stat-value"><?php echo esc_html($stats['total_scans'] ?? '0'); ?></div>
                <div class="shahi-stat-label"><?php esc_html_e('Total Scans', 'shahi-legalops-suite'); ?></div>
            </div>
        </div>
        
        <div class="shahi-stat-card">
            <div class="shahi-stat-icon">
                <span class="dashicons dashicons-warning"></span>
            </div>
            <div class="shahi-stat-content">
                <div class="shahi-stat-value"><?php echo esc_html($stats['total_issues'] ?? '0'); ?></div>
                <div class="shahi-stat-label"><?php esc_html_e('Total Issues', 'shahi-legalops-suite'); ?></div>
            </div>
        </div>
        
        <div class="shahi-stat-card">
            <div class="shahi-stat-icon">
                <span class="dashicons dashicons-dismiss"></span>
            </div>
            <div class="shahi-stat-content">
                <div class="shahi-stat-value shahi-stat-critical"><?php echo esc_html($stats['critical_issues'] ?? '0'); ?></div>
                <div class="shahi-stat-label"><?php esc_html_e('Critical Issues', 'shahi-legalops-suite'); ?></div>
            </div>
        </div>
    </div>
    
    <!-- Scan Results Table -->
    <div class="shahi-content-card">
        <div class="shahi-card-header">
            <h2><?php esc_html_e('Recent Scans', 'shahi-legalops-suite'); ?></h2>
            
            <div class="shahi-card-header-actions">
                <!-- Filter by Status -->
                <select id="shahi-a11y-filter-status" class="shahi-select">
                    <option value=""><?php esc_html_e('All Statuses', 'shahi-legalops-suite'); ?></option>
                    <option value="completed"><?php esc_html_e('Completed', 'shahi-legalops-suite'); ?></option>
                    <option value="running"><?php esc_html_e('Running', 'shahi-legalops-suite'); ?></option>
                    <option value="failed"><?php esc_html_e('Failed', 'shahi-legalops-suite'); ?></option>
                    <option value="pending"><?php esc_html_e('Pending', 'shahi-legalops-suite'); ?></option>
                </select>
                
                <!-- Search -->
                <input type="text" 
                       id="shahi-a11y-search" 
                       class="shahi-input" 
                       placeholder="<?php esc_attr_e('Search URL or Post ID...', 'shahi-legalops-suite'); ?>">
                
                <!-- Bulk Actions -->
                <select id="shahi-a11y-bulk-action" class="shahi-select">
                    <option value=""><?php esc_html_e('Bulk Actions', 'shahi-legalops-suite'); ?></option>
                    <option value="delete"><?php esc_html_e('Delete', 'shahi-legalops-suite'); ?></option>
                    <option value="export"><?php esc_html_e('Export', 'shahi-legalops-suite'); ?></option>
                </select>
                
                <button type="button" class="shahi-btn shahi-btn-secondary" id="shahi-a11y-apply-bulk">
                    <?php esc_html_e('Apply', 'shahi-legalops-suite'); ?>
                </button>
            </div>
        </div>
        
        <div class="shahi-card-body">
            <div class="shahi-table-container">
                <table class="shahi-table shahi-a11y-results-table">
                    <thead>
                        <tr>
                            <th class="shahi-table-checkbox">
                                <input type="checkbox" id="shahi-a11y-select-all">
                            </th>
                            <th class="shahi-table-sortable" data-sort="id">
                                <?php esc_html_e('ID', 'shahi-legalops-suite'); ?>
                                <span class="shahi-sort-icon dashicons dashicons-sort"></span>
                            </th>
                            <th class="shahi-table-sortable" data-sort="url">
                                <?php esc_html_e('URL / Page', 'shahi-legalops-suite'); ?>
                                <span class="shahi-sort-icon dashicons dashicons-sort"></span>
                            </th>
                            <th class="shahi-table-sortable" data-sort="score">
                                <?php esc_html_e('Score', 'shahi-legalops-suite'); ?>
                                <span class="shahi-sort-icon dashicons dashicons-sort"></span>
                            </th>
                            <th><?php esc_html_e('Issues', 'shahi-legalops-suite'); ?></th>
                            <th class="shahi-table-sortable" data-sort="status">
                                <?php esc_html_e('Status', 'shahi-legalops-suite'); ?>
                                <span class="shahi-sort-icon dashicons dashicons-sort"></span>
                            </th>
                            <th class="shahi-table-sortable" data-sort="created_at">
                                <?php esc_html_e('Date', 'shahi-legalops-suite'); ?>
                                <span class="shahi-sort-icon dashicons dashicons-sort"></span>
                            </th>
                            <th><?php esc_html_e('Actions', 'shahi-legalops-suite'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="shahi-a11y-scans-tbody">
                        <?php if (!empty($scans['scans'])) : ?>
                            <?php foreach ($scans['scans'] as $scan) : ?>
                                <tr data-scan-id="<?php echo esc_attr($scan->id); ?>">
                                    <td>
                                        <input type="checkbox" class="shahi-scan-checkbox" value="<?php echo esc_attr($scan->id); ?>">
                                    </td>
                                    <td><?php echo esc_html($scan->id); ?></td>
                                    <td>
                                        <div class="shahi-scan-url">
                                            <?php if ($scan->post_id) : ?>
                                                <a href="<?php echo esc_url(get_edit_post_link($scan->post_id)); ?>" target="_blank">
                                                    <?php echo esc_html(get_the_title($scan->post_id)); ?>
                                                </a>
                                            <?php else : ?>
                                                <a href="<?php echo esc_url($scan->url); ?>" target="_blank">
                                                    <?php echo esc_html($scan->url); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="shahi-scan-score">
                                            <span class="shahi-score-value <?php echo esc_attr($this->get_score_class($scan->score)); ?>">
                                                <?php echo esc_html($scan->score); ?>
                                            </span>
                                            <div class="shahi-score-bar">
                                                <div class="shahi-score-fill" style="width: <?php echo esc_attr($scan->score); ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="shahi-issues-summary">
                                            <?php if ($scan->failed_checks > 0) : ?>
                                                <span class="shahi-issue-badge shahi-issue-failed">
                                                    <?php echo esc_html($scan->failed_checks); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($scan->warning_checks > 0) : ?>
                                                <span class="shahi-issue-badge shahi-issue-warning">
                                                    <?php echo esc_html($scan->warning_checks); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($scan->passed_checks > 0) : ?>
                                                <span class="shahi-issue-badge shahi-issue-passed">
                                                    <?php echo esc_html($scan->passed_checks); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="shahi-status-badge shahi-status-<?php echo esc_attr($scan->status); ?>">
                                            <?php echo esc_html(ucfirst($scan->status)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="shahi-date">
                                            <?php echo esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $scan->created_at)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="shahi-table-actions">
                                            <button type="button" 
                                                    class="shahi-btn-icon shahi-view-scan" 
                                                    data-scan-id="<?php echo esc_attr($scan->id); ?>"
                                                    title="<?php esc_attr_e('View Details', 'shahi-legalops-suite'); ?>">
                                                <span class="dashicons dashicons-visibility"></span>
                                            </button>
                                            <button type="button" 
                                                    class="shahi-btn-icon shahi-export-scan" 
                                                    data-scan-id="<?php echo esc_attr($scan->id); ?>"
                                                    title="<?php esc_attr_e('Export', 'shahi-legalops-suite'); ?>">
                                                <span class="dashicons dashicons-download"></span>
                                            </button>
                                            <button type="button" 
                                                    class="shahi-btn-icon shahi-delete-scan" 
                                                    data-scan-id="<?php echo esc_attr($scan->id); ?>"
                                                    title="<?php esc_attr_e('Delete', 'shahi-legalops-suite'); ?>">
                                                <span class="dashicons dashicons-trash"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="8" class="shahi-table-empty">
                                    <div class="shahi-empty-state">
                                        <span class="dashicons dashicons-search"></span>
                                        <p><?php esc_html_e('No scans found. Run your first scan to get started!', 'shahi-legalops-suite'); ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if (!empty($scans['scans']) && $scans['pages'] > 1) : ?>
                <div class="shahi-pagination">
                    <div class="shahi-pagination-info">
                        <?php
                        printf(
                            esc_html__('Showing page %1$d of %2$d', 'shahi-legalops-suite'),
                            esc_html($scans['current_page']),
                            esc_html($scans['pages'])
                        );
                        ?>
                    </div>
                    <div class="shahi-pagination-buttons">
                        <button type="button" 
                                class="shahi-btn shahi-btn-secondary shahi-pagination-prev" 
                                data-page="<?php echo esc_attr($scans['current_page'] - 1); ?>"
                                <?php disabled($scans['current_page'], 1); ?>>
                            <?php esc_html_e('Previous', 'shahi-legalops-suite'); ?>
                        </button>
                        <button type="button" 
                                class="shahi-btn shahi-btn-secondary shahi-pagination-next" 
                                data-page="<?php echo esc_attr($scans['current_page'] + 1); ?>"
                                <?php disabled($scans['current_page'], $scans['pages']); ?>>
                            <?php esc_html_e('Next', 'shahi-legalops-suite'); ?>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
</div>

<!-- New Scan Modal -->
<div id="shahi-a11y-new-scan-modal" class="shahi-modal" style="display: none;">
    <div class="shahi-modal-overlay"></div>
    <div class="shahi-modal-container">
        <div class="shahi-modal-header">
            <h2><?php esc_html_e('Run New Scan', 'shahi-legalops-suite'); ?></h2>
            <button type="button" class="shahi-modal-close">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="shahi-modal-body">
            <form id="shahi-a11y-scan-form">
                <div class="shahi-form-group">
                    <label for="shahi-scan-type">
                        <?php esc_html_e('Scan Type', 'shahi-legalops-suite'); ?>
                    </label>
                    <select id="shahi-scan-type" class="shahi-input" required>
                        <option value="url"><?php esc_html_e('URL', 'shahi-legalops-suite'); ?></option>
                        <option value="post"><?php esc_html_e('WordPress Post/Page', 'shahi-legalops-suite'); ?></option>
                    </select>
                </div>
                
                <div class="shahi-form-group" id="shahi-url-field">
                    <label for="shahi-scan-url">
                        <?php esc_html_e('URL to Scan', 'shahi-legalops-suite'); ?>
                    </label>
                    <input type="url" 
                           id="shahi-scan-url" 
                           class="shahi-input" 
                           placeholder="https://example.com/page">
                </div>
                
                <div class="shahi-form-group" id="shahi-post-field" style="display: none;">
                    <label for="shahi-scan-post">
                        <?php esc_html_e('Select Post/Page', 'shahi-legalops-suite'); ?>
                    </label>
                    <select id="shahi-scan-post" class="shahi-input">
                        <option value=""><?php esc_html_e('Select...', 'shahi-legalops-suite'); ?></option>
                        <?php
                        $posts = get_posts([
                            'post_type' => ['post', 'page'],
                            'posts_per_page' => 100,
                            'orderby' => 'title',
                            'order' => 'ASC',
                        ]);
                        foreach ($posts as $post) :
                        ?>
                            <option value="<?php echo esc_attr($post->ID); ?>">
                                <?php echo esc_html($post->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
        <div class="shahi-modal-footer">
            <button type="button" class="shahi-btn shahi-btn-secondary shahi-modal-close">
                <?php esc_html_e('Cancel', 'shahi-legalops-suite'); ?>
            </button>
            <button type="button" class="shahi-btn shahi-btn-gradient" id="shahi-run-scan-submit">
                <span class="dashicons dashicons-search"></span>
                <?php esc_html_e('Run Scan', 'shahi-legalops-suite'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Scan Details Modal -->
<div id="shahi-a11y-details-modal" class="shahi-modal" style="display: none;">
    <div class="shahi-modal-overlay"></div>
    <div class="shahi-modal-container shahi-modal-large">
        <div class="shahi-modal-header">
            <h2><?php esc_html_e('Scan Details', 'shahi-legalops-suite'); ?></h2>
            <button type="button" class="shahi-modal-close">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="shahi-modal-body" id="shahi-scan-details-content">
            <!-- Details loaded via AJAX -->
        </div>
    </div>
</div>

<?php
// Helper method for score class
function get_score_class($score) {
    if ($score >= 90) return 'shahi-score-excellent';
    if ($score >= 75) return 'shahi-score-good';
    if ($score >= 50) return 'shahi-score-fair';
    return 'shahi-score-poor';
}
?>
