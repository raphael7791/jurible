<?php
/**
 * Plugin Name: Jurible Quiz Corrections
 * Description: Affiche les bonnes réponses sur la page résultat des quiz + fix checkbox FC 2.2.0
 */

// 1. Sauvegarder les corrections après soumission
add_action('fluent_community/quiz/submitted', function($quizResult, $user, $lesson) {

    $questions = $lesson->enabled_questions ?? [];

    $corrections = [];
    foreach ($questions as $question) {
        $correctAnswers = [];
        foreach ($question['options'] as $option) {
            if (!empty($option['is_correct'])) {
                $correctAnswers[] = $option['label'];
            }
        }
        $corrections[$question['slug']] = [
            'question' => strip_tags($question['label_rendered'] ?? $question['label']),
            'correct_answers' => $correctAnswers,
            'help_text' => $question['help_text'] ?? ''
        ];
    }

    update_user_meta($user->ID, '_fcom_quiz_corrections_' . $lesson->id, $corrections);
    error_log('Quiz corrections saved for user ' . $user->ID . ' lesson ' . $lesson->id);

}, 10, 3);

// 2. Endpoint AJAX pour récupérer les corrections par slug
add_action('wp_ajax_get_quiz_corrections', function() {
    $lesson_slug = sanitize_text_field($_GET['lesson_slug'] ?? '');
    $user_id = get_current_user_id();

    if (!$lesson_slug || !$user_id) {
        wp_send_json_error('Invalid request');
    }

    global $wpdb;
    $lesson = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}fcom_posts WHERE slug = %s AND type = 'course_lesson'",
        $lesson_slug
    ));

    if (!$lesson) {
        wp_send_json_error('Lesson not found');
    }

    $corrections = get_user_meta($user_id, '_fcom_quiz_corrections_' . $lesson->id, true);

    if (!$corrections) {
        wp_send_json_error('No corrections found');
    }

    wp_send_json_success($corrections);
});

// 3. Injecter les scripts via le hook FluentCommunity
add_action('fluent_community/portal_head', function() {
    ?>
    <script>
    /**
     * Fix checkbox quiz FluentCommunity 2.2.0
     * Injecté via JS pour s'assurer que ça s'applique après le CSS de FC
     */
    (function() {
        const style = document.createElement('style');
        style.textContent = '.el-checkbox.is-checked .el-checkbox__inner { background-color: #409EFF !important; border-color: #409EFF !important; }';
        document.head.appendChild(style);
    })();

    /**
     * Quiz corrections - affiche les bonnes réponses après soumission
     */
    console.log('Quiz corrections script injected via portal_head');

    setInterval(function() {
        const incorrectItems = document.querySelectorAll('.fcom_is_correct.incorrect');
        if (incorrectItems.length === 0) return;

        // Éviter les doublons
        if (document.querySelector('.quiz-correction')) return;

        const urlParts = window.location.pathname.split('/');
        const lessonsIndex = urlParts.indexOf('lessons');
        const lessonSlug = lessonsIndex !== -1 ? urlParts[lessonsIndex + 1] : null;

        if (!lessonSlug) return;

        fetch('/wp-admin/admin-ajax.php?action=get_quiz_corrections&lesson_slug=' + lessonSlug)
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;

                const corrections = data.data;
                const questionKeys = Object.keys(corrections);

                document.querySelectorAll('.fcom_question').forEach((questionEl, index) => {
                    const isIncorrect = questionEl.querySelector('.fcom_is_correct.incorrect');
                    if (!isIncorrect) return;

                    const optionsEl = questionEl.querySelector('.fcom_question_options');
                    if (optionsEl && !optionsEl.parentElement.querySelector('.quiz-correction')) {
                        const correctionData = corrections[questionKeys[index]];
                        let correctText = correctionData?.correct_answers?.join(', ') || 'Non disponible';

                        const correctionDiv = document.createElement('div');
                        correctionDiv.className = 'quiz-correction';
                        correctionDiv.style.cssText = 'margin-top:10px;padding:10px;background:#e8f5e9;border-radius:4px;color:#2e7d32;';
                        correctionDiv.innerHTML = '<strong>✓ Bonne réponse :</strong> ' + correctText;
                        optionsEl.after(correctionDiv);
                    }
                });
            });
    }, 1000);
    </script>
    <?php
});
