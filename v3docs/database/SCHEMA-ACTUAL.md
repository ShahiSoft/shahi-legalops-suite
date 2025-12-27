# Shahi LegalOps Suite - Database Schema (Actual vs. Planned)

**Version:** 3.0.1  
**Last Updated:** December 19, 2025  
**Status:** Updated to reflect actual implementation

---

## 📊 SUMMARY

| Status | Table Name | Purpose | Module | Created |
|--------|-----------|---------|--------|---------|
| ✅ CREATED | `wp_shahi_analytics` | Event tracking | Core | Week 1 |
| ✅ CREATED | `wp_shahi_modules` | Module state | Core | Week 1 |
| ✅ CREATED | `wp_shahi_onboarding` | Onboarding progress | Core | Week 1 |
| ✅ CREATED | `wp_slos_a11y_scans` | A11y scan results | A11y Scanner | Week 1 |
| ✅ CREATED | `wp_slos_a11y_issues` | A11y violations | A11y Scanner | Week 1 |
| 🔄 PLANNED | `wp_complyflow_consent` | Consent records | Consent (Phase 2) | Week 6 |
| 🔄 PLANNED | `wp_complyflow_consent_logs` | Consent audit trail | Consent (Phase 2) | Week 6 |
| 🔄 PLANNED | `wp_complyflow_dsr_requests` | DSR lifecycle | DSR (Phase 3) | Week 9 |
| 🔄 PLANNED | `wp_complyflow_dsr_request_data_sources` | DSR data mapping | DSR (Phase 3) | Week 9 |
| 🔄 PLANNED | `wp_complyflow_documents` | Legal documents | Legal Docs (Phase 4) | Week 11 |
| 🔄 PLANNED | `wp_complyflow_document_versions` | Document versions | Legal Docs (Phase 4) | Week 11 |
| 🔄 PLANNED | `wp_complyflow_trackers` | Cookie inventory | Cookie (Phase 6) | Week 15 |
| 🔄 PLANNED | `wp_complyflow_vendors` | Vendor data | Vendor (Phase 6) | Week 16 |
| 🔄 PLANNED | `wp_complyflow_form_submissions` | Form data | Forms (Phase 6) | Week 14 |
| 🔄 PLANNED | `wp_complyflow_form_issues` | Form compliance issues | Forms (Phase 6) | Week 14 |

---

## ✅ CREATED TABLES (5 total)

### 1. Analytics Event Tracking: `wp_shahi_analytics`

**Purpose:** Track all plugin events and user interactions for analytics dashboard

**Table Definition:**
```sql
CREATE TABLE IF NOT EXISTS wp_shahi_analytics (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    event_type VARCHAR(50) NOT NULL,
    event_data LONGTEXT,
    user_id BIGINT(20) UNSIGNED DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY event_type (event_type),
    KEY user_id (user_id),
    KEY created_at (created_at)
);
```

**Columns:**
- `id` - Auto-incrementing primary key
- `event_type` - Type of event (e.g., 'module_toggle', 'scan_completed', 'settings_updated', 'consent_saved')
- `event_data` - JSON or serialized event details
- `user_id` - WordPress user ID (NULL for anonymous events)
- `ip_address` - Visitor IP (for analytics)
- `user_agent` - Browser/device info
- `created_at` - Timestamp of event

**Example Data:**
```json
{
    "id": 1,
    "event_type": "module_toggle",
    "event_data": "{\"module\": \"accessibility-scanner\", \"action\": \"enabled\"}",
    "user_id": 1,
    "ip_address": "192.168.1.100",
    "user_agent": "Mozilla/5.0...",
    "created_at": "2025-12-19 10:30:00"
}
```

**Expected Volume:** Medium (100s per day on active site)  
**Retention:** Configurable (default 90 days)

---

### 2. Module State Management: `wp_shahi_modules`

**Purpose:** Store enable/disable state and settings for each module

**Table Definition:**
```sql
CREATE TABLE IF NOT EXISTS wp_shahi_modules (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    module_key VARCHAR(100) NOT NULL,
    is_enabled TINYINT(1) NOT NULL DEFAULT 1,
    settings LONGTEXT,
    last_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY module_key (module_key)
);
```

**Columns:**
- `id` - Auto-incrementing primary key
- `module_key` - Unique module identifier (e.g., 'compliance-dashboard', 'accessibility-scanner', 'consent-manager')
- `is_enabled` - 1 = enabled, 0 = disabled
- `settings` - JSON-encoded module-specific configuration
- `last_updated` - When module state/settings last changed

**Reserved Module Keys:**
```
- compliance-dashboard    (Dashboard module)
- accessibility-scanner   (A11y Scanner module)
- security-module         (Security hardening)
- consent-manager         (Consent Management - Phase 2)
- dsr-portal             (DSR Portal - Phase 3)
- legal-docs-generator   (Legal Docs - Phase 4)
- analytics-reporting    (Analytics - Phase 5)
- forms-compliance       (Forms - Phase 6)
- cookie-inventory       (Cookies - Phase 6)
- vendor-management      (Vendors - Phase 6)
```

**Example Data:**
```json
{
    "id": 1,
    "module_key": "accessibility-scanner",
    "is_enabled": 1,
    "settings": "{\"scan_frequency\": \"weekly\", \"wcag_level\": \"AA\", \"notify_admin\": true}",
    "last_updated": "2025-12-19 10:30:00"
}
```

---

### 3. Onboarding Progress: `wp_shahi_onboarding`

**Purpose:** Track user progress through multi-step setup wizard

**Table Definition:**
```sql
CREATE TABLE IF NOT EXISTS wp_shahi_onboarding (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    step_completed VARCHAR(100) NOT NULL,
    data_collected LONGTEXT,
    completed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY step_completed (step_completed)
);
```

**Columns:**
- `id` - Auto-incrementing primary key
- `user_id` - WordPress user ID
- `step_completed` - Step identifier (e.g., 'welcome', 'purpose', 'features', 'configuration')
- `data_collected` - JSON with step-specific data
- `completed_at` - When step was completed

**Onboarding Steps:**
```
1. 'welcome' - Initial introduction
2. 'purpose' - User's purpose/use case
3. 'features' - Feature selection
4. 'configuration' - Initial settings
5. 'completion' - Final summary
```

**Example Data:**
```json
{
    "id": 1,
    "user_id": 1,
    "step_completed": "purpose",
    "data_collected": "{\"purpose\": \"compliance_management\", \"experience\": \"intermediate\"}",
    "completed_at": "2025-12-19 10:30:00"
}
```

**Expected Volume:** Low (one entry per user per step, typically 3-5 steps)

---

### 4. Accessibility Scans: `wp_slos_a11y_scans`

**Purpose:** Store summary results of accessibility scans per page/post

**Table Definition:**
```sql
CREATE TABLE IF NOT EXISTS wp_slos_a11y_scans (
    post_id BIGINT(20) UNSIGNED NOT NULL,
    page_title VARCHAR(255) NOT NULL,
    url TEXT NULL,
    score INT(11) NOT NULL DEFAULT 100,
    issues_count INT(11) NOT NULL DEFAULT 0,
    critical_count INT(11) NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'passed',
    last_scan DATETIME NULL,
    autofix_enabled TINYINT(1) NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (post_id),
    KEY status (status),
    KEY score (score)
);
```

**Columns:**
- `post_id` - WordPress post ID (PRIMARY KEY)
- `page_title` - Title of page/post
- `url` - Full URL scanned
- `score` - Accessibility score (0-100)
- `issues_count` - Total number of issues found
- `critical_count` - Critical/serious issues
- `status` - 'passed', 'warning', 'failed'
- `last_scan` - Timestamp of last scan
- `autofix_enabled` - Whether auto-fixes are enabled
- `updated_at` - When record last updated

**Status Determination:**
- score >= 90 AND critical_count == 0 → 'passed'
- score >= 70 OR critical_count == 0 → 'warning'
- score < 70 AND critical_count > 0 → 'failed'

---

### 5. Accessibility Issues: `wp_slos_a11y_issues`

**Purpose:** Store detailed violations for each accessibility scan

**Table Definition:**
```sql
CREATE TABLE IF NOT EXISTS wp_slos_a11y_issues (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    post_id BIGINT(20) UNSIGNED NOT NULL,
    checker_id VARCHAR(100) NOT NULL,
    severity VARCHAR(20) NOT NULL DEFAULT 'warning',
    message TEXT NULL,
    element TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY post_id (post_id),
    KEY checker_id (checker_id),
    KEY severity (severity)
);
```

**Columns:**
- `id` - Auto-incrementing primary key
- `post_id` - Which page/post (foreign key to `wp_slos_a11y_scans`)
- `checker_id` - Which checker found this (e.g., 'missing_alt_text', 'contrast_ratio')
- `severity` - 'critical', 'serious', 'moderate', 'minor'
- `message` - Human-readable description
- `element` - HTML selector or element HTML
- `created_at` - When issue was detected

**Severity Mapping to WCAG:**
- Critical → WCAG Level A violation
- Serious → WCAG Level AA violation
- Moderate → Best practice
- Minor → Minor issue

**Example Data:**
```json
{
    "id": 1,
    "post_id": 123,
    "checker_id": "missing_alt_text",
    "severity": "critical",
    "message": "Image missing alt text",
    "element": "<img src='photo.jpg'>",
    "created_at": "2025-12-19 10:30:00"
}
```

---

## 🔄 PLANNED TABLES (8 additional, created during Phase 2-6)

### Phase 2: Consent Management (Weeks 6)

#### `wp_complyflow_consent`
- Stores consent records for each user/session
- Fields: id, user_id, session_id, region, categories (JSON), created_at, expires_at, withdrawn_at, ip_hash, user_agent_hash
- Indexes: user_id, session_id, created_at, expires_at

#### `wp_complyflow_consent_logs`
- Audit trail for all consent changes
- Fields: id, consent_id, action, user_id, ip_hash, changed_data (JSON), created_at
- Indexes: consent_id, action

---

### Phase 3: DSR Portal (Week 9)

#### `wp_complyflow_dsr_requests`
- Stores DSR request lifecycle
- Fields: id, request_type, status, requester_email, user_id, verification_token, verified_at, sla_deadline, completed_at, export_file_path, processed_by, admin_notes, reason
- Indexes: email, user_id, status, request_type, submitted_at, sla_deadline

#### `wp_complyflow_dsr_request_data_sources`
- Maps which data sources were searched for each request
- Fields: id, dsr_request_id, source_type, data_count, export_status, error_message, last_checked
- Indexes: dsr_request_id, source_type

---

### Phase 4: Legal Documents (Week 11)

#### `wp_complyflow_documents`
- Stores legal documents (privacy policy, terms, cookies)
- Fields: id, document_type, document_version, title, content, status, questionnaire_data (JSON), published_at, published_by, published_page_id, created_at, updated_at, gdpr_compliant, ccpa_compliant
- Indexes: document_type, status, published_at, published_by

#### `wp_complyflow_document_versions`
- Version history with rollback capability
- Fields: id, document_id, version_number, content, change_summary, change_reason, created_at, created_by, character_count, word_count
- Indexes: document_id, created_at

---

### Phase 5: Analytics (Week 13)

(Uses existing `wp_shahi_analytics` table, no new tables)

---

### Phase 6: Forms & Optional (Weeks 14-16)

#### `wp_complyflow_form_submissions`
- Stores form submission data
- Fields: id, form_type, form_id, user_id, email, submission_data (JSON), consent_id, created_at, scheduled_delete_date, deleted_at
- Indexes: form_id, email, created_at

#### `wp_complyflow_form_issues`
- Compliance issues found in forms
- Fields: id, form_id, issue_type, severity, description, resolved, detected_at, resolved_at
- Indexes: form_id, issue_type, severity

#### `wp_complyflow_trackers`
- Cookie/tracker inventory
- Fields: id, cookie_name, provider, category, expiration_type, expiration_days, first_party, description, is_blocked_until_consent, detected_at, last_seen, status, created_at, updated_at
- Indexes: cookie_name, provider, category

#### `wp_complyflow_vendors`
- Vendor/third-party data processor info
- Fields: id, vendor_name, category, website, contact_email, contact_person, data_processor, risk_score, risk_level, jurisdiction, gdpr_adequacy, data_transfer_mechanism, certifications (JSON), notes, is_active, created_at
- Indexes: vendor_name, category, risk_level, is_active

---

## 🔐 SECURITY CONSIDERATIONS

### Data Sensitivity Levels

**PUBLIC DATA (no encryption needed):**
- Event types
- Module names
- Page titles
- Scan scores
- Consent status (high-level)

**PROTECTED DATA (should be encrypted/hashed):**
- IP addresses → Use hashing (SHA-256) instead of storing raw
- User agents → Use hashing for privacy
- Email addresses → Okay to store plaintext (necessary for function)

**SENSITIVE DATA (encryption required):**
- Consent choices (in future: AES-256)
- Personal data exports (in future: AES-256 + secure downloads)
- Form submissions (must implement AES-256)
- Payment information (PCI-DSS compliant)

### Encryption Roadmap

**Phase 1 (Current):** No encryption (baseline security with access controls)

**Phase 2-3:** Add optional encryption for form submissions and DSR exports

**Phase 4+:** Implement field-level encryption for sensitive data

---

## 📈 EXPECTED DATA VOLUMES

| Table | Rows/Day | Rows/Year | Size @ 1year |
|-------|----------|-----------|-------------|
| `wp_shahi_analytics` | 200-500 | 73K-182K | 15-30 MB |
| `wp_shahi_modules` | 0 | 10 | < 1 KB |
| `wp_shahi_onboarding` | 0 | 50 | < 10 KB |
| `wp_slos_a11y_scans` | 5-20 | 2K-7K | 1-2 MB |
| `wp_slos_a11y_issues` | 50-100 | 18K-36K | 3-5 MB |
| `wp_complyflow_consent` | 100-1000 | 36K-365K | 5-50 MB |
| `wp_complyflow_dsr_requests` | 1-5 | 365-1825 | < 1 MB |
| `wp_complyflow_documents` | 0 | 10-20 | < 1 MB |

---

## 🗄️ WORDPRESS OPTIONS (Settings Storage)

In addition to custom tables, the plugin stores settings in WordPress options:

```php
// Main settings
'shahi_legalops_suite_settings' => [
    'plugin_enabled' => true,
    'admin_email' => 'admin@example.com',
    'date_format' => 'Y-m-d',
    'time_format' => 'H:i:s'
]

// Advanced settings
'shahi_legalops_suite_advanced_settings' => [
    'debug_mode' => false,
    'custom_css' => '',
    'developer_mode' => false,
    'api_rate_limit' => 100
]

// Module-specific settings (also stored per-module in wp_shahi_modules.settings JSON)
'shahi_legalops_suite_accessibility_settings' => [...]
'shahi_legalops_suite_consent_settings' => [...]
'shahi_legalops_suite_dsr_settings' => [...]
```

---

## 🔄 DATABASE MAINTENANCE

### Recommended Tasks

**Weekly:**
```sql
-- Analyze table statistics
ANALYZE TABLE wp_shahi_analytics;
ANALYZE TABLE wp_slos_a11y_issues;

-- Monitor size
SELECT 
    table_name, 
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS "Size in MB"
FROM information_schema.TABLES
WHERE table_schema = 'wordpress'
AND table_name LIKE '%shahi%' OR table_name LIKE '%complyflow%' OR table_name LIKE '%slos%';
```

**Monthly:**
```sql
-- Optimize tables
OPTIMIZE TABLE wp_shahi_analytics;
OPTIMIZE TABLE wp_slos_a11y_issues;
OPTIMIZE TABLE wp_complyflow_consent;

-- Clean old analytics (if retention set to 90 days)
DELETE FROM wp_shahi_analytics 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

**Quarterly:**
```sql
-- Check table integrity
CHECK TABLE wp_shahi_analytics;
CHECK TABLE wp_slos_a11y_issues;

-- Repair if needed (shouldn't be needed, but good to check)
REPAIR TABLE wp_shahi_analytics;
```

---

## 🎯 MIGRATION STRATEGY

### Phase-by-Phase Creation

**Week 2:** Create migration file for Phase 2-6 tables (all at once)
```php
// includes/Database/Migrations/migration_2_0_0_full_schema.php
```

**Week 6:** Run migration for Consent tables
**Week 9:** Run migration for DSR tables
**Week 11:** Run migration for Legal Docs tables
**Week 14:** Run migration for Forms tables
**Week 15:** Run migration for Cookie Inventory
**Week 16:** Run migration for Vendor tables

### Migration File Structure

```php
<?php
namespace ShahiLegalopsSuite\Database\Migrations;

class Migration_2_0_0_Consent {
    public function up() {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Create tables
        dbDelta($sql_consent);
        dbDelta($sql_consent_logs);
        
        // Log migration
        update_option('shahi_legalops_suite_db_version', '2.0.0');
    }
    
    public function down() {
        global $wpdb;
        // Drop tables if rolling back
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}complyflow_consent");
    }
}
```

---

## 📊 INDEXES STRATEGY

### Current Indexes (Optimized)

Each table has:
1. **PRIMARY KEY** on id (fast lookups)
2. **Foreign Keys** where appropriate (referential integrity)
3. **Search Indexes** on commonly filtered columns (user_id, created_at, status)
4. **Composite Indexes** for multi-column searches (event_type + created_at)

### Index Maintenance

```sql
-- View existing indexes
SHOW INDEX FROM wp_shahi_analytics;

-- Find unused indexes
SELECT * FROM sys.schema_unused_indexes 
WHERE object_schema = 'wordpress';

-- Check for missing indexes on frequently searched columns
-- If a column is in WHERE clause and not indexed, add it
ALTER TABLE wp_complyflow_consent ADD INDEX idx_region (region);
```

---

## 🚨 COMMON ISSUES & SOLUTIONS

### Issue: Slow Queries on Analytics Table

**Problem:** As data grows, analytics queries slow down

**Solution:**
```sql
-- Add composite indexes for common queries
ALTER TABLE wp_shahi_analytics ADD INDEX idx_user_created (user_id, created_at);
ALTER TABLE wp_shahi_analytics ADD INDEX idx_event_time (event_type, created_at);

-- Archive old data (older than 90 days)
-- Move to archive table or delete based on retention policy
```

### Issue: DSR Export Takes Too Long

**Problem:** Exporting data from large sites is slow

**Solution:**
```php
// Implement batch processing
// Process 100 records at a time, queue remaining
// Use WP_Background_Process or Cron Job
```

### Issue: Consent Table Growing Too Large

**Problem:** After 1 year, millions of consent records

**Solution:**
```sql
-- Archive old records
INSERT INTO wp_complyflow_consent_archive 
SELECT * FROM wp_complyflow_consent 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 12 MONTH);

DELETE FROM wp_complyflow_consent 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 12 MONTH);

-- Keep only recent consent (last 12 months)
```

---

## 📚 RELATED DOCUMENTATION

- Main Feature List: `/DevDocs/SLOS/CF_FEATURES_v3.md`
- Implementation Roadmap: `/v3docs/ROADMAP.md`
- Individual Module Guides: `/v3docs/modules/`

---

**Version:** 1.0  
**Last Updated:** December 19, 2025  
**Status:** Actual Implementation Documented
