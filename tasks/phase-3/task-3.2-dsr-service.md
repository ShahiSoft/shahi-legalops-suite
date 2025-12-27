# TASK 3.2: DSR Service

**Phase:** 3 (DSR Portal)  
**Effort:** 8-10 hours  
**Prerequisites:** Task 3.1 repository (aligned to /v3docs/database/SCHEMA-ACTUAL.md tables `wp_complyflow_dsr_requests` and `wp_complyflow_dsr_request_data_sources`)  
**Next Task:** [task-3.3-dsr-rest-api.md](task-3.3-dsr-rest-api.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

Implement business logic for DSR handling. Align with /v3docs/modules/02-DSR-IMPLEMENTATION.md: support 7 rights (access, rectification, erasure, portability, restriction, object, automated_decision), enforce SLA (default 30–45 days), and secure exports with tokenized downloads.

INPUT STATE
- DSR_Repository ready
- SLA option (30–45 days default) stored with module settings

STEPS
1) Create `includes/Services/DSR_Service.php` with methods:
   - submit_request($user_id,$type,$email,$details,$meta=[]) — enforce allowed types: access, rectification, erasure, portability, restriction, object, automated_decision; rate-limit per IP/email; hash IP for privacy
   - verify_email($token) — set status `verified`, log action
   - assign_request($id,$assignee)
   - add_note($id,$note,$author)
   - transition($id,$status) with validation of allowed states (pending_verification→verified→in_progress→completed/rejected; allow on_hold) and SLA deadline checks
   - generate_export_package($id) — delegate to export service; store tokenized, time-limited download URL
   - execute_erasure($id) — mark pending erasure, run anonymization callbacks
   - calculate_due_date($created_at,$sla_days)
   - get_timeline($id) — aggregate audit log entries (submit, verify, assign, status change, export, erasure)
2) Fire hooks: `slos_dsr_submitted`, `slos_dsr_status_changed`, `slos_dsr_completed`, `slos_dsr_export_ready`.
3) Validation: allowed types above; max detail length; rate limit per IP/email; enforce presence of verification token; ensure download links expire and require token.

VERIFICATION
- `wp eval "(new Shahi\LegalOps\Services\DSR_Service())->submit_request(0,'access','user@example.com','test');"`
- Check row inserted in `wp_complyflow_dsr_requests` and status `pending_verification` with SLA deadline set.

SUCCESS CRITERIA
- State machine enforces all seven rights
- SLA deadlines stored; status changes logged; tokenized download URLs generated
- Hooks fire; rate-limits enforced

SUCCESS CRITERIA
- State machine enforced
- Hooks fired
- Due date calculated per SLA

ROLLBACK
- Remove DSR_Service file

COMMIT MESSAGE
```
feat(dsr): add DSR service layer
```
