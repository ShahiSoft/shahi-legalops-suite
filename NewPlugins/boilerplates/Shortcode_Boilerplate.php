<?php
/**
 * Shortcode Boilerplate Template
 * 
 * PLACEHOLDER FILE - This is a template for creating WordPress shortcodes.
 * Copy this file to includes/shortcodes/ and customize it.
 * 
 * Instructions:
 * 1. Copy this file to: includes/shortcodes/{ShortcodeName}_Shortcode.php
 * 2. Replace all PLACEHOLDER values with your actual shortcode information
 * 3. Replace {PluginNamespace} with your actual namespace (e.g., ShahiTemplate)
 * 4. Replace {ShortcodeName} with your shortcode name in PascalCase (e.g., Latest_Posts)
 * 5. Replace {shortcode-tag} with your shortcode tag (e.g., latest_posts)
 * 6. Implement the shortcode logic and customize attributes
 * 7. Register the shortcode in your main plugin file or module
 * 
 * Usage example: [shortcode-tag attribute1="value1" attribute2="value2"]
 * 
 * @package    {PluginNamespace}
 * @subpackage Shortcodes
 * @since      1.0.0
 */

namespace {PluginNamespace}\Shortcodes;

// PLACEHOLDER: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * {ShortcodeName} Shortcode Class
 * 
 * PLACEHOLDER DESCRIPTION: Add your shortcode description here.
 * Explain what this shortcode does and how to use it.
 * 
 * Example usage:
 * [shortcode-tag]
 * [shortcode-tag count="5" show_date="true"]
 * [shortcode-tag title="My Title"]Content here[/shortcode-tag]
 * 
 * @since 1.0.0
 */
class {ShortcodeName}_Shortcode {
    
    /**
     * Shortcode tag
     * 
     * PLACEHOLDER: Replace with your shortcode's tag name
     * Should be lowercase with underscores or hyphens
     * 
     * @var string
     */
    protected $tag = '{shortcode-tag}';
    
    /**
     * Default attributes
     * 
     * PLACEHOLDER: Define your shortcode's default attributes
     * 
     * @var array
     */
    protected $default_atts = [
        'title'      => '',
        'count'      => 5,
        'show_date'  => false,
        'category'   => '',
        'order'      => 'DESC',
        'orderby'    => 'date',
        'class'      => '',
        // PLACEHOLDER: Add more attributes as needed
    ];
    
    /**
     * Constructor
     * 
     * Register the shortcode
     * 
     * @since 1.0.0
     */
    public function __construct() {
        $this->register();
    }
    
    /**
     * Register shortcode
     * 
     * @since 1.0.0
     * @return void
     */
    public function register() {
        add_shortcode($this->tag, [$this, 'render']);
        
        // Enqueue assets when shortcode is used
        add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue_assets']);
    }
    
    /**
     * Render shortcode output
     * 
     * PLACEHOLDER: This is the main shortcode rendering function.
     * Generate the HTML output based on the provided attributes.
     * 
     * @since 1.0.0
     * @param array  $atts    Shortcode attributes
     * @param string $content Shortcode content (for enclosing shortcodes)
     * @param string $tag     Shortcode tag
     * @return string Shortcode output
     */
    public function render($atts = [], $content = null, $tag = '') {
        // Normalize attribute keys to lowercase
        $atts = array_change_key_case((array) $atts, CASE_LOWER);
        
        // Merge user attributes with default attributes
        $atts = shortcode_atts($this->default_atts, $atts, $tag);
        
        // Sanitize attributes
        $atts = $this->sanitize_attributes($atts);
        
        // Extract attributes into variables
        extract($atts);
        
        // PLACEHOLDER: Get data for shortcode
        $items = $this->get_items($atts);
        
        // Start output buffering
        ob_start();
        
        // PLACEHOLDER: Generate shortcode output
        $this->render_output($atts, $items, $content);
        
        // Return the buffered content
        return ob_get_clean();
    }
    
    /**
     * Render shortcode output HTML
     * 
     * PLACEHOLDER: Generate the HTML structure for your shortcode
     * 
     * @since 1.0.0
     * @param array  $atts    Sanitized attributes
     * @param array  $items   Data items to display
     * @param string $content Shortcode content
     * @return void
     */
    protected function render_output($atts, $items, $content) {
        // Build CSS classes
        $classes = ['shahi-shortcode', 'shahi-' . $this->tag];
        if (!empty($atts['class'])) {
            $classes[] = $atts['class'];
        }
        
        ?>
        <div class="<?php echo esc_attr(implode(' ', $classes)); ?>">
            
            <?php if (!empty($atts['title'])): ?>
                <h3 class="shortcode-title">
                    <?php echo esc_html($atts['title']); ?>
                </h3>
            <?php endif; ?>
            
            <?php if (!empty($content)): ?>
                <div class="shortcode-content">
                    <?php echo wp_kses_post($content); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($items)): ?>
                <div class="shortcode-items">
                    <?php foreach ($items as $item): ?>
                        <div class="shortcode-item">
                            
                            <h4 class="item-title">
                                <a href="<?php echo esc_url($item['url']); ?>">
                                    <?php echo esc_html($item['title']); ?>
                                </a>
                            </h4>
                            
                            <?php if ($atts['show_date'] && !empty($item['date'])): ?>
                                <span class="item-date">
                                    <?php echo esc_html($item['date']); ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($item['excerpt'])): ?>
                                <div class="item-excerpt">
                                    <?php echo wp_kses_post($item['excerpt']); ?>
                                </div>
                            <?php endif; ?>
                            
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-items">
                    <?php esc_html_e('PLACEHOLDER: No items found.', 'shahi-template'); ?>
                </p>
            <?php endif; ?>
            
        </div>
        <?php
    }
    
    /**
     * Sanitize shortcode attributes
     * 
     * PLACEHOLDER: Add sanitization for all your attributes
     * 
     * @since 1.0.0
     * @param array $atts Raw attributes
     * @return array Sanitized attributes
     */
    protected function sanitize_attributes($atts) {
        $sanitized = [];
        
        // PLACEHOLDER: Sanitize each attribute based on its type
        $sanitized['title']     = !empty($atts['title']) 
            ? sanitize_text_field($atts['title']) 
            : '';
        
        $sanitized['count']     = !empty($atts['count']) 
            ? absint($atts['count']) 
            : 5;
        
        $sanitized['show_date'] = filter_var($atts['show_date'], FILTER_VALIDATE_BOOLEAN);
        
        $sanitized['category']  = !empty($atts['category']) 
            ? sanitize_text_field($atts['category']) 
            : '';
        
        $sanitized['order']     = in_array(strtoupper($atts['order']), ['ASC', 'DESC']) 
            ? strtoupper($atts['order']) 
            : 'DESC';
        
        $sanitized['orderby']   = !empty($atts['orderby']) 
            ? sanitize_key($atts['orderby']) 
            : 'date';
        
        $sanitized['class']     = !empty($atts['class']) 
            ? sanitize_html_class($atts['class']) 
            : '';
        
        return $sanitized;
    }
    
    /**
     * Get items to display in shortcode
     * 
     * PLACEHOLDER: Fetch the data that will be displayed in the shortcode.
     * Replace this with your actual data fetching logic.
     * 
     * @since 1.0.0
     * @param array $atts Shortcode attributes
     * @return array Array of items
     */
    protected function get_items($atts) {
        // PLACEHOLDER: Build query based on attributes
        // Example: Query posts
        /*
        $query_args = [
            'posts_per_page'      => $atts['count'],
            'order'               => $atts['order'],
            'orderby'             => $atts['orderby'],
            'post_status'         => 'publish',
            'ignore_sticky_posts' => true,
        ];
        
        // Add category filter if specified
        if (!empty($atts['category'])) {
            $query_args['category_name'] = $atts['category'];
        }
        
        $query = new WP_Query($query_args);
        $items = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $items[] = [
                    'title'   => get_the_title(),
                    'url'     => get_permalink(),
                    'date'    => get_the_date(),
                    'excerpt' => get_the_excerpt(),
                ];
            }
            wp_reset_postdata();
        }
        
        return $items;
        */
        
        // PLACEHOLDER: Mock data for demonstration
        $items = [];
        for ($i = 1; $i <= $atts['count']; $i++) {
            $items[] = [
                'title'   => 'PLACEHOLDER: Item ' . $i,
                'url'     => home_url('/'),
                'date'    => current_time('mysql'),
                'excerpt' => 'PLACEHOLDER: This is a sample excerpt for item ' . $i,
            ];
        }
        
        return $items;
    }
    
    /**
     * Maybe enqueue assets
     * 
     * PLACEHOLDER: Conditionally enqueue CSS/JS only when shortcode is used
     * 
     * @since 1.0.0
     * @return void
     */
    public function maybe_enqueue_assets() {
        global $post;
        
        // Check if shortcode is present in the content
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, $this->tag)) {
            $this->enqueue_assets();
        }
    }
    
    /**
     * Enqueue shortcode assets
     * 
     * PLACEHOLDER: Load CSS and JavaScript files for the shortcode
     * 
     * @since 1.0.0
     * @return void
     */
    protected function enqueue_assets() {
        // PLACEHOLDER: Enqueue CSS
        /*
        wp_enqueue_style(
            'shahi-shortcode-' . $this->tag,
            plugin_dir_url(__FILE__) . '../assets/css/shortcodes/' . $this->tag . '.css',
            [],
            '1.0.0'
        );
        */
        
        // PLACEHOLDER: Enqueue JavaScript
        /*
        wp_enqueue_script(
            'shahi-shortcode-' . $this->tag,
            plugin_dir_url(__FILE__) . '../assets/js/shortcodes/' . $this->tag . '.js',
            ['jquery'],
            '1.0.0',
            true
        );
        
        // Localize script with data
        wp_localize_script('shahi-shortcode-' . $this->tag, 'ShortcodeData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('shahi_' . $this->tag . '_nonce'),
        ]);
        */
    }
    
    /**
     * Get shortcode tag
     * 
     * @since 1.0.0
     * @return string Shortcode tag
     */
    public function get_tag() {
        return $this->tag;
    }
    
    /**
     * Get default attributes
     * 
     * @since 1.0.0
     * @return array Default attributes
     */
    public function get_default_atts() {
        return $this->default_atts;
    }
}

/**
 * Helper function to render shortcode
 * 
 * PLACEHOLDER: Optional helper function for programmatic shortcode rendering
 * 
 * @since 1.0.0
 * @param array $atts Shortcode attributes
 * @return string Shortcode output
 */
function render_{shortcode_tag}($atts = []) {
    $shortcode = new {ShortcodeName}_Shortcode();
    return $shortcode->render($atts);
}

/**
 * Register shortcode
 * 
 * PLACEHOLDER: Add this to your main plugin file or module initialization
 * 
 * Example:
 * add_action('init', function() {
 *     new {PluginNamespace}\Shortcodes\{ShortcodeName}_Shortcode();
 * });
 * 
 * Or use the helper function:
 * add_shortcode('{shortcode-tag}', '{PluginNamespace}\Shortcodes\render_{shortcode_tag}');
 */
