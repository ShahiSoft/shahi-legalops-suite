<?php
/**
 * Cache Module
 *
 * Provides advanced caching mechanisms for database queries, API responses,
 * and rendered templates to improve performance.
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
 * Cache Module Class
 *
 * Implements intelligent caching strategies for improved performance.
 *
 * @since 1.0.0
 */
class Cache_Module extends Module {
    
    /**
     * Get module unique key
     *
     * @since 1.0.0
     * @return string Module key
     */
    public function get_key() {
        return 'cache';
    }
    
    /**
     * Get module name
     *
     * @since 1.0.0
     * @return string Module name
     */
    public function get_name() {
        return __('Advanced Caching', 'shahi-legalops-suite');
    }
    
    /**
     * Get module description
     *
     * @since 1.0.0
     * @return string Module description
     */
    public function get_description() {
        return __('Improve performance with intelligent caching mechanisms for database queries, API responses, and rendered templates.', 'shahi-legalops-suite');
    }
    
    /**
     * Get module icon
     *
     * @since 1.0.0
     * @return string Icon class
     */
    public function get_icon() {
        return 'dashicons-performance';
    }
    
    /**
     * Get module category
     *
     * @since 1.0.0
     * @return string Category
     */
    public function get_category() {
        return 'performance';
    }
    
    /**
     * Initialize module
     *
     * @since 1.0.0
     * @return void
     */
    public function init() {
        // Set default cache duration
        if (!$this->get_setting('cache_duration')) {
            $this->update_setting('cache_duration', 3600); // 1 hour default
        }
        
        // Add cache clearing hooks
        add_action('save_post', [$this, 'clear_post_cache']);
        add_action('deleted_post', [$this, 'clear_post_cache']);
        
        // Add admin toolbar clear cache button
        add_action('admin_bar_menu', [$this, 'add_clear_cache_button'], 100);
    }
    
    /**
     * Get cached data
     *
     * @since 1.0.0
     * @param string $key Cache key.
     * @return mixed|false Cached data or false if not found
     */
    public function get($key) {
        return get_transient($this->get_cache_key($key));
    }
    
    /**
     * Set cached data
     *
     * @since 1.0.0
     * @param string $key        Cache key.
     * @param mixed  $data       Data to cache.
     * @param int    $expiration Expiration time in seconds (optional).
     * @return bool True on success, false on failure
     */
    public function set($key, $data, $expiration = null) {
        if ($expiration === null) {
            $expiration = $this->get_setting('cache_duration', 3600);
        }
        
        return set_transient($this->get_cache_key($key), $data, $expiration);
    }
    
    /**
     * Delete cached data
     *
     * @since 1.0.0
     * @param string $key Cache key.
     * @return bool True on success, false on failure
     */
    public function delete($key) {
        return delete_transient($this->get_cache_key($key));
    }
    
    /**
     * Clear all plugin caches
     *
     * [PLACEHOLDER - Mock implementation for demonstration]
     * Production should clear all transients with 'shahi_cache_' prefix
     *
     * @since 1.0.0
     * @return bool True on success
     */
    public function clear_all() {
        global $wpdb;
        
        // Delete all plugin transients
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_shahi_cache_%' 
             OR option_name LIKE '_transient_timeout_shahi_cache_%'"
        );
        
        return true;
    }
    
    /**
     * Clear post-related caches
     *
     * @since 1.0.0
     * @param int $post_id Post ID.
     * @return void
     */
    public function clear_post_cache($post_id) {
        $this->delete('post_' . $post_id);
    }
    
    /**
     * Add clear cache button to admin toolbar
     *
     * @since 1.0.0
     * @param object $wp_admin_bar Admin bar object.
     * @return void
     */
    public function add_clear_cache_button($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $wp_admin_bar->add_node([
            'id' => 'shahi-clear-cache',
            'title' => '<span class="ab-icon dashicons dashicons-update"></span>' . __('Clear Cache', 'shahi-legalops-suite'),
            'href' => wp_nonce_url(admin_url('admin-post.php?action=shahi_clear_cache'), 'shahi_clear_cache'),
        ]);
    }
    
    /**
     * Get prefixed cache key
     *
     * @since 1.0.0
     * @param string $key Original cache key.
     * @return string Prefixed cache key
     */
    private function get_cache_key($key) {
        return 'shahi_cache_' . $key;
    }
    
    /**
     * Get module settings URL
     *
     * @since 1.0.0
     * @return string Settings URL
     */
    public function get_settings_url() {
        return admin_url('admin.php?page=shahi-settings&tab=performance');
    }
}
