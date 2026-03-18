<?php
/**
 * Sommaire Article — rendu dynamique côté serveur.
 *
 * Lit les H2 du post_content via parse_blocks() et génère le HTML.
 */

if ( ! is_singular() ) {
	return;
}

$post = get_post();
if ( ! $post || empty( $post->post_content ) ) {
	return;
}

// --- Extraire les H2 via parse_blocks ---
$blocks   = parse_blocks( $post->post_content );
$headings = array();

/**
 * Parcours récursif des blocs pour trouver les core/heading level 2.
 */
function jurible_article_sommaire_extract_h2( $blocks, &$headings ) {
	foreach ( $blocks as $block ) {
		if (
			'core/heading' === $block['blockName']
			&& isset( $block['attrs']['level'] )
			&& 2 === (int) $block['attrs']['level']
		) {
			// Le contenu est dans innerHTML ; on strip les balises HTML.
			$raw  = isset( $block['innerHTML'] ) ? $block['innerHTML'] : '';
			$text = trim( wp_strip_all_tags( $raw ) );
			if ( '' !== $text ) {
				$headings[] = array(
					'text' => $text,
					'slug' => jurible_heading_to_slug( $text ),
				);
			}
		}
		// core/heading level defaults to 2 when attr not set
		if (
			'core/heading' === $block['blockName']
			&& ! isset( $block['attrs']['level'] )
		) {
			$raw  = isset( $block['innerHTML'] ) ? $block['innerHTML'] : '';
			$text = trim( wp_strip_all_tags( $raw ) );
			if ( '' !== $text ) {
				$headings[] = array(
					'text' => $text,
					'slug' => jurible_heading_to_slug( $text ),
				);
			}
		}
		// Récursion innerBlocks
		if ( ! empty( $block['innerBlocks'] ) ) {
			jurible_article_sommaire_extract_h2( $block['innerBlocks'], $headings );
		}
	}
}
jurible_article_sommaire_extract_h2( $blocks, $headings );

if ( empty( $headings ) ) {
	return;
}

$count        = count( $headings );
$needs_toggle = $count > 5;
$wrapper_class = 'jurible-sommaire';
if ( $needs_toggle ) {
	$wrapper_class .= ' is-collapsed';
}
?>
<nav <?php echo get_block_wrapper_attributes( array( 'class' => $wrapper_class ) ); ?>>
	<div class="jurible-sommaire-header">
		<span class="jurible-sommaire-icon">📑</span>
		<span class="jurible-sommaire-title">Sommaire</span>
	</div>
	<ol class="jurible-sommaire-list">
		<?php foreach ( $headings as $h ) : ?>
			<li><a href="#<?php echo esc_attr( $h['slug'] ); ?>"><?php echo esc_html( $h['text'] ); ?></a></li>
		<?php endforeach; ?>
	</ol>
	<?php if ( $needs_toggle ) : ?>
		<button type="button" class="jurible-sommaire-toggle" data-count="<?php echo esc_attr( $count ); ?>">
			Voir tout le sommaire (<?php echo esc_html( $count ); ?>)
		</button>
	<?php endif; ?>
</nav>
