<?php
/**
 * Link Accessibility Checker
 *
 * Validates link elements for accessibility compliance per WCAG 2.2
 * Level A/AA guidelines, focusing on link text quality and usability.
 *
 * Checks Performed:
 * - Empty links with no accessible text
 * - Ambiguous link text ("click here", "read more")
 * - Duplicate link text pointing to different destinations
 * - Links opening in new windows without warning
 *
 * @package ShahiLegalopsSuite
 * @subpackage Modules\AccessibilityScanner\Scanner\Checkers
 * @since 1.0.0
 * @version 1.0.0
 * @author Shahi Legal Ops Team
 * @license GPL-2.0-or-later
 *
 * @see https://www.w3.org/WAI/WCAG22/Understanding/link-purpose-in-context WCAG 2.4.4
 * @see https://www.w3.org/WAI/WCAG22/Understanding/link-purpose-link-only WCAG 2.4.9
 */

namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\AbstractChecker;

/**
 * Link Accessibility Checker Class
 *
 * Validates link elements for proper accessible text and clear purpose.
 * Implements WCAG 2.2 Success Criteria:
 * - 2.4.4 Link Purpose (In Context) - Level A
 * - 2.4.9 Link Purpose (Link Only) - Level AAA
 * - 3.2.5 Change on Request - Level AAA
 *
 * @since 1.0.0
 */
class LinkChecker extends AbstractChecker {

    /**
     * Ambiguous link text patterns that don't describe destination
     *
     * @var array
     */
    private $ambiguous_patterns = [
        'click here',
        'here',
        'read more',
        'more',
        'learn more',
        'continue',
        'continue reading',
        'this page',
        'this website',
        'this link',
        'link',
        'download',
        'view',
        'see more',
        'details',
        'info',
        'information',
    ];

    /**
     * Get check type identifier
     *
     * @return string Check type
     */
    public function get_check_type() {
        return 'link';
    }

    /**
     * Get check name
     *
     * @return string Check name
     */
    public function get_check_name() {
        return 'Link Accessibility';
    }

    /**
     * Run all link accessibility checks
     *
     * Executes comprehensive validation of link elements including:
     * - Empty links without accessible text
     * - Ambiguous link text
     * - Duplicate link text with different destinations
     * - Links opening new windows without warning
     *
     * @param \DOMDocument $dom   DOM document to check
     * @param \DOMXPath    $xpath XPath instance for querying
     * @return void
     */
    public function check($dom, $xpath) {
        $this->check_empty_links($xpath);
        $this->check_ambiguous_link_text($xpath);
        $this->check_duplicate_link_text($xpath);
        $this->check_new_window_links($xpath);
    }

    /**
     * Check for empty links
     *
     * Identifies link elements with no accessible text content.
     * Empty links are unusable for screen reader users and keyboard navigators.
     * WCAG 2.2 Level A violation (Success Criterion 2.4.4).
     *
     * @param \DOMXPath $xpath XPath instance for querying
     * @return void
     */
    private function check_empty_links($xpath) {
        // Find all links
        $links = $xpath->query('//a[@href]');

        foreach ($links as $link) {
            // Skip if link is hidden
            if ($this->is_hidden($link)) {
                continue;
            }

            // Get accessible name
            $accessible_name = $this->get_accessible_name($link);

            // Check if link is empty
            if (trim($accessible_name) === '') {
                $href = $link->getAttribute('href');
                $selector = $this->get_selector($link);

                // Check if link contains only images
                $images = $xpath->query('.//img', $link);
                $has_images = $images->length > 0;

                // Check if images have alt text
                $image_context = [];
                if ($has_images) {
                    foreach ($images as $img) {
                        $alt = $img->getAttribute('alt');
                        $image_context[] = [
                            'src' => $img->getAttribute('src'),
                            'alt' => $alt,
                            'has_alt' => $img->hasAttribute('alt'),
                        ];
                    }
                }

                $this->add_issue([
                    'type' => 'empty_link',
                    'severity' => 'critical',
                    'element' => 'a',
                    'wcag_criterion' => '2.4.4',
                    'wcag_level' => 'A',
                    'message' => 'Link has no accessible text',
                    'description' => sprintf(
                        'The link to "%s" has no accessible text content. Links must have descriptive text or aria-label for screen readers. %s',
                        $href,
                        $has_images ? 'The link contains images - ensure they have descriptive alt text.' : ''
                    ),
                    'selector' => $selector,
                    'html' => $this->get_outer_html($link),
                    'recommendation' => $has_images 
                        ? 'Add descriptive alt text to the images, or add aria-label to the link element.'
                        : 'Add descriptive text content or aria-label that describes the link destination.',
                    'context' => [
                        'href' => $href,
                        'has_images' => $has_images,
                        'image_count' => $images->length,
                        'images' => $image_context,
                        'inner_html' => $this->get_inner_html($link),
                    ]
                ]);
            }
        }
    }

    /**
     * Check for ambiguous link text
     *
     * Identifies links with generic text that doesn't describe the destination
     * (e.g., "click here", "read more"). Such text is not helpful when taken
     * out of context by screen readers.
     *
     * @param \DOMXPath $xpath XPath instance for querying
     * @return void
     */
    private function check_ambiguous_link_text($xpath) {
        // Find all links
        $links = $xpath->query('//a[@href]');

        foreach ($links as $link) {
            // Skip if link is hidden
            if ($this->is_hidden($link)) {
                continue;
            }

            // Get accessible name
            $accessible_name = trim($this->get_accessible_name($link));
            
            if ($accessible_name === '') {
                continue; // Already caught by empty_links check
            }

            // Check if link text is ambiguous
            $is_ambiguous = false;
            $matched_pattern = '';

            $normalized_text = strtolower($accessible_name);
            foreach ($this->ambiguous_patterns as $pattern) {
                if ($normalized_text === $pattern) {
                    $is_ambiguous = true;
                    $matched_pattern = $pattern;
                    break;
                }
            }

            if ($is_ambiguous) {
                $href = $link->getAttribute('href');
                $selector = $this->get_selector($link);

                // Try to get context from surrounding text
                $context_text = $this->get_link_context($link, $xpath);

                $this->add_issue([
                    'type' => 'ambiguous_link_text',
                    'severity' => 'serious',
                    'element' => 'a',
                    'wcag_criterion' => '2.4.4',
                    'wcag_level' => 'A',
                    'message' => 'Link text is ambiguous',
                    'description' => sprintf(
                        'The link "%s" uses ambiguous text that doesn\'t describe its destination. When screen readers list links out of context, users cannot understand where this link leads.',
                        $accessible_name
                    ),
                    'selector' => $selector,
                    'html' => $this->get_outer_html($link),
                    'recommendation' => sprintf(
                        'Change link text to be more descriptive. %s Instead of "%s", use text that describes the destination or action.',
                        $context_text ? "Context suggests: \"$context_text\"." : '',
                        $accessible_name
                    ),
                    'context' => [
                        'link_text' => $accessible_name,
                        'href' => $href,
                        'matched_pattern' => $matched_pattern,
                        'surrounding_context' => $context_text,
                    ]
                ]);
            }
        }
    }

    /**
     * Check for duplicate link text with different destinations
     *
     * Identifies links with identical text that point to different URLs.
     * This can confuse users as they expect the same text to lead to the
     * same destination.
     *
     * @param \DOMXPath $xpath XPath instance for querying
     * @return void
     */
    private function check_duplicate_link_text($xpath) {
        // Find all visible links
        $links = $xpath->query('//a[@href]');
        
        // Build map of link text to destinations
        $link_map = [];
        $link_elements = [];
        
        foreach ($links as $link) {
            if ($this->is_hidden($link)) {
                continue;
            }

            $accessible_name = trim($this->get_accessible_name($link));
            if ($accessible_name === '') {
                continue; // Already caught by empty_links check
            }

            $href = $link->getAttribute('href');
            
            // Normalize href (remove fragments, trailing slashes for comparison)
            $normalized_href = $this->normalize_url($href);

            // Skip empty hrefs or javascript: links
            if (empty($normalized_href) || strpos($normalized_href, 'javascript:') === 0) {
                continue;
            }

            // Store link info
            $link_key = strtolower($accessible_name);
            
            if (!isset($link_map[$link_key])) {
                $link_map[$link_key] = [];
                $link_elements[$link_key] = [];
            }

            $link_map[$link_key][] = $normalized_href;
            $link_elements[$link_key][] = [
                'element' => $link,
                'href' => $href,
                'text' => $accessible_name,
            ];
        }

        // Check for duplicate text with different destinations
        foreach ($link_map as $link_text => $destinations) {
            // Get unique destinations
            $unique_destinations = array_unique($destinations);
            
            // If same text points to different URLs, report issue
            if (count($unique_destinations) > 1) {
                // Report for each link after the first
                $elements = $link_elements[$link_text];
                
                foreach ($elements as $index => $link_data) {
                    $link = $link_data['element'];
                    $selector = $this->get_selector($link);

                    $this->add_issue([
                        'type' => 'duplicate_link_text',
                        'severity' => 'moderate',
                        'element' => 'a',
                        'wcag_criterion' => '2.4.4',
                        'wcag_level' => 'A',
                        'message' => 'Duplicate link text with different destinations',
                        'description' => sprintf(
                            'The link text "%s" appears %d times on the page but points to %d different destinations. Users expect identical link text to lead to the same place.',
                            $link_data['text'],
                            count($elements),
                            count($unique_destinations)
                        ),
                        'selector' => $selector,
                        'html' => $this->get_outer_html($link),
                        'recommendation' => 'Make link text unique and descriptive of its specific destination, or use aria-label to differentiate between links with similar visible text.',
                        'context' => [
                            'link_text' => $link_data['text'],
                            'this_href' => $link_data['href'],
                            'all_destinations' => array_values($unique_destinations),
                            'occurrence_count' => count($elements),
                            'this_occurrence' => $index + 1,
                        ]
                    ]);
                }
            }
        }
    }

    /**
     * Check for links opening in new windows without warning
     *
     * Identifies links that open in new windows/tabs (target="_blank")
     * without warning users. Unexpected new windows can disorient users,
     * especially screen reader users.
     *
     * @param \DOMXPath $xpath XPath instance for querying
     * @return void
     */
    private function check_new_window_links($xpath) {
        // Find all links with target="_blank"
        $links = $xpath->query('//a[@href and @target="_blank"]');

        foreach ($links as $link) {
            // Skip if link is hidden
            if ($this->is_hidden($link)) {
                continue;
            }

            $accessible_name = trim($this->get_accessible_name($link));
            $href = $link->getAttribute('href');
            $selector = $this->get_selector($link);

            // Check if link text indicates new window
            $has_warning = $this->has_new_window_warning($accessible_name, $link, $xpath);

            if (!$has_warning) {
                $this->add_issue([
                    'type' => 'new_window_no_warning',
                    'severity' => 'moderate',
                    'element' => 'a',
                    'wcag_criterion' => '3.2.5',
                    'wcag_level' => 'AAA',
                    'message' => 'Link opens in new window without warning',
                    'description' => sprintf(
                        'The link "%s" opens in a new window/tab (target="_blank") but doesn\'t warn users. Unexpected new windows can disorient users, especially those using screen readers.',
                        $accessible_name
                    ),
                    'selector' => $selector,
                    'html' => $this->get_outer_html($link),
                    'recommendation' => 'Add text indicating the link opens in a new window (e.g., "opens in new window") or add aria-label. Also add rel="noopener noreferrer" for security.',
                    'context' => [
                        'link_text' => $accessible_name,
                        'href' => $href,
                        'has_rel_noopener' => strpos($link->getAttribute('rel'), 'noopener') !== false,
                        'has_rel_noreferrer' => strpos($link->getAttribute('rel'), 'noreferrer') !== false,
                    ]
                ]);
            }
        }
    }

    /**
     * Get link context from surrounding text
     *
     * Attempts to extract contextual information from text surrounding
     * the link to help suggest better link text.
     *
     * @param \DOMElement $link  Link element
     * @param \DOMXPath   $xpath XPath instance
     * @return string Surrounding context text
     */
    private function get_link_context($link, $xpath) {
        $context_parts = [];

        // Try to get text from parent paragraph or list item
        $parent = $link->parentNode;
        if ($parent) {
            $parent_text = $this->get_text_content($parent);
            
            // Remove the link text itself from context
            $link_text = $this->get_text_content($link);
            $parent_text = str_replace($link_text, '', $parent_text);
            $parent_text = trim(preg_replace('/\s+/', ' ', $parent_text));
            
            if (!empty($parent_text) && mb_strlen($parent_text) > 5) {
                // Limit to 100 chars
                $context_parts[] = mb_substr($parent_text, 0, 100);
            }
        }

        return implode(' ', $context_parts);
    }

    /**
     * Check if link has new window warning
     *
     * Determines if a link indicates that it opens in a new window
     * through text, aria-label, or title attributes.
     *
     * @param string      $accessible_name Link accessible name
     * @param \DOMElement $link           Link element
     * @param \DOMXPath   $xpath          XPath instance
     * @return bool True if warning present, false otherwise
     */
    private function has_new_window_warning($accessible_name, $link, $xpath) {
        $warning_phrases = [
            'new window',
            'new tab',
            'opens in new',
            'external link',
            'opens externally',
        ];

        // Check accessible name
        $text_lower = strtolower($accessible_name);
        foreach ($warning_phrases as $phrase) {
            if (strpos($text_lower, $phrase) !== false) {
                return true;
            }
        }

        // Check title attribute
        $title = strtolower($link->getAttribute('title'));
        foreach ($warning_phrases as $phrase) {
            if (strpos($title, $phrase) !== false) {
                return true;
            }
        }

        // Check for icon indicating new window (common patterns)
        $has_external_icon = $xpath->query('.//svg[contains(@class, "external") or contains(@class, "new-window")]', $link)->length > 0;
        if ($has_external_icon) {
            return true;
        }

        // Check for screen reader only text
        $sr_only = $xpath->query('.//*[contains(@class, "sr-only") or contains(@class, "screen-reader-text") or contains(@class, "visually-hidden")]', $link);
        foreach ($sr_only as $element) {
            $text = strtolower($this->get_text_content($element));
            foreach ($warning_phrases as $phrase) {
                if (strpos($text, $phrase) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Normalize URL for comparison
     *
     * Removes fragments, trailing slashes, and converts to lowercase
     * for consistent comparison of URLs.
     *
     * @param string $url URL to normalize
     * @return string Normalized URL
     */
    private function normalize_url($url) {
        // Remove fragment
        $url = preg_replace('/#.*$/', '', $url);
        
        // Remove trailing slash
        $url = rtrim($url, '/');
        
        // Convert to lowercase for comparison
        $url = strtolower($url);
        
        return $url;
    }
}
