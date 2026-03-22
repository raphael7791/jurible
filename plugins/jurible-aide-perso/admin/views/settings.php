<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$options = get_option( 'jaide_options', [] );
?>
<div class="wrap jaide-wrap">
    <h1 class="jaide-page-title">Aide Personnalisée — Paramètres</h1>

    <!-- Shortcode -->
    <div class="jaide-settings-card" style="background:linear-gradient(135deg,#B0001D 0%,#DC2626 50%,#7C3AED 100%);color:#fff;">
        <h2 class="jaide-settings-card__title" style="color:#fff;border-bottom-color:rgba(255,255,255,0.2);">Shortcode</h2>
        <p style="margin:0 0 12px;font-size:14px;opacity:0.9;">Collez ce shortcode dans n'importe quelle page pour afficher le formulaire d'aide personnalisée :</p>
        <code id="jaide-shortcode" style="display:inline-block;padding:10px 20px;background:rgba(0,0,0,0.25);border-radius:6px;font-size:16px;font-weight:600;letter-spacing:0.5px;cursor:pointer;" onclick="navigator.clipboard.writeText('[jurible_aide_perso]');this.textContent='Copié !';setTimeout(function(){document.getElementById('jaide-shortcode').textContent='[jurible_aide_perso]'},1500);" title="Cliquer pour copier">[jurible_aide_perso]</code>
        <p style="margin:8px 0 0;font-size:12px;opacity:0.7;">Cliquez pour copier</p>
    </div>

    <form method="post" action="">
        <?php wp_nonce_field( 'jaide_save_settings' ); ?>

        <!-- Accès -->
        <div class="jaide-settings-card">
            <h2 class="jaide-settings-card__title">Accès</h2>

            <div class="jaide-form-field">
                <p style="padding:12px 16px;background:#EFF6FF;border:1px solid #BFDBFE;border-radius:6px;color:#1E40AF;margin:0;">
                    <strong>L'accès est maintenant géré via Access Manager &rarr; Règles d'accès.</strong><br>
                    Cochez « Aide personnalisée » sur les règles correspondant aux produits Formule Réussite.
                    <?php if ( is_admin() && current_user_can( 'manage_options' ) ) : ?>
                        <br><a href="<?php echo esc_url( admin_url( 'admin.php?page=jam-rules' ) ); ?>">Voir les règles d'accès &rarr;</a>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Limites -->
        <div class="jaide-settings-card">
            <h2 class="jaide-settings-card__title">Limites</h2>

            <div class="jaide-form-row">
                <div class="jaide-form-field">
                    <label for="copies_limit" class="jaide-form-label">Corrections de copies (total)</label>
                    <input type="number" id="copies_limit" name="copies_limit" class="jaide-form-input jaide-form-input--sm" value="<?php echo esc_attr( $options['copies_limit'] ?? 1 ); ?>" min="0" />
                    <p class="jaide-form-hint">Nombre total par étudiant</p>
                </div>

                <div class="jaide-form-field">
                    <label for="questions_limit" class="jaide-form-label">Questions (total)</label>
                    <input type="number" id="questions_limit" name="questions_limit" class="jaide-form-input jaide-form-input--sm" value="<?php echo esc_attr( $options['questions_limit'] ?? 0 ); ?>" min="0" />
                    <p class="jaide-form-hint">Nombre total par étudiant</p>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div class="jaide-settings-card">
            <h2 class="jaide-settings-card__title">Notifications</h2>

            <div class="jaide-form-field">
                <label class="jaide-toggle">
                    <input type="checkbox" name="notify_prof_new" value="1" <?php checked( ! empty( $options['notify_prof_new'] ) ); ?> />
                    <span class="jaide-toggle__slider"></span>
                    <span class="jaide-toggle__text">Email au prof lors d'une nouvelle demande</span>
                </label>
            </div>

            <div class="jaide-form-field">
                <label class="jaide-toggle">
                    <input type="checkbox" name="notify_student_reply" value="1" <?php checked( ! empty( $options['notify_student_reply'] ) ); ?> />
                    <span class="jaide-toggle__slider"></span>
                    <span class="jaide-toggle__text">Email à l'étudiant lors d'une réponse</span>
                </label>
            </div>
        </div>

        <!-- Expéditeur -->
        <div class="jaide-settings-card">
            <h2 class="jaide-settings-card__title">Expéditeur des emails</h2>

            <div class="jaide-form-row">
                <div class="jaide-form-field">
                    <label for="from_name" class="jaide-form-label">Nom</label>
                    <input type="text" id="from_name" name="from_name" class="jaide-form-input" value="<?php echo esc_attr( $options['from_name'] ?? get_bloginfo( 'name' ) ); ?>" />
                </div>

                <div class="jaide-form-field">
                    <label for="from_email" class="jaide-form-label">Email</label>
                    <input type="email" id="from_email" name="from_email" class="jaide-form-input" value="<?php echo esc_attr( $options['from_email'] ?? get_option( 'admin_email' ) ); ?>" />
                </div>
            </div>
        </div>

        <button type="submit" name="jaide_save_settings" class="button button-primary button-large">
            Enregistrer les paramètres
        </button>
    </form>
</div>
