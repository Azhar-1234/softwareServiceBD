<?php
/**
 * Resources page: free tools, whitepapers and recommended stack groups.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

return array(
	'filters' => array(
		array( 'key' => 'all', 'label' => 'All' ),
		array( 'key' => 'guides', 'label' => 'Guides & Blogs' ),
		array( 'key' => 'tools', 'label' => 'Business Tools' ),
		array( 'key' => 'stack', 'label' => 'Recommended Stack' ),
	),

	'free_tools' => array(
		array( 'title' => 'Website Cost Calculator', 'desc' => 'Estimate your web app or WordPress budget in 30 seconds.', 'cta' => 'Launch Calculator', 'icon' => 'dollar' ),
		array( 'title' => 'Hosting Comparison Tool', 'desc' => 'Compare hosting providers by speed, price and reliability.', 'cta' => 'Compare Now', 'icon' => 'server' ),
		array( 'title' => 'SEO Checklist', 'desc' => 'A practical checklist to audit your site’s technical SEO.', 'cta' => 'Get Checklist', 'icon' => 'check-chart' ),
		array( 'title' => 'ROI Calculator', 'desc' => 'Estimate the return on a new website or automation project.', 'cta' => 'Calculate ROI', 'icon' => 'chart' ),
		array( 'title' => 'Image Compressor', 'desc' => 'Compress images for faster page load without quality loss.', 'cta' => 'Compress Images', 'icon' => 'image' ),
		array( 'title' => 'Meta Tag Generator', 'desc' => 'Generate clean meta titles and descriptions for SEO.', 'cta' => 'Generate Tags', 'icon' => 'lines' ),
	),

	'whitepapers' => array(
		array( 'tag' => 'Case Study', 'title' => 'How to Automate E-Commerce Operations with AI', 'desc' => 'Connecting Facebook, WhatsApp, WooCommerce and payments into one automated workflow.' ),
		array( 'tag' => 'Whitepaper', 'title' => 'Choosing Between WordPress and Laravel for Growth', 'desc' => 'A framework for matching your platform choice to your growth stage.' ),
		array( 'tag' => 'Guide', 'title' => 'A Business Owner’s Guide to Hosting Decisions', 'desc' => 'What actually matters when picking hosting for a growing business.' ),
		array( 'tag' => 'Case Study', 'title' => 'Scaling a Multi-Vendor Marketplace on Laravel', 'desc' => 'How we structured vendor and product management for a growing marketplace.' ),
		array( 'tag' => 'Whitepaper', 'title' => 'CRM vs ERP: What Growing Businesses Actually Need', 'desc' => 'A practical comparison to help you choose the right system first.' ),
		array( 'tag' => 'Guide', 'title' => 'A Practical Framework for AI Integration', 'desc' => 'A step-by-step approach to adding AI into existing business workflows.' ),
	),

	'recommended_groups' => array(
		array( 'title' => 'Hosting', 'desc' => 'Hosting for WordPress, Laravel and business websites.', 'why' => 'Our top pick for high-performance Laravel apps.', 'cta' => 'Explore Hosting' ),
		array( 'title' => 'SaaS', 'desc' => 'Business tools for productivity, CRM, marketing and operations.', 'why' => 'Tools that hold up at real business scale, not just demos.', 'cta' => 'Explore SaaS Tools' ),
		array( 'title' => 'AI Tools', 'desc' => 'AI tools for content, automation and business workflows.', 'why' => 'Proven in our own AI integration projects.', 'cta' => 'Explore AI Tools' ),
		array( 'title' => 'WordPress', 'desc' => 'Plugins and tools for WordPress websites.', 'why' => 'What gets us to fast, maintainable production builds.', 'cta' => 'Explore WordPress Tools' ),
		array( 'title' => 'Marketing', 'desc' => 'SEO, analytics and digital marketing tools.', 'why' => 'What we use to track and report real campaign results.', 'cta' => 'Explore Marketing Tools' ),
	),

	'front_cards' => array(
		array( 'title' => 'Hosting & Cloud Infrastructure', 'desc' => 'Reliable cloud servers, VPS, and fast managed WordPress hosting.', 'page' => 'tools', 'icon' => 'server', 'badges' => array( 'Vultr', 'Hetzner', 'DigitalOcean' ) ),
		array( 'title' => 'SaaS & Automation Tools', 'desc' => 'Productivity tools and workflow automation tools for scaling.', 'page' => 'tools', 'icon' => 'sparkle', 'badges' => array( 'Make', 'Zapier', 'Workspace' ) ),
		array( 'title' => 'AI Development Tools', 'desc' => 'Top-tier AI API tools, model integrations, and prompt tools.', 'page' => 'tools', 'icon' => 'stars', 'badges' => array( 'OpenAI', 'Claude', 'Pinecone' ) ),
		array( 'title' => 'Free Client Guides & Specs', 'desc' => 'Free project planning checklists, budget tools, and tech guides.', 'page' => 'resources', 'icon' => 'doc', 'badges' => array( 'Planning Guides', 'Tech Stack Matrix' ) ),
	),
);
