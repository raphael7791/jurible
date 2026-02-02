<?php
if (!defined('ABSPATH')) exit;

// Récupérer les assessments
global $wpdb;
$table = $wpdb->prefix . 'jurible_assessments';
$table_submissions = $wpdb->prefix . 'jurible_assessment_submissions';

$assessments = $wpdb->get_results("
    SELECT a.*, 
           (SELECT COUNT(*) FROM $table_submissions WHERE assessment_id = a.id) as total_submissions,
           (SELECT COUNT(*) FROM $table_submissions WHERE assessment_id = a.id AND status = 'graded') as graded_submissions
    FROM $table a 
    ORDER BY a.created_at DESC
");
?>

<div class="wrap jurible-assess-wrap">
    
    <div class="jurible-assess-header">
        <h1>📚 Assessments</h1>
        <a href="<?php echo admin_url('admin.php?page=jurible-assessments-list&action=new'); ?>" class="assess-btn assess-btn-primary">
            + Créer un assessment
        </a>
    </div>
    
    <?php settings_errors('jurible_assess'); ?>
    
    <div class="assess-card">
        <?php if (empty($assessments)): ?>
            
            <div class="assess-empty">
                <div class="assess-empty-icon">📝</div>
                <p>Aucun assessment créé pour le moment.</p>
                <p><a href="<?php echo admin_url('admin.php?page=jurible-assessments-list&action=new'); ?>">Créer votre premier assessment</a></p>
            </div>
            
        <?php else: ?>
            
            <table class="assess-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Cours / Leçon</th>
                        <th>Note max</th>
                        <th>Date limite</th>
                        <th>Soumissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assessments as $assess): ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($assess->title); ?></strong>
                            </td>
                            <td>
                                <span class="assess-meta">
                                    <?php if ($assess->course_id): ?>
                                        Cours #<?php echo esc_html($assess->course_id); ?>
                                    <?php endif; ?>
                                    <?php if ($assess->lesson_id): ?>
                                        → Leçon #<?php echo esc_html($assess->lesson_id); ?>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($assess->max_score); ?></td>
                            <td>
                                <?php if ($assess->due_date): ?>
                                    <?php 
                                    $due = strtotime($assess->due_date);
                                    $now = time();
                                    $is_past = $due < $now;
                                    ?>
                                    <span style="color: <?php echo $is_past ? '#EF4444' : '#10B981'; ?>">
                                        <?php echo date_i18n('d/m/Y H:i', $due); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #9CA3AF;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="assess-submissions-count">
                                    <strong><?php echo intval($assess->graded_submissions); ?></strong> / <?php echo intval($assess->total_submissions); ?>
                                </span>
                                <span style="color: #9CA3AF; font-size: 12px;">corrigés</span>
                            </td>
                            <td class="actions">
                                <a href="<?php echo admin_url('admin.php?page=jurible-assessments-list&action=edit&id=' . $assess->id); ?>" 
                                   class="assess-btn assess-btn-small assess-btn-secondary">
                                    ✏️ Modifier
                                </a>
                                <a href="<?php echo admin_url('admin.php?page=jurible-assessments&assessment_id=' . $assess->id); ?>" 
                                   class="assess-btn assess-btn-small assess-btn-secondary">
                                    📥 Soumissions
                                </a>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=jurible-assessments-list&action=delete&id=' . $assess->id), 'delete_assessment_' . $assess->id); ?>" 
                                   class="assess-btn assess-btn-small assess-btn-delete"
                                   style="color: #EF4444;"
                                   onclick="return confirm('Supprimer cet assessment ?');">
                                    🗑️
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
        <?php endif; ?>
    </div>
    
</div>