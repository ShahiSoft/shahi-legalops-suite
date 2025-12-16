<?php
/**
 * Submissions View
 *
 * @package ShahiForms
 */

$plugin = \ShahiForms\Plugin::get_instance();
$submissions = $plugin->get_form_builder()->get_submissions();

global $wpdb;
$forms_table = $wpdb->prefix . 'shahi_forms';

?>
<div class="wrap">
    <h1><?php esc_html_e( 'Form Submissions', 'shahi-forms' ); ?></h1>

    <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
        <thead>
            <tr>
                <th><?php esc_html_e( 'ID', 'shahi-forms' ); ?></th>
                <th><?php esc_html_e( 'Form', 'shahi-forms' ); ?></th>
                <th><?php esc_html_e( 'Submission Data', 'shahi-forms' ); ?></th>
                <th><?php esc_html_e( 'IP Address', 'shahi-forms' ); ?></th>
                <th><?php esc_html_e( 'Status', 'shahi-forms' ); ?></th>
                <th><?php esc_html_e( 'Date', 'shahi-forms' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $submissions ) ) : ?>
                <?php foreach ( $submissions as $submission ) : ?>
                    <?php
                    $form = $wpdb->get_row(
                        $wpdb->prepare( "SELECT form_name FROM $forms_table WHERE id = %d", $submission['form_id'] ),
                        ARRAY_A
                    );
                    $data = json_decode( $submission['submission_data'], true );
                    ?>
                    <tr>
                        <td><?php echo esc_html( $submission['id'] ); ?></td>
                        <td><strong><?php echo esc_html( $form['form_name'] ?? 'Unknown' ); ?></strong></td>
                        <td>
                            <?php if ( $data ) : ?>
                                <?php foreach ( $data as $key => $value ) : ?>
                                    <div style="margin-bottom: 5px;">
                                        <strong><?php echo esc_html( ucfirst( $key ) ); ?>:</strong> 
                                        <?php echo esc_html( $value ); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html( $submission['ip_address'] ); ?></td>
                        <td>
                            <?php if ( 'unread' === $submission['status'] ) : ?>
                                <span style="color: #0073aa; font-weight: bold;">● <?php esc_html_e( 'Unread', 'shahi-forms' ); ?></span>
                            <?php else : ?>
                                <span style="color: #666;">○ <?php esc_html_e( 'Read', 'shahi-forms' ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html( mysql2date( 'Y-m-d H:i', $submission['submitted_at'] ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px;">
                        <?php esc_html_e( 'No submissions yet.', 'shahi-forms' ); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
