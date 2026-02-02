<?php
/**
 * Admin Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

class Jurible_Playlist_Admin {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_admin_menu() {
        add_options_page(
            'Jurible Playlist',
            'Jurible Playlist',
            'manage_options',
            'jurible-playlist',
            array($this, 'render_settings_page')
        );
    }

    public function register_settings() {
        register_setting('jurible_playlist_settings', 'jurible_playlist_library_id', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '35843'
        ));

        register_setting('jurible_playlist_settings', 'jurible_playlist_api_key', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ));

        register_setting('jurible_playlist_settings', 'jurible_playlist_pull_zone_url', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => 'https://iframe.mediadelivery.net/embed/35843/'
        ));

        register_setting('jurible_playlist_settings', 'jurible_playlist_thumbnail_url', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => ''
        ));

        register_setting('jurible_playlist_settings', 'jurible_playlist_token_key', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ));

        add_settings_section(
            'jurible_playlist_bunny_section',
            'Configuration Bunny Stream',
            array($this, 'render_section_description'),
            'jurible-playlist'
        );

        add_settings_field(
            'jurible_playlist_library_id',
            'Library ID',
            array($this, 'render_library_id_field'),
            'jurible-playlist',
            'jurible_playlist_bunny_section'
        );

        add_settings_field(
            'jurible_playlist_api_key',
            'API Key',
            array($this, 'render_api_key_field'),
            'jurible-playlist',
            'jurible_playlist_bunny_section'
        );

        add_settings_field(
            'jurible_playlist_pull_zone_url',
            'Pull Zone URL',
            array($this, 'render_pull_zone_field'),
            'jurible-playlist',
            'jurible_playlist_bunny_section'
        );

        add_settings_field(
            'jurible_playlist_thumbnail_url',
            'Thumbnail Base URL',
            array($this, 'render_thumbnail_url_field'),
            'jurible-playlist',
            'jurible_playlist_bunny_section'
        );

        add_settings_field(
            'jurible_playlist_token_key',
            'Token Key (URL Signing)',
            array($this, 'render_token_key_field'),
            'jurible-playlist',
            'jurible_playlist_bunny_section'
        );
    }

    public function render_section_description() {
        echo '<p>Configurez vos identifiants Bunny Stream pour permettre l\'affichage des playlists vidéo.</p>';
    }

    public function render_library_id_field() {
        $value = get_option('jurible_playlist_library_id', '35843');
        echo '<input type="text" name="jurible_playlist_library_id" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<p class="description">L\'identifiant de votre bibliothèque Bunny Stream.</p>';
    }

    public function render_api_key_field() {
        $value = get_option('jurible_playlist_api_key', '');
        echo '<input type="password" name="jurible_playlist_api_key" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<p class="description">Votre clé API Bunny Stream (Stream > API).</p>';
    }

    public function render_pull_zone_field() {
        $value = get_option('jurible_playlist_pull_zone_url', 'https://iframe.mediadelivery.net/embed/35843/');
        echo '<input type="url" name="jurible_playlist_pull_zone_url" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<p class="description">L\'URL de base pour l\'iframe du player (incluant le Library ID).</p>';
    }

    public function render_thumbnail_url_field() {
        $value = get_option('jurible_playlist_thumbnail_url', '');

        // Try to detect from API
        $detected = '';
        $api_key = get_option('jurible_playlist_api_key', '');
        if (!empty($api_key)) {
            $bunny_api = new Jurible_Playlist_Bunny_API();
            $library = $bunny_api->get_library_info();
            if (isset($library['PullZone']) && !empty($library['PullZone'])) {
                $detected = 'https://' . $library['PullZone'] . '.b-cdn.net';
            }
        }

        echo '<input type="url" name="jurible_playlist_thumbnail_url" value="' . esc_attr($value) . '" class="regular-text" placeholder="https://vz-xxxxx-xxx.b-cdn.net">';

        if (!empty($detected)) {
            echo '<p class="description" style="color: #10B981;"><strong>Détecté automatiquement :</strong> ' . esc_html($detected) . '</p>';
            echo '<p class="description">Laissez vide pour utiliser l\'URL détectée, ou saisissez une URL personnalisée.</p>';
        } else {
            echo '<p class="description">L\'URL de base pour les miniatures. Trouvez-la dans Bunny Stream > Library > Hostname.</p>';
        }
    }

    public function render_token_key_field() {
        $value = get_option('jurible_playlist_token_key', '');
        echo '<input type="password" name="jurible_playlist_token_key" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<p class="description">Clé pour signer les URLs (si Token Authentication est activé sur votre Pull Zone).</p>';
        echo '<p class="description">Trouvez-la dans Bunny Stream > Library > Security > Token Authentication Key.</p>';
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <form action="options.php" method="post">
                <?php
                settings_fields('jurible_playlist_settings');
                do_settings_sections('jurible-playlist');
                submit_button('Enregistrer');
                ?>
            </form>

            <hr>

            <h2>Utilisation</h2>
            <p>Utilisez le shortcode suivant pour afficher une playlist :</p>
            <code>[bunny_playlist collection="COLLECTION_ID"]</code>

            <h3>Exemple</h3>
            <code>[bunny_playlist collection="f53a2ad0-2ffe-430f-abd8-6004bac5751c"]</code>

            <hr>

            <h2>Test de connexion</h2>
            <?php $this->render_connection_test(); ?>
        </div>
        <?php
    }

    private function render_connection_test() {
        $api_key = get_option('jurible_playlist_api_key', '');

        if (empty($api_key)) {
            echo '<p class="notice notice-warning" style="padding: 10px;">Veuillez configurer votre clé API pour tester la connexion.</p>';
            return;
        }

        $bunny_api = new Jurible_Playlist_Bunny_API();

        // Try to fetch a small amount of data to test connection
        $library_id = get_option('jurible_playlist_library_id', '35843');
        $url = "https://video.bunnycdn.com/library/{$library_id}/videos?page=1&itemsPerPage=1";

        $response = wp_remote_get($url, array(
            'headers' => array(
                'AccessKey' => $api_key,
                'Accept' => 'application/json'
            ),
            'timeout' => 10
        ));

        if (is_wp_error($response)) {
            echo '<div class="notice notice-error" style="padding: 10px;"><strong>Erreur:</strong> ' . esc_html($response->get_error_message()) . '</div>';
            return;
        }

        $code = wp_remote_retrieve_response_code($response);

        if ($code === 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $total = isset($body['totalItems']) ? $body['totalItems'] : 0;
            echo '<div class="notice notice-success" style="padding: 10px;"><strong>Connexion réussie!</strong> ' . $total . ' vidéo(s) trouvée(s) dans la bibliothèque.</div>';
        } elseif ($code === 401) {
            echo '<div class="notice notice-error" style="padding: 10px;"><strong>Erreur 401:</strong> Clé API invalide.</div>';
        } else {
            echo '<div class="notice notice-error" style="padding: 10px;"><strong>Erreur ' . $code . ':</strong> Vérifiez vos paramètres.</div>';
        }
    }
}
