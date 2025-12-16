# ShahiTemplate - Security Documentation

**Version**: 1.0.0  
**Last Updated**: December 13, 2025  
**Security Class**: `ShahiTemplate\Core\Security`

---

## 🔒 **Overview**

The Security class provides comprehensive security utilities to protect the plugin against common web vulnerabilities including:

- **CSRF Attacks** - Cross-Site Request Forgery protection via nonces
- **XSS Attacks** - Cross-Site Scripting prevention via output escaping
- **SQL Injection** - Database query protection via prepared statements
- **Unauthorized Access** - Capability-based permission system
- **File Upload Attacks** - File validation and MIME type checking
- **Rate Limiting** - Brute force attack prevention
- **Privacy Protection** - IP anonymization for GDPR compliance

---

## 📚 **Table of Contents**

1. [Nonce Functions](#nonce-functions)
2. [Capability Functions](#capability-functions)
3. [Sanitization Functions](#sanitization-functions)
4. [Escaping Functions](#escaping-functions)
5. [AJAX Validation](#ajax-validation)
6. [URL Security](#url-security)
7. [File Upload Validation](#file-upload-validation)
8. [IP Address Handling](#ip-address-handling)
9. [Rate Limiting](#rate-limiting)
10. [Security Best Practices](#security-best-practices)

---

## 🔐 **Nonce Functions**

### What is a Nonce?

A nonce (Number used ONCE) is a security token that protects against CSRF attacks by ensuring requests originate from authenticated users.

### Generate Nonce

```php
use ShahiTemplate\Core\Security;

// Generate nonce for an action
$nonce = Security::generate_nonce('save_settings');
```

### Verify Nonce

```php
// Verify nonce
$nonce = $_POST['_wpnonce'];
if (Security::verify_nonce($nonce, 'save_settings')) {
    // Nonce is valid, proceed
}

// Or verify and die if invalid
Security::verify_nonce_or_die($_POST['_wpnonce'], 'save_settings');
```

### Nonce Field (Forms)

```php
// In your form
<form method="post">
    <?php Security::nonce_field('save_settings'); ?>
    <input type="text" name="setting_name" />
    <button type="submit">Save</button>
</form>

// Processing the form
if (isset($_POST['submit'])) {
    Security::verify_nonce_or_die($_POST['_wpnonce'], 'save_settings');
    // Process form...
}
```

### Nonce URL (Links)

```php
// Add nonce to URL
$url = admin_url('admin.php?page=shahi-template&action=delete&id=123');
$secure_url = Security::nonce_url($url, 'delete_item');

// In template
<a href="<?php echo esc_url($secure_url); ?>">Delete</a>

// Verifying the URL
if (isset($_GET['_wpnonce'])) {
    Security::verify_nonce_or_die($_GET['_wpnonce'], 'delete_item');
    // Process deletion...
}
```

---

## 👤 **Capability Functions**

### Check Capability

```php
// Check if user has capability
if (Security::check_capability('manage_options')) {
    // User is admin
}

// Check and die if unauthorized
Security::check_capability_or_die('manage_options');

// Check if user can manage plugin
if (Security::can_manage_plugin()) {
    // User has plugin management access
}
```

### Common Capabilities

- `manage_options` - Administrator
- `edit_posts` - Editor, Author, Contributor
- `edit_published_posts` - Editor, Author
- `publish_posts` - Editor, Author
- `edit_pages` - Editor
- `read` - All logged-in users

### Custom Capabilities (Future)

```php
// Will be added in Phase 6
'manage_shahi_template'
'view_shahi_analytics'
'manage_shahi_modules'
'edit_shahi_settings'
```

---

## 🧹 **Sanitization Functions**

### Sanitize Input

```php
use ShahiTemplate\Core\Security;

// Text field
$name = Security::sanitize_input($_POST['name'], 'text');

// Email
$email = Security::sanitize_input($_POST['email'], 'email');

// URL
$website = Security::sanitize_input($_POST['website'], 'url');

// Integer
$id = Security::sanitize_input($_POST['id'], 'int');

// Float
$price = Security::sanitize_input($_POST['price'], 'float');

// Boolean
$enabled = Security::sanitize_input($_POST['enabled'], 'bool');

// Textarea
$description = Security::sanitize_input($_POST['description'], 'textarea');

// HTML (allows safe HTML tags)
$content = Security::sanitize_input($_POST['content'], 'html');

// Slug/Key
$slug = Security::sanitize_input($_POST['slug'], 'slug');

// Filename
$filename = Security::sanitize_input($_FILES['file']['name'], 'filename');
```

### Sanitize Array

```php
// Sanitize entire array
$settings = Security::sanitize_array($_POST['settings'], 'text');

// Example: $_POST['settings'] = ['name' => 'John', 'age' => '30']
// Result: ['name' => 'John', 'age' => '30'] (sanitized)

// Nested arrays supported
$data = Security::sanitize_array($_POST['data'], 'text');
```

### Available Sanitization Types

| Type | Function | Use Case |
|------|----------|----------|
| `text` | `sanitize_text_field()` | Simple text input |
| `textarea` | `sanitize_textarea_field()` | Multi-line text |
| `email` | `sanitize_email()` | Email addresses |
| `url` | `esc_url_raw()` | URLs |
| `int` | `absint()` | Positive integers |
| `float` | `floatval()` | Decimal numbers |
| `bool` | `(bool)` | Boolean values |
| `slug` | `sanitize_key()` | Slugs, keys |
| `title` | `sanitize_title()` | Post titles |
| `filename` | `sanitize_file_name()` | File names |
| `html` | `wp_kses_post()` | HTML content |
| `array` | Array mapping | Arrays |

---

## 🔓 **Escaping Functions**

### Escape Output

```php
use ShahiTemplate\Core\Security;

// HTML content
echo Security::escape_output($title, 'html');

// HTML attribute
echo '<div class="' . Security::escape_output($class, 'attr') . '">';

// URL
echo '<a href="' . Security::escape_output($url, 'url') . '">';

// JavaScript
echo '<script>var name = "' . Security::escape_output($name, 'js') . '";</script>';

// Textarea
echo '<textarea>' . Security::escape_output($content, 'textarea') . '</textarea>';

// JSON
echo '<script>var data = ' . Security::escape_output($data, 'json') . ';</script>';
```

### Escaping Contexts

| Context | Function | When to Use |
|---------|----------|-------------|
| `html` | `esc_html()` | HTML body content |
| `attr` | `esc_attr()` | HTML attributes |
| `url` | `esc_url()` | URLs in links |
| `js` | `esc_js()` | JavaScript strings |
| `textarea` | `esc_textarea()` | Textarea content |
| `json` | `wp_json_encode()` | JSON data |

---

## 🔌 **AJAX Validation**

### Validate AJAX Request

```php
// In your AJAX handler
add_action('wp_ajax_save_module', 'handle_save_module');

function handle_save_module() {
    // Validate request (nonce + capability + referer)
    Security::validate_ajax_or_die('save_module', 'manage_options');
    
    // Request is valid, proceed
    $module = Security::sanitize_input($_POST['module'], 'text');
    
    // ... save module
    
    wp_send_json_success(array('message' => 'Module saved'));
}
```

### AJAX Request from JavaScript

```javascript
jQuery.ajax({
    url: ajaxurl,
    type: 'POST',
    data: {
        action: 'save_module',
        nonce: shahiTemplate.nonce, // Generated server-side
        module: 'analytics'
    },
    success: function(response) {
        if (response.success) {
            console.log(response.data.message);
        }
    }
});
```

### Localize AJAX Data (PHP)

```php
wp_localize_script('shahi-admin-js', 'shahiTemplate', array(
    'ajaxurl' => admin_url('admin-ajax.php'),
    'nonce' => Security::generate_nonce('save_module'),
));
```

---

## 🌐 **URL Security**

### Validate URL

```php
// Check if URL is safe
if (Security::is_safe_url($url)) {
    // URL is safe
}

// Sanitize and validate
$clean_url = Security::sanitize_url($_POST['website']);
if ($clean_url !== false) {
    // URL is valid and sanitized
}
```

### URL Validation Rules

- Only http:// and https:// protocols allowed
- Validates URL format
- Prevents javascript:, data:, and other dangerous protocols
- Relative URLs are allowed

---

## 📁 **File Upload Validation**

### Validate Uploaded File

```php
// Define allowed types
$allowed_types = array(
    'image/jpeg',
    'image/png',
    'image/gif',
);

// Validate file
$result = Security::validate_file_upload(
    $_FILES['upload'],
    $allowed_types,
    2097152 // 2MB max
);

if ($result === true) {
    // File is valid, process upload
    $upload = wp_handle_upload($_FILES['upload'], array('test_form' => false));
} else {
    // Show error message
    echo esc_html($result);
}
```

### File Validation Checks

1. Upload error check
2. File size validation
3. MIME type verification
4. Prevents dangerous file types

---

## 🌍 **IP Address Handling**

### Get User IP

```php
// Get real IP address (handles proxies)
$ip = Security::get_user_ip();
```

### Anonymize IP (GDPR)

```php
// Anonymize for privacy
$anon_ip = Security::anonymize_ip('192.168.1.100');
// Result: '192.168.1.0'

$anon_ipv6 = Security::anonymize_ip('2001:db8::1');
// Result: '2001:db8::0'
```

---

## ⏱️ **Rate Limiting**

### Check Rate Limit

```php
// Allow 10 attempts per 60 seconds
if (!Security::check_rate_limit('login_attempt', 10, 60)) {
    wp_die('Too many attempts. Please try again later.');
}

// Different limits for different actions
if (!Security::check_rate_limit('api_request', 100, 3600)) {
    wp_send_json_error('Rate limit exceeded');
}
```

### Use Cases

- Login attempts
- API requests
- Form submissions
- Password reset requests
- Search queries

---

## 🛡️ **Additional Security Functions**

### Generate Secure Token

```php
// Generate random token (32 chars)
$token = Security::generate_token();

// Custom length
$token = Security::generate_token(64);
```

### Hash Data

```php
// Hash sensitive data
$hash = Security::hash_data($sensitive_data);

// Verify hash
if (Security::verify_hash($original_data, $stored_hash)) {
    // Data matches
}
```

### Sanitize LIKE Query

```php
global $wpdb;

$search = Security::sanitize_like_query($_GET['search']);
$results = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->posts} WHERE post_title LIKE %s",
    $search
));
```

### Check Plugin Page

```php
// Check if on any plugin page
if (Security::is_plugin_page()) {
    // On plugin admin page
}

// Check specific page
if (Security::is_plugin_page('shahi-settings')) {
    // On settings page
}
```

### Log Security Events

```php
// Log security event (only in WP_DEBUG mode)
Security::log_security_event('failed_login', array(
    'username' => $username,
    'ip' => Security::get_user_ip(),
));
```

---

## ✅ **Security Best Practices**

### 1. Always Sanitize Input

```php
// ❌ WRONG - No sanitization
$name = $_POST['name'];
update_option('user_name', $name);

// ✅ CORRECT - Sanitized
$name = Security::sanitize_input($_POST['name'], 'text');
update_option('user_name', $name);
```

### 2. Always Escape Output

```php
// ❌ WRONG - No escaping
echo '<h1>' . $title . '</h1>';

// ✅ CORRECT - Escaped
echo '<h1>' . Security::escape_output($title, 'html') . '</h1>';
```

### 3. Always Verify Nonces

```php
// ❌ WRONG - No nonce verification
if (isset($_POST['save'])) {
    update_option('setting', $_POST['value']);
}

// ✅ CORRECT - Nonce verified
if (isset($_POST['save'])) {
    Security::verify_nonce_or_die($_POST['_wpnonce'], 'save_settings');
    $value = Security::sanitize_input($_POST['value'], 'text');
    update_option('setting', $value);
}
```

### 4. Always Check Capabilities

```php
// ❌ WRONG - No permission check
delete_option('important_setting');

// ✅ CORRECT - Permission checked
Security::check_capability_or_die('manage_options');
delete_option('important_setting');
```

### 5. Use Prepared Statements

```php
global $wpdb;

// ❌ WRONG - SQL injection risk
$results = $wpdb->get_results(
    "SELECT * FROM {$wpdb->prefix}table WHERE id = {$_GET['id']}"
);

// ✅ CORRECT - Prepared statement
$id = Security::sanitize_input($_GET['id'], 'int');
$results = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}table WHERE id = %d",
    $id
));
```

### 6. Validate AJAX Properly

```php
// ❌ WRONG - No validation
add_action('wp_ajax_my_action', function() {
    update_option('setting', $_POST['value']);
});

// ✅ CORRECT - Full validation
add_action('wp_ajax_my_action', function() {
    Security::validate_ajax_or_die('my_action', 'manage_options');
    $value = Security::sanitize_input($_POST['value'], 'text');
    update_option('setting', $value);
    wp_send_json_success();
});
```

---

## 🔍 **Security Checklist**

Use this checklist for all new features:

- [ ] All user input sanitized using `Security::sanitize_input()`
- [ ] All output escaped using `Security::escape_output()`
- [ ] Nonces added to all forms
- [ ] Nonces verified before processing
- [ ] Capability checks on sensitive operations
- [ ] AJAX requests validated with `validate_ajax_or_die()`
- [ ] Database queries use `$wpdb->prepare()`
- [ ] File uploads validated
- [ ] URLs validated before redirect
- [ ] Rate limiting on sensitive endpoints
- [ ] Security events logged (if needed)

---

## 📖 **Common Security Patterns**

### Form Processing Pattern

```php
// Display form
function render_settings_form() {
    ?>
    <form method="post" action="">
        <?php Security::nonce_field('save_settings'); ?>
        <input type="text" name="site_title" value="<?php echo Security::escape_output(get_option('site_title'), 'attr'); ?>" />
        <button type="submit" name="save">Save</button>
    </form>
    <?php
}

// Process form
function process_settings_form() {
    if (!isset($_POST['save'])) {
        return;
    }
    
    Security::check_capability_or_die('manage_options');
    Security::verify_nonce_or_die($_POST['_wpnonce'], 'save_settings');
    
    $title = Security::sanitize_input($_POST['site_title'], 'text');
    update_option('site_title', $title);
    
    add_settings_error('general', 'settings_updated', 'Settings saved.', 'success');
}
```

### AJAX Handler Pattern

```php
// Register AJAX
add_action('wp_ajax_toggle_module', 'handle_toggle_module');

function handle_toggle_module() {
    // Validate request
    Security::validate_ajax_or_die('toggle_module', 'manage_options');
    
    // Sanitize input
    $module = Security::sanitize_input($_POST['module'], 'slug');
    $enabled = Security::sanitize_input($_POST['enabled'], 'bool');
    
    // Process
    $result = update_module_status($module, $enabled);
    
    // Send response
    if ($result) {
        wp_send_json_success(array(
            'message' => 'Module updated successfully.',
        ));
    } else {
        wp_send_json_error(array(
            'message' => 'Failed to update module.',
        ));
    }
}
```

### URL Action Pattern

```php
// Generate action link
$delete_url = add_query_arg(array(
    'action' => 'delete',
    'id' => $item_id,
), admin_url('admin.php?page=shahi-template'));

$secure_url = Security::nonce_url($delete_url, 'delete_item');

echo '<a href="' . esc_url($secure_url) . '">Delete</a>';

// Process action
function process_delete_action() {
    if (!isset($_GET['action']) || $_GET['action'] !== 'delete') {
        return;
    }
    
    Security::check_capability_or_die('manage_options');
    Security::verify_nonce_or_die($_GET['_wpnonce'], 'delete_item');
    
    $id = Security::sanitize_input($_GET['id'], 'int');
    delete_item($id);
    
    wp_redirect(admin_url('admin.php?page=shahi-template&deleted=1'));
    exit;
}
```

---

## 🎯 **Security Testing**

### Manual Testing

1. Try submitting forms without nonces
2. Try accessing admin pages without permissions
3. Try SQL injection in search fields
4. Try XSS in text inputs
5. Try uploading malicious files
6. Try exceeding rate limits

### Automated Testing

```php
// Unit test example (PHPUnit)
public function test_nonce_verification() {
    $nonce = Security::generate_nonce('test_action');
    $this->assertTrue(Security::verify_nonce($nonce, 'test_action'));
    $this->assertFalse(Security::verify_nonce('invalid', 'test_action'));
}
```

---

## 📝 **Conclusion**

The Security class provides comprehensive protection against common vulnerabilities. Always use these utilities instead of direct WordPress functions for consistency and enhanced security.

**Key Principles:**
1. **Sanitize** all input
2. **Escape** all output
3. **Verify** all nonces
4. **Check** all capabilities
5. **Prepare** all queries

---

**Document Version**: 1.0.0  
**Last Updated**: December 13, 2025  
**Maintained By**: ShahiTemplate Security Team
