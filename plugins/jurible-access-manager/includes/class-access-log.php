<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class JAM_Access_Log {

    /**
     * Count total log entries.
     */
    public static function count() {
        global $wpdb;
        $table = JAM_Database::get_log_table();

        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) !== $table ) {
            return 0;
        }

        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
    }
}
