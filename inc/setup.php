<?php
/**
 * Theme supports, menus, image sizes and first-run page scaffolding.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme supports and navigation locations.
 */
function ssbd_setup() {
	load_theme_textdomain( 'ssbd', SSBD_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'custom-logo', array(
		'height'      => 72,
		'width'       => 72,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );

	add_editor_style( 'assets/css/theme.css' );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'ssbd' ),
		'footer'  => __( 'Footer Menu', 'ssbd' ),
		'legal'   => __( 'Legal Menu', 'ssbd' ),
	) );

	add_image_size( 'ssbd-card', 720, 450, true );
	// A same-ratio companion so the srcset WordPress builds for 'ssbd-card' has
	// a right-sized candidate for a narrow viewport, rather than every screen
	// downloading the full 720x450 — Lighthouse measured the project cards
	// rendering at 398x249 CSS px, well under the registered size.
	add_image_size( 'ssbd-card-sm', 400, 250, true );
	// The blog portal's lead story and its thumbnail rail. Existing uploads have
	// no crop at these sizes until the media library is regenerated; WordPress
	// serves the nearest available file until then, so nothing breaks meanwhile.
	add_image_size( 'ssbd-hero', 1200, 675, true );
	add_image_size( 'ssbd-thumb', 240, 160, true );
}
add_action( 'after_setup_theme', 'ssbd_setup' );

/**
 * Generate thumbnail sizes as WebP instead of PNG/JPEG.
 *
 * Screenshots and photos uploaded as PNG carry none of the compression a
 * screenshot needs — Lighthouse measured three project screenshots at
 * 190-345KB each, with "modern format" accounting for most of the estimated
 * savings. This only affects the generated intermediate sizes (thumbnail,
 * ssbd-card, etc.) — the original file WordPress keeps as uploaded is
 * untouched, so nothing is lost if a size registration ever changes.
 *
 * Only takes effect for uploads made, or thumbnails regenerated, after this
 * is added. Existing attachments keep their existing PNG/JPEG subsizes until
 * something regenerates them (Media → an image's Edit screen, or a bulk
 * "Regenerate Thumbnails" plugin).
 *
 * @param array $formats Mime type map, input => output.
 * @return array
 */
function ssbd_webp_thumbnails( $formats ) {
	$formats['image/png']  = 'image/webp';
	$formats['image/jpeg'] = 'image/webp';

	return $formats;
}
add_filter( 'image_editor_output_format', 'ssbd_webp_thumbnails' );

/**
 * Content width for embeds.
 */
function ssbd_content_width() {
	$GLOBALS['content_width'] = 760;
}
add_action( 'after_setup_theme', 'ssbd_content_width', 0 );

/**
 * Blog sidebar / footer widget area.
 */
function ssbd_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Blog Sidebar', 'ssbd' ),
		'id'            => 'sidebar-blog',
		'description'   => __( 'Shown beside single posts and archives.', 'ssbd' ),
		'before_widget' => '<section id="%1$s" class="card widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3>',
		'after_title'   => '</h3>',
	) );
}
add_action( 'widgets_init', 'ssbd_widgets_init' );

/**
 * The pages this theme ships templates for.
 *
 * Used by the one-time scaffolder below and by ssbd_page_url() so that
 * templates can link to each other without hardcoded permalinks.
 *
 * @return array<string, array{title:string, template:string}>
 */
function ssbd_page_blueprint() {
	return array(
		'services'            => array( 'title' => 'Services', 'template' => 'page-templates/template-services.php' ),
		'solutions'           => array( 'title' => 'Solutions', 'template' => 'page-templates/template-solutions.php' ),
		'products'            => array( 'title' => 'Products', 'template' => 'page-templates/template-products.php' ),
		'portfolio'           => array( 'title' => 'Portfolio', 'template' => 'page-templates/template-portfolio.php' ),
		'resources'           => array( 'title' => 'Resources', 'template' => 'page-templates/template-resources.php' ),
		'tools'               => array( 'title' => 'Tools', 'template' => 'page-templates/template-tools.php' ),
		'comparisons'         => array( 'title' => 'Comparisons', 'template' => 'page-templates/template-comparisons.php' ),
		'about'               => array( 'title' => 'About', 'template' => 'page-templates/template-about.php' ),
		'contact'             => array( 'title' => 'Contact', 'template' => 'page-templates/template-contact.php' ),
		'order'               => array( 'title' => 'Order', 'template' => 'page-templates/template-order.php' ),
		'track-order'         => array( 'title' => 'Track Order', 'template' => 'page-templates/template-track-order.php' ),
		'partners'              => array( 'title' => 'Partners', 'template' => 'page-templates/template-partners.php' ),
		'login'               => array( 'title' => 'Login', 'template' => 'page-templates/template-login.php' ),
		'register'            => array( 'title' => 'Register', 'template' => 'page-templates/template-register.php' ),
		'privacy-policy'      => array( 'title' => 'Privacy Policy', 'template' => 'page-templates/template-legal.php' ),
		'terms'               => array( 'title' => 'Terms of Service', 'template' => 'page-templates/template-legal.php' ),
		'affiliate-disclosure' => array( 'title' => 'Affiliate Disclosure', 'template' => 'page-templates/template-legal.php' ),
	);
}

/**
 * Resolve a blueprint slug to a live permalink, falling back to the pretty
 * URL the page will have once it exists.
 *
 * The published check is the point. get_page_by_path() matches drafts and
 * pending pages too, and get_permalink() on one of those returns the ugly
 * `?page_id=N` form — which is how the footer came to link every page on the
 * site at https://softwareservicebd.com/?page_id=3 for a Privacy Policy that
 * had never been published. That URL 404s, and it advertises an unfinished
 * install to anyone reading the footer. Falling through to the pretty URL
 * still 404s until the page is published, but it 404s at the address the page
 * will actually occupy, so nothing has to be re-linked afterwards.
 *
 * @param string $slug Blueprint slug.
 * @return string
 */
function ssbd_page_url( $slug ) {
	$cache = wp_cache_get( 'ssbd_page_urls' );
	if ( ! is_array( $cache ) ) {
		$cache = array();
	}

	if ( isset( $cache[ $slug ] ) ) {
		return $cache[ $slug ];
	}

	$page = get_page_by_path( $slug );
	$url  = ( $page && 'publish' === $page->post_status )
		? get_permalink( $page )
		: home_url( '/' . $slug . '/' );

	$cache[ $slug ] = $url;
	wp_cache_set( 'ssbd_page_urls', $cache );

	return $url;
}

/**
 * Whether a blueprint page is published and safe to link to.
 *
 * Used by the footer's legal row: a link to a page that does not exist is
 * worse than no link at all, and an affiliate reviewer clicking a dead
 * Privacy Policy is the fastest way to fail a review.
 *
 * @param string $slug Blueprint slug.
 * @return bool
 */
function ssbd_page_exists( $slug ) {
	$page = get_page_by_path( $slug );

	return (bool) $page && 'publish' === $page->post_status;
}

/**
 * Create the site's pages, menus and reading settings on first activation.
 *
 * Runs once. Existing pages with a matching slug are reused, never overwritten.
 */
function ssbd_scaffold_site() {
	if ( get_option( 'ssbd_scaffolded' ) ) {
		return;
	}

	// Front page.
	$home = get_page_by_path( 'home' );
	if ( ! $home ) {
		$home_id = wp_insert_post( array(
			'post_title'   => 'Home',
			'post_name'    => 'home',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		) );
	} else {
		$home_id = $home->ID;
	}

	$blog = get_page_by_path( 'blog' );
	if ( ! $blog ) {
		$blog_id = wp_insert_post( array(
			'post_title'  => 'Blog',
			'post_name'   => 'blog',
			'post_status' => 'publish',
			'post_type'   => 'page',
		) );
	} else {
		$blog_id = $blog->ID;
	}

	if ( $home_id && ! is_wp_error( $home_id ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_id );
	}
	if ( $blog_id && ! is_wp_error( $blog_id ) ) {
		update_option( 'page_for_posts', $blog_id );
	}

	$created = ssbd_ensure_blueprint_pages();

	ssbd_scaffold_menus( $created, $blog_id );
	ssbd_seed_catalogue();
	ssbd_seed_offers();
	ssbd_require_comment_approval();

	update_option( 'ssbd_scaffolded', SSBD_VERSION );
}
add_action( 'after_switch_theme', 'ssbd_scaffold_site' );

/**
 * Create any blueprint page that does not exist yet, and return them all by slug.
 *
 * Split out of ssbd_scaffold_site() so the upgrade path can call it too: a site
 * scaffolded before a page was added to the blueprint would otherwise never get
 * that page, because scaffolding only ever runs once.
 *
 * An existing page with a matching slug is reused, never overwritten — and its
 * template is only filled in when it has none, so a page deliberately switched
 * to another template stays switched.
 *
 * @return array<string,int> Page IDs keyed by blueprint slug.
 */
function ssbd_ensure_blueprint_pages() {
	$pages = array();

	foreach ( ssbd_page_blueprint() as $slug => $spec ) {
		$existing = get_page_by_path( $slug );

		if ( $existing ) {
			$pages[ $slug ] = $existing->ID;

			if ( ! get_post_meta( $existing->ID, '_wp_page_template', true ) ) {
				update_post_meta( $existing->ID, '_wp_page_template', $spec['template'] );
			}

			continue;
		}

		$id = wp_insert_post( array(
			'post_title'  => $spec['title'],
			'post_name'   => $slug,
			'post_status' => 'publish',
			'post_type'   => 'page',
		) );

		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_wp_page_template', $spec['template'] );
			$pages[ $slug ] = $id;
		}
	}

	return $pages;
}

/**
 * Bring an already-scaffolded site up to the current theme version.
 *
 * ssbd_scaffold_site() runs once, on activation, and is guarded by the
 * ssbd_scaffolded option. That option stores the version it ran at, so this
 * compares it with the running one and fills in what a newer version added —
 * today, the pages behind any newly blueprinted template.
 *
 * Runs in the admin only: it writes posts, and a front-end request is not the
 * place to discover it has work to do.
 */
function ssbd_maybe_upgrade() {
	$done = get_option( 'ssbd_scaffolded' );

	// Never scaffolded: activation will handle it, and running here first
	// would create the pages without the menus that go with them.
	if ( ! $done || version_compare( (string) $done, SSBD_VERSION, '>=' ) ) {
		return;
	}

	ssbd_rename_brands_page();
	ssbd_ensure_blueprint_pages();
	ssbd_require_comment_approval();

	update_option( 'ssbd_scaffolded', SSBD_VERSION );
}
add_action( 'admin_init', 'ssbd_maybe_upgrade' );

/**
 * Require manual approval before a first-time commenter's comment is public.
 *
 * A comment thread with no moderation queue at all means whatever a visitor
 * submits is live the moment they click Post — including spam, and including
 * anything hostile aimed at another commenter — before anyone at the site has
 * read it. Setting this in code rather than leaving it to the Settings →
 * Discussion checkbox means it survives whatever else changes on the site;
 * a site owner who wants a faster-moving comment section can still switch it
 * off there afterwards.
 *
 * A previously-approved commenter still posts straight through — this only
 * adds the queue for a first comment from someone new, which is what
 * "comment_moderation" alone controls (it does not touch the separate
 * comment_whitelist/previously-approved behaviour).
 */
function ssbd_require_comment_approval() {
	update_option( 'comment_moderation', 1 );
}

/**
 * Rename the short-lived "Brands" page to "Partners".
 *
 * 1.2.0 blueprinted the partner wall as /brands/. 1.3.0 renamed it. Renaming
 * the existing page rather than creating a second one keeps whatever the page
 * already had — its menu entries, its ID, any links pointing at it — and stops
 * the site accumulating an orphan nobody asked for.
 *
 * Only touches a page still on the partner template and still called Brands,
 * so a page deliberately repurposed since is left alone.
 */
function ssbd_rename_brands_page() {
	if ( get_page_by_path( 'partners' ) ) {
		return; // Already renamed, or a Partners page exists on its own.
	}

	$page = get_page_by_path( 'brands' );

	if ( ! $page || 'Brands' !== $page->post_title ) {
		return;
	}

	$template = (string) get_post_meta( $page->ID, '_wp_page_template', true );

	if ( ! in_array( $template, array( 'page-templates/template-brands.php', 'page-templates/template-partners.php' ), true ) ) {
		return;
	}

	wp_update_post( array(
		'ID'         => $page->ID,
		'post_title' => 'Partners',
		'post_name'  => 'partners',
	) );

	update_post_meta( $page->ID, '_wp_page_template', 'page-templates/template-partners.php' );
}

/**
 * Create the services from inc/data/services.php that do not exist yet.
 *
 * Runs at activation, and again from the button on the Services screen. It
 * only ever adds: a service whose slug is already there is left exactly as
 * the site owner edited it, so this is safe to re-run and cannot overwrite
 * anyone's copy. Children are inserted after their parents so the nesting
 * survives.
 *
 * @return int Number of services created.
 */
function ssbd_seed_services() {
	ssbd_register_post_types();

	$existing = array();

	foreach ( get_posts( array(
		'post_type'   => 'ssbd_service',
		'post_status' => 'any',
		'numberposts' => 200,
	) ) as $post ) {
		$existing[ $post->post_name ] = $post->ID;
	}

	$created = 0;
	$seeds   = ssbd_data( 'services' );

	// Parents first: a child needs its parent's ID to nest under.
	usort(
		$seeds,
		static function ( $a, $b ) {
			return ( empty( $a['parent'] ) ? 0 : 1 ) <=> ( empty( $b['parent'] ) ? 0 : 1 );
		}
	);

	foreach ( $seeds as $i => $service ) {
		if ( isset( $existing[ $service['slug'] ] ) ) {
			continue;
		}

		$parent = $service['parent'] ?? '';

		$id = wp_insert_post( array(
			'post_type'   => 'ssbd_service',
			'post_status' => 'publish',
			'post_title'  => $service['title'],
			'post_name'   => $service['slug'],
			'post_parent' => $parent && isset( $existing[ $parent ] ) ? $existing[ $parent ] : 0,
			'menu_order'  => $i + 1,
		) );

		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}

		$existing[ $service['slug'] ] = $id;
		$created++;

		foreach ( array( 'short', 'desc', 'group', 'icon', 'cta' ) as $key ) {
			if ( ! empty( $service[ $key ] ) ) {
				update_post_meta( $id, '_ssbd_' . $key, $service[ $key ] );
			}
		}
		foreach ( array( 'pills', 'items' ) as $key ) {
			if ( ! empty( $service[ $key ] ) ) {
				update_post_meta( $id, '_ssbd_' . $key, implode( "\n", $service[ $key ] ) );
			}
		}
	}

	if ( $created ) {
		ssbd_catalogue_cache();
	}

	return $created;
}

/**
 * The theme's default services that are not in wp-admin yet.
 *
 * @return string[] Titles, keyed by slug.
 */
function ssbd_missing_services() {
	$existing = wp_list_pluck( get_posts( array(
		'post_type'   => 'ssbd_service',
		'post_status' => 'any',
		'numberposts' => 200,
	) ), 'post_name' );

	$missing = array();

	foreach ( ssbd_data( 'services' ) as $service ) {
		if ( ! in_array( $service['slug'], $existing, true ) ) {
			$missing[ $service['slug'] ] = $service['title'];
		}
	}

	return $missing;
}

/**
 * Update the services that already exist to the theme's default copy.
 *
 * Matched by slug. Overwrites the title, the fields and the nesting — this is
 * the "put it back how the theme ships it" button, and the notice says so
 * before it is clicked.
 *
 * The one thing it never touches is post_content: that is the page body the
 * site owner wrote, and no default exists for it to be restored to.
 *
 * @return int Number of services updated.
 */
function ssbd_sync_services() {
	$existing = array();

	foreach ( get_posts( array(
		'post_type'   => 'ssbd_service',
		'post_status' => 'any',
		'numberposts' => 200,
	) ) as $post ) {
		$existing[ $post->post_name ] = $post;
	}

	$updated = 0;
	$order   = 0;

	foreach ( ssbd_data( 'services' ) as $service ) {
		if ( ! isset( $existing[ $service['slug'] ] ) ) {
			continue;
		}

		$post   = $existing[ $service['slug'] ];
		$parent = $service['parent'] ?? '';
		$order++;

		wp_update_post( array(
			'ID'          => $post->ID,
			'post_title'  => $service['title'],
			'post_parent' => $parent && isset( $existing[ $parent ] ) ? $existing[ $parent ]->ID : 0,
			'menu_order'  => $order,
		) );

		foreach ( array( 'short', 'desc', 'group', 'icon', 'cta' ) as $key ) {
			update_post_meta( $post->ID, '_ssbd_' . $key, $service[ $key ] ?? '' );
		}
		foreach ( array( 'pills', 'items' ) as $key ) {
			update_post_meta( $post->ID, '_ssbd_' . $key, implode( "\n", (array) ( $service[ $key ] ?? array() ) ) );
		}

		$updated++;
	}

	if ( $updated ) {
		ssbd_catalogue_cache();
	}

	return $updated;
}

/**
 * Services in wp-admin that the theme's defaults do not include.
 *
 * @return WP_Post[]
 */
function ssbd_extra_services() {
	$slugs = wp_list_pluck( ssbd_data( 'services' ), 'slug' );

	return array_values( array_filter(
		get_posts( array(
			'post_type'   => 'ssbd_service',
			'post_status' => 'any',
			'numberposts' => 200,
		) ),
		static function ( $post ) use ( $slugs ) {
			return ! in_array( $post->post_name, $slugs, true );
		}
	) );
}

/**
 * The state of the service list against the theme defaults, with a button for
 * each thing that can be done about it.
 *
 * All three actions are explicit clicks. Adding is safe, updating overwrites
 * copy, and the third only ever moves posts to the trash — nothing here
 * deletes anything permanently.
 */
function ssbd_service_seed_notice() {
	$screen = get_current_screen();

	if ( ! $screen || 'edit-ssbd_service' !== $screen->id || ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	if ( get_option( 'ssbd_services_notice_off' ) ) {
		return;
	}

	$missing = ssbd_missing_services();
	$extra   = ssbd_extra_services();
	$present = count( ssbd_data( 'services' ) ) - count( $missing );

	if ( ! $missing && ! $extra && ! $present ) {
		return;
	}

	$action = static function ( $key ) {
		return wp_nonce_url( admin_url( 'edit.php?post_type=ssbd_service&' . $key . '=1' ), $key );
	};
	?>
	<div class="notice notice-info">
		<h3 style="margin-bottom:6px"><?php esc_html_e( 'Services and the theme defaults', 'ssbd' ); ?></h3>

		<?php if ( $missing ) : ?>
			<p style="max-width:74ch">
				<?php
				printf(
					/* translators: %s: comma-separated service names */
					esc_html__( 'Not here yet: %s. Each becomes its own page with a description, technology pills and a list of what it covers.', 'ssbd' ),
					esc_html( implode( ', ', $missing ) )
				);
				?>
				<a href="<?php echo esc_url( $action( 'ssbd_seed_services' ) ); ?>" class="button button-primary">
					<?php
					printf(
						/* translators: %d: number of services */
						esc_html( _n( 'Add %d missing service', 'Add %d missing services', count( $missing ), 'ssbd' ) ),
						count( $missing )
					);
					?>
				</a>
			</p>
		<?php endif; ?>

		<?php if ( $present ) : ?>
			<p style="max-width:74ch">
				<?php
				printf(
					/* translators: %d: number of services */
					esc_html( _n(
						'%d service here matches a theme default. You can reset its name, description, pills, list, icon, group and nesting to the shipped copy.',
						'%d services here match the theme defaults. You can reset their names, descriptions, pills, lists, icons, groups and nesting to the shipped copy.',
						$present,
						'ssbd'
					) ),
					$present
				);
				?>
				<strong><?php esc_html_e( 'This overwrites your edits to those fields.', 'ssbd' ); ?></strong>
				<?php esc_html_e( 'Page content written in the editor is never touched.', 'ssbd' ); ?>
				<a href="<?php echo esc_url( $action( 'ssbd_sync_services' ) ); ?>" class="button"
					onclick="return confirm(<?php echo esc_attr( wp_json_encode( __( 'Reset these services to the theme defaults? Your edits to their descriptions and fields will be replaced.', 'ssbd' ) ) ); ?>);">
					<?php esc_html_e( 'Reset to theme defaults', 'ssbd' ); ?>
				</a>
			</p>
		<?php endif; ?>

		<?php if ( $extra ) : ?>
			<p style="max-width:74ch">
				<?php
				printf(
					/* translators: %s: comma-separated service names */
					esc_html__( 'Not part of the defaults: %s. These are yours — keep them, or move them to the trash if they are left over from an earlier list.', 'ssbd' ),
					esc_html( implode( ', ', wp_list_pluck( $extra, 'post_title' ) ) )
				);
				?>
				<a href="<?php echo esc_url( $action( 'ssbd_trash_services' ) ); ?>" class="button"
					onclick="return confirm(<?php echo esc_attr( wp_json_encode( __( 'Move these services to the trash? You can restore them from there.', 'ssbd' ) ) ); ?>);">
					<?php
					printf(
						/* translators: %d: number of services */
						esc_html( _n( 'Move %d to Trash', 'Move %d to Trash', count( $extra ), 'ssbd' ) ),
						count( $extra )
					);
					?>
				</a>
			</p>
		<?php endif; ?>

		<p>
			<a href="<?php echo esc_url( $action( 'ssbd_hide_services_notice' ) ); ?>"><?php esc_html_e( 'Hide this notice', 'ssbd' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'ssbd_service_seed_notice' );

/**
 * Handle the three buttons and the dismiss link.
 */
function ssbd_handle_service_actions() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$args = array( 'post_type' => 'ssbd_service' );

	if ( isset( $_GET['ssbd_seed_services'] ) ) {
		check_admin_referer( 'ssbd_seed_services' );
		$args['ssbd_seeded_services'] = ssbd_seed_services();

	} elseif ( isset( $_GET['ssbd_sync_services'] ) ) {
		check_admin_referer( 'ssbd_sync_services' );
		$args['ssbd_synced_services'] = ssbd_sync_services();

	} elseif ( isset( $_GET['ssbd_trash_services'] ) ) {
		check_admin_referer( 'ssbd_trash_services' );

		$trashed = 0;
		foreach ( ssbd_extra_services() as $post ) {
			if ( wp_trash_post( $post->ID ) ) {
				$trashed++;
			}
		}

		ssbd_catalogue_cache();
		$args['ssbd_trashed_services'] = $trashed;

	} elseif ( isset( $_GET['ssbd_hide_services_notice'] ) ) {
		check_admin_referer( 'ssbd_hide_services_notice' );
		update_option( 'ssbd_services_notice_off', 1 );

	} else {
		return;
	}

	wp_safe_redirect( add_query_arg( $args, admin_url( 'edit.php' ) ) );
	exit;
}
add_action( 'admin_init', 'ssbd_handle_service_actions' );

/**
 * Confirm what each action did.
 */
function ssbd_service_action_notice() {
	$messages = array();

	if ( isset( $_GET['ssbd_seeded_services'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- read-only display flag.
		$messages[] = sprintf(
			/* translators: %d: number of services */
			_n( '%d service added.', '%d services added.', (int) $_GET['ssbd_seeded_services'], 'ssbd' ), // phpcs:ignore WordPress.Security.NonceVerification
			(int) $_GET['ssbd_seeded_services'] // phpcs:ignore WordPress.Security.NonceVerification
		);
	}

	if ( isset( $_GET['ssbd_synced_services'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$messages[] = sprintf(
			/* translators: %d: number of services */
			_n( '%d service reset to the theme default.', '%d services reset to the theme defaults.', (int) $_GET['ssbd_synced_services'], 'ssbd' ), // phpcs:ignore WordPress.Security.NonceVerification
			(int) $_GET['ssbd_synced_services'] // phpcs:ignore WordPress.Security.NonceVerification
		);
	}

	if ( isset( $_GET['ssbd_trashed_services'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$messages[] = sprintf(
			/* translators: %d: number of services */
			_n( '%d service moved to the trash.', '%d services moved to the trash.', (int) $_GET['ssbd_trashed_services'], 'ssbd' ), // phpcs:ignore WordPress.Security.NonceVerification
			(int) $_GET['ssbd_trashed_services'] // phpcs:ignore WordPress.Security.NonceVerification
		);
	}

	if ( ! $messages ) {
		return;
	}
	?>
	<div class="notice notice-success is-dismissible">
		<p>
			<?php echo esc_html( implode( ' ', $messages ) ); ?>
			<?php esc_html_e( 'Open a service to write its page body, then check the header dropdown.', 'ssbd' ); ?>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'ssbd_service_action_notice' );

/**
 * Build the primary, footer and legal menus from the scaffolded pages.
 *
 * @param array    $pages   slug => post ID.
 * @param int|null $blog_id Blog page ID.
 */
function ssbd_scaffold_menus( $pages, $blog_id = null ) {
	$menus = array(
		'primary' => array(
			'name'  => 'Primary',
			'items' => array( 'services', 'solutions', 'products', 'portfolio', 'resources', 'comparisons', 'about' ),
		),
		'footer'  => array(
			'name'  => 'Footer',
			'items' => array( 'services', 'solutions', 'products', 'resources', 'tools', 'contact' ),
		),
		'legal'   => array(
			'name'  => 'Legal',
			'items' => array( 'privacy-policy', 'terms', 'affiliate-disclosure' ),
		),
	);

	$locations = get_theme_mod( 'nav_menu_locations', array() );

	foreach ( $menus as $location => $spec ) {
		$menu = wp_get_nav_menu_object( $spec['name'] );
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( $spec['name'] );
			if ( is_wp_error( $menu_id ) ) {
				continue;
			}
		} else {
			$menu_id = $menu->term_id;
			if ( wp_get_nav_menu_items( $menu_id ) ) {
				$locations[ $location ] = $menu_id;
				continue; // Respect a menu the site owner already built.
			}
		}

		foreach ( $spec['items'] as $slug ) {
			if ( empty( $pages[ $slug ] ) ) {
				continue;
			}
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-object-id' => $pages[ $slug ],
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			) );
		}

		if ( 'footer' === $location && $blog_id ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-object-id' => $blog_id,
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			) );
		}

		$locations[ $location ] = $menu_id;
	}

	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Mark the document as JS-capable as early as possible so `.no-js` styles
 * only ever apply when scripting is genuinely off.
 */
function ssbd_no_js_class( $classes ) {
	$classes[] = 'no-js';
	return $classes;
}
add_filter( 'body_class', 'ssbd_no_js_class' );


/**
 * Seed the catalogue post types from the shipped PHP data.
 *
 * Runs once on activation so that Projects, Services and Products open in
 * wp-admin already populated with the theme's own content, ready to edit,
 * rather than as three empty screens.
 *
 * @return void
 */
function ssbd_seed_catalogue() {
	ssbd_register_post_types();

	foreach ( ssbd_default_terms() as $taxonomy => $terms ) {
		foreach ( $terms as $slug => $name ) {
			if ( ! term_exists( $slug, $taxonomy ) ) {
				wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
			}
		}
	}

	// Projects.
	if ( ! get_posts( array( 'post_type' => 'ssbd_project', 'numberposts' => 1, 'post_status' => 'any', 'fields' => 'ids' ) ) ) {
		foreach ( ssbd_data( 'projects' ) as $i => $project ) {
			$id = wp_insert_post( array(
				'post_type'   => 'ssbd_project',
				'post_status' => 'publish',
				'post_title'  => $project['name'],
				'menu_order'  => $i + 1,
			) );

			if ( ! $id || is_wp_error( $id ) ) {
				continue;
			}

			foreach ( array( 'desc', 'tech', 'badge', 'tag', 'url' ) as $key ) {
				if ( ! empty( $project[ $key ] ) ) {
					update_post_meta( $id, '_ssbd_' . $key, $project[ $key ] );
				}
			}

			wp_set_object_terms( $id, (array) ( $project['filters'] ?? array() ), 'ssbd_project_category' );
		}
	}

	// Services.
	ssbd_seed_services();

	// Products.
	if ( ! get_posts( array( 'post_type' => 'ssbd_product', 'numberposts' => 1, 'post_status' => 'any', 'fields' => 'ids' ) ) ) {
		foreach ( ssbd_data( 'products' )['items'] as $i => $product ) {
			$id = wp_insert_post( array(
				'post_type'   => 'ssbd_product',
				'post_status' => 'publish',
				'post_title'  => $product['name'],
				'menu_order'  => $i + 1,
			) );

			if ( ! $id || is_wp_error( $id ) ) {
				continue;
			}

			foreach ( array( 'desc', 'category', 'status' ) as $key ) {
				if ( ! empty( $product[ $key ] ) ) {
					update_post_meta( $id, '_ssbd_' . $key, $product[ $key ] );
				}
			}

			if ( ! empty( $product['filter'] ) ) {
				wp_set_object_terms( $id, array( $product['filter'] ), 'ssbd_product_category' );
			}
		}
	}
}