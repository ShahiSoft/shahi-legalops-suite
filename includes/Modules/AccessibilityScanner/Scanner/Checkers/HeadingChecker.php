<?php
/**
 * Heading Accessibility Checker
 *
 * Validates heading elements for proper structure and accessibility compliance
 * per WCAG 2.2 Level A/AA guidelines.
 *
 * Checks Performed:
 * - Missing H1 headings on pages
 * - Multiple H1 headings (document structure)
 * - Heading hierarchy violations (skipped levels)
 * - Empty headings with no content
 *
 * @package ShahiLegalopsSuite
 * @subpackage Modules\AccessibilityScanner\Scanner\Checkers
 * @since 1.0.0
 * @version 1.0.0
 * @author Shahi Legal Ops Team
 * @license GPL-2.0-or-later
 *
 * @see https://www.w3.org/WAI/WCAG22/Understanding/info-and-relationships WCAG 1.3.1
 * @see https://www.w3.org/WAI/WCAG22/Understanding/headings-and-labels WCAG 2.4.6
 */

namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\AbstractChecker;

/**
 * Heading Accessibility Checker Class
 *
 * Validates heading structure and hierarchy for proper document outline
 * and screen reader navigation. Implements WCAG 2.2 Success Criteria:
 * - 1.3.1 Info and Relationships - Level A
 * - 2.4.6 Headings and Labels - Level AA
 *
 * @since 1.0.0
 */
class HeadingChecker extends AbstractChecker {

    /**
     * Valid heading levels
     *
     * @var array
     */
    private $valid_levels = [1, 2, 3, 4, 5, 6];

    /**
     * Get check type identifier
     *
     * @return string Check type
     */
    public function get_check_type() {
        return 'heading';
    }

    /**
     * Get check name
     *
     * @return string Check name
     */
    public function get_check_name() {
        return 'Heading Structure';
    }

    /**
     * Run all heading accessibility checks
     *
     * Executes comprehensive validation of heading elements including:
     * - Missing H1 on page
     * - Multiple H1 elements
     * - Heading hierarchy and skipped levels
     * - Empty headings
     *
     * @param \DOMDocument $dom   DOM document to check
     * @param \DOMXPath    $xpath XPath instance for querying
     * @return void
     */
    public function check($dom, $xpath) {
        $this->check_missing_h1($xpath);
        $this->check_multiple_h1($xpath);
        $this->check_heading_hierarchy($xpath);
        $this->check_empty_headings($xpath);
    }

    /**
     * Check for missing H1 heading
     *
     * Verifies that the page has at least one H1 heading element.
     * Missing H1 can affect document structure and screen reader navigation.
     * While not a strict WCAG violation, it's a best practice for accessibility.
     *
     * @param \DOMXPath $xpath XPath instance for querying
     * @return void
     */
    private function check_missing_h1($xpath) {
        // Find all visible H1 elements
        $h1_elements = $xpath->query('//h1');
        
        $visible_h1_count = 0;
        foreach ($h1_elements as $h1) {
            if (!$this->is_hidden($h1)) {
                $visible_h1_count++;
            }
        }

        // If no visible H1 found, report issue
        if ($visible_h1_count === 0) {
            $this->add_issue([
                'type' => 'missing_h1',
                'severity' => 'serious',
                'element' => 'h1',
                'wcag_criterion' => '2.4.6',
                'wcag_level' => 'AA',
                'message' => 'Page is missing an H1 heading',
                'description' => 'The page does not have a visible H1 heading. H1 headings provide the main title of the page and are essential for document structure and screen reader navigation.',
                'selector' => 'body',
                'html' => null,
                'recommendation' => 'Add an H1 heading that describes the main topic or purpose of the page. Each page should have exactly one visible H1.',
                'context' => [
                    'total_h1_count' => $h1_elements->length,
                    'visible_h1_count' => $visible_h1_count,
                    'has_any_headings' => $xpath->query('//h1|//h2|//h3|//h4|//h5|//h6')->length > 0,
                ]
            ]);
        }
    }

    /**
     * Check for multiple H1 headings
     *
     * Identifies pages with more than one visible H1 heading.
     * Multiple H1s can confuse document structure and screen reader users.
     * Best practice is to have exactly one H1 per page.
     *
     * @param \DOMXPath $xpath XPath instance for querying
     * @return void
     */
    private function check_multiple_h1($xpath) {
        // Find all H1 elements
        $h1_elements = $xpath->query('//h1');
        
        $visible_h1s = [];
        foreach ($h1_elements as $h1) {
            if (!$this->is_hidden($h1)) {
                $visible_h1s[] = $h1;
            }
        }

        // If more than one visible H1, report issue for each extra H1
        if (count($visible_h1s) > 1) {
            foreach ($visible_h1s as $index => $h1) {
                // Skip the first H1 (it's valid)
                if ($index === 0) {
                    continue;
                }

                $selector = $this->get_selector($h1);
                $text = $this->get_text_content($h1);

                $this->add_issue([
                    'type' => 'multiple_h1',
                    'severity' => 'moderate',
                    'element' => 'h1',
                    'wcag_criterion' => '2.4.6',
                    'wcag_level' => 'AA',
                    'message' => 'Page has multiple H1 headings',
                    'description' => sprintf(
                        'This is H1 #%d of %d on the page ("%s"). Pages should have exactly one H1 heading to maintain clear document structure. Additional main headings should use H2.',
                        $index + 1,
                        count($visible_h1s),
                        $text
                    ),
                    'selector' => $selector,
                    'html' => $this->get_outer_html($h1),
                    'recommendation' => 'Change this heading to H2 or lower if it\'s a subsection. Keep only one H1 for the main page title.',
                    'context' => [
                        'h1_index' => $index + 1,
                        'total_h1_count' => count($visible_h1s),
                        'h1_text' => $text,
                    ]
                ]);
            }
        }
    }

    /**
     * Check heading hierarchy
     *
     * Validates that headings follow proper hierarchical order without
     * skipping levels (e.g., H2 → H4 skips H3). Proper hierarchy is
     * essential for screen reader navigation and document structure.
     *
     * @param \DOMXPath $xpath XPath instance for querying
     * @return void
     */
    private function check_heading_hierarchy($xpath) {
        // Find all visible headings in document order
        $all_headings = $xpath->query('//h1|//h2|//h3|//h4|//h5|//h6');
        
        $visible_headings = [];
        foreach ($all_headings as $heading) {
            if (!$this->is_hidden($heading)) {
                $level = (int) substr($heading->nodeName, 1); // Extract number from h1-h6
                $visible_headings[] = [
                    'element' => $heading,
                    'level' => $level,
                    'text' => $this->get_text_content($heading),
                ];
            }
        }

        // Check hierarchy - each heading should not skip levels
        $previous_level = 0;
        
        foreach ($visible_headings as $index => $heading_data) {
            $current_level = $heading_data['level'];
            $heading = $heading_data['element'];

            // Skip first heading (no previous to compare)
            if ($index === 0) {
                $previous_level = $current_level;
                continue;
            }

            // Check if heading skips a level when going deeper
            if ($current_level > $previous_level + 1) {
                $skipped_levels = range($previous_level + 1, $current_level - 1);
                $selector = $this->get_selector($heading);

                $this->add_issue([
                    'type' => 'heading_hierarchy_skip',
                    'severity' => 'moderate',
                    'element' => $heading->nodeName,
                    'wcag_criterion' => '1.3.1',
                    'wcag_level' => 'A',
                    'message' => 'Heading level skipped',
                    'description' => sprintf(
                        'Heading "%s" jumps from H%d to H%d, skipping level(s) H%s. Heading levels should increment by one to maintain proper document structure.',
                        $heading_data['text'],
                        $previous_level,
                        $current_level,
                        implode(', H', $skipped_levels)
                    ),
                    'selector' => $selector,
                    'html' => $this->get_outer_html($heading),
                    'recommendation' => sprintf(
                        'Change this heading to H%d to maintain proper hierarchy, or add intermediate heading levels.',
                        $previous_level + 1
                    ),
                    'context' => [
                        'current_level' => $current_level,
                        'previous_level' => $previous_level,
                        'skipped_levels' => $skipped_levels,
                        'heading_text' => $heading_data['text'],
                        'heading_index' => $index,
                    ]
                ]);
            }

            $previous_level = $current_level;
        }
    }

    /**
     * Check for empty headings
     *
     * Identifies heading elements with no text content or only whitespace.
     * Empty headings confuse screen reader users and provide no value
     * to document structure.
     *
     * @param \DOMXPath $xpath XPath instance for querying
     * @return void
     */
    private function check_empty_headings($xpath) {
        // Find all heading elements
        $all_headings = $xpath->query('//h1|//h2|//h3|//h4|//h5|//h6');

        foreach ($all_headings as $heading) {
            // Skip if hidden
            if ($this->is_hidden($heading)) {
                continue;
            }

            // Get text content
            $text = $this->get_text_content($heading);
            
            // Check if heading is empty or only whitespace
            if (trim($text) === '') {
                // Check if heading has images (might be icon-based heading)
                $has_images = $xpath->query('.//img', $heading)->length > 0;
                
                // Check if heading has accessible name from ARIA
                $aria_label = $heading->getAttribute('aria-label');
                $aria_labelledby = $heading->getAttribute('aria-labelledby');
                $has_aria_name = !empty($aria_label) || !empty($aria_labelledby);

                $selector = $this->get_selector($heading);
                $level = substr($heading->nodeName, 1);

                // Determine severity and message based on content
                $severity = 'serious';
                $message = 'Empty heading';
                $description = sprintf(
                    'The H%s heading has no text content. Headings must contain meaningful text to help users understand page structure.',
                    $level
                );

                $context = [
                    'level' => $level,
                    'has_images' => $has_images,
                    'has_aria_label' => !empty($aria_label),
                    'has_aria_labelledby' => !empty($aria_labelledby),
                    'inner_html' => $this->get_inner_html($heading),
                ];

                // Adjust severity/message if there's an image or ARIA label
                if ($has_images && !$has_aria_name) {
                    $severity = 'moderate';
                    $message = 'Heading contains only image without text alternative';
                    $description = sprintf(
                        'The H%s heading contains an image but no text content or ARIA label. Add text or aria-label to provide accessible heading text.',
                        $level
                    );
                } elseif ($has_aria_name) {
                    // Has ARIA name but no visible text - less severe but still not ideal
                    $severity = 'minor';
                    $message = 'Heading has ARIA label but no visible text';
                    $description = sprintf(
                        'The H%s heading uses aria-label="%s" but has no visible text. Consider adding visible text for all users.',
                        $level,
                        $aria_label ?: '(referenced by aria-labelledby)'
                    );
                }

                $this->add_issue([
                    'type' => 'empty_heading',
                    'severity' => $severity,
                    'element' => $heading->nodeName,
                    'wcag_criterion' => '2.4.6',
                    'wcag_level' => 'AA',
                    'message' => $message,
                    'description' => $description,
                    'selector' => $selector,
                    'html' => $this->get_outer_html($heading),
                    'recommendation' => 'Add meaningful text content to the heading. If the heading contains an image, ensure the image has appropriate alt text or add aria-label to the heading.',
                    'context' => $context
                ]);
            }
        }
    }
}
