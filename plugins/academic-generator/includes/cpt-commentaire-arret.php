<?php
/**
 * Custom Post Type : Commentaire d'Arrêt
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
 * Créer le Custom Post Type pour les commentaires d'arrêt
 */
function aga_creer_cpt_commentaire_arret() {
    $labels = array(
        'name'               => 'Commentaires d\'Arrêt',
        'singular_name'      => 'Commentaire d\'Arrêt',
        'menu_name'          => 'Commentaires d\'Arrêt',
        'add_new'            => 'Ajouter',
        'add_new_item'       => 'Ajouter un commentaire',
        'edit_item'          => 'Modifier',
        'new_item'           => 'Nouveau commentaire',
        'view_item'          => 'Voir',
        'search_items'       => 'Rechercher',
        'not_found'          => 'Aucun commentaire trouvé',
        'not_found_in_trash' => 'Aucun commentaire dans la corbeille'
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array('slug' => 'commentaire-arret'),
        'capability_type'     => 'post',
        'has_archive'         => false,
        'hierarchical'        => false,
        'menu_position'       => 23,
        'menu_icon'           => 'dashicons-format-aside',
        'supports'            => array('title', 'editor', 'author'),
        'show_in_rest'        => false,
    );

    register_post_type('commentaire_arret', $args);
}
add_action('init', 'aga_creer_cpt_commentaire_arret');

// ============================================================================
// META BOXES ADMIN
// ============================================================================

/**
 * Ajouter les métadonnées personnalisées
 */
function aga_ajouter_meta_boxes_commentaire() {
    add_meta_box(
        'aga_commentaire_details',
        'Détails du commentaire d\'arrêt',
        'aga_afficher_meta_box_commentaire',
        'commentaire_arret',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'aga_ajouter_meta_boxes_commentaire');

/**
 * Afficher la meta box
 */
function aga_afficher_meta_box_commentaire($post) {
    wp_nonce_field('aga_save_meta_commentaire', 'aga_meta_nonce_commentaire');
    
    $references = get_post_meta($post->ID, '_aga_references', true);
    $matiere = get_post_meta($post->ID, '_aga_matiere', true);
    $texte_arret = get_post_meta($post->ID, '_aga_texte_arret', true);
    $date_generation = get_post_meta($post->ID, '_aga_date_generation', true);
    $credits_utilises = get_post_meta($post->ID, '_aga_credits_utilises', true);
    
    echo '<table class="form-table">';
    
    echo '<tr>';
    echo '<th><label>Références de l\'arrêt</label></th>';
    echo '<td><input type="text" value="' . esc_attr($references) . '" class="regular-text" readonly /></td>';
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
    
    echo '<tr>';
    echo '<th><label>Texte de l\'arrêt</label></th>';
    echo '<td><textarea class="large-text" rows="10" readonly>' . esc_textarea($texte_arret) . '</textarea></td>';
    echo '</tr>';
    
    echo '</table>';
}

// ============================================================================
// CRÉATION DE COMMENTAIRE
// ============================================================================

/**
 * Créer un nouveau commentaire d'arrêt dans la BDD
 */
function aga_creer_commentaire_arret($references, $matiere, $texte_arret, $contenu, $credits_utilises) {
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
        'post_title'    => 'Commentaire - ' . $references,
        'post_content'  => $contenu,
        'post_status'   => 'publish',
        'post_author'   => $user_id,
        'post_type'     => 'commentaire_arret',
        'post_name'     => $slug_unique,
    );
    
    $post_id = wp_insert_post($post_data);
    
    if ($post_id && !is_wp_error($post_id)) {
        // Sauvegarder les métadonnées
        update_post_meta($post_id, '_aga_references', sanitize_text_field($references));
        update_post_meta($post_id, '_aga_matiere', sanitize_text_field($matiere));
        update_post_meta($post_id, '_aga_texte_arret', sanitize_textarea_field($texte_arret));
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
 * Sécuriser l'accès aux commentaires d'arrêt
 */
function aga_securiser_acces_commentaire() {
    if (!is_singular('commentaire_arret') || is_admin()) {
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
add_action('template_redirect', 'aga_securiser_acces_commentaire');

// ============================================================================
// TEMPLATE OVERRIDE
// ============================================================================

/**
 * Forcer l'utilisation du template personnalisé
 */
function aga_forcer_template_commentaire($template) {
    global $post;

    if ($post && $post->post_type == 'commentaire_arret') {
        if (class_exists('FluentCommunity\App\App')) {
            return $template;
        }

        $plugin_template = AGA_PLUGIN_PATH . 'templates/single-commentaire-arret.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }

    return $template;
}
add_filter('template_include', 'aga_forcer_template_commentaire', 99);

// ============================================================================
// OBTENIR LES COMMENTAIRES D'UN UTILISATEUR
// ============================================================================

/**
 * Obtenir les commentaires d'un utilisateur
 */
function aga_obtenir_commentaires_utilisateur($user_id = null, $limite = 10, $offset = 0) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $args = array(
        'post_type'      => 'commentaire_arret',
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
 * Obtenir les commentaires regroupés par matière
 */
function aga_obtenir_commentaires_par_matiere($user_id) {
    $commentaires = aga_obtenir_commentaires_utilisateur($user_id, -1);
    $commentaires_groupes = array();
    
    foreach ($commentaires as $commentaire) {
        $matiere = get_post_meta($commentaire->ID, '_aga_matiere', true);
        $matiere_formatee = aga_formater_matiere($matiere);
        
        if (!isset($commentaires_groupes[$matiere_formatee])) {
            $commentaires_groupes[$matiere_formatee] = array();
        }
        
        $commentaires_groupes[$matiere_formatee][] = $commentaire;
    }
    
    return $commentaires_groupes;
}

// ============================================================================
// SUPPRESSION (AJAX)
// ============================================================================

/**
 * Supprimer un commentaire d'arrêt
 */
function aga_supprimer_commentaire() {
    if (!wp_verify_nonce($_POST['nonce'], 'supprimer_commentaire_nonce')) {
        wp_die('Sécurité échouée');
    }
    
    if (!isset($_POST['commentaire_id'])) {
        wp_send_json_error('Données manquantes');
        return;
    }
    
    $commentaire_id = (int) $_POST['commentaire_id'];
    $current_user_id = (int) get_current_user_id();
    
    if ($commentaire_id <= 0) {
        wp_send_json_error('ID invalide');
        return;
    }
    
    $commentaire = get_post($commentaire_id);
    if (!$commentaire || $commentaire->post_author != $current_user_id) {
        wp_send_json_error('Non autorisé');
        return;
    }
    
    $deleted = wp_delete_post($commentaire_id, true);
    
    if ($deleted) {
        wp_send_json_success('Commentaire supprimé');
    } else {
        wp_send_json_error('Erreur lors de la suppression');
    }
}
add_action('wp_ajax_supprimer_commentaire', 'aga_supprimer_commentaire');

// ============================================================================
// FORMATAGE DU CONTENU DANS LE PORTAIL FC
// ============================================================================

/**
 * Formater le contenu du commentaire quand affiché via the_content() (portail FC)
 */
function aga_formater_contenu_commentaire_fc($content) {
    if (!is_singular('commentaire_arret') || is_admin()) {
        return $content;
    }

    remove_filter('the_content', 'aga_formater_contenu_commentaire_fc', 20);

    $post_id = get_the_ID();
    $references = get_post_meta($post_id, '_aga_references', true);
    $matiere = get_post_meta($post_id, '_aga_matiere', true);
    $texte_arret = get_post_meta($post_id, '_aga_texte_arret', true);
    $date_generation = get_post_meta($post_id, '_aga_date_generation', true);
    $matiere_formatee = aga_formater_matiere($matiere);

    $sections = aga_parser_contenu_commentaire($content);

    ob_start();
    ?>
    <div class="aga-result">

        <div class="aga-result-alert aga-result-alert--success">
            <svg class="aga-result-alert-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22,4 12,14.01 9,11.01"></polyline>
            </svg>
            <div>
                <span class="aga-result-alert-title">Commentaire d'arrêt généré !</span>
                <p class="aga-result-alert-text">Ce commentaire est généré par IA et peut contenir des erreurs. Relisez et vérifiez l'exactitude juridique avant utilisation.</p>
            </div>
        </div>

        <nav class="aga-result-breadcrumb">
            <a href="<?php echo home_url('/generateur-de-commentaire-darret/'); ?>">Générateur</a>
            <span class="aga-result-breadcrumb-sep">›</span>
            <span class="aga-result-breadcrumb-current">Mon commentaire</span>
        </nav>

        <div class="aga-result-meta">
            <span><strong>Arrêt :</strong> <?php echo esc_html($references); ?></span>
            <span><strong>Matière :</strong> <?php echo esc_html($matiere_formatee); ?></span>
            <?php if ($date_generation): ?>
                <span><strong>Date :</strong> <?php echo date('d/m/Y', strtotime($date_generation)); ?></span>
            <?php endif; ?>
        </div>

        <?php if ($texte_arret): ?>
        <div class="aga-result-accordion">
            <input type="checkbox" id="aga-arret-toggle" class="aga-result-accordion-toggle">
            <label for="aga-arret-toggle" class="aga-result-accordion-header">
                <h2 class="aga-result-accordion-title">Texte de l'arrêt</h2>
                <span class="aga-result-accordion-arrow">&#9660;</span>
            </label>
            <div class="aga-result-accordion-body"><?php echo esc_html($texte_arret); ?></div>
        </div>
        <?php endif; ?>

        <div class="aga-result-card">
            <div class="aga-result-card-header">
                <h2 class="aga-result-card-title">Commentaire</h2>
                <button class="aga-btn-copy" onclick="agaCopyContent('.aga-result-card-body')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                    Copier
                </button>
            </div>
            <div class="aga-result-card-body">
                <?php if ($sections['parsing_reussi']): ?>

                    <?php if (!empty($sections['introduction'])): ?>
                        <h2>Introduction</h2>
                        <?php
                        $lignes_intro = explode("\n", $sections['introduction']);
                        foreach ($lignes_intro as $ligne) {
                            $ligne = trim($ligne);
                            if (empty($ligne)) continue;
                            if (preg_match('/^\(([^)]+)\)\s*(.+)$/i', $ligne, $match)) {
                                echo '<p><strong>(' . esc_html($match[1]) . ')</strong> ' . esc_html($match[2]) . '</p>';
                            } else {
                                echo '<p>' . esc_html($ligne) . '</p>';
                            }
                        }
                        ?>
                    <?php endif; ?>

                    <?php if (!empty($sections['partie_1']['titre'])): ?>
                        <h2>I. <?php echo esc_html($sections['partie_1']['titre']); ?></h2>
                        <?php aga_render_partie_commentaire($sections['partie_1']['contenu']); ?>
                    <?php endif; ?>

                    <?php if (!empty($sections['partie_2']['titre'])): ?>
                        <h2>II. <?php echo esc_html($sections['partie_2']['titre']); ?></h2>
                        <?php aga_render_partie_commentaire($sections['partie_2']['contenu']); ?>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="aga-result-alert aga-result-alert--warning" style="margin-bottom:1rem;">
                        <div><p class="aga-result-alert-text">La structure n'a pas pu être analysée. Contenu complet ci-dessous.</p></div>
                    </div>
                    <div style="white-space: pre-wrap;"><?php echo esc_html($sections['introduction']); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="aga-result-actions">
            <a href="<?php echo home_url('/generateur-de-commentaire-darret/'); ?>" class="aga-result-action aga-result-action--outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Nouveau commentaire
            </a>
            <a href="<?php echo home_url('/mes-commentaires/'); ?>" class="aga-result-action aga-result-action--primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v5h5"></path><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"></path><path d="M12 7v5l4 2"></path></svg>
                Mes commentaires
            </a>
        </div>
    </div>
    <?php

    add_filter('the_content', 'aga_formater_contenu_commentaire_fc', 20);
    return ob_get_clean();
}
add_filter('the_content', 'aga_formater_contenu_commentaire_fc', 20);

/**
 * Helper : rendre une partie de commentaire (I ou II) avec mise en forme
 */
function aga_render_partie_commentaire($contenu) {
    $lignes = explode("\n", $contenu);
    $in_list = false;

    foreach ($lignes as $ligne) {
        $ligne = trim($ligne);
        if (empty($ligne)) continue;

        if (preg_match('/^([A-B])\.\s+(.+)$/i', $ligne)) {
            if ($in_list) { echo '</ul>'; $in_list = false; }
            echo '<h3>' . esc_html($ligne) . '</h3>';
        } elseif (preg_match('/^\((Annonce de plan interne|Transition)\)\s*(.*)$/i', $ligne, $match)) {
            if ($in_list) { echo '</ul>'; $in_list = false; }
            echo '<p class="aga-result-transition"><strong>(' . esc_html($match[1]) . ')</strong> ' . esc_html($match[2]) . '</p>';
        } elseif (preg_match('/^[\-\*]\s+(.+)$/', $ligne, $match)) {
            if (!$in_list) { echo '<ul>'; $in_list = true; }
            echo '<li>' . esc_html($match[1]) . '</li>';
        } else {
            if ($in_list) { echo '</ul>'; $in_list = false; }
            echo '<p>' . esc_html($ligne) . '</p>';
        }
    }
    if ($in_list) { echo '</ul>'; }
}

// ============================================================================
// PARSER CONTENU COMMENTAIRE
// ============================================================================

/**
 * Parser le contenu du commentaire en sections
 */
function aga_parser_contenu_commentaire($contenu) {
    $sections = array(
        'introduction' => '',
        'partie_1' => array('titre' => '', 'contenu' => ''),
        'partie_2' => array('titre' => '', 'contenu' => ''),
        'parsing_reussi' => false
    );

    // Nettoyer le contenu (strip HTML de wpautop + décoder entités de wptexturize)
    $contenu = wp_strip_all_tags($contenu);
    $contenu = html_entity_decode($contenu, ENT_QUOTES, 'UTF-8');
    $contenu = trim($contenu);
    
    // Diviser en lignes
    $lines = explode("\n", $contenu);
    $current_section = '';
    $buffer = array();
    
    foreach ($lines as $line) {
            $line_trim = trim($line);
            
            // Si on n'a pas encore de section ET qu'on trouve "(Accroche)" → c'est le début de l'intro
            if (empty($current_section) && preg_match('/^\(Accroche\)/i', $line_trim)) {
                $current_section = 'introduction';
            }
        
        // Détecter "Introduction" explicite (avec ou sans ##)
        if (preg_match('/^(##\s*)?Introduction\s*$/i', $line_trim)) {
            $current_section = 'introduction';
            continue;
        }
        
        // Détecter "I." (avec ou sans ##)
        if (preg_match('/^(##\s*)?I\.\s*(.+)$/i', $line_trim, $match)) {
            // Sauvegarder l'introduction
            if ($current_section === 'introduction' && !empty($buffer)) {
                $sections['introduction'] = trim(implode("\n", $buffer));
                $buffer = array();
            }
            
            $sections['partie_1']['titre'] = trim($match[2]);
            $current_section = 'partie_1';
            continue;
        }
        
        // Détecter "II." (avec ou sans ##)
        if (preg_match('/^(##\s*)?II\.\s*(.+)$/i', $line_trim, $match)) {
            // Sauvegarder partie I
            if ($current_section === 'partie_1' && !empty($buffer)) {
                $sections['partie_1']['contenu'] = trim(implode("\n", $buffer));
                $buffer = array();
            }
            
            $sections['partie_2']['titre'] = trim($match[2]);
            $current_section = 'partie_2';
            continue;
        }
        
        // Ajouter la ligne au buffer
        $buffer[] = $line;
    }
    
    // Sauvegarder le dernier buffer
    if (!empty($buffer)) {
        if ($current_section === 'introduction') {
            $sections['introduction'] = trim(implode("\n", $buffer));
        } elseif ($current_section === 'partie_1') {
            $sections['partie_1']['contenu'] = trim(implode("\n", $buffer));
        } elseif ($current_section === 'partie_2') {
            $sections['partie_2']['contenu'] = trim(implode("\n", $buffer));
        }
    }
    
    // Vérifier si on a au moins l'introduction
    if (!empty($sections['introduction'])) {
        $sections['parsing_reussi'] = true;
        return $sections;
    }
    
    // FALLBACK : Tout afficher
    $sections['parsing_reussi'] = false;
    $sections['introduction'] = $contenu;
    
    return $sections;
}