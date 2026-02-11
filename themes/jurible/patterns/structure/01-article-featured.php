<?php
/**
 * Title: Structure 01 - Article Featured
 * Slug: jurible/structure-01-article-featured
 * Categories: structure
 * Description: Section article mis en avant (dynamique - dernier article)
 */
?>
<!-- wp:group {"align":"full","className":"section-article-featured","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"0","right":"0"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull section-article-featured has-white-background-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-right:0;padding-bottom:var(--wp--preset--spacing--xl);padding-left:0">

<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|xl","padding":{"left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignwide" style="padding-right:var(--wp--preset--spacing--md);padding-left:var(--wp--preset--spacing--md)">

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|sm"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
<div class="wp-block-group">

<!-- wp:paragraph {"align":"center","className":"is-style-tag-secondary"} -->
<p class="has-text-align-center is-style-tag-secondary">Notre blog</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","textColor":"text-dark"} -->
<h2 class="wp-block-heading has-text-align-center has-text-dark-color has-text-color">Nos derniers <mark style="background:linear-gradient(135deg, #B0001D 0%, #7C3AED 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text" class="has-inline-color">articles</mark></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"18px","lineHeight":"1.6"}},"textColor":"text-gray"} -->
<p class="has-text-align-center has-text-gray-color has-text-color" style="font-size:18px;line-height:1.6">Méthodologie, conseils et actualités juridiques pour réussir vos études de droit.</p>
<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->

<!-- wp:query {"queryId":1,"query":{"perPage":"1","pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"only","inherit":false},"className":"article-featured-query"} -->
<div class="wp-block-query article-featured-query">

<!-- wp:post-template -->
<!-- wp:group {"className":"article-featured","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}},"border":{"radius":"12px"}},"backgroundColor":"white","layout":{"type":"default"}} -->
<div class="wp-block-group article-featured has-white-background-color has-background" style="border-radius:12px;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0">

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"0"}}}} -->
<div class="wp-block-columns">

<!-- wp:column {"width":"55%"} -->
<div class="wp-block-column" style="flex-basis:55%">
<!-- wp:post-featured-image {"isLink":true,"className":"article-featured__image"} /-->
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"45%","style":{"spacing":{"padding":{"top":"var:preset|spacing|lg","bottom":"var:preset|spacing|lg","left":"var:preset|spacing|lg","right":"var:preset|spacing|lg"}}}} -->
<div class="wp-block-column" style="padding-top:var(--wp--preset--spacing--lg);padding-right:var(--wp--preset--spacing--lg);padding-bottom:var(--wp--preset--spacing--lg);padding-left:var(--wp--preset--spacing--lg);flex-basis:45%">

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|sm"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"left"}} -->
<div class="wp-block-group">

<!-- wp:post-terms {"term":"category","className":"is-style-tag-category"} /-->

<!-- wp:post-title {"level":3,"isLink":true,"style":{"typography":{"fontSize":"26px","fontStyle":"normal","fontWeight":"600","lineHeight":"1.4"}},"textColor":"text-dark"} /-->

<!-- wp:post-excerpt {"moreText":"","excerptLength":25,"style":{"typography":{"fontSize":"16px","lineHeight":"1.6"}},"textColor":"text-gray"} /-->

<!-- wp:post-date {"style":{"typography":{"fontSize":"13px"}},"textColor":"text-muted"} /-->

<!-- wp:read-more {"content":"Lire l'article →","className":"is-style-cta-gradient"} /-->

</div>
<!-- /wp:group -->

</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph {"align":"center","textColor":"text-muted"} -->
<p class="has-text-align-center has-text-muted-color has-text-color">Aucun article mis en avant pour le moment.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results -->

</div>
<!-- /wp:query -->

</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->
