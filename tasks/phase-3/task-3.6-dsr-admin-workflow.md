# TASK 3.6: DSR Admin Workflow

**Phase:** 3 (DSR Portal)  
**Effort:** 8-10 hours  
**Prerequisites:** Task 3.5 verification  
**Next Task:** [task-3.7-dsr-data-export.md](task-3.7-dsr-data-export.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 3.6 for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
Create admin UI for DSR management with filters, SLA indicators, bulk actions,
and status transitions for all 7 request types across regulations.

References: /v3docs/modules/02-DSR-IMPLEMENTATION.md - Team Management, Workflow

INPUT STATE (verify these exist):
✅ DSR_Repository (Task 3.1)
✅ DSR_Service (Task 3.2)
✅ REST API endpoints (Task 3.3)
✅ Verification service (Task 3.5)

YOUR TASK:

1) **Register Admin Menu**

File: `includes/Admin/DSR_Admin.php`

```php
<?php
namespace Shahi\LegalOps\Admin;

class DSR_Admin {
    private $repository;
    
    public function __construct( $repository ) {
        $this->repository = $repository;
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }
    
    public function register_menu() {
        add_menu_page(
            __( 'DSR Requests', 'shahi-legalops' ),
            __( 'DSR Requests', 'shahi-legalops' ),
            'slos_manage_dsr',
            'slos-dsr-requests',
            [ $this, 'render_list_page' ],
            'dashicons-shield-alt',
            26
        );
        
        add_submenu_page(
            'slos-dsr-requests',
            __( 'All Requests', 'shahi-legalops' ),
            __( 'All Requests', 'shahi-legalops' ),
            'slos_manage_dsr',
            'slos-dsr-requests',
            [ $this, 'render_list_page' ]
        );
    }
    
    public function render_list_page() {
        $status = $_GET['status'] ?? '';
        $request_type = $_GET['request_type'] ?? '';
        $regulation = $_GET['regulation'] ?? '';
        
        $requests = $this->repository->list_requests( [
            'status' => $status,
            'request_type' => $request_type,
            'regulation' => $regulation,
        ], 50, 0 );
        
        $stats = $this->repository->stats_by_status();
        
        include SLOS_PLUGIN_DIR . 'templates/admin/dsr-list.php';
    }
    
    public function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'slos-dsr' ) === false ) {
            return;
        }
        
        wp_enqueue_style(
            'slos-dsr-admin',
            SLOS_PLUGIN_URL . 'assets/css/admin-dsr.css',
            [],
            SLOS_VERSION
        );
        
        wp_enqueue_script(
            'slos-dsr-admin',
            SLOS_PLUGIN_URL . 'assets/js/admin-dsr.js',
            ['jquery'],
            SLOS_VERSION,
            true
        );
        
        wp_localize_script( 'slos-dsr-admin', 'slosDSR', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'restUrl' => rest_url( 'slos/v1/dsr' ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
        ] );
    }
}
```

2) **Create List Template**

File: `templates/admin/dsr-list.php`

```php
<div class="wrap slos-dsr-requests">
    <h1><?php _e( 'Data Subject Requests', 'shahi-legalops' ); ?></h1>
    
    <!-- Stats Summary -->
    <div class="slos-dsr-stats">
        <?php foreach ( $stats as $status => $data ) : ?>
            <div class="stat-card">
                <span class="count"><?php echo esc_html( $data->count ); ?></span>
                <span class="label"><?php echo esc_html( ucfirst( $status ) ); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Filters -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <select name="status" id="filter-status">
                <option value=""><?php _e( 'All Statuses', 'shahi-legalops' ); ?></option>
                <option value="pending_verification"><?php _e( 'Pending Verification', 'shahi-legalops' ); ?></option>
                <option value="verified"><?php _e( 'Verified', 'shahi-legalops' ); ?></option>
                <option value="in_progress"><?php _e( 'In Progress', 'shahi-legalops' ); ?></option>
                <option value="completed"><?php _e( 'Completed', 'shahi-legalops' ); ?></option>
                <option value="rejected"><?php _e( 'Rejected', 'shahi-legalops' ); ?></option>
            </select>
            
            <select name="request_type" id="filter-type">
                <option value=""><?php _e( 'All Types', 'shahi-legalops' ); ?></option>
                <option value="access"><?php _e( 'Access', 'shahi-legalops' ); ?></option>
                <option value="rectification"><?php _e( 'Rectification', 'shahi-legalops' ); ?></option>
                <option value="erasure"><?php _e( 'Erasure', 'shahi-legalops' ); ?></option>
                <option value="portability"><?php _e( 'Portability', 'shahi-legalops' ); ?></option>
                <option value="restriction"><?php _e( 'Restriction', 'shahi-legalops' ); ?></option>
                <option value="object"><?php _e( 'Object', 'shahi-legalops' ); ?></option>
                <option value="automated_decision"><?php _e( 'Automated Decision', 'shahi-legalops' ); ?></option>
            </select>
            
            <select name="regulation" id="filter-regulation">
                <option value=""><?php _e( 'All Regulations', 'shahi-legalops' ); ?></option>
                <option value="GDPR">GDPR</option>
                <option value="CCPA">CCPA</option>
                <option value="LGPD">LGPD</option>
                <option value="UK-GDPR">UK-GDPR</option>
                <option value="PIPEDA">PIPEDA</option>
                <option value="POPIA">POPIA</option>
            </select>
            
            <button type="button" class="button" id="btn-filter"><?php _e( 'Filter', 'shahi-legalops' ); ?></button>
        </div>
    </div>
    
    <!-- Requests Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><input type="checkbox" id="cb-select-all"></th>
                <th><?php _e( 'ID', 'shahi-legalops' ); ?></th>
                <th><?php _e( 'Type', 'shahi-legalops' ); ?></th>
                <th><?php _e( 'Email', 'shahi-legalops' ); ?></th>
                <th><?php _e( 'Regulation', 'shahi-legalops' ); ?></th>
                <th><?php _e( 'Status', 'shahi-legalops' ); ?></th>
                <th><?php _e( 'Submitted', 'shahi-legalops' ); ?></th>
                <th><?php _e( 'SLA Due', 'shahi-legalops' ); ?></th>
                <th><?php _e( 'Actions', 'shahi-legalops' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $requests as $request ) : 
                $now = new DateTime();
                $due = new DateTime( $request->due_date );
                $is_overdue = $now > $due;
                $days_left = $now->diff( $due )->days;
            ?>
                <tr class="<?php echo $is_overdue ? 'slos-overdue' : ''; ?>">
                    <td><input type="checkbox" name="request[]" value="<?php echo esc_attr( $request->id ); ?>"></td>
                    <td><?php echo esc_html( $request->id ); ?></td>
                    <td><span class="badge badge-type"><?php echo esc_html( $request->request_type ); ?></span></td>
                    <td><?php echo esc_html( $request->email ); ?></td>
                    <td><span class="badge badge-regulation"><?php echo esc_html( $request->regulation ); ?></span></td>
                    <td><span class="badge badge-status badge-<?php echo esc_attr( $request->status ); ?>"><?php echo esc_html( $request->status ); ?></span></td>
                    <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $request->submitted_at ) ) ); ?></td>
                    <td>
                        <?php if ( $is_overdue ) : ?>
                            <span class="sla-overdue"><?php printf( __( 'Overdue by %d days', 'shahi-legalops' ), $days_left ); ?></span>
                        <?php else : ?>
                            <span class="sla-due"><?php printf( __( '%d days left', 'shahi-legalops' ), $days_left ); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo admin_url( 'admin.php?page=slos-dsr-requests&action=view&id=' . $request->id ); ?>" class="button button-small"><?php _e( 'View', 'shahi-legalops' ); ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- Bulk Actions -->
    <div class="tablenav bottom">
        <div class="alignleft actions bulkactions">
            <select name="action" id="bulk-action">
                <option value="-1"><?php _e( 'Bulk Actions', 'shahi-legalops' ); ?></option>
                <option value="verify"><?php _e( 'Mark Verified', 'shahi-legalops' ); ?></option>
                <option value="in_progress"><?php _e( 'Mark In Progress', 'shahi-legalops' ); ?></option>
                <option value="complete"><?php _e( 'Mark Completed', 'shahi-legalops' ); ?></option>
                <option value="export"><?php _e( 'Export Selected', 'shahi-legalops' ); ?></option>
            </select>
            <button type="button" class="button" id="btn-bulk-apply"><?php _e( 'Apply', 'shahi-legalops' ); ?></button>
        </div>
    </div>
</div>
```

3) **Add Custom Capability**

File: `includes/Core/Capabilities.php` (add):

```php
public function register_dsr_capabilities() {
    $admin_role = get_role( 'administrator' );
    if ( $admin_role ) {
        $admin_role->add_cap( 'slos_manage_dsr' );
    }
}
```

4) **Verification Tests**

```bash
# Create test requests
wp eval "
\$repo = new Shahi\LegalOps\Repositories\DSR_Repository();
for (\$i = 0; \$i < 5; \$i++) {
    \$repo->create_request([
        'email' => 'test' . \$i . '@example.com',
        'request_type' => ['access', 'erasure', 'portability'][\$i % 3],
        'regulation' => ['GDPR', 'CCPA', 'LGPD'][\$i % 3],
    ]);
}
echo '5 test requests created' . PHP_EOL;
"

# Verify admin page accessible
wp eval "echo admin_url( 'admin.php?page=slos-dsr-requests' );"

# Check capability
wp user list-caps admin | grep slos_manage_dsr

# Test filtering
wp db query "SELECT COUNT(*) FROM wp_slos_dsr_requests WHERE status = 'pending_verification'"
```

OUTPUT STATE:
✅ Admin menu registered
✅ List page with filters (status, type, regulation)
✅ Stats summary cards
✅ SLA indicators (overdue highlighting)
✅ Bulk actions UI
✅ Custom capability slos_manage_dsr
✅ Responsive table layout

SUCCESS CRITERIA:
✅ Admin can view all requests
✅ Filters work for status/type/regulation
✅ SLA indicators show days left/overdue
✅ Bulk actions selectable
✅ Capability required to access
✅ Stats cards show counts

ROLLBACK:
```bash
rm includes/Admin/DSR_Admin.php
rm templates/admin/dsr-list.php
```

COMMIT MESSAGE:
```
feat(dsr): add admin workflow UI

- List page with filters and stats
- SLA indicators with overdue alerts
- Bulk actions support
- Custom capability slos_manage_dsr
- Regulation-aware display

Task: 3.6 (8-10 hours)
Next: Task 3.7 - Data Export
```
```
