<?php
/**
 * Plugin Name: Jurible — Se connecter en tant que
 * Description: Ajoute un lien "Se connecter en tant que" dans la liste des utilisateurs WP admin. Plugin temporaire à supprimer après tests.
 * Version: 1.1
 * Author: Jurible
 */

if (!defined('ABSPATH')) exit;

// Ajoute le lien dans la colonne actions de la liste users
add_filter('user_row_actions', function($actions, $user) {
    if (!current_user_can('manage_options')) return $actions;
    if ($user->ID === get_current_user_id()) return $actions;

    $url = wp_nonce_url(
        admin_url('admin-post.php?action=jurible_login_as&user_id=' . $user->ID),
        'jurible_login_as_' . $user->ID
    );
    $actions['login_as'] = '<a href="' . esc_url($url) . '" style="color:#2271b1;font-weight:600;">Se connecter en tant que</a>';
    return $actions;
}, 10, 2);

// Gère la connexion
add_action('admin_post_jurible_login_as', function() {
    if (!current_user_can('manage_options')) wp_die('Non autorisé');

    $user_id = intval($_GET['user_id'] ?? 0);
    if (!$user_id) wp_die('User ID manquant');

    check_admin_referer('jurible_login_as_' . $user_id);

    $user = get_user_by('id', $user_id);
    if (!$user) wp_die('Utilisateur introuvable');

    $admin_id = get_current_user_id();

    wp_clear_auth_cookie();
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    set_transient('jurible_login_as_admin_' . $user_id, $admin_id, HOUR_IN_SECONDS);

    wp_redirect(home_url('/'));
    exit;
});

// Bouton flottant "Revenir admin" visible en front pour tous les users
add_action('wp_footer', function() {
    $current_user_id = get_current_user_id();
    $admin_id = get_transient('jurible_login_as_admin_' . $current_user_id);
    if (!$admin_id) return;

    $url = wp_nonce_url(
        admin_url('admin-post.php?action=jurible_return_admin'),
        'jurible_return_admin'
    );

    echo '<a href="' . esc_url($url) . '" style="
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #d63638;
        color: #fff;
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        z-index: 999999;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        font-family: -apple-system, BlinkMacSystemFont, sans-serif;
    ">← Revenir admin</a>';
});

// Aussi dans la barre admin si visible
add_action('admin_bar_menu', function($wp_admin_bar) {
    $current_user_id = get_current_user_id();
    $admin_id = get_transient('jurible_login_as_admin_' . $current_user_id);
    if (!$admin_id) return;

    $url = wp_nonce_url(
        admin_url('admin-post.php?action=jurible_return_admin'),
        'jurible_return_admin'
    );

    $wp_admin_bar->add_node([
        'id'    => 'jurible-return-admin',
        'title' => '← Revenir admin',
        'href'  => $url,
    ]);
}, 999);

// Retour admin
add_action('admin_post_jurible_return_admin', function() {
    check_admin_referer('jurible_return_admin');
    $current_user_id = get_current_user_id();
    $admin_id = get_transient('jurible_login_as_admin_' . $current_user_id);
    if (!$admin_id) wp_die('Pas de session admin sauvegardée');

    delete_transient('jurible_login_as_admin_' . $current_user_id);
    wp_clear_auth_cookie();
    wp_set_current_user($admin_id);
    wp_set_auth_cookie($admin_id);

    wp_redirect(admin_url('users.php'));
    exit;
});
