<?php
/**
 * Link auditing for the SEO & FAQ workbench.
 *
 * Two separate questions, deliberately kept apart because they cost different
 * things to answer. Everything in ssbd_seo_link_audit() is read straight out
 * of the stored post content and is free, so it runs on every page load.
 * Reaching the other end of a link to see whether it still answers costs a
 * network round trip per URL, so that only happens when someone asks for it
 * and the answer is cached for half a day.
 *
 * Self-contained on purpose: the workbench is a theme screen and has to keep
 * working with every SEO plugin deactivated, so nothing here reaches for one.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Anchor text that tells a reader — and a crawler — nothing about the target.
 *
 * The point of an anchor is that the words themselves say where the link
 * goes; "click here" spends a link and says nothing, and it is also what a
 * screen reader reads out when a user tabs through links out of context.
 *
 * @return string[] Lower-case anchor texts.
 */
function ssbd_seo_weak_anchors() {
	/** Filter the anchor texts flagged as uninformative. */
	return (array) apply_filters(
		'ssbd_seo_weak_anchors',
		array(
			'click here', 'here', 'this', 'this link', 'this page', 'this post',
			'read more', 'more', 'learn more', 'find out more', 'link', 'website',
			'homepage', 'download', 'see more', 'go', 'continue',
			'এখানে', 'এখানে ক্লিক করুন', 'ক্লিক করুন', 'বিস্তারিত', 'আরও পড়ুন', 'দেখুন',
		)
	);
}

/**
 * Read every link in a post and say what is wrong with them.
 *
 * @param WP_Post $post Post to audit.
 * @return array{internal: array, external: array, problems: string[]}
 */
function ssbd_seo_link_audit( $post ) {
	$content = strip_shortcodes( (string) $post->post_content );

	// Gutenberg block delimiters are HTML comments; they carry no links and
	// only confuse the regex below when an attribute value happens to contain
	// a ">".
	$content = (string) preg_replace( '/<!--\s*\/?wp:.*?-->/s', '', $content );

	$internal  = array();
	$external  = array();
	$problems  = array();
	$targets   = array();
	$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
	$self      = untrailingslashit( (string) get_permalink( $post ) );
	$weak      = ssbd_seo_weak_anchors();

	if ( ! preg_match_all( '/<a\b([^>]*)>(.*?)<\/a>/is', $content, $matches, PREG_SET_ORDER ) ) {
		return array(
			'internal' => array(),
			'external' => array(),
			'problems' => array(),
		);
	}

	foreach ( $matches as $match ) {
		$attrs  = $match[1];
		$anchor = trim( wp_strip_all_tags( $match[2] ) );
		$href   = '';

		if ( preg_match( '/href\s*=\s*("|\')(.*?)\1/i', $attrs, $found ) ) {
			$href = trim( html_entity_decode( $found[2], ENT_QUOTES, 'UTF-8' ) );
		}

		$rel      = preg_match( '/rel\s*=\s*("|\')(.*?)\1/i', $attrs, $r ) ? strtolower( $r[2] ) : '';
		$target   = preg_match( '/target\s*=\s*("|\')(.*?)\1/i', $attrs, $t ) ? strtolower( $t[2] ) : '';
		$nofollow = ( false !== strpos( $rel, 'nofollow' ) );

		if ( '' === $href || '#' === $href || 0 === stripos( $href, 'javascript:' ) ) {
			$problems[] = sprintf(
				/* translators: %s: anchor text */
				__( 'A link has no destination — anchor text “%s”', 'ssbd' ),
				$anchor ? $anchor : __( '(empty)', 'ssbd' )
			);
			continue;
		}

		// A mail or phone link is neither internal nor external for these
		// purposes: there is nothing to crawl and nothing to check.
		if ( preg_match( '/^(mailto:|tel:)/i', $href ) ) {
			continue;
		}

		$host   = wp_parse_url( $href, PHP_URL_HOST );
		$is_int = ( empty( $host ) || $host === $site_host );

		$link = array(
			'href'     => $href,
			'anchor'   => $anchor,
			'nofollow' => $nofollow,
			'target'   => $target,
		);

		if ( $is_int ) {
			$internal[] = $link;

			if ( $nofollow ) {
				$problems[] = sprintf(
					/* translators: %s: anchor text or URL */
					__( 'Internal link “%s” is nofollow — that spends the link and passes nothing on', 'ssbd' ),
					$anchor ? $anchor : $href
				);
			}

			if ( untrailingslashit( strtok( $href, '#' ) ) === $self ) {
				$problems[] = __( 'The post links to itself', 'ssbd' );
			}
		} else {
			$external[] = $link;

			if ( '_blank' === $target && false === strpos( $rel, 'noopener' ) ) {
				$problems[] = sprintf(
					/* translators: %s: anchor text or URL */
					__( 'External link “%s” opens in a new tab without rel="noopener"', 'ssbd' ),
					$anchor ? $anchor : $href
				);
			}
		}

		if ( '' === $anchor ) {
			$problems[] = sprintf(
				/* translators: %s: URL */
				__( 'Link to %s has no anchor text — probably an image, which needs alt text instead', 'ssbd' ),
				$href
			);
		} elseif ( in_array( mb_strtolower( $anchor ), $weak, true ) ) {
			$problems[] = sprintf(
				/* translators: %s: anchor text */
				__( 'Anchor text “%s” says nothing about the destination', 'ssbd' ),
				$anchor
			);
		} elseif ( preg_match( '#^https?://#i', $anchor ) ) {
			$problems[] = sprintf(
				/* translators: %s: anchor text */
				__( 'A bare URL is used as anchor text (%s) — use words instead', 'ssbd' ),
				$anchor
			);
		}

		$key             = untrailingslashit( strtok( $href, '#' ) );
		$targets[ $key ] = isset( $targets[ $key ] ) ? $targets[ $key ] + 1 : 1;
	}

	foreach ( $targets as $url => $count ) {
		if ( $count > 2 ) {
			$problems[] = sprintf(
				/* translators: 1: number of links, 2: URL */
				__( '%1$d links point at the same URL (%2$s) — once or twice is enough', 'ssbd' ),
				$count,
				$url
			);
		}
	}

	return array(
		'internal' => $internal,
		'external' => $external,
		'problems' => array_values( array_unique( $problems ) ),
	);
}

/**
 * Ask each external URL whether it still answers.
 *
 * HEAD first because the body is not wanted; a fair number of servers refuse
 * HEAD outright, and answering "405" there would be reporting on our own
 * request rather than on the link, so those get a second try with GET.
 *
 * Cached per URL rather than per post, so the same reference cited from ten
 * articles is fetched once.
 *
 * @param array $links Links from ssbd_seo_link_audit().
 * @param int   $limit Most URLs to contact in one pass.
 * @return array<int, array{href: string, anchor: string, code: int, ok: bool}>
 */
function ssbd_seo_check_external_links( $links, $limit = 15 ) {
	$results = array();

	foreach ( array_slice( $links, 0, $limit ) as $link ) {
		$url   = $link['href'];
		$cache = 'ssbd_link_' . md5( $url );
		$code  = get_transient( $cache );

		if ( false === $code ) {
			$args = array(
				'timeout'     => 8,
				'redirection' => 5,
				'sslverify'   => true,
				'user-agent'  => 'Mozilla/5.0 (compatible; ssbd-linkcheck/1.0; +' . home_url() . ')',
			);

			$response = wp_remote_head( $url, $args );

			if ( ! is_wp_error( $response )
				&& in_array( (int) wp_remote_retrieve_response_code( $response ), array( 403, 405, 501 ), true ) ) {
				$response = wp_remote_get( $url, $args );
			}

			$code = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );

			set_transient( $cache, $code, 12 * HOUR_IN_SECONDS );
		}

		$code = (int) $code;

		$results[] = array(
			'href'   => $url,
			'anchor' => $link['anchor'],
			'code'   => $code,
			'ok'     => ( $code >= 200 && $code < 400 ),
		);
	}

	return $results;
}
