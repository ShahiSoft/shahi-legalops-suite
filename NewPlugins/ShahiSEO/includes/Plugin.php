<?php
/**
 * Main Plugin Class
 *
 * @package ShahiSEO
 */

namespace ShahiSEO;

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
     * Meta tags module
     *
     * @var Modules\MetaTags
     */
    private $meta_tags;

    /**
     * Schema markup module
     *
     * @var Modules\SchemaMarkup
     */
    private $schema;

    /**
     * Sitemap module
     *
     * @var Modules\Sitemap
     */
    private $sitemap;

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
        $this->meta_tags = new Modules\MetaTags();
        $this->schema = new Modules\SchemaMarkup();
        $this->sitemap = new Modules\Sitemap();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'Shahi SEO', 'shahi-seo' ),
            __( 'Shahi SEO', 'shahi-seo' ),
            'manage_options',
            'shahi-seo',
            array( $this, 'render_dashboard' ),
            'dashicons-search',
            80
        );

        add_submenu_page(
            'shahi-seo',
            __( 'Dashboard', 'shahi-seo' ),
            __( 'Dashboard', 'shahi-seo' ),
            'manage_options',
            'shahi-seo',
            array( $this, 'render_dashboard' )
        );

        add_submenu_page(
            'shahi-seo',
            __( 'Meta Tags', 'shahi-seo' ),
            __( 'Meta Tags', 'shahi-seo' ),
            'manage_options',
            'shahi-seo-meta',
            array( $this, 'render_meta_page' )
        );

        add_submenu_page(
            'shahi-seo',
            __( 'Schema Markup', 'shahi-seo' ),
            __( 'Schema Markup', 'shahi-seo' ),
            'manage_options',
            'shahi-seo-schema',
            array( $this, 'render_schema_page' )
        );

        add_submenu_page(
            'shahi-seo',
            __( 'Sitemap', 'shahi-seo' ),
            __( 'Sitemap', 'shahi-seo' ),
            'manage_options',
            'shahi-seo-sitemap',
            array( $this, 'render_sitemap_page' )
        );

        add_submenu_page(
            'shahi-seo',
            __( 'Settings', 'shahi-seo' ),
            __( 'Settings', 'shahi-seo' ),
            'manage_options',
            'shahi-seo-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page.
     */
    public function enqueue_admin_assets( $hook ) {
        if ( strpos( $hook, 'shahi-seo' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'shahi-seo-admin',
            SHAHI_SEO_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            SHAHI_SEO_VERSION
        );

        wp_enqueue_script(
            'shahi-seo-admin',
            SHAHI_SEO_PLUGIN_URL . 'admin/js/admin.js',
            array( 'jquery' ),
            SHAHI_SEO_VERSION,
            true
        );

        wp_localize_script(
            'shahi-seo-admin',
            'shahiSEO',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'shahi_seo_nonce' ),
            )
        );
    }

    /**
     * Render dashboard page
     */
    public function render_dashboard() {
        include SHAHI_SEO_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    /**
     * Render meta tags page
     */
    public function render_meta_page() {
        echo '<div class="wrap"><h1>' . esc_html__( 'Meta Tags Management', 'shahi-seo' ) . '</h1>';
        echo '<p>' . esc_html__( 'Manage meta tags for posts and pages.', 'shahi-seo' ) . '</p></div>';
    }

    /**
     * Render schema markup page
     */
    public function render_schema_page() {
        echo '<div class="wrap"><h1>' . esc_html__( 'Schema Markup', 'shahi-seo' ) . '</h1>';
        echo '<p>' . esc_html__( 'Configure schema.org structured data.', 'shahi-seo' ) . '</p></div>';
    }

    /**
     * Render sitemap page
     */
    public function render_sitemap_page() {
        echo '<div class="wrap"><h1>' . esc_html__( 'XML Sitemap', 'shahi-seo' ) . '</h1>';
        echo '<p>' . esc_html__( 'View and manage your XML sitemap.', 'shahi-seo' ) . '</p>';
        echo '<p><strong>' . esc_html__( 'Sitemap URL:', 'shahi-seo' ) . '</strong> ';
        echo '<a href="' . esc_url( home_url( '/sitemap.xml' ) ) . '" target="_blank">' . esc_url( home_url( '/sitemap.xml' ) ) . '</a></p></div>';
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        echo '<div class="wrap"><h1>' . esc_html__( 'SEO Settings', 'shahi-seo' ) . '</h1>';
        echo '<p>' . esc_html__( 'Configure global SEO settings.', 'shahi-seo' ) . '</p></div>';
    }

    /**
     * Get meta tags module
     *
     * @return Modules\MetaTags
     */
    public function get_meta_tags() {
        return $this->meta_tags;
    }

    /**
     * Get schema module
     *
     * @return Modules\SchemaMarkup
     */
    public function get_schema() {
        return $this->schema;
    }

    /**
     * Get sitemap module
     *
     * @return Modules\Sitemap
     */
    public function get_sitemap() {
        return $this->sitemap;
    }
}
