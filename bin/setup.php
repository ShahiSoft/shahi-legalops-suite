#!/usr/bin/env php
<?php
/**
 * ShahiTemplate Setup Script
 * 
 * Transforms ShahiTemplate into your custom plugin by replacing all placeholders
 * with your plugin's information.
 * 
 * Usage:
 *   Interactive mode:   php bin/setup.php
 *   Config file mode:   php bin/setup.php --config=setup-config.json
 *   Silent mode:        php bin/setup.php --silent --config=setup-config.json
 *   Dry run:            php bin/setup.php --dry-run --config=setup-config.json
 * 
 * @package ShahiTemplate
 * @version 1.0.0
 */

// Ensure script is run from command line
if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

// Load helper classes
require_once __DIR__ . '/lib/SetupValidator.php';
require_once __DIR__ . '/lib/FileProcessor.php';
require_once __DIR__ . '/lib/ComposerUpdater.php';
require_once __DIR__ . '/lib/ColorScheme.php';

use ShahiTemplate\Bin\Lib\SetupValidator;
use ShahiTemplate\Bin\Lib\FileProcessor;
use ShahiTemplate\Bin\Lib\ComposerUpdater;
use ShahiTemplate\Bin\Lib\ColorScheme;

class ShahiTemplateSetup {
    /**
     * Script arguments
     */
    private $args = [];
    
    /**
     * Configuration data
     */
    private $config = [];
    
    /**
     * Root directory of the plugin
     */
    private $root_dir;
    
    /**
     * Validator instance
     */
    private $validator;
    
    /**
     * File processor instance
     */
    private $file_processor;
    
    /**
     * Composer updater instance
     */
    private $composer_updater;
    
    /**
     * Color scheme updater instance
     */
    private $color_scheme;
    
    /**
     * Silent mode flag
     */
    private $silent = false;
    
    /**
     * Dry run mode flag
     */
    private $dry_run = false;
    
    /**
     * Constructor
     */
    public function __construct($argv) {
        $this->root_dir = dirname(__DIR__);
        $this->parse_arguments($argv);
        
        $this->validator = new SetupValidator();
        $this->file_processor = new FileProcessor($this->root_dir);
        $this->composer_updater = new ComposerUpdater($this->root_dir);
        $this->color_scheme = new ColorScheme($this->root_dir);
    }
    
    /**
     * Parse command line arguments
     */
    private function parse_arguments($argv) {
        foreach ($argv as $arg) {
            if (strpos($arg, '--config=') === 0) {
                $this->args['config'] = substr($arg, 9);
            } elseif ($arg === '--silent') {
                $this->silent = true;
            } elseif ($arg === '--dry-run') {
                $this->dry_run = true;
            }
        }
    }
    
    /**
     * Main execution
     */
    public function run() {
        $this->print_header();
        
        // Load configuration
        if (isset($this->args['config'])) {
            $this->load_config_file($this->args['config']);
        } else {
            $this->interactive_setup();
        }
        
        // Validate configuration
        $this->validate_config();
        
        // Confirm before proceeding
        if (!$this->silent && !$this->confirm_setup()) {
            $this->output("\n❌ Setup cancelled.\n", 'error');
            exit(0);
        }
        
        // Execute setup steps
        $this->execute_setup();
        
        $this->output("\n✅ Setup completed successfully!\n", 'success');
        $this->print_next_steps();
    }
    
    /**
     * Print header
     */
    private function print_header() {
        if ($this->silent) return;
        
        echo "\n";
        echo "╔═══════════════════════════════════════════════════════════╗\n";
        echo "║                                                           ║\n";
        echo "║          🚀 ShahiTemplate Setup Wizard 🚀                ║\n";
        echo "║                                                           ║\n";
        echo "║     Transform this template into your custom plugin      ║\n";
        echo "║                                                           ║\n";
        echo "╚═══════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }
    
    /**
     * Load configuration from JSON file
     */
    private function load_config_file($file_path) {
        // Check if path is relative
        if (!file_exists($file_path)) {
            $file_path = $this->root_dir . '/bin/' . $file_path;
        }
        
        if (!file_exists($file_path)) {
            $this->output("❌ Configuration file not found: $file_path\n", 'error');
            exit(1);
        }
        
        $json = file_get_contents($file_path);
        $this->config = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->output("❌ Invalid JSON in configuration file: " . json_last_error_msg() . "\n", 'error');
            exit(1);
        }
        
        $this->output("✓ Configuration loaded from: $file_path\n", 'info');
    }
    
    /**
     * Interactive setup (prompt for each value)
     */
    private function interactive_setup() {
        $this->output("Starting interactive setup. Press Ctrl+C to cancel.\n\n", 'info');
        
        // Core information
        $this->config['plugin_name'] = $this->prompt('Plugin Name', true);
        $this->config['description'] = $this->prompt('Short Description', true);
        $this->config['version'] = $this->prompt('Initial Version', false, '1.0.0');
        
        // Auto-generate technical values
        $auto_config = $this->validator->auto_generate($this->config['plugin_name']);
        
        // Author information
        $this->config['author_name'] = $this->prompt('Author Name', true);
        $this->config['author_email'] = $this->prompt('Author Email', true);
        $this->config['author_url'] = $this->prompt('Author URL', false, '');
        
        // Technical settings (with auto-generated defaults)
        $this->config['plugin_slug'] = $this->prompt('Plugin Slug', false, $auto_config['plugin_slug']);
        $this->config['namespace'] = $this->prompt('PHP Namespace', false, $auto_config['namespace']);
        $this->config['text_domain'] = $this->config['plugin_slug'];
        $this->config['function_prefix'] = $this->prompt('Function Prefix', false, $auto_config['function_prefix']);
        $this->config['constant_prefix'] = $this->prompt('Constant Prefix', false, $auto_config['constant_prefix']);
        $this->config['css_prefix'] = $this->prompt('CSS Prefix', false, $auto_config['css_prefix']);
        
        // WordPress requirements
        $this->config['min_wp_version'] = $this->prompt('Minimum WordPress Version', false, '5.8');
        $this->config['min_php_version'] = $this->prompt('Minimum PHP Version', false, '7.4');
        
        // License
        $this->config['license'] = $this->prompt('License', false, 'GPL-3.0-or-later');
        
        // Optional
        $this->config['repository_url'] = $this->prompt('Repository URL (optional)', false, '');
        $this->config['api_namespace'] = $this->prompt('REST API Namespace', false, $auto_config['api_namespace']);
        $this->config['menu_position'] = $this->prompt('Admin Menu Position', false, '6');
        $this->config['menu_icon'] = $this->prompt('Admin Menu Icon', false, 'dashicons-admin-generic');
        
        // Color Theme Selection
        $this->config['theme'] = $this->prompt_theme_selection();
        
        $this->output("\n✓ Interactive setup completed\n", 'success');
    }
    
    /**
     * Prompt for user input
     */
    private function prompt($label, $required = false, $default = '') {
        $prompt = $label;
        if ($default) {
            $prompt .= " [$default]";
        }
        if ($required) {
            $prompt .= " *";
        }
        $prompt .= ": ";
        
        echo $prompt;
        $input = trim(fgets(STDIN));
        
        if (empty($input) && $default) {
            return $default;
        }
        
        if (empty($input) && $required) {
            $this->output("This field is required!\n", 'error');
            return $this->prompt($label, $required, $default);
        }
        
        return $input ?: $default;
    }
    
    /**
     * Prompt for theme selection
     */
    private function prompt_theme_selection() {
        // Load available themes
        $themes_file = $this->root_dir . '/config/themes.php';
        if (!file_exists($themes_file)) {
            $this->output("⚠️  Themes configuration not found, using default Neon Aether\n", 'warning');
            return 'neon-aether';
        }
        
        $themes = require $themes_file;
        
        echo "\n";
        echo "═══════════════════════════════════════════════════════════\n";
        echo "Choose Your Admin Color Theme:\n";
        echo "═══════════════════════════════════════════════════════════\n";
        
        $theme_options = [];
        $i = 1;
        foreach ($themes as $key => $theme) {
            $theme_options[$i] = $key;
            $colors = implode(' ', array_map(function($c) { return "[$c]"; }, $theme['preview_colors']));
            echo "$i. {$theme['name']}\n";
            echo "   {$theme['description']}\n";
            echo "   Colors: $colors\n\n";
            $i++;
        }
        
        echo "═══════════════════════════════════════════════════════════\n";
        echo "Select theme [1-" . count($themes) . "] (default: 1): ";
        
        $selection = trim(fgets(STDIN));
        
        if (empty($selection)) {
            $selection = 1;
        }
        
        $selection = (int) $selection;
        
        if ($selection < 1 || $selection > count($themes)) {
            $this->output("Invalid selection, using default Neon Aether\n", 'warning');
            return 'neon-aether';
        }
        
        $selected_theme = $theme_options[$selection];
        $this->output("✓ Selected theme: {$themes[$selected_theme]['name']}\n", 'success');
        
        return $selected_theme;
    }
    
    /**
     * Validate configuration
     */
    private function validate_config() {
        $this->output("\n📋 Validating configuration...\n", 'info');
        
        $is_valid = $this->validator->validate($this->config);
        
        if (!$is_valid) {
            $errors = $this->validator->get_errors();
            $this->output("❌ Configuration validation failed:\n", 'error');
            foreach ($errors as $error) {
                $this->output("   • $error\n", 'error');
            }
            exit(1);
        }
        
        $this->output("✓ Configuration is valid\n", 'success');
    }
    
    /**
     * Confirm setup before execution
     */
    private function confirm_setup() {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════\n";
        echo "Configuration Summary:\n";
        echo "═══════════════════════════════════════════════════════════\n";
        echo "Plugin Name:      {$this->config['plugin_name']}\n";
        echo "Plugin Slug:      {$this->config['plugin_slug']}\n";
        echo "Namespace:        {$this->config['namespace']}\n";
        echo "Author:           {$this->config['author_name']}\n";
        echo "Version:          {$this->config['version']}\n";
        echo "License:          {$this->config['license']}\n";
        echo "═══════════════════════════════════════════════════════════\n";
        
        if ($this->dry_run) {
            echo "\n🔍 DRY RUN MODE - No files will be modified\n";
        }
        
        echo "\n⚠️  This will modify files in: {$this->root_dir}\n";
        echo "\nProceed with setup? [y/N]: ";
        
        $confirm = trim(fgets(STDIN));
        return strtolower($confirm) === 'y';
    }
    
    /**
     * Execute all setup steps
     */
    private function execute_setup() {
        $steps = [
            '1. Updating composer.json',
            '2. Processing PHP files',
            '3. Processing JavaScript files',
            '4. Processing CSS files',
            '5. Processing template files',
            '6. Processing documentation',
            '7. Updating color scheme',
            '8. Renaming main plugin file',
            '9. Regenerating autoloader',
            '10. Cleaning up setup files'
        ];
        
        $this->output("\n🚀 Executing setup steps...\n\n", 'info');
        
        // Step 1: Update composer.json
        $this->execute_step($steps[0], function() {
            $composer_file = $this->root_dir . '/composer.json';
            ComposerUpdater::update($composer_file, $this->config);
        });
        
        // Step 2-6: Process files
        $this->execute_step($steps[1] . '-' . $steps[5], function() {
            // Build old and new values from default template config
            $old_config = [
                'namespace' => 'ShahiTemplate',
                'plugin_slug' => 'shahi-template',
                'text_domain' => 'shahi-template',
                'function_prefix' => 'shahi_template_',
                'constant_prefix' => 'SHAHI_TEMPLATE_',
                'css_prefix' => 'shahi-template-',
                'plugin_name' => 'Shahi Template',
                'author_name' => 'Your Name',
                'description' => 'A comprehensive WordPress plugin template'
            ];
            
            list($old_values, $new_values) = \ShahiTemplate\Bin\Lib\FileProcessor::build_replacement_map($old_config, $this->config);
            $stats = $this->file_processor->process_files($old_values, $new_values, $this->dry_run);
            $this->output("   → Processed {$stats['files_processed']} files\n", 'info');
            $this->output("   → Made {$stats['replacements_made']} replacements\n", 'info');
        });
        
        // Step 7: Update color scheme
        $this->execute_step($steps[6], function() {
            if (isset($this->config['theme'])) {
                $this->apply_theme($this->config['theme']);
            }
        });
        
        // Step 8: Rename main plugin file
        $this->execute_step($steps[7], function() {
            $this->file_processor->rename_main_file(
                'shahi-template',
                $this->config['plugin_slug'],
                $this->dry_run
            );
        });
        
        // Step 9: Regenerate autoloader
        $this->execute_step($steps[8], function() {
            if (!$this->dry_run) {
                ComposerUpdater::regenerate_autoload($this->root_dir);
            }
        });
        
        // Step 10: Cleanup
        $this->execute_step($steps[9], function() {
            $this->cleanup_setup_files();
        });
    }
    
    /**
     * Execute a single step with error handling
     */
    private function execute_step($label, $callback) {
        $this->output("$label... ", 'info');
        
        try {
            $callback();
            $this->output("✓\n", 'success');
        } catch (Exception $e) {
            $this->output("✗\n", 'error');
            $this->output("   Error: " . $e->getMessage() . "\n", 'error');
            
            if (!$this->silent) {
                echo "\nContinue anyway? [y/N]: ";
                $confirm = trim(fgets(STDIN));
                if (strtolower($confirm) !== 'y') {
                    exit(1);
                }
            } else {
                exit(1);
            }
        }
    }
    
    /**
     * Apply selected theme to CSS variables
     */
    private function apply_theme($theme_key) {
        $themes_file = $this->root_dir . '/config/themes.php';
        if (!file_exists($themes_file)) {
            $this->output("   ⚠️  Themes file not found, skipping\n", 'warning');
            return;
        }
        
        $themes = require $themes_file;
        
        if (!isset($themes[$theme_key])) {
            $this->output("   ⚠️  Theme '$theme_key' not found, using default\n", 'warning');
            $theme_key = 'neon-aether';
        }
        
        $theme = $themes[$theme_key];
        
        if ($this->dry_run) {
            $this->output("   [Dry run] Would apply theme: {$theme['name']}\n", 'info');
            return;
        }
        
        // Update admin-global.css with theme variables
        $css_file = $this->root_dir . '/assets/css/admin-global.css';
        if (!file_exists($css_file)) {
            $this->output("   ⚠️  CSS file not found\n", 'warning');
            return;
        }
        
        $css_content = file_get_contents($css_file);
        
        // Replace each CSS variable
        foreach ($theme['variables'] as $var => $value) {
            $pattern = '/(' . preg_quote($var, '/') . ':\s*)[^;]+;/';
            $replacement = '$1' . $value . ';';
            $css_content = preg_replace($pattern, $replacement, $css_content);
        }
        
        file_put_contents($css_file, $css_content);
        
        // Also update minified version
        $css_min_file = $this->root_dir . '/assets/css/admin-global.min.css';
        if (file_exists($css_min_file)) {
            file_put_contents($css_min_file, $css_content);
        }
        
        $this->output("   → Applied {$theme['name']} theme\n", 'info');
    }
    
    /**
     * Clean up setup files
     */
    private function cleanup_setup_files() {
        if ($this->dry_run) {
            $this->output("   [Dry run] Would remove setup files\n", 'info');
            return;
        }
        
        $files_to_remove = [
            'bin/setup-config.example.json',
            'bin/setup-config-schema.json',
            'PLUGIN-INFORMATION-REQUIREMENTS.md',
            'CODECANYON-SUBMISSION-CHECKLIST-v3.3.1.md',
            'CODECANYON-SUBMISSION-Template-CHECKLIST.md'
        ];
        
        foreach ($files_to_remove as $file) {
            $full_path = $this->root_dir . '/' . $file;
            if (file_exists($full_path)) {
                @unlink($full_path);
            }
        }
    }
    
    /**
     * Print next steps
     */
    private function print_next_steps() {
        if ($this->silent) return;
        
        echo "\n";
        echo "╔═══════════════════════════════════════════════════════════╗\n";
        echo "║                      Next Steps                           ║\n";
        echo "╚═══════════════════════════════════════════════════════════╝\n";
        echo "\n";
        echo "1. Review the changes in your plugin directory\n";
        echo "2. Run: composer install\n";
        echo "3. Run: npm install && npm run build\n";
        echo "4. Activate the plugin in WordPress\n";
        echo "5. Start building your awesome plugin! 🎉\n";
        echo "\n";
        echo "Documentation:\n";
        echo "  • README.md - Getting started\n";
        echo "  • TEMPLATE-USAGE.md - Template features\n";
        echo "  • DEVELOPER-GUIDE.md - Development guide\n";
        echo "  • docs/ - Additional documentation\n";
        echo "\n";
    }
    
    /**
     * Output message with color
     */
    private function output($message, $type = 'info') {
        if ($this->silent && $type !== 'error') {
            return;
        }
        
        $colors = [
            'info' => "\033[0;36m",    // Cyan
            'success' => "\033[0;32m", // Green
            'error' => "\033[0;31m",   // Red
            'warning' => "\033[0;33m"  // Yellow
        ];
        
        $reset = "\033[0m";
        $color = $colors[$type] ?? '';
        
        echo $color . $message . $reset;
    }
}

// Run the setup
try {
    $setup = new ShahiTemplateSetup($argv);
    $setup->run();
} catch (Exception $e) {
    echo "\n❌ Fatal Error: " . $e->getMessage() . "\n";
    exit(1);
}
