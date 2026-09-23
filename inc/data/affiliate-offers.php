<?php
/**
 * Starter affiliate offers.
 *
 * Seeded as DRAFTS with an empty affiliate link. Nothing here appears on the
 * site until you have joined the programme, pasted your tracking URL, and hit
 * Publish — an offer with no URL would only produce a dead /go/ link.
 *
 * The pros and cons are deliberately real, including the drawbacks. A review
 * with no downsides reads as paid to a human and to a search classifier, and
 * thin affiliate pages are what Google's helpful-content updates remove. Edit
 * these to match your own experience before publishing — that first-hand
 * assessment is the whole reason your version can outrank a review site's.
 *
 * Prices move constantly. Every one below needs checking against the vendor's
 * current pricing page before you publish.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

return array(
	array(
		'name'     => 'Hostinger',
		'slug'     => 'hostinger',
		'vendor'   => 'Hostinger International',
		'category' => 'hosting',
		'price'    => 'From $2.99/mo',
		'rating'   => '4.4',
		'best_for' => 'first websites and small business sites',
		'cta'      => 'Visit Hostinger',
		'summary'  => 'The cheapest credible entry point into managed hosting. Fine until a site outgrows shared resources.',
		'pros'     => array( 'Very low entry price', 'NVMe storage on most plans', 'Free SSL and site migration', 'hPanel is simple for non-technical clients' ),
		'cons'     => array( 'Renewal price is far higher than the intro rate', 'No phone support', 'Shared plans throttle under real traffic' ),
		'features' => array( 'Starting price: $2.99/mo', 'Free SSL: Yes', 'Free migration: Yes', 'Backups: Weekly', 'Support: 24/7 chat' ),
	),
	array(
		'name'     => 'Namecheap',
		'slug'     => 'namecheap',
		'vendor'   => 'Namecheap',
		'category' => 'hosting',
		'price'    => 'Domains from ~$10/yr',
		'rating'   => '4.3',
		'best_for' => 'domain registration',
		'cta'      => 'Check Namecheap',
		'summary'  => 'Where we register client domains. Cheap, predictable, and the DNS panel does not fight you.',
		'pros'     => array( 'Competitive domain pricing', 'Free WHOIS privacy included', 'Clean DNS management', 'Easy transfers in and out' ),
		'cons'     => array( 'Hosting is basic next to specialists', 'Renewal prices climb after year one', 'Upsells during checkout' ),
		'features' => array( 'Starting price: ~$10/yr', 'Free SSL: First year on some plans', 'Free migration: No', 'Backups: Add-on', 'Support: 24/7 chat' ),
	),
	array(
		'name'     => 'Cloudways',
		'slug'     => 'cloudways',
		'vendor'   => 'Cloudways (DigitalOcean)',
		'category' => 'hosting',
		'price'    => 'From $11/mo',
		'rating'   => '4.6',
		'best_for' => 'Laravel apps and growing WooCommerce stores',
		'cta'      => 'Try Cloudways',
		'summary'  => 'Managed cloud without managing a server. Our default once a client outgrows shared hosting.',
		'pros'     => array( 'Managed layer over DigitalOcean, Vultr and AWS', 'Staging and one-click cloning', 'Handles Laravel and WooCommerce properly', 'Pay monthly, no long contract' ),
		'cons'     => array( 'No domain registration or email hosting', 'Costs more than shared hosting', 'Server management still needs some knowledge' ),
		'features' => array( 'Starting price: $11/mo', 'Free SSL: Yes', 'Free migration: One site', 'Backups: Configurable', 'Support: 24/7 chat' ),
	),
	array(
		'name'     => 'DigitalOcean',
		'slug'     => 'digitalocean',
		'vendor'   => 'DigitalOcean',
		'category' => 'hosting',
		'price'    => 'From $6/mo',
		'rating'   => '4.5',
		'best_for' => 'developers deploying their own stack',
		'cta'      => 'Explore DigitalOcean',
		'summary'  => 'Raw VPS with predictable pricing and genuinely good documentation. You run everything yourself.',
		'pros'     => array( 'Flat, predictable pricing', 'Excellent tutorials and docs', 'Full root access', 'Scales without migrating' ),
		'cons'     => array( 'Unmanaged — security, updates and backups are yours', 'No cPanel-style interface', 'Wrong choice for a non-technical client' ),
		'features' => array( 'Starting price: $6/mo', 'Free SSL: Self-managed', 'Free migration: No', 'Backups: Paid add-on', 'Support: Ticket' ),
	),
	array(
		'name'     => 'Kinsta',
		'slug'     => 'kinsta',
		'vendor'   => 'Kinsta',
		'category' => 'hosting',
		'price'    => 'From $35/mo',
		'rating'   => '4.7',
		'best_for' => 'high-traffic WordPress sites',
		'cta'      => 'See Kinsta plans',
		'summary'  => 'Premium managed WordPress on Google Cloud. Expensive, and worth it when downtime costs real money.',
		'pros'     => array( 'Google Cloud premium tier infrastructure', 'Support staffed by WordPress engineers', 'Staging, backups and CDN included', 'Consistently fast under load' ),
		'cons'     => array( 'Expensive next to every option above', 'Plans capped by monthly visits', 'WordPress only — no Laravel' ),
		'features' => array( 'Starting price: $35/mo', 'Free SSL: Yes', 'Free migration: Yes', 'Backups: Daily', 'Support: 24/7 expert chat' ),
	),
	array(
		'name'     => 'Elementor',
		'slug'     => 'elementor',
		'vendor'   => 'Elementor',
		'category' => 'wordpress',
		'price'    => 'From $59/yr',
		'rating'   => '4.4',
		'best_for' => 'client sites that need visual editing',
		'cta'      => 'Get Elementor',
		'summary'  => 'The page builder we hand clients when they want to edit layouts without calling us.',
		'pros'     => array( 'Large widget and template library', 'Clients learn it quickly', 'Strong third-party add-on ecosystem', 'Free version is genuinely usable' ),
		'cons'     => array( 'Adds page weight if used carelessly', 'Migrating away later is painful', 'Pro is an annual renewal, not one-off' ),
		'features' => array( 'Starting price: $59/yr', 'Free version: Yes', 'Sites per licence: 1 on entry plan', 'Support: Ticket' ),
	),
	array(
		'name'     => 'WP Rocket',
		'slug'     => 'wp-rocket',
		'vendor'   => 'WP Media',
		'category' => 'wordpress',
		'price'    => 'From $59/yr',
		'rating'   => '4.7',
		'best_for' => 'fast Core Web Vitals wins',
		'cta'      => 'Get WP Rocket',
		'summary'  => 'The caching plugin that gets a slow WordPress site into the green with almost no configuration.',
		'pros'     => array( 'Sensible defaults out of the box', 'Page caching, lazy loading and minification in one', 'Rarely breaks a theme', 'Responsive support' ),
		'cons'     => array( 'No free version', 'Annual renewal to keep updates', 'Overlaps with host-level caching' ),
		'features' => array( 'Starting price: $59/yr', 'Free version: No', 'Sites per licence: 1 on entry plan', 'Support: Ticket' ),
	),
	array(
		'name'     => 'Rank Math',
		'slug'     => 'rank-math',
		'vendor'   => 'Rank Math',
		'category' => 'wordpress',
		'price'    => 'Free / Pro from $79/yr',
		'rating'   => '4.6',
		'best_for' => 'WordPress SEO without switching plugins later',
		'cta'      => 'Try Rank Math',
		'summary'  => 'Does more in its free tier than most competitors do paid, including proper schema controls.',
		'pros'     => array( 'Generous free tier', 'Granular schema settings', 'Tracks multiple keywords per post', 'Imports cleanly from Yoast' ),
		'cons'     => array( 'Dense interface for beginners', 'Some useful features are Pro-only', 'Overlaps with this theme’s built-in SEO layer' ),
		'features' => array( 'Starting price: Free', 'Free version: Yes', 'Sites per licence: Unlimited on free', 'Support: Forum and ticket' ),
	),
	array(
		'name'     => 'Semrush',
		'slug'     => 'semrush',
		'vendor'   => 'Semrush',
		'category' => 'marketing',
		'price'    => 'From $139/mo',
		'rating'   => '4.5',
		'best_for' => 'agencies reporting SEO to clients',
		'cta'      => 'Start a Semrush trial',
		'summary'  => 'Keyword research, competitor analysis and rank tracking in one place. Priced for agencies, not hobbyists.',
		'pros'     => array( 'Very broad keyword and backlink data', 'Site audit catches real technical issues', 'Client-ready reports', 'One of the best-paying affiliate programmes' ),
		'cons'     => array( 'Expensive for a small team', 'Per-plan limits bite quickly', 'Steep learning curve' ),
		'features' => array( 'Starting price: $139/mo', 'Free trial: Yes', 'Best for: Agencies and in-house SEO' ),
	),
	array(
		'name'     => 'Make',
		'slug'     => 'make',
		'vendor'   => 'Make (formerly Integromat)',
		'category' => 'ai',
		'price'    => 'Free / from $9/mo',
		'rating'   => '4.5',
		'best_for' => 'connecting business tools without code',
		'cta'      => 'Explore Make',
		'summary'  => 'What we reach for when a client needs WooCommerce, WhatsApp and a spreadsheet talking to each other.',
		'pros'     => array( 'Visual scenario builder is easy to explain to clients', 'Far more operations per dollar than Zapier', 'Handles branching and loops properly', 'Large integration library' ),
		'cons'     => array( 'Steeper learning curve than Zapier', 'Error handling needs deliberate setup', 'Complex scenarios get hard to debug' ),
		'features' => array( 'Starting price: Free tier', 'Free trial: Yes', 'Best for: Multi-step business automation' ),
	),
);
