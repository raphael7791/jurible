<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class JAM_Admin_Menu {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );

        // AJAX handlers
        add_action( 'wp_ajax_jam_search_users', [ __CLASS__, 'ajax_search_users' ] );
        add_action( 'wp_ajax_jam_toggle_enrollment', [ __CLASS__, 'ajax_toggle_enrollment' ] );
        add_action( 'wp_ajax_jam_run_sync', [ __CLASS__, 'ajax_run_sync' ] );
        add_action( 'wp_ajax_jam_toggle_product_new', [ __CLASS__, 'ajax_toggle_product_new' ] );
    }

    public static function register_menu() {
        add_menu_page(
            'Access Manager',
            'Access Manager',
            'manage_options',
            'jam-dashboard',
            [ __CLASS__, 'render_dashboard' ],
            'dashicons-lock',
            30
        );

        add_submenu_page(
            'jam-dashboard',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'jam-dashboard',
            [ __CLASS__, 'render_dashboard' ]
        );

        add_submenu_page(
            'jam-dashboard',
            'Règles d\'accès',
            'Règles d\'accès',
            'manage_options',
            'jam-rules',
            [ __CLASS__, 'render_rules' ]
        );

        add_submenu_page(
            'jam-dashboard',
            'Historique',
            'Historique',
            'manage_options',
            'jam-logs',
            [ __CLASS__, 'render_logs' ]
        );

        add_submenu_page(
            'jam-dashboard',
            'Gestion manuelle',
            'Gestion manuelle',
            'manage_options',
            'jam-manual',
            [ __CLASS__, 'render_manual' ]
        );
    }

    public static function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'jam-' ) === false && $hook !== 'toplevel_page_jam-dashboard' ) {
            return;
        }

        wp_enqueue_style(
            'jam-admin',
            JAM_PLUGIN_URL . 'assets/admin.css',
            [],
            JAM_VERSION
        );

        wp_enqueue_script(
            'jam-admin',
            JAM_PLUGIN_URL . 'assets/admin.js',
            [ 'jquery' ],
            JAM_VERSION,
            true
        );

        wp_localize_script( 'jam-admin', 'jamAdmin', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'jam_admin_nonce' ),
        ] );
    }

    public static function render_dashboard() {
        require_once JAM_PLUGIN_DIR . 'includes/admin/page-dashboard.php';
    }

    public static function render_rules() {
        require_once JAM_PLUGIN_DIR . 'includes/admin/page-rules.php';
    }

    public static function render_logs() {
        require_once JAM_PLUGIN_DIR . 'includes/admin/page-logs.php';
    }

    public static function render_manual() {
        require_once JAM_PLUGIN_DIR . 'includes/admin/page-manual-access.php';
    }

    // ─── AJAX: Search Users ───
    public static function ajax_search_users() {
        check_ajax_referer( 'jam_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission refusée.' );
        }

        $search = sanitize_text_field( wp_unslash( $_POST['search'] ?? '' ) );
        if ( strlen( $search ) < 2 ) {
            wp_send_json_success( [] );
        }

        $users = get_users( [
            'search'         => '*' . $search . '*',
            'search_columns' => [ 'user_login', 'user_email', 'display_name' ],
            'number'         => 20,
        ] );

        $results = [];
        foreach ( $users as $user ) {
            $results[] = [
                'id'           => $user->ID,
                'display_name' => $user->display_name,
                'email'        => $user->user_email,
                'avatar'       => get_avatar_url( $user->ID, [ 'size' => 40 ] ),
                'registered'   => $user->user_registered,
            ];
        }

        wp_send_json_success( $results );
    }

    // ─── AJAX: Toggle Enrollment ───
    public static function ajax_toggle_enrollment() {
        check_ajax_referer( 'jam_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission refusée.' );
        }

        $user_id   = absint( $_POST['user_id'] ?? 0 );
        $course_id = absint( $_POST['course_id'] ?? 0 );
        $action    = sanitize_text_field( $_POST['enrollment_action'] ?? '' );

        if ( ! $user_id || ! $course_id || ! in_array( $action, [ 'enroll', 'unenroll' ], true ) ) {
            wp_send_json_error( 'Paramètres invalides.' );
        }

        if ( $action === 'enroll' ) {
            $result = JAM_Enrollment::enroll_user( $user_id, $course_id, 'manual' );
        } else {
            $result = JAM_Enrollment::unenroll_user( $user_id, $course_id, 'manual' );
        }

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
        }

        wp_send_json_success( [
            'message' => $action === 'enroll' ? 'Utilisateur inscrit.' : 'Utilisateur désinscrit.',
        ] );
    }

    // ─── AJAX: Toggle Product New/Old ───
    public static function ajax_toggle_product_new() {
        check_ajax_referer( 'jam_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission refusée.' );
        }

        $product_id = sanitize_text_field( $_POST['product_id'] ?? '' );
        $is_new     = (bool) ( $_POST['is_new'] ?? false );

        if ( ! $product_id ) {
            wp_send_json_error( 'ID produit manquant.' );
        }

        $new_ids = get_option( 'jam_new_product_ids', [] );
        $old_ids = get_option( 'jam_old_product_ids', [] );

        if ( $is_new ) {
            // Mark as new: add to new list, remove from old overrides
            $new_ids[ $product_id ] = true;
            unset( $old_ids[ $product_id ] );
        } else {
            // Mark as old: add to old overrides, remove from new list
            $old_ids[ $product_id ] = true;
            unset( $new_ids[ $product_id ] );
        }

        update_option( 'jam_new_product_ids', $new_ids );
        update_option( 'jam_old_product_ids', $old_ids );

        // Clear product cache to reflect changes
        delete_transient( 'jam_sc_products_v2' );

        wp_send_json_success();
    }

    // ─── AJAX: Run Sync ───
    public static function ajax_run_sync() {
        check_ajax_referer( 'jam_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission refusée.' );
        }

        $report = JAM_Sync::run();
        wp_send_json_success( $report );
    }
}
