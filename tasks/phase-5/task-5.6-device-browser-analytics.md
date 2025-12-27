# TASK 5.6: Device/Browser Analytics

Phase: 5 | Effort: 4-6h | Next: 5.7

Aggregate events by device type, OS, browser.
- UA parser; store normalized fields and consent-state (allowed/denied/TCF string) + regulation (GDPR/CCPA/LGPD/UK-GDPR/PIPEDA/POPIA) per event.
- Dimensions: device, OS, browser, version, template variant, consent state, regulation.
- Charts: pie/bar for device/browser share; filters by regulation and consent state; export CSV/PDF (include consented vs non-consented segments).

Success: parsed UA fields stored; charts show counts; filters/regulation segments work.
