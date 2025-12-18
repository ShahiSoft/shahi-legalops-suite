<?php
/**
 * Consent Signal Service Implementation
 *
 * Emits consent signals to third-party platforms (GCM, TCF, WP Consent API, etc).
 *
 * @package ShahiLegalOpsSuite\Modules\Consent\Services
 * @since 1.0.0
 */

namespace ShahiLegalOpsSuite\Modules\Consent\Services;

use ShahiLegalOpsSuite\Modules\Consent\Interfaces\ConsentSignalServiceInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Consent Signal Service Class
 *
 * Emits consent status to GTM, Google Consent Mode, WP Consent API, etc.
 * Handles multi-platform signal coordination.
 *
 * @since 1.0.0
 */
class ConsentSignalService implements ConsentSignalServiceInterface {

	/**
	 * Default Google Consent Mode options.
	 *
	 * @var array
	 */
	private array $gcm_defaults = array(
		'analytics_storage'    => 'denied',
		'ad_storage'           => 'denied',
		'ad_user_data'         => 'denied',
		'ad_personalization'   => 'denied',
	);

	/**
	 * User region (e.g., 'EU', 'US-CA').
	 *
	 * @var string
	 */
	private string $region = 'DEFAULT';

	/**
	 * Constructor.
	 *
	 * @param string $region User region code (e.g., 'EU', 'US-CA'). Defaults to 'DEFAULT'.
	 */
	public function __construct( string $region = 'DEFAULT' ) {
		$this->region = $region;
	}

	/**
	 * Set the user region.
	 *
	 * @param string $region Region code (e.g., 'EU', 'US-CA').
	 *
	 * @return void
	 */
	public function set_region( string $region ): void {
		$this->region = $region;
	}

	/**
	 * Emit region-specific consent signals.
	 *
	 * For EU/UK (GDPR regions): Emits Google Consent Mode v2.
	 * For US-CA (CCPA): May emit additional CCPA-specific notices.
	 * For other regions: Emits appropriate regional signals.
	 *
	 * @param array $consents User consent categories.
	 * @param array $options  Optional signal configuration.
	 *
	 * @return array Emitted signals payload.
	 */
	public function emit_regional_signals( array $consents, array $options = array() ): array {
		$signals = array(
			'region' => $this->region,
			'signals' => array(),
		);

		// EU/UK regions: Emit Google Consent Mode v2.
		if ( in_array( $this->region, array( 'EU', 'UK' ), true ) ) {
			$signals['signals']['gcm_v2'] = $this->emit_google_consent_mode( $consents, $options );
		}

		// US-CA (CCPA): Different approach - no blocking by default.
		if ( $this->region === 'US-CA' ) {
			$signals['signals']['ccpa_notice'] = array(
				'region'      => 'US-CA',
				'notice_type' => 'do_not_sell_link',
				'applied'     => true,
			);
		}

		// Other regulated regions: Also emit GCM v2 if they want analytics signals.
		if ( in_array( $this->region, array( 'BR', 'AU', 'CA', 'ZA' ), true ) ) {
			$signals['signals']['gcm_v2'] = $this->emit_google_consent_mode( $consents, $options );
		}

		/**
		 * Filter regional signals before emitting.
		 *
		 * @param array $signals Signals to emit.
		 * @param string $region User region.
		 * @param array $consents User consent preferences.
		 *
		 * @return array Filtered signals.
		 */
		return apply_filters( 'complyflow_regional_signals', $signals, $this->region, $consents );
	}

	/**
	 * Emit Google Consent Mode v2 signal.
	 *
	 * Creates the gtag.config() call for Google Consent Mode v2.
	 * Maps consent categories to GCM consent types.
	 *
	 * @param array $consents User consent categories.
	 * @param array $options {
	 *     Optional. GCM configuration.
	 *
	 *     @type bool $analytics_storage   Map 'analytics' to analytics_storage (default: true).
	 *     @type bool $ad_storage          Map 'marketing' to ad_storage (default: true).
	 *     @type bool $ad_user_data        Set ad_user_data (default: true).
	 *     @type bool $ad_personalization  Set ad_personalization (default: true).
	 * }
	 *
	 * @return array Google Consent Mode v2 payload.
	 */
	public function emit_google_consent_mode( array $consents, array $options = array() ): array {
		$payload = $this->gcm_defaults;

		// Merge options.
		$options = wp_parse_args(
			$options,
			array(
				'analytics_storage'   => true,
				'ad_storage'          => true,
				'ad_user_data'        => true,
				'ad_personalization'  => true,
			)
		);

		// Map analytics consent to analytics_storage.
		if ( $options['analytics_storage'] ) {
			$payload['analytics_storage'] = ! empty( $consents['analytics'] ) ? 'granted' : 'denied';
		}

		// Map marketing consent to ad_storage.
		if ( $options['ad_storage'] ) {
			$payload['ad_storage'] = ! empty( $consents['marketing'] ) ? 'granted' : 'denied';
		}

		// Set ad_user_data (requires marketing + functional).
		if ( $options['ad_user_data'] ) {
			$payload['ad_user_data'] = ( ! empty( $consents['marketing'] ) && ! empty( $consents['functional'] ) ) ? 'granted' : 'denied';
		}

		// Set ad_personalization (requires marketing).
		if ( $options['ad_personalization'] ) {
			$payload['ad_personalization'] = ! empty( $consents['marketing'] ) ? 'granted' : 'denied';
		}

		return $payload;
	}

	/**
	 * Emit IAB TCF v2.2 API signal.
	 *
	 * Returns TCF-compatible consent object.
	 * PRO feature: Full TCF v2.2 with vendor list and purposes.
	 * MVP: Simplified consent state.
	 *
	 * @param array $consents User consent categories.
	 * @param array $purposes Optional. TCF purposes (PRO feature).
	 * @param array $vendors  Optional. TCF vendors (PRO feature).
	 *
	 * @return array TCF API payload.
	 */
	public function emit_tcf_signal( array $consents, array $purposes = array(), array $vendors = array() ): array {
		// MVP: Simplified TCF-like structure.
		$payload = array(
			'tcfapi' => array(
				'gdprApplies' => true,
				'cmpLoaded'   => true,
				'cmpStatus'   => 'stub', // 'stub', 'loading', 'loaded', 'error'.
			),
			'consents' => array(
				'necessary'  => true, // Always true.
				'analytics'  => ! empty( $consents['analytics'] ),
				'marketing'  => ! empty( $consents['marketing'] ),
				'functional' => ! empty( $consents['functional'] ),
			),
		);

		// PRO: Include purposes and vendors if provided.
		if ( ! empty( $purposes ) ) {
			$payload['purposes'] = $purposes;
		}

		if ( ! empty( $vendors ) ) {
			$payload['vendors'] = $vendors;
		}

		return $payload;
	}

	/**
	 * Emit GPP (__gpp) signal for US state laws.
	 *
	 * Returns GPP signal string for US Privacy, CCPA, CPRA, etc.
	 * MVP: Simplified string. PRO: Full GPP encoding.
	 *
	 * @param array $consents User consent categories.
	 * @param array $options  {
	 *     Optional. GPP configuration.
	 *
	 *     @type string $region US state region (ca, va, co, ct, etc).
	 *     @type bool   $opt_out Opt-out intent (CCPA).
	 * }
	 *
	 * @return string GPP signal string.
	 */
	public function emit_gpp_signal( array $consents, array $options = array() ): string {
		// MVP: Return simplified US Privacy signal.
		// Format: 1 (version) - US Privacy Disclosed (1) - Not Opt Out (N) - Not Opt Out Sale (N).
		$opt_out = ! empty( $options['opt_out'] ) || empty( $consents['marketing'] );

		// US Privacy String format: 1UYY (version-disclosed-optout-optsale).
		if ( $opt_out ) {
			return '1---';  // Opt out of all.
		} else {
			return '1NYN'; // Not opted out, sale allowed.
		}
	}

	/**
	 * Emit WP Consent API actions.
	 *
	 * Calls do_action() for each consent category to fire WordPress hooks.
	 * Other plugins listen for 'set_cookie_consent_{category}' actions.
	 *
	 * @param array $consents User consent categories.
	 *
	 * @return void
	 */
	public function emit_wp_consent_api( array $consents ): void {
		foreach ( $consents as $category => $granted ) {
			/**
			 * Fire WordPress Consent API hook.
			 *
			 * @param bool $granted Consent granted for category.
			 */
			do_action( 'set_cookie_consent', $category, $granted );
			do_action( "set_cookie_consent_{$category}", $granted );
		}
	}

	/**
	 * Get GTM dataLayer event for consent update.
	 *
	 * Returns the dataLayer event object to push to GTM when consent changes.
	 *
	 * @param array $consents    User consent categories.
	 * @param array $gtm_options Optional. GTM configuration.
	 *
	 * @return array DataLayer event object.
	 */
	public function get_datalayer_event( array $consents, array $gtm_options = array() ): array {
		// Map consent categories to standard dataLayer properties.
		$event = array(
			'event'                 => 'consent_update',
			'event_category'        => 'consent',
			'event_label'           => 'user_consent_update',
			'consent_analytics'     => ! empty( $consents['analytics'] ),
			'consent_marketing'     => ! empty( $consents['marketing'] ),
			'consent_functional'    => ! empty( $consents['functional'] ),
			'consent_necessary'     => true, // Always true.
			'consent_all_granted'   => $this->all_consents_granted( $consents ),
			'consent_all_rejected'  => $this->all_consents_rejected( $consents ),
			'consent_timestamp'     => current_time( 'timestamp' ),
		);

		// Include GCM v2 state if requested.
		if ( ! empty( $gtm_options['include_gcm'] ) ) {
			$event['consentUpdate'] = $this->emit_google_consent_mode( $consents );
		}

		return $event;
	}

	/**
	 * Check if all non-necessary consents are granted.
	 *
	 * @param array $consents Consent categories.
	 *
	 * @return bool True if analytics, marketing, functional all granted.
	 */
	private function all_consents_granted( array $consents ): bool {
		return ! empty( $consents['analytics'] ) && ! empty( $consents['marketing'] ) && ! empty( $consents['functional'] );
	}

	/**
	 * Check if all consents are rejected (except necessary).
	 *
	 * @param array $consents Consent categories.
	 *
	 * @return bool True if all rejected.
	 */
	private function all_consents_rejected( array $consents ): bool {
		return empty( $consents['analytics'] ) && empty( $consents['marketing'] ) && empty( $consents['functional'] );
	}

	/**
	 * Get JavaScript snippet to emit GCM signal.
	 *
	 * Returns the actual JavaScript code to inject into page.
	 * Used by frontend to update gtag() with consent state.
	 *
	 * @param array $consents User consent categories.
	 *
	 * @return string JavaScript code.
	 */
	public function get_gcm_javascript( array $consents ): string {
		$payload = $this->emit_google_consent_mode( $consents );

		return sprintf(
			"gtag('consent', 'update', %s);",
			wp_json_encode( $payload )
		);
	}

	/**
	 * Get JavaScript snippet to emit GTM dataLayer event.
	 *
	 * Returns JavaScript to push consent event to dataLayer.
	 *
	 * @param array $consents User consent categories.
	 *
	 * @return string JavaScript code.
	 */
	public function get_gtm_javascript( array $consents ): string {
		$event = $this->get_datalayer_event( $consents, array( 'include_gcm' => true ) );

		return sprintf(
			"window.dataLayer = window.dataLayer || []; window.dataLayer.push(%s);",
			wp_json_encode( $event )
		);
	}

	/**
	 * Get all signals as JavaScript injection code.
	 *
	 * Returns complete JavaScript snippet to emit all signals.
	 * Safe to inject into page <head> or <footer>.
	 *
	 * @param array $consents User consent categories.
	 *
	 * @return string Complete JavaScript block.
	 */
	public function get_all_signals_javascript( array $consents ): string {
		$js = "/* Consent Signals Initialization */\n";
		$js .= "var complyflowConsents = " . wp_json_encode( $consents ) . ";\n\n";

		// GTM dataLayer push.
		$js .= "/* GTM DataLayer Event */\n";
		$js .= $this->get_gtm_javascript( $consents ) . "\n\n";

		// WP Consent API.
		$js .= "/* WordPress Consent API */\n";
		foreach ( $consents as $category => $granted ) {
			$value = $granted ? 'true' : 'false';
			$js    .= "document.dispatchEvent(new CustomEvent('wp_consent_category_set', { detail: { category: '{$category}', granted: {$value} } }));\n";
		}

		// GCM if gtag present.
		$js .= "\nif (typeof gtag !== 'undefined') {\n";
		$js .= "  " . $this->get_gcm_javascript( $consents ) . "\n";
		$js .= "}\n";

		return $js;
	}
}
