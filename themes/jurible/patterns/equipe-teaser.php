<?php
/**
 * Title: Équipe Teaser
 * Slug: jurible/equipe-teaser
 * Categories: jurible-components
 * Description: Section équipe avec avatars empilés pour pages prépa et secondaires
 */
?>
<!-- wp:group {"align":"full","className":"equipe-teaser-section","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull equipe-teaser-section has-white-background-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--md)">

	<!-- wp:group {"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
	<div class="wp-block-group">

		<!-- wp:group {"className":"equipe-teaser__avatars","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
		<div class="wp-block-group equipe-teaser__avatars">
			<!-- wp:image {"sizeSlug":"full","className":"is-style-rounded equipe-teaser__avatar"} -->
			<figure class="wp-block-image size-full is-style-rounded equipe-teaser__avatar"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/placeholder-avatar.svg'); ?>" alt=""/></figure>
			<!-- /wp:image -->

			<!-- wp:image {"sizeSlug":"full","className":"is-style-rounded equipe-teaser__avatar"} -->
			<figure class="wp-block-image size-full is-style-rounded equipe-teaser__avatar"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/placeholder-avatar.svg'); ?>" alt=""/></figure>
			<!-- /wp:image -->

			<!-- wp:image {"sizeSlug":"full","className":"is-style-rounded equipe-teaser__avatar"} -->
			<figure class="wp-block-image size-full is-style-rounded equipe-teaser__avatar"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/placeholder-avatar.svg'); ?>" alt=""/></figure>
			<!-- /wp:image -->

			<!-- wp:image {"sizeSlug":"full","className":"is-style-rounded equipe-teaser__avatar"} -->
			<figure class="wp-block-image size-full is-style-rounded equipe-teaser__avatar"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/placeholder-avatar.svg'); ?>" alt=""/></figure>
			<!-- /wp:image -->

			<!-- wp:image {"sizeSlug":"full","className":"is-style-rounded equipe-teaser__avatar"} -->
			<figure class="wp-block-image size-full is-style-rounded equipe-teaser__avatar"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/placeholder-avatar.svg'); ?>" alt=""/></figure>
			<!-- /wp:image -->

			<!-- wp:group {"className":"equipe-teaser__avatar-more","backgroundColor":"primary","layout":{"type":"flex","justifyContent":"center","verticalAlignment":"center"}} -->
			<div class="wp-block-group equipe-teaser__avatar-more has-primary-background-color has-background">
				<!-- wp:paragraph {"style":{"typography":{"fontSize":"14px","fontWeight":"600"}},"textColor":"white"} -->
				<p class="has-white-color has-text-color" style="font-size:14px;font-weight:600">+6</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->

		<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"40px","fontWeight":"700"}},"textColor":"text-dark"} -->
		<h2 class="wp-block-heading has-text-align-center has-text-dark-color has-text-color" style="font-size:40px;font-weight:700">Des enseignants <mark style="background:linear-gradient(135deg, #B0001D 0%, #7C3AED 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text" class="has-inline-color">passionnés</mark></h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"15px","lineHeight":"1.6"}},"textColor":"text-gray"} -->
		<p class="has-text-align-center has-text-gray-color has-text-color" style="font-size:15px;line-height:1.6">Tous les contenus sont rédigés par 11 professionnels du droit — avocats, doctorants et chargés d'enseignement — tous titulaires d'un Master 2 minimum.</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"is-style-secondary"} -->
			<div class="wp-block-button is-style-secondary"><a class="wp-block-button__link wp-element-button" href="/enseignants">Découvrir nos enseignants →</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->

	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
