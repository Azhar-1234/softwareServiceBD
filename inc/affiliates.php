<?php
/**
 * Affiliate management.
 *
 * Three jobs:
 *
 *  1. Hold each partner offer as editable content (an Offer post type), so a
 *     price or a link is changed in one place and every table, card and
 *     comparison that mentions it updates.
 *
 *  2. Cloak outbound links behind /go/{slug}. That keeps raw tracking URLs out
 *     of the markup, lets a link be repointed without editing old posts, and
 *     gives you a click count per offer. The redirect is noindex + nofollow so
 *     the money links never enter the crawl graph.
 *
 *  3. Keep the site compliant: rel="sponsored nofollow" on every affiliate
 *     link, and an automatic disclosure notice on any page that carries one.
 *     This is not optional — see the FTC endorsement guides and Google's
 *     spam policy on affiliate content.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * The path prefix outbound affiliate links are cloaked behind.
 *
 * @return string
 */
function ssbd_affiliate_prefix() {
	/** Filter the cloaking prefix, e.g. 'go', 'ref', 'recommends'. */
	return sanitize_title( apply_filters( 'ssbd_affiliate_prefix', 'go' ) );
}

/**
 * Register the Offer post type and its category taxonomy.
 */
function ssbd_register_offers() {
	register_post_type( 'ssbd_offer', array(
		'labels'              => ssbd_cpt_labels( __( 'Offer', 'ssbd' ), __( 'Affiliate Offers', 'ssbd' ) ),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'query_var'           => false,
		'menu_icon'           => 'dashicons-cart',
		'menu_position'       => 24,
		'capability_type'     => 'post',
		'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
	) );

	register_taxonomy( 'ssbd_offer_category', 'ssbd_offer', array(
		'labels'            => ssbd_tax_labels( __( 'Offer category', 'ssbd' ), __( 'Offer categories', 'ssbd' ) ),
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'hierarchical'      => true,
		'rewrite'           => false,
		'query_var'         => false,
	) );
}
add_action( 'init', 'ssbd_register_offers' );

/**
 * Field schema for the Offer edit screen.
 *
 * @return array
 */
function ssbd_offer_fields() {
	return array(
		'vendor'    => array( 'label' => __( 'Vendor', 'ssbd' ), 'type' => 'text', 'placeholder' => 'Hostinger', 'help' => __( 'The company behind the product.', 'ssbd' ) ),
		'url'       => array(
			'label'       => __( 'Affiliate link', 'ssbd' ),
			'type'        => 'text',
			'placeholder' => 'https://hostinger.com?REFERRALCODE=xxxx',
			'help'        => __( 'Your raw tracking URL from the partner dashboard. Never published directly — visitors only ever see the cloaked link below.', 'ssbd' ),
		),
		'is_affiliate' => array(
			'label'   => __( 'Link type', 'ssbd' ),
			'type'    => 'select',
			'options' => array(
				'yes' => __( 'Affiliate link (paid commission)', 'ssbd' ),
				'no'  => __( 'Plain recommendation (no commission)', 'ssbd' ),
			),
			'help'    => __( 'Affiliate links get rel="sponsored" and trigger the disclosure notice. Be honest here — this is the compliance switch.', 'ssbd' ),
		),
		'price'     => array( 'label' => __( 'Price label', 'ssbd' ), 'type' => 'text', 'placeholder' => 'From $2.99/mo' ),
		'rating'    => array( 'label' => __( 'Rating out of 5', 'ssbd' ), 'type' => 'text', 'placeholder' => '4.5' ),
		'best_for'  => array( 'label' => __( 'Best for', 'ssbd' ), 'type' => 'text', 'placeholder' => 'WordPress beginners' ),
		'summary'   => array( 'label' => __( 'One-line verdict', 'ssbd' ), 'type' => 'textarea', 'help' => __( 'Your own assessment. Answer engines and readers both quote this.', 'ssbd' ) ),
		'pros'      => array( 'label' => __( 'Pros', 'ssbd' ), 'type' => 'list', 'help' => __( 'One per line.', 'ssbd' ) ),
		'cons'      => array(
			'label' => __( 'Cons', 'ssbd' ),
			'type'  => 'list',
			'help'  => __( 'One per line. Fill this in properly — a review with no downsides reads as paid, and Google treats thin affiliate pages as spam.', 'ssbd' ),
		),
		'features'  => array(
			'label'       => __( 'Comparison rows', 'ssbd' ),
			'type'        => 'list',
			'placeholder' => "Starting price: $2.99/mo\nFree SSL: Yes",
			'help'        => __( 'One "Label: value" per line. These become the rows of any comparison table this offer appears in.', 'ssbd' ),
		),
		'cta'       => array( 'label' => __( 'Button label', 'ssbd' ), 'type' => 'text', 'placeholder' => 'Visit Hostinger' ),
		'coupon'    => array( 'label' => __( 'Coupon code', 'ssbd' ), 'type' => 'text', 'help' => __( 'Optional. Shown on the card when set.', 'ssbd' ) ),
	);
}

/**
 * Add the Offer meta boxes.
 */
function ssbd_add_offer_meta_boxes() {
	add_meta_box( 'ssbd-offer', __( 'Offer details', 'ssbd' ), 'ssbd_render_offer_meta_box', 'ssbd_offer', 'normal', 'high' );
	add_meta_box( 'ssbd-offer-stats', __( 'Performance', 'ssbd' ), 'ssbd_render_offer_stats', 'ssbd_offer', 'side', 'default' );
}
add_action( 'add_meta_boxes', 'ssbd_add_offer_meta_boxes' );

/**
 * Render the Offer fields.
 *
 * @param WP_Post $post Post being edited.
 */
function ssbd_render_offer_meta_box( $post ) {
	wp_nonce_field( 'ssbd_offer_save', 'ssbd_offer_nonce' );
	?>
	<style>
		.ssbd-field { margin: 0 0 18px; }
		.ssbd-field > label { display: block; font-weight: 600; margin-bottom: 4px; }
		.ssbd-field input[type=text], .ssbd-field textarea, .ssbd-field select { width: 100%; max-width: 620px; }
		.ssbd-field textarea { min-height: 70px; }
		.ssbd-field .description { display: block; margin-top: 4px; }
		.ssbd-cloak { padding: 10px 12px; background: #f0f6fc; border-left: 3px solid #2271b1; margin: 0 0 18px; }
		.ssbd-cloak code { font-size: 13px; }
	</style>

	<?php if ( $post->post_name ) : ?>
		<p class="ssbd-cloak">
			<strong><?php esc_html_e( 'Public link for this offer:', 'ssbd' ); ?></strong><br>
			<code><?php echo esc_url( ssbd_offer_link( $post->ID ) ); ?></code><br>
			<span class="description"><?php esc_html_e( 'Use this anywhere — posts, emails, social. It redirects to your affiliate URL and counts the click. Change the affiliate URL above and every existing link follows automatically.', 'ssbd' ); ?></span>
		</p>
	<?php else : ?>
		<p class="ssbd-cloak"><?php esc_html_e( 'Save this offer to generate its cloaked link.', 'ssbd' ); ?></p>
	<?php endif; ?>

	<?php
	foreach ( ssbd_offer_fields() as $key => $field ) {
		$id    = 'ssbd_' . $key;
		$value = get_post_meta( $post->ID, '_ssbd_' . $key, true );

		if ( 'is_affiliate' === $key && '' === $value ) {
			$value = 'yes';
		}
		?>
		<div class="ssbd-field">
			<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['label'] ); ?></label>

			<?php if ( in_array( $field['type'], array( 'textarea', 'list' ), true ) ) : ?>
				<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" rows="<?php echo 'list' === $field['type'] ? 5 : 3; ?>"
					placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"><?php echo esc_textarea( $value ); ?></textarea>

			<?php elseif ( 'select' === $field['type'] ) : ?>
				<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>">
					<?php foreach ( $field['options'] as $option => $label ) : ?>
						<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $value, $option ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>

			<?php else : ?>
				<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>"
					value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>">
			<?php endif; ?>

			<?php if ( ! empty( $field['help'] ) ) : ?>
				<span class="description"><?php echo esc_html( $field['help'] ); ?></span>
			<?php endif; ?>
		</div>
		<?php
	}
}

/**
 * Click stats for an offer.
 *
 * @param WP_Post $post Post being edited.
 */
function ssbd_render_offer_stats( $post ) {
	$clicks = (int) get_post_meta( $post->ID, '_ssbd_clicks', true );
	$last   = get_post_meta( $post->ID, '_ssbd_last_click', true );
	?>
	<p style="font-size:28px;font-weight:700;margin:0"><?php echo esc_html( number_format_i18n( $clicks ) ); ?></p>
	<p style="margin:0 0 12px;color:#666"><?php esc_html_e( 'clicks all time', 'ssbd' ); ?></p>

	<?php if ( $last ) : ?>
		<p style="margin:0;color:#666">
			<?php
			printf(
				/* translators: %s: human readable time difference */
				esc_html__( 'Last click %s ago', 'ssbd' ),
				esc_html( human_time_diff( strtotime( $last ) ) )
			);
			?>
		</p>
	<?php endif; ?>

	<p class="description" style="margin-top:12px">
		<?php esc_html_e( 'Clicks are counted on the cloaked link. Compare with the conversions in your partner dashboard to see which placements actually earn.', 'ssbd' ); ?>
	</p>
	<?php
}

/**
 * Save the Offer fields.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 */
function ssbd_save_offer( $post_id, $post ) {
	if ( 'ssbd_offer' !== $post->post_type ) {
		return;
	}
	if ( ! isset( $_POST['ssbd_offer_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['ssbd_offer_nonce'] ) ), 'ssbd_offer_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( ssbd_offer_fields() as $key => $field ) {
		$input = 'ssbd_' . $key;

		if ( ! isset( $_POST[ $input ] ) ) {
			continue;
		}

		$raw = wp_unslash( $_POST[ $input ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized per type below.

		if ( 'url' === $key ) {
			$value = esc_url_raw( trim( $raw ) );
		} elseif ( in_array( $field['type'], array( 'textarea', 'list' ), true ) ) {
			$value = sanitize_textarea_field( $raw );
		} elseif ( 'select' === $field['type'] ) {
			$value = array_key_exists( $raw, $field['options'] ) ? $raw : '';
		} else {
			$value = sanitize_text_field( $raw );
		}

		if ( '' === $value ) {
			delete_post_meta( $post_id, '_ssbd_' . $key );
		} else {
			update_post_meta( $post_id, '_ssbd_' . $key, $value );
		}
	}
}
add_action( 'save_post', 'ssbd_save_offer', 10, 2 );

/* ==========================================================================
   Link cloaking and click tracking
   ========================================================================== */

/**
 * The public, cloaked link for an offer.
 *
 * @param int|WP_Post $offer Offer.
 * @return string
 */
function ssbd_offer_link( $offer ) {
	$post = get_post( $offer );

	if ( ! $post || ! $post->post_name ) {
		return '';
	}

	return home_url( '/' . ssbd_affiliate_prefix() . '/' . $post->post_name . '/' );
}

/**
 * Register the /go/{slug} route.
 */
function ssbd_offer_rewrite() {
	add_rewrite_rule(
		'^' . ssbd_affiliate_prefix() . '/([^/]+)/?$',
		'index.php?ssbd_go=$matches[1]',
		'top'
	);
}
add_action( 'init', 'ssbd_offer_rewrite' );

/**
 * Register the query var behind the route.
 *
 * @param array $vars Query vars.
 * @return array
 */
function ssbd_offer_query_var( $vars ) {
	$vars[] = 'ssbd_go';
	return $vars;
}
add_filter( 'query_vars', 'ssbd_offer_query_var' );

/**
 * Handle an outbound click: count it, then redirect.
 */
function ssbd_handle_offer_redirect() {
	$slug = get_query_var( 'ssbd_go' );

	if ( ! $slug ) {
		return;
	}

	$offer = get_page_by_path( sanitize_title( $slug ), OBJECT, 'ssbd_offer' );
	$url   = $offer ? (string) get_post_meta( $offer->ID, '_ssbd_url', true ) : '';

	if ( ! $url ) {
		// A dead or renamed offer should not look like a broken site.
		wp_safe_redirect( home_url( '/' ), 302 );
		exit;
	}

	// Bots follow these links too; only count what looks like a person.
	if ( ! ssbd_is_bot() ) {
		update_post_meta( $offer->ID, '_ssbd_clicks', (int) get_post_meta( $offer->ID, '_ssbd_clicks', true ) + 1 );
		update_post_meta( $offer->ID, '_ssbd_last_click', current_time( 'mysql' ) );

		/**
		 * Fires on a counted affiliate click. Hook analytics here.
		 *
		 * @param int    $offer_id Offer post ID.
		 * @param string $url      Destination.
		 */
		do_action( 'ssbd_affiliate_click', $offer->ID, $url );
	}

	// Never let the money link into the index or the crawl graph.
	header( 'X-Robots-Tag: noindex, nofollow', true );
	header( 'Referrer-Policy: no-referrer-when-downgrade' );
	nocache_headers();

	// 302, not 301: the destination is a tracking URL that will change, and a
	// permanent redirect would be cached in browsers long after it does.
	wp_redirect( $url, 302 ); // phpcs:ignore WordPress.Security.SafeRedirect -- outbound affiliate destination by design.
	exit;
}
add_action( 'template_redirect', 'ssbd_handle_offer_redirect' );

/**
 * A crude bot check, used only to keep click counts honest.
 *
 * @return bool
 */
function ssbd_is_bot() {
	$agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) ) : '';

	if ( '' === $agent ) {
		return true;
	}

	foreach ( array( 'bot', 'crawl', 'spider', 'slurp', 'curl', 'wget', 'python', 'headless', 'preview', 'monitor' ) as $needle ) {
		if ( str_contains( $agent, $needle ) ) {
			return true;
		}
	}

	return false;
}

/*
 * The cloak directory is kept out of search engines by
 * ssbd_robots_site_rules() in inc/geo.php, which owns the whole
 * "User-agent: *" group. Appending a bare Disallow from here, as this file
 * used to, put the rule wherever the file happened to end — which, once an
 * SEO plugin had rewritten the heading above it, was inside whichever group
 * was declared last rather than the site-wide one.
 */

/* ==========================================================================
   Compliance
   ========================================================================== */

/**
 * Add rel="sponsored nofollow noopener" to affiliate links in content.
 *
 * Google requires sponsored or nofollow on any link placed for compensation.
 * Doing it here rather than by hand means it cannot be forgotten in a post.
 *
 * @param string $content Post content.
 * @return string
 */
function ssbd_mark_affiliate_links( $content ) {
	if ( ! str_contains( $content, '/' . ssbd_affiliate_prefix() . '/' ) ) {
		return $content;
	}

	return preg_replace_callback(
		'#<a\s([^>]*href=[\'"][^\'"]*/' . preg_quote( ssbd_affiliate_prefix(), '#' ) . '/[^\'"]*[\'"][^>]*)>#i',
		static function ( $matches ) {
			$attrs = $matches[1];

			// Merge with any rel the author already wrote.
			if ( preg_match( '#rel=[\'"]([^\'"]*)[\'"]#i', $attrs, $rel ) ) {
				$values = array_unique( array_merge( preg_split( '/\s+/', trim( $rel[1] ) ), array( 'sponsored', 'nofollow', 'noopener' ) ) );
				$attrs  = preg_replace( '#rel=[\'"][^\'"]*[\'"]#i', 'rel="' . implode( ' ', array_filter( $values ) ) . '"', $attrs );
			} else {
				$attrs .= ' rel="sponsored nofollow noopener"';
			}

			return '<a ' . $attrs . '>';
		},
		$content
	);
}
add_filter( 'the_content', 'ssbd_mark_affiliate_links', 15 );

/**
 * Has anything on this request rendered an affiliate link?
 *
 * @param bool|null $set Pass true to mark the page as carrying one.
 * @return bool
 */
function ssbd_page_has_affiliate_links( $set = null ) {
	static $found = false;

	if ( true === $set ) {
		$found = true;
	}

	return $found;
}

/**
 * The disclosure notice.
 *
 * Rendered automatically above content that contains affiliate links. The FTC
 * requires disclosure to be clear and near the links, not buried in a footer.
 *
 * @param bool $inline Render the compact inline variant.
 */
function ssbd_affiliate_disclosure( $inline = false ) {
	$url = ssbd_page_url( 'affiliate-disclosure' );
	?>
	<p class="affiliate-disclosure<?php echo $inline ? ' affiliate-disclosure--inline' : ''; ?>">
		<?php
		printf(
			/* translators: %s: link to the affiliate disclosure page */
			esc_html__( 'Some links on this page are affiliate links. If you buy through one we may earn a commission at no extra cost to you, and it never changes what we recommend. %s', 'ssbd' ),
			'<a href="' . esc_url( $url ) . '">' . esc_html__( 'Full disclosure', 'ssbd' ) . '</a>'
		);
		?>
	</p>
	<?php
}

/**
 * Prepend the disclosure to any single post containing affiliate links.
 *
 * @param string $content Post content.
 * @return string
 */
function ssbd_auto_disclosure( $content ) {
	if ( ! is_singular() || is_admin() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	if ( ! str_contains( $content, '/' . ssbd_affiliate_prefix() . '/' ) ) {
		return $content;
	}

	ob_start();
	ssbd_affiliate_disclosure();

	return ob_get_clean() . $content;
}
add_filter( 'the_content', 'ssbd_auto_disclosure', 16 );

/* ==========================================================================
   Reading offers back out
   ========================================================================== */

/**
 * Offers, optionally narrowed to one category.
 *
 * @param string $category Category slug, or 'all'.
 * @param int    $limit    Maximum to return.
 * @return array<array<string, mixed>>
 */
function ssbd_offers( $category = 'all', $limit = 50 ) {
	$args = array(
		'post_type'   => 'ssbd_offer',
		'post_status' => 'publish',
		'numberposts' => $limit,
		'orderby'     => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	);

	if ( 'all' !== $category ) {
		$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => 'ssbd_offer_category',
				'field'    => 'slug',
				'terms'    => $category,
			),
		);
	}

	return array_map( 'ssbd_offer_data', get_posts( $args ) );
}

/**
 * Normalise one offer into a render-ready array.
 *
 * @param WP_Post $post Offer post.
 * @return array
 */
function ssbd_offer_data( $post ) {
	$features = array();

	foreach ( ssbd_meta_list( $post->ID, 'features' ) as $line ) {
		$parts = explode( ':', $line, 2 );
		if ( count( $parts ) === 2 ) {
			$features[ trim( $parts[0] ) ] = trim( $parts[1] );
		}
	}

	$destination = (string) get_post_meta( $post->ID, '_ssbd_url', true );

	return array(
		'id'           => $post->ID,
		// The post slug, so content elsewhere can name an offer stably —
		// quick picks on the comparison pillars reference these rather than
		// hardcoding a URL that would rot the moment an offer is renamed.
		'slug'         => $post->post_name,
		'name'         => get_the_title( $post ),
		'vendor'       => (string) get_post_meta( $post->ID, '_ssbd_vendor', true ),

		// No destination means no button. The cloaked route would resolve, but
		// it would bounce the visitor back to the homepage — an offer awaiting
		// its tracking URL is a plain recommendation, not a broken link.
		'link'         => $destination ? ssbd_offer_link( $post ) : '',
		'is_affiliate' => 'no' !== get_post_meta( $post->ID, '_ssbd_is_affiliate', true ),
		'price'        => (string) get_post_meta( $post->ID, '_ssbd_price', true ),
		'rating'       => (float) get_post_meta( $post->ID, '_ssbd_rating', true ),
		'best_for'     => (string) get_post_meta( $post->ID, '_ssbd_best_for', true ),
		'summary'      => (string) get_post_meta( $post->ID, '_ssbd_summary', true ),
		'coupon'       => (string) get_post_meta( $post->ID, '_ssbd_coupon', true ),
		'cta'          => (string) get_post_meta( $post->ID, '_ssbd_cta', true ) ?: __( 'View deal', 'ssbd' ),
		'pros'         => ssbd_meta_list( $post->ID, 'pros' ),
		'cons'         => ssbd_meta_list( $post->ID, 'cons' ),
		'features'     => $features,
		'logo'         => has_post_thumbnail( $post ) ? (string) get_the_post_thumbnail_url( $post, 'medium' ) : '',
		'categories'   => ssbd_term_slugs( $post->ID, 'ssbd_offer_category' ),
	);
}

/**
 * Render an affiliate button.
 *
 * Always carries rel="sponsored nofollow noopener" when the offer is paid.
 *
 * @param array  $offer Offer data.
 * @param string $class Extra classes.
 */
function ssbd_offer_button( $offer, $class = 'btn btn--accent' ) {
	if ( empty( $offer['link'] ) ) {
		return;
	}

	ssbd_page_has_affiliate_links( $offer['is_affiliate'] ? true : null );

	printf(
		'<a class="%s" href="%s" rel="%s" target="_blank">%s <span aria-hidden="true">→</span></a>',
		esc_attr( $class ),
		esc_url( $offer['link'] ),
		esc_attr( $offer['is_affiliate'] ? 'sponsored nofollow noopener' : 'noopener' ),
		esc_html( $offer['cta'] )
	);
}

/* ==========================================================================
   Shortcodes, for dropping offers into posts
   ========================================================================== */

/**
 * [ssbd_offer slug="hostinger"] — a single offer card.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function ssbd_offer_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'slug' => '', 'style' => 'card' ), $atts, 'ssbd_offer' );

	$post = $atts['slug'] ? get_page_by_path( sanitize_title( $atts['slug'] ), OBJECT, 'ssbd_offer' ) : null;

	if ( ! $post ) {
		return '';
	}

	ob_start();
	get_template_part( 'template-parts/components/offer-card', null, array(
		'offer' => ssbd_offer_data( $post ),
		'style' => $atts['style'],
	) );

	return ob_get_clean();
}
add_shortcode( 'ssbd_offer', 'ssbd_offer_shortcode' );

/**
 * [ssbd_offers category="hosting" limit="3"] — a grid of offer cards.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function ssbd_offers_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'category' => 'all', 'limit' => 3 ), $atts, 'ssbd_offers' );

	$offers = ssbd_offers( sanitize_title( $atts['category'] ), (int) $atts['limit'] );

	if ( ! $offers ) {
		return '';
	}

	ob_start();
	echo '<div class="grid grid--3 offer-grid">';
	foreach ( $offers as $offer ) {
		get_template_part( 'template-parts/components/offer-card', null, array( 'offer' => $offer ) );
	}
	echo '</div>';

	return ob_get_clean();
}
add_shortcode( 'ssbd_offers', 'ssbd_offers_shortcode' );

/**
 * [ssbd_compare category="hosting" limit="4"] — a comparison table built from
 * the offers' feature rows.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function ssbd_compare_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'category' => 'all', 'limit' => 4 ), $atts, 'ssbd_compare' );

	$offers = ssbd_offers( sanitize_title( $atts['category'] ), (int) $atts['limit'] );

	if ( ! $offers ) {
		return '';
	}

	ob_start();
	get_template_part( 'template-parts/components/offer-table', null, array( 'offers' => $offers ) );

	return ob_get_clean();
}
add_shortcode( 'ssbd_compare', 'ssbd_compare_shortcode' );

/**
 * Flush rewrites so /go/ resolves right after activation.
 */
function ssbd_flush_offer_rewrites() {
	ssbd_offer_rewrite();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'ssbd_flush_offer_rewrites', 21 );

/* ==========================================================================
   Starter offers
   ========================================================================== */

/**
 * The offer category terms, seeded alongside the starter offers.
 *
 * @return array<string, string>
 */
function ssbd_offer_terms() {
	return array(
		'hosting'   => __( 'Hosting & Cloud', 'ssbd' ),
		'wordpress' => __( 'WordPress Plugins', 'ssbd' ),
		'saas'      => __( 'SaaS & Business Tools', 'ssbd' ),
		'ai'        => __( 'AI & Automation', 'ssbd' ),
		'marketing' => __( 'SEO & Marketing', 'ssbd' ),
	);
}

/**
 * Create the starter offers from inc/data/affiliate-offers.php.
 *
 * Created published, with the affiliate URL left blank.
 *
 * The recommendation is honest whether or not money is involved — these are
 * genuinely the tools we use — so the page should show them straight away.
 * An offer with no URL simply renders without a button; add the tracking URL
 * later and the button appears. Drafting them instead meant a freshly seeded
 * site still showed placeholder copy, which is confusing and looks broken.
 *
 * @return int Number created.
 */
function ssbd_seed_offers() {
	ssbd_register_offers();

	foreach ( ssbd_offer_terms() as $slug => $name ) {
		if ( ! term_exists( $slug, 'ssbd_offer_category' ) ) {
			wp_insert_term( $name, 'ssbd_offer_category', array( 'slug' => $slug ) );
		}
	}

	$created = 0;

	foreach ( ssbd_data( 'affiliate-offers' ) as $i => $offer ) {
		// Never duplicate: the slug is the identity, drafts included.
		if ( get_page_by_path( $offer['slug'], OBJECT, 'ssbd_offer' ) ) {
			continue;
		}

		$id = wp_insert_post( array(
			'post_type'   => 'ssbd_offer',
			'post_status' => 'publish',
			'post_title'  => $offer['name'],
			'post_name'   => $offer['slug'],
			'menu_order'  => $i + 1,
		) );

		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}

		foreach ( array( 'vendor', 'price', 'rating', 'best_for', 'cta', 'summary' ) as $key ) {
			if ( ! empty( $offer[ $key ] ) ) {
				update_post_meta( $id, '_ssbd_' . $key, $offer[ $key ] );
			}
		}

		foreach ( array( 'pros', 'cons', 'features' ) as $key ) {
			if ( ! empty( $offer[ $key ] ) ) {
				update_post_meta( $id, '_ssbd_' . $key, implode( "\n", $offer[ $key ] ) );
			}
		}

		update_post_meta( $id, '_ssbd_is_affiliate', 'yes' );
		wp_set_object_terms( $id, $offer['category'], 'ssbd_offer_category' );

		$created++;
	}

	return $created;
}

/**
 * Offer the starter set on the Offers list screen while it is empty.
 *
 * A button rather than automatic seeding: the theme may already be active, and
 * silently creating ten posts in someone's admin is not a pleasant surprise.
 */
function ssbd_offer_seed_notice() {
	$screen = get_current_screen();

	if ( ! $screen || 'edit-ssbd_offer' !== $screen->id || ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	if ( get_posts( array( 'post_type' => 'ssbd_offer', 'numberposts' => 1, 'post_status' => 'any', 'fields' => 'ids' ) ) ) {
		return;
	}

	$url = wp_nonce_url( admin_url( 'edit.php?post_type=ssbd_offer&ssbd_seed_offers=1' ), 'ssbd_seed_offers' );
	?>
	<div class="notice notice-info">
		<h3 style="margin-bottom:6px"><?php esc_html_e( 'Start with ten common offers?', 'ssbd' ); ?></h3>
		<p style="max-width:70ch">
			<?php esc_html_e( 'Hostinger, Namecheap, Cloudways, DigitalOcean, Kinsta, Elementor, WP Rocket, Rank Math, Semrush and Make — each with a written verdict, pros, cons and comparison rows already filled in.', 'ssbd' ); ?>
		</p>
		<p style="max-width:70ch">
			<strong><?php esc_html_e( 'The affiliate link is left blank on purpose.', 'ssbd' ); ?></strong>
			<?php esc_html_e( 'They appear on your Tools and Comparisons pages immediately as plain recommendations. Once you join a programme and paste its tracking URL, that offer grows a button and starts earning. Check every price against the vendor’s current pricing page.', 'ssbd' ); ?>
		</p>
		<p>
			<a href="<?php echo esc_url( $url ); ?>" class="button button-primary"><?php esc_html_e( 'Add the ten starter offers', 'ssbd' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'ssbd_offer_seed_notice' );

/**
 * Handle the seed button.
 */
function ssbd_handle_offer_seed() {
	if ( ! isset( $_GET['ssbd_seed_offers'] ) || ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	check_admin_referer( 'ssbd_seed_offers' );

	$created = ssbd_seed_offers();

	wp_safe_redirect( add_query_arg(
		array( 'post_type' => 'ssbd_offer', 'ssbd_seeded' => $created ),
		admin_url( 'edit.php' )
	) );
	exit;
}
add_action( 'admin_init', 'ssbd_handle_offer_seed' );

/**
 * Confirm what was created, and say what to do next.
 */
function ssbd_offer_seeded_notice() {
	$screen = get_current_screen();

	if ( ! $screen || 'edit-ssbd_offer' !== $screen->id || ! isset( $_GET['ssbd_seeded'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only confirmation.
		return;
	}

	$created = (int) $_GET['ssbd_seeded']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	?>
	<div class="notice notice-success is-dismissible">
		<p>
			<?php
			printf(
				/* translators: %d: number of offers created */
				esc_html( _n( '%d starter offer added.', '%d starter offers added.', $created, 'ssbd' ) ),
				esc_html( $created )
			);
			?>
			<?php esc_html_e( 'They are live on your Tools and Comparisons pages now. Open one and paste your affiliate link once the programme approves you — the “Affiliate link” column below shows which are still missing.', 'ssbd' ); ?>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'ssbd_offer_seeded_notice' );

/**
 * Published offers grouped by their category term.
 *
 * Returns the same shape as the `groups` array in inc/data/tools.php, so the
 * Tools page can swap between real offers and the shipped placeholder copy
 * without the template caring which it got.
 *
 * @return array<array{title:string,count:string,slug:string,items:array}>
 */
function ssbd_offer_groups() {
	$terms = get_terms( array(
		'taxonomy'   => 'ssbd_offer_category',
		'hide_empty' => true,
		'orderby'    => 'term_order',
	) );

	if ( is_wp_error( $terms ) || ! $terms ) {
		return array();
	}

	$groups = array();

	foreach ( $terms as $term ) {
		$offers = ssbd_offers( $term->slug );

		if ( ! $offers ) {
			continue;
		}

		$groups[] = array(
			'title' => $term->name,
			'slug'  => $term->slug,
			'count' => sprintf(
				/* translators: %d: number of recommended tools */
				_n( '%d pick', '%d picks', count( $offers ), 'ssbd' ),
				count( $offers )
			),
			'items' => $offers,
		);
	}

	return $groups;
}

/**
 * The offer category with the most published offers.
 *
 * Used to pick which comparison table to show on the Comparisons page without
 * hardcoding a category that might be empty.
 *
 * @return string Category slug, or '' when there are no offers.
 */
function ssbd_largest_offer_category() {
	$groups = ssbd_offer_groups();

	if ( ! $groups ) {
		return '';
	}

	usort(
		$groups,
		static function ( $a, $b ) {
			return count( $b['items'] ) <=> count( $a['items'] );
		}
	);

	return $groups[0]['slug'];
}

/**
 * Show which offers still need an affiliate link.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function ssbd_offer_columns( $columns ) {
	$new = array();

	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;

		if ( 'title' === $key ) {
			$new['ssbd_link']   = __( 'Affiliate link', 'ssbd' );
			$new['ssbd_clicks'] = __( 'Clicks', 'ssbd' );
		}
	}

	return $new;
}
add_filter( 'manage_ssbd_offer_posts_columns', 'ssbd_offer_columns' );

/**
 * Fill the Offer admin columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function ssbd_offer_column_content( $column, $post_id ) {
	if ( 'ssbd_link' === $column ) {
		$url = get_post_meta( $post_id, '_ssbd_url', true );

		if ( $url ) {
			printf( '<span style="color:#1e8156">&#10003; %s</span>', esc_html__( 'set', 'ssbd' ) );
		} else {
			printf( '<span style="color:#b32d2e">%s</span>', esc_html__( 'not set — earns nothing yet', 'ssbd' ) );
		}
	}

	if ( 'ssbd_clicks' === $column ) {
		echo esc_html( number_format_i18n( (int) get_post_meta( $post_id, '_ssbd_clicks', true ) ) );
	}
}
add_action( 'manage_ssbd_offer_posts_custom_column', 'ssbd_offer_column_content', 10, 2 );
