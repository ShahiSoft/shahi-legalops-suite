# TASK 2.2: Create Consent Service Layer

**Phase:** 2 (Consent Management - CORE)  
**Effort:** 8-10 hours  
**Prerequisites:** TASK 2.1 complete (Consent_Repository exists, targeting `wp_complyflow_consent` + `wp_complyflow_consent_logs` schema from /v3docs/database/SCHEMA-ACTUAL.md)  
**Next Task:** [task-2.3-consent-rest-api.md](task-2.3-consent-rest-api.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 2.2 for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
Consent_Repository exists (Task 2.1 complete). Now create the Service layer that sits
between the REST API and the Repository. Services handle business logic, validation,
events, caching, and coordinate multiple repositories. Align with v3docs: use consent
categories JSON (necessary, functional, analytics, marketing, personalization), statuses
(pending, accepted, withdrawn, expired), and log entries in `wp_complyflow_consent_logs`.
Emit Google Consent Mode v2 signals and optional IAB TCF TC string when configured.

This follows clean architecture: Controllers → Services → Repositories → Database

INPUT STATE (verify these exist):
✅ Consent_Repository exists at includes/Database/Repositories/Consent_Repository.php
✅ All repository methods tested and working
✅ Database tables `wp_complyflow_consent`, `wp_complyflow_consent_logs` exist per /v3docs/database/SCHEMA-ACTUAL.md
✅ Namespace structure: Shahi\LegalOps\Services

YOUR TASK:

1. **Create Base Service Class**

Location: `includes/Services/Base_Service.php`

```php
<?php
/**
 * Base Service Class
 * Provides common service functionality.
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

namespace Shahi\LegalOps\Services;

abstract class Base_Service {
    /**
     * Cache group
     *
     * @var string
     */
    protected $cache_group = 'slos';

    /**
     * Cache expiration (seconds)
     *
     * @var int
     */
    protected $cache_expiration = 3600;

    /**
     * Get from cache
     *
     * @param string $key Cache key
     * @return mixed Cached value or false
     */
    protected function get_cache( string $key ) {
        return wp_cache_get( $key, $this->cache_group );
    }

    /**
     * Set cache
     *
     * @param string $key   Cache key
     * @param mixed  $value Value to cache
     * @param int    $expiration Cache expiration
     * @return bool True on success
     */
    protected function set_cache( string $key, $value, int $expiration = null ): bool {
        $expiration = $expiration ?? $this->cache_expiration;
        return wp_cache_set( $key, $value, $this->cache_group, $expiration );
    }

    /**
     * Delete cache
     *
     * @param string $key Cache key
     * @return bool True on success
     */
    protected function delete_cache( string $key ): bool {
        return wp_cache_delete( $key, $this->cache_group );
    }

    /**
     * Trigger action hook
     *
     * @param string $action Action name
     * @param mixed  ...$args Action arguments
     * @return void
     */
    protected function do_action( string $action, ...$args ): void {
        do_action( "slos_{$action}", ...$args );
    }

    /**
     * Apply filter hook
     *
     * @param string $filter Filter name
     * @param mixed  $value  Value to filter
     * @param mixed  ...$args Additional arguments
     * @return mixed Filtered value
     */
    protected function apply_filter( string $filter, $value, ...$args ) {
        return apply_filters( "slos_{$filter}", $value, ...$args );
    }

    /**
     * Validate required fields
     *
     * @param array $data     Data to validate
     * @param array $required Required fields
     * @return true|\WP_Error True on success, WP_Error on failure
     */
    protected function validate_required( array $data, array $required ) {
        $missing = [];

        foreach ( $required as $field ) {
            if ( ! isset( $data[ $field ] ) || empty( $data[ $field ] ) ) {
                $missing[] = $field;
            }
        }

        if ( ! empty( $missing ) ) {
            return new \WP_Error(
                'missing_required_fields',
                sprintf(
                    'Missing required fields: %s',
                    implode( ', ', $missing )
                ),
                [ 'fields' => $missing ]
            );
        }

        return true;
    }

    /**
     * Log error
     *
     * @param string $message Error message
     * @param array  $context Error context
     * @return void
     */
    protected function log_error( string $message, array $context = [] ): void {
        error_log( sprintf(
            '[SLOS] %s: %s %s',
            get_class( $this ),
            $message,
            ! empty( $context ) ? json_encode( $context ) : ''
        ) );
    }
}
```

2. **Create Complete Consent_Service.php** (extend Base_Service, align with /v3docs/modules/01-CONSENT-IMPLEMENTATION.md)

Location: `includes/Services/Consent_Service.php`

```php
<?php
/**
 * Consent Service
 * Business logic for consent management.
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

namespace Shahi\LegalOps\Services;

use Shahi\LegalOps\Database\Repositories\Consent_Repository;
use WP_Error;

class Consent_Service extends Base_Service {
    /**
     * Consent repository
     *
     * @var Consent_Repository
     */
    private $repository;

    /**
     * Valid consent purposes
     *
     * @var array
     */
    private $valid_purposes = [
        'necessary',
        'functional',
        'analytics',
        'marketing',
        'personalization',
        'advertising',
    ];

    /**
     * Valid consent statuses
     *
     * @var array
     */
    private $valid_statuses = [
        'pending',
        'accepted',
        'withdrawn',
        'expired',
    ];

    /**
     * Constructor
     */
    public function __construct() {
        $this->repository = new Consent_Repository();
        $this->valid_purposes = $this->apply_filter( 'valid_purposes', $this->valid_purposes );
    }

    /**
    * Grant consent (also triggers Google Consent Mode v2 signal and optional IAB TCF TC string generation if enabled)
     *
     * @param int    $user_id User ID
     * @param string $purpose Purpose
     * @param array  $args    Additional arguments
     * @return int|WP_Error Consent ID or error
     */
    public function grant_consent( int $user_id, string $purpose, array $args = [] ) {
        // Validate
        $validation = $this->validate_grant( $user_id, $purpose, $args );
        if ( is_wp_error( $validation ) ) {
            return $validation;
        }

        // Check if already has active consent
        if ( $this->repository->has_consent( $user_id, $purpose ) ) {
            return new WP_Error(
                'consent_already_exists',
                'User already has active consent for this purpose',
                [ 'user_id' => $user_id, 'purpose' => $purpose ]
            );
        }

        // Prepare data (schema-aligned: categories JSON + region + hashes + banner version)
        $data = [
            'user_id'        => $user_id,
            'purpose'        => $purpose,
            'status'         => 'accepted',
            'ip_hash'        => $this->get_user_ip_hash(),
            'user_agent_hash'=> $this->get_user_agent_hash(),
            'region'         => $args['region'] ?? $this->get_region(),
            'categories'     => $args['categories_json'] ?? $this->build_categories_payload( $purpose ),
            'consent_method' => $args['consent_method'] ?? 'explicit',
            'consent_text'   => $args['consent_text'] ?? '',
            'version'        => $args['version'] ?? $this->get_current_version(),
            'banner_version' => $args['banner_version'] ?? '1.0',
            'metadata'       => $args['metadata'] ?? [],
        ];

        // Apply filter
        $data = $this->apply_filter( 'consent_data_before_create', $data, $user_id, $purpose );

        // Create consent
        $consent_id = $this->repository->create_consent( $data );

        if ( ! $consent_id ) {
            $this->log_error( 'Failed to create consent', $data );
            return new WP_Error(
                'consent_creation_failed',
                'Failed to create consent record'
            );
        }

        // Clear cache
        $this->delete_cache( "user_consents_{$user_id}" );
        $this->delete_cache( "purpose_consents_{$purpose}" );

        // Trigger action + downstream signals
        $this->do_action( 'consent_granted', $consent_id, $user_id, $purpose, $data );
        $this->emit_google_consent_mode_signal( $data );
        $this->maybe_emit_tcf_signal( $data );

        return $consent_id;
    }

    /**
     * Withdraw consent
     *
     * @param int    $user_id User ID
     * @param string $purpose Purpose
     * @param string $reason  Withdrawal reason
     * @return true|WP_Error True on success, error on failure
     */
    public function withdraw_consent( int $user_id, string $purpose, string $reason = '' ) {
        // Validate
        if ( ! $this->repository->has_consent( $user_id, $purpose ) ) {
            return new WP_Error(
                'no_active_consent',
                'No active consent found for this purpose',
                [ 'user_id' => $user_id, 'purpose' => $purpose ]
            );
        }

        // Get consent record
        $consents = $this->repository->get_active_consents( $user_id, $purpose );
        if ( empty( $consents ) ) {
            return new WP_Error(
                'consent_not_found',
                'Consent record not found'
            );
        }

        $consent = $consents[0];

        // Withdraw
        $result = $this->repository->withdraw( $consent->id, $reason );

        if ( ! $result ) {
            $this->log_error( 'Failed to withdraw consent', [
                'consent_id' => $consent->id,
                'user_id'    => $user_id,
                'purpose'    => $purpose,
            ] );
            return new WP_Error(
                'withdrawal_failed',
                'Failed to withdraw consent'
            );
        }

        // Clear cache
        $this->delete_cache( "user_consents_{$user_id}" );
        $this->delete_cache( "purpose_consents_{$purpose}" );

        // Trigger action
        $this->do_action( 'consent_withdrawn', $consent->id, $user_id, $purpose, $reason );

        return true;
    }

    /**
     * Check if user has consent
     *
     * @param int    $user_id User ID
     * @param string $purpose Purpose
     * @return bool True if has active consent
     */
    public function has_consent( int $user_id, string $purpose ): bool {
        // Try cache first
        $cache_key = "has_consent_{$user_id}_{$purpose}";
        $cached = $this->get_cache( $cache_key );
        if ( false !== $cached ) {
            return (bool) $cached;
        }

        // Check repository
        $has = $this->repository->has_consent( $user_id, $purpose );

        // Cache result
        $this->set_cache( $cache_key, $has, 300 ); // 5 minutes

        return $has;
    }

    /**
     * Get user consents
     *
     * @param int   $user_id User ID
     * @param array $args    Query arguments
     * @return array Array of consent records
     */
    public function get_user_consents( int $user_id, array $args = [] ): array {
        // Try cache
        $cache_key = "user_consents_{$user_id}_" . md5( serialize( $args ) );
        $cached = $this->get_cache( $cache_key );
        if ( false !== $cached ) {
            return $cached;
        }

        // Get from repository
        $consents = $this->repository->find_by_user( $user_id, $args );

        // Cache
        $this->set_cache( $cache_key, $consents );

        return $consents;
    }

    /**
     * Get consent statistics
     *
     * @param array $args Filter arguments
     * @return array Statistics
     */
    public function get_statistics( array $args = [] ): array {
        $cache_key = 'consent_stats_' . md5( serialize( $args ) );
        $cached = $this->get_cache( $cache_key );
        if ( false !== $cached ) {
            return $cached;
        }

        $stats = $this->repository->get_statistics( $args );
        $this->set_cache( $cache_key, $stats, 900 ); // 15 minutes

        return $stats;
    }

    /**
     * Get purpose breakdown
     *
     * @return array Purpose breakdown
     */
    public function get_purpose_breakdown(): array {
        $cache_key = 'purpose_breakdown';
        $cached = $this->get_cache( $cache_key );
        if ( false !== $cached ) {
            return $cached;
        }

        $breakdown = $this->repository->get_purpose_breakdown();
        $this->set_cache( $cache_key, $breakdown, 900 );

        return $breakdown;
    }

    /**
     * Export user consents (GDPR)
     *
     * @param int $user_id User ID
     * @return array Consent data for export
     */
    public function export_user_data( int $user_id ): array {
        $consents = $this->repository->export_user_consents( $user_id );

        // Apply filter
        return $this->apply_filter( 'export_user_consents', $consents, $user_id );
    }

    /**
     * Delete user data (GDPR erasure)
     *
     * @param int $user_id User ID
     * @return int Number of records deleted
     */
    public function erase_user_data( int $user_id ): int {
        // Trigger action before deletion
        $this->do_action( 'before_erase_user_consents', $user_id );

        // Withdraw all active consents first
        $withdrawn = $this->repository->withdraw_all_for_user( $user_id, 'GDPR erasure request' );

        // Get all consents
        $consents = $this->repository->find_by_user( $user_id );

        // Delete records
        $deleted = 0;
        foreach ( $consents as $consent ) {
            if ( $this->repository->delete( $consent->id ) ) {
                $deleted++;
            }
        }

        // Clear cache
        $this->delete_cache( "user_consents_{$user_id}" );

        // Trigger action
        $this->do_action( 'user_consents_erased', $user_id, $deleted );

        return $deleted;
    }

    /**
     * Validate consent grant
     *
     * @param int    $user_id User ID
     * @param string $purpose Purpose
     * @param array  $args    Arguments
     * @return true|WP_Error True on success, error on failure
     */
    private function validate_grant( int $user_id, string $purpose, array $args ) {
        // Validate user exists
        if ( ! get_userdata( $user_id ) ) {
            return new WP_Error(
                'invalid_user',
                'User does not exist',
                [ 'user_id' => $user_id ]
            );
        }

        // Validate purpose
        if ( ! in_array( $purpose, $this->valid_purposes, true ) ) {
            return new WP_Error(
                'invalid_purpose',
                'Invalid consent purpose',
                [
                    'purpose' => $purpose,
                    'valid'   => $this->valid_purposes,
                ]
            );
        }

        // Validate consent method
        if ( isset( $args['consent_method'] ) && ! in_array( $args['consent_method'], [ 'explicit', 'implicit', 'legitimate_interest' ], true ) ) {
            return new WP_Error(
                'invalid_consent_method',
                'Invalid consent method'
            );
        }

        return true;
    }

    /**
     * Get user IP address
     *
     * @return string IP address
     */
    private function get_user_ip(): string {
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }

        return sanitize_text_field( $ip );
    }

    /**
     * Get user agent
     *
     * @return string User agent
     */
    private function get_user_agent(): string {
        return sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' );
    }

    /**
     * Get current version
     *
     * @return string Version
     */
    private function get_current_version(): string {
        return '1.0';
    }

    /**
     * Get valid purposes
     *
     * @return array Valid purposes
     */
    public function get_valid_purposes(): array {
        return $this->valid_purposes;
    }

    /**
     * Expire old consents (scheduled task)
     *
     * @param int $days Days to keep
     * @return int Number expired
     */
    public function expire_old_consents( int $days = 730 ): int {
        $expired = $this->repository->expire_old_consents( $days );

        if ( $expired > 0 ) {
            $this->do_action( 'consents_expired', $expired, $days );
        }

        return $expired;
    }
}
```

3. **Update composer.json**

Add to psr-4 section:
```json
"Shahi\\LegalOps\\Services\\": "includes/Services/"
```

Then run:
```bash
composer dump-autoload
```

4. **Create Test Suite**

Location: `includes/Services/test-consent-service.php`

```php
<?php
/**
 * Consent Service Test Suite
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Shahi\LegalOps\Services\Consent_Service;

echo "🧪 Consent Service Test Suite\n";
echo "==============================\n\n";

$service = new Consent_Service();
$test_user_id = 1;

// Test 1: Grant consent
echo "Test 1: Grant Consent\n";
$result = $service->grant_consent( $test_user_id, 'analytics', [
    'consent_text' => 'I agree to analytics cookies',
    'consent_method' => 'explicit',
] );
echo ! is_wp_error( $result ) ? "✅ Granted consent ID: {$result}\n\n" : "❌ Error: " . $result->get_error_message() . "\n\n";

// Test 2: Check has consent
echo "Test 2: Check Has Consent\n";
$has = $service->has_consent( $test_user_id, 'analytics' );
echo $has ? "✅ User has consent\n\n" : "❌ No consent\n\n";

// Test 3: Try to grant again (should fail)
echo "Test 3: Try Grant Again (should fail)\n";
$result = $service->grant_consent( $test_user_id, 'analytics' );
echo is_wp_error( $result ) ? "✅ Correctly prevented duplicate: " . $result->get_error_message() . "\n\n" : "❌ Should have failed\n\n";

// Test 4: Get user consents
echo "Test 4: Get User Consents\n";
$consents = $service->get_user_consents( $test_user_id );
echo count( $consents ) > 0 ? "✅ Found " . count( $consents ) . " consents\n\n" : "❌ No consents\n\n";

// Test 5: Get statistics
echo "Test 5: Get Statistics\n";
$stats = $service->get_statistics();
echo isset( $stats['total'] ) ? "✅ Stats: {$stats['total']} total\n\n" : "❌ No stats\n\n";

// Test 6: Withdraw consent
echo "Test 6: Withdraw Consent\n";
$result = $service->withdraw_consent( $test_user_id, 'analytics', 'User requested' );
echo ! is_wp_error( $result ) ? "✅ Withdrawn\n\n" : "❌ Error: " . $result->get_error_message() . "\n\n";

// Test 7: Verify withdrawal
echo "Test 7: Verify Withdrawal\n";
$has = $service->has_consent( $test_user_id, 'analytics' );
echo ! $has ? "✅ Consent no longer active\n\n" : "❌ Still active\n\n";

// Test 8: Export user data
echo "Test 8: Export User Data\n";
$export = $service->export_user_data( $test_user_id );
echo is_array( $export ) ? "✅ Exported " . count( $export ) . " records\n\n" : "❌ Export failed\n\n";

// Test 9: Get valid purposes
echo "Test 9: Get Valid Purposes\n";
$purposes = $service->get_valid_purposes();
echo count( $purposes ) > 0 ? "✅ Found " . count( $purposes ) . " purposes: " . implode( ', ', $purposes ) . "\n\n" : "❌ No purposes\n\n";

// Test 10: Invalid purpose (should fail)
echo "Test 10: Try Invalid Purpose (should fail)\n";
$result = $service->grant_consent( $test_user_id, 'invalid_purpose' );
echo is_wp_error( $result ) ? "✅ Correctly rejected: " . $result->get_error_message() . "\n\n" : "❌ Should have failed\n\n";

echo "==============================\n";
echo "✅ All tests complete!\n";
```

5. **Run tests**
```bash
wp eval-file includes/Services/test-consent-service.php
```

OUTPUT STATE:
✅ Base_Service.php with caching, hooks, validation
✅ Complete Consent_Service.php with business logic
✅ Service layer sits between API and repositories
✅ Caching implemented for performance
✅ WordPress hooks for extensibility
✅ GDPR export and erasure methods
✅ Comprehensive validation
✅ All tests passing

VERIFICATION:

1. **Check files:**
```bash
ls -la includes/Services/
wc -l includes/Services/*.php
```
Expected: Base_Service.php (~150 lines), Consent_Service.php (~450+ lines)

2. **Check syntax:**
```bash
php -l includes/Services/Base_Service.php
php -l includes/Services/Consent_Service.php
```

3. **Run tests:**
```bash
wp eval-file includes/Services/test-consent-service.php
```
Expected: All 10 tests pass

4. **Test service methods:**
```bash
wp eval '
use Shahi\LegalOps\Services\Consent_Service;
$service = new Consent_Service();
$purposes = $service->get_valid_purposes();
print_r($purposes);
'
```

SUCCESS CRITERIA:
✅ Both service files created
✅ Service layer architecture implemented
✅ Caching working
✅ Validation working
✅ All tests passing
✅ WordPress hooks in place

ROLLBACK:
```bash
rm -rf includes/Services/
git checkout composer.json
composer dump-autoload
```

TROUBLESHOOTING:

**Problem 1: Class not found**
```bash
composer dump-autoload -o
```

**Problem 2: Cache not working**
```bash
wp eval 'var_dump(wp_using_ext_object_cache());'
```

**Problem 3: Hooks not firing**
```bash
wp eval 'add_action("slos_consent_granted", function() { echo "Hook fired!\n"; }); do_action("slos_consent_granted");'
```

COMMIT MESSAGE:
```
feat(services): Implement Consent Service layer

- Create Base_Service with caching, hooks, validation
- Implement complete Consent_Service business logic
- Add grant, withdraw, export, erase functionality
- Implement caching for performance
- Add WordPress hooks for extensibility
- Comprehensive validation and error handling
- Test suite with 10 tests

Service layer architecture complete.

Task: 2.2 (8-10 hours)
Next: Task 2.3 - REST API Endpoints
```

WHAT TO REPORT BACK:
"✅ TASK 2.2 COMPLETE

Created:
- Base_Service.php (caching, hooks, validation)
- Consent_Service.php (complete business logic)
- Test suite (10 tests)

Implemented:
- ✅ Grant consent with validation
- ✅ Withdraw consent
- ✅ Check consent status
- ✅ GDPR export & erasure
- ✅ Caching layer (5-15 min TTL)
- ✅ WordPress hooks (slos_consent_granted, etc.)
- ✅ Error handling with WP_Error

Architecture:
Controllers → Services → Repositories → Database

Verification passed:
- ✅ All tests passing (10/10)
- ✅ Clean architecture established
- ✅ Ready for REST API layer

📍 Ready for TASK 2.3: [task-2.3-consent-rest-api.md](task-2.3-consent-rest-api.md)"
```

---

## ✅ COMPLETION CHECKLIST

- [ ] Base_Service.php created
- [ ] Consent_Service.php created
- [ ] Composer autoloader updated
- [ ] All methods implemented
- [ ] Caching working
- [ ] WordPress hooks in place
- [ ] Tests passing (10/10)
- [ ] Committed to git
- [ ] Ready for Task 2.3

---

**Status:** ✅ Ready to execute  
**Time:** 8-10 hours  
**Next:** [task-2.3-consent-rest-api.md](task-2.3-consent-rest-api.md)
