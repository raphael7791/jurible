<?php
/**
 * Breadcrumb Block - Dynamic Render
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

$home_icon_on_mobile = $attributes['homeIconOnMobile'] ?? true;
$show_current_page = $attributes['showCurrentPage'] ?? true;
$show_schema = $attributes['showSchema'] ?? true;

$home_icon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>';

// Build breadcrumb items
$items = [];
$position = 1;

// Home
$items[] = [
    'name' => 'Accueil',
    'url' => home_url('/'),
    'is_home' => true,
];

// Get blog page URL
$blog_page_id = get_option('page_for_posts');
$blog_url = $blog_page_id ? get_permalink($blog_page_id) : home_url('/blog/');

// Get current page info
if (is_singular()) {
    $post = get_queried_object();

    // If it's a post, add Blog then category
    if (is_single() && get_post_type() === 'post') {
        // Add Blog link
        $items[] = [
            'name' => 'Blog',
            'url' => $blog_url,
        ];

        $categories = get_the_category($post->ID);
        if (!empty($categories)) {
            $category = $categories[0];
            // Add parent categories
            $parent_cats = get_ancestors($category->term_id, 'category');
            $parent_cats = array_reverse($parent_cats);
            foreach ($parent_cats as $parent_id) {
                $parent = get_term($parent_id, 'category');
                $items[] = [
                    'name' => $parent->name,
                    'url' => get_term_link($parent),
                ];
            }
            $items[] = [
                'name' => $category->name,
                'url' => get_category_link($category->term_id),
            ];
        }
    }

    // If it's a page with parent
    if (is_page() && $post->post_parent) {
        $ancestors = get_post_ancestors($post->ID);
        $ancestors = array_reverse($ancestors);
        foreach ($ancestors as $ancestor_id) {
            $items[] = [
                'name' => get_the_title($ancestor_id),
                'url' => get_permalink($ancestor_id),
            ];
        }
    }

    // If it's a custom post type
    if (!in_array(get_post_type(), ['post', 'page'])) {
        $post_type_obj = get_post_type_object(get_post_type());
        if ($post_type_obj && $post_type_obj->has_archive) {
            $items[] = [
                'name' => $post_type_obj->labels->name,
                'url' => get_post_type_archive_link(get_post_type()),
            ];
        }
    }

    // Current page
    if ($show_current_page) {
        $items[] = [
            'name' => get_the_title($post->ID),
            'url' => null,
            'is_current' => true,
        ];
    }
} elseif (is_category()) {
    $category = get_queried_object();

    // Add Blog link
    $items[] = [
        'name' => 'Blog',
        'url' => $blog_url,
    ];

    // Parent categories
    $parent_cats = get_ancestors($category->term_id, 'category');
    $parent_cats = array_reverse($parent_cats);
    foreach ($parent_cats as $parent_id) {
        $parent = get_term($parent_id, 'category');
        $items[] = [
            'name' => $parent->name,
            'url' => get_term_link($parent),
        ];
    }
    if ($show_current_page) {
        $items[] = [
            'name' => $category->name,
            'url' => null,
            'is_current' => true,
        ];
    }
} elseif (is_tag()) {
    $tag = get_queried_object();
    if ($show_current_page) {
        $items[] = [
            'name' => $tag->name,
            'url' => null,
            'is_current' => true,
        ];
    }
} elseif (is_tax()) {
    $term = get_queried_object();
    if ($show_current_page) {
        $items[] = [
            'name' => $term->name,
            'url' => null,
            'is_current' => true,
        ];
    }
} elseif (is_post_type_archive()) {
    $post_type_obj = get_queried_object();
    if ($show_current_page) {
        $items[] = [
            'name' => $post_type_obj->labels->name,
            'url' => null,
            'is_current' => true,
        ];
    }
} elseif (is_search()) {
    if ($show_current_page) {
        $items[] = [
            'name' => 'Recherche : ' . get_search_query(),
            'url' => null,
            'is_current' => true,
        ];
    }
} elseif (is_404()) {
    if ($show_current_page) {
        $items[] = [
            'name' => 'Page non trouvée',
            'url' => null,
            'is_current' => true,
        ];
    }
}

// Build HTML
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'jurible-breadcrumb']);

$html = '<nav ' . $wrapper_attributes . '>';

// Schema markup
if ($show_schema) {
    $html .= '<script type="application/ld+json">';
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [],
    ];
    foreach ($items as $index => $item) {
        $schema_item = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $item['name'],
        ];
        if (!empty($item['url'])) {
            $schema_item['item'] = $item['url'];
        }
        $schema['itemListElement'][] = $schema_item;
    }
    $html .= wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $html .= '</script>';
}

// Breadcrumb items
foreach ($items as $index => $item) {
    $is_last = $index === count($items) - 1;
    $is_current = !empty($item['is_current']);
    $is_home = !empty($item['is_home']);

    if ($index > 0) {
        $html .= '<span class="jurible-breadcrumb-separator">›</span>';
    }

    if ($is_home) {
        $home_class = 'jurible-breadcrumb-item jurible-breadcrumb-home';
        if ($home_icon_on_mobile) {
            $home_class .= ' jurible-breadcrumb-home--icon-mobile';
        }
        $html .= '<a href="' . esc_url($item['url']) . '" class="' . $home_class . '">';
        $html .= '<span class="jurible-breadcrumb-home-icon">' . $home_icon . '</span>';
        $html .= '<span class="jurible-breadcrumb-home-text">' . esc_html($item['name']) . '</span>';
        $html .= '</a>';
    } elseif ($is_current) {
        $html .= '<span class="jurible-breadcrumb-item jurible-breadcrumb-item--active">' . esc_html($item['name']) . '</span>';
    } else {
        $html .= '<a href="' . esc_url($item['url']) . '" class="jurible-breadcrumb-item">' . esc_html($item['name']) . '</a>';
    }
}

$html .= '</nav>';

echo $html;
