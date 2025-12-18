<?php
/**
 * Base Fixer Class
 *
 * Provides common functionality for all content-aware fixers
 *
 * @package ShahiLegalopsSuite\Modules\AccessibilityScanner\Fixes
 */

namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Fixes\Fixers;

if (!defined('ABSPATH')) {
    exit;
}

abstract class BaseFixer {
    
    /**
     * Get fixer ID (matches checker ID)
     */
    abstract public function get_id();
    
    /**
     * Get fixer description
     */
    abstract public function get_description();
    
    /**
     * Apply fix to content
     */
    abstract public function fix($content);
    
    /**
     * Get DOM from HTML content
     */
    protected function get_dom($content) {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        
        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);
        
        // Wrap content to handle fragments
        $content = '<?xml version="1.0" encoding="UTF-8"?><root>' . $content . '</root>';
        $dom->loadHTML($content);
        
        libxml_clear_errors();
        
        return $dom;
    }
    
    /**
     * Convert DOM to HTML
     */
    protected function dom_to_html($dom) {
        $html = $dom->saveHTML();
        
        // Remove the XML declaration and wrapper
        $html = preg_replace('/<\?xml[^?]*\?>/i', '', $html);
        $html = preg_replace('/<root>(.*)<\/root>/is', '$1', $html);
        $html = preg_replace('/<body>(.*)<\/body>/is', '$1', $html);
        
        return trim($html);
    }
    
    /**
     * Generate alt text using AI/API (placeholder for now)
     */
    protected function generate_alt_text($image_src) {
        // Extract filename as fallback
        $filename = basename($image_src);
        $filename = preg_replace('/\.[^.]+$/', '', $filename); // Remove extension
        $filename = str_replace(['-', '_'], ' ', $filename);
        return ucfirst($filename);
    }
}
