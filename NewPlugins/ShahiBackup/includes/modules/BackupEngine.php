<?php
/**
 * Backup Engine Module
 *
 * @package ShahiBackup
 */

namespace ShahiBackup\Modules;

/**
 * BackupEngine Class
 */
class BackupEngine {
    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_ajax_shahi_backup_create', array( $this, 'ajax_create_backup' ) );
        add_action( 'wp_ajax_shahi_backup_download', array( $this, 'ajax_download_backup' ) );
        add_action( 'wp_ajax_shahi_backup_delete', array( $this, 'ajax_delete_backup' ) );
        add_action( 'wp_ajax_shahi_backup_get_list', array( $this, 'ajax_get_backup_list' ) );
    }

    /**
     * Create backup
     *
     * @param string $type Backup type (database, files, full).
     * @return array|WP_Error
     */
    public function create_backup( $type = 'database' ) {
        $backup_name = 'backup-' . $type . '-' . gmdate( 'Y-m-d-His' );
        $started_at = current_time( 'mysql' );

        // Create backup record
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_backups';

        $wpdb->insert(
            $table,
            array(
                'backup_type' => $type,
                'backup_name' => $backup_name,
                'file_path' => '',
                'file_size' => 0,
                'status' => 'in_progress',
                'started_at' => $started_at,
            ),
            array( '%s', '%s', '%s', '%d', '%s', '%s' )
        );

        $backup_id = $wpdb->insert_id;

        try {
            if ( 'database' === $type ) {
                $result = $this->backup_database( $backup_name );
            } elseif ( 'files' === $type ) {
                $result = $this->backup_files( $backup_name );
            } elseif ( 'full' === $type ) {
                $result = $this->backup_full( $backup_name );
            } else {
                throw new \Exception( __( 'Invalid backup type', 'shahi-backup' ) );
            }

            // Update backup record
            $wpdb->update(
                $table,
                array(
                    'file_path' => $result['file_path'],
                    'file_size' => $result['file_size'],
                    'status' => 'completed',
                    'completed_at' => current_time( 'mysql' ),
                    'metadata' => wp_json_encode( $result['metadata'] ),
                ),
                array( 'id' => $backup_id ),
                array( '%s', '%d', '%s', '%s', '%s' ),
                array( '%d' )
            );

            return array(
                'success' => true,
                'backup_id' => $backup_id,
                'file_path' => $result['file_path'],
                'file_size' => $result['file_size'],
            );

        } catch ( \Exception $e ) {
            // Update backup record with error
            $wpdb->update(
                $table,
                array(
                    'status' => 'failed',
                    'completed_at' => current_time( 'mysql' ),
                    'error_message' => $e->getMessage(),
                ),
                array( 'id' => $backup_id ),
                array( '%s', '%s', '%s' ),
                array( '%d' )
            );

            return new \WP_Error( 'backup_failed', $e->getMessage() );
        }
    }

    /**
     * Backup database
     *
     * @param string $backup_name Backup name.
     * @return array
     */
    private function backup_database( $backup_name ) {
        global $wpdb;

        $filename = $backup_name . '.sql';
        $filepath = SHAHI_BACKUP_DIR . $filename;

        // Get all tables
        $tables = $wpdb->get_results( 'SHOW TABLES', ARRAY_N );

        $sql_dump = "-- Shahi Backup Database Dump\n";
        $sql_dump .= "-- Date: " . gmdate( 'Y-m-d H:i:s' ) . "\n\n";

        foreach ( $tables as $table ) {
            $table_name = $table[0];
            
            // Drop table statement
            $sql_dump .= "DROP TABLE IF EXISTS `$table_name`;\n\n";

            // Create table statement
            $create_table = $wpdb->get_row( "SHOW CREATE TABLE `$table_name`", ARRAY_N );
            $sql_dump .= $create_table[1] . ";\n\n";

            // Insert data
            $rows = $wpdb->get_results( "SELECT * FROM `$table_name`", ARRAY_A );
            
            if ( ! empty( $rows ) ) {
                foreach ( $rows as $row ) {
                    $values = array();
                    foreach ( $row as $value ) {
                        if ( null === $value ) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = "'" . $wpdb->_escape( $value ) . "'";
                        }
                    }
                    $sql_dump .= "INSERT INTO `$table_name` VALUES (" . implode( ',', $values ) . ");\n";
                }
                $sql_dump .= "\n";
            }
        }

        // Write to file
        file_put_contents( $filepath, $sql_dump );

        // Compress if possible
        if ( function_exists( 'gzencode' ) ) {
            $gz_filepath = $filepath . '.gz';
            file_put_contents( $gz_filepath, gzencode( $sql_dump, 9 ) );
            unlink( $filepath );
            $filepath = $gz_filepath;
            $filename .= '.gz';
        }

        return array(
            'file_path' => $filepath,
            'file_size' => filesize( $filepath ),
            'metadata' => array(
                'tables_count' => count( $tables ),
                'compressed' => function_exists( 'gzencode' ),
            ),
        );
    }

    /**
     * Backup files
     *
     * @param string $backup_name Backup name.
     * @return array
     */
    private function backup_files( $backup_name ) {
        $filename = $backup_name . '.zip';
        $filepath = SHAHI_BACKUP_DIR . $filename;

        if ( ! class_exists( 'ZipArchive' ) ) {
            throw new \Exception( __( 'ZipArchive class not available', 'shahi-backup' ) );
        }

        $zip = new \ZipArchive();
        
        if ( true !== $zip->open( $filepath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE ) ) {
            throw new \Exception( __( 'Failed to create ZIP file', 'shahi-backup' ) );
        }

        // Add wp-content directories (excluding backups)
        $paths = array(
            WP_CONTENT_DIR . '/themes',
            WP_CONTENT_DIR . '/plugins',
            WP_CONTENT_DIR . '/uploads',
        );

        $files_count = 0;

        foreach ( $paths as $path ) {
            if ( ! file_exists( $path ) ) {
                continue;
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator( $path ),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ( $files as $file ) {
                if ( ! $file->isDir() ) {
                    $file_path = $file->getRealPath();
                    $relative_path = substr( $file_path, strlen( WP_CONTENT_DIR ) + 1 );

                    // Skip backup directory
                    if ( strpos( $relative_path, 'shahi-backups' ) !== false ) {
                        continue;
                    }

                    $zip->addFile( $file_path, $relative_path );
                    $files_count++;

                    // PLACEHOLDER: In production, add progress tracking and memory management
                    // for large file systems (chunk processing, streaming, etc.)
                }
            }
        }

        $zip->close();

        return array(
            'file_path' => $filepath,
            'file_size' => filesize( $filepath ),
            'metadata' => array(
                'files_count' => $files_count,
                'paths_included' => $paths,
            ),
        );
    }

    /**
     * Backup full (database + files)
     *
     * @param string $backup_name Backup name.
     * @return array
     */
    private function backup_full( $backup_name ) {
        // Create database backup first
        $db_result = $this->backup_database( $backup_name . '-db' );

        // Create files backup
        $files_result = $this->backup_files( $backup_name . '-files' );

        return array(
            'file_path' => $files_result['file_path'],
            'file_size' => $db_result['file_size'] + $files_result['file_size'],
            'metadata' => array(
                'database' => $db_result,
                'files' => $files_result,
            ),
        );
    }

    /**
     * Get backup list
     *
     * @param int $limit Limit.
     * @return array
     */
    public function get_backup_list( $limit = 50 ) {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_backups';

        $backups = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table ORDER BY started_at DESC LIMIT %d",
                $limit
            ),
            ARRAY_A
        );

        return $backups;
    }

    /**
     * Delete backup
     *
     * @param int $backup_id Backup ID.
     * @return bool
     */
    public function delete_backup( $backup_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'shahi_backups';

        $backup = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $backup_id ),
            ARRAY_A
        );

        if ( ! $backup ) {
            return false;
        }

        // Delete file
        if ( file_exists( $backup['file_path'] ) ) {
            unlink( $backup['file_path'] );
        }

        // Delete record
        $wpdb->delete( $table, array( 'id' => $backup_id ), array( '%d' ) );

        return true;
    }

    /**
     * Clean old backups
     */
    public function clean_old_backups() {
        $keep_count = get_option( 'shahi_backup_keep_count', 7 );

        global $wpdb;
        $table = $wpdb->prefix . 'shahi_backups';

        $old_backups = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id FROM $table WHERE status = 'completed' ORDER BY started_at DESC LIMIT %d, 999999",
                $keep_count
            ),
            ARRAY_A
        );

        foreach ( $old_backups as $backup ) {
            $this->delete_backup( $backup['id'] );
        }
    }

    /**
     * AJAX: Create backup
     */
    public function ajax_create_backup() {
        check_ajax_referer( 'shahi_backup_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shahi-backup' ) ) );
        }

        $type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : 'database';

        $result = $this->create_backup( $type );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( $result );
    }

    /**
     * AJAX: Get backup list
     */
    public function ajax_get_backup_list() {
        check_ajax_referer( 'shahi_backup_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shahi-backup' ) ) );
        }

        $backups = $this->get_backup_list();

        wp_send_json_success( array( 'backups' => $backups ) );
    }

    /**
     * AJAX: Download backup
     */
    public function ajax_download_backup() {
        check_ajax_referer( 'shahi_backup_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Insufficient permissions', 'shahi-backup' ) );
        }

        $backup_id = isset( $_GET['backup_id'] ) ? absint( $_GET['backup_id'] ) : 0;

        global $wpdb;
        $table = $wpdb->prefix . 'shahi_backups';

        $backup = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $backup_id ),
            ARRAY_A
        );

        if ( ! $backup || ! file_exists( $backup['file_path'] ) ) {
            wp_die( esc_html__( 'Backup not found', 'shahi-backup' ) );
        }

        header( 'Content-Type: application/octet-stream' );
        header( 'Content-Disposition: attachment; filename="' . basename( $backup['file_path'] ) . '"' );
        header( 'Content-Length: ' . filesize( $backup['file_path'] ) );
        readfile( $backup['file_path'] );
        exit;
    }

    /**
     * AJAX: Delete backup
     */
    public function ajax_delete_backup() {
        check_ajax_referer( 'shahi_backup_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shahi-backup' ) ) );
        }

        $backup_id = isset( $_POST['backup_id'] ) ? absint( $_POST['backup_id'] ) : 0;

        $result = $this->delete_backup( $backup_id );

        if ( ! $result ) {
            wp_send_json_error( array( 'message' => __( 'Failed to delete backup', 'shahi-backup' ) ) );
        }

        wp_send_json_success();
    }
}
