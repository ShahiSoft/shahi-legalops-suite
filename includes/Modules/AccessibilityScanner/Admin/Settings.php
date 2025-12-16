<?php
/**
 * Accessibility Scanner Settings Controller
 *
 * Handles all settings management for the Accessibility Scanner module.
 * Implements WordPress Settings API with tabbed interface and validation.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Modules\AccessibilityScanner\Admin
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings Class
 *
 * Manages module settings using WordPress Settings API.
 *
 * @since 1.0.0
 */
class Settings {
    
    /**
     * Settings option group name
     *
     * @since 1.0.0
     * @var string
     */
    private $option_group = 'shahi_a11y_settings_group';
    
    /**
     * Settings option name
     *
     * @since 1.0.0
     * @var string
     */
    private $option_name = 'shahi_a11y_settings';
    
    /**
     * Default settings
     *
     * @since 1.0.0
     * @var array
     */
    private $default_settings = array();
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->default_settings = $this->get_default_settings();
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // AJAX handlers for settings
        add_action('wp_ajax_shahi_a11y_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_shahi_a11y_reset_settings', array($this, 'ajax_reset_settings'));
    }
    
    /**
     * Get default settings structure
     *
     * @since 1.0.0
     * @return array Default settings
     */
    private function get_default_settings() {
        return array(
            // General Settings
            'general' => array(
                'enabled' => true,
                'wcag_level' => 'AA',
                'wcag_version' => '2.2',
                'scan_on_save' => true,
                'scan_on_publish' => true,
                'auto_scan_interval' => 'weekly',
            ),
            
            // Scanner Settings
            'scanner' => array(
                'enabled_checks' => array(), // Empty = all checks enabled
                'check_images' => true,
                'check_headings' => true,
                'check_links' => true,
                'check_forms' => true,
                'check_aria' => true,
                'check_color_contrast' => true,
                'check_semantics' => true,
                'check_navigation' => true,
                'check_multimedia' => true,
                'check_tables' => true,
                'scan_depth' => 'full', // 'quick', 'standard', 'full'
                'max_issues_per_scan' => 1000,
                'background_scanning' => true,
            ),
            
            // Widget Settings
            'widget' => array(
                'enabled' => false,
                'position' => 'bottom-right', // 'bottom-right', 'bottom-left', 'top-right', 'top-left'
                'button_color' => '#c0c0c0',
                'button_size' => 'medium', // 'small', 'medium', 'large'
                'enabled_features' => array(), // Empty = all features enabled
                'show_on_frontend' => true,
                'show_on_admin' => false,
                'exclude_pages' => array(),
                'require_login' => false,
            ),
            
            // One-Click Fixes Settings
            'fixes' => array(
                'auto_fix_enabled' => false,
                'auto_fix_on_save' => false,
                'enabled_fixes' => array(), // Empty = all fixes available
                'require_approval' => true,
                'backup_before_fix' => true,
                'log_fixes' => true,
            ),
            
            // AI Features Settings
            'ai' => array(
                'enabled' => false,
                'provider' => 'openai', // 'openai', 'google', 'anthropic'
                'api_key' => '',
                'alt_text_generation' => false,
                'content_simplification' => false,
                'description_enhancement' => false,
                'max_tokens' => 150,
                'temperature' => 0.7,
            ),
            
            // Reporting Settings
            'reporting' => array(
                'email_notifications' => false,
                'notification_email' => get_option('admin_email'),
                'notification_frequency' => 'daily', // 'immediate', 'daily', 'weekly'
                'notification_threshold' => 'critical', // 'all', 'serious', 'critical'
                'include_recommendations' => true,
                'export_format' => 'pdf', // 'pdf', 'csv', 'json', 'html'
            ),
            
            // Advanced Settings
            'advanced' => array(
                'delete_data_on_deactivation' => false,
                'enable_cli' => true,
                'enable_rest_api' => true,
                'cache_results' => true,
                'cache_duration' => 300, // 5 minutes in seconds
                'debug_mode' => false,
                'log_level' => 'error', // 'none', 'error', 'warning', 'info', 'debug'
            ),
        );
    }
    
    /**
     * Register settings with WordPress Settings API
     *
     * @since 1.0.0
     * @return void
     */
    public function register_settings() {
        // Register setting
        register_setting(
            $this->option_group,
            $this->option_name,
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'sanitize_settings'),
                'default' => $this->default_settings,
            )
        );
        
        // Register sections and fields
        $this->register_general_section();
        $this->register_scanner_section();
        $this->register_widget_section();
        $this->register_fixes_section();
        $this->register_ai_section();
        $this->register_reporting_section();
        $this->register_advanced_section();
    }
    
    /**
     * Register General Settings section
     *
     * @since 1.0.0
     * @return void
     */
    private function register_general_section() {
        add_settings_section(
            'shahi_a11y_general',
            __('General Settings', 'shahi-legalops-suite'),
            array($this, 'render_general_section_description'),
            'shahi-accessibility-settings'
        );
        
        // WCAG Level
        add_settings_field(
            'wcag_level',
            __('WCAG Compliance Level', 'shahi-legalops-suite'),
            array($this, 'render_wcag_level_field'),
            'shahi-accessibility-settings',
            'shahi_a11y_general'
        );
        
        // WCAG Version
        add_settings_field(
            'wcag_version',
            __('WCAG Version', 'shahi-legalops-suite'),
            array($this, 'render_wcag_version_field'),
            'shahi-accessibility-settings',
            'shahi_a11y_general'
        );
        
        // Scan on Save
        add_settings_field(
            'scan_on_save',
            __('Scan on Save', 'shahi-legalops-suite'),
            array($this, 'render_scan_on_save_field'),
            'shahi-accessibility-settings',
            'shahi_a11y_general'
        );
        
        // Scan on Publish
        add_settings_field(
            'scan_on_publish',
            __('Scan on Publish', 'shahi-legalops-suite'),
            array($this, 'render_scan_on_publish_field'),
            'shahi-accessibility-settings',
            'shahi_a11y_general'
        );
        
        // Auto Scan Interval
        add_settings_field(
            'auto_scan_interval',
            __('Automatic Scan Frequency', 'shahi-legalops-suite'),
            array($this, 'render_auto_scan_interval_field'),
            'shahi-accessibility-settings',
            'shahi_a11y_general'
        );
    }
    
    /**
     * Register Scanner Settings section
     *
     * @since 1.0.0
     * @return void
     */
    private function register_scanner_section() {
        add_settings_section(
            'shahi_a11y_scanner',
            __('Scanner Settings', 'shahi-legalops-suite'),
            array($this, 'render_scanner_section_description'),
            'shahi-accessibility-settings'
        );
        
        // Scan Depth
        add_settings_field(
            'scan_depth',
            __('Scan Depth', 'shahi-legalops-suite'),
            array($this, 'render_scan_depth_field'),
            'shahi-accessibility-settings',
            'shahi_a11y_scanner'
        );
        
        // Background Scanning
        add_settings_field(
            'background_scanning',
            __('Background Scanning', 'shahi-legalops-suite'),
            array($this, 'render_background_scanning_field'),
            'shahi-accessibility-settings',
            'shahi_a11y_scanner'
        );
    }
    
    /**
     * Register Widget Settings section
     *
     * @since 1.0.0
     * @return void
     */
    private function register_widget_section() {
        add_settings_section(
            'shahi_a11y_widget',
            __('Widget Settings', 'shahi-legalops-suite'),
            array($this, 'render_widget_section_description'),
            'shahi-accessibility-settings'
        );
        
        // Widget Enabled
        add_settings_field(
            'widget_enabled',
            __('Enable Widget', 'shahi-legalops-suite'),
            array($this, 'render_widget_enabled_field'),
            'shahi-accessibility-settings',
            'shahi_a11y_widget'
        );
        
        // Widget Position
        add_settings_field(
            'widget_position',
            __('Widget Position', 'shahi-legalops-suite'),
            array($this, 'render_widget_position_field'),
            'shahi-accessibility-settings',
            'shahi_a11y_widget'
        );
    }
    
    /**
     * Register Fixes Settings section
     *
     * @since 1.0.0
     * @return void
     */
    private function register_fixes_section() {
        add_settings_section(
            'shahi_a11y_fixes',
            __('One-Click Fixes Settings', 'shahi-legalops-suite'),
            array($this, 'render_fixes_section_description'),
            'shahi-accessibility-settings'
        );
        
        // Auto Fix Enabled
        add_settings_field(
            'auto_fix_enabled',
            __('Enable Auto-Fix', 'shahi-legalops-suite'),
            array($this, 'render_auto_fix_enabled_field'),
            'shahi-accessibility-settings',
            'shahi_a11y_fixes'
        );
        
        // Require Approval
        add_settings_field(
            'require_approval',
            __('Require Approval', 'shahi-legalops-suite'),
            array($this, 'render_require_approval_field'),
            'shahi-accessibility-settings',
            'shahi_a11y_fixes'
        );
    }
    
    /**
     * Register AI Settings section
     *
     * @since 1.0.0
     * @return void
     */
    private function register_ai_section() {
        add_settings_section(
            'shahi_a11y_ai',
            __('AI Features Settings', 'shahi-legalops-suite'),
            array($this, 'render_ai_section_description'),
            'shahi-accessibility-settings'
        );
        
        // AI Enabled
        add_settings_field(
            'ai_enabled',
            __('Enable AI Features', 'shahi-legalops-suite'),
            array($this, 'render_ai_enabled_field'),
            'shahi-accessibility-settings',
            'shahi_a11y_ai'
        );
        
        // AI Provider
        add_settings_field(
            'ai_provider',
            __('AI Provider', 'shahi-legalops-suite'),
            array($this, 'render_ai_provider_field'),
            'shahi-accessibility-settings',
            'shahi_a11y_ai'
        );
        
        // API Key
        add_settings_field(
            'ai_api_key',
            __('API Key', 'shahi-legalops-suite'),
            array($this, 'render_ai_api_key_field'),
            'shahi-accessibility-settings',
            'shahi_a11y_ai'
        );
    }
    
    /**
     * Register Reporting Settings section
     *
     * @since 1.0.0
     * @return void
     */
    private function register_reporting_section() {
        add_settings_section(
            'shahi_a11y_reporting',
            __('Reporting Settings', 'shahi-legalops-suite'),
            array($this, 'render_reporting_section_description'),
            'shahi-accessibility-settings'
        );
        
        // Email Notifications
        add_settings_field(
            'email_notifications',
            __('Email Notifications', 'shahi-legalops-suite'),
            array($this, 'render_email_notifications_field'),
            'shahi-accessibility-settings',
            'shahi_a11y_reporting'
        );
        
        // Notification Email
        add_settings_field(
            'notification_email',
            __('Notification Email', 'shahi-legalops-suite'),
            array($this, 'render_notification_email_field'),
            'shahi-accessibility-settings',
            'shahi_a11y_reporting'
        );
    }
    
    /**
     * Register Advanced Settings section
     *
     * @since 1.0.0
     * @return void
     */
    private function register_advanced_section() {
        add_settings_section(
            'shahi_a11y_advanced',
            __('Advanced Settings', 'shahi-legalops-suite'),
            array($this, 'render_advanced_section_description'),
            'shahi-accessibility-settings'
        );
        
        // Debug Mode
        add_settings_field(
            'debug_mode',
            __('Debug Mode', 'shahi-legalops-suite'),
            array($this, 'render_debug_mode_field'),
            'shahi-accessibility-settings',
            'shahi_a11y_advanced'
        );
        
        // Cache Results
        add_settings_field(
            'cache_results',
            __('Cache Results', 'shahi-legalops-suite'),
            array($this, 'render_cache_results_field'),
            'shahi-accessibility-settings',
            'shahi_a11y_advanced'
        );
    }
    
    /**
     * Sanitize settings before saving
     *
     * @since 1.0.0
     * @param array $input Raw input from form
     * @return array Sanitized settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // General settings
        if (isset($input['general'])) {
            $sanitized['general'] = array(
                'enabled' => isset($input['general']['enabled']) ? (bool) $input['general']['enabled'] : true,
                'wcag_level' => in_array($input['general']['wcag_level'], array('A', 'AA', 'AAA')) 
                    ? $input['general']['wcag_level'] 
                    : 'AA',
                'wcag_version' => in_array($input['general']['wcag_version'], array('2.0', '2.1', '2.2')) 
                    ? $input['general']['wcag_version'] 
                    : '2.2',
                'scan_on_save' => isset($input['general']['scan_on_save']) ? (bool) $input['general']['scan_on_save'] : true,
                'scan_on_publish' => isset($input['general']['scan_on_publish']) ? (bool) $input['general']['scan_on_publish'] : true,
                'auto_scan_interval' => in_array($input['general']['auto_scan_interval'], array('never', 'daily', 'weekly', 'monthly')) 
                    ? $input['general']['auto_scan_interval'] 
                    : 'weekly',
            );
        }
        
        // Scanner settings
        if (isset($input['scanner'])) {
            $sanitized['scanner'] = array(
                'scan_depth' => in_array($input['scanner']['scan_depth'], array('quick', 'standard', 'full')) 
                    ? $input['scanner']['scan_depth'] 
                    : 'full',
                'background_scanning' => isset($input['scanner']['background_scanning']) ? (bool) $input['scanner']['background_scanning'] : true,
                'max_issues_per_scan' => absint($input['scanner']['max_issues_per_scan'] ?? 1000),
            );
        }
        
        // Widget settings
        if (isset($input['widget'])) {
            $sanitized['widget'] = array(
                'enabled' => isset($input['widget']['enabled']) ? (bool) $input['widget']['enabled'] : false,
                'position' => in_array($input['widget']['position'], array('bottom-right', 'bottom-left', 'top-right', 'top-left')) 
                    ? $input['widget']['position'] 
                    : 'bottom-right',
                'button_color' => sanitize_hex_color($input['widget']['button_color'] ?? '#c0c0c0'),
                'button_size' => in_array($input['widget']['button_size'], array('small', 'medium', 'large')) 
                    ? $input['widget']['button_size'] 
                    : 'medium',
                'show_on_frontend' => isset($input['widget']['show_on_frontend']) ? (bool) $input['widget']['show_on_frontend'] : true,
                'show_on_admin' => isset($input['widget']['show_on_admin']) ? (bool) $input['widget']['show_on_admin'] : false,
            );
        }
        
        // Fixes settings
        if (isset($input['fixes'])) {
            $sanitized['fixes'] = array(
                'auto_fix_enabled' => isset($input['fixes']['auto_fix_enabled']) ? (bool) $input['fixes']['auto_fix_enabled'] : false,
                'auto_fix_on_save' => isset($input['fixes']['auto_fix_on_save']) ? (bool) $input['fixes']['auto_fix_on_save'] : false,
                'require_approval' => isset($input['fixes']['require_approval']) ? (bool) $input['fixes']['require_approval'] : true,
                'backup_before_fix' => isset($input['fixes']['backup_before_fix']) ? (bool) $input['fixes']['backup_before_fix'] : true,
                'log_fixes' => isset($input['fixes']['log_fixes']) ? (bool) $input['fixes']['log_fixes'] : true,
            );
        }
        
        // AI settings
        if (isset($input['ai'])) {
            $sanitized['ai'] = array(
                'enabled' => isset($input['ai']['enabled']) ? (bool) $input['ai']['enabled'] : false,
                'provider' => in_array($input['ai']['provider'], array('openai', 'google', 'anthropic')) 
                    ? $input['ai']['provider'] 
                    : 'openai',
                'api_key' => sanitize_text_field($input['ai']['api_key'] ?? ''),
                'alt_text_generation' => isset($input['ai']['alt_text_generation']) ? (bool) $input['ai']['alt_text_generation'] : false,
                'content_simplification' => isset($input['ai']['content_simplification']) ? (bool) $input['ai']['content_simplification'] : false,
                'max_tokens' => absint($input['ai']['max_tokens'] ?? 150),
                'temperature' => floatval($input['ai']['temperature'] ?? 0.7),
            );
        }
        
        // Reporting settings
        if (isset($input['reporting'])) {
            $sanitized['reporting'] = array(
                'email_notifications' => isset($input['reporting']['email_notifications']) ? (bool) $input['reporting']['email_notifications'] : false,
                'notification_email' => sanitize_email($input['reporting']['notification_email'] ?? get_option('admin_email')),
                'notification_frequency' => in_array($input['reporting']['notification_frequency'], array('immediate', 'daily', 'weekly')) 
                    ? $input['reporting']['notification_frequency'] 
                    : 'daily',
                'notification_threshold' => in_array($input['reporting']['notification_threshold'], array('all', 'serious', 'critical')) 
                    ? $input['reporting']['notification_threshold'] 
                    : 'critical',
                'export_format' => in_array($input['reporting']['export_format'], array('pdf', 'csv', 'json', 'html')) 
                    ? $input['reporting']['export_format'] 
                    : 'pdf',
            );
        }
        
        // Advanced settings
        if (isset($input['advanced'])) {
            $sanitized['advanced'] = array(
                'delete_data_on_deactivation' => isset($input['advanced']['delete_data_on_deactivation']) ? (bool) $input['advanced']['delete_data_on_deactivation'] : false,
                'enable_cli' => isset($input['advanced']['enable_cli']) ? (bool) $input['advanced']['enable_cli'] : true,
                'enable_rest_api' => isset($input['advanced']['enable_rest_api']) ? (bool) $input['advanced']['enable_rest_api'] : true,
                'cache_results' => isset($input['advanced']['cache_results']) ? (bool) $input['advanced']['cache_results'] : true,
                'cache_duration' => absint($input['advanced']['cache_duration'] ?? 300),
                'debug_mode' => isset($input['advanced']['debug_mode']) ? (bool) $input['advanced']['debug_mode'] : false,
                'log_level' => in_array($input['advanced']['log_level'], array('none', 'error', 'warning', 'info', 'debug')) 
                    ? $input['advanced']['log_level'] 
                    : 'error',
            );
        }
        
        return $sanitized;
    }
    
    /**
     * Get a specific setting value
     *
     * @since 1.0.0
     * @param string $section Setting section
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed Setting value
     */
    public function get_setting($section, $key, $default = null) {
        $settings = get_option($this->option_name, $this->default_settings);
        
        if (isset($settings[$section][$key])) {
            return $settings[$section][$key];
        }
        
        if (isset($this->default_settings[$section][$key])) {
            return $this->default_settings[$section][$key];
        }
        
        return $default;
    }
    
    /**
     * Get all settings
     *
     * @since 1.0.0
     * @return array All settings
     */
    public function get_all_settings() {
        return get_option($this->option_name, $this->default_settings);
    }
    
    /**
     * Update a specific setting value
     *
     * @since 1.0.0
     * @param string $section Setting section
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool True on success, false on failure
     */
    public function update_setting($section, $key, $value) {
        $settings = get_option($this->option_name, $this->default_settings);
        
        if (!isset($settings[$section])) {
            $settings[$section] = array();
        }
        
        $settings[$section][$key] = $value;
        
        return update_option($this->option_name, $settings);
    }
    
    /**
     * Reset settings to defaults
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    public function reset_settings() {
        return update_option($this->option_name, $this->default_settings);
    }
    
    /**
     * AJAX handler to save settings
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_save_settings() {
        check_ajax_referer('shahi_a11y_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'shahi-legalops-suite')));
        }
        
        $settings = isset($_POST['settings']) ? $_POST['settings'] : array();
        $sanitized = $this->sanitize_settings($settings);
        
        if (update_option($this->option_name, $sanitized)) {
            wp_send_json_success(array('message' => __('Settings saved successfully', 'shahi-legalops-suite')));
        } else {
            wp_send_json_error(array('message' => __('Failed to save settings', 'shahi-legalops-suite')));
        }
    }
    
    /**
     * AJAX handler to reset settings
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_reset_settings() {
        check_ajax_referer('shahi_a11y_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'shahi-legalops-suite')));
        }
        
        if ($this->reset_settings()) {
            wp_send_json_success(array('message' => __('Settings reset to defaults', 'shahi-legalops-suite')));
        } else {
            wp_send_json_error(array('message' => __('Failed to reset settings', 'shahi-legalops-suite')));
        }
    }
    
    /**
     * Render settings page
     *
     * @since 1.0.0
     * @return void
     */
    public function render() {
        include SHAHI_LEGALOPS_SUITE_PLUGIN_DIR . 'templates/admin/accessibility-scanner/settings.php';
    }
    
    // Field render methods (placeholders - will be called by Settings API)
    public function render_general_section_description() {
        echo '<p>' . esc_html__('Configure general accessibility scanner settings.', 'shahi-legalops-suite') . '</p>';
    }
    
    public function render_wcag_level_field() {
        $value = $this->get_setting('general', 'wcag_level', 'AA');
        ?>
        <select name="<?php echo esc_attr($this->option_name); ?>[general][wcag_level]" class="shahi-select">
            <option value="A" <?php selected($value, 'A'); ?>>Level A (Minimum)</option>
            <option value="AA" <?php selected($value, 'AA'); ?>>Level AA (Recommended)</option>
            <option value="AAA" <?php selected($value, 'AAA'); ?>>Level AAA (Enhanced)</option>
        </select>
        <p class="description"><?php esc_html_e('Select the WCAG compliance level to check against.', 'shahi-legalops-suite'); ?></p>
        <?php
    }
    
    public function render_wcag_version_field() {
        $value = $this->get_setting('general', 'wcag_version', '2.2');
        ?>
        <select name="<?php echo esc_attr($this->option_name); ?>[general][wcag_version]" class="shahi-select">
            <option value="2.0" <?php selected($value, '2.0'); ?>>WCAG 2.0</option>
            <option value="2.1" <?php selected($value, '2.1'); ?>>WCAG 2.1</option>
            <option value="2.2" <?php selected($value, '2.2'); ?>>WCAG 2.2 (Latest)</option>
        </select>
        <p class="description"><?php esc_html_e('Select the WCAG version to use for scanning.', 'shahi-legalops-suite'); ?></p>
        <?php
    }
    
    public function render_scan_on_save_field() {
        $value = $this->get_setting('general', 'scan_on_save', true);
        ?>
        <label class="shahi-toggle-switch">
            <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[general][scan_on_save]" value="1" <?php checked($value, true); ?>>
            <span class="shahi-toggle-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Automatically scan content when saving posts/pages.', 'shahi-legalops-suite'); ?></p>
        <?php
    }
    
    public function render_scan_on_publish_field() {
        $value = $this->get_setting('general', 'scan_on_publish', true);
        ?>
        <label class="shahi-toggle-switch">
            <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[general][scan_on_publish]" value="1" <?php checked($value, true); ?>>
            <span class="shahi-toggle-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Automatically scan content when publishing posts/pages.', 'shahi-legalops-suite'); ?></p>
        <?php
    }
    
    public function render_auto_scan_interval_field() {
        $value = $this->get_setting('general', 'auto_scan_interval', 'weekly');
        ?>
        <select name="<?php echo esc_attr($this->option_name); ?>[general][auto_scan_interval]" class="shahi-select">
            <option value="never" <?php selected($value, 'never'); ?>>Never</option>
            <option value="daily" <?php selected($value, 'daily'); ?>>Daily</option>
            <option value="weekly" <?php selected($value, 'weekly'); ?>>Weekly</option>
            <option value="monthly" <?php selected($value, 'monthly'); ?>>Monthly</option>
        </select>
        <p class="description"><?php esc_html_e('Frequency of automatic background scans.', 'shahi-legalops-suite'); ?></p>
        <?php
    }
    
    public function render_scanner_section_description() {
        echo '<p>' . esc_html__('Configure scanner behavior and depth.', 'shahi-legalops-suite') . '</p>';
    }
    
    public function render_scan_depth_field() {
        $value = $this->get_setting('scanner', 'scan_depth', 'full');
        ?>
        <select name="<?php echo esc_attr($this->option_name); ?>[scanner][scan_depth]" class="shahi-select">
            <option value="quick" <?php selected($value, 'quick'); ?>>Quick (Critical issues only)</option>
            <option value="standard" <?php selected($value, 'standard'); ?>>Standard (Common issues)</option>
            <option value="full" <?php selected($value, 'full'); ?>>Full (All 60+ checks)</option>
        </select>
        <p class="description"><?php esc_html_e('How thorough the scanner should be.', 'shahi-legalops-suite'); ?></p>
        <?php
    }
    
    public function render_background_scanning_field() {
        $value = $this->get_setting('scanner', 'background_scanning', true);
        ?>
        <label class="shahi-toggle-switch">
            <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[scanner][background_scanning]" value="1" <?php checked($value, true); ?>>
            <span class="shahi-toggle-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Run scans in the background without blocking the UI.', 'shahi-legalops-suite'); ?></p>
        <?php
    }
    
    public function render_widget_section_description() {
        echo '<p>' . esc_html__('Configure the frontend accessibility widget.', 'shahi-legalops-suite') . '</p>';
    }
    
    public function render_widget_enabled_field() {
        $value = $this->get_setting('widget', 'enabled', false);
        ?>
        <label class="shahi-toggle-switch">
            <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[widget][enabled]" value="1" <?php checked($value, true); ?>>
            <span class="shahi-toggle-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Enable the frontend accessibility widget for visitors.', 'shahi-legalops-suite'); ?></p>
        <?php
    }
    
    public function render_widget_position_field() {
        $value = $this->get_setting('widget', 'position', 'bottom-right');
        ?>
        <select name="<?php echo esc_attr($this->option_name); ?>[widget][position]" class="shahi-select">
            <option value="bottom-right" <?php selected($value, 'bottom-right'); ?>>Bottom Right</option>
            <option value="bottom-left" <?php selected($value, 'bottom-left'); ?>>Bottom Left</option>
            <option value="top-right" <?php selected($value, 'top-right'); ?>>Top Right</option>
            <option value="top-left" <?php selected($value, 'top-left'); ?>>Top Left</option>
        </select>
        <p class="description"><?php esc_html_e('Position of the widget button on the page.', 'shahi-legalops-suite'); ?></p>
        <?php
    }
    
    public function render_fixes_section_description() {
        echo '<p>' . esc_html__('Configure one-click fix behavior.', 'shahi-legalops-suite') . '</p>';
    }
    
    public function render_auto_fix_enabled_field() {
        $value = $this->get_setting('fixes', 'auto_fix_enabled', false);
        ?>
        <label class="shahi-toggle-switch">
            <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[fixes][auto_fix_enabled]" value="1" <?php checked($value, true); ?>>
            <span class="shahi-toggle-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Enable one-click fixes for detected issues.', 'shahi-legalops-suite'); ?></p>
        <?php
    }
    
    public function render_require_approval_field() {
        $value = $this->get_setting('fixes', 'require_approval', true);
        ?>
        <label class="shahi-toggle-switch">
            <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[fixes][require_approval]" value="1" <?php checked($value, true); ?>>
            <span class="shahi-toggle-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Require manual approval before applying fixes.', 'shahi-legalops-suite'); ?></p>
        <?php
    }
    
    public function render_ai_section_description() {
        echo '<p>' . esc_html__('Configure AI-powered features (requires API key).', 'shahi-legalops-suite') . '</p>';
    }
    
    public function render_ai_enabled_field() {
        $value = $this->get_setting('ai', 'enabled', false);
        ?>
        <label class="shahi-toggle-switch">
            <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[ai][enabled]" value="1" <?php checked($value, true); ?>>
            <span class="shahi-toggle-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Enable AI-powered features like alt text generation.', 'shahi-legalops-suite'); ?></p>
        <?php
    }
    
    public function render_ai_provider_field() {
        $value = $this->get_setting('ai', 'provider', 'openai');
        ?>
        <select name="<?php echo esc_attr($this->option_name); ?>[ai][provider]" class="shahi-select">
            <option value="openai" <?php selected($value, 'openai'); ?>>OpenAI (GPT-4)</option>
            <option value="google" <?php selected($value, 'google'); ?>>Google (Gemini)</option>
            <option value="anthropic" <?php selected($value, 'anthropic'); ?>>Anthropic (Claude)</option>
        </select>
        <p class="description"><?php esc_html_e('AI service provider to use.', 'shahi-legalops-suite'); ?></p>
        <?php
    }
    
    public function render_ai_api_key_field() {
        $value = $this->get_setting('ai', 'api_key', '');
        ?>
        <input type="password" name="<?php echo esc_attr($this->option_name); ?>[ai][api_key]" value="<?php echo esc_attr($value); ?>" class="shahi-input regular-text">
        <p class="description"><?php esc_html_e('API key for the selected AI provider.', 'shahi-legalops-suite'); ?></p>
        <?php
    }
    
    public function render_reporting_section_description() {
        echo '<p>' . esc_html__('Configure reporting and notification settings.', 'shahi-legalops-suite') . '</p>';
    }
    
    public function render_email_notifications_field() {
        $value = $this->get_setting('reporting', 'email_notifications', false);
        ?>
        <label class="shahi-toggle-switch">
            <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[reporting][email_notifications]" value="1" <?php checked($value, true); ?>>
            <span class="shahi-toggle-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Send email notifications for scan results.', 'shahi-legalops-suite'); ?></p>
        <?php
    }
    
    public function render_notification_email_field() {
        $value = $this->get_setting('reporting', 'notification_email', get_option('admin_email'));
        ?>
        <input type="email" name="<?php echo esc_attr($this->option_name); ?>[reporting][notification_email]" value="<?php echo esc_attr($value); ?>" class="shahi-input regular-text">
        <p class="description"><?php esc_html_e('Email address to receive notifications.', 'shahi-legalops-suite'); ?></p>
        <?php
    }
    
    public function render_advanced_section_description() {
        echo '<p>' . esc_html__('Advanced settings for developers and power users.', 'shahi-legalops-suite') . '</p>';
    }
    
    public function render_debug_mode_field() {
        $value = $this->get_setting('advanced', 'debug_mode', false);
        ?>
        <label class="shahi-toggle-switch">
            <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[advanced][debug_mode]" value="1" <?php checked($value, true); ?>>
            <span class="shahi-toggle-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Enable debug mode for troubleshooting.', 'shahi-legalops-suite'); ?></p>
        <?php
    }
    
    public function render_cache_results_field() {
        $value = $this->get_setting('advanced', 'cache_results', true);
        ?>
        <label class="shahi-toggle-switch">
            <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[advanced][cache_results]" value="1" <?php checked($value, true); ?>>
            <span class="shahi-toggle-slider"></span>
        </label>
        <p class="description"><?php esc_html_e('Cache scan results for improved performance.', 'shahi-legalops-suite'); ?></p>
        <?php
    }
}
