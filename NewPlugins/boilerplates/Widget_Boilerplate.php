<?php
/**
 * Widget Boilerplate Template
 * 
 * PLACEHOLDER FILE - This is a template for creating WordPress widgets.
 * Copy this file to includes/widgets/ and customize it.
 * 
 * Instructions:
 * 1. Copy this file to: includes/widgets/{WidgetName}_Widget.php
 * 2. Replace all PLACEHOLDER values with your actual widget information
 * 3. Replace {PluginNamespace} with your actual namespace (e.g., ShahiTemplate)
 * 4. Replace {WidgetName} with your widget name in PascalCase (e.g., Recent_Posts)
 * 5. Replace {widget-slug} with your widget slug (e.g., recent-posts)
 * 6. Implement the widget methods and add your custom logic
 * 7. Register the widget in your main plugin file or module
 * 
 * @package    {PluginNamespace}
 * @subpackage Widgets
 * @since      1.0.0
 */

namespace {PluginNamespace}\Widgets;

use WP_Widget;

// PLACEHOLDER: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * {WidgetName} Widget Class
 * 
 * PLACEHOLDER DESCRIPTION: Add your widget description here.
 * Explain what this widget displays and its key features.
 * 
 * Example features:
 * - Feature 1
 * - Feature 2
 * - Feature 3
 * 
 * @since 1.0.0
 */
class {WidgetName}_Widget extends WP_Widget {
    
    /**
     * Widget ID base
     * 
     * PLACEHOLDER: Replace with your widget's unique ID
     * 
     * @var string
     */
    protected $widget_id_base = 'shahi_{widget-slug}';
    
    /**
     * Widget CSS class
     * 
     * @var string
     */
    protected $widget_class = 'shahi-widget-{widget-slug}';
    
    /**
     * Constructor
     * 
     * Sets up the widget name, description, and other options
     * 
     * @since 1.0.0
     */
    public function __construct() {
        $widget_options = [
            'classname'                   => $this->widget_class,
            'description'                 => __('PLACEHOLDER: Brief description of what this widget does', 'shahi-template'),
            'customize_selective_refresh' => true,
        ];
        
        parent::__construct(
            $this->widget_id_base,
            __('PLACEHOLDER: Widget Display Name', 'shahi-template'), // PLACEHOLDER: Widget name
            $widget_options
        );
        
        // Enqueue widget assets
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
    }
    
    /**
     * Outputs the widget content
     * 
     * PLACEHOLDER: This is the main widget output function.
     * Generate the HTML that will be displayed on the frontend.
     * 
     * @since 1.0.0
     * @param array $args     Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'
     * @param array $instance The settings for the particular instance of the widget
     * @return void
     */
    public function widget($args, $instance) {
        // Extract widget arguments
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $title = apply_filters('widget_title', $title, $instance, $this->id_base);
        
        // PLACEHOLDER: Get widget settings
        $count       = !empty($instance['count']) ? absint($instance['count']) : 5;
        $show_date   = !empty($instance['show_date']) ? $instance['show_date'] : false;
        $custom_text = !empty($instance['custom_text']) ? $instance['custom_text'] : '';
        
        // Before widget HTML
        echo $args['before_widget'];
        
        // Widget title
        if ($title) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }
        
        // PLACEHOLDER: Widget content
        ?>
        <div class="<?php echo esc_attr($this->widget_class); ?>-content">
            
            <?php if (!empty($custom_text)): ?>
                <p class="widget-description"><?php echo esc_html($custom_text); ?></p>
            <?php endif; ?>
            
            <?php
            // PLACEHOLDER: Add your main widget content here
            // Example: Display a list of items
            $items = $this->get_widget_items($count);
            
            if (!empty($items)): ?>
                <ul class="widget-items-list">
                    <?php foreach ($items as $item): ?>
                        <li class="widget-item">
                            <a href="<?php echo esc_url($item['url']); ?>">
                                <?php echo esc_html($item['title']); ?>
                            </a>
                            <?php if ($show_date && !empty($item['date'])): ?>
                                <span class="widget-item-date">
                                    <?php echo esc_html($item['date']); ?>
                                </span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="no-items">
                    <?php esc_html_e('PLACEHOLDER: No items found.', 'shahi-template'); ?>
                </p>
            <?php endif; ?>
            
        </div>
        <?php
        
        // After widget HTML
        echo $args['after_widget'];
    }
    
    /**
     * Outputs the widget settings form
     * 
     * PLACEHOLDER: Create the form for widget settings in the admin area.
     * Add fields for all your widget options.
     * 
     * @since 1.0.0
     * @param array $instance Current settings
     * @return void
     */
    public function form($instance) {
        // PLACEHOLDER: Get current values or set defaults
        $title       = isset($instance['title']) ? $instance['title'] : __('Widget Title', 'shahi-template');
        $count       = isset($instance['count']) ? absint($instance['count']) : 5;
        $show_date   = isset($instance['show_date']) ? (bool) $instance['show_date'] : false;
        $custom_text = isset($instance['custom_text']) ? $instance['custom_text'] : '';
        
        ?>
        <!-- PLACEHOLDER: Title field -->
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_html_e('Title:', 'shahi-template'); ?>
            </label>
            <input 
                class="widefat" 
                id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                type="text" 
                value="<?php echo esc_attr($title); ?>"
            >
        </p>
        
        <!-- PLACEHOLDER: Count/Number field -->
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('count')); ?>">
                <?php esc_html_e('Number of items to show:', 'shahi-template'); ?>
            </label>
            <input 
                class="tiny-text" 
                id="<?php echo esc_attr($this->get_field_id('count')); ?>" 
                name="<?php echo esc_attr($this->get_field_name('count')); ?>" 
                type="number" 
                step="1" 
                min="1" 
                max="20"
                value="<?php echo esc_attr($count); ?>"
            >
        </p>
        
        <!-- PLACEHOLDER: Checkbox field -->
        <p>
            <input 
                class="checkbox" 
                type="checkbox" 
                id="<?php echo esc_attr($this->get_field_id('show_date')); ?>" 
                name="<?php echo esc_attr($this->get_field_name('show_date')); ?>"
                <?php checked($show_date); ?>
            >
            <label for="<?php echo esc_attr($this->get_field_id('show_date')); ?>">
                <?php esc_html_e('Display date?', 'shahi-template'); ?>
            </label>
        </p>
        
        <!-- PLACEHOLDER: Textarea field -->
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('custom_text')); ?>">
                <?php esc_html_e('Custom text:', 'shahi-template'); ?>
            </label>
            <textarea 
                class="widefat" 
                rows="4"
                id="<?php echo esc_attr($this->get_field_id('custom_text')); ?>" 
                name="<?php echo esc_attr($this->get_field_name('custom_text')); ?>"
            ><?php echo esc_textarea($custom_text); ?></textarea>
        </p>
        
        <!-- PLACEHOLDER: Select/Dropdown field -->
        <?php
        /*
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('category')); ?>">
                <?php esc_html_e('Category:', 'shahi-template'); ?>
            </label>
            <select 
                class="widefat" 
                id="<?php echo esc_attr($this->get_field_id('category')); ?>" 
                name="<?php echo esc_attr($this->get_field_name('category')); ?>"
            >
                <option value=""><?php esc_html_e('All Categories', 'shahi-template'); ?></option>
                <?php
                $categories = get_categories();
                foreach ($categories as $category) {
                    printf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr($category->term_id),
                        selected($instance['category'] ?? '', $category->term_id, false),
                        esc_html($category->name)
                    );
                }
                ?>
            </select>
        </p>
        */
        ?>
        
        <?php
    }
    
    /**
     * Processing widget options on save
     * 
     * PLACEHOLDER: Sanitize and validate all widget settings before saving.
     * 
     * @since 1.0.0
     * @param array $new_instance New settings for this instance as input by the user
     * @param array $old_instance Old settings for this instance
     * @return array Settings to save or bool false to cancel saving
     */
    public function update($new_instance, $old_instance) {
        $instance = [];
        
        // PLACEHOLDER: Sanitize each field
        $instance['title']       = !empty($new_instance['title']) 
            ? sanitize_text_field($new_instance['title']) 
            : '';
        
        $instance['count']       = !empty($new_instance['count']) 
            ? absint($new_instance['count']) 
            : 5;
        
        $instance['show_date']   = !empty($new_instance['show_date']) 
            ? (bool) $new_instance['show_date'] 
            : false;
        
        $instance['custom_text'] = !empty($new_instance['custom_text']) 
            ? sanitize_textarea_field($new_instance['custom_text']) 
            : '';
        
        // PLACEHOLDER: Add validation for other fields
        // Example:
        // $instance['category'] = !empty($new_instance['category']) 
        //     ? absint($new_instance['category']) 
        //     : 0;
        
        // Clear widget cache
        $this->flush_widget_cache();
        
        return $instance;
    }
    
    /**
     * Get items to display in widget
     * 
     * PLACEHOLDER: Fetch the data that will be displayed in the widget.
     * Replace this with your actual data fetching logic.
     * 
     * @since 1.0.0
     * @param int $count Number of items to retrieve
     * @return array Array of items
     */
    private function get_widget_items($count) {
        // PLACEHOLDER: Check cache first
        $cache_key = $this->widget_id_base . '_items_' . $count;
        $cached = wp_cache_get($cache_key, 'widget');
        
        if (false !== $cached) {
            return $cached;
        }
        
        // PLACEHOLDER: Fetch items from database or external source
        // Example: Recent posts
        /*
        $query_args = [
            'posts_per_page'      => $count,
            'no_found_rows'       => true,
            'post_status'         => 'publish',
            'ignore_sticky_posts' => true,
        ];
        
        $query = new WP_Query($query_args);
        $items = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $items[] = [
                    'title' => get_the_title(),
                    'url'   => get_permalink(),
                    'date'  => get_the_date(),
                ];
            }
            wp_reset_postdata();
        }
        */
        
        // PLACEHOLDER: Mock data for demonstration
        $items = [];
        for ($i = 1; $i <= $count; $i++) {
            $items[] = [
                'title' => 'PLACEHOLDER: Item ' . $i,
                'url'   => home_url('/'),
                'date'  => current_time('mysql'),
            ];
        }
        
        // Cache the results
        wp_cache_set($cache_key, $items, 'widget', 3600);
        
        return $items;
    }
    
    /**
     * Flush widget cache
     * 
     * @since 1.0.0
     * @return void
     */
    private function flush_widget_cache() {
        wp_cache_delete($this->widget_id_base . '_items_*', 'widget');
    }
    
    /**
     * Enqueue admin scripts and styles
     * 
     * PLACEHOLDER: Load CSS/JS for the widget admin form
     * 
     * @since 1.0.0
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on widgets page
        if ('widgets.php' !== $hook && 'customize.php' !== $hook) {
            return;
        }
        
        // PLACEHOLDER: Enqueue admin CSS
        /*
        wp_enqueue_style(
            $this->widget_id_base . '-admin',
            plugin_dir_url(__FILE__) . '../assets/css/widgets/admin-{widget-slug}.css',
            [],
            '1.0.0'
        );
        */
        
        // PLACEHOLDER: Enqueue admin JavaScript
        /*
        wp_enqueue_script(
            $this->widget_id_base . '-admin',
            plugin_dir_url(__FILE__) . '../assets/js/widgets/admin-{widget-slug}.js',
            ['jquery', 'wp-color-picker'],
            '1.0.0',
            true
        );
        
        // Color picker
        wp_enqueue_style('wp-color-picker');
        */
    }
    
    /**
     * Enqueue frontend scripts and styles
     * 
     * PLACEHOLDER: Load CSS/JS for the widget frontend display
     * 
     * @since 1.0.0
     * @return void
     */
    public function enqueue_frontend_scripts() {
        // Check if widget is active
        if (!is_active_widget(false, false, $this->id_base, true)) {
            return;
        }
        
        // PLACEHOLDER: Enqueue frontend CSS
        /*
        wp_enqueue_style(
            $this->widget_id_base,
            plugin_dir_url(__FILE__) . '../assets/css/widgets/{widget-slug}.css',
            [],
            '1.0.0'
        );
        */
        
        // PLACEHOLDER: Enqueue frontend JavaScript
        /*
        wp_enqueue_script(
            $this->widget_id_base,
            plugin_dir_url(__FILE__) . '../assets/js/widgets/{widget-slug}.js',
            ['jquery'],
            '1.0.0',
            true
        );
        */
    }
}

/**
 * Register widget
 * 
 * PLACEHOLDER: Add this to your main plugin file or module initialization
 * 
 * Example:
 * add_action('widgets_init', function() {
 *     register_widget('{PluginNamespace}\Widgets\{WidgetName}_Widget');
 * });
 */
