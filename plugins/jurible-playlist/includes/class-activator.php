<?php
/**
 * Plugin Activator - Creates database table
 */

if (!defined('ABSPATH')) {
    exit;
}

class Jurible_Playlist_Activator {

    public static function activate() {
        self::create_tables();
        self::set_default_options();
    }

    private static function create_tables() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'jurible_video_progress';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            video_id VARCHAR(100) NOT NULL,
            collection_id VARCHAR(100) NOT NULL,
            status ENUM('started', 'completed') DEFAULT 'started',
            watch_time INT DEFAULT 0,
            completed_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY user_video (user_id, video_id),
            KEY user_collection (user_id, collection_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option('jurible_playlist_db_version', JURIBLE_PLAYLIST_VERSION);
    }

    private static function set_default_options() {
        add_option('jurible_playlist_library_id', '35843');
        add_option('jurible_playlist_api_key', '');
        add_option('jurible_playlist_pull_zone_url', 'https://iframe.mediadelivery.net/embed/35843/');
    }
}
