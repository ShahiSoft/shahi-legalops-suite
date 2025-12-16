<?php
/**
 * Fired during plugin activation
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
 * Activator Class
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since 1.0.0
 */
class Activator {
    
    /**
     * Activate the plugin
     *
     * Creates database tables, sets default options, and performs initial setup.
     *
     * @since 1.0.0
     * @return void
     */
    public static function activate() {
        global $wpdb;
        
        // Create custom database tables
        self::create_tables();
        
        // Set default options
        self::set_default_options();
        
        // Set installation timestamp
        if (!get_option('shahi_legalops_suite_installed_at')) {
            add_option('shahi_legalops_suite_installed_at', current_time('mysql'));
        }
        
        // Set version
        update_option('shahi_legalops_suite_version', SHAHI_LEGALOPS_SUITE_VERSION);
        
        // Add custom capabilities
        \ShahiLegalopsSuite\Admin\MenuManager::add_capabilities();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Create custom database tables
     *
     * @since 1.0.0
     * @return void
     */
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        // Analytics table
        $table_analytics = $wpdb->prefix . 'shahi_analytics';
        $sql_analytics = "CREATE TABLE IF NOT EXISTS {$table_analytics} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL,
            event_data longtext,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent varchar(255) DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY event_type (event_type),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) {$charset_collate};";
        
        dbDelta($sql_analytics);
        
        // Modules table
        $table_modules = $wpdb->prefix . 'shahi_modules';
        $sql_modules = "CREATE TABLE IF NOT EXISTS {$table_modules} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            module_key varchar(100) NOT NULL,
            is_enabled tinyint(1) NOT NULL DEFAULT 1,
            settings longtext,
            last_updated datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY module_key (module_key)
        ) {$charset_collate};";
        
        dbDelta($sql_modules);
        
        // Onboarding table
        $table_onboarding = $wpdb->prefix . 'shahi_onboarding';
        $sql_onboarding = "CREATE TABLE IF NOT EXISTS {$table_onboarding} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            step_completed varchar(100) NOT NULL,
            data_collected longtext,
            completed_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY step_completed (step_completed)
        ) {$charset_collate};";
        
        dbDelta($sql_onboarding);
    }
    
    /**
     * Set default plugin options
     *
     * @since 1.0.0
     * @return void
     */
    private static function set_default_options() {
        // General settings
        if (!get_option('shahi_legalops_suite_settings')) {
            $default_settings = array(
                'plugin_enabled' => true,
                'admin_email' => get_option('admin_email'),
                'date_format' => get_option('date_format'),
                'time_format' => get_option('time_format'),
            );
            add_option('shahi_legalops_suite_settings', $default_settings);
        }
        
        // Advanced settings
        if (!get_option('shahi_legalops_suite_advanced_settings')) {
            $advanced_settings = array(
                'debug_mode' => false,
                'custom_css' => '',
                'custom_js' => '',
                'developer_mode' => false,
                'api_rate_limit' => 100,
            );
            add_option('shahi_legalops_suite_advanced_settings', $advanced_settings);
        }
        
        // Uninstall preferences (preserve data by default)
        if (!get_option('shahi_legalops_suite_uninstall_preferences')) {
            $uninstall_preferences = array(
                'preserve_all' => true,
                'delete_settings' => false,
                'delete_analytics' => false,
                'delete_posts' => false,
                'delete_capabilities' => false,
                'delete_tables' => false,
            );
            add_option('shahi_legalops_suite_uninstall_preferences', $uninstall_preferences);
        }
        
        // Enabled modules
        if (!get_option('shahi_legalops_suite_modules_enabled')) {
            $modules_enabled = array(
                'analytics' => true,
                'security' => true,
            );
            add_option('shahi_legalops_suite_modules_enabled', $modules_enabled);
        }
        
        // Onboarding completion status
        if (!get_option('shahi_legalops_suite_onboarding_completed')) {
            add_option('shahi_legalops_suite_onboarding_completed', false);
        }
    }
}
