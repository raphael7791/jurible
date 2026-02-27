<?php
/**
 * Hero Dashboard Block - Server-side render
 * Style Stripe avec cartes flottantes et effet 3D
 *
 * @package JuribleBlocks
 */

// Get attributes with defaults
$user_name        = $attributes['userName'] ?? 'Marie';
$user_initials    = $attributes['userInitials'] ?? 'ML';
$current_course   = $attributes['currentCourse'] ?? 'Droit constitutionnel';
$lesson_title     = $attributes['lessonTitle'] ?? 'La séparation des pouvoirs';
$lesson_meta      = $attributes['lessonMeta'] ?? 'Leçon 8 · 18 min · Raphaël Briguet-Lamarre';
$progress_percent = $attributes['progressPercent'] ?? 67;
$qcm_count        = $attributes['qcmCount'] ?? 84;
$fiches_count     = $attributes['fichesCount'] ?? 12;
$fiches_total     = $attributes['fichesTotal'] ?? 16;
$grade_value      = $attributes['gradeValue'] ?? 15;
$grade_improvement = $attributes['gradeImprovement'] ?? '+3 pts vs dernier semestre';
$enable_parallax  = $attributes['enableParallax'] ?? true;
$enable_animations = $attributes['enableAnimations'] ?? true;

// Build wrapper classes
$wrapper_classes = ['wp-block-jurible-hero-dashboard'];
if (!empty($attributes['align'])) {
    $wrapper_classes[] = 'align' . $attributes['align'];
}

// Build hero classes
$hero_classes = ['hero-dashboard'];
if ($enable_animations) {
    $hero_classes[] = 'has-animations';
}

// Data attributes for JS
$data_attrs = '';
if ($enable_parallax) {
    $data_attrs .= ' data-parallax="true"';
}
$data_attrs .= ' data-progress="' . esc_attr($progress_percent) . '"';

// Sidebar items with colors
$sidebar_items = [
    ['name' => 'Droit constitutionnel', 'color' => '#7C3AED', 'active' => true],
    ['name' => 'Droit de la famille', 'color' => '#3B82F6', 'active' => false],
    ['name' => 'Introduction au droit', 'color' => '#16A34A', 'active' => false],
    ['name' => 'Droit des personnes', 'color' => '#F59E0B', 'active' => false],
    ['name' => 'Institutions juridictionnelles', 'color' => '#EC4899', 'active' => false],
];
?>

<div <?php echo get_block_wrapper_attributes(['class' => implode(' ', $wrapper_classes)]); ?>>
    <div class="<?php echo esc_attr(implode(' ', $hero_classes)); ?>"<?php echo $data_attrs; ?>>
        <div class="hero-dashboard__wrapper">

            <!-- Floating Card: QCM -->
            <div class="hero-dashboard__float hero-dashboard__float--qcm" data-parallax-speed="0.03">
                <div class="hero-dashboard__qcm-title">QCM — Droit civil</div>
                <div class="hero-dashboard__qcm-option">
                    <div class="hero-dashboard__qcm-radio"></div>
                    Le dol est un vice du consentement
                </div>
                <div class="hero-dashboard__qcm-option is-correct">
                    <div class="hero-dashboard__qcm-radio"></div>
                    L'erreur sur la substance est toujours excusable
                </div>
                <div class="hero-dashboard__qcm-option">
                    <div class="hero-dashboard__qcm-radio"></div>
                    La violence économique n'est pas reconnue
                </div>
            </div>

            <!-- Floating Card: Flashcard -->
            <div class="hero-dashboard__float hero-dashboard__float--flash" data-parallax-speed="-0.04">
                <div class="hero-dashboard__flash-header">
                    <span class="hero-dashboard__flash-label">Question</span>
                    <span class="hero-dashboard__flash-count">1 / 10</span>
                </div>
                <div class="hero-dashboard__flash-question">
                    <div class="hero-dashboard__flash-q">Quels sont les quatre caractères de la règle de droit ?</div>
                    <div class="hero-dashboard__flash-hint">Cliquez pour voir la réponse</div>
                </div>
                <div class="hero-dashboard__flash-answer">
                    <div class="hero-dashboard__flash-answer-label">Réponse</div>
                    <div class="hero-dashboard__flash-a">La règle de droit est <strong>générale</strong>, <strong>impersonnelle</strong>, <strong>obligatoire</strong> et <strong>coercitive</strong>.</div>
                </div>
            </div>

            <!-- Floating Card: Grade -->
            <div class="hero-dashboard__float hero-dashboard__float--grade" data-parallax-speed="0.025">
                <div class="hero-dashboard__grade-icon">🏆</div>
                <div class="hero-dashboard__grade-label">Dernier partiel</div>
                <div class="hero-dashboard__grade-value" data-target="<?php echo esc_attr($grade_value); ?>">
                    <?php echo esc_html($grade_value); ?><span>/20</span>
                </div>
                <div class="hero-dashboard__grade-sub">↑ <?php echo esc_html($grade_improvement); ?></div>
            </div>

            <!-- Main Dashboard -->
            <div class="hero-dashboard__main">
                <!-- Top Bar -->
                <div class="hero-dashboard__topbar">
                    <div class="hero-dashboard__topbar-left">
                        <div class="hero-dashboard__logo">A</div>
                        <span class="hero-dashboard__brand">AideauxTD</span>
                    </div>
                    <div class="hero-dashboard__nav">
                        <div class="hero-dashboard__nav-item is-active"><span>🏠</span> Accueil</div>
                        <div class="hero-dashboard__nav-item"><span>🎓</span> Cours</div>
                        <div class="hero-dashboard__nav-item"><span>🤖</span> Outils IA</div>
                        <div class="hero-dashboard__nav-item"><span>💬</span> Communauté</div>
                    </div>
                    <div class="hero-dashboard__topbar-right">
                        <div class="hero-dashboard__action-btn">🌙</div>
                        <div class="hero-dashboard__action-btn">🔍</div>
                        <div class="hero-dashboard__user"><?php echo esc_html($user_initials); ?></div>
                    </div>
                </div>

                <!-- Body -->
                <div class="hero-dashboard__body">
                    <!-- Sidebar -->
                    <div class="hero-dashboard__sidebar">
                        <div class="hero-dashboard__sidebar-title">Matières L1</div>
                        <?php foreach ($sidebar_items as $item) : ?>
                            <div class="hero-dashboard__sidebar-item<?php echo $item['active'] ? ' is-active' : ''; ?>">
                                <div class="hero-dashboard__sidebar-dot" style="background: <?php echo esc_attr($item['color']); ?>;"></div>
                                <?php echo esc_html($item['name']); ?>
                            </div>
                        <?php endforeach; ?>
                        <div class="hero-dashboard__sidebar-section">Méthodologie <span>›</span></div>
                        <div class="hero-dashboard__sidebar-section">Organisation <span>›</span></div>
                        <div class="hero-dashboard__sidebar-section">Communauté <span>›</span></div>
                    </div>

                    <!-- Main Content -->
                    <div class="hero-dashboard__content">
                        <div class="hero-dashboard__welcome">
                            <h3>Bonjour <?php echo esc_html($user_name); ?> 👋</h3>
                            <p>Continuez votre progression en <?php echo esc_html($current_course); ?></p>
                        </div>

                        <div class="hero-dashboard__stats">
                            <div class="hero-dashboard__stat">
                                <div class="hero-dashboard__stat-label">Progression</div>
                                <div class="hero-dashboard__stat-value" data-counter="<?php echo esc_attr($progress_percent); ?>" data-suffix="%"><?php echo esc_html($progress_percent); ?>%</div>
                                <div class="hero-dashboard__stat-change">↑ +12% ce mois</div>
                            </div>
                            <div class="hero-dashboard__stat">
                                <div class="hero-dashboard__stat-label">QCM réussis</div>
                                <div class="hero-dashboard__stat-value" data-counter="<?php echo esc_attr($qcm_count); ?>"><?php echo esc_html($qcm_count); ?></div>
                                <div class="hero-dashboard__stat-change">↑ 92% de bonnes réponses</div>
                            </div>
                            <div class="hero-dashboard__stat">
                                <div class="hero-dashboard__stat-label">Fiches lues</div>
                                <div class="hero-dashboard__stat-value" data-counter="<?php echo esc_attr($fiches_count); ?>"><?php echo esc_html($fiches_count); ?></div>
                                <div class="hero-dashboard__stat-change">sur <?php echo esc_html($fiches_total); ?> fiches</div>
                            </div>
                        </div>

                        <div class="hero-dashboard__course">
                            <div class="hero-dashboard__course-thumb"></div>
                            <div class="hero-dashboard__course-info">
                                <div class="hero-dashboard__course-title"><?php echo esc_html($lesson_title); ?></div>
                                <div class="hero-dashboard__course-meta"><?php echo esc_html($lesson_meta); ?></div>
                                <div class="hero-dashboard__progress-bar">
                                    <div class="hero-dashboard__progress-fill" style="width: <?php echo esc_attr($progress_percent); ?>%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
