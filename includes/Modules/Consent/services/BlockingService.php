<?php
/**
 * Blocking Service Implementation
 *
 * Handles consent-based script and iframe blocking.
 *
 * @package ShahiLegalOpsSuite\Modules\Consent\Services
 * @since 1.0.0
 */

namespace ShahiLegalOpsSuite\Modules\Consent\Services;

use ShahiLegalOpsSuite\Modules\Consent\Interfaces\BlockingEngineInterface;
use ShahiLegalOpsSuite\Modules\Consent\Repositories\ConsentRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blocking Service Class
 *
 * Manages script and iframe blocking based on consent preferences.
 * Queues blocked scripts for replay when consent is granted.
 *
 * @since 1.0.0
 */
class BlockingService implements BlockingEngineInterface {

	/**
	 * Consent repository instance.
	 *
	 * @var ConsentRepository
	 */
	private ConsentRepository $repository;

	/**
	 * User region (e.g., 'EU', 'US-CA').
	 *
	 * @var string
	 */
	private string $region = 'DEFAULT';

	/**
	 * Blocking rules registry.
	 *
	 * @var array
	 */
	private array $blocking_rules = array();

	/**
	 * Queued scripts (blocked and pending consent).
	 *
	 * @var array
	 */
	private array $queued_scripts = array();

	/**
	 * Constructor.
	 *
	 * @param ConsentRepository $repository Consent repository instance.
	 * @param string            $region     User region (e.g., 'EU', 'US-CA'). Defaults to 'DEFAULT'.
	 */
	public function __construct( ConsentRepository $repository, string $region = 'DEFAULT' ) {
		$this->repository = $repository;
		$this->region     = $region;
	}

	/**
	 * Register a blocking rule for a service.
	 *
	 * @param array $rule {
	 *     Rule definition.
	 *
	 *     @type string $id       Rule ID (unique identifier).
	 *     @type string $type     Type: 'external_script', 'inline_script', 'iframe', 'pixel'.
	 *     @type string $pattern  URL/pattern to match (substring or regex).
	 *     @type string $category Consent category required to unblock.
	 *     @type string $action   'block_until_consent' or 'replace_with_placeholder'.
	 * }
	 *
	 * @return bool True on success, false if rule already exists.
	 */
	public function register_blocking_rule( array $rule ): bool {
		if ( empty( $rule['id'] ) || isset( $this->blocking_rules[ $rule['id'] ] ) ) {
			return false;
		}

		// Validate required fields.
		$required = array( 'id', 'type', 'pattern', 'category', 'action' );
		foreach ( $required as $field ) {
			if ( empty( $rule[ $field ] ) ) {
				return false;
			}
		}

		// Validate type and action.
		$valid_types   = array( 'external_script', 'inline_script', 'iframe', 'pixel' );
		$valid_actions = array( 'block_until_consent', 'replace_with_placeholder' );

		if ( ! in_array( $rule['type'], $valid_types, true ) || ! in_array( $rule['action'], $valid_actions, true ) ) {
			return false;
		}

		$this->blocking_rules[ $rule['id'] ] = $rule;
		return true;
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
	 * Load default blocking rules for the current region.
	 *
	 * Loads region-specific blocking rules from regional presets.
	 * For regulated regions (EU, UK, etc.), enforces standard blocking rules.
	 * For non-regulated regions (US-CA, DEFAULT), applies no blocking.
	 *
	 * @return void
	 */
	public function load_regional_rules(): void {
		// Load regional presets.
		$presets_file = dirname( __FILE__ ) . '/../config/regional-presets.php';
		if ( ! file_exists( $presets_file ) ) {
			return;
		}

		$presets = require $presets_file;
		$config  = $presets[ $this->region ] ?? $presets['DEFAULT'];

		// Get blocking rules for this region.
		$rule_ids = $config['blocking_rules'] ?? array();

		// Register default blocking rules based on rule IDs.
		foreach ( $rule_ids as $rule_id ) {
			$rule = $this->get_default_blocking_rule( $rule_id );
			if ( ! empty( $rule ) ) {
				$this->register_blocking_rule( $rule );
			}
		}
	}

	/**
	 * Get a default blocking rule by ID.
	 *
	 * @param string $rule_id Rule ID (e.g., 'google-analytics-4').
	 *
	 * @return array Rule definition or empty array.
	 */
	private function get_default_blocking_rule( string $rule_id ): array {
		$rules = array(
			'google-analytics-4'        => array(
				'id'       => 'google-analytics-4',
				'type'     => 'external_script',
				'pattern'  => 'gtag|googletagmanager|google-analytics',
				'category' => 'analytics',
				'action'   => 'block_until_consent',
			),
			'google-analytics-universal' => array(
				'id'       => 'google-analytics-universal',
				'type'     => 'external_script',
				'pattern'  => 'google-analytics.com|ga.js',
				'category' => 'analytics',
				'action'   => 'block_until_consent',
			),
			'facebook-pixel'            => array(
				'id'       => 'facebook-pixel',
				'type'     => 'external_script',
				'pattern'  => 'facebook.com/en_US/fbevents|fbevents.js',
				'category' => 'marketing',
				'action'   => 'block_until_consent',
			),
			'linkedin-insight'          => array(
				'id'       => 'linkedin-insight',
				'type'     => 'external_script',
				'pattern'  => 'linkedin.com/px|snap.licdn.com',
				'category' => 'marketing',
				'action'   => 'block_until_consent',
			),
			'twitter-pixel'             => array(
				'id'       => 'twitter-pixel',
				'type'     => 'external_script',
				'pattern'  => 'analytics.twitter.com|t.co|platform.twitter.com',
				'category' => 'marketing',
				'action'   => 'block_until_consent',
			),
			'hotjar'                    => array(
				'id'       => 'hotjar',
				'type'     => 'external_script',
				'pattern'  => 'hotjar.com|hjcdn.com',
				'category' => 'analytics',
				'action'   => 'block_until_consent',
			),
			'segment'                   => array(
				'id'       => 'segment',
				'type'     => 'external_script',
				'pattern'  => 'segment.com|cdn.segment.com',
				'category' => 'marketing',
				'action'   => 'block_until_consent',
			),
		);

		return $rules[ $rule_id ] ?? array();
	}

	/**
	 *
	 * @return array Array of rule definitions.
	 */
	public function get_blocking_rules(): array {
		return $this->blocking_rules;
	}

	/**
	 * Determine if a URL matches any blocking rules.
	 *
	 * Checks if URL matches any rule pattern and if consent is missing.
	 *
	 * @param string $url      URL to test.
	 * @param array  $consents Current consent categories (e.g., ['analytics' => true, 'marketing' => false]).
	 *
	 * @return array|null Matching rule if should be blocked, null if allowed.
	 */
	public function should_block( string $url, array $consents ): ?array {
		if ( empty( $url ) || empty( $this->blocking_rules ) ) {
			return null;
		}

		foreach ( $this->blocking_rules as $rule ) {
			if ( $this->pattern_matches( $url, $rule['pattern'] ) ) {
				// Rule matches. Check if consent is granted for required category.
				$required_category = $rule['category'];
				$has_consent       = ! empty( $consents[ $required_category ] );

				if ( ! $has_consent ) {
					return $rule;
				}
			}
		}

		return null;
	}

	/**
	 * Check if a URL matches a pattern (substring or regex).
	 *
	 * @param string $url     URL to test.
	 * @param string $pattern Pattern (substring or regex starting with /).
	 *
	 * @return bool True if matches.
	 */
	private function pattern_matches( string $url, string $pattern ): bool {
		// If pattern starts with /, treat as regex.
		if ( str_starts_with( $pattern, '/' ) ) {
			return (bool) preg_match( $pattern, $url );
		}

		// Otherwise, simple substring match.
		return str_contains( $url, $pattern );
	}

	/**
	 * Enqueue a blocked script for later execution.
	 *
	 * Stores the script tag so it can be replayed when consent is granted.
	 *
	 * @param string $script_tag HTML script tag.
	 * @param array  $consents   Current consent state.
	 *
	 * @return bool True if queued successfully.
	 */
	public function queue_blocked_script( string $script_tag, array $consents ): bool {
		if ( empty( $script_tag ) ) {
			return false;
		}

		// Extract URL from script tag.
		$url = $this->extract_url_from_script( $script_tag );
		if ( ! $url ) {
			return false;
		}

		// Check if should block.
		$rule = $this->should_block( $url, $consents );
		if ( ! $rule ) {
			return false;
		}

		// Store for replay.
		$this->queued_scripts[] = array(
			'script_tag'      => $script_tag,
			'url'             => $url,
			'rule_id'         => $rule['id'],
			'required_consent' => $rule['category'],
			'timestamp'       => current_time( 'mysql' ),
		);

		return true;
	}

	/**
	 * Extract URL from script tag.
	 *
	 * @param string $script_tag HTML script tag.
	 *
	 * @return string|null Extracted URL or null.
	 */
	private function extract_url_from_script( string $script_tag ): ?string {
		// Match src="..." attribute.
		if ( preg_match( '/src=["\']([^"\']+)["\']/', $script_tag, $matches ) ) {
			return $matches[1];
		}

		return null;
	}

	/**
	 * Replay queued scripts that now have consent.
	 *
	 * Outputs HTML to inject previously blocked scripts into the page.
	 * Called when user grants consent and page needs to update.
	 *
	 * @param array $consents Updated consent state.
	 *
	 * @return void
	 */
	public function replay_queued_scripts( array $consents ): void {
		if ( empty( $this->queued_scripts ) ) {
			return;
		}

		// Iterate queued scripts and replay if consent now granted.
		foreach ( $this->queued_scripts as $queued ) {
			$required = $queued['required_consent'];
			if ( ! empty( $consents[ $required ] ) ) {
				// Consent granted, inject script.
				echo wp_kses_post( $queued['script_tag'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				unset( $this->queued_scripts[ array_key_first( $this->queued_scripts ) ] );
			}
		}
	}

	/**
	 * Get iframe placeholder HTML.
	 *
	 * Returns HTML to replace blocked iframes with placeholder.
	 * Shows a message prompting user to enable consent for embeds.
	 *
	 * @param array $rule      Blocking rule for the iframe.
	 * @param array $consents  Current consent state.
	 *
	 * @return string HTML placeholder.
	 */
	public function get_iframe_placeholder( array $rule, array $consents ): string {
		$required_category = $rule['category'];
		$category_label    = ucwords( str_replace( '_', ' ', $required_category ) );

		$html = sprintf(
			'<div class="complyflow-iframe-placeholder" data-rule="%s" data-category="%s" style="background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 8px; padding: 24px; text-align: center; min-height: 400px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
				<div style="font-size: 48px; margin-bottom: 16px;">🔒</div>
				<h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Content Blocked</h3>
				<p style="margin: 0 0 16px 0; font-size: 14px; color: #6b7280;">This content requires %s consent.</p>
				<button class="complyflow-enable-consent-btn" data-category="%s" style="background: #3b82f6; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;">Enable %s</button>
			</div>',
			esc_attr( $rule['id'] ),
			esc_attr( $required_category ),
			esc_html( $category_label ),
			esc_attr( $required_category ),
			esc_html( $category_label )
		);

		return $html;
	}

	/**
	 * Get queued scripts count.
	 *
	 * @return int Number of queued scripts.
	 */
	public function get_queued_scripts_count(): int {
		return count( $this->queued_scripts );
	}

	/**
	 * Get all queued scripts.
	 *
	 * @return array Array of queued script definitions.
	 */
	public function get_queued_scripts(): array {
		return $this->queued_scripts;
	}

	/**
	 * Clear all queued scripts.
	 *
	 * @return void
	 */
	public function clear_queued_scripts(): void {
		$this->queued_scripts = array();
	}
}
