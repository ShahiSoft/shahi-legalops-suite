# TASK 1.4: Create Base Repository Class

**Phase:** 1 (Infrastructure & Database)  
**Effort:** 4-6 hours  
**Prerequisites:** TASK 1.3 complete (database tables exist)  
**Next Task:** [task-1.5-base-service.md](task-1.5-base-service.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 1.4 for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
Database tables exist (Task 1.3 complete). Now create a reusable Repository base class
that provides CRUD operations, query building, and database abstraction.

This follows the Repository Pattern - all database access goes through repositories,
making code testable and database-independent.

INPUT STATE (verify these exist):
✅ Database tables created (7 tables from Task 1.3)
✅ Namespace structure: Shahi\LegalOps\Database
✅ Autoloader configured in composer.json
✅ WordPress global $wpdb available

YOUR TASK:

1. **Create Base_Repository.php**

Location: `includes/Database/Repositories/Base_Repository.php`

Complete PHP code:

```php
<?php
/**
 * Base Repository Class
 * Provides CRUD operations and query building for database tables.
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

namespace Shahi\LegalOps\Database\Repositories;

use wpdb;

abstract class Base_Repository {
    /**
     * WordPress database object
     *
     * @var wpdb
     */
    protected $wpdb;

    /**
     * Table name (without prefix)
     *
     * @var string
     */
    protected $table;

    /**
     * Primary key column
     *
     * @var string
     */
    protected $primary_key = 'id';

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table = $wpdb->prefix . $this->get_table_name();
    }

    /**
     * Get table name (without prefix)
     * Must be implemented by child classes
     *
     * @return string
     */
    abstract protected function get_table_name(): string;

    /**
     * Create new record
     *
     * @param array $data Data to insert
     * @return int|false Insert ID or false on failure
     */
    public function create( array $data ) {
        // Add timestamps if columns exist
        if ( ! isset( $data['created_at'] ) ) {
            $data['created_at'] = current_time( 'mysql' );
        }
        if ( ! isset( $data['updated_at'] ) ) {
            $data['updated_at'] = current_time( 'mysql' );
        }

        $result = $this->wpdb->insert(
            $this->table,
            $data,
            $this->get_format( $data )
        );

        if ( false === $result ) {
            error_log( 'Database insert failed: ' . $this->wpdb->last_error );
            return false;
        }

        return $this->wpdb->insert_id;
    }

    /**
     * Find record by ID
     *
     * @param int $id Record ID
     * @return object|null Record object or null if not found
     */
    public function find( int $id ) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE {$this->primary_key} = %d",
                $id
            )
        );
    }

    /**
     * Find all records
     *
     * @param array $args Query arguments (limit, offset, order_by, order)
     * @return array Array of record objects
     */
    public function find_all( array $args = [] ): array {
        $defaults = [
            'limit'    => 100,
            'offset'   => 0,
            'order_by' => $this->primary_key,
            'order'    => 'DESC',
        ];

        $args = wp_parse_args( $args, $defaults );

        $query = $this->wpdb->prepare(
            "SELECT * FROM {$this->table} ORDER BY {$args['order_by']} {$args['order']} LIMIT %d OFFSET %d",
            $args['limit'],
            $args['offset']
        );

        return $this->wpdb->get_results( $query );
    }

    /**
     * Find records by column value
     *
     * @param string $column Column name
     * @param mixed  $value  Value to search for
     * @param array  $args   Query arguments
     * @return array Array of record objects
     */
    public function find_by( string $column, $value, array $args = [] ): array {
        $defaults = [
            'limit'    => 100,
            'offset'   => 0,
            'order_by' => $this->primary_key,
            'order'    => 'DESC',
        ];

        $args = wp_parse_args( $args, $defaults );

        $query = $this->wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE {$column} = %s ORDER BY {$args['order_by']} {$args['order']} LIMIT %d OFFSET %d",
            $value,
            $args['limit'],
            $args['offset']
        );

        return $this->wpdb->get_results( $query );
    }

    /**
     * Update record by ID
     *
     * @param int   $id   Record ID
     * @param array $data Data to update
     * @return bool True on success, false on failure
     */
    public function update( int $id, array $data ): bool {
        // Update timestamp
        if ( ! isset( $data['updated_at'] ) ) {
            $data['updated_at'] = current_time( 'mysql' );
        }

        $result = $this->wpdb->update(
            $this->table,
            $data,
            [ $this->primary_key => $id ],
            $this->get_format( $data ),
            [ '%d' ]
        );

        if ( false === $result ) {
            error_log( 'Database update failed: ' . $this->wpdb->last_error );
            return false;
        }

        return true;
    }

    /**
     * Delete record by ID
     *
     * @param int $id Record ID
     * @return bool True on success, false on failure
     */
    public function delete( int $id ): bool {
        $result = $this->wpdb->delete(
            $this->table,
            [ $this->primary_key => $id ],
            [ '%d' ]
        );

        if ( false === $result ) {
            error_log( 'Database delete failed: ' . $this->wpdb->last_error );
            return false;
        }

        return true;
    }

    /**
     * Count total records
     *
     * @param array $where Where conditions
     * @return int Total count
     */
    public function count( array $where = [] ): int {
        $query = "SELECT COUNT(*) FROM {$this->table}";

        if ( ! empty( $where ) ) {
            $conditions = [];
            $values     = [];

            foreach ( $where as $column => $value ) {
                $conditions[] = "{$column} = %s";
                $values[]     = $value;
            }

            $query .= ' WHERE ' . implode( ' AND ', $conditions );
            $query  = $this->wpdb->prepare( $query, $values );
        }

        return (int) $this->wpdb->get_var( $query );
    }

    /**
     * Check if record exists
     *
     * @param int $id Record ID
     * @return bool True if exists, false otherwise
     */
    public function exists( int $id ): bool {
        $count = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table} WHERE {$this->primary_key} = %d",
                $id
            )
        );

        return $count > 0;
    }

    /**
     * Get data format array for wpdb methods
     *
     * @param array $data Data array
     * @return array Format array (%s, %d, %f)
     */
    protected function get_format( array $data ): array {
        $format = [];

        foreach ( $data as $key => $value ) {
            if ( is_int( $value ) ) {
                $format[] = '%d';
            } elseif ( is_float( $value ) ) {
                $format[] = '%f';
            } else {
                $format[] = '%s';
            }
        }

        return $format;
    }

    /**
     * Get last database error
     *
     * @return string Error message
     */
    public function get_last_error(): string {
        return $this->wpdb->last_error;
    }

    /**
     * Begin transaction
     *
     * @return void
     */
    public function begin_transaction(): void {
        $this->wpdb->query( 'START TRANSACTION' );
    }

    /**
     * Commit transaction
     *
     * @return void
     */
    public function commit(): void {
        $this->wpdb->query( 'COMMIT' );
    }

    /**
     * Rollback transaction
     *
     * @return void
     */
    public function rollback(): void {
        $this->wpdb->query( 'ROLLBACK' );
    }
}
```

2. **Create Example Repository (Consent_Repository)**

Location: `includes/Database/Repositories/Consent_Repository.php`

```php
<?php
/**
 * Consent Repository
 * Handles database operations for consent records.
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

namespace Shahi\LegalOps\Database\Repositories;

class Consent_Repository extends Base_Repository {
    /**
     * Get table name
     *
     * @return string
     */
    protected function get_table_name(): string {
        return 'slos_consent';
    }

    /**
     * Find consents by user ID
     *
     * @param int $user_id User ID
     * @return array Array of consent records
     */
    public function find_by_user( int $user_id ): array {
        return $this->find_by( 'user_id', $user_id );
    }

    /**
     * Find consents by purpose
     *
     * @param string $purpose Purpose (analytics, marketing, etc)
     * @return array Array of consent records
     */
    public function find_by_purpose( string $purpose ): array {
        return $this->find_by( 'purpose', $purpose );
    }

    /**
     * Get active consents for user
     *
     * @param int $user_id User ID
     * @return array Array of active consent records
     */
    public function get_active_consents( int $user_id ): array {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE user_id = %d AND status = 'active' ORDER BY created_at DESC",
                $user_id
            )
        );
    }

    /**
     * Check if user has active consent for purpose
     *
     * @param int    $user_id User ID
     * @param string $purpose Purpose
     * @return bool True if has active consent
     */
    public function has_consent( int $user_id, string $purpose ): bool {
        $count = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table} WHERE user_id = %d AND purpose = %s AND status = 'active'",
                $user_id,
                $purpose
            )
        );

        return $count > 0;
    }

    /**
     * Withdraw consent
     *
     * @param int $consent_id Consent ID
     * @return bool True on success
     */
    public function withdraw( int $consent_id ): bool {
        return $this->update( $consent_id, [
            'status'      => 'withdrawn',
            'withdrawn_at' => current_time( 'mysql' ),
        ] );
    }
}
```

3. **Update composer.json autoloader**

Add to `psr-4` section:
```json
"Shahi\\LegalOps\\Database\\Repositories\\": "includes/Database/Repositories/"
```

4. **Run composer dump-autoload**
```bash
composer dump-autoload
```

5. **Verify with tests**

Create test file: `includes/Database/Repositories/test-base-repository.php`

```php
<?php
// Test Base Repository

require_once __DIR__ . '/../../../vendor/autoload.php';

use Shahi\LegalOps\Database\Repositories\Consent_Repository;

// Initialize repository
$repo = new Consent_Repository();

// Test: Create
echo "Testing create...\n";
$id = $repo->create([
    'user_id'      => 1,
    'purpose'      => 'analytics',
    'status'       => 'active',
    'ip_address'   => '127.0.0.1',
    'user_agent'   => 'Test Agent',
    'consent_text' => 'I agree to analytics',
]);
echo $id ? "✅ Created record ID: {$id}\n" : "❌ Failed to create\n";

// Test: Find
echo "\nTesting find...\n";
$record = $repo->find( $id );
echo $record ? "✅ Found record\n" : "❌ Not found\n";

// Test: Update
echo "\nTesting update...\n";
$updated = $repo->update( $id, [ 'status' => 'withdrawn' ] );
echo $updated ? "✅ Updated\n" : "❌ Failed to update\n";

// Test: Find by user
echo "\nTesting find_by_user...\n";
$user_consents = $repo->find_by_user( 1 );
echo count( $user_consents ) > 0 ? "✅ Found user consents\n" : "❌ No consents found\n";

// Test: Has consent
echo "\nTesting has_consent...\n";
$has = $repo->has_consent( 1, 'analytics' );
echo $has ? "✅ Has consent\n" : "User does not have active consent\n";

// Test: Count
echo "\nTesting count...\n";
$total = $repo->count();
echo "Total records: {$total}\n";

// Test: Delete
echo "\nTesting delete...\n";
$deleted = $repo->delete( $id );
echo $deleted ? "✅ Deleted\n" : "❌ Failed to delete\n";

echo "\n✅ All tests complete!\n";
```

6. **Run the test**
```bash
wp eval-file includes/Database/Repositories/test-base-repository.php
```

OUTPUT STATE (what will exist after this task):
✅ `includes/Database/Repositories/Base_Repository.php` - Abstract base class
✅ `includes/Database/Repositories/Consent_Repository.php` - Concrete implementation
✅ Composer autoloader updated
✅ Tests pass (create, read, update, delete)
✅ Repository pattern established for all future database operations

VERIFICATION (run these commands):

1. **Check files exist:**
```bash
ls -la includes/Database/Repositories/
```
Expected: Base_Repository.php, Consent_Repository.php, test-base-repository.php

2. **Check PHP syntax:**
```bash
php -l includes/Database/Repositories/Base_Repository.php
php -l includes/Database/Repositories/Consent_Repository.php
```
Expected: No syntax errors

3. **Check namespaces:**
```bash
grep -r "namespace Shahi\\\\LegalOps\\\\Database\\\\Repositories" includes/Database/Repositories/
```
Expected: Matches in both files

4. **Run tests:**
```bash
wp eval-file includes/Database/Repositories/test-base-repository.php
```
Expected: All tests pass (✅ symbols)

5. **Check autoloader:**
```bash
composer dump-autoload -o
```
Expected: Success, optimized autoloader generated

SUCCESS CRITERIA:
✅ Base_Repository.php created with all CRUD methods
✅ Consent_Repository.php extends Base_Repository
✅ PHP syntax valid (no errors)
✅ Namespaces correct
✅ Composer autoloader works
✅ All tests pass
✅ Repository pattern established

ROLLBACK (if something fails):
```bash
# Remove files
rm includes/Database/Repositories/Base_Repository.php
rm includes/Database/Repositories/Consent_Repository.php
rm includes/Database/Repositories/test-base-repository.php

# Restore composer.json
git checkout composer.json

# Regenerate autoloader
composer dump-autoload
```

TROUBLESHOOTING:

**Problem 1: Namespace not found**
```bash
# Solution: Regenerate autoloader
composer dump-autoload -o
```

**Problem 2: $wpdb not available**
```bash
# Solution: Check WordPress is loaded
wp eval 'global $wpdb; var_dump($wpdb);'
```

**Problem 3: Tests fail**
```bash
# Solution: Check database tables exist
wp db query "SHOW TABLES LIKE 'wp_slos_%'"
```

**Problem 4: Insert fails**
```bash
# Solution: Check database permissions
wp db query "SELECT current_user();"
wp db query "SHOW GRANTS FOR current_user();"
```

COMMIT MESSAGE:
```
feat(database): Add Base Repository pattern

- Create Base_Repository abstract class with CRUD operations
- Implement Consent_Repository as concrete example
- Add transaction support (begin, commit, rollback)
- Include query building and data formatting
- Add comprehensive test suite
- Update composer autoloader

All tests passing. Repository pattern established for database access.

Task: 1.4 (4-6 hours)
Next: Task 1.5 - Base Service Layer
```

WHAT TO REPORT BACK:
"✅ TASK 1.4 COMPLETE

Created:
- Base_Repository.php (abstract base class)
- Consent_Repository.php (concrete implementation)
- Test suite passing

Verification passed:
- ✅ Files created
- ✅ PHP syntax valid
- ✅ Namespaces correct
- ✅ Autoloader working
- ✅ All tests passing (create, read, update, delete)

Repository pattern ready. All future database access will use repositories.

📍 Ready for TASK 1.5: [task-1.5-base-service.md](task-1.5-base-service.md)"
```

---

## ✅ COMPLETION CHECKLIST

- [ ] Base_Repository.php created with all methods
- [ ] Consent_Repository.php created (concrete example)
- [ ] Composer autoloader updated
- [ ] All PHP syntax valid
- [ ] Tests run and pass
- [ ] Repository pattern documented
- [ ] Changes committed to git
- [ ] Verification commands run
- [ ] Ready for Task 1.5

---

**Status:** ✅ Ready to execute  
**Time:** 4-6 hours  
**Next:** [task-1.5-base-service.md](task-1.5-base-service.md)
