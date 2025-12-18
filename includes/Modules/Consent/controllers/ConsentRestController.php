<?php
/**
 * Consent REST API Controller
 *
 * @package ShahiLegalOpsSuite\Modules\Consent\Controllers
 * @since 1.0.0
 */

namespace ShahiLegalOpsSuite\Modules\Consent\Controllers;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class ConsentRestController
 *
 * Handles REST endpoints for consent management.
 */
class ConsentRestController extends WP_REST_Controller {

	/**
	 * Module instance.
	 *
	 * @var object
	 */
	private $module;

	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'complyflow/v1';

	/**
	 * REST base.
	 *
	 * @var string
	 */
	protected $rest_base = 'consent';

	/**
	 * Constructor.
	 *
	 * @param object $module Module instance.
	 */
	public function __construct( $module ) {
		$this->module = $module;
	}

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		// POST /wp-json/complyflow/v1/consent/preferences
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/preferences',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'save_preferences' ),
					'permission_callback' => '__return_true', // Public endpoint.
					'args'                => array(
						'categories' => array(
							'type'     => 'object',
							'required' => true,
						),
						'banner_version' => array(
							'type' => 'string',
						),
						'region' => array(
							'type' => 'string',
						),
					),
				),
			)
		);

		// GET /wp-json/complyflow/v1/consent/status
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/status',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_consent_status' ),
					'permission_callback' => '__return_true',
				),
			)
		);

		// POST /wp-json/complyflow/v1/consent/withdraw
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/withdraw',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'withdraw_consent' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'categories' => array(
							'type' => 'array',
						),
					),
				),
			)
		);

		// GET /wp-json/complyflow/v1/consent/logs (admin)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/logs',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_logs' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);

		// POST /wp-json/complyflow/v1/consent/logs/bulk-export (admin)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/logs/bulk-export',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'bulk_export_logs' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'                => array(
						'format' => array(
							'type'    => 'string',
							'enum'    => array( 'csv', 'json' ),
							'default' => 'csv',
						),
						'region' => array(
							'type' => 'string',
						),
						'start_date' => array(
							'type' => 'string',
						),
						'end_date' => array(
							'type' => 'string',
						),
					),
				),
			)
		);

		// GET /wp-json/complyflow/v1/consent/settings (admin)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/settings',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);

		// POST /wp-json/complyflow/v1/consent/settings (admin)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/settings',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'                => array(
						'settings' => array(
							'type'     => 'object',
							'required' => true,
						),
					),
				),
			)
		);

		// POST /wp-json/complyflow/v1/consent/scan (admin)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/scan',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'start_scan' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'                => array(
						'scope' => array(
							'type'    => 'string',
							'enum'    => array( 'homepage', 'all_pages', 'custom_urls' ),
							'default' => 'homepage',
						),
						'urls' => array(
							'type' => 'array',
						),
					),
				),
			)
		);

		// GET /wp-json/complyflow/v1/consent/scan/results (admin)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/scan/results',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_scan_results' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);

		// GET /wp-json/complyflow/v1/consent/regions/stats (admin)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/regions/stats',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_region_statistics' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'                => array(
						'region' => array(
							'type'    => 'string',
							'description' => 'Filter by specific region',
						),
						'start_date' => array(
							'type'    => 'string',
							'description' => 'Start date for statistics (YYYY-MM-DD)',
						),
						'end_date' => array(
							'type'    => 'string',
							'description' => 'End date for statistics (YYYY-MM-DD)',
						),
					),
				),
			)
		);
	}

	/**
	 * Save user consent preferences.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function save_preferences( WP_REST_Request $request ) {
		$categories      = $request->get_param( 'categories' );
		$banner_version  = $request->get_param( 'banner_version' ) ?? '1.0';
		$region          = $request->get_param( 'region' ) ?? 'DEFAULT';

		if ( ! is_array( $categories ) ) {
			return new WP_Error(
				'invalid_categories',
				__( 'Categories must be an object.', 'shahi-legalops-suite' ),
				array( 'status' => 400 )
			);
		}

		$repository = $this->module->get_service( 'repository' );
		$log_id     = $repository->save_consent(
			array(
				'categories'      => $categories,
				'banner_version'  => $banner_version,
				'region'          => $region,
				'user_id'         => get_current_user_id() ?: null,
				'session_id'      => $this->module->get_session_id(),
			)
		);

		if ( ! $log_id ) {
			return new WP_Error(
				'consent_save_failed',
				__( 'Failed to save consent.', 'shahi-legalops-suite' ),
				array( 'status' => 500 )
			);
		}

		return new WP_REST_Response(
			array(
				'success'    => true,
				'message'    => __( 'Consent saved successfully.', 'shahi-legalops-suite' ),
				'consent_id' => $log_id,
			),
			201
		);
	}

	/**
	 * Get user's current consent status.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function get_consent_status( WP_REST_Request $request ) {
		$repository = $this->module->get_service( 'repository' );
		$session_id = $this->module->get_session_id();
		$consent    = $repository->get_consent_status( $session_id, get_current_user_id() );

		if ( ! $consent ) {
			return new WP_REST_Response(
				array(
					'categories' => array(),
					'timestamp'  => null,
					'withdrawn'  => false,
				)
			);
		}

		return new WP_REST_Response( $consent );
	}

	/**
	 * Withdraw user consent.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function withdraw_consent( WP_REST_Request $request ) {
		$categories = $request->get_param( 'categories' ) ?? array();

		$repository = $this->module->get_service( 'repository' );
		$success    = $repository->withdraw_consent( $this->module->get_session_id(), $categories );

		if ( ! $success ) {
			return new WP_Error(
				'withdraw_failed',
				__( 'Failed to withdraw consent.', 'shahi-legalops-suite' ),
				array( 'status' => 500 )
			);
		}

		return new WP_REST_Response(
			array(
				'success'       => true,
				'message'       => __( 'Consent withdrawn.', 'shahi-legalops-suite' ),
				'withdrawn_at'  => current_time( 'mysql' ),
			)
		);
	}

	/**
	 * Get consent logs (admin).
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function get_logs( WP_REST_Request $request ) {
		$per_page = (int) $request->get_param( 'per_page' ) ?? 50;
		$page     = (int) $request->get_param( 'page' ) ?? 1;
		$region   = $request->get_param( 'region' );
		$order    = strtoupper( $request->get_param( 'order' ) ?? 'DESC' );
		$orderby  = $request->get_param( 'orderby' ) ?? 'timestamp';

		$filters = array(
			'per_page' => $per_page,
			'page'     => $page,
			'orderby'  => $orderby,
			'order'    => $order,
		);

		if ( $region ) {
			$filters['region'] = $region;
		}

		$repository = $this->module->get_service( 'repository' );
		$logs       = $repository->get_logs( $filters );
		$total      = $repository->count_logs( $filters );

		return new WP_REST_Response(
			array(
				'logs'  => $logs,
				'total' => $total,
				'pages' => ceil( $total / $per_page ),
			)
		);
	}

	/**
	 * Bulk export logs.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function bulk_export_logs( WP_REST_Request $request ) {
		// TODO: Implement bulk export to CSV/JSON with filters.
		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Export started. Check your email.', 'shahi-legalops-suite' ),
			)
		);
	}

	/**
	 * Get module settings (admin).
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function get_settings( WP_REST_Request $request ) {
		$settings = $this->module->get_settings();
		return new WP_REST_Response( $settings );
	}

	/**
	 * Update module settings (admin).
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_settings( WP_REST_Request $request ) {
		$settings = $request->get_param( 'settings' );

		if ( ! is_array( $settings ) ) {
			return new WP_Error(
				'invalid_settings',
				__( 'Settings must be an object.', 'shahi-legalops-suite' ),
				array( 'status' => 400 )
			);
		}

		// Merge with existing.
		$current = $this->module->get_settings();
		$merged  = wp_parse_args( $settings, $current );

		// Sanitize and validate.
		$merged = $this->sanitize_settings( $merged );

		// Save.
		update_option( 'complyflow_consent_settings', $merged );

		return new WP_REST_Response(
			array(
				'success'  => true,
				'message'  => __( 'Settings saved.', 'shahi-legalops-suite' ),
				'settings' => $merged,
			)
		);
	}

	/**
	 * Start cookie scan (admin).
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function start_scan( WP_REST_Request $request ) {
		// TODO: Implement scanner service call; trigger async job if needed.
		$scan_id = 'scan_' . wp_rand();

		return new WP_REST_Response(
			array(
				'success'  => true,
				'message'  => __( 'Scan started.', 'shahi-legalops-suite' ),
				'scan_id'  => $scan_id,
				'status'   => 'in_progress',
			)
		);
	}

	/**
	 * Get scan results (admin).
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function get_scan_results( WP_REST_Request $request ) {
		// TODO: Retrieve scan results from repository or transient.
		return new WP_REST_Response(
			array(
				'scan_id'             => 'scan_abc123',
				'status'              => 'completed',
				'timestamp'           => current_time( 'mysql' ),
				'detected_cookies'    => array(),
				'detected_scripts'    => array(),
				'total_cookies'       => 0,
				'total_scripts'       => 0,
			)
		);
	}

	/**
	 * Check if user is admin.
	 *
	 * @return bool
	 */
	public function check_admin_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get collection parameters for pagination/filtering.
	 *
	 * @return array
	 */
	public function get_collection_params(): array {
		return array(
			'page'      => array(
				'description'       => __( 'Current page of the collection.', 'shahi-legalops-suite' ),
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
			),
			'per_page'  => array(
				'description'       => __( 'Maximum number of items to be returned.', 'shahi-legalops-suite' ),
				'type'              => 'integer',
				'default'           => 50,
				'sanitize_callback' => 'absint',
			),
			'orderby'   => array(
				'description'       => __( 'Sort collection by object attribute.', 'shahi-legalops-suite' ),
				'type'              => 'string',
				'default'           => 'timestamp',
				'enum'              => array( 'timestamp', 'region', 'user_id' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'order'     => array(
				'description'       => __( 'Order sort attribute ascending or descending.', 'shahi-legalops-suite' ),
				'type'              => 'string',
				'default'           => 'DESC',
				'enum'              => array( 'ASC', 'DESC' ),
				'sanitize_callback' => 'strtoupper',
			),
			'region'    => array(
				'description'       => __( 'Filter by region.', 'shahi-legalops-suite' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}

	/**
	 * Get region-based consent statistics.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function get_region_statistics( WP_REST_Request $request ) {
		$region     = $request->get_param( 'region' );
		$start_date = $request->get_param( 'start_date' );
		$end_date   = $request->get_param( 'end_date' );

		$repository = $this->module->get_service( 'repository' );

		$filters = array();

		// Apply region filter if specified.
		if ( $region ) {
			$valid_regions = array( 'EU', 'UK', 'US-CA', 'BR', 'AU', 'CA', 'ZA', 'DEFAULT' );
			if ( in_array( strtoupper( $region ), $valid_regions, true ) ) {
				$filters['region'] = strtoupper( $region );
			}
		}

		// Apply date range filters.
		if ( $start_date ) {
			$filters['start_date'] = sanitize_text_field( $start_date );
		}
		if ( $end_date ) {
			$filters['end_date'] = sanitize_text_field( $end_date );
		}

		// Get all logs for region (pagination not needed for stats).
		$filters['per_page'] = 10000;
		$logs = $repository->get_logs( $filters );

		// Calculate statistics.
		$stats = array(
			'total_consents'     => 0,
			'total_rejections'   => 0,
			'acceptance_rate'    => 0,
			'by_region'          => array(),
			'by_mode'            => array(),
			'by_category'        => array(),
		);

		$acceptance_count = 0;

		foreach ( $logs as $log ) {
			$stats['total_consents']++;

			// Check if user accepted all categories.
			if ( isset( $log['categories'] ) && is_array( $log['categories'] ) ) {
				$accepted_any = false;
				foreach ( $log['categories'] as $accepted ) {
					if ( $accepted ) {
						$accepted_any = true;
						break;
					}
				}
				if ( $accepted_any ) {
					$acceptance_count++;
				}
			}

			// Count by region.
			$region_key = isset( $log['region'] ) ? $log['region'] : 'UNKNOWN';
			if ( ! isset( $stats['by_region'][ $region_key ] ) ) {
				$stats['by_region'][ $region_key ] = 0;
			}
			$stats['by_region'][ $region_key ]++;

			// Count by mode (if available in metadata).
			if ( isset( $log['metadata'] ) && is_array( $log['metadata'] ) ) {
				$mode = $log['metadata']['mode'] ?? 'unknown';
				if ( ! isset( $stats['by_mode'][ $mode ] ) ) {
					$stats['by_mode'][ $mode ] = 0;
				}
				$stats['by_mode'][ $mode ]++;
			}

			// Count by category.
			if ( isset( $log['categories'] ) && is_array( $log['categories'] ) ) {
				foreach ( $log['categories'] as $category => $value ) {
					if ( ! isset( $stats['by_category'][ $category ] ) ) {
						$stats['by_category'][ $category ] = array(
							'accepted' => 0,
							'rejected' => 0,
						);
					}
					if ( $value ) {
						$stats['by_category'][ $category ]['accepted']++;
					} else {
						$stats['by_category'][ $category ]['rejected']++;
					}
				}
			}
		}

		// Calculate acceptance rate.
		if ( $stats['total_consents'] > 0 ) {
			$stats['acceptance_rate'] = round( ( $acceptance_count / $stats['total_consents'] ) * 100, 2 );
			$stats['total_rejections'] = $stats['total_consents'] - $acceptance_count;
		}

		return new WP_REST_Response( $stats );
	}

	/**
	 * Sanitize and validate settings.
	 *
	 * @param array $settings Settings to sanitize.
	 *
	 * @return array Sanitized settings.
	 */
	private function sanitize_settings( array $settings ): array {
		// Sanitize banner text.
		if ( isset( $settings['banner']['text'] ) ) {
			$settings['banner']['text'] = array_map( 'sanitize_text_field', $settings['banner']['text'] );
		}

		// Sanitize colors.
		if ( isset( $settings['banner']['colors'] ) ) {
			$settings['banner']['colors'] = array_map(
				function( $color ) {
					return sanitize_hex_color( $color );
				},
				$settings['banner']['colors']
			);
		}

		// More sanitization rules as needed.

		return $settings;
	}
}
