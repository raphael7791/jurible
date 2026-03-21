<?php
/**
 * Plugin Name: Jurible Access Manager
 * Description: Gère l'accès aux cours Fluent Community Pro lors d'achats SureCart.
 * Version: 1.3.0
 * Author: Jurible
 * Text Domain: jurible-access-manager
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'JAM_VERSION', '1.3.0' );
define( 'JAM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'JAM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Includes
require_once JAM_PLUGIN_DIR . 'includes/class-database.php';
require_once JAM_PLUGIN_DIR . 'includes/class-helpers.php';
require_once JAM_PLUGIN_DIR . 'includes/class-access-rules.php';
require_once JAM_PLUGIN_DIR . 'includes/class-access-log.php';
require_once JAM_PLUGIN_DIR . 'includes/class-enrollment.php';
require_once JAM_PLUGIN_DIR . 'includes/class-surecart-hooks.php';
require_once JAM_PLUGIN_DIR . 'includes/class-fluentcrm.php';
require_once JAM_PLUGIN_DIR . 'includes/class-sync.php';

if ( is_admin() ) {
    require_once JAM_PLUGIN_DIR . 'includes/admin/class-admin-menu.php';
}

// Activation
register_activation_hook( __FILE__, [ 'JAM_Database', 'activate' ] );

// Init
add_action( 'plugins_loaded', 'jam_init' );

function jam_init() {
    // Auto-migrate DB if version changed (handles updates without re-activation)
    if ( get_option( 'jam_db_version', '0' ) !== JAM_VERSION ) {
        JAM_Database::activate();
    }

    JAM_SureCart_Hooks::init();

    if ( is_admin() ) {
        JAM_Admin_Menu::init();
    }
}

// FC frame-template & block: notification dropdown script
// Hooks into FC's own asset action so it loads on ALL FC-framed pages (template + Gutenberg block)
add_action( 'fluent_community/enqueue_global_assets', 'jam_enqueue_fc_notifications' );

function jam_enqueue_fc_notifications() {
    if ( ! is_user_logged_in() ) {
        return;
    }

    wp_enqueue_style(
        'jam-fc-notifications',
        JAM_PLUGIN_URL . 'assets/fc-notifications.css',
        [],
        JAM_VERSION
    );

    wp_enqueue_script(
        'jam-fc-notifications',
        JAM_PLUGIN_URL . 'assets/fc-notifications.js',
        [],
        JAM_VERSION,
        true
    );

    wp_localize_script( 'jam-fc-notifications', 'jamFcNotif', [
        'restUrl' => esc_url_raw( rest_url( 'fluent-community/v2' ) ),
        'nonce'   => wp_create_nonce( 'wp_rest' ),
    ] );
}
