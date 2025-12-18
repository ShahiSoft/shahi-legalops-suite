<?php
namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if (!defined('ABSPATH')) {
    exit;
}

class PositiveTabIndexCheck extends AbstractCheck {
    
    public function get_id() {
        return 'positive-tabindex';
    }
    
    public function get_description() {
        return 'Avoid using positive tabindex values.';
    }
    
    public function get_severity() {
        return 'warning';
    }
    
    public function get_wcag_criteria() {
        return '2.4.3';
    }

    public function check($content) {
        $issues = [];
        $dom = $this->get_dom($content);
        $xpath = new \DOMXPath($dom);
        $elements = $xpath->query('//*[@tabindex]');
        
        foreach ($elements as $element) {
            $tabindex = $element->getAttribute('tabindex');
            if (is_numeric($tabindex) && intval($tabindex) > 0) {
                $issues[] = [
                    'message' => "Element has a positive tabindex ($tabindex). This disrupts the natural tab order.",
                    'element' => $dom->saveHTML($element),
                    'context' => $element->tagName
                ];
            }
        }
        
        return $issues;
    }
}
