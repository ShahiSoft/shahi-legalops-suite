<?php
/**
 * Button Shortcode
 *
 * Displays action buttons via [shahi_button] shortcode.
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
 * Class ButtonShortcode
 *
 * Handles [shahi_button] shortcode rendering.
 *
 * @since 1.0.0
 */
class ButtonShortcode {
    
    /**
     * Shortcode tag
     *
     * @since 1.0.0
     * @var string
     */
    private $tag = 'shahi_button';
    
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
     * [shahi_button action="dashboard"]
     * [shahi_button action="settings" text="Go to Settings"]
     * [shahi_button action="modules" style="secondary" size="large"]
     * [shahi_button url="https://example.com" text="Visit Site" target="_blank"]
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes.
     * @return string Shortcode output.
     */
    public function render($atts) {
        // Parse attributes
        $atts = shortcode_atts(
            array(
                'action' => '',            // dashboard, settings, modules, analytics, onboarding
                'url'    => '',            // Custom URL (overrides action)
                'text'   => '',            // Button text
                'style'  => 'primary',     // primary, secondary, success, danger
                'size'   => 'normal',      // small, normal, large
                'icon'   => '',            // Dashicon name (without dashicons- prefix)
                'target' => '_self',       // _self, _blank
            ),
            $atts,
            $this->tag
        );
        
        // Sanitize attributes
        $action = sanitize_key($atts['action']);
        $url    = esc_url($atts['url']);
        $text   = sanitize_text_field($atts['text']);
        $style  = sanitize_key($atts['style']);
        $size   = sanitize_key($atts['size']);
        $icon   = sanitize_key($atts['icon']);
        $target = sanitize_key($atts['target']);
        
        // Determine URL
        if (!empty($url)) {
            $button_url = $url;
        } elseif (!empty($action)) {
            $button_data = $this->get_action_data($action);
            $button_url = $button_data['url'];
            
            // Use action text if no custom text provided
            if (empty($text)) {
                $text = $button_data['text'];
            }
            
            // Use action icon if no custom icon provided
            if (empty($icon)) {
                $icon = $button_data['icon'];
            }
        } else {
            return '<p class="shahi-error">' . esc_html__('Button action or URL is required.', 'shahitemplate') . '</p>';
        }
        
        // Fallback text
        if (empty($text)) {
            $text = __('Click Here', 'shahitemplate');
        }
        
        // Validate target
        if (!in_array($target, array('_self', '_blank', '_parent', '_top'), true)) {
            $target = '_self';
        }
        
        // Build CSS classes
        $classes = array(
            'shahi-shortcode',
            'shahi-button-shortcode',
            'style-' . sanitize_html_class($style),
            'size-' . sanitize_html_class($size),
        );
        
        if (!empty($icon)) {
            $classes[] = 'has-icon';
        }
        
        // Build output
        ob_start();
        ?>
        <a href="<?php echo esc_url($button_url); ?>" 
           class="<?php echo esc_attr(implode(' ', $classes)); ?>"
           target="<?php echo esc_attr($target); ?>"
           <?php if ($target === '_blank') : ?>
               rel="noopener noreferrer"
           <?php endif; ?>>
            <?php if (!empty($icon)) : ?>
                <span class="dashicons dashicons-<?php echo esc_attr($icon); ?>"></span>
            <?php endif; ?>
            <?php echo esc_html($text); ?>
        </a>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get action data
     *
     * @since 1.0.0
     * @param string $action Action name.
     * @return array Action URL, text, and icon.
     */
    private function get_action_data($action) {
        $actions = array(
            'dashboard' => array(
                'url'  => admin_url('admin.php?page=shahi-dashboard'),
                'text' => __('Go to Dashboard', 'shahitemplate'),
                'icon' => 'dashboard',
            ),
            'settings' => array(
                'url'  => admin_url('admin.php?page=shahi-settings'),
                'text' => __('Plugin Settings', 'shahitemplate'),
                'icon' => 'admin-settings',
            ),
            'modules' => array(
                'url'  => admin_url('admin.php?page=shahi-modules'),
                'text' => __('Manage Modules', 'shahitemplate'),
                'icon' => 'admin-plugins',
            ),
            'analytics' => array(
                'url'  => admin_url('admin.php?page=shahi-analytics'),
                'text' => __('View Analytics', 'shahitemplate'),
                'icon' => 'chart-bar',
            ),
            'onboarding' => array(
                'url'  => admin_url('admin.php?page=shahi-onboarding'),
                'text' => __('Setup Wizard', 'shahitemplate'),
                'icon' => 'welcome-learn-more',
            ),
            'templates' => array(
                'url'  => admin_url('edit.php?post_type=shahi_legalops_suite_item'),
                'text' => __('Template Items', 'shahitemplate'),
                'icon' => 'admin-page',
            ),
            'help' => array(
                'url'  => admin_url('admin.php?page=shahi-help'),
                'text' => __('Get Help', 'shahitemplate'),
                'icon' => 'sos',
            ),
            'documentation' => array(
                'url'  => 'https://example.com/docs', // PLACEHOLDER URL
                'text' => __('Documentation', 'shahitemplate'),
                'icon' => 'book',
            ),
            'support' => array(
                'url'  => 'https://example.com/support', // PLACEHOLDER URL
                'text' => __('Support', 'shahitemplate'),
                'icon' => 'admin-users',
            ),
        );
        
        if (isset($actions[$action])) {
            return $actions[$action];
        }
        
        // Default fallback
        return array(
            'url'  => admin_url('admin.php?page=shahi-dashboard'),
            'text' => __('Go to Plugin', 'shahitemplate'),
            'icon' => 'admin-generic',
        );
    }
    
    /**
     * Get all available actions
     *
     * @since 1.0.0
     * @return array Available actions.
     */
    public function get_available_actions() {
        return array(
            'dashboard',
            'settings',
            'modules',
            'analytics',
            'onboarding',
            'templates',
            'help',
            'documentation',
            'support',
        );
    }
}
