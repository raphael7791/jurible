<?php
if (!defined('ABSPATH')) {
    exit;
}

class Jurible_Assess_API {

    /**
     * Enregistrer les routes API
     */
    public static function register_routes() {
        $namespace = 'jurible/v1';

        // GET /assessments - Liste des assessments
        register_rest_route($namespace, '/assessments', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_assessments'],
            'permission_callback' => '__return_true',
        ]);

        // GET /assessments/{id} - Un assessment
        register_rest_route($namespace, '/assessments/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_assessment'],
            'permission_callback' => '__return_true',
        ]);

        // GET /assessments/by-lesson/{lesson_id} - Assessment par leçon
        register_rest_route($namespace, '/assessments/by-lesson/(?P<lesson_id>\d+)', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_assessment_by_lesson'],
            'permission_callback' => '__return_true',
        ]);

        // POST /assessments/{id}/submit - Soumettre un devoir
        register_rest_route($namespace, '/assessments/(?P<id>\d+)/submit', [
            'methods' => 'POST',
            'callback' => [self::class, 'submit_assessment'],
            'permission_callback' => [self::class, 'check_user_logged_in'],
        ]);

        // GET /assessments/{id}/my-submission - Ma soumission
        register_rest_route($namespace, '/assessments/(?P<id>\d+)/my-submission', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_my_submission'],
            'permission_callback' => [self::class, 'check_user_logged_in'],
        ]);

        // GET /submissions - Liste des soumissions (admin)
        register_rest_route($namespace, '/submissions', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_submissions'],
            'permission_callback' => [self::class, 'check_admin'],
        ]);

        // POST /submissions/{id}/claim - Prendre en charge
        register_rest_route($namespace, '/submissions/(?P<id>\d+)/claim', [
            'methods' => 'POST',
            'callback' => [self::class, 'claim_submission'],
            'permission_callback' => [self::class, 'check_admin'],
        ]);

        // POST /submissions/{id}/grade - Noter
        register_rest_route($namespace, '/submissions/(?P<id>\d+)/grade', [
            'methods' => 'POST',
            'callback' => [self::class, 'grade_submission'],
            'permission_callback' => [self::class, 'check_admin'],
        ]);

        // POST /assessments/import - Import depuis Google Sheet
        register_rest_route($namespace, '/assessments/import', [
            'methods' => 'POST',
            'callback' => [self::class, 'import_assessments'],
            'permission_callback' => [self::class, 'check_import_key'],
        ]);
    }

    /**
     * Permissions
     */
    public static function check_user_logged_in() {
        return is_user_logged_in();
    }

    public static function check_admin() {
        return current_user_can('manage_options');
    }

    public static function check_import_key($request) {
        $key = $request->get_header('X-Import-Key');
        $stored_key = get_option('jurible_assess_import_key', 'jAssess_defaultKey123');
        return !empty($stored_key) && $key === $stored_key;
    }

    /**
     * GET /assessments
     */
    public static function get_assessments($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'jurible_assessments';

        $where = '1=1';
        $params = [];

        if ($request->get_param('course_id')) {
            $where .= ' AND course_id = %d';
            $params[] = intval($request->get_param('course_id'));
        }

        if ($request->get_param('lesson_id')) {
            $where .= ' AND lesson_id = %d';
            $params[] = intval($request->get_param('lesson_id'));
        }

        $sql = "SELECT * FROM $table WHERE $where ORDER BY created_at DESC";

        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }

        $assessments = $wpdb->get_results($sql);

        return rest_ensure_response([
            'success' => true,
            'data' => $assessments,
        ]);
    }

    /**
     * GET /assessments/{id}
     */
    public static function get_assessment($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'jurible_assessments';

        $id = intval($request->get_param('id'));
        $assessment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));

        if (!$assessment) {
            return new WP_Error('not_found', 'Assessment non trouvé', ['status' => 404]);
        }

        return rest_ensure_response([
            'success' => true,
            'data' => $assessment,
        ]);
    }

    /**
     * GET /assessments/by-lesson/{lesson_id}
     */
    public static function get_assessment_by_lesson($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'jurible_assessments';

        $lesson_id = intval($request->get_param('lesson_id'));
        $assessment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE lesson_id = %d", $lesson_id));

        if (!$assessment) {
            return rest_ensure_response([
                'success' => false,
                'data' => null,
            ]);
        }

        return rest_ensure_response([
            'success' => true,
            'data' => $assessment,
        ]);
    }

    /**
     * POST /assessments/{id}/submit
     */
    public static function submit_assessment($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'jurible_assessment_submissions';
        $table_assessments = $wpdb->prefix . 'jurible_assessments';

        $assessment_id = intval($request->get_param('id'));
        $user_id = get_current_user_id();

        // Vérifier que l'assessment existe
        $assessment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_assessments WHERE id = %d", $assessment_id));
        if (!$assessment) {
            return new WP_Error('not_found', 'Assessment non trouvé', ['status' => 404]);
        }

        // Vérifier si déjà soumis
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE assessment_id = %d AND user_id = %d",
            $assessment_id,
            $user_id
        ));

        if ($existing && $existing->status !== 'submitted') {
            return new WP_Error('already_submitted', 'Vous avez déjà soumis ce devoir et il est en cours de correction', ['status' => 400]);
        }

        // Gérer l'upload du fichier
        $files = $request->get_file_params();
        
        if (empty($files['file'])) {
            return new WP_Error('no_file', 'Aucun fichier fourni', ['status' => 400]);
        }

        $file = $files['file'];

        // Vérifier le type de fichier
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.oasis.opendocument.text'];
        $allowed_extensions = ['pdf', 'doc', 'docx', 'odt'];
        
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file['type'], $allowed_types) && !in_array($file_ext, $allowed_extensions)) {
            return new WP_Error('invalid_type', 'Type de fichier non autorisé. Formats acceptés : PDF, DOCX, ODT', ['status' => 400]);
        }

        // Vérifier la taille (20MB max)
        if ($file['size'] > 20 * 1024 * 1024) {
            return new WP_Error('file_too_large', 'Fichier trop volumineux. Maximum : 20 MB', ['status' => 400]);
        }

        // Upload du fichier
        $upload_dir = wp_upload_dir();
        $assess_dir = $upload_dir['basedir'] . '/jurible-assessments/' . $assessment_id;
        
        if (!file_exists($assess_dir)) {
            wp_mkdir_p($assess_dir);
        }

        $user = get_userdata($user_id);
        $safe_name = sanitize_file_name($user->user_login);
        $filename = $safe_name . '-' . $assessment_id . '-' . time() . '.' . $file_ext;
        $filepath = $assess_dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return new WP_Error('upload_failed', 'Erreur lors de l\'upload du fichier', ['status' => 500]);
        }

        $file_url = $upload_dir['baseurl'] . '/jurible-assessments/' . $assessment_id . '/' . $filename;

        // Insérer ou mettre à jour la soumission
        if ($existing) {
            // Supprimer l'ancien fichier
            if ($existing->file_url) {
                $old_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $existing->file_url);
                if (file_exists($old_path)) {
                    unlink($old_path);
                }
            }

            $wpdb->update(
                $table,
                [
                    'file_url' => $file_url,
                    'file_name' => $file['name'],
                    'submitted_at' => current_time('mysql'),
                    'status' => 'submitted',
                ],
                ['id' => $existing->id]
            );
            $submission_id = $existing->id;
        } else {
            $wpdb->insert($table, [
                'assessment_id' => $assessment_id,
                'user_id' => $user_id,
                'file_url' => $file_url,
                'file_name' => $file['name'],
                'status' => 'submitted',
                'submitted_at' => current_time('mysql'),
            ]);
            $submission_id = $wpdb->insert_id;
        }

        // Envoyer notification au prof
        $submission = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $submission_id));
        Jurible_Assess_Notifications::send_new_submission_notification($submission, $assessment);

        return rest_ensure_response([
            'success' => true,
            'message' => 'Devoir soumis avec succès',
            'data' => [
                'id' => $submission_id,
                'file_url' => $file_url,
                'file_name' => $file['name'],
                'submitted_at' => current_time('mysql'),
            ],
        ]);
    }

/**
 * GET /assessments/{id}/my-submission
 */
public static function get_my_submission($request) {
    global $wpdb;
    $table = $wpdb->prefix . 'jurible_assessment_submissions';
    $table_assessments = $wpdb->prefix . 'jurible_assessments';

    $assessment_id = intval($request->get_param('id'));
    $user_id = get_current_user_id();

    // Récupérer l'assessment
    $assessment = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_assessments WHERE id = %d",
        $assessment_id
    ));

    if (!$assessment) {
        return new WP_Error('not_found', 'Assessment non trouvé', ['status' => 404]);
    }

    // Récupérer la soumission de l'utilisateur
    $submission = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE assessment_id = %d AND user_id = %d",
        $assessment_id,
        $user_id
    ));

    return rest_ensure_response([
        'success' => true,
        'data' => [
            'assessment' => $assessment,
            'submission' => $submission,
        ],
    ]);
}

    /**
     * GET /submissions (admin)
     */
    public static function get_submissions($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'jurible_assessment_submissions';
        $table_assessments = $wpdb->prefix . 'jurible_assessments';

        $where = '1=1';
        $params = [];

        if ($request->get_param('status')) {
            $where .= ' AND s.status = %s';
            $params[] = $request->get_param('status');
        }

        if ($request->get_param('assessment_id')) {
            $where .= ' AND s.assessment_id = %d';
            $params[] = intval($request->get_param('assessment_id'));
        }

        $sql = "SELECT s.*, a.title as assessment_title, a.max_score,
                       u.display_name as student_name
                FROM $table s
                JOIN $table_assessments a ON s.assessment_id = a.id
                JOIN {$wpdb->users} u ON s.user_id = u.ID
                WHERE $where
                ORDER BY s.submitted_at DESC";

        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }

        $submissions = $wpdb->get_results($sql);

        return rest_ensure_response([
            'success' => true,
            'data' => $submissions,
        ]);
    }

    /**
     * POST /submissions/{id}/claim
     */
    public static function claim_submission($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'jurible_assessment_submissions';

        $id = intval($request->get_param('id'));

        $wpdb->update(
            $table,
            [
                'status' => 'in_review',
                'assigned_to' => get_current_user_id(),
            ],
            ['id' => $id]
        );

        return rest_ensure_response([
            'success' => true,
            'message' => 'Soumission prise en charge',
        ]);
    }

    /**
     * POST /submissions/{id}/grade
     */
    public static function grade_submission($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'jurible_assessment_submissions';

        $id = intval($request->get_param('id'));
        $score = floatval($request->get_param('score'));
        $feedback = sanitize_textarea_field($request->get_param('feedback'));
        $video_url = esc_url_raw($request->get_param('video_url'));

        if (empty(trim($feedback))) {
            return new WP_Error('feedback_required', 'Le feedback est obligatoire', ['status' => 400]);
        }

        $wpdb->update(
            $table,
            [
                'status' => 'graded',
                'score' => $score,
                'feedback' => $feedback,
                'video_url' => $video_url,
                'graded_at' => current_time('mysql'),
                'graded_by' => get_current_user_id(),
            ],
            ['id' => $id]
        );

        // Notification à l'étudiant
        $submission = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
        Jurible_Assess_Notifications::send_graded_notification($submission);

        return rest_ensure_response([
            'success' => true,
            'message' => 'Correction enregistrée',
        ]);
    }

    /**
     * POST /assessments/import
     */
    public static function import_assessments($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'jurible_assessments';

        $assessments = $request->get_param('assessments');

        if (!is_array($assessments)) {
            return new WP_Error('invalid_data', 'Format invalide', ['status' => 400]);
        }

        $imported = 0;
        $updated = 0;

        foreach ($assessments as $assess) {
            $data = [
                'title' => sanitize_text_field($assess['title']),
                'description' => sanitize_textarea_field($assess['description'] ?? ''),
                'course_id' => !empty($assess['course_id']) ? intval($assess['course_id']) : null,
                'lesson_id' => !empty($assess['lesson_id']) ? intval($assess['lesson_id']) : null,
                'max_score' => isset($assess['max_score']) ? floatval($assess['max_score']) : 20,
                'due_date' => !empty($assess['due_date']) ? sanitize_text_field($assess['due_date']) : null,
                'subject_pdf_url' => !empty($assess['subject_pdf_url']) ? esc_url_raw($assess['subject_pdf_url']) : null,
                'correction_pdf_url' => !empty($assess['correction_pdf_url']) ? esc_url_raw($assess['correction_pdf_url']) : null,
            ];

            // Si ID fourni, mise à jour
            if (!empty($assess['id'])) {
                $existing = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table WHERE id = %d", intval($assess['id'])));
                if ($existing) {
                    $wpdb->update($table, $data, ['id' => intval($assess['id'])]);
                    $updated++;
                    continue;
                }
            }

            // Si lesson_id fourni, vérifier si existe déjà
            if (!empty($data['lesson_id'])) {
                $existing = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table WHERE lesson_id = %d", $data['lesson_id']));
                if ($existing) {
                    $wpdb->update($table, $data, ['id' => $existing->id]);
                    $updated++;
                    continue;
                }
            }

            // Sinon, création
            $wpdb->insert($table, $data);
            $last_id = $wpdb->insert_id;
            $imported++;
        }

return rest_ensure_response([
            'success' => true,
            'imported' => $imported,
            'updated' => $updated,
            'last_id' => isset($last_id) ? $last_id : null,
        ]);
    }
}