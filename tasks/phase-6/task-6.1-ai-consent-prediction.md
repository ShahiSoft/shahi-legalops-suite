# TASK 6.1: AI Consent Prediction

Phase: 6 (Advanced) | Effort: 8-10h | Next: 6.2

Add optional AI model to suggest banner variant or purpose defaults.
- Collect anonymized signals only (country/region, regulation, device, referrer, time, template variant, prior consent state hashed/pepper). No PII; no raw IP/UA stored.
- Simple model/rules: choose template likely to yield higher consent within policy; never auto-apply without admin opt-in; record explanation for suggestion.
- A/B compare vs control; holdout group; metrics segmented by regulation (GDPR/CCPA/LGPD/UK-GDPR/PIPEDA/POPIA) and consent state.
- Opt-out setting per site; disable training/logging when off; purge cached features on disable.

Success: suggestion logged with features/explanation; never auto-applies without explicit admin enable; lift report shows segmented metrics vs control.
