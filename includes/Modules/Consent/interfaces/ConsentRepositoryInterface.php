<?php
/**
 * Consent Repository Interface
 *
 * @package ShahiLegalOpsSuite\Modules\Consent\Interfaces
 * @since 1.0.0
 */

namespace ShahiLegalOpsSuite\Modules\Consent\Interfaces;

/**
 * Interface ConsentRepositoryInterface
 *
 * Defines contract for consent data persistence.
 */
interface ConsentRepositoryInterface {

	/**
	 * Save user consent preferences.
	 *
	 * @param array $preferences {
	 *     @type array  $categories    Consent categories with boolean values.
	 *     @type string $banner_version Banner version string.
	 *     @type string $region         Region code (e.g., 'EU', 'US-CA').
	 *     @type int    $user_id        Optional user ID.
	 *     @type string $session_id     Session identifier.
	 * }
	 *
	 * @return int|false Consent log ID on success, false on failure.
	 */
	public function save_consent( array $preferences );

	/**
	 * Get current consent status for user/session.
	 *
	 * @param string $session_id Session identifier.
	 * @param int    $user_id    Optional user ID.
	 *
	 * @return array|null Consent categories and metadata, or null if not found.
	 */
	public function get_consent_status( string $session_id, int $user_id = 0 );

	/**
	 * Revoke user consent.
	 *
	 * @param string $session_id Session identifier.
	 * @param array  $categories Optional specific categories to revoke; empty = all.
	 *
	 * @return bool True on success.
	 */
	public function withdraw_consent( string $session_id, array $categories = array() ): bool;

	/**
	 * Retrieve consent logs with filtering.
	 *
	 * @param array $args {
	 *     @type string $region      Filter by region.
	 *     @type int    $user_id     Filter by user ID.
	 *     @type string $start_date  Filter by start date (YYYY-MM-DD).
	 *     @type string $end_date    Filter by end date (YYYY-MM-DD).
	 *     @type int    $per_page    Results per page.
	 *     @type int    $page        Page number (1-indexed).
	 *     @type string $orderby     Order by field (timestamp, region, etc.).
	 *     @type string $order       ASC or DESC.
	 * }
	 *
	 * @return array Array of consent log records.
	 */
	public function get_logs( array $args = array() ): array;

	/**
	 * Export consent logs.
	 *
	 * @param string $format Export format ('csv', 'json').
	 * @param array  $filters Optional filter arguments (same as get_logs).
	 *
	 * @return string Formatted export data.
	 */
	public function export_logs( string $format = 'csv', array $filters = array() ): string;

	/**
	 * Get total count of consent logs.
	 *
	 * @param array $filters Optional filter arguments.
	 *
	 * @return int Total count.
	 */
	public function count_logs( array $filters = array() ): int;

	/**
	 * Delete old consent logs based on retention policy.
	 *
	 * @param int $retention_days Days to retain.
	 *
	 * @return int Number of deleted records.
	 */
	public function cleanup_expired_logs( int $retention_days ): int;
}
