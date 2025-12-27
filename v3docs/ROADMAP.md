# Shahi LegalOps Suite v3.0.1 - Implementation Roadmap & Guide

**Version:** 3.0.1  
**Last Updated:** December 19, 2025  
**Status:** Foundation Complete - Features in Roadmap  
**Timeline:** 18-24 weeks for full feature completion (or flexible subset)  
**Implementation Method:** AI Agents (no human coders)  

---

## ⚠️ CRITICAL: AI AGENT IMPLEMENTATION NOTES

**This project will be implemented by AI agents, NOT human developers.** This changes everything about how work is organized:

### Key Differences from Human Development
- ✅ **Tasks must be atomic** - Each task is completely independent and self-contained
- ✅ **No context carryover** - Each agent invocation starts with no memory of previous work
- ✅ **Explicit verification required** - Every task must include built-in testing/verification
- ✅ **State management critical** - Database state, file changes must be explicitly tracked
- ✅ **Rollback procedures essential** - Every change must be reversible if something fails
- ✅ **Test-driven completion** - Tasks are done only when tests pass, not when code is written
- ✅ **Error recovery documented** - What to do if a task fails halfway through
- ✅ **Detailed success criteria** - Vague "complete the feature" won't work - must be very specific

### How This Affects the Roadmap
1. **Tasks are smaller** - What might be "Week 3" for a human is 5-10 independent AI tasks
2. **More checkpoints** - Verification steps between major work blocks
3. **Better documentation** - Each task is fully self-documenting
4. **State tracking** - Database migrations, file creation must be tracked explicitly
5. **No assumptions** - Can't assume the AI will "figure out" how to do something
6. **Explicit order** - Some tasks MUST run before others (hard dependencies)

---

## 📋 EXECUTIVE SUMMARY

This document provides a realistic, detailed roadmap for completing the Shahi LegalOps Suite via AI agents. Currently, **3 of 10 modules are complete** (Accessibility Scanner, Security, Dashboard UI). This roadmap covers building the remaining **7 modules** in a logical, dependency-aware sequence.

**Total Effort:** ~650-850 developer hours across 20-24 weeks (enhanced with 2026 winning features)  
**Includes:** Google Consent Mode v2, IAB TCF, 40+ languages, advanced analytics, preference center, enterprise features  
**Implementation:** AI agents working on atomic tasks with explicit verification at each step

---

## 🎯 STRATEGIC APPROACH

### Decision Point: What to Build?

**Option A: Honest Positioning (6-8 weeks)**
- Focus on Accessibility Scanner as primary product
- Position as "WordPress Accessibility Compliance Pro"
- Deliver: Scanner + Basic framework + 1-2 bonus modules
- Market fit: WCAG compliance specialists, law firms

**Option B: Full Commitment (18 weeks)**
- Build all 10 modules as promised in original vision
- Create "all-in-one compliance platform"
- Better market differentiation, higher pricing potential
- Requires sustained resource commitment

**Option C: Selective Build (10-12 weeks)**
- Build Consent + DSR + Documents + Analytics
- Skip Vendor Management, Forms Compliance, Cookie Inventory
- Strong GDPR focus, lighter on peripheral features
- Best ROI for typical WordPress sites

**Recommended:** Option C (Selective Build) - gets you to CodeCanyon-ready in 10-12 weeks with 4 must-have modules.

---

## 📦 DEPENDENCY MAP

```
Consent Management (4 weeks)
    ↓ (provides consent data)
DSR Portal (3 weeks) ← Also depends on: User data sources
Legal Documents (2 weeks) ← Needs: Consent awareness
Analytics Dashboard (2 weeks) ← Needs: Data from above
    ↓
Forms Compliance (2 weeks) ← Optional, depends on consent
Cookie Inventory (2 weeks) ← Optional, supports legal docs
Vendor Management (2 weeks) ← Optional, enterprise feature
```

**Critical Path (Must Build First):**
1. Consent Management
2. DSR Portal
3. Legal Documents Generator

---

## ⏱️ DETAILED TIMELINE (20-24 Weeks) - AI AGENT TASKS

### CRITICAL: Understanding AI Agent Task Structure

**Each "week" is broken into 3-5 atomic AI tasks.** Example:
- **TASK 1.1.1:** Create database migration file for consent table
- **TASK 1.1.2:** Test migration runs without errors
- **TASK 1.1.3:** Verify table structure matches spec
- **TASK 1.2.1:** Create ConsentRepository class
- **TASK 1.2.2:** Test repository CRUD operations

**Each task includes:**
1. **Input State** - What must exist before this task runs
2. **Actions** - Exactly what to do (code to write, files to create)
3. **Output State** - What will exist after task completes
4. **Verification** - How to test that the task worked
5. **Rollback** - How to undo if something goes wrong
6. **Dependencies** - What other tasks must finish first

---

### PHASE 1: ASSESSMENT & CLEANUP (2 Weeks = 8 Tasks)

#### TASK 1.1: Code Audit & Assessment
**Purpose:** Understand what exists, what's needed, plan work  
**Effort:** 8-10 hours  
**Input State:** Repository cloned, composer dependencies installed  
**Actions:**
- [ ] Count actual lines of code in `/includes/Modules/` (not docs)
- [ ] List all existing database tables (run all migrations)
- [ ] List all existing REST API endpoints (grep for register_rest_route)
- [ ] Audit codebase against ROADMAP claims
- [ ] Create GitHub issues for each planned module task
- [ ] Create project board with all tasks ordered by dependency
**Output State:** GitHub board with 50+ tasks organized, ready to start  
**Verification:** 
- [ ] GitHub project board visible and organized
- [ ] All tasks have clear description and acceptance criteria
- [ ] No task has ambiguous requirements
- [ ] Tasks marked with dependencies (GitHub "blocking" feature)
**Rollback:** Delete GitHub board, no code changes needed  

#### TASK 1.2: Database Schema Design & Migration Files
**Purpose:** Create all migration files for new modules  
**Effort:** 6-8 hours  
**Input State:** Task 1.1 complete  
**Dependencies:** TASK 1.1  
**Actions:**
- [ ] Create 7 migration files in `includes/Database/Migrations/`
- [ ] Implement migration_2025_12_20_consent_table()
- [ ] Implement migration_2025_12_20_dsr_requests_table()
- [ ] Implement migration_2025_12_20_documents_table()
- [ ] Implement migration_2025_12_20_trackers_table()
- [ ] Implement migration_2025_12_20_vendors_table()
- [ ] Implement migration_2025_12_20_form_issues_table()
- [ ] Reference: See /v3docs/database/SCHEMA-ACTUAL.md for exact column specs
**Output State:** 7 new migration files created (not yet run)  
**Verification:**
```bash
# Run each migration individually
php -r "require 'includes/Database/Migrations/migration_2025_12_20_consent_table.php';"
# Should complete without errors
# Verify table exists: SELECT * FROM wp_complyflow_consent LIMIT 0;
```
**Rollback:** Delete migration files, drop created tables via SQL  

#### TASK 1.3: Run Database Migrations
**Purpose:** Actually create the tables in WordPress database  
**Effort:** 2-3 hours  
**Input State:** Task 1.2 complete, migration files created  
**Dependencies:** TASK 1.2  
**Actions:**
- [ ] Create activation script or use existing activation mechanism
- [ ] Call each migration function in order
- [ ] Log which migrations succeeded
- [ ] Verify no errors on WP_DEBUG
**Output State:** 7 new tables created in WordPress database  
**Verification:**
```sql
SHOW TABLES LIKE 'wp_complyflow_%';
-- Should show 7 tables
DESC wp_complyflow_consent;
-- Verify columns match SCHEMA-ACTUAL.md
```
**Rollback:** DROP TABLE all 7 complyflow tables  

#### TASK 1.4: Create Base Repository Classes
**Purpose:** Create base CRUD repositories for all new entities  
**Effort:** 6-8 hours  
**Input State:** Task 1.3 complete (tables exist)  
**Dependencies:** TASK 1.3  
**Actions:**
- [ ] Create `includes/Repositories/ConsentRepository.php`
- [ ] Create `includes/Repositories/DSRRequestRepository.php`
- [ ] Create `includes/Repositories/DocumentRepository.php`
- [ ] Create `includes/Repositories/TrackerRepository.php`
- [ ] Create `includes/Repositories/VendorRepository.php`
- [ ] Implement standard methods: create(), update(), delete(), findById(), findAll()
- [ ] Add proper sanitization and error handling
- [ ] All repositories inherit from `BaseRepository`
**Output State:** 5 repository classes, all functional  
**Verification:**
```php
// Create test record
$repo = new ConsentRepository();
$id = $repo->create(['type' => 'analytics', 'status' => 'pending']);
assert($id > 0);

// Read test record
$record = $repo->findById($id);
assert($record['type'] === 'analytics');

// Update test record
$repo->update($id, ['status' => 'accepted']);
$record = $repo->findById($id);
assert($record['status'] === 'accepted');

// Delete test record
$repo->delete($id);
$record = $repo->findById($id);
assert($record === null);
```
**Rollback:** Delete repository files (no database changes)  

#### TASK 1.5: Create Base Module Classes
**Purpose:** Set up scaffolding for Consent, DSR, Legal Docs modules  
**Effort:** 4-6 hours  
**Input State:** Task 1.4 complete  
**Dependencies:** TASK 1.4  
**Actions:**
- [ ] Create `includes/Modules/Consent/ConsentModule.php` extending `Module` base
- [ ] Create `includes/Modules/DSR/DSRModule.php` extending `Module` base
- [ ] Create `includes/Modules/LegalDocs/LegalDocsModule.php` extending `Module` base
- [ ] Implement activate(), deactivate(), initialize() methods
- [ ] Implement get_status() method (returns 'not_configured', 'configured', 'active')
- [ ] Follow existing Module pattern from AccessibilityScanner
**Output State:** 3 new module classes, all registered but empty  
**Verification:**
```php
$modules = ModuleManager::get_all_modules();
assert(in_array('Consent', array_keys($modules)));
assert(in_array('DSR', array_keys($modules)));
assert(in_array('LegalDocs', array_keys($modules)));

$consent = ModuleManager::get_module('Consent');
assert($consent->get_status() === 'not_configured');
```
**Rollback:** Delete 3 module files  

#### TASK 1.6: Create Base REST API Scaffolds
**Purpose:** Set up empty REST endpoints that will be filled in later  
**Effort:** 4-5 hours  
**Input State:** Task 1.5 complete  
**Dependencies:** TASK 1.5  
**Actions:**
- [ ] Create `includes/API/ConsentController.php`
- [ ] Create `includes/API/DSRController.php`
- [ ] Create `includes/API/LegalDocsController.php`
- [ ] Register basic routes (GET /complyflow/consent, POST /complyflow/consent, etc.)
- [ ] Implement permission checks
- [ ] Add rate limiting framework
**Output State:** 3 REST API controllers, all endpoints registered  
**Verification:**
```bash
curl http://localhost/wp-json/complyflow/v1/consent
# Should return 200 with empty array or error message
# Should NOT return 404 (endpoint must exist)
```
**Rollback:** Delete 3 controller files and deregister routes  

#### TASK 1.7: Set Up Configuration & Settings Pages
**Purpose:** Create admin settings framework for new modules  
**Effort:** 5-6 hours  
**Input State:** Task 1.6 complete  
**Dependencies:** TASK 1.6  
**Actions:**
- [ ] Create settings pages for Consent, DSR, Legal Docs in admin
- [ ] Implement save/load settings to WordPress options
- [ ] Create basic form UI for each module
- [ ] Add validation for settings
- [ ] Store defaults in code (refer to module guides)
**Output State:** 3 settings pages, all saving to WordPress options  
**Verification:**
```php
// Settings saved correctly
update_option('complyflow_consent_settings', ['banner_position' => 'bottom']);
$settings = get_option('complyflow_consent_settings');
assert($settings['banner_position'] === 'bottom');

// Visible in admin
// Admin page should show: Dashboard > ComplyFlow > Consent Settings
```
**Rollback:** Delete settings pages, clear options table entries  

#### TASK 1.8: Verification & Checkpoint
**Purpose:** Ensure Phase 1 is complete and correct before moving to Phase 2  
**Effort:** 2 hours  
**Input State:** Tasks 1.1-1.7 complete  
**Dependencies:** TASK 1.1 through TASK 1.7  
**Actions:**
- [ ] Run full test suite (if exists) - no new failures
- [ ] Verify WP_DEBUG has no new errors/warnings
- [ ] Test plugin activation/deactivation cycle
- [ ] Verify database tables persist after deactivation/reactivation
- [ ] Confirm all module classes load without errors
- [ ] Test all REST endpoints respond (even if empty)
- [ ] Create summary report of Phase 1 completion
**Output State:** Phase 1 verified complete, ready for Phase 2  
**Verification:**
```bash
# Plugin activates without errors
wp plugin activate shahi-legalops-suite

# No PHP errors/warnings
grep -r "PHP [Warning|Error]" error_log

# All tables exist
wp db tables | grep complyflow_

# All modules registered
wp eval 'echo count(\ComplyFlow\Core\ModuleManager::get_all_modules());'
# Should output: 6 (or higher if more modules existed)
```
**Rollback:** If any test fails, previous tasks get rolled back, dependencies re-verified  

---

### PHASE 2: CONSENT MANAGEMENT MODULE (5-6 Weeks = 15-18 Tasks)

#### TASK 2.1: Consent Record Model & Repository
**Purpose:** Create database abstraction for consent tracking  
**Effort:** 6-8 hours  
**Input State:** Phase 1 complete, tables exist  
**Dependencies:** TASK 1.3, TASK 1.4  
**Actions:**
- [ ] Create `includes/Modules/Consent/Models/ConsentRecord.php`
- [ ] Implement properties: id, user_id, type, status, ip_hash, created_at, updated_at, metadata
- [ ] Add validation: type must be in (necessary, functional, analytics, marketing, personalization)
- [ ] Add validation: status must be in (pending, accepted, rejected, withdrawn)
- [ ] Implement toArray(), fromArray() methods
- [ ] Add getMetadata() method for flexible data storage
- [ ] Implement repository: `includes/Modules/Consent/Repositories/ConsentRepository.php`
- [ ] Add methods: save(), findByUserId(), findByType(), findRecent(), deleteOldRecords()
**Output State:** ConsentRecord model and repository, fully functional  
**Verification:**
```php
$record = new ConsentRecord([
    'user_id' => 123,
    'type' => 'analytics',
    'status' => 'accepted',
    'ip_hash' => hash('sha256', $_SERVER['REMOTE_ADDR'])
]);
$repo = new ConsentRepository();
$id = $repo->save($record);
assert($id > 0);

$fetched = $repo->findById($id);
assert($fetched->type === 'analytics');
assert($fetched->user_id === 123);
```
**Rollback:** Delete model and repository files  

#### TASK 2.2: Banner Configuration & Validation
**Purpose:** Store and validate banner settings (position, text, colors, etc.)  
**Effort:** 4-6 hours  
**Input State:** Task 2.1 complete  
**Dependencies:** TASK 2.1  
**Actions:**
- [ ] Create `includes/Modules/Consent/Models/Banner.php`
- [ ] Implement properties from spec: position, text_title, text_description, button_text_accept, button_text_reject, colors
- [ ] Add validation: position must be in (top, bottom, left, right, modal, corner)
- [ ] Store in WordPress options: 'complyflow_consent_banner'
- [ ] Implement Banner::load(), Banner::save() static methods
- [ ] Implement sensible defaults (bottom banner, English text)
**Output State:** Banner model with persist-to-options functionality  
**Verification:**
```php
$banner = new Banner([
    'position' => 'bottom',
    'text_title' => 'We use cookies',
    'button_text_accept' => 'Accept All'
]);
$banner->save();

$loaded = Banner::load();
assert($loaded->position === 'bottom');
assert($loaded->text_title === 'We use cookies');
```
**Rollback:** Delete Banner file, clear WordPress option  

#### TASK 2.3: Script Blocking Service (Phase 1)
**Purpose:** Detect and block scripts before consent  
**Effort:** 8-10 hours  
**Input State:** Task 2.2 complete  
**Dependencies:** TASK 2.2  
**Actions:**
- [ ] Create `includes/Modules/Consent/Services/ScriptBlocker.php`
- [ ] Implement script detection: Intercept wp_enqueue_script() calls
- [ ] Map 20+ known platforms to categories: GA → analytics, FB pixel → marketing, etc.
- [ ] Implement blocking mechanism: Rewrite inline scripts before DOM render
- [ ] Create allow-list for always-allowed scripts (jQuery, Bootstrap, etc.)
- [ ] Add custom script blocking (admin can add script URLs to block list)
- [ ] Store blocked scripts list in WordPress option
- [ ] Implement unblocking: Trigger JavaScript event when consent changes
**Output State:** Scripts properly blocked/unblocked based on consent  
**Verification:**
```javascript
// Front-end test - GA script should be blocked
// In HTML, look for: <script data-analytics="gtag"></script>
// Not: <script src="https://www.googletagmanager.com/gtag/js..."></script>

// Consent given
window.dispatchEvent(new CustomEvent('complyflow_consent_changed', {
    detail: { analytics: true }
}));

// GA script should now be active (data attribute removed, script executes)
```
**Rollback:** Delete ScriptBlocker file, remove script interception hooks  

#### TASK 2.4: Google Consent Mode v2 Service
**Purpose:** Implement Google Consent Mode signaling (critical for 2026)  
**Effort:** 8-10 hours  
**Input State:** Task 2.3 complete  
**Dependencies:** TASK 2.3  
**Actions:**
- [ ] Create `includes/Modules/Consent/Services/GoogleConsentModeService.php`
- [ ] Implement signals: ad_storage, analytics_storage, ad_user_data, ad_personalization
- [ ] Add Google Tag Manager integration
- [ ] Implement default state management (what state before banner shown)
- [ ] Handle re-consent: update Google with new consent states
- [ ] Inject gtag.js consent command at page load
- [ ] Log all consent signals sent to Google (for audit trail)
- [ ] Add settings for default states (per signal)
**Output State:** Google Consent Mode v2 fully functional  
**Verification:**
```javascript
// Check that gtag.config includes consent settings
// window.gtag should exist
// gtag('consent', 'default', {...}) should have been called
// gtag('consent', 'update', {...}) should be called when user changes consent

// In Google Analytics, check Data > Data Retention settings
// Should show "Consent Mode" if properly configured
```
**Rollback:** Delete GoogleConsentModeService file, remove gtag injections  

#### TASK 2.5: Preference Center UI
**Purpose:** Let users see and control individual cookie categories  
**Effort:** 10-12 hours  
**Input State:** Task 2.4 complete  
**Dependencies:** TASK 2.4  
**Actions:**
- [ ] Create `includes/Modules/Consent/Shortcodes/PreferenceCenterShortcode.php`
- [ ] Build UI: Show each category with toggle switch
- [ ] Show cookie descriptions for each category
- [ ] Show list of vendors/scripts in each category
- [ ] Add "Save Preferences" button
- [ ] Save preferences to ConsentRecord
- [ ] Trigger consent change event for script blocker
- [ ] Mobile responsive design
- [ ] Accessibility: WCAG 2.2 AA compliant
- [ ] Implement via shortcode: `[complyflow_preferences]`
**Output State:** Shortcode usable on any page, fully functional  
**Verification:**
```php
// Create test page with shortcode
// Add to homepage: [complyflow_preferences]

// Visit homepage, check:
// - Toggles visible and functional
// - Saving preferences persists
// - Consents written to database
// - Scripts blocked/unblocked per preferences
```
**Rollback:** Delete shortcode file  

#### TASK 2.6: Banner Display & Interaction
**Purpose:** Show banner to users and handle consent/rejection  
**Effort:** 10-12 hours  
**Input State:** Task 2.5 complete  
**Dependencies:** TASK 2.5  
**Actions:**
- [ ] Create `includes/Modules/Consent/Frontend/BannerRenderer.php`
- [ ] Hook into wp_footer to inject banner HTML/CSS
- [ ] Load banner position and text from settings (Task 2.2)
- [ ] Implement "Accept All" button - set all categories to accepted
- [ ] Implement "Reject All" button - set only necessary to accepted
- [ ] Implement "Settings" button - show preference center
- [ ] Handle cookie storage: Save consent choice to localStorage/cookie
- [ ] Respect prior consent: Don't show banner if already consented
- [ ] Implement banner styling per position (top, bottom, modal, etc.)
- [ ] Mobile responsive and touch-friendly
**Output State:** Banner displays on all pages, handles all user actions  
**Verification:**
```javascript
// Test on homepage (logged out):
// - Banner should appear within 1 second
// - "Accept All" button should set all consents
// - localStorage['complyflow_consent'] should exist
// - Banner should NOT appear on next page load

// Test after clearing localStorage:
// - Banner should appear again
```
**Rollback:** Delete BannerRenderer file  

#### TASK 2.7: Geo-Targeting Service
**Purpose:** Show region-specific banner based on user location  
**Effort:** 8-10 hours  
**Input State:** Task 2.6 complete  
**Dependencies:** TASK 2.6  
**Actions:**
- [ ] Create `includes/Modules/Consent/Services/GeoTargetingService.php`
- [ ] Implement region detection via IP (use MaxMind GeoIP2 Lite or similar)
- [ ] Map regions: EU, UK, CA, BR, SA, default
- [ ] Load region-specific banner text/options from settings
- [ ] Different required consents per region: EU=strict, CA=consent, BR=consent, default=opt-out OK
- [ ] Allow manual region override (user can select their region)
- [ ] Cache geolocation result (1 day) to avoid repeat lookups
- [ ] Implement fallback: If geo lookup fails, show default region banner
**Output State:** Banner text/options change based on detected region  
**Verification:**
```php
// Test with different IPs:
// - EU IP (e.g., 178.32.4.1) → should show GDPR-strict banner
// - US IP (e.g., 8.8.8.8) → should show CCPA-style banner
// - Unknown IP → should show default banner

// Test manual override:
// - User selects "Canada" from dropdown
// - Banner changes to PIPEDA rules
// - Preference saved in cookie
```
**Rollback:** Delete GeoTargetingService file  

#### TASK 2.8: Audit Trail & Logging
**Purpose:** Track all consent actions for compliance auditing  
**Effort:** 6-8 hours  
**Input State:** Task 2.7 complete  
**Dependencies:** TASK 2.7  
**Actions:**
- [ ] Create `includes/Modules/Consent/Services/ConsentAuditor.php`
- [ ] Log all consent actions: user_id, action (accept/reject/withdraw), timestamp, ip_hash, categories
- [ ] Store in consentrecords table with metadata
- [ ] Create audit log viewer in admin: Dashboard > ComplyFlow > Audit Trail
- [ ] Add filters: date range, user, action type
- [ ] Add export: CSV export of audit log
- [ ] Implement data retention: Delete logs older than 1 year (configurable)
- [ ] GDPR compliance: IP addresses hashed (SHA256), can't reverse
**Output State:** Comprehensive audit trail accessible to admins  
**Verification:**
```php
// Make a consent choice on site
// Admin: Go to Dashboard > ComplyFlow > Audit Trail
// Should see: [timestamp] User [IP] accepted [categories]

// Export CSV, verify contents
```
**Rollback:** Delete ConsentAuditor file  

#### TASK 2.9: Analytics Dashboard
**Purpose:** Show consent metrics to admins  
**Effort:** 8-10 hours  
**Input State:** Task 2.8 complete  
**Dependencies:** TASK 2.8  
**Actions:**
- [ ] Create `includes/Modules/Consent/Admin/ConsentAnalytics.php`
- [ ] Build dashboard widget: Consent acceptance rate (%)
- [ ] Show breakdown by category: % who accepted analytics, marketing, etc.
- [ ] Show trends: Chart of acceptance rate over time (30 days)
- [ ] Show by region: EU vs CA vs BR acceptance rates
- [ ] Show by device: Mobile vs Desktop breakdown
- [ ] Add date range filter
- [ ] Implement caching: Recalculate metrics once per hour
- [ ] Export: PDF/CSV export of report
**Output State:** Dashboard widget showing consent analytics  
**Verification:**
```php
// Check admin dashboard
// Widget should show:
// - "Overall Acceptance: 67%"
// - Chart showing trend (up/down/stable)
// - Category breakdown
// - Regional breakdown
```
**Rollback:** Delete ConsentAnalytics file  

#### TASK 2.10: REST API Endpoints (Full Implementation)
**Purpose:** Enable programmatic access to consent data  
**Effort:** 8-10 hours  
**Input State:** Task 2.9 complete  
**Dependencies:** TASK 2.9  
**Actions:**
- [ ] Implement GET `/complyflow/v1/consent/status` - Current user's consent status
- [ ] Implement POST `/complyflow/v1/consent/accept` - Accept all/specific categories
- [ ] Implement POST `/complyflow/v1/consent/reject` - Reject consent
- [ ] Implement POST `/complyflow/v1/consent/withdraw` - Withdraw consent
- [ ] Implement GET `/complyflow/v1/consent/analytics` - Get metrics (admin only)
- [ ] Add rate limiting: Max 60 requests/minute per IP
- [ ] Add nonce verification for POST requests
- [ ] Add capability checks: Only admins can access certain endpoints
- [ ] Document all endpoints (comments in code)
**Output State:** 5+ REST endpoints, fully functional and documented  
**Verification:**
```bash
curl http://localhost/wp-json/complyflow/v1/consent/status
# Should return: {"analytics": false, "marketing": false, "necessary": true}

curl -X POST http://localhost/wp-json/complyflow/v1/consent/accept \
  -H "Content-Type: application/json" \
  -d '{"categories": ["analytics", "marketing"]}'
# Should return: {"success": true, "accepted": ["analytics", "marketing"]}
```
**Rollback:** Delete API endpoint implementations  

#### TASK 2.11: Language Support & i18n
**Purpose:** Support 40+ languages in banner and preference center  
**Effort:** 8-10 hours  
**Input State:** Task 2.10 complete  
**Dependencies:** TASK 2.10  
**Actions:**
- [ ] Use WordPress i18n framework (load_plugin_textdomain, esc_html__)
- [ ] Extract all user-facing strings to POTFILES
- [ ] Generate POT file: `languages/shahi-complyflow.pot`
- [ ] Implement language detection: Use WordPress locale
- [ ] Add settings: Admin can override language
- [ ] Load appropriate translation file based on language
- [ ] Test with at least 5 languages (EN, ES, FR, DE, IT, PT, etc.)
- [ ] Use professional translations or translation API
**Output State:** Banner displays in user's language, 40+ languages supported  
**Verification:**
```php
// Change WordPress language to Spanish
// Visit homepage, banner should appear in Spanish

// Change to French
// Banner should appear in French

// Add translation via PoEdit or similar
// Verify in browser
```
**Rollback:** Delete i18n files, revert to English-only  

#### TASK 2.12: Security & Data Protection
**Purpose:** Ensure consent data is secure and compliant  
**Effort:** 6-8 hours  
**Input State:** Task 2.11 complete  
**Dependencies:** TASK 2.11  
**Actions:**
- [ ] Implement input sanitization: All user inputs to consentrecords
- [ ] SQL prepared statements: All database queries
- [ ] Nonce verification: All forms
- [ ] Capability checks: Only admins access admin pages
- [ ] Output escaping: All echoed data
- [ ] HTTPS enforcement: Warn if site not using HTTPS
- [ ] Rate limiting: Already done in API endpoints
- [ ] Data deletion: Implement consent record deletion (older than X days, user request)
- [ ] Create security audit checklist (comment in code)
**Output State:** Consent module fully secure  
**Verification:**
```php
// Try SQL injection via API:
curl "http://localhost/wp-json/complyflow/v1/consent?user_id=1' OR '1'='1'"
// Should return validation error, not database results

// Try accessing admin page without logged-in:
// Should redirect to login
```
**Rollback:** Revert security changes (but unlikely needed)  

#### TASK 2.13: Testing & QA
**Purpose:** Ensure module works correctly across browsers and devices  
**Effort:** 10-12 hours  
**Input State:** Task 2.12 complete  
**Dependencies:** TASK 2.12  
**Actions:**
- [ ] Unit tests: Test repository CRUD, service methods (8+ tests)
- [ ] Integration tests: Test banner + script blocker interaction (5+ tests)
- [ ] Browser tests: Chrome, Firefox, Safari, Edge (basic functionality)
- [ ] Mobile tests: iPhone, Android (banner responsive, touch works)
- [ ] Accessibility tests: Keyboard navigation, screen reader (WCAG AA)
- [ ] Performance tests: Banner loads in <100ms, no layout shift
- [ ] Security tests: SQL injection, XSS, CSRF attempts
- [ ] Data tests: Consent records saved correctly, audit trail complete
**Output State:** Test report, zero critical bugs, all tests passing  
**Verification:**
```bash
# Run test suite
phpunit tests/ConsentModuleTest.php

# Should output: OK (15 tests, 0 failures)

# Manual testing checklist
# ✓ Banner appears on homepage
# ✓ Accept All works
# ✓ Reject All works
# ✓ Settings button shows preferences
# ✓ Preferences save
# ✓ GA scripts block before consent
# ✓ GA scripts execute after consent
# ✓ Banner doesn't appear after consent
# ✓ Mobile responsive
# ✓ Keyboard navigable
```
**Rollback:** Fix bugs found (no rollback of functionality)  

#### TASK 2.14: Documentation & Code Comments
**Purpose:** Document module so future developers (or AI) can understand it  
**Effort:** 4-6 hours  
**Input State:** Task 2.13 complete  
**Dependencies:** TASK 2.13  
**Actions:**
- [ ] Add PHPDoc comments to all classes and methods
- [ ] Document database schema: Comments in migrations
- [ ] Document REST API: Comments on all endpoints
- [ ] Create module README: `includes/Modules/Consent/README.md`
- [ ] Document settings: What each setting does, where stored
- [ ] Add inline comments for complex logic
- [ ] Document hooks: What hooks module provides, what hooks it uses
- [ ] Document configuration: How to customize banner, scripts to block, etc.
**Output State:** Comprehensive documentation, easy to understand and maintain  
**Verification:**
```php
// Open any class file
// Should see PHPDoc on all public methods
// Should understand what class does from comments
```
**Rollback:** Delete documentation (code still works)  

#### TASK 2.15: Phase 2 Checkpoint & Verification
**Purpose:** Ensure Consent module is production-ready  
**Effort:** 3-4 hours  
**Input State:** Task 2.14 complete  
**Dependencies:** All Phase 2 tasks  
**Actions:**
- [ ] Run full test suite for Consent module
- [ ] Verify no WP_DEBUG errors/warnings
- [ ] Test on production-like environment (if possible)
- [ ] Verify database migrations stable (test activate/deactivate cycle)
- [ ] Verify settings persist across updates
- [ ] Create Phase 2 completion report
- [ ] List any known limitations
- [ ] Confirm ready to proceed with Phase 3 (DSR)
**Output State:** Phase 2 (Consent) module production-ready  
**Verification:**
```bash
# Comprehensive checklist
# ✓ Banner displays on homepage
# ✓ Consent saved to database
# ✓ Scripts blocked/unblocked correctly
# ✓ Google Consent Mode signals sent
# ✓ Audit log created for each consent action
# ✓ Analytics dashboard shows correct metrics
# ✓ REST API endpoints all working
# ✓ Security audit complete, no vulnerabilities
# ✓ Mobile responsive
# ✓ Multi-language support working
# ✓ No WP_DEBUG errors
# ✓ Performance acceptable (<100ms banner load)

# Create final report
# Phase 2 Complete: Consent Module
# Status: Production Ready
# Known Limitations: None
# Ready for Phase 3: YES
```
**Rollback:** If critical issues found, debug and fix (no rollback of Phase 2)  

---

### PHASE 3: DSR PORTAL MODULE (3-4 Weeks = 12-15 Tasks)

*[Similar structure to Phase 2, but tasks specific to DSR Portal implementation]*

**Key Tasks (outline):**
- TASK 3.1: DSR Request Model & Repository
- TASK 3.2: 7 Request Types Implementation
- TASK 3.3: Email Verification Service
- TASK 3.4: Data Detection & Discovery Service
- TASK 3.5: Multi-Format Export (PDF, JSON, CSV, XML, XLSX)
- TASK 3.6: Request Management Admin UI
- TASK 3.7: SLA Tracking & Notifications
- TASK 3.8: REST API Endpoints
- TASK 3.9: Public Request Form Shortcode
- TASK 3.10: Security & Data Protection
- TASK 3.11: Testing & QA
- TASK 3.12: Documentation
- TASK 3.13: Phase 3 Checkpoint

*[Detailed tasks to be added by following Phase 2 pattern]*

---

### PHASE 4: LEGAL DOCUMENTS GENERATOR (2-3 Weeks = 10-12 Tasks)

*[Similar structure to Phase 3, tasks for Legal Docs]*

**Key Tasks (outline):**
- TASK 4.1: Document Model & Repository
- TASK 4.2: AI-Powered Questionnaire
- TASK 4.3: Auto-Detection of Integrations (WooCommerce, Forms, Analytics, etc.)
- TASK 4.4: Document Generation Logic (6 document types)
- TASK 4.5: Version Control & Diff Viewer
- TASK 4.6: Publishing Options (page, shortcode, footer, etc.)
- TASK 4.7: Compliance Verification
- TASK 4.8: REST API Endpoints
- TASK 4.9: Multi-Language Support (20+)
- TASK 4.10: Testing & QA
- TASK 4.11: Documentation
- TASK 4.12: Phase 4 Checkpoint

*[Detailed tasks to be added by following earlier patterns]*

---

### PHASE 5+: ANALYTICS, OPTIONAL MODULES, POLISH

*[Outline only for brevity; follow same pattern for each phase]*

Each module follows the same pattern:
1. Model & Repository setup
2. Core business logic
3. Admin UI (if applicable)
4. Public-facing features (if applicable)
5. REST API
6. Security hardening
7. Testing & QA
8. Documentation
9. Phase checkpoint

---

## 🤖 INSTRUCTIONS FOR AI AGENTS

### Task Workflow
1. **Read This Section First** - Understand how to work with this roadmap
2. **Find Your Task** - Look up your assigned task number (e.g., TASK 2.5)
3. **Check Dependencies** - Verify all prerequisite tasks are complete
4. **Review Input State** - Ensure the required preconditions exist
5. **Execute Actions** - Follow the exact steps listed
6. **Verify Output** - Run verification checks to confirm success
7. **Document Completion** - Mark task complete with results
8. **Report Issues** - If verification fails, follow rollback steps

### Critical AI-Specific Guidelines

#### 1. NO ASSUMPTIONS
- Don't assume you know how something works - read the code first
- Don't assume previous tasks completed correctly - verify them
- Don't assume you understand the architecture - review existing modules
- Every assumption that's wrong costs 2-3 hours of debugging

#### 2. ATOMIC TASKS ONLY
- Each task should take 2-12 hours and be fully independent
- If a task depends on another task, it's listed in Dependencies
- Do NOT combine multiple tasks into one effort
- Do NOT skip intermediate steps to "save time"
- One task = one PR = one code review (conceptually)

#### 3. VERIFICATION IS MANDATORY
- Every task has a Verification section - follow it exactly
- If verification fails, don't proceed - follow rollback and retry
- Document what failed: "Database query returned empty set" is good
- Take screenshots of verification results for future reference

#### 4. STATE MANAGEMENT
- Each task starts fresh - no memory of previous tasks
- Review what's in the repository NOW (don't assume it's what you left)
- Check git status before starting: `git status`
- Commit your changes: `git add . && git commit -m "TASK X.Y: Description"`
- This creates a rollback point if something goes wrong

#### 5. ROLLBACK PROCEDURES
- Every task has a Rollback section
- If verification fails at any point: STOP and follow Rollback
- Then review the task description again
- Identify what went wrong
- Only then retry the task

#### 6. CODE QUALITY REQUIREMENTS
- Follow existing code patterns (see AccessibilityScanner, Security modules)
- Use Repository pattern for database access
- Use WordPress hooks and filters, not direct DOM manipulation
- Add PHPDoc comments on all public methods
- Use WordPress sanitization/escaping (sanitize_text_field, esc_html, etc.)
- Use WordPress prepared statements for all SQL (wpdb->prepare)

#### 7. TESTING REQUIREMENTS
- Write unit tests for all business logic
- Write integration tests for database operations
- Test with WP_DEBUG = true (no errors/warnings)
- Test on mobile (responsive design required)
- Test with accessibility tools (WCAG AA minimum)

#### 8. DOCUMENTATION REQUIREMENTS
- Add comments to all classes and methods (PHPDoc format)
- Document all database changes (in migration files)
- Document all REST API endpoints (in code comments)
- Create/update README.md files for new modules

#### 9. SECURITY REQUIREMENTS
- Sanitize ALL user inputs: `sanitize_text_field()`, `intval()`, etc.
- Escape ALL output: `esc_html()`, `esc_attr()`, `esc_url()`, etc.
- Use prepared statements: `$wpdb->prepare()` for all SQL
- Verify nonces on all forms: `check_admin_referer()`
- Check capabilities: `current_user_can('manage_options')`
- Never trust $_GET, $_POST, $_SERVER - always sanitize

#### 10. COMMUNICATION WITH OTHER AGENTS
- All state is stored in the repository (git)
- Use git commits to communicate progress
- Use consistent commit messages: "TASK X.Y: [Description]"
- Use git tags to mark phase completions: `git tag -a v1.0-phase1 -m "Phase 1 Complete"`
- Use issue descriptions (GitHub) to track blockers

### How to Handle Common Problems

#### Problem: "Verification fails - test returns error"
**Solution:**
1. Read the error message carefully - it contains the answer
2. Review the code you just wrote - find the bug
3. Check the Input State - did the prerequisite really complete?
4. Follow the Rollback procedure
5. Fix the issue
6. Run verification again

#### Problem: "Task dependencies list something that's not done"
**Solution:**
1. Don't proceed with this task
2. Go back and complete the dependent task first
3. Return to this task only after dependency is verified complete

#### Problem: "I don't understand what this task wants me to build"
**Solution:**
1. Read the "Feature Specification" section in related module guide
2. Look at similar code in AccessibilityScanner or Security modules
3. Check the "Output State" - that's what must exist after the task
4. Check the "Verification" - that's what the output must do

#### Problem: "The code I wrote doesn't match the existing patterns"
**Solution:**
1. Review the existing Module implementation (AccessibilityScanner)
2. Match the directory structure exactly
3. Match the naming conventions exactly
4. Match the method signatures exactly
5. Re-write your code to match

#### Problem: "I made a mistake and broke something"
**Solution:**
1. Don't panic - use git to rollback
2. `git diff` to see what changed
3. `git reset --hard` to revert to last commit
4. Review what went wrong
5. Re-do the task more carefully

### Success Criteria for Task Completion

A task is complete when:
- [ ] All actions in the "Actions" section are done
- [ ] All verification checks pass
- [ ] Code follows existing patterns
- [ ] No WP_DEBUG errors or warnings
- [ ] Comments added (PHPDoc, inline)
- [ ] Changes committed to git
- [ ] Task marked complete in GitHub project

### Example: Completing Task 2.1

**Your Assignment:** Complete TASK 2.1 (Consent Record Model & Repository)

**Step 1: Check Dependencies**
```bash
# Make sure Phase 1 is done
git log --oneline | grep "TASK 1"
# Should see TASK 1.1 through TASK 1.8 in commits
```

**Step 2: Review Input State**
```bash
# Verify database tables exist
wp db tables | grep complyflow_

# Should include: wp_complyflow_consent
```

**Step 3: Read the Task Description**
- TASK 2.1: Create ConsentRecord model and ConsentRepository
- Study the actions, output state, and verification sections

**Step 4: Implement the Code**
- Create `includes/Modules/Consent/Models/ConsentRecord.php`
- Create `includes/Modules/Consent/Repositories/ConsentRepository.php`
- Follow the patterns from existing code

**Step 5: Run Verification**
```php
// Copy the verification code into a test file and run it
// If it passes, move to Step 6
// If it fails, debug and retry
```

**Step 6: Commit to Git**
```bash
git add includes/Modules/Consent/
git commit -m "TASK 2.1: Implement ConsentRecord model and repository"
git push origin main
```

**Step 7: Update GitHub Project**
- Mark TASK 2.1 as Complete
- Add comment: "ConsentRecord model + repository created. Verification passed."
- Link to your git commit

**Step 8: Proceed to TASK 2.2**
- Only if TASK 2.1 verification passed
- Only after confirming no blockers

### When to Ask for Help

If ANY of the following occur, STOP and ask for help:
- Verification fails and you don't know why
- Code doesn't match existing patterns and you're unsure how to fix
- A dependency didn't complete correctly
- You're blocked waiting for external information
- A requirement conflicts with another requirement
- You're unsure about the design or architecture

**How to Ask:** Create a GitHub issue describing:
1. Which task you're on
2. What's failing
3. What you've tried
4. What error messages you're seeing
5. Relevant code snippets

---

## 📊 OVERALL EFFORT BREAKDOWN (AI-AGENT OPTIMIZED)

| Phase | Tasks | Hours | Weeks | Purpose |
|-------|-------|-------|-------|---------|
| 1 | 8 tasks | 35-40 | 1.5-2 | Assessment, DB, Framework |
| 2 | 15 tasks | 95-110 | 4-5 | Consent Management (2026 features) |
| 3 | 13 tasks | 70-85 | 3-4 | DSR Portal (2026 features) |
| 4 | 12 tasks | 55-70 | 2.5-3 | Legal Documents (2026 features) |
| 5 | 10 tasks | 35-45 | 2-2.5 | Analytics & Reporting |
| 6 | 12 tasks | 42-50 | 2-2.5 | Optional Modules (Forms, Cookies, Vendor) |
| 7 | 8 tasks | 30-40 | 2-2.5 | Testing, Docs, CodeCanyon Prep |
| | **78 tasks** | **650-850** | **20-24** | **TOTAL** |

**Average per task:** 8-10 hours  
**Average per week:** 30-40 hours (assuming continuous development)

---

## 🎯 NEXT STEPS

1. **Read This Entire Document** - Especially "AI Agent Instructions"
2. **Review WINNING-FEATURES-2026.md** - Understand market context
3. **Review Module Guides** - Understand feature specifications
4. **Set Up GitHub Project** - Create board with all tasks
5. **Start TASK 1.1** - Begin with Code Audit & Assessment
6. **Execute Phase 1** - Get framework and database ready
7. **Execute Phase 2** - Build Consent Management (critical module)
8. **Execute Phases 3-7** - Complete remaining modules

---

**Document Version:** 2.0 (AI Agent Optimized)  
**Last Updated:** December 19, 2025  
**Status:** Ready for AI Agent Implementation


