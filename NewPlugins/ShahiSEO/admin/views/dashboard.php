<?php
/**
 * Dashboard View
 *
 * @package ShahiSEO
 */

// Get stats
global $wpdb;
$meta_table = $wpdb->prefix . 'shahi_seo_meta';
$posts_with_meta = $wpdb->get_var( "SELECT COUNT(*) FROM $meta_table" );
$total_posts = wp_count_posts( 'post' )->publish + wp_count_posts( 'page' )->publish;
$coverage = $total_posts > 0 ? round( ( $posts_with_meta / $total_posts ) * 100 ) : 0;

// Get recent optimized posts
$recent_optimized = $wpdb->get_results(
    "SELECT m.post_id, m.meta_title, m.updated_at 
    FROM $meta_table m 
    ORDER BY m.updated_at DESC 
    LIMIT 5",
    ARRAY_A
);

?>
<div class="wrap">
    <h1><?php esc_html_e( 'Shahi SEO Dashboard', 'shahi-seo' ); ?></h1>

    <div class="shahi-seo-dashboard" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
        
        <!-- SEO Coverage -->
        <div class="shahi-card" style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0; display: flex; align-items: center; gap: 10px;">
                <span class="dashicons dashicons-search" style="font-size: 28px; color: #0073aa;"></span>
                <?php esc_html_e( 'SEO Coverage', 'shahi-seo' ); ?>
            </h2>
            <div class="stat-value" style="font-size: 48px; font-weight: bold; color: #0073aa; margin: 20px 0;">
                <?php echo esc_html( $coverage ); ?>%
            </div>
            <p style="color: #666; margin-bottom: 15px;">
                <?php
                echo esc_html(
                    sprintf(
                        /* translators: 1: posts with meta, 2: total posts */
                        __( '%1$d of %2$d posts optimized', 'shahi-seo' ),
                        $posts_with_meta,
                        $total_posts
                    )
                );
                ?>
            </p>
            <a href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>" class="button button-primary">
                <?php esc_html_e( 'Optimize Posts', 'shahi-seo' ); ?>
            </a>
        </div>

        <!-- Quick Actions -->
        <div class="shahi-card" style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0;"><?php esc_html_e( 'Quick Actions', 'shahi-seo' ); ?></h2>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-seo-meta' ) ); ?>" class="button" style="justify-content: flex-start;">
                    <span class="dashicons dashicons-tag"></span>
                    <?php esc_html_e( 'Manage Meta Tags', 'shahi-seo' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-seo-schema' ) ); ?>" class="button" style="justify-content: flex-start;">
                    <span class="dashicons dashicons-editor-code"></span>
                    <?php esc_html_e( 'Configure Schema', 'shahi-seo' ); ?>
                </a>
                <a href="<?php echo esc_url( home_url( '/sitemap.xml' ) ); ?>" class="button" style="justify-content: flex-start;" target="_blank">
                    <span class="dashicons dashicons-networking"></span>
                    <?php esc_html_e( 'View Sitemap', 'shahi-seo' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=shahi-seo-settings' ) ); ?>" class="button" style="justify-content: flex-start;">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php esc_html_e( 'Settings', 'shahi-seo' ); ?>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="shahi-card" style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0;"><?php esc_html_e( 'Recently Optimized', 'shahi-seo' ); ?></h2>
            <?php if ( ! empty( $recent_optimized ) ) : ?>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <?php foreach ( $recent_optimized as $item ) : ?>
                        <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                            <a href="<?php echo esc_url( get_edit_post_link( $item['post_id'] ) ); ?>">
                                <?php echo esc_html( $item['meta_title'] ? $item['meta_title'] : get_the_title( $item['post_id'] ) ); ?>
                            </a>
                            <br>
                            <small style="color: #666;">
                                <?php echo esc_html( mysql2date( 'M j, Y g:i a', $item['updated_at'] ) ); ?>
                            </small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p style="color: #666;"><?php esc_html_e( 'No optimized posts yet.', 'shahi-seo' ); ?></p>
            <?php endif; ?>
        </div>

        <!-- SEO Status -->
        <div class="shahi-card" style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0;"><?php esc_html_e( 'SEO Status', 'shahi-seo' ); ?></h2>
            <ul style="list-style: none; padding: 0; margin: 0;">
                <li style="padding: 10px 0; display: flex; align-items: center; justify-content: space-between;">
                    <span><?php esc_html_e( 'Meta Tags', 'shahi-seo' ); ?></span>
                    <span class="dashicons dashicons-yes-alt" style="color: #46b450; font-size: 20px;"></span>
                </li>
                <li style="padding: 10px 0; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #eee;">
                    <span><?php esc_html_e( 'Schema Markup', 'shahi-seo' ); ?></span>
                    <?php if ( get_option( 'shahi_seo_schema_enabled', true ) ) : ?>
                        <span class="dashicons dashicons-yes-alt" style="color: #46b450; font-size: 20px;"></span>
                    <?php else : ?>
                        <span class="dashicons dashicons-marker" style="color: #dc3232; font-size: 20px;"></span>
                    <?php endif; ?>
                </li>
                <li style="padding: 10px 0; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #eee;">
                    <span><?php esc_html_e( 'XML Sitemap', 'shahi-seo' ); ?></span>
                    <?php if ( get_option( 'shahi_seo_sitemap_enabled', true ) ) : ?>
                        <span class="dashicons dashicons-yes-alt" style="color: #46b450; font-size: 20px;"></span>
                    <?php else : ?>
                        <span class="dashicons dashicons-marker" style="color: #dc3232; font-size: 20px;"></span>
                    <?php endif; ?>
                </li>
                <li style="padding: 10px 0; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #eee;">
                    <span><?php esc_html_e( 'Open Graph', 'shahi-seo' ); ?></span>
                    <span class="dashicons dashicons-yes-alt" style="color: #46b450; font-size: 20px;"></span>
                </li>
                <li style="padding: 10px 0; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #eee;">
                    <span><?php esc_html_e( 'Twitter Cards', 'shahi-seo' ); ?></span>
                    <span class="dashicons dashicons-yes-alt" style="color: #46b450; font-size: 20px;"></span>
                </li>
            </ul>
        </div>
    </div>
</div>
