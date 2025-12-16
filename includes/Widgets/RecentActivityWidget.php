<?php
/**
 * Recent Activity Widget
 *
 * Displays recent plugin activity in a widget.
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
 * Class RecentActivityWidget
 *
 * Widget for displaying recent plugin activity.
 *
 * @since 1.0.0
 */
class RecentActivityWidget extends \WP_Widget {
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        parent::__construct(
            'shahi_recent_activity_widget',
            __('ShahiLegalopsSuite Recent Activity', 'shahi-legalops-suite'),
            array(
                'description' => __('Display recent plugin activity', 'shahi-legalops-suite'),
                'classname' => 'shahi-recent-activity-widget',
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
        $title = !empty($instance['title']) ? $instance['title'] : __('Recent Activity', 'shahi-legalops-suite');
        $title = apply_filters('widget_title', $title, $instance, $this->id_base);
        
        $limit = isset($instance['limit']) ? intval($instance['limit']) : 5;
        $limit = max(1, min(20, $limit)); // Between 1 and 20
        
        $show_time = isset($instance['show_time']) ? (bool) $instance['show_time'] : true;
        $activity_types = isset($instance['activity_types']) ? $instance['activity_types'] : 'all';
        
        echo $args['before_widget'];
        
        if ($title) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }
        
        echo '<div class="shahi-widget shahi-recent-activity-widget-content">';
        
        // Get activities
        $activities = $this->get_activities($limit, $activity_types);
        
        if (!empty($activities)) {
            echo '<ul class="shahi-activity-list">';
            
            foreach ($activities as $activity) {
                echo '<li>';
                echo '<div class="shahi-activity-content">';
                echo esc_html($activity['description']);
                echo '</div>';
                
                if ($show_time && !empty($activity['time'])) {
                    echo '<span class="shahi-activity-time">';
                    echo esc_html($activity['time']);
                    echo '</span>';
                }
                echo '</li>';
            }
            
            echo '</ul>';
        } else {
            echo '<p class="shahi-no-activity">';
            echo esc_html__('No recent activity found.', 'shahi-legalops-suite');
            echo '</p>';
        }
        
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
        $title = isset($instance['title']) ? $instance['title'] : __('Recent Activity', 'shahi-legalops-suite');
        $limit = isset($instance['limit']) ? intval($instance['limit']) : 5;
        $show_time = isset($instance['show_time']) ? (bool) $instance['show_time'] : true;
        $activity_types = isset($instance['activity_types']) ? $instance['activity_types'] : 'all';
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
            <label for="<?php echo esc_attr($this->get_field_id('limit')); ?>">
                <?php esc_html_e('Number of items to show:', 'shahi-legalops-suite'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('limit')); ?>" type="number" 
                   value="<?php echo esc_attr($limit); ?>" min="1" max="20">
            <span class="description"><?php esc_html_e('Between 1 and 20', 'shahi-legalops-suite'); ?></span>
        </div>
        
        <div class="shahi-widget-field">
            <label for="<?php echo esc_attr($this->get_field_id('activity_types')); ?>">
                <?php esc_html_e('Activity Type:', 'shahi-legalops-suite'); ?>
            </label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('activity_types')); ?>" 
                    name="<?php echo esc_attr($this->get_field_name('activity_types')); ?>">
                <option value="all" <?php selected($activity_types, 'all'); ?>><?php esc_html_e('All Activity', 'shahi-legalops-suite'); ?></option>
                <option value="modules" <?php selected($activity_types, 'modules'); ?>><?php esc_html_e('Modules Only', 'shahi-legalops-suite'); ?></option>
                <option value="settings" <?php selected($activity_types, 'settings'); ?>><?php esc_html_e('Settings Only', 'shahi-legalops-suite'); ?></option>
                <option value="onboarding" <?php selected($activity_types, 'onboarding'); ?>><?php esc_html_e('Onboarding Only', 'shahi-legalops-suite'); ?></option>
            </select>
        </div>
        
        <div class="shahi-widget-field">
            <label>
                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('show_time')); ?>" 
                       name="<?php echo esc_attr($this->get_field_name('show_time')); ?>" 
                       value="1" <?php checked($show_time, true); ?>>
                <?php esc_html_e('Show Timestamp', 'shahi-legalops-suite'); ?>
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
        
        $instance['limit'] = (!empty($new_instance['limit'])) ? 
            intval($new_instance['limit']) : 5;
        $instance['limit'] = max(1, min(20, $instance['limit']));
        
        $instance['show_time'] = isset($new_instance['show_time']) ? 
            (bool) $new_instance['show_time'] : false;
        
        $instance['activity_types'] = (!empty($new_instance['activity_types'])) ? 
            sanitize_text_field($new_instance['activity_types']) : 'all';
        
        return $instance;
    }
    
    /**
     * Get recent activities
     *
     * @since 1.0.0
     * @param int    $limit          Number of activities to retrieve.
     * @param string $activity_types Type of activities to show.
     * @return array Activities.
     */
    private function get_activities($limit, $activity_types) {
        global $wpdb;
        
        $activities = array();
        $analytics_table = $wpdb->prefix . 'shahi_analytics';
        
        // Check if analytics table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$analytics_table'") === $analytics_table) {
            // Build WHERE clause based on activity types
            $where = '1=1';
            
            if ($activity_types === 'modules') {
                $where = "event_type LIKE 'module_%'";
            } elseif ($activity_types === 'settings') {
                $where = "event_type LIKE 'settings_%'";
            } elseif ($activity_types === 'onboarding') {
                $where = "event_type LIKE 'onboarding_%'";
            }
            
            // Get recent events
            $events = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT event_type, event_data, created_at, user_id 
                     FROM $analytics_table 
                     WHERE $where 
                     ORDER BY created_at DESC 
                     LIMIT %d",
                    $limit
                ),
                ARRAY_A
            );
            
            if ($events) {
                foreach ($events as $event) {
                    $description = $this->format_activity_description($event);
                    $time = $this->format_time_ago($event['created_at']);
                    
                    $activities[] = array(
                        'description' => $description,
                        'time' => $time,
                    );
                }
            }
        }
        
        // If no analytics data, show placeholder activities
        if (empty($activities)) {
            $activities = $this->get_placeholder_activities($limit);
        }
        
        return $activities;
    }
    
    /**
     * Format activity description
     *
     * @since 1.0.0
     * @param array $event Event data.
     * @return string Formatted description.
     */
    private function format_activity_description($event) {
        $user = get_userdata($event['user_id']);
        $username = $user ? $user->display_name : __('Someone', 'shahi-legalops-suite');
        
        $event_type = $event['event_type'];
        $event_data = json_decode($event['event_data'], true);
        
        $descriptions = array(
            'module_enabled' => sprintf(__('%s enabled a module', 'shahi-legalops-suite'), $username),
            'module_disabled' => sprintf(__('%s disabled a module', 'shahi-legalops-suite'), $username),
            'module_settings_updated' => sprintf(__('%s updated module settings', 'shahi-legalops-suite'), $username),
            'settings_saved' => sprintf(__('%s saved plugin settings', 'shahi-legalops-suite'), $username),
            'settings_reset' => sprintf(__('%s reset plugin settings', 'shahi-legalops-suite'), $username),
            'onboarding_step_completed' => sprintf(__('%s completed an onboarding step', 'shahi-legalops-suite'), $username),
            'onboarding_completed' => sprintf(__('%s completed the onboarding', 'shahi-legalops-suite'), $username),
            'checklist_completed' => sprintf(__('%s completed a checklist item', 'shahi-legalops-suite'), $username),
        );
        
        return isset($descriptions[$event_type]) ? $descriptions[$event_type] : 
            sprintf(__('%s performed an action', 'shahi-legalops-suite'), $username);
    }
    
    /**
     * Format time ago
     *
     * @since 1.0.0
     * @param string $datetime DateTime string.
     * @return string Formatted time ago.
     */
    private function format_time_ago($datetime) {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return __('Just now', 'shahi-legalops-suite');
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return sprintf(_n('%d minute ago', '%d minutes ago', $minutes, 'shahi-legalops-suite'), $minutes);
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return sprintf(_n('%d hour ago', '%d hours ago', $hours, 'shahi-legalops-suite'), $hours);
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return sprintf(_n('%d day ago', '%d days ago', $days, 'shahi-legalops-suite'), $days);
        } else {
            return date_i18n(get_option('date_format'), $timestamp);
        }
    }
    
    /**
     * Get placeholder activities when no real data exists
     *
     * @since 1.0.0
     * @param int $limit Number of activities.
     * @return array Placeholder activities.
     */
    private function get_placeholder_activities($limit) {
        $placeholders = array(
            array(
                'description' => __('Plugin activated', 'shahi-legalops-suite'),
                'time' => __('Recently', 'shahi-legalops-suite'),
            ),
            array(
                'description' => __('Settings configured', 'shahi-legalops-suite'),
                'time' => __('Recently', 'shahi-legalops-suite'),
            ),
            array(
                'description' => __('Module enabled', 'shahi-legalops-suite'),
                'time' => __('Recently', 'shahi-legalops-suite'),
            ),
        );
        
        return array_slice($placeholders, 0, $limit);
    }
}
