<?php
namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if (!defined('ABSPATH')) {
    exit;
}

class CustomControlCheck extends AbstractCheck {
    
    public function get_id() {
        return 'custom-control';
    }
    
    public function get_description() {
        return 'Custom form controls (div/span with role) must have tabindex and accessible name.';
    }
    
    public function get_severity() {
        return 'serious';
    }

    public function get_wcag_criteria() {
        return '4.1.2';
    }
    
    public function check($content) {
        $issues = [];
        $dom = $this->get_dom($content);
        $xpath = new \DOMXPath($dom);
        
        // Find elements with widget roles
        $roles = ['button', 'checkbox', 'radio', 'slider', 'spinbutton', 'textbox', 'combobox', 'listbox', 'menuitem', 'switch'];
        $query = "//*[@role='" . implode("' or @role='", $roles) . "']";
        $elements = $xpath->query($query);
        
        foreach ($elements as $element) {
            // Skip native elements that might have roles (e.g. <button role="button"> is redundant but valid for this check)
            if (in_array($element->tagName, ['button', 'input', 'select', 'textarea', 'a'])) {
                continue;
            }
            
            // Check tabindex
            if (!$element->hasAttribute('tabindex')) {
                $issues[] = [
                    'element' => $element->tagName,
                    'context' => $this->get_element_html($element),
                    'message' => "Custom control with role '{$element->getAttribute('role')}' is missing tabindex."
                ];
            }
            
            // Check accessible name
            $hasName = $element->hasAttribute('aria-label') || 
                       $element->hasAttribute('aria-labelledby') || 
                       $element->hasAttribute('title') || 
                       !empty(trim($element->textContent));
                       
            if (!$hasName) {
                $issues[] = [
                    'element' => $element->tagName,
                    'context' => $this->get_element_html($element),
                    'message' => "Custom control with role '{$element->getAttribute('role')}' is missing an accessible name."
                ];
            }
        }
        
        return $issues;
    }

    private function get_element_html($node) {
        return $node->ownerDocument->saveHTML($node);
    }
}
