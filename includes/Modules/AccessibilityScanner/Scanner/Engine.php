<?php
/**
 * Accessibility Scanner Engine
 *
 * Core scanning engine that orchestrates accessibility checks across HTML content.
 * Manages DOM parsing, checker execution, issue aggregation, and database persistence.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Modules\AccessibilityScanner\Scanner
 * @since      1.0.0
 */

namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner;

use DOMDocument;
use DOMXPath;
use Exception;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Scanner Engine Class
 *
 * Coordinates the entire accessibility scanning process from HTML parsing
 * to issue detection and database storage.
 *
 * @since 1.0.0
 */
class Engine {
    
    /**
     * DOM Document instance
     *
     * @var DOMDocument
     */
    private $dom;
    
    /**
     * XPath query interface
     *
     * @var DOMXPath
     */
    private $xpath;
    
    /**
     * URL being scanned
     *
     * @var string
     */
    private $url;
    
    /**
     * HTML content being scanned
     *
     * @var string
     */
    private $html;
    
    /**
     * Collected issues from all checkers
     *
     * @var array
     */
    private $issues = [];
    
    /**
     * Registered checker instances
     *
     * @var array<AbstractChecker>
     */
    private $checkers = [];
    
    /**
     * WordPress database instance
     *
     * @var \wpdb
     */
    private $wpdb;
    
    /**
     * WCAG 2.2 success criteria mapping
     *
     * @var array
     */
    private $wcag_mapping = [];
    
    /**
     * Constructor
     *
     * Initializes DOM parser, loads checkers, and sets up WCAG mapping.
     *
     * @since 1.0.0
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        
        // Suppress HTML5 parsing warnings
        libxml_use_internal_errors(true);
        
        // Load all registered checkers
        $this->load_checkers();
        
        // Initialize WCAG mapping
        $this->initialize_wcag_mapping();
    }
    
    /**
     * Execute accessibility scan on URL or HTML content
     *
     * Complete scan workflow:
     * 1. Create scan record in database
     * 2. Fetch/validate HTML content
     * 3. Parse HTML into DOM
     * 4. Execute all registered checkers
     * 5. Calculate accessibility score
     * 6. Save issues to database
     * 7. Update scan record with results
     *
     * @since 1.0.0
     *
     * @param string   $url_or_html URL to scan or raw HTML content
     * @param int|null $post_id     Optional WordPress post ID
     * @param string   $scan_type   Scan type: manual, auto, scheduled, bulk
     * @return array Scan results with scan_id, score, and issues
     * @throws Exception If scan fails
     */
    public function scan($url_or_html, $post_id = null, $scan_type = 'manual') {
        try {
            // Step 1: Create scan record
            $scan_id = $this->create_scan_record($url_or_html, $post_id, $scan_type);
            
            // Step 2: Fetch HTML content
            $html = $this->fetch_html($url_or_html);
            
            if (empty($html)) {
                throw new Exception('Failed to fetch HTML content');
            }
            
            // Step 3: Load HTML into DOM
            $this->load_html($html);
            
            // Step 4: Run all registered checkers
            $this->run_checks();
            
            // Step 5: Calculate accessibility score
            $score = $this->calculate_score();
            
            // Step 6: Save issues to database
            $this->save_issues($scan_id);
            
            // Step 7: Update scan record with final results
            $this->update_scan_record($scan_id, $score);
            
            // Return scan results
            return [
                'scan_id' => $scan_id,
                'score' => $score,
                'total_checks' => $this->count_total_checks(),
                'passed_checks' => $this->count_passed_checks(),
                'failed_checks' => count($this->issues),
                'issues' => $this->issues,
            ];
            
        } catch (Exception $e) {
            error_log('Accessibility Scan Error: ' . $e->getMessage());
            
            if (isset($scan_id)) {
                $this->mark_scan_failed($scan_id, $e->getMessage());
            }
            
            throw $e;
        }
    }
    
    /**
     * Register a checker instance
     *
     * @since 1.0.0
     *
     * @param AbstractChecker $checker Checker instance to register
     * @return void
     */
    public function register_checker(AbstractChecker $checker) {
        $this->checkers[] = $checker;
    }
    
    /**
     * Load all registered checkers from CheckerRegistry
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function load_checkers() {
        $registry = CheckerRegistry::get_instance();
        $this->checkers = $registry->get_all_checkers();
    }
    
    /**
     * Load HTML content into DOM parser
     *
     * Uses DOMDocument with HTML5-compatible flags.
     * Suppresses parser warnings for malformed HTML.
     *
     * @since 1.0.0
     *
     * @param string $html HTML content to parse
     * @return void
     */
    private function load_html($html) {
        // Clear previous DOM
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        
        // Load HTML with flags to handle HTML5
        $this->dom->loadHTML(
            $html,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING
        );
        
        // Initialize XPath query interface
        $this->xpath = new DOMXPath($this->dom);
        
        // Store HTML for reference
        $this->html = $html;
    }
    
    /**
     * Execute all registered checkers
     *
     * Runs each checker against the DOM, aggregates issues,
     * and clears checker state for next scan.
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function run_checks() {
        $this->issues = [];
        
        foreach ($this->checkers as $checker) {
            // Clear previous checker issues
            $checker->clear_issues();
            
            // Run checker against current DOM
            $checker->check($this->dom, $this->xpath);
            
            // Aggregate issues
            $checker_issues = $checker->get_issues();
            
            // Enhance issues with WCAG metadata
            foreach ($checker_issues as &$issue) {
                $issue = $this->enhance_issue_metadata($issue);
            }
            
            $this->issues = array_merge($this->issues, $checker_issues);
        }
    }
    
    /**
     * Fetch HTML content from URL or validate raw HTML
     *
     * @since 1.0.0
     *
     * @param string $url_or_html URL or HTML content
     * @return string HTML content
     */
    private function fetch_html($url_or_html) {
        // Check if input is a URL
        if (filter_var($url_or_html, FILTER_VALIDATE_URL)) {
            $this->url = $url_or_html;
            
            // Fetch HTML from URL
            $response = wp_remote_get($url_or_html, [
                'timeout' => 30,
                'sslverify' => false,
            ]);
            
            if (is_wp_error($response)) {
                throw new Exception('Failed to fetch URL: ' . $response->get_error_message());
            }
            
            return wp_remote_retrieve_body($response);
        } else {
            // Input is raw HTML
            $this->url = home_url();
            return $url_or_html;
        }
    }
    
    /**
     * Create scan record in database
     *
     * @since 1.0.0
     *
     * @param string   $url_or_html URL or HTML being scanned
     * @param int|null $post_id     WordPress post ID
     * @param string   $scan_type   Scan type
     * @return int Scan ID
     */
    private function create_scan_record($url_or_html, $post_id, $scan_type) {
        $url = filter_var($url_or_html, FILTER_VALIDATE_URL) 
            ? $url_or_html 
            : get_permalink($post_id);
        
        $this->wpdb->insert(
            $this->wpdb->prefix . 'slos_a11y_scans',
            [
                'post_id' => $post_id,
                'url' => $url,
                'scan_type' => $scan_type,
                'status' => 'running',
                'wcag_level' => 'AA',
                'started_at' => current_time('mysql'),
                'created_by' => get_current_user_id(),
            ],
            ['%d', '%s', '%s', '%s', '%s', '%s', '%d']
        );
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Update scan record with final results
     *
     * @since 1.0.0
     *
     * @param int $scan_id Scan ID
     * @param int $score   Accessibility score
     * @return void
     */
    private function update_scan_record($scan_id, $score) {
        $this->wpdb->update(
            $this->wpdb->prefix . 'slos_a11y_scans',
            [
                'status' => 'completed',
                'total_checks' => $this->count_total_checks(),
                'passed_checks' => $this->count_passed_checks(),
                'failed_checks' => count($this->issues),
                'score' => $score,
                'completed_at' => current_time('mysql'),
            ],
            ['id' => $scan_id],
            ['%s', '%d', '%d', '%d', '%d', '%s'],
            ['%d']
        );
    }
    
    /**
     * Mark scan as failed
     *
     * @since 1.0.0
     *
     * @param int    $scan_id       Scan ID
     * @param string $error_message Error message
     * @return void
     */
    private function mark_scan_failed($scan_id, $error_message) {
        $this->wpdb->update(
            $this->wpdb->prefix . 'slos_a11y_scans',
            [
                'status' => 'failed',
                'completed_at' => current_time('mysql'),
            ],
            ['id' => $scan_id],
            ['%s', '%s'],
            ['%d']
        );
    }
    
    /**
     * Save detected issues to database
     *
     * @since 1.0.0
     *
     * @param int $scan_id Scan ID
     * @return void
     */
    private function save_issues($scan_id) {
        foreach ($this->issues as $issue) {
            $this->wpdb->insert(
                $this->wpdb->prefix . 'slos_a11y_issues',
                [
                    'scan_id' => $scan_id,
                    'check_type' => $issue['check_type'],
                    'check_name' => $issue['check_name'],
                    'severity' => $issue['severity'],
                    'wcag_criterion' => $issue['wcag_criterion'],
                    'wcag_level' => $issue['wcag_level'],
                    'element_selector' => $issue['element_selector'] ?? null,
                    'element_html' => $issue['element_html'] ?? null,
                    'line_number' => $issue['line_number'] ?? null,
                    'issue_description' => $issue['issue_description'],
                    'recommendation' => $issue['recommendation'],
                    'status' => 'new',
                    'priority' => $this->determine_priority($issue['severity']),
                ],
                ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s']
            );
        }
    }
    
    /**
     * Calculate overall accessibility score
     *
     * Score calculation:
     * - Base score: 100
     * - Deduct points based on severity:
     *   - Critical: -10 points each
     *   - Serious: -5 points each
     *   - Moderate: -2 points each
     *   - Minor: -1 point each
     * - Minimum score: 0
     *
     * @since 1.0.0
     *
     * @return int Accessibility score (0-100)
     */
    private function calculate_score() {
        $base_score = 100;
        $deductions = 0;
        
        foreach ($this->issues as $issue) {
            switch ($issue['severity']) {
                case 'critical':
                    $deductions += 10;
                    break;
                case 'serious':
                    $deductions += 5;
                    break;
                case 'moderate':
                    $deductions += 2;
                    break;
                case 'minor':
                    $deductions += 1;
                    break;
            }
        }
        
        $score = max(0, $base_score - $deductions);
        
        return $score;
    }
    
    /**
     * Determine priority based on severity
     *
     * @since 1.0.0
     *
     * @param string $severity Issue severity
     * @return string Priority (P0-P4)
     */
    private function determine_priority($severity) {
        $priority_map = [
            'critical' => 'P0',
            'serious' => 'P1',
            'moderate' => 'P2',
            'minor' => 'P3',
        ];
        
        return $priority_map[$severity] ?? 'P2';
    }
    
    /**
     * Determine severity based on WCAG level and issue count
     *
     * @since 1.0.0
     *
     * @param string $wcag_level   WCAG conformance level (A, AA, AAA)
     * @param int    $element_count Number of elements with this issue
     * @return string Severity level
     */
    public function determine_severity($wcag_level, $element_count = 1) {
        // Level A violations are always critical
        if ($wcag_level === 'A') {
            return 'critical';
        }
        
        // Level AA violations with many instances are serious
        if ($wcag_level === 'AA' && $element_count > 5) {
            return 'serious';
        }
        
        // Level AA violations with few instances are moderate
        if ($wcag_level === 'AA') {
            return 'moderate';
        }
        
        // Level AAA violations are minor
        return 'minor';
    }
    
    /**
     * Enhance issue with WCAG metadata
     *
     * @since 1.0.0
     *
     * @param array $issue Issue data
     * @return array Enhanced issue data
     */
    private function enhance_issue_metadata($issue) {
        $criterion = $issue['wcag_criterion'] ?? null;
        
        if ($criterion && isset($this->wcag_mapping[$criterion])) {
            $wcag_data = $this->wcag_mapping[$criterion];
            
            $issue['wcag_level'] = $wcag_data['level'];
            $issue['wcag_name'] = $wcag_data['name'];
            $issue['wcag_description'] = $wcag_data['description'] ?? '';
        }
        
        return $issue;
    }
    
    /**
     * Count total checks performed
     *
     * @since 1.0.0
     *
     * @return int Total number of checks
     */
    private function count_total_checks() {
        $total = 0;
        foreach ($this->checkers as $checker) {
            // Each checker may perform multiple checks
            // For now, count as 1 per checker type
            $total++;
        }
        return $total;
    }
    
    /**
     * Count passed checks (no issues found)
     *
     * @since 1.0.0
     *
     * @return int Number of passed checks
     */
    private function count_passed_checks() {
        return $this->count_total_checks() - count($this->issues);
    }
    
    /**
     * Initialize WCAG 2.2 success criteria mapping
     *
     * Maps all WCAG 2.2 success criteria to their levels and names.
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function initialize_wcag_mapping() {
        $this->wcag_mapping = [
            // Principle 1: Perceivable
            '1.1.1' => [
                'level' => 'A',
                'name' => 'Non-text Content',
                'description' => 'All non-text content has a text alternative',
            ],
            '1.2.1' => [
                'level' => 'A',
                'name' => 'Audio-only and Video-only (Prerecorded)',
                'description' => 'Alternatives for prerecorded audio-only and video-only media',
            ],
            '1.2.2' => [
                'level' => 'A',
                'name' => 'Captions (Prerecorded)',
                'description' => 'Captions for prerecorded audio in synchronized media',
            ],
            '1.2.3' => [
                'level' => 'A',
                'name' => 'Audio Description or Media Alternative (Prerecorded)',
                'description' => 'Audio description or media alternative for video',
            ],
            '1.2.4' => [
                'level' => 'AA',
                'name' => 'Captions (Live)',
                'description' => 'Captions for live audio in synchronized media',
            ],
            '1.2.5' => [
                'level' => 'AA',
                'name' => 'Audio Description (Prerecorded)',
                'description' => 'Audio description for prerecorded video',
            ],
            '1.3.1' => [
                'level' => 'A',
                'name' => 'Info and Relationships',
                'description' => 'Information and relationships conveyed through presentation can be programmatically determined',
            ],
            '1.3.2' => [
                'level' => 'A',
                'name' => 'Meaningful Sequence',
                'description' => 'Correct reading sequence can be programmatically determined',
            ],
            '1.3.3' => [
                'level' => 'A',
                'name' => 'Sensory Characteristics',
                'description' => 'Instructions don\'t rely solely on sensory characteristics',
            ],
            '1.3.4' => [
                'level' => 'AA',
                'name' => 'Orientation',
                'description' => 'Content does not restrict its view to a single display orientation',
            ],
            '1.3.5' => [
                'level' => 'AA',
                'name' => 'Identify Input Purpose',
                'description' => 'Purpose of input fields can be programmatically determined',
            ],
            '1.4.1' => [
                'level' => 'A',
                'name' => 'Use of Color',
                'description' => 'Color is not the only visual means of conveying information',
            ],
            '1.4.2' => [
                'level' => 'A',
                'name' => 'Audio Control',
                'description' => 'Mechanism to pause or stop audio that plays automatically',
            ],
            '1.4.3' => [
                'level' => 'AA',
                'name' => 'Contrast (Minimum)',
                'description' => 'Minimum contrast ratio of 4.5:1 for text',
            ],
            '1.4.4' => [
                'level' => 'AA',
                'name' => 'Resize Text',
                'description' => 'Text can be resized up to 200% without loss of content',
            ],
            '1.4.5' => [
                'level' => 'AA',
                'name' => 'Images of Text',
                'description' => 'Use text rather than images of text when possible',
            ],
            '1.4.10' => [
                'level' => 'AA',
                'name' => 'Reflow',
                'description' => 'Content reflows without horizontal scrolling at 320px width',
            ],
            '1.4.11' => [
                'level' => 'AA',
                'name' => 'Non-text Contrast',
                'description' => 'Minimum contrast ratio of 3:1 for UI components and graphics',
            ],
            '1.4.12' => [
                'level' => 'AA',
                'name' => 'Text Spacing',
                'description' => 'No loss of content when text spacing is adjusted',
            ],
            '1.4.13' => [
                'level' => 'AA',
                'name' => 'Content on Hover or Focus',
                'description' => 'Additional content on hover/focus is dismissible, hoverable, and persistent',
            ],
            
            // Principle 2: Operable
            '2.1.1' => [
                'level' => 'A',
                'name' => 'Keyboard',
                'description' => 'All functionality available from keyboard',
            ],
            '2.1.2' => [
                'level' => 'A',
                'name' => 'No Keyboard Trap',
                'description' => 'Keyboard focus can be moved away from content',
            ],
            '2.1.4' => [
                'level' => 'A',
                'name' => 'Character Key Shortcuts',
                'description' => 'Character key shortcuts can be turned off or remapped',
            ],
            '2.2.1' => [
                'level' => 'A',
                'name' => 'Timing Adjustable',
                'description' => 'Time limits can be turned off, adjusted, or extended',
            ],
            '2.2.2' => [
                'level' => 'A',
                'name' => 'Pause, Stop, Hide',
                'description' => 'Moving, blinking, or auto-updating content can be paused',
            ],
            '2.3.1' => [
                'level' => 'A',
                'name' => 'Three Flashes or Below Threshold',
                'description' => 'No content flashes more than three times per second',
            ],
            '2.4.1' => [
                'level' => 'A',
                'name' => 'Bypass Blocks',
                'description' => 'Mechanism to skip repeated blocks of content',
            ],
            '2.4.2' => [
                'level' => 'A',
                'name' => 'Page Titled',
                'description' => 'Web pages have descriptive titles',
            ],
            '2.4.3' => [
                'level' => 'A',
                'name' => 'Focus Order',
                'description' => 'Focus order preserves meaning and operability',
            ],
            '2.4.4' => [
                'level' => 'A',
                'name' => 'Link Purpose (In Context)',
                'description' => 'Purpose of each link can be determined from link text',
            ],
            '2.4.5' => [
                'level' => 'AA',
                'name' => 'Multiple Ways',
                'description' => 'Multiple ways to locate a page within a set of pages',
            ],
            '2.4.6' => [
                'level' => 'AA',
                'name' => 'Headings and Labels',
                'description' => 'Headings and labels describe topic or purpose',
            ],
            '2.4.7' => [
                'level' => 'AA',
                'name' => 'Focus Visible',
                'description' => 'Keyboard focus indicator is visible',
            ],
            '2.4.11' => [
                'level' => 'AA',
                'name' => 'Focus Not Obscured (Minimum)',
                'description' => 'Focused component is not entirely hidden by author-created content',
            ],
            '2.5.1' => [
                'level' => 'A',
                'name' => 'Pointer Gestures',
                'description' => 'Multipoint or path-based gestures have single-pointer alternative',
            ],
            '2.5.2' => [
                'level' => 'A',
                'name' => 'Pointer Cancellation',
                'description' => 'Single-pointer actions can be cancelled or undone',
            ],
            '2.5.3' => [
                'level' => 'A',
                'name' => 'Label in Name',
                'description' => 'Accessible name contains visible label text',
            ],
            '2.5.4' => [
                'level' => 'A',
                'name' => 'Motion Actuation',
                'description' => 'Motion-activated functionality has alternative and can be disabled',
            ],
            '2.5.7' => [
                'level' => 'AA',
                'name' => 'Dragging Movements',
                'description' => 'Dragging movements have single-pointer alternative',
            ],
            '2.5.8' => [
                'level' => 'AA',
                'name' => 'Target Size (Minimum)',
                'description' => 'Touch targets are at least 24x24 CSS pixels',
            ],
            
            // Principle 3: Understandable
            '3.1.1' => [
                'level' => 'A',
                'name' => 'Language of Page',
                'description' => 'Default language of page can be programmatically determined',
            ],
            '3.1.2' => [
                'level' => 'AA',
                'name' => 'Language of Parts',
                'description' => 'Language of each passage can be programmatically determined',
            ],
            '3.2.1' => [
                'level' => 'A',
                'name' => 'On Focus',
                'description' => 'Receiving focus does not initiate change of context',
            ],
            '3.2.2' => [
                'level' => 'A',
                'name' => 'On Input',
                'description' => 'Changing settings does not automatically cause change of context',
            ],
            '3.2.3' => [
                'level' => 'AA',
                'name' => 'Consistent Navigation',
                'description' => 'Navigation mechanisms are consistent across pages',
            ],
            '3.2.4' => [
                'level' => 'AA',
                'name' => 'Consistent Identification',
                'description' => 'Components with same functionality are identified consistently',
            ],
            '3.2.6' => [
                'level' => 'A',
                'name' => 'Consistent Help',
                'description' => 'Help mechanisms appear in consistent order across pages',
            ],
            '3.3.1' => [
                'level' => 'A',
                'name' => 'Error Identification',
                'description' => 'Input errors are identified and described in text',
            ],
            '3.3.2' => [
                'level' => 'A',
                'name' => 'Labels or Instructions',
                'description' => 'Labels or instructions provided for user input',
            ],
            '3.3.3' => [
                'level' => 'AA',
                'name' => 'Error Suggestion',
                'description' => 'Suggestions provided for fixing input errors',
            ],
            '3.3.4' => [
                'level' => 'AA',
                'name' => 'Error Prevention (Legal, Financial, Data)',
                'description' => 'Submissions are reversible, checked, or confirmed',
            ],
            '3.3.7' => [
                'level' => 'A',
                'name' => 'Redundant Entry',
                'description' => 'Information previously entered can be auto-populated',
            ],
            '3.3.8' => [
                'level' => 'AA',
                'name' => 'Accessible Authentication (Minimum)',
                'description' => 'Authentication does not rely solely on cognitive function tests',
            ],
            
            // Principle 4: Robust
            '4.1.1' => [
                'level' => 'A',
                'name' => 'Parsing',
                'description' => 'Content can be parsed unambiguously',
            ],
            '4.1.2' => [
                'level' => 'A',
                'name' => 'Name, Role, Value',
                'description' => 'Name and role can be programmatically determined',
            ],
            '4.1.3' => [
                'level' => 'AA',
                'name' => 'Status Messages',
                'description' => 'Status messages can be programmatically determined',
            ],
        ];
    }
    
    /**
     * Get WCAG mapping data
     *
     * @since 1.0.0
     *
     * @return array Complete WCAG 2.2 mapping
     */
    public function get_wcag_mapping() {
        return $this->wcag_mapping;
    }
    
    /**
     * Get current DOM instance
     *
     * @since 1.0.0
     *
     * @return DOMDocument Current DOM document
     */
    public function get_dom() {
        return $this->dom;
    }
    
    /**
     * Get current XPath instance
     *
     * @since 1.0.0
     *
     * @return DOMXPath Current XPath query interface
     */
    public function get_xpath() {
        return $this->xpath;
    }
}
