<?php
/**
 * Gestion du système de crédits utilisateur
 * Supporte les coûts variables (1 crédit ou 3 crédits selon le type de génération)
 */

if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// VÉRIFICATION ET OBTENTION DU TYPE DE COMPTE
// ============================================================================

/**
 * Obtenir le type de compte utilisateur (gratuit/premium)
 */
function aga_obtenir_type_compte($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    $user_id = (int) $user_id;
    if (!$user_id) {
        return false;
    }
    
    // Vérifier le rôle 'gfa_premium' (compatibilité avec plugin SureCart)
    $user = get_userdata($user_id);
    if ($user && in_array('gfa_premium', $user->roles)) {
        return 'premium';
    }
    
    return 'gratuit';
}

/**
 * Obtenir les limites selon le type de compte
 */
function aga_obtenir_limites($type_compte) {
    $limites = array(
        'gratuit' => 3,
        'premium' => 30
    );
    
    return isset($limites[$type_compte]) ? $limites[$type_compte] : 0;
}

// ============================================================================
// VÉRIFICATION DE LA POSSIBILITÉ DE GÉNÉRER
// ============================================================================

/**
 * Vérifier si l'utilisateur peut encore générer un document
 * 
 * @param int $user_id ID de l'utilisateur
 * @param int $cout_credits Nombre de crédits nécessaires (1 ou 3)
 * @return array ['autorise' => bool, 'raison' => string, 'utilise' => int, 'limite' => int]
 */
function aga_peut_generer($user_id = null, $cout_credits = 1) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return array('autorise' => false, 'raison' => 'Utilisateur non connecté');
    }

    // Obtenir le type de compte
    $type_compte = aga_obtenir_type_compte($user_id);
    if (!$type_compte) {
        return array('autorise' => false, 'raison' => 'Accès non autorisé');
    }

    // Obtenir la limite
    $limite = aga_obtenir_limites($type_compte);
    
    // Vérifier le compteur mensuel
    $compteur_actuel = aga_obtenir_compteur_mensuel($user_id);
    
    // Vérifier si suffisamment de crédits disponibles
    if (($compteur_actuel + $cout_credits) > $limite) {
        return array(
            'autorise' => false, 
            'raison' => 'Crédits insuffisants',
            'utilise' => $compteur_actuel,
            'limite' => $limite,
            'manquants' => ($compteur_actuel + $cout_credits) - $limite
        );
    }

    return array(
        'autorise' => true,
        'utilise' => $compteur_actuel,
        'limite' => $limite,
        'disponibles' => $limite - $compteur_actuel
    );
}

// ============================================================================
// GESTION DU COMPTEUR MENSUEL
// ============================================================================

/**
 * Obtenir le compteur mensuel d'un utilisateur
 */
function aga_obtenir_compteur_mensuel($user_id) {
    $mois_actuel = date('Y-m');
    $compteur_mois = get_user_meta($user_id, 'aga_compteur_mois', true);
    $dernier_mois = get_user_meta($user_id, 'aga_dernier_mois', true);

    // Reset si on change de mois
    if ($dernier_mois !== $mois_actuel) {
        update_user_meta($user_id, 'aga_compteur_mois', 0);
        update_user_meta($user_id, 'aga_dernier_mois', $mois_actuel);
        return 0;
    }

    return (int) $compteur_mois;
}

/**
 * Incrémenter le compteur mensuel
 * 
 * @param int $user_id ID de l'utilisateur
 * @param int $credits Nombre de crédits à déduire (1 ou 3)
 * @return int Nouveau compteur
 */
function aga_incrementer_compteur($user_id, $credits = 1) {
    $compteur_actuel = aga_obtenir_compteur_mensuel($user_id);
    $nouveau_compteur = $compteur_actuel + $credits;
    
    update_user_meta($user_id, 'aga_compteur_mois', $nouveau_compteur);
    
    return $nouveau_compteur;
}

// ============================================================================
// PROTECTION ANTI-ABUS
// ============================================================================

/**
 * Vérifier les limites de taux (rate limiting)
 * 
 * @param int $user_id ID de l'utilisateur
 * @return array ['autorise' => bool, 'raison' => string]
 */
function aga_verifier_rate_limit($user_id) {
    $now = time();
    $hour_key = 'aga_attempts_' . date('Y-m-d-H', $now);
    $last_generation = get_user_meta($user_id, 'aga_last_generation', true);
    $hourly_attempts = get_user_meta($user_id, $hour_key, true) ?: 0;

    // Max 10 générations par heure
    if ($hourly_attempts >= 100) {
        return array(
            'autorise' => false,
            'raison' => 'Limite horaire atteinte (10 max/heure). Réessayez dans ' . (60 - date('i')) . ' minutes.'
        );
    }

    // Min 10 secondes entre deux générations
    if ($last_generation && ($now - $last_generation) < 10) {
        return array(
            'autorise' => false,
            'raison' => 'Veuillez attendre 10 secondes entre deux générations'
        );
    }

    return array('autorise' => true);
}

/**
 * Enregistrer une tentative de génération
 */
function aga_enregistrer_tentative($user_id) {
    $now = time();
    $hour_key = 'aga_attempts_' . date('Y-m-d-H', $now);
    $hourly_attempts = get_user_meta($user_id, $hour_key, true) ?: 0;
    
    // Incrémenter le compteur horaire
    update_user_meta($user_id, $hour_key, $hourly_attempts + 1);
    
    // Marquer cette tentative
    update_user_meta($user_id, 'aga_last_generation', $now);
    
    // Nettoyer les anciens compteurs (optionnel)
    delete_user_meta($user_id, 'aga_attempts_' . date('Y-m-d-H', $now - 3600));
}

// ============================================================================
// GESTION MANUELLE DES CRÉDITS (ADMIN)
// ============================================================================

/**
 * Ajouter la section de gestion des crédits dans le profil utilisateur
 */
function aga_ajouter_section_credits_admin($user) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $user_id = $user->ID;
    $type_compte = aga_obtenir_type_compte($user_id);
    $verification = aga_peut_generer($user_id);
    $limite = aga_obtenir_limites($type_compte);
    $compteur_actuel = aga_obtenir_compteur_mensuel($user_id);
    $historique = aga_obtenir_historique_ajustements($user_id, 10);
    ?>
    
    <h2>Gestion des crédits - Générateur académique</h2>
    
    <table class="form-table" role="presentation">
        <tr>
            <th scope="row">Informations actuelles</th>
            <td>
                <div style="background: #f1f1f1; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                    <strong>Type de compte :</strong> <?php echo esc_html(ucfirst($type_compte)); ?><br>
                    <strong>Crédits utilisés :</strong> <?php echo $compteur_actuel; ?>/<?php echo $limite; ?> ce mois<br>
                    <strong>Statut :</strong> <?php echo $verification['autorise'] ? '<span style="color: green;">Autorisé</span>' : '<span style="color: red;">Limite atteinte</span>'; ?>
                </div>
            </td>
        </tr>
        
        <tr>
            <th scope="row">Actions rapides</th>
            <td>
                <button type="button" class="button" onclick="agaAjusterCredits(<?php echo $user_id; ?>, 'reset')">
                    Remettre à zéro
                </button>
                <button type="button" class="button" onclick="agaAjusterCredits(<?php echo $user_id; ?>, 'add', 5)">
                    +5 crédits
                </button>
                <button type="button" class="button" onclick="agaAjusterCredits(<?php echo $user_id; ?>, 'remove', 5)">
                    -5 crédits
                </button>
            </td>
        </tr>
        
        <tr>
            <th scope="row">Ajustement personnalisé</th>
            <td>
                <input type="number" id="aga-custom-amount" min="-100" max="100" step="1" value="0" style="width: 80px;">
                <button type="button" class="button" onclick="agaAjusterCreditsCustom(<?php echo $user_id; ?>)">
                    Appliquer
                </button>
                <br><small>Nombre positif pour ajouter, négatif pour retirer</small>
            </td>
        </tr>
        
        <tr>
            <th scope="row">Raison (obligatoire)</th>
            <td>
                <textarea id="aga-reason" rows="3" cols="50" placeholder="Ex: Compensation pour bug technique, geste commercial client VIP..."></textarea>
            </td>
        </tr>
    </table>
    
    <?php if (!empty($historique)): ?>
    <h3>Historique des ajustements</h3>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 15%;">Date</th>
                <th style="width: 15%;">Action</th>
                <th style="width: 10%;">Montant</th>
                <th style="width: 15%;">Admin</th>
                <th style="width: 45%;">Raison</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($historique as $entry): ?>
            <tr>
                <td><?php echo date('d/m/Y H:i', strtotime($entry['date'])); ?></td>
                <td>
                    <?php 
                    $class = '';
                    if ($entry['type'] === 'add') $class = 'color: green;';
                    elseif ($entry['type'] === 'remove') $class = 'color: red;';
                    elseif ($entry['type'] === 'reset') $class = 'color: orange;';
                    ?>
                    <span style="<?php echo $class; ?>">
                        <?php echo esc_html(ucfirst($entry['type'])); ?>
                    </span>
                </td>
                <td><?php echo $entry['montant']; ?></td>
                <td><?php echo esc_html($entry['admin_name']); ?></td>
                <td><?php echo esc_html($entry['raison']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    
    <div id="aga-result-message" style="margin-top: 15px;"></div>
    
    <script>
    function agaAjusterCredits(userId, action, montant = 0) {
        const reason = document.getElementById('aga-reason').value.trim();
        if (!reason) {
            alert('Veuillez indiquer une raison pour cet ajustement.');
            return;
        }
        
        if (action === 'reset' && !confirm('Êtes-vous sûr de vouloir remettre le compteur à zéro ?')) {
            return;
        }
        
        agaExecuterAjustement(userId, action, montant, reason);
    }
    
    function agaAjusterCreditsCustom(userId) {
        const montant = parseInt(document.getElementById('aga-custom-amount').value);
        const reason = document.getElementById('aga-reason').value.trim();
        
        if (!reason) {
            alert('Veuillez indiquer une raison pour cet ajustement.');
            return;
        }
        
        if (montant === 0) {
            alert('Veuillez indiquer un montant différent de zéro.');
            return;
        }
        
        const action = montant > 0 ? 'add' : 'remove';
        const montantAbs = Math.abs(montant);
        
        if (!confirm(`Êtes-vous sûr de vouloir ${action === 'add' ? 'ajouter' : 'retirer'} ${montantAbs} crédit(s) ?`)) {
            return;
        }
        
        agaExecuterAjustement(userId, action, montantAbs, reason);
    }
    
    function agaExecuterAjustement(userId, action, montant, reason) {
        const resultDiv = document.getElementById('aga-result-message');
        resultDiv.innerHTML = '<p>Traitement en cours...</p>';
        
        const formData = new FormData();
        formData.append('action', 'aga_ajuster_credits');
        formData.append('user_id', userId);
        formData.append('adjustment_type', action);
        formData.append('montant', montant);
        formData.append('reason', reason);
        formData.append('nonce', '<?php echo wp_create_nonce('aga_ajuster_credits'); ?>');
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = '<div class="notice notice-success"><p>' + data.data.message + '</p></div>';
                setTimeout(() => location.reload(), 2000);
            } else {
                resultDiv.innerHTML = '<div class="notice notice-error"><p>Erreur: ' + data.data.message + '</p></div>';
            }
        })
        .catch(error => {
            resultDiv.innerHTML = '<div class="notice notice-error"><p>Erreur de connexion</p></div>';
        });
        
        document.getElementById('aga-reason').value = '';
        document.getElementById('aga-custom-amount').value = '0';
    }
    </script>
    
    <?php
}
add_action('show_user_profile', 'aga_ajouter_section_credits_admin');
add_action('edit_user_profile', 'aga_ajouter_section_credits_admin');

/**
 * Handler AJAX pour ajuster les crédits
 */
function aga_ajax_ajuster_credits() {
    // Vérifications de sécurité
    if (!wp_verify_nonce($_POST['nonce'], 'aga_ajuster_credits')) {
        wp_send_json_error(array('message' => 'Erreur de sécurité'));
        return;
    }
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permissions insuffisantes'));
        return;
    }
    
    if (!isset($_POST['user_id']) || !isset($_POST['adjustment_type']) || !isset($_POST['reason'])) {
        wp_send_json_error(array('message' => 'Données manquantes'));
        return;
    }
    
    $user_id = (int) $_POST['user_id'];
    
    // Vérifier que l'utilisateur existe
    if (!get_userdata($user_id)) {
        wp_send_json_error(array('message' => 'Utilisateur inexistant'));
        return;
    }
    
    $adjustment_type = sanitize_text_field($_POST['adjustment_type']);
    $montant = intval($_POST['montant']);
    $reason = sanitize_textarea_field($_POST['reason']);
    $admin_id = get_current_user_id();
    
    // Validation
    if (!in_array($adjustment_type, ['add', 'remove', 'reset'])) {
        wp_send_json_error(array('message' => 'Type d\'ajustement invalide'));
        return;
    }
    
    // Obtenir le compteur actuel
    $compteur_actuel = aga_obtenir_compteur_mensuel($user_id);
    $nouveau_compteur = $compteur_actuel;
    
    // Appliquer l'ajustement
    switch ($adjustment_type) {
        case 'add':
            $nouveau_compteur = max(0, $compteur_actuel - $montant);
            $message = "Ajouté {$montant} crédit(s). Nouveau total: " . (aga_obtenir_limites(aga_obtenir_type_compte($user_id)) - $nouveau_compteur) . " crédits disponibles";
            break;
            
        case 'remove':
            $limite = aga_obtenir_limites(aga_obtenir_type_compte($user_id));
            $nouveau_compteur = min($limite, $compteur_actuel + $montant);
            $message = "Retiré {$montant} crédit(s). Nouveau total: " . ($limite - $nouveau_compteur) . " crédits disponibles";
            break;
            
        case 'reset':
            $nouveau_compteur = 0;
            $limite = aga_obtenir_limites(aga_obtenir_type_compte($user_id));
            $message = "Compteur remis à zéro. Total disponible: {$limite} crédits";
            break;
    }
    
    // Sauvegarder le nouveau compteur
    update_user_meta($user_id, 'aga_compteur_mois', $nouveau_compteur);
    
    // Enregistrer dans l'historique
    aga_ajouter_historique_ajustement($user_id, $adjustment_type, $montant, $reason, $admin_id);
    
    wp_send_json_success(array('message' => $message));
}
add_action('wp_ajax_aga_ajuster_credits', 'aga_ajax_ajuster_credits');

/**
 * Obtenir l'historique des ajustements
 */
function aga_obtenir_historique_ajustements($user_id, $limite = 10) {
    $historique = get_user_meta($user_id, 'aga_historique_ajustements', true);
    if (!is_array($historique)) {
        return array();
    }
    
    // Trier par date décroissante
    usort($historique, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    return array_slice($historique, 0, $limite);
}

/**
 * Ajouter une entrée à l'historique
 */
function aga_ajouter_historique_ajustement($user_id, $type, $montant, $raison, $admin_id) {
    $historique = get_user_meta($user_id, 'aga_historique_ajustements', true);
    if (!is_array($historique)) {
        $historique = array();
    }
    
    $admin_user = get_userdata($admin_id);
    
    $nouvelle_entree = array(
        'date' => current_time('mysql'),
        'type' => $type,
        'montant' => $montant,
        'raison' => $raison,
        'admin_id' => $admin_id,
        'admin_name' => $admin_user ? $admin_user->display_name : 'Admin'
    );
    
    // Ajouter en première position
    array_unshift($historique, $nouvelle_entree);
    
    // Garder seulement les 50 dernières entrées
    $historique = array_slice($historique, 0, 50);
    
    update_user_meta($user_id, 'aga_historique_ajustements', $historique);
}

/**
 * Vérifier si l'utilisateur a déjà donné un avis
 */
function aga_utilisateur_a_deja_avis($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'aga_avis_utilisateurs';
    
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND (statut = 'validated' OR statut = 'pending')",
        $user_id
    ));
    
    return ($count > 0);
}