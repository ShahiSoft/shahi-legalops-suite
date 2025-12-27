# TASK 5.10: Analytics Tests

Phase: 5 | Effort: 6-8h | Next: Phase 6

Add tests for analytics pipeline and UI.
- PHPUnit for aggregations/views segmented by regulation and consent state; verify template variant dimensions and compliance score.
- JS/E2E for dashboard rendering and filters (regulation, consent state, template variant, device/geo); test exports (CSV/PDF) and real-time feed rate limiting; include locale/RTL checks per [DevDocs/localization-rollout-plan.md](../../DevDocs/localization-rollout-plan.md).

Success: tests passing; coverage on metrics calculations and filters across regulation/consent variants.
