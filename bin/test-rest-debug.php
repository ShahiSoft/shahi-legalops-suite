<?php
/**
 * Quick REST API Test
 */

// Load WordPress
$wp_load = dirname(dirname(dirname(dirname(__DIR__)))) . '/wp-load.php';
if (!file_exists($wp_load)) {
    echo "Error: wp-load.php not found at: $wp_load\n";
    exit(1);
}
require_once $wp_load;

echo "REST API Debug\n";
echo "==============\n\n";

// Check if plugin is active
if (!class_exists('ShahiLegalopsSuite\Plugin')) {
    echo "ERROR: Plugin class not found!\n";
    exit(1);
}

echo "✓ Plugin class exists\n";

// Check if REST controller exists
if (!class_exists('ShahiLegalopsSuite\API\Legal_Doc_REST_Controller')) {
    echo "ERROR: Legal_Doc_REST_Controller class not found!\n";
    echo "Autoloader may not be loading the file.\n";
    exit(1);
}

echo "✓ Legal_Doc_REST_Controller class exists\n";

// Get all registered REST routes
$rest_server = rest_get_server();
$routes = $rest_server->get_routes();

echo "\nSearching for /slos/v1/legaldocs routes...\n";

$found = false;
foreach ($routes as $route => $handlers) {
    if (strpos($route, '/slos/v1/legaldocs') === 0) {
        echo "✓ Found route: $route\n";
        $found = true;
    }
}

if (!$found) {
    echo "✗ No legaldocs routes found!\n\n";
    echo "All slos/v1 routes:\n";
    foreach ($routes as $route => $handlers) {
        if (strpos($route, '/slos/v1/') === 0) {
            echo "  - $route\n";
        }
    }
    
    echo "\nChecking module status...\n";
    $module_manager = \ShahiLegalopsSuite\Modules\ModuleManager::get_instance();
    if ($module_manager) {
        $legal_docs = $module_manager->get_module('legal-docs');
        if ($legal_docs) {
            echo "Module exists: " . $legal_docs->get_name() . "\n";
            echo "Module enabled: " . ($legal_docs->is_enabled() ? "YES" : "NO") . "\n";
            
            if (!$legal_docs->is_enabled()) {
                echo "\nEnabling module...\n";
                $module_manager->toggle_module('legal-docs', true);
                flush_rewrite_rules();
                echo "Module enabled. Please refresh WordPress cache and try again.\n";
            }
        } else {
            echo "ERROR: Legal Docs module not found in module manager!\n";
        }
    } else {
        echo "ERROR: Module manager not found!\n";
    }
}

echo "\nDone!\n";
