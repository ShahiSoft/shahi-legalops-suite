<?php
/**
 * Stats Widget
 *
 * Displays plugin statistics in a widget.
 *
 * @package     ShahiLegalopsSuite
 * @subpackage  Widgets
 * @version     1.0.0
 * @since       1.0.0
 * @author      ShahiLegalopsSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalopsSuite\Widgets;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class StatsWidget
 *
 * Widget for displaying plugin statistics.
 *
 * @since 1.0.0
 */
class StatsWidget extends \WP_Widget {
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        parent::__construct(
            'shahi_stats_widget',
            __('ShahiLegalopsSuite Stats', 'shahi-legalops-suite'),
            array(
                'description' => __('Display plugin statistics', 'shahi-legalops-suite'),
                'classname' => 'shahi-stats-widget',
            )
        );
    }
    
    /**
     * Front-end display of widget
     *
     * @since 1.0.0
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     * @return void
     */
    public function widget($args, $instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Plugin Stats', 'shahi-legalops-suite');
        $title = apply_filters('widget_title', $title, $instance, $this->id_base);
        
        $show_modules = isset($instance['show_modules']) ? (bool) $instance['show_modules'] : true;
        $show_analytics = isset($instance['show_analytics']) ? (bool) $instance['show_analytics'] : true;
        $show_users = isset($instance['show_users']) ? (bool) $instance['show_users'] : true;
        $show_posts = isset($instance['show_posts']) ? (bool) $instance['show_posts'] : false;
        
        echo $args['before_widget'];
        
        if ($title) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }
        
        echo '<div class="shahi-widget shahi-stats-widget-content">';
        echo '<ul class="shahi-stats-list">';
        
        // Get statistics
        $stats = $this->get_stats();
        
        if ($show_modules && isset($stats['modules'])) {
            echo '<li>';
            echo '<span class="shahi-stat-label">' . esc_html__('Active Modules', 'shahi-legalops-suite') . '</span>';
            echo '<span class="shahi-stat-value">' . esc_html($stats['modules']['active']) . ' / ' . esc_html($stats['modules']['total']) . '</span>';
            echo '</li>';
        }
        
        if ($show_analytics && isset($stats['analytics'])) {
            echo '<li>';
            echo '<span class="shahi-stat-label">' . esc_html__('Total Events', 'shahi-legalops-suite') . '</span>';
            echo '<span class="shahi-stat-value">' . esc_html(number_format($stats['analytics']['total_events'])) . '</span>';
            echo '</li>';
            
            echo '<li>';
            echo '<span class="shahi-stat-label">' . esc_html__('Events Today', 'shahi-legalops-suite') . '</span>';
            echo '<span class="shahi-stat-value">' . esc_html(number_format($stats['analytics']['events_today'])) . '</span>';
            echo '</li>';
        }
        
        if ($show_users && isset($stats['users'])) {
            echo '<li>';
            echo '<span class="shahi-stat-label">' . esc_html__('Total Users', 'shahi-legalops-suite') . '</span>';
            echo '<span class="shahi-stat-value">' . esc_html(number_format($stats['users']['total'])) . '</span>';
            echo '</li>';
        }
        
        if ($show_posts && isset($stats['posts'])) {
            echo '<li>';
            echo '<span class="shahi-stat-label">' . esc_html__('Template Items', 'shahi-legalops-suite') . '</span>';
            echo '<span class="shahi-stat-value">' . esc_html(number_format($stats['posts']['template_items'])) . '</span>';
            echo '</li>';
        }
        
        echo '</ul>';
        echo '</div>';
        
        echo $args['after_widget'];
    }
    
    /**
     * Back-end widget form
     *
     * @since 1.0.0
     * @param array $instance Previously saved values from database.
     * @return void
     */
    public function form($instance) {
        $title = isset($instance['title']) ? $instance['title'] : __('Plugin Stats', 'shahi-legalops-suite');
        $show_modules = isset($instance['show_modules']) ? (bool) $instance['show_modules'] : true;
        $show_analytics = isset($instance['show_analytics']) ? (bool) $instance['show_analytics'] : true;
        $show_users = isset($instance['show_users']) ? (bool) $instance['show_users'] : true;
        $show_posts = isset($instance['show_posts']) ? (bool) $instance['show_posts'] : false;
        ?>
        
        <div class="shahi-widget-field">
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_html_e('Title:', 'shahi-legalops-suite'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </div>
        
        <div class="shahi-widget-field">
            <label>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('show_modules')); ?>" 
                       name="<?php echo esc_attr($this->get_field_name('show_modules')); ?>" 
                       value="1" <?php checked($show_modules, true); ?>>
                <?php esc_html_e('Show Modules', 'shahi-legalops-suite'); ?>
            </label>
        </div>
        
        <div class="shahi-widget-field">
            <label>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('show_analytics')); ?>" 
                       name="<?php echo esc_attr($this->get_field_name('show_analytics')); ?>" 
                       value="1" <?php checked($show_analytics, true); ?>>
                <?php esc_html_e('Show Analytics', 'shahi-legalops-suite'); ?>
            </label>
        </div>
        
        <div class="shahi-widget-field">
            <label>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('show_users')); ?>" 
                       name="<?php echo esc_attr($this->get_field_name('show_users')); ?>" 
                       value="1" <?php checked($show_users, true); ?>>
                <?php esc_html_e('Show Users', 'shahi-legalops-suite'); ?>
            </label>
        </div>
        
        <div class="shahi-widget-field">
            <label>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('show_posts')); ?>" 
                       name="<?php echo esc_attr($this->get_field_name('show_posts')); ?>" 
                       value="1" <?php checked($show_posts, true); ?>>
                <?php esc_html_e('Show Template Items', 'shahi-legalops-suite'); ?>
            </label>
        </div>
        
        <?php
    }
    
    /**
     * Sanitize widget form values as they are saved
     *
     * @since 1.0.0
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        
        $instance['title'] = (!empty($new_instance['title'])) ? 
            sanitize_text_field($new_instance['title']) : '';
        
        $instance['show_modules'] = isset($new_instance['show_modules']) ? 
            (bool) $new_instance['show_modules'] : false;
        
        $instance['show_analytics'] = isset($new_instance['show_analytics']) ? 
            (bool) $new_instance['show_analytics'] : false;
        
        $instance['show_users'] = isset($new_instance['show_users']) ? 
            (bool) $new_instance['show_users'] : false;
        
        $instance['show_posts'] = isset($new_instance['show_posts']) ? 
            (bool) $new_instance['show_posts'] : false;
        
        return $instance;
    }
    
    /**
     * Get statistics data
     *
     * @since 1.0.0
     * @return array Statistics data.
     */
    private function get_stats() {
        global $wpdb;
        
        $stats = array();
        
        // Module stats
        $modules = get_option('shahi_modules', array());
        $enabled_modules = array_filter($modules, function($module) {
            return isset($module['enabled']) && $module['enabled'];
        });
        
        $stats['modules'] = array(
            'total' => count($modules),
            'active' => count($enabled_modules),
        );
        
        // Analytics stats (if table exists)
        $analytics_table = $wpdb->prefix . 'shahi_analytics';
        if ($wpdb->get_var("SHOW TABLES LIKE '$analytics_table'") === $analytics_table) {
            $total_events = intval($wpdb->get_var("SELECT COUNT(*) FROM $analytics_table"));
            $events_today = intval($wpdb->get_var(
                "SELECT COUNT(*) FROM $analytics_table WHERE DATE(created_at) = CURDATE()"
            ));
            
            $stats['analytics'] = array(
                'total_events' => $total_events,
                'events_today' => $events_today,
            );
        } else {
            // Placeholder data if analytics table doesn't exist
            $stats['analytics'] = array(
                'total_events' => 0,
                'events_today' => 0,
            );
        }
        
        // User stats
        $user_count = count_users();
        $stats['users'] = array(
            'total' => $user_count['total_users'],
        );
        
        // Template items count
        $template_items = wp_count_posts('shahi_legalops_suite_item');
        $stats['posts'] = array(
            'template_items' => isset($template_items->publish) ? $template_items->publish : 0,
        );
        
        return $stats;
    }
}
