<?php
/**
 * Onboarding Controller
 *
 * Handles the multi-step onboarding wizard for first-time users.
 * Collects user preferences and configures the plugin accordingly.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalopsSuite\Admin;

use ShahiLegalopsSuite\Core\Security;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Onboarding Class
 *
 * Manages the onboarding wizard flow, data collection, and completion tracking.
 *
 * @since 1.0.0
 */
class Onboarding {
    
    /**
     * Security instance
     *
     * @since 1.0.0
     * @var Security
     */
    private $security;
    
    /**
     * Option name for onboarding completion status
     *
     * @since 1.0.0
     * @var string
     */
    const OPTION_COMPLETED = 'shahi_legalops_suite_onboarding_completed';
    
    /**
     * Option name for onboarding data
     *
     * @since 1.0.0
     * @var string
     */
    const OPTION_DATA = 'shahi_legalops_suite_onboarding_data';
    
    /**
     * Initialize the onboarding controller
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->security = new Security();
    }
    
    /**
     * Check if onboarding should be shown
     *
     * @since 1.0.0
     * @return bool True if onboarding should be displayed
     */
    public function should_show_onboarding() {
        // Debug logging
        $debug_log = [];
        
        // Force fresh read from database, bypassing cache
        wp_cache_delete('shahi_legalops_suite_onboarding_completed', 'options');
        wp_cache_delete('shahi_legalops_suite_onboarding_data', 'options');
        
        // Don't show if user doesn't have permission
        $has_permission = current_user_can('manage_shahi_template');
        $debug_log['has_permission'] = $has_permission;
        if (!$has_permission) {
            error_log('SHAHI ONBOARDING: User lacks manage_shahi_template capability');
            return false;
        }
        
        // Don't show if already completed
        $is_completed = get_option(self::OPTION_COMPLETED, false);
        $debug_log['is_completed'] = $is_completed;
        $debug_log['completed_option_value'] = $is_completed;
        if ($is_completed) {
            error_log('SHAHI ONBOARDING: Already completed (option value: ' . var_export($is_completed, true) . ')');
            return false;
        }
        
        // Only show on plugin pages
        $is_plugin_page = $this->is_plugin_page();
        $debug_log['is_plugin_page'] = $is_plugin_page;
        $debug_log['current_page'] = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'none';
        if (!$is_plugin_page) {
            error_log('SHAHI ONBOARDING: Not on plugin page (current: ' . $debug_log['current_page'] . ')');
            return false;
        }
        
        error_log('SHAHI ONBOARDING: All checks passed - SHOWING MODAL - ' . json_encode($debug_log));
        return true;
    }
    
    /**
     * Check if current page is a plugin page
     *
     * @since 1.0.0
     * @return bool True if on a plugin page
     */
    private function is_plugin_page() {
        if (!is_admin()) {
            return false;
        }
        
        $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
        return strpos($page, 'shahi-legalops-suite') === 0;
    }
    
    /**
     * Render the onboarding modal
     *
     * Outputs the onboarding modal HTML if conditions are met.
     *
     * @since 1.0.0
     * @return void
     */
    public function render_modal() {
        error_log('SHAHI ONBOARDING: render_modal() called');
        
        if (!$this->should_show_onboarding()) {
            error_log('SHAHI ONBOARDING: should_show_onboarding() returned false - NOT RENDERING');
            return;
        }
        
        error_log('SHAHI ONBOARDING: Including modal template from: ' . SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/onboarding-modal.php');
        include SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/onboarding-modal.php';
        error_log('SHAHI ONBOARDING: Modal template included successfully');
    }
    
    /**
     * Get onboarding steps data
     *
     * @since 1.0.0
     * @return array Onboarding steps configuration
     */
    public function get_steps() {
        return [
            'welcome' => [
                'title' => __('Welcome to ShahiLegalopsSuite', 'shahi-legalops-suite'),
                'subtitle' => __('Let\'s get you set up in just a few steps', 'shahi-legalops-suite'),
                'icon' => 'dashicons-welcome-learn-more',
            ],
            'purpose' => [
                'title' => __('What will you use this plugin for?', 'shahi-legalops-suite'),
                'subtitle' => __('Help us customize your experience', 'shahi-legalops-suite'),
                'icon' => 'dashicons-admin-tools',
            ],
            'features' => [
                'title' => __('Choose Your Features', 'shahi-legalops-suite'),
                'subtitle' => __('Enable the modules that fit your needs', 'shahi-legalops-suite'),
                'icon' => 'dashicons-admin-plugins',
            ],
            'configuration' => [
                'title' => __('Basic Configuration', 'shahi-legalops-suite'),
                'subtitle' => __('Set up your preferences', 'shahi-legalops-suite'),
                'icon' => 'dashicons-admin-settings',
            ],
            'complete' => [
                'title' => __('All Set!', 'shahi-legalops-suite'),
                'subtitle' => __('You\'re ready to start using ShahiLegalopsSuite', 'shahi-legalops-suite'),
                'icon' => 'dashicons-yes-alt',
            ],
        ];
    }
    
    /**
     * Get purpose options
     *
     * @since 1.0.0
     * @return array Purpose options for step 2
     */
    public function get_purpose_options() {
        return [
            'ecommerce' => [
                'label' => __('E-commerce', 'shahi-legalops-suite'),
                'description' => __('Online store or product catalog', 'shahi-legalops-suite'),
                'icon' => 'dashicons-cart',
            ],
            'blog' => [
                'label' => __('Blog / Content', 'shahi-legalops-suite'),
                'description' => __('Publishing articles and content', 'shahi-legalops-suite'),
                'icon' => 'dashicons-edit-page',
            ],
            'business' => [
                'label' => __('Business Site', 'shahi-legalops-suite'),
                'description' => __('Company or service website', 'shahi-legalops-suite'),
                'icon' => 'dashicons-building',
            ],
            'portfolio' => [
                'label' => __('Portfolio', 'shahi-legalops-suite'),
                'description' => __('Showcase work or projects', 'shahi-legalops-suite'),
                'icon' => 'dashicons-portfolio',
            ],
            'membership' => [
                'label' => __('Membership', 'shahi-legalops-suite'),
                'description' => __('Community or member area', 'shahi-legalops-suite'),
                'icon' => 'dashicons-groups',
            ],
            'other' => [
                'label' => __('Other', 'shahi-legalops-suite'),
                'description' => __('Something else', 'shahi-legalops-suite'),
                'icon' => 'dashicons-grid-view',
            ],
        ];
    }
    
    /**
     * Get recommended modules based on purpose
     *
     * @since 1.0.0
     * @param string $purpose User's selected purpose.
     * @return array Recommended module keys
     */
    public function get_recommended_modules($purpose = '') {
        $recommendations = [
            'ecommerce' => ['analytics', 'cache', 'security'],
            'blog' => ['analytics', 'seo', 'cache'],
            'business' => ['analytics', 'security', 'notifications'],
            'portfolio' => ['analytics', 'cache'],
            'membership' => ['security', 'notifications', 'api'],
            'other' => ['analytics'],
        ];
        
        return isset($recommendations[$purpose]) ? $recommendations[$purpose] : ['analytics'];
    }
    
    /**
     * Get available modules for selection
     *
     * @since 1.0.0
     * @return array Available modules
     */
    public function get_available_modules() {
        return [
            'analytics' => [
                'name' => __('Analytics Tracking', 'shahi-legalops-suite'),
                'description' => __('Track user behavior and plugin usage', 'shahi-legalops-suite'),
                'icon' => 'dashicons-chart-line',
            ],
            'notifications' => [
                'name' => __('Email Notifications', 'shahi-legalops-suite'),
                'description' => __('Automated email alerts for events', 'shahi-legalops-suite'),
                'icon' => 'dashicons-email',
            ],
            'cache' => [
                'name' => __('Advanced Caching', 'shahi-legalops-suite'),
                'description' => __('Improve performance with caching', 'shahi-legalops-suite'),
                'icon' => 'dashicons-performance',
            ],
            'security' => [
                'name' => __('Enhanced Security', 'shahi-legalops-suite'),
                'description' => __('Additional security features', 'shahi-legalops-suite'),
                'icon' => 'dashicons-shield',
            ],
            'api' => [
                'name' => __('REST API', 'shahi-legalops-suite'),
                'description' => __('Extended API endpoints', 'shahi-legalops-suite'),
                'icon' => 'dashicons-rest-api',
            ],
            'import_export' => [
                'name' => __('Import/Export', 'shahi-legalops-suite'),
                'description' => __('Backup and restore settings', 'shahi-legalops-suite'),
                'icon' => 'dashicons-database-export',
            ],
        ];
    }
    
    /**
     * Save onboarding data
     *
     * Processes the onboarding form submission and saves user preferences.
     *
     * @since 1.0.0
     * @return void
     */
    public function save_onboarding() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !Security::verify_nonce($_POST['nonce'], 'shahi_onboarding')) {
            wp_send_json_error([
                'message' => __('Security check failed.', 'shahi-legalops-suite'),
            ]);
        }
        
        // Check permissions
        if (!current_user_can('manage_shahi_template')) {
            wp_send_json_error([
                'message' => __('Insufficient permissions.', 'shahi-legalops-suite'),
            ]);
        }
        
        // Get submitted data
        $purpose = isset($_POST['purpose']) ? sanitize_text_field($_POST['purpose']) : '';
        $modules = isset($_POST['modules']) && is_array($_POST['modules']) 
            ? array_map('sanitize_text_field', $_POST['modules']) 
            : [];
        $settings = isset($_POST['settings']) && is_array($_POST['settings']) 
            ? $_POST['settings'] 
            : [];
        
        // Sanitize settings
        $sanitized_settings = [
            'enable_analytics' => isset($settings['enable_analytics']) ? (bool) $settings['enable_analytics'] : true,
            'enable_notifications' => isset($settings['enable_notifications']) ? (bool) $settings['enable_notifications'] : false,
        ];
        
        // Save onboarding data
        $onboarding_data = [
            'purpose' => $purpose,
            'modules_enabled' => $modules,
            'settings' => $sanitized_settings,
            'completed_at' => current_time('mysql'),
            'user_id' => get_current_user_id(),
        ];
        
        update_option(self::OPTION_DATA, $onboarding_data);
        update_option(self::OPTION_COMPLETED, true);
        
        // Enable selected modules in database
        $this->enable_modules($modules);
        
        // Apply basic settings
        $this->apply_settings($sanitized_settings);
        
        // Track analytics event
        $this->track_onboarding_completion($onboarding_data);
        
        wp_send_json_success([
            'message' => __('Onboarding completed successfully!', 'shahi-legalops-suite'),
            'redirect' => admin_url('admin.php?page=shahi-legalops-suite'),
        ]);
    }
    
    /**
     * Enable selected modules
     *
     * @since 1.0.0
     * @param array $modules Array of module keys to enable.
     * @return void
     */
    private function enable_modules($modules) {
        if (empty($modules)) {
            return;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_modules';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return;
        }
        
        foreach ($modules as $module_key) {
            // Check if module exists
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE module_key = %s",
                $module_key
            ));
            
            if ($exists) {
                // Update existing module
                $wpdb->update(
                    $table,
                    [
                        'is_enabled' => 1,
                        'last_updated' => current_time('mysql'),
                    ],
                    ['module_key' => $module_key],
                    ['%d', '%s'],
                    ['%s']
                );
            } else {
                // Insert new module
                $wpdb->insert(
                    $table,
                    [
                        'module_key' => $module_key,
                        'is_enabled' => 1,
                        'settings' => '{}',
                        'last_updated' => current_time('mysql'),
                    ],
                    ['%s', '%d', '%s', '%s']
                );
            }
        }
    }
    
    /**
     * Apply basic settings from onboarding
     *
     * @since 1.0.0
     * @param array $settings Settings to apply.
     * @return void
     */
    private function apply_settings($settings) {
        $current_settings = get_option('shahi_legalops_suite_settings', []);
        
        // Merge with onboarding preferences
        if (isset($settings['enable_analytics'])) {
            $current_settings['enable_analytics'] = $settings['enable_analytics'];
        }
        
        if (isset($settings['enable_notifications'])) {
            $current_settings['enable_email_notifications'] = $settings['enable_notifications'];
        }
        
        update_option('shahi_legalops_suite_settings', $current_settings);
    }
    
    /**
     * Track onboarding completion event
     *
     * @since 1.0.0
     * @param array $data Onboarding data.
     * @return void
     */
    private function track_onboarding_completion($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_analytics';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return;
        }
        
        $event_data = json_encode([
            'purpose' => $data['purpose'],
            'modules_count' => count($data['modules_enabled']),
            'modules' => $data['modules_enabled'],
        ]);
        
        $wpdb->insert(
            $table,
            [
                'event_type' => 'onboarding_completed',
                'event_data' => $event_data,
                'user_id' => get_current_user_id(),
                'ip_address' => $this->security->get_client_ip(),
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : '',
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%d', '%s', '%s', '%s']
        );
    }
    
    /**
     * Skip onboarding
     *
     * Marks onboarding as completed without collecting data.
     *
     * @since 1.0.0
     * @return void
     */
    public function skip_onboarding() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !Security::verify_nonce($_POST['nonce'], 'shahi_onboarding')) {
            wp_send_json_error([
                'message' => __('Security check failed.', 'shahi-legalops-suite'),
            ]);
        }
        
        // Check permissions
        if (!current_user_can('manage_shahi_template')) {
            wp_send_json_error([
                'message' => __('Insufficient permissions.', 'shahi-legalops-suite'),
            ]);
        }
        
        // Mark as completed without data
        update_option(self::OPTION_COMPLETED, true);
        update_option(self::OPTION_DATA, [
            'skipped' => true,
            'completed_at' => current_time('mysql'),
        ]);
        
        wp_send_json_success([
            'message' => __('Onboarding skipped.', 'shahi-legalops-suite'),
            'redirect' => admin_url('admin.php?page=shahi-legalops-suite'),
        ]);
    }
    
    /**
     * Reset onboarding
     *
     * Allows users to re-run the onboarding wizard.
     *
     * @since 1.0.0
     * @return bool True on success
     */
    public function reset_onboarding() {
        if (!current_user_can('manage_shahi_template')) {
            return false;
        }
        
        delete_option(self::OPTION_COMPLETED);
        delete_option(self::OPTION_DATA);
        
        return true;
    }
    
    /**
     * Get onboarding data
     *
     * @since 1.0.0
     * @return array Saved onboarding data
     */
    public function get_onboarding_data() {
        return get_option(self::OPTION_DATA, []);
    }
    
    /**
     * Check if onboarding is completed
     *
     * @since 1.0.0
     * @return bool True if completed
     */
    public function is_completed() {
        return (bool) get_option(self::OPTION_COMPLETED, false);
    }
}
