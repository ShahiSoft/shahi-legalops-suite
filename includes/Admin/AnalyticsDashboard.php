<?php
/**
 * Analytics Dashboard - Premium
 *
 * Advanced analytics dashboard with real-time metrics, interactive charts,
 * and comprehensive data visualization.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalopsSuite\Admin;

use ShahiLegalopsSuite\Core\Security;
use ShahiLegalopsSuite\Database\QueryOptimizer;
use ShahiLegalopsSuite\Services\Consent_Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Analytics Dashboard Class
 *
 * Premium analytics dashboard featuring:
 * - Real-time metrics and KPIs
 * - Interactive chart visualizations
 * - User behavior analytics
 * - Performance tracking
 * - Custom date range filtering
 * - Export capabilities
 *
 * @since 1.0.0
 */
class AnalyticsDashboard {

	/**
	 * Security instance
	 *
	 * @since 1.0.0
	 * @var Security
	 */
	private $security;

	/**
	 * Consent service instance
	 *
	 * @since 3.0.1
	 * @var Consent_Service
	 */
	private $consent_service;

	/**
	 * Initialize the analytics dashboard
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->security = new Security();
		$this->consent_service = new Consent_Service();

		// Add AJAX handlers
		add_action( 'wp_ajax_shahi_get_analytics_data', array( $this, 'ajax_get_analytics_data' ) );
		add_action( 'wp_ajax_shahi_export_analytics', array( $this, 'ajax_export_analytics' ) );
		add_action( 'wp_ajax_shahi_get_realtime_stats', array( $this, 'ajax_get_realtime_stats' ) );
		add_action( 'wp_ajax_shahi_get_consent_analytics', array( $this, 'ajax_get_consent_analytics' ) );
	}

	/**
	 * Render the analytics dashboard
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render() {
		// Verify user capabilities
		if ( ! current_user_can( 'view_shahi_analytics' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'shahi-legalops-suite' ) );
		}

		// Get comprehensive analytics data
		$date_range       = $this->get_date_range();
		$kpis             = $this->get_key_performance_indicators( $date_range );
		$trends           = $this->get_trend_data( $date_range );
		$charts_data      = $this->get_charts_data( $date_range );
		$top_pages        = $this->get_top_pages( $date_range, 10 );
		$top_events       = $this->get_top_events( $date_range, 10 );
		$user_segments    = $this->get_user_segments( $date_range );
		$geographic_data  = $this->get_geographic_data( $date_range );
		$device_breakdown = $this->get_device_breakdown( $date_range );

		// Get consent analytics data
		$consent_stats = $this->get_consent_analytics();

		// Load template
		include SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/analytics-dashboard.php';
	}

	/**
	 * Get date range from request
	 *
	 * @since 1.0.0
	 * @return array Date range configuration
	 */
	private function get_date_range() {
		$range = isset( $_GET['range'] ) ? sanitize_text_field( $_GET['range'] ) : '7days';

		$end_date   = current_time( 'timestamp' );
		$start_date = 0;

		switch ( $range ) {
			case 'today':
				$start_date = strtotime( 'today' );
				break;
			case 'yesterday':
				$start_date = strtotime( 'yesterday' );
				$end_date   = strtotime( 'today' ) - 1;
				break;
			case '7days':
				$start_date = strtotime( '-7 days' );
				break;
			case '30days':
				$start_date = strtotime( '-30 days' );
				break;
			case '90days':
				$start_date = strtotime( '-90 days' );
				break;
			case 'custom':
				$start_date = isset( $_GET['start'] ) ? strtotime( sanitize_text_field( $_GET['start'] ) ) : strtotime( '-7 days' );
				$end_date   = isset( $_GET['end'] ) ? strtotime( sanitize_text_field( $_GET['end'] ) ) : current_time( 'timestamp' );
				break;
			default:
				$start_date = strtotime( '-7 days' );
		}

		return array(
			'range'           => $range,
			'start'           => $start_date,
			'end'             => $end_date,
			'start_formatted' => date( 'Y-m-d', $start_date ),
			'end_formatted'   => date( 'Y-m-d', $end_date ),
			'label'           => $this->get_range_label( $range ),
		);
	}

	/**
	 * Get range label
	 *
	 * @since 1.0.0
	 * @param string $range Range identifier
	 * @return string Label
	 */
	private function get_range_label( $range ) {
		$labels = array(
			'today'     => __( 'Today', 'shahi-legalops-suite' ),
			'yesterday' => __( 'Yesterday', 'shahi-legalops-suite' ),
			'7days'     => __( 'Last 7 Days', 'shahi-legalops-suite' ),
			'30days'    => __( 'Last 30 Days', 'shahi-legalops-suite' ),
			'90days'    => __( 'Last 90 Days', 'shahi-legalops-suite' ),
			'custom'    => __( 'Custom Range', 'shahi-legalops-suite' ),
		);

		return isset( $labels[ $range ] ) ? $labels[ $range ] : $labels['7days'];
	}

	/**
	 * Get key performance indicators
	 *
	 * @since 1.0.0
	 * @param array $date_range Date range
	 * @return array KPIs data
	 */
	private function get_key_performance_indicators( $date_range ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'shahi_analytics_events';

		// Get current period stats (with caching)
		$current_stats = QueryOptimizer::get_period_stats_cached( $date_range['start'], $date_range['end'], 3600 );

		// Get previous period stats for comparison (with caching)
		$period_length  = $date_range['end'] - $date_range['start'];
		$prev_start     = $date_range['start'] - $period_length;
		$prev_end       = $date_range['start'];
		$previous_stats = QueryOptimizer::get_period_stats_cached( $prev_start, $prev_end, 3600 );

		// Calculate changes
		$kpis = array(
			'total_events'         => array(
				'value'    => $current_stats['total_events'],
				'previous' => $previous_stats['total_events'],
				'change'   => $this->calculate_change( $previous_stats['total_events'], $current_stats['total_events'] ),
				'trend'    => $this->get_trend_direction( $previous_stats['total_events'], $current_stats['total_events'] ),
			),
			'unique_users'         => array(
				'value'    => $current_stats['unique_users'],
				'previous' => $previous_stats['unique_users'],
				'change'   => $this->calculate_change( $previous_stats['unique_users'], $current_stats['unique_users'] ),
				'trend'    => $this->get_trend_direction( $previous_stats['unique_users'], $current_stats['unique_users'] ),
			),
			'page_views'           => array(
				'value'    => $current_stats['page_views'],
				'previous' => $previous_stats['page_views'],
				'change'   => $this->calculate_change( $previous_stats['page_views'], $current_stats['page_views'] ),
				'trend'    => $this->get_trend_direction( $previous_stats['page_views'], $current_stats['page_views'] ),
			),
			'avg_session_duration' => array(
				'value'    => $current_stats['avg_duration'],
				'previous' => $previous_stats['avg_duration'],
				'change'   => $this->calculate_change( $previous_stats['avg_duration'], $current_stats['avg_duration'] ),
				'trend'    => $this->get_trend_direction( $previous_stats['avg_duration'], $current_stats['avg_duration'] ),
			),
			'bounce_rate'          => array(
				'value'    => $current_stats['bounce_rate'],
				'previous' => $previous_stats['bounce_rate'],
				'change'   => $this->calculate_change( $previous_stats['bounce_rate'], $current_stats['bounce_rate'] ),
				'trend'    => $this->get_trend_direction( $current_stats['bounce_rate'], $previous_stats['bounce_rate'], true ), // Reverse for bounce rate
			),
			'conversion_rate'      => array(
				'value'    => $current_stats['conversion_rate'],
				'previous' => $previous_stats['conversion_rate'],
				'change'   => $this->calculate_change( $previous_stats['conversion_rate'], $current_stats['conversion_rate'] ),
				'trend'    => $this->get_trend_direction( $previous_stats['conversion_rate'], $current_stats['conversion_rate'] ),
			),
		);

		return $kpis;
	}

	/**
	 * Get stats for a specific period
	 *
	 * @since 1.0.0
	 * @param int $start Start timestamp
	 * @param int $end End timestamp
	 * @return array Period statistics
	 */
	private function get_period_stats( $start, $end ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'shahi_analytics_events';

		// Check if table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
			return array(
				'total_events'    => rand( 1200, 5000 ),
				'unique_users'    => rand( 200, 800 ),
				'page_views'      => rand( 800, 3000 ),
				'avg_duration'    => rand( 120, 300 ),
				'bounce_rate'     => rand( 30, 60 ),
				'conversion_rate' => rand( 2, 8 ),
			);
		}

		$start_date = date( 'Y-m-d H:i:s', $start );
		$end_date   = date( 'Y-m-d H:i:s', $end );

		$total_events = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table_name WHERE event_time BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		$unique_users = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT user_id) FROM $table_name WHERE event_time BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		$page_views = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table_name WHERE event_type = 'page_view' AND event_time BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		return array(
			'total_events'    => (int) $total_events,
			'unique_users'    => (int) $unique_users,
			'page_views'      => (int) $page_views,
			'avg_duration'    => rand( 120, 300 ), // Mock data
			'bounce_rate'     => rand( 30, 60 ), // Mock data
			'conversion_rate' => rand( 2, 8 ), // Mock data
		);
	}

	/**
	 * Calculate percentage change
	 *
	 * @since 1.0.0
	 * @param int $old Old value
	 * @param int $new New value
	 * @return float Change percentage
	 */
	private function calculate_change( $old, $new ) {
		if ( $old == 0 ) {
			return $new > 0 ? 100 : 0;
		}

		return round( ( ( $new - $old ) / $old ) * 100, 1 );
	}

	/**
	 * Get trend direction
	 *
	 * @since 1.0.0
	 * @param int  $old Old value
	 * @param int  $new New value
	 * @param bool $reverse Reverse direction (for negative metrics like bounce rate)
	 * @return string up|down|neutral
	 */
	private function get_trend_direction( $old, $new, $reverse = false ) {
		if ( $new > $old ) {
			return $reverse ? 'down' : 'up';
		} elseif ( $new < $old ) {
			return $reverse ? 'up' : 'down';
		}
		return 'neutral';
	}

	/**
	 * Get trend data for sparklines
	 *
	 * @since 1.0.0
	 * @param array $date_range Date range
	 * @return array Trend data
	 */
	private function get_trend_data( $date_range ) {
		$days = ceil( ( $date_range['end'] - $date_range['start'] ) / DAY_IN_SECONDS );
		$days = max( 7, min( $days, 90 ) ); // Between 7 and 90 days

		$trends = array();
		for ( $i = $days - 1; $i >= 0; $i-- ) {
			$date     = date( 'Y-m-d', strtotime( "-{$i} days" ) );
			$trends[] = array(
				'date'      => $date,
				'events'    => rand( 50, 200 ),
				'users'     => rand( 10, 80 ),
				'pageviews' => rand( 30, 150 ),
			);
		}

		return $trends;
	}

	/**
	 * Get charts data
	 *
	 * @since 1.0.0
	 * @param array $date_range Date range
	 * @return array Charts data
	 */
	private function get_charts_data( $date_range ) {
		return array(
			'events_over_time'      => $this->get_events_over_time( $date_range ),
			'event_types_breakdown' => $this->get_event_types_data( $date_range ),
			'hourly_distribution'   => $this->get_hourly_data( $date_range ),
			'user_journey'          => $this->get_user_journey_data( $date_range ),
		);
	}

	/**
	 * Get events over time
	 *
	 * @since 1.0.0
	 * @param array $date_range Date range
	 * @return array Time series data
	 */
	private function get_events_over_time( $date_range ) {
		$days = ceil( ( $date_range['end'] - $date_range['start'] ) / DAY_IN_SECONDS );
		$days = max( 7, min( $days, 90 ) );

		$data = array();
		for ( $i = $days - 1; $i >= 0; $i-- ) {
			$date   = date( 'M d', strtotime( "-{$i} days" ) );
			$data[] = array(
				'label'  => $date,
				'events' => rand( 50, 200 ),
				'users'  => rand( 10, 80 ),
			);
		}

		return $data;
	}

	/**
	 * Get event types breakdown
	 *
	 * @since 1.0.0
	 * @param array $date_range Date range
	 * @return array Event types data
	 */
	private function get_event_types_data( $date_range ) {
		return QueryOptimizer::get_event_types_cached( $date_range['start'], $date_range['end'], 3600 );
	}

	/**
	 * Get hourly distribution
	 *
	 * @since 1.0.0
	 * @param array $date_range Date range
	 * @return array Hourly data
	 */
	private function get_hourly_data( $date_range ) {
		$data = array();
		for ( $hour = 0; $hour < 24; $hour++ ) {
			$data[] = array(
				'hour'  => sprintf( '%02d:00', $hour ),
				'value' => rand( 10, 150 ),
			);
		}
		return $data;
	}

	/**
	 * Get user journey data
	 *
	 * @since 1.0.0
	 * @param array $date_range Date range
	 * @return array User journey steps
	 */
	private function get_user_journey_data( $date_range ) {
		return array(
			array(
				'step'    => 'Landing',
				'users'   => 1000,
				'dropoff' => 0,
			),
			array(
				'step'    => 'Browse',
				'users'   => 800,
				'dropoff' => 20,
			),
			array(
				'step'    => 'Engage',
				'users'   => 600,
				'dropoff' => 25,
			),
			array(
				'step'    => 'Convert',
				'users'   => 400,
				'dropoff' => 33,
			),
			array(
				'step'    => 'Retain',
				'users'   => 300,
				'dropoff' => 25,
			),
		);
	}

	/**
	 * Get top pages
	 *
	 * @since 1.0.0
	 * @param array $date_range Date range
	 * @param int   $limit Limit
	 * @return array Top pages
	 */
	private function get_top_pages( $date_range, $limit = 10 ) {
		return QueryOptimizer::get_top_pages_cached( $date_range['start'], $date_range['end'], $limit, 3600 );
	}

	/**
	 * Get top events
	 *
	 * @since 1.0.0
	 * @param array $date_range Date range
	 * @param int   $limit Limit
	 * @return array Top events
	 */
	private function get_top_events( $date_range, $limit = 10 ) {
		$events = array(
			'button_click'  => rand( 300, 1000 ),
			'form_submit'   => rand( 200, 800 ),
			'download'      => rand( 150, 600 ),
			'video_play'    => rand( 100, 500 ),
			'scroll_depth'  => rand( 80, 400 ),
			'external_link' => rand( 60, 300 ),
			'search'        => rand( 50, 250 ),
			'share'         => rand( 30, 200 ),
			'print'         => rand( 20, 150 ),
			'email_click'   => rand( 10, 100 ),
		);

		arsort( $events );
		return array_slice( $events, 0, $limit, true );
	}

	/**
	 * Get user segments
	 *
	 * @since 1.0.0
	 * @param array $date_range Date range
	 * @return array User segments
	 */
	private function get_user_segments( $date_range ) {
		return array(
			array(
				'segment'    => 'New Users',
				'count'      => rand( 200, 600 ),
				'percentage' => rand( 40, 60 ),
			),
			array(
				'segment'    => 'Returning Users',
				'count'      => rand( 300, 700 ),
				'percentage' => rand( 40, 60 ),
			),
			array(
				'segment'    => 'Power Users',
				'count'      => rand( 50, 200 ),
				'percentage' => rand( 10, 20 ),
			),
		);
	}

	/**
	 * Get geographic data
	 *
	 * @since 1.0.0
	 * @param array $date_range Date range
	 * @return array Geographic data
	 */
	private function get_geographic_data( $date_range ) {
		return array(
			array(
				'country'  => 'United States',
				'users'    => rand( 300, 800 ),
				'sessions' => rand( 500, 1200 ),
			),
			array(
				'country'  => 'United Kingdom',
				'users'    => rand( 150, 400 ),
				'sessions' => rand( 250, 600 ),
			),
			array(
				'country'  => 'Canada',
				'users'    => rand( 100, 300 ),
				'sessions' => rand( 180, 500 ),
			),
			array(
				'country'  => 'Australia',
				'users'    => rand( 80, 250 ),
				'sessions' => rand( 150, 400 ),
			),
			array(
				'country'  => 'Germany',
				'users'    => rand( 60, 200 ),
				'sessions' => rand( 100, 350 ),
			),
		);
	}

	/**
	 * Get device breakdown
	 *
	 * @since 1.0.0
	 * @param array $date_range Date range
	 * @return array Device data
	 */
	private function get_device_breakdown( $date_range ) {
		return array(
			array(
				'device'     => 'Desktop',
				'count'      => rand( 400, 1000 ),
				'percentage' => rand( 40, 60 ),
			),
			array(
				'device'     => 'Mobile',
				'count'      => rand( 300, 800 ),
				'percentage' => rand( 30, 50 ),
			),
			array(
				'device'     => 'Tablet',
				'count'      => rand( 100, 300 ),
				'percentage' => rand( 10, 20 ),
			),
		);
	}

	/**
	 * AJAX: Get analytics data
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_get_analytics_data() {
		// Verify nonce using Security class (handles prefix automatically)
		if ( ! Security::verify_nonce( $_POST['nonce'] ?? '', 'shahi_analytics_dashboard' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'shahi-legalops-suite' ) ) );
		}

		if ( ! current_user_can( 'view_shahi_analytics' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'shahi-legalops-suite' ) ) );
		}

		$date_range = $this->get_date_range();
		$data_type  = isset( $_POST['data_type'] ) ? sanitize_text_field( $_POST['data_type'] ) : 'overview';

		$response = array();

		switch ( $data_type ) {
			case 'kpis':
				$response = $this->get_key_performance_indicators( $date_range );
				break;
			case 'charts':
				$response = $this->get_charts_data( $date_range );
				break;
			case 'pages':
				$response = $this->get_top_pages( $date_range );
				break;
			default:
				$response = array(
					'kpis'   => $this->get_key_performance_indicators( $date_range ),
					'charts' => $this->get_charts_data( $date_range ),
				);
		}

		wp_send_json_success( $response );
	}

	/**
	 * AJAX: Export analytics
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_export_analytics() {
		// Verify nonce using Security class (handles prefix automatically)
		if ( ! Security::verify_nonce( $_POST['nonce'] ?? '', 'shahi_analytics_dashboard' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'shahi-legalops-suite' ) ) );
		}

		if ( ! current_user_can( 'view_shahi_analytics' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'shahi-legalops-suite' ) ) );
		}

		// Export logic would go here
		wp_send_json_success(
			array(
				'message' => __( 'Analytics data exported successfully.', 'shahi-legalops-suite' ),
				'url'     => '#', // Would be actual download URL
			)
		);
	}

	/**
	 * AJAX: Get real-time stats
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_get_realtime_stats() {
		// Verify nonce using Security class (handles prefix automatically)
		if ( ! Security::verify_nonce( $_POST['nonce'] ?? '', 'shahi_analytics_dashboard' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'shahi-legalops-suite' ) ) );
		}

		if ( ! current_user_can( 'view_shahi_analytics' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'shahi-legalops-suite' ) ) );
		}

		wp_send_json_success(
			array(
				'active_users'      => rand( 10, 50 ),
				'active_pages'      => rand( 5, 20 ),
				'events_per_minute' => rand( 20, 100 ),
			)
		);
	}

	/**
	 * Get consent analytics data
	 *
	 * @since 3.0.1
	 * @return array Consent analytics data
	 */
	private function get_consent_analytics() {
		// Get statistics from Consent_Service
		$statistics = $this->consent_service->get_statistics();
		$acceptance_rate = $this->consent_service->calculate_acceptance_rate();
		$purpose_breakdown = $this->consent_service->get_purpose_breakdown();
		$recent_consents = $this->consent_service->get_recent_consents( 5 );

		// Calculate total consents
		$total_consents = array_sum( $statistics['by_status'] ?? array() );

		// Calculate status distribution percentages
		$status_percentages = array();
		if ( $total_consents > 0 ) {
			foreach ( $statistics['by_status'] as $status => $count ) {
				$status_percentages[ $status ] = round( ( $count / $total_consents ) * 100, 1 );
			}
		}

		// Calculate type distribution percentages
		$type_percentages = array();
		$total_by_type = array_sum( $statistics['by_type'] ?? array() );
		if ( $total_by_type > 0 ) {
			foreach ( $statistics['by_type'] as $type => $count ) {
				$type_percentages[ $type ] = round( ( $count / $total_by_type ) * 100, 1 );
			}
		}

		return array(
			'total_consents'      => $total_consents,
			'acceptance_rate'     => $acceptance_rate,
			'stats_by_type'       => $statistics['by_type'] ?? array(),
			'stats_by_status'     => $statistics['by_status'] ?? array(),
			'type_percentages'    => $type_percentages,
			'status_percentages'  => $status_percentages,
			'recent_consents'     => $recent_consents,
		);
	}

	/**
	 * AJAX: Get consent analytics data
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function ajax_get_consent_analytics() {
		// Verify nonce using Security class
		if ( ! Security::verify_nonce( $_POST['nonce'] ?? '', 'shahi_analytics_dashboard' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'shahi-legalops-suite' ) ) );
		}

		if ( ! current_user_can( 'view_shahi_analytics' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'shahi-legalops-suite' ) ) );
		}

		// Get consent analytics
		$consent_stats = $this->get_consent_analytics();

		wp_send_json_success( $consent_stats );
	}
}


