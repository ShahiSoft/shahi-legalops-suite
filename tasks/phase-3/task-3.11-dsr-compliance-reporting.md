# TASK 3.11: DSR Compliance Reporting

**Phase:** 3 (DSR Portal)  
**Effort:** 4-6 hours  
**Prerequisites:** Task 3.10 notifications  
**Next Task:** [task-3.12-dsr-audit-logs.md](task-3.12-dsr-audit-logs.md)

---

Generate compliance reports for DSR handling.

STEPS
1) Report generator summarizing period metrics: total requests, by type, by status, average response time, SLA breaches, open vs closed.
2) Export formats: CSV, PDF summary.
3) Admin UI with date range filters; cron to email monthly summary to admins.
4) Include evidence links and anonymized examples.

VERIFICATION
- Run report for last 30 days; values match DB counts.

SUCCESS CRITERIA
- Accurate metrics, exportable
- Scheduled summary email works

COMMIT MESSAGE
```
feat(dsr): add compliance reporting
```
