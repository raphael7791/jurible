<?php
if (!defined('ABSPATH')) {
    exit;
}

class Jurible_Assess_Notifications {

    /**
     * Initialiser les notifications
     */
    public static function init() {
        add_filter('wp_mail_content_type', [self::class, 'set_html_content_type']);
    }

    /**
     * Définir le type de contenu HTML pour les emails
     */
    public static function set_html_content_type() {
        return 'text/html';
    }

    /**
     * Obtenir les options de notification
     */
    private static function get_options() {
        return get_option('jurible_assess_options', [
            'notify_prof_new_submission' => true,
            'notify_student_graded' => true,
            'notify_student_reminder' => true,
            'notify_student_overdue' => true,
            'notify_prof_digest' => true,
            'digest_day' => 'monday',
            'reminder_days_before' => 2,
            'overdue_days_after' => 1,
            'from_name' => get_bloginfo('name'),
            'from_email' => get_option('admin_email'),
        ]);
    }

    /**
     * Obtenir les headers des emails
     */
    private static function get_headers() {
        $options = self::get_options();
        $from_name = $options['from_name'] ?? get_bloginfo('name');
        $from_email = $options['from_email'] ?? get_option('admin_email');
        
        return [
            'Content-Type: text/html; charset=UTF-8',
            "From: {$from_name} <{$from_email}>",
        ];
    }

    /**
     * Template de base pour les emails
     */
    private static function get_email_template($content, $title = '') {
        $logo_url = ''; // Tu peux ajouter l'URL de ton logo ici
        $site_name = get_bloginfo('name');
        $primary_color = '#B0001D';
        $secondary_color = '#7C3AED';

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body style="margin: 0; padding: 0; font-family: \'Poppins\', -apple-system, BlinkMacSystemFont, sans-serif; background-color: #F3F4F6;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #F3F4F6; padding: 40px 20px;">
                <tr>
                    <td align="center">
                        <table width="600" cellpadding="0" cellspacing="0" style="background-color: #FFFFFF; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                            <!-- Header -->
                            <tr>
                                <td style="background: linear-gradient(94.73deg, ' . $primary_color . ' 0%, #DC2626 50%, ' . $secondary_color . ' 100%); padding: 30px; text-align: center;">
                                    <h1 style="color: #FFFFFF; margin: 0; font-size: 24px; font-weight: 700;">' . esc_html($site_name) . '</h1>
                                </td>
                            </tr>
                            
                            <!-- Content -->
                            <tr>
                                <td style="padding: 40px 30px;">
                                    ' . $content . '
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style="background-color: #F9FAFB; padding: 20px 30px; text-align: center; border-top: 1px solid #E5E7EB;">
                                    <p style="margin: 0; color: #9CA3AF; font-size: 12px;">
                                        Cet email a été envoyé automatiquement par ' . esc_html($site_name) . '
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>';
    }

    /**
     * Notification : Nouvelle soumission (au prof)
     */
    public static function send_new_submission_notification($submission, $assessment) {
        $options = self::get_options();
        
        if (empty($options['notify_prof_new_submission'])) {
            return;
        }

        $student = get_userdata($submission->user_id);
        $admin_email = get_option('admin_email');
        $admin_url = admin_url('admin.php?page=jurible-assessments&action=grade&id=' . $submission->id);

        $subject = '📝 Nouvelle soumission - ' . $assessment->title;

        $content = '
            <h2 style="color: #1A1A1A; margin: 0 0 20px 0; font-size: 20px;">Nouvelle soumission reçue</h2>
            
            <div style="background-color: #F9FAFB; border-radius: 12px; padding: 20px; margin-bottom: 25px;">
                <p style="margin: 0 0 10px 0; color: #4A4A4A;">
                    <strong style="color: #1A1A1A;">Étudiant :</strong> ' . esc_html($student->display_name) . '
                </p>
                <p style="margin: 0 0 10px 0; color: #4A4A4A;">
                    <strong style="color: #1A1A1A;">Devoir :</strong> ' . esc_html($assessment->title) . '
                </p>
                <p style="margin: 0 0 10px 0; color: #4A4A4A;">
                    <strong style="color: #1A1A1A;">Fichier :</strong> ' . esc_html($submission->file_name) . '
                </p>
                <p style="margin: 0; color: #4A4A4A;">
                    <strong style="color: #1A1A1A;">Date :</strong> ' . date_i18n('d/m/Y à H:i', strtotime($submission->submitted_at)) . '
                </p>
            </div>
            
            <div style="text-align: center;">
                <a href="' . esc_url($admin_url) . '" style="display: inline-block; background: linear-gradient(94.73deg, #B0001D 0%, #DC2626 50%, #7C3AED 100%); color: #FFFFFF; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 14px;">
                    Corriger maintenant
                </a>
            </div>
        ';

        $html = self::get_email_template($content);
        
        wp_mail($admin_email, $subject, $html, self::get_headers());
    }

    /**
     * Notification : Devoir corrigé (à l'étudiant)
     */
    public static function send_graded_notification($submission) {
        $options = self::get_options();
        
        if (empty($options['notify_student_graded'])) {
            return;
        }

        global $wpdb;
        $table_assessments = $wpdb->prefix . 'jurible_assessments';
        $assessment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_assessments WHERE id = %d", $submission->assessment_id));

        $student = get_userdata($submission->user_id);
        
        // Construire l'URL vers la leçon (Fluent Community)
        $lesson_url = home_url('/portal/courses/' . $assessment->course_id . '/lessons/' . $assessment->lesson_id);

        $subject = '✅ Votre devoir a été corrigé - ' . $submission->score . '/' . $assessment->max_score;

        $score_percentage = ($submission->score / $assessment->max_score) * 100;
        $score_color = $score_percentage >= 50 ? '#10B981' : '#EF4444';

        $video_section = '';
        if (!empty($submission->video_url)) {
            $video_section = '
                <div style="background-color: #F0F9FF; border-radius: 8px; padding: 15px; margin-top: 20px; border-left: 4px solid #3B82F6;">
                    <p style="margin: 0; color: #1E40AF; font-weight: 600;">
                        🎥 Une vidéo de correction est disponible
                    </p>
                </div>
            ';
        }

        $content = '
            <h2 style="color: #1A1A1A; margin: 0 0 10px 0; font-size: 20px;">Bonjour ' . esc_html($student->display_name) . ',</h2>
            
            <p style="color: #4A4A4A; margin: 0 0 25px 0; font-size: 16px;">
                Votre devoir a été corrigé !
            </p>
            
            <div style="background-color: #F9FAFB; border-radius: 12px; padding: 25px; margin-bottom: 25px; text-align: center;">
                <p style="margin: 0 0 10px 0; color: #6B7280; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">
                    ' . esc_html($assessment->title) . '
                </p>
                <p style="margin: 0; font-size: 48px; font-weight: 700; color: ' . $score_color . ';">
                    ' . $submission->score . '<span style="font-size: 24px; color: #9CA3AF;">/' . $assessment->max_score . '</span>
                </p>
            </div>
            
            <div style="background-color: #FFFBEB; border-radius: 8px; padding: 20px; margin-bottom: 20px; border-left: 4px solid #F59E0B;">
                <p style="margin: 0 0 10px 0; color: #92400E; font-weight: 600;">💬 Feedback de votre professeur :</p>
                <p style="margin: 0; color: #78350F; font-style: italic; line-height: 1.6;">
                    ' . nl2br(esc_html($submission->feedback)) . '
                </p>
            </div>
            
            ' . $video_section . '
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="' . esc_url($lesson_url) . '" style="display: inline-block; background: linear-gradient(94.73deg, #B0001D 0%, #DC2626 50%, #7C3AED 100%); color: #FFFFFF; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 14px;">
                    Voir ma correction complète
                </a>
            </div>
        ';

        $html = self::get_email_template($content);
        
        wp_mail($student->user_email, $subject, $html, self::get_headers());
    }

    /**
     * Notification : Rappel deadline (à l'étudiant)
     */
    public static function send_reminder_notification($user, $assessment, $days_left) {
        $options = self::get_options();
        
        if (empty($options['notify_student_reminder'])) {
            return;
        }

        $lesson_url = home_url('/portal/courses/' . $assessment->course_id . '/lessons/' . $assessment->lesson_id);

        $subject = '⏰ Rappel : ' . $assessment->title . ' à rendre dans ' . $days_left . ' jour' . ($days_left > 1 ? 's' : '');

        $content = '
            <h2 style="color: #1A1A1A; margin: 0 0 10px 0; font-size: 20px;">Bonjour ' . esc_html($user->display_name) . ',</h2>
            
            <p style="color: #4A4A4A; margin: 0 0 25px 0; font-size: 16px;">
                N\'oubliez pas de rendre votre devoir !
            </p>
            
            <div style="background-color: #FEF3C7; border-radius: 12px; padding: 25px; margin-bottom: 25px; text-align: center; border: 2px solid #F59E0B;">
                <p style="margin: 0 0 10px 0; color: #92400E; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">
                    ' . esc_html($assessment->title) . '
                </p>
                <p style="margin: 0; font-size: 24px; font-weight: 700; color: #B45309;">
                    ⏳ ' . $days_left . ' jour' . ($days_left > 1 ? 's' : '') . ' restant' . ($days_left > 1 ? 's' : '') . '
                </p>
                <p style="margin: 10px 0 0 0; color: #92400E; font-size: 14px;">
                    Date limite : ' . date_i18n('d/m/Y', strtotime($assessment->due_date)) . '
                </p>
            </div>
            
            <div style="text-align: center;">
                <a href="' . esc_url($lesson_url) . '" style="display: inline-block; background: linear-gradient(94.73deg, #B0001D 0%, #DC2626 50%, #7C3AED 100%); color: #FFFFFF; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 14px;">
                    Rendre mon devoir
                </a>
            </div>
        ';

        $html = self::get_email_template($content);
        
        wp_mail($user->user_email, $subject, $html, self::get_headers());
    }

    /**
     * Notification : Deadline dépassée (à l'étudiant)
     */
    public static function send_overdue_notification($user, $assessment, $days_overdue) {
        $options = self::get_options();
        
        if (empty($options['notify_student_overdue'])) {
            return;
        }

        $lesson_url = home_url('/portal/courses/' . $assessment->course_id . '/lessons/' . $assessment->lesson_id);

        $subject = '🚨 Deadline dépassée : ' . $assessment->title;

        $content = '
            <h2 style="color: #1A1A1A; margin: 0 0 10px 0; font-size: 20px;">Bonjour ' . esc_html($user->display_name) . ',</h2>
            
            <p style="color: #4A4A4A; margin: 0 0 25px 0; font-size: 16px;">
                La date limite pour rendre votre devoir est dépassée.
            </p>
            
            <div style="background-color: #FEE2E2; border-radius: 12px; padding: 25px; margin-bottom: 25px; text-align: center; border: 2px solid #EF4444;">
                <p style="margin: 0 0 10px 0; color: #991B1B; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">
                    ' . esc_html($assessment->title) . '
                </p>
                <p style="margin: 0; font-size: 24px; font-weight: 700; color: #DC2626;">
                    🚨 Retard de ' . $days_overdue . ' jour' . ($days_overdue > 1 ? 's' : '') . '
                </p>
            </div>
            
            <p style="color: #4A4A4A; margin: 0 0 25px 0; font-size: 14px; text-align: center;">
                Nous vous encourageons à rendre votre devoir dès que possible.
            </p>
            
            <div style="text-align: center;">
                <a href="' . esc_url($lesson_url) . '" style="display: inline-block; background: linear-gradient(94.73deg, #B0001D 0%, #DC2626 50%, #7C3AED 100%); color: #FFFFFF; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 14px;">
                    Rendre mon devoir maintenant
                </a>
            </div>
        ';

        $html = self::get_email_template($content);
        
        wp_mail($user->user_email, $subject, $html, self::get_headers());
    }

    /**
     * Notification : Digest hebdomadaire (au prof)
     */
    public static function send_digest() {
        $options = self::get_options();
        
        if (empty($options['notify_prof_digest'])) {
            return;
        }

        // Vérifier le jour
        $today = strtolower(date('l'));
        $digest_day = $options['digest_day'] ?? 'monday';
        
        if ($today !== $digest_day) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'jurible_assessment_submissions';

        $pending_count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'submitted'");
        $in_review_count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'in_review'");
        $graded_this_week = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'graded' AND graded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");

        if ($pending_count == 0 && $in_review_count == 0) {
            return; // Pas besoin d'envoyer si rien à signaler
        }

        $admin_email = get_option('admin_email');
        $admin_url = admin_url('admin.php?page=jurible-assessments');

        $subject = '📊 Résumé hebdo - ' . $pending_count . ' soumission' . ($pending_count > 1 ? 's' : '') . ' en attente';

        $content = '
            <h2 style="color: #1A1A1A; margin: 0 0 20px 0; font-size: 20px;">Votre résumé hebdomadaire</h2>
            
            <div style="display: flex; gap: 15px; margin-bottom: 25px;">
                <div style="flex: 1; background-color: #FEF3C7; border-radius: 12px; padding: 20px; text-align: center;">
                    <p style="margin: 0; font-size: 36px; font-weight: 700; color: #B45309;">' . $pending_count . '</p>
                    <p style="margin: 5px 0 0 0; color: #92400E; font-size: 12px; text-transform: uppercase;">En attente</p>
                </div>
                <div style="flex: 1; background-color: #DBEAFE; border-radius: 12px; padding: 20px; text-align: center;">
                    <p style="margin: 0; font-size: 36px; font-weight: 700; color: #1D4ED8;">' . $in_review_count . '</p>
                    <p style="margin: 5px 0 0 0; color: #1E40AF; font-size: 12px; text-transform: uppercase;">En cours</p>
                </div>
                <div style="flex: 1; background-color: #D1FAE5; border-radius: 12px; padding: 20px; text-align: center;">
                    <p style="margin: 0; font-size: 36px; font-weight: 700; color: #059669;">' . $graded_this_week . '</p>
                    <p style="margin: 5px 0 0 0; color: #047857; font-size: 12px; text-transform: uppercase;">Corrigés (7j)</p>
                </div>
            </div>
            
            <div style="text-align: center;">
                <a href="' . esc_url($admin_url) . '" style="display: inline-block; background: linear-gradient(94.73deg, #B0001D 0%, #DC2626 50%, #7C3AED 100%); color: #FFFFFF; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 14px;">
                    Accéder à l\'inbox
                </a>
            </div>
        ';

        $html = self::get_email_template($content);
        
        wp_mail($admin_email, $subject, $html, self::get_headers());
    }

    /**
     * Envoyer les rappels quotidiens
     */
    public static function send_reminders() {
        $options = self::get_options();
        
        global $wpdb;
        $table_assessments = $wpdb->prefix . 'jurible_assessments';
        $table_submissions = $wpdb->prefix . 'jurible_assessment_submissions';

        $reminder_days = $options['reminder_days_before'] ?? 2;
        $overdue_days = $options['overdue_days_after'] ?? 1;

        // Assessments avec deadline
        $assessments = $wpdb->get_results("SELECT * FROM $table_assessments WHERE due_date IS NOT NULL");

        foreach ($assessments as $assessment) {
            $due_date = strtotime($assessment->due_date);
            $now = time();
            $days_diff = floor(($due_date - $now) / (60 * 60 * 24));

            // Récupérer les utilisateurs qui n'ont pas soumis
            // (Ici, tu pourrais affiner avec les utilisateurs inscrits au cours)
            $submitted_users = $wpdb->get_col($wpdb->prepare(
                "SELECT user_id FROM $table_submissions WHERE assessment_id = %d",
                $assessment->id
            ));

            // Pour l'instant, on envoie aux admins comme test
            // Tu pourrais étendre cela aux utilisateurs du cours Fluent

            if ($days_diff > 0 && $days_diff <= $reminder_days) {
                // Rappel avant deadline
                // À implémenter : récupérer les étudiants du cours qui n'ont pas soumis
            } elseif ($days_diff < 0 && abs($days_diff) == $overdue_days) {
                // Deadline dépassée
                // À implémenter : récupérer les étudiants du cours qui n'ont pas soumis
            }
        }
    }
}