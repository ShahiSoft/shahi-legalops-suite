# TASK 6.9: Webhooks & Integrations

Phase: 6 | Effort: 6-8h | Next: 6.10

Webhook system for events: consent granted/withdrawn, DSR submitted/completed, doc accepted.
- Payload includes regulation, consent state, template variant, locale; signed (HMAC) with secret; idempotency key; replay protection.
- Retry policy with exponential backoff and DLQ; rate limits; per-endpoint enable/disable; test delivery button.
- Prebuilt connectors: Slack/Teams/Zapier; docs for custom endpoints; delivery logs with status and response snippet.

Success: webhook deliveries logged with signatures; retries on 5xx; payload contains regulation/consent fields; test delivery succeeds.
