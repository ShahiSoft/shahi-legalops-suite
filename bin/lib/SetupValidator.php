<?php
/**
 * Setup Validator
 *
 * Validates plugin configuration data
 *
 * @package ShahiTemplate
 * @subpackage Bin\Lib
 */

namespace ShahiTemplate\Bin\Lib;

class SetupValidator {
    /**
     * Validation errors
     *
     * @var array
     */
    private $errors = [];

    /**
     * Validate configuration data
     *
     * @param array $config Configuration data
     * @return bool True if valid
     */
    public function validate($config) {
        $this->errors = [];

        // Required fields
        $this->validate_required($config, 'plugin_name');
        $this->validate_required($config, 'plugin_slug');
        $this->validate_required($config, 'namespace');
        $this->validate_required($config, 'author_name');
        $this->validate_required($config, 'author_email');

        // Validate plugin_name
        if (isset($config['plugin_name'])) {
            $this->validate_length($config['plugin_name'], 'plugin_name', 3, 50);
        }

        // Validate plugin_slug
        if (isset($config['plugin_slug'])) {
            $this->validate_slug($config['plugin_slug']);
        }

        // Validate namespace
        if (isset($config['namespace'])) {
            $this->validate_namespace($config['namespace']);
        }

        // Validate email
        if (isset($config['author_email'])) {
            $this->validate_email($config['author_email']);
        }

        // Validate URL
        if (!empty($config['author_url'])) {
            $this->validate_url($config['author_url']);
        }

        // Validate version
        if (isset($config['version'])) {
            $this->validate_version($config['version']);
        }

        // Validate colors
        if (isset($config['colors']) && is_array($config['colors'])) {
            foreach ($config['colors'] as $key => $color) {
                $this->validate_hex_color($color, $key);
            }
        }

        return empty($this->errors);
    }

    /**
     * Get validation errors
     *
     * @return array Errors
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * Validate required field
     *
     * @param array $config Configuration
     * @param string $field Field name
     */
    private function validate_required($config, $field) {
        if (empty($config[$field])) {
            $this->errors[] = "Field '{$field}' is required";
        }
    }

    /**
     * Validate string length
     *
     * @param string $value Value
     * @param string $field Field name
     * @param int $min Minimum length
     * @param int $max Maximum length
     */
    private function validate_length($value, $field, $min, $max) {
        $len = strlen($value);
        if ($len < $min || $len > $max) {
            $this->errors[] = "Field '{$field}' must be between {$min} and {$max} characters";
        }
    }

    /**
     * Validate plugin slug
     *
     * @param string $slug Slug
     */
    private function validate_slug($slug) {
        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            $this->errors[] = "Plugin slug must contain only lowercase letters, numbers, and hyphens";
        }
        if (strlen($slug) < 3 || strlen($slug) > 30) {
            $this->errors[] = "Plugin slug must be between 3 and 30 characters";
        }
    }

    /**
     * Validate namespace
     *
     * @param string $namespace Namespace
     */
    private function validate_namespace($namespace) {
        if (!preg_match('/^[A-Z][a-zA-Z0-9]*$/', $namespace)) {
            $this->errors[] = "Namespace must start with uppercase letter and contain only alphanumeric characters";
        }
        if (strlen($namespace) < 3 || strlen($namespace) > 30) {
            $this->errors[] = "Namespace must be between 3 and 30 characters";
        }
    }

    /**
     * Validate email
     *
     * @param string $email Email address
     */
    private function validate_email($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Invalid email address format";
        }
    }

    /**
     * Validate URL
     *
     * @param string $url URL
     */
    private function validate_url($url) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->errors[] = "Invalid URL format";
        }
    }

    /**
     * Validate version number
     *
     * @param string $version Version
     */
    private function validate_version($version) {
        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            $this->errors[] = "Version must follow semantic versioning (e.g., 1.0.0)";
        }
    }

    /**
     * Validate hex color
     *
     * @param string $color Color value
     * @param string $key Color key
     */
    private function validate_hex_color($color, $key) {
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $this->errors[] = "Color '{$key}' must be a valid hex color (e.g., #00d4ff)";
        }
    }

    /**
     * Auto-generate values from plugin name
     *
     * @param string $plugin_name Plugin name
     * @return array Generated values
     */
    public static function auto_generate($plugin_name) {
        // Generate slug
        $slug = strtolower($plugin_name);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        // Generate namespace
        $namespace = str_replace([' ', '-', '_'], '', ucwords($plugin_name, ' -_'));
        $namespace = preg_replace('/[^A-Za-z0-9]/', '', $namespace);

        // Generate prefixes
        $words = explode('-', $slug);
        $acronym = '';
        foreach ($words as $word) {
            if (!empty($word)) {
                $acronym .= substr($word, 0, 1);
            }
        }
        $function_prefix = strtolower($acronym) . '_';
        $constant_prefix = strtoupper($acronym) . '_';
        $css_prefix = strtolower($acronym) . '-';

        return [
            'plugin_slug' => $slug,
            'namespace' => $namespace,
            'text_domain' => $slug,
            'function_prefix' => $function_prefix,
            'constant_prefix' => $constant_prefix,
            'css_prefix' => $css_prefix,
            'api_namespace' => $slug . '/v1'
        ];
    }
}
