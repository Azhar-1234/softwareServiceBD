<?php
/**
 * Portfolio projects. Feeds the front-page work strip, the Portfolio
 * template filter island and the CreativeWork JSON-LD nodes.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

return array(
	array(
		'name'    => 'ansclothes',
		'desc'    => 'Fashion eCommerce store with a streamlined catalog and checkout.',
		'tech'    => 'WordPress · WooCommerce',
		'badge'   => 'WooCommerce',
		'tag'     => 'Live Store',
		'filters' => array( 'wordpress', 'ecommerce', 'websites' ),
		'url'     => '',
	),
	array(
		'name'    => 'dccbazar',
		'desc'    => 'Custom marketplace platform with vendor and product management.',
		'tech'    => 'Laravel',
		'badge'   => 'Laravel',
		'tag'     => 'Multi-Vendor',
		'filters' => array( 'laravel', 'software', 'ecommerce' ),
		'url'     => '',
	),
	array(
		'name'    => 'gobaby',
		'desc'    => 'Baby & kids eCommerce store built for a faster, more reliable checkout.',
		'tech'    => 'WordPress · WooCommerce',
		'badge'   => 'WooCommerce',
		'tag'     => 'Speed Optimized',
		'filters' => array( 'wordpress', 'ecommerce', 'websites' ),
		'url'     => '',
	),
	array(
		'name'    => 'CDS',
		'desc'    => 'A fast, accessible website for an education organization.',
		'tech'    => 'WordPress',
		'badge'   => 'WordPress',
		'tag'     => 'Education',
		'filters' => array( 'wordpress', 'websites' ),
		'url'     => '',
	),
);
