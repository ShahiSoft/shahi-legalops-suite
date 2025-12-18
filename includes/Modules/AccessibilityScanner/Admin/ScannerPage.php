<?php
namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class ScannerPage {
    
    /**
     * Page Hook Suffix
     * @var string
     */
    private $page_hook;

    /**
     * Initialize the page
     */
    public function init() {
        add_action('admin_menu', [$this, 'register_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('slos_scanner_fixes', 'slos_active_fixes');
    }

    /**
     * Register the admin menu page
     */
    public function register_page() {
        $this->page_hook = add_submenu_page(
            'shahi-legalops-suite',
            'Accessibility Scanner',
            'Accessibility Scanner',
            'manage_options',
            'slos-accessibility-scanner',
            [$this, 'render_page']
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_assets($hook) {
        // If page hook is not set yet, try to guess it or return
        if (!$this->page_hook && $hook !== 'shahi-legalops-suite_page_slos-accessibility-scanner') {
            return;
        }
        
        // If page hook is set, check against it
        if ($this->page_hook && $hook !== $this->page_hook) {
            return;
        }

        wp_enqueue_script(
            'slos-scanner-admin',
            SHAHI_LEGALOPS_SUITE_PLUGIN_URL . 'assets/js/slos-scanner-admin.js',
            ['jquery'],
            SHAHI_LEGALOPS_SUITE_VERSION,
            true
        );

        wp_localize_script('slos-scanner-admin', 'slosScanner', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('slos_scanner_nonce')
        ]);
        
        wp_enqueue_style(
            'slos-scanner-admin',
            SHAHI_LEGALOPS_SUITE_PLUGIN_URL . 'assets/css/slos-scanner-admin.css',
            [],
            SHAHI_LEGALOPS_SUITE_VERSION
        );
    }

    /**
     * Render the page content
     */
    public function render_page() {
        ?>
        <div class="wrap">
            <h1>Accessibility Scanner Dashboard</h1>
            
            <div class="slos-scanner-card">
                <h2>Bulk Scanner</h2>
                <p>Scan all your posts and pages for accessibility issues.</p>
                
                <div class="slos-scanner-controls">
                    <button id="slos-start-scan" class="button button-primary button-large">Start Full Scan</button>
                    <span id="slos-scan-status" style="margin-left: 10px; display: none;">Scanning... <span id="slos-scan-progress">0</span>%</span>
                </div>
                
                <div id="slos-progress-bar-wrapper" style="display: none; margin-top: 15px; background: #f0f0f1; height: 20px; border-radius: 10px; overflow: hidden;">
                    <div id="slos-progress-bar" style="width: 0%; height: 100%; background: #2271b1; transition: width 0.3s;"></div>
                </div>
            </div>

            <div class="slos-scanner-results-card" style="margin-top: 20px;">
                <h2>Scan Results</h2>
                <div id="slos-scan-results">
                    <p>No scan results yet. Click "Start Full Scan" to begin.</p>
                </div>
            </div>

            <div class="slos-scanner-card" style="margin-top: 20px;">
                <h2>Automated Fixes Configuration</h2>
                <p>Select the automated fixes you want to apply to your site.</p>
                <form method="post" action="options.php">
                    <?php settings_fields('slos_scanner_fixes'); ?>
                    <?php $active_fixes = get_option('slos_active_fixes', []); ?>
                    
                    <div class="slos-fixes-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px; margin-bottom: 20px;">
                        <?php
                        $fixes = [
                            'skip_links' => 'Add Skip Links',
                            'fix_focus_outlines' => 'Add Focus Outlines',
                            'fix_link_underlines' => 'Force Link Underlines',
                            'block_new_window' => 'Block New Window Links',
                            'add_lang_attr' => 'Add Language Attributes',
                            'scalable_viewport' => 'Make Viewport Scalable',
                            'label_search' => 'Label Search Fields',
                            'label_comments' => 'Label Comment Fields',
                            'add_page_titles' => 'Add Page Titles',
                            'fix_tab_index' => 'Fix Tab Index',
                            'remove_title_attrs' => 'Remove Title Attributes',
                            'add_alt_placeholders' => 'Add Alt Text Placeholders',
                            'add_landmarks' => 'Add ARIA Landmarks',
                            'fix_empty_links' => 'Fix Empty Links',
                            'add_heading_structure' => 'Add Heading Structure',
                            'add_table_headers' => 'Add Table Headers',
                            'add_form_labels' => 'Add Form Labels',
                            'fix_color_contrast' => 'Fix Color Contrast (Enforce)',
                            'fix_link_warnings' => 'Add Link Warnings',
                            'fix_image_maps' => 'Fix Image Maps',
                            'add_button_labels' => 'Add Button Labels',
                            'fix_list_semantics' => 'Fix List Semantics',
                            'add_live_regions' => 'Add Live Regions',
                            'fix_modal_dialogs' => 'Fix Modal Dialogs',
                            'generate_transcripts' => 'Generate Transcripts (Structure)'
                        ];
                        
                        foreach ($fixes as $key => $label) {
                            $checked = in_array($key, $active_fixes) ? 'checked' : '';
                            echo "<label><input type='checkbox' name='slos_active_fixes[]' value='$key' $checked> $label</label>";
                        }
                        ?>
                    </div>
                    
                    <?php submit_button(); ?>
                </form>
            </div>

            <div class="slos-scanner-card" style="margin-top: 20px;">
                <h2>Export Reports</h2>
                <p>Download accessibility compliance reports.</p>
                <div class="slos-export-controls">
                    <button class="button button-secondary slos-export-btn" data-format="csv">Export CSV</button>
                    <button class="button button-secondary slos-export-btn" data-format="json">Export JSON</button>
                    <button class="button button-secondary slos-export-btn" data-format="html">Export HTML (Printable)</button>
                </div>
            </div>

            <div class="slos-scanner-card" style="margin-top: 20px;">
                <h2>Compliance Tools</h2>
                <p>Generate an accessibility statement for your website.</p>
                <button id="slos-generate-statement" class="button button-secondary">Generate Accessibility Statement</button>
                <div id="slos-statement-result" style="margin-top: 10px;"></div>
            </div>
        </div>
        <?php
    }
}
