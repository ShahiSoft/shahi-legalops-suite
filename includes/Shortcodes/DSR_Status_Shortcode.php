<?php
/**
 * DSR Status Shortcode
 *
 * Provides a public-facing portal for requesters to check their DSR status.
 * Renders a form to lookup status and displays progress information.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Shortcodes
 * @since      3.0.1
 */

namespace ShahiLegalopsSuite\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * DSR Status Shortcode Class
 *
 * Handles [slos_dsr_status] shortcode rendering.
 *
 * @since 3.0.1
 */
class DSR_Status_Shortcode {

    /**
     * Initialize shortcode
     *
     * @since 3.0.1
     */
    public function __construct() {
        add_shortcode( 'slos_dsr_status', array( $this, 'render' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Render the shortcode
     *
     * @since 3.0.1
     * @param array $atts Shortcode attributes
     * @return string Shortcode output
     */
    public function render( $atts = array() ): string {
        // Parse attributes
        $atts = shortcode_atts(
            array(
                'title'       => __( 'Check Your Request Status', 'shahi-legalops-suite' ),
                'show_title'  => 'yes',
                'show_help'   => 'yes',
            ),
            $atts,
            'slos_dsr_status'
        );

        ob_start();

        echo '<div class="slos-dsr-status-portal" id="slos-dsr-status-portal">';

        // Title
        if ( 'yes' === $atts['show_title'] && ! empty( $atts['title'] ) ) {
            echo '<h2 class="slos-dsr-status-title">' . esc_html( $atts['title'] ) . '</h2>';
        }

        // Help text
        if ( 'yes' === $atts['show_help'] ) {
            echo '<div class="slos-dsr-status-help">';
            echo '<p>' . esc_html__( 'Enter your tracking token to view the status of your data subject request.', 'shahi-legalops-suite' ) . '</p>';
            echo '<p class="slos-dsr-status-help-note">' . esc_html__( 'Your tracking token was sent to your email when you submitted your request.', 'shahi-legalops-suite' ) . '</p>';
            echo '</div>';
        }

        // Lookup form
        echo '<div class="slos-dsr-status-form-wrapper">';
        echo '<form class="slos-dsr-status-form" id="slos-dsr-status-form">';
        echo '<div class="slos-form-field">';
        echo '<label for="slos-dsr-token">' . esc_html__( 'Tracking Token', 'shahi-legalops-suite' ) . '</label>';
        echo '<input type="text" id="slos-dsr-token" name="token" class="slos-input" placeholder="' . esc_attr__( 'Enter your tracking token', 'shahi-legalops-suite' ) . '" required />';
        echo '</div>';

        echo '<div class="slos-form-actions">';
        echo '<button type="submit" class="slos-button slos-button-primary">' . esc_html__( 'Check Status', 'shahi-legalops-suite' ) . '</button>';
        echo '</div>';
        echo '</form>';
        echo '</div>';

        // Status result container
        echo '<div class="slos-dsr-status-result" id="slos-dsr-status-result" style="display: none;"></div>';

        // Loading indicator
        echo '<div class="slos-dsr-status-loading" id="slos-dsr-status-loading" style="display: none;">';
        echo '<div class="slos-spinner"></div>';
        echo '<p>' . esc_html__( 'Loading your request status...', 'shahi-legalops-suite' ) . '</p>';
        echo '</div>';

        // Error container
        echo '<div class="slos-dsr-status-error" id="slos-dsr-status-error" style="display: none;"></div>';

        echo '</div>'; // .slos-dsr-status-portal

        return ob_get_clean();
    }

    /**
     * Enqueue shortcode assets
     *
     * @since 3.0.1
     * @return void
     */
    public function enqueue_assets(): void {
        global $post;

        // Check if shortcode is present
        if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'slos_dsr_status' ) ) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'slos-dsr-status',
            SHAHI_LEGALOPS_SUITE_PLUGIN_URL . 'assets/css/dsr-status.css',
            array(),
            SHAHI_LEGALOPS_SUITE_VERSION
        );

        // Enqueue JS
        wp_enqueue_script(
            'slos-dsr-status',
            SHAHI_LEGALOPS_SUITE_PLUGIN_URL . 'assets/js/dsr-status.js',
            array( 'jquery' ),
            SHAHI_LEGALOPS_SUITE_VERSION,
            true
        );

        // Localize script
        wp_localize_script(
            'slos-dsr-status',
            'slosDsrStatus',
            array(
                'apiUrl'   => rest_url( 'slos/v1/dsr/status' ),
                'nonce'    => wp_create_nonce( 'wp_rest' ),
                'i18n'     => array(
                    'error'            => __( 'Error', 'shahi-legalops-suite' ),
                    'invalidToken'     => __( 'Invalid or expired token. Please check your token and try again.', 'shahi-legalops-suite' ),
                    'requestFailed'    => __( 'Failed to retrieve status. Please try again later.', 'shahi-legalops-suite' ),
                    'statusNew'        => __( 'New', 'shahi-legalops-suite' ),
                    'statusPendingVerification' => __( 'Pending Verification', 'shahi-legalops-suite' ),
                    'statusVerified'   => __( 'Verified', 'shahi-legalops-suite' ),
                    'statusInProgress' => __( 'In Progress', 'shahi-legalops-suite' ),
                    'statusCompleted'  => __( 'Completed', 'shahi-legalops-suite' ),
                    'statusRejected'   => __( 'Rejected', 'shahi-legalops-suite' ),
                    'typeAccess'       => __( 'Data Access', 'shahi-legalops-suite' ),
                    'typeDeletion'     => __( 'Data Deletion', 'shahi-legalops-suite' ),
                    'typeErasure'      => __( 'Data Erasure', 'shahi-legalops-suite' ),
                    'typeRectification' => __( 'Data Rectification', 'shahi-legalops-suite' ),
                    'typePortability'  => __( 'Data Portability', 'shahi-legalops-suite' ),
                    'typeRestriction'  => __( 'Processing Restriction', 'shahi-legalops-suite' ),
                    'typeObjection'    => __( 'Processing Objection', 'shahi-legalops-suite' ),
                    'typeOptOut'       => __( 'Opt-Out', 'shahi-legalops-suite' ),
                ),
            )
        );
    }
}
