# TASK 3.3: DSR REST API

**Phase:** 3 (DSR Portal)  
**Effort:** 6-8 hours  
**Prerequisites:** Task 3.2 service  
**Next Task:** [task-3.4-dsr-request-form.md](task-3.4-dsr-request-form.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 3.3 for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
Expose DSR operations via REST API with public endpoints for submission/verification
and protected endpoints for admin management. Support all 7 request types and regulation-aware responses.

References: /v3docs/modules/02-DSR-IMPLEMENTATION.md - Request workflow

INPUT STATE (verify these exist):
✅ DSR_Service with all 7 request types (Task 3.2)
✅ DSR_Repository (Task 3.1)
✅ Base_REST_Controller (Task 1.6)
✅ Verification service (Task 3.5)

YOUR TASK:

1) **Create DSR REST Controller**

File: `includes/API/DSR_Controller.php`

```php
<?php
namespace Shahi\LegalOps\API;

use Shahi\LegalOps\Core\Base_REST_Controller;

class DSR_Controller extends Base_REST_Controller {
    protected $namespace = 'slos/v1';
    protected $rest_base = 'dsr';
    private $service;
    private $repository;
    
    public function __construct( $service, $repository ) {
        $this->service = $service;
        $this->repository = $repository;
    }
    
    public function register_routes() {
        // Public: Submit request
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/submit', [
            'methods' => 'POST',
            'callback' => [ $this, 'submit_request' ],
            'permission_callback' => '__return_true',
            'args' => [
                'email' => ['required' => true, 'type' => 'string', 'format' => 'email'],
                'request_type' => ['required' => true, 'enum' => ['access', 'rectification', 'erasure', 'portability', 'restriction', 'object', 'automated_decision']],
                'regulation' => ['default' => 'GDPR', 'enum' => ['GDPR', 'CCPA', 'LGPD', 'UK-GDPR', 'PIPEDA', 'POPIA']],
                'details' => ['type' => 'string'],
            ],
        ] );
        
        // Public: Verify email
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/verify', [
            'methods' => 'GET',
            'callback' => [ $this, 'verify_email' ],
            'permission_callback' => '__return_true',
            'args' => [
                'token' => ['required' => true, 'type' => 'string'],
            ],
        ] );
        
        // Public: Check status
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/status', [
            'methods' => 'GET',
            'callback' => [ $this, 'check_status' ],
            'permission_callback' => '__return_true',
            'args' => [
                'token' => ['required' => true, 'type' => 'string'],
            ],
        ] );
        
        // Admin: List requests
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            'methods' => 'GET',
            'callback' => [ $this, 'list_requests' ],
            'permission_callback' => [ $this, 'check_admin_permission' ],
            'args' => [
                'status' => ['type' => 'string'],
                'request_type' => ['type' => 'string'],
                'regulation' => ['type' => 'string'],
                'page' => ['default' => 1, 'type' => 'integer'],
                'per_page' => ['default' => 20, 'type' => 'integer'],
            ],
        ] );
        
        // Admin: Get single request
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [ $this, 'get_request' ],
            'permission_callback' => [ $this, 'check_admin_permission' ],
        ] );
        
        // Admin: Update status
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>\d+)/status', [
            'methods' => 'POST',
            'callback' => [ $this, 'update_status' ],
            'permission_callback' => [ $this, 'check_admin_permission' ],
            'args' => [
                'status' => ['required' => true, 'enum' => ['verified', 'in_progress', 'completed', 'rejected']],
                'notes' => ['type' => 'string'],
            ],
        ] );
        
        // Admin: Download export
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>\d+)/export', [
            'methods' => 'GET',
            'callback' => [ $this, 'download_export' ],
            'permission_callback' => [ $this, 'check_admin_permission' ],
        ] );
    }
    
    public function submit_request( \WP_REST_Request $request ) {
        $result = $this->service->submit_request( $request->get_params() );
        
        if ( is_wp_error( $result ) ) {
            return new \WP_REST_Response( [
                'success' => false,
                'message' => $result->get_error_message(),
            ], 400 );
        }
        
        $dsr_request = $this->repository->find( $result );
        
        return new \WP_REST_Response( [
            'success' => true,
            'message' => __( 'Request submitted successfully. Please check your email to verify.', 'shahi-legalops' ),
            'request_id' => $result,
            'due_date' => $dsr_request->due_date,
            'sla_days' => $dsr_request->sla_days,
        ], 201 );
    }
    
    public function check_status( \WP_REST_Request $request ) {
        $token = $request->get_param( 'token' );
        $dsr_request = $this->repository->find_by_token( $token );
        
        if ( ! $dsr_request ) {
            return new \WP_REST_Response( [
                'success' => false,
                'message' => __( 'Invalid or expired token.', 'shahi-legalops' ),
            ], 404 );
        }
        
        return new \WP_REST_Response( [
            'success' => true,
            'request' => [
                'id' => $dsr_request->id,
                'type' => $dsr_request->request_type,
                'status' => $dsr_request->status,
                'submitted_at' => $dsr_request->submitted_at,
                'due_date' => $dsr_request->due_date,
                'regulation' => $dsr_request->regulation,
            ],
        ], 200 );
    }
    
    public function list_requests( \WP_REST_Request $request ) {
        $filters = [
            'status' => $request->get_param( 'status' ),
            'request_type' => $request->get_param( 'request_type' ),
            'regulation' => $request->get_param( 'regulation' ),
        ];
        
        $page = $request->get_param( 'page' );
        $per_page = $request->get_param( 'per_page' );
        $offset = ( $page - 1 ) * $per_page;
        
        $requests = $this->repository->list_requests( $filters, $per_page, $offset );
        $stats = $this->repository->stats_by_status();
        
        return new \WP_REST_Response( [
            'requests' => $requests,
            'stats' => $stats,
            'page' => $page,
            'per_page' => $per_page,
        ], 200 );
    }
    
    public function get_request( \WP_REST_Request $request ) {
        $id = $request->get_param( 'id' );
        $dsr_request = $this->repository->find( $id );
        
        if ( ! $dsr_request ) {
            return new \WP_REST_Response( [
                'success' => false,
                'message' => __( 'Request not found.', 'shahi-legalops' ),
            ], 404 );
        }
        
        return new \WP_REST_Response( [
            'success' => true,
            'request' => $dsr_request,
        ], 200 );
    }
    
    public function update_status( \WP_REST_Request $request ) {
        $id = $request->get_param( 'id' );
        $status = $request->get_param( 'status' );
        $notes = $request->get_param( 'notes' );
        
        $updated = $this->repository->update_status( $id, $status, [
            'admin_notes' => $notes,
            'processed_by' => get_current_user_id(),
        ] );
        
        if ( ! $updated ) {
            return new \WP_REST_Response( [
                'success' => false,
                'message' => __( 'Failed to update status.', 'shahi-legalops' ),
            ], 400 );
        }
        
        return new \WP_REST_Response( [
            'success' => true,
            'message' => __( 'Status updated successfully.', 'shahi-legalops' ),
        ], 200 );
    }
    
    public function check_admin_permission() {
        return current_user_can( 'manage_options' ) || current_user_can( 'slos_manage_dsr' );
    }
}
```

2) **Register Controller**

File: `includes/Core/Container.php` (add):

```php
$this->register( 'dsr_controller', function() {
    $service = $this->get( 'dsr_service' );
    $repository = $this->get( 'dsr_repository' );
    return new \Shahi\LegalOps\API\DSR_Controller( $service, $repository );
} );
```

3) **Verification Tests**

```bash
# Test submit (public)
curl -X POST http://yoursite.local/wp-json/slos/v1/dsr/submit \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","request_type":"access","regulation":"GDPR"}'

# Get token from DB
wp db query "SELECT verification_token FROM wp_slos_dsr_requests WHERE email = 'user@example.com' ORDER BY id DESC LIMIT 1"

# Test status check (public)
curl "http://yoursite.local/wp-json/slos/v1/dsr/status?token=TOKEN"

# Test list (admin - get auth token first)
wp rest request /slos/v1/dsr --user=admin

# Test update status (admin)
curl -X POST http://yoursite.local/wp-json/slos/v1/dsr/1/status \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"status":"in_progress","notes":"Processing request"}'
```

OUTPUT STATE:
✅ REST controller with 7 routes
✅ Public endpoints: submit, verify, status
✅ Admin endpoints: list, get, update status, download
✅ Permission checks enforced
✅ Regulation-aware responses
✅ SLA/due_date in responses

SUCCESS CRITERIA:
✅ All routes registered and accessible
✅ Public endpoints work without auth
✅ Admin endpoints require manage_options or slos_manage_dsr
✅ Responses include due_date, regulation, sla_days
✅ Input validation via args
✅ Error handling with WP_Error

ROLLBACK:
```bash
rm includes/API/DSR_Controller.php
git checkout includes/Core/Container.php
```

COMMIT MESSAGE:
```
feat(dsr): add REST API with 7 endpoints

- Public: submit, verify, status check
- Admin: list, get, update, export
- All 7 request types supported
- Regulation-aware responses
- SLA tracking in JSON

Task: 3.3 (6-8 hours)
Next: Task 3.4 - Request Form
```
```
