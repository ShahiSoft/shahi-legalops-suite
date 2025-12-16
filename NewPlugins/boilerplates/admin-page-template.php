<?php
/**
 * Admin Page HTML Template
 * 
 * PLACEHOLDER FILE - This is a reusable HTML template for admin pages.
 * Copy this file to includes/admin/pages/templates/ and customize it.
 * 
 * Instructions:
 * 1. Copy this file to: includes/admin/pages/templates/{page-slug}.php
 * 2. Replace all PLACEHOLDER values with your actual page content
 * 3. This file is loaded by your Admin Page class
 * 4. Variables available: $options (page settings), $this (page class instance)
 * 
 * @package    {PluginNamespace}
 * @subpackage Admin\Pages\Templates
 * @since      1.0.0
 */

// PLACEHOLDER: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap shahi-admin-page">
    
    <!-- PLACEHOLDER: Page Header -->
    <h1 class="shahi-admin-title">
        <span class="shahi-icon">⚙️</span> <!-- PLACEHOLDER: Change icon -->
        <?php echo esc_html(get_admin_page_title()); ?>
    </h1>
    
    <!-- PLACEHOLDER: Admin Notices -->
    <?php settings_errors(); ?>
    
    <?php if (isset($_GET['message'])): ?>
        <?php $message_type = sanitize_text_field($_GET['message']); ?>
        
        <?php if ($message_type === 'saved'): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Settings saved successfully!', 'shahi-template'); ?></p>
            </div>
        <?php elseif ($message_type === 'error'): ?>
            <div class="notice notice-error is-dismissible">
                <p><?php esc_html_e('An error occurred. Please try again.', 'shahi-template'); ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- PLACEHOLDER: Page Content -->
    <div class="shahi-admin-content">
        
        <!-- PLACEHOLDER: Sidebar (optional) -->
        <div class="shahi-admin-sidebar">
            <div class="shahi-card">
                <div class="shahi-card-header">
                    <h3><?php esc_html_e('Quick Info', 'shahi-template'); ?></h3>
                </div>
                <div class="shahi-card-body">
                    <p>
                        <?php esc_html_e('PLACEHOLDER: Add helpful information or quick links here.', 'shahi-template'); ?>
                    </p>
                    
                    <ul class="shahi-quick-links">
                        <li>
                            <a href="#" target="_blank">
                                <?php esc_html_e('Documentation', 'shahi-template'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" target="_blank">
                                <?php esc_html_e('Support', 'shahi-template'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- PLACEHOLDER: Main Content Area -->
        <div class="shahi-admin-main">
            
            <!-- PLACEHOLDER: Tab Navigation (if using tabs) -->
            <div class="shahi-tabs">
                <nav class="nav-tab-wrapper">
                    <a href="#tab-general" class="nav-tab nav-tab-active" data-tab="general">
                        <?php esc_html_e('General', 'shahi-template'); ?>
                    </a>
                    <a href="#tab-advanced" class="nav-tab" data-tab="advanced">
                        <?php esc_html_e('Advanced', 'shahi-template'); ?>
                    </a>
                    <a href="#tab-tools" class="nav-tab" data-tab="tools">
                        <?php esc_html_e('Tools', 'shahi-template'); ?>
                    </a>
                </nav>
            </div>
            
            <!-- PLACEHOLDER: Form -->
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="shahi-admin-form">
                
                <?php wp_nonce_field('save_page_slug'); // PLACEHOLDER: Replace 'save_page_slug' ?>
                <input type="hidden" name="action" value="save_page_slug"> <!-- PLACEHOLDER: Replace action name -->
                
                <!-- PLACEHOLDER: Tab 1 - General Settings -->
                <div id="tab-general" class="shahi-tab-content active">
                    <div class="shahi-card">
                        <div class="shahi-card-header">
                            <h2><?php esc_html_e('General Settings', 'shahi-template'); ?></h2>
                        </div>
                        
                        <div class="shahi-card-body">
                            <table class="form-table" role="presentation">
                                <tbody>
                                    
                                    <!-- PLACEHOLDER: Text Field -->
                                    <tr>
                                        <th scope="row">
                                            <label for="text_field">
                                                <?php esc_html_e('Text Field', 'shahi-template'); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input 
                                                type="text" 
                                                id="text_field" 
                                                name="options[text_field]" 
                                                value="<?php echo esc_attr($options['text_field'] ?? ''); ?>"
                                                class="regular-text"
                                            >
                                            <p class="description">
                                                <?php esc_html_e('PLACEHOLDER: Add field description here', 'shahi-template'); ?>
                                            </p>
                                        </td>
                                    </tr>
                                    
                                    <!-- PLACEHOLDER: Checkbox Field -->
                                    <tr>
                                        <th scope="row">
                                            <?php esc_html_e('Checkbox Field', 'shahi-template'); ?>
                                        </th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input 
                                                        type="checkbox" 
                                                        name="options[checkbox_field]" 
                                                        value="1"
                                                        <?php checked($options['checkbox_field'] ?? false, true); ?>
                                                    >
                                                    <?php esc_html_e('Enable this feature', 'shahi-template'); ?>
                                                </label>
                                                <p class="description">
                                                    <?php esc_html_e('PLACEHOLDER: Checkbox description', 'shahi-template'); ?>
                                                </p>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    
                                    <!-- PLACEHOLDER: Select Field -->
                                    <tr>
                                        <th scope="row">
                                            <label for="select_field">
                                                <?php esc_html_e('Select Field', 'shahi-template'); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <select id="select_field" name="options[select_field]">
                                                <option value="option1" <?php selected($options['select_field'] ?? '', 'option1'); ?>>
                                                    <?php esc_html_e('Option 1', 'shahi-template'); ?>
                                                </option>
                                                <option value="option2" <?php selected($options['select_field'] ?? '', 'option2'); ?>>
                                                    <?php esc_html_e('Option 2', 'shahi-template'); ?>
                                                </option>
                                                <option value="option3" <?php selected($options['select_field'] ?? '', 'option3'); ?>>
                                                    <?php esc_html_e('Option 3', 'shahi-template'); ?>
                                                </option>
                                            </select>
                                            <p class="description">
                                                <?php esc_html_e('PLACEHOLDER: Select field description', 'shahi-template'); ?>
                                            </p>
                                        </td>
                                    </tr>
                                    
                                    <!-- PLACEHOLDER: Textarea Field -->
                                    <tr>
                                        <th scope="row">
                                            <label for="textarea_field">
                                                <?php esc_html_e('Textarea Field', 'shahi-template'); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <textarea 
                                                id="textarea_field" 
                                                name="options[textarea_field]" 
                                                rows="5" 
                                                class="large-text"
                                            ><?php echo esc_textarea($options['textarea_field'] ?? ''); ?></textarea>
                                            <p class="description">
                                                <?php esc_html_e('PLACEHOLDER: Textarea description', 'shahi-template'); ?>
                                            </p>
                                        </td>
                                    </tr>
                                    
                                    <!-- PLACEHOLDER: Number Field -->
                                    <tr>
                                        <th scope="row">
                                            <label for="number_field">
                                                <?php esc_html_e('Number Field', 'shahi-template'); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input 
                                                type="number" 
                                                id="number_field" 
                                                name="options[number_field]" 
                                                value="<?php echo esc_attr($options['number_field'] ?? '10'); ?>"
                                                min="1"
                                                max="100"
                                                class="small-text"
                                            >
                                            <p class="description">
                                                <?php esc_html_e('PLACEHOLDER: Number field description', 'shahi-template'); ?>
                                            </p>
                                        </td>
                                    </tr>
                                    
                                    <!-- PLACEHOLDER: Color Picker Field -->
                                    <tr>
                                        <th scope="row">
                                            <label for="color_field">
                                                <?php esc_html_e('Color Field', 'shahi-template'); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input 
                                                type="text" 
                                                id="color_field" 
                                                name="options[color_field]" 
                                                value="<?php echo esc_attr($options['color_field'] ?? '#00d4ff'); ?>"
                                                class="color-picker"
                                                data-default-color="#00d4ff"
                                            >
                                            <p class="description">
                                                <?php esc_html_e('PLACEHOLDER: Choose a color', 'shahi-template'); ?>
                                            </p>
                                        </td>
                                    </tr>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- PLACEHOLDER: Tab 2 - Advanced Settings -->
                <div id="tab-advanced" class="shahi-tab-content">
                    <div class="shahi-card">
                        <div class="shahi-card-header">
                            <h2><?php esc_html_e('Advanced Settings', 'shahi-template'); ?></h2>
                        </div>
                        
                        <div class="shahi-card-body">
                            <table class="form-table" role="presentation">
                                <tbody>
                                    
                                    <!-- PLACEHOLDER: Add advanced settings here -->
                                    <tr>
                                        <th scope="row">
                                            <?php esc_html_e('Advanced Option', 'shahi-template'); ?>
                                        </th>
                                        <td>
                                            <p class="description">
                                                <?php esc_html_e('PLACEHOLDER: Add advanced options here', 'shahi-template'); ?>
                                            </p>
                                        </td>
                                    </tr>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- PLACEHOLDER: Tab 3 - Tools -->
                <div id="tab-tools" class="shahi-tab-content">
                    <div class="shahi-card">
                        <div class="shahi-card-header">
                            <h2><?php esc_html_e('Tools & Utilities', 'shahi-template'); ?></h2>
                        </div>
                        
                        <div class="shahi-card-body">
                            <p>
                                <?php esc_html_e('PLACEHOLDER: Add tools and utility functions here', 'shahi-template'); ?>
                            </p>
                            
                            <button type="button" class="button" id="tool-action-btn">
                                <?php esc_html_e('Run Tool', 'shahi-template'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- PLACEHOLDER: Form Actions -->
                <div class="shahi-form-actions">
                    <?php submit_button(__('Save Changes', 'shahi-template'), 'primary', 'submit', false); ?>
                    <button type="reset" class="button">
                        <?php esc_html_e('Reset to Defaults', 'shahi-template'); ?>
                    </button>
                </div>
                
            </form>
            
        </div>
        
    </div>
    
</div>

<!-- PLACEHOLDER: Inline JavaScript for tab switching -->
<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        var tab = $(this).data('tab');
        
        // Update active tab
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Show corresponding content
        $('.shahi-tab-content').removeClass('active');
        $('#tab-' + tab).addClass('active');
    });
    
    // Color picker
    if ($.fn.wpColorPicker) {
        $('.color-picker').wpColorPicker();
    }
    
    // PLACEHOLDER: Add your custom JavaScript here
});
</script>
