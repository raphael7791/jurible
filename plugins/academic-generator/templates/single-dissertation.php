<?php
/**
 * Template pour afficher une dissertation générée
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html($sujet); ?> - <?php bloginfo('name'); ?></title>
    
    <!-- Google Font Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Poppins', sans-serif !important;
        }
        
        body {
            margin: 0;
            padding: 0;
            background: #f8f9fa;
        }
        
        .gfa-success {
            background: #d1e7dd !important;
            border: 1px solid #badbcc !important;
            border-radius: 8px !important;
            padding: 15px 20px !important;
        }
    </style>
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php
if (have_posts()) : 
    while (have_posts()) : the_post();
        
        // Récupérer les métadonnées
        $sujet = get_post_meta(get_the_ID(), '_aga_sujet', true);
        $matiere = get_post_meta(get_the_ID(), '_aga_matiere', true);
        $type_generation = get_post_meta(get_the_ID(), '_aga_type_generation', true);
        $date_generation = get_post_meta(get_the_ID(), '_aga_date_generation', true);
        
        $type_label = ($type_generation === 'plan_detaille') ? 'Plan détaillé' : 'Dissertation complète';
        $matiere_formatee = aga_formater_matiere($matiere);
?>

<div class="container-custom" style="max-width: 900px; margin: 0 auto; padding: 20px 15px;">
    
    <!-- Message de succès -->
    <div class="gfa-success" style="display: flex; align-items: start; gap: 12px; margin-bottom: 20px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22,4 12,14.01 9,11.01"></polyline>
        </svg>
        <div>
            <strong style="display: block; margin-bottom: 4px;"><?php echo esc_html($type_label); ?> générée !</strong>
            <p style="margin: 0; font-size: 0.9rem;">Ce contenu est généré par IA et peut contenir des erreurs. Relisez et vérifiez l'exactitude juridique avant utilisation.</p>
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav style="margin-bottom: 15px; font-size: 0.85rem; color: #6c757d;">
        <a href="<?php echo home_url('/generateur-dissertation/'); ?>" style="color: #6c757d; text-decoration: none;">Générateur</a>
        <span style="margin: 0 8px;">›</span>
        <span style="color: #495057;">Ma dissertation</span>
    </nav>

    <!-- Titre -->
    <h1 class="result-title" style="font-size: 1.75rem; font-weight: 600; color: #1B1B1B; margin-bottom: 15px;">
        <?php echo esc_html($sujet); ?>
    </h1>
    
    <!-- Métadonnées -->
    <div class="result-meta" style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 25px; font-size: 0.9rem; color: #6c757d;">
        <span><strong>Type :</strong> <?php echo esc_html($type_label); ?></span>
        <span><strong>Matière :</strong> <?php echo esc_html($matiere_formatee); ?></span>
        <span><strong>Date de création :</strong> <?php echo date('d/m/Y', strtotime($date_generation)); ?></span>
    </div>
    
    <!-- Contenu de la dissertation -->
    <div class="dissertation-card" style="background: white; border: 1px solid #dee2e6; border-radius: 12px; overflow: hidden;">
        
        <!-- Header avec bouton copier -->
        <div class="dissertation-navbar" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border-bottom: 1px solid #dee2e6; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: #1B1B1B;">Contenu de la dissertation</h2>
            <button class="btn-copy-nav" onclick="copierDissertation()" style="background: #f8f9fa; color: #495057; border: 1px solid #6c757d; padding: 8px 15px; border-radius: 6px; font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 6px; cursor: pointer; transition: all 0.2s;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                </svg>
                Copier
            </button>
        </div>
        
        <!-- Contenu -->
        <div class="dissertation-content" style="padding: 30px; color: #495057; line-height: 1.8;">
            <?php 
            $lignes = explode("\n", get_the_content());
            $in_list = false;
            
            foreach ($lignes as $ligne) {
                $ligne = trim($ligne);
                if (empty($ligne)) continue;
                
                // Détecter "Introduction"
                if (preg_match('/^Introduction$/i', $ligne)) {
                    if ($in_list) { echo '</ul>'; $in_list = false; }
                    echo '<h2 style="font-size: 1.3rem; font-weight: 700; color: #1B1B1B; margin: 25px 0 15px 0;">' . esc_html($ligne) . '</h2>';
                }
                // Détecter "I.", "II."
                elseif (preg_match('/^(I{1,3})\.\s+(.+)$/', $ligne)) {
                    if ($in_list) { echo '</ul>'; $in_list = false; }
                    echo '<h2 style="font-size: 1.2rem; font-weight: 700; color: #1B1B1B; margin: 30px 0 15px 0; text-decoration: underline;">' . esc_html($ligne) . '</h2>';
                }
                // Détecter "A.", "B."
                elseif (preg_match('/^([A-B])\.\s+(.+)$/', $ligne)) {
                    if ($in_list) { echo '</ul>'; $in_list = false; }
                    echo '<h3 style="font-size: 1.1rem; font-weight: 700; color: #1B1B1B; margin: 20px 0 12px 0; text-decoration: underline;">' . esc_html($ligne) . '</h3>';
                }
                // Détecter "(Transition)" ou "Transition :"
                elseif (preg_match('/^(\(Transition\)|Transition\s*:)\s*(.*)$/i', $ligne, $match)) {
                    if ($in_list) { echo '</ul>'; $in_list = false; }
                    $texte_transition = !empty($match[2]) ? $match[2] : '';
                    echo '<p style="margin: 15px 0; font-style: italic; color: #6c757d;">';
                    echo '<strong>(Transition)</strong> ' . esc_html($texte_transition);
                    echo '</p>';
                }
                // Détecter marqueurs entre parenthèses (Accroche, Définitions, etc.)
                elseif (preg_match('/^\(([^)]+)\)\s*(.+)$/i', $ligne, $match)) {
                    if ($in_list) { echo '</ul>'; $in_list = false; }
                    echo '<p style="margin: 12px 0; text-align: justify; font-size: 16px; line-height: 1.8;">';
                    echo '<strong>(' . esc_html($match[1]) . ')</strong> ' . esc_html($match[2]);
                    echo '</p>';
                }
                // Détecter lignes avec tirets (plan détaillé)
                elseif (preg_match('/^[\-\*]\s+(.+)$/', $ligne, $match)) {
                    if (!$in_list) { 
                        echo '<ul style="margin: 15px 0 15px 30px; padding: 0; list-style: disc;">'; 
                        $in_list = true; 
                    }
                    echo '<li style="margin-bottom: 10px; line-height: 1.6; font-size: 16px;">' . esc_html($match[1]) . '</li>';
                }
                // Paragraphes normaux
                else {
                    if ($in_list) { echo '</ul>'; $in_list = false; }
                    echo '<p style="margin: 12px 0; text-align: justify; font-size: 16px; line-height: 1.8; font-weight: 400 !important;;">' . esc_html($ligne) . '</p>';
                }
            }
            
            if ($in_list) { echo '</ul>'; }
            ?>
        </div>
    </div>
    
    <!-- Boutons d'action -->
    <div class="action-buttons" style="margin-top: 25px; display: flex; gap: 12px;">
        <a href="<?php echo home_url('/generateur-dissertation/'); ?>" class="btn-action btn-new" target="_blank" style="padding: 10px 20px; border-radius: 8px; font-weight: 500; background: #f8f9fa; color: #495057; border: 1px solid #6c757d; display: flex; align-items: center; gap: 8px; text-decoration: none; transition: all 0.2s;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Nouvelle dissertation
        </a>
        <a href="<?php echo home_url('/mes-dissertations/'); ?>" class="btn-action btn-history" style="padding: 10px 20px; border-radius: 8px; font-weight: 500; background: #b0001d; color: white; border: 1px solid #b0001d; display: flex; align-items: center; gap: 8px; text-decoration: none; transition: all 0.2s;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 3v5h5"></path>
                <path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"></path>
                <path d="M12 7v5l4 2"></path>
            </svg>
            Mes dissertations
        </a>
    </div>
</div>

<script>
function copierDissertation() {
    const content = document.querySelector('.dissertation-content').innerText;
    const btn = event.target.closest('button');
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(content).then(() => showCopiedState(btn));
    } else {
        const textarea = document.createElement('textarea');
        textarea.value = content;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showCopiedState(btn);
    }
}

function showCopiedState(btn) {
    const original = btn.innerHTML;
    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>Copié !';
    
    setTimeout(() => {
        btn.innerHTML = original;
    }, 2000);
}
</script>

<?php
    endwhile;
endif;

get_footer();
?>