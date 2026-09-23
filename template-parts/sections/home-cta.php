<?php
/**
 * Front page closing call to action.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/components/cta', null, array(
	'heading' => ssbd_section_heading( 'cta' ),
	'text'    => ssbd_section_lede( 'cta' ),
) );
