<?php
/**
 * The lede paragraph for each page — which doubles as its GEO/AEO answer.
 *
 * These used to render as a bordered card below the H1, which meant every
 * interior page opened by stating the same idea three times: once in the H1,
 * once in a lede, and once more in the card. The card also used the accent
 * left-border treatment that normally signals an aside, so the page's densest
 * block of text was both the heaviest thing on screen and dressed as a
 * footnote.
 *
 * So they are now simply the lede. That costs nothing for answer engines —
 * they extract from rendered text, not from chrome, and this is still the
 * first prose on the page — while the page reads as one statement instead of
 * three. The question-shaped headings that used to sit on the cards are
 * already carried by each page's FAQ section, which is what emits FAQPage.
 *
 * Writing rules, unchanged:
 *   - 30–55 words. Long enough to stand alone when quoted, short enough to
 *     work as a hero lede.
 *   - Name the entity. "Software Service BD does X", not "We do X" — a
 *     quoted passage has to carry its own subject.
 *   - State facts, do not hedge. An engine cannot quote a maybe.
 *   - Use {placeholders} for anything that also lives in site.php, so the
 *     two can never drift apart.
 *
 * `heading` is retained because inc/seo.php and inc/schema.php read these
 * entries for the meta description and the WebPage `abstract`.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

return array(
	'home' => array(
		'heading' => 'What is Software Service BD?',
		'summary' => '{name} builds high-performance websites, custom business software, AI-powered automation and digital growth programmes for companies in Bangladesh and worldwide. Based in {location}, working with everyone from startups to established eCommerce operators.',
	),

	/*
	 * Read directly by ssbd_meta_description() for the blog/posts page — not
	 * through ssbd_current_page_key(), which maps privacy-policy, terms and
	 * affiliate-disclosure to one shared 'legal' key so they can share one FAQ
	 * list. The blog index used to fall past every other branch and inherit
	 * the site-wide description instead, which read as a duplicate against
	 * itself on every other unset page. See inc/seo.php.
	 */
	'blog' => array(
		'heading' => 'The Software Service BD blog',
		'summary' => "{name}'s blog covers WordPress, hosting, AI automation and digital growth for businesses in Bangladesh and beyond — practical guides and honest tool comparisons, written from projects the team actually ships.",
	),

	'services' => array(
		'heading' => 'What services does Software Service BD offer?',
		'summary' => '{name} offers six services covering the full arc of a digital project: web development, custom software, AI and business automation, eCommerce, API integration, and SEO. Every engagement runs Discover, Plan, Build, Grow — on a fixed scope agreed before anyone writes code.',
	),

	'solutions' => array(
		'heading' => 'What business problems does Software Service BD solve?',
		'summary' => '{name} groups its work by business outcome rather than by technology — building a digital presence, digitising manual operations, automating repetitive work with AI, selling online, and connecting disconnected systems through APIs. Start from the problem you have, not the technology you think you need.',
	),

	'products' => array(
		'heading' => 'What products does Software Service BD build?',
		'summary' => '{name} has three products in development: a WooCommerce Discount Manager for advanced quantity and category pricing, a Business CRM for managing leads and sales, and Laravel Business Manager for business operations. Custom versions and feature requests are available on request.',
	),

	'about' => array(
		'heading' => 'About Software Service BD',
		'summary' => '{name} is a software development company in Bangladesh, based in Feni, building websites, WordPress and WooCommerce stores, custom Laravel applications, AI automation and SEO for businesses here and abroad. Development, automation and digital growth come from one team — so you get a single accountable partner instead of three vendors who never speak to each other.',
	),

	'portfolio' => array(
		'heading' => 'Software Service BD project portfolio',
		'summary' => 'Stores, marketplaces and organisational websites {name} has shipped and still maintains — ansclothes and gobaby on WooCommerce, dccbazar as a Laravel multi-vendor marketplace, and CDS for an education organisation.',
	),

	'contact' => array(
		'heading' => 'How to contact Software Service BD',
		'summary' => '{name} can be reached by email at {email}, on WhatsApp, or through the project inquiry form below. Based in {location}, working with clients in Bangladesh and international markets, and replying to project inquiries {response}.',
	),

	'resources' => array(
		'heading' => 'What is in the Software Service BD resource library?',
		'summary' => 'Free calculators, checklists and whitepapers for scoping a project — website cost and ROI calculators, a hosting comparison tool, an SEO checklist and a meta tag generator — plus guides on platform choice, hosting decisions and AI integration.',
	),

	'comparisons' => array(
		'heading' => 'How Software Service BD compares tools',
		'summary' => '{name} compares hosting, SaaS, AI tools, WordPress plugins, marketing software and business software against four criteria: features, pricing, performance and business fit. Some links are affiliate links, and recommendations are never ranked by commission.',
	),

	'tools' => array(
		'heading' => 'The Software Service BD recommended stack',
		'summary' => 'The hosting, WordPress, SEO, AI and SaaS tools {name} runs on its own client projects — managed WordPress hosting and cloud VPS for Laravel, a production page builder, caching and SEO suites, plus privacy-friendly analytics and email automation.',
	),
);
