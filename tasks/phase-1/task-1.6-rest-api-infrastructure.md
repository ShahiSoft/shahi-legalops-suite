# TASK 1.6: Create REST API Infrastructure

**Phase:** 1 (Infrastructure & Database)  
**Effort:** 6-8 hours  
**Prerequisites:** TASK 1.5 complete (Base_Service exists)  
**Next Task:** [task-1.7-wordpress-hooks.md](task-1.7-wordpress-hooks.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 1.6 for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
Services layer exists (Task 1.5 complete). Now create the REST API infrastructure that all
endpoints will use: base controller class, authentication, permission checks, request/response
formatting, error handling, and API registration system.

This establishes the foundation for all REST API endpoints in the plugin.

INPUT STATE (verify these exist):
✅ Base_Service exists at includes/Services/Base_Service.php
✅ WordPress REST API available
✅ Namespace structure: Shahi\LegalOps\API

YOUR TASK:

1. **Create Base REST Controller**

Location: `includes/API/Base_REST_Controller.php`

```php
<?php
/**
 * Base REST Controller
 * Foundation for all REST API endpoints.
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

namespace Shahi\LegalOps\API;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

abstract class Base_REST_Controller extends WP_REST_Controller {
    /**
     * API namespace
     *
     * @var string
     */
    protected $namespace = 'slos/v1';

    /**
     * Route base (e.g., 'consents', 'dsr-requests')
     *
     * @var string
     */
    protected $rest_base;

    /**
     * Register routes (must be implemented by child classes)
     *
     * @return void
     */
    abstract public function register_routes();

    /**
     * Check if user can read
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error True if has permission, error otherwise
     */
    public function get_items_permissions_check( $request ) {
        return $this->check_permission( 'read' );
    }

    /**
     * Check if user can read single item
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error True if has permission, error otherwise
     */
    public function get_item_permissions_check( $request ) {
        return $this->check_permission( 'read' );
    }

    /**
     * Check if user can create
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error True if has permission, error otherwise
     */
    public function create_item_permissions_check( $request ) {
        return $this->check_permission( 'create' );
    }

    /**
     * Check if user can update
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error True if has permission, error otherwise
     */
    public function update_item_permissions_check( $request ) {
        return $this->check_permission( 'edit' );
    }

    /**
     * Check if user can delete
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error True if has permission, error otherwise
     */
    public function delete_item_permissions_check( $request ) {
        return $this->check_permission( 'delete' );
    }

    /**
     * Check permission
     *
     * @param string $action Action type (read, create, edit, delete)
     * @return bool|WP_Error True if has permission, error otherwise
     */
    protected function check_permission( string $action ) {
        // Admin can do everything
        if ( current_user_can( 'manage_options' ) ) {
            return true;
        }

        // Map actions to capabilities
        $capability_map = [
            'read'   => 'slos_read_data',
            'create' => 'slos_create_data',
            'edit'   => 'slos_edit_data',
            'delete' => 'slos_delete_data',
        ];

        $capability = $capability_map[ $action ] ?? 'read';

        if ( ! current_user_can( $capability ) ) {
            return new WP_Error(
                'rest_forbidden',
                __( 'Sorry, you are not allowed to do that.', 'shahi-legalops-suite' ),
                [ 'status' => rest_authorization_required_code() ]
            );
        }

        return true;
    }

    /**
     * Prepare response for collection
     *
     * @param WP_REST_Response $response Response object
     * @return WP_REST_Response Modified response
     */
    protected function prepare_response_for_collection( $response ) {
        if ( ! ( $response instanceof WP_REST_Response ) ) {
            return $response;
        }

        $data = (array) $response->get_data();
        $links = rest_get_server()->get_compact_response_links( $response );

        if ( ! empty( $links ) ) {
            $data['_links'] = $links;
        }

        return $data;
    }

    /**
     * Get collection params
     *
     * @return array Collection parameters
     */
    public function get_collection_params(): array {
        return [
            'page' => [
                'description'       => 'Current page of the collection.',
                'type'              => 'integer',
                'default'           => 1,
                'sanitize_callback' => 'absint',
                'validate_callback' => function( $param ) {
                    return is_numeric( $param ) && $param > 0;
                },
            ],
            'per_page' => [
                'description'       => 'Maximum number of items per page.',
                'type'              => 'integer',
                'default'           => 10,
                'sanitize_callback' => 'absint',
                'validate_callback' => function( $param ) {
                    return is_numeric( $param ) && $param > 0 && $param <= 100;
                },
            ],
            'search' => [
                'description'       => 'Search term.',
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'order_by' => [
                'description'       => 'Order by field.',
                'type'              => 'string',
                'default'           => 'id',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'order' => [
                'description'       => 'Order direction (ASC or DESC).',
                'type'              => 'string',
                'default'           => 'DESC',
                'enum'              => [ 'ASC', 'DESC' ],
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ];
    }

    /**
     * Prepare item for database
     *
     * @param WP_REST_Request $request Request object
     * @return array Prepared data
     */
    protected function prepare_item_for_database( $request ): array {
        $prepared = [];

        foreach ( $request->get_params() as $key => $value ) {
            if ( is_string( $value ) ) {
                $prepared[ $key ] = sanitize_text_field( $value );
            } elseif ( is_array( $value ) ) {
                $prepared[ $key ] = array_map( 'sanitize_text_field', $value );
            } else {
                $prepared[ $key ] = $value;
            }
        }

        return $prepared;
    }

    /**
     * Create success response
     *
     * @param mixed  $data    Response data
     * @param int    $status  HTTP status code
     * @param string $message Success message
     * @return WP_REST_Response Response object
     */
    protected function success_response( $data = null, int $status = 200, string $message = '' ): WP_REST_Response {
        $response = [
            'success' => true,
        ];

        if ( $message ) {
            $response['message'] = $message;
        }

        if ( null !== $data ) {
            $response['data'] = $data;
        }

        return new WP_REST_Response( $response, $status );
    }

    /**
     * Create error response
     *
     * @param string $code    Error code
     * @param string $message Error message
     * @param int    $status  HTTP status code
     * @param array  $data    Additional error data
     * @return WP_Error Error object
     */
    protected function error_response( string $code, string $message, int $status = 400, array $data = [] ): WP_Error {
        $data['status'] = $status;
        return new WP_Error( $code, $message, $data );
    }

    /**
     * Validate ID parameter
     *
     * @param int $id Item ID
     * @return true|WP_Error True if valid, error otherwise
     */
    protected function validate_id( $id ) {
        if ( ! is_numeric( $id ) || $id <= 0 ) {
            return $this->error_response(
                'invalid_id',
                'Invalid ID parameter',
                400
            );
        }
        return true;
    }

    /**
     * Log API request
     *
     * @param WP_REST_Request $request Request object
     * @param string          $action  Action being performed
     * @return void
     */
    protected function log_request( WP_REST_Request $request, string $action ): void {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( sprintf(
                '[SLOS API] %s %s by user %d',
                $action,
                $request->get_route(),
                get_current_user_id()
            ) );
        }
    }

    /**
     * Add pagination headers
     *
     * @param WP_REST_Response $response    Response object
     * @param WP_REST_Request  $request     Request object
     * @param int              $total       Total items
     * @param int              $total_pages Total pages
     * @return WP_REST_Response Modified response
     */
    protected function add_pagination_headers( WP_REST_Response $response, WP_REST_Request $request, int $total, int $total_pages ): WP_REST_Response {
        $response->header( 'X-WP-Total', $total );
        $response->header( 'X-WP-TotalPages', $total_pages );

        $page = $request->get_param( 'page' ) ?? 1;
        $per_page = $request->get_param( 'per_page' ) ?? 10;

        $base_url = rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) );

        // Add pagination links
        if ( $page > 1 ) {
            $prev_url = add_query_arg( 'page', $page - 1, $base_url );
            $response->link_header( 'prev', $prev_url );
        }

        if ( $page < $total_pages ) {
            $next_url = add_query_arg( 'page', $page + 1, $base_url );
            $response->link_header( 'next', $next_url );
        }

        return $response;
    }

    /**
     * Get schema for item
     * Should be implemented by child classes
     *
     * @return array Item schema
     */
    public function get_item_schema(): array {
        return [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => $this->rest_base,
            'type'       => 'object',
            'properties' => [],
        ];
    }
}
```

2. **Create API Registration Class**

Location: `includes/API/REST_API.php`

```php
<?php
/**
 * REST API Registration
 * Registers all REST API routes.
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

namespace Shahi\LegalOps\API;

class REST_API {
    /**
     * Controllers to register
     *
     * @var array
     */
    private $controllers = [];

    /**
     * Initialize REST API
     *
     * @return void
     */
    public function init(): void {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
        add_filter( 'rest_pre_serve_request', [ $this, 'add_cors_headers' ] );
    }

    /**
     * Register controller
     *
     * @param string $controller_class Controller class name
     * @return void
     */
    public function register_controller( string $controller_class ): void {
        $this->controllers[] = $controller_class;
    }

    /**
     * Register all routes
     *
     * @return void
     */
    public function register_routes(): void {
        foreach ( $this->controllers as $controller_class ) {
            if ( class_exists( $controller_class ) ) {
                $controller = new $controller_class();
                $controller->register_routes();
            }
        }
    }

    /**
     * Add CORS headers
     *
     * @param bool $served Whether request has been served
     * @return bool Modified served status
     */
    public function add_cors_headers( $served ): bool {
        header( 'Access-Control-Allow-Origin: *' );
        header( 'Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS' );
        header( 'Access-Control-Allow-Headers: Content-Type, Authorization' );
        header( 'Access-Control-Allow-Credentials: true' );

        return $served;
    }
}
```

3. **Create Example Controller**

Location: `includes/API/Controllers/Health_Controller.php`

```php
<?php
/**
 * Health Check Controller
 * Simple endpoint to verify API is working.
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

namespace Shahi\LegalOps\API\Controllers;

use Shahi\LegalOps\API\Base_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Health_Controller extends Base_REST_Controller {
    /**
     * Route base
     *
     * @var string
     */
    protected $rest_base = 'health';

    /**
     * Register routes
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_health' ],
                    'permission_callback' => '__return_true', // Public endpoint
                ],
            ]
        );
    }

    /**
     * Get health status
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function get_health( WP_REST_Request $request ): WP_REST_Response {
        global $wpdb;

        $status = [
            'status' => 'healthy',
            'timestamp' => current_time( 'mysql' ),
            'version' => '3.0.1',
            'checks' => [
                'database' => $this->check_database(),
                'wordpress' => $this->check_wordpress(),
                'plugin' => $this->check_plugin(),
            ],
        ];

        return $this->success_response( $status );
    }

    /**
     * Check database connection
     *
     * @return array Check result
     */
    private function check_database(): array {
        global $wpdb;

        try {
            $result = $wpdb->get_var( 'SELECT 1' );
            return [
                'status' => 'ok',
                'connected' => true,
            ];
        } catch ( \Exception $e ) {
            return [
                'status' => 'error',
                'connected' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check WordPress
     *
     * @return array Check result
     */
    private function check_wordpress(): array {
        return [
            'status' => 'ok',
            'version' => get_bloginfo( 'version' ),
            'multisite' => is_multisite(),
        ];
    }

    /**
     * Check plugin
     *
     * @return array Check result
     */
    private function check_plugin(): array {
        return [
            'status' => 'ok',
            'active' => true,
            'version' => '3.0.1',
        ];
    }
}
```

4. **Update composer.json**

Add to psr-4:
```json
"Shahi\\LegalOps\\API\\": "includes/API/",
"Shahi\\LegalOps\\API\\Controllers\\": "includes/API/Controllers/"
```

Run:
```bash
composer dump-autoload
```

5. **Initialize API in main plugin file**

Add to `shahi-legalops-suite.php`:

```php
// Initialize REST API
$rest_api = new \Shahi\LegalOps\API\REST_API();
$rest_api->register_controller( \Shahi\LegalOps\API\Controllers\Health_Controller::class );
$rest_api->init();
```

6. **Test API**

```bash
# Test health endpoint
wp eval 'echo wp_remote_retrieve_body(wp_remote_get(rest_url("slos/v1/health")));'

# Or use curl
curl http://localhost/wp-json/slos/v1/health
```

OUTPUT STATE:
✅ Base_REST_Controller with authentication, permissions, utilities
✅ REST_API registration system
✅ Health_Controller example endpoint
✅ CORS headers configured
✅ Pagination support
✅ Error handling infrastructure
✅ API namespace: slos/v1

VERIFICATION:

1. **Check files:**
```bash
ls -la includes/API/
ls -la includes/API/Controllers/
```

2. **Check syntax:**
```bash
php -l includes/API/Base_REST_Controller.php
php -l includes/API/REST_API.php
php -l includes/API/Controllers/Health_Controller.php
```

3. **Test health endpoint:**
```bash
wp eval 'echo wp_remote_retrieve_body(wp_remote_get(rest_url("slos/v1/health")));'
```
Expected: JSON with status: healthy

4. **Check routes registered:**
```bash
wp rest route list | grep slos
```
Expected: slos/v1/health

5. **Test with curl:**
```bash
curl -X GET "http://localhost/wp-json/slos/v1/health"
```

SUCCESS CRITERIA:
✅ Base_REST_Controller created
✅ REST_API class created
✅ Health endpoint working
✅ Routes registered
✅ CORS headers present
✅ Authentication working

ROLLBACK:
```bash
rm -rf includes/API/
git checkout shahi-legalops-suite.php composer.json
composer dump-autoload
```

TROUBLESHOOTING:

**Problem 1: Routes not found**
```bash
wp rewrite flush
wp rest route list | grep slos
```

**Problem 2: Permission denied**
```bash
# Check current user
wp eval 'echo "User ID: " . get_current_user_id();'

# Test as admin
wp eval --user=admin 'echo wp_remote_retrieve_body(wp_remote_get(rest_url("slos/v1/health")));'
```

**Problem 3: CORS errors**
Check headers:
```bash
curl -I http://localhost/wp-json/slos/v1/health
```

COMMIT MESSAGE:
```
feat(api): Create REST API infrastructure

- Add Base_REST_Controller with auth, permissions, utilities
- Create REST_API registration system
- Implement Health_Controller example endpoint
- Add CORS headers support
- Implement pagination helpers
- Add error/success response formatting
- Create API namespace: slos/v1

REST API foundation ready.

Task: 1.6 (6-8 hours)
Next: Task 1.7 - WordPress Hooks Integration
```

WHAT TO REPORT BACK:
"✅ TASK 1.6 COMPLETE

Created:
- Base_REST_Controller (authentication, permissions)
- REST_API registration system
- Health_Controller (example endpoint)

Implemented:
- ✅ API namespace: slos/v1
- ✅ Permission checks (read, create, edit, delete)
- ✅ CORS headers
- ✅ Pagination support
- ✅ Error/success formatting
- ✅ Request logging

Verification passed:
- ✅ Health endpoint responding
- ✅ Routes registered
- ✅ Authentication working
- ✅ Ready for endpoint implementation

📍 Ready for TASK 1.7: [task-1.7-wordpress-hooks.md](task-1.7-wordpress-hooks.md)"
```

---

## ✅ COMPLETION CHECKLIST

- [ ] Base_REST_Controller created
- [ ] REST_API class created
- [ ] Health_Controller working
- [ ] Routes registered
- [ ] CORS configured
- [ ] Tests passing
- [ ] Committed to git
- [ ] Ready for Task 1.7

---

**Status:** ✅ Ready to execute  
**Time:** 6-8 hours  
**Next:** [task-1.7-wordpress-hooks.md](task-1.7-wordpress-hooks.md)
