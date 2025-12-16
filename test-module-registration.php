<?php
/**
 * Test Module Registration
 *
 * Quick test to verify AccessibilityScanner module can be loaded.
 */

// Define WordPress constants (minimal stubs for testing)
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// Define plugin constants
if (!defined('SHAHI_LEGALOPS_SUITE_PLUGIN_DIR')) {
    define('SHAHI_LEGALOPS_SUITE_PLUGIN_DIR', __DIR__ . '/');
}

// Load autoloader
require_once __DIR__ . '/includes/Core/Autoloader.php';
$autoloader = new ShahiLegalopsSuite\Core\Autoloader();
$autoloader->register();

// Test 1: Class existence
echo "Test 1: Class autoloading...\n";
if (class_exists('ShahiLegalopsSuite\Modules\AccessibilityScanner\AccessibilityScanner')) {
    echo "✓ PASS: AccessibilityScanner class can be autoloaded\n";
} else {
    echo "✗ FAIL: AccessibilityScanner class not found\n";
    exit(1);
}

// Test 2: Module instantiation
echo "\nTest 2: Module instantiation...\n";
try {
    // Mock functions needed for Module base class
    if (!function_exists('__')) {
        function __($text) { return $text; }
    }
    if (!function_exists('get_option')) {
        function get_option($key, $default = false) { return $default; }
    }
    
    $module = new ShahiLegalopsSuite\Modules\AccessibilityScanner\AccessibilityScanner();
    echo "✓ PASS: Module instantiated successfully\n";
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Required methods
echo "\nTest 3: Required abstract methods...\n";
$tests = [
    'get_key()' => 'accessibility-scanner',
    'get_name()' => 'Accessibility Scanner',
    'get_category()' => 'compliance',
    'get_icon()' => 'dashicons-universal-access-alt',
];

foreach ($tests as $method => $expected) {
    $method_name = str_replace('()', '', $method);
    $actual = $module->$method_name();
    if ($actual === $expected) {
        echo "  ✓ $method returns: $actual\n";
    } else {
        echo "  ✗ $method expected '$expected', got '$actual'\n";
        exit(1);
    }
}

// Test 4: Optional methods
echo "\nTest 4: Optional methods...\n";
echo "  Version: " . $module->get_version() . "\n";
echo "  Priority: " . $module->get_priority() . "\n";
echo "  Settings URL: " . $module->get_settings_url() . "\n";

// Test 5: Statistics method
echo "\nTest 5: Statistics method...\n";
if (method_exists($module, 'get_stats')) {
    echo "  ✓ get_stats() method exists\n";
} else {
    echo "  ✗ get_stats() method missing\n";
    exit(1);
}

echo "\n" . str_repeat('=', 50) . "\n";
echo "ALL TESTS PASSED ✓\n";
echo str_repeat('=', 50) . "\n";
