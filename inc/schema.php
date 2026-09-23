<?php
/**
 * Structured data.
 *
 * Emits a single connected @graph rather than a pile of unrelated blobs.
 * Every node carries an @id and references its neighbours, which is what
 * lets Google (and answer engines building an entity view of the business)
 * resolve "the publisher of this article" to "this LocalBusiness".
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Stable @id for a graph node.
 *
 * @param string $fragment Fragment name.
 * @return string
 */
function ssbd_schema_id( $fragment ) {
	return home_url( '/' ) . '#' . $fragment;
}

/**
 * The Organization / LocalBusiness node.
 *
 * ProfessionalService is a LocalBusiness subtype, which is the right choice
 * for a service business with a real address and a defined service area.
 *
 * @return array
 */
function ssbd_schema_organization() {
	$site = ssbd_site();

	$node = array(
		'@type'       => array( 'Organization', 'ProfessionalService' ),
		/*
		 * An SEO plugin's own Organization already occupies #organization,
		 * and two nodes under one @id are a single node to a parser. Merging
		 * two different @type values and two sets of properties is not
		 * something to leave to a parser's discretion, so the local-business
		 * detail this node exists for — the address, the coordinates, the
		 * opening hours, the service catalogue, none of which a plugin emits
		 * — takes an identity of its own. Every reference elsewhere in the
		 * graph still points at #organization, which the plugin defines.
		 */
		'@id'         => ssbd_seo_plugin_active()
			? ssbd_schema_id( 'localbusiness' )
			: ssbd_schema_id( 'organization' ),
		'name'        => $site['legal_name'],
		'alternateName' => array_values( array_unique( array_merge(
			array( $site['short_name'] ),
			(array) ( $site['alternate_names'] ?? array() )
		) ) ),
		'url'         => home_url( '/' ),
		'description' => $site['description'],
		'slogan'      => $site['tagline'],
		'email'       => $site['email'],
		'foundingDate' => $site['founded'],
		'priceRange'  => $site['price_range'],
		'logo'        => array(
			'@type'      => 'ImageObject',
			'@id'        => ssbd_schema_id( 'logo' ),
			'url'        => SSBD_URI . '/assets/images/logo-full.png',
			'contentUrl' => SSBD_URI . '/assets/images/logo-full.png',
			'caption'    => $site['legal_name'],
		),
		'image'       => array( '@id' => ssbd_schema_id( 'logo' ) ),
		'knowsLanguage' => $site['languages'],
	);

	if ( ! empty( $site['phone'] ) ) {
		$node['telephone'] = $site['phone'];
	}

	// Address + geo: the local pack reads these.
	$node['address'] = array_filter( array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => $site['address']['street'],
		'addressLocality' => $site['address']['locality'],
		'addressRegion'   => $site['address']['region'],
		'postalCode'      => $site['address']['postcode'],
		'addressCountry'  => $site['address']['country'],
	) );

	if ( ! empty( $site['geo']['latitude'] ) ) {
		$node['geo'] = array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => $site['geo']['latitude'],
			'longitude' => $site['geo']['longitude'],
		);
	}

	$node['areaServed'] = array_map(
		static function ( $area ) {
			return array(
				'@type' => 'Worldwide' === $area ? 'Place' : 'AdministrativeArea',
				'name'  => $area,
			);
		},
		$site['areas_served']
	);

	foreach ( (array) $site['opening_hours'] as $hours ) {
		$node['openingHoursSpecification'][] = array(
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => $hours['days'],
			'opens'     => $hours['opens'],
			'closes'    => $hours['closes'],
		);
	}

	$node['contactPoint'] = array(
		array(
			'@type'             => 'ContactPoint',
			'contactType'       => 'sales',
			'email'             => $site['email'],
			'availableLanguage' => array( 'English', 'Bengali' ),
			'areaServed'        => $site['address']['country'],
		),
	);

	$profiles = array_values( array_filter( (array) $site['social'] ) );
	if ( $profiles ) {
		$node['sameAs'] = $profiles;
	}

	// The service catalogue, so an engine can enumerate what is on offer.
	$node['hasOfferCatalog'] = array(
		'@type'      => 'OfferCatalog',
		'name'       => __( 'Services', 'ssbd' ),
		'itemListElement' => array_map(
			static function ( $service ) {
				return array(
					'@type'       => 'Offer',
					'itemOffered' => array(
						'@type'       => 'Service',
						'name'        => $service['title'],
						'description' => $service['desc'],
						'serviceType' => $service['title'],
						'url'         => ssbd_service_url( $service ),
					),
				);
			},
			ssbd_services()
		),
	);

	return $node;
}

/**
 * The WebSite node, with a SearchAction for sitelinks search.
 *
 * @return array
 */
function ssbd_schema_website() {
	return array(
		'@type'           => 'WebSite',
		'@id'             => ssbd_schema_id( 'website' ),
		'url'             => home_url( '/' ),
		'name'            => ssbd_site( 'legal_name' ),
		'description'     => ssbd_site( 'description' ),
		'publisher'       => array( '@id' => ssbd_schema_id( 'organization' ) ),
		'inLanguage'      => get_bloginfo( 'language' ),
		'potentialAction' => array(
			array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => home_url( '/?s={search_term_string}' ),
				),
				'query-input' => 'required name=search_term_string',
			),
		),
	);
}

/**
 * The WebPage node for the current request.
 *
 * @return array
 */
function ssbd_schema_webpage() {
	$canonical = ssbd_canonical_url();
	$key       = ssbd_current_page_key();
	$answer    = ssbd_answer( $key );

	$type = 'WebPage';
	if ( is_front_page() ) {
		$type = 'WebPage';
	} elseif ( 'contact' === $key ) {
		$type = 'ContactPage';
	} elseif ( 'about' === $key ) {
		$type = 'AboutPage';
	} elseif ( in_array( $key, array( 'services', 'solutions', 'products', 'portfolio', 'resources', 'comparisons', 'tools' ), true ) ) {
		$type = 'CollectionPage';
	} elseif ( is_singular( 'post' ) ) {
		$type = 'ItemPage';
	} elseif ( is_archive() || is_home() ) {
		$type = 'CollectionPage';
	}

	$node = array(
		'@type'      => $type,
		'@id'        => $canonical . '#webpage',
		'url'        => $canonical,
		'name'       => wp_get_document_title(),
		'description' => ssbd_meta_description(),
		'isPartOf'   => array( '@id' => ssbd_schema_id( 'website' ) ),
		'about'      => array( '@id' => ssbd_schema_id( 'organization' ) ),
		'inLanguage' => get_bloginfo( 'language' ),
	);

	// abstract mirrors the visible answer block — the same claim in both
	// the rendered text and the structured data.
	if ( $answer && ! empty( $answer['summary'] ) ) {
		$node['abstract'] = $answer['summary'];
	}

	if ( is_singular() ) {
		$node['datePublished'] = get_the_date( DATE_W3C );
		$node['dateModified']  = get_the_modified_date( DATE_W3C );
		if ( has_post_thumbnail() ) {
			$node['primaryImageOfPage'] = array( '@id' => $canonical . '#primaryimage' );
		}
	}

	$trail = ssbd_breadcrumb_trail();
	if ( count( $trail ) > 1 ) {
		$node['breadcrumb'] = array( '@id' => $canonical . '#breadcrumb' );
	}

	return $node;
}

/**
 * BreadcrumbList node.
 *
 * @return array|null
 */
function ssbd_schema_breadcrumbs() {
	$trail = ssbd_breadcrumb_trail();

	if ( count( $trail ) < 2 ) {
		return null;
	}

	$items = array();
	foreach ( $trail as $i => $crumb ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => $crumb['name'],
			'item'     => $crumb['url'],
		);
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'@id'             => ssbd_canonical_url() . '#breadcrumb',
		'itemListElement' => $items,
	);
}

/**
 * Is the Auto SEO Filler plugin emitting its own structured data graph?
 *
 * @return bool
 */
function ssbd_askaf_schema_active() {
	if ( ! class_exists( 'ASKAF_Schema' ) ) {
		return false;
	}

	$settings = get_option( 'askaf_settings', array() );
	if ( ! is_array( $settings ) ) {
		return false;
	}

	return ! empty( $settings['enable_schema'] );
}

/**
 * FAQPage node built from the page's visible FAQ list.
 *
 * Only emitted when the questions are actually rendered on the page —
 * FAQ markup for invisible content is a structured-data violation.
 *
 * @param array $faqs FAQ pairs.
 * @return array|null
 */
function ssbd_schema_faq( $faqs ) {
	if ( empty( $faqs ) ) {
		return null;
	}

	return array(
		'@type'      => 'FAQPage',
		'@id'        => ssbd_canonical_url() . '#faq',
		'mainEntity' => array_map(
			static function ( $faq ) {
				return array(
					'@type'          => 'Question',
					'name'           => $faq['q'],
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => $faq['a'],
					),
				);
			},
			$faqs
		),
	);
}

/**
 * Article node for single posts.
 *
 * @return array|null
 */
function ssbd_schema_article() {
	if ( ! is_singular( 'post' ) ) {
		return null;
	}

	$canonical = ssbd_canonical_url();

	$node = array(
		'@type'            => 'BlogPosting',
		'@id'              => $canonical . '#article',
		'isPartOf'         => array( '@id' => $canonical . '#webpage' ),
		'mainEntityOfPage' => array( '@id' => $canonical . '#webpage' ),
		'headline'         => wp_html_excerpt( get_the_title(), 110, '' ),
		'description'      => ssbd_meta_description(),
		'datePublished'    => get_the_date( DATE_W3C ),
		'dateModified'     => get_the_modified_date( DATE_W3C ),
		'wordCount'        => str_word_count( wp_strip_all_tags( get_the_content() ) ),
		'inLanguage'       => get_bloginfo( 'language' ),
		/*
		 * Person only when a real one is named. Falling back to the editorial
		 * team while still claiming @type Person would assert a human author
		 * that does not exist — the schema equivalent of signing "admin".
		 */
		'author'           => ssbd_author_is_person()
			? array(
				'@type' => 'Person',
				'name'  => ssbd_author_name(),
				'url'   => get_author_posts_url( (int) get_the_author_meta( 'ID' ) ),
			)
			: array(
				'@type' => 'Organization',
				'name'  => ssbd_author_name(),
				'@id'   => ssbd_schema_id( 'organization' ),
			),
		'publisher'        => array( '@id' => ssbd_schema_id( 'organization' ) ),
	);

	// A per-post GEO answer, if the editor wrote one.
	$answer = get_post_meta( get_the_ID(), '_ssbd_answer_summary', true );
	if ( $answer ) {
		$node['abstract'] = $answer;
	}

	$terms = wp_get_post_terms( get_the_ID(), array( 'category', 'post_tag' ), array( 'fields' => 'names' ) );
	if ( $terms && ! is_wp_error( $terms ) ) {
		$node['keywords'] = implode( ', ', $terms );
	}

	// The node itself is emitted once by ssbd_schema_primary_image(); this is
	// a reference to it, so the two can never describe the same image twice.
	if ( has_post_thumbnail() ) {
		$node['image'] = array( '@id' => $canonical . '#primaryimage' );
	}

	return $node;
}

/**
 * The page's primary image, as its own node in the graph.
 *
 * ssbd_schema_webpage() points primaryImageOfPage at `#primaryimage` for any
 * singular view with a featured image, but the node itself was only ever
 * built inside ssbd_schema_article() — which runs on posts alone. So every
 * Page with a featured image published a reference to a node that was not in
 * the graph. A dangling @id is not a validation error, it is worse: the
 * parser resolves it to nothing and the claim silently means nothing.
 *
 * @return array|null
 */
function ssbd_schema_primary_image() {
	if ( ! is_singular() || ! has_post_thumbnail() ) {
		return null;
	}

	$id  = get_post_thumbnail_id();
	$img = wp_get_attachment_image_src( $id, 'full' );

	if ( ! $img ) {
		return null;
	}

	$node = array(
		'@type'  => 'ImageObject',
		'@id'    => ssbd_canonical_url() . '#primaryimage',
		'url'    => $img[0],
		'width'  => $img[1],
		'height' => $img[2],
	);

	// caption and description carry the words a human wrote about the image —
	// which is the part Google Images has to work with.
	$alt = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
	if ( '' !== trim( $alt ) ) {
		$node['description'] = $alt;
	}

	$caption = (string) wp_get_attachment_caption( $id );
	if ( '' !== trim( $caption ) ) {
		$node['caption'] = $caption;
	}

	return $node;
}

/**
 * ItemList node describing the portfolio, used on the portfolio page.
 *
 * @return array|null
 */
function ssbd_schema_portfolio() {
	if ( 'portfolio' !== ssbd_current_page_key() ) {
		return null;
	}

	$items = array();
	foreach ( ssbd_projects() as $i => $project ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'item'     => array_filter( array(
				'@type'       => 'CreativeWork',
				'name'        => $project['name'],
				'description' => $project['desc'],
				'creator'     => array( '@id' => ssbd_schema_id( 'organization' ) ),
				'url'         => $project['url'] ?: null,
			) ),
		);
	}

	return array(
		'@type'           => 'ItemList',
		'@id'             => ssbd_canonical_url() . '#portfolio',
		'name'            => __( 'Software Service BD portfolio', 'ssbd' ),
		'itemListElement' => $items,
	);
}

/**
 * Collect extra graph nodes registered by a template.
 *
 * Templates call ssbd_schema_add() before get_footer(); this is why the graph
 * is printed in the footer rather than the head.
 *
 * @param array|null $node Node to add, or null to read the queue.
 * @return array
 */
function ssbd_schema_queue( $node = null ) {
	static $queue = array();

	if ( is_array( $node ) ) {
		$queue[] = $node;
	}

	return $queue;
}

/**
 * Register an extra JSON-LD node from a template.
 *
 * @param array|null $node Node.
 */
function ssbd_schema_add( $node ) {
	if ( is_array( $node ) ) {
		ssbd_schema_queue( $node );
	}
}

/**
 * Print the connected @graph.
 */
function ssbd_print_schema() {
	if ( is_404() ) {
		return;
	}

	/*
	 * The structural half of the graph — the nodes that describe the page
	 * rather than the business. An SEO plugin emits all four under the same
	 * @id this theme uses, and two nodes sharing an @id are one node to a
	 * parser. For BreadcrumbList that merge is destructive: one
	 * itemListElement array is appended to the other, so position 3 appears
	 * twice, once carrying an item and once not, and the Rich Results Test
	 * reports the version without it as "Missing field item".
	 *
	 * So when a plugin is handling the page, these stand down — the same
	 * deference the head output already makes in inc/seo.php. What is left is
	 * everything a plugin does not do: the local-business node, the FAQ built
	 * from the per-post meta box, the portfolio list, and whatever a template
	 * queued for itself.
	 */
	$structural = ssbd_seo_plugin_active() || ssbd_askaf_schema_active()
		? array()
		: array(
			ssbd_schema_website(),
			ssbd_schema_webpage(),
			ssbd_schema_breadcrumbs(),
			ssbd_schema_article(),
		);

	$graph = array_filter( array_merge(
		array( ssbd_schema_organization() ),
		$structural,
		array( ssbd_schema_portfolio(), ssbd_schema_primary_image() ),
		ssbd_schema_queue()
	) );

	/** Filter the full JSON-LD graph before output. */
	$graph = apply_filters( 'ssbd_schema_graph', $graph );

	if ( empty( $graph ) ) {
		return;
	}

	$payload = array(
		'@context' => 'https://schema.org',
		'@graph'   => array_values( $graph ),
	);

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}
add_action( 'wp_footer', 'ssbd_print_schema', 100 );

/**
 * Is this custom JSON-LD block something we are willing to print?
 *
 * One rule, used by the editor screen and by the printer below, so that
 * "valid" on the workbench and "printed" on the front end can never disagree.
 * A bare scalar decodes without error but is not structured data, hence the
 * array requirement.
 *
 * @param string $raw Stored JSON.
 * @return bool
 */
function ssbd_jsonld_is_printable( $raw ) {
	$raw = trim( (string) $raw );

	if ( '' === $raw ) {
		return false;
	}

	$decoded = json_decode( $raw, true );

	return ( JSON_ERROR_NONE === json_last_error() && is_array( $decoded ) );
}

/**
 * Print the per-post custom JSON-LD block, if one is set.
 *
 * Kept entirely separate from the connected @graph above. Whatever an editor
 * pastes in — a Product, a Review, an Event, a VideoObject, something the
 * theme has no opinion about — is its own script tag, so a mistake in it
 * cannot corrupt the graph the theme is sure of.
 *
 * The stored text is decoded and re-encoded rather than echoed. That is what
 * makes this safe: a string that is not valid JSON never prints at all, and
 * re-encoding with slashes escaped means a "</script>" inside a value cannot
 * close the tag early.
 */
function ssbd_print_custom_jsonld() {
	if ( ! is_singular() ) {
		return;
	}

	$post_id = get_queried_object_id();

	if ( ! $post_id ) {
		return;
	}

	$where = (string) get_post_meta( $post_id, '_ssbd_custom_jsonld_where', true );

	if ( '' === $where ) {
		$where = 'footer';
	}

	$current = ( 'wp_head' === current_filter() ) ? 'head' : 'footer';

	if ( 'off' === $where || $current !== $where ) {
		return;
	}

	$raw = trim( (string) get_post_meta( $post_id, '_ssbd_custom_jsonld', true ) );

	if ( '' === $raw ) {
		return;
	}

	if ( ! ssbd_jsonld_is_printable( $raw ) ) {
		return;
	}

	$decoded = json_decode( $raw, true );

	printf(
		'<script type="application/ld+json" data-ssbd="custom">%s</script>' . "\n",
		wp_json_encode( $decoded, JSON_UNESCAPED_UNICODE )
	);
}
add_action( 'wp_head', 'ssbd_print_custom_jsonld', 99 );
add_action( 'wp_footer', 'ssbd_print_custom_jsonld', 99 );
