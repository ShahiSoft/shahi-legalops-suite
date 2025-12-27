# TASK 3.10: DSR Notifications (Email + Hooks)

**Phase:** 3 (DSR Portal)  
**Effort:** 4-6 hours  
**Prerequisites:** Task 3.9 status portal  
**Next Task:** [task-3.11-dsr-compliance-reporting.md](task-3.11-dsr-compliance-reporting.md)

---

Add email notifications and hooks for DSR lifecycle.

STEPS
1) Emails to requester: submission, verification needed, verified/started, completed, rejected, export ready.
2) Emails to admin/staff: new request, overdue warning, erasure actions required.
3) Templates filterable; include tokens/links.
4) Throttle emails to avoid spam.
5) Fire hooks: `slos_dsr_email_sent` with context.

VERIFICATION
- Trigger status changes; confirm emails logged (or intercepted).

SUCCESS CRITERIA
- Notifications sent on key events
- Templates customizable

COMMIT MESSAGE
```
feat(dsr): add notifications for DSR lifecycle
```
