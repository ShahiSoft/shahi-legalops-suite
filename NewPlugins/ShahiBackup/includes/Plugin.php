<?php
/**
 * Main Plugin Class
 *
 * @package ShahiBackup
 */

namespace ShahiBackup;

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
     * Backup engine
     *
     * @var Modules\BackupEngine
     */
    private $backup_engine;

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
        $this->backup_engine = new Modules\BackupEngine();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'shahi_backup_cron', array( $this, 'run_scheduled_backup' ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'Shahi Backup', 'shahi-backup' ),
            __( 'Shahi Backup', 'shahi-backup' ),
            'manage_options',
            'shahi-backup',
            array( $this, 'render_dashboard' ),
            'dashicons-backup',
            80
        );

        add_submenu_page(
            'shahi-backup',
            __( 'Dashboard', 'shahi-backup' ),
            __( 'Dashboard', 'shahi-backup' ),
            'manage_options',
            'shahi-backup',
            array( $this, 'render_dashboard' )
        );

        add_submenu_page(
            'shahi-backup',
            __( 'Backups', 'shahi-backup' ),
            __( 'Backups', 'shahi-backup' ),
            'manage_options',
            'shahi-backup-list',
            array( $this, 'render_backups_page' )
        );

        add_submenu_page(
            'shahi-backup',
            __( 'Schedule', 'shahi-backup' ),
            __( 'Schedule', 'shahi-backup' ),
            'manage_options',
            'shahi-backup-schedule',
            array( $this, 'render_schedule_page' )
        );

        add_submenu_page(
            'shahi-backup',
            __( 'Settings', 'shahi-backup' ),
            __( 'Settings', 'shahi-backup' ),
            'manage_options',
            'shahi-backup-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page.
     */
    public function enqueue_admin_assets( $hook ) {
        if ( strpos( $hook, 'shahi-backup' ) === false ) {
            return;
        }

        wp_enqueue_script(
            'shahi-backup-admin',
            SHAHI_BACKUP_PLUGIN_URL . 'admin/js/admin.js',
            array( 'jquery' ),
            SHAHI_BACKUP_VERSION,
            true
        );

        wp_localize_script(
            'shahi-backup-admin',
            'shahiBackup',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'shahi_backup_nonce' ),
            )
        );
    }

    /**
     * Render dashboard page
     */
    public function render_dashboard() {
        include SHAHI_BACKUP_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    /**
     * Render backups page
     */
    public function render_backups_page() {
        include SHAHI_BACKUP_PLUGIN_DIR . 'admin/views/backups.php';
    }

    /**
     * Render schedule page
     */
    public function render_schedule_page() {
        echo '<div class="wrap"><h1>' . esc_html__( 'Backup Schedule', 'shahi-backup' ) . '</h1>';
        echo '<p>' . esc_html__( 'Configure automatic backup schedule.', 'shahi-backup' ) . '</p></div>';
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        echo '<div class="wrap"><h1>' . esc_html__( 'Backup Settings', 'shahi-backup' ) . '</h1>';
        echo '<p>' . esc_html__( 'Configure backup settings and retention.', 'shahi-backup' ) . '</p></div>';
    }

    /**
     * Run scheduled backup
     */
    public function run_scheduled_backup() {
        if ( ! get_option( 'shahi_backup_auto_enabled', false ) ) {
            return;
        }

        $backup_types = array();

        if ( get_option( 'shahi_backup_database_enabled', true ) ) {
            $backup_types[] = 'database';
        }

        if ( get_option( 'shahi_backup_files_enabled', false ) ) {
            $backup_types[] = 'files';
        }

        foreach ( $backup_types as $type ) {
            $this->backup_engine->create_backup( $type );
        }

        // Clean old backups
        $this->backup_engine->clean_old_backups();
    }

    /**
     * Get backup engine
     *
     * @return Modules\BackupEngine
     */
    public function get_backup_engine() {
        return $this->backup_engine;
    }
}
