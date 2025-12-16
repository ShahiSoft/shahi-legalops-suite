<?php
/**
 * Support Admin Page
 *
 * Provides support resources, documentation links, and help information.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalopsSuite\Admin;

use ShahiLegalopsSuite\Core\Security;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Support Page Class
 *
 * Handles the rendering of the support and documentation page.
 * Provides links to documentation, FAQs, and support resources.
 *
 * @since 1.0.0
 */
class Support {
    
    /**
     * Security instance
     *
     * @since 1.0.0
     * @var Security
     */
    private $security;
    
    /**
     * Initialize the support page
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->security = new Security();
    }
    
    /**
     * Render the support page
     *
     * @since 1.0.0
     * @return void
     */
    public function render() {
        // Verify user capabilities
        if (!current_user_can('manage_shahi_template')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'shahi-legalops-suite'));
        }
        
        // Get support data
        $resources = $this->get_support_resources();
        $faqs = $this->get_faqs();
        $system_info = $this->get_system_info();
        $changelog = $this->get_changelog();
        
        // Load template
        include SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/support.php';
    }
    
    /**
     * Get support resources
     *
     * @since 1.0.0
     * @return array Support resources
     */
    private function get_support_resources() {
        return [
            [
                'title' => __('Documentation', 'shahi-legalops-suite'),
                'description' => __('Complete guide to using ShahiLegalopsSuite with detailed instructions and examples.', 'shahi-legalops-suite'),
                'icon' => 'dashicons-book',
                'url' => '#',
                'type' => 'primary',
            ],
            [
                'title' => __('Video Tutorials', 'shahi-legalops-suite'),
                'description' => __('Watch step-by-step video tutorials to get started quickly.', 'shahi-legalops-suite'),
                'icon' => 'dashicons-video-alt3',
                'url' => '#',
                'type' => 'success',
            ],
            [
                'title' => __('Community Forum', 'shahi-legalops-suite'),
                'description' => __('Join our community to ask questions and share experiences.', 'shahi-legalops-suite'),
                'icon' => 'dashicons-groups',
                'url' => '#',
                'type' => 'info',
            ],
            [
                'title' => __('Submit Ticket', 'shahi-legalops-suite'),
                'description' => __('Get direct support from our team by submitting a support ticket.', 'shahi-legalops-suite'),
                'icon' => 'dashicons-sos',
                'url' => '#',
                'type' => 'accent',
            ],
            [
                'title' => __('API Reference', 'shahi-legalops-suite'),
                'description' => __('Developer documentation for extending and customizing the plugin.', 'shahi-legalops-suite'),
                'icon' => 'dashicons-editor-code',
                'url' => '#',
                'type' => 'primary',
            ],
            [
                'title' => __('Changelog', 'shahi-legalops-suite'),
                'description' => __('See what\'s new in the latest version and view update history.', 'shahi-legalops-suite'),
                'icon' => 'dashicons-list-view',
                'url' => '#changelog',
                'type' => 'info',
            ],
        ];
    }
    
    /**
     * Get frequently asked questions
     *
     * @since 1.0.0
     * @return array FAQs
     */
    private function get_faqs() {
        return [
            [
                'question' => __('How do I enable or disable modules?', 'shahi-legalops-suite'),
                'answer' => __('Go to the Modules page from the main menu. You can toggle each module on or off using the switches. Changes are saved automatically.', 'shahi-legalops-suite'),
            ],
            [
                'question' => __('Can I customize the dark theme colors?', 'shahi-legalops-suite'),
                'answer' => __('The dark theme is designed to be consistent across the plugin. However, developers can override CSS variables in their theme to customize colors.', 'shahi-legalops-suite'),
            ],
            [
                'question' => __('How long is analytics data retained?', 'shahi-legalops-suite'),
                'answer' => __('By default, analytics data is retained for 90 days. You can change this in Settings > Analytics. Older data is automatically cleaned up.', 'shahi-legalops-suite'),
            ],
            [
                'question' => __('Is the plugin translation-ready?', 'shahi-legalops-suite'),
                'answer' => __('Yes! The plugin is fully translation-ready. You can use tools like Loco Translate or Poedit to translate all text strings.', 'shahi-legalops-suite'),
            ],
            [
                'question' => __('What happens to my data if I deactivate the plugin?', 'shahi-legalops-suite'),
                'answer' => __('Your data is preserved when you deactivate the plugin. To remove all data, enable "Delete data on uninstall" in Settings > General before uninstalling.', 'shahi-legalops-suite'),
            ],
            [
                'question' => __('How do I reset settings to defaults?', 'shahi-legalops-suite'),
                'answer' => __('In the Settings page, scroll to the bottom and click the "Reset to Defaults" button. This will restore all settings to their original values.', 'shahi-legalops-suite'),
            ],
            [
                'question' => __('Can I export and import settings?', 'shahi-legalops-suite'),
                'answer' => __('Yes! Use the Import/Export module to save your settings as a JSON file and restore them on another site or after a reset.', 'shahi-legalops-suite'),
            ],
            [
                'question' => __('Where can I report bugs or request features?', 'shahi-legalops-suite'),
                'answer' => __('Please submit a support ticket or visit our community forum. We actively monitor all feedback and regularly update the plugin.', 'shahi-legalops-suite'),
            ],
        ];
    }
    
    /**
     * Get system information
     *
     * @since 1.0.0
     * @return array System information
     */
    private function get_system_info() {
        global $wpdb;
        
        return [
            [
                'label' => __('Plugin Version', 'shahi-legalops-suite'),
                'value' => SHAHI_LEGALOPS_SUITE_VERSION,
            ],
            [
                'label' => __('WordPress Version', 'shahi-legalops-suite'),
                'value' => get_bloginfo('version'),
            ],
            [
                'label' => __('PHP Version', 'shahi-legalops-suite'),
                'value' => PHP_VERSION,
            ],
            [
                'label' => __('MySQL Version', 'shahi-legalops-suite'),
                'value' => $wpdb->db_version(),
            ],
            [
                'label' => __('Web Server', 'shahi-legalops-suite'),
                'value' => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : __('Unknown', 'shahi-legalops-suite'),
            ],
            [
                'label' => __('WordPress Memory Limit', 'shahi-legalops-suite'),
                'value' => WP_MEMORY_LIMIT,
            ],
            [
                'label' => __('Debug Mode', 'shahi-legalops-suite'),
                'value' => WP_DEBUG ? __('Enabled', 'shahi-legalops-suite') : __('Disabled', 'shahi-legalops-suite'),
            ],
            [
                'label' => __('Active Theme', 'shahi-legalops-suite'),
                'value' => wp_get_theme()->get('Name'),
            ],
            [
                'label' => __('Active Plugins', 'shahi-legalops-suite'),
                'value' => count(get_option('active_plugins', [])),
            ],
            [
                'label' => __('Site Language', 'shahi-legalops-suite'),
                'value' => get_locale(),
            ],
        ];
    }
    
    /**
     * Get changelog entries
     *
     * @since 1.0.0
     * @return array Changelog entries
     */
    private function get_changelog() {
        return [
            [
                'version' => '1.0.0',
                'date' => 'December 14, 2025',
                'changes' => [
                    'added' => [
                        __('Initial release', 'shahi-legalops-suite'),
                        __('Dark futuristic admin interface', 'shahi-legalops-suite'),
                        __('Multi-step onboarding wizard', 'shahi-legalops-suite'),
                        __('Modular architecture', 'shahi-legalops-suite'),
                        __('Analytics tracking system', 'shahi-legalops-suite'),
                        __('Settings management', 'shahi-legalops-suite'),
                        __('Support documentation', 'shahi-legalops-suite'),
                    ],
                    'fixed' => [],
                    'changed' => [],
                    'removed' => [],
                ],
            ],
        ];
    }
    
    /**
     * Get system status
     *
     * Returns a status check for system requirements.
     *
     * @since 1.0.0
     * @return array System status
     */
    public function get_system_status() {
        $php_version = PHP_VERSION;
        $wp_version = get_bloginfo('version');
        
        $status = [
            'php_version' => [
                'label' => __('PHP Version', 'shahi-legalops-suite'),
                'required' => '7.4',
                'current' => $php_version,
                'passed' => version_compare($php_version, '7.4', '>='),
            ],
            'wp_version' => [
                'label' => __('WordPress Version', 'shahi-legalops-suite'),
                'required' => '5.8',
                'current' => $wp_version,
                'passed' => version_compare($wp_version, '5.8', '>='),
            ],
            'pdo_extension' => [
                'label' => __('PDO Extension', 'shahi-legalops-suite'),
                'required' => __('Enabled', 'shahi-legalops-suite'),
                'current' => extension_loaded('pdo') ? __('Enabled', 'shahi-legalops-suite') : __('Disabled', 'shahi-legalops-suite'),
                'passed' => extension_loaded('pdo'),
            ],
            'json_extension' => [
                'label' => __('JSON Extension', 'shahi-legalops-suite'),
                'required' => __('Enabled', 'shahi-legalops-suite'),
                'current' => extension_loaded('json') ? __('Enabled', 'shahi-legalops-suite') : __('Disabled', 'shahi-legalops-suite'),
                'passed' => extension_loaded('json'),
            ],
            'mbstring_extension' => [
                'label' => __('Mbstring Extension', 'shahi-legalops-suite'),
                'required' => __('Enabled', 'shahi-legalops-suite'),
                'current' => extension_loaded('mbstring') ? __('Enabled', 'shahi-legalops-suite') : __('Disabled', 'shahi-legalops-suite'),
                'passed' => extension_loaded('mbstring'),
            ],
        ];
        
        return $status;
    }
    
    /**
     * Export system information as text
     *
     * @since 1.0.0
     * @return string System information as formatted text
     */
    public function export_system_info() {
        if (!current_user_can('manage_shahi_template')) {
            return '';
        }
        
        $info = $this->get_system_info();
        $output = "ShahiLegalopsSuite System Information\n";
        $output .= "==================================\n\n";
        
        foreach ($info as $item) {
            $output .= $item['label'] . ": " . $item['value'] . "\n";
        }
        
        $output .= "\nSystem Status\n";
        $output .= "=============\n\n";
        
        $status = $this->get_system_status();
        foreach ($status as $check) {
            $passed = $check['passed'] ? 'PASS' : 'FAIL';
            $output .= "[{$passed}] {$check['label']}: {$check['current']} (Required: {$check['required']})\n";
        }
        
        return $output;
    }
}
