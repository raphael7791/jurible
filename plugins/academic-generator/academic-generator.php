<?php
/**
 * Plugin Name: Générateur Académique Juridique
 * Plugin URI: https://aideauxtd.com
 * Description: Générateurs de fiches d'arrêt, dissertations, commentaires et cas pratiques pour étudiants en droit
 * Version: 2.0.0
 * Author: AideauxTD
 * License: GPL v2 or later
 * Text Domain: academic-generator
 */

// Sécurité : empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Définir les constantes du plugin
define('AGA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AGA_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AGA_PLUGIN_VERSION', '2.0.0');

// Coûts en crédits par type de génération
define('AGA_COUT_FICHE_ARRET', 1);
define('AGA_COUT_PLAN_DETAILLE', 1);
define('AGA_COUT_DISSERTATION_COMPLETE', 1);
define('AGA_COUT_COMMENTAIRE_COMPLET', 1);
define('AGA_COUT_CAS_PRATIQUE', 1);

// ============================================================================
// INCLUSIONS DES MODULES
// ============================================================================

// Menu admin consolidé et dashboard
require_once AGA_PLUGIN_PATH . 'includes/admin-menu-structure.php';
require_once AGA_PLUGIN_PATH . 'includes/admin-dashboard-stats.php';

// Fonctions communes (crédits, OpenAI, sécurité)
require_once AGA_PLUGIN_PATH . 'includes/functions-credits.php';
require_once AGA_PLUGIN_PATH . 'includes/functions-openai.php';
require_once AGA_PLUGIN_PATH . 'includes/functions-common.php';

// Modal d'avis réutilisable
require_once AGA_PLUGIN_PATH . 'includes/modal-avis.php';

// Générateur fiche d'arrêt
require_once AGA_PLUGIN_PATH . 'includes/cpt-fiche-arret.php';
require_once AGA_PLUGIN_PATH . 'includes/generator-fiche-arret.php';

// Générateur dissertation
require_once AGA_PLUGIN_PATH . 'includes/cpt-dissertation.php';
require_once AGA_PLUGIN_PATH . 'includes/generator-dissertation.php';

// API pour synchronisation fiche d'arrêt
require_once AGA_PLUGIN_PATH . 'includes/api-catalogue.php';

// Générateur cas pratique
require_once AGA_PLUGIN_PATH . 'includes/cpt-cas-pratique.php';
require_once AGA_PLUGIN_PATH . 'includes/generator-cas-pratique.php';

// Générateur commentaire d'arrêt
require_once AGA_PLUGIN_PATH . 'includes/cpt-commentaire-arret.php';
require_once AGA_PLUGIN_PATH . 'includes/generator-commentaire-arret.php';

// Interface de publication catalogue
require_once AGA_PLUGIN_PATH . 'includes/admin-publication-catalogue.php';

// ============================================================================
// ACTIVATION / DÉSACTIVATION DU PLUGIN
// ============================================================================

/**
 * Activation du plugin
 */
function aga_activation_plugin() {
    // Flush des règles de réécriture
    flush_rewrite_rules();
    
    // Créer le rôle premium
    aga_creer_role_premium();
    
    // Créer la table pour les avis utilisateurs
    aga_creer_table_avis();
}
register_activation_hook(__FILE__, 'aga_activation_plugin');

/**
 * Désactivation du plugin
 */
function aga_desactivation_plugin() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'aga_desactivation_plugin');

// ============================================================================
// CHARGEMENT DES STYLES ET SCRIPTS
// ============================================================================

/**
 * Charger les styles CSS
 */
function aga_enqueue_styles() {
    // Google Fonts
    wp_enqueue_style(
        'google-fonts-poppins',
        'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap',
        array(),
        null
    );

    // CSS principal du plugin
    wp_enqueue_style(
        'academic-generator-style',
        AGA_PLUGIN_URL . 'assets/style.css',
        array(),
        AGA_PLUGIN_VERSION
    );
}
add_action('wp_enqueue_scripts', 'aga_enqueue_styles');

// ============================================================================
// RÈGLES DE RÉÉCRITURE PERSONNALISÉES
// ============================================================================

/**
 * Ajouter les règles de réécriture pour les URLs propres
 */
function aga_add_rewrite_rules() {
    add_rewrite_rule(
        '^fiche/([0-9]+)/?$',
        'index.php?post_type=fiche_arret&p=$matches[1]',
        'top'
    );
}
add_action('init', 'aga_add_rewrite_rules');

/**
 * Modifier les permaliens pour utiliser l'ID
 */
function aga_custom_post_link($post_link, $post) {
    if ($post->post_type == 'fiche_arret') {
        return home_url('fiche/' . $post->ID . '/');
    }
    return $post_link;
}
add_filter('post_type_link', 'aga_custom_post_link', 1, 2);

// ============================================================================
// RÔLE PREMIUM
// ============================================================================

/**
 * Créer le rôle premium pour le générateur
 */
function aga_creer_role_premium() {
    add_role('aga_premium', 'Générateur Premium', array(
        'read' => true,
        'aga_premium_access' => true
    ));
}

// ============================================================================
// TABLE AVIS UTILISATEURS
// ============================================================================

/**
 * Créer la table pour les avis utilisateurs (TrustPilot)
 */
function aga_creer_table_avis() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'aga_avis_utilisateurs';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        note tinyint(1) NOT NULL,
        feedback_texte text DEFAULT NULL,
        trustpilot_url varchar(500) DEFAULT NULL,
        credits_attribues tinyint(1) DEFAULT 0,
        date_creation datetime DEFAULT CURRENT_TIMESTAMP,
        date_validation datetime DEFAULT NULL,
        statut varchar(20) DEFAULT 'pending',
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// ============================================================================
// MENUS ADMIN
// ============================================================================
/**
 * Page de validation du catalogue
 */

function aga_page_validation_catalogue() {
    // Traiter la validation/modification
    if (isset($_POST['valider_fiche'])) {
        check_admin_referer('aga_validation_fiche');
        $fiche_id = intval($_POST['valider_fiche']);
        
        // Mettre à jour les références
        update_post_meta($fiche_id, '_aga_references', sanitize_text_field($_POST['refs_' . $fiche_id]));
        update_post_meta($fiche_id, '_aga_statut_catalogue', 'publiable');
        
        echo '<div class="notice notice-success"><p>Fiche validée !</p></div>';
    }
    
    // Pagination
    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 20;
    
    // Compter le total
    $total = count(get_posts(array(
        'post_type' => 'fiche_arret',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_key' => '_aga_statut_catalogue',
        'meta_value' => 'brouillon'
    )));
    
    $fiches = get_posts(array(
        'post_type' => 'fiche_arret',
        'posts_per_page' => $per_page,
        'paged' => $paged,
        'meta_key' => '_aga_statut_catalogue',
        'meta_value' => 'brouillon'
    ));
    
    ?>
    <div class="wrap">
        <h1>Validation Catalogue</h1>
        <p><strong><?php echo $total; ?></strong> fiche(s) en attente de validation</p>
        
        <?php if (!empty($fiches)): ?>
            <form method="post">
                <?php wp_nonce_field('aga_validation_fiche'); ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width:35%">Références</th>
                            <th style="width:20%">Matière</th>
                            <th style="width:15%">Date</th>
                            <th style="width:30%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fiches as $fiche): 
                            $refs = get_post_meta($fiche->ID, '_aga_references', true);
                            $refs_norm = get_post_meta($fiche->ID, '_aga_references_normalized', true);
                            $matiere = get_post_meta($fiche->ID, '_aga_matiere', true);
                            $date = get_post_meta($fiche->ID, '_aga_date_generation', true);
                        ?>
                            <tr>
                                <td>
                                    <strong>Tapées par l'étudiant :</strong><br>
                                    <?php echo esc_html($refs); ?>
                                    <br><br>
                                    <strong>Références suggérées :</strong><br>
                                    <input type="text" name="refs_<?php echo $fiche->ID; ?>" 
                                           value="<?php echo esc_attr($refs_norm ?: $refs); ?>" 
                                           class="widefat">
                                </td>
                                <td><?php echo esc_html($matiere); ?></td>
                                <td><?php echo esc_html($date); ?></td>
                                <td>
                                <a href="<?php echo get_permalink($fiche->ID); ?>" target="_blank" class="button">Voir la fiche</a><br>
                                <button type="submit" name="valider_fiche" value="<?php echo $fiche->ID; ?>" 
                                        class="button button-primary" style="margin-top:8px">
                                    ✓ Valider pour le catalogue
                                </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
            
            <?php
            // Pagination
            $total_pages = ceil($total / $per_page);
            if ($total_pages > 1) {
                echo '<div class="tablenav"><div class="tablenav-pages">';
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'current' => $paged,
                    'total' => $total_pages
                ));
                echo '</div></div>';
            }
            ?>
        <?php else: ?>
            <p>Aucune fiche en attente.</p>
        <?php endif; ?>
    </div>
    <?php
}

// ============================================================================
// API REST POUR LE CATALOGUE
// ============================================================================

/**
 * Créer l'endpoint REST API pour le catalogue public
 */
add_action('rest_api_init', function() {
    register_rest_route('aga/v1', '/fiches', array(
        'methods' => 'GET',
        'callback' => 'aga_api_obtenir_fiches',
        'permission_callback' => '__return_true'
    ));
});

/**
 * Retourner les fiches pour le catalogue
 */
function aga_api_obtenir_fiches($request) {
    $statut = $request->get_param('statut') ?: 'tous';
    
    $args = array(
        'post_type' => 'fiche_arret',
        'post_status' => 'publish',
        'posts_per_page' => 100,
        'orderby' => 'date',
        'order' => 'DESC'
    );
    
    // Filtrer par statut catalogue si demandé
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
        $resultats[] = array(
            'id' => $fiche->ID,
            'references' => get_post_meta($fiche->ID, '_aga_references_normalized', true),
            'references_originales' => get_post_meta($fiche->ID, '_aga_references', true),
            'matiere' => get_post_meta($fiche->ID, '_aga_matiere', true),
            'contenu' => $fiche->post_content,
            'date_generation' => get_post_meta($fiche->ID, '_aga_date_generation', true),
            'statut_catalogue' => get_post_meta($fiche->ID, '_aga_statut_catalogue', true)
        );
    }
    
    return rest_ensure_response($resultats);
}

// ============================================================================
// GESTION DES AVIS UTILISATEURS
// ============================================================================

/**
 * Handler AJAX - Enregistrer avis
 */
function aga_enregistrer_avis() {
    if (!wp_verify_nonce($_POST['nonce'], 'aga_avis_nonce')) {
        wp_send_json_error(array('message' => 'Sécurité'));
        return;
    }
    
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(array('message' => 'Non connecté'));
        return;
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'aga_avis_utilisateurs';
    
    // Pour TrustPilot, éviter les doublons
    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'feedback';
    if ($type === 'trustpilot') {
        $existe = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND statut = 'pending' AND note >= 4",
            $user_id
        ));
        if ($existe > 0) {
            wp_send_json_success(array('message' => 'Déjà enregistré'));
            return;
        }
    }
    
    $wpdb->insert($table, array(
        'user_id' => $user_id,
        'note' => intval($_POST['note']),
        'feedback_texte' => isset($_POST['feedback']) ? sanitize_textarea_field($_POST['feedback']) : null,
        'date_creation' => current_time('mysql'),
        'statut' => 'pending'
    ));
    
    wp_send_json_success(array('message' => 'OK'));
}
add_action('wp_ajax_aga_enregistrer_avis', 'aga_enregistrer_avis');
