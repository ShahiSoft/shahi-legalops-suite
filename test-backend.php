<?php
/**
 * Test Backend for Legal Docs Generation
 * 
 * Usage: wp eval-file test-backend.php
 */

// Load WordPress environment if not already loaded (for direct execution if needed, though eval-file is better)
if (!defined('ABSPATH')) {
    require_once(explode('wp-content', __FILE__)[0] . 'wp-load.php');
}

use ShahiLegalopsSuite\Services\Document_Generator;
use ShahiLegalopsSuite\Core\Activator;

echo "Starting Backend Test...\n";

// 1. Run Activation (to create tables)
echo "1. Ensuring tables exist...\n";
Activator::activate();
echo "   Tables should be created.\n";

// 2. Create Dummy Profile
echo "2. Creating/Finding Dummy Profile...\n";
global $wpdb;
$profile_table = $wpdb->prefix . 'slos_company_profile';

// Check if table exists (it might not if this is a fresh environment without the full plugin active)
if ($wpdb->get_var("SHOW TABLES LIKE '$profile_table'") != $profile_table) {
    echo "   Creating profile table manually for test...\n";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $profile_table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        company_name varchar(255) NOT NULL,
        data longtext,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

$profile_data = array(
    'company' => array(
        'legal_name' => 'Acme Test Corp',
        'address' => array(
            'street' => '123 Test St',
            'city' => 'Testville',
            'country' => 'US'
        ),
        'business_type' => 'inc'
    )
);

$existing_profile = $wpdb->get_row("SELECT * FROM $profile_table LIMIT 1");
if (!$existing_profile) {
    $wpdb->insert($profile_table, array(
        'company_name' => 'Acme Test Corp',
        'data' => json_encode($profile_data)
    ));
    $profile_id = $wpdb->insert_id;
    echo "   Created profile ID: $profile_id\n";
} else {
    $profile_id = $existing_profile->id;
    // Update data to ensure it matches our test needs
    $wpdb->update($profile_table, array('data' => json_encode($profile_data)), array('id' => $profile_id));
    echo "   Using existing profile ID: $profile_id\n";
}

// 3. Create Dummy Template
echo "3. Creating Dummy Template...\n";
$template_dir = SHAHI_LEGALOPS_SUITE_PLUGIN_DIR . 'templates/legaldocs/';
if (!file_exists($template_dir)) {
    mkdir($template_dir, 0755, true);
}
file_put_contents($template_dir . 'test-doc.html', '<h1>{{company.legal_name}}</h1><p>Address: {{company.address.street}}, {{company.address.city}}</p>');
echo "   Template created at: {$template_dir}test-doc.html\n";

// 4. Test Generation
echo "4. Testing Generation...\n";
$generator = new Document_Generator();
$result = $generator->generate_from_profile('test-doc', $profile_id);

if ($result['success']) {
    echo "   SUCCESS! Document generated.\n";
    echo "   Doc ID: " . $result['doc_id'] . "\n";
    echo "   Content Preview: " . substr($result['content'], 0, 100) . "...\n";
} else {
    echo "   FAILURE! " . $result['error'] . "\n";
    if (isset($result['missing'])) {
        print_r($result['missing']);
    }
}

// Clean up
// unlink($template_dir . 'test-doc.html');

echo "Test Complete.\n";
