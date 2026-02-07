<?php
/**
 * Title: C04 - Bio Auteur
 * Slug: jurible/c04-bio-auteur
 * Categories: jurible-components
 * Description: Affiche les infos de l auteur du post
 */
?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|sm","bottom":"var:preset|spacing|sm","left":"var:preset|spacing|sm","right":"var:preset|spacing|sm"},"blockGap":"var:preset|spacing|sm"},"border":{"radius":"12px"}},"backgroundColor":"muted","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top"}} -->
<div class="wp-block-group has-muted-background-color has-background" style="border-radius:12px;padding-top:var(--wp--preset--spacing--sm);padding-right:var(--wp--preset--spacing--sm);padding-bottom:var(--wp--preset--spacing--sm);padding-left:var(--wp--preset--spacing--sm)">
	<!-- wp:avatar {"size":64,"isLink":false,"style":{"border":{"radius":"9999px"}}} /-->
	<!-- wp:group {"style":{"spacing":{"blockGap":"4px"}},"layout":{"type":"flex","orientation":"vertical"}} -->
	<div class="wp-block-group">
		<!-- wp:paragraph {"style":{"typography":{"fontSize":"11px"}},"textColor":"text-muted"} -->
		<p class="has-text-muted-color has-text-color" style="font-size:11px">Rédigé par</p>
		<!-- /wp:paragraph -->
		<!-- wp:post-author-name {"isLink":false,"style":{"typography":{"fontSize":"16px","fontWeight":"600"}},"textColor":"text-dark"} /-->
		<!-- wp:post-author-biography {"style":{"typography":{"fontSize":"13px","lineHeight":"1.5"}},"textColor":"text-gray"} /-->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
