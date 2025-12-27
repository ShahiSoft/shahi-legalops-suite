<?php
/**
 * Accessibility Main Page (Tabbed Interface)
 *
 * Consolidated page for all accessibility scanning functionality.
 * Includes tabs for Scanner, Dashboard, and Settings.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      3.0.2
 */

namespace ShahiLegalopsSuite\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility Main Page Class
 *
 * Provides tabbed interface for accessibility scanner management.
 *
 * @since 3.0.2
 */
class AccessibilityMainPage {

	/**
	 * Current active tab
	 *
	 * @var string
	 */
	private $current_tab = 'scanner';

	/**
	 * Scanner page instance
	 *
	 * @var \ShahiLegalopsSuite\Modules\AccessibilityScanner\Admin\ScannerPage
	 */
	private $scanner_page;

	/**
	 * Dashboard page instance
	 *
	 * @var \ShahiLegalopsSuite\Modules\AccessibilityScanner\Admin\AccessibilityDashboard
	 */
	private $dashboard_page;

	/**
	 * Settings page instance
	 *
	 * @var \ShahiLegalopsSuite\Modules\AccessibilityScanner\Admin\AccessibilitySettings
	 */
	private $settings_page;

	/**
	 * Constructor
	 *
	 * @since 3.0.2
	 */
	public function __construct() {
		// Initialize page instances
		$this->scanner_page   = new \ShahiLegalopsSuite\Modules\AccessibilityScanner\Admin\ScannerPage();
		$this->dashboard_page = new \ShahiLegalopsSuite\Modules\AccessibilityScanner\Admin\AccessibilityDashboard();
		$this->settings_page  = new \ShahiLegalopsSuite\Modules\AccessibilityScanner\Admin\AccessibilitySettings();
	}

	/**
	 * Get available tabs
	 *
	 * @since 3.0.2
	 * @return array Tab key => Label pairs
	 */
	private function get_tabs() {
		return array(
			'tools'     => __( 'Tools & Scanner', 'shahi-legalops-suite' ),
			'dashboard' => __( 'Dashboard & Reports', 'shahi-legalops-suite' ),
			'settings'  => __( 'Settings', 'shahi-legalops-suite' ),
		);
	}

	/**
	 * Render the main page
	 *
	 * @since 3.0.2
	 * @return void
	 */
	public function render() {
		// Check capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shahi-legalops-suite' ) );
		}

		// Get current tab
		$this->current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'tools';

		// Validate tab
		$valid_tabs = array_keys( $this->get_tabs() );
		if ( ! in_array( $this->current_tab, $valid_tabs, true ) ) {
			$this->current_tab = 'tools';
		}

		?>
		<div class="wrap slos-accessibility-main-page">
			<h1 class="wp-heading-inline">
				<?php echo esc_html__( 'Accessibility Scanner', 'shahi-legalops-suite' ); ?>
			</h1>
			
			<?php $this->render_tabs(); ?>
			
			<hr class="wp-header-end">
			
			<div class="tab-content slos-tab-content">
				<?php $this->render_tab_content(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render tab navigation
	 *
	 * @since 3.0.2
	 * @return void
	 */
	private function render_tabs() {
		$tabs        = $this->get_tabs();
		$current_url = admin_url( 'admin.php?page=slos-accessibility' );

		echo '<nav class="nav-tab-wrapper wp-clearfix" aria-label="' . esc_attr__( 'Secondary menu', 'shahi-legalops-suite' ) . '">';
		
		foreach ( $tabs as $tab_key => $tab_label ) {
			$active_class = ( $this->current_tab === $tab_key ) ? 'nav-tab-active' : '';
			$tab_url      = add_query_arg( 'tab', $tab_key, $current_url );
			
			printf(
				'<a href="%s" class="nav-tab %s">%s</a>',
				esc_url( $tab_url ),
				esc_attr( $active_class ),
				esc_html( $tab_label )
			);
		}
		
		echo '</nav>';
	}

	/**
	 * Render current tab content
	 *
	 * @since 3.0.2
	 * @return void
	 */
	private function render_tab_content() {
		switch ( $this->current_tab ) {
			case 'tools':
				$this->render_tools_tab();
				break;

			case 'dashboard':
				$this->render_dashboard_tab();
				break;

			case 'settings':
				$this->render_settings_tab();
				break;

			default:
				$this->render_tools_tab();
		}
	}

	/**
	 * Render Tools tab content (formerly Scanner)
	 *
	 * @since 3.0.2
	 * @return void
	 */
	private function render_tools_tab() {
		echo '<div class="slos-tab-pane slos-tools-pane">';
		
		// Render tools page content
		if ( method_exists( $this->scanner_page, 'render_content' ) ) {
			$this->scanner_page->render_content();
		} else {
			// Fallback
			echo '<p>' . esc_html__( 'Tools interface will be displayed here.', 'shahi-legalops-suite' ) . '</p>';
		}
		
		echo '</div>';
	}

	/**
	 * Render Dashboard tab content
	 *
	 * @since 3.0.2
	 * @return void
	 */
	private function render_dashboard_tab() {
		echo '<div class="slos-tab-pane slos-dashboard-pane">';
		
		// Render dashboard page content
		if ( method_exists( $this->dashboard_page, 'render_content' ) ) {
			$this->dashboard_page->render_content();
		} else {
			echo '<p>' . esc_html__( 'Dashboard and reports will be displayed here.', 'shahi-legalops-suite' ) . '</p>';
		}
		
		echo '</div>';
	}

	/**
	 * Render Settings tab content
	 *
	 * @since 3.0.2
	 * @return void
	 */
	private function render_settings_tab() {
		echo '<div class="slos-tab-pane slos-settings-pane">';
		
		// Render settings page content
		if ( method_exists( $this->settings_page, 'render_content' ) ) {
			$this->settings_page->render_content();
		} else {
			// Link to settings page
			?>
			<div class="slos-settings-wrapper">
				<p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=slos-accessibility-settings' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Configure Accessibility Settings', 'shahi-legalops-suite' ); ?>
					</a>
				</p>
				<p class="description">
					<?php esc_html_e( 'Configure accessibility scanner rules, automatic fixes, and compliance requirements.', 'shahi-legalops-suite' ); ?>
				</p>
			</div>
			<?php
		}
		
		echo '</div>';
	}
}
