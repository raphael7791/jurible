<?php
/**
 * Title: Structure 05 - Commentaires
 * Slug: jurible/structure-05-commentaires
 * Categories: structure
 * Description: Section commentaires pour les articles de blog
 */
?>
<!-- wp:group {"align":"full","className":"comments-section","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull comments-section has-white-background-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--md)">

<!-- wp:group {"align":"wide","className":"comments-section__header","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|lg"}}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
<div class="wp-block-group alignwide comments-section__header" style="margin-bottom:var(--wp--preset--spacing--lg)">
<!-- wp:paragraph {"style":{"typography":{"fontSize":"20px"}}} -->
<p style="font-size:20px">💬</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3,"className":"comments-section__title","style":{"typography":{"fontSize":"20px","fontWeight":"700"}}} -->
<h3 class="wp-block-heading comments-section__title" style="font-size:20px;font-weight:700">Commentaires</h3>
<!-- /wp:heading -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide">
<!-- wp:post-comments-form {"className":"comments-form"} /-->
</div>
<!-- /wp:group -->

<!-- wp:comments {"align":"wide","className":"comments-list"} -->
<div class="wp-block-comments alignwide comments-list">
<!-- wp:comment-template -->
<!-- wp:group {"className":"comment","style":{"spacing":{"blockGap":"var:preset|spacing|md"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top"}} -->
<div class="wp-block-group comment">
<!-- wp:avatar {"size":48,"className":"comment__avatar"} /-->
<!-- wp:group {"className":"comment__content","style":{"spacing":{"blockGap":"var:preset|spacing|xs"}},"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group comment__content">
<!-- wp:group {"className":"comment__header","style":{"spacing":{"blockGap":"var:preset|spacing|xs"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
<div class="wp-block-group comment__header">
<!-- wp:comment-author-name {"className":"comment__author","style":{"typography":{"fontSize":"14px","fontWeight":"600"}}} /-->
<!-- wp:comment-date {"className":"comment__date","style":{"typography":{"fontSize":"13px"}},"textColor":"text-muted"} /-->
</div>
<!-- /wp:group -->
<!-- wp:comment-content {"className":"comment__text","style":{"typography":{"fontSize":"14px"}},"textColor":"text-gray"} /-->
<!-- wp:comment-reply-link {"className":"comment__reply-btn","style":{"typography":{"fontSize":"14px","fontWeight":"500"}},"textColor":"primary"} /-->
<!-- wp:comment-template {"className":"comment--replies"} -->
<!-- wp:group {"className":"comment comment--reply","style":{"spacing":{"blockGap":"var:preset|spacing|sm"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top"}} -->
<div class="wp-block-group comment comment--reply">
<!-- wp:avatar {"size":40,"className":"comment__avatar comment__avatar--small"} /-->
<!-- wp:group {"className":"comment__content","style":{"spacing":{"blockGap":"var:preset|spacing|xs"}},"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group comment__content">
<!-- wp:group {"className":"comment__header","style":{"spacing":{"blockGap":"var:preset|spacing|xs"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
<div class="wp-block-group comment__header">
<!-- wp:comment-author-name {"className":"comment__author","style":{"typography":{"fontSize":"14px","fontWeight":"600"}}} /-->
<!-- wp:comment-date {"className":"comment__date","style":{"typography":{"fontSize":"13px"}},"textColor":"text-muted"} /-->
</div>
<!-- /wp:group -->
<!-- wp:comment-content {"className":"comment__text","style":{"typography":{"fontSize":"14px"}},"textColor":"text-gray"} /-->
<!-- wp:comment-reply-link {"className":"comment__reply-btn","style":{"typography":{"fontSize":"14px","fontWeight":"500"}},"textColor":"primary"} /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<!-- /wp:comment-template -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<!-- /wp:comment-template -->

<!-- wp:comments-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:comments-pagination-previous /-->
<!-- wp:comments-pagination-numbers /-->
<!-- wp:comments-pagination-next /-->
<!-- /wp:comments-pagination -->
</div>
<!-- /wp:comments -->

</div>
<!-- /wp:group -->
