<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class JAM_Helpers {

    /**
     * Get the SureCart API token (works on all environments).
     *
     * Tries: 1) sc_api_token() global function
     *        2) SureCart\Models\ApiToken::get() (protected, called via __callStatic)
     *        3) Direct option decrypt
     *
     * @return string|false Token or false if unavailable.
     */
    public static function get_sc_api_token() {
        // 1. Global function (older SureCart versions or custom sites)
        if ( function_exists( 'sc_api_token' ) ) {
            return sc_api_token();
        }

        // 2. SureCart ApiToken model
        if ( class_exists( '\SureCart\Models\ApiToken' ) ) {
            try {
                $token = \SureCart\Models\ApiToken::get();
                if ( $token ) {
                    return $token;
                }
            } catch ( \Exception $e ) {
                // Fall through
            }
        }

        // 3. Direct decrypt from option
        $encrypted = get_option( 'sc_api_token', '' );
        if ( $encrypted && class_exists( '\SureCart\Support\Encryption' ) ) {
            try {
                $token = \SureCart\Support\Encryption::decrypt( $encrypted );
                if ( $token ) {
                    return $token;
                }
            } catch ( \Exception $e ) {
                // Fall through
            }
        }

        return false;
    }

    /**
     * Get SureCart products (cached 15 min).
     */
    public static function get_sc_products() {
        $cached = get_transient( 'jam_sc_products_v2' );
        if ( $cached !== false ) {
            return $cached;
        }

        $products = [];

        if ( ! self::get_sc_api_token() ) {
            if ( class_exists( '\SureCart\Models\Product' ) ) {
                try {
                    $result = \SureCart\Models\Product::where( [ 'archived' => false ] )->paginate( [
                        'per_page' => 100,
                    ] );

                    $items = $result->data ?? $result;
                    if ( is_array( $items ) || is_object( $items ) ) {
                        foreach ( $items as $p ) {
                            $p = (object) $p;
                            $products[] = [
                                'id'           => $p->id ?? '',
                                'name'         => $p->name ?? '',
                                'type'         => isset( $p->recurring ) && $p->recurring ? 'Abonnement' : 'One-shot',
                                'active'       => ! ( $p->archived ?? false ),
                                'prices'       => [],
                                'active_count' => 0,
                                'created_at'   => $p->created_at ?? '',
                            ];
                        }
                    }
                } catch ( \Exception $e ) {
                    // silent
                }
            }

            set_transient( 'jam_sc_products_v2', $products, 15 * MINUTE_IN_SECONDS );
            return $products;
        }

        // SureCart REST API — products with prices expanded
        $response = wp_remote_get( 'https://api.surecart.com/v1/products?archived=false&limit=100&expand[]=prices', [
            'headers' => [
                'Authorization' => 'Bearer ' . self::get_sc_api_token(),
                'Content-Type'  => 'application/json',
            ],
        ] );

        if ( ! is_wp_error( $response ) ) {
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( ! empty( $body['data'] ) ) {
                foreach ( $body['data'] as $p ) {
                    $prices_display = [];
                    $has_recurring  = false;

                    $price_list = $p['prices']['data'] ?? $p['prices'] ?? [];
                    if ( is_array( $price_list ) ) {
                        foreach ( $price_list as $price ) {
                            if ( ! empty( $price['archived'] ) ) {
                                continue;
                            }
                            $amount   = ( $price['amount'] ?? 0 ) / 100;
                            $currency = strtoupper( $price['currency'] ?? 'EUR' );
                            $label    = number_format( $amount, 2, ',', ' ' ) . ' ' . $currency;

                            $interval       = $price['recurring_interval'] ?? '';
                            $interval_count = $price['recurring_interval_count'] ?? 1;
                            if ( $interval ) {
                                $has_recurring = true;
                                if ( $interval === 'month' && $interval_count == 1 ) {
                                    $label .= '/mois';
                                } elseif ( $interval === 'month' ) {
                                    $label .= '/' . $interval_count . ' mois';
                                } elseif ( $interval === 'year' ) {
                                    $label .= '/an';
                                } elseif ( $interval === 'week' ) {
                                    $label .= '/sem.';
                                }
                            }

                            $prices_display[] = $label;
                        }
                    }

                    $products[] = [
                        'id'           => $p['id'] ?? '',
                        'name'         => $p['name'] ?? '',
                        'type'         => $has_recurring ? 'Abonnement' : 'One-shot',
                        'active'       => empty( $p['archived'] ),
                        'prices'       => $prices_display,
                        'active_count' => 0,
                        'created_at'   => $p['created_at'] ?? '',
                    ];
                }
            }
        }

        // Fetch active subscription counts per product
        $sub_counts = self::get_active_counts_per_product();
        foreach ( $products as &$product ) {
            $product['active_count'] = $sub_counts[ $product['id'] ] ?? 0;
        }
        unset( $product );

        set_transient( 'jam_sc_products_v2', $products, 15 * MINUTE_IN_SECONDS );
        return $products;
    }

    /**
     * Get active subscription counts per SureCart product.
     */
    public static function get_active_counts_per_product() {
        $token = self::get_sc_api_token();
        if ( ! $token ) {
            return [];
        }

        $counts = [];
        $page   = 1;
        $limit  = 100;

        do {
            $offset   = ( $page - 1 ) * $limit;
            $url      = "https://api.surecart.com/v1/subscriptions?status=active&limit={$limit}&offset={$offset}&expand[]=price&expand[]=price.product";
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
            $subs     = $body['data'] ?? [];
            $has_more = ! empty( $body['pagination']['has_more'] );

            foreach ( $subs as $sub ) {
                $price   = $sub['price'] ?? [];
                $product = $price['product'] ?? [];
                $pid     = is_array( $product ) ? ( $product['id'] ?? '' ) : $product;
                if ( $pid ) {
                    $counts[ $pid ] = ( $counts[ $pid ] ?? 0 ) + 1;
                }
            }

            $page++;
        } while ( $has_more && $page <= 20 );

        return $counts;
    }

    /**
     * Get Fluent Community courses (cached 15 min).
     */
    public static function get_fcom_courses() {
        $cached = get_transient( 'jam_fcom_courses' );
        if ( $cached !== false ) {
            return $cached;
        }

        $courses = [];

        if ( class_exists( '\FluentCommunity\Modules\Course\Model\Course' ) ) {
            try {
                $all = \FluentCommunity\Modules\Course\Model\Course::all();
                foreach ( $all as $course ) {
                    $enrolled_count = 0;
                    if ( method_exists( $course, 'students' ) ) {
                        $enrolled_count = $course->students()->count();
                    }
                    $courses[] = [
                        'id'             => $course->id,
                        'title'          => $course->title ?? $course->name ?? '(sans titre)',
                        'status'         => $course->status ?? 'draft',
                        'enrolled_count' => $enrolled_count,
                    ];
                }
            } catch ( \Exception $e ) {
                // silent
            }
        }

        // Fallback: query DB directly
        if ( empty( $courses ) ) {
            global $wpdb;
            $table = $wpdb->prefix . 'fcom_spaces';
            if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) === $table ) {
                $rows = $wpdb->get_results(
                    "SELECT s.id, s.title, s.status,
                        (SELECT COUNT(*) FROM {$wpdb->prefix}fcom_space_user su WHERE su.space_id = s.id) as enrolled_count
                    FROM {$table} s
                    WHERE s.type = 'course'
                    ORDER BY s.title ASC"
                );
                foreach ( $rows as $row ) {
                    $courses[] = [
                        'id'             => $row->id,
                        'title'          => $row->title,
                        'status'         => $row->status,
                        'enrolled_count' => (int) $row->enrolled_count,
                    ];
                }
            }
        }

        set_transient( 'jam_fcom_courses', $courses, 15 * MINUTE_IN_SECONDS );
        return $courses;
    }

    /**
     * Status badge HTML.
     */
    public static function status_badge( $status ) {
        $map = [
            'active'     => [ 'green', 'Actif' ],
            'trialing'   => [ 'blue', 'Essai' ],
            'past_due'   => [ 'orange', 'En retard' ],
            'canceled'   => [ 'red', 'Annulé' ],
            'completed'  => [ 'gray', 'Terminé' ],
            'unpaid'     => [ 'red', 'Impayé' ],
            'enrolled'   => [ 'green', 'Inscrit' ],
            'unenrolled' => [ 'red', 'Désinscrit' ],
            'published'  => [ 'green', 'Publié' ],
            'draft'      => [ 'orange', 'Brouillon' ],
        ];

        $info = $map[ $status ] ?? [ 'gray', ucfirst( $status ) ];
        return '<span class="jam-badge jam-badge--' . $info[0] . '">' . esc_html( $info[1] ) . '</span>';
    }

    /**
     * Group products by category name.
     */
    public static function group_products( $products ) {
        $groups = [];

        foreach ( $products as $product ) {
            $name = $product['name'];

            if ( preg_match( '/^Académie (L\d|Licence|Capacité)/i', $name, $m ) ) {
                $group = 'Académie ' . $m[1];
            } elseif ( preg_match( '/^Académie/i', $name ) ) {
                $group = 'Académie';
            } elseif ( preg_match( '/^(Fiches|Pack|Manuel)/i', $name ) ) {
                $group = 'PDF';
            } elseif ( preg_match( '/^Prépa/i', $name ) ) {
                $group = 'Prépa';
            } elseif ( preg_match( '/^(Crédits|Minos)/i', $name ) ) {
                $group = 'Crédits IA';
            } else {
                $group = 'Autres';
            }

            $groups[ $group ][] = $product;
        }

        ksort( $groups );
        return $groups;
    }
}

// Backward-compatible function aliases
function jam_dashboard_get_sc_products() {
    return JAM_Helpers::get_sc_products();
}

function jam_dashboard_get_fcom_courses() {
    return JAM_Helpers::get_fcom_courses();
}
