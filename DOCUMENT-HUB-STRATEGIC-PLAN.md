# Strategic Implementation Plan: Legal Document Hub

> **Created:** December 22, 2025  
> **Version:** 1.0  
> **Status:** Active Development

---

## Executive Summary

Transform the Legal Documents module from a traditional create/edit flow to a modern **Document Hub** with:
- Card-based dashboard showing all document types
- Single multi-step questionnaire for company profile
- One-click document generation from profile + templates
- Easy regeneration when profile or templates change
- Version management for edits

---

## Phase Overview

| Phase | Name | Duration | Priority |
|-------|------|----------|----------|
| **Phase 1** | Database & Data Layer | Foundation | 🔴 Critical |
| **Phase 2** | Company Profile System | Core | 🔴 Critical |
| **Phase 3** | Document Hub UI | Core | 🔴 Critical |
| **Phase 4** | Generation Engine | Core | 🔴 Critical |
| **Phase 5** | Editor & Versioning | Important | 🟡 High |
| **Phase 6** | Export & Delivery | Important | 🟡 High |
| **Phase 7** | Polish & Integration | Enhancement | 🟢 Medium |

---

## Phase 1: Database & Data Layer

**Objective:** Create storage for company profile data and document metadata

### 1.1 New Database Tables

```sql
-- Company Profile Table
slos_company_profile
├── id (INT, PK, AUTO_INCREMENT)
├── profile_data (LONGTEXT, JSON)
├── completion_percentage (INT, DEFAULT 0)
├── version (INT, DEFAULT 1)
├── created_at (DATETIME)
├── updated_at (DATETIME)
└── updated_by (INT, user_id)
```

### 1.2 Legal Documents Table Modifications

Add/ensure these fields in existing legal_docs table:
- `status` (VARCHAR) - not_generated, draft, published
- `generated_from_profile_version` (INT)
- `version` (VARCHAR) - 1.0, 1.1, 2.0

### 1.3 Files to Create/Modify

| File | Action | Purpose |
|------|--------|---------|
| `includes/Database/Migrations/Migration_Company_Profile.php` | Create | Profile table migration |
| `includes/Database/Repositories/Company_Profile_Repository.php` | Create | CRUD for profile data |
| `includes/Database/Repositories/Legal_Doc_Repository.php` | Modify | Add status-based queries |

### 1.4 Deliverables
- [ ] Migration class for company profile table
- [ ] Repository with get/save/update profile methods
- [ ] Profile versioning for "outdated" detection
- [ ] Status-based document queries

---

## Phase 2: Company Profile System (Questionnaire)

**Objective:** Multi-step wizard to capture all company/legal information

### 2.1 Profile Data Structure

```json
{
  "company": {
    "legal_name": "",
    "trading_name": "",
    "registration_number": "",
    "address": {
      "street": "",
      "city": "",
      "state": "",
      "postal_code": "",
      "country": ""
    },
    "business_type": ""
  },
  "contacts": {
    "legal_email": "",
    "support_email": "",
    "phone": "",
    "dpo": {
      "name": "",
      "email": "",
      "address": ""
    }
  },
  "website": {
    "url": "",
    "app_name": "",
    "service_description": "",
    "industry": ""
  },
  "data_collection": {
    "personal_data_types": [],
    "purposes": [],
    "lawful_bases": {},
    "special_categories": false,
    "children_data": false,
    "minimum_age": 16
  },
  "third_parties": {
    "analytics": [],
    "payment": [],
    "marketing": [],
    "hosting": [],
    "other": []
  },
  "cookies": {
    "essential": [],
    "analytics": [],
    "marketing": [],
    "preferences": []
  },
  "legal": {
    "primary_jurisdiction": "",
    "gdpr_applies": false,
    "ccpa_applies": false,
    "other_frameworks": [],
    "supervisory_authority": ""
  },
  "retention": {
    "default_period": "",
    "by_category": {},
    "deletion_policy": "",
    "backup_retention": ""
  },
  "_meta": {
    "version": 1,
    "completion": 0,
    "completed_steps": [],
    "last_step": 1
  }
}
```

### 2.2 Questionnaire Steps Configuration

| Step | Key | Fields | Validation |
|------|-----|--------|------------|
| 1 | `company` | legal_name*, trading_name, registration_number, address*, business_type* | Required fields |
| 2 | `contacts` | legal_email*, support_email, phone, dpo.name, dpo.email* | Valid emails |
| 3 | `website` | url*, app_name, service_description*, industry | Valid URL |
| 4 | `data_collection` | personal_data_types*, purposes*, lawful_bases | At least 1 each |
| 5 | `third_parties` | analytics, payment, marketing, hosting, other | Optional |
| 6 | `cookies` | essential, analytics, marketing, preferences | At least essential |
| 7 | `legal` | primary_jurisdiction*, gdpr_applies, ccpa_applies | Required jurisdiction |
| 8 | `retention` | default_period*, by_category, deletion_policy | Required default |

### 2.3 Files to Create

| File | Purpose |
|------|---------|
| `includes/Services/Company_Profile_Service.php` | Business logic for profile |
| `includes/Admin/Profile_Wizard.php` | Wizard controller |
| `templates/admin/documents/profile-wizard.php` | Wizard UI template |
| `templates/admin/documents/wizard-steps/*.php` | Individual step templates (8 files) |
| `assets/js/profile-wizard.js` | Wizard JS (validation, navigation, auto-save) |
| `assets/css/profile-wizard.css` | Wizard-specific styles |

### 2.4 Features
- [ ] Step-by-step navigation with progress bar
- [ ] Auto-save on step change
- [ ] Skip step with "I'll do this later"
- [ ] Field validation per step
- [ ] Smart defaults detection (WooCommerce, analytics plugins, etc.)
- [ ] Contextual help tooltips
- [ ] Import from existing settings (if available)

---

## Phase 3: Document Hub UI

**Objective:** Main dashboard with document cards and status overview

### 3.1 Page Structure

```
Document Hub Page
├── Header Section
│   ├── Title: "Legal Document Hub"
│   ├── Profile completion banner (with CTA)
│   └── Last updated timestamp
├── Profile Summary Card
│   ├── Completion percentage bar
│   ├── Missing fields alert
│   └── [Complete Setup] / [Edit Profile] button
├── Document Cards Grid (3 columns)
│   ├── Privacy Policy Card
│   ├── Terms of Service Card
│   ├── Cookie Policy Card
│   ├── GDPR Addendum Card
│   ├── CCPA Notice Card
│   └── Data Processing Agreement Card
├── Bulk Actions Bar
│   ├── [Regenerate All Outdated]
│   ├── [Export All Published]
│   └── [View Compliance Report]
└── Footer
    └── Shortcode reference / help link
```

### 3.2 Card Component Specification

```
┌────────────────────────────────────────┐
│ [Icon]  Document Title        [Badge]  │  <- Badge: Published v2.1 / Draft / Outdated
│                                        │
│ Description text about this document   │
│ type and its purpose...                │
│                                        │
│ ┌─────────────────────────────────────┐│
│ │ Last Updated: Dec 20, 2025          ││  <- Metadata row
│ │ Shortcode: [legal_doc type="x"]     ││
│ └─────────────────────────────────────┘│
│                                        │
│ ┌────┐ ┌────┐ ┌────┐ ┌──────────┐     │  <- Action buttons (contextual)
│ │View│ │Edit│ │ ↓  │ │Regenerate│     │
│ └────┘ └────┘ └────┘ └──────────┘     │
└────────────────────────────────────────┘
```

### 3.3 Card States & Actions Matrix

| State | Badge Color | Available Actions |
|-------|-------------|-------------------|
| `not_generated` | Gray dashed | Generate |
| `draft` | Yellow/Amber | Edit, Preview, Publish, Delete, Regenerate |
| `published` | Green | View, Edit (new version), Download, Regenerate |
| `outdated` | Orange | View, Edit, Download, Regenerate (highlighted) |

### 3.4 Files to Create/Modify

| File | Action | Purpose |
|------|--------|---------|
| `templates/admin/documents/hub.php` | Create | Main hub template |
| `templates/admin/documents/partials/document-card.php` | Create | Reusable card component |
| `templates/admin/documents/partials/profile-banner.php` | Create | Profile completion banner |
| `includes/Admin/DocumentsMainPage.php` | Modify | New render method for hub |
| `assets/css/document-hub.css` | Create | Hub-specific styles |
| `assets/js/document-hub.js` | Create | Card interactions, modals |

---

## Phase 4: Generation Engine

**Objective:** Generate documents from templates + profile data

### 4.1 Generation Flow

```
User clicks [Generate]
       │
       ▼
Check Profile Completion
       │
       ├── < 100% → Show "Complete these fields" modal
       │                    │
       │                    ▼
       │             [Go to Setup] or [Generate Anyway]
       │
       ▼
Load Template (by document type)
       │
       ▼
Map Profile Data → Template Placeholders
       │
       ▼
Resolve All Placeholders
       │
       ▼
Validate Content (check for unresolved {{placeholders}})
       │
       ▼
Save as Draft (status: draft, version: 1.0)
       │
       ▼
Show Success → Redirect to Hub with highlight
```

### 4.2 Placeholder Mapping

| Profile Field | Placeholder |
|---------------|-------------|
| `company.legal_name` | `{{business_name}}` |
| `company.address.full` | `{{company_address}}` |
| `website.url` | `{{site_url}}` |
| `contacts.legal_email` | `{{legal_contact}}` |
| `contacts.dpo.name` | `{{dpo_name}}` |
| `contacts.dpo.email` | `{{dpo_email}}` |
| `legal.primary_jurisdiction` | `{{jurisdiction}}` |
| `retention.default_period` | `{{retention_period}}` |
| `third_parties.analytics[]` | `{{analytics_providers}}` |
| `third_parties.payment[]` | `{{payment_processors}}` |
| `cookies.essential[]` | `{{essential_cookies}}` |

### 4.3 Regeneration Logic

```
User clicks [Regenerate]
       │
       ▼
Show Confirmation Modal
"This will regenerate from current profile & template.
 Custom edits will be preserved in version history.
 A new version will be created."
       │
       ├── [Cancel]
       │
       ▼
       [Regenerate]
       │
       ▼
Archive current version (if exists)
       │
       ▼
Generate new content (same as Generate flow)
       │
       ▼
Save as new version (1.0 → 2.0 for major regenerate)
       │
       ▼
Mark previous versions as "superseded"
```

### 4.4 Files to Create/Modify

| File | Action | Purpose |
|------|--------|---------|
| `includes/Services/Document_Generator.php` | Create | Core generation logic |
| `includes/Services/Placeholder_Mapper.php` | Create | Profile → Placeholder mapping |
| `includes/Services/Template_Manager.php` | Modify | Add template retrieval by type |
| `includes/API/REST_Documents.php` | Modify | Add generate/regenerate endpoints |

### 4.5 REST API Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/slos/v1/documents/generate` | POST | Generate new document |
| `/slos/v1/documents/{id}/regenerate` | POST | Regenerate existing |
| `/slos/v1/documents/{type}/status` | GET | Get document status |
| `/slos/v1/profile` | GET/POST | Get/Save profile |
| `/slos/v1/profile/completion` | GET | Get completion status |

---

## Phase 5: Editor & Versioning

**Objective:** Edit documents and maintain version history

### 5.1 Editor Modes

| Mode | Trigger | Behavior |
|------|---------|----------|
| **New Version Edit** | Edit published doc | Creates v1.0 → v1.1 |
| **Draft Edit** | Edit draft doc | Updates in place |
| **View Only** | View published | Read-only display |

### 5.2 Version Management

```
Document: Privacy Policy
├── v1.0 (draft) - Dec 15 - Generated
├── v1.0 (published) - Dec 16 - First publish
├── v1.1 (draft) - Dec 18 - Manual edit
├── v1.1 (published) - Dec 18 - Published edit
├── v2.0 (draft) - Dec 20 - Regenerated (profile updated)
└── v2.0 (published) - Dec 20 - Current
```

### 5.3 Files to Modify

| File | Changes |
|------|---------|
| `includes/Admin/Legal_Doc_Editor.php` | Add version-aware editing |
| `templates/admin/legaldoc-editor.php` | Show version info, add version bump option |
| `includes/Admin/Version_History.php` | Enhance with compare view |

---

## Phase 6: Export & Delivery

**Objective:** Download, embed, and share documents

### 6.1 Export Options

| Format | Method | Features |
|--------|--------|----------|
| **PDF** | Single document | Branded header, watermark option |
| **HTML** | Single document | Clean HTML for embedding |
| **ZIP** | All published | All docs in PDF + HTML |
| **Shortcode** | Embed | `[legal_doc type="privacy-policy"]` |

### 6.2 Shortcode Enhancements

```php
// Basic
[legal_doc type="privacy-policy"]

// With options
[legal_doc type="privacy-policy" locale="en_US" format="full"]
[legal_doc type="terms" show_version="yes" show_date="yes"]

// All documents list
[legal_docs_list]
```

### 6.3 Files to Create/Modify

| File | Action | Purpose |
|------|--------|---------|
| `includes/Services/PDF_Generator.php` | Modify | Enhanced PDF export |
| `includes/Services/Export_Service.php` | Create | Bulk export logic |
| `includes/Shortcodes/Legal_Doc_Shortcode.php` | Modify | Enhanced shortcode options |

---

## Phase 7: Polish & Integration

**Objective:** Final touches, testing, and integration

### 7.1 Smart Detection

| Detect | Source | Auto-fill |
|--------|--------|-----------|
| WooCommerce | Plugin active | Payment processors, shipping data handling |
| Google Analytics | Site Kit / manual | Analytics provider |
| Contact Form 7 | Plugin active | Form data collection |
| Mailchimp | Plugin/API | Marketing provider |
| Stripe | Plugin/API | Payment processor |
| Site info | WordPress | Site name, URL, admin email |

### 7.2 Compliance Scoring

```
Document Compliance Score
├── Has required sections: +40%
├── All placeholders resolved: +20%
├── Matches detected jurisdiction: +20%
├── Up to date with profile: +10%
└── Published status: +10%
```

### 7.3 Notification System

| Event | Notification |
|-------|--------------|
| Profile updated | "X documents may need regeneration" |
| Document outdated 30+ days | Admin notice |
| Missing required documents | Dashboard widget alert |
| Template updated (plugin update) | "New template available" |

---

## Implementation Dependencies

```
Phase 1: Database ─────────────────────────────┐
                                               │
Phase 2: Profile System ◄──────────────────────┤
         ├── Profile Repository                │
         ├── Profile Service                   │
         └── Wizard UI                         │
                                               │
Phase 3: Document Hub UI ◄─────────────────────┤
         ├── Hub Template                      │
         ├── Card Components                   │
         └── Status Logic                      │
                                               │
Phase 4: Generation Engine ◄───────────────────┘
         ├── Document Generator
         ├── Placeholder Mapper
         └── REST Endpoints

Phase 5: Editor & Versioning ◄─────────────────┐
         ├── Version-aware Editor              │
         └── History Enhancement               │
                                               │
Phase 6: Export & Delivery ◄───────────────────┤
         ├── PDF Enhancement                   │
         ├── Bulk Export                       │
         └── Shortcode Updates                 │
                                               │
Phase 7: Polish ◄──────────────────────────────┘
         ├── Smart Detection
         ├── Compliance Score
         └── Notifications
```

---

## File Inventory Summary

| Category | New Files | Modified Files |
|----------|-----------|----------------|
| **Database** | 2 | 1 |
| **Services** | 4 | 2 |
| **Admin** | 2 | 2 |
| **Templates** | 12 | 2 |
| **Assets (JS)** | 2 | 0 |
| **Assets (CSS)** | 2 | 0 |
| **API** | 0 | 1 |
| **Total** | **24** | **8** |

---

## Progress Tracking

### Phase 1: Database & Data Layer
- [ ] Migration_Company_Profile.php
- [ ] Company_Profile_Repository.php
- [ ] Legal_Doc_Repository.php updates

### Phase 2: Company Profile System
- [ ] Company_Profile_Service.php
- [ ] Profile_Wizard.php
- [ ] profile-wizard.php template
- [ ] 8 wizard step templates
- [ ] profile-wizard.js
- [ ] profile-wizard.css

### Phase 3: Document Hub UI
- [ ] hub.php template
- [ ] document-card.php partial
- [ ] profile-banner.php partial
- [ ] DocumentsMainPage.php updates
- [ ] document-hub.css
- [ ] document-hub.js

### Phase 4: Generation Engine
- [ ] Document_Generator.php
- [ ] Placeholder_Mapper.php
- [ ] Template_Manager.php updates
- [ ] REST API endpoints

### Phase 5: Editor & Versioning
- [ ] Legal_Doc_Editor.php updates
- [ ] legaldoc-editor.php updates
- [ ] Version_History.php updates

### Phase 6: Export & Delivery
- [ ] PDF_Generator.php updates
- [ ] Export_Service.php
- [ ] Legal_Doc_Shortcode.php updates

### Phase 7: Polish & Integration
- [x] Smart detection logic (Smart_Detection_Service.php)
- [x] Compliance scoring (Hub_Compliance_Score_Service.php)
- [x] Notification system (Hub_Notification_Service.php)
- [x] Hub settings panel (Hub_Settings_Service.php)
- [x] Search/filter functionality (document-hub.js, document-hub.php)
- [x] Keyboard navigation (document-hub.js)

---

## Changelog

| Date | Phase | Changes |
|------|-------|---------|
| Dec 22, 2025 | Plan | Initial strategic plan created |
| Dec 2025 | Phase 7 | Smart Detection, Compliance Scoring, Notifications, Settings, Search/Filter, Keyboard Nav |
