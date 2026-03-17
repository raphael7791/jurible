<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class JAM_Access_Log {

    /**
     * Log an action.
     *
     * @param array $data {
     *     @type int    $user_id
     *     @type string $user_email
     *     @type int    $fcom_course_id
     *     @type string $action          enrolled|unenrolled|credits_added
     *     @type string $source          surecart|manual|sync
     *     @type string $sc_purchase_id  Optional.
     *     @type string $details         JSON string.
     * }
     */
    public static function log( $data ) {
        global $wpdb;
        $table = JAM_Database::get_log_table();

        // Ensure table exists
        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) !== $table ) {
            JAM_Database::create_tables();
        }

        $wpdb->insert( $table, [
            'user_id'        => absint( $data['user_id'] ?? 0 ),
            'user_email'     => sanitize_email( $data['user_email'] ?? '' ),
            'fcom_course_id' => absint( $data['fcom_course_id'] ?? 0 ),
            'action'         => sanitize_text_field( $data['action'] ?? '' ),
            'source'         => sanitize_text_field( $data['source'] ?? '' ),
            'sc_purchase_id' => ! empty( $data['sc_purchase_id'] ) ? sanitize_text_field( $data['sc_purchase_id'] ) : null,
            'details'        => $data['details'] ?? null,
            'created_at'     => current_time( 'mysql' ),
        ] );
    }

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

    /**
     * Get paginated log entries with optional filters.
     *
     * @param array $args {
     *     @type int    $per_page   Default 25.
     *     @type int    $page       Default 1.
     *     @type string $action     Filter by action.
     *     @type string $source     Filter by source.
     *     @type string $search     Search by email.
     *     @type int    $course_id  Filter by course ID.
     * }
     * @return array [ 'items' => [], 'total' => int ]
     */
    public static function get_paginated( $args = [] ) {
        global $wpdb;
        $table = JAM_Database::get_log_table();

        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) !== $table ) {
            return [ 'items' => [], 'total' => 0 ];
        }

        $per_page = absint( $args['per_page'] ?? 25 );
        $page     = max( 1, absint( $args['page'] ?? 1 ) );
        $offset   = ( $page - 1 ) * $per_page;

        $where   = [];
        $values  = [];

        if ( ! empty( $args['action'] ) ) {
            $where[]  = 'action = %s';
            $values[] = $args['action'];
        }

        if ( ! empty( $args['source'] ) ) {
            $where[]  = 'source = %s';
            $values[] = $args['source'];
        }

        if ( ! empty( $args['search'] ) ) {
            $where[]  = 'user_email LIKE %s';
            $values[] = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        }

        if ( ! empty( $args['course_id'] ) ) {
            $where[]  = 'fcom_course_id = %d';
            $values[] = absint( $args['course_id'] );
        }

        $where_sql = '';
        if ( ! empty( $where ) ) {
            $where_sql = 'WHERE ' . implode( ' AND ', $where );
        }

        // Count
        $count_sql = "SELECT COUNT(*) FROM {$table} {$where_sql}";
        if ( ! empty( $values ) ) {
            $count_sql = $wpdb->prepare( $count_sql, ...$values );
        }
        $total = (int) $wpdb->get_var( $count_sql );

        // Fetch
        $query = "SELECT * FROM {$table} {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $query_values = array_merge( $values, [ $per_page, $offset ] );
        $items = $wpdb->get_results( $wpdb->prepare( $query, ...$query_values ) );

        return [
            'items' => $items ?: [],
            'total' => $total,
        ];
    }

    /**
     * Get recent logs for a specific user.
     */
    public static function get_user_logs( $user_id, $limit = 10 ) {
        global $wpdb;
        $table = JAM_Database::get_log_table();

        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) !== $table ) {
            return [];
        }

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC LIMIT %d",
            $user_id,
            $limit
        ) );
    }
}
