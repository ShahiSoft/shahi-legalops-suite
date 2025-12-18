# Consent Management Module — Formal Product Specification v1.0

**Module Name:** Consent Management (CM)  
**Version:** 1.0.0  
**Status:** Pre-Development / Planning  
**Date:** 2025-12-17  
**Author:** Shahi LegalOps Suite Dev Team  
**Scope:** Consent-only (excludes accessibility, legal documents, DSR)

---

## 1. OVERVIEW & VALUE PROPOSITION

### 1.1 Purpose
The Consent Management Module provides enterprise-grade GDPR/CCPA/LGPD/PIPEDA compliance through automated cookie consent, script blocking, and consent proof recording—integrated into the Shahi LegalOps Suite's modular architecture.

### 1.2 Target Users
- E-commerce sites (WooCommerce)
- Publishers (ad/analytics tracking)
- SaaS platforms (user data flows)
- Agencies (multi-site management)
- High-traffic sites (performance critical)

### 1.3 Key Differentiators (vs. Standalone Competitors)
- **Integrated Suite**: Combines consent with accessibility, DSR, and legal docs in one UI.
- **High Performance**: <20KB JS footprint; GTM Consent Initialization ready.
- **Developer First**: REST API, PHP SDK, hooks, extensible service adapter framework.
- **Modern Signals**: Google Consent Mode v2, IAB TCF v2.2, GPP/USP, GPC, WP Consent API.
- **Enterprise Ready**: Multisite/subsite management, granular roles, audit logs, proof of consent.

---

## 2. FUNCTIONAL REQUIREMENTS (MVP → PRO)

### 2.1 MVP Phase (v1.0 — Q1 2026)

#### 2.1.1 Banner & UX
- [x] Template system: top bar, bottom bar, modal, side-slide, sticky footer.
- [x] First layer: Accept All, Reject All, Customize (equal choice, no dark patterns).
- [x] Preference Center: per-category toggles (Necessary, Functional, Analytics, Marketing + custom).
- [x] Per-service toggles inside Preference Center (e.g., toggle GA4, Facebook Pixel separately).
- [x] Revisit button: floating widget or footer link; triggers banner re-open for withdraw/update.
- [x] Customization: colors, fonts, logo, spacing; CSS class hooks for custom styling.
- [x] Multilingual: auto-language detection; manual WPML/Polylang support; 20+ languages.
- [x] Mobile responsive: full collapse on mobile; touch-friendly buttons.
- [x] Accessibility: WCAG 2.2 AA (keyboard nav, focus traps, ARIA labels, contrast).

#### 2.1.2 Consent Signals & Google Integration
- [x] Google Consent Mode v2: emit ad_storage, analytics_storage, ad_user_data, ad_personalization.
- [x] GTM Consent Initialization: hook into GTM dataLayer at correct timing.
- [x] WP Consent API: publish category status to do_action('set_cookie_consent', $cat, $value).

#### 2.1.3 Blocking Engine (MVP)
- [x] Prior-consent blocking: detect and block external <script src="..."> tags.
- [x] Inline script blocking: JSON-LD, analytics, pixel tracking (pattern-based).
- [x] iFrame blocking: YouTube, Vimeo, Google Maps, social embeds; show placeholders.
- [x] Re-enable on consent: async re-run blocked scripts without page reload (dataLayer-safe).
- [x] Graceful fallback: works without JS (banner shows, some scripts load after consent assumed).

#### 2.1.4 Geo & Localization
- [x] IP geolocation: detect EU/UK/US/CA/BR/AU/ZA regions.
- [x] Regional presets: GDPR mode for EU/UK, CCPA mode for US, LGPD for BR, etc.
- [x] Auto-enforcement: prior-consent blocking only where required; skip in non-regulated regions.

#### 2.1.5 Consent Recording & Audit
- [x] Proof of Consent: log user ID (hashed), timestamp, categories granted, banner version.
- [x] Consent logs table: indexed by user, region, consent status.
- [x] Data minimization: optional IP anonymization; retention policy per region (e.g., 12mo EU).
- [x] Export: CSV/JSON of consent logs; accessible from admin panel.
- [x] Re-consent triggers: on banner config change, region policy update, or manual trigger.

#### 2.1.6 Admin Panel (Dashboard Card)
- [x] Module Card in Dashboard: status indicator, quick actions, mini stats.
- [x] Settings page: accessible only via settings icon on card.
- [x] Settings tabs:
  - General: Enable/Disable, IP anonymization, data retention, reset to defaults.
  - Banner: template, colors, text, logo, position, animation.
  - Consent: categories, per-service list, blocking rules.
  - Geo: region mapping, conditional enforcement.
  - Integrations: enable/disable GA, GTM, WP Consent API, etc.
  - Logs: view, filter, export consents; audit trail.

#### 2.1.7 Scanning (MVP Lite)
- [x] One-click scan: detect cookies and scripts on site homepage and common pages.
- [x] Auto-categorize: match detected scripts/cookies against known providers (GA, FB, etc.).
- [x] Manual reconciliation: admin can override or add custom trackers to inventory.

#### 2.1.8 Database & API
- [x] Custom table: `complyflow_consent_logs` (user, region, categories, timestamp, banner_version).
- [x] Settings storage: JSON in wp_options (complyflow_consent_settings).
- [x] REST endpoints (stub):
  - POST /wp-json/complyflow/v1/consent/preferences (save user preferences).
  - GET /wp-json/complyflow/v1/consent/status (read current consent state).
  - POST /wp-json/complyflow/v1/consent/withdraw (revoke consent).
  - GET /wp-json/complyflow/v1/consent/logs (admin export, paginated).

#### 2.1.9 Performance & Security
- [x] JS core: <20KB gzipped; ESM modules; no jQuery; async-first.
- [x] Security: nonce validation, capability checks, input sanitization, prepared SQL.
- [x] No layout shift: CSS inlined, GA / GTM scripts deferred.

### 2.2 PRO Phase (v1.1+ — Q2 2026+)

#### 2.2.1 IAB TCF v2.2 & Advanced Signals
- [ ] Full CMP registration with IAB; __tcfapi bridge.
- [ ] Vendor list integration; purpose/LI consent granularity.
- [ ] GPP (__gpp) signal emission for US state laws.
- [ ] Enhanced consent metadata in logs.

#### 2.2.2 Deep Scanner & Repository
- [ ] Deep scan: localStorage, sessionStorage, indexedDB, pixels, beacons.
- [ ] Scheduled rescans (weekly); delta alerts.
- [ ] Cookie repository: auto-updated service descriptions; custom provider additions.
- [ ] CSV import/export for inventory.

#### 2.2.3 Multi-Site & Cross-Domain
- [ ] Subdomain consent sharing: sync preferences across subdomains.
- [ ] Network-wide presets: central policy; per-site overrides.
- [ ] Multi-user roles: editor, publisher, reviewer, auditor with granular caps.

#### 2.2.4 A/B Testing & Analytics
- [ ] Banner variant testing: layout, text, button copy; statistical significance.
- [ ] Consent dashboard: acceptance rates, drop-off funnels, regional breakdown.
- [ ] Recommendations engine: heuristic tips for improving compliance + conversions.

#### 2.2.5 Advanced Integrations
- [ ] Microsoft UET Consent Mode.
- [ ] Facebook / Meta Conversion API mode.
- [ ] TikTok, LinkedIn, Pinterest pixel tracking coordination.
- [ ] Server-side tagging consent propagation (Google Tag Manager Server).

#### 2.2.6 Enterprise & Automation
- [ ] 2FA / SSO for admin login (optional hosted component).
- [ ] API-based consent updates for third-party integrations.
- [ ] Webhooks: on consent change, log export, region policy shifts.
- [ ] Real-time sync across devices (optional authenticated user feature).

---

## 3. NON-FUNCTIONAL REQUIREMENTS

### 3.1 Performance
| Metric                | Target       | Current | Status |
|----------------------|--------------|---------|--------|
| Core JS size (gz)    | <20KB        | TBD     | TBD    |
| Banner load time     | <500ms       | TBD     | TBD    |
| DB queries/page      | <5           | TBD     | TBD    |
| CLS score            | <0.1         | TBD     | TBD    |
| Memory (banner JS)   | <2MB         | TBD     | TBD    |

### 3.2 Security & Privacy
- Input validation: sanitize all settings, banner text, custom CSS.
- Output escaping: esc_html, esc_attr, wp_kses_post as needed.
- Nonce verification: all admin forms and AJAX calls.
- Capability checks: current_user_can('manage_options') for admin.
- Data minimization: hash user identifiers in logs; optional IP anonymization.
- Encryption: optional AES-256 for sensitive log fields (TLS in transit required).
- OWASP Top 10: SQL injection, XSS, CSRF, broken auth, sensitive data exposure mitigations.

### 3.3 Accessibility (WCAG 2.2 AA)
- Keyboard navigation: Tab through banner, Escape to close, Enter to submit.
- Screen reader: banner announced; purpose of each button clear; preference center labeling.
- Focus management: focus trap in modal; return focus to trigger button.
- Color contrast: 4.5:1 for text, 3:1 for graphics; no color-only info.
- Motion: respect prefers-reduced-motion; no auto-play animations.

### 3.4 Browser & Device Support
- Modern browsers: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+.
- Mobile: iOS Safari 14+, Chrome Mobile 90+; full touch support.
- Legacy support: IE11 – opt-in via UMD build; graceful degrade.
- Offline: banner works with service worker; logs queued if offline.

### 3.5 Compatibility
- WordPress: 6.4+; tested up to 6.9+.
- PHP: 8.0+; strict types enabled.
- Multisite: full support; per-site settings + network defaults.
- Popular plugins: WPML, Polylang, WP Rocket, LiteSpeed, W3TC, Elementor, Divi, CF7, etc.

---

## 4. DATA MODELS & DATABASE SCHEMA

### 4.1 Consent Log Schema (`complyflow_consent_logs`)
```sql
CREATE TABLE complyflow_consent_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED,                           -- NULL = anonymous
  session_id VARCHAR(64) NOT NULL,                   -- hash(IP + UA + timestamp)
  region VARCHAR(10) NOT NULL,                       -- 'EU', 'US-CA', 'BR', etc.
  categories JSON NOT NULL,                          -- {"analytics": true, "marketing": false, ...}
  purposes JSON,                                     -- TCF v2.2 purposes (PRO only)
  banner_version VARCHAR(50) NOT NULL,               -- 'v1.0', track config changes
  timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expiry_date DATETIME,                              -- when consent expires
  source VARCHAR(50) DEFAULT 'banner',               -- 'banner', 'api', 'form', 'cron'
  ip_hash VARCHAR(64),                               -- optional, anonymized
  user_agent_hash VARCHAR(64),                       -- optional
  withdrawn_at DATETIME,                             -- if consent revoked
  metadata JSON,                                     -- custom fields for extensions
  PRIMARY KEY (id),
  KEY idx_user_id (user_id),
  KEY idx_session_id (session_id),
  KEY idx_region (region),
  KEY idx_timestamp (timestamp),
  KEY idx_withdrawn (withdrawn_at)
);
```

### 4.2 Settings Schema (`wp_options`)
```php
// Option key: 'complyflow_consent_settings'
{
  "enabled": true,
  "version": "1.0.0",
  "banner": {
    "template": "top_bar",                        // top_bar, bottom_bar, modal, etc.
    "position": "top",                            // top, bottom
    "animation": "slide",                         // slide, fade
    "colors": {
      "primary": "#1f2937",
      "background": "#ffffff",
      "text": "#111827",
      "button_accept": "#10b981",
      "button_reject": "#ef4444",
      "button_customize": "#3b82f6"
    },
    "typography": {
      "font_family": "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
      "font_size": "14px",
      "line_height": "1.5"
    },
    "branding": {
      "logo_url": "",
      "logo_alt": "",
      "company_name": ""
    },
    "text": {
      "title": "Cookie Consent",
      "description": "We use cookies...",
      "accept_all": "Accept All",
      "reject_all": "Reject All",
      "customize": "Customize"
    },
    "revisit": {
      "enabled": true,
      "style": "floating_button",                 // floating_button, footer_link
      "label": "Preferences"
    }
  },
  "consent": {
    "categories": [
      {
        "id": "necessary",
        "label": "Necessary",
        "description": "Essential for site function",
        "enabled": true,
        "required": true,                         // cannot unconsent
        "services": [
          {
            "id": "wp_core",
            "name": "WordPress Core",
            "description": "Session, CSRF protection",
            "category": "necessary"
          }
        ]
      },
      {
        "id": "analytics",
        "label": "Analytics",
        "description": "Understand user behavior",
        "enabled": false,
        "services": [
          {
            "id": "google_analytics",
            "name": "Google Analytics 4",
            "description": "Traffic & behavior tracking",
            "category": "analytics",
            "provider_id": "google"                // for auto-detect
          },
          {
            "id": "hotjar",
            "name": "Hotjar",
            "description": "Session replay & heatmaps",
            "category": "analytics",
            "provider_id": "hotjar"
          }
        ]
      },
      {
        "id": "marketing",
        "label": "Marketing",
        "description": "Personalized ads & campaigns",
        "enabled": false,
        "services": [
          {
            "id": "facebook_pixel",
            "name": "Facebook Pixel",
            "category": "marketing",
            "provider_id": "meta"
          },
          {
            "id": "google_ads",
            "name": "Google Ads",
            "category": "marketing",
            "provider_id": "google"
          }
        ]
      },
      {
        "id": "functional",
        "label": "Functional",
        "description": "Enhance user experience",
        "enabled": true,
        "services": []
      }
    ],
    "blocking_rules": [
      {
        "id": "ga_script",
        "type": "external_script",
        "pattern": "gtag.js",
        "category": "analytics",
        "action": "block_until_consent"
      },
      {
        "id": "fb_pixel",
        "type": "external_script",
        "pattern": "pixel.facebook.com",
        "category": "marketing",
        "action": "block_until_consent"
      },
      {
        "id": "youtube_iframe",
        "type": "iframe",
        "pattern": "youtube.com",
        "category": "marketing",
        "action": "replace_with_placeholder"
      }
    ]
  },
  "geo": {
    "detection": "maxmind_db",                    // maxmind_db, ip_api, builtin
    "regions": {
      "EU": {
        "countries": ["AT", "BE", "BG", "HR", "CY", "CZ", "DK", "EE", "FI", "FR", "DE", "GR", "HU", "IE", "IT", "LV", "LT", "LU", "MT", "NL", "PL", "PT", "RO", "SK", "SI", "ES", "SE"],
        "mode": "gdpr",
        "prior_consent_required": true,
        "cookie_duration_max": 12,                // months
        "banner_variant": "gdpr"
      },
      "UK": {
        "countries": ["GB"],
        "mode": "uk_gdpr",
        "prior_consent_required": true,
        "banner_variant": "uk"
      },
      "US": {
        "countries": ["US"],
        "mode": "ccpa",
        "states": ["CA"],                         // per-state handling (future)
        "prior_consent_required": false,
        "banner_variant": "ccpa"
      },
      "BR": {
        "countries": ["BR"],
        "mode": "lgpd",
        "prior_consent_required": true,
        "banner_variant": "lgpd"
      },
      "CA": {
        "countries": ["CA"],
        "mode": "pipeda",
        "prior_consent_required": true,
        "banner_variant": "ca"
      }
    }
  },
  "integrations": {
    "google_consent_mode": {
      "enabled": true,
      "version": 2,                               // v2 for advanced signals
      "emit_on": "page_load"
    },
    "wp_consent_api": {
      "enabled": true
    },
    "gtm": {
      "enabled": false,
      "gtm_id": "",
      "consent_initialization": true
    },
    "providers": {
      "google_analytics": true,
      "facebook": true,
      "tiktok": false,
      "linkedin": false
    }
  },
  "privacy": {
    "ip_anonymization": true,
    "retention_days_eu": 365,
    "retention_days_us": 365,
    "retention_days_default": 365,
    "hash_ip": true,
    "hash_ua": true
  },
  "logging": {
    "enabled": true,
    "level": "info",                              // debug, info, warn, error
    "export_format": "csv"                        // csv, json
  }
}
```

### 4.3 Service Inventory Schema (Optional, can be in JSON or separate table in PRO)
```php
// Option key: 'complyflow_consent_service_inventory'
{
  "services": [
    {
      "id": "google_analytics",
      "name": "Google Analytics 4",
      "provider": "google",
      "category": "analytics",
      "domain": "google-analytics.com",
      "cookies": ["_ga", "_gat", "_gid"],
      "retention_days": 395,
      "privacy_policy_url": "https://policies.google.com/privacy",
      "dpa_url": "https://...",
      "description": "Web analytics and traffic analysis",
      "last_detected": "2025-12-17T10:00:00Z",
      "enabled": false
    }
  ]
}
```

---

## 5. REST API SPECIFICATION

### 5.1 Endpoints Overview
**Base:** `/wp-json/complyflow/v1/consent`

| Method | Endpoint                    | Purpose                   | Auth     |
|--------|----------------------------|---------------------------|----------|
| POST   | /preferences                | Save user consent         | none     |
| GET    | /status                     | Get user consent state    | none     |
| POST   | /withdraw                   | Revoke consent            | none     |
| GET    | /logs                       | Admin export logs         | admin    |
| POST   | /logs/bulk-export           | Export to CSV/JSON        | admin    |
| GET    | /settings                   | Get module settings       | admin    |
| POST   | /settings                   | Update settings           | admin    |
| POST   | /scan                       | Trigger cookie scan       | admin    |
| GET    | /scan/results               | Get latest scan results   | admin    |
| POST   | /re-consent                 | Trigger re-consent        | admin    |

### 5.2 Endpoint Details

#### POST /preferences
Save user consent preferences.
```json
{
  "categories": {
    "necessary": true,
    "functional": true,
    "analytics": false,
    "marketing": false
  },
  "banner_version": "v1.0",
  "region": "EU",
  "consent_duration_days": 365
}
```
Response:
```json
{
  "success": true,
  "message": "Consent saved",
  "consent_id": "sess_abc123...",
  "expires": "2026-12-17T10:00:00Z"
}
```

#### GET /status
Retrieve current consent state.
```json
{
  "categories": {
    "necessary": true,
    "functional": true,
    "analytics": false,
    "marketing": false
  },
  "timestamp": "2025-12-17T10:00:00Z",
  "expires": "2026-12-17T10:00:00Z",
  "region": "EU",
  "withdrawn": false
}
```

#### POST /withdraw
Revoke consent.
```json
{
  "categories": ["analytics", "marketing"]    // optional; [] = revoke all non-necessary
}
```
Response:
```json
{
  "success": true,
  "message": "Consent withdrawn",
  "withdrawn_at": "2025-12-17T10:00:00Z"
}
```

#### GET /logs (admin)
Export consent logs with filters.
```
GET /wp-json/complyflow/v1/consent/logs?region=EU&per_page=50&page=1&orderby=timestamp&order=desc
```
Response:
```json
{
  "logs": [
    {
      "id": 123,
      "user_id": null,
      "session_id": "sess_...",
      "region": "EU",
      "categories": {"analytics": true, "marketing": false},
      "timestamp": "2025-12-17T10:00:00Z",
      "banner_version": "v1.0",
      "source": "banner",
      "withdrawn_at": null
    }
  ],
  "total": 1000,
  "pages": 20
}
```

#### POST /logs/bulk-export (admin)
Export logs to CSV or JSON.
```json
{
  "format": "csv",                              // csv, json
  "region": "EU",                               // optional filter
  "start_date": "2025-12-01",
  "end_date": "2025-12-17",
  "withdrawn_only": false
}
```
Response: Download CSV/JSON file.

#### GET /settings (admin)
Retrieve module settings.
```json
{
  // Full settings object as per schema above
}
```

#### POST /settings (admin)
Update module settings.
```json
{
  "banner": { ... },
  "consent": { ... },
  "geo": { ... },
  "integrations": { ... },
  "privacy": { ... }
}
```
Response:
```json
{
  "success": true,
  "message": "Settings updated",
  "settings": { ... }
}
```

#### POST /scan (admin)
Trigger a cookie/script scan.
```json
{
  "scope": "homepage",                          // homepage, all_pages, custom_urls
  "urls": ["https://example.com", "https://example.com/about"]
}
```
Response:
```json
{
  "success": true,
  "message": "Scan started",
  "scan_id": "scan_123",
  "status": "in_progress"
}
```

#### GET /scan/results (admin)
Retrieve latest scan results.
```json
{
  "scan_id": "scan_123",
  "status": "completed",
  "timestamp": "2025-12-17T10:00:00Z",
  "detected_cookies": [
    {
      "name": "_ga",
      "domain": ".example.com",
      "category": "analytics",
      "provider": "google",
      "retention": "395 days"
    }
  ],
  "detected_scripts": [
    {
      "url": "https://www.googletagmanager.com/gtag/js?id=G-...",
      "category": "analytics",
      "provider": "google"
    }
  ],
  "total_cookies": 15,
  "total_scripts": 12
}
```

---

## 6. MODULE ARCHITECTURE & SCAFFOLDING

### 6.1 Directory Structure
```
includes/
  modules/
    consent/
      Consent.php                           # Module class, implements ModuleInterface
      interfaces/
        ConsentRepositoryInterface.php       # Data access contract
        ConsentLoggerInterface.php           # Logging contract
        BlockingEngineInterface.php          # Script blocking contract
        ScannerInterface.php                 # Scanner contract (PRO)
      repositories/
        ConsentRepository.php
        ConsentLogRepository.php
        ServiceInventoryRepository.php
      services/
        BlockingService.php                  # Script/iFrame blocking logic
        ScannerService.php                   # Cookie/script detection
        ConsentSignalService.php             # GCM v2, TCF, GPP emission
        GeoService.php                       # IP geolocation, region mapping
        ReportingService.php                 # Consent analytics
      controllers/
        ConsentRestController.php            # REST endpoint handlers
      storage/
        migrations/
          001_create_consent_logs_table.php
      assets/
        js/
          consent-banner.js                  # Frontend banner (React/Alpine)
          consent-blocker.js                 # Script blocking engine
          consent-signals.js                 # GCM/TCF/GPP emission
        css/
          consent-banner.css
          consent-preferences.css
      views/
        settings/
          general-settings.php
          banner-settings.php
          consent-settings.php
          geo-settings.php
          integrations-settings.php
          logs-settings.php
        dashboard/
          module-card.php
          mini-stats.php
      config/
        consent-defaults.php                 # Default settings
        providers.php                        # Known tracker providers
```

### 6.2 Module Class Scaffold
See separate file: `ConsentModule.php` (created below)

### 6.3 Key Interfaces (Contracts)
See separate files in `interfaces/` folder (created below)

---

## 7. DEVELOPMENT MILESTONES

### Phase 1: Foundation (Weeks 1–2)
- [x] Finalize DB schema, migrations.
- [x] Scaffold module class, interfaces, repositories.
- [x] Create banner settings UI (general, colors, text).
- [x] Frontend banner component (bare-minimum UI in Alpine.js or React).
- **Deliverable**: Module loads in dashboard; settings accessible; banner renders on frontend.

### Phase 2: Core Blocking & Signals (Weeks 3–4)
- [ ] Script/iFrame blocking engine (MutationObserver + pattern matching).
- [ ] Google Consent Mode v2 signal emission.
- [ ] WP Consent API integration.
- [ ] Consent preference saving and retrieval (localStorage + server).
- **Deliverable**: Banner blocks scripts pre-consent; signals flow to GTM/GA.

### Phase 3: Geo & Compliance (Weeks 5–6)
- [ ] IP geolocation (local MaxMind DB or IP API).
- [ ] Regional preset loading (EU/UK/US/BR/CA).
- [ ] Prior-consent enforcement per region.
- [ ] Consent logging and proof of consent.
- **Deliverable**: Banner adapts to user region; logs capture consent proof.

### Phase 4: Admin Panel & Auditing (Weeks 7–8)
- [ ] Module card on Dashboard (status, mini-stats, quick actions).
- [ ] Settings pages (all tabs): banner, consent categories, geo, integrations, logs.
- [ ] Logs viewer with export (CSV/JSON).
- [ ] Re-consent triggers and admin actions.
- **Deliverable**: Full admin control; consent auditing.

### Phase 5: REST API & Scanning (Weeks 9–10)
- [ ] REST endpoint stubs → full implementation.
- [ ] Cookie/script scanner (basic one-click scan).
- [ ] Service inventory (known providers).
- [ ] API testing (Postman/REST Client).
- **Deliverable**: API-first consent management; automated scanning.

### Phase 6: Polish & QA (Weeks 11–12)
- [ ] Performance optimization (<20KB JS).
- [ ] Accessibility audit (WCAG 2.2 AA).
- [ ] Cross-browser testing (modern + IE11 UMD).
- [ ] Security review (sanitization, escaping, nonces).
- [ ] Documentation (user guide, dev guide).
- **Deliverable**: v1.0 Release candidate.

### Phase 7: PRO Features (Q2 2026+)
- [ ] IAB TCF v2.2 full CMP support.
- [ ] Deep scanner, scheduled rescans.
- [ ] A/B testing on banners.
- [ ] Multisite + cross-domain sharing.
- [ ] Advanced integrations (UET, Meta, TikTok, sGTM).
- **Deliverable**: v1.1 Pro release.

---

## 8. TESTING STRATEGY

### 8.1 Unit Tests (PHPUnit)
- ConsentRepository CRUD operations.
- ConsentLogRepository filtering, export.
- BlockingService pattern matching.
- GeoService region detection.

### 8.2 Integration Tests
- Module initialization with Dashboard.
- Settings save/load via options.
- REST endpoint request/response cycles.
- DataLayer emission (mock GTM).

### 8.3 E2E Tests (Cypress or Playwright)
- Banner renders and is interactive.
- Consent preferences saved to localStorage.
- Scripts blocked pre-consent, unblocked post-consent.
- Geo presets load correctly per region.
- Admin settings persist and affect frontend.

### 8.4 Security Tests
- SQL injection: parameterized queries.
- XSS: banner HTML escaped, custom CSS sanitized.
- CSRF: nonce validation on settings POST.
- GDPR: consent deletion on user account deletion (WP Privacy API).

### 8.5 Performance Tests
- Lighthouse (FCP, LCP, CLS <0.1).
- JS bundle size (<20KB gz).
- DB query count per page (<5).

---

## 9. TECHNICAL STACK & DEPENDENCIES

### 9.1 Backend (PHP)
- **Framework**: WP hooks/filters, PSR-4 autoloading (Composer).
- **DB**: WordPress WPDB API, prepared statements.
- **REST**: WordPress REST API (core).
- **Logging**: WP_DEBUG, custom logs table.

### 9.2 Frontend (JavaScript)
- **Framework**: Alpine.js (lightweight) or React (if large interactivity needed).
- **Build**: Vite (as per plugin standard).
- **CSS**: Tailwind CSS v3 (as per plugin standard).
- **APIs**: Fetch, localStorage, MutationObserver.

### 9.3 Optional Dependencies (PRO)
- **MaxMind GeoIP2**: IP geolocation.
- **IAB TCF vendor list**: TCF v2.2 support.
- **Google Tag Manager API**: GTM template generation.

---

## 10. SUCCESS METRICS

### 10.1 Functional
- ✅ Banner renders in <500ms.
- ✅ Consent categories fully functional.
- ✅ Script blocking 100% accurate.
- ✅ Admin logs capture ≥95% of consents.
- ✅ API endpoints respond within 200ms.

### 10.2 Compliance
- ✅ Consent proof meets GDPR art. 7 requirements.
- ✅ Regional enforcement works per spec.
- ✅ GCM v2 signals correctly emitted.
- ✅ No dark patterns detected (WCAG + EU guidelines).

### 10.3 User Adoption
- ✅ Setup wizard completes in <5 min.
- ✅ Consent rate ≥70% (accept + reject = total consent).
- ✅ <2% support inquiries (MVP 1.0 launch).

### 10.4 Performance
- ✅ Core JS <20KB gz.
- ✅ CLS score <0.1.
- ✅ Accessibility score ≥95 (Lighthouse).

---

## 11. APPENDICES

### 11.1 Glossary
- **CMP**: Consent Management Platform.
- **GCM**: Google Consent Mode.
- **TCF**: Transparency and Consent Framework (IAB).
- **GPP**: Global Privacy Platform (IAB).
- **GDPR**: General Data Protection Regulation (EU).
- **CCPA/CPRA**: California Consumer Privacy Act / California Privacy Rights Act.
- **LGPD**: Lei Geral de Proteção de Dados (Brazil).
- **PIPEDA**: Personal Information Protection and Electronic Documents Act (Canada).

### 11.2 Definitions
- **Prior Consent**: Blocking scripts/cookies until user explicitly consents.
- **Proof of Consent**: Audit log entry documenting user consent decision, timestamp, config version.
- **Dark Pattern**: UI design that manipulates users into unintended consent choices.
- **MutationObserver**: Browser API to detect DOM changes (e.g., dynamically injected scripts).

---

## Document Version History
| Version | Date       | Author | Notes                      |
|---------|------------|--------|----------------------------|
| 1.0     | 2025-12-17 | Team   | Initial formal spec        |

---

**Status: Ready for Development Phase 1**

Approval sign-off: [TBD]
