<?php
/**
 * Comparison pillar pages.
 *
 * /comparisons/ is the hub; /comparisons/hosting-domains/ and its two
 * siblings are real pages with their own title, description, canonical and
 * breadcrumb. They are not posts: the three pillars are defined in
 * inc/data/comparisons.php, and creating three near-empty Pages to hold
 * content that already lives in a data file would mean two sources of truth
 * and three things to keep in sync.
 *
 * So a rewrite rule sends the segment through the Comparisons page template,
 * which reads the pillar and renders that view instead of the hub.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * The URL segment the pillar pages live under — the hub page's slug.
 *
 * @return string
 */
function ssbd_comparison_base() {
	/** Filter the base slug for comparison pillar pages. */
	return sanitize_title( (string) apply_filters( 'ssbd_comparison_base', 'comparisons' ) );
}

/**
 * Register the pillar rewrite and its query var.
 */
function ssbd_comparison_rewrites() {
	add_rewrite_rule(
		'^' . ssbd_comparison_base() . '/([^/]+)/?$',
		'index.php?pagename=' . ssbd_comparison_base() . '&ssbd_pillar=$matches[1]',
		'top'
	);
}
add_action( 'init', 'ssbd_comparison_rewrites' );

/**
 * Let WordPress keep the pillar segment.
 *
 * @param string[] $vars Query vars.
 * @return string[]
 */
function ssbd_comparison_query_var( $vars ) {
	$vars[] = 'ssbd_pillar';

	return $vars;
}
add_filter( 'query_vars', 'ssbd_comparison_query_var' );

/**
 * Every pillar, keyed by its URL slug.
 *
 * @return array<string, array>
 */
function ssbd_pillars() {
	$pillars = array();

	foreach ( ssbd_data( 'comparisons' )['pillars'] as $pillar ) {
		$pillar['slug']          = sanitize_title( $pillar['label'] );
		$pillars[ $pillar['slug'] ] = $pillar;
	}

	/** Filter the comparison pillars. */
	return apply_filters( 'ssbd_pillars', $pillars );
}

/**
 * The pillar being viewed, or null on the hub.
 *
 * @return array|null
 */
function ssbd_current_pillar() {
	$slug = get_query_var( 'ssbd_pillar' );

	if ( ! $slug ) {
		return null;
	}

	return ssbd_pillars()[ sanitize_title( $slug ) ] ?? null;
}

/**
 * The URL of a pillar page, by pillar key.
 *
 * @param string $key Pillar key.
 * @return string
 */
function ssbd_pillar_url( $key ) {
	foreach ( ssbd_pillars() as $slug => $pillar ) {
		if ( $pillar['key'] === $key ) {
			return trailingslashit( ssbd_page_url( 'comparisons' ) ) . $slug . '/';
		}
	}

	return ssbd_page_url( 'comparisons' );
}

/**
 * A made-up segment must 404 rather than quietly serving the hub.
 *
 * Without this, /comparisons/anything-at-all/ would return the hub with a 200
 * — an infinite supply of duplicate pages for a crawler to find.
 */
function ssbd_comparison_404() {
	global $wp_query;

	if ( get_query_var( 'ssbd_pillar' ) && ! ssbd_current_pillar() ) {
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}
add_action( 'wp', 'ssbd_comparison_404' );

/**
 * Title, description, canonical and breadcrumb for a pillar page.
 *
 * All four have to move together: three pages sharing the hub's title and
 * canonical would be three copies of one page as far as search is concerned.
 */
function ssbd_comparison_seo() {
	$pillar = ssbd_current_pillar();

	if ( ! $pillar ) {
		return;
	}

	$content = ssbd_pillar_content( $pillar['key'] );

	add_filter( 'document_title_parts', static function ( $parts ) use ( $pillar, $content ) {
		/*
		 * The editorial title when the pillar has one. "Website Infrastructure
		 * — Tool Comparisons" describes the category the way the site files it;
		 * it matches nothing anyone searches for. `seo_title` is written to sit
		 * inside 60 characters once the site name is appended.
		 */
		$parts['title'] = $content['seo_title'] ?? sprintf(
			/* translators: %s: pillar name */
			__( '%s — Tool Comparisons', 'ssbd' ),
			$pillar['title']
		);

		return $parts;
	}, 20 );

	add_filter( 'ssbd_meta_description', static function () use ( $pillar, $content ) {
		return $content['meta_description'] ?? $pillar['desc'];
	}, 20 );

	add_filter( 'ssbd_canonical_url', static function () use ( $pillar ) {
		return ssbd_pillar_url( $pillar['key'] );
	}, 20 );

	add_filter( 'ssbd_breadcrumb_trail', static function ( $trail ) use ( $pillar ) {
		// The hub page is the parent, then this pillar.
		$trail = array( $trail[0] );

		$trail[] = array( 'name' => __( 'Comparisons', 'ssbd' ), 'url' => ssbd_page_url( 'comparisons' ) );
		$trail[] = array( 'name' => $pillar['title'], 'url' => ssbd_pillar_url( $pillar['key'] ) );

		return $trail;
	}, 20 );
}
add_action( 'wp', 'ssbd_comparison_seo' );

/**
 * Add the pillar pages to the XML sitemap.
 *
 * They are real URLs with their own content, so they belong in it — but
 * nothing else knows they exist, because no post type backs them.
 *
 * @param array  $entries  Sitemap entries.
 * @param string $type     Object type.
 * @param string $sub_type Object sub type.
 * @return array
 */
function ssbd_comparison_sitemap( $entries, $type, $sub_type ) {
	if ( 'post' !== $type || 'page' !== $sub_type ) {
		return $entries;
	}

	foreach ( ssbd_pillars() as $pillar ) {
		$entries[] = array( 'loc' => ssbd_pillar_url( $pillar['key'] ) );
	}

	return $entries;
}
add_filter( 'wp_sitemaps_posts_entries', 'ssbd_comparison_sitemap', 10, 3 );

/* -------------------------------------------------------------------------
 * Editorial content for a pillar
 * ---------------------------------------------------------------------- */

/**
 * The long-form editorial content for one pillar, or an empty array.
 *
 * Every consumer treats a missing key as "skip this section", so a pillar with
 * nothing written for it renders exactly the page it rendered before any of
 * this existed.
 *
 * @param string $key Pillar key.
 * @return array
 */
function ssbd_pillar_content( $key ) {
	$content = ssbd_data( 'pillar-content' )[ $key ] ?? array();

	/**
	 * Filter one pillar's editorial content.
	 *
	 * @param array  $content Editorial sections.
	 * @param string $key     Pillar key.
	 */
	return apply_filters( 'ssbd_pillar_content', $content, $key );
}

/**
 * Resolve a list of matchup post slugs into full matchup rows.
 *
 * The rows come back through ssbd_matchups(), which has already decided
 * whether each article exists — so a slug naming an unwritten comparison
 * arrives with an empty url and published = false, and the template renders it
 * as planned instead of linking to a 404. That is the whole reason groups name
 * slugs rather than URLs.
 *
 * @param string[] $slugs  Post slugs, in the order they should appear.
 * @param string   $pillar Pillar key to search within.
 * @return array
 */
function ssbd_matchups_by_slug( $slugs, $pillar = 'all' ) {
	if ( ! $slugs ) {
		return array();
	}

	$indexed = array();

	foreach ( ssbd_matchups( $pillar ) as $matchup ) {
		if ( ! empty( $matchup['post'] ) ) {
			$indexed[ $matchup['post'] ] = $matchup;
		}
	}

	$rows = array();

	foreach ( (array) $slugs as $slug ) {
		if ( isset( $indexed[ $slug ] ) ) {
			$rows[] = $indexed[ $slug ];
		}
	}

	return $rows;
}

/**
 * Find one offer in a list by its slug.
 *
 * Returns null when the offer is not published, which is what makes a quick
 * pick safe to write in a data file: naming an offer that has not been created
 * or has no tracking URL yet drops the row rather than rendering a dead card.
 *
 * @param array  $offers Offers from ssbd_offers().
 * @param string $slug   Offer post slug.
 * @return array|null
 */
function ssbd_offer_by_slug( $offers, $slug ) {
	foreach ( $offers as $offer ) {
		if ( ( $offer['slug'] ?? '' ) === $slug ) {
			return $offer;
		}
	}

	return null;
}

/**
 * The comparison rows a hosting table should always try to show, in order.
 *
 * This fixes the order; the offers' own feature lists supply the values. A row
 * appears once at least one provider has a value for it, and a provider that
 * does not renders an em dash beside the ones that do — the gap is visible
 * where it means something. Rows nobody has filled stay hidden rather than
 * printing a column of dashes that says nothing.
 *
 * To fill one in: edit the offer in wp-admin and add a Features line in
 * "Label: value" form, using exactly the label below.
 *
 * @param string $category Offer category slug.
 * @return string[]
 */
function ssbd_offer_table_rows( $category = '' ) {
	$rows = 'hosting' === $category
		? array(
			'Starting price',
			'Renewal price',
			'Hosting type',
			'Free SSL',
			'Free migration',
			'Backups',
			'CDN',
			'Control panel',
			'Data centres',
			'WordPress',
			'Laravel',
			'Developer access',
			'Scalability',
			'Money-back guarantee',
			'Support',
		)
		: array();

	/**
	 * Filter the ordered comparison rows for an offer table.
	 *
	 * @param string[] $rows     Feature labels, in display order.
	 * @param string   $category Offer category slug.
	 */
	return apply_filters( 'ssbd_offer_table_rows', $rows, $category );
}
