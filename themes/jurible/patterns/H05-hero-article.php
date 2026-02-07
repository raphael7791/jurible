<?php
/**
 * Title: H05 - Hero Article
 * Slug: jurible/h05-hero-article
 * Categories: hero
 * Description: Hero article E1 - Fond lilas avec badge, titre, meta, auteur et image
 */
?>
<!-- wp:group {"align":"full","className":"hero-article","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"layout":{"type":"constrained","contentSize":"720px"}} -->
<div class="wp-block-group alignfull hero-article" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--md)">

<!-- wp:group {"className":"hero-article__container","layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
<div class="wp-block-group hero-article__container">

<!-- wp:paragraph {"align":"center","className":"hero-article__badge"} -->
<p class="has-text-align-center hero-article__badge">📝 Méthodologie</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","level":1,"className":"hero-article__title"} -->
<h1 class="wp-block-heading has-text-align-center hero-article__title">Comment rédiger une fiche d'arrêt parfaite en 6 étapes</h1>
<!-- /wp:heading -->

<!-- wp:group {"className":"hero-article__author-block","layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
<div class="wp-block-group hero-article__author-block">

<!-- wp:group {"className":"hero-article__meta-row","layout":{"type":"flex"}} -->
<div class="wp-block-group hero-article__meta-row">
<!-- wp:paragraph {"className":"hero-article__meta-spacer"} -->
<p class="hero-article__meta-spacer"></p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"hero-article__meta","layout":{"type":"flex"}} -->
<div class="wp-block-group hero-article__meta">
<!-- wp:paragraph -->
<p>12 janvier 2026</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"hero-article__meta-separator"} -->
<p class="hero-article__meta-separator">·</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>8 min de lecture</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"hero-article__author","layout":{"type":"flex"}} -->
<div class="wp-block-group hero-article__author">
<!-- wp:paragraph {"className":"hero-article__author-initials"} -->
<p class="hero-article__author-initials">R</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"hero-article__author-info","layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group hero-article__author-info">
<!-- wp:paragraph {"className":"hero-article__author-name"} -->
<p class="hero-article__author-name">Raphaël Briguet-Lamarre</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"hero-article__author-role"} -->
<p class="hero-article__author-role">Ex-avocat, chargé d'enseignement à l'université</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->

<!-- wp:image {"className":"hero-article__image","sizeSlug":"large"} -->
<figure class="wp-block-image size-large hero-article__image"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/placeholder-article-hero.jpg' ) ); ?>" alt="Image à la une de l'article"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"hero-article__cta","layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-group hero-article__cta">
<!-- wp:paragraph -->
<p>Envie d'aller plus loin ? <a href="#">Rejoindre l'Académie · 29€/mois</a></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->
