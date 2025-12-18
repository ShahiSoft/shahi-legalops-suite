<?php
/**
 * Regional Preset Configuration
 *
 * Defines compliance modes, blocking rules, and settings per region.
 *
 * @package ShahiLegalOpsSuite\Modules\Consent\Config
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	/**
	 * European Union (GDPR)
	 *
	 * Applies to all 27 EU member states plus EEA countries (Iceland, Liechtenstein, Norway).
	 * Requires prior-consent blocking for all non-essential tracking.
	 */
	'EU' => array(
		'mode'                 => 'gdpr',
		'label'                => 'European Union (GDPR)',
		'countries'            => array(
			'AT', // Austria
			'BE', // Belgium
			'BG', // Bulgaria
			'HR', // Croatia
			'CY', // Cyprus
			'CZ', // Czech Republic
			'DK', // Denmark
			'EE', // Estonia
			'FI', // Finland
			'FR', // France
			'DE', // Germany
			'GR', // Greece
			'HU', // Hungary
			'IE', // Ireland
			'IT', // Italy
			'LV', // Latvia
			'LT', // Lithuania
			'LU', // Luxembourg
			'MT', // Malta
			'NL', // Netherlands
			'PL', // Poland
			'PT', // Portugal
			'RO', // Romania
			'SK', // Slovakia
			'SI', // Slovenia
			'ES', // Spain
			'SE', // Sweden
			'IS', // Iceland (EEA)
			'LI', // Liechtenstein (EEA)
			'NO', // Norway (EEA)
		),
		'requires_consent'     => true,
		'banner_variant'       => 'gdpr',
		'blocking_rules'       => array(
			'google-analytics-4',
			'google-analytics-universal',
			'facebook-pixel',
			'linkedin-insight',
			'twitter-pixel',
			'hotjar',
			'segment',
		),
		'retention_days'       => 365,
		'retention_policy'     => 'anonymize_after_12mo',
		'anonymize_ip'         => true,
		'categories'           => array(
			'essential',
			'functional',
			'analytics',
			'marketing',
		),
		'default_consents'     => array(
			'essential'   => true,
			'functional'  => false,
			'analytics'   => false,
			'marketing'   => false,
		),
	),

	/**
	 * United Kingdom (UK GDPR)
	 *
	 * Post-Brexit, UK has its own data protection regime but very similar to GDPR.
	 */
	'UK' => array(
		'mode'                 => 'uk_gdpr',
		'label'                => 'United Kingdom (UK GDPR)',
		'countries'            => array( 'GB' ),
		'requires_consent'     => true,
		'banner_variant'       => 'gdpr',
		'blocking_rules'       => array(
			'google-analytics-4',
			'google-analytics-universal',
			'facebook-pixel',
			'linkedin-insight',
			'twitter-pixel',
			'hotjar',
			'segment',
		),
		'retention_days'       => 365,
		'retention_policy'     => 'anonymize_after_12mo',
		'anonymize_ip'         => true,
		'categories'           => array(
			'essential',
			'functional',
			'analytics',
			'marketing',
		),
		'default_consents'     => array(
			'essential'   => true,
			'functional'  => false,
			'analytics'   => false,
			'marketing'   => false,
		),
	),

	/**
	 * California, USA (CCPA / CPRA)
	 *
	 * California Consumer Privacy Act and California Privacy Rights Act.
	 * Opt-out model: scripts load by default, user can disable.
	 */
	'US-CA' => array(
		'mode'                 => 'ccpa',
		'label'                => 'California, USA (CCPA)',
		'states'               => array( 'CA' ),
		'countries'            => array( 'US' ),
		'requires_consent'     => false,
		'banner_variant'       => 'ccpa',
		'blocking_rules'       => array(),
		'retention_days'       => 90,
		'retention_policy'     => 'delete_after_3mo',
		'anonymize_ip'         => true,
		'categories'           => array(
			'essential',
			'analytics',
			'marketing',
		),
		'default_consents'     => array(
			'essential'   => true,
			'analytics'   => true,
			'marketing'   => true,
		),
		'special_feature'      => 'do_not_sell_link',
	),

	/**
	 * Brazil (LGPD)
	 *
	 * Lei Geral de Proteção de Dados (General Data Protection Law).
	 * Similar to GDPR with additional "Legitimate Interest" category.
	 */
	'BR' => array(
		'mode'                 => 'lgpd',
		'label'                => 'Brazil (LGPD)',
		'countries'            => array( 'BR' ),
		'requires_consent'     => true,
		'banner_variant'       => 'gdpr',
		'blocking_rules'       => array(
			'google-analytics-4',
			'facebook-pixel',
			'linkedin-insight',
		),
		'retention_days'       => 180,
		'retention_policy'     => 'anonymize_after_6mo',
		'anonymize_ip'         => true,
		'categories'           => array(
			'essential',
			'functional',
			'analytics',
			'marketing',
		),
		'default_consents'     => array(
			'essential'   => true,
			'functional'  => false,
			'analytics'   => false,
			'marketing'   => false,
		),
	),

	/**
	 * Australia (Privacy Act 1988 & APPs)
	 *
	 * Australian Privacy Principles.
	 */
	'AU' => array(
		'mode'                 => 'privacy_act',
		'label'                => 'Australia (Privacy Act)',
		'countries'            => array( 'AU' ),
		'requires_consent'     => true,
		'banner_variant'       => 'gdpr',
		'blocking_rules'       => array(
			'google-analytics-4',
			'facebook-pixel',
		),
		'retention_days'       => 365,
		'retention_policy'     => 'anonymize_after_12mo',
		'anonymize_ip'         => true,
		'categories'           => array(
			'essential',
			'functional',
			'analytics',
			'marketing',
		),
		'default_consents'     => array(
			'essential'   => true,
			'functional'  => false,
			'analytics'   => false,
			'marketing'   => false,
		),
	),

	/**
	 * Canada (PIPEDA)
	 *
	 * Personal Information Protection and Electronic Documents Act.
	 */
	'CA' => array(
		'mode'                 => 'pipeda',
		'label'                => 'Canada (PIPEDA)',
		'countries'            => array( 'CA' ),
		'requires_consent'     => true,
		'banner_variant'       => 'gdpr',
		'blocking_rules'       => array(
			'google-analytics-4',
			'facebook-pixel',
			'linkedin-insight',
		),
		'retention_days'       => 365,
		'retention_policy'     => 'anonymize_after_12mo',
		'anonymize_ip'         => true,
		'categories'           => array(
			'essential',
			'functional',
			'analytics',
			'marketing',
		),
		'default_consents'     => array(
			'essential'   => true,
			'functional'  => false,
			'analytics'   => false,
			'marketing'   => false,
		),
	),

	/**
	 * South Africa (POPIA)
	 *
	 * Protection of Personal Information Act.
	 */
	'ZA' => array(
		'mode'                 => 'popia',
		'label'                => 'South Africa (POPIA)',
		'countries'            => array( 'ZA' ),
		'requires_consent'     => true,
		'banner_variant'       => 'gdpr',
		'blocking_rules'       => array(
			'google-analytics-4',
			'facebook-pixel',
		),
		'retention_days'       => 365,
		'retention_policy'     => 'anonymize_after_12mo',
		'anonymize_ip'         => true,
		'categories'           => array(
			'essential',
			'functional',
			'analytics',
			'marketing',
		),
		'default_consents'     => array(
			'essential'   => true,
			'functional'  => false,
			'analytics'   => false,
			'marketing'   => false,
		),
	),

	/**
	 * Default (Non-regulated regions)
	 *
	 * Fallback for regions not specifically mapped.
	 */
	'DEFAULT' => array(
		'mode'                 => 'default',
		'label'                => 'Default (Opt-in)',
		'requires_consent'     => false,
		'banner_variant'       => 'default',
		'blocking_rules'       => array(),
		'retention_days'       => 90,
		'retention_policy'     => 'delete_after_3mo',
		'anonymize_ip'         => false,
		'categories'           => array(
			'essential',
			'analytics',
			'marketing',
		),
		'default_consents'     => array(
			'essential'   => true,
			'analytics'   => true,
			'marketing'   => true,
		),
	),
);
