<?php
/**
 * Migration Runner
 *
 * Manages execution of database migrations with proper ordering and error handling.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Modules\AccessibilityScanner\Database
 * @since      1.0.0
 * @license    GPL-3.0+
 */

namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Database;

use ShahiLegalopsSuite\Modules\AccessibilityScanner\Database\Migrations\Migration_1_0_0;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * MigrationRunner Class
 *
 * Executes database migrations in order and handles version tracking.
 *
 * @since 1.0.0
 */
class MigrationRunner {
    
    /**
     * Available migrations
     *
     * @var array
     */
    private $migrations = [];
    
    /**
     * Migration results
     *
     * @var array
     */
    private $results = [];
    
    /**
     * Constructor
     *
     * Registers all available migrations.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->register_migrations();
    }
    
    /**
     * Register all migrations
     *
     * Add new migrations here in chronological order.
     *
     * @since 1.0.0
     * @return void
     */
    private function register_migrations() {
        $this->migrations = [
            new Migration_1_0_0(),
            // Future migrations will be added here
            // new Migration_1_1_0(),
            // new Migration_1_2_0(),
        ];
    }
    
    /**
     * Run all pending migrations
     *
     * Executes migrations that haven't been run yet.
     *
     * @since 1.0.0
     * @param bool $verbose Whether to output verbose messages
     * @return array Results of migration execution
     */
    public function run_migrations($verbose = false) {
        $this->results = [];
        
        if (empty($this->migrations)) {
            $this->results[] = [
                'status' => 'info',
                'message' => __('No migrations to run', 'shahi-legalops-suite'),
            ];
            return $this->results;
        }
        
        foreach ($this->migrations as $migration) {
            $version = $migration->get_version();
            
            if ($verbose) {
                $this->results[] = [
                    'status' => 'info',
                    'version' => $version,
                    'message' => sprintf(
                        __('Checking migration %s...', 'shahi-legalops-suite'),
                        $version
                    ),
                ];
            }
            
            $result = $migration->run();
            
            if ($result) {
                $this->results[] = [
                    'status' => 'success',
                    'version' => $version,
                    'description' => $migration->get_description(),
                    'message' => sprintf(
                        __('Migration %s executed successfully', 'shahi-legalops-suite'),
                        $version
                    ),
                ];
            } else {
                $this->results[] = [
                    'status' => 'error',
                    'version' => $version,
                    'description' => $migration->get_description(),
                    'message' => sprintf(
                        __('Migration %s failed', 'shahi-legalops-suite'),
                        $version
                    ),
                ];
                
                // Stop on first error
                break;
            }
        }
        
        return $this->results;
    }
    
    /**
     * Rollback last migration
     *
     * Reverts the most recently executed migration.
     *
     * @since 1.0.0
     * @return array Result of rollback
     */
    public function rollback_last() {
        $current_version = get_option('shahi_a11y_db_version', '0.0.0');
        
        if ($current_version === '0.0.0') {
            return [
                'status' => 'info',
                'message' => __('No migrations to rollback', 'shahi-legalops-suite'),
            ];
        }
        
        // Find migration matching current version
        foreach (array_reverse($this->migrations) as $migration) {
            if ($migration->get_version() === $current_version) {
                $result = $migration->rollback();
                
                if ($result) {
                    return [
                        'status' => 'success',
                        'version' => $current_version,
                        'message' => sprintf(
                            __('Migration %s rolled back successfully', 'shahi-legalops-suite'),
                            $current_version
                        ),
                    ];
                } else {
                    return [
                        'status' => 'error',
                        'version' => $current_version,
                        'message' => sprintf(
                            __('Failed to rollback migration %s', 'shahi-legalops-suite'),
                            $current_version
                        ),
                    ];
                }
            }
        }
        
        return [
            'status' => 'error',
            'message' => sprintf(
                __('Migration %s not found', 'shahi-legalops-suite'),
                $current_version
            ),
        ];
    }
    
    /**
     * Rollback to specific version
     *
     * Rolls back migrations until reaching the specified version.
     *
     * @since 1.0.0
     * @param string $target_version Target version to rollback to
     * @return array Results of rollback operations
     */
    public function rollback_to($target_version) {
        $results = [];
        $current_version = get_option('shahi_a11y_db_version', '0.0.0');
        
        if (version_compare($current_version, $target_version, '<=')) {
            return [
                [
                    'status' => 'info',
                    'message' => sprintf(
                        __('Already at version %s or lower', 'shahi-legalops-suite'),
                        $target_version
                    ),
                ],
            ];
        }
        
        // Rollback migrations in reverse order
        foreach (array_reverse($this->migrations) as $migration) {
            $version = $migration->get_version();
            
            if (version_compare($version, $target_version, '>')) {
                $result = $migration->rollback();
                
                if ($result) {
                    $results[] = [
                        'status' => 'success',
                        'version' => $version,
                        'message' => sprintf(
                            __('Rolled back migration %s', 'shahi-legalops-suite'),
                            $version
                        ),
                    ];
                } else {
                    $results[] = [
                        'status' => 'error',
                        'version' => $version,
                        'message' => sprintf(
                            __('Failed to rollback migration %s', 'shahi-legalops-suite'),
                            $version
                        ),
                    ];
                    break;
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Get current database version
     *
     * @since 1.0.0
     * @return string Current database version
     */
    public function get_current_version() {
        return get_option('shahi_a11y_db_version', '0.0.0');
    }
    
    /**
     * Get migration history
     *
     * Returns all executed migrations with timestamps.
     *
     * @since 1.0.0
     * @return array Migration history
     */
    public function get_migration_history() {
        return get_option('shahi_a11y_migration_history', []);
    }
    
    /**
     * Get pending migrations
     *
     * Returns migrations that haven't been executed yet.
     *
     * @since 1.0.0
     * @return array Pending migrations
     */
    public function get_pending_migrations() {
        $current_version = $this->get_current_version();
        $pending = [];
        
        foreach ($this->migrations as $migration) {
            if (version_compare($migration->get_version(), $current_version, '>')) {
                $pending[] = [
                    'version' => $migration->get_version(),
                    'description' => $migration->get_description(),
                ];
            }
        }
        
        return $pending;
    }
    
    /**
     * Reset migration history
     *
     * WARNING: This will delete all migration tracking data.
     * Use only for testing or cleanup.
     *
     * @since 1.0.0
     * @return void
     */
    public function reset_history() {
        delete_option('shahi_a11y_db_version');
        delete_option('shahi_a11y_migration_history');
    }
    
    /**
     * Get migration results
     *
     * Returns results from the last run_migrations() call.
     *
     * @since 1.0.0
     * @return array Migration results
     */
    public function get_results() {
        return $this->results;
    }
}
