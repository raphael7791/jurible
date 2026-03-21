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
        add_action( 'wp_ajax_jam_get_users_products', [ __CLASS__, 'ajax_get_users_products' ] );
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

        set_time_limit( 600 );

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

    // ─── AJAX: Get Products + coherence for multiple users (batch) ───
    public static function ajax_get_users_products() {
        check_ajax_referer( 'jam_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission refusée.' );
        }

        $user_ids = array_map( 'absint', (array) ( $_POST['user_ids'] ?? [] ) );
        $user_ids = array_filter( $user_ids );

        if ( empty( $user_ids ) ) {
            wp_send_json_success( [] );
        }

        // Build map: customer_id → user_id
        $customer_to_user = [];
        foreach ( $user_ids as $uid ) {
            $cids = get_user_meta( $uid, 'sc_customer_ids', true );
            if ( empty( $cids ) ) {
                continue;
            }
            $cids = is_array( $cids ) ? $cids : [ $cids ];
            foreach ( $cids as $cid ) {
                $customer_to_user[ $cid ] = $uid;
            }
        }

        // Initialize results
        $results = [];
        foreach ( $user_ids as $uid ) {
            $results[ $uid ] = [ 'products' => [] ];
        }

        // ─── Fetch SC products ───
        $sc_token = JAM_Helpers::get_sc_api_token();
        $all_cids = array_keys( $customer_to_user );

        if ( ! empty( $all_cids ) && $sc_token ) {
            $headers = [
                'Authorization' => 'Bearer ' . $sc_token,
                'Content-Type'  => 'application/json',
            ];

            // Subscriptions (paginated)
            $page = 1;
            do {
                $url = 'https://api.surecart.com/v1/subscriptions?expand[]=price&expand[]=price.product&limit=100&page=' . $page;
                foreach ( $all_cids as $cid ) {
                    $url .= '&customer_ids[]=' . urlencode( $cid );
                }
                $resp = wp_remote_get( $url, [ 'headers' => $headers, 'timeout' => 30 ] );
                if ( is_wp_error( $resp ) ) break;
                $body = json_decode( wp_remote_retrieve_body( $resp ), true );

                foreach ( ( $body['data'] ?? [] ) as $sub ) {
                    $cid = $sub['customer'] ?? '';
                    if ( ! isset( $customer_to_user[ $cid ] ) ) continue;
                    $uid     = $customer_to_user[ $cid ];
                    $price   = $sub['price'] ?? [];
                    $product = $price['product'] ?? [];
                    $pid     = is_array( $product ) ? ( $product['id'] ?? '' ) : $product;
                    $name    = is_array( $product ) ? ( $product['name'] ?? '—' ) : '—';
                    $status  = $sub['status'] ?? 'unknown';

                    $results[ $uid ]['products'][] = [
                        'name'       => $name,
                        'product_id' => $pid,
                        'status'     => in_array( $status, [ 'active', 'trialing' ], true ) ? 'active' : $status,
                        'type'       => 'subscription',
                    ];
                }

                $has_more = ! empty( $body['pagination']['next_page'] );
                $page++;
            } while ( $has_more && $page <= 10 );

            // Purchases (paginated)
            $page = 1;
            do {
                $url = 'https://api.surecart.com/v1/purchases?expand[]=product&limit=100&page=' . $page;
                foreach ( $all_cids as $cid ) {
                    $url .= '&customer_ids[]=' . urlencode( $cid );
                }
                $resp = wp_remote_get( $url, [ 'headers' => $headers, 'timeout' => 30 ] );
                if ( is_wp_error( $resp ) ) break;
                $body = json_decode( wp_remote_retrieve_body( $resp ), true );

                foreach ( ( $body['data'] ?? [] ) as $pur ) {
                    if ( ! empty( $pur['subscription'] ) ) continue;
                    $cid = $pur['customer'] ?? '';
                    if ( ! isset( $customer_to_user[ $cid ] ) ) continue;
                    $uid     = $customer_to_user[ $cid ];
                    $product = $pur['product'] ?? [];
                    $pid     = is_array( $product ) ? ( $product['id'] ?? '' ) : $product;
                    $name    = is_array( $product ) ? ( $product['name'] ?? '—' ) : '—';
                    $status  = $pur['status'] ?? 'unknown';
                    $revoked = ! empty( $pur['revoked'] );
                    $active  = ! $revoked && in_array( $status, [ 'paid', 'completed', 'active' ], true );

                    $results[ $uid ]['products'][] = [
                        'name'       => $name,
                        'product_id' => $pid,
                        'status'     => $revoked ? 'revoked' : ( $active ? 'active' : $status ),
                        'type'       => 'purchase',
                    ];
                }

                $has_more = ! empty( $body['pagination']['next_page'] );
                $page++;
            } while ( $has_more && $page <= 10 );
        }

        // Deduplicate products per user
        foreach ( $results as $uid => &$data ) {
            $seen = [];
            $deduped = [];
            foreach ( $data['products'] as $p ) {
                $key = $p['name'] . '|' . $p['status'];
                if ( ! isset( $seen[ $key ] ) ) {
                    $seen[ $key ] = true;
                    $deduped[] = $p;
                }
            }
            $data['products'] = $deduped;
        }
        unset( $data );

        // ─── Compute coherence ───
        global $wpdb;
        $su_table = $wpdb->prefix . 'fcom_space_user';
        $s_table  = $wpdb->prefix . 'fcom_spaces';

        // Batch get enrollments for all users
        $user_enrollments = [];
        $has_table = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $su_table ) ) === $su_table;
        if ( $has_table && ! empty( $user_ids ) ) {
            $ids_str = implode( ',', array_map( 'intval', $user_ids ) );
            $rows = $wpdb->get_results(
                "SELECT su.user_id, su.space_id
                 FROM {$su_table} su
                 INNER JOIN {$s_table} s ON su.space_id = s.id AND s.type = 'course'
                 WHERE su.user_id IN ({$ids_str})"
            );
            foreach ( $rows as $row ) {
                $user_enrollments[ (int) $row->user_id ][] = (int) $row->space_id;
            }
        }

        foreach ( $results as $uid => &$data ) {
            $enrolled_ids = $user_enrollments[ $uid ] ?? [];
            $products     = $data['products'];

            // Expected course IDs from active products via rules
            $expected_ids = [];
            $has_active   = false;
            foreach ( $products as $p ) {
                if ( in_array( $p['status'], [ 'active', 'trialing' ], true ) ) {
                    $has_active = true;
                    if ( ! empty( $p['product_id'] ) ) {
                        $rules = JAM_Access_Rules::find_by_product( $p['product_id'] );
                        foreach ( $rules as $rule ) {
                            $cids = JAM_Access_Rules::get_course_ids( $rule );
                            $expected_ids = array_merge( $expected_ids, $cids );
                        }
                    }
                }
            }
            $expected_ids        = array_unique( array_map( 'intval', $expected_ids ) );
            $enrolled_in_expected = count( array_intersect( $enrolled_ids, $expected_ids ) );
            $expected_count      = count( $expected_ids );
            $enrolled_total      = count( $enrolled_ids );

            // Determine coherence
            if ( empty( $products ) ) {
                $coherence = $enrolled_total > 0 ? 'extra' : 'none';
            } elseif ( $has_active ) {
                if ( $expected_count === 0 ) {
                    $coherence = 'no_rules';
                } elseif ( $enrolled_in_expected >= $expected_count ) {
                    $coherence = 'ok';
                } else {
                    $coherence = 'missing';
                }
            } else {
                // All products expired/canceled
                $coherence = $enrolled_total > 0 ? 'extra' : 'expired_ok';
            }

            $data['enrolled_count'] = $has_active ? $enrolled_in_expected : $enrolled_total;
            $data['expected_count'] = $expected_count;
            $data['enrolled_total'] = $enrolled_total;
            $data['coherence']      = $coherence;
        }
        unset( $data );

        wp_send_json_success( $results );
    }
}
