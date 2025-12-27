# TASK 2.7: Geolocation Detection

**Phase:** 2 (Consent Management - GEO)  
**Effort:** 6-8 hours  
**Prerequisites:** Tasks 2.1–2.6 complete (Repository, Service, REST API, Banner, Scanner, Blocker)  
**Next Task:** [task-2.8-admin-dashboard.md](task-2.8-admin-dashboard.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 2.7 (Geolocation Detection) for the Shahi LegalOps Suite WordPress plugin.

GOAL:
- Detect user country/region via IP using swappable provider abstraction (MaxMind, IP2Location, ipapi)
- Determine applicable regulation (GDPR/EEA, UK-GDPR/PECR, CCPA/CPRA, LGPD, PIPEDA, POPIA, default)
- Auto-select consent banner template based on location/regulation
- Provide fallback and caching layer (transients/localStorage)
- Respect privacy: do not store IPs permanently; only for audit trail when consent is recorded (hash IP)

INPUT STATE (verify):
✅ Consent REST API exists `/wp-json/slos/v1/consents/*`
✅ Consent Service ready (grant/withdraw)
✅ Frontend assets present `assets/js/`, `assets/css/`
✅ Banner templates available (Task 2.4)

DELIVERABLES:
1) PHP service `includes/Services/GeolocationService.php` with provider abstraction
2) Provider classes: MaxMind, IP2Location, ipapi
3) Regulation detector utility with mappings: EU/EEA+UK→gdpr, US-CA→ccpa, BR→lgpd, CA→pipeda, ZA→popia, default
4) WordPress hooks to expose `slos_geo_context` and banner auto-selection (default→simple, gdpr→eu, ccpa→ccpa, lgpd→advanced)
5) Admin settings for provider keys and enable/disable geolocation
6) Frontend JS utility to read cached geo context and influence banner; allow override via option `slos_banner_template_override`

IMPLEMENTATION NOTES:
- Provider calls should be robust; timeout 1500ms; fail gracefully
- Cache results per IP in transient for 12h; on frontend also cache in localStorage with TTL
- Regulation map: EU/EEA+UK → GDPR; US-CA → CCPA; BR → LGPD; else default
- Auto template: GDPR→eu; CCPA→ccpa; LGPD→advanced; default→simple
- Allow override via WP option `slos_banner_template_override`

DELIVER:
- COMPLETE code blocks with classes, hooks, admin fields, JS helpers
- Verification steps using wp-cli and curl
- Troubleshooting and rollback
```

---

## CONTEXT
This module adds IP-based geolocation with provider abstraction and uses the result to choose the best consent banner template and regulation labels. This ensures compliance across multiple jurisdictions.

---

## INPUT STATE VERIFICATION

```bash
# Check REST API exists
curl -sS http://localhost/wp-json/slos/v1/consents/purposes | jq .status

# Ensure options return defaults (geolocation disabled before config):
wp option get slos_geolocation_enabled || echo "(not set)"
wp option get slos_geolocation_provider || echo "(not set)"
wp option get slos_banner_template_override || echo "(not set)"
```

---

## COMPLETE CODE

### 1) PHP: GeolocationService and Provider Abstraction

Location: `includes/Services/GeolocationService.php`

```php
<?php
/**
 * Geolocation Service
 *
 * @package Shahi\LegalOps\Services
 * @since 3.0.1
 */

namespace Shahi\LegalOps\Services;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class GeolocationService {
    const TRANSIENT_PREFIX = 'slos_geo_';
    const CACHE_TTL = 12 * HOUR_IN_SECONDS;

    protected $enabled;
    protected $provider;

    public function __construct() {
        $this->enabled  = (bool) get_option( 'slos_geolocation_enabled', false );
        $providerKey    = (string) get_option( 'slos_geolocation_provider', 'ipapi' );
        $this->provider = $this->makeProvider( $providerKey );
    }

    protected function makeProvider( $key ) {
        switch ( $key ) {
            case 'maxmind':
                return new Provider\MaxMindProvider();
            case 'ip2location':
                return new Provider\IP2LocationProvider();
            case 'ipapi':
            default:
                return new Provider\IpApiProvider();
        }
    }

    public function is_enabled() {
        return $this->enabled;
    }

    public function detect( $ip ) {
        if ( ! $this->enabled || empty( $ip ) ) {
            return $this->defaultContext();
        }

        $cacheKey = self::TRANSIENT_PREFIX . md5( $ip );
        $cached   = get_transient( $cacheKey );
        if ( $cached ) {
            return $cached;
        }

        $ctx = $this->provider->lookup( $ip );
        if ( ! is_array( $ctx ) || empty( $ctx['country_code'] ) ) {
            $ctx = $this->defaultContext();
        }

        $ctx['regulation'] = RegulationDetector::detect( $ctx['country_code'], $ctx['region'] ?? '', $ctx['continent'] ?? '' );
        $ctx['banner']     = RegulationDetector::banner_for( $ctx['regulation'] );

        set_transient( $cacheKey, $ctx, self::CACHE_TTL );
        return $ctx;
    }

    protected function defaultContext() {
        return [
            'country_code' => 'UN',
            'country_name' => 'Unknown',
            'region'       => '',
            'continent'    => '',
            'regulation'   => 'default',
            'banner'       => RegulationDetector::banner_for( 'default' ),
        ];
    }
}

class RegulationDetector {
    public static function detect( $country, $region = '', $continent = '' ) {
        $country = strtoupper( $country );
        $region  = strtoupper( $region );
        $continent = strtoupper( $continent );

        // EU/EEA + UK – GDPR
        $euCountries = [
            'AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE','IS','LI','NO','UK'
        ];
        if ( in_array( $country, $euCountries, true ) || $continent === 'EU' ) {
            return 'gdpr';
        }

        // California – CCPA/CPRA (basic US mapping)
        if ( $country === 'US' && $region === 'CA' ) {
            return 'ccpa';
        }

        // Brazil – LGPD
        if ( $country === 'BR' ) {
            return 'lgpd';
        }

        return 'default';
    }

    public static function banner_for( $regulation ) {
        switch ( $regulation ) {
            case 'gdpr':
                return 'eu';
            case 'ccpa':
                return 'ccpa';
            case 'lgpd':
                return 'advanced';
            default:
                return 'simple';
        }
    }
}
```

### 2) PHP: Provider Classes

Location: `includes/Services/Provider/`

```php
<?php
namespace Shahi\LegalOps\Services\Provider;
if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class BaseProvider {
    protected $timeout = 1.5;

    abstract public function lookup( $ip );

    protected function request( $url, $headers = [] ) {
        $args = [
            'headers' => $headers,
            'timeout' => $this->timeout,
        ];
        $res = wp_remote_get( $url, $args );
        if ( is_wp_error( $res ) ) {
            return [];
        }
        $code = wp_remote_retrieve_response_code( $res );
        $body = wp_remote_retrieve_body( $res );
        if ( $code !== 200 || empty( $body ) ) {
            return [];
        }
        $json = json_decode( $body, true );
        return is_array( $json ) ? $json : [];
    }
}
```

```php
<?php
namespace Shahi\LegalOps\Services\Provider;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class IpApiProvider extends BaseProvider {
    public function lookup( $ip ) {
        $url = sprintf( 'https://ipapi.co/%s/json/', rawurlencode( $ip ) );
        $j = $this->request( $url );
        if ( empty( $j ) ) return [];
        return [
            'country_code' => $j['country_code'] ?? '',
            'country_name' => $j['country_name'] ?? '',
            'region'       => $j['region_code'] ?? '',
            'continent'    => $j['continent_code'] ?? '',
        ];
    }
}
```

```php
<?php
namespace Shahi\LegalOps\Services\Provider;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class MaxMindProvider extends BaseProvider {
    public function lookup( $ip ) {
        $license = get_option( 'slos_maxmind_license', '' );
        if ( empty( $license ) ) { return []; }
        $url = sprintf( 'https://geoip.maxmind.com/geoip/v2.1/city/%s?license_key=%s', rawurlencode( $ip ), rawurlencode( $license ) );
        $j = $this->request( $url );
        if ( empty( $j ) ) return [];
        $country = $j['country']['iso_code'] ?? '';
        $region  = '';
        if ( ! empty( $j['subdivisions'] ) && is_array( $j['subdivisions'] ) ) {
            $region = $j['subdivisions'][0]['iso_code'] ?? '';
        }
        return [
            'country_code' => $country,
            'country_name' => $j['country']['names']['en'] ?? '',
            'region'       => $region,
            'continent'    => $j['continent']['code'] ?? '',
        ];
    }
}
```

```php
<?php
namespace Shahi\LegalOps\Services\Provider;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class IP2LocationProvider extends BaseProvider {
    public function lookup( $ip ) {
        $apiKey = get_option( 'slos_ip2location_api_key', '' );
        if ( empty( $apiKey ) ) { return []; }
        $url = sprintf( 'https://api.ip2location.io/?key=%s&ip=%s&format=json', rawurlencode( $apiKey ), rawurlencode( $ip ) );
        $j = $this->request( $url );
        if ( empty( $j ) ) return [];
        return [
            'country_code' => $j['country_code'] ?? '',
            'country_name' => $j['country_name'] ?? '',
            'region'       => $j['region_name'] ?? '',
            'continent'    => $j['continent_code'] ?? '',
        ];
    }
}
```

### 3) PHP: Hooks and Admin Settings

Location: `includes/Core/GeolocationHooks.php` and option registration in `shahi-legalops-suite.php`

```php
<?php
namespace Shahi\LegalOps\Core;
use Shahi\LegalOps\Services\GeolocationService;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class GeolocationHooks {
    public static function init() {
        add_action( 'init', [ __CLASS__, 'register_options' ] );
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'localize_geo_context' ] );
        add_filter( 'slos/banner_template', [ __CLASS__, 'auto_select_banner' ] );
    }

    public static function register_options() {
        register_setting( 'slos_settings', 'slos_geolocation_enabled', [ 'type' => 'boolean', 'default' => false ] );
        register_setting( 'slos_settings', 'slos_geolocation_provider', [ 'type' => 'string', 'default' => 'ipapi' ] );
        register_setting( 'slos_settings', 'slos_maxmind_license', [ 'type' => 'string', 'default' => '' ] );
        register_setting( 'slos_settings', 'slos_ip2location_api_key', [ 'type' => 'string', 'default' => '' ] );
        register_setting( 'slos_settings', 'slos_banner_template_override', [ 'type' => 'string', 'default' => '' ] );
    }

    public static function localize_geo_context() {
        if ( is_admin() ) return;
        $service = new GeolocationService();
        $ip      = $_SERVER['REMOTE_ADDR'] ?? '';
        $ctx     = $service->detect( $ip );
        wp_register_script( 'slos-geo-context', false, [], '3.0.1', true );
        wp_enqueue_script( 'slos-geo-context' );
        wp_add_inline_script( 'slos-geo-context', 'window.slosGeoContext=' . wp_json_encode( $ctx ) . ';' );
    }

    public static function auto_select_banner( $template ) {
        $override = get_option( 'slos_banner_template_override', '' );
        if ( ! empty( $override ) ) { return $override; }
        $service = new GeolocationService();
        $ip      = $_SERVER['REMOTE_ADDR'] ?? '';
        $ctx     = $service->detect( $ip );
        return $ctx['banner'] ?? $template;
    }
}

// Bootstrap from main plugin
\Shahi\LegalOps\Core\GeolocationHooks::init();
```

Update `shahi-legalops-suite.php` to ensure the above file loads (if not using autoloader):

```php
// require_once __DIR__ . '/includes/Core/GeolocationHooks.php';
```

### 4) JS: Frontend Helper (localStorage caching + banner influence)

Location: `assets/js/geolocation-helper.js`

```javascript
(function(){
  'use strict';

  function setCache(ctx){
    try{
      localStorage.setItem('slos_geo_context', JSON.stringify({ctx:ctx, ts:Date.now()}));
    }catch(e){}
  }
  function getCache(){
    try{
      var v = localStorage.getItem('slos_geo_context');
      if(!v) return null;
      var o = JSON.parse(v);
      var ttl = 12*60*60*1000; // 12h
      if(!o || !o.ts || (Date.now()-o.ts)>ttl) return null;
      return o.ctx;
    }catch(e){return null}
  }

  function influenceBanner(){
    var ctx = window.slosGeoContext || getCache();
    if(!ctx){return;}
    setCache(ctx);
    document.documentElement.setAttribute('data-slos-regulation', ctx.regulation||'default');
    document.documentElement.setAttribute('data-slos-country', ctx.country_code||'UN');
    document.documentElement.setAttribute('data-slos-banner', ctx.banner||'simple');
    // If banner script listens to this, it can switch template before render
    document.dispatchEvent(new CustomEvent('slos-geo-ready', {detail:ctx}));
  }

  if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded', influenceBanner);
  }else{ influenceBanner(); }
})();
```

Add enqueue in `shahi-legalops-suite.php`:

```php
add_action( 'wp_enqueue_scripts', function(){
    if ( ! is_admin() ) {
        wp_enqueue_script( 'slos-geo-helper', plugin_dir_url(__FILE__) . 'assets/js/geolocation-helper.js', [], '3.0.1', true );
    }
});
```

### 5) Admin UI: Settings Section (Provider selection)

Location: `includes/Admin/GeolocationSettings.php`

```php
<?php
namespace Shahi\LegalOps\Admin;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class GeolocationSettings {
    public static function init(){
        add_action( 'admin_init', [ __CLASS__, 'register' ] );
        add_action( 'admin_menu', [ __CLASS__, 'menu' ] );
    }

    public static function register(){
        register_setting( 'slos_geo', 'slos_geolocation_enabled', ['type'=>'boolean', 'default'=>false] );
        register_setting( 'slos_geo', 'slos_geolocation_provider', ['type'=>'string', 'default'=>'ipapi'] );
        register_setting( 'slos_geo', 'slos_maxmind_license', ['type'=>'string', 'default'=>''] );
        register_setting( 'slos_geo', 'slos_ip2location_api_key', ['type'=>'string', 'default'=>''] );
        register_setting( 'slos_geo', 'slos_banner_template_override', ['type'=>'string', 'default'=>''] );
    }

    public static function menu(){
        add_submenu_page(
            'slos',
            'Geolocation',
            'Geolocation',
            'manage_options',
            'slos-geolocation',
            [ __CLASS__, 'render' ]
        );
    }

    public static function render(){
        echo '<div class="wrap"><h1>Geolocation Settings</h1><form method="post" action="options.php">';
        settings_fields( 'slos_geo' );
        echo '<table class="form-table">';

        // Enable
        echo '<tr><th scope="row">Enable Geolocation</th><td>';
        echo '<input type="checkbox" name="slos_geolocation_enabled" value="1" ' . checked( 1, get_option('slos_geolocation_enabled', 0), false ) . ' />';
        echo '<p class="description">Enable IP-based country detection and automatic banner selection.</p>';
        echo '</td></tr>';

        // Provider
        $provider = esc_attr( get_option( 'slos_geolocation_provider', 'ipapi' ) );
        echo '<tr><th scope="row">Provider</th><td>';
        echo '<select name="slos_geolocation_provider">';
        foreach( ['ipapi'=>'ipapi','maxmind'=>'maxmind','ip2location'=>'ip2location'] as $k=>$v ){
            echo '<option value="' . esc_attr($k) . '"' . selected( $provider, $k, false ) . '>' . esc_html( strtoupper($v) ) . '</option>';
        }
        echo '</select>';
        echo '</td></tr>';

        // MaxMind
        echo '<tr><th scope="row">MaxMind License</th><td>';
        echo '<input type="text" class="regular-text" name="slos_maxmind_license" value="' . esc_attr( get_option('slos_maxmind_license','') ) . '" />';
        echo '</td></tr>';

        // IP2Location
        echo '<tr><th scope="row">IP2Location API Key</th><td>';
        echo '<input type="text" class="regular-text" name="slos_ip2location_api_key" value="' . esc_attr( get_option('slos_ip2location_api_key','') ) . '" />';
        echo '</td></tr>';

        // Override
        echo '<tr><th scope="row">Banner Template Override</th><td>';
        echo '<input type="text" class="regular-text" name="slos_banner_template_override" value="' . esc_attr( get_option('slos_banner_template_override','') ) . '" placeholder="eu|ccpa|advanced|simple" />';
        echo '<p class="description">Force a specific template regardless of geolocation.</p>';
        echo '</td></tr>';

        echo '</table>';
        submit_button();
        echo '</form></div>';
    }
}

\Shahi\LegalOps\Admin\GeolocationSettings::init();
```

---

## OUTPUT STATE
- ✅ Provider abstraction implemented (ipapi, MaxMind, IP2Location)
- ✅ Regulation detection (GDPR, CCPA, LGPD, default)
- ✅ Auto banner selection via filter `slos/banner_template`
- ✅ Admin settings for provider keys and enable toggle
- ✅ Frontend JS helper with localStorage caching
- ✅ Transient caching (12h) and graceful fallback

---

## VERIFICATION

```bash
# 1) Enable geolocation and set provider
wp option update slos_geolocation_enabled 1
wp option update slos_geolocation_provider ipapi

# 2) Simulate detection by calling a simple diagnostic endpoint (if added)
# Alternatively, inspect localized script on frontend
# In the browser console:
# > window.slosGeoContext

# 3) Check banner auto-selection (requires banner script reading the filter)
wp option get slos_banner_template_override

# 4) Check transients created (requires direct DB query)
wp db query "SELECT option_name FROM wp_options WHERE option_name LIKE '%%slos_geo_%%' LIMIT 5"

# 5) Curl provider
curl -sS https://ipapi.co/8.8.8.8/json/ | jq '.country_code'
```

---

## SUCCESS CRITERIA
- Geolocation enabled toggles influence of `window.slosGeoContext` on frontend.
- For EU/EEA+UK IPs, regulation resolves to `gdpr` and banner to `eu`.
- For US-CA IPs, regulation resolves to `ccpa` and banner to `ccpa`.
- For BR IPs, regulation resolves to `lgpd` and banner to `advanced`.
- Transient caching reduces repeat lookups; localStorage TTL respected.
- Admin page saves provider keys and toggles.

---

## ROLLBACK

```bash
wp option delete slos_geolocation_enabled
wp option delete slos_geolocation_provider
wp option delete slos_maxmind_license
wp option delete slos_ip2location_api_key
wp option delete slos_banner_template_override

# Optionally remove transient caches
wp eval "global $wpdb; $wpdb->query( \"DELETE FROM {$wpdb->options} WHERE option_name LIKE '%%_transient_slos_geo_%%'\" );"
```

---

## TROUBLESHOOTING
- **Provider timeout:** Increase timeout in `BaseProvider::$timeout` to 3.0s; check server outbound requests.
- **Incorrect regulation:** Verify country codes are uppercase; inspect `RegulationDetector::detect()` mapping.
- **Banner not switching:** Ensure your banner script applies `apply_filters('slos/banner_template', $template)` before rendering.
- **Local dev behind proxy:** `REMOTE_ADDR` may be proxy address; add logic to read `HTTP_X_FORWARDED_FOR` when trusted.
- **Admin settings not saving:** Confirm `register_setting('slos_geo', ...)` matches `settings_fields('slos_geo')` in the form.

---

## COMMIT MESSAGE
```
feat(geo): Add IP-based geolocation with provider abstraction

- Providers: ipapi, MaxMind, IP2Location
- Regulation detector (GDPR/CCPA/LGPD)
- Auto banner selection via filter
- Admin settings for provider keys
- Frontend helper with localStorage cache
- Transient cache (12h) and graceful fallback

Task: 2.7 (6-8 hrs)
Next: 2.8 Admin Dashboard
```

---

## WHAT TO REPORT BACK
"✅ TASK 2.7 COMPLETE

Implemented:
- Geolocation service + providers
- Regulation detection + banner selection
- Admin settings and frontend helper
- Caching (transient + localStorage)

Verified with curl/wp-cli and UI checks.

📍 Ready for TASK 2.8: Admin Dashboard"

---

## ✅ COMPLETION CHECKLIST
- [ ] GeolocationService + providers added
- [ ] RegulationDetector mapped
- [ ] Filter for banner selection
- [ ] Admin settings page
- [ ] Frontend helper enqueued
- [ ] Verified with wp-cli/curl
- [ ] Committed changes
- [ ] Proceed to 2.8
