<?php
/**
 * GEO layer — two senses of the word, both of which this site needs:
 *
 *  1. Geographic / local SEO: geo.region, geo.placename, geo.position and
 *     ICBM meta tags, plus the LocalBusiness address and service-area data
 *     that Google Business Profile and local packs read.
 *
 *  2. Generative Engine Optimization: making the site quotable by AI answer
 *     engines. That means explicit crawler permissions, an /llms.txt manifest,
 *     visible self-contained answer passages, and last-reviewed dates so an
 *     engine can judge freshness.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Geographic meta tags.
 *
 * Printed on every page — local intent queries ("web developer in Feni",
 * "Laravel developer Bangladesh") are resolved partly from these.
 */
function ssbd_geo_meta() {
	$geo     = ssbd_site( 'geo', array() );
	$address = ssbd_site( 'address', array() );

	if ( empty( $geo['latitude'] ) || empty( $geo['longitude'] ) ) {
		return;
	}

	echo "\n<!-- Software Service BD :: GEO (local) -->\n";

	printf( '<meta name="geo.region" content="%s">' . "\n", esc_attr( $geo['region'] ) );
	printf( '<meta name="geo.placename" content="%s">' . "\n", esc_attr( $geo['placename'] ) );
	printf( '<meta name="geo.position" content="%s;%s">' . "\n", esc_attr( $geo['latitude'] ), esc_attr( $geo['longitude'] ) );
	printf( '<meta name="ICBM" content="%s, %s">' . "\n", esc_attr( $geo['latitude'] ), esc_attr( $geo['longitude'] ) );

	if ( ! empty( $address['country'] ) ) {
		printf( '<meta name="geo.country" content="%s">' . "\n", esc_attr( $address['country'] ) );
	}

	printf(
		'<meta name="business.contact_data.locality" content="%s">' . "\n",
		esc_attr( $address['locality'] ?? '' )
	);
	printf(
		'<meta name="business.contact_data.country_name" content="%s">' . "\n",
		esc_attr( $address['country'] ?? '' )
	);

	// Open Graph place extensions — used by Facebook's local surfaces.
	printf( '<meta property="place:location:latitude" content="%s">' . "\n", esc_attr( $geo['latitude'] ) );
	printf( '<meta property="place:location:longitude" content="%s">' . "\n", esc_attr( $geo['longitude'] ) );
	printf( '<meta property="business:contact_data:locality" content="%s">' . "\n", esc_attr( $address['locality'] ?? '' ) );
	printf( '<meta property="business:contact_data:country_name" content="%s">' . "\n", esc_attr( $address['country'] ?? '' ) );
	if ( ! empty( ssbd_site( 'email' ) ) ) {
		printf( '<meta property="business:contact_data:email" content="%s">' . "\n", esc_attr( ssbd_site( 'email' ) ) );
	}
}
add_action( 'wp_head', 'ssbd_geo_meta', 2 );

/**
 * Generative-engine metadata.
 *
 * `citation-*` and `last-reviewed` are not ranking factors in the classic
 * sense; they are attribution hints. Answer engines that surface a source
 * card use them to label who is being quoted and how current the claim is.
 */
function ssbd_geo_ai_meta() {
	$site = ssbd_site();

	echo "\n<!-- Software Service BD :: GEO (generative engines) -->\n";

	printf( '<meta name="citation_publisher" content="%s">' . "\n", esc_attr( $site['legal_name'] ) );

	if ( is_singular() ) {
		printf( '<meta name="citation_title" content="%s">' . "\n", esc_attr( get_the_title() ) );
		printf( '<meta name="citation_publication_date" content="%s">' . "\n", esc_attr( get_the_date( 'Y/m/d' ) ) );
		printf( '<meta name="last-reviewed" content="%s">' . "\n", esc_attr( get_the_modified_date( 'Y-m-d' ) ) );
		if ( is_singular( 'post' ) ) {
			printf( '<meta name="citation_author" content="%s">' . "\n", esc_attr( get_the_author() ) );
		}
	} else {
		// get_lastpostmodified() returns a MySQL datetime string, not a timestamp.
		$modified = get_lastpostmodified( 'GMT', 'page' );
		$reviewed = $modified ? substr( $modified, 0, 10 ) : gmdate( 'Y-m-d' );

		printf( '<meta name="last-reviewed" content="%s">' . "\n", esc_attr( $reviewed ) );
	}

	// A one-line, quotable statement of what this page answers.
	$answer = ssbd_answer( ssbd_current_page_key() );
	if ( $answer && ! empty( $answer['summary'] ) ) {
		printf( '<meta name="ai:summary" content="%s">' . "\n", esc_attr( $answer['summary'] ) );
	}

	printf( '<link rel="alternate" type="text/plain" href="%s" title="LLM-readable site summary">' . "\n", esc_url( home_url( '/llms.txt' ) ) );
}
add_action( 'wp_head', 'ssbd_geo_ai_meta', 3 );

/**
 * The lede paragraph for a page.
 *
 * This is the GEO answer passage and the page's opening copy at once. Answer
 * engines extract from rendered text, so the first prose on the page is
 * exactly where it needs to be — it never needed a card around it.
 *
 * @param string $key      Page key.
 * @param string $fallback Copy to use when the page has no answer entry.
 * @return string
 */
function ssbd_lede( $key, $fallback = '' ) {
	$answer = ssbd_answer( $key );

	return ( $answer && ! empty( $answer['summary'] ) ) ? $answer['summary'] : $fallback;
}

/**
 * Paths withheld from crawlers in robots.txt. Empty, deliberately.
 *
 * The two candidates were the search pages and the /go/ affiliate cloak, and
 * both are already handled a layer down, where the handling is strictly
 * better:
 *
 *   /go/     sends "X-Robots-Tag: noindex, nofollow" on the redirect itself
 *            (inc/affiliates.php), and every affiliate link is rendered with
 *            rel="sponsored nofollow noopener".
 *   /?s=     is served with "noindex, follow" (ssbd_robots_directives()).
 *
 * Blocking either in robots.txt would make that worse rather than better.
 * Google has to fetch a URL to see a noindex directive, so a robots.txt
 * Disallow hides the very instruction that does the work: the URL stays
 * eligible to appear as a bare link if anything points at it, and Google
 * never learns it was asked to drop it. Google's own documentation says not
 * to combine the two.
 *
 * Crawl budget is the usual argument for blocking an infinite space like site
 * search anyway. On a site of this size there is no budget pressure to spend.
 *
 * Filter this to add a path that genuinely has no on-page way to say no —
 * and if you do, remember every named user-agent group has to repeat it,
 * because a crawler obeys only its own best-matching group.
 *
 * @return string[] Disallow lines, without the trailing newline.
 */
function ssbd_robots_disallows() {
	/** Filter the paths disallowed for every crawler. Empty by default. */
	$paths = (array) apply_filters( 'ssbd_robots_disallows', array() );

	return array_map(
		static function ( $path ) {
			return 'Disallow: ' . $path;
		},
		$paths
	);
}

/**
 * Explicitly welcome AI crawlers in robots.txt.
 *
 * Being crawlable by GPTBot, ClaudeBot, PerplexityBot and friends is the
 * precondition for being cited by them. Set the `ssbd_allow_ai_crawlers`
 * filter to false to opt out instead.
 *
 * @param string $output Existing robots.txt body.
 * @param string $public Whether the site is public.
 * @return string
 */
function ssbd_robots_txt( $output, $public ) {
	if ( '1' !== (string) $public ) {
		return $output;
	}

	// Core's default output is a `User-agent: *` group that this function
	// re-emits below in full. Appending to it would leave two groups for the
	// same agent, so it is replaced rather than extended.
	$lines = array();

	/*
	 * Off by default. The wildcard group below already allows everything, so
	 * a group per bot repeats it — and worse, naming a bot removes it from
	 * "User-agent: *" entirely under RFC 9309, so any rule added to the
	 * wildcard later would silently not apply to the fifteen crawlers most
	 * worth applying it to. Filter this true to publish the list as an
	 * explicit, human-readable statement instead.
	 */
	if ( apply_filters( 'ssbd_allow_ai_crawlers', false ) ) {
		$bots = array(
			'GPTBot',            // OpenAI training.
			'OAI-SearchBot',     // ChatGPT search index.
			'ChatGPT-User',      // ChatGPT live fetch.
			'ClaudeBot',         // Anthropic.
			'Claude-Web',
			'anthropic-ai',
			'PerplexityBot',
			'Perplexity-User',
			'Google-Extended',   // Gemini / AI Overviews grounding.
			'Applebot-Extended',
			'Bingbot',
			'CCBot',             // Common Crawl.
			'Meta-ExternalAgent',
			'Amazonbot',
			'cohere-ai',
		);

		/*
		 * Allow: / plus the same Disallow list the wildcard group carries.
		 * Naming a bot takes it out of "User-agent: *" completely, so without
		 * these three lines every crawler listed here — Bingbot and CCBot
		 * included — was free to crawl the search pages and walk the /go/
		 * affiliate redirects, which is the opposite of what naming them was
		 * meant to achieve.
		 */
		$disallows = ssbd_robots_disallows();

		$lines[] = '# AI and answer-engine crawlers are welcome to index and cite this site.';
		foreach ( $bots as $bot ) {
			$lines[] = 'User-agent: ' . $bot;
			$lines[] = 'Allow: /';

			foreach ( $disallows as $disallow ) {
				$lines[] = $disallow;
			}

			$lines[] = '';
		}
	}

	/*
	 * The site-wide group is NOT emitted here. It used to be, together with
	 * the "Disallow: /wp-admin/ + Allow: /wp-admin/admin-ajax.php" pair — and
	 * Yoast recognises that exact pair as WordPress's default block and strips
	 * it, taking the "User-agent: *" line above it as well. Everything the
	 * theme had declared under that heading was then orphaned: the served file
	 * handed those rules to whichever agent happened to be named last
	 * (cohere-ai), while Googlebot matched Yoast's own empty "Disallow:" and
	 * was free to crawl the search pages and the affiliate cloak.
	 *
	 * So the group is appended after every other filter instead — see
	 * ssbd_robots_site_rules(). wp-admin is left out entirely: WordPress
	 * protects it with headers, and repeating it is what triggered the strip.
	 */

	// Only when nothing else is publishing one. Yoast serves the real index at
	// /sitemap_index.xml and advertises it in its own block; the core URL the
	// theme used to print is a 301 to that, so declaring it here only adds a
	// second, redirecting sitemap for a crawler to reconcile.
	if ( ! ssbd_seo_plugin_active() ) {
		$lines[] = 'Sitemap: ' . home_url( '/wp-sitemap.xml' );
	}

	// A comment, not a directive. RFC 9309 defines User-agent, Allow and
	// Disallow; Sitemap is a de-facto extension every major crawler honours.
	// "LLM-Manifest:" is neither, so parsers report the whole file as
	// malformed — Lighthouse fails its robots.txt audit on it — and a crawler
	// that rejects the file may ignore the rules above it too. The manifest is
	// already discoverable from the <link rel="alternate"> in <head>, so
	// nothing is lost by leaving this as a human-readable pointer.
	$lines[] = '# LLM manifest: ' . home_url( '/llms.txt' );

	return implode( "\n", $lines ) . "\n";
}
add_filter( 'robots_txt', 'ssbd_robots_txt', 10, 2 );

/**
 * Append the site-wide crawl rules, last.
 *
 * Priority 99 so this lands after any SEO plugin has finished rewriting the
 * file: a group that is already the final one cannot be orphaned by something
 * removing a heading above it.
 *
 * A second "User-agent: *" group alongside a plugin's own is intentional and
 * correct — RFC 9309 has crawlers merge the rules of every group that matches
 * them, and an empty "Disallow:" contributes no restriction, so the result is
 * exactly these paths blocked and nothing else.
 *
 * @param string $output robots.txt body.
 * @return string
 */
function ssbd_robots_site_rules( $output ) {
	$rules = array_merge(
		array( '', 'User-agent: *', 'Allow: /' ),
		ssbd_robots_disallows()
	);

	/** Filter the site-wide robots.txt disallow rules. */
	$rules = (array) apply_filters( 'ssbd_robots_site_rules', $rules );

	return $output . implode( "\n", $rules ) . "\n";
}
add_filter( 'robots_txt', 'ssbd_robots_site_rules', 99 );

/**
 * Register the /llms.txt route.
 *
 * llms.txt is an emerging convention: a plain-text, link-dense map of the
 * site written for language models rather than for browsers. It costs almost
 * nothing and gives an engine a clean entry point instead of making it guess
 * the site structure from rendered HTML.
 */
function ssbd_llms_txt_rewrite() {
	add_rewrite_rule( '^llms\.txt$', 'index.php?ssbd_llms=1', 'top' );
}
add_action( 'init', 'ssbd_llms_txt_rewrite' );

/**
 * Register the query var backing /llms.txt.
 *
 * @param array $vars Query vars.
 * @return array
 */
function ssbd_llms_query_var( $vars ) {
	$vars[] = 'ssbd_llms';
	return $vars;
}
add_filter( 'query_vars', 'ssbd_llms_query_var' );

/**
 * Stop WordPress canonicalising /llms.txt into /llms.txt/.
 *
 * redirect_canonical() does not know the rewrite rule points at a plain-text
 * file, so it 301s to a trailing slash. Crawlers do follow it, but a redirect
 * on a manifest file is needless friction.
 *
 * @param string $redirect The URL core wants to redirect to.
 * @return string|false
 */
function ssbd_no_canonical_redirect_for_llms( $redirect ) {
	return get_query_var( 'ssbd_llms' ) ? false : $redirect;
}
add_filter( 'redirect_canonical', 'ssbd_no_canonical_redirect_for_llms' );

/**
 * Serve /llms.txt.
 */
function ssbd_render_llms_txt() {
	if ( ! get_query_var( 'ssbd_llms' ) ) {
		return;
	}

	$site = ssbd_site();

	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'X-Robots-Tag: index, follow' );

	$out   = array();
	$out[] = '# ' . $site['legal_name'];
	$out[] = '';
	$out[] = '> ' . $site['description'];
	$out[] = '';
	$out[] = 'Location: ' . $site['address']['locality'] . ', ' . $site['address']['region'] . ', Bangladesh';
	$out[] = 'Serves: ' . implode( ', ', $site['areas_served'] );
	$out[] = 'Contact: ' . $site['email'];
	$out[] = 'Last updated: ' . gmdate( 'Y-m-d' );
	$out[] = '';

	$out[] = '## Services';
	$out[] = '';
	foreach ( ssbd_services() as $service ) {
		$out[] = sprintf( '- [%s](%s): %s', $service['title'], ssbd_service_url( $service ), $service['desc'] );
	}
	$out[] = '';

	$out[] = '## Key pages';
	$out[] = '';
	foreach ( ssbd_page_blueprint() as $slug => $spec ) {
		$answer  = ssbd_answer( $slug );
		$summary = $answer['summary'] ?? '';
		$out[]   = sprintf( '- [%s](%s)%s', $spec['title'], ssbd_page_url( $slug ), $summary ? ': ' . $summary : '' );
	}
	$out[] = '';

	$posts = get_posts( array(
		'numberposts' => 30,
		'post_status' => 'publish',
	) );

	if ( $posts ) {
		$out[] = '## Articles';
		$out[] = '';
		foreach ( $posts as $post ) {
			$excerpt = wp_strip_all_tags( get_the_excerpt( $post ) );
			$out[]   = sprintf( '- [%s](%s): %s', get_the_title( $post ), get_permalink( $post ), wp_html_excerpt( $excerpt, 160, '…' ) );
		}
		$out[] = '';
	}

	$out[] = '## Frequently asked questions';
	$out[] = '';
	foreach ( ssbd_data( 'faqs' ) as $group ) {
		foreach ( $group as $faq ) {
			$out[] = '### ' . $faq['q'];
			$out[] = $faq['a'];
			$out[] = '';
		}
	}

	$out[] = '## Usage';
	$out[] = '';
	$out[] = 'This content may be quoted and cited with attribution to ' . $site['legal_name'] . ' (' . home_url( '/' ) . ').';

	echo implode( "\n", array_map( 'wp_strip_all_tags', $out ) ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- plain-text response.
	exit;
}
add_action( 'template_redirect', 'ssbd_render_llms_txt' );

/**
 * Flush rewrite rules once so /llms.txt resolves after activation.
 */
function ssbd_flush_rewrites() {
	ssbd_llms_txt_rewrite();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'ssbd_flush_rewrites', 20 );
