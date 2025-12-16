<?php
/**
 * Consent Manager Module
 *
 * Handles user consent collection and management
 *
 * @package ShahiPrivacyShield
 * @subpackage Modules
 */

namespace ShahiPrivacyShield\Modules;

/**
 * Consent Manager Class
 */
class ConsentManager {

	/**
	 * Consent types
	 *
	 * @var array
	 */
	private $consent_types = array(
		'necessary'   => 'Necessary Cookies',
		'analytics'   => 'Analytics & Performance',
		'marketing'   => 'Marketing & Advertising',
		'preferences' => 'Preferences',
	);

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
		add_action( 'wp_footer', array( $this, 'render_consent_banner' ) );
		add_action( 'wp_ajax_shahi_privacy_shield_save_consent', array( $this, 'ajax_save_consent' ) );
		add_action( 'wp_ajax_nopriv_shahi_privacy_shield_save_consent', array( $this, 'ajax_save_consent' ) );
		add_action( 'wp_ajax_shahi_privacy_shield_get_consent_stats', array( $this, 'ajax_get_consent_stats' ) );
	}

	/**
	 * Render consent banner
	 */
	public function render_consent_banner() {
		// Don't show if user already consented
		if ( $this->has_user_consented() ) {
			return;
		}

		// Don't show if banner is disabled
		if ( ! get_option( 'shahi_privacy_shield_consent_banner_enabled', '1' ) ) {
			return;
		}

		include SHAHI_PRIVACY_SHIELD_PLUGIN_DIR . 'public/views/consent-banner.php';
	}

	/**
	 * Check if user has consented
	 *
	 * @return bool
	 */
	private function has_user_consented() {
		// Check cookie
		return isset( $_COOKIE['shahi_privacy_shield_consent'] );
	}

	/**
	 * AJAX handler for saving consent
	 */
	public function ajax_save_consent() {
		check_ajax_referer( 'shahi_privacy_shield_consent_nonce', 'nonce' );

		$consents = isset( $_POST['consents'] ) ? (array) $_POST['consents'] : array();

		// Sanitize consent data
		$sanitized_consents = array();
		foreach ( $consents as $type => $value ) {
			if ( isset( $this->consent_types[ $type ] ) ) {
				$sanitized_consents[ $type ] = (bool) $value;
			}
		}

		// Save consent to database
		$consent_id = $this->save_consent( $sanitized_consents );

		if ( $consent_id ) {
			// Set cookie (1 year expiration)
			setcookie(
				'shahi_privacy_shield_consent',
				wp_json_encode( $sanitized_consents ),
				time() + YEAR_IN_SECONDS,
				COOKIEPATH,
				COOKIE_DOMAIN,
				is_ssl(),
				true
			);

			wp_send_json_success(
				array(
					'message'     => __( 'Your consent preferences have been saved.', 'shahi-privacy-shield' ),
					'consent_id'  => $consent_id,
					'consents'    => $sanitized_consents,
				)
			);
		} else {
			wp_send_json_error( __( 'Failed to save consent preferences.', 'shahi-privacy-shield' ) );
		}
	}

	/**
	 * Save consent to database
	 *
	 * @param array $consents Consent preferences
	 * @return int|false Insert ID or false on failure
	 */
	private function save_consent( $consents ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'privacy_shield_consents';
		$user_id    = get_current_user_id();
		$ip_address = $this->get_user_ip();
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		$inserted = $wpdb->insert(
			$table_name,
			array(
				'user_id'       => $user_id ? $user_id : null,
				'ip_address'    => $ip_address,
				'consent_type'  => 'cookie_consent',
				'consent_given' => 1,
				'consent_data'  => wp_json_encode( $consents ),
				'user_agent'    => $user_agent,
			),
			array( '%d', '%s', '%s', '%d', '%s', '%s' )
		);

		return $inserted ? $wpdb->insert_id : false;
	}

	/**
	 * Get user IP address
	 *
	 * @return string IP address
	 */
	private function get_user_ip() {
		$ip_keys = array(
			'HTTP_CF_CONNECTING_IP', // CloudFlare
			'HTTP_X_FORWARDED_FOR',  // Proxy
			'REMOTE_ADDR',           // Direct
		);

		foreach ( $ip_keys as $key ) {
			if ( isset( $_SERVER[ $key ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
				
				// Handle comma-separated IPs (from proxies)
				if ( strpos( $ip, ',' ) !== false ) {
					$ip = trim( explode( ',', $ip )[0] );
				}

				// Validate IP
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '0.0.0.0';
	}

	/**
	 * AJAX handler for getting consent statistics
	 */
	public function ajax_get_consent_stats() {
		check_ajax_referer( 'shahi_privacy_shield_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions', 'shahi-privacy-shield' ) );
		}

		$stats = $this->get_consent_statistics();

		wp_send_json_success( $stats );
	}

	/**
	 * Get consent statistics
	 *
	 * @return array Statistics
	 */
	public function get_consent_statistics() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'privacy_shield_consents';

		// Total consents
		$total_consents = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table_name}"
		);

		// Consents by type
		$consent_breakdown = array();
		foreach ( $this->consent_types as $type => $label ) {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table_name} 
					WHERE consent_data LIKE %s",
					'%"' . $type . '":true%'
				)
			);
			$consent_breakdown[ $type ] = array(
				'label' => $label,
				'count' => (int) $count,
			);
		}

		// Recent consents (last 7 days)
		$recent_consents = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} 
				WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
				7
			)
		);

		// Consent rate (percentage who accepted at least one category)
		$accepted_any = $wpdb->get_var(
			"SELECT COUNT(DISTINCT ip_address) FROM {$table_name} 
			WHERE consent_given = 1"
		);

		return array(
			'total_consents'     => (int) $total_consents,
			'consent_breakdown'  => $consent_breakdown,
			'recent_consents'    => (int) $recent_consents,
			'accepted_any'       => (int) $accepted_any,
		);
	}

	/**
	 * Get user's consent preferences
	 *
	 * @return array|null Consent preferences or null
	 */
	public function get_user_consent() {
		// Check cookie first
		if ( isset( $_COOKIE['shahi_privacy_shield_consent'] ) ) {
			$consents = json_decode( stripslashes( $_COOKIE['shahi_privacy_shield_consent'] ), true );
			if ( is_array( $consents ) ) {
				return $consents;
			}
		}

		// Check database for logged-in users
		if ( is_user_logged_in() ) {
			global $wpdb;

			$table_name = $wpdb->prefix . 'privacy_shield_consents';
			$user_id    = get_current_user_id();

			$consent_data = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT consent_data FROM {$table_name} 
					WHERE user_id = %d 
					ORDER BY id DESC 
					LIMIT 1",
					$user_id
				)
			);

			if ( $consent_data ) {
				return json_decode( $consent_data, true );
			}
		}

		return null;
	}

	/**
	 * Check if user has consented to specific type
	 *
	 * @param string $type Consent type
	 * @return bool
	 */
	public function has_consent_for( $type ) {
		// Necessary cookies are always allowed
		if ( $type === 'necessary' ) {
			return true;
		}

		$consents = $this->get_user_consent();

		if ( ! $consents ) {
			return false;
		}

		return isset( $consents[ $type ] ) && $consents[ $type ];
	}

	/**
	 * Revoke user consent
	 *
	 * @return bool Success
	 */
	public function revoke_consent() {
		// Clear cookie
		setcookie(
			'shahi_privacy_shield_consent',
			'',
			time() - 3600,
			COOKIEPATH,
			COOKIE_DOMAIN,
			is_ssl(),
			true
		);

		// Log revocation in database
		if ( is_user_logged_in() ) {
			global $wpdb;

			$table_name = $wpdb->prefix . 'privacy_shield_consents';
			$user_id    = get_current_user_id();
			$ip_address = $this->get_user_ip();

			$wpdb->insert(
				$table_name,
				array(
					'user_id'       => $user_id,
					'ip_address'    => $ip_address,
					'consent_type'  => 'cookie_consent',
					'consent_given' => 0,
					'consent_data'  => wp_json_encode( array() ),
				),
				array( '%d', '%s', '%s', '%d', '%s' )
			);
		}

		return true;
	}

	/**
	 * Get consent types
	 *
	 * @return array Consent types
	 */
	public function get_consent_types() {
		return $this->consent_types;
	}
}
