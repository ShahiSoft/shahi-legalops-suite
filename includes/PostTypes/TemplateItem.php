<?php
/**
 * Template Item Post Type
 *
 * Example custom post type implementation for template items.
 *
 * @package     ShahiLegalopsSuite
 * @subpackage  PostTypes
 * @version     1.0.0
 * @since       1.0.0
 * @author      ShahiLegalopsSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalopsSuite\PostTypes;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TemplateItem
 *
 * Registers and manages the Template Item custom post type.
 *
 * @since 1.0.0
 */
class TemplateItem {
    
    /**
     * Post type key
     *
     * @since 1.0.0
     * @var string
     */
    private $post_type = 'slos_template_item';
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        // Constructor intentionally empty - registration happens via PostTypeManager
    }
    
    /**
     * Get post type key
     *
     * @since 1.0.0
     * @return string Post type key.
     */
    public function get_post_type_key() {
        return $this->post_type;
    }
    
    /**
     * Register the post type
     *
     * @since 1.0.0
     * @return void
     */
    public function register() {
        $labels = array(
            'name'                  => _x('Template Items', 'Post Type General Name', 'shahi-legalops-suite'),
            'singular_name'         => _x('Template Item', 'Post Type Singular Name', 'shahi-legalops-suite'),
            'menu_name'             => __('Template Items', 'shahi-legalops-suite'),
            'name_admin_bar'        => __('Template Item', 'shahi-legalops-suite'),
            'archives'              => __('Item Archives', 'shahi-legalops-suite'),
            'attributes'            => __('Item Attributes', 'shahi-legalops-suite'),
            'parent_item_colon'     => __('Parent Item:', 'shahi-legalops-suite'),
            'all_items'             => __('All Items', 'shahi-legalops-suite'),
            'add_new_item'          => __('Add New Item', 'shahi-legalops-suite'),
            'add_new'               => __('Add New', 'shahi-legalops-suite'),
            'new_item'              => __('New Item', 'shahi-legalops-suite'),
            'edit_item'             => __('Edit Item', 'shahi-legalops-suite'),
            'update_item'           => __('Update Item', 'shahi-legalops-suite'),
            'view_item'             => __('View Item', 'shahi-legalops-suite'),
            'view_items'            => __('View Items', 'shahi-legalops-suite'),
            'search_items'          => __('Search Item', 'shahi-legalops-suite'),
            'not_found'             => __('Not found', 'shahi-legalops-suite'),
            'not_found_in_trash'    => __('Not found in Trash', 'shahi-legalops-suite'),
            'featured_image'        => __('Featured Image', 'shahi-legalops-suite'),
            'set_featured_image'    => __('Set featured image', 'shahi-legalops-suite'),
            'remove_featured_image' => __('Remove featured image', 'shahi-legalops-suite'),
            'use_featured_image'    => __('Use as featured image', 'shahi-legalops-suite'),
            'insert_into_item'      => __('Insert into item', 'shahi-legalops-suite'),
            'uploaded_to_this_item' => __('Uploaded to this item', 'shahi-legalops-suite'),
            'items_list'            => __('Items list', 'shahi-legalops-suite'),
            'items_list_navigation' => __('Items list navigation', 'shahi-legalops-suite'),
            'filter_items_list'     => __('Filter items list', 'shahi-legalops-suite'),
        );
        
        $args = array(
            'label'                 => __('Template Item', 'shahi-legalops-suite'),
            'description'           => __('Template items for ShahiLegalopsSuite plugin', 'shahi-legalops-suite'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'excerpt', 'thumbnail', 'author', 'comments', 'revisions', 'custom-fields'),
            'taxonomies'            => array(),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => 'shahi-legalops-suite',
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-layout',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
            'rest_base'             => 'template-items',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'rewrite'               => array(
                'slug'       => 'template-items',
                'with_front' => false,
                'pages'      => true,
                'feeds'      => true,
            ),
        );
        
        register_post_type($this->post_type, $args);
    }
    
    /**
     * Register custom taxonomies
     *
     * @since 1.0.0
     * @return void
     */
    public function register_taxonomies() {
        // Register Category taxonomy
        $category_labels = array(
            'name'                       => _x('Item Categories', 'Taxonomy General Name', 'shahi-legalops-suite'),
            'singular_name'              => _x('Item Category', 'Taxonomy Singular Name', 'shahi-legalops-suite'),
            'menu_name'                  => __('Categories', 'shahi-legalops-suite'),
            'all_items'                  => __('All Categories', 'shahi-legalops-suite'),
            'parent_item'                => __('Parent Category', 'shahi-legalops-suite'),
            'parent_item_colon'          => __('Parent Category:', 'shahi-legalops-suite'),
            'new_item_name'              => __('New Category Name', 'shahi-legalops-suite'),
            'add_new_item'               => __('Add New Category', 'shahi-legalops-suite'),
            'edit_item'                  => __('Edit Category', 'shahi-legalops-suite'),
            'update_item'                => __('Update Category', 'shahi-legalops-suite'),
            'view_item'                  => __('View Category', 'shahi-legalops-suite'),
            'separate_items_with_commas' => __('Separate categories with commas', 'shahi-legalops-suite'),
            'add_or_remove_items'        => __('Add or remove categories', 'shahi-legalops-suite'),
            'choose_from_most_used'      => __('Choose from the most used', 'shahi-legalops-suite'),
            'popular_items'              => __('Popular Categories', 'shahi-legalops-suite'),
            'search_items'               => __('Search Categories', 'shahi-legalops-suite'),
            'not_found'                  => __('Not Found', 'shahi-legalops-suite'),
            'no_terms'                   => __('No categories', 'shahi-legalops-suite'),
            'items_list'                 => __('Categories list', 'shahi-legalops-suite'),
            'items_list_navigation'      => __('Categories list navigation', 'shahi-legalops-suite'),
        );
        
        $category_args = array(
            'labels'                     => $category_labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
            'rest_base'                  => 'item-categories',
            'rest_controller_class'      => 'WP_REST_Terms_Controller',
            'rewrite'                    => array(
                'slug'       => 'item-category',
                'with_front' => false,
            ),
        );
        
        register_taxonomy('shahi_item_category', array($this->post_type), $category_args);
        
        // Register Tag taxonomy
        $tag_labels = array(
            'name'                       => _x('Item Tags', 'Taxonomy General Name', 'shahi-legalops-suite'),
            'singular_name'              => _x('Item Tag', 'Taxonomy Singular Name', 'shahi-legalops-suite'),
            'menu_name'                  => __('Tags', 'shahi-legalops-suite'),
            'all_items'                  => __('All Tags', 'shahi-legalops-suite'),
            'new_item_name'              => __('New Tag Name', 'shahi-legalops-suite'),
            'add_new_item'               => __('Add New Tag', 'shahi-legalops-suite'),
            'edit_item'                  => __('Edit Tag', 'shahi-legalops-suite'),
            'update_item'                => __('Update Tag', 'shahi-legalops-suite'),
            'view_item'                  => __('View Tag', 'shahi-legalops-suite'),
            'separate_items_with_commas' => __('Separate tags with commas', 'shahi-legalops-suite'),
            'add_or_remove_items'        => __('Add or remove tags', 'shahi-legalops-suite'),
            'choose_from_most_used'      => __('Choose from the most used', 'shahi-legalops-suite'),
            'popular_items'              => __('Popular Tags', 'shahi-legalops-suite'),
            'search_items'               => __('Search Tags', 'shahi-legalops-suite'),
            'not_found'                  => __('Not Found', 'shahi-legalops-suite'),
            'no_terms'                   => __('No tags', 'shahi-legalops-suite'),
            'items_list'                 => __('Tags list', 'shahi-legalops-suite'),
            'items_list_navigation'      => __('Tags list navigation', 'shahi-legalops-suite'),
        );
        
        $tag_args = array(
            'labels'                     => $tag_labels,
            'hierarchical'               => false,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
            'rest_base'                  => 'item-tags',
            'rest_controller_class'      => 'WP_REST_Terms_Controller',
            'rewrite'                    => array(
                'slug'       => 'item-tag',
                'with_front' => false,
            ),
        );
        
        register_taxonomy('shahi_item_tag', array($this->post_type), $tag_args);
    }
    
    /**
     * Get custom admin columns
     *
     * @since 1.0.0
     * @return array Custom columns.
     */
    public function get_admin_columns() {
        return array(
            'featured' => __('Featured', 'shahi-legalops-suite'),
            'status_badge' => __('Status', 'shahi-legalops-suite'),
            'item_type' => __('Type', 'shahi-legalops-suite'),
            'views' => __('Views', 'shahi-legalops-suite'),
        );
    }
    
    /**
     * Render admin column content
     *
     * @since 1.0.0
     * @param string $column  Column name.
     * @param int    $post_id Post ID.
     * @return void
     */
    public function render_admin_column($column, $post_id) {
        switch ($column) {
            case 'featured':
                $is_featured = get_post_meta($post_id, '_shahi_featured', true);
                if ($is_featured) {
                    echo '<span class="dashicons dashicons-star-filled" style="color: #f0b429;" title="' . esc_attr__('Featured', 'shahi-legalops-suite') . '"></span>';
                } else {
                    echo '<span class="dashicons dashicons-star-empty" style="color: #ccc;" title="' . esc_attr__('Not Featured', 'shahi-legalops-suite') . '"></span>';
                }
                break;
                
            case 'status_badge':
                $status = get_post_meta($post_id, '_shahi_status', true);
                $status = $status ? $status : 'active';
                $badge_colors = array(
                    'active' => '#46b450',
                    'inactive' => '#dc3232',
                    'pending' => '#ffb900',
                );
                $color = isset($badge_colors[$status]) ? $badge_colors[$status] : '#666';
                echo '<span style="display:inline-block;padding:3px 8px;background:' . esc_attr($color) . ';color:#fff;border-radius:3px;font-size:11px;font-weight:600;text-transform:uppercase;">' . esc_html($status) . '</span>';
                break;
                
            case 'item_type':
                $type = get_post_meta($post_id, '_shahi_item_type', true);
                echo $type ? esc_html($type) : '<span style="color:#999;">—</span>';
                break;
                
            case 'views':
                $views = get_post_meta($post_id, '_shahi_views', true);
                echo $views ? esc_html(number_format($views)) : '0';
                break;
        }
    }
    
    /**
     * Get sortable columns
     *
     * @since 1.0.0
     * @return array Sortable columns.
     */
    public function get_sortable_columns() {
        return array(
            'views' => 'views',
            'status_badge' => 'status',
        );
    }
    
    /**
     * Render quick edit fields
     *
     * @since 1.0.0
     * @param string $column_name Column name.
     * @return void
     */
    public function render_quick_edit($column_name) {
        // Only show once
        static $printed = false;
        if ($printed) {
            return;
        }
        $printed = true;
        
        ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <label>
                    <span class="title"><?php _e('Featured', 'shahi-legalops-suite'); ?></span>
                    <select name="shahi_featured">
                        <option value="">— <?php _e('No Change', 'shahi-legalops-suite'); ?> —</option>
                        <option value="1"><?php _e('Yes', 'shahi-legalops-suite'); ?></option>
                        <option value="0"><?php _e('No', 'shahi-legalops-suite'); ?></option>
                    </select>
                </label>
                
                <label>
                    <span class="title"><?php _e('Status', 'shahi-legalops-suite'); ?></span>
                    <select name="shahi_status">
                        <option value="">— <?php _e('No Change', 'shahi-legalops-suite'); ?> —</option>
                        <option value="active"><?php _e('Active', 'shahi-legalops-suite'); ?></option>
                        <option value="inactive"><?php _e('Inactive', 'shahi-legalops-suite'); ?></option>
                        <option value="pending"><?php _e('Pending', 'shahi-legalops-suite'); ?></option>
                    </select>
                </label>
                
                <label>
                    <span class="title"><?php _e('Item Type', 'shahi-legalops-suite'); ?></span>
                    <input type="text" name="shahi_item_type" value="" placeholder="<?php esc_attr_e('Enter type...', 'shahi-legalops-suite'); ?>">
                </label>
            </div>
        </fieldset>
        <?php
    }
    
    /**
     * Save quick edit data
     *
     * @since 1.0.0
     * @param int $post_id Post ID.
     * @return void
     */
    public function save_quick_edit($post_id) {
        // Featured
        if (isset($_POST['shahi_featured']) && $_POST['shahi_featured'] !== '') {
            $featured = $_POST['shahi_featured'] === '1' ? '1' : '';
            if ($featured) {
                update_post_meta($post_id, '_shahi_featured', $featured);
            } else {
                delete_post_meta($post_id, '_shahi_featured');
            }
        }
        
        // Status
        if (isset($_POST['shahi_status']) && $_POST['shahi_status'] !== '') {
            $status = sanitize_text_field($_POST['shahi_status']);
            update_post_meta($post_id, '_shahi_status', $status);
        }
        
        // Item Type
        if (isset($_POST['shahi_item_type'])) {
            $type = sanitize_text_field($_POST['shahi_item_type']);
            if ($type) {
                update_post_meta($post_id, '_shahi_item_type', $type);
            }
        }
    }
}
