<?php
/**
 * Template pour afficher un commentaire d'arrêt généré
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
    <title><?php echo esc_html($references ?? 'Commentaire d\'arrêt'); ?> - <?php bloginfo('name'); ?></title>
    
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
        $references = get_post_meta(get_the_ID(), '_aga_references', true);
        $matiere = get_post_meta(get_the_ID(), '_aga_matiere', true);
        $texte_arret = get_post_meta(get_the_ID(), '_aga_texte_arret', true);
        $date_generation = get_post_meta(get_the_ID(), '_aga_date_generation', true);
        
        $matiere_formatee = aga_formater_matiere($matiere);
        
        // Parser le contenu
        $sections = aga_parser_contenu_commentaire(get_the_content());
?>

<div class="container-custom" style="max-width: 900px; margin: 0 auto; padding: 20px 15px;">
    
    <!-- Message de succès -->
    <div class="gfa-success" style="display: flex; align-items: start; gap: 12px; margin-bottom: 20px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22,4 12,14.01 9,11.01"></polyline>
        </svg>
        <div>
            <strong style="display: block; margin-bottom: 4px;">Commentaire d'arrêt généré !</strong>
            <p style="margin: 0; font-size: 0.9rem;">Ce commentaire est généré par IA et peut contenir des erreurs. Relisez et vérifiez l'exactitude juridique avant utilisation.</p>
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a href="<?php echo home_url('/generateur-commentaire-arret/'); ?>">Générateur</a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-current">Mon commentaire</span>
    </nav>

    <!-- Titre -->
    <h1 class="result-title">
        Commentaire d'arrêt <span class="highlight">généré</span>
    </h1>
    
    <!-- Métadonnées -->
    <div class="result-meta">
        <span><strong>Arrêt :</strong> <?php echo esc_html($references); ?></span>
        <span><strong>Matière :</strong> <?php echo esc_html($matiere_formatee); ?></span>
        <span><strong>Date de création :</strong> <?php echo date('d/m/Y', strtotime($date_generation)); ?></span>
    </div>
    
    <!-- Texte de l'arrêt (accordéon) -->
    <style>
    #arretToggle {
        display: none;
    }

    #arretToggle:checked ~ .document-section {
        display: block !important;
    }

    #arretToggle:checked ~ .document-navbar .toggle-arrow {
        transform: rotate(180deg);
    }

    .toggle-arrow {
        transition: transform 0.3s ease;
        display: inline-block;
    }

    .arret-label {
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
    }
    </style>

    <div class="document-card" style="margin-bottom: 25px; background: #fff9e6; border: 1px solid #ffc107;">
        <input type="checkbox" id="arretToggle">
        <div class="document-navbar" style="background: linear-gradient(to bottom, #fffbf0 0%, #fff9e6 100%) !important; border-bottom: 1px solid #ffc107;">
            <label for="arretToggle" class="arret-label">
                <h2 class="navbar-title" style="color: #856404; margin: 0;">📄 Texte de l'arrêt</h2>
                <span class="toggle-arrow" style="font-size: 1rem; color: #856404;">▼</span>
            </label>
        </div>
        <div class="document-section" id="arretContent" style="display: none;">
            <div class="section-content" style="white-space: pre-wrap;"><?php echo esc_html($texte_arret); ?></div>
        </div>
    </div>
    
    <!-- Contenu du commentaire -->
    <div class="document-card">
        
        <!-- Header avec bouton copier -->
        <div class="document-navbar">
            <h2 class="navbar-title">Commentaire</h2>
            <button class="btn-copy-nav" onclick="copierCommentaire()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                </svg>
                Copier
            </button>
        </div>
        
        <!-- Contenu -->
        <div class="document-section">
            <div class="dissertation-content">
                <?php if ($sections['parsing_reussi']): ?>
                    
                    <!-- Introduction -->
                    <?php if (!empty($sections['introduction'])): ?>
                        <h2 style="font-size: 1.3rem; font-weight: 700; color: #1B1B1B; margin: 25px 0 15px 0;">Introduction</h2>
                        <?php 
                        // Traiter l'introduction avec marqueurs (Accroche), (Faits), etc.
                        $lignes_intro = explode("\n", $sections['introduction']);
                        foreach ($lignes_intro as $ligne) {
                            $ligne = trim($ligne);
                            if (empty($ligne)) continue;
                            
                            // Détecter marqueurs entre parenthèses
                            if (preg_match('/^\(([^)]+)\)\s*(.+)$/i', $ligne, $match)) {
                                echo '<p style="margin: 12px 0; text-align: justify; font-size: 16px; line-height: 1.8;">';
                                echo '<strong>(' . esc_html($match[1]) . ')</strong> ' . esc_html($match[2]);
                                echo '</p>';
                            } else {
                                echo '<p style="margin: 12px 0; text-align: justify; font-size: 16px; line-height: 1.8;">' . esc_html($ligne) . '</p>';
                            }
                        }
                        ?>
                    <?php endif; ?>
                    
                    <!-- Partie I -->
                    <?php if (!empty($sections['partie_1']['titre'])): ?>
                        <h2 style="font-size: 1.2rem; font-weight: 700; color: #1B1B1B; margin: 30px 0 15px 0; text-decoration: underline;">
                            I. <?php echo esc_html($sections['partie_1']['titre']); ?>
                        </h2>
                        <?php 
                        // Traiter le contenu de la partie I
                        $lignes_p1 = explode("\n", $sections['partie_1']['contenu']);
                        $in_list = false;
                        
                        foreach ($lignes_p1 as $ligne) {
                            $ligne = trim($ligne);
                            if (empty($ligne)) continue;
                            
                            // Détecter "A." ou "B."
                            if (preg_match('/^([A-B])\.\s+(.+)$/i', $ligne)) {
                                if ($in_list) { echo '</ul>'; $in_list = false; }
                                echo '<h3 style="font-size: 1.1rem; font-weight: 700; color: #1B1B1B; margin: 20px 0 12px 0; text-decoration: underline;">' . esc_html($ligne) . '</h3>';
                            }
                            // Détecter "(Annonce de plan interne)" ou "(Transition)"
                            elseif (preg_match('/^\((Annonce de plan interne|Transition)\)\s*(.*)$/i', $ligne, $match)) {
                                if ($in_list) { echo '</ul>'; $in_list = false; }
                                $texte = !empty($match[2]) ? $match[2] : '';
                                echo '<p style="margin: 15px 0; font-style: italic; color: #6c757d;">';
                                echo '<strong>(' . esc_html($match[1]) . ')</strong> ' . esc_html($texte);
                                echo '</p>';
                            }
                            // Détecter lignes avec tirets
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
                                echo '<p style="margin: 12px 0; text-align: justify; font-size: 16px; line-height: 1.8;">' . esc_html($ligne) . '</p>';
                            }
                        }
                        if ($in_list) { echo '</ul>'; }
                        ?>
                    <?php endif; ?>
                    
                    <!-- Partie II -->
                    <?php if (!empty($sections['partie_2']['titre'])): ?>
                        <h2 style="font-size: 1.2rem; font-weight: 700; color: #1B1B1B; margin: 30px 0 15px 0; text-decoration: underline;">
                            II. <?php echo esc_html($sections['partie_2']['titre']); ?>
                        </h2>
                        <?php 
                        // Traiter le contenu de la partie II (même logique que partie I)
                        $lignes_p2 = explode("\n", $sections['partie_2']['contenu']);
                        $in_list = false;
                        
                        foreach ($lignes_p2 as $ligne) {
                            $ligne = trim($ligne);
                            if (empty($ligne)) continue;
                            
                            if (preg_match('/^([A-B])\.\s+(.+)$/i', $ligne)) {
                                if ($in_list) { echo '</ul>'; $in_list = false; }
                                echo '<h3 style="font-size: 1.1rem; font-weight: 700; color: #1B1B1B; margin: 20px 0 12px 0; text-decoration: underline;">' . esc_html($ligne) . '</h3>';
                            }
                            elseif (preg_match('/^\((Annonce de plan interne|Transition)\)\s*(.*)$/i', $ligne, $match)) {
                                if ($in_list) { echo '</ul>'; $in_list = false; }
                                $texte = !empty($match[2]) ? $match[2] : '';
                                echo '<p style="margin: 15px 0; font-style: italic; color: #6c757d;">';
                                echo '<strong>(' . esc_html($match[1]) . ')</strong> ' . esc_html($texte);
                                echo '</p>';
                            }
                            elseif (preg_match('/^[\-\*]\s+(.+)$/', $ligne, $match)) {
                                if (!$in_list) { 
                                    echo '<ul style="margin: 15px 0 15px 30px; padding: 0; list-style: disc;">'; 
                                    $in_list = true; 
                                }
                                echo '<li style="margin-bottom: 10px; line-height: 1.6; font-size: 16px;">' . esc_html($match[1]) . '</li>';
                            }
                            else {
                                if ($in_list) { echo '</ul>'; $in_list = false; }
                                echo '<p style="margin: 12px 0; text-align: justify; font-size: 16px; line-height: 1.8;">' . esc_html($ligne) . '</p>';
                            }
                        }
                        if ($in_list) { echo '</ul>'; }
                        ?>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <!-- Affichage fallback si parsing échoué -->
                    <div class="gfa-warning" style="background: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px 20px; margin-bottom: 20px; border-radius: 8px;">
                        <p><strong>Attention :</strong> La structure du commentaire n'a pas pu être analysée automatiquement. Le contenu complet est affiché ci-dessous.</p>
                    </div>
                    
                    <div style="white-space: pre-wrap; font-size: 16px; line-height: 1.8; text-align: justify;">
                        <?php echo esc_html($sections['introduction']); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Boutons d'action -->
    <div class="action-buttons">
        <a href="<?php echo home_url('/generateur-commentaire-arret/'); ?>" class="btn-action btn-new" target="_blank">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Nouveau commentaire
        </a>
        <a href="<?php echo home_url('/mes-commentaires/'); ?>" class="btn-action btn-history">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 3v5h5"></path>
                <path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"></path>
                <path d="M12 7v5l4 2"></path>
            </svg>
            Mes commentaires
        </a>
    </div>
</div>

<script>
function copierCommentaire() {
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