<?php

# Bases du thème
# Dans https://capitainewp.io/formations/wordpress-full-site-editing/base-theme-fse/#le-fichier-functions-php

# ACF JSON - Charger depuis le thème parent (même si un thème enfant est actif)
add_filter('acf/settings/load_json', function($paths) {
    $paths[] = get_template_directory() . '/acf-json';
    return $paths;
});

# Retirer les accents des noms de fichiers
add_filter("sanitize_file_name", "remove_accents");

# Shortcode pour l'année dynamique [jurible_year]
function jurible_year_shortcode() {
    return date('Y');
}
add_shortcode('jurible_year', 'jurible_year_shortcode');

# Shortcode pour le copyright complet [jurible_copyright]
function jurible_copyright_shortcode() {
    $year = date('Y');
    return '<p class="footer-copyright" style="font-size:13px;margin:0;">© ' . $year . ' Jurible.com - Tous droits réservés</p>';
}
add_shortcode('jurible_copyright', 'jurible_copyright_shortcode');

# Shortcode pour la vidéo YouTube du cours [course_video_embed]
function jurible_course_video_embed_shortcode() {
    $video_url = get_field('video_url');
    if (empty($video_url)) {
        return '';
    }

    // Extraire l'ID de la vidéo YouTube depuis différents formats d'URL
    $video_id = '';

    // Format: youtu.be/VIDEO_ID
    if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $video_url, $matches)) {
        $video_id = $matches[1];
    }
    // Format: youtube.com/watch?v=VIDEO_ID
    elseif (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $video_url, $matches)) {
        $video_id = $matches[1];
    }
    // Format: youtube.com/embed/VIDEO_ID
    elseif (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $video_url, $matches)) {
        $video_id = $matches[1];
    }

    if (empty($video_id)) {
        return '';
    }

    $embed_url = 'https://www.youtube.com/embed/' . $video_id;

    return '<div style="position:absolute;top:0;left:0;right:0;bottom:0;"><iframe src="' . esc_url($embed_url) . '" style="width:100%;height:100%;border:0;border-radius:8px;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
}
add_shortcode('course_video_embed', 'jurible_course_video_embed_shortcode');

# Shortcode pour afficher le sommaire des cours dynamique [course_sommaire]
function jurible_course_sommaire_shortcode() {
    $sommaire = get_field('sommaire_cours');
    if (empty($sommaire)) {
        return '';
    }

    // Détecter si c'est du JSON (nouveau format) ou du texte (ancien format)
    $data = json_decode($sommaire, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
        // Nouveau format JSON avec types
        return jurible_render_sommaire_json($data);
    } else {
        // Ancien format texte (rétrocompatibilité)
        return jurible_render_sommaire_text($sommaire);
    }
}

/**
 * Rendu du sommaire au format JSON (avec badges de type)
 */
function jurible_render_sommaire_json($data) {
    $output = '<div class="sommaire-cours">';

    foreach ($data as $theme) {
        $output .= '<div class="sommaire-cours__theme">';
        $output .= '<h4 class="sommaire-cours__theme-title">' . esc_html($theme['title']) . '</h4>';
        $output .= '<ul class="sommaire-cours__items">';

        foreach ($theme['items'] as $item) {
            $type = isset($item['type']) ? $item['type'] : 'lecon';
            $badge = jurible_get_type_badge($type);

            $output .= '<li class="sommaire-cours__item sommaire-cours__item--' . esc_attr($type) . '">';
            $output .= '<span class="sommaire-cours__badge sommaire-cours__badge--' . esc_attr($type) . '">' . $badge['label'] . '</span>';
            $output .= '<span class="sommaire-cours__item-title">' . esc_html($item['title']) . '</span>';
            $output .= '</li>';
        }

        $output .= '</ul>';
        $output .= '</div>';
    }

    $output .= '</div>';

    return $output;
}

/**
 * Retourne le badge correspondant au type de contenu
 */
function jurible_get_type_badge($type) {
    $types = [
        'lecon' => ['label' => 'Leçon', 'icon' => '📄', 'color' => '#4A90D9'],
        'qcm' => ['label' => 'QCM', 'icon' => '✅', 'color' => '#34C759'],
        'flashcard' => ['label' => 'Flashcards', 'icon' => '🃏', 'color' => '#F59E0B'],
        'fiche-arret' => ['label' => 'Fiche-arret', 'icon' => '⚖️', 'color' => '#A3E635'],
        'cas-pratique' => ['label' => 'Cas-pratique', 'icon' => '📝', 'color' => '#34D399'],
        'dissertation' => ['label' => 'Dissertation', 'icon' => '✍️', 'color' => '#34D399'],
        'question' => ['label' => 'Question-cours', 'icon' => '❓', 'color' => '#34D399'],
        'analyse-arret' => ['label' => 'Analyse-arret', 'icon' => '🔍', 'color' => '#34D399'],
        'commentaire-arret' => ['label' => 'Commentaire-arret', 'icon' => '📋', 'color' => '#34D399'],
        'commentaire-texte' => ['label' => 'Commentaire-texte', 'icon' => '📝', 'color' => '#34D399'],
        'commentaire' => ['label' => 'Commentaire', 'icon' => '📋', 'color' => '#34D399'],
        'annexe' => ['label' => 'Annexe', 'icon' => '📎', 'color' => '#6B7280'],
        'annale' => ['label' => 'Annale', 'icon' => '📚', 'color' => '#FF9500'],
        'fiche-video' => ['label' => 'Fiche vidéo', 'icon' => '🎥', 'color' => '#FF3B30'],
        'mindmap' => ['label' => 'Mindmap', 'icon' => '🗺️', 'color' => '#5AC8FA'],
        'video' => ['label' => 'Vidéo', 'icon' => '🎬', 'color' => '#FF2D55'],
    ];

    return isset($types[$type]) ? $types[$type] : $types['lecon'];
}

/**
 * Rendu du sommaire au format texte (rétrocompatibilité)
 */
function jurible_render_sommaire_text($sommaire) {
    $output = '<div class="sommaire-cours">';
    $lines = explode("\n", $sommaire);
    $current_theme_items = [];
    $current_theme_title = '';

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }

        // Détecter un titre de thème (commence par ##)
        if (strpos($line, '##') === 0) {
            // Fermer le thème précédent s'il existe
            if (!empty($current_theme_title) && !empty($current_theme_items)) {
                $output .= jurible_render_sommaire_theme_text($current_theme_title, $current_theme_items);
            }
            // Nouveau thème
            $current_theme_title = trim(str_replace('##', '', $line));
            $current_theme_items = [];
        }
        // Détecter un cours (commence par -)
        elseif (strpos($line, '-') === 0) {
            $current_theme_items[] = trim(substr($line, 1));
        }
    }

    // Fermer le dernier thème
    if (!empty($current_theme_title) && !empty($current_theme_items)) {
        $output .= jurible_render_sommaire_theme_text($current_theme_title, $current_theme_items);
    }

    $output .= '</div>';
    return $output;
}

function jurible_render_sommaire_theme_text($title, $items) {
    $html = '<div class="sommaire-cours__theme">';
    $html .= '<h4 class="sommaire-cours__theme-title">' . esc_html($title) . '</h4>';
    $html .= '<ul class="sommaire-cours__items">';

    foreach ($items as $item) {
        // Détecter le type à partir du titre
        $type = jurible_detect_type_from_title($item);
        $badge = jurible_get_type_badge($type);

        $html .= '<li class="sommaire-cours__item sommaire-cours__item--' . esc_attr($type) . '">';
        $html .= '<span class="sommaire-cours__badge sommaire-cours__badge--' . esc_attr($type) . '">' . $badge['label'] . '</span>';
        $html .= '<span class="sommaire-cours__item-title">' . esc_html($item) . '</span>';
        $html .= '</li>';
    }

    $html .= '</ul>';
    $html .= '</div>';

    return $html;
}

/**
 * Détecte le type de contenu depuis le titre (pour format texte)
 */
function jurible_detect_type_from_title($title) {
    $title_lower = mb_strtolower($title);

    // Les titres commencent souvent par "Type : Titre"
    if (strpos($title_lower, 'qcm') === 0 || strpos($title_lower, 'qcm :') !== false) {
        return 'qcm';
    }
    if (strpos($title_lower, 'flashcard') === 0 || strpos($title_lower, 'flashcards :') !== false) {
        return 'flashcard';
    }
    if (strpos($title_lower, 'cas pratique') === 0) {
        return 'cas-pratique';
    }
    if (strpos($title_lower, 'fiche d\'arrêt') === 0 || strpos($title_lower, 'fiche d\'arret') === 0) {
        return 'fiche-arret';
    }
    if (strpos($title_lower, 'question de cours') === 0) {
        return 'question';
    }
    if (strpos($title_lower, 'dissertation') === 0) {
        return 'dissertation';
    }
    if (strpos($title_lower, 'analyse d\'arrêt') === 0 || strpos($title_lower, 'analyse d\'arret') === 0 || strpos($title_lower, 'analyse-arret') === 0) {
        return 'analyse-arret';
    }
    if (strpos($title_lower, 'commentaire d\'arrêt') === 0 || strpos($title_lower, 'commentaire d\'arret') === 0 || strpos($title_lower, 'commentaire-arret') === 0) {
        return 'commentaire-arret';
    }
    if (strpos($title_lower, 'commentaire de texte') === 0 || strpos($title_lower, 'commentaire-texte') === 0) {
        return 'commentaire-texte';
    }
    if (strpos($title_lower, 'commentaire') === 0) {
        return 'commentaire';
    }
    if (strpos($title_lower, 'annexe') === 0) {
        return 'annexe';
    }
    if (strpos($title_lower, 'leçon') === 0 || strpos($title_lower, 'lecon') === 0) {
        return 'lecon';
    }
    if (strpos($title_lower, 'annale') !== false) {
        return 'annale';
    }
    if (strpos($title_lower, 'fiche') !== false && strpos($title_lower, 'vidéo') !== false) {
        return 'fiche-video';
    }
    if (strpos($title_lower, 'mindmap') !== false) {
        return 'mindmap';
    }

    return 'lecon';
}

add_shortcode('course_sommaire', 'jurible_course_sommaire_shortcode');

# Shortcode pour le badge du cours [course_badge] → "Cours complet de droit des obligations"
function jurible_course_badge_shortcode() {
    $matiere = get_field('matiere_name');
    if (empty($matiere)) {
        return 'Cours complet';
    }
    return 'Cours complet de ' . esc_html($matiere);
}
add_shortcode('course_badge', 'jurible_course_badge_shortcode');

# Shortcode pour le titre du cours [course_title] → "Maîtrisez le droit des obligations"
function jurible_course_title_shortcode() {
    $matiere = get_field('matiere_name');
    if (empty($matiere)) {
        return 'Maîtrisez cette matière';
    }
    return 'Maîtrisez le ' . esc_html($matiere);
}
add_shortcode('course_title', 'jurible_course_title_shortcode');

# Shortcode pour le titre Pain Points [course_pain_title] → "Le droit des obligations : une matière redoutée !"
function jurible_course_pain_title_shortcode() {
    $matiere = get_field('matiere_name');
    if (empty($matiere)) {
        return 'Une matière redoutée !';
    }
    return 'Le ' . esc_html($matiere) . ' : une matière redoutée !';
}
add_shortcode('course_pain_title', 'jurible_course_pain_title_shortcode');

# Retirer le pattern directory et la suggestion de blocs
remove_action("enqueue_block_editor_assets", "wp_enqueue_editor_block_directory_assets");
remove_theme_support("core-block-patterns");

# Ajouter des fonctionnalités
add_theme_support("editor-styles");
add_editor_style("style-editor.css");
add_theme_support("custom-logo", [
    "height"      => 32,
    "width"       => 120,
    "flex-height" => true,
    "flex-width"  => true,
]);


# Déclarer les scripts et les styles
function jurible_register_assets()
{
    # Intégrer des feuilles de style sur le site
    wp_enqueue_style("main", get_stylesheet_uri(), [], wp_get_theme()->get('Version'));

    # Footer accordion script (mobile)
    wp_enqueue_script(
        "jurible-footer-accordion",
        get_theme_file_uri('assets/js/footer-accordion.js'),
        [],
        wp_get_theme()->get('Version'),
        true
    );

    # Désactiver le CSS de certains blocs
    wp_dequeue_style("wp-block-columns");
}
add_action("wp_enqueue_scripts", "jurible_register_assets");


# Autoriser l'import de fichiers SVG et WebP
function jurible_allow_mime($mimes)
{
    $mimes["svg"] = "image/svg+xml";
    $mimes["webp"] = "image/webp";

    return $mimes;
}
add_filter("upload_mimes", "jurible_allow_mime");


# Autorisations supplémentaires pour le WebP
function jurible_allow_file_types($types, $file, $filename, $mimes)
{
    if (false !== strpos($filename, ".webp")) {
        $types["ext"] = "webp";
        $types["type"] = "image/webp";
    }

    return $types;
}
add_filter("wp_check_filetype_and_ext", "jurible_allow_file_types", 10, 4);

# Charger les styles de blocs personnalisés
# Dans https://capitainewp.io/formations/wordpress-full-site-editing/surcharger-css-blocs-natifs/#automatiser-le-chargement-des-feuilles-de-styles
function jurible_register_blocks_assets()
{
    $files = glob(get_template_directory() . "/assets/css/*.css");

    foreach ($files as $file) {
        $filename   = basename($file, ".css");
        $block_name = str_replace("core-", "core/", $filename);

        wp_enqueue_block_style(
            $block_name,
            [
                "handle" => "jurible-{$filename}",
                "src"    => get_theme_file_uri("assets/css/{$filename}.css"),
                "path"   => get_theme_file_path("assets/css/{$filename}.css"),
                "ver"    => filemtime(get_theme_file_path("assets/css/{$filename}.css"))
            ]
        );
    }
}
add_action("init", "jurible_register_blocks_assets");


# Retirer les variations de styles de blocs natifs 
# Dans https://capitainewp.io/formations/wordpress-full-site-editing/retirer-variations-styles-blocs-natifs/#la-methode-javascript
function jurible_deregister_blocks_variations()
{
    wp_enqueue_script(
        "unregister-styles",
        get_template_directory_uri() . "/assets/js/unregister-blocks-styles.js",
        ["wp-blocks", "wp-dom-ready", "wp-edit-post"],
        "1.0",
        true // Important pour que ça fonctionne dans le FSE et Gut
    );
}
add_action("enqueue_block_assets", "jurible_deregister_blocks_variations");


# Activer toutes les fonctionnalités de l'éditeur de blocks aux administrateurs
# Dans https://capitainewp.io/formations/wordpress-full-site-editing/hooker-le-theme-json-en-php/#offrir-une-experience-differente-en-fonction-des-roles-utilisateurs
function jurible_filter_theme_json_theme($theme_json)
{
    if (!current_user_can("edit_theme_options")) {
        return $theme_json;
    }

    $new_data = json_decode(
        file_get_contents(get_theme_file_path("admin.json")),
        true
    );

    return $theme_json->update_with($new_data);
}
//add_filter('wp_theme_json_data_theme', 'jurible_filter_theme_json_theme');


# Ajouter des catégories de compositions personnalisées
# Dans https://capitainewp.io/formations/wordpress-full-site-editing/categories-compositions/#declarer-des-categories-de-compositions
function jurible_register_patterns_categories()
{
    register_block_pattern_category(
        "marketing",
        ["label" => __("Marketing", "jurible")]
    );

    register_block_pattern_category(
        "cards",
        ["label" => __("Cartes", "jurible")]
    );

    register_block_pattern_category(
        "hero",
        ["label" => __("Hero", "jurible")]
    );

    register_block_pattern_category(
        "posts",
        ["label" => __("Publications", "jurible")]
    );

    register_block_pattern_category(
        "pages",
        ["label" => __("Pages complètes", "jurible")]
    );
register_block_pattern_category(
        "contenu",
        ["label" => __("Contenu", "jurible")]
    );

    register_block_pattern_category(
        "commerce",
        ["label" => __("Commerce", "jurible")]
    );

    register_block_pattern_category(
        "confiance",
        ["label" => __("Confiance", "jurible")]
    );

    register_block_pattern_category(
        "equipe",
        ["label" => __("Équipe", "jurible")]
    );

    register_block_pattern_category(
        "structure",
        ["label" => __("Structure", "jurible")]
    );
}
add_filter("init", "jurible_register_patterns_categories");


# Retirer certains blocs de l'éditeur
# Dans https://capitainewp.io/formations/wordpress-full-site-editing/desactiver-blocs-gutenberg/#exclusionnbsp-retirer-seulement-certains-blocs
function jurible_deregister_blocks($allowed_block_types, $editor_context)
{
    $blocks_to_disable = [
        "core/preformatted",
        "core/pullquote",
        "core/quote",
        "core/rss",
        "core/verse",
    ];
    $active_blocks = array_keys(
        WP_Block_Type_Registry::get_instance()->get_all_registered()
    );

    return array_values(array_diff($active_blocks, $blocks_to_disable));
}
add_filter("allowed_block_types_all", "jurible_deregister_blocks", 10, 2);


# Ajouter des meta dans la balise <head> de la page
# Dans https://capitainewp.io/formations/wordpress-full-site-editing/header-footer-full-site-editing/#utiliser-les-hooks-pour-inserer-des-balises
function jurible_add_google_site_verification()
{
    echo '<meta name="google-site-verification" content="12345" />';
}
add_action("wp_head", "jurible_add_google_site_verification");


# Ajouter une classe sur la balise <body>
# Dans https://capitainewp.io/formations/wordpress-full-site-editing/header-footer-full-site-editing/#utiliser-les-hooks-pour-inserer-des-balises
function jurible_body_class($classes)
{
    $classes[] = "jurible";
    return $classes;
}
add_filter("body_class", "jurible_body_class");


# Modifier les paramètes d'une boucle de requête pour faire une liste Related Posts par exemple
# Dans : https://capitainewp.io/formations/wordpress-full-site-editing/modifier-parametres-boucles-requetes-php/#une-boucle-personnalisee-related-posts
function jurible_related_posts_query($query_args, $block)
{
    if ($block->context["queryId"] === 3) {
        $current_post_id = get_the_ID();
        $current_post_categories = wp_get_post_categories($current_post_id, ["fields" => "ids"]);

        $query_args["post__not_in"] = [$current_post_id];
        $query_args["cat"] = $current_post_categories;
    }

    return $query_args;
}
add_filter("query_loop_block_query_vars", "jurible_related_posts_query", 10, 2);


# Retirer des niveaux de titre dans les blocs de type Heading
# Dans : https://capitainewp.io/formations/wordpress-full-site-editing/retirer-des-niveaux-de-titre-dans-le-bloc-titre/#retirer-les-niveaux-de-titres
function jurible_remove_heading_levels($args, $block_type)
{
    if ($block_type !== "core/heading") {
        return $args;
    }

    $args["attributes"]["levelOptions"]["default"] = [1, 2, 3, 4];

    return $args;
}
add_action("register_block_type_args", "jurible_remove_heading_levels", 10, 2);


# Enregistrer les Block Styles pour core/image
function jurible_register_image_block_styles()
{
    // Style Card - ratio 16:9, arrondi, ombre
    register_block_style("core/image", [
        "name"  => "card",
        "label" => __("Card", "jurible"),
    ]);

    // Styles Avatar
    register_block_style("core/image", [
        "name"  => "avatar-sm",
        "label" => __("Avatar SM (32px)", "jurible"),
    ]);

    register_block_style("core/image", [
        "name"  => "avatar-md",
        "label" => __("Avatar MD (48px)", "jurible"),
    ]);

    register_block_style("core/image", [
        "name"  => "avatar-lg",
        "label" => __("Avatar LG (64px)", "jurible"),
    ]);

    register_block_style("core/image", [
        "name"  => "avatar-xl",
        "label" => __("Avatar XL (96px)", "jurible"),
    ]);

    // Style Hero - pleine largeur, ratio 21:9
    register_block_style("core/image", [
        "name"  => "hero",
        "label" => __("Hero (21:9)", "jurible"),
    ]);
}
add_action("init", "jurible_register_image_block_styles");


# Charger le CSS media
function jurible_enqueue_media_styles()
{
    wp_enqueue_style(
        "jurible-media",
        get_template_directory_uri() . "/assets/css/media.css",
        [],
        filemtime(get_template_directory() . "/assets/css/media.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_media_styles");
add_action("enqueue_block_assets", "jurible_enqueue_media_styles");


# Enregistrer les Block Styles pour core/button
function jurible_register_button_block_styles()
{
    register_block_style("core/button", [
        "name"  => "primary",
        "label" => __("Primary", "jurible"),
        "is_default" => true,
    ]);

    register_block_style("core/button", [
        "name"  => "secondary",
        "label" => __("Secondary", "jurible"),
    ]);

    register_block_style("core/button", [
        "name"  => "outline",
        "label" => __("Outline", "jurible"),
    ]);

    register_block_style("core/button", [
        "name"  => "ghost",
        "label" => __("Ghost", "jurible"),
    ]);

    register_block_style("core/button", [
        "name"  => "link",
        "label" => __("Link", "jurible"),
    ]);

    register_block_style("core/button", [
        "name"  => "gray",
        "label" => __("Gray", "jurible"),
    ]);

    register_block_style("core/button", [
        "name"  => "destructive",
        "label" => __("Destructive", "jurible"),
    ]);

    register_block_style("core/button", [
        "name"  => "accent",
        "label" => __("Accent", "jurible"),
    ]);

    register_block_style("core/button", [
        "name"  => "cta-white",
        "label" => __("CTA White", "jurible"),
    ]);

    register_block_style("core/button", [
        "name"  => "primary-white",
        "label" => __("Primary White", "jurible"),
    ]);

    register_block_style("core/button", [
        "name"  => "ghost-white",
        "label" => __("Ghost White", "jurible"),
    ]);
}
add_action("init", "jurible_register_button_block_styles");


# Charger le CSS buttons
function jurible_enqueue_buttons_styles()
{
    wp_enqueue_style(
        "jurible-buttons",
        get_template_directory_uri() . "/assets/css/buttons.css",
        [],
        filemtime(get_template_directory() . "/assets/css/buttons.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_buttons_styles");
add_action("enqueue_block_assets", "jurible_enqueue_buttons_styles");


# Charger la lightbox sur les pages produit
function jurible_enqueue_lightbox()
{
    if (is_singular('sc_product')) {
        wp_enqueue_style(
            "jurible-lightbox",
            get_template_directory_uri() . "/assets/css/lightbox.css",
            [],
            filemtime(get_template_directory() . "/assets/css/lightbox.css")
        );
        wp_enqueue_script(
            "jurible-lightbox",
            get_template_directory_uri() . "/assets/js/lightbox.js",
            [],
            filemtime(get_template_directory() . "/assets/js/lightbox.js"),
            true
        );
    }
}
add_action("wp_enqueue_scripts", "jurible_enqueue_lightbox");


# Enregistrer les Block Styles pour core/paragraph (Tags)
function jurible_register_tag_block_styles()
{
    register_block_style("core/paragraph", [
        "name"  => "tag-primary",
        "label" => __("Tag Primary", "jurible"),
    ]);

    register_block_style("core/paragraph", [
        "name"  => "tag-secondary",
        "label" => __("Tag Secondary", "jurible"),
    ]);

    register_block_style("core/paragraph", [
        "name"  => "tag-gray",
        "label" => __("Tag Gray", "jurible"),
    ]);

    register_block_style("core/paragraph", [
        "name"  => "tag-success",
        "label" => __("Tag Success", "jurible"),
    ]);

    register_block_style("core/paragraph", [
        "name"  => "tag-warning",
        "label" => __("Tag Warning", "jurible"),
    ]);

    register_block_style("core/paragraph", [
        "name"  => "tag-error",
        "label" => __("Tag Error", "jurible"),
    ]);

    register_block_style("core/paragraph", [
        "name"  => "tag-info",
        "label" => __("Tag Info", "jurible"),
    ]);

    register_block_style("core/paragraph", [
        "name"  => "tag-dark",
        "label" => __("Tag Dark", "jurible"),
    ]);
}
add_action("init", "jurible_register_tag_block_styles");


# Charger le CSS tags
function jurible_enqueue_tags_styles()
{
    wp_enqueue_style(
        "jurible-tags",
        get_template_directory_uri() . "/assets/css/tags.css",
        [],
        filemtime(get_template_directory() . "/assets/css/tags.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_tags_styles");
add_action("enqueue_block_assets", "jurible_enqueue_tags_styles");


# Charger le CSS pagination
function jurible_enqueue_pagination_styles()
{
    wp_enqueue_style(
        "jurible-pagination",
        get_template_directory_uri() . "/assets/css/pagination.css",
        [],
        filemtime(get_template_directory() . "/assets/css/pagination.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_pagination_styles");


# Charger le CSS inputs (formulaires)
function jurible_enqueue_inputs_styles()
{
    wp_enqueue_style(
        "jurible-inputs",
        get_template_directory_uri() . "/assets/css/inputs.css",
        [],
        filemtime(get_template_directory() . "/assets/css/inputs.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_inputs_styles");
add_action("enqueue_block_assets", "jurible_enqueue_inputs_styles");

# Charger le CSS pour SureCart (boutons, galerie produit)
function jurible_enqueue_surecart_styles()
{
    wp_enqueue_style(
        "jurible-surecart",
        get_template_directory_uri() . "/assets/css/surecart.css",
        [],
        wp_get_theme()->get("Version")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_surecart_styles");
add_action("enqueue_block_assets", "jurible_enqueue_surecart_styles");


# Enregistrer les Block Styles pour core/separator (Dividers)
function jurible_register_separator_block_styles()
{
    register_block_style("core/separator", [
        "name"  => "gradient",
        "label" => __("Gradient", "jurible"),
    ]);
}
add_action("init", "jurible_register_separator_block_styles");


# Enregistrer les emplacements de menu
function jurible_register_menus()
{
    register_nav_menus([
        "header" => __("Menu Header", "jurible"),
    ]);
}
add_action("after_setup_theme", "jurible_register_menus");


# Charger le CSS et JS du header (mega menu, mobile menu, glassmorphism)
function jurible_enqueue_header_assets()
{
    wp_enqueue_style(
        "jurible-header",
        get_template_directory_uri() . "/assets/css/header.css",
        [],
        filemtime(get_template_directory() . "/assets/css/header.css")
    );

    wp_enqueue_script(
        "jurible-header",
        get_template_directory_uri() . "/assets/js/header.js",
        [],
        filemtime(get_template_directory() . "/assets/js/header.js"),
        true
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_header_assets");


# Charger le CSS du footer
function jurible_enqueue_footer_assets()
{
    wp_enqueue_style(
        "jurible-footer",
        get_template_directory_uri() . "/assets/css/footer.css",
        [],
        filemtime(get_template_directory() . "/assets/css/footer.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_footer_assets");


# CSS Footer Minimal
function jurible_enqueue_footer_minimal_assets()
{
    wp_enqueue_style(
        "jurible-footer-minimal",
        get_template_directory_uri() . "/assets/css/footer-minimal.css",
        [],
        filemtime(get_template_directory() . "/assets/css/footer-minimal.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_footer_minimal_assets");


# CSS Header Minimal
function jurible_enqueue_header_minimal_assets()
{
    wp_enqueue_style(
        "jurible-header-minimal",
        get_template_directory_uri() . "/assets/css/header-minimal.css",
        [],
        filemtime(get_template_directory() . "/assets/css/header-minimal.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_header_minimal_assets");


# Shortcode Header Minimal (avec panier)
function jurible_header_minimal_shortcode()
{
    ob_start();
    ?>
    <header id="site-header" class="site-header site-header--minimal">
        <div class="site-header__inner">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="site-header__logo">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logos/logo-color.svg'); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="site-header__logo-img">
            </a>
            <div class="site-header__actions">
                <a href="/boutique" class="header-minimal__back">
                    <span class="header-minimal__back-arrow">←</span>
                    <span class="header-minimal__back-text">Boutique</span>
                </a>
                <?php echo do_blocks('<!-- wp:surecart/cart-menu-icon --><div class="wp-block-surecart-cart-menu-icon"><!-- wp:surecart/cart-icon /--></div><!-- /wp:surecart/cart-menu-icon -->'); ?>
            </div>
        </div>
    </header>
    <?php
    return ob_get_clean();
}
add_shortcode('jurible_header_minimal', 'jurible_header_minimal_shortcode');


# Shortcode Header Minimal Checkout (paiement sécurisé)
function jurible_header_minimal_checkout_shortcode()
{
    ob_start();
    ?>
    <header class="site-header site-header--minimal site-header--checkout">
        <div class="site-header__inner">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="site-header__logo">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logos/logo-color.svg'); ?>" alt="Jurible" class="site-header__logo-img" width="120" height="32">
            </a>
            <span class="header-minimal__secure">
                🔒 Paiement sécurisé
            </span>
        </div>
    </header>
    <?php
    return ob_get_clean();
}
add_shortcode('jurible_header_minimal_checkout', 'jurible_header_minimal_checkout_shortcode');


# Shortcode pour afficher le header avec navigation
function jurible_header_shortcode()
{
    remove_filter('the_content', 'wpautop');
    ob_start();
    get_template_part('template-parts/header-nav');
    $output = ob_get_clean();
    add_filter('the_content', 'wpautop');
    return $output;
}
add_shortcode('jurible_header', 'jurible_header_shortcode');


# ==========================================================================
# STICKY BAR - Customizer Settings
# ==========================================================================

function jurible_sticky_bar_customizer($wp_customize)
{
    // Section Sticky Bar
    $wp_customize->add_section('jurible_sticky_bar', [
        'title'    => __('Sticky Bar', 'jurible'),
        'priority' => 30,
    ]);

    // Toggle activer/désactiver
    $wp_customize->add_setting('sticky_bar_enabled', [
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ]);
    $wp_customize->add_control('sticky_bar_enabled', [
        'label'   => __('Activer la Sticky Bar', 'jurible'),
        'section' => 'jurible_sticky_bar',
        'type'    => 'checkbox',
    ]);

    // Variante
    $wp_customize->add_setting('sticky_bar_variant', [
        'default'           => 'gradient',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('sticky_bar_variant', [
        'label'   => __('Variante', 'jurible'),
        'section' => 'jurible_sticky_bar',
        'type'    => 'select',
        'choices' => [
            'gradient' => __('Gradient (Primary)', 'jurible'),
            'white'    => __('Blanc', 'jurible'),
            'dark'     => __('Noir', 'jurible'),
        ],
    ]);

    // Texte
    $wp_customize->add_setting('sticky_bar_text', [
        'default'           => '🎓 Profite de -20% sur l\'Académie avec le code JURIBLE20',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('sticky_bar_text', [
        'label'   => __('Texte', 'jurible'),
        'section' => 'jurible_sticky_bar',
        'type'    => 'text',
    ]);

    // Texte du bouton
    $wp_customize->add_setting('sticky_bar_button_text', [
        'default'           => 'J\'en profite →',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('sticky_bar_button_text', [
        'label'   => __('Texte du bouton', 'jurible'),
        'section' => 'jurible_sticky_bar',
        'type'    => 'text',
    ]);

    // URL du bouton
    $wp_customize->add_setting('sticky_bar_button_url', [
        'default'           => '/tarifs',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control('sticky_bar_button_url', [
        'label'   => __('URL du bouton', 'jurible'),
        'section' => 'jurible_sticky_bar',
        'type'    => 'url',
    ]);

    // Option pour permettre de fermer
    $wp_customize->add_setting('sticky_bar_dismissible', [
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ]);
    $wp_customize->add_control('sticky_bar_dismissible', [
        'label'   => __('Permettre de fermer (croix)', 'jurible'),
        'section' => 'jurible_sticky_bar',
        'type'    => 'checkbox',
    ]);
}
add_action('customize_register', 'jurible_sticky_bar_customizer');


# Helper pour récupérer les options de la sticky bar
function jurible_get_sticky_bar_options()
{
    return [
        'enabled'     => get_theme_mod('sticky_bar_enabled', false),
        'variant'     => get_theme_mod('sticky_bar_variant', 'gradient'),
        'text'        => get_theme_mod('sticky_bar_text', '🎓 Profite de -20% sur l\'Académie avec le code JURIBLE20'),
        'button_text' => get_theme_mod('sticky_bar_button_text', 'J\'en profite →'),
        'button_url'  => get_theme_mod('sticky_bar_button_url', '/tarifs'),
        'dismissible' => get_theme_mod('sticky_bar_dismissible', true),
    ];
}


# Charger le CSS et JS de la sticky bar
function jurible_enqueue_sticky_bar_assets()
{
    // Toujours charger le CSS (pour les transitions)
    wp_enqueue_style(
        "jurible-sticky-bar",
        get_template_directory_uri() . "/assets/css/sticky-bar.css",
        [],
        filemtime(get_template_directory() . "/assets/css/sticky-bar.css")
    );

    // Charger le JS seulement si la sticky bar est active et dismissible
    $options = jurible_get_sticky_bar_options();
    if ($options['enabled'] && $options['dismissible']) {
        wp_enqueue_script(
            "jurible-sticky-bar",
            get_template_directory_uri() . "/assets/js/sticky-bar.js",
            [],
            filemtime(get_template_directory() . "/assets/js/sticky-bar.js"),
            true
        );
    }
}
add_action("wp_enqueue_scripts", "jurible_enqueue_sticky_bar_assets");


# Charger le CSS des patterns enseignants (frontend + éditeur)
function jurible_enqueue_enseignants_assets()
{
    wp_enqueue_style(
        "jurible-enseignants",
        get_template_directory_uri() . "/assets/css/equipe-enseignants.css",
        [],
        filemtime(get_template_directory() . "/assets/css/equipe-enseignants.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_enseignants_assets");
add_action("enqueue_block_assets", "jurible_enqueue_enseignants_assets");


# Charger le CSS des patterns réassurance (frontend + éditeur)
function jurible_enqueue_reassurance_assets()
{
    wp_enqueue_style(
        "jurible-reassurance",
        get_template_directory_uri() . "/assets/css/confiance-reassurance.css",
        [],
        filemtime(get_template_directory() . "/assets/css/confiance-reassurance.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_reassurance_assets");
add_action("enqueue_block_assets", "jurible_enqueue_reassurance_assets");


# Charger le CSS du pattern FAQ (frontend + éditeur)
function jurible_enqueue_faq_assets()
{
    wp_enqueue_style(
        "jurible-faq",
        get_template_directory_uri() . "/assets/css/confiance-faq.css",
        [],
        filemtime(get_template_directory() . "/assets/css/confiance-faq.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_faq_assets");
add_action("enqueue_block_assets", "jurible_enqueue_faq_assets");


# Charger le CSS du pattern Fonctionnalités (frontend + éditeur)
function jurible_enqueue_fonctionnalites_assets()
{
    wp_enqueue_style(
        "jurible-fonctionnalites",
        get_template_directory_uri() . "/assets/css/marketing-features.css",
        [],
        filemtime(get_template_directory() . "/assets/css/marketing-features.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_fonctionnalites_assets");
add_action("enqueue_block_assets", "jurible_enqueue_fonctionnalites_assets");


# Charger le CSS du pattern Contenu (frontend + éditeur)
function jurible_enqueue_contenu_assets()
{
    wp_enqueue_style(
        "jurible-contenu",
        get_template_directory_uri() . "/assets/css/contenu-chiffres.css",
        [],
        filemtime(get_template_directory() . "/assets/css/contenu-chiffres.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_contenu_assets");
add_action("enqueue_block_assets", "jurible_enqueue_contenu_assets");

# Charger le CSS du pattern Pain Points (frontend + éditeur)
function jurible_enqueue_pain_points_assets()
{
    wp_enqueue_style(
        "jurible-pain-points",
        get_template_directory_uri() . "/assets/css/marketing-pain-points.css",
        [],
        filemtime(get_template_directory() . "/assets/css/marketing-pain-points.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_pain_points_assets");
add_action("enqueue_block_assets", "jurible_enqueue_pain_points_assets");

# Charger le CSS des Hero Conversion (frontend + éditeur)
function jurible_enqueue_hero_conversion_assets()
{
    wp_enqueue_style(
        "jurible-hero-conversion",
        get_template_directory_uri() . "/assets/css/hero-conversion.css",
        [],
        filemtime(get_template_directory() . "/assets/css/hero-conversion.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_hero_conversion_assets");
add_action("enqueue_block_assets", "jurible_enqueue_hero_conversion_assets");

# Charger le CSS des Hero Archive (frontend + éditeur)
function jurible_enqueue_hero_archive_assets()
{
    wp_enqueue_style(
        "jurible-hero-archive",
        get_template_directory_uri() . "/assets/css/hero-archive.css",
        [],
        filemtime(get_template_directory() . "/assets/css/hero-archive.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_hero_archive_assets");
add_action("enqueue_block_assets", "jurible_enqueue_hero_archive_assets");

# Charger le CSS des Hero Produit (frontend + éditeur)
function jurible_enqueue_hero_produit_assets()
{
    wp_enqueue_style(
        "jurible-hero-produit",
        get_template_directory_uri() . "/assets/css/hero-produit.css",
        [],
        filemtime(get_template_directory() . "/assets/css/hero-produit.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_hero_produit_assets");
add_action("enqueue_block_assets", "jurible_enqueue_hero_produit_assets");

# Charger le CSS des Hero Simple (frontend + éditeur)
function jurible_enqueue_hero_simple_assets()
{
    wp_enqueue_style(
        "jurible-hero-simple",
        get_template_directory_uri() . "/assets/css/hero-simple.css",
        [],
        filemtime(get_template_directory() . "/assets/css/hero-simple.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_hero_simple_assets");
add_action("enqueue_block_assets", "jurible_enqueue_hero_simple_assets");

# Charger le CSS des Hero Article (frontend + éditeur)
function jurible_enqueue_hero_article_assets()
{
    wp_enqueue_style(
        "jurible-hero-article",
        get_template_directory_uri() . "/assets/css/hero-article.css",
        [],
        filemtime(get_template_directory() . "/assets/css/hero-article.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_hero_article_assets");
add_action("enqueue_block_assets", "jurible_enqueue_hero_article_assets");

# Charger le CSS du CTA Final (frontend + éditeur)
function jurible_enqueue_cta_final_assets()
{
    wp_enqueue_style(
        "jurible-cta-final",
        get_template_directory_uri() . "/assets/css/commerce-cta-final.css",
        [],
        filemtime(get_template_directory() . "/assets/css/commerce-cta-final.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_cta_final_assets");
add_action("enqueue_block_assets", "jurible_enqueue_cta_final_assets");

# Charger le CSS du Trust Bar (frontend + éditeur)
function jurible_enqueue_trust_bar_assets()
{
    wp_enqueue_style(
        "jurible-trust-bar",
        get_template_directory_uri() . "/assets/css/confiance-logos.css",
        [],
        filemtime(get_template_directory() . "/assets/css/confiance-logos.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_trust_bar_assets");
add_action("enqueue_block_assets", "jurible_enqueue_trust_bar_assets");

# Charger le CSS des Paragraphes (frontend + éditeur)
function jurible_enqueue_paragraphe_assets()
{
    wp_enqueue_style(
        "jurible-paragraphe",
        get_template_directory_uri() . "/assets/css/contenu-paragraphe.css",
        [],
        filemtime(get_template_directory() . "/assets/css/contenu-paragraphe.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_paragraphe_assets");
add_action("enqueue_block_assets", "jurible_enqueue_paragraphe_assets");

# Charger le CSS du Bloc Texte + Média (frontend + éditeur)
function jurible_enqueue_bloc_texte_media_assets()
{
    wp_enqueue_style(
        "jurible-bloc-texte-media",
        get_template_directory_uri() . "/assets/css/contenu-texte-media.css",
        [],
        filemtime(get_template_directory() . "/assets/css/contenu-texte-media.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_bloc_texte_media_assets");
add_action("enqueue_block_assets", "jurible_enqueue_bloc_texte_media_assets");

# Charger le CSS et JS de Quelle Offre Choisir (frontend + éditeur)
function jurible_enqueue_quelle_offre_assets()
{
    wp_enqueue_style(
        "jurible-quelle-offre",
        get_template_directory_uri() . "/assets/css/commerce-quelle-offre.css",
        [],
        filemtime(get_template_directory() . "/assets/css/commerce-quelle-offre.css")
    );

    wp_enqueue_script(
        "jurible-quelle-offre",
        get_template_directory_uri() . "/assets/js/commerce-quelle-offre.js",
        [],
        filemtime(get_template_directory() . "/assets/js/commerce-quelle-offre.js"),
        true
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_quelle_offre_assets");
add_action("enqueue_block_assets", "jurible_enqueue_quelle_offre_assets");

# Charger le CSS de Produits Complémentaires (frontend + éditeur)
function jurible_enqueue_produits_complementaires_assets()
{
    wp_enqueue_style(
        "jurible-produits-complementaires",
        get_template_directory_uri() . "/assets/css/commerce-produits.css",
        [],
        filemtime(get_template_directory() . "/assets/css/commerce-produits.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_produits_complementaires_assets");
add_action("enqueue_block_assets", "jurible_enqueue_produits_complementaires_assets");

# Charger le CSS de Pricing Académie (frontend + éditeur)
function jurible_enqueue_pricing_academie_assets()
{
    wp_enqueue_style(
        "jurible-pricing-academie",
        get_template_directory_uri() . "/assets/css/commerce-pricing.css",
        [],
        filemtime(get_template_directory() . "/assets/css/commerce-pricing.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_pricing_academie_assets");
add_action("enqueue_block_assets", "jurible_enqueue_pricing_academie_assets");

# Charger le CSS de Comparaison (frontend + éditeur)
function jurible_enqueue_comparaison_assets()
{
    wp_enqueue_style(
        "jurible-comparaison",
        get_template_directory_uri() . "/assets/css/marketing-comparaison.css",
        [],
        filemtime(get_template_directory() . "/assets/css/marketing-comparaison.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_comparaison_assets");
add_action("enqueue_block_assets", "jurible_enqueue_comparaison_assets");

# Charger le CSS de Features Grid (frontend + éditeur)
function jurible_enqueue_features_assets()
{
    wp_enqueue_style(
        "jurible-features",
        get_template_directory_uri() . "/assets/css/marketing-features-incluses.css",
        [],
        filemtime(get_template_directory() . "/assets/css/marketing-features-incluses.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_features_assets");
add_action("enqueue_block_assets", "jurible_enqueue_features_assets");

# Charger le CSS de Stats Section (frontend + éditeur)
function jurible_enqueue_stats_assets()
{
    wp_enqueue_style(
        "jurible-stats",
        get_template_directory_uri() . "/assets/css/contenu-stats.css",
        [],
        filemtime(get_template_directory() . "/assets/css/contenu-stats.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_stats_assets");
add_action("enqueue_block_assets", "jurible_enqueue_stats_assets");

// P14 - Steps assets
function jurible_enqueue_steps_assets()
{
    wp_enqueue_style(
        "jurible-steps",
        get_template_directory_uri() . "/assets/css/marketing-steps.css",
        [],
        filemtime(get_template_directory() . "/assets/css/marketing-steps.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_steps_assets");
add_action("enqueue_block_assets", "jurible_enqueue_steps_assets");

// P15 - Solution assets
function jurible_enqueue_solution_assets()
{
    wp_enqueue_style(
        "jurible-solution",
        get_template_directory_uri() . "/assets/css/marketing-solution.css",
        [],
        filemtime(get_template_directory() . "/assets/css/marketing-solution.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_solution_assets");
add_action("enqueue_block_assets", "jurible_enqueue_solution_assets");

// P16 - Article Featured assets
function jurible_enqueue_article_featured_assets()
{
    wp_enqueue_style(
        "jurible-article-featured",
        get_template_directory_uri() . "/assets/css/structure-article-featured.css",
        [],
        filemtime(get_template_directory() . "/assets/css/structure-article-featured.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_article_featured_assets");
add_action("enqueue_block_assets", "jurible_enqueue_article_featured_assets");

// P17 - Articles Grid assets
function jurible_enqueue_articles_grid_assets()
{
    wp_enqueue_style(
        "jurible-articles-grid",
        get_template_directory_uri() . "/assets/css/structure-articles-grid.css",
        [],
        filemtime(get_template_directory() . "/assets/css/structure-articles-grid.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_articles_grid_assets");
add_action("enqueue_block_assets", "jurible_enqueue_articles_grid_assets");

// P18 - Articles Lies assets
function jurible_enqueue_articles_lies_assets()
{
    wp_enqueue_style(
        "jurible-articles-lies",
        get_template_directory_uri() . "/assets/css/structure-articles-lies.css",
        [],
        filemtime(get_template_directory() . "/assets/css/structure-articles-lies.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_articles_lies_assets");
add_action("enqueue_block_assets", "jurible_enqueue_articles_lies_assets");

// P19 - Page 404 assets
function jurible_enqueue_page_404_assets()
{
    wp_enqueue_style(
        "jurible-page-404",
        get_template_directory_uri() . "/assets/css/structure-page-404.css",
        [],
        filemtime(get_template_directory() . "/assets/css/structure-page-404.css")
    );
}
add_action("wp_enqueue_scripts", "jurible_enqueue_page_404_assets");
add_action("enqueue_block_assets", "jurible_enqueue_page_404_assets");


# Autoriser le HTML dans les descriptions de catégories/taxonomies
remove_filter('pre_term_description', 'wp_filter_kses');
remove_filter('term_description', 'wp_kses_data');
add_filter('term_description', 'wpautop');


# Limiter la recherche aux articles uniquement (exclure les pages)
function jurible_search_filter($query)
{
    if ($query->is_search && !is_admin() && $query->is_main_query()) {
        $query->set('post_type', 'post');
    }
    return $query;
}
add_filter('pre_get_posts', 'jurible_search_filter');


# Charger le CSS du template single (articles)
function jurible_enqueue_single_assets()
{
    if (is_single()) {
        wp_enqueue_style(
            "jurible-template-single",
            get_template_directory_uri() . "/assets/css/template-single.css",
            [],
            filemtime(get_template_directory() . "/assets/css/template-single.css")
        );
        wp_enqueue_style(
            "jurible-comments",
            get_template_directory_uri() . "/assets/css/comments.css",
            [],
            filemtime(get_template_directory() . "/assets/css/comments.css")
        );
    }
}
add_action("wp_enqueue_scripts", "jurible_enqueue_single_assets");


# Charger le CSS typographique des articles de blog (uniquement sur single posts)
function jurible_enqueue_single_post_typography()
{
    if (is_singular('post')) {
        wp_enqueue_style(
            "jurible-single-post-typography",
            get_template_directory_uri() . "/assets/css/single-post-typography.css",
            [],
            filemtime(get_template_directory() . "/assets/css/single-post-typography.css")
        );
    }
}
add_action("wp_enqueue_scripts", "jurible_enqueue_single_post_typography");


# ==========================================================================
# ACF SHORTCODES POUR TEMPLATES FSE
# ==========================================================================

/**
 * Shortcode générique pour afficher un champ ACF
 * Usage: [acf field="matiere_name"] ou [acf field="rating_score" default="4.5"]
 */
function jurible_acf_shortcode($atts)
{
    $atts = shortcode_atts([
        'field'   => '',
        'post_id' => get_the_ID(),
        'default' => '',
    ], $atts);

    if (empty($atts['field'])) {
        return $atts['default'];
    }

    // Vérifier si ACF est actif
    if (!function_exists('get_field')) {
        return $atts['default'];
    }

    $value = get_field($atts['field'], $atts['post_id']);

    if (empty($value) && $value !== 0 && $value !== '0') {
        return esc_html($atts['default']);
    }

    // Si c'est une image, retourner l'URL
    if (is_array($value) && isset($value['url'])) {
        return esc_url($value['url']);
    }

    return esc_html($value);
}
add_shortcode('acf', 'jurible_acf_shortcode');


/**
 * Filtre pour exécuter do_shortcode() sur les templates FSE
 * Nécessaire car les templates .html n'appliquent pas automatiquement les shortcodes
 */
function jurible_render_block_shortcodes($block_content, $block)
{
    // Exécuter les shortcodes dans tous les blocs
    if (strpos($block_content, '[acf') !== false ||
        strpos($block_content, '[course_') !== false) {
        $block_content = do_shortcode($block_content);
    }
    return $block_content;
}
add_filter('render_block', 'jurible_render_block_shortcodes', 10, 2);


# ==========================================================================
# CUSTOM POST TYPE : COURS
# ==========================================================================

/**
 * Enregistrer le CPT "course" pour les pages de vente des cours de l'Académie
 */
function jurible_register_course_cpt()
{
    register_post_type('course', [
        'labels' => [
            'name'               => __('Cours', 'jurible'),
            'singular_name'      => __('Cours', 'jurible'),
            'add_new'            => __('Ajouter un cours', 'jurible'),
            'add_new_item'       => __('Ajouter un nouveau cours', 'jurible'),
            'edit_item'          => __('Modifier le cours', 'jurible'),
            'new_item'           => __('Nouveau cours', 'jurible'),
            'view_item'          => __('Voir le cours', 'jurible'),
            'search_items'       => __('Rechercher des cours', 'jurible'),
            'not_found'          => __('Aucun cours trouvé', 'jurible'),
            'not_found_in_trash' => __('Aucun cours dans la corbeille', 'jurible'),
            'all_items'          => __('Tous les cours', 'jurible'),
            'menu_name'          => __('Cours', 'jurible'),
        ],
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true, // Gutenberg support
        'rest_base'           => 'cours', // Endpoint API : /wp/v2/cours
        'query_var'           => true,
        'rewrite'             => ['slug' => 'cours', 'with_front' => false],
        'capability_type'     => 'post',
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-welcome-learn-more',
        'supports'            => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
    ]);
}
add_action('init', 'jurible_register_course_cpt');


/**
 * Charger la lightbox sur les pages cours (comme pour les produits)
 */
function jurible_enqueue_course_lightbox()
{
    if (is_singular('course')) {
        wp_enqueue_style(
            "jurible-lightbox",
            get_template_directory_uri() . "/assets/css/lightbox.css",
            [],
            filemtime(get_template_directory() . "/assets/css/lightbox.css")
        );
        wp_enqueue_script(
            "jurible-lightbox",
            get_template_directory_uri() . "/assets/js/lightbox.js",
            [],
            filemtime(get_template_directory() . "/assets/js/lightbox.js"),
            true
        );
    }
}
add_action("wp_enqueue_scripts", "jurible_enqueue_course_lightbox");

/**
 * Charger le CSS du sommaire cours (pages cours)
 */
function jurible_enqueue_sommaire_cours_assets()
{
    if (is_singular('course')) {
        wp_enqueue_style(
            "jurible-sommaire-cours",
            get_template_directory_uri() . "/assets/css/sommaire-cours.css",
            [],
            filemtime(get_template_directory() . "/assets/css/sommaire-cours.css")
        );
    }
}
add_action("wp_enqueue_scripts", "jurible_enqueue_sommaire_cours_assets");


# ==========================================================================
# ACF FIELDS : COURS (enregistrement programmatique)
# ==========================================================================

/**
 * Enregistrer les champs ACF pour le CPT "course"
 * Ces champs sont utilisés dans le template single-course.html
 */
function jurible_register_course_acf_fields()
{
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_course_fields',
        'title' => 'Cours - Champs dynamiques',
        'fields' => [
            // === SECTION : HERO ===
            [
                'key' => 'field_course_tab_hero',
                'label' => 'Hero',
                'type' => 'tab',
            ],
            [
                'key' => 'field_course_matiere_name',
                'label' => 'Nom de la matière',
                'name' => 'matiere_name',
                'type' => 'text',
                'required' => 1,
                'placeholder' => 'Ex: droit des obligations',
                'instructions' => 'Utilisé pour générer automatiquement : Badge "Cours complet de [matière]" et Titre "Maîtrisez le [matière]"',
                'show_in_rest' => true,
            ],
            [
                'key' => 'field_course_sous_titre',
                'label' => 'Sous-titre / Description courte',
                'name' => 'sous_titre',
                'type' => 'textarea',
                'rows' => 2,
                'placeholder' => 'Ex: Maîtrisez tous les concepts essentiels avec nos vidéos, fiches et exercices.',
                'show_in_rest' => true,
            ],
            [
                'key' => 'field_course_video_url',
                'label' => 'URL Vidéo (embed)',
                'name' => 'video_url',
                'type' => 'url',
                'placeholder' => 'https://www.youtube.com/watch?v=...',
                'instructions' => 'URL YouTube ou Vimeo pour l\'embed dans la card hero',
                'show_in_rest' => true,
            ],

            // === SECTION : AUTEUR ===
            [
                'key' => 'field_course_tab_auteur',
                'label' => 'Auteur',
                'type' => 'tab',
            ],
            [
                'key' => 'field_course_texte_auteur',
                'label' => 'Texte auteur',
                'name' => 'texte_auteur',
                'type' => 'textarea',
                'rows' => 4,
                'placeholder' => 'Parmi eux, Raphaël est l\'auteur de ce cours. Ancien avocat inscrit au barreau et titulaire du Master 2...',
                'instructions' => 'Texte de présentation de l\'auteur du cours',
                'show_in_rest' => true,
            ],

            // === SECTION : PROGRAMME ===
            [
                'key' => 'field_course_tab_programme',
                'label' => 'Programme',
                'type' => 'tab',
            ],
            [
                'key' => 'field_course_texte_section_programme',
                'label' => 'Texte section programme',
                'name' => 'texte_section_programme',
                'type' => 'textarea',
                'rows' => 4,
                'default_value' => 'Ce cours couvre l\'intégralité du programme universitaire. Chaque chapitre est accompagné de vidéos explicatives, de fiches de synthèse et d\'exercices corrigés.',
                'show_in_rest' => true,
            ],
            [
                'key' => 'field_course_sommaire_cours',
                'label' => 'Sommaire du cours',
                'name' => 'sommaire_cours',
                'type' => 'textarea',
                'rows' => 15,
                'instructions' => 'Format: ## Titre du thème (sur une ligne) puis - Cours n°X - Titre (un par ligne). Exemple:
## Thème 1 — Le cadre du pouvoir politique
- Cours n°1 - Les composantes de l\'État
- Cours n°2 - Les formes de l\'État

## Thème 2 — La source du pouvoir politique
- Cours n°3 - La notion de souveraineté',
                'placeholder' => '## Thème 1 — Le cadre du pouvoir politique
- Cours n°1 - Les composantes de l\'État
- Cours n°2 - Les formes de l\'État : L\'État unitaire
- Cours n°3 - Les formes de l\'État : L\'État fédéral

## Thème 2 — La source du pouvoir politique
- Cours n°4 - La notion de souveraineté
- Cours n°5 - Les modes d\'exercice du pouvoir',
                'show_in_rest' => true,
            ],
            // === SECTION : SOLUTION ===
            [
                'key' => 'field_course_tab_solution',
                'label' => 'Solution',
                'type' => 'tab',
            ],
            [
                'key' => 'field_course_solution_sous_titre',
                'label' => 'Sous-titre section Solution',
                'name' => 'solution_sous_titre',
                'type' => 'textarea',
                'rows' => 2,
                'default_value' => 'Tout ce dont vous avez besoin pour comprendre et réussir vos examens.',
                'placeholder' => 'Ex: Tout ce dont vous avez besoin pour comprendre les institutions de la Vème République...',
                'show_in_rest' => true,
            ],

        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'course',
                ],
            ],
        ],
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'active' => true,
    ]);
}
add_action('acf/init', 'jurible_register_course_acf_fields');

/**
 * Exposer les champs ACF dans l'API REST pour le CPT course
 */
function jurible_add_acf_to_rest_api() {
    register_rest_field('course', 'acf', [
        'get_callback' => function($post) {
            return [
                'matiere_name' => get_field('matiere_name', $post['id']),
                'sous_titre' => get_field('sous_titre', $post['id']),
                'video_url' => get_field('video_url', $post['id']),
                'texte_auteur' => get_field('texte_auteur', $post['id']),
                'texte_section_programme' => get_field('texte_section_programme', $post['id']),
                'sommaire_cours' => get_field('sommaire_cours', $post['id']),
                'solution_sous_titre' => get_field('solution_sous_titre', $post['id']),
            ];
        },
        'update_callback' => function($values, $post) {
            if (!is_array($values)) return;

            foreach ($values as $key => $value) {
                update_field($key, $value, $post->ID);
            }
        },
        'schema' => [
            'type' => 'object',
            'context' => ['view', 'edit'],
        ],
    ]);
}
add_action('rest_api_init', 'jurible_add_acf_to_rest_api');

/**
 * Exposer les métadonnées Rank Math via l'API REST pour le CPT course
 */
function jurible_register_rankmath_meta() {
    register_meta('post', 'rank_math_title', [
        'object_subtype' => 'course',
        'show_in_rest'   => true,
        'single'         => true,
        'type'           => 'string',
        'auth_callback'  => '__return_true',
    ]);

    register_meta('post', 'rank_math_description', [
        'object_subtype' => 'course',
        'show_in_rest'   => true,
        'single'         => true,
        'type'           => 'string',
        'auth_callback'  => '__return_true',
    ]);
}
add_action('init', 'jurible_register_rankmath_meta');

/**
 * Activer Rank Math SEO sur le CPT course
 */
function jurible_rankmath_post_types($post_types) {
    $post_types[] = 'course';
    return array_unique($post_types);
}
add_filter('rank_math/sitemap/post_type', 'jurible_rankmath_post_types');
add_filter('rank_math/metabox/post_types', 'jurible_rankmath_post_types');

/**
 * TEMPORAIRE - Créer un cours de test
 * Visiter : /wp-admin/?create_test_course=1
 * À SUPPRIMER après les tests
 */
function jurible_create_test_course() {
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    if (!isset($_GET['create_test_course']) || $_GET['create_test_course'] !== '1') {
        return;
    }

    // Vérifier si le cours de test existe déjà
    $existing = get_posts([
        'post_type' => 'course',
        'post_status' => 'any',
        'meta_key' => '_is_test_course',
        'meta_value' => '1',
        'numberposts' => 1,
    ]);

    if (!empty($existing)) {
        wp_redirect(get_edit_post_link($existing[0]->ID, 'redirect'));
        exit;
    }

    // Créer le cours de test
    $course_id = wp_insert_post([
        'post_title' => 'Droit des obligations - TEST',
        'post_type' => 'course',
        'post_status' => 'draft',
        'post_content' => '',
    ]);

    if (is_wp_error($course_id)) {
        wp_die('Erreur lors de la création du cours de test');
    }

    // Marquer comme cours de test
    update_post_meta($course_id, '_is_test_course', '1');

    // Remplir tous les champs ACF
    $acf_data = [
        // Hero (simplifié : badge et titre générés automatiquement depuis matiere_name)
        'matiere_name' => 'droit des obligations',
        'sous_titre' => 'Maîtrisez les fondamentaux du droit des contrats et de la responsabilité civile avec notre formation complète en vidéo.',
        'video_url' => 'https://www.youtube.com/embed/VRlP_VoONN8',

        // Auteur
        'texte_auteur' => 'Parmi eux, Raphaël est l\'auteur de ce cours. Ancien avocat inscrit au barreau et titulaire du Master 2 Droit et pratiques des relations de travail de l\'université Panthéon-Assas Paris II, il a également été chargé d\'enseignement à l\'université de Nice.',

        // Programme
        'texte_section_programme' => 'Ce cours couvre l\'intégralité du programme de droit des obligations de L2 : formation du contrat, validité, effets, inexécution, responsabilité contractuelle et délictuelle.',
        'sommaire_cours' => '## Thème 1 — La formation du contrat
- Cours n°1 - Les conditions de validité du contrat
- Cours n°2 - Le consentement
- Cours n°3 - La capacité et le pouvoir

## Thème 2 — Le contenu du contrat
- Cours n°4 - Les clauses du contrat
- Cours n°5 - L\'interprétation du contrat

## Thème 3 — Les effets du contrat
- Cours n°6 - La force obligatoire du contrat
- Cours n°7 - L\'effet relatif du contrat
- Cours n°8 - L\'inexécution du contrat',

        // Solution
        'solution_sous_titre' => 'AideAuxTD vous accompagne avec une méthode claire et des contenus pensés pour votre réussite.',
    ];

    foreach ($acf_data as $field_name => $value) {
        update_field($field_name, $value, $course_id);
    }

    // Rediriger vers l'édition du cours
    wp_redirect(admin_url('post.php?post=' . $course_id . '&action=edit'));
    exit;
}
add_action('admin_init', 'jurible_create_test_course');

