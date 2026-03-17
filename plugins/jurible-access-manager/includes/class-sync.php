<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class JAM_Sync {

    /**
     * Run synchronization: loop through configured products, fetch active
     * subscriptions/purchases from SureCart, resolve WP users, enroll.
     *
     * @param bool $dry_run If true, simulate without enrolling.
     * @return array Report.
     */
    public static function run( $dry_run = false ) {
        $start = microtime( true );

        $report = [
            'enrolled'         => 0,
            'already_enrolled' => 0,
            'errors'           => 0,
            'products'         => [],
            'error_emails'     => [],
            'dry_run'          => $dry_run,
            'duration'         => 0,
        ];

        if ( ! function_exists( 'sc_api_token' ) ) {
            $report['message'] = 'SureCart API indisponible (sc_api_token introuvable).';
            return $report;
        }

        // 1. Load all rules, group by product_id
        $all_rules     = JAM_Access_Rules::get_all();
        $rules_by_product = [];

        foreach ( $all_rules as $rule ) {
            $pid = $rule->sc_product_id;
            if ( ! isset( $rules_by_product[ $pid ] ) ) {
                $rules_by_product[ $pid ] = [];
            }
            $rules_by_product[ $pid ][] = $rule;
        }

        if ( empty( $rules_by_product ) ) {
            $report['message'] = 'Aucune règle d\'accès configurée.';
            $report['duration'] = round( microtime( true ) - $start, 1 );
            return $report;
        }

        // 2. For each product, fetch subscriptions + purchases, process customers
        foreach ( $rules_by_product as $product_id => $rules ) {
            $product_name    = $rules[0]->rule_name ?? $product_id;
            $product_report  = [
                'name'    => $product_name,
                'enrolled' => 0,
                'already'  => 0,
                'errors'   => 0,
            ];

            // Collect unique customer emails for this product
            $customer_emails = [];

            // 2a. Active subscriptions
            $subscriptions = self::fetch_active_subscriptions( $product_id );
            foreach ( $subscriptions as $sub ) {
                $email = strtolower( trim( $sub['email'] ) );
                if ( $email ) {
                    $customer_emails[ $email ] = true;
                }
            }

            // 2b. Active purchases (one-shot only, no subscription)
            $purchases = self::fetch_active_purchases( $product_id );
            foreach ( $purchases as $pur ) {
                $email = strtolower( trim( $pur['email'] ) );
                if ( $email ) {
                    $customer_emails[ $email ] = true;
                }
            }

            // 2c. Process each customer
            foreach ( array_keys( $customer_emails ) as $email ) {
                $result = self::process_customer( $email, $rules, $dry_run );

                $product_report['enrolled'] += $result['enrolled'];
                $product_report['already']  += $result['already'];
                $product_report['errors']   += $result['errors'];

                if ( ! empty( $result['error_email'] ) ) {
                    $report['error_emails'][] = $result['error_email'];
                }
            }

            $report['enrolled']         += $product_report['enrolled'];
            $report['already_enrolled'] += $product_report['already'];
            $report['errors']           += $product_report['errors'];
            $report['products'][]        = $product_report;
        }

        $report['error_emails'] = array_unique( $report['error_emails'] );
        $report['duration']     = round( microtime( true ) - $start, 1 );

        return $report;
    }

    /**
     * Fetch all active/trialing subscriptions for a product (paginated).
     *
     * @param string $product_id SureCart product ID.
     * @return array [ ['email' => '...'], ... ]
     */
    private static function fetch_active_subscriptions( $product_id ) {
        $results  = [];
        $page     = 1;
        $limit    = 100;
        $token    = sc_api_token();

        do {
            $offset = ( $page - 1 ) * $limit;
            $url    = add_query_arg( [
                'status[]'       => 'active',
                'product_ids[]'  => $product_id,
                'expand[]'       => 'customer',
                'limit'          => $limit,
                'offset'         => $offset,
            ], 'https://api.surecart.com/v1/subscriptions' );

            // SureCart API needs multiple status[] params — build manually
            $url = 'https://api.surecart.com/v1/subscriptions'
                . '?status[]=active&status[]=trialing'
                . '&product_ids[]=' . urlencode( $product_id )
                . '&expand[]=customer'
                . '&limit=' . $limit
                . '&offset=' . $offset;

            $response = wp_remote_get( $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                ],
                'timeout' => 30,
            ] );

            if ( is_wp_error( $response ) ) {
                break;
            }

            $body     = json_decode( wp_remote_retrieve_body( $response ), true );
            $items    = $body['data'] ?? [];
            $has_more = ! empty( $body['pagination']['has_more'] );

            foreach ( $items as $item ) {
                $customer = $item['customer'] ?? [];
                $email    = is_array( $customer ) ? ( $customer['email'] ?? '' ) : '';
                if ( $email ) {
                    $results[] = [ 'email' => $email ];
                }
            }

            $page++;
        } while ( $has_more && $page <= 50 );

        return $results;
    }

    /**
     * Fetch active purchases for a product (one-shot only, paginated).
     * Excludes purchases linked to a subscription (avoid double-counting).
     *
     * @param string $product_id SureCart product ID.
     * @return array [ ['email' => '...'], ... ]
     */
    private static function fetch_active_purchases( $product_id ) {
        $results  = [];
        $page     = 1;
        $limit    = 100;
        $token    = sc_api_token();

        do {
            $offset = ( $page - 1 ) * $limit;
            $url    = 'https://api.surecart.com/v1/purchases'
                . '?product_ids[]=' . urlencode( $product_id )
                . '&expand[]=customer'
                . '&limit=' . $limit
                . '&offset=' . $offset;

            $response = wp_remote_get( $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                ],
                'timeout' => 30,
            ] );

            if ( is_wp_error( $response ) ) {
                break;
            }

            $body     = json_decode( wp_remote_retrieve_body( $response ), true );
            $items    = $body['data'] ?? [];
            $has_more = ! empty( $body['pagination']['has_more'] );

            foreach ( $items as $item ) {
                // Skip purchases linked to a subscription
                if ( ! empty( $item['subscription'] ) ) {
                    continue;
                }

                // Only keep paid/active purchases
                $status = $item['status'] ?? '';
                if ( ! in_array( $status, [ 'paid', 'active', 'completed' ], true ) ) {
                    continue;
                }

                $customer = $item['customer'] ?? [];
                $email    = is_array( $customer ) ? ( $customer['email'] ?? '' ) : '';
                if ( $email ) {
                    $results[] = [ 'email' => $email ];
                }
            }

            $page++;
        } while ( $has_more && $page <= 50 );

        return $results;
    }

    /**
     * Process a single customer: resolve WP user, apply rules (or simulate).
     *
     * @param string $email    Customer email.
     * @param array  $rules    Array of rule objects for this product.
     * @param bool   $dry_run  Simulation mode.
     * @return array ['enrolled' => N, 'already' => N, 'errors' => N, 'error_email' => '']
     */
    private static function process_customer( $email, $rules, $dry_run ) {
        $result = [
            'enrolled'    => 0,
            'already'     => 0,
            'errors'      => 0,
            'error_email' => '',
        ];

        $user = get_user_by( 'email', $email );
        if ( ! $user ) {
            $result['errors']      = 1;
            $result['error_email'] = $email;
            return $result;
        }

        foreach ( $rules as $rule ) {
            if ( $dry_run ) {
                // Simulate: check what would happen
                $course_ids = JAM_Access_Rules::get_course_ids( $rule );
                foreach ( $course_ids as $course_id ) {
                    if ( JAM_Enrollment::is_enrolled( $user->ID, $course_id ) ) {
                        $result['already']++;
                    } else {
                        $result['enrolled']++;
                    }
                }
            } else {
                $sub_report = JAM_Enrollment::apply_rule( $user->ID, $rule, 'sync' );
                $result['enrolled'] += $sub_report['enrolled'];
                $result['already']  += $sub_report['already'];
                $result['errors']   += $sub_report['errors'];
            }
        }

        return $result;
    }
}
