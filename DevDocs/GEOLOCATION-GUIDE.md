# Geolocation Detection (Task 2.7)

This guide explains the geolocation detection service, REST endpoint, and frontend integration used to select an appropriate consent banner template per visitor region.

## Overview
- Service: `includes/Services/Geo_Service.php`
- REST: `includes/API/Geo_REST_Controller.php` → `/wp-json/slos/v1/geo/region`
- Frontend: `assets/js/consent-banner.js` resolves region and maps to template
- Config: `slosConsentConfig` includes `region`, `routes.geo`, and default `template`

## Region Mapping
The service maps country/state codes to regulation buckets:
- `EU` → GDPR/EEA/UK
- `US-CA` → California CCPA/CPRA
- `US` → US (other states)
- `BR` → Brazil LGPD
- `ZA`, `AU`, `IN` → POPIA, Australia Privacy Act, India DPDP (currently use `simple` unless overridden)
- Default: `GLOBAL`

`Geo_Service::map_region_to_template()` maps:
- `EU` → `eu`
- `US-CA` → `ccpa`
- `BR` → `advanced`
- others → `simple`

Override via `add_filter('slos_consent_template', fn($tpl) => 'eu')`.

## Providers
The service uses a filterable provider:
- Default: `ipapi` (`https://ipapi.co/json/`)
- Supported: `ipinfo` (`https://ipinfo.io/json`), `ip-api` (`http://ip-api.com/json`)
- Filters:
  - `slos_geo_provider` → `'ipapi' | 'ipinfo' | 'ip-api'`
  - `slos_geo_provider_url` → customize endpoint

Results cached for 24h per-IP via `set_transient()`.

## Frontend Flow
`consent-banner.js`:
1. Reads `slosConsentConfig.region` and `template`.
2. If region missing, calls `routes.geo` to resolve.
3. If template missing, maps region via `mapRegionToTemplate()`.
4. Shows the appropriate banner (`eu`, `ccpa`, `simple`, `advanced`).

## REST Endpoint
- `GET /wp-json/slos/v1/geo/region` (public)
- Response:
```
{
  "success": true,
  "data": {
    "region": "EU",
    "country": "Germany",
    "country_code": "DE",
    "state": "BY",
    "source": "provider:ipapi",
    "template": "eu"
  }
}
```

## Server Integration
`shahi-legalops-suite.php` localizes `slosConsentConfig` with:
- `region` (detected)
- `template` (suggested; still filterable)
- `routes.geo` for frontend fallback.

## Extensibility
- `slos_geo_us_region` filter: Map US states to specific buckets (e.g., `US-VA`).
- `slos_consent_template` filter: Force a template regardless of region.

## Notes
- Provider calls time out after 3s and gracefully fall back to Accept-Language heuristics.
- Errors are logged via `Base_Service::add_error()`; public endpoint remains safe and returns `GLOBAL` when uncertain.
