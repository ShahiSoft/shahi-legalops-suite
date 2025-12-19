<?php
/**
 * Menu Manager Class
 *
 * Handles all admin menu registration and navigation for the plugin.
 * Provides centralized menu management with capability-based access control.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalopsSuite\Admin;

use ShahiLegalopsSuite\Core\Security;
use ShahiLegalopsSuite\Admin\AnalyticsDashboard;
use ShahiLegalopsSuite\Admin\Consent;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu Manager Class
 *
 * Registers admin menus, submenus, and manages navigation structure.
 * Implements capability-based access control for all menu items.
 *
 * @since 1.0.0
 */
class MenuManager {

	/**
	 * Main menu slug
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const MENU_SLUG = 'shahi-legalops-suite';

	/**
	 * Security instance
	 *
	 * @since 1.0.0
	 * @var Security
	 */
	private $security;

	/**
	 * Dashboard page instance
	 *
	 * @since 1.0.0
	 * @var Dashboard
	 */
	private $dashboard;

	/**
	 * Module Dashboard page instance
	 *
	 * @since 1.0.0
	 * @var ModuleDashboard
	 */
	private $module_dashboard;

	/**
	 * Analytics Dashboard page instance
	 *
	 * @since 1.0.0
	 * @var AnalyticsDashboard
	 */
	private $analytics_dashboard;

	/**
	 * Settings page instance
	 *
	 * @since 1.0.0
	 * @var Settings
	 */
	private $settings;

	/**
	 * Consent manager page instance
	 *
	 * @since 3.0.1
	 * @var Consent
	 */
	private $consent;

	/**
	 * Initialize the menu manager
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->security = new Security();

		// Initialize page controllers
		$this->dashboard           = new Dashboard();
		$this->module_dashboard    = new ModuleDashboard();
		$this->analytics_dashboard = new AnalyticsDashboard();
		$this->settings            = new Settings();
		$this->consent             = new Consent();
	}

	/**
	 * Register all admin menus
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_menus() {
		// Main menu - Dashboard (default page)
		add_menu_page(
			__( 'ShahiLegalopsSuite Dashboard', 'shahi-legalops-suite' ),
			__( 'ShahiLegalopsSuite', 'shahi-legalops-suite' ),
			'manage_shahi_template',
			self::MENU_SLUG,
			array( $this->dashboard, 'render' ),
			$this->get_menu_icon(),
			30
		);

		// Dashboard submenu (rename the default)
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Dashboard', 'shahi-legalops-suite' ),
			__( 'Dashboard', 'shahi-legalops-suite' ),
			'manage_shahi_template',
			self::MENU_SLUG,
			array( $this->dashboard, 'render' )
		);

		// Analytics Dashboard submenu
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Analytics Dashboard', 'shahi-legalops-suite' ),
			__( 'Analytics Dashboard', 'shahi-legalops-suite' ),
			'view_shahi_analytics',
			self::MENU_SLUG . '-analytics-dashboard',
			array( $this->analytics_dashboard, 'render' )
		);

		// Module Dashboard submenu
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Module Dashboard', 'shahi-legalops-suite' ),
			__( 'Module Dashboard', 'shahi-legalops-suite' ),
			'manage_shahi_modules',
			self::MENU_SLUG . '-module-dashboard',
			array( $this->module_dashboard, 'render' )
		);

		// Settings submenu
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Settings', 'shahi-legalops-suite' ),
			__( 'Settings', 'shahi-legalops-suite' ),
			'edit_shahi_settings',
			self::MENU_SLUG . '-settings',
			array( $this->settings, 'render' )
		);

		// Consent & Compliance submenu
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Consent & Compliance', 'shahi-legalops-suite' ),
			__( 'Consent & Compliance', 'shahi-legalops-suite' ),
			'manage_options',
			self::MENU_SLUG . '-consent',
			array( $this->consent, 'render' )
		);

		// Submenus for optional modules are added by their own classes.

		// Debug Onboarding page (hidden from menu, accessible via Settings → Advanced tab)
		if ( current_user_can( 'manage_options' ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			add_submenu_page(
				null, // null parent = hidden from menu
				__( 'Debug Onboarding', 'shahi-legalops-suite' ),
				__( 'Debug Onboarding', 'shahi-legalops-suite' ),
				'manage_options',
				self::MENU_SLUG . '-debug-onboarding',
				function () {
					include SHAHI_LEGALOPS_SUITE_PATH . 'debug-onboarding.php';
				}
			);
		}
	}

	/**
	 * Add custom capabilities
	 *
	 * This method should be called on plugin activation to add
	 * custom capabilities to administrator role.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function add_capabilities() {
		$role = get_role( 'administrator' );

		if ( $role ) {
			$capabilities = array(
				'manage_shahi_template',
				'view_shahi_analytics',
				'manage_shahi_modules',
				'edit_shahi_settings',
			);

			foreach ( $capabilities as $cap ) {
				$role->add_cap( $cap );
			}
		}
	}

	/**
	 * Remove custom capabilities
	 *
	 * This method should be called on plugin deactivation to remove
	 * custom capabilities from administrator role.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function remove_capabilities() {
		$role = get_role( 'administrator' );

		if ( $role ) {
			$capabilities = array(
				'manage_shahi_template',
				'view_shahi_analytics',
				'manage_shahi_modules',
				'edit_shahi_settings',
			);

			foreach ( $capabilities as $cap ) {
				$role->remove_cap( $cap );
			}
		}
	}

	/**
	 * Get menu icon SVG (base64 encoded)
	 *
	 * Returns a custom SVG icon for the main menu item.
	 * The icon is a futuristic geometric shape that matches the dark theme.
	 *
	 * @since 1.0.0
	 * @return string Base64 encoded SVG icon
	 */
	private function get_menu_icon() {
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
            <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="currentColor" opacity="0.3"/>
            <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>';

		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	/**
	 * Get current admin page
	 *
	 * Determines which page is currently being viewed based on the
	 * page parameter in the URL.
	 *
	 * @since 1.0.0
	 * @return string Current page slug or empty string
	 */
	public function get_current_page() {
		if ( ! is_admin() ) {
			return '';
		}

		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

		// Check if it's one of our pages
		$valid_pages = array(
			self::MENU_SLUG,
			self::MENU_SLUG . '-analytics-dashboard',
			self::MENU_SLUG . '-module-dashboard',
			self::MENU_SLUG . '-settings',
			self::MENU_SLUG . '-support',
			self::MENU_SLUG . '-consent',
		);

		return in_array( $page, $valid_pages, true ) ? $page : '';
	}

	/**
	 * Check if current page is a plugin page
	 *
	 * @since 1.0.0
	 * @return bool True if on a plugin page, false otherwise
	 */
	public function is_plugin_page() {
		return ! empty( $this->get_current_page() );
	}

	/**
	 * Highlight parent menu item
	 *
	 * Ensures the main menu item is highlighted when on any submenu page.
	 *
	 * @since 1.0.0
	 * @param string $parent_file The parent file.
	 * @return string Modified parent file
	 */
	public function highlight_menu( $parent_file ) {
		global $plugin_page;

		if ( $this->is_plugin_page() ) {
			$plugin_page = self::MENU_SLUG;
		}

		return $parent_file;
	}

	/**
	 * Add admin body classes
	 *
	 * Adds custom CSS classes to the admin body for styling purposes.
	 *
	 * @since 1.0.0
	 * @param string $classes Existing body classes.
	 * @return string Modified body classes
	 */
	public function add_body_classes( $classes ) {
		if ( $this->is_plugin_page() ) {
			$classes .= ' shahi-legalops-suite-admin';
			$page     = $this->get_current_page();
			$classes .= ' shahi-page-' . str_replace( self::MENU_SLUG . '-', '', $page );
		}

		return $classes;
	}

	/**
	 * Get breadcrumb navigation
	 *
	 * Generates breadcrumb navigation for the current page.
	 *
	 * @since 1.0.0
	 * @return array Breadcrumb items
	 */
	public function get_breadcrumbs() {
		$breadcrumbs  = array();
		$current_page = $this->get_current_page();

		// Always start with Dashboard
		$breadcrumbs[] = array(
			'title'  => __( 'Dashboard', 'shahi-legalops-suite' ),
			'url'    => admin_url( 'admin.php?page=' . self::MENU_SLUG ),
			'active' => $current_page === self::MENU_SLUG,
		);

		// Add current page if not dashboard
		if ( $current_page !== self::MENU_SLUG ) {
			$page_titles = array(
				self::MENU_SLUG . '-analytics-dashboard' => __( 'Analytics Dashboard', 'shahi-legalops-suite' ),
				self::MENU_SLUG . '-module-dashboard'    => __( 'Module Dashboard', 'shahi-legalops-suite' ),
				self::MENU_SLUG . '-settings'            => __( 'Settings', 'shahi-legalops-suite' ),
				self::MENU_SLUG . '-support'             => __( 'Support & Docs', 'shahi-legalops-suite' ),
				self::MENU_SLUG . '-consent'             => __( 'Consent & Compliance', 'shahi-legalops-suite' ),
			);

			if ( isset( $page_titles[ $current_page ] ) ) {
				$breadcrumbs[] = array(
					'title'  => $page_titles[ $current_page ],
					'url'    => admin_url( 'admin.php?page=' . $current_page ),
					'active' => true,
				);
			}
		}

		return $breadcrumbs;
	}

	/**
	 * Render breadcrumb navigation
	 *
	 * Outputs the breadcrumb navigation HTML.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_breadcrumbs() {
		$breadcrumbs = $this->get_breadcrumbs();

		if ( empty( $breadcrumbs ) ) {
			return;
		}

		echo '<nav class="shahi-breadcrumbs">';
		echo '<ul class="shahi-breadcrumb-list">';

		foreach ( $breadcrumbs as $index => $item ) {
			$class = $item['active'] ? 'active' : '';
			echo '<li class="shahi-breadcrumb-item ' . esc_attr( $class ) . '">';

			if ( $item['active'] ) {
				echo '<span>' . esc_html( $item['title'] ) . '</span>';
			} else {
				echo '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['title'] ) . '</a>';
			}

			if ( $index < count( $breadcrumbs ) - 1 ) {
				echo '<span class="shahi-breadcrumb-separator">/</span>';
			}

			echo '</li>';
		}

		echo '</ul>';
		echo '</nav>';
	}
}

