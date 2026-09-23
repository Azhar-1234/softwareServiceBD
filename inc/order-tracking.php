<?php
/**
 * Let a customer see the status of their own orders.
 *
 * Deliberately without WordPress user accounts. Open registration on a site
 * like this is a bot magnet, and every account created is a password to be
 * reset, stuffed or leaked — a lot of attack surface to buy a page that says
 * "Paid". So instead: the customer types the email they ordered with, and gets
 * a signed, expiring link to their own orders. Nothing to register, nothing to
 * remember, nothing to steal.
 *
 * The signature is an HMAC over the email and an expiry, keyed with the site's
 * auth salt, so a link cannot be forged or edited to read somebody else's
 * orders, and stops working on its own after a day.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * How long a tracking link stays valid.
 *
 * @return int Seconds.
 */
function ssbd_tracking_ttl() {
	/** Filter the lifetime of an order-tracking link. */
	return (int) apply_filters( 'ssbd_tracking_ttl', DAY_IN_SECONDS );
}

/**
 * Sign an email address into an opaque tracking token.
 *
 * @param string $email Customer email.
 * @return string URL-safe token.
 */
function ssbd_tracking_token( $email ) {
	$email   = strtolower( trim( $email ) );
	$expires = time() + ssbd_tracking_ttl();
	$payload = $email . '|' . $expires;
	$sig     = hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );

	return rtrim( strtr( base64_encode( $payload . '|' . $sig ), '+/', '-_' ), '=' );
}

/**
 * Recover the email from a token, if it is genuine and still current.
 *
 * @param string $token Token from the link.
 * @return string Email, or '' when the token is forged, altered or expired.
 */
function ssbd_tracking_email( $token ) {
	$token = (string) $token;
	$token = strtr( $token, '-_', '+/' );
	$token .= str_repeat( '=', ( 4 - strlen( $token ) % 4 ) % 4 );

	$raw = base64_decode( $token, true );

	if ( ! $raw ) {
		return '';
	}

	// An email address cannot contain a pipe, so this split is unambiguous.
	$parts = explode( '|', $raw );

	if ( 3 !== count( $parts ) ) {
		return '';
	}

	list( $email, $expires, $sig ) = $parts;

	if ( (int) $expires < time() ) {
		return '';
	}

	$expected = hash_hmac( 'sha256', $email . '|' . $expires, wp_salt( 'auth' ) );

	// hash_equals rather than ===: a timing-safe comparison is the whole point
	// of signing the thing in the first place.
	if ( ! hash_equals( $expected, $sig ) ) {
		return '';
	}

	return is_email( $email ) ? $email : '';
}

/**
 * The orders placed with one email address, newest first.
 *
 * @param string $email Customer email.
 * @return WP_Post[]
 */
function ssbd_orders_for_email( $email ) {
	if ( ! is_email( $email ) ) {
		return array();
	}

	return get_posts( array(
		'post_type'        => 'ssbd_order',
		'post_status'      => 'publish',
		'numberposts'      => 50,
		'orderby'          => 'date',
		'order'            => 'DESC',
		'suppress_filters' => false,
		'meta_query'       => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => '_ssbd_email',
				'value'   => $email,
				'compare' => '=',
			),
		),
	) );
}

/**
 * One order, flattened for display.
 *
 * @param WP_Post $order Order post.
 * @return array
 */
function ssbd_order_summary( $order ) {
	$statuses = ssbd_order_statuses();
	$status   = (string) get_post_meta( $order->ID, '_ssbd_order_status', true );

	return array(
		'id'      => $order->ID,
		'product' => (string) get_post_meta( $order->ID, '_ssbd_product_name', true ),
		'price'   => (string) get_post_meta( $order->ID, '_ssbd_price', true ),
		'status'  => $status,
		'label'   => $statuses[ $status ] ?? __( 'New', 'ssbd' ),
		'placed'  => get_the_date( '', $order ),
		'notes'   => (string) $order->post_content,
	);
}

/**
 * Throttle the request-a-link form.
 *
 * Without this the form is a way to post somebody else's address repeatedly
 * and fill their inbox, and a way to probe which addresses have ordered.
 *
 * @return bool True when this caller has had enough for now.
 */
function ssbd_tracking_rate_limited() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] )
		? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
		: '';

	if ( ! $ip ) {
		return false;
	}

	$key   = 'ssbd_track_' . md5( $ip );
	$count = (int) get_transient( $key );

	if ( $count >= 5 ) {
		return true;
	}

	set_transient( $key, $count + 1, 15 * MINUTE_IN_SECONDS );

	return false;
}

/**
 * Email a tracking link to an address that has orders.
 *
 * @param string $email Customer email.
 * @return void
 */
function ssbd_send_tracking_link( $email ) {
	$orders = ssbd_orders_for_email( $email );

	// No orders: send nothing. The form says the same thing either way, so
	// this never reveals whether an address is a customer.
	if ( ! $orders ) {
		return;
	}

	$link = add_query_arg( 'token', ssbd_tracking_token( $email ), ssbd_page_url( 'track-order' ) );

	$lines = array(
		sprintf(
			/* translators: %s: site name */
			__( 'Here is your link to view your orders with %s:', 'ssbd' ),
			ssbd_site( 'legal_name' )
		),
		'',
		$link,
		'',
		sprintf(
			/* translators: %d: number of hours */
			_n(
				'The link works for %d hour. Request another any time.',
				'The link works for %d hours. Request another any time.',
				(int) ( ssbd_tracking_ttl() / HOUR_IN_SECONDS ),
				'ssbd'
			),
			(int) ( ssbd_tracking_ttl() / HOUR_IN_SECONDS )
		),
		'',
		__( 'If you did not ask for this, you can ignore it — nobody can see your orders without the link.', 'ssbd' ),
	);

	wp_mail(
		$email,
		sprintf(
			/* translators: %s: site name */
			__( 'Your orders with %s', 'ssbd' ),
			ssbd_site( 'legal_name' )
		),
		implode( "\n", $lines )
	);
}

/**
 * Handle the "email me my orders" form.
 */
function ssbd_handle_tracking_post() {
	check_admin_referer( 'ssbd_track', 'ssbd_track_nonce' );

	$redirect = wp_get_referer() ?: ssbd_page_url( 'track-order' );
	$redirect = remove_query_arg( array( 'track', 'token' ), $redirect );

	// phpcs:ignore WordPress.Security.NonceVerification -- verified above.
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	if ( ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'track', 'invalid', $redirect ) . '#track' );
		exit;
	}

	if ( ssbd_tracking_rate_limited() ) {
		wp_safe_redirect( add_query_arg( 'track', 'throttled', $redirect ) . '#track' );
		exit;
	}

	ssbd_send_tracking_link( $email );

	// Always the same answer, whether or not that address has orders.
	wp_safe_redirect( add_query_arg( 'track', 'sent', $redirect ) . '#track' );
	exit;
}
add_action( 'admin_post_nopriv_ssbd_track', 'ssbd_handle_tracking_post' );
add_action( 'admin_post_ssbd_track', 'ssbd_handle_tracking_post' );

/**
 * Keep the tracking page, and anything hanging off a token, out of search.
 *
 * @param array $directives Robots directives.
 * @return array
 */
function ssbd_tracking_robots( $directives ) {
	if ( is_page_template( 'page-templates/template-track-order.php' ) ) {
		$directives = array( 'noindex', 'nofollow' );
	}

	return $directives;
}
add_filter( 'ssbd_robots_directives', 'ssbd_tracking_robots' );
