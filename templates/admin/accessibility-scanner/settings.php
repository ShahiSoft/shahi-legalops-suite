<?php
/**
 * Accessibility Scanner Settings Page Template
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Modules\AccessibilityScanner
 * @version    1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap shahi-a11y-settings">
    <h1><?php esc_html_e('Accessibility Scanner Settings', 'shahi-legalops-suite'); ?></h1>
    
    <?php settings_errors('shahi_a11y_settings'); ?>
    
    <form method="post" action="options.php" id="shahi-a11y-settings-form">
        <?php
        settings_fields('shahi_a11y_settings_group');
        ?>
        
        <!-- Tabbed Navigation -->
        <div class="shahi-tabs-nav" style="margin-bottom: 24px;">
            <a href="#general" class="shahi-tab-link active" data-tab="general">
                <span class="dashicons dashicons-admin-generic"></span>
                <?php esc_html_e('General', 'shahi-legalops-suite'); ?>
            </a>
            <a href="#scanner" class="shahi-tab-link" data-tab="scanner">
                <span class="dashicons dashicons-search"></span>
                <?php esc_html_e('Scanner', 'shahi-legalops-suite'); ?>
            </a>
            <a href="#widget" class="shahi-tab-link" data-tab="widget">
                <span class="dashicons dashicons-admin-appearance"></span>
                <?php esc_html_e('Widget', 'shahi-legalops-suite'); ?>
            </a>
            <a href="#fixes" class="shahi-tab-link" data-tab="fixes">
                <span class="dashicons dashicons-admin-tools"></span>
                <?php esc_html_e('Fixes', 'shahi-legalops-suite'); ?>
            </a>
            <a href="#ai" class="shahi-tab-link" data-tab="ai">
                <span class="dashicons dashicons-star-filled"></span>
                <?php esc_html_e('AI Features', 'shahi-legalops-suite'); ?>
            </a>
            <a href="#reporting" class="shahi-tab-link" data-tab="reporting">
                <span class="dashicons dashicons-chart-bar"></span>
                <?php esc_html_e('Reporting', 'shahi-legalops-suite'); ?>
            </a>
            <a href="#advanced" class="shahi-tab-link" data-tab="advanced">
                <span class="dashicons dashicons-admin-settings"></span>
                <?php esc_html_e('Advanced', 'shahi-legalops-suite'); ?>
            </a>
        </div>
        
        <!-- General Settings Tab -->
        <div id="general" class="shahi-tab-content active">
            <div class="shahi-a11y-settings-section">
                <?php do_settings_sections('shahi-accessibility-settings'); ?>
            </div>
        </div>
        
        <!-- Scanner Settings Tab -->
        <div id="scanner" class="shahi-tab-content" style="display: none;">
            <div class="shahi-a11y-settings-section">
                <h3><?php esc_html_e('Scanner Configuration', 'shahi-legalops-suite'); ?></h3>
                <p class="description">
                    <?php esc_html_e('Configure how the accessibility scanner behaves and which checks to run.', 'shahi-legalops-suite'); ?>
                </p>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label><?php esc_html_e('Enabled Checks', 'shahi-legalops-suite'); ?></label>
                            </th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text">
                                        <span><?php esc_html_e('Enabled Checks', 'shahi-legalops-suite'); ?></span>
                                    </legend>
                                    <label>
                                        <input type="checkbox" name="shahi_a11y_settings[scanner][check_images]" value="1" checked>
                                        <?php esc_html_e('Image Alt Text', 'shahi-legalops-suite'); ?>
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="shahi_a11y_settings[scanner][check_headings]" value="1" checked>
                                        <?php esc_html_e('Heading Structure', 'shahi-legalops-suite'); ?>
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="shahi_a11y_settings[scanner][check_links]" value="1" checked>
                                        <?php esc_html_e('Link Text', 'shahi-legalops-suite'); ?>
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="shahi_a11y_settings[scanner][check_forms]" value="1" checked>
                                        <?php esc_html_e('Form Labels', 'shahi-legalops-suite'); ?>
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="shahi_a11y_settings[scanner][check_aria]" value="1" checked>
                                        <?php esc_html_e('ARIA Attributes', 'shahi-legalops-suite'); ?>
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="shahi_a11y_settings[scanner][check_color_contrast]" value="1" checked>
                                        <?php esc_html_e('Color Contrast', 'shahi-legalops-suite'); ?>
                                    </label><br>
                                </fieldset>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Widget Settings Tab -->
        <div id="widget" class="shahi-tab-content" style="display: none;">
            <div class="shahi-a11y-settings-section">
                <h3><?php esc_html_e('Frontend Widget Configuration', 'shahi-legalops-suite'); ?></h3>
                <p class="description">
                    <?php esc_html_e('Configure the frontend accessibility widget for visitors.', 'shahi-legalops-suite'); ?>
                </p>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label><?php esc_html_e('Widget Preview', 'shahi-legalops-suite'); ?></label>
                            </th>
                            <td>
                                <div class="shahi-a11y-widget-preview" style="background: var(--shahi-bg-tertiary); padding: 20px; border-radius: 8px; margin-bottom: 16px;">
                                    <p><?php esc_html_e('Widget preview will appear here', 'shahi-legalops-suite'); ?></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="widget-button-color"><?php esc_html_e('Button Color', 'shahi-legalops-suite'); ?></label>
                            </th>
                            <td>
                                <input type="color" id="widget-button-color" name="shahi_a11y_settings[widget][button_color]" value="#c0c0c0">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Fixes Settings Tab -->
        <div id="fixes" class="shahi-tab-content" style="display: none;">
            <div class="shahi-a11y-settings-section">
                <h3><?php esc_html_e('One-Click Fix Configuration', 'shahi-legalops-suite'); ?></h3>
                <p class="description">
                    <?php esc_html_e('Configure automatic fix behavior and approval requirements.', 'shahi-legalops-suite'); ?>
                </p>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label><?php esc_html_e('Backup Before Fix', 'shahi-legalops-suite'); ?></label>
                            </th>
                            <td>
                                <label class="shahi-toggle-switch">
                                    <input type="checkbox" name="shahi_a11y_settings[fixes][backup_before_fix]" value="1" checked>
                                    <span class="shahi-toggle-slider"></span>
                                </label>
                                <p class="description"><?php esc_html_e('Create backup before applying fixes.', 'shahi-legalops-suite'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?php esc_html_e('Log Fixes', 'shahi-legalops-suite'); ?></label>
                            </th>
                            <td>
                                <label class="shahi-toggle-switch">
                                    <input type="checkbox" name="shahi_a11y_settings[fixes][log_fixes]" value="1" checked>
                                    <span class="shahi-toggle-slider"></span>
                                </label>
                                <p class="description"><?php esc_html_e('Keep log of all applied fixes.', 'shahi-legalops-suite'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- AI Features Tab -->
        <div id="ai" class="shahi-tab-content" style="display: none;">
            <div class="shahi-a11y-settings-section">
                <h3><?php esc_html_e('AI-Powered Features', 'shahi-legalops-suite'); ?></h3>
                <p class="description">
                    <?php esc_html_e('Configure AI-powered accessibility features like automatic alt text generation.', 'shahi-legalops-suite'); ?>
                </p>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label><?php esc_html_e('Alt Text Generation', 'shahi-legalops-suite'); ?></label>
                            </th>
                            <td>
                                <label class="shahi-toggle-switch">
                                    <input type="checkbox" name="shahi_a11y_settings[ai][alt_text_generation]" value="1">
                                    <span class="shahi-toggle-slider"></span>
                                </label>
                                <p class="description"><?php esc_html_e('Use AI to generate alt text for images.', 'shahi-legalops-suite'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?php esc_html_e('Content Simplification', 'shahi-legalops-suite'); ?></label>
                            </th>
                            <td>
                                <label class="shahi-toggle-switch">
                                    <input type="checkbox" name="shahi_a11y_settings[ai][content_simplification]" value="1">
                                    <span class="shahi-toggle-slider"></span>
                                </label>
                                <p class="description"><?php esc_html_e('Use AI to simplify complex content.', 'shahi-legalops-suite'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Reporting Tab -->
        <div id="reporting" class="shahi-tab-content" style="display: none;">
            <div class="shahi-a11y-settings-section">
                <h3><?php esc_html_e('Reporting & Notifications', 'shahi-legalops-suite'); ?></h3>
                <p class="description">
                    <?php esc_html_e('Configure report generation and email notifications.', 'shahi-legalops-suite'); ?>
                </p>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label><?php esc_html_e('Notification Frequency', 'shahi-legalops-suite'); ?></label>
                            </th>
                            <td>
                                <select name="shahi_a11y_settings[reporting][notification_frequency]" class="shahi-select">
                                    <option value="immediate"><?php esc_html_e('Immediate', 'shahi-legalops-suite'); ?></option>
                                    <option value="daily" selected><?php esc_html_e('Daily Digest', 'shahi-legalops-suite'); ?></option>
                                    <option value="weekly"><?php esc_html_e('Weekly Summary', 'shahi-legalops-suite'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?php esc_html_e('Export Format', 'shahi-legalops-suite'); ?></label>
                            </th>
                            <td>
                                <select name="shahi_a11y_settings[reporting][export_format]" class="shahi-select">
                                    <option value="pdf" selected><?php esc_html_e('PDF', 'shahi-legalops-suite'); ?></option>
                                    <option value="csv"><?php esc_html_e('CSV', 'shahi-legalops-suite'); ?></option>
                                    <option value="json"><?php esc_html_e('JSON', 'shahi-legalops-suite'); ?></option>
                                    <option value="html"><?php esc_html_e('HTML', 'shahi-legalops-suite'); ?></option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Advanced Tab -->
        <div id="advanced" class="shahi-tab-content" style="display: none;">
            <div class="shahi-a11y-settings-section">
                <h3><?php esc_html_e('Advanced Settings', 'shahi-legalops-suite'); ?></h3>
                <p class="description">
                    <?php esc_html_e('Advanced settings for developers and power users.', 'shahi-legalops-suite'); ?>
                </p>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label><?php esc_html_e('Enable REST API', 'shahi-legalops-suite'); ?></label>
                            </th>
                            <td>
                                <label class="shahi-toggle-switch">
                                    <input type="checkbox" name="shahi_a11y_settings[advanced][enable_rest_api]" value="1" checked>
                                    <span class="shahi-toggle-slider"></span>
                                </label>
                                <p class="description"><?php esc_html_e('Allow access via REST API endpoints.', 'shahi-legalops-suite'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?php esc_html_e('Enable WP-CLI', 'shahi-legalops-suite'); ?></label>
                            </th>
                            <td>
                                <label class="shahi-toggle-switch">
                                    <input type="checkbox" name="shahi_a11y_settings[advanced][enable_cli]" value="1" checked>
                                    <span class="shahi-toggle-slider"></span>
                                </label>
                                <p class="description"><?php esc_html_e('Enable WP-CLI commands for accessibility scanning.', 'shahi-legalops-suite'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?php esc_html_e('Cache Duration', 'shahi-legalops-suite'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="shahi_a11y_settings[advanced][cache_duration]" value="300" min="0" step="60" class="shahi-input small-text">
                                <span class="description"><?php esc_html_e('seconds', 'shahi-legalops-suite'); ?></span>
                                <p class="description"><?php esc_html_e('How long to cache scan results (0 = no caching).', 'shahi-legalops-suite'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?php esc_html_e('Delete Data on Deactivation', 'shahi-legalops-suite'); ?></label>
                            </th>
                            <td>
                                <label class="shahi-toggle-switch">
                                    <input type="checkbox" name="shahi_a11y_settings[advanced][delete_data_on_deactivation]" value="1">
                                    <span class="shahi-toggle-slider"></span>
                                </label>
                                <p class="description" style="color: #ff4757;">
                                    <?php esc_html_e('⚠️ WARNING: This will delete all scan data, issues, and reports when the module is deactivated.', 'shahi-legalops-suite'); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php submit_button(__('Save Settings', 'shahi-legalops-suite'), 'primary', 'submit', true, array('id' => 'shahi-a11y-save-settings')); ?>
        
        <button type="button" id="shahi-a11y-reset-settings" class="button">
            <?php esc_html_e('Reset to Defaults', 'shahi-legalops-suite'); ?>
        </button>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.shahi-tab-link').on('click', function(e) {
        e.preventDefault();
        const tab = $(this).data('tab');
        
        // Update active states
        $('.shahi-tab-link').removeClass('active');
        $(this).addClass('active');
        
        $('.shahi-tab-content').hide();
        $('#' + tab).show();
    });
});
</script>
