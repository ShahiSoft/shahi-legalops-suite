# TASK 3.0b: DSR Settings Page

**Phase:** 3 (DSR Portal)  
**Effort:** 6-8 hours  
**Prerequisites:** Task 3.0a (DSR Module), Task 3.0b-admin-controller  
**Next Task:** [task-3.1-dsr-repository.md](task-3.1-dsr-repository.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 3.0b for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
Create a comprehensive DSR settings page accessible from Module Dashboard,
allowing admins to configure SLA deadlines per regulation, toggle data sources,
manage notification preferences, and customize portal appearance.

References: /includes/Modules/DSR_Portal/DSR_Portal.php (get_settings_url method)

INPUT STATE (verify these exist):
✅ DSR_Portal module registered (Task 3.0a)
✅ Module Dashboard UI in place
✅ WordPress options API available
✅ Admin capabilities system ready

YOUR TASK:

1) **Create DSR Settings Page Handler**

File: `includes/Admin/DSR_Settings.php`

```php
<?php
/**
 * DSR Settings Page
 *
 * Admin interface for configuring DSR Portal behavior, SLA rules, notifications, and data sources.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      3.0.1
 */

namespace ShahiLegalopsSuite\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DSR_Settings Class
 *
 * @since 3.0.1
 */
class DSR_Settings {

	/**
	 * Option key for DSR settings
	 *
	 * @var string
	 */
	private const OPTION_KEY = 'slos_dsr_settings';

	/**
	 * Capability required
	 *
	 * @var string
	 */
	private const CAPABILITY = 'manage_options';

	/**
	 * Constructor
	 *
	 * @since 3.0.1
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register settings page
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function register_page(): void {
		add_submenu_page(
			'shahi-legalops-suite',
			__( 'DSR Settings', 'shahi-legalops-suite' ),
			__( 'DSR Settings', 'shahi-legalops-suite' ),
			self::CAPABILITY,
			'slos-dsr-settings',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register settings and sections
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function register_settings(): void {
		// Register option
		register_setting(
			'slos_dsr_settings_group',
			self::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'show_in_rest'      => false,
			)
		);

		// SLA Settings Section
		add_settings_section(
			'slos_dsr_sla',
			__( 'SLA Configuration (Business Days)', 'shahi-legalops-suite' ),
			array( $this, 'render_sla_section' ),
			'slos_dsr_settings'
		);

		// SLA fields per regulation
		$regulations = array(
			'GDPR'     => __( 'GDPR', 'shahi-legalops-suite' ),
			'UK-GDPR'  => __( 'UK-GDPR', 'shahi-legalops-suite' ),
			'CCPA'     => __( 'CCPA', 'shahi-legalops-suite' ),
			'LGPD'     => __( 'LGPD (Brazil)', 'shahi-legalops-suite' ),
			'PIPEDA'   => __( 'PIPEDA (Canada)', 'shahi-legalops-suite' ),
			'POPIA'    => __( 'POPIA (South Africa)', 'shahi-legalops-suite' ),
		);

		foreach ( $regulations as $code => $label ) {
			add_settings_field(
				'slos_dsr_sla_' . strtolower( $code ),
				$label,
				array( $this, 'render_sla_field' ),
				'slos_dsr_settings',
				'slos_dsr_sla',
				array( 'regulation' => $code )
			);
		}

		// Data Sources Section
		add_settings_section(
			'slos_dsr_sources',
			__( 'Data Sources to Search', 'shahi-legalops-suite' ),
			array( $this, 'render_sources_section' ),
			'slos_dsr_settings'
		);

		$sources = array(
			'posts'  => __( 'Posts & Pages', 'shahi-legalops-suite' ),
			'users'  => __( 'User Accounts', 'shahi-legalops-suite' ),
			'comments' => __( 'Comments', 'shahi-legalops-suite' ),
			'forms'  => __( 'Form Submissions', 'shahi-legalops-suite' ),
			'logs'   => __( 'Activity Logs', 'shahi-legalops-suite' ),
		);

		foreach ( $sources as $key => $label ) {
			add_settings_field(
				'slos_dsr_source_' . $key,
				$label,
				array( $this, 'render_source_checkbox' ),
				'slos_dsr_settings',
				'slos_dsr_sources',
				array( 'source' => $key )
			);
		}

		// Notifications Section
		add_settings_section(
			'slos_dsr_notifications',
			__( 'Notifications & Emails', 'shahi-legalops-suite' ),
			array( $this, 'render_notifications_section' ),
			'slos_dsr_settings'
		);

		add_settings_field(
			'slos_dsr_notify_requester',
			__( 'Email Requester on Status Changes', 'shahi-legalops-suite' ),
			array( $this, 'render_checkbox' ),
			'slos_dsr_settings',
			'slos_dsr_notifications',
			array( 'key' => 'notify_requester' )
		);

		add_settings_field(
			'slos_dsr_notify_admin',
			__( 'Email Admin on New Requests', 'shahi-legalops-suite' ),
			array( $this, 'render_checkbox' ),
			'slos_dsr_settings',
			'slos_dsr_notifications',
			array( 'key' => 'notify_admin' )
		);

		add_settings_field(
			'slos_dsr_notify_overdue',
			__( 'Alert on SLA Breach (Admin)', 'shahi-legalops-suite' ),
			array( $this, 'render_checkbox' ),
			'slos_dsr_settings',
			'slos_dsr_notifications',
			array( 'key' => 'notify_overdue' )
		);

		// Portal Appearance Section
		add_settings_section(
			'slos_dsr_appearance',
			__( 'Portal Appearance', 'shahi-legalops-suite' ),
			array( $this, 'render_appearance_section' ),
			'slos_dsr_settings'
		);

		add_settings_field(
			'slos_dsr_form_title',
			__( 'Form Page Title', 'shahi-legalops-suite' ),
			array( $this, 'render_text_field' ),
			'slos_dsr_settings',
			'slos_dsr_appearance',
			array(
				'key'         => 'form_title',
				'default'     => __( 'Submit a Data Subject Request', 'shahi-legalops-suite' ),
				'placeholder' => __( 'Submit a Data Subject Request', 'shahi-legalops-suite' ),
			)
		);

		add_settings_field(
			'slos_dsr_form_description',
			__( 'Form Description', 'shahi-legalops-suite' ),
			array( $this, 'render_textarea_field' ),
			'slos_dsr_settings',
			'slos_dsr_appearance',
			array(
				'key'         => 'form_description',
				'default'     => __( 'Exercise your data privacy rights. Submit a request and we\'ll respond within the applicable SLA.', 'shahi-legalops-suite' ),
				'placeholder' => __( 'Description shown above form', 'shahi-legalops-suite' ),
				'rows'        => 3,
			)
		);

		add_settings_field(
			'slos_dsr_privacy_policy_url',
			__( 'Privacy Policy URL (linked in form)', 'shahi-legalops-suite' ),
			array( $this, 'render_text_field' ),
			'slos_dsr_settings',
			'slos_dsr_appearance',
			array(
				'key'         => 'privacy_policy_url',
				'type'        => 'url',
				'default'     => home_url( '/privacy-policy/' ),
				'placeholder' => home_url( '/privacy-policy/' ),
			)
		);

		// Advanced Section
		add_settings_section(
			'slos_dsr_advanced',
			__( 'Advanced Options', 'shahi-legalops-suite' ),
			array( $this, 'render_advanced_section' ),
			'slos_dsr_settings'
		);

		add_settings_field(
			'slos_dsr_require_identity_verification',
			__( 'Require Identity Verification (Upload ID)', 'shahi-legalops-suite' ),
			array( $this, 'render_checkbox' ),
			'slos_dsr_settings',
			'slos_dsr_advanced',
			array( 'key' => 'require_identity_verification' )
		);

		add_settings_field(
			'slos_dsr_enable_encryption',
			__( 'Encrypt PII in Database', 'shahi-legalops-suite' ),
			array( $this, 'render_checkbox' ),
			'slos_dsr_settings',
			'slos_dsr_advanced',
			array( 'key' => 'enable_encryption' )
		);

		add_settings_field(
			'slos_dsr_auto_delete_days',
			__( 'Auto-delete Completed Requests After (days)', 'shahi-legalops-suite' ),
			array( $this, 'render_number_field' ),
			'slos_dsr_settings',
			'slos_dsr_advanced',
			array(
				'key'     => 'auto_delete_days',
				'default' => 365,
				'min'     => 0,
				'max'     => 3650,
			)
		);
	}

	/**
	 * Render settings page
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorized', 'shahi-legalops-suite' ) );
		}

		?>
		<div class="wrap slos-dsr-settings">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<form method="post" action="options.php">
				<?php
				settings_fields( 'slos_dsr_settings_group' );
				do_settings_sections( 'slos_dsr_settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render SLA section description
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function render_sla_section(): void {
		echo wp_kses_post( __( 'Set the Service Level Agreement (SLA) deadline in business days for each regulation. Defaults are regulatory minimums.', 'shahi-legalops-suite' ) );
	}

	/**
	 * Render SLA input field
	 *
	 * @since 3.0.1
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_sla_field( array $args ): void {
		$regulation = $args['regulation'] ?? '';
		$settings   = $this->get_settings();
		$defaults   = $this->get_sla_defaults();
		$value      = $settings['sla_' . strtolower( $regulation )] ?? $defaults[ $regulation ];

		?>
		<input 
			type="number" 
			name="<?php echo esc_attr( self::OPTION_KEY . '[sla_' . strtolower( $regulation ) . ']' ); ?>" 
			value="<?php echo esc_attr( $value ); ?>" 
			min="1" 
			max="365"
			class="small-text"
		/>
		<span class="description"><?php esc_html_e( 'business days', 'shahi-legalops-suite' ); ?></span>
		<?php
	}

	/**
	 * Render sources section description
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function render_sources_section(): void {
		echo wp_kses_post( __( 'Select which data sources to search when processing DSR requests.', 'shahi-legalops-suite' ) );
	}

	/**
	 * Render data source checkbox
	 *
	 * @since 3.0.1
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_source_checkbox( array $args ): void {
		$source   = $args['source'] ?? '';
		$settings = $this->get_settings();
		$checked  = isset( $settings['sources'][ $source ] ) ? (bool) $settings['sources'][ $source ] : true;

		?>
		<input 
			type="checkbox" 
			name="<?php echo esc_attr( self::OPTION_KEY . '[sources][' . $source . ']' ); ?>" 
			value="1"
			<?php checked( $checked ); ?>
		/>
		<?php
	}

	/**
	 * Render notifications section description
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function render_notifications_section(): void {
		echo wp_kses_post( __( 'Configure email notifications for DSR lifecycle events.', 'shahi-legalops-suite' ) );
	}

	/**
	 * Render appearance section description
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function render_appearance_section(): void {
		echo wp_kses_post( __( 'Customize the DSR form appearance and messaging.', 'shahi-legalops-suite' ) );
	}

	/**
	 * Render advanced section description
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function render_advanced_section(): void {
		echo wp_kses_post( __( 'Advanced options for security and data handling.', 'shahi-legalops-suite' ) );
	}

	/**
	 * Render generic checkbox field
	 *
	 * @since 3.0.1
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_checkbox( array $args ): void {
		$key      = $args['key'] ?? '';
		$settings = $this->get_settings();
		$checked  = isset( $settings[ $key ] ) ? (bool) $settings[ $key ] : false;

		?>
		<input 
			type="checkbox" 
			name="<?php echo esc_attr( self::OPTION_KEY . '[' . $key . ']' ); ?>" 
			value="1"
			<?php checked( $checked ); ?>
		/>
		<?php
	}

	/**
	 * Render text field
	 *
	 * @since 3.0.1
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_text_field( array $args ): void {
		$key         = $args['key'] ?? '';
		$type        = $args['type'] ?? 'text';
		$default     = $args['default'] ?? '';
		$placeholder = $args['placeholder'] ?? '';
		$settings    = $this->get_settings();
		$value       = $settings[ $key ] ?? $default;

		?>
		<input 
			type="<?php echo esc_attr( $type ); ?>" 
			name="<?php echo esc_attr( self::OPTION_KEY . '[' . $key . ']' ); ?>" 
			value="<?php echo esc_attr( $value ); ?>"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			class="regular-text"
		/>
		<?php
	}

	/**
	 * Render textarea field
	 *
	 * @since 3.0.1
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_textarea_field( array $args ): void {
		$key         = $args['key'] ?? '';
		$rows        = $args['rows'] ?? 5;
		$default     = $args['default'] ?? '';
		$placeholder = $args['placeholder'] ?? '';
		$settings    = $this->get_settings();
		$value       = $settings[ $key ] ?? $default;

		?>
		<textarea 
			name="<?php echo esc_attr( self::OPTION_KEY . '[' . $key . ']' ); ?>"
			rows="<?php echo absint( $rows ); ?>"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			class="large-text"
		><?php echo esc_textarea( $value ); ?></textarea>
		<?php
	}

	/**
	 * Render number field
	 *
	 * @since 3.0.1
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_number_field( array $args ): void {
		$key      = $args['key'] ?? '';
		$default  = $args['default'] ?? 0;
		$min      = $args['min'] ?? 0;
		$max      = $args['max'] ?? 9999;
		$settings = $this->get_settings();
		$value    = $settings[ $key ] ?? $default;

		?>
		<input 
			type="number" 
			name="<?php echo esc_attr( self::OPTION_KEY . '[' . $key . ']' ); ?>" 
			value="<?php echo esc_attr( $value ); ?>"
			min="<?php echo esc_attr( $min ); ?>"
			max="<?php echo esc_attr( $max ); ?>"
			class="small-text"
		/>
		<?php
	}

	/**
	 * Get all DSR settings
	 *
	 * @since 3.0.1
	 * @return array Settings array
	 */
	public function get_settings(): array {
		$settings = get_option( self::OPTION_KEY, array() );
		return is_array( $settings ) ? $settings : array();
	}

	/**
	 * Get default SLA days per regulation
	 *
	 * @since 3.0.1
	 * @return array Regulation => days
	 */
	private function get_sla_defaults(): array {
		return array(
			'GDPR'     => 30,
			'UK-GDPR'  => 30,
			'CCPA'     => 45,
			'LGPD'     => 15,
			'PIPEDA'   => 30,
			'POPIA'    => 30,
		);
	}

	/**
	 * Sanitize and validate settings on save
	 *
	 * @since 3.0.1
	 * @param array $input Raw input from form.
	 * @return array Sanitized settings
	 */
	public function sanitize_settings( array $input ): array {
		$sanitized = array();

		// Sanitize SLA fields (must be positive integers)
		foreach ( array( 'GDPR', 'UK-GDPR', 'CCPA', 'LGPD', 'PIPEDA', 'POPIA' ) as $reg ) {
			$key = 'sla_' . strtolower( $reg );
			if ( isset( $input[ $key ] ) ) {
				$sanitized[ $key ] = absint( $input[ $key ] );
				if ( $sanitized[ $key ] < 1 || $sanitized[ $key ] > 365 ) {
					$sanitized[ $key ] = $this->get_sla_defaults()[ $reg ];
				}
			}
		}

		// Sanitize checkboxes
		$checkbox_fields = array(
			'notify_requester',
			'notify_admin',
			'notify_overdue',
			'require_identity_verification',
			'enable_encryption',
		);
		foreach ( $checkbox_fields as $field ) {
			$sanitized[ $field ] = isset( $input[ $field ] ) && $input[ $field ] ? true : false;
		}

		// Sanitize sources
		if ( isset( $input['sources'] ) && is_array( $input['sources'] ) ) {
			$sanitized['sources'] = array();
			foreach ( array( 'posts', 'users', 'comments', 'forms', 'logs' ) as $source ) {
				$sanitized['sources'][ $source ] = isset( $input['sources'][ $source ] ) && $input['sources'][ $source ] ? true : false;
			}
		}

		// Sanitize text fields
		$text_fields = array( 'form_title', 'form_description', 'privacy_policy_url' );
		foreach ( $text_fields as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$sanitized[ $field ] = sanitize_text_field( $input[ $field ] );
			}
		}

		// Sanitize auto-delete days
		if ( isset( $input['auto_delete_days'] ) ) {
			$sanitized['auto_delete_days'] = absint( $input['auto_delete_days'] );
		}

		return $sanitized;
	}

	/**
	 * Enqueue assets for settings page
	 *
	 * @since 3.0.1
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( string $hook ): void {
		if ( $hook !== 'shahi-legalops-suite_page_slos-dsr-settings' ) {
			return;
		}

		wp_enqueue_style(
			'slos-dsr-settings',
			SLOS_PLUGIN_URL . 'assets/css/admin-dsr-settings.css',
			array(),
			SLOS_VERSION
		);
	}
}
```

2) **Initialize Settings Page in Plugin Boot**

File: `includes/Core/Plugin.php` → Add to `run()` method or constructor:

```php
// Initialize DSR Settings page (only in admin)
if ( is_admin() ) {
	new \ShahiLegalopsSuite\Admin\DSR_Settings();
}
```

Alternative location: Fire via action hook in DSR module init:

File: `includes/Modules/DSR_Portal/DSR_Portal.php` → Modify `init()` method:

```php
public function init(): void {
	if ( ! $this->is_enabled() ) {
		return;
	}

	// ... existing code ...

	// Initialize admin settings page
	if ( is_admin() ) {
		new \ShahiLegalopsSuite\Admin\DSR_Settings();
	}

	// ... rest of init ...
}
```

3) **Create Basic CSS for Settings Page**

File: `assets/css/admin-dsr-settings.css`

```css
.slos-dsr-settings {
	max-width: 900px;
	margin-top: 20px;
}

.slos-dsr-settings h1 {
	margin-bottom: 30px;
	padding-bottom: 10px;
	border-bottom: 2px solid #0073aa;
}

.slos-dsr-settings .form-table th {
	width: 300px;
}

.slos-dsr-settings .form-table td {
	padding: 15px 10px;
}

.slos-dsr-settings input[type="number"].small-text {
	width: 100px;
}

.slos-dsr-settings textarea {
	width: 100%;
	max-width: 600px;
}

.slos-dsr-settings .description {
	display: block;
	margin-top: 5px;
	font-style: italic;
	color: #666;
}

.slos-dsr-settings .submit {
	margin-top: 30px;
}
```

4) **Verification Tests**

```bash
# Test settings page accessible
wp eval "echo admin_url( 'admin.php?page=slos-dsr-settings' );"

# Create test settings
wp option set slos_dsr_settings '{
  "sla_gdpr": "35",
  "sla_ccpa": "50",
  "notify_requester": true,
  "notify_admin": true,
  "form_title": "Submit Your Data Subject Request",
  "sources": {
    "posts": true,
    "users": true
  }
}' --format=json

# Retrieve and verify
wp option get slos_dsr_settings --format=json

# Test sanitization (check values are bounds-checked)
wp eval "
\$settings = new ShahiLegalopsSuite\Admin\DSR_Settings();
\$input = [
    'sla_gdpr' => 500,  // Too high
    'notify_requester' => 1,
    'form_title' => 'Test Title'
];
\$sanitized = \$settings->sanitize_settings( \$input );
print_r( \$sanitized );
"

# Verify settings page menu item
wp menu item list admin --format=list | grep -i dsr
```

OUTPUT STATE:
✅ DSR Settings page registered and accessible at /admin.php?page=slos-dsr-settings
✅ SLA configuration per regulation (GDPR, CCPA, LGPD, UK-GDPR, PIPEDA, POPIA)
✅ Data source selection checkboxes (Posts, Users, Comments, Forms, Logs)
✅ Notification toggles (Requester, Admin, Overdue alerts)
✅ Portal appearance customization (title, description, privacy URL)
✅ Advanced options (Identity verification, encryption, auto-delete)
✅ All settings persisted in wp_options (slos_dsr_settings)
✅ Sanitization and validation applied on save

SUCCESS CRITERIA:
✅ Settings page accessible from DSR module settings URL
✅ All form fields render correctly
✅ Settings saved and retrieved from database
✅ Sanitization enforces bounds (SLA 1-365 days, etc.)
✅ Checkboxes properly checked/unchecked
✅ CSS styling applied
✅ No PHP errors on page load
✅ Settings accessible via get_option('slos_dsr_settings')

ROLLBACK:
```bash
# Remove settings page class
rm includes/Admin/DSR_Settings.php

# Remove settings CSS
rm assets/css/admin-dsr-settings.css

# Delete settings from database
wp option delete slos_dsr_settings

# Revert Plugin.php or DSR_Portal.php initialization
git checkout includes/Core/Plugin.php
git checkout includes/Modules/DSR_Portal/DSR_Portal.php
```

TROUBLESHOOTING:
- **Issue:** Settings page not showing → Verify DSR module is enabled; check menu registration hook order
- **Issue:** Settings not saving → Check wp_options table permissions; verify sanitize_settings callback
- **Issue:** CSS not loading → Clear browser cache; verify SLOS_PLUGIN_URL constant is set
- **Issue:** SLA values reverting to defaults → Check sanitize_settings logic for bounds

COMMIT MESSAGE:
```
feat(dsr): add comprehensive settings page

- SLA configuration per regulation (GDPR/CCPA/LGPD/UK-GDPR/PIPEDA/POPIA)
- Data source selection (Posts, Users, Comments, Forms, Logs)
- Notification preferences (Requester, Admin, Overdue alerts)
- Portal appearance customization (title, description, privacy URL)
- Advanced options (Identity verification, encryption, auto-delete)
- Full validation and sanitization
- Settings persisted in wp_options

Task: 3.0b (6-8 hours)
Next: Task 3.1 - DSR Repository
```

WHAT TO REPORT BACK:
"✅ TASK 3.0b COMPLETE
- DSR Settings page created and accessible
- SLA configuration per regulation implemented
- All preferences (notifications, sources, appearance) configurable
- Settings validated and stored in wp_options
- Ready to move to Task 3.1 (Repository)
"
```

---

## Notes for Implementation

- **Settings Storage:** All settings stored in single `slos_dsr_settings` option (serialized array) for simplicity
- **Defaults:** SLA defaults per regulation are regulatory minimums; can be overridden by admin
- **Sanitization:** Strict bounds on SLA (1-365), checkbox validation, text field escaping
- **Accessibility:** Form uses WordPress standard form rendering, fully keyboard accessible
- **i18n:** All strings translatable with `shahi-legalops-suite` domain
- **Linked from Module:** Settings URL available via `DSR_Portal::get_settings_url()` and Module Dashboard
