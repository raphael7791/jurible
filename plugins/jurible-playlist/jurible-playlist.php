<?php
/**
 * Plugin Name: Jurible Playlist
 * Plugin URI: https://jurible.com
 * Description: Affiche des playlists de vidéos hébergées sur Bunny Stream avec tracking de progression
 * Version: 1.0.0
 * Author: Jurible
 * Author URI: https://jurible.com
 * License: GPL v2 or later
 * Text Domain: jurible-playlist
 */

if (!defined('ABSPATH')) {
    exit;
}

define('JURIBLE_PLAYLIST_VERSION', '1.0.0');
define('JURIBLE_PLAYLIST_PATH', plugin_dir_path(__FILE__));
define('JURIBLE_PLAYLIST_URL', plugin_dir_url(__FILE__));

class Jurible_Playlist {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    private function load_dependencies() {
        require_once JURIBLE_PLAYLIST_PATH . 'includes/class-activator.php';
        require_once JURIBLE_PLAYLIST_PATH . 'includes/class-bunny-api.php';
        require_once JURIBLE_PLAYLIST_PATH . 'includes/class-rest-api.php';
        require_once JURIBLE_PLAYLIST_PATH . 'admin/class-admin.php';
    }

    private function init_hooks() {
        // Activation/Deactivation
        register_activation_hook(__FILE__, array('Jurible_Playlist_Activator', 'activate'));

        // REST API
        add_action('rest_api_init', array('Jurible_Playlist_Rest_API', 'register_routes'));

        // Admin
        if (is_admin()) {
            new Jurible_Playlist_Admin();
        }

        // Delete user data on user deletion
        add_action('delete_user', array($this, 'delete_user_data'));
    }

    public function delete_user_data($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'jurible_video_progress';
        $wpdb->delete($table_name, array('user_id' => $user_id), array('%d'));
    }

    public static function get_option($key, $default = '') {
        return get_option('jurible_playlist_' . $key, $default);
    }
}

// Initialize plugin
function jurible_playlist() {
    return Jurible_Playlist::get_instance();
}

add_action('plugins_loaded', 'jurible_playlist');
