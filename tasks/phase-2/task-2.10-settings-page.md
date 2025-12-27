# TASK 2.10: Settings Page

**Phase:** 2 (Consent Management - CORE)  
**Effort:** 8-10 hours  
**Prerequisites:** TASK 2.9 complete (Analytics exists)  
**Next Task:** [task-2.11-export-import.md](task-2.11-export-import.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 2.10 for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
Create a comprehensive settings page for the consent management system. Allow administrators
to customize banner appearance, text, cookie scanner settings, geolocation provider configuration,
data retention periods, and export preferences.

INPUT STATE (verify these exist):
✅ Consent banner (Task 2.4)
✅ Cookie scanner (Task 2.5)
✅ Geolocation detection (Task 2.7)
✅ Analytics dashboard (Task 2.8)

YOUR TASK:

1. **Create Settings Page**

Location: `includes/Admin/Consent_Settings_Page.php`

```php
<?php
/**
 * Consent Settings Page
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

namespace Shahi\LegalOps\Admin;

class Consent_Settings_Page {

    /**
     * Initialize settings page
     */
    public function init() {
        add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    /**
     * Add settings page to admin menu
     */
    public function add_settings_page() {
        add_submenu_page(
            'slos-dashboard',
            __( 'Consent Settings', 'shahi-legalops' ),
            __( 'Settings', 'shahi-legalops' ),
            'manage_options',
            'slos-consent-settings',
            [ $this, 'render_settings_page' ]
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // Banner Settings Section
        add_settings_section(
            'slos_banner_settings',
            __( 'Banner Settings', 'shahi-legalops' ),
            [ $this, 'render_banner_section' ],
            'slos_consent_settings'
        );

        // Template
        register_setting( 'slos_consent_settings', 'slos_banner_template' );
        add_settings_field(
            'slos_banner_template',
            __( 'Banner Template', 'shahi-legalops' ),
            [ $this, 'render_template_field' ],
            'slos_consent_settings',
            'slos_banner_settings'
        );

        // Position
        register_setting( 'slos_consent_settings', 'slos_banner_position' );
        add_settings_field(
            'slos_banner_position',
            __( 'Banner Position', 'shahi-legalops' ),
            [ $this, 'render_position_field' ],
            'slos_consent_settings',
            'slos_banner_settings'
        );

        // Theme
        register_setting( 'slos_consent_settings', 'slos_banner_theme' );
        add_settings_field(
            'slos_banner_theme',
            __( 'Banner Theme', 'shahi-legalops' ),
            [ $this, 'render_theme_field' ],
            'slos_consent_settings',
            'slos_banner_settings'
        );

        // Primary Color
        register_setting( 'slos_consent_settings', 'slos_banner_primary_color' );
        add_settings_field(
            'slos_banner_primary_color',
            __( 'Primary Color', 'shahi-legalops' ),
            [ $this, 'render_color_field' ],
            'slos_consent_settings',
            'slos_banner_settings',
            [ 'option' => 'slos_banner_primary_color', 'default' => '#4CAF50' ]
        );

        // Accept Button Color
        register_setting( 'slos_consent_settings', 'slos_banner_accept_color' );
        add_settings_field(
            'slos_banner_accept_color',
            __( 'Accept Button Color', 'shahi-legalops' ),
            [ $this, 'render_color_field' ],
            'slos_consent_settings',
            'slos_banner_settings',
            [ 'option' => 'slos_banner_accept_color', 'default' => '#4CAF50' ]
        );

        // Reject Button Color
        register_setting( 'slos_consent_settings', 'slos_banner_reject_color' );
        add_settings_field(
            'slos_banner_reject_color',
            __( 'Reject Button Color', 'shahi-legalops' ),
            [ $this, 'render_color_field' ],
            'slos_consent_settings',
            'slos_banner_settings',
            [ 'option' => 'slos_banner_reject_color', 'default' => '#f44336' ]
        );

        // Text Customization Section
        add_settings_section(
            'slos_text_settings',
            __( 'Text Customization', 'shahi-legalops' ),
            [ $this, 'render_text_section' ],
            'slos_consent_settings'
        );

        // Banner Heading
        register_setting( 'slos_consent_settings', 'slos_banner_heading' );
        add_settings_field(
            'slos_banner_heading',
            __( 'Banner Heading', 'shahi-legalops' ),
            [ $this, 'render_text_field' ],
            'slos_consent_settings',
            'slos_text_settings',
            [ 'option' => 'slos_banner_heading', 'default' => 'We value your privacy' ]
        );

        // Banner Message
        register_setting( 'slos_consent_settings', 'slos_banner_message' );
        add_settings_field(
            'slos_banner_message',
            __( 'Banner Message', 'shahi-legalops' ),
            [ $this, 'render_textarea_field' ],
            'slos_consent_settings',
            'slos_text_settings',
            [
                'option' => 'slos_banner_message',
                'default' => 'We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic.',
            ]
        );

        // Accept Button Text
        register_setting( 'slos_consent_settings', 'slos_accept_button_text' );
        add_settings_field(
            'slos_accept_button_text',
            __( 'Accept Button Text', 'shahi-legalops' ),
            [ $this, 'render_text_field' ],
            'slos_consent_settings',
            'slos_text_settings',
            [ 'option' => 'slos_accept_button_text', 'default' => 'Accept All' ]
        );

        // Reject Button Text
        register_setting( 'slos_consent_settings', 'slos_reject_button_text' );
        add_settings_field(
            'slos_reject_button_text',
            __( 'Reject Button Text', 'shahi-legalops' ),
            [ $this, 'render_text_field' ],
            'slos_consent_settings',
            'slos_text_settings',
            [ 'option' => 'slos_reject_button_text', 'default' => 'Reject All' ]
        );

        // Cookie Scanner Settings Section
        add_settings_section(
            'slos_scanner_settings',
            __( 'Cookie Scanner Settings', 'shahi-legalops' ),
            [ $this, 'render_scanner_section' ],
            'slos_consent_settings'
        );

        // Auto-scan enabled
        register_setting( 'slos_consent_settings', 'slos_scanner_auto_scan' );
        add_settings_field(
            'slos_scanner_auto_scan',
            __( 'Automatic Scanning', 'shahi-legalops' ),
            [ $this, 'render_checkbox_field' ],
            'slos_consent_settings',
            'slos_scanner_settings',
            [ 'option' => 'slos_scanner_auto_scan', 'label' => 'Enable automatic daily cookie scans' ]
        );

        // Scan frequency
        register_setting( 'slos_consent_settings', 'slos_scanner_frequency' );
        add_settings_field(
            'slos_scanner_frequency',
            __( 'Scan Frequency', 'shahi-legalops' ),
            [ $this, 'render_frequency_field' ],
            'slos_consent_settings',
            'slos_scanner_settings'
        );

        // Geolocation Settings Section
        add_settings_section(
            'slos_geo_settings',
            __( 'Geolocation Settings', 'shahi-legalops' ),
            [ $this, 'render_geo_section' ],
            'slos_consent_settings'
        );

        // Geolocation enabled
        register_setting( 'slos_consent_settings', 'slos_geo_enabled' );
        add_settings_field(
            'slos_geo_enabled',
            __( 'Enable Geolocation', 'shahi-legalops' ),
            [ $this, 'render_checkbox_field' ],
            'slos_consent_settings',
            'slos_geo_settings',
            [ 'option' => 'slos_geo_enabled', 'label' => 'Auto-detect user location for compliance' ]
        );

        // Geolocation provider
        register_setting( 'slos_consent_settings', 'slos_geo_provider' );
        add_settings_field(
            'slos_geo_provider',
            __( 'Geolocation Provider', 'shahi-legalops' ),
            [ $this, 'render_provider_field' ],
            'slos_consent_settings',
            'slos_geo_settings'
        );

        // MaxMind API Key
        register_setting( 'slos_consent_settings', 'slos_maxmind_api_key' );
        add_settings_field(
            'slos_maxmind_api_key',
            __( 'MaxMind API Key', 'shahi-legalops' ),
            [ $this, 'render_text_field' ],
            'slos_consent_settings',
            'slos_geo_settings',
            [ 'option' => 'slos_maxmind_api_key', 'default' => '' ]
        );

        // Data Retention Settings Section
        add_settings_section(
            'slos_retention_settings',
            __( 'Data Retention Settings', 'shahi-legalops' ),
            [ $this, 'render_retention_section' ],
            'slos_consent_settings'
        );

        // Consent retention period
        register_setting( 'slos_consent_settings', 'slos_consent_retention' );
        add_settings_field(
            'slos_consent_retention',
            __( 'Consent Retention Period', 'shahi-legalops' ),
            [ $this, 'render_retention_field' ],
            'slos_consent_settings',
            'slos_retention_settings'
        );

        // Log retention period
        register_setting( 'slos_consent_settings', 'slos_log_retention' );
        add_settings_field(
            'slos_log_retention',
            __( 'Log Retention Period', 'shahi-legalops' ),
            [ $this, 'render_retention_field' ],
            'slos_consent_settings',
            'slos_retention_settings',
            [ 'option' => 'slos_log_retention', 'default' => 365 ]
        );

        // Export Settings Section
        add_settings_section(
            'slos_export_settings',
            __( 'Export Settings', 'shahi-legalops' ),
            [ $this, 'render_export_section' ],
            'slos_consent_settings'
        );

        // Default export format
        register_setting( 'slos_consent_settings', 'slos_export_format' );
        add_settings_field(
            'slos_export_format',
            __( 'Default Export Format', 'shahi-legalops' ),
            [ $this, 'render_export_format_field' ],
            'slos_consent_settings',
            'slos_export_settings'
        );

        // Scheduled exports
        register_setting( 'slos_consent_settings', 'slos_scheduled_exports' );
        add_settings_field(
            'slos_scheduled_exports',
            __( 'Scheduled Exports', 'shahi-legalops' ),
            [ $this, 'render_checkbox_field' ],
            'slos_consent_settings',
            'slos_export_settings',
            [ 'option' => 'slos_scheduled_exports', 'label' => 'Enable weekly automatic exports' ]
        );
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields( 'slos_consent_settings' );
                do_settings_sections( 'slos_consent_settings' );
                submit_button();
                ?>
            </form>

            <!-- Preview Banner -->
            <div class="slos-banner-preview">
                <h2><?php _e( 'Banner Preview', 'shahi-legalops' ); ?></h2>
                <div id="slos-preview-container"></div>
                <button type="button" class="button" id="slos-refresh-preview">
                    <?php _e( 'Refresh Preview', 'shahi-legalops' ); ?>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Render sections
     */
    public function render_banner_section() {
        echo '<p>' . __( 'Customize the appearance and behavior of the consent banner.', 'shahi-legalops' ) . '</p>';
    }

    public function render_text_section() {
        echo '<p>' . __( 'Customize all text displayed in the consent banner.', 'shahi-legalops' ) . '</p>';
    }

    public function render_scanner_section() {
        echo '<p>' . __( 'Configure automatic cookie scanning and detection.', 'shahi-legalops' ) . '</p>';
    }

    public function render_geo_section() {
        echo '<p>' . __( 'Configure geolocation detection for location-based compliance.', 'shahi-legalops' ) . '</p>';
    }

    public function render_retention_section() {
        echo '<p>' . __( 'Set data retention periods for consents and logs (GDPR Article 5).', 'shahi-legalops' ) . '</p>';
    }

    public function render_export_section() {
        echo '<p>' . __( 'Configure export settings for consent records.', 'shahi-legalops' ) . '</p>';
    }

    /**
     * Render fields
     */
    public function render_template_field() {
        $value = get_option( 'slos_banner_template', 'eu' );
        ?>
        <select name="slos_banner_template">
            <option value="eu" <?php selected( $value, 'eu' ); ?>><?php _e( 'EU/GDPR (Granular)', 'shahi-legalops' ); ?></option>
            <option value="ccpa" <?php selected( $value, 'ccpa' ); ?>><?php _e( 'CCPA (Opt-out)', 'shahi-legalops' ); ?></option>
            <option value="simple" <?php selected( $value, 'simple' ); ?>><?php _e( 'Simple', 'shahi-legalops' ); ?></option>
            <option value="advanced" <?php selected( $value, 'advanced' ); ?>><?php _e( 'Advanced', 'shahi-legalops' ); ?></option>
        </select>
        <?php
    }

    public function render_position_field() {
        $value = get_option( 'slos_banner_position', 'bottom' );
        ?>
        <select name="slos_banner_position">
            <option value="bottom" <?php selected( $value, 'bottom' ); ?>><?php _e( 'Bottom', 'shahi-legalops' ); ?></option>
            <option value="top" <?php selected( $value, 'top' ); ?>><?php _e( 'Top', 'shahi-legalops' ); ?></option>
        </select>
        <?php
    }

    public function render_theme_field() {
        $value = get_option( 'slos_banner_theme', 'light' );
        ?>
        <select name="slos_banner_theme">
            <option value="light" <?php selected( $value, 'light' ); ?>><?php _e( 'Light', 'shahi-legalops' ); ?></option>
            <option value="dark" <?php selected( $value, 'dark' ); ?>><?php _e( 'Dark', 'shahi-legalops' ); ?></option>
        </select>
        <?php
    }

    public function render_color_field( $args ) {
        $option = $args['option'];
        $default = $args['default'] ?? '#000000';
        $value = get_option( $option, $default );
        ?>
        <input type="color" name="<?php echo esc_attr( $option ); ?>" value="<?php echo esc_attr( $value ); ?>">
        <?php
    }

    public function render_text_field( $args ) {
        $option = $args['option'];
        $default = $args['default'] ?? '';
        $value = get_option( $option, $default );
        ?>
        <input type="text" name="<?php echo esc_attr( $option ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text">
        <?php
    }

    public function render_textarea_field( $args ) {
        $option = $args['option'];
        $default = $args['default'] ?? '';
        $value = get_option( $option, $default );
        ?>
        <textarea name="<?php echo esc_attr( $option ); ?>" rows="3" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
        <?php
    }

    public function render_checkbox_field( $args ) {
        $option = $args['option'];
        $label = $args['label'];
        $value = get_option( $option, false );
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr( $option ); ?>" value="1" <?php checked( $value, 1 ); ?>>
            <?php echo esc_html( $label ); ?>
        </label>
        <?php
    }

    public function render_frequency_field() {
        $value = get_option( 'slos_scanner_frequency', 'daily' );
        ?>
        <select name="slos_scanner_frequency">
            <option value="daily" <?php selected( $value, 'daily' ); ?>><?php _e( 'Daily', 'shahi-legalops' ); ?></option>
            <option value="weekly" <?php selected( $value, 'weekly' ); ?>><?php _e( 'Weekly', 'shahi-legalops' ); ?></option>
            <option value="monthly" <?php selected( $value, 'monthly' ); ?>><?php _e( 'Monthly', 'shahi-legalops' ); ?></option>
        </select>
        <?php
    }

    public function render_provider_field() {
        $value = get_option( 'slos_geo_provider', 'ipapi' );
        ?>
        <select name="slos_geo_provider">
            <option value="ipapi" <?php selected( $value, 'ipapi' ); ?>><?php _e( 'ipapi.co (Free)', 'shahi-legalops' ); ?></option>
            <option value="maxmind" <?php selected( $value, 'maxmind' ); ?>><?php _e( 'MaxMind (Paid)', 'shahi-legalops' ); ?></option>
            <option value="ip2location" <?php selected( $value, 'ip2location' ); ?>><?php _e( 'IP2Location (Paid)', 'shahi-legalops' ); ?></option>
        </select>
        <?php
    }

    public function render_retention_field( $args = [] ) {
        $option = $args['option'] ?? 'slos_consent_retention';
        $default = $args['default'] ?? 730;
        $value = get_option( $option, $default );
        ?>
        <input type="number" name="<?php echo esc_attr( $option ); ?>" value="<?php echo esc_attr( $value ); ?>" min="1" max="3650"> days
        <p class="description"><?php _e( 'GDPR recommends keeping consent records for at least 2 years (730 days).', 'shahi-legalops' ); ?></p>
        <?php
    }

    public function render_export_format_field() {
        $value = get_option( 'slos_export_format', 'csv' );
        ?>
        <select name="slos_export_format">
            <option value="csv" <?php selected( $value, 'csv' ); ?>><?php _e( 'CSV', 'shahi-legalops' ); ?></option>
            <option value="json" <?php selected( $value, 'json' ); ?>><?php _e( 'JSON', 'shahi-legalops' ); ?></option>
            <option value="pdf" <?php selected( $value, 'pdf' ); ?>><?php _e( 'PDF', 'shahi-legalops' ); ?></option>
        </select>
        <?php
    }

    /**
     * Enqueue assets
     */
    public function enqueue_assets( $hook ) {
        if ( 'slos_page_slos-consent-settings' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        
        wp_enqueue_script(
            'slos-settings',
            plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/js/admin-settings.js',
            [ 'jquery', 'wp-color-picker' ],
            '3.0.1',
            true
        );
    }
}
```

2. **Test settings page**

```bash
# Access settings page
# Navigate to: SLOS > Settings

# Change banner template to CCPA
# Save settings

# Verify option saved
wp option get slos_banner_template
# Should output: ccpa

# Verify color picker works
# Change primary color, save
wp option get slos_banner_primary_color
```

OUTPUT STATE:
✅ Consent_Settings_Page created
✅ Settings registered in WordPress
✅ Banner customization (template, position, theme, colors)
✅ Text customization (heading, message, button text)
✅ Cookie scanner settings
✅ Geolocation settings
✅ Data retention settings
✅ Export settings
✅ Live banner preview

VERIFICATION:

1. **Check settings page:**
```bash
# Navigate to SLOS > Settings in admin
# Should see all sections
```

2. **Test settings save:**
- Change banner template
- Click "Save Changes"
- Refresh page
- Setting should persist

3. **Test preview:**
- Change colors
- Click "Refresh Preview"
- Banner preview should update

SUCCESS CRITERIA:
✅ Settings page accessible
✅ All sections render
✅ Settings save correctly
✅ Color pickers work
✅ Preview updates live

ROLLBACK:
```bash
rm includes/Admin/Consent_Settings_Page.php
wp option delete slos_banner_template
wp option delete slos_banner_position
# etc.
```

TROUBLESHOOTING:

**Problem 1: Settings not saving**
- Check user capabilities: current_user_can('manage_options')
- Verify settings registered: print_r(get_registered_settings())

**Problem 2: Color picker not working**
- Check wp-color-picker enqueued
- Verify jQuery loaded

**Problem 3: Preview not updating**
- Check JavaScript console for errors
- Verify admin-settings.js enqueued

COMMIT MESSAGE:
```
feat(consent): Add settings page

- Create Consent_Settings_Page
- Add banner customization (template, position, theme, colors)
- Add text customization (heading, message, buttons)
- Add cookie scanner settings
- Add geolocation settings
- Add data retention settings
- Add export settings
- Integrate color pickers
- Add live banner preview

Admin customization ready.

Task: 2.10 (8-10 hours)
Next: Task 2.11 - Export/Import
```

WHAT TO REPORT BACK:
"✅ TASK 2.10 COMPLETE

Created:
- Consent_Settings_Page

Implemented:
- ✅ Banner customization (4 templates, colors, positioning)
- ✅ Text customization (all strings editable)
- ✅ Cookie scanner settings
- ✅ Geolocation provider configuration
- ✅ Data retention settings (GDPR compliant)
- ✅ Export preferences
- ✅ Color pickers
- ✅ Live preview

Verification passed:
- ✅ Settings page accessible
- ✅ All settings save correctly
- ✅ Preview updates live
- ✅ Color pickers working

📍 Ready for TASK 2.11: [task-2.11-export-import.md](task-2.11-export-import.md)"
```

---

## ✅ COMPLETION CHECKLIST

- [ ] Consent_Settings_Page.php created
- [ ] Settings registered
- [ ] All sections rendering
- [ ] Settings saving correctly
- [ ] Preview working
- [ ] Committed to git
- [ ] Ready for Task 2.11

---

**Status:** ✅ Ready to execute  
**Time:** 8-10 hours  
**Next:** [task-2.11-export-import.md](task-2.11-export-import.md)
