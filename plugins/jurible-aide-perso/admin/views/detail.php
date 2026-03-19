<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$table = $wpdb->prefix . 'jurible_aide_requests';

$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
if ( ! $id ) {
    echo '<div class="wrap"><p>ID invalide.</p></div>';
    return;
}

$r = $wpdb->get_row( $wpdb->prepare(
    "SELECT r.*, u.display_name, u.user_email as user_email_wp
     FROM $table r
     JOIN {$wpdb->users} u ON r.user_id = u.ID
     WHERE r.id = %d",
    $id
) );

if ( ! $r ) {
    echo '<div class="wrap"><p>Demande introuvable.</p></div>';
    return;
}

$type_label = $r->type === 'question' ? 'Question' : 'Copie';
$status_labels = [ 'pending' => 'En attente', 'in_progress' => 'En cours', 'completed' => 'Traité' ];
?>
<div class="wrap jaide-wrap">
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=jaide-inbox' ) ); ?>" class="jaide-back">&larr; Retour à l'inbox</a>

    <div class="jaide-detail-header">
        <div class="jaide-detail-header__left">
            <?php echo get_avatar( $r->user_id, 48 ); ?>
            <div>
                <h1 class="jaide-detail-header__title"><?php echo esc_html( $type_label ); ?> — <?php echo esc_html( $r->matiere ); ?></h1>
                <p class="jaide-detail-header__meta">
                    Par <strong><?php echo esc_html( $r->nom ); ?></strong> · <?php echo esc_html( $r->annee ); ?>
                    · <?php echo esc_html( date_i18n( 'j F Y à H:i', strtotime( $r->created_at ) ) ); ?>
                </p>
            </div>
        </div>
        <span class="jaide-badge jaide-badge--<?php echo esc_attr( $r->status ); ?> jaide-badge--lg">
            <?php echo esc_html( $status_labels[ $r->status ] ?? $r->status ); ?>
        </span>
    </div>

    <div class="jaide-detail-grid">
        <!-- Colonne gauche : demande -->
        <div class="jaide-detail-card">
            <h2 class="jaide-detail-card__title">Demande</h2>

            <div class="jaide-detail-field">
                <span class="jaide-detail-field__label">Type</span>
                <span class="jaide-item__type jaide-item__type--<?php echo esc_attr( $r->type ); ?>">
                    <?php echo esc_html( $type_label ); ?>
                </span>
            </div>

            <div class="jaide-detail-field">
                <span class="jaide-detail-field__label">Étudiant</span>
                <span><?php echo esc_html( $r->nom ); ?> (<?php echo esc_html( $r->email ); ?>)</span>
            </div>

            <div class="jaide-detail-field">
                <span class="jaide-detail-field__label">Année / Matière</span>
                <span><?php echo esc_html( $r->annee ); ?> — <?php echo esc_html( $r->matiere ); ?></span>
            </div>

            <?php if ( $r->message ) : ?>
            <div class="jaide-detail-field">
                <span class="jaide-detail-field__label"><?php echo $r->type === 'question' ? 'Question' : 'Commentaires'; ?></span>
                <div class="jaide-detail-field__text"><?php echo nl2br( esc_html( $r->message ) ); ?></div>
            </div>
            <?php endif; ?>

            <?php if ( $r->file_url ) : ?>
            <div class="jaide-detail-field">
                <span class="jaide-detail-field__label">Fichier joint</span>
                <a href="<?php echo esc_url( $r->file_url ); ?>" target="_blank" rel="noopener" class="jaide-file-link">
                    📎 <?php echo esc_html( $r->file_name ); ?>
                </a>
            </div>
            <?php endif; ?>

            <?php if ( $r->status === 'completed' && $r->response ) : ?>
            <hr class="jaide-detail-sep" />
            <h2 class="jaide-detail-card__title jaide-detail-card__title--success">Réponse envoyée</h2>
            <div class="jaide-detail-field__text"><?php echo nl2br( esc_html( $r->response ) ); ?></div>
            <?php if ( $r->response_file_url ) : ?>
                <p style="margin-top:12px;">
                    <a href="<?php echo esc_url( $r->response_file_url ); ?>" target="_blank" rel="noopener" class="jaide-file-link">
                        📎 Fichier/vidéo de correction
                    </a>
                </p>
            <?php endif; ?>
            <p class="jaide-detail-responded-info">
                Répondu le <?php echo esc_html( date_i18n( 'j F Y à H:i', strtotime( $r->responded_at ) ) ); ?>
                <?php
                if ( $r->responded_by ) {
                    $responder = get_userdata( $r->responded_by );
                    if ( $responder ) {
                        echo ' par ' . esc_html( $responder->display_name );
                    }
                }
                ?>
            </p>
            <?php endif; ?>
        </div>

        <!-- Colonne droite : formulaire de réponse / modification -->
        <div class="jaide-detail-card">
            <?php if ( $r->status === 'completed' ) : ?>
                <h2 class="jaide-detail-card__title">Modifier la réponse</h2>

                <div id="jaide-edit-toggle">
                    <p style="color:#6B7280;font-size:14px;margin:0 0 16px;">La réponse a déjà été envoyée à l'étudiant. Vous pouvez la modifier et renvoyer.</p>
                    <button type="button" class="button" onclick="document.getElementById('jaide-edit-toggle').style.display='none';document.getElementById('jaide-respond-form').style.display='';">
                        Modifier la réponse
                    </button>
                </div>

                <form id="jaide-respond-form" data-id="<?php echo esc_attr( $r->id ); ?>" style="display:none;">
                    <div class="jaide-form-field">
                        <label for="jaide-response" class="jaide-form-label">Votre réponse</label>
                        <textarea id="jaide-response" name="response" class="jaide-form-textarea" rows="8" required><?php echo esc_textarea( $r->response ); ?></textarea>
                    </div>

                    <div class="jaide-form-field">
                        <label for="jaide-response-file" class="jaide-form-label">Nouveau fichier de correction <span class="jaide-optional">(optionnel — remplace l'existant)</span></label>
                        <input type="file" id="jaide-response-file" name="file" accept=".pdf,.docx,.odt" class="jaide-form-file" />
                        <?php if ( $r->response_file_url ) : ?>
                            <p class="jaide-form-hint">Fichier actuel : <a href="<?php echo esc_url( $r->response_file_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( basename( $r->response_file_url ) ); ?></a></p>
                        <?php endif; ?>
                    </div>

                    <div class="jaide-form-field">
                        <label for="jaide-video-url" class="jaide-form-label">URL vidéo de correction <span class="jaide-optional">(optionnel)</span></label>
                        <input type="url" id="jaide-video-url" name="video_url" class="jaide-form-input" placeholder="https://www.loom.com/share/..." value="<?php echo esc_attr( $r->response_file_url && (strpos($r->response_file_url, 'http') === 0 && !preg_match('/\.(pdf|docx|odt)$/i', $r->response_file_url)) ? $r->response_file_url : '' ); ?>" />
                    </div>

                    <button type="submit" class="button button-primary button-large jaide-btn-respond">
                        Modifier et renvoyer
                    </button>
                </form>

            <?php else : ?>
                <h2 class="jaide-detail-card__title">Répondre</h2>

                <form id="jaide-respond-form" data-id="<?php echo esc_attr( $r->id ); ?>">
                    <div class="jaide-form-field">
                        <label for="jaide-response" class="jaide-form-label">Votre réponse</label>
                        <textarea id="jaide-response" name="response" class="jaide-form-textarea" rows="8" placeholder="Rédigez votre réponse..." required></textarea>
                    </div>

                    <div class="jaide-form-field">
                        <label for="jaide-response-file" class="jaide-form-label">Fichier de correction <span class="jaide-optional">(optionnel)</span></label>
                        <input type="file" id="jaide-response-file" name="file" accept=".pdf,.docx,.odt" class="jaide-form-file" />
                    </div>

                    <div class="jaide-form-field">
                        <label for="jaide-video-url" class="jaide-form-label">URL vidéo de correction <span class="jaide-optional">(optionnel)</span></label>
                        <input type="url" id="jaide-video-url" name="video_url" class="jaide-form-input" placeholder="https://www.loom.com/share/..." />
                    </div>

                    <button type="submit" class="button button-primary button-large jaide-btn-respond">
                        Envoyer la réponse
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
