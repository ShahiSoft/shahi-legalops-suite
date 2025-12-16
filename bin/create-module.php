#!/usr/bin/env php
<?php
/**
 * Module Generator for ShahiTemplate
 * 
 * Generates a new module with boilerplate code following the template's architecture.
 * 
 * Usage:
 *   php bin/create-module.php ModuleName "Module description"
 * 
 * @package ShahiTemplate
 * @version 1.0.0
 */

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

class ModuleGenerator {
    private $root_dir;
    private $module_name;
    private $module_slug;
    private $description;
    private $namespace;
    
    public function __construct($argv) {
        $this->root_dir = dirname(__DIR__);
        
        if (count($argv) < 2) {
            $this->print_usage();
            exit(1);
        }
        
        $this->module_name = $argv[1];
        $this->description = $argv[2] ?? 'A new module for the plugin';
        $this->module_slug = $this->generate_slug($this->module_name);
        $this->namespace = $this->detect_namespace();
    }
    
    public function run() {
        echo "\n🔨 Creating module: {$this->module_name}\n\n";
        
        $module_dir = $this->root_dir . '/includes/modules/' . $this->module_slug;
        
        if (file_exists($module_dir)) {
            echo "❌ Module already exists: {$this->module_slug}\n";
            exit(1);
        }
        
        // Create directory structure
        $this->create_directories($module_dir);
        
        // Create module files
        $this->create_main_file($module_dir);
        $this->create_settings_file($module_dir);
        $this->create_admin_file($module_dir);
        $this->create_frontend_file($module_dir);
        
        echo "\n✅ Module created successfully!\n\n";
        $this->print_next_steps();
    }
    
    private function create_directories($module_dir) {
        $dirs = [
            $module_dir,
            $module_dir . '/admin',
            $module_dir . '/frontend',
            $module_dir . '/api'
        ];
        
        foreach ($dirs as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
                echo "✓ Created: " . basename($dir) . "/\n";
            }
        }
    }
    
    private function create_main_file($module_dir) {
        $class_name = $this->module_name;
        $content = <<<PHP
<?php
/**
 * {$this->module_name} Module
 * 
 * {$this->description}
 * 
 * @package {$this->namespace}
 * @subpackage Modules\\{$class_name}
 */

namespace {$this->namespace}\\Modules\\{$class_name};

use {$this->namespace}\\Core\\Module_Base;

class {$class_name}_Module extends Module_Base {
    /**
     * Module ID
     */
    protected \$id = '{$this->module_slug}';
    
    /**
     * Module name
     */
    protected \$name = '{$this->module_name}';
    
    /**
     * Module description
     */
    protected \$description = '{$this->description}';
    
    /**
     * Module version
     */
    protected \$version = '1.0.0';
    
    /**
     * Module dependencies
     */
    protected \$dependencies = [];
    
    /**
     * Initialize module
     */
    public function init() {
        if (!parent::init()) {
            return false;
        }
        
        // Initialize subcomponents
        if (is_admin()) {
            new Admin\\{$class_name}_Admin();
        } else {
            new Frontend\\{$class_name}_Frontend();
        }
        
        // Register hooks
        \$this->register_hooks();
        
        return true;
    }
    
    /**
     * Register WordPress hooks
     */
    private function register_hooks() {
        // Add your hooks here
        add_action('init', [\$this, 'on_init']);
    }
    
    /**
     * Handle init action
     */
    public function on_init() {
        // Module initialization logic
    }
    
    /**
     * Get module settings
     */
    public function get_settings() {
        return get_option(\$this->get_option_name(), \$this->get_default_settings());
    }
    
    /**
     * Get default settings
     */
    public function get_default_settings() {
        return [
            'enabled' => true,
            // Add your default settings here
        ];
    }
    
    /**
     * Activate module
     */
    public function activate() {
        parent::activate();
        
        // Add module-specific activation logic
        \$this->create_tables();
        \$this->set_default_options();
        
        flush_rewrite_rules();
    }
    
    /**
     * Deactivate module
     */
    public function deactivate() {
        parent::deactivate();
        
        // Add module-specific deactivation logic
        flush_rewrite_rules();
    }
    
    /**
     * Uninstall module
     */
    public function uninstall() {
        parent::uninstall();
        
        // Add module-specific uninstall logic
        \$this->drop_tables();
        \$this->delete_options();
    }
    
    /**
     * Create database tables
     */
    private function create_tables() {
        // Add table creation logic if needed
    }
    
    /**
     * Drop database tables
     */
    private function drop_tables() {
        // Add table drop logic if needed
    }
    
    /**
     * Set default options
     */
    private function set_default_options() {
        if (!get_option(\$this->get_option_name())) {
            update_option(\$this->get_option_name(), \$this->get_default_settings());
        }
    }
    
    /**
     * Delete options
     */
    private function delete_options() {
        delete_option(\$this->get_option_name());
    }
}

PHP;
        
        file_put_contents($module_dir . '/' . $class_name . '_Module.php', $content);
        echo "✓ Created: {$class_name}_Module.php\n";
    }
    
    private function create_admin_file($module_dir) {
        $class_name = $this->module_name;
        $content = <<<PHP
<?php
/**
 * {$this->module_name} Admin Component
 * 
 * Handles admin-side functionality for {$this->module_name} module
 * 
 * @package {$this->namespace}
 * @subpackage Modules\\{$class_name}\\Admin
 */

namespace {$this->namespace}\\Modules\\{$class_name}\\Admin;

class {$class_name}_Admin {
    /**
     * Constructor
     */
    public function __construct() {
        \$this->register_hooks();
    }
    
    /**
     * Register WordPress hooks
     */
    private function register_hooks() {
        add_action('admin_menu', [\$this, 'add_menu_page']);
        add_action('admin_enqueue_scripts', [\$this, 'enqueue_assets']);
        add_action('admin_init', [\$this, 'register_settings']);
    }
    
    /**
     * Add admin menu page
     */
    public function add_menu_page() {
        add_submenu_page(
            'shahi-template', // Parent slug (adjust if needed)
            __('{$this->module_name}', 'shahi-template'),
            __('{$this->module_name}', 'shahi-template'),
            'manage_options',
            'shahi-{$this->module_slug}',
            [\$this, 'render_page']
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_assets(\$hook) {
        if (strpos(\$hook, 'shahi-{$this->module_slug}') === false) {
            return;
        }
        
        // Enqueue CSS
        wp_enqueue_style(
            'shahi-{$this->module_slug}-admin',
            plugin_dir_url(__DIR__) . 'assets/css/admin.css',
            [],
            '1.0.0'
        );
        
        // Enqueue JS
        wp_enqueue_script(
            'shahi-{$this->module_slug}-admin',
            plugin_dir_url(__DIR__) . 'assets/js/admin.js',
            ['jquery'],
            '1.0.0',
            true
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'shahi_{$this->module_slug}_settings',
            'shahi_{$this->module_slug}_options'
        );
    }
    
    /**
     * Render admin page
     */
    public function render_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="shahi-admin-content">
                <p><?php esc_html_e('{$this->description}', 'shahi-template'); ?></p>
                
                <!-- Add your admin interface here -->
            </div>
        </div>
        <?php
    }
}

PHP;
        
        file_put_contents($module_dir . '/admin/' . $class_name . '_Admin.php', $content);
        echo "✓ Created: admin/{$class_name}_Admin.php\n";
    }
    
    private function create_frontend_file($module_dir) {
        $class_name = $this->module_name;
        $content = <<<PHP
<?php
/**
 * {$this->module_name} Frontend Component
 * 
 * Handles frontend functionality for {$this->module_name} module
 * 
 * @package {$this->namespace}
 * @subpackage Modules\\{$class_name}\\Frontend
 */

namespace {$this->namespace}\\Modules\\{$class_name}\\Frontend;

class {$class_name}_Frontend {
    /**
     * Constructor
     */
    public function __construct() {
        \$this->register_hooks();
    }
    
    /**
     * Register WordPress hooks
     */
    private function register_hooks() {
        add_action('wp_enqueue_scripts', [\$this, 'enqueue_assets']);
        add_shortcode('{$this->module_slug}', [\$this, 'render_shortcode']);
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets() {
        wp_enqueue_style(
            'shahi-{$this->module_slug}',
            plugin_dir_url(__DIR__) . 'assets/css/frontend.css',
            [],
            '1.0.0'
        );
        
        wp_enqueue_script(
            'shahi-{$this->module_slug}',
            plugin_dir_url(__DIR__) . 'assets/js/frontend.js',
            ['jquery'],
            '1.0.0',
            true
        );
    }
    
    /**
     * Render shortcode
     */
    public function render_shortcode(\$atts) {
        \$atts = shortcode_atts([
            // Add shortcode attributes here
        ], \$atts, '{$this->module_slug}');
        
        ob_start();
        ?>
        <div class="shahi-{$this->module_slug}">
            <!-- Add your frontend output here -->
            <p><?php esc_html_e('{$this->module_name} shortcode output', 'shahi-template'); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }
}

PHP;
        
        file_put_contents($module_dir . '/frontend/' . $class_name . '_Frontend.php', $content);
        echo "✓ Created: frontend/{$class_name}_Frontend.php\n";
    }
    
    private function create_settings_file($module_dir) {
        // Create a simple settings template
        $content = <<<JSON
{
    "enabled": true,
    "settings": {
        
    }
}
JSON;
        
        file_put_contents($module_dir . '/settings.json', $content);
        echo "✓ Created: settings.json\n";
    }
    
    private function generate_slug($name) {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $name));
    }
    
    private function detect_namespace() {
        $composer_file = $this->root_dir . '/composer.json';
        if (file_exists($composer_file)) {
            $composer = json_decode(file_get_contents($composer_file), true);
            if (isset($composer['autoload']['psr-4'])) {
                $namespaces = array_keys($composer['autoload']['psr-4']);
                return rtrim($namespaces[0], '\\');
            }
        }
        return 'ShahiTemplate'; // Default fallback
    }
    
    private function print_usage() {
        echo "\nUsage: php bin/create-module.php ModuleName \"Module description\"\n\n";
        echo "Examples:\n";
        echo "  php bin/create-module.php Analytics \"Track user analytics\"\n";
        echo "  php bin/create-module.php EmailMarketing \"Email campaign management\"\n\n";
    }
    
    private function print_next_steps() {
        echo "Next steps:\n";
        echo "1. Register the module in includes/class-module-manager.php\n";
        echo "2. Add module assets to assets/css/ and assets/js/\n";
        echo "3. Implement your module logic\n";
        echo "4. Run: composer dump-autoload\n\n";
    }
}

// Run the generator
try {
    $generator = new ModuleGenerator($argv);
    $generator->run();
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
