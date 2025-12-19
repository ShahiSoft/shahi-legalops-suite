<?php
/**
 * Consent REST Controller Class
 *
 * Handles REST API endpoints for consent management.
 * Provides CRUD operations and consent lifecycle management.
 *
 * @package     ShahiLegalopsSuite
 * @subpackage  API
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalopsSuite\API;

use ShahiLegalopsSuite\Services\Consent_Service;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Consent_REST_Controller Class
 *
 * REST API endpoints for consent operations.
 *
 * @since 3.0.1
 */
class Consent_REST_Controller extends Base_REST_Controller {

	/**
	 * Consent service
	 *
	 * @since 3.0.1
	 * @var Consent_Service
	 */
	private $service;

	/**
	 * Constructor
	 *
	 * @since 3.0.1
	 */
	public function __construct() {
		$this->rest_base = 'consents';
		$this->service   = new Consent_Service();
	}

	/**
	 * Register REST API routes
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function register_routes() {
		// Get all consents (admin only)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'check_authentication' ),
					'args'                => $this->get_create_params(),
				),
			)
		);

		// Get single consent
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'check_read_permission' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Consent ID', 'shahi-legalops-suite' ),
							'type'        => 'integer',
							'required'    => true,
						),
					),
				),
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'check_update_permission' ),
					'args'                => $this->get_update_params(),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Consent ID', 'shahi-legalops-suite' ),
							'type'        => 'integer',
							'required'    => true,
						),
					),
				),
			)
		);

		// Get user consents
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/user/(?P<user_id>[\d]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_user_consents' ),
				'permission_callback' => array( $this, 'check_authentication' ),
				'args'                => array(
					'user_id' => array(
						'description' => __( 'User ID', 'shahi-legalops-suite' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);

		// Withdraw consent
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/withdraw',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'withdraw_consent' ),
				'permission_callback' => array( $this, 'check_authentication' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'Consent ID', 'shahi-legalops-suite' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);

		// Get consent statistics (admin only)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/stats',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_statistics' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// Check user consent
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/check',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'check_consent' ),
				'permission_callback' => array( $this, 'check_authentication' ),
				'args'                => array(
					'user_id' => array(
						'description' => __( 'User ID', 'shahi-legalops-suite' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'type'    => array(
						'description' => __( 'Consent type', 'shahi-legalops-suite' ),
						'type'        => 'string',
						'required'    => true,
						'enum'        => array( 'necessary', 'analytics', 'marketing', 'preferences' ),
					),
				),
			)
		);

		// Get valid purposes
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/purposes',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_purposes' ),
				'permission_callback' => '__return_true', // Public endpoint
			)
		);

		// Export user consent data (GDPR Article 15)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/export/(?P<user_id>[\d]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'export_user_data' ),
				'permission_callback' => array( $this, 'check_user_or_admin' ),
				'args'                => array(
					'user_id' => array(
						'description' => __( 'User ID', 'shahi-legalops-suite' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);
	}

	/**
	 * Get all consents
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response object
	 */
	public function get_items( $request ) {
		$this->log_request( $request, 'Get Consents' );

		$pagination = $this->prepare_pagination_params( $request );
		$consents   = $this->service->get_recent_consents( $pagination['per_page'] );

		if ( $this->service->has_errors() ) {
			$errors = $this->service->get_errors();
			return $this->error_response(
				$errors[0]['code'],
				$errors[0]['message'],
				500
			);
		}

		$response = $this->success_response(
			array(
				'consents' => array_map( array( $this, 'prepare_item_for_response' ), $consents ),
			)
		);

		return $response;
	}

	/**
	 * Get single consent
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response object
	 */
	public function get_item( $request ) {
		$this->log_request( $request, 'Get Consent' );

		$id      = absint( $request->get_param( 'id' ) );
		$consent = $this->service->get_consent( $id );

		if ( ! $consent ) {
			return $this->error_response(
				'consent_not_found',
				__( 'Consent not found', 'shahi-legalops-suite' ),
				404
			);
		}

		return $this->success_response(
			$this->prepare_item_for_response( $consent )
		);
	}

	/**
	 * Create new consent
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response object
	 */
	public function create_item( $request ) {
		$this->log_request( $request, 'Create Consent' );

		// Validate required parameters
		$validation = $this->validate_required_param( $request, 'type' );
		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$validation = $this->validate_required_param( $request, 'status' );
		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		// Prepare consent data
		$data = array(
			'user_id'      => absint( $request->get_param( 'user_id' ) ) ?: get_current_user_id(),
			'type'         => $this->sanitize_text_param( $request->get_param( 'type' ) ),
			'status'       => $this->sanitize_text_param( $request->get_param( 'status' ) ),
			'consent_text' => $request->get_param( 'consent_text' ) ? $this->sanitize_textarea_param( $request->get_param( 'consent_text' ) ) : '',
			'source'       => $request->get_param( 'source' ) ? $this->sanitize_text_param( $request->get_param( 'source' ) ) : 'api',
		);

		// Add metadata if provided
		if ( $request->get_param( 'metadata' ) ) {
			$data['metadata'] = $request->get_param( 'metadata' );
		}

		// Record consent
		$consent_id = $this->service->record_consent( $data );

		if ( ! $consent_id ) {
			$errors            = $this->service->get_errors();
			$validation_errors = $this->service->get_validation_errors();

			if ( ! empty( $validation_errors ) ) {
				return $this->error_response(
					'validation_failed',
					__( 'Validation failed', 'shahi-legalops-suite' ),
					400,
					array( 'validation_errors' => $validation_errors )
				);
			}

			return $this->error_response(
				$errors[0]['code'] ?? 'create_failed',
				$errors[0]['message'] ?? __( 'Failed to create consent', 'shahi-legalops-suite' ),
				400
			);
		}

		$consent = $this->service->get_consent( $consent_id );

		return $this->success_response(
			$this->prepare_item_for_response( $consent ),
			__( 'Consent recorded successfully', 'shahi-legalops-suite' ),
			201
		);
	}

	/**
	 * Update consent
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response object
	 */
	public function update_item( $request ) {
		$this->log_request( $request, 'Update Consent' );

		$id = absint( $request->get_param( 'id' ) );

		// Check if consent exists
		$consent = $this->service->get_consent( $id );
		if ( ! $consent ) {
			return $this->error_response(
				'consent_not_found',
				__( 'Consent not found', 'shahi-legalops-suite' ),
				404
			);
		}

		// Prepare update data
		$data = array();

		if ( $request->get_param( 'status' ) ) {
			$data['status'] = $this->sanitize_text_param( $request->get_param( 'status' ) );
		}

		if ( $request->get_param( 'metadata' ) ) {
			$data['metadata'] = $request->get_param( 'metadata' );
		}

		// Update consent
		$updated = $this->service->update_consent( $id, $data );

		if ( ! $updated ) {
			$errors = $this->service->get_errors();
			return $this->error_response(
				$errors[0]['code'] ?? 'update_failed',
				$errors[0]['message'] ?? __( 'Failed to update consent', 'shahi-legalops-suite' ),
				400
			);
		}

		$consent = $this->service->get_consent( $id );

		return $this->success_response(
			$this->prepare_item_for_response( $consent ),
			__( 'Consent updated successfully', 'shahi-legalops-suite' )
		);
	}

	/**
	 * Delete consent
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response object
	 */
	public function delete_item( $request ) {
		$this->log_request( $request, 'Delete Consent' );

		$id = absint( $request->get_param( 'id' ) );

		$deleted = $this->service->delete_consent( $id );

		if ( ! $deleted ) {
			$errors = $this->service->get_errors();
			return $this->error_response(
				$errors[0]['code'] ?? 'delete_failed',
				$errors[0]['message'] ?? __( 'Failed to delete consent', 'shahi-legalops-suite' ),
				400
			);
		}

		return $this->success_response(
			null,
			__( 'Consent deleted successfully', 'shahi-legalops-suite' )
		);
	}

	/**
	 * Get user consents
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response object
	 */
	public function get_user_consents( $request ) {
		$this->log_request( $request, 'Get User Consents' );

		$user_id = absint( $request->get_param( 'user_id' ) );

		// Users can only view their own consents unless admin
		if ( $user_id !== get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
			return $this->error_response(
				'rest_forbidden',
				__( 'You can only view your own consents', 'shahi-legalops-suite' ),
				403
			);
		}

		$consents = $this->service->get_user_consent_history( $user_id );

		return $this->success_response(
			array(
				'consents' => array_map( array( $this, 'prepare_item_for_response' ), $consents ),
			)
		);
	}

	/**
	 * Withdraw consent
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response object
	 */
	public function withdraw_consent( $request ) {
		$this->log_request( $request, 'Withdraw Consent' );

		$id = absint( $request->get_param( 'id' ) );

		// Check if consent exists and belongs to user (or user is admin)
		$consent = $this->service->get_consent( $id );
		if ( ! $consent ) {
			return $this->error_response(
				'consent_not_found',
				__( 'Consent not found', 'shahi-legalops-suite' ),
				404
			);
		}

		if ( absint( $consent->user_id ) !== get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
			return $this->error_response(
				'rest_forbidden',
				__( 'You can only withdraw your own consents', 'shahi-legalops-suite' ),
				403
			);
		}

		$withdrawn = $this->service->withdraw_consent( $id );

		if ( ! $withdrawn ) {
			$errors = $this->service->get_errors();
			return $this->error_response(
				$errors[0]['code'] ?? 'withdraw_failed',
				$errors[0]['message'] ?? __( 'Failed to withdraw consent', 'shahi-legalops-suite' ),
				400
			);
		}

		$consent = $this->service->get_consent( $id );

		return $this->success_response(
			$this->prepare_item_for_response( $consent ),
			__( 'Consent withdrawn successfully', 'shahi-legalops-suite' )
		);
	}

	/**
	 * Get consent statistics
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response object
	 */
	public function get_statistics( $request ) {
		$this->log_request( $request, 'Get Statistics' );

		$stats = $this->service->get_statistics();

		return $this->success_response( $stats );
	}

	/**
	 * Check user consent
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response object
	 */
	public function check_consent( $request ) {
		$this->log_request( $request, 'Check Consent' );

		$user_id = absint( $request->get_param( 'user_id' ) );
		$type    = $this->sanitize_text_param( $request->get_param( 'type' ) );

		// Users can only check their own consents unless admin
		if ( $user_id !== get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
			return $this->error_response(
				'rest_forbidden',
				__( 'You can only check your own consents', 'shahi-legalops-suite' ),
				403
			);
		}

		$has_consent = $this->service->has_active_consent( $user_id, $type );

		return $this->success_response(
			array(
				'has_consent' => $has_consent,
				'user_id'     => $user_id,
				'type'        => $type,
			)
		);
	}

	/**
	 * Get valid consent purposes
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response object
	 */
	public function get_purposes( $request ) {
		$this->log_request( $request, 'Get Consent Purposes' );

		$purposes = $this->service->get_valid_purposes();

		return $this->success_response(
			array(
				'purposes' => $purposes,
			)
		);
	}

	/**
	 * Export user consent data
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response object
	 */
	public function export_user_data( $request ) {
		$this->log_request( $request, 'Export User Consent Data' );

		$user_id = absint( $request->get_param( 'user_id' ) );

		$export = $this->service->export_user_consents( $user_id );

		if ( $this->service->has_errors() ) {
			$errors = $this->service->get_errors();
			return $this->error_response(
				$errors[0]['code'] ?? 'export_failed',
				$errors[0]['message'] ?? __( 'Failed to export consent data', 'shahi-legalops-suite' ),
				403
			);
		}

		return $this->success_response(
			array(
				'user_id'     => $user_id,
				'consents'    => $export,
				'exported_at' => current_time( 'mysql' ),
			)
		);
	}

	/**
	 * Check if current user is the target user or admin
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object
	 * @return bool|WP_Error True if allowed
	 */
	public function check_user_or_admin( $request ) {
		$user_id = absint( $request->get_param( 'user_id' ) );
		$current_user = get_current_user_id();

		if ( $user_id === $current_user || current_user_can( 'manage_options' ) ) {
			return true;
		}

		return $this->error_response(
			'rest_forbidden',
			__( 'You are not allowed to access this data', 'shahi-legalops-suite' ),
			403
		);
	}

	/**
	 * Prepare item for response
	 *
	 * @since 3.0.1
	 * @param object $consent Consent object
	 * @return array Formatted consent data
	 */
	protected function prepare_item_for_response( $consent ): array {
		return array(
			'id'         => absint( $consent->id ),
			'user_id'    => absint( $consent->user_id ),
			'type'       => $consent->type,
			'status'     => $consent->status,
			'ip_hash'    => $consent->ip_hash,
			'metadata'   => json_decode( $consent->metadata ?? '{}', true ),
			'created_at' => $consent->created_at,
			'updated_at' => $consent->updated_at,
		);
	}

	/**
	 * Get create parameters schema
	 *
	 * @since 3.0.1
	 * @return array Parameters schema
	 */
	protected function get_create_params(): array {
		return array(
			'user_id'      => array(
				'description'       => __( 'User ID', 'shahi-legalops-suite' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'type'         => array(
				'description' => __( 'Consent type', 'shahi-legalops-suite' ),
				'type'        => 'string',
				'required'    => true,
				'enum'        => array( 'necessary', 'analytics', 'marketing', 'preferences' ),
			),
			'status'       => array(
				'description' => __( 'Consent status', 'shahi-legalops-suite' ),
				'type'        => 'string',
				'required'    => true,
				'enum'        => array( 'accepted', 'rejected', 'withdrawn' ),
			),
			'consent_text' => array(
				'description'       => __( 'Consent text', 'shahi-legalops-suite' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
			),
			'source'       => array(
				'description'       => __( 'Consent source', 'shahi-legalops-suite' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'metadata'     => array(
				'description' => __( 'Additional metadata', 'shahi-legalops-suite' ),
				'type'        => 'object',
			),
		);
	}

	/**
	 * Get update parameters schema
	 *
	 * @since 3.0.1
	 * @return array Parameters schema
	 */
	protected function get_update_params(): array {
		return array(
			'id'       => array(
				'description' => __( 'Consent ID', 'shahi-legalops-suite' ),
				'type'        => 'integer',
				'required'    => true,
			),
			'status'   => array(
				'description' => __( 'Consent status', 'shahi-legalops-suite' ),
				'type'        => 'string',
				'enum'        => array( 'accepted', 'rejected', 'withdrawn' ),
			),
			'metadata' => array(
				'description' => __( 'Additional metadata', 'shahi-legalops-suite' ),
				'type'        => 'object',
			),
		);
	}
}
