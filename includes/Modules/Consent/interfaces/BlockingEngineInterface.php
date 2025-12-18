<?php
/**
 * Blocking Engine Interface
 *
 * @package ShahiLegalOpsSuite\Modules\Consent\Interfaces
 * @since 1.0.0
 */

namespace ShahiLegalOpsSuite\Modules\Consent\Interfaces;

/**
 * Interface BlockingEngineInterface
 *
 * Defines contract for consent-based script and iframe blocking.
 */
interface BlockingEngineInterface {

	/**
	 * Register a blocking rule for a service.
	 *
	 * @param array $rule {
	 *     @type string $id       Rule ID.
	 *     @type string $type     Type: 'external_script', 'inline_script', 'iframe', 'pixel'.
	 *     @type string $pattern  URL/pattern to match (substring or regex).
	 *     @type string $category Consent category required to unblock.
	 *     @type string $action   'block_until_consent' or 'replace_with_placeholder'.
	 * }
	 *
	 * @return bool True on success.
	 */
	public function register_blocking_rule( array $rule ): bool;

	/**
	 * Get all active blocking rules.
	 *
	 * @return array Array of rule definitions.
	 */
	public function get_blocking_rules(): array;

	/**
	 * Determine if a URL matches any blocking rules.
	 *
	 * @param string $url      URL to test.
	 * @param array  $consents Current consent categories (keyed by category).
	 *
	 * @return array|null Matching rule or null if should be allowed.
	 */
	public function should_block( string $url, array $consents ): ?array;

	/**
	 * Enqueue a blocked script for later execution.
	 *
	 * @param string $script_tag HTML script tag.
	 * @param array  $consents   Current consent state.
	 *
	 * @return bool True if queued.
	 */
	public function queue_blocked_script( string $script_tag, array $consents ): bool;

	/**
	 * Replay queued scripts after consent change.
	 *
	 * @param array $consents Updated consent state.
	 *
	 * @return int Number of scripts replayed.
	 */
	public function replay_queued_scripts( array $consents ): int;

	/**
	 * Generate placeholder HTML for blocked iFrame.
	 *
	 * @param string $url        Original iframe src.
	 * @param string $service_id Service ID (e.g., 'youtube').
	 *
	 * @return string Placeholder HTML.
	 */
	public function get_iframe_placeholder( string $url, string $service_id = '' ): string;
}
