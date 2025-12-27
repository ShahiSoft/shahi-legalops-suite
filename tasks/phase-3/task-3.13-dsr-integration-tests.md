# TASK 3.13: DSR Integration Tests

**Phase:** 3 (DSR Portal)  
**Effort:** 8-10 hours  
**Prerequisites:** All Phase 3 features  
**Next Task:** Phase 4 begins

---

Comprehensive tests for DSR module.

STEPS
1) PHPUnit integration tests in `tests/integration/dsr/` covering:
   - Submit + verify flow
   - Admin status transitions and SLA due date
   - Export package generation content
   - Erasure dry-run vs execute
   - Status portal token access
   - Notifications triggered on state change
   - Audit logs created for each action
2) Optional E2E (Playwright/Cypress): submit form, verify via mocked link, admin completes request, user checks status portal.
3) CLI helper `bin/test-dsr.sh` running dsr testsuite.

VERIFICATION
- `vendor/bin/phpunit --testsuite dsr`
- All tests pass.

SUCCESS CRITERIA
- High coverage of DSR flows
- No regressions in lifecycle

COMMIT MESSAGE
```
test(dsr): add DSR integration tests
```
