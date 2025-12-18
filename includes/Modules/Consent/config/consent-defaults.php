<?php
/**
 * Consent Module — Default Configuration
 *
 * @package ShahiLegalOpsSuite\Modules\Consent\Config
 * @since 1.0.0
 */

return array(
	'enabled'  => true,
	'version'  => '1.0.0',
	'banner'   => array(
		'template'  => 'top_bar',
		'position'  => 'top',
		'animation' => 'slide',
		'colors'    => array(
			'primary'              => '#1f2937',
			'background'           => '#ffffff',
			'text'                 => '#111827',
			'button_accept'        => '#10b981',
			'button_reject'        => '#ef4444',
			'button_customize'     => '#3b82f6',
		),
		'typography' => array(
			'font_family' => "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
			'font_size'   => '14px',
			'line_height' => '1.5',
		),
		'branding'  => array(
			'logo_url'    => '',
			'logo_alt'    => '',
			'company_name' => '',
		),
		'text'      => array(
			'title'       => __( 'We use cookies', 'shahi-legalops-suite' ),
			'description' => __( 'We use cookies to enhance your experience, analyze site traffic, and serve targeted ads. You can accept all or customize your preferences.', 'shahi-legalops-suite' ),
			'accept_all'  => __( 'Accept All', 'shahi-legalops-suite' ),
			'reject_all'  => __( 'Reject All', 'shahi-legalops-suite' ),
			'customize'   => __( 'Customize', 'shahi-legalops-suite' ),
			'save_prefs'  => __( 'Save Preferences', 'shahi-legalops-suite' ),
		),
		'revisit'   => array(
			'enabled' => true,
			'style'   => 'floating_button',
			'label'   => __( 'Preferences', 'shahi-legalops-suite' ),
		),
	),
	'consent'  => array(
		'categories' => array(
			array(
				'id'          => 'necessary',
				'label'       => __( 'Necessary', 'shahi-legalops-suite' ),
				'description' => __( 'Essential for website functionality', 'shahi-legalops-suite' ),
				'enabled'     => true,
				'required'    => true,
				'services'    => array(
					array(
						'id'          => 'wordpress_core',
						'name'        => __( 'WordPress Core', 'shahi-legalops-suite' ),
						'description' => __( 'Session management, security, authentication', 'shahi-legalops-suite' ),
						'category'    => 'necessary',
					),
				),
			),
			array(
				'id'          => 'functional',
				'label'       => __( 'Functional', 'shahi-legalops-suite' ),
				'description' => __( 'Improve user experience and site features', 'shahi-legalops-suite' ),
				'enabled'     => true,
				'required'    => false,
				'services'    => array(),
			),
			array(
				'id'          => 'analytics',
				'label'       => __( 'Analytics', 'shahi-legalops-suite' ),
				'description' => __( 'Understand how visitors use the site', 'shahi-legalops-suite' ),
				'enabled'     => false,
				'required'    => false,
				'services'    => array(
					array(
						'id'          => 'google_analytics',
						'name'        => __( 'Google Analytics 4', 'shahi-legalops-suite' ),
						'description' => __( 'Web traffic and behavior analysis', 'shahi-legalops-suite' ),
						'category'    => 'analytics',
						'provider_id' => 'google',
					),
				),
			),
			array(
				'id'          => 'marketing',
				'label'       => __( 'Marketing', 'shahi-legalops-suite' ),
				'description' => __( 'Enable personalized advertising and campaigns', 'shahi-legalops-suite' ),
				'enabled'     => false,
				'required'    => false,
				'services'    => array(
					array(
						'id'          => 'facebook_pixel',
						'name'        => __( 'Meta Pixel', 'shahi-legalops-suite' ),
						'description' => __( 'Conversion tracking and audience insights', 'shahi-legalops-suite' ),
						'category'    => 'marketing',
						'provider_id' => 'meta',
					),
					array(
						'id'          => 'google_ads',
						'name'        => __( 'Google Ads', 'shahi-legalops-suite' ),
						'description' => __( 'Ad campaign conversion tracking', 'shahi-legalops-suite' ),
						'category'    => 'marketing',
						'provider_id' => 'google',
					),
				),
			),
		),
		'blocking_rules' => array(
			array(
				'id'       => 'ga_gtag_script',
				'type'     => 'external_script',
				'pattern'  => 'gtag.js',
				'category' => 'analytics',
				'action'   => 'block_until_consent',
			),
			array(
				'id'       => 'fb_pixel_script',
				'type'     => 'external_script',
				'pattern'  => 'pixel.facebook.com',
				'category' => 'marketing',
				'action'   => 'block_until_consent',
			),
			array(
				'id'       => 'youtube_iframe',
				'type'     => 'iframe',
				'pattern'  => 'youtube.com',
				'category' => 'marketing',
				'action'   => 'replace_with_placeholder',
			),
		),
	),
	'geo'      => array(
		'detection' => 'ip_api',
		'regions'   => array(
			'EU'   => array(
				'countries'                 => array( 'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE' ),
				'mode'                      => 'gdpr',
				'prior_consent_required'    => true,
				'cookie_duration_max'       => 12,
				'banner_variant'            => 'gdpr',
			),
			'UK'   => array(
				'countries'                 => array( 'GB' ),
				'mode'                      => 'uk_gdpr',
				'prior_consent_required'    => true,
				'cookie_duration_max'       => 12,
				'banner_variant'            => 'uk',
			),
			'US'   => array(
				'countries'                 => array( 'US' ),
				'mode'                      => 'ccpa',
				'states'                    => array( 'CA' ),
				'prior_consent_required'    => false,
				'banner_variant'            => 'ccpa',
			),
			'BR'   => array(
				'countries'                 => array( 'BR' ),
				'mode'                      => 'lgpd',
				'prior_consent_required'    => true,
				'cookie_duration_max'       => 12,
				'banner_variant'            => 'lgpd',
			),
			'CA'   => array(
				'countries'                 => array( 'CA' ),
				'mode'                      => 'pipeda',
				'prior_consent_required'    => true,
				'cookie_duration_max'       => 12,
				'banner_variant'            => 'ca',
			),
			'DEFAULT' => array(
				'mode'                      => 'default',
				'prior_consent_required'    => false,
				'banner_variant'            => 'default',
			),
		),
	),
	'integrations' => array(
		'google_consent_mode' => array(
			'enabled' => true,
			'version' => 2,
			'emit_on' => 'page_load',
		),
		'wp_consent_api'      => array(
			'enabled' => true,
		),
		'gtm'                 => array(
			'enabled'               => false,
			'gtm_id'                => '',
			'consent_initialization' => true,
		),
		'providers'           => array(
			'google_analytics' => true,
			'facebook'         => true,
			'tiktok'           => false,
			'linkedin'         => false,
		),
	),
	'privacy'      => array(
		'ip_anonymization'    => true,
		'retention_days_eu'   => 365,
		'retention_days_us'   => 365,
		'retention_days_default' => 365,
		'hash_ip'             => true,
		'hash_ua'             => true,
	),
	'logging'      => array(
		'enabled'        => true,
		'level'          => 'info',
		'export_format'  => 'csv',
	),
);
