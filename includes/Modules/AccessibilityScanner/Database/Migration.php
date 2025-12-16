<?php
/**
 * Abstract Migration Base Class
 *
 * Provides structure for database migrations with version tracking and rollback support.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Modules\AccessibilityScanner\Database
 * @since      1.0.0
 * @license    GPL-3.0+
 */

namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Database;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Migration Abstract Class
 *
 * Base class for all database migrations. Each migration must implement
 * up() and down() methods for applying and rolling back changes.
 *
 * @since 1.0.0
 */
abstract class Migration {
    
    /**
     * WordPress database object
     *
     * @var \wpdb
     */
    protected $wpdb;
    
    /**
     * Migration version
     *
     * @var string
     */
    protected $version;
    
    /**
     * Migration description
     *
     * @var string
     */
    protected $description = '';
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }
    
    /**
     * Apply migration
     *
     * Implement this method to define schema changes.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    abstract public function up();
    
    /**
     * Rollback migration
     *
     * Implement this method to reverse schema changes.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    abstract public function down();
    
    /**
     * Get migration version
     *
     * @since 1.0.0
     * @return string Migration version
     */
    public function get_version() {
        return $this->version;
    }
    
    /**
     * Get migration description
     *
     * @since 1.0.0
     * @return string Migration description
     */
    public function get_description() {
        return $this->description;
    }
    
    /**
     * Run migration
     *
     * Checks if migration needs to run and executes up() method.
     *
     * @since 1.0.0
     * @return bool True if migration ran successfully, false otherwise
     */
    public function run() {
        if ($this->should_run()) {
            $start_time = microtime(true);
            
            $result = $this->up();
            
            if ($result) {
                $this->log_migration($start_time);
                $this->update_version();
                return true;
            }
            
            return false;
        }
        
        return true; // Already run
    }
    
    /**
     * Rollback migration
     *
     * Executes down() method and updates version tracking.
     *
     * @since 1.0.0
     * @return bool True if rollback successful, false otherwise
     */
    public function rollback() {
        $start_time = microtime(true);
        
        $result = $this->down();
        
        if ($result) {
            $this->log_rollback($start_time);
            $this->remove_version();
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if migration should run
     *
     * Compares current database version with migration version.
     *
     * @since 1.0.0
     * @return bool True if migration should run, false otherwise
     */
    protected function should_run() {
        $current_version = get_option('shahi_a11y_db_version', '0.0.0');
        return version_compare($current_version, $this->version, '<');
    }
    
    /**
     * Update database version
     *
     * Updates the stored database version to this migration's version.
     *
     * @since 1.0.0
     * @return void
     */
    protected function update_version() {
        update_option('shahi_a11y_db_version', $this->version);
    }
    
    /**
     * Remove version (on rollback)
     *
     * Reverts to previous version after rollback.
     *
     * @since 1.0.0
     * @return void
     */
    protected function remove_version() {
        // Find previous migration version
        $migrations = $this->get_migration_history();
        
        if (count($migrations) > 1) {
            // Get second-to-last migration version
            $previous = $migrations[count($migrations) - 2];
            update_option('shahi_a11y_db_version', $previous['version']);
        } else {
            // No previous migrations, set to 0.0.0
            update_option('shahi_a11y_db_version', '0.0.0');
        }
    }
    
    /**
     * Log migration execution
     *
     * Records migration in history log.
     *
     * @since 1.0.0
     * @param float $start_time Migration start time
     * @return void
     */
    protected function log_migration($start_time) {
        $execution_time = microtime(true) - $start_time;
        
        $history = get_option('shahi_a11y_migration_history', []);
        
        $history[] = [
            'version' => $this->version,
            'description' => $this->description,
            'action' => 'up',
            'executed_at' => current_time('mysql'),
            'execution_time' => round($execution_time, 4),
        ];
        
        update_option('shahi_a11y_migration_history', $history);
    }
    
    /**
     * Log migration rollback
     *
     * Records rollback in history log.
     *
     * @since 1.0.0
     * @param float $start_time Rollback start time
     * @return void
     */
    protected function log_rollback($start_time) {
        $execution_time = microtime(true) - $start_time;
        
        $history = get_option('shahi_a11y_migration_history', []);
        
        $history[] = [
            'version' => $this->version,
            'description' => $this->description,
            'action' => 'down',
            'executed_at' => current_time('mysql'),
            'execution_time' => round($execution_time, 4),
        ];
        
        update_option('shahi_a11y_migration_history', $history);
    }
    
    /**
     * Get migration history
     *
     * Returns all executed migrations.
     *
     * @since 1.0.0
     * @return array Migration history
     */
    protected function get_migration_history() {
        return get_option('shahi_a11y_migration_history', []);
    }
    
    /**
     * Check if migration was already executed
     *
     * @since 1.0.0
     * @return bool True if migration was executed, false otherwise
     */
    protected function was_executed() {
        $history = $this->get_migration_history();
        
        foreach ($history as $entry) {
            if ($entry['version'] === $this->version && $entry['action'] === 'up') {
                return true;
            }
        }
        
        return false;
    }
}
