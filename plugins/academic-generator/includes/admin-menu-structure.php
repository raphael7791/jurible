<?php
/**
 * Structure du menu admin consolidée
 * Regroupe tous les menus du plugin sous un seul menu principal
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Créer le menu principal avec tous les sous-menus
 */
function aga_creer_menu_admin() {
    // Menu principal
    add_menu_page(
        'Générateur Académique',           // Titre de la page
        'Générateur Académique',           // Titre du menu
        'manage_options',                  // Capacité requise
        'aga-dashboard',                   // Slug du menu
        'aga_afficher_dashboard_stats',    // Fonction callback (page stats)
        'dashicons-welcome-learn-more',    // Icône
        25                                 // Position
    );
    
    // Sous-menu : Statistiques (remplace le menu principal)
    add_submenu_page(
        'aga-dashboard',
        'Statistiques',
        '📊 Statistiques',
        'manage_options',
        'aga-dashboard',
        'aga_afficher_dashboard_stats'
    );
    
    // Sous-menu : Fiches d'Arrêt
    add_submenu_page(
        'aga-dashboard',
        'Fiches d\'Arrêt',
        '📄 Fiches d\'Arrêt',
        'manage_options',
        'edit.php?post_type=fiche_arret'
    );
    
    // Sous-menu : Dissertations
    add_submenu_page(
        'aga-dashboard',
        'Dissertations',
        '✍️ Dissertations',
        'manage_options',
        'edit.php?post_type=dissertation'
    );
    
    // Sous-menu : Commentaires d'Arrêt
    add_submenu_page(
        'aga-dashboard',
        'Commentaires d\'Arrêt',
        '💬 Commentaires',
        'manage_options',
        'edit.php?post_type=commentaire_arret'
    );
    
    // Sous-menu : Cas Pratiques
    add_submenu_page(
        'aga-dashboard',
        'Cas Pratiques',
        '📋 Cas Pratiques',
        'manage_options',
        'edit.php?post_type=cas_pratique'
    );
    
    // Sous-menu : Validation Catalogue
    add_submenu_page(
        'aga-dashboard',
        'Validation Catalogue',
        '✅ Validation Catalogue',
        'manage_options',
        'aga-validation-catalogue',
        'aga_page_validation_catalogue'
    );
    
    // Sous-menu : Publication Catalogue (vers site principal)
add_submenu_page(
    'aga-dashboard',
    'Publication Catalogue',
    '📤 Publication Catalogue',
    'manage_options',
    'aga-publication-catalogue',
    'aga_page_publication_catalogue'
);

    // Sous-menu : Avis TrustPilot
    add_submenu_page(
        'aga-dashboard',
        'Avis TrustPilot',
        '⭐ Avis TrustPilot',
        'manage_options',
        'aga-avis-trustpilot',
        'aga_page_avis_trustpilot'
    );
}
add_action('admin_menu', 'aga_creer_menu_admin');

/**
 * Masquer les menus par défaut des CPT dans la sidebar
 * (ils seront accessibles via le menu consolidé)
 */
function aga_masquer_menus_cpt_defaut() {
    remove_menu_page('edit.php?post_type=fiche_arret');
    remove_menu_page('edit.php?post_type=dissertation');
    remove_menu_page('edit.php?post_type=commentaire_arret');
    remove_menu_page('edit.php?post_type=cas_pratique');
}
add_action('admin_menu', 'aga_masquer_menus_cpt_defaut', 999);