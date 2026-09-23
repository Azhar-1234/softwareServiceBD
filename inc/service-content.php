<?php
/**
 * Laying out a service page's body content.
 *
 * The body of a service is written in wp-admin as ordinary prose: an <h2> per
 * section, paragraphs under it, a list where a list belongs, and an image
 * dropped in on its own line. That is the right way to write it — nobody
 * should have to hand-build a two-column layout to describe what they sell.
 *
 * It is not, however, something CSS alone can lay out. The theme already
 * styles the "Service: Media Left/Right" block patterns, but those only apply
 * to content an editor deliberately built out of Media & Text blocks. Content
 * typed the ordinary way arrives as a flat run of siblings, and no selector
 * can group a heading with the paragraphs beneath it, or sit an image beside
 * the text next to it, because CSS cannot pair siblings into a box.
 *
 * So the grouping happens here, once, on the rendered HTML:
 *
 *   - each <h2> and everything under it becomes one .service-block section
 *   - a section holding a standalone image becomes a two-column split, image
 *     one side and text the other, alternating sides down the page
 *   - a run of <h3> sub-sections becomes a numbered card grid
 *   - lists whose items open with <strong> become feature rows / process steps
 *
 * Nothing is added, removed or reworded — this only wraps what is already
 * there. ssbd_service_layout_is_lossless() re-checks that on every render and
 * throws the whole transform away if a single character of text moved, so the
 * worst case is the plain content it started with.
 *
 * Regex rather than DOMDocument on purpose: the ext-dom extension is not
 * guaranteed on shared PHP hosting, this content is WordPress's own output
 * rather than arbitrary HTML, and inc/seo.php already rewrites headings the
 * same way. The transform only ever splits on top-level tags and moves whole
 * matched blocks, so nesting it does not understand is carried across intact.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * A standalone image: the block editor's <figure>, or the classic editor's
 * <p> holding nothing but an <img> (optionally wrapped in a link).
 *
 * A paragraph with an image *and* words in it is prose with an inline image,
 * not a figure, and is deliberately not matched — pulling it into the media
 * column would tear a sentence off the page.
 */
const SSBD_STANDALONE_IMAGE = '#<figure\b[^>]*>.*?</figure>|<p>\s*(?:<a\b[^>]*>\s*)?<img\b[^>]*?/?>(?:\s*</a>)?\s*</p>#is';

/**
 * The comparable text of a fragment: every tag gone, every run of whitespace
 * collapsed, so two fragments that say the same thing compare equal however
 * their markup is arranged.
 *
 * @param string $html Fragment.
 * @return string
 */
function ssbd_layout_text( $html ) {
	return trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $html ) ) );
}

/**
 * Whether a transform preserved every word and every image of the original.
 *
 * The layout is cosmetic: it is allowed to move markup around, and it is never
 * allowed to lose a sentence. Checking rather than trusting means a service
 * whose body is written in some shape this file did not anticipate renders as
 * plain prose instead of rendering wrong.
 *
 * @param string $before Original content.
 * @param string $after  Transformed content.
 * @return bool
 */
function ssbd_service_layout_is_lossless( $before, $after ) {
	if ( ssbd_layout_text( $before ) !== ssbd_layout_text( $after ) ) {
		return false;
	}

	return preg_match_all( '/<img\b/i', $before ) === preg_match_all( '/<img\b/i', $after );
}

/**
 * Whether a fragment closes every block element it opens.
 *
 * The section split assumes each <h2> is a top-level sibling, which is what
 * wp-admin produces. An <h2> nested inside something else — a group block, a
 * table cell, a paste that brought its own wrapper — would be split in the
 * middle of that wrapper, and the text check alone would not notice, because
 * every word would still be present in the broken markup. So each group has to
 * balance on its own before any of it is used.
 *
 * @param string $html Fragment.
 * @return bool
 */
function ssbd_fragment_is_balanced( $html ) {
	foreach ( array( 'div', 'section', 'p', 'ul', 'ol', 'li', 'figure', 'table', 'blockquote' ) as $tag ) {
		$open  = preg_match_all( '#<' . $tag . '\b[^>]*(?<!/)>#i', $html );
		$close = preg_match_all( '#</' . $tag . '\s*>#i', $html );

		if ( $open !== $close ) {
			return false;
		}
	}

	return true;
}

/**
 * Whether every item in a list opens with a <strong> label.
 *
 * That shape — "<strong>Clean, expressive syntax</strong> that speeds up
 * development" — is a definition list written as bullets, and reads far better
 * as a row of labelled features. A list that only sometimes does it is left
 * alone: guessing wrong would style prose as a feature table.
 *
 * @param string $list A single <ul>…</ul> or <ol>…</ol>.
 * @return bool
 */
function ssbd_list_is_labelled( $list ) {
	if ( ! preg_match_all( '#<li\b[^>]*>(.*?)</li>#is', $list, $items ) ) {
		return false;
	}

	if ( count( $items[1] ) < 2 ) {
		return false;
	}

	foreach ( $items[1] as $item ) {
		if ( ! preg_match( '#^\s*<(strong|b)\b#i', $item ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Add a class to the opening tag of a fragment.
 *
 * @param string $tag   Matched opening tag.
 * @param string $class Class to add.
 * @return string
 */
function ssbd_add_class_to_tag( $tag, $class ) {
	if ( preg_match( '/\sclass\s*=\s*"([^"]*)"/i', $tag ) ) {
		return preg_replace(
			'/(\sclass\s*=\s*")([^"]*)(")/i',
			'$1$2 ' . $class . '$3',
			$tag,
			1
		);
	}

	return preg_replace( '/^<(\w+)/', '<$1 class="' . $class . '"', $tag, 1 );
}

/**
 * Mark labelled lists so they can be styled as features and process steps.
 *
 * @param string $html One section's text-side markup.
 * @return string
 */
function ssbd_mark_labelled_lists( $html ) {
	return (string) preg_replace_callback(
		'#<(ul|ol)\b[^>]*>.*?</\1>#is',
		static function ( $m ) {
			if ( ! ssbd_list_is_labelled( $m[0] ) ) {
				return $m[0];
			}

			$class = 'ol' === strtolower( $m[1] ) ? 'service-steps' : 'service-features';

			return preg_replace_callback(
				'#^<(?:ul|ol)\b[^>]*>#i',
				static function ( $open ) use ( $class ) {
					return ssbd_add_class_to_tag( $open[0], $class );
				},
				$m[0],
				1
			);
		},
		$html
	);
}

/**
 * Turn a run of <h3> sub-sections into a numbered card grid.
 *
 * Only from two headings up: a single <h3> is a sub-heading inside prose,
 * while six in a row — "1. Custom Web Application Development", "2. E-Commerce
 * Development" — is a list of things being sold, and belongs in a grid where
 * they can be compared.
 *
 * @param string $html One section's text-side markup.
 * @return string
 */
function ssbd_group_service_cards( $html ) {
	if ( preg_match_all( '#<h3\b#i', $html ) < 2 ) {
		return $html;
	}

	$parts = preg_split( '#(?=<h3\b)#i', $html );

	if ( ! $parts ) {
		return $html;
	}

	// Anything before the first <h3> is the section's own intro, not a card.
	$lead  = '' !== trim( $parts[0] ) && 0 !== stripos( ltrim( $parts[0] ), '<h3' ) ? array_shift( $parts ) : '';
	$cards = '';

	foreach ( $parts as $part ) {
		if ( '' === trim( $part ) ) {
			continue;
		}

		// "1. Custom Web Application Development" — lift the numeral the editor
		// typed into its own span so it can be set as a badge. The digits and
		// the dot stay in the text exactly as written, so this survives the
		// lossless check; only their wrapper is new.
		$part = (string) preg_replace(
			'#(<h3\b[^>]*>)\s*(\d{1,2}\.)\s*#i',
			'$1<span class="service-numbered__no">$2</span> ',
			$part,
			1
		);

		$cards .= '<div class="service-numbered__item">' . $part . '</div>';
	}

	if ( '' === $cards ) {
		return $html;
	}

	return $lead . '<div class="service-numbered">' . $cards . '</div>';
}

/**
 * Structure a service body into laid-out sections.
 *
 * @param string $content Rendered post content.
 * @return string
 */
function ssbd_service_content_layout( $content ) {
	if ( ! is_singular( 'ssbd_service' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	if ( '' === trim( $content ) || false === stripos( $content, '<h2' ) ) {
		return $content;
	}

	// Content the editor already laid out with Media & Text is left exactly as
	// built: it has its own styling in theme.css, and re-grouping it here would
	// fight the layout somebody chose on purpose.
	if ( false !== stripos( $content, 'wp-block-media-text' ) ) {
		return $content;
	}

	$groups = preg_split( '#(?=<h2\b)#i', $content, -1, PREG_SPLIT_NO_EMPTY );

	if ( ! $groups ) {
		return $content;
	}

	$out    = '';
	$splits = 0;

	foreach ( $groups as $group ) {
		if ( '' === trim( $group ) ) {
			continue;
		}

		if ( ! ssbd_fragment_is_balanced( $group ) ) {
			return $content;
		}

		// Pull the standalone images out; whatever is left is the text column.
		preg_match_all( SSBD_STANDALONE_IMAGE, $group, $found );

		$media = implode( '', $found[0] );
		$text  = (string) preg_replace( SSBD_STANDALONE_IMAGE, '', $group );

		$text    = ssbd_group_service_cards( ssbd_mark_labelled_lists( $text ) );
		$classes = 'service-block';

		// A section whose sub-sections became a card grid keeps the full width:
		// squeezing a six-card grid into half a row stacks it into one long
		// column, which is worse than the plain run it replaced. Its image goes
		// underneath at full width instead.
		$has_grid = false !== strpos( $text, 'service-numbered' );

		if ( '' !== $media && ! $has_grid && '' !== trim( wp_strip_all_tags( $text ) ) ) {
			$splits++;

			// Alternate which side the image sits on, so a run of split
			// sections reads as a rhythm rather than a column of the same row.
			$classes .= 2 === $splits % 2
				? ' service-block--split service-block--flipped'
				: ' service-block--split';

			$out .= '<section class="' . $classes . '">'
				. '<div class="service-block__text">' . $text . '</div>'
				. '<div class="service-block__media">' . $media . '</div>'
				. '</section>';
			continue;
		}

		$out .= '<section class="' . $classes . '">' . $text . $media . '</section>';
	}

	if ( '' === $out || ! ssbd_service_layout_is_lossless( $content, $out ) ) {
		return $content;
	}

	return $out;
}
// After ssbd_demote_content_h1() at 20, so a heading it rewrites to <h2> is
// also a section boundary here.
add_filter( 'the_content', 'ssbd_service_content_layout', 25 );
