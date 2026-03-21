<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ─── Filters ───
$current_search = sanitize_text_field( $_GET['search'] ?? '' );
$current_course = absint( $_GET['course_id'] ?? 0 );
$sc_filter      = sanitize_text_field( $_GET['sc_filter'] ?? 'all_sc' );
$current_page   = max( 1, absint( $_GET['paged'] ?? 1 ) );
$per_page       = 20;

global $wpdb;

$su_table = $wpdb->prefix . 'fcom_space_user';
$s_table  = $wpdb->prefix . 'fcom_spaces';

// Get all Fluent Community courses for filter dropdown + badge display
$courses     = JAM_Helpers::get_fcom_courses();
$courses_map = [];
foreach ( $courses as $c ) {
    $courses_map[ $c['id'] ] = $c['title'];
}

// ─── Build user query ───
$user_args = [
    'number'       => $per_page,
    'paged'        => $current_page,
    'orderby'      => 'display_name',
    'order'        => 'ASC',
];

// Search filter
if ( $current_search ) {
    $user_args['search']         = '*' . $current_search . '*';
    $user_args['search_columns'] = [ 'user_login', 'user_email', 'display_name' ];
}

// SC / course enrollment filter
$has_su_table = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $su_table ) ) === $su_table;

if ( $sc_filter === 'sc_sans_cours' || $sc_filter === 'sc_avec_cours' ) {
    // Always restrict to SC customers for these filters
    $user_args['meta_key']     = 'sc_customer_ids';
    $user_args['meta_compare'] = 'EXISTS';

    if ( $has_su_table ) {
        $enrolled_uids = array_map( 'intval', $wpdb->get_col(
            "SELECT DISTINCT su.user_id FROM {$su_table} su
             INNER JOIN {$s_table} s ON su.space_id = s.id AND s.type = 'course'"
        ) );

        if ( $sc_filter === 'sc_sans_cours' ) {
            if ( ! empty( $enrolled_uids ) ) {
                $user_args['exclude'] = $enrolled_uids;
            }
        } else {
            $user_args['include'] = ! empty( $enrolled_uids ) ? $enrolled_uids : [ 0 ];
        }
    }
} elseif ( ! $current_search ) {
    // Default: SC customers only
    $user_args['meta_key']     = 'sc_customer_ids';
    $user_args['meta_compare'] = 'EXISTS';
}

// Course filter: restrict to users enrolled in a specific course
if ( $current_course && $has_su_table ) {
    $course_filter_user_ids = $wpdb->get_col( $wpdb->prepare(
        "SELECT user_id FROM {$su_table} WHERE space_id = %d",
        $current_course
    ) );
    if ( empty( $course_filter_user_ids ) ) {
        $course_filter_user_ids = [ 0 ];
    }
    // Intersect with existing include if set, otherwise just set it
    if ( ! empty( $user_args['include'] ) ) {
        $user_args['include'] = array_values( array_intersect( $user_args['include'], $course_filter_user_ids ) );
        if ( empty( $user_args['include'] ) ) {
            $user_args['include'] = [ 0 ];
        }
    } else {
        $user_args['include'] = $course_filter_user_ids;
    }
}

// Run query
$user_query  = new WP_User_Query( $user_args );
$users       = $user_query->get_results();
$total_users = $user_query->get_total();
$total_pages = ceil( $total_users / $per_page );

// Get enrolled course IDs for each user (batch query)
$user_courses = [];
if ( ! empty( $users ) ) {
    $has_table = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $su_table ) ) === $su_table;
    if ( $has_table ) {
        $user_ids_list = implode( ',', array_map( 'intval', wp_list_pluck( $users, 'ID' ) ) );
        $rows = $wpdb->get_results(
            "SELECT su.user_id, su.space_id, s.title
             FROM {$su_table} su
             INNER JOIN {$s_table} s ON su.space_id = s.id AND s.type = 'course'
             WHERE su.user_id IN ({$user_ids_list})
             ORDER BY s.title ASC"
        );
        foreach ( $rows as $row ) {
            $user_courses[ $row->user_id ][] = [
                'id'    => (int) $row->space_id,
                'title' => $row->title,
            ];
        }
    }
}

?>
<div class="wrap jam-wrap">

    <h1>Utilisateurs</h1>

    <!-- ─── Filters ─── -->
    <form method="get" class="jam-filters">
        <input type="hidden" name="page" value="jam-manual">

        <div class="jam-filters__row">
            <input type="text" name="search" value="<?php echo esc_attr( $current_search ); ?>"
                   placeholder="Rechercher par email, nom ou login..." style="min-width: 280px;">

            <?php if ( ! empty( $courses ) ) : ?>
            <select name="course_id">
                <option value="">Tous les cours</option>
                <?php foreach ( $courses as $c ) : ?>
                    <option value="<?php echo esc_attr( $c['id'] ); ?>" <?php selected( $current_course, $c['id'] ); ?>>
                        <?php echo esc_html( $c['title'] ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>

            <select name="sc_filter">
                <option value="all_sc" <?php selected( $sc_filter, 'all_sc' ); ?>>Clients SureCart</option>
                <option value="sc_avec_cours" <?php selected( $sc_filter, 'sc_avec_cours' ); ?>>SC avec cours FC</option>
                <option value="sc_sans_cours" <?php selected( $sc_filter, 'sc_sans_cours' ); ?>>SC sans cours FC</option>
            </select>

            <select id="jam-product-filter" style="display:none;">
                <option value="">Tous les produits</option>
            </select>

            <button type="submit" class="button">Filtrer</button>

            <?php if ( $current_search || $current_course || $sc_filter !== 'all_sc' ) : ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=jam-manual' ) ); ?>" class="button">Reinitialiser</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- ─── Stats ─── -->
    <p class="description" style="margin-bottom: 16px;">
        <?php echo number_format_i18n( $total_users ); ?> utilisateur<?php echo $total_users > 1 ? 's' : ''; ?>
        <?php if ( $current_search ) : ?>
            pour « <?php echo esc_html( $current_search ); ?> »
        <?php endif; ?>
        <?php if ( $sc_filter === 'sc_sans_cours' ) : ?>
            (clients SC sans cours FC)
        <?php elseif ( $sc_filter === 'sc_avec_cours' ) : ?>
            (clients SC avec cours FC)
        <?php elseif ( ! $current_search && ! $current_course ) : ?>
            (clients SureCart)
        <?php endif; ?>
        <?php if ( $current_course ) : ?>
            (cours filtre)
        <?php endif; ?>
    </p>

    <!-- ─── Users Table ─── -->
    <?php if ( empty( $users ) ) : ?>
        <div class="jam-section">
            <div class="jam-empty">
                <?php if ( $current_search || $current_course ) : ?>
                    Aucun utilisateur ne correspond a ces filtres.
                <?php else : ?>
                    Aucun client SureCart trouve. Verifiez que le meta <code>sc_customer_ids</code> est present sur vos utilisateurs.
                <?php endif; ?>
            </div>
        </div>
    <?php else : ?>
        <div class="jam-section">
            <div class="jam-section__body">
                <table class="jam-table jam-users-table">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th class="jam-sortable-products" style="cursor:pointer;" title="Cliquer pour trier">Produit SC <span class="jam-sort-arrow"></span></th>
                            <th>Cours inscrits</th>
                            <th class="jam-sortable-coherence" style="width:120px;text-align:center;cursor:pointer;" title="Cliquer pour trier">Coherence <span class="jam-sort-arrow"></span></th>
                            <th style="width: 100px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $users as $user ) :
                            $enrolled = $user_courses[ $user->ID ] ?? [];
                            $nb_cours = count( $enrolled );
                        ?>
                        <tr class="jam-user-row" data-user-id="<?php echo esc_attr( $user->ID ); ?>">
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <?php echo get_avatar( $user->ID, 32 ); ?>
                                    <div>
                                        <strong><?php echo esc_html( $user->display_name ); ?></strong>
                                        <br><span style="color: #646970; font-size: 12px;"><?php echo esc_html( $user->user_email ); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="jam-user-products" data-user-id="<?php echo esc_attr( $user->ID ); ?>">
                                <span class="spinner is-active" style="float:none;"></span>
                            </td>
                            <td>
                                <?php if ( empty( $enrolled ) ) : ?>
                                    <span style="color: #646970;">Aucun cours</span>
                                <?php else : ?>
                                    <?php foreach ( $enrolled as $ec ) : ?>
                                        <span class="jam-badge jam-badge--green" style="margin: 1px 2px;"><?php echo esc_html( $ec['title'] ); ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                            <td class="jam-user-coherence" data-user-id="<?php echo esc_attr( $user->ID ); ?>" style="text-align: center;">
                                <span class="spinner is-active" style="float:none;"></span>
                            </td>
                            <td style="text-align: center;">
                                <button type="button" class="button button-small jam-user-toggle" data-user-id="<?php echo esc_attr( $user->ID ); ?>">
                                    Details &#9662;
                                </button>
                            </td>
                        </tr>
                        <tr class="jam-user-detail" data-user-id="<?php echo esc_attr( $user->ID ); ?>" style="display: none;">
                            <td colspan="5">
                                <div class="jam-user-detail__inner">
                                    <div class="jam-loading">
                                        <span class="spinner"></span> Chargement...
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ─── Pagination ─── -->
        <?php if ( $total_pages > 1 ) : ?>
            <div class="jam-pagination">
                <?php
                $base_url = admin_url( 'admin.php?page=jam-manual' );
                $params   = [];
                if ( $current_search ) $params['search'] = $current_search;
                if ( $current_course ) $params['course_id'] = $current_course;
                if ( $sc_filter && $sc_filter !== 'all_sc' ) $params['sc_filter'] = $sc_filter;

                for ( $i = 1; $i <= min( $total_pages, 20 ); $i++ ) :
                    $params['paged'] = $i;
                    $url = add_query_arg( $params, $base_url );
                ?>
                    <?php if ( $i === $current_page ) : ?>
                        <span class="jam-pagination__current"><?php echo $i; ?></span>
                    <?php else : ?>
                        <a href="<?php echo esc_url( $url ); ?>" class="jam-pagination__link"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <span class="jam-pagination__info">
                    Page <?php echo $current_page; ?> / <?php echo $total_pages; ?>
                    (<?php echo number_format_i18n( $total_users ); ?> utilisateurs)
                </span>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>
