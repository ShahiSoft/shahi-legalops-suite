<?php
/**
 * Debug Onboarding Status
 * 
 * Temporary debug file to check onboarding status
 * Access: /wp-admin/admin.php?page=shahi-template-debug-onboarding
 */

// Check if accessed directly
if (!defined('WPINC')) {
    die;
}

// Check user capabilities
if (!current_user_can('manage_options')) {
    wp_die('Access denied');
}

echo '<div class="wrap">';
echo '<h1>Onboarding Debug Information</h1>';

// 1. Check option values
echo '<h2>Database Options</h2>';
echo '<table class="widefat">';
echo '<thead><tr><th>Option Name</th><th>Value</th><th>Type</th></tr></thead>';
echo '<tbody>';

$completed = get_option('shahi_template_onboarding_completed', 'NOT_FOUND');
$data = get_option('shahi_template_onboarding_data', 'NOT_FOUND');

echo '<tr>';
echo '<td><code>shahi_template_onboarding_completed</code></td>';
echo '<td><pre>' . var_export($completed, true) . '</pre></td>';
echo '<td>' . gettype($completed) . '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><code>shahi_template_onboarding_data</code></td>';
echo '<td><pre>' . var_export($data, true) . '</pre></td>';
echo '<td>' . gettype($data) . '</td>';
echo '</tr>';

echo '</tbody></table>';

// 2. Direct database check
global $wpdb;
echo '<h2>Direct Database Query</h2>';
$db_options = $wpdb->get_results(
    "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE '%onboarding%'",
    ARRAY_A
);
echo '<table class="widefat">';
echo '<thead><tr><th>Option Name</th><th>Value</th></tr></thead>';
echo '<tbody>';
foreach ($db_options as $opt) {
    echo '<tr>';
    echo '<td><code>' . esc_html($opt['option_name']) . '</code></td>';
    echo '<td><pre>' . esc_html($opt['option_value']) . '</pre></td>';
    echo '</tr>';
}
echo '</tbody></table>';

// 3. Check capabilities
echo '<h2>User Capabilities</h2>';
$user = wp_get_current_user();
echo '<p>Current User: ' . esc_html($user->user_login) . ' (ID: ' . $user->ID . ')</p>';
echo '<p>Has <code>manage_shahi_template</code>: ' . (current_user_can('manage_shahi_template') ? 'YES' : 'NO') . '</p>';
echo '<p>Has <code>edit_shahi_settings</code>: ' . (current_user_can('edit_shahi_settings') ? 'YES' : 'NO') . '</p>';
echo '<p>Has <code>manage_options</code>: ' . (current_user_can('manage_options') ? 'YES' : 'NO') . '</p>';

// 4. Check if onboarding class exists and its methods
echo '<h2>Onboarding Class Status</h2>';
if (class_exists('\ShahiTemplate\Admin\Onboarding')) {
    echo '<p>✅ Onboarding class exists</p>';
    
    $onboarding = new \ShahiTemplate\Admin\Onboarding();
    
    echo '<p>should_show_onboarding(): ' . ($onboarding->should_show_onboarding() ? 'TRUE' : 'FALSE') . '</p>';
    echo '<p>is_completed(): ' . ($onboarding->is_completed() ? 'TRUE' : 'FALSE') . '</p>';
} else {
    echo '<p>❌ Onboarding class NOT found</p>';
}

// 5. Check current page
echo '<h2>Current Page Info</h2>';
echo '<p>Current Screen ID: ' . get_current_screen()->id . '</p>';
echo '<p>Page Parameter: ' . (isset($_GET['page']) ? esc_html($_GET['page']) : 'NONE') . '</p>';
echo '<p>is_admin(): ' . (is_admin() ? 'YES' : 'NO') . '</p>';

// 6. Check if scripts are enqueued
echo '<h2>Enqueued Scripts</h2>';
global $wp_scripts;
echo '<ul>';
$onboarding_scripts = ['shahi-onboarding', 'shahi-components', 'shahi-admin-global'];
foreach ($onboarding_scripts as $script) {
    $enqueued = wp_script_is($script, 'enqueued');
    $registered = wp_script_is($script, 'registered');
    echo '<li><code>' . $script . '</code>: ';
    echo 'Registered=' . ($registered ? 'YES' : 'NO') . ', ';
    echo 'Enqueued=' . ($enqueued ? 'YES' : 'NO');
    echo '</li>';
}
echo '</ul>';

// 7. Actions to take
echo '<h2>Actions</h2>';
echo '<form method="post" style="margin: 20px 0;">';
wp_nonce_field('debug_onboarding_action', 'debug_nonce');

if (isset($_POST['action']) && isset($_POST['debug_nonce']) && wp_verify_nonce($_POST['debug_nonce'], 'debug_onboarding_action')) {
    if ($_POST['action'] === 'force_delete') {
        delete_option('shahi_template_onboarding_completed');
        delete_option('shahi_template_onboarding_data');
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name IN ('shahi_template_onboarding_completed', 'shahi_template_onboarding_data')");
        wp_cache_flush();
        echo '<div class="notice notice-success"><p>✅ Options forcefully deleted and cache flushed!</p></div>';
        echo '<script>setTimeout(function(){ window.location.reload(); }, 1000);</script>';
    }
}

echo '<button type="submit" name="action" value="force_delete" class="button button-primary">Force Delete Options & Flush Cache</button>';
echo '</form>';

echo '<p><a href="' . admin_url('admin.php?page=shahi-template') . '" class="button">Go to Dashboard (should show modal)</a></p>';

echo '</div>';
