<?php
/**
 * Test script for get_generation_context
 */

// Load WordPress
require_once dirname(__DIR__, 4) . '/wp-load.php';

echo "=== Testing get_generation_context ===\n\n";

// Check if generator exists
if (class_exists('ShahiLegalopsSuite\Services\Document_Generator')) {
    echo "✓ Generator class exists\n";
    
    $gen = new ShahiLegalopsSuite\Services\Document_Generator();
    
    try {
        echo "\nCalling get_generation_context('privacy-policy')...\n\n";
        $context = $gen->get_generation_context('privacy-policy');
        
        echo "Result:\n";
        echo "- is_valid: " . ($context['is_valid'] ? 'true' : 'false') . "\n";
        echo "- can_generate: " . ($context['can_generate'] ? 'true' : 'false') . "\n";
        echo "- document_type: " . ($context['document_type'] ?? 'N/A') . "\n";
        echo "- document_title: " . ($context['document_title'] ?? 'N/A') . "\n";
        echo "- missing_fields: " . print_r($context['missing_fields'] ?? [], true) . "\n";
        
        if (isset($context['error'])) {
            echo "- error: " . $context['error'] . "\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Exception: " . $e->getMessage() . "\n";
        echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    } catch (Error $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
        echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
} else {
    echo "✗ Generator class NOT found\n";
}

echo "\n=== Done ===\n";
