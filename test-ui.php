<?php
/**
 * Test UI Assets & Module Registration
 */

// Simulate WordPress environment constants if not set (for CLI)
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../../../');
}
if (!defined('SHAHI_LEGALOPS_SUITE_PLUGIN_DIR')) {
    define('SHAHI_LEGALOPS_SUITE_PLUGIN_DIR', __DIR__ . '/');
}

echo "Starting UI Asset Verification...\n";

$files = [
    'templates/admin/document-hub.php',
    'templates/admin/documents/tab-generate.php',
    'templates/admin/documents/partials/document-card.php',
    'templates/admin/documents/partials/review-modal.php',
    'assets/css/document-hub.css',
    'assets/js/document-hub.js',
    'includes/Modules/LegalDocs/Module.php',
    'includes/Services/Document_Hub_Service.php'
];

$missing = [];
foreach ($files as $file) {
    if (file_exists(SHAHI_LEGALOPS_SUITE_PLUGIN_DIR . $file)) {
        echo "[OK] Found: $file\n";
    } else {
        echo "[MISSING] $file\n";
        $missing[] = $file;
    }
}

if (!empty($missing)) {
    echo "\nFAILED: Missing files detected.\n";
    exit(1);
}

echo "\nAll files present.\n";

// Verify Class Loading
require_once 'vendor/autoload.php'; // Assuming composer autoloader
// Or manual requires if autoloader relies on WP
require_once 'includes/Modules/Module.php';
require_once 'includes/Modules/LegalDocs/Module.php';

if (class_exists('ShahiLegalopsSuite\Modules\LegalDocs\Module')) {
    echo "[OK] LegalDocs Module class exists.\n";
} else {
    echo "[FAIL] LegalDocs Module class not found.\n";
    exit(1);
}

echo "\nVerification Complete: UI Setup looks good.\n";
