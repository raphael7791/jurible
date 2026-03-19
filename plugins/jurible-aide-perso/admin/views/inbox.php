<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$table = $wpdb->prefix . 'jurible_aide_requests';

// Filtres
$filter_type   = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : '';
$filter_status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';

$where  = '1=1';
$params = [];

if ( $filter_type ) {
    $where   .= ' AND r.type = %s';
    $params[] = $filter_type;
}
if ( $filter_status ) {
    $where   .= ' AND r.status = %s';
    $params[] = $filter_status;
}

$sql = "SELECT r.*, u.display_name, u.user_email as user_email_wp
        FROM $table r
        JOIN {$wpdb->users} u ON r.user_id = u.ID
        WHERE $where
        ORDER BY r.created_at DESC";

if ( ! empty( $params ) ) {
    $sql = $wpdb->prepare( $sql, $params );
}

$requests = $wpdb->get_results( $sql );

// Stats
$total      = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
$pending    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'pending'" );
$in_prog    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'in_progress'" );
$completed  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'completed'" );
?>
<div class="wrap jaide-wrap">
    <h1 class="jaide-page-title">Aide Personnalisée — Inbox</h1>

    <!-- Stats -->
    <div class="jaide-stats">
        <div class="jaide-stat">
            <span class="jaide-stat__number"><?php echo $total; ?></span>
            <span class="jaide-stat__label">Total</span>
        </div>
        <div class="jaide-stat jaide-stat--warning">
            <span class="jaide-stat__number"><?php echo $pending; ?></span>
            <span class="jaide-stat__label">En attente</span>
        </div>
        <div class="jaide-stat jaide-stat--info">
            <span class="jaide-stat__number"><?php echo $in_prog; ?></span>
            <span class="jaide-stat__label">En cours</span>
        </div>
        <div class="jaide-stat jaide-stat--success">
            <span class="jaide-stat__number"><?php echo $completed; ?></span>
            <span class="jaide-stat__label">Traités</span>
        </div>
    </div>

    <!-- Filtres -->
    <div class="jaide-filters">
        <form method="get" action="">
            <input type="hidden" name="page" value="jaide-inbox" />
            <select name="type">
                <option value="">Tous les types</option>
                <option value="question" <?php selected( $filter_type, 'question' ); ?>>Questions</option>
                <option value="copie" <?php selected( $filter_type, 'copie' ); ?>>Copies</option>
            </select>
            <select name="status">
                <option value="">Tous les statuts</option>
                <option value="pending" <?php selected( $filter_status, 'pending' ); ?>>En attente</option>
                <option value="in_progress" <?php selected( $filter_status, 'in_progress' ); ?>>En cours</option>
                <option value="completed" <?php selected( $filter_status, 'completed' ); ?>>Traités</option>
            </select>
            <button type="submit" class="button">Filtrer</button>
            <?php if ( $filter_type || $filter_status ) : ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=jaide-inbox' ) ); ?>" class="button">Réinitialiser</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Liste -->
    <div class="jaide-list">
        <?php if ( empty( $requests ) ) : ?>
            <div class="jaide-empty">
                <p>📭 Aucune demande pour l'instant.</p>
            </div>
        <?php else : ?>
            <?php foreach ( $requests as $r ) : ?>
                <div class="jaide-item jaide-item--<?php echo esc_attr( $r->status ); ?>">
                    <div class="jaide-item__avatar">
                        <?php echo get_avatar( $r->user_id, 40 ); ?>
                    </div>
                    <div class="jaide-item__info">
                        <div class="jaide-item__top">
                            <strong class="jaide-item__name"><?php echo esc_html( $r->nom ); ?></strong>
                            <span class="jaide-item__type jaide-item__type--<?php echo esc_attr( $r->type ); ?>">
                                <?php echo esc_html( ucfirst( $r->type ) ); ?>
                            </span>
                        </div>
                        <div class="jaide-item__meta">
                            <span><?php echo esc_html( $r->matiere ); ?></span>
                            <span>·</span>
                            <span><?php echo esc_html( $r->annee ); ?></span>
                            <span>·</span>
                            <span><?php echo esc_html( human_time_diff( strtotime( $r->created_at ), current_time( 'timestamp' ) ) ); ?></span>
                            <?php if ( $r->file_name ) : ?>
                                <span>·</span>
                                <span>📎</span>
                            <?php endif; ?>
                        </div>
                        <?php if ( $r->message ) : ?>
                            <p class="jaide-item__excerpt"><?php echo esc_html( wp_trim_words( $r->message, 15 ) ); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="jaide-item__status">
                        <span class="jaide-badge jaide-badge--<?php echo esc_attr( $r->status ); ?>">
                            <?php
                            $labels = [ 'pending' => 'En attente', 'in_progress' => 'En cours', 'completed' => 'Traité' ];
                            echo esc_html( $labels[ $r->status ] ?? $r->status );
                            ?>
                        </span>
                    </div>
                    <div class="jaide-item__actions">
                        <?php if ( $r->status === 'pending' ) : ?>
                            <button class="button jaide-btn-claim" data-id="<?php echo esc_attr( $r->id ); ?>">Prendre en charge</button>
                        <?php endif; ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=jaide-detail&id=' . $r->id ) ); ?>" class="button button-primary">
                            <?php echo $r->status === 'completed' ? 'Voir' : 'Répondre'; ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
