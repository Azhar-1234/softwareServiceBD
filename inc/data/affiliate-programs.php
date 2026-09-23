<?php
/**
 * A starting directory of affiliate programmes relevant to this site.
 *
 * Reference material for whoever runs the partnerships — not rendered on the
 * front end. Commission figures move, so treat these as the shape of the deal
 * rather than a quote; always confirm in the programme's own dashboard.
 *
 * `network` matters more than it looks: joining through a network (Impact,
 * PartnerStack, ShareASale, CJ) means one payout threshold and one payment
 * method across many vendors, which is the difference between getting paid
 * monthly and waiting to clear five separate $100 minimums.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

return array(
	'hosting' => array(
		array( 'name' => 'Hostinger', 'network' => 'In-house + Impact', 'model' => 'Up to 60% of first term', 'cookie' => '30 days', 'notes' => 'Low entry price suits a Bangladeshi audience; approval is usually quick.' ),
		array( 'name' => 'Cloudways', 'network' => 'In-house', 'model' => 'Flat bounty or recurring', 'cookie' => '90 days', 'notes' => 'Good fit for the Laravel content on this site.' ),
		array( 'name' => 'Kinsta', 'network' => 'In-house', 'model' => 'Bounty + 10% recurring', 'cookie' => '60 days', 'notes' => 'High ticket, low volume. Recurring is the real value.' ),
		array( 'name' => 'SiteGround', 'network' => 'In-house', 'model' => 'Tiered per sale', 'cookie' => '60 days', 'notes' => 'Strict on coupon and brand-bidding terms.' ),
		array( 'name' => 'Namecheap', 'network' => 'ShareASale / Impact', 'model' => 'Percentage per order', 'cookie' => '30 days', 'notes' => 'Domains convert well but pay little; useful as an entry product.' ),
		array( 'name' => 'DigitalOcean', 'network' => 'Impact', 'model' => 'Bounty on qualified signup', 'cookie' => '60 days', 'notes' => 'Pairs naturally with VPS and deployment tutorials.' ),
	),

	'wordpress' => array(
		array( 'name' => 'Elementor', 'network' => 'In-house', 'model' => '~50% first year', 'cookie' => '30 days', 'notes' => 'Requires a real audience at application.' ),
		array( 'name' => 'WP Rocket', 'network' => 'In-house', 'model' => '~20% per sale', 'cookie' => '30 days', 'notes' => 'Fits the performance work this site already writes about.' ),
		array( 'name' => 'Rank Math', 'network' => 'In-house', 'model' => 'Recurring share', 'cookie' => '60 days', 'notes' => 'Natural companion to SEO content.' ),
		array( 'name' => 'Astra / Brainstorm Force', 'network' => 'In-house', 'model' => '~30% recurring', 'cookie' => '60 days', 'notes' => 'Bundle deals convert during sale periods.' ),
	),

	'saas' => array(
		array( 'name' => 'Semrush', 'network' => 'Impact', 'model' => 'Bounty per trial + sale', 'cookie' => '120 days', 'notes' => 'One of the highest payouts in the SEO tool space.' ),
		array( 'name' => 'Ahrefs', 'network' => 'In-house', 'model' => 'Recurring share', 'cookie' => '—', 'notes' => 'Selective; approval needs demonstrated traffic.' ),
		array( 'name' => 'HubSpot', 'network' => 'Impact', 'model' => 'Bounty by tier', 'cookie' => '180 days', 'notes' => 'Long cookie suits slow B2B decisions.' ),
		array( 'name' => 'Monday.com / ClickUp', 'network' => 'PartnerStack', 'model' => 'Recurring share', 'cookie' => '90 days', 'notes' => 'PartnerStack consolidates several SaaS payouts into one.' ),
		array( 'name' => 'Zoho', 'network' => 'In-house', 'model' => 'Percentage per sale', 'cookie' => '90 days', 'notes' => 'Strong in South Asian markets — relevant for BD clients.' ),
	),

	'ai' => array(
		array( 'name' => 'Jasper', 'network' => 'PartnerStack', 'model' => '~25% recurring', 'cookie' => '—', 'notes' => 'Fits AI-automation content.' ),
		array( 'name' => 'Writesonic / Copy.ai', 'network' => 'In-house', 'model' => 'Recurring share', 'cookie' => '60 days', 'notes' => 'Lower ticket, easier approval.' ),
		array( 'name' => 'Make / Zapier', 'network' => 'PartnerStack / In-house', 'model' => 'Bounty or recurring', 'cookie' => '90 days', 'notes' => 'Directly relevant to the automation services sold here.' ),
	),
);
