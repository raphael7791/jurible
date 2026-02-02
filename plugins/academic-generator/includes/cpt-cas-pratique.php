<?php
/**
 * Custom Post Type : Cas Pratique
 * Enregistrement du CPT et gestion des métadonnées
 */

if (!defined('ABSPATH')) {
    exit;
}

// Définir la constante si elle n'existe pas (pour AJAX)
if (!defined('AGA_PLUGIN_PATH')) {
    define('AGA_PLUGIN_PATH', plugin_dir_path(dirname(__FILE__)));
}

// ============================================================================
// ENREGISTREMENT DU CUSTOM POST TYPE
// ============================================================================

/**
 * Créer le Custom Post Type pour les cas pratiques
 */
function aga_creer_cpt_cas_pratique() {
    $labels = array(
        'name'               => 'Cas Pratiques',
        'singular_name'      => 'Cas Pratique',
        'menu_name'          => 'Cas Pratiques',
        'add_new'            => 'Ajouter',
        'add_new_item'       => 'Ajouter un cas pratique',
        'edit_item'          => 'Modifier',
        'new_item'           => 'Nouveau cas pratique',
        'view_item'          => 'Voir',
        'search_items'       => 'Rechercher',
        'not_found'          => 'Aucun cas pratique trouvé',
        'not_found_in_trash' => 'Aucun cas pratique dans la corbeille'
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array('slug' => 'cas-pratique'),
        'capability_type'     => 'post',
        'has_archive'         => false,
        'hierarchical'        => false,
        'menu_position'       => 22,
        'menu_icon'           => 'dashicons-clipboard',
        'supports'            => array('title', 'editor', 'author'),
        'show_in_rest'        => false,
    );

    register_post_type('cas_pratique', $args);
}
add_action('init', 'aga_creer_cpt_cas_pratique');

// ============================================================================
// META BOXES ADMIN
// ============================================================================

/**
 * Ajouter les métadonnées personnalisées
 */
function aga_ajouter_meta_boxes_cas_pratique() {
    add_meta_box(
        'aga_cas_pratique_details',
        'Détails du cas pratique',
        'aga_afficher_meta_box_cas_pratique',
        'cas_pratique',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'aga_ajouter_meta_boxes_cas_pratique');

/**
 * Afficher la meta box
 */
function aga_afficher_meta_box_cas_pratique($post) {
    wp_nonce_field('aga_save_meta_cas_pratique', 'aga_meta_nonce_cas_pratique');
    
    $sujet = get_post_meta($post->ID, '_aga_sujet_cas_pratique', true);
    $matiere = get_post_meta($post->ID, '_aga_matiere', true);
    $date_generation = get_post_meta($post->ID, '_aga_date_generation', true);
    $credits_utilises = get_post_meta($post->ID, '_aga_credits_utilises', true);
    
    echo '<table class="form-table">';
    
    echo '<tr>';
    echo '<th><label>Sujet du cas pratique</label></th>';
    echo '<td><textarea class="large-text" rows="5" readonly>' . esc_textarea($sujet) . '</textarea></td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<th><label>Matière</label></th>';
    echo '<td><input type="text" value="' . esc_attr($matiere) . '" class="regular-text" readonly /></td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<th><label>Crédits utilisés</label></th>';
    echo '<td><input type="text" value="' . esc_attr($credits_utilises ?: 'N/A') . '" class="regular-text" readonly /></td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<th><label>Date de génération</label></th>';
    echo '<td><input type="text" value="' . esc_attr($date_generation) . '" class="regular-text" readonly /></td>';
    echo '</tr>';
    
    echo '</table>';
}

// ============================================================================
// CRÉATION DE CAS PRATIQUE
// ============================================================================

/**
 * Créer un nouveau cas pratique dans la BDD
 */
function aga_creer_cas_pratique($sujet, $matiere, $contenu, $credits_utilises) {
    $user_id = get_current_user_id();
    
    if (!$user_id) {
        return false;
    }
    
    // Générer un slug unique : DDMMYYYY-XXXXXX
    $date_slug = date('dmY');
    $hash = substr(md5(uniqid(rand(), true)), 0, 6);
    $slug_unique = $date_slug . '-' . $hash;
    
    // Créer le post
    $post_data = array(
        'post_title'    => 'Cas pratique - ' . mb_substr($sujet, 0, 60) . '...',
        'post_content'  => $contenu,
        'post_status'   => 'publish',
        'post_author'   => $user_id,
        'post_type'     => 'cas_pratique',
        'post_name'     => $slug_unique,
    );
    
    $post_id = wp_insert_post($post_data);
    
    if ($post_id && !is_wp_error($post_id)) {
        // Sauvegarder les métadonnées
        update_post_meta($post_id, '_aga_sujet_cas_pratique', sanitize_textarea_field($sujet));
        update_post_meta($post_id, '_aga_matiere', sanitize_text_field($matiere));
        update_post_meta($post_id, '_aga_date_generation', current_time('mysql'));
        update_post_meta($post_id, '_aga_credits_utilises', (int) $credits_utilises);
        
        return $post_id;
    }
    
    return false;
}

// ============================================================================
// SÉCURITÉ D'ACCÈS
// ============================================================================

/**
 * Sécuriser l'accès aux cas pratiques
 */
function aga_securiser_acces_cas_pratique() {
    if (!is_singular('cas_pratique') || is_admin()) {
        return;
    }
    
    global $post;
    $current_user_id = get_current_user_id();
    
    if (!$post || !$current_user_id) {
        wp_die('Accès non autorisé.', 'Erreur 403', array('response' => 403));
        return;
    }
    
    if ($post->post_author != $current_user_id && !current_user_can('manage_options')) {
        wp_die('Accès non autorisé.', 'Erreur 403', array('response' => 403));
    }
}
add_action('template_redirect', 'aga_securiser_acces_cas_pratique');

// ============================================================================
// TEMPLATE OVERRIDE
// ============================================================================

/**
 * Forcer l'utilisation du template personnalisé
 */
function aga_forcer_template_cas_pratique($template) {
    global $post;
    
    if ($post && $post->post_type == 'cas_pratique') {
        $plugin_template = AGA_PLUGIN_PATH . 'templates/single-cas-pratique.php';
        
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    
    return $template;
}
add_filter('template_include', 'aga_forcer_template_cas_pratique', 99);

// ============================================================================
// OBTENIR LES CAS PRATIQUES D'UN UTILISATEUR
// ============================================================================

/**
 * Obtenir les cas pratiques d'un utilisateur
 */
function aga_obtenir_cas_pratiques_utilisateur($user_id = null, $limite = 10, $offset = 0) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $args = array(
        'post_type'      => 'cas_pratique',
        'post_status'    => 'publish',
        'author'         => $user_id,
        'posts_per_page' => $limite,
        'offset'         => $offset,
        'orderby'        => 'date',
        'order'          => 'DESC'
    );
    
    return get_posts($args);
}

/**
 * Obtenir les cas pratiques regroupés par matière
 */
function aga_obtenir_cas_pratiques_par_matiere($user_id) {
    $cas_pratiques = aga_obtenir_cas_pratiques_utilisateur($user_id, -1);
    $cas_pratiques_groupes = array();
    
    foreach ($cas_pratiques as $cas) {
        $matiere = get_post_meta($cas->ID, '_aga_matiere', true);
        $matiere_formatee = aga_formater_matiere($matiere);
        
        if (!isset($cas_pratiques_groupes[$matiere_formatee])) {
            $cas_pratiques_groupes[$matiere_formatee] = array();
        }
        
        $cas_pratiques_groupes[$matiere_formatee][] = $cas;
    }
    
    return $cas_pratiques_groupes;
}

// ============================================================================
// SUPPRESSION (AJAX)
// ============================================================================

/**
 * Supprimer un cas pratique
 */
function aga_supprimer_cas_pratique() {
    if (!wp_verify_nonce($_POST['nonce'], 'supprimer_cas_pratique_nonce')) {
        wp_die('Sécurité échouée');
    }
    
    if (!isset($_POST['cas_pratique_id'])) {
        wp_send_json_error('Données manquantes');
        return;
    }
    
    $cas_pratique_id = (int) $_POST['cas_pratique_id'];
    $current_user_id = (int) get_current_user_id();
    
    if ($cas_pratique_id <= 0) {
        wp_send_json_error('ID invalide');
        return;
    }
    
    $cas_pratique = get_post($cas_pratique_id);
    if (!$cas_pratique || $cas_pratique->post_author != $current_user_id) {
        wp_send_json_error('Non autorisé');
        return;
    }
    
    $deleted = wp_delete_post($cas_pratique_id, true);
    
    if ($deleted) {
        wp_send_json_success('Cas pratique supprimé');
    } else {
        wp_send_json_error('Erreur lors de la suppression');
    }
}
add_action('wp_ajax_supprimer_cas_pratique', 'aga_supprimer_cas_pratique');

// ============================================================================
// PARSER CONTENU CAS PRATIQUE
// ============================================================================

/**
 * Parser le contenu du cas pratique en sections
 */
function aga_parser_contenu_cas_pratique($contenu) {
    $sections = array(
        'plan' => '',
        'contenu' => '',
        'parsing_reussi' => false
    );
    
    // Nettoyer le contenu
    $contenu = trim($contenu);
    
    // Tentative de parsing avec marqueurs
    if (preg_match('/===PLAN===\s*(.*?)===CONTENU===/s', $contenu, $match_plan) &&
        preg_match('/===CONTENU===\s*(.*?)$/s', $contenu, $match_contenu)) {
        
        $sections['plan'] = trim($match_plan[1]);
        $sections['contenu'] = trim($match_contenu[1]);
        $sections['parsing_reussi'] = true;
        
        return $sections;
    }
    
    // FALLBACK : Tout afficher
    $sections['parsing_reussi'] = false;
    $sections['contenu'] = $contenu;
    
    return $sections;
}