<?php
/**
 * Interface de publication directe vers le catalogue du site principal
 * Tableau de bord avec détection des doublons et édition inline
 */

if (!defined('ABSPATH')) {
    exit;
}

// URL de l'API du site principal
define('AGA_SITE_PRINCIPAL_API', 'https://aideauxtd.com/wp-json/gfa/v1');

// ============================================================================
// EXTRACTION DU NUMÉRO DE POURVOI
// ============================================================================

/**
 * Extraire le numéro de pourvoi depuis les références
 * Formats supportés : 21-20.345, 21-20345, 2120345, n°21-20.345
 */
function aga_extraire_numero_pourvoi($references) {
    // Pattern principal : XX-XX.XXX ou XX-XXXXX
    if (preg_match('/(\d{2})[\s\-\.]?(\d{2})[\s\.\-]?(\d{3,5})\b/', $references, $matches)) {
        // Normaliser au format XX-XX.XXX
        $partie1 = $matches[1];
        $partie2 = $matches[2];
        $partie3 = $matches[3];
        
        // Si partie3 a plus de 3 chiffres, c'est peut-être collé
        if (strlen($partie3) > 3) {
            return $partie1 . '-' . $partie2 . '.' . $partie3;
        }
        
        return $partie1 . '-' . $partie2 . '.' . $partie3;
    }
    
    return null;
}

// ============================================================================
// VÉRIFICATION DES DOUBLONS
// ============================================================================

/**
 * Vérifier si un numéro de pourvoi existe déjà sur le site principal
 */
function aga_verifier_doublon_site_principal($numero_pourvoi) {
    if (empty($numero_pourvoi)) {
        return array('existe' => false);
    }
    
    $api_url = AGA_SITE_PRINCIPAL_API . '/verifier-doublon';
    
    $response = wp_remote_get(add_query_arg(array(
        'pourvoi' => $numero_pourvoi,
        'api_key' => defined('AGA_SYNC_API_KEY') ? AGA_SYNC_API_KEY : ''
    ), $api_url), array(
        'timeout' => 10
    ));
    
    if (is_wp_error($response)) {
        return array('existe' => false, 'erreur' => $response->get_error_message());
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    return $body ?: array('existe' => false);
}

/**
 * Vérifier les doublons locaux (autres fiches sur le sous-domaine avec même pourvoi)
 */
function aga_verifier_doublon_local($numero_pourvoi, $exclure_id = 0) {
    if (empty($numero_pourvoi)) {
        return array('existe' => false);
    }
    
    global $wpdb;
    
    // Chercher dans les références
    $query = $wpdb->prepare(
        "SELECT p.ID, pm.meta_value as refs
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'fiche_arret'
        AND p.post_status = 'publish'
        AND p.ID != %d
        AND pm.meta_key IN ('_aga_references', '_aga_references_normalized')
        AND pm.meta_value LIKE %s",
        $exclure_id,
        '%' . $wpdb->esc_like($numero_pourvoi) . '%'
    );
    
    $resultats = $wpdb->get_results($query);
    
    if (!empty($resultats)) {
        return array(
            'existe' => true,
            'fiche_id' => $resultats[0]->ID,
            'type' => 'local'
        );
    }
    
    return array('existe' => false);
}

// ============================================================================
// PUBLICATION VERS LE SITE PRINCIPAL
// ============================================================================

/**
 * Publier une fiche sur le site principal via API
 */
function aga_publier_sur_site_principal($fiche_id) {
    $fiche = get_post($fiche_id);
    
    if (!$fiche || $fiche->post_type !== 'fiche_arret') {
        return array('success' => false, 'message' => 'Fiche introuvable');
    }
    
    // Récupérer les données
    $references = get_post_meta($fiche_id, '_aga_references_normalized', true);
    if (empty($references)) {
        $references = get_post_meta($fiche_id, '_aga_references', true);
    }
    
    $data = array(
        'api_key' => defined('AGA_SYNC_API_KEY') ? AGA_SYNC_API_KEY : '',
        'source_id' => $fiche_id,
        'references' => $references,
        'matiere' => get_post_meta($fiche_id, '_aga_matiere', true),
        'contenu' => $fiche->post_content
    );
    
    $response = wp_remote_post(AGA_SITE_PRINCIPAL_API . '/publier-fiche', array(
        'timeout' => 30,
        'headers' => array('Content-Type' => 'application/json'),
        'body' => json_encode($data)
    ));
    
    if (is_wp_error($response)) {
        return array('success' => false, 'message' => $response->get_error_message());
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    $http_code = wp_remote_retrieve_response_code($response);
    
    if ($http_code === 200 && isset($body['success']) && $body['success']) {
        // Mettre à jour le statut local
        update_post_meta($fiche_id, '_aga_statut_catalogue', 'publiee');
        update_post_meta($fiche_id, '_aga_date_publication', current_time('mysql'));
        update_post_meta($fiche_id, '_aga_id_site_principal', $body['post_id']);
        
        return array(
            'success' => true,
            'post_id' => $body['post_id'],
            'url' => $body['url']
        );
    }
    
    return array(
        'success' => false,
        'message' => isset($body['message']) ? $body['message'] : 'Erreur inconnue'
    );
}

// ============================================================================
// HANDLERS AJAX
// ============================================================================

/**
 * AJAX : Mettre à jour les références d'une fiche
 */
function aga_ajax_update_references() {
    check_ajax_referer('aga_publication_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Accès refusé'));
    }
    
    $fiche_id = intval($_POST['fiche_id']);
    $nouvelles_refs = sanitize_text_field($_POST['references']);
    
    if (empty($nouvelles_refs)) {
        wp_send_json_error(array('message' => 'Références vides'));
    }
    
    // Mettre à jour
    update_post_meta($fiche_id, '_aga_references_normalized', $nouvelles_refs);
    
    // Recalculer le numéro de pourvoi
    $pourvoi = aga_extraire_numero_pourvoi($nouvelles_refs);
    
    wp_send_json_success(array(
        'message' => 'Références mises à jour',
        'pourvoi' => $pourvoi
    ));
}
add_action('wp_ajax_aga_update_references', 'aga_ajax_update_references');

/**
 * AJAX : Publier une fiche
 */
function aga_ajax_publier_fiche_catalogue() {
    check_ajax_referer('aga_publication_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Accès refusé'));
    }
    
    $fiche_id = intval($_POST['fiche_id']);
    
    $resultat = aga_publier_sur_site_principal($fiche_id);
    
    if ($resultat['success']) {
        wp_send_json_success($resultat);
    } else {
        wp_send_json_error($resultat);
    }
}
add_action('wp_ajax_aga_publier_fiche_catalogue', 'aga_ajax_publier_fiche_catalogue');

/**
 * AJAX : Vérifier doublon pour une fiche
 */
function aga_ajax_verifier_doublon() {
    check_ajax_referer('aga_publication_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Accès refusé'));
    }
    
    $pourvoi = sanitize_text_field($_POST['pourvoi']);
    $fiche_id = intval($_POST['fiche_id']);
    
    // Vérifier sur le site principal
    $doublon_principal = aga_verifier_doublon_site_principal($pourvoi);
    
    // Vérifier localement
    $doublon_local = aga_verifier_doublon_local($pourvoi, $fiche_id);
    
    wp_send_json_success(array(
        'site_principal' => $doublon_principal,
        'local' => $doublon_local
    ));
}
add_action('wp_ajax_aga_verifier_doublon', 'aga_ajax_verifier_doublon');

/**
 * AJAX : Rejeter une fiche
 */
function aga_ajax_rejeter_fiche() {
    check_ajax_referer('aga_publication_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Accès refusé'));
    }
    
    $fiche_id = intval($_POST['fiche_id']);
    
    update_post_meta($fiche_id, '_aga_statut_catalogue', 'rejete');
    
    wp_send_json_success(array('message' => 'Fiche rejetée'));
}
add_action('wp_ajax_aga_rejeter_fiche', 'aga_ajax_rejeter_fiche');

// ============================================================================
// PAGE ADMIN
// ============================================================================

/**
 * Extraire les références depuis le titre du post
 * Format attendu : "Fiche d'arrêt générée (Civ. 1ère, 12 février 2020, n° 19-10.088)"
 */
function aga_extraire_refs_depuis_titre($titre) {
    // Chercher le contenu entre parenthèses
    if (preg_match('/\(([^)]+)\)\s*$/', $titre, $matches)) {
        return trim($matches[1]);
    }
    // Si pas de parenthèses, retourner le titre nettoyé
    $titre_nettoye = str_replace(array('Fiche d\'arrêt générée', 'Fiche d\'arrêt'), '', $titre);
    return trim($titre_nettoye, ' -:');
}

/**
 * Afficher la page de publication catalogue
 */
function aga_page_publication_catalogue() {
    // Récupérer les fiches publiables
    $fiches = get_posts(array(
        'post_type' => 'fiche_arret',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_aga_statut_catalogue',
                'value' => 'publiable',
                'compare' => '='
            )
        ),
        'orderby' => 'date',
        'order' => 'DESC'
    ));
    
    // Pré-calculer les doublons
    $doublons_detectes = 0;
    $fiches_data = array();
    
    foreach ($fiches as $fiche) {
        $refs = get_post_meta($fiche->ID, '_aga_references_normalized', true);
        if (empty($refs)) {
            $refs = get_post_meta($fiche->ID, '_aga_references', true);
        }
        // Si toujours vide, extraire depuis le titre
        if (empty($refs)) {
            $refs = aga_extraire_refs_depuis_titre($fiche->post_title);
            // Sauvegarder pour la prochaine fois
            if (!empty($refs)) {
                update_post_meta($fiche->ID, '_aga_references', $refs);
            }
        }
        
        $pourvoi = aga_extraire_numero_pourvoi($refs);
        $doublon_info = null;
        
        if ($pourvoi) {
            // Vérifier doublon site principal (en cache pour performance)
            $cache_key = 'aga_doublon_' . md5($pourvoi);
            $doublon_info = get_transient($cache_key);
            
            if ($doublon_info === false) {
                $doublon_info = aga_verifier_doublon_site_principal($pourvoi);
                set_transient($cache_key, $doublon_info, 300); // Cache 5 min
            }
            
            if (isset($doublon_info['existe']) && $doublon_info['existe']) {
                $doublons_detectes++;
            }
        }
        
        // Récupérer la matière (compatible ancien préfixe _gfa_ et nouveau _aga_)
        $matiere = get_post_meta($fiche->ID, '_aga_matiere', true);
        if (empty($matiere)) {
            $matiere = get_post_meta($fiche->ID, '_gfa_matiere', true);
        }
        
        // Récupérer la date (compatible ancien préfixe)
        $date = get_post_meta($fiche->ID, '_aga_date_generation', true);
        if (empty($date)) {
            $date = get_post_meta($fiche->ID, '_gfa_date_generation', true);
        }
        
        $fiches_data[] = array(
            'fiche' => $fiche,
            'refs' => $refs,
            'pourvoi' => $pourvoi,
            'doublon' => $doublon_info,
            'matiere' => $matiere,
            'date' => $date
        );
    }
    
    $nonce = wp_create_nonce('aga_publication_nonce');
    ?>
    <div class="wrap aga-publication-wrap">
        <h1>📤 Publication Catalogue</h1>
        
        <!-- Stats rapides -->
        <div class="aga-stats-bar">
            <div class="stat-item">
                <span class="stat-number"><?php echo count($fiches); ?></span>
                <span class="stat-label">fiches en attente</span>
            </div>
            <div class="stat-item <?php echo $doublons_detectes > 0 ? 'warning' : ''; ?>">
                <span class="stat-number"><?php echo $doublons_detectes; ?></span>
                <span class="stat-label">doublons potentiels</span>
            </div>
            <button type="button" class="button button-secondary" onclick="agaRefreshDoublons()">
                🔄 Revérifier les doublons
            </button>
        </div>
        
        <?php if (empty($fiches)): ?>
            <div class="aga-empty-state">
                <p>🎉 Aucune fiche en attente de publication !</p>
            </div>
        <?php else: ?>
            
            <!-- Tableau principal -->
            <table class="wp-list-table widefat fixed striped aga-publication-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">État</th>
                        <th style="width: 40%;">Références (cliquez pour modifier)</th>
                        <th style="width: 15%;">N° Pourvoi</th>
                        <th style="width: 15%;">Matière</th>
                        <th style="width: 10%;">Date</th>
                        <th style="width: 20%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fiches_data as $data): 
                        $fiche = $data['fiche'];
                        $has_doublon = isset($data['doublon']['existe']) && $data['doublon']['existe'];
                        $row_class = $has_doublon ? 'has-doublon' : '';
                    ?>
                        <tr id="fiche-row-<?php echo $fiche->ID; ?>" class="<?php echo $row_class; ?>" data-fiche-id="<?php echo $fiche->ID; ?>">
                            <td class="column-status">
                                <?php if ($has_doublon): ?>
                                    <span class="status-badge doublon" title="Doublon détecté sur le site principal">⚠️</span>
                                <?php else: ?>
                                    <span class="status-badge ok" title="Aucun doublon détecté">✅</span>
                                <?php endif; ?>
                            </td>
                            <td class="column-refs">
                                <div class="refs-display" onclick="agaEditRefs(<?php echo $fiche->ID; ?>)">
                                    <span class="refs-text" id="refs-text-<?php echo $fiche->ID; ?>"><?php echo esc_html($data['refs']); ?></span>
                                    <span class="edit-icon">✏️</span>
                                </div>
                                <div class="refs-edit" id="refs-edit-<?php echo $fiche->ID; ?>" style="display:none;">
                                    <input type="text" class="refs-input" id="refs-input-<?php echo $fiche->ID; ?>" value="<?php echo esc_attr($data['refs']); ?>">
                                    <button type="button" class="button button-small" onclick="agaSaveRefs(<?php echo $fiche->ID; ?>)">✓</button>
                                    <button type="button" class="button button-small" onclick="agaCancelEditRefs(<?php echo $fiche->ID; ?>)">✗</button>
                                </div>
                                <?php if ($has_doublon && isset($data['doublon']['url'])): ?>
                                    <div class="doublon-warning">
                                        ⚠️ Existe déjà : <a href="<?php echo esc_url($data['doublon']['url']); ?>" target="_blank">Voir sur le blog</a>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="column-pourvoi">
                                <code id="pourvoi-<?php echo $fiche->ID; ?>"><?php echo $data['pourvoi'] ?: '—'; ?></code>
                            </td>
                            <td class="column-matiere">
                                <?php echo esc_html(aga_formater_matiere($data['matiere'])); ?>
                            </td>
                            <td class="column-date">
                                <?php echo $data['date'] ? date('d/m/Y', strtotime($data['date'])) : '—'; ?>
                            </td>
                            <td class="column-actions">
                                <a href="<?php echo get_permalink($fiche->ID); ?>" target="_blank" class="button button-small">
                                    👁️ Voir
                                </a>
                                <?php if ($has_doublon): ?>
                                    <button type="button" class="button button-primary button-small btn-publish" disabled title="Résolvez le doublon d'abord">
                                        🚀 Publier
                                    </button>
                                <?php elseif (empty($data['pourvoi'])): ?>
                                    <button type="button" class="button button-small btn-confirm" onclick="agaConfirmerSansPourvoi(<?php echo $fiche->ID; ?>, this)" title="Pas de n° de pourvoi détecté - Cliquez pour confirmer que ce n'est pas un doublon">
                                        ⚠️ Confirmer
                                    </button>
                                    <button type="button" class="button button-primary button-small btn-publish" onclick="agaPublier(<?php echo $fiche->ID; ?>)" style="display:none;" id="btn-publish-<?php echo $fiche->ID; ?>">
                                        🚀 Publier
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="button button-primary button-small btn-publish" onclick="agaPublier(<?php echo $fiche->ID; ?>)">
                                        🚀 Publier
                                    </button>
                                <?php endif; ?>
                                <button type="button" class="button button-small btn-reject" onclick="agaRejeter(<?php echo $fiche->ID; ?>)">
                                    ❌
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
        <?php endif; ?>
    </div>
    
    <style>
    .aga-publication-wrap {
        max-width: 1400px;
    }
    
    .aga-stats-bar {
        display: flex;
        gap: 30px;
        align-items: center;
        background: #fff;
        padding: 20px;
        border: 1px solid #c3c4c7;
        border-radius: 8px;
        margin: 20px 0;
    }
    
    .stat-item {
        display: flex;
        flex-direction: column;
    }
    
    .stat-item.warning .stat-number {
        color: #d63638;
    }
    
    .stat-number {
        font-size: 28px;
        font-weight: 700;
        color: #1d2327;
    }
    
    .stat-label {
        font-size: 13px;
        color: #646970;
    }
    
    .aga-empty-state {
        background: #d1e7dd;
        padding: 40px;
        text-align: center;
        border-radius: 8px;
        margin-top: 20px;
    }
    
    .aga-publication-table {
        margin-top: 20px;
    }
    
    .aga-publication-table td {
        vertical-align: middle;
    }
    
    .aga-publication-table tr.has-doublon {
        background: #fcf0f0 !important;
    }
    
    .status-badge {
        font-size: 18px;
        cursor: help;
    }
    
    .refs-display {
        cursor: pointer;
        padding: 8px;
        border-radius: 4px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .refs-display:hover {
        background: #f0f0f1;
    }
    
    .refs-display .edit-icon {
        opacity: 0;
        transition: opacity 0.2s;
    }
    
    .refs-display:hover .edit-icon {
        opacity: 1;
    }
    
    .refs-edit {
        display: flex;
        gap: 5px;
        align-items: center;
    }
    
    .refs-input {
        flex: 1;
        padding: 6px 10px;
        font-size: 13px;
    }
    
    .doublon-warning {
        margin-top: 8px;
        padding: 6px 10px;
        background: #fff3cd;
        border-radius: 4px;
        font-size: 12px;
        color: #856404;
    }
    
    .column-pourvoi code {
        background: #f0f0f1;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 12px;
    }
    
    .column-actions {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }
    
    .btn-confirm {
        background: #fff3cd !important;
        border-color: #ffc107 !important;
        color: #856404 !important;
    }
    
    .btn-confirm:hover {
        background: #ffe69c !important;
    }
    
    .btn-publish:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .btn-reject {
        color: #d63638 !important;
    }
    
    /* Animation de suppression */
    @keyframes fadeOutRow {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(-20px); }
    }
    
    .fiche-removing {
        animation: fadeOutRow 0.3s ease forwards;
    }
    </style>
    
    <script>
    const agaNonce = '<?php echo $nonce; ?>';
    const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
    
    // Confirmer sans numéro de pourvoi
    function agaConfirmerSansPourvoi(ficheId, btn) {
        if (!confirm('Aucun numéro de pourvoi détecté.\n\nAvez-vous vérifié que cet arrêt n\'est pas déjà publié sur le blog ?')) {
            return;
        }
        
        // Masquer le bouton Confirmer, afficher le bouton Publier
        btn.style.display = 'none';
        document.getElementById('btn-publish-' + ficheId).style.display = 'inline-block';
    }
    
    // Édition inline des références
    function agaEditRefs(ficheId) {
        document.getElementById('refs-text-' + ficheId).parentElement.style.display = 'none';
        document.getElementById('refs-edit-' + ficheId).style.display = 'flex';
        document.getElementById('refs-input-' + ficheId).focus();
    }
    
    function agaCancelEditRefs(ficheId) {
        document.getElementById('refs-edit-' + ficheId).style.display = 'none';
        document.getElementById('refs-text-' + ficheId).parentElement.style.display = 'flex';
    }
    
    function agaSaveRefs(ficheId) {
        const input = document.getElementById('refs-input-' + ficheId);
        const newRefs = input.value.trim();
        
        if (!newRefs) {
            alert('Les références ne peuvent pas être vides');
            return;
        }
        
        input.disabled = true;
        
        fetch(ajaxUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'aga_update_references',
                nonce: agaNonce,
                fiche_id: ficheId,
                references: newRefs
            })
        })
        .then(r => r.json())
        .then(data => {
            input.disabled = false;
            if (data.success) {
                document.getElementById('refs-text-' + ficheId).textContent = newRefs;
                document.getElementById('pourvoi-' + ficheId).textContent = data.data.pourvoi || '—';
                agaCancelEditRefs(ficheId);
            } else {
                alert('Erreur : ' + (data.data?.message || 'Erreur inconnue'));
            }
        })
        .catch(err => {
            input.disabled = false;
            alert('Erreur réseau');
        });
    }
    
    // Publication
    function agaPublier(ficheId) {
        if (!confirm('Publier cette fiche sur le blog principal ?')) return;
        
        const row = document.getElementById('fiche-row-' + ficheId);
        const btn = row.querySelector('.btn-publish');
        btn.disabled = true;
        btn.textContent = '⏳...';
        
        fetch(ajaxUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'aga_publier_fiche_catalogue',
                nonce: agaNonce,
                fiche_id: ficheId
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                row.classList.add('fiche-removing');
                setTimeout(() => {
                    row.remove();
                    updateStats();
                }, 300);
                
                // Notification
                if (data.data.url) {
                    const notif = document.createElement('div');
                    notif.innerHTML = `✅ Publié ! <a href="${data.data.url}" target="_blank">Voir sur le blog</a>`;
                    notif.style.cssText = 'position:fixed;top:50px;right:20px;background:#d1e7dd;padding:15px 20px;border-radius:8px;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,0.15);';
                    document.body.appendChild(notif);
                    setTimeout(() => notif.remove(), 5000);
                }
            } else {
                alert('Erreur : ' + (data.data?.message || 'Erreur inconnue'));
                btn.disabled = false;
                btn.textContent = '🚀 Publier';
            }
        })
        .catch(err => {
            alert('Erreur réseau');
            btn.disabled = false;
            btn.textContent = '🚀 Publier';
        });
    }
    
    // Rejet
    function agaRejeter(ficheId) {
        if (!confirm('Rejeter cette fiche ? Elle ne sera plus proposée à la publication.')) return;
        
        const row = document.getElementById('fiche-row-' + ficheId);
        
        fetch(ajaxUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'aga_rejeter_fiche',
                nonce: agaNonce,
                fiche_id: ficheId
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                row.classList.add('fiche-removing');
                setTimeout(() => {
                    row.remove();
                    updateStats();
                }, 300);
            }
        });
    }
    
    // Mise à jour des stats
    function updateStats() {
        const remaining = document.querySelectorAll('.aga-publication-table tbody tr').length;
        document.querySelector('.stat-number').textContent = remaining;
        
        if (remaining === 0) {
            document.querySelector('.aga-publication-table').outerHTML = `
                <div class="aga-empty-state">
                    <p>🎉 Aucune fiche en attente de publication !</p>
                </div>
            `;
        }
    }
    
    // Rafraîchir les doublons
    function agaRefreshDoublons() {
        location.reload();
    }
    
    // Raccourci clavier : Entrée pour sauvegarder les refs
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.classList.contains('refs-input')) {
            const ficheId = e.target.id.replace('refs-input-', '');
            agaSaveRefs(ficheId);
        }
        if (e.key === 'Escape' && e.target.classList.contains('refs-input')) {
            const ficheId = e.target.id.replace('refs-input-', '');
            agaCancelEditRefs(ficheId);
        }
    });
    </script>
    <?php
}