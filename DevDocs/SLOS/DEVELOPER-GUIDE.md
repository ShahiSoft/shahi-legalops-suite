# Developer Guide

## Architecture Overview

ShahiTemplate follows a strict PSR-4 autoloading structure with modern object-oriented design patterns. The entry point is the main plugin file (`shahi-template.php`), which initializes the `Core\Plugin` class.

### Core Design Principles
1. **Separation of Concerns**: Each class has a single, well-defined responsibility
2. **Dependency Injection**: Dependencies are injected rather than hardcoded
3. **Hook-Based Architecture**: Leverages WordPress action and filter system
4. **Lazy Loading**: Components are loaded only when needed
5. **Service-Oriented**: Business logic separated from presentation

### Application Flow
```
shahi-template.php (Entry Point)
    │
    ├── Core\Plugin (Main Controller)
    │   ├── Core\Loader (Hook Manager)
    │   ├── Core\Assets (Asset Manager)
    │   ├── Core\Security (Security Layer)
    │   ├── Admin\* (Admin Components)
    │   ├── API\RestAPI (REST API Router)
    │   └── Modules\* (Feature Modules)
    │
    └── Run Application
```

### Directory Structure
- `assets/` - CSS, JS, Images
  - Static files served to the browser
  - Organized by type and context (admin/public)
- `bin/` - CLI scripts for setup and maintenance
  - `setup.php` [PLACEHOLDER: Not yet created]
  - `create-module.php` [PLACEHOLDER: Not yet created]
- `includes/` - PHP Classes (PSR-4 autoloaded)
  - `Core/` - Core functionality (Loader, Assets, Security, Plugin)
  - `Admin/` - Admin pages and UI (Dashboard, Settings, Onboarding)
  - `Ajax/` - AJAX request handlers
  - `API/` - REST API endpoints and controllers
  - `Database/` - Database helpers and migrations
  - `Modules/` - Feature modules (Analytics, Cache, SEO)
  - `PostTypes/` - Custom post types and meta boxes
  - `Services/` - Business logic and services
  - `Shortcodes/` - Shortcode handlers
  - `Widgets/` - Dashboard widgets
- `templates/` - HTML/PHP views
  - `admin/` - Backend templates
  - `public/` - Frontend templates
- `languages/` - Translation files (.pot, .po, .mo)

## Design Patterns Used

### 1. Singleton Pattern
Used for the main Plugin class to ensure single instance:

```php
namespace ShahiTemplate\Core;

class Plugin {
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Initialization
    }
}
```

### 2. Factory Pattern
Used for creating modules dynamically:

```php
namespace ShahiTemplate\Modules;

class ModuleManager {
    public function create_module($type) {
        switch ($type) {
            case 'analytics':
                return new Analytics_Module();
            case 'cache':
                return new Cache_Module();
            default:
                throw new \Exception('Unknown module type');
        }
    }
}
```

### 3. Observer Pattern
Leverages WordPress hooks system:

```php
// Subject (WordPress)
do_action('shahi_template_init');

// Observer (Your code)
add_action('shahi_template_init', function() {
    // React to event
});
```

### 4. Strategy Pattern
Used for module activation/deactivation:

```php
interface ModuleStrategy {
    public function activate();
    public function deactivate();
}

class CacheModule implements ModuleStrategy {
    public function activate() {
        // Activate caching
    }
    
    public function deactivate() {
        // Clear and disable cache
    }
}
```

### 5. Dependency Injection
Constructor injection for dependencies:

```php
class Dashboard {
    private $analytics;
    private $widget_manager;
    
    public function __construct(Analytics $analytics, WidgetManager $widgets) {
        $this->analytics = $analytics;
        $this->widget_manager = $widgets;
    }
}
```

## Coding Standards

### PHP
- Follow **PSR-12** coding standards.
- Use strict typing where possible (`declare(strict_types=1);`).
- All classes must be namespaced.
- Use type hints for parameters and return types.
- Maximum line length: 120 characters.
- Use camelCase for methods, snake_case for variables (WordPress convention).

```php
<?php
declare(strict_types=1);

namespace ShahiTemplate\Services;

class EmailService {
    /**
     * Send notification email
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $message Email body
     * @return bool Success status
     */
    public function sendNotification(string $to, string $subject, string $message): bool {
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        return wp_mail($to, $subject, $message, $headers);
    }
}
```

### CSS
- Use **CSS variables** for theming.
- Prefix all classes with `shahi-` (or your plugin slug) to avoid conflicts.
- Mobile-first media queries.
- BEM naming convention where applicable.
- Group related properties together.

```css
/* CSS Variables */
:root {
    --shahi-primary: #00d4ff;
    --shahi-spacing: 1rem;
}

/* BEM Naming */
.shahi-card {
    /* Layout */
    display: flex;
    padding: var(--shahi-spacing);
    
    /* Visual */
    background: var(--shahi-bg-light);
    border-radius: 8px;
}

.shahi-card__header {
    font-size: 1.2rem;
    color: var(--shahi-primary);
}

.shahi-card--featured {
    border: 2px solid var(--shahi-primary);
}

/* Mobile first */
.shahi-container {
    width: 100%;
}

@media (min-width: 768px) {
    .shahi-container {
        width: 750px;
    }
}
```

### JavaScript
- Use **ES6+** syntax.
- Avoid jQuery unless necessary for WordPress core compatibility.
- Use `wp_localize_script` for passing PHP data to JS.
- Always use `'use strict';`.
- Handle errors with try/catch.

```javascript
'use strict';

(function($) {
    const ShahiAdmin = {
        init() {
            this.bindEvents();
            this.loadInitialData();
        },
        
        bindEvents() {
            $(document).on('click', '.shahi-save-btn', this.saveSettings.bind(this));
        },
        
        async saveSettings(e) {
            e.preventDefault();
            
            try {
                const response = await $.ajax({
                    url: shahiData.ajaxUrl,
                    method: 'POST',
                    data: {
                        action: 'shahi_save_settings',
                        nonce: shahiData.nonce,
                        settings: this.getFormData()
                    }
                });
                
                this.showNotice('success', response.message);
            } catch (error) {
                this.showNotice('error', error.responseJSON?.message || 'An error occurred');
            }
        },
        
        getFormData() {
            return {
                option1: $('#option1').val(),
                option2: $('#option2').is(':checked')
            };
        },
        
        showNotice(type, message) {
            // Show notification
        }
    };
    
    $(document).ready(() => ShahiAdmin.init());
})(jQuery);
```

### PHPDoc Comments
Document all public methods:

```php
/**
 * Calculate user engagement score
 *
 * Analyzes user activity over the past 30 days and returns
 * an engagement score between 0 and 100.
 *
 * @since 1.0.0
 *
 * @param int $user_id WordPress user ID
 * @param array $options {
 *     Optional. Calculation options.
 *
 *     @type int    $days      Number of days to analyze. Default 30.
 *     @type string $method    Calculation method. Default 'weighted'.
 *     @type bool   $cache     Whether to cache results. Default true.
 * }
 * @return float Engagement score (0-100)
 * @throws \InvalidArgumentException If user ID is invalid
 */
public function calculateEngagement(int $user_id, array $options = []): float {
    // Implementation
}
```

## Performance Optimization

### Asset Loading
Only load assets where needed:

```php
public function enqueue_scripts() {
    // Only on plugin pages
    if (!$this->is_plugin_page()) {
        return;
    }
    
    wp_enqueue_style(
        'shahi-admin',
        SHAHI_TEMPLATE_URL . 'assets/css/admin.css',
        [],
        SHAHI_TEMPLATE_VERSION
    );
}

private function is_plugin_page() {
    $screen = get_current_screen();
    return strpos($screen->id, 'shahi-template') !== false;
}
```

### Database Optimization
- Use indexes on frequently queried columns
- Limit query results
- Use `SELECT` specific columns instead of `*`
- Cache expensive queries

```php
// Good - Specific columns, limited results
$wpdb->get_results(
    "SELECT id, title FROM {$wpdb->prefix}shahi_data 
    WHERE status = 'active' 
    ORDER BY created_at DESC 
    LIMIT 10"
);

// Bad - All columns, no limit
$wpdb->get_results(
    "SELECT * FROM {$wpdb->prefix}shahi_data"
);
```

### Caching Strategy
```php
class CacheHelper {
    const CACHE_GROUP = 'shahi_template';
    const CACHE_EXPIRY = 3600; // 1 hour
    
    public static function get($key, $callback) {
        $cached = wp_cache_get($key, self::CACHE_GROUP);
        
        if (false !== $cached) {
            return $cached;
        }
        
        $data = $callback();
        wp_cache_set($key, $data, self::CACHE_GROUP, self::CACHE_EXPIRY);
        
        return $data;
    }
}

// Usage
$stats = CacheHelper::get('user_stats', function() {
    // Expensive operation
    return calculate_stats();
});
```

## Testing

### Manual Testing Checklist
- Test plugin activation/deactivation
- Test on fresh WordPress install
- Test with different themes
- Test with WP_DEBUG enabled
- Test on different PHP versions
- Test on mobile devices
- Test all AJAX endpoints
- Test all forms with invalid data

### Error Handling
```php
try {
    $result = $this->performOperation();
} catch (\Exception $e) {
    error_log('Shahi Template Error: ' . $e->getMessage());
    
    if (WP_DEBUG) {
        wp_die($e->getMessage());
    } else {
        wp_die(__('An error occurred. Please try again.', 'shahi-template'));
    }
}
```

## The Module System

Features should be encapsulated in Modules. A module is a self-contained class that handles a specific feature set. This promotes code organization and makes features easy to enable/disable.

### Module Structure
Each module should:
- Be placed in `includes/Modules/`
- Have a `run()` method that registers hooks
- Be self-contained (no external dependencies where possible)
- Follow single responsibility principle

### Creating a Module
1. Create a class in `includes/Modules/`.
2. Implement the `run()` method.
3. Register the module in `Core\Plugin::init_modules()`.

```php
<?php
namespace ShahiTemplate\Modules;

class Backup_Module {
    private $backup_dir;
    
    public function __construct() {

### Using WordPress Database Class
Use the global `$wpdb` object for database interactions. **Always prepare your SQL statements** to prevent SQL injection.

```php
global $wpdb;

// SELECT with prepare
$user_id = 123;
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}shahi_data WHERE user_id = %d",
        $user_id
    )
);

// INSERT with prepare
$wpdb->insert(
    $wpdb->prefix . 'shahi_data',
    [
        'user_id' => $user_id,
        'data' => 'value',
        'created_at' => current_time('mysql')
    ],
    ['%d', '%s', '%s'] // Format: %d=int, %s=string, %f=float
);

// UPDATE with prepare
$wpdb->update(
    $wpdb->prefix . 'shahi_data',
    ['data' => 'new_value'], // Data to update
    ['id' => 1],             // WHERE clause
    ['%s'],                  // Format for data
    ['%d']                   // Format for WHERE
);

// DELETE with prepare
$wpdb->delete(
    $wpdb->prefix . 'shahi_data',
    ['id' => 1],
    ['%d']
);
```

### Database Migrations
Create migrations in `includes/Database/Migrations/`:

```php
<?php
namespace ShahiTemplate\Database\Migrations;

class Migration_1_1_0 {
    public function up() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'shahi_new_table';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            data longtext NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function down() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'shahi_new_table';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }
}
```

### Caching Database Queries
Use WordPress Transients API:

```php
// Try to get cached data
$cached_data = get_transient('shahi_expensive_query');

if (false === $cached_data) {
    // Cache miss - run query
    global $wpdb;
    $cached_data = $wpdb->get_results("SELECT ...");
    
    // Cache for 1 hour
    set_transient('shahi_expensive_query', $cached_data, HOUR_IN_SECONDS);
}

// Use $cached_data
        add_action('init', [$this, 'init_backup_system']);
        add_action('shahi_template_daily_backup', [$this, 'perform_backup']);
        
        // Schedule cron if not scheduled
        if (!wp_next_scheduled('shahi_template_daily_backup')) {
            wp_schedule_event(time(), 'daily', 'shahi_template_daily_backup');
        }
    }

    public function init_backup_system() {
        // Create backup directory if it doesn't exist
        if (!file_exists($this->backup_dir)) {
            wp_mkdir_p($this->backup_dir);
        }
    }
    
    public function perform_backup() {
        // Backup logic here
        $this->backup_database();
        $this->backup_files();
    }
    
    private function backup_database() {
        // Database backup implementation
    }
    
    private function backup_files() {
        // Files backup implementation
    }
}
```

### Module Best Practices
- Keep modules focused on a single feature
- Use private methods for internal logic
- Document public methods with PHPDoc
- Handle errors gracefully
- Clean up on deactivation (implement cleanup method)

## Service Layer

Services contain business logic that can be reused across different parts of the plugin.

### Creating a Service
Place in `includes/Services/`:


Security is paramount. Follow these guidelines for all code:

### Nonces
Always use nonces for actions that change state:

```php
// Generate nonce
<input type="hidden" name="shahi_nonce" value="<?php echo wp_create_nonce('shahi_action'); ?>">

// Verify nonce
if (!wp_verify_nonce($_POST['shahi_nonce'], 'shahi_action')) {
    wp_die(__('Security check failed', 'shahi-template'));
}
```

For AJAX:
```php
// JavaScript
data: {
    action: 'shahi_save_data',
    nonce: shahiData.nonce,
    value: 'test'
}

// PHP
public function ajax_save_data() {
    check_ajax_referer('shahi_nonce', 'nonce');
    // Process request
}
```

### Input Sanitization
**Never trust user input.** Always sanitize:

```php
// Text fields
$name = sanitize_text_field($_POST['name']);

// Textareas
$description = sanitize_textarea_field($_POST['description']);

// Emails
$email = sanitize_email($_POST['email']);

// URLs
$url = esc_url_raw($_POST['url']);

// Integers
$count = absint($_POST['count']);

// Arrays
$data = array_map('sanitize_text_field', $_POST['data']);

// File paths
$file = sanitize_file_name($_POST['file']);
```

### Output Escaping
**Never output unescaped data.** Always escape:

```php
// HTML content
echo esc_html($variable);

// HTML attributes
echo '<div class="' . esc_attr($class) . '">';

// URLs
echo '<a href="' . esc_url($url) . '">';

// JavaScript
echo '<script>var data = ' . wp_json_encode($data) . ';</script>';

// Textarea content
echo '<textarea>' . esc_textarea($content) . '</textarea>';
```

### Capability Checks
Always verify user permissions:

```php
// Check capability
if (!current_user_can('manage_options')) {
    wp_die(__('Insufficient permissions', 'shahi-template'));
}

// Check for specific role
if (!in_array('administrator', wp_get_current_user()->roles)) {
    wp_die(__('Admin access required', 'shahi-template'));
}

// Custom capability
if (!current_user_can('shahi_manage_settings')) {
    wp_die(__('You cannot manage these settings', 'shahi-template'));
}
```

### File Operations
Secure file handling:

```php
// Validate file type
$allowed_types = ['jpg', 'png', 'gif'];
$file_type = wp_check_filetype($_FILES['upload']['name']);

if (!in_array($file_type['ext'], $allowed_types)) {
    wp_die(__('Invalid file type', 'shahi-template'));
}

// Use WordPress upload handler
$upload = wp_handle_upload($_FILES['upload'], ['test_form' => false]);

if (isset($upload['error'])) {
    wp_die($upload['error']);
}
```

### SQL Injection Prevention
Always use prepared statements:

```php
// WRONG - Vulnerable to SQL injection
$wpdb->query("SELECT * FROM table WHERE id = {$_GET['id']}");

// RIGHT - Safe with prepare
$wpdb->get_results(
    $wpdb->prepare("SELECT * FROM table WHERE id = %d", $_GET['id'])
);
```
class AnalyticsTracker {
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'shahi_analytics';
    }
    
    /**
     * Track an event
     *
     * @param string $event_type Type of event
     * @param array $data Event data
     * @return int|false Insert ID or false on failure
     */
    public function track_event($event_type, $data = []) {
        global $wpdb;
        
        return $wpdb->insert(
            $this->table_name,
            [
                'event_type' => sanitize_text_field($event_type),
                'event_data' => wp_json_encode($data),
                'user_id' => get_current_user_id(),
                'ip_address' => $this->get_user_ip(),
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%d', '%s', '%s']
        );
    }
    
    /**
     * Get events by type
     *
     * @param string $event_type Event type
     * @param int $limit Number of events to retrieve
     * @return array Events
     */
    public function get_events($event_type, $limit = 100) {
        global $wpdb;
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} 
                WHERE event_type = %s 
                ORDER BY created_at DESC 
                LIMIT %d",
                $event_type,
                $limit
            )
        );
    }
    
    private function get_user_ip() {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
```

### Using Services
Inject services where needed:

```php
class Dashboard {
    private $analytics;
    
    public function __construct() {
        $this->analytics = new \ShahiTemplate\Services\AnalyticsTracker();
    }
    
    public function render_dashboard() {
        $events = $this->analytics->get_events('page_view', 10);
        // Render dashboard with events
    }
}
```

## Database Access
Use the global `$wpdb` object for database interactions. Always prepare your SQL statements.

```php
global $wpdb;
$wpdb->prepare("SELECT * FROM {$wpdb->prefix}mytable WHERE id = %d", $id);
```

## Security
- Use nonces for all form submissions and AJAX requests.
- Sanitize all inputs (`sanitize_text_field`, `absint`, etc.).
- Escape all outputs (`esc_html`, `esc_attr`, etc.).
- Check user capabilities (`current_user_can`).
