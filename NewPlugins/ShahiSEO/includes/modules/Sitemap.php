<?php
/**
 * Sitemap Module
 *
 * @package ShahiSEO
 */

namespace ShahiSEO\Modules;

/**
 * Sitemap Class
 */
class Sitemap {
    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'init', array( $this, 'add_rewrite_rules' ) );
        add_action( 'template_redirect', array( $this, 'handle_sitemap_request' ) );
    }

    /**
     * Add rewrite rules
     */
    public function add_rewrite_rules() {
        add_rewrite_rule( '^sitemap\.xml$', 'index.php?shahi_seo_sitemap=1', 'top' );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
    }

    /**
     * Add query vars
     *
     * @param array $vars Query vars.
     * @return array
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'shahi_seo_sitemap';
        return $vars;
    }

    /**
     * Handle sitemap request
     */
    public function handle_sitemap_request() {
        if ( ! get_query_var( 'shahi_seo_sitemap' ) ) {
            return;
        }

        if ( ! get_option( 'shahi_seo_sitemap_enabled', true ) ) {
            return;
        }

        header( 'Content-Type: application/xml; charset=utf-8' );
        echo $this->generate_sitemap();
        exit;
    }

    /**
     * Generate sitemap XML
     *
     * @return string
     */
    public function generate_sitemap() {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Add homepage
        $xml .= $this->add_url( home_url(), get_lastpostmodified( 'gmt' ), 'daily', '1.0' );

        // Add posts and pages
        $post_types = get_option( 'shahi_seo_sitemap_post_types', array( 'post', 'page' ) );

        foreach ( $post_types as $post_type ) {
            $posts = get_posts( array(
                'post_type' => $post_type,
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'orderby' => 'modified',
                'order' => 'DESC',
            ) );

            foreach ( $posts as $post ) {
                $xml .= $this->add_url(
                    get_permalink( $post ),
                    get_post_modified_time( 'c', false, $post ),
                    $this->get_change_frequency( $post ),
                    $this->get_priority( $post )
                );
            }
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Add URL to sitemap
     *
     * @param string $loc Location.
     * @param string $lastmod Last modified date.
     * @param string $changefreq Change frequency.
     * @param string $priority Priority.
     * @return string
     */
    private function add_url( $loc, $lastmod, $changefreq, $priority ) {
        $xml = '  <url>' . "\n";
        $xml .= '    <loc>' . esc_url( $loc ) . '</loc>' . "\n";
        $xml .= '    <lastmod>' . esc_html( $lastmod ) . '</lastmod>' . "\n";
        $xml .= '    <changefreq>' . esc_html( $changefreq ) . '</changefreq>' . "\n";
        $xml .= '    <priority>' . esc_html( $priority ) . '</priority>' . "\n";
        $xml .= '  </url>' . "\n";
        return $xml;
    }

    /**
     * Get change frequency
     *
     * @param WP_Post $post Post object.
     * @return string
     */
    private function get_change_frequency( $post ) {
        $age_days = ( time() - strtotime( $post->post_modified ) ) / DAY_IN_SECONDS;

        if ( $age_days < 7 ) {
            return 'daily';
        } elseif ( $age_days < 30 ) {
            return 'weekly';
        } else {
            return 'monthly';
        }
    }

    /**
     * Get priority
     *
     * @param WP_Post $post Post object.
     * @return string
     */
    private function get_priority( $post ) {
        if ( is_front_page() ) {
            return '1.0';
        } elseif ( $post->post_type === 'page' ) {
            return '0.8';
        } elseif ( $post->post_type === 'post' ) {
            return '0.6';
        }

        return '0.5';
    }
}
