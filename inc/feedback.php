<?php
/**
 * Page feedback.
 *
 * "Was this helpful?" with an optional note, stored in wp-admin. Two reasons
 * it is worth the code: a comparison that people mark unhelpful is one to
 * rewrite, and the note field is where readers say which comparison they came
 * looking for and did not find — which is the content plan, written by the
 * audience.
 *
 * A plain form posting to admin-post, like the other two forms in the theme,
 * so it works with JavaScript off.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the feedback post type.
 */
function ssbd_register_feedback_type() {
	register_post_type( 'ssbd_feedback', array(
		'labels'              => ssbd_cpt_labels( __( 'Feedback', 'ssbd' ), __( 'Page Feedback', 'ssbd' ) ),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'query_var'           => false,
		'menu_icon'           => 'dashicons-feedback',
		'menu_position'       => 26,
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
		'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
		'supports'            => array( 'title', 'editor' ),
	) );
}
add_action( 'init', 'ssbd_register_feedback_type' );

/**
 * Store one piece of feedback.
 *
 * @param array $input Raw input.
 * @return string Status key.
 */
function ssbd_process_feedback( $input ) {
	if ( ! empty( $input['website'] ) ) {
		return 'ok'; // Honeypot.
	}

	$verdict = sanitize_key( wp_unslash( $input['verdict'] ?? '' ) );

	if ( ! in_array( $verdict, array( 'yes', 'no' ), true ) ) {
		return 'error';
	}

	$note   = sanitize_textarea_field( wp_unslash( $input['note'] ?? '' ) );
	$source = isset( $input['source'] ) ? esc_url_raw( wp_unslash( $input['source'] ) ) : home_url( '/' );

	$id = wp_insert_post( array(
		'post_type'    => 'ssbd_feedback',
		'post_status'  => 'publish',
		'post_title'   => sprintf(
			/* translators: 1: yes or no, 2: page path */
			__( '%1$s — %2$s', 'ssbd' ),
			'yes' === $verdict ? __( 'Helpful', 'ssbd' ) : __( 'Not helpful', 'ssbd' ),
			wp_parse_url( $source, PHP_URL_PATH ) ?: '/'
		),
		'post_content' => $note,
	) );

	if ( ! $id || is_wp_error( $id ) ) {
		return 'error';
	}

	update_post_meta( $id, '_ssbd_verdict', $verdict );
	update_post_meta( $id, '_ssbd_source', $source );

	/**
	 * Fires after feedback is stored.
	 *
	 * @param string $verdict 'yes' or 'no'.
	 * @param string $note    Optional note.
	 * @param string $source  Page it came from.
	 */
	do_action( 'ssbd_feedback_received', $verdict, $note, $source );

	// Only the notes are worth an email — a bare thumb is a number, not news.
	if ( $note && apply_filters( 'ssbd_notify_on_feedback', true ) ) {
		wp_mail(
			ssbd_inquiry_recipient(),
			__( 'New page feedback', 'ssbd' ),
			$note . "\n\n" . $source,
			array( 'Content-Type: text/plain; charset=UTF-8' )
		);
	}

	return 'ok';
}

/**
 * Handle the form post.
 */
function ssbd_handle_feedback_post() {
	check_admin_referer( 'ssbd_feedback', 'ssbd_feedback_nonce' );

	$status   = ssbd_process_feedback( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification -- verified above.
	$redirect = wp_get_referer() ?: home_url( '/' );

	$redirect = remove_query_arg( 'feedback', $redirect );
	$redirect = add_query_arg( 'feedback', $status, $redirect );

	wp_safe_redirect( $redirect . '#feedback' );
	exit;
}
add_action( 'admin_post_nopriv_ssbd_feedback', 'ssbd_handle_feedback_post' );
add_action( 'admin_post_ssbd_feedback', 'ssbd_handle_feedback_post' );

/**
 * Show the verdict and the page in the admin list.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function ssbd_feedback_columns( $columns ) {
	return array(
		'cb'           => $columns['cb'] ?? '',
		'title'        => __( 'Feedback', 'ssbd' ),
		'ssbd_note'    => __( 'What they said', 'ssbd' ),
		'ssbd_source'  => __( 'Page', 'ssbd' ),
		'date'         => __( 'Date', 'ssbd' ),
	);
}
add_filter( 'manage_ssbd_feedback_posts_columns', 'ssbd_feedback_columns' );

/**
 * Fill the custom columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function ssbd_feedback_column_content( $column, $post_id ) {
	if ( 'ssbd_note' === $column ) {
		echo esc_html( wp_trim_words( get_post_field( 'post_content', $post_id ), 20 ) ?: '—' );
		return;
	}

	if ( 'ssbd_source' === $column ) {
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
}
add_action( 'manage_ssbd_feedback_posts_custom_column', 'ssbd_feedback_column_content', 10, 2 );
