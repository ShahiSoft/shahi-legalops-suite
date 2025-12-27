# TASK 3.12: DSR Audit Logs

**Phase:** 3 (DSR Portal)  
**Effort:** 4-6 hours  
**Prerequisites:** Task 3.11 reporting  
**Next Task:** [task-3.13-dsr-integration-tests.md](task-3.13-dsr-integration-tests.md)

---

Add audit logging specific to DSR actions, aligned to `wp_complyflow_dsr_logs` and the full DSR lifecycle.

STEPS
1) Table `wp_complyflow_dsr_logs`: id, request_id, action, actor_id, note, ip_hash, user_agent_hash, created_at (hash IP/UA for privacy).
2) Log actions: submit, verify, assign, status change, note added, export generated, download served, erasure executed.
3) REST endpoint GET /dsr/logs (admin) with filters (request_id, action, date range, actor).
4) UI tab in DSR admin detail showing timeline + SLA countdown + tokens/download events.
5) Include hook `slos_dsr_audit_logged` after insert for integrations.

VERIFICATION
- Perform status change; log row created with hashed IP/UA.
- Fetch via REST shows timeline and includes download/export events.

SUCCESS CRITERIA
- Complete audit trail for DSR lifecycle
- Admin can review timeline

COMMIT MESSAGE
```
feat(dsr): add DSR audit logging
```
