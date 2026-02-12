<?php
/**
 * Title: Structure 04 - Articles Lies
 * Slug: jurible/structure-04-articles-lies
 * Categories: structure
 * Description: Section articles similaires (dynamique - Query Loop) - cartes simplifiees
 */
?>
<!-- wp:group {"align":"full","className":"section-articles-lies","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"0","right":"0"}}},"backgroundColor":"muted","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull section-articles-lies has-muted-background-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-right:0;padding-bottom:var(--wp--preset--spacing--xl);padding-left:0">

<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|xl","padding":{"left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignwide" style="padding-right:var(--wp--preset--spacing--md);padding-left:var(--wp--preset--spacing--md)">

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|sm"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
<div class="wp-block-group">

<!-- wp:paragraph {"align":"center","className":"is-style-tag-secondary"} -->
<p class="has-text-align-center is-style-tag-secondary">À lire aussi</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","textColor":"text-dark"} -->
<h2 class="wp-block-heading has-text-align-center has-text-dark-color has-text-color">Articles <mark style="background:linear-gradient(135deg, #B0001D 0%, #7C3AED 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text" class="has-inline-color">similaires</mark></h2>
<!-- /wp:heading -->

</div>
<!-- /wp:group -->

<!-- wp:query {"queryId":3,"query":{"perPage":"3","pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false},"className":"articles-lies-query"} -->
<div class="wp-block-query articles-lies-query">

<!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->

<!-- wp:group {"className":"card-article card-article--simple","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}},"border":{"radius":"16px"}},"backgroundColor":"white","layout":{"type":"default"}} -->
<div class="wp-block-group card-article card-article--simple has-white-background-color has-background" style="border-radius:16px;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0">

<!-- wp:post-featured-image {"isLink":true,"height":"180px","className":"card-article__image"} /-->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|sm","bottom":"var:preset|spacing|md","left":"var:preset|spacing|md","right":"var:preset|spacing|md"},"blockGap":"var:preset|spacing|xs"}},"layout":{"type":"default"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--sm);padding-right:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--md);padding-left:var(--wp--preset--spacing--md)">

<!-- wp:post-terms {"term":"category","className":"is-style-tag-category"} /-->

<!-- wp:post-title {"level":3,"isLink":true,"style":{"typography":{"fontSize":"16px","fontStyle":"normal","fontWeight":"600","lineHeight":"1.4"},"spacing":{"margin":{"top":"var:preset|spacing|xs"}}},"textColor":"text-dark"} /-->

<!-- wp:post-date {"style":{"typography":{"fontSize":"13px"},"spacing":{"margin":{"top":"var:preset|spacing|sm"}}},"textColor":"text-muted"} /-->

</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->

<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph {"align":"center","textColor":"text-muted"} -->
<p class="has-text-align-center has-text-muted-color has-text-color">Aucun article similaire pour le moment.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results -->

</div>
<!-- /wp:query -->

</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->
