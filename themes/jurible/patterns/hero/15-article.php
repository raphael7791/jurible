<?php
/**
 * Title: Hero 15 - Article
 * Slug: jurible/hero-15-article
 * Categories: hero
 * Description: Hero article E1 - Fond lilas avec badge, titre, meta, auteur et image
 * Block Types: core/post-content
 */
?>
<!-- wp:group {"align":"full","className":"hero-article","style":{"spacing":{"padding":{"top":"var:preset|spacing|sm","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull hero-article" style="padding-top:var(--wp--preset--spacing--sm);padding-right:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--md)">

<!-- wp:group {"className":"hero-article__container","layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
<div class="wp-block-group hero-article__container">

<!-- wp:post-terms {"term":"category","textAlign":"center","className":"hero-article__badge"} /-->

<!-- wp:post-title {"textAlign":"center","level":1,"className":"hero-article__title"} /-->

<!-- wp:group {"className":"hero-article__author-block","layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
<div class="wp-block-group hero-article__author-block">

<!-- wp:group {"className":"hero-article__meta-row","layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-group hero-article__meta-row">

<!-- wp:group {"className":"hero-article__meta","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group hero-article__meta">

<!-- wp:post-date {"format":"j F Y"} /-->

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

<!-- wp:group {"className":"hero-article__author","layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-group hero-article__author">

<!-- wp:avatar {"size":48,"className":"hero-article__author-avatar"} /-->

<!-- wp:group {"className":"hero-article__author-info","layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group hero-article__author-info">

<!-- wp:post-author-name {"className":"hero-article__author-name"} /-->

<!-- wp:paragraph {"className":"hero-article__author-role"} -->
<p class="hero-article__author-role">Rédacteur Jurible</p>
<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->

<!-- wp:post-featured-image {"className":"hero-article__image","sizeSlug":"large"} /-->

<!-- wp:group {"className":"hero-article__cta","layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-group hero-article__cta">
<!-- wp:paragraph -->
<p>Envie d'aller plus loin ? <a href="/academie">Rejoindre l'Académie</a></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->
