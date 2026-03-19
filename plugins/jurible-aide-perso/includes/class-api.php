<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Jaide_API {

    const NAMESPACE = 'aide-perso/v1';

    public static function register_routes() {
        // POST /submit — Soumettre question ou copie
        register_rest_route( self::NAMESPACE, '/submit', [
            'methods'             => 'POST',
            'callback'            => [ self::class, 'submit' ],
            'permission_callback' => [ self::class, 'check_user_has_access' ],
        ] );

        // GET /my-requests — Historique étudiant
        register_rest_route( self::NAMESPACE, '/my-requests', [
            'methods'             => 'GET',
            'callback'            => [ self::class, 'get_my_requests' ],
            'permission_callback' => [ self::class, 'check_logged_in' ],
        ] );

        // GET /my-requests/{id} — Détail d'une de mes demandes (avec réponse)
        register_rest_route( self::NAMESPACE, '/my-requests/(?P<id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [ self::class, 'get_my_request_detail' ],
            'permission_callback' => [ self::class, 'check_logged_in' ],
        ] );

        // GET /requests — Admin : toutes les demandes
        register_rest_route( self::NAMESPACE, '/requests', [
            'methods'             => 'GET',
            'callback'            => [ self::class, 'get_requests' ],
            'permission_callback' => [ self::class, 'check_admin' ],
        ] );

        // GET /requests/{id} — Admin : détail
        register_rest_route( self::NAMESPACE, '/requests/(?P<id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [ self::class, 'get_request' ],
            'permission_callback' => [ self::class, 'check_admin' ],
        ] );

        // POST /requests/{id}/claim — Admin : prendre en charge
        register_rest_route( self::NAMESPACE, '/requests/(?P<id>\d+)/claim', [
            'methods'             => 'POST',
            'callback'            => [ self::class, 'claim' ],
            'permission_callback' => [ self::class, 'check_admin' ],
        ] );

        // POST /requests/{id}/respond — Admin : répondre
        register_rest_route( self::NAMESPACE, '/requests/(?P<id>\d+)/respond', [
            'methods'             => 'POST',
            'callback'            => [ self::class, 'respond' ],
            'permission_callback' => [ self::class, 'check_admin' ],
        ] );
    }

    // ── Permissions ─────────────────────────────────────────────────────────

    public static function check_logged_in() {
        return is_user_logged_in();
    }

    public static function check_admin() {
        return current_user_can( 'manage_options' );
    }

    public static function check_user_has_access() {
        return is_user_logged_in() && Jaide_Access::user_has_access();
    }

    // ── POST /submit ────────────────────────────────────────────────────────

    public static function submit( $request ) {
        global $wpdb;
        $table   = $wpdb->prefix . 'jurible_aide_requests';
        $user_id = get_current_user_id();

        $type    = sanitize_text_field( $request->get_param( 'type' ) );
        $nom     = sanitize_text_field( $request->get_param( 'nom' ) );
        $email   = sanitize_email( $request->get_param( 'email' ) );
        $annee   = sanitize_text_field( $request->get_param( 'annee' ) );
        $matiere = sanitize_text_field( $request->get_param( 'matiere' ) );
        $message = sanitize_textarea_field( $request->get_param( 'message' ) );

        // Validation type
        if ( ! in_array( $type, [ 'question', 'copie' ], true ) ) {
            return new WP_Error( 'invalid_type', 'Type invalide', [ 'status' => 400 ] );
        }

        // Validation année
        if ( ! in_array( $annee, [ 'L1', 'L2', 'L3', 'Capacite' ], true ) ) {
            return new WP_Error( 'invalid_annee', 'Année invalide', [ 'status' => 400 ] );
        }

        // Validation champs requis
        if ( empty( $nom ) || empty( $email ) || empty( $matiere ) ) {
            return new WP_Error( 'missing_fields', 'Nom, email et matière sont requis', [ 'status' => 400 ] );
        }

        // Pour les questions, le message est requis
        if ( $type === 'question' && empty( $message ) ) {
            return new WP_Error( 'missing_message', 'La question est requise', [ 'status' => 400 ] );
        }

        // Vérifier les limites
        if ( $type === 'copie' ) {
            $remaining = Jaide_Access::copies_remaining( $user_id );
            if ( $remaining <= 0 ) {
                return new WP_Error( 'limit_reached', 'Vous avez atteint la limite de corrections de copies', [ 'status' => 403 ] );
            }
        } else {
            $remaining = Jaide_Access::questions_remaining( $user_id );
            if ( $remaining <= 0 ) {
                return new WP_Error( 'limit_reached', 'Vous avez atteint la limite de questions', [ 'status' => 403 ] );
            }
        }

        // Gérer le fichier (optionnel pour question, requis pour copie)
        $file_url  = null;
        $file_name = null;
        $files     = $request->get_file_params();

        if ( ! empty( $files['file'] ) ) {
            $upload_result = self::handle_upload( $files['file'], $user_id, $type );
            if ( is_wp_error( $upload_result ) ) {
                return $upload_result;
            }
            $file_url  = $upload_result['url'];
            $file_name = $upload_result['name'];
        } elseif ( $type === 'copie' ) {
            return new WP_Error( 'file_required', 'Un fichier est requis pour une copie', [ 'status' => 400 ] );
        }

        // Insérer en base
        $wpdb->insert( $table, [
            'user_id'    => $user_id,
            'type'       => $type,
            'nom'        => $nom,
            'email'      => $email,
            'annee'      => $annee,
            'matiere'    => $matiere,
            'message'    => $message,
            'file_url'   => $file_url,
            'file_name'  => $file_name,
            'status'     => 'pending',
            'created_at' => current_time( 'mysql' ),
        ] );

        $request_id = $wpdb->insert_id;

        // Notification au prof
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $request_id ) );
        Jaide_Notifications::send_new_request( $row );

        return rest_ensure_response( [
            'success' => true,
            'message' => $type === 'question'
                ? 'Votre question a bien été envoyée !'
                : 'Votre copie a bien été déposée !',
            'data'    => [
                'id'   => $request_id,
                'type' => $type,
            ],
        ] );
    }

    // ── GET /my-requests/{id} ───────────────────────────────────────────

    public static function get_my_request_detail( $request ) {
        global $wpdb;
        $table   = $wpdb->prefix . 'jurible_aide_requests';
        $user_id = get_current_user_id();
        $id      = intval( $request->get_param( 'id' ) );

        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT id, type, matiere, annee, message, file_url, file_name, status, response, response_file_url, created_at, responded_at
             FROM $table WHERE id = %d AND user_id = %d",
            $id,
            $user_id
        ) );

        if ( ! $row ) {
            return new WP_Error( 'not_found', 'Demande non trouvée', [ 'status' => 404 ] );
        }

        return rest_ensure_response( [
            'success' => true,
            'data'    => $row,
        ] );
    }

    /**
     * Upload un fichier et retourne url + nom.
     */
    private static function handle_upload( $file, $user_id, $type ) {
        $allowed_types = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.oasis.opendocument.text',
        ];
        $allowed_ext = [ 'pdf', 'docx', 'odt' ];

        $file_ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

        if ( ! in_array( $file['type'], $allowed_types, true ) && ! in_array( $file_ext, $allowed_ext, true ) ) {
            return new WP_Error( 'invalid_file_type', 'Formats acceptés : PDF, DOCX, ODT', [ 'status' => 400 ] );
        }

        if ( $file['size'] > 20 * 1024 * 1024 ) {
            return new WP_Error( 'file_too_large', 'Fichier trop volumineux (max 20 Mo)', [ 'status' => 400 ] );
        }

        $upload_dir = wp_upload_dir();
        $year       = gmdate( 'Y' );
        $target_dir = $upload_dir['basedir'] . '/jurible-aide-perso/' . $year . '/' . $user_id;

        if ( ! file_exists( $target_dir ) ) {
            wp_mkdir_p( $target_dir );
        }

        $user     = get_userdata( $user_id );
        $safe     = sanitize_file_name( $user->user_login );
        $filename = $safe . '-' . $type . '-' . time() . '.' . $file_ext;
        $filepath = $target_dir . '/' . $filename;

        if ( ! move_uploaded_file( $file['tmp_name'], $filepath ) ) {
            return new WP_Error( 'upload_failed', 'Erreur lors de l\'upload', [ 'status' => 500 ] );
        }

        return [
            'url'  => $upload_dir['baseurl'] . '/jurible-aide-perso/' . $year . '/' . $user_id . '/' . $filename,
            'name' => $file['name'],
        ];
    }

    // ── GET /my-requests ────────────────────────────────────────────────────

    public static function get_my_requests( $request ) {
        global $wpdb;
        $table   = $wpdb->prefix . 'jurible_aide_requests';
        $user_id = get_current_user_id();

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT id, type, matiere, annee, status, created_at, responded_at FROM $table WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ) );

        return rest_ensure_response( [
            'success' => true,
            'data'    => $results,
        ] );
    }

    // ── GET /requests (admin) ───────────────────────────────────────────────

    public static function get_requests( $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'jurible_aide_requests';

        $where  = '1=1';
        $params = [];

        if ( $request->get_param( 'type' ) ) {
            $where   .= ' AND type = %s';
            $params[] = sanitize_text_field( $request->get_param( 'type' ) );
        }

        if ( $request->get_param( 'status' ) ) {
            $where   .= ' AND status = %s';
            $params[] = sanitize_text_field( $request->get_param( 'status' ) );
        }

        $sql = "SELECT r.*, u.display_name as user_display_name
                FROM $table r
                JOIN {$wpdb->users} u ON r.user_id = u.ID
                WHERE $where
                ORDER BY r.created_at DESC";

        if ( ! empty( $params ) ) {
            $sql = $wpdb->prepare( $sql, $params );
        }

        return rest_ensure_response( [
            'success' => true,
            'data'    => $wpdb->get_results( $sql ),
        ] );
    }

    // ── GET /requests/{id} (admin) ──────────────────────────────────────────

    public static function get_request( $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'jurible_aide_requests';
        $id    = intval( $request->get_param( 'id' ) );

        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT r.*, u.display_name as user_display_name
             FROM $table r
             JOIN {$wpdb->users} u ON r.user_id = u.ID
             WHERE r.id = %d",
            $id
        ) );

        if ( ! $row ) {
            return new WP_Error( 'not_found', 'Demande non trouvée', [ 'status' => 404 ] );
        }

        return rest_ensure_response( [
            'success' => true,
            'data'    => $row,
        ] );
    }

    // ── POST /requests/{id}/claim ───────────────────────────────────────────

    public static function claim( $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'jurible_aide_requests';
        $id    = intval( $request->get_param( 'id' ) );

        $wpdb->update(
            $table,
            [
                'status'      => 'in_progress',
                'assigned_to' => get_current_user_id(),
            ],
            [ 'id' => $id ]
        );

        return rest_ensure_response( [
            'success' => true,
            'message' => 'Demande prise en charge',
        ] );
    }

    // ── POST /requests/{id}/respond ─────────────────────────────────────────

    public static function respond( $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'jurible_aide_requests';
        $id    = intval( $request->get_param( 'id' ) );

        $response_text = sanitize_textarea_field( $request->get_param( 'response' ) );
        $video_url     = esc_url_raw( $request->get_param( 'video_url' ) );

        if ( empty( trim( $response_text ) ) ) {
            return new WP_Error( 'response_required', 'La réponse est obligatoire', [ 'status' => 400 ] );
        }

        // Gérer le fichier de réponse (optionnel)
        $response_file_url = null;
        $files             = $request->get_file_params();

        if ( ! empty( $files['file'] ) ) {
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT user_id, type FROM $table WHERE id = %d", $id ) );
            if ( $row ) {
                $upload_result = self::handle_upload( $files['file'], $row->user_id, 'correction' );
                if ( ! is_wp_error( $upload_result ) ) {
                    $response_file_url = $upload_result['url'];
                }
            }
        }

        $update_data = [
            'status'       => 'completed',
            'response'     => $response_text,
            'responded_at' => current_time( 'mysql' ),
            'responded_by' => get_current_user_id(),
        ];

        if ( $response_file_url ) {
            $update_data['response_file_url'] = $response_file_url;
        }

        // Si une URL vidéo est fournie, l'ajouter à la réponse
        if ( ! empty( $video_url ) ) {
            $update_data['response_file_url'] = $video_url;
        }

        $wpdb->update( $table, $update_data, [ 'id' => $id ] );

        // Notification à l'étudiant
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) );
        Jaide_Notifications::send_response( $row );

        return rest_ensure_response( [
            'success' => true,
            'message' => 'Réponse envoyée',
        ] );
    }
}
