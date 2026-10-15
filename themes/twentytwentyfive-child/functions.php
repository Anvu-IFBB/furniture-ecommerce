<?php
/**
 * Twenty Twenty-Five Child Theme functions and definitions.
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
 * Declare WooCommerce theme support.
 */
function twentytwentyfive_child_setup() {
	add_theme_support( 'woocommerce' );
}
add_action( 'after_setup_theme', 'twentytwentyfive_child_setup' );