<?php
/**
 * Custom Post Type : Fiche d'arrêt
 * Enregistrement du CPT et gestion des métadonnées
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('AGA_PLUGIN_PATH')) {
    define('AGA_PLUGIN_PATH', plugin_dir_path(dirname(__FILE__)));
}
// ============================================================================
// ENREGISTREMENT DU CUSTOM POST TYPE
// ============================================================================

/**
 * Créer le Custom Post Type pour les fiches d'arrêt
 */
function aga_creer_cpt_fiche_arret() {
    $labels = array(
        'name'               => 'Fiches d\'arrêt',
        'singular_name'      => 'Fiche d\'arrêt',
        'menu_name'          => 'Fiches d\'arrêt',
        'add_new'            => 'Ajouter une fiche',
        'add_new_item'       => 'Ajouter une nouvelle fiche',
        'edit_item'          => 'Modifier la fiche',
        'new_item'           => 'Nouvelle fiche',
        'view_item'          => 'Voir la fiche',
        'search_items'       => 'Rechercher des fiches',
        'not_found'          => 'Aucune fiche trouvée',
        'not_found_in_trash' => 'Aucune fiche dans la corbeille'
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array('slug' => 'fiche'),
        'capability_type'     => 'post',
        'has_archive'         => false,
        'hierarchical'        => false,
        'menu_position'       => 20,
        'menu_icon'           => 'dashicons-media-document',
        'supports'            => array('title', 'editor', 'author'),
        'show_in_rest'        => false,
    );

    register_post_type('fiche_arret', $args);
}
add_action('init', 'aga_creer_cpt_fiche_arret');

// ============================================================================
// META BOXES ADMIN
// ============================================================================

/**
 * Ajouter les métadonnées personnalisées pour les fiches
 */
function aga_ajouter_meta_boxes_fiche() {
    add_meta_box(
        'aga_fiche_details',
        'Détails de la fiche',
        'aga_afficher_meta_box_fiche',
        'fiche_arret',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'aga_ajouter_meta_boxes_fiche');

/**
 * Afficher la meta box dans l'admin
 */
function aga_afficher_meta_box_fiche($post) {
    wp_nonce_field('aga_save_meta_fiche', 'aga_meta_nonce_fiche');
    
    $references = get_post_meta($post->ID, '_aga_references', true);
    $references_normalized = get_post_meta($post->ID, '_aga_references_normalized', true);
    $matiere = get_post_meta($post->ID, '_aga_matiere', true);
    $texte_original = get_post_meta($post->ID, '_aga_texte_original', true);
    $date_generation = get_post_meta($post->ID, '_aga_date_generation', true);
    $statut_catalogue = get_post_meta($post->ID, '_aga_statut_catalogue', true);
    
    echo '<table class="form-table">';
    
    echo '<tr>';
    echo '<th><label for="aga_references">Références originales</label></th>';
    echo '<td><input type="text" id="aga_references" name="aga_references" value="' . esc_attr($references ?: '') . '" class="regular-text" /></td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<th><label for="aga_references_normalized">Références normalisées</label></th>';
    echo '<td><input type="text" id="aga_references_normalized" name="aga_references_normalized" value="' . esc_attr($references_normalized ?: '') . '" class="regular-text" /></td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<th><label for="aga_matiere">Matière</label></th>';
    echo '<td><input type="text" id="aga_matiere" name="aga_matiere" value="' . esc_attr($matiere) . '" class="regular-text" /></td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<th><label for="aga_statut_catalogue">Statut catalogue</label></th>';
    echo '<td>';
    echo '<select id="aga_statut_catalogue" name="aga_statut_catalogue">';
    echo '<option value="brouillon"' . selected($statut_catalogue, 'brouillon', false) . '>Brouillon</option>';
    echo '<option value="publiable"' . selected($statut_catalogue, 'publiable', false) . '>Publiable</option>';
    echo '<option value="rejete"' . selected($statut_catalogue, 'rejete', false) . '>Rejeté</option>';
    echo '</select>';
    echo '</td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<th><label for="aga_date_generation">Date de génération</label></th>';
    echo '<td><input type="text" id="aga_date_generation" value="' . esc_attr($date_generation) . '" class="regular-text" readonly /></td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<th><label for="aga_texte_original">Texte original de l\'arrêt</label></th>';
    echo '<td><textarea id="aga_texte_original" name="aga_texte_original" rows="10" cols="50" class="large-text">' . esc_textarea($texte_original ?: '') . '</textarea></td>';
    echo '</tr>';
    
    echo '</table>';
}

/**
 * Sauvegarder les métadonnées
 */
function aga_sauvegarder_meta_donnees_fiche($post_id) {
    if (!isset($_POST['aga_meta_nonce_fiche']) || !wp_verify_nonce($_POST['aga_meta_nonce_fiche'], 'aga_save_meta_fiche')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['aga_references'])) {
        update_post_meta($post_id, '_aga_references', sanitize_text_field($_POST['aga_references']));
    }
    
    if (isset($_POST['aga_references_normalized'])) {
        update_post_meta($post_id, '_aga_references_normalized', sanitize_text_field($_POST['aga_references_normalized']));
    }

    if (isset($_POST['aga_matiere'])) {
        update_post_meta($post_id, '_aga_matiere', sanitize_text_field($_POST['aga_matiere']));
    }
    
    if (isset($_POST['aga_statut_catalogue'])) {
        update_post_meta($post_id, '_aga_statut_catalogue', sanitize_text_field($_POST['aga_statut_catalogue']));
    }

    if (isset($_POST['aga_texte_original'])) {
        update_post_meta($post_id, '_aga_texte_original', sanitize_textarea_field($_POST['aga_texte_original']));
    }
}
add_action('save_post', 'aga_sauvegarder_meta_donnees_fiche');

// ============================================================================
// CRÉATION DE FICHE
// ============================================================================

/**
 * Créer une nouvelle fiche d'arrêt
 */
function aga_creer_fiche_arret($references, $matiere, $contenu_original, $fiche_generee, $parsing_reussi) {
    $user_id = get_current_user_id();
    
    // Normaliser les références
    $references_normalisees = aga_normaliser_references_prudent($references);
    
    // Créer le post
    $post_data = array(
        'post_title'    => $references,
        'post_content'  => $fiche_generee,
        'post_status'   => 'publish',
        'post_type'     => 'fiche_arret',
        'post_author'   => $user_id
    );
    
    $post_id = wp_insert_post($post_data);
    
    if (!is_wp_error($post_id)) {
        // Sauvegarder les métadonnées
        update_post_meta($post_id, '_aga_references', $references);
        update_post_meta($post_id, '_aga_references_normalized', $references_normalisees);
        update_post_meta($post_id, '_aga_matiere', $matiere);
        update_post_meta($post_id, '_aga_texte_original', $contenu_original);
        update_post_meta($post_id, '_aga_date_generation', current_time('mysql'));
        update_post_meta($post_id, '_aga_user_id', $user_id);
        update_post_meta($post_id, '_aga_parsing_reussi', $parsing_reussi);
        update_post_meta($post_id, '_aga_statut_catalogue', 'brouillon');
        
        return $post_id;
    }
    
    return false;
}

// ============================================================================
// SÉCURITÉ D'ACCÈS
// ============================================================================

/**
 * Sécuriser l'accès aux fiches - seulement le propriétaire peut voir sa fiche
 */
function aga_securiser_acces_fiche() {
    // Vérifier si nous sommes sur une page de fiche d'arrêt
    if (!is_singular('fiche_arret') || is_admin()) {
        return;
    }
    
    global $post;
    $current_user_id = get_current_user_id();
    
    // Vérifier que le post existe et que l'utilisateur est connecté
    if (!$post || !$current_user_id) {
        wp_die('Accès non autorisé.', 'Erreur 403', array('response' => 403));
        return;
    }
    
    // Vérifier si l'utilisateur est le propriétaire ou admin
    if ($post->post_author != $current_user_id && !current_user_can('manage_options')) {
        wp_die('Accès non autorisé à cette fiche.', 'Erreur 403', array('response' => 403));
    }
}
add_action('template_redirect', 'aga_securiser_acces_fiche');

// ============================================================================
// TEMPLATE OVERRIDE
// ============================================================================

/**
 * Forcer l'utilisation du template personnalisé pour les fiches d'arrêt
 */
function aga_forcer_template_fiche($template) {
    global $post;
    
    // Vérifier si nous sommes sur une page de fiche d'arrêt
    if ($post && $post->post_type == 'fiche_arret') {
        // Chemin vers le template dans le plugin
        $plugin_template = AGA_PLUGIN_PATH . 'templates/single-fiche-arret.php';
        
        // Vérifier si le template existe dans le plugin
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    
    return $template;
}
add_filter('template_include', 'aga_forcer_template_fiche', 99);

// ============================================================================
// PARSER SPÉCIFIQUE FICHE D'ARRÊT
// ============================================================================

/**
 * Parser le contenu de la fiche en sections (conservé de votre code original)
 */
function aga_parser_contenu_fiche($contenu) {
    $sections = array(
        'faits' => '',
        'procedure' => '',
        'probleme' => '',
        'solution' => '',
        'parsing_reussi' => false
    );
    
    // Tentative 1 : Parser avec les marqueurs ===XXX===
    if (preg_match('/===FAITS===\s*(.*?)===PROCEDURE===/s', $contenu, $matches_faits) &&
        preg_match('/===PROCEDURE===\s*(.*?)===PROBLEME===/s', $contenu, $matches_procedure) &&
        preg_match('/===PROBLEME===\s*(.*?)===SOLUTION===/s', $contenu, $matches_probleme) &&
        preg_match('/===SOLUTION===\s*(.*?)$/s', $contenu, $matches_solution)) {
        
        $sections['faits'] = trim($matches_faits[1]);
        $sections['procedure'] = trim($matches_procedure[1]);
        $sections['probleme'] = trim($matches_probleme[1]);
        $sections['solution'] = trim($matches_solution[1]);
        $sections['parsing_reussi'] = true;
        
        return $sections;
    }
    
    // Tentative 2 : Parsing intelligent avec mots-clés si échec des marqueurs
    $contenu_lower = mb_strtolower($contenu);
    
    // Chercher des indices de structure
    $indices_procedure = ['première instance', 'cour d\'appel', 'pourvoi', 'juridiction', 'tribunal'];
    $indices_solution = ['cour de cassation rejette', 'cour de cassation casse', 'haute juridiction'];
    
    $pos_procedure = PHP_INT_MAX;
    $pos_solution = PHP_INT_MAX;
    
    // Trouver le début de la procédure
    foreach ($indices_procedure as $indice) {
        $pos = strpos($contenu_lower, $indice);
        if ($pos !== false && $pos < $pos_procedure) {
            $pos_procedure = $pos;
        }
    }
    
    // Trouver le début de la solution
    foreach ($indices_solution as $indice) {
        $pos = strpos($contenu_lower, $indice);
        if ($pos !== false && $pos < $pos_solution) {
            $pos_solution = $pos;
        }
    }
    
    // Trouver le problème de droit (phrase avec ?)
    preg_match('/([^.!?]*\?)/s', substr($contenu, $pos_procedure), $match_probleme);
    $pos_probleme = $match_probleme ? strpos($contenu, $match_probleme[0]) : PHP_INT_MAX;
    
    // Si on a trouvé au moins la procédure et la solution
    if ($pos_procedure < PHP_INT_MAX && $pos_solution < PHP_INT_MAX) {
        $sections['faits'] = trim(substr($contenu, 0, $pos_procedure));
        
        if ($pos_probleme < PHP_INT_MAX && $pos_probleme > $pos_procedure && $pos_probleme < $pos_solution) {
            $sections['procedure'] = trim(substr($contenu, $pos_procedure, $pos_probleme - $pos_procedure));
            $sections['probleme'] = trim($match_probleme[0]);
            $sections['solution'] = trim(substr($contenu, $pos_solution));
        } else {
            // Pas de problème trouvé, diviser entre procédure et solution
            $sections['procedure'] = trim(substr($contenu, $pos_procedure, $pos_solution - $pos_procedure));
            $sections['probleme'] = 'Question de droit non identifiée';
            $sections['solution'] = trim(substr($contenu, $pos_solution));
        }
        
        $sections['parsing_reussi'] = true;
    } else {
        // Échec total du parsing - tout dans faits
        $sections['faits'] = $contenu;
        $sections['parsing_reussi'] = false;
    }
    
    return $sections;
}

// ============================================================================
// OBTENIR LES FICHES D'UN UTILISATEUR
// ============================================================================

/**
 * Obtenir les fiches d'un utilisateur
 */
function aga_obtenir_fiches_utilisateur($user_id = null, $limite = 10, $offset = 0) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $args = array(
        'post_type'      => 'fiche_arret',
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
 * Obtenir les fiches regroupées par matière
 */
function aga_obtenir_fiches_par_matiere($user_id) {
    $fiches = aga_obtenir_fiches_utilisateur($user_id, -1);
    $fiches_groupees = array();
    
    foreach ($fiches as $fiche) {
        $matiere = get_post_meta($fiche->ID, '_aga_matiere', true);
        $matiere_formatee = aga_formater_matiere($matiere);
        
        if (!isset($fiches_groupees[$matiere_formatee])) {
            $fiches_groupees[$matiere_formatee] = array();
        }
        
        $fiches_groupees[$matiere_formatee][] = $fiche;
    }
    
    return $fiches_groupees;
}

// ============================================================================
// SUPPRESSION DE FICHE (AJAX)
// ============================================================================

/**
 * Supprimer une fiche d'arrêt (AJAX)
 */
function aga_supprimer_fiche() {
    // Vérification de sécurité
    if (!wp_verify_nonce($_POST['nonce'], 'supprimer_fiche_nonce')) {
        wp_die('Sécurité échouée');
    }
    
    if (!isset($_POST['fiche_id'])) {
        wp_send_json_error('Données manquantes');
        return;
    }
    
    $fiche_id = (int) $_POST['fiche_id'];
    $current_user_id = (int) get_current_user_id();
    
    if ($fiche_id <= 0) {
        wp_send_json_error('ID invalide');
        return;
    }
    
    // Vérifier que l'utilisateur est propriétaire de la fiche
    $fiche = get_post($fiche_id);
    if (!$fiche || $fiche->post_author != $current_user_id) {
        wp_send_json_error('Non autorisé');
        return;
    }
    
    // Supprimer la fiche
    $deleted = wp_delete_post($fiche_id, true);
    
    if ($deleted) {
        wp_send_json_success('Fiche supprimée');
    } else {
        wp_send_json_error('Erreur lors de la suppression');
    }
}
add_action('wp_ajax_supprimer_fiche', 'aga_supprimer_fiche');