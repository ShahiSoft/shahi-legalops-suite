<?php
/**
 * Backups List View
 *
 * @package ShahiBackup
 */

global $wpdb;
$table = $wpdb->prefix . 'shahi_backups';

$backups = $wpdb->get_results(
    "SELECT * FROM $table ORDER BY started_at DESC LIMIT 50",
    ARRAY_A
);

?>
<div class="wrap">
    <h1><?php esc_html_e( 'Backup History', 'shahi-backup' ); ?></h1>

    <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Backup Name', 'shahi-backup' ); ?></th>
                <th><?php esc_html_e( 'Type', 'shahi-backup' ); ?></th>
                <th><?php esc_html_e( 'Size', 'shahi-backup' ); ?></th>
                <th><?php esc_html_e( 'Status', 'shahi-backup' ); ?></th>
                <th><?php esc_html_e( 'Date', 'shahi-backup' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'shahi-backup' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $backups ) ) : ?>
                <?php foreach ( $backups as $backup ) : ?>
                    <tr>
                        <td><strong><?php echo esc_html( $backup['backup_name'] ); ?></strong></td>
                        <td><?php echo esc_html( ucfirst( $backup['backup_type'] ) ); ?></td>
                        <td><?php echo esc_html( size_format( $backup['file_size'] ) ); ?></td>
                        <td>
                            <?php if ( 'completed' === $backup['status'] ) : ?>
                                <span style="color: #46b450;">✓ <?php esc_html_e( 'Completed', 'shahi-backup' ); ?></span>
                            <?php elseif ( 'failed' === $backup['status'] ) : ?>
                                <span style="color: #dc3232;">✗ <?php esc_html_e( 'Failed', 'shahi-backup' ); ?></span>
                            <?php else : ?>
                                <span style="color: #0073aa;">⟳ <?php esc_html_e( 'In Progress', 'shahi-backup' ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html( mysql2date( 'Y-m-d H:i', $backup['started_at'] ) ); ?></td>
                        <td>
                            <?php if ( 'completed' === $backup['status'] ) : ?>
                                <a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=shahi_backup_download&backup_id=' . $backup['id'] . '&nonce=' . wp_create_nonce( 'shahi_backup_nonce' ) ) ); ?>" class="button button-small">
                                    <?php esc_html_e( 'Download', 'shahi-backup' ); ?>
                                </a>
                            <?php endif; ?>
                            <button class="button button-small shahi-delete-backup" data-id="<?php echo esc_attr( $backup['id'] ); ?>">
                                <?php esc_html_e( 'Delete', 'shahi-backup' ); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px;">
                        <?php esc_html_e( 'No backups found.', 'shahi-backup' ); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
jQuery(document).ready(function($) {
    $('.shahi-delete-backup').on('click', function() {
        if (!confirm('<?php esc_html_e( 'Are you sure you want to delete this backup?', 'shahi-backup' ); ?>')) {
            return;
        }
        
        var $btn = $(this);
        var backupId = $btn.data('id');
        
        $btn.prop('disabled', true).text('<?php esc_html_e( 'Deleting...', 'shahi-backup' ); ?>');
        
        $.ajax({
            url: shahiBackup.ajaxUrl,
            type: 'POST',
            data: {
                action: 'shahi_backup_delete',
                nonce: shahiBackup.nonce,
                backup_id: backupId
            },
            success: function(response) {
                if (response.success) {
                    $btn.closest('tr').fadeOut();
                } else {
                    alert('<?php esc_html_e( 'Error:', 'shahi-backup' ); ?> ' + response.data.message);
                    $btn.prop('disabled', false).text('<?php esc_html_e( 'Delete', 'shahi-backup' ); ?>');
                }
            }
        });
    });
});
</script>
