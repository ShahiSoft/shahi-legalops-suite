<?php
/**
 * ShahiPrivacyShield - GDPR/CCPA Compliance Plugin
 *
 * Example plugin built from ShahiTemplate demonstrating:
 * - Real-time compliance scanning
 * - Consent management
 * - Privacy policy automation
 * - Cookie tracking
 * - Data subject rights management
 *
 * @package           ShahiPrivacyShield
 * @author            Shahi Development Team
 * @copyright         2024 Shahi Development Team
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       ShahiPrivacyShield
 * Plugin URI:        https://github.com/shahitemplate/shahi-privacy-shield
 * Description:       Real-time GDPR/CCPA compliance scanning and consent management tool. Built from ShahiTemplate.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Shahi Development Team
 * Author URI:        https://github.com/shahitemplate
 * Text Domain:       shahi-privacy-shield
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Constants
 */
define( 'SHAHI_PRIVACY_SHIELD_VERSION', '1.0.0' );
define( 'SHAHI_PRIVACY_SHIELD_PLUGIN_FILE', __FILE__ );
define( 'SHAHI_PRIVACY_SHIELD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SHAHI_PRIVACY_SHIELD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SHAHI_PRIVACY_SHIELD_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Autoloader for plugin classes
 */
spl_autoload_register( function ( $class ) {
	$prefix   = 'ShahiPrivacyShield\\';
	$base_dir = SHAHI_PRIVACY_SHIELD_PLUGIN_DIR . 'includes/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require $file;
	}
} );

/**
 * Initialize the plugin
 */
function shahi_privacy_shield_init() {
	// Load plugin textdomain
	load_plugin_textdomain(
		'shahi-privacy-shield',
		false,
		dirname( SHAHI_PRIVACY_SHIELD_PLUGIN_BASENAME ) . '/languages'
	);

	// Initialize main plugin class
	if ( class_exists( 'ShahiPrivacyShield\\Plugin' ) ) {
		$plugin = new ShahiPrivacyShield\Plugin();
		$plugin->run();
	}
}
add_action( 'plugins_loaded', 'shahi_privacy_shield_init' );

/**
 * Activation hook
 */
function shahi_privacy_shield_activate() {
	// Create necessary database tables
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	// Consent logs table
	$table_name = $wpdb->prefix . 'privacy_shield_consents';
	$sql        = "CREATE TABLE IF NOT EXISTS $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		user_id bigint(20) DEFAULT NULL,
		ip_address varchar(45) NOT NULL,
		consent_type varchar(50) NOT NULL,
		consent_given tinyint(1) NOT NULL DEFAULT 0,
		consent_data longtext,
		user_agent text,
		created_at datetime DEFAULT CURRENT_TIMESTAMP,
		updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		KEY user_id (user_id),
		KEY consent_type (consent_type),
		KEY created_at (created_at)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	// Compliance scans table
	$table_name = $wpdb->prefix . 'privacy_shield_scans';
	$sql        = "CREATE TABLE IF NOT EXISTS $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		scan_type varchar(50) NOT NULL,
		scan_status varchar(20) NOT NULL DEFAULT 'pending',
		issues_found int(11) DEFAULT 0,
		scan_results longtext,
		started_at datetime DEFAULT CURRENT_TIMESTAMP,
		completed_at datetime DEFAULT NULL,
		PRIMARY KEY  (id),
		KEY scan_type (scan_type),
		KEY scan_status (scan_status),
		KEY started_at (started_at)
	) $charset_collate;";

	dbDelta( $sql );

	// Set default options
	add_option( 'shahi_privacy_shield_version', SHAHI_PRIVACY_SHIELD_VERSION );
	add_option( 'shahi_privacy_shield_gdpr_enabled', '1' );
	add_option( 'shahi_privacy_shield_ccpa_enabled', '1' );
	add_option( 'shahi_privacy_shield_consent_banner_enabled', '1' );
	add_option( 'shahi_privacy_shield_auto_scan_enabled', '0' );

	// Flush rewrite rules
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'shahi_privacy_shield_activate' );

/**
 * Deactivation hook
 */
function shahi_privacy_shield_deactivate() {
	// Clear scheduled events
	wp_clear_scheduled_hook( 'shahi_privacy_shield_daily_scan' );

	// Flush rewrite rules
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'shahi_privacy_shield_deactivate' );

/**
 * Uninstall hook (in separate uninstall.php file)
 */
// See uninstall.php for cleanup code
