<?php
/**
 * Consent Repository
 *
 * Handles database operations for consent records (GDPR/CCPA/LGPD compliance).
 *
 * @package     ShahiLegalopsSuite
 * @subpackage  Database\Repositories
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalopsSuite\Database\Repositories;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Consent_Repository Class
 *
 * Concrete repository implementation for consent table operations.
 *
 * @since 3.0.1
 */
class Consent_Repository extends Base_Repository {

	/**
	 * Get table name
	 *
	 * @since 3.0.1
	 * @return string Table name without prefix
	 */
	protected function get_table_name(): string {
		return 'slos_consent';
	}

	/**
	 * Find consents by user ID
	 *
	 * @since 3.0.1
	 * @param int   $user_id User ID
	 * @param array $args    Query arguments
	 * @return array Array of consent records
	 */
	public function find_by_user( int $user_id, array $args = array() ): array {
		return $this->find_by( 'user_id', $user_id, $args );
	}

	/**
	 * Find consents by type
	 *
	 * @since 3.0.1
	 * @param string $type Type (necessary, functional, analytics, marketing, personalization)
	 * @param array  $args Query arguments
	 * @return array Array of consent records
	 */
	public function find_by_type( string $type, array $args = array() ): array {
		return $this->find_by( 'type', $type, $args );
	}

	/**
	 * Find consents by status
	 *
	 * @since 3.0.1
	 * @param string $status Status (pending, accepted, rejected, withdrawn)
	 * @param array  $args   Query arguments
	 * @return array Array of consent records
	 */
	public function find_by_status( string $status, array $args = array() ): array {
		return $this->find_by( 'status', $status, $args );
	}

	/**
	 * Get active consents for user
	 *
	 * @since 3.0.1
	 * @param int $user_id User ID
	 * @return array Array of accepted consent records
	 */
	public function get_active_consents( int $user_id ): array {
		return $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE user_id = %d AND status = 'accepted' ORDER BY created_at DESC",
				$user_id
			)
		);
	}

	/**
	 * Check if user has active consent for specific type
	 *
	 * @since 3.0.1
	 * @param int    $user_id User ID
	 * @param string $type    Consent type
	 * @return bool True if has active consent
	 */
	public function has_consent( int $user_id, string $type ): bool {
		$count = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->table} WHERE user_id = %d AND type = %s AND status = 'accepted'",
				$user_id,
				$type
			)
		);

		return $count > 0;
	}

	/**
	 * Withdraw consent
	 *
	 * @since 3.0.1
	 * @param int $consent_id Consent ID
	 * @return bool True on success
	 */
	public function withdraw( int $consent_id ): bool {
		return $this->update(
			$consent_id,
			array(
				'status' => 'withdrawn',
			)
		);
	}

	/**
	 * Get consent statistics by type
	 *
	 * @since 3.0.1
	 * @return array Array of statistics (type => count)
	 */
	public function get_stats_by_type(): array {
		$results = $this->wpdb->get_results(
			"SELECT type, COUNT(*) as count FROM {$this->table} WHERE status = 'accepted' GROUP BY type"
		);

		$stats = array();
		foreach ( $results as $row ) {
			$stats[ $row->type ] = (int) $row->count;
		}

		return $stats;
	}

	/**
	 * Get consent statistics by status
	 *
	 * @since 3.0.1
	 * @return array Array of statistics (status => count)
	 */
	public function get_stats_by_status(): array {
		$results = $this->wpdb->get_results(
			"SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status"
		);

		$stats = array();
		foreach ( $results as $row ) {
			$stats[ $row->status ] = (int) $row->count;
		}

		return $stats;
	}

	/**
	 * Find consents by IP hash
	 *
	 * @since 3.0.1
	 * @param string $ip_hash SHA256 hash of IP address
	 * @param array  $args    Query arguments
	 * @return array Array of consent records
	 */
	public function find_by_ip_hash( string $ip_hash, array $args = array() ): array {
		return $this->find_by( 'ip_hash', $ip_hash, $args );
	}

	/**
	 * Get recent consents
	 *
	 * @since 3.0.1
	 * @param int $limit Number of records to retrieve
	 * @return array Array of recent consent records
	 */
	public function get_recent( int $limit = 10 ): array {
		return $this->find_all(
			array(
				'limit'    => $limit,
				'order_by' => 'created_at',
				'order'    => 'DESC',
			)
		);
	}

	/**
	 * Count consents by user
	 *
	 * @since 3.0.1
	 * @param int $user_id User ID
	 * @return int Total consent count for user
	 */
	public function count_by_user( int $user_id ): int {
		return $this->count( array( 'user_id' => $user_id ) );
	}
}
