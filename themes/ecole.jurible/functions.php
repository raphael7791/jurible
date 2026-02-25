<?php
/**
 * Jurible Membres - Thème enfant
 * Code spécifique pour l'espace membre avec Fluent Community
 */

// =============================================
// JURIBLE BLOCKS REACT - Fluent Community
// =============================================

// Désactiver la conversion des emojis en images
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

// Autoriser les blocs dans Fluent Community
add_filter('fluent_community/allowed_block_types', function($blockTypes) {
    $blockTypes[] = 'jurible/infobox';
    $blockTypes[] = 'jurible/sommaire';
    $blockTypes[] = 'jurible/lien-lecon';
    $blockTypes[] = 'jurible/bouton';
    $blockTypes[] = 'jurible/citation';
    $blockTypes[] = 'jurible/flashcards';
    $blockTypes[] = 'jurible/assessment';
    $blockTypes[] = 'jurible/playlist';
    return $blockTypes;
});

// Autoriser les assets du plugin
add_filter('fluent_com_editor/asset_listed_slugs', function($slugs) {
    $slugs[] = 'jurible-blocks-react';
    return $slugs;
});

// Charger le JS avec attente de wp.blocks
add_action('fluent_community/block_editor_footer', function() {
    $infobox_js = plugins_url('jurible-blocks-react/build/infobox/index.js');
    $sommaire_js = plugins_url('jurible-blocks-react/build/sommaire/index.js');
    $lien_lecon_js = plugins_url('jurible-blocks-react/build/lien-lecon/index.js');
    $bouton_js = plugins_url('jurible-blocks-react/build/bouton/index.js');
    $citation_js = plugins_url('jurible-blocks-react/build/citation/index.js');
    $flashcards_js = plugins_url('jurible-blocks-react/build/flashcards/index.js');
    $assessment_js = plugins_url('jurible-blocks-react/build/assessment/index.js');
    $playlist_js = plugins_url('jurible-blocks-react/build/playlist/index.js');
    ?>
    <script>
    (function loadJuribleBlocks() {
        if (typeof wp !== 'undefined' && typeof wp.blocks !== 'undefined' && typeof wp.blocks.registerBlockType !== 'undefined') {
            var scripts = [
                '<?php echo esc_url($infobox_js); ?>',
                '<?php echo esc_url($sommaire_js); ?>',
                '<?php echo esc_url($lien_lecon_js); ?>',
                '<?php echo esc_url($bouton_js); ?>',
                '<?php echo esc_url($citation_js); ?>',
                '<?php echo esc_url($flashcards_js); ?>',
                '<?php echo esc_url($assessment_js); ?>',
                '<?php echo esc_url($playlist_js); ?>'
            ];
            scripts.forEach(function(src) {
                var s = document.createElement('script');
                s.src = src;
                document.body.appendChild(s);
            });
        } else {
            setTimeout(loadJuribleBlocks, 50);
        }
    })();
    </script>
    <?php
});

// Passer le nonce REST API au frontend pour les assessments
add_action('fluent_community/portal_head', function() {
    $pull_zone_url = get_option('jurible_playlist_pull_zone_url', 'https://iframe.mediadelivery.net/embed/35843/');
    ?>
    <script>
    var wpApiSettings = {
        nonce: '<?php echo wp_create_nonce('wp_rest'); ?>'
    };
    var juriblePlaylistConfig = {
        pullZoneUrl: '<?php echo esc_js($pull_zone_url); ?>'
    };
    </script>
    <?php
}, 5);

// Charger le CSS des blocs côté front Fluent Community
add_action('fluent_community/portal_head', function() {
    $styles = [
        plugins_url('jurible-blocks-react/build/infobox/style-index.css'),
        plugins_url('jurible-blocks-react/build/sommaire/style-index.css'),
        plugins_url('jurible-blocks-react/build/lien-lecon/style-index.css'),
        plugins_url('jurible-blocks-react/build/bouton/style-index.css'),
        plugins_url('jurible-blocks-react/build/citation/style-index.css'),
        plugins_url('jurible-blocks-react/build/flashcards/style-index.css'),
        plugins_url('jurible-blocks-react/build/assessment/style-index.css'),
        plugins_url('jurible-blocks-react/build/playlist/style-index.css'),
    ];
    foreach ($styles as $style) {
        echo '<link rel="stylesheet" href="' . esc_url($style) . '">';
    }
});

// Charger les scripts front (view.js)
add_action('fluent_community/portal_head', function() {
    $flashcards_view = plugins_url('jurible-blocks-react/build/flashcards/view.js');
    $assessment_view = plugins_url('jurible-blocks-react/build/assessment/view.js');
    $playlist_view = plugins_url('jurible-blocks-react/build/playlist/view.js');
    echo '<script src="' . esc_url($flashcards_view) . '" defer></script>';
    echo '<script src="' . esc_url($assessment_view) . '" defer></script>';
    echo '<script src="' . esc_url($playlist_view) . '" defer></script>';
}, 20);

// Charger Google Fonts et le design system Jurible pour Fluent Community
// Priorité 999 pour charger APRÈS tous les CSS de Fluent Community
add_action('fluent_community/portal_head', function() {
    // Préconnexion Google Fonts pour performance
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    // Charger Poppins directement via <link> (plus fiable que @import)
    echo '<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">';
    // Design system
    $css_url = get_theme_file_uri('assets/css/jurible-design-system.css');
    echo '<link rel="stylesheet" href="' . esc_url($css_url) . '">';
}, 999);

add_action('fluent_community/block_editor_head', function() {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">';
    $css_url = get_theme_file_uri('assets/css/jurible-design-system.css');
    echo '<link rel="stylesheet" href="' . esc_url($css_url) . '">';
}, 999);

// Charger le design system sur les pages WordPress standard aussi
add_action('wp_enqueue_scripts', function() {
    $css_url = get_theme_file_uri('assets/css/jurible-design-system.css');
    wp_enqueue_style('jurible-design-system', $css_url, [], '1.0.0');
});

// Ajouter les ancres aux titres H2 côté front Fluent Community
add_action('fluent_community/portal_head', function() {
    ?>
    <script>
    function addAnchorsToHeadings() {
        document.querySelectorAll('h2').forEach(function(h2) {
            if (!h2.id) {
                var text = h2.textContent || h2.innerText;
                var slug = text.toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-|-$/g, '');
                h2.id = slug;
            }
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', addAnchorsToHeadings);
    } else {
        addAnchorsToHeadings();
    }
    setInterval(addAnchorsToHeadings, 1000);
    </script>
    <?php
});

// Passer les settings Fluent Community au JavaScript
add_action('fluent_community/block_editor_footer', function() {
    $portal_slug = 'accueil';
    if (function_exists('FluentCommunity')) {
        $settings = get_option('fluent_community_settings', []);
        if (!empty($settings['slug'])) {
            $portal_slug = $settings['slug'];
        }
    }
    ?>
    <script>
    window.fluentCommunityPortalSlug = '<?php echo esc_js($portal_slug); ?>';
    </script>
    <?php
});

// Personnaliser les textes Fluent Community
add_filter('gettext', 'jurible_membres_customize_fluent_texts', 20, 3);
function jurible_membres_customize_fluent_texts($translated, $original, $domain) {
    if ($domain === 'fluent-community') {
        if ($original === 'Documents & Files') {
            return 'Pour approfondir';
        }
    }
    return $translated;
}

// Charger le CSS du plugin playlist dans Fluent Community
add_action('fluent_community/portal_head', function() {
    $css_url = plugins_url('jurible-playlist/assets/css/playlist.css');
    echo '<link rel="stylesheet" href="' . esc_url($css_url) . '?v=1">';
});

// Lightbox pour les images dans Fluent Community
add_action('fluent_community/portal_head', function() {
    ?>
    <style>
    .jurible-lightbox-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 99999;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s, visibility 0.3s;
        cursor: zoom-out;
    }
    .jurible-lightbox-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .jurible-lightbox-overlay img {
        max-width: 90%;
        max-height: 90%;
        border-radius: 8px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.5);
    }
    .fcom_lesson_details img {
        cursor: zoom-in;
    }
    .jurible-lightbox-close {
        position: absolute;
        top: 20px;
        right: 20px;
        color: white;
        font-size: 30px;
        cursor: pointer;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(255,255,255,0.1);
        transition: background 0.2s;
    }
    .jurible-lightbox-close:hover {
        background: rgba(255,255,255,0.2);
    }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const overlay = document.createElement('div');
        overlay.className = 'jurible-lightbox-overlay';
        overlay.innerHTML = '<span class="jurible-lightbox-close">&times;</span><img src="" alt="">';
        document.body.appendChild(overlay);
        const overlayImg = overlay.querySelector('img');
        overlay.addEventListener('click', function() {
            overlay.classList.remove('active');
        });
        function initLightbox() {
            document.querySelectorAll('.fcom_lesson_details img').forEach(function(img) {
                if (img.dataset.lightboxInit) return;
                img.dataset.lightboxInit = 'true';
                img.addEventListener('click', function(e) {
                    e.preventDefault();
                    overlayImg.src = this.src;
                    overlay.classList.add('active');
                });
            });
        }
        initLightbox();
        setInterval(initLightbox, 1000);
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                overlay.classList.remove('active');
            }
        });
    });
    </script>
    <?php
});

// Injecter le CSS des légendes d'images dans l'éditeur Fluent Community
add_action('fluent_community/block_editor_footer', function() {
    ?>
    <script>
    (function injectImageCaptionStyles() {
        var cssStyles = `
            .wp-block-image {
                margin-top: clamp(1rem, 4vw, 3rem) !important;
                margin-bottom: clamp(1rem, 4vw, 3rem) !important;
            }
            .wp-block-image img {
                border: 1px solid #E5E7EB !important;
                border-radius: 12px !important;
            }
            .wp-block-image figcaption {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 0.5rem !important;
                color: #9CA3AF !important;
                font-size: 0.875rem !important;
                font-style: italic !important;
                margin-top: 0.5rem !important;
                text-align: center !important;
            }
        `;
        function injectIntoIframes() {
            if (!document.getElementById('jurible-image-caption-styles')) {
                var style = document.createElement('style');
                style.id = 'jurible-image-caption-styles';
                style.textContent = cssStyles;
                document.head.appendChild(style);
            }
            var iframes = document.querySelectorAll('iframe[name="editor-canvas"]');
            iframes.forEach(function(iframe) {
                try {
                    var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    if (iframeDoc && !iframeDoc.getElementById('jurible-image-caption-styles')) {
                        var style = iframeDoc.createElement('style');
                        style.id = 'jurible-image-caption-styles';
                        style.textContent = cssStyles;
                        iframeDoc.head.appendChild(style);
                    }
                } catch (e) {}
            });
        }
        injectIntoIframes();
        setTimeout(injectIntoIframes, 1000);
        setTimeout(injectIntoIframes, 3000);
        setTimeout(injectIntoIframes, 5000);
    })();
    </script>
    <?php
});

// Enable Social Login FluentCommunity
add_filter('fluent_community/auth/settings', function($settings) {
    $settings['allow_social_login'] = true;
    return $settings;
});

add_filter('fluent_community/get_auth_settings', function($settings) {
    $settings['allow_social_login'] = true;
    return $settings;
});

// No link in logo fluent login page + Remove admin bar spacing
add_action('wp_head', function() {
    ?>
    <style>
    .fcom_logo a {
        pointer-events: none !important;
        cursor: default !important;
    }
    /* Supprimer l'espace pour l'admin bar (override header.css de Fluent) */
    .admin-bar {
        padding-top: 0 !important;
    }
    body.admin-bar,
    html.admin-bar body {
        padding-top: 0 !important;
        margin-top: 0 !important;
    }
    </style>
    <?php
});

// Redirection my account page surecart
add_filter('sc_customer_dashboard_back_home_url', function($url) {
    return 'https://ecole.jurible.com/accueil/';
});

add_filter('sc_customer_dashboard_store_logo_url', function($url) {
    return 'https://ecole.jurible.com/accueil/';
});
