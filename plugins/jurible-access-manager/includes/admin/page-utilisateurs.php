<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ─── Filters ───
$current_search = sanitize_text_field( $_GET['search'] ?? '' );
$current_course = absint( $_GET['course_id'] ?? 0 );
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

// Search filter: when searching, search ALL users (not just SureCart customers)
// When no search, default to SureCart customers only
if ( $current_search ) {
    $user_args['search']         = '*' . $current_search . '*';
    $user_args['search_columns'] = [ 'user_login', 'user_email', 'display_name' ];
} else {
    $user_args['meta_key']     = 'sc_customer_ids';
    $user_args['meta_compare'] = 'EXISTS';
}

// Course filter: get user IDs enrolled in this course first
$course_filter_user_ids = null;
if ( $current_course ) {
    $has_table = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $su_table ) ) === $su_table;
    if ( $has_table ) {
        $course_filter_user_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT user_id FROM {$su_table} WHERE space_id = %d",
            $current_course
        ) );
        if ( empty( $course_filter_user_ids ) ) {
            $course_filter_user_ids = [ 0 ]; // No users = show empty
        }
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

            <button type="submit" class="button">Filtrer</button>

            <?php if ( $current_search || $current_course ) : ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=jam-manual' ) ); ?>" class="button">Reinitialiser</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- ─── Stats ─── -->
    <p class="description" style="margin-bottom: 16px;">
        <?php echo number_format_i18n( $total_users ); ?> utilisateur<?php echo $total_users > 1 ? 's' : ''; ?>
        <?php if ( $current_search ) : ?>
            pour « <?php echo esc_html( $current_search ); ?> »
        <?php elseif ( ! $current_course ) : ?>
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
                            <th>Cours inscrits</th>
                            <th style="width: 80px; text-align: center;">Nb cours</th>
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
                            <td>
                                <?php if ( empty( $enrolled ) ) : ?>
                                    <span style="color: #646970;">Aucun cours</span>
                                <?php else : ?>
                                    <?php foreach ( $enrolled as $ec ) : ?>
                                        <span class="jam-badge jam-badge--green" style="margin: 1px 2px;"><?php echo esc_html( $ec['title'] ); ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ( $nb_cours > 0 ) : ?>
                                    <strong><?php echo $nb_cours; ?></strong> cours
                                <?php else : ?>
                                    <span style="color: #646970;">0</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <button type="button" class="button button-small jam-user-toggle" data-user-id="<?php echo esc_attr( $user->ID ); ?>">
                                    Details &#9662;
                                </button>
                            </td>
                        </tr>
                        <tr class="jam-user-detail" data-user-id="<?php echo esc_attr( $user->ID ); ?>" style="display: none;">
                            <td colspan="4">
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
