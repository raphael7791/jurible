<?php
/**
 * Plugin Name:       Jurible Aide Personnalisée
 * Description:       Aide personnalisée pour les étudiants Formule Réussite — questions de cours et corrections de copies
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Jurible
 * License:           GPL-2.0-or-later
 * Text Domain:       jurible-aide-perso
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'JAIDE_VERSION', '1.1.0' );
define( 'JAIDE_PATH', plugin_dir_path( __FILE__ ) );
define( 'JAIDE_URL', plugin_dir_url( __FILE__ ) );

// ── Activation ──────────────────────────────────────────────────────────────
register_activation_hook( __FILE__, 'jaide_activate' );

function jaide_activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table = $wpdb->prefix . 'jurible_aide_requests';

    $sql = "CREATE TABLE $table (
        id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id         BIGINT(20) UNSIGNED NOT NULL,
        type            ENUM('question','copie') NOT NULL,
        nom             VARCHAR(255) NOT NULL,
        email           VARCHAR(255) NOT NULL,
        annee           ENUM('L1','L2','L3','Capacite') NOT NULL,
        matiere         VARCHAR(255) NOT NULL,
        message         TEXT,
        file_url        VARCHAR(500) DEFAULT NULL,
        file_name       VARCHAR(255) DEFAULT NULL,
        status          ENUM('pending','in_progress','completed') DEFAULT 'pending',
        assigned_to     BIGINT(20) UNSIGNED DEFAULT NULL,
        response        TEXT,
        response_file_url VARCHAR(500) DEFAULT NULL,
        created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
        responded_at    DATETIME DEFAULT NULL,
        responded_by    BIGINT(20) UNSIGNED DEFAULT NULL,
        PRIMARY KEY (id),
        KEY idx_user    (user_id),
        KEY idx_type    (type),
        KEY idx_status  (status)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );

    update_option( 'jaide_version', JAIDE_VERSION );

    // Options par défaut
    if ( ! get_option( 'jaide_options' ) ) {
        update_option( 'jaide_options', [
            'product_id'            => '',
            'copies_limit'          => 1,
            'questions_limit'       => 0,
            'notify_prof_new'       => true,
            'notify_student_reply'  => true,
            'from_name'             => get_bloginfo( 'name' ),
            'from_email'            => get_option( 'admin_email' ),
        ] );
    }

    // Dossier uploads
    $upload_dir = wp_upload_dir();
    $aide_dir   = $upload_dir['basedir'] . '/jurible-aide-perso';
    if ( ! file_exists( $aide_dir ) ) {
        wp_mkdir_p( $aide_dir );
        file_put_contents( $aide_dir . '/.htaccess', 'Options -Indexes' );
    }
}

// ── Désactivation ───────────────────────────────────────────────────────────
register_deactivation_hook( __FILE__, 'jaide_deactivate' );

function jaide_deactivate() {
    // Rien à nettoyer pour l'instant
}

// ── Charger les fichiers ────────────────────────────────────────────────────
require_once JAIDE_PATH . 'includes/class-access.php';
require_once JAIDE_PATH . 'includes/class-api.php';
require_once JAIDE_PATH . 'includes/class-shortcode.php';
require_once JAIDE_PATH . 'includes/class-admin.php';
require_once JAIDE_PATH . 'includes/class-notifications.php';

// ── Hooks ───────────────────────────────────────────────────────────────────
add_action( 'rest_api_init', [ 'Jaide_API', 'register_routes' ] );
add_action( 'admin_menu', [ 'Jaide_Admin', 'add_menu' ] );
add_action( 'admin_enqueue_scripts', [ 'Jaide_Admin', 'enqueue_scripts' ] );
add_action( 'init', [ 'Jaide_Shortcode', 'register' ] );

// Badge compteur pending dans le menu admin
add_action( 'admin_menu', 'jaide_add_pending_count', 99 );

function jaide_add_pending_count() {
    global $wpdb, $menu;

    $table = $wpdb->prefix . 'jurible_aide_requests';
    $count = $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'pending'" );

    if ( $count > 0 ) {
        foreach ( $menu as $key => $item ) {
            if ( isset( $item[2] ) && $item[2] === 'jaide-inbox' ) {
                $menu[ $key ][0] .= ' <span class="awaiting-mod">' . $count . '</span>';
                break;
            }
        }
    }
}
