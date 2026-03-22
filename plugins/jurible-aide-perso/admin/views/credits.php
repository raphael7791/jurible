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
    <?php if ( $user && $credits ) :
        $has_access = get_user_meta( $user->ID, 'jam_aide_perso_access', true ) == '1';
    ?>
        <div class="jaide-settings-card">
            <h2 class="jaide-settings-card__title" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                <span style="display:flex;align-items:center;">
                    <?php echo get_avatar( $user->ID, 24 ); ?>
                    <span style="margin-left:8px;"><?php echo esc_html( $user->display_name ); ?></span>
                    <span style="font-weight:400;color:#6B7280;font-size:14px;margin-left:8px;"><?php echo esc_html( $user->user_email ); ?></span>
                </span>
                <form method="post" action="" style="margin:0;">
                    <?php wp_nonce_field( 'jaide_toggle_access' ); ?>
                    <input type="hidden" name="user_id" value="<?php echo esc_attr( $user->ID ); ?>" />
                    <?php if ( $has_access ) : ?>
                        <button type="submit" name="jaide_toggle_access" value="revoke" class="button" style="background:#FEE2E2;border-color:#EF4444;color:#991B1B;font-size:12px;"
                                onclick="return confirm('Retirer l\'accès aide perso à <?php echo esc_attr( $user->display_name ); ?> ?');">
                            Retirer l'accès
                        </button>
                    <?php else : ?>
                        <button type="submit" name="jaide_toggle_access" value="grant" class="button" style="background:#D1FAE5;border-color:#10B981;color:#065F46;font-size:12px;"
                                onclick="return confirm('Donner l\'accès aide perso (5Q / 1C) à <?php echo esc_attr( $user->display_name ); ?> ?');">
                            Donner l'accès
                        </button>
                    <?php endif; ?>
                </form>
            </h2>

            <?php if ( ! $has_access ) : ?>
                <div style="padding:16px;background:#FEF3C7;border:1px solid #F59E0B;border-radius:6px;color:#92400E;margin-bottom:16px;">
                    Cet utilisateur n'a <strong>pas accès</strong> à l'aide personnalisée. Cliquez « Donner l'accès » ci-dessus pour l'activer.
                </div>
            <?php endif; ?>

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

            <!-- Actions rapides -->
            <div style="margin-top:24px;">
                <h3 style="font-size:15px;margin:0 0 12px;color:#374151;">Actions rapides</h3>
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <form method="post" action="" style="margin:0;">
                        <?php wp_nonce_field( 'jaide_deduct_credit' ); ?>
                        <input type="hidden" name="user_id" value="<?php echo esc_attr( $user->ID ); ?>" />
                        <input type="hidden" name="jaide_deduct" value="question" />
                        <button type="submit" class="button" style="background:#FEF3C7;border-color:#F59E0B;color:#92400E;"
                                onclick="return confirm('Retirer 1 question à <?php echo esc_attr( $user->display_name ); ?> ?');"
                                <?php echo $credits['questions_remaining'] <= 0 ? 'disabled' : ''; ?>>
                            &minus;1 Question (reste : <?php echo $credits['questions_remaining']; ?>)
                        </button>
                    </form>
                    <form method="post" action="" style="margin:0;">
                        <?php wp_nonce_field( 'jaide_deduct_credit' ); ?>
                        <input type="hidden" name="user_id" value="<?php echo esc_attr( $user->ID ); ?>" />
                        <input type="hidden" name="jaide_deduct" value="copie" />
                        <button type="submit" class="button" style="background:#FEE2E2;border-color:#EF4444;color:#991B1B;"
                                onclick="return confirm('Retirer 1 copie à <?php echo esc_attr( $user->display_name ); ?> ?');"
                                <?php echo $credits['copies_remaining'] <= 0 ? 'disabled' : ''; ?>>
                            &minus;1 Copie (reste : <?php echo $credits['copies_remaining']; ?>)
                        </button>
                    </form>
                </div>
                <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
                    <form method="post" action="" style="margin:0;">
                        <?php wp_nonce_field( 'jaide_add_credit' ); ?>
                        <input type="hidden" name="user_id" value="<?php echo esc_attr( $user->ID ); ?>" />
                        <input type="hidden" name="jaide_add" value="question" />
                        <button type="submit" class="button" style="background:#D1FAE5;border-color:#10B981;color:#065F46;">
                            +1 Question
                        </button>
                    </form>
                    <form method="post" action="" style="margin:0;">
                        <?php wp_nonce_field( 'jaide_add_credit' ); ?>
                        <input type="hidden" name="user_id" value="<?php echo esc_attr( $user->ID ); ?>" />
                        <input type="hidden" name="jaide_add" value="copie" />
                        <button type="submit" class="button" style="background:#D1FAE5;border-color:#10B981;color:#065F46;">
                            +1 Copie
                        </button>
                    </form>
                </div>
                <p style="font-size:12px;color:#9CA3AF;margin:8px 0 0;">Ajustez manuellement les crédits (ex : aide déjà fournie hors système ou bonus).</p>
            </div>

            <!-- Formulaire ajustement avancé -->
            <details style="margin-top:24px;">
                <summary style="cursor:pointer;font-size:14px;color:#6B7280;font-weight:500;">Ajustement avancé (modifier les limites)</summary>
                <form method="post" action="" style="margin-top:12px;">
                    <?php wp_nonce_field( 'jaide_save_credits' ); ?>
                    <input type="hidden" name="user_id" value="<?php echo esc_attr( $user->ID ); ?>" />

                    <p style="font-size:13px;color:#6B7280;margin:0 0 12px;">
                        Laissez vide pour utiliser la limite de la règle Access Manager (<?php echo $credits['copies_limit_global']; ?> copies, <?php echo $credits['questions_limit_global']; ?> questions).
                        Saisissez un nombre pour remplacer.
                    </p>

                    <div class="jaide-form-row">
                        <div class="jaide-form-field">
                            <label class="jaide-form-label">Limite copies</label>
                            <input type="number" name="copies_limit" class="jaide-form-input jaide-form-input--sm"
                                   value="<?php echo $credits['copies_has_override'] ? esc_attr( $credits['copies_limit'] ) : ''; ?>"
                                   placeholder="<?php echo esc_attr( $credits['copies_limit_global'] ); ?>" min="0" />
                        </div>
                        <div class="jaide-form-field">
                            <label class="jaide-form-label">Limite questions</label>
                            <input type="number" name="questions_limit" class="jaide-form-input jaide-form-input--sm"
                                   value="<?php echo $credits['questions_has_override'] ? esc_attr( $credits['questions_limit'] ) : ''; ?>"
                                   placeholder="<?php echo esc_attr( $credits['questions_limit_global'] ); ?>" min="0" />
                        </div>
                    </div>

                    <button type="submit" name="jaide_save_credits" class="button button-primary">
                        Enregistrer
                    </button>
                </form>
            </details>
        </div>
    <?php endif; ?>
</div>
