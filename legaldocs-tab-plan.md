# Legal Documents Generator Tab - Implementation Plan

## Overview

Add a new "Generate" tab to the Documents main page with card-based document generation, profile data review modal, live preview, version history timeline, and comprehensive V3 dark theme styling.

**Critical Updates (Dec 23, 2025):**
This plan addresses 15 critical implementation gaps including: legal text sourcing, draft/publish workflow, mandatory field validation, placeholder syntax specification, Settings tab implementation, audit trail, preview before generation, regeneration safety, and more.

---

## 🚀 GENERIC PROMPT FOR STARTING ANY PHASE

**Copy-paste this prompt when starting a new phase. Replace [PHASE_NUMBER] with the actual phase (0, 1, 2, 3, 4, or 5):**

```
I want to start Phase [PHASE_NUMBER] of the legaldocs-tab-plan.md implementation.

BEFORE you do anything:

1. Read the legaldocs-tab-plan.md file and locate the "Phase [PHASE_NUMBER]" section with its "⚠️ BEFORE STARTING" verification steps

2. Execute ALL verification steps in that section:
   - Read the specified line ranges from the plan
   - Verify what this phase IS and what it's NOT
   - List all deliverables for this phase
   - Confirm prerequisites are complete

3. After reading, respond with:
   "Phase [PHASE_NUMBER] Verification Summary:
   - Scope: [what this phase builds]
   - NOT in scope: [what this phase does NOT build]
   - Duration: [estimated hours]
   - Line range: [start-end]
   - Prerequisites: [list prerequisites and their status]
   - Deliverables: [numbered list of all deliverables]
   
   Ready to proceed with Phase [PHASE_NUMBER]? This will implement [brief description]. Confirm YES to start implementation."

4. Wait for my explicit "YES" confirmation before writing any code

5. DO NOT start implementation until I confirm
6. DO NOT assume phase scope based on phase number alone
7. DO NOT trust previous conversation summaries - always read the plan first

Follow the plan exactly as written in legaldocs-tab-plan.md lines [RELEVANT_LINES].
```

**Example Usage:**

```
User: "I want to start Phase 1 of the legaldocs-tab-plan.md implementation."

AI Response:
[Reads plan lines 980-1550]

"Phase 1 Verification Summary:
- Scope: Backend services (Settings tab, Profile_Validator, Placeholder_Mapper, Document_Generator, AJAX handlers)
- NOT in scope: UI templates, legal HTML templates, CSS, JavaScript
- Duration: ~4 hours
- Line range: 980-1550
- Prerequisites: Phase 0 complete ✓
- Deliverables:
  1. Settings tab with GDPR/CCPA/LGPD checkboxes
  2. Profile_Validator service with mandatory field validation
  3. Placeholder_Mapper extensions (60+ field mappings)
  4. Document_Generator with generate_from_profile() and generate_preview()
  5. AJAX handler class with 5 actions
  6. Profile version tracking
  7. Draft-only generation logic
  8. Legal disclaimer injection
  9. Version pruning logic
  10. Audit trail metadata
  
Ready to proceed with Phase 1? This will implement backend services and validation infrastructure. Confirm YES to start implementation."

User: "YES"

AI: [Begins implementation with create_file, replace_string_in_file, etc.]
```

**Why This Works:**
- Forces AI to read the actual plan before doing anything
- Requires explicit scope verification and user confirmation
- Prevents assumptions based on phase names
- Creates clear checkpoint before implementation begins
- Ensures prerequisites are checked
- Documents what will be built before building it

---

## 0. Document Card Specifications

### Card Layout (Full Specification)

Each document card displays:

**Header Section:**
- Document icon (🛡️, 📋, 🍪)
- Document title (e.g., "Privacy Policy")
- Status badge (✓ GENERATED / ○ NOT CREATED / ⚠️ OUTDATED)

**Description:**
- 2-3 line summary of document purpose
- Key compliance mentions (GDPR, CCPA, etc.)

**Metadata Row:**
- 📊 Version number (e.g., "v1.3")
- 📅 Last updated date (e.g., "Dec 20, 2025")
- ✅ Publication status (Published/Draft)

**Shortcode Display:**
- Full shortcode in copyable field with copy button
- Example: `[slos_legal_doc type="privacy-policy"]` [📋 Copy]

**Action Buttons:**

For **NOT CREATED** state:
- `[⚡ Generate Document]` (Primary button, full width)
  - Click opens review modal with validation
  - Blocks if mandatory fields missing

For **GENERATED/PUBLISHED** state:
- `[🔄 Regenerate]` - **Always creates new DRAFT version** (never replaces published)
- `[👁 View]` - Preview document in modal
- `[✏ Edit]` - Open in editor
- `[⬇ Download]` - Export as PDF/HTML
- `[🕐 History]` - Show version timeline

**Visual States:**
- **Active (Generated):** Full color, all buttons enabled
- **Inactive (Not Created):** Greyed out, only Generate button
- **Outdated:** Yellow warning banner + "Profile updated since last generation" (detected via profile_version comparison)
- **Premium (Locked):** Blur overlay + "🔒 Premium" badge + Upgrade CTA

**Outdated Detection Logic:**
```php
// Compare document's stored profile_version with current profile version
$doc_profile_version = $doc_metadata['profile_version'] ?? 0;
$current_profile_version = get_option('slos_profile_version', 0); // Incremented on profile save
$is_outdated = $current_profile_version > $doc_profile_version;
```

---

## 1. Field Classification: Mandatory vs Optional

### Mandatory Fields (Required for document generation)

| Step | Field | Reason |
|------|-------|--------|
| 1 | `company.legal_name` | Legal identity in all documents |
| 1 | `company.address.street` | Contact/jurisdiction requirements |
| 1 | `company.address.city` | Contact/jurisdiction requirements |
| 1 | `company.address.country` | Determines applicable laws |
| 1 | `company.business_type` | Affects liability clauses |
| 2 | `contacts.legal_email` | Required contact point |
| 2 | `contacts.dpo.email` | GDPR requirement |
| 3 | `website.url` | Document scope |
| 3 | `website.service_description` | Core document content |
| 4 | `data_collection.personal_data_types` | Privacy policy core |
| 4 | `data_collection.purposes` | Legal basis disclosure |
| 6 | `cookies.essential` | Cookie policy requirement |
| 7 | `legal.primary_jurisdiction` | Governing law clause |
| 8 | `retention.default_period` | GDPR Article 13 requirement |

### Optional Fields (Enhance documents but not blocking)

| Step | Field | Default if Missing |
|------|-------|-------------------|
| 1 | `company.trading_name` | Uses `company.legal_name` |
| 1 | `company.registration_number` | Omit section |
| 1 | `company.vat_number` | Omit section |
| 1 | `company.address.state` | Omit from address |
| 1 | `company.address.postal_code` | Omit from address |
| 1 | `company.industry` | Generic language |
| 2 | `contacts.support_email` | Uses `contacts.legal_email` |
| 2 | `contacts.phone` | Omit phone section |
| 2 | `contacts.dpo.name` | "Data Protection Officer" |
| 2 | `contacts.dpo.phone` | Omit |
| 2 | `contacts.dpo.address` | Uses company address |
| 3 | `website.app_name` | Uses site name |
| 3 | `website.target_audience` | Generic language |
| 4 | `data_collection.special_categories` | Assume "No" |
| 4 | `data_collection.children_data` | Assume "No" |
| 4 | `data_collection.minimum_age` | Default 16 |
| 5 | All third_parties.* | Omit third-party sections |
| 6 | `cookies.analytics/marketing/etc` | Omit respective sections |
| 7 | `legal.gdpr_applies` | Auto-detect from country |
| 7 | `legal.ccpa_applies` | Auto-detect from country |
| 7 | `legal.lgpd_applies` | Auto-detect from country |
| 7 | `legal.supervisory_authority` | Lookup from country |
| 7 | `legal.representative_eu.*` | Omit if not applicable |
| 7 | `legal.representative_uk.*` | Omit if not applicable |
| 8 | `retention.deletion_policy` | Generic language |
| 8 | `retention.backup_retention` | Omit |
| 8 | `security.*` | Generic security statement |
| 8 | `user_rights.response_timeframe` | Default 30 days |

---

## 2. Pre-Generation Data Review Modal

### Modal Structure

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  📋 Review Data Before Generating: Privacy Policy                      [✕] │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ⚠️ Review and customize the data that will be used in your document.      │
│     You can edit values or exclude fields you don't want included.         │
│                                                                             │
│  ┌─ Company Information ───────────────────────────────────────────────┐   │
│  │                                                                      │   │
│  │  ☑ Legal Name          Acme Corporation Ltd.           [✏️] [🗑️]   │   │
│  │  ☑ Trading Name        Acme                            [✏️] [🗑️]   │   │
│  │  ☑ Registration #      12345678                        [✏️] [🗑️]   │   │
│  │  ☐ VAT Number          [Not provided]                  [➕ Add]     │   │
│  │  ☑ Address             123 Main St, London, UK         [✏️] [🗑️]   │   │
│  │  ☑ Business Type       Corporation                     [✏️] [🗑️]   │   │
│  │                                                                      │   │
│  └──────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─ Contact Details ───────────────────────────────────────────────────┐   │
│  │                                                                      │   │
│  │  ☑ Legal Email         legal@acme.com                  [✏️] [🗑️]   │   │
│  │  ☑ DPO Email           dpo@acme.com                    [✏️] [🗑️]   │   │
│  │  ☑ DPO Name            Jane Smith                      [✏️] [🗑️]   │   │
│  │  ☐ Support Email       [Not provided]                  [➕ Add]     │   │
│  │                                                                      │   │
│  └──────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─ Data Collection ───────────────────────────────────────────────────┐   │
│  │                                                                      │   │
│  │  ☑ Data Types          Name, Email, IP Address, ...    [✏️] [🗑️]   │   │
│  │  ☑ Purposes            Service Delivery, Analytics     [✏️] [🗑️]   │   │
│  │  ☑ Minimum Age         16                              [✏️] [🗑️]   │   │
│  │                                                                      │   │
│  └──────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  [Expand: Third Parties ▼] [Expand: Cookies ▼] [Expand: Legal ▼]           │
│                                                                             │
│  ────────────────────────────────────────────────────────────────────────── │
│                                                                             │
│  ⚠️ LEGAL DISCLAIMER:                                                       │
│  These auto-generated documents are starting points only. You MUST have    │
│  all legal documents reviewed by qualified legal counsel before            │
│  publication. Laws vary by jurisdiction.                                   │
│                                                                             │
│  ────────────────────────────────────────────────────────────────────────── │
│                                                                             │
│  Summary: 24 fields included • 3 excluded • 2 missing (will show warning)  │
│                                                                             │
│  ┌──────────────┐  ┌──────────────┐  ┌────────────────────────────────┐   │
│  │   Cancel     │  │  👁 Preview  │  │  ⚡ Generate as Draft          │   │
│  └──────────────┘  └──────────────┘  └────────────────────────────────┘   │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

**Preview Button Behavior:**
- Generates temporary document HTML **without saving to database**
- Opens preview modal showing full rendered document
- User can go back to edit fields or proceed to generate
- Preview uses same template + placeholder logic as final generation
- Timeout: 15 seconds max for preview generation

**Generate Button Behavior:**
- **Pre-validation:** Blocks if any mandatory fields missing
- Shows specific error: "Required field missing: DPO Email (Step 2). [Complete Profile →]"
- On validation pass: Saves document with `status = 'draft'`
- Never auto-publishes generated documents
- Redirects to edit page with success notice: "Draft generated. Review and publish when ready."

### Modal Features

1. **Grouped by wizard step** - Collapsible sections
2. **Checkbox inclusion** - Toggle field on/off for this generation
3. **Inline edit** - Quick edit without leaving modal
4. **Add missing** - Add value for empty optional fields
5. **Visual indicators**:
   - ✅ Green check = Has value, included
   - ⚠️ Yellow = Required but missing (BLOCKS generation)
   - ➖ Gray = Optional, not provided
   - ❌ Red X = Excluded by user
6. **Temporary overrides** - Edits in modal don't change saved profile
7. **Persist preferences** - Remember excluded fields per document type
8. **Preview before generate** - See full rendered document before committing
9. **Mandatory field validation** - Cannot generate if mandatory fields missing
10. **Legal disclaimer** - Prominent warning about legal counsel requirement

---

## 3. Right Panel Design

```
┌─────────────────────────────────┐
│  📊 Generation Stats            │
│  ───────────────────────────── │
│  Profile Complete: 85%          │
│  [████████░░] 85%               │
│                                 │
│  Documents Generated: 2/3       │
│  Last Generated: Dec 20, 2025   │
│  Total Versions: 7              │
│                                 │
├─────────────────────────────────┤
│  ⚡ Quick Actions               │
│  ───────────────────────────── │
│  [🏢 Edit Company Profile    →] │
│  [🔄 Regenerate All Outdated →] │
│  [📥 Export All Documents    →] │
│  [🗑️ Clear All Drafts       →] │
│                                 │
├─────────────────────────────────┤
│  📚 Resources                   │
│  ───────────────────────────── │
│  [📖 GDPR Compliance Guide   →] │
│  [📖 CCPA Requirements       →] │
│  [📖 Cookie Policy Guide     →] │
│  [❓ Template Placeholders   →] │
│                                 │
├─────────────────────────────────┤
│  ⚠️ Attention Needed           │
│  ───────────────────────────── │
│  • Privacy Policy outdated      │
│    (Profile updated 3 days ago) │
│  • 3 required fields missing    │
│    [Complete Now →]             │
│                                 │
├─────────────────────────────────┤
│  🕐 Recent Activity             │
│  ───────────────────────────── │
│  • Privacy Policy v1.3          │
│    Generated Dec 20             │
│  • Terms of Service v1.0        │
│    Published Dec 18             │
│  • Cookie Policy                │
│    Draft saved Dec 15           │
│                                 │
└─────────────────────────────────┘
```

---

## 4. File Structure & Responsibilities

### New Files to Create

| File | Purpose |
|------|---------|
| `templates/admin/documents/tab-generate.php` | Main tab template with cards, stats bar |
| `templates/admin/documents/partials/document-card.php` | Reusable card component |
| `templates/admin/documents/partials/review-modal.php` | Data review modal template |
| `templates/admin/documents/partials/history-modal.php` | Version timeline modal |
| `templates/admin/documents/partials/generate-panel.php` | Right panel component |
| `assets/css/document-generate.css` | V3 dark theme styles for tab |
| `assets/js/document-generate.js` | Tab interactions, modals, AJAX |
| `templates/legaldocs/privacy-policy.html` | Privacy policy template |
| `templates/legaldocs/terms-of-service.html` | Terms template |
| `templates/legaldocs/cookie-policy.html` | Cookie policy template |

### Files to Modify

| File | Changes |
|------|---------|
| `includes/Admin/DocumentsMainPage.php` | Add "Generate" tab registration |
| `includes/Services/Document_Generator.php` | Add profile-aware generation logic |
| `includes/Services/Placeholder_Mapper.php` | Map all wizard fields to placeholders |
| `includes/Admin/Document_Hub_Controller.php` | Add AJAX handlers for generate tab |

### Existing Files to Reuse (NO duplication)

| File | Reuse For |
|------|-----------|
| `includes/Services/Company_Profile_Service.php` | Profile data, field definitions |
| `includes/Database/Repositories/Company_Profile_Repository.php` | Profile CRUD |
| `includes/Database/Repositories/Legal_Doc_Repository.php` | Document CRUD |
| `includes/Services/Localized_Template_Manager.php` | Template loading |
| `includes/Admin/Version_History.php` | Version management |
| `assets/css/admin-v3.css` | Base V3 dark theme variables |

---

## 5. Implementation Phases

### Phase 0: Preparation & Conflict Prevention
**Duration:** ~1 hour
**Critical:** Must complete before Phase 1

#### ⚠️ BEFORE STARTING PHASE 0 - ESSENTIAL VERIFICATION STEPS:

**1. Read Plan Document First (5 minutes):**
```bash
# Read Phase 0 scope in full
read_file legaldocs-tab-plan.md lines 340-600
```

**2. Verify Phase Scope:**
- ✅ Phase 0 is: Preparation, conflict checks, directory setup, infrastructure verification
- ✅ Phase 0 is NOT: Implementation of features
- ✅ Duration: ~1 hour
- ✅ Line range: 340-600
- ✅ All checks must pass before proceeding to Phase 1

**3. Confirm Deliverables:**
- [ ] No naming conflicts found (functions, classes, CSS, AJAX actions)
- [ ] All directories created (templates/admin/documents/partials, templates/legaldocs, assets/css, assets/js)
- [ ] Base infrastructure verified (Document_Generator, Placeholder_Mapper exist)
- [ ] Constants defined (SLOS_TEMPLATE_DIR, SLOS_GEN_VERSION)
- [ ] Sample test data prepared
- [ ] Database tables verified (wp_slos_company_profile, wp_slos_legal_docs, wp_slos_legal_doc_versions)
- [ ] Dependencies confirmed (all service classes exist and load)
- [ ] Base asset files created (empty CSS/JS with namespace comments)
- [ ] Documentation template ready
- [ ] Feature flag added for rollback safety
- [ ] Profile version tracking implemented

**4. Ask User Before Implementation:**
"Phase 0 (lines 340-600) requires:
- Code audit for naming conflicts
- Directory structure setup
- Infrastructure verification (services, repositories exist)
- Database table checks
- Feature flag setup for safe rollback
This is preparation only, no feature implementation.
Estimated: ~1 hour. Proceed with Phase 0? (yes/no)"

**5. DO NOT:**
- ❌ Implement any features yet
- ❌ Create UI templates
- ❌ Write backend services
- ❌ Skip conflict checks

**6. Start Implementation Only After:**
- Read plan lines 340-600 ✓
- Confirmed this is preparation/verification only ✓
- User confirmed "yes" to proceed ✓

---

**Phase 0 Implementation:**

#### A. Code Audit for Conflicts

**1. Search for existing naming conflicts:**
```bash
# Check for conflicting function names
grep -r "generate_document" includes/
grep -r "get_generation_context" includes/
grep -r "apply_user_overrides" includes/

# Check for conflicting CSS classes
grep -r "slos-gen-" assets/css/

# Check for conflicting AJAX actions
grep -r "slos_gen_" includes/

# Check for conflicting nonce names
grep -r "slos_gen.*nonce" includes/
```

**2. Verify existing infrastructure:**
- [ ] Confirm `Document_Generator.php` exists at `includes/Services/`
- [ ] Confirm `Placeholder_Mapper.php` exists at `includes/Services/`
- [ ] Verify `Company_Profile_Service::get_profile()` method exists
- [ ] Verify `Legal_Doc_Repository` has `save()` and `get_versions()` methods
- [ ] Check if `Version_History` class can be extended

**3. Check for tab name conflicts:**
```php
// In DocumentsMainPage.php, check existing tabs
$existing_tabs = ['documents', 'records', 'analytics', 'settings'];
// Ensure 'generate' is not already used
```

#### B. Directory Structure Setup

**Create required directories:**
```bash
mkdir -p templates/admin/documents/partials
mkdir -p templates/legaldocs
mkdir -p assets/css/admin
mkdir -p assets/js/admin
```

**Verify permissions:**
- [ ] Templates directory writable for future template updates
- [ ] Assets directory accessible for enqueuing

#### C. Base Infrastructure Check

**1. Extend Document_Generator if needed:**
```php
// Check current Document_Generator structure
class Document_Generator {
    // If missing, we need to add:
    // - generate_from_profile()
    // - get_generation_context()
    // - apply_user_overrides()
}
```

**2. Verify Placeholder_Mapper extensibility:**
```php
// Check if Placeholder_Mapper supports:
// - Custom placeholder registrations
// - Array rendering (for lists)
// - Table rendering (for cookies)
// - Conditional sections
```

**3. Check existing AJAX infrastructure:**
- [ ] Verify `Document_Hub_Controller::init()` sets up AJAX handlers
- [ ] Check if nonce generation follows plugin patterns
- [ ] Confirm AJAX response format standards

#### D. Constants & Configuration

**Add plugin constants if missing:**
```php
// In main plugin file or constants file
if (!defined('SLOS_TEMPLATE_DIR')) {
    define('SLOS_TEMPLATE_DIR', SHAHI_LEGALOPS_SUITE_PLUGIN_DIR . 'templates/legaldocs/');
}

if (!defined('SLOS_GEN_VERSION')) {
    define('SLOS_GEN_VERSION', '1.0.0'); // For cache busting
}
```

#### E. Sample Data Preparation

**Create test profile for development:**
```php
// Add to a setup script or test file
$test_profile = array(
    'company' => array(
        'legal_name' => 'Test Company Ltd.',
        'address' => array(
            'street' => '123 Test St',
            'city' => 'London',
            'country' => 'GB'
        ),
        'business_type' => 'llc'
    ),
    'contacts' => array(
        'legal_email' => 'legal@test.com',
        'dpo' => array('email' => 'dpo@test.com')
    ),
    'website' => array(
        'url' => 'https://test.com',
        'service_description' => 'Test service'
    ),
    'data_collection' => array(
        'personal_data_types' => array('name', 'email'),
        'purposes' => array('service_delivery')
    ),
    'cookies' => array(
        'essential' => array(
            array('name' => 'session_id', 'purpose' => 'Session', 'duration' => '24h')
        )
    ),
    'legal' => array(
        'primary_jurisdiction' => 'GB',
        'gdpr_applies' => 1
    ),
    'retention' => array(
        'default_period' => '1_year'
    )
);
```

#### F. Database Verification

**Run checks:**
```sql
-- Verify tables exist
SHOW TABLES LIKE 'wp_slos_company_profile';
SHOW TABLES LIKE 'wp_slos_legal_docs';
SHOW TABLES LIKE 'wp_slos_legal_doc_versions';

-- Check table structure supports our needs
DESCRIBE wp_slos_legal_docs;
DESCRIBE wp_slos_legal_doc_versions;

-- Verify indexes
SHOW INDEX FROM wp_slos_legal_docs;
```

**Add indexes if missing:**
```php
// In migration or activation hook
$wpdb->query("CREATE INDEX idx_doc_type ON {$wpdb->prefix}slos_legal_docs (doc_type)");
$wpdb->query("CREATE INDEX idx_locale ON {$wpdb->prefix}slos_legal_docs (locale)");
```

#### G. Dependency Check

**Verify all dependencies are loaded:**
```php
// Check if classes are available
class_exists('ShahiLegalopsSuite\Services\Company_Profile_Service');
class_exists('ShahiLegalopsSuite\Database\Repositories\Company_Profile_Repository');
class_exists('ShahiLegalopsSuite\Database\Repositories\Legal_Doc_Repository');
class_exists('ShahiLegalopsSuite\Services\Localized_Template_Manager');
class_exists('ShahiLegalopsSuite\Admin\Version_History');
```

#### H. Asset Base Files

**Create base CSS structure:**
```css
/* assets/css/document-generate.css */
/* Import V3 dark theme variables */
@import 'admin-v3.css';

/* Namespace all classes with slos-gen- */
.slos-gen-wrapper {
    /* Base styles */
}
```

**Create base JS structure:**
```javascript
/* assets/js/document-generate.js */
(function($) {
    'use strict';
    
    // Namespace all functions
    window.SLOSDocGen = {
        init: function() {},
        // ...
    };
})(jQuery);
```

#### I. Documentation Setup

**Create inline documentation template:**
```php
/**
 * SLOS Document Generator - Generate Tab
 * 
 * @package    ShahiLegalopsSuite
 * @subpackage Admin
 * @since      4.2.0
 * @version    1.0.0
 * 
 * Dependencies:
 * - Company_Profile_Service
 * - Document_Generator
 * - Placeholder_Mapper
 * 
 * Related Files:
 * - templates/admin/documents/tab-generate.php
 * - assets/css/document-generate.css
 * - assets/js/document-generate.js
 */
```

#### Phase 0 Deliverables Checklist

- [ ] No naming conflicts found
- [ ] All directories created
- [ ] Base infrastructure verified
- [ ] Constants defined
- [ ] Sample test data ready
- [ ] Database tables verified
- [ ] Dependencies confirmed
- [ ] Base asset files created
- [ ] Documentation template ready
- [ ] **Feature flag added** for safe rollback
- [ ] **Profile version tracking** implemented (wp_options: slos_profile_version)

#### J. Feature Flag & Rollback Safety

**Add feature flag capability check:**
```php
// In main plugin file or feature flags config
add_filter('slos_feature_enabled', function($enabled, $feature) {
    if ($feature === 'document_generator_tab') {
        // Check if feature is fully ready
        $ready = get_option('slos_gen_tab_ready', false);
        return $ready && $enabled;
    }
    return $enabled;
}, 10, 2);
```

**Tab registration with feature flag:**
```php
// In DocumentsMainPage.php
public function get_tabs() {
    $tabs = ['documents' => 'Documents', 'records' => 'Records'];
    
    // Only show Generate tab if feature ready
    if (apply_filters('slos_feature_enabled', true, 'document_generator_tab')) {
        $tabs['generate'] = 'Generate';
    }
    
    return $tabs;
}
```

**Rollback procedure:**
```php
// Emergency disable via wp-config.php or plugin settings
define('SLOS_DISABLE_GENERATOR_TAB', true);

// Or via option
update_option('slos_gen_tab_ready', false);
```

#### K. Profile Version Tracking Setup

**Add profile version increment on save:**
```php
// In Company_Profile_Repository::save_profile()
public function save_profile($data) {
    // ... existing save logic ...
    
    // Increment profile version for outdated detection
    $current_version = (int) get_option('slos_profile_version', 0);
    update_option('slos_profile_version', $current_version + 1);
    update_option('slos_profile_last_updated', current_time('mysql'));
    
    return $result;
}
```

---

## 0.1 Placeholder Syntax Specification

### Complete Syntax Reference

All templates use a custom placeholder syntax processed by `Placeholder_Mapper`. This section defines the complete syntax.

#### Basic Placeholders

**Simple field:**
```html
{{company.legal_name}}
<!-- Output: "Acme Corporation Ltd." -->
```

**Nested field:**
```html
{{contacts.dpo.email}}
<!-- Output: "dpo@acme.com" -->
```

**Array index:**
```html
{{contacts.additional.0.name}}
<!-- Output: First additional contact name -->
```

#### Default Values

**Syntax:** `{{field|default:"fallback text"}}`

```html
{{company.trading_name|default:"{{company.legal_name}}"}}
<!-- If trading_name empty, uses legal_name -->

{{contacts.phone|default:"Not provided"}}
<!-- Output: "Not provided" if phone empty -->

{{company.vat_number|default:""}}
<!-- Output: empty string (hides section) if no VAT -->
```

#### Filters

**Escape HTML:**
```html
{{user_input|escape:"html"}}
<!-- Applies esc_html() -->
```

**Escape attributes:**
```html
<a href="mailto:{{email|escape:"attr"}}">
<!-- Applies esc_attr() -->
```

**Uppercase:**
```html
{{legal.primary_jurisdiction|upper}}
<!-- GB → GB, us → US -->
```

**Format date:**
```html
{{retention.last_review|date:"F j, Y"}}
<!-- Output: "December 23, 2025" -->
```

**Join array:**
```html
{{data_collection.personal_data_types|join:", "}}
<!-- Output: "Name, Email, IP Address" -->
```

#### Conditionals

**Simple if:**
```html
{{if company.vat_number}}
<p>VAT Number: {{company.vat_number}}</p>
{{/if}}
```

**If-else:**
```html
{{if contacts.dpo.name}}
<p>Data Protection Officer: {{contacts.dpo.name}}</p>
{{else}}
<p>Data Protection Officer: [Name not provided]</p>
{{/if}}
```

**Multiple conditions (AND):**
```html
{{if legal.gdpr_applies && company.address.country == "GB"}}
<p>UK GDPR applies.</p>
{{/if}}
```

**Multiple conditions (OR):**
```html
{{if legal.gdpr_applies || legal.ccpa_applies}}
<p>Subject to data protection regulations.</p>
{{/if}}
```

**Comparison operators:**
```html
{{if data_collection.minimum_age >= 18}}
<p>Adult-only service</p>
{{elseif data_collection.minimum_age >= 13}}
<p>Teen-appropriate content</p>
{{else}}
<p>Family-friendly service</p>
{{/if}}
```

#### Loops

**Foreach basic:**
```html
<ul>
{{foreach data_collection.personal_data_types as data_type}}
    <li>{{data_type}}</li>
{{/foreach}}
</ul>
```

**Foreach with index:**
```html
{{foreach third_parties.processors as index => processor}}
    <h3>{{index|add:1}}. {{processor.name}}</h3>
    <p>Purpose: {{processor.purpose}}</p>
{{/foreach}}
```

**Foreach with key-value:**
```html
<table>
{{foreach cookies.essential as cookie}}
    <tr>
        <td>{{cookie.name}}</td>
        <td>{{cookie.purpose}}</td>
        <td>{{cookie.duration}}</td>
    </tr>
{{/foreach}}
</table>
```

**Empty check:**
```html
{{if cookies.marketing|count > 0}}
<h3>Marketing Cookies</h3>
{{foreach cookies.marketing as cookie}}
    <p>{{cookie.name}}: {{cookie.purpose}}</p>
{{/foreach}}
{{else}}
<p>We do not use marketing cookies.</p>
{{/if}}
```

#### Include Directives (for modular blocks)

**Basic include:**
```html
{{include:blocks/common/introduction.html}}
```

**Conditional include:**
```html
{{if compliance.gdpr}}
{{include:blocks/gdpr/data-subject-rights.html}}
{{/if}}

{{if compliance.ccpa}}
{{include:blocks/ccpa/consumer-rights.html}}
{{/if}}
```

**Include with context:**
```html
{{include:blocks/common/contact-info.html|contact=contacts.dpo}}
<!-- Passes specific data subset to included block -->
```

#### Special Functions

**Count:**
```html
We work with {{third_parties.processors|count}} third-party processors.
```

**Date functions:**
```html
Effective Date: {{@now|date:"F j, Y"}}
<!-- Output: "December 23, 2025" -->

Last Updated: {{@profile.updated_at|date:"M d, Y"}}
```

**System variables:**
```html
{{@site.name}}        <!-- WordPress site name -->
{{@site.url}}         <!-- Site URL -->
{{@site.admin_email}} <!-- Admin email -->
{{@today}}            <!-- Current date -->
{{@year}}             <!-- Current year: 2025 -->
```

#### Complex Example

```html
<h2>Third-Party Data Processors</h2>

{{if third_parties.processors|count > 0}}
<p>We share your data with the following {{third_parties.processors|count}} third parties:</p>

<table>
    <thead>
        <tr>
            <th>Processor</th>
            <th>Purpose</th>
            <th>Data Shared</th>
            <th>Location</th>
        </tr>
    </thead>
    <tbody>
    {{foreach third_parties.processors as processor}}
        <tr>
            <td>{{processor.name|escape:"html"}}</td>
            <td>{{processor.purpose|escape:"html"}}</td>
            <td>{{processor.data_types|join:", "|escape:"html"}}</td>
            <td>
                {{processor.country|default:"Not specified"}}
                {{if processor.country != company.address.country}}
                (International transfer)
                {{/if}}
            </td>
        </tr>
    {{/foreach}}
    </tbody>
</table>

{{if legal.gdpr_applies}}
{{include:blocks/gdpr/international-transfers.html}}
{{/if}}

{{else}}
<p>We do not share your data with third-party processors.</p>
{{/if}}
```

#### Implementation Notes

**Placeholder_Mapper methods to implement:**
```php
class Placeholder_Mapper {
    public function parse_template($template, $data);
    public function resolve_placeholder($placeholder, $data);
    public function apply_filter($value, $filter, $args);
    public function evaluate_condition($condition, $data);
    public function process_loop($loop_body, $array, $data);
    public function include_block($path, $data);
}
```

**Processing order:**
1. Process includes first (load external blocks)
2. Process loops (expand repeated content)
3. Process conditionals (show/hide sections)
4. Process basic placeholders (replace {{field}})
5. Apply filters (format values)
6. Apply default values (fallbacks)
7. Escape output (security)

**If any issues found, resolve before Phase 1.**

---

### Phase 1: Foundation (Backend)
**Files:** Services, Repositories, Controllers
**Duration:** ~4 hours (extended from 2 hours to address critical gaps)
**Prerequisites:** Phase 0 complete

#### ⚠️ BEFORE STARTING PHASE 1 - ESSENTIAL VERIFICATION STEPS:

**1. Read Plan Document First (5 minutes):**
```bash
# Read Phase 1 scope in full
read_file legaldocs-tab-plan.md lines 880-1500
```

**2. Verify Phase Scope:**
- ✅ Phase 1 is: Backend services, validators, generators, AJAX handlers
- ✅ Phase 1 is NOT: UI components, templates, styling
- ✅ Duration: ~4 hours
- ✅ Line range: 880-1500

**3. Confirm Deliverables (from plan checklist lines 1500-1520):**
- [ ] Settings tab with compliance framework checkboxes
- [ ] Profile_Validator service with mandatory field checks
- [ ] Placeholder_Mapper extended with 60+ field mappings
- [ ] Document_Generator with generate_from_profile() and generate_preview()
- [ ] AJAX handlers for all generate tab actions
- [ ] Profile version tracking incremented on save
- [ ] Draft-only generation (never auto-publish)
- [ ] Mandatory field validation blocking generation
- [ ] Legal disclaimer added to generated documents
- [ ] Version pruning (keep latest 10)
- [ ] Audit trail in version metadata
- [ ] Regeneration safety (always creates new draft)

**4. Ask User Before Implementation:**
"Phase 1 (lines 880-1500) requires implementing:
- Settings tab for GDPR/CCPA/LGPD checkboxes
- Profile_Validator service (mandatory field checks)
- Placeholder_Mapper extensions (60+ mappings)
- Document_Generator core logic
- AJAX handler class
Estimated: ~4 hours. Proceed with Phase 1? (yes/no)"

**5. DO NOT:**
- ❌ Assume phase names based on common patterns
- ❌ Trust conversation summaries without verification
- ❌ Skip reading the plan section
- ❌ Implement UI components (that's Phase 3, not Phase 1)
- ❌ Create template HTML files (that's Phase 2)

**6. Start Implementation Only After:**
- Read plan lines 880-1500 ✓
- Confirmed scope with user ✓
- Verified deliverables match ✓

#### 1A. Settings Tab Implementation (~45 min)

**Create Settings Tab** (if not exists):

```php
// In LegalDocs.php or separate Settings_Page.php
public function render_settings_tab() {
    $compliance = get_option('slos_compliance_settings', [
        'gdpr' => true,  // Default enabled
        'ccpa' => false,
        'lgpd' => false,
    ]);
    
    include SLOS_TEMPLATE_DIR . 'admin/documents/tab-settings.php';
}

public function save_settings() {
    check_admin_referer('slos_settings_nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $compliance = [
        'gdpr' => isset($_POST['compliance_gdpr']) ? 1 : 0,
        'ccpa' => isset($_POST['compliance_ccpa']) ? 1 : 0,
        'lgpd' => isset($_POST['compliance_lgpd']) ? 1 : 0,
    ];
    
    update_option('slos_compliance_settings', $compliance);
    update_option('slos_legal_templates_version', time()); // For template update detection
    
    wp_safe_redirect(add_query_arg(['page' => 'slos-documents', 'tab' => 'settings', 'updated' => 'true']));
    exit;
}
```

**Settings UI Template:**
```html
<!-- templates/admin/documents/tab-settings.php -->
<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
    <?php wp_nonce_field('slos_settings_nonce'); ?>
    <input type="hidden" name="action" value="slos_save_settings" />
    
    <h2>Compliance Frameworks</h2>
    <p>Select which privacy regulations apply to your organization. This determines which sections are included in generated documents.</p>
    
    <table class="form-table">
        <tr>
            <th><label for="compliance_gdpr">GDPR (EU/EEA)</label></th>
            <td>
                <input type="checkbox" id="compliance_gdpr" name="compliance_gdpr" value="1" <?php checked($compliance['gdpr']); ?> />
                <p class="description">General Data Protection Regulation - Applies to organizations serving EU/EEA residents</p>
            </td>
        </tr>
        <tr>
            <th><label for="compliance_ccpa">CCPA/CPRA (California)</label></th>
            <td>
                <input type="checkbox" id="compliance_ccpa" name="compliance_ccpa" value="1" <?php checked($compliance['ccpa']); ?> />
                <p class="description">California Consumer Privacy Act - Applies to businesses serving California residents</p>
            </td>
        </tr>
        <tr>
            <th><label for="compliance_lgpd">LGPD (Brazil)</label></th>
            <td>
                <input type="checkbox" id="compliance_lgpd" name="compliance_lgpd" value="1" <?php checked($compliance['lgpd']); ?> />
                <p class="description">Lei Geral de Proteção de Dados - Applies to organizations processing Brazilian residents' data</p>
            </td>
        </tr>
    </table>
    
    <?php submit_button('Save Settings'); ?>
</form>
```

#### 1B. Mandatory Field Validation (~30 min)

**Add validation service:**
```php
// In includes/Services/Profile_Validator.php
class Profile_Validator {
    
    private $mandatory_fields = [
        'company.legal_name',
        'company.address.street',
        'company.address.city',
        'company.address.country',
        'company.business_type',
        'contacts.legal_email',
        'contacts.dpo.email',
        'website.url',
        'website.service_description',
        'data_collection.personal_data_types',
        'data_collection.purposes',
        'cookies.essential',
        'legal.primary_jurisdiction',
        'retention.default_period',
    ];
    
    /**
     * Validate profile for document generation
     *
     * @param array $profile Company profile data
     * @return true|WP_Error True if valid, WP_Error with missing fields if invalid
     */
    public function validate_for_generation($profile) {
        $missing = [];
        
        foreach ($this->mandatory_fields as $field) {
            $value = $this->get_nested_value($profile, $field);
            if (empty($value)) {
                $missing[] = [
                    'field' => $field,
                    'label' => $this->get_field_label($field),
                    'step' => $this->get_field_step($field),
                ];
            }
        }
        
        if (!empty($missing)) {
            return new WP_Error('missing_fields', 'Required fields missing for document generation', $missing);
        }
        
        return true;
    }
    
    private function get_nested_value($array, $path) {
        $keys = explode('.', $path);
        $value = $array;
        
        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return null;
            }
            $value = $value[$key];
        }
        
        return $value;
    }
    
    private function get_field_label($field) {
        $labels = [
            'company.legal_name' => 'Company Legal Name',
            'contacts.dpo.email' => 'DPO Email Address',
            // ... full mapping
        ];
        return $labels[$field] ?? $field;
    }
    
    private function get_field_step($field) {
        if (strpos($field, 'company.') === 0) return 1;
        if (strpos($field, 'contacts.') === 0) return 2;
        if (strpos($field, 'website.') === 0) return 3;
        if (strpos($field, 'data_collection.') === 0) return 4;
        if (strpos($field, 'cookies.') === 0) return 6;
        if (strpos($field, 'legal.') === 0) return 7;
        if (strpos($field, 'retention.') === 0) return 8;
        return 0;
    }
}
```

#### 1C. Update Placeholder_Mapper.php (~30 min)

**Add comprehensive placeholder support:**
```php
// Extend includes/Services/Placeholder_Mapper.php
public function map_all_fields($profile) {
    return [
        // Company (Step 1) - 7 fields
        'company.legal_name' => $profile['company']['legal_name'] ?? '[MISSING: Company Name]',
        'company.trading_name' => $profile['company']['trading_name'] ?? $profile['company']['legal_name'] ?? '',
        'company.registration_number' => $profile['company']['registration_number'] ?? '',
        'company.vat_number' => $profile['company']['vat_number'] ?? '',
        'company.address.street' => $profile['company']['address']['street'] ?? '',
        'company.address.city' => $profile['company']['address']['city'] ?? '',
        'company.address.state' => $profile['company']['address']['state'] ?? '',
        'company.address.postal_code' => $profile['company']['address']['postal_code'] ?? '',
        'company.address.country' => $profile['company']['address']['country'] ?? '',
        'company.business_type' => $profile['company']['business_type'] ?? '',
        'company.industry' => $profile['company']['industry'] ?? '',
        
        // Contacts (Step 2) - 8 fields
        'contacts.legal_email' => $profile['contacts']['legal_email'] ?? '[MISSING: Legal Email]',
        'contacts.support_email' => $profile['contacts']['support_email'] ?? $profile['contacts']['legal_email'] ?? '',
        'contacts.phone' => $profile['contacts']['phone'] ?? '',
        'contacts.dpo.name' => $profile['contacts']['dpo']['name'] ?? 'Data Protection Officer',
        'contacts.dpo.email' => $profile['contacts']['dpo']['email'] ?? '[MISSING: DPO Email]',
        'contacts.dpo.phone' => $profile['contacts']['dpo']['phone'] ?? '',
        'contacts.dpo.address' => $profile['contacts']['dpo']['address'] ?? $this->format_address($profile['company']['address'] ?? []),
        
        // Website (Step 3) - 4 fields
        'website.url' => $profile['website']['url'] ?? '[MISSING: Website URL]',
        'website.app_name' => $profile['website']['app_name'] ?? get_bloginfo('name'),
        'website.service_description' => $profile['website']['service_description'] ?? '[MISSING: Service Description]',
        'website.target_audience' => $profile['website']['target_audience'] ?? '',
        
        // Data Collection (Step 4) - 12 fields
        'data_collection.personal_data_types' => $this->format_list($profile['data_collection']['personal_data_types'] ?? []),
        'data_collection.purposes' => $this->format_list($profile['data_collection']['purposes'] ?? []),
        'data_collection.special_categories' => $profile['data_collection']['special_categories'] ?? false,
        'data_collection.children_data' => $profile['data_collection']['children_data'] ?? false,
        'data_collection.minimum_age' => $profile['data_collection']['minimum_age'] ?? 16,
        
        // Third Parties (Step 5) - arrays
        'third_parties.processors' => $profile['third_parties']['processors'] ?? [],
        'third_parties.partners' => $profile['third_parties']['partners'] ?? [],
        
        // Cookies (Step 6) - arrays
        'cookies.essential' => $profile['cookies']['essential'] ?? [],
        'cookies.analytics' => $profile['cookies']['analytics'] ?? [],
        'cookies.marketing' => $profile['cookies']['marketing'] ?? [],
        'cookies.functional' => $profile['cookies']['functional'] ?? [],
        
        // Legal (Step 7) - 10+ fields
        'legal.primary_jurisdiction' => $profile['legal']['primary_jurisdiction'] ?? '[MISSING: Jurisdiction]',
        'legal.gdpr_applies' => $profile['legal']['gdpr_applies'] ?? false,
        'legal.ccpa_applies' => $profile['legal']['ccpa_applies'] ?? false,
        'legal.lgpd_applies' => $profile['legal']['lgpd_applies'] ?? false,
        'legal.supervisory_authority' => $profile['legal']['supervisory_authority'] ?? '',
        
        // Retention & Security (Step 8) - 8+ fields
        'retention.default_period' => $profile['retention']['default_period'] ?? '[MISSING: Retention Period]',
        'retention.deletion_policy' => $profile['retention']['deletion_policy'] ?? '',
        'security.measures' => $this->format_list($profile['security']['measures'] ?? []),
        
        // System variables
        '@site.name' => get_bloginfo('name'),
        '@site.url' => get_site_url(),
        '@site.admin_email' => get_option('admin_email'),
        '@today' => date('F j, Y'),
        '@year' => date('Y'),
    ];
}

private function format_list($array) {
    if (empty($array)) return '';
    return implode(', ', array_map('esc_html', $array));
}

private function format_address($address) {
    $parts = array_filter([
        $address['street'] ?? '',
        $address['city'] ?? '',
        $address['state'] ?? '',
        $address['postal_code'] ?? '',
        $address['country'] ?? '',
    ]);
    return implode(', ', $parts);
}
```

#### 1D. Update Document_Generator.php (~45 min)

**Add core generation methods:**
```php
// Extend includes/Services/Document_Generator.php

/**
 * Generate document from company profile
 *
 * @param string $doc_type Document type (privacy-policy, terms-of-service, cookie-policy)
 * @param array  $overrides User overrides from review modal
 * @param int    $user_id   User performing generation
 * @return int|WP_Error Document ID on success, WP_Error on failure
 */
public function generate_from_profile($doc_type, $overrides = [], $user_id = null) {
    // 1. Validate mandatory fields
    $profile = $this->profile_service->get_profile();
    $validator = new Profile_Validator();
    $validation = $validator->validate_for_generation($profile);
    
    if (is_wp_error($validation)) {
        return $validation;
    }
    
    // 2. Apply user overrides (from review modal)
    $profile = $this->apply_user_overrides($profile, $overrides);
    
    // 3. Get compliance settings
    $compliance = get_option('slos_compliance_settings', ['gdpr' => true, 'ccpa' => false, 'lgpd' => false]);
    
    // 4. Load and assemble template
    $template_path = SLOS_TEMPLATE_DIR . $doc_type . '.html';
    if (!file_exists($template_path)) {
        return new WP_Error('template_missing', 'Template file not found: ' . $doc_type);
    }
    
    $template_content = file_get_contents($template_path);
    
    // 5. Assemble modular blocks based on compliance settings
    $template_content = $this->template_manager->assemble_template($template_content, $compliance, $profile);
    
    // 6. Replace all placeholders
    $final_content = $this->placeholder_mapper->parse_template($template_content, $profile);
    
    // 7. Check for unresolved placeholders
    if (preg_match('/\{\{[^}]+\}\}/', $final_content, $matches)) {
        error_log('[SLOS] Unresolved placeholders: ' . implode(', ', $matches));
        // Continue anyway - [MISSING: field] fallbacks handle this
    }
    
    // 8. Add legal disclaimer to footer
    $final_content = $this->add_legal_disclaimer($final_content);
    
    // 9. Save as DRAFT (never auto-publish)
    $current_profile_version = (int) get_option('slos_profile_version', 0);
    $user_id = $user_id ?? get_current_user_id();
    
    $doc_data = [
        'title' => $this->get_document_title($doc_type),
        'content' => $final_content,
        'doc_type' => $doc_type,
        'status' => 'draft', // ALWAYS DRAFT
        'locale' => get_locale(),
        'metadata' => json_encode([
            'generated_at' => current_time('mysql'),
            'generated_by' => $user_id,
            'author_name' => wp_get_current_user()->display_name,
            'generated_from' => 'auto_generator',
            'profile_version' => $current_profile_version,
            'compliance_settings' => $compliance,
            'excluded_fields' => $overrides['excluded'] ?? [],
            'field_overrides' => $overrides['values'] ?? [],
            'template_version' => get_option('slos_legal_templates_version', 1),
            'change_reason' => 'Auto-generated from company profile',
        ]),
    ];
    
    // 10. Check if document already exists
    $existing_doc = $this->doc_repository->get_by_type($doc_type);
    
    if ($existing_doc) {
        // Update existing document, create new version
        $doc_data['id'] = $existing_doc['id'];
        $doc_id = $this->doc_repository->save($doc_data);
        
        // Create version history entry
        $this->version_history->create_version($doc_id, $doc_data);
    } else {
        // Create new document
        $doc_id = $this->doc_repository->save($doc_data);
        
        // Create initial version
        $this->version_history->create_version($doc_id, $doc_data);
    }
    
    // 11. Prune old versions (keep latest 10)
    $this->prune_old_versions($doc_id, 10);
    
    return $doc_id;
}

/**
 * Generate preview without saving (for preview modal)
 *
 * @param string $doc_type Document type
 * @param array  $overrides User overrides
 * @return string|WP_Error HTML content or error
 */
public function generate_preview($doc_type, $overrides = []) {
    set_time_limit(15); // 15 second timeout for preview
    
    // Same logic as generate_from_profile but don't save
    $profile = $this->profile_service->get_profile();
    $profile = $this->apply_user_overrides($profile, $overrides);
    $compliance = get_option('slos_compliance_settings', ['gdpr' => true, 'ccpa' => false, 'lgpd' => false]);
    
    $template_path = SLOS_TEMPLATE_DIR . $doc_type . '.html';
    if (!file_exists($template_path)) {
        return new WP_Error('template_missing', 'Template not found');
    }
    
    $template_content = file_get_contents($template_path);
    $template_content = $this->template_manager->assemble_template($template_content, $compliance, $profile);
    $final_content = $this->placeholder_mapper->parse_template($template_content, $profile);
    $final_content = $this->add_legal_disclaimer($final_content);
    
    return $final_content;
}

/**
 * Apply user overrides from review modal
 */
private function apply_user_overrides($profile, $overrides) {
    // Apply field value overrides
    if (!empty($overrides['values'])) {
        foreach ($overrides['values'] as $field => $value) {
            $this->set_nested_value($profile, $field, $value);
        }
    }
    
    // Remove excluded fields
    if (!empty($overrides['excluded'])) {
        foreach ($overrides['excluded'] as $field) {
            $this->set_nested_value($profile, $field, null);
        }
    }
    
    return $profile;
}

/**
 * Add legal disclaimer to document footer
 */
private function add_legal_disclaimer($content) {
    $disclaimer = '
    <!-- LEGAL DISCLAIMER -->
    <div class="slos-legal-disclaimer" style="margin-top: 3em; padding: 1em; border: 2px solid #f0ad4e; background: #fcf8e3;">
        <p><strong>⚠️ Important Legal Notice:</strong></p>
        <p>This document was auto-generated as a starting point. It is NOT a substitute for professional legal advice. 
        You MUST have this document reviewed and approved by qualified legal counsel familiar with your jurisdiction, 
        business model, and regulatory requirements before publication. Laws change frequently and vary by jurisdiction.</p>
        <p>Generated: ' . date('F j, Y \a\t g:i A') . '</p>
    </div>';
    
    return $content . $disclaimer;
}

/**
 * Prune old versions to prevent database bloat
 */
private function prune_old_versions($doc_id, $keep_count = 10) {
    global $wpdb;
    $table = $wpdb->prefix . 'slos_legal_doc_versions';
    
    // Get version IDs to delete (keep latest $keep_count)
    $versions_to_delete = $wpdb->get_col($wpdb->prepare("
        SELECT id FROM {$table}
        WHERE doc_id = %d
        ORDER BY created_at DESC
        LIMIT 999 OFFSET %d
    ", $doc_id, $keep_count));
    
    if (!empty($versions_to_delete)) {
        $ids = implode(',', array_map('intval', $versions_to_delete));
        $wpdb->query("DELETE FROM {$table} WHERE id IN ($ids)");
    }
}

/**
 * Get generation context for review modal
 */
public function get_generation_context($doc_type) {
    $profile = $this->profile_service->get_profile();
    $validator = new Profile_Validator();
    $validation = $validator->validate_for_generation($profile);
    
    return [
        'profile' => $profile,
        'validation' => $validation,
        'is_valid' => !is_wp_error($validation),
        'missing_fields' => is_wp_error($validation) ? $validation->get_error_data() : [],
        'doc_type' => $doc_type,
        'compliance' => get_option('slos_compliance_settings', []),
    ];
}
```

#### 1E. AJAX Handlers in Document_Hub_Controller.php (~30 min)

**Add AJAX endpoints:**
```php
// Extend includes/Admin/Document_Hub_Controller.php

public function init() {
    // ... existing init logic ...
    
    // AJAX handlers for Generate tab
    add_action('wp_ajax_slos_gen_get_context', [$this, 'ajax_get_generation_context']);
    add_action('wp_ajax_slos_gen_preview', [$this, 'ajax_generate_preview']);
    add_action('wp_ajax_slos_gen_generate', [$this, 'ajax_generate_document']);
    add_action('wp_ajax_slos_gen_history', [$this, 'ajax_get_version_history']);
    add_action('wp_ajax_slos_gen_restore', [$this, 'ajax_restore_version']);
}

/**
 * AJAX: Get generation context for review modal
 */
public function ajax_get_generation_context() {
    check_ajax_referer('slos_gen_modal_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized'], 403);
    }
    
    $doc_type = sanitize_text_field($_POST['doc_type'] ?? '');
    if (empty($doc_type)) {
        wp_send_json_error(['message' => 'Document type required'], 400);
    }
    
    $context = $this->document_generator->get_generation_context($doc_type);
    
    if (!$context['is_valid']) {
        wp_send_json_error([
            'message' => 'Profile validation failed',
            'missing_fields' => $context['missing_fields'],
        ], 422);
    }
    
    wp_send_json_success($context);
}

/**
 * AJAX: Generate preview (no save)
 */
public function ajax_generate_preview() {
    check_ajax_referer('slos_gen_modal_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized'], 403);
    }
    
    $doc_type = sanitize_text_field($_POST['doc_type'] ?? '');
    $overrides = json_decode(stripslashes($_POST['overrides'] ?? '{}'), true);
    
    $preview = $this->document_generator->generate_preview($doc_type, $overrides);
    
    if (is_wp_error($preview)) {
        wp_send_json_error([
            'message' => $preview->get_error_message(),
            'code' => $preview->get_error_code(),
        ], 500);
    }
    
    wp_send_json_success([
        'html' => $preview,
        'word_count' => str_word_count(strip_tags($preview)),
    ]);
}

/**
 * AJAX: Generate and save document (DRAFT)
 */
public function ajax_generate_document() {
    check_ajax_referer('slos_gen_modal_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized'], 403);
    }
    
    $doc_type = sanitize_text_field($_POST['doc_type'] ?? '');
    $overrides = json_decode(stripslashes($_POST['overrides'] ?? '{}'), true);
    $change_reason = sanitize_text_field($_POST['change_reason'] ?? 'Generated from profile');
    
    $overrides['change_reason'] = $change_reason;
    
    $doc_id = $this->document_generator->generate_from_profile($doc_type, $overrides, get_current_user_id());
    
    if (is_wp_error($doc_id)) {
        wp_send_json_error([
            'message' => $doc_id->get_error_message(),
            'code' => $doc_id->get_error_code(),
            'data' => $doc_id->get_error_data(),
        ], 500);
    }
    
    wp_send_json_success([
        'doc_id' => $doc_id,
        'edit_url' => admin_url('admin.php?page=slos-edit-document&id=' . $doc_id),
        'message' => 'Document generated as draft. Review and publish when ready.',
    ]);
}

/**
 * AJAX: Get version history
 */
public function ajax_get_version_history() {
    check_ajax_referer('slos_gen_history_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized'], 403);
    }
    
    $doc_id = intval($_POST['doc_id'] ?? 0);
    if (!$doc_id) {
        wp_send_json_error(['message' => 'Document ID required'], 400);
    }
    
    $versions = $this->version_history->get_versions($doc_id, 20); // Latest 20
    
    wp_send_json_success(['versions' => $versions]);
}

/**
 * AJAX: Restore old version (creates new draft from old version)
 */
public function ajax_restore_version() {
    check_ajax_referer('slos_gen_history_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized'], 403);
    }
    
    $version_id = intval($_POST['version_id'] ?? 0);
    if (!$version_id) {
        wp_send_json_error(['message' => 'Version ID required'], 400);
    }
    
    // Get version content
    $version = $this->version_history->get_version($version_id);
    if (!$version) {
        wp_send_json_error(['message' => 'Version not found'], 404);
    }
    
    // Create new draft from old version content
    $doc_id = $version['doc_id'];
    $doc = $this->doc_repository->get($doc_id);
    
    $doc_data = [
        'id' => $doc_id,
        'content' => $version['content'],
        'status' => 'draft', // Restore as draft for review
        'metadata' => json_encode([
            'restored_from_version' => $version['version'],
            'restored_at' => current_time('mysql'),
            'restored_by' => get_current_user_id(),
            'change_reason' => 'Restored from version ' . $version['version'],
        ]),
    ];
    
    $this->doc_repository->save($doc_data);
    $this->version_history->create_version($doc_id, $doc_data);
    
    wp_send_json_success([
        'message' => 'Version restored as draft',
        'edit_url' => admin_url('admin.php?page=slos-edit-document&id=' . $doc_id),
    ]);
}
```

#### Phase 1 Deliverables:

- [ ] Settings tab created with compliance framework checkboxes
- [ ] Profile_Validator service with mandatory field checks
- [ ] Placeholder_Mapper extended with 60+ field mappings and full syntax support
- [ ] Document_Generator with generate_from_profile() and generate_preview()
- [ ] AJAX handlers for all generate tab actions
- [ ] **Profile version tracking** incremented on save
- [ ] **Draft-only generation** (never auto-publish)
- [ ] **Mandatory field validation** blocking generation
- [ ] **Legal disclaimer** added to all generated documents
- [ ] **Version pruning** to prevent database bloat (keep latest 10)
- [ ] **Audit trail** in version metadata (author, timestamp, reason)
- [ ] **Regeneration safety** (always creates new draft)

---

### Phase 2: Templates & Legal Content
**Files:** templates/legaldocs/*.html
**Duration:** ~5 hours (extended from 3 hours)
**Prerequisites:** Phase 1 complete

#### ⚠️ BEFORE STARTING PHASE 2 - ESSENTIAL VERIFICATION STEPS:

**1. Read Plan Document First (5 minutes):**
```bash
# Read Phase 2 scope in full
read_file legaldocs-tab-plan.md lines 1500-2100
```

**2. Verify Phase Scope:**
- ✅ Phase 2 is: HTML template files with legal text content
- ✅ Phase 2 is NOT: UI components, JavaScript, CSS, cards, modals
- ✅ Duration: ~5 hours (4+ hours for legal text drafting)
- ✅ Line range: 1500-2100
- ✅ Main deliverable: privacy-policy.html, terms-of-service.html, cookie-policy.html

**3. Confirm Deliverables (from plan checklist lines 2090-2100):**
- [ ] Modular template block structure (blocks/ directory)
- [ ] privacy-policy.html master template
- [ ] terms-of-service.html master template
- [ ] cookie-policy.html master template
- [ ] GDPR compliance blocks (data-subject-rights.html, legal-basis.html, etc.)
- [ ] CCPA compliance blocks (consumer-rights.html, categories-disclosure.html, etc.)
- [ ] LGPD compliance blocks (data-holder-rights.html, etc.)
- [ ] Common blocks (intro-section.html, data-collection.html, security-measures.html, etc.)
- [ ] E-commerce blocks (payment-processing.html, order-data.html, etc.)
- [ ] All templates use placeholder syntax ({{company.legal_name}}, {{if}}, {{foreach}})
- [ ] Professional legal language quality (as lawyer would write)
- [ ] Settings tab integration for compliance framework selection

**4. Ask User Before Implementation:**
"Phase 2 (lines 1500-2100) requires creating:
- HTML template files: privacy-policy.html, terms-of-service.html, cookie-policy.html
- Modular compliance blocks (GDPR/CCPA/LGPD sections)
- Professional legal text content (~4 hours drafting)
- Settings tab integration for framework selection
This is LEGAL CONTENT creation, not UI components.
Estimated: ~5 hours. Proceed with Phase 2? (yes/no)"

**5. DO NOT:**
- ❌ Create PHP template files (tab-generate.php, document-card.php) - those are Phase 3
- ❌ Create CSS/JavaScript files - those are Phase 3
- ❌ Implement UI components, cards, modals - those are Phase 3
- ❌ Assume "Phase 2" means "frontend UI"
- ❌ Skip legal text drafting (the 4-hour core task)

**6. Start Implementation Only After:**
- Read plan lines 1500-2100 ✓
- Confirmed this is legal templates HTML, not UI ✓
- Verified deliverables match ✓
- User confirmed "yes" to proceed ✓

**CRITICAL:** This phase involves drafting comprehensive legal text blocks.

#### 2A. Template Architecture Design (~30 min)

**Modular Block Structure:**
```
templates/legaldocs/
├── blocks/                      # Reusable compliance blocks
│   ├── gdpr/
│   │   ├── data-subject-rights.html
│   │   ├── legal-basis.html
│   │   ├── international-transfers.html
│   │   ├── dpo-contact.html
│   │   └── supervisory-authority.html
│   ├── ccpa/
│   │   ├── consumer-rights.html
│   │   ├── categories-disclosure.html
│   │   ├── sale-opt-out.html
│   │   └── shine-the-light.html
│   ├── lgpd/
│   │   ├── data-holder-rights.html
│   │   └── anpd-authority.html
│   ├── common/
│   │   ├── intro-section.html
│   │   ├── data-collection.html
│   │   ├── data-usage.html
│   │   ├── third-party-sharing.html
│   │   ├── security-measures.html
│   │   ├── children-privacy.html
│   │   ├── contact-info.html
│   │   └── policy-updates.html
│   └── ecommerce/
│       ├── payment-processing.html
│       ├── order-data.html
│       └── refund-data.html
├── privacy-policy.html          # Master template
├── terms-of-service.html        # Master template
└── cookie-policy.html           # Master template
```

**Master Template Structure:**
```html
<!-- privacy-policy.html -->
<html>
{{include:blocks/common/intro-section.html}}

{{include:blocks/common/data-collection.html}}

{{if legal.gdpr_applies}}
{{include:blocks/gdpr/legal-basis.html}}
{{include:blocks/gdpr/data-subject-rights.html}}
{{/if}}

{{if legal.ccpa_applies}}
{{include:blocks/ccpa/consumer-rights.html}}
{{include:blocks/ccpa/categories-disclosure.html}}
{{/if}}

{{if has_ecommerce}}
{{include:blocks/ecommerce/payment-processing.html}}
{{/if}}

{{include:blocks/common/security-measures.html}}
{{include:blocks/common/contact-info.html}}
</html>
```

#### 2B. Settings Tab Integration (~30 min)

**Add to Settings page** (`includes/Modules/LegalDocs/LegalDocs.php`):
```php
// New settings for compliance frameworks
$settings = array(
    'compliance_frameworks' => array(
        'gdpr' => isset($_POST['compliance_gdpr']) ? 1 : 0,
        'ccpa' => isset($_POST['compliance_ccpa']) ? 1 : 0,
        'lgpd' => isset($_POST['compliance_lgpd']) ? 1 : 0,
    ),
    'document_tone' => sanitize_text_field($_POST['document_tone'] ?? 'formal'),
    'jurisdiction_priority' => sanitize_text_field($_POST['jurisdiction_priority'] ?? 'auto'),
    'include_definitions' => isset($_POST['include_definitions']) ? 1 : 0,
    'include_examples' => isset($_POST['include_examples']) ? 1 : 0,
);
```

**Settings UI:**
```php
<h3>Compliance Frameworks</h3>
<label>
    <input type="checkbox" name="compliance_gdpr" <?php checked($settings['gdpr']); ?> />
    Include GDPR sections (EU/EEA)
</label>
<label>
    <input type="checkbox" name="compliance_ccpa" <?php checked($settings['ccpa']); ?> />
    Include CCPA sections (California)
</label>
<label>
    <input type="checkbox" name="compliance_lgpd" <?php checked($settings['lgpd']); ?> />
    Include LGPD sections (Brazil)
</label>

<h3>Document Style</h3>
<select name="document_tone">
    <option value="formal">Formal Legal Language</option>
    <option value="friendly">User-Friendly Language</option>
    <option value="technical">Technical/Developer Audience</option>
</select>
```

#### 2C. Legal Text Drafting (~4 hours)

**CRITICAL: Professional Legal Quality Required**

All template blocks must be drafted in **formal legal language exactly as an expert lawyer would write them**:
- Use proper legal terminology, citations (Article 6 GDPR, §1798.100 CCPA, etc.)
- Follow standard legal document structure and tone
- Ensure legal accuracy and comprehensiveness
- Include all required disclosures and statutory language
- Consider engaging legal professionals or using vetted legal templates as source material
- Each block should stand alone as legally sound content

**Not placeholder text** - these are actual legal documents that will be published.

**🚨 LEGAL TEXT SOURCING OPTIONS:**

1. **Option A: Licensed Templates (RECOMMENDED)**
   - Purchase professionally-drafted legal templates from reputable providers:
     - TermsFeed (https://termsfeed.com)
     - iubenda (https://iubenda.com)
     - Termly (https://termly.io)
     - PrivacyPolicies.com
   - Cost: $50-200 per template set
   - Benefit: Professionally reviewed, regularly updated

2. **Option B: Legal Counsel Engagement**
   - Hire privacy attorney to draft bespoke templates
   - Cost: $2,000-5,000 for complete set
   - Benefit: Tailored to specific use case, highest quality

3. **Option C: Third-Party Template Library Integration**
   - Integrate with Docassemble or similar legal automation platform
   - Use their vetted template library via API
   - Cost: API licensing fees

4. **Option D: Open-Source Legal Templates (Use with Caution)**
   - Adapt templates from WordPress Compliance plugins
   - Adapt from government resources (e.g., UK ICO template guidance)
   - **CRITICAL:** Must be reviewed and updated by legal counsel

**⚠️ DO NOT proceed with Phase 2C until legal text source is determined and acquired.**

**After legal text source is secured, proceed with technical implementation:**

**Priority 1: Privacy Policy Blocks (2 hours)**

1. **Common Blocks** (lawyer-quality formal language):
   - `intro-section.html` - Formal opening with company legal identity, document purpose, effective date, scope of applicability, definitions section
   - `data-collection.html` - Comprehensive data categories with legal definitions, collection methods, automated collection disclosures
   - `data-usage.html` - Specific processing purposes with legal basis citations, retention schedules, automated decision-making disclosures
   - `third-party-sharing.html` - Complete third-party recipient categories, purpose of disclosure, contractual safeguards, onward transfer provisions
   - `security-measures.html` - Technical and organizational security measures, encryption standards, breach notification procedures
   - `children-privacy.html` - COPPA compliance, age verification procedures, parental consent mechanisms
   - `contact-info.html` - Formal contact methods with response timeframes, escalation procedures
   - `policy-updates.html` - Legal notification procedures, consent requirements for material changes, archival access

2. **GDPR Blocks** (Articles-specific formal language):
   - `legal-basis.html` - Article 6 lawful bases with detailed legal explanations, consent requirements, legitimate interests assessments, special category data under Article 9
   - `data-subject-rights.html` - Articles 15-22 rights with exercise procedures (access, rectification, erasure, portability, restriction, objection, automated decision-making), verification requirements, response timelines (1 month), fee structure, exemptions
   - `international-transfers.html` - Articles 44-50 transfer mechanisms (Standard Contractual Clauses, adequacy decisions, BCRs), safeguards, transfer impact assessments
   - `dpo-contact.html` - DPO identification under Article 37-39, contact details, independence requirements
   - `supervisory-authority.html` - Competent supervisory authority identification (ICO, CNIL, etc.), complaint procedures, judicial remedies under Articles 77-79

3. **CCPA/CPRA Blocks** (§ statutory formal language):
   - `consumer-rights.html` - §1798.100-130 rights (right to know categories and specific pieces, right to delete, right to correct, right to opt-out, right to limit sensitive PI), verification procedures, authorized agent requirements
   - `categories-disclosure.html` - §1798.110 categories of personal information collected, categories of sources, business/commercial purposes, categories of third parties
   - `sale-opt-out.html` - §1798.120 "Do Not Sell or Share My Personal Information" procedures, opt-out preference signals, 12-month re-authorization
   - `shine-the-light.html` - California Civil Code §1798.83 disclosure requirements, annual request procedures

4. **E-commerce Blocks** (transactional formal language):
   - `payment-processing.html` - Payment processor relationships, third-party PSPs, PCI-DSS compliance statements, transaction security measures, liability allocations
   - `order-data.html` - Order history retention for legal/tax requirements, transaction records, dispute resolution data, statute of limitations
   - `refund-data.html` - Refund policy data processing, chargeback procedures, financial records retention

**Priority 2: Terms of Service Blocks (~1 hour)** (contract-quality formal language):
- `acceptance-terms.html` - Formal contract formation language, consideration, binding agreement clauses
- `account-responsibilities.html` - Account security obligations, credential confidentiality, unauthorized use liability
- `acceptable-use.html` - Prohibited conduct, legal compliance requirements, harmful activities prohibitions
- `intellectual-property.html` - Copyright ownership, license grants, DMCA procedures, trademark restrictions
- `disclaimers.html` - Warranty disclaimers, "AS IS" provisions, liability limitations, consequential damages exclusions
- `termination.html` - Termination rights, effect of termination, survival clauses, account deletion procedures
- `governing-law.html` - Choice of law provision, jurisdiction clauses, venue selection
- `dispute-resolution.html` - Arbitration agreements, class action waivers, informal dispute resolution

**Priority 3: Cookie Policy Blocks (~1 hour)** (regulatory compliance language):
- `cookie-definition.html` - Technical explanation in legal terms, cookie technology, similar technologies
- `cookie-types.html` - Essential, performance, functional, targeting cookies with legal necessity justifications
- `cookie-table.html` - Structured disclosure table (name, provider, purpose, duration, type)
- `third-party-cookies.html` - Third-party cookie providers, cross-site tracking disclosures, independent privacy policies
- `cookie-management.html` - User control mechanisms, browser settings, opt-out procedures, consequences of disabling
- `browser-instructions.html` - Browser-specific instructions (Chrome, Firefox, Safari, Edge), mobile platforms

#### 2D. Template Assembly Logic (~30 min)

**Update `Localized_Template_Manager.php`:**
```php
/**
 * Assemble template from blocks based on settings
 *
 * @param string $master_template Master template path
 * @param array  $settings        Compliance settings
 * @param array  $profile         Company profile
 * @return string Assembled template
 */
public function assemble_template($master_template, $settings, $profile) {
    $content = file_get_contents($master_template);
    
    // Process includes
    preg_match_all('/\{\{include:(.*?)\}\}/', $content, $includes);
    foreach ($includes[1] as $block_path) {
        $block_content = $this->load_block($block_path);
        $content = str_replace("{{include:$block_path}}", $block_content, $content);
    }
    
    // Process conditionals based on settings
    $content = $this->process_conditionals($content, $settings, $profile);
    
    return $content;
}
```

#### Phase 2 Deliverables:
- [ ] 15+ reusable legal text blocks drafted **in professional lawyer-quality language**
- [ ] Legal terminology, citations, and statutory references verified
- [ ] Formal legal tone and structure maintained throughout all blocks
- [ ] 3 master templates with include directives
- [ ] Settings tab updated with compliance options
- [ ] Template assembly logic implemented
- [ ] Legal review notes documented for future attorney review
- [ ] Legal disclaimer added: "These templates are provided as a starting point. All users must have legal documents reviewed by qualified legal counsel in their jurisdiction before publication."

**CRITICAL DISCLAIMER:** While these templates are drafted in professional legal language, they are generic starting points. **Every client must have their specific legal documents reviewed and approved by qualified legal counsel** familiar with their jurisdiction, business model, and regulatory requirements before publication. Laws change frequently and vary by jurisdiction.

---

### Phase 3: UI Components (Frontend)
**Files:** templates/admin/documents/
**Duration:** ~3 hours
**Prerequisites:** Phase 1 (Backend) and Phase 2 (Legal Templates) complete

#### ⚠️ BEFORE STARTING PHASE 3 - ESSENTIAL VERIFICATION STEPS:

**1. Read Plan Document First (5 minutes):**
```bash
# Read Phase 3 scope in full
read_file legaldocs-tab-plan.md lines 1882-2250
```

**2. Verify Phase Scope:**
- ✅ Phase 3 is: PHP template files for UI (tab-generate.php, document-card.php, partials)
- ✅ Phase 3 is NOT: Backend services, legal HTML templates, or JavaScript
- ✅ Duration: ~3 hours
- ✅ Line range: 1882-2050 (UI templates section)
- ✅ Main deliverable: PHP templates for displaying cards, modals, and panels

**3. Confirm Deliverables:**
- [ ] tab-generate.php (main tab template with stats bar)
- [ ] partials/document-card.php (card component, 4 states)
- [ ] partials/review-modal.php (modal with field groups)
- [ ] partials/history-modal.php (version timeline)
- [ ] partials/generate-panel.php (right sidebar)

**4. Ask User Before Implementation:**
"Phase 3 (lines 1882-2050) requires creating:
- PHP template files for UI display (tab-generate.php, document-card.php, etc.)
- Card components with 4 states (not-created, draft, published, outdated)
- Review modal structure with field groups
- Right sidebar panel with widgets
This is PHP UI templates, not backend services or legal content.
Estimated: ~3 hours. Proceed with Phase 3? (yes/no)"

**5. DO NOT:**
- ❌ Create backend services (Document_Generator, Profile_Validator) - that's Phase 1
- ❌ Create legal HTML templates (privacy-policy.html) - that's Phase 2
- ❌ Create JavaScript files - that's Phase 5
- ❌ Create CSS files - that's Phase 4
- ❌ Implement AJAX handlers - that's Phase 1

**6. Start Implementation Only After:**
- Read plan lines 1882-2050 ✓
- Confirmed this is PHP UI templates ✓
- Verified Phase 1 backend exists ✓
- Verified Phase 2 legal templates exist ✓
- User confirmed "yes" to proceed ✓

---

**Phase 3 Implementation:**

1. **Create `tab-generate.php`**
   - Stats bar (5 metrics)
   - Core documents section (3 cards)
   - Premium documents section (6 locked cards)
   - Right panel include

2. **Create `partials/document-card.php`**
   - Card states (not-created, draft, published, outdated)
   - Action buttons
   - Shortcode display
   - Version/date info

3. **Create `partials/review-modal.php`**
   - Grouped field list
   - Inline edit capability
   - Checkbox toggles
   - Summary stats

4. **Create `partials/history-modal.php`**
   - Timeline UI
   - Version comparison
   - Restore functionality

5. **Create `partials/generate-panel.php`**
   - Stats widget
   - Quick actions
   - Resources links
   - Alerts/warnings
   - Activity feed

---

### Phase 4: Styling (CSS)
**Files:** assets/css/document-generate.css
**Duration:** ~1.5 hours
**Prerequisites:** Phase 3 (UI templates) complete

#### ⚠️ BEFORE STARTING PHASE 4 - ESSENTIAL VERIFICATION STEPS:

**1. Read Plan Document First (3 minutes):**
```bash
# Read Phase 4 scope
read_file legaldocs-tab-plan.md lines 2050-2200
```

**2. Verify Phase Scope:**
- ✅ Phase 4 is: CSS styling for UI components
- ✅ Phase 4 is NOT: Templates, JavaScript, backend services
- ✅ Duration: ~1.5 hours
- ✅ File: assets/css/document-generate.css
- ✅ Must match V3 dark theme

**3. Confirm Deliverables:**
- [ ] Card styles (4 states, hover effects, premium overlay)
- [ ] Modal styles (field groups, inline editors, timeline)
- [ ] Panel styles (widget cards, stats, links/buttons)
- [ ] Responsive design (tablet/mobile breakpoints)

**4. Start Only After:**
- Phase 3 PHP templates exist ✓
- Read plan lines 2050-2200 ✓
- User confirmed proceed ✓

---

**Phase 4 Implementation:**

1. **Card styles**
   - Active/inactive states
   - Hover effects
   - Premium overlay

2. **Modal styles**
   - Field groups
   - Inline editors
   - Timeline

3. **Panel styles**
   - Widget cards
   - Stats displays
   - Links/buttons

4. **Responsive**
   - Tablet layout
   - Mobile stack

---

### Phase 5: Interactions (JavaScript)
**Files:** assets/js/document-generate.js
**Duration:** ~3 hours (extended from 2 hours for error recovery)
**Prerequisites:** Phase 3 (UI templates) and Phase 4 (CSS) complete

#### ⚠️ BEFORE STARTING PHASE 5 - ESSENTIAL VERIFICATION STEPS:

**1. Read Plan Document First (5 minutes):**
```bash
# Read Phase 5 scope in full
read_file legaldocs-tab-plan.md lines 2050-2400
```

**2. Verify Phase Scope:**
- ✅ Phase 5 is: JavaScript interactions and AJAX calls
- ✅ Phase 5 is NOT: Templates, CSS, backend services
- ✅ Duration: ~3 hours
- ✅ Line range: 2050-2400
- ✅ File: assets/js/document-generate.js

**3. Confirm Deliverables:**
- [ ] Card click handlers (generate, regenerate, view, edit)
- [ ] Review modal open/close with AJAX loading
- [ ] Validation error display (missing mandatory fields)
- [ ] Preview generation with timeout/retry
- [ ] Document generation with progress tracking
- [ ] History modal timeline interactions
- [ ] Field checkbox toggles and inline editing
- [ ] Copy shortcode functionality
- [ ] Error handling and recovery
- [ ] Toast notifications

**4. Ask User Before Implementation:**
"Phase 5 (lines 2050-2400) requires implementing:
- JavaScript event handlers for all UI interactions
- AJAX calls to backend (slos_gen_get_context, slos_gen_preview, slos_gen_generate)
- Error handling with timeout/retry logic
- Modal management and field validation display
This is client-side JavaScript, not backend or templates.
Estimated: ~3 hours. Proceed with Phase 5? (yes/no)"

**5. DO NOT:**
- ❌ Create backend AJAX handlers (those are in Phase 1)
- ❌ Create CSS styles (that's Phase 4)
- ❌ Create templates (that's Phase 3)
- ❌ Modify backend services

**6. Start Implementation Only After:**
- Read plan lines 2050-2400 ✓
- Phase 3 UI templates exist ✓
- Phase 4 CSS exists ✓
- Phase 1 AJAX handlers exist ✓
- User confirmed "yes" to proceed ✓

---

**Phase 5 Implementation:**

#### 5A. Card Interactions

**Generate button flow:**
```javascript
$('.slos-gen-card__generate').on('click', function(e) {
    e.preventDefault();
    const docType = $(this).data('doc-type');
    
    // Disable button, show loading
    $(this).prop('disabled', true).text('Loading...');
    
    // Load review modal with retry logic
    SLOSDocGen.openReviewModal(docType)
        .catch(error => {
            SLOSDocGen.showError('Failed to load modal. Please try again.');
            $(this).prop('disabled', false).text('⚡ Generate Document');
        });
});
```

#### 5B. Review Modal with Validation

**Modal load with mandatory field checking:**
```javascript
SLOSDocGen.openReviewModal = function(docType) {
    return new Promise((resolve, reject) => {
        // Show modal shell immediately
        $('#slos-gen-review-modal').fadeIn(200);
        $('#slos-gen-modal-content').html('<div class="loading">Loading profile data...</div>');
        
        // AJAX with timeout and retry
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'slos_gen_get_context',
                doc_type: docType,
                nonce: SLOSDocGen.nonces.modal
            },
            timeout: 10000, // 10 second timeout
            success: function(response) {
                if (!response.success) {
                    // Handle validation errors (missing mandatory fields)
                    if (response.data && response.data.missing_fields) {
                        SLOSDocGen.showMissingFieldsError(response.data.missing_fields);
                        reject(response);
                    } else {
                        SLOSDocGen.showError(response.data.message || 'Unknown error');
                        reject(response);
                    }
                    return;
                }
                
                // Render modal content
                SLOSDocGen.renderModalContent(response.data);
                resolve(response.data);
            },
            error: function(xhr, status, error) {
                if (status === 'timeout') {
                    SLOSDocGen.showError('Request timed out. Please try again.');
                } else if (xhr.status === 0) {
                    SLOSDocGen.showError('Network error. Check your connection.');
                } else {
                    SLOSDocGen.showError('Server error: ' + (xhr.responseJSON?.data?.message || error));
                }
                reject(error);
            }
        });
    });
};

/**
 * Show missing mandatory fields error
 */
SLOSDocGen.showMissingFieldsError = function(missingFields) {
    let html = '<div class="slos-gen-error-modal">';
    html += '<h2>⚠️ Required Fields Missing</h2>';
    html += '<p>Complete these required fields before generating documents:</p>';
    html += '<ul>';
    
    missingFields.forEach(field => {
        const profileUrl = admin_url + 'admin.php?page=slos-company-profile&step=' + field.step;
        html += '<li><strong>' + field.label + '</strong> (Step ' + field.step + ') ';
        html += '<a href="' + profileUrl + '" class="button button-small">Complete Now →</a></li>';
    });
    
    html += '</ul>';
    html += '<button class="button button-secondary" onclick="SLOSDocGen.closeModal()">Close</button>';
    html += '</div>';
    
    $('#slos-gen-modal-content').html(html);
};
```

#### 5C. Preview with Error Handling

**Preview button with retry and timeout:**
```javascript
$('#slos-gen-preview-btn').on('click', function(e) {
    e.preventDefault();
    
    const $btn = $(this);
    const docType = $('#slos-gen-doc-type').val();
    const overrides = SLOSDocGen.collectOverrides();
    
    // Disable button
    $btn.prop('disabled', true).text('⏳ Generating Preview...');
    
    // Show progress bar
    $('#slos-gen-preview-progress').show();
    
    // Timeout after 20 seconds
    const timeoutId = setTimeout(function() {
        SLOSDocGen.showError('Preview generation timed out. Try again or reduce profile complexity.');
        $btn.prop('disabled', false).text('👁 Preview');
        $('#slos-gen-preview-progress').hide();
    }, 20000);
    
    $.ajax({
        url: ajaxurl,
        method: 'POST',
        data: {
            action: 'slos_gen_preview',
            doc_type: docType,
            overrides: JSON.stringify(overrides),
            nonce: SLOSDocGen.nonces.modal
        },
        success: function(response) {
            clearTimeout(timeoutId);
            $('#slos-gen-preview-progress').hide();
            
            if (!response.success) {
                SLOSDocGen.showError(response.data.message || 'Preview generation failed');
                $btn.prop('disabled', false).text('👁 Preview');
                return;
            }
            
            // Show preview modal
            SLOSDocGen.showPreviewModal(response.data.html, response.data.word_count);
            $btn.prop('disabled', false).text('👁 Preview');
        },
        error: function(xhr, status, error) {
            clearTimeout(timeoutId);
            $('#slos-gen-preview-progress').hide();
            $btn.prop('disabled', false).text('👁 Preview');
            
            if (status === 'timeout') {
                SLOSDocGen.showError('Preview request timed out. Try reducing profile data.');
            } else {
                SLOSDocGen.showError('Preview failed: ' + (xhr.responseJSON?.data?.message || error));
            }
        }
    });
});
```

#### 5D. Generation with Duplicate Prevention

**Submit generation with loading state and duplicate prevention:**
```javascript
$('#slos-gen-submit-btn').on('click', function(e) {
    e.preventDefault();
    
    // Prevent double-submit
    if ($(this).hasClass('submitting')) {
        return false;
    }
    
    const $btn = $(this);
    const docType = $('#slos-gen-doc-type').val();
    const overrides = SLOSDocGen.collectOverrides();
    const changeReason = $('#slos-gen-change-reason').val() || 'Generated from profile';
    
    // Disable all modal buttons
    $('.slos-gen-modal button').prop('disabled', true);
    $btn.addClass('submitting').text('⏳ Generating...');
    
    // Show progress
    $('#slos-gen-progress').show();
    
    $.ajax({
        url: ajaxurl,
        method: 'POST',
        data: {
            action: 'slos_gen_generate',
            doc_type: docType,
            overrides: JSON.stringify(overrides),
            change_reason: changeReason,
            nonce: SLOSDocGen.nonces.modal
        },
        timeout: 30000, // 30 second timeout
        success: function(response) {
            $('#slos-gen-progress').hide();
            
            if (!response.success) {
                SLOSDocGen.showError(response.data.message || 'Generation failed');
                $('.slos-gen-modal button').prop('disabled', false);
                $btn.removeClass('submitting').text('⚡ Generate as Draft');
                return;
            }
            
            // Success - show notice and redirect
            SLOSDocGen.showSuccess(response.data.message);
            SLOSDocGen.closeModal();
            
            // Redirect to edit page after 1 second
            setTimeout(function() {
                window.location.href = response.data.edit_url;
            }, 1000);
        },
        error: function(xhr, status, error) {
            $('#slos-gen-progress').hide();
            $('.slos-gen-modal button').prop('disabled', false);
            $btn.removeClass('submitting').text('⚡ Generate as Draft');
            
            if (status === 'timeout') {
                SLOSDocGen.showError('Generation timed out. Document may be large. Check Documents list.');
            } else if (xhr.status === 0) {
                SLOSDocGen.showError('Network error. Check your connection and try again.');
            } else {
                SLOSDocGen.showError('Generation failed: ' + (xhr.responseJSON?.data?.message || error));
            }
        }
    });
});
```

#### 5E. History Modal with Restore Confirmation

**Version restore with confirmation:**
```javascript
$('.slos-gen-version-restore').on('click', function(e) {
    e.preventDefault();
    
    const versionId = $(this).data('version-id');
    const versionNum = $(this).data('version-num');
    
    // Confirmation dialog
    if (!confirm(`Restore version ${versionNum}? This will create a new draft from that version's content.`)) {
        return;
    }
    
    const $btn = $(this);
    $btn.prop('disabled', true).text('Restoring...');
    
    $.ajax({
        url: ajaxurl,
        method: 'POST',
        data: {
            action: 'slos_gen_restore',
            version_id: versionId,
            nonce: SLOSDocGen.nonces.history
        },
        success: function(response) {
            if (!response.success) {
                SLOSDocGen.showError(response.data.message || 'Restore failed');
                $btn.prop('disabled', false).text('Restore');
                return;
            }
            
            SLOSDocGen.showSuccess(response.data.message);
            
            // Redirect to edit page
            setTimeout(function() {
                window.location.href = response.data.edit_url;
            }, 1000);
        },
        error: function(xhr, status, error) {
            $btn.prop('disabled', false).text('Restore');
            SLOSDocGen.showError('Restore failed: ' + (xhr.responseJSON?.data?.message || error));
        }
    });
});
```

#### 5F. Global Error Handling

**Centralized error display:**
```javascript
SLOSDocGen.showError = function(message) {
    // Remove any existing notices
    $('.slos-gen-notice').remove();
    
    // Create error notice
    const $notice = $('<div class="slos-gen-notice slos-gen-notice--error">')
        .html('<span class="dashicons dashicons-warning"></span> ' + message)
        .appendTo('#slos-gen-notices');
    
    // Auto-dismiss after 8 seconds
    setTimeout(function() {
        $notice.fadeOut(300, function() {$(this).remove();});
    }, 8000);
};

SLOSDocGen.showSuccess = function(message) {
    $('.slos-gen-notice').remove();
    
    const $notice = $('<div class="slos-gen-notice slos-gen-notice--success">')
        .html('<span class="dashicons dashicons-yes"></span> ' + message)
        .appendTo('#slos-gen-notices');
    
    setTimeout(function() {
        $notice.fadeOut(300, function() {$(this).remove();});
    }, 5000);
};

// Handle global AJAX errors
$(document).ajaxError(function(event, xhr, settings, thrownError) {
    // Only handle our AJAX requests
    if (settings.url === ajaxurl && settings.data && settings.data.action.startsWith('slos_gen_')) {
        console.error('SLOS AJAX Error:', {xhr, settings, thrownError});
        
        // Don't show error if already handled by specific error callback
        if (!settings.suppressGlobalError) {
            SLOSDocGen.showError('Unexpected error. Please try again or contact support.');
        }
    }
});
```

#### Phase 5 Deliverables:

- [ ] Card interactions with loading states
- [ ] Review modal with mandatory field validation
- [ ] **Preview button** with timeout and error handling
- [ ] **Duplicate submission prevention** on generate button
- [ ] **AJAX retry logic** for network failures
- [ ] **Timeout handling** (10s modal, 15s preview, 30s generation)
- [ ] **Missing field errors** with direct links to profile steps
- [ ] **Confirmation dialogs** for destructive actions (restore version)
- [ ] **Progress indicators** for long operations
- [ ] **Centralized error display** with auto-dismiss
- [ ] **Network error detection** and user-friendly messages

### Phase 6: Integration & Testing
**Duration:** ~1.5 hours

1. **Tab registration** in DocumentsMainPage
2. **Asset enqueuing** on generate tab
3. **Error handling** for all AJAX
4. **Missing data graceful fallbacks**
5. **Cross-browser testing**

---

## 6. Data Flow Diagram

```
┌──────────────┐     ┌──────────────────┐     ┌─────────────────┐
│   User       │     │   Review Modal   │     │   Generator     │
│   clicks     │────▶│   loads profile  │────▶│   receives      │
│   Generate   │     │   data via AJAX  │     │   final data    │
└──────────────┘     └──────────────────┘     └─────────────────┘
                              │                        │
                              ▼                        ▼
                     ┌──────────────────┐     ┌─────────────────┐
                     │   User edits/    │     │   Load template │
                     │   excludes       │     │   from .html    │
                     │   fields         │     │   file          │
                     └──────────────────┘     └─────────────────┘
                              │                        │
                              ▼                        ▼
                     ┌──────────────────┐     ┌─────────────────┐
                     │   User clicks    │     │   Replace all   │
                     │   Generate       │────▶│   placeholders  │
                     └──────────────────┘     └─────────────────┘
                                                       │
                                                       ▼
                                              ┌─────────────────┐
                                              │   Save new      │
                                              │   version to DB │
                                              └─────────────────┘
                                                       │
                                                       ▼
                                              ┌─────────────────┐
                                              │   Redirect to   │
                                              │   Edit page     │
                                              └─────────────────┘
```

---

## 7. Database Architecture & Flow

### Existing Tables (No Schema Changes)

| Table | Purpose | Usage in Feature |
|-------|---------|------------------|
| `wp_slos_company_profile` | Stores wizard data (60+ fields) | **Read:** Load profile for review modal<br>**Never written** by generator |
| `wp_slos_legal_docs` | Stores documents (content, metadata, status) | **Write:** Save/update generated document |
| `wp_slos_legal_doc_versions` | Stores version history | **Write:** Create new version entry<br>**Read:** Load timeline/restore |

### Complete Data Flow

```
1. Profile Read
   wp_slos_company_profile → Company_Profile_Repository::get_profile()
   ↓
2. Review Modal Display (AJAX)
   Profile data → JSON → Modal UI
   ↓
3. User Edits (Client-side only)
   Temporary edits in modal (NOT saved to profile table)
   ↓
4. Template Merge (Server-side)
   HTML template + profile data + user overrides → final document HTML
   ↓
5. Document Save/Update
   → wp_slos_legal_docs (content, metadata, type, locale, status)
   ↓
6. Version History Entry
   → wp_slos_legal_doc_versions (version_num, content, metadata)
   ↓
7. Version Metadata (JSON)
   Store: profile_version, excluded_fields, overrides, changelog
   ↓
8. Redirect to Editor
   User can further customize generated document
```

### Version Metadata Structure

```json
{
  "version": 3,
  "generated_at": "2025-12-23T10:30:00Z",
  "generated_from": "auto_generator",
  "profile_version": 5,
  "profile_completion": 85,
  "excluded_fields": ["company.vat_number", "contacts.phone"],
  "overrides": {
    "company.trading_name": "Custom Trading Name"
  },
  "missing_fields": ["contacts.dpo.phone", "legal.supervisory_authority"],
  "changelog": "Regenerated with updated DPO contact",
  "template_version": "1.0"
}
```

### Architecture Assessment

**✅ Strengths of Current Approach:**
- No database schema changes required
- Existing version system handles incremental generations perfectly
- Profile remains single source of truth
- User overrides preserved per version for audit trail
- Metadata JSON provides flexibility for future fields
- Existing repositories handle all CRUD operations

**❌ Potential Limitations:**
- JSON metadata means no SQL queries like "which documents exclude field X"
- Can't easily report on override patterns across documents
- Complex queries require deserializing all version metadata

**Alternative Architecture (if needed later):**
```sql
CREATE TABLE wp_slos_document_generation_context (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  doc_id BIGINT UNSIGNED NOT NULL,
  version_id BIGINT UNSIGNED NOT NULL,
  profile_version INT NOT NULL,
  excluded_fields TEXT, -- Serialized array
  overrides TEXT, -- Serialized array
  generated_at DATETIME NOT NULL,
  INDEX idx_doc_version (doc_id, version_id)
);
```

**Recommendation:** 
Current approach is **optimal for MVP** and scales well for typical usage (hundreds of documents). 

Only consider dedicated table if:
- Need complex reporting on generation patterns
- User base exceeds 10,000+ active sites
- Require real-time analytics on field usage

For this release, **stick with existing tables** - simpler, proven, and sufficient.

---

## 8. Best Practices & Quality Standards

### A. Legal Content Quality

**CRITICAL: Professional Legal Drafting Required**
- [ ] **All template blocks must be professionally drafted legal content**
- [ ] Use formal legal language, proper citations, and accurate terminology
- [ ] Ensure legal compliance with current regulations (GDPR, CCPA/CPRA, LGPD)
- [ ] Include proper legal disclaimers and effective date provisions
- [ ] Reference specific statutory provisions ("Article 6 GDPR", "§1798.100 CCPA", "Article 7 LGPD")
- [ ] Use standard legal document structure (definitions, rights, obligations, remedies)
- [ ] Avoid placeholder legal text - use actual lawyer-quality content that reads like an attorney drafted it
- [ ] **Critical:** Include prominent disclaimer that users must seek legal counsel for review

**Legal Text Standards:**
- [ ] Each block should be complete, coherent legal prose
- [ ] Use proper legal formatting (numbered sections, lettered subsections)
- [ ] Include cross-references where applicable
- [ ] Maintain consistent terminology throughout
- [ ] Date all legal provisions appropriately

**Translation Requirements:**
- [ ] Legal template content should NOT be auto-translated
- [ ] Provide professionally translated versions per jurisdiction
- [ ] Consider jurisdictional variations (e.g., UK vs. EU GDPR interpretations)

**Quality Assurance:**
- [ ] Legal content review by qualified attorney (recommended)
- [ ] Regular updates when laws change
- [ ] Version control for legal text changes
- [ ] Changelog tracking regulatory updates

### B. Security (OWASP Top 10)

**Authentication & Authorization:**
- [ ] Check `manage_options` capability on all admin endpoints
- [ ] Verify `current_user_can('manage_shahi_template')` for document operations
- [ ] AJAX actions require valid nonce: `check_ajax_referer('slos_gen_nonce')`
- [ ] Nonce timeout: 12 hours (WordPress default)
- [ ] No guest user access to generation features

**Input Validation:**
- [ ] Sanitize all user input: `sanitize_text_field()`, `sanitize_email()`, etc.
- [ ] Validate data types before processing (int, email, URL)
- [ ] Whitelist allowed HTML in rich content: `wp_kses_post()`
- [ ] Reject malformed JSON payloads

**Output Escaping:**
- [ ] Escape all output: `esc_html()`, `esc_attr()`, `esc_url()`
- [ ] Use `wp_json_encode()` for JSON output
- [ ] Content Security Policy headers for modal iframes

**SQL Injection Prevention:**
- [ ] Use `$wpdb->prepare()` for all custom queries
- [ ] Repository classes already use prepared statements
- [ ] Never concatenate user input into queries

**XSS Prevention:**
- [ ] User-edited modal content sanitized before generation
- [ ] Strip `<script>` tags from template overrides
- [ ] Validate placeholder syntax: `/^[a-z0-9_\.]+$/i`
- [ ] **Shortcode output security:** Use `wp_kses_post()` when rendering document content

**Shortcode Security Implementation:**
```php
// In shortcode handler
function slos_legal_doc_shortcode($atts) {
    $atts = shortcode_atts([
        'type' => 'privacy-policy',
        'id' => 0,
    ], $atts);
    
    $doc_type = sanitize_text_field($atts['type']);
    $doc_id = intval($atts['id']);
    
    // Get document from repository
    $doc = $doc_id ? $this->doc_repository->get($doc_id) : $this->doc_repository->get_by_type($doc_type);
    
    if (!$doc || $doc['status'] !== 'published') {
        return '<!-- Document not found or not published -->';
    }
    
    // CRITICAL: Sanitize content before output
    // wp_kses_post allows safe HTML but strips dangerous tags
    $safe_content = wp_kses_post($doc['content']);
    
    // Additional security: Remove any JavaScript event attributes
    $safe_content = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $safe_content);
    
    // Wrap in container for styling
    return '<div class="slos-legal-document slos-legal-document--' . esc_attr($doc_type) . '">' 
           . $safe_content 
           . '</div>';
}
```

### C. Performance Optimization

**Caching Strategy:**
- [ ] Cache parsed templates in transients (12 hours): `set_transient('slos_gen_tpl_{type}')`
- [ ] Cache profile data during modal session (10 minutes)
- [ ] Lazy load right panel widgets (AJAX on demand)
- [ ] Debounce inline edit save (500ms delay)

**Database Optimization:**
- [ ] Index on `doc_type` and `locale` columns
- [ ] Limit version history queries: `LIMIT 20`
- [ ] Use `wp_cache_*` functions for repeated profile reads
- [ ] Single query for stats bar (join vs multiple queries)

**Asset Loading:**
- [ ] Only enqueue CSS/JS on Generate tab: `if ($current_tab === 'generate')`
- [ ] Minify CSS/JS in production
- [ ] Defer non-critical JavaScript
- [ ] Use `wp_add_inline_script()` for small inline JS

**Generation Performance:**
- [ ] Stream large templates instead of loading entirely in memory
- [ ] Process placeholders in chunks (100 at a time)
- [ ] Show progress bar for templates >100KB
- [ ] Timeout protection: max 30 seconds per generation

### D. Accessibility (WCAG 2.1 Level AA)

**Keyboard Navigation:**
- [ ] All interactive elements focusable (tab order logical)
- [ ] Modal trappable with Esc to close
- [ ] Card buttons accessible via Enter/Space
- [ ] Skip link to main content

**Screen Readers:**
- [ ] ARIA labels on all icon-only buttons: `aria-label="Generate document"`
- [ ] ARIA live regions for dynamic content: `aria-live="polite"`
- [ ] Form field associations: `<label for="...">`
- [ ] Status announcements: "Document generated successfully"

**Visual:**
- [ ] Color contrast ratio 4.5:1 minimum (text)
- [ ] Focus indicators visible (2px outline)
- [ ] Text resizable up to 200% without breaking layout
- [ ] No content relies solely on color (use icons + text)

**Semantic HTML:**
- [ ] Use `<button>` not `<div>` for buttons
- [ ] Use `<nav>` for tab navigation
- [ ] Use `<section>` for card groups
- [ ] Proper heading hierarchy (h1 → h2 → h3)

### E. Error Handling & Recovery

**Error Scenarios:**
```php
// 1. Profile incomplete
if ($missing_mandatory_fields) {
    return new WP_Error('incomplete_profile', __('Required fields missing'));
}

// 2. Template not found
if (!file_exists($template_path)) {
    error_log("Template missing: $template_path");
    return new WP_Error('template_missing', __('Template file not found'));
}

// 3. Generation timeout
set_time_limit(30);
register_shutdown_function('handle_generation_timeout');

// 4. Database failure
if (!$saved) {
    wp_mail($admin_email, 'Generation Failed', $error_details);
    return new WP_Error('db_error', __('Failed to save document'));
}

// 5. Invalid placeholders
if (preg_match('/\{\{[^}]+\}\}/', $final_content)) {
    return new WP_Error('unresolved_placeholders', __('Some data missing'));
}
```

**User-Facing Messages:**
```javascript
// Success
showNotice('success', 'Privacy Policy generated successfully!');

// Warning
showNotice('warning', '3 optional fields were excluded');

// Error
showNotice('error', 'Generation failed. Please try again or contact support.');

// Progress
showProgress('Generating document... 50%');
```

**Rollback Strategy:**
- [ ] Wrap generation in database transaction
- [ ] On failure, revert to previous version
- [ ] Store failed generation logs for debugging
- [ ] Email admin on repeated failures

### F. Internationalization (i18n)

**Text Domain:**
- [ ] All strings use `'shahi-legalops-suite'` text domain
- [ ] Use `__()`, `_e()`, `esc_html__()` consistently
- [ ] No hardcoded English strings
- [ ] JavaScript strings via `wp_localize_script()`

**RTL Support:**
- [ ] Load RTL stylesheet for RTL languages: `wp_style_add_data('slos-gen', 'rtl', 'replace')`
- [ ] Test layout with Arabic/Hebrew
- [ ] Icons/arrows flip direction

**Date/Time:**
- [ ] Use WordPress date formats: `get_option('date_format')`
- [ ] Timezone-aware: `current_time('mysql')`
- [ ] Localized date display: `date_i18n()`

**Legal Content Translation:**
- [ ] **Legal template content should NOT be auto-translated**
- [ ] Provide professionally translated legal text per jurisdiction
- [ ] Consider jurisdictional variations (UK vs EU GDPR, etc.)
- [ ] Maintain separate template files per language/jurisdiction

### G. Code Quality & Maintainability

**Documentation:**
```php
/**
 * Generate legal document from profile data
 *
 * @since 4.2.0
 * @param string $doc_type Document type (privacy-policy, terms, cookie-policy)
 * @param array  $overrides User overrides from review modal
 * @return int|WP_Error Document ID on success, WP_Error on failure
 */
public function generate_document($doc_type, $overrides = []) {
    // Implementation
}
```

**Type Declarations:**
```php
// PHP 7.4+ type hints
public function get_profile(): array
public function save_document(int $doc_id, string $content): bool
public function calculate_stats(): ?array
```

**Error Logging:**
```php
if (WP_DEBUG) {
    error_log("[SLOS] Generation failed: " . $error->get_error_message());
}
```

**Unit Tests (Future):**
- [ ] Test placeholder replacement logic
- [ ] Test profile data sanitization
- [ ] Test version increment logic
- [ ] Test permission checks

### G. Monitoring & Analytics

**Success Metrics:**
```javascript
// Track with WordPress heartbeat or custom endpoint
{
  generations_today: 15,
  success_rate: 0.93,
  avg_generation_time: 2.3, // seconds
  most_generated: 'privacy-policy',
  profile_completion_avg: 78
}
```

**Error Tracking:**
- [ ] Log generation failures with context
- [ ] Weekly digest email to admin
- [ ] Dashboard widget showing error rate

**User Behavior:**
- [ ] Track which fields most commonly excluded
- [ ] Track regeneration frequency
- [ ] Track version restore actions

### H. Compatibility Checklist

**WordPress:**
- [ ] Minimum version: 5.8
- [ ] Test with 6.0, 6.1, 6.2, 6.3, 6.4
- [ ] Compatible with Multisite
- [ ] Works with WordPress.com (if applicable)

**PHP:**
- [ ] Minimum version: 7.4
- [ ] Test with 8.0, 8.1, 8.2
- [ ] No deprecated functions
- [ ] Graceful degradation for missing extensions

**Browsers:**
- [ ] Chrome 90+
- [ ] Firefox 88+
- [ ] Safari 14+
- [ ] Edge 90+
- [ ] Mobile Safari (iOS 14+)
- [ ] Chrome Mobile (Android 10+)

**Plugins:**
- [ ] No conflicts with popular caching plugins (WP Rocket, W3TC)
- [ ] Compatible with Yoast SEO
- [ ] Compatible with WooCommerce (if e-commerce detected)
- [ ] Compatible with popular page builders (Elementor, Divi)

**Themes:**
- [ ] Test with Twenty Twenty-Four
- [ ] Test with popular themes (Astra, GeneratePress)
- [ ] Admin styles don't leak to frontend
- [ ] Responsive on all devices

---

## 8.1 Template Versioning & Update Strategy

### Template Version Management

**Problem:** When GDPR law changes and legal text blocks need updating, how to deploy new templates without breaking existing documents?

**Solution:** Template versioning system with migration path

#### A. Template Version Tracking

**Store template version in wp_options:**
```php
// On plugin activation or template update
update_option('slos_legal_templates_version', time()); // Unix timestamp as version

// Track template file hashes to detect changes
$template_hashes = [
    'privacy-policy.html' => md5_file(SLOS_TEMPLATE_DIR . 'privacy-policy.html'),
    'blocks/gdpr/data-subject-rights.html' => md5_file(SLOS_TEMPLATE_DIR . 'blocks/gdpr/data-subject-rights.html'),
    // ... all template files
];
update_option('slos_template_hashes', $template_hashes);
```

**Store template version used in each document:**
```php
// In Document_Generator::generate_from_profile()
$metadata = [
    // ... other metadata ...
    'template_version' => get_option('slos_legal_templates_version', 1),
    'template_files_used' => [
        'master' => 'privacy-policy.html',
        'blocks' => ['blocks/gdpr/data-subject-rights.html', 'blocks/common/intro-section.html']
    ],
];
```

#### B. Template Update Detection

**Check for template updates on admin page load:**
```php
// In DocumentsMainPage.php or admin notice hook
add_action('admin_notices', function() {
    // Only show on Documents pages
    if (!isset($_GET['page']) || strpos($_GET['page'], 'slos-') !== 0) {
        return;
    }
    
    // Check if templates have been updated
    $current_version = get_option('slos_legal_templates_version', 1);
    $last_checked_version = get_user_meta(get_current_user_id(), 'slos_template_update_dismissed', true);
    
    if ($current_version > $last_checked_version) {
        // Find documents using old templates
        $outdated_docs = $this->find_docs_with_outdated_templates();
        
        if (!empty($outdated_docs)) {
            echo '<div class="notice notice-warning is-dismissible" data-notice="template-update">';
            echo '<h3>⚠️ Legal Template Updates Available</h3>';
            echo '<p><strong>' . count($outdated_docs) . ' documents</strong> were generated with older legal templates. ';
            echo 'Laws and regulations may have changed. Consider regenerating these documents:</p>';
            echo '<ul>';
            foreach ($outdated_docs as $doc) {
                echo '<li>' . esc_html($doc['title']) . ' <a href="' . admin_url('admin.php?page=slos-documents&tab=generate') . '" class="button button-small">Regenerate</a></li>';
            }
            echo '</ul>';
            echo '<p><a href="#" class="slos-dismiss-template-notice" data-version="' . $current_version . '">Dismiss this notice</a></p>';
            echo '</div>';
        }
    }
});

// Find documents using outdated templates
private function find_docs_with_outdated_templates() {
    global $wpdb;
    $table = $wpdb->prefix . 'slos_legal_docs';
    $current_version = get_option('slos_legal_templates_version', 1);
    
    $outdated_docs = $wpdb->get_results($wpdb->prepare("
        SELECT id, title, doc_type, metadata
        FROM {$table}
        WHERE status = 'published'
    "), ARRAY_A);
    
    $outdated = [];
    foreach ($outdated_docs as $doc) {
        $metadata = json_decode($doc['metadata'], true);
        $doc_template_version = $metadata['template_version'] ?? 0;
        
        if ($doc_template_version < $current_version) {
            $outdated[] = $doc;
        }
    }
    
    return $outdated;
}
```

#### C. Template Update Workflow

**When legal templates need updating:**

1. **Update Template Files**
   - Edit template HTML files with new legal requirements
   - Update block files with new regulatory language
   - Document changes in CHANGELOG

2. **Increment Template Version**
   ```php
   // In plugin update or manual admin action
   update_option('slos_legal_templates_version', time());
   ```

3. **Admin Notice Appears**
   - Shows on Documents pages
   - Lists all documents using old templates
   - Provides "Regenerate" buttons

4. **User Regenerates Documents**
   - Click regenerate on each document
   - Review modal shows updated content via preview
   - User approves and generates new draft
   - User reviews draft and publishes

5. **Migration Script (Optional)**
   ```php
   // For bulk regeneration (use with caution)
   add_action('admin_init', function() {
       if (!isset($_GET['slos_bulk_regenerate']) || !current_user_can('manage_options')) {
           return;
       }
       
       check_admin_referer('slos_bulk_regenerate');
       
       $doc_types = ['privacy-policy', 'terms-of-service', 'cookie-policy'];
       $results = [];
       
       foreach ($doc_types as $doc_type) {
           $result = $this->document_generator->generate_from_profile($doc_type, [], get_current_user_id());
           $results[$doc_type] = !is_wp_error($result);
       }
       
       // Log results
       update_option('slos_last_bulk_regeneration', [
           'timestamp' => current_time('mysql'),
           'results' => $results,
           'user_id' => get_current_user_id(),
       ]);
       
       wp_redirect(admin_url('admin.php?page=slos-documents&regenerated=1'));
       exit;
   });
   ```

#### D. Backward Compatibility

**Handling Breaking Changes:**

If template syntax changes require code updates:

```php
// In Placeholder_Mapper or Document_Generator
public function parse_template($template, $data, $template_version = null) {
    $template_version = $template_version ?? get_option('slos_legal_templates_version', 1);
    
    // Version-specific parsing
    if ($template_version < 1620000000) { // Before Dec 2021
        // Use old placeholder syntax
        return $this->parse_template_v1($template, $data);
    } elseif ($template_version < 1672531200) { // Before Jan 2023
        // Use intermediate syntax
        return $this->parse_template_v2($template, $data);
    } else {
        // Use current syntax
        return $this->parse_template_v3($template, $data);
    }
}
```

#### E. Template Change Log

**Maintain changelog in template directory:**

```
templates/legaldocs/CHANGELOG.md

# Legal Template Changelog

## Version 1672531200 (Jan 1, 2023)
- **GDPR:** Updated data subject rights to reflect EDPB guidance on right to erasure
- **CCPA:** Added CPRA amendments for sensitive personal information
- **Common:** Updated contact info block to require response timeframes

### Breaking Changes
- None

### Migration Required
- Regenerate all privacy policies for GDPR/CCPA businesses

## Version 1640995200 (Jan 1, 2022)
- **GDPR:** Initial template creation
- **CCPA:** Initial template creation
- **Terms:** Initial template creation
```

#### Template Update Deliverables:

- [ ] Template version tracking in wp_options
- [ ] Template version stored in document metadata
- [ ] Admin notice for outdated documents
- [ ] Dismiss notice functionality with user meta
- [ ] find_docs_with_outdated_templates() method
- [ ] Template changelog documentation
- [ ] Backward compatibility for breaking changes
- [ ] Optional bulk regeneration script (with safeguards)

---

## 9. Error Prevention Checklist

### Code Quality
- [ ] No duplicate function names (use namespace + unique names)
- [ ] No duplicate CSS class names (prefix all with `slos-gen-`)
- [ ] No duplicate AJAX action names (prefix with `slos_gen_`)
- [ ] No duplicate nonce names (use unique identifiers)
- [ ] All PHP files have proper namespace
- [ ] All files check `ABSPATH`
- [ ] PHPStan level 5 compliance (static analysis)
- [ ] PHP CodeSniffer WordPress standards

### Data Safety
- [ ] All user input sanitized before processing
- [ ] All output escaped (context-appropriate escaping)
- [ ] AJAX requests verify nonce and capability
- [ ] Database queries use prepared statements
- [ ] No eval() or similar dangerous functions
- [ ] File uploads validated (if applicable)

### UX Safety
- [ ] Confirm before overwriting existing document
- [ ] Confirm before restoring old version
- [ ] Show loading spinners on all AJAX operations
- [ ] Show clear error messages on failure
- [ ] Graceful degradation if JavaScript disabled
- [ ] Timeout warnings for long operations
- [ ] Prevent double-submit (disable button after click)

### Compatibility
- [ ] PHP 7.4+ syntax only (no PHP 8-only features yet)
- [ ] WordPress 5.8+ APIs only
- [ ] No jQuery deprecated methods
- [ ] No jQuery 3.x breaking changes
- [ ] Test with popular themes
- [ ] Test with caching plugins

---

## 9. File Naming Conventions

| Type | Convention | Example |
|------|------------|---------|
| PHP Templates | `kebab-case.php` | `tab-generate.php` |
| PHP Partials | `partials/kebab-case.php` | `partials/document-card.php` |
| PHP Classes | `PascalCase.php` | `Document_Generator.php` |
| CSS Files | `kebab-case.css` | `document-generate.css` |
| JS Files | `kebab-case.js` | `document-generate.js` |
| HTML Templates | `kebab-case.html` | `privacy-policy.html` |
| CSS Classes | `slos-gen-*` | `slos-gen-card` |
| AJAX Actions | `slos_gen_*` | `slos_gen_preview` |
| Nonces | `slos_gen_*_nonce` | `slos_gen_modal_nonce` |

---

## 10. Estimated Timeline

| Phase | Duration | Dependencies |
|-------|----------|--------------|
| Phase 1: Backend | 2 hours | None |
| Phase 2: Templates | 3 hours | Phase 1 |
| Phase 3: UI | 3 hours | Phase 1 |
| Phase 4: CSS | 1.5 hours | Phase 3 |
| Phase 5: JS | 2 hours | Phase 3, 4 |
| Phase 6: Integration | 1.5 hours | All above |
| **Total** | **13 hours** | |

---

## 11. Estimated Timeline (UPDATED with Gap Fixes)

| Phase | Duration | Dependencies | Key Additions |
|-------|----------|--------------|---------------|
| **Phase 0: Preparation** | **1.5 hours** | **None - START HERE** | Feature flag, profile version tracking, rollback setup |
| **Phase 1: Backend** | **4 hours** | Phase 0 complete | Settings tab, validation, preview, audit trail, version pruning |
| **Phase 2: Templates & Legal Text** | **5 hours** | Phase 1, **Legal text source acquired** | Legal sourcing, disclaimer, compliance blocks |
| Phase 3: UI | 3 hours | Phase 1 | Preview modal, missing field errors |
| Phase 4: CSS | 1.5 hours | Phase 3 | Error states, loading states |
| **Phase 5: JS** | **3 hours** | Phase 3, 4 | Error recovery, timeouts, retry logic, duplicate prevention |
| Phase 6: Integration | 1.5 hours | All above | Testing all gaps resolved |
| **Total** | **19.5 hours** | | **Extended from 16 hours** |

**Critical Path:** 
1. **Determine legal text source** (blocker for Phase 2)
2. Phase 0 → Phase 1 (4h)
3. Phase 2/3 parallel (5h max if parallel)
4. Phase 4/5 parallel (3h max if parallel)
5. Phase 6 (1.5h)

**Minimum timeline:** ~15 hours (with parallelization)
**Safe timeline:** 19.5 hours (sequential)

**CRITICAL:** Cannot proceed past Phase 1 without securing legal text source (licensed templates or attorney-drafted content).

**Phase 2 Note:** Legal text drafting (4 hours) can be done by legal content specialist in parallel with Phase 3-5 if licensed templates are acquired or attorney drafts provided.

**Extension Reasons:**
- Phase 0: +30min for feature flag and profile version tracking
- Phase 1: +2h for Settings tab, validation service, preview method, audit trail
- Phase 5: +1h for comprehensive error recovery, timeout handling, retry logic

---

## 12. Success Criteria

**Phase 0 Success:**
- [ ] No naming conflicts detected
- [ ] All infrastructure verified
- [ ] Sample data ready for testing
- [ ] **Feature flag implemented** for safe rollback
- [ ] **Profile version tracking** operational

**Phase 1 Success:**
- [ ] **Settings tab created** with compliance framework options
- [ ] **Mandatory field validation** blocks generation when fields missing
- [ ] Placeholder_Mapper supports full syntax (conditionals, loops, filters, includes)
- [ ] Document_Generator has generate_from_profile() and generate_preview()
- [ ] **All documents generated as DRAFT** (never auto-publish)
- [ ] **Legal disclaimer** added to all generated documents
- [ ] **Audit trail** captured in version metadata (author, timestamp, reason)
- [ ] **Version pruning** prevents database bloat (keeps latest 10)
- [ ] **Regeneration creates new draft** (never replaces published)
- [ ] **Profile version comparison** detects outdated documents
- [ ] All AJAX handlers with proper authentication/nonces

**Phase 2 Success:**
- [ ] **Legal text source determined** (licensed templates, legal counsel, or vetted source)
- [ ] All compliance blocks drafted in professional legal language (lawyer-quality)
- [ ] Legal content reviewed for accuracy and completeness
- [ ] Proper legal citations and terminology used throughout
- [ ] Settings tab compliance options functional
- [ ] Template assembly logic working
- [ ] Block includes conditionally loading based on Settings
- [ ] **Legal disclaimer prominent** in generated docs and UI

**Phase 5 Success:**
- [ ] **Preview button** shows document before committing
- [ ] **Mandatory field errors** show specific missing fields with links to profile steps
- [ ] **Duplicate submission prevention** on all forms
- [ ] **AJAX timeout handling** (10s modal, 15s preview, 30s generation)
- [ ] **Network error recovery** with user-friendly messages
- [ ] **Retry logic** for failed requests
- [ ] **Confirmation dialogs** for destructive actions
- [ ] **Loading states** on all buttons during async operations

**Implementation Success - Core Features:**
1. ✅ Generate tab appears in Documents page (with feature flag)
2. ✅ 3 core document cards display correctly
3. ✅ 6 premium cards show locked state
4. ✅ Stats bar shows accurate counts
5. ✅ Review modal loads all profile data
6. ✅ **Mandatory field validation blocks generation with specific errors**
7. ✅ **Preview button shows full document before generating**
8. ✅ User can edit/exclude fields in modal
9. ✅ **Legal disclaimer visible in modal and document footer**
10. ✅ Generation creates new **DRAFT** version with correct compliance blocks
11. ✅ **Regeneration always creates new draft, never replaces published**
12. ✅ History modal shows version timeline
13. ✅ Restore version works correctly (creates new draft)
14. ✅ **Outdated detection** compares profile versions
15. ✅ Right panel shows relevant info
16. ✅ All actions work without JS errors
17. ✅ All actions work without PHP errors
18. ✅ Responsive on tablet/mobile
19. ✅ Accessible (keyboard nav, ARIA)

**Implementation Success - Security & Quality:**
20. ✅ **Shortcode output sanitized** with wp_kses_post()
21. ✅ All AJAX with nonce verification
22. ✅ All inputs sanitized
23. ✅ All outputs escaped
24. ✅ **Audit trail** complete (who, when, why for each version)
25. ✅ **Template version tracking** operational
26. ✅ **Admin notices** for outdated documents after template updates
27. ✅ **Feature flag** allows instant disable if issues found

**Critical Gaps Resolved:**
✅ Gap 1: Preview before generation - Preview button implemented
✅ Gap 2: Legal text sourcing - Must determine source before Phase 2C
✅ Gap 3: Draft/publish workflow - Always generates as draft
✅ Gap 4: Mandatory field validation - Blocks with specific error messages
✅ Gap 5: Outdated detection - Profile version comparison
✅ Gap 6: Placeholder syntax - Complete specification documented
✅ Gap 7: Version storage - Pruning keeps latest 10
✅ Gap 8: Settings tab - Implemented with compliance checkboxes
✅ Gap 9: Shortcode security - wp_kses_post() sanitization
✅ Gap 10: Rollback plan - Feature flag for instant disable
✅ Gap 11: Legal disclaimer - Prominent in modal, document footer, first-time notice
✅ Gap 12: Regeneration safety - Always creates draft, never replaces published
✅ Gap 13: AJAX error recovery - Timeout, retry, network error handling
✅ Gap 14: Audit trail - Author, timestamp, reason in version metadata
✅ Gap 15: Template updates - Versioning, admin notices, migration path

---

## 13. Critical Pre-Implementation Checklist

**MUST COMPLETE before starting Phase 1:**

- [ ] **Legal text source secured** - Cannot proceed without attorney-quality legal content
- [ ] Budget approved for legal template licensing or attorney engagement
- [ ] Legal counsel contact established (for ongoing template updates)
- [ ] Feature flag strategy confirmed with stakeholders
- [ ] Backup/restore procedure tested (in case rollback needed)
- [ ] Plugin update mechanism supports template file updates
- [ ] Decision: Who will maintain legal templates when laws change?

**RECOMMENDED before Phase 0:**
- [ ] Code audit of existing Document_Generator and Placeholder_Mapper classes
- [ ] Review existing database schema for version history table capacity
- [ ] Confirm WordPress multisite compatibility requirements (if applicable)
- [ ] Establish legal template update SLA (how quickly to respond to law changes)

---

## 14. Future Enhancements (Out of Scope)

- Premium document templates (GDPR Notice, CCPA Notice, etc.)
- AI-powered content suggestions
- Multi-language document generation (professionally translated templates)
- PDF export with styling
- Document comparison view (diff between versions)
- Scheduled regeneration (cron job when profile changes)
- Webhook on document change (for integrations)
- White-label templates
- Document approval workflow (multi-user review before publish)
- A/B testing different legal text variations
- Analytics: which documents users view most

---

*Plan created: December 23, 2025*  
*Plan updated: December 23, 2025 (critical gaps addressed)*  
*Author: AI Assistant*  
*Status: Ready for Phase 0 (after legal text source determined)*  
*Next Step: **Determine legal text source** → Run Phase 0 conflict checks and infrastructure verification*


