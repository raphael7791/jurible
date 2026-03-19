<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$search   = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$selected = isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : 0;

// Si on vient de sauvegarder, garder le user sélectionné
if ( ! $selected && isset( $_POST['user_id'] ) ) {
    $selected = intval( $_POST['user_id'] );
}

// Recherche d'utilisateurs
$users = [];
if ( $search ) {
    $users = get_users( [
        'search'  => '*' . $search . '*',
        'number'  => 20,
        'orderby' => 'display_name',
    ] );
}

// Infos crédits du user sélectionné
$credits = null;
$user    = null;
if ( $selected ) {
    $user    = get_userdata( $selected );
    $credits = $user ? Jaide_Access::get_credits_info( $selected ) : null;
}
?>
<div class="wrap jaide-wrap">
    <h1 class="jaide-page-title">Aide Personnalisée — Crédits étudiants</h1>

    <!-- Recherche -->
    <div class="jaide-settings-card">
        <h2 class="jaide-settings-card__title">Rechercher un étudiant</h2>
        <form method="get" action="">
            <input type="hidden" name="page" value="jaide-credits" />
            <div style="display:flex;gap:12px;align-items:center;">
                <input type="text" name="s" class="jaide-form-input" value="<?php echo esc_attr( $search ); ?>" placeholder="Nom, email ou login..." style="max-width:400px;" />
                <button type="submit" class="button button-primary">Rechercher</button>
            </div>
        </form>

        <?php if ( $search && ! empty( $users ) ) : ?>
            <div class="jaide-credits-results">
                <?php foreach ( $users as $u ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=jaide-credits&user_id=' . $u->ID . '&s=' . urlencode( $search ) ) ); ?>"
                       class="jaide-credits-user <?php echo $selected === $u->ID ? 'jaide-credits-user--active' : ''; ?>">
                        <?php echo get_avatar( $u->ID, 32 ); ?>
                        <div>
                            <strong><?php echo esc_html( $u->display_name ); ?></strong>
                            <span><?php echo esc_html( $u->user_email ); ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php elseif ( $search ) : ?>
            <p style="margin-top:12px;color:#6B7280;">Aucun utilisateur trouvé.</p>
        <?php endif; ?>
    </div>

    <!-- Détail crédits -->
    <?php if ( $user && $credits ) : ?>
        <div class="jaide-settings-card">
            <h2 class="jaide-settings-card__title">
                <?php echo get_avatar( $user->ID, 24 ); ?>
                <span style="margin-left:8px;"><?php echo esc_html( $user->display_name ); ?></span>
                <span style="font-weight:400;color:#6B7280;font-size:14px;margin-left:8px;"><?php echo esc_html( $user->user_email ); ?></span>
            </h2>

            <!-- Stats visuelles -->
            <div class="jaide-credits-stats">
                <!-- Copies -->
                <div class="jaide-credits-stat">
                    <h3>Corrections de copies</h3>
                    <?php if ( $credits['copies_limit'] === 0 ) : ?>
                        <p class="jaide-credits-stat__unlimited">Illimité</p>
                    <?php else : ?>
                        <div class="jaide-credits-bar">
                            <div class="jaide-credits-bar__fill jaide-credits-bar__fill--<?php echo $credits['copies_remaining'] <= 0 ? 'empty' : ( $credits['copies_remaining'] === 1 ? 'low' : 'ok' ); ?>"
                                 style="width:<?php echo min( 100, ( $credits['copies_used'] / max( 1, $credits['copies_limit'] ) ) * 100 ); ?>%"></div>
                        </div>
                        <p class="jaide-credits-stat__detail">
                            <strong><?php echo $credits['copies_used']; ?></strong> utilisée(s)
                            sur <strong><?php echo $credits['copies_limit']; ?></strong>
                            — <strong><?php echo $credits['copies_remaining']; ?> restante(s)</strong>
                            <?php if ( $credits['copies_has_override'] ) : ?>
                                <span style="color:#7C3AED;">(personnalisé)</span>
                            <?php else : ?>
                                <span style="color:#9CA3AF;">(limite globale)</span>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Questions -->
                <div class="jaide-credits-stat">
                    <h3>Questions</h3>
                    <?php if ( $credits['questions_limit'] === 0 ) : ?>
                        <p class="jaide-credits-stat__unlimited">Illimité</p>
                    <?php else : ?>
                        <div class="jaide-credits-bar">
                            <div class="jaide-credits-bar__fill jaide-credits-bar__fill--<?php echo $credits['questions_remaining'] <= 0 ? 'empty' : ( $credits['questions_remaining'] === 1 ? 'low' : 'ok' ); ?>"
                                 style="width:<?php echo min( 100, ( $credits['questions_used'] / max( 1, $credits['questions_limit'] ) ) * 100 ); ?>%"></div>
                        </div>
                        <p class="jaide-credits-stat__detail">
                            <strong><?php echo $credits['questions_used']; ?></strong> utilisée(s)
                            sur <strong><?php echo $credits['questions_limit']; ?></strong>
                            — <strong><?php echo $credits['questions_remaining']; ?> restante(s)</strong>
                            <?php if ( $credits['questions_has_override'] ) : ?>
                                <span style="color:#7C3AED;">(personnalisé)</span>
                            <?php else : ?>
                                <span style="color:#9CA3AF;">(limite globale)</span>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Formulaire ajustement -->
            <form method="post" action="" style="margin-top:24px;">
                <?php wp_nonce_field( 'jaide_save_credits' ); ?>
                <input type="hidden" name="user_id" value="<?php echo esc_attr( $user->ID ); ?>" />

                <h3 style="font-size:15px;margin:0 0 8px;color:#374151;">Crédits de cet étudiant</h3>
                <p style="font-size:13px;color:#6B7280;margin:0 0 16px;">
                    Laissez vide pour utiliser la limite globale (Paramètres : <?php echo $credits['copies_limit_global']; ?> copies, <?php echo $credits['questions_limit_global']; ?> questions).
                    Saisissez un nombre pour remplacer la limite globale pour cet étudiant. 0 = illimité.
                </p>

                <div class="jaide-form-row">
                    <div class="jaide-form-field">
                        <label class="jaide-form-label">Crédits copies</label>
                        <input type="number" name="copies_limit" class="jaide-form-input jaide-form-input--sm"
                               value="<?php echo $credits['copies_has_override'] ? esc_attr( $credits['copies_limit'] ) : ''; ?>"
                               placeholder="<?php echo esc_attr( $credits['copies_limit_global'] ); ?>" min="0" />
                    </div>
                    <div class="jaide-form-field">
                        <label class="jaide-form-label">Crédits questions</label>
                        <input type="number" name="questions_limit" class="jaide-form-input jaide-form-input--sm"
                               value="<?php echo $credits['questions_has_override'] ? esc_attr( $credits['questions_limit'] ) : ''; ?>"
                               placeholder="<?php echo esc_attr( $credits['questions_limit_global'] ); ?>" min="0" />
                    </div>
                </div>

                <button type="submit" name="jaide_save_credits" class="button button-primary">
                    Enregistrer
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>
