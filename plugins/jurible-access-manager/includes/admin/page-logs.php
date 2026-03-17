<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ─── Filters ───
$current_action  = sanitize_text_field( $_GET['log_action'] ?? '' );
$current_source  = sanitize_text_field( $_GET['source'] ?? '' );
$current_search  = sanitize_text_field( $_GET['search'] ?? '' );
$current_course  = absint( $_GET['course_id'] ?? 0 );
$current_page    = max( 1, absint( $_GET['paged'] ?? 1 ) );
$per_page        = 25;

$result = JAM_Access_Log::get_paginated( [
    'per_page'  => $per_page,
    'page'      => $current_page,
    'action'    => $current_action,
    'source'    => $current_source,
    'search'    => $current_search,
    'course_id' => $current_course,
] );

$logs       = $result['items'];
$total      = $result['total'];
$total_pages = ceil( $total / $per_page );

// Get courses for filter dropdown
$courses = JAM_Helpers::get_fcom_courses();
$courses_map = [];
foreach ( $courses as $c ) {
    $courses_map[ $c['id'] ] = $c['title'];
}

// ─── Hooks status ───
$hooks_enabled = JAM_SureCart_Hooks::is_enabled();

// Handle hooks toggle
if ( isset( $_POST['jam_toggle_hooks'] ) && check_admin_referer( 'jam_toggle_hooks' ) ) {
    JAM_SureCart_Hooks::set_enabled( ! $hooks_enabled );
    $hooks_enabled = ! $hooks_enabled;
    echo '<div class="notice notice-success is-dismissible"><p>Hooks ' . ( $hooks_enabled ? 'activés' : 'désactivés' ) . '.</p></div>';
}

// Handle debug toggle
$debug_enabled = (bool) get_option( 'jam_debug_enabled', false );
if ( isset( $_POST['jam_toggle_debug'] ) && check_admin_referer( 'jam_toggle_debug' ) ) {
    $debug_enabled = ! $debug_enabled;
    update_option( 'jam_debug_enabled', $debug_enabled );
    echo '<div class="notice notice-success is-dismissible"><p>Debug ' . ( $debug_enabled ? 'activé' : 'désactivé' ) . '.</p></div>';
}

// Handle clear debug logs
if ( isset( $_POST['jam_clear_debug'] ) && check_admin_referer( 'jam_clear_debug' ) ) {
    delete_option( 'jam_debug_logs' );
    echo '<div class="notice notice-success is-dismissible"><p>Logs de debug effacés.</p></div>';
}

?>
<div class="wrap jam-wrap">

    <h1>Historique des accès</h1>

    <!-- ─── Hooks ON/OFF Toggle ─── -->
    <div class="jam-section">
        <h2>Configuration des hooks SureCart</h2>
        <div class="jam-hooks-status" style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
            <div>
                <strong>Hooks automatiques :</strong>
                <?php if ( $hooks_enabled ) : ?>
                    <span class="jam-badge jam-badge--green">ACTIVÉS</span>
                <?php else : ?>
                    <span class="jam-badge jam-badge--red">DÉSACTIVÉS</span>
                <?php endif; ?>
            </div>
            <form method="post" style="display: inline;">
                <?php wp_nonce_field( 'jam_toggle_hooks' ); ?>
                <button type="submit" name="jam_toggle_hooks" class="button <?php echo $hooks_enabled ? 'button-secondary' : 'button-primary'; ?>">
                    <?php echo $hooks_enabled ? 'Désactiver les hooks' : 'Activer les hooks'; ?>
                </button>
            </form>
            <div>
                <strong>Mode debug :</strong>
                <?php if ( $debug_enabled ) : ?>
                    <span class="jam-badge jam-badge--blue">ACTIVÉ</span>
                <?php else : ?>
                    <span class="jam-badge jam-badge--gray">DÉSACTIVÉ</span>
                <?php endif; ?>
            </div>
            <form method="post" style="display: inline;">
                <?php wp_nonce_field( 'jam_toggle_debug' ); ?>
                <button type="submit" name="jam_toggle_debug" class="button button-secondary">
                    <?php echo $debug_enabled ? 'Désactiver debug' : 'Activer debug'; ?>
                </button>
            </form>
        </div>

        <?php if ( $hooks_enabled ) : ?>
            <p class="description" style="margin-top: 10px;">
                Les hooks écoutent <code>surecart/checkout_confirmed</code> et <code>surecart/purchase_revoked</code>.
                Chaque achat confirmé inscrit automatiquement l'utilisateur aux cours définis dans les règles d'accès.
            </p>
        <?php else : ?>
            <p class="description" style="margin-top: 10px;">
                Les hooks sont désactivés. Les achats SureCart ne déclencheront pas d'inscription automatique.
                Activez-les une fois que vos règles d'accès sont configurées et testées.
            </p>
        <?php endif; ?>
    </div>

    <?php
    // ─── Debug Logs ───
    if ( $debug_enabled ) :
        $debug_logs = get_option( 'jam_debug_logs', [] );
    ?>
    <div class="jam-section">
        <h2>
            Logs de debug
            <form method="post" style="display: inline; margin-left: 10px;">
                <?php wp_nonce_field( 'jam_clear_debug' ); ?>
                <button type="submit" name="jam_clear_debug" class="button button-small">Vider</button>
            </form>
        </h2>
        <?php if ( empty( $debug_logs ) ) : ?>
            <p class="description">Aucun log de debug. Les événements SureCart apparaîtront ici.</p>
        <?php else : ?>
            <div style="max-height: 300px; overflow-y: auto; background: #1e1e1e; color: #d4d4d4; padding: 12px; border-radius: 4px; font-family: monospace; font-size: 12px; line-height: 1.6;">
                <?php foreach ( array_reverse( $debug_logs ) as $log ) : ?>
                    <div style="margin-bottom: 4px; border-bottom: 1px solid #333; padding-bottom: 4px;">
                        <span style="color: #888;"><?php echo esc_html( $log['time'] ); ?></span>
                        <span style="color: #569cd6; font-weight: bold;"><?php echo esc_html( $log['event'] ); ?></span>
                        <span style="color: #9cdcfe;"><?php echo esc_html( wp_json_encode( $log['data'], JSON_UNESCAPED_UNICODE ) ); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ─── Filters ─── -->
    <div class="jam-section">
        <h2>Historique (<?php echo number_format_i18n( $total ); ?> entrées)</h2>

        <form method="get" class="jam-filters">
            <input type="hidden" name="page" value="jam-logs">

            <div class="jam-filters__row">
                <input type="text" name="search" value="<?php echo esc_attr( $current_search ); ?>"
                       placeholder="Rechercher par email..." class="regular-text">

                <select name="log_action">
                    <option value="">Toutes les actions</option>
                    <option value="enrolled" <?php selected( $current_action, 'enrolled' ); ?>>Inscrit</option>
                    <option value="unenrolled" <?php selected( $current_action, 'unenrolled' ); ?>>Désinscrit</option>
                    <option value="credits_added" <?php selected( $current_action, 'credits_added' ); ?>>Crédits ajoutés</option>
                </select>

                <select name="source">
                    <option value="">Toutes les sources</option>
                    <option value="surecart" <?php selected( $current_source, 'surecart' ); ?>>SureCart</option>
                    <option value="manual" <?php selected( $current_source, 'manual' ); ?>>Manuel</option>
                    <option value="sync" <?php selected( $current_source, 'sync' ); ?>>Synchronisation</option>
                </select>

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

                <?php if ( $current_action || $current_source || $current_search || $current_course ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=jam-logs' ) ); ?>" class="button">Réinitialiser</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ─── Logs Table ─── -->
    <?php if ( empty( $logs ) ) : ?>
        <div class="jam-section">
            <p>Aucune entrée dans l'historique<?php echo ( $current_action || $current_source || $current_search ) ? ' pour ces filtres' : ''; ?>.</p>
        </div>
    <?php else : ?>
        <table class="widefat striped jam-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Utilisateur</th>
                    <th>Cours</th>
                    <th>Action</th>
                    <th>Source</th>
                    <th>Détails</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $logs as $log ) :
                    $course_name = $courses_map[ $log->fcom_course_id ] ?? '#' . $log->fcom_course_id;
                    $details     = json_decode( $log->details ?? '{}', true );
                ?>
                <tr>
                    <td style="white-space: nowrap;">
                        <?php echo esc_html( wp_date( 'd/m/Y H:i', strtotime( $log->created_at ) ) ); ?>
                    </td>
                    <td>
                        <?php if ( $log->user_id ) : ?>
                            <a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . $log->user_id ) ); ?>">
                                <?php echo esc_html( $log->user_email ?: '#' . $log->user_id ); ?>
                            </a>
                        <?php else : ?>
                            <?php echo esc_html( $log->user_email ?: '—' ); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ( $log->fcom_course_id ) : ?>
                            <?php echo esc_html( $course_name ); ?>
                        <?php else : ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $action_map = [
                            'enrolled'      => [ 'green', 'Inscrit' ],
                            'unenrolled'    => [ 'red', 'Désinscrit' ],
                            'credits_added' => [ 'blue', 'Crédits' ],
                        ];
                        $a = $action_map[ $log->action ] ?? [ 'gray', ucfirst( $log->action ) ];
                        ?>
                        <span class="jam-badge jam-badge--<?php echo $a[0]; ?>"><?php echo esc_html( $a[1] ); ?></span>
                    </td>
                    <td>
                        <?php
                        $source_map = [
                            'surecart' => [ 'blue', 'SureCart' ],
                            'manual'   => [ 'orange', 'Manuel' ],
                            'sync'     => [ 'gray', 'Sync' ],
                        ];
                        $s = $source_map[ $log->source ] ?? [ 'gray', ucfirst( $log->source ) ];
                        ?>
                        <span class="jam-badge jam-badge--<?php echo $s[0]; ?>"><?php echo esc_html( $s[1] ); ?></span>
                    </td>
                    <td>
                        <?php if ( $log->sc_purchase_id ) : ?>
                            <small>Purchase: <?php echo esc_html( substr( $log->sc_purchase_id, 0, 12 ) ); ?>…</small>
                        <?php endif; ?>
                        <?php if ( ! empty( $details['credits_added'] ) ) : ?>
                            <small>+<?php echo (int) $details['credits_added']; ?> crédits (total: <?php echo (int) ( $details['new_total'] ?? '?' ); ?>)</small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- ─── Pagination ─── -->
        <?php if ( $total_pages > 1 ) : ?>
            <div class="jam-pagination">
                <?php
                $base_url = admin_url( 'admin.php?page=jam-logs' );
                $params   = [];
                if ( $current_action ) $params['log_action'] = $current_action;
                if ( $current_source ) $params['source'] = $current_source;
                if ( $current_search ) $params['search'] = $current_search;
                if ( $current_course ) $params['course_id'] = $current_course;

                for ( $i = 1; $i <= $total_pages; $i++ ) :
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
                    (<?php echo number_format_i18n( $total ); ?> entrées)
                </span>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>
