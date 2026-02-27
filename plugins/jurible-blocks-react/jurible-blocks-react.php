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
}
add_action( 'init', 'jurible_blocks_react_init' );

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
