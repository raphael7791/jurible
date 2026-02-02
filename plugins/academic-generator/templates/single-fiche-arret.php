<?php
/**
 * Template pour afficher une fiche d'arrêt individuelle
 * Template Name: Single Fiche Arrêt
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
    <title><?php echo esc_html($references ?? 'Fiche d\'arrêt'); ?> - <?php bloginfo('name'); ?></title>
    
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
    </style>
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php
if (have_posts()) : 
    while (have_posts()) : the_post();
        
// Récupérer les métadonnées (compatible ancien préfixe _gfa_ et nouveau _aga_)
$references_normalized = get_post_meta(get_the_ID(), '_aga_references_normalized', true) ?: get_post_meta(get_the_ID(), '_gfa_references_normalized', true);
$references = $references_normalized ? $references_normalized : (get_post_meta(get_the_ID(), '_aga_references', true) ?: get_post_meta(get_the_ID(), '_gfa_references', true));
$matiere = get_post_meta(get_the_ID(), '_aga_matiere', true) ?: get_post_meta(get_the_ID(), '_gfa_matiere', true);
$date_generation = get_post_meta(get_the_ID(), '_aga_date_generation', true) ?: get_post_meta(get_the_ID(), '_gfa_date_generation', true);
$parsing_reussi = get_post_meta(get_the_ID(), '_aga_parsing_reussi', true) ?: get_post_meta(get_the_ID(), '_gfa_parsing_reussi', true);
        
        // Récupérer le contenu et le traiter
        $contenu_brut = get_the_content();
        
        // Parser le contenu en sections
        $sections = aga_parser_contenu_fiche($contenu_brut);
?>

<div class="container-custom">
    
    <!-- Message de succès -->
    <div class="gfa-success">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2" class="success-icon">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22,4 12,14.01 9,11.01"></polyline>
        </svg>
        <div>
            <strong class="success-title">Fiche d'arrêt générée !</strong>
            <p class="success-text">Cette fiche est générée par IA et peut contenir des erreurs. Relisez et vérifiez l'exactitude juridique avant utilisation.</p>
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a href="<?php echo home_url('/generateur-fiche/'); ?>">Générateur</a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-current">Ma fiche d'arrêt</span>
    </nav>

    <!-- Titre -->
    <h1 class="result-title">
        Fiche d'arrêt <span class="highlight">générée</span>
        <?php if($references): ?> (<?php echo esc_html($references); ?>)<?php endif; ?>
    </h1>
    
    <!-- Métadonnées -->
    <div class="result-meta">
        <?php if($matiere): ?>
            <span><strong>Matière :</strong> <?php echo esc_html(aga_formater_matiere($matiere)); ?></span>
        <?php endif; ?>
        <?php if($date_generation): ?>
            <span><strong>Date de création :</strong> <?php echo date('d/m/Y', strtotime($date_generation)); ?></span>
        <?php endif; ?>
    </div>
    
    <?php 
    // Vérifier si le parsing a réussi
    if (!$sections['parsing_reussi']):
    ?>
        <!-- Template alternatif si le parsing a échoué -->
        <div class="gfa-warning">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffc107" stroke-width="2" class="warning-icon">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <div>
                <strong class="warning-title">Attention : Structure non détectée automatiquement</strong>
                <p class="warning-text">La structure de cette fiche n'a pas pu être analysée correctement. Le contenu complet est affiché ci-dessous.</p>
            </div>
        </div>
        
        <!-- Affichage du contenu complet dans une seule carte -->
        <div class="document-card">
            <div class="document-navbar">
                <h2 class="navbar-title">Contenu complet de la fiche</h2>
                <button class="btn-copy-nav" onclick="gfaCopyToClipboard()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                    Copier
                </button>
            </div>
            <div class="document-section">
                <div class="section-content">
                    <?php echo wpautop(wp_kses_post($contenu_brut)); ?>
                </div>
            </div>
        </div>
        
    <?php else : ?>
        <!-- Template normal avec les 4 sections si le parsing a réussi -->
        
        <!-- Navigation -->
        <div class="fiche-navbar">
            <div class="nav-links">
                <a href="#section-faits" class="nav-link">
                    <span class="nav-number">1</span>
                    Faits
                </a>
                <a href="#section-procedure" class="nav-link">
                    <span class="nav-number">2</span>
                    Procédure
                </a>
                <a href="#section-probleme" class="nav-link">
                    <span class="nav-number">3</span>
                    Problème
                </a>
                <a href="#section-solution" class="nav-link">
                    <span class="nav-number">4</span>
                    Solution
                </a>
            </div>
            <button class="btn-copy-nav" onclick="gfaCopyToClipboard()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                </svg>
                Copier
            </button>
        </div>
        
        <!-- Fiche avec 4 sections -->
        <div class="fiche-card">
            
            <!-- Section 1: Faits -->
            <div class="fiche-section section-facts" id="section-faits">
                <h2 class="section-title">
                    <span class="section-number section-number-blue">1</span>
                    Faits
                </h2>
                <div class="section-content">
                    <?php echo wpautop(wp_kses_post($sections['faits'])); ?>
                </div>
            </div>
            
            <!-- Section 2: Procédure -->
            <div class="fiche-section section-procedure" id="section-procedure">
                <h2 class="section-title">
                    <span class="section-number section-number-purple">2</span>
                    Procédure
                </h2>
                <div class="section-content">
                    <?php echo wpautop(wp_kses_post($sections['procedure'])); ?>
                </div>
            </div>
            
            <!-- Section 3: Problème de droit -->
            <div class="fiche-section section-problem" id="section-probleme">
                <h2 class="section-title">
                    <span class="section-number section-number-red">3</span>
                    Problème de droit
                </h2>
                <div class="section-content">
                    <?php echo wpautop(wp_kses_post($sections['probleme'])); ?>
                </div>
            </div>
            
            <!-- Section 4: Solution -->
            <div class="fiche-section section-solution" id="section-solution">
                <h2 class="section-title">
                    <span class="section-number section-number-green">4</span>
                    Solution
                </h2>
                <div class="section-content">
                    <?php echo wpautop(wp_kses_post($sections['solution'])); ?>
                </div>
            </div>
        </div>
        
    <?php endif; ?>
    
    <!-- Boutons d'action -->
    <div class="action-buttons">
        <a href="<?php echo home_url('/generateur-fiche/'); ?>" class="btn-action btn-new" target="_blank">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Nouvelle fiche
        </a>
        <a href="<?php echo home_url('/mes-fiches/'); ?>" class="btn-action btn-history" target="_blank">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 3v5h5"></path>
                <path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"></path>
                <path d="M12 7v5l4 2"></path>
            </svg>
            Mes fiches
        </a>
    </div>
</div>

<script>
function gfaCopyToClipboard() {
    const content = document.querySelector('.fiche-card, .document-card').innerText;
    const btn = event.target.closest('button');
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(content).then(() => gfaShowCopiedState(btn));
    } else {
        const textarea = document.createElement('textarea');
        textarea.value = content;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        gfaShowCopiedState(btn);
    }
}

function gfaShowCopiedState(btn) {
    const original = btn.innerHTML;
    btn.classList.add('copied');
    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>Copié !';
    
    setTimeout(() => {
        btn.classList.remove('copied');
        btn.innerHTML = original;
    }, 2000);
}

// Navigation fluide
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if(target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>

<?php
    endwhile;
endif;

wp_footer();
?>

</body>
</html>