<?php
/**
 * Reading the catalogue post types back out.
 *
 * Each loader returns exactly the array shape the corresponding file in
 * inc/data/ returns, so every template, schema node and llms.txt entry keeps
 * working untouched. If a post type has no published posts the PHP data file
 * is used instead — which means the theme's shipped copy is also its seed
 * data, and adding your first Project in wp-admin quietly takes over.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * The per-request cache of catalogue posts.
 *
 * Held in a static that can be reset, rather than a static local, because a
 * request that both writes and reads — activation seeding, or saving a post
 * with an active front-end cache — would otherwise keep serving the result
 * from before the write.
 *
 * @param string|null $type  Post type to read, or null to read the whole cache.
 * @param array|null  $store Posts to store, or null to just read.
 * @return array
 */
function ssbd_catalogue_cache( $type = null, $store = null ) {
	static $cache = array();

	if ( null === $type ) {
		$cache = array();
		return array();
	}

	if ( null !== $store ) {
		$cache[ $type ] = $store;
	}

	return $cache[ $type ] ?? array();
}

/**
 * Drop the cache whenever catalogue content changes.
 */
function ssbd_flush_catalogue_cache() {
	ssbd_catalogue_cache();
}
add_action( 'save_post', 'ssbd_flush_catalogue_cache' );
add_action( 'deleted_post', 'ssbd_flush_catalogue_cache' );
add_action( 'set_object_terms', 'ssbd_flush_catalogue_cache' );

/**
 * Fetch published posts of a catalogue type, in Order then title sequence.
 *
 * @param string $type Post type.
 * @return WP_Post[]
 */
function ssbd_catalogue_posts( $type ) {
	$cached = ssbd_catalogue_cache( $type );

	if ( $cached ) {
		return $cached;
	}

	$posts = get_posts( array(
		'post_type'        => $type,
		'post_status'      => 'publish',
		'numberposts'      => 100,
		'orderby'          => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		'suppress_filters' => false,
	) );

	// An empty result is deliberately not cached: it is the state that gets
	// invalidated by the very next write, and re-running an empty query is
	// cheap next to serving stale content.
	if ( $posts ) {
		ssbd_catalogue_cache( $type, $posts );
	}

	return $posts;
}

/**
 * Read a list-type meta field back as an array.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Field key without the underscore prefix.
 * @return string[]
 */
function ssbd_meta_list( $post_id, $key ) {
	$raw = (string) get_post_meta( $post_id, '_ssbd_' . $key, true );

	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ) ) );
}

/**
 * The term slugs attached to a post, used as filter keys.
 *
 * @param int    $post_id  Post ID.
 * @param string $taxonomy Taxonomy.
 * @return string[]
 */
function ssbd_term_slugs( $post_id, $taxonomy ) {
	$terms = wp_get_post_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );

	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Projects from wp-admin, or null when there are none.
 *
 * @return array|null
 */
function ssbd_catalogue_projects() {
	$posts = ssbd_catalogue_posts( 'ssbd_project' );

	if ( ! $posts ) {
		return null;
	}

	$out = array();

	foreach ( $posts as $post ) {
		$out[] = array(
			'name'    => get_the_title( $post ),
			'desc'    => (string) get_post_meta( $post->ID, '_ssbd_desc', true ),
			'tech'    => (string) get_post_meta( $post->ID, '_ssbd_tech', true ),
			'badge'   => (string) get_post_meta( $post->ID, '_ssbd_badge', true ),
			'tag'     => (string) get_post_meta( $post->ID, '_ssbd_tag', true ),
			'url'     => (string) get_post_meta( $post->ID, '_ssbd_url', true ),
			'filters' => ssbd_term_slugs( $post->ID, 'ssbd_project_category' ),
			// The attachment ID, not a rendered URL: the card needs it to build
			// a srcset, so a 398px-wide card on a phone is not downloading the
			// same 720x450 file as a wide desktop grid.
			'image'   => has_post_thumbnail( $post ) ? get_post_thumbnail_id( $post ) : 0,
		);
	}

	return $out;
}

/**
 * Services from wp-admin, or null when there are none.
 *
 * @return array|null
 */
function ssbd_catalogue_services() {
	$posts = ssbd_catalogue_posts( 'ssbd_service' );

	if ( ! $posts ) {
		return null;
	}

	$slugs = array();
	foreach ( $posts as $post ) {
		$slugs[ $post->ID ] = $post->post_name;
	}

	$out      = array();
	$position = 0;

	foreach ( $posts as $post ) {
		$desc   = (string) get_post_meta( $post->ID, '_ssbd_desc', true );
		$short  = (string) get_post_meta( $post->ID, '_ssbd_short', true );
		$parent = $slugs[ $post->post_parent ] ?? '';

		// A service published before its description fields are filled in used to
		// render an empty paragraph on the front page and the Services page. The
		// excerpt — or the opening of the body, which is what get_the_excerpt()
		// falls back to — says more than nothing while those fields are empty.
		$fallback = ( $desc || $short ) ? '' : trim( (string) get_the_excerpt( $post ) );

		// Only headline services are numbered; a child is part of its parent's
		// entry, not another row on the Services page.
		if ( ! $parent ) {
			$position++;
		}

		$out[] = array(
			// The displayed number follows list position, so reordering in
			// wp-admin renumbers the Services page automatically.
			'no'     => $parent ? '' : str_pad( (string) $position, 2, '0', STR_PAD_LEFT ),
			'slug'   => $post->post_name,
			'parent' => $parent,
			'title'  => get_the_title( $post ),
			'short'  => $short ?: $desc ?: $fallback,
			'desc'   => $desc ?: $short ?: $fallback,
			'group'  => (string) get_post_meta( $post->ID, '_ssbd_group', true ) ?: 'build',
			'icon'   => (string) get_post_meta( $post->ID, '_ssbd_icon', true ) ?: 'code',
			'pills'  => ssbd_meta_list( $post->ID, 'pills' ),
			'items'  => ssbd_meta_list( $post->ID, 'items' ),
			'cta'    => (string) get_post_meta( $post->ID, '_ssbd_cta', true ) ?: __( 'Learn more', 'ssbd' ),
			'id'     => $post->ID,
		);
	}

	return $out;
}

/**
 * The screenshots attached to a service, ready to render.
 *
 * Returns attachment IDs, so the caller can use wp_get_attachment_image() and
 * get responsive sizes, lazy loading and the alt text from the media library
 * for free.
 *
 * @param string $slug Service slug.
 * @return int[]
 */
function ssbd_service_screens( $slug ) {
	$posts = get_posts( array(
		'post_type'        => 'ssbd_service',
		'name'             => $slug,
		'post_status'      => 'publish',
		'numberposts'      => 1,
		'suppress_filters' => false,
	) );

	if ( ! $posts ) {
		return array();
	}

	$ids = array_filter( array_map(
		'absint',
		explode( ',', (string) get_post_meta( $posts[0]->ID, '_ssbd_screens', true ) )
	) );

	// An ID whose attachment has been deleted would render an empty frame.
	$ids = array_filter(
		$ids,
		static function ( $id ) {
			return (bool) wp_get_attachment_image_src( $id, 'medium' );
		}
	);

	/** Filter the screenshots shown for a service. */
	return array_values( apply_filters( 'ssbd_service_screens', $ids, $slug ) );
}

/**
 * Products from wp-admin, or null when there are none.
 *
 * @return array|null
 */
function ssbd_catalogue_products() {
	$posts = ssbd_catalogue_posts( 'ssbd_product' );

	if ( ! $posts ) {
		return null;
	}

	$out = array();

	foreach ( $posts as $post ) {
		$slugs = ssbd_term_slugs( $post->ID, 'ssbd_product_category' );

		$out[] = array(
			'name'     => get_the_title( $post ),
			'desc'     => (string) get_post_meta( $post->ID, '_ssbd_desc', true ),
			'category' => (string) get_post_meta( $post->ID, '_ssbd_category', true ),
			'filter'   => $slugs[0] ?? '',
			// Every category the product is in, not just the first: the header
			// dropdown lists sub-categories, and a product filed under one has
			// to still show when its parent category is the one picked.
			'filters'  => $slugs,
			'status'   => (string) get_post_meta( $post->ID, '_ssbd_status', true ),
			'price'    => (string) get_post_meta( $post->ID, '_ssbd_price', true ),
			'slug'     => $post->post_name,
			'id'       => $post->ID,
		);
	}

	return $out;
}

/**
 * Product categories as a two-level tree, for the header dropdown.
 *
 * Only terms that actually have products are listed, so a dropdown row can
 * never lead to an empty grid. The taxonomy is hierarchical, so a category can
 * have sub-categories; anything nested deeper than one level is flattened up
 * to its top-level ancestor, because the header has room for Products > a
 * category > a sub-category and no further.
 *
 * Falls back to the shipped filter list when the taxonomy is unused, minus its
 * leading "All" tab — the Products menu item is already that link.
 *
 * @return array<array{key:string,label:string,children:array}>
 */
function ssbd_product_category_tree() {
	$terms = get_terms( array(
		'taxonomy'   => 'ssbd_product_category',
		'hide_empty' => true,
		'orderby'    => 'name',
	) );

	if ( is_wp_error( $terms ) || ! $terms ) {
		$rows = array();

		foreach ( ssbd_data( 'products' )['filters'] ?? array() as $tab ) {
			if ( 'all' === $tab['key'] ) {
				continue;
			}

			$rows[] = array( 'key' => $tab['key'], 'label' => $tab['label'], 'children' => array() );
		}

		return $rows;
	}

	$by_id = array();
	foreach ( $terms as $term ) {
		$by_id[ $term->term_id ] = $term;
	}

	$top      = array();
	$children = array();

	foreach ( $terms as $term ) {
		// Walk up to the highest ancestor that is itself listed. A term whose
		// parent has no products of its own is promoted rather than dropped,
		// which is the same rule ssbd_services() uses for orphaned children.
		$ancestor = $term;
		$hops     = 0;

		while ( $ancestor->parent && isset( $by_id[ $ancestor->parent ] ) && $hops < 10 ) {
			$ancestor = $by_id[ $ancestor->parent ];
			$hops++;
		}

		// Still pointing at a listed parent means the loop hit its guard rather
		// than reaching a root — a cycle. Treat the term as top-level: a flat
		// dropdown is a poor result, an empty one is a broken one.
		$looped = $ancestor->parent && isset( $by_id[ $ancestor->parent ] );

		if ( 0 === $hops || $looped ) {
			$top[] = $term;
		} else {
			$children[ $ancestor->term_id ][] = $term;
		}
	}

	$tree = array();

	foreach ( $top as $term ) {
		$node = array( 'key' => $term->slug, 'label' => $term->name, 'children' => array() );

		foreach ( $children[ $term->term_id ] ?? array() as $child ) {
			$node['children'][] = array( 'key' => $child->slug, 'label' => $child->name, 'children' => array() );
		}

		$tree[] = $node;
	}

	/** Filter the product category tree shown in the header. */
	return apply_filters( 'ssbd_product_category_tree', $tree );
}

/**
 * Filter tabs for a catalogue taxonomy, always led by an "All" tab.
 *
 * Only terms that actually have posts are listed, so a tab can never lead to
 * an empty grid.
 *
 * @param string $taxonomy Taxonomy name.
 * @param string $all      Label for the leading tab.
 * @param array  $fallback Tabs to use when the taxonomy is unused.
 * @return array<array{key:string,label:string}>
 */
function ssbd_catalogue_filters( $taxonomy, $all, $fallback ) {
	$terms = get_terms( array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => true,
		'orderby'    => 'name',
	) );

	if ( is_wp_error( $terms ) || ! $terms ) {
		return $fallback;
	}

	$tabs = array( array( 'key' => 'all', 'label' => $all ) );

	foreach ( $terms as $term ) {
		$tabs[] = array( 'key' => $term->slug, 'label' => $term->name );
	}

	return $tabs;
}
