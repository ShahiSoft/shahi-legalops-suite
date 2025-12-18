# ShahiTemplate - Database Schema Documentation

**Version**: 1.0.0  
**Last Updated**: December 13, 2025  
**Database Version**: 1.0.0

---

## 📊 **Overview**

ShahiTemplate uses a hybrid approach for data storage:
- **Custom Tables**: For high-volume transactional data (analytics, modules)
- **WordPress Options**: For settings and configuration
- **WordPress Tables**: For standard WordPress data (users, posts, etc.)

This approach ensures optimal performance while maintaining WordPress compatibility.

---

## 🗄️ **Custom Database Tables**

### 1. Analytics Table: `{prefix}_shahi_analytics`

**Purpose**: Track user events, interactions, and plugin usage for analytics dashboard.

**Table Structure**:
```sql
CREATE TABLE {prefix}_shahi_analytics (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    event_type varchar(50) NOT NULL,
    event_data longtext,
    user_id bigint(20) UNSIGNED DEFAULT NULL,
    ip_address varchar(45) DEFAULT NULL,
    user_agent varchar(255) DEFAULT NULL,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY  (id),
    KEY event_type (event_type),
    KEY user_id (user_id),
    KEY created_at (created_at)
) {charset_collate};
```

**Column Descriptions**:
- `id` - Auto-incrementing primary key
- `event_type` - Type of event (e.g., 'page_view', 'button_click', 'module_toggle')
- `event_data` - JSON-encoded additional event data
- `user_id` - WordPress user ID (NULL for anonymous events)
- `ip_address` - IPv4 or IPv6 address (45 chars supports both)
- `user_agent` - Browser user agent string
- `created_at` - Timestamp when event occurred

**Indexes**:
- PRIMARY KEY on `id` - Fast lookups by ID
- KEY on `event_type` - Filter by event type
- KEY on `user_id` - Filter by user
- KEY on `created_at` - Date range queries, sorting

**Expected Volume**: High (thousands to millions of rows)

**Example Data**:
```json
{
    "id": 1,
    "event_type": "module_enabled",
    "event_data": "{\"module\":\"analytics\",\"version\":\"1.0.0\"}",
    "user_id": 1,
    "ip_address": "192.168.1.1",
    "user_agent": "Mozilla/5.0...",
    "created_at": "2025-12-13 10:30:00"
}
```

**Data Retention**: 
- Recommended: 90 days for performance
- Configurable in settings
- Automatic cleanup via cron job (future feature)

---

### 2. Modules Table: `{prefix}_shahi_modules`

**Purpose**: Store module states, settings, and metadata for the modular system.

**Table Structure**:
```sql
CREATE TABLE {prefix}_shahi_modules (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    module_key varchar(100) NOT NULL,
    is_enabled tinyint(1) NOT NULL DEFAULT 1,
    settings longtext,
    last_updated datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY  (id),
    UNIQUE KEY module_key (module_key)
) {charset_collate};
```

**Column Descriptions**:
- `id` - Auto-incrementing primary key
- `module_key` - Unique module identifier (e.g., 'analytics', 'seo', 'cache')
- `is_enabled` - Module enabled status (1 = enabled, 0 = disabled)
- `settings` - JSON-encoded module-specific settings
- `last_updated` - Auto-updated timestamp on any change

**Indexes**:
- PRIMARY KEY on `id` - Fast lookups by ID
- UNIQUE KEY on `module_key` - Enforce unique modules, fast lookups by key

**Expected Volume**: Low (typically 10-50 modules)

**Example Data**:
```json
{
    "id": 1,
    "module_key": "analytics",
    "is_enabled": 1,
    "settings": "{\"tracking_enabled\":true,\"anonymize_ip\":true,\"retention_days\":90}",
    "last_updated": "2025-12-13 10:30:00"
}
```

**Module Keys** (Reserved):
- `analytics` - Analytics tracking module
- `security` - Security hardening module
- `cache` - Performance caching module
- `seo` - SEO optimization module
- `custom_post_types` - CPT module
- `rest_api` - REST API module
- `widgets` - Widget module
- `shortcodes` - Shortcode module

---

### 3. Onboarding Table: `{prefix}_shahi_onboarding`

**Purpose**: Track user progress through onboarding flow for resumability and analytics.

**Table Structure**:
```sql
CREATE TABLE {prefix}_shahi_onboarding (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id bigint(20) UNSIGNED NOT NULL,
    step_completed varchar(100) NOT NULL,
    data_collected longtext,
    completed_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY  (id),
    KEY user_id (user_id),
    KEY step_completed (step_completed)
) {charset_collate};
```

**Column Descriptions**:
- `id` - Auto-incrementing primary key
- `user_id` - WordPress user ID who completed the step
- `step_completed` - Step identifier (e.g., 'welcome', 'purpose', 'features')
- `data_collected` - JSON-encoded data collected in this step
- `completed_at` - Timestamp when step was completed

**Indexes**:
- PRIMARY KEY on `id` - Fast lookups by ID
- KEY on `user_id` - Get all steps for a user
- KEY on `step_completed` - Analytics on which steps are completed

**Expected Volume**: Low to medium (one entry per user per step)

**Example Data**:
```json
{
    "id": 1,
    "user_id": 1,
    "step_completed": "purpose",
    "data_collected": "{\"purpose\":\"ecommerce\",\"experience\":\"beginner\"}",
    "completed_at": "2025-12-13 10:30:00"
}
```

**Onboarding Steps** (Defined):
- `welcome` - Introduction step
- `purpose` - User's purpose/use case
- `features` - Feature selection
- `configuration` - Basic settings
- `complete` - Final completion

---

## ⚙️ **WordPress Options**

All plugin settings stored in WordPress `wp_options` table with consistent prefix.

### Option Naming Convention
**Prefix**: `shahi_template_`  
**Format**: `shahi_template_{category}_{name}`

### Core Options

#### 1. `shahi_template_version`
- **Type**: String
- **Purpose**: Track installed plugin version
- **Default**: Current plugin version (e.g., '1.0.0')
- **Updated**: On plugin update/activation
- **Example**: `'1.0.0'`

#### 2. `shahi_template_installed_at`
- **Type**: String (datetime)
- **Purpose**: Track when plugin was first installed
- **Default**: Current timestamp on first activation
- **Updated**: Never (set once)
- **Example**: `'2025-12-13 10:30:00'`

#### 3. `shahi_template_onboarding_completed`
- **Type**: Boolean
- **Purpose**: Track if user completed onboarding
- **Default**: `false`
- **Updated**: When onboarding is completed
- **Example**: `true` or `false`

#### 4. `shahi_template_settings`
- **Type**: Array (serialized)
- **Purpose**: General plugin settings
- **Default**: 
  ```php
  array(
      'plugin_enabled' => true,
      'admin_email' => get_option('admin_email'),
      'date_format' => get_option('date_format'),
      'time_format' => get_option('time_format'),
  )
  ```
- **Structure**:
  ```php
  array(
      'plugin_enabled' => bool,     // Master enable/disable
      'admin_email' => string,      // Admin notification email
      'date_format' => string,      // Date display format
      'time_format' => string,      // Time display format
  )
  ```

#### 5. `shahi_template_advanced_settings`
- **Type**: Array (serialized)
- **Purpose**: Advanced/developer settings
- **Default**:
  ```php
  array(
      'debug_mode' => false,
      'custom_css' => '',
      'custom_js' => '',
      'developer_mode' => false,
      'api_rate_limit' => 100,
  )
  ```
- **Structure**:
  ```php
  array(
      'debug_mode' => bool,         // Enable debug logging
      'custom_css' => string,       // Custom CSS code
      'custom_js' => string,        // Custom JavaScript code
      'developer_mode' => bool,     // Developer features
      'api_rate_limit' => int,      // API requests per hour
  )
  ```

#### 6. `shahi_template_uninstall_preferences`
- **Type**: Array (serialized)
- **Purpose**: Control what data to delete on uninstall
- **Default** (CodeCanyon compliant):
  ```php
  array(
      'preserve_all' => true,           // Preserve everything
      'delete_settings' => false,       // Don't delete settings
      'delete_analytics' => false,      // Don't delete analytics
      'delete_posts' => false,          // Don't delete posts
      'delete_capabilities' => false,   // Don't delete capabilities
      'delete_tables' => false,         // Don't delete tables
  )
  ```
- **Structure**:
  ```php
  array(
      'preserve_all' => bool,           // Master preserve flag
      'delete_settings' => bool,        // Delete all options
      'delete_analytics' => bool,       // Truncate analytics table
      'delete_posts' => bool,           // Delete CPT entries
      'delete_capabilities' => bool,    // Remove user capabilities
      'delete_tables' => bool,          // Drop custom tables
  )
  ```

#### 7. `shahi_template_modules_enabled`
- **Type**: Array (serialized)
- **Purpose**: Quick lookup of enabled modules
- **Default**:
  ```php
  array(
      'analytics' => true,
      'security' => true,
  )
  ```
- **Structure**:
  ```php
  array(
      'module_key' => bool,  // true = enabled, false = disabled
  )
  ```
- **Note**: Synced with `{prefix}_shahi_modules` table

---

## 🔄 **Database Versioning**

### Version Tracking

**Current Version**: 1.0.0

**Version Storage**:
- Option: `shahi_template_db_version`
- Format: Semantic versioning (MAJOR.MINOR.PATCH)

### Migration Strategy

When database schema changes:
1. Increment `shahi_template_db_version`
2. Run migration script
3. Update tables/columns as needed
4. Update option with new version

**Migration File Naming**:
- Format: `migration_X_Y_Z.php`
- Example: `migration_1_1_0.php` (version 1.1.0)
- Location: `/includes/Database/Migrations/`

---

## 📈 **Performance Optimization**

### Indexing Strategy

**Analytics Table**:
- `event_type` - Frequently filtered
- `user_id` - User-specific queries
- `created_at` - Date range queries, sorting

**Modules Table**:
- `module_key` - UNIQUE, most common lookup

**Onboarding Table**:
- `user_id` - User-specific queries
- `step_completed` - Analytics queries

### Query Optimization Tips

1. **Use specific columns** instead of `SELECT *`
2. **Leverage indexes** in WHERE clauses
3. **Cache results** using WordPress transients
4. **Limit result sets** to necessary data
5. **Use EXPLAIN** to analyze slow queries

### Caching Strategy

**Transient Keys**:
- `shahi_template_analytics_{period}` - Analytics data
- `shahi_template_module_{key}` - Module settings
- `shahi_template_stats_{type}` - Dashboard stats

**Cache Duration**:
- Analytics: 1 hour (3600 seconds)
- Modules: Until updated (no expiry)
- Stats: 5 minutes (300 seconds)

---

## 🔒 **Security Considerations**

### SQL Injection Prevention

**Always use**:
- `$wpdb->prepare()` for dynamic queries
- `%s` for strings
- `%d` for integers
- `%f` for floats

**Example**:
```php
$wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}shahi_analytics WHERE event_type = %s",
        $event_type
    )
);
```

### Data Sanitization

**Before INSERT/UPDATE**:
- `sanitize_text_field()` for simple text
- `sanitize_email()` for emails
- `absint()` for positive integers
- `wp_json_encode()` for JSON data

**Example**:
```php
$data = array(
    'event_type' => sanitize_text_field($type),
    'user_id' => absint($user_id),
    'event_data' => wp_json_encode($data),
);
```

### Data Validation

**Before storage**:
- Validate data types
- Check required fields
- Validate foreign keys
- Enforce constraints

---

## 📊 **Data Integrity**

### Foreign Key Relationships

While MySQL MyISAM doesn't enforce foreign keys, maintain logical relationships:

**Analytics**:
- `user_id` → `wp_users.ID` (can be NULL)

**Modules**:
- No foreign keys (self-contained)

**Onboarding**:
- `user_id` → `wp_users.ID` (must exist)

### Data Cleanup

**Orphaned Records**:
- Check for users that no longer exist
- Clean up via scheduled cron job
- Maintain referential integrity

**Old Data**:
- Analytics older than retention period
- Completed onboarding steps (configurable)
- Transient data

---

## 🛠️ **Maintenance**

### Regular Tasks

1. **Optimize tables** monthly
   ```sql
   OPTIMIZE TABLE {prefix}_shahi_analytics;
   OPTIMIZE TABLE {prefix}_shahi_modules;
   OPTIMIZE TABLE {prefix}_shahi_onboarding;
   ```

2. **Analyze table statistics**
   ```sql
   ANALYZE TABLE {prefix}_shahi_analytics;
   ```

3. **Check table integrity**
   ```sql
   CHECK TABLE {prefix}_shahi_analytics;
   ```

### Backup Recommendations

**What to backup**:
- All custom tables
- All `shahi_template_*` options
- User meta with `shahi_template_*` prefix

**Frequency**:
- Before plugin updates
- Daily (if high-value data)
- Before bulk operations

---

## 📋 **Database Queries Reference**

### Common Queries

#### Get Analytics for Date Range
```php
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}shahi_analytics 
        WHERE created_at BETWEEN %s AND %s 
        ORDER BY created_at DESC 
        LIMIT %d",
        $start_date,
        $end_date,
        $limit
    )
);
```

#### Get Enabled Modules
```php
$modules = $wpdb->get_results(
    "SELECT module_key, settings 
    FROM {$wpdb->prefix}shahi_modules 
    WHERE is_enabled = 1"
);
```

#### Get User Onboarding Progress
```php
$steps = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT step_completed, data_collected, completed_at 
        FROM {$wpdb->prefix}shahi_onboarding 
        WHERE user_id = %d 
        ORDER BY completed_at ASC",
        $user_id
    )
);
```

#### Track Event
```php
$wpdb->insert(
    $wpdb->prefix . 'shahi_analytics',
    array(
        'event_type' => $type,
        'event_data' => wp_json_encode($data),
        'user_id' => get_current_user_id(),
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    ),
    array('%s', '%s', '%d', '%s', '%s')
);
```

---

## 🎯 **Future Enhancements**

### Planned Additions

1. **Caching Table** (v1.1)
   - Store cached data
   - Faster than transients
   - Better for large datasets

2. **Queue Table** (v1.2)
   - Background job processing
   - Email queue
   - API request queue

3. **Logs Table** (v1.3)
   - Error logging
   - Debug logging
   - Audit trail

4. **Sessions Table** (v2.0)
   - User session management
   - Multi-device support
   - Session analytics

---

## 📖 **Glossary**

- **MyISAM**: Default WordPress storage engine
- **InnoDB**: Alternative engine with foreign key support
- **Charset**: Character encoding (utf8mb4 recommended)
- **Collate**: Character comparison rules
- **Index**: Database optimization structure
- **Transient**: WordPress temporary cache
- **dbDelta**: WordPress table creation/update function

---

**Document Version**: 1.0.0  
**Last Updated**: December 13, 2025  
**Maintained By**: ShahiTemplate Development Team
