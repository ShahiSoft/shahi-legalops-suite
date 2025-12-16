#!/usr/bin/env php
<?php
/**
 * Admin Page Generator for ShahiTemplate
 * 
 * Generates a new admin page with boilerplate code.
 * 
 * Usage:
 *   php bin/create-admin-page.php PageName "Page Title" [parent-slug]
 * 
 * @package ShahiTemplate
 * @version 1.0.0
 */

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

class AdminPageGenerator {
    private $root_dir;
    private $page_name;
    private $page_title;
    private $page_slug;
    private $parent_slug;
    private $namespace;
    
    public function __construct($argv) {
        $this->root_dir = dirname(__DIR__);
        
        if (count($argv) < 3) {
            $this->print_usage();
            exit(1);
        }
        
        $this->page_name = $argv[1];
        $this->page_title = $argv[2];
        $this->parent_slug = $argv[3] ?? 'shahi-template';
        $this->page_slug = $this->generate_slug($this->page_name);
        $this->namespace = $this->detect_namespace();
    }
    
    public function run() {
        echo "\n📄 Creating admin page: {$this->page_name}\n\n";
        
        $pages_dir = $this->root_dir . '/includes/admin/pages';
        
        if (!file_exists($pages_dir)) {
            mkdir($pages_dir, 0755, true);
        }
        
        $this->create_page_file($pages_dir);
        $this->create_template_file($pages_dir);
        
        echo "\n✅ Admin page created successfully!\n\n";
        $this->print_next_steps();
    }
    
    private function create_page_file($pages_dir) {
        $class_name = $this->page_name;
        $content = <<<PHP
<?php
/**
 * {$this->page_name} Admin Page
 * 
 * {$this->page_title}
 * 
 * @package {$this->namespace}
 * @subpackage Admin\\Pages
 */

namespace {$this->namespace}\\Admin\\Pages;

class {$class_name}_Page {
    /**
     * Page slug
     */
    private \$page_slug = '{$this->page_slug}';
    
    /**
     * Parent slug
     */
    private \$parent_slug = '{$this->parent_slug}';
    
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
        add_action('admin_post_save_{$this->page_slug}', [\$this, 'handle_save']);
    }
    
    /**
     * Add menu page
     */
    public function add_menu_page() {
        add_submenu_page(
            \$this->parent_slug,
            __('{$this->page_title}', 'shahi-template'),
            __('{$this->page_name}', 'shahi-template'),
            'manage_options',
            \$this->page_slug,
            [\$this, 'render_page']
        );
    }
    
    /**
     * Enqueue assets
     */
    public function enqueue_assets(\$hook) {
        if (strpos(\$hook, \$this->page_slug) === false) {
            return;
        }
        
        // Common admin styles
        wp_enqueue_style('shahi-admin-global');
        
        // Page-specific styles
        wp_enqueue_style(
            'shahi-{$this->page_slug}',
            plugin_dir_url(__FILE__) . 'assets/css/{$this->page_slug}.css',
            [],
            '1.0.0'
        );
        
        // Page-specific scripts
        wp_enqueue_script(
            'shahi-{$this->page_slug}',
            plugin_dir_url(__FILE__) . 'assets/js/{$this->page_slug}.js',
            ['jquery'],
            '1.0.0',
            true
        );
        
        // Localize script
        wp_localize_script('shahi-{$this->page_slug}', 'shahi{$class_name}', [
            'nonce' => wp_create_nonce('shahi_{$this->page_slug}_nonce'),
            'ajaxUrl' => admin_url('admin-ajax.php')
        ]);
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'shahi_{$this->page_slug}_group',
            'shahi_{$this->page_slug}_options',
            [\$this, 'sanitize_options']
        );
    }
    
    /**
     * Sanitize options
     */
    public function sanitize_options(\$options) {
        // Add sanitization logic here
        return \$options;
    }
    
    /**
     * Render page
     */
    public function render_page() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Get current options
        \$options = get_option('shahi_{$this->page_slug}_options', []);
        
        // Include template
        include __DIR__ . '/templates/{$this->page_slug}.php';
    }
    
    /**
     * Handle save action
     */
    public function handle_save() {
        // Verify nonce
        if (!isset(\$_POST['_wpnonce']) || !wp_verify_nonce(\$_POST['_wpnonce'], 'shahi_{$this->page_slug}_save')) {
            wp_die(__('Security check failed'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions'));
        }
        
        // Process and save data
        \$options = [];
        // Add your save logic here
        
        update_option('shahi_{$this->page_slug}_options', \$options);
        
        // Redirect back with success message
        wp_redirect(add_query_arg([
            'page' => \$this->page_slug,
            'message' => 'saved'
        ], admin_url('admin.php')));
        exit;
    }
    
    /**
     * Get option value
     */
    private function get_option(\$key, \$default = '') {
        \$options = get_option('shahi_{$this->page_slug}_options', []);
        return isset(\$options[\$key]) ? \$options[\$key] : \$default;
    }
}

PHP;
        
        file_put_contents($pages_dir . '/' . $class_name . '_Page.php', $content);
        echo "✓ Created: {$class_name}_Page.php\n";
    }
    
    private function create_template_file($pages_dir) {
        $template_dir = $pages_dir . '/templates';
        if (!file_exists($template_dir)) {
            mkdir($template_dir, 0755, true);
        }
        
        $content = <<<PHP
<?php
/**
 * Template for {$this->page_name} page
 * 
 * @package {$this->namespace}
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap shahi-admin-page">
    <h1 class="shahi-admin-title">
        <span class="shahi-icon">📄</span>
        <?php echo esc_html(get_admin_page_title()); ?>
    </h1>

    <?php if (isset(\$_GET['message']) && \$_GET['message'] === 'saved'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Settings saved successfully!', 'shahi-template'); ?></p>
        </div>
    <?php endif; ?>

    <div class="shahi-admin-content">
        <div class="shahi-card">
            <div class="shahi-card-header">
                <h2><?php esc_html_e('{$this->page_title}', 'shahi-template'); ?></h2>
            </div>
            
            <div class="shahi-card-body">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('shahi_{$this->page_slug}_save'); ?>
                    <input type="hidden" name="action" value="save_{$this->page_slug}">
                    
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="example_field">
                                        <?php esc_html_e('Example Field', 'shahi-template'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input 
                                        type="text" 
                                        id="example_field" 
                                        name="options[example_field]" 
                                        value="<?php echo esc_attr(\$this->get_option('example_field')); ?>"
                                        class="regular-text"
                                    >
                                    <p class="description">
                                        <?php esc_html_e('Description of this field', 'shahi-template'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Add more fields here -->
                        </tbody>
                    </table>
                    
                    <div class="shahi-form-actions">
                        <?php submit_button(__('Save Settings', 'shahi-template'), 'primary', 'submit', false); ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

PHP;
        
        file_put_contents($template_dir . '/' . $this->page_slug . '.php', $content);
        echo "✓ Created: templates/{$this->page_slug}.php\n";
    }
    
    private function generate_slug($name) {
        return 'shahi-' . strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $name));
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
        return 'ShahiTemplate';
    }
    
    private function print_usage() {
        echo "\nUsage: php bin/create-admin-page.php PageName \"Page Title\" [parent-slug]\n\n";
        echo "Examples:\n";
        echo "  php bin/create-admin-page.php Settings \"Plugin Settings\"\n";
        echo "  php bin/create-admin-page.php Dashboard \"Analytics Dashboard\" shahi-analytics\n\n";
    }
    
    private function print_next_steps() {
        echo "Next steps:\n";
        echo "1. Register the page in your admin class\n";
        echo "2. Create CSS file: assets/css/{$this->page_slug}.css\n";
        echo "3. Create JS file: assets/js/{$this->page_slug}.js\n";
        echo "4. Customize the template in includes/admin/pages/templates/{$this->page_slug}.php\n";
        echo "5. Run: composer dump-autoload\n\n";
    }
}

// Run the generator
try {
    $generator = new AdminPageGenerator($argv);
    $generator->run();
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
