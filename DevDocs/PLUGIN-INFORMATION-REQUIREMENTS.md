# Plugin Information Requirements

**Document Purpose**: Comprehensive list of all information required to configure a WordPress plugin from the ShahiTemplate base template.

**Created**: 2025-12-14  
**For**: Phase 6, Task 6.2 - Setup & Scaffolding Scripts

---

## 1. Core Plugin Metadata

### 1.1 Basic Identification
- **Plugin Name** (Display)
  - Example: "My Awesome Plugin"
  - Used in: Admin UI, plugin headers, titles
  - Format: Human-readable title case
  - Character limit: 50 characters recommended

- **Plugin Slug** (Technical)
  - Example: "my-awesome-plugin"
  - Used in: File names, URLs, option names, database tables
  - Format: lowercase, hyphens only, no spaces
  - Character limit: 30 characters max
  - Must be unique in WordPress ecosystem

- **Plugin Description** (Short)
  - Example: "A powerful tool for managing your website efficiently"
  - Used in: Plugin header, readme.txt, marketing
  - Character limit: 150 characters

- **Plugin Description** (Long)
  - Example: Detailed multi-paragraph description
  - Used in: README.md, WordPress.org description
  - Character limit: 1000+ characters

### 1.2 Versioning
- **Initial Version Number**
  - Default: "1.0.0"
  - Format: Semantic versioning (MAJOR.MINOR.PATCH)
  - Used in: Plugin header, constants, package.json

- **Minimum WordPress Version**
  - Default: "5.8"
  - Used in: Plugin header, compatibility checks
  - Format: "X.Y" or "X.Y.Z"

- **Minimum PHP Version**
  - Default: "7.4"
  - Recommended: "8.0"
  - Used in: Plugin header, compatibility checks
  - Format: "X.Y"

- **Tested Up To WordPress Version**
  - Example: "6.4"
  - Used in: Plugin header, WordPress.org listing

### 1.3 Classification
- **Plugin Category**
  - Options: SEO, Security, E-commerce, Social, Analytics, Utilities, etc.
  - Used in: Marketing, organization, filtering

- **Plugin Tags/Keywords**
  - Example: "analytics, tracking, reports, dashboard"
  - Used in: WordPress.org search, SEO
  - Limit: 5-10 keywords

---

## 2. Author & Organization Information

### 2.1 Author Details
- **Author Name** (Individual or Company)
  - Example: "John Doe" or "Acme Corporation"
  - Used in: Plugin header, credits, copyright

- **Author Email**
  - Example: "john@example.com"
  - Used in: Support contact, plugin header (optional)
  - Format: Valid email address

- **Author URL/Website**
  - Example: "https://example.com"
  - Used in: Plugin header, credits, branding
  - Format: Full URL with https://

- **Author Username** (WordPress.org)
  - Example: "johndoe"
  - Used in: WordPress.org profile linking
  - Format: Alphanumeric, no spaces

### 2.2 Company/Organization (if applicable)
- **Company Legal Name**
  - Used in: License agreements, legal documents

- **Company Address**
  - Used in: Legal compliance, privacy policies

- **Support Email**
  - Example: "support@example.com"
  - Used in: User support, admin notices

- **Sales/Commercial Email**
  - Example: "sales@example.com"
  - Used in: Premium version inquiries

---

## 3. Technical Configuration

### 3.1 Namespace & Prefixes
- **PHP Namespace**
  - Example: "MyAwesomePlugin"
  - Used in: All PHP classes
  - Format: PascalCase, no spaces, alphanumeric only
  - Requirements: Must start with letter, no numbers at start

- **Function Prefix**
  - Example: "map_" (my_awesome_plugin → map)
  - Used in: Global functions, to avoid conflicts
  - Format: lowercase, underscore suffix
  - Length: 3-6 characters recommended

- **Constant Prefix**
  - Example: "MAP_" or "MY_AWESOME_PLUGIN_"
  - Used in: Global constants
  - Format: UPPERCASE, underscore suffix

- **Database Table Prefix** (beyond wp_)
  - Example: "map_" (results in wp_map_tablename)
  - Used in: Custom database tables
  - Format: lowercase, underscore suffix

- **Option/Meta Key Prefix**
  - Example: "map_" or "my_awesome_plugin_"
  - Used in: wp_options, post meta, user meta
  - Format: lowercase, underscore suffix

- **CSS Class Prefix**
  - Example: "map-" or "my-awesome-plugin-"
  - Used in: CSS classes, HTML elements
  - Format: lowercase, hyphen suffix

- **JavaScript Object Name**
  - Example: "MapPlugin" or "MyAwesomePlugin"
  - Used in: Global JS objects
  - Format: PascalCase

### 3.2 Text Domain & Localization
- **Text Domain**
  - Example: "my-awesome-plugin"
  - Used in: Translation functions (__(), _e(), etc.)
  - Format: Same as plugin slug (lowercase, hyphens)
  - Must match: Main plugin file header

- **Domain Path**
  - Default: "/languages"
  - Used in: Plugin header, translation loading

- **Default Language**
  - Default: "en_US"
  - Used in: POT file generation

### 3.3 File Names
- **Main Plugin File Name**
  - Example: "my-awesome-plugin.php"
  - Current: "shahi-template.php"
  - Format: Same as plugin slug + .php
  - Requirements: Must match plugin slug

- **Main Plugin Directory Name**
  - Example: "MyAwesomePlugin" or "my-awesome-plugin"
  - Current: "ShahiTemplate"
  - Used in: Installation path
  - Note: Can differ from slug for capitalization

---

## 4. Branding & Design

### 4.1 Visual Identity
- **Brand Colors** (Hex codes)
  - Primary Color: e.g., "#00d4ff"
  - Secondary Color: e.g., "#7000ff"
  - Accent Color: e.g., "#00ff88"
  - Background Dark: e.g., "#0a0a12"
  - Background Light: e.g., "#1a1a2e"
  - Text Primary: e.g., "#ffffff"
  - Success: e.g., "#00ff88"
  - Warning: e.g., "#ffaa00"
  - Error: e.g., "#ff3366"

- **Logo File**
  - Format: PNG with transparency
  - Recommended sizes:
    - Standard: 200x50px (horizontal)
    - Square icon: 256x256px
    - Icon: 128x128px

- **Favicon/Icon**
  - Format: ICO or PNG
  - Size: 128x128px minimum

- **Plugin Banner** (WordPress.org)
  - Size: 772x250px (standard)
  - Size: 1544x500px (retina)
  - Format: JPG or PNG

### 4.2 Admin UI Elements
- **Admin Menu Icon**
  - Options: Dashicons class (e.g., "dashicons-shield")
  - Or: Custom SVG icon
  - Or: Image URL

- **Menu Position**
  - Range: 1-100
  - Common positions:
    - 5: Below Posts
    - 10: Below Media
    - 20: Below Pages
    - 25: Below Comments
  - Default: 6

---

## 5. Repository & Distribution

### 5.1 Version Control
- **Git Repository URL**
  - Example: "https://github.com/username/my-awesome-plugin"
  - Used in: package.json, composer.json, documentation

- **Repository Type**
  - Options: GitHub, GitLab, Bitbucket, Private
  - Used in: CI/CD, issue tracking

- **Branch Strategy**
  - Main branch: "main" or "master"
  - Development branch: "develop"
  - Used in: Git configuration

### 5.2 Package Managers
- **Composer Package Name**
  - Example: "vendor/my-awesome-plugin"
  - Format: vendor/package (lowercase)
  - Used in: composer.json

- **NPM Package Name** (if applicable)
  - Example: "@vendor/my-awesome-plugin"
  - Format: Scoped or unscoped
  - Used in: package.json

### 5.3 Distribution Channels
- **WordPress.org Hosting**
  - Yes/No: Will this be on WordPress.org?
  - Affects: Naming, readme.txt requirements

- **Commercial/Premium**
  - Yes/No: Is this a paid plugin?
  - Affects: License system, update mechanisms

- **Update Server URL** (if self-hosted)
  - Example: "https://updates.example.com/api/v1/"
  - Used in: Update checker

---

## 6. Legal & Licensing

### 6.1 License Information
- **License Type**
  - Default: GPL v3 or later
  - Options: GPL v2, GPL v3, MIT, Commercial
  - Used in: Plugin header, LICENSE.txt

- **License URI**
  - Example: "https://www.gnu.org/licenses/gpl-3.0.html"
  - Used in: Plugin header

### 6.2 Copyright
- **Copyright Year**
  - Example: "2025" or "2024-2025"
  - Used in: License files, headers

- **Copyright Holder**
  - Example: "John Doe" or "Acme Corporation"
  - Used in: License files, headers

### 6.3 Privacy & Legal
- **Privacy Policy URL**
  - Example: "https://example.com/privacy"
  - Used in: Settings pages, compliance

- **Terms of Service URL**
  - Example: "https://example.com/terms"
  - Used in: Premium versions

---

## 7. API & External Services

### 7.1 REST API Configuration
- **API Namespace**
  - Example: "my-awesome-plugin/v1"
  - Current: "shahi-template/v1"
  - Format: plugin-slug/v1
  - Used in: REST endpoint registration

- **API Version**
  - Default: "v1"
  - Used in: REST API namespace

### 7.2 External Integrations
- **API Keys** (if needed)
  - List of required API services
  - Example: Google Analytics, Stripe, Mailchimp
  - Used in: Settings configuration

- **Webhook URLs** (if applicable)
  - Used in: External service callbacks

### 7.3 CDN & Assets
- **CDN URL** (if using)
  - Example: "https://cdn.example.com/plugin-assets/"
  - Used in: Asset loading

---

## 8. Database & Storage

### 8.1 Database Tables
- **Custom Table Names**
  - Example: "analytics", "logs", "cache"
  - Will be prefixed: wp_{plugin_prefix}_tablename
  - Used in: Database creation, queries

- **Table Schema Version**
  - Example: "1.0.0"
  - Used in: Migration tracking

### 8.2 WordPress Options
- **Main Settings Option Name**
  - Example: "my_awesome_plugin_settings"
  - Used in: get_option(), update_option()

- **Activation Timestamp Option**
  - Example: "my_awesome_plugin_activated_at"
  - Used in: Activation tracking

### 8.3 File Storage
- **Upload Directory Name**
  - Example: "my-awesome-plugin"
  - Path: wp-content/uploads/my-awesome-plugin/
  - Used in: File uploads, cache

---

## 9. Features & Modules

### 9.1 Core Features
- **Feature List**
  - List all primary features to enable by default
  - Example: Analytics, Dashboard, Settings, etc.

- **Optional Modules**
  - List modules that should be opt-in
  - Example: Email notifications, API access, etc.

### 9.2 Admin Capabilities
- **Required Capability**
  - Default: "manage_options"
  - Options: "edit_posts", custom capability
  - Used in: Admin menu, permission checks

- **Custom Capabilities** (if any)
  - Example: "manage_plugin_settings", "view_plugin_reports"
  - Used in: Fine-grained permissions

---

## 10. Support & Documentation

### 10.1 Support Channels
- **Documentation URL**
  - Example: "https://docs.example.com"
  - Used in: Help links, support pages

- **Support Forum URL**
  - Example: "https://forum.example.com"
  - Or WordPress.org forum URL

- **Issue Tracker URL**
  - Example: "https://github.com/user/plugin/issues"
  - Used in: Bug reporting

### 10.2 Community
- **Twitter/X Handle**
  - Example: "@myplugin"
  - Used in: Social sharing, branding

- **Facebook Page**
  - Used in: Social integration

- **YouTube Channel** (for tutorials)
  - Used in: Video documentation

---

## 11. Development Environment

### 11.1 Development Tools
- **Node.js Version**
  - Example: ">=18.0.0"
  - Used in: package.json engines

- **PHP Version** (development)
  - Example: "8.2"
  - Used in: Development environment

- **Composer Version**
  - Example: "^2.0"
  - Used in: Development dependencies

### 11.2 Build Configuration
- **Asset Build Tool**
  - Options: Webpack, Vite, Gulp, none
  - Used in: Build scripts

- **CSS Preprocessor**
  - Options: None (vanilla CSS), SASS, LESS
  - Current: Vanilla CSS with variables

### 11.3 Testing
- **Testing Framework**
  - Options: PHPUnit, Codeception, none
  - Used in: Automated testing

- **Code Quality Tools**
  - Options: PHPCS, PHPStan, ESLint
  - Used in: Code standards enforcement

---

## 12. Security & Performance

### 12.1 Security Settings
- **Nonce Action Prefix**
  - Example: "map_" or "my_awesome_plugin_"
  - Used in: wp_create_nonce() calls

- **Security Salt/Key** (if needed for encryption)
  - Used in: Data encryption, secure tokens

### 12.2 Performance
- **Cache Duration** (default)
  - Example: 3600 (1 hour)
  - Used in: Transient caching

- **Asset Version**
  - Options: Plugin version or timestamp
  - Used in: Cache busting

---

## 13. Onboarding & UX

### 13.1 Setup Wizard
- **Wizard Steps**
  - List of onboarding steps to include
  - Example: Welcome, Configuration, Modules, Complete

- **Default Settings**
  - Initial configuration values
  - Feature toggles (enabled/disabled)

### 13.2 Admin Notices
- **Welcome Message**
  - Text shown on first activation
  - Can include setup instructions

---

## Summary: Minimum Required Information

For a basic setup script, these are **absolutely essential**:

### Tier 1: Critical (Must Have)
1. ✅ Plugin Name (display)
2. ✅ Plugin Slug (technical)
3. ✅ PHP Namespace
4. ✅ Text Domain
5. ✅ Author Name
6. ✅ Author Email
7. ✅ Author URL
8. ✅ Plugin Description
9. ✅ Initial Version

### Tier 2: Important (Should Have)
10. Function/Constant Prefix
11. API Namespace
12. Brand Colors (primary, secondary)
13. Admin Menu Icon
14. License Type
15. Minimum WP/PHP versions

### Tier 3: Optional (Nice to Have)
16. Repository URL
17. Company Information
18. Custom capabilities
19. External API keys
20. Social media handles

---

## Template Replacement Map

This table shows what needs to be replaced in the template:

| Current Value | Replacement Type | Used In |
|---------------|-----------------|---------|
| `ShahiTemplate` | PHP Namespace | All PHP classes |
| `shahi-template` | Plugin Slug | Files, URLs, options |
| `shahi_template` | Function Prefix | Global functions |
| `SHAHI_TEMPLATE` | Constant Prefix | Constants |
| `shahi-` | CSS Prefix | CSS classes |
| `ShahiTemplate` | JS Object | JavaScript |
| `shahi-template` | Text Domain | Translation functions |
| `shahi_template_` | Option Prefix | Database options |
| `Shahi Soft Dev` | Author Name | Headers, credits |
| `1.0.0` | Version | Headers, constants |
| `shahi-template.php` | Main File | Entry point |
| `ShahiTemplate` | Directory Name | Folder name |

---

**Next Step**: Use this document to design the interactive setup script (`bin/setup.php`) that collects this information and performs search/replace operations across the entire plugin codebase.
