<?php
/**
 * Newsletter Block - Server-side rendering
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 */

$title       = $attributes['title'] ?? 'Newsletter';
$description = $attributes['description'] ?? '';
$placeholder = $attributes['placeholder'] ?? 'Votre email';
$buttonText  = $attributes['buttonText'] ?? "S'inscrire";
$variant     = $attributes['variant'] ?? 'dark';
$layout      = $attributes['layout'] ?? 'horizontal';

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => "jurible-newsletter jurible-newsletter--{$variant} jurible-newsletter--{$layout}"
]);
?>

<div <?php echo $wrapper_attributes; ?>>
    <?php if ($title) : ?>
        <h4 class="jurible-newsletter__title"><?php echo esc_html($title); ?></h4>
    <?php endif; ?>

    <?php if ($description) : ?>
        <p class="jurible-newsletter__description"><?php echo esc_html($description); ?></p>
    <?php endif; ?>

    <form class="jurible-newsletter__form" action="#" method="post">
        <input
            type="email"
            name="email"
            class="jurible-newsletter__input"
            placeholder="<?php echo esc_attr($placeholder); ?>"
            required
        />
        <button type="submit" class="jurible-newsletter__btn">
            <?php echo esc_html($buttonText); ?>
        </button>
    </form>
</div>
