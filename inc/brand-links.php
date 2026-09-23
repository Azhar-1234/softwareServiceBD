<?php
/**
 * Turn written mentions of the business name into links to the home page.
 *
 * An internal link only helps if it is actually there, and asking every
 * author to remember to link the brand by hand is a rule that gets forgotten
 * by the second paragraph. This finds the name in the rendered page and links
 * it, so a mention anywhere — a post, a service page, one of the sections the
 * theme builds from inc/data.php — points home on its own.
 *
 * It works on the finished HTML rather than on the_content because most of
 * this site's prose never passes through the_content: the front page, the
 * service layouts and the footer are all template output. Buffering the body
 * is the only place that sees all of it at once.
 *
 * Only text is rewritten. Tag names and attributes are never touched, and
 * anything inside a link, a heading, code, a script or a form control is left
 * exactly as it was — see ssbd_brand_link_skip_tags().
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is the automatic brand linking switched on?
 *
 * Off on the home page itself: a link from a page to itself carries no
 * signal and reads as a dead end to anyone who follows it.
 *
 * @return bool
 */
function ssbd_brand_links_enabled() {
	$enabled = (bool) get_theme_mod( 'ssbd_brand_links', true )
		&& ! is_front_page()
		&& ! is_admin()
		&& ! is_feed()
		&& ! is_embed();

	/** Filter whether brand mentions are linked on this request. */
	return (bool) apply_filters( 'ssbd_brand_links_enabled', $enabled );
}

/**
 * The names to look for, longest first.
 *
 * The registered legal name and the site title are usually the same string;
 * where they differ, both are worth catching. Longest first so "Software
 * Service BD Ltd." wins over "Software Service BD" when both are listed and
 * the longer one is what the sentence actually says.
 *
 * @return string[]
 */
function ssbd_brand_link_phrases() {
	$phrases = array(
		(string) ssbd_site( 'legal_name', '' ),
		(string) get_bloginfo( 'name' ),
	);

	/** Filter the names linked to the home page. */
	$phrases = (array) apply_filters( 'ssbd_brand_link_phrases', $phrases );

	$phrases = array_values( array_unique( array_filter( array_map( 'trim', $phrases ) ) ) );

	usort(
		$phrases,
		static function ( $a, $b ) {
			return strlen( $b ) <=> strlen( $a );
		}
	);

	return $phrases;
}

/**
 * Elements whose text is left alone.
 *
 * A link inside a link is invalid and breaks the outer one, so `a` is the
 * load-bearing entry here. The headings are a house-style choice rather than
 * a rule: an underlined phrase inside a 44px display heading reads as damage,
 * and body copy is where an internal link belongs anyway.
 *
 * @return string[]
 */
function ssbd_brand_link_skip_tags() {
	$tags = array(
		'a', 'button', 'code', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
		'kbd', 'label', 'noscript', 'option', 'pre', 'samp', 'script',
		'select', 'style', 'svg', 'template', 'textarea', 'title',
	);

	/** Filter the elements whose text is never linked. */
	return (array) apply_filters( 'ssbd_brand_link_skip_tags', $tags );
}

/**
 * One name as a regex body, tolerant of how the spaces were written.
 *
 * A name that survived a copy-paste out of a word processor can carry a
 * non-breaking space between its words, and the editor writes that as an
 * entity. Matching on the literal string would miss every one of those.
 *
 * @param string $phrase Business name.
 * @return string
 */
function ssbd_brand_link_pattern_body( $phrase ) {
	$words = preg_split( '/\s+/u', trim( $phrase ) );

	return implode( '(?:&nbsp;|&#160;|\s)+', array_map(
		static function ( $word ) {
			return preg_quote( $word, '~' );
		},
		$words
	) );
}

/**
 * Link every written mention of the business name in a block of HTML.
 *
 * Walks the markup as tags and text rather than parsing it: ext-dom is not
 * guaranteed on shared hosting, and inc/service-content.php works the same
 * way for the same reason. Splitting on tags is enough here because the only
 * thing that has to be understood is which side of a tag boundary a run of
 * characters falls on.
 *
 * @param string $html Rendered HTML.
 * @return string
 */
function ssbd_link_brand_mentions( $html ) {
	$phrases = ssbd_brand_link_phrases();

	if ( ! $phrases || '' === trim( $html ) ) {
		return $html;
	}

	// Cheap way out for the pages that never say it. The test is the first
	// word rather than the whole name, because the name may be written with
	// a non-breaking space and would not be found as a literal string.
	$present = false;

	foreach ( $phrases as $phrase ) {
		$first = preg_split( '/\s+/u', trim( $phrase ) )[0];

		if ( '' !== $first && false !== stripos( $html, $first ) ) {
			$present = true;
			break;
		}
	}

	if ( ! $present ) {
		return $html;
	}

	$bodies = array_map( 'ssbd_brand_link_pattern_body', $phrases );

	// The lookarounds keep the match to a whole name: "Software Service BDX"
	// is a different word and must not be cut in half. A trailing apostrophe
	// is not a letter, so the possessive still matches the name inside it.
	$pattern = '~(?<![\p{L}\p{N}])(' . implode( '|', $bodies ) . ')(?![\p{L}\p{N}])~iu';

	$skip = array_map( 'strtolower', ssbd_brand_link_skip_tags() );

	/**
	 * Filter how many mentions are linked per page. 0 links every one.
	 *
	 * Lower it to 1 to link only the first mention on a page, which is the
	 * conservative reading of internal linking: the tenth link to the same
	 * destination in one document adds nothing the first did not.
	 */
	$max = (int) apply_filters( 'ssbd_brand_link_max', 0 );

	$href  = esc_url( home_url( '/' ) );
	$open  = '<a class="ssbd-brand-link" href="' . $href . '" rel="home">';
	$depth = 0;
	$done  = 0;
	$out   = '';

	// Comments are captured whole: one holding a ">" would otherwise be torn
	// apart and its innards rewritten as if they were page text.
	$tokens = preg_split( '#(<!--.*?-->|<[^>]*>)#s', $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

	if ( ! is_array( $tokens ) ) {
		return $html;
	}

	foreach ( $tokens as $token ) {
		if ( '<' === $token[0] ) {
			if ( preg_match( '#^</?([a-z0-9]+)#i', $token, $name ) ) {
				$tag = strtolower( $name[1] );

				if ( in_array( $tag, $skip, true ) ) {
					if ( '/' === $token[1] ) {
						$depth = max( 0, $depth - 1 );
					} elseif ( ! str_ends_with( rtrim( $token, '> ' ), '/' ) ) {
						++$depth;
					}
				}
			}

			$out .= $token;
			continue;
		}

		if ( $depth > 0 || ( $max && $done >= $max ) ) {
			$out .= $token;
			continue;
		}

		$out .= preg_replace_callback(
			$pattern,
			static function ( $match ) use ( $open, $max, &$done ) {
				if ( $max && $done >= $max ) {
					return $match[0];
				}

				++$done;

				return $open . $match[0] . '</a>';
			},
			$token
		);
	}

	return $out;
}

/**
 * Remember which buffer level this module opened.
 *
 * @param int|null $set Level to record, or null to read.
 * @return int
 */
function ssbd_brand_links_buffer_level( $set = null ) {
	static $level = 0;

	if ( null !== $set ) {
		$level = (int) $set;
	}

	return $level;
}

/**
 * Start buffering the body so the whole page can be rewritten at once.
 */
function ssbd_brand_links_buffer_start() {
	if ( ssbd_brand_links_enabled() && ob_start( 'ssbd_link_brand_mentions' ) ) {
		ssbd_brand_links_buffer_level( ob_get_level() );
	}
}

/**
 * Flush the body buffer.
 *
 * Registered after inc/delay-scripts.php so its footer buffer — opened inside
 * this one — closes first. The level check is what makes that safe: if a
 * plugin still has a buffer of its own open on top, this leaves it alone and
 * lets PHP flush everything in order at the end of the request, which runs
 * this callback just the same, only later.
 */
function ssbd_brand_links_buffer_end() {
	$level = ssbd_brand_links_buffer_level();

	if ( $level && ob_get_level() === $level ) {
		ssbd_brand_links_buffer_level( 0 );
		ob_end_flush();
	}
}

add_action( 'wp_body_open', 'ssbd_brand_links_buffer_start', 0 );
add_action( 'wp_footer', 'ssbd_brand_links_buffer_end', PHP_INT_MAX );
