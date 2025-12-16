<?php
/**
 * ARIA Accessibility Checker
 *
 * Validates ARIA (Accessible Rich Internet Applications) attributes for
 * proper usage and compliance with WCAG 2.2 Level A guidelines.
 *
 * Checks Performed:
 * - Invalid ARIA roles
 * - Missing required ARIA attributes for roles
 * - Redundant ARIA on native HTML elements
 *
 * @package ShahiLegalopsSuite
 * @subpackage Modules\AccessibilityScanner\Scanner\Checkers
 * @since 1.0.0
 * @version 1.0.0
 * @author Shahi Legal Ops Team
 * @license GPL-2.0-or-later
 *
 * @see https://www.w3.org/WAI/WCAG22/Understanding/name-role-value WCAG 4.1.2
 * @see https://www.w3.org/TR/wai-aria-1.2/
 */

namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\AbstractChecker;

/**
 * ARIA Accessibility Checker Class
 *
 * Validates ARIA attributes and roles for proper implementation.
 * Implements WCAG 2.2 Success Criterion:
 * - 4.1.2 Name, Role, Value - Level A
 *
 * @since 1.0.0
 */
class ARIAChecker extends AbstractChecker {

    /**
     * Valid ARIA roles (ARIA 1.2)
     *
     * @var array
     */
    private $valid_roles = [
        // Document structure roles
        'application', 'article', 'cell', 'columnheader', 'definition',
        'directory', 'document', 'feed', 'figure', 'group', 'heading',
        'img', 'list', 'listitem', 'math', 'none', 'note', 'presentation',
        'row', 'rowgroup', 'rowheader', 'separator', 'table', 'term',
        'toolbar', 'tooltip',
        
        // Widget roles
        'button', 'checkbox', 'gridcell', 'link', 'menuitem', 'menuitemcheckbox',
        'menuitemradio', 'option', 'progressbar', 'radio', 'scrollbar',
        'searchbox', 'slider', 'spinbutton', 'switch', 'tab', 'tabpanel',
        'textbox', 'treeitem',
        
        // Composite widget roles
        'combobox', 'grid', 'listbox', 'menu', 'menubar', 'radiogroup',
        'tablist', 'tree', 'treegrid',
        
        // Landmark roles
        'banner', 'complementary', 'contentinfo', 'form', 'main',
        'navigation', 'region', 'search',
        
        // Live region roles
        'alert', 'log', 'marquee', 'status', 'timer',
        
        // Window roles
        'alertdialog', 'dialog',
    ];

    /**
     * Required attributes for specific ARIA roles
     *
     * @var array
     */
    private $required_attributes = [
        'checkbox' => ['aria-checked'],
        'combobox' => ['aria-expanded', 'aria-controls'],
        'gridcell' => [],
        'listbox' => [],
        'option' => ['aria-selected'],
        'radio' => ['aria-checked'],
        'scrollbar' => ['aria-controls', 'aria-valuenow', 'aria-valuemin', 'aria-valuemax'],
        'separator' => [], // Only when focusable: aria-valuenow, aria-valuemin, aria-valuemax
        'slider' => ['aria-valuenow', 'aria-valuemin', 'aria-valuemax'],
        'spinbutton' => ['aria-valuenow', 'aria-valuemin', 'aria-valuemax'],
        'switch' => ['aria-checked'],
        'tab' => ['aria-selected'],
        'tabpanel' => [],
        'textbox' => [],
        'treeitem' => ['aria-selected'],
    ];

    /**
     * Redundant ARIA patterns (ARIA on elements that don't need it)
     *
     * @var array
     */
    private $redundant_patterns = [
        ['element' => 'button', 'role' => 'button'],
        ['element' => 'a', 'role' => 'link'],
        ['element' => 'img', 'role' => 'img'],
        ['element' => 'nav', 'role' => 'navigation'],
        ['element' => 'main', 'role' => 'main'],
        ['element' => 'header', 'role' => 'banner'],
        ['element' => 'footer', 'role' => 'contentinfo'],
        ['element' => 'aside', 'role' => 'complementary'],
        ['element' => 'form', 'role' => 'form'],
        ['element' => 'article', 'role' => 'article'],
        ['element' => 'h1', 'role' => 'heading'],
        ['element' => 'h2', 'role' => 'heading'],
        ['element' => 'h3', 'role' => 'heading'],
        ['element' => 'h4', 'role' => 'heading'],
        ['element' => 'h5', 'role' => 'heading'],
        ['element' => 'h6', 'role' => 'heading'],
    ];

    /**
     * Get check type identifier
     *
     * @return string Check type
     */
    public function get_check_type() {
        return 'aria';
    }

    /**
     * Get check name
     *
     * @return string Check name
     */
    public function get_check_name() {
        return 'ARIA Attributes';
    }

    /**
     * Run all ARIA accessibility checks
     *
     * Executes comprehensive validation of ARIA attributes including:
     * - Invalid ARIA roles
     * - Missing required ARIA attributes
     * - Redundant ARIA on native HTML elements
     *
     * @param \DOMDocument $dom   DOM document to check
     * @param \DOMXPath    $xpath XPath instance for querying
     * @return void
     */
    public function check($dom, $xpath) {
        $this->check_invalid_roles($xpath);
        $this->check_required_attributes($xpath);
        $this->check_redundant_aria($xpath);
    }

    /**
     * Check for invalid ARIA roles
     *
     * Identifies elements with role attributes that contain invalid
     * or misspelled ARIA role values. Invalid roles are ignored by
     * assistive technologies.
     *
     * @param \DOMXPath $xpath XPath instance for querying
     * @return void
     */
    private function check_invalid_roles($xpath) {
        // Find all elements with role attribute
        $elements = $xpath->query('//*[@role]');

        foreach ($elements as $element) {
            // Skip if element is hidden
            if ($this->is_hidden($element)) {
                continue;
            }

            $role = trim($element->getAttribute('role'));
            
            // Skip empty roles
            if ($role === '') {
                continue;
            }

            // ARIA roles can have multiple values (space-separated fallback)
            $roles = preg_split('/\s+/', $role);
            $invalid_roles = [];
            $has_valid_role = false;

            foreach ($roles as $single_role) {
                if (!in_array($single_role, $this->valid_roles)) {
                    $invalid_roles[] = $single_role;
                } else {
                    $has_valid_role = true;
                }
            }

            // Report if any invalid roles found
            if (!empty($invalid_roles)) {
                $tag = $element->nodeName;
                $selector = $this->get_selector($element);
                
                // Determine severity - critical if no valid fallback
                $severity = $has_valid_role ? 'moderate' : 'serious';

                // Try to suggest corrections for common typos
                $suggestions = $this->suggest_role_corrections($invalid_roles);

                $this->add_issue([
                    'type' => 'invalid_aria_role',
                    'severity' => $severity,
                    'element' => $tag,
                    'wcag_criterion' => '4.1.2',
                    'wcag_level' => 'A',
                    'message' => 'Invalid ARIA role',
                    'description' => sprintf(
                        'The %s element has invalid ARIA role(s): "%s". %s',
                        $tag,
                        implode('", "', $invalid_roles),
                        $has_valid_role 
                            ? 'The element has a valid fallback role, but invalid roles should be removed or corrected.'
                            : 'This element has no valid ARIA role, so its semantic meaning may not be conveyed to assistive technologies.'
                    ),
                    'selector' => $selector,
                    'html' => $this->get_outer_html($element),
                    'recommendation' => !empty($suggestions)
                        ? sprintf('Did you mean: %s? Otherwise, remove invalid role(s) or use a valid ARIA 1.2 role.', implode(' or ', $suggestions))
                        : 'Remove invalid role(s) or replace with a valid ARIA 1.2 role from the specification.',
                    'context' => [
                        'invalid_roles' => $invalid_roles,
                        'all_roles' => $roles,
                        'has_valid_fallback' => $has_valid_role,
                        'suggestions' => $suggestions,
                    ]
                ]);
            }
        }
    }

    /**
     * Check for missing required ARIA attributes
     *
     * Identifies elements with ARIA roles that are missing required
     * attributes. Required attributes are essential for the role to
     * function properly with assistive technologies.
     *
     * @param \DOMXPath $xpath XPath instance for querying
     * @return void
     */
    private function check_required_attributes($xpath) {
        // Find all elements with role attribute
        $elements = $xpath->query('//*[@role]');

        foreach ($elements as $element) {
            // Skip if element is hidden
            if ($this->is_hidden($element)) {
                continue;
            }

            $role = trim($element->getAttribute('role'));
            
            // Skip if no role or multiple roles (check first role only)
            if ($role === '' || strpos($role, ' ') !== false) {
                continue;
            }

            // Check if this role has required attributes
            if (!isset($this->required_attributes[$role])) {
                continue;
            }

            $required = $this->required_attributes[$role];
            
            // Skip if no required attributes for this role
            if (empty($required)) {
                continue;
            }

            // Check for missing required attributes
            $missing = [];
            foreach ($required as $attr) {
                if (!$element->hasAttribute($attr)) {
                    $missing[] = $attr;
                }
            }

            // Report if any required attributes are missing
            if (!empty($missing)) {
                $tag = $element->nodeName;
                $selector = $this->get_selector($element);

                $this->add_issue([
                    'type' => 'missing_required_aria_attribute',
                    'severity' => 'serious',
                    'element' => $tag,
                    'wcag_criterion' => '4.1.2',
                    'wcag_level' => 'A',
                    'message' => 'Missing required ARIA attributes',
                    'description' => sprintf(
                        'The %s element has role="%s" but is missing required attribute(s): %s. Elements with this role must have these attributes for assistive technologies to function correctly.',
                        $tag,
                        $role,
                        implode(', ', $missing)
                    ),
                    'selector' => $selector,
                    'html' => $this->get_outer_html($element),
                    'recommendation' => sprintf(
                        'Add the following required attribute(s): %s',
                        implode(', ', $missing)
                    ),
                    'context' => [
                        'role' => $role,
                        'missing_attributes' => $missing,
                        'all_required' => $required,
                    ]
                ]);
            }
        }
    }

    /**
     * Check for redundant ARIA
     *
     * Identifies cases where ARIA roles or attributes are used on
     * native HTML elements that already have the same semantic meaning.
     * Redundant ARIA adds unnecessary complexity and maintenance burden.
     *
     * @param \DOMXPath $xpath XPath instance for querying
     * @return void
     */
    private function check_redundant_aria($xpath) {
        foreach ($this->redundant_patterns as $pattern) {
            $element_name = $pattern['element'];
            $redundant_role = $pattern['role'];

            // Find all elements matching this pattern
            $elements = $xpath->query("//{$element_name}[@role='{$redundant_role}']");

            foreach ($elements as $element) {
                // Skip if element is hidden
                if ($this->is_hidden($element)) {
                    continue;
                }

                $selector = $this->get_selector($element);

                // Special case: header/footer only have implicit banner/contentinfo
                // roles when they're not descendants of article/aside/main/nav/section
                if (in_array($element_name, ['header', 'footer'])) {
                    $is_scoped = $xpath->query(
                        'ancestor::article | ancestor::aside | ancestor::main | ancestor::nav | ancestor::section',
                        $element
                    )->length > 0;

                    // If header/footer is scoped, the role is NOT redundant
                    if ($is_scoped) {
                        continue;
                    }
                }

                $this->add_issue([
                    'type' => 'redundant_aria',
                    'severity' => 'minor',
                    'element' => $element_name,
                    'wcag_criterion' => '4.1.2',
                    'wcag_level' => 'A',
                    'message' => 'Redundant ARIA role on native element',
                    'description' => sprintf(
                        'The <%s> element has role="%s" which is redundant. Native HTML5 elements already have implicit ARIA roles, so explicitly adding the same role is unnecessary.',
                        $element_name,
                        $redundant_role
                    ),
                    'selector' => $selector,
                    'html' => $this->get_outer_html($element),
                    'recommendation' => sprintf(
                        'Remove role="%s" from this <%s> element. The native element already conveys this semantic meaning.',
                        $redundant_role,
                        $element_name
                    ),
                    'context' => [
                        'element' => $element_name,
                        'redundant_role' => $redundant_role,
                    ]
                ]);
            }
        }

        // Also check for common redundant aria-label patterns
        $this->check_redundant_aria_labels($xpath);
    }

    /**
     * Check for redundant aria-label usage
     *
     * Identifies aria-label attributes that duplicate visible text,
     * which is redundant and can cause maintenance issues.
     *
     * @param \DOMXPath $xpath XPath instance for querying
     * @return void
     */
    private function check_redundant_aria_labels($xpath) {
        // Find all elements with aria-label
        $elements = $xpath->query('//*[@aria-label]');

        foreach ($elements as $element) {
            // Skip if element is hidden
            if ($this->is_hidden($element)) {
                continue;
            }

            $aria_label = trim($element->getAttribute('aria-label'));
            $visible_text = trim($this->get_text_content($element));

            // Skip if no visible text
            if ($visible_text === '' || $aria_label === '') {
                continue;
            }

            // Check if aria-label exactly matches visible text
            if (strcasecmp($aria_label, $visible_text) === 0) {
                $tag = $element->nodeName;
                $selector = $this->get_selector($element);

                $this->add_issue([
                    'type' => 'redundant_aria_label',
                    'severity' => 'minor',
                    'element' => $tag,
                    'wcag_criterion' => '4.1.2',
                    'wcag_level' => 'A',
                    'message' => 'Redundant aria-label duplicates visible text',
                    'description' => sprintf(
                        'The %s element has aria-label="%s" which exactly matches the visible text. This is redundant and can cause maintenance issues if the visible text is updated but aria-label is not.',
                        $tag,
                        $aria_label
                    ),
                    'selector' => $selector,
                    'html' => $this->get_outer_html($element),
                    'recommendation' => 'Remove the aria-label attribute. Screen readers will announce the visible text content by default.',
                    'context' => [
                        'aria_label' => $aria_label,
                        'visible_text' => $visible_text,
                    ]
                ]);
            }
        }
    }

    /**
     * Suggest role corrections for common typos
     *
     * Attempts to suggest valid ARIA roles for common misspellings
     * or typos in role attributes.
     *
     * @param array $invalid_roles Invalid role names
     * @return array Suggested corrections
     */
    private function suggest_role_corrections($invalid_roles) {
        $suggestions = [];

        foreach ($invalid_roles as $invalid) {
            $invalid_lower = strtolower($invalid);
            
            // Check for close matches using Levenshtein distance
            $best_match = null;
            $min_distance = PHP_INT_MAX;

            foreach ($this->valid_roles as $valid) {
                $distance = levenshtein($invalid_lower, $valid);
                
                // Only suggest if distance is small (likely typo)
                if ($distance <= 2 && $distance < $min_distance) {
                    $min_distance = $distance;
                    $best_match = $valid;
                }
            }

            if ($best_match !== null) {
                $suggestions[] = $best_match;
            }
        }

        return array_unique($suggestions);
    }
}
