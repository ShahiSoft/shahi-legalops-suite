<?php
/**
 * Geo Service Interface
 *
 * Defines contract for IP geolocation and regional detection.
 *
 * @package ShahiLegalOpsSuite\Modules\Consent\Interfaces
 * @since 1.0.0
 */

namespace ShahiLegalOpsSuite\Modules\Consent\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Geo Service Interface
 *
 * @since 1.0.0
 */
interface GeoServiceInterface {

	/**
	 * Detect user region from IP address.
	 *
	 * @param string $ip User IP address (defaults to REMOTE_ADDR).
	 *
	 * @return array {
	 *     Region detection result.
	 *
	 *     @type string $region       Region code: 'EU', 'UK', 'US-CA', 'BR', 'AU', etc.
	 *     @type string $country      ISO 3166-1 alpha-2 country code: 'DE', 'US', 'BR', etc.
	 *     @type string $mode         Compliance mode: 'gdpr', 'ccpa', 'lgpd', etc.
	 *     @type bool   $requires_consent True if prior-consent blocking required.
	 *     @type int    $cached        Cache lifetime in seconds.
	 * }
	 */
	public function detect_region( string $ip = '' ): array;

	/**
	 * Get regional preset configuration.
	 *
	 * Returns all region-specific settings including blocking rules,
	 * banner variant, retention policy, etc.
	 *
	 * @param string $region Region code: 'EU', 'UK', 'US-CA', 'BR', 'AU'.
	 *
	 * @return array {
	 *     Regional configuration.
	 *
	 *     @type string   $mode                  Compliance mode.
	 *     @type array    $countries             ISO country codes in this region.
	 *     @type bool     $requires_consent      Prior-consent blocking required.
	 *     @type string   $banner_variant        Banner variant ID (e.g., 'gdpr', 'ccpa').
	 *     @type array    $blocking_rules        Rule IDs to enforce in this region.
	 *     @type int      $retention_days        Consent log retention period.
	 *     @type string   $retention_policy      'delete' or 'anonymize_after_Xmo'.
	 *     @type bool     $anonymize_ip          Anonymize IP in logs.
	 * }
	 */
	public function get_region_config( string $region ): array;

	/**
	 * Check if region requires prior-consent blocking.
	 *
	 * @param string $region Region code.
	 *
	 * @return bool True if blocking required before user consent.
	 */
	public function is_regulated_region( string $region ): bool;

	/**
	 * Get list of supported regions.
	 *
	 * @return array Array of region codes: ['EU', 'UK', 'US-CA', 'BR', 'AU'].
	 */
	public function get_supported_regions(): array;

	/**
	 * Get country-to-region mapping.
	 *
	 * @return array Associative array: ['DE' => 'EU', 'US' => 'US-CA', ...].
	 */
	public function get_country_region_mapping(): array;

	/**
	 * Map country code to region.
	 *
	 * @param string $country ISO 3166-1 alpha-2 country code (e.g., 'DE').
	 *
	 * @return string Region code, or 'default' if not mapped.
	 */
	public function get_region_for_country( string $country ): string;

	/**
	 * Get all countries in a region.
	 *
	 * @param string $region Region code (e.g., 'EU').
	 *
	 * @return array ISO country codes in the region.
	 */
	public function get_countries_for_region( string $region ): array;
}
