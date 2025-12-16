<?php
/**
 * Plugin Name: Shahi Forms
 * Plugin URI: https://github.com/yourusername/shahi-forms
 * Description: Advanced form builder demonstrating drag-and-drop UI, form submissions, and email notifications using ShahiTemplate architecture
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Shahi Template Demo
 * Author URI: https://github.com/yourusername/shahi-template
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: shahi-forms
 * Domain Path: /languages
 *
 * @package ShahiForms
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Plugin version
 */
define( 'SHAHI_FORMS_VERSION', '1.0.0' );

/**
 * Plugin file
 */
define( 'SHAHI_FORMS_PLUGIN_FILE', __FILE__ );

/**
 * Plugin directory
 */
define( 'SHAHI_FORMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin URL
 */
define( 'SHAHI_FORMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename
 */
define( 'SHAHI_FORMS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * PSR-4 autoloader
 */
spl_autoload_register( function ( $class ) {
    $prefix = 'ShahiForms\\';
    $base_dir = SHAHI_FORMS_PLUGIN_DIR . 'includes/';

    $len = strlen( $prefix );
    if ( strncmp( $prefix, $class, $len ) !== 0 ) {
        return;
    }

    $relative_class = substr( $class, $len );
    $file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

    if ( file_exists( $file ) ) {
        require $file;
    }
} );

/**
 * Initialize the plugin
 */
function shahi_forms_init() {
    if ( class_exists( 'ShahiForms\\Plugin' ) ) {
        ShahiForms\Plugin::get_instance();
    }
}
add_action( 'plugins_loaded', 'shahi_forms_init' );

/**
 * Activation hook
 */
function shahi_forms_activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Create forms table
    $forms_table = $wpdb->prefix . 'shahi_forms';
    $sql_forms = "CREATE TABLE IF NOT EXISTS $forms_table (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        form_name varchar(255) NOT NULL,
        form_description text DEFAULT NULL,
        form_fields longtext NOT NULL,
        form_settings longtext DEFAULT NULL,
        status varchar(50) DEFAULT 'active',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY status (status),
        KEY created_at (created_at)
    ) $charset_collate;";

    // Create submissions table
    $submissions_table = $wpdb->prefix . 'shahi_form_submissions';
    $sql_submissions = "CREATE TABLE IF NOT EXISTS $submissions_table (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        form_id bigint(20) UNSIGNED NOT NULL,
        user_id bigint(20) UNSIGNED DEFAULT NULL,
        ip_address varchar(100) DEFAULT NULL,
        user_agent text DEFAULT NULL,
        submission_data longtext NOT NULL,
        status varchar(50) DEFAULT 'unread',
        submitted_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY form_id (form_id),
        KEY user_id (user_id),
        KEY status (status),
        KEY submitted_at (submitted_at)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql_forms );
    dbDelta( $sql_submissions );

    // Set default options
    $default_options = array(
        'shahi_forms_version' => SHAHI_FORMS_VERSION,
        'shahi_forms_recaptcha_enabled' => false,
        'shahi_forms_email_notifications' => true,
        'shahi_forms_notification_email' => get_option( 'admin_email' ),
    );

    foreach ( $default_options as $key => $value ) {
        if ( false === get_option( $key ) ) {
            add_option( $key, $value );
        }
    }

    // Create sample contact form
    $sample_form = array(
        'form_name' => 'Contact Form',
        'form_description' => 'Sample contact form',
        'form_fields' => wp_json_encode( array(
            array(
                'id' => 'name',
                'type' => 'text',
                'label' => 'Name',
                'required' => true,
                'placeholder' => 'Enter your name',
            ),
            array(
                'id' => 'email',
                'type' => 'email',
                'label' => 'Email',
                'required' => true,
                'placeholder' => 'your@email.com',
            ),
            array(
                'id' => 'message',
                'type' => 'textarea',
                'label' => 'Message',
                'required' => true,
                'placeholder' => 'Your message here...',
            ),
        ) ),
        'form_settings' => wp_json_encode( array(
            'submit_button_text' => 'Send Message',
            'success_message' => 'Thank you for your message!',
            'email_notification' => true,
        ) ),
        'status' => 'active',
    );

    $wpdb->insert( $forms_table, $sample_form );
}
register_activation_hook( __FILE__, 'shahi_forms_activate' );

/**
 * Deactivation hook
 */
function shahi_forms_deactivate() {
    // Cleanup if needed
}
register_deactivation_hook( __FILE__, 'shahi_forms_deactivate' );
