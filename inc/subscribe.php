<?php
/**
 * Newsletter subscriptions.
 *
 * Subscribers are stored in wp-admin as a post type rather than posted off to
 * a third-party service: the list is the site owner's, it works the day the
 * theme is activated with nothing to sign up for, and it exports to CSV for
 * whichever mail platform is chosen later.
 *
 * The form is a plain <form> posting to admin-post, so it works with
 * JavaScript off, like the inquiry form it sits beside.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the subscriber post type.
 *
 * Not publicly queryable and not creatable by hand — every row arrives from
 * the form, and an email address is not a page.
 */
function ssbd_register_subscriber_type() {
	register_post_type( 'ssbd_subscriber', array(
		'labels'              => ssbd_cpt_labels( __( 'Subscriber', 'ssbd' ), __( 'Subscribers', 'ssbd' ) ),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'query_var'           => false,
		'menu_icon'           => 'dashicons-email-alt',
		'menu_position'       => 25,
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
		'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
		'supports'            => array( 'title' ),
	) );
}
add_action( 'init', 'ssbd_register_subscriber_type' );

/**
 * Store a subscription.
 *
 * @param array $input Raw input.
 * @return array{status:string, message:string}
 */
function ssbd_process_subscribe( $input ) {
	// Honeypot: a field no human sees, so anything in it is a bot. Bots are
	// told it worked, because telling them otherwise only invites a retry.
	if ( ! empty( $input['website'] ) ) {
		return array( 'status' => 'ok', 'message' => __( 'Thanks — you are on the list.', 'ssbd' ) );
	}

	$email = sanitize_email( wp_unslash( $input['email'] ?? '' ) );

	if ( ! $email || ! is_email( $email ) ) {
		return array( 'status' => 'invalid', 'message' => __( 'Enter a valid email address.', 'ssbd' ) );
	}

	$existing = get_posts( array(
		'post_type'        => 'ssbd_subscriber',
		'post_status'      => 'any',
		'numberposts'      => 1,
		'title'            => $email,
		'fields'           => 'ids',
		'suppress_filters' => false,
	) );

	if ( $existing ) {
		// Not an error: the visitor's intent is satisfied either way, and
		// saying "you are already subscribed" leaks who is on the list.
		return array( 'status' => 'ok', 'message' => __( 'Thanks — you are on the list.', 'ssbd' ) );
	}

	$id = wp_insert_post( array(
		'post_type'   => 'ssbd_subscriber',
		'post_status' => 'publish',
		'post_title'  => $email,
	) );

	if ( ! $id || is_wp_error( $id ) ) {
		return array( 'status' => 'error', 'message' => __( 'We could not save that. Please try again.', 'ssbd' ) );
	}

	$source = isset( $input['source'] ) ? esc_url_raw( wp_unslash( $input['source'] ) ) : home_url( '/' );
	update_post_meta( $id, '_ssbd_source', $source );

	/**
	 * Fires after a subscription is stored — hook a mail platform here.
	 *
	 * @param string $email  Subscriber email.
	 * @param string $source Page the form was submitted from.
	 */
	do_action( 'ssbd_subscribed', $email, $source );

	if ( apply_filters( 'ssbd_notify_on_subscribe', true ) ) {
		wp_mail(
			ssbd_inquiry_recipient(),
			__( 'New newsletter subscriber', 'ssbd' ),
			$email . "\n" . $source,
			array( 'Content-Type: text/plain; charset=UTF-8' )
		);
	}

	return array( 'status' => 'ok', 'message' => __( 'Thanks — you are on the list.', 'ssbd' ) );
}

/**
 * Handle the form post.
 */
function ssbd_handle_subscribe_post() {
	check_admin_referer( 'ssbd_subscribe', 'ssbd_subscribe_nonce' );

	$result   = ssbd_process_subscribe( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification -- verified above.
	$redirect = wp_get_referer() ?: home_url( '/' );

	$redirect = remove_query_arg( 'subscribe', $redirect );
	$redirect = add_query_arg( 'subscribe', $result['status'], $redirect );

	wp_safe_redirect( $redirect . '#subscribe' );
	exit;
}
add_action( 'admin_post_nopriv_ssbd_subscribe', 'ssbd_handle_subscribe_post' );
add_action( 'admin_post_ssbd_subscribe', 'ssbd_handle_subscribe_post' );

/**
 * Show where each subscriber signed up, and when.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function ssbd_subscriber_columns( $columns ) {
	return array(
		'cb'           => $columns['cb'] ?? '',
		'title'        => __( 'Email', 'ssbd' ),
		'ssbd_source'  => __( 'Signed up from', 'ssbd' ),
		'date'         => __( 'Date', 'ssbd' ),
	);
}
add_filter( 'manage_ssbd_subscriber_posts_columns', 'ssbd_subscriber_columns' );

/**
 * Fill the source column.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function ssbd_subscriber_column_content( $column, $post_id ) {
	if ( 'ssbd_source' !== $column ) {
		return;
	}

	$source = (string) get_post_meta( $post_id, '_ssbd_source', true );

	if ( ! $source ) {
		echo '—';
		return;
	}

	printf(
		'<a href="%s" rel="noopener">%s</a>',
		esc_url( $source ),
		esc_html( wp_parse_url( $source, PHP_URL_PATH ) ?: $source )
	);
}
add_action( 'manage_ssbd_subscriber_posts_custom_column', 'ssbd_subscriber_column_content', 10, 2 );

/**
 * An export button above the list, because a mailing list nobody can get out
 * of wp-admin is not a mailing list.
 */
function ssbd_subscriber_export_button() {
	$screen = get_current_screen();

	if ( ! $screen || 'edit-ssbd_subscriber' !== $screen->id || ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$url = wp_nonce_url( admin_url( 'edit.php?post_type=ssbd_subscriber&ssbd_export_subscribers=1' ), 'ssbd_export_subscribers' );
	?>
	<div class="notice notice-info">
		<p>
			<?php esc_html_e( 'These addresses were collected by the footer sign-up form.', 'ssbd' ); ?>
			<a href="<?php echo esc_url( $url ); ?>" class="button"><?php esc_html_e( 'Export CSV', 'ssbd' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'ssbd_subscriber_export_button' );

/**
 * Stream the subscriber list as CSV.
 */
function ssbd_export_subscribers() {
	if ( ! isset( $_GET['ssbd_export_subscribers'] ) || ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	check_admin_referer( 'ssbd_export_subscribers' );

	$rows = get_posts( array(
		'post_type'   => 'ssbd_subscriber',
		'post_status' => 'any',
		'numberposts' => -1,
		'orderby'     => 'date',
		'order'       => 'ASC',
	) );

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=subscribers-' . gmdate( 'Y-m-d' ) . '.csv' );

	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'email', 'signed_up', 'source' ) );

	foreach ( $rows as $row ) {
		fputcsv( $out, array(
			$row->post_title,
			$row->post_date,
			(string) get_post_meta( $row->ID, '_ssbd_source', true ),
		) );
	}

	fclose( $out );
	exit;
}
add_action( 'admin_init', 'ssbd_export_subscribers' );
