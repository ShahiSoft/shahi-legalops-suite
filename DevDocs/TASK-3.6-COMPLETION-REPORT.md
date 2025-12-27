# Task 3.6 — DSR Admin UI (Requests List)

Status: Completed (phase 1: menu + page scaffold)

Changes:
- Added DSR admin submenu: ShahiLegalopsSuite → DSR Requests
- Added capability: `slos_manage_dsr` (activation/deactivation wiring via MenuManager)
- New page controller: `includes/Admin/DSRRequests.php` with filters and table rendering using `DSR_Repository::list_requests()`
- Updated breadcrumbs and page detection in `includes/Admin/MenuManager.php`

Files:
- includes/Admin/MenuManager.php — submenu, capability, breadcrumbs
- includes/Admin/DSRRequests.php — page controller (render)

Notes:
- Filters supported: `status`, `request_type`
- Table columns: ID, Email, Type, Status, Created (submitted_at)
- Capability check matches API controller (`slos_manage_dsr`)

Next Steps:
- Implement `WP_List_Table` integration for pagination and bulk actions
- Add actions (view details, assign, status updates) via admin-ajax or REST
- Add stats header (counts by status/type) from repository helpers
