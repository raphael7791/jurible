<?php
/**
 * Title: Equipe 01 - Enseignants Grille
 * Slug: jurible/equipe-01-enseignants-grille
 * Categories: equipe
 * Description: Grille de cartes enseignants pour page Nos enseignants (colonnes responsive)
 */
?>
<!-- wp:group {"align":"full","className":"equipe-grille-section","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull equipe-grille-section has-white-background-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--md)">

	<!-- wp:group {"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|lg"}}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
	<div class="wp-block-group" style="margin-bottom:var(--wp--preset--spacing--lg)">
		<!-- wp:paragraph {"align":"center","className":"is-style-tag-secondary"} -->
		<p class="has-text-align-center is-style-tag-secondary">👨‍🏫 L'équipe</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"textAlign":"center","textColor":"text-dark"} -->
		<h2 class="wp-block-heading has-text-align-center has-text-dark-color has-text-color">Une équipe de <mark style="background:linear-gradient(135deg, #B0001D 0%, #7C3AED 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text" class="has-inline-color">juristes passionnés</mark></h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"15px","lineHeight":"1.6"}},"textColor":"text-gray"} -->
		<p class="has-text-align-center has-text-gray-color has-text-color" style="font-size:15px;line-height:1.6">Des enseignants diplômés, dédiés à votre réussite.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:columns {"align":"wide","className":"equipe-grille__columns"} -->
	<div class="wp-block-columns alignwide equipe-grille__columns">

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"className":"equipe-grille__card equipe-grille__card--fondateur","style":{"border":{"radius":"12px"}},"backgroundColor":"white","layout":{"type":"default"}} -->
			<div class="wp-block-group equipe-grille__card equipe-grille__card--fondateur has-white-background-color has-background" style="border-radius:12px">
				<!-- wp:group {"className":"equipe-grille__header equipe-grille__header--fondateur","layout":{"type":"default"}} -->
				<div class="wp-block-group equipe-grille__header equipe-grille__header--fondateur">
					<!-- wp:paragraph {"align":"center","className":"equipe-grille__badge","style":{"typography":{"fontSize":"11px","fontWeight":"700","textTransform":"uppercase"}}} -->
					<p class="has-text-align-center equipe-grille__badge" style="font-size:11px;font-weight:700;text-transform:uppercase">Fondateur</p>
					<!-- /wp:paragraph -->

					<!-- wp:image {"sizeSlug":"full","className":"equipe-grille__avatar"} -->
					<figure class="wp-block-image size-full equipe-grille__avatar"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/placeholder-avatar.svg'); ?>" alt=""/></figure>
					<!-- /wp:image -->
				</div>
				<!-- /wp:group -->

				<!-- wp:group {"className":"equipe-grille__body","style":{"spacing":{"padding":{"top":"var:preset|spacing|md","bottom":"var:preset|spacing|md","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"layout":{"type":"default"}} -->
				<div class="wp-block-group equipe-grille__body" style="padding-top:var(--wp--preset--spacing--md);padding-right:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--md);padding-left:var(--wp--preset--spacing--md)">
					<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"18px","fontWeight":"700"}},"textColor":"text-dark"} -->
					<h3 class="wp-block-heading has-text-dark-color has-text-color" style="font-size:18px;font-weight:700">Raphaël Briguet-Lamarre</h3>
					<!-- /wp:heading -->

					<!-- wp:paragraph {"className":"equipe-grille__role","style":{"typography":{"fontSize":"11px","fontWeight":"600","textTransform":"uppercase"}},"textColor":"primary"} -->
					<p class="equipe-grille__role has-primary-color has-text-color" style="font-size:11px;font-weight:600;text-transform:uppercase">Ex-avocat, chargé d'enseignement</p>
					<!-- /wp:paragraph -->

					<!-- wp:group {"className":"equipe-grille__tags","style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
					<div class="wp-block-group equipe-grille__tags">
						<!-- wp:paragraph {"className":"is-style-tag-secondary","style":{"typography":{"fontSize":"12px"}}} -->
						<p class="is-style-tag-secondary" style="font-size:12px">Droit social</p>
						<!-- /wp:paragraph -->
						<!-- wp:paragraph {"className":"is-style-tag-secondary","style":{"typography":{"fontSize":"12px"}}} -->
						<p class="is-style-tag-secondary" style="font-size:12px">Responsabilité civile</p>
						<!-- /wp:paragraph -->
					</div>
					<!-- /wp:group -->

					<!-- wp:paragraph {"style":{"typography":{"fontSize":"13px","lineHeight":"1.5"}},"textColor":"text-gray"} -->
					<p class="has-text-gray-color has-text-color" style="font-size:13px;line-height:1.5">Fondateur de Jurible, enseignant à l'Université Nice Sophia Antipolis et formateur en droit social.</p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"equipe-grille__diploma","style":{"typography":{"fontSize":"12px"}},"textColor":"text-muted"} -->
					<p class="equipe-grille__diploma has-text-muted-color has-text-color" style="font-size:12px">🎓 Master 2 Droit social — Paris II Panthéon-Assas</p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"equipe-grille__linkedin","style":{"typography":{"fontSize":"12px","fontWeight":"500"}},"textColor":"secondary"} -->
					<p class="equipe-grille__linkedin has-secondary-color has-text-color" style="font-size:12px;font-weight:500"><a href="#">Voir le profil LinkedIn</a></p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"className":"equipe-grille__card equipe-grille__card--fondateur","style":{"border":{"radius":"12px"}},"backgroundColor":"white","layout":{"type":"default"}} -->
			<div class="wp-block-group equipe-grille__card equipe-grille__card--fondateur has-white-background-color has-background" style="border-radius:12px">
				<!-- wp:group {"className":"equipe-grille__header equipe-grille__header--fondateur","layout":{"type":"default"}} -->
				<div class="wp-block-group equipe-grille__header equipe-grille__header--fondateur">
					<!-- wp:paragraph {"align":"center","className":"equipe-grille__badge","style":{"typography":{"fontSize":"11px","fontWeight":"700","textTransform":"uppercase"}}} -->
					<p class="has-text-align-center equipe-grille__badge" style="font-size:11px;font-weight:700;text-transform:uppercase">Fondatrice</p>
					<!-- /wp:paragraph -->

					<!-- wp:image {"sizeSlug":"full","className":"equipe-grille__avatar"} -->
					<figure class="wp-block-image size-full equipe-grille__avatar"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/placeholder-avatar.svg'); ?>" alt=""/></figure>
					<!-- /wp:image -->
				</div>
				<!-- /wp:group -->

				<!-- wp:group {"className":"equipe-grille__body","style":{"spacing":{"padding":{"top":"var:preset|spacing|md","bottom":"var:preset|spacing|md","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"layout":{"type":"default"}} -->
				<div class="wp-block-group equipe-grille__body" style="padding-top:var(--wp--preset--spacing--md);padding-right:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--md);padding-left:var(--wp--preset--spacing--md)">
					<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"18px","fontWeight":"700"}},"textColor":"text-dark"} -->
					<h3 class="wp-block-heading has-text-dark-color has-text-color" style="font-size:18px;font-weight:700">Laura Briguet-Lamarre</h3>
					<!-- /wp:heading -->

					<!-- wp:paragraph {"className":"equipe-grille__role","style":{"typography":{"fontSize":"11px","fontWeight":"600","textTransform":"uppercase"}},"textColor":"primary"} -->
					<p class="equipe-grille__role has-primary-color has-text-color" style="font-size:11px;font-weight:600;text-transform:uppercase">Co-fondatrice</p>
					<!-- /wp:paragraph -->

					<!-- wp:group {"className":"equipe-grille__tags","style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
					<div class="wp-block-group equipe-grille__tags">
						<!-- wp:paragraph {"className":"is-style-tag-secondary","style":{"typography":{"fontSize":"12px"}}} -->
						<p class="is-style-tag-secondary" style="font-size:12px">Direction</p>
						<!-- /wp:paragraph -->
						<!-- wp:paragraph {"className":"is-style-tag-secondary","style":{"typography":{"fontSize":"12px"}}} -->
						<p class="is-style-tag-secondary" style="font-size:12px">Stratégie</p>
						<!-- /wp:paragraph -->
						<!-- wp:paragraph {"className":"is-style-tag-secondary","style":{"typography":{"fontSize":"12px"}}} -->
						<p class="is-style-tag-secondary" style="font-size:12px">Pédagogie</p>
						<!-- /wp:paragraph -->
					</div>
					<!-- /wp:group -->

					<!-- wp:paragraph {"style":{"typography":{"fontSize":"13px","lineHeight":"1.5"}},"textColor":"text-gray"} -->
					<p class="has-text-gray-color has-text-color" style="font-size:13px;line-height:1.5">Co-fondatrice de Jurible, ancienne responsable de clientèle dans la publicité.</p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"equipe-grille__diploma","style":{"typography":{"fontSize":"12px"}},"textColor":"text-muted"} -->
					<p class="equipe-grille__diploma has-text-muted-color has-text-color" style="font-size:12px">🎓 Master 2 Sciences politiques — Paris II</p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"equipe-grille__linkedin","style":{"typography":{"fontSize":"12px","fontWeight":"500"}},"textColor":"secondary"} -->
					<p class="equipe-grille__linkedin has-secondary-color has-text-color" style="font-size:12px;font-weight:500"><a href="#">Voir le profil LinkedIn</a></p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"className":"equipe-grille__card equipe-grille__card--intervenant","style":{"border":{"radius":"12px"}},"backgroundColor":"white","layout":{"type":"default"}} -->
			<div class="wp-block-group equipe-grille__card equipe-grille__card--intervenant has-white-background-color has-background" style="border-radius:12px">
				<!-- wp:group {"className":"equipe-grille__header equipe-grille__header--intervenant","layout":{"type":"default"}} -->
				<div class="wp-block-group equipe-grille__header equipe-grille__header--intervenant">
					<!-- wp:paragraph {"align":"center","className":"equipe-grille__badge","style":{"typography":{"fontSize":"11px","fontWeight":"700","textTransform":"uppercase"}}} -->
					<p class="has-text-align-center equipe-grille__badge" style="font-size:11px;font-weight:700;text-transform:uppercase">Intervenante</p>
					<!-- /wp:paragraph -->

					<!-- wp:image {"sizeSlug":"full","className":"equipe-grille__avatar"} -->
					<figure class="wp-block-image size-full equipe-grille__avatar"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/placeholder-avatar.svg'); ?>" alt=""/></figure>
					<!-- /wp:image -->
				</div>
				<!-- /wp:group -->

				<!-- wp:group {"className":"equipe-grille__body","style":{"spacing":{"padding":{"top":"var:preset|spacing|md","bottom":"var:preset|spacing|md","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"layout":{"type":"default"}} -->
				<div class="wp-block-group equipe-grille__body" style="padding-top:var(--wp--preset--spacing--md);padding-right:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--md);padding-left:var(--wp--preset--spacing--md)">
					<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"18px","fontWeight":"700"}},"textColor":"text-dark"} -->
					<h3 class="wp-block-heading has-text-dark-color has-text-color" style="font-size:18px;font-weight:700">Maître Shéhérazade Aqil</h3>
					<!-- /wp:heading -->

					<!-- wp:paragraph {"className":"equipe-grille__role","style":{"typography":{"fontSize":"11px","fontWeight":"600","textTransform":"uppercase"}},"textColor":"text-muted"} -->
					<p class="equipe-grille__role has-text-muted-color has-text-color" style="font-size:11px;font-weight:600;text-transform:uppercase">Avocate au barreau de Paris</p>
					<!-- /wp:paragraph -->

					<!-- wp:group {"className":"equipe-grille__tags","style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
					<div class="wp-block-group equipe-grille__tags">
						<!-- wp:paragraph {"className":"is-style-tag-secondary","style":{"typography":{"fontSize":"12px"}}} -->
						<p class="is-style-tag-secondary" style="font-size:12px">Droit des sociétés</p>
						<!-- /wp:paragraph -->
						<!-- wp:paragraph {"className":"is-style-tag-secondary","style":{"typography":{"fontSize":"12px"}}} -->
						<p class="is-style-tag-secondary" style="font-size:12px">Droit commercial</p>
						<!-- /wp:paragraph -->
					</div>
					<!-- /wp:group -->

					<!-- wp:paragraph {"style":{"typography":{"fontSize":"13px","lineHeight":"1.5"}},"textColor":"text-gray"} -->
					<p class="has-text-gray-color has-text-color" style="font-size:13px;line-height:1.5">Avocate spécialisée au sein d'AQUIL AVOCAT, accompagnant entrepreneurs et TPE/PME.</p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"equipe-grille__diploma","style":{"typography":{"fontSize":"12px"}},"textColor":"text-muted"} -->
					<p class="equipe-grille__diploma has-text-muted-color has-text-color" style="font-size:12px">🎓 Master 2 Droit des affaires</p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"equipe-grille__linkedin","style":{"typography":{"fontSize":"12px","fontWeight":"500"}},"textColor":"secondary"} -->
					<p class="equipe-grille__linkedin has-secondary-color has-text-color" style="font-size:12px;font-weight:500"><a href="#">Voir le profil LinkedIn</a></p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

</div>
<!-- /wp:group -->
