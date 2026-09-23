<?php
/**
 * The article reading page.
 *
 * Two jobs: build a table of contents out of the post's own headings, and
 * supply the share row. Both need the same brand marks the footer already
 * draws, so those live here too rather than being written out twice.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * The SVG path for a network's mark, drawn in a 24×24 box.
 *
 * One source of truth: the footer's social row and the article's share row
 * both draw from here, so a corrected path is corrected everywhere.
 *
 * @param string $network Network key.
 * @return string Raw SVG path markup, or '' when unknown.
 */
function ssbd_social_mark( $network ) {
	$marks = array(
		'facebook' => '<path d="M13.5 21v-7.2h2.4l.4-2.8h-2.8V9.2c0-.8.2-1.4 1.4-1.4h1.5V5.3c-.3 0-1.2-.1-2.2-.1-2.1 0-3.6 1.3-3.6 3.7V11H8.2v2.8h2.4V21h2.9z"/>',
		'linkedin' => '<path d="M6.9 8.6H4.2V20h2.7V8.6zM5.5 4.2a1.6 1.6 0 1 0 0 3.2 1.6 1.6 0 0 0 0-3.2zM19.8 13.5c0-2.9-1.6-4.3-3.7-4.3-1.7 0-2.5.9-2.9 1.6V8.6H10.6c0 .8 0 11.4 0 11.4h2.6v-6.3c0-.3 0-.7.1-.9.3-.7.9-1.4 1.9-1.4 1.3 0 1.9 1 1.9 2.5V20h2.7v-6.5z"/>',
		'x'        => '<path d="M17.4 4h2.9l-6.3 7.2L21.5 20h-5.8l-4.1-5.4L6.9 20H4l6.7-7.7L3.8 4h5.9l3.7 4.9L17.4 4zm-1 14.3h1.6L8.7 5.6H7l9.4 12.7z"/>',
		'github'   => '<path d="M12 3.2A8.9 8.9 0 0 0 9.2 20.6c.4.1.6-.2.6-.4v-1.6c-2.5.5-3-1.1-3-1.1-.4-1-1-1.3-1-1.3-.8-.6.1-.6.1-.6.9.1 1.4.9 1.4.9.8 1.4 2.1 1 2.6.8.1-.6.3-1 .6-1.2-2-.2-4.1-1-4.1-4.4 0-1 .3-1.8.9-2.4-.1-.3-.4-1.2.1-2.4 0 0 .7-.2 2.4.9a8.4 8.4 0 0 1 4.4 0c1.7-1.1 2.4-.9 2.4-.9.5 1.2.2 2.1.1 2.4.6.6.9 1.4.9 2.4 0 3.4-2.1 4.2-4.1 4.4.3.3.6.9.6 1.8v2.6c0 .2.2.5.6.4A8.9 8.9 0 0 0 12 3.2z"/>',
		'whatsapp' => '<path d="M12 3.4a8.5 8.5 0 0 0-7.3 12.9L3.4 21l4.8-1.3A8.5 8.5 0 1 0 12 3.4zm0 1.7a6.8 6.8 0 0 1 5.6 10.7 6.8 6.8 0 0 1-8.9 2l-.4-.2-2.5.7.7-2.4-.2-.4A6.8 6.8 0 0 1 12 5.1zm-2.8 3.3c-.2 0-.4 0-.6.3-.2.3-.8.8-.8 1.9s.8 2.2.9 2.4c.1.2 1.6 2.6 4 3.5 1.9.7 2.3.6 2.7.5.4 0 1.3-.5 1.5-1.1.2-.6.2-1 .1-1.1l-.5-.3-1.5-.7c-.2-.1-.4-.1-.5.1l-.7.9c-.1.2-.3.2-.5.1a5.6 5.6 0 0 1-2.8-2.4c-.2-.3 0-.5.1-.6l.4-.5.2-.4v-.4l-.7-1.7c-.2-.4-.4-.4-.5-.4h-.4z"/>',
		'youtube'  => '<path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2C0 8.1 0 12 0 12s0 3.9.5 5.8a3 3 0 0 0 2.1 2.1c1.9.6 9.4.6 9.4.6s7.5 0 9.4-.6a3 3 0 0 0 2.1-2.1c.5-1.9.5-5.8.5-5.8s0-3.9-.5-5.8zM9.5 15.6V8.4L15.8 12l-6.3 3.6z"/>',
		'pinterest' => '<path d="M12 2C6.5 2 2 6.5 2 12c0 4.1 2.5 7.6 6 9.1-.1-.8-.2-1.9 0-2.8l1.2-4.9s-.3-.6-.3-1.5c0-1.4.8-2.4 1.8-2.4.9 0 1.3.6 1.3 1.4 0 .9-.6 2.2-.8 3.4-.2 1 .5 1.8 1.5 1.8 1.8 0 3.1-1.9 3.1-4.6 0-2.4-1.7-4-4.1-4-2.8 0-4.5 2.1-4.5 4.3 0 .9.3 1.8.8 2.3.1.1.1.2.1.3l-.3 1.1c0 .2-.2.2-.3.1-1.2-.6-1.9-2.3-1.9-3.8 0-3.1 2.2-5.9 6.5-5.9 3.4 0 6.1 2.4 6.1 5.7 0 3.4-2.1 6.1-5.1 6.1-1 0-1.9-.5-2.3-1.1l-.6 2.3c-.2.9-.8 1.9-1.2 2.6.9.3 1.8.4 2.8.4 5.5 0 10-4.5 10-10S17.5 2 12 2z"/>',
	);

	return $marks[ $network ] ?? '';
}

/**
 * Give every H2 and H3 in the content an id, and list them for the contents box.
 *
 * Done in PHP rather than JavaScript so the anchors exist in the HTML: a shared
 * link to #benefits has to land in the right place before any script runs, and
 * an answer engine quoting a section needs the id in the markup it fetched.
 *
 * A heading that already carries an id keeps it, because that id may already be
 * linked from somewhere else.
 *
 * @param string $content Rendered post content.
 * @return array{content:string, headings:array<array{id:string,text:string,level:int}>}
 */
function ssbd_article_toc( $content ) {
	$headings = array();
	$used     = array();

	$content = preg_replace_callback(
		'#<h([23])((?:\s[^>]*)?)>(.*?)</h\1>#is',
		static function ( $matches ) use ( &$headings, &$used ) {
			$level = (int) $matches[1];
			$attrs = $matches[2];
			$inner = $matches[3];
			$text  = trim( wp_strip_all_tags( $inner ) );

			// A heading holding only an image has nothing to list.
			if ( '' === $text ) {
				return $matches[0];
			}

			if ( preg_match( '#\sid=(["\'])(.*?)\1#i', $attrs, $existing ) ) {
				$id = $existing[2];
			} else {
				$id = sanitize_title( $text );

				// A heading in a script sanitize_title() strips to nothing —
				// it percent-encodes Bengali rather than transliterating it —
				// still needs a stable anchor to link to.
				if ( '' === $id ) {
					$id = 'section-' . ( count( $headings ) + 1 );
				}

				// Two sections called "Pricing" would otherwise share an anchor
				// and the second would be unreachable.
				$base   = $id;
				$suffix = 2;

				while ( isset( $used[ $id ] ) ) {
					$id = $base . '-' . $suffix;
					$suffix++;
				}

				$attrs .= ' id="' . esc_attr( $id ) . '"';
			}

			$used[ $id ] = true;

			$headings[] = array(
				'id'    => $id,
				'text'  => $text,
				'level' => $level,
			);

			return '<h' . $level . $attrs . '>' . $inner . '</h' . $level . '>';
		},
		$content
	);

	return array( 'content' => $content, 'headings' => $headings );
}

/**
 * How many headings an article needs before a contents box earns its place.
 *
 * @return int
 */
function ssbd_toc_threshold() {
	/** Filter the minimum heading count for the table of contents. */
	return (int) apply_filters( 'ssbd_toc_threshold', 3 );
}

/**
 * The FAQ list an editor entered for one article, via the "FAQ" meta box.
 *
 * Already sanitized on save (see ssbd_save_catalogue_meta() and
 * ssbd_faq_allowed_html() in inc/meta-boxes.php) — this just decodes it.
 * Feeds template-parts/components/faq.php, which renders the accordion and
 * emits the matching FAQPage structured data from the same array.
 *
 * @param int|null $post_id Defaults to the post in the loop.
 * @return array<int, array{q: string, a: string}>
 */
function ssbd_post_faqs( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$items   = json_decode( (string) get_post_meta( $post_id, '_ssbd_faqs', true ), true );

	if ( ! is_array( $items ) ) {
		return array();
	}

	return array_values( array_filter(
		$items,
		static function ( $item ) {
			return ! empty( $item['q'] ) && ! empty( $item['a'] );
		}
	) );
}

/**
 * Share destinations for one article.
 *
 * WhatsApp is included deliberately — for a Bangladeshi audience it carries
 * more sharing than the desktop networks do.
 *
 * @param string $url   Canonical URL.
 * @param string $title Post title.
 * @param string $image Absolute URL of the image to pin. Pinterest is offered
 *                      only when there is one.
 * @return array<string, array{label:string, url:string}>
 */
function ssbd_share_links( $url, $title, $image = '' ) {
	$links = array(
		'facebook' => array(
			'label' => __( 'Share on Facebook', 'ssbd' ),
			'url'   => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url ),
		),
		'linkedin' => array(
			'label' => __( 'Share on LinkedIn', 'ssbd' ),
			'url'   => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $url ),
		),
		'x'        => array(
			'label' => __( 'Share on X', 'ssbd' ),
			'url'   => 'https://twitter.com/intent/tweet?url=' . rawurlencode( $url ) . '&text=' . rawurlencode( $title ),
		),
		'whatsapp' => array(
			'label' => __( 'Share on WhatsApp', 'ssbd' ),
			'url'   => 'https://wa.me/?text=' . rawurlencode( $title . ' ' . $url ),
		),
	);

	/*
	 * Pinterest only when the article has a featured image. Its dialog needs
	 * something to pin, and with no media parameter it falls back to scraping
	 * whatever it can find on the page — which on an article without a
	 * featured image is the site logo, a pin nobody saves.
	 *
	 * The full-size file rather than ssbd-hero: that size is a 16:9 hard crop,
	 * and a pin at 16:9 renders as a thin sliver in a feed of tall images.
	 */
	if ( $image ) {
		$links['pinterest'] = array(
			'label' => __( 'Save to Pinterest', 'ssbd' ),
			'url'   => 'https://www.pinterest.com/pin/create/button/?' . http_build_query( array(
				'url'         => $url,
				'media'       => $image,
				'description' => $title,
			) ),
		);
	}

	/** Filter the article share destinations. */
	return apply_filters( 'ssbd_share_links', $links, $url, $title, $image );
}

/**
 * Render one comment.
 *
 * A callback rather than the default walker markup so the comment carries the
 * same avatar / byline / body shape as the rest of the site, and so the reply
 * link can sit in a footer row instead of floating inside the text.
 *
 * wp_list_comments() opens the list item and leaves it open; ssbd_render_comment_end()
 * closes it after any children have been rendered.
 *
 * @param WP_Comment $comment Comment.
 * @param array      $args    wp_list_comments() arguments.
 * @param int        $depth   Nesting depth.
 */
function ssbd_render_comment( $comment, $args, $depth ) {
	$ssbd_is_author = (int) $comment->user_id && (int) $comment->user_id === (int) get_the_author_meta( 'ID' );
	$ssbd_pending   = '0' === $comment->comment_approved;
	?>
	<li id="comment-<?php comment_ID(); ?>" <?php comment_class( $ssbd_pending ? 'comment-item is-pending' : 'comment-item' ); ?>>
		<article class="comment-body">
			<div class="comment-avatar">
				<?php echo get_avatar( $comment, (int) ( $args['avatar_size'] ?? 44 ), '', '' ); ?>
			</div>

			<div class="comment-main">
				<header class="comment-head">
					<span class="comment-author"><?php comment_author(); ?></span>

					<?php if ( $ssbd_is_author ) : ?>
						<span class="comment-badge"><?php esc_html_e( 'Author', 'ssbd' ); ?></span>
					<?php endif; ?>

					<?php
					// Relative time reads well for a live discussion and badly
					// for an archive: "8 months ago" tells a reader less than
					// the date does once a thread has gone quiet.
					$ssbd_posted = (int) get_comment_time( 'U' );
					$ssbd_age    = time() - $ssbd_posted;
					?>
					<a class="comment-date" href="<?php echo esc_url( get_comment_link( $comment ) ); ?>">
						<time datetime="<?php comment_time( 'c' ); ?>"><?php
							echo esc_html(
								$ssbd_age < MONTH_IN_SECONDS
									? sprintf(
										/* translators: %s: human-readable time difference */
										__( '%s ago', 'ssbd' ),
										human_time_diff( $ssbd_posted )
									)
									: get_comment_date( '', $comment )
							);
						?></time>
					</a>
				</header>

				<?php if ( $ssbd_pending ) : ?>
					<p class="comment-pending"><?php esc_html_e( 'Awaiting moderation — only you can see this.', 'ssbd' ); ?></p>
				<?php endif; ?>

				<div class="comment-text"><?php comment_text(); ?></div>

				<footer class="comment-actions">
					<?php
					comment_reply_link( array_merge( $args, array(
						'depth'      => $depth,
						'max_depth'  => (int) ( $args['max_depth'] ?? get_option( 'thread_comments_depth' ) ),
						'reply_text' => __( 'Reply', 'ssbd' ),
						'before'     => '',
						'after'      => '',
					) ), $comment );

					edit_comment_link( __( 'Edit', 'ssbd' ), '', '' );
					?>
				</footer>
			</div>
		</article>
	<?php
	// Deliberately left open — wp_list_comments() nests replies inside this
	// item and calls the end-callback once they are done.
}

/**
 * Close a comment list item.
 *
 * @param WP_Comment $comment Comment.
 * @param array      $args    wp_list_comments() arguments.
 * @param int        $depth   Nesting depth.
 */
function ssbd_render_comment_end( $comment, $args, $depth ) {
	echo '</li>';
}

/**
 * The article's video, embedded and ready to print — or '' when there is
 * none, or the URL does not resolve to anything oEmbed recognises.
 *
 * Cached in a transient keyed to the post and the URL, so editing the URL
 * invalidates the cache automatically and a typo does not get stuck cached as
 * a failure forever: a transient is a cache, not a record, and re-fetching a
 * broken URL costs one slow request the next time someone asks, not more.
 *
 * @param int|null $post_id Post ID. Defaults to the current post.
 * @return string HTML embed markup, or ''.
 */
function ssbd_article_video_embed( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$url     = trim( (string) get_post_meta( $post_id, '_ssbd_video_url', true ) );

	if ( '' === $url ) {
		return '';
	}

	$cache_key = 'ssbd_video_' . $post_id . '_' . md5( $url );
	$cached    = get_transient( $cache_key );

	if ( false !== $cached ) {
		return $cached;
	}

	$embed = wp_oembed_get( $url );
	$html  = $embed ? '<div class="article-video-frame">' . $embed . '</div>' : '';

	// A day either way: long enough that a page with real traffic is not
	// re-fetching the provider on every view, short enough that a video
	// swapped out for another still shows up the same day.
	set_transient( $cache_key, $html, DAY_IN_SECONDS );

	return $html;
}

/**
 * Credit the same author in the feed as on the page.
 *
 * ssbd_author_name() already replaces a placeholder login — admin, editor,
 * user — with the editorial name, but that runs in the theme's templates and
 * the RSS feed does not use them: core builds <dc:creator> from the account's
 * display name directly. The result was a byline reading "Software Service BD
 * Editorial Team" on the article and "admin" in the feed for the same post,
 * which is the version syndicators and feed readers republish.
 *
 * @param string $name Display name core resolved.
 * @return string
 */
function ssbd_feed_author( $name ) {
	if ( ! is_feed() ) {
		return $name;
	}

	$post_id = get_the_ID();

	return $post_id ? ssbd_author_name( $post_id ) : $name;
}
add_filter( 'the_author', 'ssbd_feed_author' );
