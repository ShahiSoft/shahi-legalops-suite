<?php
/**
 * Plugin Name: Shahi SEO
 * Plugin URI: https://github.com/yourusername/shahi-seo
 * Description: Comprehensive SEO plugin demonstrating meta tag management, schema markup, and sitemap generation using ShahiTemplate architecture
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Shahi Template Demo
 * Author URI: https://github.com/yourusername/shahi-template
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: shahi-seo
 * Domain Path: /languages
 *
 * @package ShahiSEO
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Plugin version
 */
define( 'SHAHI_SEO_VERSION', '1.0.0' );

/**
 * Plugin file
 */
define( 'SHAHI_SEO_PLUGIN_FILE', __FILE__ );

/**
 * Plugin directory
 */
define( 'SHAHI_SEO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin URL
 */
define( 'SHAHI_SEO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename
 */
define( 'SHAHI_SEO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * PSR-4 autoloader
 */
spl_autoload_register( function ( $class ) {
    $prefix = 'ShahiSEO\\';
    $base_dir = SHAHI_SEO_PLUGIN_DIR . 'includes/';

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
function shahi_seo_init() {
    if ( class_exists( 'ShahiSEO\\Plugin' ) ) {
        ShahiSEO\Plugin::get_instance();
    }
}
add_action( 'plugins_loaded', 'shahi_seo_init' );

/**
 * Activation hook
 */
function shahi_seo_activate() {
    global $wpdb;

    // Create meta tags table
    $meta_table = $wpdb->prefix . 'shahi_seo_meta';
    $charset_collate = $wpdb->get_charset_collate();

    $sql_meta = "CREATE TABLE IF NOT EXISTS $meta_table (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        post_id bigint(20) UNSIGNED NOT NULL,
        meta_title varchar(255) DEFAULT NULL,
        meta_description text DEFAULT NULL,
        meta_keywords text DEFAULT NULL,
        canonical_url varchar(500) DEFAULT NULL,
        og_title varchar(255) DEFAULT NULL,
        og_description text DEFAULT NULL,
        og_image varchar(500) DEFAULT NULL,
        twitter_title varchar(255) DEFAULT NULL,
        twitter_description text DEFAULT NULL,
        twitter_image varchar(500) DEFAULT NULL,
        robots_index tinyint(1) DEFAULT 1,
        robots_follow tinyint(1) DEFAULT 1,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY post_id (post_id),
        KEY robots_index (robots_index),
        KEY created_at (created_at)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql_meta );

    // Set default options
    $default_options = array(
        'shahi_seo_version' => SHAHI_SEO_VERSION,
        'shahi_seo_meta_tags_enabled' => true,
        'shahi_seo_schema_enabled' => true,
        'shahi_seo_sitemap_enabled' => true,
        'shahi_seo_default_og_image' => '',
        'shahi_seo_twitter_username' => '',
        'shahi_seo_google_analytics' => '',
        'shahi_seo_sitemap_post_types' => array( 'post', 'page' ),
    );

    foreach ( $default_options as $key => $value ) {
        if ( false === get_option( $key ) ) {
            add_option( $key, $value );
        }
    }

    // Flush rewrite rules for sitemap
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'shahi_seo_activate' );

/**
 * Deactivation hook
 */
function shahi_seo_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'shahi_seo_deactivate' );
