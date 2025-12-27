# TASK 1.7: WordPress Hooks & Integration

**Phase:** 1 (Infrastructure & Database)  
**Effort:** 4-6 hours  
**Prerequisites:** TASK 1.6 complete (REST API infrastructure exists)  
**Next Task:** [task-1.8-integration-tests.md](task-1.8-integration-tests.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 1.7 for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
REST API infrastructure exists (Task 1.6 complete). Now integrate the plugin with WordPress
core systems: activation/deactivation hooks, cron jobs, admin capabilities, menu registration,
and plugin lifecycle management.

This ensures the plugin plays nicely with WordPress and follows WP best practices.

INPUT STATE (verify these exist):
✅ REST API infrastructure at includes/API/
✅ Services layer at includes/Services/
✅ Main plugin file: shahi-legalops-suite.php
✅ Database migrations at includes/Database/Migrations/

YOUR TASK:

1. **Create Plugin Activator**

Location: `includes/Core/Activator.php`

```php
<?php
/**
 * Plugin Activator
 * Handles plugin activation.
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

namespace Shahi\LegalOps\Core;

use Shahi\LegalOps\Database\Migrations\Runner;

class Activator {
    /**
     * Activate plugin
     *
     * @return void
     */
    public static function activate(): void {
        // Check PHP version
        if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die(
                esc_html__( 'Shahi LegalOps Suite requires PHP 8.0 or higher.', 'shahi-legalops-suite' ),
                esc_html__( 'Plugin Activation Error', 'shahi-legalops-suite' ),
                [ 'back_link' => true ]
            );
        }

        // Check WordPress version
        if ( version_compare( get_bloginfo( 'version' ), '6.0', '<' ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die(
                esc_html__( 'Shahi LegalOps Suite requires WordPress 6.0 or higher.', 'shahi-legalops-suite' ),
                esc_html__( 'Plugin Activation Error', 'shahi-legalops-suite' ),
                [ 'back_link' => true ]
            );
        }

        // Run database migrations
        self::run_migrations();

        // Add custom capabilities
        self::add_capabilities();

        // Schedule cron jobs
        self::schedule_events();

        // Set default options
        self::set_default_options();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Set activation flag
        update_option( 'slos_activated', true );
        update_option( 'slos_activation_time', current_time( 'mysql' ) );
        update_option( 'slos_version', '3.0.1' );

        // Trigger action
        do_action( 'slos_activated' );
    }

    /**
     * Run database migrations
     *
     * @return void
     */
    private static function run_migrations(): void {
        try {
            $runner = new Runner();
            $runner->run_all();
        } catch ( \Exception $e ) {
            error_log( '[SLOS] Migration failed during activation: ' . $e->getMessage() );
            // Don't fail activation, but log the error
        }
    }

    /**
     * Add custom capabilities
     *
     * @return void
     */
    private static function add_capabilities(): void {
        $capabilities = [
            'slos_manage_settings',
            'slos_read_data',
            'slos_create_data',
            'slos_edit_data',
            'slos_delete_data',
            'slos_export_data',
            'slos_view_analytics',
        ];

        // Add to administrator role
        $admin_role = get_role( 'administrator' );
        if ( $admin_role ) {
            foreach ( $capabilities as $cap ) {
                $admin_role->add_cap( $cap );
            }
        }

        // Create custom role (optional)
        add_role(
            'slos_manager',
            __( 'Compliance Manager', 'shahi-legalops-suite' ),
            [
                'read' => true,
                'slos_read_data' => true,
                'slos_view_analytics' => true,
            ]
        );
    }

    /**
     * Schedule cron events
     *
     * @return void
     */
    private static function schedule_events(): void {
        // Schedule daily cleanup
        if ( ! wp_next_scheduled( 'slos_daily_cleanup' ) ) {
            wp_schedule_event( time(), 'daily', 'slos_daily_cleanup' );
        }

        // Schedule weekly reports
        if ( ! wp_next_scheduled( 'slos_weekly_report' ) ) {
            wp_schedule_event( time(), 'weekly', 'slos_weekly_report' );
        }

        // Schedule monthly data retention
        if ( ! wp_next_scheduled( 'slos_monthly_retention' ) ) {
            wp_schedule_event( time(), 'monthly', 'slos_monthly_retention' );
        }
    }

    /**
     * Set default options
     *
     * @return void
     */
    private static function set_default_options(): void {
        $defaults = [
            'slos_consent_banner_enabled' => true,
            'slos_consent_position' => 'bottom',
            'slos_consent_theme' => 'light',
            'slos_data_retention_days' => 730, // 2 years
            'slos_cookie_expiry_days' => 365,
            'slos_enable_logging' => true,
            'slos_enable_analytics' => true,
        ];

        foreach ( $defaults as $key => $value ) {
            if ( false === get_option( $key ) ) {
                add_option( $key, $value );
            }
        }
    }
}
```

2. **Create Plugin Deactivator**

Location: `includes/Core/Deactivator.php`

```php
<?php
/**
 * Plugin Deactivator
 * Handles plugin deactivation.
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

namespace Shahi\LegalOps\Core;

class Deactivator {
    /**
     * Deactivate plugin
     *
     * @return void
     */
    public static function deactivate(): void {
        // Unschedule cron events
        self::unschedule_events();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Clear cache
        wp_cache_flush();

        // Set deactivation flag
        update_option( 'slos_deactivated', true );
        update_option( 'slos_deactivation_time', current_time( 'mysql' ) );

        // Trigger action
        do_action( 'slos_deactivated' );
    }

    /**
     * Unschedule cron events
     *
     * @return void
     */
    private static function unschedule_events(): void {
        $events = [
            'slos_daily_cleanup',
            'slos_weekly_report',
            'slos_monthly_retention',
        ];

        foreach ( $events as $event ) {
            $timestamp = wp_next_scheduled( $event );
            if ( $timestamp ) {
                wp_unschedule_event( $timestamp, $event );
            }
        }
    }
}
```

3. **Create Cron Handler**

Location: `includes/Core/Cron.php`

```php
<?php
/**
 * Cron Handler
 * Handles scheduled tasks.
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

namespace Shahi\LegalOps\Core;

use Shahi\LegalOps\Services\Consent_Service;

class Cron {
    /**
     * Initialize cron hooks
     *
     * @return void
     */
    public function init(): void {
        add_action( 'slos_daily_cleanup', [ $this, 'daily_cleanup' ] );
        add_action( 'slos_weekly_report', [ $this, 'weekly_report' ] );
        add_action( 'slos_monthly_retention', [ $this, 'monthly_retention' ] );
    }

    /**
     * Daily cleanup task
     *
     * @return void
     */
    public function daily_cleanup(): void {
        // Clean up old logs
        $this->cleanup_logs();

        // Clean up transients
        delete_expired_transients();

        // Log
        error_log( '[SLOS] Daily cleanup completed' );
    }

    /**
     * Weekly report task
     *
     * @return void
     */
    public function weekly_report(): void {
        // Generate weekly analytics report
        // Email to admins (implement in future task)

        error_log( '[SLOS] Weekly report generated' );
    }

    /**
     * Monthly data retention task
     *
     * @return void
     */
    public function monthly_retention(): void {
        $retention_days = get_option( 'slos_data_retention_days', 730 );

        // Expire old consents
        $consent_service = new Consent_Service();
        $expired = $consent_service->expire_old_consents( $retention_days );

        error_log( "[SLOS] Monthly retention: {$expired} consents expired" );
    }

    /**
     * Cleanup old logs
     *
     * @return void
     */
    private function cleanup_logs(): void {
        global $wpdb;

        $days = 30;
        $date = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        // This will be implemented when we have logging table
        // For now, just a placeholder
    }
}
```

4. **Create Admin Menu**

Location: `includes/Admin/Menu.php`

```php
<?php
/**
 * Admin Menu
 * Registers admin menu items.
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

namespace Shahi\LegalOps\Admin;

class Menu {
    /**
     * Initialize menu
     *
     * @return void
     */
    public function init(): void {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
    }

    /**
     * Register admin menu
     *
     * @return void
     */
    public function register_menu(): void {
        // Main menu
        add_menu_page(
            __( 'LegalOps Suite', 'shahi-legalops-suite' ),
            __( 'LegalOps', 'shahi-legalops-suite' ),
            'slos_read_data',
            'shahi-legalops',
            [ $this, 'render_dashboard' ],
            'dashicons-shield-alt',
            30
        );

        // Dashboard submenu
        add_submenu_page(
            'shahi-legalops',
            __( 'Dashboard', 'shahi-legalops-suite' ),
            __( 'Dashboard', 'shahi-legalops-suite' ),
            'slos_read_data',
            'shahi-legalops',
            [ $this, 'render_dashboard' ]
        );

        // Consent Management
        add_submenu_page(
            'shahi-legalops',
            __( 'Consent Management', 'shahi-legalops-suite' ),
            __( 'Consents', 'shahi-legalops-suite' ),
            'slos_read_data',
            'shahi-legalops-consents',
            [ $this, 'render_consents' ]
        );

        // Analytics
        add_submenu_page(
            'shahi-legalops',
            __( 'Analytics', 'shahi-legalops-suite' ),
            __( 'Analytics', 'shahi-legalops-suite' ),
            'slos_view_analytics',
            'shahi-legalops-analytics',
            [ $this, 'render_analytics' ]
        );

        // Settings
        add_submenu_page(
            'shahi-legalops',
            __( 'Settings', 'shahi-legalops-suite' ),
            __( 'Settings', 'shahi-legalops-suite' ),
            'slos_manage_settings',
            'shahi-legalops-settings',
            [ $this, 'render_settings' ]
        );
    }

    /**
     * Render dashboard page
     *
     * @return void
     */
    public function render_dashboard(): void {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'LegalOps Suite Dashboard', 'shahi-legalops-suite' ) . '</h1>';
        echo '<p>' . esc_html__( 'Welcome to Shahi LegalOps Suite!', 'shahi-legalops-suite' ) . '</p>';
        echo '</div>';
    }

    /**
     * Render consents page
     *
     * @return void
     */
    public function render_consents(): void {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Consent Management', 'shahi-legalops-suite' ) . '</h1>';
        echo '<p>' . esc_html__( 'Manage user consents here.', 'shahi-legalops-suite' ) . '</p>';
        echo '</div>';
    }

    /**
     * Render analytics page
     *
     * @return void
     */
    public function render_analytics(): void {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Analytics', 'shahi-legalops-suite' ) . '</h1>';
        echo '<p>' . esc_html__( 'View analytics and reports here.', 'shahi-legalops-suite' ) . '</p>';
        echo '</div>';
    }

    /**
     * Render settings page
     *
     * @return void
     */
    public function render_settings(): void {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Settings', 'shahi-legalops-suite' ) . '</h1>';
        echo '<p>' . esc_html__( 'Configure plugin settings here.', 'shahi-legalops-suite' ) . '</p>';
        echo '</div>';
    }
}
```

5. **Update main plugin file**

Update `shahi-legalops-suite.php`:

```php
<?php
/**
 * Plugin Name: Shahi LegalOps Suite
 * Version: 3.0.1
 */

namespace Shahi\LegalOps;

// Prevent direct access
defined( 'ABSPATH' ) || exit;

// Autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Activation/Deactivation hooks
register_activation_hook( __FILE__, [ 'Shahi\LegalOps\Core\Activator', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'Shahi\LegalOps\Core\Deactivator', 'deactivate' ] );

// Initialize plugin
add_action( 'plugins_loaded', function() {
    // Initialize REST API
    $rest_api = new API\REST_API();
    $rest_api->register_controller( API\Controllers\Health_Controller::class );
    $rest_api->init();

    // Initialize Cron
    $cron = new Core\Cron();
    $cron->init();

    // Initialize Admin Menu
    $menu = new Admin\Menu();
    $menu->init();
} );
```

6. **Update composer.json**

Add namespaces:
```json
"Shahi\\LegalOps\\Core\\": "includes/Core/",
"Shahi\\LegalOps\\Admin\\": "includes/Admin/"
```

Run:
```bash
composer dump-autoload
```

7. **Test hooks**

```bash
# Deactivate and reactivate
wp plugin deactivate shahi-legalops-suite
wp plugin activate shahi-legalops-suite

# Check cron events
wp cron event list | grep slos

# Check options
wp option get slos_version
wp option get slos_activated

# Check capabilities
wp cap list administrator | grep slos

# Check admin menu
wp eval 'global $menu; print_r(array_filter($menu, function($item) { return strpos($item[2] ?? "", "shahi-legalops") !== false; }));'
```

OUTPUT STATE:
✅ Activator with migrations, capabilities, cron
✅ Deactivator with cleanup
✅ Cron handler with scheduled tasks
✅ Admin menu with 4 pages
✅ Custom capabilities
✅ Plugin lifecycle management

VERIFICATION:

1. **Check files:**
```bash
ls -la includes/Core/
ls -la includes/Admin/
```

2. **Test activation:**
```bash
wp plugin deactivate shahi-legalops-suite
wp plugin activate shahi-legalops-suite
wp option get slos_activated
```
Expected: true

3. **Check cron:**
```bash
wp cron event list | grep slos
```
Expected: 3 events (daily, weekly, monthly)

4. **Check capabilities:**
```bash
wp cap list administrator | grep slos
```
Expected: 7 capabilities

5. **Check menu:**
Visit WordPress admin → should see "LegalOps" menu

SUCCESS CRITERIA:
✅ Activation/deactivation working
✅ Cron jobs scheduled
✅ Capabilities added
✅ Admin menu visible
✅ Default options set
✅ All hooks integrated

ROLLBACK:
```bash
wp plugin deactivate shahi-legalops-suite
rm -rf includes/Core/ includes/Admin/
git checkout shahi-legalops-suite.php composer.json
composer dump-autoload
wp plugin activate shahi-legalops-suite
```

TROUBLESHOOTING:

**Problem 1: Activation fails**
```bash
# Check error log
tail -f wp-content/debug.log

# Check PHP errors
wp plugin activate shahi-legalops-suite --debug
```

**Problem 2: Cron not scheduled**
```bash
# Manually schedule
wp cron event schedule slos_daily_cleanup now daily

# List all events
wp cron event list
```

**Problem 3: Menu not showing**
```bash
# Check current user capabilities
wp eval 'echo current_user_can("slos_read_data") ? "yes" : "no";'

# Add capability manually
wp cap add administrator slos_read_data
```

COMMIT MESSAGE:
```
feat(core): Add WordPress hooks and integration

- Create Activator (migrations, capabilities, cron, options)
- Create Deactivator (cleanup, unschedule events)
- Implement Cron handler (daily, weekly, monthly tasks)
- Add Admin Menu (dashboard, consents, analytics, settings)
- Register custom capabilities (7 capabilities)
- Add custom role (compliance_manager)
- Schedule automated tasks

WordPress integration complete.

Task: 1.7 (4-6 hours)
Next: Task 1.8 - Integration Tests
```

WHAT TO REPORT BACK:
"✅ TASK 1.7 COMPLETE

Created:
- Activator (migrations, caps, cron)
- Deactivator (cleanup)
- Cron handler (3 scheduled tasks)
- Admin Menu (4 pages)

Implemented:
- ✅ Plugin activation/deactivation
- ✅ 7 custom capabilities
- ✅ Custom role (Compliance Manager)
- ✅ 3 cron jobs (daily, weekly, monthly)
- ✅ Admin menu with 4 pages
- ✅ Default options

Verification passed:
- ✅ Activation successful
- ✅ Cron scheduled
- ✅ Capabilities added
- ✅ Admin menu visible
- ✅ WordPress integration complete

📍 Ready for TASK 1.8: [task-1.8-integration-tests.md](task-1.8-integration-tests.md)"
```

---

## ✅ COMPLETION CHECKLIST

- [ ] Activator created
- [ ] Deactivator created
- [ ] Cron handler implemented
- [ ] Admin menu registered
- [ ] Capabilities added
- [ ] Plugin activates successfully
- [ ] Committed to git
- [ ] Ready for Task 1.8

---

**Status:** ✅ Ready to execute  
**Time:** 4-6 hours  
**Next:** [task-1.8-integration-tests.md](task-1.8-integration-tests.md)
