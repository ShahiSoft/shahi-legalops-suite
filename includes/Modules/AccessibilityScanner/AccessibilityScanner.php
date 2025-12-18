<?php
/**
 * Accessibility Scanner Module
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Modules\AccessibilityScanner
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalopsSuite\Modules\AccessibilityScanner;

use ShahiLegalopsSuite\Modules\Module;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\ScannerEngine;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\MissingAltTextCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\EmptyAltTextCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\MissingH1Check;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\SkippedHeadingLevelCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\EmptyLinkCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\GenericLinkTextCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\MissingFormLabelCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\RedundantAltTextCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\EmptyHeadingCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\NewWindowLinkCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\PositiveTabIndexCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\ImageMapAltCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\IframeTitleCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\ButtonLabelCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\TableHeaderCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\AltTextQualityCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\DecorativeImageCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\ComplexImageCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\SvgAccessibilityCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\BackgroundImageCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\LogoImageCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\MultipleH1Check;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\HeadingVisualCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\HeadingLengthCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\HeadingUniquenessCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\HeadingNestingCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\FieldsetLegendCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\AutocompleteCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\InputTypeCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\PlaceholderLabelCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\CustomControlCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\OrphanedLabelCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\RequiredAttributeCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\ErrorMessageCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\FormAriaCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\LinkDestinationCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\SkipLinkCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\DownloadLinkCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\ExternalLinkCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\TextColorContrastCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\FocusIndicatorCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\ColorRelianceCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\ComplexContrastCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\KeyboardTrapCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\FocusOrderCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\InteractiveElementCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\ModalAccessibilityCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\CustomWidgetKeyboardCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\AriaRoleCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\AriaAttributeCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\LandmarkRoleCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\RedundantAriaCheck; // Note: RedundantAltTextCheck exists, this is RedundantAriaCheck
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\HiddenContentCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\SemanticHtmlCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\LiveRegionCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\AriaStateCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\InvalidAriaCombinationCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\PageStructureCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\VideoAccessibilityCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\AudioAccessibilityCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\MediaAlternativeCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\TableCaptionCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\ComplexTableCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\LayoutTableCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\EmptyTableCellCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\ViewportCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\TouchTargetCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers\TouchGestureCheck;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Widget\AccessibilityWidget;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Admin\ScannerPage;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Admin\AccessibilityDashboard;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Admin\AccessibilitySettings;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Fixes\AltTextGenerator;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Fixes\AccessibilityFixer;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Reporting\AccessibilityReporter;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Compliance\AccessibilityStatementGenerator;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Accessibility Scanner Module Class
 *
 * @since 1.0.0
 */
class AccessibilityScanner extends Module {
    
    /**
     * Scanner Engine Instance
     * @var ScannerEngine
     */
    private $scanner;

    /**
     * Get module unique key
     *
     * @since 1.0.0
     * @return string Module key
     */
    public function get_key() {
        return 'accessibility-scanner';
    }
    
    /**
     * Get module name
     *
     * @since 1.0.0
     * @return string Module name
     */
    public function get_name() {
        return 'Accessibility Scanner Pro';
    }
    
    /**
     * Get module description
     *
     * @since 1.0.0
     * @return string Module description
     */
    public function get_description() {
        return 'Automated accessibility scanning engine with real-time checks and compliance reporting.';
    }
    
    /**
     * Get module icon
     *
     * @since 1.0.0
     * @return string Icon class
     */
    public function get_icon() {
        return 'dashicons-universal-access';
    }
    
    /**
     * Get module category
     *
     * @since 1.0.0
     * @return string Category
     */
    public function get_category() {
        return 'compliance';
    }
    
    /**
     * Initialize module
     *
     * @since 1.0.0
     * @return void
     */
    public function init() {
        $this->scanner = new ScannerEngine();
        $this->register_checks();
        
        // Initialize Widget
        $widget = new AccessibilityWidget();
        $widget->init();

        // Initialize Fixer
        $fixer = new AccessibilityFixer();

        // Initialize Reporter
        $reporter = new AccessibilityReporter();

        // Initialize Admin Pages
        if (is_admin()) {
            $scanner_page = new ScannerPage();
            $scanner_page->init();
            
            // Dashboard and Settings classes don't need init() - Assets.php handles everything
            
            $settings = new AccessibilitySettings();
            $settings->init();
            
            add_action('admin_menu', [$this, 'register_admin_menus']);
        }
        
        add_action('save_post', [$this, 'run_scan_on_save'], 10, 3);
        add_action('add_meta_boxes', [$this, 'add_scan_meta_box']);

        // AJAX Handlers
        add_action('wp_ajax_slos_get_posts_to_scan', [$this, 'ajax_get_posts_to_scan']);
        add_action('wp_ajax_slos_scan_single_post', [$this, 'ajax_scan_single_post']);
        add_action('wp_ajax_slos_generate_alt_text', [$this, 'ajax_generate_alt_text']);
        add_action('wp_ajax_slos_generate_statement', [$this, 'ajax_generate_statement']);
        add_action('wp_ajax_slos_fix_single_issue', [$this, 'ajax_fix_single_issue']);
        add_action('wp_ajax_slos_fix_all_issues', [$this, 'ajax_fix_all_issues']);
        add_action('wp_ajax_slos_toggle_autofix', [$this, 'ajax_toggle_autofix']);
        add_action('wp_ajax_slos_get_page_issues', [$this, 'ajax_get_page_issues']);
        add_action('wp_ajax_slos_run_full_scan', [$this, 'ajax_run_full_scan']);
        add_action('wp_ajax_slos_consolidate_scan_results', [$this, 'ajax_consolidate_scan_results']);
    }

    /**
     * Check if user has permission to manage accessibility
     * @return bool
     */
    public function user_can_manage_accessibility() {
        // Default to administrator, but allow filtering for custom roles
        $capability = apply_filters('slos_accessibility_capability', 'manage_options');
        return current_user_can($capability);
    }

    /**
     * AJAX: Generate Accessibility Statement
     */
    public function ajax_generate_statement() {
        check_ajax_referer('slos_scanner_nonce', 'nonce');
        
        if (!$this->user_can_manage_accessibility()) {
            wp_send_json_error('Unauthorized');
        }

        $generator = new AccessibilityStatementGenerator();
        $result = $generator->generate($_POST['data'] ?? []);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success([
            'post_id' => $result,
            'edit_link' => get_edit_post_link($result),
            'view_link' => get_permalink($result)
        ]);
    }

    /**
     * AJAX: Generate Alt Text
     */
    public function ajax_generate_alt_text() {
        check_ajax_referer('slos_scanner_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Unauthorized');
        }

        $attachment_id = intval($_POST['attachment_id']);
        $generator = new AltTextGenerator();
        $result = $generator->generate_for_attachment($attachment_id);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success($result);
    }

    /**
     * AJAX: Get all posts to scan
     */
    public function ajax_get_posts_to_scan() {
        check_ajax_referer('slos_scanner_nonce', 'nonce');
        
        if (!$this->user_can_manage_accessibility()) {
            wp_send_json_error('Unauthorized');
        }

        // Use lightweight query - only get IDs and titles, skip get_permalink (slow)
        global $wpdb;
        $results = $wpdb->get_results(
            "SELECT ID, post_title FROM {$wpdb->posts} 
             WHERE post_type IN ('post', 'page') 
             AND post_status = 'publish' 
             ORDER BY post_title ASC",
            ARRAY_A
        );

        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'id' => (int) $row['ID'],
                'title' => $row['post_title']
            ];
        }

        wp_send_json_success($data);
    }

    /**
     * AJAX: Scan single post
     */
    public function ajax_scan_single_post() {
        check_ajax_referer('slos_scanner_nonce', 'nonce');
        
        if (!$this->user_can_manage_accessibility()) {
            wp_send_json_error('Unauthorized');
        }

        $post_id = intval($_POST['post_id']);
        $post = get_post($post_id);

        if (!$post) {
            wp_send_json_error('Post not found');
        }

        $result = $this->run_scan_for_post($post_id, $post);

        // Note: Consolidation removed from here - should only run at end of full scan
        // Individual scans don't need to rebuild entire dashboard data

        wp_send_json_success($result);
    }

    /**
     * AJAX: Consolidate scan results (called after full scan completes)
     */
    public function ajax_consolidate_scan_results() {
        check_ajax_referer('slos_scanner_nonce', 'nonce');

        if (!$this->user_can_manage_accessibility()) {
            wp_send_json_error('Unauthorized');
        }

        $this->consolidate_scan_results();

        wp_send_json_success(['message' => 'Results consolidated']);
    }

    /**
     * AJAX: Run full scan server-side in one request
     */
    public function ajax_run_full_scan() {
        check_ajax_referer('slos_scanner_nonce', 'nonce');

        if (!$this->user_can_manage_accessibility()) {
            wp_send_json_error('Unauthorized');
        }

        $posts = get_posts([
            'post_type' => ['post', 'page'],
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ]);

        $summaries = [];
        foreach ($posts as $post) {
            $summaries[] = $this->run_scan_for_post($post->ID, $post);
        }

        // Consolidate once after all scans
        $this->consolidate_scan_results();

        wp_send_json_success([
            'total_scanned' => count($posts),
            'results' => $summaries,
        ]);
    }

    /**
     * Run a scan for a single post and persist results
     *
     * @param int $post_id
     * @param WP_Post|null $post
     * @return array Summary of scan results for UI
     */
    private function run_scan_for_post($post_id, $post = null) {
        $post_id = intval($post_id);
        if (!$post) {
            $post = get_post($post_id);
        }

        if (!$post) {
            return [
                'post_id' => $post_id,
                'title' => '',
                'edit_link' => '',
                'issues_count' => 0,
                'critical_count' => 0,
                'all_issues' => [],
                'error' => 'Post not found'
            ];
        }

        $results = $this->scanner->scan($post->post_content);

        // Save results to post meta
        update_post_meta($post_id, '_slos_accessibility_scan_results', $results);
        update_post_meta($post_id, '_slos_accessibility_scan_date', current_time('mysql'));

        $issues_count = 0;
        $critical_count = 0;
        $issue_types = []; // Track unique issue types instead of full details

        foreach ($results as $check) {
            $check_issues = isset($check['issues']) ? (array) $check['issues'] : [];
            $issue_count = count($check_issues);
            
            if ($issue_count > 0) {
                $issues_count += $issue_count;
                $issue_types[] = $check['id'];
                
                if (isset($check['severity']) && $check['severity'] === 'critical') {
                    $critical_count += $issue_count;
                }
            }
        }

        return [
            'post_id' => $post_id,
            'title' => $post->post_title,
            'edit_link' => get_edit_post_link($post_id),
            'issues_count' => $issues_count,
            'critical_count' => $critical_count,
            'issue_types' => array_unique($issue_types), // Only unique types
            'scan_date' => current_time('mysql')
        ];
    }

    /**
     * Consolidate scan results from all posts
     * Aggregates post-level scans into a global consolidated view
     * Also used for dashboard and fixing operations
     */
    private function consolidate_scan_results() {
        $posts = get_posts([
            'post_type' => ['post', 'page'],
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids'
        ]);

        $consolidated = [];
        $total_issues = 0;
        $total_critical = 0;
        $pages_scanned = 0;

        foreach ($posts as $post_id) {
            $post = get_post($post_id);
            $scan_results = get_post_meta($post_id, '_slos_accessibility_scan_results', true);
            
            if (empty($scan_results)) {
                continue;
            }

            $pages_scanned++;
            $issues_count = 0;
            $critical_count = 0;
            $page_issues = [];
            $score = 100;

            foreach ($scan_results as $check) {
                $check_issues = count($check['issues'] ?? []);
                $issues_count += $check_issues;

                if ($check_issues > 0 && isset($check['severity']) && $check['severity'] === 'critical') {
                    $critical_count += $check_issues;
                }

                // Build issues list for this page
                foreach ($check['issues'] ?? [] as $issue) {
                    $page_issues[] = [
                        'type' => $check['id'],
                        'checker_id' => $check['id'],
                        'severity' => $check['severity'] ?? 'warning',
                        'description' => $check['description'] ?? '',
                        'message' => $issue['message'],
                        'element' => $issue['element'] ?? '',
                    ];
                }
            }

            // Calculate accessibility score
            if ($issues_count > 0) {
                $score = max(0, 100 - ($critical_count * 10 + ($issues_count - $critical_count) * 3));
            }

            $total_issues += $issues_count;
            $total_critical += $critical_count;

            $consolidated[] = [
                'post_id' => $post_id,
                'page' => $post->post_title,
                'url' => get_permalink($post_id),
                'score' => $score,
                'issues' => $page_issues,
                'issues_count' => $issues_count,
                'critical_count' => $critical_count,
                'status' => $critical_count > 0 ? 'critical' : ($issues_count > 0 ? 'warning' : 'passed'),
                'last_scan' => get_post_meta($post_id, '_slos_accessibility_scan_date', true),
                'autofix_enabled' => (bool) get_post_meta($post_id, '_slos_accessibility_autofix', true),
            ];
        }

        // Sort by score (lowest first - most issues)
        usort($consolidated, function($a, $b) {
            return $a['score'] <=> $b['score'];
        });

        // Save consolidated results
        update_option('slos_last_scan_results', $consolidated);
        update_option('slos_scan_statistics', [
            'total_pages_scanned' => $pages_scanned,
            'total_issues' => $total_issues,
            'total_critical' => $total_critical,
            'average_score' => $pages_scanned > 0 ? round($total_issues > 0 ? (100 - ($total_critical * 10 + ($total_issues - $total_critical) * 3) / $pages_scanned) : 100) : 0,
            'last_consolidated' => current_time('mysql'),
        ]);
    }

    /**
     * Register scanner checks
     */
    private function register_checks() {
        $this->scanner->register_check(new MissingAltTextCheck());
        $this->scanner->register_check(new EmptyAltTextCheck());
        $this->scanner->register_check(new MissingH1Check());
        $this->scanner->register_check(new SkippedHeadingLevelCheck());
        $this->scanner->register_check(new EmptyLinkCheck());
        $this->scanner->register_check(new GenericLinkTextCheck());
        $this->scanner->register_check(new MissingFormLabelCheck());
        $this->scanner->register_check(new RedundantAltTextCheck());
        $this->scanner->register_check(new EmptyHeadingCheck());
        $this->scanner->register_check(new NewWindowLinkCheck());
        $this->scanner->register_check(new PositiveTabIndexCheck());
        $this->scanner->register_check(new ImageMapAltCheck());
        $this->scanner->register_check(new IframeTitleCheck());
        $this->scanner->register_check(new ButtonLabelCheck());
        $this->scanner->register_check(new TableHeaderCheck());
        $this->scanner->register_check(new AltTextQualityCheck());
        $this->scanner->register_check(new DecorativeImageCheck());
        $this->scanner->register_check(new ComplexImageCheck());
        $this->scanner->register_check(new SvgAccessibilityCheck());
        $this->scanner->register_check(new BackgroundImageCheck());
        $this->scanner->register_check(new LogoImageCheck());
        $this->scanner->register_check(new MultipleH1Check());
        $this->scanner->register_check(new HeadingVisualCheck());
        $this->scanner->register_check(new HeadingLengthCheck());
        $this->scanner->register_check(new HeadingUniquenessCheck());
        $this->scanner->register_check(new HeadingNestingCheck());
        $this->scanner->register_check(new FieldsetLegendCheck());
        $this->scanner->register_check(new AutocompleteCheck());
        $this->scanner->register_check(new InputTypeCheck());
        $this->scanner->register_check(new PlaceholderLabelCheck());
        $this->scanner->register_check(new CustomControlCheck());
        $this->scanner->register_check(new OrphanedLabelCheck());
        $this->scanner->register_check(new RequiredAttributeCheck());
        $this->scanner->register_check(new ErrorMessageCheck());
        $this->scanner->register_check(new FormAriaCheck());
        $this->scanner->register_check(new LinkDestinationCheck());
        $this->scanner->register_check(new SkipLinkCheck());
        $this->scanner->register_check(new DownloadLinkCheck());
        $this->scanner->register_check(new ExternalLinkCheck());
        $this->scanner->register_check(new TextColorContrastCheck());
        $this->scanner->register_check(new FocusIndicatorCheck());
        $this->scanner->register_check(new ColorRelianceCheck());
        $this->scanner->register_check(new ComplexContrastCheck());
        $this->scanner->register_check(new KeyboardTrapCheck());
        $this->scanner->register_check(new FocusOrderCheck());
        $this->scanner->register_check(new InteractiveElementCheck());
        $this->scanner->register_check(new ModalAccessibilityCheck());
        $this->scanner->register_check(new CustomWidgetKeyboardCheck());
        $this->scanner->register_check(new AriaRoleCheck());
        $this->scanner->register_check(new AriaAttributeCheck());
        $this->scanner->register_check(new LandmarkRoleCheck());
        // RedundantAltTextCheck is already registered, this is RedundantAriaCheck
        $this->scanner->register_check(new RedundantAriaCheck());
        $this->scanner->register_check(new HiddenContentCheck());
        $this->scanner->register_check(new SemanticHtmlCheck());
        $this->scanner->register_check(new LiveRegionCheck());
        $this->scanner->register_check(new AriaStateCheck());
        $this->scanner->register_check(new InvalidAriaCombinationCheck());
        $this->scanner->register_check(new PageStructureCheck());
        $this->scanner->register_check(new VideoAccessibilityCheck());
        $this->scanner->register_check(new AudioAccessibilityCheck());
        $this->scanner->register_check(new MediaAlternativeCheck());
        $this->scanner->register_check(new TableCaptionCheck());
        $this->scanner->register_check(new ComplexTableCheck());
        $this->scanner->register_check(new LayoutTableCheck());
        $this->scanner->register_check(new EmptyTableCellCheck());
        $this->scanner->register_check(new ViewportCheck());
        $this->scanner->register_check(new TouchTargetCheck());
        $this->scanner->register_check(new TouchGestureCheck());
    }

    /**
     * Run scan when post is saved
     */
    /**
     * Register admin menus
     */
    public function register_admin_menus() {
        add_submenu_page(
            'shahi-legalops-suite',
            __('Accessibility Dashboard', 'shahi-legalops-suite'),
            __('Accessibility Dashboard', 'shahi-legalops-suite'),
            'manage_options',
            'slos-accessibility-dashboard',
            [new AccessibilityDashboard(), 'render']
        );
        
        // Hidden settings page (accessible via URL or Module Card)
        add_submenu_page(
            null,
            __('Accessibility Settings', 'shahi-legalops-suite'),
            __('Accessibility Settings', 'shahi-legalops-suite'),
            'manage_options',
            'slos-accessibility-settings',
            [new AccessibilitySettings(), 'render']
        );
    }

    /**
     * Run scan on post save
     *
     * @since 1.0.0
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     * @param bool    $update  Whether this is an existing post being updated.
     * @return void
     */
    public function run_scan_on_save($post_id, $post, $update) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if ($post->post_type === 'revision') return;

        $content = $post->post_content;
        $results = $this->scanner->scan($content);
        
        update_post_meta($post_id, '_slos_accessibility_scan_results', $results);
        update_post_meta($post_id, '_slos_accessibility_scan_date', current_time('mysql'));
    }

    /**
     * Add meta box to post editor
     */
    public function add_scan_meta_box() {
        add_meta_box(
            'slos_accessibility_scan_results',
            'Accessibility Scan Results',
            [$this, 'render_scan_meta_box'],
            ['post', 'page'],
            'side',
            'high'
        );
    }

    /**
     * Render meta box content
     */
    public function render_scan_meta_box($post) {
        $results = get_post_meta($post->ID, '_slos_accessibility_scan_results', true);
        $last_scan = get_post_meta($post->ID, '_slos_accessibility_scan_date', true);
        
        echo '<div class="slos-accessibility-results">';
        if ($last_scan) {
            echo '<p><strong>Last Scan:</strong> ' . esc_html($last_scan) . '</p>';
        }
        
        if (empty($results)) {
            echo '<p style="color: green;">No accessibility issues found!</p>';
        } else {
            echo '<ul style="list-style: none; padding: 0;">';
            foreach ($results as $check_id => $result) {
                $color = $result['severity'] === 'critical' ? '#d63638' : '#dba617';
                echo '<li style="margin-bottom: 10px; border-left: 4px solid ' . $color . '; padding-left: 10px;">';
                echo '<strong>' . esc_html($result['description']) . '</strong>';
                echo '<ul style="margin-top: 5px; padding-left: 15px;">';
                foreach ($result['issues'] as $issue) {
                    echo '<li>' . esc_html($issue['message']) . '</li>';
                }
                echo '</ul>';
                echo '</li>';
            }
            echo '</ul>';
        }
        echo '</div>';
    }
    
    /**
     * AJAX: Get page issues
     */
    public function ajax_get_page_issues() {
        check_ajax_referer('slos_scanner_nonce', 'nonce');
        
        if (!$this->user_can_manage_accessibility()) {
            wp_send_json_error('Unauthorized');
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        if (empty($post_id)) {
            wp_send_json_error('Post not specified');
        }

        // Get post by ID first
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error('Post not found');
        }
        
        // Get consolidated results
        $results = get_option('slos_last_scan_results', []);
        $page_data = null;
        
        // Find by post_id instead of page name
        foreach ($results as $result) {
            if (isset($result['post_id']) && $result['post_id'] === $post_id) {
                $page_data = $result;
                break;
            }
        }
        
        if (!$page_data || empty($page_data['issues'])) {
            wp_send_json_success(['issues' => []]);
        }
        
        wp_send_json_success([
            'issues' => $page_data['issues'],
            'score' => $page_data['score'] ?? 0,
            'issues_count' => $page_data['issues_count'] ?? 0,
            'status' => $page_data['status'] ?? 'unknown'
        ]);
    }
    
    /**
     * AJAX: Fix single issue
     */
    public function ajax_fix_single_issue() {
        check_ajax_referer('slos_scanner_nonce', 'nonce');
        
        if (!$this->user_can_manage_accessibility()) {
            wp_send_json_error('Unauthorized');
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $issue_type = sanitize_text_field($_POST['issue_type'] ?? '');
        
        if (empty($post_id) || empty($issue_type)) {
            wp_send_json_error('Missing parameters');
        }

        // Get post
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error('Post not found');
        }
        
        // Get the fixer instance
        $fixer = new AccessibilityFixer();
        
        // Apply fix based on issue type
        $result = $fixer->fix_issue($post_id, $issue_type);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        // Update scan results
        $this->update_scan_results_after_fix($post_id, $issue_type);
        
        wp_send_json_success([
            'message' => 'Issue fixed successfully',
            'fixed_count' => $result['fixed_count'] ?? 1
        ]);
    }
    
    /**
     * AJAX: Fix all issues for a page
     */
    public function ajax_fix_all_issues() {
        check_ajax_referer('slos_scanner_nonce', 'nonce');
        
        if (!$this->user_can_manage_accessibility()) {
            wp_send_json_error('Unauthorized');
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        
        if (empty($post_id)) {
            wp_send_json_error('Post not specified');
        }

        // Get post
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error('Post not found');
        }
        
        // Get all issues for this page from consolidated data
        $results = get_option('slos_last_scan_results', []);
        $page_data = null;
        
        foreach ($results as $result) {
            if (isset($result['post_id']) && $result['post_id'] === $post_id) {
                $page_data = $result;
                break;
            }
        }
        
        if (!$page_data || empty($page_data['issues'])) {
            wp_send_json_error('No issues found');
        }
        
        // Get the fixer instance
        $fixer = new AccessibilityFixer();
        
        // Track progress
        $fixed_issues = [];
        $failed_issues = [];
        $fixed_count_total = 0;
        
        foreach ($page_data['issues'] as $issue) {
            $result = $fixer->fix_issue($post_id, $issue['type']);
            
            if (is_wp_error($result)) {
                $failed_issues[] = $issue['type'];
            } else {
                $fixed_count = $result['fixed_count'] ?? 1;
                $fixed_issues[] = [
                    'type' => $issue['type'],
                    'count' => $fixed_count
                ];
                $fixed_count_total += $fixed_count;
            }
        }
        
        // Update scan results after all fixes
        foreach ($fixed_issues as $fixed) {
            $this->update_scan_results_after_fix($post_id, $fixed['type']);
        }
        
        // Recalculate page score
        $this->recalculate_page_score($post_id);
        
        // Reconsolidate results
        $this->consolidate_scan_results();
        
        wp_send_json_success([
            'message' => 'Issues fixed successfully',
            'fixed_count' => count($fixed_issues),
            'failed_count' => count($failed_issues),
            'total_issues_fixed' => $fixed_count_total,
            'details' => $fixed_issues
        ]);
    }
    
    /**
     * AJAX: Toggle autofix for a page
     */
    public function ajax_toggle_autofix() {
        check_ajax_referer('slos_scanner_nonce', 'nonce');
        
        if (!$this->user_can_manage_accessibility()) {
            wp_send_json_error('Unauthorized');
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $enabled = filter_var($_POST['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        
        if (empty($post_id)) {
            wp_send_json_error('Post ID not specified');
        }
        
        // Verify post exists
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error('Post not found');
        }
        
        // Update autofix setting in post meta (more reliable than options array)
        update_post_meta($post_id, '_slos_accessibility_autofix', $enabled);
        
        // Update the consolidated scan results cache
        $results = get_option('slos_last_scan_results', []);
        foreach ($results as &$result) {
            if ($result['post_id'] === $post_id) {
                $result['autofix_enabled'] = $enabled;
                break;
            }
        }
        update_option('slos_last_scan_results', $results);
        
        wp_send_json_success([
            'message' => $enabled ? 'Auto Fix enabled' : 'Auto Fix disabled',
            'enabled' => $enabled
        ]);
    }
    
    /**
     * Update scan results after fixing an issue
     */
    /**
     * Update scan results after fixing an issue
     */
    private function update_scan_results_after_fix($post_id, $issue_type) {
        $results = get_option('slos_last_scan_results', []);
        
        foreach ($results as &$result) {
            if (isset($result['post_id']) && $result['post_id'] === $post_id) {
                // Remove the fixed issue from the issues list
                $result['issues'] = array_filter($result['issues'], function($issue) use ($issue_type) {
                    return $issue['type'] !== $issue_type;
                });
                
                // Recalculate counts
                $result['issues'] = array_values($result['issues']); // Re-index
                $result['issues_count'] = count($result['issues']);
                
                $critical_count = 0;
                foreach ($result['issues'] as $issue) {
                    if ($issue['severity'] === 'critical') {
                        $critical_count++;
                    }
                }
                $result['critical_count'] = $critical_count;
                $result['status'] = $critical_count > 0 ? 'critical' : (count($result['issues']) > 0 ? 'warning' : 'passed');
                
                break;
            }
        }
        
        update_option('slos_last_scan_results', $results);
    }
    
    /**
     * Recalculate page score after fixes
     */
    private function recalculate_page_score($post_id) {
        $results = get_option('slos_last_scan_results', []);
        
        foreach ($results as &$result) {
            if (isset($result['post_id']) && $result['post_id'] === $post_id) {
                // Recalculate score based on remaining issues
                $issues_count = count($result['issues'] ?? []);
                $critical_count = 0;
                
                foreach ($result['issues'] ?? [] as $issue) {
                    if ($issue['severity'] === 'critical') {
                        $critical_count++;
                    }
                }
                
                // Score calculation: 100 - (critical * 10) - (warnings * 3)
                $score = max(0, 100 - ($critical_count * 10 + ($issues_count - $critical_count) * 3));
                $result['score'] = $score;
                $result['issues_count'] = $issues_count;
                $result['critical_count'] = $critical_count;
                
                // Determine status
                if ($score >= 90) {
                    $result['status'] = 'passed';
                } elseif ($score >= 70) {
                    $result['status'] = 'warning';
                } else {
                    $result['status'] = 'critical';
                }
                
                break;
            }
        }
        
        update_option('slos_last_scan_results', $results);
    }
    
    /**
     * Update global accessibility stats
     */
    private function update_global_stats() {
        $results = get_option('slos_last_scan_results', []);
        
        $total_critical = 0;
        $total_warning = 0;
        $total_score = 0;
        $pages_scanned = count($results);
        
        foreach ($results as $result) {
            $total_critical += $result['critical'];
            $total_warning += $result['warning'];
            $total_score += $result['score'];
        }
        
        update_option('slos_accessibility_issues_critical', $total_critical);
        update_option('slos_accessibility_issues_warning', $total_warning);
        update_option('slos_accessibility_issues_total', $total_critical + $total_warning);
        update_option('slos_accessibility_score', $pages_scanned > 0 ? round($total_score / $pages_scanned) : 0);
        update_option('slos_accessibility_pages_scanned', $pages_scanned);
    }
}
