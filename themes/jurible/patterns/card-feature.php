<?php
/**
 * Title: Card Feature
 * Slug: jurible/card-feature
 * Description: Card avec icône, titre et liste
 * Categories: jurible-components, cards
 * Viewport Width: 400
 * Inserter: true
 */
?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|m","bottom":"var:preset|spacing|m","left":"var:preset|spacing|m","right":"var:preset|spacing|m"},"blockGap":"var:preset|spacing|s"},"border":{"radius":"16px"}},"backgroundColor":"muted","layout":{"type":"constrained"},"className":"card-feature"} -->
<div class="wp-block-group card-feature has-muted-background-color has-background" style="border-radius:16px;padding-top:var(--wp--preset--spacing--m);padding-right:var(--wp--preset--spacing--m);padding-bottom:var(--wp--preset--spacing--m);padding-left:var(--wp--preset--spacing--m)">
    
    <!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|s","bottom":"var:preset|spacing|s","left":"var:preset|spacing|s","right":"var:preset|spacing|s"}},"border":{"radius":"12px"},"layout":{"selfStretch":"fit"}},"backgroundColor":"white","layout":{"type":"constrained"},"className":"icon-box"} -->
    <div class="wp-block-group icon-box has-white-background-color has-background" style="border-radius:12px;padding-top:var(--wp--preset--spacing--s);padding-right:var(--wp--preset--spacing--s);padding-bottom:var(--wp--preset--spacing--s);padding-left:var(--wp--preset--spacing--s)">
        <!-- wp:paragraph {"style":{"typography":{"fontSize":"24px"}}} -->
        <p style="font-size:24px">⛔</p>
        <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->
    
    <!-- wp:heading {"level":3,"fontSize":"h4"} -->
    <h3 class="wp-block-heading has-h-4-font-size">Titre de la card</h3>
    <!-- /wp:heading -->
    
    <!-- wp:list {"className":"list-bullet-primary"} -->
    <ul class="list-bullet-primary">
        <!-- wp:list-item -->
        <li>Premier élément de la liste</li>
        <!-- /wp:list-item -->
        
        <!-- wp:list-item -->
        <li>Deuxième élément</li>
        <!-- /wp:list-item -->
        
        <!-- wp:list-item -->
        <li>Troisième élément</li>
        <!-- /wp:list-item -->
    </ul>
    <!-- /wp:list -->
    
</div>
<!-- /wp:group -->
