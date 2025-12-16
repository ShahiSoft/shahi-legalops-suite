<?php
/**
 * Scan Results Admin Page
 *
 * Displays accessibility scan results with filtering, sorting, and bulk actions.
 * Provides interface for running new scans and viewing detailed issue reports.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Modules\AccessibilityScanner\Admin
 * @since      1.0.0
 * @version    1.0.0
 * @author     Shahi Legal Ops Team
 * @license    GPL-2.0-or-later
 */

namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * ScanResults Class
 *
 * Manages the scan results admin interface including AJAX handlers,
 * data retrieval, and template rendering.
 *
 * @since 1.0.0
 */
class ScanResults {
    
    /**
     * WordPress database instance
     *
     * @var \wpdb
     */
    private $wpdb;
    
    /**
     * Items per page for pagination
     *
     * @var int
     */
    private $per_page = 20;
    
    /**
     * Constructor
     *
     * Registers admin menu, AJAX handlers, and enqueues assets.
     *
     * @since 1.0.0
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // AJAX handlers
        add_action('wp_ajax_shahi_a11y_run_scan', [$this, 'ajax_run_scan']);
        add_action('wp_ajax_shahi_a11y_get_scan_results', [$this, 'ajax_get_scan_results']);
        add_action('wp_ajax_shahi_a11y_delete_scan', [$this, 'ajax_delete_scan']);
        add_action('wp_ajax_shahi_a11y_get_scan_details', [$this, 'ajax_get_scan_details']);
        add_action('wp_ajax_shahi_a11y_bulk_delete', [$this, 'ajax_bulk_delete']);
        add_action('wp_ajax_shahi_a11y_export_scan', [$this, 'ajax_export_scan']);
    }
    
    /**
     * Add admin menu page
     *
     * Registers submenu under Module Dashboard for accessibility scanner.
     *
     * @since 1.0.0
     * @return void
     */
    public function add_menu_page() {
        add_submenu_page(
            'shahi-module-dashboard',
            __('Accessibility Scanner', 'shahi-legalops-suite'),
            __('Accessibility', 'shahi-legalops-suite'),
            'manage_options',
            'shahi-accessibility-scanner',
            [$this, 'render_page']
        );
    }
    
    /**
     * Render scan results page
     *
     * Loads the template file for displaying scan results.
     *
     * @since 1.0.0
     * @return void
     */
    public function render_page() {
        $scans = $this->get_recent_scans();
        $stats = $this->get_dashboard_stats();
        
        include dirname(dirname(dirname(dirname(__DIR__)))) . '/templates/admin/accessibility-scanner/scan-results.php';
    }
    
    /**
     * Enqueue admin assets
     *
     * Loads CSS and JavaScript files for the scan results page.
     *
     * @since 1.0.0
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueue_assets($hook) {
        // Only load on our page
        if ($hook !== 'shahi-module-dashboard_page_shahi-accessibility-scanner') {
            return;
        }
        
        // Enqueue CSS
        wp_enqueue_style(
            'shahi-accessibility-scanner',
            plugins_url('assets/css/accessibility-scanner.css', dirname(dirname(dirname(__DIR__)))),
            ['shahi-admin-global', 'shahi-components'],
            '1.0.0'
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'shahi-accessibility-scanner',
            plugins_url('assets/js/accessibility-scanner/admin.js', dirname(dirname(dirname(__DIR__)))),
            ['jquery', 'wp-i18n'],
            '1.0.0',
            true
        );
        
        // Localize script
        wp_localize_script('shahi-accessibility-scanner', 'shahiA11y', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('shahi_a11y_nonce'),
            'strings' => [
                'scanRunning' => __('Scan in progress...', 'shahi-legalops-suite'),
                'scanComplete' => __('Scan completed successfully!', 'shahi-legalops-suite'),
                'scanFailed' => __('Scan failed. Please try again.', 'shahi-legalops-suite'),
                'confirmDelete' => __('Are you sure you want to delete this scan?', 'shahi-legalops-suite'),
                'confirmBulkDelete' => __('Are you sure you want to delete selected scans?', 'shahi-legalops-suite'),
                'noScansSelected' => __('Please select scans to delete.', 'shahi-legalops-suite'),
                'exportStarted' => __('Export started...', 'shahi-legalops-suite'),
                'loading' => __('Loading...', 'shahi-legalops-suite'),
            ],
        ]);
    }
    
    /**
     * Get recent scans
     *
     * Retrieves paginated list of accessibility scans with filtering and sorting.
     *
     * @since 1.0.0
     * @param array $args Query arguments (page, per_page, orderby, order, status, search)
     * @return array Scans data with pagination info
     */
    public function get_recent_scans($args = []) {
        $defaults = [
            'page' => 1,
            'per_page' => $this->per_page,
            'orderby' => 'created_at',
            'order' => 'DESC',
            'status' => '',
            'search' => '',
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $offset = ($args['page'] - 1) * $args['per_page'];
        $table = $this->wpdb->prefix . 'slos_a11y_scans';
        
        // Build WHERE clause
        $where = ['1=1'];
        $where_values = [];
        
        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $where_values[] = $args['status'];
        }
        
        if (!empty($args['search'])) {
            $where[] = '(url LIKE %s OR post_id = %d)';
            $where_values[] = '%' . $this->wpdb->esc_like($args['search']) . '%';
            $where_values[] = intval($args['search']);
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Sanitize orderby and order
        $allowed_orderby = ['id', 'created_at', 'score', 'status', 'url'];
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'created_at';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';
        
        // Get total count
        if (!empty($where_values)) {
            $total_query = $this->wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE $where_clause",
                $where_values
            );
        } else {
            $total_query = "SELECT COUNT(*) FROM $table WHERE $where_clause";
        }
        
        $total = (int) $this->wpdb->get_var($total_query);
        
        // Get scans
        if (!empty($where_values)) {
            $query = $this->wpdb->prepare(
                "SELECT * FROM $table WHERE $where_clause ORDER BY $orderby $order LIMIT %d OFFSET %d",
                array_merge($where_values, [$args['per_page'], $offset])
            );
        } else {
            $query = $this->wpdb->prepare(
                "SELECT * FROM $table WHERE $where_clause ORDER BY $orderby $order LIMIT %d OFFSET %d",
                [$args['per_page'], $offset]
            );
        }
        
        $scans = $this->wpdb->get_results($query);
        
        return [
            'scans' => $scans,
            'total' => $total,
            'pages' => ceil($total / $args['per_page']),
            'current_page' => $args['page'],
        ];
    }
    
    /**
     * Get dashboard statistics
     *
     * Retrieves aggregate statistics for the dashboard cards.
     *
     * @since 1.0.0
     * @return array Statistics data
     */
    public function get_dashboard_stats() {
        $scans_table = $this->wpdb->prefix . 'slos_a11y_scans';
        $issues_table = $this->wpdb->prefix . 'slos_a11y_issues';
        
        $total_scans = (int) $this->wpdb->get_var(
            "SELECT COUNT(*) FROM $scans_table"
        );
        
        $completed_scans = (int) $this->wpdb->get_var(
            "SELECT COUNT(*) FROM $scans_table WHERE status = 'completed'"
        );
        
        $avg_score = (float) $this->wpdb->get_var(
            "SELECT AVG(score) FROM $scans_table WHERE status = 'completed'"
        );
        
        $total_issues = (int) $this->wpdb->get_var(
            "SELECT COUNT(*) FROM $issues_table"
        );
        
        $critical_issues = (int) $this->wpdb->get_var(
            "SELECT COUNT(*) FROM $issues_table WHERE severity = 'critical' AND status != 'fixed'"
        );
        
        $last_scan = $this->wpdb->get_row(
            "SELECT * FROM $scans_table WHERE status = 'completed' ORDER BY completed_at DESC LIMIT 1"
        );
        
        return [
            'total_scans' => $total_scans,
            'completed_scans' => $completed_scans,
            'avg_score' => round($avg_score, 1),
            'total_issues' => $total_issues,
            'critical_issues' => $critical_issues,
            'last_scan' => $last_scan,
        ];
    }
    
    /**
     * AJAX: Run new scan
     *
     * Handles AJAX request to initiate a new accessibility scan.
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_run_scan() {
        check_ajax_referer('shahi_a11y_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'shahi-legalops-suite')]);
        }
        
        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : null;
        
        if (empty($url) && empty($post_id)) {
            wp_send_json_error(['message' => __('URL or Post ID required', 'shahi-legalops-suite')]);
        }
        
        try {
            // Get URL from post_id if provided
            if ($post_id) {
                $url = get_permalink($post_id);
            }
            
            // Run scan using Engine
            $engine = new \ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Engine();
            $result = $engine->scan($url, $post_id, 'manual');
            
            wp_send_json_success([
                'message' => __('Scan completed successfully', 'shahi-legalops-suite'),
                'scan_id' => $result['scan_id'],
                'score' => $result['score'],
                'issues' => count($result['issues']),
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => sprintf(
                    __('Scan failed: %s', 'shahi-legalops-suite'),
                    $e->getMessage()
                )
            ]);
        }
    }
    
    /**
     * AJAX: Get scan results
     *
     * Retrieves paginated scan results with filtering.
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_get_scan_results() {
        check_ajax_referer('shahi_a11y_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'shahi-legalops-suite')]);
        }
        
        $args = [
            'page' => isset($_POST['page']) ? intval($_POST['page']) : 1,
            'per_page' => isset($_POST['per_page']) ? intval($_POST['per_page']) : $this->per_page,
            'orderby' => isset($_POST['orderby']) ? sanitize_key($_POST['orderby']) : 'created_at',
            'order' => isset($_POST['order']) ? sanitize_key($_POST['order']) : 'DESC',
            'status' => isset($_POST['status']) ? sanitize_key($_POST['status']) : '',
            'search' => isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '',
        ];
        
        $results = $this->get_recent_scans($args);
        
        wp_send_json_success($results);
    }
    
    /**
     * AJAX: Get scan details
     *
     * Retrieves detailed information about a specific scan including all issues.
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_get_scan_details() {
        check_ajax_referer('shahi_a11y_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'shahi-legalops-suite')]);
        }
        
        $scan_id = isset($_POST['scan_id']) ? intval($_POST['scan_id']) : 0;
        
        if (!$scan_id) {
            wp_send_json_error(['message' => __('Invalid scan ID', 'shahi-legalops-suite')]);
        }
        
        $scans_table = $this->wpdb->prefix . 'slos_a11y_scans';
        $issues_table = $this->wpdb->prefix . 'slos_a11y_issues';
        
        $scan = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM $scans_table WHERE id = %d",
            $scan_id
        ));
        
        if (!$scan) {
            wp_send_json_error(['message' => __('Scan not found', 'shahi-legalops-suite')]);
        }
        
        $issues = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM $issues_table WHERE scan_id = %d ORDER BY severity DESC, wcag_criterion ASC",
            $scan_id
        ));
        
        wp_send_json_success([
            'scan' => $scan,
            'issues' => $issues,
        ]);
    }
    
    /**
     * AJAX: Delete scan
     *
     * Deletes a scan and all associated issues.
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_delete_scan() {
        check_ajax_referer('shahi_a11y_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'shahi-legalops-suite')]);
        }
        
        $scan_id = isset($_POST['scan_id']) ? intval($_POST['scan_id']) : 0;
        
        if (!$scan_id) {
            wp_send_json_error(['message' => __('Invalid scan ID', 'shahi-legalops-suite')]);
        }
        
        $scans_table = $this->wpdb->prefix . 'slos_a11y_scans';
        
        $deleted = $this->wpdb->delete($scans_table, ['id' => $scan_id], ['%d']);
        
        if ($deleted === false) {
            wp_send_json_error(['message' => __('Failed to delete scan', 'shahi-legalops-suite')]);
        }
        
        wp_send_json_success(['message' => __('Scan deleted successfully', 'shahi-legalops-suite')]);
    }
    
    /**
     * AJAX: Bulk delete scans
     *
     * Deletes multiple scans at once.
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_bulk_delete() {
        check_ajax_referer('shahi_a11y_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'shahi-legalops-suite')]);
        }
        
        $scan_ids = isset($_POST['scan_ids']) ? array_map('intval', $_POST['scan_ids']) : [];
        
        if (empty($scan_ids)) {
            wp_send_json_error(['message' => __('No scans selected', 'shahi-legalops-suite')]);
        }
        
        $scans_table = $this->wpdb->prefix . 'slos_a11y_scans';
        $placeholders = implode(',', array_fill(0, count($scan_ids), '%d'));
        
        $deleted = $this->wpdb->query($this->wpdb->prepare(
            "DELETE FROM $scans_table WHERE id IN ($placeholders)",
            $scan_ids
        ));
        
        wp_send_json_success([
            'message' => sprintf(
                _n('%d scan deleted', '%d scans deleted', $deleted, 'shahi-legalops-suite'),
                $deleted
            )
        ]);
    }
    
    /**
     * AJAX: Export scan
     *
     * Exports scan results in specified format (CSV, JSON, PDF).
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_export_scan() {
        check_ajax_referer('shahi_a11y_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'shahi-legalops-suite')]);
        }
        
        $scan_id = isset($_POST['scan_id']) ? intval($_POST['scan_id']) : 0;
        $format = isset($_POST['format']) ? sanitize_key($_POST['format']) : 'csv';
        
        if (!$scan_id) {
            wp_send_json_error(['message' => __('Invalid scan ID', 'shahi-legalops-suite')]);
        }
        
        // Get scan details
        $scans_table = $this->wpdb->prefix . 'slos_a11y_scans';
        $issues_table = $this->wpdb->prefix . 'slos_a11y_issues';
        
        $scan = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM $scans_table WHERE id = %d",
            $scan_id
        ));
        
        $issues = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM $issues_table WHERE scan_id = %d",
            $scan_id
        ));
        
        if (!$scan) {
            wp_send_json_error(['message' => __('Scan not found', 'shahi-legalops-suite')]);
        }
        
        // Generate export based on format
        switch ($format) {
            case 'json':
                $data = json_encode([
                    'scan' => $scan,
                    'issues' => $issues,
                ], JSON_PRETTY_PRINT);
                $filename = 'accessibility-scan-' . $scan_id . '.json';
                $mime_type = 'application/json';
                break;
                
            case 'csv':
            default:
                $csv = "Scan ID,URL,Score,Severity,WCAG,Element,Description\n";
                foreach ($issues as $issue) {
                    $csv .= sprintf(
                        '"%s","%s","%s","%s","%s","%s","%s"' . "\n",
                        $scan_id,
                        $scan->url,
                        $scan->score,
                        $issue->severity,
                        $issue->wcag_criterion,
                        str_replace('"', '""', $issue->element_selector),
                        str_replace('"', '""', $issue->issue_description)
                    );
                }
                $data = $csv;
                $filename = 'accessibility-scan-' . $scan_id . '.csv';
                $mime_type = 'text/csv';
                break;
        }
        
        wp_send_json_success([
            'data' => $data,
            'filename' => $filename,
            'mime_type' => $mime_type,
        ]);
    }
}
