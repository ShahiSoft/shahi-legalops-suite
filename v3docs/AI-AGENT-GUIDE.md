# 🤖 AI Agent Implementation Guide

**Purpose:** This guide explains how to work with this codebase if you're an AI agent. Read this FIRST.

---

## 📖 Start Here: The Problem We're Solving

This WordPress plugin suite has:
- **3 completed modules** (Accessibility Scanner, Security, Dashboard UI)
- **7 planned modules** (Consent, DSR, Legal Docs, Forms, Cookies, Vendors, Analytics)
- **10 weeks of work** to implement all modules
- **No human developers** - only AI agents building the code

This creates unique challenges:
- ✅ Tasks must be fully atomic (no dependencies between simultaneous AI agents)
- ✅ Every step must be verifiable (no "I think it's done")
- ✅ Every step must be reversible (if something breaks, rollback)
- ✅ No context carryover between agent invocations (state is git + code)
- ✅ Code quality must be consistent (follow patterns, not conventions)

---

## 🎯 The Three Types of Tasks You'll See

### Type 1: Infrastructure Tasks (Most Critical)
**Example:** TASK 1.1 (Code Audit), TASK 1.2 (Database Migrations)

These lay foundation for everything else. If you mess up:
- Database schema wrong → All data storage breaks
- File structure wrong → All code organization breaks
- Naming conventions wrong → All APIs become inconsistent

**How to approach:**
- Read existing code carefully (AccessibilityScanner is your template)
- Follow patterns exactly
- Verify multiple times
- Test thoroughly before moving forward

### Type 2: Feature Implementation Tasks
**Example:** TASK 2.5 (Preference Center), TASK 3.1 (DSR Model)

These build actual features. If you mess up:
- Feature doesn't work → User experience broken
- Not testable → Blockers later
- Security issues → GDPR non-compliance

**How to approach:**
- Follow the module guide specifications exactly
- Write tests alongside code
- Security review is mandatory
- Verify with real data if possible

### Type 3: Integration & Testing Tasks
**Example:** TASK 2.13 (Testing & QA), TASK 2.15 (Phase Checkpoint)

These ensure everything works together. If you mess up:
- Hidden bugs surface later (expensive)
- Performance issues compound
- Security vulnerabilities discovered in production

**How to approach:**
- Run all tests - don't skip any
- Test edge cases
- Test with real WordPress environment
- Document findings thoroughly

---

## 🔄 The Task Workflow (What Every Task Follows)

Every task in this roadmap has this structure:

```
TASK X.Y: [Name]
├─ Purpose: [Why we're doing this]
├─ Input State: [What must exist before this task starts]
├─ Dependencies: [What other tasks must finish first]
├─ Actions: [Exactly what to do - step by step]
├─ Output State: [What will exist after the task]
├─ Verification: [How to test that it worked]
└─ Rollback: [What to do if it fails]
```

**Your job:** Execute the actions, verify the output, move on (or rollback if verification fails).

---

## ⚠️ Critical Rules (FOLLOW THESE EXACTLY)

### Rule 1: Check Dependencies First
```bash
# Before starting TASK 2.5, verify TASK 2.4 is complete
git log --oneline | grep "TASK 2.4"
# Should see: "TASK 2.4: [Description]" in recent commits
# If not, go back and complete TASK 2.4 first
```

### Rule 2: Verify Input State Exists
```bash
# Task says "Input State: Database tables must exist"
wp db tables | grep complyflow_
# Must see: wp_complyflow_consent, wp_complyflow_dsr_requests, etc.
# If missing, previous task didn't complete - don't proceed
```

### Rule 3: Follow Actions Exactly
- Don't skip steps "to save time"
- Don't combine multiple tasks
- Don't make assumptions about what comes next
- Each action is there for a reason

### Rule 4: Run Verification (Not Optional)
```bash
# Task provides verification code - run it EXACTLY as written
# If it fails: STOP
# Don't debug on your own - follow the Rollback section
# Then re-read the Actions and try again
```

### Rule 5: Commit After Each Task
```bash
git add .
git commit -m "TASK X.Y: [Description of what you did]"
git push origin main
```

This creates a rollback point and communicates progress to other agents.

### Rule 6: Check Code Quality
Before committing, verify:
- [ ] No WP_DEBUG errors/warnings: `grep -i "php error" debug.log`
- [ ] Code follows WordPress standards: Check class naming, method names
- [ ] Database queries use prepare(): `grep -r "wp_query" --include="*.php"`
- [ ] All user input is sanitized: `grep -r "\\$_GET\\|\\$_POST" --include="*.php"`
- [ ] All output is escaped: Check echo statements

### Rule 7: Document Your Work
Add comments to your code:
```php
/**
 * Fetch user consent records from database
 *
 * @param int $user_id The WordPress user ID
 * @return array Array of ConsentRecord objects
 * @throws Exception If user doesn't exist
 */
public function getConsentByUser($user_id) {
    // Implementation here
}
```

This helps future AI agents understand what you built.

---

## 🏗️ Code Structure You'll See

### Module Pattern (Study This!)
All modules follow this structure:

```
includes/Modules/{ModuleName}/
├── {ModuleName}.php           # Main module class (extend Module)
├── Models/                    # Data classes
│   ├── Entity1.php           # e.g., ConsentRecord.php
│   └── Entity2.php           # e.g., Banner.php
├── Services/                 # Business logic
│   ├── Service1.php          # e.g., ScriptBlocker.php
│   └── Service2.php          # e.g., GeoTargeting.php
├── Repositories/             # Database access (CRUD)
│   ├── Entity1Repository.php
│   └── Entity2Repository.php
├── Admin/                    # Admin pages/forms
│   ├── Settings.php
│   └── Dashboard.php
├── Shortcodes/              # Public-facing [shortcodes]
│   └── FormShortcode.php
├── API/                     # REST API endpoints
│   └── {Entity}Controller.php
└── Database/
    └── Migrations/
        └── migration_version.php
```

### Example: Consent Module Structure
```
includes/Modules/Consent/
├── Consent.php                # Main class
├── Models/
│   ├── ConsentRecord.php      # Represents one consent record
│   └── Banner.php             # Represents banner settings
├── Services/
│   ├── ScriptBlocker.php      # Blocks scripts until consent
│   ├── GoogleConsentMode.php  # Google Consent Mode v2
│   └── GeoTargeting.php       # Region detection
├── Repositories/
│   ├── ConsentRepository.php  # CRUD for consent records
│   └── BannerRepository.php   # CRUD for banner settings
├── Admin/
│   ├── BannerSettings.php     # Admin settings page
│   └── ConsentDashboard.php   # Analytics dashboard
├── Shortcodes/
│   └── PreferenceCenterShortcode.php
├── API/
│   └── ConsentController.php  # REST endpoints
└── Database/
    └── Migrations/
        └── migration_2025_12_20_consent.php
```

**Key pattern:** Model → Repository → Service → Admin/API

### Database Access Pattern (ALWAYS USE THIS)
```php
// This is the pattern - copy it exactly

class ConsentRepository extends BaseRepository {
    protected $table = 'complyflow_consent';
    
    public function create(array $data): int {
        // Validate
        if (empty($data['user_id'])) {
            throw new Exception('user_id required');
        }
        
        // Prepare
        $prepared = [
            'user_id' => intval($data['user_id']),
            'type' => sanitize_text_field($data['type']),
            'status' => sanitize_text_field($data['status']),
            'created_at' => current_time('mysql'),
        ];
        
        // Insert
        $result = $this->wpdb->insert(
            $this->wpdb->prefix . $this->table,
            $prepared,
            ['%d', '%s', '%s', '%s']
        );
        
        if (!$result) {
            throw new Exception('Failed to insert: ' . $this->wpdb->last_error);
        }
        
        return $this->wpdb->insert_id;
    }
    
    public function findById(int $id): ?array {
        $result = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}{$this->table} WHERE id = %d",
                $id
            ),
            ARRAY_A
        );
        
        return $result ?? null;
    }
}
```

**Key points:**
- Always use `$wpdb->prepare()` for SQL
- Always sanitize/validate inputs
- Always check for errors
- Always add type hints

---

## 🧪 How to Test Your Code

### Unit Test Example
```php
// Create file: tests/Modules/Consent/ConsentRepositoryTest.php

class ConsentRepositoryTest extends \PHPUnit\Framework\TestCase {
    private $repository;
    
    protected function setUp(): void {
        $this->repository = new ConsentRepository();
    }
    
    public function testCreateConsentRecord() {
        // Arrange
        $data = [
            'user_id' => 123,
            'type' => 'analytics',
            'status' => 'accepted',
        ];
        
        // Act
        $id = $this->repository->create($data);
        
        // Assert
        $this->assertGreaterThan(0, $id);
        
        $record = $this->repository->findById($id);
        $this->assertEquals(123, $record['user_id']);
        $this->assertEquals('analytics', $record['type']);
    }
    
    public function testFindByIdReturnsNull() {
        $record = $this->repository->findById(99999);
        $this->assertNull($record);
    }
}
```

Run with: `phpunit tests/Modules/Consent/ConsentRepositoryTest.php`

### Manual Test Example
```bash
# Test via command line
wp eval 'require "includes/Modules/Consent/Repositories/ConsentRepository.php"; 
$repo = new ConsentRepository();
$id = $repo->create(["user_id" => 1, "type" => "analytics", "status" => "accepted"]);
var_dump($id); // Should print: int(1) or similar
'
```

### Browser Test Example
```bash
# 1. Visit your WordPress site
# 2. Open developer console (F12)
# 3. Run verification code:

curl -X GET http://localhost/wp-json/complyflow/v1/consent/status \
  -H "Content-Type: application/json"

# Should return valid JSON with consent status
```

---

## 🔒 Security Checklist (MANDATORY)

Before committing ANY code, verify:

### Input Handling
```php
// ❌ WRONG - directly uses user input
$user_id = $_GET['user_id'];
$query = "SELECT * FROM users WHERE id = $user_id";

// ✅ RIGHT - sanitizes and prepares
$user_id = intval($_GET['user_id']);
$query = $wpdb->prepare("SELECT * FROM users WHERE id = %d", $user_id);
```

### Output Handling
```php
// ❌ WRONG - outputs user data directly
echo $user_name;

// ✅ RIGHT - escapes output
echo esc_html($user_name);
echo esc_attr($data_attribute);
echo esc_url($link_url);
echo esc_js($javascript_variable);
```

### SQL Queries
```php
// ❌ WRONG - vulnerable to SQL injection
$query = "SELECT * FROM users WHERE name = '" . $_GET['name'] . "'";

// ✅ RIGHT - uses prepared statements
$query = $wpdb->prepare(
    "SELECT * FROM users WHERE name = %s",
    $_GET['name']
);
```

### Forms & Nonces
```php
// ❌ WRONG - form without nonce protection
<form method="POST">
    <input name="consent" value="accept">
    <button>Save</button>
</form>

// ✅ RIGHT - form with nonce
<form method="POST">
    <?php wp_nonce_field('consent_action', 'consent_nonce'); ?>
    <input name="consent" value="accept">
    <button>Save</button>
</form>

// ✅ RIGHT - verify nonce in handler
if (!isset($_POST['consent_nonce']) || 
    !wp_verify_nonce($_POST['consent_nonce'], 'consent_action')) {
    wp_die('Security check failed');
}
```

### Capabilities
```php
// ❌ WRONG - anyone can see admin data
$data = get_option('complyflow_settings');
return rest_ensure_response($data);

// ✅ RIGHT - only admins can see
if (!current_user_can('manage_options')) {
    return new WP_Error('permission_denied', 'Insufficient permissions');
}
$data = get_option('complyflow_settings');
return rest_ensure_response($data);
```

---

## 🐛 Debugging Guide

### Problem: "Verification fails with database error"
```bash
# Check WP_DEBUG log
tail -50 wp-content/debug.log

# Check database directly
wp db query "SHOW TABLES LIKE 'wp_complyflow_%';"

# Check if migration ran
wp db query "DESC wp_complyflow_consent;"

# If table doesn't exist, the migration task failed
# Follow TASK 1.2 Rollback, then retry
```

### Problem: "REST API endpoint returns 404"
```bash
# Check if REST API is registered
wp eval 'echo json_encode(rest_get_routes());' | grep consent

# If not found, the registration code failed
# Check includes/API/ConsentController.php
# Verify register_rest_route() is called
```

### Problem: "Permission denied error"
```php
// Check current user capabilities
wp eval 'var_dump(get_current_user_by("id", 1)->caps);'

// Make sure code uses current_user_can()
// Or ensure test user has admin capability
```

### Problem: "PHP Parse Error"
```bash
# Check for syntax errors
php -l includes/Modules/Consent/Consent.php

# Run full PHP check
wp eval 'echo "PHP is working";'

# If error, review last code you wrote
# Check for missing semicolons, braces, etc.
```

---

## 📋 Checklist Before Every Commit

- [ ] Task description says this is done
- [ ] Verification code passes completely
- [ ] WP_DEBUG has no new errors/warnings
- [ ] Code follows WordPress standards
- [ ] Security checklist passed (no user input issues)
- [ ] Comments/documentation added
- [ ] Tests written and passing
- [ ] Database changes committed
- [ ] Git commit message is clear: `TASK X.Y: [Description]`
- [ ] Code reviewed (at least by looking at diffs)

---

## 🚨 When to Stop & Ask for Help

Stop if ANY of these are true:
- [ ] You don't understand what the task wants
- [ ] Verification fails and you don't know why
- [ ] You need to break a design pattern (suggests understanding is wrong)
- [ ] Code is significantly different from existing patterns
- [ ] Security implications are unclear
- [ ] Task seems to conflict with another task
- [ ] You've been stuck on this for 2+ hours

**When asking for help:**
1. Tell us which task you're on
2. Tell us exactly what failed
3. Show error messages
4. Show relevant code snippets
5. Tell us what you've tried

---

## 📚 Key Files to Study First

Before starting implementation, study these files:

1. **`includes/Modules/AccessibilityScanner/AccessibilityScanner.php`**
   - Look at how Module class extends base Module
   - Study how it's structured

2. **`includes/Core/ModuleManager.php`**
   - Understand how modules are registered and loaded

3. **`includes/Core/Database/BaseRepository.php`**
   - Understand the repository pattern (copy this pattern)

4. **`v3docs/CF_FEATURES_v3.md`**
   - Understand what features you're building

5. **`v3docs/WINNING-FEATURES-2026.md`**
   - Understand market context and why we're building this way

---

## 💡 Pro Tips

### Tip 1: Use Existing Code as Template
```bash
# Want to create a new repository?
# Copy the AccessibilityScanner repository and modify it
cp includes/Modules/AccessibilityScanner/Repositories/*.php \
   includes/Modules/Consent/Repositories/
# Then modify for Consent model
```

### Tip 2: Test Incrementally
```bash
# Don't write 500 lines then test
# Write 50 lines, test, then write next 50 lines

# Write model → Test model → Write repository → Test repository
# This catches bugs early
```

### Tip 3: Use WordPress CLI for Testing
```bash
# Much faster than clicking in admin panel
wp eval 'echo "Test code here"'
wp plugin activate shahi-legalops-suite
wp plugin deactivate shahi-legalops-suite
wp db tables
wp option get complyflow_settings
```

### Tip 4: Check git diff Before Committing
```bash
git diff
# Review exactly what you changed
# Make sure it makes sense
# Make sure you didn't accidentally change unrelated files
```

### Tip 5: Use Descriptive Commit Messages
```bash
# ❌ BAD
git commit -m "fixed stuff"

# ✅ GOOD
git commit -m "TASK 2.1: Create ConsentRecord model and repository with CRUD operations"

# This helps other agents understand what was done
```

---

## 🎓 Learning Resources

- [WordPress Plugin Developer Handbook](https://developer.wordpress.org/plugins/)
- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)
- [WordPress Security Documentation](https://developer.wordpress.org/plugins/security/)
- [PHP PSR Standards](https://www.php-fig.org/psr/)
- [WCAG 2.2 Accessibility Standards](https://www.w3.org/WAI/WCAG22/quickref/)

---

## 🎯 Success Metrics for Your Implementation

You know you've done well when:
- ✅ Code follows existing patterns exactly
- ✅ All tests pass (unit + integration)
- ✅ Zero WP_DEBUG errors/warnings
- ✅ Security checklist passed
- ✅ Verification code passes
- ✅ Code is well-documented
- ✅ Other modules don't break
- ✅ Feature works as specified

---

## 📞 Quick Reference

### Git Commands You'll Use
```bash
git status              # See what changed
git diff                # See exact changes
git add .               # Stage all changes
git commit -m "..."     # Commit with message
git push origin main    # Push to repository
git log --oneline       # See recent commits
git reset --hard        # Undo all changes (if broken)
```

### WordPress CLI Commands
```bash
wp plugin list          # See installed plugins
wp plugin activate      # Activate plugin
wp plugin deactivate    # Deactivate plugin
wp db tables            # List database tables
wp option get           # Get option value
wp eval                 # Run PHP code
```

### PHP Commands
```bash
php -l file.php         # Check syntax
php -r "echo 'test';"   # Run code
```

---

## 🚀 You're Ready!

You now understand:
- ✅ How the project is structured
- ✅ What tasks look like
- ✅ How to verify your work
- ✅ How to handle failures
- ✅ Code patterns to follow
- ✅ Security requirements
- ✅ Testing approach

**Next Steps:**
1. Read TASK 1.1 (Code Audit)
2. Execute the actions listed
3. Verify the output
4. Commit to git
5. Move to TASK 1.2

Good luck! 🎉

---

**Document Version:** 1.0  
**Last Updated:** December 19, 2025  
**Audience:** AI Agents implementing Shahi LegalOps Suite
