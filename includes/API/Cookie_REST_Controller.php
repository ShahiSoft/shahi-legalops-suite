<?php
/**
 * Cookie Scanner REST Controller
 *
 * Registers endpoints for cookie categories, patterns, reporting scan results,
 * and retrieving/clearing the last inventory.
 *
 * @package     ShahiLegalopsSuite
 * @subpackage  API
 * @version     3.0.1
 */

namespace ShahiLegalopsSuite\API;

use WP_REST_Request;
use ShahiLegalopsSuite\Services\Cookie_Scanner_Service;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Cookie_REST_Controller extends Base_REST_Controller {

    /** @var Cookie_Scanner_Service */
    private $service;

    /**
     * Constructor
     */
    public function __construct() {
        $this->rest_base = 'cookies';
        $this->service   = new Cookie_Scanner_Service();
    }

    /**
     * Register routes
     */
    public function register_routes() {
        // GET /cookies/categories
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/categories',
            array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array( $this, 'get_categories' ),
                    'permission_callback' => array( $this, 'check_read_permission' ),
                ),
            )
        );

        // GET /cookies/patterns
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/patterns',
            array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array( $this, 'get_patterns' ),
                    'permission_callback' => array( $this, 'check_admin_permission' ),
                ),
            )
        );

        // POST /cookies/report
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/report',
            array(
                array(
                    'methods'             => 'POST',
                    'callback'            => array( $this, 'report_scan' ),
                    'permission_callback' => array( $this, 'check_create_permission' ),
                    'args'                => array(
                        'cookies' => array( 'required' => false ),
                        'localStorageKeys' => array( 'required' => false ),
                        'sessionStorageKeys' => array( 'required' => false ),
                        'url' => array( 'required' => false ),
                        'userAgent' => array( 'required' => false ),
                    ),
                ),
            )
        );

        // GET /cookies/inventory
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/inventory',
            array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array( $this, 'get_inventory' ),
                    'permission_callback' => array( $this, 'check_admin_permission' ),
                ),
                array(
                    'methods'             => 'DELETE',
                    'callback'            => array( $this, 'clear_inventory' ),
                    'permission_callback' => array( $this, 'check_admin_permission' ),
                ),
            )
        );
    }

    /**
     * Get categories
     */
    public function get_categories( WP_REST_Request $request ) {
        $cats = $this->service->get_categories();
        return $this->success_response( $cats );
    }

    /**
     * Get default patterns (admin only)
     */
    public function get_patterns( WP_REST_Request $request ) {
        $patterns = $this->service->get_default_patterns();
        return $this->success_response( $patterns );
    }

    /**
     * Report scan results from client
     */
    public function report_scan( WP_REST_Request $request ) {
        $payload = array(
            'cookies'            => $request->get_param( 'cookies' ),
            'localStorageKeys'   => $request->get_param( 'localStorageKeys' ),
            'sessionStorageKeys' => $request->get_param( 'sessionStorageKeys' ),
            'url'                => $request->get_param( 'url' ),
            'userAgent'          => $request->get_param( 'userAgent' ),
        );

        $processed = $this->service->process_scan_report( $payload );
        if ( $this->service->has_errors() ) {
            return $this->error_response( 'scan_error', __( 'Failed to process scan report', 'shahi-legalops-suite' ), 400, array( 'errors' => $this->service->get_errors() ) );
        }
        return $this->success_response( $processed, __( 'Scan report received', 'shahi-legalops-suite' ), 201 );
    }

    /**
     * Get last inventory
     */
    public function get_inventory( WP_REST_Request $request ) {
        $inv = $this->service->get_inventory();
        return $this->success_response( $inv );
    }

    /**
     * Clear stored inventory
     */
    public function clear_inventory( WP_REST_Request $request ) {
        $ok = $this->service->clear_inventory();
        if ( ! $ok ) {
            return $this->error_response( 'clear_failed', __( 'Failed to clear inventory', 'shahi-legalops-suite' ), 500 );
        }
        return $this->success_response( array( 'cleared' => true ), __( 'Inventory cleared', 'shahi-legalops-suite' ) );
    }
}
