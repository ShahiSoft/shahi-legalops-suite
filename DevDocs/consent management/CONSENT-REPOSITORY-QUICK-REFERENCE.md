# ConsentRepository Quick Reference Guide

**Version:** 1.0.0  
**Status:** ✅ Production Ready  
**File:** `includes/modules/consent/repositories/ConsentRepository.php`

---

## 1-Minute Overview

`ConsentRepository` handles all database operations for consent logs:
- **Save** consent preferences with validation
- **Retrieve** current consent status
- **Withdraw** consent (full or partial)
- **Query** logs with filtering, pagination, sorting
- **Export** logs in CSV/JSON
- **Count** logs with filters
- **Cleanup** expired logs for GDPR/retention compliance

---

## Instantiation

```php
// Method 1: Direct instantiation
$repository = new ConsentRepository();

// Method 2: Via Consent module (preferred in hooks)
$consent_module = apply_filters('complyflow_get_module', 'consent');
$repository = $consent_module->get_service('repository');
```

---

## Common Operations

### Save Consent
```php
$log_id = $repository->save_consent([
    'session_id'      => 'sess_xyz123',              // Required
    'region'          => 'EU',                       // Required
    'categories'      => ['necessary' => true, ...], // Required
    'banner_version'  => '1.0.0',                    // Optional (default)
    'user_id'         => get_current_user_id(),     // Optional
    'source'          => 'banner',                   // Optional
    'ip_hash'         => ConsentRepository::hash_ip($_SERVER['REMOTE_ADDR']), // Optional
    'user_agent_hash' => ConsentRepository::hash_user_agent($_SERVER['HTTP_USER_AGENT']), // Optional
]);

if ($log_id) {
    // Success: log_id is the database record ID
} else {
    // Failure: validation error or database error
}
```

### Check Consent Status
```php
$consent = $repository->get_consent_status('sess_xyz123');

if ($consent) {
    $is_analytics_allowed = $consent['categories']['analytics'] ?? false;
    $region = $consent['region'];
    $timestamp = $consent['timestamp'];
} else {
    // No active consent found
}
```

### Withdraw Consent
```php
// Full withdrawal (all categories)
$repository->withdraw_consent('sess_xyz123');

// Partial withdrawal (specific categories only)
$repository->withdraw_consent('sess_xyz123', ['analytics', 'marketing']);
```

### Get Filtered Logs
```php
$logs = $repository->get_logs([
    'region'     => 'EU',
    'user_id'    => 1,
    'start_date' => '2025-01-01 00:00:00',
    'end_date'   => '2025-12-31 23:59:59',
    'per_page'   => 20,
    'page'       => 1,
    'orderby'    => 'timestamp',
    'order'      => 'DESC',
]);

foreach ($logs as $log) {
    echo $log['session_id'] . ': ' . $log['region'];
}
```

### Count Logs
```php
$total = $repository->count_logs();
$eu_total = $repository->count_logs(['region' => 'EU']);
$withdrawn = $repository->count_logs(['withdrawn' => true]);
```

### Export Logs
```php
// CSV export
$csv = $repository->export_logs('csv', ['region' => 'EU']);

// JSON export
$json = $repository->export_logs('json', [
    'start_date' => '2025-01-01',
    'end_date'   => '2025-03-31',
]);
```

### Cleanup Expired Logs
```php
// Delete logs older than 365 days
$deleted = $repository->cleanup_expired_logs(365);
echo "Deleted $deleted old consent logs";
```

---

## Static Helper Methods

```php
// Generate unique session ID
$sid = ConsentRepository::generate_session_id();

// Hash IP (SHA256)
$ip_hash = ConsentRepository::hash_ip('192.168.1.1');

// Hash user agent (SHA256)
$ua_hash = ConsentRepository::hash_user_agent($_SERVER['HTTP_USER_AGENT']);

// Get client IP (handles proxies, CDNs)
$ip = ConsentRepository::get_client_ip();
```

---

## Database Schema

**Table:** `wp_complyflow_consent_logs`

```sql
CREATE TABLE wp_complyflow_consent_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED,
    session_id VARCHAR(64) NOT NULL,
    region VARCHAR(10) NOT NULL,
    categories LONGTEXT NOT NULL,           -- JSON
    purposes LONGTEXT,                      -- JSON (PRO)
    banner_version VARCHAR(50) NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    expiry_date DATETIME,
    source VARCHAR(50),
    ip_hash VARCHAR(64),
    user_agent_hash VARCHAR(64),
    withdrawn_at DATETIME,
    metadata LONGTEXT,                      -- JSON
    KEY idx_user_id (user_id),
    KEY idx_session_id (session_id),
    KEY idx_region (region),
    KEY idx_timestamp (timestamp),
    KEY idx_withdrawn (withdrawn_at)
);
```

**Key Indexes:**
- `idx_session_id` — Fast session lookups
- `idx_region` — Fast regional filtering
- `idx_timestamp` — Fast date range queries
- `idx_withdrawn` — Fast active/withdrawn filtering

---

## Return Values

### save_consent()
| Return | Meaning |
|--------|---------|
| `int` | Success — log ID in database |
| `false` | Failure — missing required fields or database error |

### get_consent_status()
| Return | Meaning |
|--------|---------|
| `array` | Consent found — with decoded JSON fields |
| `null` | No consent — session doesn't exist or all withdrawn |

### withdraw_consent()
| Return | Meaning |
|--------|---------|
| `true` | Success |
| `false` | Failure |

### get_logs()
| Return | Meaning |
|--------|---------|
| `array` | Array of logs (may be empty) |

### count_logs()
| Return | Meaning |
|--------|---------|
| `int` | Total count (0 or more) |

### export_logs()
| Return | Meaning |
|--------|---------|
| `string` | CSV/JSON data or empty string if no logs |

### cleanup_expired_logs()
| Return | Meaning |
|--------|---------|
| `int` | Number of deleted records (0 or more) |

---

## Error Handling

```php
// validate required fields
if (empty($preferences['session_id']) || empty($preferences['region'])) {
    return false;
}

// Use try-catch if calling in critical path
try {
    $consent = $repository->get_consent_status($session_id);
    // Use $consent
} catch (Exception $e) {
    error_log('Consent lookup failed: ' . $e->getMessage());
    // Handle gracefully
}
```

---

## Performance Considerations

### Query Optimization
- ✅ All queries use prepared statements
- ✅ Heavy queries use indexed columns
- ✅ Pagination limits per-query results

### Indexing
- `session_id` — Indexed (most common lookup)
- `region` — Indexed (regional filtering)
- `timestamp` — Indexed (date range queries)
- `withdrawn_at` — Indexed (active vs withdrawn)

### Best Practices
1. **Always specify `per_page` and `page`** for `get_logs()` to avoid huge result sets
2. **Use `count_logs()`** instead of counting results for large sets
3. **Call `cleanup_expired_logs()`** on schedule (e.g., weekly cron job)
4. **Cache `get_consent_status()`** in session/transient if called multiple times

---

## Example: WordPress Hook Integration

```php
// Hook into consent save from REST endpoint
add_action('rest_insert_complyflow_consent_preferences', function($preferences) {
    $repository = new ConsentRepository();
    
    $log_id = $repository->save_consent([
        'session_id'      => $preferences['session_id'],
        'region'          => $preferences['region'],
        'categories'      => $preferences['categories'],
        'banner_version'  => '1.0.0',
        'user_id'         => get_current_user_id(),
        'source'          => 'rest_api',
        'ip_hash'         => ConsentRepository::hash_ip(ConsentRepository::get_client_ip()),
    ]);
    
    if ($log_id) {
        do_action('complyflow_consent_saved', $log_id, $preferences);
    }
});

// Hook for consent withdrawal
add_action('complyflow_withdraw_consent', function($session_id, $categories) {
    $repository = new ConsentRepository();
    $repository->withdraw_consent($session_id, $categories);
    do_action('complyflow_consent_withdrawn', $session_id, $categories);
}, 10, 2);
```

---

## JSON Field Formats

### Categories (required)
```json
{
    "necessary": true,
    "functional": false,
    "analytics": true,
    "marketing": false,
    "custom_category": true
}
```

### Purposes (optional, PRO)
```json
{
    "analytics": ["consent", "legitimate_interest"],
    "marketing": ["consent"],
    "functional": []
}
```

### Metadata (optional)
```json
{
    "device_type": "mobile",
    "language": "en-US",
    "page_url": "https://example.com",
    "referrer": "https://google.com"
}
```

---

## Testing

Run unit tests:
```bash
# Using WordPress phpunit
wp phpunit tests/ConsentRepositoryTest.php

# Or use your preferred PHP test runner
phpunit includes/modules/consent/tests/ConsentRepositoryTest.php
```

Test file: `includes/modules/consent/tests/ConsentRepositoryTest.php`

---

## Troubleshooting

### "False is returned but no error message"
- Check required fields: `session_id`, `region`, `categories`
- Verify database table exists: `wp_complyflow_consent_logs`

### "get_consent_status() returns null"
- Session ID might be misspelled
- Consent might be withdrawn (`withdrawn_at IS NOT NULL`)
- Use `get_logs(['withdrawn' => true])` to find withdrawn records

### "Memory issue with large exports"
- Use pagination for large result sets
- Consider exporting in batches per region/date range
- Use JSON streaming for very large datasets

### "Timestamps are in wrong timezone"
- All timestamps use WordPress `current_time('mysql')`
- Check WordPress Settings → Timezone

---

## Related Classes

- `ConsentRepositoryInterface` — Contract for repository
- `Consent` — Main module class (parent)
- `ConsentRestController` — REST API endpoints
- `BlockingService` — Uses repository for blocking decisions
- `ConsentSignalService` — Emits signals based on consent status

---

## Additional Resources

- [Full Documentation](PHASE-1-COMPLETION-REPORT.md)
- [Product Specification](PRODUCT-SPEC.md)
- [Implementation Quickstart](IMPLEMENTATION-QUICKSTART.md)
- [Test Suite](../tests/ConsentRepositoryTest.php)
