<?php
/**
 * Title: Equipe 03b - Enseignants Teaser Compact
 * Slug: jurible/equipe-03b-enseignants-teaser-compact
 * Categories: equipe
 * Description: Version compacte du teaser équipe pour les articles (sans alignfull)
 */
?>
<!-- wp:group {"align":"full","className":"equipe-teaser-section","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"backgroundColor":"muted","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull equipe-teaser-section has-muted-background-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--md)">

<!-- wp:group {"className":"equipe-teaser__photo-wrapper","gradient":"cta-gradient","layout":{"type":"default"}} -->
<div class="wp-block-group equipe-teaser__photo-wrapper has-cta-gradient-gradient-background has-background">

<!-- wp:image {"sizeSlug":"full","className":"equipe-teaser__photo is-style-default"} -->
<figure class="wp-block-image size-full equipe-teaser__photo is-style-default"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/team/photo-des-profs-jurible.com.webp'); ?>" alt="L'équipe pédagogique Jurible"/></figure>
<!-- /wp:image -->

</div>
<!-- /wp:group -->

<!-- wp:spacer {"height":"var:preset|spacing|xs"} -->
<div style="height:var(--wp--preset--spacing--xs)" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:group {"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
<div class="wp-block-group">

<!-- wp:heading {"textAlign":"center","textColor":"text-dark"} -->
<h2 class="wp-block-heading has-text-align-center has-text-dark-color has-text-color">Des enseignants <mark>passionnés</mark></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","textColor":"text-gray","fontSize":"body-large"} -->
<p class="has-text-align-center has-text-gray-color has-text-color has-body-large-font-size">Tous nos cours sont conçus par des professionnels du droit — avocats, doctorants et chargés d'enseignement — titulaires d'un Master 2 minimum.</p>
<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->
