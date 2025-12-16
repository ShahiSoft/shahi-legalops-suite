<?php
/**
 * Database Schema for Accessibility Scanner Module
 *
 * Defines all database tables with proper indexes and foreign key relationships.
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
 * Schema Class
 *
 * Manages database schema creation and updates for Accessibility Scanner.
 *
 * @since 1.0.0
 */
class Schema {
    
    /**
     * WordPress database object
     *
     * @var \wpdb
     */
    private $wpdb;
    
    /**
     * Database charset collation
     *
     * @var string
     */
    private $charset_collate;
    
    /**
     * Database version
     *
     * @var string
     */
    private $db_version = '1.0.0';
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->charset_collate = $wpdb->get_charset_collate();
    }
    
    /**
     * Create all database tables
     *
     * Uses dbDelta for safe table creation/updates.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    public function create_tables() {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        $tables_sql = [
            $this->get_scans_table_sql(),
            $this->get_issues_table_sql(),
            $this->get_fixes_table_sql(),
            $this->get_ignores_table_sql(),
            $this->get_reports_table_sql(),
            $this->get_analytics_table_sql(),
        ];
        
        // Execute table creation
        foreach ($tables_sql as $sql) {
            dbDelta($sql);
        }
        
        // Update database version
        update_option('shahi_a11y_db_version', $this->db_version);
        
        return true;
    }
    
    /**
     * Drop all database tables
     *
     * WARNING: This will delete all data. Use only for uninstall.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    public function drop_tables() {
        $tables = [
            $this->wpdb->prefix . 'slos_a11y_analytics',
            $this->wpdb->prefix . 'slos_a11y_reports',
            $this->wpdb->prefix . 'slos_a11y_ignores',
            $this->wpdb->prefix . 'slos_a11y_fixes',
            $this->wpdb->prefix . 'slos_a11y_issues',
            $this->wpdb->prefix . 'slos_a11y_scans',
        ];
        
        foreach ($tables as $table) {
            $this->wpdb->query("DROP TABLE IF EXISTS {$table}");
        }
        
        delete_option('shahi_a11y_db_version');
        
        return true;
    }
    
    /**
     * Get scans table SQL
     *
     * Stores accessibility scan records with metadata and results.
     *
     * @since 1.0.0
     * @return string SQL CREATE TABLE statement
     */
    private function get_scans_table_sql() {
        $table_name = $this->wpdb->prefix . 'slos_a11y_scans';
        
        return "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT UNSIGNED NULL COMMENT 'Associated WordPress post ID',
            url VARCHAR(500) NOT NULL COMMENT 'URL scanned',
            scan_type ENUM('manual', 'auto', 'scheduled', 'bulk') DEFAULT 'manual' COMMENT 'How scan was initiated',
            status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending' COMMENT 'Current scan status',
            total_checks INT UNSIGNED DEFAULT 0 COMMENT 'Total checks performed',
            passed_checks INT UNSIGNED DEFAULT 0 COMMENT 'Number of passed checks',
            failed_checks INT UNSIGNED DEFAULT 0 COMMENT 'Number of failed checks',
            warning_checks INT UNSIGNED DEFAULT 0 COMMENT 'Number of warnings',
            score INT UNSIGNED DEFAULT 0 COMMENT 'Overall accessibility score (0-100)',
            wcag_level ENUM('A', 'AA', 'AAA') DEFAULT 'AA' COMMENT 'Target WCAG compliance level',
            started_at DATETIME NULL COMMENT 'When scan started',
            completed_at DATETIME NULL COMMENT 'When scan completed',
            created_by BIGINT UNSIGNED NULL COMMENT 'User who initiated scan',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY status (status),
            KEY scan_type (scan_type),
            KEY created_at (created_at),
            KEY created_by (created_by),
            KEY wcag_level (wcag_level),
            KEY score (score),
            KEY completed_at (completed_at),
            KEY post_status_idx (post_id, status),
            KEY type_status_idx (scan_type, status),
            KEY user_date_idx (created_by, created_at)
        ) {$this->charset_collate} COMMENT='Accessibility scan records';";
    }
    
    /**
     * Get issues table SQL
     *
     * Stores individual accessibility issues found during scans.
     *
     * @since 1.0.0
     * @return string SQL CREATE TABLE statement
     */
    private function get_issues_table_sql() {
        $table_name = $this->wpdb->prefix . 'slos_a11y_issues';
        
        return "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            scan_id BIGINT UNSIGNED NOT NULL COMMENT 'Reference to parent scan',
            check_type VARCHAR(100) NOT NULL COMMENT 'Checker category (image, heading, form, etc)',
            check_name VARCHAR(200) NOT NULL COMMENT 'Specific check name',
            severity ENUM('critical', 'serious', 'moderate', 'minor') DEFAULT 'moderate' COMMENT 'Issue severity level',
            wcag_criterion VARCHAR(50) NULL COMMENT 'WCAG success criterion (e.g., 1.1.1)',
            wcag_level ENUM('A', 'AA', 'AAA') DEFAULT 'AA' COMMENT 'WCAG conformance level',
            element_selector VARCHAR(500) NULL COMMENT 'CSS selector for problematic element',
            element_html TEXT NULL COMMENT 'HTML of problematic element',
            line_number INT UNSIGNED NULL COMMENT 'Line number in source',
            issue_description TEXT NULL COMMENT 'Detailed issue description',
            recommendation TEXT NULL COMMENT 'Recommended fix',
            status ENUM('new', 'in_progress', 'fixed', 'verified', 'closed', 'ignored') DEFAULT 'new' COMMENT 'Issue resolution status',
            priority ENUM('P0', 'P1', 'P2', 'P3', 'P4') DEFAULT 'P2' COMMENT 'Fix priority',
            assigned_to BIGINT UNSIGNED NULL COMMENT 'User assigned to fix',
            due_date DATETIME NULL COMMENT 'Fix deadline',
            fixed_at DATETIME NULL COMMENT 'When issue was fixed',
            fixed_by BIGINT UNSIGNED NULL COMMENT 'User who fixed issue',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
            PRIMARY KEY (id),
            KEY scan_id (scan_id),
            KEY check_type (check_type),
            KEY severity (severity),
            KEY status (status),
            KEY assigned_to (assigned_to),
            KEY wcag_criterion (wcag_criterion),
            KEY wcag_level (wcag_level),
            KEY priority (priority),
            KEY created_at (created_at),
            KEY scan_severity_idx (scan_id, severity),
            KEY scan_status_idx (scan_id, status),
            KEY type_severity_idx (check_type, severity),
            KEY wcag_severity_idx (wcag_criterion, severity),
            KEY assigned_status_idx (assigned_to, status),
            CONSTRAINT fk_issues_scan_id
                FOREIGN KEY (scan_id)
                REFERENCES {$this->wpdb->prefix}slos_a11y_scans(id)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        ) {$this->charset_collate} COMMENT='Accessibility issues found in scans';";
    }
    
    /**
     * Get fixes table SQL
     *
     * Stores automated and manual fixes applied to issues.
     *
     * @since 1.0.0
     * @return string SQL CREATE TABLE statement
     */
    private function get_fixes_table_sql() {
        $table_name = $this->wpdb->prefix . 'slos_a11y_fixes';
        
        return "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            issue_id BIGINT UNSIGNED NULL COMMENT 'Reference to issue (NULL for preventive fixes)',
            fix_type VARCHAR(100) NOT NULL COMMENT 'Type of fix applied',
            fix_name VARCHAR(200) NOT NULL COMMENT 'Descriptive name of fix',
            fix_description TEXT NULL COMMENT 'Detailed description of what was fixed',
            before_html TEXT NULL COMMENT 'HTML before fix',
            after_html TEXT NULL COMMENT 'HTML after fix',
            applied BOOLEAN DEFAULT FALSE COMMENT 'Whether fix has been applied',
            applied_at DATETIME NULL COMMENT 'When fix was applied',
            applied_by BIGINT UNSIGNED NULL COMMENT 'User who applied fix',
            reverted_at DATETIME NULL COMMENT 'When fix was reverted (if applicable)',
            reverted_by BIGINT UNSIGNED NULL COMMENT 'User who reverted fix',
            success BOOLEAN NULL COMMENT 'Whether fix was successful',
            error_message TEXT NULL COMMENT 'Error message if fix failed',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',
            PRIMARY KEY (id),
            KEY issue_id (issue_id),
            KEY fix_type (fix_type),
            KEY applied (applied),
            KEY applied_by (applied_by),
            KEY created_at (created_at),
            KEY success (success),
            KEY issue_applied_idx (issue_id, applied),
            KEY type_success_idx (fix_type, success),
            KEY user_date_idx (applied_by, applied_at),
            CONSTRAINT fk_fixes_issue_id
                FOREIGN KEY (issue_id)
                REFERENCES {$this->wpdb->prefix}slos_a11y_issues(id)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        ) {$this->charset_collate} COMMENT='Accessibility fixes applied';";
    }
    
    /**
     * Get ignores table SQL
     *
     * Stores ignored issues with reasons and expiration.
     *
     * @since 1.0.0
     * @return string SQL CREATE TABLE statement
     */
    private function get_ignores_table_sql() {
        $table_name = $this->wpdb->prefix . 'slos_a11y_ignores';
        
        return "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            issue_id BIGINT UNSIGNED NOT NULL COMMENT 'Reference to ignored issue',
            reason TEXT NULL COMMENT 'Reason for ignoring',
            ignored_by BIGINT UNSIGNED NOT NULL COMMENT 'User who ignored issue',
            ignored_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'When issue was ignored',
            expires_at DATETIME NULL COMMENT 'When ignore expires (NULL = permanent)',
            reopened_at DATETIME NULL COMMENT 'When issue was reopened',
            reopened_by BIGINT UNSIGNED NULL COMMENT 'User who reopened issue',
            PRIMARY KEY (id),
            KEY issue_id (issue_id),
            KEY ignored_by (ignored_by),
            KEY expires_at (expires_at),
            KEY reopened_at (reopened_at),
            KEY issue_expires_idx (issue_id, expires_at),
            KEY user_date_idx (ignored_by, ignored_at),
            CONSTRAINT fk_ignores_issue_id
                FOREIGN KEY (issue_id)
                REFERENCES {$this->wpdb->prefix}slos_a11y_issues(id)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        ) {$this->charset_collate} COMMENT='Ignored accessibility issues';";
    }
    
    /**
     * Get reports table SQL
     *
     * Stores generated accessibility reports.
     *
     * @since 1.0.0
     * @return string SQL CREATE TABLE statement
     */
    private function get_reports_table_sql() {
        $table_name = $this->wpdb->prefix . 'slos_a11y_reports';
        
        return "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            scan_id BIGINT UNSIGNED NULL COMMENT 'Reference to scan (NULL for multi-scan reports)',
            report_type ENUM('summary', 'detailed', 'vpat', 'wcag-em', 'csv', 'pdf') DEFAULT 'summary' COMMENT 'Report format type',
            report_data LONGTEXT NULL COMMENT 'Report data (JSON or serialized)',
            file_path VARCHAR(500) NULL COMMENT 'Path to generated report file',
            generated_by BIGINT UNSIGNED NOT NULL COMMENT 'User who generated report',
            generated_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Report generation timestamp',
            PRIMARY KEY (id),
            KEY scan_id (scan_id),
            KEY report_type (report_type),
            KEY generated_by (generated_by),
            KEY generated_at (generated_at),
            KEY scan_type_idx (scan_id, report_type),
            KEY user_date_idx (generated_by, generated_at),
            CONSTRAINT fk_reports_scan_id
                FOREIGN KEY (scan_id)
                REFERENCES {$this->wpdb->prefix}slos_a11y_scans(id)
                ON DELETE SET NULL
                ON UPDATE CASCADE
        ) {$this->charset_collate} COMMENT='Generated accessibility reports';";
    }
    
    /**
     * Get analytics table SQL
     *
     * Stores usage analytics and events for the module.
     *
     * @since 1.0.0
     * @return string SQL CREATE TABLE statement
     */
    private function get_analytics_table_sql() {
        $table_name = $this->wpdb->prefix . 'slos_a11y_analytics';
        
        return "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            event_type VARCHAR(100) NOT NULL COMMENT 'Event category (scan, fix, report, etc)',
            event_name VARCHAR(200) NOT NULL COMMENT 'Specific event name',
            event_data TEXT NULL COMMENT 'Event metadata (JSON)',
            user_id BIGINT UNSIGNED NULL COMMENT 'User who triggered event',
            ip_address VARCHAR(45) NULL COMMENT 'IP address (IPv4 or IPv6)',
            user_agent VARCHAR(500) NULL COMMENT 'User agent string',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Event timestamp',
            PRIMARY KEY (id),
            KEY event_type (event_type),
            KEY event_name (event_name),
            KEY user_id (user_id),
            KEY created_at (created_at),
            KEY type_name_idx (event_type, event_name),
            KEY user_date_idx (user_id, created_at),
            KEY type_date_idx (event_type, created_at)
        ) {$this->charset_collate} COMMENT='Module usage analytics';";
    }
    
    /**
     * Get database version
     *
     * @since 1.0.0
     * @return string Database version
     */
    public function get_version() {
        return $this->db_version;
    }
    
    /**
     * Get current database version from options
     *
     * @since 1.0.0
     * @return string|false Current version or false if not set
     */
    public function get_current_version() {
        return get_option('shahi_a11y_db_version', false);
    }
    
    /**
     * Check if tables exist
     *
     * @since 1.0.0
     * @return bool True if all tables exist, false otherwise
     */
    public function tables_exist() {
        $required_tables = [
            $this->wpdb->prefix . 'slos_a11y_scans',
            $this->wpdb->prefix . 'slos_a11y_issues',
            $this->wpdb->prefix . 'slos_a11y_fixes',
            $this->wpdb->prefix . 'slos_a11y_ignores',
            $this->wpdb->prefix . 'slos_a11y_reports',
            $this->wpdb->prefix . 'slos_a11y_analytics',
        ];
        
        foreach ($required_tables as $table) {
            $result = $this->wpdb->get_var("SHOW TABLES LIKE '{$table}'");
            if ($result !== $table) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get table statistics
     *
     * Returns row counts for all tables.
     *
     * @since 1.0.0
     * @return array Table statistics
     */
    public function get_table_stats() {
        $stats = [];
        
        $tables = [
            'scans' => $this->wpdb->prefix . 'slos_a11y_scans',
            'issues' => $this->wpdb->prefix . 'slos_a11y_issues',
            'fixes' => $this->wpdb->prefix . 'slos_a11y_fixes',
            'ignores' => $this->wpdb->prefix . 'slos_a11y_ignores',
            'reports' => $this->wpdb->prefix . 'slos_a11y_reports',
            'analytics' => $this->wpdb->prefix . 'slos_a11y_analytics',
        ];
        
        foreach ($tables as $key => $table) {
            $count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$table}");
            $stats[$key] = (int) $count;
        }
        
        return $stats;
    }
}
