<?php
/**
 * Banner Settings Page
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Templates/Admin
 * @since      3.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap shahi-legalops-suite-admin shahi-settings-page">
	<div class="shahi-page-header">
		<div class="shahi-header-content">
			<h1 class="shahi-page-title">
				<span class="dashicons dashicons-format-image"></span>
				<?php echo esc_html__( 'Banner Settings', 'shahi-legalops-suite' ); ?>
			</h1>
			<p class="shahi-page-description">
				<?php echo esc_html__( 'Customize consent banner appearance, behavior, and copy.', 'shahi-legalops-suite' ); ?>
			</p>
		</div>
		<div class="shahi-header-actions">
			<a class="shahi-btn shahi-btn-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-legalops-suite-consent' ) ); ?>">
				<span class="dashicons dashicons-undo"></span>
				<?php echo esc_html__( 'Back to Consent & Compliance', 'shahi-legalops-suite' ); ?>
			</a>
		</div>
	</div>

	<form method="post" action="">
		<?php wp_nonce_field( 'shahi_save_settings', 'shahi_settings_nonce' ); ?>

		<div class="shahi-card">
			<div class="shahi-card-header">
				<h2 class="shahi-card-title"><?php echo esc_html__( 'Layout & Theme', 'shahi-legalops-suite' ); ?></h2>
				<p class="shahi-card-description"><?php echo esc_html__( 'Choose template, position, theme, and motion for the banner.', 'shahi-legalops-suite' ); ?></p>
			</div>
			<div class="shahi-card-body">
				<div class="shahi-settings-group">

					<div class="shahi-setting-row">
						<label for="consent_banner_template" class="shahi-setting-label"><?php echo esc_html__( 'Banner Template', 'shahi-legalops-suite' ); ?></label>
						<div class="shahi-setting-control">
							<select id="consent_banner_template" name="consent_banner_template" class="shahi-select">
								<option value="eu" <?php selected( $settings['consent_banner_template'], 'eu' ); ?>><?php echo esc_html__( 'EU (GDPR)', 'shahi-legalops-suite' ); ?></option>
								<option value="ccpa" <?php selected( $settings['consent_banner_template'], 'ccpa' ); ?>><?php echo esc_html__( 'CCPA', 'shahi-legalops-suite' ); ?></option>
								<option value="simple" <?php selected( $settings['consent_banner_template'], 'simple' ); ?>><?php echo esc_html__( 'Simple', 'shahi-legalops-suite' ); ?></option>
								<option value="advanced" <?php selected( $settings['consent_banner_template'], 'advanced' ); ?>><?php echo esc_html__( 'Advanced', 'shahi-legalops-suite' ); ?></option>
							</select>
						</div>
					</div>

					<div class="shahi-setting-row">
						<label for="consent_banner_position" class="shahi-setting-label"><?php echo esc_html__( 'Banner Position', 'shahi-legalops-suite' ); ?></label>
						<div class="shahi-setting-control">
							<select id="consent_banner_position" name="consent_banner_position" class="shahi-select">
								<option value="bottom" <?php selected( $settings['consent_banner_position'], 'bottom' ); ?>><?php echo esc_html__( 'Bottom', 'shahi-legalops-suite' ); ?></option>
								<option value="top" <?php selected( $settings['consent_banner_position'], 'top' ); ?>><?php echo esc_html__( 'Top', 'shahi-legalops-suite' ); ?></option>
							</select>
						</div>
					</div>

					<div class="shahi-setting-row">
						<label for="consent_banner_theme" class="shahi-setting-label"><?php echo esc_html__( 'Banner Theme', 'shahi-legalops-suite' ); ?></label>
						<div class="shahi-setting-control">
							<select id="consent_banner_theme" name="consent_banner_theme" class="shahi-select">
								<option value="light" <?php selected( $settings['consent_banner_theme'], 'light' ); ?>><?php echo esc_html__( 'Light', 'shahi-legalops-suite' ); ?></option>
								<option value="dark" <?php selected( $settings['consent_banner_theme'], 'dark' ); ?>><?php echo esc_html__( 'Dark', 'shahi-legalops-suite' ); ?></option>
								<option value="auto" <?php selected( $settings['consent_banner_theme'], 'auto' ); ?>><?php echo esc_html__( 'Auto (match system)', 'shahi-legalops-suite' ); ?></option>
							</select>
						</div>
					</div>

					<div class="shahi-setting-row">
						<label class="shahi-setting-label"><?php echo esc_html__( 'Banner Colors', 'shahi-legalops-suite' ); ?></label>
						<div class="shahi-setting-control">
							<div class="shahi-inline-group">
								<div>
									<span class="shahi-setting-subtitle"><?php echo esc_html__( 'Primary', 'shahi-legalops-suite' ); ?></span>
									<input type="color" name="consent_banner_primary_color" value="<?php echo esc_attr( $settings['consent_banner_primary_color'] ); ?>" class="shahi-input-color">
								</div>
								<div>
									<span class="shahi-setting-subtitle"><?php echo esc_html__( 'Accept Button', 'shahi-legalops-suite' ); ?></span>
									<input type="color" name="consent_banner_accept_color" value="<?php echo esc_attr( $settings['consent_banner_accept_color'] ); ?>" class="shahi-input-color">
								</div>
								<div>
									<span class="shahi-setting-subtitle"><?php echo esc_html__( 'Reject Button', 'shahi-legalops-suite' ); ?></span>
									<input type="color" name="consent_banner_reject_color" value="<?php echo esc_attr( $settings['consent_banner_reject_color'] ); ?>" class="shahi-input-color">
								</div>
							</div>
						</div>
					</div>

					<div class="shahi-setting-row">
						<label class="shahi-setting-label"><?php echo esc_html__( 'Buttons & Motion', 'shahi-legalops-suite' ); ?></label>
						<div class="shahi-setting-control">
							<label class="shahi-checkbox-label">
								<input type="checkbox" name="show_accept_selected" value="1" <?php checked( $settings['show_accept_selected'] ); ?>>
								<span><?php echo esc_html__( 'Show “Accept Selected” button', 'shahi-legalops-suite' ); ?></span>
							</label>
							<label class="shahi-checkbox-label">
								<input type="checkbox" name="enable_reduced_motion" value="1" <?php checked( $settings['enable_reduced_motion'] ); ?>>
								<span><?php echo esc_html__( 'Enable reduced motion (accessibility)', 'shahi-legalops-suite' ); ?></span>
							</label>
						</div>
					</div>

				</div>
			</div>
		</div>

		<div class="shahi-card">
			<div class="shahi-card-header">
				<h2 class="shahi-card-title"><?php echo esc_html__( 'Content & Links', 'shahi-legalops-suite' ); ?></h2>
				<p class="shahi-card-description"><?php echo esc_html__( 'Update banner copy and outbound links.', 'shahi-legalops-suite' ); ?></p>
			</div>
			<div class="shahi-card-body">
				<div class="shahi-settings-group">

					<div class="shahi-setting-row">
						<label for="consent_banner_heading" class="shahi-setting-label"><?php echo esc_html__( 'Banner Heading', 'shahi-legalops-suite' ); ?></label>
						<div class="shahi-setting-control">
							<input type="text" id="consent_banner_heading" name="consent_banner_heading" value="<?php echo esc_attr( $settings['consent_banner_heading'] ); ?>" class="shahi-input">
						</div>
					</div>

					<div class="shahi-setting-row">
						<label for="consent_banner_message" class="shahi-setting-label"><?php echo esc_html__( 'Banner Message', 'shahi-legalops-suite' ); ?></label>
						<div class="shahi-setting-control">
							<textarea id="consent_banner_message" name="consent_banner_message" rows="4" class="shahi-textarea"><?php echo esc_textarea( $settings['consent_banner_message'] ); ?></textarea>
						</div>
					</div>

					<div class="shahi-setting-row">
						<label class="shahi-setting-label"><?php echo esc_html__( 'Button Labels', 'shahi-legalops-suite' ); ?></label>
						<div class="shahi-setting-control">
							<div class="shahi-inline-group">
								<input type="text" name="consent_accept_button_text" value="<?php echo esc_attr( $settings['consent_accept_button_text'] ); ?>" class="shahi-input shahi-input-sm" placeholder="<?php echo esc_attr__( 'Accept All', 'shahi-legalops-suite' ); ?>">
								<input type="text" name="consent_reject_button_text" value="<?php echo esc_attr( $settings['consent_reject_button_text'] ); ?>" class="shahi-input shahi-input-sm" placeholder="<?php echo esc_attr__( 'Reject All', 'shahi-legalops-suite' ); ?>">
								<input type="text" name="consent_settings_button_text" value="<?php echo esc_attr( $settings['consent_settings_button_text'] ); ?>" class="shahi-input shahi-input-sm" placeholder="<?php echo esc_attr__( 'Cookie Settings', 'shahi-legalops-suite' ); ?>">
								<input type="text" name="consent_privacy_policy_text" value="<?php echo esc_attr( $settings['consent_privacy_policy_text'] ); ?>" class="shahi-input shahi-input-sm" placeholder="<?php echo esc_attr__( 'Privacy Policy', 'shahi-legalops-suite' ); ?>">
							</div>
						</div>
					</div>

					<div class="shahi-setting-row">
						<label class="shahi-setting-label"><?php echo esc_html__( 'Link URLs', 'shahi-legalops-suite' ); ?></label>
						<div class="shahi-setting-control">
							<div class="shahi-inline-group">
								<input type="url" name="privacy_policy_url" value="<?php echo esc_attr( $settings['privacy_policy_url'] ); ?>" class="shahi-input" placeholder="<?php echo esc_attr__( 'https://example.com/privacy-policy', 'shahi-legalops-suite' ); ?>">
								<input type="url" name="learn_more_url" value="<?php echo esc_attr( $settings['learn_more_url'] ); ?>" class="shahi-input" placeholder="<?php echo esc_attr__( 'https://example.com/learn-more', 'shahi-legalops-suite' ); ?>">
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>

		<div class="shahi-form-footer">
			<button type="submit" name="shahi_save_settings" class="shahi-btn shahi-btn-primary shahi-btn-lg">
				<span class="dashicons dashicons-saved"></span>
				<?php echo esc_html__( 'Save Banner Settings', 'shahi-legalops-suite' ); ?>
			</button>
		</div>
	</form>
</div>
