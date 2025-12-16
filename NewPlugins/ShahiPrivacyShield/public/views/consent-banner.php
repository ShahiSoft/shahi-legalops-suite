<?php
/**
 * Consent Banner Frontend View
 *
 * @package ShahiPrivacyShield
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$consent_manager = new \ShahiPrivacyShield\Modules\ConsentManager();
$consent_types   = $consent_manager->get_consent_types();
?>

<div id="shahi-privacy-shield-consent-banner" class="shahi-consent-banner" style="display: none;">
	<div class="shahi-consent-overlay"></div>
	<div class="shahi-consent-modal">
		<div class="shahi-consent-header">
			<h3><?php esc_html_e( 'We Value Your Privacy', 'shahi-privacy-shield' ); ?></h3>
			<button type="button" class="shahi-consent-close" aria-label="<?php esc_attr_e( 'Close', 'shahi-privacy-shield' ); ?>">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>

		<div class="shahi-consent-body">
			<p>
				<?php
				printf(
					/* translators: %s: privacy policy link */
					esc_html__( 'We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies. Read our %s for more information.', 'shahi-privacy-shield' ),
					'<a href="' . esc_url( get_privacy_policy_url() ) . '" target="_blank">' . esc_html__( 'Privacy Policy', 'shahi-privacy-shield' ) . '</a>'
				);
				?>
			</p>

			<div class="shahi-consent-options">
				<?php foreach ( $consent_types as $type => $label ) : ?>
					<div class="shahi-consent-option">
						<label>
							<input 
								type="checkbox" 
								name="consent_<?php echo esc_attr( $type ); ?>" 
								value="1"
								<?php checked( $type === 'necessary' ); ?>
								<?php disabled( $type === 'necessary' ); ?>
							/>
							<span class="consent-label"><?php echo esc_html( $label ); ?></span>
							<?php if ( $type === 'necessary' ) : ?>
								<span class="consent-required"><?php esc_html_e( '(Required)', 'shahi-privacy-shield' ); ?></span>
							<?php endif; ?>
						</label>
						<div class="consent-description">
							<?php
							switch ( $type ) {
								case 'necessary':
									esc_html_e( 'Essential cookies for site functionality and security.', 'shahi-privacy-shield' );
									break;
								case 'analytics':
									esc_html_e( 'Help us understand how visitors interact with our website.', 'shahi-privacy-shield' );
									break;
								case 'marketing':
									esc_html_e( 'Used to deliver personalized advertisements.', 'shahi-privacy-shield' );
									break;
								case 'preferences':
									esc_html_e( 'Remember your settings and preferences.', 'shahi-privacy-shield' );
									break;
							}
							?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="shahi-consent-footer">
			<button type="button" class="shahi-consent-btn shahi-consent-btn-primary" data-action="accept-all">
				<?php esc_html_e( 'Accept All', 'shahi-privacy-shield' ); ?>
			</button>
			<button type="button" class="shahi-consent-btn shahi-consent-btn-secondary" data-action="accept-selected">
				<?php esc_html_e( 'Accept Selected', 'shahi-privacy-shield' ); ?>
			</button>
			<button type="button" class="shahi-consent-btn shahi-consent-btn-tertiary" data-action="reject-all">
				<?php esc_html_e( 'Reject All', 'shahi-privacy-shield' ); ?>
			</button>
		</div>

		<div class="shahi-consent-links">
			<?php if ( get_privacy_policy_url() ) : ?>
				<a href="<?php echo esc_url( get_privacy_policy_url() ); ?>" target="_blank">
					<?php esc_html_e( 'Privacy Policy', 'shahi-privacy-shield' ); ?>
				</a>
			<?php endif; ?>
			<a href="#" class="shahi-consent-manage">
				<?php esc_html_e( 'Manage Preferences', 'shahi-privacy-shield' ); ?>
			</a>
		</div>
	</div>
</div>

<script>
// Show banner after page load
document.addEventListener('DOMContentLoaded', function() {
	setTimeout(function() {
		var banner = document.getElementById('shahi-privacy-shield-consent-banner');
		if (banner) {
			banner.style.display = 'block';
		}
	}, 1000);
});
</script>
