# TASK 3.4: DSR Request Form (Shortcode)

**Phase:** 3 (DSR Portal)  
**Effort:** 6-8 hours  
**Prerequisites:** Task 3.3 REST API  
**Next Task:** [task-3.5-dsr-email-verification.md](task-3.5-dsr-email-verification.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

Provide a public form for DSR submission with email verification hook. Cover all 7 rights, collect consent to verify identity, and show SLA/processing notice.

STEPS
1) Create shortcode `[slos_dsr_form]` in `includes/Shortcodes/DSR_Form_Shortcode.php` rendering form fields: name, email, request type select (access, rectification, erasure, portability, restriction, object, automated_decision), details textarea, checkbox for identity attestation + privacy notice link.
2) Enqueue JS `assets/js/dsr-form.js` to POST to /dsr/submit, show success message, handle validation, throttle repeated submissions, and display SLA notice (default 30–45 days) pulled from settings/REST.
3) Add basic CSS `assets/css/dsr-form.css` responsive with accessibility (labels, aria, focus states).
4) On submit: display message "Check your email to verify your request" and hide PII; log client IP hashed server-side.
5) Expose optional upload field for identity docs only if enabled in settings; ensure nonces and size/type validation.

VERIFICATION
- `wp eval "echo do_shortcode('[slos_dsr_form]');"`
- Submit form; check request stored with status pending_verification in `wp_complyflow_dsr_requests`, SLA deadline set, IP hash stored (not raw).

SUCCESS CRITERIA
- Form renders on frontend
- Submission hits REST API
- Handles errors inline

ROLLBACK
- Remove shortcode + assets

COMMIT MESSAGE
```
feat(dsr): add public DSR form shortcode
```
