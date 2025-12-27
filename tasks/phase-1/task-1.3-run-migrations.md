# TASK 1.3: Run Database Migrations

**Phase:** 1 (Assessment & Cleanup)  
**Effort:** 2-3 hours  
**Prerequisites:** TASK 1.2 complete (migration files created)  
**Next Task:** task-1.4-base-repository.md  

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 1.3 of the Shahi LegalOps Suite plugin.

TASK: Run Database Migrations
PHASE: 1 (Assessment & Cleanup)
EFFORT: 2-3 hours
DEPENDENCY: TASK 1.2 must be complete (migration files exist in includes/Database/Migrations/)
LOCATION: c:\docker-wp\html\wp-content\plugins\Shahi LegalOps Suite - 3.0.1

CONTEXT:
Now you'll actually run the migration files created in TASK 1.2 to create the 7 new WordPress tables 
in the database. This is where the database schema becomes reality.

The migrations use WordPress's dbDelta() function which is intelligent about creating and updating 
tables safely. It can create new tables or modify existing ones without data loss.

INPUT STATE (verify these exist):
- [ ] TASK 1.2 complete: Migration files exist in includes/Database/Migrations/
- [ ] 7 migration files created (migration_*.php)
- [ ] Runner.php file exists
- [ ] WordPress database is accessible
- [ ] wp-config.php has correct database credentials

YOUR TASK:

1. VERIFY MIGRATION FILES EXIST
   
   Check that all migration files are in place:
   Command: Get-ChildItem -Path "includes\Database\Migrations" -Filter "migration_*.php"
   
   Expected: 7 files shown
   
   If not 7 files, STOP and go back to TASK 1.2.

2. TEST AUTOLOADER CAN FIND CLASSES
   
   Check if migration classes can be autoloaded:
   Command: wp eval 'echo class_exists("ComplyFlow\\Database\\Migrations\\Runner") ? "✓ Runner class found" : "✗ Class not found";'
   
   Expected: "✓ Runner class found"
   
   If class not found, check composer autoload:
   Command: composer dump-autoload
   Then try again.

3. RUN ALL MIGRATIONS VIA WP-CLI
   
   Execute the migration runner:
   Command: wp eval '
   require_once "includes/Database/Migrations/Runner.php";
   $results = \ComplyFlow\Database\Migrations\Runner::run_all();
   foreach ($results as $migration => $status) {
       echo "$migration: $status\n";
   }
   '
   
   Expected output (all should show "OK"):
   ```
   Migration_2025_12_20_consent_table: OK
   Migration_2025_12_20_dsr_requests_table: OK
   Migration_2025_12_20_documents_table: OK
   Migration_2025_12_20_trackers_table: OK
   Migration_2025_12_20_vendors_table: OK
   Migration_2025_12_20_form_submissions_table: OK
   Migration_2025_12_20_form_issues_table: OK
   ```
   
   If any show "FAILED", check the error log and troubleshoot.

4. VERIFY TABLES WERE CREATED
   
   List all database tables:
   Command: wp db tables
   
   Then filter for complyflow tables:
   Command: wp db tables | Select-String "complyflow"
   
   Expected output (7 tables):
   ```
   wp_complyflow_consent
   wp_complyflow_dsr_requests
   wp_complyflow_documents
   wp_complyflow_trackers
   wp_complyflow_vendors
   wp_complyflow_form_submissions
   wp_complyflow_form_issues
   ```

5. VERIFY TABLE STRUCTURES
   
   Check the structure of each table. Example for consent table:
   Command: wp db query "DESCRIBE wp_complyflow_consent;"
   
   Expected columns:
   - id (BIGINT, PK, auto_increment)
   - user_id (BIGINT, nullable)
   - ip_hash (VARCHAR 64)
   - type (VARCHAR 50)
   - status (VARCHAR 20)
   - metadata (JSON)
   - created_at (DATETIME)
   - updated_at (DATETIME)
   
   Repeat for at least 2-3 other tables to verify structure.

6. VERIFY INDEXES EXIST
   
   Check indexes on consent table:
   Command: wp db query "SHOW INDEXES FROM wp_complyflow_consent;"
   
   Expected: Should show indexes on PRIMARY, user_id, type, status, ip_hash
   
   Verify at least the PRIMARY key exists.

7. VERIFY FOREIGN KEYS
   
   Check if foreign keys are set up:
   Command: wp db query "SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_NAME LIKE '%complyflow%';"
   
   Expected: Should show foreign key from wp_complyflow_consent.user_id to wp_users.ID
   
   Note: Some MySQL configurations might not show this. If empty, that's OK - continue.

8. TEST INSERT OPERATION
   
   Insert a test record into consent table:
   Command: wp eval '
   global $wpdb;
   $result = $wpdb->insert(
       $wpdb->prefix . "complyflow_consent",
       [
           "user_id" => 1,
           "type" => "analytics",
           "status" => "accepted",
           "ip_hash" => "test_hash_" . time()
       ]
   );
   echo "Insert result: " . ($result ? "SUCCESS (ID: " . $wpdb->insert_id . ")" : "FAILED");
   echo "\nLast error: " . $wpdb->last_error;
   '
   
   Expected output: "Insert result: SUCCESS (ID: [positive number])"

9. TEST READ OPERATION
   
   Read the test record:
   Command: wp eval '
   global $wpdb;
   $result = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}complyflow_consent ORDER BY id DESC LIMIT 1");
   if ($result) {
       echo "✓ Read successful\n";
       echo "Type: " . $result->type . "\n";
       echo "Status: " . $result->status . "\n";
   } else {
       echo "✗ Read failed\n";
   }
   '
   
   Expected output: Shows type = "analytics", status = "accepted"

10. TEST UPDATE OPERATION
   
    Update the test record:
    Command: wp eval '
    global $wpdb;
    $latest = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}complyflow_consent ORDER BY id DESC LIMIT 1");
    $result = $wpdb->update(
        $wpdb->prefix . "complyflow_consent",
        ["status" => "rejected"],
        ["id" => $latest->id]
    );
    echo "Update result: " . ($result !== false ? "SUCCESS" : "FAILED");
    $updated = $wpdb->get_row("SELECT status FROM {$wpdb->prefix}complyflow_consent WHERE id = {$latest->id}");
    echo "\nNew status: " . $updated->status;
    '
    
    Expected output: "Update result: SUCCESS" and "New status: rejected"

11. TEST DELETE OPERATION
    
    Delete the test record:
    Command: wp eval '
    global $wpdb;
    $latest = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}complyflow_consent ORDER BY id DESC LIMIT 1");
    $result = $wpdb->delete(
        $wpdb->prefix . "complyflow_consent",
        ["id" => $latest->id]
    );
    echo "Delete result: " . ($result !== false ? "SUCCESS" : "FAILED");
    $check = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}complyflow_consent WHERE id = {$latest->id}");
    echo "\nRecord still exists: " . ($check > 0 ? "YES (ERROR)" : "NO (CORRECT)");
    '
    
    Expected output: "Delete result: SUCCESS" and "Record still exists: NO (CORRECT)"

12. CHECK FOR DATABASE ERRORS
    
    Command: wp eval 'global $wpdb; echo "Last error: " . ($wpdb->last_error ?: "None");'
    
    Expected output: "Last error: None"

13. TEST TABLE PERSISTENCE (PLUGIN DEACTIVATE/ACTIVATE)
    
    Test that tables persist when plugin is deactivated:
    Command: wp plugin deactivate shahi-legalops-suite
    Command: wp db tables | Select-String "complyflow"
    
    Expected: All 7 tables should still exist
    
    Reactivate:
    Command: wp plugin activate shahi-legalops-suite
    
    Verify tables still exist:
    Command: wp db tables | Select-String "complyflow"
    
    Expected: All 7 tables still present

14. CREATE MIGRATION RESULTS DOCUMENT
    
    Create file: migration-run-results.md in plugin root
    
    Content:
    ```markdown
    # Migration Execution Results - TASK 1.3
    
    Date: [Current Date]
    Executed by: AI Agent
    
    ## Migrations Executed
    - [x] migration_2025_12_20_consent_table
    - [x] migration_2025_12_20_dsr_requests_table
    - [x] migration_2025_12_20_documents_table
    - [x] migration_2025_12_20_trackers_table
    - [x] migration_2025_12_20_vendors_table
    - [x] migration_2025_12_20_form_submissions_table
    - [x] migration_2025_12_20_form_issues_table
    
    ## Tables Created
    - [x] wp_complyflow_consent
    - [x] wp_complyflow_dsr_requests
    - [x] wp_complyflow_documents
    - [x] wp_complyflow_trackers
    - [x] wp_complyflow_vendors
    - [x] wp_complyflow_form_submissions
    - [x] wp_complyflow_form_issues
    
    ## Tests Passed
    - [x] Table structure verified (DESCRIBE command)
    - [x] Indexes verified (SHOW INDEXES)
    - [x] Foreign keys checked
    - [x] Insert operation successful (CREATE)
    - [x] Read operation successful (READ)
    - [x] Update operation successful (UPDATE)
    - [x] Delete operation successful (DELETE)
    - [x] No database errors
    - [x] Tables persist after plugin deactivate/reactivate
    
    ## CRUD Operations Test Results
    - CREATE: ✓ PASSED
    - READ: ✓ PASSED
    - UPDATE: ✓ PASSED
    - DELETE: ✓ PASSED
    
    ## Database State
    - Total tables: 7
    - All structures match schema
    - All indexes created
    - Foreign keys configured (where supported)
    - No errors in error log
    
    ## Ready for Next Phase
    - [x] Database schema fully implemented
    - [x] All tables operational
    - [x] CRUD operations verified
    - [x] Ready for TASK 1.4 (Base Repository Classes)
    ```

OUTPUT STATE (what exists after this task):
- [ ] 7 new tables created in WordPress database
- [ ] All table structures match schema specification from TASK 1.2
- [ ] All indexes created and functional
- [ ] Foreign keys configured (where supported by MySQL version)
- [ ] Migration runner successfully executed
- [ ] All CRUD operations verified working (Create, Read, Update, Delete)
- [ ] migration-run-results.md created with full verification results
- [ ] Tables persist through plugin deactivate/reactivate cycles

VERIFICATION (run these final checks):

1. Count tables:
   Command: (wp db tables | Select-String "complyflow").Count
   Expected: 7

2. Test persistence:
   Command: wp plugin deactivate shahi-legalops-suite && wp plugin activate shahi-legalops-suite && wp db tables | Select-String "complyflow"
   Expected: All 7 tables still exist after cycle

3. Verify no errors:
   Command: wp eval 'global $wpdb; echo $wpdb->last_error ?: "No errors";'
   Expected: "No errors"

4. Check migration results doc:
   Command: Test-Path migration-run-results.md
   Expected: True
   
   Command: Get-Content migration-run-results.md
   Expected: Document shows all checks passed

SUCCESS CRITERIA:
- [ ] All 7 tables created successfully
- [ ] Table structures verified (all columns present with correct types)
- [ ] All indexes created on specified columns
- [ ] Foreign keys configured (consent table -> users table)
- [ ] CRUD operations all working (Create, Read, Update, Delete)
- [ ] No database errors in WP_DEBUG log or $wpdb->last_error
- [ ] Tables persist after plugin deactivate/reactivate
- [ ] migration-run-results.md created with all checks marked passed
- [ ] No data loss during operations
- [ ] All verification tests passed

ROLLBACK (if verification fails):

   1. Drop all created tables using the rollback function:
      Command: wp eval '
      require_once "includes/Database/Migrations/Runner.php";
      $results = \ComplyFlow\Database\Migrations\Runner::rollback_all();
      foreach ($results as $migration => $status) {
          echo "$migration: $status\n";
      }
      '
      
      Expected: All migrations show "DROPPED"

   2. Verify tables are gone:
      Command: wp db tables | Select-String "complyflow"
      Expected: No tables found

   3. Check error messages from step 3 (run_all) to identify which migration failed

   4. Fix the failing migration file in includes/Database/Migrations/

   5. Re-run TASK 1.3 from the beginning

TROUBLESHOOTING:

Problem: "Migration returned FAILED"
Solution: 
  - Check WP_DEBUG log: wp eval 'echo WP_DEBUG_LOG;'
  - Check database error: wp eval 'global $wpdb; echo $wpdb->last_error;'
  - Common issues:
    * SQL syntax error in migration file
    * Database user lacks CREATE TABLE permission
    * Table already exists (not harmful, but rerun after dropping)
  - Review the specific migration file that failed
  - Check MySQL version supports all syntax (JSON, AUTO_INCREMENT, etc.)

Problem: "Table already exists error"
Solution: 
  - This is OK - dbDelta() handles existing tables
  - But if you want clean slate:
    1. Run rollback (see ROLLBACK section above)
    2. Re-run migrations

Problem: "Foreign key constraint fails"
Solution:
  - Ensure wp_users table exists: wp db query "SHOW TABLES LIKE 'wp_users';"
  - Check MySQL/MariaDB version supports foreign keys
  - Some MySQL configs disable foreign keys - this is OK, continue without them
  - The application logic doesn't rely on DB-level foreign keys

Problem: "Class not found" error
Solution:
  - Run: composer dump-autoload
  - Verify namespace in migration files matches exactly
  - Check file paths are correct

Problem: "Permission denied" creating tables
Solution:
  - Check database user has CREATE, ALTER, DROP permissions
  - Command: wp db query "SHOW GRANTS;"
  - Contact database administrator to grant necessary permissions

WHAT TO REPORT BACK:
When this task is complete, report:
1. "✅ TASK 1.3 verification passed"
2. Number of tables created (should be 7)
3. Confirmation all CRUD tests passed
4. Confirmation tables persist through deactivation
5. Any warnings or issues encountered (even if resolved)
6. "Ready to proceed to TASK 1.4"

COMMIT MESSAGE:
After verification passes, commit your changes:
Command: git add migration-run-results.md
Command: git commit -m "TASK 1.3: Database migrations executed, 7 tables created and verified"

NEXT TASK:
After this task is complete and verified, proceed to:
tasks/phase-1/task-1.4-base-repository.md
```

---

## ✅ COMPLETION CHECKLIST

After running this task, verify:

- [ ] All 7 tables created
- [ ] Table structures verified
- [ ] CRUD operations tested
- [ ] migration-run-results.md created
- [ ] Git commit created
- [ ] Ready for TASK 1.4

---

**Status:** Ready to execute  
**Time:** 2-3 hours  
**Next:** task-1.4-base-repository.md
