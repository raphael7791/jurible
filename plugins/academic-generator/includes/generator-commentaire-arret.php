<?php
/**
 * Générateur de commentaires d'arrêt - Design Jurible
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once dirname(__FILE__) . '/functions-common.php';
require_once dirname(__FILE__) . '/cpt-commentaire-arret.php';

// ============================================================================
// SHORTCODE FORMULAIRE PRINCIPAL
// ============================================================================

function aga_shortcode_generateur_commentaire_arret($atts) {
    ob_start();
    
    if (!defined('OPENAI_API_KEY') || empty(OPENAI_API_KEY)) {
        echo '<div class="aga-alert aga-alert-error">';
        echo '<p><strong>Configuration manquante</strong><br>Le générateur n\'est pas correctement configuré.</p>';
        echo '</div>';
        return ob_get_clean();
    }

    aga_render_formulaire_commentaire_arret();
    return ob_get_clean();
}
add_shortcode('generateur_commentaire_arret', 'aga_shortcode_generateur_commentaire_arret');

// ============================================================================
// RENDU DU FORMULAIRE
// ============================================================================

function aga_render_formulaire_commentaire_arret() {
    $current_user_id = get_current_user_id();
    $verification = aga_peut_generer($current_user_id, 1);
    ?>
    
    <div class="aga-page">
        <!-- Breadcrumb -->
        <nav class="aga-breadcrumb">
            <a href="<?php echo home_url('/generateurs/'); ?>" class="aga-breadcrumb-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Générateurs IA
            </a>
        </nav>

        <!-- Header -->
        <header class="aga-header">
            <div class="aga-header-content">
                <h1 class="aga-title">Générateur de <span class="aga-title-highlight">commentaire d'arrêt</span></h1>
                <p class="aga-subtitle">Obtenez un commentaire complet selon la méthodologie universitaire.</p>
            </div>
            <div class="aga-header-actions">
                <a href="<?php echo home_url('/mes-commentaires/'); ?>" class="aga-btn aga-btn-outline">
                    Mon historique
                </a>
            </div>
        </header>

        <?php if ($verification['autorise']): ?>
        
        <!-- Formulaire -->
        <div class="aga-form-wrapper">
            <form id="commentaireArretForm" class="aga-form" method="POST">
                <?php wp_nonce_field('generateur_commentaire_arret_action', 'generateur_commentaire_arret_nonce'); ?>
                
                <div class="aga-form-card">
                    <!-- Ligne 1 : Références + Matière -->
                    <div class="aga-form-row">
                        <div class="aga-form-group">
                            <label class="aga-label">
                                Références de l'arrêt <span class="aga-required">*</span>
                            </label>
                            <input type="text" 
                                   class="aga-input" 
                                   name="references" 
                                   placeholder="Ex: Cass. Civ. 1ère, 4 mai 2017, n°16-17.189" 
                                   required 
                                   minlength="8"
                                   maxlength="200">
                        </div>
                        
                        <div class="aga-form-group">
                            <label class="aga-label">
                                Matière concernée <span class="aga-required">*</span>
                            </label>
                            <select class="aga-select" name="matiere" required>
                                <option value="" disabled selected>Sélectionnez une matière...</option>
                                <optgroup label="LICENCE 1">
                                    <option value="introduction-droit">Introduction au droit</option>
                                    <option value="droit-constitutionnel">Droit constitutionnel</option>
                                    <option value="droit-civil-personnes">Droit civil - Personnes</option>
                                    <option value="droit-civil-famille">Droit civil - Famille</option>
                                    <option value="histoire-droit">Histoire du droit</option>
                                    <option value="institutions-judiciaires">Institutions judiciaires</option>
                                </optgroup>
                                <optgroup label="LICENCE 2">
                                    <option value="droit-obligations">Droit des obligations</option>
                                    <option value="droit-penal">Droit pénal général</option>
                                    <option value="droit-administratif">Droit administratif</option>
                                    <option value="droit-biens">Droit des biens</option>
                                    <option value="droit-europeen">Droit européen</option>
                                    <option value="procedure-civile">Procédure civile</option>
                                    <option value="droit-commercial">Droit commercial</option>
                                </optgroup>
                                <optgroup label="LICENCE 3">
                                    <option value="droit-societes">Droit des sociétés</option>
                                    <option value="droit-travail">Droit du travail</option>
                                    <option value="droit-contrats-speciaux">Contrats spéciaux</option>
                                    <option value="droit-suretes">Droit des sûretés</option>
                                    <option value="libertes-fondamentales">Libertés fondamentales</option>
                                    <option value="droit-international-public">Droit international public</option>
                                    <option value="droit-international-prive">Droit international privé</option>
                                    <option value="procedure-penale">Procédure pénale</option>
                                    <option value="responsabilite-civile">Responsabilité civile</option>
                                </optgroup>
                                <optgroup label="AUTRES">
                                    <option value="droit-fiscal">Droit fiscal</option>
                                    <option value="philosophie-droit">Philosophie du droit</option>
                                    <option value="autres">Autres</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <!-- Textarea -->
                    <div class="aga-form-group">
                        <label class="aga-label">
                            Contenu de l'arrêt <span class="aga-required">*</span>
                        </label>
                        <div class="aga-textarea-wrapper">
                            <textarea class="aga-textarea" 
                                      name="contenu_arret"
                                      placeholder="Collez ici le texte intégral de l'arrêt à commenter...

Exemple :
LA COUR DE CASSATION, PREMIÈRE CHAMBRE CIVILE, a rendu l'arrêt suivant :

Sur le moyen unique :
Attendu que..." 
                                      required
                                      minlength="100"
                                      maxlength="15000"></textarea>
                            <button type="submit" class="aga-submit-btn" title="Générer le commentaire">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M12 19V5M5 12l7-7 7 7"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Lien Légifrance -->
                    <p class="aga-help-text">
                        <span class="aga-help-icon">💡</span>
                        Vous pouvez trouver les arrêts sur <a href="https://www.legifrance.gouv.fr/search/juri" target="_blank" rel="noopener">Légifrance</a>
                    </p>

                    <!-- Feedback zone -->
                    <div class="aga-feedback" id="feedbackZone">
                        <div class="aga-feedback-content">
                            <div class="aga-spinner"></div>
                            <span id="feedbackText">Génération en cours...</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Guide d'utilisation -->
        <div class="aga-guide">
            <button class="aga-guide-header" onclick="toggleGuide()" type="button">
                <span class="aga-guide-icon">💡</span>
                <span class="aga-guide-title">Comment utiliser le générateur ?</span>
                <svg class="aga-guide-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </button>
            
            <div class="aga-guide-content" id="guideContent">
                <div class="aga-guide-steps">
                    <div class="aga-step">
                        <span class="aga-step-number">1</span>
                        <div class="aga-step-content">
                            <h4>Trouvez votre arrêt sur internet</h4>
                            <p>· Où chercher ? Tapez dans Google : <code>"nom de l'arrêt" site:legifrance.gouv.fr</code></p>
                        </div>
                    </div>
                    
                    <div class="aga-step">
                        <span class="aga-step-number">2</span>
                        <div class="aga-step-content">
                            <h4>Copiez les bonnes références</h4>
                            <p>· Format attendu : <code>Cass. Civ. 1ère, 12 juillet 2023, n°21-20.345</code></p>
                        </div>
                    </div>
                    
                    <div class="aga-step">
                        <span class="aga-step-number">3</span>
                        <div class="aga-step-content">
                            <h4>Copiez le texte intégral</h4>
                            <p>· Depuis : "LA COUR DE CASSATION... a rendu l'arrêt suivant :"</p>
                            <p>· Jusqu'à : "PAR CES MOTIFS : REJETTE/CASSE..."</p>
                        </div>
                    </div>
                    
                    <div class="aga-step">
                        <span class="aga-step-number">4</span>
                        <div class="aga-step-content">
                            <h4>Analysez le commentaire généré</h4>
                            <p>· Structure : Introduction (accroche, faits, procédure, problématique, annonce) + I. Sens + II. Valeur/Portée</p>
                        </div>
                    </div>
                    
                    <div class="aga-step">
                        <span class="aga-step-number">5</span>
                        <div class="aga-step-content">
                            <h4>Vérifiez et complétez</h4>
                            <p>Ajoutez les références de votre cours et la jurisprudence vue en TD.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php else: ?>
            <!-- Limite atteinte -->
            <?php 
            $doit_afficher_modal = aga_doit_afficher_modal_avis($current_user_id);
            if ($doit_afficher_modal): aga_render_modal_avis(); endif;
            ?>
            
            <div class="aga-limit-card" <?php echo $doit_afficher_modal ? 'style="display:none;"' : ''; ?>>
                <div class="aga-limit-icon">🔒</div>
                <h3>Limite <span class="highlight">mensuelle</span> atteinte</h3>
                <?php if (aga_obtenir_type_compte() === 'gratuit'): ?>
                    <p>Vous avez utilisé vos <?php echo $verification['limite']; ?> crédits gratuits ce mois-ci.</p>
                    <div class="aga-limit-cta">
                        <p><strong>Rejoignez l'Académie pour continuer :</strong></p>
                        <ul>
                            <li>✓ 30 crédits par mois</li>
                            <li>✓ Accès à tous les générateurs</li>
                            <li>✓ Support prioritaire</li>
                        </ul>
                        <a href="https://aideauxtd.com/academie-droit" class="aga-btn aga-btn-primary">
                            Rejoindre l'Académie
                        </a>
                    </div>
                <?php else: ?>
                    <p>Votre limite premium se réinitialisera le 1er du mois prochain.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
    // Toggle guide
    function toggleGuide() {
        const content = document.getElementById('guideContent');
        const chevron = document.querySelector('.aga-guide-chevron');
        content.classList.toggle('open');
        chevron.classList.toggle('open');
    }

    // Form submission
    document.getElementById('commentaireArretForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = document.querySelector('.aga-submit-btn');
        const feedback = document.getElementById('feedbackZone');
        
        if (btn.disabled) return;
        
        btn.disabled = true;
        btn.classList.add('loading');
        feedback.classList.add('show');
        document.getElementById('feedbackText').textContent = 'Génération en cours... (environ 25-30 secondes)';
        
        const formData = new FormData(this);
        formData.append('action', 'aga_ajax_generer_commentaire_arret');
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.data.url;
            } else {
                document.getElementById('feedbackText').textContent = data.data.message;
                feedback.classList.add('error');
                btn.disabled = false;
                btn.classList.remove('loading');
                setTimeout(() => {
                    feedback.classList.remove('show', 'error');
                }, 4000);
            }
        })
        .catch(() => {
            document.getElementById('feedbackText').textContent = 'Erreur de connexion';
            feedback.classList.add('error');
            btn.disabled = false;
            btn.classList.remove('loading');
        });
    });
    </script>
    <?php
}

// ============================================================================
// AJAX HANDLER
// ============================================================================

function aga_ajax_generer_commentaire_arret() {
    if (!wp_verify_nonce($_POST['generateur_commentaire_arret_nonce'], 'generateur_commentaire_arret_action')) {
        wp_send_json_error(['message' => 'Erreur de sécurité']);
        return;
    }

    $user_id = (int) get_current_user_id();
    
    $rate_limit = aga_verifier_rate_limit($user_id);
    if (!$rate_limit['autorise']) {
        wp_send_json_error(['message' => $rate_limit['raison']]);
        return;
    }
    
    aga_enregistrer_tentative($user_id);
    
    $verification = aga_peut_generer($user_id, 1);
    if (!$verification['autorise']) {
        wp_send_json_error(['message' => $verification['raison']]);
        return;
    }

    $references = sanitize_text_field($_POST['references'] ?? '');
    $matiere = sanitize_text_field($_POST['matiere'] ?? '');
    $contenu_arret = sanitize_textarea_field($_POST['contenu_arret'] ?? '');
    
    $erreurs = aga_valider_donnees_formulaire([
        'references' => $references,
        'matiere' => $matiere,
        'contenu' => $contenu_arret
    ], 'commentaire_arret');
    
    if (!empty($erreurs)) {
        wp_send_json_error(['message' => implode(' ', $erreurs)]);
        return;
    }
    
    $prompt = aga_construire_prompt_commentaire_arret($references, $matiere, $contenu_arret);
    $resultat = aga_appeler_openai($prompt, 'commentaire_arret');
    
    if (isset($resultat['erreur'])) {
        wp_send_json_error(['message' => $resultat['erreur']]);
        return;
    }
    
    $post_id = aga_creer_commentaire_arret($references, $matiere, $contenu_arret, $resultat['succes'], 1);

    if ($post_id) {
        aga_incrementer_compteur($user_id, 1);
        wp_send_json_success(['url' => get_permalink($post_id)]);
    } else {
        wp_send_json_error(['message' => 'Erreur lors de l\'enregistrement']);
    }
}
add_action('wp_ajax_aga_ajax_generer_commentaire_arret', 'aga_ajax_generer_commentaire_arret');

// ============================================================================
// SHORTCODE HISTORIQUE
// ============================================================================

function aga_shortcode_historique_commentaires($atts) {
    ob_start();
    aga_render_historique_commentaires();
    return ob_get_clean();
}
add_shortcode('historique_commentaires_arret', 'aga_shortcode_historique_commentaires');

function aga_render_historique_commentaires() {
    $current_user_id = get_current_user_id();
    
    if (!$current_user_id) {
        echo '<div class="aga-alert aga-alert-error"><p>Vous devez être connecté.</p></div>';
        return;
    }
    
    $verification = aga_peut_generer($current_user_id, 1);
    $commentaires_par_matiere = aga_obtenir_commentaires_par_matiere($current_user_id);
    ?>

    <div class="aga-page">
        <nav class="aga-breadcrumb">
            <a href="<?php echo home_url('/generateur-commentaire-arret/'); ?>" class="aga-breadcrumb-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Générateur
            </a>
        </nav>

        <header class="aga-header">
            <div class="aga-header-content">
                <h1 class="aga-title">Mes <span class="aga-title-highlight">commentaires d'arrêt</span></h1>
                <p class="aga-subtitle"><?php echo $verification['utilise']; ?>/<?php echo $verification['limite']; ?> crédits utilisés ce mois</p>
            </div>
            <div class="aga-header-actions">
                <a href="<?php echo home_url('/generateur-commentaire-arret/'); ?>" class="aga-btn aga-btn-primary">
                    + Nouveau commentaire
                </a>
            </div>
        </header>

        <?php if (empty($commentaires_par_matiere)): ?>
            <div class="aga-empty-state">
                <p>Aucun commentaire d'arrêt pour le moment.</p>
                <a href="<?php echo home_url('/generateur-commentaire-arret/'); ?>" class="aga-btn aga-btn-outline">Créer mon premier commentaire</a>
            </div>
        <?php else: ?>
            <?php foreach ($commentaires_par_matiere as $matiere => $commentaires): ?>
                <div class="aga-section">
                    <h2 class="aga-section-title"><?php echo esc_html($matiere); ?></h2>
                    <div class="aga-list">
                        <?php foreach ($commentaires as $commentaire): 
                            $refs = get_post_meta($commentaire->ID, '_aga_references', true);
                            $date = get_post_meta($commentaire->ID, '_aga_date_generation', true);
                        ?>
                            <div class="aga-list-item" data-id="<?php echo $commentaire->ID; ?>">
                                <div class="aga-list-info">
                                    <span class="aga-list-title"><?php echo esc_html($refs ?: $commentaire->post_title); ?></span>
                                    <span class="aga-list-date"><?php echo $date ? date('d/m/Y', strtotime($date)) : ''; ?></span>
                                </div>
                                <div class="aga-list-actions">
                                    <a href="<?php echo get_permalink($commentaire->ID); ?>" class="aga-btn aga-btn-small">Voir</a>
                                    <button class="aga-btn aga-btn-small aga-btn-danger" onclick="deleteCommentaire(<?php echo $commentaire->ID; ?>, this)">Supprimer</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
    function deleteCommentaire(id, btn) {
        if (!confirm('Supprimer ce commentaire ?')) return;
        
        btn.disabled = true;
        const formData = new FormData();
        formData.append('action', 'supprimer_commentaire');
        formData.append('commentaire_id', id);
        formData.append('nonce', '<?php echo wp_create_nonce('supprimer_commentaire_nonce'); ?>');
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.closest('.aga-list-item').remove();
            } else {
                alert('Erreur');
                btn.disabled = false;
            }
        });
    }
    </script>
    <?php
}