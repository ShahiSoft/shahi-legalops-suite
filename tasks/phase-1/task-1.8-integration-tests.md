# TASK 1.8: Integration Tests for Phase 1

**Phase:** 1 (Infrastructure & Database)  
**Effort:** 6-8 hours  
**Prerequisites:** TASK 1.7 complete (WordPress hooks integrated)  
**Next Task:** [../phase-2/task-2.1-consent-repository.md](../phase-2/task-2.1-consent-repository.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 1.8 for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
All Phase 1 infrastructure is complete (Tasks 1.1-1.7). Now create comprehensive integration
tests to verify everything works together: database, repositories, services, REST API, hooks.

This ensures the foundation is solid before building modules on top of it.

INPUT STATE (verify these exist):
✅ Database tables created (Task 1.3)
✅ Base_Repository exists (Task 1.4)
✅ Base_Service exists (Task 1.5)
✅ REST API infrastructure (Task 1.6)
✅ WordPress hooks integrated (Task 1.7)

YOUR TASK:

1. **Create Integration Test Suite**

Location: `tests/integration/test-phase-1-integration.php`

```php
<?php
/**
 * Phase 1 Integration Tests
 * Tests that all infrastructure components work together.
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

require_once __DIR__ . '/../../vendor/autoload.php';

class Phase_1_Integration_Test {
    private $results = [];
    private $passed = 0;
    private $failed = 0;

    public function run_all_tests() {
        echo "🧪 PHASE 1 INTEGRATION TEST SUITE\n";
        echo "==================================\n\n";

        // Database Tests
        $this->test_database_connection();
        $this->test_tables_exist();
        $this->test_table_structure();

        // Repository Tests
        $this->test_repository_crud();
        $this->test_repository_queries();

        // Service Tests
        $this->test_service_caching();
        $this->test_service_validation();

        // REST API Tests
        $this->test_api_health_endpoint();
        $this->test_api_authentication();

        // WordPress Integration Tests
        $this->test_plugin_activated();
        $this->test_capabilities_exist();
        $this->test_cron_scheduled();
        $this->test_admin_menu_exists();

        // Summary
        $this->print_summary();
    }

    // === DATABASE TESTS ===

    private function test_database_connection() {
        global $wpdb;
        $this->test( 'Database connection', function() use ( $wpdb ) {
            $result = $wpdb->get_var( 'SELECT 1' );
            return $result === '1';
        } );
    }

    private function test_tables_exist() {
        global $wpdb;
        $this->test( 'Database tables exist', function() use ( $wpdb ) {
            $tables = [
                'slos_consent',
                'slos_dsr_requests',
                'slos_legal_documents',
                'slos_cookie_scanners',
                'slos_vendor_lists',
                'slos_form_submissions',
                'slos_accessibility_issues',
            ];

            foreach ( $tables as $table ) {
                $full_table = $wpdb->prefix . $table;
                $exists = $wpdb->get_var( "SHOW TABLES LIKE '{$full_table}'" );
                if ( ! $exists ) {
                    return false;
                }
            }
            return true;
        } );
    }

    private function test_table_structure() {
        global $wpdb;
        $this->test( 'Consent table structure', function() use ( $wpdb ) {
            $table = $wpdb->prefix . 'slos_consent';
            $columns = $wpdb->get_results( "DESCRIBE {$table}" );
            $column_names = array_map( function( $col ) {
                return $col->Field;
            }, $columns );

            $required = [ 'id', 'user_id', 'purpose', 'status', 'created_at' ];
            foreach ( $required as $col ) {
                if ( ! in_array( $col, $column_names, true ) ) {
                    return false;
                }
            }
            return true;
        } );
    }

    // === REPOSITORY TESTS ===

    private function test_repository_crud() {
        $this->test( 'Repository CRUD operations', function() {
            $repo = new \Shahi\LegalOps\Database\Repositories\Consent_Repository();

            // Create
            $id = $repo->create_consent([
                'user_id' => 1,
                'purpose' => 'test',
                'status' => 'active',
            ]);
            if ( ! $id ) return false;

            // Read
            $record = $repo->find( $id );
            if ( ! $record || $record->purpose !== 'test' ) return false;

            // Update
            $updated = $repo->update( $id, [ 'status' => 'withdrawn' ] );
            if ( ! $updated ) return false;

            $record = $repo->find( $id );
            if ( $record->status !== 'withdrawn' ) return false;

            // Delete
            $deleted = $repo->delete( $id );
            if ( ! $deleted ) return false;

            $record = $repo->find( $id );
            return $record === null;
        } );
    }

    private function test_repository_queries() {
        $this->test( 'Repository complex queries', function() {
            $repo = new \Shahi\LegalOps\Database\Repositories\Consent_Repository();

            // Create test data
            $id1 = $repo->create_consent([ 'user_id' => 1, 'purpose' => 'analytics', 'status' => 'active' ]);
            $id2 = $repo->create_consent([ 'user_id' => 1, 'purpose' => 'marketing', 'status' => 'active' ]);

            // Test find_by_user
            $consents = $repo->find_by_user( 1 );
            if ( count( $consents ) < 2 ) return false;

            // Test statistics
            $stats = $repo->get_statistics();
            if ( ! isset( $stats['total'] ) || $stats['total'] < 2 ) return false;

            // Cleanup
            $repo->delete( $id1 );
            $repo->delete( $id2 );

            return true;
        } );
    }

    // === SERVICE TESTS ===

    private function test_service_caching() {
        $this->test( 'Service caching layer', function() {
            $service = new \Shahi\LegalOps\Services\Consent_Service();

            // Grant consent (this should cache)
            $id = $service->grant_consent( 1, 'analytics', [
                'consent_text' => 'Test consent',
            ] );

            if ( is_wp_error( $id ) ) {
                // If already exists, that's OK
                if ( $id->get_error_code() !== 'consent_already_exists' ) {
                    return false;
                }
            }

            // Check has_consent (should use cache)
            $has = $service->has_consent( 1, 'analytics' );
            return $has === true;
        } );
    }

    private function test_service_validation() {
        $this->test( 'Service validation', function() {
            $service = new \Shahi\LegalOps\Services\Consent_Service();

            // Test invalid purpose
            $result = $service->grant_consent( 1, 'invalid_purpose' );
            if ( ! is_wp_error( $result ) ) return false;
            if ( $result->get_error_code() !== 'invalid_purpose' ) return false;

            // Test invalid user
            $result = $service->grant_consent( 999999, 'analytics' );
            if ( ! is_wp_error( $result ) ) return false;

            return true;
        } );
    }

    // === REST API TESTS ===

    private function test_api_health_endpoint() {
        $this->test( 'REST API health endpoint', function() {
            $response = wp_remote_get( rest_url( 'slos/v1/health' ) );
            if ( is_wp_error( $response ) ) return false;

            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body, true );

            return isset( $data['success'] ) && $data['success'] === true;
        } );
    }

    private function test_api_authentication() {
        $this->test( 'REST API authentication', function() {
            // Health endpoint should work without auth
            $response = wp_remote_get( rest_url( 'slos/v1/health' ) );
            $code = wp_remote_retrieve_response_code( $response );

            return $code === 200;
        } );
    }

    // === WORDPRESS INTEGRATION TESTS ===

    private function test_plugin_activated() {
        $this->test( 'Plugin activated', function() {
            $activated = get_option( 'slos_activated' );
            return $activated === true;
        } );
    }

    private function test_capabilities_exist() {
        $this->test( 'Custom capabilities exist', function() {
            $admin = get_role( 'administrator' );
            if ( ! $admin ) return false;

            $caps = [
                'slos_manage_settings',
                'slos_read_data',
                'slos_create_data',
            ];

            foreach ( $caps as $cap ) {
                if ( ! $admin->has_cap( $cap ) ) return false;
            }

            return true;
        } );
    }

    private function test_cron_scheduled() {
        $this->test( 'Cron jobs scheduled', function() {
            $events = [
                'slos_daily_cleanup',
                'slos_weekly_report',
                'slos_monthly_retention',
            ];

            foreach ( $events as $event ) {
                $timestamp = wp_next_scheduled( $event );
                if ( ! $timestamp ) return false;
            }

            return true;
        } );
    }

    private function test_admin_menu_exists() {
        $this->test( 'Admin menu registered', function() {
            global $menu;

            foreach ( $menu as $item ) {
                if ( isset( $item[2] ) && strpos( $item[2], 'shahi-legalops' ) !== false ) {
                    return true;
                }
            }

            return false;
        } );
    }

    // === HELPER METHODS ===

    private function test( $name, $callback ) {
        try {
            $result = $callback();
            if ( $result ) {
                echo "✅ {$name}\n";
                $this->passed++;
            } else {
                echo "❌ {$name}\n";
                $this->failed++;
            }
        } catch ( \Exception $e ) {
            echo "❌ {$name}: {$e->getMessage()}\n";
            $this->failed++;
        }
    }

    private function print_summary() {
        $total = $this->passed + $this->failed;
        $percentage = $total > 0 ? round( ( $this->passed / $total ) * 100, 2 ) : 0;

        echo "\n==================================\n";
        echo "TEST SUMMARY\n";
        echo "==================================\n";
        echo "Total Tests:  {$total}\n";
        echo "Passed:       {$this->passed} ✅\n";
        echo "Failed:       {$this->failed} ❌\n";
        echo "Success Rate: {$percentage}%\n";
        echo "==================================\n";

        if ( $this->failed === 0 ) {
            echo "\n🎉 ALL TESTS PASSED! Phase 1 is complete and ready.\n";
        } else {
            echo "\n⚠️  Some tests failed. Please review and fix before proceeding.\n";
        }
    }
}

// Run tests
$test_suite = new Phase_1_Integration_Test();
$test_suite->run_all_tests();
```

2. **Create Test Runner Script**

Location: `tests/run-integration-tests.sh`

```bash
#!/bin/bash

echo "=================================="
echo "Running Phase 1 Integration Tests"
echo "=================================="
echo ""

# Check WordPress CLI
if ! command -v wp &> /dev/null; then
    echo "❌ WordPress CLI not found"
    exit 1
fi

# Run the test
wp eval-file tests/integration/test-phase-1-integration.php

echo ""
echo "=================================="
echo "Test execution complete"
echo "=================================="
```

Make it executable:
```bash
chmod +x tests/run-integration-tests.sh
```

3. **Create Verification Checklist**

Location: `tests/PHASE-1-VERIFICATION.md`

```markdown
# Phase 1 Verification Checklist

## Database Infrastructure

- [ ] All 7 tables created
- [ ] Tables have correct structure
- [ ] Indexes exist
- [ ] Foreign keys configured
- [ ] Migration runner works

## Repository Layer

- [ ] Base_Repository exists
- [ ] CRUD operations work
- [ ] Complex queries work
- [ ] Transactions supported
- [ ] Error handling works

## Service Layer

- [ ] Base_Service exists
- [ ] Caching works
- [ ] Validation works
- [ ] WordPress hooks integrated
- [ ] Error handling works

## REST API

- [ ] Base_REST_Controller exists
- [ ] Health endpoint works
- [ ] Authentication works
- [ ] Permission checks work
- [ ] CORS headers present

## WordPress Integration

- [ ] Plugin activates without errors
- [ ] Custom capabilities added
- [ ] Cron jobs scheduled
- [ ] Admin menu visible
- [ ] Default options set

## Code Quality

- [ ] No PHP syntax errors
- [ ] Autoloader working
- [ ] All classes namespaced correctly
- [ ] No security vulnerabilities
- [ ] Error logging works

## Final Checks

- [ ] All integration tests pass
- [ ] Can deactivate/reactivate plugin
- [ ] Database persists after deactivation
- [ ] No errors in debug.log
- [ ] Ready for Phase 2
```

4. **Run all tests**

```bash
# Run integration tests
./tests/run-integration-tests.sh

# Or directly
wp eval-file tests/integration/test-phase-1-integration.php
```

5. **Manual verification**

```bash
# Check database
wp db query "SHOW TABLES LIKE 'wp_slos_%'"

# Check plugin status
wp plugin status shahi-legalops-suite

# Check options
wp option get slos_activated
wp option get slos_version

# Check cron
wp cron event list | grep slos

# Check capabilities
wp cap list administrator | grep slos

# Check health endpoint
curl http://localhost/wp-json/slos/v1/health

# Check autoloader
wp eval 'var_dump(class_exists("Shahi\LegalOps\Database\Repositories\Consent_Repository"));'
```

OUTPUT STATE:
✅ Comprehensive integration test suite
✅ 14+ integration tests covering all infrastructure
✅ Test runner script
✅ Verification checklist
✅ All tests passing
✅ Phase 1 complete and verified

VERIFICATION:

1. **Run integration tests:**
```bash
wp eval-file tests/integration/test-phase-1-integration.php
```
Expected: All tests pass (100% success rate)

2. **Check test output:**
- ✅ Database connection
- ✅ All tables exist
- ✅ Table structure correct
- ✅ Repository CRUD works
- ✅ Repository queries work
- ✅ Service caching works
- ✅ Service validation works
- ✅ API health endpoint works
- ✅ API authentication works
- ✅ Plugin activated
- ✅ Capabilities exist
- ✅ Cron scheduled
- ✅ Admin menu exists

3. **Manual checks:**
```bash
# Verify 7 tables
wp db query "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name LIKE 'wp_slos_%'"
```
Expected: 7

4. **Check for errors:**
```bash
tail -f wp-content/debug.log
```
Expected: No errors related to SLOS

SUCCESS CRITERIA:
✅ All 14 integration tests pass
✅ No PHP errors
✅ Database structure correct
✅ All features working together
✅ Plugin lifecycle works (activate/deactivate)
✅ Ready for Phase 2

ROLLBACK:
```bash
# If tests fail, check specific component
# Use individual task rollback procedures

# Full reset
wp plugin deactivate shahi-legalops-suite
wp db query "DROP TABLE IF EXISTS wp_slos_consent, wp_slos_dsr_requests, wp_slos_legal_documents, wp_slos_cookie_scanners, wp_slos_vendor_lists, wp_slos_form_submissions, wp_slos_accessibility_issues"
wp plugin activate shahi-legalops-suite
```

TROUBLESHOOTING:

**Problem 1: Some tests fail**
```bash
# Run tests with debug
WP_DEBUG=true wp eval-file tests/integration/test-phase-1-integration.php

# Check specific component
wp eval 'use Shahi\LegalOps\Database\Repositories\Consent_Repository; $r = new Consent_Repository(); var_dump($r->count());'
```

**Problem 2: Database tests fail**
```bash
# Check database connection
wp db check

# Verify tables
wp db query "SHOW TABLES LIKE 'wp_slos_%'"

# Re-run migrations
wp eval 'use Shahi\LegalOps\Database\Migrations\Runner; (new Runner())->run_all();'
```

**Problem 3: API tests fail**
```bash
# Check REST API
wp rest route list | grep slos

# Test health endpoint directly
wp eval 'echo wp_remote_retrieve_body(wp_remote_get(rest_url("slos/v1/health")));'
```

**Problem 4: Hook tests fail**
```bash
# Check cron
wp cron event list

# Reschedule
wp cron event run slos_daily_cleanup --due-now
```

COMMIT MESSAGE:
```
test(integration): Add Phase 1 integration tests

- Create comprehensive test suite (14 tests)
- Test database infrastructure
- Test repository layer
- Test service layer
- Test REST API
- Test WordPress integration
- Add test runner script
- Create verification checklist

All tests passing. Phase 1 verified and complete.

Task: 1.8 (6-8 hours)
Phase 1: COMPLETE ✅
Next: Phase 2 - Consent Management
```

WHAT TO REPORT BACK:
"✅ TASK 1.8 COMPLETE

Created:
- Integration test suite (14 tests)
- Test runner script
- Verification checklist

Test Results:
- ✅ Database tests (3/3)
- ✅ Repository tests (2/2)
- ✅ Service tests (2/2)
- ✅ REST API tests (2/2)
- ✅ WordPress integration tests (4/4)
- ✅ Code quality tests (1/1)

Success Rate: 100%

Verification passed:
- ✅ All 14 integration tests pass
- ✅ No PHP errors
- ✅ All components working together
- ✅ Plugin lifecycle verified

🎉 PHASE 1 COMPLETE AND VERIFIED

Foundation is solid:
- ✅ Database infrastructure
- ✅ Repository pattern
- ✅ Service layer
- ✅ REST API
- ✅ WordPress hooks

📍 Ready for PHASE 2: [task-2.1-consent-repository.md](../phase-2/task-2.1-consent-repository.md)

Phase 1 Total Time: 35-45 hours
Phase 2 Starting: Consent Management Module"
```

---

## ✅ COMPLETION CHECKLIST

- [ ] Integration test suite created
- [ ] All 14 tests passing
- [ ] Test runner script created
- [ ] Verification checklist completed
- [ ] No PHP errors
- [ ] Database verified
- [ ] All components integrated
- [ ] Committed to git
- [ ] Phase 1 complete ✅
- [ ] Ready for Phase 2

---

**Status:** ✅ Ready to execute  
**Time:** 6-8 hours  
**Next:** [../phase-2/task-2.1-consent-repository.md](../phase-2/task-2.1-consent-repository.md)

---

## 🎉 PHASE 1 MILESTONE

**Congratulations!** After completing this task, Phase 1 is done.

You'll have:
- ✅ Solid database foundation
- ✅ Clean architecture (Repository → Service → API)
- ✅ REST API infrastructure
- ✅ WordPress integration
- ✅ Complete test coverage

**Time to Phase 1 completion:** 35-45 hours  
**Progress:** 10% of total project  
**Next milestone:** Phase 2 (MVP with Consent Management)
