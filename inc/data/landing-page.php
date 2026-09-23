<?php
/**
 * Copy for the Landing Page service page.
 *
 * Rendered by template-parts/services/service-page.php.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

return array(
	'frame'        => 'browser',
	'screen_start' => 0,

	'hero' => array(
		'eyebrow'  => 'Landing Page',
		'headline' => 'Landing Pages That Turn Clicks Into Enquiries',
		'lede'     => 'One page, one offer, one action. Written, designed, built and tracked — so the money you spend sending people there stops leaking on arrival.',
		'trust'    => array( 'Conversion Copy', 'Fast Loading', 'Mobile First', 'Analytics', 'A/B Ready' ),
	),

	'build' => array(
		'heading' => 'Landing Pages We Build',
		'lede'    => 'Every one is built around a single decision you want the visitor to make — and nothing else on the page competing for it.',
		'items'   => array(
			array(
				'icon'  => 'bolt',
				'title' => 'Campaign Pages',
				'desc'  => 'For a season, an offer or a launch — live in days, not weeks.',
				'pills' => array( 'Fast build', 'Single offer' ),
			),
			array(
				'icon'  => 'chart',
				'title' => 'Paid Ad Pages',
				'desc'  => 'Message-matched to your Google or Meta ad, so the click does not bounce.',
				'pills' => array( 'Google Ads', 'Meta' ),
			),
			array(
				'icon'  => 'rocket',
				'title' => 'Product Launch Pages',
				'desc'  => 'Waitlist, pre-order or launch-day page with the countdown that matters.',
				'pills' => array( 'Waitlist', 'Pre-order' ),
			),
			array(
				'icon'  => 'doc',
				'title' => 'Lead Magnet Pages',
				'desc'  => 'Guide, checklist or quote form in exchange for a contact detail.',
				'pills' => array( 'Forms', 'Downloads' ),
			),
			array(
				'icon'  => 'chat',
				'title' => 'Conversion Copywriting',
				'desc'  => 'The headline, the objections and the call to action, written for your buyer.',
				'pills' => array( 'Copy', 'Offer' ),
			),
			array(
				'icon'  => 'check-chart',
				'title' => 'Tracking & Analytics',
				'desc'  => 'Events, conversions and forms wired up before launch, not after.',
				'pills' => array( 'GA4', 'Pixels' ),
			),
			array(
				'icon'  => 'lines',
				'title' => 'A/B Testing',
				'desc'  => 'Two versions of the headline or the offer, and a number that decides.',
				'pills' => array( 'Variants', 'Data' ),
			),
			array(
				'icon'  => 'bolt',
				'title' => 'Speed & Core Web Vitals',
				'desc'  => 'A page that loads before the visitor decides to leave — on mobile data.',
				'pills' => array( 'LCP', 'Mobile' ),
			),
		),
	),

	'showcase' => array(
		'heading' => 'Built to Be Read, Then Acted On',
		'lede'    => 'Clear hierarchy, one visible action, and proof placed where the doubt appears — not a long page of decoration.',
		'labels'  => array( 'Campaign page', 'Lead form', 'Thank you' ),
	),

	'process' => array(
		'heading' => 'How We Build It',
		'lede'    => 'Five short stages. Most landing pages are live inside two weeks.',
		'steps'   => array(
			array( 'no' => '01', 'title' => 'Offer & Audience', 'desc' => 'What is being offered, to whom, and what would make them say no.' ),
			array( 'no' => '02', 'title' => 'Copy & Structure', 'desc' => 'The argument in order: headline, proof, objections, action.' ),
			array( 'no' => '03', 'title' => 'Design', 'desc' => 'One page, laid out so the eye reaches the button.' ),
			array( 'no' => '04', 'title' => 'Build & Tracking', 'desc' => 'Fast build, forms connected, conversions measurable from day one.' ),
			array( 'no' => '05', 'title' => 'Test & Improve', 'desc' => 'Watch the real numbers, then change the one thing holding it back.' ),
		),
		'stack'   => array(
			'lede'  => 'Built on the same stack as the rest of your site, so it is one thing to maintain.',
			'items' => array( 'wordpress', 'php', 'javascript', 'ai' ),
		),
	),

	'consultation' => array(
		'heading' => 'Tell Us What You Are Promoting',
		'lede'    => 'Send the offer, the audience and where the traffic comes from. You get a page plan and a fixed price — and an honest answer if the problem is the offer rather than the page.',
		'points'  => array(
			array( 'title' => 'Copy included', 'desc' => 'We write the page. You are not left filling in a template.' ),
			array( 'title' => 'Measurable from day one', 'desc' => 'Conversions tracked, so you know what the traffic is doing.' ),
			array( 'title' => 'Live in days', 'desc' => 'A campaign page should not take a month.' ),
		),
	),

	'faq_heading' => 'Landing Page FAQs',

	'cta' => array(
		'heading' => 'Running Ads to a Weak Page?',
		'text'    => 'Send us the offer and the page you have now. We will tell you what is costing you conversions and what a rebuild would cost.',
		'button'  => 'Start Your Landing Page',
	),

	'faqs' => array(
		array(
			'q' => 'How fast can a landing page go live?',
			'a' => 'Usually within one to two weeks, depending on how quickly the offer and any photography are settled. A campaign page with copy already written can be faster.',
		),
		array(
			'q' => 'Do you write the copy?',
			'a' => 'Yes. The words are most of what makes a landing page work, so writing them is part of the job — not something handed back to you as a blank template.',
		),
		array(
			'q' => 'Can it connect to my ads and analytics?',
			'a' => 'Yes. Conversion tracking for Google Ads and Meta, GA4 events and form notifications are set up before launch, so the first click is already measured.',
		),
		array(
			'q' => 'Where does the page live?',
			'a' => 'Usually on your existing site as its own page, which keeps one domain, one host and one thing to maintain. A separate domain is possible when a campaign needs it.',
		),
		array(
			'q' => 'Can you A/B test it?',
			'a' => 'Yes, once there is enough traffic for the result to mean anything. Below a few hundred conversions a test mostly measures noise, and we will say so rather than sell you one.',
		),
		array(
			'q' => 'What makes a landing page fail?',
			'a' => 'Most often a weak or unclear offer, a mismatch between the ad and the page, slow loading on mobile, or asking for too much information in the form. We check all four before blaming the design.',
		),
	),
);
