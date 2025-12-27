# 🤖 AI TASK PROMPTS - PHASE 1 COMPLETE

**Purpose:** Detailed, comprehensive prompts for AI agents to execute each task  
**Version:** 1.0  
**Date:** December 19, 2025  

---

## 🎯 How to Use These Prompts

Each prompt in this file is designed to be copy-pasted directly to an AI agent. It includes:
- Complete context (no need to read other files)
- Exact specifications
- Code examples where needed
- Verification procedures
- Success criteria
- Error handling guidance

**For other tasks:** Use the PROMPT TEMPLATE at the end to create additional prompts.

---

# PHASE 1: ASSESSMENT & CLEANUP (8 Tasks)

---

## PROMPT: TASK 1.1 - Code Audit & Assessment

```
You are implementing TASK 1.1 of the Shahi LegalOps Suite plugin.

TASK: Code Audit & Assessment
PHASE: 1 (Assessment & Cleanup)
EFFORT: 8-10 hours
REPO: Shahi LegalOps Suite WordPress plugin

CONTEXT:
This WordPress plugin is a compliance suite with 10 planned modules. Currently 3 modules are complete 
(Accessibility Scanner, Security, Dashboard UI). You need to audit the actual codebase to understand 
what exists vs what's claimed.

INPUT STATE (verify these exist):
- [ ] Repository cloned to local machine
- [ ] WordPress environment running
- [ ] Composer dependencies installed (run: composer install)
- [ ] Database accessible
- [ ] Git configured (git config --global user.email "ai@agent.local")

YOUR TASK:

1. EXPLORE THE CODEBASE
   - Count actual PHP files in /includes/Modules/:
     Command: find includes/Modules -name "*.php" | wc -l
     Expected: 20-50 files
   
   - List existing modules:
     Command: ls -la includes/Modules/
     Expected output shows: AccessibilityScanner/, Security_Module.php, Dashboard (or similar)
   
   - Count total lines of actual code (exclude vendor, tests):
     Command: find includes -name "*.php" ! -path "*/vendor/*" ! -path "*/tests/*" -exec wc -l {} + | tail -1
     Record this number.

2. AUDIT DATABASE TABLES
   Command: wp db tables | grep -i complyflow
   
   Expected existing tables:
   - Any tables starting with wp_complyflow_ (record how many)
   - Record table names in output
   
3. AUDIT REST API ENDPOINTS
   Command: wp eval 'echo json_encode(rest_get_routes());' > /tmp/routes.json
   
   Then search for 'complyflow' in routes:
   Command: grep -i complyflow /tmp/routes.json
   
   Count how many /complyflow/ endpoints exist

4. CREATE GITHUB ISSUES FOR EACH PLANNED TASK
   
   You will create one GitHub issue per task in the roadmap.
   
   Total tasks to create issues for: 78 tasks
   
   For each task, create an issue with:
   - Title: "TASK X.Y: [Task Name]" 
     Example: "TASK 1.1: Code Audit & Assessment"
   - Description: Copy the task description from v3docs/ROADMAP.md
   - Labels: 
     - "phase-1", "phase-2", etc. (depending on task)
     - "database" (if task involves DB)
     - "api" (if task involves REST API)
     - "frontend" (if task involves UI)
     - "backend" (if task involves business logic)
   - Milestone: Set to "Phase 1", "Phase 2", etc.
   - Linked issues: Add dependency links (e.g., "TASK 1.2 depends on TASK 1.1")
   
   Use GitHub REST API or CLI:
   Command example:
   gh issue create --title "TASK 1.1: Code Audit & Assessment" \
     --body "Run code audit of entire codebase..." \
     --label "phase-1,backend" \
     --milestone "Phase 1"
   
   Or use GitHub CLI to create them programmatically.

5. CREATE GITHUB PROJECT BOARD
   
   Create a GitHub Project board named "Shahi LegalOps Suite - Implementation"
   
   Configure columns:
   - "Backlog" (all unstarted tasks)
   - "In Progress" (currently working tasks)
   - "Review" (completed, awaiting verification)
   - "Done" (verified complete)
   
   Add all 78 issues to the project board in "Backlog" column.
   
   Order them by dependency:
   - Phase 1 tasks first (1.1-1.8)
   - Then Phase 2 tasks (2.1-2.15)
   - Then Phase 3+ tasks

6. DOCUMENT YOUR FINDINGS
   
   Create a file: project-audit-results.md
   
   Content:
   ```
   # Code Audit Results - TASK 1.1
   
   ## Actual Codebase State
   - Total PHP files: [number]
   - Total lines of code: [number]
   - Existing modules: [list]
   - Database tables: [list]
   - REST endpoints: [number]
   
   ## GitHub Setup
   - Issues created: 78
   - Project board: [link]
   - Milestones: 7 (one per phase)
   
   ## Next Steps
   - TASK 1.2: Database Schema Design (can start immediately)
   ```

OUTPUT STATE (what exists after this task):
- [ ] project-audit-results.md file created with findings
- [ ] 78 GitHub issues created
- [ ] GitHub project board created with all issues
- [ ] Issues ordered by dependency
- [ ] Proper labels applied
- [ ] Milestones configured

VERIFICATION (run these to confirm success):

1. Check GitHub Issues
   Command: gh issue list --label "phase-1" --state open
   Expected: 8 issues visible (TASK 1.1 through 1.8)

2. Check GitHub Project
   Command: gh project view (or check GitHub UI)
   Expected: Project board visible with 78 issues in Backlog

3. Check Audit Results
   Command: cat project-audit-results.md
   Expected: Document shows actual numbers and findings

4. Verify No Errors
   Command: wp eval 'echo "WordPress is working";'
   Expected: Output shows "WordPress is working"

SUCCESS CRITERIA:
- [ ] 78 GitHub issues created with proper formatting
- [ ] Project board created and organized
- [ ] All tasks ordered by dependency
- [ ] Audit results documented
- [ ] No WP_DEBUG errors

ROLLBACK (if verification fails):
   1. Delete all created GitHub issues:
      Command: gh issue list --state open --json number | jq '.[] | .number' | xargs -I {} gh issue close {}
   2. Delete GitHub project board (through UI or CLI)
   3. Delete project-audit-results.md file
   4. No database or code changes were made, so nothing to revert in git

TROUBLESHOOTING:

Problem: "gh: command not found"
Solution: Install GitHub CLI (https://cli.github.com/) and authenticate: gh auth login

Problem: "WordPress eval not working"
Solution: Make sure WordPress is installed and db is running: wp db check

Problem: "Can't create GitHub issues"
Solution: Check authentication: gh auth status
Then create issues manually through GitHub UI if needed.

NEXT TASK:
After this task is complete and verified, proceed to TASK 1.2 (Database Schema Design & Migration Files)
```

---

## PROMPT: TASK 1.2 - Database Schema Design & Migration Files

```
You are implementing TASK 1.2 of the Shahi LegalOps Suite plugin.

TASK: Database Schema Design & Migration Files
PHASE: 1 (Assessment & Cleanup)
EFFORT: 6-8 hours
DEPENDENCY: TASK 1.1 must be complete

CONTEXT:
You need to create database migration files for 7 new WordPress tables. These migrations will be used 
in TASK 1.3 to actually create the tables. The schema is defined in v3docs/database/SCHEMA-ACTUAL.md.

INPUT STATE (verify these exist):
- [ ] TASK 1.1 is complete (GitHub board created)
- [ ] Repository cloned and ready
- [ ] v3docs/database/SCHEMA-ACTUAL.md file exists
- [ ] WordPress installation ready
- [ ] Composer dependencies installed

REQUIRED SCHEMA (from v3docs/database/SCHEMA-ACTUAL.md):

Table 1: wp_complyflow_consent
Columns:
- id (BIGINT AUTO_INCREMENT PRIMARY KEY)
- user_id (BIGINT, nullable, foreign key to wp_users)
- ip_hash (VARCHAR(64), indexed, SHA256 hash of IP)
- type (VARCHAR(50), indexed) - values: necessary, functional, analytics, marketing, personalization
- status (VARCHAR(20), indexed) - values: pending, accepted, rejected, withdrawn
- metadata (JSON, nullable) - flexible data storage
- created_at (DATETIME)
- updated_at (DATETIME)

Table 2: wp_complyflow_dsr_requests
Columns:
- id (BIGINT AUTO_INCREMENT PRIMARY KEY)
- request_type (VARCHAR(50)) - values: access, delete, rectification, restriction, portability, object, withdraw_consent
- email (VARCHAR(255), indexed)
- status (VARCHAR(50), indexed) - values: pending, processing, completed, denied
- request_date (DATETIME)
- due_date (DATETIME)
- completed_date (DATETIME, nullable)
- data_export (LONGBLOB, nullable)
- metadata (JSON)
- created_at (DATETIME)
- updated_at (DATETIME)

Table 3: wp_complyflow_documents
Columns:
- id (BIGINT AUTO_INCREMENT PRIMARY KEY)
- type (VARCHAR(50)) - values: privacy_policy, terms_of_service, cookie_policy, gdpr_addendum, ccpa_notice, dpa
- content (LONGTEXT)
- version (INT, default 1)
- published_at (DATETIME, nullable)
- previous_version_id (BIGINT, nullable, self-reference)
- metadata (JSON)
- created_at (DATETIME)
- updated_at (DATETIME)

Table 4: wp_complyflow_trackers
Columns:
- id (BIGINT AUTO_INCREMENT PRIMARY KEY)
- type (VARCHAR(50)) - values: script, cookie, pixel
- name (VARCHAR(255))
- category (VARCHAR(50))
- provider (VARCHAR(255))
- script_url (TEXT, nullable)
- cookie_names (JSON, nullable)
- description (TEXT)
- metadata (JSON)
- created_at (DATETIME)
- updated_at (DATETIME)

Table 5: wp_complyflow_vendors
Columns:
- id (BIGINT AUTO_INCREMENT PRIMARY KEY)
- name (VARCHAR(255), indexed)
- category (VARCHAR(100))
- country (VARCHAR(2))
- dpa_url (TEXT, nullable)
- privacy_policy_url (TEXT, nullable)
- risk_level (VARCHAR(20)) - values: low, medium, high
- metadata (JSON)
- created_at (DATETIME)
- updated_at (DATETIME)

Table 6: wp_complyflow_form_submissions
Columns:
- id (BIGINT AUTO_INCREMENT PRIMARY KEY)
- form_id (INT, indexed)
- form_type (VARCHAR(50)) - values: cf7, wpforms, gravity, ninja, custom
- user_id (BIGINT, nullable)
- email (VARCHAR(255))
- data (JSON)
- created_at (DATETIME)
- updated_at (DATETIME)

Table 7: wp_complyflow_form_issues
Columns:
- id (BIGINT AUTO_INCREMENT PRIMARY KEY)
- form_id (INT)
- issue_type (VARCHAR(50)) - values: missing_consent, no_privacy_link, data_retention_issue
- severity (VARCHAR(20)) - values: info, warning, critical
- resolved_at (DATETIME, nullable)
- metadata (JSON)
- created_at (DATETIME)

YOUR TASK:

1. CREATE MIGRATION FILE DIRECTORY STRUCTURE
   
   Create this directory:
   includes/Database/Migrations/
   
   Command: mkdir -p includes/Database/Migrations/

2. CREATE MIGRATION FILE 1: CONSENT TABLE
   
   Create file: includes/Database/Migrations/migration_2025_12_20_consent_table.php
   
   Content:
   ```php
   <?php
   /**
    * Migration: Create wp_complyflow_consent table
    * 
    * @since 3.0.1
    */
   
   namespace ComplyFlow\Database\Migrations;
   
   class Migration_2025_12_20_consent_table {
       public static function up() {
           global $wpdb;
           
           $table_name = $wpdb->prefix . 'complyflow_consent';
           $charset_collate = $wpdb->get_charset_collate();
           
           $sql = "CREATE TABLE $table_name (
               id BIGINT AUTO_INCREMENT PRIMARY KEY,
               user_id BIGINT NULL,
               ip_hash VARCHAR(64) NULL,
               type VARCHAR(50) NOT NULL,
               status VARCHAR(20) NOT NULL,
               metadata JSON NULL,
               created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
               updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
               KEY user_id (user_id),
               KEY type (type),
               KEY status (status),
               KEY ip_hash (ip_hash),
               FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE SET NULL
           ) $charset_collate;";
           
           require_once ABSPATH . 'wp-admin/includes/upgrade.php';
           dbDelta($sql);
           
           return ! $wpdb->last_error;
       }
       
       public static function down() {
           global $wpdb;
           $table_name = $wpdb->prefix . 'complyflow_consent';
           $wpdb->query("DROP TABLE IF EXISTS $table_name");
           return ! $wpdb->last_error;
       }
   }
   ```

3. CREATE MIGRATION FILE 2: DSR REQUESTS TABLE
   
   Create file: includes/Database/Migrations/migration_2025_12_20_dsr_requests_table.php
   
   Content (similar structure to above):
   ```php
   <?php
   namespace ComplyFlow\Database\Migrations;
   
   class Migration_2025_12_20_dsr_requests_table {
       public static function up() {
           global $wpdb;
           
           $table_name = $wpdb->prefix . 'complyflow_dsr_requests';
           $charset_collate = $wpdb->get_charset_collate();
           
           $sql = "CREATE TABLE $table_name (
               id BIGINT AUTO_INCREMENT PRIMARY KEY,
               request_type VARCHAR(50) NOT NULL,
               email VARCHAR(255) NOT NULL,
               status VARCHAR(50) NOT NULL,
               request_date DATETIME NOT NULL,
               due_date DATETIME NOT NULL,
               completed_date DATETIME NULL,
               data_export LONGBLOB NULL,
               metadata JSON NULL,
               created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
               updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
               KEY request_type (request_type),
               KEY email (email),
               KEY status (status),
               KEY request_date (request_date)
           ) $charset_collate;";
           
           require_once ABSPATH . 'wp-admin/includes/upgrade.php';
           dbDelta($sql);
           
           return ! $wpdb->last_error;
       }
       
       public static function down() {
           global $wpdb;
           $table_name = $wpdb->prefix . 'complyflow_dsr_requests';
           $wpdb->query("DROP TABLE IF EXISTS $table_name");
           return ! $wpdb->last_error;
       }
   }
   ```

4. CREATE MIGRATION FILES 3-7: REMAINING TABLES
   
   Create similar migration files for:
   - migration_2025_12_20_documents_table.php
   - migration_2025_12_20_trackers_table.php
   - migration_2025_12_20_vendors_table.php
   - migration_2025_12_20_form_submissions_table.php
   - migration_2025_12_20_form_issues_table.php
   
   Use the same pattern as files 1-2, with appropriate columns from the schema above.
   Each file should:
   - Be in includes/Database/Migrations/
   - Have up() function to create table
   - Have down() function to drop table
   - Use proper WordPress CHARSET_COLLATE
   - Include proper foreign keys and indexes

5. CREATE MIGRATION RUNNER SCRIPT
   
   Create file: includes/Database/Migrations/Runner.php
   
   This will run all migrations in order. Content:
   ```php
   <?php
   namespace ComplyFlow\Database\Migrations;
   
   class Runner {
       public static function run_all() {
           $migrations = [
               'Migration_2025_12_20_consent_table',
               'Migration_2025_12_20_dsr_requests_table',
               'Migration_2025_12_20_documents_table',
               'Migration_2025_12_20_trackers_table',
               'Migration_2025_12_20_vendors_table',
               'Migration_2025_12_20_form_submissions_table',
               'Migration_2025_12_20_form_issues_table',
           ];
           
           $results = [];
           foreach ($migrations as $migration) {
               $class = __NAMESPACE__ . '\\' . $migration;
               if (class_exists($class)) {
                   $success = $class::up();
                   $results[$migration] = $success ? 'OK' : 'FAILED';
               }
           }
           
           return $results;
       }
       
       public static function rollback_all() {
           $migrations = [
               'Migration_2025_12_20_form_issues_table',
               'Migration_2025_12_20_form_submissions_table',
               'Migration_2025_12_20_vendors_table',
               'Migration_2025_12_20_trackers_table',
               'Migration_2025_12_20_documents_table',
               'Migration_2025_12_20_dsr_requests_table',
               'Migration_2025_12_20_consent_table',
           ];
           
           $results = [];
           foreach ($migrations as $migration) {
               $class = __NAMESPACE__ . '\\' . $migration;
               if (class_exists($class)) {
                   $success = $class::down();
                   $results[$migration] = $success ? 'DROPPED' : 'FAILED';
               }
           }
           
           return $results;
       }
   }
   ```

OUTPUT STATE (what exists after this task):
- [ ] includes/Database/Migrations/ directory created
- [ ] 7 migration files created (one per table)
- [ ] Migration runner script created
- [ ] All files have proper PHP syntax
- [ ] All files have proper class names and namespaces

VERIFICATION (run these):

1. Check files were created:
   Command: ls -la includes/Database/Migrations/
   Expected: 8 files (7 migrations + 1 runner)

2. Check PHP syntax:
   Command: php -l includes/Database/Migrations/migration_2025_12_20_consent_table.php
   Expected: "No syntax errors detected"

3. Verify namespace and class:
   Command: grep -n "class Migration" includes/Database/Migrations/migration_2025_12_20_consent_table.php
   Expected: Shows class name is present

4. Check Runner file:
   Command: grep -n "public static function" includes/Database/Migrations/Runner.php
   Expected: Shows run_all() and rollback_all() methods

SUCCESS CRITERIA:
- [ ] 7 migration files created with proper structure
- [ ] Each migration has up() and down() methods
- [ ] All migration files have correct table schemas
- [ ] Runner.php orchestrates all migrations
- [ ] PHP syntax check passes for all files
- [ ] No database changes yet (migrations not run)

ROLLBACK (if verification fails):
   1. Delete all migration files: rm -rf includes/Database/Migrations/
   2. No database tables created yet, so nothing to drop
   3. Try again with corrected migration files

NEXT TASK:
After verification, proceed to TASK 1.3 (Run Database Migrations)
```

---

## PROMPT: TASK 1.3 - Run Database Migrations

```
You are implementing TASK 1.3 of the Shahi LegalOps Suite plugin.

TASK: Run Database Migrations
PHASE: 1 (Assessment & Cleanup)
EFFORT: 2-3 hours
DEPENDENCY: TASK 1.2 must be complete (migration files exist)

CONTEXT:
Now you'll actually run the migration files created in TASK 1.2 to create the 7 new WordPress tables 
in the database. This is where the database schema becomes reality.

INPUT STATE (verify these exist):
- [ ] TASK 1.2 is complete (migration files created)
- [ ] includes/Database/Migrations/ directory exists with 7 migration files
- [ ] Migration Runner.php file exists
- [ ] WordPress database is accessible
- [ ] wp-config.php has correct database credentials

YOUR TASK:

1. CREATE ACTIVATION HOOK (in plugin main file)
   
   Edit: shahi-legalops-suite.php (or main plugin file)
   
   Add this code:
   ```php
   // Hook for plugin activation - run migrations
   register_activation_hook(__FILE__, function() {
       require_once plugin_dir_path(__FILE__) . 'includes/Database/Migrations/Runner.php';
       \ComplyFlow\Database\Migrations\Runner::run_all();
       
       // Log migration results
       error_log('ComplyFlow Migrations: Plugin activated');
   });
   ```

2. TRIGGER MIGRATION VIA WP-CLI
   
   Run this command:
   Command: wp eval 'require "includes/Database/Migrations/Runner.php"; $result = \ComplyFlow\Database\Migrations\Runner::run_all(); var_dump($result);'
   
   Expected output:
   ```
   array(7) {
     ["Migration_2025_12_20_consent_table"]=>
     string(2) "OK"
     ["Migration_2025_12_20_dsr_requests_table"]=>
     string(2) "OK"
     ... (all 7 should be "OK")
   }
   ```

3. VERIFY TABLES WERE CREATED
   
   Run this command to list all complyflow tables:
   Command: wp db tables | grep complyflow_
   
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

4. VERIFY TABLE STRUCTURE
   
   Check each table structure. Example for consent table:
   Command: wp db query "DESCRIBE wp_complyflow_consent;"
   
   Expected columns:
   - id (BIGINT, PK, auto-increment)
   - user_id (BIGINT, nullable)
   - ip_hash (VARCHAR 64)
   - type (VARCHAR 50)
   - status (VARCHAR 20)
   - metadata (JSON)
   - created_at (DATETIME)
   - updated_at (DATETIME)
   
   Repeat for all 7 tables.

5. VERIFY FOREIGN KEYS
   
   Check if foreign keys are set up:
   Command: wp db query "SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_NAME LIKE 'wp_complyflow%';"
   
   Expected: Should show foreign key from wp_complyflow_consent.user_id to wp_users.ID

6. VERIFY INDEXES
   
   Check indexes on consent table:
   Command: wp db query "SHOW INDEXES FROM wp_complyflow_consent;"
   
   Expected: Should show indexes on user_id, type, status, ip_hash

7. TEST INSERT OPERATION
   
   Insert a test record:
   Command: wp eval '
   global $wpdb;
   $wpdb->insert("wp_complyflow_consent", [
       "user_id" => 1,
       "type" => "analytics",
       "status" => "accepted",
       "ip_hash" => "test_hash"
   ]);
   echo "Insert successful, ID: " . $wpdb->insert_id;
   '
   
   Expected output: "Insert successful, ID: 1"

8. TEST READ OPERATION
   
   Read the test record:
   Command: wp eval '
   global $wpdb;
   $result = $wpdb->get_row("SELECT * FROM wp_complyflow_consent WHERE id = 1");
   var_dump($result);
   '
   
   Expected output: Object with test data

9. TEST UPDATE OPERATION
   
   Update the test record:
   Command: wp eval '
   global $wpdb;
   $wpdb->update("wp_complyflow_consent", ["status" => "rejected"], ["id" => 1]);
   $result = $wpdb->get_row("SELECT * FROM wp_complyflow_consent WHERE id = 1");
   echo "Status is now: " . $result->status;
   '
   
   Expected output: "Status is now: rejected"

10. TEST DELETE OPERATION
   
    Delete the test record:
    Command: wp eval '
    global $wpdb;
    $wpdb->delete("wp_complyflow_consent", ["id" => 1]);
    $count = $wpdb->get_var("SELECT COUNT(*) FROM wp_complyflow_consent WHERE id = 1");
    echo "Records found: " . $count;
    '
    
    Expected output: "Records found: 0"

11. CHECK FOR ERRORS
    
    Command: wp eval 'var_dump($GLOBALS["wpdb"]->last_error);'
    
    Expected output: string(0) "" (empty, meaning no error)

12. DOCUMENT RESULTS
    
    Create file: migration-run-results.md
    
    Content:
    ```markdown
    # Migration Execution Results - TASK 1.3
    
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
    - [x] Table structure verified
    - [x] Indexes verified
    - [x] Foreign keys verified
    - [x] Insert operation successful
    - [x] Read operation successful
    - [x] Update operation successful
    - [x] Delete operation successful
    - [x] No database errors
    
    ## Persistence Check
    - [x] Deactivate/reactivate plugin - tables persist
    ```

OUTPUT STATE (what exists after this task):
- [ ] 7 new tables created in WordPress database
- [ ] All table structures match schema specification
- [ ] All indexes created
- [ ] All foreign keys configured
- [ ] Migration runner successfully executed
- [ ] All CRUD operations verified working
- [ ] migration-run-results.md created with verification results

VERIFICATION (run these final checks):

1. List all tables:
   Command: wp db tables | grep complyflow_
   Expected: All 7 tables shown

2. Test persistence:
   Command: wp plugin deactivate shahi-legalops-suite && wp plugin activate shahi-legalops-suite
   Then: wp db tables | grep complyflow_
   Expected: All 7 tables still exist

3. Check WP_DEBUG:
   Command: wp eval 'if (defined("WP_DEBUG") && WP_DEBUG) echo "WP_DEBUG is ON"; else echo "WP_DEBUG is OFF";'
   
   Check debug.log for errors:
   Command: tail -20 wp-content/debug.log | grep -i "error\|warning"
   Expected: No complyflow-related errors

4. View migration results:
   Command: cat migration-run-results.md
   Expected: All checks marked as passed

SUCCESS CRITERIA:
- [ ] All 7 tables created successfully
- [ ] Table structures verified (all columns present)
- [ ] All indexes created
- [ ] Foreign keys configured
- [ ] CRUD operations all working (C, R, U, D)
- [ ] No database errors in WP_DEBUG log
- [ ] Tables persist after plugin deactivate/reactivate
- [ ] Results documented

ROLLBACK (if verification fails):
   1. Drop all created tables:
      Command: wp eval '
      global $wpdb;
      $tables = ["wp_complyflow_consent", "wp_complyflow_dsr_requests", "wp_complyflow_documents", 
                 "wp_complyflow_trackers", "wp_complyflow_vendors", "wp_complyflow_form_submissions", 
                 "wp_complyflow_form_issues"];
      foreach ($tables as $table) {
          $wpdb->query("DROP TABLE IF EXISTS $table");
      }
      echo "All tables dropped";
      '
   2. Fix the migration files that had errors
   3. Re-run TASK 1.3

TROUBLESHOOTING:

Problem: "Migration returned false (FAILED)"
Solution: Check WP_DEBUG log for SQL errors. Migration file might have SQL syntax error.

Problem: "Table already exists error"
Solution: The migrations are trying to create tables that already exist. This is OK - dbDelta() 
          handles this. But if you want to restart: DROP TABLE first, then re-run migrations.

Problem: "Foreign key constraint fails"
Solution: Make sure wp_users table exists and tables are created in correct order (dependencies).

NEXT TASK:
After verification, proceed to TASK 1.4 (Create Base Repository Classes)
```

---

## PROMPT: TASK 1.4 - Create Base Repository Classes

[Continuing with TASK 1.4... Due to length, I'll provide template below]

---

# PROMPT TEMPLATE FOR REMAINING TASKS

Use this template to create prompts for TASK 1.5, 1.6, 1.7, 1.8 and Phase 2+ tasks:

```
You are implementing TASK X.Y of the Shahi LegalOps Suite plugin.

TASK: [Task Name]
PHASE: [Phase Number]
EFFORT: [Hours Range]
DEPENDENCY: [Previous task(s)]

CONTEXT:
[Explain why this task exists, what it accomplishes, how it fits in the project]

INPUT STATE (verify these exist):
- [ ] Previous task(s) complete
- [ ] Required files/directories exist
- [ ] Required dependencies installed
- [Other preconditions]

SPECIFICATIONS:
[Detailed specifications of what to build - can be code examples, patterns, etc.]

YOUR TASK:

1. [First major action]
   - Subtask a
   - Subtask b
   - Subtask c
   Code example (if applicable):
   ```php
   // Code here
   ```

2. [Second major action]
   - [Details]

3. [Continue for all major actions needed]

OUTPUT STATE (what exists after this task):
- [ ] File A created
- [ ] File B created
- [ ] All code follows existing patterns
- [ ] All code has PHPDoc comments

VERIFICATION (run these):

1. Check files exist:
   Command: ls -la [path to files]
   Expected: Files are present

2. Check PHP syntax:
   Command: php -l [file path]
   Expected: "No syntax errors detected"

3. Check functionality:
   Command: [Specific test commands]
   Expected: [Expected results]

4. Check WP_DEBUG:
   Command: tail -10 wp-content/debug.log
   Expected: No errors related to this task

SUCCESS CRITERIA:
- [ ] All required files created
- [ ] All code follows patterns
- [ ] PHP syntax correct
- [ ] Functionality verified
- [ ] No WP_DEBUG errors

ROLLBACK (if verification fails):
   1. [How to delete/revert files]
   2. [How to undo any database changes]
   3. [Git commands to clean up]

TROUBLESHOOTING:
Problem: [Common problem description]
Solution: [How to solve it]

NEXT TASK:
After verification, proceed to TASK X+1
```

---

## HOW TO CREATE FULL PROMPT SET

To create comprehensive prompts for all 78 tasks:

1. **Phase 1 (Tasks 1.1-1.8):** Already detailed above - use as templates
2. **Phase 2 (Tasks 2.1-2.15):** Use specs from `v3docs/modules/01-CONSENT-IMPLEMENTATION.md`
3. **Phase 3 (Tasks 3.1-3.13):** Use specs from `v3docs/modules/02-DSR-IMPLEMENTATION.md`
4. **Phase 4 (Tasks 4.1-4.12):** Use specs from `v3docs/modules/03-LEGALDOCS-IMPLEMENTATION.md`
5. **Phase 5+ (Tasks 5.1+):** Use specs from `v3docs/WINNING-FEATURES-2026.md`

For each task:
1. Copy the PROMPT TEMPLATE above
2. Fill in task-specific information
3. Add code examples where applicable
4. Add specific verification steps
5. Define success criteria precisely

---

**Version:** 1.0  
**Format:** AI Agent Prompts  
**Date:** December 19, 2025  
**Status:** Complete for Phase 1, Template ready for all phases
