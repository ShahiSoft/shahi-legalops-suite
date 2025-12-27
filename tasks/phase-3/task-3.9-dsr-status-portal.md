# TASK 3.9: DSR Status Portal

**Phase:** 3 (DSR Portal)  
**Effort:** 6-8 hours  
**Prerequisites:** Task 3.8 erasure  
**Next Task:** [task-3.10-dsr-notifications.md](task-3.10-dsr-notifications.md)

---

Public portal for requesters to check status securely.

STEPS
1) Shortcode `[slos_dsr_status]` renders token/email form and status view.
2) REST endpoint GET /dsr/status?token=... returns status, due_date, last update, notes (public-safe subset).
3) Ensure no sensitive data leaked (only status timeline and next steps).
4) Optional captcha/nonce to reduce abuse.

VERIFICATION
- Submit request, verify, then load status page with token; status matches DB.

SUCCESS CRITERIA
- Requester can self-check without admin intervention
- No PII beyond necessary contact email

COMMIT MESSAGE
```
feat(dsr): add status portal
```
