<?php
namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Fixes;

use ShahiLegalopsSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry;

class AccessibilityFixer {
    
    private $active_fixes = [];

    public function __construct() {
        // Load active fixes from settings
        $this->active_fixes = get_option('slos_active_fixes', []);
        
        // Initialize hooks
        add_action('wp_enqueue_scripts', [$this, 'enqueue_fix_assets']);
        add_action('wp_footer', [$this, 'inject_js_fixes']);
        add_filter('language_attributes', [$this, 'fix_language_attributes']);
        add_filter('the_content', [$this, 'apply_content_fixes'], 20);
        add_action('wp_head', [$this, 'fix_viewport_meta'], 1);
        add_filter('pre_get_document_title', [$this, 'fix_page_title'], 99);
        add_filter('wp_title', [$this, 'fix_page_title'], 99);
    }

    public function enqueue_fix_assets() {
        wp_enqueue_style('slos-a11y-fixes', plugin_dir_url(__FILE__) . '../../../../assets/css/slos-a11y-fixes.css', [], '1.0.0');
        
        // Add body classes for CSS fixes
        $classes = [];
        if (in_array('fix_focus_outlines', $this->active_fixes)) $classes[] = 'slos-fix-focus-outlines';
        if (in_array('fix_link_underlines', $this->active_fixes)) $classes[] = 'slos-fix-link-underlines';
        if (in_array('fix_link_warnings', $this->active_fixes)) $classes[] = 'slos-fix-link-warnings';
        if (in_array('fix_color_contrast', $this->active_fixes)) $classes[] = 'slos-fix-color-contrast';
        
        if (!empty($classes)) {
            add_filter('body_class', function($body_classes) use ($classes) {
                return array_merge($body_classes, $classes);
            });
        }
    }

    public function fix_page_title($title) {
        if (in_array('add_page_titles', $this->active_fixes)) {
            if (empty($title)) {
                return get_bloginfo('name');
            }
        }
        return $title;
    }

    public function inject_js_fixes() {
        ?>
        <script>
        (function($) {
            'use strict';
            $(document).ready(function() {
                <?php if (in_array('skip_links', $this->active_fixes)): ?>
                // 1. Add Skip Links
                if ($('#skip-link').length === 0) {
                    $('body').prepend('<a id="skip-link" class="slos-skip-link" href="#main">Skip to content</a>');
                    if ($('#main').length === 0) {
                        $('main, [role="main"], article, .content-area').first().attr('id', 'main');
                    }
                }
                <?php endif; ?>

                <?php if (in_array('block_new_window', $this->active_fixes)): ?>
                // 4. Block New Window Links
                $('a[target="_blank"]').removeAttr('target');
                <?php endif; ?>

                <?php if (in_array('label_search', $this->active_fixes)): ?>
                // 7. Label Search Fields
                $('input[type="search"], .search-field').each(function() {
                    if (!$(this).attr('aria-label') && !$(this).attr('id')) {
                        $(this).attr('aria-label', 'Search');
                    }
                });
                <?php endif; ?>

                <?php if (in_array('label_comments', $this->active_fixes)): ?>
                // 8. Label Comment Fields
                $('#comment').attr('aria-label', 'Comment');
                $('input#author').attr('aria-label', 'Name');
                $('input#email').attr('aria-label', 'Email');
                $('input#url').attr('aria-label', 'Website');
                <?php endif; ?>

                <?php if (in_array('fix_tab_index', $this->active_fixes)): ?>
                // 10. Fix Tab Index
                $('[tabindex]').each(function() {
                    if (parseInt($(this).attr('tabindex')) > 0) {
                        $(this).removeAttr('tabindex');
                    }
                });
                <?php endif; ?>

                <?php if (in_array('remove_title_attrs', $this->active_fixes)): ?>
                // 11. Remove Title Attributes
                $('[title]').removeAttr('title');
                <?php endif; ?>

                <?php if (in_array('add_landmarks', $this->active_fixes)): ?>
                // 13. Add ARIA Landmarks
                $('header:not([role])').attr('role', 'banner');
                $('nav:not([role])').attr('role', 'navigation');
                $('main:not([role])').attr('role', 'main');
                $('footer:not([role])').attr('role', 'contentinfo');
                $('aside:not([role])').attr('role', 'complementary');
                $('form[role="search"]').attr('role', 'search');
                <?php endif; ?>

                <?php if (in_array('add_form_labels', $this->active_fixes)): ?>
                // 17. Add Form Labels (from placeholders)
                $('input:not([type="submit"]):not([type="hidden"]):not([type="button"]), textarea, select').each(function() {
                    if (!$(this).attr('id') && !$(this).attr('aria-label') && !$(this).attr('aria-labelledby')) {
                        var placeholder = $(this).attr('placeholder');
                        if (placeholder) {
                            $(this).attr('aria-label', placeholder);
                        } else {
                            $(this).attr('aria-label', 'Input field');
                        }
                    }
                });
                <?php endif; ?>

                <?php if (in_array('add_button_labels', $this->active_fixes)): ?>
                // 21. Add Button Labels
                $('button, a.button, .btn').each(function() {
                    if ($(this).text().trim() === '' && !$(this).attr('aria-label')) {
                        var icon = $(this).find('i, span.icon, svg').first();
                        var label = 'Button';
                        if (icon.length) {
                            // Try to guess from class name
                            var classNames = icon.attr('class') || '';
                            if (classNames.includes('search')) label = 'Search';
                            else if (classNames.includes('menu')) label = 'Menu';
                            else if (classNames.includes('close')) label = 'Close';
                            else if (classNames.includes('facebook')) label = 'Facebook';
                            else if (classNames.includes('twitter')) label = 'Twitter';
                        }
                        $(this).attr('aria-label', label);
                    }
                });
                <?php endif; ?>

                <?php if (in_array('fix_modal_dialogs', $this->active_fixes)): ?>
                // 24. Fix Modal Dialogs
                $('.modal, .popup, .dialog, [class*="modal"], [class*="popup"]').attr({
                    'role': 'dialog',
                    'aria-modal': 'true'
                });
                <?php endif; ?>

                <?php if (in_array('fix_image_maps', $this->active_fixes)): ?>
                // 20. Fix Image Maps
                $('area').each(function() {
                    if (!$(this).attr('alt')) {
                        $(this).attr('alt', 'Image Map Area');
                    }
                });
                <?php endif; ?>

                <?php if (in_array('add_live_regions', $this->active_fixes)): ?>
                // 23. Add Live Regions
                $('.alert, .notice, .error, .success, [class*="message"], [class*="notification"]').attr('aria-live', 'polite');
                <?php endif; ?>

                <?php if (in_array('generate_transcripts', $this->active_fixes)): ?>
                // 25. Generate Transcripts (Structure)
                $('audio, video').each(function() {
                    if ($(this).next('.slos-transcript').length === 0) {
                        // Check if track exists
                        if ($(this).find('track[kind="captions"], track[kind="subtitles"]').length === 0) {
                            $(this).after('<div class="slos-transcript" style="margin-top: 10px; padding: 10px; background: #f9f9f9; border: 1px solid #ddd;"><p><strong>Transcript:</strong> <span style="font-style: italic; color: #666;">(No transcript available. Please contact site administrator.)</span></p></div>');
                        }
                    }
                });
                <?php endif; ?>
            });
        })(jQuery);
        </script>
        <?php
    }

    public function fix_language_attributes($output) {
        if (in_array('add_lang_attr', $this->active_fixes)) {
            if (strpos($output, 'lang=') === false) {
                $output .= ' lang="' . get_bloginfo('language') . '"';
            }
            if (strpos($output, 'dir=') === false) {
                $output .= ' dir="' . (is_rtl() ? 'rtl' : 'ltr') . '"';
            }
        }
        return $output;
    }

    public function fix_viewport_meta() {
        if (in_array('scalable_viewport', $this->active_fixes)) {
            // Remove existing viewport meta if possible (hard in WP without output buffering)
            // Instead, we can inject JS to fix it or rely on theme support.
            // Here we'll try to output a correct one that might override.
            echo '<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">';
        }
    }

    public function apply_content_fixes($content) {
        if (empty($content)) return $content;

        // 12. Add Alt Text Placeholders
        if (in_array('add_alt_placeholders', $this->active_fixes)) {
            $content = preg_replace('/<img(?![^>]*\balt=)[^>]*>/i', '$0 alt="[Image]"', $content);
            $content = preg_replace('/(<img[^>]*\balt=["\'])\s*(["\'][^>]*>)/i', '$1[Image]$2', $content);
        }

        // 14. Fix Empty Links
        if (in_array('fix_empty_links', $this->active_fixes)) {
            $content = preg_replace_callback('/<a([^>]*)>(.*?)<\/a>/is', function($matches) {
                $attrs = $matches[1];
                $text = trim(strip_tags($matches[2]));
                if (empty($text) && strpos($attrs, 'aria-label') === false) {
                    // Check if it has an image
                    if (strpos($matches[2], '<img') !== false) {
                        return $matches[0]; // Has image, assume image has alt (handled by other fix)
                    }
                    return '<a' . $attrs . ' aria-label="Link"></a>';
                }
                return $matches[0];
            }, $content);
        }

        // 16. Add Table Headers
        if (in_array('add_table_headers', $this->active_fixes)) {
            $content = preg_replace_callback('/<table[^>]*>.*?<\/table>/is', function($matches) {
                $table = $matches[0];
                // Find first tr
                if (preg_match('/<tr[^>]*>(.*?)<\/tr>/is', $table, $tr_match)) {
                    $first_row = $tr_match[1];
                    // Replace td with th in first row
                    $new_first_row = str_replace('<td', '<th scope="col"', $first_row);
                    $new_first_row = str_replace('</td>', '</th>', $new_first_row);
                    $table = str_replace($first_row, $new_first_row, $table);
                }
                return $table;
            }, $content);
        }

        // 22. Fix List Semantics (Wrap orphan li)
        if (in_array('fix_list_semantics', $this->active_fixes)) {
            // This is complex regex, simplified for now: ensure <li> are inside <ul> or <ol>
            // WP usually handles this well, but custom HTML might not.
        }

        return $content;
    }

    /**
     * Fix a specific issue on a page using content-aware fixers
     *
     * @param int $post_id The post ID to fix
     * @param string $issue_type The type of accessibility issue
     * @return array|WP_Error Array with 'fixed_count' and 'content' keys or WP_Error
     */
    public function fix_issue($post_id, $issue_type) {
        // Get page content
        $content = $this->get_page_content($post_id);
        
        if (empty($content)) {
            return new \WP_Error('content_not_found', 'Could not retrieve page content');
        }

        // Initialize fixer registry and get the appropriate fixer
        FixerRegistry::init();
        $fixer = FixerRegistry::get_fixer($issue_type);

        if (!$fixer) {
            return new \WP_Error('fixer_not_found', sprintf('No fixer available for issue type: %s', $issue_type));
        }

        // Apply the fix
        try {
            $result = $fixer->fix($content);
            
            if (!isset($result['fixed_count']) || !isset($result['content'])) {
                return new \WP_Error('invalid_fixer_result', 'Fixer returned invalid result');
            }

            // Update post content with fixed content
            $post_update = [
                'ID' => $post_id,
                'post_content' => $result['content']
            ];
            wp_update_post($post_update);

            return $result;
        } catch (\Exception $e) {
            return new \WP_Error('fixer_exception', sprintf('Error applying fix: %s', $e->getMessage()));
        }
    }

    /**
     * Get page content from post ID
     *
     * @param int $post_id
     * @return string|false
     */
    private function get_page_content($post_id) {
        $post = get_post($post_id);

        if (!$post) {
            return false;
        }

        // Get post content
        $content = $post->post_content;

        // Process shortcodes and apply the_content filters
        $content = do_shortcode($content);
        
        // Note: We don't apply the_content filter here because that may include
        // other filters that modify output. We want raw content for fixing.

        return $content;
    }
}
