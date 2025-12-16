<?php
/**
 * Base Module Class
 *
 * Abstract base class for all plugin modules. Provides the structure
 * and common functionality that all modules must implement.
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
 * Abstract Module Class
 *
 * All plugin modules must extend this class and implement its abstract methods.
 * This ensures consistency across all modules and provides a standardized API.
 *
 * @since 1.0.0
 */
abstract class Module {
    
    /**
     * Module unique key/slug
     *
     * @since 1.0.0
     * @var string
     */
    protected $key;
    
    /**
     * Module enabled state
     *
     * @since 1.0.0
     * @var bool
     */
    protected $enabled = false;
    
    /**
     * Module settings
     *
     * @since 1.0.0
     * @var array
     */
    protected $settings = [];
    
    /**
     * Module dependencies
     *
     * Array of module keys that this module depends on.
     *
     * @since 1.0.0
     * @var array
     */
    protected $dependencies = [];
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->key = $this->get_key();
        $this->load_settings();
    }
    
    /**
     * Get module unique key
     *
     * Used for identification in database and registration.
     *
     * @since 1.0.0
     * @return string Module key
     */
    abstract public function get_key();
    
    /**
     * Get module name
     *
     * Human-readable name displayed in admin interface.
     *
     * @since 1.0.0
     * @return string Module name
     */
    abstract public function get_name();
    
    /**
     * Get module description
     *
     * Short description of module functionality.
     *
     * @since 1.0.0
     * @return string Module description
     */
    abstract public function get_description();
    
    /**
     * Get module icon
     *
     * Dashicon class or custom icon identifier.
     *
     * @since 1.0.0
     * @return string Icon class (e.g., 'dashicons-chart-line')
     */
    abstract public function get_icon();
    
    /**
     * Get module version
     *
     * @since 1.0.0
     * @return string Version number
     */
    public function get_version() {
        return '1.0.0';
    }
    
    /**
     * Get module author
     *
     * @since 1.0.0
     * @return string Author name
     */
    public function get_author() {
        return 'ShahiLegalopsSuite';
    }
    
    /**
     * Get module category
     *
     * Used for filtering and organization.
     *
     * @since 1.0.0
     * @return string Category (tracking, performance, security, etc.)
     */
    public function get_category() {
        return 'general';
    }
    
    /**
     * Check if module is enabled
     *
     * @since 1.0.0
     * @return bool True if enabled, false otherwise
     */
    public function is_enabled() {
        return $this->enabled;
    }
    
    /**
     * Get module dependencies
     *
     * @since 1.0.0
     * @return array Array of module keys this module depends on
     */
    public function get_dependencies() {
        return $this->dependencies;
    }
    
    /**
     * Check if module has unmet dependencies
     *
     * @since 1.0.0
     * @return bool True if dependencies are met, false otherwise
     */
    public function dependencies_met() {
        if (empty($this->dependencies)) {
            return true;
        }
        
        $module_manager = ModuleManager::get_instance();
        
        foreach ($this->dependencies as $dependency_key) {
            $dependency = $module_manager->get_module($dependency_key);
            if (!$dependency || !$dependency->is_enabled()) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Activate module
     *
     * Called when module is enabled. Override to add custom activation logic.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    public function activate() {
        if (!$this->dependencies_met()) {
            return false;
        }
        
        $this->enabled = true;
        $this->save_enabled_state(true);
        $this->on_activate();
        
        return true;
    }
    
    /**
     * Deactivate module
     *
     * Called when module is disabled. Override to add custom deactivation logic.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    public function deactivate() {
        $this->enabled = false;
        $this->save_enabled_state(false);
        $this->on_deactivate();
        
        return true;
    }
    
    /**
     * Hook called on module activation
     *
     * Override in child classes to add custom activation logic.
     *
     * @since 1.0.0
     * @return void
     */
    protected function on_activate() {
        // Override in child classes
    }
    
    /**
     * Hook called on module deactivation
     *
     * Override in child classes to add custom deactivation logic.
     *
     * @since 1.0.0
     * @return void
     */
    protected function on_deactivate() {
        // Override in child classes
    }
    
    /**
     * Initialize module
     *
     * Called when module is loaded. Override to add hooks, filters, etc.
     *
     * @since 1.0.0
     * @return void
     */
    abstract public function init();
    
    /**
     * Get module settings URL
     *
     * Returns the admin URL for module-specific settings page.
     * Return empty string if module has no settings page.
     *
     * @since 1.0.0
     * @return string Settings URL or empty string
     */
    public function get_settings_url() {
        return '';
    }
    
    /**
     * Get module setting
     *
     * @since 1.0.0
     * @param string $key     Setting key.
     * @param mixed  $default Default value if setting not found.
     * @return mixed Setting value
     */
    public function get_setting($key, $default = null) {
        return isset($this->settings[$key]) ? $this->settings[$key] : $default;
    }
    
    /**
     * Update module setting
     *
     * @since 1.0.0
     * @param string $key   Setting key.
     * @param mixed  $value Setting value.
     * @return bool True on success, false on failure
     */
    public function update_setting($key, $value) {
        $this->settings[$key] = $value;
        return $this->save_settings();
    }
    
    /**
     * Load settings from database
     *
     * @since 1.0.0
     * @return void
     */
    protected function load_settings() {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_modules';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return;
        }
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT is_enabled, settings FROM $table WHERE module_key = %s",
            $this->key
        ), ARRAY_A);
        
        if ($result) {
            $this->enabled = (bool) $result['is_enabled'];
            $this->settings = !empty($result['settings']) ? json_decode($result['settings'], true) : [];
        }
    }
    
    /**
     * Save settings to database
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    protected function save_settings() {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_modules';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return false;
        }
        
        $result = $wpdb->update(
            $table,
            [
                'settings' => wp_json_encode($this->settings),
                'last_updated' => current_time('mysql'),
            ],
            ['module_key' => $this->key],
            ['%s', '%s'],
            ['%s']
        );
        
        return $result !== false;
    }
    
    /**
     * Save enabled state to database
     *
     * @since 1.0.0
     * @param bool $enabled Whether module is enabled.
     * @return bool True on success, false on failure
     */
    protected function save_enabled_state($enabled) {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_modules';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return false;
        }
        
        // Check if record exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE module_key = %s",
            $this->key
        ));
        
        if ($exists) {
            // Update existing record
            $result = $wpdb->update(
                $table,
                [
                    'is_enabled' => $enabled ? 1 : 0,
                    'last_updated' => current_time('mysql'),
                ],
                ['module_key' => $this->key],
                ['%d', '%s'],
                ['%s']
            );
        } else {
            // Insert new record
            $result = $wpdb->insert(
                $table,
                [
                    'module_key' => $this->key,
                    'is_enabled' => $enabled ? 1 : 0,
                    'settings' => wp_json_encode($this->settings),
                    'last_updated' => current_time('mysql'),
                ],
                ['%s', '%d', '%s', '%s']
            );
        }
        
        return $result !== false;
    }
    
    /**
     * Check if module is premium/pro
     *
     * @since 1.0.0
     * @return bool True if premium, false if free
     */
    public function is_premium() {
        return false;
    }
    
    /**
     * Get module data as array
     *
     * Used for rendering in templates and AJAX responses.
     *
     * @since 1.0.0
     * @return array Module data
     */
    public function to_array() {
        return [
            'key' => $this->get_key(),
            'name' => $this->get_name(),
            'description' => $this->get_description(),
            'icon' => $this->get_icon(),
            'version' => $this->get_version(),
            'author' => $this->get_author(),
            'category' => $this->get_category(),
            'enabled' => $this->is_enabled(),
            'dependencies' => $this->get_dependencies(),
            'dependencies_met' => $this->dependencies_met(),
            'settings_url' => $this->get_settings_url(),
            'is_premium' => $this->is_premium(),
        ];
    }
}
