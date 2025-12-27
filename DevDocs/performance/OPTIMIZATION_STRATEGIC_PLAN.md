# Strategic Performance Optimization Plan

## Phase 1: Asset Optimization (Conditional Loading)

### 1.1 - Analyze Current Asset Loading Patterns
**Files to audit:**
- `includes/Core/Assets.php` - Main asset manager
- `includes/Admin/Dashboard.php` - Dashboard page
- `includes/Admin/AnalyticsDashboard.php` - Analytics page
- `includes/Admin/Settings.php` - Settings page
- `includes/Admin/ModuleDashboard.php` - Module dashboard
- `includes/Admin/Modules.php` - Modules page

**Key info to extract:**
- Which page hooks correspond to which pages
- Which CSS/JS are truly required per page
- Which assets have interdependencies

### 1.2 - Create Asset Configuration Map
**Purpose:** Define exactly which assets load on each page type

**Asset Groups to Create:**
```
GLOBAL_REQUIRED = [admin-global] // Absolutely necessary
COMPONENT_LIBS = [components, animations, utilities] // UI lib, conditional
MODALS = [onboarding] // Load only if feature enabled
PAGE_SPECIFIC = [dashboard, analytics, settings, modules, etc.]
EXTERNAL = [chartjs for analytics only]
```

### 1.3 - Refactor Assets.php - Implement Page Detection
**Changes needed:**
1. Add method `get_page_type($hook)` - returns 'dashboard', 'analytics', 'settings', etc.
2. Add method `should_load_component_libs($page_type)` - check if page needs UI library
3. Add method `should_load_onboarding($page_type)` - check if onboarding enabled
4. Remove global enqueuing from `enqueue_admin_styles()` and `enqueue_admin_scripts()`
5. Add conditional checks before EACH asset enqueue

**Before (Current):**
```php
// Loads on EVERY page
$this->enqueue_style('shahi-components', ...);
$this->enqueue_style('shahi-animations', ...);
$this->enqueue_style('shahi-utilities', ...);
```

**After (Optimized):**
```php
if ($this->should_load_component_libs($page_type)) {
    $this->enqueue_style('shahi-components', ...);
    $this->enqueue_style('shahi-animations', ...);
    $this->enqueue_style('shahi-utilities', ...);
}
```

### 1.4 - Eliminate Redundant Inline Scripts
**Target:** The menu highlighting script that runs on every page
- Keep CSS for menu (non-blocking)
- Move JS to only pages that need it
- OR: Use CSS-only solution instead of jQuery

---

## Phase 2: Database Query Optimization

### 2.1 - Add Database Indexes (Schema Update)
**Location:** `includes/Database/` or migration file

**Indexes to add:**
```sql
ALTER TABLE wp_shahi_analytics_events ADD INDEX idx_event_time (event_time);
ALTER TABLE wp_shahi_analytics_events ADD INDEX idx_user_id (user_id);
ALTER TABLE wp_shahi_analytics_events ADD INDEX idx_event_type (event_type);
ALTER TABLE wp_shahi_analytics_events ADD INDEX idx_event_type_time (event_type, event_time);
ALTER TABLE wp_shahi_analytics ADD INDEX idx_created_at (created_at);
```

**Safety:** Make indexes non-blocking, test in staging first

### 2.2 - Implement Transient Caching Strategy
**Location:** Add to `AnalyticsDashboard.php`

**Transient Keys & Duration:**
```php
'shahi_kpis_' . date('Y-m-d') // 6 hours
'shahi_trend_data_' . $date_range_key // 4 hours
'shahi_top_pages_' . date('Y-m-d') // 2 hours
'shahi_top_events_' . date('Y-m-d') // 2 hours
'shahi_device_breakdown_' . date('Y-m-d') // 6 hours
'shahi_geographic_data_' . date('Y-m-d') // 6 hours
```

**Pattern:**
```php
$cache_key = 'shahi_kpis_' . date('Y-m-d H:00'); // 1-hour buckets
$data = get_transient($cache_key);
if (false === $data) {
    $data = $this->compute_kpis($date_range); // Expensive query
    set_transient($cache_key, $data, 3600); // 1 hour TTL
}
return $data;
```

### 2.3 - Refactor Heavy Query Methods
**Methods to optimize in `AnalyticsDashboard.php`:**
1. `get_key_performance_indicators()` - Replace with cached version
2. `get_trend_data()` - Use DB instead of mock, add LIMIT
3. `get_top_pages()` - Add LIMIT 10 to query
4. `get_top_events()` - Add LIMIT 10 to query
5. `get_user_segments()` - Add pagination
6. `get_geographic_data()` - Add pagination
7. `get_device_breakdown()` - Use cached GROUP BY

**Key principle:** Replace mock data with actual DB queries (with limits), then cache results

### 2.4 - Add Query Optimization Helpers
**Create:** `includes/Database/QueryOptimizer.php`

**Methods:**
```php
public function get_period_stats_cached($start, $end, $ttl = 3600)
public function get_event_counts_by_type_cached($start, $end)
public function get_top_pages_cached($start, $end, $limit = 10)
public function get_unique_users_cached($start, $end)
```

---

## Phase 3: Implementation Sequence

### Step 1: Database Indexes (Non-breaking, improves reads)
- Add migration or direct SQL execution in Activator
- Can be run without disabling plugin
- **Risk:** Very low - only improves performance

### Step 2: Asset Refactoring (Medium risk - requires testing)
- Create new methods in Assets.php for conditional loading
- Keep old enqueuing intact initially
- Switch pages one-by-one to new conditional loading
- Test each page thoroughly
- Remove old code after all pages migrated

### Step 3: Query Optimization (Medium risk - requires testing)
- Add transient caching wrapper around expensive methods
- Ensure cache invalidation on data changes
- Test analytics page with real data
- Monitor for stale data issues

### Step 4: Validation & Testing
- Load test each page
- Verify all interactive features work
- Check for JavaScript errors (browser console)
- Benchmark load times before/after

---

## Phase 4: Risk Mitigation

### 4.1 - Functionality Preservation Checklist
- [ ] Dashboard statistics display correctly
- [ ] Analytics dashboard loads without JS errors
- [ ] Settings page tab switching works
- [ ] Modules page functionality intact
- [ ] AJAX calls still work (nonce validation)
- [ ] Menu highlighting works
- [ ] Onboarding modal appears when needed
- [ ] Chart.js initializes on analytics pages
- [ ] All buttons/forms submit correctly

### 4.2 - Error Prevention
- [ ] Check PHP error log before/after each change
- [ ] Use `wp_enqueue_script` dependencies correctly
- [ ] Verify `wp_localize_script` data available for all scripts
- [ ] Test in SCRIPT_DEBUG mode to catch issues
- [ ] Use version bumping to clear cache

### 4.3 - Rollback Plan
- Keep backup copies of modified files
- Version changes in git with clear commit messages
- Test changes in local/staging first
- Can disable specific optimizations if issues arise

---

## Phase 5: Detailed Implementation Plan

### ASSET OPTIMIZATION - Detailed Steps:

**Step A1: Add helper methods to Assets.php**
```php
private function get_current_page_type($hook) {
    if ($this->is_dashboard_page($hook)) return 'dashboard';
    if ($this->is_analytics_dashboard_page($hook)) return 'analytics_dashboard';
    if ($this->is_settings_page($hook)) return 'settings';
    if ($this->is_modules_page($hook)) return 'modules';
    return 'generic';
}

private function needs_component_library($page_type) {
    // Components needed for: dashboard, analytics, modules, settings
    return in_array($page_type, ['dashboard', 'analytics_dashboard', 'settings', 'modules']);
}

private function is_onboarding_enabled() {
    return !get_option('shahi_onboarding_completed');
}
```

**Step A2: Move component/animation/utilities to conditional block**
```php
if ($this->needs_component_library($page_type)) {
    $this->enqueue_style('shahi-components', ...);
    $this->enqueue_style('shahi-animations', ...);
    $this->enqueue_style('shahi-utilities', ...);
}
```

**Step A3: Move onboarding styles/scripts to conditional block**
```php
if ($this->is_onboarding_enabled()) {
    $this->enqueue_style('shahi-onboarding', ...);
    $this->enqueue_script('shahi-onboarding', ...);
}
```

**Step A4: Move menu highlight script to only necessary pages**
```php
// Only run on pages that use WordPress admin menu
if (!in_array($page_type, ['onboarding'])) {
    $this->add_menu_highlight_script();
}
```

### QUERY OPTIMIZATION - Detailed Steps:

**Step Q1: Create `includes/Database/QueryOptimizer.php`**
- Wrapper methods with transient caching
- Automatic cache invalidation hooks
- Fallback to real queries if cache misses

**Step Q2: Refactor `get_key_performance_indicators()` in AnalyticsDashboard.php**
- Use `get_period_stats_cached()` instead of direct query
- Implement 6-hour cache
- Add manual cache clear button in UI

**Step Q3: Refactor `get_period_stats()` in AnalyticsDashboard.php**
- Add query limits where appropriate
- Add indexes to WHERE clauses
- Use DISTINCT only when necessary

**Step Q4: Add pagination to get_top_pages() and get_top_events()**
- Keep current 10-item limit
- Prepare for future pagination UI

**Step Q5: Replace mock data with real DB queries**
- `get_trend_data()` - query actual data with LIMIT
- `get_hourly_data()` - group by hour instead of hardcoded
- Others as applicable

---

## Expected Performance Gains

| Issue | Before | After | Gain |
|-------|--------|-------|------|
| Dashboard load | 2-3s | 0.5-1s | 66% faster |
| Analytics page | 8-15s | 2-4s | 70% faster |
| Settings page | 2-3s | 0.5s | 80% faster |
| General admin | 2-3s | 0.3-0.5s | 85% faster |

---

## Success Criteria

✅ All pages load without JavaScript errors
✅ All functionality works identically before/after
✅ Admin pages load > 70% faster
✅ Database queries execute < 1 second each
✅ No 404 errors for assets
✅ Cache invalidation works correctly
✅ Onboarding modal appears when needed

---

## Timeline

- Phase 1 (Assets): 2-3 hours of code changes + 1 hour testing
- Phase 2 (DB): 1-2 hours of code changes + 1 hour testing
- Phase 3 (Integration): 1 hour
- Phase 4 (Validation): 2-3 hours
- **Total: 7-10 hours development + testing**
