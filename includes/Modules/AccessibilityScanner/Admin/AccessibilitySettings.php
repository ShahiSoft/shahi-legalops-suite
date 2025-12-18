<?php
namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Admin;

use ShahiLegalopsSuite\Core\Security;

if (!defined('ABSPATH')) {
    exit;
}

class AccessibilitySettings {
    
    private $security;

    public function __construct() {
        $this->security = new Security();
    }

    public function init() {
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings() {
        register_setting('slos_accessibility_settings', 'slos_active_checkers');
        register_setting('slos_accessibility_settings', 'slos_active_fixes');
    }

    public function render() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'shahi-legalops-suite'));
        }

        // Get available checkers and fixes
        $checkers = $this->get_available_checkers();
        $fixes = $this->get_available_fixes();
        
        $active_checkers = get_option('slos_active_checkers', []);
        $active_fixes = get_option('slos_active_fixes', []);

        include SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/accessibility-settings.php';
    }

    private function get_available_checkers() {
        // This should ideally come from the ScannerEngine, but hardcoding for now based on implementation
        return [
            'missing-alt-text' => 'Missing Alt Text',
            'empty-alt-text' => 'Empty Alt Text',
            'missing-h1' => 'Missing H1 Heading',
            'skipped-heading' => 'Skipped Heading Levels',
            'empty-link' => 'Empty Links',
            'generic-link' => 'Generic Link Text',
            'missing-label' => 'Missing Form Labels',
            'redundant-alt' => 'Redundant Alt Text',
            'empty-heading' => 'Empty Headings',
            'new-window' => 'New Window Links',
            'positive-tabindex' => 'Positive TabIndex',
            'image-map' => 'Image Map Alt Text',
            'iframe-title' => 'Iframe Titles',
            'button-label' => 'Button Labels',
            'table-header' => 'Table Headers',
            'alt-quality' => 'Alt Text Quality',
            'decorative-image' => 'Decorative Images',
            'complex-image' => 'Complex Images',
            'svg-access' => 'SVG Accessibility',
            'bg-image' => 'Background Images',
            'logo-image' => 'Logo Images',
            'multiple-h1' => 'Multiple H1 Headings',
            'heading-visual' => 'Visual Headings',
            'heading-length' => 'Heading Length',
            'heading-unique' => 'Unique Headings',
            'heading-nesting' => 'Heading Nesting',
            'fieldset-legend' => 'Fieldset Legends',
            'autocomplete' => 'Autocomplete Attributes',
            'input-type' => 'Input Types',
            'placeholder-label' => 'Placeholder as Label',
            'custom-control' => 'Custom Controls',
            'orphaned-label' => 'Orphaned Labels',
            'required-attr' => 'Required Attributes',
            'error-message' => 'Error Messages',
            'form-aria' => 'Form ARIA',
            'link-dest' => 'Link Destinations',
            'skip-link' => 'Skip Links',
            'download-link' => 'Download Links',
            'external-link' => 'External Links',
            'contrast' => 'Color Contrast',
            'focus-indicator' => 'Focus Indicators',
            'color-reliance' => 'Color Reliance',
            'complex-contrast' => 'Complex Contrast',
            'keyboard-trap' => 'Keyboard Traps',
            'focus-order' => 'Focus Order',
            'interactive-element' => 'Interactive Elements',
            'modal-access' => 'Modal Accessibility',
            'widget-keyboard' => 'Widget Keyboard Access',
            'aria-role' => 'ARIA Roles',
            'aria-attr' => 'ARIA Attributes',
            'landmark-role' => 'Landmark Roles',
            'redundant-aria' => 'Redundant ARIA',
            'hidden-content' => 'Hidden Content',
            'semantic-html' => 'Semantic HTML',
            'live-region' => 'Live Regions',
            'aria-state' => 'ARIA States',
            'invalid-aria' => 'Invalid ARIA Combinations',
            'page-structure' => 'Page Structure',
            'video-access' => 'Video Accessibility',
            'audio-access' => 'Audio Accessibility',
            'media-alt' => 'Media Alternatives',
            'table-caption' => 'Table Captions',
            'complex-table' => 'Complex Tables',
            'layout-table' => 'Layout Tables',
            'empty-cell' => 'Empty Table Cells',
            'viewport' => 'Viewport Configuration',
            'touch-target' => 'Touch Targets',
            'touch-gesture' => 'Touch Gestures',
        ];
    }

    private function get_available_fixes() {
        return [
            'add_skip_links' => 'Add Skip Links',
            'fix_focus_outlines' => 'Fix Focus Outlines',
            'fix_link_underlines' => 'Force Link Underlines',
            'block_new_window' => 'Block New Window Links',
            'fix_language_attributes' => 'Add Language Attributes',
            'fix_viewport_meta' => 'Fix Viewport Meta',
            'label_search_fields' => 'Label Search Fields',
            'label_comment_fields' => 'Label Comment Fields',
            'add_page_titles' => 'Add Page Titles',
            'fix_tab_index' => 'Fix Tab Index',
            'remove_title_attributes' => 'Remove Title Attributes',
            'add_alt_placeholders' => 'Add Alt Text Placeholders',
            'add_aria_landmarks' => 'Add ARIA Landmarks',
            'fix_empty_links' => 'Fix Empty Links',
            'add_heading_structure' => 'Add Heading Structure',
            'add_table_headers' => 'Add Table Headers',
            'add_form_labels' => 'Add Form Labels',
            'fix_color_contrast' => 'Fix Color Contrast',
            'fix_link_warnings' => 'Add Link Warnings',
            'fix_image_maps' => 'Fix Image Maps',
            'add_button_labels' => 'Add Button Labels',
            'fix_list_semantics' => 'Fix List Semantics',
            'add_live_regions' => 'Add Live Regions',
            'fix_modal_dialogs' => 'Fix Modal Dialogs',
            'generate_transcripts' => 'Generate Transcripts',
        ];
    }
}
