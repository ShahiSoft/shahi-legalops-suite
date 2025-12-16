<?php
/**
 * Accessibility Scanner Module
 *
 * Comprehensive WCAG 2.2 accessibility scanning with 60+ automated checks,
 * 25+ one-click fixes, AI-powered features, and frontend accessibility widget.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Modules\AccessibilityScanner
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalopsSuite\Modules\AccessibilityScanner;

use ShahiLegalopsSuite\Modules\Module;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Admin\Settings;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Database\Schema;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AccessibilityScanner Module Class
 *
 * Provides comprehensive WCAG 2.2 accessibility scanning and automatic fixes.
 * Includes scanner engine with 60+ checkers, one-click fixes, AI-powered alt text
 * generation, and customizable frontend widget with 70+ accessibility features.
 *
 * @since 1.0.0
 */
class AccessibilityScanner extends Module {
    
    /**
     * Module unique key
     *
     * @since 1.0.0
     * @var string
     */
    protected $key = 'accessibility-scanner';
    
    /**
     * Settings instance
     *
     * @since 1.0.0
     * @var Settings
     */
    private $settings;
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        parent::__construct();
        
        // Initialize settings
        $this->settings = new Settings();
    }
    
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
        return __('Accessibility Scanner', 'shahi-legalops-suite');
    }
    
    /**
     * Get module description
     *
     * @since 1.0.0
     * @return string Module description
     */
    public function get_description() {
        return __('Comprehensive WCAG 2.2 accessibility scanning with 60+ automated checks, 25+ one-click fixes, and AI-powered features.', 'shahi-legalops-suite');
    }
    
    /**
     * Get module icon
     *
     * @since 1.0.0
     * @return string Icon class
     */
    public function get_icon() {
        return 'dashicons-universal-access-alt';
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
     * Get module version
     *
     * @since 1.0.0
     * @return string Version number
     */
    public function get_version() {
        return '1.0.0';
    }
    
    /**
     * Get module priority
     *
     * Used for dashboard sorting (high priority modules appear first).
     *
     * @since 1.0.0
     * @return string Priority level (high|medium|low)
     */
    public function get_priority() {
        return 'high';
    }
    
    /**
     * Initialize module
     *
     * Called when module is loaded and enabled. Registers hooks, filters,
     * admin pages, AJAX handlers, REST API endpoints, and CLI commands.
     *
     * @since 1.0.0
     * @return void
     */
    public function init() {
        // Register admin menu pages
        add_action('admin_menu', [$this, 'register_admin_pages'], 20);
        
        // Enqueue admin assets
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // Enqueue frontend widget assets (if widget enabled)
        if ($this->get_setting('widget', 'enabled', false)) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_widget_assets']);
            add_action('wp_footer', [$this, 'render_widget']);
        }
        
        // Register AJAX handlers
        add_action('wp_ajax_shahi_a11y_run_scan', [$this, 'ajax_run_scan']);
        add_action('wp_ajax_shahi_a11y_apply_fix', [$this, 'ajax_apply_fix']);
        add_action('wp_ajax_shahi_a11y_get_issues', [$this, 'ajax_get_issues']);
        add_action('wp_ajax_shahi_a11y_ignore_issue', [$this, 'ajax_ignore_issue']);
        add_action('wp_ajax_shahi_a11y_export_report', [$this, 'ajax_export_report']);
        
        // Register REST API endpoints
        add_action('rest_api_init', [$this, 'register_rest_routes']);
        
        // Register WP-CLI commands (if in CLI context)
        if (defined('WP_CLI') && WP_CLI) {
            \WP_CLI::add_command('a11y', 'ShahiLegalopsSuite\\Modules\\AccessibilityScanner\\CLI\\Commands');
        }
        
        // Schedule automated scans (if enabled)
        $scan_frequency = $this->get_setting('general', 'auto_scan_interval', 'never');
        if ($scan_frequency !== 'never') {
            add_action('shahi_a11y_scheduled_scan', [$this, 'run_scheduled_scan']);
            
            if (!wp_next_scheduled('shahi_a11y_scheduled_scan')) {
                wp_schedule_event(time(), $scan_frequency, 'shahi_a11y_scheduled_scan');
            }
        }
    }
    
    /**
     * Hook called on module activation
     *
     * Creates database tables, sets default settings, and schedules tasks.
     *
     * @since 1.0.0
     * @return void
     */
    protected function on_activate() {
        // Create database tables
        $this->create_database_tables();
        
        // Set default settings if not exist
        $existing_settings = get_option('shahi_a11y_settings', false);
        
        if (!$existing_settings) {
            $default_settings = [
                'scan_frequency' => 'disabled',
                'auto_fix_enabled' => false,
                'wcag_level' => 'AA',
                'widget_enabled' => false,
                'widget_position' => 'bottom-right',
                'widget_color' => '#c0c0c0',
                'ai_enabled' => false,
                'ai_provider' => '',
                'ai_api_key' => '',
                'email_notifications' => false,
                'notification_email' => get_option('admin_email'),
                'enabled_checks' => [], // Empty = all enabled
                'enabled_fixes' => [],  // Empty = all enabled
                'enabled_widget_features' => [], // Empty = all enabled
            ];
            
            update_option('shahi_a11y_settings', $default_settings);
        }
        
        // Set module version
        update_option('shahi_a11y_version', $this->get_version());
    }
    
    /**
     * Hook called on module deactivation
     *
     * Clears scheduled tasks and temporary data.
     *
     * @since 1.0.0
     * @return void
     */
    protected function on_deactivate() {
        // Clear scheduled scans
        wp_clear_scheduled_hook('shahi_a11y_scheduled_scan');
        
        // Clear transients
        delete_transient('shahi_a11y_scan_results');
        delete_transient('shahi_a11y_dashboard_stats');
    }
    
    /**
     * Create database tables
     *
     * Creates all required database tables for the module using dbDelta.
     * Delegates to Schema class for table definitions.
     *
     * @since 1.0.0
     * @return void
     */
    private function create_database_tables() {
        $schema = new Schema();
        $schema->create_tables();
    }
    
    /**
     * Register admin menu pages
     *
     * Adds menu pages under the Module Dashboard.
     *
     * @since 1.0.0
     * @return void
     */
    public function register_admin_pages() {
        // Main dashboard page
        add_submenu_page(
            'shahi-module-dashboard',
            __('Accessibility Scanner', 'shahi-legalops-suite'),
            __('Accessibility', 'shahi-legalops-suite'),
            'manage_options',
            'shahi-accessibility',
            [$this, 'render_dashboard_page']
        );
        
        // Settings page
        add_submenu_page(
            'shahi-accessibility',
            __('Accessibility Settings', 'shahi-legalops-suite'),
            __('Settings', 'shahi-legalops-suite'),
            'manage_options',
            'shahi-accessibility-settings',
            [$this->settings, 'render']
        );
        
        // Scan results page
        add_submenu_page(
            'shahi-accessibility',
            __('Scan Results', 'shahi-legalops-suite'),
            __('Scan Results', 'shahi-legalops-suite'),
            'manage_options',
            'shahi-accessibility-results',
            [$this, 'render_results_page']
        );
        
        // Reports page
        add_submenu_page(
            'shahi-accessibility',
            __('Reports', 'shahi-legalops-suite'),
            __('Reports', 'shahi-legalops-suite'),
            'manage_options',
            'shahi-accessibility-reports',
            [$this, 'render_reports_page']
        );
    }
    
    /**
     * Enqueue admin assets
     *
     * Loads CSS and JavaScript files for admin pages.
     *
     * @since 1.0.0
     * @param string $hook Current admin page hook.
     * @return void
     */
    public function enqueue_admin_assets($hook) {
        // Only load on module pages
        $module_pages = [
            'shahi-module-dashboard_page_shahi-accessibility',
            'admin_page_shahi-accessibility-settings',
            'admin_page_shahi-accessibility-results',
            'admin_page_shahi-accessibility-reports',
        ];
        
        if (!in_array($hook, $module_pages, true)) {
            return;
        }
        
        // Enqueue CSS
        wp_enqueue_style(
            'shahi-accessibility-admin',
            plugin_dir_url(dirname(dirname(dirname(__FILE__)))) . 'assets/css/accessibility-scanner/admin.css',
            ['shahi-admin-global', 'shahi-components'],
            $this->get_version()
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'shahi-accessibility-admin',
            plugin_dir_url(dirname(dirname(dirname(__FILE__)))) . 'assets/js/accessibility-scanner/admin.js',
            ['jquery', 'wp-i18n'],
            $this->get_version(),
            true
        );
        
        // Localize script with data
        wp_localize_script('shahi-accessibility-admin', 'shahiA11y', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('shahi_a11y_nonce'),
            'strings' => [
                'scanRunning' => __('Scan in progress...', 'shahi-legalops-suite'),
                'scanComplete' => __('Scan completed', 'shahi-legalops-suite'),
                'fixApplied' => __('Fix applied successfully', 'shahi-legalops-suite'),
                'fixFailed' => __('Fix failed to apply', 'shahi-legalops-suite'),
                'confirmIgnore' => __('Are you sure you want to ignore this issue?', 'shahi-legalops-suite'),
            ],
        ]);
    }
    
    /**
     * Enqueue widget assets
     *
     * Loads CSS and JavaScript files for frontend widget.
     *
     * @since 1.0.0
     * @return void
     */
    public function enqueue_widget_assets() {
        wp_enqueue_style(
            'shahi-accessibility-widget',
            plugin_dir_url(dirname(dirname(dirname(__FILE__)))) . 'assets/css/accessibility-scanner/widget.css',
            [],
            $this->get_version()
        );
        
        wp_enqueue_script(
            'shahi-accessibility-widget',
            plugin_dir_url(dirname(dirname(dirname(__FILE__)))) . 'assets/js/accessibility-scanner/widget.js',
            ['jquery'],
            $this->get_version(),
            true
        );
        
        wp_localize_script('shahi-accessibility-widget', 'shahiA11yWidget', [
            'position' => $this->get_setting('widget_position', 'bottom-right'),
            'color' => $this->get_setting('widget_color', '#c0c0c0'),
            'enabledFeatures' => $this->get_setting('enabled_widget_features', []),
        ]);
    }
    
    /**
     * Render widget
     *
     * Outputs the frontend accessibility widget HTML.
     *
     * @since 1.0.0
     * @return void
     */
    public function render_widget() {
        // Widget will be rendered here (future implementation)
        echo '<!-- Accessibility Widget Placeholder -->';
    }
    
    /**
     * Render dashboard page
     *
     * @since 1.0.0
     * @return void
     */
    public function render_dashboard_page() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Accessibility Scanner Dashboard', 'shahi-legalops-suite') . '</h1>';
        echo '<p>' . esc_html__('Dashboard content will be implemented in future tasks.', 'shahi-legalops-suite') . '</p>';
        echo '</div>';
    }
    
    /**
     * Render settings page
     *
     * @since 1.0.0
     * @return void
     */
    public function render_settings_page() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Accessibility Scanner Settings', 'shahi-legalops-suite') . '</h1>';
        echo '<p>' . esc_html__('Settings interface will be implemented in future tasks.', 'shahi-legalops-suite') . '</p>';
        echo '</div>';
    }
    
    /**
     * Render results page
     *
     * @since 1.0.0
     * @return void
     */
    public function render_results_page() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Scan Results', 'shahi-legalops-suite') . '</h1>';
        echo '<p>' . esc_html__('Scan results will be implemented in future tasks.', 'shahi-legalops-suite') . '</p>';
        echo '</div>';
    }
    
    /**
     * Render reports page
     *
     * @since 1.0.0
     * @return void
     */
    public function render_reports_page() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Accessibility Reports', 'shahi-legalops-suite') . '</h1>';
        echo '<p>' . esc_html__('Reports will be implemented in future tasks.', 'shahi-legalops-suite') . '</p>';
        echo '</div>';
    }
    
    /**
     * AJAX handler: Run scan
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_run_scan() {
        check_ajax_referer('shahi_a11y_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'shahi-legalops-suite')]);
        }
        
        // Placeholder - Scanner engine will be implemented in future tasks
        wp_send_json_success([
            'message' => __('Scan functionality will be implemented in Task 1.7', 'shahi-legalops-suite'),
        ]);
    }
    
    /**
     * AJAX handler: Apply fix
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_apply_fix() {
        check_ajax_referer('shahi_a11y_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'shahi-legalops-suite')]);
        }
        
        // Placeholder
        wp_send_json_success([
            'message' => __('Fix engine will be implemented in future tasks', 'shahi-legalops-suite'),
        ]);
    }
    
    /**
     * AJAX handler: Get issues
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_get_issues() {
        check_ajax_referer('shahi_a11y_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'shahi-legalops-suite')]);
        }
        
        // Placeholder
        wp_send_json_success([
            'issues' => [],
        ]);
    }
    
    /**
     * AJAX handler: Ignore issue
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_ignore_issue() {
        check_ajax_referer('shahi_a11y_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'shahi-legalops-suite')]);
        }
        
        // Placeholder
        wp_send_json_success([
            'message' => __('Issue ignored', 'shahi-legalops-suite'),
        ]);
    }
    
    /**
     * AJAX handler: Export report
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_export_report() {
        check_ajax_referer('shahi_a11y_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'shahi-legalops-suite')]);
        }
        
        // Placeholder
        wp_send_json_success([
            'message' => __('Report export will be implemented in future tasks', 'shahi-legalops-suite'),
        ]);
    }
    
    /**
     * Register REST API routes
     *
     * @since 1.0.0
     * @return void
     */
    public function register_rest_routes() {
        register_rest_route('shahi-legalops-suite/v1', '/accessibility/scan', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_run_scan'],
            'permission_callback' => function() {
                return current_user_can('manage_options');
            },
        ]);
        
        register_rest_route('shahi-legalops-suite/v1', '/accessibility/issues', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_issues'],
            'permission_callback' => function() {
                return current_user_can('manage_options');
            },
        ]);
    }
    
    /**
     * REST API: Run scan
     *
     * @since 1.0.0
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response Response object
     */
    public function rest_run_scan($request) {
        // Placeholder
        return new \WP_REST_Response([
            'message' => 'REST API scan will be implemented in future tasks',
        ], 200);
    }
    
    /**
     * REST API: Get issues
     *
     * @since 1.0.0
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response Response object
     */
    public function rest_get_issues($request) {
        // Placeholder
        return new \WP_REST_Response([
            'issues' => [],
        ], 200);
    }
    
    /**
     * Run scheduled scan
     *
     * @since 1.0.0
     * @return void
     */
    public function run_scheduled_scan() {
        // Placeholder - will be implemented with scanner engine
    }
    
    /**
     * Get module settings URL
     *
     * @since 1.0.0
     * @return string Settings URL
     */
    public function get_settings_url() {
        return admin_url('admin.php?page=shahi-accessibility-settings');
    }
    
    /**
     * Get module documentation URL
     *
     * @since 1.0.0
     * @return string Documentation URL
     */
    public function get_documentation_url() {
        return 'https://shahilegalops.com/docs/accessibility-scanner/';
    }
    
    /**
     * Get module statistics
     *
     * Returns statistics for display on Module Dashboard card.
     *
     * @since 1.0.0
     * @return array Statistics array
     */
    public function get_stats() {
        // Check for cached stats
        $cached_stats = get_transient('shahi_a11y_dashboard_stats');
        if ($cached_stats !== false) {
            return $cached_stats;
        }
        
        global $wpdb;
        
        // Get total scans
        $total_scans = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}slos_a11y_scans"
        );
        
        // Get total issues found
        $total_issues = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}slos_a11y_issues"
        );
        
        // Get total fixes applied
        $total_fixes = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}slos_a11y_fixes WHERE applied = 1"
        );
        
        // Get last scan date
        $last_scan = $wpdb->get_var(
            "SELECT MAX(completed_at) FROM {$wpdb->prefix}slos_a11y_scans WHERE status = 'completed'"
        );
        
        // Calculate average score
        $avg_score = $wpdb->get_var(
            "SELECT AVG(score) FROM {$wpdb->prefix}slos_a11y_scans WHERE status = 'completed'"
        );
        
        $stats = [
            'scans_run' => intval($total_scans),
            'issues_found' => intval($total_issues),
            'fixes_applied' => intval($total_fixes),
            'last_scan' => $last_scan ? mysql2date('F j, Y g:i a', $last_scan) : __('Never', 'shahi-legalops-suite'),
            'performance_score' => $avg_score ? round($avg_score) : 0,
        ];
        
        // Cache stats for 5 minutes
        set_transient('shahi_a11y_dashboard_stats', $stats, 5 * MINUTE_IN_SECONDS);
        
        return $stats;
    }
    
    /**
     * Get a setting value
     *
     * @since 1.0.0
     * @param string $section Setting section
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed Setting value
     */
    private function get_setting($section, $key, $default = null) {
        if ($this->settings) {
            return $this->settings->get_setting($section, $key, $default);
        }
        return $default;
    }
}
