<?php
/**
 * Title: P02 - Texte + Video
 * Slug: jurible/p02-texte-video
 * Categories: jurible-components
 * Description: Bloc 2 colonnes - Texte à gauche + Vidéo YouTube à droite
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-white-background-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-right:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--xl);padding-left:var(--wp--preset--spacing--md)">

<!-- wp:columns {"align":"wide","verticalAlignment":"center","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|xl"}}}} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%">

<!-- wp:paragraph {"className":"is-style-tag-secondary"} -->
<p class="is-style-tag-secondary">📚 L'Académie</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Apprenez le droit autrement</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"text-gray"} -->
<p class="has-text-gray-color has-text-color">L'Académie Jurible vous donne accès à l'ensemble de nos cours de droit en vidéo, des fiches de révision interactives et des centaines de QCM pour valider vos acquis.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"is-style-checkmark-list","textColor":"text-gray"} -->
<ul class="is-style-checkmark-list has-text-gray-color has-text-color">
<!-- wp:list-item -->
<li>+500 heures de cours vidéo par des enseignants qualifiés</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Fiches de révision sous chaque vidéo</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>+2 000 QCM et flashcards interactives</li>
<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->

</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%">

<!-- wp:group {"className":"video-wrapper","style":{"border":{"radius":"12px"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group video-wrapper" style="border-radius:12px">

<!-- wp:embed {"url":"https://youtu.be/02uydthbrlE","type":"video","providerNameSlug":"youtube","responsive":true} -->
<figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube"><div class="wp-block-embed__wrapper">
https://youtu.be/02uydthbrlE
</div></figure>
<!-- /wp:embed -->

</div>
<!-- /wp:group -->

</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->
