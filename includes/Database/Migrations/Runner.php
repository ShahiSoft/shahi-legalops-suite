<?php
/**
 * Migration Runner
 *
 * Orchestrates running all database migrations.
 *
 * @package     ShahiLegalopsSuite
 * @subpackage  Database\Migrations
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalopsSuite\Database\Migrations;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Runner
 *
 * Manages execution of all database migrations.
 *
 * @since 3.0.1
 */
class Runner {

	/**
	 * Run all migrations (create tables)
	 *
	 * @since 3.0.1
	 * @return array Results of each migration
	 */
	public static function run_all() {
		$migrations = array(
			'Migration_2025_12_20_consent_table',
			'Migration_2025_12_20_dsr_requests_table',
			'Migration_2025_12_20_documents_table',
			'Migration_2025_12_20_trackers_table',
			'Migration_2025_12_20_vendors_table',
			'Migration_2025_12_20_form_submissions_table',
			'Migration_2025_12_20_form_issues_table',
		);

		$results = array();
		foreach ( $migrations as $migration ) {
			$class = __NAMESPACE__ . '\\' . $migration;
			if ( class_exists( $class ) ) {
				$success                = $class::up();
				$results[ $migration ] = $success ? 'OK' : 'FAILED';
			} else {
				$results[ $migration ] = 'CLASS NOT FOUND';
			}
		}

		return $results;
	}

	/**
	 * Rollback all migrations (drop tables)
	 *
	 * @since 3.0.1
	 * @return array Results of each migration rollback
	 */
	public static function rollback_all() {
		// Reverse order to handle dependencies
		$migrations = array(
			'Migration_2025_12_20_form_issues_table',
			'Migration_2025_12_20_form_submissions_table',
			'Migration_2025_12_20_vendors_table',
			'Migration_2025_12_20_trackers_table',
			'Migration_2025_12_20_documents_table',
			'Migration_2025_12_20_dsr_requests_table',
			'Migration_2025_12_20_consent_table',
		);

		$results = array();
		foreach ( $migrations as $migration ) {
			$class = __NAMESPACE__ . '\\' . $migration;
			if ( class_exists( $class ) ) {
				$success                = $class::down();
				$results[ $migration ] = $success ? 'DROPPED' : 'FAILED';
			} else {
				$results[ $migration ] = 'CLASS NOT FOUND';
			}
		}

		return $results;
	}

	/**
	 * Check if all migrations have been run
	 *
	 * @since 3.0.1
	 * @return bool True if all tables exist, false otherwise
	 */
	public static function is_migrated() {
		global $wpdb;

		$tables = array(
			$wpdb->prefix . 'slos_consent',
			$wpdb->prefix . 'slos_dsr_requests',
			$wpdb->prefix . 'slos_documents',
			$wpdb->prefix . 'slos_trackers',
			$wpdb->prefix . 'slos_vendors',
			$wpdb->prefix . 'slos_form_submissions',
			$wpdb->prefix . 'slos_form_issues',
		);

		foreach ( $tables as $table ) {
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get list of tables that exist
	 *
	 * @since 3.0.1
	 * @return array List of existing table names
	 */
	public static function get_existing_tables() {
		global $wpdb;

		$tables = array(
			$wpdb->prefix . 'slos_consent',
			$wpdb->prefix . 'slos_dsr_requests',
			$wpdb->prefix . 'slos_documents',
			$wpdb->prefix . 'slos_trackers',
			$wpdb->prefix . 'slos_vendors',
			$wpdb->prefix . 'slos_form_submissions',
			$wpdb->prefix . 'slos_form_issues',
		);

		$existing = array();
		foreach ( $tables as $table ) {
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) === $table ) {
				$existing[] = $table;
			}
		}

		return $existing;
	}
}
