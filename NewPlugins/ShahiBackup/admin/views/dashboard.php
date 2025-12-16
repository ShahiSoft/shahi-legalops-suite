<?php
/**
 * Dashboard View
 *
 * @package ShahiBackup
 */

// Get stats
global $wpdb;
$table = $wpdb->prefix . 'shahi_backups';
$total_backups = $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'completed'" );
$failed_backups = $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'failed'" );

$latest_backup = $wpdb->get_row(
    "SELECT * FROM $table WHERE status = 'completed' ORDER BY completed_at DESC LIMIT 1",
    ARRAY_A
);

$total_size = $wpdb->get_var( "SELECT SUM(file_size) FROM $table WHERE status = 'completed'" );
$next_scheduled = wp_next_scheduled( 'shahi_backup_cron' );

?>
<div class="wrap">
    <h1><?php esc_html_e( 'Shahi Backup Dashboard', 'shahi-backup' ); ?></h1>

    <div class="shahi-backup-dashboard" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
        
        <!-- Backup Stats -->
        <div class="shahi-card" style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0; display: flex; align-items: center; gap: 10px;">
                <span class="dashicons dashicons-backup" style="font-size: 28px; color: #0073aa;"></span>
                <?php esc_html_e( 'Backup Statistics', 'shahi-backup' ); ?>
            </h2>
            <div class="stat-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-top: 20px;">
                <div>
                    <div style="font-size: 32px; font-weight: bold; color: #46b450;"><?php echo esc_html( $total_backups ); ?></div>
                    <div style="color: #666;"><?php esc_html_e( 'Total Backups', 'shahi-backup' ); ?></div>
                </div>
                <div>
                    <div style="font-size: 32px; font-weight: bold; color: <?php echo $failed_backups > 0 ? '#dc3232' : '#46b450'; ?>;">
                        <?php echo esc_html( $failed_backups ); ?>
                    </div>
                    <div style="color: #666;"><?php esc_html_e( 'Failed', 'shahi-backup' ); ?></div>
                </div>
            </div>
            <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee;">
                <strong><?php esc_html_e( 'Total Size:', 'shahi-backup' ); ?></strong> 
                <?php echo esc_html( size_format( $total_size ) ); ?>
            </div>
        </div>

        <!-- Latest Backup -->
        <div class="shahi-card" style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0;"><?php esc_html_e( 'Latest Backup', 'shahi-backup' ); ?></h2>
            <?php if ( $latest_backup ) : ?>
                <div style="margin-top: 15px;">
                    <div style="padding: 10px; background: #f7f7f7; border-radius: 6px; margin-bottom: 10px;">
                        <strong><?php echo esc_html( $latest_backup['backup_name'] ); ?></strong>
                        <div style="color: #666; font-size: 13px; margin-top: 5px;">
                            <?php echo esc_html( ucfirst( $latest_backup['backup_type'] ) ); ?> • 
                            <?php echo esc_html( size_format( $latest_backup['file_size'] ) ); ?>
                        </div>
                        <div style="color: #666; font-size: 13px;">
                            <?php echo esc_html( mysql2date( 'F j, Y g:i a', $latest_backup['completed_at'] ) ); ?>
                        </div>
                    </div>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-backup-list' ) ); ?>" class="button">
                        <?php esc_html_e( 'View All Backups', 'shahi-backup' ); ?>
                    </a>
                </div>
            <?php else : ?>
                <p style="color: #666;"><?php esc_html_e( 'No backups yet.', 'shahi-backup' ); ?></p>
                <button class="button button-primary shahi-create-backup" data-type="database">
                    <?php esc_html_e( 'Create First Backup', 'shahi-backup' ); ?>
                </button>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="shahi-card" style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0;"><?php esc_html_e( 'Quick Actions', 'shahi-backup' ); ?></h2>
            <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
                <button class="button button-primary shahi-create-backup" data-type="database" style="justify-content: center;">
                    <span class="dashicons dashicons-database"></span>
                    <?php esc_html_e( 'Backup Database', 'shahi-backup' ); ?>
                </button>
                <button class="button button-secondary shahi-create-backup" data-type="files" style="justify-content: center;">
                    <span class="dashicons dashicons-media-archive"></span>
                    <?php esc_html_e( 'Backup Files', 'shahi-backup' ); ?>
                </button>
                <button class="button button-secondary shahi-create-backup" data-type="full" style="justify-content: center;">
                    <span class="dashicons dashicons-backup"></span>
                    <?php esc_html_e( 'Full Backup', 'shahi-backup' ); ?>
                </button>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-backup-schedule' ) ); ?>" class="button" style="justify-content: center;">
                    <span class="dashicons dashicons-clock"></span>
                    <?php esc_html_e( 'Manage Schedule', 'shahi-backup' ); ?>
                </a>
            </div>
        </div>

        <!-- Schedule Status -->
        <div class="shahi-card" style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0;"><?php esc_html_e( 'Automated Backups', 'shahi-backup' ); ?></h2>
            <div style="margin-top: 15px;">
                <?php if ( get_option( 'shahi_backup_auto_enabled', false ) ) : ?>
                    <div style="display: flex; align-items: center; gap: 10px; padding: 15px; background: #e7f7e7; border-radius: 6px; margin-bottom: 15px;">
                        <span class="dashicons dashicons-yes-alt" style="color: #46b450; font-size: 24px;"></span>
                        <div>
                            <strong style="color: #46b450;"><?php esc_html_e( 'Active', 'shahi-backup' ); ?></strong>
                            <div style="font-size: 13px; color: #666; margin-top: 3px;">
                                <?php echo esc_html( ucfirst( get_option( 'shahi_backup_schedule', 'daily' ) ) ); ?> schedule
                            </div>
                        </div>
                    </div>
                    <?php if ( $next_scheduled ) : ?>
                        <p style="margin: 0; color: #666;">
                            <strong><?php esc_html_e( 'Next backup:', 'shahi-backup' ); ?></strong><br>
                            <?php echo esc_html( date_i18n( 'F j, Y g:i a', $next_scheduled ) ); ?>
                        </p>
                    <?php endif; ?>
                <?php else : ?>
                    <div style="display: flex; align-items: center; gap: 10px; padding: 15px; background: #fff3cd; border-radius: 6px; margin-bottom: 15px;">
                        <span class="dashicons dashicons-warning" style="color: #856404; font-size: 24px;"></span>
                        <div>
                            <strong style="color: #856404;"><?php esc_html_e( 'Disabled', 'shahi-backup' ); ?></strong>
                            <div style="font-size: 13px; color: #666; margin-top: 3px;">
                                <?php esc_html_e( 'Automatic backups are not scheduled', 'shahi-backup' ); ?>
                            </div>
                        </div>
                    </div>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-backup-schedule' ) ); ?>" class="button button-primary">
                        <?php esc_html_e( 'Enable Automatic Backups', 'shahi-backup' ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.shahi-create-backup').on('click', function() {
        var $btn = $(this);
        var type = $btn.data('type');
        
        $btn.prop('disabled', true).text('<?php esc_html_e( 'Creating backup...', 'shahi-backup' ); ?>');
        
        $.ajax({
            url: shahiBackup.ajaxUrl,
            type: 'POST',
            data: {
                action: 'shahi_backup_create',
                nonce: shahiBackup.nonce,
                type: type
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php esc_html_e( 'Backup created successfully!', 'shahi-backup' ); ?>');
                    location.reload();
                } else {
                    alert('<?php esc_html_e( 'Error:', 'shahi-backup' ); ?> ' + response.data.message);
                    $btn.prop('disabled', false).html($btn.data('original-text'));
                }
            },
            error: function() {
                alert('<?php esc_html_e( 'An error occurred. Please try again.', 'shahi-backup' ); ?>');
                $btn.prop('disabled', false).html($btn.data('original-text'));
            }
        });
    });
    
    // Store original button text
    $('.shahi-create-backup').each(function() {
        $(this).data('original-text', $(this).html());
    });
});
</script>
