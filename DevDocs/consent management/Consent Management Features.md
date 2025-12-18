# Consent Management Features — Competitive Research and 2026+ Plan

Status: Draft v0.1
Last Updated: 2025-12-17
Scope: Consent management only (excludes accessibility, policy pages, DSR)

---

## Summary
This document consolidates best-in-class Consent Management Platform (CMP) features from top WordPress.org and premium vendors, then defines a forward-looking spec to make our Consent Management module market-leading in 2026 and beyond. Focus areas: robust legal coverage, reliable blocking, modern consent signals (TCF v2.2, GCM v2, GPP), performance, accessibility, and enterprise-grade auditability.

Sources reviewed (high level):
- Complianz – GDPR/CCPA Cookie Consent (WP.org)
- CookieYes – Cookie Banner for Cookie Consent (WP.org)
- Cookie Notice & Compliance by Hu-manity.co (WP.org)
- Cookiebot by Usercentrics (WP.org)
- iubenda (WP.org)
- Google Tag Manager Consent Mode guidance (GCM v2; ad_user_data, ad_personalization, analytics_storage, etc.)

---

## Competitor Highlights (What "Best" Looks Like Today)
- Complianz
  - Region-aware, subregion overrides; extensive integrations; script/iFrame blocking with placeholders; Proof of Consent; WP Consent API integration; A/B testing on consent banners (pro); IAB TCF support; Google Consent Mode.
- CookieYes
  - Strong banner + preference center; automatic blocking; consent logging/export; Geo-targeting; Google Consent Mode v2; Microsoft UET Consent Mode; IAB TCF v2.2; Global Privacy Control (GPC); subdomain consent sharing; scheduled scans.
- Cookie Notice (Hu-manity.co)
  - Intentional Consent (equal choice); consent duration selector; automatic blocking; consent analytics; GCM integration; anti–dark pattern defaults; multi-domain management via web app.
- Cookiebot (Usercentrics)
  - Google-certified CMP for Consent Mode v2; deep cookie scanning and repository; continuous updates; secure EU consent storage; extensive language support.
- iubenda
  - Auto-configuration; granular category control; automatic script blocking; GCM (basic/advanced) and IAB TCF integration; AMP support; consent analytics; form consent records.

Key takeaways to exceed:
- First-class support for Google Consent Mode v2, IAB TCF v2.2, and broad geo frameworks (EU/UK, US state laws, LGPD, PIPEDA, POPIA, etc.).
- Reliable “prior consent” blocking including dynamic/inline scripts, iFrames, SPA apps, and late-injected tags.
- Enterprise-grade consent logs, audit trails, re-consent, and subdomain/cross-domain sync.
- Optimization (A/B tests), analytics, and developer-first APIs.

---

## Ultimate Feature Set (2026+)

### Banner & UX
- Templates: top/bottom bar, modal, slide-in, sticky footer, center dialog, mobile-first variants.
- Equal-choice first layer: Accept All / Reject All / Customize.
- Preference Center: granular per-category and per-service toggles; context help.
- Consent duration selector: configurable validity (e.g., 6 months) and per-region defaults.
- Theming: color, typography, logo, corner radius, spacing; dark mode; CSS variables; custom CSS.
- Multilingual: auto-language detection; manual overrides; WPML/Polylang support.
- Accessibility: WCAG 2.2 AA patterns; keyboard focus trap; ARIA roles/labels; screen-reader text; sufficient contrast.
- Revisit widget: floating button/link for update/withdraw; optional footer link.
- AMP & SPA support: AMP components; SPA route-change handling; no hard reload on preference updates.

### Consent Signals & Frameworks
- Google Consent Mode v2: `ad_storage`, `analytics_storage`, `ad_user_data`, `ad_personalization`, plus regional defaults.
- IAB TCF v2.2: full CMP API (`__tcfapi`) bridge; vendor list, purposes, legitimate interest; addtl_consent.
- US privacy signals: `__gpp` (IAB GPP) for US state laws; `__uspapi` (CCPA/CPRA legacy) compatibility.
- Browser signals: Global Privacy Control (GPC); Do Not Track (DNT) optional respect.
- WP Consent API: publish/subscribe consent categories and statuses across plugins.

### Blocking & Enforcement Engine
- Prior-consent blocking for: inline/external scripts, iFrames, pixels, images with trackers.
- Pattern-based controls: URL substring/regex, attribute rules, CSP nonces/hashes compatibility.
- Dynamic injection handling: MutationObserver to catch late/dynamic scripts; queue and replay post-consent.
- iFrame placeholders: per-service templates (YouTube, Vimeo, Maps, social embeds) with click-to-activate.
- GTM integration: Consent Initialization timing; Tag Manager Consent APIs; dataLayer events on updates.
- Resilient fallback: non-blocking JS (<20KB gz), no jQuery; fails-safe if JS disabled (graceful degrade).

### Scanning & Inventory
- Deep scanner: detect cookies, localStorage, sessionStorage, indexedDB, beacons, pixels, scripts.
- Service mapping: maintain tracker repository (providers, purposes, retention, domains) with auto-categorization.
- Schedules: weekly scans; delta reports; notifications on changes (new trackers or category drift).
- Admin reconciliation: approve/override auto-detected items; CSV import/export.

### Integrations & Tagging
- Analytics/Ads: GA4, Google Ads, Floodlight, Microsoft Ads/UET, Facebook/Meta Pixel, TikTok, LinkedIn, Pinterest, Hotjar, HubSpot, Intercom, Segment, RudderStack, Sentry, etc.
- Builders/Forms: Elementor, Divi, Beaver Builder, CF7, WPForms, Gravity Forms, Ninja Forms.
- E‑commerce: WooCommerce checkout consent hooks and post-purchase events.
- Caching/CDN: WP Rocket, LiteSpeed Cache, W3TC; delay/deferral compatibility; edge cache-safe.
- Server-side tagging: propagate consent to sGTM; HTTP headers for consent state when feasible.

### Geo/Regionalization
- IP geolocation with privacy: on-device DB or privacy-preserving lookup; region bundles (EU/EEA, UK, CH, BR, CA, ZA, AU, JP, US states).
- Policy-driven presets: per-region banner texts, button sets, defaults, and consent categories.
- Conditional enforcement: prior consent only where required; regional suppression where not applicable.

### Logging & Audit
- Proof of Consent: pseudonymous user ID, timestamp, purposes granted/denied, versioned banner config, region.
- Data minimization: IP anonymization, user agent hashing; retention policy per region.
- Export: CSV/JSON; REST API for DSR references; audit trails of admin changes.
- Re-consent: trigger on banner changes, vendor list updates, or regulation shifts.
- Cross-(sub)domain sharing: consent state shared across subdomains; optional cross-domain linking.

### Multi‑Site & Enterprise
- Network-wide policies: centralize presets; site-level overrides; bulk push; per-site analytics.
- Roles & permissions: granular caps for viewing logs, editing templates, publishing changes.
- SSO-ready admin (optional), 2FA for dashboard actions (if we host a web app component).

### Developer Experience
- JavaScript SDK: subscribe to consent updates; query current categories; promise-based gating helpers.
- PHP & REST API: read/write consent state, logs export, banner versions; webhooks for changes.
- Data layer events: `consent_init`, `consent_update`, `consent_withdrawn` with normalized payloads.
- Extensibility: service adapters for new vendors; filterable repository; hooks/actions documented.

### Performance, Accessibility, Reliability
- Performance: <20KB gz core JS; critical CSS inline; async/defer; no layout shift.
- Accessibility: tested patterns; screen-reader only texts; focus management; AAA-ready options.
- Resilience: race-free GTM Consent Initialization; idempotent updates; offline-safe storage.

### Analytics & Optimization
- Consent analytics: rates by region/device/source; drop-off at layers; time-to-consent.
- A/B testing: banner variants (layout/text/buttons) with statistical significance reporting.
- Recommendations: heuristic tips (e.g., "Add Reject on first layer for EU").

### Future-Forward (2026+)
- Privacy Sandbox alignment: consent-aware modeling; Topics/Protected Audiences coordination.
- Full IAB GPP coverage: evolving US state signals; automatic string generation and updates.
- Real-time multi-device sync: optional authenticated user linking to sync preferences across devices.
- Edge consent storage: CDN/edge KV backing for ultra-fast revisit reads (optional enterprise add-on).
- AI-assisted optimization: propose copy/layout variants to improve acceptance while remaining compliant.
- In-app WebView patterns: SDK guidance for hybrid/mobile wrappers.

---

## Implementation Notes (Our Stack)
- JS: lightweight, framework-agnostic; ESM build; no jQuery; TypeScript definitions for SDK.
- PHP: integrate WP Consent API; expose REST endpoints; consent logs in custom table with indexes.
- GTM/GCM: emit consent updates at Consent Initialization; provide GTM templates where useful.
- Repository: seed with common providers; allow updates via JSON feed; local override capability.
- Security: nonces for admin actions; capability checks; encryption at rest for sensitive log fields.

---

## MVP vs. Pro Cut (Recommendation)
- MVP
  - Banner templates with equal choice
  - Preference Center (categories) and revisit widget
  - Prior-consent blocking for common scripts/iFrames
  - Google Consent Mode v2 signaling
  - Geo presets for EU/UK + US (basic)
  - Consent logs + export; re-consent on config change
  - WP Consent API + basic JS SDK
- Pro
  - IAB TCF v2.2 (CMP) + GPP signals
  - Deep scanner + repository feed + scheduled scans
  - A/B testing + consent analytics dashboard
  - Subdomain/cross-domain sharing; multisite control
  - Expanded integrations (Microsoft UET, Meta, TikTok, LinkedIn)
  - Server-side tagging consent propagation; edge storage option

---

## References
- Complianz – GDPR/CCPA Cookie Consent (wordpress.org)
- CookieYes – Cookie Banner for Cookie Consent (wordpress.org)
- Cookie Notice & Compliance for GDPR/CCPA (wordpress.org)
- Cookiebot by Usercentrics (wordpress.org)
- iubenda | All-in-one Compliance (wordpress.org)
- Google Tag Manager – Consent mode support (support.google.com)
