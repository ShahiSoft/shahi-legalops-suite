<?php
/**
 * Consent Signal Service Interface
 *
 * @package ShahiLegalOpsSuite\Modules\Consent\Interfaces
 * @since 1.0.0
 */

namespace ShahiLegalOpsSuite\Modules\Consent\Interfaces;

/**
 * Interface ConsentSignalServiceInterface
 *
 * Defines contract for emitting consent signals to third-party platforms.
 */
interface ConsentSignalServiceInterface {

	/**
	 * Emit Google Consent Mode v2 signal.
	 *
	 * @param array $consents User consent categories.
	 * @param array $options  {
	 *     @type bool $analytics_storage Set analytics_storage.
	 *     @type bool $ad_storage        Set ad_storage.
	 *     @type bool $ad_user_data      Set ad_user_data.
	 *     @type bool $ad_personalization Set ad_personalization.
	 * }
	 *
	 * @return array Google Consent Mode payload.
	 */
	public function emit_google_consent_mode( array $consents, array $options = array() ): array;

	/**
	 * Emit IAB TCF v2.2 API signal.
	 *
	 * @param array $consents    User consent categories.
	 * @param array $purposes    TCF purposes (PRO feature).
	 * @param array $vendors     TCF vendors (PRO feature).
	 *
	 * @return array TCF API payload.
	 */
	public function emit_tcf_signal( array $consents, array $purposes = array(), array $vendors = array() ): array;

	/**
	 * Emit GPP (__gpp) signal for US state laws.
	 *
	 * @param array $consents User consent categories.
	 * @param array $options  Regional options.
	 *
	 * @return string GPP string.
	 */
	public function emit_gpp_signal( array $consents, array $options = array() ): string;

	/**
	 * Emit WP Consent API action.
	 *
	 * @param array $consents User consent categories.
	 *
	 * @return void
	 */
	public function emit_wp_consent_api( array $consents ): void;

	/**
	 * Get dataLayer event for GTM.
	 *
	 * @param array $consents User consent categories.
	 * @param string $event    Event type ('consent_init', 'consent_update', etc.).
	 *
	 * @return array DataLayer event object.
	 */
	public function get_datalayer_event( array $consents, string $event = 'consent_init' ): array;
}
