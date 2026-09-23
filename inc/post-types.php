<?php
/**
 * Custom post types for the catalogue content.
 *
 * Projects, services and products are the three things this site accumulates
 * more of over time, so they belong in wp-admin rather than in a PHP array.
 *
 * The accessors in inc/data.php prefer these post types and fall back to
 * inc/data/*.php when none exist, so an install with no posts yet renders
 * exactly as it always did — and the shipped copy doubles as seed data.
 *
 * The types are registered with `public => false`. They are content blocks
 * assembled into the Portfolio, Services and Products pages, not pages in
 * their own right; giving each one a URL would publish a lot of thin,
 * near-duplicate pages, which is the opposite of what this site needs.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * The URL segment individual service pages live under.
 *
 * Matches the Services hub page's slug, so /services/ is the hub and
 * /services/web-development/ is the service. WordPress resolves the hub page
 * first and falls through to the post type for anything below it.
 *
 * @return string
 */
function ssbd_service_base() {
	/** Filter the base slug for service pages. */
	return sanitize_title( (string) apply_filters( 'ssbd_service_base', 'services' ) );
}

/**
 * Shared registration arguments.
 *
 * @param string $icon Dashicon.
 * @param int    $pos  Menu position.
 * @return array
 */
function ssbd_cpt_base_args( $icon, $pos ) {
	return array(
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'query_var'           => false,
		'menu_icon'           => $icon,
		'menu_position'       => $pos,
		'capability_type'     => 'post',
		'hierarchical'        => false,
		'supports'            => array( 'title', 'page-attributes' ),
	);
}

/**
 * Register the catalogue post types and their taxonomies.
 */
function ssbd_register_post_types() {
	register_post_type( 'ssbd_project', array_merge(
		ssbd_cpt_base_args( 'dashicons-portfolio', 21 ),
		array(
			'labels'   => ssbd_cpt_labels( __( 'Project', 'ssbd' ), __( 'Projects', 'ssbd' ) ),
			'supports' => array( 'title', 'thumbnail', 'page-attributes' ),
		)
	) );

	/*
	 * Services are the exception to the "content block, not a page" rule
	 * above: each one is a page people search for and land on, and they nest
	 * — Web Development has Laravel and WordPress under it — which is what
	 * the header dropdown and the /services/web-development/laravel/ style
	 * URLs are built from. The Services template stays the hub that lists
	 * them; these are the pages it links to.
	 */
	register_post_type( 'ssbd_service', array_merge(
		ssbd_cpt_base_args( 'dashicons-screenoptions', 22 ),
		array(
			'labels'              => ssbd_cpt_labels( __( 'Service', 'ssbd' ), __( 'Services', 'ssbd' ) ),
			'public'              => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'show_in_nav_menus'   => true,
			'hierarchical'        => true,
			'has_archive'         => false,
			'query_var'           => true,
			'rewrite'             => array( 'slug' => ssbd_service_base(), 'with_front' => false ),
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
		)
	) );

	register_post_type( 'ssbd_product', array_merge(
		ssbd_cpt_base_args( 'dashicons-products', 23 ),
		array( 'labels' => ssbd_cpt_labels( __( 'Product', 'ssbd' ), __( 'Products', 'ssbd' ) ) )
	) );

	// The filter tabs on the Portfolio and Products pages are these terms.
	register_taxonomy( 'ssbd_project_category', 'ssbd_project', array(
		'labels'            => ssbd_tax_labels( __( 'Project category', 'ssbd' ), __( 'Project categories', 'ssbd' ) ),
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'hierarchical'      => true,
		'rewrite'           => false,
		'query_var'         => false,
	) );

	register_taxonomy( 'ssbd_product_category', 'ssbd_product', array(
		'labels'            => ssbd_tax_labels( __( 'Product category', 'ssbd' ), __( 'Product categories', 'ssbd' ) ),
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'hierarchical'      => true,
		'rewrite'           => false,
		'query_var'         => false,
	) );
}
add_action( 'init', 'ssbd_register_post_types' );

/**
 * Bump this whenever the rules above change; it triggers exactly one flush.
 */
const SSBD_REWRITE_VERSION = 6;

/**
 * Flush the rewrite rules once after the service pages gain their URLs.
 *
 * Flushing on every request is expensive, and asking a site owner to visit
 * Settings > Permalinks to make new URLs work is a support ticket waiting to
 * happen, so it runs once per rule change and remembers that it did.
 */
function ssbd_maybe_flush_service_rewrites() {
	if ( (int) get_option( 'ssbd_rewrite_version' ) === SSBD_REWRITE_VERSION ) {
		return;
	}

	flush_rewrite_rules( false );
	update_option( 'ssbd_rewrite_version', SSBD_REWRITE_VERSION );
}
add_action( 'init', 'ssbd_maybe_flush_service_rewrites', 20 );
add_action( 'after_switch_theme', 'ssbd_maybe_flush_service_rewrites', 22 );

/**
 * Build a labels array.
 *
 * @param string $single Singular name.
 * @param string $plural Plural name.
 * @return array
 */
function ssbd_cpt_labels( $single, $plural ) {
	return array(
		'name'               => $plural,
		'singular_name'      => $single,
		'menu_name'          => $plural,
		/* translators: %s: singular post type name */
		'add_new_item'       => sprintf( __( 'Add New %s', 'ssbd' ), $single ),
		/* translators: %s: singular post type name */
		'edit_item'          => sprintf( __( 'Edit %s', 'ssbd' ), $single ),
		/* translators: %s: singular post type name */
		'new_item'           => sprintf( __( 'New %s', 'ssbd' ), $single ),
		/* translators: %s: singular post type name */
		'view_item'          => sprintf( __( 'View %s', 'ssbd' ), $single ),
		/* translators: %s: plural post type name */
		'search_items'       => sprintf( __( 'Search %s', 'ssbd' ), $plural ),
		/* translators: %s: plural post type name */
		'not_found'          => sprintf( __( 'No %s yet.', 'ssbd' ), strtolower( $plural ) ),
		/* translators: %s: plural post type name */
		'not_found_in_trash' => sprintf( __( 'No %s in the trash.', 'ssbd' ), strtolower( $plural ) ),
		'all_items'          => $plural,
	);
}

/**
 * Build taxonomy labels.
 *
 * @param string $single Singular name.
 * @param string $plural Plural name.
 * @return array
 */
function ssbd_tax_labels( $single, $plural ) {
	return array(
		'name'          => $plural,
		'singular_name' => $single,
		'menu_name'     => $plural,
		/* translators: %s: singular taxonomy name */
		'add_new_item'  => sprintf( __( 'Add %s', 'ssbd' ), strtolower( $single ) ),
		/* translators: %s: plural taxonomy name */
		'search_items'  => sprintf( __( 'Search %s', 'ssbd' ), strtolower( $plural ) ),
		'all_items'     => $plural,
	);
}

/**
 * Order the admin lists by the Order field rather than by date.
 *
 * These are ordered catalogues, not a stream, so "most recently added first"
 * is the wrong default in the list table as well as on the front end.
 *
 * @param WP_Query $query Current query.
 */
function ssbd_admin_order_catalogue( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( in_array( $query->get( 'post_type' ), array( 'ssbd_project', 'ssbd_service', 'ssbd_product' ), true )
		&& ! $query->get( 'orderby' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
	}
}
add_action( 'pre_get_posts', 'ssbd_admin_order_catalogue' );

/**
 * Give a new catalogue item the next Order number instead of 0.
 *
 * These lists are sorted by the Order field, and wp-admin leaves that field
 * at 0 on a new post — so a service added from Add New Service sorted above
 * everything already published, took the 01 badge on the Services page and
 * renumbered every row beneath it. The end of the list is where a newly added
 * service belongs; the Order field is still there to say otherwise.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post being saved.
 */
function ssbd_default_catalogue_order( $post_id, $post ) {
	if ( ! in_array( $post->post_type, array( 'ssbd_project', 'ssbd_service', 'ssbd_product' ), true ) ) {
		return;
	}

	// A revision carries its parent's order, and an auto-draft is not yet a
	// post the site owner has decided to keep.
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id )
		|| in_array( $post->post_status, array( 'auto-draft', 'inherit', 'trash' ), true ) ) {
		return;
	}

	if ( 0 !== (int) $post->menu_order ) {
		return;
	}

	global $wpdb;

	$max = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT MAX(menu_order) FROM {$wpdb->posts} WHERE post_type = %s AND post_status != 'trash'",
		$post->post_type
	) );

	// Written to the row directly: wp_update_post() here would re-enter
	// save_post and run every hook on this screen a second time.
	$wpdb->update( $wpdb->posts, array( 'menu_order' => $max + 1 ), array( 'ID' => $post_id ) );
	clean_post_cache( $post_id );
}
add_action( 'save_post', 'ssbd_default_catalogue_order', 10, 2 );

/**
 * The default filter terms, used to seed the taxonomies on first activation.
 *
 * @return array<string, array<string, string>>
 */
function ssbd_default_terms() {
	return array(
		'ssbd_project_category' => array(
			'websites'  => __( 'Websites', 'ssbd' ),
			'wordpress' => __( 'WordPress', 'ssbd' ),
			'laravel'   => __( 'Laravel', 'ssbd' ),
			'software'  => __( 'Software', 'ssbd' ),
			'ecommerce' => __( 'eCommerce', 'ssbd' ),
			'ai'        => __( 'AI & Automation', 'ssbd' ),
		),
		'ssbd_product_category' => array(
			'wordpress' => __( 'WordPress Plugins', 'ssbd' ),
			'laravel'   => __( 'Laravel Applications', 'ssbd' ),
			'saas'      => __( 'SaaS', 'ssbd' ),
			'business'  => __( 'Business Software', 'ssbd' ),
			'ai'        => __( 'AI Tools', 'ssbd' ),
		),
	);
}
