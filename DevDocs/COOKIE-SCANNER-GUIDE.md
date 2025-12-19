# Cookie Scanner Component - Implementation Guide

## Overview
The Cookie Scanner identifies cookies and storage keys set in the browser, classifies them into consent categories, and submits a report to the backend. It integrates with the SLOS REST API and stores the last scan inventory for admin review.

## Files
- `includes/Services/Cookie_Scanner_Service.php` — Classification + inventory persistence
- `includes/API/Cookie_REST_Controller.php` — REST endpoints (`/cookies/*`)
- `assets/js/cookie-scanner.js` — Frontend scanner and report sender
- `shahi-legalops-suite.php` — Enqueue scanner script

## REST Endpoints (namespace: `slos/v1`)
- `GET /cookies/categories` — List of consent categories
- `GET /cookies/patterns` — Default cookie patterns (admin-only)
- `POST /cookies/report` — Submit scan payload `{ cookies, localStorageKeys, sessionStorageKeys, url, userAgent }`
- `GET /cookies/inventory` — Retrieve last stored inventory (admin-only)
- `DELETE /cookies/inventory` — Clear stored inventory (admin-only)

## Classification Categories
- `necessary` (required)
- `functional`
- `analytics`
- `marketing`
- `preferences`
- `personalization`

## Default Patterns (examples)
- Necessary: `PHPSESSID`, `wordpress_*`, `wp-settings-*`, `woocommerce_*`
- Analytics: `_ga`, `_gid`, `_gat*`, `_hj*`, `ajs_*`
- Marketing: `_fbp`, `fr`, `IDE`, `gcl_au`, `_gcl_*`
- Functional/Preferences: `intercom*`, `hs*`, `sf*`, `pll_language`

## Client Scanner (`assets/js/cookie-scanner.js`)
- Collects cookies via `document.cookie`
- Enumerates `localStorage` and `sessionStorage` keys
- Submits report to `/wp-json/slos/v1/cookies/report`
- Auto-runs on `DOMContentLoaded`
- Re-runs on `slos-consent-updated` events
- Emits `slos-cookie-scan-complete` event with API response

## Inventory Persistence
- Stored in WordPress option `slos_cookie_inventory` (JSON)
- Contains summary, cookie list, storage keys, categorical breakdown, environment metadata

## Usage Examples
### Trigger a scan manually
```js
window.slosCookieScanner.runScan().then((result) => {
  console.log('Scan result:', result);
});
```

### Listen for scan completion
```js
document.addEventListener('slos-cookie-scan-complete', (e) => {
  console.log('Inventory updated:', e.detail);
});
```

### Admin: Review last inventory
```bash
curl -H "Cookie: wordpress_logged_in=..." \
  https://example.com/wp-json/slos/v1/cookies/inventory
```

## Security & Privacy
- IP addresses are hashed (`sha256`) before persistence
- User agent and URL included for context
- No sensitive values are required; cookie values are sanitized and included for reference only

## Notes
- Server cannot scan client cookies; scanning is performed client-side and posted to the server
- Classification is based on patterns + simple heuristics; customize as needed

## Customization
You can extend `get_default_patterns()` in `Cookie_Scanner_Service` to include additional vendors and rules.

## Next Steps
- Integrate scan results into admin analytics dashboard
- Provide UI to review and flag cookies by consent category
- Add removal suggestions or automated blocking for non-consented categories
