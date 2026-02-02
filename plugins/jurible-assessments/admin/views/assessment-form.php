<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table = $wpdb->prefix . 'jurible_assessments';

// Mode édition ou création ?
$assessment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$assessment = null;

if ($assessment_id) {
    $assessment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $assessment_id));
    if (!$assessment) {
        wp_die('Assessment non trouvé.');
    }
}

$is_edit = !empty($assessment);
$page_title = $is_edit ? 'Modifier l\'assessment' : 'Créer un assessment';
?>

<div class="wrap jurible-assess-wrap">
    
    <div class="jurible-assess-header">
        <h1><?php echo $is_edit ? '✏️' : '➕'; ?> <?php echo esc_html($page_title); ?></h1>
    </div>
    
    <?php settings_errors('jurible_assess'); ?>
    
    <div class="assess-card">
        <form method="post" action="" class="assess-form">
            <?php wp_nonce_field('jurible_assess_save'); ?>
            <input type="hidden" name="assessment_id" value="<?php echo esc_attr($assessment_id); ?>">
            
            <div class="assess-form-group">
                <label for="title">Titre de l'assessment *</label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       value="<?php echo $assessment ? esc_attr($assessment->title) : ''; ?>" 
                       placeholder="Ex: TD 6 - Cas pratique Droit pénal"
                       required>
            </div>
            
            <div class="assess-form-row">
                <div class="assess-form-group">
                    <label for="assess-course-select">Cours</label>
                    <select id="assess-course-select" name="course_id" data-selected="<?php echo $assessment ? esc_attr($assessment->course_id) : ''; ?>">
                        <option value="">-- Chargement... --</option>
                    </select>
                </div>
                
                <div class="assess-form-group">
                    <label for="assess-lesson-select">Leçon</label>
                    <select id="assess-lesson-select" name="lesson_id" data-selected="<?php echo $assessment ? esc_attr($assessment->lesson_id) : ''; ?>" disabled>
                        <option value="">-- Sélectionner d'abord un cours --</option>
                    </select>
                </div>
            </div>
            
            <div class="assess-form-row">
                <div class="assess-form-group">
                    <label for="max_score">Note maximale</label>
                    <input type="number" 
                           id="max_score" 
                           name="max_score" 
                           value="<?php echo $assessment ? esc_attr($assessment->max_score) : '20'; ?>" 
                           min="1" 
                           max="100">
                </div>
                
                <div class="assess-form-group">
                    <label for="due_date">Date limite (optionnel)</label>
                    <input type="datetime-local" 
                           id="due_date" 
                           name="due_date" 
                           value="<?php echo $assessment && $assessment->due_date ? date('Y-m-d\TH:i', strtotime($assessment->due_date)) : ''; ?>">
                </div>
            </div>
            
            <div class="assess-form-group">
                <label for="subject_pdf_url">URL du PDF sujet (optionnel)</label>
                <input type="url" 
                       id="subject_pdf_url" 
                       name="subject_pdf_url" 
                       value="<?php echo $assessment ? esc_attr($assessment->subject_pdf_url) : ''; ?>" 
                       placeholder="https://...">
                <p class="description">Lien vers le PDF du sujet (uploadé dans la médiathèque WordPress)</p>
            </div>
            
            <div class="assess-form-group">
                <label for="correction_pdf_url">URL du PDF correction type (optionnel)</label>
                <input type="url" 
                       id="correction_pdf_url" 
                       name="correction_pdf_url" 
                       value="<?php echo $assessment ? esc_attr($assessment->correction_pdf_url) : ''; ?>" 
                       placeholder="https://...">
                <p class="description">Lien vers le PDF de la correction type</p>
            </div>
            
            <div class="assess-form-actions">
                <button type="submit" name="jurible_assess_save" class="assess-btn assess-btn-primary">
                    💾 <?php echo $is_edit ? 'Mettre à jour' : 'Créer l\'assessment'; ?>
                </button>
                <a href="<?php echo admin_url('admin.php?page=jurible-assessments'); ?>" class="assess-btn assess-btn-secondary">
                    Annuler
                </a>
            </div>
            
        </form>
    </div>
    
</div>