<?php
/**
 * Image Accessibility Checker
 *
 * Validates image elements for accessibility compliance, focusing on
 * alternative text requirements per WCAG 2.2 Level A/AA guidelines.
 *
 * Checks Performed:
 * - Missing alt attributes on images
 * - Empty/inadequate alt text on informative images
 * - Alt text quality (length, content, redundancy)
 *
 * @package ShahiLegalopsSuite
 * @subpackage Modules\AccessibilityScanner\Scanner\Checkers
 * @since 1.0.0
 * @version 1.0.0
 * @author Shahi Legal Ops Team
 * @license GPL-2.0-or-later
 *
 * @see https://www.w3.org/WAI/WCAG22/Understanding/non-text-content WCAG 1.1.1
 */

namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\AbstractChecker;

/**
 * Image Accessibility Checker Class
 *
 * Validates image elements for proper alternative text and accessibility attributes.
 * Implements WCAG 2.2 Success Criterion 1.1.1 (Non-text Content) - Level A.
 *
 * @since 1.0.0
 */
class ImageChecker extends AbstractChecker {

    /**
     * List of file extensions that indicate decorative images
     *
     * @var array
     */
    private $decorative_patterns = [
        'spacer', 'separator', 'divider', 'bullet', 'arrow',
        'icon-', 'bg-', 'background-', 'decoration'
    ];

    /**
     * Redundant phrases commonly found in alt text
     *
     * @var array
     */
    private $redundant_phrases = [
        'image of', 'picture of', 'photo of', 'graphic of',
        'screenshot of', 'illustration of', 'icon of'
    ];

    /**
     * Minimum acceptable alt text length (characters)
     *
     * @var int
     */
    private $min_alt_length = 3;

    /**
     * Maximum recommended alt text length (characters)
     *
     * @var int
     */
    private $max_alt_length = 150;

    /**
     * Get check type identifier
     *
     * @return string Check type
     */
    public function get_check_type() {
        return 'image';
    }

    /**
     * Get check name
     *
     * @return string Check name
     */
    public function get_check_name() {
        return 'Image Accessibility';
    }

    /**
     * Run all image accessibility checks
     *
     * Executes comprehensive validation of image elements including:
     * - Missing alt attributes
     * - Empty alt text on informative images
     * - Alt text quality and content
     *
     * @param \DOMDocument $dom   DOM document to check
     * @param \DOMXPath    $xpath XPath instance for querying
     * @return void
     */
    public function check($dom, $xpath) {
        $this->check_missing_alt($xpath);
        $this->check_empty_alt_on_informative($xpath);
        $this->check_alt_quality($xpath);
    }

    /**
     * Check for images missing alt attributes
     *
     * Identifies <img> elements without alt attributes, which is a
     * WCAG 2.2 Level A violation (Success Criterion 1.1.1).
     * All images must have an alt attribute, even if empty for decorative images.
     *
     * @param \DOMXPath $xpath XPath instance for querying
     * @return void
     */
    private function check_missing_alt($xpath) {
        // Find all img elements without alt attribute
        $images = $xpath->query('//img[not(@alt)]');

        foreach ($images as $img) {
            // Skip if image is hidden
            if ($this->is_hidden($img)) {
                continue;
            }

            $src = $img->getAttribute('src');
            $selector = $this->get_selector($img);

            $this->add_issue([
                'type' => 'missing_alt',
                'severity' => 'critical',
                'element' => 'img',
                'wcag_criterion' => '1.1.1',
                'wcag_level' => 'A',
                'message' => 'Image is missing an alt attribute',
                'description' => sprintf(
                    'The image "%s" does not have an alt attribute. All images must have an alt attribute to provide alternative text for screen readers. Use alt="" for decorative images.',
                    $src ?: '(no src)'
                ),
                'selector' => $selector,
                'html' => $this->get_outer_html($img),
                'recommendation' => 'Add an alt attribute with descriptive text. If the image is decorative, use alt="" (empty string).',
                'context' => [
                    'src' => $src,
                    'has_title' => $img->hasAttribute('title'),
                    'title' => $img->getAttribute('title'),
                ]
            ]);
        }
    }

    /**
     * Check for empty alt text on informative images
     *
     * Identifies images with empty alt="" that appear to be informative
     * (not decorative). Empty alt text should only be used for purely
     * decorative images that convey no meaningful content.
     *
     * @param \DOMXPath $xpath XPath instance for querying
     * @return void
     */
    private function check_empty_alt_on_informative($xpath) {
        // Find all img elements with empty alt attribute
        $images = $xpath->query('//img[@alt=""]');

        foreach ($images as $img) {
            // Skip if image is hidden
            if ($this->is_hidden($img)) {
                continue;
            }

            // Check if image appears to be decorative
            if ($this->is_likely_decorative($img)) {
                continue;
            }

            $src = $img->getAttribute('src');
            $selector = $this->get_selector($img);

            // Check context clues that suggest informative content
            $context_clues = $this->get_informative_clues($img, $xpath);

            if (!empty($context_clues)) {
                $this->add_issue([
                    'type' => 'empty_alt_informative',
                    'severity' => 'serious',
                    'element' => 'img',
                    'wcag_criterion' => '1.1.1',
                    'wcag_level' => 'A',
                    'message' => 'Image has empty alt text but appears to be informative',
                    'description' => sprintf(
                        'The image "%s" has empty alt text (alt="") but appears to convey meaningful information. Empty alt should only be used for purely decorative images. Reason: %s',
                        $src ?: '(no src)',
                        implode(', ', $context_clues)
                    ),
                    'selector' => $selector,
                    'html' => $this->get_outer_html($img),
                    'recommendation' => 'Add descriptive alt text that conveys the same information as the image, or confirm this is a decorative image.',
                    'context' => [
                        'src' => $src,
                        'clues' => $context_clues,
                        'parent_tag' => $img->parentNode ? $img->parentNode->nodeName : null,
                    ]
                ]);
            }
        }
    }

    /**
     * Check alt text quality
     *
     * Validates the quality of alt text on images, checking for:
     * - Minimum length requirements
     * - Maximum length recommendations
     * - Redundant phrases ("image of", "picture of", etc.)
     * - File names used as alt text
     * - Generic/placeholder text
     *
     * @param \DOMXPath $xpath XPath instance for querying
     * @return void
     */
    private function check_alt_quality($xpath) {
        // Find all img elements with non-empty alt attribute
        $images = $xpath->query('//img[@alt and @alt!=""]');

        foreach ($images as $img) {
            // Skip if image is hidden
            if ($this->is_hidden($img)) {
                continue;
            }

            $alt = trim($img->getAttribute('alt'));
            $src = $img->getAttribute('src');
            $selector = $this->get_selector($img);
            $issues = [];

            // Check 1: Alt text too short
            if (mb_strlen($alt) < $this->min_alt_length) {
                $issues[] = sprintf(
                    'Alt text is too short (%d characters). Provide more descriptive text.',
                    mb_strlen($alt)
                );
            }

            // Check 2: Alt text too long
            if (mb_strlen($alt) > $this->max_alt_length) {
                $issues[] = sprintf(
                    'Alt text is too long (%d characters). Consider using a caption or nearby text for detailed descriptions.',
                    mb_strlen($alt)
                );
            }

            // Check 3: Redundant phrases
            $alt_lower = strtolower($alt);
            foreach ($this->redundant_phrases as $phrase) {
                if (strpos($alt_lower, $phrase) === 0) {
                    $issues[] = sprintf(
                        'Alt text starts with redundant phrase "%s". Remove it and start with the description.',
                        $phrase
                    );
                    break;
                }
            }

            // Check 4: Filename used as alt text
            if ($src && $this->is_filename_as_alt($alt, $src)) {
                $issues[] = 'Alt text appears to be a filename. Use descriptive text instead.';
            }

            // Check 5: Generic placeholder text
            if ($this->is_generic_alt($alt)) {
                $issues[] = 'Alt text is generic or placeholder text. Provide specific description of the image content.';
            }

            // Report issues if any found
            if (!empty($issues)) {
                $this->add_issue([
                    'type' => 'poor_alt_quality',
                    'severity' => 'moderate',
                    'element' => 'img',
                    'wcag_criterion' => '1.1.1',
                    'wcag_level' => 'A',
                    'message' => 'Image alt text quality needs improvement',
                    'description' => sprintf(
                        'The image "%s" has alt text "%s" with the following quality issues: %s',
                        $src ?: '(no src)',
                        $alt,
                        implode(' ', $issues)
                    ),
                    'selector' => $selector,
                    'html' => $this->get_outer_html($img),
                    'recommendation' => 'Revise alt text to be concise, descriptive, and free of redundant phrases or generic placeholders.',
                    'context' => [
                        'alt' => $alt,
                        'alt_length' => mb_strlen($alt),
                        'src' => $src,
                        'quality_issues' => $issues,
                    ]
                ]);
            }
        }
    }

    /**
     * Determine if image is likely decorative
     *
     * Analyzes image attributes and context to determine if it's
     * likely a decorative image that doesn't require alt text.
     *
     * @param \DOMElement $img Image element to check
     * @return bool True if likely decorative, false otherwise
     */
    private function is_likely_decorative($img) {
        $src = strtolower($img->getAttribute('src'));
        
        // Check for decorative patterns in filename
        foreach ($this->decorative_patterns as $pattern) {
            if (strpos($src, $pattern) !== false) {
                return true;
            }
        }

        // Check for role="presentation" or role="none"
        $role = $img->getAttribute('role');
        if (in_array($role, ['presentation', 'none'])) {
            return true;
        }

        // Check for very small dimensions (likely decorative)
        $width = $img->getAttribute('width');
        $height = $img->getAttribute('height');
        if ($width && $height && (int)$width <= 5 && (int)$height <= 5) {
            return true;
        }

        // Check for CSS class names indicating decorative
        $class = strtolower($img->getAttribute('class'));
        if (preg_match('/(decorative|decoration|spacer|separator|divider|bg-|background)/i', $class)) {
            return true;
        }

        return false;
    }

    /**
     * Get clues that suggest image is informative
     *
     * Analyzes image context to identify indicators that suggest
     * the image conveys meaningful information.
     *
     * @param \DOMElement $img   Image element to check
     * @param \DOMXPath   $xpath XPath instance for querying
     * @return array List of clues found
     */
    private function get_informative_clues($img, $xpath) {
        $clues = [];

        // Clue 1: Image is a link
        $parent = $img->parentNode;
        if ($parent && $parent->nodeName === 'a') {
            $clues[] = 'image is inside a link';
        }

        // Clue 2: Image has significant dimensions
        $width = $img->getAttribute('width');
        $height = $img->getAttribute('height');
        if ($width && $height && ((int)$width > 100 || (int)$height > 100)) {
            $clues[] = 'image has significant dimensions';
        }

        // Clue 3: Image has title attribute (suggests informative)
        if ($img->hasAttribute('title') && $img->getAttribute('title')) {
            $clues[] = 'image has title attribute';
        }

        // Clue 4: Image is in a figure element
        $figure_parent = $xpath->query('ancestor::figure', $img);
        if ($figure_parent->length > 0) {
            $clues[] = 'image is inside a figure element';
        }

        // Clue 5: Image has caption nearby
        if ($parent) {
            $siblings = $xpath->query('following-sibling::figcaption | preceding-sibling::figcaption', $img);
            if ($siblings->length > 0) {
                $clues[] = 'image has associated figcaption';
            }
        }

        // Clue 6: Image is part of content (not in header/footer/aside)
        $content_context = $xpath->query('ancestor::article | ancestor::main', $img);
        if ($content_context->length > 0) {
            $clues[] = 'image is within main content area';
        }

        // Clue 7: Image has data attributes (often indicates dynamic content)
        foreach ($img->attributes as $attr) {
            if (strpos($attr->name, 'data-') === 0) {
                $clues[] = 'image has data attributes suggesting dynamic content';
                break;
            }
        }

        return $clues;
    }

    /**
     * Check if alt text is just a filename
     *
     * Determines if the alt text appears to be derived from the
     * image filename rather than being descriptive text.
     *
     * @param string $alt Alt text to check
     * @param string $src Image source URL
     * @return bool True if alt appears to be a filename, false otherwise
     */
    private function is_filename_as_alt($alt, $src) {
        // Get filename from src
        $filename = basename($src);
        $filename_without_ext = pathinfo($filename, PATHINFO_FILENAME);

        // Remove common separators and compare
        $normalized_alt = preg_replace('/[_\-\s]+/', '', strtolower($alt));
        $normalized_filename = preg_replace('/[_\-\s]+/', '', strtolower($filename_without_ext));

        // Check if they're very similar
        if ($normalized_alt === $normalized_filename) {
            return true;
        }

        // Check for file extensions in alt text
        if (preg_match('/\.(jpg|jpeg|png|gif|svg|webp|bmp)$/i', $alt)) {
            return true;
        }

        return false;
    }

    /**
     * Check if alt text is generic/placeholder
     *
     * Identifies common generic or placeholder alt text that
     * doesn't provide meaningful description.
     *
     * @param string $alt Alt text to check
     * @return bool True if generic, false otherwise
     */
    private function is_generic_alt($alt) {
        $generic_patterns = [
            'image',
            'img',
            'picture',
            'photo',
            'graphic',
            'icon',
            'logo',
            'banner',
            'untitled',
            'placeholder',
            'temp',
            'test',
            'xxx',
            '???',
        ];

        $alt_lower = strtolower(trim($alt));

        // Check exact matches
        if (in_array($alt_lower, $generic_patterns)) {
            return true;
        }

        // Check for numbered patterns like "image1", "photo2"
        foreach ($generic_patterns as $pattern) {
            if (preg_match('/^' . preg_quote($pattern, '/') . '\d*$/i', $alt_lower)) {
                return true;
            }
        }

        return false;
    }
}
