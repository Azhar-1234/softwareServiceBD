<?php
/**
 * SEO metadata layer.
 *
 * Emits canonical, robots, description, Open Graph, Twitter Card, hreflang
 * and pagination rel links for every request type. Written so it stands on
 * its own, but yields to a dedicated SEO plugin (Yoast, Rank Math, SEOPress,
 * AIOSEO) if one is active — see ssbd_seo_plugin_active().
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ownership-verification meta tags for the platforms that use one.
 *
 * Google is verified by the file at the site root instead, which is why it is
 * absent here. Keyed by meta name so a second platform — Bing's msvalidate.01,
 * Yandex, whatever comes next — is one line rather than another hook.
 *
 * Printed on every page rather than just the front page: the platforms only
 * check the homepage, but a tag that exists everywhere cannot be missed by a
 * checker that follows a redirect or lands on a different entry point.
 *
 * @return array<string, string> Meta name => content.
 */
function ssbd_verification_tags() {
	/** Filter the platform ownership-verification meta tags. */
	return (array) apply_filters( 'ssbd_verification_tags', array(
		'p:domain_verify' => 'a4f021d9ff60a922f1335dec2c26a6b2', // Pinterest.
	) );
}

/**
 * Print the verification tags.
 *
 * Runs regardless of whether an SEO plugin is active: these are not the
 * descriptive metadata the theme stands down from, and an empty field in a
 * plugin's "Webmaster Tools" screen would otherwise leave the site unverified.
 */
function ssbd_print_verification_tags() {
	foreach ( ssbd_verification_tags() as $name => $content ) {
		if ( '' === (string) $content ) {
			continue;
		}

		printf(
			'<meta name="%s" content="%s">' . "\n",
			esc_attr( $name ),
			esc_attr( $content )
		);
	}
}
add_action( 'wp_head', 'ssbd_print_verification_tags', 2 );

/**
 * Send the old date-based post URLs to wherever the post lives now.
 *
 * The site launched on WordPress's default /%year%/%monthnum%/%day%/%postname%/
 * permalinks. Changing that structure leaves every already-published URL
 * pointing at nothing: the date prefix matches no rewrite rule under a
 * postname-based structure, so the request 404s, and core's
 * redirect_guess_404_permalink() cannot rescue it because no `name` query var
 * was ever parsed out of the URL.
 *
 * So: on a 404 that looks like YYYY/MM/DD/slug, find the post by that slug and
 * 301 to its current permalink. A 301 is what passes the indexing signal on,
 * and it costs nothing while the old structure is still in use — under the old
 * permalinks these URLs resolve normally and this never runs.
 */
function ssbd_redirect_dated_permalinks() {
	if ( ! is_404() || is_admin() ) {
		return;
	}

	$path = wp_parse_url( add_query_arg( array() ), PHP_URL_PATH );
	$path = is_string( $path ) ? trim( $path, '/' ) : '';

	// Anchored on both ends: only a bare date prefix plus one slug segment, so
	// this never hijacks a 404 that happens to contain numbers.
	if ( ! preg_match( '#^\d{4}/\d{1,2}/\d{1,2}/([^/]+)/?$#', $path, $matches ) ) {
		return;
	}

	$post = get_page_by_path( $matches[1], OBJECT, 'post' );

	if ( ! $post || 'publish' !== $post->post_status ) {
		return;
	}

	$permalink = get_permalink( $post );

	// A permalink that still carries the date means the structure has not
	// actually changed — redirecting would loop.
	if ( ! $permalink || trim( (string) wp_parse_url( $permalink, PHP_URL_PATH ), '/' ) === $path ) {
		return;
	}

	wp_safe_redirect( $permalink, 301 );
	exit;
}
add_action( 'template_redirect', 'ssbd_redirect_dated_permalinks' );

/**
 * Is a dedicated SEO plugin handling head output?
 *
 * @return bool
 */
function ssbd_seo_plugin_active() {
	$active = defined( 'WPSEO_VERSION' )
		|| class_exists( 'RankMath' )
		|| defined( 'SEOPRESS_VERSION' )
		|| defined( 'AIOSEO_VERSION' );

	/** Filter whether the theme should stand down from head output. */
	return (bool) apply_filters( 'ssbd_seo_plugin_active', $active );
}

/**
 * The description for the current request.
 *
 * Priority: per-post SEO description meta → GEO answer summary → excerpt →
 * trimmed content → site description.
 *
 * @return string
 */
function ssbd_meta_description() {
	$description = '';

	if ( is_singular() ) {
		$custom = get_post_meta( get_queried_object_id(), '_ssbd_meta_description', true );
		if ( $custom ) {
			$description = $custom;
		}
	}

	/*
	 * The blog index and the three legal pages ran their meta description all
	 * the way down to the site-wide fallback, because none of the branches
	 * below them ever fires for these pages: the blog listing is is_home(),
	 * not is_singular(), so the excerpt/content branch never runs on it, and
	 * the three legal pages ARE is_singular() but their post_content is empty
	 * — template-legal.php pulls its visible copy from inc/data/legal.php
	 * instead of the Page's own body, so there was nothing there to excerpt
	 * either. Four different URLs, one identical description, which is
	 * exactly the "duplicate meta description" a crawler flags.
	 *
	 * ssbd_current_page_key() collapses all three legal pages to one 'legal'
	 * key for FAQ and schema lookups, which is the right call there — they
	 * share one FAQ list — but reusing it here would just swap one duplicate
	 * for another. So these read their own already-written intro directly,
	 * keyed by the actual page slug, and the blog gets a description of its
	 * own written for this specific field.
	 */
	if ( ! $description && is_home() && ! is_front_page() ) {
		$description = ssbd_answer( 'blog' )['summary'] ?? '';
	}

	if ( ! $description && is_page( array( 'privacy-policy', 'terms', 'affiliate-disclosure' ) ) ) {
		$slug        = get_post_field( 'post_name', get_queried_object_id() );
		$description = ssbd_data( 'legal' )[ $slug ]['intro'] ?? '';
	}

	if ( ! $description ) {
		$answer = ssbd_answer( ssbd_current_page_key() );
		if ( $answer && ! empty( $answer['summary'] ) ) {
			$description = $answer['summary'];
		}
	}

	// A service's written summary beats the first 300 characters of its body,
	// and is there even before anyone writes the body.
	if ( ! $description && is_singular( 'ssbd_service' ) ) {
		$id          = get_queried_object_id();
		$description = (string) get_post_meta( $id, '_ssbd_desc', true )
			?: (string) get_post_meta( $id, '_ssbd_short', true );
	}

	if ( ! $description && is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$description = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
		}
	}

	if ( ! $description && ( is_category() || is_tag() || is_tax() ) ) {
		$description = term_description();
	}

	if ( ! $description && is_author() ) {
		$description = get_the_author_meta( 'description', get_queried_object_id() );
	}

	if ( ! $description ) {
		$description = ssbd_site( 'description', get_bloginfo( 'description' ) );
	}

	$description = wp_specialchars_decode( (string) $description, ENT_QUOTES );
	$description = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $description ) ) );
	$description = wp_html_excerpt( $description, 300, '' );

	// Trim to the last complete sentence or word so the snippet never breaks mid-word.
	if ( strlen( $description ) >= 300 ) {
		$description = preg_replace( '/\s+\S*$/', '', $description ) . '…';
	}

	/** Filter the meta description. */
	return apply_filters( 'ssbd_meta_description', $description );
}

/**
 * The description as it goes into a meta tag, capped to what search engines
 * actually display.
 *
 * ssbd_meta_description() returns the best *passage* for the request, and its
 * usual source is the page's GEO answer summary — deliberately 30–55 words,
 * because that is the length an answer engine can lift and quote. That is far
 * too long for a snippet: the home page shipped a 273-character description,
 * and Google truncates around 160.
 *
 * Two different jobs, so two different lengths. The full passage still feeds
 * the JSON-LD description and ai:summary, where length is an asset; only the
 * <meta name="description"> and og:description tags — and the description
 * handed to an SEO plugin — get the short form.
 *
 * @return string
 */
function ssbd_meta_description_tag() {
	$description = ssbd_meta_description();

	/** Filter the character budget for the description meta tag. */
	$limit = (int) apply_filters( 'ssbd_meta_description_length', 155 );

	if ( mb_strlen( $description ) <= $limit ) {
		return $description;
	}

	// Prefer ending on a sentence: a snippet that stops at a full stop reads
	// as written rather than as cut off.
	$clipped  = mb_substr( $description, 0, $limit );
	$sentence = max(
		(int) mb_strrpos( $clipped, '. ' ),
		(int) mb_strrpos( $clipped, '? ' ),
		(int) mb_strrpos( $clipped, '! ' )
	);

	if ( $sentence > $limit * 0.6 ) {
		return trim( mb_substr( $clipped, 0, $sentence + 1 ) );
	}

	$space = mb_strrpos( $clipped, ' ' );

	return rtrim( $space ? mb_substr( $clipped, 0, $space ) : $clipped, " ,;:-–—" ) . '…';
}

/**
 * Give the active SEO plugin a description when it has none of its own.
 *
 * The theme stands down from head output whenever an SEO plugin is active, on
 * the assumption that the plugin covers the same ground. Yoast does not: with
 * no description template configured and none typed on the page, it omits the
 * tag entirely rather than deriving one, and the page ships with no
 * <meta name="description"> at all — which is what Lighthouse reports.
 *
 * ssbd_meta_description() has already worked out a good answer for this
 * request (it is what feeds ai:summary and the JSON-LD abstract), so hand that
 * over as the fallback. Filtering the plugin rather than printing our own tag
 * keeps a single description in the document, and anything typed into the
 * plugin's own field still wins.
 *
 * @param string $description The plugin's description, possibly empty.
 * @return string
 */
function ssbd_seo_plugin_description( $description ) {
	if ( is_string( $description ) && '' !== trim( $description ) ) {
		return $description;
	}

	return ssbd_meta_description_tag();
}
add_filter( 'wpseo_metadesc', 'ssbd_seo_plugin_description' );          // Yoast.
add_filter( 'rank_math/frontend/description', 'ssbd_seo_plugin_description' );
add_filter( 'seopress_titles_desc', 'ssbd_seo_plugin_description' );
add_filter( 'aioseo_description', 'ssbd_seo_plugin_description' );

/**
 * Give the last breadcrumb an "item", which Yoast leaves off.
 *
 * Google's own breadcrumb documentation says the final list item may omit
 * `item`, since it is the page you are already on — and Yoast follows that.
 * Google's validator disagrees with Google's documentation: Search Console
 * reports "Missing field item" against those entries and marks them invalid,
 * which takes the page out of the running for a breadcrumb rich result.
 *
 * Filling it in satisfies the validator and costs nothing: the value is the
 * canonical URL of the page the crumb already refers to, so the statement is
 * true either way.
 *
 * @param array $data Breadcrumb schema piece.
 * @return array
 */
function ssbd_breadcrumb_last_item( $data ) {
	if ( empty( $data['itemListElement'] ) || ! is_array( $data['itemListElement'] ) ) {
		return $data;
	}

	$keys = array_keys( $data['itemListElement'] );
	$last = end( $keys );

	if ( is_array( $data['itemListElement'][ $last ] ) && empty( $data['itemListElement'][ $last ]['item'] ) ) {
		$data['itemListElement'][ $last ]['item'] = ssbd_canonical_url();
	}

	return $data;
}
add_filter( 'wpseo_schema_breadcrumb', 'ssbd_breadcrumb_last_item' );
add_filter( 'rank_math/snippet/breadcrumb', 'ssbd_breadcrumb_last_item' );

/**
 * Canonical URL for the current request.
 *
 * @return string
 */
function ssbd_canonical_url() {
	$canonical = '';

	if ( is_front_page() ) {
		$canonical = home_url( '/' );
	} elseif ( is_home() ) {
		$blog_id   = (int) get_option( 'page_for_posts' );
		$canonical = $blog_id ? get_permalink( $blog_id ) : home_url( '/' );
	} elseif ( is_singular() ) {
		$canonical = get_permalink();
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		$link = $term ? get_term_link( $term ) : '';
		$canonical = is_wp_error( $link ) ? '' : $link;
	} elseif ( is_post_type_archive() ) {
		$canonical = get_post_type_archive_link( get_query_var( 'post_type' ) );
	} elseif ( is_author() ) {
		$canonical = get_author_posts_url( get_queried_object_id() );
	} elseif ( is_search() ) {
		$canonical = get_search_link();
	} elseif ( is_year() || is_month() || is_day() ) {
		$canonical = home_url( add_query_arg( array() ) );
	}

	if ( ! $canonical ) {
		$canonical = home_url( '/' );
	}

	// Preserve pagination so page 2 does not canonicalize onto page 1.
	$paged = max( (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	if ( $paged > 1 && ! is_search() ) {
		$canonical = trailingslashit( $canonical ) . 'page/' . $paged . '/';
	}

	/** Filter the canonical URL. */
	return apply_filters( 'ssbd_canonical_url', $canonical );
}

/**
 * The share image for the current request.
 *
 * @return array{url:string,width:int,height:int,alt:string}
 */
function ssbd_share_image() {
	// Read the real dimensions rather than asserting 1200x630: Facebook and
	// LinkedIn both reject a share image whose declared size does not match
	// the file, and fall back to picking an arbitrary image from the page.
	$share_path = SSBD_DIR . '/assets/images/logo-full.png';
	$size       = is_readable( $share_path ) ? getimagesize( $share_path ) : false;

	$fallback = array(
		'url'    => SSBD_URI . '/assets/images/logo-full.png',
		'width'  => $size ? (int) $size[0] : 0,
		'height' => $size ? (int) $size[1] : 0,
		'alt'    => ssbd_site( 'legal_name', get_bloginfo( 'name' ) ),
	);

	if ( is_singular() && has_post_thumbnail() ) {
		$id  = get_post_thumbnail_id();
		$img = wp_get_attachment_image_src( $id, 'full' );
		if ( $img ) {
			return array(
				'url'    => $img[0],
				'width'  => (int) $img[1],
				'height' => (int) $img[2],
				'alt'    => get_post_meta( $id, '_wp_attachment_image_alt', true ) ?: get_the_title(),
			);
		}
	}

	$custom = get_theme_mod( 'ssbd_share_image' );
	if ( $custom ) {
		$fallback['url'] = $custom;
	}

	/** Filter the Open Graph / Twitter share image. */
	return apply_filters( 'ssbd_share_image', $fallback );
}

/**
 * Archives that exist for navigation but have nothing of their own to rank for.
 *
 * Three kinds, and each is a duplicate rather than a page:
 *
 * - Tag archives. A tag applied to one post is that post's headline and excerpt
 *   under a different URL. Thirty of them on a blog of five posts is thirty
 *   near-empty pages competing with the posts they point at, and it is where a
 *   small site's crawl budget goes instead of the articles.
 * - Author archives. With one author the archive is a copy of the blog index,
 *   at /author/admin/ — a URL that also publishes the administrator's login.
 * - The default category. WordPress's "Uncategorized" bucket is not an
 *   editorial section; the theme already hides it from every listing.
 *
 * noindex with follow, not nofollow: the pages stay useful to a reader browsing
 * by tag, and the links on them still pass through to the posts.
 *
 * @return bool
 */
function ssbd_is_thin_archive() {
	if ( is_tag() || is_author() || is_date() ) {
		return true;
	}

	if ( is_category() ) {
		$term = get_queried_object();

		return $term instanceof WP_Term && (int) $term->term_id === (int) get_option( 'default_category' );
	}

	return false;
}

/**
 * The robots directives for the current request.
 *
 * @return string
 */
function ssbd_robots_directives() {
	$directives = array( 'max-snippet:-1', 'max-image-preview:large', 'max-video-preview:-1' );

	/*
	 * The auth pages are kept out of the index: a sign-in form has nothing to
	 * rank for, and a "Login" result under the brand name is the wrong door to
	 * offer someone searching for the company.
	 */
	$is_auth = is_page() && in_array( get_post_field( 'post_name', get_queried_object_id() ), array( 'login', 'register' ), true );

	if ( is_search() || is_404() || $is_auth || ssbd_is_thin_archive() || ( is_paged() && is_front_page() ) ) {
		array_unshift( $directives, 'noindex', 'follow' );
	} else {
		array_unshift( $directives, 'index', 'follow' );
	}

	/**
	 * Filter robots directives.
	 *
	 * max-snippet:-1 is deliberate: it lets Google and AI Overviews quote a
	 * full passage rather than a clipped one, which is the single biggest
	 * lever on whether an answer engine cites the page.
	 */
	return implode( ', ', apply_filters( 'ssbd_robots_directives', $directives ) );
}

/**
 * Shape the <title>.
 *
 * The front page gets "Business Name — Tagline" rather than the bare site
 * name, which is what shows in the SERP and in an answer engine's source
 * card. Interior pages keep "Page — Business Name".
 *
 * @param array $parts Title parts.
 * @return array
 */
function ssbd_document_title_parts( $parts ) {
	if ( ssbd_seo_plugin_active() ) {
		return $parts;
	}

	$name = ssbd_site( 'legal_name', get_bloginfo( 'name' ) );

	if ( is_front_page() && is_home() || is_front_page() ) {
		$parts['title']   = $name;
		$parts['tagline'] = ssbd_site( 'tagline', get_bloginfo( 'description' ) );
		unset( $parts['site'] );
	} else {
		$parts['site'] = $name;
		unset( $parts['tagline'] );
	}

	return $parts;
}
add_filter( 'document_title_parts', 'ssbd_document_title_parts' );

/**
 * Use an en dash between title parts — it reads better and survives
 * truncation in the SERP more gracefully than a pipe.
 *
 * @return string
 */
function ssbd_document_title_separator() {
	return ssbd_seo_plugin_active() ? '-' : '—';
}
add_filter( 'document_title_separator', 'ssbd_document_title_separator' );

/**
 * Print the head metadata.
 */
function ssbd_head_meta() {
	if ( ssbd_seo_plugin_active() ) {
		return;
	}

	// wp_get_document_title() returns texturized HTML entities; decode once
	// so esc_attr() does not emit &#038;amp; into the meta tags.
	$title       = wp_specialchars_decode( wp_get_document_title(), ENT_QUOTES );
	$description = ssbd_meta_description_tag();
	$canonical   = ssbd_canonical_url();
	$image       = ssbd_share_image();
	$site_name   = ssbd_site( 'legal_name', get_bloginfo( 'name' ) );
	$locale      = get_locale();

	echo "\n<!-- Software Service BD :: SEO -->\n";

	printf( '<meta name="robots" content="%s">' . "\n", esc_attr( ssbd_robots_directives() ) );
	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
	printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $canonical ) );

	// Open Graph.
	printf( '<meta property="og:locale" content="%s">' . "\n", esc_attr( $locale ) );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( $site_name ) );
	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( is_singular( 'post' ) ? 'article' : 'website' ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $canonical ) );
	printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image['url'] ) );
	printf( '<meta property="og:image:alt" content="%s">' . "\n", esc_attr( $image['alt'] ) );
	if ( ! empty( $image['width'] ) ) {
		printf( '<meta property="og:image:width" content="%d">' . "\n", (int) $image['width'] );
		printf( '<meta property="og:image:height" content="%d">' . "\n", (int) $image['height'] );
	}

	if ( is_singular( 'post' ) ) {
		printf( '<meta property="article:published_time" content="%s">' . "\n", esc_attr( get_the_date( DATE_W3C ) ) );
		printf( '<meta property="article:modified_time" content="%s">' . "\n", esc_attr( get_the_modified_date( DATE_W3C ) ) );
		printf( '<meta property="article:author" content="%s">' . "\n", esc_attr( get_the_author() ) );
		foreach ( get_the_category() ?: array() as $category ) {
			printf( '<meta property="article:section" content="%s">' . "\n", esc_attr( $category->name ) );
		}
		// get_the_tags() returns false — not an empty array — on an untagged
		// post, and (array) false is array( false ), not array().
		foreach ( get_the_tags() ?: array() as $tag ) {
			printf( '<meta property="article:tag" content="%s">' . "\n", esc_attr( $tag->name ) );
		}
	}

	// Twitter / X.
	printf( '<meta name="twitter:card" content="summary_large_image">' . "\n" );
	printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $description ) );
	printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image['url'] ) );
	$handle = ssbd_site( 'social.x' );
	if ( $handle ) {
		printf( '<meta name="twitter:site" content="@%s">' . "\n", esc_attr( ltrim( $handle, '@' ) ) );
	}

	// Pagination hints for archives.
	if ( is_archive() || is_home() || is_search() ) {
		global $wp_query;
		$paged = max( 1, (int) get_query_var( 'paged' ) );
		if ( $paged > 1 ) {
			printf( '<link rel="prev" href="%s">' . "\n", esc_url( get_pagenum_link( $paged - 1 ) ) );
		}
		if ( $paged < (int) $wp_query->max_num_pages ) {
			printf( '<link rel="next" href="%s">' . "\n", esc_url( get_pagenum_link( $paged + 1 ) ) );
		}
	}

	printf( '<meta name="author" content="%s">' . "\n", esc_attr( is_singular( 'post' ) ? ssbd_author_name() : $site_name ) );
	printf( '<meta name="theme-color" content="#1E8156" media="(prefers-color-scheme: light)">' . "\n" );
	printf( '<meta name="theme-color" content="#0A1220" media="(prefers-color-scheme: dark)">' . "\n" );
}
add_action( 'wp_head', 'ssbd_head_meta', 1 );

/**
 * A meta description field on posts and pages, so per-URL snippets can be
 * tuned without a plugin.
 */
function ssbd_add_meta_box() {
	if ( ssbd_seo_plugin_active() ) {
		return;
	}

	add_meta_box(
		'ssbd-seo',
		__( 'SEO & Answer Engine', 'ssbd' ),
		'ssbd_render_meta_box',
		array( 'post', 'page' ),
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'ssbd_add_meta_box' );

/**
 * Render the SEO meta box.
 *
 * @param WP_Post $post Post being edited.
 */
function ssbd_render_meta_box( $post ) {
	wp_nonce_field( 'ssbd_seo_save', 'ssbd_seo_nonce' );

	$description = get_post_meta( $post->ID, '_ssbd_meta_description', true );
	$answer      = get_post_meta( $post->ID, '_ssbd_answer_summary', true );
	?>
	<p>
		<label for="ssbd_meta_description"><strong><?php esc_html_e( 'Meta description', 'ssbd' ); ?></strong></label><br>
		<textarea id="ssbd_meta_description" name="ssbd_meta_description" rows="3" class="large-text" maxlength="300"><?php echo esc_textarea( $description ); ?></textarea>
		<span class="description"><?php esc_html_e( 'Up to ~155 characters shows in Google. Leave empty to auto-generate from the excerpt.', 'ssbd' ); ?></span>
	</p>
	<p>
		<label for="ssbd_answer_summary"><strong><?php esc_html_e( 'Direct answer summary (GEO)', 'ssbd' ); ?></strong></label><br>
		<textarea id="ssbd_answer_summary" name="ssbd_answer_summary" rows="4" class="large-text"><?php echo esc_textarea( $answer ); ?></textarea>
		<span class="description"><?php esc_html_e( 'A self-contained 40–60 word answer to this page\'s core question. Rendered near the top of the post and mirrored into JSON-LD so AI answer engines can quote it.', 'ssbd' ); ?></span>
	</p>
	<?php
}

/**
 * Save the SEO meta box.
 *
 * @param int $post_id Post ID.
 */
function ssbd_save_meta_box( $post_id ) {
	if ( ! isset( $_POST['ssbd_seo_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['ssbd_seo_nonce'] ) ), 'ssbd_seo_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'_ssbd_meta_description' => 'ssbd_meta_description',
		'_ssbd_answer_summary'   => 'ssbd_answer_summary',
	);

	foreach ( $fields as $meta_key => $field ) {
		$value = isset( $_POST[ $field ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) : '';
		if ( '' === $value ) {
			delete_post_meta( $post_id, $meta_key );
		} else {
			update_post_meta( $post_id, $meta_key, $value );
		}
	}
}
add_action( 'save_post', 'ssbd_save_meta_box' );

/**
 * Never let post content introduce a second <h1>.
 *
 * Every template that prints the_content() — single.php, single-ssbd_service.php
 * — already prints its own <h1> from the post title above it. That is
 * supposed to be the only one: two <h1> tags on one page is a basic on-page
 * SEO fault, and it happened here the ordinary way content faults happen —
 * an editor pasted the Brand Promotion service's body in from somewhere that
 * used its own top-level heading, dragging a second <h1> into the post body
 * with it.
 *
 * Fixing the one page in wp-admin fixes the one page. This fixes the class of
 * mistake: whatever lands in a Heading 1 block from here on renders as an
 * <h2>, so the page never has more than one <h1> regardless of what a paste
 * carries in. It only touches the rendered HTML — the stored content, and
 * what the block editor shows as "Heading 1", are unchanged.
 *
 * @param string $content Rendered post content.
 * @return string
 */
function ssbd_demote_content_h1( $content ) {
	if ( false === stripos( $content, '<h1' ) ) {
		return $content;
	}

	return (string) preg_replace(
		array( '/<h1(\s[^>]*)?>/i', '/<\/h1>/i' ),
		array( '<h2$1>', '</h2>' ),
		$content
	);
}
add_filter( 'the_content', 'ssbd_demote_content_h1', 20 );

/* -------------------------------------------------------------------------
 * XML sitemap hygiene
 *
 * A sitemap is a request to index. Listing a URL there and then serving it
 * with noindex is a contradiction the crawler has to resolve by fetching the
 * page — which is exactly the crawl this is trying to avoid. So everything
 * ssbd_is_thin_archive() hides from the index comes out of the sitemap too.
 *
 * On a small site this matters more than it looks. Google's "Discovered —
 * currently not indexed" queue is finite attention: thirty tag URLs and an
 * author archive sitting in it are thirty-one things being weighed ahead of
 * the articles and service pages that were meant to rank.
 * ---------------------------------------------------------------------- */

/**
 * Drop the author sitemap.
 *
 * @param WP_Sitemaps_Provider $provider Provider instance.
 * @param string               $name     Provider name.
 * @return WP_Sitemaps_Provider|false
 */
function ssbd_sitemap_providers( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'ssbd_sitemap_providers', 10, 2 );

/**
 * Drop tag archives from the sitemap, keeping real categories.
 *
 * @param array $taxonomies Taxonomy objects keyed by name.
 * @return array
 */
function ssbd_sitemap_taxonomies( $taxonomies ) {
	unset( $taxonomies['post_tag'] );

	return $taxonomies;
}
add_filter( 'wp_sitemaps_taxonomies', 'ssbd_sitemap_taxonomies' );

/**
 * Keep the default "Uncategorized" category out of the sitemap.
 *
 * @param array  $args     Term query arguments.
 * @param string $taxonomy Taxonomy name.
 * @return array
 */
function ssbd_sitemap_term_args( $args, $taxonomy ) {
	if ( 'category' === $taxonomy ) {
		$args['exclude'] = array_merge(
			(array) ( $args['exclude'] ?? array() ),
			array( (int) get_option( 'default_category' ) )
		);
	}

	return $args;
}
add_filter( 'wp_sitemaps_taxonomies_query_args', 'ssbd_sitemap_term_args', 10, 2 );

/* ==========================================================================
   Retired tag archives
   ========================================================================== */

/**
 * Tag archives are off the site.
 *
 * The tags themselves were never written by hand — a plugin derived them from
 * each post's wording — so /tag/domain/, /tag/traffic/ and thirty-eight more
 * were pages nobody had authored. Each one listed a post or two under the
 * site's own description, because a generated tag has no description of its
 * own to serve as one, and a crawler reads forty pages carrying the same
 * description as forty duplicates.
 *
 * noindex alone did not settle it: the pages were still linked from every
 * article, still crawled, and still counted. Withdrawing the archive is the
 * honest version of the same decision — the URL stops existing rather than
 * existing and asking to be ignored.
 *
 * The admin screen stays, so the terms can be reviewed and deleted under
 * Posts > Tags, and so does the editor panel: a tag is still a reasonable
 * thing to record against a post, it just no longer publishes a page.
 *
 * @param array  $args     Taxonomy arguments.
 * @param string $taxonomy Taxonomy name.
 * @return array
 */
function ssbd_retire_tag_archives( $args, $taxonomy ) {
	if ( 'post_tag' !== $taxonomy ) {
		return $args;
	}

	$args['public']             = false;
	$args['publicly_queryable'] = false;
	$args['rewrite']            = false;
	$args['show_in_nav_menus']  = false;

	// public => false would otherwise take the admin UI down with it, and the
	// existing terms have to be reachable to be cleared out.
	$args['show_ui']           = true;
	$args['show_in_menu']      = true;
	$args['show_in_rest']      = true;
	$args['show_admin_column'] = true;

	return $args;
}
add_filter( 'register_taxonomy_args', 'ssbd_retire_tag_archives', 10, 2 );

/**
 * The first path segment tag archives used to live under.
 *
 * @return string
 */
function ssbd_tag_archive_base() {
	$base = trim( (string) get_option( 'tag_base' ), '/' );

	return '' !== $base ? $base : 'tag';
}

/**
 * Is this request for one of the withdrawn tag URLs?
 *
 * @return bool
 */
function ssbd_is_retired_tag_request() {
	$home = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	$path = trim( (string) wp_parse_url( ssbd_current_url(), PHP_URL_PATH ), '/' );

	// A site in a subdirectory carries that directory in every path.
	if ( '' !== $home && str_starts_with( $path, $home . '/' ) ) {
		$path = trim( substr( $path, strlen( $home ) ), '/' );
	}

	$base = ssbd_tag_archive_base();

	return $path === $base || str_starts_with( $path, $base . '/' );
}

/**
 * Answer the withdrawn tag URLs with 410 Gone rather than 404.
 *
 * Both keep the page out of the index; 410 is the one that says the removal
 * was deliberate, and Google drops a 410 in a single visit where a 404 is
 * re-checked for weeks in case it was a mistake. Forty URLs is worth the
 * distinction.
 */
function ssbd_tag_archives_gone() {
	if ( ! is_404() || ! ssbd_is_retired_tag_request() ) {
		return;
	}

	status_header( 410 );
	nocache_headers();
}
add_action( 'template_redirect', 'ssbd_tag_archives_gone', 1 );

/**
 * Stop WordPress guessing a replacement for a withdrawn tag URL.
 *
 * Left alone, /tag/domain/ is close enough to a post called "Domain" that
 * core redirects to it — which would turn a deliberate removal into a
 * misleading 301 and put the URL back in the index.
 *
 * @param bool $guess Whether to attempt the guess.
 * @return bool
 */
function ssbd_no_guess_for_retired_tags( $guess ) {
	return ssbd_is_retired_tag_request() ? false : $guess;
}
add_filter( 'do_redirect_guess_404_permalink', 'ssbd_no_guess_for_retired_tags' );
