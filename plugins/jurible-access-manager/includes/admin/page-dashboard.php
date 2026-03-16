<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ─── Fetch data ───
$sc_products    = jam_dashboard_get_sc_products();
$fcom_courses   = jam_dashboard_get_fcom_courses();
$rules_count    = JAM_Access_Rules::count();
$log_count      = JAM_Access_Log::count();

// Pagination for subscriptions
$sub_page   = max( 1, absint( $_GET['sub_page'] ?? 1 ) );
$sub_filter = sanitize_text_field( $_GET['sub_product'] ?? '' );
$sub_status = sanitize_text_field( $_GET['sub_status'] ?? '' );

// Pagination for enrollments
$enr_page = max( 1, absint( $_GET['enr_page'] ?? 1 ) );
$per_page = 20;

?>
<div class="jam-wrap">
    <h1>Access Manager — Dashboard</h1>

    <!-- Stat Cards -->
    <div class="jam-stats">
        <div class="jam-stat-card">
            <div class="jam-stat-card__number"><?php echo count( $sc_products ); ?></div>
            <div class="jam-stat-card__label">Produits SureCart</div>
        </div>
        <div class="jam-stat-card">
            <div class="jam-stat-card__number"><?php echo count( $fcom_courses ); ?></div>
            <div class="jam-stat-card__label">Cours Fluent Community</div>
        </div>
        <div class="jam-stat-card">
            <div class="jam-stat-card__number"><?php echo $rules_count; ?></div>
            <div class="jam-stat-card__label">Règles d'accès</div>
        </div>
        <div class="jam-stat-card">
            <div class="jam-stat-card__number"><?php echo $log_count; ?></div>
            <div class="jam-stat-card__label">Actions loguées</div>
        </div>
    </div>

    <!-- Section 1: SureCart Products -->
    <div class="jam-section">
        <div class="jam-section__header">
            <h2>Produits SureCart</h2>
        </div>
        <div class="jam-section__body">
            <?php if ( empty( $sc_products ) ) : ?>
                <div class="jam-empty">Aucun produit trouvé. Vérifiez que SureCart est actif.</div>
            <?php else : ?>
                <table class="jam-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $sc_products as $product ) : ?>
                            <tr>
                                <td><strong><?php echo esc_html( $product['name'] ); ?></strong></td>
                                <td><code><?php echo esc_html( $product['id'] ); ?></code></td>
                                <td><?php echo esc_html( $product['type'] ); ?></td>
                                <td>
                                    <?php if ( $product['active'] ) : ?>
                                        <span class="jam-badge jam-badge--green">Actif</span>
                                    <?php else : ?>
                                        <span class="jam-badge jam-badge--gray">Inactif</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Section 2: Fluent Community Courses -->
    <div class="jam-section">
        <div class="jam-section__header">
            <h2>Cours Fluent Community</h2>
        </div>
        <div class="jam-section__body">
            <?php if ( empty( $fcom_courses ) ) : ?>
                <div class="jam-empty">Aucun cours trouvé. Vérifiez que Fluent Community Pro est actif.</div>
            <?php else : ?>
                <table class="jam-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>ID</th>
                            <th>Inscrits</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $fcom_courses as $course ) : ?>
                            <tr>
                                <td><strong><?php echo esc_html( $course['title'] ); ?></strong></td>
                                <td><code><?php echo esc_html( $course['id'] ); ?></code></td>
                                <td><?php echo intval( $course['enrolled_count'] ); ?></td>
                                <td>
                                    <?php if ( $course['status'] === 'published' ) : ?>
                                        <span class="jam-badge jam-badge--green">Publié</span>
                                    <?php else : ?>
                                        <span class="jam-badge jam-badge--orange">Brouillon</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Section 3: Active Subscriptions -->
    <div class="jam-section">
        <div class="jam-section__header">
            <h2>Abonnements actifs SureCart</h2>
        </div>
        <?php
        $subscriptions = jam_dashboard_get_sc_subscriptions( $sub_page, $per_page, $sub_filter, $sub_status );
        ?>
        <div class="jam-filters">
            <form method="get">
                <input type="hidden" name="page" value="jam-dashboard">
                <select name="sub_product">
                    <option value="">Tous les produits</option>
                    <?php foreach ( $sc_products as $p ) : ?>
                        <option value="<?php echo esc_attr( $p['id'] ); ?>" <?php selected( $sub_filter, $p['id'] ); ?>>
                            <?php echo esc_html( $p['name'] ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="sub_status">
                    <option value="">Tous les statuts</option>
                    <option value="active" <?php selected( $sub_status, 'active' ); ?>>Actif</option>
                    <option value="canceled" <?php selected( $sub_status, 'canceled' ); ?>>Annulé</option>
                    <option value="past_due" <?php selected( $sub_status, 'past_due' ); ?>>En retard</option>
                </select>
                <button type="submit" class="button">Filtrer</button>
            </form>
        </div>
        <div class="jam-section__body">
            <?php if ( empty( $subscriptions['data'] ) ) : ?>
                <div class="jam-empty">Aucun abonnement trouvé.</div>
            <?php else : ?>
                <table class="jam-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Produit</th>
                            <th>Prix</th>
                            <th>Statut</th>
                            <th>Depuis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $subscriptions['data'] as $sub ) : ?>
                            <tr>
                                <td><?php echo esc_html( $sub['email'] ); ?></td>
                                <td><?php echo esc_html( $sub['product_name'] ); ?></td>
                                <td><?php echo esc_html( $sub['price_display'] ); ?></td>
                                <td>
                                    <?php echo jam_status_badge( $sub['status'] ); ?>
                                </td>
                                <td><?php echo esc_html( $sub['created_at'] ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ( $subscriptions['total_pages'] > 1 ) : ?>
                    <div class="jam-pagination">
                        <span>Page <?php echo $sub_page; ?> sur <?php echo $subscriptions['total_pages']; ?></span>
                        <div class="jam-pagination__links">
                            <?php
                            $base_url = admin_url( 'admin.php?page=jam-dashboard' );
                            if ( $sub_filter ) {
                                $base_url .= '&sub_product=' . urlencode( $sub_filter );
                            }
                            if ( $sub_status ) {
                                $base_url .= '&sub_status=' . urlencode( $sub_status );
                            }

                            if ( $sub_page > 1 ) {
                                echo '<a href="' . esc_url( $base_url . '&sub_page=' . ( $sub_page - 1 ) ) . '">&laquo; Préc.</a>';
                            }
                            if ( $sub_page < $subscriptions['total_pages'] ) {
                                echo '<a href="' . esc_url( $base_url . '&sub_page=' . ( $sub_page + 1 ) ) . '">Suiv. &raquo;</a>';
                            }
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Section 4: Fluent Community Enrollments -->
    <div class="jam-section">
        <div class="jam-section__header">
            <h2>Inscriptions Fluent Community</h2>
        </div>
        <div class="jam-section__body">
            <?php
            $enrollments = jam_dashboard_get_fcom_enrollments( $enr_page, $per_page );
            ?>
            <?php if ( empty( $enrollments['data'] ) ) : ?>
                <div class="jam-empty">Aucune inscription trouvée.</div>
            <?php else : ?>
                <table class="jam-table">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Cours inscrits</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $enrollments['data'] as $enr ) : ?>
                            <tr>
                                <td><?php echo esc_html( $enr['display_name'] ); ?></td>
                                <td><?php echo esc_html( $enr['email'] ); ?></td>
                                <td><?php echo esc_html( $enr['courses'] ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ( $enrollments['total_pages'] > 1 ) : ?>
                    <div class="jam-pagination">
                        <span>Page <?php echo $enr_page; ?> sur <?php echo $enrollments['total_pages']; ?></span>
                        <div class="jam-pagination__links">
                            <?php
                            $base_url = admin_url( 'admin.php?page=jam-dashboard' );
                            if ( $enr_page > 1 ) {
                                echo '<a href="' . esc_url( $base_url . '&enr_page=' . ( $enr_page - 1 ) ) . '">&laquo; Préc.</a>';
                            }
                            if ( $enr_page < $enrollments['total_pages'] ) {
                                echo '<a href="' . esc_url( $base_url . '&enr_page=' . ( $enr_page + 1 ) ) . '">Suiv. &raquo;</a>';
                            }
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sync Button -->
    <div class="jam-section">
        <div class="jam-section__header">
            <h2>Synchronisation</h2>
        </div>
        <div class="jam-section__body jam-section__body--padded">
            <p>Synchronise les achats SureCart actifs avec les inscriptions Fluent Community selon les règles définies.</p>
            <button type="button" class="button button-primary" id="jam-sync-btn">Lancer la synchronisation</button>
            <div id="jam-sync-report"></div>
        </div>
    </div>
</div>

<?php

// ─── Helper Functions ───

function jam_status_badge( $status ) {
    $map = [
        'active'        => [ 'green', 'Actif' ],
        'trialing'      => [ 'blue', 'Essai' ],
        'past_due'      => [ 'orange', 'En retard' ],
        'canceled'      => [ 'red', 'Annulé' ],
        'completed'     => [ 'gray', 'Terminé' ],
        'unpaid'        => [ 'red', 'Impayé' ],
        'enrolled'      => [ 'green', 'Inscrit' ],
        'unenrolled'    => [ 'red', 'Désinscrit' ],
        'published'     => [ 'green', 'Publié' ],
        'draft'         => [ 'orange', 'Brouillon' ],
    ];

    $info  = $map[ $status ] ?? [ 'gray', ucfirst( $status ) ];
    return '<span class="jam-badge jam-badge--' . $info[0] . '">' . esc_html( $info[1] ) . '</span>';
}

function jam_dashboard_get_sc_products() {
    $cached = get_transient( 'jam_sc_products' );
    if ( $cached !== false ) {
        return $cached;
    }

    $products = [];

    // Try SureCart PHP models first
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
                        'id'     => $p->id ?? '',
                        'name'   => $p->name ?? '',
                        'type'   => isset( $p->recurring ) && $p->recurring ? 'Abonnement' : 'One-shot',
                        'active' => ! ( $p->archived ?? false ),
                    ];
                }
            }
        } catch ( \Exception $e ) {
            // Fallback below
        }
    }

    // Fallback: SureCart REST API via WP remote
    if ( empty( $products ) && function_exists( 'sc_api_token' ) ) {
        $response = wp_remote_get( 'https://api.surecart.com/v1/products?archived=false&limit=100', [
            'headers' => [
                'Authorization' => 'Bearer ' . sc_api_token(),
                'Content-Type'  => 'application/json',
            ],
        ] );

        if ( ! is_wp_error( $response ) ) {
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( ! empty( $body['data'] ) ) {
                foreach ( $body['data'] as $p ) {
                    $products[] = [
                        'id'     => $p['id'] ?? '',
                        'name'   => $p['name'] ?? '',
                        'type'   => ! empty( $p['recurring'] ) ? 'Abonnement' : 'One-shot',
                        'active' => empty( $p['archived'] ),
                    ];
                }
            }
        }
    }

    set_transient( 'jam_sc_products', $products, 15 * MINUTE_IN_SECONDS );
    return $products;
}

function jam_dashboard_get_fcom_courses() {
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

function jam_dashboard_get_sc_subscriptions( $page = 1, $per_page = 20, $product_filter = '', $status_filter = '' ) {
    $result = [
        'data'        => [],
        'total_pages' => 1,
    ];

    $args = [
        'limit'    => $per_page,
        'offset'   => ( $page - 1 ) * $per_page,
        'expand[]' => 'customer,price,price.product',
    ];

    if ( $status_filter ) {
        $args['status'] = $status_filter;
    }
    if ( $product_filter ) {
        $args['product_ids[]'] = $product_filter;
    }

    if ( ! function_exists( 'sc_api_token' ) ) {
        return $result;
    }

    $url      = 'https://api.surecart.com/v1/subscriptions?' . http_build_query( $args );
    $response = wp_remote_get( $url, [
        'headers' => [
            'Authorization' => 'Bearer ' . sc_api_token(),
            'Content-Type'  => 'application/json',
        ],
    ] );

    if ( is_wp_error( $response ) ) {
        return $result;
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( ! empty( $body['data'] ) ) {
        foreach ( $body['data'] as $sub ) {
            $customer = $sub['customer'] ?? [];
            $price    = $sub['price'] ?? [];
            $product  = $price['product'] ?? [];

            $amount      = ( $price['amount'] ?? 0 ) / 100;
            $currency    = strtoupper( $price['currency'] ?? 'EUR' );
            $interval    = $price['recurring_interval'] ?? '';
            $period_text = '';
            if ( $interval === 'month' ) {
                $period_text = '/mois';
            } elseif ( $interval === 'year' ) {
                $period_text = '/an';
            } elseif ( $interval === 'week' ) {
                $period_text = '/sem.';
            }

            $result['data'][] = [
                'email'         => $customer['email'] ?? '—',
                'product_name'  => $product['name'] ?? '—',
                'price_display' => number_format( $amount, 2, ',', ' ' ) . ' ' . $currency . $period_text,
                'status'        => $sub['status'] ?? 'unknown',
                'created_at'    => ! empty( $sub['created_at'] )
                    ? wp_date( 'd/m/Y', strtotime( $sub['created_at'] ) )
                    : '—',
            ];
        }
    }

    $total = $body['pagination']['count'] ?? count( $result['data'] );
    $result['total_pages'] = max( 1, ceil( $total / $per_page ) );

    return $result;
}

function jam_dashboard_get_fcom_enrollments( $page = 1, $per_page = 20 ) {
    global $wpdb;

    $result = [
        'data'        => [],
        'total_pages' => 1,
    ];

    $su_table = $wpdb->prefix . 'fcom_space_user';
    $s_table  = $wpdb->prefix . 'fcom_spaces';

    // Check if table exists
    if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $su_table ) ) !== $su_table ) {
        return $result;
    }

    $offset = ( $page - 1 ) * $per_page;

    // Get distinct users with enrollments
    $total = (int) $wpdb->get_var(
        "SELECT COUNT(DISTINCT su.user_id)
         FROM {$su_table} su
         INNER JOIN {$s_table} s ON su.space_id = s.id AND s.type = 'course'"
    );

    $result['total_pages'] = max( 1, ceil( $total / $per_page ) );

    $user_ids = $wpdb->get_col( $wpdb->prepare(
        "SELECT DISTINCT su.user_id
         FROM {$su_table} su
         INNER JOIN {$s_table} s ON su.space_id = s.id AND s.type = 'course'
         ORDER BY su.user_id DESC
         LIMIT %d OFFSET %d",
        $per_page,
        $offset
    ) );

    if ( empty( $user_ids ) ) {
        return $result;
    }

    foreach ( $user_ids as $uid ) {
        $user = get_userdata( $uid );
        if ( ! $user ) {
            continue;
        }

        $placeholders = implode( ',', array_fill( 0, 1, '%d' ) );
        $courses = $wpdb->get_col( $wpdb->prepare(
            "SELECT s.title
             FROM {$su_table} su
             INNER JOIN {$s_table} s ON su.space_id = s.id AND s.type = 'course'
             WHERE su.user_id = %d
             ORDER BY s.title ASC",
            $uid
        ) );

        $result['data'][] = [
            'display_name' => $user->display_name,
            'email'        => $user->user_email,
            'courses'      => implode( ', ', $courses ),
        ];
    }

    return $result;
}
