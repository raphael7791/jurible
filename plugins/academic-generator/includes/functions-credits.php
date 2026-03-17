<?php
/**
 * Gestion du système de crédits utilisateur
 * Système de solde simple : 1 crédit = 1 génération, pas de reset mensuel.
 */

if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// SYSTÈME DE CRÉDITS (SOLDE SIMPLE)
// ============================================================================

/**
 * Obtenir le solde de crédits d'un utilisateur.
 *
 * @param int $user_id
 * @return int Solde actuel (0 par défaut)
 */
function aga_get_credits($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return 0;
    }
    return max(0, (int) get_user_meta($user_id, 'aga_credits', true));
}

/**
 * Ajouter des crédits au solde d'un utilisateur.
 *
 * @param int $user_id
 * @param int $amount Nombre de crédits à ajouter (positif)
 * @return int Nouveau solde
 */
function aga_add_credits($user_id, $amount) {
    $amount = max(0, (int) $amount);
    $solde = aga_get_credits($user_id);
    $nouveau = $solde + $amount;
    update_user_meta($user_id, 'aga_credits', $nouveau);
    return $nouveau;
}

/**
 * Vérifier si l'utilisateur peut générer un document.
 *
 * @param int $user_id
 * @param int $cout Nombre de crédits nécessaires
 * @return array ['autorise' => bool, 'solde' => int]
 */
function aga_peut_generer($user_id = null, $cout = 1) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return array('autorise' => false, 'raison' => 'Utilisateur non connecté', 'solde' => 0);
    }

    $solde = aga_get_credits($user_id);

    if ($solde < $cout) {
        return array(
            'autorise' => false,
            'raison' => 'Crédits insuffisants',
            'solde' => $solde,
        );
    }

    return array(
        'autorise' => true,
        'solde' => $solde,
    );
}

/**
 * Consommer des crédits après une génération réussie.
 *
 * @param int $user_id
 * @param int $cout Nombre de crédits à déduire
 * @return int Nouveau solde
 */
function aga_consommer_credits($user_id, $cout = 1) {
    $solde = aga_get_credits($user_id);
    $nouveau = max(0, $solde - $cout);
    update_user_meta($user_id, 'aga_credits', $nouveau);
    return $nouveau;
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

    // Max 100 tentatives par heure
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
    $solde = aga_get_credits($user_id);
    $historique = aga_obtenir_historique_ajustements($user_id, 10);
    ?>

    <h2>Gestion des crédits - Générateur académique</h2>

    <table class="form-table" role="presentation">
        <tr>
            <th scope="row">Informations actuelles</th>
            <td>
                <div style="background: #f1f1f1; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                    <strong>Solde actuel :</strong> <?php echo $solde; ?> crédit<?php echo $solde !== 1 ? 's' : ''; ?><br>
                    <strong>Statut :</strong> <?php echo $solde > 0 ? '<span style="color: green;">Peut générer</span>' : '<span style="color: red;">Aucun crédit</span>'; ?>
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

        if (action === 'reset' && !confirm('Êtes-vous sûr de vouloir remettre le solde à zéro ?')) {
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
    $montant = absint($_POST['montant']);
    $reason = sanitize_textarea_field($_POST['reason']);
    $admin_id = get_current_user_id();

    // Validation
    if (!in_array($adjustment_type, ['add', 'remove', 'reset'])) {
        wp_send_json_error(array('message' => 'Type d\'ajustement invalide'));
        return;
    }

    $solde_actuel = aga_get_credits($user_id);
    $nouveau_solde = $solde_actuel;

    // Appliquer l'ajustement
    switch ($adjustment_type) {
        case 'add':
            $nouveau_solde = $solde_actuel + $montant;
            $message = "Ajouté {$montant} crédit(s). Nouveau solde: {$nouveau_solde}";
            break;

        case 'remove':
            $nouveau_solde = max(0, $solde_actuel - $montant);
            $message = "Retiré {$montant} crédit(s). Nouveau solde: {$nouveau_solde}";
            break;

        case 'reset':
            $nouveau_solde = 0;
            $message = "Solde remis à zéro.";
            break;
    }

    // Sauvegarder le nouveau solde
    update_user_meta($user_id, 'aga_credits', $nouveau_solde);

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
