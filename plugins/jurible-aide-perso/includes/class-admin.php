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
        // Donner / retirer l'accès aide perso
        if ( isset( $_POST['jaide_toggle_access'] ) && check_admin_referer( 'jaide_toggle_access' ) ) {
            $uid    = intval( $_POST['user_id'] ?? 0 );
            $action = sanitize_text_field( $_POST['jaide_toggle_access'] );

            if ( $uid ) {
                if ( $action === 'grant' ) {
                    update_user_meta( $uid, 'jam_aide_perso_access', '1' );
                    // Poser les limites par défaut (5Q / 1C) si pas déjà présentes
                    if ( ! get_user_meta( $uid, 'jam_aide_perso_questions_limit', true ) ) {
                        update_user_meta( $uid, 'jam_aide_perso_questions_limit', 5 );
                    }
                    if ( ! get_user_meta( $uid, 'jam_aide_perso_copies_limit', true ) ) {
                        update_user_meta( $uid, 'jam_aide_perso_copies_limit', 1 );
                    }
                    echo '<div class="notice notice-success"><p>Accès aide personnalisée activé (5 questions, 1 copie).</p></div>';
                } elseif ( $action === 'revoke' ) {
                    delete_user_meta( $uid, 'jam_aide_perso_access' );
                    delete_user_meta( $uid, 'jam_aide_perso_copies_limit' );
                    delete_user_meta( $uid, 'jam_aide_perso_questions_limit' );
                    // Supprimer aussi les overrides individuels
                    delete_user_meta( $uid, 'jaide_copies_limit' );
                    delete_user_meta( $uid, 'jaide_questions_limit' );
                    echo '<div class="notice notice-success"><p>Accès aide personnalisée retiré.</p></div>';
                }
            }
        }

        // Ajouter un crédit (bouton +1) — annule une conso manuelle ou augmente la limite
        if ( isset( $_POST['jaide_add'] ) && check_admin_referer( 'jaide_add_credit' ) ) {
            $uid  = intval( $_POST['user_id'] ?? 0 );
            $type = sanitize_text_field( $_POST['jaide_add'] );

            if ( $uid && in_array( $type, [ 'question', 'copie' ], true ) ) {
                $manual_key    = $type === 'copie' ? 'jaide_copies_manual_used' : 'jaide_questions_manual_used';
                $manual_used   = max( 0, (int) get_user_meta( $uid, $manual_key, true ) );

                if ( $manual_used > 0 ) {
                    // Annuler une consommation manuelle
                    update_user_meta( $uid, $manual_key, $manual_used - 1 );
                } else {
                    // Augmenter la limite
                    $limit_key     = $type === 'copie' ? 'jaide_copies_limit' : 'jaide_questions_limit';
                    $current_limit = $type === 'copie'
                        ? Jaide_Access::get_copies_limit( $uid )
                        : Jaide_Access::get_questions_limit( $uid );
                    update_user_meta( $uid, $limit_key, $current_limit + 1 );
                }

                $remaining = $type === 'copie'
                    ? Jaide_Access::copies_remaining( $uid )
                    : Jaide_Access::questions_remaining( $uid );

                $label = $type === 'copie' ? 'copie' : 'question';
                echo '<div class="notice notice-success"><p>1 ' . $label . ' ajoutée. Solde : ' . $remaining . ' restante(s).</p></div>';
            }
        }

        // Retirer un crédit (bouton -1) — incrémente le compteur de consommation manuelle
        if ( isset( $_POST['jaide_deduct'] ) && check_admin_referer( 'jaide_deduct_credit' ) ) {
            $uid  = intval( $_POST['user_id'] ?? 0 );
            $type = sanitize_text_field( $_POST['jaide_deduct'] );

            if ( $uid && in_array( $type, [ 'question', 'copie' ], true ) ) {
                $remaining = $type === 'copie'
                    ? Jaide_Access::copies_remaining( $uid )
                    : Jaide_Access::questions_remaining( $uid );

                if ( $remaining > 0 ) {
                    $manual_key = $type === 'copie' ? 'jaide_copies_manual_used' : 'jaide_questions_manual_used';
                    $current    = max( 0, (int) get_user_meta( $uid, $manual_key, true ) );
                    update_user_meta( $uid, $manual_key, $current + 1 );

                    $label = $type === 'copie' ? 'copie' : 'question';
                    echo '<div class="notice notice-success"><p>1 ' . $label . ' retirée. Nouveau solde : ' . max( 0, $remaining - 1 ) . ' restante(s).</p></div>';
                } else {
                    echo '<div class="notice notice-warning"><p>Aucun crédit restant à retirer.</p></div>';
                }
            }
        }

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
            $existing = get_option( 'jaide_options', [] );

            $options = [
                'product_id'           => $existing['product_id'] ?? '', // Conservé pour rétrocompatibilité
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
