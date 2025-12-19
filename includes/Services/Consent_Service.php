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
}
