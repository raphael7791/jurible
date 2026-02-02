<?php
/**
 * Template pour afficher un cas pratique généré
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
    <title><?php echo esc_html('Cas pratique'); ?> - <?php bloginfo('name'); ?></title>
    
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
        $sujet = get_post_meta(get_the_ID(), '_aga_sujet_cas_pratique', true);
        $matiere = get_post_meta(get_the_ID(), '_aga_matiere', true);
        $date_generation = get_post_meta(get_the_ID(), '_aga_date_generation', true);
        
        $matiere_formatee = aga_formater_matiere($matiere);
        
        // Parser le contenu
        $sections = aga_parser_contenu_cas_pratique(get_the_content());
?>

<div class="container-custom" style="max-width: 900px; margin: 0 auto; padding: 20px 15px;">
    
<!-- Message d'avertissement amélioré -->
    <div class="gfa-success" style="display: flex; align-items: start; gap: 12px; margin-bottom: 20px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22,4 12,14.01 9,11.01"></polyline>
        </svg>
        <div>
            <strong style="display: block; margin-bottom: 8px; color: #155724;">Cas pratique corrigé</strong>
            <p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: #155724;">
                <strong>Attention :</strong> Cette correction est générée par IA et <strong>peut contenir des erreurs, approximations ou oublis</strong>. 
                Utilisez-la comme <strong>base de travail</strong> et vérifiez systématiquement avec votre cours, vos codes et les articles cités.
            </p>
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a href="<?php echo home_url('/generateur-cas-pratique/'); ?>">Générateur</a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-current">Mon cas pratique</span>
    </nav>

    <!-- Titre -->
    <h1 class="result-title">
        Correction de <span class="highlight">cas pratique</span>
    </h1>
    
    <!-- Métadonnées -->
    <div class="result-meta">
        <span><strong>Matière :</strong> <?php echo esc_html($matiere_formatee); ?></span>
        <span><strong>Date de création :</strong> <?php echo date('d/m/Y', strtotime($date_generation)); ?></span>
    </div>
    
    <!-- Sujet du cas pratique -->
    <style>
    #sujetToggle {
        display: none;
    }

    #sujetToggle:checked ~ .document-section {
        display: block !important;
    }

    #sujetToggle:checked ~ .document-navbar .toggle-arrow {
        transform: rotate(180deg);
    }

    .toggle-arrow {
        transition: transform 0.3s ease;
        display: inline-block;
    }

    .sujet-label {
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
    }
    </style>

    <div class="document-card" style="margin-bottom: 25px; background: #fff9e6; border: 1px solid #ffc107;">
        <input type="checkbox" id="sujetToggle">
        <div class="document-navbar" style="background: linear-gradient(to bottom, #fffbf0 0%, #fff9e6 100%) !important; border-bottom: 1px solid #ffc107;">
            <label for="sujetToggle" class="sujet-label">
                <h2 class="navbar-title" style="color: #856404; margin: 0;"> Sujet </h2>
                <span class="toggle-arrow" style="font-size: 1rem; color: #856404;">▼</span>
            </label>
        </div>
        <div class="document-section" id="sujetContent" style="display: none;">
            <div class="section-content" style="white-space: pre-wrap;"><?php echo esc_html($sujet); ?></div>
        </div>
    </div>
    
    <!-- Contenu de la correction -->
    <div class="document-card">
        
        <!-- Header avec bouton copier -->
        <div class="document-navbar">
            <h2 class="navbar-title">Correction</h2>
            <button class="btn-copy-nav" onclick="copierCasPratique()">
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
                    
                    <?php if (!empty($sections['plan'])): ?>
                        <div style="background: #f8f9fa; border: 1px solid #b0001d; padding: 15px 20px; margin-bottom: 30px; border-radius: 4px;">
                            <h3 style="margin: 0 0 10px 0; font-size: 1rem; font-weight: 600; color: #1B1B1B;">Plan de résolution</h3>
                            <div style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.6; color: #1B1B1B;"><?php echo esc_html($sections['plan']); ?></div>
                        </div>
                     <?php endif; ?>
                    
                    <?php 
                    // Afficher le contenu avec mise en forme minimaliste
                    $lignes = explode("\n", $sections['contenu']);
                    
                    foreach ($lignes as $ligne) {
                        $ligne = trim($ligne);
                        if (empty($ligne)) continue;
                        
                        // Détecter les parties I, II, III
                        if (preg_match('/^(I{1,3})\.\s+(.+)$/', $ligne)) {
                            echo '<h2>' . esc_html($ligne) . '</h2>';
                        }
                        // Détecter FAITS, PROBLÈME, SOLUTION EN DROIT, etc.
                        elseif (preg_match('/^(FAITS|PROBLÈME DE DROIT|SOLUTION EN DROIT|SOLUTION EN L\'ESPÈCE|CONCLUSION)\s*:\s*(.*)$/i', $ligne, $match)) {
                            $titre = $match[1];
                            $texte = trim($match[2]);
                            
                            echo '<h3>' . esc_html($titre) . '</h3>';
                            if (!empty($texte)) {
                                echo '<p>' . esc_html($texte) . '</p>';
                            }
                        }
                        // Paragraphe normal
                        else {
                            echo '<p>' . esc_html($ligne) . '</p>';
                        }
                    }
                    ?>
                    
                <?php else: ?>
                    <!-- Affichage fallback si parsing échoué -->
                    <div style="white-space: pre-wrap;">
                        <?php echo esc_html($sections['contenu']); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Boutons d'action -->
    <div class="action-buttons">
        <a href="<?php echo home_url('/generateur-cas-pratique/'); ?>" class="btn-action btn-new" target="_blank">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Nouveau cas pratique
        </a>
        <a href="<?php echo home_url('/mes-cas-pratiques/'); ?>" class="btn-action btn-history">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 3v5h5"></path>
                <path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"></path>
                <path d="M12 7v5l4 2"></path>
            </svg>
            Mes cas pratiques
        </a>
    </div>
</div>

<script>
function copierCasPratique() {
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