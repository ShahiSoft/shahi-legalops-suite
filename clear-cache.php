<?php
/**
 * Cache Clear Utility
 * Use this file to manually clear WordPress caches
 * Access via: /wp-content/plugins/ShahiTemplate/clear-cache.php
 */

// Load WordPress
$wp_load_path = dirname(dirname(dirname(__DIR__))) . '/wp-load.php';
if (file_exists($wp_load_path)) {
    require_once($wp_load_path);
} else {
    die('Could not find wp-load.php at: ' . $wp_load_path);
}

// Security check - only admin can access
if (!current_user_can('manage_options')) {
    die('Access denied. Admin privileges required.');
}

echo "<h1>Cache Clearing Utility</h1>";
echo "<hr>";

// Clear WordPress object cache
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "✓ WordPress object cache flushed<br>";
}

// Clear all transients
global $wpdb;
$count = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
echo "✓ Cleared {$count} transients<br>";

// Clear rewrite rules cache
flush_rewrite_rules();
echo "✓ Rewrite rules flushed<br>";

// Delete any plugin-specific transients
delete_transient('shahi_template_cache');
delete_transient('shahi_template_modules');
echo "✓ Plugin transients cleared<br>";

// Touch the CSS files to update modification time
$plugin_dir = plugin_dir_path(__FILE__);
$css_files = array(
    $plugin_dir . 'assets/css/admin-modules.css',
    $plugin_dir . 'assets/css/admin-modules.min.css',
);

foreach ($css_files as $file) {
    if (file_exists($file)) {
        touch($file);
        echo "✓ Updated timestamp for: " . basename($file) . " (New mtime: " . filemtime($file) . ")<br>";
    }
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Clear your browser cache (Ctrl+Shift+Delete)</li>";
echo "<li>Hard refresh the admin modules page (Ctrl+F5)</li>";
echo "<li>If still not working, try opening in incognito/private mode</li>";
echo "</ol>";

echo "<hr>";
echo "<p><strong>Current CSS File Info:</strong></p>";
$css_file = $plugin_dir . 'assets/css/admin-modules.min.css';
if (file_exists($css_file)) {
    echo "File: " . $css_file . "<br>";
    echo "Size: " . filesize($css_file) . " bytes<br>";
    echo "Modified: " . date('Y-m-d H:i:s', filemtime($css_file)) . "<br>";
    echo "Version string: " . filemtime($css_file) . '.' . filesize($css_file) . "<br>";
}

echo "<hr>";
echo "<p><a href='" . admin_url('admin.php?page=shahi-modules') . "'>Go to Modules Page</a></p>";
