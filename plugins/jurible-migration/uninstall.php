<?php
/**
 * Uninstall - Supprime toutes les données du plugin
 */

defined('WP_UNINSTALL_PLUGIN') || exit;

// Supprimer toutes les options
delete_option('jurible_migration_status');

// Supprimer les transients
delete_transient('jurible_source_posts_cache');

// Aucune table custom à supprimer
// Aucun fichier à supprimer (les articles migrés restent)
