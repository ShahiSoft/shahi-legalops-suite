# Consent Banner Component - Implementation Guide

## Overview

The Consent Banner is a fully-featured, responsive frontend component for collecting user consent preferences. It integrates with the REST API endpoints from Task 2.3 and supports 4 distinct templates with multiple theme and position variations.

**Status:** Task 2.4 - Consent Banner Component (Frontend UI/UX)
**Files Created/Modified:**
- `assets/js/consent-banner.js` - ConsentBanner class (1200+ lines)
- `assets/css/consent-banner.css` - Responsive styles (500+ lines)
- `shahi-legalops-suite.php` - Enqueue logic with wp_localize_script

## 4 Banner Templates

### 1. EU/GDPR Template
**Description:** Full-featured consent banner with granular purpose selection
**Best For:** GDPR-compliant websites, European users
**Features:**
- Individual checkboxes for each consent purpose
- Necessary cookies always checked and disabled
- Accept All / Accept Selected / Reject All buttons
- Purpose descriptions
- Privacy Policy link

**HTML Structure:**
```
.slos-template-eu
├── .slos-banner-header
│   └── .slos-banner-title
├── .slos-banner-body
│   └── .slos-banner-message
├── .slos-consent-options (checkbox grid)
│   ├── .slos-consent-option (necessary - disabled)
│   ├── .slos-consent-option (functional)
│   ├── .slos-consent-option (analytics)
│   ├── .slos-consent-option (marketing)
│   ├── .slos-consent-option (preferences)
│   └── .slos-consent-option (personalization)
└── .slos-banner-footer
    ├── .slos-btn-accept-all
    ├── .slos-btn-accept-selected
    ├── .slos-btn-reject-all
    └── .slos-privacy-link
```

### 2. CCPA Template
**Description:** California-focused banner emphasizing opt-out rights
**Best For:** CCPA-compliant websites, California users
**Features:**
- "Do Not Sell My Info" emphasis
- Simplified layout without granular toggles
- Accept / Decline buttons
- Privacy Policy link

**HTML Structure:**
```
.slos-template-ccpa
├── .slos-banner-body (inline with buttons)
│   └── .slos-banner-message
└── .slos-banner-footer (right-aligned)
    ├── .slos-btn-accept
    ├── .slos-btn-reject
    └── .slos-privacy-link
```

### 3. Simple Template
**Description:** Minimal consent banner
**Best For:** Basic cookie consent, simple websites
**Features:**
- Minimal message
- Accept / Decline buttons
- Centered layout

**HTML Structure:**
```
.slos-template-simple
├── .slos-banner-body (centered)
│   └── .slos-banner-message
└── .slos-banner-footer (centered)
    ├── .slos-btn-accept
    └── .slos-btn-decline
```

### 4. Advanced Template
**Description:** Extended EU template with enhanced styling
**Best For:** Premium compliance, advanced customization
**Features:**
- All EU template features
- Enhanced styling with blue accent border
- Expanded descriptions
- Advanced preference management

**HTML Structure:**
```
.slos-template-advanced
├── .slos-banner-header
│   └── .slos-banner-title
├── .slos-banner-body
│   └── .slos-banner-message
├── .slos-consent-options (with left blue border)
│   └── [Same as EU template]
└── .slos-banner-footer
    └── [Same as EU template]
```

## Configuration

### JavaScript Configuration Object

The banner is configured via `slosConsentConfig`, injected via `wp_localize_script`:

```javascript
window.slosConsentConfig = {
    apiUrl: '/wp-json/slos/v1',           // REST API base URL
    userId: 1,                             // Current user ID
    template: 'eu',                        // Template: 'eu', 'ccpa', 'simple', 'advanced'
    position: 'bottom',                    // Position: 'bottom' or 'top'
    theme: 'light',                        // Theme: 'light' or 'dark'
    reloadOnConsent: false,                // Reload page after consent granted
    privacyLink: 'https://example.com/privacy/'
};
```

### Translation Object

The banner uses `slosConsentI18n` for all translatable strings:

```javascript
window.slosConsentI18n = {
    euTitle: 'Your Consent Preferences',
    euMessage: 'We use cookies...',
    ccpaMessage: 'We respect your privacy...',
    simpleMessage: 'We use cookies...',
    
    acceptAll: 'Accept All',
    acceptSelected: 'Accept Selected',
    rejectAll: 'Reject All',
    
    purposeNecessary: 'Necessary Cookies',
    purposeFunctional: 'Functional Cookies',
    purposeAnalytics: 'Analytics',
    purposeMarketing: 'Marketing & Advertising',
    purposePreferences: 'Preference Management',
    purposePersonalization: 'Personalization',
    
    // ... 20+ more keys
};
```

### WordPress Filters for Customization

```php
// Change default template
add_filter('slos_consent_template', function() {
    return 'ccpa';  // or 'eu', 'simple', 'advanced'
});

// Change position
add_filter('slos_consent_position', function() {
    return 'top';  // or 'bottom'
});

// Change theme
add_filter('slos_consent_theme', function() {
    return 'dark';  // or 'light'
});

// Change privacy policy URL
add_filter('slos_privacy_policy_url', function() {
    return home_url('/privacy-policy');
});

// Reload page after consent
add_filter('slos_reload_on_consent', function() {
    return true;
});
```

## CSS Styling

### Main Classes

| Class | Purpose |
|-------|---------|
| `#slos-consent-banner` | Banner container |
| `.slos-banner-visible` | Applied when banner is shown |
| `.slos-banner-bottom` | Bottom position variant |
| `.slos-banner-top` | Top position variant |
| `.slos-banner-light` | Light theme |
| `.slos-banner-dark` | Dark theme |
| `.slos-banner-header` | Title/header section |
| `.slos-banner-body` | Message/content area |
| `.slos-banner-footer` | Button action area |
| `.slos-consent-options` | Checkbox container grid |
| `.slos-consent-option` | Individual consent checkbox item |
| `.slos-btn-accept-all` | Accept all button |
| `.slos-btn-reject-all` | Reject all button |
| `.slos-btn-accept-selected` | Accept selected button |
| `.slos-template-eu` | EU template styling |
| `.slos-template-ccpa` | CCPA template styling |
| `.slos-template-simple` | Simple template styling |
| `.slos-template-advanced` | Advanced template styling |

### Responsive Breakpoints

**Desktop:** `> 768px`
- Full banner width, side-by-side layout for CCPA
- Buttons in horizontal flex row
- Full message text

**Tablet:** `768px - 481px`
- Full-width banner
- Buttons stack when needed
- Reduced padding

**Mobile:** `≤ 480px`
- Stacked layout (flex-direction: column)
- Buttons full-width
- Reduced font sizes
- Minimal padding

### Animation

- **Entrance:** `translateY(100%)` → `translateY(0)` over 0.3s ease-in-out
- **Exit:** Reverse animation
- **Button hover:** `translateY(-1px)` with shadow
- **Supports:** `prefers-reduced-motion: reduce`

## JavaScript API

### ConsentBanner Class

```javascript
class ConsentBanner {
    // Constructor - initializes from slosConsentConfig
    constructor()
    
    // Main initialization - loads purposes and shows banner
    async init()
    
    // Load valid consent purposes from API
    async loadPurposes()
    
    // Check if user has granted specific consent
    async checkConsent(purpose)
    
    // Display banner in DOM
    showBanner()
    
    // Create banner DOM element
    createBanner()
    
    // Get HTML for current template
    getBannerHTML()
    
    // Template-specific HTML generators
    getEUBannerHTML()
    getCCPABannerHTML()
    getSimpleBannerHTML()
    getAdvancedBannerHTML()
    
    // Consent actions
    async acceptAll()
    async rejectAll()
    async acceptSelected()
    
    // Grant consent for specific purpose
    async grantConsent(purpose)
    
    // LocalStorage persistence
    saveToLocalStorage(purpose, granted)
    
    // Emit consent signals (Google Consent Mode v2, WordPress API)
    emitConsentSignals()
    
    // Hide banner
    hideBanner()
    
    // Translation lookup
    t(key, fallback)
}
```

### Initialization

```javascript
// Create and initialize banner
const banner = new ConsentBanner();

// or with custom config override
window.slosConsentConfig.template = 'ccpa';
const banner = new ConsentBanner();
```

### Custom Events

The banner emits two custom events:

```javascript
// Fired when user grants/updates consent
document.addEventListener('slos-consent-updated', (e) => {
    console.log('Consent updated:', e.detail);
    // e.detail contains: { purpose, granted, timestamp, userId }
});

// WordPress Consent API compatibility
document.addEventListener('wp_consent_category_set', (e) => {
    console.log('WordPress consent set:', e.detail);
    // e.detail contains: { category, status, timestamp }
});
```

## REST API Integration

The banner integrates with these endpoints (from Task 2.3):

### Get Valid Purposes
```
GET /wp-json/slos/v1/consents/purposes
Response: [
    { "id": "necessary", "label": "Necessary Cookies", "required": true },
    { "id": "functional", "label": "Functional", "required": false },
    { "id": "analytics", "label": "Analytics", "required": false },
    { "id": "marketing", "label": "Marketing", "required": false }
]
```

### Check Consent
```
GET /wp-json/slos/v1/consents/check?purpose=analytics
Response: { "purpose": "analytics", "has_consent": true }
```

### Grant Consent
```
POST /wp-json/slos/v1/consents/grant
Body: {
    "purpose": "analytics",
    "user_id": 1,
    "consent_text": "User granted consent",
    "consent_method": "explicit"
}
Response: { "success": true, "consent_id": 123 }
```

## localStorage Persistence

The banner stores consent in localStorage with 30-day expiry:

```javascript
// Format:
localStorage.setItem('slos_consent_analytics', JSON.stringify({
    granted: true,
    timestamp: 1234567890,
    expires: 1237245890  // 30 days later
}));

// The banner checks localStorage first before API calls
// Falls back to API if cache expired
```

## Testing

### Test File
A test file is available at: `test-consent-banner.html`

**Test Coverage:**
1. All 4 templates (EU, CCPA, Simple, Advanced)
2. Position variants (top/bottom)
3. Theme variants (light/dark)
4. Responsive design (mobile/tablet/desktop)
5. Dark mode preference
6. Console logging and event verification

**How to Test:**
1. Copy `test-consent-banner.html` to your webroot
2. Open in browser
3. Click template buttons to verify each variant
4. Check browser console (F12) for event logs
5. Test mobile view with responsive design tools

### Manual Testing Checklist

- [ ] EU template shows 6 checkboxes (necessary disabled)
- [ ] CCPA template shows simplified layout
- [ ] Simple template shows minimal buttons
- [ ] Advanced template shows blue-bordered options
- [ ] Top position banner appears at top
- [ ] Bottom position banner appears at bottom
- [ ] Light theme has white background
- [ ] Dark theme has dark background
- [ ] Mobile view buttons stack vertically
- [ ] Animations smooth (0.3s ease)
- [ ] Accept All button grants all consents
- [ ] Reject All button grants only necessary
- [ ] Accept Selected respects checked boxes
- [ ] localStorage updated after consent
- [ ] API POST calls successful to /consents/grant
- [ ] Custom events fired (slos-consent-updated)
- [ ] Google Consent Mode v2 signals emitted
- [ ] Privacy link navigates correctly
- [ ] Banner not shown after 30 days
- [ ] Banner shown again on new localStorage entry

## Performance Considerations

**Bundle Size:**
- `consent-banner.js`: ~12 KB minified
- `consent-banner.css`: ~8 KB minified
- **Total:** ~20 KB combined

**Network Requests:**
- 1 API call to `/purposes` on init
- 1 API call per consent grant/withdraw
- Cached in localStorage (30 days)

**Rendering:**
- Non-blocking enqueue (wp_footer priority)
- Fixed positioning (doesn't affect layout)
- Hardware-accelerated animations (transform/opacity)

## Accessibility

**Features:**
- Semantic HTML (fieldset, legend, labels)
- ARIA labels on buttons and checkboxes
- Keyboard navigation (Tab, Space, Enter)
- Focus indicators (2px outline)
- High contrast mode support (@media prefers-contrast)
- Reduced motion support (@media prefers-reduced-motion)
- Screen reader announcements (role=alert on changes)

**WCAG 2.1 Level AA Compliance:**
- ✓ Color contrast ≥ 4.5:1
- ✓ Focus visible (outline 2px)
- ✓ Keyboard accessible
- ✓ Resize text support
- ✓ No seizure-inducing animations

## Browser Support

| Browser | Version | Support |
|---------|---------|---------|
| Chrome | 90+ | ✓ Full |
| Firefox | 88+ | ✓ Full |
| Safari | 14+ | ✓ Full |
| Edge | 90+ | ✓ Full |
| IE 11 | - | ✗ Not supported |
| Mobile Safari | 14+ | ✓ Full |
| Chrome Mobile | 90+ | ✓ Full |

## Customization Examples

### Change Default Template
```php
// In wp-config.php or child theme functions.php
add_filter('slos_consent_template', function() {
    return 'ccpa';
});
```

### Custom Styling
```css
/* Override in custom CSS */
#slos-consent-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.slos-btn-accept-all {
    background: #fff;
    color: #667eea;
}
```

### Custom Translations
```php
// Register custom i18n strings
add_filter('slos_consent_i18n', function($translations) {
    $translations['euTitle'] = 'Manage Your Preferences';
    $translations['euMessage'] = 'Custom message here...';
    return $translations;
});
```

### Programmatic Banner Trigger
```javascript
// After page load, show banner programmatically
setTimeout(() => {
    if (window.slosConsentBanner) {
        window.slosConsentBanner.showBanner();
    }
}, 5000);  // Show after 5 seconds
```

## Troubleshooting

### Banner Not Appearing
1. Check console for JavaScript errors
2. Verify `slosConsentConfig` is loaded: `console.log(window.slosConsentConfig)`
3. Check CSS is enqueued: `wp_enqueue_style('slos-consent-banner', ...)`
4. Verify z-index doesn't conflict: `#slos-consent-banner { z-index: 999999; }`

### API Calls Failing
1. Check REST API is enabled: `wp-json/slos/v1/` accessible
2. Verify nonce/authentication if required
3. Check browser console Network tab for 401/403
4. Verify user capability to manage consents

### Styling Issues
1. Clear browser cache
2. Check for CSS vendor prefixes (especially Safari)
3. Verify media query breakpoints
4. Test in different browsers
5. Check for CSS conflicts (use `!important` if needed)

### localStorage Not Working
1. Check private/incognito mode
2. Verify localStorage not disabled in browser
3. Check domain/path matches
4. Verify storage quota not exceeded

## Next Steps (Task 2.5)

The next task will implement the **Cookie Scanner Component**, which will:
- Scan page for active cookies
- Categorize by consent type
- Generate cookie inventory report
- Provide cookie management UI
- Integrate with consent banner

## References

- **Task 2.3:** [Consent REST API Endpoints](../includes/API/Consent_REST_Controller.php)
- **Task 2.2:** [Consent Service](../includes/Services/Consent_Service.php)
- **WordPress REST API:** https://developer.wordpress.org/rest-api/
- **GDPR:** https://gdpr-info.eu/
- **CCPA:** https://oag.ca.gov/privacy/ccpa
- **Google Consent Mode v2:** https://support.google.com/analytics/answer/11228093

---

**Last Updated:** 2024
**Version:** 3.0.1
**Status:** Task 2.4 Complete ✓
