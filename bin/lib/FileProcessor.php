<?php
/**
 * File Processor
 *
 * Handles search and replace operations across plugin files
 *
 * @package ShahiTemplate
 * @subpackage Bin\Lib
 */

namespace ShahiTemplate\Bin\Lib;

class FileProcessor {
    /**
     * Plugin root directory
     *
     * @var string
     */
    private $root_dir;

    /**
     * Files processed count
     *
     * @var int
     */
    private $files_processed = 0;

    /**
     * Replacements made count
     *
     * @var int
     */
    private $replacements_made = 0;

    /**
     * Constructor
     *
     * @param string $root_dir Plugin root directory
     */
    public function __construct($root_dir) {
        $this->root_dir = rtrim($root_dir, '/\\');
    }

    /**
     * Process all files with search and replace
     *
     * @param array $old_values Old values to replace
     * @param array $new_values New values
     * @param bool $dry_run Dry run mode (don't actually change files)
     * @return array Statistics
     */
    public function process_files($old_values, $new_values, $dry_run = false) {
        $this->files_processed = 0;
        $this->replacements_made = 0;

        // Get all files to process
        $files = $this->get_files_to_process();

        foreach ($files as $file) {
            $this->process_file($file, $old_values, $new_values, $dry_run);
        }

        return [
            'files_processed' => $this->files_processed,
            'replacements_made' => $this->replacements_made
        ];
    }

    /**
     * Get list of files to process
     *
     * @return array File paths
     */
    private function get_files_to_process() {
        $files = [];
        $extensions = ['php', 'js', 'css', 'json', 'md', 'txt', 'sh'];
        
        // Directories to scan
        $dirs_to_scan = [
            $this->root_dir . '/includes',
            $this->root_dir . '/assets',
            $this->root_dir . '/templates',
            $this->root_dir . '/languages'
        ];

        // Add root-level files
        $root_files = [
            $this->root_dir . '/shahi-template.php',
            $this->root_dir . '/composer.json',
            $this->root_dir . '/package.json',
            $this->root_dir . '/README.md',
            $this->root_dir . '/CHANGELOG.md'
        ];

        foreach ($root_files as $file) {
            if (file_exists($file)) {
                $files[] = $file;
            }
        }

        // Scan directories recursively
        foreach ($dirs_to_scan as $dir) {
            if (is_dir($dir)) {
                $files = array_merge($files, $this->scan_directory($dir, $extensions));
            }
        }

        return $files;
    }

    /**
     * Scan directory recursively
     *
     * @param string $dir Directory path
     * @param array $extensions File extensions to include
     * @return array File paths
     */
    private function scan_directory($dir, $extensions) {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, $extensions)) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    /**
     * Process a single file
     *
     * @param string $file File path
     * @param array $old_values Old values
     * @param array $new_values New values
     * @param bool $dry_run Dry run mode
     */
    private function process_file($file, $old_values, $new_values, $dry_run) {
        $content = file_get_contents($file);
        $original_content = $content;

        // Perform replacements
        foreach ($old_values as $key => $old_value) {
            if (isset($new_values[$key])) {
                $content = str_replace($old_value, $new_values[$key], $content);
            }
        }

        // Check if content changed
        if ($content !== $original_content) {
            if (!$dry_run) {
                file_put_contents($file, $content);
            }
            $this->files_processed++;
            
            // Count replacements
            $count = 0;
            foreach ($old_values as $key => $old_value) {
                if (isset($new_values[$key])) {
                    $count += substr_count($original_content, $old_value);
                }
            }
            $this->replacements_made += $count;
        }
    }

    /**
     * Rename main plugin file
     *
     * @param string $old_slug Old plugin slug
     * @param string $new_slug New plugin slug
     * @param bool $dry_run Dry run mode
     * @return bool Success
     */
    public function rename_main_file($old_slug, $new_slug, $dry_run = false) {
        $old_file = $this->root_dir . '/' . $old_slug . '.php';
        $new_file = $this->root_dir . '/' . $new_slug . '.php';

        if (!file_exists($old_file)) {
            return false;
        }

        if ($old_file === $new_file) {
            return true; // No rename needed
        }

        if (!$dry_run) {
            return rename($old_file, $new_file);
        }

        return true;
    }

    /**
     * Build replacement map from old and new configs
     *
     * @param array $old_config Old configuration
     * @param array $new_config New configuration
     * @return array [old_values, new_values]
     */
    public static function build_replacement_map($old_config, $new_config) {
        $old_values = [];
        $new_values = [];

        // Namespace
        $old_values['namespace'] = $old_config['namespace'];
        $new_values['namespace'] = $new_config['namespace'];

        // Slug
        $old_values['slug'] = $old_config['plugin_slug'];
        $new_values['slug'] = $new_config['plugin_slug'];

        // Text domain
        $old_values['text_domain'] = $old_config['text_domain'];
        $new_values['text_domain'] = $new_config['text_domain'];

        // Function prefix
        $old_values['function_prefix'] = $old_config['function_prefix'];
        $new_values['function_prefix'] = $new_config['function_prefix'];

        // Constant prefix
        $old_values['constant_prefix'] = $old_config['constant_prefix'];
        $new_values['constant_prefix'] = $new_config['constant_prefix'];

        // CSS prefix
        $old_values['css_prefix'] = $old_config['css_prefix'];
        $new_values['css_prefix'] = $new_config['css_prefix'];

        // Plugin name
        $old_values['plugin_name'] = $old_config['plugin_name'];
        $new_values['plugin_name'] = $new_config['plugin_name'];

        // Author
        $old_values['author_name'] = $old_config['author_name'];
        $new_values['author_name'] = $new_config['author_name'];

        // Description
        if (!empty($new_config['description'])) {
            $old_values['description'] = $old_config['description'];
            $new_values['description'] = $new_config['description'];
        }

        return [$old_values, $new_values];
    }

    /**
     * Get statistics
     *
     * @return array Statistics
     */
    public function get_stats() {
        return [
            'files_processed' => $this->files_processed,
            'replacements_made' => $this->replacements_made
        ];
    }
}
