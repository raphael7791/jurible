<?php
/**
 * Title: Structure 03 - Articles Grid
 * Slug: jurible/structure-03-articles-grid
 * Categories: structure
 * Description: Section grille 3 articles (dynamique - Query Loop)
 */
?>
<!-- wp:group {"align":"full","className":"section-articles-grid","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"0","right":"0"}}},"backgroundColor":"muted","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull section-articles-grid has-muted-background-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-right:0;padding-bottom:var(--wp--preset--spacing--xl);padding-left:0">

<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|xl","padding":{"left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignwide" style="padding-right:var(--wp--preset--spacing--md);padding-left:var(--wp--preset--spacing--md)">

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|sm"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
<div class="wp-block-group">

<!-- wp:paragraph {"align":"center","className":"is-style-tag-secondary"} -->
<p class="has-text-align-center is-style-tag-secondary">Tous les articles</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","textColor":"text-dark"} -->
<h2 class="wp-block-heading has-text-align-center has-text-dark-color has-text-color">Ressources pour <mark style="background:linear-gradient(135deg, #B0001D 0%, #7C3AED 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text" class="has-inline-color">réussir en droit</mark></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"18px","lineHeight":"1.6"}},"textColor":"text-gray"} -->
<p class="has-text-align-center has-text-gray-color has-text-color" style="font-size:18px;line-height:1.6">Méthodologie, orientation, vie étudiante et actualités juridiques.</p>
<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->

<!-- wp:query {"queryId":2,"query":{"perPage":"3","pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false},"className":"articles-grid-query"} -->
<div class="wp-block-query articles-grid-query">

<!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->

<!-- wp:group {"className":"card-article","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}},"border":{"radius":"16px"}},"backgroundColor":"white","layout":{"type":"default"}} -->
<div class="wp-block-group card-article has-white-background-color has-background" style="border-radius:16px;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0">

<!-- wp:post-featured-image {"isLink":true,"height":"180px","className":"card-article__image"} /-->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|md","bottom":"var:preset|spacing|md","left":"var:preset|spacing|md","right":"var:preset|spacing|md"},"blockGap":"var:preset|spacing|xs"}},"layout":{"type":"default"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--md);padding-right:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--md);padding-left:var(--wp--preset--spacing--md)">

<!-- wp:post-terms {"term":"category","className":"is-style-tag-category"} /-->

<!-- wp:post-title {"level":3,"isLink":true,"style":{"typography":{"fontSize":"16px","fontStyle":"normal","fontWeight":"600","lineHeight":"1.4"},"spacing":{"margin":{"top":"var:preset|spacing|xs"}}},"textColor":"text-dark"} /-->

<!-- wp:post-excerpt {"moreText":"","excerptLength":18,"style":{"typography":{"fontSize":"14px","lineHeight":"1.6"},"spacing":{"margin":{"top":"var:preset|spacing|xs"}}},"textColor":"text-gray"} /-->

<!-- wp:group {"style":{"spacing":{"margin":{"top":"var:preset|spacing|sm"}}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group" style="margin-top:var(--wp--preset--spacing--sm)">

<!-- wp:post-date {"style":{"typography":{"fontSize":"13px"}},"textColor":"text-muted"} /-->

<!-- wp:read-more {"content":"Lire →","className":"card-article__link"} /-->

</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->

<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph {"align":"center","textColor":"text-muted"} -->
<p class="has-text-align-center has-text-muted-color has-text-color">Aucun article pour le moment.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results -->

</div>
<!-- /wp:query -->

<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"var:preset|spacing|xl"}}},"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--xl)">
<!-- wp:button {"className":"is-style-outline-dark"} -->
<div class="wp-block-button is-style-outline-dark"><a class="wp-block-button__link wp-element-button" href="/blog">Voir tous les articles →</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->
