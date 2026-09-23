<?php
/**
 * The comparisons hub: three pillars, and the matchups under them.
 *
 * The site is a tech ecosystem review site, not a general gadget blog. Three
 * pillars — what a website runs on, what makes it work, what grows the
 * business — and nothing outside them. That boundary is the whole SEO
 * argument: a narrow topic covered completely beats a wide one covered
 * thinly, so laptops and VPNs do not belong here however well they convert.
 *
 * Each matchup names the article that answers one search. `post` is the slug
 * of the post that answers it: when that post exists the card links to it,
 * and until then the card shows as planned. Nothing here fabricates a link.
 *
 * `price` and `audience` drive the filter bar. Keep them honest — they are
 * how a reader narrows sixteen comparisons down to the two that apply to them.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

return array(
	'pillars' => array(
		array(
			'key'      => 'infrastructure',
			'title'    => 'Website Infrastructure',
			'desc'     => 'What the site runs on: hosting, domains, email and the server underneath.',
			'icon'     => 'server',
			'offers'   => 'hosting',
			'label'    => 'Hosting & Domains',
		),
		array(
			'key'      => 'functionality',
			'title'    => 'Site Functionality',
			'desc'     => 'What makes the site work: WordPress plugins for SEO, speed, security and building pages.',
			'icon'     => 'code',
			'offers'   => 'wordpress',
			'label'    => 'WP Plugins',
		),
		array(
			'key'      => 'growth',
			'title'    => 'Business Growth & SaaS',
			'desc'     => 'What grows the business: email marketing, analytics, CRM and the AI tools around them.',
			'icon'     => 'chart',
			'offers'   => 'saas',
			'label'    => 'Business SaaS',
		),
	),

	/*
	 * Facet values. 'any' is added by the template, so these are the real
	 * choices only. Price bands are monthly unless a tool is one-off.
	 */
	'facets' => array(
		'price' => array(
			'free'    => 'Free / freemium',
			'budget'  => 'Under $10/mo',
			'mid'     => '$10–$50/mo',
			'premium' => 'Over $50/mo',
		),
		'audience' => array(
			'beginner'  => 'Beginner',
			'business'  => 'Business owner',
			'developer' => 'Developer',
		),
	),

	'matchups' => array(
		// Website Infrastructure.
		array(
			'pillar'   => 'infrastructure',
			'title'    => 'Hostinger vs SiteGround',
			'intent'   => 'Best budget WordPress hosting',
			'post'     => 'hostinger-vs-siteground',
			'price'    => 'budget',
			'audience' => 'beginner',
		),
		array(
			'pillar'   => 'infrastructure',
			'title'    => 'Bluehost vs Namecheap',
			'intent'   => 'Cheapest reliable hosting and domains together',
			'post'     => 'bluehost-vs-namecheap',
			'price'    => 'budget',
			'audience' => 'beginner',
		),
		array(
			'pillar'   => 'infrastructure',
			'title'    => 'Shared vs Cloud vs Managed WordPress',
			'intent'   => 'Which type of hosting do I actually need?',
			'post'     => 'shared-vs-cloud-vs-managed-wordpress-hosting',
			'price'    => 'mid',
			'audience' => 'business',
		),
		array(
			'pillar'   => 'infrastructure',
			'title'    => 'Cloudways vs DigitalOcean',
			'intent'   => 'Managed cloud hosting vs raw VPS for Laravel',
			'post'     => 'cloudways-vs-digitalocean',
			'price'    => 'mid',
			'audience' => 'developer',
		),
		array(
			'pillar'   => 'infrastructure',
			'title'    => 'Kinsta vs Cloudways',
			'intent'   => 'Premium managed WordPress hosting compared',
			'post'     => 'kinsta-vs-cloudways',
			'price'    => 'premium',
			'audience' => 'business',
		),

		// Site Functionality.
		array(
			'pillar'   => 'functionality',
			'title'    => 'Rank Math vs Yoast vs AIOSEO',
			'intent'   => 'Best WordPress SEO plugin',
			'post'     => 'rank-math-vs-yoast-vs-aioseo',
			'price'    => 'free',
			'audience' => 'beginner',
		),
		array(
			'pillar'   => 'functionality',
			'title'    => 'Elementor vs Divi vs Bricks',
			'intent'   => 'Page builder speed and clean code compared',
			'post'     => 'elementor-vs-divi-vs-bricks',
			'price'    => 'mid',
			'audience' => 'business',
		),
		array(
			'pillar'   => 'functionality',
			'title'    => 'WP Rocket vs LiteSpeed Cache',
			'intent'   => 'Fastest WordPress caching plugin',
			'post'     => 'wp-rocket-vs-litespeed-cache',
			'price'    => 'budget',
			'audience' => 'developer',
		),
		array(
			'pillar'   => 'functionality',
			'title'    => 'Wordfence vs Solid Security',
			'intent'   => 'Best WordPress security plugin',
			'post'     => 'wordfence-vs-solid-security',
			'price'    => 'free',
			'audience' => 'business',
		),
		array(
			'pillar'   => 'functionality',
			'title'    => 'Page builder vs custom theme',
			'intent'   => 'Is a page builder worth the performance cost?',
			'post'     => 'page-builder-vs-custom-theme',
			'price'    => 'free',
			'audience' => 'business',
		),

		// Business Growth & SaaS.
		array(
			'pillar'   => 'growth',
			'title'    => 'MailerLite vs Mailchimp vs ConvertKit',
			'intent'   => 'Best email marketing platform for a small business',
			'post'     => 'mailerlite-vs-mailchimp-vs-convertkit',
			'price'    => 'free',
			'audience' => 'business',
		),
		array(
			'pillar'   => 'growth',
			'title'    => 'Hotjar vs Lucky Orange',
			'intent'   => 'Heatmaps and session recording compared',
			'post'     => 'hotjar-vs-lucky-orange',
			'price'    => 'mid',
			'audience' => 'business',
		),
		array(
			'pillar'   => 'growth',
			'title'    => 'HubSpot vs Zoho CRM',
			'intent'   => 'Best CRM for a small sales team',
			'post'     => 'hubspot-vs-zoho-crm',
			'price'    => 'premium',
			'audience' => 'business',
		),
		array(
			'pillar'   => 'growth',
			'title'    => 'Semrush vs Ahrefs',
			'intent'   => 'Which SEO toolkit is worth the subscription?',
			'post'     => 'semrush-vs-ahrefs',
			'price'    => 'premium',
			'audience' => 'developer',
		),
		array(
			'pillar'   => 'growth',
			'title'    => 'Make vs Zapier',
			'intent'   => 'Best automation platform for a small team',
			'post'     => 'make-vs-zapier',
			'price'    => 'budget',
			'audience' => 'business',
		),
	),

	/*
	 * The banner that sends a reader from one pillar to the next. Boundary
	 * cross-selling is where the second and third purchase come from: someone
	 * choosing a host this week needs plugins next week.
	 */
	'cross_sell' => array(
		'infrastructure' => array(
			'eyebrow' => 'Next step',
			'title'   => 'Got the hosting sorted?',
			'text'    => 'A new WordPress site needs four plugins before it needs forty. See which SEO, caching and security plugins are worth installing on day one.',
			'link'    => 'Compare WordPress plugins',
			'target'  => 'functionality',
		),
		'functionality' => array(
			'eyebrow' => 'Next step',
			'title'   => 'Site built and fast?',
			'text'    => 'Then the question becomes traffic and follow-up. Compare the email, analytics and CRM tools that turn visitors into a list you own.',
			'link'    => 'Compare growth tools',
			'target'  => 'growth',
		),
		'growth' => array(
			'eyebrow' => 'Start here',
			'title'   => 'Still choosing a host?',
			'text'    => 'Marketing tools cannot fix a slow site. Start with the hosting comparisons, then come back to the growth stack.',
			'link'    => 'Compare hosting',
			'target'  => 'infrastructure',
		),
	),

	'criteria' => array(
		array( 'title' => 'We use it', 'desc' => 'Every tool here is one we run on our own or client projects.' ),
		array( 'title' => 'Real pricing', 'desc' => 'Renewal price, not the first-year teaser rate.' ),
		array( 'title' => 'Named downsides', 'desc' => 'A review with no cons is an advert. Every tool has both.' ),
		array( 'title' => 'Who it suits', 'desc' => 'The right answer for a beginner is rarely the right one for a developer.' ),
	),

	'faq_note' => 'Some links on this page are affiliate links.',
);
