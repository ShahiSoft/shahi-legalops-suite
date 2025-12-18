<?php
namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if (!defined('ABSPATH')) {
    exit;
}

class LinkDestinationCheck extends AbstractCheck {
    
    public function get_id() {
        return 'link-destination';
    }
    
    public function get_description() {
        return 'Links with the same text should go to the same destination.';
    }
    
    public function get_severity() {
        return 'warning';
    }

    public function get_wcag_criteria() {
        return '2.4.4';
    }
    
    public function check($content) {
        $issues = [];
        $dom = $this->get_dom($content);
        $links = $dom->getElementsByTagName('a');
        
        $linkMap = [];
        
        foreach ($links as $link) {
            $text = trim($link->textContent);
            $href = $link->getAttribute('href');
            
            if (empty($text) || empty($href)) continue;
            
            if (isset($linkMap[$text])) {
                if ($linkMap[$text] !== $href) {
                    $issues[] = [
                        'element' => 'a',
                        'context' => $text,
                        'message' => "Link text '$text' is used for multiple different destinations. This can be confusing."
                    ];
                }
            } else {
                $linkMap[$text] = $href;
            }
        }
        
        return $issues;
    }
}
