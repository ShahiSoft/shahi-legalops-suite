<?php
/**
 * Meta Tags Module
 *
 * @package ShahiSEO
 */

namespace ShahiSEO\Modules;

/**
 * MetaTags Class
 */
class MetaTags {
    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_head', array( $this, 'render_meta_tags' ), 1 );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_meta_box' ) );
    }

    /**
     * Render meta tags in head
     */
    public function render_meta_tags() {
        if ( ! is_singular() ) {
            return;
        }

        global $post;
        $meta = $this->get_post_meta( $post->ID );

        // Title tag
        if ( ! empty( $meta['meta_title'] ) ) {
            echo '<title>' . esc_html( $meta['meta_title'] ) . '</title>' . "\n";
        }

        // Meta description
        if ( ! empty( $meta['meta_description'] ) ) {
            echo '<meta name="description" content="' . esc_attr( $meta['meta_description'] ) . '">' . "\n";
        }

        // Meta keywords
        if ( ! empty( $meta['meta_keywords'] ) ) {
            echo '<meta name="keywords" content="' . esc_attr( $meta['meta_keywords'] ) . '">' . "\n";
        }

        // Canonical URL
        $canonical = ! empty( $meta['canonical_url'] ) ? $meta['canonical_url'] : get_permalink( $post->ID );
        echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";

        // Robots meta
        $robots = array();
        if ( ! $meta['robots_index'] ) {
            $robots[] = 'noindex';
        }
        if ( ! $meta['robots_follow'] ) {
            $robots[] = 'nofollow';
        }
        if ( ! empty( $robots ) ) {
            echo '<meta name="robots" content="' . esc_attr( implode( ', ', $robots ) ) . '">' . "\n";
        }

        // Open Graph tags
        $this->render_og_tags( $meta, $post );

        // Twitter Card tags
        $this->render_twitter_tags( $meta, $post );
    }

    /**
     * Render Open Graph tags
     *
     * @param array   $meta Meta data.
     * @param WP_Post $post Post object.
     */
    private function render_og_tags( $meta, $post ) {
        $og_title = ! empty( $meta['og_title'] ) ? $meta['og_title'] : get_the_title( $post );
        $og_description = ! empty( $meta['og_description'] ) ? $meta['og_description'] : wp_trim_words( $post->post_content, 30 );
        $og_image = ! empty( $meta['og_image'] ) ? $meta['og_image'] : get_the_post_thumbnail_url( $post, 'large' );
        $og_url = get_permalink( $post );

        echo '<meta property="og:title" content="' . esc_attr( $og_title ) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr( $og_description ) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url( $og_url ) . '">' . "\n";
        echo '<meta property="og:type" content="article">' . "\n";

        if ( $og_image ) {
            echo '<meta property="og:image" content="' . esc_url( $og_image ) . '">' . "\n";
        }
    }

    /**
     * Render Twitter Card tags
     *
     * @param array   $meta Meta data.
     * @param WP_Post $post Post object.
     */
    private function render_twitter_tags( $meta, $post ) {
        $twitter_title = ! empty( $meta['twitter_title'] ) ? $meta['twitter_title'] : get_the_title( $post );
        $twitter_description = ! empty( $meta['twitter_description'] ) ? $meta['twitter_description'] : wp_trim_words( $post->post_content, 30 );
        $twitter_image = ! empty( $meta['twitter_image'] ) ? $meta['twitter_image'] : get_the_post_thumbnail_url( $post, 'large' );

        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr( $twitter_title ) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr( $twitter_description ) . '">' . "\n";

        if ( $twitter_image ) {
            echo '<meta name="twitter:image" content="' . esc_url( $twitter_image ) . '">' . "\n";
        }

        $twitter_username = get_option( 'shahi_seo_twitter_username' );
        if ( $twitter_username ) {
            echo '<meta name="twitter:site" content="@' . esc_attr( $twitter_username ) . '">' . "\n";
        }
    }

    /**
     * Get post meta data
     *
     * @param int $post_id Post ID.
     * @return array
     */
    public function get_post_meta( $post_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_seo_meta';

        $meta = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE post_id = %d", $post_id ),
            ARRAY_A
        );

        if ( ! $meta ) {
            return array(
                'meta_title' => '',
                'meta_description' => '',
                'meta_keywords' => '',
                'canonical_url' => '',
                'og_title' => '',
                'og_description' => '',
                'og_image' => '',
                'twitter_title' => '',
                'twitter_description' => '',
                'twitter_image' => '',
                'robots_index' => 1,
                'robots_follow' => 1,
            );
        }

        return $meta;
    }

    /**
     * Add meta box
     */
    public function add_meta_box() {
        $post_types = get_option( 'shahi_seo_sitemap_post_types', array( 'post', 'page' ) );

        foreach ( $post_types as $post_type ) {
            add_meta_box(
                'shahi-seo-meta',
                __( 'SEO Settings', 'shahi-seo' ),
                array( $this, 'render_meta_box' ),
                $post_type,
                'normal',
                'high'
            );
        }
    }

    /**
     * Render meta box
     *
     * @param WP_Post $post Post object.
     */
    public function render_meta_box( $post ) {
        wp_nonce_field( 'shahi_seo_meta_box', 'shahi_seo_meta_box_nonce' );
        $meta = $this->get_post_meta( $post->ID );
        ?>
        <table class="form-table">
            <tr>
                <th><label for="shahi_seo_meta_title"><?php esc_html_e( 'Meta Title', 'shahi-seo' ); ?></label></th>
                <td>
                    <input type="text" id="shahi_seo_meta_title" name="shahi_seo_meta_title" value="<?php echo esc_attr( $meta['meta_title'] ); ?>" class="large-text">
                    <p class="description"><?php esc_html_e( 'Leave blank to use post title', 'shahi-seo' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="shahi_seo_meta_description"><?php esc_html_e( 'Meta Description', 'shahi-seo' ); ?></label></th>
                <td>
                    <textarea id="shahi_seo_meta_description" name="shahi_seo_meta_description" rows="3" class="large-text"><?php echo esc_textarea( $meta['meta_description'] ); ?></textarea>
                    <p class="description"><?php esc_html_e( 'Recommended: 150-160 characters', 'shahi-seo' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="shahi_seo_meta_keywords"><?php esc_html_e( 'Meta Keywords', 'shahi-seo' ); ?></label></th>
                <td>
                    <input type="text" id="shahi_seo_meta_keywords" name="shahi_seo_meta_keywords" value="<?php echo esc_attr( $meta['meta_keywords'] ); ?>" class="large-text">
                    <p class="description"><?php esc_html_e( 'Comma-separated keywords', 'shahi-seo' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="shahi_seo_canonical_url"><?php esc_html_e( 'Canonical URL', 'shahi-seo' ); ?></label></th>
                <td>
                    <input type="url" id="shahi_seo_canonical_url" name="shahi_seo_canonical_url" value="<?php echo esc_url( $meta['canonical_url'] ); ?>" class="large-text">
                    <p class="description"><?php esc_html_e( 'Leave blank to use permalink', 'shahi-seo' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Robots', 'shahi-seo' ); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="shahi_seo_robots_index" value="1" <?php checked( $meta['robots_index'], 1 ); ?>>
                        <?php esc_html_e( 'Allow search engines to index this page', 'shahi-seo' ); ?>
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="shahi_seo_robots_follow" value="1" <?php checked( $meta['robots_follow'], 1 ); ?>>
                        <?php esc_html_e( 'Allow search engines to follow links on this page', 'shahi-seo' ); ?>
                    </label>
                </td>
            </tr>
        </table>

        <h3><?php esc_html_e( 'Social Media', 'shahi-seo' ); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="shahi_seo_og_title"><?php esc_html_e( 'Open Graph Title', 'shahi-seo' ); ?></label></th>
                <td>
                    <input type="text" id="shahi_seo_og_title" name="shahi_seo_og_title" value="<?php echo esc_attr( $meta['og_title'] ); ?>" class="large-text">
                </td>
            </tr>
            <tr>
                <th><label for="shahi_seo_og_description"><?php esc_html_e( 'Open Graph Description', 'shahi-seo' ); ?></label></th>
                <td>
                    <textarea id="shahi_seo_og_description" name="shahi_seo_og_description" rows="3" class="large-text"><?php echo esc_textarea( $meta['og_description'] ); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><label for="shahi_seo_og_image"><?php esc_html_e( 'Open Graph Image URL', 'shahi-seo' ); ?></label></th>
                <td>
                    <input type="url" id="shahi_seo_og_image" name="shahi_seo_og_image" value="<?php echo esc_url( $meta['og_image'] ); ?>" class="large-text">
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save meta box
     *
     * @param int $post_id Post ID.
     */
    public function save_meta_box( $post_id ) {
        if ( ! isset( $_POST['shahi_seo_meta_box_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['shahi_seo_meta_box_nonce'], 'shahi_seo_meta_box' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'shahi_seo_meta';

        $data = array(
            'post_id' => $post_id,
            'meta_title' => isset( $_POST['shahi_seo_meta_title'] ) ? sanitize_text_field( $_POST['shahi_seo_meta_title'] ) : '',
            'meta_description' => isset( $_POST['shahi_seo_meta_description'] ) ? sanitize_textarea_field( $_POST['shahi_seo_meta_description'] ) : '',
            'meta_keywords' => isset( $_POST['shahi_seo_meta_keywords'] ) ? sanitize_text_field( $_POST['shahi_seo_meta_keywords'] ) : '',
            'canonical_url' => isset( $_POST['shahi_seo_canonical_url'] ) ? esc_url_raw( $_POST['shahi_seo_canonical_url'] ) : '',
            'og_title' => isset( $_POST['shahi_seo_og_title'] ) ? sanitize_text_field( $_POST['shahi_seo_og_title'] ) : '',
            'og_description' => isset( $_POST['shahi_seo_og_description'] ) ? sanitize_textarea_field( $_POST['shahi_seo_og_description'] ) : '',
            'og_image' => isset( $_POST['shahi_seo_og_image'] ) ? esc_url_raw( $_POST['shahi_seo_og_image'] ) : '',
            'twitter_title' => isset( $_POST['shahi_seo_twitter_title'] ) ? sanitize_text_field( $_POST['shahi_seo_twitter_title'] ) : '',
            'twitter_description' => isset( $_POST['shahi_seo_twitter_description'] ) ? sanitize_textarea_field( $_POST['shahi_seo_twitter_description'] ) : '',
            'twitter_image' => isset( $_POST['shahi_seo_twitter_image'] ) ? esc_url_raw( $_POST['shahi_seo_twitter_image'] ) : '',
            'robots_index' => isset( $_POST['shahi_seo_robots_index'] ) ? 1 : 0,
            'robots_follow' => isset( $_POST['shahi_seo_robots_follow'] ) ? 1 : 0,
        );

        // Check if record exists
        $exists = $wpdb->get_var(
            $wpdb->prepare( "SELECT id FROM $table WHERE post_id = %d", $post_id )
        );

        if ( $exists ) {
            $wpdb->update(
                $table,
                $data,
                array( 'post_id' => $post_id ),
                array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' ),
                array( '%d' )
            );
        } else {
            $wpdb->insert(
                $table,
                $data,
                array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' )
            );
        }
    }
}
