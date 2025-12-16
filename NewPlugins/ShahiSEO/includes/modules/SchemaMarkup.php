<?php
/**
 * Schema Markup Module
 *
 * @package ShahiSEO
 */

namespace ShahiSEO\Modules;

/**
 * SchemaMarkup Class
 */
class SchemaMarkup {
    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_head', array( $this, 'render_schema' ), 10 );
    }

    /**
     * Render schema markup
     */
    public function render_schema() {
        if ( ! get_option( 'shahi_seo_schema_enabled', true ) ) {
            return;
        }

        if ( is_singular( 'post' ) ) {
            $this->render_article_schema();
        } elseif ( is_singular( 'page' ) ) {
            $this->render_webpage_schema();
        } elseif ( is_home() || is_front_page() ) {
            $this->render_website_schema();
        }
    }

    /**
     * Render article schema
     */
    private function render_article_schema() {
        global $post;

        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => get_the_title( $post ),
            'description' => wp_trim_words( $post->post_content, 30 ),
            'datePublished' => get_the_date( 'c', $post ),
            'dateModified' => get_the_modified_date( 'c', $post ),
            'author' => array(
                '@type' => 'Person',
                'name' => get_the_author_meta( 'display_name', $post->post_author ),
            ),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => get_bloginfo( 'name' ),
                'logo' => array(
                    '@type' => 'ImageObject',
                    'url' => get_site_icon_url(),
                ),
            ),
        );

        $thumbnail = get_the_post_thumbnail_url( $post, 'large' );
        if ( $thumbnail ) {
            $schema['image'] = $thumbnail;
        }

        $this->output_schema( $schema );
    }

    /**
     * Render webpage schema
     */
    private function render_webpage_schema() {
        global $post;

        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => get_the_title( $post ),
            'description' => wp_trim_words( $post->post_content, 30 ),
            'url' => get_permalink( $post ),
        );

        $this->output_schema( $schema );
    }

    /**
     * Render website schema
     */
    private function render_website_schema() {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => get_bloginfo( 'name' ),
            'description' => get_bloginfo( 'description' ),
            'url' => home_url(),
            'potentialAction' => array(
                '@type' => 'SearchAction',
                'target' => home_url( '/?s={search_term_string}' ),
                'query-input' => 'required name=search_term_string',
            ),
        );

        $this->output_schema( $schema );
    }

    /**
     * Output schema JSON-LD
     *
     * @param array $schema Schema data.
     */
    private function output_schema( $schema ) {
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "\n";
        echo '</script>' . "\n";
    }
}
