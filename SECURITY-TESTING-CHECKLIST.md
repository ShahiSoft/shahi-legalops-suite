# ShahiTemplate - Security Testing Checklist

**Version**: 1.0.0  
**Last Updated**: December 13, 2025  
**Purpose**: Security validation for all plugin features

---

## 📋 **Testing Overview**

This checklist ensures all security measures are properly implemented and functioning. Complete all sections before releasing any feature.

**Testing Levels:**
- 🔴 **Critical** - Must pass before deployment
- 🟡 **Important** - Should pass, issues need tracking
- 🟢 **Recommended** - Best practice validation

---

## 🔐 **1. Nonce Protection Testing**

### 1.1 Form Nonces (🔴 Critical)

- [ ] **All forms have nonce fields**
  - Test: Search codebase for `<form` tags
  - Verify: Each form contains `Security::nonce_field()` call
  - Location: Admin pages, settings forms, action forms

- [ ] **Nonce verification on form submission**
  - Test: Submit form without nonce parameter
  - Expected: Request denied with security error
  - Test: Submit form with invalid nonce
  - Expected: Request denied

- [ ] **Nonce action names are unique**
  - Test: Review all nonce actions in codebase
  - Verify: No duplicate action names
  - Pattern: Use descriptive names like `'save_settings'`, not `'save'`

### 1.2 URL Nonces (🔴 Critical)

- [ ] **Action links include nonces**
  - Test: Check all delete/activate/deactivate links
  - Verify: URLs generated via `Security::nonce_url()`
  - Example: Module enable/disable links

- [ ] **URL nonce verification**
  - Test: Access action URL without `_wpnonce` parameter
  - Expected: Access denied
  - Test: Access with expired/invalid nonce
  - Expected: Access denied

### 1.3 AJAX Nonces (🔴 Critical)

- [ ] **AJAX nonces localized to JavaScript**
  - Test: View page source, check for nonce in `wp_localize_script`
  - Verify: Fresh nonce generated on each page load
  - File: Check asset enqueuing code

- [ ] **AJAX handlers validate nonces**
  - Test: Send AJAX request without nonce
  - Expected: Returns error response
  - Test: Send with invalid nonce
  - Expected: Returns error response

- [ ] **Check nonce in network tab**
  - Test: Open browser DevTools, monitor AJAX requests
  - Verify: Nonce parameter present in request data
  - Verify: Server validates before processing

---

## 👤 **2. Capability Checks Testing**

### 2.1 Admin Page Access (🔴 Critical)

- [ ] **Non-admin users cannot access admin pages**
  - Test: Log in as Subscriber role
  - Navigate: Try accessing plugin admin pages directly
  - Expected: "You do not have sufficient permissions"

- [ ] **Capability checks at page render**
  - Test: Review page rendering functions
  - Verify: `Security::check_capability_or_die()` at function start
  - Location: All admin page callbacks

### 2.2 Settings Modification (🔴 Critical)

- [ ] **Settings cannot be modified without permissions**
  - Test: POST settings form as non-admin
  - Expected: Request blocked
  - Verify: `check_capability('manage_options')` before save

- [ ] **Module management requires capability**
  - Test: Try enabling/disabling module without permission
  - Expected: Permission denied
  - Check: Module AJAX handlers

### 2.3 AJAX Permission Checks (🔴 Critical)

- [ ] **All AJAX handlers check capabilities**
  - Test: Send AJAX as non-admin with valid nonce
  - Expected: Permission error
  - Review: All `wp_ajax_*` action callbacks

- [ ] **Combined validation works correctly**
  - Test: `Security::validate_ajax_or_die()` usage
  - Verify: Checks both nonce AND capability
  - Coverage: All AJAX endpoints

---

## 🧹 **3. Input Sanitization Testing**

### 3.1 Text Input (🔴 Critical)

- [ ] **All $_POST data sanitized**
  - Test: Submit form with `<script>alert('XSS')</script>`
  - Expected: Tags stripped, text-only stored
  - Verify: `Security::sanitize_input($data, 'text')` used

- [ ] **All $_GET data sanitized**
  - Test: Add `?search=<script>` to URL
  - Expected: Script tags removed
  - Check: Search functionality, filters

### 3.2 Special Input Types (🔴 Critical)

- [ ] **Email validation**
  - Test: Submit invalid email `user@invalid`
  - Expected: Sanitized to empty or error shown
  - Code: `sanitize_input($email, 'email')`

- [ ] **URL validation**
  - Test: Submit `javascript:alert('XSS')` as URL
  - Expected: Invalid URL rejected
  - Code: `sanitize_input($url, 'url')`

- [ ] **Integer validation**
  - Test: Submit `abc` for ID field
  - Expected: Converted to 0 or rejected
  - Code: `sanitize_input($id, 'int')`

- [ ] **Boolean validation**
  - Test: Submit `'false'` string for checkbox
  - Expected: Converted to actual boolean
  - Code: `sanitize_input($bool, 'bool')`

### 3.3 Array Sanitization (🟡 Important)

- [ ] **Array inputs sanitized recursively**
  - Test: Submit nested array with scripts
  - Expected: All levels sanitized
  - Code: `Security::sanitize_array()`

- [ ] **JSON inputs validated**
  - Test: Submit invalid JSON
  - Expected: Validation fails gracefully
  - Code: `sanitize_input($json, 'json')`

### 3.4 HTML Content (🟡 Important)

- [ ] **HTML sanitized with allowed tags**
  - Test: Submit `<script>` in rich editor
  - Expected: Script removed, safe HTML kept
  - Code: `sanitize_input($html, 'html')`

- [ ] **Verify wp_kses_post() usage**
  - Test: Review rich text processing
  - Verify: Only safe tags allowed (p, a, img, etc.)

---

## 🔓 **4. Output Escaping Testing**

### 4.1 HTML Context (🔴 Critical)

- [ ] **All dynamic content escaped**
  - Test: Store `<script>alert('XSS')</script>` in database
  - Display: Should show as text, not execute
  - Code: `Security::escape_output($var, 'html')`

- [ ] **Variable inspection**
  - Review: All `echo` statements with variables
  - Verify: Each uses `escape_output()` or `esc_html()`

### 4.2 Attribute Context (🔴 Critical)

- [ ] **HTML attributes escaped**
  - Test: Store `" onclick="alert('XSS')` in setting
  - Display: `<div class="<?php echo $class; ?>">`
  - Expected: Quotes escaped, no execution
  - Code: `Security::escape_output($attr, 'attr')`

- [ ] **Common attributes checked**
  - Review: class, id, data-*, title attributes
  - Verify: Using `esc_attr()` or `escape_output(..., 'attr')`

### 4.3 URL Context (🔴 Critical)

- [ ] **URLs escaped before output**
  - Test: Store `javascript:alert()` URL
  - Display: In href or src attribute
  - Expected: Dangerous protocol removed
  - Code: `Security::escape_output($url, 'url')`

- [ ] **Verify all links**
  - Review: `<a href="">`, `<img src="">`
  - Verify: Using `esc_url()` or `escape_output(..., 'url')`

### 4.4 JavaScript Context (🟡 Important)

- [ ] **JS strings escaped**
  - Test: Variable passed to inline JavaScript
  - Code: `<script>var x = '<?php echo $val; ?>';</script>`
  - Expected: Quotes escaped properly
  - Fix: Use `Security::escape_output($val, 'js')`

- [ ] **JSON data escaped**
  - Test: Pass PHP array to JavaScript
  - Code: Use `wp_json_encode()` or `escape_output(..., 'json')`
  - Verify: Valid JSON, no XSS

---

## 🌐 **5. Database Security Testing**

### 5.1 SQL Injection Prevention (🔴 Critical)

- [ ] **All queries use $wpdb->prepare()**
  - Review: All custom SQL queries
  - Pattern: `$wpdb->prepare("SELECT * WHERE id = %d", $id)`
  - Never: Direct variable interpolation in SQL

- [ ] **Test injection attempts**
  - Test: Submit `1 OR 1=1` in ID field
  - Expected: Treated as literal string/number
  - Verify: No unauthorized data access

- [ ] **LIKE queries sanitized**
  - Test: Search with `%` or `_` characters
  - Code: Use `Security::sanitize_like_query()`
  - Expected: Special chars escaped

### 5.2 Database Helper Usage (🟡 Important)

- [ ] **DatabaseHelper methods used**
  - Review: Database operations
  - Verify: Using `DatabaseHelper::insert()`, `update()`, etc.
  - Benefit: Automatic data sanitization

- [ ] **Data types enforced**
  - Test: Insert string where int expected
  - Expected: Type validation in helper
  - Check: `%d`, `%s`, `%f` usage

---

## 🔌 **6. AJAX Security Testing**

### 6.1 Request Validation (🔴 Critical)

- [ ] **validate_ajax_or_die() used**
  - Review: All AJAX handler functions
  - Pattern: First line checks nonce + capability
  - Code: `Security::validate_ajax_or_die('action', 'capability')`

- [ ] **Test unauthorized AJAX**
  - Test: Call AJAX endpoint while logged out
  - Expected: Error response
  - Test: Call as non-admin user
  - Expected: Permission denied

### 6.2 Response Security (🟡 Important)

- [ ] **Responses use wp_send_json_***
  - Review: AJAX return statements
  - Use: `wp_send_json_success()` or `wp_send_json_error()`
  - Avoid: Custom JSON encoding

- [ ] **Error messages don't leak data**
  - Test: Trigger AJAX error
  - Verify: Generic message, no system details
  - Bad: "MySQL error on line 123"
  - Good: "Failed to save data"

---

## 📁 **7. File Upload Security Testing**

### 7.1 File Validation (🔴 Critical)

- [ ] **File type restriction**
  - Test: Upload PHP file to image upload
  - Expected: Upload rejected
  - Code: `Security::validate_file_upload()` with allowed types

- [ ] **File size limits**
  - Test: Upload file exceeding limit
  - Expected: Upload rejected with message
  - Verify: Size check in validation

- [ ] **MIME type verification**
  - Test: Rename `.php.jpg` file
  - Expected: Real MIME checked, not extension
  - Code: Uses `wp_check_filetype()`

### 7.2 Upload Processing (🟡 Important)

- [ ] **Uploaded files moved safely**
  - Review: File upload handling
  - Use: `wp_handle_upload()` WordPress function
  - Verify: Files go to `wp-content/uploads`

- [ ] **Filenames sanitized**
  - Test: Upload `../../etc/passwd.txt`
  - Expected: Path traversal prevented
  - Code: `sanitize_file_name()` used

---

## 🌍 **8. URL & Redirect Security Testing**

### 8.1 URL Validation (🔴 Critical)

- [ ] **External URLs validated**
  - Test: Submit malicious URL
  - Code: `Security::is_safe_url($url)`
  - Pattern: Only http/https allowed

- [ ] **Redirect validation**
  - Test: `?redirect_to=http://evil.com`
  - Expected: Redirect blocked or validated
  - Code: Use `wp_safe_redirect()`

### 8.2 Open Redirect Prevention (🔴 Critical)

- [ ] **No unvalidated redirects**
  - Review: All `wp_redirect()` calls
  - Verify: URL is internal or validated
  - Use: `wp_safe_redirect()` for user input

---

## 🛡️ **9. Additional Security Testing**

### 9.1 Rate Limiting (🟡 Important)

- [ ] **Rate limits on sensitive actions**
  - Test: Submit login form 20 times rapidly
  - Expected: Blocked after threshold
  - Code: `Security::check_rate_limit()`

- [ ] **Rate limit bypass attempts**
  - Test: Change IP (if possible)
  - Verify: Limits tracked per user/IP combo

### 9.2 CSRF Protection (🔴 Critical)

- [ ] **All state-changing operations protected**
  - Review: POST, DELETE, PUT operations
  - Verify: Nonce required for each
  - Test: CSRF attack simulation

- [ ] **prevent_csrf() on critical actions**
  - Check: High-risk operations
  - Code: `Security::prevent_csrf()` usage
  - Example: User deletion, data export

### 9.3 IP Privacy (🟢 Recommended)

- [ ] **IP addresses anonymized**
  - Review: IP logging code
  - Code: `Security::anonymize_ip()`
  - Compliance: GDPR requirement

- [ ] **get_user_ip() handles proxies**
  - Test: Access behind proxy
  - Verify: Real IP detected, not proxy IP

### 9.4 Security Logging (🟢 Recommended)

- [ ] **Security events logged**
  - Review: `Security::log_security_event()` calls
  - Check: Failed login, permission denials
  - Verify: Only in WP_DEBUG mode

---

## 📱 **10. Frontend Security Testing**

### 10.1 Public Forms (🔴 Critical)

- [ ] **Public-facing forms have nonces**
  - Check: Contact forms, search forms
  - Verify: Honeypot or CAPTCHA if needed

- [ ] **Public AJAX endpoints secure**
  - Review: `wp_ajax_nopriv_*` handlers
  - Verify: Rate limiting applied
  - Test: Spam submission attempts

### 10.2 Content Security (🟡 Important)

- [ ] **User-generated content sanitized**
  - Test: Comments, reviews (if applicable)
  - Verify: XSS prevention in UGC

---

## 🔍 **11. Code Review Checklist**

### 11.1 Security Code Patterns (🔴 Critical)

- [ ] **No eval() usage**
  - Search: `eval(`, `create_function(`
  - Expected: Zero occurrences

- [ ] **No direct file includes of user input**
  - Search: `include $_GET`, `require $_POST`
  - Expected: Zero occurrences

- [ ] **No unserialize() of untrusted data**
  - Search: `unserialize($_`
  - Expected: Zero occurrences or validated

- [ ] **No system execution functions**
  - Search: `exec(`, `shell_exec(`, `system(`
  - Expected: Zero occurrences or justified

### 11.2 WordPress Security Functions (🟡 Important)

- [ ] **Using WordPress functions**
  - Prefer: `wp_remote_get()` over `file_get_contents(url)`
  - Prefer: `wp_json_encode()` over `json_encode()`
  - Prefer: `wp_safe_redirect()` over `header('Location:')`

---

## ✅ **Testing Documentation**

### Test Results Template

```markdown
## Security Test Results - [Feature Name]

**Tested By**: [Name]
**Date**: [YYYY-MM-DD]
**Plugin Version**: 1.0.0

### Test Summary
- Total Tests: X
- Passed: X
- Failed: X
- Skipped: X

### Critical Issues Found
1. [Issue description]
   - Severity: Critical/Important/Recommended
   - Location: [File:Line]
   - Fix: [What needs to be done]

### Test Details
- [x] Nonce protection: PASS
- [ ] Capability checks: FAIL - Missing check on line 123
- [x] Input sanitization: PASS
...

### Recommendations
1. [Recommendation]
2. [Recommendation]
```

---

## 🎯 **Security Testing Schedule**

| When to Test | What to Test |
|--------------|--------------|
| **Before each commit** | Changed files only (quick scan) |
| **Before PR/merge** | All affected security areas |
| **Before release** | Full security checklist |
| **Monthly** | Full security audit |
| **After security update** | Related security areas |

---

## 📚 **Testing Tools**

### Recommended Tools

1. **WordPress Plugin Check** - Official WordPress.org plugin scanner
2. **WPScan** - WordPress security scanner
3. **PHP CodeSniffer** - WordPress coding standards
4. **PHPStan** - Static analysis tool
5. **Browser DevTools** - Network tab for AJAX testing
6. **Postman** - API/AJAX endpoint testing

### Manual Testing Browser Extensions

- **EditThisCookie** - Test with different user sessions
- **ModHeader** - Test with modified headers
- **User-Agent Switcher** - Test different user agents

---

## 🚨 **Common Vulnerabilities to Test For**

### OWASP Top 10 (WordPress Context)

1. ✅ **Injection** - SQL, XSS, Command injection
2. ✅ **Broken Authentication** - Session management
3. ✅ **Sensitive Data Exposure** - Data encryption, privacy
4. ✅ **XML External Entities** - N/A (no XML processing)
5. ✅ **Broken Access Control** - Capability checks
6. ✅ **Security Misconfiguration** - Default settings
7. ✅ **XSS** - Output escaping
8. ✅ **Insecure Deserialization** - Unserialize checks
9. ✅ **Using Components with Known Vulnerabilities** - Dependency check
10. ✅ **Insufficient Logging & Monitoring** - Security event logging

---

## 📞 **Security Issue Reporting**

If security issues are found:

1. **Do NOT commit** security vulnerabilities
2. **Document the issue** privately
3. **Fix immediately** before proceeding
4. **Re-test** after fix
5. **Document the fix** in changelog

---

## 📖 **References**

- [WordPress Security White Paper](https://wordpress.org/about/security/)
- [Plugin Security Best Practices](https://developer.wordpress.org/plugins/security/)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Data Validation - WordPress](https://developer.wordpress.org/apis/security/data-validation/)
- [Sanitizing Data - WordPress](https://developer.wordpress.org/apis/security/sanitizing/)
- [Escaping Data - WordPress](https://developer.wordpress.org/apis/security/escaping/)

---

**Checklist Version**: 1.0.0  
**Last Updated**: December 13, 2025  
**Maintained By**: ShahiTemplate Security Team  
**Next Review**: Before Phase 2 implementation
