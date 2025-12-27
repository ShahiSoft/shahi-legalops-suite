# TASK 2.15: Integration Tests (Consent Module)

**Phase:** 2 (Consent Management - CORE)  
**Effort:** 8-10 hours  
**Prerequisites:** TASK 2.14 complete (Preferences UI)  
**Next Task:** Phase 3 begins (DSR Portal)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 2.15 for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
Create comprehensive integration tests covering the entire consent module: banner, scanner,
script blocker, geolocation, settings, analytics, export/import, audit logs, and preferences UI.
Use PHPUnit + WP test suite; add E2E flows with Playwright or Cypress if available.

INPUT STATE (verify these exist):
✅ All consent module features (Tasks 2.1-2.14)
✅ WP test bootstrap from Phase 1

YOUR TASK:

1) **PHPUnit integration tests**

Location: `tests/integration/consent/` (create if needed)

Create tests:
- `BannerTest.php`: ensures enqueue + localized data, banner renders HTML fragment
- `ConsentApiTest.php`: grant/withdraw/check endpoints return expected JSON
- `CookieScannerTest.php`: scanner populates definitions table
- `ScriptBlockerTest.php`: scripts enqueued are blocked (type=text/plain) without consent
- `GeolocationTest.php`: provider called; banner selection uses location
- `SettingsTest.php`: options save & persist, color values stored
- `ExportImportTest.php`: export returns CSV/JSON; import inserts rows
- `AuditLogTest.php`: actions create log rows; filters work
- `PreferencesUiTest.php`: shortcode renders container; localized strings present

2) **Sample test skeleton (one file)**

```php
<?php
use WP_UnitTestCase;
use Shahi\LegalOps\Services\Consent_Service;

class ConsentApiTest extends WP_UnitTestCase {
    public function setUp(): void {
        parent::setUp();
        $this->service = new Consent_Service();
    }

    public function test_grant_consent() {
        $result = $this->service->grant_consent( 0, 'analytics', 'text', 'explicit' );
        $this->assertTrue( $result );
    }

    public function test_rest_grant_endpoint() {
        $request = new WP_REST_Request( 'POST', '/slos/v1/consents/grant' );
        $request->set_body_params([
            'user_id' => 0,
            'purpose' => 'analytics'
        ]);
        $response = rest_get_server()->dispatch( $request );
        $this->assertEquals( 200, $response->get_status() );
    }
}
```

3) **E2E (optional but recommended)**
- Playwright/Cypress flow:
  - Visit homepage, see banner, accept selected
  - Reload, confirm scripts unblocked
  - Open preferences UI, change toggles, download data

4) **CLI helper**
- Add `bin/test-consent.sh` to run subset: `vendor/bin/phpunit --testsuite consent`

5) **Verification commands**

```bash
# Run full suite
vendor/bin/phpunit --testsuite consent

# Run specific
vendor/bin/phpunit --filter ConsentApiTest

# Check DB tables after tests
wp db query "SHOW TABLES LIKE 'wp_slos_consent%';"
```

OUTPUT STATE:
✅ Integration test suite for consent module
✅ Covers API, UI, scanner, blocker, geo, settings, export/import, logs
✅ E2E script (if enabled)

SUCCESS CRITERIA:
✅ Tests pass locally
✅ Critical flows covered
✅ No regressions for consent features

ROLLBACK:
```bash
rm -rf tests/integration/consent
rm bin/test-consent.sh
```

TROUBLESHOOTING:
- REST dispatch failing: ensure routes loaded in test bootstrap
- Tables missing: run migrations in test setUp
- JS E2E flaky: add waits for banner visibility

COMMIT MESSAGE:
```
test(consent): Add integration tests for consent module

- PHPUnit integration tests (API, banner, scanner, blocker, geo, settings, export/import, logs)
- Optional E2E script
- CLI helper

Task: 2.15 (8-10 hours)
Next: Phase 3 - DSR Portal
```

WHAT TO REPORT BACK:
"✅ TASK 2.15 COMPLETE
- Integration tests added (consent module)
- Coverage: API, banner, scanner, blocker, geo, settings, export/import, logs, preferences
- PHPUnit suite runnable
"
```

---

## ✅ COMPLETION CHECKLIST

- [ ] Integration test files added
- [ ] CLI helper added
- [ ] Tests passing
- [ ] Committed to git
- [ ] Phase 2 complete

---

**Status:** ✅ Ready to execute  
**Time:** 8-10 hours  
**Next:** Phase 3 (DSR Portal)
