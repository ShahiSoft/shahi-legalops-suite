<?php
/**
 * Module Settings Template
 * 
 * PLACEHOLDER FILE - This is a template for module settings pages.
 * Copy this file to your module's admin directory and customize it.
 * 
 * Instructions:
 * 1. Copy to: includes/modules/{module-slug}/admin/templates/settings.php
 * 2. Replace all PLACEHOLDER values with your actual content
 * 3. This file is loaded by your module's admin class
 * 4. Variables available: $module (module instance), $settings (module settings)
 * 
 * @package    {PluginNamespace}
 * @subpackage Modules\{ModuleName}\Admin\Templates
 * @since      1.0.0
 */

// PLACEHOLDER: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap shahi-module-settings">
    
    <!-- PLACEHOLDER: Module Header -->
    <div class="shahi-module-header">
        <h1 class="shahi-module-title">
            <span class="shahi-icon">🔧</span> <!-- PLACEHOLDER: Change icon -->
            <?php echo esc_html($module->get_name()); ?>
            <span class="shahi-version">v<?php echo esc_html($module->get_version()); ?></span>
        </h1>
        
        <div class="shahi-module-actions">
            <!-- PLACEHOLDER: Module enable/disable toggle -->
            <label class="shahi-toggle">
                <input 
                    type="checkbox" 
                    id="module-enabled" 
                    name="module_enabled"
                    <?php checked($module->is_enabled(), true); ?>
                >
                <span class="shahi-toggle-slider"></span>
                <span class="shahi-toggle-label">
                    <?php esc_html_e('Module Enabled', 'shahi-template'); ?>
                </span>
            </label>
        </div>
    </div>
    
    <!-- PLACEHOLDER: Module Description -->
    <div class="shahi-module-description">
        <p><?php echo esc_html($module->get_description()); ?></p>
    </div>
    
    <!-- PLACEHOLDER: Admin Notices -->
    <?php if (isset($_GET['message']) && $_GET['message'] === 'saved'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Module settings saved successfully!', 'shahi-template'); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- PLACEHOLDER: Module Content -->
    <div class="shahi-module-content">
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="shahi-module-form">
            
            <?php wp_nonce_field('save_module_settings'); // PLACEHOLDER: Update nonce action ?>
            <input type="hidden" name="action" value="save_module_settings"> <!-- PLACEHOLDER: Update action -->
            <input type="hidden" name="module_id" value="<?php echo esc_attr($module->get_id()); ?>">
            
            <!-- PLACEHOLDER: Settings Grid -->
            <div class="shahi-settings-grid">
                
                <!-- PLACEHOLDER: Settings Card 1 -->
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h3><?php esc_html_e('Basic Configuration', 'shahi-template'); ?></h3>
                    </div>
                    <div class="shahi-card-body">
                        
                        <!-- PLACEHOLDER: Setting Field 1 -->
                        <div class="shahi-form-field">
                            <label for="setting1" class="shahi-label">
                                <?php esc_html_e('Setting 1', 'shahi-template'); ?>
                                <span class="shahi-required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="setting1" 
                                name="settings[setting1]" 
                                value="<?php echo esc_attr($settings['setting1'] ?? ''); ?>"
                                class="shahi-input"
                                required
                            >
                            <p class="shahi-field-description">
                                <?php esc_html_e('PLACEHOLDER: Description of setting 1', 'shahi-template'); ?>
                            </p>
                        </div>
                        
                        <!-- PLACEHOLDER: Setting Field 2 -->
                        <div class="shahi-form-field">
                            <label for="setting2" class="shahi-label">
                                <?php esc_html_e('Setting 2', 'shahi-template'); ?>
                            </label>
                            <select id="setting2" name="settings[setting2]" class="shahi-select">
                                <option value="option1" <?php selected($settings['setting2'] ?? '', 'option1'); ?>>
                                    <?php esc_html_e('Option 1', 'shahi-template'); ?>
                                </option>
                                <option value="option2" <?php selected($settings['setting2'] ?? '', 'option2'); ?>>
                                    <?php esc_html_e('Option 2', 'shahi-template'); ?>
                                </option>
                            </select>
                            <p class="shahi-field-description">
                                <?php esc_html_e('PLACEHOLDER: Description of setting 2', 'shahi-template'); ?>
                            </p>
                        </div>
                        
                    </div>
                </div>
                
                <!-- PLACEHOLDER: Settings Card 2 -->
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h3><?php esc_html_e('Feature Options', 'shahi-template'); ?></h3>
                    </div>
                    <div class="shahi-card-body">
                        
                        <!-- PLACEHOLDER: Checkbox List -->
                        <div class="shahi-form-field">
                            <label class="shahi-label">
                                <?php esc_html_e('Enable Features', 'shahi-template'); ?>
                            </label>
                            
                            <div class="shahi-checkbox-list">
                                <label class="shahi-checkbox-label">
                                    <input 
                                        type="checkbox" 
                                        name="settings[feature_a]" 
                                        value="1"
                                        <?php checked($settings['feature_a'] ?? false, true); ?>
                                    >
                                    <span><?php esc_html_e('Feature A', 'shahi-template'); ?></span>
                                </label>
                                
                                <label class="shahi-checkbox-label">
                                    <input 
                                        type="checkbox" 
                                        name="settings[feature_b]" 
                                        value="1"
                                        <?php checked($settings['feature_b'] ?? false, true); ?>
                                    >
                                    <span><?php esc_html_e('Feature B', 'shahi-template'); ?></span>
                                </label>
                                
                                <label class="shahi-checkbox-label">
                                    <input 
                                        type="checkbox" 
                                        name="settings[feature_c]" 
                                        value="1"
                                        <?php checked($settings['feature_c'] ?? false, true); ?>
                                    >
                                    <span><?php esc_html_e('Feature C', 'shahi-template'); ?></span>
                                </label>
                            </div>
                        </div>
                        
                    </div>
                </div>
                
                <!-- PLACEHOLDER: Settings Card 3 -->
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h3><?php esc_html_e('Advanced Settings', 'shahi-template'); ?></h3>
                    </div>
                    <div class="shahi-card-body">
                        
                        <!-- PLACEHOLDER: Number Range -->
                        <div class="shahi-form-field">
                            <label for="limit" class="shahi-label">
                                <?php esc_html_e('Limit', 'shahi-template'); ?>
                            </label>
                            <input 
                                type="range" 
                                id="limit" 
                                name="settings[limit]" 
                                min="0" 
                                max="100" 
                                value="<?php echo esc_attr($settings['limit'] ?? 50); ?>"
                                class="shahi-range"
                            >
                            <output for="limit" class="shahi-range-output">
                                <?php echo esc_html($settings['limit'] ?? 50); ?>
                            </output>
                            <p class="shahi-field-description">
                                <?php esc_html_e('PLACEHOLDER: Adjust the limit value', 'shahi-template'); ?>
                            </p>
                        </div>
                        
                        <!-- PLACEHOLDER: Code Editor -->
                        <div class="shahi-form-field">
                            <label for="custom_code" class="shahi-label">
                                <?php esc_html_e('Custom Code', 'shahi-template'); ?>
                            </label>
                            <textarea 
                                id="custom_code" 
                                name="settings[custom_code]" 
                                rows="8" 
                                class="shahi-code-editor"
                            ><?php echo esc_textarea($settings['custom_code'] ?? ''); ?></textarea>
                            <p class="shahi-field-description">
                                <?php esc_html_e('PLACEHOLDER: Add custom code here', 'shahi-template'); ?>
                            </p>
                        </div>
                        
                    </div>
                </div>
                
            </div>
            
            <!-- PLACEHOLDER: Module Statistics (optional) -->
            <div class="shahi-card shahi-stats-card">
                <div class="shahi-card-header">
                    <h3><?php esc_html_e('Module Statistics', 'shahi-template'); ?></h3>
                </div>
                <div class="shahi-card-body">
                    <div class="shahi-stats-grid">
                        
                        <div class="shahi-stat-item">
                            <div class="shahi-stat-value">
                                <?php echo esc_html($module->get_stat('total_items', 0)); ?>
                            </div>
                            <div class="shahi-stat-label">
                                <?php esc_html_e('PLACEHOLDER: Total Items', 'shahi-template'); ?>
                            </div>
                        </div>
                        
                        <div class="shahi-stat-item">
                            <div class="shahi-stat-value">
                                <?php echo esc_html($module->get_stat('active_items', 0)); ?>
                            </div>
                            <div class="shahi-stat-label">
                                <?php esc_html_e('PLACEHOLDER: Active Items', 'shahi-template'); ?>
                            </div>
                        </div>
                        
                        <div class="shahi-stat-item">
                            <div class="shahi-stat-value">
                                <?php echo esc_html($module->get_stat('last_update', 'Never')); ?>
                            </div>
                            <div class="shahi-stat-label">
                                <?php esc_html_e('PLACEHOLDER: Last Update', 'shahi-template'); ?>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
            
            <!-- PLACEHOLDER: Form Actions -->
            <div class="shahi-form-actions">
                <?php submit_button(__('Save Module Settings', 'shahi-template'), 'primary large', 'submit', false); ?>
                <button type="button" class="button button-secondary" id="reset-to-defaults">
                    <?php esc_html_e('Reset to Defaults', 'shahi-template'); ?>
                </button>
            </div>
            
        </form>
        
    </div>
    
</div>

<!-- PLACEHOLDER: Inline JavaScript -->
<script>
jQuery(document).ready(function($) {
    // Range input value display
    $('input[type="range"]').on('input', function() {
        $(this).next('output').text($(this).val());
    });
    
    // Module enable/disable
    $('#module-enabled').on('change', function() {
        var isEnabled = $(this).is(':checked');
        // PLACEHOLDER: Add AJAX call to enable/disable module
        console.log('Module enabled:', isEnabled);
    });
    
    // Reset to defaults
    $('#reset-to-defaults').on('click', function() {
        if (confirm('<?php esc_html_e('Are you sure you want to reset all settings to defaults?', 'shahi-template'); ?>')) {
            // PLACEHOLDER: Add reset logic
            console.log('Reset to defaults');
        }
    });
    
    // PLACEHOLDER: Add your custom JavaScript here
});
</script>
