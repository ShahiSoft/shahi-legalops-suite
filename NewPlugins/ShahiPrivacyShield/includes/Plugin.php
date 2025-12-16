<?php
/**
 * Main Plugin Class
 *
 * @package ShahiPrivacyShield
 */

namespace ShahiPrivacyShield;

/**
 * Main Plugin Class
 */
class Plugin {

	/**
	 * Modules
	 *
	 * @var array
	 */
	private $modules = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->init_modules();
		$this->init_hooks();
	}

	/**
	 * Load dependencies
	 */
	private function load_dependencies() {
		// Core modules will be autoloaded
	}

	/**
	 * Initialize modules
	 */
	private function init_modules() {
		// Compliance Scanner Module
		if ( class_exists( 'ShahiPrivacyShield\\Modules\\ComplianceScanner' ) ) {
			$this->modules['compliance_scanner'] = new Modules\ComplianceScanner();
		}

		// Consent Management Module
		if ( class_exists( 'ShahiPrivacyShield\\Modules\\ConsentManager' ) ) {
			$this->modules['consent_manager'] = new Modules\ConsentManager();
		}
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'Privacy Shield', 'shahi-privacy-shield' ),
			__( 'Privacy Shield', 'shahi-privacy-shield' ),
			'manage_options',
			'shahi-privacy-shield',
			array( $this, 'render_dashboard' ),
			'dashicons-shield',
			80
		);

		add_submenu_page(
			'shahi-privacy-shield',
			__( 'Dashboard', 'shahi-privacy-shield' ),
			__( 'Dashboard', 'shahi-privacy-shield' ),
			'manage_options',
			'shahi-privacy-shield',
			array( $this, 'render_dashboard' )
		);

		add_submenu_page(
			'shahi-privacy-shield',
			__( 'Compliance Scan', 'shahi-privacy-shield' ),
			__( 'Compliance Scan', 'shahi-privacy-shield' ),
			'manage_options',
			'shahi-privacy-shield-scan',
			array( $this, 'render_scan_page' )
		);

		add_submenu_page(
			'shahi-privacy-shield',
			__( 'Consent Management', 'shahi-privacy-shield' ),
			__( 'Consent Management', 'shahi-privacy-shield' ),
			'manage_options',
			'shahi-privacy-shield-consent',
			array( $this, 'render_consent_page' )
		);

		add_submenu_page(
			'shahi-privacy-shield',
			__( 'Settings', 'shahi-privacy-shield' ),
			__( 'Settings', 'shahi-privacy-shield' ),
			'manage_options',
			'shahi-privacy-shield-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Render dashboard page
	 */
	public function render_dashboard() {
		include SHAHI_PRIVACY_SHIELD_PLUGIN_DIR . 'admin/views/dashboard.php';
	}

	/**
	 * Render scan page
	 */
	public function render_scan_page() {
		include SHAHI_PRIVACY_SHIELD_PLUGIN_DIR . 'admin/views/scan.php';
	}

	/**
	 * Render consent page
	 */
	public function render_consent_page() {
		include SHAHI_PRIVACY_SHIELD_PLUGIN_DIR . 'admin/views/consent.php';
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		include SHAHI_PRIVACY_SHIELD_PLUGIN_DIR . 'admin/views/settings.php';
	}

	/**
	 * Enqueue admin assets
	 *
	 * @param string $hook Current admin page hook
	 */
	public function enqueue_admin_assets( $hook ) {
		// Only load on our plugin pages
		if ( strpos( $hook, 'shahi-privacy-shield' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'shahi-privacy-shield-admin',
			SHAHI_PRIVACY_SHIELD_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			SHAHI_PRIVACY_SHIELD_VERSION
		);

		wp_enqueue_script(
			'shahi-privacy-shield-admin',
			SHAHI_PRIVACY_SHIELD_PLUGIN_URL . 'admin/js/admin.js',
			array( 'jquery' ),
			SHAHI_PRIVACY_SHIELD_VERSION,
			true
		);

		wp_localize_script(
			'shahi-privacy-shield-admin',
			'shahiPrivacyShield',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'shahi_privacy_shield_nonce' ),
			)
		);
	}

	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_frontend_assets() {
		// Only load if consent banner is enabled
		if ( ! get_option( 'shahi_privacy_shield_consent_banner_enabled', '1' ) ) {
			return;
		}

		wp_enqueue_style(
			'shahi-privacy-shield-frontend',
			SHAHI_PRIVACY_SHIELD_PLUGIN_URL . 'public/css/consent-banner.css',
			array(),
			SHAHI_PRIVACY_SHIELD_VERSION
		);

		wp_enqueue_script(
			'shahi-privacy-shield-consent',
			SHAHI_PRIVACY_SHIELD_PLUGIN_URL . 'public/js/consent-banner.js',
			array( 'jquery' ),
			SHAHI_PRIVACY_SHIELD_VERSION,
			true
		);

		wp_localize_script(
			'shahi-privacy-shield-consent',
			'shahiPrivacyShieldConsent',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'shahi_privacy_shield_consent_nonce' ),
			)
		);
	}

	/**
	 * Run the plugin
	 */
	public function run() {
		// Plugin is running
	}
}
