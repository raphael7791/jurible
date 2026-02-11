<?php
/**
 * Title: Structure 07 - Catalogue Matieres
 * Slug: jurible/structure-07-catalogue-matieres
 * Categories: structure
 * Description: Section catalogue cours avec onglets par niveau (L1, L2, L3, etc.)
 */
?>
<!-- wp:group {"align":"full","className":"section-catalogue","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"0","right":"0"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull section-catalogue has-white-background-color has-background" style="padding-top:var(--wp--preset--spacing--xl);padding-right:0;padding-bottom:var(--wp--preset--spacing--xl);padding-left:0">

<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|lg","padding":{"left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignwide" style="padding-right:var(--wp--preset--spacing--md);padding-left:var(--wp--preset--spacing--md)">

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|sm"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
<div class="wp-block-group">

<!-- wp:paragraph {"align":"center","className":"is-style-tag-secondary"} -->
<p class="has-text-align-center is-style-tag-secondary">Nos cours</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","textColor":"text-dark"} -->
<h2 class="wp-block-heading has-text-align-center has-text-dark-color has-text-color">Explore nos cours par <mark style="background:linear-gradient(135deg, #B0001D 0%, #7C3AED 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text" class="has-inline-color">niveau</mark></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"18px","lineHeight":"1.6"}},"textColor":"text-gray"} -->
<p class="has-text-align-center has-text-gray-color has-text-color" style="font-size:18px;line-height:1.6">+20 matieres couvrant tout le programme de la L1 au Master. Videos, fiches, QCM et exercices corriges.</p>
<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->

<!-- wp:html -->
<div class="section-catalogue__tabs">
<button class="section-catalogue__tab active" data-tab="l1">L1 Droit</button>
<button class="section-catalogue__tab" data-tab="l2">L2 Droit</button>
<button class="section-catalogue__tab" data-tab="l3">L3 Droit</button>
<button class="section-catalogue__tab" data-tab="capacite">Capacite</button>
<button class="section-catalogue__tab" data-tab="master">Master</button>
</div>
<!-- /wp:html -->

<!-- wp:html -->
<div class="section-catalogue__panel section-catalogue__panel--l1 active">
<!-- /wp:html -->

<!-- wp:group {"className":"section-catalogue__grid","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"left"}} -->
<div class="wp-block-group section-catalogue__grid">

<!-- wp:jurible/card-cours {"badgeIcon":"🔥","showBadge":true,"title":"Droit constitutionnel","videosCount":32,"fichesCount":45,"qcmCount":120,"flashcardsCount":80,"annalesCount":15,"mindmapsCount":8,"linkUrl":"/cours/droit-constitutionnel"} /-->

<!-- wp:jurible/card-cours {"title":"Introduction au droit","videosCount":28,"fichesCount":38,"qcmCount":95,"flashcardsCount":60,"linkUrl":"/cours/introduction-au-droit"} /-->

<!-- wp:jurible/card-cours {"badgeIcon":"🔥","showBadge":true,"title":"Droit de la famille","videosCount":25,"fichesCount":35,"qcmCount":80,"mindmapsCount":5,"linkUrl":"/cours/droit-de-la-famille"} /-->

</div>
<!-- /wp:group -->

<!-- wp:html -->
</div>
<!-- /wp:html -->

<!-- wp:html -->
<div class="section-catalogue__panel section-catalogue__panel--l2">
<!-- /wp:html -->

<!-- wp:group {"className":"section-catalogue__grid","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"left"}} -->
<div class="wp-block-group section-catalogue__grid">

<!-- wp:jurible/card-cours {"badgeIcon":"🔥","showBadge":true,"title":"Droit des obligations","videosCount":40,"fichesCount":52,"qcmCount":150,"flashcardsCount":90,"linkUrl":"/cours/droit-des-obligations"} /-->

<!-- wp:jurible/card-cours {"title":"Droit administratif","videosCount":35,"fichesCount":48,"qcmCount":130,"flashcardsCount":70,"linkUrl":"/cours/droit-administratif"} /-->

<!-- wp:jurible/card-cours {"title":"Droit penal","videosCount":30,"fichesCount":42,"qcmCount":110,"mindmapsCount":6,"linkUrl":"/cours/droit-penal"} /-->

</div>
<!-- /wp:group -->

<!-- wp:html -->
</div>
<!-- /wp:html -->

<!-- wp:html -->
<div class="section-catalogue__panel section-catalogue__panel--l3">
<!-- /wp:html -->

<!-- wp:group {"className":"section-catalogue__grid","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"left"}} -->
<div class="wp-block-group section-catalogue__grid">

<!-- wp:jurible/card-cours {"title":"Droit des societes","videosCount":38,"fichesCount":50,"qcmCount":140,"flashcardsCount":85,"linkUrl":"/cours/droit-des-societes"} /-->

<!-- wp:jurible/card-cours {"title":"Droit international public","videosCount":32,"fichesCount":45,"qcmCount":100,"linkUrl":"/cours/droit-international-public"} /-->

<!-- wp:jurible/card-cours {"title":"Droit du travail","videosCount":36,"fichesCount":48,"qcmCount":120,"mindmapsCount":7,"linkUrl":"/cours/droit-du-travail"} /-->

</div>
<!-- /wp:group -->

<!-- wp:html -->
</div>
<!-- /wp:html -->

<!-- wp:html -->
<div class="section-catalogue__panel section-catalogue__panel--capacite">
<!-- /wp:html -->

<!-- wp:group {"className":"section-catalogue__grid","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"left"}} -->
<div class="wp-block-group section-catalogue__grid">

<!-- wp:jurible/card-cours {"title":"Droit civil - Capacite","videosCount":24,"fichesCount":30,"qcmCount":80,"linkUrl":"/cours/droit-civil-capacite"} /-->

<!-- wp:jurible/card-cours {"title":"Droit constitutionnel - Capacite","videosCount":20,"fichesCount":28,"qcmCount":70,"linkUrl":"/cours/droit-constitutionnel-capacite"} /-->

</div>
<!-- /wp:group -->

<!-- wp:html -->
</div>
<!-- /wp:html -->

<!-- wp:html -->
<div class="section-catalogue__panel section-catalogue__panel--master">
<!-- /wp:html -->

<!-- wp:group {"className":"section-catalogue__grid","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"left"}} -->
<div class="wp-block-group section-catalogue__grid">

<!-- wp:jurible/card-cours {"title":"Droit des affaires","videosCount":42,"fichesCount":55,"qcmCount":160,"flashcardsCount":100,"linkUrl":"/cours/droit-des-affaires"} /-->

<!-- wp:jurible/card-cours {"title":"Droit europeen","videosCount":38,"fichesCount":50,"qcmCount":130,"linkUrl":"/cours/droit-europeen"} /-->

<!-- wp:jurible/card-cours {"title":"Contentieux administratif","videosCount":35,"fichesCount":45,"qcmCount":120,"mindmapsCount":8,"linkUrl":"/cours/contentieux-administratif"} /-->

</div>
<!-- /wp:group -->

<!-- wp:html -->
</div>
<!-- /wp:html -->

</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->
