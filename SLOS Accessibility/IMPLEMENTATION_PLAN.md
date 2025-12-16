# SLOS Accessibility Scanner - Detailed Implementation Plan

## 📋 Document Overview

**Purpose:** Comprehensive phased implementation plan with actionable tasks and subtasks  
**Based On:** IMPLEMENTATION_ROADMAP.md  
**Architecture:** Module Dashboard integration with global styling  
**Created:** December 16, 2025  
**Status:** Ready for Implementation

---

## 🏗️ ARCHITECTURE PRINCIPLES

### ✅ EXISTING INFRASTRUCTURE (Already Built - DO NOT RECREATE)

Based on ShahiTemplate Strategic Implementation Plan and completion reports, the following infrastructure already exists in the plugin:

**✅ Core Foundation (Phase 1 - COMPLETED)**
1. **PSR-4 Autoloading System** (`includes/Core/Autoloader.php`)
   - Namespace: `ShahiLegalopsSuite\`
   - Automatic class loading from `/includes/` directory
   - No manual require statements needed

2. **Plugin Core Architecture** (`includes/Core/`)
   - `Plugin.php` - Main plugin orchestration
   - `Loader.php` - Centralized hook manager (actions/filters)
   - `Activator.php` - Database table creation, options setup
   - `Deactivator.php` - Cleanup on deactivation
   - `Assets.php` - Asset enqueuing system with conditional loading

3. **Security Layer** (`includes/Core/Security.php`)
   - Nonce generation/verification
   - Capability checking
   - Input sanitization wrappers
   - Output escaping helpers
   - CSRF/XSS protection

4. **Database Infrastructure**
   - Uses WordPress `$wpdb` API
   - `dbDelta()` for table creation
   - Migration system ready
   - Database tables: `wp_shahi_analytics`, `wp_shahi_modules`, `wp_shahi_onboarding`

**✅ Module System (Phase 4 - COMPLETED)**
1. **Module Architecture** (`includes/Modules/`)
   - `Module.php` - Abstract base class for all modules
   - `ModuleManager.php` - Singleton registry managing all modules
   - `SEO_Module.php`, `Security_Module.php` - Example implementations
   
2. **Module Base Class Properties**
   ```php
   abstract class Module {
       protected $key;           // Unique identifier
       protected $enabled;       // Enable/disable state
       protected $settings;      // Module settings array
       protected $dependencies;  // Required modules
       
       // Abstract methods (must implement)
       abstract public function get_key();
       abstract public function get_name();
       abstract public function get_description();
       abstract public function get_icon();
       
       // Optional methods
       public function get_version()
       public function get_author()
       public function get_category()
       public function is_enabled()
       // ... lifecycle hooks
   }
   ```

3. **ModuleManager Methods**
   - `register(Module $module)` - Register new module
   - `unregister($key)` - Remove module
   - `get_module($key)` - Retrieve specific module
   - `get_modules($enabled_only)` - Get all modules
   - `initialize_modules()` - Run on init hook

**✅ Admin Interface (Phase 2 & 3 - COMPLETED)**
1. **Module Dashboard** (`includes/Admin/ModuleDashboard.php`)
   - **Premium 3D card interface** with animations
   - Module cards display: icon, name, description, category, priority badges
   - **Interactive toggle switch** (animated with icons)
   - **Settings gear icon** (opens modal or dedicated page)
   - **Real-time statistics** (usage count, performance score, last used)
   - **Bulk actions** (enable/disable all modules)
   - **Advanced filtering** (search, status, category)
   - **AJAX operations** (no page reload)
   - Template: `templates/admin/module-dashboard.php`

2. **Dashboard Statistics**
   - `get_modules_with_stats()` - Enhanced module data
   - `calculate_dashboard_stats()` - Metrics computation
   - `ajax_toggle_module()` - Enable/disable handler
   - `ajax_bulk_action()` - Bulk operations

**✅ Global Styling System (Phase 2 - COMPLETED)**
1. **CSS Variables** (`assets/css/admin-global.css`)
   ```css
   --shahi-bg-primary: #0a0e27;
   --shahi-bg-secondary: #141834;
   --shahi-bg-tertiary: #1e2542;
   --shahi-accent-primary: #00d4ff;
   --shahi-accent-secondary: #7c3aed;
   --shahi-accent-gradient: linear-gradient(135deg, #00d4ff 0%, #7c3aed 100%);
   --shahi-text-primary: #ffffff;
   --shahi-text-secondary: #a8b2d1;
   --shahi-border: #2d3561;
   --shahi-shadow: 0 4px 20px rgba(0, 212, 255, 0.1);
   --shahi-space-sm: 16px;
   --shahi-radius-md: 12px;
   --shahi-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
   ```

2. **UI Components** (`assets/css/components.css`)
   - `.shahi-card` - Cards with glassmorphism
   - `.shahi-btn` - Gradient buttons
   - `.shahi-stat-card` - Animated stats
   - `.shahi-toggle` - Modern toggle switches
   - `.shahi-modal` - Dark themed modals
   - Loading animations, tooltips, notifications

3. **Asset Loading** (`includes/Core/Assets.php`)
   - Conditional loading per page
   - `admin-global.css` - Global admin styles
   - `components.css` - Reusable UI components
   - `animations.css` - Transition effects
   - `utilities.css` - Helper classes
   - Minified versions with unminified sources

**✅ Translation System (Phase 1 - COMPLETED)**
- Text domain: `shahi-legalops-suite`
- `includes/Core/I18n.php` - Internationalization class
- `languages/shahi-legalops-suite.pot` - Translation template
- All strings wrapped: `__()`, `_e()`, `esc_html__()`, `_n()`

**✅ REST API Foundation (Phase 5 - READY)**
- REST API namespace: `shahi-legalops-suite/v1`
- Authentication: Cookie-based (logged-in users)
- Error responses standardized
- Nonce verification for endpoints

---

### 🆕 NEW MODULE INTEGRATION REQUIREMENTS

**Your Accessibility Scanner module must:**

1. **Extend Existing Module Architecture**
   ```php
   namespace ShahiLegalopsSuite\Modules\AccessibilityScanner;
   
   use ShahiLegalopsSuite\Modules\Module; // Extend base class
   
   class AccessibilityScanner extends Module {
       protected $key = 'accessibility-scanner';
       protected $name = 'Accessibility Scanner';
       protected $description = 'Comprehensive WCAG 2.2 accessibility scanning and fixes';
       protected $icon = 'dashicons-universal-access-alt';
       protected $category = 'compliance';
       
       // Implement required abstract methods
       public function get_key() { return $this->key; }
       public function get_name() { return $this->name; }
       public function get_description() { return $this->description; }
       public function get_icon() { return $this->icon; }
   }
   ```

2. **Register in ModuleManager**
   - Add to `includes/Modules/ModuleManager.php` in `register_default_modules()`:
   ```php
   $this->register(new \ShahiLegalopsSuite\Modules\AccessibilityScanner\AccessibilityScanner());
   ```

3. **Use Global Styling System**
   - **DO NOT create custom color schemes**
   - Use existing CSS variables from `admin-global.css`
   - Extend `components.css` with `.shahi-a11y-*` prefixed classes
   - Follow dark futuristic theme (cyberpunk aesthetics)

4. **Follow Asset Loading Pattern**
   - Add module-specific CSS to `includes/Core/Assets.php`:
   ```php
   if ($current_page === 'accessibility-scanner') {
       wp_enqueue_style('shahi-accessibility-scanner', 
           SHAHI_LEGALOPS_URL . 'assets/css/accessibility-scanner.css',
           ['shahi-admin-global', 'shahi-components'],
           SHAHI_LEGALOPS_VERSION
       );
   }
   ```

5. **Module Card Integration**
   - Module appears automatically in Module Dashboard
   - Toggle switch for enable/disable (handled by ModuleManager)
   - Settings icon opens dedicated admin page
   - Stats display: scans run, issues found, fixes applied

### 🎯 File Structure for NEW Module

```
includes/Modules/AccessibilityScanner/
├── AccessibilityScanner.php (main module - extends Module base)
├── Admin/
│   ├── Settings.php
│   ├── ScanResults.php
│   ├── IssueManager.php
│   └── Reports.php
├── Scanner/
│   ├── Engine.php
│   ├── AbstractChecker.php
│   ├── CheckerRegistry.php
│   └── Checkers/ (60+ checkers)
├── Fixes/
│   ├── FixEngine.php
│   ├── AbstractFix.php
│   └── Fixes/ (25+ fixes)
├── Widget/
│   ├── WidgetLoader.php
│   └── Features/
├── AI/
│   ├── AltTextGenerator.php
│   └── ContentAnalyzer.php
├── API/
│   └── RestController.php
├── CLI/
│   └── Commands.php
└── Database/
    ├── Schema.php
    └── Migrations/

assets/css/
└── accessibility-scanner.css (module-specific)

assets/js/
└── accessibility-scanner/
    ├── admin.js
    ├── widget.js
    └── fixes.js

templates/admin/accessibility-scanner/
├── dashboard.php
├── scan-results.php
├── settings.php
└── reports.php
```

---

## 📅 PHASE 1: FOUNDATION & CORE SCANNER (Months 1-3)

### **MONTH 1: Project Setup & Database Foundation**

---

#### **WEEK 1: Environment Setup & Module Registration**

##### **Task 1.1: Development Environment Setup** ✅ MOSTLY COMPLETE
**Priority:** P0 (Critical)  
**Estimated Time:** 2 hours (reduced from 8 - infrastructure exists)  
**Assigned To:** DevOps/Lead Developer

**Status:** ✅ Core infrastructure already exists. Only module-specific setup needed.

**✅ Already Built (Do Not Recreate):**
- ✅ PSR-4 Autoloader configured (`includes/Core/Autoloader.php`)
- ✅ WordPress environment running
- ✅ PHP, MySQL, Node.js configured
- ✅ `phpcs.xml` exists in root
- ✅ `phpstan.neon` exists in root
- ✅ Composer dependencies installed
- ✅ Testing framework ready

**🆕 Remaining Subtasks:**
1. [ ] **Create feature branch** (10 min)
   - Branch name: `feature/accessibility-scanner`
   - Base branch: `develop`
   - Branch protection rules applied

2. [ ] **Install module-specific dependencies** (30 min)
   ```bash
   # HTML parser for accessibility scanning
   composer require masterminds/html5-php ^2.8
   
   # DOM manipulation library
   composer require symfony/dom-crawler ^6.0
   
   # WCAG validation rules
   composer require quail/quail ^3.0 || composer require --dev phpunit/phpunit
   ```

3. [ ] **Configure module-specific test suite** (1 hour)
   - Create test directory: `tests/Unit/AccessibilityScanner/`
   - Create PHPUnit configuration for module tests
   - Set up mock data for accessibility issues
   - Create test fixtures (sample HTML with violations)

**Deliverables:**
- ✅ Feature branch ready
- ✅ HTML parsing dependencies installed
- ✅ Module test suite configured

**Acceptance Criteria:**
- Can parse HTML with `HTML5-PHP` library
- Module tests run in isolation: `phpunit tests/Unit/AccessibilityScanner/`
- Mock HTML fixtures available for testing

---

##### **Task 1.2: Create Module Directory Structure**
**Priority:** P0 (Critical)  
**Estimated Time:** 1 hour (reduced from 4 - follow existing pattern)  
**Assigned To:** Lead Developer

**Status:** Directory structure follows established plugin architecture.

**✅ Reference Pattern (From existing modules like SEO_Module, Security_Module):**
```
includes/Modules/{ModuleName}/
├── {ModuleName}.php (Main module class)
└── [Feature subdirectories as needed]
```

**🆕 Subtasks:**
1. [ ] **Create main module directory** (5 min)
   ```bash
   cd includes/Modules
   mkdir -p AccessibilityScanner
   ```

2. [ ] **Create feature subdirectories** (15 min)
   ```bash
   cd includes/Modules/AccessibilityScanner
   mkdir -p Admin Scanner Fixes Widget AI API CLI Database
   mkdir -p Scanner/Checkers Fixes/Fixes Widget/Features
   mkdir -p API/Endpoints Database/Migrations
   ```

3. [ ] **Create asset directories** (10 min)
   ```bash
   # Following existing pattern (separate folder per module)
   mkdir -p assets/css/accessibility-scanner
   mkdir -p assets/js/accessibility-scanner
   mkdir -p assets/images/accessibility-scanner
   ```

4. [ ] **Create template directories** (10 min)
   ```bash
   # Following existing pattern: templates/admin/{module-name}/
   mkdir -p templates/admin/accessibility-scanner
   mkdir -p templates/widgets/accessibility-scanner
   ```

5. [ ] **Create test directories** (10 min)
   ```bash
   mkdir -p tests/Unit/AccessibilityScanner
   mkdir -p tests/Integration/AccessibilityScanner
   mkdir -p tests/E2E/AccessibilityScanner
   ```

6. [ ] **Create placeholder .gitkeep files** (10 min)
   ```bash
   find includes/Modules/AccessibilityScanner -type d -exec touch {}/.gitkeep \;
   ```

**Deliverables:**
- ✅ Complete directory structure
- ✅ Follows existing module pattern
- ✅ Test directories ready

**Acceptance Criteria:**
- Directory structure matches existing modules (SEO_Module, Security_Module)
- All subdirectories have proper PSR-4 namespace alignment
- `.gitkeep` files ensure empty directories are tracked

---

##### **Task 1.3: Module Class Implementation** ✅ USE EXISTING PATTERN
**Priority:** P0 (Critical)  
**Estimated Time:** 6 hours (reduced from 12 - extend existing base)  
**Assigned To:** Lead Developer

**Status:** Module architecture exists. Extend `Module` base class, register in `ModuleManager`.

**✅ Existing Infrastructure to Use:**
- ✅ `includes/Modules/Module.php` - Abstract base class
- ✅ `includes/Modules/ModuleManager.php` - Registry system
- ✅ `includes/Modules/SEO_Module.php` - Reference implementation
- ✅ `includes/Admin/ModuleDashboard.php` - Module card UI

**🆕 Subtasks:**
1. [ ] **Create main module class** (2 hours)
   - File: `includes/Modules/AccessibilityScanner/AccessibilityScanner.php`
   
   ```php
   <?php
   /**
    * Accessibility Scanner Module
    *
    * @package    ShahiLegalopsSuite
    * @subpackage Modules\AccessibilityScanner
    * @license    GPL-3.0+
    * @since      1.0.0
    */
   
   namespace ShahiLegalopsSuite\Modules\AccessibilityScanner;
   
   use ShahiLegalopsSuite\Modules\Module;
   
   if (!defined('ABSPATH')) {
       exit;
   }
   
   /**
    * AccessibilityScanner Module Class
    *
    * Comprehensive WCAG 2.2 accessibility scanning and automatic fixes.
    *
    * @since 1.0.0
    */
   class AccessibilityScanner extends Module {
       
       /**
        * Module unique key
        * @var string
        */
       protected $key = 'accessibility-scanner';
       
       /**
        * Get module unique key
        * @return string
        */
       public function get_key() {
           return 'accessibility-scanner';
       }
       
       /**
        * Get module name
        * @return string
        */
       public function get_name() {
           return __('Accessibility Scanner', 'shahi-legalops-suite');
       }
       
       /**
        * Get module description
        * @return string
        */
       public function get_description() {
           return __('Comprehensive WCAG 2.2 accessibility scanning with 60+ automated checks, 25+ one-click fixes, and AI-powered features.', 'shahi-legalops-suite');
       }
       
       /**
        * Get module icon (Dashicon or custom)
        * @return string
        */
       public function get_icon() {
           return 'dashicons-universal-access-alt';
       }
       
       /**
        * Get module category
        * @return string
        */
       public function get_category() {
           return 'compliance';
       }
       
       /**
        * Get module version
        * @return string
        */
       public function get_version() {
           return '1.0.0';
       }
       
       /**
        * Get module priority (for dashboard sorting)
        * @return string high|medium|low
        */
       public function get_priority() {
           return 'high';
       }
       
       /**
        * Initialize module (called when enabled)
        *
        * @return void
        */
       public function init() {
           // Register admin menu pages
           add_action('admin_menu', [$this, 'register_admin_pages'], 20);
           
           // Enqueue assets
           add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
           
           // Register AJAX handlers
           add_action('wp_ajax_shahi_a11y_scan', [$this, 'ajax_run_scan']);
           add_action('wp_ajax_shahi_a11y_fix', [$this, 'ajax_apply_fix']);
           
           // Register REST API endpoints
           add_action('rest_api_init', [$this, 'register_rest_routes']);
           
           // Register WP-CLI commands (if in CLI context)
           if (defined('WP_CLI') && WP_CLI) {
               \WP_CLI::add_command('a11y', 'ShahiLegalopsSuite\\Modules\\AccessibilityScanner\\CLI\\Commands');
           }
       }
       
       /**
        * Module activation hook
        *
        * @return void
        */
       public function activate() {
           // Create database tables
           $this->create_database_tables();
           
           // Set default settings
           $default_settings = [
               'scan_frequency' => 'weekly',
               'auto_fix_enabled' => false,
               'wcag_level' => 'AA',
               'widget_enabled' => false,
               'ai_enabled' => false,
           ];
           
           update_option('shahi_a11y_settings', $default_settings);
       }
       
       /**
        * Module deactivation hook
        *
        * @return void
        */
       public function deactivate() {
           // Clear scheduled scans
           wp_clear_scheduled_hook('shahi_a11y_scheduled_scan');
           
           // Clear transients
           delete_transient('shahi_a11y_scan_results');
       }
       
       /**
        * Create database tables for module
        *
        * @return void
        */
       private function create_database_tables() {
           require_once ABSPATH . 'wp-admin/includes/upgrade.php';
           
           global $wpdb;
           $charset_collate = $wpdb->get_charset_collate();
           
           // See Database Schema section for full SQL
           // Tables: scans, issues, fixes_log, scan_history, reports, widget_sessions, ai_suggestions
           
           $tables_sql = $this->get_database_schema();
           
           foreach ($tables_sql as $sql) {
               dbDelta($sql);
           }
       }
       
       /**
        * Get settings URL
        *
        * @return string
        */
       public function get_settings_url() {
           return admin_url('admin.php?page=shahi-accessibility-settings');
       }
       
       /**
        * Get documentation URL
        *
        * @return string
        */
       public function get_documentation_url() {
           return 'https://shahilegalops.com/docs/accessibility-scanner/';
       }
       
       /**
        * Register admin menu pages
        *
        * @return void
        */
       public function register_admin_pages() {
           // Add submenu under Module Dashboard
           add_submenu_page(
               'shahi-module-dashboard',
               __('Accessibility Scanner', 'shahi-legalops-suite'),
               __('Accessibility', 'shahi-legalops-suite'),
               'manage_options',
               'shahi-accessibility',
               [$this, 'render_dashboard_page']
           );
           
           // Settings page
           add_submenu_page(
               'shahi-accessibility',
               __('Accessibility Settings', 'shahi-legalops-suite'),
               __('Settings', 'shahi-legalops-suite'),
               'manage_options',
               'shahi-accessibility-settings',
               [$this, 'render_settings_page']
           );
       }
       
       // ... Additional methods for AJAX, REST API, rendering, etc.
   }
   ```

2. [ ] **Register module in ModuleManager** (30 min)
   - File: `includes/Modules/ModuleManager.php`
   - Method: `register_default_modules()`
   - Add after existing module registrations:
   
   ```php
   // Register Accessibility Scanner module
   if (class_exists('ShahiLegalopsSuite\\Modules\\AccessibilityScanner\\AccessibilityScanner')) {
       $this->register(new \ShahiLegalopsSuite\Modules\AccessibilityScanner\AccessibilityScanner());
   }
   ```

3. [ ] **Test module registration** (1 hour)
   - Verify autoloader finds the class
   - Check Module Dashboard displays new module card
   - Test enable/disable toggle
   - Verify settings icon appears

4. [ ] **Add module statistics methods** (2 hours)
   ```php
   public function get_stats() {
       return [
           'scans_run' => $this->get_total_scans(),
           'issues_found' => $this->get_total_issues(),
           'fixes_applied' => $this->get_total_fixes(),
           'last_scan' => $this->get_last_scan_date(),
           'performance_score' => $this->calculate_average_score(),
       ];
   }
   ```

5. [ ] **Implement module dependencies** (30 min)
   - No dependencies required (standalone module)
   - Future: May depend on Analytics module for tracking

**Deliverables:**
- ✅ Module class created extending `Module` base
- ✅ Module registered in `ModuleManager`
- ✅ Module card appears in Module Dashboard
- ✅ Toggle and settings functionality working

**Acceptance Criteria:**
- Module card visible at `/wp-admin/admin.php?page=shahi-module-dashboard`
- Enable/disable toggle works via AJAX
- Settings icon links to `/wp-admin/admin.php?page=shahi-accessibility-settings`
- Module follows existing architecture patterns (SEO_Module, Security_Module)
- PSR-4 autoloading works: `ShahiLegalopsSuite\Modules\AccessibilityScanner\AccessibilityScanner`

---

##### **Task 1.4: Module Settings Architecture** ✅ USE EXISTING ASSET PATTERN
**Priority:** P0 (Critical)  
**Estimated Time:** 6 hours (reduced from 10 - follow existing Assets.php pattern)  
**Assigned To:** Lead Developer

**Status:** Asset management system exists. Add module-specific enqueues to `Assets.php`.

**✅ Existing Infrastructure:**
- ✅ `includes/Core/Assets.php` - Centralized asset enqueuing
- ✅ Conditional loading per page implemented
- ✅ Global styles available: `admin-global.css`, `components.css`, `animations.css`
- ✅ Minification workflow exists

**🆕 Subtasks:**
1. [ ] **Create Settings controller class** (2 hours)
   - File: `includes/Modules/AccessibilityScanner/Admin/Settings.php`
   
   ```php
   <?php
   namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Admin;
   
   class Settings {
       
       public function __construct() {
           add_action('admin_init', [$this, 'register_settings']);
       }
       
       /**
        * Register settings using WordPress Settings API
        */
       public function register_settings() {
           register_setting(
               'shahi_a11y_settings_group',
               'shahi_a11y_settings',
               [$this, 'sanitize_settings']
           );
           
           // Section: General Settings
           add_settings_section(
               'shahi_a11y_general',
               __('General Settings', 'shahi-legalops-suite'),
               [$this, 'render_general_section'],
               'shahi-accessibility-settings'
           );
           
           // Field: WCAG Level
           add_settings_field(
               'wcag_level',
               __('WCAG Compliance Level', 'shahi-legalops-suite'),
               [$this, 'render_wcag_level_field'],
               'shahi-accessibility-settings',
               'shahi_a11y_general'
           );
           
           // Field: Auto-fix
           add_settings_field(
               'auto_fix_enabled',
               __('Enable Auto-Fix', 'shahi-legalops-suite'),
               [$this, 'render_auto_fix_field'],
               'shahi-accessibility-settings',
               'shahi_a11y_general'
           );
           
           // ... Additional fields
       }
       
       /**
        * Sanitize settings before saving
        */
       public function sanitize_settings($input) {
           $sanitized = [];
           
           $sanitized['wcag_level'] = in_array($input['wcag_level'], ['A', 'AA', 'AAA']) 
               ? $input['wcag_level'] 
               : 'AA';
           
           $sanitized['auto_fix_enabled'] = isset($input['auto_fix_enabled']) ? 1 : 0;
           $sanitized['scan_frequency'] = sanitize_text_field($input['scan_frequency']);
           $sanitized['widget_enabled'] = isset($input['widget_enabled']) ? 1 : 0;
           $sanitized['ai_enabled'] = isset($input['ai_enabled']) ? 1 : 0;
           
           return $sanitized;
       }
       
       /**
        * Render settings page
        */
       public function render() {
           include SHAHI_LEGALOPS_PATH . 'templates/admin/accessibility-scanner/settings.php';
       }
   }
   ```

2. [ ] **Add module assets to Assets.php** (2 hours)
   - File: `includes/Core/Assets.php`
   - Method: `enqueue_admin_styles()` and `enqueue_admin_scripts()`
   
   ```php
   // In enqueue_admin_styles() method
   if ($current_screen->id === 'shahi-module-dashboard_page_shahi-accessibility' ||
       $current_screen->id === 'admin_page_shahi-accessibility-settings') {
       
       wp_enqueue_style(
           'shahi-accessibility-scanner',
           SHAHI_LEGALOPS_URL . 'assets/css/accessibility-scanner/admin.css',
           ['shahi-admin-global', 'shahi-components'],
           SHAHI_LEGALOPS_VERSION
       );
   }
   
   // In enqueue_admin_scripts() method
   if ($current_screen->id === 'shahi-module-dashboard_page_shahi-accessibility') {
       wp_enqueue_script(
           'shahi-accessibility-admin',
           SHAHI_LEGALOPS_URL . 'assets/js/accessibility-scanner/admin.js',
           ['jquery', 'wp-i18n'],
           SHAHI_LEGALOPS_VERSION,
           true
       );
       
       wp_localize_script('shahi-accessibility-admin', 'shahiA11y', [
           'ajaxUrl' => admin_url('admin-ajax.php'),
           'nonce' => wp_create_nonce('shahi_a11y_nonce'),
           'strings' => [
               'scanRunning' => __('Scan in progress...', 'shahi-legalops-suite'),
               'scanComplete' => __('Scan completed', 'shahi-legalops-suite'),
               'fixApplied' => __('Fix applied successfully', 'shahi-legalops-suite'),
           ],
       ]);
   }
   ```

3. [ ] **Create module CSS file** (1 hour)
   - File: `assets/css/accessibility-scanner/admin.css`
   - **Use existing CSS variables** (DO NOT create custom colors)
   
   ```css
   /* Accessibility Scanner Admin Styles */
   /* Uses global CSS variables from admin-global.css */
   
   .shahi-a11y-dashboard {
       padding: var(--shahi-space-md);
   }
   
   .shahi-a11y-stat-card {
       /* Extends .shahi-stat-card from components.css */
       background: var(--shahi-bg-secondary);
       border: 1px solid var(--shahi-border);
       border-radius: var(--shahi-radius-md);
       padding: var(--shahi-space-md);
       box-shadow: var(--shahi-shadow);
       transition: var(--shahi-transition);
   }
   
   .shahi-a11y-stat-card:hover {
       box-shadow: var(--shahi-hover-shadow);
       transform: translateY(-2px);
   }
   
   .shahi-a11y-issue-severity-high {
       color: #ff4757;
       background: rgba(255, 71, 87, 0.1);
   }
   
   .shahi-a11y-issue-severity-medium {
       color: #ffa502;
       background: rgba(255, 165, 2, 0.1);
   }
   
   .shahi-a11y-issue-severity-low {
       color: var(--shahi-accent-primary);
       background: rgba(0, 212, 255, 0.1);
   }
   
   /* Scan results table */
   .shahi-a11y-results-table {
       width: 100%;
       border-collapse: collapse;
       background: var(--shahi-bg-secondary);
       border-radius: var(--shahi-radius-md);
       overflow: hidden;
   }
   
   .shahi-a11y-results-table th {
       background: var(--shahi-bg-tertiary);
       color: var(--shahi-text-primary);
       padding: var(--shahi-space-sm);
       text-align: left;
       font-weight: 600;
   }
   
   .shahi-a11y-results-table td {
       padding: var(--shahi-space-sm);
       border-top: 1px solid var(--shahi-border);
       color: var(--shahi-text-secondary);
   }
   
   /* Widget preview */
   .shahi-a11y-widget-preview {
       position: fixed;
       bottom: 20px;
       right: 20px;
       z-index: 9999;
       /* Widget styles will be defined in widget.css */
   }
   ```

4. [ ] **Create module JavaScript file** (1 hour)
   - File: `assets/js/accessibility-scanner/admin.js`
   
   ```javascript
   (function($) {
       'use strict';
       
       const ShahiA11y = {
           
           init: function() {
               this.bindEvents();
               this.initCharts();
           },
           
           bindEvents: function() {
               $('#shahi-a11y-run-scan').on('click', this.runScan);
               $('.shahi-a11y-apply-fix').on('click', this.applyFix);
               $('#shahi-a11y-export-report').on('click', this.exportReport);
           },
           
           runScan: function(e) {
               e.preventDefault();
               
               const $btn = $(this);
               const url = $('#shahi-a11y-scan-url').val();
               
               $btn.prop('disabled', true).text(shahiA11y.strings.scanRunning);
               
               $.ajax({
                   url: shahiA11y.ajaxUrl,
                   type: 'POST',
                   data: {
                       action: 'shahi_a11y_scan',
                       nonce: shahiA11y.nonce,
                       url: url
                   },
                   success: function(response) {
                       if (response.success) {
                           ShahiA11y.displayResults(response.data);
                       }
                   },
                   complete: function() {
                       $btn.prop('disabled', false).text('Run Scan');
                   }
               });
           },
           
           applyFix: function(e) {
               e.preventDefault();
               // Implementation
           },
           
           displayResults: function(data) {
               // Implementation
           },
           
           initCharts: function() {
               // Chart.js initialization for dashboard
           }
       };
       
       $(document).ready(function() {
           ShahiA11y.init();
       });
       
   })(jQuery);
   ```

**Deliverables:**
- ✅ Settings controller created
- ✅ Module assets registered in `Assets.php`
- ✅ CSS follows global variable system
- ✅ JavaScript with AJAX handlers

**Acceptance Criteria:**
- Settings page renders at `/wp-admin/admin.php?page=shahi-accessibility-settings`
- CSS uses existing `--shahi-*` variables (NO custom color schemes)
- Assets only load on module pages (conditional loading)
- JavaScript can make AJAX calls with proper nonce verification
   
   class Settings {
       private $option_group = 'shahi_accessibility_scanner';
       private $option_name = 'shahi_a11y_settings';
       
       public function __construct() {
           add_action('admin_init', [$this, 'register_settings']);
       }
       
       public function register_settings() {
           // Register settings sections and fields
       }
       
       public function render_settings_page() {
           // Render settings page using global styles
       }
   }
   ```

2. [ ] **Define settings structure**
   ```php
   private function get_default_settings() {
       return [
           'scanning' => [
               'enabled_checks' => [], // All 60 checks
               'scan_on_save' => true,
               'scan_on_publish' => true,
               'background_scanning' => true,
           ],
           'widget' => [
               'enabled' => true,
               'position' => 'bottom-right',
               'color' => '#c0c0c0',
               'enabled_features' => [], // All 70 features
           ],
           'fixes' => [
               'auto_fix_enabled' => false,
               'enabled_fixes' => [],
           ],
           'ai' => [
               'alt_text_generation' => false,
               'api_provider' => 'openai', // or 'google'
               'api_key' => '',
           ],
           'reporting' => [
               'email_notifications' => false,
               'notification_email' => get_option('admin_email'),
           ],
       ];
   }
   ```

3. [ ] **Create settings page template**
   - File: `templates/admin/accessibility-scanner/settings.php`
   - Use global styling system (CSS variables)
   - Implement tabbed interface:
     - Scanner Settings
     - Widget Settings
     - Fix Settings
     - AI Settings
     - Reporting Settings
     - Advanced Settings

4. [ ] **Implement settings save/retrieve functions**
   ```php
   public function get_setting($key, $default = null) {
       $settings = get_option($this->option_name, $this->get_default_settings());
       return $settings[$key] ?? $default;
   }
   
   public function update_setting($key, $value) {
       $settings = get_option($this->option_name, []);
       $settings[$key] = $value;
       update_option($this->option_name, $settings);
   }
   ```

5. [ ] **Add settings link to Module Card**
   - Settings icon (gear) on module card
   - Links to: `admin.php?page=shahi-accessibility-scanner-settings`
   - Or opens modal with settings (decided in design)

6. [ ] **Style settings page with global CSS**
   - Extend `assets/css/accessibility-scanner.css`
   - Use existing component classes:
     - `.shahi-settings-container`
     - `.shahi-settings-section`
     - `.shahi-form-group`
     - `.shahi-btn`
     - `.shahi-tab-navigation`

**Deliverables:**
- ✅ Settings class implemented
- ✅ Settings page template created
- ✅ Settings accessible from Module Dashboard
- ✅ Default settings defined

**Acceptance Criteria:**
- Settings page uses global styling
- Settings save/load correctly
- Module card links to settings
- All setting sections present

---

#### **WEEK 2: Database Schema & Migrations**

##### **Task 1.5: Database Schema Design**
**Priority:** P0 (Critical)  
**Estimated Time:** 16 hours  
**Assigned To:** Lead Developer + Database Specialist

**Subtasks:**
1. [ ] **Create Schema class**
   - File: `includes/Modules/AccessibilityScanner/Database/Schema.php`

2. [ ] **Define table structures**
   ```php
   <?php
   namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Database;
   
   class Schema {
       private $wpdb;
       private $charset_collate;
       
       public function __construct() {
           global $wpdb;
           $this->wpdb = $wpdb;
           $this->charset_collate = $wpdb->get_charset_collate();
       }
       
       public function create_tables() {
           $this->create_scans_table();
           $this->create_issues_table();
           $this->create_fixes_table();
           $this->create_ignores_table();
           $this->create_reports_table();
           $this->create_settings_table();
           $this->create_analytics_table();
       }
   }
   ```

3. [ ] **Create scans table**
   ```sql
   CREATE TABLE {$wpdb->prefix}slos_a11y_scans (
       id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
       post_id BIGINT UNSIGNED NULL,
       url VARCHAR(500) NOT NULL,
       scan_type ENUM('manual', 'auto', 'scheduled', 'bulk') DEFAULT 'manual',
       status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
       total_checks INT UNSIGNED DEFAULT 0,
       passed_checks INT UNSIGNED DEFAULT 0,
       failed_checks INT UNSIGNED DEFAULT 0,
       warning_checks INT UNSIGNED DEFAULT 0,
       score INT UNSIGNED DEFAULT 0,
       wcag_level ENUM('A', 'AA', 'AAA') DEFAULT 'AA',
       started_at DATETIME NULL,
       completed_at DATETIME NULL,
       created_by BIGINT UNSIGNED NULL,
       created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
       updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
       PRIMARY KEY (id),
       KEY post_id (post_id),
       KEY status (status),
       KEY created_at (created_at),
       KEY created_by (created_by)
   ) {$this->charset_collate};
   ```

4. [ ] **Create issues table**
   ```sql
   CREATE TABLE {$wpdb->prefix}slos_a11y_issues (
       id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
       scan_id BIGINT UNSIGNED NOT NULL,
       check_type VARCHAR(100) NOT NULL,
       check_name VARCHAR(200) NOT NULL,
       severity ENUM('critical', 'serious', 'moderate', 'minor') DEFAULT 'moderate',
       wcag_criterion VARCHAR(50) NULL,
       wcag_level ENUM('A', 'AA', 'AAA') DEFAULT 'AA',
       element_selector VARCHAR(500) NULL,
       element_html TEXT NULL,
       line_number INT UNSIGNED NULL,
       issue_description TEXT NULL,
       recommendation TEXT NULL,
       status ENUM('new', 'in_progress', 'fixed', 'verified', 'closed', 'ignored') DEFAULT 'new',
       priority ENUM('P0', 'P1', 'P2', 'P3', 'P4') DEFAULT 'P2',
       assigned_to BIGINT UNSIGNED NULL,
       due_date DATETIME NULL,
       fixed_at DATETIME NULL,
       fixed_by BIGINT UNSIGNED NULL,
       created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
       updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
       PRIMARY KEY (id),
       KEY scan_id (scan_id),
       KEY severity (severity),
       KEY status (status),
       KEY assigned_to (assigned_to),
       KEY wcag_criterion (wcag_criterion),
       FOREIGN KEY (scan_id) REFERENCES {$wpdb->prefix}slos_a11y_scans(id) ON DELETE CASCADE
   ) {$this->charset_collate};
   ```

5. [ ] **Create fixes table**
   ```sql
   CREATE TABLE {$wpdb->prefix}slos_a11y_fixes (
       id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
       issue_id BIGINT UNSIGNED NULL,
       fix_type VARCHAR(100) NOT NULL,
       fix_name VARCHAR(200) NOT NULL,
       fix_description TEXT NULL,
       before_html TEXT NULL,
       after_html TEXT NULL,
       applied BOOLEAN DEFAULT FALSE,
       applied_at DATETIME NULL,
       applied_by BIGINT UNSIGNED NULL,
       reverted_at DATETIME NULL,
       reverted_by BIGINT UNSIGNED NULL,
       success BOOLEAN NULL,
       error_message TEXT NULL,
       created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
       PRIMARY KEY (id),
       KEY issue_id (issue_id),
       KEY applied (applied),
       KEY fix_type (fix_type),
       FOREIGN KEY (issue_id) REFERENCES {$wpdb->prefix}slos_a11y_issues(id) ON DELETE CASCADE
   ) {$this->charset_collate};
   ```

6. [ ] **Create ignores table**
   ```sql
   CREATE TABLE {$wpdb->prefix}slos_a11y_ignores (
       id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
       issue_id BIGINT UNSIGNED NOT NULL,
       reason TEXT NULL,
       ignored_by BIGINT UNSIGNED NOT NULL,
       ignored_at DATETIME DEFAULT CURRENT_TIMESTAMP,
       expires_at DATETIME NULL,
       reopened_at DATETIME NULL,
       reopened_by BIGINT UNSIGNED NULL,
       PRIMARY KEY (id),
       KEY issue_id (issue_id),
       KEY ignored_by (ignored_by),
       FOREIGN KEY (issue_id) REFERENCES {$wpdb->prefix}slos_a11y_issues(id) ON DELETE CASCADE
   ) {$this->charset_collate};
   ```

7. [ ] **Create reports table**
   ```sql
   CREATE TABLE {$wpdb->prefix}slos_a11y_reports (
       id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
       scan_id BIGINT UNSIGNED NULL,
       report_type ENUM('summary', 'detailed', 'vpat', 'wcag-em', 'csv', 'pdf') DEFAULT 'summary',
       report_data LONGTEXT NULL,
       file_path VARCHAR(500) NULL,
       generated_by BIGINT UNSIGNED NOT NULL,
       generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
       PRIMARY KEY (id),
       KEY scan_id (scan_id),
       KEY report_type (report_type),
       FOREIGN KEY (scan_id) REFERENCES {$wpdb->prefix}slos_a11y_scans(id) ON DELETE SET NULL
   ) {$this->charset_collate};
   ```

8. [ ] **Create analytics table**
   ```sql
   CREATE TABLE {$wpdb->prefix}slos_a11y_analytics (
       id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
       event_type VARCHAR(100) NOT NULL,
       event_name VARCHAR(200) NOT NULL,
       event_data TEXT NULL,
       user_id BIGINT UNSIGNED NULL,
       ip_address VARCHAR(45) NULL,
       user_agent VARCHAR(500) NULL,
       created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
       PRIMARY KEY (id),
       KEY event_type (event_type),
       KEY user_id (user_id),
       KEY created_at (created_at)
   ) {$this->charset_collate};
   ```

9. [ ] **Add indexes for performance**
   - Composite indexes for common queries
   - Full-text indexes for search
   - Foreign key constraints

**Deliverables:**
- ✅ Schema class with all 7 tables
- ✅ Foreign key relationships defined
- ✅ Indexes for performance
- ✅ Database documentation

**Acceptance Criteria:**
- All tables created without errors
- Foreign keys working correctly
- Indexes improve query performance

---

##### **Task 1.6: Database Migration System**
**Priority:** P0 (Critical)  
**Estimated Time:** 12 hours  
**Assigned To:** Lead Developer

**Subtasks:**
1. [ ] **Create Migration base class**
   - File: `includes/Modules/AccessibilityScanner/Database/Migration.php`
   
   ```php
   <?php
   namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Database;
   
   abstract class Migration {
       protected $wpdb;
       protected $version;
       
       abstract public function up();
       abstract public function down();
       
       public function run() {
           $current_version = get_option('shahi_a11y_db_version', '0.0.0');
           if (version_compare($current_version, $this->version, '<')) {
               $this->up();
               update_option('shahi_a11y_db_version', $this->version);
           }
       }
   }
   ```

2. [ ] **Create initial migration**
   - File: `includes/Modules/AccessibilityScanner/Database/Migrations/Migration_1_0_0.php`
   
   ```php
   class Migration_1_0_0 extends Migration {
       protected $version = '1.0.0';
       
       public function up() {
           $schema = new Schema();
           $schema->create_tables();
       }
       
       public function down() {
           // Drop tables
       }
   }
   ```

3. [ ] **Create migration runner**
   ```php
   class MigrationRunner {
       public function run_migrations() {
           $migrations = [
               new Migrations\Migration_1_0_0(),
               // Future migrations...
           ];
           
           foreach ($migrations as $migration) {
               $migration->run();
           }
       }
   }
   ```

4. [ ] **Hook migrations to module activation**
   ```php
   public function activate() {
       $runner = new Database\MigrationRunner();
       $runner->run_migrations();
   }
   ```

5. [ ] **Add rollback capability**
   - Implement `down()` method in migrations
   - Create rollback command for testing
   - Backup data before migrations

6. [ ] **Create database version tracking**
   - Store version in wp_options
   - Log migration history
   - Prevent duplicate migrations

**Deliverables:**
- ✅ Migration system implemented
- ✅ Initial migration created
- ✅ Rollback capability
- ✅ Version tracking

**Acceptance Criteria:**
- Migrations run on module activation
- Database version tracked correctly
- Rollback works for testing

---

#### **WEEK 3: Scanner Engine Foundation**

##### **Task 1.7: Scanner Engine Core**
**Priority:** P0 (Critical)  
**Estimated Time:** 20 hours  
**Assigned To:** Senior PHP Developer

**Subtasks:**
1. [ ] **Create Scanner Engine class**
   - File: `includes/Modules/AccessibilityScanner/Scanner/Engine.php`
   
   ```php
   <?php
   namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner;
   
   use DOMDocument;
   use DOMXPath;
   
   class Engine {
       private $checkers = [];
       private $dom;
       private $xpath;
       private $url;
       private $html;
       private $issues = [];
       
       public function __construct() {
           $this->dom = new DOMDocument();
           libxml_use_internal_errors(true);
       }
       
       public function scan($url_or_html, $post_id = null) {
           // Scan logic
       }
       
       public function register_checker(AbstractChecker $checker) {
           $this->checkers[] = $checker;
       }
       
       private function load_html($html) {
           $this->dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
           $this->xpath = new DOMXPath($this->dom);
       }
       
       private function run_checks() {
           foreach ($this->checkers as $checker) {
               $checker->check($this->dom, $this->xpath);
               $this->issues = array_merge($this->issues, $checker->get_issues());
           }
       }
   }
   ```

2. [ ] **Create Abstract Checker base class**
   - File: `includes/Modules/AccessibilityScanner/Scanner/AbstractChecker.php`
   
   ```php
   <?php
   namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner;
   
   abstract class AbstractChecker {
       protected $issues = [];
       protected $dom;
       protected $xpath;
       
       abstract public function check($dom, $xpath);
       abstract public function get_check_name();
       abstract public function get_check_type();
       
       protected function add_issue($data) {
           $this->issues[] = array_merge([
               'check_type' => $this->get_check_type(),
               'check_name' => $this->get_check_name(),
               'severity' => 'moderate',
               'wcag_criterion' => null,
               'wcag_level' => 'AA',
           ], $data);
       }
       
       public function get_issues() {
           return $this->issues;
       }
       
       public function clear_issues() {
           $this->issues = [];
       }
   }
   ```

3. [ ] **Create Checker Registry**
   - File: `includes/Modules/AccessibilityScanner/Scanner/CheckerRegistry.php`
   
   ```php
   <?php
   namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner;
   
   class CheckerRegistry {
       private static $instance = null;
       private $checkers = [];
       
       public static function get_instance() {
           if (self::$instance === null) {
               self::$instance = new self();
           }
           return self::$instance;
       }
       
       public function register($checker_class) {
           $this->checkers[$checker_class::get_check_type()] = $checker_class;
       }
       
       public function get_all_checkers() {
           return array_map(function($class) {
               return new $class();
           }, $this->checkers);
       }
       
       public function get_checker($type) {
           if (isset($this->checkers[$type])) {
               return new $this->checkers[$type]();
           }
           return null;
       }
   }
   ```

4. [ ] **Integrate HTML5 parser**
   ```bash
   composer require masterminds/html5
   ```
   
   ```php
   use Masterminds\HTML5;
   
   private function load_html($html) {
       $html5 = new HTML5();
       $this->dom = $html5->loadHTML($html);
       $this->xpath = new DOMXPath($this->dom);
   }
   ```

5. [ ] **Implement scan workflow**
   ```php
   public function scan($url_or_html, $post_id = null) {
       // 1. Create scan record
       $scan_id = $this->create_scan_record($url_or_html, $post_id);
       
       // 2. Fetch HTML (if URL provided)
       $html = $this->fetch_html($url_or_html);
       
       // 3. Load HTML into DOM
       $this->load_html($html);
       
       // 4. Run all registered checkers
       $this->run_checks();
       
       // 5. Calculate score
       $score = $this->calculate_score();
       
       // 6. Save issues to database
       $this->save_issues($scan_id);
       
       // 7. Update scan record
       $this->update_scan_record($scan_id, $score);
       
       // 8. Return scan result
       return [
           'scan_id' => $scan_id,
           'score' => $score,
           'issues' => $this->issues,
       ];
   }
   ```

6. [ ] **Add WCAG criterion mapping**
   ```php
   private $wcag_mapping = [
       '1.1.1' => ['level' => 'A', 'name' => 'Non-text Content'],
       '1.3.1' => ['level' => 'A', 'name' => 'Info and Relationships'],
       '1.4.3' => ['level' => 'AA', 'name' => 'Contrast (Minimum)'],
       // ... all WCAG 2.2 success criteria
   ];
   ```

7. [ ] **Implement severity classification**
   ```php
   private function determine_severity($wcag_level, $element_count) {
       if ($wcag_level === 'A') {
           return 'critical';
       } elseif ($wcag_level === 'AA' && $element_count > 5) {
           return 'serious';
       } elseif ($wcag_level === 'AA') {
           return 'moderate';
       } else {
           return 'minor';
       }
   }
   ```

**Deliverables:**
- ✅ Scanner engine core implemented
- ✅ Abstract checker base class
- ✅ Checker registry system
- ✅ HTML5 parser integrated
- ✅ WCAG mapping complete

**Acceptance Criteria:**
- Engine can load and parse HTML
- Checkers can be registered
- Scan workflow executes
- Issues stored in database

---

##### **Task 1.8: First 5 Basic Checkers**
**Priority:** P0 (Critical)  
**Estimated Time:** 24 hours  
**Assigned To:** Senior PHP Developer

**Subtasks:**
1. [ ] **Create ImageChecker (alt text)**
   - File: `includes/Modules/AccessibilityScanner/Scanner/Checkers/ImageChecker.php`
   
   ```php
   <?php
   namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers;
   
   use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\AbstractChecker;
   
   class ImageChecker extends AbstractChecker {
       public static function get_check_type() {
           return 'image';
       }
       
       public function get_check_name() {
           return 'Image Alt Text Checker';
       }
       
       public function check($dom, $xpath) {
           $this->dom = $dom;
           $this->xpath = $xpath;
           
           // Check 1: Missing alt attribute
           $this->check_missing_alt();
           
           // Check 2: Empty alt on informative images
           $this->check_empty_alt_on_informative();
           
           // Check 3: Alt text quality
           $this->check_alt_quality();
       }
       
       private function check_missing_alt() {
           $images = $this->xpath->query('//img[not(@alt)]');
           foreach ($images as $img) {
               $this->add_issue([
                   'severity' => 'critical',
                   'wcag_criterion' => '1.1.1',
                   'wcag_level' => 'A',
                   'element_selector' => $this->get_selector($img),
                   'element_html' => $this->get_outer_html($img),
                   'issue_description' => 'Image is missing alt attribute',
                   'recommendation' => 'Add descriptive alt text or empty alt="" for decorative images',
               ]);
           }
       }
       
       private function check_empty_alt_on_informative() {
           // Check for images with empty alt that appear to be informative
           $images = $this->xpath->query('//img[@alt=""][not(contains(@class, "decorative"))]');
           foreach ($images as $img) {
               $src = $img->getAttribute('src');
               // Heuristic: if src contains keywords, likely informative
               if (preg_match('/(logo|banner|product|screenshot|diagram)/i', $src)) {
                   $this->add_issue([
                       'severity' => 'serious',
                       'wcag_criterion' => '1.1.1',
                       'wcag_level' => 'A',
                       'element_selector' => $this->get_selector($img),
                       'element_html' => $this->get_outer_html($img),
                       'issue_description' => 'Image appears informative but has empty alt text',
                       'recommendation' => 'Provide descriptive alt text for this image',
                   ]);
               }
           }
       }
       
       private function check_alt_quality() {
           $images = $this->xpath->query('//img[@alt]');
           foreach ($images as $img) {
               $alt = $img->getAttribute('alt');
               
               // Check for filename as alt
               if (preg_match('/\.(jpg|png|gif|svg)$/i', $alt)) {
                   $this->add_issue([
                       'severity' => 'moderate',
                       'wcag_criterion' => '1.1.1',
                       'wcag_level' => 'A',
                       'element_selector' => $this->get_selector($img),
                       'element_html' => $this->get_outer_html($img),
                       'issue_description' => 'Alt text is a filename',
                       'recommendation' => 'Replace filename with descriptive text',
                   ]);
               }
               
               // Check for redundant phrases
               if (preg_match('/(image of|picture of|photo of)/i', $alt)) {
                   $this->add_issue([
                       'severity' => 'minor',
                       'wcag_criterion' => '1.1.1',
                       'wcag_level' => 'A',
                       'element_selector' => $this->get_selector($img),
                       'element_html' => $this->get_outer_html($img),
                       'issue_description' => 'Alt text contains redundant phrases',
                       'recommendation' => 'Remove "image of", "picture of", etc.',
                   ]);
               }
           }
       }
   }
   ```

2. [ ] **Create HeadingChecker**
   - File: `includes/Modules/AccessibilityScanner/Scanner/Checkers/HeadingChecker.php`
   
   - Check for missing H1
   - Check for multiple H1s
   - Check heading hierarchy (no skipped levels)
   - Check empty headings

3. [ ] **Create LinkChecker**
   - File: `includes/Modules/AccessibilityScanner/Scanner/Checkers/LinkChecker.php`
   
   - Check for empty links
   - Check for ambiguous link text ("click here", "read more")
   - Check for links with same text, different destinations
   - Check for new window/tab without warning

4. [ ] **Create FormChecker**
   - File: `includes/Modules/AccessibilityScanner/Scanner/Checkers/FormChecker.php`
   
   - Check for missing form labels
   - Check for placeholder as label
   - Check for required field indicators
   - Check for fieldset/legend on radio groups

5. [ ] **Create ARIAChecker**
   - File: `includes/Modules/AccessibilityScanner/Scanner/Checkers/ARIAChecker.php`
   
   - Check for invalid ARIA roles
   - Check for missing required ARIA attributes
   - Check for redundant ARIA (e.g., role="button" on <button>)

6. [ ] **Add helper methods to AbstractChecker**
   ```php
   protected function get_selector($element) {
       // Generate CSS selector for element
   }
   
   protected function get_outer_html($element) {
       // Get element HTML
   }
   
   protected function get_xpath_selector($element) {
       // Generate XPath for element
   }
   ```

7. [ ] **Register checkers in Engine**
   ```php
   public function __construct() {
       parent::__construct();
       
       // Register all checkers
       $registry = CheckerRegistry::get_instance();
       $registry->register(Checkers\ImageChecker::class);
       $registry->register(Checkers\HeadingChecker::class);
       $registry->register(Checkers\LinkChecker::class);
       $registry->register(Checkers\FormChecker::class);
       $registry->register(Checkers\ARIAChecker::class);
   }
   ```

**Deliverables:**
- ✅ 5 checker classes implemented
- ✅ Each checker has 3-4 specific checks
- ✅ All checkers registered
- ✅ Helper methods added

**Acceptance Criteria:**
- Each checker detects issues correctly
- Issues stored with proper severity
- WCAG criteria mapped
- Recommendations provided

---

#### **WEEK 4: Admin UI & Basic Integration**

##### **Task 1.9: Create Scan Results Page**
**Priority:** P0 (Critical)  
**Estimated Time:** 20 hours  
**Assigned To:** Frontend Developer

**Subtasks:**
1. [ ] **Create admin page class**
   - File: `includes/Modules/AccessibilityScanner/Admin/ScanResults.php`
   
   ```php
   <?php
   namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Admin;
   
   class ScanResults {
       public function __construct() {
           add_action('admin_menu', [$this, 'add_menu_page']);
           add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
       }
       
       public function add_menu_page() {
           // This page is accessed from Module Dashboard
           // Add as submenu under "ShahiLegalOps Suite"
           add_submenu_page(
               'shahi-legalops-suite',
               __('Accessibility Scanner', 'shahi-legalops-suite'),
               __('Accessibility', 'shahi-legalops-suite'),
               'manage_shahi_accessibility',
               'shahi-accessibility-scanner',
               [$this, 'render_page']
           );
       }
       
       public function render_page() {
           include SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/accessibility-scanner/scan-results.php';
       }
   }
   ```

2. [ ] **Create scan results template**
   - File: `templates/admin/accessibility-scanner/scan-results.php`
   
   - Use global styling system
   - Implement layout:
     ```html
     <div class="wrap shahi-accessibility-scanner">
         <!-- Header with stats -->
         <div class="shahi-dashboard-header">
             <h1 class="shahi-page-title">
                 <span class="shahi-icon-badge">
                     <span class="dashicons dashicons-universal-access-alt"></span>
                 </span>
                 Accessibility Scanner
             </h1>
         </div>
         
         <!-- Stats cards -->
         <div class="shahi-stats-container">
             <div class="shahi-stat-card">
                 <div class="shahi-stat-value">95</div>
                 <div class="shahi-stat-label">Accessibility Score</div>
             </div>
             <!-- More stat cards... -->
         </div>
         
         <!-- Scan results table -->
         <div class="shahi-content-card">
             <div class="shahi-card-header">
                 <h2>Recent Scans</h2>
                 <button class="shahi-btn shahi-btn-gradient">
                     New Scan
                 </button>
             </div>
             <div class="shahi-card-body">
                 <table class="shahi-table">
                     <!-- Scan results... -->
                 </table>
             </div>
         </div>
     </div>
     ```

3. [ ] **Style scan results page**
   - File: `assets/css/accessibility-scanner.css`
   
   ```css
   /* Use global CSS variables */
   .shahi-accessibility-scanner {
       /* Inherits from .wrap and global styles */
   }
   
   .shahi-scan-status-badge {
       display: inline-flex;
       align-items: center;
       padding: 4px 12px;
       border-radius: var(--shahi-radius-sm);
       font-size: 12px;
       font-weight: 600;
   }
   
   .shahi-scan-status-completed {
       background: rgba(144, 238, 144, 0.1);
       color: var(--shahi-accent-success);
       border: 1px solid var(--shahi-accent-success);
   }
   
   .shahi-scan-score {
       display: flex;
       align-items: center;
       gap: 8px;
   }
   
   .shahi-score-bar {
       width: 100px;
       height: 8px;
       background: var(--shahi-bg-tertiary);
       border-radius: var(--shahi-radius-sm);
       overflow: hidden;
   }
   
   .shahi-score-fill {
       height: 100%;
       background: var(--shahi-gradient-success);
       transition: width var(--shahi-transition-base);
   }
   ```

4. [ ] **Implement scan results table**
   - Display: Scan ID, URL/Page, Date, Score, Status, Actions
   - Sortable columns
   - Pagination
   - Filter by status, date, score
   - Bulk actions (delete, export)

5. [ ] **Add AJAX handlers**
   ```php
   add_action('wp_ajax_shahi_a11y_run_scan', [$this, 'ajax_run_scan']);
   add_action('wp_ajax_shahi_a11y_get_scan_results', [$this, 'ajax_get_results']);
   add_action('wp_ajax_shahi_a11y_delete_scan', [$this, 'ajax_delete_scan']);
   ```

6. [ ] **Create JavaScript for interactions**
   - File: `assets/js/accessibility-scanner-admin.js`
   
   ```javascript
   (function($) {
       'use strict';
       
       const AccessibilityScannerAdmin = {
           init: function() {
               this.bindEvents();
               this.loadScans();
           },
           
           bindEvents: function() {
               $('.shahi-run-scan').on('click', this.runScan);
               $('.shahi-view-scan').on('click', this.viewScan);
               $('.shahi-delete-scan').on('click', this.deleteScan);
           },
           
           runScan: function(e) {
               e.preventDefault();
               // AJAX call to run scan
           },
           
           loadScans: function() {
               // AJAX call to load scans
           }
       };
       
       $(document).ready(function() {
           AccessibilityScannerAdmin.init();
       });
   })(jQuery);
   ```

7. [ ] **Enqueue assets correctly**
   ```php
   public function enqueue_assets($hook) {
       if ($hook !== 'toplevel_page_shahi-accessibility-scanner') {
           return;
       }
       
       wp_enqueue_style(
           'shahi-accessibility-scanner',
           SHAHI_LEGALOPS_SUITE_URL . 'assets/css/accessibility-scanner.css',
           ['shahi-admin-global', 'shahi-components'],
           SHAHI_LEGALOPS_SUITE_VERSION
       );
       
       wp_enqueue_script(
           'shahi-accessibility-scanner-admin',
           SHAHI_LEGALOPS_SUITE_URL . 'assets/js/accessibility-scanner-admin.js',
           ['jquery'],
           SHAHI_LEGALOPS_SUITE_VERSION,
           true
       );
       
       wp_localize_script('shahi-accessibility-scanner-admin', 'shahiA11yAdmin', [
           'ajaxUrl' => admin_url('admin-ajax.php'),
           'nonce' => wp_create_nonce('shahi-a11y-nonce'),
           'strings' => [
               'scanStarted' => __('Scan started...', 'shahi-legalops-suite'),
               'scanCompleted' => __('Scan completed!', 'shahi-legalops-suite'),
               // ... more strings
           ]
       ]);
   }
   ```

**Deliverables:**
- ✅ Scan results page created
- ✅ Global styling applied
- ✅ AJAX functionality
- ✅ Table with pagination

**Acceptance Criteria:**
- Page uses global CSS variables
- Matches Module Dashboard design
- AJAX loading works
- Responsive design

---

##### **Task 1.10: Module Dashboard Integration**
**Priority:** P0 (Critical)  
**Estimated Time:** 12 hours  
**Assigned To:** Lead Developer

**Subtasks:**
1. [ ] **Add module metadata for card**
   ```php
   public function to_array() {
       $stats = $this->get_module_stats();
       
       return array_merge(parent::to_array(), [
           'stats' => [
               'scans_run' => $stats['total_scans'],
               'issues_found' => $stats['total_issues'],
               'fixes_applied' => $stats['total_fixes'],
               'avg_score' => $stats['average_score'],
           ],
           'quick_actions' => [
               [
                   'label' => __('Run Scan', 'shahi-legalops-suite'),
                   'url' => admin_url('admin.php?page=shahi-accessibility-scanner&action=new-scan'),
                   'icon' => 'dashicons-search',
               ],
               [
                   'label' => __('View Results', 'shahi-legalops-suite'),
                   'url' => admin_url('admin.php?page=shahi-accessibility-scanner'),
                   'icon' => 'dashicons-analytics',
               ],
               [
                   'label' => __('Settings', 'shahi-legalops-suite'),
                   'url' => admin_url('admin.php?page=shahi-accessibility-scanner-settings'),
                   'icon' => 'dashicons-admin-settings',
               ],
           ],
       ]);
   }
   ```

2. [ ] **Implement stats calculation**
   ```php
   private function get_module_stats() {
       global $wpdb;
       
       $total_scans = $wpdb->get_var(
           "SELECT COUNT(*) FROM {$wpdb->prefix}slos_a11y_scans"
       );
       
       $total_issues = $wpdb->get_var(
           "SELECT COUNT(*) FROM {$wpdb->prefix}slos_a11y_issues WHERE status != 'fixed'"
       );
       
       $total_fixes = $wpdb->get_var(
           "SELECT COUNT(*) FROM {$wpdb->prefix}slos_a11y_fixes WHERE applied = 1"
       );
       
       $avg_score = $wpdb->get_var(
           "SELECT AVG(score) FROM {$wpdb->prefix}slos_a11y_scans WHERE status = 'completed'"
       );
       
       return [
           'total_scans' => (int) $total_scans,
           'total_issues' => (int) $total_issues,
           'total_fixes' => (int) $total_fixes,
           'average_score' => round($avg_score, 1),
       ];
   }
   ```

3. [ ] **Update module card template**
   - Module card automatically renders stats
   - Quick actions appear in card
   - Settings icon links to settings page

4. [ ] **Test module card appearance**
   - Enable module from Module Dashboard
   - Verify stats display correctly
   - Test quick actions links
   - Verify settings icon

**Deliverables:**
- ✅ Module card with stats
- ✅ Quick actions working
- ✅ Settings link functional

**Acceptance Criteria:**
- Stats update in real-time
- Card design matches other modules
- All links working

---

**MONTH 1 DELIVERABLES:**
- ✅ Development environment set up
- ✅ Module registered in Module Dashboard
- ✅ Database schema created (7 tables)
- ✅ Migration system implemented
- ✅ Scanner engine core built
- ✅ 5 basic checkers implemented
- ✅ Scan results page created
- ✅ Module Dashboard integration complete
- ✅ Global styling applied throughout

**MONTH 1 ACCEPTANCE CRITERIA:**
- Module appears in Module Dashboard
- Enable/disable toggle works
- Scanner can run basic scans
- Results displayed correctly
- All pages use global styling
- Database migrations successful
- 50+ unit tests passing

---

## 🚀 CONTINUED IN NEXT PHASES...

This implementation plan will continue with:

- **Month 2:** Remaining 55 checkers implementation
- **Month 3:** Admin UI completion & bulk scanning
- **Month 4-6:** Widget development (70+ features)
- **Month 7-9:** Fix engine & AI integration
- **Month 10-11:** Reporting & issue management
- **Month 12:** Integrations & launch prep

**Would you like me to continue with Month 2 and beyond, or would you like to review Month 1 first?**

---

**Document Status:** Month 1 Complete - Ready for Review  
**Next Update:** Month 2-3 Detailed Plan  
**Total Pages So Far:** 35 pages  
**Estimated Total:** 200+ pages when complete
