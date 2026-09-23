<?php
/**
 * React island plumbing.
 *
 * An island is a server-rendered chunk of real HTML wrapped in a mount point.
 * React attaches to it after load to add interactivity. The rule the whole
 * theme follows: nothing that matters for SEO or GEO may exist only in JS —
 * PHP always renders the complete content first, and React only changes how
 * that content is revealed (filtered, collapsed, toggled).
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Open an island wrapper.
 *
 * @param string $name  Component name registered in assets/js/src/islands.
 * @param array  $props Props serialized to the mount point.
 * @param array  $args  'tag', 'class', 'id'.
 */
function ssbd_island_open( $name, $props = array(), $args = array() ) {
	$args = wp_parse_args( $args, array(
		'tag'   => 'div',
		'class' => '',
		'id'    => '',
	) );

	$tag = preg_replace( '/[^a-z]/', '', strtolower( $args['tag'] ) ) ?: 'div';

	printf(
		'<%1$s data-ssbd-island="%2$s" data-ssbd-props="%3$s"%4$s%5$s>',
		esc_attr( $tag ),
		esc_attr( $name ),
		esc_attr( wp_json_encode( $props ) ),
		$args['class'] ? ' class="' . esc_attr( $args['class'] ) . '"' : '',
		$args['id'] ? ' id="' . esc_attr( $args['id'] ) . '"' : ''
	);
}

/**
 * Close an island wrapper.
 *
 * @param string $tag Tag used when opening.
 */
function ssbd_island_close( $tag = 'div' ) {
	$tag = preg_replace( '/[^a-z]/', '', strtolower( $tag ) ) ?: 'div';
	printf( '</%s>', esc_attr( $tag ) );
}

/**
 * Render an island with a server-rendered body.
 *
 * @param string   $name     Component name.
 * @param array    $props    Props.
 * @param callable $fallback Renders the crawlable HTML inside the island.
 * @param array    $args     See ssbd_island_open().
 */
function ssbd_island( $name, $props, callable $fallback, $args = array() ) {
	$args = wp_parse_args( $args, array( 'tag' => 'div' ) );

	ssbd_island_open( $name, $props, $args );
	call_user_func( $fallback );
	ssbd_island_close( $args['tag'] );
}
