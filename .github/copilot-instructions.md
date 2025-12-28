## Copilot / AI Agent Instructions — Shahi LegalOps Suite

Enterprise WordPress privacy compliance plugin. Modular architecture with consent management, DSR portal, legal document generation, and accessibility scanning.

### Architecture Overview

**Core structure** (PSR-4 autoloaded, namespace `ShahiLegalopsSuite\`):
- **Entry**: [shahi-legalops-suite.php](shahi-legalops-suite.php) → loads on `plugins_loaded` hook at priority 10
- **Core** ([includes/Core/](includes/Core/)): Plugin.php orchestrates lifecycle, Loader.php manages hooks, Assets.php handles scripts/styles
- **Modules** ([includes/Modules/](includes/Modules/)): Feature units extending `Module` base class; toggle via admin UI
- **Services** ([includes/Services/](includes/Services/)): Business logic layer extending `Base_Service`; keep controllers thin
- **API** ([includes/API/](includes/API/)): REST controllers extending `Base_REST_Controller`; namespace `slos/v1`
- **Database** ([includes/Database/](includes/Database/)): Repositories, migrations, query optimizer

**Key modules**: ConsentManagement, DSR_Portal, LegalDocs, AccessibilityScanner — each self-contained in `includes/Modules/{Name}/`

### Developer Commands

```bash
composer install          # Install dependencies
composer test             # Run PHPUnit (requires WP_ROOT_DIR env var or phpunit.xml.dist config)
composer test:consent     # Run consent test suite only
composer sniff            # PHPCS WordPress standards check
composer fix              # Auto-fix coding standards
composer analyse          # PHPStan level 5
composer check-all        # Run all checks (sniff + analyse + compat + test)
```

**Testing**: Requires real WordPress install. Set `WP_ROOT_DIR` environment variable or update [phpunit.xml.dist](phpunit.xml.dist). Test bootstrap loads WP via [tests/bootstrap.php](tests/bootstrap.php).

### Module Development Pattern

Create modules in `includes/Modules/` extending the abstract `Module` class:

```php
namespace ShahiLegalopsSuite\Modules\YourModule;
use ShahiLegalopsSuite\Modules\Module;

class YourModule extends Module {
    public function get_key() { return 'your-module'; }
    public function get_name() { return 'Your Module'; }
    public function get_description() { return 'Description here.'; }
    public function get_icon() { return 'dashicons-admin-generic'; }
    public function get_category() { return 'compliance'; }
    
    public function init() {
        if (!$this->is_enabled()) return;
        add_action('admin_menu', [$this, 'register_admin_menu'], 20);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }
}
```

Register in `ModuleManager::register_default_modules()`. See [ConsentManagement.php](includes/Modules/ConsentManagement/ConsentManagement.php) for reference.

### REST API Pattern

Extend `Base_REST_Controller` (namespace `slos/v1`):

```php
namespace ShahiLegalopsSuite\API;
class Your_Controller extends Base_REST_Controller {
    protected $rest_base = 'your-resource';
    
    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, [...]);
    }
}
```

Permission callbacks: `RestAPI::permission_callback_admin()`, `RestAPI::permission_callback_authenticated()`

### Service Layer Pattern

Extend `Base_Service` for business logic:

```php
namespace ShahiLegalopsSuite\Services;
class Your_Service extends Base_Service {
    protected function add_error(string $code, string $message, $data = null): void;
    protected function add_validation_error(string $field, string $message): void;
}
```

### Database / Repository Pattern

All database access goes through repositories in `includes/Database/Repositories/`. Extend `Base_Repository`:

```php
namespace ShahiLegalopsSuite\Database\Repositories;
class Your_Repository extends Base_Repository {
    protected function get_table_name(): string { return 'slos_your_table'; }
    
    // Inherited: create(), find(), update(), delete(), find_by(), paginate()
}
```

**Key methods in Base_Repository**: `create($data)`, `find($id)`, `update($id, $data)`, `delete($id)`, `find_by($column, $value)`, `paginate($args)`. Auto-adds `created_at`/`updated_at` timestamps.

**Existing repositories**: `Consent_Repository`, `DSR_Repository`, `Legal_Doc_Repository`, `Company_Profile_Repository`

### AJAX Handler Pattern

AJAX handlers live in `includes/Ajax/`. Create handler class with `register_ajax_actions()` method:

```php
namespace ShahiLegalopsSuite\Ajax;
class YourAjax {
    public function register_ajax_actions() {
        add_action('wp_ajax_shahi_your_action', [$this, 'handle_action']);
    }
    
    public function handle_action() {
        AjaxHandler::verify_request('shahi_your_action', 'manage_shahi_template');
        // ... logic ...
        AjaxHandler::success($data, 'Success message');
    }
}
```

Register in `AjaxHandler::init_handlers()`. Use `AjaxHandler::success()`, `AjaxHandler::error()` for responses.

### Admin UI Structure

**Page controllers** (`includes/Admin/`): Each admin page has a controller class with `render()` method that loads a template.

```php
namespace ShahiLegalopsSuite\Admin;
class YourPage {
    public function render() {
        if (!current_user_can('manage_shahi_template')) wp_die(...);
        $data = $this->get_data();
        include SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/your-page.php';
    }
}
```

**Menu registration**: All menus registered via `MenuManager`. Main slug: `shahi-legalops-suite`. Add submenus with:
```php
add_submenu_page('shahi-legalops-suite', $title, $menu_title, 'manage_shahi_template', 'slos-your-page', [$this, 'render']);
```

**Template organization** (`templates/`):
- `admin/` — Admin page templates (dashboard.php, settings.php, consent/, documents/, profile/)
- `frontend/` — Public-facing templates
- `legaldocs/` — Legal document templates (privacy policy, terms of service)
- `widgets/` — Widget templates

### Critical Conventions

- **Namespaces**: Mirror directory layout (`ShahiLegalopsSuite\Services\` → `includes/Services/`)
- **Text domain**: `shahi-legalops-suite` — wrap all strings in `__()` or `_e()`
- **Capabilities**: `manage_shahi_template`, `slos_manage_dsr`, `manage_shahi_modules`
- **Feature flags**: Defined in [config/stage-1-constants.php](config/stage-1-constants.php) — check `SLOS_FEATURE_*` constants
- **Defensive loading**: Always check `defined('ABSPATH')` at file start; use try/catch for external services

### Custom Hooks Reference

Actions (see [docs/hooks-reference.md](docs/hooks-reference.md)):
- `slos_consent_recorded` — after consent saved
- `slos_consent_updated` — after consent modified
- `slos_consent_withdrawn` — after user withdraws
- `slos_dsr_request_created` — after DSR request

### External Dependencies

- **dompdf/dompdf**: PDF generation for legal documents
- **symfony/dom-crawler**: HTML parsing for accessibility scanning
- **Geolocation**: `Geo_Service` + `Geo_Rule_Matcher` for region detection
- **WPML**: [wpml-config.xml](wpml-config.xml) for multilingual support

### File References

| Purpose | Location |
|---------|----------|
| Plugin constants | [shahi-legalops-suite.php#L28-45](shahi-legalops-suite.php) |
| Feature flags | [config/stage-1-constants.php](config/stage-1-constants.php) |
| Module base class | [includes/Modules/Module.php](includes/Modules/Module.php) |
| Service base class | [includes/Services/Base_Service.php](includes/Services/Base_Service.php) |
| REST base class | [includes/API/Base_REST_Controller.php](includes/API/Base_REST_Controller.php) |
| Test bootstrap | [tests/bootstrap.php](tests/bootstrap.php) |
| Module development guide | [docs/module-development.md](docs/module-development.md) |

### PR Checklist

1. Run `composer sniff` and `composer analyse` — fix all issues
2. Add tests for new features in `tests/integration/` or `tests/unit/`
3. Preserve PSR-4 namespace structure
4. Use existing Services; avoid duplicating logic
5. Wrap new strings with text domain for i18n
