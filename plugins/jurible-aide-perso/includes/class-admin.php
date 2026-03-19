<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Jaide_Admin {

    public static function add_menu() {
        add_menu_page(
            'Aide Perso',
            'Aide Perso',
            'manage_options',
            'jaide-inbox',
            [ self::class, 'render_inbox' ],
            'dashicons-format-chat',
            32
        );

        add_submenu_page(
            'jaide-inbox',
            'Inbox',
            'Inbox',
            'manage_options',
            'jaide-inbox',
            [ self::class, 'render_inbox' ]
        );

        add_submenu_page(
            'jaide-inbox',
            'Crédits',
            'Crédits',
            'manage_options',
            'jaide-credits',
            [ self::class, 'render_credits' ]
        );

        add_submenu_page(
            'jaide-inbox',
            'Paramètres',
            'Paramètres',
            'manage_options',
            'jaide-settings',
            [ self::class, 'render_settings' ]
        );

        // Page cachée : détail/réponse
        add_submenu_page(
            null,
            'Détail demande',
            'Détail',
            'manage_options',
            'jaide-detail',
            [ self::class, 'render_detail' ]
        );
    }

    public static function enqueue_scripts( $hook ) {
        if ( strpos( $hook, 'jaide-' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'jaide-admin',
            JAIDE_URL . 'admin/css/admin.css',
            [],
            JAIDE_VERSION
        );

        wp_enqueue_script(
            'jaide-admin',
            JAIDE_URL . 'admin/js/admin.js',
            [],
            JAIDE_VERSION,
            true
        );

        wp_localize_script( 'jaide-admin', 'jaideAdmin', [
            'restUrl'  => esc_url_raw( rest_url( 'aide-perso/v1' ) ),
            'adminUrl' => admin_url( 'admin.php' ),
            'nonce'    => wp_create_nonce( 'wp_rest' ),
        ] );
    }

    public static function render_inbox() {
        include JAIDE_PATH . 'admin/views/inbox.php';
    }

    public static function render_detail() {
        include JAIDE_PATH . 'admin/views/detail.php';
    }

    public static function render_credits() {
        // Sauvegarder les crédits
        if ( isset( $_POST['jaide_save_credits'] ) && check_admin_referer( 'jaide_save_credits' ) ) {
            $uid = intval( $_POST['user_id'] ?? 0 );
            if ( $uid ) {
                // Copies : vide = supprimer l'override (retour à la globale)
                $copies_val = $_POST['copies_limit'] ?? '';
                if ( $copies_val === '' ) {
                    delete_user_meta( $uid, 'jaide_copies_limit' );
                } else {
                    update_user_meta( $uid, 'jaide_copies_limit', max( 0, intval( $copies_val ) ) );
                }

                // Questions : idem
                $questions_val = $_POST['questions_limit'] ?? '';
                if ( $questions_val === '' ) {
                    delete_user_meta( $uid, 'jaide_questions_limit' );
                } else {
                    update_user_meta( $uid, 'jaide_questions_limit', max( 0, intval( $questions_val ) ) );
                }

                echo '<div class="notice notice-success"><p>Crédits mis à jour.</p></div>';
            }
        }

        include JAIDE_PATH . 'admin/views/credits.php';
    }

    public static function render_settings() {
        // Save settings
        if ( isset( $_POST['jaide_save_settings'] ) && check_admin_referer( 'jaide_save_settings' ) ) {
            $options = [
                'product_id'           => sanitize_text_field( $_POST['product_id'] ?? '' ),
                'copies_limit'         => max( 0, intval( $_POST['copies_limit'] ?? 1 ) ),
                'questions_limit'      => max( 0, intval( $_POST['questions_limit'] ?? 0 ) ),
                'notify_prof_new'      => ! empty( $_POST['notify_prof_new'] ),
                'notify_student_reply' => ! empty( $_POST['notify_student_reply'] ),
                'from_name'            => sanitize_text_field( $_POST['from_name'] ?? get_bloginfo( 'name' ) ),
                'from_email'           => sanitize_email( $_POST['from_email'] ?? get_option( 'admin_email' ) ),
            ];
            update_option( 'jaide_options', $options );
            echo '<div class="notice notice-success"><p>Paramètres enregistrés.</p></div>';
        }

        include JAIDE_PATH . 'admin/views/settings.php';
    }
}
