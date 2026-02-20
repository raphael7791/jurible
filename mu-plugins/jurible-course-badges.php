<?php
/**
 * Jurible - Badges du sommaire de cours (Optimisé)
 */

add_action('fluent_community/portal_footer', 'jurible_inject_course_badges_script');

function jurible_inject_course_badges_script() {
    ?>
    <script>
    (function() {
        'use strict';

        const TYPES = {
            'Leçon': { c: 'lecon', l: 'LEÇON', bg: true },
            'QCM': { c: 'qcm', l: 'QCM' },
            'Flashcards': { c: 'flashcards', l: 'FLASHCARDS' },
            'Flashcard': { c: 'flashcards', l: 'FLASHCARDS' },
            'Playlist complète': { c: 'playlist', l: '▶ PLAYLIST' },
            'Playlist': { c: 'playlist', l: '▶ PLAYLIST' },
            'Vidéo': { c: 'video', l: '▶ VIDÉO' },
            'Video': { c: 'video', l: '▶ VIDÉO' },
            'Commentaire d\'arrêt': { c: 'blue', l: 'COMMENTAIRE D\'ARRÊT' },
            'Commentaire de texte': { c: 'blue', l: 'COMMENTAIRE DE TEXTE' },
            'Dissertation': { c: 'blue', l: 'DISSERTATION' },
            'Question': { c: 'blue', l: 'QUESTION' },
            'Fiche d\'arrêt': { c: 'blue', l: 'FICHE D\'ARRÊT' },
            'Cas pratique': { c: 'blue', l: 'CAS PRATIQUE' },
            'Question de cours': { c: 'blue', l: 'QUESTION DE COURS' },
            'Annexe': { c: 'annexe', l: 'ANNEXE' },
            'Assessment': { c: 'assessment', l: 'ASSESSMENT' }
        };

        function applySidebarStyles() {
            document.querySelectorAll('aside.fcom_lesson_sidebar .jb-badge').forEach(el => {
                el.style.setProperty('font-size', '8px', 'important');
                el.style.setProperty('padding', '2px 6px', 'important');
                el.style.setProperty('margin-right', '5px', 'important');
            });
            document.querySelectorAll('aside.fcom_lesson_sidebar .jb-title').forEach(el => {
                el.style.setProperty('font-size', '12px', 'important');
            });
            document.querySelectorAll('aside.fcom_lesson_sidebar .fcom_section_item .fcom_heading_item > span:not([data-jb])').forEach(el => {
                el.style.setProperty('font-size', '12px', 'important');
            });
            document.querySelectorAll('aside.fcom_lesson_sidebar .fcom_section_primary_item h4').forEach(el => {
                el.style.setProperty('font-size', '12px', 'important');
            });
        }

        function transform() {
            // Transformer les titres de leçons
            document.querySelectorAll('.fcom_section_item_title span, .fcom_section_item .fcom_heading_item span').forEach(span => {
                if (span.dataset.jb || span.querySelector('.jb-badge')) return;
                const txt = span.textContent.trim();
                
                for (const [prefix, cfg] of Object.entries(TYPES)) {
                    const rx = new RegExp(`^${prefix}\\s*[:\\-]\\s*`, 'i');
                    if (rx.test(txt)) {
                        const title = txt.replace(rx, '').trim();
                        span.innerHTML = `<span class="jb-badge jb-badge--${cfg.c}">${cfg.l}</span><span class="jb-title">${title}</span>`;
                        span.dataset.jb = '1';
                        if (cfg.bg) span.closest('.fcom_section_item')?.classList.add('jb-row');
                        break;
                    }
                }
            });

            // Déplacer "X leçons" sur la même ligne
            document.querySelectorAll('.fcom_section_primary_item_title').forEach(container => {
                const h4 = container.querySelector('.title_line h4');
                const meta = container.querySelector('.fcom_meta_text');
                const primaryItem = container.closest('.fcom_section_primary_item');
                
                // Section "Fiches vidéo" - style spécial (toujours vérifier)
                if (h4 && h4.textContent.toLowerCase().includes('fiches vidéo')) {
                    primaryItem?.classList.add('jb-section-video');
                }
                
                if (container.dataset.jbMeta) return;
                
                if (h4 && meta && !h4.querySelector('.jb-count')) {
                    const count = document.createElement('span');
                    count.className = 'jb-count';
                    count.textContent = meta.textContent;
                    h4.appendChild(count);
                    meta.style.display = 'none';
                    container.dataset.jbMeta = '1';
                }
            });

            // Appliquer styles sidebar
            applySidebarStyles();
        }

        // Observer unique
        const obs = new MutationObserver(() => requestAnimationFrame(transform));
        obs.observe(document.body, { childList: true, subtree: true });
        
        // Init
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', transform);
        } else {
            transform();
        }
    })();
    </script>
    <?php
}

add_action('fluent_community/portal_head', 'jurible_inject_course_badges_styles');

function jurible_inject_course_badges_styles() {
    ?>
    <style>
    /* === BADGES === */
    .jb-badge {
        display: inline-flex;
        align-items: center;
        font-family: var(--jurible-font-family);
        font-size: clamp(8px, 2vw, 10px) !important;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: clamp(2px, 0.5vw, 3px) clamp(6px, 1.5vw, 8px);
        border-radius: var(--jurible-radius-s);
        margin-right: clamp(4px, 1vw, 6px);
        flex-shrink: 0;
        line-height: 1;
        white-space: nowrap;
    }

    /* Violet plein (Leçon) */
    .jb-badge--lecon {
        background-color: var(--jurible-secondary);
        color: #FFFFFF;
    }

    /* Rouge plein (Playlist, Vidéo) */
    .jb-badge--playlist,
    .jb-badge--video {
        background-color: var(--jurible-primary);
        color: #FFFFFF;
    }

    /* Vert clair (QCM) */
    .jb-badge--qcm {
        background-color: #ECFDF5;
        color: var(--jurible-success);
    }

    /* Orange clair (Flashcards) */
    .jb-badge--flashcards {
        background-color: #FFFBEB;
        color: #D97706;
    }

    /* Bleu clair (Commentaire, Dissertation, Question, Fiche, Cas pratique, Question de cours) */
    .jb-badge--blue {
        background-color: #EFF6FF;
        color: var(--jurible-info);
    }

    /* Vert clair (Annexe) */
    .jb-badge--annexe {
        background-color: #F3F4F6;
        color: #9CA3AF;
    }

    /* Rouge plein (Assessment) */
    .jb-badge--assessment {
        background-color: var(--jurible-primary);
        color: #FFFFFF;
    }

    /* === TITRES === */
    .jb-title,
    .fcom_section_item .fcom_heading_item span,
    .fcom_section_item .fcom_section_item_title span {
        font-size: clamp(12px, 3vw, 14px);
        font-weight: 400;
    }

    .jb-row .jb-title {
        font-weight: 600;
    }

    /* === LIGNE LEÇON AVEC FOND DÉGRADÉ === */
    .fcom_section_item.jb-row {
        background: linear-gradient(to right, #F3F0FF 0%, #FFFFFF 60%);
        border-left: none !important;
    }

    /* === SECTIONS THÈMES === */
    .fcom_section_primary_item {
        background-color: var(--jurible-text-dark, #111827) !important;
        border-radius: var(--jurible-radius-m) !important;
    }

    /* Section Fiches vidéo - dégradé rouge → violet */
    .fcom_section_primary_item.jb-section-video {
        background: linear-gradient(to right, var(--jurible-primary), var(--jurible-secondary)) !important;
    }

    .fcom_section_primary_item h4,
    .fcom_section_primary_item .fcom_meta_text {
        color: #FFFFFF !important;
    }

    .fcom_section_primary_item h4 {
        font-size: clamp(12px, 3vw, 14px) !important;
        font-weight: 600 !important;
        display: flex !important;
        align-items: center !important;
        gap: clamp(4px, 1vw, 8px) !important;
    }

    .fcom_section_primary_item .el-icon,
    .fcom_section_primary_item .el-icon svg {
        color: #FFFFFF !important;
        fill: #FFFFFF !important;
    }

    /* Compteur leçons */
    .jb-count {
        font-size: clamp(10px, 2.5vw, 12px) !important;
        font-weight: 400 !important;
        opacity: 0.7;
        white-space: nowrap;
    }

    /* === MODE SOMBRE === */
    html.dark .jb-row {
        background: linear-gradient(to right, rgba(124, 58, 237, 0.1) 0%, transparent 60%);
    }

    html.dark .jb-badge--qcm {
        background-color: rgba(16, 185, 129, 0.15);
    }

    html.dark .jb-badge--flashcards {
        background-color: rgba(217, 119, 6, 0.15);
    }

    html.dark .jb-badge--blue {
        background-color: rgba(59, 130, 246, 0.15);
    }

    html.dark .fcom_section_primary_item {
        background-color: #1E1E2E !important;
    }

    html.dark .fcom_section_primary_item h4,
    html.dark .fcom_section_primary_item .jb-count {
        color: #FFFFFF !important;
    }
    </style>
    <?php
}