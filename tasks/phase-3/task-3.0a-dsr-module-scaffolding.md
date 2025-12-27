# TASK 3.0a: DSR Module Scaffolding

**Phase:** 3 (DSR Portal)  
**Effort:** 4-6 hours  
**Prerequisites:** Module and ModuleManager system in place (from core)  
**Next Task:** [task-3.0b-dsr-settings-page.md](task-3.0b-dsr-settings-page.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 3.0a for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
Create a Data Subject Rights (DSR) module that extends the base Module class,
enabling enable/disable control via ModuleManager with a card in the Module Dashboard.
This allows admins to toggle the entire DSR portal feature and manage its lifecycle.

References: /includes/Modules/Module.php (base class), /includes/Modules/ModuleManager.php

INPUT STATE (verify these exist):
✅ Module base class exists at includes/Modules/Module.php
✅ ModuleManager singleton at includes/Modules/ModuleManager.php
✅ Module Dashboard exists (shows Consent, Accessibility, Security modules)
✅ DSR_Repository created (Task 3.1 ready)

YOUR TASK:

1) **Create DSR Module Class**

File: `includes/Modules/DSR_Portal/DSR_Portal.php`

```php
<?php
/**
 * DSR Portal Module
 *
 * Data Subject Rights portal for GDPR/CCPA/LGPD compliance.
 * Manages DSR request submission, tracking, and fulfillment.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Modules\DSR_Portal
 * @license    GPL-3.0+
 * @since      3.0.1
 */

namespace ShahiLegalopsSuite\Modules\DSR_Portal;

use ShahiLegalopsSuite\Modules\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DSR_Portal Module Class
 *
 * @since 3.0.1
 */
class DSR_Portal extends Module {

	/**
	 * Get module unique key
	 *
	 * @since 3.0.1
	 * @return string Module key
	 */
	public function get_key(): string {
		return 'dsr-portal';
	}

	/**
	 * Get module name
	 *
	 * @since 3.0.1
	 * @return string Module name
	 */
	public function get_name(): string {
		return 'DSR Portal';
	}

	/**
	 * Get module description
	 *
	 * @since 3.0.1
	 * @return string Module description
	 */
	public function get_description(): string {
		return 'Data Subject Rights portal for GDPR, CCPA, LGPD, and other privacy regulations. Manage access requests, erasure, portability, and more.';
	}

	/**
	 * Get module icon
	 *
	 * @since 3.0.1
	 * @return string Icon class
	 */
	public function get_icon(): string {
		return 'dashicons-format-aside';
	}

	/**
	 * Get module category
	 *
	 * @since 3.0.1
	 * @return string Category
	 */
	public function get_category(): string {
		return 'compliance';
	}

	/**
	 * Get module version
	 *
	 * @since 3.0.1
	 * @return string Version
	 */
	public function get_version(): string {
		return '1.0.0';
	}

	/**
	 * Get module dependencies
	 *
	 * @since 3.0.1
	 * @return array Array of module keys this module depends on
	 */
	public function get_dependencies(): array {
		return array(); // DSR is independent
	}

	/**
	 * Initialize module
	 *
	 * Called when module is enabled. Registers hooks, REST routes, and shortcodes.
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function init(): void {
		// Only proceed if module is enabled
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Register REST API endpoints
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Register shortcodes
		add_action( 'init', array( $this, 'register_shortcodes' ) );

		// Register admin pages
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 20 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		}

		// Register frontend assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

		// Fire initialization hook
		do_action( 'slos_dsr_portal_init' );
	}

	/**
	 * Register REST API routes
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function register_rest_routes(): void {
		// Controllers will be registered here in subsequent tasks
		// Placeholder for Task 3.3 (DSR REST API)
		do_action( 'slos_dsr_register_rest_routes' );
	}

	/**
	 * Register shortcodes
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function register_shortcodes(): void {
		// Shortcodes will be registered here in subsequent tasks
		// Placeholder for Task 3.4 (DSR Form Shortcode)
		do_action( 'slos_dsr_register_shortcodes' );
	}

	/**
	 * Register admin menu
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function register_admin_menu(): void {
		// Admin pages will be registered here in Task 3.6
		do_action( 'slos_dsr_register_admin_menu' );
	}

	/**
	 * Enqueue admin assets
	 *
	 * @since 3.0.1
	 * @param string $hook Admin page hook.
	 * @return void
	 */
	public function enqueue_admin_assets( string $hook ): void {
		// Check if on DSR-related admin page
		if ( strpos( $hook, 'slos-dsr' ) === false && strpos( $hook, 'dsr' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'slos-dsr-admin',
			SLOS_PLUGIN_URL . 'assets/css/admin-dsr.css',
			array(),
			SLOS_VERSION
		);

		wp_enqueue_script(
			'slos-dsr-admin',
			SLOS_PLUGIN_URL . 'assets/js/admin-dsr.js',
			array( 'jquery', 'wp-api-fetch' ),
			SLOS_VERSION,
			true
		);
	}

	/**
	 * Enqueue frontend assets
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function enqueue_frontend_assets(): void {
		// Only enqueue if DSR form shortcode is present on page
		global $post;
		if ( ! isset( $post ) || ! has_shortcode( $post->post_content, 'slos_dsr_form' ) ) {
			return;
		}

		wp_enqueue_style(
			'slos-dsr-form',
			SLOS_PLUGIN_URL . 'assets/css/dsr-form.css',
			array(),
			SLOS_VERSION
		);

		wp_enqueue_script(
			'slos-dsr-form',
			SLOS_PLUGIN_URL . 'assets/js/dsr-form.js',
			array( 'jquery', 'wp-api-fetch' ),
			SLOS_VERSION,
			true
		);

		wp_localize_script(
			'slos-dsr-form',
			'slosDSRForm',
			array(
				'restUrl' => rest_url( 'slos/v1/dsr' ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	/**
	 * Hook called on module activation
	 *
	 * @since 3.0.1
	 * @return void
	 */
	protected function on_activate(): void {
		// Fire activation hook
		do_action( 'slos_dsr_portal_activate' );

		// Log activation
		error_log( 'DSR Portal module activated.' );
	}

	/**
	 * Hook called on module deactivation
	 *
	 * @since 3.0.1
	 * @return void
	 */
	protected function on_deactivate(): void {
		// Fire deactivation hook
		do_action( 'slos_dsr_portal_deactivate' );

		// Log deactivation
		error_log( 'DSR Portal module deactivated.' );
	}

	/**
	 * Get module settings URL
	 *
	 * @since 3.0.1
	 * @return string Settings URL
	 */
	public function get_settings_url(): string {
		return admin_url( 'admin.php?page=slos-dsr-settings' );
	}
}
```

2) **Register Module in ModuleManager**

File: `includes/Modules/ModuleManager.php` → Find `register_default_modules()` method and add:

```php
// DSR Portal Module (Data Subject Rights - Phase 3)
if ( class_exists( 'ShahiLegalopsSuite\Modules\DSR_Portal\DSR_Portal' ) ) {
	$dsr_module = new \ShahiLegalopsSuite\Modules\DSR_Portal\DSR_Portal();
	if ( $this->register( $dsr_module ) ) {
		// Auto-enable on first registration
		global $wpdb;
		$table  = $wpdb->prefix . 'shahi_modules';
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table WHERE module_key = %s",
				'dsr-portal'
			)
		);

		if ( ! $exists ) {
			$this->enable_module( 'dsr-portal' );
		}
	}
}
```

Location: Add this **after** the Consent Management module registration, around line 330.

3) **Verification Tests**

```bash
# Check module is registered
wp eval "
\$manager = ShahiLegalopsSuite\Modules\ModuleManager::get_instance();
\$module = \$manager->get_module( 'dsr-portal' );
if ( \$module ) {
    echo 'DSR Portal module registered' . PHP_EOL;
    echo 'Key: ' . \$module->get_key() . PHP_EOL;
    echo 'Name: ' . \$module->get_name() . PHP_EOL;
    echo 'Enabled: ' . ( \$module->is_enabled() ? 'Yes' : 'No' ) . PHP_EOL;
    echo 'Settings URL: ' . \$module->get_settings_url() . PHP_EOL;
} else {
    echo 'DSR Portal module NOT found' . PHP_EOL;
}
"

# Check in Module Dashboard (admin UI)
# Navigate to WordPress admin > Module Dashboard
# Verify DSR Portal card appears with toggle

# Test enable/disable
wp eval "
\$manager = ShahiLegalopsSuite\Modules\ModuleManager::get_instance();
\$result = \$manager->disable_module( 'dsr-portal' );
echo 'Module disabled: ' . ( \$result ? 'Yes' : 'No' ) . PHP_EOL;
\$result = \$manager->enable_module( 'dsr-portal' );
echo 'Module enabled: ' . ( \$result ? 'Yes' : 'No' ) . PHP_EOL;
"

# Check database state
wp db query "SELECT module_key, is_enabled FROM wp_shahi_modules WHERE module_key = 'dsr-portal'"
```

OUTPUT STATE:
✅ DSR_Portal module class created and extends Module base class
✅ Module registered in ModuleManager
✅ Auto-enabled on first registration
✅ Module appears in Module Dashboard with toggle
✅ Settings URL available
✅ init() hooks registered for REST, shortcodes, admin menus, assets
✅ on_activate() and on_deactivate() hooks fire

SUCCESS CRITERIA:
✅ Module shows in Module Dashboard card list
✅ Toggle switch works (enable/disable)
✅ get_settings_url() returns /admin.php?page=slos-dsr-settings
✅ Module status persists in wp_shahi_modules
✅ init() called only when enabled
✅ Dependencies check passes (no dependencies)
✅ All hooks (activation, deactivation, init) fire without errors

ROLLBACK:
```bash
# Remove module class
rm includes/Modules/DSR_Portal/DSR_Portal.php
rm -rf includes/Modules/DSR_Portal

# Revert ModuleManager registration
git checkout includes/Modules/ModuleManager.php
```

TROUBLESHOOTING:
- **Issue:** Module not showing in dashboard → Clear WordPress object cache; check class_exists() and namespace
- **Issue:** Toggle not working → Verify wp_shahi_modules table exists (run migrations first)
- **Issue:** init() not firing → Check is_enabled() returns true; verify autoloader recognizes new namespace

COMMIT MESSAGE:
```
feat(dsr): scaffold DSR Portal module

- Create DSR_Portal module class extending Module
- Register in ModuleManager with auto-enable
- Add to Module Dashboard with toggle control
- Wire hooks for REST, shortcodes, admin, assets
- Settings page URL available

Task: 3.0a (4-6 hours)
Next: Task 3.0b - DSR Settings Page
```

WHAT TO REPORT BACK:
"✅ TASK 3.0a COMPLETE
- DSR Portal module created and registered
- Appears in Module Dashboard with enable/disable toggle
- Settings URL: /admin.php?page=slos-dsr-settings
- Ready for Task 3.0b (Settings Page)
"
```

---

## Notes for Implementation

- **Namespace:** Module is in `ShahiLegalopsSuite\Modules\DSR_Portal`, allowing future expansion (e.g., DSR_Service, DSR_Admin as siblings)
- **Dependencies:** DSR Portal has zero dependencies; it's self-contained and can be toggled independently
- **Asset Loading:** Admin and frontend assets load conditionally (only on DSR pages/shortcodes)
- **Hooks:** All major operations fire action hooks for extensibility
- **Database:** Uses existing `wp_shahi_modules` table for state persistence
