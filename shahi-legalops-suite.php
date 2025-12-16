<?php
/**
 * Plugin Name: Shahi LegalOps Suite
 * Plugin URI: https://shahisoft.com/shahi-legalops-suite
 * Description: A professional, modular WordPress plugin template with dark futuristic UI, analytics dashboard, and extensible architecture.
 * Version: 1.0.0
 * Author: ShahiSoft
 * Author URI: https://shahisoft.com
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: shahi-legalops-suite
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Core
 * @license    GPL-3.0+
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Plugin Constants
 */
define('SHAHI_LEGALOPS_SUITE_VERSION', '1.0.0');
define('SHAHI_LEGALOPS_SUITE_PATH', plugin_dir_path(__FILE__));
define('SHAHI_LEGALOPS_SUITE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SHAHI_LEGALOPS_SUITE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SHAHI_LEGALOPS_SUITE_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('SHAHI_LEGALOPS_SUITE_PLUGIN_FILE', __FILE__);

/**
 * PSR-4 Autoloader
 */
require_once SHAHI_LEGALOPS_SUITE_PLUGIN_DIR . 'includes/Core/Autoloader.php';
ShahiLegalopsSuite\Core\Autoloader::register();

/**
 * Plugin Activation Hook
 */
function activate_shahi_template() {
    ShahiLegalopsSuite\Core\Activator::activate();
}
register_activation_hook(__FILE__, 'activate_shahi_template');

/**
 * Plugin Deactivation Hook
 */
function deactivate_shahi_template() {
    ShahiLegalopsSuite\Core\Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_shahi_template');

/**
 * Initialize the plugin
 */
function run_shahi_template() {
    $plugin = new ShahiLegalopsSuite\Core\Plugin();
    $plugin->run();
}
run_shahi_template();
