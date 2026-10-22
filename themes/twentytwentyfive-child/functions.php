<?php
/**
 * Twenty Twenty-Five Child Theme functions and definitions.
 *
 * Stage: 1F.1 - Design System Foundation
 *
 * @package TwentyTwentyFiveChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Enqueue child theme stylesheet.
 */
function twentytwentyfive_child_enqueue_styles() {
	wp_enqueue_style(
		'twentytwentyfive-child-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'twentytwentyfive_child_enqueue_styles' );

/**
 * Declare WooCommerce theme support and essential gallery features.
 */
function twentytwentyfive_child_setup() {
	// Add core WooCommerce support
	add_theme_support( 'woocommerce' );

	// Add product gallery features for modern single product experience
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'twentytwentyfive_child_setup' );
