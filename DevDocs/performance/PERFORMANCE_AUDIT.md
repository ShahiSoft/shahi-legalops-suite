# Admin Page Performance Audit - Key Issues

## 2 Major Performance Bottlenecks

### 1. **EXCESSIVE ASSET ENQUEUING (All Plugin Pages)**
**Location:** [`includes/Core/Assets.php`](includes/Core/Assets.php#L87-L138)

**Problem:** The plugin loads 8+ CSS files and 4+ JS files on **EVERY** admin page, regardless of whether they're needed:
- Every page loads: `admin-global`, `components`, `animations`, `utilities`, `onboarding` CSS/JS
- Adds inline CSS/JS for menu highlighting on every page
- Chart.js CDN is loaded on non-analytics pages unnecessarily
- All these files load synchronously, blocking page render

**Impact:** Increases Time to Interactive (TTI) by 2-3 seconds minimum. Bloats DOM with unnecessary code.

**Solution:** Implement strict page-specific loading - only load assets for the current page type.

---

### 2. **UNOPTIMIZED ANALYTICS DASHBOARD QUERIES**
**Location:** [`includes/Admin/AnalyticsDashboard.php`](includes/Admin/AnalyticsDashboard.php#L161-L280)

**Problem:** The Analytics Dashboard runs **multiple full-table scans** on page load:
- `get_period_stats()` runs 3 separate COUNT queries without indexes
- `get_trend_data()` loads 90 days of data in a loop (unused mock data)
- No query caching or transient usage
- Multiple `SELECT DISTINCT` queries on unindexed columns
- No pagination - loads all 10 "top pages" and "top events" without limits
- Each page renders calls all data methods (KPIs + trends + charts + top pages + top events + user segments + geographic + device breakdown)

**Impact:** Page load time: **8-15+ seconds** for medium-sized analytics tables (10k+ rows).

**Solution:** 
- Add database indexes on `event_time`, `user_id`, `event_type`
- Implement transient caching (1-5 min)
- Paginate query results
- Use LIMIT clauses on all queries

---

## Summary
- **Issue #1:** Kills load time with bloated CSS/JS on every page
- **Issue #2:** Kills load time with N+1 unoptimized database queries
