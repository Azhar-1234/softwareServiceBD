<?php
/**
 * Front page FAQ.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/components/faq', null, array(
	'faqs'    => ssbd_faqs( 'home' ),
	'heading' => ssbd_section_heading( 'faq' ),
) );
