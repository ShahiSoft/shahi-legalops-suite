# TASK 7.1: Full Regression QA

Phase: 7 | Effort: 8-10h | Next: 7.2

Run end-to-end regression across all modules.
- Scope: consent (banner/preferences/script blocker/geo), DSR (7 rights flows, SLA timers, exports/download tokens), legal docs (templates, versioning, acceptance), analytics (dashboards, filters, exports, real-time), vendor, webhooks, backup/restore, optional forms/cookie inventory, AI toggle off/on.
- Cross-browser/mobile: latest Chrome/Firefox/Safari/Edge; mobile Safari/Chrome; RTL locale sample (ar/he) and LTR (en/fr/ja) per [DevDocs/localization-rollout-plan.md](../../DevDocs/localization-rollout-plan.md).
- Data integrity: hashed IDs only, no PII leaks; verify consent/DSR/log/audit entries match actions; rollbacks (migrations/backup restore) clean.
- Regression matrix: platforms (desktop/mobile), locales, regulations (GDPR/CCPA/LGPD/UK-GDPR/PIPEDA/POPIA), template variants.
- Deliverable: QA report with pass/fail, defects filed with repro, screenshots, and owner; go/no-go signoff.

Success: regression suite passes across target browsers/locales/regulations; defects triaged; no P0/P1 open before release.

Execution workflow (copy/paste):
- Run automated suites (PHPUnit/JS/E2E) then manual matrix by regulation/locale/device.
- Log defects with repro, env, screenshots; rerun smoke after fixes.
- Attach QA report and go/no-go decision.
