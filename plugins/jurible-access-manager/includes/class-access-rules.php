<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class JAM_Access_Rules {

    /**
     * Get all rules.
     */
    public static function get_all() {
        global $wpdb;
        $table = JAM_Database::get_rules_table();
        return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC" );
    }

    /**
     * Get a single rule by ID.
     */
    public static function get( $id ) {
        global $wpdb;
        $table = JAM_Database::get_rules_table();
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
    }

    /**
     * Find rules matching a SureCart product ID.
     */
    public static function find_by_product( $product_id ) {
        global $wpdb;
        $table = JAM_Database::get_rules_table();
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE sc_product_id = %s",
            $product_id
        ) );
    }

    /**
     * Count total rules.
     */
    public static function count() {
        global $wpdb;
        $table = JAM_Database::get_rules_table();
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
    }

    /**
     * Create a new rule.
     */
    public static function create( $data ) {
        global $wpdb;
        $table = JAM_Database::get_rules_table();

        $result = $wpdb->insert( $table, [
            'rule_name'       => sanitize_text_field( $data['rule_name'] ),
            'sc_product_id'   => sanitize_text_field( $data['sc_product_id'] ),
            'fcom_course_ids' => wp_json_encode( array_map( 'absint', (array) ( $data['fcom_course_ids'] ?? [] ) ) ),
            'crm_tag_ids'     => ! empty( $data['crm_tag_ids'] ) ? wp_json_encode( array_map( 'absint', (array) $data['crm_tag_ids'] ) ) : null,
            'crm_list_ids'    => ! empty( $data['crm_list_ids'] ) ? wp_json_encode( array_map( 'absint', (array) $data['crm_list_ids'] ) ) : null,
            'credit_amount'   => absint( $data['credit_amount'] ?? 0 ),
            'created_at'      => current_time( 'mysql' ),
            'updated_at'      => current_time( 'mysql' ),
        ] );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update an existing rule.
     */
    public static function update( $id, $data ) {
        global $wpdb;
        $table = JAM_Database::get_rules_table();

        return $wpdb->update(
            $table,
            [
                'rule_name'       => sanitize_text_field( $data['rule_name'] ),
                'sc_product_id'   => sanitize_text_field( $data['sc_product_id'] ),
                'fcom_course_ids' => wp_json_encode( array_map( 'absint', (array) ( $data['fcom_course_ids'] ?? [] ) ) ),
                'crm_tag_ids'     => ! empty( $data['crm_tag_ids'] ) ? wp_json_encode( array_map( 'absint', (array) $data['crm_tag_ids'] ) ) : null,
                'crm_list_ids'    => ! empty( $data['crm_list_ids'] ) ? wp_json_encode( array_map( 'absint', (array) $data['crm_list_ids'] ) ) : null,
                'credit_amount'   => absint( $data['credit_amount'] ?? 0 ),
                'updated_at'      => current_time( 'mysql' ),
            ],
            [ 'id' => $id ],
            null,
            [ '%d' ]
        );
    }

    /**
     * Delete a rule.
     */
    public static function delete( $id ) {
        global $wpdb;
        $table = JAM_Database::get_rules_table();
        return $wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] );
    }

    /**
     * Get decoded course IDs from a rule.
     */
    public static function get_course_ids( $rule ) {
        $ids = json_decode( $rule->fcom_course_ids, true );
        return is_array( $ids ) ? $ids : [];
    }

    /**
     * Get decoded CRM tag IDs from a rule.
     */
    public static function get_crm_tag_ids( $rule ) {
        if ( empty( $rule->crm_tag_ids ) ) {
            return [];
        }
        $ids = json_decode( $rule->crm_tag_ids, true );
        return is_array( $ids ) ? $ids : [];
    }

    /**
     * Get decoded CRM list IDs from a rule.
     */
    public static function get_crm_list_ids( $rule ) {
        if ( empty( $rule->crm_list_ids ) ) {
            return [];
        }
        $ids = json_decode( $rule->crm_list_ids, true );
        return is_array( $ids ) ? $ids : [];
    }
}
