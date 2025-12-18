<?php
/**
 * Consent Management Module (Dashboard Adapter)
 *
 * Adapter to expose the Consent Management module in the Module Dashboard,
 * allowing enable/disable and linking to its settings page.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Modules
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalopsSuite\Modules;

use ShahiLegalOpsSuite\Modules\Consent\Consent as ConsentCore;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Consent Management Dashboard Module Adapter
 *
 * Exposes the Consent Management feature in the premium Module Dashboard,
 * mapping enable/disable to the core consent settings and providing a settings URL.
 */
class Consent_Module extends Module {

    /**
     * Core consent module instance
     *
     * @var ConsentCore
     */
    private $consent_core;

    /**
     * Get module unique key
     *
     * @since 1.0.0
     * @return string
     */
    public function get_key() {
        return 'consent-management';
    }

    /**
     * Get module name
     *
     * @since 1.0.0
     * @return string
     */
    public function get_name() {
        return __('Consent Management', 'shahi-legalops-suite');
    }

    /**
     * Get module description
     *
     * @since 1.0.0
     * @return string
     */
    public function get_description() {
        return __('Manage cookie consent, blocking, and proof of consent across jurisdictions (GDPR, CCPA, LGPD).', 'shahi-legalops-suite');
    }

    /**
     * Get module icon
     *
     * @since 1.0.0
     * @return string
     */
    public function get_icon() {
        return 'dashicons-lock';
    }

    /**
     * Get module category
     *
     * @since 1.0.0
     * @return string
     */
    public function get_category() {
        return 'compliance';
    }

    /**
     * Initialize module
     *
     * Instantiate and initialize the core consent module.
     *
     * @since 1.0.0
     * @return void
     */
    public function init() {
        // Instantiate core consent module
        if (class_exists('ShahiLegalOpsSuite\\Modules\\Consent\\Consent')) {
            $this->consent_core = new ConsentCore();
            $this->consent_core->initialize();
        }
    }

    /**
     * Hook called on module activation
     *
     * Sync enabled state with core consent module settings.
     *
     * @since 1.0.0
     * @return void
     */
    protected function on_activate() {
        // Toggle core consent module setting.
        $settings = (array) get_option('complyflow_consent_settings', []);
        $settings['enabled'] = true;
        update_option('complyflow_consent_settings', $settings);

        // Ensure core tables exist.
        if (class_exists('ShahiLegalOpsSuite\\Modules\\Consent\\Consent')) {
            $consent = new ConsentCore();
            // Create tables and ensure initialization sequence is respected.
            $consent->create_tables();
        }
    }

    /**
     * Hook called on module deactivation
     *
     * Sync disabled state with core consent module settings.
     *
     * @since 1.0.0
     * @return void
     */
    protected function on_deactivate() {
        $settings = (array) get_option('complyflow_consent_settings', []);
        $settings['enabled'] = false;
        update_option('complyflow_consent_settings', $settings);
    }

    /**
     * Get module settings URL
     *
     * Points to the Consent Management settings admin page.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_settings_url() {
        return admin_url('admin.php?page=complyflow-consent-settings');
    }
}
