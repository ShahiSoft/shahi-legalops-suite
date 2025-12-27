# TASK 6.13: Forms Compliance (Optional)

Phase: 6 (Advanced Optional) | Effort: 6-8h | Next: 6.14

Detect and enforce consent alignment on site forms.
- Auto-detect forms (native WP, Gutenberg blocks, popular builders: CF7, Gravity, WPForms, Ninja, Elementor forms); hook submit events.
- Map fields to purposes/categories; enforce consent checkbox where regulation requires (GDPR/UK-GDPR/LGPD/CCPA opt-out); block submit or tag as "no-consent" when missing.
- Banner-aware: reuse latest consent state; attach consent record ID to submission metadata; hashed user/device IDs only.
- Admin UI: form catalog with detected purposes, last scan, compliance status; per-form toggle for enforcement; logs of blocked/submissions.
- Exports: CSV/PDF of forms and compliance status; webhook trigger on non-compliant submission attempts.

Success: forms detected and cataloged; required consent checks applied per regulation; submissions carry consent linkage or are blocked; exports and logs available.
