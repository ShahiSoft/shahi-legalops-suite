<?php
/**
 * Abstract Checker Base Class
 *
 * Foundation for all accessibility checkers. Defines the contract that all
 * checker implementations must follow and provides common utility methods.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Modules\AccessibilityScanner\Scanner
 * @since      1.0.0
 */

namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner;

use DOMDocument;
use DOMXPath;
use DOMElement;
use DOMNode;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AbstractChecker Class
 *
 * Base class for all accessibility checkers. Each checker focuses on
 * specific WCAG success criteria (e.g., images, headings, forms).
 *
 * @since 1.0.0
 */
abstract class AbstractChecker {
    
    /**
     * Detected accessibility issues
     *
     * @var array
     */
    protected $issues = [];
    
    /**
     * DOM Document reference
     *
     * @var DOMDocument
     */
    protected $dom;
    
    /**
     * XPath query interface
     *
     * @var DOMXPath
     */
    protected $xpath;
    
    /**
     * Execute accessibility checks
     *
     * Concrete checkers must implement this method to perform their
     * specific accessibility validations against the DOM.
     *
     * @since 1.0.0
     *
     * @param DOMDocument $dom   DOM document to check
     * @param DOMXPath    $xpath XPath query interface
     * @return void
     */
    abstract public function check($dom, $xpath);
    
    /**
     * Get checker name
     *
     * Human-readable name for this checker (e.g., "Image Alt Text Checker")
     *
     * @since 1.0.0
     *
     * @return string Checker name
     */
    abstract public function get_check_name();
    
    /**
     * Get checker type
     *
     * Machine-readable identifier (e.g., "image", "heading", "form")
     *
     * @since 1.0.0
     *
     * @return string Checker type identifier
     */
    abstract public function get_check_type();
    
    /**
     * Add an issue to the collection
     *
     * Merges provided issue data with default structure, ensuring
     * consistent issue format across all checkers.
     *
     * @since 1.0.0
     *
     * @param array $data Issue data with keys:
     *                    - severity: critical|serious|moderate|minor
     *                    - wcag_criterion: WCAG SC number (e.g., "1.1.1")
     *                    - wcag_level: A|AA|AAA
     *                    - element_selector: CSS selector for element
     *                    - element_html: Outer HTML of problematic element
     *                    - line_number: Line number in source
     *                    - issue_description: Description of the issue
     *                    - recommendation: How to fix the issue
     * @return void
     */
    protected function add_issue($data) {
        $default_issue = [
            'check_type' => $this->get_check_type(),
            'check_name' => $this->get_check_name(),
            'severity' => 'moderate',
            'wcag_criterion' => null,
            'wcag_level' => 'AA',
            'element_selector' => null,
            'element_html' => null,
            'line_number' => null,
            'issue_description' => '',
            'recommendation' => '',
        ];
        
        $this->issues[] = array_merge($default_issue, $data);
    }
    
    /**
     * Get all detected issues
     *
     * @since 1.0.0
     *
     * @return array Array of issue data
     */
    public function get_issues() {
        return $this->issues;
    }
    
    /**
     * Clear all issues
     *
     * Resets issues array. Called before each scan to ensure
     * checkers start with clean state.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function clear_issues() {
        $this->issues = [];
    }
    
    /**
     * Generate CSS selector for a DOM element
     *
     * Creates a unique CSS selector path to identify the element.
     * Uses ID if available, otherwise builds path with classes and nth-child.
     *
     * @since 1.0.0
     *
     * @param DOMElement $element Element to generate selector for
     * @return string CSS selector string
     */
    protected function get_selector($element) {
        // Use ID if available
        if ($element->hasAttribute('id')) {
            return '#' . $element->getAttribute('id');
        }
        
        // Build selector path
        $path = [];
        $current = $element;
        
        while ($current && $current->nodeType === XML_ELEMENT_NODE) {
            $selector = $current->nodeName;
            
            // Add class if available
            if ($current->hasAttribute('class')) {
                $classes = explode(' ', $current->getAttribute('class'));
                $selector .= '.' . implode('.', array_filter($classes));
            }
            
            // Add nth-child if needed for uniqueness
            if ($current->parentNode) {
                $siblings = [];
                foreach ($current->parentNode->childNodes as $sibling) {
                    if ($sibling->nodeType === XML_ELEMENT_NODE && 
                        $sibling->nodeName === $current->nodeName) {
                        $siblings[] = $sibling;
                    }
                }
                
                if (count($siblings) > 1) {
                    $index = array_search($current, $siblings, true) + 1;
                    $selector .= ':nth-child(' . $index . ')';
                }
            }
            
            array_unshift($path, $selector);
            $current = $current->parentNode;
            
            // Stop at body or if we have enough specificity
            if ($current && $current->nodeName === 'body') {
                break;
            }
        }
        
        return implode(' > ', array_slice($path, -4)); // Last 4 levels for brevity
    }
    
    /**
     * Get outer HTML of a DOM element
     *
     * Returns the complete HTML string including the element's tags.
     *
     * @since 1.0.0
     *
     * @param DOMElement $element Element to extract HTML from
     * @return string Outer HTML
     */
    protected function get_outer_html($element) {
        if (!$element || !$element->ownerDocument) {
            return '';
        }
        
        return $element->ownerDocument->saveHTML($element);
    }
    
    /**
     * Get inner HTML of a DOM element
     *
     * Returns HTML content inside the element (excluding element's tags).
     *
     * @since 1.0.0
     *
     * @param DOMElement $element Element to extract inner HTML from
     * @return string Inner HTML
     */
    protected function get_inner_html($element) {
        if (!$element || !$element->ownerDocument) {
            return '';
        }
        
        $innerHTML = '';
        foreach ($element->childNodes as $child) {
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }
        
        return $innerHTML;
    }
    
    /**
     * Get element's text content (without HTML tags)
     *
     * @since 1.0.0
     *
     * @param DOMElement $element Element to extract text from
     * @return string Text content
     */
    protected function get_text_content($element) {
        if (!$element) {
            return '';
        }
        
        return trim($element->textContent);
    }
    
    /**
     * Generate XPath selector for a DOM element
     *
     * Creates an XPath expression to uniquely identify the element.
     *
     * @since 1.0.0
     *
     * @param DOMElement $element Element to generate XPath for
     * @return string XPath expression
     */
    protected function get_xpath_selector($element) {
        if (!$element) {
            return '';
        }
        
        // Use ID if available
        if ($element->hasAttribute('id')) {
            return '//*[@id="' . $element->getAttribute('id') . '"]';
        }
        
        // Build XPath path
        $path = [];
        $current = $element;
        
        while ($current && $current->nodeType === XML_ELEMENT_NODE) {
            $index = 1;
            $sibling = $current->previousSibling;
            
            while ($sibling) {
                if ($sibling->nodeType === XML_ELEMENT_NODE && 
                    $sibling->nodeName === $current->nodeName) {
                    $index++;
                }
                $sibling = $sibling->previousSibling;
            }
            
            $path[] = $current->nodeName . '[' . $index . ']';
            $current = $current->parentNode;
        }
        
        return '/' . implode('/', array_reverse($path));
    }
    
    /**
     * Get line number of element in source HTML
     *
     * Attempts to determine the line number where the element appears.
     *
     * @since 1.0.0
     *
     * @param DOMElement $element Element to find line number for
     * @return int|null Line number or null if unavailable
     */
    protected function get_line_number($element) {
        if (!$element) {
            return null;
        }
        
        // DOMDocument provides line numbers in some cases
        if (method_exists($element, 'getLineNo')) {
            return $element->getLineNo();
        }
        
        return null;
    }
    
    /**
     * Check if element is hidden (not visible to users or assistive tech)
     *
     * Checks for:
     * - display: none
     * - visibility: hidden
     * - hidden attribute
     * - aria-hidden="true"
     *
     * @since 1.0.0
     *
     * @param DOMElement $element Element to check
     * @return bool True if element is hidden
     */
    protected function is_hidden($element) {
        if (!$element) {
            return false;
        }
        
        // Check hidden attribute
        if ($element->hasAttribute('hidden')) {
            return true;
        }
        
        // Check aria-hidden
        if ($element->hasAttribute('aria-hidden') && 
            $element->getAttribute('aria-hidden') === 'true') {
            return true;
        }
        
        // Check style attribute for display/visibility
        if ($element->hasAttribute('style')) {
            $style = $element->getAttribute('style');
            if (preg_match('/display\s*:\s*none/i', $style) ||
                preg_match('/visibility\s*:\s*hidden/i', $style)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if element is decorative
     *
     * Elements are considered decorative if they have:
     * - role="presentation"
     * - role="none"
     * - Empty alt attribute (for images)
     *
     * @since 1.0.0
     *
     * @param DOMElement $element Element to check
     * @return bool True if element is decorative
     */
    protected function is_decorative($element) {
        if (!$element) {
            return false;
        }
        
        // Check ARIA role
        if ($element->hasAttribute('role')) {
            $role = $element->getAttribute('role');
            if (in_array($role, ['presentation', 'none'])) {
                return true;
            }
        }
        
        // Check for empty alt on images (indicates decorative)
        if ($element->nodeName === 'img' && 
            $element->hasAttribute('alt') && 
            $element->getAttribute('alt') === '') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get computed accessible name for an element
     *
     * Determines the accessible name using the Accessible Name Computation:
     * 1. aria-labelledby
     * 2. aria-label
     * 3. Native label association
     * 4. placeholder (for inputs)
     * 5. title attribute
     * 6. Text content
     *
     * @since 1.0.0
     *
     * @param DOMElement $element Element to compute name for
     * @return string Accessible name
     */
    protected function get_accessible_name($element) {
        if (!$element) {
            return '';
        }
        
        // 1. aria-labelledby
        if ($element->hasAttribute('aria-labelledby')) {
            $ids = explode(' ', $element->getAttribute('aria-labelledby'));
            $names = [];
            foreach ($ids as $id) {
                $referenced = $this->xpath->query('//*[@id="' . $id . '"]');
                if ($referenced->length > 0) {
                    $names[] = $this->get_text_content($referenced->item(0));
                }
            }
            if (!empty($names)) {
                return implode(' ', $names);
            }
        }
        
        // 2. aria-label
        if ($element->hasAttribute('aria-label')) {
            return trim($element->getAttribute('aria-label'));
        }
        
        // 3. Native label (for form controls)
        if (in_array($element->nodeName, ['input', 'select', 'textarea'])) {
            if ($element->hasAttribute('id')) {
                $labels = $this->xpath->query('//label[@for="' . $element->getAttribute('id') . '"]');
                if ($labels->length > 0) {
                    return $this->get_text_content($labels->item(0));
                }
            }
        }
        
        // 4. Placeholder (for inputs)
        if ($element->hasAttribute('placeholder')) {
            return trim($element->getAttribute('placeholder'));
        }
        
        // 5. Title attribute
        if ($element->hasAttribute('title')) {
            return trim($element->getAttribute('title'));
        }
        
        // 6. Text content (for buttons, links, etc.)
        return $this->get_text_content($element);
    }
    
    /**
     * Sanitize text for display
     *
     * Removes excessive whitespace and limits length.
     *
     * @since 1.0.0
     *
     * @param string $text    Text to sanitize
     * @param int    $max_len Maximum length (default 200)
     * @return string Sanitized text
     */
    protected function sanitize_text($text, $max_len = 200) {
        // Remove excessive whitespace
        $text = preg_replace('/\s+/', ' ', trim($text));
        
        // Limit length
        if (strlen($text) > $max_len) {
            $text = substr($text, 0, $max_len) . '...';
        }
        
        return $text;
    }
    
    /**
     * Count elements matching XPath query
     *
     * @since 1.0.0
     *
     * @param string $query XPath query
     * @return int Number of matching elements
     */
    protected function count_elements($query) {
        if (!$this->xpath) {
            return 0;
        }
        
        $elements = $this->xpath->query($query);
        return $elements ? $elements->length : 0;
    }
}
