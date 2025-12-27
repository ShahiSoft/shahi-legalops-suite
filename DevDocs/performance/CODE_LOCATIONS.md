# EXACT CODE LOCATIONS & LINE NUMBERS

## Quick Map for Code Changes

---

## FILE 1: `includes/Core/Assets.php`

### Location 1.1: Add Import (if needed)
**Line:** Top of file (already has namespace)
**Current:** `namespace ShahiLegalopsSuite\Core;`
**No change needed** - namespace already exists

---

### Location 1.2: Add Helper Methods
**Location:** After `__construct()` method
**Current line:** ~60 (after `$this->assets_url = ...`)
**Action:** INSERT 3 new methods before `enqueue_admin_styles()`

**Method 1 - get_current_page_type() [35 lines]**
```
Place after line 60
Before line 75 (before enqueue_admin_styles definition)
```

**Method 2 - needs_component_library() [10 lines]**  
```
Place after Method 1
```

**Method 3 - should_load_onboarding() [6 lines]**
```
Place after Method 2
```

---

### Location 1.3: Edit enqueue_admin_styles()
**Current location:** Lines 75-180
**Action:** Modify the method body

**Before (Current):**
```php
public function enqueue_admin_styles($hook) {
    // Line 87: $this->enqueue_style('shahi-admin-global', ...);
    // Line 95: $this->enqueue_style('shahi-components', ...);
    // Line 103: $this->enqueue_style('shahi-animations', ...);
    // Line 110: $this->enqueue_style('shahi-utilities', ...);
    // Line 118: $this->enqueue_style('shahi-onboarding', ...);
    // ... then page-specific styles
}
```

**Changes needed:**
1. Line 82: Add `$page_type = $this->get_current_page_type($hook);`
2. Line 95: Wrap components in `if ($this->needs_component_library($page_type)) {`
3. Line 103-114: Keep inside that if block (animations + utilities)
4. Line 118: Wrap onboarding in `if ($this->should_load_onboarding()) {`
5. Add closing braces appropriately

---

### Location 1.4: Edit enqueue_admin_scripts()
**Current location:** Lines 330-500
**Action:** Modify the method body similarly

**Add after line 340:**
```php
$page_type = $this->get_current_page_type($hook);
```

**Wrap components script (lines 345-355):**
```php
if ($this->needs_component_library($page_type)) {
    // enqueue shahi-components here
}
```

**Wrap onboarding script (lines 357-368):**
```php
if ($this->should_load_onboarding()) {
    // enqueue shahi-onboarding here
}
```

---

## FILE 2: `includes/Database/QueryOptimizer.php` (NEW FILE)

### Create New File
**Location:** `includes/Database/QueryOptimizer.php`
**Size:** ~400 lines
**Action:** Create entire new file (provided in IMPLEMENTATION_CHECKLIST.md)

**Key methods:**
1. `get_period_stats_cached()` - 60 lines
2. `get_event_types_cached()` - 50 lines
3. `get_top_pages_cached()` - 50 lines
4. `clear_cache()` - 15 lines

---

## FILE 3: `includes/Admin/AnalyticsDashboard.php`

### Location 3.1: Add Import
**Location:** Top of file, after `namespace ShahiLegalopsSuite\Admin;`
**Line:** ~15
**Add:**
```php
use ShahiLegalopsSuite\Database\QueryOptimizer;
```

### Location 3.2: Modify get_key_performance_indicators()
**Current location:** Lines 160-220
**Action:** Replace 2 method calls

**Find line ~165:**
```php
$current_stats = $this->get_period_stats($date_range['start'], $date_range['end']);
```
**Replace with:**
```php
$current_stats = QueryOptimizer::get_period_stats_cached($date_range['start'], $date_range['end'], 3600);
```

**Find line ~171:**
```php
$previous_stats = $this->get_period_stats($prev_start, $prev_end);
```
**Replace with:**
```php
$previous_stats = QueryOptimizer::get_period_stats_cached($prev_start, $prev_end, 3600);
```

### Location 3.3: Modify get_event_types_data()
**Current location:** ~380-390
**Find:**
```php
private function get_event_types_data($date_range) {
    return [
        // hardcoded array...
```

**Replace entire method with:**
```php
private function get_event_types_data($date_range) {
    return QueryOptimizer::get_event_types_cached($date_range['start'], $date_range['end'], 3600);
}
```

### Location 3.4: Modify get_top_pages()
**Current location:** ~400-420
**Find:**
```php
private function get_top_pages($date_range, $limit = 10) {
    $pages = [
        // hardcoded array...
```

**Replace entire method with:**
```php
private function get_top_pages($date_range, $limit = 10) {
    return QueryOptimizer::get_top_pages_cached($date_range['start'], $date_range['end'], $limit, 3600);
}
```

---

## FILE 4: `includes/Core/Activator.php`

### Location 4.1: Add Index Creation Method
**Location:** After `activate()` method, add new private method
**Line:** After `activate()` definition (line ~40-60)

**Add new method:**
```php
/**
 * Add performance indexes to analytics tables
 * 
 * @since 1.0.0
 * @return void
 */
private static function add_analytics_indexes() {
    global $wpdb;
    
    $events_table = $wpdb->prefix . 'shahi_analytics_events';
    $analytics_table = $wpdb->prefix . 'shahi_analytics';
    
    // Check if tables exist before adding indexes
    if ($wpdb->get_var("SHOW TABLES LIKE '$events_table'") === $events_table) {
        $wpdb->query("ALTER TABLE $events_table ADD INDEX IF NOT EXISTS idx_event_time (event_time)");
        $wpdb->query("ALTER TABLE $events_table ADD INDEX IF NOT EXISTS idx_user_id (user_id)");
        $wpdb->query("ALTER TABLE $events_table ADD INDEX IF NOT EXISTS idx_event_type (event_type)");
        $wpdb->query("ALTER TABLE $events_table ADD INDEX IF NOT EXISTS idx_event_type_time (event_type, event_time)");
    }
    
    if ($wpdb->get_var("SHOW TABLES LIKE '$analytics_table'") === $analytics_table) {
        $wpdb->query("ALTER TABLE $analytics_table ADD INDEX IF NOT EXISTS idx_created_at (created_at)");
    }
}
```

### Location 4.2: Call Index Method in activate()
**Find in `activate()` method:** The end of the method
**Add before closing brace:**
```php
self::add_analytics_indexes();
```

---

## SUMMARY TABLE

| File | Lines | Type | Difficulty |
|------|-------|------|-----------|
| Assets.php | 50-70 | Add methods + edit | Medium |
| QueryOptimizer.php | 1-400 | New file | High |
| AnalyticsDashboard.php | 4 method changes | Edit method calls | Low |
| Activator.php | +20 | Add method + call | Low |

**Total lines of code changes:** ~150 lines (mostly new methods)

---

## VISUAL STRUCTURE

```
includes/
├── Core/
│   ├── Assets.php                  ← EDIT (add methods + conditionals)
│   └── Activator.php              ← EDIT (add index creation)
├── Admin/
│   └── AnalyticsDashboard.php     ← EDIT (4 method call changes)
└── Database/
    └── QueryOptimizer.php          ← CREATE (new file)
```

---

## STEP-BY-STEP EDIT SEQUENCE

### Step 1: Create QueryOptimizer.php (5 min)
1. Create new file: `includes/Database/QueryOptimizer.php`
2. Paste entire QueryOptimizer class code
3. Verify syntax (no errors in IDE)

### Step 2: Add indexes in Activator.php (5 min)
1. Open `includes/Core/Activator.php`
2. Find `activate()` method
3. Add `add_analytics_indexes()` method
4. Call `self::add_analytics_indexes();` in `activate()`

### Step 3: Add helpers in Assets.php (10 min)
1. Open `includes/Core/Assets.php`
2. Find line ~60 (after `$this->assets_url = ...`)
3. Add 3 helper methods before `enqueue_admin_styles()`

### Step 4: Edit enqueue_admin_styles() (10 min)
1. Add `$page_type = $this->get_current_page_type($hook);` at line 82
2. Wrap components styles with conditional
3. Wrap onboarding styles with conditional

### Step 5: Edit enqueue_admin_scripts() (10 min)
1. Add `$page_type = $this->get_current_page_type($hook);` at line 340
2. Wrap components scripts with conditional
3. Wrap onboarding scripts with conditional

### Step 6: Update AnalyticsDashboard.php (10 min)
1. Add import: `use ShahiLegalopsSuite\Database\QueryOptimizer;`
2. Replace 4 method calls with QueryOptimizer wrapper calls
3. Replace `get_event_types_data()` method body
4. Replace `get_top_pages()` method body

### Step 7: Test & Verify (30 min)
1. Activate plugin
2. Visit each admin page
3. Check browser console (no errors)
4. Check WordPress debug log (no errors)
5. Measure page load times

---

## VERIFICATION CHECKLIST

After each edit, verify:
- ✅ PHP syntax is correct (IDE shows no errors)
- ✅ File is saved
- ✅ No merge conflicts if using version control
- ✅ File paths are absolute (use `SHAHI_LEGALOPS_SUITE_PATH`)
- ✅ Namespaces are correct
- ✅ Method visibility is correct (public/private)

---

## QUICK REFERENCE BY ISSUE

### For "Assets Loading Everywhere" Issue
→ Edit `includes/Core/Assets.php`
→ Locations 1.2, 1.3, 1.4
→ Add page type helpers + conditionals

### For "Analytics Queries Slow" Issue  
→ Create `includes/Database/QueryOptimizer.php`
→ Edit `includes/Admin/AnalyticsDashboard.php` (Location 3)
→ Edit `includes/Core/Activator.php` (Location 4)
→ Add caching layer + indexes

---

**Ready to edit? Use IMPLEMENTATION_CHECKLIST.md for exact code to copy/paste.**
