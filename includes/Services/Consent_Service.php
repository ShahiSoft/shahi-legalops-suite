<?php
/**
 * Consent Service Class
 *
 * Business logic for consent management (GDPR/CCPA/LGPD compliance).
 * Handles consent creation, validation, withdrawal, and reporting.
 *
 * @package     ShahiLegalopsSuite
 * @subpackage  Services
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalopsSuite\Services;

use ShahiLegalopsSuite\Database\Repositories\Consent_Repository;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Consent_Service Class
 *
 * Manages consent lifecycle and business rules.
 *
 * @since 3.0.1
 */
class Consent_Service extends Base_Service {

	/**
	 * Consent repository
	 *
	 * @since 3.0.1
	 * @var Consent_Repository
	 */
	private $repository;

	/**
	 * Allowed consent types
	 *
	 * @since 3.0.1
	 * @var array
	 */
	private $allowed_types = array( 'necessary', 'analytics', 'marketing', 'preferences' );

	/**
	 * Allowed consent statuses
	 *
	 * @since 3.0.1
	 * @var array
	 */
	private $allowed_statuses = array( 'accepted', 'rejected', 'withdrawn' );

	/**
	 * Constructor
	 *
	 * @since 3.0.1
	 * @param Consent_Repository $repository Consent repository instance
	 */
	public function __construct( Consent_Repository $repository = null ) {
		$this->repository = $repository ?? new Consent_Repository();
	}

	/**
	 * Record new consent
	 *
	 * @since 3.0.1
	 * @param array $data Consent data
	 * @return int|false Consent ID or false on failure
	 */
	public function record_consent( array $data ) {
		$this->clear_errors();

		// Validate required fields
		if ( ! $this->validate_required( $data['type'] ?? '', 'type' ) ) {
			return false;
		}

		if ( ! $this->validate_required( $data['status'] ?? '', 'status' ) ) {
			return false;
		}

		// Validate consent type
		if ( ! $this->validate_in_list( $data['type'], $this->allowed_types, 'type' ) ) {
			return false;
		}

		// Validate consent status
		if ( ! $this->validate_in_list( $data['status'], $this->allowed_statuses, 'status' ) ) {
			return false;
		}

		// Prepare consent data
		$consent_data = array(
			'user_id'  => $data['user_id'] ?? get_current_user_id(),
			'type'     => $this->sanitize_string( $data['type'] ),
			'status'   => $this->sanitize_string( $data['status'] ),
			'ip_hash'  => $this->hash_ip( $data['ip_address'] ?? $this->get_user_ip() ),
			'metadata' => $this->prepare_metadata( array(
				'user_agent'    => $data['user_agent'] ?? $this->get_user_agent(),
				'consent_text'  => $data['consent_text'] ?? '',
				'source'        => $data['source'] ?? 'website',
				'language'      => $data['language'] ?? get_locale(),
				'timestamp'     => current_time( 'mysql' ),
			) ),
		);

		// Merge additional metadata if provided
		if ( ! empty( $data['metadata'] ) && is_array( $data['metadata'] ) ) {
			$existing_metadata = $this->parse_metadata( $consent_data['metadata'] );
			$merged_metadata   = array_merge( $existing_metadata, $data['metadata'] );
			$consent_data['metadata'] = $this->prepare_metadata( $merged_metadata );
		}

		// Create consent record
		$consent_id = $this->repository->create( $consent_data );

		if ( ! $consent_id ) {
			$this->add_error( 'create_failed', 'Failed to record consent', $this->repository->get_last_error() );
			return false;
		}

		$this->add_message( sprintf( 'Consent recorded successfully (ID: %d)', $consent_id ) );

		/**
		 * Fires after consent is recorded
		 *
		 * @since 3.0.1
		 * @param int   $consent_id Consent ID
		 * @param array $consent_data Consent data
		 */
		do_action( 'slos_consent_recorded', $consent_id, $consent_data );

		return $consent_id;
	}

	/**
	 * Update existing consent
	 *
	 * @since 3.0.1
	 * @param int   $consent_id Consent ID
	 * @param array $data Update data
	 * @return bool True on success, false on failure
	 */
	public function update_consent( int $consent_id, array $data ): bool {
		$this->clear_errors();

		// Check if consent exists
		if ( ! $this->repository->exists( $consent_id ) ) {
			$this->add_error( 'not_found', 'Consent not found' );
			return false;
		}

		// Validate status if provided
		if ( isset( $data['status'] ) && ! $this->validate_in_list( $data['status'], $this->allowed_statuses, 'status' ) ) {
			return false;
		}

		// Prepare update data
		$update_data = array();

		if ( isset( $data['status'] ) ) {
			$update_data['status'] = $this->sanitize_string( $data['status'] );
		}

		if ( isset( $data['metadata'] ) && is_array( $data['metadata'] ) ) {
			$update_data['metadata'] = $this->prepare_metadata( $data['metadata'] );
		}

		// Update consent record
		$updated = $this->repository->update( $consent_id, $update_data );

		if ( ! $updated ) {
			$this->add_error( 'update_failed', 'Failed to update consent', $this->repository->get_last_error() );
			return false;
		}

		$this->add_message( 'Consent updated successfully' );

		/**
		 * Fires after consent is updated
		 *
		 * @since 3.0.1
		 * @param int   $consent_id Consent ID
		 * @param array $update_data Update data
		 */
		do_action( 'slos_consent_updated', $consent_id, $update_data );

		return true;
	}

	/**
	 * Withdraw consent
	 *
	 * @since 3.0.1
	 * @param int $consent_id Consent ID
	 * @return bool True on success, false on failure
	 */
	public function withdraw_consent( int $consent_id ): bool {
		$this->clear_errors();

		// Check if consent exists
		if ( ! $this->repository->exists( $consent_id ) ) {
			$this->add_error( 'not_found', 'Consent not found' );
			return false;
		}

		// Withdraw consent
		$withdrawn = $this->repository->withdraw( $consent_id );

		if ( ! $withdrawn ) {
			$this->add_error( 'withdraw_failed', 'Failed to withdraw consent' );
			return false;
		}

		$this->add_message( 'Consent withdrawn successfully' );

		/**
		 * Fires after consent is withdrawn
		 *
		 * @since 3.0.1
		 * @param int $consent_id Consent ID
		 */
		do_action( 'slos_consent_withdrawn', $consent_id );

		return true;
	}

	/**
	 * Check if user has active consent
	 *
	 * @since 3.0.1
	 * @param int    $user_id User ID
	 * @param string $type Consent type
	 * @return bool True if user has active consent
	 */
	public function has_active_consent( int $user_id, string $type ): bool {
		return $this->repository->has_consent( $user_id, $type, 'accepted' );
	}

	/**
	 * Get user's active consents
	 *
	 * @since 3.0.1
	 * @param int $user_id User ID
	 * @return array Array of consent objects
	 */
	public function get_user_consents( int $user_id ): array {
		return $this->repository->get_active_consents( $user_id );
	}

	/**
	 * Get all consents for a user (including withdrawn/rejected)
	 *
	 * @since 3.0.1
	 * @param int $user_id User ID
	 * @return array Array of consent objects
	 */
	public function get_user_consent_history( int $user_id ): array {
		return $this->repository->find_by_user( $user_id );
	}

	/**
	 * Get consent by ID
	 *
	 * @since 3.0.1
	 * @param int $consent_id Consent ID
	 * @return object|null Consent object or null if not found
	 */
	public function get_consent( int $consent_id ) {
		return $this->repository->find( $consent_id );
	}

	/**
	 * Delete consent record (admin only)
	 *
	 * @since 3.0.1
	 * @param int $consent_id Consent ID
	 * @return bool True on success, false on failure
	 */
	public function delete_consent( int $consent_id ): bool {
		$this->clear_errors();

		// Validate admin capability
		if ( ! $this->validate_capability( 'manage_options' ) ) {
			return false;
		}

		// Check if consent exists
		if ( ! $this->repository->exists( $consent_id ) ) {
			$this->add_error( 'not_found', 'Consent not found' );
			return false;
		}

		// Delete consent
		$deleted = $this->repository->delete( $consent_id );

		if ( ! $deleted ) {
			$this->add_error( 'delete_failed', 'Failed to delete consent' );
			return false;
		}

		$this->add_message( 'Consent deleted successfully' );

		/**
		 * Fires after consent is deleted
		 *
		 * @since 3.0.1
		 * @param int $consent_id Consent ID
		 */
		do_action( 'slos_consent_deleted', $consent_id );

		return true;
	}

	/**
	 * Get consent statistics
	 *
	 * @since 3.0.1
	 * @return array Statistics array
	 */
	public function get_statistics(): array {
		$stats_by_type   = $this->repository->get_stats_by_type();
		$stats_by_status = $this->repository->get_stats_by_status();

		return array(
			'by_type'   => $stats_by_type,
			'by_status' => $stats_by_status,
		);
	}

	/**
	 * Get recent consents
	 *
	 * @since 3.0.1
	 * @param int $limit Number of records to retrieve
	 * @return array Array of consent objects
	 */
	public function get_recent_consents( int $limit = 10 ): array {
		return $this->repository->get_recent( $limit );
	}

	/**
	 * Bulk withdraw user consents
	 *
	 * @since 3.0.1
	 * @param int    $user_id User ID
	 * @param string $type Optional consent type to filter by
	 * @return int Number of consents withdrawn
	 */
	public function bulk_withdraw_user_consents( int $user_id, string $type = '' ): int {
		$this->clear_errors();

		// Get user's active consents
		$consents = $type
			? $this->repository->find_by( 'user_id', $user_id, array() )
			: $this->repository->get_active_consents( $user_id );

		$withdrawn_count = 0;

		foreach ( $consents as $consent ) {
			// Skip if filtering by type and doesn't match
			if ( $type && $consent->type !== $type ) {
				continue;
			}

			// Skip if already withdrawn
			if ( 'withdrawn' === $consent->status ) {
				continue;
			}

			if ( $this->repository->withdraw( $consent->id ) ) {
				$withdrawn_count++;
			}
		}

		if ( $withdrawn_count > 0 ) {
			$this->add_message( sprintf( '%d consent(s) withdrawn', $withdrawn_count ) );

			/**
			 * Fires after bulk consent withdrawal
			 *
			 * @since 3.0.1
			 * @param int    $user_id User ID
			 * @param int    $withdrawn_count Number of consents withdrawn
			 * @param string $type Consent type filter
			 */
			do_action( 'slos_bulk_consent_withdrawn', $user_id, $withdrawn_count, $type );
		}

		return $withdrawn_count;
	}

	/**
	 * Validate consent data structure
	 *
	 * @since 3.0.1
	 * @param array $data Consent data to validate
	 * @return bool True if valid
	 */
	public function validate_consent_data( array $data ): bool {
		$this->clear_errors();

		// Required fields
		$required_fields = array( 'type', 'status' );

		foreach ( $required_fields as $field ) {
			if ( ! isset( $data[ $field ] ) || empty( $data[ $field ] ) ) {
				$this->add_validation_error( $field, sprintf( '%s is required', ucfirst( $field ) ) );
			}
		}

		// Validate type
		if ( isset( $data['type'] ) && ! in_array( $data['type'], $this->allowed_types, true ) ) {
			$this->add_validation_error( 'type', sprintf( 'Invalid consent type. Allowed types: %s', implode( ', ', $this->allowed_types ) ) );
		}

		// Validate status
		if ( isset( $data['status'] ) && ! in_array( $data['status'], $this->allowed_statuses, true ) ) {
			$this->add_validation_error( 'status', sprintf( 'Invalid consent status. Allowed statuses: %s', implode( ', ', $this->allowed_statuses ) ) );
		}

		return ! $this->has_errors();
	}

	/**
	 * Get allowed consent types
	 *
	 * @since 3.0.1
	 * @return array Allowed consent types
	 */
	public function get_allowed_types(): array {
		return $this->allowed_types;
	}

	/**
	 * Get allowed consent statuses
	 *
	 * @since 3.0.1
	 * @return array Allowed consent statuses
	 */
	public function get_allowed_statuses(): array {
		return $this->allowed_statuses;
	}

	/**
	 * Get user's consent preferences (convenience method)
	 *
	 * Returns the current consent status for each type for a specific user.
	 * Most recent consent of each type is returned.
	 *
	 * @since 3.0.1
	 * @param int    $user_id User ID
	 * @param string $ip_hash Optional IP hash for anonymous users
	 * @return array Array of preferences indexed by type (necessary, analytics, marketing, preferences, accepted, rejected, withdrawn, not_asked)
	 */
	public function get_user_preferences( int $user_id = 0, string $ip_hash = '' ): array {
		$this->clear_errors();

		// If no user and no IP hash, return defaults
		if ( ! $user_id && empty( $ip_hash ) ) {
			return $this->get_default_preferences();
		}

		// Get consents from repository
		if ( $user_id ) {
			$consents = $this->repository->find_by_user( $user_id );
		} elseif ( ! empty( $ip_hash ) ) {
			$consents = $this->repository->find_by_ip_hash( $ip_hash );
		} else {
			return $this->get_default_preferences();
		}

		// Build preferences array
		$preferences = array();

		foreach ( $this->allowed_types as $type ) {
			// Find most recent consent for this type
			$recent_consent = null;

			foreach ( $consents as $consent ) {
				if ( $consent->type === $type ) {
					// Keep the most recent one
					if ( ! $recent_consent || strtotime( $consent->updated_at ) > strtotime( $recent_consent->updated_at ) ) {
						$recent_consent = $consent;
					}
				}
			}

			if ( $recent_consent ) {
				$preferences[ $type ] = $recent_consent->status;
			} else {
				// Never asked about this type
				$preferences[ $type ] = 'not_asked';
			}
		}

		return $preferences;
	}

	/**
	 * Get default consent preferences
	 *
	 * Returns default consent state for new users (necessary is pre-accepted per GDPR).
	 *
	 * @since 3.0.1
	 * @return array Array of default preferences
	 */
	public function get_default_preferences(): array {
		$preferences = array();

		foreach ( $this->allowed_types as $type ) {
			// 'necessary' is always pre-consented per GDPR
			$preferences[ $type ] = ( 'necessary' === $type ) ? 'accepted' : 'not_asked';
		}

		/**
		 * Filter default consent preferences
		 *
		 * @since 3.0.1
		 * @param array $preferences Default preferences array
		 * @return array Filtered preferences
		 */
		return apply_filters( 'slos_default_consent_preferences', $preferences );
	}

	/**
	 * Check if user needs to be shown consent banner
	 *
	 * User needs banner if they haven't been asked about non-necessary consents yet.
	 *
	 * @since 3.0.1
	 * @param int    $user_id User ID
	 * @param string $ip_hash Optional IP hash for anonymous users
	 * @return bool True if user should see consent banner
	 */
	public function should_show_banner( int $user_id = 0, string $ip_hash = '' ): bool {
		$this->clear_errors();

		// Get user's preferences
		$preferences = $this->get_user_preferences( $user_id, $ip_hash );

		// Check if user has made a choice about non-necessary consents
		foreach ( $this->allowed_types as $type ) {
			if ( 'necessary' !== $type && 'not_asked' === ( $preferences[ $type ] ?? 'not_asked' ) ) {
				// User hasn't been asked about this type yet
				return true;
			}
		}

		return false;
	}

	/**
	 * Record multiple consents at once (bulk operation)
	 *
	 * @since 3.0.1
	 * @param array $data {
	 *     @type int    $user_id User ID
	 *     @type string $ip_address IP address to hash
	 *     @type array  $consents Array of type => status pairs
	 * }
	 * @return array Response with count of created records
	 */
	public function record_multiple_consents( array $data ): array {
		$this->clear_errors();

		// Validate user identification
		$user_id = $data['user_id'] ?? 0;
		$ip_address = $data['ip_address'] ?? $this->get_user_ip();
		$ip_hash = $this->hash_ip( $ip_address );

		if ( ! $user_id && empty( $ip_hash ) ) {
			$this->add_error( 'invalid_identifier', 'Either user_id or ip_address is required' );
			return array(
				'success' => false,
				'error'   => 'Either user_id or ip_address is required',
			);
		}

		// Validate consents array
		if ( ! isset( $data['consents'] ) || ! is_array( $data['consents'] ) || empty( $data['consents'] ) ) {
			$this->add_error( 'invalid_consents', 'Consents array is required and cannot be empty' );
			return array(
				'success' => false,
				'error'   => 'Consents array is required',
			);
		}

		$created_count = 0;
		$failed_count = 0;
		$failed_types = array();

		// Record each consent
		foreach ( $data['consents'] as $type => $status ) {
			$consent_data = array(
				'user_id'     => $user_id ?: null,
				'ip_hash'     => $ip_hash,
				'type'        => $type,
				'status'      => $status,
				'ip_address'  => $ip_address,
				'user_agent'  => $this->get_user_agent(),
			);

			$result = $this->record_consent( $consent_data );

			if ( $result ) {
				$created_count++;
			} else {
				$failed_count++;
				$failed_types[] = $type;
			}
		}

		$message = sprintf( 'Recorded %d consent(s)', $created_count );
		if ( $failed_count > 0 ) {
			$message .= sprintf( '; Failed: %d (%s)', $failed_count, implode( ', ', $failed_types ) );
		}

		return array(
			'success'         => $failed_count === 0,
			'created_count'   => $created_count,
			'failed_count'    => $failed_count,
			'failed_types'    => $failed_types,
			'message'         => $message,
		);
	}

	/**
	 * Get anonymized consent summary
	 *
	 * Returns aggregated statistics without identifying individual users.
	 * Useful for privacy-respecting analytics.
	 *
	 * @since 3.0.1
	 * @return array Summary statistics
	 */
	public function get_anonymized_summary(): array {
		$this->clear_errors();

		$stats = $this->get_statistics();

		return array(
			'total_consents'      => array_sum( $stats['by_status'] ?? array() ),
			'accepted_percentage' => $this->calculate_acceptance_rate(),
			'type_distribution'   => $stats['by_type'] ?? array(),
			'status_distribution' => $stats['by_status'] ?? array(),
		);
	}

	/**
	 * Calculate acceptance rate as percentage
	 *
	 * @since 3.0.1
	 * @return float Acceptance rate (0-100)
	 */
	public function calculate_acceptance_rate(): float {
		$stats = $this->get_statistics();
		$total = array_sum( $stats['by_status'] ?? array() );

		if ( $total === 0 ) {
			return 0.0;
		}

		$accepted = $stats['by_status']['accepted'] ?? 0;
		return round( ( $accepted / $total ) * 100, 2 );
	}

	/**
	 * Export user consents as array
	 *
	 * Used for data export requests (GDPR Article 15).
	 *
	 * @since 3.0.1
	 * @param int $user_id User ID
	 * @return array User's consent records
	 */
	public function export_user_consents( int $user_id ): array {
		$this->clear_errors();

		if ( ! $this->validate_capability( 'manage_options' ) && get_current_user_id() !== $user_id ) {
			$this->add_error( 'unauthorized', 'You do not have permission to export this user\'s consents' );
			return array();
		}

		$consents = $this->get_user_consent_history( $user_id );
		$exported = array();

		foreach ( $consents as $consent ) {
			$exported[] = array(
				'id'         => $consent->id,
				'type'       => $consent->type,
				'status'     => $consent->status,
				'created_at' => $consent->created_at,
				'updated_at' => $consent->updated_at,
				'metadata'   => is_string( $consent->metadata ) ? json_decode( $consent->metadata, true ) : $consent->metadata,
			);
		}

		return $exported;
	}
}
