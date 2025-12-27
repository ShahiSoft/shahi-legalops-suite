# TASK 5.2: Metrics Definitions

Phase: 5 | Effort: 4-6h | Next: 5.3

Define computed metrics and SQL views (consent-aware, regulation-aware).
- Metrics: consent rate (by regulation/template/device/locale), opt-out rate, withdrawal rate, time-to-accept, banner CTR, DSR volume and SLA compliance %, doc acceptance rate, alert counts, performance budget hit rate.
- Create DB views/materialized cache for daily/weekly aggregates; include consented vs non-consented event segments (GCMv2 state) and cohort windows.
- Define compliance score composite (consent, DSR SLA, docs published, accessibility scans).

Success: view queries return correct aggregates; formulas documented; segments available for dashboards and exports.
