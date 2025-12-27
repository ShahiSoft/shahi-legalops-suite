# TASK 7.3: Performance Tuning

Phase: 7 | Effort: 6-8h | Next: 7.4

Optimize final build.
- Profile DB hotspots; add missing indexes; cache heavy queries; pre-aggregate analytics; partition by date/regulation where needed.
- Frontend: banner JS <50KB gzipped; no-blocking scripts; lazy-load locales; consent API p95 <200ms; real-time feed p95 <1s; CLS <0.1.
- Admin: dashboards load <1s TTI on sample data (1M rows); exports complete within SLA; charts stay under perf budget with filters.
- Assets: minify/treeshake; HTTP/2 hints; image/WebP optimization; RTL bundles split; verify CSP doesn’t block assets.

Success: Benchmarks meet targets; perf report recorded with before/after and action items.

Execution workflow (copy/paste):
- Profile -> optimize -> re-measure loop; capture before/after metrics (p95, TTI, bundle size).
- Verify caching/partitioning and RTL/locale bundle loads; rerun with/without cache.
- Publish perf report with actions taken and remaining risks.
