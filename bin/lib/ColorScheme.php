<?php
/**
 * Color Scheme Updater
 *
 * Updates CSS color variables in stylesheet files
 *
 * @package ShahiTemplate
 * @subpackage Bin\Lib
 */

namespace ShahiTemplate\Bin\Lib;

class ColorScheme {
    /**
     * Update CSS color variables
     *
     * @param string $css_file Path to CSS file
     * @param array $colors New color values
     * @return bool Success
     */
    public static function update($css_file, $colors) {
        if (!file_exists($css_file)) {
            return false;
        }

        $content = file_get_contents($css_file);

        // Color mappings
        $color_vars = [
            'primary' => '--shahi-primary',
            'secondary' => '--shahi-secondary',
            'accent' => '--shahi-accent',
            'background_dark' => '--shahi-bg-dark',
            'background_light' => '--shahi-bg-light'
        ];

        // Replace each color variable
        foreach ($color_vars as $key => $var_name) {
            if (isset($colors[$key])) {
                $pattern = '/(' . preg_quote($var_name, '/') . '\s*:\s*)#[0-9a-fA-F]{6}/';
                $replacement = '${1}' . $colors[$key];
                $content = preg_replace($pattern, $replacement, $content);
            }
        }

        return file_put_contents($css_file, $content) !== false;
    }

    /**
     * Update all CSS files in plugin
     *
     * @param string $plugin_dir Plugin root directory
     * @param array $colors New colors
     * @return int Number of files updated
     */
    public static function update_all($plugin_dir, $colors) {
        $updated = 0;
        
        // Main CSS files to update
        $css_files = [
            $plugin_dir . '/assets/css/admin-global.css',
            $plugin_dir . '/assets/css/admin-settings.css',
            $plugin_dir . '/assets/css/admin-dashboard.css'
        ];

        foreach ($css_files as $file) {
            if (self::update($file, $colors)) {
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Get default color scheme
     *
     * @return array Default colors
     */
    public static function get_defaults() {
        return [
            'primary' => '#00d4ff',
            'secondary' => '#7000ff',
            'accent' => '#00ff88',
            'background_dark' => '#0a0a12',
            'background_light' => '#1a1a2e'
        ];
    }

    /**
     * Validate color format
     *
     * @param string $color Color value
     * @return bool Valid
     */
    public static function validate_color($color) {
        return preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1;
    }
}
