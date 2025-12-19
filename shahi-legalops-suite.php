<?php
/**
 * Plugin Name: Shahi LegalOps Suite
 * Plugin URI: https://shahisoft.com/shahi-legalops-suite
 * Description: A professional, modular WordPress plugin template with dark futuristic UI, analytics dashboard, and extensible architecture.
 * Version: 3.0.1
 * Author: ShahiSoft
 * Author URI: https://shahisoft.com
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: shahi-legalops-suite
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Core
 * @license    GPL-3.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin Constants
 */
define( 'SHAHI_LEGALOPS_SUITE_VERSION', '3.0.1' );
define( 'SHAHI_LEGALOPS_SUITE_PATH', plugin_dir_path( __FILE__ ) );
define( 'SHAHI_LEGALOPS_SUITE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SHAHI_LEGALOPS_SUITE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SHAHI_LEGALOPS_SUITE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'SHAHI_LEGALOPS_SUITE_PLUGIN_FILE', __FILE__ );

/**
 * PSR-4 Autoloader
 */
require_once SHAHI_LEGALOPS_SUITE_PLUGIN_DIR . 'includes/Core/Autoloader.php';
ShahiLegalopsSuite\Core\Autoloader::register();

// Load translations at init hook to avoid just-in-time load notices
add_action(
	'init',
	function () {
		load_plugin_textdomain(
			'shahi-legalops-suite',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);
	},
	1
);

/**
 * Plugin Activation Hook
 */
function activate_shahi_template() {
	ShahiLegalopsSuite\Core\Activator::activate();
}
register_activation_hook( __FILE__, 'activate_shahi_template' );

/**
 * Plugin Deactivation Hook
 */
function deactivate_shahi_template() {
	ShahiLegalopsSuite\Core\Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_shahi_template' );

/**
 * Initialize the plugin
 */
function run_shahi_template() {
	$plugin = new ShahiLegalopsSuite\Core\Plugin();
	$plugin->run();
}
run_shahi_template();

/**
 * Enqueue Consent Banner Scripts and Styles
 */
function enqueue_slos_consent_banner() {
	// Get current user ID
	$user_id = get_current_user_id();
	
	// Enqueue consent banner stylesheet
	wp_enqueue_style(
		'slos-consent-banner',
		SHAHI_LEGALOPS_SUITE_PLUGIN_URL . 'assets/css/consent-banner.css',
		array(),
		SHAHI_LEGALOPS_SUITE_VERSION,
		'all'
	);
	
	// Enqueue consent banner script
	wp_enqueue_script(
		'slos-consent-banner',
		SHAHI_LEGALOPS_SUITE_PLUGIN_URL . 'assets/js/consent-banner.js',
		array(),
		SHAHI_LEGALOPS_SUITE_VERSION,
		true
	);
	
	// Localize banner configuration
	wp_localize_script(
		'slos-consent-banner',
		'slosConsentConfig',
		array(
			'apiUrl'         => rest_url( 'slos/v1' ),
			'userId'         => $user_id,
			'template'       => apply_filters( 'slos_consent_template', 'eu' ), // Options: 'eu', 'ccpa', 'simple', 'advanced'
			'position'       => apply_filters( 'slos_consent_position', 'bottom' ), // Options: 'top', 'bottom'
			'theme'          => apply_filters( 'slos_consent_theme', 'light' ), // Options: 'light', 'dark'
			'reloadOnConsent' => apply_filters( 'slos_reload_on_consent', false ),
			'privacyLink'    => apply_filters( 'slos_privacy_policy_url', home_url( '/privacy-policy' ) ),
		)
	);
	
	// Localize translations
	wp_localize_script(
		'slos-consent-banner',
		'slosConsentI18n',
		array(
			// Banner titles
			'euTitle'        => __( 'Your Consent Preferences', 'shahi-legalops-suite' ),
			'euMessage'      => __( 'We use cookies and similar tracking technologies to enhance your experience. Please select your preferences below.', 'shahi-legalops-suite' ),
			'ccpaMessage'    => __( 'We respect your privacy. You have the right to opt-out of the sale or sharing of your personal information.', 'shahi-legalops-suite' ),
			'simpleMessage'  => __( 'We use cookies to enhance your experience. Do you accept?', 'shahi-legalops-suite' ),
			
			// Buttons
			'acceptAll'      => __( 'Accept All', 'shahi-legalops-suite' ),
			'acceptSelected' => __( 'Accept Selected', 'shahi-legalops-suite' ),
			'rejectAll'      => __( 'Reject All', 'shahi-legalops-suite' ),
			'reject'         => __( 'Reject', 'shahi-legalops-suite' ),
			'accept'         => __( 'Accept', 'shahi-legalops-suite' ),
			'decline'        => __( 'Decline', 'shahi-legalops-suite' ),
			'doNotSell'      => __( 'Do Not Sell My Info', 'shahi-legalops-suite' ),
			'save'           => __( 'Save Preferences', 'shahi-legalops-suite' ),
			
			// Links
			'privacyPolicy'  => __( 'Privacy Policy', 'shahi-legalops-suite' ),
			'learnMore'      => __( 'Learn More', 'shahi-legalops-suite' ),
			
			// Purpose labels
			'purposeNecessary'     => __( 'Necessary Cookies', 'shahi-legalops-suite' ),
			'purposeFunctional'    => __( 'Functional Cookies', 'shahi-legalops-suite' ),
			'purposeAnalytics'     => __( 'Analytics', 'shahi-legalops-suite' ),
			'purposeMarketing'     => __( 'Marketing & Advertising', 'shahi-legalops-suite' ),
			'purposePreferences'   => __( 'Preference Management', 'shahi-legalops-suite' ),
			'purposePersonalization' => __( 'Personalization', 'shahi-legalops-suite' ),
			
			// Purpose descriptions
			'descNecessary'        => __( 'Essential for basic functionality and security.', 'shahi-legalops-suite' ),
			'descFunctional'       => __( 'Enables enhanced features and user experience.', 'shahi-legalops-suite' ),
			'descAnalytics'        => __( 'Helps us understand how you use our site.', 'shahi-legalops-suite' ),
			'descMarketing'        => __( 'Allows us to show relevant content and offers.', 'shahi-legalops-suite' ),
			'descPreferences'      => __( 'Remembers your user preferences.', 'shahi-legalops-suite' ),
			'descPersonalization'  => __( 'Personalizes content for better experience.', 'shahi-legalops-suite' ),
			
			// Other
			'required'  => __( 'Required', 'shahi-legalops-suite' ),
			'bannerTitle' => __( 'Manage Consent', 'shahi-legalops-suite' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'enqueue_slos_consent_banner', 100 );

/**
 * Enqueue Cookie Scanner Script
 */
function enqueue_slos_cookie_scanner() {
	wp_enqueue_script(
		'slos-cookie-scanner',
		SHAHI_LEGALOPS_SUITE_PLUGIN_URL . 'assets/js/cookie-scanner.js',
		array(),
		SHAHI_LEGALOPS_SUITE_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'enqueue_slos_cookie_scanner', 110 );

/**
 * Initialize Script Blocker
 */
function init_slos_script_blocker() {
	try {
		$blocker = new ShahiLegalopsSuite\Services\Script_Blocker_Service();
		$blocker->init();
	} catch ( \Throwable $e ) {
		// Fail-safe: do not break site if blocker fails
		error_log( 'SLOS Script Blocker init error: ' . $e->getMessage() );
	}
}
add_action( 'init', 'init_slos_script_blocker', 0 );
