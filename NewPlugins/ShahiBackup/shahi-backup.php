<?php
/**
 * Plugin Name: Shahi Backup
 * Plugin URI: https://github.com/yourusername/shahi-backup
 * Description: Comprehensive backup solution demonstrating database backups, file backups, and scheduled tasks using ShahiTemplate architecture
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Shahi Template Demo
 * Author URI: https://github.com/yourusername/shahi-template
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: shahi-backup
 * Domain Path: /languages
 *
 * @package ShahiBackup
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Plugin version
 */
define( 'SHAHI_BACKUP_VERSION', '1.0.0' );

/**
 * Plugin file
 */
define( 'SHAHI_BACKUP_PLUGIN_FILE', __FILE__ );

/**
 * Plugin directory
 */
define( 'SHAHI_BACKUP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin URL
 */
define( 'SHAHI_BACKUP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename
 */
define( 'SHAHI_BACKUP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Backup directory
 */
define( 'SHAHI_BACKUP_DIR', WP_CONTENT_DIR . '/shahi-backups/' );

/**
 * PSR-4 autoloader
 */
spl_autoload_register( function ( $class ) {
    $prefix = 'ShahiBackup\\';
    $base_dir = SHAHI_BACKUP_PLUGIN_DIR . 'includes/';

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
function shahi_backup_init() {
    if ( class_exists( 'ShahiBackup\\Plugin' ) ) {
        ShahiBackup\Plugin::get_instance();
    }
}
add_action( 'plugins_loaded', 'shahi_backup_init' );

/**
 * Activation hook
 */
function shahi_backup_activate() {
    global $wpdb;

    // Create backups table
    $table_name = $wpdb->prefix . 'shahi_backups';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        backup_type varchar(50) NOT NULL,
        backup_name varchar(255) NOT NULL,
        file_path varchar(500) NOT NULL,
        file_size bigint(20) UNSIGNED NOT NULL,
        status varchar(50) NOT NULL,
        started_at datetime NOT NULL,
        completed_at datetime DEFAULT NULL,
        error_message text DEFAULT NULL,
        metadata longtext DEFAULT NULL,
        PRIMARY KEY  (id),
        KEY backup_type (backup_type),
        KEY status (status),
        KEY started_at (started_at)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );

    // Create backup directory
    if ( ! file_exists( SHAHI_BACKUP_DIR ) ) {
        wp_mkdir_p( SHAHI_BACKUP_DIR );
        
        // Add .htaccess to protect backups
        $htaccess = SHAHI_BACKUP_DIR . '.htaccess';
        if ( ! file_exists( $htaccess ) ) {
            file_put_contents( $htaccess, 'deny from all' );
        }

        // Add index.php for security
        $index = SHAHI_BACKUP_DIR . 'index.php';
        if ( ! file_exists( $index ) ) {
            file_put_contents( $index, '<?php // Silence is golden' );
        }
    }

    // Set default options
    $default_options = array(
        'shahi_backup_version' => SHAHI_BACKUP_VERSION,
        'shahi_backup_auto_enabled' => false,
        'shahi_backup_schedule' => 'daily',
        'shahi_backup_keep_count' => 7,
        'shahi_backup_database_enabled' => true,
        'shahi_backup_files_enabled' => false,
        'shahi_backup_email_notifications' => false,
    );

    foreach ( $default_options as $key => $value ) {
        if ( false === get_option( $key ) ) {
            add_option( $key, $value );
        }
    }

    // Schedule cron if auto backup enabled
    if ( get_option( 'shahi_backup_auto_enabled', false ) ) {
        $schedule = get_option( 'shahi_backup_schedule', 'daily' );
        if ( ! wp_next_scheduled( 'shahi_backup_cron' ) ) {
            wp_schedule_event( time(), $schedule, 'shahi_backup_cron' );
        }
    }
}
register_activation_hook( __FILE__, 'shahi_backup_activate' );

/**
 * Deactivation hook
 */
function shahi_backup_deactivate() {
    // Clear scheduled event
    $timestamp = wp_next_scheduled( 'shahi_backup_cron' );
    if ( $timestamp ) {
        wp_unschedule_event( $timestamp, 'shahi_backup_cron' );
    }
}
register_deactivation_hook( __FILE__, 'shahi_backup_deactivate' );
