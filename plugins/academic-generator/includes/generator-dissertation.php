<?php
/**
 * Générateur de dissertations - Design Jurible
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once dirname(__FILE__) . '/functions-common.php';
require_once dirname(__FILE__) . '/cpt-dissertation.php';

// ============================================================================
// SHORTCODE FORMULAIRE PRINCIPAL
// ============================================================================

function aga_shortcode_generateur_dissertation($atts) {
    ob_start();
    
    if (!defined('OPENAI_API_KEY') || empty(OPENAI_API_KEY)) {
        echo '<div class="aga-alert aga-alert-error">';
        echo '<p><strong>Configuration manquante</strong><br>Le générateur n\'est pas correctement configuré.</p>';
        echo '</div>';
        return ob_get_clean();
    }

    aga_render_formulaire_dissertation();
    return ob_get_clean();
}
add_shortcode('generateur_dissertation', 'aga_shortcode_generateur_dissertation');

// ============================================================================
// RENDU DU FORMULAIRE
// ============================================================================

function aga_render_formulaire_dissertation() {
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


        <?php if ($verification['autorise']): ?>

        <p style="text-align:right;margin:0 0 12px;"><span class="aga-credits-badge"><?php echo $verification['solde']; ?> crédit<?php echo $verification['solde'] !== 1 ? 's' : ''; ?></span></p>

        <!-- Formulaire -->
        <div class="aga-form-wrapper">
            <form id="dissertationForm" class="aga-form" method="POST">
                <?php wp_nonce_field('generateur_dissertation_action', 'generateur_dissertation_nonce'); ?>
                
                <div class="aga-form-card">
                    <!-- Ligne 1 : Matière + Type de génération -->
                    <div class="aga-form-row">
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
                        
                        <div class="aga-form-group">
                            <label class="aga-label">
                                Type de génération <span class="aga-required">*</span>
                            </label>
                            <select class="aga-select" name="type_generation" required>
                                <option value="" disabled selected>Choisissez...</option>
                                <option value="plan_detaille">📋 Plan détaillé</option>
                                <option value="dissertation_complete">📝 Dissertation complète</option>
                            </select>
                        </div>
                    </div>

                    <!-- Sujet -->
                    <div class="aga-form-group">
                        <label class="aga-label">
                            Sujet de dissertation <span class="aga-required">*</span>
                        </label>
                        <div class="aga-textarea-wrapper">
                            <input type="text" 
                                   class="aga-input" 
                                   name="sujet_dissertation"
                                   placeholder="Ex: La séparation des pouvoirs en France sous la Ve République" 
                                   required
                                   minlength="10"
                                   maxlength="500"
                                   style="padding-right: 4rem;">
                            <button type="submit" class="aga-submit-btn" title="Générer la dissertation" style="bottom: 0.5rem;">
                                <span style="color:#FFFFFF!important;font-size:20px;line-height:1;">↑</span>
                            </button>
                        </div>
                    </div>

                    <!-- Info -->
                    <p class="aga-help-text">
                        <span class="aga-help-icon">💡</span>
                        Le plan détaillé contient l'introduction + le plan structuré. La dissertation complète est entièrement rédigée.
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
                            <h4>Saisissez le sujet exact</h4>
                            <p>· Recopiez le sujet mot pour mot tel qu'il vous a été donné</p>
                            <p>· Ne modifiez pas la formulation</p>
                        </div>
                    </div>
                    
                    <div class="aga-step">
                        <span class="aga-step-number">2</span>
                        <div class="aga-step-content">
                            <h4>Choisissez le type de génération</h4>
                            <p>· <strong>Plan détaillé</strong> : introduction + plan structuré avec arguments</p>
                            <p>· <strong>Dissertation complète</strong> : version intégralement rédigée</p>
                        </div>
                    </div>
                    
                    <div class="aga-step">
                        <span class="aga-step-number">3</span>
                        <div class="aga-step-content">
                            <h4>Analysez la structure</h4>
                            <p>· Introduction : accroche, définitions, intérêts, problématique, annonce de plan</p>
                            <p>· Développement : I (A+B) et II (A+B) avec transitions</p>
                        </div>
                    </div>
                    
                    <div class="aga-step">
                        <span class="aga-step-number">4</span>
                        <div class="aga-step-content">
                            <h4>Personnalisez votre copie</h4>
                            <p>· Ajoutez les références de votre cours</p>
                            <p>· Adaptez le vocabulaire à celui de votre professeur</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php else: ?>
            <!-- Plus de crédits -->
            <?php
            $doit_afficher_modal = aga_doit_afficher_modal_avis($current_user_id);
            if ($doit_afficher_modal): aga_render_modal_avis(); endif;
            ?>

            <div class="aga-limit-card" <?php echo $doit_afficher_modal ? 'style="display:none;"' : ''; ?>>
                <div class="aga-limit-icon">🔒</div>
                <h3>Plus de <span class="highlight">crédits</span></h3>
                <p>Votre solde est de 0 crédit.</p>
                <div class="aga-limit-cta">
                    <p><strong>Achetez des crédits pour continuer :</strong></p>
                    <a href="https://jurible.com/credits-ia" class="aga-btn aga-btn-primary">
                        Acheter des crédits
                    </a>
                </div>
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
    document.getElementById('dissertationForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = document.querySelector('.aga-submit-btn');
        const feedback = document.getElementById('feedbackZone');
        const typeGen = this.querySelector('select[name="type_generation"]').value;
        
        if (btn.disabled) return;
        
        btn.disabled = true;
        btn.classList.add('loading');
        feedback.classList.add('show');
        
        const temps = typeGen === 'plan_detaille' ? '10-15' : '20-30';
        document.getElementById('feedbackText').textContent = `Génération en cours... (environ ${temps} secondes)`;
        
        const formData = new FormData(this);
        formData.append('action', 'aga_ajax_generer_dissertation');
        
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

function aga_ajax_generer_dissertation() {
    if (!wp_verify_nonce($_POST['generateur_dissertation_nonce'], 'generateur_dissertation_action')) {
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

    $sujet = sanitize_textarea_field($_POST['sujet_dissertation'] ?? '');
    $matiere = sanitize_text_field($_POST['matiere'] ?? '');
    $type_generation = sanitize_text_field($_POST['type_generation'] ?? '');
    
    $erreurs = aga_valider_donnees_formulaire([
        'sujet' => $sujet,
        'matiere' => $matiere,
        'type_generation' => $type_generation
    ], 'dissertation');
    
    if (!empty($erreurs)) {
        wp_send_json_error(['message' => implode(' ', $erreurs)]);
        return;
    }
    
    // Construire le prompt selon le type
    if ($type_generation === 'plan_detaille') {
        $prompt = aga_construire_prompt_plan_detaille($sujet, $matiere);
    } else {
        $prompt = aga_construire_prompt_dissertation_complete($sujet, $matiere);
    }
    
    $resultat = aga_appeler_openai($prompt, 'dissertation');
    
    if (isset($resultat['erreur'])) {
        wp_send_json_error(['message' => $resultat['erreur']]);
        return;
    }
    
    $post_id = aga_creer_dissertation($sujet, $matiere, $type_generation, $resultat['succes'], 1);

    if ($post_id) {
        aga_consommer_credits($user_id, 1);
        wp_send_json_success(['url' => get_permalink($post_id)]);
    } else {
        wp_send_json_error(['message' => 'Erreur lors de l\'enregistrement']);
    }
}
add_action('wp_ajax_aga_ajax_generer_dissertation', 'aga_ajax_generer_dissertation');

// ============================================================================
// SHORTCODE HISTORIQUE
// ============================================================================

function aga_shortcode_historique_dissertations($atts) {
    ob_start();
    aga_render_historique_dissertations();
    return ob_get_clean();
}
add_shortcode('historique_dissertations', 'aga_shortcode_historique_dissertations');

function aga_render_historique_dissertations() {
    $current_user_id = get_current_user_id();
    
    if (!$current_user_id) {
        echo '<div class="aga-alert aga-alert-error"><p>Vous devez être connecté.</p></div>';
        return;
    }
    
    $verification = aga_peut_generer($current_user_id, 1);
    $dissertations_par_matiere = aga_obtenir_dissertations_par_matiere($current_user_id);
    ?>

    <div class="aga-page">
        <nav class="aga-breadcrumb">
            <a href="<?php echo home_url('/generateur-dissertation/'); ?>" class="aga-breadcrumb-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Générateur
            </a>
        </nav>

        <header class="aga-header">
            <div class="aga-header-content">
                <span class="aga-credits-badge"><?php echo $verification['solde']; ?> crédit<?php echo $verification['solde'] !== 1 ? 's' : ''; ?></span>
            </div>
            <div class="aga-header-actions">
                <a href="<?php echo home_url('/generateur-dissertation/'); ?>" class="aga-btn aga-btn-primary">
                    + Nouvelle dissertation
                </a>
            </div>
        </header>

        <?php if (empty($dissertations_par_matiere)): ?>
            <div class="aga-empty-state">
                <p>Aucune dissertation pour le moment.</p>
                <a href="<?php echo home_url('/generateur-dissertation/'); ?>" class="aga-btn aga-btn-outline">Créer ma première dissertation</a>
            </div>
        <?php else: ?>
            <?php foreach ($dissertations_par_matiere as $matiere => $dissertations): ?>
                <div class="aga-section">
                    <h2 class="aga-section-title"><?php echo esc_html($matiere); ?></h2>
                    <div class="aga-list">
                        <?php foreach ($dissertations as $dissertation): 
                            $sujet = get_post_meta($dissertation->ID, '_aga_sujet', true);
                            $type_gen = get_post_meta($dissertation->ID, '_aga_type_generation', true);
                            $date = get_post_meta($dissertation->ID, '_aga_date_generation', true);
                            $sujet_court = mb_strlen($sujet) > 70 ? mb_substr($sujet, 0, 67) . '...' : $sujet;
                            $type_label = ($type_gen === 'plan_detaille') ? '📋' : '📝';
                        ?>
                            <div class="aga-list-item" data-id="<?php echo $dissertation->ID; ?>">
                                <div class="aga-list-info">
                                    <span class="aga-list-title"><?php echo $type_label; ?> <?php echo esc_html($sujet_court ?: $dissertation->post_title); ?></span>
                                    <span class="aga-list-date"><?php echo $date ? date('d/m/Y', strtotime($date)) : ''; ?></span>
                                </div>
                                <div class="aga-list-actions">
                                    <a href="<?php echo get_permalink($dissertation->ID); ?>" class="aga-btn aga-btn-small">Voir</a>
                                    <button class="aga-btn aga-btn-small aga-btn-danger" onclick="deleteDissertation(<?php echo $dissertation->ID; ?>, this)">Supprimer</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
    function deleteDissertation(id, btn) {
        if (!confirm('Supprimer cette dissertation ?')) return;
        
        btn.disabled = true;
        const formData = new FormData();
        formData.append('action', 'supprimer_dissertation');
        formData.append('dissertation_id', id);
        formData.append('nonce', '<?php echo wp_create_nonce('supprimer_dissertation_nonce'); ?>');
        
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