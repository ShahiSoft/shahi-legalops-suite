<?php
/**
 * SEO Module
 *
 * Provides SEO optimization features including meta tags, Open Graph,
 * Twitter Cards, and XML sitemap generation.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Modules
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalopsSuite\Modules;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * SEO Module Class
 *
 * Handles SEO-related functionality for the plugin.
 *
 * @since 1.0.0
 */
class SEO_Module extends Module {
    
    /**
     * Get module unique key
     *
     * @since 1.0.0
     * @return string Module key
     */
    public function get_key() {
        return 'seo';
    }
    
    /**
     * Get module name
     *
     * @since 1.0.0
     * @return string Module name
     */
    public function get_name() {
        return __('SEO Optimization', 'shahi-legalops-suite');
    }
    
    /**
     * Get module description
     *
     * @since 1.0.0
     * @return string Module description
     */
    public function get_description() {
        return __('Advanced SEO features including meta tags, Open Graph, Twitter Cards, and XML sitemaps for better search engine visibility.', 'shahi-legalops-suite');
    }
    
    /**
     * Get module icon
     *
     * @since 1.0.0
     * @return string Icon class
     */
    public function get_icon() {
        return 'dashicons-search';
    }
    
    /**
     * Get module category
     *
     * @since 1.0.0
     * @return string Category
     */
    public function get_category() {
        return 'marketing';
    }
    
    /**
     * Initialize module
     *
     * @since 1.0.0
     * @return void
     */
    public function init() {
        // Add meta tags to head
        add_action('wp_head', [$this, 'add_meta_tags'], 1);
        
        // Add Open Graph tags
        add_action('wp_head', [$this, 'add_open_graph_tags'], 5);
        
        // Add Twitter Card tags
        add_action('wp_head', [$this, 'add_twitter_card_tags'], 10);
        
        // Register sitemap endpoint
        add_action('init', [$this, 'register_sitemap_endpoint']);
    }
    
    /**
     * Add meta tags to page head
     *
     * [PLACEHOLDER - Mock implementation for demonstration]
     * This is a simplified version. Production should include:
     * - Custom meta description per post
     * - Keyword optimization
     * - Canonical URLs
     * - Schema.org markup
     *
     * @since 1.0.0
     * @return void
     */
    public function add_meta_tags() {
        // Get meta description
        $description = $this->get_setting('default_description', get_bloginfo('description'));
        
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        echo '<!-- SEO Module Active - ShahiLegalopsSuite -->' . "\n";
    }
    
    /**
     * Add Open Graph tags
     *
     * [PLACEHOLDER - Mock implementation for demonstration]
     *
     * @since 1.0.0
     * @return void
     */
    public function add_open_graph_tags() {
        echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
        echo '<meta property="og:type" content="website">' . "\n";
        echo '<meta property="og:url" content="' . esc_url(get_permalink()) . '">' . "\n";
    }
    
    /**
     * Add Twitter Card tags
     *
     * [PLACEHOLDER - Mock implementation for demonstration]
     *
     * @since 1.0.0
     * @return void
     */
    public function add_twitter_card_tags() {
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr(get_the_title()) . '">' . "\n";
    }
    
    /**
     * Register sitemap endpoint
     *
     * [PLACEHOLDER - Mock implementation for demonstration]
     *
     * @since 1.0.0
     * @return void
     */
    public function register_sitemap_endpoint() {
        // Placeholder - Would register rewrite rule for /sitemap.xml
    }
    
    /**
     * Get module settings URL
     *
     * @since 1.0.0
     * @return string Settings URL
     */
    public function get_settings_url() {
        return admin_url('admin.php?page=shahi-settings&tab=seo');
    }
}
