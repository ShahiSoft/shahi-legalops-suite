<?php
/**
 * Debug CSS Loading
 * Temporary file to debug CSS loading issues
 */

// Load WordPress
require_once('../../../wp-load.php');

$plugin_dir = plugin_dir_path(__FILE__);
$plugin_url = plugin_dir_url(__FILE__);
$css_file = $plugin_dir . 'assets/css/admin-modules.min.css';

echo "<h2>CSS File Debug Information</h2>";
echo "<strong>Plugin Directory:</strong> " . $plugin_dir . "<br>";
echo "<strong>Plugin URL:</strong> " . $plugin_url . "<br>";
echo "<strong>CSS File Path:</strong> " . $css_file . "<br>";
echo "<strong>File Exists:</strong> " . (file_exists($css_file) ? 'YES' : 'NO') . "<br>";

if (file_exists($css_file)) {
    echo "<strong>File Size:</strong> " . filesize($css_file) . " bytes<br>";
    echo "<strong>Last Modified:</strong> " . date('Y-m-d H:i:s', filemtime($css_file)) . "<br>";
    echo "<strong>Modification Timestamp:</strong> " . filemtime($css_file) . "<br>";
    echo "<strong>MD5 Hash:</strong> " . md5_file($css_file) . "<br>";
    echo "<strong>CSS URL:</strong> " . $plugin_url . 'assets/css/admin-modules.min.css?ver=' . filemtime($css_file) . "<br>";
    
    echo "<h3>First 50 lines of CSS:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 15px; overflow-x: auto;'>";
    $lines = file($css_file);
    echo htmlspecialchars(implode('', array_slice($lines, 0, 50)));
    echo "</pre>";
    
    // Check for specific styles
    $content = file_get_contents($css_file);
    echo "<h3>CSS Content Checks:</h3>";
    echo "<strong>Contains '#00ff88' (new green color):</strong> " . (strpos($content, '#00ff88') !== false ? 'YES' : 'NO') . "<br>";
    echo "<strong>Contains 'minmax(360px, 1fr)' (new grid):</strong> " . (strpos($content, 'minmax(360px, 1fr)') !== false ? 'YES' : 'NO') . "<br>";
    echo "<strong>Contains 'minmax(300px, 1fr)' (old grid):</strong> " . (strpos($content, 'minmax(300px, 1fr)') !== false ? 'YES' : 'NO') . "<br>";
}

echo "<hr>";
echo "<h3>WordPress Debug Info:</h3>";
echo "<strong>WP_DEBUG:</strong> " . (defined('WP_DEBUG') && WP_DEBUG ? 'TRUE' : 'FALSE') . "<br>";
echo "<strong>SCRIPT_DEBUG:</strong> " . (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? 'TRUE' : 'FALSE') . "<br>";
echo "<strong>Use Minified:</strong> " . ((!defined('SCRIPT_DEBUG') || !SCRIPT_DEBUG) ? 'YES (.min.css)' : 'NO (.css)') . "<br>";
