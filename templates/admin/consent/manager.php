<?php
/**
 * Consent & Compliance Admin Template
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Templates/Admin
 * @since      3.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$total_consents  = array_sum( $stats['by_status'] );
$accepted_total  = $stats['by_status']['accepted'] ?? 0;
$rejected_total  = $stats['by_status']['rejected'] ?? 0;
$withdrawn_total = $stats['by_status']['withdrawn'] ?? 0;
?>

<div class="wrap shahi-legalops-suite-admin shahi-consent-page">
	<div class="shahi-page-header">
		<div class="shahi-header-content">
			<h1 class="shahi-page-title">
				<span class="dashicons dashicons-lock"></span>
				<?php echo esc_html__( 'Consent & Compliance', 'shahi-legalops-suite' ); ?>
			</h1>
			<p class="shahi-page-description">
				<?php echo esc_html__( 'Monitor consent signals, review audit trails, and keep your compliance posture visible.', 'shahi-legalops-suite' ); ?>
			</p>
		</div>
		<div class="shahi-header-actions">
			<button type="button" class="shahi-btn shahi-btn-primary" id="slos-consent-refresh">
				<span class="dashicons dashicons-update"></span>
				<?php echo esc_html__( 'Refresh Data', 'shahi-legalops-suite' ); ?>
			</button>
			<a class="shahi-btn shahi-btn-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-legalops-suite-settings&tab=security' ) ); ?>">
				<span class="dashicons dashicons-shield"></span>
				<?php echo esc_html__( 'Security Settings', 'shahi-legalops-suite' ); ?>
			</a>
		</div>
	</div>

	<div class="slos-consent-grid">
		<div class="slos-consent-main">
			<div class="slos-consent-filters">
				<div class="filter-group">
					<label for="slos-consent-period"><?php echo esc_html__( 'Period', 'shahi-legalops-suite' ); ?></label>
					<select id="slos-consent-period">
						<?php foreach ( $filters['periods'] as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="filter-group">
					<label for="slos-consent-type"><?php echo esc_html__( 'Type', 'shahi-legalops-suite' ); ?></label>
					<select id="slos-consent-type">
						<option value=""><?php echo esc_html__( 'All types', 'shahi-legalops-suite' ); ?></option>
						<?php foreach ( $filters['types'] as $type ) : ?>
							<option value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( ucfirst( $type ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="filter-group">
					<label for="slos-consent-status"><?php echo esc_html__( 'Status', 'shahi-legalops-suite' ); ?></label>
					<select id="slos-consent-status">
						<option value=""><?php echo esc_html__( 'All statuses', 'shahi-legalops-suite' ); ?></option>
						<?php foreach ( $filters['statuses'] as $status ) : ?>
							<option value="<?php echo esc_attr( $status ); ?>"><?php echo esc_html( ucfirst( $status ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="filter-group search">
					<label for="slos-consent-search"><?php echo esc_html__( 'Search', 'shahi-legalops-suite' ); ?></label>
					<div class="search-box">
						<span class="dashicons dashicons-search"></span>
						<input type="search" id="slos-consent-search" placeholder="<?php echo esc_attr__( 'Search by user, type, or source', 'shahi-legalops-suite' ); ?>" />
					</div>
				</div>
			</div>

			<div class="slos-consent-stats">
				<div class="stat-card accent">
					<div class="stat-label"><?php echo esc_html__( 'Total Consents', 'shahi-legalops-suite' ); ?></div>
					<div class="stat-value" data-key="total"><?php echo esc_html( $total_consents ); ?></div>
					<div class="stat-meta">
						<span class="dot live"></span>
						<?php echo esc_html__( 'Live snapshot', 'shahi-legalops-suite' ); ?>
					</div>
				</div>
				<div class="stat-card positive">
					<div class="stat-label"><?php echo esc_html__( 'Accepted', 'shahi-legalops-suite' ); ?></div>
					<div class="stat-value" data-key="accepted"><?php echo esc_html( $accepted_total ); ?></div>
					<div class="stat-meta">
						<?php echo esc_html__( 'Validated consents', 'shahi-legalops-suite' ); ?>
					</div>
				</div>
				<div class="stat-card neutral">
					<div class="stat-label"><?php echo esc_html__( 'Rejected', 'shahi-legalops-suite' ); ?></div>
					<div class="stat-value" data-key="rejected"><?php echo esc_html( $rejected_total ); ?></div>
					<div class="stat-meta">
						<?php echo esc_html__( 'Opt-outs tracked', 'shahi-legalops-suite' ); ?>
					</div>
				</div>
				<div class="stat-card warning">
					<div class="stat-label"><?php echo esc_html__( 'Withdrawn', 'shahi-legalops-suite' ); ?></div>
					<div class="stat-value" data-key="withdrawn"><?php echo esc_html( $withdrawn_total ); ?></div>
					<div class="stat-meta">
						<?php echo esc_html__( 'Requires follow-up', 'shahi-legalops-suite' ); ?>
					</div>
				</div>
			</div>

			<div class="slos-consent-panels">
				<div class="slos-panel">
					<div class="slos-panel-header">
						<div>
							<p class="subtitle"><?php echo esc_html__( 'Density by type', 'shahi-legalops-suite' ); ?></p>
							<h2><?php echo esc_html__( 'Acceptance mix', 'shahi-legalops-suite' ); ?></h2>
						</div>
						<button class="shahi-btn ghost" id="slos-consent-export">
							<span class="dashicons dashicons-download"></span>
							<?php echo esc_html__( 'Export CSV', 'shahi-legalops-suite' ); ?>
						</button>
					</div>
					<div class="slos-panel-body">
						<div class="slos-consent-chart" id="slos-consent-chart-types" aria-label="<?php echo esc_attr__( 'Consent volume by type', 'shahi-legalops-suite' ); ?>"></div>
						<div class="slos-consent-legend" id="slos-consent-legend-types"></div>
					</div>
				</div>

				<div class="slos-panel">
					<div class="slos-panel-header">
						<div>
							<p class="subtitle"><?php echo esc_html__( 'Audit trail', 'shahi-legalops-suite' ); ?></p>
							<h2><?php echo esc_html__( 'Recent signals', 'shahi-legalops-suite' ); ?></h2>
						</div>
						<div class="mini-filters">
							<button class="chip active" data-limit="10">10</button>
							<button class="chip" data-limit="25">25</button>
							<button class="chip" data-limit="50">50</button>
						</div>
					</div>
					<div class="slos-panel-body">
						<div class="slos-table-wrapper">
							<table class="slos-table" id="slos-consent-table">
								<thead>
									<tr>
										<th><?php echo esc_html__( 'Consent ID', 'shahi-legalops-suite' ); ?></th>
										<th><?php echo esc_html__( 'User', 'shahi-legalops-suite' ); ?></th>
										<th><?php echo esc_html__( 'Type', 'shahi-legalops-suite' ); ?></th>
										<th><?php echo esc_html__( 'Status', 'shahi-legalops-suite' ); ?></th>
										<th><?php echo esc_html__( 'Created', 'shahi-legalops-suite' ); ?></th>
										<th><?php echo esc_html__( 'Updated', 'shahi-legalops-suite' ); ?></th>
										<th><?php echo esc_html__( 'Actions', 'shahi-legalops-suite' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php if ( ! empty( $recent_consents ) ) : ?>
										<?php foreach ( $recent_consents as $consent ) : ?>
											<tr data-id="<?php echo esc_attr( $consent['id'] ); ?>">
												<td>#<?php echo esc_html( $consent['id'] ); ?></td>
												<td><?php echo esc_html( $consent['user_id'] ); ?></td>
												<td><span class="badge neutral"><?php echo esc_html( ucfirst( $consent['type'] ) ); ?></span></td>
												<td><span class="badge state-<?php echo esc_attr( $consent['status'] ); ?>"><?php echo esc_html( ucfirst( $consent['status'] ) ); ?></span></td>
												<td><?php echo esc_html( $consent['created_at'] ); ?></td>
												<td><?php echo esc_html( $consent['updated_at'] ); ?></td>
												<td>
													<button class="link" data-action="view" aria-label="<?php echo esc_attr__( 'View consent', 'shahi-legalops-suite' ); ?>">
														<?php echo esc_html__( 'View', 'shahi-legalops-suite' ); ?>
													</button>
												</td>
											</tr>
										<?php endforeach; ?>
									<?php else : ?>
										<tr class="empty">
											<td colspan="7">
												<div class="shahi-empty-state">
													<span class="dashicons dashicons-visibility"></span>
													<p><?php echo esc_html__( 'No consent activity yet. Data will appear as signals are recorded.', 'shahi-legalops-suite' ); ?></p>
												</div>
											</td>
										</tr>
									<?php endif; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="slos-consent-side">
			<div class="slos-panel">
				<div class="slos-panel-header">
					<p class="subtitle"><?php echo esc_html__( 'Policy posture', 'shahi-legalops-suite' ); ?></p>
					<h2><?php echo esc_html__( 'Compliance pulse', 'shahi-legalops-suite' ); ?></h2>
				</div>
				<div class="slos-panel-body">
					<ul class="slos-pill-list" id="slos-consent-insights">
						<li>
							<span class="dot positive"></span>
							<strong><?php echo esc_html__( 'Active signals', 'shahi-legalops-suite' ); ?></strong>
							<span class="value" data-key="accepted"><?php echo esc_html( $accepted_total ); ?></span>
						</li>
						<li>
							<span class="dot warning"></span>
							<strong><?php echo esc_html__( 'Withdrawn', 'shahi-legalops-suite' ); ?></strong>
							<span class="value" data-key="withdrawn"><?php echo esc_html( $withdrawn_total ); ?></span>
						</li>
						<li>
							<span class="dot neutral"></span>
							<strong><?php echo esc_html__( 'Rejected', 'shahi-legalops-suite' ); ?></strong>
							<span class="value" data-key="rejected"><?php echo esc_html( $rejected_total ); ?></span>
						</li>
						<li>
							<span class="dot accent"></span>
							<strong><?php echo esc_html__( 'Types covered', 'shahi-legalops-suite' ); ?></strong>
							<span class="value" data-key="types"><?php echo esc_html( count( $stats['by_type'] ) ); ?></span>
						</li>
					</ul>
					<div class="slos-note">
						<p><?php echo esc_html__( 'Use filters to isolate jurisdictions, channels, or consent types. All actions are captured in the audit log.', 'shahi-legalops-suite' ); ?></p>
					</div>
				</div>
			</div>

			<div class="slos-panel ghost">
				<div class="slos-panel-header">
					<p class="subtitle"><?php echo esc_html__( 'Shortcuts', 'shahi-legalops-suite' ); ?></p>
					<h2><?php echo esc_html__( 'Operational moves', 'shahi-legalops-suite' ); ?></h2>
				</div>
				<div class="slos-panel-body shortcut-grid">
					<a class="shortcut" href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-legalops-suite-settings&tab=privacy' ) ); ?>">
						<span class="dashicons dashicons-admin-settings"></span>
						<?php echo esc_html__( 'Privacy defaults', 'shahi-legalops-suite' ); ?>
					</a>
					<a class="shortcut" href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-legalops-suite-settings&tab=security' ) ); ?>">
						<span class="dashicons dashicons-shield"></span>
						<?php echo esc_html__( 'Hashing & IP policy', 'shahi-legalops-suite' ); ?>
					</a>
					<a class="shortcut" href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-legalops-suite-settings&tab=notifications' ) ); ?>">
						<span class="dashicons dashicons-email"></span>
						<?php echo esc_html__( 'Alerts & notices', 'shahi-legalops-suite' ); ?>
					</a>
					<a class="shortcut" href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-legalops-suite-settings&tab=import_export' ) ); ?>">
						<span class="dashicons dashicons-database-export"></span>
						<?php echo esc_html__( 'Export evidence', 'shahi-legalops-suite' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
