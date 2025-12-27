# TASK 5.1: Analytics Data Pipeline

Phase: 5 (Analytics) | Effort: 6-8h | Next: 5.2

Build ingestion pipeline for consent/events metrics (consent-aware, consent-mode ready).
- Collect events: banner views, accept/reject, preference changes, DSR submissions, doc acceptance, alerts, SLA breaches. Capture regulation, template variant, device, and locale.
- Store in `wp_shahi_analytics` (existing) or dedicated `wp_complyflow_events` table with indexes (event_type, ts, user_id/session_hash, country, regulation, template_variant).
- Sanitize PII; store hashed user_id/session if anonymous; drop raw IP/UA (hash only).
- Emit derived Google Consent Mode v2 state for analytics events; mark events as consented vs non-consented for filtering.

Success: events recorded, indexed, minimal latency; consent/state flags stored for downstream metrics.
