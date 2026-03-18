<?php
/**
 * Plugin Name:       Jurible Blocks React
 * Description:       Blocs Gutenberg personnalisés pour Jurible (Infobox, Sommaire, Lien Leçon, Bouton, Citation, Flashcards, Assessment)
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Jurible
 * License:           GPL-2.0-or-later
 * Text Domain:       jurible-blocks-react
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/class-contact-api.php';
add_action( 'rest_api_init', [ 'Jurible_Contact_API', 'register_routes' ] );

function jurible_blocks_react_init() {
	register_block_type( __DIR__ . '/build/infobox' );
	register_block_type( __DIR__ . '/build/sommaire' );
	register_block_type( __DIR__ . '/build/lien-lecon' );
	register_block_type( __DIR__ . '/build/bouton' );
	register_block_type( __DIR__ . '/build/citation' );
	register_block_type( __DIR__ . '/build/flashcards' );
	register_block_type( __DIR__ . '/build/assessment' );
	register_block_type( __DIR__ . '/build/alert' );
	register_block_type( __DIR__ . '/build/breadcrumb' );
	register_block_type( __DIR__ . '/build/step-indicator' );
	register_block_type( __DIR__ . '/build/card-cours' );
	register_block_type( __DIR__ . '/build/card-testimonial' );
	register_block_type( __DIR__ . '/build/badge-trust' );
	register_block_type( __DIR__ . '/build/pricing-duration-selector' );
	register_block_type( __DIR__ . '/build/card-formule-reussite' );
	register_block_type( __DIR__ . '/build/card-pricing-suite-ia' );
	register_block_type( __DIR__ . '/build/card-produits-comparatif' );
	register_block_type( __DIR__ . '/build/solution-card' );
	register_block_type( __DIR__ . '/build/newsletter' );
	register_block_type( __DIR__ . '/build/cta-banner' );
	register_block_type( __DIR__ . '/build/method-tabs' );
	// register_block_type( __DIR__ . '/build/footer-accordion' ); // Désactivé - utilise CSS/JS à la place
	register_block_type( __DIR__ . '/build/playlist' );
	register_block_type( __DIR__ . '/build/checkout-included' );
	register_block_type( __DIR__ . '/build/checkout-reassurance' );
	register_block_type( __DIR__ . '/build/checkout-social-proof' );
	register_block_type( __DIR__ . '/build/checkout-testimonial' );
	register_block_type( __DIR__ . '/build/hero-dashboard' );
	register_block_type( __DIR__ . '/build/qcm' );
	register_block_type( __DIR__ . '/build/contact-form' );
	register_block_type( __DIR__ . '/build/article-sommaire' );
}
add_action( 'init', 'jurible_blocks_react_init' );

/**
 * Génère un slug à partir d'un texte de heading (compatible PHP/JS).
 * Utilisé par render.php du sommaire et par le filtre heading ci-dessous.
 */
function jurible_heading_to_slug( $text ) {
	$slug = remove_accents( $text );
	$slug = strtolower( $slug );
	$slug = preg_replace( '/[^a-z0-9]+/', '-', $slug );
	$slug = trim( $slug, '-' );
	return $slug;
}

/**
 * Injecte un id="slug" sur les H2 du contenu (singulier uniquement).
 * Permet aux ancres du sommaire de fonctionner.
 */
function jurible_inject_heading_ids( $block_content, $block ) {
	if ( ! is_singular() ) {
		return $block_content;
	}

	$level = isset( $block['attrs']['level'] ) ? (int) $block['attrs']['level'] : 2;
	if ( 2 !== $level ) {
		return $block_content;
	}

	// Ne pas écraser un id déjà présent
	if ( preg_match( '/\bid=["\']/', $block_content ) ) {
		return $block_content;
	}

	$text = trim( wp_strip_all_tags( $block_content ) );
	if ( '' === $text ) {
		return $block_content;
	}

	$slug = jurible_heading_to_slug( $text );

	// Injecter l'id dans la balise <h2>
	$block_content = preg_replace(
		'/<h2(\s)/',
		'<h2 id="' . esc_attr( $slug ) . '"$1',
		$block_content,
		1
	);

	// Cas <h2> sans attributs
	$block_content = preg_replace(
		'/<h2>/',
		'<h2 id="' . esc_attr( $slug ) . '">',
		$block_content,
		1
	);

	return $block_content;
}
add_filter( 'render_block_core/heading', 'jurible_inject_heading_ids', 10, 2 );

/**
 * Register block category for Jurible blocks
 */
function jurible_blocks_react_register_block_category( $categories ) {
	return array_merge(
		array(
			array(
				'slug'  => 'jurible',
				'title' => __( 'Jurible', 'jurible-blocks-react' ),
				'icon'  => 'welcome-learn-more',
			),
		),
		$categories
	);
}
add_filter( 'block_categories_all', 'jurible_blocks_react_register_block_category', 10, 1 );

/**
 * Register block pattern category
 */
function jurible_blocks_react_register_pattern_category() {
	register_block_pattern_category( 'jurible', array(
		'label' => __( 'Jurible', 'jurible-blocks-react' ),
	) );
}
add_action( 'init', 'jurible_blocks_react_register_pattern_category' );

/**
 * Register block patterns
 */
function jurible_blocks_react_register_patterns() {
	$patterns_dir = __DIR__ . '/patterns/';

	if ( ! is_dir( $patterns_dir ) ) {
		return;
	}

	$pattern_files = glob( $patterns_dir . '*.php' );

	foreach ( $pattern_files as $pattern_file ) {
		$pattern_data = get_file_data( $pattern_file, array(
			'title'       => 'Title',
			'slug'        => 'Slug',
			'description' => 'Description',
			'categories'  => 'Categories',
		) );

		if ( empty( $pattern_data['title'] ) || empty( $pattern_data['slug'] ) ) {
			continue;
		}

		ob_start();
		include $pattern_file;
		$content = ob_get_clean();

		$categories = ! empty( $pattern_data['categories'] )
			? array_map( 'trim', explode( ',', $pattern_data['categories'] ) )
			: array( 'jurible' );

		register_block_pattern(
			$pattern_data['slug'],
			array(
				'title'       => $pattern_data['title'],
				'description' => $pattern_data['description'],
				'content'     => $content,
				'categories'  => $categories,
			)
		);
	}
}
add_action( 'init', 'jurible_blocks_react_register_patterns' );
