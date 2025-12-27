# Stage 1: Foundation Hub - Developer Guide

> **Version:** 1.0  
> **Last Updated:** December 24, 2025  
> **For:** Shahi LegalOps Suite v4.1.0+

---

## Architecture Overview

### Directory Structure

```
includes/
├── Admin/
│   ├── Document_Hub_Controller.php  # Hub page controller & AJAX handlers
│   └── Profile_Wizard.php           # Profile wizard controller
├── Database/
│   ├── Migrations/
│   │   └── Migration_Company_Profile.php  # Database schema migrations
│   └── Repositories/
│       ├── Company_Profile_Repository.php # Profile data access
│       └── Legal_Doc_Repository.php       # Document data access
├── Services/
│   ├── Base_Service.php             # Abstract service base class
│   ├── Document_Generator.php       # Document generation logic
│   ├── Placeholder_Mapper.php       # Template placeholder processing
│   ├── Profile_Validator.php        # Profile validation logic
│   └── Template_Manager.php         # Template loading/management
└── Helpers/
    └── (utility functions)

templates/
├── admin/
│   ├── documents/
│   │   ├── hub.php                  # Main dashboard template
│   │   └── partials/
│   │       ├── document-card.php    # Document card component
│   │       └── profile-banner.php   # Profile completion banner
│   └── profile/
│       └── wizard.php               # Profile wizard template
└── legaldocs/
    ├── privacy-policy.html          # Privacy policy template
    ├── terms-of-service.html        # Terms of service template
    └── cookie-policy.html           # Cookie policy template

assets/
├── css/
│   ├── document-hub.css             # Hub dashboard styles
│   └── profile-wizard.css           # Wizard styles
└── js/
    ├── document-hub.js              # Hub interactivity
    └── profile-wizard.js            # Wizard interactivity

tests/
└── unit/
    ├── Profile_Validator_Test.php   # Validator tests
    ├── Placeholder_Mapper_Test.php  # Mapper tests
    └── Document_Generator_Test.php  # Generator tests

config/
└── stage-1-constants.php            # Feature flags & constants
```

---

## Core Components

### 1. Document_Generator

The central service for generating legal documents from templates and profile data.

#### Class Diagram

```
Document_Generator
├── Properties
│   ├── $validator: Profile_Validator
│   ├── $mapper: Placeholder_Mapper
│   ├── $doc_repository: Legal_Doc_Repository
│   └── $profile_repository: Company_Profile_Repository
├── Constants
│   └── DOCUMENT_TYPES: array
└── Methods
    ├── generate_from_profile($doc_type, $user_id, $force)
    ├── generate_preview($doc_type)
    ├── get_generation_context($doc_type)
    ├── load_template($doc_type)
    ├── add_legal_disclaimer($content)
    ├── build_metadata($doc_type, $profile)
    └── create_version($doc_id, $content, $metadata)
```

#### Key Method: `generate_from_profile()`

```php
/**
 * Generate a legal document from profile data
 *
 * @param string $doc_type Document type key
 * @param int|null $user_id User ID (defaults to current user)
 * @param bool $force Force generation even with warnings
 * @return array Result with success, doc_id, or error info
 */
public function generate_from_profile( $doc_type, $user_id = null, $force = false ) {
    // 1. Validate document type
    if ( ! isset( self::DOCUMENT_TYPES[ $doc_type ] ) ) {
        return $this->error( 'Invalid document type.' );
    }

    // 2. Get current profile
    $profile = $this->profile_repository->get_profile();
    
    // 3. Validate profile readiness
    $validation = $this->validator->validate( $profile );
    if ( ! $validation['is_valid'] && ! $force ) {
        return array(
            'success' => false,
            'requires_profile' => true,
            'missing_fields' => $validation['missing_fields'],
            'completion' => $validation['completion'],
        );
    }

    // 4. Load template
    $template = $this->load_template( $doc_type );
    if ( empty( $template ) ) {
        return $this->error( 'Template not found.' );
    }

    // 5. Process template with profile data
    $content = $this->mapper->parse_template( $template, $profile );

    // 6. Add legal disclaimer
    $content = $this->add_legal_disclaimer( $content );

    // 7. Check for existing document
    $existing = $this->doc_repository->get_by_type( $doc_type );
    
    // 8. Save as draft
    $doc_data = array(
        'doc_type' => $doc_type,
        'title' => self::DOCUMENT_TYPES[ $doc_type ],
        'content' => $content,
        'status' => 'draft', // Always draft
        'metadata' => $this->build_metadata( $doc_type, $profile ),
    );

    if ( $existing ) {
        // Update existing & create version
        $doc_id = $existing['id'];
        $this->doc_repository->update( $doc_id, $doc_data );
        $this->create_version( $doc_id, $content, $doc_data['metadata'] );
    } else {
        // Create new document
        $doc_id = $this->doc_repository->save( $doc_data );
        $this->create_version( $doc_id, $content, $doc_data['metadata'] );
    }

    return array(
        'success' => true,
        'doc_id' => $doc_id,
        'status' => 'draft',
        'message' => 'Document generated successfully.',
    );
}
```

### 2. Profile_Validator

Validates company profile data for completeness before document generation.

#### Mandatory Fields

```php
protected $mandatory_fields = array(
    'company.legal_name',
    'company.address.street',
    'company.address.city',
    'company.address.country',
    'company.business_type',
    'contacts.legal_email',
    'contacts.dpo.email',
    'website.url',
    'website.service_description',
    'data_collection.personal_data_types',  // Array - needs at least 1 item
    'data_collection.purposes',             // Array - needs at least 1 item
    'cookies.essential',                    // Array - needs at least 1 item
    'legal.primary_jurisdiction',
    'retention.default_period',
);
```

#### Validation Flow

```php
public function validate( array $profile ): array {
    $missing = array();
    $filled = 0;
    $total = count( $this->mandatory_fields );

    foreach ( $this->mandatory_fields as $field ) {
        $value = $this->get_field_value( $profile, $field );
        
        if ( $this->is_empty_value( $value ) ) {
            $missing[ $field ] = $this->get_field_label( $field );
        } else {
            $filled++;
        }
    }

    return array(
        'is_valid' => empty( $missing ),
        'missing_fields' => $missing,
        'completion' => (int) round( ( $filled / $total ) * 100 ),
        'filled_count' => $filled,
        'total_count' => $total,
    );
}
```

### 3. Placeholder_Mapper

Processes template placeholders and transforms them with profile data.

#### Placeholder Syntax

| Pattern | Example | Description |
|---------|---------|-------------|
| `{{field}}` | `{{company_legal_name}}` | Simple replacement |
| `{{field\|default:"value"}}` | `{{trading_name\|default:"Company"}}` | With default |
| `{{if field}}...{{/if}}` | `{{if gdpr_applies}}GDPR text{{/if}}` | Conditional |
| `{{foreach arr as item}}...{{/foreach}}` | `{{foreach data_types as type}}...{{/foreach}}` | Loop |

#### Processing Order

```php
public function parse_template( string $template, array $profile ): string {
    $this->profile = $profile;
    $this->resolved = $this->build_placeholder_map( $profile );

    // 1. Process conditionals ({{if}}...{{/if}})
    $content = $this->process_conditionals( $template );
    
    // 2. Process loops ({{foreach}}...{{/foreach}})
    $content = $this->process_loops( $content );
    
    // 3. Process simple placeholders ({{field}})
    $content = $this->process_placeholders( $content );
    
    // 4. Clean up any unresolved
    $content = $this->clean_unresolved( $content );

    return $content;
}
```

#### Placeholder Map Structure

```php
protected function build_placeholder_map( array $profile ): array {
    $map = array();

    // Flatten nested profile to dot notation
    // company.legal_name => Acme Corp
    $flattened = $this->flatten_array( $profile );
    
    // Convert to underscore format for templates
    // company_legal_name => Acme Corp
    foreach ( $flattened as $key => $value ) {
        $placeholder_key = str_replace( '.', '_', $key );
        $map[ $placeholder_key ] = $this->format_value( $value );
    }

    // Add system placeholders
    $map['@today'] = date_i18n( get_option( 'date_format' ) );
    $map['@year'] = date( 'Y' );
    $map['@site_name'] = get_bloginfo( 'name' );
    $map['@site_url'] = home_url();
    $map['@admin_email'] = get_option( 'admin_email' );

    return $map;
}
```

---

## Database Schema

### `wp_slos_company_profile`

```sql
CREATE TABLE wp_slos_company_profile (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    profile_data LONGTEXT NOT NULL,
    completion_percentage INT(3) DEFAULT 0,
    version INT(11) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT(20) UNSIGNED,
    PRIMARY KEY (id),
    KEY idx_version (version),
    KEY idx_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### `wp_slos_legal_docs`

```sql
CREATE TABLE wp_slos_legal_docs (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    doc_type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'draft',
    locale VARCHAR(10) DEFAULT 'en_US',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by BIGINT(20) UNSIGNED,
    metadata LONGTEXT,
    PRIMARY KEY (id),
    UNIQUE KEY idx_doc_type_locale (doc_type, locale),
    KEY idx_status (status),
    KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### `wp_slos_legal_doc_versions`

```sql
CREATE TABLE wp_slos_legal_doc_versions (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    doc_id BIGINT(20) UNSIGNED NOT NULL,
    version_number VARCHAR(20) NOT NULL,
    content LONGTEXT NOT NULL,
    metadata LONGTEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT(20) UNSIGNED,
    PRIMARY KEY (id),
    KEY idx_doc_id (doc_id),
    KEY idx_created_at (created_at),
    FOREIGN KEY (doc_id) REFERENCES wp_slos_legal_docs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## Extending Stage 1

### Adding Custom Document Types

```php
add_filter( 'slos_document_types', function( $types ) {
    $types['custom-policy'] = 'Custom Policy';
    return $types;
});

// Create template at: templates/legaldocs/custom-policy.html
```

### Adding Custom Placeholders

```php
add_filter( 'slos_placeholder_map', function( $map, $profile ) {
    // Add custom placeholder
    $map['custom_field'] = get_option( 'my_custom_option' );
    
    // Add computed placeholder
    $map['full_address'] = sprintf(
        '%s, %s, %s',
        $profile['company']['address']['street'] ?? '',
        $profile['company']['address']['city'] ?? '',
        $profile['company']['address']['country'] ?? ''
    );
    
    return $map;
}, 10, 2 );
```

### Adding Mandatory Fields

```php
add_filter( 'slos_mandatory_profile_fields', function( $fields ) {
    // Add custom required field
    $fields[] = 'company.custom_field';
    return $fields;
});

// Don't forget to add label
add_filter( 'slos_field_labels', function( $labels ) {
    $labels['company.custom_field'] = 'Custom Field Name';
    return $labels;
});
```

### Customizing Legal Disclaimer

```php
add_filter( 'slos_legal_disclaimer', function( $disclaimer, $doc_type ) {
    // Add document-specific disclaimer
    if ( $doc_type === 'privacy-policy' ) {
        $disclaimer .= '<p class="slos-disclaimer-privacy">
            This privacy policy complies with GDPR requirements.
        </p>';
    }
    return $disclaimer;
}, 10, 2 );
```

### Custom Template Loading

```php
add_filter( 'slos_template_path', function( $path, $doc_type ) {
    // Use custom template from theme
    $theme_template = get_template_directory() . "/slos-templates/{$doc_type}.html";
    if ( file_exists( $theme_template ) ) {
        return $theme_template;
    }
    return $path;
}, 10, 2 );
```

---

## Testing

### Running Unit Tests

```bash
# From plugin root directory
cd tests/

# Run all Stage 1 tests
php -r "require '../vendor/autoload.php'; require 'bootstrap.php';"
php unit/Profile_Validator_Test.php
php unit/Placeholder_Mapper_Test.php
php unit/Document_Generator_Test.php
```

### Writing New Tests

Follow the existing test pattern:

```php
<?php
namespace ShahiLegalopsSuite\Tests;

use ShahiLegalopsSuite\Services\YourService;

class YourService_Test {
    private $test_count = 0;
    private $passed = 0;
    private $failed = 0;
    private $service;

    public function __construct() {
        $this->service = new YourService();
    }

    public function run(): void {
        echo "\n=== YourService Unit Tests ===\n";
        
        $this->test_feature_one();
        $this->test_feature_two();
        
        $this->print_summary();
    }

    private function test_feature_one(): void {
        $this->test_count++;
        echo "\nTest: Feature one description... ";

        $result = $this->service->feature_one();

        if ( $expected === $result ) {
            $this->passed++;
            echo "PASSED";
        } else {
            $this->failed++;
            echo "FAILED - Expected X, got Y";
        }
    }

    private function print_summary(): void {
        echo "\n\n=== Summary ===\n";
        echo "Total: {$this->test_count}\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
    }
}
```

### Test Data

Use the complete profile fixture for testing:

```php
private function get_complete_profile(): array {
    return array(
        'company' => array(
            'legal_name' => 'Test Company Ltd',
            'trading_name' => 'TestCo',
            'address' => array(
                'street' => '123 Test Street',
                'city' => 'London',
                'state' => 'Greater London',
                'postal_code' => 'EC1A 1BB',
                'country' => 'United Kingdom',
            ),
            'business_type' => 'Limited Company',
            'industry' => 'Technology',
        ),
        'contacts' => array(
            'legal_email' => 'legal@test.com',
            'support_email' => 'support@test.com',
            'phone' => '+44 20 7123 4567',
            'dpo' => array(
                'name' => 'Jane DPO',
                'email' => 'dpo@test.com',
            ),
        ),
        // ... (see Profile_Validator_Test.php for full fixture)
    );
}
```

---

## Performance Considerations

### Caching

```php
// Profile is cached in repository
$profile = $this->profile_repository->get_profile();
// Returns cached version if called multiple times in same request

// Clear cache when profile updates
$this->profile_repository->clear_cache();
```

### Template Loading

Templates are loaded from disk on each generation. For high-volume sites, consider:

```php
add_filter( 'slos_template_cache', function( $should_cache ) {
    return true; // Enable template caching
}, 10, 1 );
```

### Database Queries

- Documents are indexed by `doc_type` and `locale`
- Versions are indexed by `doc_id`
- Profile version is stored in `wp_options` for fast access

---

## Security Best Practices

### Input Sanitization

```php
// Always sanitize user input
$doc_type = sanitize_key( $_POST['doc_type'] );
$doc_id = absint( $_POST['doc_id'] );
$content = wp_kses_post( $_POST['content'] );
```

### Output Escaping

```php
// Escape all output
echo esc_html( $title );
echo esc_attr( $class );
echo esc_url( $url );
echo wp_kses_post( $content ); // For rich content
```

### Nonce Verification

```php
// In AJAX handlers
check_ajax_referer( 'slos_hub_nonce', 'nonce' );

// In form handlers
wp_verify_nonce( $_POST['_wpnonce'], 'slos_action' );
```

### Capability Checks

```php
if ( ! current_user_can( 'manage_options' ) ) {
    wp_send_json_error( array( 'message' => 'Permission denied.' ) );
}
```

---

## Debugging

### Enable Debug Logging

```php
// In wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );

// In your code
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    error_log( 'SLOS Debug: ' . print_r( $data, true ) );
}
```

### AJAX Debugging

```javascript
// In browser console
$.ajax({
    url: slosHub.ajaxUrl,
    type: 'POST',
    data: {
        action: 'slos_gen_get_context',
        nonce: slosHub.nonce,
        doc_type: 'privacy-policy'
    },
    success: function(response) {
        console.log('Response:', response);
    },
    error: function(xhr, status, error) {
        console.error('Error:', error);
        console.log('Response:', xhr.responseText);
    }
});
```

### Template Debugging

```php
// Temporarily add to template to debug placeholders
add_filter( 'slos_placeholder_map', function( $map, $profile ) {
    error_log( 'Placeholder Map: ' . print_r( $map, true ) );
    return $map;
}, 10, 2 );
```

---

## Upgrade Path

### Stage 1 → Stage 2

Stage 2 will add:
- Pre-generation review modal
- Live preview with overrides
- Version comparison & restore
- Modular compliance blocks (GDPR/CCPA/LGPD)
- Enhanced regeneration workflow
- Bulk document actions

### Migration Considerations

- Database schema remains compatible
- New tables may be added for compliance blocks
- Existing documents will gain new metadata fields
- Templates will be enhanced with new conditional blocks

---

## Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | Dec 24, 2025 | Initial developer guide for Stage 1 |

---

**Previous:** [Stage 1 API Reference](STAGE-1-API-REFERENCE.md) | [Stage 1 User Guide](STAGE-1-USER-GUIDE.md)
