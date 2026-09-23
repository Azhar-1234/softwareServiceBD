<?php
/**
 * Services — used by the front page grid, the Services hub, the individual
 * service pages, the header dropdown, the Service JSON-LD nodes and the
 * footer navigation.
 *
 * `group` drives the Build / Automate / Grow / Support filter on the front
 * page. `parent` nests a service under another one: it becomes a child page
 * at /services/<parent>/<slug>/ and an item in the header dropdown, and it
 * stays out of the top-level grids.
 *
 * This file is the seed and the fallback. Once services exist in wp-admin
 * they take over — see inc/catalogue.php.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

return array(
	array(
		'no'    => '01',
		'slug'  => 'web-development',
		'title' => 'Web Development',
		'short' => 'Business websites and web applications built on Laravel and WordPress.',
		'desc'  => 'Modern, fast and scalable websites and web applications built for businesses and organizations — from a marketing site to a full custom platform.',
		'group' => 'build',
		'icon'  => 'code',
		'pills' => array( 'Laravel', 'WordPress', 'WooCommerce', 'React' ),
		'items' => array( 'Business Websites', 'Web Applications', 'WooCommerce Stores', 'Website Redesign', 'Performance Optimization' ),
		'cta'   => 'View Web Development',
	),
	array(
		'no'     => '01a',
		'slug'   => 'laravel-development',
		'parent' => 'web-development',
		'title'  => 'Laravel Development',
		'short'  => 'Custom web platforms, dashboards and APIs built on Laravel.',
		'desc'   => 'Laravel applications built for businesses that have outgrown off-the-shelf tools — multi-user platforms, dashboards, portals and the APIs behind them.',
		'group'  => 'build',
		'icon'   => 'code',
		'pills'  => array( 'Laravel', 'MySQL', 'REST APIs', 'Livewire' ),
		'items'  => array( 'Custom Web Applications', 'Admin Dashboards', 'REST APIs', 'Multi-user Portals', 'Database Design' ),
		'cta'    => 'View Laravel Development',
	),
	array(
		'no'     => '01b',
		'slug'   => 'wordpress-development',
		'parent' => 'web-development',
		'title'  => 'WordPress Development',
		'short'  => 'Custom themes, plugins and fast, secure WordPress builds.',
		'desc'   => 'WordPress sites built properly — a custom theme instead of a bloated page builder, only the plugins you need, and a Core Web Vitals score you can be proud of.',
		'group'  => 'build',
		'icon'   => 'window',
		'pills'  => array( 'Custom Themes', 'Plugins', 'WooCommerce', 'Core Web Vitals' ),
		'items'  => array( 'Custom Theme Development', 'Plugin Development', 'Speed Optimization', 'WooCommerce Setup', 'Migration' ),
		'cta'    => 'View WordPress Development',
	),
	array(
		'no'    => '02',
		'slug'  => 'landing-page',
		'title' => 'Landing Page',
		'short' => 'High-converting single pages for campaigns, launches and ads.',
		'desc'  => 'Conversion-focused landing pages for a campaign, a product launch or a paid ad — written, designed and built to turn visitors into enquiries.',
		'group' => 'grow',
		'icon'  => 'image',
		'pills' => array( 'Conversion Copy', 'A/B Ready', 'Fast Loading' ),
		'items' => array( 'Campaign Landing Pages', 'Product Launch Pages', 'Lead Capture Forms', 'Ad Landing Pages', 'Conversion Tracking' ),
		'cta'   => 'View Landing Pages',
	),
	array(
		'no'    => '03',
		'slug'  => 'software-development',
		'title' => 'Software Development',
		'short' => 'Tailored business management tools, dashboards and operational platforms.',
		'desc'  => 'Custom software designed around how your business actually works — inventory, billing, CRM, reporting — instead of bending your process to fit someone else\'s product.',
		'group' => 'build',
		'icon'  => 'window',
		'pills' => array( 'Custom PHP', 'APIs', 'Cloud' ),
		'items' => array( 'Business Software', 'CRM', 'ERP', 'Management Systems', 'Custom Dashboards' ),
		'cta'   => 'Explore Software Development',
	),
	array(
		'no'    => '04',
		'slug'  => 'app-development',
		'title' => 'App Development',
		'short' => 'Mobile and web apps that work with the systems you already run.',
		'desc'  => 'Mobile and progressive web applications for customers or staff, connected to the same data your website and software already use.',
		'group' => 'build',
		'icon'  => 'mobile',
		'pills' => array( 'React Native', 'PWA', 'APIs' ),
		'items' => array( 'Mobile Apps', 'Progressive Web Apps', 'App Backends', 'Push Notifications', 'App Maintenance' ),
		'cta'   => 'Explore App Development',
	),
	array(
		'no'    => '05',
		'slug'  => 'ai-integration',
		'title' => 'AI Integration',
		'short' => 'Reduce manual work with AI assistants, workflows and API connections.',
		'desc'  => 'Put AI where it actually saves time — answering repetitive customer questions, drafting content, sorting enquiries and moving data between the tools you already pay for.',
		'group' => 'automate',
		'icon'  => 'sparkle',
		'pills' => array( 'OpenAI API', 'Claude', 'Zapier', 'Webhooks' ),
		'items' => array( 'AI Chatbots', 'Workflow Automation', 'AI Content Tools', 'API Automation', 'Third-party Integrations' ),
		'cta'   => 'See AI Integration',
	),
	array(
		'no'    => '06',
		'slug'  => 'domain-hosting',
		'title' => 'Domain & Hosting',
		'short' => 'Domain registration, managed hosting, email and SSL — set up and looked after.',
		'desc'  => 'Domain registration, hosting selection and server setup, business email, SSL and backups — configured properly once, then monitored so you never think about it again.',
		'group' => 'support',
		'icon'  => 'server',
		'pills' => array( 'Managed Hosting', 'Business Email', 'SSL', 'Backups' ),
		'items' => array( 'Domain Registration', 'Hosting Setup', 'Business Email', 'SSL Certificates', 'Migration & DNS' ),
		'cta'   => 'View Domain & Hosting',
	),
	array(
		'no'    => '07',
		'slug'  => 'website-maintenance-support',
		'title' => 'Website Maintenance & Support',
		'short' => 'Updates, backups, security and a real person to call when something breaks.',
		'desc'  => 'Monthly care for the site you already have: core and plugin updates, off-site backups, uptime and security monitoring, plus a monthly allowance of content and fix requests.',
		'group' => 'support',
		'icon'  => 'shield',
		'pills' => array( 'Monthly Plans', 'Backups', 'Security', 'Uptime' ),
		'items' => array( 'Core & Plugin Updates', 'Off-site Backups', 'Security Monitoring', 'Uptime Monitoring', 'Content Updates', 'Emergency Fixes' ),
		'cta'   => 'View Maintenance Plans',
	),
);
