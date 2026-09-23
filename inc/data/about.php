<?php
/**
 * About page content: intro, pillars, values, purpose, process, stack, audiences.
 *
 * The wording here is the About page's real copy, not filler — it is what the
 * page renders and what the GEO layer quotes, so it is written the way a
 * person would say it out loud. Keywords ("software development company in
 * Bangladesh", "WordPress", "Laravel", "AI automation", "SEO") appear where
 * they belong in a sentence and nowhere else; a page that lists them twice
 * reads worse to a human and no better to a crawler.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

return array(
	/*
	 * The opening split section: narrative on the left, the numbers from
	 * inc/data/site.php on the right. Three paragraphs is the limit — this is
	 * the part visitors actually read, and a fourth is where About pages start
	 * repeating themselves.
	 */
	'intro' => array(
		'heading'    => 'Technology that solves business problems, not just tickets',
		'paragraphs' => array(
			'Software Service BD is a software development company in Bangladesh, based in Feni and working with clients across the country and abroad. We started from a simple observation: most businesses do not need more software. They need the right software, built by people who took the time to understand what the business is actually trying to do.',
			'So that is where every project starts. We build business websites, WooCommerce and eCommerce stores, custom Laravel web applications, AI-assisted automation and the technical SEO that makes any of it findable — and we decide what to build only after we understand the workflow it has to fit.',
			'Development, automation and digital growth come from the same team. That is deliberate. You get one group accountable for whether the thing works, gets found, and keeps earning after launch, instead of three vendors who never speak to each other.',
		),
		'cta'        => 'Start a project',
	),

	'pillars' => array(
		array( 'title' => 'Build', 'desc' => 'Business websites, WordPress and WooCommerce stores, and custom web applications — fast, secure and SEO-ready from day one.' ),
		array( 'title' => 'Automate', 'desc' => 'AI integrations, chatbots, API connections and workflow automation that take repetitive work off your team.' ),
		array( 'title' => 'Solve', 'desc' => 'Custom software and Laravel applications — CRM, ERP, dashboards and management systems shaped around how you already work.' ),
		array( 'title' => 'Grow', 'desc' => 'Technical SEO, on-page optimisation and digital marketing that turn a finished site into a source of customers.' ),
	),

	'values' => array(
		array( 'title' => 'Business first', 'desc' => 'We start from the outcome you need — more leads, more sales, fewer hours lost — and choose the technology that gets there. Not the other way round.', 'icon' => 'star' ),
		array( 'title' => 'Transparent scope', 'desc' => 'A written scope, a fixed price and clear milestones before the build starts. You always know what is being made and what it costs.', 'icon' => 'clipboard' ),
		array( 'title' => 'Built to last', 'desc' => 'Clean, maintainable code that is secure by default and quick on real connections, not just on a developer machine.', 'icon' => 'check-chart' ),
		array( 'title' => 'Direct communication', 'desc' => 'You talk to the developer building your project, in Bangla or English, during the build and long after it ships.', 'icon' => 'chat' ),
	),

	/*
	 * Mission and vision. Kept to two blocks and written as commitments we
	 * could be held to, because the alternative — the usual "world-class,
	 * cutting-edge, customer-centric" paragraph — tells a reader nothing.
	 */
	'purpose' => array(
		array(
			'eyebrow' => 'Our mission',
			'title'   => 'Make good technology ordinary for small businesses',
			'body'    => 'The kind of systems that used to belong to large companies — a fast website that ranks, an online store that handles its own orders, software that removes an afternoon of manual entry — should be within reach of a business of any size. Our mission is to move companies off paper and spreadsheets onto systems that genuinely save hours, and to give them an online presence their customers can actually find.',
			'icon'    => 'rocket',
		),
		array(
			'eyebrow' => 'Our vision',
			'title'   => 'Be the technology partner Bangladeshi businesses turn to first',
			'body'    => 'We want to be known for work that is honest about scope, built to modern standards, and still running well years after launch — locally in Feni and Chattogram, nationally, and for the international clients who find us. The measure we care about is simple: the businesses we work with should outgrow their competition, never their software.',
			'icon'    => 'globe',
		),
	),

	'process' => array(
		array( 'no' => '01', 'title' => 'Discover', 'desc' => 'We learn the business, the workflow and the outcome you need before a line of code is written.' ),
		array( 'no' => '02', 'title' => 'Plan', 'desc' => 'You get the technology choice, a written scope and a fixed price broken into milestones.' ),
		array( 'no' => '03', 'title' => 'Build', 'desc' => 'We develop, test and optimise — with progress you can see every week, not only at the end.' ),
		array( 'no' => '04', 'title' => 'Grow', 'desc' => 'After launch: support, monitoring, SEO and the next set of features as the business changes.' ),
	),

	'tech_stack' => array(
		array( 'title' => 'Websites & CMS', 'desc' => 'WordPress • WooCommerce • Elementor • Custom themes and plugins' ),
		array( 'title' => 'Applications', 'desc' => 'Laravel • PHP • MySQL • REST APIs • Custom web applications' ),
		array( 'title' => 'Frontend', 'desc' => 'HTML • CSS • JavaScript • React • Responsive, accessible interfaces' ),
		array( 'title' => 'AI & automation', 'desc' => 'AI APIs • Chatbots • Workflow automation • Third-party integrations' ),
	),

	'audiences' => array(
		array( 'title' => 'Startups', 'desc' => 'Turn an idea into a working product — an MVP web application and a platform that can grow with it.' ),
		array( 'title' => 'Small businesses', 'desc' => 'A professional website, an online store that sells, and fewer hours lost to manual admin.' ),
		array( 'title' => 'Growing companies', 'desc' => 'Custom software and automation for teams that have outgrown spreadsheets and shared inboxes.' ),
		array( 'title' => 'Organisations', 'desc' => 'Websites, portals and management systems for schools, NGOs, associations and institutions.' ),
	),

	'why_us' => array(
		array( 'title' => 'Built for Business ROI', 'desc' => 'We design tools around clear metrics like sales growth, user retention, and speed.', 'icon' => 'chart' ),
		array( 'title' => 'Modern Tech Stack', 'desc' => 'Clean, maintainable codebases written in Laravel and modern WordPress.', 'icon' => 'code' ),
		array( 'title' => 'AI-Powered Workflows', 'desc' => 'Smart automation to cut manual workload and save operational costs.', 'icon' => 'sparkle' ),
		array( 'title' => 'Transparent Scoping', 'desc' => 'Fixed-price plans, weekly progress demos, and reliable post-launch maintenance.', 'icon' => 'clipboard' ),
	),

	'capabilities' => array(
		array( 'title' => 'Fast Execution', 'desc' => 'Weekly demos & structured sprints. Never miss a deadline.', 'icon' => 'bolt' ),
		array( 'title' => 'Clean Code Architecture', 'desc' => 'Scalable Laravel & modern WordPress codebase with zero bloat.', 'icon' => 'code' ),
		array( 'title' => 'Transparent Pricing', 'desc' => 'Fixed-scope project pricing with clear milestones. No hidden fees.', 'icon' => 'dollar' ),
		array( 'title' => 'Direct Support', 'desc' => 'Dedicated developer access via WhatsApp or email post-launch.', 'icon' => 'chat' ),
	),

	'delivery_process' => array(
		array( 'no' => '01', 'title' => 'Discover', 'desc' => 'We understand your business, goals and requirements.' ),
		array( 'no' => '02', 'title' => 'Plan', 'desc' => 'We create the right technology and implementation strategy.' ),
		array( 'no' => '03', 'title' => 'Build', 'desc' => 'We develop, test and optimize your solution.' ),
		array( 'no' => '04', 'title' => 'Grow', 'desc' => 'We provide support, optimization and digital growth guidance.' ),
	),

	'ecosystem' => array( 'Services', 'Products', 'Resources', 'Technology Recommendations' ),
);
