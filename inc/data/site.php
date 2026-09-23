<?php
/**
 * Site-wide business facts.
 *
 * Everything the SEO / GEO / schema layer needs about the business lives here
 * so there is exactly one source of truth. Values can be overridden per-install
 * through the `ssbd_site` filter or via the Customizer (see inc/customizer.php).
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

return array(
	'legal_name'   => 'Software Service BD',
	'short_name'   => 'Software Service BD',

	/*
	 * Every way people and search engines write this brand. Google currently
	 * treats "softwareservicebd" as a misspelling of "software service bd"
	 * ("Did you mean:" on the SERP), which means the two strings are not yet
	 * joined into one entity. Listing them as alternateName in the
	 * Organization schema is the explicit signal that they are the same thing.
	 */
	'alternate_names' => array(
		'SoftwareServiceBD',
		'softwareservicebd',
		'Software Service Bangladesh',
		'Software Service BD Feni',
	),
	'tagline'      => 'Build, Automate & Grow Your Business with Technology',
	'description'  => 'Software Service BD builds high-performance websites, custom software, AI-powered automation and digital growth solutions for businesses in Bangladesh and worldwide.',
	'founded'      => '2023',
	'email'        => 'info@softwareservicebd.com',
	'phone'        => '+8801815128784',
	'whatsapp'     => 'https://wa.me/8801815128784',

	// Local GEO signals. Coordinates are Feni, Bangladesh.
	'address'      => array(
		'street'   => '',
		'locality' => 'Feni',
		'region'   => 'Chattogram Division',
		'postcode' => '3900',
		'country'  => 'BD',
	),
	'geo'          => array(
		'latitude'  => '23.0159',
		'longitude' => '91.3976',
		'region'    => 'BD-04', // ISO 3166-2, Chattogram Division.
		'placename' => 'Feni, Bangladesh',
	),
	'areas_served' => array( 'Bangladesh', 'Dhaka', 'Chattogram', 'Feni', 'Worldwide' ),
	'languages'    => array( 'en', 'bn' ),
	'opening_hours' => array(
		array(
			'days'  => array( 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday' ),
			'opens' => '09:00',
			'closes' => '18:00',
		),
	),
	'price_range'  => '$$',

	/*
	 * The canonical human-readable renderings of the facts that appear in
	 * body copy. These exist so that the location and hours are written the
	 * same way everywhere they are visible — inconsistent NAP on a single
	 * page is exactly what local search does not want to see. Anything
	 * needing the structured form reads 'address' / 'opening_hours' instead.
	 */
	'location_label' => 'Feni, Bangladesh',
	'hours_label'    => 'Saturday–Thursday, 09:00–18:00 (GMT+6)',
	'response_time'  => 'within one business day',

	/*
	 * Every value here also becomes a sameAs entry on the Organization schema
	 * (see inc/schema.php), which is how a search engine ties these profiles
	 * to the business as one entity. Only the keys listed in
	 * template-parts/components/social-links.php get a visible icon —
	 * facebook_group is deliberately not one of them, because a second
	 * Facebook glyph beside the first reads as a duplicate rather than a
	 * different destination. It still counts as a profile for schema.
	 */
	'social'       => array(
		'facebook'       => 'https://www.facebook.com/profile.php?id=61593750788165',
		'facebook_group' => 'https://www.facebook.com/groups/1367434245552612/',
		'youtube'        => 'https://www.youtube.com/@SoftwareServicebd',
		'pinterest'      => 'https://www.pinterest.com/3tl7yj39238471a3unuolrwgtqy3la/',
		'linkedin'       => '',
		'github'         => '',
		'x'              => '',
	),

	'capabilities' => array(
		'Web Development',
		'Software Solutions',
		'AI & Automation',
		'eCommerce',
		'SEO & Digital Growth',
	),

	'stats'        => array(
		array( 'value' => '15+', 'label' => 'Projects Shipped' ),
		array( 'value' => '10+', 'label' => 'Happy Clients' ),
		array( 'value' => 'Feni', 'label' => 'Serving BD + International' ),
	),
);
