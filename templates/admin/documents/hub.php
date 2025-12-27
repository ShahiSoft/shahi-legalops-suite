<?php
/**
 * Document Hub Dashboard Template
 *
 * Main template for the Legal Document Hub dashboard.
 * Displays profile banner, document cards, and quick actions.
 *
 * @package     ShahiLegalopsSuite
 * @subpackage  Templates/Admin/Documents
 * @since       4.2.0
 *
 * @var array $data Template data from controller.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Extract data for convenience.
$cards      = $data['cards'] ?? array();
$categories = $data['categories'] ?? array();
$profile    = $data['profile'] ?? array();
$statistics = $data['statistics'] ?? array();
$outdated   = $data['outdated'] ?? array();
$nonce      = $data['nonce'] ?? '';
?>

<div class="wrap slos-hub-wrap">
	<div class="slos-hub-header">
		<h1 class="slos-hub-title">
			<span class="dashicons dashicons-media-document"></span>
			<?php esc_html_e( 'Legal Document Hub', 'shahi-legalops-suite' ); ?>
		</h1>
		<p class="slos-hub-subtitle">
			<?php esc_html_e( 'Generate and manage your legal documents from your company profile.', 'shahi-legalops-suite' ); ?>
		</p>
	</div>

	<?php
	// Profile completion banner.
	if ( isset( $profile['exists'] ) && $profile['exists'] ) {
		include SHAHI_LEGALOPS_SUITE_PLUGIN_DIR . 'templates/admin/documents/partials/profile-banner.php';
	} else {
		// No profile exists - show setup prompt.
		?>
		<div class="slos-hub-banner slos-hub-banner--setup">
			<div class="slos-hub-banner__icon">
				<span class="dashicons dashicons-info-outline"></span>
			</div>
			<div class="slos-hub-banner__content">
				<h3 class="slos-hub-banner__title">
					<?php esc_html_e( 'Get Started with Your Legal Documents', 'shahi-legalops-suite' ); ?>
				</h3>
				<p class="slos-hub-banner__text">
					<?php esc_html_e( 'Complete your company profile to auto-generate professional legal documents. It only takes about 15 minutes.', 'shahi-legalops-suite' ); ?>
				</p>
			</div>
			<div class="slos-hub-banner__actions">
				<a href="<?php echo esc_url( $data['profile_url'] ); ?>" class="slos-btn slos-btn--primary">
					<span class="dashicons dashicons-edit"></span>
					<?php esc_html_e( 'Start Profile Setup', 'shahi-legalops-suite' ); ?>
				</a>
			</div>
		</div>
		<?php
	}
	?>

	<?php if ( ! empty( $outdated ) ) : ?>
	<div class="slos-hub-alert slos-hub-alert--warning">
		<span class="dashicons dashicons-warning"></span>
		<span>
			<?php
			printf(
				/* translators: %d: number of outdated documents */
				esc_html( _n(
					'%d document is outdated. Your profile has changed since it was generated.',
					'%d documents are outdated. Your profile has changed since they were generated.',
					count( $outdated ),
					'shahi-legalops-suite'
				) ),
				count( $outdated )
			);
			?>
		</span>
		<button type="button" class="slos-btn slos-btn--sm slos-btn--warning slos-hub-regenerate-all" data-nonce="<?php echo esc_attr( $nonce ); ?>">
			<span class="dashicons dashicons-update"></span>
			<?php esc_html_e( 'Regenerate All', 'shahi-legalops-suite' ); ?>
		</button>
	</div>
	<?php endif; ?>

	<!-- Category Filters -->
	<div class="slos-hub-filters">
		<div class="slos-hub-filters__tabs">
			<?php foreach ( $categories as $key => $label ) : ?>
				<button type="button" 
						class="slos-hub-filter-btn <?php echo 'all' === $key ? 'slos-hub-filter-btn--active' : ''; ?>" 
						data-filter="<?php echo esc_attr( $key ); ?>">
					<?php echo esc_html( $label ); ?>
				</button>
			<?php endforeach; ?>
		</div>
		<div class="slos-hub-filters__actions">
			<button type="button" class="slos-btn slos-btn--secondary slos-btn--sm slos-hub-export-all" 
					title="<?php esc_attr_e( 'Export all documents', 'shahi-legalops-suite' ); ?>">
				<span class="dashicons dashicons-download"></span>
				<?php esc_html_e( 'Export All', 'shahi-legalops-suite' ); ?>
			</button>
		</div>
	</div>

	<!-- Document Cards Grid -->
	<div class="slos-hub-grid">
		<?php if ( ! empty( $cards ) ) : ?>
			<?php foreach ( $cards as $card ) : ?>
				<?php
				// Pass card data to partial.
				include SHAHI_LEGALOPS_SUITE_PLUGIN_DIR . 'templates/admin/documents/partials/document-card.php';
				?>
			<?php endforeach; ?>
		<?php else : ?>
			<div class="slos-hub-empty">
				<span class="dashicons dashicons-media-document"></span>
				<p><?php esc_html_e( 'No document types configured.', 'shahi-legalops-suite' ); ?></p>
			</div>
		<?php endif; ?>
	</div>

	<!-- Quick Stats Panel -->
	<div class="slos-hub-stats">
		<div class="slos-hub-stats__item">
			<span class="slos-hub-stats__value"><?php echo esc_html( $statistics['total_generated'] ?? 0 ); ?></span>
			<span class="slos-hub-stats__label"><?php esc_html_e( 'Documents Generated', 'shahi-legalops-suite' ); ?></span>
		</div>
		<div class="slos-hub-stats__item">
			<span class="slos-hub-stats__value"><?php echo esc_html( $profile['completeness'] ?? 0 ); ?>%</span>
			<span class="slos-hub-stats__label"><?php esc_html_e( 'Profile Complete', 'shahi-legalops-suite' ); ?></span>
		</div>
		<div class="slos-hub-stats__item">
			<span class="slos-hub-stats__value <?php echo ( $statistics['needs_attention'] ?? 0 ) > 0 ? 'slos-hub-stats__value--warning' : ''; ?>">
				<?php echo esc_html( $statistics['needs_attention'] ?? 0 ); ?>
			</span>
			<span class="slos-hub-stats__label"><?php esc_html_e( 'Needs Attention', 'shahi-legalops-suite' ); ?></span>
		</div>
	</div>

	<!-- Shortcode Reference -->
	<div class="slos-hub-shortcodes">
		<h3 class="slos-hub-shortcodes__title">
			<span class="dashicons dashicons-shortcode"></span>
			<?php esc_html_e( 'Display Documents on Your Site', 'shahi-legalops-suite' ); ?>
		</h3>
		<p class="slos-hub-shortcodes__desc">
			<?php esc_html_e( 'Use these shortcodes to display your legal documents on any page or post:', 'shahi-legalops-suite' ); ?>
		</p>
		<div class="slos-hub-shortcodes__list">
			<div class="slos-hub-shortcode">
				<code class="slos-hub-shortcode__code">[slos_legal_doc type="privacy_policy"]</code>
				<button type="button" class="slos-btn slos-btn--icon slos-hub-copy-shortcode" 
						data-shortcode='[slos_legal_doc type="privacy_policy"]'
						title="<?php esc_attr_e( 'Copy shortcode', 'shahi-legalops-suite' ); ?>">
					<span class="dashicons dashicons-clipboard"></span>
				</button>
			</div>
			<div class="slos-hub-shortcode">
				<code class="slos-hub-shortcode__code">[slos_legal_doc type="terms_conditions"]</code>
				<button type="button" class="slos-btn slos-btn--icon slos-hub-copy-shortcode" 
						data-shortcode='[slos_legal_doc type="terms_conditions"]'
						title="<?php esc_attr_e( 'Copy shortcode', 'shahi-legalops-suite' ); ?>">
					<span class="dashicons dashicons-clipboard"></span>
				</button>
			</div>
			<div class="slos-hub-shortcode">
				<code class="slos-hub-shortcode__code">[slos_legal_doc type="cookie_policy"]</code>
				<button type="button" class="slos-btn slos-btn--icon slos-hub-copy-shortcode" 
						data-shortcode='[slos_legal_doc type="cookie_policy"]'
						title="<?php esc_attr_e( 'Copy shortcode', 'shahi-legalops-suite' ); ?>">
					<span class="dashicons dashicons-clipboard"></span>
				</button>
			</div>
		</div>
	</div>
</div>

<!-- Review & Generate Modal -->
<div id="slos-hub-modal" class="slos-modal" role="dialog" aria-modal="true" aria-labelledby="slos-hub-modal-title">
	<div class="slos-modal__backdrop"></div>
	<div class="slos-modal__container">
		<div class="slos-modal__header">
			<h2 id="slos-hub-modal-title" class="slos-modal__title"><?php esc_html_e( 'Generate Document', 'shahi-legalops-suite' ); ?></h2>
			<button type="button" class="slos-modal__close" aria-label="<?php esc_attr_e( 'Close', 'shahi-legalops-suite' ); ?>">
				<span class="dashicons dashicons-no-alt"></span>
			</button>
		</div>
		<div class="slos-modal__body">
			<div class="slos-modal__loading">
				<span class="slos-spinner"></span>
				<span><?php esc_html_e( 'Loading...', 'shahi-legalops-suite' ); ?></span>
			</div>
		</div>
		<div class="slos-modal__footer">
			<button type="button" class="slos-btn slos-btn--secondary slos-modal__cancel">
				<?php esc_html_e( 'Cancel', 'shahi-legalops-suite' ); ?>
			</button>
			<button type="button" class="slos-btn slos-btn--primary slos-hub-confirm-generate" disabled>
				<span class="dashicons dashicons-yes"></span>
				<?php esc_html_e( 'Generate Document', 'shahi-legalops-suite' ); ?>
			</button>
		</div>
	</div>
</div>

<!-- View Document Modal -->
<div id="slos-hub-view-modal" class="slos-modal slos-modal--large" role="dialog" aria-modal="true" aria-labelledby="slos-hub-view-title">
	<div class="slos-modal__backdrop"></div>
	<div class="slos-modal__container">
		<div class="slos-modal__header">
			<h2 id="slos-hub-view-title" class="slos-modal__title"><?php esc_html_e( 'Document Preview', 'shahi-legalops-suite' ); ?></h2>
			<button type="button" class="slos-modal__close" aria-label="<?php esc_attr_e( 'Close', 'shahi-legalops-suite' ); ?>">
				<span class="dashicons dashicons-no-alt"></span>
			</button>
		</div>
		<div class="slos-modal__body">
			<div class="slos-hub-preview-content"></div>
		</div>
		<div class="slos-modal__footer">
			<button type="button" class="slos-btn slos-btn--secondary slos-modal__cancel">
				<?php esc_html_e( 'Close', 'shahi-legalops-suite' ); ?>
			</button>
			<a href="#" class="slos-btn slos-btn--primary slos-hub-edit-doc" target="_blank">
				<span class="dashicons dashicons-edit"></span>
				<?php esc_html_e( 'Edit Document', 'shahi-legalops-suite' ); ?>
			</a>
		</div>
	</div>
</div>

<!-- Version History Modal -->
<div id="slos-hub-history-modal" class="slos-modal" role="dialog" aria-modal="true" aria-labelledby="slos-hub-history-title">
	<div class="slos-modal__backdrop"></div>
	<div class="slos-modal__container">
		<div class="slos-modal__header">
			<h2 id="slos-hub-history-title" class="slos-modal__title"><?php esc_html_e( 'Version History', 'shahi-legalops-suite' ); ?></h2>
			<button type="button" class="slos-modal__close" aria-label="<?php esc_attr_e( 'Close', 'shahi-legalops-suite' ); ?>">
				<span class="dashicons dashicons-no-alt"></span>
			</button>
		</div>
		<div class="slos-modal__body">
			<div class="slos-hub-history-list"></div>
		</div>
		<div class="slos-modal__footer">
			<button type="button" class="slos-btn slos-btn--secondary slos-modal__cancel">
				<?php esc_html_e( 'Close', 'shahi-legalops-suite' ); ?>
			</button>
		</div>
	</div>
</div>

<input type="hidden" id="slos-hub-nonce" value="<?php echo esc_attr( $nonce ); ?>" />
