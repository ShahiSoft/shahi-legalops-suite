<?php
/**
 * Consent Admin Controller
 *
 * Handles admin interface for consent module settings and region management.
 *
 * @package ShahiLegalOpsSuite\Modules\Consent\Controllers
 * @since 1.0.0
 */

namespace ShahiLegalOpsSuite\Modules\Consent\Controllers;

use ShahiLegalOpsSuite\Modules\Consent\Services\GeoService;

/**
 * Class ConsentAdminController
 *
 * Manages WordPress admin pages for consent settings.
 */
class ConsentAdminController {

	/**
	 * Module instance.
	 *
	 * @var object
	 */
	private $module;

	/**
	 * GeoService instance.
	 *
	 * @var GeoService
	 */
	private $geo_service;

	/**
	 * Admin page slug.
	 *
	 * @var string
	 */
	const ADMIN_PAGE_SLUG = 'complyflow-consent-settings';

	/**
	 * Settings option key.
	 *
	 * @var string
	 */
	const SETTINGS_OPTION_KEY = 'complyflow_consent_admin_settings';

	/**
	 * Constructor.
	 *
	 * @param object     $module      Module instance.
	 * @param GeoService $geo_service GeoService instance.
	 */
	public function __construct( $module, GeoService $geo_service ) {
		$this->module      = $module;
		$this->geo_service = $geo_service;
	}

	/**
	 * Register admin menu and page.
	 *
	 * Called on admin_menu hook.
	 *
	 * @return void
	 */
	public function register_admin_page(): void {
		add_submenu_page(
			'tools.php',
			__( 'Consent Management Settings', 'shahi-legalops-suite' ),
			__( 'Consent Management', 'shahi-legalops-suite' ),
			'read',
			self::ADMIN_PAGE_SLUG,
			array( $this, 'render_admin_page' ),
			99
		);
	}

	/**
	 * Render the admin page.
	 *
	 * @return void
	 */
	public function render_admin_page(): void {

		// Process form submission.
		if ( isset( $_POST['complyflow_region_override_nonce'] ) ) {
			$this->process_settings_form();
		}

		$admin_settings = get_option( self::SETTINGS_OPTION_KEY, array() );
		$detected_region = $this->module->get_user_region();
		$manual_override  = ! empty( $admin_settings['region_override'] ) ? $admin_settings['region_override'] : '';
		$retention_days   = ! empty( $admin_settings['retention_days'] ) ? (int) $admin_settings['retention_days'] : 30;

		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Consent Management Settings', 'shahi-legalops-suite' ); ?></h1>

			<div class="complyflow-admin-section">
				<h2><?php echo esc_html__( 'Region Detection', 'shahi-legalops-suite' ); ?></h2>
				<table class="form-table">
					<tr>
						<th scope="row">
							<label><?php echo esc_html__( 'Detected Region', 'shahi-legalops-suite' ); ?></label>
						</th>
						<td>
							<strong><?php echo esc_html( $detected_region['region'] ); ?></strong>
							<?php if ( ! empty( $detected_region['country'] ) ) { ?>
								(<?php echo esc_html( $detected_region['country'] ); ?>)
							<?php } ?>
							<p class="description">
								<?php echo esc_html__( 'Compliance mode:', 'shahi-legalops-suite' ); ?>
								<code><?php echo esc_html( $detected_region['mode'] ); ?></code>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label><?php echo esc_html__( 'Requires Consent', 'shahi-legalops-suite' ); ?></label>
						</th>
						<td>
							<?php
							if ( $detected_region['requires_consent'] ) {
								echo '<span style="color: #d63638; font-weight: bold;">' . esc_html__( 'Yes - Prior consent required', 'shahi-legalops-suite' ) . '</span>';
							} else {
								echo '<span style="color: #007cba; font-weight: bold;">' . esc_html__( 'No - Opt-out consent model', 'shahi-legalops-suite' ) . '</span>';
							}
							?>
						</td>
					</tr>
				</table>
			</div>

			<div class="complyflow-admin-section">
				<h2><?php echo esc_html__( 'Region Settings', 'shahi-legalops-suite' ); ?></h2>
				<form method="post" action="">
					<?php wp_nonce_field( 'complyflow_region_override', 'complyflow_region_override_nonce' ); ?>

					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="region_override"><?php echo esc_html__( 'Manual Region Override', 'shahi-legalops-suite' ); ?></label>
							</th>
							<td>
								<select name="region_override" id="region_override">
									<option value=""><?php echo esc_html__( 'Use Detected Region', 'shahi-legalops-suite' ); ?></option>
									<option value="EU" <?php selected( $manual_override, 'EU' ); ?>>
										<?php echo esc_html__( 'European Union (GDPR)', 'shahi-legalops-suite' ); ?>
									</option>
									<option value="UK" <?php selected( $manual_override, 'UK' ); ?>>
										<?php echo esc_html__( 'United Kingdom (UK GDPR)', 'shahi-legalops-suite' ); ?>
									</option>
									<option value="US-CA" <?php selected( $manual_override, 'US-CA' ); ?>>
										<?php echo esc_html__( 'California, USA (CCPA)', 'shahi-legalops-suite' ); ?>
									</option>
									<option value="BR" <?php selected( $manual_override, 'BR' ); ?>>
										<?php echo esc_html__( 'Brazil (LGPD)', 'shahi-legalops-suite' ); ?>
									</option>
									<option value="AU" <?php selected( $manual_override, 'AU' ); ?>>
										<?php echo esc_html__( 'Australia (Privacy Act)', 'shahi-legalops-suite' ); ?>
									</option>
									<option value="CA" <?php selected( $manual_override, 'CA' ); ?>>
										<?php echo esc_html__( 'Canada (PIPEDA)', 'shahi-legalops-suite' ); ?>
									</option>
									<option value="ZA" <?php selected( $manual_override, 'ZA' ); ?>>
										<?php echo esc_html__( 'South Africa (POPIA)', 'shahi-legalops-suite' ); ?>
									</option>
									<option value="DEFAULT" <?php selected( $manual_override, 'DEFAULT' ); ?>>
										<?php echo esc_html__( 'Default (No specific compliance)', 'shahi-legalops-suite' ); ?>
									</option>
								</select>
								<p class="description">
									<?php echo esc_html__( 'Override the automatically detected region. Useful for testing different compliance modes.', 'shahi-legalops-suite' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="retention_days"><?php echo esc_html__( 'Consent Data Retention (Days)', 'shahi-legalops-suite' ); ?></label>
							</th>
							<td>
								<input type="number" name="retention_days" id="retention_days" 
									   value="<?php echo esc_attr( $retention_days ); ?>" 
									   min="1" max="3650" step="1" />
								<p class="description">
									<?php echo esc_html__( 'How long to retain consent records. Range: 1-3650 days.', 'shahi-legalops-suite' ); ?>
								</p>
							</td>
						</tr>
					</table>

					<?php submit_button( __( 'Save Settings', 'shahi-legalops-suite' ), 'primary', 'submit', true ); ?>
				</form>
			</div>

			<div class="complyflow-admin-section">
				<h2><?php echo esc_html__( 'Regional Blocking Rules', 'shahi-legalops-suite' ); ?></h2>
				<?php $this->render_blocking_rules( $detected_region ); ?>
			</div>

			<div class="complyflow-admin-section">
				<h2><?php echo esc_html__( 'System Information', 'shahi-legalops-suite' ); ?></h2>
				<?php $this->render_system_info(); ?>
			</div>
		</div>

		<style>
			.complyflow-admin-section {
				background: #fff;
				border: 1px solid #ccd0d4;
				border-radius: 4px;
				padding: 20px;
				margin: 20px 0;
			}

			.complyflow-admin-section h2 {
				margin-top: 0;
			}

			.complyflow-blocking-rules {
				border-collapse: collapse;
				width: 100%;
				margin-top: 10px;
			}

			.complyflow-blocking-rules th,
			.complyflow-blocking-rules td {
				border: 1px solid #ddd;
				padding: 10px;
				text-align: left;
			}

			.complyflow-blocking-rules th {
				background-color: #f1f1f1;
				font-weight: bold;
			}

			.complyflow-rule-active {
				color: #007cba;
				font-weight: bold;
			}

			.complyflow-rule-inactive {
				color: #6c757d;
			}
		</style>
		<?php
	}

	/**
	 * Process admin settings form submission.
	 *
	 * @return void
	 */
	private function process_settings_form(): void {
		// Verify nonce.
		if ( ! isset( $_POST['complyflow_region_override_nonce'] ) ||
		     ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['complyflow_region_override_nonce'] ) ), 'complyflow_region_override' ) ) {
			wp_die( esc_html__( 'Security check failed', 'shahi-legalops-suite' ) );
		}

		// Check capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'shahi-legalops-suite' ) );
		}

		$admin_settings = get_option( self::SETTINGS_OPTION_KEY, array() );

		// Process region override.
		if ( isset( $_POST['region_override'] ) ) {
			$region_override = sanitize_text_field( wp_unslash( $_POST['region_override'] ) );
			$valid_regions   = array( 'EU', 'UK', 'US-CA', 'BR', 'AU', 'CA', 'ZA', 'DEFAULT', '' );

			if ( in_array( $region_override, $valid_regions, true ) ) {
				$admin_settings['region_override'] = $region_override;
			}
		}

		// Process retention days.
		if ( isset( $_POST['retention_days'] ) ) {
			$retention_days = (int) sanitize_text_field( wp_unslash( $_POST['retention_days'] ) );
			$retention_days = max( 1, min( 3650, $retention_days ) ); // Clamp between 1 and 3650.
			$admin_settings['retention_days'] = $retention_days;
		}

		// Save settings.
		update_option( self::SETTINGS_OPTION_KEY, $admin_settings );

		// Show success message.
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo esc_html__( 'Settings saved successfully!', 'shahi-legalops-suite' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Render blocking rules table.
	 *
	 * @param array $region Region information.
	 *
	 * @return void
	 */
	private function render_blocking_rules( array $region ): void {
		$blocking = $this->module->get_service( 'blocking' );
		if ( null === $blocking ) {
			echo '<p class="error">' . esc_html__( 'BlockingService not available', 'shahi-legalops-suite' ) . '</p>';
			return;
		}

		// Load rules for the current region.
		$blocking->set_region( $region['region'] );
		$blocking->load_regional_rules();

		// Get the reflection of BlockingService to access rules property.
		$blocking_ref = new \ReflectionClass( $blocking );
		$rules_prop   = $blocking_ref->getProperty( 'rules' );
		$rules_prop->setAccessible( true );
		$rules = $rules_prop->getValue( $blocking );

		if ( empty( $rules ) ) {
			echo '<p>' . esc_html__( 'No blocking rules configured for this region.', 'shahi-legalops-suite' ) . '</p>';
			return;
		}

		?>
		<p>
			<?php
			printf(
				esc_html__( 'Blocking rules active for region: %s', 'shahi-legalops-suite' ),
				'<code>' . esc_html( $region['region'] ) . '</code>'
			);
			?>
		</p>
		<table class="complyflow-blocking-rules">
			<thead>
				<tr>
					<th><?php echo esc_html__( 'Service', 'shahi-legalops-suite' ); ?></th>
					<th><?php echo esc_html__( 'Selectors', 'shahi-legalops-suite' ); ?></th>
					<th><?php echo esc_html__( 'Category', 'shahi-legalops-suite' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rules as $service => $rule ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $service ); ?></strong></td>
						<td>
							<?php
							if ( is_array( $rule['selectors'] ) ) {
								echo esc_html( implode( ', ', array_slice( $rule['selectors'], 0, 3 ) ) );
								if ( count( $rule['selectors'] ) > 3 ) {
									echo ' <em>+' . esc_html( count( $rule['selectors'] ) - 3 ) . ' more</em>';
								}
							} else {
								echo esc_html( $rule['selectors'] );
							}
							?>
						</td>
						<td>
							<?php echo esc_html( $rule['category'] ); ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render system information.
	 *
	 * @return void
	 */
	private function render_system_info(): void {
		$module_version = $this->module->get_version();
		$php_version    = phpversion();

		?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label><?php echo esc_html__( 'Module Version', 'shahi-legalops-suite' ); ?></label>
				</th>
				<td>
					<code><?php echo esc_html( $module_version ); ?></code>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php echo esc_html__( 'PHP Version', 'shahi-legalops-suite' ); ?></label>
				</th>
				<td>
					<code><?php echo esc_html( $php_version ); ?></code>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php echo esc_html__( 'GeoService Available', 'shahi-legalops-suite' ); ?></label>
				</th>
				<td>
					<?php
					if ( null !== $this->geo_service ) {
						echo '<span style="color: #28a745; font-weight: bold;">' . esc_html__( 'Yes', 'shahi-legalops-suite' ) . '</span>';
					} else {
						echo '<span style="color: #dc3545; font-weight: bold;">' . esc_html__( 'No', 'shahi-legalops-suite' ) . '</span>';
					}
					?>
				</td>
			</tr>
		</table>
		<?php
	}
}
