# Onboarding Wizard Troubleshooting Guide

## Comprehensive Fixes Applied

### 1. **Enhanced Debugging & Logging**

#### PHP Error Logs
All critical checkpoints now log to `debug.log`:
- Option deletion attempts and results
- Cache clearing operations
- Direct database queries
- `should_show_onboarding()` decision flow
- `render_modal()` execution
- Current user capabilities

#### JavaScript Console Logs
Browser console now shows:
- Script loading confirmation
- Modal DOM element detection
- Initialization flow
- Element caching results
- AJAX request/response details

### 2. **Cache Busting Strategies**

#### Multiple Cache Layers Cleared
```php
// Object cache
wp_cache_delete('shahi_template_onboarding_completed', 'options');
wp_cache_delete('shahi_template_onboarding_data', 'options');
wp_cache_delete('alloptions', 'options');

// Browser cache busting on redirect
window.location.replace(url + '?_onboarding_reset=' + Date.now());
```

#### Fresh Data Reads
- Cache deleted before every `should_show_onboarding()` check
- Direct database deletion as fallback
- `alloptions` cache cleared (WordPress internal cache)

### 3. **Direct Database Operations**

```php
// Bypass WordPress options API entirely
global $wpdb;
$wpdb->query($wpdb->prepare(
    "DELETE FROM {$wpdb->options} WHERE option_name IN (%s, %s)",
    'shahi_template_onboarding_completed',
    'shahi_template_onboarding_data'
));
```

### 4. **Permission Alignment**

Restart endpoint now accepts both:
- `manage_shahi_template` (original requirement)
- `edit_shahi_settings` (settings page access)

### 5. **Visual Improvements**

- **Red Bold Header**: Onboarding Wizard label in Advanced tab
- **Danger Button**: Red-hued button for visibility and importance
- Both changes emphasize the critical nature of this action

## Debug Page

Access: **WP Admin → ShahiTemplate → 🔍 Debug Onboarding**  
(Only visible when `WP_DEBUG` is enabled)

### What It Shows:
1. **Database Options**: Current values with types
2. **Direct DB Query**: Bypasses WordPress caching
3. **User Capabilities**: All relevant permissions
4. **Onboarding Class Status**: Method execution results
5. **Current Page Info**: Screen ID and parameters
6. **Enqueued Scripts**: Script registration status
7. **Force Delete Button**: Nuclear option to clear everything

## Testing Steps

### Step 1: Check Logs
```bash
# Windows PowerShell
Get-Content "C:\path\to\wordpress\wp-content\debug.log" -Tail 50 | Select-String "SHAHI"
```

### Step 2: Use Debug Page
1. Enable `WP_DEBUG` in `wp-config.php`
2. Go to **ShahiTemplate → Debug Onboarding**
3. Review all status information
4. Use "Force Delete" button if needed
5. Click "Go to Dashboard" to test modal

### Step 3: Browser Console
1. Open DevTools (F12)
2. Go to **Settings → Advanced**
3. Click **Restart Onboarding Wizard**
4. Watch Network tab for AJAX response
5. Check Console for log messages
6. After redirect, confirm modal logs

## Common Issues & Solutions

### Issue: Modal Not Showing After Restart

**Check:**
```
✓ Option deleted? (debug.log: "SHAHI RESTART")
✓ Redirect happened? (console: "Redirecting to")
✓ render_modal() called? (debug.log: "render_modal() called")
✓ should_show_onboarding() passed? (debug.log: "All checks passed")
✓ DOM element exists? (console: "Modal element exists: true")
✓ Script initialized? (console: "init() called")
```

**Solutions:**
1. Check debug.log for the exact failure point
2. Use Debug Page to verify database state
3. Force delete via Debug Page
4. Clear browser cache (Ctrl+Shift+Delete)
5. Try incognito/private browsing window

### Issue: Permission Denied

**Error:** "Insufficient permissions"

**Solution:**
```php
// Verify user has capability
if (!current_user_can('manage_shahi_template')) {
    // User needs administrator role
}
```

Use Debug Page to check capabilities.

### Issue: AJAX Request Fails

**Check Network Tab:**
- Status code 200?
- Response JSON valid?
- Nonce verification passing?

**Common Causes:**
- `shahi_settings_vars` not localized
- Wrong AJAX URL
- Nonce mismatch
- Server timeout

### Issue: Persistent Cache

**Nuclear Option:**
```php
// Via Debug Page "Force Delete" button, OR:
delete_option('shahi_template_onboarding_completed');
delete_option('shahi_template_onboarding_data');
wp_cache_flush();

// If using Redis/Memcached:
// - Restart cache service
// - Flush cache manually
```

## Log Messages Reference

### Success Flow
```
SHAHI RESTART: Attempting to delete onboarding options
SHAHI RESTART: Before deletion - completed: ...
SHAHI RESTART: Delete results - completed: true, data: true
SHAHI RESTART: Direct database deletion executed
SHAHI RESTART: Cache cleared (including alloptions)
SHAHI RESTART: After deletion - completed option value: false

[After redirect]
SHAHI ONBOARDING: render_modal() called
SHAHI ONBOARDING: User has permission: true
SHAHI ONBOARDING: Option completed: false
SHAHI ONBOARDING: On plugin page: true
SHAHI ONBOARDING: All checks passed - SHOWING MODAL
SHAHI ONBOARDING: Including modal template
SHAHI ONBOARDING: Template script loaded
SHAHI ONBOARDING JS: jQuery document.ready fired
SHAHI ONBOARDING JS: Overlay found: true
SHAHI ONBOARDING JS: init() called
SHAHI ONBOARDING JS: Initialization complete
```

### Failure Indicators
```
"Already completed" = Cache not cleared
"Not on plugin page" = Wrong URL/page parameter
"User lacks capability" = Permission issue
"Overlay found: false" = render_modal() not executed
"ShahiOnboarding object NOT FOUND" = Script not loaded
```

## Files Modified

1. `includes/Admin/Onboarding.php`
   - Enhanced `should_show_onboarding()` with logging
   - Added cache clearing
   - Enhanced `render_modal()` with logging

2. `includes/Admin/Settings.php`
   - Direct database deletion
   - Multi-level cache clearing
   - Comprehensive logging

3. `assets/js/admin-settings.js`
   - AJAX URL fallback
   - Console logging
   - Cache-busting redirect

4. `assets/js/onboarding.js`
   - Initialization logging
   - Element detection logging

5. `templates/admin/onboarding-modal.php`
   - Template load logging
   - Script initialization logging

6. `templates/admin/settings.php`
   - Red bold header
   - Danger button styling

7. `includes/Admin/MenuManager.php`
   - Debug page menu item

8. `debug-onboarding.php` (NEW)
   - Comprehensive status page
   - Force delete option

## Next Steps

1. **Enable WP_DEBUG** in `wp-config.php`
2. **Access Debug Page** (ShahiTemplate → Debug Onboarding)
3. **Review Status** (all values should show option doesn't exist)
4. **Force Delete** if needed
5. **Go to Dashboard** and verify modal appears
6. **Check Console & Logs** for any errors

## Support

If issues persist after following this guide:
1. Export Debug Page information
2. Export last 100 lines of debug.log with "SHAHI" filter
3. Export browser console logs
4. Take screenshot of Network tab (admin-ajax.php request)
