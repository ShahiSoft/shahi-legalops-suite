# TASK 6.11: Load & Reliability Testing

Phase: 6 | Effort: 6-8h | Next: 6.12

Stress test banner API, scanner, analytics.
- k6/JMeter scripts; targets: banner endpoints 500 rps with <200ms P95; consent write + analytics ingest 200 rps with <300ms P95; real-time feed remains <1s P95; queue backpressure noted.
- Scenarios per regulation/template and consent state; include webhook retry bursts; run with/without caching.
- Document bottlenecks and fixes; include resource charts.

Success: targets met; report lists bottlenecks and remediation; no data loss under load; retries do not overload system.
