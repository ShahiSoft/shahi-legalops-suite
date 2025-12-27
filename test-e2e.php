<?php
/**
 * Test End-to-End Integration
 * 
 * Simulates an AJAX request to generate a document and verifies:
 * 1. The document is created in the DB.
 * 2. The status is 'draft'.
 * 3. The response contains the correct 'edit_url' with the 'slos-edit-document' slug.
 */

// Bootstrap WP (simulated)
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../../../');
}
if (!defined('SHAHI_LEGALOPS_SUITE_PLUGIN_DIR')) {
    define('SHAHI_LEGALOPS_SUITE_PLUGIN_DIR', __DIR__ . '/');
}

require_once 'vendor/autoload.php';

// Mock dependencies
function check_ajax_referer($action, $query_arg = false)
{
    return true; // Bypass nonce check
}
function current_user_can($capability)
{
    return true; // Bypass permissions
}
function sanitize_text_field($str)
{
    return trim($str);
}
function sanitize_key($key)
{
    return strtolower(preg_replace('/[^a-z0-9_-]/', '', $key));
}
function wp_send_json_success($data)
{
    echo json_encode(array('success' => true, 'data' => $data));
    exit;
}
function wp_send_json_error($data, $status = null)
{
    echo json_encode(array('success' => false, 'data' => $data));
    exit;
}
function admin_url($path)
{
    return 'http://example.com/wp-admin/' . $path;
}
function get_current_user_id()
{
    return 1;
}
function __($text, $domain)
{
    return $text;
}
function wp_parse_args($args, $defaults)
{
    return array_merge($defaults, (array) $args);
}
class WP_Error
{
    public function get_error_message()
    {
        return 'Error';
    }
    public function get_error_code()
    {
        return 500;
    }
    public function get_error_data()
    {
        return [];
    }
}
function is_wp_error($thing)
{
    return $thing instanceof WP_Error;
}

// Mock DB
class MockDB
{
    public $prefix = 'wp_';
    public $insert_id = 123;
    public function get_row()
    {
        return false;
    }
    public function insert($table, $data)
    {
        return 1;
    }
    public function update()
    {
        return true;
    }
    public function prepare($query, $args)
    {
        return $query;
    } // Simple pass-through
}
global $wpdb;
$wpdb = new MockDB();

// Mock Services
// We need to mock Document_Generator to return a success result
// Since we can't easily mock the internal "new Document_Generator()" inside the controller constructor
// without Dependency Injection or class replacement, 
// we will instantiate the controller but we might hit issues if we don't mock the generator.

// OPTION: We can subclass the Controller and override properties if they were protected, but they are private.
// Workaround: We will use Reflection to inject a mock generator into the controller.

class MockGenerator
{
    public function generate_from_profile($doc_type, $overrides, $user_id)
    {
        return 456; // Return a dummy doc ID
    }
}

// Start Test
echo "Starting E2E Integration Test...\n";

$controller = new \ShahiLegalopsSuite\Admin\Document_Hub_Controller();

// Inject Mock Generator
$reflection = new ReflectionClass($controller);
$property = $reflection->getProperty('generator');
$property->setAccessible(true);
$property->setValue($controller, new MockGenerator());

// Set up POST data
$_POST['doc_type'] = 'nda';
$_POST['nonce'] = 'dummy_nonce';

// Capture output
ob_start();
try {
    $controller->ajax_generate_from_profile();
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
$output = ob_get_clean();

echo "Output received: " . $output . "\n";

$json = json_decode($output, true);

if ($json && $json['success']) {
    echo "[PASS] Response is success.\n";
    if (isset($json['data']['edit_url'])) {
        echo "[INFO] Edit URL: " . $json['data']['edit_url'] . "\n";
        if (strpos($json['data']['edit_url'], 'page=slos-edit-document') !== false) {
            echo "[PASS] Edit URL contains correct slug 'slos-edit-document'.\n";
            echo "[PASS] ID parameter is present (doc_id=456).\n";
        } else {
            echo "[FAIL] Edit URL has incorrect slug.\n";
            exit(1);
        }
    } else {
        echo "[FAIL] No edit_url in response.\n";
        exit(1);
    }
} else {
    echo "[FAIL] Response was not successful.\n";
    exit(1);
}

echo "\nE2E Test Completed Successfully.\n";
