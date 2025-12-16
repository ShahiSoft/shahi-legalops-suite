<?php
/**
 * Form Builder Module
 *
 * @package ShahiForms
 */

namespace ShahiForms\Modules;

/**
 * FormBuilder Class
 */
class FormBuilder {
    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_ajax_shahi_forms_submit', array( $this, 'ajax_submit_form' ) );
        add_action( 'wp_ajax_nopriv_shahi_forms_submit', array( $this, 'ajax_submit_form' ) );
        add_action( 'wp_ajax_shahi_forms_get_submissions', array( $this, 'ajax_get_submissions' ) );
    }

    /**
     * Render form
     *
     * @param int $form_id Form ID.
     * @return string
     */
    public function render_form( $form_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_forms';

        $form = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE id = %d AND status = 'active'", $form_id ),
            ARRAY_A
        );

        if ( ! $form ) {
            return '<p>' . esc_html__( 'Form not found', 'shahi-forms' ) . '</p>';
        }

        $fields = json_decode( $form['form_fields'], true );
        $settings = json_decode( $form['form_settings'], true );

        ob_start();
        ?>
        <form class="shahi-form" data-form-id="<?php echo esc_attr( $form_id ); ?>" style="max-width: 600px; margin: 0 auto;">
            <?php if ( ! empty( $form['form_description'] ) ) : ?>
                <p><?php echo esc_html( $form['form_description'] ); ?></p>
            <?php endif; ?>

            <?php foreach ( $fields as $field ) : ?>
                <div class="shahi-form-field" style="margin-bottom: 20px;">
                    <label for="field-<?php echo esc_attr( $field['id'] ); ?>" style="display: block; margin-bottom: 5px; font-weight: 600;">
                        <?php echo esc_html( $field['label'] ); ?>
                        <?php if ( ! empty( $field['required'] ) ) : ?>
                            <span style="color: red;">*</span>
                        <?php endif; ?>
                    </label>

                    <?php if ( 'textarea' === $field['type'] ) : ?>
                        <textarea
                            id="field-<?php echo esc_attr( $field['id'] ); ?>"
                            name="<?php echo esc_attr( $field['id'] ); ?>"
                            placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
                            <?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>
                            rows="5"
                            style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: inherit;"
                        ></textarea>
                    <?php elseif ( 'select' === $field['type'] ) : ?>
                        <select
                            id="field-<?php echo esc_attr( $field['id'] ); ?>"
                            name="<?php echo esc_attr( $field['id'] ); ?>"
                            <?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>
                            style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                        >
                            <option value=""><?php esc_html_e( 'Select...', 'shahi-forms' ); ?></option>
                            <?php if ( ! empty( $field['options'] ) ) : ?>
                                <?php foreach ( $field['options'] as $option ) : ?>
                                    <option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    <?php elseif ( 'checkbox' === $field['type'] ) : ?>
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input
                                type="checkbox"
                                id="field-<?php echo esc_attr( $field['id'] ); ?>"
                                name="<?php echo esc_attr( $field['id'] ); ?>"
                                value="1"
                                <?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>
                            >
                            <span><?php echo esc_html( $field['checkbox_label'] ?? $field['label'] ); ?></span>
                        </label>
                    <?php else : ?>
                        <input
                            type="<?php echo esc_attr( $field['type'] ); ?>"
                            id="field-<?php echo esc_attr( $field['id'] ); ?>"
                            name="<?php echo esc_attr( $field['id'] ); ?>"
                            placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
                            <?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>
                            style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                        >
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="shahi-form-messages" style="margin: 15px 0;"></div>

            <button type="submit" class="shahi-form-submit" style="background: #0073aa; color: white; padding: 12px 30px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">
                <?php echo esc_html( $settings['submit_button_text'] ?? __( 'Submit', 'shahi-forms' ) ); ?>
            </button>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * Submit form via AJAX
     */
    public function ajax_submit_form() {
        check_ajax_referer( 'shahi_forms_submit', 'nonce' );

        $form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
        $form_data = isset( $_POST['form_data'] ) ? $_POST['form_data'] : array();

        if ( ! $form_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid form ID', 'shahi-forms' ) ) );
        }

        // Get form
        global $wpdb;
        $forms_table = $wpdb->prefix . 'shahi_forms';

        $form = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $forms_table WHERE id = %d AND status = 'active'", $form_id ),
            ARRAY_A
        );

        if ( ! $form ) {
            wp_send_json_error( array( 'message' => __( 'Form not found', 'shahi-forms' ) ) );
        }

        // Validate fields
        $fields = json_decode( $form['form_fields'], true );
        $errors = array();

        foreach ( $fields as $field ) {
            if ( ! empty( $field['required'] ) && empty( $form_data[ $field['id'] ] ) ) {
                $errors[] = sprintf(
                    /* translators: %s: field label */
                    __( '%s is required', 'shahi-forms' ),
                    $field['label']
                );
            }

            if ( 'email' === $field['type'] && ! empty( $form_data[ $field['id'] ] ) ) {
                if ( ! is_email( $form_data[ $field['id'] ] ) ) {
                    $errors[] = sprintf(
                        /* translators: %s: field label */
                        __( '%s must be a valid email address', 'shahi-forms' ),
                        $field['label']
                    );
                }
            }
        }

        if ( ! empty( $errors ) ) {
            wp_send_json_error( array( 'message' => implode( '<br>', $errors ) ) );
        }

        // Sanitize and save submission
        $sanitized_data = array();
        foreach ( $form_data as $key => $value ) {
            $sanitized_data[ $key ] = sanitize_text_field( $value );
        }

        $submissions_table = $wpdb->prefix . 'shahi_form_submissions';

        $submission_id = $wpdb->insert(
            $submissions_table,
            array(
                'form_id' => $form_id,
                'user_id' => get_current_user_id() ?: null,
                'ip_address' => $this->get_ip_address(),
                'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '',
                'submission_data' => wp_json_encode( $sanitized_data ),
                'status' => 'unread',
            ),
            array( '%d', '%d', '%s', '%s', '%s', '%s' )
        );

        if ( ! $submission_id ) {
            wp_send_json_error( array( 'message' => __( 'Failed to save submission', 'shahi-forms' ) ) );
        }

        // Send email notification
        $settings = json_decode( $form['form_settings'], true );
        if ( ! empty( $settings['email_notification'] ) && get_option( 'shahi_forms_email_notifications', true ) ) {
            $this->send_notification_email( $form, $sanitized_data );
        }

        $success_message = $settings['success_message'] ?? __( 'Thank you for your submission!', 'shahi-forms' );

        wp_send_json_success( array(
            'message' => $success_message,
            'submission_id' => $wpdb->insert_id,
        ) );
    }

    /**
     * Send notification email
     *
     * @param array $form Form data.
     * @param array $submission_data Submission data.
     */
    private function send_notification_email( $form, $submission_data ) {
        $to = get_option( 'shahi_forms_notification_email', get_option( 'admin_email' ) );
        $subject = sprintf(
            /* translators: %s: form name */
            __( 'New submission: %s', 'shahi-forms' ),
            $form['form_name']
        );

        $message = __( 'You have received a new form submission:', 'shahi-forms' ) . "\n\n";
        $message .= __( 'Form:', 'shahi-forms' ) . ' ' . $form['form_name'] . "\n\n";

        foreach ( $submission_data as $key => $value ) {
            $message .= ucfirst( $key ) . ': ' . $value . "\n";
        }

        wp_mail( $to, $subject, $message );
    }

    /**
     * Get IP address
     *
     * @return string
     */
    private function get_ip_address() {
        if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
            $ip = sanitize_text_field( $_SERVER['HTTP_CF_CONNECTING_IP'] );
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = explode( ',', sanitize_text_field( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )[0];
        } else {
            $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '';
        }

        return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
    }

    /**
     * Get all forms
     *
     * @return array
     */
    public function get_all_forms() {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_forms';

        return $wpdb->get_results(
            "SELECT * FROM $table ORDER BY created_at DESC",
            ARRAY_A
        );
    }

    /**
     * Get submissions for a form
     *
     * @param int $form_id Form ID.
     * @return array
     */
    public function get_submissions( $form_id = null ) {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_form_submissions';

        if ( $form_id ) {
            return $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table WHERE form_id = %d ORDER BY submitted_at DESC",
                    $form_id
                ),
                ARRAY_A
            );
        }

        return $wpdb->get_results(
            "SELECT * FROM $table ORDER BY submitted_at DESC LIMIT 100",
            ARRAY_A
        );
    }

    /**
     * AJAX: Get submissions
     */
    public function ajax_get_submissions() {
        check_ajax_referer( 'shahi_forms_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shahi-forms' ) ) );
        }

        $form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : null;
        $submissions = $this->get_submissions( $form_id );

        wp_send_json_success( array( 'submissions' => $submissions ) );
    }
}
