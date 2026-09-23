<?php
/**
 * Presentational helpers shared across templates.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render one of the theme's stroke icons.
 *
 * @param string $key   Icon key from inc/data/icons.php.
 * @param int    $size  Pixel size.
 * @param array  $attrs Extra attributes for the <svg>.
 */
function ssbd_icon( $key, $size = 20, $attrs = array() ) {
	$icons = ssbd_data( 'icons' );

	if ( empty( $icons[ $key ] ) ) {
		return;
	}

	$attrs = wp_parse_args( $attrs, array(
		'aria-hidden' => 'true',
		'focusable'   => 'false',
	) );

	$rendered = '';
	foreach ( $attrs as $name => $value ) {
		$rendered .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) );
	}

	printf(
		'<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none"%2$s><path d="%3$s" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		(int) $size,
		$rendered, // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
		esc_attr( $icons[ $key ] )
	);
}

/**
 * The site logo: a custom logo if one is set, otherwise the packaged mark.
 */
function ssbd_logo_url() {
	$id = get_theme_mod( 'custom_logo' );
	if ( $id ) {
		$src = wp_get_attachment_image_url( $id, 'full' );
		if ( $src ) {
			return $src;
		}
	}
	return SSBD_URI . '/assets/images/logo-icon.png';
}

/**
 * Header/footer brand lockup.
 *
 * @param string $class Extra class names.
 */
function ssbd_brand( $class = '' ) {
	$name  = ssbd_site( 'legal_name', get_bloginfo( 'name' ) );
	$parts = explode( ' ', $name );
	$last  = array_pop( $parts );
	?>
	<a class="brand <?php echo esc_attr( $class ); ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<img src="<?php echo esc_url( ssbd_logo_url() ); ?>" width="36" height="36" alt="" loading="eager" decoding="async">
		<span class="brand-name"><?php echo esc_html( implode( ' ', $parts ) ); ?> <span class="accent"><?php echo esc_html( $last ); ?></span></span>
	</a>
	<?php
}

/**
 * Section eyebrow.
 *
 * @param string $text  Label.
 * @param bool   $dot   Show the accent dot.
 * @param string $class Extra classes.
 */
function ssbd_eyebrow( $text, $dot = false, $class = '' ) {
	printf(
		'<span class="eyebrow %s">%s%s</span>',
		esc_attr( $class ),
		$dot ? '<span class="dot"></span>' : '',
		esc_html( $text )
	);
}

/**
 * Render a section heading + optional lede.
 *
 * @param string $heading Heading text.
 * @param string $lede    Supporting copy.
 * @param array  $args    'level', 'align' (center|left|split), 'eyebrow', 'id'.
 */
function ssbd_section_head( $heading, $lede = '', $args = array() ) {
	$args = wp_parse_args( $args, array(
		'level'   => 'h2',
		'align'   => 'center',
		'eyebrow' => '',
		'id'      => '',
	) );

	$tag = in_array( $args['level'], array( 'h1', 'h2', 'h3' ), true ) ? $args['level'] : 'h2';

	// 'split' sets the heading against the left edge and moves the lede to a
	// second column beside it, which only reads as deliberate when there is a
	// lede to put there — without one it falls back to plain left alignment
	// rather than leaving half the row empty.
	$align = $args['align'];
	if ( 'split' === $align && ! $lede ) {
		$align = 'left';
	}

	$modifier = array(
		'left'  => ' section-head--left',
		'split' => ' section-head--split',
	);
	$class    = 'section-head' . ( $modifier[ $align ] ?? '' );
	?>
	<div class="<?php echo esc_attr( $class ); ?>">
		<?php if ( $args['eyebrow'] ) : ?>
			<?php ssbd_eyebrow( $args['eyebrow'] ); ?>
		<?php endif; ?>
		<<?php echo esc_attr( $tag ); ?><?php echo $args['id'] ? ' id="' . esc_attr( $args['id'] ) . '"' : ''; ?>><?php echo esc_html( $heading ); ?></<?php echo esc_attr( $tag ); ?>>
		<?php if ( $lede ) : ?>
			<p><?php echo esc_html( $lede ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Breadcrumb trail. Also feeds BreadcrumbList JSON-LD in inc/schema.php.
 *
 * @return array<array{name:string,url:string}>
 */
function ssbd_breadcrumb_trail() {
	$trail = array( array( 'name' => __( 'Home', 'ssbd' ), 'url' => home_url( '/' ) ) );

	if ( is_singular( 'post' ) ) {
		$blog_id = (int) get_option( 'page_for_posts' );
		if ( $blog_id ) {
			$trail[] = array( 'name' => get_the_title( $blog_id ), 'url' => get_permalink( $blog_id ) );
		}
		$trail[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
	} elseif ( is_singular( 'ssbd_service' ) ) {
		// Home / Services / Web Development / Laravel Development — the hub
		// page is not an ancestor in the database, but it is one to a reader.
		$trail[] = array( 'name' => __( 'Services', 'ssbd' ), 'url' => ssbd_page_url( 'services' ) );

		foreach ( array_reverse( get_post_ancestors( get_queried_object_id() ) ) as $ancestor ) {
			$trail[] = array( 'name' => get_the_title( $ancestor ), 'url' => get_permalink( $ancestor ) );
		}

		$trail[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
	} elseif ( is_page() && get_queried_object_id() ) {
		foreach ( array_reverse( get_post_ancestors( get_queried_object_id() ) ) as $ancestor ) {
			$trail[] = array( 'name' => get_the_title( $ancestor ), 'url' => get_permalink( $ancestor ) );
		}
		$trail[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		// The Blog page is not an ancestor of a category in the database, but
		// it is one to a reader — a category is a slice of the blog. Without
		// this step the trail on an archive reads Home / Domain Hosting and
		// offers no way up except all the way back to the front page. Scoped
		// to categories and tags: another taxonomy need not belong to posts.
		$blog_id = ( is_category() || is_tag() ) ? (int) get_option( 'page_for_posts' ) : 0;

		if ( $blog_id ) {
			$trail[] = array( 'name' => get_the_title( $blog_id ), 'url' => get_permalink( $blog_id ) );
		}

		$trail[] = array( 'name' => single_term_title( '', false ), 'url' => get_term_link( get_queried_object() ) );
	} elseif ( is_search() ) {
		$trail[] = array( 'name' => __( 'Search results', 'ssbd' ), 'url' => get_search_link() );
	}

	return apply_filters( 'ssbd_breadcrumb_trail', $trail );
}

/**
 * Output the visible breadcrumb trail.
 */
function ssbd_breadcrumbs() {
	$trail = ssbd_breadcrumb_trail();

	if ( count( $trail ) < 2 ) {
		return;
	}

	echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'ssbd' ) . '">';
	$last = count( $trail ) - 1;
	foreach ( $trail as $i => $crumb ) {
		if ( $i > 0 ) {
			echo '<span class="sep" aria-hidden="true">/</span>';
		}
		if ( $i === $last ) {
			echo '<span aria-current="page">' . esc_html( $crumb['name'] ) . '</span>';
		} else {
			echo '<a href="' . esc_url( $crumb['url'] ) . '">' . esc_html( $crumb['name'] ) . '</a>';
		}
	}
	echo '</nav>';
}

/**
 * Estimated reading time for the current post, in minutes.
 *
 * @param int|null $post_id Post ID.
 * @return int
 */
function ssbd_reading_time( $post_id = null ) {
	$content = get_post_field( 'post_content', $post_id ?: get_the_ID() );
	$words   = str_word_count( wp_strip_all_tags( (string) $content ) );

	return max( 1, (int) ceil( $words / 220 ) );
}

/**
 * Pagination for archives.
 */
function ssbd_pagination() {
	the_posts_pagination( array(
		'class'              => 'pagination',
		'mid_size'           => 2,
		'prev_text'          => __( 'Previous', 'ssbd' ),
		'next_text'          => __( 'Next', 'ssbd' ),
		'screen_reader_text' => __( 'Articles pagination', 'ssbd' ),
	) );
}

/**
 * The editorial name posts are published under when no real person is set.
 *
 * @return string
 */
function ssbd_editorial_name() {
	return sprintf(
		/* translators: %s: business name */
		__( '%s Editorial Team', 'ssbd' ),
		ssbd_site( 'legal_name', get_bloginfo( 'name' ) )
	);
}

/**
 * The name to publish the current post under.
 *
 * get_the_author() returns the WordPress display_name, which on a fresh
 * install is the user_login — so every article, every <meta name="author">
 * and every Article JSON-LD node on this site was signed "admin" while the
 * author box underneath said "Software Service BD Editorial Team". Two
 * different bylines on one page, one of them the default account name, is
 * exactly the E-E-A-T signal a reviewer looks for.
 *
 * A display name that is still the login, or one of the stock administrator
 * names, is treated as unset and falls back to the editorial name. Set a real
 * display name on the user profile and it takes over everywhere at once.
 *
 * @param int|null $post_id Post ID. Defaults to the current post.
 * @return string
 */
function ssbd_author_name( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();

	// A name typed directly onto the post wins over anything derived from the
	// WordPress account that owns it — the account is who has edit access,
	// which is not always who is being credited as having written the piece.
	$override = trim( (string) get_post_meta( $post_id, '_ssbd_author_name', true ) );
	if ( '' !== $override ) {
		/** Filter the resolved author name. */
		return (string) apply_filters( 'ssbd_author_name', $override, 0, true );
	}

	$author_id = (int) get_post_field( 'post_author', $post_id );
	$user      = $author_id ? get_userdata( $author_id ) : false;

	if ( $user ) {
		$name = trim( (string) $user->display_name );

		/** Filter the names treated as "no real author set". */
		$placeholders = apply_filters(
			'ssbd_placeholder_author_names',
			array( 'admin', 'administrator', 'user', 'editor', strtolower( (string) $user->user_login ) )
		);

		if ( '' !== $name && ! in_array( strtolower( $name ), $placeholders, true ) ) {
			/** Filter the resolved author name. */
			return (string) apply_filters( 'ssbd_author_name', $name, $author_id, true );
		}
	}

	return (string) apply_filters( 'ssbd_author_name', ssbd_editorial_name(), $author_id, false );
}

/**
 * Whether the current post is signed by a named person rather than the
 * editorial fallback. Drives the Person vs Organization split in schema.
 *
 * @param int|null $post_id Post ID.
 * @return bool
 */
function ssbd_author_is_person( $post_id = null ) {
	return ssbd_author_name( $post_id ) !== ssbd_editorial_name();
}

/**
 * The small circular photo shown beside the byline and in the author box.
 *
 * Reads the same per-post override as ssbd_author_name(), so a post signed
 * "Azhar Uddin" and a photo of someone else never happens — the two are set
 * in the same place, the post's own edit screen, and there is nothing here
 * that falls back to a WordPress user's Gravatar: an account photo says who
 * has edit access, not who is being credited, and the two already diverge
 * the moment ssbd_author_name() prefers the on-post override.
 *
 * @param int|null $post_id Post ID. Defaults to the current post.
 * @return string Image URL, or '' to fall back to the site mark.
 */
function ssbd_author_photo( $post_id = null ) {
	$post_id      = $post_id ?: get_the_ID();
	$attachment_id = (int) get_post_meta( $post_id, '_ssbd_author_photo', true );

	if ( ! $attachment_id ) {
		return '';
	}

	$src = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );

	/** Filter the resolved byline photo URL. */
	return (string) apply_filters( 'ssbd_author_photo', $src ?: '', $attachment_id, $post_id );
}

/**
 * What this site calls its article hub.
 *
 * There were five names for one destination: the front page section said
 * "Latest Insights & Resources", its button said "View all articles", the
 * blog masthead's eyebrow said "News & Insights", its H1 said "Blog" (the
 * Posts page title), and the footer and nav said "Blog" again. Clicking
 * "Insights & Resources → View all articles" and landing on "Blog" breaks the
 * scent, spends an internal link on anchor text that describes nothing, and
 * gives Google and an answer engine four candidate names for one entity.
 *
 * So there is one name now, and it is the Posts page title — the thing a site
 * owner can already edit, and the one that was already driving the H1, the
 * breadcrumb and the footer link. Rename that page and the whole site follows.
 *
 * Name it a plural noun ("Insights", "Guides"), because ssbd_blog_link_label()
 * builds "All Insights" out of it.
 *
 * @return string
 */
function ssbd_blog_label() {
	$blog_id = (int) get_option( 'page_for_posts' );
	$label   = $blog_id ? trim( (string) get_the_title( $blog_id ) ) : '';

	if ( '' === $label ) {
		$label = __( 'Insights', 'ssbd' );
	}

	/** Filter the name used for the article hub everywhere it is referred to. */
	return (string) apply_filters( 'ssbd_blog_label', $label );
}

/**
 * Anchor text for a link to the article hub.
 *
 * "View all articles" told a crawler nothing about the page it pointed at.
 * Naming the destination is what an internal link is for.
 *
 * @return string
 */
function ssbd_blog_link_label() {
	/* translators: %s: the name of the article hub, e.g. "Insights" */
	return sprintf( __( 'All %s', 'ssbd' ), ssbd_blog_label() );
}
