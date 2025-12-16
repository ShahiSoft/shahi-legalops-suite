<?php
/**
 * Form Accessibility Checker
 *
 * Validates form elements for accessibility compliance per WCAG 2.2
 * Level A/AA guidelines, ensuring forms are usable by all users.
 *
 * Checks Performed:
 * - Form controls without associated labels
 * - Placeholder text used as sole label
 * - Required fields without indicators
 * - Fieldsets missing legends
 *
 * @package ShahiLegalopsSuite
 * @subpackage Modules\AccessibilityScanner\Scanner\Checkers
 * @since 1.0.0
 * @version 1.0.0
 * @author Shahi Legal Ops Team
 * @license GPL-2.0-or-later
 *
 * @see https://www.w3.org/WAI/WCAG22/Understanding/info-and-relationships WCAG 1.3.1
 * @see https://www.w3.org/WAI/WCAG22/Understanding/labels-or-instructions WCAG 3.3.2
 * @see https://www.w3.org/WAI/WCAG22/Understanding/identify-input-purpose WCAG 1.3.5
 */

namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\AbstractChecker;

/**
 * Form Accessibility Checker Class
 *
 * Validates form elements for proper labeling and accessibility.
 * Implements WCAG 2.2 Success Criteria:
 * - 1.3.1 Info and Relationships - Level A
 * - 3.3.2 Labels or Instructions - Level A
 * - 1.3.5 Identify Input Purpose - Level AA
 *
 * @since 1.0.0
 */
class FormChecker extends AbstractChecker {

    /**
     * Form control types that require labels
     *
     * @var array
     */
    private $labelable_types = [
        'text', 'password', 'email', 'tel', 'url', 'search',
        'number', 'date', 'datetime-local', 'month', 'week', 'time',
        'color', 'file', 'checkbox', 'radio'
    ];

    /**
     * Get check type identifier
     *
     * @return string Check type
     */
    public function get_check_type() {
        return 'form';
    }

    /**
     * Get check name
     *
     * @return string Check name
     */
    public function get_check_name() {
        return 'Form Accessibility';
    }

    /**
     * Run all form accessibility checks
     *
     * Executes comprehensive validation of form elements including:
     * - Missing labels on form controls
     * - Placeholder used as sole label
     * - Required fields without indicators
     * - Fieldsets without legends
     *
     * @param \DOMDocument $dom   DOM document to check
     * @param \DOMXPath    $xpath XPath instance for querying
     * @return void
     */
    public function check($dom, $xpath) {
        $this->check_missing_labels($xpath);
        $this->check_placeholder_as_label($xpath);
        $this->check_required_field_indicators($xpath);
        $this->check_fieldset_legends($xpath);
    }

    /**
     * Check for form controls missing labels
     *
     * Identifies input, select, and textarea elements without associated
     * label elements or ARIA labels. Form controls must have labels for
     * screen reader users to understand their purpose.
     *
     * @param \DOMXPath $xpath XPath instance for querying
     * @return void
     */
    private function check_missing_labels($xpath) {
        // Find all form controls (input, select, textarea)
        $controls = $xpath->query('//input | //select | //textarea');

        foreach ($controls as $control) {
            // Skip if control is hidden
            if ($this->is_hidden($control)) {
                continue;
            }

            // Skip submit/button/hidden inputs
            $type = $control->getAttribute('type');
            if (in_array($type, ['submit', 'button', 'hidden', 'image', 'reset'])) {
                continue;
            }

            // Check if control has accessible name
            $accessible_name = trim($this->get_accessible_name($control));
            
            if ($accessible_name === '') {
                $tag = $control->nodeName;
                $id = $control->getAttribute('id');
                $name = $control->getAttribute('name');
                $selector = $this->get_selector($control);

                // Check if label exists but is empty
                $has_empty_label = false;
                if ($id) {
                    $label = $xpath->query("//label[@for='$id']")->item(0);
                    if ($label && trim($this->get_text_content($label)) === '') {
                        $has_empty_label = true;
                    }
                }

                $this->add_issue([
                    'type' => 'missing_form_label',
                    'severity' => 'critical',
                    'element' => $tag,
                    'wcag_criterion' => '3.3.2',
                    'wcag_level' => 'A',
                    'message' => 'Form control is missing a label',
                    'description' => sprintf(
                        'The %s control "%s" has no associated label. All form controls must have labels so users understand what information to provide. %s',
                        $tag,
                        $id ?: $name ?: '(no id or name)',
                        $has_empty_label ? 'A label element exists but is empty.' : ''
                    ),
                    'selector' => $selector,
                    'html' => $this->get_outer_html($control),
                    'recommendation' => 'Add a <label> element with for="" attribute pointing to this control\'s id, or add aria-label or aria-labelledby.',
                    'context' => [
                        'tag' => $tag,
                        'type' => $type,
                        'id' => $id,
                        'name' => $name,
                        'has_placeholder' => $control->hasAttribute('placeholder'),
                        'placeholder' => $control->getAttribute('placeholder'),
                        'has_empty_label' => $has_empty_label,
                    ]
                ]);
            }
        }
    }

    /**
     * Check for placeholder used as sole label
     *
     * Identifies form controls that use only placeholder text without
     * a proper label. Placeholders disappear when typing and are not
     * adequate substitutes for labels.
     *
     * @param \DOMXPath $xpath XPath instance for querying
     * @return void
     */
    private function check_placeholder_as_label($xpath) {
        // Find all inputs with placeholder attribute
        $controls = $xpath->query('//input[@placeholder] | //textarea[@placeholder]');

        foreach ($controls as $control) {
            // Skip if control is hidden
            if ($this->is_hidden($control)) {
                continue;
            }

            // Skip submit/button inputs
            $type = $control->getAttribute('type');
            if (in_array($type, ['submit', 'button', 'hidden', 'image', 'reset'])) {
                continue;
            }

            // Get accessible name excluding placeholder
            $id = $control->getAttribute('id');
            $aria_label = $control->getAttribute('aria-label');
            $aria_labelledby = $control->getAttribute('aria-labelledby');
            
            // Check for label element
            $has_label = false;
            if ($id) {
                $label = $xpath->query("//label[@for='$id']")->item(0);
                if ($label && trim($this->get_text_content($label)) !== '') {
                    $has_label = true;
                }
            }

            // Check if wrapped in label
            $parent = $control->parentNode;
            if ($parent && $parent->nodeName === 'label' && trim($this->get_text_content($parent)) !== '') {
                $has_label = true;
            }

            // If no label but has placeholder, report issue
            if (!$has_label && !$aria_label && !$aria_labelledby) {
                $placeholder = $control->getAttribute('placeholder');
                $tag = $control->nodeName;
                $selector = $this->get_selector($control);

                $this->add_issue([
                    'type' => 'placeholder_as_label',
                    'severity' => 'serious',
                    'element' => $tag,
                    'wcag_criterion' => '3.3.2',
                    'wcag_level' => 'A',
                    'message' => 'Placeholder used as sole label',
                    'description' => sprintf(
                        'The %s control uses placeholder="%s" as its only label. Placeholders disappear when users start typing and are not announced consistently by screen readers. Use a proper label element.',
                        $tag,
                        $placeholder
                    ),
                    'selector' => $selector,
                    'html' => $this->get_outer_html($control),
                    'recommendation' => 'Add a visible <label> element. The placeholder can remain as a hint, but should not be the only way to identify the field.',
                    'context' => [
                        'tag' => $tag,
                        'type' => $type,
                        'placeholder' => $placeholder,
                        'id' => $id,
                        'name' => $control->getAttribute('name'),
                    ]
                ]);
            }
        }
    }

    /**
     * Check for required field indicators
     *
     * Identifies required form fields that don't clearly indicate they
     * are required. Required fields must be programmatically marked and
     * visually indicated.
     *
     * @param \DOMXPath $xpath XPath instance for querying
     * @return void
     */
    private function check_required_field_indicators($xpath) {
        // Find all required form controls
        $controls = $xpath->query('//input[@required] | //select[@required] | //textarea[@required]');

        foreach ($controls as $control) {
            // Skip if control is hidden
            if ($this->is_hidden($control)) {
                continue;
            }

            $tag = $control->nodeName;
            $id = $control->getAttribute('id');
            $selector = $this->get_selector($control);

            // Check if required status is indicated to users
            $has_visual_indicator = $this->has_required_indicator($control, $xpath, $id);

            if (!$has_visual_indicator) {
                $accessible_name = $this->get_accessible_name($control);

                $this->add_issue([
                    'type' => 'missing_required_indicator',
                    'severity' => 'moderate',
                    'element' => $tag,
                    'wcag_criterion' => '3.3.2',
                    'wcag_level' => 'A',
                    'message' => 'Required field lacks visual indicator',
                    'description' => sprintf(
                        'The %s control "%s" has the required attribute but doesn\'t have a clear visual indicator (like asterisk or "required" text) in its label. Both sighted and screen reader users need to know which fields are required.',
                        $tag,
                        $accessible_name ?: $id ?: '(unlabeled)'
                    ),
                    'selector' => $selector,
                    'html' => $this->get_outer_html($control),
                    'recommendation' => 'Add "(required)" or an asterisk (*) to the field label, and include explanatory text about required field indicators. Ensure aria-required="true" or required attribute is present.',
                    'context' => [
                        'tag' => $tag,
                        'id' => $id,
                        'name' => $control->getAttribute('name'),
                        'has_aria_required' => $control->hasAttribute('aria-required'),
                        'aria_required' => $control->getAttribute('aria-required'),
                    ]
                ]);
            }
        }
    }

    /**
     * Check for fieldsets missing legends
     *
     * Identifies <fieldset> elements without <legend> elements.
     * Fieldsets group related form controls and legends provide
     * the group description, essential for screen readers.
     *
     * @param \DOMXPath $xpath XPath instance for querying
     * @return void
     */
    private function check_fieldset_legends($xpath) {
        // Find all fieldsets
        $fieldsets = $xpath->query('//fieldset');

        foreach ($fieldsets as $fieldset) {
            // Skip if fieldset is hidden
            if ($this->is_hidden($fieldset)) {
                continue;
            }

            // Check if fieldset has legend
            $legends = $xpath->query('./legend', $fieldset);
            
            if ($legends->length === 0) {
                $selector = $this->get_selector($fieldset);
                
                // Count form controls inside fieldset
                $controls = $xpath->query('.//input | .//select | .//textarea', $fieldset);
                
                $this->add_issue([
                    'type' => 'fieldset_missing_legend',
                    'severity' => 'serious',
                    'element' => 'fieldset',
                    'wcag_criterion' => '1.3.1',
                    'wcag_level' => 'A',
                    'message' => 'Fieldset is missing a legend',
                    'description' => sprintf(
                        'The fieldset contains %d form control(s) but has no <legend> element. Legends provide context for grouped form fields and are announced by screen readers when users navigate to controls within the fieldset.',
                        $controls->length
                    ),
                    'selector' => $selector,
                    'html' => $this->get_outer_html($fieldset),
                    'recommendation' => 'Add a <legend> element as the first child of the fieldset with descriptive text explaining the purpose of the grouped fields.',
                    'context' => [
                        'control_count' => $controls->length,
                        'has_aria_label' => $fieldset->hasAttribute('aria-label'),
                        'aria_label' => $fieldset->getAttribute('aria-label'),
                    ]
                ]);
            } else {
                // Check if legend is empty
                $legend = $legends->item(0);
                $legend_text = trim($this->get_text_content($legend));
                
                if ($legend_text === '') {
                    $selector = $this->get_selector($fieldset);
                    
                    $this->add_issue([
                        'type' => 'empty_legend',
                        'severity' => 'serious',
                        'element' => 'legend',
                        'wcag_criterion' => '1.3.1',
                        'wcag_level' => 'A',
                        'message' => 'Fieldset has empty legend',
                        'description' => 'The fieldset has a <legend> element but it contains no text. Legends must have meaningful text to describe the purpose of the grouped form fields.',
                        'selector' => $selector,
                        'html' => $this->get_outer_html($legend),
                        'recommendation' => 'Add descriptive text to the legend element that explains the purpose of the grouped fields.',
                        'context' => [
                            'legend_inner_html' => $this->get_inner_html($legend),
                        ]
                    ]);
                }
            }
        }
    }

    /**
     * Check if required field has visual indicator
     *
     * Determines if a required field has visual indication through
     * label text, asterisks, or other common patterns.
     *
     * @param \DOMElement $control Control element to check
     * @param \DOMXPath   $xpath   XPath instance
     * @param string      $id      Control ID
     * @return bool True if indicator found, false otherwise
     */
    private function has_required_indicator($control, $xpath, $id) {
        // Check aria-label and accessible name
        $accessible_name = strtolower($this->get_accessible_name($control));
        if (strpos($accessible_name, 'required') !== false || 
            strpos($accessible_name, '*') !== false ||
            strpos($accessible_name, '(*)') !== false) {
            return true;
        }

        // Check associated label
        if ($id) {
            $label = $xpath->query("//label[@for='$id']")->item(0);
            if ($label) {
                $label_text = strtolower($this->get_text_content($label));
                if (strpos($label_text, 'required') !== false || 
                    strpos($label_text, '*') !== false) {
                    return true;
                }

                // Check for abbr or span with asterisk in label
                $abbr = $xpath->query('.//abbr[@title] | .//span[contains(@class, "required")]', $label);
                if ($abbr->length > 0) {
                    return true;
                }
            }
        }

        // Check parent label (for wrapped inputs)
        $parent = $control->parentNode;
        if ($parent && $parent->nodeName === 'label') {
            $label_text = strtolower($this->get_text_content($parent));
            if (strpos($label_text, 'required') !== false || 
                strpos($label_text, '*') !== false) {
                return true;
            }
        }

        // Check for aria-describedby pointing to required text
        $describedby = $control->getAttribute('aria-describedby');
        if ($describedby) {
            $description = $xpath->query("//*[@id='$describedby']")->item(0);
            if ($description) {
                $desc_text = strtolower($this->get_text_content($description));
                if (strpos($desc_text, 'required') !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}
