<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class JAM_Database {

    public static function activate() {
        self::create_tables();
        update_option( 'jam_db_version', JAM_VERSION );
    }

    public static function create_tables() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        $rules_table = $wpdb->prefix . 'jam_access_rules';
        $log_table   = $wpdb->prefix . 'jam_access_log';

        $sql = "CREATE TABLE {$rules_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            rule_name VARCHAR(255) NOT NULL,
            sc_product_id VARCHAR(100) NOT NULL,
            fcom_course_ids TEXT NOT NULL,
            crm_tag_ids TEXT DEFAULT NULL,
            crm_list_ids TEXT DEFAULT NULL,
            credit_amount INT UNSIGNED DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY sc_product_id (sc_product_id)
        ) {$charset};

        CREATE TABLE {$log_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            user_email VARCHAR(255) NOT NULL DEFAULT '',
            fcom_course_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            action VARCHAR(20) NOT NULL DEFAULT '',
            source VARCHAR(20) NOT NULL DEFAULT '',
            sc_purchase_id VARCHAR(100) DEFAULT NULL,
            details TEXT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY fcom_course_id (fcom_course_id),
            KEY action (action),
            KEY source (source),
            KEY created_at (created_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public static function get_rules_table() {
        global $wpdb;
        return $wpdb->prefix . 'jam_access_rules';
    }

    public static function get_log_table() {
        global $wpdb;
        return $wpdb->prefix . 'jam_access_log';
    }
}
