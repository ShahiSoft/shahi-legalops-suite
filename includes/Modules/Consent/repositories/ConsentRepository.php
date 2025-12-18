<?php
/**
 * Consent Repository Implementation
 *
 * Handles all database operations for consent logs and preferences.
 *
 * @package ShahiLegalOpsSuite\Modules\Consent\Repositories
 * @since 1.0.0
 */

namespace ShahiLegalOpsSuite\Modules\Consent\Repositories;

use ShahiLegalOpsSuite\Modules\Consent\Interfaces\ConsentRepositoryInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Consent Repository Class
 *
 * Implements ConsentRepositoryInterface for database operations.
 * Handles consent log CRUD, filtering, export, and cleanup.
 *
 * @since 1.0.0
 */
class ConsentRepository implements ConsentRepositoryInterface {

	/**
	 * Database table name (without prefix).
	 *
	 * @var string
	 */
	private string $table_name = 'complyflow_consent_logs';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Constructor intentionally empty for now.
		// Table creation is handled by Consent module's create_tables() method.
	}

	/**
	 * Get the full table name with prefix.
	 *
	 * @return string Full table name.
	 */
	private function get_table_name(): string {
		global $wpdb;
		return $wpdb->prefix . $this->table_name;
	}

	/**
	 * Save user consent preferences.
	 *
	 * @param array $preferences {
	 *     Consent preferences to save.
	 *
	 *     @type array  $categories    Consent categories with boolean values.
	 *                                  E.g., ['necessary' => true, 'analytics' => false]
	 *     @type string $banner_version Banner version identifier.
	 *     @type string $region         Region code (e.g., 'EU', 'US-CA', 'BR', 'CA').
	 *     @type int    $user_id        Optional authenticated user ID.
	 *     @type string $session_id     Session identifier (required).
	 *     @type array  $purposes       Optional purposes array (PRO feature).
	 *     @type string $source         Consent source (e.g., 'banner', 'api', 'import').
	 * }
	 *
	 * @return int|false Log record ID on success, false on failure.
	 */
	public function save_consent( array $preferences ) {
		global $wpdb;

		// Validate required fields.
		if ( empty( $preferences['session_id'] ) || empty( $preferences['region'] ) ) {
			return false;
		}

		// Prepare data for insertion.
		$data   = array();
		$format = array();

		// Session ID (required).
		$data['session_id'] = sanitize_text_field( $preferences['session_id'] );
		$format[]           = '%s';

		// Region code (required).
		$data['region'] = strtoupper( sanitize_text_field( $preferences['region'] ) );
		$format[]       = '%s';

		// Categories (JSON-encoded).
		if ( isset( $preferences['categories'] ) && is_array( $preferences['categories'] ) ) {
			$data['categories'] = wp_json_encode( $preferences['categories'] );
			$format[]           = '%s';
		} else {
			return false; // Categories are required.
		}

		// Banner version.
		if ( isset( $preferences['banner_version'] ) ) {
			$data['banner_version'] = sanitize_text_field( $preferences['banner_version'] );
			$format[]               = '%s';
		} else {
			$data['banner_version'] = '1.0.0';
			$format[]               = '%s';
		}

		// User ID (optional, can be NULL).
		if ( isset( $preferences['user_id'] ) && absint( $preferences['user_id'] ) > 0 ) {
			$data['user_id'] = absint( $preferences['user_id'] );
			$format[]        = '%d';
		} else {
			$data['user_id'] = null;
			$format[]        = '%d';
		}

		// Purposes (optional, JSON-encoded, PRO feature).
		if ( isset( $preferences['purposes'] ) && is_array( $preferences['purposes'] ) ) {
			$data['purposes'] = wp_json_encode( $preferences['purposes'] );
			$format[]         = '%s';
		}

		// Source (how consent was obtained).
		if ( isset( $preferences['source'] ) ) {
			$data['source'] = sanitize_text_field( $preferences['source'] );
			$format[]       = '%s';
		} else {
			$data['source'] = 'banner';
			$format[]       = '%s';
		}

		// IP hash (optional, for audit trail).
		if ( isset( $preferences['ip_hash'] ) ) {
			$data['ip_hash'] = sanitize_text_field( $preferences['ip_hash'] );
			$format[]        = '%s';
		}

		// User agent hash (optional, for audit trail).
		if ( isset( $preferences['user_agent_hash'] ) ) {
			$data['user_agent_hash'] = sanitize_text_field( $preferences['user_agent_hash'] );
			$format[]                = '%s';
		}

		// Expiry date (optional, for consent expiration).
		if ( isset( $preferences['expiry_date'] ) ) {
			$data['expiry_date'] = sanitize_text_field( $preferences['expiry_date'] );
			$format[]            = '%s';
		}

		// Metadata (optional, JSON-encoded).
		if ( isset( $preferences['metadata'] ) && is_array( $preferences['metadata'] ) ) {
			$data['metadata'] = wp_json_encode( $preferences['metadata'] );
			$format[]         = '%s';
		}

		// Timestamp (automatic via DEFAULT CURRENT_TIMESTAMP).
		// withdrawn_at is NULL until user revokes consent.

		// Insert into database.
		$table_name = $this->get_table_name();
		$result     = $wpdb->insert( $table_name, $data, $format );

		if ( false === $result ) {
			// Log error for debugging.
			// error_log( 'ConsentRepository::save_consent() failed: ' . $wpdb->last_error );
			return false;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Get current consent status for user/session.
	 *
	 * Retrieves the most recent, non-withdrawn consent record.
	 * Returns null if no active consent found.
	 *
	 * @param string $session_id Session identifier (required).
	 * @param int    $user_id    Optional user ID (defaults to 0).
	 *
	 * @return array|null Consent record with decoded JSON fields, or null if not found.
	 */
	public function get_consent_status( string $session_id, int $user_id = 0 ) {
		global $wpdb;

		if ( empty( $session_id ) ) {
			return null;
		}

		$session_id = sanitize_text_field( $session_id );
		$table_name = $this->get_table_name();

		// Build query: get most recent non-withdrawn consent.
		if ( $user_id > 0 ) {
			// If user_id provided, prioritize user-specific consent.
			$query = $wpdb->prepare(
				"SELECT * FROM {$table_name}
				WHERE session_id = %s AND withdrawn_at IS NULL
				ORDER BY timestamp DESC
				LIMIT 1",
				$session_id
			);
		} else {
			// Session-only lookup.
			$query = $wpdb->prepare(
				"SELECT * FROM {$table_name}
				WHERE session_id = %s AND withdrawn_at IS NULL
				ORDER BY timestamp DESC
				LIMIT 1",
				$session_id
			);
		}

		$result = $wpdb->get_row( $query, ARRAY_A );

		if ( ! $result ) {
			return null;
		}

		// Decode JSON fields.
		$result['categories'] = json_decode( $result['categories'], true ) ?? array();
		if ( ! empty( $result['purposes'] ) ) {
			$result['purposes'] = json_decode( $result['purposes'], true ) ?? array();
		}
		if ( ! empty( $result['metadata'] ) ) {
			$result['metadata'] = json_decode( $result['metadata'], true ) ?? array();
		}

		return $result;
	}

	/**
	 * Revoke user consent.
	 *
	 * Marks consent record(s) as withdrawn by setting withdrawn_at timestamp.
	 * If $categories is empty, withdraws ALL categories.
	 * If $categories is specified, only marks relevant records withdrawn.
	 *
	 * @param string $session_id Session identifier (required).
	 * @param array  $categories Optional specific categories to revoke. Empty = all.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function withdraw_consent( string $session_id, array $categories = array() ): bool {
		global $wpdb;

		if ( empty( $session_id ) ) {
			return false;
		}

		$session_id = sanitize_text_field( $session_id );
		$table_name = $this->get_table_name();

		// If specific categories provided, we need to fetch the record,
		// update categories, and insert new record (audit trail).
		// If no categories specified, just mark most recent record as withdrawn.

		if ( empty( $categories ) ) {
			// Simple case: revoke all by marking withdrawn_at.
			$result = $wpdb->update(
				$table_name,
				array( 'withdrawn_at' => current_time( 'mysql' ) ),
				array( 'session_id' => $session_id, 'withdrawn_at' => null ),
				array( '%s' ),
				array( '%s', '%s' )
			);

			return false !== $result;
		} else {
			// Complex case: partial withdrawal (remove specific categories).
			// Get current consent.
			$current = $this->get_consent_status( $session_id );
			if ( ! $current ) {
				return false;
			}

			// Remove specified categories.
			$updated_categories = $current['categories'];
			foreach ( $categories as $category ) {
				unset( $updated_categories[ $category ] );
			}

			// Mark original as withdrawn.
			$wpdb->update(
				$table_name,
				array( 'withdrawn_at' => current_time( 'mysql' ) ),
				array( 'id' => $current['id'] ),
				array( '%s' ),
				array( '%d' )
			);

			// Insert new record with updated categories (audit trail).
			return false !== $this->save_consent(
				array(
					'session_id'     => $session_id,
					'region'         => $current['region'],
					'categories'     => $updated_categories,
					'banner_version' => $current['banner_version'],
					'user_id'        => $current['user_id'],
					'source'         => 'withdraw',
				)
			);
		}
	}

	/**
	 * Retrieve consent logs with filtering, pagination, and sorting.
	 *
	 * @param array $args {
	 *     Filtering and pagination arguments.
	 *
	 *     @type string $region      Filter by region code (e.g., 'EU', 'US-CA').
	 *     @type int    $user_id     Filter by user ID.
	 *     @type string $start_date  Filter by start date (YYYY-MM-DD HH:MM:SS).
	 *     @type string $end_date    Filter by end date (YYYY-MM-DD HH:MM:SS).
	 *     @type int    $per_page    Results per page (default: 20, max: 500).
	 *     @type int    $page        Page number (1-indexed, default: 1).
	 *     @type string $orderby     Order by field: timestamp, region, user_id (default: timestamp).
	 *     @type string $order       Sort order: ASC or DESC (default: DESC).
	 *     @type bool   $withdrawn   Include withdrawn consents? (default: false).
	 * }
	 *
	 * @return array Array of consent log records with decoded JSON fields.
	 */
	public function get_logs( array $args = array() ): array {
		global $wpdb;

		// Sanitize and validate arguments.
		$per_page   = min( absint( $args['per_page'] ?? 20 ), 500 );
		$page       = max( 1, absint( $args['page'] ?? 1 ) );
		$offset     = ( $page - 1 ) * $per_page;
		$orderby    = $args['orderby'] ?? 'timestamp';
		$order      = strtoupper( $args['order'] ?? 'DESC' );
		$withdrawn  = ! empty( $args['withdrawn'] );

		// Validate orderby.
		$allowed_orderby = array( 'timestamp', 'region', 'user_id', 'id', 'banner_version' );
		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = 'timestamp';
		}

		// Validate order.
		if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
			$order = 'DESC';
		}

		$table_name = $this->get_table_name();
		$where      = array();
		$where_vals = array();

		// Build WHERE clause.
		if ( ! empty( $args['region'] ) ) {
			$where[]       = 'region = %s';
			$where_vals[]  = strtoupper( sanitize_text_field( $args['region'] ) );
		}

		if ( isset( $args['user_id'] ) && absint( $args['user_id'] ) > 0 ) {
			$where[]       = 'user_id = %d';
			$where_vals[]  = absint( $args['user_id'] );
		}

		if ( ! empty( $args['start_date'] ) ) {
			$where[]       = 'timestamp >= %s';
			$where_vals[]  = sanitize_text_field( $args['start_date'] );
		}

		if ( ! empty( $args['end_date'] ) ) {
			$where[]       = 'timestamp <= %s';
			$where_vals[]  = sanitize_text_field( $args['end_date'] );
		}

		// Filter withdrawn status.
		if ( ! $withdrawn ) {
			$where[] = 'withdrawn_at IS NULL';
		}

		// Build final query.
		$sql = "SELECT * FROM {$table_name}";

		if ( ! empty( $where ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $where );
		}

		$sql .= " ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";

		if ( ! empty( $where_vals ) ) {
			$query = $wpdb->prepare( $sql, array_merge( $where_vals, array( $per_page, $offset ) ) );
		} else {
			$query = $wpdb->prepare( $sql, array( $per_page, $offset ) );
		}

		$results = $wpdb->get_results( $query, ARRAY_A );

		if ( empty( $results ) ) {
			return array();
		}

		// Decode JSON fields for each result.
		foreach ( $results as &$record ) {
			$record['categories'] = json_decode( $record['categories'], true ) ?? array();
			if ( ! empty( $record['purposes'] ) ) {
				$record['purposes'] = json_decode( $record['purposes'], true ) ?? array();
			}
			if ( ! empty( $record['metadata'] ) ) {
				$record['metadata'] = json_decode( $record['metadata'], true ) ?? array();
			}
		}

		return $results;
	}

	/**
	 * Export consent logs in CSV or JSON format.
	 *
	 * @param string $format Export format: 'csv' or 'json' (default: 'csv').
	 * @param array  $filters Optional filter arguments (same as get_logs()).
	 *
	 * @return string Formatted export data.
	 */
	public function export_logs( string $format = 'csv', array $filters = array() ): string {
		$format = strtolower( $format );
		$format = in_array( $format, array( 'csv', 'json' ), true ) ? $format : 'csv';

		// Get all logs (remove pagination for exports).
		$filters['per_page'] = 10000; // Large limit for exports.
		$filters['page']     = 1;
		$logs                = $this->get_logs( $filters );

		if ( empty( $logs ) ) {
			return 'csv' === $format ? '' : '[]';
		}

		if ( 'json' === $format ) {
			return wp_json_encode( $logs );
		}

		// CSV export.
		return $this->export_to_csv( $logs );
	}

	/**
	 * Export logs to CSV format.
	 *
	 * @param array $logs Array of log records.
	 *
	 * @return string CSV-formatted string.
	 */
	private function export_to_csv( array $logs ): string {
		if ( empty( $logs ) ) {
			return '';
		}

		// CSV headers.
		$headers = array(
			'ID',
			'User ID',
			'Session ID',
			'Region',
			'Categories',
			'Purposes',
			'Banner Version',
			'Timestamp',
			'Expiry Date',
			'Source',
			'IP Hash',
			'User Agent Hash',
			'Withdrawn At',
			'Metadata',
		);

		$output = fputcsv( fopen( 'php://memory', 'r+' ), $headers );
		rewind( $output );

		foreach ( $logs as $log ) {
			$row = array(
				$log['id'],
				$log['user_id'] ?? '',
				$log['session_id'],
				$log['region'],
				wp_json_encode( $log['categories'] ),
				! empty( $log['purposes'] ) ? wp_json_encode( $log['purposes'] ) : '',
				$log['banner_version'],
				$log['timestamp'],
				$log['expiry_date'] ?? '',
				$log['source'],
				$log['ip_hash'] ?? '',
				$log['user_agent_hash'] ?? '',
				$log['withdrawn_at'] ?? '',
				! empty( $log['metadata'] ) ? wp_json_encode( $log['metadata'] ) : '',
			);

			$output .= fputcsv( fopen( 'php://memory', 'r+' ), $row );
		}

		return $output;
	}

	/**
	 * Get total count of consent logs.
	 *
	 * @param array $filters Optional filter arguments (same as get_logs()).
	 *
	 * @return int Total count of matching records.
	 */
	public function count_logs( array $filters = array() ): int {
		global $wpdb;

		$table_name = $this->get_table_name();
		$where      = array();
		$where_vals = array();
		$withdrawn  = ! empty( $filters['withdrawn'] );

		// Build WHERE clause (same as get_logs).
		if ( ! empty( $filters['region'] ) ) {
			$where[]       = 'region = %s';
			$where_vals[]  = strtoupper( sanitize_text_field( $filters['region'] ) );
		}

		if ( isset( $filters['user_id'] ) && absint( $filters['user_id'] ) > 0 ) {
			$where[]       = 'user_id = %d';
			$where_vals[]  = absint( $filters['user_id'] );
		}

		if ( ! empty( $filters['start_date'] ) ) {
			$where[]       = 'timestamp >= %s';
			$where_vals[]  = sanitize_text_field( $filters['start_date'] );
		}

		if ( ! empty( $filters['end_date'] ) ) {
			$where[]       = 'timestamp <= %s';
			$where_vals[]  = sanitize_text_field( $filters['end_date'] );
		}

		if ( ! $withdrawn ) {
			$where[] = 'withdrawn_at IS NULL';
		}

		// Build query.
		$sql = "SELECT COUNT(*) FROM {$table_name}";

		if ( ! empty( $where ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $where );
			$query = $wpdb->prepare( $sql, $where_vals );
		} else {
			$query = $sql;
		}

		return (int) $wpdb->get_var( $query );
	}

	/**
	 * Delete expired consent logs based on retention policy.
	 *
	 * Removes records older than specified retention period.
	 * Regional retention policies can be implemented by calling this
	 * with different retention_days based on region.
	 *
	 * @param int $retention_days Days to retain. Records older than this are deleted.
	 *
	 * @return int Number of deleted records.
	 */
	public function cleanup_expired_logs( int $retention_days ): int {
		global $wpdb;

		if ( $retention_days <= 0 ) {
			return 0;
		}

		$table_name = $this->get_table_name();

		// Calculate cutoff date.
		$cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$retention_days} days" ) );

		// Delete old records.
		$deleted = $wpdb->delete(
			$table_name,
			array( 'timestamp' => $cutoff_date ),
			array( '%s' ),
			'<' // WHERE timestamp < cutoff_date
		);

		// Note: wpdb->delete() uses = by default, so we use raw query for <.
		// Correcting with raw prepared query:
		$sql = $wpdb->prepare(
			"DELETE FROM {$table_name} WHERE timestamp < %s",
			$cutoff_date
		);
		$deleted = $wpdb->query( $sql );

		return max( 0, (int) $deleted );
	}

	/**
	 * Hash IP address for privacy.
	 *
	 * @param string $ip IP address to hash.
	 *
	 * @return string SHA-256 hash of IP.
	 */
	public static function hash_ip( string $ip ): string {
		return hash( 'sha256', $ip );
	}

	/**
	 * Hash user agent for audit trail.
	 *
	 * @param string $user_agent User agent string.
	 *
	 * @return string SHA-256 hash of user agent.
	 */
	public static function hash_user_agent( string $user_agent ): string {
		return hash( 'sha256', $user_agent );
	}

	/**
	 * Generate session ID (if not provided).
	 *
	 * @return string Unique session identifier.
	 */
	public static function generate_session_id(): string {
		return wp_generate_uuid4();
	}

	/**
	 * Get client IP address.
	 *
	 * Attempts to determine true client IP, handling proxies and CDNs.
	 *
	 * @return string Client IP address.
	 */
	public static function get_client_ip(): string {
		// Check for shared internet.
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			// Handle multiple IPs (take first one).
			$ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			$ip  = trim( $ips[0] );
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		} else {
			$ip = '0.0.0.0';
		}

		// Validate IP.
		if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return $ip;
		}

		return '0.0.0.0';
	}
}
