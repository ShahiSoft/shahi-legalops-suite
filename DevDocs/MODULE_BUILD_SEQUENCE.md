# Shahi LegalOps Suite - Module Build Sequence & Planning

**Document Purpose:** Strategic roadmap for building remaining modules with clear dependencies, scope, and implementation requirements.

**Current Status:** 3 of 10 modules implemented (Consent, Accessibility, Security)  
**Last Updated:** December 18, 2025

---

## 📊 Module Build Roadmap

### Current Implementation
```
✅ COMPLETE: Consent Management (consent-management)
✅ COMPLETE: Accessibility Scanner (accessibility-scanner)
✅ COMPLETE: Security Module (security)

❌ INCOMPLETE: Cookie Inventory
❌ INCOMPLETE: Forms Compliance
❌ INCOMPLETE: Vendor Management
❌ INCOMPLETE: Analytics & Reporting
❌ INCOMPLETE: Legal Documents Generator
❌ INCOMPLETE: Data Subject Rights (DSR) Portal
❌ INCOMPLETE: Dashboard Module
❌ INCOMPLETE: Developer Tools
```

---

## 🏗️ BUILD SEQUENCE

### PHASE 1: Data Collection Foundation (Independent)

---

## 1️⃣ COOKIE INVENTORY MODULE
**Key:** `cookie-inventory`  
**Status:** Not Started  
**Priority:** HIGH - Foundation for Legal Docs, Vendor Mgmt, Dashboard  
**Depends On:** None (independent)

### Purpose
Automatic passive monitoring and inventory of all cookies used on the website, with third-party provider recognition and consent linkage.

### Core Features
- **Automatic Detection**
  - Passive JavaScript monitoring of document.cookie writes
  - Real-time capture of cookie name, value, domain, path, expiration
  - No blocking or modification of legitimate cookie operations
  
- **Provider Recognition**
  - 50+ known provider patterns (Google Analytics, Facebook, TikTok, LinkedIn, YouTube, Stripe, PayPal, etc.)
  - Provider categorization and description
  - Risk level assignment per provider
  
- **Categorization**
  - Necessary (session, security, preferences)
  - Functional (language, site preferences)
  - Analytics (tracking, performance)
  - Marketing (advertising, retargeting)
  
- **Manual Management**
  - Add cookies not captured automatically
  - Edit/delete cookies
  - Bulk category updates
  - Tag system for organization
  
- **Consent Linkage**
  - Map cookies to consent categories
  - Block marketing/analytics cookies until consent given
  - Auto-link based on provider database
  
- **Reporting**
  - CSV export of cookie inventory
  - Provider-level summary
  - Risk assessment report
  - Consent compliance view

### Database Tables
```
wp_shahi_cookies (new)
- id (int, primary key)
- cookie_name (varchar, 255)
- provider (varchar, 100)
- category (enum: necessary|functional|analytics|marketing)
- expiration_type (session|days|persistent)
- expiration_days (int, nullable)
- first_party (bool)
- description (text)
- is_blocked_until_consent (bool)
- detected_at (datetime)
- last_seen (datetime)
- status (active|removed)
- created_at (datetime)
- updated_at (datetime)

wp_shahi_cookie_providers (lookup table)
- id (int)
- name (varchar, 100) - unique
- description (text)
- website (varchar, 255)
- category (enum)
- risk_level (low|medium|high)
- pattern (text) - regex for detection
```

### Admin Interface Components
- **Cookie Inventory Table**
  - Searchable/filterable list
  - Provider recognition with icons
  - Last detected timestamp
  - Bulk actions (categorize, block, delete)
  - Inline editing
  
- **Detection Dashboard**
  - Real-time detection status
  - Last scan timestamp
  - Provider distribution chart
  - Category breakdown
  - Risk summary
  
- **Settings**
  - Enable/disable automatic detection
  - Detection sensitivity
  - Auto-categorization rules
  - Consent blocking behavior
  - Exclusion patterns

### Frontend Integration
- **Detection Script**
  - Non-blocking JavaScript
  - Monitors cookie creation via intercept
  - Sends data to AJAX endpoint
  - Respects user consent preferences
  
- **Consent Integration**
  - Prevent marketing/analytics cookies before consent
  - Allow necessary cookies always
  - Respect functional cookies preference
  - Log blocked attempts

### REST API Endpoints
```
GET    /wp-json/shahi/v1/cookies
POST   /wp-json/shahi/v1/cookies
GET    /wp-json/shahi/v1/cookies/{id}
PUT    /wp-json/shahi/v1/cookies/{id}
DELETE /wp-json/shahi/v1/cookies/{id}

GET    /wp-json/shahi/v1/providers
GET    /wp-json/shahi/v1/cookie-stats
POST   /wp-json/shahi/v1/cookies/bulk-update
POST   /wp-json/shahi/v1/cookies/export
```

### WP-CLI Commands
```
wp shahi cookie list
wp shahi cookie add <name> <category>
wp shahi cookie detect --start
wp shahi cookie export --format=csv
wp shahi cookie stats
```

### Settings Keys
```
shahi_cookie_detection_enabled (bool)
shahi_cookie_auto_block (bool)
shahi_cookie_sensitivity (enum: strict|moderate|lenient)
shahi_cookie_exclusions (array)
shahi_cookie_provider_db (array - auto-updated)
```

### Integration Points
- Hooks into Consent Management (cookie blocking rules)
- Feeds data to Legal Documents Generator (auto-detect cookies)
- Provides inventory to Dashboard
- Used by Analytics module

### Estimated Files
```
CookieInventory.php (main module class)
CookieRepository.php
CookieProvider.php
CookieDetectionEngine.php
CookieBlockingManager.php
DetectionScript.js
AdminController.php
tables/
  cookie-inventory.php
  detection-status.php
  detection-dashboard.php
```

---

## 2️⃣ FORMS COMPLIANCE MODULE
**Key:** `forms-compliance`  
**Status:** Not Started  
**Priority:** HIGH - Independent, complements Consent  
**Depends On:** Consent Management (loosely)

### Purpose
Monitor and ensure all form submissions comply with privacy regulations, manage data retention, and detect missing consent checkboxes.

### Core Features
- **Supported Form Plugins**
  - Contact Form 7 (built-in support)
  - WPForms
  - Gravity Forms
  - Ninja Forms
  
- **Form Scanner**
  - Detect all forms on website
  - Identify form fields and types
  - Check for privacy/consent issues
  - Detect sensitive data collection (email, phone, etc.)
  
- **Compliance Checks**
  - Missing consent checkbox detection
  - Privacy policy link presence
  - Field validation rules
  - Accessible label associations
  - Required field handling
  
- **Consent Tracking**
  - Link form submissions to consent record
  - Verify consent given at submission time
  - Store consent proof with submission
  
- **Data Retention Management**
  - Per-form retention periods
  - Automated scheduled deletion
  - Bulk deletion utilities
  - Retention compliance reporting
  
- **Data Encryption**
  - Optional AES-256 encryption for sensitive fields
  - Per-field encryption toggles
  - Encryption key management
  
- **Submission Logging**
  - Track all form submissions
  - Log consent status at time of submission
  - User information (IP, timestamp)
  - Submission editing/viewing access control

### Database Tables
```
wp_shahi_forms (new)
- id (int)
- form_type (cf7|wpforms|gravity|ninja)
- form_id (int) - external form ID
- form_title (varchar, 255)
- fields (json) - array of field configs
- has_consent_checkbox (bool)
- retention_days (int)
- encryption_enabled (bool)
- encrypted_fields (json) - field names to encrypt
- last_scanned (datetime)
- status (active|inactive)
- created_at (datetime)
- updated_at (datetime)

wp_shahi_form_submissions (new)
- id (int, primary key)
- form_id (int)
- form_type (varchar, 50)
- submission_data (longtext) - JSON
- encrypted_data (longblob, nullable)
- user_id (int, nullable)
- user_email (varchar, 255)
- user_ip (varchar, 45)
- consent_id (int, nullable) - FK to consent table
- consent_given (bool)
- submission_timestamp (datetime)
- processed (bool)
- scheduled_delete_date (datetime)
- deleted_at (datetime, nullable)
- created_at (datetime)

wp_shahi_form_issues (new)
- id (int)
- form_id (int)
- issue_type (missing_consent|missing_privacy_link|accessibility|validation)
- severity (critical|warning|info)
- description (text)
- resolved (bool)
- detected_at (datetime)
- resolved_at (datetime, nullable)
```

### Admin Interface Components
- **Forms Dashboard**
  - List all detected forms
  - Compliance status per form
  - Submission count
  - Issues identified
  - Last scan date
  
- **Form Editor**
  - Form-specific settings
  - Consent checkbox requirement toggle
  - Retention period setting
  - Encryption field selection
  - Custom validation rules
  
- **Submissions Manager**
  - View all submissions
  - Search/filter by form, date, user
  - Export submissions (with encryption consideration)
  - Bulk delete options
  - Consent status verification
  
- **Issues Tracker**
  - List compliance issues found
  - Severity indicators
  - Resolution guidance
  - Mark as resolved

### Frontend Integration
- **Form Interceptor**
  - Hook into form submission handlers
  - Verify consent before submission
  - Capture form data and consent status
  - Trigger encryption if needed
  - Log submission
  
- **Consent Requirement**
  - Dynamically inject consent checkbox
  - Block submission if unchecked
  - Link to privacy policy

### REST API Endpoints
```
GET    /wp-json/shahi/v1/forms
GET    /wp-json/shahi/v1/forms/{id}
PUT    /wp-json/shahi/v1/forms/{id}
GET    /wp-json/shahi/v1/form-submissions
GET    /wp-json/shahi/v1/form-submissions/{id}
DELETE /wp-json/shahi/v1/form-submissions/{id}
POST   /wp-json/shahi/v1/forms/scan
POST   /wp-json/shahi/v1/form-submissions/export
GET    /wp-json/shahi/v1/form-issues
```

### WP-CLI Commands
```
wp shahi forms scan
wp shahi forms list
wp shahi submissions list --form=<id>
wp shahi submissions export --form=<id> --format=csv
wp shahi submissions delete --days=<num> --dry-run
```

### Settings Keys
```
shahi_form_auto_scan_enabled (bool)
shahi_form_require_consent (bool)
shahi_form_default_retention_days (int)
shahi_form_encryption_enabled (bool)
shahi_form_supported_plugins (array)
shahi_form_privacy_policy_link (varchar)
```

### Integration Points
- Hooks into Consent Management (consent verification)
- Feeds submission data to Analytics/Reporting
- Provides data to DSR Portal (user data export)
- Used by Dashboard for compliance metrics

### Estimated Files
```
FormsCompliance.php
FormScanner.php
FormRepository.php
SubmissionHandler.php
EncryptionManager.php
DataRetentionManager.php
AdminController.php
Integrations/
  ContactForm7Integration.php
  WPFormsIntegration.php
  GravityFormsIntegration.php
  NinjaFormsIntegration.php
tables/
  forms-list.php
  submissions-table.php
  issues-tracker.php
```

---

### PHASE 2: Secondary Data Collectors & Management

---

## 3️⃣ VENDOR MANAGEMENT MODULE
**Key:** `vendor-management`  
**Status:** Not Started  
**Priority:** HIGH - Management layer  
**Depends On:** Cookie Inventory (loosely - for vendor identification)

### Purpose
Maintain comprehensive inventory of data processors and vendors, track Data Processing Agreements (DPAs), assess risk, and monitor compliance.

### Core Features
- **Vendor Inventory**
  - Auto-detection from Cookie Inventory and Forms
  - Manual vendor entry
  - Vendor categorization (payment, analytics, email, hosting, etc.)
  - Contact information storage
  
- **Data Processing Agreements (DPA)**
  - DPA document upload and versioning
  - Expiration tracking
  - Renewal alerts
  - Signature status tracking
  - Document versioning with rollback
  
- **Risk Assessment**
  - Risk scoring algorithm (1-100)
  - Assessment categories:
    - Data sensitivity
    - Geographic location/jurisdiction
    - Security practices
    - Breach history
    - Compliance certifications
  - Risk level indicators (low/medium/high/critical)
  
- **Compliance Monitoring**
  - Jurisdiction tracking
  - GDPR adequacy assessment
  - Data transfer mechanism (SCCs, BCRs, etc.)
  - Security certification tracking
  - Renewal/audit schedules
  
- **Notifications & Alerts**
  - DPA expiration alerts
  - Risk level changes
  - Compliance deadline reminders
  - Audit schedule notifications

### Database Tables
```
wp_shahi_vendors (new)
- id (int, primary key)
- vendor_name (varchar, 255) - unique
- category (enum: payment|analytics|email|hosting|cdn|crm|other)
- website (varchar, 255)
- contact_email (varchar, 255)
- contact_person (varchar, 255)
- phone (varchar, 20)
- data_processor (bool) - is data processor or controller
- risk_score (int, 0-100)
- risk_level (enum: low|medium|high|critical)
- jurisdiction (varchar, 50)
- gdpr_adequacy (bool)
- data_transfer_mechanism (varchar, 50) - SCCs|BCRs|adequacy_decision
- certifications (json) - ISO27001, SOC2, etc.
- notes (text)
- is_active (bool)
- created_at (datetime)
- updated_at (datetime)

wp_shahi_vendor_dpa (new)
- id (int)
- vendor_id (int, FK)
- dpa_version (varchar, 20) - e.g., "1.0", "2.0"
- file_path (varchar, 255)
- file_hash (varchar, 64)
- signed_date (date, nullable)
- execution_date (date)
- expiration_date (date, nullable)
- status (draft|signed|executed|expired|renewed)
- uploaded_by (int) - user ID
- created_at (datetime)
- updated_at (datetime)

wp_shahi_vendor_compliance (new)
- id (int)
- vendor_id (int)
- audit_date (date)
- audit_type (internal|external|third_party)
- audit_result (pass|fail|conditional)
- next_audit_date (date)
- notes (text)
- created_at (datetime)

wp_shahi_vendor_data_categories (new)
- id (int)
- vendor_id (int)
- category (personal_data|special_categories|sensitive_data)
- data_types (json) - email, phone, purchase history, etc.
- retention_period (varchar, 100) - e.g., "12 months"
- processing_purpose (varchar, 255)
```

### Admin Interface Components
- **Vendor Directory**
  - Searchable vendor list
  - Category filtering
  - Risk level visualization
  - Compliance status indicators
  - Contact information
  
- **Vendor Profile**
  - Complete vendor details
  - DPA history and status
  - Compliance certifications
  - Data categories processed
  - Risk assessment details
  - Audit history
  
- **DPA Management**
  - Upload and version management
  - Signature tracking
  - Expiration alerts
  - Document comparison/diff view
  
- **Risk Dashboard**
  - Overall vendor risk score
  - High-risk vendors alert
  - Risk trend charts
  - Assessment history
  
- **Compliance Calendar**
  - DPA renewal dates
  - Audit schedules
  - Certification expiration
  - Notification timeline

### REST API Endpoints
```
GET    /wp-json/shahi/v1/vendors
POST   /wp-json/shahi/v1/vendors
GET    /wp-json/shahi/v1/vendors/{id}
PUT    /wp-json/shahi/v1/vendors/{id}
DELETE /wp-json/shahi/v1/vendors/{id}

GET    /wp-json/shahi/v1/vendors/{id}/dpa
POST   /wp-json/shahi/v1/vendors/{id}/dpa
GET    /wp-json/shahi/v1/vendors/{id}/dpa/{version}

GET    /wp-json/shahi/v1/vendors/risk-assessment/{id}
GET    /wp-json/shahi/v1/vendors/high-risk
POST   /wp-json/shahi/v1/vendors/import-from-cookies
```

### WP-CLI Commands
```
wp shahi vendors list
wp shahi vendors add <name> <category>
wp shahi vendors dpa-status
wp shahi vendors risk-report
wp shahi vendors sync-from-cookies
wp shahi vendors export --format=csv
```

### Settings Keys
```
shahi_vendor_auto_detect_from_cookies (bool)
shahi_vendor_dpa_expiration_alert_days (int)
shahi_vendor_risk_threshold_alert (int, 1-100)
shahi_vendor_audit_default_frequency (varchar) - e.g., "12 months"
```

### Integration Points
- Consumes vendor info from Cookie Inventory
- Feeds to Analytics/Reporting for compliance scores
- Provides data to Dashboard
- Integrates with DSR Portal for vendor notifications

### Estimated Files
```
VendorManagement.php
VendorRepository.php
RiskAssessmentEngine.php
DPAManager.php
ComplianceMonitor.php
AdminController.php
tables/
  vendor-directory.php
  dpa-management.php
  risk-dashboard.php
  compliance-calendar.php
```

---

### PHASE 3: Aggregators & Advanced Features

---

## 4️⃣ ANALYTICS & REPORTING MODULE
**Key:** `analytics-reporting`  
**Status:** Not Started  
**Priority:** HIGH - Aggregates all data  
**Depends On:** Consent, Accessibility, Forms, Cookie Inventory, Vendor Mgmt

### Purpose
Comprehensive compliance metrics, audit trails, historical tracking, and intelligent reporting across all compliance dimensions.

### Core Features
- **Compliance Score Calculator**
  - Weighted algorithm combining:
    - Consent compliance (banner presence, consent rate)
    - Accessibility compliance (WCAG scan results)
    - Data protection (DPA coverage, vendor risk)
    - Privacy policy (presence, completeness)
    - Cookie transparency (inventory accuracy)
    - Form compliance (retention management, consent)
  - Letter grade assignment (A-F)
  - Score 0-100
  - Historical trend tracking
  
- **Audit Trail Logging**
  - Log all compliance events:
    - Scans run
    - Modules enabled/disabled
    - Consent given/withdrawn
    - DSR requests
    - Form submissions
    - Vendor added/updated
    - Document created/updated
  - Timestamp, user, action details
  - Immutable log (for compliance proof)
  
- **Event Tracking**
  - Track events by module
  - Timeline view
  - Filter by event type, date range, user
  - Export capability
  
- **Dashboard Integration**
  - Provide metrics to Dashboard Module
  - Real-time score updates
  - Widget data preparation
  
- **Reporting & Export**
  - Generate compliance reports (PDF)
  - CSV exports of all metrics
  - Scheduled report generation
  - Email delivery of reports
  - Customizable report templates
  
- **Historical Trends**
  - Track score changes over time
  - Identify improvement areas
  - Benchmark against compliance baselines
  - Predictive analytics (planned)

### Database Tables
```
wp_shahi_audit_log (new)
- id (int, primary key)
- event_type (varchar, 100) - scan_run|consent_given|module_enabled|etc
- module (varchar, 100) - which module triggered event
- user_id (int, nullable)
- action_details (json)
- ip_address (varchar, 45)
- timestamp (datetime)
- [indexed on: event_type, module, timestamp]

wp_shahi_compliance_scores (new)
- id (int)
- score_date (date)
- overall_score (int, 0-100)
- letter_grade (varchar, 1)
- consent_score (int)
- accessibility_score (int)
- data_protection_score (int)
- privacy_score (int)
- cookie_score (int)
- form_score (int)
- metadata (json) - factors contributing to score
- created_at (datetime)

wp_shahi_compliance_events (new)
- id (int)
- event_type (enum: scan|consent|dsr|form|vendor|document)
- event_module (varchar, 100)
- event_count (int)
- event_date (date)
- summary_data (json)
- created_at (datetime)

wp_shahi_reports (new)
- id (int)
- report_type (compliance|audit|custom)
- title (varchar, 255)
- date_from (date)
- date_to (date)
- content (longtext) - JSON report structure
- generated_by (int)
- export_formats (json) - pdf|csv|html
- created_at (datetime)
```

### Admin Interface Components
- **Compliance Dashboard**
  - Overall compliance score (large, prominent)
  - Letter grade display
  - Score by category (pie/bar chart)
  - Trend chart (last 12 months)
  - Problem areas highlighted
  
- **Audit Log Viewer**
  - Searchable event log
  - Filter by type, module, date, user
  - Event detail view
  - Export functionality
  - Immutability indicator
  
- **Report Generator**
  - Date range selector
  - Report template selection
  - Preview
  - Export format options (PDF, CSV, HTML)
  - Scheduled report setup
  - Email delivery configuration
  
- **Trend Analysis**
  - Score history chart
  - Month-over-month comparison
  - Improvement recommendations
  - Baseline comparisons

### REST API Endpoints
```
GET    /wp-json/shahi/v1/compliance-score
GET    /wp-json/shahi/v1/compliance-score/history
GET    /wp-json/shahi/v1/audit-log
GET    /wp-json/shahi/v1/audit-log/{id}
GET    /wp-json/shahi/v1/events
POST   /wp-json/shahi/v1/reports/generate
GET    /wp-json/shahi/v1/reports/{id}
POST   /wp-json/shahi/v1/reports/{id}/export
```

### WP-CLI Commands
```
wp shahi score current
wp shahi score history --months=12
wp shahi audit-log list --limit=100
wp shahi audit-log search --event=scan_run
wp shahi report generate --type=compliance --from=<date> --to=<date>
wp shahi report export --id=<id> --format=pdf
```

### Settings Keys
```
shahi_compliance_score_weights (json) - weighting for each dimension
shahi_audit_log_retention_days (int)
shahi_report_schedule (cron expression)
shahi_report_email_recipients (array)
shahi_score_calculation_frequency (enum: hourly|daily|weekly)
```

### Integration Points
- Consumes data from ALL modules
- Provides metrics to Dashboard Module
- Feeds historical data to trend analysis
- Used for compliance proof and audits

### Estimated Files
```
AnalyticsReporting.php
ComplianceScoreCalculator.php
AuditLogManager.php
ReportGenerator.php
EventTracker.php
TrendAnalyzer.php
AdminController.php
tables/
  compliance-dashboard.php
  audit-log-viewer.php
  report-generator.php
  trend-analysis.php
```

---

## 5️⃣ LEGAL DOCUMENTS GENERATOR MODULE
**Key:** `legal-documents`  
**Status:** Not Started  
**Priority:** MEDIUM - Aggregator, useful but not critical  
**Depends On:** Cookie Inventory, Forms Compliance (loosely)

### Purpose
Generate legally accurate Privacy Policy, Terms of Service, and Cookie Policy documents based on website configuration and auto-detection.

### Core Features
- **3 Document Types**
  - Privacy Policy (GDPR/CCPA/LGPD compliant)
  - Terms of Service (general T&Cs)
  - Cookie Policy (cookie disclosure)
  
- **Guided Questionnaire**
  - 8-step interactive wizard
  - Questions about:
    - Business type
    - Geographic reach
    - Data collection methods
    - Third-party services used
    - Age targeting (COPPA)
    - Payment processing
    - International transfers
  
- **Auto-Detection**
  - Scan website for:
    - Installed plugins (contact forms, e-commerce)
    - Active integrations (analytics, ads)
    - WooCommerce shop (if present)
    - Comment system (if enabled)
    - Cookies (from Cookie Inventory module)
  
- **Template System**
  - Base templates (GDPR, CCPA, LGPD)
  - Customizable sections
  - Snippet library for specific scenarios
  - White-label ready
  
- **Document Management**
  - Version history with diff view
  - Rollback capability
  - Auto-save functionality
  - Comparison between versions
  
- **Publishing**
  - Auto-create pages in WordPress
  - Shortcode embedding
  - Link generation
  - Privacy page assignment
  
- **Compliance Sections**
  - GDPR Art. 6 (lawful basis)
  - CCPA disclosures
  - COPPA compliance
  - Third-party links and policies
  - Contact information for data requests

### Database Tables
```
wp_shahi_documents (new)
- id (int, primary key)
- document_type (privacy_policy|terms_of_service|cookie_policy)
- title (varchar, 255)
- content (longtext)
- version (varchar, 20) - e.g., "1.0", "1.1"
- status (draft|published|archived)
- last_questionnaire_data (json)
- auto_updated (bool) - was it auto-generated
- published_page_id (int, nullable)
- published_url (varchar, 255)
- created_at (datetime)
- updated_at (datetime)

wp_shahi_document_versions (new)
- id (int)
- document_id (int)
- version (varchar, 20)
- content (longtext)
- change_summary (text)
- created_by (int)
- created_at (datetime)

wp_shahi_document_questionnaire (new)
- id (int)
- document_id (int)
- question_key (varchar, 100)
- answer (json) - flexible for different answer types
- created_at (datetime)
```

### Admin Interface Components
- **Document Manager**
  - List of all documents
  - Status indicators
  - Last updated date
  - Version history link
  - Quick actions (edit, preview, publish)
  
- **Questionnaire Wizard**
  - Step-by-step UI
  - Progress indicator
  - Save progress locally
  - Question help text
  - Auto-detection results display
  
- **Document Editor**
  - Rich text editor
  - Section-based organization
  - Quick variable insertion (e.g., {{company_name}})
  - Compliance annotation tooltips
  
- **Version History**
  - Timeline view
  - Diff comparison between versions
  - Rollback button
  - Change summary per version
  
- **Publishing Interface**
  - Preview mode
  - Publish to new page
  - Update existing page
  - URL/slug configuration
  - Link verification

### REST API Endpoints
```
GET    /wp-json/shahi/v1/documents
GET    /wp-json/shahi/v1/documents/{id}
POST   /wp-json/shahi/v1/documents
PUT    /wp-json/shahi/v1/documents/{id}
DELETE /wp-json/shahi/v1/documents/{id}

GET    /wp-json/shahi/v1/documents/{id}/versions
GET    /wp-json/shahi/v1/documents/{id}/versions/{version}
POST   /wp-json/shahi/v1/documents/{id}/publish

GET    /wp-json/shahi/v1/questionnaire/{type}
POST   /wp-json/shahi/v1/questionnaire/{type}/generate
GET    /wp-json/shahi/v1/questionnaire/auto-detect
```

### WP-CLI Commands
```
wp shahi document list
wp shahi document generate --type=privacy_policy
wp shahi document publish --id=<id> --page_id=<id>
wp shahi document version-history --id=<id>
wp shahi document rollback --id=<id> --version=<version>
```

### Settings Keys
```
shahi_document_company_name (varchar)
shahi_document_company_email (varchar)
shahi_document_default_jurisdiction (varchar)
shahi_document_auto_update (bool)
shahi_document_privacy_page_id (int)
shahi_document_cookie_policy_page_id (int)
```

### Integration Points
- Reads from Cookie Inventory (cookie list)
- Reads from Forms Compliance (form types)
- Uses plugin detection for auto-discovery
- Data feeds to Dashboard/Analytics

### Estimated Files
```
LegalDocuments.php
DocumentRepository.php
QuestionnaireEngine.php
DocumentGenerator.php
TemplateManager.php
PublishingManager.php
AdminController.php
templates/
  questionnaire/
    privacy-policy-wizard.php
    terms-of-service-wizard.php
    cookie-policy-wizard.php
tables/
  document-manager.php
  version-history.php
```

---

## 6️⃣ DATA SUBJECT RIGHTS (DSR) PORTAL MODULE
**Key:** `dsr-portal`  
**Status:** Not Started  
**Priority:** HIGH - Legal requirement  
**Depends On:** Forms Compliance, Cookie Inventory (for data sources)

### Purpose
Fully automated Data Subject Rights request management with request processing, data export, and compliance tracking for GDPR, CCPA, LGPD, and similar regulations.

### Core Features
- **7 Request Types**
  1. Access (GDPR Art. 15 / CCPA § 1798.100)
  2. Rectification (GDPR Art. 16)
  3. Erasure (GDPR Art. 17)
  4. Portability (GDPR Art. 20)
  5. Restriction (GDPR Art. 18)
  6. Object (GDPR Art. 21)
  7. Automated Decision (GDPR Art. 22)
  
- **Public Request Portal**
  - Shortcode-based form: `[shahi_dsr_form]`
  - Request type selection
  - Email verification (double opt-in)
  - CAPTCHA protection
  - Multi-language support
  
- **Verification Process**
  - Email verification token
  - Time-limited verification links
  - Secondary verification for sensitive requests
  - Verification email templates
  
- **Data Export & Compilation**
  - Multi-source data gathering:
    - WordPress users
    - WooCommerce orders/customer data
    - Form submissions (from Forms module)
    - Comments
    - Cookie consent records
    - Custom post types
  - Export formats: JSON, CSV, XML
  - Aggregated data package
  - Searchable/filterable export
  
- **Request Workflow**
  - Status: pending → verified → in_progress → completed/rejected
  - SLA tracking (default 30 days)
  - Deadline alerts
  - Email notifications at each step
  
- **Admin Management Dashboard**
  - Request queue
  - Filter by type, status, date
  - Search by email/user
  - Request details view
  - Bulk actions
  - Status update capability
  - Notes/comments per request
  
- **Compliance Tracking**
  - SLA compliance metrics
  - Request history
  - Export audit trail
  - Performance reporting
  
- **Auto-Processing**
  - Erasure requests can auto-delete data
  - Portability auto-packages data
  - Configurable per request type

### Database Tables
```
wp_shahi_dsr_requests (new)
- id (int, primary key)
- request_type (access|rectification|erasure|portability|restriction|object|automated_decision)
- requester_email (varchar, 255)
- user_id (int, nullable) - if authenticated
- verification_token (varchar, 64)
- verification_sent (datetime)
- verified_at (datetime, nullable)
- status (pending|verified|in_progress|completed|rejected)
- data_export (longblob, nullable) - JSON/CSV of data
- export_format (json) - available formats
- notes (text)
- admin_notes (text)
- sla_deadline (datetime)
- completed_at (datetime, nullable)
- processed_by (int) - user ID
- created_at (datetime)
- updated_at (datetime)

wp_shahi_dsr_request_data_sources (new)
- id (int)
- request_id (int)
- source_type (users|woocommerce|forms|comments|cookies|custom)
- source_id (int)
- data_included (bool)
- notes (text)

wp_shahi_dsr_compliance_metrics (new)
- id (int)
- metric_date (date)
- total_requests (int)
- verified_count (int)
- completed_count (int)
- avg_days_to_complete (decimal)
- sla_compliance_rate (decimal, 0-100)
- created_at (datetime)
```

### Frontend Components
- **DSR Request Form**
  - Request type dropdown
  - Email input with validation
  - Additional fields based on type
  - CAPTCHA protection
  - Privacy agreement checkbox
  - Submit button
  
- **Verification Email**
  - Secure verification link
  - Time limit (24-48 hours)
  - Clear instructions
  - Confirmation page after verification

### Admin Interface Components
- **Request Dashboard**
  - Queue view with status counts
  - Overdue request alerts
  - Visual status indicators
  - Quick filters
  
- **Request Details**
  - Full request information
  - Verification status
  - Data export contents
  - SLA deadline and progress
  - Admin notes
  - Status update actions
  
- **Data Export Manager**
  - Preview what will be exported
  - Select/deselect data sources
  - Choose export format
  - Download/send via email
  - Verify data completeness
  
- **Compliance Report**
  - SLA compliance metrics
  - Request type breakdown
  - Timeline analysis
  - Export for audits

### REST API Endpoints
```
POST   /wp-json/shahi/v1/dsr/request
GET    /wp-json/shahi/v1/dsr/requests
GET    /wp-json/shahi/v1/dsr/requests/{id}
PUT    /wp-json/shahi/v1/dsr/requests/{id}
DELETE /wp-json/shahi/v1/dsr/requests/{id}

POST   /wp-json/shahi/v1/dsr/{id}/verify
POST   /wp-json/shahi/v1/dsr/{id}/export
POST   /wp-json/shahi/v1/dsr/{id}/complete
POST   /wp-json/shahi/v1/dsr/{id}/reject

GET    /wp-json/shahi/v1/dsr/compliance-metrics
```

### WP-CLI Commands
```
wp shahi dsr list
wp shahi dsr list --status=pending
wp shahi dsr get <id>
wp shahi dsr complete <id>
wp shahi dsr reject <id> --reason="<reason>"
wp shahi dsr export <id>
wp shahi dsr metrics
```

### Settings Keys
```
shahi_dsr_sla_days (int, default: 30)
shahi_dsr_email_from (varchar)
shahi_dsr_auto_process_erasure (bool)
shahi_dsr_auto_process_portability (bool)
shahi_dsr_data_sources (json) - which sources to include
shahi_dsr_secondary_verification_required (bool)
shahi_dsr_captcha_enabled (bool)
shahi_dsr_verification_link_expiration_hours (int, default: 48)
```

### Integration Points
- Integrates with Forms Compliance (form submission data)
- Integrates with Cookie Inventory (consent records)
- WooCommerce integration (orders, customer data)
- WordPress core data (users, posts, comments)
- Feeds data to Analytics/Reporting for metrics

### Estimated Files
```
DSRPortal.php
DSRRepository.php
RequestProcessor.php
DataExporter.php
VerificationManager.php
SLATracker.php
Integrations/
  UserDataExporter.php
  WooCommerceDataExporter.php
  FormSubmissionExporter.php
  CommentDataExporter.php
AdminController.php
FrontendController.php
tables/
  dsr-request-queue.php
  dsr-request-details.php
  dsr-data-manager.php
  dsr-compliance-report.php
```

---

### PHASE 4: Final Aggregators & Support

---

## 7️⃣ DASHBOARD MODULE
**Key:** `dashboard-module` (Primary Admin Dashboard)  
**Status:** Not Started  
**Priority:** CRITICAL - Central hub  
**Depends On:** ALL OTHER MODULES (Consent, Accessibility, Cookies, Forms, Vendors, Analytics, Legal, DSR)

### Purpose
Comprehensive compliance dashboard showing real-time metrics, status of all modules, quick actions, and compliance overview.

### Core Features
- **Compliance Score Dashboard**
  - Large compliance score (0-100)
  - Letter grade (A-F)
  - Last updated timestamp
  - Trend indicator (↑↓)
  
- **Module Status Overview**
  - Cards for each enabled module
  - Status indicators (enabled/disabled)
  - Module-specific metrics
  - Quick action buttons (Settings, Scan, etc.)
  
- **Widget System**
  - Accessibility Summary Widget
    - Latest scan score
    - Critical issues count
    - WCAG level
    - "Run Scan" button
  
  - Consent Management Widget
    - Active consent rate (%)
    - Total consents tracked
    - Pending/withdrawn count
    - "View Consents" link
  
  - Cookie Inventory Widget
    - Total cookies detected
    - Categorization breakdown (pie chart)
    - High-risk cookies highlighted
    - "Manage Cookies" link
  
  - Forms Compliance Widget
    - Active forms count
    - Forms with issues highlighted
    - Pending submissions
    - "View Forms" link
  
  - DSR Portal Widget
    - Pending requests
    - SLA status (on-track/at-risk/overdue)
    - Completion rate
    - "Manage Requests" link
  
  - Vendor Management Widget
    - Total vendors
    - High-risk vendors alert
    - DPA expiration alerts
    - "Manage Vendors" link
  
  - Activity Feed
    - Recent compliance events
    - Last 10 activities
    - Timestamps
    - User information
  
- **Quick Actions**
  - Run accessibility scan
  - View consent log
  - Add new vendor
  - Create DSR request
  - Generate compliance report
  
- **Alerts & Notifications**
  - SLA deadline warnings
  - DPA expiration alerts
  - Critical accessibility issues
  - High-risk vendors
  - Policy expiration notices
  
- **Performance Metrics**
  - Page load time
  - Database query efficiency
  - Cache hit rate
  - Asset loading status

### Database Tables
```
(Primarily uses data from other modules - no new tables needed)
wp_shahi_dashboard_preferences (new, optional)
- id (int)
- user_id (int)
- widget_layout (json)
- hidden_widgets (json)
- refresh_interval (int) - seconds
- created_at (datetime)
- updated_at (datetime)
```

### Admin Interface Components
- **Main Dashboard**
  - Header with compliance score
  - Grid layout for widgets
  - Draggable/customizable widgets
  - Full-screen widgets available
  
- **Module Cards**
  - Module name
  - Status (enabled/disabled)
  - Key metrics
  - Status indicator light
  - Settings button
  - Module-specific action button
  
- **Alert Center**
  - Dismissible alerts
  - Color-coded severity
  - Action links
  - Alert history
  
- **Customization Panel**
  - Widget visibility toggles
  - Layout options
  - Refresh frequency
  - Dark/light mode toggle
  - Export dashboard view

### REST API Endpoints
```
GET    /wp-json/shahi/v1/dashboard/overview
GET    /wp-json/shahi/v1/dashboard/compliance-score
GET    /wp-json/shahi/v1/dashboard/modules-status
GET    /wp-json/shahi/v1/dashboard/alerts
GET    /wp-json/shahi/v1/dashboard/activity-feed
GET    /wp-json/shahi/v1/dashboard/widgets/{widget_id}
POST   /wp-json/shahi/v1/dashboard/preferences
```

### WP-CLI Commands
```
wp shahi dashboard status
wp shahi dashboard metrics
wp shahi dashboard reset-preferences
```

### Settings Keys
```
shahi_dashboard_refresh_interval (int, default: 300 - seconds)
shahi_dashboard_show_disabled_modules (bool, default: false)
shahi_dashboard_default_widgets (json)
shahi_dashboard_dark_mode (bool)
```

### Integration Points
- Reads data from ALL modules
- Real-time updates via AJAX
- Analytics provides compliance score
- Each module provides widget data
- Activity feed aggregates all events

### Estimated Files
```
DashboardModule.php
DashboardController.php
WidgetManager.php
Widgets/
  AccessibilityWidget.php
  ConsentWidget.php
  CookieWidget.php
  FormWidget.php
  DSRWidget.php
  VendorWidget.php
  ActivityFeedWidget.php
AlertManager.php
PerformanceTracker.php
AdminController.php
tables/
  main-dashboard.php
  widget-areas.php
  alerts-center.php
```

---

## 8️⃣ DEVELOPER TOOLS MODULE
**Key:** `developer-tools`  
**Status:** Not Started  
**Priority:** MEDIUM - Support/extensibility  
**Depends On:** None (standalone, but benefits from all modules)

### Purpose
Documentation, APIs, SDKs, and extension hooks for developers to integrate with and extend the plugin.

### Core Features
- **JavaScript SDK**
  - Consent API methods
  - Cookie helpers
  - Event dispatching
  - Initialization
  - Code examples
  
- **REST API Documentation**
  - Complete endpoint reference
  - Request/response examples
  - Authentication details
  - Rate limiting info
  - SDK integration guide
  
- **Hook Reference**
  - All 50+ documented hooks
  - Usage examples
  - Parameter documentation
  - Callback signatures
  
- **Code Examples & Snippets**
  - Integration patterns
  - Custom module example
  - Common tasks
  - Best practices
  - GitHub repository link
  
- **Extensibility Framework**
  - Module interface contract
  - Custom module scaffolding
  - Hook system explanation
  - Filter documentation
  
- **Admin Documentation**
  - In-plugin help system
  - Contextual documentation
  - Developer mode (enables debug logging)
  - Error message details

### Features Documentation
- **Consent API**
  ```javascript
  SLOS.consent.getStatus() // -> boolean
  SLOS.consent.savePreferences(categories) // -> promise
  SLOS.consent.withdrawConsent() // -> promise
  SLOS.consent.getPreferences() // -> object
  ```
  
- **Cookie Helpers**
  ```javascript
  SLOS.cookies.getCookie(name)
  SLOS.cookies.setCookie(name, value, days)
  SLOS.cookies.deleteCookie(name)
  SLOS.cookies.getCookiesByCategory(category)
  ```
  
- **Event System**
  ```javascript
  SLOS.events.on('consent:given', callback)
  SLOS.events.on('scan:completed', callback)
  SLOS.events.trigger('custom:event', data)
  ```

### Admin Interface Components
- **Documentation Hub**
  - Search functionality
  - Table of contents
  - Code syntax highlighting
  - Copy-to-clipboard for code
  
- **API Playground**
  - Interactive API testing
  - Request builder
  - Response viewer
  - Authentication setup
  
- **Developer Mode**
  - Debug logging toggle
  - Performance profiling
  - Error details in output
  - Detailed error logs
  
- **Custom Hooks Reference**
  - Hook name, type (action/filter)
  - Hook location (file/line)
  - Parameters/return values
  - Usage example
  
- **Code Snippets**
  - Categorized code examples
  - Copy button
  - Language selection (PHP/JS)
  - Links to full examples

### Database Tables
```
(Minimal - mainly documentation)
wp_shahi_api_keys (new, optional)
- id (int)
- user_id (int)
- api_key (varchar, 255) - hashed
- api_secret (varchar, 255) - hashed
- name (varchar, 100)
- is_active (bool)
- created_at (datetime)
- last_used (datetime, nullable)

wp_shahi_developer_logs (new, optional - if dev mode enabled)
- id (int)
- user_id (int)
- event_type (api_call|hook_fired|error)
- event_data (json)
- timestamp (datetime)
```

### REST API Endpoints
```
GET    /wp-json/shahi/v1/docs
GET    /wp-json/shahi/v1/docs/api
GET    /wp-json/shahi/v1/docs/hooks
GET    /wp-json/shahi/v1/docs/examples/{category}

GET    /wp-json/shahi/v1/dev/config
POST   /wp-json/shahi/v1/dev/api-keys
GET    /wp-json/shahi/v1/dev/logs (if dev mode enabled)
```

### WP-CLI Commands
```
wp shahi docs list
wp shahi docs search <keyword>
wp shahi api-keys generate
wp shahi api-keys revoke <key_id>
wp shahi dev-mode enable
wp shahi dev-mode disable
wp shahi dev logs --tail=50
```

### Settings Keys
```
shahi_developer_mode (bool, default: false)
shahi_developer_debug_logging (bool, default: false)
shahi_api_rate_limit (int, default: 1000 per hour)
shahi_api_require_auth (bool, default: true)
```

### Documentation Files
```
docs/
  api-reference.md (REST API)
  hook-reference.md (WP hooks/filters)
  sdk-reference.md (JavaScript SDK)
  module-development.md (Custom modules)
  code-examples.md (Snippets & patterns)
  best-practices.md (Recommendations)
  troubleshooting.md (FAQ & issues)
  changelog.md (Version history)
```

### Integration Points
- Documents APIs from all modules
- Hooks system for plugin extensibility
- SDK wraps core functionality
- Developer mode integrates with all modules

### Estimated Files
```
DeveloperTools.php
DocumentationManager.php
APIKeyManager.php
DeveloperLogger.php (if logging enabled)
SDK/
  Consent.js
  Cookies.js
  Events.js
  API.js
AdminController.php
docs/
  (markdown files)
tables/
  developer-hub.php
  api-playground.php
```

---

## 📋 MODULE DEPENDENCY MATRIX

```
                    Depends On
                    ↓
Consent             ✅ (No dependencies)
Accessibility       ✅ (No dependencies)
Security            ✅ (No dependencies)

Cookie Inventory    ✅ (No dependencies)
Forms Compliance    ← Consent (loosely)
Vendor Mgmt         ← Cookie Inventory (loosely)

Analytics & Report  ← ALL (Consent, Accessibility, Forms, Cookies, Vendors)
Legal Documents     ← Cookie Inventory, Forms (loosely)
DSR Portal          ← Forms, Cookies, Core WordPress

Dashboard           ← ALL (All other modules)
Developer Tools     ✅ (No dependencies - standalone docs)
```

---

## 🚀 BUILD PRIORITY MATRIX

```
Priority  Phase   Module
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CRITICAL  1       Cookie Inventory (foundation)
HIGH      1       Forms Compliance (independent, important)
HIGH      2       Vendor Management (depends on Cookie)
HIGH      3       Analytics & Reporting (depends on all)
MEDIUM    2       Legal Documents (depends on Cookie, Forms)
HIGH      3       DSR Portal (legal requirement)
CRITICAL  4       Dashboard (final aggregator)
MEDIUM    4       Developer Tools (documentation/support)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

## 📝 NEXT STEPS

1. **Phase 1 Start** (Cookie Inventory)
   - [ ] Create module directory structure
   - [ ] Implement database tables
   - [ ] Build detection JavaScript
   - [ ] Create admin interface
   - [ ] Add REST API endpoints

2. **Detailed Analysis** (Before coding each module)
   - [ ] Research existing implementations
   - [ ] Study CF_FEATURES requirements
   - [ ] Design database schema
   - [ ] Plan API endpoints
   - [ ] Create wireframes

3. **Testing & Validation**
   - [ ] Unit tests for business logic
   - [ ] Integration tests with other modules
   - [ ] Manual QA testing
   - [ ] Performance benchmarking
   - [ ] Security audit

---

**Document Status:** Planning Phase  
**Last Updated:** December 18, 2025  
**Ready For:** Detailed research and implementation planning
