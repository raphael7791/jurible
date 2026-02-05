<?php
/**
 * Title: Enseignant Spécifique
 * Slug: jurible/enseignant-card
 * Categories: jurible-components
 * Description: Section enseignant pour pages cours/fiches par matière
 */
?>
<!-- wp:group {"align":"full","className":"enseignant-section","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull enseignant-section has-white-background-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--md)">

	<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|sm","margin":{"bottom":"var:preset|spacing|lg"}}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
	<div class="wp-block-group" style="margin-bottom:var(--wp--preset--spacing--lg)">
		<!-- wp:paragraph {"align":"center","className":"is-style-tag-secondary"} -->
		<p class="has-text-align-center is-style-tag-secondary">👨‍🏫 Votre enseignant</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"40px","fontWeight":"700"}},"textColor":"text-dark"} -->
		<h2 class="wp-block-heading has-text-align-center has-text-dark-color has-text-color" style="font-size:40px;font-weight:700">Un cours créé par des <mark style="background:linear-gradient(135deg, #B0001D 0%, #7C3AED 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text" class="has-inline-color">experts</mark></h2>
		<!-- /wp:heading -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|md","bottom":"var:preset|spacing|md","left":"var:preset|spacing|md","right":"var:preset|spacing|md"},"blockGap":"var:preset|spacing|md"},"border":{"radius":"12px"}},"backgroundColor":"muted","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top","justifyContent":"center"}} -->
	<div class="wp-block-group has-muted-background-color has-background" style="border-radius:12px;padding-top:var(--wp--preset--spacing--md);padding-right:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--md);padding-left:var(--wp--preset--spacing--md)">

		<!-- wp:image {"sizeSlug":"full","className":"is-style-rounded enseignant-avatar"} -->
		<figure class="wp-block-image size-full is-style-rounded enseignant-avatar"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/placeholder-avatar.svg'); ?>" alt="Photo enseignant"/></figure>
		<!-- /wp:image -->

		<!-- wp:group {"style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"flex","orientation":"vertical"}} -->
		<div class="wp-block-group">
			<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"20px","fontWeight":"700"}},"textColor":"text-dark"} -->
			<h3 class="wp-block-heading has-text-dark-color has-text-color" style="font-size:20px;font-weight:700">Raphaël Briguet-Lamarre</h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"style":{"typography":{"fontSize":"14px"}},"textColor":"text-gray"} -->
			<p class="has-text-gray-color has-text-color" style="font-size:14px">Responsable pédagogique — Droit constitutionnel</p>
			<!-- /wp:paragraph -->

			<!-- wp:list {"className":"enseignant-credentials","style":{"typography":{"fontSize":"13px"},"spacing":{"padding":{"top":"8px","bottom":"0"}}},"textColor":"text-gray"} -->
			<ul class="enseignant-credentials has-text-gray-color has-text-color" style="font-size:13px;padding-top:8px;padding-bottom:0">
				<li>Ex-chargé d'enseignement Université de Nice</li>
				<li>Ancien avocat inscrit au barreau</li>
				<li>Master 2 Droit et pratiques des relations de travail (Assas)</li>
			</ul>
			<!-- /wp:list -->

			<!-- wp:paragraph {"style":{"typography":{"fontSize":"14px","fontStyle":"italic"},"border":{"left":{"color":"var:preset|color|primary","width":"3px"}},"spacing":{"padding":{"left":"var:preset|spacing|sm"},"margin":{"top":"8px"}}},"textColor":"primary"} -->
			<p class="has-primary-color has-text-color" style="border-left-color:var(--wp--preset--color--primary);border-left-width:3px;margin-top:8px;padding-left:var(--wp--preset--spacing--sm);font-size:14px;font-style:italic">« Connaître les bases du droit constitutionnel relève de la culture générale ! »</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|md"}},"border":{"top":{"color":"var:preset|color|border","width":"1px"}}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
	<div class="wp-block-group" style="border-top-color:var(--wp--preset--color--border);border-top-width:1px;padding-top:var(--wp--preset--spacing--md)">
		<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"13px"}},"textColor":"text-gray"} -->
		<p class="has-text-align-center has-text-gray-color has-text-color" style="font-size:13px">Notre équipe pédagogique de <strong>11 enseignants</strong> — avocats, doctorants et chargés d'enseignement — assure la qualité du contenu.</p>
		<!-- /wp:paragraph -->

		<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"13px","fontWeight":"600"}}} -->
		<p class="has-text-align-center" style="font-size:13px;font-weight:600"><a href="/enseignants">Découvrir toute l'équipe →</a></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
