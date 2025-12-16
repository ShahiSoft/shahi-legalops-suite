<?php
/**
 * Module Boilerplate Template
 * 
 * PLACEHOLDER FILE - This is a template for creating new modules.
 * Copy this file to includes/modules/your-module-name/ and customize it.
 * 
 * Instructions:
 * 1. Copy this file to: includes/modules/{module-slug}/{ModuleName}_Module.php
 * 2. Replace all PLACEHOLDER values with your actual module information
 * 3. Replace {PluginNamespace} with your actual namespace (e.g., ShahiTemplate)
 * 4. Replace {ModuleName} with your module name in PascalCase (e.g., Analytics)
 * 5. Replace {module-slug} with your module slug (e.g., analytics)
 * 6. Implement the abstract methods and add your custom logic
 * 
 * @package    {PluginNamespace}
 * @subpackage Modules\{ModuleName}
 * @since      1.0.0
 */

namespace {PluginNamespace}\Modules\{ModuleName};

use {PluginNamespace}\Core\Module_Base;

// PLACEHOLDER: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * {ModuleName} Module Class
 * 
 * PLACEHOLDER DESCRIPTION: Add your module description here.
 * Explain what this module does and its key features.
 * 
 * Example features:
 * - Feature 1
 * - Feature 2
 * - Feature 3
 * 
 * @since 1.0.0
 */
class {ModuleName}_Module extends Module_Base {
    
    /**
     * Module ID
     * 
     * PLACEHOLDER: Replace with your module's unique identifier
     * Should match the module directory name
     * 
     * @var string
     */
    protected $id = '{module-slug}';
    
    /**
     * Module name
     * 
     * PLACEHOLDER: Replace with your module's display name
     * 
     * @var string
     */
    protected $name = '{Module Display Name}';
    
    /**
     * Module description
     * 
     * PLACEHOLDER: Replace with your module's description
     * 
     * @var string
     */
    protected $description = '{Brief description of what this module does}';
    
    /**
     * Module version
     * 
     * @var string
     */
    protected $version = '1.0.0';
    
    /**
     * Module author
     * 
     * PLACEHOLDER: Replace with your name or company
     * 
     * @var string
     */
    protected $author = '{Author Name}';
    
    /**
     * Module dependencies
     * 
     * PLACEHOLDER: List IDs of modules this module depends on
     * Example: ['analytics', 'security']
     * 
     * @var array
     */
    protected $dependencies = [];
    
    /**
     * Minimum WordPress version required
     * 
     * @var string
     */
    protected $min_wp_version = '5.8';
    
    /**
     * Minimum PHP version required
     * 
     * @var string
     */
    protected $min_php_version = '7.4';
    
    /**
     * Initialize module
     * 
     * This method is called when the module is loaded.
     * Register hooks, initialize subcomponents, etc.
     * 
     * @since 1.0.0
     * @return bool True if initialization successful, false otherwise
     */
    public function init() {
        // Call parent initialization
        if (!parent::init()) {
            return false;
        }
        
        // PLACEHOLDER: Initialize your module components here
        // Example: Load admin or frontend components based on context
        if (is_admin()) {
            $this->init_admin();
        } else {
            $this->init_frontend();
        }
        
        // Register WordPress hooks
        $this->register_hooks();
        
        // Register custom post types, taxonomies, etc.
        $this->register_custom_types();
        
        return true;
    }
    
    /**
     * Initialize admin-side components
     * 
     * PLACEHOLDER: Add your admin initialization logic here
     * 
     * @since 1.0.0
     * @return void
     */
    private function init_admin() {
        // Example: Load admin class
        // new Admin\{ModuleName}_Admin();
    }
    
    /**
     * Initialize frontend components
     * 
     * PLACEHOLDER: Add your frontend initialization logic here
     * 
     * @since 1.0.0
     * @return void
     */
    private function init_frontend() {
        // Example: Load frontend class
        // new Frontend\{ModuleName}_Frontend();
    }
    
    /**
     * Register WordPress hooks
     * 
     * PLACEHOLDER: Register your WordPress action and filter hooks here
     * 
     * @since 1.0.0
     * @return void
     */
    private function register_hooks() {
        // Actions
        add_action('init', [$this, 'on_init']);
        add_action('admin_init', [$this, 'on_admin_init']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // Filters
        // add_filter('the_content', [$this, 'filter_content']);
        
        // Custom hooks
        // do_action("{$this->get_prefix()}{$this->id}_loaded", $this);
    }
    
    /**
     * Register custom post types and taxonomies
     * 
     * PLACEHOLDER: Register any custom post types or taxonomies here
     * 
     * @since 1.0.0
     * @return void
     */
    private function register_custom_types() {
        // Example: Register custom post type
        /*
        register_post_type('{module-slug}_item', [
            'labels' => [
                'name' => __('Items', 'shahi-template'),
                'singular_name' => __('Item', 'shahi-template'),
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor', 'thumbnail'],
        ]);
        */
    }
    
    /**
     * Handle init action
     * 
     * PLACEHOLDER: Add your init logic here
     * 
     * @since 1.0.0
     * @return void
     */
    public function on_init() {
        // Module initialization logic on WordPress init hook
        // Example: Register shortcodes, register REST API routes, etc.
    }
    
    /**
     * Handle admin_init action
     * 
     * PLACEHOLDER: Add your admin init logic here
     * 
     * @since 1.0.0
     * @return void
     */
    public function on_admin_init() {
        // Admin initialization logic
        // Example: Register settings, add meta boxes, etc.
    }
    
    /**
     * Enqueue frontend assets
     * 
     * PLACEHOLDER: Enqueue your frontend CSS and JavaScript files
     * 
     * @since 1.0.0
     * @return void
     */
    public function enqueue_frontend_assets() {
        // Only load if needed
        // if (!$this->should_load_assets()) {
        //     return;
        // }
        
        // Example: Enqueue CSS
        /*
        wp_enqueue_style(
            "{$this->get_prefix()}{$this->id}",
            $this->get_assets_url() . 'css/{module-slug}.css',
            [],
            $this->version
        );
        */
        
        // Example: Enqueue JavaScript
        /*
        wp_enqueue_script(
            "{$this->get_prefix()}{$this->id}",
            $this->get_assets_url() . 'js/{module-slug}.js',
            ['jquery'],
            $this->version,
            true
        );
        
        // Localize script with data
        wp_localize_script("{$this->get_prefix()}{$this->id}", '{ModuleName}Data', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce("{$this->get_prefix()}{$this->id}_nonce"),
            'settings' => $this->get_settings(),
        ]);
        */
    }
    
    /**
     * Enqueue admin assets
     * 
     * PLACEHOLDER: Enqueue your admin CSS and JavaScript files
     * 
     * @since 1.0.0
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueue_admin_assets($hook) {
        // Only load on specific admin pages
        // if (!$this->is_module_admin_page($hook)) {
        //     return;
        // }
        
        // Example: Enqueue admin CSS
        /*
        wp_enqueue_style(
            "{$this->get_prefix()}{$this->id}-admin",
            $this->get_assets_url() . 'css/admin-{module-slug}.css',
            [],
            $this->version
        );
        */
        
        // Example: Enqueue admin JavaScript
        /*
        wp_enqueue_script(
            "{$this->get_prefix()}{$this->id}-admin",
            $this->get_assets_url() . 'js/admin-{module-slug}.js',
            ['jquery', 'wp-api'],
            $this->version,
            true
        );
        */
    }
    
    /**
     * Get module settings
     * 
     * PLACEHOLDER: Return your module's settings
     * 
     * @since 1.0.0
     * @return array Module settings
     */
    public function get_settings() {
        $defaults = $this->get_default_settings();
        $saved = get_option($this->get_option_name(), []);
        
        return wp_parse_args($saved, $defaults);
    }
    
    /**
     * Get default settings
     * 
     * PLACEHOLDER: Define your module's default settings here
     * 
     * @since 1.0.0
     * @return array Default settings
     */
    public function get_default_settings() {
        return [
            'enabled' => true,
            // PLACEHOLDER: Add your default settings here
            // Example:
            // 'feature_x_enabled' => true,
            // 'api_key' => '',
            // 'cache_duration' => 3600,
        ];
    }
    
    /**
     * Update module settings
     * 
     * PLACEHOLDER: Customize settings update logic if needed
     * 
     * @since 1.0.0
     * @param array $settings New settings
     * @return bool True on success, false on failure
     */
    public function update_settings($settings) {
        // Sanitize settings
        $settings = $this->sanitize_settings($settings);
        
        // Update option
        return update_option($this->get_option_name(), $settings);
    }
    
    /**
     * Sanitize settings
     * 
     * PLACEHOLDER: Add your settings sanitization logic here
     * 
     * @since 1.0.0
     * @param array $settings Raw settings
     * @return array Sanitized settings
     */
    private function sanitize_settings($settings) {
        $sanitized = [];
        
        // PLACEHOLDER: Sanitize each setting
        // Example:
        // $sanitized['enabled'] = isset($settings['enabled']) ? (bool) $settings['enabled'] : true;
        // $sanitized['api_key'] = isset($settings['api_key']) ? sanitize_text_field($settings['api_key']) : '';
        
        return $sanitized;
    }
    
    /**
     * Activate module
     * 
     * Called when module is activated. Create tables, set default options, etc.
     * 
     * @since 1.0.0
     * @return void
     */
    public function activate() {
        parent::activate();
        
        // PLACEHOLDER: Add activation logic here
        // Example: Create database tables
        $this->create_tables();
        
        // Set default options
        $this->set_default_options();
        
        // Schedule cron events
        // $this->schedule_events();
        
        // Flush rewrite rules if needed
        flush_rewrite_rules();
    }
    
    /**
     * Deactivate module
     * 
     * Called when module is deactivated. Clean up temporary data, unschedule events, etc.
     * 
     * @since 1.0.0
     * @return void
     */
    public function deactivate() {
        parent::deactivate();
        
        // PLACEHOLDER: Add deactivation logic here
        // Example: Unschedule cron events
        // $this->unschedule_events();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Uninstall module
     * 
     * Called when module is uninstalled. Remove all data, tables, options, etc.
     * 
     * @since 1.0.0
     * @return void
     */
    public function uninstall() {
        parent::uninstall();
        
        // PLACEHOLDER: Add uninstall logic here
        // Example: Drop database tables
        // $this->drop_tables();
        
        // Delete options
        $this->delete_options();
        
        // Delete transients
        // $this->delete_transients();
    }
    
    /**
     * Create database tables
     * 
     * PLACEHOLDER: Define and create your module's database tables
     * 
     * @since 1.0.0
     * @return void
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // PLACEHOLDER: Define your table structure
        /*
        $table_name = $wpdb->prefix . "{module-slug}_data";
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            data_key varchar(100) NOT NULL,
            data_value longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY data_key (data_key)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        */
    }
    
    /**
     * Drop database tables
     * 
     * PLACEHOLDER: Drop your module's database tables
     * 
     * @since 1.0.0
     * @return void
     */
    private function drop_tables() {
        global $wpdb;
        
        // PLACEHOLDER: Drop your tables
        // $table_name = $wpdb->prefix . "{module-slug}_data";
        // $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }
    
    /**
     * Set default options
     * 
     * @since 1.0.0
     * @return void
     */
    private function set_default_options() {
        if (!get_option($this->get_option_name())) {
            update_option($this->get_option_name(), $this->get_default_settings());
        }
    }
    
    /**
     * Delete module options
     * 
     * @since 1.0.0
     * @return void
     */
    private function delete_options() {
        delete_option($this->get_option_name());
    }
    
    /**
     * Get option name
     * 
     * @since 1.0.0
     * @return string
     */
    private function get_option_name() {
        return "{$this->get_prefix()}{$this->id}_settings";
    }
    
    /**
     * Get plugin prefix
     * 
     * PLACEHOLDER: This should return your plugin's prefix
     * Example: 'shahi_'
     * 
     * @since 1.0.0
     * @return string
     */
    private function get_prefix() {
        return 'shahi_'; // PLACEHOLDER: Replace with your plugin prefix
    }
    
    /**
     * Get assets URL
     * 
     * PLACEHOLDER: This should return the URL to your module's assets
     * 
     * @since 1.0.0
     * @return string
     */
    private function get_assets_url() {
        return plugin_dir_url(dirname(__DIR__)) . 'assets/'; // PLACEHOLDER: Adjust path as needed
    }
    
    /**
     * Check if current page is a module admin page
     * 
     * PLACEHOLDER: Customize this to match your module's admin pages
     * 
     * @since 1.0.0
     * @param string $hook Current admin page hook
     * @return bool
     */
    private function is_module_admin_page($hook) {
        // PLACEHOLDER: Check if current page is your module's admin page
        // Example:
        // return strpos($hook, "shahi-{$this->id}") !== false;
        return false;
    }
}
