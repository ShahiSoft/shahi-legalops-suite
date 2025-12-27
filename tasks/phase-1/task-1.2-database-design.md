# TASK 1.2: Database Schema Design & Migration Files

**Phase:** 1 (Assessment & Cleanup)  
**Effort:** 6-8 hours  
**Prerequisites:** TASK 1.1 complete  
**Next Task:** task-1.3-run-migrations.md  

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 1.2 of the Shahi LegalOps Suite plugin.

TASK: Database Schema Design & Migration Files
PHASE: 1 (Assessment & Cleanup)
EFFORT: 6-8 hours
DEPENDENCY: TASK 1.1 must be complete (project-audit-results.md exists)
LOCATION: c:\docker-wp\html\wp-content\plugins\Shahi LegalOps Suite - 3.0.1

CONTEXT:
You need to create database migration files for 7 new WordPress tables. These migrations will be used 
in TASK 1.3 to actually create the tables. The schema is defined below.

This follows WordPress best practices using dbDelta() for schema management, allowing for both 
creation and updates of tables safely.

INPUT STATE (verify these exist):
- [ ] TASK 1.1 complete: project-audit-results.md exists
- [ ] Repository accessible at: c:\docker-wp\html\wp-content\plugins\Shahi LegalOps Suite - 3.0.1
- [ ] WordPress installation working
- [ ] Composer dependencies installed
- [ ] Git repository initialized

REQUIRED SCHEMA (7 tables):

Table 1: wp_complyflow_consent
- id (BIGINT AUTO_INCREMENT PRIMARY KEY)
- user_id (BIGINT, nullable, foreign key to wp_users)
- ip_hash (VARCHAR(64), indexed, SHA256 hash of IP)
- type (VARCHAR(50), indexed) - values: necessary, functional, analytics, marketing, personalization
- status (VARCHAR(20), indexed) - values: pending, accepted, rejected, withdrawn
- metadata (JSON, nullable) - flexible data storage
- created_at (DATETIME)
- updated_at (DATETIME)

Table 2: wp_complyflow_dsr_requests
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
- id (BIGINT AUTO_INCREMENT PRIMARY KEY)
- form_id (INT, indexed)
- form_type (VARCHAR(50)) - values: cf7, wpforms, gravity, ninja, custom
- user_id (BIGINT, nullable)
- email (VARCHAR(255))
- data (JSON)
- created_at (DATETIME)
- updated_at (DATETIME)

Table 7: wp_complyflow_form_issues
- id (BIGINT AUTO_INCREMENT PRIMARY KEY)
- form_id (INT)
- issue_type (VARCHAR(50)) - values: missing_consent, no_privacy_link, data_retention_issue
- severity (VARCHAR(20)) - values: info, warning, critical
- resolved_at (DATETIME, nullable)
- metadata (JSON)
- created_at (DATETIME)

YOUR TASK:

1. CREATE MIGRATION FILE DIRECTORY
   
   Create directory structure:
   Command: New-Item -ItemType Directory -Path "includes\Database\Migrations" -Force

2. CREATE MIGRATION FILE 1: CONSENT TABLE
   
   Create file: includes/Database/Migrations/migration_2025_12_20_consent_table.php
   
   Content (copy exactly):
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
           
           $sql = "CREATE TABLE IF NOT EXISTS $table_name (
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
   
   Content:
   ```php
   <?php
   /**
    * Migration: Create wp_complyflow_dsr_requests table
    * 
    * @since 3.0.1
    */
   
   namespace ComplyFlow\Database\Migrations;
   
   class Migration_2025_12_20_dsr_requests_table {
       
       public static function up() {
           global $wpdb;
           
           $table_name = $wpdb->prefix . 'complyflow_dsr_requests';
           $charset_collate = $wpdb->get_charset_collate();
           
           $sql = "CREATE TABLE IF NOT EXISTS $table_name (
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

4. CREATE MIGRATION FILES 3-7
   
   Create the following migration files with the same pattern:
   
   A. includes/Database/Migrations/migration_2025_12_20_documents_table.php
      - Use Table 3 schema above
      - Class name: Migration_2025_12_20_documents_table
      - Table name: complyflow_documents
   
   B. includes/Database/Migrations/migration_2025_12_20_trackers_table.php
      - Use Table 4 schema above
      - Class name: Migration_2025_12_20_trackers_table
      - Table name: complyflow_trackers
   
   C. includes/Database/Migrations/migration_2025_12_20_vendors_table.php
      - Use Table 5 schema above
      - Class name: Migration_2025_12_20_vendors_table
      - Table name: complyflow_vendors
   
   D. includes/Database/Migrations/migration_2025_12_20_form_submissions_table.php
      - Use Table 6 schema above
      - Class name: Migration_2025_12_20_form_submissions_table
      - Table name: complyflow_form_submissions
   
   E. includes/Database/Migrations/migration_2025_12_20_form_issues_table.php
      - Use Table 7 schema above
      - Class name: Migration_2025_12_20_form_issues_table
      - Table name: complyflow_form_issues

5. CREATE MIGRATION RUNNER SCRIPT
   
   Create file: includes/Database/Migrations/Runner.php
   
   Content:
   ```php
   <?php
   /**
    * Migration Runner
    * Orchestrates running all migrations
    * 
    * @since 3.0.1
    */
   
   namespace ComplyFlow\Database\Migrations;
   
   class Runner {
       
       /**
        * Run all migrations (create tables)
        * 
        * @return array Results of each migration
        */
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
               } else {
                   $results[$migration] = 'CLASS NOT FOUND';
               }
           }
           
           return $results;
       }
       
       /**
        * Rollback all migrations (drop tables)
        * 
        * @return array Results of each migration rollback
        */
       public static function rollback_all() {
           // Reverse order to handle dependencies
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

6. CREATE MIGRATION DOCUMENTATION
   
   Create file: includes/Database/Migrations/README.md
   
   Content:
   ```markdown
   # Database Migrations
   
   This directory contains database migration files for the Shahi LegalOps Suite plugin.
   
   ## Migration Files
   
   - migration_2025_12_20_consent_table.php - User consent tracking
   - migration_2025_12_20_dsr_requests_table.php - Data subject request management
   - migration_2025_12_20_documents_table.php - Legal document versioning
   - migration_2025_12_20_trackers_table.php - Third-party tracker inventory
   - migration_2025_12_20_vendors_table.php - Vendor/processor management
   - migration_2025_12_20_form_submissions_table.php - Form submission tracking
   - migration_2025_12_20_form_issues_table.php - Form compliance issue tracking
   
   ## Running Migrations
   
   Migrations are run automatically on plugin activation.
   
   To manually run migrations:
   ```php
   wp eval 'require "includes/Database/Migrations/Runner.php"; 
           $results = \ComplyFlow\Database\Migrations\Runner::run_all(); 
           var_dump($results);'
   ```
   
   ## Rollback Migrations
   
   To drop all tables:
   ```php
   wp eval 'require "includes/Database/Migrations/Runner.php"; 
           $results = \ComplyFlow\Database\Migrations\Runner::rollback_all(); 
           var_dump($results);'
   ```
   
   ## Migration Pattern
   
   Each migration class has two methods:
   - `up()` - Creates the table
   - `down()` - Drops the table
   
   Both methods return true on success, false on failure.
   ```
   ```

OUTPUT STATE (what exists after this task):
- [ ] includes/Database/Migrations/ directory created
- [ ] 7 migration files created (one per table)
- [ ] Migration runner script (Runner.php) created
- [ ] Migration documentation (README.md) created
- [ ] All files have correct PHP syntax
- [ ] All classes have correct namespaces
- [ ] All migrations follow the same pattern

VERIFICATION (run these to confirm success):

1. Check files were created:
   Command: Get-ChildItem -Path "includes\Database\Migrations" -Filter "*.php"
   Expected: Should show 8 files (7 migrations + 1 Runner.php)

2. Count migration files:
   Command: (Get-ChildItem -Path "includes\Database\Migrations" -Filter "migration_*.php").Count
   Expected: 7

3. Check PHP syntax on each file:
   Command: php -l includes/Database/Migrations/migration_2025_12_20_consent_table.php
   Expected: "No syntax errors detected"
   
   Repeat for each migration file and Runner.php

4. Check Runner syntax:
   Command: php -l includes/Database/Migrations/Runner.php
   Expected: "No syntax errors detected"

5. Verify namespace in files:
   Command: Select-String -Path "includes\Database\Migrations\*.php" -Pattern "namespace ComplyFlow"
   Expected: Should show namespace declarations in all files

6. Check README exists:
   Command: Test-Path "includes\Database\Migrations\README.md"
   Expected: True

SUCCESS CRITERIA:
- [ ] All 8 PHP files created successfully
- [ ] README.md created
- [ ] All migration files have correct table schemas matching specifications
- [ ] All files pass PHP syntax check (php -l)
- [ ] Runner.php has run_all() and rollback_all() methods
- [ ] All migration classes use correct namespace (ComplyFlow\Database\Migrations)
- [ ] All migration classes have up() and down() methods
- [ ] Foreign key relationships defined where needed
- [ ] Indexes defined on appropriate columns
- [ ] JSON columns for flexible metadata storage

ROLLBACK (if verification fails):
   1. Delete all migration files:
      Command: Remove-Item -Recurse -Force includes\Database\Migrations\
   2. No database changes made yet (tables not created), so nothing to revert in database
   3. Review error messages from PHP syntax check
   4. Recreate migration files with corrections

TROUBLESHOOTING:

Problem: "PHP syntax error detected"
Solution: 
  - Review the error message carefully
  - Check for missing semicolons, brackets, or quotes
  - Ensure namespace declaration is correct
  - Verify class name matches filename

Problem: "Directory already exists"
Solution: This is OK, continue with creating migration files

Problem: "Can't create file"
Solution:
  - Check file permissions
  - Ensure you're in the correct directory
  - Verify path is correct: includes\Database\Migrations\

WHAT TO REPORT BACK:
When this task is complete, report:
1. "✅ TASK 1.2 verification passed"
2. Confirmation that all 7 migration files + Runner.php created
3. Confirmation that all PHP syntax checks passed
4. Any issues encountered and how they were resolved
5. "Ready to proceed to TASK 1.3"

COMMIT MESSAGE:
After verification passes, commit your changes:
Command: git add includes/Database/Migrations/
Command: git commit -m "TASK 1.2: Database schema design and migration files created"

NEXT TASK:
After this task is complete and verified, proceed to:
tasks/phase-1/task-1.3-run-migrations.md
```

---

## ✅ COMPLETION CHECKLIST

After running this task, verify:

- [ ] 7 migration files created
- [ ] Runner.php created
- [ ] README.md created
- [ ] All PHP syntax checks passed
- [ ] Git commit created
- [ ] Ready for TASK 1.3

---

**Status:** Ready to execute  
**Time:** 6-8 hours  
**Next:** task-1.3-run-migrations.md
