<?php
/**
 * Stats Shortcode
 *
 * Displays statistics via [shahi_stats] shortcode.
 *
 * @package     ShahiLegalopsSuite
 * @subpackage  Shortcodes
 * @version     1.0.0
 * @since       1.0.0
 * @author      ShahiLegalopsSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalopsSuite\Shortcodes;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class StatsShortcode
 *
 * Handles [shahi_stats] shortcode rendering.
 *
 * @since 1.0.0
 */
class StatsShortcode {
    
    /**
     * Shortcode tag
     *
     * @since 1.0.0
     * @var string
     */
    private $tag = 'shahi_stats';
    
    /**
     * Register shortcode
     *
     * @since 1.0.0
     * @return void
     */
    public function register() {
        add_shortcode($this->tag, array($this, 'render'));
    }
    
    /**
     * Render shortcode
     *
     * Usage:
     * [shahi_stats type="total"]
     * [shahi_stats type="modules"]
     * [shahi_stats type="analytics"]
     * [shahi_stats type="users"]
     * [shahi_stats type="posts" display="inline"]
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes.
     * @return string Shortcode output.
     */
    public function render($atts) {
        // Parse attributes
        $atts = shortcode_atts(
            array(
                'type'    => 'total',      // total, modules, analytics, users, posts, active_modules
                'display' => 'card',       // card, inline
                'label'   => '',           // Custom label
            ),
            $atts,
            $this->tag
        );
        
        // Sanitize attributes
        $type    = sanitize_key($atts['type']);
        $display = sanitize_key($atts['display']);
        $label   = sanitize_text_field($atts['label']);
        
        // Get stat value and default label
        $stat = $this->get_stat($type);
        
        // Use custom label if provided
        if (empty($label)) {
            $label = $stat['label'];
        }
        
        // Build CSS classes
        $classes = array(
            'shahi-shortcode',
            'shahi-stats-shortcode',
            sanitize_html_class($display),
            'stat-type-' . sanitize_html_class($type)
        );
        
        // Build output
        ob_start();
        ?>
        <div class="<?php echo esc_attr(implode(' ', $classes)); ?>">
            <span class="shahi-stat-label"><?php echo esc_html($label); ?></span>
            <span class="shahi-stat-value"><?php echo esc_html($stat['value']); ?></span>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get stat data
     *
     * @since 1.0.0
     * @param string $type Stat type.
     * @return array Stat label and value.
     */
    private function get_stat($type) {
        switch ($type) {
            case 'total':
                return array(
                    'label' => __('Total Items', 'shahitemplate'),
                    'value' => $this->get_total_stat()
                );
                
            case 'modules':
                return array(
                    'label' => __('Total Modules', 'shahitemplate'),
                    'value' => $this->get_modules_count()
                );
                
            case 'active_modules':
                return array(
                    'label' => __('Active Modules', 'shahitemplate'),
                    'value' => $this->get_active_modules_count()
                );
                
            case 'analytics':
                return array(
                    'label' => __('Analytics Events', 'shahitemplate'),
                    'value' => $this->get_analytics_count()
                );
                
            case 'users':
                return array(
                    'label' => __('Total Users', 'shahitemplate'),
                    'value' => $this->get_users_count()
                );
                
            case 'posts':
                return array(
                    'label' => __('Published Posts', 'shahitemplate'),
                    'value' => $this->get_posts_count()
                );
                
            case 'template_items':
                return array(
                    'label' => __('Template Items', 'shahitemplate'),
                    'value' => $this->get_template_items_count()
                );
                
            default:
                return array(
                    'label' => __('Unknown Stat', 'shahitemplate'),
                    'value' => '0'
                );
        }
    }
    
    /**
     * Get total stat (combines multiple metrics)
     *
     * @since 1.0.0
     * @return string Formatted stat value.
     */
    private function get_total_stat() {
        // PLACEHOLDER: This combines multiple metrics
        // TODO: Define what "total" means for your specific use case
        $modules = $this->get_modules_count();
        $users = $this->get_users_count();
        $posts = $this->get_posts_count();
        
        $total = (int)$modules + (int)$users + (int)$posts;
        
        return number_format_i18n($total);
    }
    
    /**
     * Get modules count
     *
     * @since 1.0.0
     * @return string Formatted count.
     */
    private function get_modules_count() {
        $modules = get_option('shahi_modules', array());
        
        if (!is_array($modules)) {
            return '0';
        }
        
        return number_format_i18n(count($modules));
    }
    
    /**
     * Get active modules count
     *
     * @since 1.0.0
     * @return string Formatted count.
     */
    private function get_active_modules_count() {
        $modules = get_option('shahi_modules', array());
        
        if (!is_array($modules)) {
            return '0';
        }
        
        $active = 0;
        foreach ($modules as $module) {
            if (isset($module['enabled']) && $module['enabled']) {
                $active++;
            }
        }
        
        return number_format_i18n($active);
    }
    
    /**
     * Get analytics count
     *
     * @since 1.0.0
     * @return string Formatted count.
     */
    private function get_analytics_count() {
        // PLACEHOLDER: Get from analytics data
        // TODO: Implement actual analytics counting when analytics system is ready
        $analytics_data = get_option('shahi_analytics_events', array());
        
        if (!is_array($analytics_data)) {
            return '0';
        }
        
        return number_format_i18n(count($analytics_data));
    }
    
    /**
     * Get users count
     *
     * @since 1.0.0
     * @return string Formatted count.
     */
    private function get_users_count() {
        $users = count_users();
        
        if (!isset($users['total_users'])) {
            return '0';
        }
        
        return number_format_i18n($users['total_users']);
    }
    
    /**
     * Get posts count
     *
     * @since 1.0.0
     * @return string Formatted count.
     */
    private function get_posts_count() {
        $count = wp_count_posts('post');
        
        if (!isset($count->publish)) {
            return '0';
        }
        
        return number_format_i18n($count->publish);
    }
    
    /**
     * Get template items count
     *
     * @since 1.0.0
     * @return string Formatted count.
     */
    private function get_template_items_count() {
        $count = wp_count_posts('shahi_legalops_suite_item');
        
        if (!$count) {
            return '0';
        }
        
        $total = 0;
        if (isset($count->publish)) {
            $total += $count->publish;
        }
        if (isset($count->draft)) {
            $total += $count->draft;
        }
        
        return number_format_i18n($total);
    }
}
