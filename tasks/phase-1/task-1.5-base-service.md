# TASK 1.5: Create Base Service Layer

**Phase:** 1 (Infrastructure & Database)  
**Effort:** 4-6 hours  
**Prerequisites:** TASK 1.4 complete (Base_Repository exists)  
**Next Task:** [task-1.6-rest-api-infrastructure.md](task-1.6-rest-api-infrastructure.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 1.5 for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
Base Repository exists (Task 1.4 complete). Now create the Base Service layer that provides
common functionality for all service classes: caching, WordPress hooks, validation, logging,
and error handling. This establishes clean architecture separation.

INPUT STATE (verify these exist):
✅ Base_Repository exists at includes/Database/Repositories/Base_Repository.php
✅ Namespace structure: Shahi\LegalOps\Services
✅ Composer autoloader configured

YOUR TASK:

1. **Create Base_Service.php**

Location: `includes/Services/Base_Service.php`

```php
<?php
/**
 * Base Service Class
 * Provides common service functionality for all service classes.
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

namespace Shahi\LegalOps\Services;

use WP_Error;

abstract class Base_Service {
    /**
     * Cache group
     *
     * @var string
     */
    protected $cache_group = 'slos';

    /**
     * Cache expiration (seconds)
     *
     * @var int
     */
    protected $cache_expiration = 3600;

    /**
     * Get from cache
     *
     * @param string $key Cache key
     * @return mixed Cached value or false
     */
    protected function get_cache( string $key ) {
        return wp_cache_get( $key, $this->cache_group );
    }

    /**
     * Set cache
     *
     * @param string $key        Cache key
     * @param mixed  $value      Value to cache
     * @param int    $expiration Cache expiration (optional)
     * @return bool True on success
     */
    protected function set_cache( string $key, $value, int $expiration = null ): bool {
        $expiration = $expiration ?? $this->cache_expiration;
        return wp_cache_set( $key, $value, $this->cache_group, $expiration );
    }

    /**
     * Delete cache
     *
     * @param string $key Cache key
     * @return bool True on success
     */
    protected function delete_cache( string $key ): bool {
        return wp_cache_delete( $key, $this->cache_group );
    }

    /**
     * Flush all cache for this service
     *
     * @return bool True on success
     */
    protected function flush_cache(): bool {
        return wp_cache_flush_group( $this->cache_group );
    }

    /**
     * Trigger action hook
     *
     * @param string $action Action name (will be prefixed with 'slos_')
     * @param mixed  ...$args Action arguments
     * @return void
     */
    protected function do_action( string $action, ...$args ): void {
        do_action( "slos_{$action}", ...$args );
    }

    /**
     * Apply filter hook
     *
     * @param string $filter Filter name (will be prefixed with 'slos_')
     * @param mixed  $value  Value to filter
     * @param mixed  ...$args Additional arguments
     * @return mixed Filtered value
     */
    protected function apply_filter( string $filter, $value, ...$args ) {
        return apply_filters( "slos_{$filter}", $value, ...$args );
    }

    /**
     * Validate required fields
     *
     * @param array $data     Data to validate
     * @param array $required Required field names
     * @return true|WP_Error True on success, WP_Error on failure
     */
    protected function validate_required( array $data, array $required ) {
        $missing = [];

        foreach ( $required as $field ) {
            if ( ! isset( $data[ $field ] ) || '' === $data[ $field ] ) {
                $missing[] = $field;
            }
        }

        if ( ! empty( $missing ) ) {
            return new WP_Error(
                'missing_required_fields',
                sprintf(
                    'Missing required fields: %s',
                    implode( ', ', $missing )
                ),
                [ 'fields' => $missing ]
            );
        }

        return true;
    }

    /**
     * Validate email format
     *
     * @param string $email Email address
     * @return true|WP_Error True if valid, error otherwise
     */
    protected function validate_email( string $email ) {
        if ( ! is_email( $email ) ) {
            return new WP_Error(
                'invalid_email',
                'Invalid email address format',
                [ 'email' => $email ]
            );
        }
        return true;
    }

    /**
     * Validate user exists
     *
     * @param int $user_id User ID
     * @return true|WP_Error True if exists, error otherwise
     */
    protected function validate_user_exists( int $user_id ) {
        if ( ! get_userdata( $user_id ) ) {
            return new WP_Error(
                'user_not_found',
                'User does not exist',
                [ 'user_id' => $user_id ]
            );
        }
        return true;
    }

    /**
     * Validate date format
     *
     * @param string $date   Date string
     * @param string $format Expected format (default: Y-m-d H:i:s)
     * @return true|WP_Error True if valid, error otherwise
     */
    protected function validate_date( string $date, string $format = 'Y-m-d H:i:s' ) {
        $d = \DateTime::createFromFormat( $format, $date );
        if ( ! $d || $d->format( $format ) !== $date ) {
            return new WP_Error(
                'invalid_date',
                'Invalid date format',
                [ 'date' => $date, 'expected_format' => $format ]
            );
        }
        return true;
    }

    /**
     * Log error
     *
     * @param string $message Error message
     * @param array  $context Error context
     * @return void
     */
    protected function log_error( string $message, array $context = [] ): void {
        $class = get_class( $this );
        $context_json = ! empty( $context ) ? ' ' . wp_json_encode( $context ) : '';
        error_log( "[SLOS] {$class}: {$message}{$context_json}" );
    }

    /**
     * Log info
     *
     * @param string $message Info message
     * @param array  $context Context data
     * @return void
     */
    protected function log_info( string $message, array $context = [] ): void {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $class = get_class( $this );
            $context_json = ! empty( $context ) ? ' ' . wp_json_encode( $context ) : '';
            error_log( "[SLOS INFO] {$class}: {$message}{$context_json}" );
        }
    }

    /**
     * Sanitize text field
     *
     * @param string $value Value to sanitize
     * @return string Sanitized value
     */
    protected function sanitize_text( string $value ): string {
        return sanitize_text_field( $value );
    }

    /**
     * Sanitize HTML content
     *
     * @param string $value HTML content
     * @return string Sanitized HTML
     */
    protected function sanitize_html( string $value ): string {
        return wp_kses_post( $value );
    }

    /**
     * Sanitize array of text fields
     *
     * @param array $data Array to sanitize
     * @return array Sanitized array
     */
    protected function sanitize_array( array $data ): array {
        return array_map( 'sanitize_text_field', $data );
    }

    /**
     * Check user capability
     *
     * @param string $capability Capability to check
     * @param int    $user_id    User ID (optional, defaults to current user)
     * @return true|WP_Error True if has capability, error otherwise
     */
    protected function check_capability( string $capability, int $user_id = null ) {
        $user_id = $user_id ?? get_current_user_id();
        
        if ( ! user_can( $user_id, $capability ) ) {
            return new WP_Error(
                'insufficient_permissions',
                'User does not have required capability',
                [ 'capability' => $capability, 'user_id' => $user_id ]
            );
        }

        return true;
    }

    /**
     * Get current user IP address
     *
     * @return string IP address
     */
    protected function get_user_ip(): string {
        $ip = '';

        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return sanitize_text_field( $ip );
    }

    /**
     * Get user agent string
     *
     * @return string User agent
     */
    protected function get_user_agent(): string {
        return sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' );
    }

    /**
     * Generate unique identifier
     *
     * @return string Unique ID
     */
    protected function generate_unique_id(): string {
        return wp_generate_uuid4();
    }

    /**
     * Paginate results
     *
     * @param array $items       Items to paginate
     * @param int   $page        Current page (1-indexed)
     * @param int   $per_page    Items per page
     * @return array Paginated results with metadata
     */
    protected function paginate( array $items, int $page = 1, int $per_page = 20 ): array {
        $total = count( $items );
        $total_pages = (int) ceil( $total / $per_page );
        $offset = ( $page - 1 ) * $per_page;
        
        return [
            'items'       => array_slice( $items, $offset, $per_page ),
            'total'       => $total,
            'per_page'    => $per_page,
            'current_page' => $page,
            'total_pages' => $total_pages,
            'has_more'    => $page < $total_pages,
        ];
    }

    /**
     * Format date for output
     *
     * @param string $date   Date string
     * @param string $format Output format
     * @return string Formatted date
     */
    protected function format_date( string $date, string $format = null ): string {
        $format = $format ?? get_option( 'date_format' );
        return mysql2date( $format, $date );
    }

    /**
     * Check if request is AJAX
     *
     * @return bool True if AJAX request
     */
    protected function is_ajax(): bool {
        return wp_doing_ajax();
    }

    /**
     * Check if request is REST API
     *
     * @return bool True if REST request
     */
    protected function is_rest(): bool {
        return defined( 'REST_REQUEST' ) && REST_REQUEST;
    }
}
```

2. **Update composer.json**

Add to psr-4 section:
```json
"Shahi\\LegalOps\\Services\\": "includes/Services/"
```

Run:
```bash
composer dump-autoload
```

3. **Create Test Suite**

Location: `includes/Services/test-base-service.php`

```php
<?php
/**
 * Base Service Test Suite
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Shahi\LegalOps\Services\Base_Service;

// Create test service
class Test_Service extends Base_Service {
    public function test_cache() {
        $key = 'test_key';
        $value = 'test_value';
        
        $this->set_cache( $key, $value );
        return $this->get_cache( $key );
    }

    public function test_validation() {
        return $this->validate_required(
            [ 'name' => 'John', 'email' => 'john@example.com' ],
            [ 'name', 'email' ]
        );
    }

    public function test_missing_fields() {
        return $this->validate_required(
            [ 'name' => 'John' ],
            [ 'name', 'email' ]
        );
    }

    public function test_email_validation() {
        return $this->validate_email( 'john@example.com' );
    }

    public function test_invalid_email() {
        return $this->validate_email( 'not-an-email' );
    }

    public function test_hooks() {
        $this->do_action( 'test_action', 'param1', 'param2' );
        return $this->apply_filter( 'test_filter', 'original_value' );
    }

    public function test_sanitization() {
        return $this->sanitize_text( '<script>alert("xss")</script>Hello' );
    }

    public function test_pagination() {
        $items = range( 1, 50 );
        return $this->paginate( $items, 2, 10 );
    }

    public function test_ip() {
        return $this->get_user_ip();
    }

    public function test_uuid() {
        return $this->generate_unique_id();
    }
}

echo "🧪 Base Service Test Suite\n";
echo "===========================\n\n";

$service = new Test_Service();

// Test 1: Cache
echo "Test 1: Cache Get/Set\n";
$cached = $service->test_cache();
echo $cached === 'test_value' ? "✅ Cache working\n\n" : "❌ Cache failed\n\n";

// Test 2: Validation (success)
echo "Test 2: Validate Required (success)\n";
$result = $service->test_validation();
echo $result === true ? "✅ Validation passed\n\n" : "❌ Validation failed\n\n";

// Test 3: Validation (missing fields)
echo "Test 3: Validate Required (missing fields)\n";
$result = $service->test_missing_fields();
echo is_wp_error( $result ) ? "✅ Correctly detected missing fields: " . $result->get_error_message() . "\n\n" : "❌ Should have failed\n\n";

// Test 4: Email validation (valid)
echo "Test 4: Email Validation (valid)\n";
$result = $service->test_email_validation();
echo $result === true ? "✅ Email valid\n\n" : "❌ Should be valid\n\n";

// Test 5: Email validation (invalid)
echo "Test 5: Email Validation (invalid)\n";
$result = $service->test_invalid_email();
echo is_wp_error( $result ) ? "✅ Correctly rejected invalid email\n\n" : "❌ Should have failed\n\n";

// Test 6: Hooks
echo "Test 6: WordPress Hooks\n";
add_filter( 'slos_test_filter', function( $value ) {
    return 'filtered_value';
} );
$result = $service->test_hooks();
echo $result === 'filtered_value' ? "✅ Hooks working\n\n" : "❌ Hooks failed\n\n";

// Test 7: Sanitization
echo "Test 7: Sanitization\n";
$result = $service->test_sanitization();
echo ! str_contains( $result, '<script>' ) ? "✅ Sanitized: {$result}\n\n" : "❌ Not sanitized\n\n";

// Test 8: Pagination
echo "Test 8: Pagination\n";
$result = $service->test_pagination();
echo count( $result['items'] ) === 10 && $result['current_page'] === 2 ? "✅ Pagination working\n\n" : "❌ Pagination failed\n\n";

// Test 9: IP address
echo "Test 9: Get IP Address\n";
$ip = $service->test_ip();
echo ! empty( $ip ) ? "✅ IP: {$ip}\n\n" : "❌ No IP\n\n";

// Test 10: UUID
echo "Test 10: Generate UUID\n";
$uuid = $service->test_uuid();
echo strlen( $uuid ) === 36 ? "✅ UUID: {$uuid}\n\n" : "❌ Invalid UUID\n\n";

echo "===========================\n";
echo "✅ All tests complete!\n";
```

4. **Run tests**
```bash
wp eval-file includes/Services/test-base-service.php
```

OUTPUT STATE:
✅ Base_Service.php with 30+ utility methods
✅ Caching infrastructure
✅ WordPress hooks integration
✅ Validation helpers
✅ Sanitization methods
✅ Logging utilities
✅ All tests passing

VERIFICATION:

1. **Check file exists:**
```bash
ls -la includes/Services/Base_Service.php
wc -l includes/Services/Base_Service.php
```
Expected: ~400+ lines

2. **Check PHP syntax:**
```bash
php -l includes/Services/Base_Service.php
```

3. **Check methods:**
```bash
grep -E "protected function" includes/Services/Base_Service.php | wc -l
```
Expected: 25+ methods

4. **Run tests:**
```bash
wp eval-file includes/Services/test-base-service.php
```
Expected: All 10 tests pass

5. **Test autoloader:**
```bash
wp eval 'use Shahi\LegalOps\Services\Base_Service; var_dump(class_exists("Shahi\LegalOps\Services\Base_Service"));'
```
Expected: bool(true)

SUCCESS CRITERIA:
✅ Base_Service.php created
✅ All utility methods implemented
✅ PHP syntax valid
✅ Autoloader working
✅ All tests passing (10/10)
✅ Service layer foundation ready

ROLLBACK:
```bash
rm -rf includes/Services/
git checkout composer.json
composer dump-autoload
```

TROUBLESHOOTING:

**Problem 1: Class not found**
```bash
composer dump-autoload -o
```

**Problem 2: Cache not working**
```bash
wp eval 'var_dump(wp_using_ext_object_cache());'
# If false, that's OK - internal cache still works
```

**Problem 3: Hooks not firing**
```bash
wp eval 'add_action("slos_test", function() { echo "Hook works\n"; }); do_action("slos_test");'
```

COMMIT MESSAGE:
```
feat(services): Create Base Service layer

- Add Base_Service abstract class with 25+ utility methods
- Implement caching layer (get, set, delete, flush)
- Add WordPress hooks integration (actions, filters)
- Create validation helpers (required, email, date, user)
- Add sanitization methods (text, HTML, arrays)
- Implement logging (error, info)
- Add utility methods (IP, user agent, UUID, pagination)
- Comprehensive test suite (10 tests)

Service layer foundation complete.

Task: 1.5 (4-6 hours)
Next: Task 1.6 - REST API Infrastructure
```

WHAT TO REPORT BACK:
"✅ TASK 1.5 COMPLETE

Created:
- Base_Service.php (400+ lines, 25+ methods)
- Test suite (10 tests)

Implemented:
- ✅ Caching layer
- ✅ WordPress hooks (actions & filters)
- ✅ Validation helpers
- ✅ Sanitization methods
- ✅ Logging utilities
- ✅ Helper methods (IP, UUID, pagination, etc.)

Verification passed:
- ✅ All tests passing (10/10)
- ✅ PHP syntax valid
- ✅ Autoloader working
- ✅ Service layer foundation ready

📍 Ready for TASK 1.6: [task-1.6-rest-api-infrastructure.md](task-1.6-rest-api-infrastructure.md)"
```

---

## ✅ COMPLETION CHECKLIST

- [ ] Base_Service.php created
- [ ] All utility methods implemented
- [ ] Composer autoloader updated
- [ ] PHP syntax validated
- [ ] Tests created and passing
- [ ] Committed to git
- [ ] Ready for Task 1.6

---

**Status:** ✅ Ready to execute  
**Time:** 4-6 hours  
**Next:** [task-1.6-rest-api-infrastructure.md](task-1.6-rest-api-infrastructure.md)
