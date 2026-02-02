<?php
if (!defined('ABSPATH')) {
    exit;
}

class Jurible_Assess_Admin {

    /**
     * Ajouter les menus admin
     */
    public static function add_menu() {
        add_menu_page(
            'Assessments',
            'Assessments',
            'manage_options',
            'jurible-assessments',
            [self::class, 'render_assessments_page'],
            'dashicons-clipboard',
            31
        );

        add_submenu_page(
            'jurible-assessments',
            'Soumissions',
            'Soumissions',
            'manage_options',
            'jurible-assessments',
            [self::class, 'render_assessments_page']
        );

        add_submenu_page(
            'jurible-assessments',
            'Tous les assessments',
            'Tous les assessments',
            'manage_options',
            'jurible-assessments-list',
            [self::class, 'render_list_page']
        );

        add_submenu_page(
            'jurible-assessments',
            'Paramètres',
            'Paramètres',
            'manage_options',
            'jurible-assessments-settings',
            [self::class, 'render_settings_page']
        );

        // Page cachée pour la correction
        add_submenu_page(
            null,
            'Correction',
            'Correction',
            'manage_options',
            'jurible-assessments-correction',
            [self::class, 'render_correction_page']
        );
    }

    /**
     * Charger les scripts admin
     */
    public static function enqueue_scripts($hook) {
        // Vérifier si on est sur une page du plugin
        if (strpos($hook, 'jurible-assessments') === false) {
            return;
        }

        wp_enqueue_style(
            'jurible-assess-admin',
            JURIBLE_ASSESS_URL . 'admin/css/admin.css',
            [],
            JURIBLE_ASSESS_VERSION
        );

        wp_enqueue_script(
            'jurible-assess-admin',
            JURIBLE_ASSESS_URL . 'admin/js/admin.js',
            ['jquery'],
            JURIBLE_ASSESS_VERSION,
            true
        );

        wp_localize_script('jurible-assess-admin', 'juribleAssess', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('jurible/v1/'),
            'adminUrl' => admin_url(),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }

    /**
     * Page principale : Soumissions (inbox)
     */
public static function render_assessments_page() {
    // Traitement suppression soumission
    if (isset($_GET['action']) && $_GET['action'] === 'delete_submission' && isset($_GET['id'])) {
        if (wp_verify_nonce($_GET['_wpnonce'], 'delete_submission_' . $_GET['id'])) {
            global $wpdb;
            $table = $wpdb->prefix . 'jurible_assessment_submissions';
            $wpdb->delete($table, ['id' => intval($_GET['id'])]);
            wp_redirect(admin_url('admin.php?page=jurible-assessments&deleted=1'));
            exit;
        }
    }
    
    include JURIBLE_ASSESS_PATH . 'admin/views/submissions-inbox.php';
}

    /**
     * Page liste des assessments + formulaire création/édition
     */
    public static function render_list_page() {
        // Traitement de la sauvegarde
        if (isset($_POST['jurible_assess_save']) && wp_verify_nonce($_POST['_wpnonce'], 'jurible_assess_save')) {
            self::save_assessment();
        }

        // Traitement de la suppression
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            if (wp_verify_nonce($_GET['_wpnonce'], 'delete_assessment_' . $_GET['id'])) {
                self::delete_assessment(intval($_GET['id']));
            }
        }

        // Afficher le formulaire ou la liste ?
        $action = isset($_GET['action']) ? $_GET['action'] : '';
        
        if ($action === 'new' || $action === 'edit') {
            include JURIBLE_ASSESS_PATH . 'admin/views/assessment-form.php';
        } else {
            include JURIBLE_ASSESS_PATH . 'admin/views/assessments-list.php';
        }
    }

    /**
     * Page paramètres
     */
    public static function render_settings_page() {
        include JURIBLE_ASSESS_PATH . 'admin/views/settings.php';
    }

    /**
     * Page correction
     */
    public static function render_correction_page() {
        include JURIBLE_ASSESS_PATH . 'admin/views/correction-page.php';
    }

    /**
     * Sauvegarder un assessment
     */
    private static function save_assessment() {
        global $wpdb;
        $table = $wpdb->prefix . 'jurible_assessments';

        $assessment_id = isset($_POST['assessment_id']) ? intval($_POST['assessment_id']) : 0;

        $data = [
            'title' => sanitize_text_field($_POST['title']),
            'course_id' => !empty($_POST['course_id']) ? intval($_POST['course_id']) : null,
            'lesson_id' => !empty($_POST['lesson_id']) ? intval($_POST['lesson_id']) : null,
            'max_score' => intval($_POST['max_score']) ?: 20,
            'due_date' => !empty($_POST['due_date']) ? sanitize_text_field($_POST['due_date']) : null,
            'subject_pdf_url' => esc_url_raw($_POST['subject_pdf_url']),
            'correction_pdf_url' => esc_url_raw($_POST['correction_pdf_url']),
        ];

        if ($assessment_id) {
            // Mise à jour
            $wpdb->update($table, $data, ['id' => $assessment_id]);
            add_settings_error('jurible_assess', 'updated', 'Assessment mis à jour !', 'success');
        } else {
            // Création
            $data['created_at'] = current_time('mysql');
            $wpdb->insert($table, $data);
            $assessment_id = $wpdb->insert_id;
            add_settings_error('jurible_assess', 'created', 'Assessment créé !', 'success');
        }

        // Rediriger vers la liste
        wp_redirect(admin_url('admin.php?page=jurible-assessments-list&message=saved'));
        exit;
    }

    /**
     * Supprimer un assessment
     */
    private static function delete_assessment($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'jurible_assessments';
        $wpdb->delete($table, ['id' => $id]);
        
        wp_redirect(admin_url('admin.php?page=jurible-assessments-list&message=deleted'));
        exit;
    }
}