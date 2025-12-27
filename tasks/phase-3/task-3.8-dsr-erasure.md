# TASK 3.8: DSR Data Erasure / Anonymization

**Phase:** 3 (DSR Portal)  
**Effort:** 8-10 hours  
**Prerequisites:** Task 3.7 export  
**Next Task:** [task-3.9-dsr-status-portal.md](task-3.9-dsr-status-portal.md)

---

Implement secure erasure/anonymization pipeline for approved requests.

STEPS
1) Add state `pending_erasure` -> `erased` or `failed`.
2) Erasure handlers (pluggable) for:
   - WP core user data (delete or anonymize)
   - WooCommerce orders (anonymize personal fields)
   - Consent records (set user_id null but keep audit)
   - Custom providers via filter `slos_dsr_erasure_handlers`.
3) Dry-run mode to show what will be deleted.
4) Log all actions to audit trail with timestamp and operator.

VERIFICATION
- Approve erasure for sample user; check user fields anonymized.
- Dry-run shows items without modifying data.

SUCCESS CRITERIA
- Idempotent erasure; no orphan data
- Audit log records actions

COMMIT MESSAGE
```
feat(dsr): implement erasure/anonymization pipeline
```
