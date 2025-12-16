# Getting Started: Create Your Plugin from ShahiTemplate

This guide walks you through creating a new WordPress plugin using ShahiTemplate as your foundation.

## Prerequisites

Before you begin, ensure you have:

- **PHP 7.4+** installed
- **Composer** installed ([getcomposer.org](https://getcomposer.org))
- **Node.js & npm** installed ([nodejs.org](https://nodejs.org))
- **Git** installed
- A code editor (VS Code, PhpStorm, etc.)

For Windows users: **Git Bash or WSL** is recommended for running build scripts.

---

## Step 1: Clone the Repository

Clone ShahiTemplate to a new directory with your plugin name:

```bash
# Clone to a new directory
git clone https://github.com/yourusername/shahi-template.git my-awesome-plugin

# Navigate to the directory
cd my-awesome-plugin
```

**Alternative:** Download the ZIP file and extract it to your desired location.

---

## Step 2: Run the Setup Wizard

The setup wizard will transform ShahiTemplate into your custom plugin:

```bash
php bin/setup.php
```

The wizard will ask you for:

1. **Plugin Name** - e.g., "My Awesome Plugin"
2. **Plugin Slug** - e.g., "my-awesome-plugin" (for URLs/IDs)
3. **Plugin Namespace** - e.g., "MyAwesomePlugin" (for PHP classes)
4. **Description** - Brief description of your plugin
5. **Author Name** - Your name or company
6. **Author Email** - Your email address
7. **Author URL** - Your website
8. **Plugin URL** - Plugin homepage/repository URL
9. **Text Domain** - For translations (usually same as slug)

**Example:**
```
Plugin Name *: Security Pro
Plugin Slug *: security-pro
Plugin Namespace *: SecurityPro
Description: Advanced security features for WordPress
Author Name *: John Doe
Author Email *: john@example.com
Author URL: https://johndoe.com
Plugin URL: https://github.com/johndoe/security-pro
Text Domain *: security-pro
```

The wizard will:
- ✅ Rename all files and directories
- ✅ Update namespaces throughout the codebase
- ✅ Update composer.json and package.json
- ✅ Update all text domains for translations
- ✅ Replace all "ShahiTemplate" references

---

## Step 3: Install Dependencies

After setup completes, install PHP and JavaScript dependencies:

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

---

## Step 4: Build Frontend Assets

Compile CSS and JavaScript files:

```bash
# Development build (with source maps)
npm run dev

# Production build (minified)
npm run build
```

**Available npm commands:**
- `npm run dev` - Development build
- `npm run build` - Production build (optimized)
- `npm run watch` - Watch mode (auto-rebuild on changes)

---

## Step 5: Start Building Features

### 5.1 Create Your First Module

Generate a new module with boilerplate code:

```bash
php bin/create-module.php ContactForm "Handle contact form submissions"
```

This creates:
```
includes/modules/contact-form/
├── ContactForm_Module.php          (Main module class)
├── admin/
│   └── ContactForm_Admin.php       (Admin functionality)
├── frontend/
│   └── ContactForm_Frontend.php    (Frontend functionality)
├── api/                            (API endpoints folder)
└── settings.json                   (Module configuration)
```

### 5.2 Register Your Module

Edit `includes/class-module-manager.php` and add your module to the `$this->modules` array:

```php
$this->modules = [
    'contact-form' => [
        'class' => ContactForm_Module::class,
        'enabled' => true,
        'priority' => 10,
    ],
];
```

### 5.3 Update Autoloader

After creating modules, regenerate the autoloader:

```bash
composer dump-autoload
```

---

## Step 6: Development Workflow

### Test Your Plugin in WordPress

1. **Symlink or copy** your plugin folder to WordPress plugins directory:
   ```bash
   # Symlink (Linux/Mac)
   ln -s /path/to/my-awesome-plugin /path/to/wordpress/wp-content/plugins/
   
   # Windows (Run as Administrator)
   mklink /D "C:\xampp\htdocs\wordpress\wp-content\plugins\my-awesome-plugin" "C:\path\to\my-awesome-plugin"
   ```

2. **Activate the plugin** in WordPress admin (Plugins → Installed Plugins)

3. **Access plugin settings** at WordPress Admin → Your Plugin Name

### Code Quality Checks

Run these commands before committing:

```bash
# Check coding standards
composer sniff

# Fix coding standards automatically
composer fix

# Run static analysis
composer analyse

# Run PHP compatibility check
composer check-compat

# Run all tests
composer test
```

### Watch Mode for Development

Keep assets auto-compiling while you work:

```bash
npm run watch
```

---

## Step 7: Build for Production

When ready to deploy or distribute:

```bash
# Run the build script (requires bash)
bash bin/build.sh
```

This will:
1. Clean previous builds
2. Install production dependencies (no dev packages)
3. Build and minify frontend assets
4. Copy files to `build/` directory
5. Remove development files (.git, tests, node_modules, etc.)
6. Create optimized ZIP file in `dist/` folder
7. Restore development environment

**Output:** `dist/my-awesome-plugin.zip` (ready for distribution)

**Windows Users:** Use Git Bash or WSL to run the build script.

---

## Step 8: Customize Further

### Modify the UI Theme

Edit dark futuristic theme colors in:
- `assets/css/admin/variables.css` - CSS variables
- `assets/css/admin/dashboard.css` - Dashboard styles
- `assets/css/admin/forms.css` - Form styles

### Add Custom Settings

1. Create settings in `includes/admin/class-settings.php`
2. Add settings sections and fields
3. Register settings with WordPress Settings API

### Create Database Tables

Define schema in:
- `includes/class-database.php` - Table creation
- `DATABASE-SCHEMA.md` - Documentation

### Add Admin Pages

Create admin pages in:
- `includes/admin/pages/` - Page classes
- Register in `includes/admin/class-admin.php`

---

## Useful Resources

### Included Documentation

- **README.md** - Plugin overview
- **DEVELOPER-GUIDE.md** - Detailed development guide
- **API-DOCUMENTATION.md** - API reference
- **TEMPLATE-USAGE.md** - Template usage guide
- **DATABASE-SCHEMA.md** - Database structure
- **SECURITY-DOCUMENTATION.md** - Security best practices

### Code Examples

Check the `examples/` folder for:
- Admin notices
- Cron jobs
- Data export/import
- Database operations
- Email sending
- File uploads
- Form handling
- Settings API usage

### Demo Plugins

Four complete working examples in `NewPlugins/`:
1. **ShahiPrivacyShield** - Privacy & data protection
2. **ShahiSEO** - SEO optimization
3. **ShahiBackup** - Backup & restore
4. **ShahiForms** - Form builder

Study these to understand the architecture!

---

## Troubleshooting

### Setup Wizard Fails

**Issue:** "Class not found" errors

**Solution:** Ensure you're running PHP 7.4+ and composer dependencies are installed:
```bash
composer install
php bin/setup.php
```

### Build Script Fails on Windows

**Issue:** "bash: command not found"

**Solution:** Use Git Bash or WSL:
```bash
# Git Bash
"C:\Program Files\Git\bin\bash.exe" bin/build.sh

# WSL
wsl bash bin/build.sh
```

### Assets Not Loading

**Issue:** CSS/JS files not loading in WordPress

**Solution:** Rebuild assets:
```bash
npm install
npm run build
```

### Module Not Working

**Issue:** Created module doesn't appear

**Solution:** 
1. Register module in `includes/class-module-manager.php`
2. Regenerate autoloader: `composer dump-autoload`
3. Clear WordPress cache

### Code Quality Checks Fail

**Issue:** `composer sniff` reports errors

**Solution:** Auto-fix most issues:
```bash
composer fix
```

---

## Next Steps

1. ✅ **Read DEVELOPER-GUIDE.md** for in-depth architecture details
2. ✅ **Study demo plugins** in NewPlugins/ folder
3. ✅ **Review examples** in examples/ folder
4. ✅ **Check API-DOCUMENTATION.md** for available classes/methods
5. ✅ **Join the community** (if applicable)

---

## Support

- **Documentation:** See `/docs` folder
- **Issues:** Report on GitHub repository
- **Examples:** Check `/examples` folder
- **Security:** Review SECURITY-DOCUMENTATION.md

---

## Quick Reference Commands

```bash
# Setup
php bin/setup.php                    # Transform template to your plugin
composer install                     # Install PHP dependencies
npm install                          # Install JS dependencies

# Development
php bin/create-module.php Name Desc  # Create new module
npm run dev                          # Build assets (development)
npm run watch                        # Watch and auto-rebuild
composer dump-autoload               # Regenerate autoloader

# Quality
composer sniff                       # Check code standards
composer fix                         # Fix code standards
composer analyse                     # Static analysis
composer test                        # Run tests

# Production
npm run build                        # Build assets (production)
bash bin/build.sh                    # Create distribution ZIP
```

---

**Happy Plugin Building! 🚀**

Your enterprise-grade WordPress plugin starts here.
