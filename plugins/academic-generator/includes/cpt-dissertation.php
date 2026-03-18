<?php
/**
 * Custom Post Type : Dissertation
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
 * Créer le Custom Post Type pour les dissertations
 */
function aga_creer_cpt_dissertation() {
    $labels = array(
        'name'               => 'Dissertations',
        'singular_name'      => 'Dissertation',
        'menu_name'          => 'Dissertations',
        'add_new'            => 'Ajouter',
        'add_new_item'       => 'Ajouter une dissertation',
        'edit_item'          => 'Modifier',
        'new_item'           => 'Nouvelle dissertation',
        'view_item'          => 'Voir',
        'search_items'       => 'Rechercher',
        'not_found'          => 'Aucune dissertation trouvée',
        'not_found_in_trash' => 'Aucune dissertation dans la corbeille'
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array('slug' => 'dissertation'),
        'capability_type'     => 'post',
        'has_archive'         => false,
        'hierarchical'        => false,
        'menu_position'       => 21,
        'menu_icon'           => 'dashicons-edit-large',
        'supports'            => array('title', 'editor', 'author'),
        'show_in_rest'        => false,
    );

    register_post_type('dissertation', $args);
}
add_action('init', 'aga_creer_cpt_dissertation');

// ============================================================================
// META BOXES ADMIN
// ============================================================================

/**
 * Ajouter les métadonnées personnalisées
 */
function aga_ajouter_meta_boxes_dissertation() {
    add_meta_box(
        'aga_dissertation_details',
        'Détails de la dissertation',
        'aga_afficher_meta_box_dissertation',
        'dissertation',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'aga_ajouter_meta_boxes_dissertation');

/**
 * Afficher la meta box
 */
function aga_afficher_meta_box_dissertation($post) {
    wp_nonce_field('aga_save_meta_dissertation', 'aga_meta_nonce_dissertation');
    
    $sujet = get_post_meta($post->ID, '_aga_sujet', true);
    $matiere = get_post_meta($post->ID, '_aga_matiere', true);
    $type_generation = get_post_meta($post->ID, '_aga_type_generation', true);
    $date_generation = get_post_meta($post->ID, '_aga_date_generation', true);
    $credits_utilises = get_post_meta($post->ID, '_aga_credits_utilises', true);
    
    echo '<table class="form-table">';
    
    echo '<tr>';
    echo '<th><label>Sujet</label></th>';
    echo '<td><textarea class="large-text" rows="3" readonly>' . esc_textarea($sujet) . '</textarea></td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<th><label>Matière</label></th>';
    echo '<td><input type="text" value="' . esc_attr($matiere) . '" class="regular-text" readonly /></td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<th><label>Type</label></th>';
    echo '<td><input type="text" value="' . esc_attr($type_generation === 'plan_detaille' ? 'Plan détaillé (1 crédit)' : 'Dissertation complète (3 crédits)') . '" class="regular-text" readonly /></td>';
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
// CRÉATION DE DISSERTATION
// ============================================================================

/**
 * Créer une nouvelle dissertation dans la BDD
 */
function aga_creer_dissertation($sujet, $matiere, $type_generation, $contenu, $credits_utilises) {
    $user_id = get_current_user_id();
    
    if (!$user_id) {
        return false;
    }
    
    // Générer un slug unique : DDMMYYYY-XXXXXX
    $date_slug = date('dmY'); // Ex: 16102025
    $hash = substr(md5(uniqid(rand(), true)), 0, 6); // Hash court de 6 caractères
    $slug_unique = $date_slug . '-' . $hash; // Ex: 16102025-a3f9k2
    
    // Créer le post
    $post_data = array(
        'post_title'    => $sujet,
        'post_content'  => $contenu,
        'post_status'   => 'publish',
        'post_author'   => $user_id,
        'post_type'     => 'dissertation',
        'post_name'     => $slug_unique, // Slug unique
    );
    
    $post_id = wp_insert_post($post_data);
    
    if ($post_id && !is_wp_error($post_id)) {
        // Sauvegarder les métadonnées
        update_post_meta($post_id, '_aga_sujet', sanitize_text_field($sujet));
        update_post_meta($post_id, '_aga_matiere', sanitize_text_field($matiere));
        update_post_meta($post_id, '_aga_type_generation', sanitize_text_field($type_generation));
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
 * Sécuriser l'accès aux dissertations
 */
function aga_securiser_acces_dissertation() {
    if (!is_singular('dissertation') || is_admin()) {
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
add_action('template_redirect', 'aga_securiser_acces_dissertation');

// ============================================================================
// TEMPLATE OVERRIDE
// ============================================================================

/**
 * Forcer l'utilisation du template personnalisé
 */
function aga_forcer_template_dissertation($template) {
    global $post;

    if ($post && $post->post_type == 'dissertation') {
        if (class_exists('FluentCommunity\App\App')) {
            return $template;
        }

        $plugin_template = AGA_PLUGIN_PATH . 'templates/single-dissertation.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }

    return $template;
}
add_filter('template_include', 'aga_forcer_template_dissertation', 99);

// ============================================================================
// OBTENIR LES DISSERTATIONS D'UN UTILISATEUR
// ============================================================================

/**
 * Obtenir les dissertations d'un utilisateur
 */
function aga_obtenir_dissertations_utilisateur($user_id = null, $limite = 10, $offset = 0) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $args = array(
        'post_type'      => 'dissertation',
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
 * Obtenir les dissertations regroupées par matière
 */
function aga_obtenir_dissertations_par_matiere($user_id) {
    $dissertations = aga_obtenir_dissertations_utilisateur($user_id, -1);
    $dissertations_groupees = array();
    
    foreach ($dissertations as $dissertation) {
        $matiere = get_post_meta($dissertation->ID, '_aga_matiere', true);
        $matiere_formatee = aga_formater_matiere($matiere);
        
        if (!isset($dissertations_groupees[$matiere_formatee])) {
            $dissertations_groupees[$matiere_formatee] = array();
        }
        
        $dissertations_groupees[$matiere_formatee][] = $dissertation;
    }
    
    return $dissertations_groupees;
}

// ============================================================================
// SUPPRESSION (AJAX)
// ============================================================================

/**
 * Supprimer une dissertation
 */
function aga_supprimer_dissertation() {
    if (!wp_verify_nonce($_POST['nonce'], 'supprimer_dissertation_nonce')) {
        wp_die('Sécurité échouée');
    }
    
    if (!isset($_POST['dissertation_id'])) {
        wp_send_json_error('Données manquantes');
        return;
    }
    
    $dissertation_id = (int) $_POST['dissertation_id'];
    $current_user_id = (int) get_current_user_id();
    
    if ($dissertation_id <= 0) {
        wp_send_json_error('ID invalide');
        return;
    }
    
    $dissertation = get_post($dissertation_id);
    if (!$dissertation || $dissertation->post_author != $current_user_id) {
        wp_send_json_error('Non autorisé');
        return;
    }
    
    $deleted = wp_delete_post($dissertation_id, true);
    
    if ($deleted) {
        wp_send_json_success('Dissertation supprimée');
    } else {
        wp_send_json_error('Erreur lors de la suppression');
    }
}
add_action('wp_ajax_supprimer_dissertation', 'aga_supprimer_dissertation');

// ============================================================================
// FORMATAGE DU CONTENU DANS LE PORTAIL FC
// ============================================================================

/**
 * Formater le contenu de la dissertation quand affichée via the_content() (portail FC)
 */
function aga_formater_contenu_dissertation_fc($content) {
    if (!is_singular('dissertation') || is_admin()) {
        return $content;
    }

    remove_filter('the_content', 'aga_formater_contenu_dissertation_fc', 20);

    $post_id = get_the_ID();
    $sujet = get_post_meta($post_id, '_aga_sujet', true);
    $matiere = get_post_meta($post_id, '_aga_matiere', true);
    $type_generation = get_post_meta($post_id, '_aga_type_generation', true);
    $date_generation = get_post_meta($post_id, '_aga_date_generation', true);

    $type_label = ($type_generation === 'plan_detaille') ? 'Plan détaillé' : 'Dissertation complète';
    $matiere_formatee = aga_formater_matiere($matiere);

    ob_start();
    ?>
    <div class="aga-result">

        <div class="aga-result-alert aga-result-alert--success">
            <svg class="aga-result-alert-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22,4 12,14.01 9,11.01"></polyline>
            </svg>
            <div>
                <span class="aga-result-alert-title"><?php echo esc_html($type_label); ?> générée !</span>
                <p class="aga-result-alert-text">Ce contenu est généré par IA et peut contenir des erreurs. Relisez et vérifiez l'exactitude juridique avant utilisation.</p>
            </div>
        </div>

        <nav class="aga-result-breadcrumb">
            <a href="<?php echo home_url('/generateur-de-dissertation/'); ?>">Générateur</a>
            <span class="aga-result-breadcrumb-sep">›</span>
            <span class="aga-result-breadcrumb-current">Ma dissertation</span>
        </nav>

        <div class="aga-result-meta">
            <span><strong>Type :</strong> <?php echo esc_html($type_label); ?></span>
            <span><strong>Matière :</strong> <?php echo esc_html($matiere_formatee); ?></span>
            <?php if ($date_generation): ?>
                <span><strong>Date :</strong> <?php echo date('d/m/Y', strtotime($date_generation)); ?></span>
            <?php endif; ?>
        </div>

        <div class="aga-result-card">
            <div class="aga-result-card-header">
                <h2 class="aga-result-card-title">Contenu de la dissertation</h2>
                <button class="aga-btn-copy" onclick="agaCopyContent('.aga-result-card-body')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                    Copier
                </button>
            </div>
            <div class="aga-result-card-body">
                <?php
                $contenu_brut = wp_strip_all_tags($content);
                $contenu_brut = html_entity_decode($contenu_brut, ENT_QUOTES, 'UTF-8');
                $lignes = explode("\n", $contenu_brut);
                $in_list = false;

                foreach ($lignes as $ligne) {
                    $ligne = trim($ligne);
                    if (empty($ligne)) continue;

                    if (preg_match('/^Introduction$/i', $ligne)) {
                        if ($in_list) { echo '</ul>'; $in_list = false; }
                        echo '<h2>' . esc_html($ligne) . '</h2>';
                    } elseif (preg_match('/^(I{1,3})\.\s+(.+)$/', $ligne)) {
                        if ($in_list) { echo '</ul>'; $in_list = false; }
                        echo '<h2>' . esc_html($ligne) . '</h2>';
                    } elseif (preg_match('/^([A-B])\.\s+(.+)$/', $ligne)) {
                        if ($in_list) { echo '</ul>'; $in_list = false; }
                        echo '<h3>' . esc_html($ligne) . '</h3>';
                    } elseif (preg_match('/^(\(Transition\)|Transition\s*:)\s*(.*)$/i', $ligne, $match)) {
                        if ($in_list) { echo '</ul>'; $in_list = false; }
                        echo '<p class="aga-result-transition"><strong>(Transition)</strong> ' . esc_html($match[2]) . '</p>';
                    } elseif (preg_match('/^\(([^)]+)\)\s*(.+)$/i', $ligne, $match)) {
                        if ($in_list) { echo '</ul>'; $in_list = false; }
                        echo '<p><strong>(' . esc_html($match[1]) . ')</strong> ' . esc_html($match[2]) . '</p>';
                    } elseif (preg_match('/^[\-\*]\s+(.+)$/', $ligne, $match)) {
                        if (!$in_list) { echo '<ul>'; $in_list = true; }
                        echo '<li>' . esc_html($match[1]) . '</li>';
                    } else {
                        if ($in_list) { echo '</ul>'; $in_list = false; }
                        echo '<p>' . esc_html($ligne) . '</p>';
                    }
                }
                if ($in_list) { echo '</ul>'; }
                ?>
            </div>
        </div>

        <div class="aga-result-actions">
            <a href="<?php echo home_url('/generateur-de-dissertation/'); ?>" class="aga-result-action aga-result-action--outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Nouvelle dissertation
            </a>
            <a href="<?php echo home_url('/mes-dissertations/'); ?>" class="aga-result-action aga-result-action--primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v5h5"></path><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"></path><path d="M12 7v5l4 2"></path></svg>
                Mes dissertations
            </a>
        </div>
    </div>
    <?php

    add_filter('the_content', 'aga_formater_contenu_dissertation_fc', 20);
    return ob_get_clean();
}
add_filter('the_content', 'aga_formater_contenu_dissertation_fc', 20);

// ============================================================================
// PARSER CONTENU DISSERTATION
// ============================================================================

/**
 * Parser le contenu de la dissertation en sections
 */
function aga_parser_contenu_dissertation($contenu) {
    $sections = array(
        'introduction' => '',
        'partie_1' => array('titre' => '', 'contenu' => ''),
        'partie_2' => array('titre' => '', 'contenu' => ''),
        'parsing_reussi' => false
    );
    
    // Nettoyer le contenu
    $contenu = trim($contenu);
    
    // Diviser en lignes
    $lines = explode("\n", $contenu);
    $current_section = '';
    $buffer = array();
    
    foreach ($lines as $line) {
        $line_trim = trim($line);
        
        // Détecter "Introduction" (avec ou sans ##)
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