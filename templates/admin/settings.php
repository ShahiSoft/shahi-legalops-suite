<?php
/**
 * Settings Page Template
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Templates/Admin
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap shahi-legalops-suite-admin shahi-settings-page">
    <style>
        /* Force bright, legible tabs even if other styles load late */
        .shahi-settings-page .shahi-tabs-nav {
            background: #0a0e27 !important;
            padding: 20px !important;
            border-radius: 12px !important;
            margin-bottom: 20px !important;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .shahi-settings-page a.shahi-tab-link {
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 14px 24px !important;
            color: #ffffff !important;
            background: #1e2542 !important;
            text-decoration: none !important;
            border-radius: 8px !important;
            font-size: 15px !important;
            font-weight: 600 !important;
            border: 2px solid #2d3561 !important;
            white-space: nowrap;
            transition: all 0.2s ease;
        }

        .shahi-settings-page a.shahi-tab-link .dashicons {
            color: #00d4ff !important;
            font-size: 20px !important;
            width: 20px;
            height: 20px;
        }

        .shahi-settings-page a.shahi-tab-link:hover {
            background: #252d50 !important;
            color: #00d4ff !important;
            border-color: #00d4ff !important;
            box-shadow: 0 4px 12px rgba(0, 212, 255, 0.25) !important;
            transform: translateY(-2px);
        }

        .shahi-settings-page a.shahi-tab-link.active {
            background: linear-gradient(135deg, #00d4ff 0%, #7c3aed 100%) !important;
            color: #ffffff !important;
            border-color: #00d4ff !important;
            box-shadow: 0 6px 20px rgba(0, 212, 255, 0.4), 0 0 40px rgba(124, 58, 237, 0.35) !important;
            transform: translateY(-2px);
        }

        .shahi-settings-page a.shahi-tab-link.active .dashicons {
            color: #ffffff !important;
        }
    </style>
    
    <!-- Page Header -->
    <div class="shahi-page-header">
        <div class="shahi-header-content">
            <h1 class="shahi-page-title">
                <span class="dashicons dashicons-admin-settings"></span>
                <?php echo esc_html__('Settings', 'shahi-legalops-suite'); ?>
            </h1>
            <p class="shahi-page-description">
                <?php echo esc_html__('Configure plugin behavior and preferences', 'shahi-legalops-suite'); ?>
            </p>
        </div>
    </div>

    <!-- Settings Messages -->
    <?php settings_errors('shahi_settings'); ?>

    <!-- Settings Tabs -->
    <div class="shahi-settings-tabs">
        <nav class="shahi-tabs-nav">
            <?php foreach ($tabs as $tab_key => $tab): ?>
                <a href="?page=shahi-legalops-suite-settings&tab=<?php echo esc_attr($tab_key); ?>" 
                   class="shahi-tab-link <?php echo $active_tab === $tab_key ? 'active' : ''; ?>">
                    <span class="dashicons <?php echo esc_attr($tab['icon']); ?>"></span>
                    <?php echo esc_html($tab['title']); ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <!-- Settings Form -->
    <form method="post" action="">
        <?php wp_nonce_field('shahi_save_settings', 'shahi_settings_nonce'); ?>

        <div class="shahi-settings-content">
            
            <?php if ($active_tab === 'general'): ?>
                <!-- General Settings -->
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h2 class="shahi-card-title"><?php echo esc_html__('General Settings', 'shahi-legalops-suite'); ?></h2>
                    </div>
                    <div class="shahi-card-body">
                        <div class="shahi-settings-group">
                            
                            <div class="shahi-setting-row">
                                <label for="plugin_name" class="shahi-setting-label">
                                    <?php echo esc_html__('Plugin Name', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <input type="text" id="plugin_name" name="plugin_name" 
                                           value="<?php echo esc_attr($settings['plugin_name']); ?>" 
                                           class="shahi-input">
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Display name for the plugin in the admin interface.', 'shahi-legalops-suite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Debug Mode', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="enable_debug" value="1" 
                                               <?php checked($settings['enable_debug']); ?>>
                                        <span><?php echo esc_html__('Enable debug logging', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Log detailed information for troubleshooting. Disable in production.', 'shahi-legalops-suite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Uninstall Options', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="delete_data_on_uninstall" value="1" 
                                               <?php checked($settings['delete_data_on_uninstall']); ?>>
                                        <span><?php echo esc_html__('Delete all plugin data on uninstall', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description shahi-text-warning">
                                        <?php echo esc_html__('Warning: This will permanently delete all plugin data when the plugin is uninstalled.', 'shahi-legalops-suite'); ?>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($active_tab === 'analytics'): ?>
                <!-- Analytics Settings -->
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h2 class="shahi-card-title"><?php echo esc_html__('Analytics Settings', 'shahi-legalops-suite'); ?></h2>
                    </div>
                    <div class="shahi-card-body">
                        <div class="shahi-settings-group">
                            
                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Enable Analytics', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="enable_analytics" value="1" 
                                               <?php checked($settings['enable_analytics']); ?>>
                                        <span><?php echo esc_html__('Track plugin events and usage', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('User Tracking', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="track_logged_in_users" value="1" 
                                               <?php checked($settings['track_logged_in_users']); ?>>
                                        <span><?php echo esc_html__('Track logged-in administrators', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label for="analytics_retention_days" class="shahi-setting-label">
                                    <?php echo esc_html__('Data Retention', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <input type="number" id="analytics_retention_days" name="analytics_retention_days" 
                                           value="<?php echo esc_attr($settings['analytics_retention_days']); ?>" 
                                           min="1" max="365" class="shahi-input shahi-input-sm">
                                    <span class="shahi-input-suffix"><?php echo esc_html__('days', 'shahi-legalops-suite'); ?></span>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('How long to keep analytics data before automatic cleanup.', 'shahi-legalops-suite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Privacy', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="anonymize_ip" value="1" 
                                               <?php checked($settings['anonymize_ip']); ?>>
                                        <span><?php echo esc_html__('Anonymize IP addresses for GDPR compliance', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($active_tab === 'notifications'): ?>
                <!-- Notification Settings -->
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h2 class="shahi-card-title"><?php echo esc_html__('Notification Settings', 'shahi-legalops-suite'); ?></h2>
                    </div>
                    <div class="shahi-card-body">
                        <div class="shahi-settings-group">
                            
                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Email Notifications', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="enable_email_notifications" value="1" 
                                               <?php checked($settings['enable_email_notifications']); ?>>
                                        <span><?php echo esc_html__('Enable email notifications', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label for="notification_email" class="shahi-setting-label">
                                    <?php echo esc_html__('Notification Email', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <input type="email" id="notification_email" name="notification_email" 
                                           value="<?php echo esc_attr($settings['notification_email']); ?>" 
                                           class="shahi-input">
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Where to send notification emails.', 'shahi-legalops-suite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Notification Events', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="notify_on_error" value="1" 
                                               <?php checked($settings['notify_on_error']); ?>>
                                        <span><?php echo esc_html__('Notify on errors', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="notify_on_module_change" value="1" 
                                               <?php checked($settings['notify_on_module_change']); ?>>
                                        <span><?php echo esc_html__('Notify on module changes', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($active_tab === 'performance'): ?>
                <!-- Performance Settings -->
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h2 class="shahi-card-title"><?php echo esc_html__('Performance Settings', 'shahi-legalops-suite'); ?></h2>
                    </div>
                    <div class="shahi-card-body">
                        <div class="shahi-settings-group">
                            
                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Caching', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="enable_caching" value="1" 
                                               <?php checked($settings['enable_caching']); ?>>
                                        <span><?php echo esc_html__('Enable query result caching', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label for="cache_duration" class="shahi-setting-label">
                                    <?php echo esc_html__('Cache Duration', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <input type="number" id="cache_duration" name="cache_duration" 
                                           value="<?php echo esc_attr($settings['cache_duration']); ?>" 
                                           min="60" max="86400" class="shahi-input shahi-input-sm">
                                    <span class="shahi-input-suffix"><?php echo esc_html__('seconds', 'shahi-legalops-suite'); ?></span>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('How long to cache query results (60-86400 seconds).', 'shahi-legalops-suite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Asset Optimization', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="enable_minification" value="1" 
                                               <?php checked($settings['enable_minification']); ?>>
                                        <span><?php echo esc_html__('Load minified CSS/JS files', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="lazy_load_assets" value="1" 
                                               <?php checked($settings['lazy_load_assets']); ?>>
                                        <span><?php echo esc_html__('Lazy load page-specific assets', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($active_tab === 'advanced'): ?>
                <!-- Advanced Settings -->
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h2 class="shahi-card-title"><?php echo esc_html__('Advanced Settings', 'shahi-legalops-suite'); ?></h2>
                    </div>
                    <div class="shahi-card-body">
                        <div class="shahi-settings-group">
                            
                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('REST API', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="api_enabled" value="1" 
                                               <?php checked($settings['api_enabled']); ?>>
                                        <span><?php echo esc_html__('Enable REST API endpoints', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label for="api_key" class="shahi-setting-label">
                                    <?php echo esc_html__('API Key', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <div class="shahi-input-group">
                                        <input type="text" id="api_key" name="api_key" 
                                               value="<?php echo esc_attr($settings['api_key']); ?>" 
                                               class="shahi-input" readonly>
                                        <button type="button" class="shahi-btn shahi-btn-secondary" 
                                                onclick="document.getElementById('api_key').value='<?php echo \ShahiLegalopsSuite\Admin\Settings::generate_api_key(); ?>';">
                                            <?php echo esc_html__('Generate New', 'shahi-legalops-suite'); ?>
                                        </button>
                                    </div>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Use this key to authenticate API requests.', 'shahi-legalops-suite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Rate Limiting', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="rate_limit_enabled" value="1" 
                                               <?php checked($settings['rate_limit_enabled']); ?>>
                                        <span><?php echo esc_html__('Enable rate limiting for API requests', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Rate Limit Configuration', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <div class="shahi-inline-group">
                                        <input type="number" name="rate_limit_requests" 
                                               value="<?php echo esc_attr($settings['rate_limit_requests']); ?>" 
                                               min="1" max="1000" class="shahi-input shahi-input-sm">
                                        <span><?php echo esc_html__('requests per', 'shahi-legalops-suite'); ?></span>
                                        <input type="number" name="rate_limit_window" 
                                               value="<?php echo esc_attr($settings['rate_limit_window']); ?>" 
                                               min="1" max="3600" class="shahi-input shahi-input-sm">
                                        <span><?php echo esc_html__('seconds', 'shahi-legalops-suite'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="shahi-setting-row shahi-setting-row-highlight">
                                <label class="shahi-setting-label">
                                    <strong class="shahi-text-danger"><?php echo esc_html__('Onboarding Wizard', 'shahi-legalops-suite'); ?></strong>
                                </label>
                                <div class="shahi-setting-control">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=shahi-legalops-suite-debug-onboarding')); ?>" class="shahi-btn shahi-btn-danger" target="_blank">
                                            <span class="dashicons dashicons-external"></span>
                                            <?php echo esc_html__('Open Debug Onboarding (Force Delete & Restart)', 'shahi-legalops-suite'); ?>
                                        </a>
                                        <p class="shahi-setting-description">
                                            <?php echo esc_html__('Opens the Debug Onboarding page where you can force delete onboarding options and flush caches. This is the same action previously triggered here.', 'shahi-legalops-suite'); ?>
                                        </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($active_tab === 'security'): ?>
                <!-- Security Settings -->
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h2 class="shahi-card-title"><?php echo esc_html__('Security Settings', 'shahi-legalops-suite'); ?></h2>
                        <p class="shahi-card-description"><?php echo esc_html__('Configure security features to protect your site.', 'shahi-legalops-suite'); ?></p>
                    </div>
                    <div class="shahi-card-body">
                        <div class="shahi-settings-group">
                            
                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Enable Rate Limiting', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="enable_rate_limiting" value="1" 
                                               <?php checked($settings['enable_rate_limiting']); ?>>
                                        <span><?php echo esc_html__('Limit the number of requests from a single IP address', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Helps prevent brute force attacks and DDoS attempts.', 'shahi-legalops-suite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label for="ip_blacklist" class="shahi-setting-label">
                                    <?php echo esc_html__('IP Blacklist', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <textarea id="ip_blacklist" name="ip_blacklist" 
                                              class="shahi-textarea" rows="5" 
                                              placeholder="<?php echo esc_attr__('192.168.1.1&#10;10.0.0.1&#10;172.16.0.1', 'shahi-legalops-suite'); ?>"><?php echo esc_textarea($settings['ip_blacklist']); ?></textarea>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Enter one IP address per line. These IPs will be blocked from accessing your site.', 'shahi-legalops-suite'); ?>
                                        <span class="shahi-badge shahi-badge-warning"><?php echo esc_html__('MOCK DATA', 'shahi-legalops-suite'); ?></span>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('File Upload Restrictions', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="file_upload_restrictions" value="1" 
                                               <?php checked($settings['file_upload_restrictions']); ?>>
                                        <span><?php echo esc_html__('Restrict file upload types to safe formats only', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Only allows images, PDFs, and common document formats.', 'shahi-legalops-suite'); ?>
                                        <span class="shahi-badge shahi-badge-warning"><?php echo esc_html__('PLACEHOLDER', 'shahi-legalops-suite'); ?></span>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Two-Factor Authentication', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="two_factor_auth" value="1" 
                                               <?php checked($settings['two_factor_auth']); ?>>
                                        <span><?php echo esc_html__('Require 2FA for admin login', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Adds an extra layer of security to admin accounts.', 'shahi-legalops-suite'); ?>
                                        <span class="shahi-badge shahi-badge-warning"><?php echo esc_html__('PLACEHOLDER', 'shahi-legalops-suite'); ?></span>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Activity Logging', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="activity_logging" value="1" 
                                               <?php checked($settings['activity_logging']); ?>>
                                        <span><?php echo esc_html__('Log all admin actions for security audits', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($active_tab === 'import_export'): ?>
                <!-- Import/Export Settings -->
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h2 class="shahi-card-title"><?php echo esc_html__('Import/Export Settings', 'shahi-legalops-suite'); ?></h2>
                        <p class="shahi-card-description"><?php echo esc_html__('Backup and restore your plugin settings.', 'shahi-legalops-suite'); ?></p>
                    </div>
                    <div class="shahi-card-body">
                        <div class="shahi-settings-group">
                            
                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Export Settings', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <button type="button" id="shahi-export-settings" class="shahi-btn shahi-btn-secondary">
                                        <span class="dashicons dashicons-download"></span>
                                        <?php echo esc_html__('Download Settings (JSON)', 'shahi-legalops-suite'); ?>
                                    </button>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Export all your settings as a JSON file for backup.', 'shahi-legalops-suite'); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label for="shahi-import-file" class="shahi-setting-label">
                                    <?php echo esc_html__('Import Settings', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <input type="file" id="shahi-import-file" accept=".json" class="shahi-file-input">
                                    <button type="button" id="shahi-import-settings" class="shahi-btn shahi-btn-secondary">
                                        <span class="dashicons dashicons-upload"></span>
                                        <?php echo esc_html__('Import Settings (JSON)', 'shahi-legalops-suite'); ?>
                                    </button>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Import settings from a previously exported JSON file.', 'shahi-legalops-suite'); ?>
                                        <span class="shahi-badge shahi-badge-warning"><?php echo esc_html__('REQUIRES AJAX', 'shahi-legalops-suite'); ?></span>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Reset to Defaults', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <button type="button" id="shahi-reset-settings" class="shahi-btn shahi-btn-danger">
                                        <span class="dashicons dashicons-update"></span>
                                        <?php echo esc_html__('Reset All Settings', 'shahi-legalops-suite'); ?>
                                    </button>
                                    <p class="shahi-setting-description shahi-text-danger">
                                        <?php echo esc_html__('Warning: This will reset all settings to their default values. This action cannot be undone!', 'shahi-legalops-suite'); ?>
                                        <span class="shahi-badge shahi-badge-warning"><?php echo esc_html__('REQUIRES AJAX', 'shahi-legalops-suite'); ?></span>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($active_tab === 'uninstall'): ?>
                <!-- Uninstall Settings -->
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h2 class="shahi-card-title"><?php echo esc_html__('Uninstall Options', 'shahi-legalops-suite'); ?></h2>
                        <p class="shahi-card-description"><?php echo esc_html__('Choose what data to preserve when uninstalling the plugin.', 'shahi-legalops-suite'); ?></p>
                    </div>
                    <div class="shahi-card-body">
                        <div class="shahi-settings-group">
                            
                            <div class="shahi-alert shahi-alert-info">
                                <span class="dashicons dashicons-info"></span>
                                <p><?php echo esc_html__('These settings determine what happens to your data when you uninstall the plugin. By default, all data will be removed.', 'shahi-legalops-suite'); ?></p>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Preserve Landing Pages', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="preserve_landing_pages" value="1" 
                                               <?php checked($settings['preserve_landing_pages']); ?>>
                                        <span><?php echo esc_html__('Keep all landing pages created with this plugin', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Preserve Analytics Data', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="preserve_analytics_data" value="1" 
                                               <?php checked($settings['preserve_analytics_data']); ?>>
                                        <span><?php echo esc_html__('Keep all analytics and tracking data', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Preserve Settings', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="preserve_settings" value="1" 
                                               <?php checked($settings['preserve_settings']); ?>>
                                        <span><?php echo esc_html__('Keep plugin settings for future reinstallation', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Preserve User Capabilities', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="preserve_user_capabilities" value="1" 
                                               <?php checked($settings['preserve_user_capabilities']); ?>>
                                        <span><?php echo esc_html__('Keep custom user roles and capabilities', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('Complete Cleanup', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <label class="shahi-checkbox-label">
                                        <input type="checkbox" name="complete_cleanup" value="1" 
                                               <?php checked($settings['complete_cleanup']); ?>>
                                        <span><?php echo esc_html__('Remove ALL plugin data on uninstall (overrides preservation options)', 'shahi-legalops-suite'); ?></span>
                                    </label>
                                    <p class="shahi-setting-description shahi-text-danger">
                                        <?php echo esc_html__('Warning: Enabling this will permanently delete all plugin data, regardless of other preservation settings.', 'shahi-legalops-suite'); ?>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($active_tab === 'license'): ?>
                <!-- License Settings -->
                <div class="shahi-card">
                    <div class="shahi-card-header">
                        <h2 class="shahi-card-title"><?php echo esc_html__('License Activation', 'shahi-legalops-suite'); ?></h2>
                        <p class="shahi-card-description"><?php echo esc_html__('Activate your license to receive updates and premium support.', 'shahi-legalops-suite'); ?></p>
                    </div>
                    <div class="shahi-card-body">
                        <div class="shahi-settings-group">
                            
                            <?php if (!empty($settings['license_key']) && $settings['license_status'] === 'active'): ?>
                                <div class="shahi-alert shahi-alert-success">
                                    <span class="dashicons dashicons-yes-alt"></span>
                                    <p>
                                        <?php echo esc_html__('Your license is active!', 'shahi-legalops-suite'); ?>
                                        <?php if (!empty($settings['license_expires'])): ?>
                                            <?php echo sprintf(
                                                esc_html__('Expires: %s', 'shahi-legalops-suite'),
                                                esc_html($settings['license_expires'])
                                            ); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <div class="shahi-setting-row">
                                <label for="license_key" class="shahi-setting-label">
                                    <?php echo esc_html__('License Key', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <div class="shahi-input-group">
                                        <input type="text" id="license_key" name="license_key" 
                                               value="<?php echo esc_attr($settings['license_key']); ?>" 
                                               class="shahi-input" 
                                               placeholder="<?php echo esc_attr__('XXXX-XXXX-XXXX-XXXX', 'shahi-legalops-suite'); ?>">
                                        <button type="button" id="shahi-activate-license" class="shahi-btn shahi-btn-primary">
                                            <?php echo esc_html__('Activate', 'shahi-legalops-suite'); ?>
                                        </button>
                                    </div>
                                    <p class="shahi-setting-description">
                                        <?php echo esc_html__('Enter your license key to activate premium features.', 'shahi-legalops-suite'); ?>
                                        <span class="shahi-badge shahi-badge-warning"><?php echo esc_html__('PLACEHOLDER - LICENSE SYSTEM NOT IMPLEMENTED', 'shahi-legalops-suite'); ?></span>
                                    </p>
                                </div>
                            </div>

                            <div class="shahi-setting-row">
                                <label class="shahi-setting-label">
                                    <?php echo esc_html__('License Status', 'shahi-legalops-suite'); ?>
                                </label>
                                <div class="shahi-setting-control">
                                    <span class="shahi-badge <?php echo $settings['license_status'] === 'active' ? 'shahi-badge-success' : 'shahi-badge-secondary'; ?>">
                                        <?php echo esc_html(ucfirst($settings['license_status'])); ?>
                                    </span>
                                </div>
                            </div>

                            <?php if ($settings['license_status'] === 'active'): ?>
                                <div class="shahi-setting-row">
                                    <label class="shahi-setting-label"></label>
                                    <div class="shahi-setting-control">
                                        <button type="button" id="shahi-deactivate-license" class="shahi-btn shahi-btn-secondary">
                                            <?php echo esc_html__('Deactivate License', 'shahi-legalops-suite'); ?>
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- Form Footer -->
        <div class="shahi-form-footer">
            <button type="submit" name="shahi_save_settings" class="shahi-btn shahi-btn-primary shahi-btn-lg">
                <span class="dashicons dashicons-saved"></span>
                <?php echo esc_html__('Save Settings', 'shahi-legalops-suite'); ?>
            </button>
        </div>

    </form>

</div>
