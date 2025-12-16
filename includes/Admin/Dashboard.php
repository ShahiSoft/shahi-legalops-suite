<?php
/**
 * Dashboard Admin Page
 *
 * Renders the main dashboard page with statistics, quick actions,
 * recent activity, and getting started guide.
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
 * Dashboard Page Class
 *
 * Handles the rendering and functionality of the main dashboard page.
 * Displays statistics, quick actions, and recent activity.
 *
 * @since 1.0.0
 */
class Dashboard {
    
    /**
     * Security instance
     *
     * @since 1.0.0
     * @var Security
     */
    private $security;
    
    /**
     * Initialize the dashboard
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->security = new Security();
    }
    
    /**
     * Render the dashboard page
     *
     * @since 1.0.0
     * @return void
     */
    public function render() {
        // Verify user capabilities
        if (!current_user_can('manage_shahi_template')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'shahi-legalops-suite'));
        }
        
        // Get dashboard data
        $stats = $this->get_statistics();
        $quick_actions = $this->get_quick_actions();
        $recent_activity = $this->get_recent_activity();
        $getting_started = $this->get_getting_started_items();
        
        // Load template
        include SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/dashboard.php';
    }
    
    /**
     * Get dashboard statistics
     *
     * Returns an array of statistics to display on the dashboard.
     *
     * @since 1.0.0
     * @return array Statistics data
     */
    private function get_statistics() {
        return [
            [
                'title' => __('Active Modules', 'shahi-legalops-suite'),
                'value' => $this->get_active_modules_count(),
                'icon' => 'dashicons-admin-plugins',
                'color' => 'primary',
                'trend' => null,
            ],
            [
                'title' => __('Total Events', 'shahi-legalops-suite'),
                'value' => $this->get_total_events_count(),
                'icon' => 'dashicons-chart-line',
                'color' => 'success',
                'trend' => '+12%',
            ],
            [
                'title' => __('Performance Score', 'shahi-legalops-suite'),
                'value' => '98',
                'suffix' => '%',
                'icon' => 'dashicons-performance',
                'color' => 'accent',
                'trend' => '+5%',
            ],
            [
                'title' => __('Last Activity', 'shahi-legalops-suite'),
                'value' => $this->get_last_activity_time(),
                'icon' => 'dashicons-clock',
                'color' => 'info',
                'trend' => null,
                'is_time' => true,
            ],
        ];
    }
    
    /**
     * Get active modules count
     *
     * @since 1.0.0
     * @return int Number of active modules
     */
    private function get_active_modules_count() {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_modules';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return 0;
        }
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE is_enabled = 1");
        return (int) $count;
    }
    
    /**
     * Get total events count
     *
     * @since 1.0.0
     * @return int Number of tracked events
     */
    private function get_total_events_count() {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_analytics';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return 0;
        }
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        return (int) $count;
    }
    
    /**
     * Get last activity time
     *
     * @since 1.0.0
     * @return string Formatted time or "N/A"
     */
    private function get_last_activity_time() {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_analytics';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return __('N/A', 'shahi-legalops-suite');
        }
        
        $last_time = $wpdb->get_var("SELECT created_at FROM $table ORDER BY created_at DESC LIMIT 1");
        
        if (!$last_time) {
            return __('N/A', 'shahi-legalops-suite');
        }
        
        return human_time_diff(strtotime($last_time), current_time('timestamp')) . ' ' . __('ago', 'shahi-legalops-suite');
    }
    
    /**
     * Get quick actions
     *
     * Returns an array of quick action buttons to display on the dashboard.
     *
     * @since 1.0.0
     * @return array Quick actions data
     */
    private function get_quick_actions() {
        return [
            [
                'title' => __('Manage Modules', 'shahi-legalops-suite'),
                'description' => __('Enable or disable plugin modules', 'shahi-legalops-suite'),
                'icon' => 'dashicons-admin-plugins',
                'url' => admin_url('admin.php?page=shahi-legalops-suite-modules'),
                'color' => 'primary',
            ],
            [
                'title' => __('View Analytics', 'shahi-legalops-suite'),
                'description' => __('Check your plugin analytics', 'shahi-legalops-suite'),
                'icon' => 'dashicons-chart-bar',
                'url' => admin_url('admin.php?page=shahi-legalops-suite-analytics'),
                'color' => 'success',
            ],
            [
                'title' => __('Plugin Settings', 'shahi-legalops-suite'),
                'description' => __('Configure plugin options', 'shahi-legalops-suite'),
                'icon' => 'dashicons-admin-settings',
                'url' => admin_url('admin.php?page=shahi-legalops-suite-settings'),
                'color' => 'accent',
            ],
            [
                'title' => __('Get Support', 'shahi-legalops-suite'),
                'description' => __('Documentation and help', 'shahi-legalops-suite'),
                'icon' => 'dashicons-sos',
                'url' => admin_url('admin.php?page=shahi-legalops-suite-support'),
                'color' => 'info',
            ],
        ];
    }
    
    /**
     * Get recent activity
     *
     * Returns recent activity events to display on the dashboard.
     *
     * @since 1.0.0
     * @param int $limit Number of items to return.
     * @return array Recent activity data
     */
    private function get_recent_activity($limit = 10) {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_analytics';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return [];
        }
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table ORDER BY created_at DESC LIMIT %d",
                $limit
            )
        );
        
        if (empty($results)) {
            return [];
        }
        
        $activity = [];
        foreach ($results as $event) {
            $activity[] = [
                'title' => $this->format_event_title($event->event_type),
                'description' => $this->format_event_description($event),
                'time' => human_time_diff(strtotime($event->created_at), current_time('timestamp')) . ' ' . __('ago', 'shahi-legalops-suite'),
                'icon' => $this->get_event_icon($event->event_type),
                'type' => $event->event_type,
            ];
        }
        
        return $activity;
    }
    
    /**
     * Format event title
     *
     * @since 1.0.0
     * @param string $event_type Event type.
     * @return string Formatted title
     */
    private function format_event_title($event_type) {
        $titles = [
            'module_enabled' => __('Module Enabled', 'shahi-legalops-suite'),
            'module_disabled' => __('Module Disabled', 'shahi-legalops-suite'),
            'settings_updated' => __('Settings Updated', 'shahi-legalops-suite'),
            'plugin_activated' => __('Plugin Activated', 'shahi-legalops-suite'),
            'plugin_deactivated' => __('Plugin Deactivated', 'shahi-legalops-suite'),
        ];
        
        return isset($titles[$event_type]) ? $titles[$event_type] : ucwords(str_replace('_', ' ', $event_type));
    }
    
    /**
     * Format event description
     *
     * @since 1.0.0
     * @param object $event Event object.
     * @return string Formatted description
     */
    private function format_event_description($event) {
        $data = json_decode($event->event_data, true);
        
        if (empty($data)) {
            return __('No additional details', 'shahi-legalops-suite');
        }
        
        // Format based on event type
        switch ($event->event_type) {
            case 'module_enabled':
            case 'module_disabled':
                return isset($data['module_name']) ? $data['module_name'] : __('Unknown module', 'shahi-legalops-suite');
            
            case 'settings_updated':
                return isset($data['section']) ? sprintf(__('Section: %s', 'shahi-legalops-suite'), $data['section']) : __('General settings', 'shahi-legalops-suite');
            
            default:
                return __('Event recorded', 'shahi-legalops-suite');
        }
    }
    
    /**
     * Get event icon
     *
     * @since 1.0.0
     * @param string $event_type Event type.
     * @return string Dashicon class
     */
    private function get_event_icon($event_type) {
        $icons = [
            'module_enabled' => 'dashicons-yes-alt',
            'module_disabled' => 'dashicons-dismiss',
            'settings_updated' => 'dashicons-admin-settings',
            'plugin_activated' => 'dashicons-plugins-checked',
            'plugin_deactivated' => 'dashicons-plugins-checked',
        ];
        
        return isset($icons[$event_type]) ? $icons[$event_type] : 'dashicons-marker';
    }
    
    /**
     * Get getting started items
     *
     * Returns a checklist of getting started tasks.
     *
     * @since 1.0.0
     * @return array Getting started items
     */
    private function get_getting_started_items() {
        $onboarding_completed = get_option('shahi_legalops_suite_onboarding_completed', false);
        $modules_configured = $this->get_active_modules_count() > 0;
        $settings_configured = !empty(get_option('shahi_legalops_suite_settings', []));
        
        return [
            [
                'title' => __('Complete Onboarding', 'shahi-legalops-suite'),
                'description' => __('Set up your plugin with our guided onboarding wizard', 'shahi-legalops-suite'),
                'completed' => $onboarding_completed,
                'action_text' => $onboarding_completed ? __('Review', 'shahi-legalops-suite') : __('Start Now', 'shahi-legalops-suite'),
                'action_url' => '#', // Will trigger onboarding modal via JS
                'action_class' => 'shahi-trigger-onboarding',
            ],
            [
                'title' => __('Enable Modules', 'shahi-legalops-suite'),
                'description' => __('Activate the features you need for your site', 'shahi-legalops-suite'),
                'completed' => $modules_configured,
                'action_text' => __('Manage Modules', 'shahi-legalops-suite'),
                'action_url' => admin_url('admin.php?page=shahi-legalops-suite-modules'),
                'action_class' => '',
            ],
            [
                'title' => __('Configure Settings', 'shahi-legalops-suite'),
                'description' => __('Customize plugin behavior to match your needs', 'shahi-legalops-suite'),
                'completed' => $settings_configured,
                'action_text' => __('Go to Settings', 'shahi-legalops-suite'),
                'action_url' => admin_url('admin.php?page=shahi-legalops-suite-settings'),
                'action_class' => '',
            ],
            [
                'title' => __('Explore Analytics', 'shahi-legalops-suite'),
                'description' => __('Track your plugin performance and usage', 'shahi-legalops-suite'),
                'completed' => false,
                'action_text' => __('View Analytics', 'shahi-legalops-suite'),
                'action_url' => admin_url('admin.php?page=shahi-legalops-suite-analytics'),
                'action_class' => '',
            ],
        ];
    }
}
