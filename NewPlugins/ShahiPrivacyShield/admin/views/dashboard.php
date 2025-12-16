<?php
/**
 * Admin Dashboard View
 *
 * @package ShahiPrivacyShield
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get latest scan results
$scanner      = new \ShahiPrivacyShield\Modules\ComplianceScanner();
$latest_scan  = $scanner->get_latest_scan();

// Get consent statistics
$consent_manager = new \ShahiPrivacyShield\Modules\ConsentManager();
$consent_stats   = $consent_manager->get_consent_statistics();
?>

<div class="wrap shahi-privacy-shield-dashboard">
	<h1><?php esc_html_e( 'Privacy Shield Dashboard', 'shahi-privacy-shield' ); ?></h1>

	<div class="shahi-privacy-shield-grid">
		<!-- Compliance Status -->
		<div class="shahi-privacy-shield-card">
			<h2><?php esc_html_e( 'Compliance Status', 'shahi-privacy-shield' ); ?></h2>
			
			<?php if ( $latest_scan ) : ?>
				<div class="compliance-overview">
					<div class="status-item">
						<span class="status-icon <?php echo $latest_scan['issues_found'] === 0 ? 'success' : 'warning'; ?>"></span>
						<div class="status-details">
							<h3>
								<?php
								if ( $latest_scan['issues_found'] === 0 ) {
									esc_html_e( 'Compliant', 'shahi-privacy-shield' );
								} else {
									printf(
										/* translators: %d: number of issues */
										esc_html__( '%d Issues Found', 'shahi-privacy-shield' ),
										(int) $latest_scan['issues_found']
									);
								}
								?>
							</h3>
							<p class="description">
								<?php
								printf(
									/* translators: %s: scan date */
									esc_html__( 'Last scan: %s', 'shahi-privacy-shield' ),
									esc_html( mysql2date( 'F j, Y g:i a', $latest_scan['completed_at'] ) )
								);
								?>
							</p>
						</div>
					</div>

					<?php if ( ! empty( $latest_scan['scan_results']['issues'] ) ) : ?>
						<div class="issues-summary">
							<h4><?php esc_html_e( 'Critical Issues:', 'shahi-privacy-shield' ); ?></h4>
							<ul>
								<?php foreach ( $latest_scan['scan_results']['issues'] as $issue ) : ?>
									<li class="severity-<?php echo esc_attr( $issue['severity'] ); ?>">
										<strong><?php echo esc_html( $issue['message'] ); ?></strong>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<a href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-privacy-shield-scan' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Run New Scan', 'shahi-privacy-shield' ); ?>
					</a>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'No scans have been run yet.', 'shahi-privacy-shield' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-privacy-shield-scan' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Run First Scan', 'shahi-privacy-shield' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<!-- Consent Statistics -->
		<div class="shahi-privacy-shield-card">
			<h2><?php esc_html_e( 'Consent Statistics', 'shahi-privacy-shield' ); ?></h2>
			
			<div class="consent-stats">
				<div class="stat-item">
					<div class="stat-number"><?php echo number_format_i18n( $consent_stats['total_consents'] ); ?></div>
					<div class="stat-label"><?php esc_html_e( 'Total Consents', 'shahi-privacy-shield' ); ?></div>
				</div>

				<div class="stat-item">
					<div class="stat-number"><?php echo number_format_i18n( $consent_stats['recent_consents'] ); ?></div>
					<div class="stat-label"><?php esc_html_e( 'Last 7 Days', 'shahi-privacy-shield' ); ?></div>
				</div>

				<div class="stat-item">
					<div class="stat-number"><?php echo number_format_i18n( $consent_stats['accepted_any'] ); ?></div>
					<div class="stat-label"><?php esc_html_e( 'Accepted Users', 'shahi-privacy-shield' ); ?></div>
				</div>
			</div>

			<?php if ( ! empty( $consent_stats['consent_breakdown'] ) ) : ?>
				<div class="consent-breakdown">
					<h4><?php esc_html_e( 'Consent by Category:', 'shahi-privacy-shield' ); ?></h4>
					<ul>
						<?php foreach ( $consent_stats['consent_breakdown'] as $type => $data ) : ?>
							<li>
								<span class="consent-type"><?php echo esc_html( $data['label'] ); ?>:</span>
								<strong><?php echo number_format_i18n( $data['count'] ); ?></strong>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<a href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-privacy-shield-consent' ) ); ?>" class="button">
				<?php esc_html_e( 'View Details', 'shahi-privacy-shield' ); ?>
			</a>
		</div>

		<!-- Quick Actions -->
		<div class="shahi-privacy-shield-card">
			<h2><?php esc_html_e( 'Quick Actions', 'shahi-privacy-shield' ); ?></h2>
			
			<div class="quick-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-privacy-shield-scan' ) ); ?>" class="action-button">
					<span class="dashicons dashicons-search"></span>
					<?php esc_html_e( 'Run Compliance Scan', 'shahi-privacy-shield' ); ?>
				</a>

				<a href="<?php echo esc_url( admin_url( 'privacy.php' ) ); ?>" class="action-button">
					<span class="dashicons dashicons-admin-page"></span>
					<?php esc_html_e( 'Edit Privacy Policy', 'shahi-privacy-shield' ); ?>
				</a>

				<a href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-privacy-shield-settings' ) ); ?>" class="action-button">
					<span class="dashicons dashicons-admin-settings"></span>
					<?php esc_html_e( 'Configure Settings', 'shahi-privacy-shield' ); ?>
				</a>

				<a href="<?php echo esc_url( admin_url( 'tools.php?page=export_personal_data' ) ); ?>" class="action-button">
					<span class="dashicons dashicons-download"></span>
					<?php esc_html_e( 'Export User Data', 'shahi-privacy-shield' ); ?>
				</a>

				<a href="<?php echo esc_url( admin_url( 'tools.php?page=remove_personal_data' ) ); ?>" class="action-button">
					<span class="dashicons dashicons-trash"></span>
					<?php esc_html_e( 'Erase User Data', 'shahi-privacy-shield' ); ?>
				</a>
			</div>
		</div>

		<!-- Compliance Checklist -->
		<div class="shahi-privacy-shield-card">
			<h2><?php esc_html_e( 'Compliance Checklist', 'shahi-privacy-shield' ); ?></h2>
			
			<?php
			$privacy_page_id = get_option( 'wp_page_for_privacy_policy' );
			$consent_enabled = get_option( 'shahi_privacy_shield_consent_banner_enabled' );
			$gdpr_enabled    = get_option( 'shahi_privacy_shield_gdpr_enabled' );
			$ccpa_enabled    = get_option( 'shahi_privacy_shield_ccpa_enabled' );
			?>

			<ul class="checklist">
				<li class="<?php echo $privacy_page_id ? 'complete' : 'incomplete'; ?>">
					<span class="dashicons dashicons-<?php echo $privacy_page_id ? 'yes' : 'no'; ?>"></span>
					<?php esc_html_e( 'Privacy Policy Page Set', 'shahi-privacy-shield' ); ?>
				</li>

				<li class="<?php echo $consent_enabled ? 'complete' : 'incomplete'; ?>">
					<span class="dashicons dashicons-<?php echo $consent_enabled ? 'yes' : 'no'; ?>"></span>
					<?php esc_html_e( 'Consent Banner Enabled', 'shahi-privacy-shield' ); ?>
				</li>

				<li class="<?php echo $gdpr_enabled ? 'complete' : 'incomplete'; ?>">
					<span class="dashicons dashicons-<?php echo $gdpr_enabled ? 'yes' : 'no'; ?>"></span>
					<?php esc_html_e( 'GDPR Compliance Active', 'shahi-privacy-shield' ); ?>
				</li>

				<li class="<?php echo $ccpa_enabled ? 'complete' : 'incomplete'; ?>">
					<span class="dashicons dashicons-<?php echo $ccpa_enabled ? 'yes' : 'no'; ?>"></span>
					<?php esc_html_e( 'CCPA Compliance Active', 'shahi-privacy-shield' ); ?>
				</li>

				<li class="<?php echo $latest_scan && $latest_scan['issues_found'] === 0 ? 'complete' : 'incomplete'; ?>">
					<span class="dashicons dashicons-<?php echo $latest_scan && $latest_scan['issues_found'] === 0 ? 'yes' : 'no'; ?>"></span>
					<?php esc_html_e( 'Zero Compliance Issues', 'shahi-privacy-shield' ); ?>
				</li>
			</ul>
		</div>
	</div>
</div>

<style>
.shahi-privacy-shield-dashboard {
	max-width: 1400px;
}

.shahi-privacy-shield-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
	gap: 20px;
	margin-top: 20px;
}

.shahi-privacy-shield-card {
	background: #fff;
	padding: 20px;
	border: 1px solid #ccd0d4;
	box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.shahi-privacy-shield-card h2 {
	margin-top: 0;
	padding-bottom: 10px;
	border-bottom: 1px solid #eee;
	font-size: 18px;
}

.compliance-overview .status-item {
	display: flex;
	align-items: center;
	margin: 20px 0;
}

.status-icon {
	width: 50px;
	height: 50px;
	border-radius: 50%;
	margin-right: 15px;
}

.status-icon.success {
	background: #46b450;
}

.status-icon.warning {
	background: #ffb900;
}

.issues-summary {
	margin: 20px 0;
	padding: 15px;
	background: #fef7f1;
	border-left: 4px solid #ffb900;
}

.issues-summary h4 {
	margin-top: 0;
}

.issues-summary ul {
	margin: 10px 0;
}

.issues-summary li {
	margin: 5px 0;
}

.severity-critical {
	color: #dc3232;
}

.severity-high {
	color: #f56e28;
}

.consent-stats {
	display: flex;
	justify-content: space-around;
	margin: 20px 0;
}

.stat-item {
	text-align: center;
}

.stat-number {
	font-size: 36px;
	font-weight: bold;
	color: #0073aa;
}

.stat-label {
	color: #666;
	font-size: 12px;
	text-transform: uppercase;
}

.consent-breakdown {
	margin: 20px 0;
}

.consent-breakdown ul {
	margin: 10px 0;
}

.consent-breakdown li {
	padding: 5px 0;
	display: flex;
	justify-content: space-between;
}

.quick-actions {
	display: flex;
	flex-direction: column;
	gap: 10px;
	margin: 20px 0;
}

.action-button {
	display: flex;
	align-items: center;
	padding: 12px;
	background: #f7f7f7;
	border: 1px solid #ddd;
	text-decoration: none;
	color: #333;
	transition: background 0.2s;
}

.action-button:hover {
	background: #fff;
	border-color: #0073aa;
}

.action-button .dashicons {
	margin-right: 10px;
}

.checklist {
	list-style: none;
	margin: 20px 0;
	padding: 0;
}

.checklist li {
	padding: 10px 0;
	display: flex;
	align-items: center;
}

.checklist li.complete {
	color: #46b450;
}

.checklist li.incomplete {
	color: #dc3232;
}

.checklist .dashicons {
	margin-right: 10px;
}
</style>
