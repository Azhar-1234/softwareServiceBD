<?php
/**
 * Block patterns for the parts of a page a site owner builds in the editor.
 *
 * The service pages are plain wp-admin content now — see the note in
 * single-ssbd_service.php about ssbd_service_landing_pages — which is right
 * for letting a non-developer control every word on the page, but it leaves
 * them with nothing but plain paragraphs to build a marketing page out of.
 * A media-and-copy row, laid out properly with the image sharing space with
 * the text instead of the two stacking with empty gutters either side, is
 * exactly the piece missing. These two patterns are that row, built from
 * WordPress's own Media & Text block so nothing here is unfamiliar to edit:
 * swap the image, change the heading, and the layout is already the theme's.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the pattern category these live under, so they group together in
 * the inserter instead of scattering into WordPress's own default patterns.
 */
function ssbd_register_pattern_category() {
	register_block_pattern_category( 'ssbd', array(
		'label' => __( 'Software Service BD', 'ssbd' ),
	) );
}
add_action( 'init', 'ssbd_register_pattern_category' );

/**
 * One "Media & Text" row: an image beside a heading, a paragraph and a short
 * bullet list, in the theme's own type and colour.
 *
 * @param string $media_position 'left' or 'right'.
 * @return string Block markup.
 */
function ssbd_media_row_pattern( $media_position ) {
	$is_right = 'right' === $media_position;
	$classes  = 'wp-block-media-text alignwide is-stacked-on-mobile' . ( $is_right ? ' has-media-on-the-right' : '' );
	$attrs    = $is_right ? '{"mediaPosition":"right","align":"wide"}' : '{"align":"wide"}';

	return <<<HTML
<!-- wp:media-text {$attrs} -->
<div class="{$classes}"><figure class="wp-block-media-text__media"></figure><div class="wp-block-media-text__content">
<!-- wp:heading -->
<h2 class="wp-block-heading">What this section covers</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>One or two sentences on what this part of the service actually does for the client — written the way you would say it to someone on a call, not a slogan.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list">
<!-- wp:list-item -->
<li>First specific thing included</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Second specific thing included</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Third specific thing included</li>
<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->
</div></div>
<!-- /wp:media-text -->
HTML;
}

/**
 * Register the two patterns.
 */
function ssbd_register_patterns() {
	register_block_pattern( 'ssbd/service-media-left', array(
		'title'       => __( 'Service: Media Left', 'ssbd' ),
		'description' => __( 'A heading, a paragraph and a short list beside an image on the left. Click the empty image area to add your own; everything else edits like normal text.', 'ssbd' ),
		'categories'  => array( 'ssbd' ),
		'content'     => ssbd_media_row_pattern( 'left' ),
	) );

	register_block_pattern( 'ssbd/service-media-right', array(
		'title'       => __( 'Service: Media Right', 'ssbd' ),
		'description' => __( 'The same row with the image on the right — alternate the two down a page the way a services page usually does.', 'ssbd' ),
		'categories'  => array( 'ssbd' ),
		'content'     => ssbd_media_row_pattern( 'right' ),
	) );
}
add_action( 'init', 'ssbd_register_patterns' );
