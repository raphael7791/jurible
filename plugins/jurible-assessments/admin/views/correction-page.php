<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_submissions = $wpdb->prefix . 'jurible_assessment_submissions';
$table_assessments = $wpdb->prefix . 'jurible_assessments';

$submission_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$submission_id) {
    wp_die('Soumission non trouvée.');
}

// Récupérer la soumission avec les infos de l'assessment et de l'étudiant
$submission = $wpdb->get_row($wpdb->prepare("
    SELECT s.*, 
           a.title as assessment_title, 
           a.max_score,
           a.course_id,
           a.lesson_id,
           a.subject_pdf_url,
           a.correction_pdf_url,
           u.display_name as student_name,
           u.user_email as student_email
    FROM $table_submissions s
    LEFT JOIN $table_assessments a ON s.assessment_id = a.id
    LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID
    WHERE s.id = %d
", $submission_id));

if (!$submission) {
    wp_die('Soumission non trouvée.');
}

$is_graded = $submission->status === 'graded';
$is_readonly = $is_graded;
?>

<div class="wrap jurible-assess-wrap">
    
    <!-- Header -->
    <div class="assess-correction-header">
        <a href="<?php echo admin_url('admin.php?page=jurible-assessments'); ?>" class="assess-correction-back">
            ← Retour aux soumissions
        </a>
    </div>
    
    <div class="assess-correction-title">
        <h1>📝 <?php echo esc_html($submission->assessment_title); ?></h1>
        <p>
            <strong><?php echo esc_html($submission->student_name ?: 'Utilisateur #' . $submission->user_id); ?></strong>
            (<?php echo esc_html($submission->student_email); ?>)
            • Soumis le <?php echo date_i18n('d/m/Y à H:i', strtotime($submission->submitted_at)); ?>
        </p>
    </div>
    
    <?php if ($is_graded): ?>
        <div class="notice notice-success" style="margin: 20px 0; padding: 15px; border-radius: 8px;">
            <strong>✅ Cette copie a été corrigée</strong> le <?php echo date_i18n('d/m/Y à H:i', strtotime($submission->graded_at)); ?>
        </div>
    <?php endif; ?>
    
    <div class="assess-correction-grid">
        
        <!-- Colonne gauche : Fichier de l'étudiant -->
        <div class="assess-card">
            <div class="assess-card-header">
                <h2>📄 Copie de l'étudiant</h2>
            </div>
            
            <div class="assess-student-file">
                <div class="assess-file-icon">📄</div>
                
                <?php if ($submission->file_url): ?>
                    <?php 
                    $file_name = basename(parse_url($submission->file_url, PHP_URL_PATH));
                    ?>
                    <div class="assess-file-name"><?php echo esc_html($file_name); ?></div>
                    <div class="assess-file-date">
                        Soumis le <?php echo date_i18n('d/m/Y à H:i', strtotime($submission->submitted_at)); ?>
                    </div>
                    
                    <div class="assess-file-actions">
                        <a href="<?php echo esc_url($submission->file_url); ?>" 
                           class="assess-btn assess-btn-secondary"
                           download>
                            📥 Télécharger
                        </a>
                        <a href="<?php echo esc_url($submission->file_url); ?>" 
                           class="assess-btn assess-btn-secondary"
                           target="_blank">
                            👁️ Ouvrir
                        </a>
                    </div>
                <?php else: ?>
                    <div class="assess-file-name" style="color: #9CA3AF;">Aucun fichier</div>
                <?php endif; ?>
            </div>
            
            <?php if ($submission->subject_pdf_url): ?>
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #E5E7EB;">
                    <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #4A4A4A;">📋 Sujet de l'exercice</h4>
                    <a href="<?php echo esc_url($submission->subject_pdf_url); ?>" 
                       class="assess-btn assess-btn-small assess-btn-secondary"
                       target="_blank">
                        Voir le sujet
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if ($submission->correction_pdf_url): ?>
                <div style="margin-top: 15px;">
                    <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #4A4A4A;">🔍 Correction type</h4>
                    <a href="<?php echo esc_url($submission->correction_pdf_url); ?>" 
                       class="assess-btn assess-btn-small assess-btn-secondary"
                       target="_blank">
                        Voir la correction type
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Colonne droite : Formulaire de correction -->
        <div class="assess-grade-form">
            <h3>✍️ Correction</h3>
            
            <form id="assess-grade-form" data-submission-id="<?php echo $submission_id; ?>">
                
                <div class="assess-form-group">
                    <label for="assess-score">Note</label>
                    <div class="assess-score-input">
                        <input type="number" 
                               id="assess-score" 
                               name="score" 
                               min="0" 
                               max="<?php echo esc_attr($submission->max_score); ?>" 
                               step="0.5"
                               value="<?php echo $submission->score !== null ? esc_attr($submission->score) : ''; ?>"
                               <?php echo $is_readonly ? 'readonly' : ''; ?>
                               required>
                        <span>/ <?php echo esc_html($submission->max_score); ?></span>
                        <input type="hidden" id="assess-max-score" value="<?php echo esc_attr($submission->max_score); ?>">
                    </div>
                </div>
                
                <div class="assess-form-group">
                    <label for="assess-feedback">Feedback <span style="color: #EF4444;">*</span></label>
                    <textarea id="assess-feedback" 
                              name="feedback" 
                              rows="6" 
                              placeholder="Votre retour détaillé sur le travail de l'étudiant..."
                              <?php echo $is_readonly ? 'readonly' : ''; ?>
                              required><?php echo esc_textarea($submission->feedback); ?></textarea>
                    <p class="description">Minimum 10 caractères. Soyez constructif et précis.</p>
                </div>
                
                <div class="assess-form-group">
                    <label for="assess-video-url">🎥 Vidéo de correction (optionnel)</label>
                    <input type="url" 
                           id="assess-video-url" 
                           name="video_url" 
                           placeholder="https://www.tella.tv/video/..."
                           value="<?php echo esc_attr($submission->video_url); ?>"
                           <?php echo $is_readonly ? 'readonly' : ''; ?>>
                    <p class="description">Collez l'URL de votre vidéo Tella ou autre.</p>
                </div>
                
                <?php if ($submission->video_url && $is_graded): ?>
                    <div style="margin-bottom: 20px; padding: 15px; background: #F3F4F6; border-radius: 8px;">
                        <strong>🎥 Vidéo de correction :</strong><br>
                        <a href="<?php echo esc_url($submission->video_url); ?>" target="_blank">
                            <?php echo esc_html($submission->video_url); ?>
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="assess-form-actions">
                    <?php if (!$is_graded): ?>
                        <button type="submit" class="assess-btn assess-btn-primary">
                            💾 Enregistrer et notifier l'étudiant
                        </button>
                        <a href="<?php echo admin_url('admin.php?page=jurible-assessments'); ?>" 
                           class="assess-btn assess-btn-secondary">
                            Annuler
                        </a>
                    <?php else: ?>
                        <a href="<?php echo admin_url('admin.php?page=jurible-assessments'); ?>" 
                           class="assess-btn assess-btn-secondary">
                            ← Retour à l'inbox
                        </a>
                        <button type="button" 
                                class="assess-btn assess-btn-secondary"
                                onclick="document.querySelectorAll('#assess-grade-form input, #assess-grade-form textarea').forEach(el => el.removeAttribute('readonly')); this.style.display='none'; document.querySelector('#assess-grade-form button[type=submit]').style.display='inline-flex';">
                            ✏️ Modifier la correction
                        </button>
                        <button type="submit" class="assess-btn assess-btn-primary" style="display: none;">
                            💾 Mettre à jour
                        </button>
                    <?php endif; ?>
                </div>
                
            </form>
        </div>
        
    </div>
    
</div>