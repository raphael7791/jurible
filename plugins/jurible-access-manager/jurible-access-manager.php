<?php
/**
 * Plugin Name: Jurible Access Manager
 * Description: Gère l'accès aux cours Fluent Community Pro lors d'achats SureCart.
 * Version: 1.0.0
 * Author: Jurible
 * Text Domain: jurible-access-manager
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'JAM_VERSION', '1.0.0' );
define( 'JAM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'JAM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Includes
require_once JAM_PLUGIN_DIR . 'includes/class-database.php';
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
    JAM_SureCart_Hooks::init();

    if ( is_admin() ) {
        JAM_Admin_Menu::init();
    }
}
