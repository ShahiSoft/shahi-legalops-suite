<?php
/**
 * Module Manager
 *
 * Registry and management system for plugin modules. Handles module
 * registration, activation, deactivation, and dependency resolution.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Modules
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalopsSuite\Modules;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Module Manager Class
 *
 * Singleton class that manages all plugin modules. Provides methods for
 * registering modules, checking dependencies, and managing module states.
 *
 * @since 1.0.0
 */
class ModuleManager {
    
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var ModuleManager
     */
    private static $instance = null;
    
    /**
     * Registered modules
     *
     * @since 1.0.0
     * @var array
     */
    private $modules = [];
    
    /**
     * Initialized modules
     *
     * @since 1.0.0
     * @var array
     */
    private $initialized = [];
    
    /**
     * Get singleton instance
     *
     * @since 1.0.0
     * @return ModuleManager Singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    private function __construct() {
        // Register default modules
        $this->register_default_modules();
        
        // Initialize enabled modules
        add_action('init', [$this, 'initialize_modules'], 5);
    }
    
    /**
     * Register a module
     *
     * @since 1.0.0
     * @param Module $module Module instance to register.
     * @return bool True on success, false on failure
     */
    public function register(Module $module) {
        $key = $module->get_key();
        
        if (isset($this->modules[$key])) {
            return false; // Module already registered
        }
        
        $this->modules[$key] = $module;
        
        return true;
    }
    
    /**
     * Unregister a module
     *
     * @since 1.0.0
     * @param string $key Module key to unregister.
     * @return bool True on success, false if module not found
     */
    public function unregister($key) {
        if (!isset($this->modules[$key])) {
            return false;
        }
        
        // Deactivate if enabled
        if ($this->modules[$key]->is_enabled()) {
            $this->modules[$key]->deactivate();
        }
        
        unset($this->modules[$key]);
        unset($this->initialized[$key]);
        
        return true;
    }
    
    /**
     * Get a module by key
     *
     * @since 1.0.0
     * @param string $key Module key.
     * @return Module|null Module instance or null if not found
     */
    public function get_module($key) {
        return isset($this->modules[$key]) ? $this->modules[$key] : null;
    }
    
    /**
     * Get all registered modules
     *
     * @since 1.0.0
     * @param bool $enabled_only If true, return only enabled modules.
     * @return array Array of Module instances
     */
    public function get_modules($enabled_only = false) {
        if (!$enabled_only) {
            return $this->modules;
        }
        
        return array_filter($this->modules, function($module) {
            return $module->is_enabled();
        });
    }
    
    /**
     * Get modules by category
     *
     * @since 1.0.0
     * @param string $category Category name.
     * @param bool   $enabled_only If true, return only enabled modules.
     * @return array Array of Module instances
     */
    public function get_modules_by_category($category, $enabled_only = false) {
        $modules = $this->get_modules($enabled_only);
        
        return array_filter($modules, function($module) use ($category) {
            return $module->get_category() === $category;
        });
    }
    
    /**
     * Check if a module is registered
     *
     * @since 1.0.0
     * @param string $key Module key.
     * @return bool True if registered, false otherwise
     */
    public function is_registered($key) {
        return isset($this->modules[$key]);
    }
    
    /**
     * Check if a module is enabled
     *
     * @since 1.0.0
     * @param string $key Module key.
     * @return bool True if enabled, false otherwise
     */
    public function is_enabled($key) {
        if (!isset($this->modules[$key])) {
            return false;
        }
        
        return $this->modules[$key]->is_enabled();
    }
    
    /**
     * Enable a module
     *
     * @since 1.0.0
     * @param string $key Module key.
     * @return bool True on success, false on failure
     */
    public function enable_module($key) {
        $module = $this->get_module($key);
        
        if (!$module) {
            return false;
        }
        
        // Check dependencies
        if (!$module->dependencies_met()) {
            return false;
        }
        
        // Activate module
        if ($module->activate()) {
            // Initialize if not already initialized
            if (!isset($this->initialized[$key])) {
                $module->init();
                $this->initialized[$key] = true;
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Disable a module
     *
     * @since 1.0.0
     * @param string $key Module key.
     * @return bool True on success, false on failure
     */
    public function disable_module($key) {
        $module = $this->get_module($key);
        
        if (!$module) {
            return false;
        }
        
        // Check if other modules depend on this one
        $dependents = $this->get_dependent_modules($key);
        if (!empty($dependents)) {
            return false; // Cannot disable if other modules depend on it
        }
        
        return $module->deactivate();
    }
    
    /**
     * Toggle a module (enable or disable)
     *
     * @since 1.0.0
     * @param string $key Module key.
     * @param bool   $enabled True to enable, false to disable.
     * @return bool True on success, false on failure
     */
    public function toggle_module($key, $enabled) {
        if ($enabled) {
            return $this->enable_module($key);
        } else {
            return $this->disable_module($key);
        }
    }
    
    /**
     * Get modules that depend on a specific module
     *
     * @since 1.0.0
     * @param string $key Module key.
     * @return array Array of dependent module keys
     */
    public function get_dependent_modules($key) {
        $dependents = [];
        
        foreach ($this->modules as $module_key => $module) {
            if ($module->is_enabled() && in_array($key, $module->get_dependencies(), true)) {
                $dependents[] = $module_key;
            }
        }
        
        return $dependents;
    }
    
    /**
     * Initialize enabled modules
     *
     * Called on WordPress 'init' hook. Initializes all enabled modules
     * that haven't been initialized yet.
     *
     * @since 1.0.0
     * @return void
     */
    public function initialize_modules() {
        foreach ($this->modules as $key => $module) {
            if ($module->is_enabled() && !isset($this->initialized[$key])) {
                $module->init();
                $this->initialized[$key] = true;
            }
        }
    }
    
    /**
     * Register default modules
     *
     * Registers all built-in modules that come with the plugin.
     *
     * @since 1.0.0
     * @return void
     */
    private function register_default_modules() {
        // Compliance Dashboard Module (Phase 2.1)
        if (class_exists('ShahiLegalopsSuite\Modules\ComplianceDashboard\ComplianceDashboard')) {
            $compliance_dashboard = new \ShahiLegalopsSuite\Modules\ComplianceDashboard\ComplianceDashboard();
            if ($this->register($compliance_dashboard)) {
                // Auto-enable core compliance module on first registration
                global $wpdb;
                $table = $wpdb->prefix . 'shahi_modules';
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table WHERE module_key = %s",
                    'compliance-dashboard'
                ));
                
                if (!$exists) {
                    // First time - enable by default
                    $this->enable_module('compliance-dashboard');
                }
            }
        }
        
        // SEO Module
        if (class_exists('ShahiLegalopsSuite\Modules\SEO_Module')) {
            $this->register(new SEO_Module());
        }
        
        // Analytics Module
        if (class_exists('ShahiLegalopsSuite\Modules\Analytics_Module')) {
            $this->register(new Analytics_Module());
        }
        
        // Cache Module
        if (class_exists('ShahiLegalopsSuite\Modules\Cache_Module')) {
            $this->register(new Cache_Module());
        }
        
        // Security Module
        if (class_exists('ShahiLegalopsSuite\Modules\Security_Module')) {
            $this->register(new Security_Module());
        }
        
        // Custom Post Type Module
        if (class_exists('ShahiLegalopsSuite\Modules\CustomPostType_Module')) {
            $this->register(new CustomPostType_Module());
        }
        
        // Accessibility Scanner Module
        if (class_exists('ShahiLegalopsSuite\Modules\AccessibilityScanner\AccessibilityScanner')) {
            $this->register(new \ShahiLegalopsSuite\Modules\AccessibilityScanner\AccessibilityScanner());
        }
        
        /**
         * Allow third-party code to register custom modules
         *
         * @since 1.0.0
         * @param ModuleManager $manager Module manager instance.
         */
        do_action('shahi_legalops_suite_register_modules', $this);
    }
    
    /**
     * Get module statistics
     *
     * Returns statistics about registered and enabled modules.
     *
     * @since 1.0.0
     * @return array Statistics array
     */
    public function get_statistics() {
        $total = count($this->modules);
        $enabled = count($this->get_modules(true));
        $disabled = $total - $enabled;
        
        // Count by category
        $by_category = [];
        foreach ($this->modules as $module) {
            $category = $module->get_category();
            if (!isset($by_category[$category])) {
                $by_category[$category] = 0;
            }
            $by_category[$category]++;
        }
        
        return [
            'total' => $total,
            'enabled' => $enabled,
            'disabled' => $disabled,
            'by_category' => $by_category,
            'initialized' => count($this->initialized),
        ];
    }
    
    /**
     * Bulk enable modules
     *
     * @since 1.0.0
     * @param array $keys Array of module keys to enable.
     * @return array Array with 'success' and 'failed' keys
     */
    public function bulk_enable(array $keys) {
        $result = [
            'success' => [],
            'failed' => [],
        ];
        
        foreach ($keys as $key) {
            if ($this->enable_module($key)) {
                $result['success'][] = $key;
            } else {
                $result['failed'][] = $key;
            }
        }
        
        return $result;
    }
    
    /**
     * Bulk disable modules
     *
     * @since 1.0.0
     * @param array $keys Array of module keys to disable.
     * @return array Array with 'success' and 'failed' keys
     */
    public function bulk_disable(array $keys) {
        $result = [
            'success' => [],
            'failed' => [],
        ];
        
        foreach ($keys as $key) {
            if ($this->disable_module($key)) {
                $result['success'][] = $key;
            } else {
                $result['failed'][] = $key;
            }
        }
        
        return $result;
    }
    
    /**
     * Export module configuration
     *
     * Returns module states and settings as JSON.
     *
     * @since 1.0.0
     * @return string JSON string
     */
    public function export_configuration() {
        $config = [];
        
        foreach ($this->modules as $key => $module) {
            $config[$key] = [
                'enabled' => $module->is_enabled(),
                'settings' => $module->get_setting('all', []),
            ];
        }
        
        return wp_json_encode($config, JSON_PRETTY_PRINT);
    }
    
    /**
     * Import module configuration
     *
     * Imports module states and settings from JSON.
     *
     * @since 1.0.0
     * @param string $json JSON configuration string.
     * @return bool True on success, false on failure
     */
    public function import_configuration($json) {
        $config = json_decode($json, true);
        
        if (!is_array($config)) {
            return false;
        }
        
        foreach ($config as $key => $data) {
            $module = $this->get_module($key);
            
            if (!$module) {
                continue;
            }
            
            // Set enabled state
            if (isset($data['enabled'])) {
                if ($data['enabled']) {
                    $this->enable_module($key);
                } else {
                    $this->disable_module($key);
                }
            }
            
            // Import settings
            if (isset($data['settings']) && is_array($data['settings'])) {
                foreach ($data['settings'] as $setting_key => $setting_value) {
                    $module->update_setting($setting_key, $setting_value);
                }
            }
        }
        
        return true;
    }
}
