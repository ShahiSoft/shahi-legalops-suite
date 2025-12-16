<?php
/**
 * Module Dashboard - Test & Fix Script
 * 
 * This file helps verify the Module Dashboard is working correctly
 * and provides troubleshooting information.
 */

// Load WordPress
require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Access denied. Admin privileges required.');
}

echo "<h1>Module Dashboard - Status Check</h1>";
echo "<hr>";

// Test 1: Check if class exists
echo "<h2>1. Class Availability</h2>";
if (class_exists('ShahiTemplate\Admin\ModuleDashboard')) {
    echo "✅ <strong>ModuleDashboard class exists</strong><br>";
} else {
    echo "❌ <strong>ModuleDashboard class NOT found</strong><br>";
    echo "Check: includes/Admin/ModuleDashboard.php<br>";
}

// Test 2: Check if ModuleManager exists
echo "<h2>2. Module Manager</h2>";
if (class_exists('ShahiTemplate\Modules\ModuleManager')) {
    echo "✅ <strong>ModuleManager class exists</strong><br>";
    
    $manager = ShahiTemplate\Modules\ModuleManager::get_instance();
    $modules = $manager->get_modules();
    echo "📊 <strong>" . count($modules) . " modules registered</strong><br>";
    
    if (!empty($modules)) {
        echo "<ul>";
        foreach ($modules as $module) {
            $key = method_exists($module, 'get_key') ? $module->get_key() : 'unknown';
            $name = method_exists($module, 'get_name') ? $module->get_name() : 'Unknown Module';
            echo "<li>{$name} ({$key})</li>";
        }
        echo "</ul>";
    }
} else {
    echo "❌ <strong>ModuleManager class NOT found</strong><br>";
}

// Test 3: Try to instantiate ModuleDashboard
echo "<h2>3. ModuleDashboard Instantiation</h2>";
try {
    $dashboard = new ShahiTemplate\Admin\ModuleDashboard();
    echo "✅ <strong>ModuleDashboard instantiated successfully</strong><br>";
} catch (Exception $e) {
    echo "❌ <strong>Error creating ModuleDashboard:</strong><br>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}

// Test 4: Check menu registration
echo "<h2>4. Menu Registration</h2>";
global $submenu;
if (isset($submenu['shahi-template'])) {
    echo "✅ <strong>ShahiTemplate menu exists</strong><br>";
    echo "<ul>";
    foreach ($submenu['shahi-template'] as $item) {
        echo "<li>" . htmlspecialchars($item[0]) . " (slug: {$item[2]})</li>";
    }
    echo "</ul>";
    
    // Check for Module Dashboard specifically
    $found = false;
    foreach ($submenu['shahi-template'] as $item) {
        if (strpos($item[2], 'module-dashboard') !== false) {
            echo "✅ <strong>Module Dashboard menu item found!</strong><br>";
            $found = true;
            break;
        }
    }
    if (!$found) {
        echo "⚠️ <strong>Module Dashboard menu item not found</strong><br>";
    }
} else {
    echo "⚠️ <strong>ShahiTemplate menu not registered yet</strong><br>";
    echo "This is normal if checked before admin_menu hook runs.<br>";
}

// Test 5: Check assets
echo "<h2>5. Asset Files</h2>";
$assets = [
    'CSS' => SHAHI_TEMPLATE_PLUGIN_DIR . 'assets/css/admin-module-dashboard.css',
    'CSS (min)' => SHAHI_TEMPLATE_PLUGIN_DIR . 'assets/css/admin-module-dashboard.min.css',
    'JS' => SHAHI_TEMPLATE_PLUGIN_DIR . 'assets/js/admin-module-dashboard.js',
    'JS (min)' => SHAHI_TEMPLATE_PLUGIN_DIR . 'assets/js/admin-module-dashboard.min.js',
    'Template' => SHAHI_TEMPLATE_PLUGIN_DIR . 'templates/admin/module-dashboard.php',
];

foreach ($assets as $name => $path) {
    if (file_exists($path)) {
        $size = round(filesize($path) / 1024, 1);
        echo "✅ <strong>{$name}:</strong> {$size} KB<br>";
    } else {
        echo "❌ <strong>{$name} NOT FOUND</strong><br>";
    }
}

// Test 6: Error log check
echo "<h2>6. Recent Errors</h2>";
$debug_log = WP_CONTENT_DIR . '/debug.log';
if (file_exists($debug_log)) {
    $log_lines = file($debug_log);
    $recent_errors = array_slice($log_lines, -10);
    
    $has_critical = false;
    foreach ($recent_errors as $line) {
        if (stripos($line, 'fatal') !== false || stripos($line, 'moduledashboard') !== false) {
            $has_critical = true;
            echo "<div style='background: #fee; padding: 10px; margin: 5px 0; border-left: 4px solid #c00;'>";
            echo "<code>" . htmlspecialchars($line) . "</code>";
            echo "</div>";
        }
    }
    
    if (!$has_critical) {
        echo "✅ <strong>No critical errors in recent log</strong><br>";
    }
} else {
    echo "ℹ️ <strong>Debug log not found</strong> (WP_DEBUG_LOG may be disabled)<br>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p><strong>Status:</strong> ";
if (class_exists('ShahiTemplate\Admin\ModuleDashboard')) {
    echo "<span style='color: green; font-weight: bold;'>✅ READY</span></p>";
    echo "<p>The Module Dashboard should be accessible at:</p>";
    echo "<p><a href='" . admin_url('admin.php?page=shahi-template-module-dashboard') . "' style='display: inline-block; padding: 10px 20px; background: linear-gradient(135deg, #00d4ff 0%, #7c3aed 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: bold;'>Open Module Dashboard →</a></p>";
} else {
    echo "<span style='color: red; font-weight: bold;'>❌ NOT READY</span></p>";
    echo "<p>Please check the errors above and ensure all files are in place.</p>";
}
