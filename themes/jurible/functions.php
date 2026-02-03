<?php

# Bases du thème
# Dans https://capitainewp.io/formations/wordpress-full-site-editing/base-theme-fse/#le-fichier-functions-php

# Retirer les accents des noms de fichiers
add_filter("sanitize_file_name", "remove_accents");

# Retirer le pattern directory et la suggestion de blocs
remove_action("enqueue_block_editor_assets", "wp_enqueue_editor_block_directory_assets");
remove_theme_support("core-block-patterns");

# Ajouter des fonctionnalités
add_theme_support("editor-styles");
add_editor_style("style-editor.css");


# Déclarer les scripts et les styles
function jurible_register_assets()
{
    # Intégrer des feuilles de style sur le site
    wp_enqueue_style("main", get_stylesheet_uri(), [], wp_get_theme()->get('Version'));

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
add_action("enqueue_block_editor_assets", "jurible_deregister_blocks_variations");


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
        "jurible-components",
        ["label" => __("Composants Jurible", "jurible")]
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
add_action("enqueue_block_editor_assets", "jurible_enqueue_media_styles");
