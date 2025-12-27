# TASK 2.3: Consent REST API Endpoints

**Phase:** 2 (Consent Management - CORE)  
**Effort:** 6-8 hours  
**Prerequisites:** TASK 2.2 complete (Consent_Service exists)  
**Next Task:** [task-2.4-consent-banner.md](task-2.4-consent-banner.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 2.3 for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
Consent Service exists (Task 2.2 complete). Now create REST API endpoints that expose
consent functionality: grant consent, withdraw consent, get user consents, check consent
status, get statistics. These endpoints will be used by the frontend banner and admin dashboard.

INPUT STATE (verify these exist):
✅ Consent_Service at includes/Services/Consent_Service.php
✅ Base_REST_Controller at includes/API/Base_REST_Controller.php
✅ REST API infrastructure working

YOUR TASK:

1. **Create Consent REST Controller**

Location: `includes/API/Controllers/Consent_Controller.php`

```php
<?php
/**
 * Consent REST API Controller
 * Handles consent management endpoints.
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

namespace Shahi\LegalOps\API\Controllers;

use Shahi\LegalOps\API\Base_REST_Controller;
use Shahi\LegalOps\Services\Consent_Service;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Consent_Controller extends Base_REST_Controller {
    /**
     * Route base
     *
     * @var string
     */
    protected $rest_base = 'consents';

    /**
     * Consent service
     *
     * @var Consent_Service
     */
    private $service;

    /**
     * Constructor
     */
    public function __construct() {
        $this->service = new Consent_Service();
    }

    /**
     * Register routes
     *
     * @return void
     */
    public function register_routes() {
        // GET /consents - Get all consents
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_items' ],
                    'permission_callback' => [ $this, 'get_items_permissions_check' ],
                    'args'                => $this->get_collection_params(),
                ],
            ]
        );

        // GET /consents/user/:user_id - Get user consents
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/user/(?P<user_id>[\d]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_user_consents' ],
                    'permission_callback' => [ $this, 'get_user_consents_permissions_check' ],
                    'args'                => [
                        'user_id' => [
                            'description' => 'User ID',
                            'type'        => 'integer',
                            'required'    => true,
                        ],
                    ],
                ],
            ]
        );

        // POST /consents/grant - Grant consent
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/grant',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'grant_consent' ],
                    'permission_callback' => '__return_true', // Public endpoint
                    'args'                => [
                        'user_id' => [
                            'description'       => 'User ID (0 for anonymous)',
                            'type'              => 'integer',
                            'required'          => true,
                            'sanitize_callback' => 'absint',
                        ],
                        'purpose' => [
                            'description'       => 'Consent purpose',
                            'type'              => 'string',
                            'required'          => true,
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                        'consent_text' => [
                            'description'       => 'Consent text shown to user',
                            'type'              => 'string',
                            'sanitize_callback' => 'wp_kses_post',
                        ],
                        'consent_method' => [
                            'description'       => 'How consent was obtained',
                            'type'              => 'string',
                            'enum'              => [ 'explicit', 'implicit', 'legitimate_interest' ],
                            'default'           => 'explicit',
                        ],
                    ],
                ],
            ]
        );

        // POST /consents/withdraw - Withdraw consent
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/withdraw',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'withdraw_consent' ],
                    'permission_callback' => '__return_true', // Public endpoint
                    'args'                => [
                        'user_id' => [
                            'description'       => 'User ID',
                            'type'              => 'integer',
                            'required'          => true,
                            'sanitize_callback' => 'absint',
                        ],
                        'purpose' => [
                            'description'       => 'Consent purpose',
                            'type'              => 'string',
                            'required'          => true,
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                        'reason' => [
                            'description'       => 'Withdrawal reason',
                            'type'              => 'string',
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                    ],
                ],
            ]
        );

        // GET /consents/check/:user_id/:purpose - Check consent
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/check/(?P<user_id>[\d]+)/(?P<purpose>[\w-]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'check_consent' ],
                    'permission_callback' => '__return_true', // Public endpoint
                    'args'                => [
                        'user_id' => [
                            'description' => 'User ID',
                            'type'        => 'integer',
                            'required'    => true,
                        ],
                        'purpose' => [
                            'description' => 'Consent purpose',
                            'type'        => 'string',
                            'required'    => true,
                        ],
                    ],
                ],
            ]
        );

        // GET /consents/statistics - Get statistics
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/statistics',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_statistics' ],
                    'permission_callback' => [ $this, 'check_admin_permission' ],
                    'args'                => [
                        'start_date' => [
                            'description'       => 'Start date (Y-m-d)',
                            'type'              => 'string',
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                        'end_date' => [
                            'description'       => 'End date (Y-m-d)',
                            'type'              => 'string',
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                        'purpose' => [
                            'description'       => 'Filter by purpose',
                            'type'              => 'string',
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                    ],
                ],
            ]
        );

        // GET /consents/purposes - Get valid purposes
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/purposes',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_purposes' ],
                    'permission_callback' => '__return_true', // Public endpoint
                ],
            ]
        );

        // POST /consents/export/:user_id - Export user data (GDPR)
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/export/(?P<user_id>[\d]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'export_user_data' ],
                    'permission_callback' => [ $this, 'check_user_or_admin' ],
                    'args'                => [
                        'user_id' => [
                            'description' => 'User ID',
                            'type'        => 'integer',
                            'required'    => true,
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Get all consents (admin only)
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function get_items( $request ): WP_REST_Response {
        // This would return all consents with pagination
        // For now, return empty array
        return $this->success_response( [] );
    }

    /**
     * Get user consents
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response or error
     */
    public function get_user_consents( $request ) {
        $user_id = (int) $request->get_param( 'user_id' );

        $consents = $this->service->get_user_consents( $user_id );

        return $this->success_response( $consents );
    }

    /**
     * Grant consent
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response or error
     */
    public function grant_consent( $request ) {
        $user_id = (int) $request->get_param( 'user_id' );
        $purpose = $request->get_param( 'purpose' );
        $args = [
            'consent_text'   => $request->get_param( 'consent_text' ),
            'consent_method' => $request->get_param( 'consent_method' ),
        ];

        $result = $this->service->grant_consent( $user_id, $purpose, $args );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return $this->success_response(
            [ 'consent_id' => $result ],
            201,
            'Consent granted successfully'
        );
    }

    /**
     * Withdraw consent
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response or error
     */
    public function withdraw_consent( $request ) {
        $user_id = (int) $request->get_param( 'user_id' );
        $purpose = $request->get_param( 'purpose' );
        $reason = $request->get_param( 'reason' );

        $result = $this->service->withdraw_consent( $user_id, $purpose, $reason );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return $this->success_response(
            null,
            200,
            'Consent withdrawn successfully'
        );
    }

    /**
     * Check if user has consent
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function check_consent( $request ): WP_REST_Response {
        $user_id = (int) $request->get_param( 'user_id' );
        $purpose = $request->get_param( 'purpose' );

        $has_consent = $this->service->has_consent( $user_id, $purpose );

        return $this->success_response( [
            'has_consent' => $has_consent,
            'user_id'     => $user_id,
            'purpose'     => $purpose,
        ] );
    }

    /**
     * Get statistics
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function get_statistics( $request ): WP_REST_Response {
        $args = [
            'start_date' => $request->get_param( 'start_date' ),
            'end_date'   => $request->get_param( 'end_date' ),
            'purpose'    => $request->get_param( 'purpose' ),
        ];

        $stats = $this->service->get_statistics( $args );
        $breakdown = $this->service->get_purpose_breakdown();

        return $this->success_response( [
            'stats'     => $stats,
            'breakdown' => $breakdown,
        ] );
    }

    /**
     * Get valid purposes
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function get_purposes( $request ): WP_REST_Response {
        $purposes = $this->service->get_valid_purposes();

        return $this->success_response( [
            'purposes' => $purposes,
        ] );
    }

    /**
     * Export user consent data (GDPR)
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function export_user_data( $request ): WP_REST_Response {
        $user_id = (int) $request->get_param( 'user_id' );

        $export = $this->service->export_user_data( $user_id );

        return $this->success_response( [
            'user_id' => $user_id,
            'consents' => $export,
            'exported_at' => current_time( 'mysql' ),
        ] );
    }

    /**
     * Check user consents permission
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error True if allowed
     */
    public function get_user_consents_permissions_check( $request ) {
        $user_id = (int) $request->get_param( 'user_id' );
        $current_user = get_current_user_id();

        // User can view their own or admin can view any
        if ( $user_id === $current_user || current_user_can( 'manage_options' ) ) {
            return true;
        }

        return $this->error_response(
            'rest_forbidden',
            'You are not allowed to view these consents',
            403
        );
    }

    /**
     * Check if current user is admin
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error True if admin
     */
    public function check_admin_permission( $request ) {
        if ( current_user_can( 'slos_view_analytics' ) ) {
            return true;
        }

        return $this->error_response(
            'rest_forbidden',
            'Admin access required',
            403
        );
    }

    /**
     * Check if current user is the user or admin
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error True if allowed
     */
    public function check_user_or_admin( $request ) {
        $user_id = (int) $request->get_param( 'user_id' );
        $current_user = get_current_user_id();

        if ( $user_id === $current_user || current_user_can( 'manage_options' ) ) {
            return true;
        }

        return $this->error_response(
            'rest_forbidden',
            'You are not allowed to access this data',
            403
        );
    }
}
```

2. **Register controller in main plugin file**

Update `shahi-legalops-suite.php`:

```php
// Initialize REST API
$rest_api = new API\REST_API();
$rest_api->register_controller( API\Controllers\Health_Controller::class );
$rest_api->register_controller( API\Controllers\Consent_Controller::class ); // Add this
$rest_api->init();
```

3. **Test all endpoints**

```bash
# 1. Get valid purposes
curl http://localhost/wp-json/slos/v1/consents/purposes

# 2. Grant consent
curl -X POST http://localhost/wp-json/slos/v1/consents/grant \
  -H "Content-Type: application/json" \
  -d '{"user_id":1,"purpose":"analytics","consent_text":"I agree"}'

# 3. Check consent
curl http://localhost/wp-json/slos/v1/consents/check/1/analytics

# 4. Get user consents (requires auth)
wp eval 'echo wp_remote_retrieve_body(wp_remote_get(rest_url("slos/v1/consents/user/1")));'

# 5. Withdraw consent
curl -X POST http://localhost/wp-json/slos/v1/consents/withdraw \
  -H "Content-Type: application/json" \
  -d '{"user_id":1,"purpose":"analytics","reason":"Changed mind"}'

# 6. Get statistics (requires admin)
wp eval --user=admin 'echo wp_remote_retrieve_body(wp_remote_get(rest_url("slos/v1/consents/statistics")));'

# 7. Export user data
wp eval --user=admin 'echo wp_remote_retrieve_body(wp_remote_get(rest_url("slos/v1/consents/export/1")));'
```

OUTPUT STATE:
✅ Complete Consent REST API (8 endpoints)
✅ Grant/withdraw consent endpoints
✅ Check consent status endpoint
✅ User consents endpoint
✅ Statistics endpoint
✅ GDPR export endpoint
✅ Proper authentication/authorization
✅ All CRUD operations exposed

VERIFICATION:

1. **List routes:**
```bash
wp rest route list | grep consents
```
Expected: 8 routes

2. **Test grant consent:**
```bash
wp eval '
$response = wp_remote_post(rest_url("slos/v1/consents/grant"), [
    "body" => json_encode([
        "user_id" => 1,
        "purpose" => "analytics",
        "consent_text" => "Test"
    ]),
    "headers" => ["Content-Type" => "application/json"]
]);
echo wp_remote_retrieve_body($response);
'
```

3. **Test check consent:**
```bash
curl http://localhost/wp-json/slos/v1/consents/check/1/analytics
```

4. **Test purposes:**
```bash
curl http://localhost/wp-json/slos/v1/consents/purposes
```

SUCCESS CRITERIA:
✅ All 8 endpoints registered
✅ Grant consent works
✅ Withdraw consent works
✅ Check consent works
✅ Statistics work
✅ Proper authentication
✅ GDPR export works

ROLLBACK:
```bash
rm includes/API/Controllers/Consent_Controller.php
git checkout shahi-legalops-suite.php
```

TROUBLESHOOTING:

**Problem 1: Routes not found**
```bash
wp rewrite flush
wp rest route list | grep slos
```

**Problem 2: Permission denied**
```bash
wp eval --user=admin 'echo wp_remote_retrieve_body(wp_remote_get(rest_url("slos/v1/consents/statistics")));'
```

**Problem 3: Service errors**
```bash
tail -f wp-content/debug.log
```

COMMIT MESSAGE:
```
feat(api): Add Consent REST API endpoints

- Create Consent_Controller with 8 endpoints
- Implement grant/withdraw consent
- Add check consent status
- Add get user consents
- Implement statistics endpoint
- Add GDPR export endpoint
- Proper authentication/authorization
- Public and protected endpoints

Consent API complete.

Task: 2.3 (6-8 hours)
Next: Task 2.4 - Consent Banner UI
```

WHAT TO REPORT BACK:
"✅ TASK 2.3 COMPLETE

Created:
- Consent_Controller with 8 REST API endpoints

Endpoints implemented:
- ✅ POST /consents/grant (public)
- ✅ POST /consents/withdraw (public)
- ✅ GET /consents/check/:user_id/:purpose (public)
- ✅ GET /consents/user/:user_id (auth required)
- ✅ GET /consents/purposes (public)
- ✅ GET /consents/statistics (admin only)
- ✅ GET /consents/export/:user_id (user or admin)
- ✅ GET /consents (admin only)

Verification passed:
- ✅ All routes registered
- ✅ Grant consent working
- ✅ Withdraw working
- ✅ Authentication working
- ✅ GDPR export working

📍 Ready for TASK 2.4: [task-2.4-consent-banner.md](task-2.4-consent-banner.md)"
```

---

## ✅ COMPLETION CHECKLIST

- [ ] Consent_Controller created
- [ ] All 8 endpoints implemented
- [ ] Routes registered
- [ ] Authentication working
- [ ] All endpoints tested
- [ ] GDPR compliance verified
- [ ] Committed to git
- [ ] Ready for Task 2.4

---

**Status:** ✅ Ready to execute  
**Time:** 6-8 hours  
**Next:** [task-2.4-consent-banner.md](task-2.4-consent-banner.md)
