<?php
/**
 * The front page.
 *
 * The hero is always first. Everything after it comes from the Customizer:
 * Appearance → Customize → Front Page controls which sections appear and in
 * what order. See inc/front-page.php for the registry.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part( 'template-parts/sections/home-hero' );

$ssbd_registry = ssbd_front_page_sections();

foreach ( ssbd_front_page_order() as $ssbd_key ) {
	get_template_part( $ssbd_registry[ $ssbd_key ]['part'] );
}

get_footer();
