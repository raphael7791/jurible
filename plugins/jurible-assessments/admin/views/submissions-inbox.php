<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_submissions = $wpdb->prefix . 'jurible_assessment_submissions';
$table_assessments = $wpdb->prefix . 'jurible_assessments';

// Filtres
$filter_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$filter_course = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$filter_assessment = isset($_GET['assessment_id']) ? intval($_GET['assessment_id']) : 0;

// Construire la requête
$where = "WHERE 1=1";
$params = [];

if ($filter_status) {
    $where .= " AND s.status = %s";
    $params[] = $filter_status;
}

if ($filter_course) {
    $where .= " AND a.course_id = %d";
    $params[] = $filter_course;
}

if ($filter_assessment) {
    $where .= " AND s.assessment_id = %d";
    $params[] = $filter_assessment;
}

$query = "
    SELECT s.*, a.title as assessment_title, a.max_score, a.course_id, a.lesson_id,
           u.display_name as student_name, u.user_email as student_email
    FROM $table_submissions s
    LEFT JOIN $table_assessments a ON s.assessment_id = a.id
    LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID
    $where
    ORDER BY 
        CASE s.status 
            WHEN 'submitted' THEN 1 
            WHEN 'in_review' THEN 2 
            WHEN 'graded' THEN 3 
        END,
        s.submitted_at DESC
";

if (!empty($params)) {
    $submissions = $wpdb->get_results($wpdb->prepare($query, $params));
} else {
    $submissions = $wpdb->get_results($query);
}

// Stats
$stats = $wpdb->get_row("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'in_review' THEN 1 ELSE 0 END) as in_review,
        SUM(CASE WHEN status = 'graded' THEN 1 ELSE 0 END) as graded
    FROM $table_submissions
");

// Liste des assessments pour le filtre
$assessments_list = $wpdb->get_results("SELECT id, title FROM $table_assessments ORDER BY title");
?>

<div class="wrap jurible-assess-wrap">
    
    <div class="jurible-assess-header">
        <h1>📥 Soumissions à corriger</h1>
    </div>
    
    <!-- Stats -->
    <div class="assess-stats">
        <a href="<?php echo admin_url('admin.php?page=jurible-assessments'); ?>" 
           class="assess-stat-item <?php echo !$filter_status ? 'active' : ''; ?>">
            <span class="assess-stat-number"><?php echo intval($stats->total); ?></span>
            <span class="assess-stat-label">Total</span>
        </a>
        <a href="<?php echo admin_url('admin.php?page=jurible-assessments&status=submitted'); ?>" 
           class="assess-stat-item <?php echo $filter_status === 'submitted' ? 'active' : ''; ?>">
            <span class="assess-stat-number pending"><?php echo intval($stats->pending); ?></span>
            <span class="assess-stat-label">En attente</span>
        </a>
        <a href="<?php echo admin_url('admin.php?page=jurible-assessments&status=in_review'); ?>" 
           class="assess-stat-item <?php echo $filter_status === 'in_review' ? 'active' : ''; ?>">
            <span class="assess-stat-number in-review"><?php echo intval($stats->in_review); ?></span>
            <span class="assess-stat-label">En cours</span>
        </a>
        <a href="<?php echo admin_url('admin.php?page=jurible-assessments&status=graded'); ?>" 
           class="assess-stat-item <?php echo $filter_status === 'graded' ? 'active' : ''; ?>">
            <span class="assess-stat-number graded"><?php echo intval($stats->graded); ?></span>
            <span class="assess-stat-label">Corrigés</span>
        </a>
    </div>
    
    <!-- Filtres -->
    <div class="assess-filters">
        <select id="filter-assessment" class="assess-filter-select">
            <option value="">Tous les assessments</option>
            <?php foreach ($assessments_list as $assess): ?>
                <option value="<?php echo $assess->id; ?>" <?php selected($filter_assessment, $assess->id); ?>>
                    <?php echo esc_html($assess->title); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <!-- Liste des soumissions -->
    <div class="assess-card">
        <?php if (empty($submissions)): ?>
            
            <div class="assess-empty">
                <div class="assess-empty-icon">✨</div>
                <p>Aucune soumission <?php echo $filter_status ? 'avec ce statut' : ''; ?> pour le moment.</p>
            </div>
            
        <?php else: ?>
            
            <div class="assess-submissions-list">
                <?php foreach ($submissions as $sub): ?>
                    <div class="assess-submission-item">
                        
                        <div class="assess-submission-avatar">
                            <?php echo strtoupper(substr($sub->student_name ?: 'U', 0, 1)); ?>
                        </div>
                        
                        <div class="assess-submission-info">
                            <div class="assess-submission-student">
                                <?php echo esc_html($sub->student_name ?: 'Utilisateur #' . $sub->user_id); ?>
                            </div>
                            <div class="assess-submission-meta">
                                <span>📝 <?php echo esc_html($sub->assessment_title); ?></span>
                                <span>📅 <?php echo date_i18n('d/m/Y à H:i', strtotime($sub->submitted_at)); ?></span>
                                <?php if ($sub->status === 'graded' && $sub->score !== null): ?>
                                    <span>🎯 <?php echo esc_html($sub->score); ?>/<?php echo esc_html($sub->max_score); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <span class="assess-submission-status <?php echo esc_attr($sub->status); ?>">
                            <?php
                            switch ($sub->status) {
                                case 'submitted':
                                    echo '⏳ En attente';
                                    break;
                                case 'in_review':
                                    echo '🔵 En cours';
                                    break;
                                case 'graded':
                                    echo '✅ Corrigé';
                                    break;
                            }
                            ?>
                        </span>
                        
                        <div class="assess-submission-actions">
                            <?php if ($sub->status === 'submitted'): ?>
                                <button class="assess-btn assess-btn-small assess-btn-primary assess-btn-claim" 
                                        data-id="<?php echo $sub->id; ?>">
                                    Prendre en charge
                                </button>
                            <?php elseif ($sub->status === 'in_review'): ?>
                                <a href="<?php echo admin_url('admin.php?page=jurible-assessments-correction&id=' . $sub->id); ?>" 
                                   class="assess-btn assess-btn-small assess-btn-success">
                                    Corriger
                                </a>
                            <?php else: ?>
                                <a href="<?php echo admin_url('admin.php?page=jurible-assessments-correction&id=' . $sub->id); ?>" 
                                   class="assess-btn assess-btn-small assess-btn-secondary">
                                    Voir
                                </a>
                            <?php endif; ?>
                            
                           <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=jurible-assessments&action=delete_submission&id=' . $sub->id), 'delete_submission_' . $sub->id); ?>" 
   class="assess-btn assess-btn-small"
   style="color: #EF4444;"
   onclick="return confirm('Supprimer cette soumission ?');">
    🗑️
</a>
                        </div>
                        
                    </div>
                <?php endforeach; ?>
            </div>
            
        <?php endif; ?>
    </div>
    
</div>

<script>
// Filtre par assessment
document.getElementById('filter-assessment').addEventListener('change', function() {
    var url = new URL(window.location.href);
    if (this.value) {
        url.searchParams.set('assessment_id', this.value);
    } else {
        url.searchParams.delete('assessment_id');
    }
    window.location.href = url.toString();
});
</script>