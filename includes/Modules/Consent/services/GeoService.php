<?php
/**
 * Geo Service Implementation
 *
 * Handles IP geolocation, region detection, and regional preset loading.
 *
 * @package ShahiLegalOpsSuite\Modules\Consent\Services
 * @since 1.0.0
 */

namespace ShahiLegalOpsSuite\Modules\Consent\Services;

use ShahiLegalOpsSuite\Modules\Consent\Interfaces\GeoServiceInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Geo Service Class
 *
 * Detects user region from IP address and loads regional presets.
 *
 * @since 1.0.0
 */
class GeoService implements GeoServiceInterface {

	/**
	 * Regional presets configuration.
	 *
	 * @var array
	 */
	private array $regional_presets = array();

	/**
	 * Country-to-region mapping.
	 *
	 * @var array
	 */
	private array $country_region_map = array();

	/**
	 * IP geolocation cache TTL (in seconds).
	 *
	 * @var int
	 */
	private int $cache_ttl = 3600;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->load_regional_presets();
		$this->build_country_region_map();
	}

	/**
	 * Load regional presets from configuration file.
	 *
	 * @return void
	 */
	private function load_regional_presets(): void {
		$presets_file = dirname( __FILE__ ) . '/../config/regional-presets.php';

		if ( file_exists( $presets_file ) ) {
			$this->regional_presets = require $presets_file;
		}
	}

	/**
	 * Build country-to-region mapping.
	 *
	 * @return void
	 */
	private function build_country_region_map(): void {
		foreach ( $this->regional_presets as $region => $config ) {
			if ( $region === 'DEFAULT' ) {
				continue;
			}

			$countries = $config['countries'] ?? array();
			foreach ( $countries as $country ) {
				$this->country_region_map[ $country ] = $region;
			}
		}
	}

	/**
	 * Detect user region from IP address.
	 *
	 * @param string $ip User IP address (defaults to REMOTE_ADDR).
	 *
	 * @return array {
	 *     Region detection result.
	 *
	 *     @type string $region          Region code: 'EU', 'UK', 'US-CA', etc.
	 *     @type string $country         ISO 3166-1 alpha-2 country code.
	 *     @type string $mode            Compliance mode: 'gdpr', 'ccpa', etc.
	 *     @type bool   $requires_consent Prior-consent blocking required.
	 *     @type int    $cached          Cache lifetime in seconds.
	 * }
	 */
	public function detect_region( string $ip = '' ): array {
		if ( empty( $ip ) ) {
			$ip = $this->get_user_ip();
		}

		// Check cache first.
		$cache_key = 'complyflow_geo_' . hash( 'sha256', $ip );
		$cached     = wp_cache_get( $cache_key );

		if ( ! empty( $cached ) ) {
			return $cached;
		}

		// Detect region from IP.
		$country = $this->geoip_lookup( $ip );
		$region  = $this->get_region_for_country( $country );

		$result = array(
			'region'            => $region,
			'country'           => $country,
			'mode'              => $this->regional_presets[ $region ]['mode'] ?? 'default',
			'requires_consent'  => $this->is_regulated_region( $region ),
			'cached'            => $this->cache_ttl,
		);

		// Cache the result.
		wp_cache_set( $cache_key, $result, '', $this->cache_ttl );

		return $result;
	}

	/**
	 * Get regional preset configuration.
	 *
	 * @param string $region Region code (e.g., 'EU').
	 *
	 * @return array Regional configuration.
	 */
	public function get_region_config( string $region ): array {
		// Return region config or default.
		$region = strtoupper( $region );

		if ( ! isset( $this->regional_presets[ $region ] ) ) {
			$region = 'DEFAULT';
		}

		return $this->regional_presets[ $region ];
	}

	/**
	 * Check if region requires prior-consent blocking.
	 *
	 * @param string $region Region code.
	 *
	 * @return bool True if blocking required.
	 */
	public function is_regulated_region( string $region ): bool {
		$config = $this->get_region_config( $region );
		return ! empty( $config['requires_consent'] );
	}

	/**
	 * Get list of supported regions.
	 *
	 * @return array Region codes.
	 */
	public function get_supported_regions(): array {
		return array_keys( $this->regional_presets );
	}

	/**
	 * Get country-to-region mapping.
	 *
	 * @return array Mapping array.
	 */
	public function get_country_region_mapping(): array {
		return $this->country_region_map;
	}

	/**
	 * Map country code to region.
	 *
	 * @param string $country ISO 3166-1 alpha-2 country code.
	 *
	 * @return string Region code.
	 */
	public function get_region_for_country( string $country ): string {
		$country = strtoupper( $country );

		if ( isset( $this->country_region_map[ $country ] ) ) {
			return $this->country_region_map[ $country ];
		}

		// Default to 'DEFAULT' for unknown regions.
		return 'DEFAULT';
	}

	/**
	 * Get all countries in a region.
	 *
	 * @param string $region Region code.
	 *
	 * @return array ISO country codes.
	 */
	public function get_countries_for_region( string $region ): array {
		$config = $this->get_region_config( $region );
		return $config['countries'] ?? array();
	}

	/**
	 * Get user IP address.
	 *
	 * @return string User IP address.
	 */
	private function get_user_ip(): string {
		// Check for shared internet (proxy).
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		}

		// Check for forwarded IPs.
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			return trim( $ips[0] );
		}

		// Remote address.
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	}

	/**
	 * Lookup country from IP address.
	 *
	 * Tries multiple methods:
	 * 1. MaxMind GeoIP2 (if available)
	 * 2. Free IP geolocation API
	 * 3. Fallback to 'DEFAULT'
	 *
	 * @param string $ip IP address to lookup.
	 *
	 * @return string ISO 3166-1 alpha-2 country code (e.g., 'DE', 'US', 'BR').
	 */
	private function geoip_lookup( string $ip ): string {
		/**
		 * Filter to override geolocation country detection.
		 *
		 * Allows third-party plugins to provide their own geolocation method.
		 *
		 * @param string $country Detected country code (initially empty).
		 * @param string $ip      IP address being looked up.
		 *
		 * @return string ISO country code or empty string to continue default detection.
		 */
		$country = apply_filters( 'complyflow_geoip_lookup', '', $ip );

		if ( ! empty( $country ) ) {
			return strtoupper( $country );
		}

		// Try MaxMind (if installed via composer).
		if ( function_exists( '\\GeoIp2\\Database\\Reader' ) ) {
			$country = $this->geoip_maxmind( $ip );
			if ( ! empty( $country ) ) {
				return $country;
			}
		}

		// Try free IP API.
		$country = $this->geoip_api( $ip );
		if ( ! empty( $country ) ) {
			return $country;
		}

		// Fallback: return empty (will default to 'DEFAULT' region).
		return '';
	}

	/**
	 * Lookup country using MaxMind GeoIP2.
	 *
	 * @param string $ip IP address.
	 *
	 * @return string Country code or empty.
	 */
	private function geoip_maxmind( string $ip ): string {
		try {
			// Check for MaxMind database.
			$db_path = WP_CONTENT_DIR . '/geoip/GeoLite2-Country.mmdb';

			if ( ! file_exists( $db_path ) ) {
				return '';
			}

			// Load MaxMind reader.
			$reader  = new \GeoIp2\Database\Reader( $db_path );
			$record  = $reader->country( $ip );
			$country = $record->country->isoCode;

			return strtoupper( $country ?? '' );
		} catch ( \Exception $e ) {
			// MaxMind lookup failed, continue to API.
			return '';
		}
	}

	/**
	 * Lookup country using free IP geolocation API.
	 *
	 * Uses ip-api.com (free, no key required for reasonable usage).
	 *
	 * @param string $ip IP address.
	 *
	 * @return string Country code or empty.
	 */
	private function geoip_api( string $ip ): string {
		// Bail if ip-api.com is blocked or cURL not available.
		if ( ! function_exists( 'wp_remote_get' ) ) {
			return '';
		}

		// Call ip-api.com.
		$api_url = 'http://ip-api.com/php/' . urlencode( $ip );

		$response = wp_remote_get( $api_url, array( 'timeout' => 5 ) );

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$body = wp_remote_retrieve_body( $response );
		$data = unserialize( $body ); // phpcs:ignore WordPress.PHP.DiscouragedFunctions.serialize_unserialize

		if ( ! is_array( $data ) || empty( $data['countryCode'] ) ) {
			return '';
		}

		return strtoupper( $data['countryCode'] );
	}
}
