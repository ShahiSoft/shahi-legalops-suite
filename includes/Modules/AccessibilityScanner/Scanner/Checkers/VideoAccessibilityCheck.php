<?php
namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\Checkers;

use ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner\AbstractCheck;

if (!defined('ABSPATH')) {
    exit;
}

class VideoAccessibilityCheck extends AbstractCheck {
    
    public function get_id() {
        return 'video-accessibility';
    }
    
    public function get_description() {
        return 'Videos must have captions, controls, and no autoplay.';
    }
    
    public function get_severity() {
        return 'serious';
    }

    public function get_wcag_criteria() {
        return '1.2.2';
    }
    
    public function check($content) {
        $issues = [];
        $dom = $this->get_dom($content);
        $xpath = new \DOMXPath($dom);
        
        $videos = $xpath->query('//video');
        
        foreach ($videos as $video) {
            // Check for controls
            if (!$video->hasAttribute('controls')) {
                $issues[] = [
                    'element' => 'video',
                    'context' => $this->get_element_html($video),
                    'message' => '<video> element is missing the "controls" attribute. Users must be able to control media playback.'
                ];
            }
            
            // Check for autoplay
            if ($video->hasAttribute('autoplay')) {
                $issues[] = [
                    'element' => 'video',
                    'context' => $this->get_element_html($video),
                    'message' => '<video> element has "autoplay" enabled. Automatically playing audio/video can be disruptive.'
                ];
            }
            
            // Check for captions (track kind="captions" or "subtitles")
            $tracks = $video->getElementsByTagName('track');
            $hasCaptions = false;
            
            foreach ($tracks as $track) {
                $kind = $track->getAttribute('kind');
                if ($kind === 'captions' || $kind === 'subtitles') {
                    $hasCaptions = true;
                    break;
                }
            }
            
            if (!$hasCaptions) {
                $issues[] = [
                    'element' => 'video',
                    'context' => $this->get_element_html($video),
                    'message' => '<video> element is missing a caption track (<track kind="captions">).'
                ];
            }
        }
        
        return $issues;
    }

    private function get_element_html($node) {
        return $node->ownerDocument->saveHTML($node);
    }
}
