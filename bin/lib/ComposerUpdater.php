<?php
/**
 * Composer Updater
 *
 * Updates composer.json with new plugin information
 *
 * @package ShahiTemplate
 * @subpackage Bin\Lib
 */

namespace ShahiTemplate\Bin\Lib;

class ComposerUpdater {
    /**
     * Update composer.json
     *
     * @param string $composer_file Path to composer.json
     * @param array $config New configuration
     * @return bool Success
     */
    public static function update($composer_file, $config) {
        if (!file_exists($composer_file)) {
            return false;
        }

        $composer_data = json_decode(file_get_contents($composer_file), true);
        
        if ($composer_data === null) {
            return false;
        }

        // Update name
        if (isset($config['plugin_slug'])) {
            $vendor = explode('/', $composer_data['name'])[0] ?? 'vendor';
            $composer_data['name'] = $vendor . '/' . $config['plugin_slug'];
        }

        // Update description
        if (isset($config['description'])) {
            $composer_data['description'] = $config['description'];
        }

        // Update homepage
        if (isset($config['author_url'])) {
            $composer_data['homepage'] = $config['author_url'];
        }

        // Update authors
        if (isset($config['author_name']) || isset($config['author_email'])) {
            $composer_data['authors'] = [[
                'name' => $config['author_name'] ?? 'Author Name',
                'email' => $config['author_email'] ?? ''
            ]];
        }

        // Update license
        if (isset($config['license'])) {
            $composer_data['license'] = $config['license'];
        }

        // Update autoload namespace
        if (isset($config['namespace'])) {
            $old_namespace = 'ShahiTemplate\\\\';
            $new_namespace = $config['namespace'] . '\\\\';
            
            if (isset($composer_data['autoload']['psr-4'][$old_namespace])) {
                $path = $composer_data['autoload']['psr-4'][$old_namespace];
                unset($composer_data['autoload']['psr-4'][$old_namespace]);
                $composer_data['autoload']['psr-4'][$new_namespace] = $path;
            }
        }

        // Write updated composer.json
        $json = json_encode($composer_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return file_put_contents($composer_file, $json) !== false;
    }

    /**
     * Regenerate autoload files
     *
     * @param string $plugin_dir Plugin directory
     * @return bool Success
     */
    public static function regenerate_autoload($plugin_dir) {
        $composer_phar = $plugin_dir . '/composer.phar';
        $composer_cmd = file_exists($composer_phar) ? "php $composer_phar" : 'composer';
        
        $old_dir = getcwd();
        chdir($plugin_dir);
        
        exec("$composer_cmd dump-autoload 2>&1", $output, $return_var);
        
        chdir($old_dir);
        
        return $return_var === 0;
    }
}
