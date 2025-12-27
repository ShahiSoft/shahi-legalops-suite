# TASK 2.11: Export / Import

**Phase:** 2 (Consent Management - CORE)  
**Effort:** 6-8 hours  
**Prerequisites:** TASK 2.10 complete (Settings page)  
**Next Task:** [task-2.12-consent-logs-audit.md](task-2.12-consent-logs-audit.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 2.11 for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
Implement export/import for consent records. Admins must be able to export consents in CSV/JSON/PDF,
import consent data, run bulk operations, and configure scheduled exports. Ensure GDPR support for
per-user export.

INPUT STATE (verify these exist):
✅ Consent repository/service/API (Tasks 2.1-2.3)
✅ Settings page with export defaults (Task 2.10)
✅ Admin dashboard + analytics (Tasks 2.8-2.9)

YOUR TASK:

1) **Create Export/Import Service**

Location: `includes/Services/Consent_Export_Service.php`

```php
<?php
/**
 * Consent Export/Import Service
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

namespace Shahi\LegalOps\Services;

use Shahi\LegalOps\Repositories\Consent_Repository;

class Consent_Export_Service extends Base_Service {

    /**
     * Export consents
     *
     * @param array $args Filters: user_id, purpose, date_from, date_to, format (csv|json|pdf)
     * @return string|array Export content or file path
     */
    public function export( $args = [] ) {
        $defaults = [
            'format' => get_option( 'slos_export_format', 'csv' ),
            'user_id' => null,
            'purpose' => null,
            'date_from' => null,
            'date_to' => null,
            'limit' => 10000,
        ];
        $args = wp_parse_args( $args, $defaults );

        $repo = new Consent_Repository();
        $items = $repo->find_export( $args );

        switch ( $args['format'] ) {
            case 'json':
                return $this->export_json( $items );
            case 'pdf':
                return $this->export_pdf( $items );
            case 'csv':
            default:
                return $this->export_csv( $items );
        }
    }

    /**
     * Export CSV
     */
    private function export_csv( $items ) {
        $fh = fopen( 'php://temp', 'w' );
        fputcsv( $fh, [ 'id', 'user_id', 'purpose', 'granted', 'method', 'ip_address', 'user_agent', 'created_at' ] );
        foreach ( $items as $item ) {
            fputcsv( $fh, [
                $item['id'],
                $item['user_id'],
                $item['purpose'],
                $item['granted'] ? 'yes' : 'no',
                $item['consent_method'],
                $item['ip_address'],
                $item['user_agent'],
                $item['created_at'],
            ] );
        }
        rewind( $fh );
        return stream_get_contents( $fh );
    }

    /**
     * Export JSON
     */
    private function export_json( $items ) {
        return wp_json_encode( $items );
    }

    /**
     * Export PDF (simple wrapper for now)
     */
    private function export_pdf( $items ) {
        // Minimal: return HTML string that a PDF generator can consume
        $html = '<h1>Consent Export</h1><table border="1" cellpadding="6" cellspacing="0">';
        $html .= '<tr><th>ID</th><th>User</th><th>Purpose</th><th>Granted</th><th>Method</th><th>IP</th><th>UA</th><th>Date</th></tr>';
        foreach ( $items as $item ) {
            $html .= sprintf(
                '<tr><td>%d</td><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                $item['id'],
                $item['user_id'],
                esc_html( $item['purpose'] ),
                $item['granted'] ? 'yes' : 'no',
                esc_html( $item['consent_method'] ),
                esc_html( $item['ip_address'] ),
                esc_html( $item['user_agent'] ),
                esc_html( $item['created_at'] )
            );
        }
        $html .= '</table>';
        return $html; // PDF rendering handled by caller or future enhancement
    }

    /**
     * Import consents
     *
     * @param array $data Rows to import
     * @return array Summary
     */
    public function import( $data ) {
        $repo = new Consent_Repository();
        $imported = 0;
        $skipped = 0;

        foreach ( $data as $row ) {
            $validated = $this->validate_row( $row );
            if ( is_wp_error( $validated ) ) {
                $skipped++;
                continue;
            }

            $repo->create_consent( [
                'user_id' => (int) $row['user_id'],
                'purpose' => sanitize_text_field( $row['purpose'] ),
                'granted' => (bool) $row['granted'],
                'consent_text' => sanitize_textarea_field( $row['consent_text'] ?? '' ),
                'consent_method' => sanitize_text_field( $row['consent_method'] ?? 'import' ),
                'ip_address' => sanitize_text_field( $row['ip_address'] ?? '' ),
                'user_agent' => sanitize_text_field( $row['user_agent'] ?? '' ),
                'created_at' => $row['created_at'] ?? current_time( 'mysql' ),
            ] );
            $imported++;
        }

        return [ 'imported' => $imported, 'skipped' => $skipped ];
    }

    /**
     * Validate import row
     */
    private function validate_row( $row ) {
        if ( empty( $row['user_id'] ) || empty( $row['purpose'] ) ) {
            return new \WP_Error( 'invalid_row', 'Missing user_id or purpose' );
        }
        return true;
    }
}
```

2) **Add REST API endpoints**

Location: `includes/API/Consent_Export_Controller.php`

```php
<?php
/**
 * Consent Export/Import REST Controller
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

namespace Shahi\LegalOps\API;

use Shahi\LegalOps\Services\Consent_Export_Service;
use WP_REST_Response;

class Consent_Export_Controller extends Base_REST_Controller {

    protected $namespace = 'slos/v1';
    protected $rest_base = 'consents/export';

    public function register_routes() {
        // Export
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods' => 'GET',
                'callback' => [ $this, 'export' ],
                'permission_callback' => [ $this, 'admin_permissions_check' ],
            ],
        ] );

        // Import
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/import', [
            [
                'methods' => 'POST',
                'callback' => [ $this, 'import' ],
                'permission_callback' => [ $this, 'admin_permissions_check' ],
            ],
        ] );
    }

    public function export( $request ) {
        $service = new Consent_Export_Service();
        $data = $service->export( [
            'format' => $request->get_param( 'format' ),
            'user_id' => $request->get_param( 'user_id' ),
            'purpose' => $request->get_param( 'purpose' ),
            'date_from' => $request->get_param( 'date_from' ),
            'date_to' => $request->get_param( 'date_to' ),
        ] );

        return new WP_REST_Response( [
            'success' => true,
            'data' => $data,
        ], 200 );
    }

    public function import( $request ) {
        $body = $request->get_json_params();
        $rows = $body['rows'] ?? [];

        if ( empty( $rows ) ) {
            return new WP_REST_Response( [
                'success' => false,
                'message' => 'No rows provided',
            ], 400 );
        }

        $service = new Consent_Export_Service();
        $result = $service->import( $rows );

        return new WP_REST_Response( [
            'success' => true,
            'data' => $result,
        ], 200 );
    }
}
```

3) **Wire to cron for scheduled exports**

In `includes/Core/Cron_Handler.php` add:

```php
add_action( 'slos_export_consents_weekly', [ $this, 'run_weekly_export' ] );

public function schedule_exports() {
    if ( get_option( 'slos_scheduled_exports', false ) && ! wp_next_scheduled( 'slos_export_consents_weekly' ) ) {
        wp_schedule_event( time(), 'weekly', 'slos_export_consents_weekly' );
    }
}

public function run_weekly_export() {
    $service = new \Shahi\LegalOps\Services\Consent_Export_Service();
    $data = $service->export( [ 'format' => 'csv' ] );
    // Future: email to admin or store file
}
```

4) **Add admin UI actions**
- Button on settings page to run export now
- File upload to import CSV/JSON (convert to rows and pass to service)
- Use nonce for security

5) **Tests**

```bash
# Export all consents (CSV)
curl -X GET "http://localhost/wp-json/slos/v1/consents/export?format=csv" -H "Authorization: Bearer TOKEN"

# Export user-specific (JSON)
curl -X GET "http://localhost/wp-json/slos/v1/consents/export?format=json&user_id=5" -H "Authorization: Bearer TOKEN"

# Import sample rows
curl -X POST http://localhost/wp-json/slos/v1/consents/export/import \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"rows":[{"user_id":10,"purpose":"analytics","granted":true,"consent_method":"import"}]}'
```

OUTPUT STATE:
✅ Export service (CSV/JSON/PDF)
✅ Import support
✅ REST API endpoints for export/import
✅ Cron hook for scheduled export
✅ Admin UI buttons

VERIFICATION:
- REST endpoints return data with 200
- Import returns imported count
- CSV has header row and data
- Scheduled export event registered

SUCCESS CRITERIA:
✅ Export works for CSV/JSON/PDF
✅ Import processes rows and skips invalid
✅ Scheduled weekly export when enabled
✅ Admin buttons functional

ROLLBACK:
```bash
rm includes/Services/Consent_Export_Service.php
rm includes/API/Consent_Export_Controller.php
wp cron event delete slos_export_consents_weekly
```

TROUBLESHOOTING:
- Export empty: check consent data exists
- Import fails: validate required fields user_id/purpose
- Cron not running: wp cron event list | grep slos_export

COMMIT MESSAGE:
```
feat(consent): Add export/import for consents

- Export consents to CSV/JSON/PDF
- Import consent records via REST
- Admin buttons and cron for scheduled export
- Supports filters (user, purpose, date)

Task: 2.11 (6-8 hours)
Next: Task 2.12 - Consent Logs & Audit
```

WHAT TO REPORT BACK:
"✅ TASK 2.11 COMPLETE
- Export/import service
- REST endpoints
- Scheduled exports
- Admin UI buttons
- CSV/JSON/PDF supported
"
```

---

## ✅ COMPLETION CHECKLIST

- [ ] Consent_Export_Service.php created
- [ ] Consent_Export_Controller.php created
- [ ] Admin buttons wired
- [ ] Cron scheduled
- [ ] Export/import tested
- [ ] Committed to git
- [ ] Ready for Task 2.12

---

**Status:** ✅ Ready to execute  
**Time:** 6-8 hours  
**Next:** [task-2.12-consent-logs-audit.md](task-2.12-consent-logs-audit.md)
