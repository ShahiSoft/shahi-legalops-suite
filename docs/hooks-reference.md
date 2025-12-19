# WordPress Hooks Reference

Complete reference for all action and filter hooks in Shahi LegalOps Suite.

Version: 3.0.1

## Action Hooks

Action hooks allow you to execute custom code at specific points in the plugin lifecycle.

### `slos_consent_recorded`

Fires after a consent record is successfully created

**Parameters:**

- `$consent_id` - (int) The consent record ID
- `$consent_data` - (array) The consent data that was saved

**Example:**

```php
add_action('slos_consent_recorded', function($consent_id, $consent_data) {
    error_log('Consent ' . $consent_id . ' recorded for user ' . $consent_data['user_id']);
}, 10, 2);
```

---

### `slos_consent_updated`

Fires after a consent record is updated

**Parameters:**

- `$consent_id` - (int) The consent record ID
- `$update_data` - (array) The data that was updated

**Example:**

```php
add_action('slos_consent_updated', function($consent_id, $update_data) {
    // Custom logic after consent update
}, 10, 2);
```

---

### `slos_consent_withdrawn`

Fires after a consent is withdrawn by a user

**Parameters:**

- `$consent_id` - (int) The consent record ID that was withdrawn

**Example:**

```php
add_action('slos_consent_withdrawn', function($consent_id) {
    // Notify admin of consent withdrawal
}, 10, 1);
```

---

### `slos_consent_deleted`

Fires after a consent record is deleted (admin only)

**Parameters:**

- `$consent_id` - (int) The consent record ID that was deleted

**Example:**

```php
add_action('slos_consent_deleted', function($consent_id) {
    // Log deletion for audit trail
}, 10, 1);
```

---

### `slos_bulk_consent_withdrawn`

Fires after bulk consent withdrawal operation

**Parameters:**

- `$user_id` - (int) The user ID whose consents were withdrawn
- `$withdrawn_count` - (int) Number of consents withdrawn
- `$type` - (string) Consent type filter (empty for all types)

**Example:**

```php
add_action('slos_bulk_consent_withdrawn', function($user_id, $withdrawn_count, $type) {
    error_log('Bulk withdrawal: ' . $withdrawn_count . ' consents for user ' . $user_id);
}, 10, 3);
```

---

### `slos_plugin_activated`

Fires when the plugin is activated

**Example:**

```php
add_action('slos_plugin_activated', function() {
    // Custom activation logic
});
```

---

### `slos_plugin_deactivated`

Fires when the plugin is deactivated

**Example:**

```php
add_action('slos_plugin_deactivated', function() {
    // Custom deactivation logic
});
```

---

### `slos_migrations_completed`

Fires after all database migrations are completed

**Parameters:**

- `$migrated_tables` - (array) List of tables that were migrated

**Example:**

```php
add_action('slos_migrations_completed', function($migrated_tables) {
    // Post-migration setup
}, 10, 1);
```

---

### `slos_rest_api_init`

Fires when REST API routes are being registered

**Example:**

```php
add_action('slos_rest_api_init', function() {
    // Register custom REST routes
});
```

---

### `slos_rest_authentication`

Fires during REST API authentication checks

**Parameters:**

- `$user_id` - (int) The authenticated user ID (0 if not authenticated)

**Example:**

```php
add_action('slos_rest_authentication', function($user_id) {
    // Custom authentication logging
}, 10, 1);
```

---

## Filter Hooks

Filter hooks allow you to modify data before it's used by the plugin.

### `slos_consent_data_before_save`

Filter consent data before saving to database

**Parameters:**

- `$consent_data` - (array) The consent data to be saved
- `$context` - (string) Context: "create" or "update"

**Return:** (array) Modified consent data

**Example:**

```php
add_filter('slos_consent_data_before_save', function($consent_data, $context) {
    // Modify consent data before save
    $consent_data['custom_field'] = 'custom_value';
    return $consent_data;
}, 10, 2);
```

---

### `slos_consent_metadata`

Filter consent metadata before saving

**Parameters:**

- `$metadata` - (array) Metadata array
- `$consent_type` - (string) Type of consent

**Return:** (array) Modified metadata

**Example:**

```php
add_filter('slos_consent_metadata', function($metadata, $consent_type) {
    $metadata['additional_info'] = 'value';
    return $metadata;
}, 10, 2);
```

---

### `slos_allowed_consent_types`

Filter the allowed consent types

**Parameters:**

- `$types` - (array) Default allowed consent types

**Return:** (array) Modified list of consent types

**Example:**

```php
add_filter('slos_allowed_consent_types', function($types) {
    $types[] = 'custom_type';
    return $types;
});
```

---

### `slos_allowed_consent_statuses`

Filter the allowed consent statuses

**Parameters:**

- `$statuses` - (array) Default allowed consent statuses

**Return:** (array) Modified list of consent statuses

**Example:**

```php
add_filter('slos_allowed_consent_statuses', function($statuses) {
    $statuses[] = 'pending';
    return $statuses;
});
```

---

### `slos_validate_consent_data`

Filter validation result for consent data

**Parameters:**

- `$is_valid` - (bool) Whether data is valid
- `$consent_data` - (array) The consent data being validated
- `$errors` - (array) Validation errors

**Return:** (bool) Modified validation result

**Example:**

```php
add_filter('slos_validate_consent_data', function($is_valid, $consent_data, $errors) {
    // Custom validation logic
    return $is_valid;
}, 10, 3);
```

---

### `slos_consent_query_args`

Filter query arguments for consent retrieval

**Parameters:**

- `$args` - (array) Query arguments
- `$context` - (string) Query context

**Return:** (array) Modified query arguments

**Example:**

```php
add_filter('slos_consent_query_args', function($args, $context) {
    $args['limit'] = 50;
    return $args;
}, 10, 2);
```

---

### `slos_consent_statistics`

Filter consent statistics before returning

**Parameters:**

- `$stats` - (array) Statistics data

**Return:** (array) Modified statistics

**Example:**

```php
add_filter('slos_consent_statistics', function($stats) {
    // Add custom statistics
    return $stats;
});
```

---

### `slos_rest_consent_response`

Filter consent data before REST API response

**Parameters:**

- `$consent_data` - (array) Prepared consent data
- `$consent` - (object) Raw consent object

**Return:** (array) Modified consent data for response

**Example:**

```php
add_filter('slos_rest_consent_response', function($consent_data, $consent) {
    unset($consent_data['ip_hash']); // Remove sensitive data
    return $consent_data;
}, 10, 2);
```

---

### `slos_rest_error_response`

Filter REST API error response

**Parameters:**

- `$error_data` - (array) Error response data
- `$error_code` - (string) Error code

**Return:** (array) Modified error response

**Example:**

```php
add_filter('slos_rest_error_response', function($error_data, $error_code) {
    // Customize error messages
    return $error_data;
}, 10, 2);
```

---

### `slos_ip_hash_algorithm`

Filter the hashing algorithm for IP addresses

**Parameters:**

- `$algorithm` - (string) Hash algorithm (default: sha256)

**Return:** (string) Modified hash algorithm

**Example:**

```php
add_filter('slos_ip_hash_algorithm', function($algorithm) {
    return 'sha512'; // Use stronger hashing
});
```

---

### `slos_anonymize_consent_data`

Filter consent data anonymization

**Parameters:**

- `$anonymized_data` - (array) Anonymized consent data
- `$original_data` - (array) Original consent data

**Return:** (array) Modified anonymized data

**Example:**

```php
add_filter('slos_anonymize_consent_data', function($anonymized_data, $original_data) {
    // Custom anonymization logic
    return $anonymized_data;
}, 10, 2);
```

---

### `slos_user_can_withdraw_consent`

Filter whether a user can withdraw consent

**Parameters:**

- `$can_withdraw` - (bool) Whether user can withdraw
- `$consent_id` - (int) Consent ID
- `$user_id` - (int) User ID

**Return:** (bool) Modified permission

**Example:**

```php
add_filter('slos_user_can_withdraw_consent', function($can_withdraw, $consent_id, $user_id) {
    // Custom permission logic
    return $can_withdraw;
}, 10, 3);
```

---

### `slos_user_can_view_consent`

Filter whether a user can view consent details

**Parameters:**

- `$can_view` - (bool) Whether user can view
- `$consent_id` - (int) Consent ID
- `$user_id` - (int) User ID

**Return:** (bool) Modified permission

**Example:**

```php
add_filter('slos_user_can_view_consent', function($can_view, $consent_id, $user_id) {
    // Custom permission logic
    return $can_view;
}, 10, 3);
```

---

## Hook Usage Best Practices

### Priority and Arguments

- Use appropriate priority values (default is 10)
- Always specify the number of arguments your callback accepts
- Lower priority numbers run earlier

### Performance

- Keep hook callbacks lightweight
- Avoid database queries in frequently-fired hooks
- Use caching when appropriate

### Error Handling

- Always validate data in filter callbacks
- Return the original value if validation fails
- Log errors for debugging

### Examples

#### Logging Consent Activity

```php
add_action('slos_consent_recorded', function($consent_id, $consent_data) {
    $log_entry = sprintf(
        '[%s] User %d gave %s consent for %s',
        current_time('mysql'),
        $consent_data['user_id'],
        $consent_data['status'],
        $consent_data['type']
    );
    error_log($log_entry);
}, 10, 2);
```

#### Custom Consent Validation

```php
add_filter('slos_validate_consent_data', function($is_valid, $consent_data, $errors) {
    // Require consent text for marketing consents
    if ($consent_data['type'] === 'marketing' && empty($consent_data['consent_text'])) {
        return false;
    }
    return $is_valid;
}, 10, 3);
```

#### Extending Consent Types

```php
add_filter('slos_allowed_consent_types', function($types) {
    $types[] = 'research';
    $types[] = 'third_party_sharing';
    return $types;
});
```
