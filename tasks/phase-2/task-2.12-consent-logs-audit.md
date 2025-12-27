# TASK 2.12: Consent Logs & Audit Trail

**Phase:** 2 (Consent Management - CORE)  
**Effort:** 6-8 hours  
**Prerequisites:** TASK 2.11 complete (Export/Import)  
**Next Task:** [task-2.13-multi-language.md](task-2.13-multi-language.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 2.12 for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
Implement a complete audit trail for consents. Every consent change must be logged with timestamp,
user, IP, user agent, method, and previous state. Provide admin UI to search/filter logs, set
retention policies, and export logs. This is required for GDPR/CCPA compliance evidence.

INPUT STATE (verify these exist):
✅ Consent repo/service/API
✅ Export/import service (Task 2.11)
✅ Settings page (Task 2.10) with log retention option

YOUR TASK:

1) **Create Audit Log Table (if not already)**
- Table: `wp_slos_consent_logs`
- Columns: id, consent_id, user_id, purpose, action (grant/withdraw/import/export),
  previous_state JSON, new_state JSON, method, ip_address, user_agent, created_at

Add migration snippet (if missing) in migrations folder or via activation hook.

2) **Create Audit Logger Service**

Location: `includes/Services/Consent_Audit_Logger.php`

```php
<?php
/**
 * Consent Audit Logger
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

namespace Shahi\LegalOps\Services;

use Shahi\LegalOps\Repositories\Consent_Repository;

class Consent_Audit_Logger extends Base_Service {

    public function log( $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'slos_consent_logs';

        $wpdb->insert( $table, [
            'consent_id' => $data['consent_id'] ?? 0,
            'user_id' => $data['user_id'] ?? 0,
            'purpose' => $data['purpose'] ?? '',
            'action' => $data['action'] ?? '',
            'previous_state' => isset( $data['previous_state'] ) ? wp_json_encode( $data['previous_state'] ) : null,
            'new_state' => isset( $data['new_state'] ) ? wp_json_encode( $data['new_state'] ) : null,
            'method' => $data['method'] ?? '',
            'ip_address' => $data['ip_address'] ?? '',
            'user_agent' => $data['user_agent'] ?? '',
            'created_at' => current_time( 'mysql' ),
        ] );
    }

    public function search( $args = [] ) {
        global $wpdb;
        $table = $wpdb->prefix . 'slos_consent_logs';

        $defaults = [
            'user_id' => null,
            'purpose' => null,
            'action' => null,
            'date_from' => null,
            'date_to' => null,
            'limit' => 100,
            'offset' => 0,
        ];
        $args = wp_parse_args( $args, $defaults );

        $where = 'WHERE 1=1';
        $params = [];

        if ( $args['user_id'] ) {
            $where .= ' AND user_id = %d';
            $params[] = $args['user_id'];
        }
        if ( $args['purpose'] ) {
            $where .= ' AND purpose = %s';
            $params[] = $args['purpose'];
        }
        if ( $args['action'] ) {
            $where .= ' AND action = %s';
            $params[] = $args['action'];
        }
        if ( $args['date_from'] ) {
            $where .= ' AND created_at >= %s';
            $params[] = $args['date_from'];
        }
        if ( $args['date_to'] ) {
            $where .= ' AND created_at <= %s';
            $params[] = $args['date_to'];
        }

        $sql = $wpdb->prepare(
            "SELECT * FROM $table $where ORDER BY created_at DESC LIMIT %d OFFSET %d",
            ...array_merge( $params, [ $args['limit'], $args['offset'] ] )
        );

        return $wpdb->get_results( $sql, ARRAY_A );
    }

    public function purge_old_logs() {
        global $wpdb;
        $table = $wpdb->prefix . 'slos_consent_logs';
        $days = (int) get_option( 'slos_log_retention', 365 );
        $wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)", $days ) );
    }
}
```

3) **Integrate logging into consent service**
- On grant/withdraw/import/export, call Consent_Audit_Logger->log with previous/new state
- Include IP + user agent

4) **Add REST API for logs**

Location: `includes/API/Consent_Log_Controller.php`

```php
<?php
namespace Shahi\LegalOps\API;

use Shahi\LegalOps\Services\Consent_Audit_Logger;
use WP_REST_Response;

class Consent_Log_Controller extends Base_REST_Controller {
    protected $namespace = 'slos/v1';
    protected $rest_base = 'consents/logs';

    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods' => 'GET',
                'callback' => [ $this, 'get_logs' ],
                'permission_callback' => [ $this, 'admin_permissions_check' ],
            ],
        ] );
    }

    public function get_logs( $request ) {
        $logger = new Consent_Audit_Logger();
        $logs = $logger->search( [
            'user_id' => $request->get_param( 'user_id' ),
            'purpose' => $request->get_param( 'purpose' ),
            'action' => $request->get_param( 'action' ),
            'date_from' => $request->get_param( 'date_from' ),
            'date_to' => $request->get_param( 'date_to' ),
            'limit' => $request->get_param( 'limit' ) ?? 100,
            'offset' => $request->get_param( 'offset' ) ?? 0,
        ] );

        return new WP_REST_Response( [ 'success' => true, 'data' => $logs ], 200 );
    }
}
```

5) **Admin UI**
- Page: SLOS > Consent Logs
- Filters: date range, user, purpose, action
- Table listing logs
- Export button (re-use export service)

6) **Cron for retention**
- In Cron_Handler: daily hook to purge_old_logs()

7) **Tests**

```bash
# Create consent then withdraw to generate logs
wp eval "
$service = new \Shahi\LegalOps\Services\Consent_Service();
$service->grant_consent(0,'analytics','text','explicit');
$service->withdraw_consent(0,'analytics');
"

# Fetch logs via REST
curl -H "Authorization: Bearer TOKEN" http://localhost/wp-json/slos/v1/consents/logs | jq

# Check purge
wp eval "
$logger = new \Shahi\LegalOps\Services\Consent_Audit_Logger();
$logger->purge_old_logs();
"
```

OUTPUT STATE:
✅ Audit log table created
✅ Consent_Audit_Logger service
✅ Logging integrated into consent actions
✅ REST endpoint for logs
✅ Admin UI for filtering/export
✅ Retention purge via cron

SUCCESS CRITERIA:
✅ Logs created for grant/withdraw/import/export
✅ Search/filter works
✅ Retention purge removes old logs
✅ REST API secured to admins

ROLLBACK:
```bash
wp db query "DROP TABLE wp_slos_consent_logs";
rm includes/Services/Consent_Audit_Logger.php
rm includes/API/Consent_Log_Controller.php
```

TROUBLESHOOTING:
- No logs: ensure logger called in service methods
- Table missing: run migration / activator
- Purge not running: check cron events

COMMIT MESSAGE:
```
feat(consent): Add audit trail for consents

- Create consent_logs table
- Log grant/withdraw/import/export events
- REST endpoint for log search
- Admin UI with filters and export
- Retention purge via cron

Task: 2.12 (6-8 hours)
Next: Task 2.13 - Multi-language
```

WHAT TO REPORT BACK:
"✅ TASK 2.12 COMPLETE
- Audit log table
- Logger service
- REST endpoint
- Admin UI filters
- Retention purge
"
```

---

## ✅ COMPLETION CHECKLIST

- [ ] consent_logs table created
- [ ] Consent_Audit_Logger.php created
- [ ] Consent_Log_Controller.php created
- [ ] Admin UI added
- [ ] Cron purge added
- [ ] Logs verified
- [ ] Committed to git
- [ ] Ready for Task 2.13

---

**Status:** ✅ Ready to execute  
**Time:** 6-8 hours  
**Next:** [task-2.13-multi-language.md](task-2.13-multi-language.md)
