# 🤖 AI TASK PROMPTS - PHASE 2 COMPLETE

**Purpose:** Detailed, comprehensive prompts for Phase 2 tasks (Consent Management Module)  
**Version:** 1.0  
**Date:** December 19, 2025  
**Tasks Covered:** TASK 2.1 through TASK 2.15  

---

# PHASE 2: CONSENT MANAGEMENT IMPLEMENTATION (15 Tasks)

---

## PROMPT: TASK 2.1 - Consent Repository & Data Layer

```
You are implementing TASK 2.1 of the Shahi LegalOps Suite plugin.

TASK: Consent Repository & Data Layer
PHASE: 2 (Consent Management - CORE)
EFFORT: 8-10 hours
DEPENDENCY: TASK 1.3 (Database tables created), TASK 1.4 (Base repository class exists)

CONTEXT:
The Consent module is the core of the entire plugin. Users must explicitly consent to different 
data tracking categories (analytics, marketing, personalization, etc.). This task creates the 
data access layer for the consent system.

The Repository Pattern separates database queries from business logic. This keeps code clean, 
testable, and maintainable. Every CRUD operation goes through the repository.

INPUT STATE (verify these exist):
- [ ] TASK 1.3 complete: wp_complyflow_consent table exists
- [ ] TASK 1.4 complete: includes/Core/BaseRepository.php exists
- [ ] includes/Modules/ directory exists (where you'll create Consent module)
- [ ] WordPress database accessible
- [ ] Composer autoloader working

SPECIFICATIONS:

The ConsentRepository must support:
1. Create consent records (user consents to a category)
2. Read consent records (check what user consented to)
3. Update consent records (user changes their mind)
4. Delete consent records (not common, but needed for cleanup)
5. Query consents by user ID
6. Query consents by IP hash (for anonymous users)
7. Query consents by type (get all analytics consents, etc.)
8. Check if user has specific consent

DATABASE OPERATIONS:

Insert (CREATE):
```sql
INSERT INTO wp_complyflow_consent (user_id, ip_hash, type, status, metadata, created_at, updated_at)
VALUES (123, 'hash123', 'analytics', 'accepted', '{}', NOW(), NOW())
```

Read (RETRIEVE):
```sql
SELECT * FROM wp_complyflow_consent WHERE id = 1
SELECT * FROM wp_complyflow_consent WHERE user_id = 123 AND type = 'analytics'
SELECT * FROM wp_complyflow_consent WHERE ip_hash = 'hash123'
```

Update (EDIT):
```sql
UPDATE wp_complyflow_consent SET status = 'withdrawn', updated_at = NOW() WHERE id = 1
```

Delete (REMOVE):
```sql
DELETE FROM wp_complyflow_consent WHERE id = 1
```

YOUR TASK:

1. CREATE CONSENT MODEL CLASS
   
   Create file: includes/Modules/Consent/ConsentModel.php
   
   Content:
   ```php
   <?php
   /**
    * Consent Model
    * Represents a single consent record
    * 
    * @since 3.0.1
    * @package ComplyFlow\Modules\Consent
    */
   
   namespace ComplyFlow\Modules\Consent;
   
   class ConsentModel {
       private $id;
       private $user_id;
       private $ip_hash;
       private $type;
       private $status;
       private $metadata;
       private $created_at;
       private $updated_at;
       
       /**
        * Constructor
        */
       public function __construct($data = []) {
           $this->id = $data['id'] ?? null;
           $this->user_id = $data['user_id'] ?? null;
           $this->ip_hash = $data['ip_hash'] ?? null;
           $this->type = $data['type'] ?? null;
           $this->status = $data['status'] ?? null;
           $this->metadata = $data['metadata'] ?? '{}';
           $this->created_at = $data['created_at'] ?? null;
           $this->updated_at = $data['updated_at'] ?? null;
       }
       
       // Getters
       public function get_id() { return $this->id; }
       public function get_user_id() { return $this->user_id; }
       public function get_ip_hash() { return $this->ip_hash; }
       public function get_type() { return $this->type; }
       public function get_status() { return $this->status; }
       public function get_metadata() { return json_decode($this->metadata, true) ?? []; }
       public function get_created_at() { return $this->created_at; }
       public function get_updated_at() { return $this->updated_at; }
       
       // Setters
       public function set_user_id($value) { $this->user_id = $value; }
       public function set_type($value) { $this->type = $value; }
       public function set_status($value) { $this->status = $value; }
       public function set_metadata($value) { 
           $this->metadata = is_string($value) ? $value : json_encode($value); 
       }
       
       // Convert to array for database operations
       public function to_array() {
           return [
               'id' => $this->id,
               'user_id' => $this->user_id,
               'ip_hash' => $this->ip_hash,
               'type' => $this->type,
               'status' => $this->status,
               'metadata' => $this->metadata,
               'created_at' => $this->created_at,
               'updated_at' => $this->updated_at,
           ];
       }
       
       // Create from database row
       public static function from_db($row) {
           return new self((array) $row);
       }
   }
   ```

2. CREATE CONSENT REPOSITORY CLASS
   
   Create file: includes/Modules/Consent/ConsentRepository.php
   
   Content:
   ```php
   <?php
   /**
    * Consent Repository
    * Handles all database operations for consent records
    * 
    * @since 3.0.1
    * @package ComplyFlow\Modules\Consent
    */
   
   namespace ComplyFlow\Modules\Consent;
   
   use ComplyFlow\Core\BaseRepository;
   
   class ConsentRepository extends BaseRepository {
       protected $table = 'complyflow_consent';
       
       /**
        * Create a new consent record
        * 
        * @param array $data {
        *     @type int    $user_id     WordPress user ID (optional)
        *     @type string $ip_hash     Hash of IP address
        *     @type string $type        Consent type (analytics, marketing, etc.)
        *     @type string $status      Status (accepted, rejected, withdrawn)
        *     @type array  $metadata    Optional metadata
        * }
        * @return int|false Insert ID or false on failure
        */
       public function create($data) {
           global $wpdb;
           
           // Validate required fields
           if (empty($data['type']) || empty($data['status'])) {
               return false;
           }
           
           // Sanitize data
           $insert_data = [
               'user_id'   => isset($data['user_id']) ? intval($data['user_id']) : null,
               'ip_hash'   => isset($data['ip_hash']) ? sanitize_text_field($data['ip_hash']) : null,
               'type'      => sanitize_text_field($data['type']),
               'status'    => sanitize_text_field($data['status']),
               'metadata'  => isset($data['metadata']) ? wp_json_encode($data['metadata']) : '{}',
           ];
           
           $result = $wpdb->insert(
               $this->get_table_name(),
               $insert_data,
               ['%d', '%s', '%s', '%s', '%s']
           );
           
           return $result ? $wpdb->insert_id : false;
       }
       
       /**
        * Get consent by ID
        * 
        * @param int $id Consent record ID
        * @return ConsentModel|null
        */
       public function get($id) {
           global $wpdb;
           
           $id = intval($id);
           $row = $wpdb->get_row(
               $wpdb->prepare(
                   "SELECT * FROM {$this->get_table_name()} WHERE id = %d",
                   $id
               )
           );
           
           return $row ? ConsentModel::from_db($row) : null;
       }
       
       /**
        * Get all consents for a user
        * 
        * @param int $user_id WordPress user ID
        * @return ConsentModel[] Array of consent records
        */
       public function get_by_user($user_id) {
           global $wpdb;
           
           $user_id = intval($user_id);
           $rows = $wpdb->get_results(
               $wpdb->prepare(
                   "SELECT * FROM {$this->get_table_name()} WHERE user_id = %d ORDER BY created_at DESC",
                   $user_id
               )
           );
           
           return array_map([ConsentModel::class, 'from_db'], $rows ?? []);
       }
       
       /**
        * Get all consents by IP hash (for anonymous users)
        * 
        * @param string $ip_hash Hash of IP address
        * @return ConsentModel[] Array of consent records
        */
       public function get_by_ip_hash($ip_hash) {
           global $wpdb;
           
           $ip_hash = sanitize_text_field($ip_hash);
           $rows = $wpdb->get_results(
               $wpdb->prepare(
                   "SELECT * FROM {$this->get_table_name()} WHERE ip_hash = %s ORDER BY created_at DESC",
                   $ip_hash
               )
           );
           
           return array_map([ConsentModel::class, 'from_db'], $rows ?? []);
       }
       
       /**
        * Get all consents of a specific type
        * 
        * @param string $type Consent type (analytics, marketing, etc.)
        * @return ConsentModel[] Array of consent records
        */
       public function get_by_type($type) {
           global $wpdb;
           
           $type = sanitize_text_field($type);
           $rows = $wpdb->get_results(
               $wpdb->prepare(
                   "SELECT * FROM {$this->get_table_name()} WHERE type = %s ORDER BY created_at DESC",
                   $type
               )
           );
           
           return array_map([ConsentModel::class, 'from_db'], $rows ?? []);
       }
       
       /**
        * Check if user has specific consent
        * 
        * @param int    $user_id Wordpress user ID
        * @param string $type    Consent type to check
        * @return bool True if consent exists and is accepted
        */
       public function has_consent($user_id, $type) {
           global $wpdb;
           
           $user_id = intval($user_id);
           $type = sanitize_text_field($type);
           
           $result = $wpdb->get_var(
               $wpdb->prepare(
                   "SELECT status FROM {$this->get_table_name()} 
                    WHERE user_id = %d AND type = %s AND status = 'accepted'
                    LIMIT 1",
                   $user_id,
                   $type
               )
           );
           
           return !empty($result);
       }
       
       /**
        * Update consent record
        * 
        * @param int   $id   Consent record ID
        * @param array $data Fields to update
        * @return bool Success
        */
       public function update($id, $data) {
           global $wpdb;
           
           $id = intval($id);
           
           // Sanitize update data
           $update_data = [];
           if (isset($data['status'])) {
               $update_data['status'] = sanitize_text_field($data['status']);
           }
           if (isset($data['metadata'])) {
               $update_data['metadata'] = wp_json_encode($data['metadata']);
           }
           if (isset($data['type'])) {
               $update_data['type'] = sanitize_text_field($data['type']);
           }
           
           $update_data['updated_at'] = current_time('mysql');
           
           $result = $wpdb->update(
               $this->get_table_name(),
               $update_data,
               ['id' => $id],
               null,
               ['%d']
           );
           
           return $result !== false;
       }
       
       /**
        * Delete consent record
        * 
        * @param int $id Consent record ID
        * @return bool Success
        */
       public function delete($id) {
           global $wpdb;
           
           $id = intval($id);
           $result = $wpdb->delete(
               $this->get_table_name(),
               ['id' => $id],
               ['%d']
           );
           
           return $result !== false;
       }
   }
   ```

3. CREATE DIRECTORY STRUCTURE
   
   Create these directories:
   ```bash
   mkdir -p includes/Modules/Consent
   mkdir -p includes/Modules/Consent/Tests
   ```

4. CREATE UNIT TESTS FOR REPOSITORY
   
   Create file: includes/Modules/Consent/Tests/ConsentRepositoryTest.php
   
   Content:
   ```php
   <?php
   /**
    * Unit tests for ConsentRepository
    * 
    * @since 3.0.1
    */
   
   namespace ComplyFlow\Modules\Consent\Tests;
   
   use ComplyFlow\Modules\Consent\ConsentRepository;
   
   class ConsentRepositoryTest {
       
       private $repo;
       
       public function setup() {
           $this->repo = new ConsentRepository();
       }
       
       public function test_create_consent() {
           $id = $this->repo->create([
               'user_id' => 1,
               'type' => 'analytics',
               'status' => 'accepted',
           ]);
           
           assert($id > 0, 'Create should return positive ID');
           assert(is_int($id), 'Create should return integer');
       }
       
       public function test_get_consent() {
           $created_id = $this->repo->create([
               'user_id' => 1,
               'type' => 'analytics',
               'status' => 'accepted',
           ]);
           
           $consent = $this->repo->get($created_id);
           
           assert($consent !== null, 'Get should return object, not null');
           assert($consent->get_id() == $created_id, 'ID should match');
           assert($consent->get_type() === 'analytics', 'Type should be analytics');
           assert($consent->get_status() === 'accepted', 'Status should be accepted');
       }
       
       public function test_get_by_user() {
           $user_id = 1;
           $this->repo->create(['user_id' => $user_id, 'type' => 'analytics', 'status' => 'accepted']);
           $this->repo->create(['user_id' => $user_id, 'type' => 'marketing', 'status' => 'rejected']);
           
           $consents = $this->repo->get_by_user($user_id);
           
           assert(count($consents) >= 2, 'Should have at least 2 consents for user');
           assert(all($consents, fn($c) => $c->get_user_id() == $user_id), 
                  'All consents should belong to user');
       }
       
       public function test_has_consent() {
           $this->repo->create([
               'user_id' => 1,
               'type' => 'analytics',
               'status' => 'accepted',
           ]);
           
           assert($this->repo->has_consent(1, 'analytics') === true, 
                  'Should find accepted consent');
           assert($this->repo->has_consent(1, 'marketing') === false, 
                  'Should not find non-existent consent');
       }
       
       public function test_update_consent() {
           $id = $this->repo->create([
               'user_id' => 1,
               'type' => 'analytics',
               'status' => 'accepted',
           ]);
           
           $success = $this->repo->update($id, ['status' => 'withdrawn']);
           assert($success === true, 'Update should return true');
           
           $consent = $this->repo->get($id);
           assert($consent->get_status() === 'withdrawn', 'Status should be updated to withdrawn');
       }
       
       public function test_delete_consent() {
           $id = $this->repo->create([
               'user_id' => 1,
               'type' => 'analytics',
               'status' => 'accepted',
           ]);
           
           $success = $this->repo->delete($id);
           assert($success === true, 'Delete should return true');
           
           $consent = $this->repo->get($id);
           assert($consent === null, 'Deleted consent should return null');
       }
   }
   ```

OUTPUT STATE (what exists after this task):
- [ ] includes/Modules/Consent/ConsentModel.php created
- [ ] includes/Modules/Consent/ConsentRepository.php created
- [ ] includes/Modules/Consent/Tests/ConsentRepositoryTest.php created
- [ ] All files have proper PHP syntax
- [ ] All classes follow repository pattern
- [ ] All database queries use prepared statements
- [ ] All input is sanitized

VERIFICATION (run these):

1. Check files exist:
   Command: ls -la includes/Modules/Consent/
   Expected: ConsentModel.php, ConsentRepository.php, Tests/ConsentRepositoryTest.php

2. Check PHP syntax:
   Command: php -l includes/Modules/Consent/ConsentModel.php && php -l includes/Modules/Consent/ConsentRepository.php
   Expected: "No syntax errors detected" for both files

3. Check autoload:
   Command: wp eval 'echo class_exists("ComplyFlow\\Modules\\Consent\\ConsentRepository") ? "Class loaded" : "Class NOT found";'
   Expected: "Class loaded"

4. Test repository operations:
   Command: wp eval '
   $repo = new \ComplyFlow\Modules\Consent\ConsentRepository();
   $id = $repo->create(["user_id" => 1, "type" => "test", "status" => "accepted"]);
   echo "Create ID: " . $id . "\n";
   $consent = $repo->get($id);
   echo "Retrieved: " . $consent->get_type() . "\n";
   $repo->delete($id);
   echo "Deleted successfully";
   '
   Expected:
   ```
   Create ID: [positive number]
   Retrieved: test
   Deleted successfully
   ```

5. Test sanitization (security check):
   Command: wp eval '
   $repo = new \ComplyFlow\Modules\Consent\ConsentRepository();
   // Try to inject SQL - should be sanitized
   $id = $repo->create(["user_id" => 1, "type" => "x\"; DROP TABLE wp_complyflow_consent; --", "status" => "accepted"]);
   $count = $wpdb->get_var("SELECT COUNT(*) FROM wp_complyflow_consent");
   echo "Table still exists, records: " . $count;
   '
   Expected: Table still exists (injection prevented)

6. Check WP_DEBUG:
   Command: tail -20 wp-content/debug.log | grep -i "error"
   Expected: No errors from repository operations

SUCCESS CRITERIA:
- [ ] ConsentModel class created with all getters/setters
- [ ] ConsentRepository extends BaseRepository
- [ ] All CRUD methods implemented (create, get, update, delete)
- [ ] Query methods implemented (get_by_user, get_by_ip_hash, get_by_type, has_consent)
- [ ] All database queries use $wpdb->prepare()
- [ ] All input is sanitized (sanitize_text_field, intval, wp_json_encode)
- [ ] Unit tests created and all pass
- [ ] No PHP syntax errors
- [ ] No WP_DEBUG errors
- [ ] No security vulnerabilities (SQL injection, data validation)

ROLLBACK (if verification fails):
   1. Delete created files:
      Command: rm -rf includes/Modules/Consent/
   2. No database changes made, only data access layer
   3. Try again with corrected code

NEXT TASK:
After verification, proceed to TASK 2.2 (Consent Service & Business Logic)
```

---

## PROMPT: TASK 2.2 - Consent Service & Business Logic

```
You are implementing TASK 2.2 of the Shahi LegalOps Suite plugin.

TASK: Consent Service & Business Logic
PHASE: 2 (Consent Management - CORE)
EFFORT: 8-10 hours
DEPENDENCY: TASK 2.1 (ConsentRepository created)

CONTEXT:
The Repository handles database access. The Service handles business logic.

For example:
- Repository: "Save this consent to the database"
- Service: "User consented to analytics. Check if they previously rejected it. Update their 
            preference. Log this event. Update the UI. Send confirmation email."

The Service layer is where most of the logic lives. It uses the Repository to persist data, 
but adds validation, business rules, events, and notifications.

INPUT STATE (verify these exist):
- [ ] TASK 2.1 complete: ConsentRepository exists
- [ ] includes/Modules/Consent/ directory exists
- [ ] Database table wp_complyflow_consent exists

SPECIFICATIONS:

ConsentService must:
1. Record consent (with validation)
2. Check if user has given consent
3. Withdraw consent
4. Handle consent preferences update
5. Validate consent types (only allow: necessary, functional, analytics, marketing, personalization)
6. Handle both authenticated users and anonymous visitors (IP-based)
7. Fire WordPress hooks for other plugins to react
8. Return properly formatted responses

CONSENT FLOW:
1. User loads website
2. ConsentService.get_user_preferences() returns what they've consented to
3. If not set, show consent banner
4. User clicks "Accept All" or "Manage Preferences"
5. ConsentService.record_consent() saves their choice
6. WordPress hooks fired: complyflow_consent_updated
7. UI updated via AJAX

YOUR TASK:

1. CREATE CONSENT SERVICE CLASS
   
   Create file: includes/Modules/Consent/ConsentService.php
   
   Content:
   ```php
   <?php
   /**
    * Consent Service
    * Business logic for consent management
    * 
    * @since 3.0.1
    * @package ComplyFlow\Modules\Consent
    */
   
   namespace ComplyFlow\Modules\Consent;
   
   class ConsentService {
       
       private $repo;
       private $valid_types = ['necessary', 'functional', 'analytics', 'marketing', 'personalization'];
       
       public function __construct() {
           $this->repo = new ConsentRepository();
       }
       
       /**
        * Record user consent
        * 
        * @param int    $user_id  WordPress user ID (null for anonymous)
        * @param string $type     Consent type
        * @param string $status   'accepted' or 'rejected'
        * @param string $ip_hash  IP hash for anonymous users (optional)
        * @return array Response with success status and ID
        */
       public function record_consent($user_id, $type, $status, $ip_hash = null) {
           
           // Validate type
           if (!in_array($type, $this->valid_types, true)) {
               return [
                   'success' => false,
                   'error' => "Invalid consent type: $type",
               ];
           }
           
           // Validate status
           if (!in_array($status, ['accepted', 'rejected'], true)) {
               return [
                   'success' => false,
                   'error' => "Invalid status: $status",
               ];
           }
           
           // For anonymous users, must have IP hash
           if (empty($user_id) && empty($ip_hash)) {
               $ip_hash = $this->hash_ip($_SERVER['REMOTE_ADDR'] ?? '');
           }
           
           try {
               $consent_id = $this->repo->create([
                   'user_id' => $user_id ?: null,
                   'ip_hash' => $ip_hash,
                   'type' => $type,
                   'status' => $status,
                   'metadata' => [
                       'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                       'timestamp' => current_time('mysql'),
                   ],
               ]);
               
               if (!$consent_id) {
                   return [
                       'success' => false,
                       'error' => 'Failed to save consent',
                   ];
               }
               
               // Fire WordPress hook for other plugins
               do_action('complyflow_consent_recorded', $user_id, $type, $status, $consent_id);
               
               return [
                   'success' => true,
                   'consent_id' => $consent_id,
                   'message' => "Consent recorded: $type = $status",
               ];
               
           } catch (Exception $e) {
               return [
                   'success' => false,
                   'error' => 'Exception: ' . $e->getMessage(),
               ];
           }
       }
       
       /**
        * Get user's consent preferences
        * 
        * @param int    $user_id  WordPress user ID
        * @param string $ip_hash  IP hash for anonymous users (optional)
        * @return array Consent status for each type
        */
       public function get_user_preferences($user_id = null, $ip_hash = null) {
           
           $consents = [];
           
           if ($user_id) {
               $consents = $this->repo->get_by_user($user_id);
           } elseif ($ip_hash) {
               $consents = $this->repo->get_by_ip_hash($ip_hash);
           } else {
               // No user and no IP - return defaults
               return $this->get_default_preferences();
           }
           
           $preferences = [];
           foreach ($this->valid_types as $type) {
               // Find most recent consent for this type
               $consent = array_reduce($consents, function($carry, $consent) use ($type) {
                   if ($consent->get_type() === $type) {
                       if (!$carry || $carry->get_updated_at() < $consent->get_updated_at()) {
                           return $consent;
                       }
                   }
                   return $carry;
               }, null);
               
               if ($consent) {
                   $preferences[$type] = $consent->get_status();
               } else {
                   $preferences[$type] = 'not_asked';
               }
           }
           
           return $preferences;
       }
       
       /**
        * Withdraw consent for a specific type
        * 
        * @param int    $user_id WordPress user ID
        * @param string $type    Consent type
        * @return array Response
        */
       public function withdraw_consent($user_id, $type) {
           
           if (!in_array($type, $this->valid_types, true)) {
               return [
                   'success' => false,
                   'error' => "Invalid consent type: $type",
               ];
           }
           
           try {
               // Get current consent
               $consents = $this->repo->get_by_user($user_id);
               $current = array_reduce($consents, function($carry, $consent) use ($type) {
                   if ($consent->get_type() === $type && $consent->get_status() === 'accepted') {
                       return $consent;
                   }
                   return $carry;
               }, null);
               
               if (!$current) {
                   return [
                       'success' => false,
                       'error' => 'No active consent found to withdraw',
                   ];
               }
               
               // Update to withdrawn status
               $this->repo->update($current->get_id(), ['status' => 'withdrawn']);
               
               // Fire hook
               do_action('complyflow_consent_withdrawn', $user_id, $type);
               
               return [
                   'success' => true,
                   'message' => "Consent withdrawn: $type",
               ];
               
           } catch (Exception $e) {
               return [
                   'success' => false,
                   'error' => 'Exception: ' . $e->getMessage(),
               ];
           }
       }
       
       /**
        * Check if user has specific consent
        * 
        * @param int    $user_id WordPress user ID
        * @param string $type    Consent type to check
        * @return bool True if consent exists and is accepted
        */
       public function has_consent($user_id, $type) {
           return $this->repo->has_consent($user_id, $type);
       }
       
       /**
        * Get default preferences (for new users)
        * 
        * @return array
        */
       private function get_default_preferences() {
           $preferences = [];
           foreach ($this->valid_types as $type) {
               // 'necessary' is always pre-consented per GDPR
               $preferences[$type] = ($type === 'necessary') ? 'accepted' : 'not_asked';
           }
           return $preferences;
       }
       
       /**
        * Hash IP address for privacy
        * 
        * @param string $ip IP address
        * @return string SHA256 hash of IP
        */
       private function hash_ip($ip) {
           return hash('sha256', $ip);
       }
       
       /**
        * Get valid consent types
        * 
        * @return array Valid consent type names
        */
       public function get_valid_types() {
           return $this->valid_types;
       }
   }
   ```

2. CREATE SERVICE TESTS
   
   Create file: includes/Modules/Consent/Tests/ConsentServiceTest.php
   
   Content:
   ```php
   <?php
   /**
    * Unit tests for ConsentService
    */
   
   namespace ComplyFlow\Modules\Consent\Tests;
   
   use ComplyFlow\Modules\Consent\ConsentService;
   
   class ConsentServiceTest {
       
       private $service;
       
       public function setup() {
           $this->service = new ConsentService();
       }
       
       public function test_record_consent() {
           $response = $this->service->record_consent(1, 'analytics', 'accepted');
           
           assert($response['success'] === true, 'Should succeed');
           assert(isset($response['consent_id']), 'Should return consent ID');
       }
       
       public function test_invalid_type() {
           $response = $this->service->record_consent(1, 'invalid_type', 'accepted');
           
           assert($response['success'] === false, 'Should fail for invalid type');
           assert(strpos($response['error'], 'Invalid consent type') !== false, 'Should mention invalid type');
       }
       
       public function test_invalid_status() {
           $response = $this->service->record_consent(1, 'analytics', 'maybe');
           
           assert($response['success'] === false, 'Should fail for invalid status');
       }
       
       public function test_get_user_preferences() {
           $this->service->record_consent(1, 'analytics', 'accepted');
           $this->service->record_consent(1, 'marketing', 'rejected');
           
           $prefs = $this->service->get_user_preferences(1);
           
           assert(isset($prefs['analytics']), 'Should have analytics');
           assert($prefs['analytics'] === 'accepted', 'Analytics should be accepted');
           assert($prefs['marketing'] === 'rejected', 'Marketing should be rejected');
       }
       
       public function test_has_consent() {
           $this->service->record_consent(1, 'analytics', 'accepted');
           
           assert($this->service->has_consent(1, 'analytics') === true, 'Should have consent');
           assert($this->service->has_consent(1, 'marketing') === false, 'Should not have marketing consent');
       }
       
       public function test_withdraw_consent() {
           $this->service->record_consent(1, 'analytics', 'accepted');
           $response = $this->service->withdraw_consent(1, 'analytics');
           
           assert($response['success'] === true, 'Should withdraw successfully');
           assert($this->service->has_consent(1, 'analytics') === false, 'Consent should be gone');
       }
   }
   ```

3. CREATE HOOKS DOCUMENTATION
   
   Create file: includes/Modules/Consent/HOOKS.md
   
   Content explaining hooks that other code can hook into:
   ```markdown
   # Consent Module Hooks
   
   ## Actions (triggered by the system)
   
   ### complyflow_consent_recorded
   Fired when user gives consent
   ```php
   do_action('complyflow_consent_recorded', $user_id, $type, $status, $consent_id);
   ```
   
   ### complyflow_consent_withdrawn
   Fired when user withdraws consent
   ```php
   do_action('complyflow_consent_withdrawn', $user_id, $type);
   ```
   
   ## Usage Example
   ```php
   // In your plugin/theme:
   add_action('complyflow_consent_recorded', function($user_id, $type, $status, $id) {
       // Do something when consent is recorded
       error_log("User $user_id consented to $type");
   }, 10, 4);
   ```
   ```

OUTPUT STATE:
- [ ] ConsentService class created with full business logic
- [ ] All validation implemented
- [ ] All WordPress hooks defined
- [ ] Service tests created
- [ ] Hooks documentation created

VERIFICATION (run tests):

1. Create test runner:
   Command: wp eval '
   require "includes/Modules/Consent/Tests/ConsentServiceTest.php";
   $test = new \ComplyFlow\Modules\Consent\Tests\ConsentServiceTest();
   $test->setup();
   $test->test_record_consent();
   echo "✓ Record consent passed\n";
   $test->test_invalid_type();
   echo "✓ Invalid type validation passed\n";
   $test->test_get_user_preferences();
   echo "✓ Get preferences passed\n";
   echo "All tests passed!";
   '
   Expected: All tests passed messages

2. Verify hooks fire:
   Command: wp eval '
   $called = false;
   add_action("complyflow_consent_recorded", function() {
       global $called;
       $called = true;
   });
   $service = new \ComplyFlow\Modules\Consent\ConsentService();
   $service->record_consent(1, "analytics", "accepted");
   echo $called ? "✓ Hook fired" : "✗ Hook did not fire";
   '
   Expected: ✓ Hook fired

SUCCESS CRITERIA:
- [ ] ConsentService created with all methods
- [ ] Validation for all inputs (type, status)
- [ ] Business logic for record, check, withdraw
- [ ] Preferences tracking and retrieval
- [ ] WordPress hooks integrated
- [ ] All unit tests pass
- [ ] No WP_DEBUG errors

ROLLBACK (if verification fails):
   1. Delete ConsentService.php
   2. Delete test files
   3. Try again

NEXT TASK:
After verification, proceed to TASK 2.3 (REST API Endpoints for Consent)
```

---

## PROMPT: TASK 2.3 - REST API Endpoints for Consent

[Due to character limits, providing template - continues same pattern]

```
You are implementing TASK 2.3 of the Shahi LegalOps Suite plugin.

TASK: REST API Endpoints for Consent
PHASE: 2 (Consent Management)
EFFORT: 6-8 hours
DEPENDENCY: TASK 2.2 (ConsentService created)

CONTEXT:
The REST API is how the frontend consent banner communicates with the backend. The user clicks 
"Accept All" in the banner, JavaScript sends a POST request to /wp-json/complyflow/v1/consent/record, 
and the backend saves their preference.

This task creates the REST endpoints that the frontend will call.

YOUR TASK:

1. Create file: includes/Modules/Consent/ConsentAPI.php
   - Register REST routes
   - Create endpoints:
     * POST /complyflow/v1/consent/record - Record new consent
     * GET /complyflow/v1/consent/preferences - Get user preferences
     * POST /complyflow/v1/consent/withdraw - Withdraw consent
   - Implement permission checks (nonce verification)
   - Implement rate limiting to prevent abuse
   - Return proper JSON responses

2. Create file: includes/Modules/Consent/Tests/ConsentAPITest.php
   - Test each endpoint
   - Test permission checks
   - Test invalid input
   - Test rate limiting

[Complete prompt would follow same structure as TASK 2.1-2.2]

OUTPUT STATE:
- [ ] ConsentAPI class created
- [ ] All endpoints registered
- [ ] REST routes functional
- [ ] Permissions implemented
- [ ] Tests created and passing

VERIFICATION: [Test each endpoint via REST calls]

NEXT TASK: TASK 2.4 - Consent Banner Frontend Component
```

---

## REMAINING PHASE 2 TASKS (Quick Overview)

**TASK 2.4 - Consent Banner Frontend Component** (8-10 hours)
- Create HTML/CSS/JS consent banner
- Integrate with ConsentAPI endpoints
- Handle "Accept All" / "Reject All" / "Manage Preferences"
- Responsive design (mobile, tablet, desktop)
- Accessibility (WCAG 2.1 AA)

**TASK 2.5 - Cookie Consent Banner Settings** (4-6 hours)
- Admin page for banner customization
- Colors, text, position, styles
- Save to WordPress options
- Preview functionality

**TASK 2.6 - User Consent Preferences Page** (6-8 hours)
- Frontend page where users can manage their preferences
- Show current consent status for each type
- Allow withdrawal of consent
- Show history of consent changes

**TASK 2.7 - Consent Shortcode [complyflow_preferences]** (2-3 hours)
- Create shortcode for embedding consent panel
- Use on any page/post

**TASK 2.8 - Form Integration - Consent Field** (8-10 hours)
- Detect WPForms, Gravity Forms, Contact Form 7, Ninja Forms
- Automatically audit forms for consent checkboxes
- Flag missing consent checkboxes as issues

**TASK 2.9 - Form Issue Tracking & Reports** (6-8 hours)
- Store form issues in database
- Create admin page showing issues
- Mark issues as resolved
- Report generation

**TASK 2.10 - Cookie Scanner - Cookie Detection** (10-12 hours)
- Scan website traffic for cookies
- Identify third-party trackers
- Categorize by purpose (analytics, marketing, etc.)
- Compare against allowed list

**TASK 2.11 - Cookie Management Database** (4-6 hours)
- Store detected cookies in database
- Map to vendors
- Map to consent categories
- Allow whitelist/blacklist

**TASK 2.12 - Cookie Policy Document** (4-5 hours)
- Auto-generate cookie policy from detected cookies
- Include required legal text
- Allow customization
- Store versions with timestamps

**TASK 2.13 - Consent Audit Reports** (6-8 hours)
- Generate reports: who consented to what
- Export to CSV
- Filter by date range
- Privacy-respecting (no PII in default export)

**TASK 2.14 - Consent Analytics Dashboard** (6-8 hours)
- Show consent acceptance rates
- Charts and graphs
- Breakdown by type (analytics vs marketing vs personalization)
- Trends over time

**TASK 2.15 - Phase 2 Integration Testing** (8-10 hours)
- Full end-to-end testing
- User flow: Visit site → See banner → Accept → Check preferences
- Test form validation
- Test persistence
- Performance testing
- Security testing
- Create final Phase 2 verification checklist

---

## HOW TO USE THESE PROMPTS

1. **First 3 tasks (2.1-2.3):** Complete detailed prompts provided above
2. **Remaining tasks (2.4-2.15):** Use PROMPT TEMPLATE at end of v3docs/TASK-PROMPTS-PHASE-1.md
3. **For each task:**
   - Copy the template
   - Fill in task-specific information
   - Add code examples
   - Define success criteria
   - Create verification tests
   - Define rollback procedures

4. **Run prompts sequentially:**
   - Start with TASK 2.1
   - After verification passes, move to TASK 2.2
   - Continue for all 15 Phase 2 tasks
   - Then move to Phase 3

---

**Version:** 1.0  
**Format:** AI Agent Prompts with Examples  
**Date:** December 19, 2025  
**Status:** Ready for execution starting with TASK 2.1
