<?php
/**
 * API REST pour le catalogue de fiches
 */

if (!defined('ABSPATH')) {
    exit;
}

// Enregistrer l'API REST
add_action('rest_api_init', function() {
    register_rest_route('gfa/v1', '/fiches-sync', array( // ✅ Changé l'URL
        'methods' => 'GET',
        'callback' => 'aga_api_sync_obtenir_fiches', // ✅ Nouveau nom
        'permission_callback' => '__return_true'
    ));
});

/**
 * Retourner les fiches publiables pour le catalogue (version améliorée)
 */
function aga_api_sync_obtenir_fiches($request) {
    $statut = $request->get_param('statut') ?: 'tous';
    
    $args = array(
        'post_type' => 'fiche_arret',
        'post_status' => 'publish',
        'posts_per_page' => 100,
        'orderby' => 'date',
        'order' => 'DESC'
    );
    
    if ($statut !== 'tous') {
        $args['meta_query'] = array(
            array(
                'key' => '_aga_statut_catalogue',
                'value' => $statut,
                'compare' => '='
            )
        );
    }
    
    $fiches = get_posts($args);
    $resultats = array();
    
    foreach ($fiches as $fiche) {
        // Récupérer les références
        $ref_normalized = get_post_meta($fiche->ID, '_aga_references_normalized', true);
        $ref_brutes = get_post_meta($fiche->ID, '_aga_references', true);
        
        // ✅ SI VIDES, extraire depuis le titre
        if (empty($ref_normalized) && empty($ref_brutes)) {
            $titre = $fiche->post_title;
            $ref_brutes = $titre;
        }
        
        $resultats[] = array(
            'id' => $fiche->ID,
            'references' => !empty($ref_normalized) ? $ref_normalized : $ref_brutes,
            'matiere' => get_post_meta($fiche->ID, '_aga_matiere', true),
            'contenu' => $fiche->post_content,
            'date_generation' => get_post_meta($fiche->ID, '_aga_date_generation', true),
            'statut_catalogue' => get_post_meta($fiche->ID, '_aga_statut_catalogue', true)
        );
    }
    
    return rest_ensure_response($resultats);
}