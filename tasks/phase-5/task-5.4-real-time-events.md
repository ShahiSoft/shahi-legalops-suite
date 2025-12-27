# TASK 5.4: Real-time Event Stream

Phase: 5 | Effort: 6-8h | Next: 5.5

Provide live view of incoming events.
- AJAX/REST polling or websockets fallback to polling with rate limits/backoff.
- Show last 50 events with timestamp, country, event type, consented flag (GCMv2 state), regulation, template variant.
- Toggle auto-refresh; throttle to protect DB; cache layer for last N events.

Success: live feed updates without page reload; no DB overload; consent state visible.
