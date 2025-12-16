<?php
/**
 * Main Plugin Class
 *
 * @package ShahiForms
 */

namespace ShahiForms;

/**
 * Plugin Class
 */
class Plugin {
    /**
     * Plugin instance
     *
     * @var Plugin
     */
    private static $instance = null;

    /**
     * Form builder module
     *
     * @var Modules\FormBuilder
     */
    private $form_builder;

    /**
     * Get plugin instance
     *
     * @return Plugin
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_modules();
        $this->init_hooks();
    }

    /**
     * Initialize modules
     */
    private function init_modules() {
        $this->form_builder = new Modules\FormBuilder();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        add_shortcode( 'shahi_form', array( $this, 'render_form_shortcode' ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'Shahi Forms', 'shahi-forms' ),
            __( 'Shahi Forms', 'shahi-forms' ),
            'manage_options',
            'shahi-forms',
            array( $this, 'render_dashboard' ),
            'dashicons-feedback',
            80
        );

        add_submenu_page(
            'shahi-forms',
            __( 'All Forms', 'shahi-forms' ),
            __( 'All Forms', 'shahi-forms' ),
            'manage_options',
            'shahi-forms',
            array( $this, 'render_dashboard' )
        );

        add_submenu_page(
            'shahi-forms',
            __( 'Add New', 'shahi-forms' ),
            __( 'Add New', 'shahi-forms' ),
            'manage_options',
            'shahi-forms-new',
            array( $this, 'render_form_builder' )
        );

        add_submenu_page(
            'shahi-forms',
            __( 'Submissions', 'shahi-forms' ),
            __( 'Submissions', 'shahi-forms' ),
            'manage_options',
            'shahi-forms-submissions',
            array( $this, 'render_submissions_page' )
        );

        add_submenu_page(
            'shahi-forms',
            __( 'Settings', 'shahi-forms' ),
            __( 'Settings', 'shahi-forms' ),
            'manage_options',
            'shahi-forms-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page.
     */
    public function enqueue_admin_assets( $hook ) {
        if ( strpos( $hook, 'shahi-forms' ) === false ) {
            return;
        }

        wp_enqueue_script( 'jquery-ui-sortable' );
        
        wp_enqueue_script(
            'shahi-forms-admin',
            SHAHI_FORMS_PLUGIN_URL . 'admin/js/form-builder.js',
            array( 'jquery', 'jquery-ui-sortable' ),
            SHAHI_FORMS_VERSION,
            true
        );

        wp_localize_script(
            'shahi-forms-admin',
            'shahiForms',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'shahi_forms_nonce' ),
            )
        );
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        wp_enqueue_script(
            'shahi-forms-frontend',
            SHAHI_FORMS_PLUGIN_URL . 'public/js/forms.js',
            array( 'jquery' ),
            SHAHI_FORMS_VERSION,
            true
        );

        wp_localize_script(
            'shahi-forms-frontend',
            'shahiForms',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'shahi_forms_submit' ),
            )
        );
    }

    /**
     * Render dashboard page
     */
    public function render_dashboard() {
        include SHAHI_FORMS_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    /**
     * Render form builder page
     */
    public function render_form_builder() {
        echo '<div class="wrap"><h1>' . esc_html__( 'Form Builder', 'shahi-forms' ) . '</h1>';
        echo '<p>' . esc_html__( 'Drag and drop form builder interface would go here.', 'shahi-forms' ) . '</p>';
        echo '<p><em>' . esc_html__( 'PLACEHOLDER: Full drag-and-drop UI would require extensive JavaScript implementation.', 'shahi-forms' ) . '</em></p></div>';
    }

    /**
     * Render submissions page
     */
    public function render_submissions_page() {
        include SHAHI_FORMS_PLUGIN_DIR . 'admin/views/submissions.php';
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        echo '<div class="wrap"><h1>' . esc_html__( 'Form Settings', 'shahi-forms' ) . '</h1>';
        echo '<p>' . esc_html__( 'Configure email notifications and other form settings.', 'shahi-forms' ) . '</p></div>';
    }

    /**
     * Render form shortcode
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function render_form_shortcode( $atts ) {
        $atts = shortcode_atts(
            array(
                'id' => 0,
            ),
            $atts
        );

        $form_id = absint( $atts['id'] );

        if ( ! $form_id ) {
            return '<p>' . esc_html__( 'Error: Form ID not specified', 'shahi-forms' ) . '</p>';
        }

        return $this->form_builder->render_form( $form_id );
    }

    /**
     * Get form builder module
     *
     * @return Modules\FormBuilder
     */
    public function get_form_builder() {
        return $this->form_builder;
    }
}
