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
        add_action( 'wp_ajax_jam_get_user_details', [ __CLASS__, 'ajax_get_user_details' ] );
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
            'Utilisateurs',
            'Utilisateurs',
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
        require_once JAM_PLUGIN_DIR . 'includes/admin/page-utilisateurs.php';
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

        $dry_run = ! empty( $_POST['dry_run'] );
        $report  = JAM_Sync::run( $dry_run );
        wp_send_json_success( $report );
    }

    // ─── AJAX: Get User Details (subscriptions + coherence) ───
    public static function ajax_get_user_details() {
        check_ajax_referer( 'jam_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission refusée.' );
        }

        $user_id = absint( $_POST['user_id'] ?? 0 );
        if ( ! $user_id ) {
            wp_send_json_error( 'ID utilisateur manquant.' );
        }

        $user = get_userdata( $user_id );
        if ( ! $user ) {
            wp_send_json_error( 'Utilisateur introuvable.' );
        }

        // ─── 1. SureCart subscriptions + purchases ───
        $customer_ids = get_user_meta( $user_id, 'sc_customer_ids', true );
        $subscriptions = [];
        $purchases     = [];
        $active_product_ids = [];

        $sc_token = JAM_Helpers::get_sc_api_token();
        if ( ! empty( $customer_ids ) && $sc_token ) {
            $cids = is_array( $customer_ids ) ? $customer_ids : [ $customer_ids ];

            foreach ( $cids as $cid ) {
                // Subscriptions
                $sub_url  = 'https://api.surecart.com/v1/subscriptions?customer_ids[]=' . urlencode( $cid ) . '&expand[]=price&expand[]=price.product&limit=100';
                $sub_resp = wp_remote_get( $sub_url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $sc_token,
                        'Content-Type'  => 'application/json',
                    ],
                    'timeout' => 15,
                ] );

                if ( ! is_wp_error( $sub_resp ) ) {
                    $sub_body = json_decode( wp_remote_retrieve_body( $sub_resp ), true );
                    foreach ( ( $sub_body['data'] ?? [] ) as $sub ) {
                        $price   = $sub['price'] ?? [];
                        $product = $price['product'] ?? [];

                        $amount   = ( $price['amount'] ?? 0 ) / 100;
                        $currency = strtoupper( $price['currency'] ?? 'EUR' );
                        $interval = $price['recurring_interval'] ?? '';
                        $period   = '';
                        if ( $interval === 'month' ) {
                            $count = $price['recurring_interval_count'] ?? 1;
                            $period = $count == 1 ? '/mois' : '/' . $count . ' mois';
                        } elseif ( $interval === 'year' ) {
                            $period = '/an';
                        } elseif ( $interval === 'week' ) {
                            $period = '/sem.';
                        }

                        $product_id   = is_array( $product ) ? ( $product['id'] ?? '' ) : $product;
                        $product_name = is_array( $product ) ? ( $product['name'] ?? '—' ) : '—';
                        $status       = $sub['status'] ?? 'unknown';

                        $subscriptions[] = [
                            'product_name' => $product_name,
                            'product_id'   => $product_id,
                            'price'        => number_format( $amount, 2, ',', ' ' ) . ' ' . $currency . $period,
                            'status'       => $status,
                            'created_at'   => ! empty( $sub['created_at'] )
                                ? wp_date( 'd/m/Y', $sub['created_at'] )
                                : '—',
                        ];

                        // Track active products
                        if ( in_array( $status, [ 'active', 'trialing' ], true ) && $product_id ) {
                            $active_product_ids[] = $product_id;
                        }
                    }
                }

                // Purchases (one-shot)
                $pur_url  = 'https://api.surecart.com/v1/purchases?customer_ids[]=' . urlencode( $cid ) . '&expand[]=product&limit=100';
                $pur_resp = wp_remote_get( $pur_url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $sc_token,
                        'Content-Type'  => 'application/json',
                    ],
                    'timeout' => 15,
                ] );

                if ( ! is_wp_error( $pur_resp ) ) {
                    $pur_body = json_decode( wp_remote_retrieve_body( $pur_resp ), true );
                    foreach ( ( $pur_body['data'] ?? [] ) as $pur ) {
                        $product    = $pur['product'] ?? [];
                        $product_id = is_array( $product ) ? ( $product['id'] ?? '' ) : $product;

                        // Skip purchases that are part of a subscription (already counted above)
                        if ( ! empty( $pur['subscription'] ) ) {
                            continue;
                        }

                        $product_name = is_array( $product ) ? ( $product['name'] ?? '—' ) : '—';
                        $status       = $pur['status'] ?? 'unknown';

                        // Map purchase statuses
                        $status_display = $status;
                        if ( $status === 'paid' || $status === 'completed' ) {
                            $status_display = 'active';
                        }

                        $purchases[] = [
                            'product_name' => $product_name,
                            'product_id'   => $product_id,
                            'status'       => $status_display,
                            'created_at'   => ! empty( $pur['created_at'] )
                                ? wp_date( 'd/m/Y', $pur['created_at'] )
                                : '—',
                        ];

                        if ( in_array( $status, [ 'paid', 'completed', 'active' ], true ) && $product_id ) {
                            $active_product_ids[] = $product_id;
                        }
                    }
                }
            }
        }

        $active_product_ids = array_unique( $active_product_ids );

        // ─── 2. Expected courses from rules ───
        $expected_course_ids = [];
        foreach ( $active_product_ids as $pid ) {
            $rules = JAM_Access_Rules::find_by_product( $pid );
            foreach ( $rules as $rule ) {
                $cids = JAM_Access_Rules::get_course_ids( $rule );
                $expected_course_ids = array_merge( $expected_course_ids, $cids );
            }
        }
        $expected_course_ids = array_unique( array_map( 'intval', $expected_course_ids ) );

        // ─── 3. Actual enrollments ───
        global $wpdb;
        $su_table      = $wpdb->prefix . 'fcom_space_user';
        $enrolled_ids  = [];
        $has_table     = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $su_table ) ) === $su_table;
        if ( $has_table ) {
            $enrolled_ids = array_map( 'intval', $wpdb->get_col( $wpdb->prepare(
                "SELECT space_id FROM {$su_table} WHERE user_id = %d",
                $user_id
            ) ) );
        }

        // ─── 4. Build course list with coherence ───
        $all_courses = JAM_Helpers::get_fcom_courses();
        $course_list = [];
        foreach ( $all_courses as $c ) {
            $cid        = (int) $c['id'];
            $is_enrolled = in_array( $cid, $enrolled_ids, true );
            $is_expected = in_array( $cid, $expected_course_ids, true );

            $coherence = 'ok';
            if ( $is_expected && ! $is_enrolled ) {
                $coherence = 'missing'; // Should be enrolled but isn't
            } elseif ( ! $is_expected && $is_enrolled ) {
                $coherence = 'extra'; // Enrolled but no rule justifies it
            }

            $course_list[] = [
                'id'         => $cid,
                'title'      => $c['title'],
                'enrolled'   => $is_enrolled,
                'expected'   => $is_expected,
                'coherence'  => $coherence,
            ];
        }

        // Sort: missing first, then extra, then enrolled, then not enrolled
        usort( $course_list, function( $a, $b ) {
            $order = [ 'missing' => 0, 'extra' => 1, 'ok' => 2 ];
            $oa = $order[ $a['coherence'] ] ?? 2;
            $ob = $order[ $b['coherence'] ] ?? 2;
            if ( $oa !== $ob ) return $oa - $ob;
            // Within same coherence: enrolled first
            if ( $a['enrolled'] !== $b['enrolled'] ) return $b['enrolled'] - $a['enrolled'];
            return strcmp( $a['title'], $b['title'] );
        } );

        wp_send_json_success( [
            'subscriptions' => $subscriptions,
            'purchases'     => $purchases,
            'courses'       => $course_list,
        ] );
    }
}
