# TASK 3.1: DSR Repository

**Phase:** 3 (DSR Portal)  
**Effort:** 8-10 hours  
**Prerequisites:** Phase 1 complete (DB, Base Repository)  
**Next Task:** [task-3.2-dsr-service.md](task-3.2-dsr-service.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 3.1 for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
Implement the Data Subject Request repository to manage all 7 request types (access, rectification, 
erasure, portability, restriction, object, automated_decision) with full GDPR/CCPA/LGPD compliance,
SLA tracking, and hashed IDs for privacy.

References: /v3docs/modules/02-DSR-IMPLEMENTATION.md - Request types, workflow, SLA management

INPUT STATE (verify these exist):
✅ Base_Repository class exists (Task 1.4)
✅ DSR tables from migrations: wp_slos_dsr_requests, wp_slos_dsr_request_data, wp_slos_dsr_evidence
✅ Database helper methods available

YOUR TASK:

1) **Create DSR Repository Class**

File: `includes/Repositories/DSR_Repository.php`

```php
<?php
namespace Shahi\LegalOps\Repositories;

use Shahi\LegalOps\Core\Base_Repository;

class DSR_Repository extends Base_Repository {
    protected $table = 'slos_dsr_requests';
    protected $primary_key = 'id';
    
    /**
     * Create new DSR request with SLA calculation
     * @param array $data request_type, email, user_id, regulation, details
     * @return int|false request ID or false
     */
    public function create_request( $data ) {
        global $wpdb;
        
        $defaults = [
            'request_type' => 'access',
            'status' => 'pending_verification',
            'user_id' => null,
            'regulation' => 'GDPR', // GDPR/CCPA/LGPD/UK-GDPR/PIPEDA/POPIA
            'sla_days' => $this->get_sla_days( $data['regulation'] ?? 'GDPR' ),
            'submitted_at' => current_time( 'mysql' ),
            'verification_token' => wp_generate_password( 32, false ),
            'ip_hash' => hash( 'sha256', $this->get_client_ip() ),
            'user_agent_hash' => hash( 'sha256', $_SERVER['HTTP_USER_AGENT'] ?? '' ),
        ];
        
        $data = wp_parse_args( $data, $defaults );
        $data['due_date'] = $this->calculate_due_date( $data['submitted_at'], $data['sla_days'] );
        
        $inserted = $wpdb->insert(
            $this->get_table_name(),
            $this->sanitize_data( $data ),
            $this->get_format()
        );
        
        return $inserted ? $wpdb->insert_id : false;
    }
    
    /**
     * Find request by ID
     */
    public function find( $id ) {
        global $wpdb;
        $table = $this->get_table_name();
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $id
        ) );
    }
    
    /**
     * Find by verification token (single-use)
     */
    public function find_by_token( $token ) {
        global $wpdb;
        $table = $this->get_table_name();
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE verification_token = %s AND status = 'pending_verification'",
            $token
        ) );
    }
    
    /**
     * List requests with filters
     * @param array $filters status, request_type, regulation, date_from, date_to, assignee
     * @param int $limit
     * @param int $offset
     */
    public function list_requests( $filters = [], $limit = 50, $offset = 0 ) {
        global $wpdb;
        $table = $this->get_table_name();
        $where = ['1=1'];
        $params = [];
        
        if ( ! empty( $filters['status'] ) ) {
            $where[] = 'status = %s';
            $params[] = $filters['status'];
        }
        
        if ( ! empty( $filters['request_type'] ) ) {
            $where[] = 'request_type = %s';
            $params[] = $filters['request_type'];
        }
        
        if ( ! empty( $filters['regulation'] ) ) {
            $where[] = 'regulation = %s';
            $params[] = $filters['regulation'];
        }
        
        if ( ! empty( $filters['date_from'] ) ) {
            $where[] = 'submitted_at >= %s';
            $params[] = $filters['date_from'];
        }
        
        if ( ! empty( $filters['date_to'] ) ) {
            $where[] = 'submitted_at <= %s';
            $params[] = $filters['date_to'];
        }
        
        $where_clause = implode( ' AND ', $where );
        $params[] = $limit;
        $params[] = $offset;
        
        $sql = "SELECT * FROM {$table} WHERE {$where_clause} ORDER BY submitted_at DESC LIMIT %d OFFSET %d";
        
        return $wpdb->get_results( $wpdb->prepare( $sql, ...$params ) );
    }
    
    /**
     * Update request status with audit trail
     */
    public function update_status( $id, $status, $metadata = [] ) {
        global $wpdb;
        
        $data = [
            'status' => $status,
            'updated_at' => current_time( 'mysql' ),
        ];
        
        if ( $status === 'verified' ) {
            $data['verified_at'] = current_time( 'mysql' );
        } elseif ( $status === 'completed' || $status === 'rejected' ) {
            $data['completed_at'] = current_time( 'mysql' );
        }
        
        if ( ! empty( $metadata['processed_by'] ) ) {
            $data['processed_by'] = $metadata['processed_by'];
        }
        
        if ( ! empty( $metadata['admin_notes'] ) ) {
            $data['admin_notes'] = $metadata['admin_notes'];
        }
        
        return $wpdb->update(
            $this->get_table_name(),
            $data,
            ['id' => $id],
            $this->get_format(),
            ['%d']
        );
    }
    
    /**
     * Get statistics by status
     */
    public function stats_by_status() {
        global $wpdb;
        $table = $this->get_table_name();
        return $wpdb->get_results(
            "SELECT status, COUNT(*) as count FROM {$table} GROUP BY status",
            OBJECT_K
        );
    }
    
    /**
     * Get statistics by type
     */
    public function stats_by_type() {
        global $wpdb;
        $table = $this->get_table_name();
        return $wpdb->get_results(
            "SELECT request_type, COUNT(*) as count FROM {$table} GROUP BY request_type",
            OBJECT_K
        );
    }
    
    /**
     * Get SLA days based on regulation
     */
    private function get_sla_days( $regulation ) {
        $defaults = [
            'GDPR' => 30,
            'UK-GDPR' => 30,
            'CCPA' => 45,
            'LGPD' => 15,
            'PIPEDA' => 30,
            'POPIA' => 30,
        ];
        
        return $defaults[ $regulation ] ?? 30;
    }
    
    /**
     * Calculate due date (business days)
     */
    private function calculate_due_date( $start, $days ) {
        $date = new \DateTime( $start );
        $added = 0;
        
        while ( $added < $days ) {
            $date->modify( '+1 day' );
            // Skip weekends
            if ( $date->format( 'N' ) < 6 ) {
                $added++;
            }
        }
        
        return $date->format( 'Y-m-d H:i:s' );
    }
    
    /**
     * Get client IP (hashed for privacy)
     */
    private function get_client_ip() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ips = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
            $ip = trim( $ips[0] );
        }
        return $ip;
    }
    
    /**
     * Sanitize request data
     */
    private function sanitize_data( $data ) {
        return [
            'request_type' => sanitize_text_field( $data['request_type'] ),
            'status' => sanitize_text_field( $data['status'] ),
            'email' => sanitize_email( $data['email'] ),
            'user_id' => ! empty( $data['user_id'] ) ? absint( $data['user_id'] ) : null,
            'regulation' => sanitize_text_field( $data['regulation'] ),
            'verification_token' => sanitize_text_field( $data['verification_token'] ),
            'submitted_at' => sanitize_text_field( $data['submitted_at'] ),
            'due_date' => sanitize_text_field( $data['due_date'] ),
            'sla_days' => absint( $data['sla_days'] ),
            'ip_hash' => sanitize_text_field( $data['ip_hash'] ),
            'user_agent_hash' => sanitize_text_field( $data['user_agent_hash'] ),
        ];
    }
    
    /**
     * Get field formats for wpdb
     */
    private function get_format() {
        return ['%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s'];
    }
}
```

2) **Register Repository in Container**

File: `includes/Core/Container.php` (add to singleton method):

```php
$this->register( 'dsr_repository', function() {
    return new \Shahi\LegalOps\Repositories\DSR_Repository();
} );
```

3) **Verification Tests**

```bash
# Test create request
wp eval "
\$repo = new Shahi\LegalOps\Repositories\DSR_Repository();
\$id = \$repo->create_request([
    'email' => 'user@example.com',
    'request_type' => 'access',
    'regulation' => 'GDPR',
    'user_id' => 1
]);
echo 'Created request ID: ' . \$id . PHP_EOL;
"

# Verify in database
wp db query "SELECT id, request_type, status, email, regulation, due_date FROM wp_slos_dsr_requests ORDER BY id DESC LIMIT 1"

# Test find methods
wp eval "
\$repo = new Shahi\LegalOps\Repositories\DSR_Repository();
\$request = \$repo->find(1);
print_r(\$request);
"

# Test statistics
wp eval "
\$repo = new Shahi\LegalOps\Repositories\DSR_Repository();
\$stats = \$repo->stats_by_type();
print_r(\$stats);
"
```

OUTPUT STATE:
✅ DSR_Repository created and extends Base_Repository
✅ CRUD methods work for all 7 request types
✅ SLA calculation by regulation (GDPR=30, CCPA=45, LGPD=15, etc.)
✅ Hashed IP/UA stored for privacy
✅ Statistics methods return counts
✅ Verification token generation
✅ Container registration

SUCCESS CRITERIA:
✅ create_request() returns ID with proper due_date
✅ find() and list_requests() return sanitized records
✅ Tokens are unique and single-use
✅ Hashed IPs stored, no raw PII
✅ SLA days correct per regulation
✅ Stats queries return accurate counts

ROLLBACK:
```bash
# Remove repository file
rm includes/Repositories/DSR_Repository.php

# Rollback container registration
git checkout includes/Core/Container.php
```

TROUBLESHOOTING:
- **Issue:** "Table doesn't exist" → Run Task 1.3 migrations first
- **Issue:** "Base_Repository not found" → Ensure Task 1.4 complete and autoloader refreshed
- **Issue:** "SLA calculation wrong" → Check get_sla_days() matches regulation; verify business days logic
- **Issue:** "Hashes missing" → Ensure $_SERVER vars available; fallback to empty string if needed

COMMIT MESSAGE:
```
feat(dsr): add DSR repository with 7 request types and SLA tracking

- Support all GDPR/CCPA/LGPD/UK-GDPR/PIPEDA/POPIA request types
- Automatic SLA calculation by regulation (business days)
- Privacy-first: hashed IP/UA, no raw PII storage
- Statistics by status and type
- Verification token generation

Task: 3.1 (8-10 hours)
Next: Task 3.2 - DSR Service Layer
```

WHAT TO REPORT BACK:
"✅ TASK 3.1 COMPLETE
- DSR Repository created with all 7 request types
- SLA tracking per regulation
- Hashed IPs for privacy
- CRUD and stats methods verified
- Ready for Task 3.2
"
```
