<?php
/**
 * Outcome-led solution categories for the Solutions template.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

return array(
	'categories' => array(
		array(
			'title' => 'Build Your Digital Presence',
			'desc'  => 'Launch a professional website or online platform that represents your business and converts visitors into customers.',
			'icon'  => 'globe',
			'items' => array( 'Business Website', 'Corporate Website', 'Landing Page', 'WordPress', 'eCommerce' ),
		),
		array(
			'title' => 'Digitize Your Business',
			'desc'  => 'Replace manual processes with software designed around your business workflow.',
			'icon'  => 'window',
			'items' => array( 'Custom Business Software', 'CRM', 'ERP', 'Inventory Management', 'Employee Management' ),
		),
		array(
			'title' => 'Automate Your Business',
			'desc'  => 'Reduce repetitive work and improve efficiency with AI, automation and connected systems.',
			'icon'  => 'sparkle',
			'items' => array( 'AI Integration', 'AI Chatbot', 'Workflow Automation', 'API Integration', 'WhatsApp/Facebook Automation' ),
		),
		array(
			'title' => 'Sell Online',
			'desc'  => 'Build a complete digital commerce experience for your products and services.',
			'icon'  => 'cart',
			'items' => array( 'WooCommerce', 'Custom eCommerce', 'Payment Integration', 'Order Management', 'Customer Automation' ),
		),
		array(
			'title' => 'Grow Your Online Business',
			'desc'  => 'Improve your visibility, attract the right audience and generate more opportunities online.',
			'icon'  => 'chart',
			'items' => array( 'SEO', 'Content Strategy', 'Digital Marketing', 'Conversion Optimization', 'Analytics' ),
		),
		array(
			'title' => 'Connect Your Systems',
			'desc'  => 'Make your tools work together through reliable integrations and APIs.',
			'icon'  => 'plug',
			'items' => array( 'REST API', 'Payment Gateway', 'CRM Integration', 'Social Media API', 'Third-party Software Integration' ),
		),
	),

	'journey' => array( 'Idea', 'Build', 'Digitize', 'Automate', 'Grow' ),

	'business_types' => array(
		array( 'title' => 'Startups', 'desc' => 'Launch your MVP, website or digital product with a scalable technology foundation.' ),
		array( 'title' => 'Small Businesses', 'desc' => 'Digitize everyday operations, reach more customers and automate repetitive tasks.' ),
		array( 'title' => 'Growing Businesses', 'desc' => 'Connect your systems, improve workflows and scale with custom technology.' ),
		array( 'title' => 'eCommerce Businesses', 'desc' => 'Build, optimize and automate your online sales operation.' ),
	),

	'why_steps' => array(
		array( 'no' => '1', 'title' => 'Understand', 'desc' => 'Understand your workflow.' ),
		array( 'no' => '2', 'title' => 'Design', 'desc' => 'Design the right solution.' ),
		array( 'no' => '3', 'title' => 'Integrate', 'desc' => 'Connect your existing tools.' ),
		array( 'no' => '4', 'title' => 'Scale', 'desc' => 'Grow with your business.' ),
	),
);
