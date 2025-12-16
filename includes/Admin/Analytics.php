<?php
/**
 * Analytics Admin Page
 *
 * Displays analytics data, charts, and event tracking information.
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
 * Analytics Page Class
 *
 * Handles the rendering and functionality of the analytics page.
 * Displays event tracking, user behavior, and performance metrics.
 *
 * @since 1.0.0
 */
class Analytics {
    
    /**
     * Security instance
     *
     * @since 1.0.0
     * @var Security
     */
    private $security;
    
    /**
     * Initialize the analytics page
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->security = new Security();
    }
    
    /**
     * Render the analytics page
     *
     * @since 1.0.0
     * @return void
     */
    public function render() {
        // Verify user capabilities
        if (!current_user_can('view_shahi_analytics')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'shahi-legalops-suite'));
        }
        
        // Get analytics data
        $overview = $this->get_overview_stats();
        $event_types = $this->get_event_types_breakdown();
        $recent_events = $this->get_recent_events(50);
        $date_range = $this->get_date_range();
        $chart_data = $this->get_events_over_time_data();
        $hourly_data = $this->get_hourly_distribution_data();
        $user_activity = $this->get_user_activity_data();
        
        // Load template
        include SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/analytics.php';
    }
    
    /**
     * Get date range from query parameters
     *
     * @since 1.0.0
     * @return array Date range with start and end dates
     */
    private function get_date_range() {
        $range = isset($_GET['range']) ? sanitize_text_field($_GET['range']) : '7days';
        
        $end_date = current_time('mysql');
        $start_date = '';
        
        switch ($range) {
            case '24hours':
                $start_date = date('Y-m-d H:i:s', strtotime('-24 hours'));
                break;
            case '7days':
                $start_date = date('Y-m-d H:i:s', strtotime('-7 days'));
                break;
            case '30days':
                $start_date = date('Y-m-d H:i:s', strtotime('-30 days'));
                break;
            case '90days':
                $start_date = date('Y-m-d H:i:s', strtotime('-90 days'));
                break;
            case 'all':
                $start_date = '2000-01-01 00:00:00';
                break;
            default:
                $start_date = date('Y-m-d H:i:s', strtotime('-7 days'));
        }
        
        return [
            'range' => $range,
            'start' => $start_date,
            'end' => $end_date,
            'label' => $this->get_range_label($range),
        ];
    }
    
    /**
     * Get range label
     *
     * @since 1.0.0
     * @param string $range Range identifier.
     * @return string Human-readable label
     */
    private function get_range_label($range) {
        $labels = [
            '24hours' => __('Last 24 Hours', 'shahi-legalops-suite'),
            '7days' => __('Last 7 Days', 'shahi-legalops-suite'),
            '30days' => __('Last 30 Days', 'shahi-legalops-suite'),
            '90days' => __('Last 90 Days', 'shahi-legalops-suite'),
            'all' => __('All Time', 'shahi-legalops-suite'),
        ];
        
        return isset($labels[$range]) ? $labels[$range] : $labels['7days'];
    }
    
    /**
     * Get overview statistics
     *
     * @since 1.0.0
     * @return array Overview stats
     */
    private function get_overview_stats() {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_analytics';
        $date_range = $this->get_date_range();
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return $this->get_empty_stats();
        }
        
        // Total events in date range
        $total_events = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE created_at BETWEEN %s AND %s",
            $date_range['start'],
            $date_range['end']
        ));
        
        // Unique users
        $unique_users = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT user_id) FROM $table WHERE created_at BETWEEN %s AND %s AND user_id IS NOT NULL",
            $date_range['start'],
            $date_range['end']
        ));
        
        // Average events per day
        $days_diff = max(1, floor((strtotime($date_range['end']) - strtotime($date_range['start'])) / 86400));
        $avg_per_day = $total_events > 0 ? round($total_events / $days_diff, 2) : 0;
        
        // Most active hour
        $most_active_hour = $wpdb->get_var($wpdb->prepare(
            "SELECT HOUR(created_at) as hour, COUNT(*) as count 
             FROM $table 
             WHERE created_at BETWEEN %s AND %s 
             GROUP BY HOUR(created_at) 
             ORDER BY count DESC 
             LIMIT 1",
            $date_range['start'],
            $date_range['end']
        ));
        
        return [
            'total_events' => (int) $total_events,
            'unique_users' => (int) $unique_users,
            'avg_per_day' => $avg_per_day,
            'most_active_hour' => $most_active_hour !== null ? sprintf('%02d:00', $most_active_hour) : __('N/A', 'shahi-legalops-suite'),
        ];
    }
    
    /**
     * Get empty stats array
     *
     * @since 1.0.0
     * @return array Empty statistics
     */
    private function get_empty_stats() {
        return [
            'total_events' => 0,
            'unique_users' => 0,
            'avg_per_day' => 0,
            'most_active_hour' => __('N/A', 'shahi-legalops-suite'),
        ];
    }
    
    /**
     * Get event types breakdown
     *
     * @since 1.0.0
     * @return array Event types with counts
     */
    private function get_event_types_breakdown() {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_analytics';
        $date_range = $this->get_date_range();
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return [];
        }
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT event_type, COUNT(*) as count 
             FROM $table 
             WHERE created_at BETWEEN %s AND %s 
             GROUP BY event_type 
             ORDER BY count DESC 
             LIMIT 10",
            $date_range['start'],
            $date_range['end']
        ));
        
        if (empty($results)) {
            return [];
        }
        
        $total = array_sum(array_column($results, 'count'));
        $breakdown = [];
        
        foreach ($results as $row) {
            $percentage = $total > 0 ? round(($row->count / $total) * 100, 1) : 0;
            
            $breakdown[] = [
                'type' => $row->event_type,
                'label' => $this->format_event_type_label($row->event_type),
                'count' => (int) $row->count,
                'percentage' => $percentage,
            ];
        }
        
        return $breakdown;
    }
    
    /**
     * Format event type label
     *
     * @since 1.0.0
     * @param string $event_type Event type.
     * @return string Formatted label
     */
    private function format_event_type_label($event_type) {
        $labels = [
            'module_enabled' => __('Module Enabled', 'shahi-legalops-suite'),
            'module_disabled' => __('Module Disabled', 'shahi-legalops-suite'),
            'settings_updated' => __('Settings Updated', 'shahi-legalops-suite'),
            'plugin_activated' => __('Plugin Activated', 'shahi-legalops-suite'),
            'plugin_deactivated' => __('Plugin Deactivated', 'shahi-legalops-suite'),
            'page_view' => __('Page View', 'shahi-legalops-suite'),
            'ajax_request' => __('AJAX Request', 'shahi-legalops-suite'),
        ];
        
        return isset($labels[$event_type]) ? $labels[$event_type] : ucwords(str_replace('_', ' ', $event_type));
    }
    
    /**
     * Get recent events
     *
     * @since 1.0.0
     * @param int $limit Number of events to retrieve.
     * @return array Recent events
     */
    private function get_recent_events($limit = 50) {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_analytics';
        $date_range = $this->get_date_range();
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return [];
        }
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table 
             WHERE created_at BETWEEN %s AND %s 
             ORDER BY created_at DESC 
             LIMIT %d",
            $date_range['start'],
            $date_range['end'],
            $limit
        ));
        
        if (empty($results)) {
            return [];
        }
        
        $events = [];
        foreach ($results as $row) {
            $events[] = [
                'id' => $row->id,
                'type' => $row->event_type,
                'type_label' => $this->format_event_type_label($row->event_type),
                'data' => json_decode($row->event_data, true),
                'user_id' => $row->user_id,
                'user_display' => $this->get_user_display($row->user_id),
                'ip_address' => $row->ip_address,
                'user_agent' => $row->user_agent,
                'browser' => $this->parse_browser($row->user_agent),
                'created_at' => $row->created_at,
                'time_ago' => human_time_diff(strtotime($row->created_at), current_time('timestamp')) . ' ' . __('ago', 'shahi-legalops-suite'),
            ];
        }
        
        return $events;
    }
    
    /**
     * Get user display name
     *
     * @since 1.0.0
     * @param int $user_id User ID.
     * @return string User display name or "Guest"
     */
    private function get_user_display($user_id) {
        if (empty($user_id)) {
            return __('Guest', 'shahi-legalops-suite');
        }
        
        $user = get_userdata($user_id);
        return $user ? $user->display_name : __('Unknown User', 'shahi-legalops-suite');
    }
    
    /**
     * Parse browser from user agent
     *
     * @since 1.0.0
     * @param string $user_agent User agent string.
     * @return string Browser name
     */
    private function parse_browser($user_agent) {
        if (empty($user_agent)) {
            return __('Unknown', 'shahi-legalops-suite');
        }
        
        if (preg_match('/Firefox/i', $user_agent)) {
            return 'Firefox';
        } elseif (preg_match('/Chrome/i', $user_agent)) {
            return 'Chrome';
        } elseif (preg_match('/Safari/i', $user_agent)) {
            return 'Safari';
        } elseif (preg_match('/Edge/i', $user_agent)) {
            return 'Edge';
        } elseif (preg_match('/Opera|OPR/i', $user_agent)) {
            return 'Opera';
        } else {
            return __('Other', 'shahi-legalops-suite');
        }
    }
    
    /**
     * Get chart data for events over time
     *
     * This method would be called via AJAX to populate charts.
     *
     * @since 1.0.0
     * @return array Chart data
     */
    public function get_chart_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_analytics';
        $date_range = $this->get_date_range();
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return [];
        }
        
        // Get events grouped by date
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(created_at) as date, COUNT(*) as count 
             FROM $table 
             WHERE created_at BETWEEN %s AND %s 
             GROUP BY DATE(created_at) 
             ORDER BY date ASC",
            $date_range['start'],
            $date_range['end']
        ));
        
        if (empty($results)) {
            return [];
        }
        
        $chart_data = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => __('Events', 'shahi-legalops-suite'),
                    'data' => [],
                    'borderColor' => '#00d4ff',
                    'backgroundColor' => 'rgba(0, 212, 255, 0.1)',
                ],
            ],
        ];
        
        foreach ($results as $row) {
            $chart_data['labels'][] = date('M j', strtotime($row->date));
            $chart_data['datasets'][0]['data'][] = (int) $row->count;
        }
        
        return $chart_data;
    }
    
    /**
     * Get events over time data for line chart
     *
     * @since 1.0.0
     * @return array Chart data
     */
    private function get_events_over_time_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_analytics';
        $date_range = $this->get_date_range();
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return $this->get_mock_events_over_time();
        }
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(created_at) as date, COUNT(*) as count 
             FROM $table 
             WHERE created_at BETWEEN %s AND %s 
             GROUP BY DATE(created_at) 
             ORDER BY date ASC",
            $date_range['start'],
            $date_range['end']
        ));
        
        if (empty($results)) {
            return $this->get_mock_events_over_time();
        }
        
        $labels = [];
        $data = [];
        
        foreach ($results as $row) {
            $labels[] = date('M j', strtotime($row->date));
            $data[] = (int) $row->count;
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
    
    /**
     * Get hourly distribution data for bar chart
     *
     * @since 1.0.0
     * @return array Chart data
     */
    private function get_hourly_distribution_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_analytics';
        $date_range = $this->get_date_range();
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return $this->get_mock_hourly_distribution();
        }
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT HOUR(created_at) as hour, COUNT(*) as count 
             FROM $table 
             WHERE created_at BETWEEN %s AND %s 
             GROUP BY HOUR(created_at) 
             ORDER BY hour ASC",
            $date_range['start'],
            $date_range['end']
        ));
        
        if (empty($results)) {
            return $this->get_mock_hourly_distribution();
        }
        
        // Initialize all 24 hours with 0
        $hourly_counts = array_fill(0, 24, 0);
        
        foreach ($results as $row) {
            $hourly_counts[(int)$row->hour] = (int)$row->count;
        }
        
        $labels = [];
        for ($i = 0; $i < 24; $i++) {
            $labels[] = sprintf('%02d:00', $i);
        }
        
        return [
            'labels' => $labels,
            'data' => array_values($hourly_counts),
        ];
    }
    
    /**
     * Get user activity data for pie chart
     *
     * @since 1.0.0
     * @return array Chart data
     */
    private function get_user_activity_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_analytics';
        $date_range = $this->get_date_range();
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return $this->get_mock_user_activity();
        }
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                CASE 
                    WHEN user_id IS NULL THEN 'Guest'
                    ELSE 'Authenticated'
                END as user_type,
                COUNT(*) as count
             FROM $table 
             WHERE created_at BETWEEN %s AND %s 
             GROUP BY user_type",
            $date_range['start'],
            $date_range['end']
        ));
        
        if (empty($results)) {
            return $this->get_mock_user_activity();
        }
        
        $labels = [];
        $data = [];
        
        foreach ($results as $row) {
            $labels[] = $row->user_type === 'Guest' ? __('Guest Users', 'shahi-legalops-suite') : __('Authenticated Users', 'shahi-legalops-suite');
            $data[] = (int)$row->count;
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
    
    /**
     * Get mock events over time data
     * [MOCK DATA - For demonstration purposes]
     *
     * @since 1.0.0
     * @return array Mock chart data
     */
    private function get_mock_events_over_time() {
        $date_range = $this->get_date_range();
        $days = max(1, floor((strtotime($date_range['end']) - strtotime($date_range['start'])) / 86400));
        $days = min($days, 30); // Limit to 30 days for display
        
        $labels = [];
        $data = [];
        
        for ($i = $days; $i >= 0; $i--) {
            $date = date('M j', strtotime("-$i days"));
            $labels[] = $date;
            $data[] = rand(5, 50); // Mock data
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'is_mock' => true,
        ];
    }
    
    /**
     * Get mock hourly distribution data
     * [MOCK DATA - For demonstration purposes]
     *
     * @since 1.0.0
     * @return array Mock chart data
     */
    private function get_mock_hourly_distribution() {
        $labels = [];
        $data = [];
        
        for ($i = 0; $i < 24; $i++) {
            $labels[] = sprintf('%02d:00', $i);
            // Mock data with higher activity during work hours
            if ($i >= 9 && $i <= 17) {
                $data[] = rand(20, 60);
            } else {
                $data[] = rand(2, 15);
            }
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'is_mock' => true,
        ];
    }
    
    /**
     * Get mock user activity data
     * [MOCK DATA - For demonstration purposes]
     *
     * @since 1.0.0
     * @return array Mock chart data
     */
    private function get_mock_user_activity() {
        return [
            'labels' => [
                __('Guest Users', 'shahi-legalops-suite'),
                __('Authenticated Users', 'shahi-legalops-suite'),
            ],
            'data' => [rand(100, 300), rand(400, 800)],
            'is_mock' => true,
        ];
    }
}
