<?php
/**
 * Enable Legal Docs Module
 *
 * This script enables the Legal Docs module and verifies REST API registration.
 */

// Load WordPress
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/wp-load.php';

echo "Legal Docs Module Setup\n";
echo "========================\n\n";

// Get module manager
$plugin = ShahiLegalopsSuite\Plugin::get_instance();
$module_manager = $plugin->get_module_manager();

// Get Legal Docs module
$legal_docs = $module_manager->get_module('legal-docs');

if (!$legal_docs) {
    echo "ERROR: Legal Docs module not found!\n";
    exit(1);
}

// Check if enabled
$is_enabled = $legal_docs->is_enabled();
echo "Current status: " . ($is_enabled ? "ENABLED" : "DISABLED") . "\n\n";

// Enable if not enabled
if (!$is_enabled) {
    echo "Enabling Legal Docs module...\n";
    $module_manager->toggle_module('legal-docs', true);
    
    // Verify
    $is_enabled = $legal_docs->is_enabled();
    echo "New status: " . ($is_enabled ? "ENABLED" : "DISABLED") . "\n\n";
    
    if ($is_enabled) {
        echo "✓ Successfully enabled Legal Docs module\n";
    } else {
        echo "✗ Failed to enable Legal Docs module\n";
        exit(1);
    }
} else {
    echo "✓ Legal Docs module is already enabled\n";
}

// Flush rewrite rules to ensure REST routes are registered
flush_rewrite_rules();
echo "✓ Flushed rewrite rules\n\n";

// Check REST routes
echo "Checking REST API routes...\n";
$rest_server = rest_get_server();
$routes = $rest_server->get_routes();

$legaldoc_routes = array_filter(array_keys($routes), function($route) {
    return strpos($route, '/slos/v1/legaldocs') === 0;
});

if (empty($legaldoc_routes)) {
    echo "✗ No Legal Docs REST routes found!\n";
    echo "\nDebugging information:\n";
    echo "- Module enabled: " . var_export($is_enabled, true) . "\n";
    echo "- Module key: " . $legal_docs->get_key() . "\n";
    echo "- Available slos/v1 routes:\n";
    
    $slos_routes = array_filter(array_keys($routes), function($route) {
        return strpos($route, '/slos/v1/') === 0;
    });
    
    foreach ($slos_routes as $route) {
        echo "  - $route\n";
    }
} else {
    echo "✓ Found " . count($legaldoc_routes) . " Legal Docs REST routes:\n";
    foreach ($legaldoc_routes as $route) {
        echo "  - $route\n";
    }
}

echo "\nSetup complete!\n";
