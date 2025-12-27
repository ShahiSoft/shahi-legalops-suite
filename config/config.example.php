<?php
/**
 * Plugin Configuration Template
 * 
 * PLACEHOLDER FILE - This is a template for plugin configuration.
 * Copy this file to config.php and customize it.
 * 
 * Instructions:
 * 1. Copy this file to config/config.php
 * 2. Replace all PLACEHOLDER values with your actual settings
 * 3. Import this configuration in your main plugin file
 * 4. Never commit sensitive data to version control
 * 
 * @package    {PluginNamespace}
 * @subpackage Config
 * @since      1.0.0
 */

// PLACEHOLDER: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

return [
    
    /*
    |--------------------------------------------------------------------------
    | Plugin Information
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER: Basic plugin information and metadata
    |
    */
    
    'plugin_name'        => 'ShahiTemplate', // PLACEHOLDER: Your plugin name
    'plugin_slug'        => 'shahi-template', // PLACEHOLDER: Your plugin slug
    'plugin_version'     => '1.0.0', // PLACEHOLDER: Current version
    'plugin_author'      => 'Your Name', // PLACEHOLDER: Author name
    'plugin_author_uri'  => 'https://example.com', // PLACEHOLDER: Author website
    'plugin_uri'         => 'https://example.com/plugin', // PLACEHOLDER: Plugin website
    'text_domain'        => 'shahi-template', // PLACEHOLDER: Text domain for translations
    'domain_path'        => '/languages', // Translation files path
    
    /*
    |--------------------------------------------------------------------------
    | Environment Settings
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER: Environment-specific configuration
    |
    */
    
    'environment'        => 'development', // PLACEHOLDER: development, staging, production
    'debug'              => true, // PLACEHOLDER: Set to false in production
    'debug_log'          => true, // PLACEHOLDER: Enable error logging
    
    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER: Custom database tables and prefixes
    |
    */
    
    'database' => [
        'tables' => [
            'analytics' => 'shahi_analytics', // PLACEHOLDER: Table name without prefix
            'modules'   => 'shahi_modules',
            'settings'  => 'shahi_settings',
            'cache'     => 'shahi_cache',
        ],
        'version' => '1.0', // Database schema version
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER: Enable or disable plugin features
    |
    */
    
    'features' => [
        'analytics'      => true, // PLACEHOLDER: Enable analytics module
        'cache'          => true, // PLACEHOLDER: Enable caching
        'api'            => true, // PLACEHOLDER: Enable REST API endpoints
        'dashboard'      => true, // PLACEHOLDER: Enable custom dashboard
        'modules'        => true, // PLACEHOLDER: Enable module system
        'onboarding'     => true, // PLACEHOLDER: Enable onboarding wizard
        'notifications'  => true, // PLACEHOLDER: Enable notifications
        'security'       => true, // PLACEHOLDER: Enable security features
        'optimization'   => true, // PLACEHOLDER: Enable performance optimization
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Module Configuration
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER: Settings for individual modules
    |
    */
    
    'modules' => [
        'autoload'      => true, // PLACEHOLDER: Auto-load modules on init
        'directory'     => 'includes/modules', // Modules directory path
        'default_enabled' => [ // PLACEHOLDER: Modules enabled by default
            'analytics',
            'security',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER: REST API settings
    |
    */
    
    'api' => [
        'namespace'     => 'shahi-template/v1', // PLACEHOLDER: API namespace
        'rate_limit'    => 60, // Requests per minute
        'cache_enabled' => true,
        'cache_duration' => 300, // Cache duration in seconds
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER: Caching settings
    |
    */
    
    'cache' => [
        'enabled'       => true, // PLACEHOLDER: Enable caching
        'driver'        => 'transient', // PLACEHOLDER: transient, file, redis, memcached
        'prefix'        => 'shahi_cache_', // Cache key prefix
        'default_ttl'   => 3600, // Default cache duration (1 hour)
        'groups'        => [
            'analytics' => 1800, // 30 minutes
            'settings'  => 86400, // 24 hours
            'api'       => 300, // 5 minutes
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Admin Configuration
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER: Admin area settings
    |
    */
    
    'admin' => [
        'menu_position'     => 6, // PLACEHOLDER: Admin menu position
        'menu_icon'         => 'dashicons-admin-generic', // PLACEHOLDER: Dashicons class
        'capability'        => 'manage_options', // Required capability
        'show_in_menu'      => true,
        'show_in_admin_bar' => true,
        'page_slug'         => 'shahi-template', // Main admin page slug
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Assets Configuration
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER: Frontend and admin assets settings
    |
    */
    
    'assets' => [
        'version'           => '1.0.0', // PLACEHOLDER: Asset version for cache busting
        'minified'          => false, // PLACEHOLDER: Load minified assets (true in production)
        'combine'           => false, // PLACEHOLDER: Combine CSS/JS files
        'enqueue_globally'  => false, // PLACEHOLDER: Load assets on all pages
        'cdn_enabled'       => false, // PLACEHOLDER: Use CDN for assets
        'cdn_url'           => '', // PLACEHOLDER: CDN URL
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER: Security and permissions settings
    |
    */
    
    'security' => [
        'nonce_life'        => 86400, // Nonce lifetime (24 hours)
        'enforce_ssl'       => false, // PLACEHOLDER: Require HTTPS
        'disable_file_edit' => true, // PLACEHOLDER: Disable file editing in admin
        'ip_whitelist'      => [], // PLACEHOLDER: Whitelisted IP addresses
        'rate_limiting'     => true,
        'csrf_protection'   => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER: Error and event logging settings
    |
    */
    
    'logging' => [
        'enabled'       => true, // PLACEHOLDER: Enable logging
        'level'         => 'debug', // PLACEHOLDER: debug, info, warning, error
        'channel'       => 'file', // PLACEHOLDER: file, database, external
        'path'          => WP_CONTENT_DIR . '/uploads/shahi-logs', // Log file path
        'max_files'     => 7, // Keep logs for 7 days
        'log_queries'   => false, // PLACEHOLDER: Log database queries
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Email Configuration
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER: Email notification settings
    |
    */
    
    'email' => [
        'from_name'     => 'ShahiTemplate', // PLACEHOLDER: From name
        'from_email'    => 'noreply@example.com', // PLACEHOLDER: From email
        'service'       => 'default', // PLACEHOLDER: default, smtp, sendgrid, mailgun
        'templates_dir' => 'templates/emails', // Email templates directory
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Notification Configuration
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER: User notification settings
    |
    */
    
    'notifications' => [
        'enabled'       => true,
        'channels'      => ['in_app', 'email'], // PLACEHOLDER: in_app, email, sms
        'frequency'     => 'realtime', // PLACEHOLDER: realtime, daily, weekly
        'digest_time'   => '09:00', // Time for digest notifications
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER: Performance optimization settings
    |
    */
    
    'performance' => [
        'lazy_load_modules' => true, // PLACEHOLDER: Lazy load modules
        'async_processing'  => true, // PLACEHOLDER: Use async processing
        'query_optimization' => true,
        'object_cache'      => false, // PLACEHOLDER: Use object caching
        'page_cache'        => false, // PLACEHOLDER: Enable page caching
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cron Jobs Configuration
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER: Scheduled tasks settings
    |
    */
    
    'cron' => [
        'enabled'       => true,
        'jobs'          => [
            'cleanup'       => 'daily', // PLACEHOLDER: Run cleanup daily
            'analytics'     => 'hourly', // PLACEHOLDER: Update analytics hourly
            'cache_refresh' => 'twicedaily',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Third-Party Integrations
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER: External service configurations
    |
    */
    
    'integrations' => [
        'google_analytics' => [
            'enabled'       => false,
            'tracking_id'   => '', // PLACEHOLDER: GA tracking ID
        ],
        'stripe' => [
            'enabled'       => false,
            'public_key'    => '', // PLACEHOLDER: Stripe public key
            'secret_key'    => '', // PLACEHOLDER: Stripe secret key
            'webhook_secret' => '',
        ],
        'mailchimp' => [
            'enabled'       => false,
            'api_key'       => '', // PLACEHOLDER: Mailchimp API key
            'list_id'       => '',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Customization Options
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER: UI customization and branding
    |
    */
    
    'customization' => [
        'colors' => [
            'primary'           => '#00d4ff', // PLACEHOLDER: Primary color
            'secondary'         => '#7000ff', // PLACEHOLDER: Secondary color
            'accent'            => '#00ff88', // PLACEHOLDER: Accent color
            'background_dark'   => '#0a0a12',
            'background_light'  => '#1a1a2e',
        ],
        'logo'      => '', // PLACEHOLDER: Custom logo URL
        'favicon'   => '', // PLACEHOLDER: Custom favicon URL
        'css_file'  => '', // PLACEHOLDER: Custom CSS file path
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Advanced Options
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER: Advanced developer options
    |
    */
    
    'advanced' => [
        'autoloader'        => true,
        'namespace'         => 'ShahiTemplate', // PLACEHOLDER: PHP namespace
        'prefix'            => 'shahi_', // PLACEHOLDER: Function prefix
        'constant_prefix'   => 'SHAHI_', // PLACEHOLDER: Constant prefix
        'ajax_prefix'       => 'shahi_ajax_',
        'hook_prefix'       => 'shahi_',
    ],
    
];

