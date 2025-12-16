<?php
/**
 * Main Plugin Class
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Core
 * @license    GPL-3.0+
 */

namespace ShahiLegalopsSuite\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Plugin Class
 *
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 *
 * @since 1.0.0
 */
class Plugin {
    
    /**
     * The loader that's responsible for maintaining and registering all hooks
     *
     * @since 1.0.0
     * @var Loader
     */
    protected $loader;
    
    /**
     * The unique identifier of this plugin
     *
     * @since 1.0.0
     * @var string
     */
    protected $plugin_name;
    
    /**
     * The current version of the plugin
     *
     * @since 1.0.0
     * @var string
     */
    protected $version;
    
    /**
     * Initialize the plugin
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->plugin_name = 'shahi-legalops-suite';
        $this->version = SHAHI_LEGALOPS_SUITE_VERSION;
        
        $this->loader = new Loader();
        
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    /**
     * Load the required dependencies for this plugin.
     *
     * @since 1.0.0
     * @return void
     */
    private function load_dependencies() {
        // Dependencies are auto-loaded via PSR-4 autoloader
        // Additional manual requires can be added here if needed
    }
    
    /**
     * Define the locale for this plugin for internationalization.
     *
     * @since 1.0.0
     * @return void
     */
    private function set_locale() {
        $this->loader->add_action('init', 'ShahiLegalopsSuite\Core\I18n', 'load_plugin_textdomain', 0);
    }
    
    /**
     * Ensure capabilities exist
     *
     * Checks if custom capabilities are present and adds them if missing.
     * This ensures capabilities work even if plugin was activated before capabilities were added.
     *
     * @since 1.0.0
     * @return void
     */
    private function ensure_capabilities() {
        $role = get_role('administrator');
        
        if ($role && !$role->has_cap('manage_shahi_template')) {
            \ShahiLegalopsSuite\Admin\MenuManager::add_capabilities();
        }
    }
    
    /**
     * Define admin-specific hooks
     *
     * @since 1.0.0
     * @return void
     */
    private function define_admin_hooks() {
        // Ensure capabilities exist
        $this->ensure_capabilities();
        // Initialize Assets Manager
        $assets = new Assets();
        $this->loader->add_action('admin_enqueue_scripts', $assets, 'enqueue_admin_styles');
        $this->loader->add_action('admin_enqueue_scripts', $assets, 'enqueue_admin_scripts');
        
        // Initialize Menu Manager
        $menu_manager = new \ShahiLegalopsSuite\Admin\MenuManager();
        $this->loader->add_action('admin_menu', $menu_manager, 'register_menus');
        $this->loader->add_filter('parent_file', $menu_manager, 'highlight_menu');
        $this->loader->add_filter('admin_body_class', $menu_manager, 'add_body_classes');
        
        // Initialize Onboarding
        $onboarding = new \ShahiLegalopsSuite\Admin\Onboarding();
        $this->loader->add_action('admin_footer', $onboarding, 'render_modal');
        $this->loader->add_action('wp_ajax_shahi_save_onboarding', $onboarding, 'save_onboarding');
        $this->loader->add_action('wp_ajax_shahi_skip_onboarding', $onboarding, 'skip_onboarding');
        
        // Initialize Module Manager (Phase 4)
        $module_manager = \ShahiLegalopsSuite\Modules\ModuleManager::get_instance();
        
        // REST API (Phase 5)
        $rest_api = new \ShahiLegalopsSuite\API\RestAPI();
        
        // AJAX Handler (Phase 5.2)
        $ajax_handler = new \ShahiLegalopsSuite\Ajax\AjaxHandler();
        
        // Post Type Manager (Phase 5.3)
        $post_type_manager = new \ShahiLegalopsSuite\PostTypes\PostTypeManager();
        
        // Widget Manager (Phase 5.4)
        $widget_manager = new \ShahiLegalopsSuite\Widgets\WidgetManager();
        
        // Shortcode Manager (Phase 5.5)
        $shortcode_manager = new \ShahiLegalopsSuite\Shortcodes\ShortcodeManager();
    }
    
    /**
     * Define public-facing hooks
     *
     * @since 1.0.0
     * @return void
     */
    private function define_public_hooks() {
        // Public assets (will be added in Phase 1.5)
        // $assets = new Assets();
        // $this->loader->add_action('wp_enqueue_scripts', $assets, 'enqueue_public_assets');
    }
    
    /**
     * Run the loader to execute all hooks
     *
     * @since 1.0.0
     * @return void
     */
    public function run() {
        $this->loader->run();
    }
    
    /**
     * Get the plugin name
     *
     * @since 1.0.0
     * @return string The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }
    
    /**
     * Get the version number
     *
     * @since 1.0.0
     * @return string The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
    
    /**
     * Get the loader
     *
     * @since 1.0.0
     * @return Loader Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }
}
