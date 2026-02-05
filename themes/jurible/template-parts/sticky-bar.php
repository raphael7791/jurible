<?php
/**
 * Sticky Bar Template
 * Barre promotionnelle fixée en haut du site
 * Configurable via Apparence > Personnaliser > Sticky Bar
 */

$options = jurible_get_sticky_bar_options();

if (!$options['enabled']) {
    return;
}

$variant_class = 'sticky-bar--' . esc_attr($options['variant']);
?>
<div class="sticky-bar <?php echo $variant_class; ?>" id="sticky-bar">
    <div class="sticky-bar__inner">
        <p class="sticky-bar__text"><?php echo esc_html($options['text']); ?></p>
        <a href="<?php echo esc_url($options['button_url']); ?>" class="sticky-bar__btn"><?php echo esc_html($options['button_text']); ?></a>
    </div>
    <?php if ($options['dismissible']) : ?>
        <button class="sticky-bar__close" id="sticky-bar-close" aria-label="<?php esc_attr_e('Fermer', 'jurible'); ?>">✕</button>
    <?php endif; ?>
</div>
