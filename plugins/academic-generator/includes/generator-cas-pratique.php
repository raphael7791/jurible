<?php
/**
 * Générateur de cas pratiques - Design Jurible
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once dirname(__FILE__) . '/functions-common.php';
require_once dirname(__FILE__) . '/cpt-cas-pratique.php';

// ============================================================================
// SHORTCODE FORMULAIRE PRINCIPAL
// ============================================================================

function aga_shortcode_generateur_cas_pratique($atts) {
    ob_start();
    
    if (!defined('OPENAI_API_KEY') || empty(OPENAI_API_KEY)) {
        echo '<div class="aga-alert aga-alert-error">';
        echo '<p><strong>Configuration manquante</strong><br>Le générateur n\'est pas correctement configuré.</p>';
        echo '</div>';
        return ob_get_clean();
    }

    aga_render_formulaire_cas_pratique();
    return ob_get_clean();
}
add_shortcode('generateur_cas_pratique', 'aga_shortcode_generateur_cas_pratique');

// ============================================================================
// RENDU DU FORMULAIRE
// ============================================================================

function aga_render_formulaire_cas_pratique() {
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
                <h1 class="aga-title">Générateur de <span class="aga-title-highlight">cas pratique</span></h1>
                <p class="aga-subtitle">Obtenez une correction complète de votre cas pratique.</p>
            </div>
            <div class="aga-header-actions">
                <a href="<?php echo home_url('/mes-cas-pratiques/'); ?>" class="aga-btn aga-btn-outline">
                    Mon historique
                </a>
            </div>
        </header>

        <?php if ($verification['autorise']): ?>
        
        <!-- Formulaire -->
        <div class="aga-form-wrapper">
            <form id="casPratiqueForm" class="aga-form" method="POST">
                <?php wp_nonce_field('generateur_cas_pratique_action', 'generateur_cas_pratique_nonce'); ?>
                
                <div class="aga-form-card">
                    <!-- Matière -->
                    <div class="aga-form-group" style="margin-bottom: 1.25rem;">
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

                    <!-- Textarea -->
                    <div class="aga-form-group">
                        <label class="aga-label">
                            Sujet du cas pratique <span class="aga-required">*</span>
                        </label>
                        <div class="aga-textarea-wrapper">
                            <textarea class="aga-textarea" 
                                      name="cas_pratique"
                                      placeholder="Collez ici l'intégralité de votre sujet de cas pratique...

Exemple :
Monsieur Dupont a vendu sa voiture à Madame Martin le 15 janvier 2023 pour un montant de 15 000 euros. Toutefois, deux semaines après la vente, Madame Martin découvre que le véhicule a subi un accident grave qui n'avait pas été mentionné lors de la transaction.

Question : Madame Martin peut-elle obtenir l'annulation de la vente ?" 
                                      required
                                      minlength="50"
                                      maxlength="10000"></textarea>
                            <button type="submit" class="aga-submit-btn" title="Générer la correction">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M12 19V5M5 12l7-7 7 7"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Avertissement -->
                    <p class="aga-help-text">
                        <span class="aga-help-icon">⚠️</span>
                        Cet outil est une aide à la révision. Vérifiez toujours avec votre cours.
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
                            <h4>Copiez l'intégralité du sujet</h4>
                            <p>· Incluez tous les faits, même ceux qui semblent anodins</p>
                            <p>· N'oubliez pas les questions posées à la fin</p>
                        </div>
                    </div>
                    
                    <div class="aga-step">
                        <span class="aga-step-number">2</span>
                        <div class="aga-step-content">
                            <h4>Sélectionnez la bonne matière</h4>
                            <p>· Choisissez la matière principale du cas pratique</p>
                            <p>· Ex : droit des obligations, droit pénal, droit du travail...</p>
                        </div>
                    </div>
                    
                    <div class="aga-step">
                        <span class="aga-step-number">3</span>
                        <div class="aga-step-content">
                            <h4>Analysez la correction générée</h4>
                            <p>· Structure : Faits → Problème de droit → Solution en droit → Solution en l'espèce</p>
                            <p>· Vérifiez les articles de loi cités avec votre Code</p>
                        </div>
                    </div>
                    
                    <div class="aga-step">
                        <span class="aga-step-number">4</span>
                        <div class="aga-step-content">
                            <h4>Complétez avec votre cours</h4>
                            <p>· Ajoutez la jurisprudence vue en TD</p>
                            <p>· Adaptez le vocabulaire à celui de votre professeur</p>
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
    document.getElementById('casPratiqueForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = document.querySelector('.aga-submit-btn');
        const feedback = document.getElementById('feedbackZone');
        
        if (btn.disabled) return;
        
        btn.disabled = true;
        btn.classList.add('loading');
        feedback.classList.add('show');
        document.getElementById('feedbackText').textContent = 'Génération en cours... (environ 20-30 secondes)';
        
        const formData = new FormData(this);
        formData.append('action', 'aga_ajax_generer_cas_pratique');
        
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

function aga_ajax_generer_cas_pratique() {
    if (!wp_verify_nonce($_POST['generateur_cas_pratique_nonce'], 'generateur_cas_pratique_action')) {
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

    $cas_pratique = sanitize_textarea_field($_POST['cas_pratique'] ?? '');
    $matiere = sanitize_text_field($_POST['matiere'] ?? '');
    
    $erreurs = aga_valider_donnees_formulaire([
        'cas_pratique' => $cas_pratique,
        'matiere' => $matiere
    ], 'cas_pratique');
    
    if (!empty($erreurs)) {
        wp_send_json_error(['message' => implode(' ', $erreurs)]);
        return;
    }
    
    $prompt = aga_construire_prompt_cas_pratique($matiere, $cas_pratique);
    $resultat = aga_appeler_openai($prompt, 'cas_pratique');
    
    if (isset($resultat['erreur'])) {
        wp_send_json_error(['message' => $resultat['erreur']]);
        return;
    }
    
    $post_id = aga_creer_cas_pratique($cas_pratique, $matiere, $resultat['succes'], 1);

    if ($post_id) {
        aga_incrementer_compteur($user_id, 1);
        wp_send_json_success(['url' => get_permalink($post_id)]);
    } else {
        wp_send_json_error(['message' => 'Erreur lors de l\'enregistrement']);
    }
}
add_action('wp_ajax_aga_ajax_generer_cas_pratique', 'aga_ajax_generer_cas_pratique');

// ============================================================================
// SHORTCODE HISTORIQUE
// ============================================================================

function aga_shortcode_historique_cas_pratiques($atts) {
    ob_start();
    aga_render_historique_cas_pratiques();
    return ob_get_clean();
}
add_shortcode('historique_cas_pratiques', 'aga_shortcode_historique_cas_pratiques');

function aga_render_historique_cas_pratiques() {
    $current_user_id = get_current_user_id();
    
    if (!$current_user_id) {
        echo '<div class="aga-alert aga-alert-error"><p>Vous devez être connecté.</p></div>';
        return;
    }
    
    $verification = aga_peut_generer($current_user_id, 1);
    $cas_pratiques_par_matiere = aga_obtenir_cas_pratiques_par_matiere($current_user_id);
    ?>

    <div class="aga-page">
        <nav class="aga-breadcrumb">
            <a href="<?php echo home_url('/generateur-cas-pratique/'); ?>" class="aga-breadcrumb-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Générateur
            </a>
        </nav>

        <header class="aga-header">
            <div class="aga-header-content">
                <h1 class="aga-title">Mes <span class="aga-title-highlight">cas pratiques</span></h1>
                <p class="aga-subtitle"><?php echo $verification['utilise']; ?>/<?php echo $verification['limite']; ?> crédits utilisés ce mois</p>
            </div>
            <div class="aga-header-actions">
                <a href="<?php echo home_url('/generateur-cas-pratique/'); ?>" class="aga-btn aga-btn-primary">
                    + Nouveau cas pratique
                </a>
            </div>
        </header>

        <?php if (empty($cas_pratiques_par_matiere)): ?>
            <div class="aga-empty-state">
                <p>Aucun cas pratique pour le moment.</p>
                <a href="<?php echo home_url('/generateur-cas-pratique/'); ?>" class="aga-btn aga-btn-outline">Créer mon premier cas pratique</a>
            </div>
        <?php else: ?>
            <?php foreach ($cas_pratiques_par_matiere as $matiere => $cas_pratiques): ?>
                <div class="aga-section">
                    <h2 class="aga-section-title"><?php echo esc_html($matiere); ?></h2>
                    <div class="aga-list">
                        <?php foreach ($cas_pratiques as $cas): 
                            $sujet = get_post_meta($cas->ID, '_aga_sujet_cas_pratique', true);
                            $date = get_post_meta($cas->ID, '_aga_date_generation', true);
                            $sujet_court = mb_strlen($sujet) > 80 ? mb_substr($sujet, 0, 77) . '...' : $sujet;
                        ?>
                            <div class="aga-list-item" data-id="<?php echo $cas->ID; ?>">
                                <div class="aga-list-info">
                                    <span class="aga-list-title"><?php echo esc_html($sujet_court ?: $cas->post_title); ?></span>
                                    <span class="aga-list-date"><?php echo $date ? date('d/m/Y', strtotime($date)) : ''; ?></span>
                                </div>
                                <div class="aga-list-actions">
                                    <a href="<?php echo get_permalink($cas->ID); ?>" class="aga-btn aga-btn-small">Voir</a>
                                    <button class="aga-btn aga-btn-small aga-btn-danger" onclick="deleteCasPratique(<?php echo $cas->ID; ?>, this)">Supprimer</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
    function deleteCasPratique(id, btn) {
        if (!confirm('Supprimer ce cas pratique ?')) return;
        
        btn.disabled = true;
        const formData = new FormData();
        formData.append('action', 'supprimer_cas_pratique');
        formData.append('cas_pratique_id', id);
        formData.append('nonce', '<?php echo wp_create_nonce('supprimer_cas_pratique_nonce'); ?>');
        
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