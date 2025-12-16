# Using ShahiTemplate for New Plugin Development

This guide walks you through the complete process of using ShahiTemplate to create a new WordPress plugin.

## Prerequisites
- Git installed on your system
- Composer installed globally
- PHP 7.4+ and WordPress 5.8+ environment
- Basic understanding of WordPress plugin development

## Step 1: Clone and Setup

### Option A: Clone the Repository
```bash
# Clone the template
git clone https://github.com/your-org/ShahiTemplate MyNewPlugin
cd MyNewPlugin

# Remove the original git history
rm -rf .git
git init

# Install dependencies
composer install

# Run the setup script [PLACEHOLDER: Script not yet created]
php bin/setup.php
```

The setup script will prompt you for:
- **Plugin Name**: "My Awesome Plugin"
- **Plugin Slug**: "my-awesome-plugin"
- **PHP Namespace**: "MyAwesomePlugin"
- **Author Name**: "Your Name"
- **Author Email**: "your@email.com"
- **Author URL**: "https://yoursite.com"
- **Text Domain**: "my-awesome-plugin" (auto-generated from slug)

### Option B: Use GitHub Template
1. Click "Use this template" on the GitHub repository
2. Name your new repository
3. Clone your new repository locally
4. Run `composer install`
5. Run `php bin/setup.php`

### What the Setup Script Does [PLACEHOLDER: Script pending]
1. Search and replace all namespace occurrences
2. Update text domain in all PHP files
3. Rename the main plugin file
4. Update `composer.json` with new namespace
5. Regenerate Composer autoloader
6. Clear language files (ready for new translations)
7. Reset version to 1.0.0
8. Clear `CHANGELOG.md` for fresh start
9. Update constants (PREFIX, VERSION, PATH, URL)

## Step 2: Customize Brand and Appearance

### Update Color Scheme
Edit `assets/css/admin-global.css` to change the theme colors:

```css
:root {
    /* Primary colors - Change these to match your brand */
    --shahi-primary: #00d4ff;          /* Main accent color */
    --shahi-secondary: #7000ff;        /* Secondary accent */
    --shahi-accent: #00ff88;           /* Tertiary accent */
    
    /* Background colors */
    --shahi-bg-dark: #0a0a12;          /* Main background */
    --shahi-bg-light: #1a1a2e;         /* Card backgrounds */
    --shahi-bg-lighter: #252541;       /* Hover states */
    
    /* Text colors */
    --shahi-text-primary: #ffffff;     /* Main text */
    --shahi-text-secondary: #a0a0b0;   /* Secondary text */
    --shahi-text-muted: #6b6b7f;       /* Muted text */
}
```

### Replace Logo and Icons
1. Replace `assets/images/logo.png` with your plugin logo (recommended: 200x50px)
2. Replace `assets/images/icon.png` with your plugin icon (recommended: 256x256px)
3. Update icon references in `includes/Core/MenuManager.php`:

```php
add_menu_page(
    __('My Plugin', 'my-plugin'),
    __('My Plugin', 'my-plugin'),
    'manage_options',
    'my-plugin',
    [$this, 'display_dashboard'],
    'dashicons-admin-generic', // Change this to your icon
    6
);
```

### Customize Onboarding Content
Edit `includes/Admin/Onboarding.php` to update the wizard steps:

```php
private function get_steps() {
    return [
        'welcome' => [
            'title' => __('Welcome to My Plugin', 'my-plugin'),
            'description' => __('Your custom description here', 'my-plugin'),
        ],
        'configuration' => [
            'title' => __('Configuration', 'my-plugin'),
            'description' => __('Set up your initial settings', 'my-plugin'),
        ],
        // Add or remove steps as needed
    ];
}
```

### Customize Dashboard Widgets
Edit `includes/Admin/Dashboard.php` to modify widgets:

```php
private function render_widgets() {
    $widgets = [
        'stats' => new \ShahiTemplate\Widgets\StatsWidget(),
        'activity' => new \ShahiTemplate\Widgets\RecentActivityWidget(),
        // Add your custom widgets here
    ];
}
```

## Step 3: Build Features

### Adding a New Module
Modules are self-contained features. To create one:

1. **Create the Module File**: `includes/Modules/EmailNotifications_Module.php`

```php
<?php
namespace ShahiTemplate\Modules;

class EmailNotifications_Module {
    public function run() {
        // Hook into WordPress actions
        add_action('user_register', [$this, 'send_welcome_email']);
        add_filter('wp_mail_from_name', [$this, 'custom_email_name']);
    }
    
    public function send_welcome_email($user_id) {
        $user = get_userdata($user_id);
        wp_mail(
            $user->user_email,
            __('Welcome!', 'shahi-template'),
            __('Thank you for registering!', 'shahi-template')
        );
    }
    
    public function custom_email_name($name) {
        return get_bloginfo('name');
    }
}
```

2. **Register the Module**: Add to `includes/Core/Plugin.php`

```php
private function init_modules() {
    $modules = [
        new \ShahiTemplate\Modules\Analytics_Module(),
        new \ShahiTemplate\Modules\EmailNotifications_Module(), // Add here
    ];
    
    foreach ($modules as $module) {
        if (method_exists($module, 'run')) {
            $module->run();
        }
    }
}
```

### Adding REST API Endpoints
Create a controller in `includes/API/`:

```php
<?php
namespace ShahiTemplate\API;

class ProductsController {
    public function register_routes() {
        register_rest_route('shahi-template/v1', '/products', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_products'],
                'permission_callback' => [$this, 'check_permission']
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'create_product'],
                'permission_callback' => [$this, 'check_permission']
            ]
        ]);
    }
    
    public function get_products($request) {
        // Your logic here
        return rest_ensure_response(['products' => []]);
    }
    
    public function check_permission() {
        return current_user_can('manage_options');
    }
}
```

Register it in `includes/API/RestAPI.php`:
```php
public function register_routes() {
    $controllers = [
        new SettingsController(),
        new ProductsController(), // Add here
    ];
    
    foreach ($controllers as $controller) {
        $controller->register_routes();
    }
}
```

### Adding Admin Pages
Create a page class in `includes/Admin/`:

```php
<?php
namespace ShahiTemplate\Admin;

class Reports {
    public function register_page() {
        add_submenu_page(
            'shahi-template',
            __('Reports', 'shahi-template'),
            __('Reports', 'shahi-template'),
            'manage_options',
            'shahi-template-reports',
            [$this, 'render_page']
        );
    }
    
    public function render_page() {
        include SHAHI_TEMPLATE_PATH . 'templates/admin/reports.php';
    }
}
```

### Adding Shortcodes
Create in `includes/Shortcodes/`:

```php
<?php
namespace ShahiTemplate\Shortcodes;

class PricingTableShortcode {
    public function register() {
        add_shortcode('pricing_table', [$this, 'render']);
    }
    
    public function render($atts) {
        $atts = shortcode_atts([
            'plans' => '3',
            'style' => 'default'
        ], $atts);
        
        ob_start();
        include SHAHI_TEMPLATE_PATH . 'templates/public/pricing-table.php';
        return ob_get_clean();
    }
}
```

## Step 4: Testing and Deployment

### Pre-Deployment Testing
1. **Enable Debug Mode**: Add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

**Before**:
```php
namespace ShahiTemplate\Admin;
```

**After**:
```php
namespace MyPlugin\Admin;
```

### Text Domain
The default text domain is `shahi-template`. The setup script will replace this with your chosen slug (e.g., `my-plugin`).

**Before**:
```php
__('Settings', 'shahi-template')
```

**After**:
```php
__('Settings', 'my-plugin')
```

### Constants
Global constants are defined in the main plugin file.
- `SHAHI_TEMPLATE_VERSION` - Plugin version
- `SHAHI_TEMPLATE_PATH` - Absolute path to plugin directory
- `SHAHI_TEMPLATE_URL` - URL to plugin directory
- `SHAHI_TEMPLATE_BASENAME` - Plugin basename for activation/deactivation

These will be renamed to match your plugin prefix (e.g., `MY_PLUGIN_VERSION`).

## Common Workflows

### Adding a Settings Tab
1. Open `includes/Admin/Settings.php`
2. Add to the `get_tabs()` method:
```php
'my_tab' => [
    'title' => __('My Tab', 'shahi-template'),
    'icon' => 'dashicons-admin-generic'
]
```
3. Create render method: `render_my_tab_settings()`
4. Add fields using Settings API

### Adding a Dashboard Widget
1. Create widget class in `includes/Widgets/MyWidget.php`
2. Extend from base widget or implement interface
3. Register in `includes/Widgets/WidgetManager.php`

### Adding a Database Table
1. Create migration in `includes/Database/Migrations/migration_x_x_x.php`
2. Implement `up()` and `down()` methods
3. Run migration via activation hook

## Troubleshooting

### Autoloader Not Working
```bash
composer dump-autoload
```

### Assets Not Loading
- Check file paths in `includes/Core/Assets.php`
- Verify `SHAHI_TEMPLATE_URL` constant
- Check browser console for 404 errors

### Modules Not Running
- Verify module is registered in `includes/Core/Plugin.php`
- Check module has `run()` method
- Enable `WP_DEBUG` to see errors

### Settings Not Saving
- Verify nonce is included in form
- Check capability: `current_user_can('manage_options')`
- Verify field names match registered settings

## Additional Resources
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)
- [REST API Handbook](https://developer.wordpress.org/rest-api/)
- [Template Best Practices](docs/best-practices.md)
   - All outputs are escaped
   - Capability checks in place

4. **Performance Check**:
   - Use Query Monitor plugin
   - Check for N+1 queries
   - Verify asset loading is conditional
   - Test on mobile devices

### Deployment Checklist
Follow the complete checklist in `docs/deployment-checklist.md`:

- [ ] Update version number in main plugin file
- [ ] Update `CHANGELOG.md` with changes
- [ ] Run PHPCS for code standards
- [ ] Test on multiple PHP versions (7.4, 8.0, 8.1, 8.2)
- [ ] Test on fresh WordPress installation
- [ ] Generate POT file for translations
- [ ] Create plugin ZIP (exclude dev files)
- [ ] Backup database before deployment

### Package and Distribute
```bash
# Build production version
composer install --no-dev --optimize-autoloader

# Create ZIP excluding dev files
zip -r my-plugin.zip . \
  -x "*.git*" \
  -x "*node_modules*" \
  -x "*.env*" \
  -x "*tests*" \
  -x "*bin*"
```

## Key Concepts

### Namespace
The default namespace is `ShahiTemplate`. The setup script will replace this with your chosen namespace (e.g., `MyPlugin`).

### Text Domain
The default text domain is `shahi-template`. The setup script will replace this with your chosen slug (e.g., `my-plugin`).

### Constants
Global constants are defined in the main plugin file.
- `SHAHI_TEMPLATE_VERSION`
- `SHAHI_TEMPLATE_PATH`
- `SHAHI_TEMPLATE_URL`

These will be renamed to match your plugin prefix (e.g., `MY_PLUGIN_VERSION`).
