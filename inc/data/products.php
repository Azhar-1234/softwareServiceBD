<?php
/**
 * Products in development, plus the Products page supporting content.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

return array(
	'items' => array(
		array(
			'name'     => 'WooCommerce Discount Manager',
			'desc'     => 'Create advanced quantity, category and product-based discounts for WooCommerce stores.',
			'category' => 'WordPress Plugin',
			'filter'   => 'wordpress',
			'status'   => 'In development',
		),
		array(
			'name'     => 'Business CRM',
			'desc'     => 'Manage leads, customers, sales and business operations from one platform.',
			'category' => 'SaaS / Business Software',
			'filter'   => 'saas',
			'status'   => 'In development',
		),
		array(
			'name'     => 'Laravel Business Manager',
			'desc'     => 'A scalable Laravel application for managing business operations.',
			'category' => 'Laravel Application',
			'filter'   => 'laravel',
			'status'   => 'In development',
		),
	),

	'filters' => array(
		array( 'key' => 'all', 'label' => 'All Products' ),
		array( 'key' => 'wordpress', 'label' => 'WordPress Plugins' ),
		array( 'key' => 'laravel', 'label' => 'Laravel Applications' ),
		array( 'key' => 'saas', 'label' => 'SaaS' ),
		array( 'key' => 'business', 'label' => 'Business Software' ),
		array( 'key' => 'ai', 'label' => 'AI Tools' ),
	),

	'stats' => array(
		array( 'value' => '3', 'label' => 'Products in development' ),
		array( 'value' => '4', 'label' => 'Categories' ),
		array( 'value' => 'WP · Laravel', 'label' => 'Core platforms' ),
		array( 'value' => 'SaaS', 'label' => 'Delivery model' ),
	),

	'benefits' => array(
		array( 'title' => 'Easy to Use', 'desc' => 'Simple interfaces designed for everyday business users.' ),
		array( 'title' => 'Flexible', 'desc' => 'Configure the product around your workflow.' ),
		array( 'title' => 'Scalable', 'desc' => 'Built to support growing businesses.' ),
		array( 'title' => 'Continuously Improved', 'desc' => 'Regular improvements, updates and new features.' ),
	),

	'how_steps' => array(
		array( 'no' => '01', 'title' => 'Choose a Product' ),
		array( 'no' => '02', 'title' => 'Explore Features' ),
		array( 'no' => '03', 'title' => 'Purchase / Request Demo' ),
		array( 'no' => '04', 'title' => 'Setup & Start Using' ),
	),
);
