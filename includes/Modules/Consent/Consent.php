<?php
/**
 * Main Consent Module Class
 *
 * @package ShahiLegalOpsSuite\Modules\Consent
 * @since 1.0.0
 */

namespace ShahiLegalOpsSuite\Modules\Consent;

use ShahiLegalOpsSuite\Core\ModuleInterface;
use ShahiLegalOpsSuite\Modules\Consent\Controllers\ConsentRestController;
use ShahiLegalOpsSuite\Modules\Consent\Controllers\ConsentAdminController;
use ShahiLegalOpsSuite\Modules\Consent\Repositories\ConsentRepository;
use ShahiLegalOpsSuite\Modules\Consent\Services\BlockingService;
use ShahiLegalOpsSuite\Modules\Consent\Services\ConsentSignalService;
use ShahiLegalOpsSuite\Modules\Consent\Services\GeoService;

/**
 * Class Consent
 *
 * Main module for consent management and cookie compliance.
 */
class Consent implements ModuleInterface {

	/**
	 * Module ID.
	 *
	 * @var string
	 */
	const MODULE_ID = 'consent';

	/**
	 * Module version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Service instances.
	 *
	 * @var array
	 */
	private array $services = array();

	/**
	 * Detected user region (cached during request).
	 *
	 * @var array|null
	 */
	private ?array $user_region = null;

	/**
	 * Get module ID.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return self::MODULE_ID;
	}

	/**
	 * Get module name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return __( 'Consent Management', 'shahi-legalops-suite' );
	}

	/**
	 * Get module description.
	 *
	 * @return string
	 */
	public function get_description(): string {
		return __( 'Manage cookie consent, blocking, and proof of consent with GDPR, CCPA, and global compliance.', 'shahi-legalops-suite' );
	}

	/**
	 * Get module version.
	 *
	 * @return string
	 */
	public function get_version(): string {
		return self::VERSION;
	}

	/**
	 * Initialize the module.
	 *
	 * @return void
	 */
	public function initialize(): void {
		// Register custom database table.
		add_action( 'plugins_loaded', array( $this, 'create_tables' ), 5 );

		// Initialize services.
		add_action( 'plugins_loaded', array( $this, 'init_services' ), 10 );

		// Detect user region early.
		add_action( 'plugins_loaded', array( $this, 'detect_user_region' ), 11 );

		// Load regional blocking rules after region is detected.
		add_action( 'plugins_loaded', array( $this, 'load_regional_blocking_rules' ), 12 );

		// Register REST routes.
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Register admin pages.
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );

		// Enqueue frontend assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

		// Enqueue admin assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Register module card in dashboard.
		add_filter( 'complyflow_dashboard_modules', array( $this, 'register_dashboard_card' ) );

		// Load language files.
		load_plugin_textdomain( 'shahi-legalops-suite', false, dirname( PLUGIN_FILE ) . '/languages' );

		// Emit consent signals (GTM, GCM v2, etc.).
		add_action( 'wp_footer', array( $this, 'emit_consent_signals' ), 5 );
	}

	/**
	 * Create custom database tables.
	 *
	 * @return void
	 */
	public function create_tables(): void {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'complyflow_consent_logs';

		// Check if table exists to avoid errors.
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) !== $table_name ) {
			// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query(
				"CREATE TABLE $table_name (
				  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				  user_id BIGINT UNSIGNED,
				  session_id VARCHAR(64) NOT NULL,
				  region VARCHAR(10) NOT NULL,
				  categories LONGTEXT NOT NULL COMMENT 'JSON',
				  purposes LONGTEXT COMMENT 'JSON',
				  banner_version VARCHAR(50) NOT NULL,
				  timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  expiry_date DATETIME,
				  source VARCHAR(50) DEFAULT 'banner',
				  ip_hash VARCHAR(64),
				  user_agent_hash VARCHAR(64),
				  withdrawn_at DATETIME,
				  metadata LONGTEXT COMMENT 'JSON',
				  PRIMARY KEY (id),
				  KEY idx_user_id (user_id),
				  KEY idx_session_id (session_id),
				  KEY idx_region (region),
				  KEY idx_timestamp (timestamp),
				  KEY idx_withdrawn (withdrawn_at)
				) $charset_collate;"
			);
			// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared
		}
	}

	/**
	 * Initialize module services.
	 *
	 * @return void
	 */
	public function init_services(): void {
		$this->services['repository']      = new ConsentRepository();
		$this->services['blocking']        = new BlockingService( $this->services['repository'] );
		$this->services['signals']         = new ConsentSignalService();
		$this->services['geo']             = new GeoService();
	}

	/**
	 * Get a service instance.
	 *
	 * @param string $service_name Service name.
	 *
	 * @return mixed Service instance or null.
	 */
	public function get_service( string $service_name ) {
		return $this->services[ $service_name ] ?? null;
	}

	/**
	 * Detect user region from IP address.
	 *
	 * Called during plugins_loaded hook (priority 11) after services are initialized.
	 *
	 * @return void
	 */
	public function detect_user_region(): void {
		if ( null !== $this->user_region ) {
			return; // Already detected.
		}

		$geo = $this->get_service( 'geo' );
		if ( null === $geo ) {
			return; // GeoService not available.
		}

		// Detect region from user's IP.
		$this->user_region = $geo->detect_region();

		/**
		 * Action fired after user region is detected.
		 *
		 * @param array $region User region info: ['region' => 'EU', 'country' => 'DE', ...].
		 * @param self  $module Consent module instance.
		 */
		do_action( 'complyflow_region_detected', $this->user_region, $this );
	}

	/**
	 * Get detected user region.
	 *
	 * @return array {
	 *     Region information.
	 *
	 *     @type string $region          Region code: 'EU', 'UK', 'US-CA', etc.
	 *     @type string $country         ISO country code.
	 *     @type string $mode            Compliance mode: 'gdpr', 'ccpa', etc.
	 *     @type bool   $requires_consent Prior-consent blocking required.
	 * }
	 */
	public function get_user_region(): array {
		// Trigger detection if not already done.
		if ( null === $this->user_region ) {
			$this->detect_user_region();
		}

		return $this->user_region ?? array(
			'region'            => 'DEFAULT',
			'country'           => '',
			'mode'              => 'default',
			'requires_consent'  => false,
		);
	}

	/**
	 * Load regional blocking rules for the detected region.
	 *
	 * Called after region is detected. Tells BlockingService to load
	 * blocking rules appropriate for the user's region.
	 *
	 * @return void
	 */
	public function load_regional_blocking_rules(): void {
		$blocking = $this->get_service( 'blocking' );
		if ( null === $blocking ) {
			return;
		}

		// Get detected region (will trigger detection if needed).
		$region = $this->get_user_region();

		// Set region on blocking service.
		$blocking->set_region( $region['region'] );

		// Load regional blocking rules.
		$blocking->load_regional_rules();

		/**
		 * Action fired after regional blocking rules are loaded.
		 *
		 * @param array                                               $region User region.
		 * @param \ShahiLegalOpsSuite\Modules\Consent\Services\BlockingService $blocking BlockingService instance.
		 * @param \ShahiLegalOpsSuite\Modules\Consent\Consent        $module Consent module instance.
		 */
		do_action( 'complyflow_regional_blocking_loaded', $region, $blocking, $this );
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register_rest_routes(): void {
		$controller = new ConsentRestController( $this );
		$controller->register_routes();
	}

	/**
	 * Register admin menu and pages.
	 *
	 * @return void
	 */
	public function register_admin_menu(): void {
		if ( ! is_admin() ) {
			return;
		}

		$geo_service = $this->get_service( 'geo' );
		$controller  = new ConsentAdminController( $this, $geo_service );
		$controller->register_admin_page();
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @return void
	 */
	public function enqueue_frontend_assets(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$settings = $this->get_settings();
		$region   = $this->get_user_region();

		// Core blocking script (non-critical, high priority).
		wp_enqueue_script(
			'complyflow-consent-blocker',
			plugins_url( 'assets/js/consent-blocker.js', __FILE__ ),
			array(),
			self::VERSION,
			false // Load in header before other scripts.
		);

		// Banner component (footer, async).
		wp_enqueue_script(
			'complyflow-consent-banner',
			plugins_url( 'assets/js/consent-banner.js', __FILE__ ),
			array( 'wp-api-fetch' ),
			self::VERSION,
			true
		);
		wp_localize_script(
			'complyflow-consent-banner',
			'complyflowData',
			array(
				'region'    => $region['region'],
				'country'   => $region['country'],
				'mode'      => $region['mode'],
				'apiRoot'   => rest_url( 'complyflow/v1/consent/' ),
				'nonce'     => wp_create_nonce( 'complyflow-consent' ),
				'settings'  => $settings,
			)
		);

		// Consent signals script (async).
		wp_enqueue_script(
			'complyflow-consent-signals',
			plugins_url( 'assets/js/consent-signals.js', __FILE__ ),
			array( 'complyflow-consent-banner' ),
			self::VERSION,
			true
		);

		// WordPress actions/filters hooks (async).
		wp_enqueue_script(
			'complyflow-consent-hooks',
			plugins_url( 'assets/js/consent-hooks.js', __FILE__ ),
			array( 'complyflow-consent-banner' ),
			self::VERSION,
			true
		);

		// Geo region detection and styling (async).
		wp_enqueue_script(
			'complyflow-consent-geo',
			plugins_url( 'assets/js/consent-geo.js', __FILE__ ),
			array( 'complyflow-consent-banner' ),
			self::VERSION,
			true
		);

		// Styles.
		wp_enqueue_style(
			'complyflow-consent-styles',
			plugins_url( 'assets/css/consent-styles.css', __FILE__ ),
			array(),
			self::VERSION
		);

		wp_enqueue_style(
			'complyflow-consent-animations',
			plugins_url( 'assets/css/consent-animations.css', __FILE__ ),
			array( 'complyflow-consent-styles' ),
			self::VERSION
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @return void
	 */
	public function enqueue_admin_assets(): void {
		$screen = get_current_screen();
		if ( ! $screen || ! strpos( $screen->id, 'complyflow_consent' ) ) {
			return;
		}

		wp_enqueue_script(
			'complyflow-consent-admin',
			plugins_url( 'assets/js/admin/consent-settings.js', __FILE__ ),
			array( 'jquery' ),
			self::VERSION,
			true
		);

		wp_localize_script(
			'complyflow-consent-admin',
			'complyflowConsentAdmin',
			array(
				'nonce'   => wp_create_nonce( 'complyflow-consent-admin' ),
				'restUrl' => rest_url( 'complyflow/v1/consent' ),
			)
		);

		wp_enqueue_style(
			'complyflow-consent-admin',
			plugins_url( 'assets/css/admin/consent-settings.css', __FILE__ ),
			array(),
			self::VERSION
		);
	}

	/**
	 * Register module card in dashboard.
	 *
	 * @param array $modules Existing modules.
	 *
	 * @return array Updated modules.
	 */
	public function register_dashboard_card( array $modules ): array {
		$modules[ self::MODULE_ID ] = array(
			'id'          => self::MODULE_ID,
			'name'        => $this->get_name(),
			'description' => $this->get_description(),
			'version'     => self::VERSION,
			'enabled'     => $this->is_enabled(),
			'icon'        => 'dashicons-lock',
			'stats'       => array(
				'consents'      => $this->get_total_consents(),
				'compliance'    => $this->get_compliance_score(),
				'logs'          => $this->get_logs_count(),
			),
			'quick_actions' => array(
				array(
					'label' => __( 'View Logs', 'shahi-legalops-suite' ),
					'url'   => admin_url( 'admin.php?page=complyflow-consent-logs' ),
					'icon'  => 'dashicons-list-view',
				),
				array(
					'label' => __( 'Scan Cookies', 'shahi-legalops-suite' ),
					'url'   => '#',
					'action' => 'complyflow_consent_scan',
					'icon'  => 'dashicons-search',
				),
			),
			'settings_url' => '#', // Accessible via settings icon only.
		);

		return $modules;
	}

	/**
	 * Emit consent signals (Google Consent Mode, TCF, etc.).
	 *
	 * @return void
	 */
	public function emit_consent_signals(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$settings = $this->get_settings();
		if ( empty( $settings['integrations']['google_consent_mode']['enabled'] ) ) {
			return;
		}

		// Get signals service and set region.
		$signals = $this->get_service( 'signals' );
		if ( null === $signals ) {
			return;
		}

		// Set region on signals service.
		$region = $this->get_user_region();
		$signals->set_region( $region['region'] );

		// Emit regional signals.
		$consents     = $this->get_user_consent();
		$emit_signals = $signals->emit_regional_signals( $consents );

		echo '<!-- Consent signals prepared for GTM -->';
		echo wp_json_encode( $emit_signals ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Check if module is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		$settings = $this->get_settings();
		return ! empty( $settings['enabled'] );
	}

	/**
	 * Get module settings.
	 *
	 * @return array Settings array.
	 */
	public function get_settings(): array {
		$defaults = $this->get_default_settings();
		$saved    = (array) get_option( 'complyflow_consent_settings', array() );
		return wp_parse_args( $saved, $defaults );
	}

	/**
	 * Get default settings.
	 *
	 * @return array Default settings.
	 */
	public function get_default_settings(): array {
		return include PLUGIN_DIR . '/includes/modules/consent/config/consent-defaults.php';
	}

	/**
	 * Get user's current consent state.
	 *
	 * @return array User consent categories.
	 */
	public function get_user_consent(): array {
		$session_id = $this->get_session_id();
		$repository = $this->get_service( 'repository' );
		$consent    = $repository->get_consent_status( $session_id, get_current_user_id() );

		return $consent ? (array) $consent['categories'] : array();
	}

	/**
	 * Get or create session ID.
	 *
	 * @return string Session ID.
	 */
	public function get_session_id(): string {
		// Typically stored in cookie or derived from IP + UA hash.
		static $session_id = null;

		if ( null === $session_id ) {
			if ( isset( $_COOKIE['complyflow_session_id'] ) ) {
				$session_id = sanitize_text_field( wp_unslash( $_COOKIE['complyflow_session_id'] ) );
			} else {
				// Generate session ID.
				$session_id = wp_hash( wp_rand() . microtime() );
				setcookie( 'complyflow_session_id', $session_id, time() + ( 365 * 24 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN );
			}
		}

		return $session_id;
	}

	/**
	 * Get total consents recorded.
	 *
	 * @return int
	 */
	public function get_total_consents(): int {
		return $this->get_service( 'repository' )->count_logs();
	}

	/**
	 * Get compliance score (0-100).
	 *
	 * @return int Compliance score.
	 */
	public function get_compliance_score(): int {
		// TODO: Calculate based on banner configuration, blocking rules, etc.
		return 85; // Placeholder.
	}

	/**
	 * Get count of consent logs.
	 *
	 * @return int
	 */
	public function get_logs_count(): int {
		return $this->get_total_consents();
	}

	/**
	 * Get frontend UI strings (localized).
	 *
	 * @return array
	 */
	public function get_frontend_strings(): array {
		return array(
			'accept_all'    => __( 'Accept All', 'shahi-legalops-suite' ),
			'reject_all'    => __( 'Reject All', 'shahi-legalops-suite' ),
			'customize'     => __( 'Customize', 'shahi-legalops-suite' ),
			'save'          => __( 'Save Preferences', 'shahi-legalops-suite' ),
			'preferences'   => __( 'Preferences', 'shahi-legalops-suite' ),
		);
	}

	/**
	 * Activate module.
	 *
	 * @return void
	 */
	public function activate(): void {
		$settings           = $this->get_settings();
		$settings['enabled'] = true;
		update_option( 'complyflow_consent_settings', $settings );

		// Create tables if needed.
		$this->create_tables();
	}

	/**
	 * Deactivate module.
	 *
	 * @return void
	 */
	public function deactivate(): void {
		$settings           = $this->get_settings();
		$settings['enabled'] = false;
		update_option( 'complyflow_consent_settings', $settings );
	}

	/**
	 * Uninstall module (cleanup).
	 *
	 * @return void
	 */
	public function uninstall(): void {
		global $wpdb;

		// Delete option.
		delete_option( 'complyflow_consent_settings' );

		// Optional: Drop table (be careful in production).
		// $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}complyflow_consent_logs" );
	}
}
