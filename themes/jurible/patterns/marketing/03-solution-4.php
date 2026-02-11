<?php
/**
 * Title: Marketing 03 - Solution 4 Cartes
 * Slug: jurible/marketing-03-solution-4
 * Categories: marketing
 * Description: Section avec header et 4 cartes solutions en grille 4 colonnes
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"backgroundColor":"background","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-background-background-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--md)">
	<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|sm"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
	<div class="wp-block-group">
		<!-- wp:paragraph {"className":"is-style-tag-secondary"} -->
		<p class="is-style-tag-secondary">✨ La solution</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"textAlign":"center","level":2} -->
		<h2 class="wp-block-heading has-text-align-center">Tout ce dont vous avez besoin pour <mark style="background: linear-gradient(135deg, var(--wp--preset--color--primary) 0%, var(--wp--preset--color--secondary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">réussir</mark></h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"var:preset|font-size|body-large"}},"textColor":"text-gray"} -->
		<p class="has-text-align-center has-text-gray-color has-text-color" style="font-size:var(--wp--preset--font-size--body-large)">Une plateforme complète pour vous accompagner dans votre préparation</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"var:preset|spacing|md","left":"var:preset|spacing|md"},"margin":{"top":"var:preset|spacing|lg"}}}} -->
	<div class="wp-block-columns" style="margin-top:var(--wp--preset--spacing--lg)">
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:jurible/solution-card {"layout":"centered","icon":"📚","title":"Cours complets","description":"Des cours structurés et progressifs pour maîtriser chaque notion."} -->
			<div class="wp-block-jurible-solution-card solution-card solution-card--centered"><div class="solution-card__icon">📚</div><div class="solution-card__content"><h4 class="solution-card__title">Cours complets</h4><p class="solution-card__description">Des cours structurés et progressifs pour maîtriser chaque notion.</p></div></div>
			<!-- /wp:jurible/solution-card -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:jurible/solution-card {"layout":"centered","icon":"🎯","title":"Exercices ciblés","description":"Des exercices adaptés pour s'entraîner efficacement."} -->
			<div class="wp-block-jurible-solution-card solution-card solution-card--centered"><div class="solution-card__icon">🎯</div><div class="solution-card__content"><h4 class="solution-card__title">Exercices ciblés</h4><p class="solution-card__description">Des exercices adaptés pour s'entraîner efficacement.</p></div></div>
			<!-- /wp:jurible/solution-card -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:jurible/solution-card {"layout":"centered","icon":"📝","title":"Fiches de révision","description":"Des fiches synthétiques pour réviser rapidement."} -->
			<div class="wp-block-jurible-solution-card solution-card solution-card--centered"><div class="solution-card__icon">📝</div><div class="solution-card__content"><h4 class="solution-card__title">Fiches de révision</h4><p class="solution-card__description">Des fiches synthétiques pour réviser rapidement.</p></div></div>
			<!-- /wp:jurible/solution-card -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:jurible/solution-card {"layout":"centered","icon":"🤖","title":"IA personnalisée","description":"Un assistant IA pour répondre à toutes vos questions."} -->
			<div class="wp-block-jurible-solution-card solution-card solution-card--centered"><div class="solution-card__icon">🤖</div><div class="solution-card__content"><h4 class="solution-card__title">IA personnalisée</h4><p class="solution-card__description">Un assistant IA pour répondre à toutes vos questions.</p></div></div>
			<!-- /wp:jurible/solution-card -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
