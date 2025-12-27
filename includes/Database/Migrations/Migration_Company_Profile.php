<?php
/**
 * Migration: Company Profile Table
 *
 * Creates the wp_slos_company_profile table for storing company/organization
 * data used in legal document generation.
 *
 * @package     ShahiLegalopsSuite
 * @subpackage  Database\Migrations
 * @version     4.1.0
 * @since       4.1.0
 */

namespace ShahiLegalopsSuite\Database\Migrations;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Migration_Company_Profile
 *
 * Handles creation and management of the company profile table.
 *
 * @since 4.1.0
 */
class Migration_Company_Profile {

	/**
	 * Table name without prefix
	 */
	const TABLE_NAME = 'slos_company_profile';

	/**
	 * Migration version
	 */
	const VERSION = '4.1.0';

	/**
	 * Run the migration (create table)
	 *
	 * @since 4.1.0
	 * @return bool True on success, false on failure
	 */
	public static function up(): bool {
		global $wpdb;

		$table_name      = $wpdb->prefix . self::TABLE_NAME;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			profile_data LONGTEXT NOT NULL,
			completion_percentage INT(3) UNSIGNED NOT NULL DEFAULT 0,
			version INT(10) UNSIGNED NOT NULL DEFAULT 1,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			updated_by BIGINT(20) UNSIGNED DEFAULT NULL,
			PRIMARY KEY (id),
			KEY idx_version (version),
			KEY idx_updated_at (updated_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Verify table was created
		$table_exists = $wpdb->get_var(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name )
		) === $table_name;

		if ( $table_exists ) {
			update_option( 'slos_migration_company_profile_version', self::VERSION );
		}

		return $table_exists;
	}

	/**
	 * Reverse the migration (drop table)
	 *
	 * @since 4.1.0
	 * @return bool True on success
	 */
	public static function down(): bool {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

		delete_option( 'slos_migration_company_profile_version' );

		return true;
	}

	/**
	 * Check if migration has been applied
	 *
	 * @since 4.1.0
	 * @return bool True if table exists
	 */
	public static function is_applied(): bool {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$table_exists = $wpdb->get_var(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name )
		) === $table_name;

		return $table_exists;
	}

	/**
	 * Get the default profile structure
	 *
	 * This defines all available fields for the company profile.
	 *
	 * @since 4.1.0
	 * @return array Default profile structure
	 */
	public static function get_default_profile_structure(): array {
		return array(
			// Step 1: Company Information
			'company'         => array(
				'legal_name'          => '',
				'trading_name'        => '',
				'registration_number' => '',
				'vat_number'          => '',
				'address'             => array(
					'street'      => '',
					'city'        => '',
					'state'       => '',
					'postal_code' => '',
					'country'     => '',
				),
				'business_type'       => '',
				'industry'            => '',
			),
			// Step 2: Contact Information
			'contacts'        => array(
				'legal_email'   => '',
				'support_email' => '',
				'phone'         => '',
				'dpo'           => array(
					'name'    => '',
					'email'   => '',
					'phone'   => '',
					'address' => '',
				),
			),
			// Step 3: Website Information
			'website'         => array(
				'url'                 => '',
				'app_name'            => '',
				'service_description' => '',
				'target_audience'     => '',
			),
			// Step 4: Data Collection
			'data_collection' => array(
				'personal_data_types'  => array(),
				'purposes'             => array(),
				'lawful_bases'         => array(),
				'special_categories'   => false,
				'children_data'        => false,
				'minimum_age'          => 16,
			),
			// Step 5: Third Parties
			'third_parties'   => array(
				'processors' => array(),
				'partners'   => array(),
			),
			// Step 6: Cookies
			'cookies'         => array(
				'essential'  => array(),
				'analytics'  => array(),
				'marketing'  => array(),
				'functional' => array(),
			),
			// Step 7: Legal Framework
			'legal'           => array(
				'primary_jurisdiction'  => '',
				'gdpr_applies'          => false,
				'ccpa_applies'          => false,
				'lgpd_applies'          => false,
				'supervisory_authority' => '',
				'representative_eu'     => array(
					'name'    => '',
					'email'   => '',
					'address' => '',
				),
				'representative_uk'     => array(
					'name'    => '',
					'email'   => '',
					'address' => '',
				),
			),
			// Step 8: Data Retention & Security
			'retention'       => array(
				'default_period'   => '',
				'by_category'      => array(),
				'deletion_policy'  => '',
				'backup_retention' => '',
			),
			'security'        => array(
				'measures'              => array(),
				'certifications'        => array(),
				'breach_procedure'      => '',
				'dpia_required'         => false,
			),
			'user_rights'     => array(
				'response_timeframe'    => 30,
				'identity_verification' => '',
				'appeal_process'        => '',
			),
			// Metadata
			'_meta'           => array(
				'version'         => 1,
				'completion'      => 0,
				'completed_steps' => array(),
				'last_step'       => 1,
				'created_at'      => '',
				'updated_at'      => '',
			),
		);
	}

	/**
	 * Get available personal data types
	 *
	 * @since 4.1.0
	 * @return array Data types with labels
	 */
	public static function get_personal_data_types(): array {
		return array(
			'name'              => __( 'Full Name', 'shahi-legalops-suite' ),
			'email'             => __( 'Email Address', 'shahi-legalops-suite' ),
			'phone'             => __( 'Phone Number', 'shahi-legalops-suite' ),
			'address'           => __( 'Physical Address', 'shahi-legalops-suite' ),
			'ip_address'        => __( 'IP Address', 'shahi-legalops-suite' ),
			'device_id'         => __( 'Device Identifiers', 'shahi-legalops-suite' ),
			'location'          => __( 'Location Data', 'shahi-legalops-suite' ),
			'payment'           => __( 'Payment Information', 'shahi-legalops-suite' ),
			'browsing_history'  => __( 'Browsing History', 'shahi-legalops-suite' ),
			'purchase_history'  => __( 'Purchase History', 'shahi-legalops-suite' ),
			'account_data'      => __( 'Account Credentials', 'shahi-legalops-suite' ),
			'social_profiles'   => __( 'Social Media Profiles', 'shahi-legalops-suite' ),
			'photos'            => __( 'Photos/Images', 'shahi-legalops-suite' ),
			'communications'    => __( 'Communications Content', 'shahi-legalops-suite' ),
			'employment'        => __( 'Employment Information', 'shahi-legalops-suite' ),
			'education'         => __( 'Education History', 'shahi-legalops-suite' ),
			'health'            => __( 'Health Data', 'shahi-legalops-suite' ),
			'biometric'         => __( 'Biometric Data', 'shahi-legalops-suite' ),
			'financial'         => __( 'Financial Information', 'shahi-legalops-suite' ),
			'preferences'       => __( 'User Preferences', 'shahi-legalops-suite' ),
		);
	}

	/**
	 * Get available data processing purposes
	 *
	 * @since 4.1.0
	 * @return array Purposes with labels
	 */
	public static function get_processing_purposes(): array {
		return array(
			'service_delivery'   => __( 'Service Delivery', 'shahi-legalops-suite' ),
			'account_management' => __( 'Account Management', 'shahi-legalops-suite' ),
			'customer_support'   => __( 'Customer Support', 'shahi-legalops-suite' ),
			'communication'      => __( 'Communication with Users', 'shahi-legalops-suite' ),
			'marketing'          => __( 'Marketing & Promotions', 'shahi-legalops-suite' ),
			'analytics'          => __( 'Analytics & Improvement', 'shahi-legalops-suite' ),
			'personalization'    => __( 'Personalization', 'shahi-legalops-suite' ),
			'security'           => __( 'Security & Fraud Prevention', 'shahi-legalops-suite' ),
			'legal_compliance'   => __( 'Legal Compliance', 'shahi-legalops-suite' ),
			'research'           => __( 'Research & Development', 'shahi-legalops-suite' ),
			'advertising'        => __( 'Targeted Advertising', 'shahi-legalops-suite' ),
			'transactions'       => __( 'Processing Transactions', 'shahi-legalops-suite' ),
		);
	}

	/**
	 * Get available lawful bases (GDPR Article 6)
	 *
	 * @since 4.1.0
	 * @return array Lawful bases with labels
	 */
	public static function get_lawful_bases(): array {
		return array(
			'consent'            => __( 'Consent', 'shahi-legalops-suite' ),
			'contract'           => __( 'Contractual Necessity', 'shahi-legalops-suite' ),
			'legal_obligation'   => __( 'Legal Obligation', 'shahi-legalops-suite' ),
			'vital_interests'    => __( 'Vital Interests', 'shahi-legalops-suite' ),
			'public_task'        => __( 'Public Task', 'shahi-legalops-suite' ),
			'legitimate_interest' => __( 'Legitimate Interest', 'shahi-legalops-suite' ),
		);
	}

	/**
	 * Get available retention periods
	 *
	 * @since 4.1.0
	 * @return array Retention periods with labels
	 */
	public static function get_retention_periods(): array {
		return array(
			'session'       => __( 'Session Only', 'shahi-legalops-suite' ),
			'30_days'       => __( '30 Days', 'shahi-legalops-suite' ),
			'90_days'       => __( '90 Days', 'shahi-legalops-suite' ),
			'6_months'      => __( '6 Months', 'shahi-legalops-suite' ),
			'1_year'        => __( '1 Year', 'shahi-legalops-suite' ),
			'2_years'       => __( '2 Years', 'shahi-legalops-suite' ),
			'3_years'       => __( '3 Years', 'shahi-legalops-suite' ),
			'5_years'       => __( '5 Years', 'shahi-legalops-suite' ),
			'7_years'       => __( '7 Years', 'shahi-legalops-suite' ),
			'10_years'      => __( '10 Years', 'shahi-legalops-suite' ),
			'indefinite'    => __( 'Indefinite (Until Deletion Request)', 'shahi-legalops-suite' ),
			'legal_minimum' => __( 'As Required by Law', 'shahi-legalops-suite' ),
		);
	}

	/**
	 * Get list of countries
	 *
	 * @since 4.1.0
	 * @return array Country codes with names
	 */
	public static function get_countries(): array {
		return array(
			''   => __( 'Select Country...', 'shahi-legalops-suite' ),
			'US' => __( 'United States', 'shahi-legalops-suite' ),
			'GB' => __( 'United Kingdom', 'shahi-legalops-suite' ),
			'CA' => __( 'Canada', 'shahi-legalops-suite' ),
			'AU' => __( 'Australia', 'shahi-legalops-suite' ),
			'DE' => __( 'Germany', 'shahi-legalops-suite' ),
			'FR' => __( 'France', 'shahi-legalops-suite' ),
			'ES' => __( 'Spain', 'shahi-legalops-suite' ),
			'IT' => __( 'Italy', 'shahi-legalops-suite' ),
			'NL' => __( 'Netherlands', 'shahi-legalops-suite' ),
			'BE' => __( 'Belgium', 'shahi-legalops-suite' ),
			'AT' => __( 'Austria', 'shahi-legalops-suite' ),
			'CH' => __( 'Switzerland', 'shahi-legalops-suite' ),
			'SE' => __( 'Sweden', 'shahi-legalops-suite' ),
			'NO' => __( 'Norway', 'shahi-legalops-suite' ),
			'DK' => __( 'Denmark', 'shahi-legalops-suite' ),
			'FI' => __( 'Finland', 'shahi-legalops-suite' ),
			'IE' => __( 'Ireland', 'shahi-legalops-suite' ),
			'PT' => __( 'Portugal', 'shahi-legalops-suite' ),
			'PL' => __( 'Poland', 'shahi-legalops-suite' ),
			'CZ' => __( 'Czech Republic', 'shahi-legalops-suite' ),
			'HU' => __( 'Hungary', 'shahi-legalops-suite' ),
			'RO' => __( 'Romania', 'shahi-legalops-suite' ),
			'BG' => __( 'Bulgaria', 'shahi-legalops-suite' ),
			'GR' => __( 'Greece', 'shahi-legalops-suite' ),
			'NZ' => __( 'New Zealand', 'shahi-legalops-suite' ),
			'SG' => __( 'Singapore', 'shahi-legalops-suite' ),
			'JP' => __( 'Japan', 'shahi-legalops-suite' ),
			'KR' => __( 'South Korea', 'shahi-legalops-suite' ),
			'IN' => __( 'India', 'shahi-legalops-suite' ),
			'BR' => __( 'Brazil', 'shahi-legalops-suite' ),
			'MX' => __( 'Mexico', 'shahi-legalops-suite' ),
			'AR' => __( 'Argentina', 'shahi-legalops-suite' ),
			'ZA' => __( 'South Africa', 'shahi-legalops-suite' ),
			'AE' => __( 'United Arab Emirates', 'shahi-legalops-suite' ),
			'IL' => __( 'Israel', 'shahi-legalops-suite' ),
			'HK' => __( 'Hong Kong', 'shahi-legalops-suite' ),
			'TW' => __( 'Taiwan', 'shahi-legalops-suite' ),
			'MY' => __( 'Malaysia', 'shahi-legalops-suite' ),
			'TH' => __( 'Thailand', 'shahi-legalops-suite' ),
			'PH' => __( 'Philippines', 'shahi-legalops-suite' ),
			'ID' => __( 'Indonesia', 'shahi-legalops-suite' ),
			'VN' => __( 'Vietnam', 'shahi-legalops-suite' ),
		);
	}

	/**
	 * Get EU/EEA country codes (for GDPR applicability)
	 *
	 * @since 4.1.0
	 * @return array EU/EEA country codes
	 */
	public static function get_eu_countries(): array {
		return array(
			'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR',
			'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL',
			'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE',
			// EEA countries
			'IS', 'LI', 'NO',
		);
	}

	/**
	 * Check if a country is in EU/EEA
	 *
	 * @since 4.1.0
	 * @param string $country_code Country code.
	 * @return bool True if EU/EEA country
	 */
	public static function is_eu_country( string $country_code ): bool {
		return in_array( strtoupper( $country_code ), self::get_eu_countries(), true );
	}
}
