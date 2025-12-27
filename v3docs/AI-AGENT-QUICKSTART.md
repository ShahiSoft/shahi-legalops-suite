# 🚀 AI AGENT QUICK START

**Read this first if you're an AI agent about to implement this project.**

---

## ⚡ 60-Second Overview

- **What:** WordPress compliance plugin (Consent, DSR, Legal Docs, Accessibility)
- **Why:** Market-proven 2026 features from CookieYes, Cookiebot, Complianz
- **How:** 78 atomic AI tasks, 20-24 weeks, 650-850 hours
- **Your job:** Pick a task, execute it, verify it works, commit it, move on

---

## 📚 Read These Files In Order

1. **This file (5 min)** ← You're here
2. **`v3docs/AI-AGENT-GUIDE.md` (30 min)** ← Core AI implementation guidelines
3. **`v3docs/ROADMAP.md` (40 min)** ← Your task list with all tasks documented
4. **Module guide for your phase** (20 min) ← Feature specifications

---

## 🎯 Your First Task: TASK 1.1

**Name:** Code Audit & Assessment  
**Time:** 8-10 hours  
**Difficulty:** Easy (just exploration)  

**What to do:**
1. Clone repository: `git clone [repo]`
2. Count actual code: `find includes/Modules -name "*.php" | wc -l`
3. List tables: `wp db tables | grep complyflow_`
4. Create GitHub issues and project board
5. Verify success: GitHub board visible with all tasks

**Success looks like:**
- GitHub project board with 78 tasks
- All tasks have descriptions
- Tasks are ordered by dependency
- No ambiguous requirements

**If it breaks:**
- Follow "Rollback" section: Delete GitHub board
- Re-read task description
- Try again

---

## 🔄 Task Workflow (Every Task Follows This)

```
1. Find your task (e.g., TASK 2.5)
2. Check dependencies (are TASK 2.1-2.4 complete?)
3. Verify input state (do required files/tables exist?)
4. Execute actions (follow step-by-step)
5. Run verification (does test pass?)
6. Commit to git (git commit -m "TASK X.Y: Description")
7. Mark complete in GitHub
8. Proceed to next task
```

**If verification fails:**
- Don't debug on your own
- Follow the Rollback section
- Try again from step 4

---

## ⚠️ 7 Critical Rules

### Rule 1: Finish Dependencies First
```bash
git log --oneline | grep TASK
# Make sure TASK 2.1-2.4 exist before starting TASK 2.5
```

### Rule 2: Verify Input State
```bash
# Task says "tables must exist"
wp db tables | grep complyflow_
# Must see: wp_complyflow_consent, wp_complyflow_dsr_requests
```

### Rule 3: Follow Actions Exactly
- Don't skip steps
- Don't combine tasks
- Each step is there for a reason

### Rule 4: Run Verification (Not Optional)
```bash
# Task provides verification code
# Run it exactly as written
# If it fails: STOP, follow Rollback
```

### Rule 5: Commit After Each Task
```bash
git add .
git commit -m "TASK X.Y: [Clear description]"
git push origin main
```

### Rule 6: Check Code Quality
Before committing, verify:
- No WP_DEBUG errors: `grep "error" debug.log | head -5`
- Code follows WordPress standards (check class names, method names)
- All SQL uses `$wpdb->prepare()`
- All user input is sanitized
- All output is escaped

### Rule 7: Document Your Code
Add comments to every class and method:
```php
/**
 * Create a new consent record
 * 
 * @param int $user_id The WordPress user ID
 * @return int The new record ID
 */
public function createConsent($user_id) {
    // ...
}
```

---

## 📂 Code Structure to Follow

**All modules follow this pattern:**

```
includes/Modules/Consent/
├── Consent.php                    # Main class
├── Models/
│   ├── ConsentRecord.php          # Data class
│   └── Banner.php                 # Data class
├── Repositories/
│   └── ConsentRepository.php      # Database CRUD
├── Services/
│   ├── ScriptBlocker.php          # Business logic
│   └── GoogleConsentMode.php      # Business logic
├── Admin/
│   └── ConsentSettings.php        # Admin pages
├── Shortcodes/
│   └── PreferenceCenterShortcode.php
├── API/
│   └── ConsentController.php      # REST endpoints
└── Database/
    └── Migrations/
        └── migration_2025_12_20_consent.php
```

**Look at AccessibilityScanner module for the pattern.**

---

## 🧪 Quick Testing Guide

### Test 1: Unit Test
```php
$repo = new ConsentRepository();
$id = $repo->create(['user_id' => 1, 'type' => 'analytics', 'status' => 'accepted']);
assert($id > 0);  // Should pass
```

### Test 2: Database Check
```bash
wp db query "SELECT COUNT(*) FROM wp_complyflow_consent;"
```

### Test 3: Admin Interface
```bash
# Visit: http://localhost/wp-admin/admin.php?page=complyflow-consent
# Should load without errors
```

### Test 4: REST API
```bash
curl http://localhost/wp-json/complyflow/v1/consent/status
# Should return JSON
```

---

## 🔒 Security Checklist (MANDATORY)

Before committing EVERY time:

### Input Handling
```php
// ❌ WRONG
$user_id = $_GET['user_id'];

// ✅ RIGHT
$user_id = intval($_GET['user_id']);
```

### Database Queries
```php
// ❌ WRONG
$query = "SELECT * FROM users WHERE id = $id";

// ✅ RIGHT
$query = $wpdb->prepare("SELECT * FROM users WHERE id = %d", $id);
```

### Output
```php
// ❌ WRONG
echo $name;

// ✅ RIGHT
echo esc_html($name);
```

### Forms
```php
// ✅ ALWAYS add nonce to forms
<?php wp_nonce_field('action_name', 'action_nonce'); ?>

// ✅ ALWAYS verify nonce
if (!wp_verify_nonce($_POST['action_nonce'], 'action_name')) {
    wp_die('Security failed');
}
```

---

## 🐛 Common Problems & Solutions

### "Task verification fails with database error"
1. Check: `wp db tables | grep complyflow_`
2. If missing: Previous task failed, follow its Rollback
3. Re-run previous task

### "REST API endpoint returns 404"
1. Check: `wp eval 'echo json_encode(rest_get_routes());' | grep consent`
2. If not found: Registration code didn't run
3. Check your API controller file for errors

### "Permission denied when accessing admin page"
1. Make sure you're logged in as admin
2. Verify capability check in code: `current_user_can('manage_options')`
3. Test with: `wp eval 'var_dump(current_user_can("manage_options"));'`

### "PHP Parse Error"
1. Check syntax: `php -l includes/Modules/Consent/Consent.php`
2. Review your last code changes
3. Look for missing semicolons, braces

---

## ✅ Checklist Before Every Commit

- [ ] All task actions completed
- [ ] Verification code passed
- [ ] WP_DEBUG has no new errors
- [ ] Code follows WordPress standards
- [ ] No user input vulnerabilities
- [ ] No output escaping issues
- [ ] Comments/documentation added
- [ ] Tests written and passing
- [ ] Commit message is clear: `TASK X.Y: [Description]`

---

## 📊 Project At a Glance

| Phase | Tasks | Hours | Weeks | What |
|-------|-------|-------|-------|------|
| 1 | 8 | 35-40 | 2 | Database, Framework |
| 2 | 15 | 95-110 | 4 | Consent Management |
| 3 | 13 | 70-85 | 3 | DSR Portal |
| 4 | 12 | 55-70 | 3 | Legal Documents |
| 5-7 | 30 | 400+ | 10 | Analytics, Optional, Polish |
| | **78** | **650-850** | **22** | **TOTAL** |

**Your path forward:**
1. Complete TASK 1.1 (you are here)
2. Complete TASK 1.2-1.8 (Phase 1 framework)
3. Complete TASK 2.1-2.15 (Consent module - the core)
4. Complete TASK 3.1-3.13 (DSR portal - the differentiator)
5. Continue with Phases 4-7

---

## 🎓 Key Files to Study

Before starting implementation, study:

1. **`includes/Modules/AccessibilityScanner/AccessibilityScanner.php`**
   - This is your template for module structure

2. **`includes/Core/ModuleManager.php`**
   - How modules are registered and managed

3. **`includes/Core/Database/BaseRepository.php`**
   - The repository pattern (copy this exactly)

4. **`v3docs/CF_FEATURES_v3.md`**
   - What features you're building

5. **`v3docs/WINNING-FEATURES-2026.md`**
   - Why we're building it this way

---

## 🎯 Success Looks Like

After TASK 1.1:
- ✅ GitHub project board created
- ✅ All 78 tasks listed
- ✅ Tasks ordered by dependency
- ✅ No ambiguous requirements

After TASK 2.15 (Consent module complete):
- ✅ Banner displays on frontend
- ✅ Script blocking works
- ✅ Google Consent Mode v2 sends signals to Google
- ✅ Admin dashboard shows analytics
- ✅ REST API endpoints functional
- ✅ All tests passing
- ✅ No WP_DEBUG errors

After TASK 3.13 (DSR module complete):
- ✅ Users can request their data
- ✅ Site admin can export data in multiple formats
- ✅ Audit trail tracks all requests
- ✅ SLA notifications send automatically
- ✅ Team can manage requests together

---

## 💬 When to Ask For Help

Stop and ask if:
- [ ] You don't understand what a task wants
- [ ] Verification fails and you can't figure out why
- [ ] Code is very different from existing patterns
- [ ] Security implications are unclear
- [ ] Task conflicts with another task
- [ ] You're stuck for 2+ hours

**How to ask:**
1. Tell us which task
2. Show the error message
3. Show relevant code
4. Tell us what you tried

---

## 🚀 Let's Go!

You're ready. Here's what happens next:

1. **You:** Read `v3docs/AI-AGENT-GUIDE.md` (next 30 minutes)
2. **You:** Read `v3docs/ROADMAP.md` (next 40 minutes)
3. **You:** Start TASK 1.1 (Code Audit & Assessment)
4. **You:** Complete all 78 tasks over 20-24 weeks
5. **Result:** Production-ready WordPress compliance plugin with 2026 winning features

**Questions? Check:**
- AI-AGENT-GUIDE.md (troubleshooting section)
- ROADMAP.md (instructions for AI agents section)
- Module guides (specifications for features)

**Let's build something great!** 🎉

---

**Version:** 1.0  
**Last Updated:** December 19, 2025  
**Audience:** AI Agents starting implementation
