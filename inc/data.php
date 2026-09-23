<?php
/**
 * Content data accessors.
 *
 * Every dataset under inc/data/ is loaded once per request and passed through
 * a filter, so a child theme or plugin can replace or extend any section
 * without editing the theme.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load a dataset by name.
 *
 * @param string $name File basename under inc/data/, without extension.
 * @return array
 */
function ssbd_data( $name ) {
	static $cache = array();

	// Normalise the cache key, but resolve the filename against both spellings:
	// the key is used in a filter name (where a hyphen is illegal) while the
	// files themselves are hyphenated where that reads better.
	$key = sanitize_key( str_replace( '-', '_', $name ) );

	if ( ! isset( $cache[ $key ] ) ) {
		$data = array();

		foreach ( array( $key, str_replace( '_', '-', $key ) ) as $candidate ) {
			$file = SSBD_DIR . '/inc/data/' . $candidate . '.php';

			if ( is_readable( $file ) ) {
				$data = require $file;
				break;
			}
		}

		/**
		 * Filter a theme dataset.
		 *
		 * @param array  $data Dataset contents.
		 * @param string $name Dataset name.
		 */
		$cache[ $key ] = apply_filters( "ssbd_data_{$key}", (array) $data, $key );
	}

	return $cache[ $key ];
}

/**
 * How many photographs the About page's opening section can show.
 *
 * Two, because the layout beside a column of copy is either one wide picture
 * or a pair of portraits — a third would have to shrink past the point of
 * being worth loading.
 */
const SSBD_ABOUT_PHOTOS = 2;

/**
 * The photographs shown beside the About page's opening copy.
 *
 * Resolved most specific first: whatever is set in the Customizer under
 * About page, and failing that the images bundled with the theme at
 * assets/images/about-1.webp and about-2.webp. A bundled file that is not
 * there is skipped, so dropping the two files in is all a fresh install needs
 * to get its default pictures, and setting either Customizer control replaces
 * them without anyone editing the theme.
 *
 * Setting one control replaces both defaults rather than mixing a chosen photo
 * with a shipped one: a half-swapped pair is never what someone means by
 * changing the picture.
 *
 * Attachments come back as IDs, not URLs, so the rendered <img> gets
 * WordPress's srcset, the WebP sizes inc/setup.php generates, and the alt text
 * held in the Media Library. A bundled file has none of those, so it carries
 * its own URL and an empty alt — the theme will not invent a description for a
 * photograph it cannot see. Give a bundled image an 'alt' through the filter
 * below, or upload it to the Media Library instead, if it needs one.
 *
 * @return array<array{id?:int,src?:string,alt?:string}> At most two, in order.
 */
function ssbd_about_photos() {
	$photos = array();

	for ( $i = 1; $i <= SSBD_ABOUT_PHOTOS; $i++ ) {
		$id = absint( get_theme_mod( 'ssbd_about_photo_' . $i, 0 ) );

		if ( $id && wp_attachment_is_image( $id ) ) {
			$photos[] = array( 'id' => $id );
		}
	}

	if ( ! $photos ) {
		for ( $i = 1; $i <= SSBD_ABOUT_PHOTOS; $i++ ) {
			$file = '/assets/images/about-' . $i . '.webp';

			if ( file_exists( SSBD_DIR . $file ) ) {
				$photos[] = array(
					'src' => SSBD_URI . $file,
					'alt' => '',
				);
			}
		}
	}

	/**
	 * Filter the About page photographs.
	 *
	 * @param array $photos Each entry an attachment 'id', or a 'src' + 'alt'.
	 */
	return apply_filters( 'ssbd_about_photos', $photos );
}

/**
 * A single site-level business fact, with Customizer override support.
 *
 * @param string $key     Dot path, e.g. 'address.locality'.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function ssbd_site( $key = '', $default = null ) {
	$site = ssbd_data( 'site' );

	// Customizer overrides for the fields a site owner is most likely to change.
	$overrides = array(
		'email'    => get_theme_mod( 'ssbd_email' ),
		'phone'    => get_theme_mod( 'ssbd_phone' ),
		'whatsapp' => get_theme_mod( 'ssbd_whatsapp' ),
	);
	foreach ( $overrides as $field => $value ) {
		if ( ! empty( $value ) ) {
			$site[ $field ] = $value;
		}
	}

	/** Filter the resolved business facts. */
	$site = apply_filters( 'ssbd_site', $site );

	if ( '' === $key ) {
		return $site;
	}

	$value = $site;
	foreach ( explode( '.', $key ) as $segment ) {
		if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
			return $default;
		}
		$value = $value[ $segment ];
	}

	return $value;
}

/**
 * FAQs for a page key.
 *
 * @param string $key Page key, e.g. 'services'.
 * @return array<array{q:string,a:string}>
 */
function ssbd_faqs( $key ) {
	$all = ssbd_data( 'faqs' );
	return isset( $all[ $key ] ) ? $all[ $key ] : array();
}

/**
 * Resolve {placeholders} in answer copy against the business facts.
 *
 * Answer text used to hardcode the email, location and opening hours, which
 * meant the same fact could be written two different ways on one page once
 * inc/data/site.php changed. Writing `{location}` instead makes drift
 * impossible: there is one source, and it is site.php.
 *
 * @param string $text Text possibly containing placeholders.
 * @return string
 */
function ssbd_resolve_placeholders( $text ) {
	static $map = null;

	if ( null === $map ) {
		$map = array(
			'{email}'     => (string) ssbd_site( 'email' ),
			'{phone}'     => (string) ssbd_site( 'phone' ),
			'{location}'  => (string) ssbd_site( 'location_label' ),
			'{locality}'  => (string) ssbd_site( 'address.locality' ),
			'{region}'    => (string) ssbd_site( 'address.region' ),
			'{hours}'     => (string) ssbd_site( 'hours_label' ),
			'{response}'  => (string) ssbd_site( 'response_time' ),
			'{areas}'     => implode( ', ', (array) ssbd_site( 'areas_served', array() ) ),
			'{name}'      => (string) ssbd_site( 'legal_name' ),
		);
	}

	return strtr( (string) $text, $map );
}

/**
 * The GEO/AEO answer block for a page key.
 *
 * @param string $key Page key.
 * @return array{heading:string,summary:string}|null
 */
function ssbd_answer( $key ) {
	$all = ssbd_data( 'answers' );

	if ( ! isset( $all[ $key ] ) ) {
		return null;
	}

	$answer = $all[ $key ];

	$answer['summary'] = ssbd_resolve_placeholders( $answer['summary'] ?? '' );

	/**
	 * Filter a resolved answer block.
	 *
	 * @param array  $answer Resolved answer.
	 * @param string $key    Page key.
	 */
	return apply_filters( 'ssbd_answer', $answer, $key );
}

/**
 * Every service, parents and children alike, in display order.
 *
 * @return array
 */
function ssbd_all_services() {
	// wp-admin wins when it has content; the PHP file is the seed and fallback.
	return ssbd_catalogue_services() ?? ssbd_data( 'services' );
}

/**
 * Top-level services, each with its children under a 'children' key.
 *
 * The grids and the JSON-LD list want one entry per headline service, with
 * Laravel and WordPress living inside Web Development rather than competing
 * with it. The header dropdown reads the same tree.
 *
 * @param string $group 'all', 'build', 'automate', 'grow' or 'support'.
 * @return array
 */
function ssbd_services( $group = 'all' ) {
	$services = ssbd_all_services();
	$tree     = array();

	foreach ( $services as $service ) {
		if ( empty( $service['parent'] ) ) {
			$service['children'] = array();
			$tree[ $service['slug'] ] = $service;
		}
	}

	foreach ( $services as $service ) {
		$parent = $service['parent'] ?? '';

		// A child whose parent is gone would otherwise vanish from the site
		// entirely, so it is promoted rather than dropped.
		if ( ! $parent ) {
			continue;
		}

		if ( isset( $tree[ $parent ] ) ) {
			$tree[ $parent ]['children'][] = $service;
		} else {
			$service['children']      = array();
			$tree[ $service['slug'] ] = $service;
		}
	}

	$tree = array_values( $tree );

	if ( 'all' !== $group ) {
		$tree = array_values( array_filter(
			$tree,
			static function ( $service ) use ( $group ) {
				return isset( $service['group'] ) && $group === $service['group'];
			}
		) );
	}

	/** Filter the resolved service tree. */
	return apply_filters( 'ssbd_services', $tree, $group );
}

/**
 * One service by slug, children included, or null.
 *
 * @param string $slug Service slug.
 * @return array|null
 */
function ssbd_service( $slug ) {
	foreach ( ssbd_services() as $service ) {
		if ( $service['slug'] === $slug ) {
			return $service;
		}

		foreach ( $service['children'] as $child ) {
			if ( $child['slug'] === $slug ) {
				return $child;
			}
		}
	}

	return null;
}

/**
 * The permalink for a service.
 *
 * Falls back to the hub page with the service's anchor, which is where these
 * links pointed before services had pages of their own — so a service that
 * only exists in the PHP data file still lands somewhere real.
 *
 * @param array $service Service array.
 * @return string
 */
function ssbd_service_url( $service ) {
	$slug = $service['slug'] ?? '';

	if ( $slug ) {
		// Matched on post_name rather than path: a child service's path is
		// web-development/laravel-development, but the slug is the leaf.
		$posts = get_posts( array(
			'post_type'        => 'ssbd_service',
			'name'             => $slug,
			'post_status'      => 'publish',
			'numberposts'      => 1,
			'suppress_filters' => false,
		) );

		if ( $posts ) {
			return (string) get_permalink( $posts[0] );
		}
	}

	return ssbd_page_url( 'services' ) . ( $slug ? '#' . $slug : '' );
}

/**
 * The comparison matchups, with each one's article resolved.
 *
 * A matchup names the post that answers its search. Where that post is
 * published the card becomes a link; where it is not, the card says so rather
 * than pointing at a 404. That way the list doubles as the editorial plan and
 * fills in on its own as posts go live.
 *
 * @param string $pillar Pillar key, or 'all'.
 * @return array
 */
function ssbd_matchups( $pillar = 'all' ) {
	$data     = ssbd_data( 'comparisons' );
	$matchups = array();

	foreach ( $data['matchups'] as $matchup ) {
		if ( 'all' !== $pillar && $matchup['pillar'] !== $pillar ) {
			continue;
		}

		$post = $matchup['post'] ? get_page_by_path( $matchup['post'], OBJECT, 'post' ) : null;

		$matchup['url']       = ( $post && 'publish' === $post->post_status ) ? get_permalink( $post ) : '';
		$matchup['published'] = (bool) $matchup['url'];

		$matchups[] = $matchup;
	}

	// Published comparisons lead: a reader wants the ones they can read now.
	usort(
		$matchups,
		static function ( $a, $b ) {
			return ( $b['published'] <=> $a['published'] );
		}
	);

	/** Filter the resolved comparison matchups. */
	return apply_filters( 'ssbd_matchups', $matchups, $pillar );
}

/**
 * The matchups whose article is live — the ones a reader can actually read.
 *
 * @param string $pillar Pillar key, or 'all'.
 * @return array
 */
function ssbd_matchups_published( $pillar = 'all' ) {
	return array_values( array_filter(
		ssbd_matchups( $pillar ),
		static function ( $matchup ) {
			return ! empty( $matchup['published'] );
		}
	) );
}

/**
 * The matchups still to be written, as a short roadmap.
 *
 * These used to sit in the comparison table itself with "Being written" in
 * the Read column — fifteen rows of a table promising an article behind each
 * one, none of which existed. A reader, and an affiliate reviewer, reads that
 * as content that was promised and never delivered. The same list under a
 * heading that says it is a plan is a plan, which is a different claim.
 *
 * The list is capped rather than trimmed in the data file: the full editorial
 * plan stays in inc/data/comparisons.php as the working document, and only
 * what is worth showing is shown. A published post is picked up by slug, so a
 * row leaves this list and joins the table on its own the day it goes live.
 *
 * @param string $pillar Pillar key, or 'all'.
 * @param int    $limit  Maximum rows to return. 0 for the filtered default.
 * @return array
 */
function ssbd_matchups_roadmap( $pillar = 'all', $limit = 0 ) {
	$planned = array_values( array_filter(
		ssbd_matchups( $pillar ),
		static function ( $matchup ) {
			return empty( $matchup['published'] );
		}
	) );

	if ( ! $limit ) {
		/** Filter how many planned comparisons a roadmap block shows. */
		$limit = (int) apply_filters( 'ssbd_roadmap_limit', 3, $pillar );
	}

	return $limit > 0 ? array_slice( $planned, 0, $limit ) : $planned;
}

/**
 * Portfolio projects, optionally narrowed to one filter key.
 *
 * @param string $filter Filter key or 'all'.
 * @return array
 */
function ssbd_projects( $filter = 'all' ) {
	$projects = ssbd_catalogue_projects() ?? ssbd_data( 'projects' );

	if ( 'all' === $filter ) {
		return $projects;
	}

	return array_values( array_filter(
		$projects,
		static function ( $project ) use ( $filter ) {
			return in_array( $filter, (array) ( $project['filters'] ?? array() ), true );
		}
	) );
}

/**
 * The page key for the current request, used to look up FAQs, answer blocks
 * and schema type. Falls back to the post slug.
 *
 * @return string
 */
function ssbd_current_page_key() {
	if ( is_front_page() ) {
		return 'home';
	}

	if ( is_page() ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		$map  = array(
			'privacy-policy'       => 'legal',
			'terms'                => 'legal',
			'affiliate-disclosure' => 'legal',
		);
		return $map[ $slug ] ?? $slug;
	}

	if ( is_singular( 'post' ) ) {
		return 'article';
	}

	return 'archive';
}

/**
 * Products, from wp-admin when available.
 *
 * @param string $filter Filter key or 'all'.
 * @return array
 */
function ssbd_products( $filter = 'all' ) {
	$products = ssbd_catalogue_products() ?? ( ssbd_data( 'products' )['items'] ?? array() );

	if ( 'all' === $filter ) {
		return $products;
	}

	return array_values( array_filter(
		$products,
		static function ( $product ) use ( $filter ) {
			// Match every category the product is filed under, not just the
			// first, so a product in a sub-category still shows under the
			// parent category the header dropdown links to.
			$keys = $product['filters'] ?? array();

			if ( isset( $product['filter'] ) && '' !== $product['filter'] ) {
				$keys[] = $product['filter'];
			}

			return in_array( $filter, $keys, true );
		}
	) );
}

/**
 * The stable key that identifies a product in a URL.
 *
 * Products are registered with `public => false` and have no permalink of
 * their own, so the order form addresses them by this key rather than by ID —
 * which also means a product that only exists in the PHP data file, with no
 * post behind it, can still be ordered.
 *
 * @param array $product Product array.
 * @return string
 */
function ssbd_product_key( $product ) {
	$slug = $product['slug'] ?? '';

	return $slug ? $slug : sanitize_title( $product['name'] ?? '' );
}

/**
 * One product by its key, or null.
 *
 * @param string $key Key from ssbd_product_key().
 * @return array|null
 */
function ssbd_product_by_key( $key ) {
	$key = sanitize_title( $key );

	if ( ! $key ) {
		return null;
	}

	foreach ( ssbd_products() as $product ) {
		if ( ssbd_product_key( $product ) === $key ) {
			return $product;
		}
	}

	return null;
}

/**
 * A product's price as it should be shown, or '' when there is none.
 *
 * The field is free text rather than a number so it can hold whatever the
 * product is actually sold in — BDT, USD, "from ৳5,000", "৳500/mo". An empty
 * price is not a zero price: the caller shows "Request a quote" instead.
 *
 * @param array $product Product array.
 * @return string
 */
function ssbd_product_price( $product ) {
	return trim( (string) ( $product['price'] ?? '' ) );
}

/**
 * The order page URL for a product.
 *
 * @param array $product Product array.
 * @return string
 */
function ssbd_order_url( $product ) {
	return add_query_arg( 'product', ssbd_product_key( $product ), ssbd_page_url( 'order' ) );
}

/**
 * Filter tabs for the Portfolio page.
 *
 * @return array<array{key:string,label:string}>
 */
function ssbd_project_filters() {
	return ssbd_catalogue_filters(
		'ssbd_project_category',
		__( 'All', 'ssbd' ),
		array(
			array( 'key' => 'all', 'label' => __( 'All', 'ssbd' ) ),
			array( 'key' => 'websites', 'label' => __( 'Websites', 'ssbd' ) ),
			array( 'key' => 'wordpress', 'label' => __( 'WordPress', 'ssbd' ) ),
			array( 'key' => 'laravel', 'label' => __( 'Laravel', 'ssbd' ) ),
			array( 'key' => 'software', 'label' => __( 'Software', 'ssbd' ) ),
			array( 'key' => 'ecommerce', 'label' => __( 'eCommerce', 'ssbd' ) ),
		)
	);
}

/**
 * Filter tabs for the Products page.
 *
 * @return array<array{key:string,label:string}>
 */
function ssbd_product_filters() {
	return ssbd_catalogue_filters(
		'ssbd_product_category',
		__( 'All Products', 'ssbd' ),
		ssbd_data( 'products' )['filters'] ?? array()
	);
}
