<?php
/**
 * Server-side rendering for the Method Tabs block.
 * Allows dynamic video URL from ACF field.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 */

// Get attributes with defaults
$section_badge    = $attributes['sectionBadge'] ?? 'Testez gratuitement';
$section_title    = $attributes['sectionTitle'] ?? 'Découvrez notre <mark>méthode</mark>';
$section_subtitle = $attributes['sectionSubtitle'] ?? 'Accédez à un extrait du cours sans inscription';
$cta_text         = $attributes['ctaText'] ?? 'Débloquer le cours complet — 29€/mois';
$cta_url          = $attributes['ctaUrl'] ?? '#pricing';

// Get video URL from ACF field (dynamic) or fallback to block attribute
$video_url = get_field('video_url');
if (empty($video_url)) {
    $video_url = $attributes['videoUrl'] ?? '';
}

// Convert video URL to embed URL (YouTube or Bunny Stream)
$embed_url = '';
if (!empty($video_url)) {
    // Handle Bunny Stream (iframe.mediadelivery.net) - already embed format
    if (strpos($video_url, 'mediadelivery.net') !== false || strpos($video_url, 'bunnycdn') !== false) {
        $embed_url = $video_url;
        // Add default params if not present
        if (strpos($embed_url, '?') === false) {
            $embed_url .= '?autoplay=false&preload=true';
        }
    }
    // Handle youtu.be format
    elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $video_url, $matches)) {
        $embed_url = 'https://www.youtube.com/embed/' . $matches[1];
    }
    // Handle youtube.com/watch?v= format
    elseif (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $video_url, $matches)) {
        $embed_url = 'https://www.youtube.com/embed/' . $matches[1];
    }
    // Handle youtube.com/embed/ format
    elseif (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $video_url, $matches)) {
        $embed_url = 'https://www.youtube.com/embed/' . $matches[1];
    }
}

// Block wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes();

// Tab configuration
$tabs = [
    ['id' => 'video', 'icon' => '🎬', 'label' => 'Vidéo', 'shortLabel' => 'Vidéo'],
    ['id' => 'cours', 'icon' => '📄', 'label' => 'Cours écrit', 'shortLabel' => 'Cours'],
    ['id' => 'mindmap', 'icon' => '🗺️', 'label' => 'Mindmap', 'shortLabel' => 'Mind.'],
    ['id' => 'qcm', 'icon' => '✅', 'label' => 'QCM', 'shortLabel' => 'QCM'],
    ['id' => 'flashcard', 'icon' => '🃏', 'label' => 'Flashcard', 'shortLabel' => 'Flash.'],
    ['id' => 'annale', 'icon' => '📚', 'label' => 'Annale', 'shortLabel' => 'Annale'],
    ['id' => 'fiche-video', 'icon' => '🎥', 'label' => 'Fiche vidéo', 'shortLabel' => 'F. vidéo'],
];
?>

<div <?php echo $wrapper_attributes; ?>>
    <!-- Header -->
    <div class="method-tabs__header">
        <span class="method-tabs__badge method-tabs__badge--green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            <?php echo esc_html($section_badge); ?>
        </span>
        <h2 class="method-tabs__title"><?php echo wp_kses_post($section_title); ?></h2>
        <p class="method-tabs__subtitle"><?php echo esc_html($section_subtitle); ?></p>
    </div>

    <!-- Tabs Navigation -->
    <div class="method-tabs__nav" role="tablist">
        <?php foreach ($tabs as $index => $tab) : ?>
            <button
                class="method-tabs__tab<?php echo $index === 0 ? ' is-active' : ''; ?>"
                role="tab"
                aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                aria-controls="method-panel-<?php echo esc_attr($tab['id']); ?>"
                data-tab-index="<?php echo $index; ?>"
                type="button"
            >
                <span class="method-tabs__tab-icon"><?php echo $tab['icon']; ?></span>
                <span class="method-tabs__tab-label method-tabs__tab-label--full"><?php echo esc_html($tab['label']); ?></span>
                <span class="method-tabs__tab-label method-tabs__tab-label--short"><?php echo esc_html($tab['shortLabel']); ?></span>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Preview Card -->
    <div class="method-tabs__card">
        <!-- TAB 1: VIDÉO -->
        <div class="method-tabs__panel is-active" id="method-panel-video" role="tabpanel" data-panel-index="0">
            <div class="method-tabs__split">
                <div class="method-tabs__split-visual">
                    <?php if (!empty($embed_url)) : ?>
                        <div class="method-tabs__video-player">
                            <iframe
                                src="<?php echo esc_url($embed_url); ?>"
                                title="Vidéo de présentation"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                                loading="lazy"
                            ></iframe>
                        </div>
                    <?php else : ?>
                        <div class="method-tabs__video-placeholder">
                            <div class="method-tabs__play-btn">
                                <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                            </div>
                            <div class="method-tabs__video-overlay">Les composantes de l'État (15 min)</div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="method-tabs__split-info">
                    <h3>Vidéo de présentation de la matière</h3>
                    <p>Le cours comprend plus de 20 vidéos de cours ainsi que des corrections d'annales en vidéo pour vous accompagner tout au long de votre apprentissage.</p>
                </div>
            </div>
        </div>

        <!-- TAB 2: COURS ÉCRITS -->
        <div class="method-tabs__panel" id="method-panel-cours" role="tabpanel" data-panel-index="1">
            <div class="method-tabs__example-banner">
                <span class="method-tabs__example-banner-icon">📌</span>
                Voici un exemple en droit constitutionnel
            </div>
            <div class="method-tabs__fiche">
                <div class="method-tabs__fiche-content">
                    <h3>Leçon : Les composantes de l'État</h3>
                    <div class="method-tabs__fiche-meta">Droit constitutionnel · Leçon 1 sur 71</div>

                    <p class="method-tabs__theme-title"><strong>Thème n°1 : Le cadre du pouvoir politique</strong></p>

                    <div class="wp-block-jurible-infobox jurible-infobox jurible-infobox-important">
                        <div class="jurible-infobox-header">
                            <span class="jurible-infobox-icon">📌</span>
                            <span class="jurible-infobox-title">Important</span>
                        </div>
                        <p class="jurible-infobox-content">Deux théories permettent d'expliquer l'origine de l'État.<br><strong>1. Théorie du Pacte social</strong><br>Selon la théorie du <strong>Pacte social</strong>, l'État a pour origine un accord de volonté des hommes visant à assurer la défense de leurs intérêts et la garantie de leurs libertés au sein de la société (Spinoza, Rousseau, Hobbes…). Les hommes renoncent à l'état de nature par un « pacte social ».<br><strong>2. Théorie de l'évolution naturelle</strong><br>Selon la théorie de l'<strong>évolution naturelle</strong>, l'État naît de l'évolution naturelle de la société. Les hommes, naturellement, se regroupent et s'organisent pour améliorer la vie en communauté.</p>
                    </div>

                    <div class="wp-block-jurible-infobox jurible-infobox jurible-infobox-definition">
                        <div class="jurible-infobox-header">
                            <span class="jurible-infobox-icon">📖</span>
                            <span class="jurible-infobox-title">Définition</span>
                        </div>
                        <p class="jurible-infobox-content">Le mot « Etat » vient du latin « stare » signifiant « se tenir debout ». Il s'agit d'une forme d'organisation des sociétés humaines. C'est une personne morale de droit public représentant une collectivité, un peuple ou une <strong>nation</strong>, à l'intérieur ou à l'extérieur d'un <strong>territoire</strong> déterminé sur lequel elle exerce le pouvoir suprême, la <strong>souveraineté</strong>.</p>
                    </div>

                    <p>Trois éléments permettent de caractériser un État.</p>

                    <h2 class="wp-block-heading" id="i-le-territoire">I. Le territoire</h2>

                    <div class="wp-block-jurible-infobox jurible-infobox jurible-infobox-definition">
                        <div class="jurible-infobox-header">
                            <span class="jurible-infobox-icon">📖</span>
                            <span class="jurible-infobox-title">Définition</span>
                        </div>
                        <p class="jurible-infobox-content">Cet élément constitutif de l'État est une partie de l'espace géographique qui relève de cet État, sur lequel vit une <strong>population</strong> et s'exerce l'autorité publique. Il comprend :</p>
                    </div>

                    <ul class="wp-block-list">
                        <li>Le <strong>territoire</strong> terrestre ;</li>
                        <li><strong>L'espace maritime</strong> comprenant la mer territoriale et la zone économique exclusive ;</li>
                        <li><strong>L'espace aérien</strong> : atmosphère au-dessus du territoire terrestre et de l'espace maritime.</li>
                    </ul>

                    <h3 class="wp-block-heading">A. Éléments indifférents</h3>

                    <ul class="wp-block-list">
                        <li>La taille du territoire n'importe pas (ex. : Vatican / Russie).</li>
                        <li>Le territoire peut être morcelé en plusieurs entités sans continuité (ex : les DOM/TOM en France).</li>
                        <li>Le territoire peut être enclavé dans un État étranger (ex : Vatican).</li>
                    </ul>

                    <h3 class="wp-block-heading">B. Abandon d'une partie du territoire</h3>

                    <p>La Constitution française autorise les cessions et les adjonctions de territoires à certaines conditions (<span style="color:#009051">art. 53 al. 3</span>) alors que d'autres constitutions interdisent aux pouvoirs publics de consentir à des abandons de territoire (principe d'intangibilité du territoire).</p>

                    <h2 class="wp-block-heading" id="ii-la-population">II. La population</h2>

                    <div class="wp-block-jurible-infobox jurible-infobox jurible-infobox-definition">
                        <div class="jurible-infobox-header">
                            <span class="jurible-infobox-icon">📖</span>
                            <span class="jurible-infobox-title">Définition</span>
                        </div>
                        <p class="jurible-infobox-content">C'est un groupe humain, un ensemble de personnes physiques formant une communauté, distincte des autres rattachés à un État.</p>
                    </div>

                    <h3 class="wp-block-heading">A. Distinction avec la nation</h3>

                    <p>La <strong>nation</strong> n'est pas la <strong>population</strong>. La nation est une entité abstraite et collective regroupant les hommes et les femmes qui, partageant une histoire et des valeurs communes, acceptent de lier leur destin.</p>
                </div>
                <div class="method-tabs__fiche-fade">
                    <span>Scrollez pour voir la suite ↓</span>
                </div>
            </div>
        </div>

        <!-- TAB 3: MINDMAP -->
        <div class="method-tabs__panel" id="method-panel-mindmap" role="tabpanel" data-panel-index="2">
            <div class="method-tabs__example-banner">
                <span class="method-tabs__example-banner-icon">📌</span>
                Voici un exemple en droit constitutionnel
            </div>
            <div class="method-tabs__split">
                <div class="method-tabs__split-visual method-tabs__split-visual--mindmap">
                    <img
                        src="<?php echo esc_url(get_theme_file_uri('assets/images/mindmap-composantes-etat.webp')); ?>"
                        alt="Mindmap : Les composantes de l'État - Territoire, Population, Souveraineté, Nation"
                        class="method-tabs__mindmap-img"
                        loading="lazy"
                    />
                </div>
                <div class="method-tabs__split-info">
                    <h3>Mindmap : Les composantes de l'État</h3>
                    <p>Visualisez la structure complète du cours sous forme de carte mentale. Idéal pour mémoriser les relations entre les concepts clés et réviser efficacement.</p>
                </div>
            </div>
        </div>

        <!-- TAB 4: QCM -->
        <div class="method-tabs__panel" id="method-panel-qcm" role="tabpanel" data-panel-index="3">
            <div class="method-tabs__example-banner">
                <span class="method-tabs__example-banner-icon">📌</span>
                Voici un exemple en droit constitutionnel
            </div>
            <div class="method-tabs__qcm">
                <h3>QCM : Les composantes de l'État</h3>
                <div class="method-tabs__qcm-meta">Droit constitutionnel · 10 questions</div>

                <div class="method-tabs__qcm-question">Quelles sont les caractéristiques de la souveraineté étatique ?</div>

                <div class="method-tabs__qcm-options">
                    <div class="method-tabs__qcm-option" data-correct="false">
                        <div class="method-tabs__qcm-radio"></div>
                        <span>Divisible, aliénable, prescriptible</span>
                    </div>
                    <div class="method-tabs__qcm-option" data-correct="true">
                        <div class="method-tabs__qcm-radio"></div>
                        <span>Unique, indivisible, inaliénable et imprescriptible</span>
                    </div>
                    <div class="method-tabs__qcm-option" data-correct="false">
                        <div class="method-tabs__qcm-radio"></div>
                        <span>Partagée, temporaire, conditionnelle</span>
                    </div>
                    <div class="method-tabs__qcm-option" data-correct="false">
                        <div class="method-tabs__qcm-radio"></div>
                        <span>Limitée, déléguée, révocable</span>
                    </div>
                </div>

                <div class="method-tabs__qcm-feedback"></div>
            </div>
        </div>

        <!-- TAB 5: FLASHCARD -->
        <div class="method-tabs__panel" id="method-panel-flashcard" role="tabpanel" data-panel-index="4">
            <div class="method-tabs__example-banner">
                <span class="method-tabs__example-banner-icon">📌</span>
                Voici un exemple en droit constitutionnel
            </div>
            <div class="method-tabs__flashcard">
                <div class="method-tabs__fc-title">Flashcards : Les composantes de l'État</div>

                <div class="method-tabs__fc-progress">
                    <span class="method-tabs__fc-progress-label">Progression</span>
                    <div class="method-tabs__fc-bar">
                        <div class="method-tabs__fc-bar-fill" style="width: 7%;"></div>
                    </div>
                    <span class="method-tabs__fc-progress-label">1 / 15 cartes</span>
                </div>

                <div class="method-tabs__fc-card-wrapper" tabindex="0" role="button" aria-label="Cliquez pour retourner la carte">
                    <div class="method-tabs__fc-card">
                        <div class="method-tabs__fc-face method-tabs__fc-front">
                            <div class="method-tabs__fc-header method-tabs__fc-header--question">
                                <span>Question</span>
                                <span>1/15</span>
                            </div>
                            <div class="method-tabs__fc-body">
                                Qu'est-ce que l'État ?
                            </div>
                            <div class="method-tabs__fc-footer">
                                <span>👆</span> Cliquez pour voir la réponse
                            </div>
                        </div>
                        <div class="method-tabs__fc-face method-tabs__fc-back">
                            <div class="method-tabs__fc-header method-tabs__fc-header--answer">
                                <span>💡 Réponse</span>
                                <span>1/15</span>
                            </div>
                            <div class="method-tabs__fc-body">
                                Personne morale de droit public représentant une collectivité sur un territoire où elle exerce la souveraineté
                            </div>
                        </div>
                    </div>
                </div>

                <div class="method-tabs__fc-actions">
                    <button class="method-tabs__fc-btn method-tabs__fc-btn--no" type="button">Je ne savais pas</button>
                    <button class="method-tabs__fc-btn method-tabs__fc-btn--yes" type="button">Je savais</button>
                </div>
            </div>
        </div>

        <!-- TAB 6: ANNALE -->
        <div class="method-tabs__panel" id="method-panel-annale" role="tabpanel" data-panel-index="5">
            <div class="method-tabs__example-banner">
                <span class="method-tabs__example-banner-icon">📌</span>
                Voici un exemple en droit constitutionnel
            </div>
            <div class="method-tabs__fiche">
                <div class="method-tabs__fiche-content">
                    <h3>Dissertation : À quoi sert la séparation des pouvoirs ?</h3>
                    <div class="method-tabs__fiche-meta">Droit constitutionnel · Annale corrigée</div>

                    <div class="wp-block-jurible-infobox jurible-infobox jurible-infobox-conditions">
                        <div class="jurible-infobox-header">
                            <span class="jurible-infobox-icon">📌</span>
                            <span class="jurible-infobox-title">Conditions</span>
                        </div>
                        <p class="jurible-infobox-content">Durée de réalisation : 3 heures</p>
                    </div>

                    <p>Réalisez une dissertation sur le sujet suivant :</p>

                    <p><strong>« À quoi sert la séparation des pouvoirs ? »</strong></p>

                    <p><em>(Accroche et contexte)</em> « Toute société dans laquelle la garantie des droits n'est pas assurée, ni la séparation des pouvoirs déterminée, n'a point de Constitution », énonce l'<span style="color:#009051">article 16 de la Déclaration des Droits de l'Homme et du Citoyen de 1789</span>. Cette affirmation, dans un texte de valeur constitutionnelle, témoigne du caractère fondamental de la <strong>séparation des pouvoirs</strong>, principe qui s'est progressivement imposé comme l'un des piliers de l'<strong>État de droit</strong> moderne depuis sa théorisation par <strong>Montesquieu</strong> au XVIIIe siècle.</p>

                    <p><em>(Définitions)</em> La <strong>séparation des pouvoirs</strong> est une théorie politique et constitutionnelle développée par plusieurs penseurs au fil du temps. Si <strong>Aristote</strong> évoquait déjà la distinction des pouvoirs dans l'Antiquité, c'est <strong>John Locke</strong> qui, dans son « <em>Second Traité du gouvernement civil</em> » (1690), établit la première distinction moderne entre trois pouvoirs : <strong>législatif</strong>, <strong>exécutif</strong> et <strong>fédératif</strong>. <strong>Montesquieu</strong> formalise ensuite la célèbre trilogie des pouvoirs (législatif, exécutif et judiciaire) dans « <em>De l'esprit des lois</em> » (1748).</p>

                    <p><em>(Impératifs contradictoires)</em> Bien que la séparation des pouvoirs soit traditionnellement considérée comme un rempart contre l'arbitraire et une garantie des libertés individuelles, son application concrète soulève de nombreuses questions. D'une part, la pratique contemporaine révèle une tendance à la <strong>concentration des pouvoirs</strong> au profit de l'exécutif, ce qui remet en question l'efficacité de cette théorie.</p>

                    <p><em>(Problématique)</em> <strong>Dans quelle mesure la séparation des pouvoirs constitue-t-elle aujourd'hui un instrument efficace et pertinent pour l'organisation politique des États démocratiques ?</strong></p>

                    <p><em>(Annonce de plan générale)</em> Si la séparation des pouvoirs apparaît comme une théorie contestée dans sa capacité à décrire et garantir le fonctionnement démocratique des États modernes <strong>(I)</strong>, elle demeure néanmoins un principe fondamental et une référence indispensable pour la protection des droits et libertés <strong>(II)</strong>.</p>

                    <h2 class="wp-block-heading">I. Un outil insuffisant dans sa capacité à décrire et garantir le fonctionnement démocratique des États modernes</h2>

                    <p><em>(Annonce du plan interne)</em> La théorie de la séparation des pouvoirs fait l'objet de critiques importantes tant dans sa capacité à servir d'instrument de classification des régimes politiques <strong>(A)</strong> que dans son aptitude à constituer un véritable gage de démocratie politique <strong>(B)</strong>.</p>
                </div>
                <div class="method-tabs__fiche-fade">
                    <span>Scrollez pour voir la suite ↓</span>
                </div>
            </div>
        </div>

        <!-- TAB 7: FICHE VIDÉO -->
        <div class="method-tabs__panel" id="method-panel-fiche-video" role="tabpanel" data-panel-index="6">
            <div class="method-tabs__example-banner">
                <span class="method-tabs__example-banner-icon">📌</span>
                Voici un exemple en droit constitutionnel
            </div>
            <div class="method-tabs__notice">
                <span class="method-tabs__notice-icon">ℹ️</span>
                Les fiches vidéo sont disponibles uniquement pour les matières de L1 à l'heure actuelle.
            </div>
            <div class="method-tabs__fiche-video">
                <div class="method-tabs__fv-player method-tabs__fv-player--video">
                    <div class="method-tabs__fv-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        Fiche animée
                    </div>
                    <iframe
                        src="https://www.youtube.com/embed/0oeuKSAXOts"
                        title="Fiche vidéo : Les composantes de l'État"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        loading="lazy"
                    ></iframe>
                </div>
                <div class="method-tabs__fv-playlist">
                    <div class="method-tabs__fv-playlist-header">
                        <span class="method-tabs__fv-playlist-title">Fiches animées</span>
                        <span class="method-tabs__fv-playlist-count">38 vidéos</span>
                    </div>
                    <div class="method-tabs__fv-playlist-items">
                        <div class="method-tabs__fv-item method-tabs__fv-item--active">
                            <span class="method-tabs__fv-item-num">1</span>
                            <div class="method-tabs__fv-item-thumb"></div>
                            <div class="method-tabs__fv-item-info">
                                <span class="method-tabs__fv-item-name">Les composantes de l'État</span>
                                <span class="method-tabs__fv-item-dur">2:30</span>
                            </div>
                        </div>
                        <div class="method-tabs__fv-item method-tabs__fv-item--locked">
                            <span class="method-tabs__fv-item-num">2</span>
                            <div class="method-tabs__fv-item-thumb"></div>
                            <div class="method-tabs__fv-item-info">
                                <span class="method-tabs__fv-item-name">L'État et le territoire</span>
                                <span class="method-tabs__fv-item-dur">2:15</span>
                            </div>
                            <svg class="method-tabs__fv-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <div class="method-tabs__fv-item method-tabs__fv-item--locked">
                            <span class="method-tabs__fv-item-num">3</span>
                            <div class="method-tabs__fv-item-thumb"></div>
                            <div class="method-tabs__fv-item-info">
                                <span class="method-tabs__fv-item-name">L'État et la population</span>
                                <span class="method-tabs__fv-item-dur">2:45</span>
                            </div>
                            <svg class="method-tabs__fv-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <div class="method-tabs__fv-item method-tabs__fv-item--locked">
                            <span class="method-tabs__fv-item-num">4</span>
                            <div class="method-tabs__fv-item-thumb"></div>
                            <div class="method-tabs__fv-item-info">
                                <span class="method-tabs__fv-item-name">L'État et la souveraineté</span>
                                <span class="method-tabs__fv-item-dur">2:20</span>
                            </div>
                            <svg class="method-tabs__fv-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <div class="method-tabs__fv-item method-tabs__fv-item--locked">
                            <span class="method-tabs__fv-item-num">5</span>
                            <div class="method-tabs__fv-item-thumb"></div>
                            <div class="method-tabs__fv-item-info">
                                <span class="method-tabs__fv-item-name">L'État unitaire</span>
                                <span class="method-tabs__fv-item-dur">2:00</span>
                            </div>
                            <svg class="method-tabs__fv-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Bottom -->
    <div class="method-tabs__cta-wrapper">
        <p class="method-tabs__cta-intro">⭐ Vous aimez ce contenu ?</p>
        <a href="<?php echo esc_url($cta_url); ?>" class="method-tabs__cta"><?php echo esc_html($cta_text); ?></a>
    </div>
</div>
