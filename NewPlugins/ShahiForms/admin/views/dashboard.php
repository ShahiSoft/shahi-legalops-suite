<?php
/**
 * Dashboard View
 *
 * @package ShahiForms
 */

$plugin = \ShahiForms\Plugin::get_instance();
$forms = $plugin->get_form_builder()->get_all_forms();

global $wpdb;
$submissions_table = $wpdb->prefix . 'shahi_form_submissions';
$total_submissions = $wpdb->get_var( "SELECT COUNT(*) FROM $submissions_table" );
$unread_submissions = $wpdb->get_var( "SELECT COUNT(*) FROM $submissions_table WHERE status = 'unread'" );

?>
<div class="wrap">
    <h1><?php esc_html_e( 'Shahi Forms', 'shahi-forms' ); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-forms-new' ) ); ?>" class="page-title-action">
            <?php esc_html_e( 'Add New', 'shahi-forms' ); ?>
        </a>
    </h1>

    <div class="shahi-forms-dashboard" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
        
        <!-- Stats -->
        <div class="shahi-card" style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0; display: flex; align-items: center; gap: 10px;">
                <span class="dashicons dashicons-feedback" style="font-size: 28px; color: #0073aa;"></span>
                <?php esc_html_e( 'Overview', 'shahi-forms' ); ?>
            </h2>
            <div class="stat-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-top: 20px;">
                <div>
                    <div style="font-size: 32px; font-weight: bold; color: #0073aa;"><?php echo esc_html( count( $forms ) ); ?></div>
                    <div style="color: #666;"><?php esc_html_e( 'Total Forms', 'shahi-forms' ); ?></div>
                </div>
                <div>
                    <div style="font-size: 32px; font-weight: bold; color: #46b450;"><?php echo esc_html( $total_submissions ); ?></div>
                    <div style="color: #666;"><?php esc_html_e( 'Submissions', 'shahi-forms' ); ?></div>
                </div>
            </div>
            <?php if ( $unread_submissions > 0 ) : ?>
                <div style="margin-top: 15px; padding: 15px; background: #fff3cd; border-radius: 6px; border-left: 4px solid #ffc107;">
                    <strong><?php echo esc_html( $unread_submissions ); ?></strong> 
                    <?php esc_html_e( 'unread submissions', 'shahi-forms' ); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="shahi-card" style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0;"><?php esc_html_e( 'Quick Actions', 'shahi-forms' ); ?></h2>
            <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-forms-new' ) ); ?>" class="button button-primary" style="justify-content: center;">
                    <span class="dashicons dashicons-plus"></span>
                    <?php esc_html_e( 'Create New Form', 'shahi-forms' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-forms-submissions' ) ); ?>" class="button" style="justify-content: center;">
                    <span class="dashicons dashicons-email"></span>
                    <?php esc_html_e( 'View Submissions', 'shahi-forms' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-forms-settings' ) ); ?>" class="button" style="justify-content: center;">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php esc_html_e( 'Settings', 'shahi-forms' ); ?>
                </a>
            </div>
        </div>
    </div>

    <h2 style="margin-top: 40px;"><?php esc_html_e( 'Your Forms', 'shahi-forms' ); ?></h2>
    
    <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Form Name', 'shahi-forms' ); ?></th>
                <th><?php esc_html_e( 'Shortcode', 'shahi-forms' ); ?></th>
                <th><?php esc_html_e( 'Submissions', 'shahi-forms' ); ?></th>
                <th><?php esc_html_e( 'Status', 'shahi-forms' ); ?></th>
                <th><?php esc_html_e( 'Created', 'shahi-forms' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $forms ) ) : ?>
                <?php foreach ( $forms as $form ) : ?>
                    <?php
                    $submission_count = $wpdb->get_var(
                        $wpdb->prepare( "SELECT COUNT(*) FROM $submissions_table WHERE form_id = %d", $form['id'] )
                    );
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html( $form['form_name'] ); ?></strong>
                            <?php if ( ! empty( $form['form_description'] ) ) : ?>
                                <br><small style="color: #666;"><?php echo esc_html( $form['form_description'] ); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code style="background: #f5f5f5; padding: 4px 8px; border-radius: 3px; user-select: all;">[shahi_form id="<?php echo esc_attr( $form['id'] ); ?>"]</code>
                        </td>
                        <td><?php echo esc_html( $submission_count ); ?></td>
                        <td>
                            <?php if ( 'active' === $form['status'] ) : ?>
                                <span style="color: #46b450;">● <?php esc_html_e( 'Active', 'shahi-forms' ); ?></span>
                            <?php else : ?>
                                <span style="color: #dc3232;">● <?php esc_html_e( 'Inactive', 'shahi-forms' ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html( mysql2date( 'Y-m-d', $form['created_at'] ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px;">
                        <?php esc_html_e( 'No forms found. Create your first form!', 'shahi-forms' ); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
