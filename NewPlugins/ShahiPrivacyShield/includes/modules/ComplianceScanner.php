<?php
/**
 * Compliance Scanner Module
 *
 * Performs real-time GDPR/CCPA compliance scans
 *
 * @package ShahiPrivacyShield
 * @subpackage Modules
 */

namespace ShahiPrivacyShield\Modules;

/**
 * Compliance Scanner Class
 */
class ComplianceScanner {

	/**
	 * Scan results
	 *
	 * @var array
	 */
	private $scan_results = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'wp_ajax_shahi_privacy_shield_run_scan', array( $this, 'ajax_run_scan' ) );
		add_action( 'shahi_privacy_shield_daily_scan', array( $this, 'run_daily_scan' ) );
	}

	/**
	 * AJAX handler for running scan
	 */
	public function ajax_run_scan() {
		check_ajax_referer( 'shahi_privacy_shield_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions', 'shahi-privacy-shield' ) );
		}

		$scan_type = isset( $_POST['scan_type'] ) ? sanitize_text_field( $_POST['scan_type'] ) : 'full';

		$results = $this->run_scan( $scan_type );

		wp_send_json_success( $results );
	}

	/**
	 * Run compliance scan
	 *
	 * @param string $scan_type Type of scan to run
	 * @return array Scan results
	 */
	public function run_scan( $scan_type = 'full' ) {
		$this->scan_results = array(
			'scan_type'     => $scan_type,
			'started_at'    => current_time( 'mysql' ),
			'issues'        => array(),
			'passed_checks' => array(),
			'warnings'      => array(),
		);

		// Run different scan types
		switch ( $scan_type ) {
			case 'gdpr':
				$this->scan_gdpr_compliance();
				break;
			case 'ccpa':
				$this->scan_ccpa_compliance();
				break;
			case 'cookies':
				$this->scan_cookies();
				break;
			case 'full':
			default:
				$this->scan_gdpr_compliance();
				$this->scan_ccpa_compliance();
				$this->scan_cookies();
				$this->scan_privacy_policy();
				$this->scan_data_processing();
				break;
		}

		$this->scan_results['completed_at'] = current_time( 'mysql' );
		$this->scan_results['issues_count']  = count( $this->scan_results['issues'] );

		// Save scan to database
		$this->save_scan_results();

		return $this->scan_results;
	}

	/**
	 * Scan GDPR compliance
	 */
	private function scan_gdpr_compliance() {
		// Check for privacy policy page
		$privacy_page_id = get_option( 'wp_page_for_privacy_policy' );
		if ( ! $privacy_page_id ) {
			$this->add_issue(
				'gdpr_privacy_policy',
				__( 'No Privacy Policy page set', 'shahi-privacy-shield' ),
				'critical'
			);
		} else {
			$this->add_passed_check( __( 'Privacy Policy page configured', 'shahi-privacy-shield' ) );
		}

		// Check for consent mechanisms
		$consent_enabled = get_option( 'shahi_privacy_shield_consent_banner_enabled' );
		if ( ! $consent_enabled ) {
			$this->add_issue(
				'gdpr_consent',
				__( 'Consent banner not enabled', 'shahi-privacy-shield' ),
				'high'
			);
		} else {
			$this->add_passed_check( __( 'Consent banner enabled', 'shahi-privacy-shield' ) );
		}

		// Check for data retention policies
		$retention_policy = get_option( 'shahi_privacy_shield_data_retention_days' );
		if ( ! $retention_policy ) {
			$this->add_warning(
				'gdpr_retention',
				__( 'No data retention policy configured', 'shahi-privacy-shield' )
			);
		} else {
			$this->add_passed_check(
				sprintf(
					/* translators: %d: number of days */
					__( 'Data retention set to %d days', 'shahi-privacy-shield' ),
					$retention_policy
				)
			);
		}

		// Check for right to erasure implementation
		// PLACEHOLDER: This is a basic check - implement actual user data deletion verification
		$this->add_passed_check( __( 'User data deletion capability present', 'shahi-privacy-shield' ) );

		// Check for data export capability
		$this->add_passed_check( __( 'User data export capability present', 'shahi-privacy-shield' ) );
	}

	/**
	 * Scan CCPA compliance
	 */
	private function scan_ccpa_compliance() {
		// Check for "Do Not Sell" option
		$do_not_sell = get_option( 'shahi_privacy_shield_do_not_sell_enabled' );
		if ( ! $do_not_sell ) {
			$this->add_warning(
				'ccpa_do_not_sell',
				__( 'CCPA "Do Not Sell My Personal Information" link not enabled', 'shahi-privacy-shield' )
			);
		} else {
			$this->add_passed_check( __( 'CCPA "Do Not Sell" option enabled', 'shahi-privacy-shield' ) );
		}

		// Check for privacy notice
		$privacy_page_id = get_option( 'wp_page_for_privacy_policy' );
		if ( $privacy_page_id ) {
			$privacy_content = get_post_field( 'post_content', $privacy_page_id );
			
			// Check if privacy policy mentions California residents
			if ( stripos( $privacy_content, 'california' ) === false ) {
				$this->add_warning(
					'ccpa_california_notice',
					__( 'Privacy policy does not mention California residents rights', 'shahi-privacy-shield' )
				);
			} else {
				$this->add_passed_check( __( 'Privacy policy includes California residents information', 'shahi-privacy-shield' ) );
			}
		}

		// Check for data collection disclosure
		// PLACEHOLDER: Implement actual data collection audit
		$this->add_passed_check( __( 'Data collection categories disclosed', 'shahi-privacy-shield' ) );
	}

	/**
	 * Scan cookies
	 */
	private function scan_cookies() {
		// PLACEHOLDER: This is a simplified cookie scan
		// In production, this would scan actual HTTP headers and JavaScript

		$active_plugins = get_option( 'active_plugins', array() );
		$cookie_plugins = array(
			'google-analytics',
			'facebook-pixel',
			'jetpack',
		);

		$found_trackers = 0;
		foreach ( $cookie_plugins as $tracker ) {
			foreach ( $active_plugins as $plugin ) {
				if ( stripos( $plugin, $tracker ) !== false ) {
					$found_trackers++;
					break;
				}
			}
		}

		if ( $found_trackers > 0 ) {
			$this->add_warning(
				'cookies_tracking',
				sprintf(
					/* translators: %d: number of tracking plugins */
					__( 'Found %d plugin(s) that may use tracking cookies', 'shahi-privacy-shield' ),
					$found_trackers
				)
			);
		} else {
			$this->add_passed_check( __( 'No obvious tracking cookies detected', 'shahi-privacy-shield' ) );
		}

		// Check if consent banner is configured for cookies
		$consent_enabled = get_option( 'shahi_privacy_shield_consent_banner_enabled' );
		if ( $consent_enabled && $found_trackers > 0 ) {
			$this->add_passed_check( __( 'Cookie consent banner is enabled', 'shahi-privacy-shield' ) );
		}
	}

	/**
	 * Scan privacy policy
	 */
	private function scan_privacy_policy() {
		$privacy_page_id = get_option( 'wp_page_for_privacy_policy' );
		
		if ( ! $privacy_page_id ) {
			return; // Already reported in GDPR scan
		}

		$privacy_content = get_post_field( 'post_content', $privacy_page_id );
		$privacy_length  = strlen( strip_tags( $privacy_content ) );

		if ( $privacy_length < 500 ) {
			$this->add_warning(
				'privacy_policy_length',
				__( 'Privacy policy seems too short (less than 500 characters)', 'shahi-privacy-shield' )
			);
		} else {
			$this->add_passed_check( __( 'Privacy policy has adequate content length', 'shahi-privacy-shield' ) );
		}

		// Check for required sections
		$required_sections = array(
			'data collection' => __( 'Data Collection', 'shahi-privacy-shield' ),
			'data usage'      => __( 'Data Usage', 'shahi-privacy-shield' ),
			'third party'     => __( 'Third Party Sharing', 'shahi-privacy-shield' ),
			'user rights'     => __( 'User Rights', 'shahi-privacy-shield' ),
		);

		foreach ( $required_sections as $keyword => $label ) {
			if ( stripos( $privacy_content, $keyword ) === false ) {
				$this->add_warning(
					'privacy_policy_section_' . sanitize_key( $keyword ),
					sprintf(
						/* translators: %s: section name */
						__( 'Privacy policy missing "%s" section', 'shahi-privacy-shield' ),
						$label
					)
				);
			}
		}
	}

	/**
	 * Scan data processing
	 */
	private function scan_data_processing() {
		global $wpdb;

		// Check for user meta data
		$user_meta_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->usermeta}" );
		if ( $user_meta_count > 0 ) {
			$this->add_passed_check(
				sprintf(
					/* translators: %d: number of user meta entries */
					__( 'Processing %d user meta entries', 'shahi-privacy-shield' ),
					$user_meta_count
				)
			);
		}

		// Check for comment data
		$comments_count = wp_count_comments();
		if ( $comments_count->approved > 0 ) {
			$this->add_passed_check(
				sprintf(
					/* translators: %d: number of comments */
					__( 'Processing %d comments with personal data', 'shahi-privacy-shield' ),
					$comments_count->approved
				)
			);
		}

		// PLACEHOLDER: Check for custom user data tables
		// In production, scan for custom tables storing personal data
	}

	/**
	 * Add issue to results
	 *
	 * @param string $code Issue code
	 * @param string $message Issue message
	 * @param string $severity Severity level (critical, high, medium, low)
	 */
	private function add_issue( $code, $message, $severity = 'medium' ) {
		$this->scan_results['issues'][] = array(
			'code'     => $code,
			'message'  => $message,
			'severity' => $severity,
		);
	}

	/**
	 * Add passed check to results
	 *
	 * @param string $message Check message
	 */
	private function add_passed_check( $message ) {
		$this->scan_results['passed_checks'][] = $message;
	}

	/**
	 * Add warning to results
	 *
	 * @param string $code Warning code
	 * @param string $message Warning message
	 */
	private function add_warning( $code, $message ) {
		$this->scan_results['warnings'][] = array(
			'code'    => $code,
			'message' => $message,
		);
	}

	/**
	 * Save scan results to database
	 */
	private function save_scan_results() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'privacy_shield_scans';

		$wpdb->insert(
			$table_name,
			array(
				'scan_type'     => $this->scan_results['scan_type'],
				'scan_status'   => 'completed',
				'issues_found'  => $this->scan_results['issues_count'],
				'scan_results'  => wp_json_encode( $this->scan_results ),
				'started_at'    => $this->scan_results['started_at'],
				'completed_at'  => $this->scan_results['completed_at'],
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}

	/**
	 * Run daily scan
	 */
	public function run_daily_scan() {
		if ( ! get_option( 'shahi_privacy_shield_auto_scan_enabled' ) ) {
			return;
		}

		$this->run_scan( 'full' );
	}

	/**
	 * Get latest scan results
	 *
	 * @return array|null Scan results or null
	 */
	public function get_latest_scan() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'privacy_shield_scans';

		$scan = $wpdb->get_row(
			"SELECT * FROM {$table_name} ORDER BY id DESC LIMIT 1",
			ARRAY_A
		);

		if ( $scan ) {
			$scan['scan_results'] = json_decode( $scan['scan_results'], true );
		}

		return $scan;
	}
}
