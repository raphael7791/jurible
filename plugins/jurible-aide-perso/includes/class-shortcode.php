<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Jaide_Shortcode {

    public static function register() {
        add_shortcode( 'jurible_aide_perso', [ self::class, 'render' ] );
    }

    public static function render( $atts ) {
        // Enqueue assets
        wp_enqueue_style( 'jaide-front', JAIDE_URL . 'public/css/aide-perso.css', [], JAIDE_VERSION );
        wp_enqueue_script( 'jaide-front', JAIDE_URL . 'public/js/aide-perso.js', [], JAIDE_VERSION, true );
        wp_localize_script( 'jaide-front', 'jaideData', [
            'restUrl' => esc_url_raw( rest_url( 'aide-perso/v1' ) ),
            'nonce'   => wp_create_nonce( 'wp_rest' ),
        ] );

        ob_start();

        // ── Non connecté ────────────────────────────────────────────────
        if ( ! is_user_logged_in() ) {
            self::render_not_logged_in();
            return ob_get_clean();
        }

        // ── Pas d'accès ─────────────────────────────────────────────────
        if ( ! Jaide_Access::user_has_access() ) {
            self::render_no_access();
            return ob_get_clean();
        }

        // ── Formulaire ──────────────────────────────────────────────────
        $user                = wp_get_current_user();
        $copies_remaining    = Jaide_Access::copies_remaining();
        $questions_remaining = Jaide_Access::questions_remaining();
        $copies_limit        = Jaide_Access::get_copies_limit();
        $questions_limit     = Jaide_Access::get_questions_limit();
        ?>
        <div class="aide-perso" id="aide-perso">

            <!-- Header -->
            <div class="aide-perso__header">
                <span class="aide-perso__badge">Formule Réussite</span>
                <h2 class="aide-perso__title">Aide personnalisée</h2>
                <p class="aide-perso__subtitle">Posez vos questions de cours ou déposez une copie pour correction par un enseignant.</p>
            </div>

            <!-- Historique (en haut) -->
            <div class="aide-perso__history" id="jaide-history" style="display:none;">
                <h3 class="aide-perso__history-title">Mes demandes</h3>
                <div class="aide-perso__history-list" id="jaide-history-list"></div>
            </div>

            <!-- Onglets -->
            <div class="aide-perso__tabs">
                <button class="aide-perso__tab aide-perso__tab--active" data-tab="question" type="button">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Poser une question
                    <span class="aide-perso__tab-counter"><?php echo esc_html( $questions_remaining . '/' . $questions_limit ); ?></span>
                </button>
                <button class="aide-perso__tab" data-tab="copie" type="button">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    Déposer une copie
                    <span class="aide-perso__tab-counter"><?php echo esc_html( $copies_remaining . '/' . $copies_limit ); ?></span>
                </button>
            </div>

            <!-- Tab Question -->
            <div class="aide-perso__panel aide-perso__panel--active" id="panel-question">

                <div class="aide-perso__credits aide-perso__credits--<?php echo $questions_remaining <= 0 ? 'empty' : ( $questions_remaining === 1 ? 'low' : 'ok' ); ?>">
                    <span class="aide-perso__credits-number"><?php echo esc_html( $questions_remaining ); ?></span>
                    <span class="aide-perso__credits-text">question<?php echo $questions_remaining > 1 ? 's' : ''; ?> restante<?php echo $questions_remaining > 1 ? 's' : ''; ?> sur <?php echo esc_html( $questions_limit ); ?></span>
                </div>

                <?php if ( $questions_remaining <= 0 ) : ?>
                    <div class="aide-perso__limit-card">
                        <div class="aide-perso__limit-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        </div>
                        <h3>Plus de <span class="aide-perso__limit-highlight">crédits</span></h3>
                        <p>Vous avez utilisé vos <?php echo esc_html( $questions_limit ); ?> question<?php echo $questions_limit > 1 ? 's' : ''; ?> incluse<?php echo $questions_limit > 1 ? 's' : ''; ?> dans votre Formule Réussite.</p>
                    </div>
                <?php else : ?>

                    <!-- Accordion conseils -->
                    <div class="aide-perso__accordion" id="accordion-conseils">
                        <button class="aide-perso__accordion-trigger" type="button" onclick="jaideToggleAccordion('accordion-conseils')">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            Conseils pour une bonne question
                            <svg class="aide-perso__accordion-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div class="aide-perso__accordion-content">
                            <ul>
                                <li>Indiquez précisément le chapitre ou la notion concernée</li>
                                <li>Expliquez ce que vous avez déjà compris et ce qui vous bloque</li>
                                <li>Si possible, joignez une capture d'écran ou un document</li>
                                <li>Une question précise = une réponse plus utile !</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Formulaire question -->
                    <form class="aide-perso__form" id="form-question" data-type="question">
                        <?php self::render_common_fields( $user ); ?>

                        <div class="aide-perso__field">
                            <label for="q-message" class="aide-perso__label">Votre question <span class="aide-perso__required">*</span></label>
                            <textarea id="q-message" name="message" class="aide-perso__textarea" rows="5" placeholder="Décrivez votre question de cours..." required></textarea>
                        </div>

                        <div class="aide-perso__field">
                            <label class="aide-perso__label">Fichier joint <span class="aide-perso__optional">(optionnel)</span></label>
                            <?php self::render_dropzone( 'question' ); ?>
                        </div>

                        <button type="submit" class="aide-perso__submit">
                            <span class="aide-perso__submit-text">Envoyer ma question</span>
                            <span class="aide-perso__submit-loading" style="display:none;">
                                <svg class="aide-perso__spinner" width="20" height="20" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="3" stroke-dasharray="31.4 31.4" stroke-linecap="round"/></svg>
                                Envoi en cours...
                            </span>
                        </button>
                    </form>

                <?php endif; ?>
            </div>

            <!-- Tab Copie -->
            <div class="aide-perso__panel" id="panel-copie">

                <div class="aide-perso__credits aide-perso__credits--<?php echo $copies_remaining <= 0 ? 'empty' : ( $copies_remaining === 1 ? 'low' : 'ok' ); ?>">
                    <span class="aide-perso__credits-number"><?php echo esc_html( $copies_remaining ); ?></span>
                    <span class="aide-perso__credits-text">correction<?php echo $copies_remaining > 1 ? 's' : ''; ?> restante<?php echo $copies_remaining > 1 ? 's' : ''; ?> sur <?php echo esc_html( $copies_limit ); ?></span>
                </div>

                <?php if ( $copies_remaining <= 0 ) : ?>
                    <div class="aide-perso__limit-card">
                        <div class="aide-perso__limit-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        </div>
                        <h3>Plus de <span class="aide-perso__limit-highlight">crédits</span></h3>
                        <p>Vous avez utilisé vos <?php echo esc_html( $copies_limit ); ?> correction<?php echo $copies_limit > 1 ? 's' : ''; ?> de copie incluse<?php echo $copies_limit > 1 ? 's' : ''; ?> dans votre Formule Réussite.</p>
                    </div>
                <?php else : ?>

                    <div class="aide-perso__alert aide-perso__alert--warning">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <div>
                            <strong>Consignes importantes</strong>
                            <ul>
                                <li>Déposez votre copie au format PDF, DOCX ou ODT (max 20 Mo)</li>
                                <li>La correction sera effectuée sous 4 jours ouvrés maximum</li>
                                <li>Vous recevrez un email quand la correction sera disponible</li>
                            </ul>
                        </div>
                    </div>

                    <form class="aide-perso__form" id="form-copie" data-type="copie">
                        <?php self::render_common_fields( $user ); ?>

                        <div class="aide-perso__field">
                            <label for="c-message" class="aide-perso__label">Commentaires <span class="aide-perso__optional">(optionnel)</span></label>
                            <textarea id="c-message" name="message" class="aide-perso__textarea" rows="3" placeholder="Précisions sur votre copie, points à vérifier..."></textarea>
                        </div>

                        <div class="aide-perso__field">
                            <label class="aide-perso__label">Votre copie <span class="aide-perso__required">*</span></label>
                            <?php self::render_dropzone( 'copie' ); ?>
                        </div>

                        <button type="submit" class="aide-perso__submit">
                            <span class="aide-perso__submit-text">Déposer ma copie</span>
                            <span class="aide-perso__submit-loading" style="display:none;">
                                <svg class="aide-perso__spinner" width="20" height="20" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="3" stroke-dasharray="31.4 31.4" stroke-linecap="round"/></svg>
                                Envoi en cours...
                            </span>
                        </button>
                    </form>

                <?php endif; ?>
            </div>

            <!-- Modal confirmation -->
            <div class="aide-perso__modal" id="jaide-modal" style="display:none;">
                <div class="aide-perso__modal-backdrop" onclick="jaideCloseModal()"></div>
                <div class="aide-perso__modal-content">
                    <button class="aide-perso__modal-close" onclick="jaideCloseModal()" type="button">&times;</button>
                    <div class="aide-perso__modal-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <h3 class="aide-perso__modal-title" id="jaide-modal-title"></h3>
                    <p class="aide-perso__modal-text" id="jaide-modal-text"></p>
                    <button class="aide-perso__modal-btn" onclick="jaideCloseModal()" type="button">Compris !</button>
                </div>
            </div>

        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Champs communs aux deux formulaires (nom, email, année, matière).
     */
    private static function render_common_fields( $user ) {
        ?>
        <div class="aide-perso__field-row">
            <div class="aide-perso__field">
                <label for="<?php echo esc_attr( 'nom-' . wp_rand() ); ?>" class="aide-perso__label">Nom complet <span class="aide-perso__required">*</span></label>
                <input type="text" name="nom" class="aide-perso__input" value="<?php echo esc_attr( $user->display_name ); ?>" required />
            </div>
            <div class="aide-perso__field">
                <label for="<?php echo esc_attr( 'email-' . wp_rand() ); ?>" class="aide-perso__label">Email <span class="aide-perso__required">*</span></label>
                <input type="email" name="email" class="aide-perso__input" value="<?php echo esc_attr( $user->user_email ); ?>" required />
            </div>
        </div>
        <div class="aide-perso__field-row">
            <div class="aide-perso__field">
                <label class="aide-perso__label">Année d'étude <span class="aide-perso__required">*</span></label>
                <select name="annee" class="aide-perso__select" required>
                    <option value="">— Sélectionner —</option>
                    <option value="Capacite">Capacité</option>
                    <option value="L1">Licence 1 (L1)</option>
                    <option value="L2">Licence 2 (L2)</option>
                    <option value="L3">Licence 3 (L3)</option>
                </select>
            </div>
            <div class="aide-perso__field">
                <label class="aide-perso__label">Matière <span class="aide-perso__required">*</span></label>
                <input type="text" name="matiere" class="aide-perso__input" placeholder="Ex : Droit civil, Droit constitutionnel..." required />
            </div>
        </div>
        <?php
    }

    /**
     * Zone de drag-drop pour fichier.
     */
    private static function render_dropzone( $type ) {
        $id = 'dropzone-' . $type;
        ?>
        <div class="aide-perso__dropzone" id="<?php echo esc_attr( $id ); ?>" data-type="<?php echo esc_attr( $type ); ?>">
            <input type="file" name="file" class="aide-perso__dropzone-input" accept=".pdf,.docx,.odt" />
            <div class="aide-perso__dropzone-content">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <p>Glissez votre fichier ici ou <span class="aide-perso__dropzone-link">parcourir</span></p>
                <p class="aide-perso__dropzone-hint">PDF, DOCX ou ODT — Max 20 Mo</p>
            </div>
            <div class="aide-perso__file-chip" style="display:none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span class="aide-perso__file-chip-name"></span>
                <button type="button" class="aide-perso__file-chip-remove">&times;</button>
            </div>
        </div>
        <?php
    }

    /**
     * Rendu pour utilisateur non connecté.
     */
    private static function render_not_logged_in() {
        ?>
        <div class="aide-perso aide-perso--locked">
            <div class="aide-perso__locked-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            </div>
            <h3>Connectez-vous pour accéder à l'aide personnalisée</h3>
            <p>Cette fonctionnalité est réservée aux étudiants connectés.</p>
            <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="aide-perso__btn">Se connecter</a>
        </div>
        <?php
    }

    /**
     * Rendu pour utilisateur sans accès.
     */
    private static function render_no_access() {
        ?>
        <div class="aide-perso aide-perso--locked">
            <div class="aide-perso__locked-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
            <h3>Réservé à la Formule Réussite</h3>
            <p>L'aide personnalisée (questions de cours et corrections de copies) est incluse dans la <strong>Formule Réussite</strong>.</p>
            <a href="https://jurible.com" class="aide-perso__btn" target="_blank" rel="noopener">Découvrir la Formule Réussite</a>
        </div>
        <?php
    }
}
