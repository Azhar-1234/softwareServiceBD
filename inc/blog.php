<?php
/**
 * The news portal blog index.
 *
 * home.php composes the blog index out of several passes over the posts —
 * featured, latest, one block per category — and the risk with that shape is
 * the same article turning up three times on one screen. These helpers own the
 * queries and the bookkeeping that stops it.
 *
 * The rule: featured and latest never overlap, because they sit next to each
 * other. Category blocks are allowed to repeat a post, because a category is a
 * different way in rather than a continuation of the list — and excluding
 * would leave most blocks empty on a small blog.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Post IDs already placed on this page.
 *
 * @param int[]|null $add IDs to record, or null to just read.
 * @return int[]
 */
function ssbd_blog_seen( $add = null ) {
	static $seen = array();

	if ( null !== $add ) {
		$seen = array_values( array_unique( array_merge( $seen, array_map( 'intval', (array) $add ) ) ) );
	}

	return $seen;
}

/**
 * Shared query arguments for every portal query.
 *
 * @param array $extra Arguments to merge in.
 * @return array
 */
function ssbd_blog_query_args( $extra = array() ) {
	return array_merge(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'fields'              => 'ids',
		),
		$extra
	);
}

/**
 * The lead stories: one main, the rest as siblings beside it.
 *
 * Sticky posts come first so an editor can hold a story at the top, then the
 * newest fill whatever is left.
 *
 * @param int $count How many in total.
 * @return int[]
 */
function ssbd_blog_featured( $count = 4 ) {
	$sticky = array_values( array_filter( array_map( 'intval', (array) get_option( 'sticky_posts', array() ) ) ) );
	$ids    = array();

	if ( $sticky ) {
		$ids = get_posts( ssbd_blog_query_args( array(
			'post__in'       => $sticky,
			'posts_per_page' => $count,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) ) );
	}

	if ( count( $ids ) < $count ) {
		$ids = array_merge( $ids, get_posts( ssbd_blog_query_args( array(
			'posts_per_page' => $count - count( $ids ),
			'post__not_in'   => $ids,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) ) ) );
	}

	ssbd_blog_seen( $ids );

	return $ids;
}

/**
 * The latest column, excluding anything already featured above it.
 *
 * @param int $count How many.
 * @return int[]
 */
function ssbd_blog_latest( $count = 6 ) {
	$ids = get_posts( ssbd_blog_query_args( array(
		'posts_per_page' => $count,
		'post__not_in'   => ssbd_blog_seen(),
		'orderby'        => 'date',
		'order'          => 'DESC',
	) ) );

	ssbd_blog_seen( $ids );

	return $ids;
}

/**
 * The trending list.
 *
 * Ranked by comment count, which is the only engagement signal WordPress keeps
 * without a plugin — it is a proxy for popularity, not a page-view count. On a
 * blog with no comments it degrades to "most recent", which is a reasonable
 * thing for the slot to show and never an empty box.
 *
 * @param int $count How many.
 * @return int[]
 */
function ssbd_blog_trending( $count = 5 ) {
	$ids = get_posts( ssbd_blog_query_args( array(
		'posts_per_page' => $count,
		'orderby'        => array( 'comment_count' => 'DESC', 'date' => 'DESC' ),
	) ) );

	/** Filter the trending list — hook a real analytics source here. */
	return apply_filters( 'ssbd_blog_trending', $ids, $count );
}

/**
 * Categories that actually have posts, biggest first.
 *
 * "Uncategorized" is dropped: it is WordPress's default bucket, not an
 * editorial section, and a portal row headed "Uncategorized" looks unfinished.
 *
 * @param int $limit Maximum to return.
 * @return WP_Term[]
 */
function ssbd_blog_categories( $limit = 4 ) {
	$terms = get_terms( array(
		'taxonomy'   => 'category',
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'exclude'    => array( (int) get_option( 'default_category' ) ),
	) );

	if ( is_wp_error( $terms ) || ! $terms ) {
		return array();
	}

	$terms = array_values( array_filter(
		$terms,
		static function ( $term ) {
			return 'uncategorized' !== $term->slug;
		}
	) );

	return array_slice( $terms, 0, max( 0, (int) $limit ) );
}

/**
 * Posts inside one category, for a category block.
 *
 * @param int $term_id Category ID.
 * @param int $count   How many.
 * @return int[]
 */
function ssbd_blog_category_posts( $term_id, $count = 3 ) {
	return get_posts( ssbd_blog_query_args( array(
		'posts_per_page' => $count,
		// 'cat' rather than category__in so a parent brings its children with
		// it — the same rule the front page's insights section follows.
		'cat'            => (int) $term_id,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) ) );
}

/**
 * Run the loop over a list of post IDs, in the order given.
 *
 * Wrapped here because every section needs it and each one otherwise repeats
 * the same six lines of WP_Query plumbing plus the reset that goes with it.
 *
 * @param int[]    $ids      Post IDs.
 * @param callable $callback Called once per post, inside the loop.
 */
function ssbd_blog_each( $ids, callable $callback ) {
	if ( ! $ids ) {
		return;
	}

	$query = new WP_Query( ssbd_blog_query_args( array(
		'post__in'       => $ids,
		'orderby'        => 'post__in',
		'posts_per_page' => count( $ids ),
		'fields'         => '',
	) ) );

	$index = 0;

	while ( $query->have_posts() ) {
		$query->the_post();
		$callback( $index );
		$index++;
	}

	wp_reset_postdata();
}

/**
 * Is there more than one page of posts?
 *
 * @return bool
 */
function ssbd_blog_has_archive() {
	return (int) get_option( 'posts_per_page' ) < (int) wp_count_posts( 'post' )->publish;
}

/*
 * Drop the "Category:" / "Tag:" prefix WordPress puts in front of an archive
 * title. The kind of archive is shown as an eyebrow above the heading instead,
 * so repeating it inside the H1 makes the heading longer without making it say
 * anything more — and the term's own name is what a reader came looking for.
 */
add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );

/**
 * What kind of archive is being viewed, for the eyebrow above its title.
 *
 * @return string
 */
function ssbd_archive_kind() {
	if ( is_category() ) {
		return __( 'Category', 'ssbd' );
	}

	if ( is_tag() ) {
		return __( 'Tag', 'ssbd' );
	}

	if ( is_author() ) {
		return __( 'Author', 'ssbd' );
	}

	if ( is_tax() ) {
		$taxonomy = get_taxonomy( get_queried_object()->taxonomy ?? '' );

		if ( $taxonomy ) {
			return $taxonomy->labels->singular_name;
		}
	}

	return __( 'Archive', 'ssbd' );
}

/**
 * The current archive's term, when it has one.
 *
 * Used to mark the matching pill in the topic bar. A date or author archive
 * has no term, so nothing is marked and the bar simply reads as navigation.
 *
 * @return WP_Term|null
 */
function ssbd_archive_term() {
	$object = get_queried_object();

	return $object instanceof WP_Term ? $object : null;
}

/**
 * The Posts page URL, or the site root when no page is assigned to it.
 *
 * @return string
 */
function ssbd_blog_url() {
	$blog_id = (int) get_option( 'page_for_posts' );

	return $blog_id ? get_permalink( $blog_id ) : home_url( '/' );
}
