<?php
/**
 * Asset loading.
 *
 * The goal here is a critical path with nothing avoidable in it. Lighthouse
 * originally measured ~1,650ms of render-blocking requests across three files:
 *
 *   style.css       760ms  — 860 bytes of licence comments and nothing else
 *   Google Fonts    750ms  — a second origin the browser had to visit before
 *                            it could even discover the font files
 *   theme.css       260ms  — the only one that was actually earning its place
 *
 * So: style.css is no longer enqueued on the front end unless a child theme
 * gives it something to say, the fonts are self-hosted with their @font-face
 * rules inlined, and theme.css stays a normal cacheable stylesheet.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Version an asset by its mtime so cache busting is automatic.
 *
 * @param string $relative Path relative to the theme root.
 * @return string
 */
function ssbd_asset_version( $relative ) {
	$path = SSBD_DIR . '/' . ltrim( $relative, '/' );
	return file_exists( $path ) ? (string) filemtime( $path ) : SSBD_VERSION;
}

/**
 * Read a theme file, or return an empty string.
 *
 * @param string $relative Path relative to the theme root.
 * @return string
 */
function ssbd_read_asset( $relative ) {
	$path = SSBD_DIR . '/' . ltrim( $relative, '/' );

	if ( ! is_readable( $path ) ) {
		return '';
	}

	// Direct read is correct here: this is a bundled theme file, not
	// user-supplied content, and it is on the critical rendering path.
	return (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}

/**
 * The fonts whose absence would delay the first paint.
 *
 * Both are on the critical path for every page, and neither is discoverable
 * until the browser has parsed the inlined @font-face rules below:
 *
 *   Bricolage Grotesque  the LCP element on nearly every page — the H1, and
 *                        the .brand-name in the header before it
 *   Instrument Sans      --font-body, so every paragraph of visible text
 *
 * Lighthouse measured Instrument Sans arriving at 1,102ms without this,
 * against a 1,130ms critical path — it was the tail of the chain.
 *
 * @return string[]
 */
function ssbd_preload_font_urls() {
	$fonts = array(
		SSBD_URI . '/assets/fonts/bricolage-grotesque-latin.woff2',
		SSBD_URI . '/assets/fonts/instrument-sans-latin.woff2',
	);

	/** Filter the woff2 files preloaded in <head>. */
	return (array) apply_filters( 'ssbd_preload_font_urls', $fonts );
}

/**
 * Inline the @font-face rules and preload the two critical-path fonts.
 *
 * Inlining is worth it here: the file is ~3KB, and linking it instead would
 * put a render-blocking request back on the critical path purely to tell the
 * browser where the fonts live.
 */
function ssbd_font_head() {
	foreach ( ssbd_preload_font_urls() as $font_url ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( $font_url )
		);
	}

	$css = ssbd_read_asset( 'assets/css/fonts.css' );

	if ( ! $css ) {
		return;
	}

	// Rewrite the stylesheet-relative font paths to absolute URLs, since the
	// CSS is about to live in the document rather than in assets/css/.
	$css = str_replace( '../fonts/', SSBD_URI . '/assets/fonts/', $css );

	printf( '<style id="ssbd-fonts">%s</style>' . "\n", wp_strip_all_tags( $css ) );
}
add_action( 'wp_head', 'ssbd_font_head', 1 );

/**
 * The stylesheet to serve: the built one when it is current, else the source.
 *
 * theme.css is the file you edit; theme.min.css is what `npm run build:css`
 * produces from it, and it saves ~6KB gzipped on a render-blocking request.
 * Comparing mtimes means an edit to the source that has not been rebuilt yet
 * falls back to the source rather than silently serving stale CSS.
 *
 * @return string Path relative to the theme root.
 */
function ssbd_theme_stylesheet() {
	$source = 'assets/css/theme.css';
	$built  = 'assets/css/theme.min.css';

	$source_path = SSBD_DIR . '/' . $source;
	$built_path  = SSBD_DIR . '/' . $built;

	if ( ! file_exists( $built_path ) ) {
		return $source;
	}

	return filemtime( $built_path ) >= filemtime( $source_path ) ? $built : $source;
}

/**
 * The build of a hand-written script to serve: the minified one when current.
 *
 * Same contract as ssbd_theme_stylesheet() one function up — `npm run build`
 * writes a .min.js beside each source, and an mtime comparison means an edit
 * that has not been rebuilt yet serves the source rather than stale output.
 * The React bundle is not routed through here: Vite already minifies it in
 * place, and it has no unminified twin.
 *
 * @param string $name File name inside assets/js, e.g. 'clipboard.js'.
 * @return string Path relative to the theme root.
 */
function ssbd_theme_script( $name ) {
	$source = 'assets/js/' . $name;
	$built  = 'assets/js/' . preg_replace( '/\.js$/', '.min.js', $name );

	$source_path = SSBD_DIR . '/' . $source;
	$built_path  = SSBD_DIR . '/' . $built;

	if ( ! file_exists( $built_path ) || ! file_exists( $source_path ) ) {
		return $source;
	}

	return filemtime( $built_path ) >= filemtime( $source_path ) ? $built : $source;
}

/**
 * Enqueue front-end styles and scripts.
 */
function ssbd_enqueue_assets() {
	$stylesheet = ssbd_theme_stylesheet();

	wp_enqueue_style(
		'ssbd-theme',
		SSBD_URI . '/' . $stylesheet,
		array(),
		ssbd_asset_version( $stylesheet )
	);

	/*
	 * style.css carries the theme header and nothing else, so loading it on
	 * the front end is a render-blocking request for zero CSS. A child theme's
	 * style.css does carry real rules, so that one still loads.
	 */
	if ( is_child_theme() ) {
		wp_enqueue_style(
			'ssbd-style',
			get_stylesheet_uri(),
			array( 'ssbd-theme' ),
			ssbd_asset_version( 'style.css' )
		);
	}

	wp_enqueue_script(
		'ssbd-link-targets',
		SSBD_URI . '/' . ssbd_theme_script( 'link-targets.js' ),
		array(),
		ssbd_asset_version( ssbd_theme_script( 'link-targets.js' ) ),
		array( 'strategy' => 'defer', 'in_footer' => true )
	);

	$bundle = 'assets/js/dist/app.js';
	if ( file_exists( SSBD_DIR . '/' . $bundle ) ) {
		wp_enqueue_script(
			'ssbd-app',
			SSBD_URI . '/' . $bundle,
			array(),
			ssbd_asset_version( $bundle ),
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);

		wp_localize_script( 'ssbd-app', 'ssbdConfig', array(
			'restUrl' => esc_url_raw( rest_url( 'ssbd/v1/' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'homeUrl' => home_url( '/' ),
			'i18n'    => array(
				'day'          => __( 'Day', 'ssbd' ),
				'night'        => __( 'Night', 'ssbd' ),
				'toggleTheme'  => __( 'Toggle day / night theme', 'ssbd' ),
				'sending'      => __( 'Sending…', 'ssbd' ),
				'sent'         => __( 'Inquiry sent ✓', 'ssbd' ),
				'sendInquiry'  => __( 'Send Project Inquiry', 'ssbd' ),
				'errorGeneric' => __( 'Something went wrong. Please email info@softwareservicebd.com instead.', 'ssbd' ),
				'required'     => __( 'This field is required.', 'ssbd' ),
				'invalidEmail' => __( 'Enter a valid email address.', 'ssbd' ),
				'noResults'    => __( 'Nothing matches that filter yet.', 'ssbd' ),
			),
		) );
	}

	/*
	 * The comparison table: search, filter, sort, paginate, export. Not part
	 * of the React bundle — that island filters on one dimension and cannot
	 * sort — and small enough that a table library would cost more than it
	 * saves. Loaded only on the page that has the table.
	 */
	if ( is_page_template( 'page-templates/template-comparisons.php' ) ) {
		wp_enqueue_script(
			'ssbd-comparison-table',
			SSBD_URI . '/' . ssbd_theme_script( 'comparison-table.js' ),
			array(),
			ssbd_asset_version( ssbd_theme_script( 'comparison-table.js' ) ),
			array( 'strategy' => 'defer', 'in_footer' => true )
		);
	}

	if ( is_post_type_archive( 'ssbd_job' ) || is_tax( 'ssbd_job_category' ) || is_singular( 'ssbd_job' ) || get_query_var( 'ssbd_saved_jobs' ) ) {
		wp_enqueue_script(
			'ssbd-jobs',
			SSBD_URI . '/' . ssbd_theme_script( 'jobs.js' ),
			array(),
			ssbd_asset_version( ssbd_theme_script( 'jobs.js' ) ),
			array( 'strategy' => 'defer', 'in_footer' => true )
		);
		wp_localize_script( 'ssbd-jobs', 'ssbdJobs', array(
			'savedEndpoint' => esc_url_raw( rest_url( 'ssbd/v1/saved-jobs' ) ),
			'jobsUrl'       => esc_url_raw( ssbd_jobs_url() ),
		) );
	}

	/*
	 * The clipboard helper. Registered rather than enqueued: it loads only
	 * because one of the scripts below declares it as a dependency.
	 */
	wp_register_script(
		'ssbd-clipboard',
		SSBD_URI . '/' . ssbd_theme_script( 'clipboard.js' ),
		array(),
		ssbd_asset_version( ssbd_theme_script( 'clipboard.js' ) ),
		array( 'strategy' => 'defer', 'in_footer' => true )
	);

	/*
	 * The rotating keyword in the homepage H1. Front page only, and deferred:
	 * the headline is already complete and painted without it — every keyword
	 * is in the markup with the first one marked active — so this is a
	 * progressive enhancement in the literal sense and must never sit on the
	 * critical path of the LCP element it decorates.
	 */
	if ( is_front_page() ) {
		wp_enqueue_script(
			'ssbd-hero',
			SSBD_URI . '/' . ssbd_theme_script( 'hero.js' ),
			array(),
			ssbd_asset_version( ssbd_theme_script( 'hero.js' ) ),
			array( 'strategy' => 'defer', 'in_footer' => true )
		);
	}

	/*
	 * Coupon copy buttons on the partner cards. They appear on the partner
	 * wall, which is the front page section and the Partners page.
	 */
	if ( is_front_page() || is_page_template( 'page-templates/template-partners.php' ) ) {
		wp_enqueue_script(
			'ssbd-partners',
			SSBD_URI . '/' . ssbd_theme_script( 'partners.js' ),
			array( 'ssbd-clipboard' ),
			ssbd_asset_version( ssbd_theme_script( 'partners.js' ) ),
			array( 'strategy' => 'defer', 'in_footer' => true )
		);
	}

	/*
	 * The article page: contents highlighting, the mobile contents toggle and
	 * the copy-link button in the share row.
	 */
	if ( is_singular( 'post' ) ) {
		wp_enqueue_script(
			'ssbd-article',
			SSBD_URI . '/' . ssbd_theme_script( 'article.js' ),
			array( 'ssbd-clipboard' ),
			ssbd_asset_version( ssbd_theme_script( 'article.js' ) ),
			array( 'strategy' => 'defer', 'in_footer' => true )
		);
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'ssbd_enqueue_assets' );

/**
 * Load the main theme stylesheet without holding first paint behind it.
 *
 * The theme already inlines font-face rules and does not depend on WordPress
 * global styles for its first viewport. Turning the remaining cacheable CSS
 * file into a preload removes the last render-blocking request Lighthouse
 * reports, while the noscript fallback keeps the page styled for visitors
 * without JavaScript.
 *
 * @param string $html   Link tag HTML.
 * @param string $handle Enqueued style handle.
 * @param string $href   Stylesheet URL.
 * @param string $media  Media attribute.
 * @return string
 */
function ssbd_async_theme_stylesheet( $html, $handle, $href, $media ) {
	if ( 'ssbd-theme' !== $handle || is_admin() ) {
		return $html;
	}

	$media = $media ?: 'all';

	return sprintf(
		'<link rel="preload" href="%1$s" as="style" media="%2$s" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n" .
		'<noscript><link rel="stylesheet" href="%1$s" media="%2$s"></noscript>' . "\n",
		esc_url( $href ),
		esc_attr( $media )
	);
}
add_filter( 'style_loader_tag', 'ssbd_async_theme_stylesheet', 10, 4 );

/**
 * Warm up third-party origins only when their scripts are not delayed.
 *
 * Site Kit pulls AdSense, Tag Manager and the consent messages in from three
 * separate origins, and each one costs a DNS lookup, a TCP handshake and a TLS
 * negotiation before its first byte arrives. Lighthouse measured them as the
 * three heaviest things on the page — 274KB, 167KB and 70KB — and reported no
 * preconnected origins at all, so every one of those handshakes was serial
 * with the request that triggered it.
 *
 * preconnect holds a socket open, so hinting at origins the page might not
 * reach is a cost, not a saving. When ssbd_delay_scripts() parks the ad stack
 * until interaction, these hints stay out of the head.
 *
 * @param string[] $urls URLs for the given relation.
 * @param string   $relation Relation type, e.g. 'preconnect'.
 * @return string[]
 */
function ssbd_resource_hints( $urls, $relation ) {
	if ( 'preconnect' !== $relation || is_admin() ) {
		return $urls;
	}

	/** Filter the third-party origins preconnected on the front end. */
	$origins = (array) apply_filters(
		'ssbd_preconnect_origins',
		ssbd_should_delay_scripts() ? array() : array(
			'https://pagead2.googlesyndication.com',
			'https://www.googletagmanager.com',
			'https://fundingchoicesmessages.google.com',
		)
	);

	foreach ( $origins as $origin ) {
		$urls[] = array(
			'href'        => $origin,
			'crossorigin' => 'anonymous',
		);
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'ssbd_resource_hints', 10, 2 );

/**
 * Trim WordPress head output the theme handles itself or does not need.
 */
function ssbd_clean_head() {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );

	// Core prints its own canonical; ours is pagination-aware.
	if ( ! ssbd_seo_plugin_active() ) {
		remove_action( 'wp_head', 'rel_canonical' );
	}
}
add_action( 'init', 'ssbd_clean_head' );

/**
 * Drop the emoji polyfill.
 *
 * It costs a script, a stylesheet and a DNS lookup to s.w.org, and every
 * browser this theme supports renders emoji natively.
 */
function ssbd_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'ssbd_disable_emojis' );

/**
 * Does this request actually render block content?
 *
 * @return bool
 */
function ssbd_needs_block_styles() {
	if ( is_singular() && has_blocks( get_queried_object_id() ) ) {
		return true;
	}

	/** Filter whether core's block stylesheets are needed on this request. */
	return (bool) apply_filters( 'ssbd_needs_block_styles', false );
}

/**
 * Drop core's block stylesheets on pages that contain no blocks.
 *
 * The templates render their own markup, so wp-block-library and the ~9KB of
 * inline global-styles are dead weight everywhere except posts and pages that
 * genuinely use the block editor.
 *
 * wp_enqueue_global_styles() has to be unhooked rather than dequeued: it
 * prints inline CSS directly and runs on both wp_enqueue_scripts and wp_footer,
 * so a plain wp_dequeue_style() never catches it.
 */
function ssbd_dequeue_block_styles() {
	if ( ssbd_needs_block_styles() ) {
		return;
	}

	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
	remove_action( 'wp_enqueue_scripts', 'wp_common_block_scripts_and_styles' );

	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'ssbd_dequeue_block_styles', 1 );
add_action( 'wp_enqueue_scripts', 'ssbd_dequeue_block_styles', 100 );
