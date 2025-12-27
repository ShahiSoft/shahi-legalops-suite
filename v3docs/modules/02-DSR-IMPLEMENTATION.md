# DSR (Data Subject Rights) Portal - Implementation Guide

**Phase:** 3 (Weeks 8-10 with 2026 enhancements)  
**Effort:** 70-85 hours (enhanced from 45)  
**Priority:** 1 (GDPR/CCPA/LGPD critical compliance)  
**Dependencies:** TASK 2.15 (Consent module complete, Phase 2 checkpoint)  
**Implementation:** AI Agents (atomic tasks with verification)  
**Key Tasks:** TASK 3.1 through TASK 3.13 (see ROADMAP.md for details)  
**Code Pattern:** Follow AccessibilityScanner module structure exactly  
**Documentation:** See `/v3docs/AI-AGENT-GUIDE.md` for implementation approach  
**Winning Features:** Automated detection, AI suggestions, enterprise team management

---

## 📋 FEATURE SPECIFICATION - ENHANCED 2026 WINNING FEATURES

### Request Types - Complete GDPR/CCPA Compliance
1. **Access Request** - User requests copy of their data
   - **Enhanced:** Multi-format export (PDF, JSON, CSV, XML)
   - **Preview:** Show what will be exported before generating
   - **Encryption:** Optional encrypted download link
   - **Streaming:** Large exports via stream (not in memory)

2. **Rectification** - User requests to correct incorrect data
   - **Self-Service:** User proposes corrections
   - **Review Workflow:** Admin approval required
   - **Audit Trail:** Track all changes with timestamps
   - **Confirmation:** Auto-email when approved/rejected

3. **Erasure** - User requests data deletion ("right to be forgotten")
   - **Cascade Deletion:** Remove from all data sources
   - **Soft Delete Option:** Archive instead of permanent delete
   - **Compliance:** GDPR Article 17 full compliance
   - **Proof:** Generate compliance certificate

4. **Portability** - User requests data in machine-readable format
   - **Multiple Formats:** JSON, CSV, XML, Excel
   - **Structured:** Organized by data source
   - **Validation:** Ensure format compliance
   - **Streaming:** Handle large exports efficiently

5. **Restriction** - User requests to restrict processing
   - **Flag System:** Mark user data as restricted
   - **Marketing Suspension:** Stop marketing emails
   - **Analytics Opt-out:** Exclude from analytics
   - **Compliance Badge:** Show restriction status

6. **Object** - User objects to processing (marketing, analytics)
   - **Type-Specific:** Object to marketing, analytics, or all
   - **Preference Center:** Integration with consent system
   - **Automation:** Auto-suppress based on preference
   - **Audit:** Track objections

7. **Automated Decision** - User requests human review
   - **LLM Integration:** Option for AI-powered summary
   - **Manual Review:** Flag for staff review
   - **Timeline:** Separate SLA tracking
   - **Explanation:** Provide reasoning for decisions

### Request Workflow - 2026 Standard
```
Submitted → Email Verification → Verified → In Progress → Completed/Rejected → Archive
   (1)          (2)              (3)         (4)          (5)                 (6)
   
With notifications at each step:
- Step 1: Receipt acknowledgment
- Step 2: Verification required
- Step 3: Request acknowledged
- Step 4: Progress updates (if >10 days)
- Step 5: Completion notification
```

### Data Source Detection - AI Powered
- **Automatic Discovery:** Scan WordPress for user data
  - Users table + all user meta
  - Posts/Pages authored or commented on
  - WooCommerce (orders, customer data, reviews)
  - Forms (CF7, WPForms, Gravity Forms, Ninja Forms, Forminator)
  - Comments authored
  - Custom post types
  - ACF fields
  - Custom meta boxes
  - User activity logs
  - Backup files (if searchable)
  
- **Plugin Integration:** Hooks for 3rd-party plugins
  - `complyflow_dsr_data_sources` hook
  - Plugin can register custom data fetchers
  - Extensible architecture

- **AI Suggestions:** (new)
  - Suggest likely data sources
  - Recommend related exports
  - Warn of potential issues
  - Estimate completion time

### Export Formats - Comprehensive
- **JSON** - Structured, nested data
- **CSV** - Tabular, spreadsheet-ready
- **XML** - Machine-readable, enterprise
- **PDF** - Human-readable, legally formatted
- **HTML** - Interactive, web-viewable
- **XLSX** - Excel format with formatting

### SLA Management - Enterprise Grade
- **Response Time:** 
  - 30 days (GDPR default)
  - Configurable by jurisdiction
  - Auto-calculated from submission
  
- **Tracking:** 
  - Visual SLA progress indicator
  - Days remaining counter
  - Automatic deadline reminders (5 days, 1 day)
  
- **Notifications:** 
  - Email user when approaching deadline
  - Internal alerts for staff
  - Dashboard widget showing pending
  
- **Extensions:** 
  - Support for requesting 1-month extensions
  - Track extension approvals
  - Complex cases (30 + 30 + 30 = 90 days)

### Team Management - Enterprise Features (New)
- **Role-Based Access:**
  - DSR Manager: Full access
  - DSR Reviewer: Review/approve requests
  - DSR Processor: Mark complete
  - View Only: Can view but not modify
  
- **Workload Distribution:**
  - Assign requests to team members
  - Track who's working on what
  - Capacity planning
  
- **Audit Trail:**
  - Who accessed what data
  - When reports were exported
  - Compliance documentation

### User-Friendly Request Form - CX Focus
- **Simple Interface:**
  - Clear request type selection with explanations
  - Required fields only (name, email, request type)
  - Optional: user account connection
  
- **Smart Detection:**
  - Pre-detect if user is logged in (auto-fill email)
  - Auto-identify associated data
  - Show what will be included
  
- **Transparency:**
  - Clear explanation of each request type
  - Estimate of completion time
  - Link to privacy policy
  - Compliance badges

### Security & Privacy
- **Verification:**
  - Email verification required
  - Optional: SMS verification
  - Challenge questions (for heightened security)
  
- **Data Protection:**
  - Encrypted storage for export files
  - Time-limited download links (7 days default)
  - HTTPS enforcement
  - No unencrypted export files on disk
  
- **Access Control:**
  - Only requester can download their export
  - Track who downloads what
  - Downloadable via secure token link
  - Single-use tokens (optional)
  
- **Logging:**
  - Complete audit trail of access
  - Timestamp all actions
  - Track admin access to requests
  - GDPR-compliant retention

### Analytics & Reporting (New)
- **Dashboard:**
  - Total requests received
  - Breakdown by type
  - Average resolution time
  - SLA compliance percentage
  
- **Reports:**
  - Monthly request summary
  - Trend analysis
  - SLA adherence report
  - Data size analysis
  
- **Compliance:**
  - Export audit trail
  - Generate compliance certificates
  - Proof of timely handling

---

## 🗄️ DATABASE SCHEMA

### Table: `wp_complyflow_dsr_requests`

```sql
CREATE TABLE wp_complyflow_dsr_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Request info
    request_type ENUM('access', 'rectification', 'erasure', 'portability', 'restriction', 'object', 'automated_decision') NOT NULL,
    status ENUM('pending', 'verified', 'in_progress', 'completed', 'rejected') DEFAULT 'pending',
    
    -- Requester info
    requester_email VARCHAR(255) NOT NULL,
    requester_name VARCHAR(255) NULL,
    user_id BIGINT UNSIGNED NULL,
    
    -- Verification
    verification_token VARCHAR(64) UNIQUE NOT NULL,
    verification_sent_at DATETIME NOT NULL,
    verified_at DATETIME NULL,
    
    -- SLA tracking
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    sla_deadline DATETIME NOT NULL,
    completed_at DATETIME NULL,
    
    -- Export data
    export_format VARCHAR(10) DEFAULT 'json',
    export_file_path VARCHAR(255) NULL,
    download_count INT DEFAULT 0,
    download_expires_at DATETIME NULL,
    
    -- Processing
    processed_by BIGINT UNSIGNED NULL,
    admin_notes LONGTEXT NULL,
    
    -- Request details
    reason VARCHAR(500) NULL,
    additional_info LONGTEXT NULL,
    
    KEY idx_email (requester_email),
    KEY idx_user_id (user_id),
    KEY idx_status (status),
    KEY idx_request_type (request_type),
    KEY idx_submitted_at (submitted_at),
    KEY idx_sla_deadline (sla_deadline)
);
```

### Table: `wp_complyflow_dsr_request_data_sources`

```sql
CREATE TABLE wp_complyflow_dsr_request_data_sources (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dsr_request_id BIGINT UNSIGNED NOT NULL,
    source_type VARCHAR(50) NOT NULL,  -- 'wordpress_users', 'woocommerce', 'cf7', etc.
    data_count INT DEFAULT 0,          -- How many records found
    export_status VARCHAR(20) DEFAULT 'pending', -- pending, exported, error
    error_message VARCHAR(255) NULL,
    last_checked DATETIME NULL,
    
    FOREIGN KEY (dsr_request_id) REFERENCES wp_complyflow_dsr_requests(id) ON DELETE CASCADE,
    KEY idx_dsr_request_id (dsr_request_id),
    KEY idx_source_type (source_type)
);
```

---

## 🏗️ FILE STRUCTURE

```
includes/Modules/DSR/
├── DSRPortal.php                   # Main module class
├── Models/
│   ├── DSRRequest.php              # Request model
│   └── DataSource.php              # Data source model
├── Services/
│   ├── RequestService.php           # Request lifecycle
│   ├── VerificationService.php      # Email verification
│   ├── DataExporter.php             # Data collection and export
│   ├── DataSourceDetector.php       # Auto-detect data sources
│   └── SLATracker.php               # SLA calculation
├── Repositories/
│   ├── DSRRepository.php            # Request CRUD
│   └── DataSourceRepository.php     # Source access
├── Admin/
│   ├── DSRDashboard.php             # Request management UI
│   ├── RequestViewer.php            # Request detail view
│   └── ExportManager.php            # Export creation/download
├── Frontend/
│   └── RequestForm.php              # Public form rendering
├── Shortcodes/
│   └── DSRFormShortcode.php          # [complyflow_dsr_form] shortcode
├── Extractors/
│   ├── UserDataExtractor.php        # WordPress users
│   ├── PostDataExtractor.php        # Posts/pages/comments
│   ├── WooCommerceExtractor.php     # WooCommerce data
│   ├── FormExtractor.php            # Form submissions
│   └── CustomDataExtractor.php      # Hook for custom data
├── API/
│   └── DSRController.php            # REST API endpoints
├── Database/
│   └── Migrations/
│       └── migration_2_0_0_dsr.php
└── Hooks/
    └── Filters.php                  # Hooks for extensions
```

---

## 📝 IMPLEMENTATION PHASES (Week by Week)

### WEEK 7: Request Processing & Verification (15 hours)

#### Day 1: Database & Models (5 hours)
```
Tasks:
- [ ] Create migration for DSR tables
- [ ] Create DSRRequest model
- [ ] Create DataSource model
- [ ] Create DSRRepository with CRUD
- [ ] Test table creation
```

#### Day 2-3: Request & Verification Service (10 hours)
```
Tasks:
- [ ] Create RequestService class
  - [ ] Create new request
  - [ ] Generate verification token (secure random)
  - [ ] Calculate SLA deadline (30 days from today)
  - [ ] Store request in database
- [ ] Create VerificationService class
  - [ ] Send verification email (double opt-in)
  - [ ] Validate token from email link
  - [ ] Mark as verified
  - [ ] Send confirmation email
- [ ] Create email templates (verification, receipt, completion)
```

**Code Example:**
```php
class RequestService {
    public function create_request($request_type, $email, $details = []) {
        // Validate request type
        // Generate token
        // Calculate SLA deadline (30 days)
        // Save to database
        // Return request ID
        
        // Fire hook: do_action('complyflow_dsr_created', $request_id)
    }
    
    public function verify_request($token) {
        // Find request by token
        // Mark as verified
        // Fire hook: do_action('complyflow_dsr_verified', $request_id)
        // Return success/error
    }
}
```

---

### WEEK 8: Data Collection & Export (16 hours)

#### Day 1-2: Data Extractors (8 hours)
```
Tasks:
- [ ] Create UserDataExtractor
  - [ ] Get WP user data
  - [ ] Include user metadata
  - [ ] Include user email subscriptions
- [ ] Create PostDataExtractor
  - [ ] Get posts authored by user
  - [ ] Get comments by user
  - [ ] Include post metadata
  - [ ] Include attachment data
- [ ] Create WooCommerceExtractor (if WooCommerce active)
  - [ ] Get customer orders
  - [ ] Get customer address data
  - [ ] Get customer reviews
  - [ ] Get customer billing/shipping info
- [ ] Create FormExtractor
  - [ ] Detect CF7, WPForms, Gravity, Ninja Forms
  - [ ] Extract submissions for this email
  - [ ] Include form data
```

#### Day 3-4: Export Generation (8 hours)
```
Tasks:
- [ ] Create DataExporter service
  - [ ] Aggregate data from all sources
  - [ ] Generate JSON export
  - [ ] Generate CSV export
  - [ ] Generate XML export
  - [ ] Create downloadable file
  - [ ] Implement download tracking
  - [ ] Implement file expiration (7-14 days)
- [ ] Create export storage mechanism
  - [ ] Store in wp-content/uploads/complyflow/
  - [ ] Secure filename (not guessable)
  - [ ] Delete expired files via cron
```

**Export Structure (JSON):**
```json
{
  "exported_at": "2025-12-19T10:30:00Z",
  "requester_email": "user@example.com",
  "data_sources": {
    "wordpress_user": {
      "user_id": 123,
      "email": "user@example.com",
      "display_name": "John Doe",
      "user_meta": { ... }
    },
    "posts": [
      { "post_id": 456, "title": "...", "content": "...", "created_at": "..." }
    ],
    "woocommerce": {
      "orders": [
        { "order_id": "789", "total": "100.00", "items": [...] }
      ],
      "customer_data": { ... }
    },
    "form_submissions": [
      { "form_id": "cf7_contact", "submission_date": "...", "data": {...} }
    ]
  }
}
```

---

### WEEK 9: Admin Dashboard & API (14 hours)

#### Day 1-2: Admin Dashboard (8 hours)
```
Tasks:
- [ ] Create DSRDashboard admin page
- [ ] Build requests table with:
  - [ ] Filters (status, type, date, email)
  - [ ] Bulk actions (mark completed, reject, download export)
  - [ ] Search by email
  - [ ] Pagination
  - [ ] Sort by SLA deadline (show urgent first)
- [ ] Create request detail modal
  - [ ] Show all request info
  - [ ] Show verification status
  - [ ] Show SLA progress bar
  - [ ] Show data sources found
  - [ ] Show admin notes textarea
  - [ ] Allow status change dropdown
  - [ ] Allow download export button
- [ ] Add SLA tracking (color code urgent requests)
```

#### Day 3: Shortcode & REST API (6 hours)
```
Tasks:
- [ ] Create DSRFormShortcode
  - [ ] Build public request form
  - [ ] Form fields: request type, email, name, details
  - [ ] Form validation (email format, required fields)
  - [ ] CAPTCHA (optional, recommended)
  - [ ] Terms acceptance checkbox
- [ ] Create DSRController REST API
  - [ ] POST /dsr/request - Submit new request
  - [ ] GET /dsr/requests/{id} - View own request
  - [ ] GET /dsr/requests/{id}/export - Download export
  - [ ] POST /dsr/admin/requests (admin only) - List all requests
  - [ ] PUT /dsr/admin/requests/{id} (admin only) - Update status
```

**REST API Endpoints:**
```
POST /wp-json/shahi-legalops-suite/v1/dsr/request
    Body: {request_type: "access", email: "user@example.com", name: "John Doe"}
    Response: {success: true, message: "Check your email for verification link"}

GET /wp-json/shahi-legalops-suite/v1/dsr/requests/{id}?token={token}
    Response: {id: 123, status: "verified", sla_deadline: "...", data_found: {...}}

GET /wp-json/shahi-legalops-suite/v1/dsr/requests/{id}/export?format=json&token={token}
    Response: [Streaming file download]

GET /wp-json/shahi-legalops-suite/v1/dsr/admin/requests (admin only)
    Params: status, type, search, page
    Response: {total: 10, requests: [...]}

PUT /wp-json/shahi-legalops-suite/v1/dsr/admin/requests/{id}
    Body: {status: "completed", notes: "..."}
    Response: {success: true}
```

---

## 🧪 TESTING CHECKLIST

- [ ] Requests created with correct token and SLA deadline
- [ ] Verification email sent correctly
- [ ] Token validation works
- [ ] Status workflow transitions correctly
- [ ] All data sources detected automatically
- [ ] User data extracted accurately
- [ ] WooCommerce data extracted (if active)
- [ ] Form submissions extracted (for all form plugins)
- [ ] JSON export created and downloads
- [ ] CSV export created and downloads
- [ ] Export file expires after X days
- [ ] Admin dashboard shows all requests
- [ ] Filters work correctly
- [ ] SLA deadline highlighted if < 5 days
- [ ] REST API endpoints secured with nonces
- [ ] Rate limiting prevents spam
- [ ] Email templates render correctly

---

## 🔒 SECURITY CONSIDERATIONS

- [ ] Use secure random token for email verification (not predictable)
- [ ] Verify token before giving access to export
- [ ] Don't expose user data without verification
- [ ] Sanitize all form inputs
- [ ] Rate limit request submissions (1 per email per day)
- [ ] Rate limit export downloads
- [ ] Secure file storage (outside web root if possible)
- [ ] Delete exports after 7-14 days
- [ ] Log all DSR actions for audit trail
- [ ] Capability checks for admin functions

---

## 📊 PERFORMANCE CONSIDERATIONS

- [ ] Batch data collection for large sites
- [ ] Use pagination in export (don't load 10k rows at once)
- [ ] Implement async export generation (for large datasets)
- [ ] Cache data source detection
- [ ] Archive old completed requests (move to history)

---

## 🎯 DELIVERABLES

By end of Week 9:
- ✅ DSR request creation and verification
- ✅ Automatic data source detection
- ✅ Data export in JSON/CSV/XML formats
- ✅ Admin dashboard for request management
- ✅ SLA tracking with deadline visibility
- ✅ Public request form via shortcode
- ✅ REST API for frontend integration
- ✅ Email verification system
- ✅ Secure file download with expiration
- ✅ Audit logging of all DSR actions

---

## 📚 RELATED DOCUMENTATION

- Main Roadmap: `/v3docs/ROADMAP.md`
- Consent Module: `/v3docs/modules/01-CONSENT-IMPLEMENTATION.md`
- Feature List: `/DevDocs/SLOS/CF_FEATURES_v3.md`

---

**Version:** 1.0  
**Status:** Ready for Implementation
