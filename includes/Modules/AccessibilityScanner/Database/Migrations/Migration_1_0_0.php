<?php
/**
 * Migration 1.0.0 - Initial Database Schema
 *
 * Creates all required database tables for the Accessibility Scanner module.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Modules\AccessibilityScanner\Database\Migrations
 * @since      1.0.0
 * @license    GPL-3.0+
 */

namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Database\Migrations;

use ShahiLegalopsSuite\Modules\AccessibilityScanner\Database\Migration;
use ShahiLegalopsSuite\Modules\AccessibilityScanner\Database\Schema;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Migration_1_0_0 Class
 *
 * Initial migration that creates all database tables using the Schema class.
 *
 * @since 1.0.0
 */
class Migration_1_0_0 extends Migration {
    
    /**
     * Migration version
     *
     * @var string
     */
    protected $version = '1.0.0';
    
    /**
     * Migration description
     *
     * @var string
     */
    protected $description = 'Initial database schema - creates scans, issues, fixes, ignores, reports, and analytics tables';
    
    /**
     * Apply migration
     *
     * Creates all database tables using the Schema class.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    public function up() {
        try {
            $schema = new Schema();
            $result = $schema->create_tables();
            
            // Verify tables were created
            if ($schema->tables_exist()) {
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log('Accessibility Scanner Migration 1.0.0 up() failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rollback migration
     *
     * Drops all database tables using the Schema class.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    public function down() {
        try {
            $schema = new Schema();
            $result = $schema->drop_tables();
            
            // Verify tables were dropped
            if (!$schema->tables_exist()) {
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log('Accessibility Scanner Migration 1.0.0 down() failed: ' . $e->getMessage());
            return false;
        }
    }
}
