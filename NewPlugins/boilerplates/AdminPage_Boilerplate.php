<?php
/**
 * Admin Page Boilerplate Template
 * 
 * PLACEHOLDER FILE - This is a template for creating new admin pages.
 * Copy this file to includes/admin/pages/ and customize it.
 * 
 * Instructions:
 * 1. Copy this file to: includes/admin/pages/{PageName}_Page.php
 * 2. Replace all PLACEHOLDER values with your actual page information
 * 3. Replace {PluginNamespace} with your actual namespace (e.g., ShahiTemplate)
 * 4. Replace {PageName} with your page name in PascalCase (e.g., Analytics_Dashboard)
 * 5. Replace {page-slug} with your page slug (e.g., analytics-dashboard)
 * 6. Create corresponding template file in includes/admin/pages/templates/
 * 7. Implement the required methods and add your custom logic
 * 
 * @package    {PluginNamespace}
 * @subpackage Admin\Pages
 * @since      1.0.0
 */

namespace {PluginNamespace}\Admin\Pages;

// PLACEHOLDER: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * {PageName} Admin Page Class
 * 
 * PLACEHOLDER DESCRIPTION: Add your admin page description here.
 * Explain what this page does and its purpose.
 * 
 * Features:
 * - Feature 1
 * - Feature 2
 * - Feature 3
 * 
 * @since 1.0.0
 */
class {PageName}_Page {
    
    /**
     * Page slug
     * 
     * PLACEHOLDER: Replace with your page's unique slug
     * 
     * @var string
     */
    private $page_slug = '{page-slug}';
    
    /**
     * Parent page slug
     * 
     * PLACEHOLDER: Replace with parent menu slug or leave empty for top-level menu
     * Examples: 'shahi-template', 'tools.php', 'options-general.php'
     * 
     * @var string
     */
    private $parent_slug = 'shahi-template';
    
    /**
     * Page title
     * 
     * PLACEHOLDER: Replace with your page's title (shown in browser title bar)
     * 
     * @var string
     */
    private $page_title = '{Page Title}';
    
    /**
     * Menu title
     * 
     * PLACEHOLDER: Replace with menu item text (shown in admin menu)
     * 
     * @var string
     */
    private $menu_title = '{Menu Title}';
    
    /**
     * Required capability
     * 
     * PLACEHOLDER: Set the capability required to access this page
     * Common values: 'manage_options', 'edit_posts', 'read'
     * 
     * @var string
     */
    private $capability = 'manage_options';
    
    /**
     * Menu icon (for top-level menus only)
     * 
     * PLACEHOLDER: Replace with dashicons class or image URL
     * Example: 'dashicons-chart-bar', 'dashicons-admin-generic'
     * 
     * @var string
     */
    private $icon = 'dashicons-admin-generic';
    
    /**
     * Menu position (for top-level menus only)
     * 
     * @var int
     */
    private $position = 6;
    
    /**
     * Nonce action name
     * 
     * @var string
     */
    private $nonce_action;
    
    /**
     * Constructor
     * 
     * Initialize the admin page and register hooks
     * 
     * @since 1.0.0
     */
    public function __construct() {
        $this->nonce_action = "save_{$this->page_slug}";
        $this->register_hooks();
    }
    
    /**
     * Register WordPress hooks
     * 
     * @since 1.0.0
     * @return void
     */
    private function register_hooks() {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_post_' . $this->nonce_action, [$this, 'handle_form_submission']);
        
        // AJAX handlers
        add_action('wp_ajax_' . $this->page_slug . '_action', [$this, 'handle_ajax_request']);
    }
    
    /**
     * Add menu page to WordPress admin
     * 
     * PLACEHOLDER: Customize this method based on your menu structure
     * 
     * @since 1.0.0
     * @return void
     */
    public function add_menu_page() {
        // For submenu page (most common)
        if (!empty($this->parent_slug)) {
            add_submenu_page(
                $this->parent_slug,
                $this->page_title,
                $this->menu_title,
                $this->capability,
                $this->page_slug,
                [$this, 'render_page']
            );
        } else {
            // For top-level menu page
            add_menu_page(
                $this->page_title,
                $this->menu_title,
                $this->capability,
                $this->page_slug,
                [$this, 'render_page'],
                $this->icon,
                $this->position
            );
        }
    }
    
    /**
     * Register settings
     * 
     * PLACEHOLDER: Register your settings sections and fields here
     * 
     * @since 1.0.0
     * @return void
     */
    public function register_settings() {
        $option_group = $this->page_slug . '_group';
        $option_name = $this->page_slug . '_options';
        
        // Register setting
        register_setting(
            $option_group,
            $option_name,
            [$this, 'sanitize_options']
        );
        
        // PLACEHOLDER: Add settings sections
        /*
        add_settings_section(
            'general_section',
            __('General Settings', 'shahi-template'),
            [$this, 'render_section_general'],
            $this->page_slug
        );
        */
        
        // PLACEHOLDER: Add settings fields
        /*
        add_settings_field(
            'example_field',
            __('Example Field', 'shahi-template'),
            [$this, 'render_field_example'],
            $this->page_slug,
            'general_section'
        );
        */
    }
    
    /**
     * Enqueue page assets
     * 
     * PLACEHOLDER: Enqueue your CSS and JavaScript files
     * 
     * @since 1.0.0
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueue_assets($hook) {
        // Only load on this page
        if (!$this->is_current_page($hook)) {
            return;
        }
        
        // PLACEHOLDER: Enqueue CSS
        /*
        wp_enqueue_style(
            $this->page_slug . '-admin',
            plugin_dir_url(__FILE__) . '../../assets/css/admin-' . $this->page_slug . '.css',
            [],
            '1.0.0'
        );
        */
        
        // PLACEHOLDER: Enqueue JavaScript
        /*
        wp_enqueue_script(
            $this->page_slug . '-admin',
            plugin_dir_url(__FILE__) . '../../assets/js/admin-' . $this->page_slug . '.js',
            ['jquery', 'wp-api'],
            '1.0.0',
            true
        );
        
        // Localize script with data
        wp_localize_script($this->page_slug . '-admin', 'PageData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce($this->page_slug . '_nonce'),
            'pageSlug' => $this->page_slug,
            'settings' => $this->get_options(),
        ]);
        */
        
        // WordPress core assets
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_media();
    }
    
    /**
     * Render the admin page
     * 
     * PLACEHOLDER: This method loads your page template
     * 
     * @since 1.0.0
     * @return void
     */
    public function render_page() {
        // Check permissions
        if (!current_user_can($this->capability)) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'shahi-template'));
        }
        
        // Get current options
        $options = $this->get_options();
        
        // PLACEHOLDER: Include your template file
        $template_path = __DIR__ . '/templates/' . $this->page_slug . '.php';
        
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            // Fallback inline template
            $this->render_default_template($options);
        }
    }
    
    /**
     * Render default template (fallback)
     * 
     * PLACEHOLDER: This is a basic template shown if the template file doesn't exist
     * 
     * @since 1.0.0
     * @param array $options Current options
     * @return void
     */
    private function render_default_template($options) {
        ?>
        <div class="wrap shahi-admin-page">
            <h1 class="shahi-admin-title">
                <span class="shahi-icon">⚙️</span>
                <?php echo esc_html($this->page_title); ?>
            </h1>
            
            <!-- PLACEHOLDER: Success/Error Messages -->
            <?php $this->render_admin_notices(); ?>
            
            <div class="shahi-admin-content">
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h2><?php esc_html_e('Settings', 'shahi-template'); ?></h2>
                    </div>
                    
                    <div class="shahi-card-body">
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <?php wp_nonce_field($this->nonce_action); ?>
                            <input type="hidden" name="action" value="<?php echo esc_attr($this->nonce_action); ?>">
                            
                            <!-- PLACEHOLDER: Add your form fields here -->
                            <table class="form-table" role="presentation">
                                <tbody>
                                    <tr>
                                        <th scope="row">
                                            <label for="example_field">
                                                <?php esc_html_e('Example Field', 'shahi-template'); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input 
                                                type="text" 
                                                id="example_field" 
                                                name="options[example_field]" 
                                                value="<?php echo esc_attr($options['example_field'] ?? ''); ?>"
                                                class="regular-text"
                                            >
                                            <p class="description">
                                                <?php esc_html_e('PLACEHOLDER: Add field description here', 'shahi-template'); ?>
                                            </p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <div class="shahi-form-actions">
                                <?php submit_button(__('Save Settings', 'shahi-template'), 'primary', 'submit', false); ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render admin notices
     * 
     * @since 1.0.0
     * @return void
     */
    private function render_admin_notices() {
        if (isset($_GET['message'])) {
            $message_type = sanitize_text_field($_GET['message']);
            
            switch ($message_type) {
                case 'saved':
                    ?>
                    <div class="notice notice-success is-dismissible">
                        <p><?php esc_html_e('Settings saved successfully!', 'shahi-template'); ?></p>
                    </div>
                    <?php
                    break;
                    
                case 'error':
                    ?>
                    <div class="notice notice-error is-dismissible">
                        <p><?php esc_html_e('An error occurred. Please try again.', 'shahi-template'); ?></p>
                    </div>
                    <?php
                    break;
            }
        }
    }
    
    /**
     * Handle form submission
     * 
     * PLACEHOLDER: Add your form processing logic here
     * 
     * @since 1.0.0
     * @return void
     */
    public function handle_form_submission() {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], $this->nonce_action)) {
            wp_die(__('Security check failed', 'shahi-template'));
        }
        
        // Check permissions
        if (!current_user_can($this->capability)) {
            wp_die(__('You do not have sufficient permissions', 'shahi-template'));
        }
        
        // PLACEHOLDER: Process form data
        $options = isset($_POST['options']) ? $_POST['options'] : [];
        
        // Sanitize and save
        $sanitized_options = $this->sanitize_options($options);
        $this->update_options($sanitized_options);
        
        // PLACEHOLDER: Add any additional processing here
        // Example: Clear caches, trigger hooks, etc.
        do_action($this->page_slug . '_settings_saved', $sanitized_options);
        
        // Redirect back with success message
        wp_redirect(add_query_arg([
            'page' => $this->page_slug,
            'message' => 'saved'
        ], admin_url('admin.php')));
        exit;
    }
    
    /**
     * Handle AJAX request
     * 
     * PLACEHOLDER: Add your AJAX handling logic here
     * 
     * @since 1.0.0
     * @return void
     */
    public function handle_ajax_request() {
        // Verify nonce
        check_ajax_referer($this->page_slug . '_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can($this->capability)) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'shahi-template')]);
        }
        
        // PLACEHOLDER: Process AJAX request
        $action_type = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';
        
        switch ($action_type) {
            case 'example_action':
                // Process action
                $result = $this->process_example_action($_POST);
                wp_send_json_success($result);
                break;
                
            default:
                wp_send_json_error(['message' => __('Invalid action', 'shahi-template')]);
        }
    }
    
    /**
     * Process example AJAX action
     * 
     * PLACEHOLDER: Replace with your actual AJAX action logic
     * 
     * @since 1.0.0
     * @param array $data POST data
     * @return array Result data
     */
    private function process_example_action($data) {
        // PLACEHOLDER: Add your processing logic
        return [
            'message' => __('Action processed successfully', 'shahi-template'),
            'data' => []
        ];
    }
    
    /**
     * Sanitize options
     * 
     * PLACEHOLDER: Add your sanitization logic for all fields
     * 
     * @since 1.0.0
     * @param array $options Raw options
     * @return array Sanitized options
     */
    public function sanitize_options($options) {
        $sanitized = [];
        
        // PLACEHOLDER: Sanitize each field
        // Example:
        // $sanitized['example_field'] = isset($options['example_field']) 
        //     ? sanitize_text_field($options['example_field']) 
        //     : '';
        
        // $sanitized['checkbox_field'] = isset($options['checkbox_field']) 
        //     ? (bool) $options['checkbox_field'] 
        //     : false;
        
        // $sanitized['email_field'] = isset($options['email_field']) 
        //     ? sanitize_email($options['email_field']) 
        //     : '';
        
        // $sanitized['url_field'] = isset($options['url_field']) 
        //     ? esc_url_raw($options['url_field']) 
        //     : '';
        
        // $sanitized['textarea_field'] = isset($options['textarea_field']) 
        //     ? sanitize_textarea_field($options['textarea_field']) 
        //     : '';
        
        return $sanitized;
    }
    
    /**
     * Get page options
     * 
     * @since 1.0.0
     * @return array Current options
     */
    private function get_options() {
        $defaults = $this->get_default_options();
        $saved = get_option($this->page_slug . '_options', []);
        
        return wp_parse_args($saved, $defaults);
    }
    
    /**
     * Get default options
     * 
     * PLACEHOLDER: Define your default option values
     * 
     * @since 1.0.0
     * @return array Default options
     */
    private function get_default_options() {
        return [
            // PLACEHOLDER: Add your default values
            // 'example_field' => '',
            // 'checkbox_field' => false,
            // 'select_field' => 'option1',
        ];
    }
    
    /**
     * Update options
     * 
     * @since 1.0.0
     * @param array $options New options
     * @return bool True on success, false on failure
     */
    private function update_options($options) {
        return update_option($this->page_slug . '_options', $options);
    }
    
    /**
     * Check if current page is this admin page
     * 
     * @since 1.0.0
     * @param string $hook Current admin page hook
     * @return bool
     */
    private function is_current_page($hook) {
        return strpos($hook, $this->page_slug) !== false;
    }
    
    /**
     * Render settings section
     * 
     * PLACEHOLDER: Add section descriptions
     * 
     * @since 1.0.0
     * @return void
     */
    public function render_section_general() {
        ?>
        <p><?php esc_html_e('PLACEHOLDER: Configure general settings for this feature.', 'shahi-template'); ?></p>
        <?php
    }
    
    /**
     * Render example field
     * 
     * PLACEHOLDER: Replace with your actual field rendering
     * 
     * @since 1.0.0
     * @return void
     */
    public function render_field_example() {
        $options = $this->get_options();
        $value = $options['example_field'] ?? '';
        ?>
        <input 
            type="text" 
            name="<?php echo esc_attr($this->page_slug . '_options[example_field]'); ?>" 
            value="<?php echo esc_attr($value); ?>" 
            class="regular-text"
        >
        <p class="description">
            <?php esc_html_e('PLACEHOLDER: Add field description', 'shahi-template'); ?>
        </p>
        <?php
    }
}
