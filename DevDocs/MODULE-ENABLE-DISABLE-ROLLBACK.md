# Module Enable/Disable Enforcement - Rollback Plan

## Version Information
- **Feature Version**: 2.0.2
- **Implementation Date**: 2025-12-19
- **Files Modified**: 5

## Modified Files
1. `includes/Modules/Consent/controllers/ConsentAdminController.php`
2. `includes/Modules/AccessibilityScanner/AccessibilityScanner.php`
3. `includes/Modules/AccessibilityScanner/Admin/AccessibilitySettings.php`
4. `includes/Modules/AccessibilityScanner/Widget/AccessibilityWidget.php`
5. `templates/admin/module-dashboard.php`

## Quick Rollback Instructions

### Option 1: Git Revert (Recommended if committed)
```bash
# If this work was committed in a single commit
git log --oneline -10
# Find the commit hash for "Module enable/disable enforcement"
git revert <commit-hash>

# If spread across multiple commits
git revert <hash1> <hash2> <hash3>
```

### Option 2: Manual File Restoration
If you have backups or need to restore specific files:

```bash
# Restore from git (if not yet committed)
git checkout HEAD -- includes/Modules/Consent/controllers/ConsentAdminController.php
git checkout HEAD -- includes/Modules/AccessibilityScanner/AccessibilityScanner.php
git checkout HEAD -- includes/Modules/AccessibilityScanner/Admin/AccessibilitySettings.php
git checkout HEAD -- includes/Modules/AccessibilityScanner/Widget/AccessibilityWidget.php
git checkout HEAD -- templates/admin/module-dashboard.php
```

### Option 3: Specific Change Reversions

#### Revert ConsentAdminController.php
Remove the `is_enabled()` check in `register_admin_page()` and `render_admin_page()`:

**In register_admin_page() method:**
```php
// REMOVE THIS BLOCK:
if (!$this->module->is_enabled()) {
    return;
}
```

**In render_admin_page() method:**
```php
// REMOVE THIS BLOCK:
if (!$this->module->is_enabled()) {
    wp_die(
        __('This module is currently disabled. Please enable it from the Module Dashboard.', 'shahi-legalops-suite'),
        __('Module Disabled', 'shahi-legalops-suite'),
        ['back_link' => true]
    );
}
```

#### Revert AccessibilityScanner.php
Remove the `is_enabled()` check in `register_admin_menus()`:

```php
// REMOVE THIS BLOCK:
if (!$this->is_enabled()) {
    return;
}
```

#### Revert AccessibilitySettings.php
Remove the `is_enabled()` check in `render()` method:

```php
// REMOVE THIS BLOCK:
$module = ModuleManager::get_instance()->get_module('accessibility-scanner');
if (!$module || !$module->is_enabled()) {
    wp_die(
        __('The Accessibility Scanner module is currently disabled. Please enable it from the Module Dashboard.', 'shahi-legalops-suite'),
        __('Module Disabled', 'shahi-legalops-suite'),
        ['back_link' => true]
    );
}
```

#### Revert AccessibilityWidget.php
Remove the `is_enabled()` checks in `enqueue_assets()` and `render_widget()`:

**In enqueue_assets():**
```php
// REMOVE THIS BLOCK:
$module = ModuleManager::get_instance()->get_module('accessibility-scanner');
if (!$module || !$module->is_enabled()) {
    return;
}
```

**In render_widget():**
```php
// REMOVE THIS BLOCK:
$module = ModuleManager::get_instance()->get_module('accessibility-scanner');
if (!$module || !$module->is_enabled()) {
    return;
}
```

#### Revert module-dashboard.php
Restore the original settings button code:

```php
<!-- REPLACE THIS: -->
<a href="<?php echo esc_url($module->get_settings_url()); ?>" 
   class="slos-btn slos-btn--outline slos-btn--sm"
   <?php echo !$is_enabled ? 'style="opacity: 0.5; cursor: not-allowed; pointer-events: none;" title="Enable module to access settings"' : ''; ?>>
    <i class="dashicons dashicons-admin-settings"></i>
    <?php esc_html_e('Settings', 'shahi-legalops-suite'); ?>
</a>

<!-- WITH THIS ORIGINAL: -->
<a href="<?php echo esc_url($module->get_settings_url()); ?>" class="slos-btn slos-btn--outline slos-btn--sm">
    <i class="dashicons dashicons-admin-settings"></i>
    <?php esc_html_e('Settings', 'shahi-legalops-suite'); ?>
</a>
```

## Testing After Rollback

1. **Clear WordPress cache**:
```bash
wp cache flush --path=/path/to/wordpress
```

2. **Verify admin menus show regardless of module state**:
   - Navigate to WordPress admin
   - Disable a module in Module Dashboard
   - Confirm menu items remain visible (old behavior)

3. **Verify settings pages are accessible**:
   - Disable a module
   - Navigate directly to its settings URL
   - Confirm page loads (old behavior)

4. **Verify frontend widgets load**:
   - Disable Accessibility Scanner module
   - Load a frontend page
   - Confirm accessibility widget still appears (old behavior)

## Known Issues After Rollback
After reverting these changes, the following behaviors will return:

1. ❌ Admin menu items will show even when modules are disabled
2. ❌ Settings pages will be accessible via direct URL when disabled
3. ❌ Frontend widgets will load regardless of module state
4. ❌ Settings buttons in Module Dashboard will always be clickable

These are the original issues that this implementation fixed.

## Prevention for Future
- Always commit module enable/disable enforcement changes separately
- Tag releases before major structural changes
- Maintain automated tests for module state enforcement
- Document all module-related state checks in code comments

## Support Contacts
- Developer: [Your contact]
- Git Repository: [Repository URL]
- Issue Tracker: [Issue tracker URL]

## Validation Checklist
After rollback, verify:
- [ ] No PHP errors in WordPress debug.log
- [ ] Admin dashboard loads correctly
- [ ] Module Dashboard shows all 4 modules
- [ ] Toggle switches still work
- [ ] Settings pages load (even when disabled)
- [ ] Frontend pages render without errors
- [ ] No JavaScript console errors
