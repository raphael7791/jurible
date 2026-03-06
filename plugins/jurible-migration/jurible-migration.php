<?php
/**
 * Plugin Name: Jurible Migration
 * Description: Migration des articles de aideauxtd.com vers jurible.com
 * Version: 1.0.0
 * Author: Jurible
 * Text Domain: jurible-migration
 */

defined('ABSPATH') || exit;

define('JURIBLE_MIGRATION_VERSION', '1.0.0');
define('JURIBLE_MIGRATION_PATH', plugin_dir_path(__FILE__));
define('JURIBLE_MIGRATION_URL', plugin_dir_url(__FILE__));

// Chemins sur le serveur O2switch
define('JURIBLE_AIDEAUXTD_PATH', '/home/aideauxtd/public_html');
// Le site destination est le site courant (là où le plugin est installé)

class Jurible_Migration {

    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->includes();
        $this->hooks();
    }

    private function includes() {
        require_once JURIBLE_MIGRATION_PATH . 'includes/class-converter.php';
        require_once JURIBLE_MIGRATION_PATH . 'includes/class-migrator.php';
        require_once JURIBLE_MIGRATION_PATH . 'includes/class-admin-page.php';
    }

    private function hooks() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_jurible_migrate_post', [$this, 'ajax_migrate_post']);
        add_action('wp_ajax_jurible_get_source_posts', [$this, 'ajax_get_source_posts']);
    }

    public function add_admin_menu() {
        add_menu_page(
            'Migration Aideauxtd',
            'Migration',
            'manage_options',
            'jurible-migration',
            [Jurible_Migration_Admin_Page::class, 'render'],
            'dashicons-migrate',
            30
        );
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'toplevel_page_jurible-migration') {
            return;
        }

        wp_enqueue_style(
            'jurible-migration-admin',
            JURIBLE_MIGRATION_URL . 'assets/admin.css',
            [],
            JURIBLE_MIGRATION_VERSION
        );

        wp_enqueue_script(
            'jurible-migration-admin',
            JURIBLE_MIGRATION_URL . 'assets/admin.js',
            ['jquery'],
            JURIBLE_MIGRATION_VERSION,
            true
        );

        wp_localize_script('jurible-migration-admin', 'juribleMigration', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('jurible_migration_nonce'),
        ]);
    }

    public function ajax_migrate_post() {
        check_ajax_referer('jurible_migration_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission refusée');
        }

        $source_post_id = intval($_POST['post_id'] ?? 0);
        if (!$source_post_id) {
            wp_send_json_error('ID article invalide');
        }

        $migrator = new Jurible_Migration_Migrator();
        $result = $migrator->migrate($source_post_id);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        // Sauvegarder le statut
        $this->mark_as_migrated($source_post_id, $result);

        wp_send_json_success([
            'message' => 'Article migré avec succès',
            'new_post_id' => $result,
            'edit_url' => admin_url('post.php?post=' . $result . '&action=edit'),
        ]);
    }

    public function ajax_get_source_posts() {
        check_ajax_referer('jurible_migration_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission refusée');
        }

        $posts = $this->get_source_posts();
        $migrated = get_option('jurible_migration_status', []);

        wp_send_json_success([
            'posts' => $posts,
            'migrated' => $migrated,
        ]);
    }

    private function get_source_posts() {
        $cache_key = 'jurible_source_posts_cache';
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        // Récupérer la liste via WP-CLI
        $command = sprintf(
            'cd %s && wp post list --post_type=post --post_status=publish --fields=ID,post_title,post_date --format=json --allow-root 2>/dev/null',
            escapeshellarg(JURIBLE_AIDEAUXTD_PATH)
        );

        $output = shell_exec($command);
        $posts = json_decode($output, true) ?: [];

        // Cache pour 1 heure
        set_transient($cache_key, $posts, HOUR_IN_SECONDS);

        return $posts;
    }

    private function mark_as_migrated($source_id, $new_id) {
        $status = get_option('jurible_migration_status', []);
        $status[$source_id] = [
            'new_id' => $new_id,
            'date' => current_time('mysql'),
        ];
        update_option('jurible_migration_status', $status);
    }

    public static function get_migration_status() {
        return get_option('jurible_migration_status', []);
    }
}

// Initialiser le plugin
add_action('plugins_loaded', function() {
    Jurible_Migration::instance();
});

// Activation
register_activation_hook(__FILE__, function() {
    add_option('jurible_migration_status', []);
});

// Désactivation - ne supprime rien
register_deactivation_hook(__FILE__, function() {
    // Ne rien faire, garder les données
});
