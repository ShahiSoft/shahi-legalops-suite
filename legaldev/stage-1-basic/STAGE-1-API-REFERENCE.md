# Stage 1: Foundation Hub - API Reference

> **Version:** 1.0  
> **Last Updated:** December 24, 2025  
> **For:** Shahi LegalOps Suite v4.1.0+

---

## Overview

This document provides a comprehensive reference for all AJAX endpoints and APIs available in the Stage 1 Legal Document Hub.

---

## Authentication & Security

### Nonce Verification

All AJAX requests require a valid WordPress nonce:

```php
// Nonce creation (done in Document_Hub_Controller)
wp_create_nonce( 'slos_hub_nonce' );

// Nonce verification in handlers
check_ajax_referer( 'slos_hub_nonce', 'nonce' );
```

### Capability Requirements

Most endpoints require `manage_options` capability:

```php
if ( ! current_user_can( 'manage_options' ) ) {
    wp_send_json_error( array( 'message' => 'Permission denied.' ) );
}
```

---

## AJAX Endpoints

### Document Generation

#### `slos_gen_get_context`

Get the generation context for a document type, including profile validation status.

**Method:** POST

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | `slos_gen_get_context` |
| `nonce` | string | Yes | Security nonce |
| `doc_type` | string | Yes | Document type (privacy-policy, terms-of-service, cookie-policy) |

**Success Response:**
```json
{
    "success": true,
    "data": {
        "doc_type": "privacy-policy",
        "document_title": "Privacy Policy",
        "is_valid": true,
        "can_generate": true,
        "completion_percentage": 100,
        "missing_fields": [],
        "existing_doc_id": 123,
        "existing_doc_version": "1.2",
        "profile_version": 5
    }
}
```

**Error Response:**
```json
{
    "success": false,
    "data": {
        "message": "Invalid document type."
    }
}
```

**Example Usage:**
```javascript
$.ajax({
    url: slosHub.ajaxUrl,
    type: 'POST',
    data: {
        action: 'slos_gen_get_context',
        nonce: slosHub.nonce,
        doc_type: 'privacy-policy'
    },
    success: function(response) {
        if (response.success) {
            console.log(response.data);
        }
    }
});
```

---

#### `slos_gen_generate`

Generate a document from the current company profile.

**Method:** POST

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | `slos_gen_generate` |
| `nonce` | string | Yes | Security nonce |
| `doc_type` | string | Yes | Document type |
| `force` | boolean | No | Force generation even with warnings |

**Success Response:**
```json
{
    "success": true,
    "data": {
        "message": "Document generated successfully.",
        "doc_id": 456,
        "doc_type": "privacy-policy",
        "status": "draft",
        "version": "1.0",
        "edit_url": "https://example.com/wp-admin/admin.php?page=slos-edit-document&id=456",
        "warnings": []
    }
}
```

**Error Response (Incomplete Profile):**
```json
{
    "success": false,
    "data": {
        "message": "Profile incomplete. Please complete required fields.",
        "requires_profile": true,
        "missing_fields": {
            "contacts.dpo.email": "DPO Email",
            "retention.default_period": "Default Retention Period"
        },
        "completion": 78
    }
}
```

---

#### `slos_gen_preview`

Generate a preview without saving to database.

**Method:** POST

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | `slos_gen_preview` |
| `nonce` | string | Yes | Security nonce |
| `doc_type` | string | Yes | Document type |

**Success Response:**
```json
{
    "success": true,
    "data": {
        "html": "<div class='legal-document'>...</div>",
        "title": "Privacy Policy",
        "doc_type": "privacy-policy",
        "generated_at": "2025-12-24T14:30:00Z"
    }
}
```

---

### Document Management

#### `slos_gen_view_document`

Retrieve a document's content for viewing.

**Method:** POST

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | `slos_gen_view_document` |
| `nonce` | string | Yes | Security nonce |
| `doc_id` | int | Yes | Document ID |

**Success Response:**
```json
{
    "success": true,
    "data": {
        "doc_id": 456,
        "title": "Privacy Policy",
        "html": "<div class='legal-document'>...</div>",
        "status": "draft",
        "version": "1.2",
        "created_at": "2025-12-20T10:00:00Z",
        "updated_at": "2025-12-24T14:30:00Z",
        "metadata": {
            "author_id": 1,
            "profile_version": 5
        }
    }
}
```

---

#### `slos_hub_generate_document`

Alternative endpoint for document generation (legacy support).

**Method:** POST

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | `slos_hub_generate_document` |
| `nonce` | string | Yes | Security nonce |
| `doc_type` | string | Yes | Document type |
| `force` | string | No | "true" to force generation |

---

#### `slos_hub_regenerate_document`

Regenerate an existing document.

**Method:** POST

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | `slos_hub_regenerate_document` |
| `nonce` | string | Yes | Security nonce |
| `doc_id` | int | Yes | Existing document ID |
| `reason` | string | No | Reason for regeneration |

**Success Response:**
```json
{
    "success": true,
    "data": {
        "message": "Document regenerated successfully.",
        "doc_id": 456,
        "new_version": "1.3",
        "previous_version": "1.2"
    }
}
```

---

#### `slos_hub_delete_document`

Delete a document (moves to trash).

**Method:** POST

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | `slos_hub_delete_document` |
| `nonce` | string | Yes | Security nonce |
| `doc_id` | int | Yes | Document ID to delete |

**Success Response:**
```json
{
    "success": true,
    "data": {
        "message": "Document deleted successfully.",
        "doc_id": 456
    }
}
```

---

### Version History

#### `slos_gen_history`

Get version history for a document.

**Method:** POST

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | `slos_gen_history` |
| `nonce` | string | Yes | Security nonce |
| `doc_id` | int | Yes | Document ID |

**Success Response:**
```json
{
    "success": true,
    "data": {
        "doc_id": 456,
        "versions": [
            {
                "id": 789,
                "version_num": "1.3",
                "author_id": 1,
                "author_name": "Admin User",
                "change_reason": "Profile updated - company address changed",
                "created_at": "December 24, 2025",
                "profile_version": 6
            },
            {
                "id": 788,
                "version_num": "1.2",
                "author_id": 1,
                "author_name": "Admin User",
                "change_reason": "Added DPO information",
                "created_at": "December 22, 2025",
                "profile_version": 5
            },
            {
                "id": 787,
                "version_num": "1.0",
                "author_id": 1,
                "author_name": "Admin User",
                "change_reason": "Initial generation",
                "created_at": "December 20, 2025",
                "profile_version": 4
            }
        ]
    }
}
```

---

#### `slos_gen_restore`

Restore a previous version (Stage 2 feature, stub in Stage 1).

**Method:** POST

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | `slos_gen_restore` |
| `nonce` | string | Yes | Security nonce |
| `version_id` | int | Yes | Version ID to restore |

**Response (Stage 1):**
```json
{
    "success": false,
    "data": {
        "message": "Version restore will be available in Stage 2."
    }
}
```

---

### Export Endpoints

#### `slos_export_document`

Export a single document.

**Method:** POST

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | `slos_export_document` |
| `nonce` | string | Yes | Security nonce |
| `doc_id` | int | Yes | Document ID |
| `format` | string | No | Export format: `pdf`, `html` (default: pdf) |

**Success Response:**
```json
{
    "success": true,
    "data": {
        "download_url": "https://example.com/wp-content/uploads/slos-exports/privacy-policy-456.pdf",
        "filename": "privacy-policy-456.pdf",
        "format": "pdf",
        "size": 125678
    }
}
```

---

#### `slos_export_bulk`

Export multiple documents as a ZIP archive.

**Method:** POST

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | `slos_export_bulk` |
| `nonce` | string | Yes | Security nonce |
| `doc_ids` | array | Yes | Array of document IDs |
| `format` | string | No | Export format: `pdf`, `html` (default: html) |

**Success Response:**
```json
{
    "success": true,
    "data": {
        "download_url": "https://example.com/wp-content/uploads/slos-exports/legal-documents-2025-12-24.zip",
        "filename": "legal-documents-2025-12-24.zip",
        "documents_exported": 3
    }
}
```

---

### Bulk Actions

#### `slos_hub_bulk_action`

Perform bulk operations on documents.

**Method:** POST

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | `slos_hub_bulk_action` |
| `nonce` | string | Yes | Security nonce |
| `bulk_action` | string | Yes | Action: `regenerate_outdated`, `export_all`, `delete_drafts` |

**Success Response (regenerate_outdated):**
```json
{
    "success": true,
    "data": {
        "message": "3 documents regenerated successfully.",
        "updated_count": 3,
        "updated_docs": [
            { "id": 456, "type": "privacy-policy", "version": "1.4" },
            { "id": 457, "type": "terms-of-service", "version": "1.2" },
            { "id": 458, "type": "cookie-policy", "version": "1.3" }
        ]
    }
}
```

---

### Profile Endpoints

#### `slos_profile_save_step`

Save a single wizard step.

**Method:** POST

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | `slos_profile_save_step` |
| `nonce` | string | Yes | Security nonce |
| `step` | string | Yes | Step key (company, contacts, website, etc.) |
| `data` | object | Yes | Step field data |

**Success Response:**
```json
{
    "success": true,
    "data": {
        "message": "Step saved successfully.",
        "step": "company",
        "completion_percentage": 35,
        "next_step": "contacts"
    }
}
```

---

## JavaScript API

### Global Object

The `slosHub` object is available when the Document Hub is loaded:

```javascript
slosHub = {
    ajaxUrl: '/wp-admin/admin-ajax.php',
    nonce: 'abc123...',
    profileUrl: '/wp-admin/admin.php?page=slos-company-profile',
    createUrl: '/wp-admin/admin.php?page=slos-legal-docs-create',
    editUrl: '/wp-admin/admin.php?page=slos-edit-document',
    strings: {
        generating: 'Generating document...',
        generated: 'Document generated successfully!',
        // ... more translatable strings
    }
};
```

### SLOSHub Methods

```javascript
// Initialize hub
SLOSHub.init();

// Show toast notification
SLOSHub.showToast('Message text', 'success'); // success, error, warning, info

// Open generate modal
SLOSHub.currentDocType = 'privacy-policy';
SLOSHub.openGenerateModal();

// Close all modals
SLOSHub.closeModals();

// Filter cards by category
SLOSHub.handleFilter({ currentTarget: { data: () => 'privacy' } });
```

---

## PHP API

### Document_Generator Class

```php
use ShahiLegalopsSuite\Services\Document_Generator;

$generator = new Document_Generator();

// Get generation context
$context = $generator->get_generation_context( 'privacy-policy' );

// Generate preview (no save)
$preview = $generator->generate_preview( 'privacy-policy' );

// Generate and save document
$result = $generator->generate_from_profile( 'privacy-policy', $user_id, $force );
```

### Profile_Validator Class

```php
use ShahiLegalopsSuite\Services\Profile_Validator;

$validator = new Profile_Validator();

// Validate full profile
$result = $validator->validate( $profile_data );
// Returns: ['is_valid' => bool, 'missing_fields' => [], 'completion' => int]

// Validate for specific document
$result = $validator->validate_for_document( $profile_data, 'privacy-policy' );

// Calculate completion percentage
$percentage = $validator->calculate_completion( $profile_data );

// Get step completion status
$steps = $validator->get_step_completion( $profile_data );
```

### Placeholder_Mapper Class

```php
use ShahiLegalopsSuite\Services\Placeholder_Mapper;

$mapper = new Placeholder_Mapper();

// Parse template with profile data
$html = $mapper->parse_template( $template_html, $profile_data );

// Build placeholder map
$placeholders = $mapper->build_placeholder_map( $profile_data );
```

### Company_Profile_Repository Class

```php
use ShahiLegalopsSuite\Database\Repositories\Company_Profile_Repository;

$repo = Company_Profile_Repository::get_instance();

// Get profile
$profile = $repo->get_profile();

// Save profile
$repo->save_profile( $data );

// Get completion percentage
$completion = $repo->get_completion_percentage();

// Get profile version
$version = $repo->get_profile_version();
```

### Legal_Doc_Repository Class

```php
use ShahiLegalopsSuite\Database\Repositories\Legal_Doc_Repository;

$repo = new Legal_Doc_Repository();

// Get document by ID
$doc = $repo->get_by_id( $doc_id );

// Get document by type
$doc = $repo->get_by_type( 'privacy-policy' );

// Save document
$doc_id = $repo->save( $data );

// Create version
$version_id = $repo->create_version( $doc_id, $content, $metadata );

// Get versions
$versions = $repo->get_versions( $doc_id );

// Get outdated documents
$outdated = $repo->get_outdated_documents( $current_profile_version );
```

---

## Hooks & Filters

### Actions

```php
// Before document generation
do_action( 'slos_before_document_generation', $doc_type, $profile_data );

// After document generation
do_action( 'slos_after_document_generation', $doc_id, $doc_type, $result );

// Before profile save
do_action( 'slos_before_profile_save', $step, $data );

// After profile save
do_action( 'slos_after_profile_save', $step, $data, $new_version );
```

### Filters

```php
// Modify mandatory fields list
add_filter( 'slos_mandatory_profile_fields', function( $fields ) {
    $fields[] = 'custom.field';
    return $fields;
});

// Modify placeholder map
add_filter( 'slos_placeholder_map', function( $map, $profile ) {
    $map['custom_placeholder'] = $profile['custom']['value'];
    return $map;
}, 10, 2 );

// Modify legal disclaimer
add_filter( 'slos_legal_disclaimer', function( $disclaimer, $doc_type ) {
    return $disclaimer . '<p>Custom disclaimer text.</p>';
}, 10, 2 );

// Modify document before save
add_filter( 'slos_document_before_save', function( $content, $doc_type ) {
    return $content;
}, 10, 2 );
```

---

## Error Codes

| Code | Message | Description |
|------|---------|-------------|
| `invalid_nonce` | Security check failed | Nonce verification failed |
| `permission_denied` | Permission denied | User lacks required capability |
| `invalid_doc_type` | Invalid document type | Unknown document type provided |
| `profile_incomplete` | Profile incomplete | Required profile fields missing |
| `template_not_found` | Template not found | Document template file missing |
| `generation_failed` | Generation failed | Error during document generation |
| `save_failed` | Save failed | Database error saving document |
| `export_failed` | Export failed | Error generating export file |

---

## Rate Limiting

No rate limiting is applied in Stage 1. Rate limiting will be considered for Stage 2+ based on server load patterns.

---

## Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | Dec 24, 2025 | Initial API reference for Stage 1 |

---

**Previous:** [Stage 1 User Guide](STAGE-1-USER-GUIDE.md) | **Next:** [Stage 1 Developer Guide](STAGE-1-DEVELOPER-GUIDE.md)
