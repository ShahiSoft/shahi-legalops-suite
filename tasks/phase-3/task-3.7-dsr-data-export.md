# TASK 3.7: DSR Data Export Generator

**Phase:** 3 (DSR Portal)  
**Effort:** 8-10 hours  
**Prerequisites:** Task 3.6 workflow  
**Next Task:** [task-3.8-dsr-erasure.md](task-3.8-dsr-erasure.md)

---

Generate GDPR-compliant export packages for DSR requests (access/portability) with secure, tokenized downloads and multiple formats.

STEPS
1) Aggregators to collect user data from:
   - WordPress core (profile, comments)
   - WooCommerce (if present): orders, addresses
   - Consent records (module)
   - Form plugins (CF7, WPForms, Gravity, Ninja, Forminator) if detected
   - Any registered data providers via filter `slos_dsr_data_providers`.
2) Build export package with formats: JSON, CSV, XML, PDF summary; bundle as ZIP. Stream large exports; avoid memory blow-up.
3) Store generated package path, hash, size; generate single-use tokenized download URL that expires; email link to requester.
4) Ensure redaction of third-party secrets; include metadata (generated_at, processor contact, SLA deadline, regulation basis).
5) Log audit entry on generation and download; restrict download to requester token.

VERIFICATION
- Trigger export: `wp eval "(new Shahi\LegalOps\Services\DSR_Service())->generate_export_package(1);"`
- Check file exists, contains JSON/CSV/XML/PDF, tokenized download URL stored, audit log entry created.

SUCCESS CRITERIA
- Exports generated without PII leakage beyond requester
- Providers extensible via filter

COMMIT MESSAGE
```
feat(dsr): add data export generator
```
