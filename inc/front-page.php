<?php
/**
 * Front page composition.
 *
 * The homepage used to be a fixed sequence of get_template_part() calls. It is
 * now a registry: each section declares a key, a label and a template part,
 * and the Customizer decides which of them run and in what order.
 *
 * Every editable string falls back to the theme's data files, so an untouched
 * install renders exactly what it always did and nothing has to be filled in
 * before the site looks right.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * The sections available to the front page, in their default order.
 *
 * The hero is deliberately absent: it is always first and always shown, so
 * there is nothing useful to reorder or switch off.
 *
 * @return array<string, array{label:string, part:string, heading:string, lede:string}>
 */
function ssbd_front_page_sections() {
	$sections = array(
		'services'  => array(
			'label'   => __( 'Services', 'ssbd' ),
			'part'    => 'template-parts/sections/home-services',
			'heading' => __( 'End-to-End Digital Solutions to Scale Your Business', 'ssbd' ),
			'lede'    => __( 'Custom Laravel platforms, high-speed WordPress builds, and custom AI tools designed for measurable growth.', 'ssbd' ),
		),
		'why'       => array(
			'label'   => __( 'Why us', 'ssbd' ),
			'part'    => 'template-parts/sections/home-why',
			'heading' => __( 'Why businesses choose Software Service BD', 'ssbd' ),
			'lede'    => __( 'Development, automation and growth from one team — so nothing falls between vendors.', 'ssbd' ),
		),
		'work'      => array(
			'label'   => __( 'Recent work', 'ssbd' ),
			'part'    => 'template-parts/sections/home-work',
			'heading' => __( 'Recent work', 'ssbd' ),
			'lede'    => __( 'Stores, marketplaces and platforms we have shipped and continue to support.', 'ssbd' ),
		),
		'partners'    => array(
			'label'   => __( 'Partners', 'ssbd' ),
			'part'    => 'template-parts/sections/home-partners',
			'heading' => __( 'Our Partners', 'ssbd' ),
			'lede'    => __( 'The platforms we build on, host with and recommend — with the discount codes our partners give our readers.', 'ssbd' ),
		),
		'resources' => array(
			'label'   => __( 'Resources', 'ssbd' ),
			'part'    => 'template-parts/sections/home-resources',
			'heading' => __( 'Tools, guides and the stack we actually use', 'ssbd' ),
			'lede'    => __( 'Free planning tools plus honest recommendations on hosting, SaaS and AI — the same ones we run on client projects.', 'ssbd' ),
		),
		/*
		 * The heading deliberately does not say "Resources": the resources
		 * section is the one directly above this, with a page of its own at
		 * /resources/. Two sections claiming the same word on one screen is
		 * how a reader ends up unsure which link goes where.
		 */
		'posts'     => array(
			'label'   => __( 'Latest insights', 'ssbd' ),
			'part'    => 'template-parts/sections/home-posts',
			'heading' => __( 'Latest insights and guides', 'ssbd' ),
			'lede'    => __( 'Practical writing on hosting, WordPress, AI and digital business — from the projects we build and support.', 'ssbd' ),
		),
		'faq'       => array(
			'label'   => __( 'FAQ', 'ssbd' ),
			'part'    => 'template-parts/sections/home-faq',
			'heading' => __( 'Common questions about working with us', 'ssbd' ),
			'lede'    => '',
		),
		'cta'       => array(
			'label'   => __( 'Closing call to action', 'ssbd' ),
			'part'    => 'template-parts/sections/home-cta',
			'heading' => __( 'Have a project in mind?', 'ssbd' ),
			'lede'    => __( 'Tell us what you are trying to build or fix. We will come back with a scope, a timeline and a fixed price.', 'ssbd' ),
		),
	);

	/** Filter the registry of front page sections. */
	return apply_filters( 'ssbd_front_page_sections', $sections );
}

/**
 * The ordered list of section keys to render.
 *
 * Stored as a comma-separated string of enabled keys. Anything not in the
 * list is hidden; anything in the list but not in the registry is dropped,
 * so a stale value can never fatal the homepage.
 *
 * @return string[]
 */
function ssbd_front_page_order() {
	$registry = ssbd_front_page_sections();
	$stored   = get_theme_mod( 'ssbd_front_sections', '' );

	if ( '' === $stored ) {
		return array_keys( $registry );
	}

	$keys = array_filter( array_map( 'trim', explode( ',', $stored ) ) );
	$keys = array_values( array_intersect( $keys, array_keys( $registry ) ) );

	/** Filter the resolved front page section order. */
	return apply_filters( 'ssbd_front_page_order', $keys );
}

/**
 * A front page section's heading, honouring the Customizer override.
 *
 * @param string $key Section key.
 * @return string
 */
function ssbd_section_heading( $key ) {
	$registry = ssbd_front_page_sections();
	$custom   = get_theme_mod( 'ssbd_heading_' . $key, '' );

	return '' !== $custom ? $custom : ( $registry[ $key ]['heading'] ?? '' );
}

/**
 * A front page section's supporting copy, honouring the Customizer override.
 *
 * A single "-" means "no lede", so a site owner can clear one that ships with
 * default copy. An empty string cannot mean that, because empty is how the
 * Customizer represents "unset, use the default".
 *
 * @param string $key Section key.
 * @return string
 */
function ssbd_section_lede( $key ) {
	$registry = ssbd_front_page_sections();
	$custom   = get_theme_mod( 'ssbd_lede_' . $key, '' );

	if ( '-' === trim( $custom ) ) {
		return '';
	}

	return '' !== $custom ? $custom : ( $registry[ $key ]['lede'] ?? '' );
}

/**
 * Hero field defaults, before any Customizer override.
 *
 * @return array<string, string>
 */
function ssbd_hero_defaults() {
	return array(
		'eyebrow'    => __( '{location} — working worldwide', 'ssbd' ),

		/*
		 * The H1 opens on the brand string itself, not on the tagline.
		 * "Software Service BD" is the query this site is trying to own, and
		 * the tagline — which is still the <title> suffix and the schema
		 * slogan — never contained it. Keep this as the exact brand name;
		 * the selling happens on the two lines under it and in the lede.
		 */
		'headline'   => (string) ssbd_site( 'short_name' ),
		'subhead'    => __( 'Your partner in', 'ssbd' ),

		'lede'       => ssbd_lede( 'home' ),
		'cta1_label' => __( 'Start Your Project', 'ssbd' ),
		'cta1_url'   => ssbd_page_url( 'contact' ),
		'cta2_label' => __( 'Explore Our Services', 'ssbd' ),
		'cta2_url'   => '#services',
	);
}

/**
 * A hero field, honouring the Customizer override.
 *
 * Placeholders are resolved here too, so {email} typed into the Customizer
 * behaves the same way as {email} written in inc/data/answers.php.
 *
 * @param string $field Field name from ssbd_hero_defaults().
 * @return string
 */
function ssbd_hero( $field ) {
	$defaults = ssbd_hero_defaults();
	$custom   = get_theme_mod( 'ssbd_hero_' . $field, '' );
	$value    = '' !== $custom ? $custom : ( $defaults[ $field ] ?? '' );

	// URLs are not prose; leave them exactly as entered.
	if ( in_array( $field, array( 'cta1_url', 'cta2_url' ), true ) ) {
		return $value;
	}

	return ssbd_resolve_placeholders( $value );
}

/**
 * Let the Customizer hero lede drive the homepage answer passage.
 *
 * The hero lede, the meta description, the ai:summary tag and the JSON-LD
 * abstract are deliberately one sentence from one source. Editing it in the
 * Customizer has to move all four, or they start disagreeing again.
 *
 * @param array  $answer Resolved answer.
 * @param string $key    Page key.
 * @return array
 */
function ssbd_hero_lede_overrides_answer( $answer, $key ) {
	if ( 'home' !== $key ) {
		return $answer;
	}

	$custom = get_theme_mod( 'ssbd_hero_lede', '' );
	if ( '' !== $custom ) {
		$answer['summary'] = ssbd_resolve_placeholders( $custom );
	}

	return $answer;
}
add_filter( 'ssbd_answer', 'ssbd_hero_lede_overrides_answer', 10, 2 );

/**
 * How many capability keywords the Customizer exposes.
 *
 * Five ship in inc/data/site.php; the spare slots let a site owner add one
 * without touching the data file. The orbit spaces its nodes evenly at any
 * count, so no layout depends on this number.
 */
const SSBD_CAPABILITY_SLOTS = 6;

/**
 * The service keywords the hero is built from.
 *
 * One list feeds three things: the orbit nodes, the strip under the hero and
 * the "·" separated line beneath the buttons — they are the same keywords by
 * design, so they resolve from one place.
 *
 * Same rules as ssbd_stats(): empty means "use the theme default", a single
 * dash removes the slot, and an untouched Customizer returns the data file
 * verbatim, including its count.
 *
 * @return string[]
 */
function ssbd_capabilities() {
	$defaults   = array_values( (array) ssbd_site( 'capabilities', array() ) );
	$customized = false;
	$labels     = array();

	for ( $i = 0; $i < SSBD_CAPABILITY_SLOTS; $i++ ) {
		$label = get_theme_mod( 'ssbd_capability_' . $i, '' );

		if ( '' !== $label ) {
			$customized = true;
		}

		if ( '-' === trim( $label ) ) {
			continue;
		}

		$labels[] = '' !== $label ? $label : ( $defaults[ $i ] ?? '' );
	}

	if ( ! $customized ) {
		return $defaults;
	}

	$labels = array_values( array_filter(
		$labels,
		static function ( $label ) {
			return '' !== trim( $label );
		}
	) );

	/** Filter the resolved capability keywords. */
	return apply_filters( 'ssbd_capabilities', $labels );
}

/**
 * How many keywords the hero prints on the line under the buttons.
 *
 * Zero hides that line; the orbit and the strip keep the whole list.
 *
 * @return int
 */
function ssbd_hero_meta_count() {
	$count = (int) get_theme_mod( 'ssbd_hero_meta_count', 4 );

	return max( 0, min( SSBD_CAPABILITY_SLOTS, $count ) );
}

/**
 * The keywords the H1 cycles through, in the order they are shown.
 *
 * The same list again — orbit, strip, meta line, and now the rotating half of
 * the headline all read ssbd_capabilities(), so a site owner who edits the
 * keywords in the Customizer moves every one of them together.
 *
 * Every word is printed into the H1 and stays in the served HTML; only one is
 * visible at a time, and which one is a CSS class the browser moves. Nothing
 * about the headline's text depends on JavaScript running, which is the whole
 * point — a rotator that injects its words after load gives a crawler one
 * keyword instead of five.
 *
 * Returns an empty array when the rotator is switched off, or when there is
 * only one keyword to show: a "rotator" that never rotates is just a word,
 * and the template renders it as plain text in that case.
 *
 * @return string[]
 */
function ssbd_hero_rotator() {
	if ( ! get_theme_mod( 'ssbd_hero_rotator', true ) ) {
		return array();
	}

	$words = array_values( array_filter(
		ssbd_capabilities(),
		static function ( $word ) {
			return '' !== trim( (string) $word );
		}
	) );

	/** Filter the keywords the hero headline cycles through. */
	return (array) apply_filters( 'ssbd_hero_rotator', $words );
}

/**
 * How many stat slots the Customizer exposes.
 *
 * The grid is `repeat(auto-fit, minmax(160px, 1fr))`, so any count from one to
 * four lays out correctly without touching the CSS.
 */
const SSBD_STAT_SLOTS = 4;

/**
 * The stat pairs shown above the recent work grid.
 *
 * Reads the Customizer first and falls back to inc/data/site.php, matching how
 * every other editable string in the theme resolves. A slot with neither a
 * value nor a label is dropped rather than rendered as an empty tile.
 *
 * @return array<array{value:string,label:string}>
 */
function ssbd_stats() {
	$defaults  = (array) ssbd_site( 'stats', array() );
	$customized = false;
	$stats      = array();

	for ( $i = 0; $i < SSBD_STAT_SLOTS; $i++ ) {
		$value = get_theme_mod( 'ssbd_stat_value_' . $i, '' );
		$label = get_theme_mod( 'ssbd_stat_label_' . $i, '' );

		if ( '' !== $value || '' !== $label ) {
			$customized = true;
		}

		// A single dash removes a counter outright. Empty cannot mean that,
		// because empty is how the Customizer says "unset, use the default" —
		// so without this there would be no way to show fewer than the three
		// stats the data file ships.
		if ( '-' === trim( $value ) || '-' === trim( $label ) ) {
			continue;
		}

		$stats[ $i ] = array(
			'value' => '' !== $value ? $value : ( $defaults[ $i ]['value'] ?? '' ),
			'label' => '' !== $label ? $label : ( $defaults[ $i ]['label'] ?? '' ),
		);
	}

	// Untouched in the Customizer means "use the data file verbatim", including
	// its count — otherwise the fourth empty slot would start appearing.
	if ( ! $customized ) {
		return $defaults;
	}

	$stats = array_values( array_filter(
		$stats,
		static function ( $stat ) {
			return '' !== $stat['value'] || '' !== $stat['label'];
		}
	) );

	/** Filter the resolved front page stats. */
	return apply_filters( 'ssbd_stats', $stats );
}

/**
 * How many posts the Latest insights section shows.
 *
 * Four fills the row on a wide screen; three reads as a deliberate short list.
 *
 * @return int
 */
function ssbd_home_post_count() {
	$count = (int) get_theme_mod( 'ssbd_home_posts', 3 );

	/** Filter how many posts the front page lists. */
	return max( 1, min( 6, (int) apply_filters( 'ssbd_home_post_count', $count ) ) );
}

/**
 * The categories the Latest insights section is limited to.
 *
 * An empty list means every category. Stale ids — a category ticked here and
 * deleted later — are dropped, so a removed hub cannot silently empty the
 * section.
 *
 * @return int[]
 */
function ssbd_home_post_categories() {
	$stored = (string) get_theme_mod( 'ssbd_home_posts_cats', '' );

	// The control used to be a single-choice dropdown; honour what it saved.
	if ( '' === $stored ) {
		$stored = (string) max( 0, (int) get_theme_mod( 'ssbd_home_posts_cat', 0 ) );
	}

	$ids = array_filter( array_map( 'absint', explode( ',', $stored ) ) );

	$ids = array_filter(
		$ids,
		static function ( $id ) {
			return get_term( $id, 'category' ) instanceof WP_Term;
		}
	);

	return array_values( array_unique( $ids ) );
}

/**
 * The posts the Latest insights section shows, pinned first, then newest.
 *
 * "Stick to the top of the blog" in the post editor is the per-post switch for
 * "put this on the homepage", so an evergreen guide can hold its slot instead
 * of being pushed off by a fresher but less useful post. The remaining slots
 * fill by date, and a pinned post outside the chosen categories is skipped
 * like any other.
 *
 * @return int[]
 */
function ssbd_home_post_ids() {
	$count      = ssbd_home_post_count();
	$categories = ssbd_home_post_categories();

	$args = array(
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'suppress_filters'    => false,
		'fields'              => 'ids',
	);

	// 'cat' rather than 'category__in' so a hub brings its child categories
	// with it — a post filed under "Guides / Laravel" still counts as Guides.
	if ( $categories ) {
		$args['cat'] = implode( ',', $categories );
	}

	$pinned = array_values( array_filter( array_map( 'intval', (array) get_option( 'sticky_posts', array() ) ) ) );
	$ids    = array();

	if ( $pinned ) {
		$ids = get_posts( array_merge( $args, array(
			'post__in'       => $pinned,
			'posts_per_page' => $count,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) ) );
	}

	if ( count( $ids ) < $count ) {
		$ids = array_merge( $ids, get_posts( array_merge( $args, array(
			'posts_per_page' => $count - count( $ids ),
			'post__not_in'   => $ids,
		) ) ) );
	}

	/** Filter the posts listed on the front page. */
	return array_map( 'intval', (array) apply_filters( 'ssbd_home_post_ids', $ids ) );
}

/**
 * How many projects the Recent work section shows.
 *
 * @return int
 */
function ssbd_work_project_count() {
	$count = (int) get_theme_mod( 'ssbd_work_projects', 3 );

	return max( 1, min( 12, $count ) );
}
