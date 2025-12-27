# TASK 7.8: Launch & Monitoring

Phase: 7 | Effort: 4-6h | Next: —

Post-launch monitoring setup.
- Enable error logging, uptime checks, SLA monitors for consent/DSR/api/webhooks; alerts for 5xx, webhook retries DLQ growth, queue lag, and rate-limit spikes.
- Dashboards for key KPIs: consent rate, withdrawal rate, DSR SLA breaches, webhook success, backup status; segment by regulation/template.
- Add feedback link and support triage SOP (sev levels, ETA, on-call). Ensure PII redaction in logs; rotate secrets.

Success: Monitoring live with alerts; KPIs visible; SOP documented and tested.

Execution workflow (copy/paste):
- Configure alerts/monitors and run synthetic checks; trigger test alerts (5xx, webhook DLQ, SLA breach).
- Verify dashboards show KPIs by regulation/template; ensure PII redaction in logs.
- Review/approve SOP; confirm on-call/triage contacts.
