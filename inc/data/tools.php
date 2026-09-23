<?php
/**
 * Tools page: curated stack groups and quick comparison links.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

return array(
	'groups' => array(
		array(
			'title' => 'Hosting',
			'count' => '3 picks',
			'items' => array(
				array( 'name' => 'Managed WP hosting', 'desc' => 'Our default for client sites under heavy traffic' ),
				array( 'name' => 'Cloud VPS', 'desc' => 'For Laravel apps that need real control' ),
				array( 'name' => 'CDN & security', 'desc' => 'Caching, WAF and DDoS protection' ),
			),
		),
		array(
			'title' => 'WordPress & SEO',
			'count' => '3 picks',
			'items' => array(
				array( 'name' => 'Page builder', 'desc' => 'What we build production themes with' ),
				array( 'name' => 'SEO suite', 'desc' => 'Schema, sitemaps and content scoring' ),
				array( 'name' => 'Caching plugin', 'desc' => 'What gets us to 95+ PageSpeed' ),
			),
		),
		array(
			'title' => 'AI & SaaS',
			'count' => '3 picks',
			'items' => array(
				array( 'name' => 'AI writing & research', 'desc' => 'Drafting and keyword clustering at scale' ),
				array( 'name' => 'Analytics', 'desc' => 'Privacy-friendly, no cookie banner needed' ),
				array( 'name' => 'Email marketing', 'desc' => 'Automation flows for service businesses' ),
			),
		),
	),

	'quick_comparisons' => array(
		'Hostinger vs Cloudways',
		'WordPress vs Laravel',
		'Shopify vs WooCommerce',
		'Semrush vs Ahrefs',
		'Top CRM Platforms',
		'Top AI Chatbot Tools',
	),
);
