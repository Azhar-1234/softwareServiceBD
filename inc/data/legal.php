<?php
/**
 * Default copy for the legal pages, carried over from the design source.
 *
 * The template renders the WordPress page content when there is any, and
 * falls back to this so the pages are never blank on a fresh install.
 * Editing the page in wp-admin overrides everything here.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

return array(
	'privacy-policy' => array(
		'updated' => 'August 2026',
		'intro'   => 'Software Service BD ("we", "us", "our") respects your privacy. This policy explains what information we collect through this website and how we use it.',
		'sections' => array(
			array(
				'title' => 'Information We Collect',
				'body'  => 'When you submit a project inquiry or contact form, we collect the information you provide — such as your name, email, phone number and project details. We may also collect basic analytics data about how visitors use this website.',
			),
			array(
				'title' => 'How We Use Information',
				'body'  => 'We use the information you provide to respond to inquiries, discuss project requirements and provide our services. We do not sell your personal information to third parties.',
			),
			array(
				'title' => 'Affiliate & Third-Party Links',
				'body'  => 'Some pages on this website contain affiliate links to third-party products and services. Clicking these links may take you to external websites with their own privacy practices, which we do not control.',
			),
			array(
				'title' => 'Contact',
				'body'  => 'If you have questions about this policy, contact us at {email}.',
			),
		),
	),

	'terms' => array(
		'updated' => 'August 2026',
		'intro'   => 'By using this website or engaging Software Service BD for services, you agree to the following terms.',
		'sections' => array(
			array(
				'title' => 'Services',
				'body'  => 'Software Service BD provides web development, custom software, AI automation, eCommerce, SEO and digital marketing services. Specific project scope, timelines and pricing are agreed separately with each client in writing.',
			),
			array(
				'title' => 'Website Content',
				'body'  => 'Content on this website — including guides, comparisons and free tools — is provided for general informational purposes. We make reasonable efforts to keep it accurate but do not guarantee completeness or that it applies to your specific situation.',
			),
			array(
				'title' => 'Third-Party Links',
				'body'  => 'This website may link to third-party products, services and tools, including affiliate links. We are not responsible for the content, accuracy or practices of external websites.',
			),
			array(
				'title' => 'Limitation of Liability',
				'body'  => 'Software Service BD is not liable for indirect or consequential damages arising from use of this website or its content.',
			),
			array(
				'title' => 'Contact',
				'body'  => 'Questions about these terms can be sent to {email}.',
			),
		),
	),

	'affiliate-disclosure' => array(
		'updated' => 'August 2026',
		'intro'   => 'Some links on Software Service BD — including links in our Resources, Comparisons and Tools sections — are affiliate links.',
		'sections' => array(
			array(
				'title' => 'What This Means',
				'body'  => 'If you click an affiliate link and make a purchase, we may earn a commission at no additional cost to you. This helps support the free guides, tools and content we publish.',
			),
			array(
				'title' => 'Our Approach',
				'body'  => 'We recommend tools, hosting and services based on features, value, performance and suitability — not solely on commission rate. We only list tools we would genuinely consider or use ourselves on client projects.',
			),
			array(
				'title' => 'Your Trust Matters',
				'body'  => 'The price you pay never changes because you used our link. If our opinion of a product changes, we update our recommendations accordingly.',
			),
			array(
				'title' => 'Questions',
				'body'  => 'If you have questions about a specific recommendation, contact us at {email}.',
			),
		),
	),
);
