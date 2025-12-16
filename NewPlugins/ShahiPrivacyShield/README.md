# ShahiPrivacyShield - GDPR/CCPA Compliance Plugin

**Demo Plugin Built from ShahiTemplate**

A comprehensive WordPress plugin for real-time GDPR/CCPA compliance scanning and consent management, demonstrating advanced features of the ShahiTemplate framework.

---

## Overview

ShahiPrivacyShield is a fully functional example plugin built using ShahiTemplate that helps WordPress site owners maintain GDPR and CCPA compliance through:

- **Real-time Compliance Scanning**: Automated checks for privacy policy, consent mechanisms, data retention, and more
- **Consent Management**: User-friendly cookie consent banner with granular control
- **Privacy Dashboard**: Comprehensive admin interface for monitoring compliance status
- **Data Subject Rights**: Integration with WordPress's native data export and erasure tools

---

## Features Demonstrated

### From ShahiTemplate Architecture

✅ **Modular Structure**: Compliance Scanner and Consent Manager as separate modules  
✅ **PSR-4 Autoloading**: Clean namespace organization  
✅ **Database Operations**: Custom tables for consent logs and scan results  
✅ **Admin Interface**: WordPress admin menu integration with custom views  
✅ **Frontend Assets**: Enqueued CSS/JS with localization  
✅ **AJAX Integration**: Real-time scanning and consent saving  
✅ **WordPress Standards**: Follows WordPress Coding Standards and best practices  

### Privacy & Compliance Features

✅ **GDPR Compliance Checks**:
- Privacy policy page validation
- Consent mechanism verification
- Data retention policy checks
- Right to erasure implementation
- Data export capability

✅ **CCPA Compliance Checks**:
- "Do Not Sell" option verification
- California residents rights disclosure
- Data collection category disclosure

✅ **Cookie Scanning**:
- Detection of tracking plugins
- Cookie consent banner status
- Third-party cookie identification

✅ **Consent Management**:
- Granular consent categories (Necessary, Analytics, Marketing, Preferences)
- Consent logging with IP and user agent
- Cookie-based consent storage
- User consent revocation

---

## Installation

### As a Standalone Plugin

1. Copy the `ShahiPrivacyShield` directory to `wp-content/plugins/`
2. Activate via WordPress admin dashboard
3. Navigate to **Privacy Shield** menu in admin

### As a Reference/Example

Study the code structure to understand how ShahiTemplate can be used to build complex WordPress plugins.

---

## File Structure

```
ShahiPrivacyShield/
├── shahi-privacy-shield.php        # Main plugin file
├── includes/
│   ├── Plugin.php                  # Main plugin class
│   └── modules/
│       ├── ComplianceScanner.php   # Compliance scanning module
│       └── ConsentManager.php      # Consent management module
├── admin/
│   └── views/
│       └── dashboard.php           # Admin dashboard view
├── public/
│   ├── css/
│   │   └── consent-banner.css      # Consent banner styles
│   ├── js/
│   │   └── consent-banner.js       # Consent banner JavaScript
│   └── views/
│       └── consent-banner.php      # Consent banner template
└── README.md                       # This file
```

---

## Database Schema

### Consent Logs Table
```sql
wp_privacy_shield_consents
- id (bigint)
- user_id (bigint, nullable)
- ip_address (varchar)
- consent_type (varchar)
- consent_given (tinyint)
- consent_data (longtext)
- user_agent (text)
- created_at (datetime)
- updated_at (datetime)
```

### Compliance Scans Table
```sql
wp_privacy_shield_scans
- id (bigint)
- scan_type (varchar)
- scan_status (varchar)
- issues_found (int)
- scan_results (longtext)
- started_at (datetime)
- completed_at (datetime)
```

---

## Usage

### Running Compliance Scans

1. Navigate to **Privacy Shield > Compliance Scan** in admin
2. Click **Run New Scan**
3. Review scan results showing:
   - Critical issues
   - Warnings
   - Passed checks
4. Address any compliance issues identified

### Managing Consents

1. Navigate to **Privacy Shield > Consent Management**
2. View consent statistics:
   - Total consents collected
   - Breakdown by category
   - Recent activity
3. Export consent data if needed

### Frontend Consent Banner

The consent banner automatically displays to users who haven't consented:

- **Accept All**: Enables all cookie categories
- **Accept Selected**: Enables only checked categories
- **Reject All**: Disables optional cookies (keeps necessary)

Consents are stored in:
- Database (for logged-in users)
- Cookie (for all users, 1-year expiration)
- LocalStorage (for quick client-side checks)

---

## Code Examples

### Using Compliance Scanner

```php
$scanner = new \ShahiPrivacyShield\Modules\ComplianceScanner();

// Run full compliance scan
$results = $scanner->run_scan('full');

// Run specific scan types
$gdpr_results = $scanner->run_scan('gdpr');
$ccpa_results = $scanner->run_scan('ccpa');
$cookie_results = $scanner->run_scan('cookies');

// Get latest scan results
$latest_scan = $scanner->get_latest_scan();
```

### Using Consent Manager

```php
$consent_manager = new \ShahiPrivacyShield\Modules\ConsentManager();

// Check if user has consented
$has_analytics = $consent_manager->has_consent_for('analytics');

// Get all user consents
$consents = $consent_manager->get_user_consent();

// Get consent statistics
$stats = $consent_manager->get_consent_statistics();

// Revoke user consent
$consent_manager->revoke_consent();
```

---

## AJAX Endpoints

### Save Consent
```javascript
// Endpoint: shahi_privacy_shield_save_consent
// Nonce: shahiPrivacyShieldConsent.nonce

jQuery.ajax({
    url: shahiPrivacyShieldConsent.ajaxUrl,
    type: 'POST',
    data: {
        action: 'shahi_privacy_shield_save_consent',
        nonce: shahiPrivacyShieldConsent.nonce,
        consents: {
            necessary: true,
            analytics: true,
            marketing: false,
            preferences: true
        }
    }
});
```

### Run Compliance Scan
```javascript
// Endpoint: shahi_privacy_shield_run_scan
// Nonce: shahiPrivacyShield.nonce

jQuery.ajax({
    url: shahiPrivacyShield.ajaxUrl,
    type: 'POST',
    data: {
        action: 'shahi_privacy_shield_run_scan',
        nonce: shahiPrivacyShield.nonce,
        scan_type: 'full' // or 'gdpr', 'ccpa', 'cookies'
    }
});
```

---

## Customization

### Adding Custom Compliance Checks

Edit `includes/modules/ComplianceScanner.php`:

```php
private function scan_custom_compliance() {
    // Your custom compliance logic
    
    if ($issue_found) {
        $this->add_issue('custom_check', 'Issue description', 'high');
    } else {
        $this->add_passed_check('Custom check passed');
    }
}
```

### Adding Consent Categories

Edit `includes/modules/ConsentManager.php`:

```php
private $consent_types = array(
    'necessary'   => 'Necessary Cookies',
    'analytics'   => 'Analytics & Performance',
    'marketing'   => 'Marketing & Advertising',
    'preferences' => 'Preferences',
    'custom'      => 'Custom Category', // Add your category
);
```

---

## Built With ShahiTemplate

This plugin demonstrates:

- How to use ShahiTemplate's modular architecture
- Database table creation and management
- Admin interface development
- Frontend integration
- AJAX handling
- Asset enqueueing
- WordPress hooks and filters

**Learn More**: [ShahiTemplate Repository](https://github.com/shahitemplate/shahi-template)

---

## Requirements

- WordPress 5.8+
- PHP 7.4+
- MySQL 5.6+

---

## License

GPL v2 or later

---

## Support

This is a **demo/example plugin** for educational purposes. For production use:

1. Review all compliance checks for your specific jurisdiction
2. Consult with legal counsel
3. Customize checks based on your data processing activities
4. Add additional compliance features as needed

**Note**: This plugin provides tools for compliance monitoring but does not guarantee legal compliance. Always consult with privacy/legal professionals for your specific situation.

---

## Credits

Built from **ShahiTemplate** - Enterprise WordPress Plugin Base Template

Demonstrates best practices for:
- Plugin architecture
- Code organization
- WordPress integration
- User experience
- Security implementation

---

## Version

**Version**: 1.0.0  
**Built**: December 2024  
**Template**: ShahiTemplate v1.0.0
