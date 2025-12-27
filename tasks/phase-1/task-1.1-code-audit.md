# TASK 1.1: Code Audit & Assessment

**Phase:** 1 (Assessment & Cleanup)  
**Effort:** 8-10 hours  
**Prerequisites:** None (first task)  
**Next Task:** task-1.2-database-design.md  

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 1.1 of the Shahi LegalOps Suite plugin.

TASK: Code Audit & Assessment
PHASE: 1 (Assessment & Cleanup)
EFFORT: 8-10 hours
REPO: Shahi LegalOps Suite WordPress plugin
LOCATION: c:\docker-wp\html\wp-content\plugins\Shahi LegalOps Suite - 3.0.1

CONTEXT:
This WordPress plugin is a compliance suite with 10 planned modules. Currently 3 modules are complete 
(Accessibility Scanner, Security, Dashboard UI). You need to audit the actual codebase to understand 
what exists vs what's claimed.

INPUT STATE (verify these exist):
- [ ] Repository cloned to local machine at: c:\docker-wp\html\wp-content\plugins\Shahi LegalOps Suite - 3.0.1
- [ ] WordPress environment running at: http://localhost (or appropriate local URL)
- [ ] Composer dependencies installed (run: cd "c:\docker-wp\html\wp-content\plugins\Shahi LegalOps Suite - 3.0.1" && composer install)
- [ ] Database accessible and WordPress working
- [ ] Git configured (if not: git config --global user.email "ai@agent.local")

YOUR TASK:

1. EXPLORE THE CODEBASE
   
   First, navigate to the plugin directory:
   Command: cd "c:\docker-wp\html\wp-content\plugins\Shahi LegalOps Suite - 3.0.1"
   
   Count PHP files in includes/Modules/:
   Command (Windows PowerShell): Get-ChildItem -Path "includes\Modules" -Filter "*.php" -Recurse | Measure-Object | Select-Object -ExpandProperty Count
   
   Expected: 20-50 files
   
   List what exists in the modules:
   Command: Get-ChildItem -Path "includes\Modules" -Directory
   
   What you're looking for: AccessibilityScanner, Security, Dashboard, etc.
   
   Count total lines of actual code (exclude vendor, tests):
   Command: Get-ChildItem -Path "includes" -Filter "*.php" -Recurse | Where-Object { $_.FullName -notmatch "vendor|tests" } | Get-Content | Measure-Object -Line | Select-Object -ExpandProperty Lines
   
   Record this number.

2. CHECK DATABASE TABLES
   
   WordPress CLI command:
   Command: wp db tables
   
   Then filter for complyflow tables:
   Command: wp db tables | Select-String "complyflow"
   
   This should show any existing complyflow tables. If none exist, that's OK - you'll create them in TASK 1.3.
   Record how many tables exist.

3. AUDIT REST API ENDPOINTS
   
   Check for existing REST endpoints:
   Command: wp rest-api list | Select-String "complyflow"
   
   Count how many /complyflow/ endpoints exist.
   If command doesn't work, note that and move on.

4. CREATE PROJECT AUDIT DOCUMENT
   
   Create a file: project-audit-results.md in the plugin root directory
   
   Write down what you found. Example content:
   ```markdown
   # Code Audit Results - TASK 1.1
   
   Date: [Current Date]
   Audited by: AI Agent
   
   ## Actual Codebase State
   - Total PHP files in includes/: [NUMBER]
   - Total PHP files in includes/Modules/: [NUMBER]
   - Total lines of code (excluding vendor/tests): [NUMBER]
   - Existing modules found: [LIST THEM]
   - Database tables: [NUMBER] tables found
   - REST API endpoints: [NUMBER] endpoints found
   
   ## Module Status
   - Accessibility Scanner: [EXISTS/NOT FOUND]
   - Security Module: [EXISTS/NOT FOUND]
   - Dashboard UI: [EXISTS/NOT FOUND]
   - Consent Management: [EXISTS/NOT FOUND]
   - DSR Portal: [EXISTS/NOT FOUND]
   - Legal Documents: [EXISTS/NOT FOUND]
   
   ## General Observations
   - [Any notable findings]
   - [Code quality observations]
   - [Missing components]
   
   ## Ready for Phase 1 Development
   - [ ] WordPress installation verified working
   - [ ] Plugin directory accessible
   - [ ] Database connection working
   - [ ] Git repository initialized
   - [ ] Composer dependencies installed
   
   ## Next Steps
   - Proceed to TASK 1.2: Database Schema Design
   - Create migration files for 7 new tables
   ```

5. VERIFY WORDPRESS HEALTH
   
   Check WordPress is working properly:
   Command: wp core check-update
   Command: wp plugin list
   
   Verify the plugin shows up in the list.

6. CHECK WP_DEBUG STATUS
   
   Command: wp config get WP_DEBUG
   
   If WP_DEBUG is false, recommend enabling it for development:
   Note in audit document: "Recommend enabling WP_DEBUG for development"

OUTPUT STATE (what exists after this task):
- [ ] project-audit-results.md file created with comprehensive findings
- [ ] Understanding of current codebase state documented
- [ ] Module inventory complete
- [ ] Database state documented
- [ ] WordPress health verified
- [ ] Ready to proceed to TASK 1.2

VERIFICATION (run these to confirm success):

1. Check audit file exists:
   Command (Windows): Get-Content project-audit-results.md
   Expected: Shows the contents of the audit document with all sections filled

2. Check WordPress is working:
   Command: wp eval 'echo "WordPress ready: " . get_bloginfo("name");'
   Expected: Shows WordPress blog name

3. Verify plugin file exists:
   Command: Test-Path shahi-legalops-suite.php
   Expected: True

4. Check Git repository:
   Command: git status
   Expected: Shows current branch and status

SUCCESS CRITERIA:
- [ ] project-audit-results.md created with all sections filled
- [ ] All audit numbers recorded (file counts, line counts, etc.)
- [ ] WordPress verified as working
- [ ] Plugin directory verified as accessible
- [ ] Database connection verified
- [ ] Git repository verified
- [ ] All commands ran without critical errors
- [ ] Clear picture of current state documented

ROLLBACK (if verification fails):
   No database changes or code modifications made in this task.
   If audit document has errors, simply delete it and recreate:
   Command: Remove-Item project-audit-results.md
   Then re-run the audit steps.

TROUBLESHOOTING:

Problem: "wp: command not found"
Solution: Install WordPress CLI:
  - Download from https://wp-cli.org/
  - Or install via Composer: composer global require wp-cli/wp-cli-bundle
  - Or use Docker: docker run -it --rm wordpress:cli wp --info

Problem: "Database connection failed"
Solution: 
  - Check wp-config.php has correct database credentials
  - Verify MySQL/MariaDB is running: Get-Service MySQL (or appropriate service name)
  - Test database connection: wp db check

Problem: "Can't find plugin directory"
Solution: 
  - Verify you're in the correct directory
  - Check path: Get-Location
  - Navigate to plugin: cd "c:\docker-wp\html\wp-content\plugins\Shahi LegalOps Suite - 3.0.1"

Problem: "Git not initialized"
Solution:
  - Initialize git: git init
  - Configure git: git config user.email "ai@agent.local"
  - Create first commit: git add . && git commit -m "Initial commit"

WHAT TO REPORT BACK:
When this task is complete, report:
1. "✅ TASK 1.1 verification passed"
2. Summary of findings (number of files, modules found, etc.)
3. Confirmation that project-audit-results.md was created
4. Any issues encountered and how they were resolved
5. "Ready to proceed to TASK 1.2"

COMMIT MESSAGE:
After verification passes, commit your changes:
Command: git add project-audit-results.md
Command: git commit -m "TASK 1.1: Code audit and assessment completed"

NEXT TASK:
After this task is complete and verified, proceed to:
tasks/phase-1/task-1.2-database-design.md
```

---

## ✅ COMPLETION CHECKLIST

After running this task, verify:

- [ ] project-audit-results.md exists and is complete
- [ ] All file counts recorded
- [ ] All module statuses documented
- [ ] WordPress verified working
- [ ] Git commit created
- [ ] Ready for TASK 1.2

---

**Status:** Ready to execute  
**Time:** 8-10 hours  
**Next:** task-1.2-database-design.md
