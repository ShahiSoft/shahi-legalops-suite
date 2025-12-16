<?php
/**
 * Custom Post Type Module
 *
 * Provides functionality to create and manage custom post types
 * with a user-friendly interface.
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
 * Custom Post Type Module Class
 *
 * Allows easy creation and management of custom post types.
 *
 * @since 1.0.0
 */
class CustomPostType_Module extends Module {
    
    /**
     * Get module unique key
     *
     * @since 1.0.0
     * @return string Module key
     */
    public function get_key() {
        return 'custom_post_type';
    }
    
    /**
     * Get module name
     *
     * @since 1.0.0
     * @return string Module name
     */
    public function get_name() {
        return __('Custom Post Types', 'shahi-legalops-suite');
    }
    
    /**
     * Get module description
     *
     * @since 1.0.0
     * @return string Module description
     */
    public function get_description() {
        return __('Create and manage custom post types with a user-friendly interface. Perfect for portfolios, testimonials, products, and more.', 'shahi-legalops-suite');
    }
    
    /**
     * Get module icon
     *
     * @since 1.0.0
     * @return string Icon class
     */
    public function get_icon() {
        return 'dashicons-admin-post';
    }
    
    /**
     * Get module category
     *
     * @since 1.0.0
     * @return string Category
     */
    public function get_category() {
        return 'content';
    }
    
    /**
     * Initialize module
     *
     * @since 1.0.0
     * @return void
     */
    public function init() {
        // Register custom post types
        add_action('init', [$this, 'register_custom_post_types']);
        
        // Add admin menu for managing custom post types
        add_action('admin_menu', [$this, 'add_admin_menu'], 20);
    }
    
    /**
     * Register custom post types
     *
     * [PLACEHOLDER - Mock implementation for demonstration]
     * This registers example post types. Production should:
     * - Load post types from database
     * - Allow user to create/edit/delete post types via admin
     * - Support custom taxonomies
     * - Support custom fields
     *
     * @since 1.0.0
     * @return void
     */
    public function register_custom_post_types() {
        $post_types = $this->get_setting('post_types', []);
        
        // Register example post type if none configured
        if (empty($post_types)) {
            $this->register_portfolio_post_type();
        }
        
        // Register configured post types
        foreach ($post_types as $post_type) {
            register_post_type($post_type['key'], $post_type['args']);
        }
    }
    
    /**
     * Register example portfolio post type
     *
     * [PLACEHOLDER - Mock example post type for demonstration]
     *
     * @since 1.0.0
     * @return void
     */
    private function register_portfolio_post_type() {
        $labels = [
            'name' => __('Portfolio', 'shahi-legalops-suite'),
            'singular_name' => __('Portfolio Item', 'shahi-legalops-suite'),
            'add_new' => __('Add New', 'shahi-legalops-suite'),
            'add_new_item' => __('Add New Portfolio Item', 'shahi-legalops-suite'),
            'edit_item' => __('Edit Portfolio Item', 'shahi-legalops-suite'),
            'new_item' => __('New Portfolio Item', 'shahi-legalops-suite'),
            'view_item' => __('View Portfolio Item', 'shahi-legalops-suite'),
            'search_items' => __('Search Portfolio', 'shahi-legalops-suite'),
            'not_found' => __('No portfolio items found', 'shahi-legalops-suite'),
        ];
        
        $args = [
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-portfolio',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'show_in_rest' => true,
        ];
        
        register_post_type('shahi_portfolio', $args);
    }
    
    /**
     * Add admin menu
     *
     * @since 1.0.0
     * @return void
     */
    public function add_admin_menu() {
        add_submenu_page(
            'shahi-legalops-suite',
            __('Custom Post Types', 'shahi-legalops-suite'),
            __('Post Types', 'shahi-legalops-suite'),
            'manage_options',
            'shahi-post-types',
            [$this, 'render_admin_page']
        );
    }
    
    /**
     * Render admin page
     *
     * [PLACEHOLDER - Mock implementation for demonstration]
     *
     * @since 1.0.0
     * @return void
     */
    public function render_admin_page() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Custom Post Types', 'shahi-legalops-suite') . '</h1>';
        echo '<p>' . esc_html__('Manage your custom post types here.', 'shahi-legalops-suite') . '</p>';
        echo '<p><em>' . esc_html__('[PLACEHOLDER] Full post type management UI to be implemented.', 'shahi-legalops-suite') . '</em></p>';
        echo '</div>';
    }
    
    /**
     * Get module settings URL
     *
     * @since 1.0.0
     * @return string Settings URL
     */
    public function get_settings_url() {
        return admin_url('admin.php?page=shahi-post-types');
    }
}
