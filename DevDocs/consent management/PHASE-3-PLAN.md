# Phase 3 Implementation Plan: Geo & Compliance

**Status**: Ready to Start  
**Timeline**: Weeks 5-6  
**Date**: December 17, 2025

---

## 📋 Phase 3 Overview

Phase 3 adds geo-location detection and regional compliance enforcement. Users in different regions (EU, UK, US-CA, BR, AU) will see different banner variants and have different blocking rules applied automatically based on their location.

### Deliverables
- [ ] GeoService implementation
- [ ] Regional preset system  
- [ ] Prior-consent enforcement per region
- [ ] IP geolocation integration
- [ ] Regional compliance modes (GDPR, CCPA, LGPD, etc.)
- [ ] Settings UI for geo management

---

## 🏗️ Architecture Overview

```
User Request
    ↓
GeoService.detect_region() → Determines user region from IP
    ↓
Consent.initialize() → Loads regional preset (GDPR/CCPA/etc.)
    ↓
BlockingService → Applies region-specific blocking rules
    ↓
ConsentSignalService → Emits region-specific signals
    ↓
Geo-Enforcer → Applies prior-consent requirements by region
```

---

## 🔧 Implementation Tasks

### Task 3.1: Create GeoService

**File**: `includes/modules/consent/services/GeoService.php`

**Responsibilities**:
1. Detect user region from IP address
2. Map IP to region code (EU, UK, US-CA, BR, AU, ZA)
3. Return region with compliance metadata

**Key Methods**:
```php
public function detect_region(string $ip = ''): array {
    // Returns: ['region' => 'EU', 'mode' => 'gdpr', 'country' => 'DE', ...]
}

public function get_region_config(string $region): array {
    // Returns regional settings (blocking rules, banner variant, retention, etc.)
}

public function is_regulated_region(string $region): bool {
    // Returns true if region requires prior-consent blocking
}
```

**Dependencies**:
- MaxMind GeoIP2 (if integrated) OR free IP geolocation API
- WordPress transients for caching

**Testing**:
- Test with known IPs from each region
- Verify region detection accuracy
- Test caching behavior

---

### Task 3.2: Create GeoService Interface

**File**: `includes/modules/consent/interfaces/GeoServiceInterface.php`

```php
namespace ShahiLegalOpsSuite\Modules\Consent\Interfaces;

interface GeoServiceInterface {
    public function detect_region(string $ip = ''): array;
    public function get_region_config(string $region): array;
    public function is_regulated_region(string $region): bool;
    public function get_supported_regions(): array;
}
```

---

### Task 3.3: Create Regional Preset Configuration

**File**: `includes/modules/consent/config/regional-presets.php`

**Content**: Defines blocking rules, banner variants, and compliance modes per region.

```php
return [
    'EU' => [
        'mode'              => 'gdpr',
        'countries'         => ['AT', 'BE', 'BG', ...], // All 27 EU countries
        'requires_consent'  => true,
        'banner_variant'    => 'gdpr',
        'blocking_rules'    => ['google-analytics', 'facebook-pixel', ...],
        'retention_days'    => 365,
        'retention_policy'  => 'anonymize_after_12mo',
    ],
    'UK' => [
        'mode'              => 'uk_gdpr',
        'countries'         => ['GB'],
        'requires_consent'  => true,
        'banner_variant'    => 'gdpr',
        'blocking_rules'    => ['google-analytics', 'facebook-pixel', ...],
        'retention_days'    => 365,
    ],
    'US-CA' => [
        'mode'              => 'ccpa',
        'states'            => ['CA'],
        'requires_consent'  => false, // CCPA is opt-out
        'banner_variant'    => 'ccpa',
        'blocking_rules'    => [],
        'retention_days'    => 90,
    ],
    // ... more regions
];
```

---

### Task 3.4: Integrate GeoService with Consent Module

**File**: `includes/modules/consent/Consent.php`

**Changes**:
1. Initialize GeoService in `init_services()`
2. Detect user region on page load
3. Load regional preset
4. Pass region to blocking/signal services

```php
public function initialize(): void {
    // ... existing code ...
    
    // Detect user region early
    add_action('plugins_loaded', [$this, 'detect_user_region'], 8);
}

public function detect_user_region(): void {
    $geo = $this->get_service('geo');
    $this->user_region = $geo->detect_region($_SERVER['REMOTE_ADDR'] ?? '');
    
    // Store in session for later use
    if (!isset($_SESSION['complyflow_region'])) {
        $_SESSION['complyflow_region'] = $this->user_region['region'];
    }
}
```

---

### Task 3.5: Apply Regional Blocking Rules

**File**: `includes/modules/consent/services/BlockingService.php`

**Changes**:
1. Accept region parameter in constructor
2. Register only region-specific blocking rules
3. Enforce prior-consent blocking if region requires it

```php
public function __construct(ConsentRepository $repository, string $region = '') {
    $this->repository = $repository;
    $this->region = $region;
    $this->register_regional_rules();
}

private function register_regional_rules(): void {
    $regional_rules = $this->get_regional_blocking_rules($this->region);
    
    foreach ($regional_rules as $rule) {
        $this->register_blocking_rule($rule);
    }
}
```

---

### Task 3.6: Regional Signal Emission

**File**: `includes/modules/consent/services/ConsentSignalService.php`

**Changes**:
1. Accept region parameter
2. Emit region-specific signals (GCM v2 for EU, CCPA notice for US-CA)

```php
public function __construct(string $region = '') {
    $this->region = $region;
}

public function emit_regional_signals(array $consents): void {
    if ($this->region === 'EU' || $this->region === 'UK') {
        $this->emit_google_consent_mode($consents);
    }
    
    if ($this->region === 'US-CA') {
        $this->emit_ccpa_notice();
    }
}
```

---

### Task 3.7: Frontend Region Detection (JavaScript)

**File**: `assets/js/consent-geo.js` (new)

**Responsibilities**:
1. Detect region on client side (optional, for UX)
2. Load region-specific banner variant
3. Apply region-specific CSS classes

```javascript
document.addEventListener('complyflow-ready', function() {
    const region = complyflowData.region;
    const bannerEl = document.getElementById('complyflow-banner');
    
    // Apply region-specific class
    bannerEl.classList.add(`banner-${region.toLowerCase()}`);
    
    // Load region-specific styles
    if (region === 'EU') {
        loadStyle('consent-banner-gdpr.css');
    } else if (region === 'US-CA') {
        loadStyle('consent-banner-ccpa.css');
    }
});
```

**Enqueue**: Add to `Consent.php::enqueue_frontend_assets()`

---

### Task 3.8: Regional Admin Settings Page

**File**: `includes/modules/consent/views/geo-settings.php` (new)

**UI Elements**:
- [ ] Region selector dropdown (currently detected region shown)
- [ ] List of supported regions with their modes
- [ ] Regional preset editor (blocking rules per region)
- [ ] Retention policy selector per region
- [ ] Prior-consent toggle per region

---

### Task 3.9: Database & Logging Updates

**Already Done** (Phase 1):
- `complyflow_consent_logs.region` column exists
- `idx_region` index created

**To Do**:
- Update ConsentRepository to filter logs by region
- Update REST API to support `?region=EU` filter

---

### Task 3.10: Testing & QA

**Unit Tests**:
- [ ] GeoService detects regions correctly
- [ ] Regional presets load properly
- [ ] Blocking rules apply per region
- [ ] Signals emit correctly per region

**Integration Tests**:
- [ ] EU user sees GDPR banner
- [ ] US-CA user sees CCPA notice
- [ ] Logs capture region correctly
- [ ] Admin can filter logs by region

**Edge Cases**:
- [ ] User with VPN (proxy IP) detection
- [ ] Newly regulated region added
- [ ] Region changed during session

---

## 📊 Detailed Task Breakdown

### GeoService Implementation

```
GeoService
├── IP Geolocation
│   ├── MaxMind integration (optional)
│   ├── Free IP API fallback
│   └── Transient caching (1 hour)
├── Region Detection
│   ├── IP → Country code mapping
│   ├── Country code → Region grouping
│   └── Return region + metadata
└── Regional Config
    ├── Load preset for region
    ├── Merge with site settings
    └── Return combined config
```

**Sample Flow**:
```
User IP: 203.0.113.45 (Australia)
    ↓
GeoService::detect_region('203.0.113.45')
    ↓
IP → Country: 'AU'
    ↓
Lookup: 'AU' → Region: 'AU'
    ↓
Return: ['region' => 'AU', 'mode' => 'privacy_act', ...]
```

---

### Regional Presets

**Supported Regions** (Phase 3 MVP):
1. **EU** - GDPR (27 countries)
2. **UK** - UK GDPR (1 country)
3. **US-CA** - CCPA (California)
4. **BR** - LGPD (Brazil)
5. **AU** - Privacy Act (Australia)

**Phase 3+ Additions**:
- Canada (PIPEDA)
- New Zealand (Privacy Act 2020)
- South Africa (POPIA)
- China (PIPL)
- India (DPIA)

---

### Prior-Consent Enforcement

**For EU/UK** (GDPR):
- All non-essential scripts blocked until consent
- Banner shown with opt-in requirement
- Consent required before GA, Facebook Pixel, etc.

**For US-CA** (CCPA):
- Scripts load by default (opt-out model)
- "Do Not Sell My Personal Information" link shown
- Consent not required unless opted-in to tracking

**For BR** (LGPD):
- Similar to GDPR with additional category: "Legitimate Interest"

---

## 🎯 Success Criteria

### Functional
- [ ] GeoService detects region with 95%+ accuracy
- [ ] Regional presets load in <50ms
- [ ] Correct blocking rules applied per region
- [ ] Correct banner variant shown per region
- [ ] Regional signals emitted correctly

### Compliance
- [ ] EU/UK users have prior-consent blocking
- [ ] US-CA users have opt-out messaging
- [ ] Regional retention policies enforced
- [ ] Logs capture region for all consents

### Performance
- [ ] Region detection < 100ms
- [ ] Preset loading < 50ms
- [ ] Geo-enforcement rules < 20ms

### User Experience
- [ ] Banner adapts to region in <500ms
- [ ] No FOUC (Flash of Unstyled Content)
- [ ] Regional variant clearly distinguishable

---

## 📅 Timeline

**Week 5**:
- Day 1-2: Create GeoService + Interface
- Day 2-3: Create regional preset configuration
- Day 3-4: Integrate with Consent module
- Day 4-5: Frontend region detection JS

**Week 6**:
- Day 1-2: Regional blocking rule application
- Day 2-3: Regional signal emission
- Day 3-4: Admin settings UI
- Day 4-5: Testing & QA

---

## 🔗 Related Documentation

- [PRODUCT-SPEC.md § 2.1.4](./PRODUCT-SPEC.md#214-geo--localization) - Geo requirements
- [PRODUCT-SPEC.md § Phase 3](./PRODUCT-SPEC.md#phase-3-geo--compliance-weeks-5–6) - Phase 3 details
- [IMPLEMENTATION-QUICKSTART.md § Phase 3](./IMPLEMENTATION-QUICKSTART.md) - Phase 3 steps

---

**Status**: Ready to Begin  
**Next Step**: Start Task 3.1 - Create GeoService
