<?php
/**
 * Project inquiry handling.
 *
 * Two paths, deliberately:
 *   - REST (ssbd/v1/inquiry) for the React island, so submitting never
 *     reloads the page.
 *   - admin-post for the plain <form> the island progressively enhances,
 *     so the form still works with JavaScript disabled.
 *
 * Both funnel through ssbd_process_inquiry().
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Where inquiries are sent.
 *
 * @return string
 */
function ssbd_inquiry_recipient() {
	$to = get_theme_mod( 'ssbd_inquiry_recipient' );

	if ( ! $to || ! is_email( $to ) ) {
		$to = ssbd_site( 'email' );
	}
	if ( ! $to || ! is_email( $to ) ) {
		$to = get_option( 'admin_email' );
	}

	return apply_filters( 'ssbd_inquiry_recipient', $to );
}

/**
 * The inquiry form's field definitions — one source of truth for the
 * rendered form, the validator and the email body.
 *
 * @return array
 */
function ssbd_inquiry_fields() {
	return array(
		'name'    => array( 'label' => __( 'Your name', 'ssbd' ), 'type' => 'text', 'required' => true ),
		'email'   => array( 'label' => __( 'Email address', 'ssbd' ), 'type' => 'email', 'required' => true ),
		'company' => array( 'label' => __( 'Company', 'ssbd' ), 'type' => 'text', 'required' => false ),
		'phone'   => array( 'label' => __( 'Phone / WhatsApp', 'ssbd' ), 'type' => 'tel', 'required' => false ),
		'service' => array(
			'label'    => __( 'What do you need?', 'ssbd' ),
			'type'     => 'select',
			'required' => true,
			'options'  => array_merge(
				array( '' => __( 'Select a service', 'ssbd' ) ),
				wp_list_pluck( ssbd_all_services(), 'title', 'slug' ),
				array( 'other' => __( 'Something else', 'ssbd' ) )
			),
		),
		'budget'  => array(
			'label'    => __( 'Approximate budget', 'ssbd' ),
			'type'     => 'select',
			'required' => false,
			'options'  => array(
				''            => __( 'Prefer not to say', 'ssbd' ),
				'under-1k'    => __( 'Under $1,000', 'ssbd' ),
				'1k-5k'       => __( '$1,000 – $5,000', 'ssbd' ),
				'5k-15k'      => __( '$5,000 – $15,000', 'ssbd' ),
				'over-15k'    => __( '$15,000+', 'ssbd' ),
			),
		),
		'message' => array( 'label' => __( 'Project details', 'ssbd' ), 'type' => 'textarea', 'required' => true ),
	);
}

/**
 * Validate and send an inquiry.
 *
 * @param array $input Raw input.
 * @return array{ok:bool, errors?:array<string,string>, message:string}
 */
function ssbd_process_inquiry( $input ) {
	// Honeypot: a field no human sees, so anything in it is a bot.
	if ( ! empty( $input['website'] ) ) {
		return array( 'ok' => true, 'message' => __( 'Thanks — we will be in touch.', 'ssbd' ) );
	}

	$fields = ssbd_inquiry_fields();
	$clean  = array();
	$errors = array();

	foreach ( $fields as $key => $field ) {
		$raw = isset( $input[ $key ] ) ? wp_unslash( $input[ $key ] ) : '';

		if ( 'email' === $field['type'] ) {
			$value = sanitize_email( $raw );
			if ( $value && ! is_email( $value ) ) {
				$errors[ $key ] = __( 'Enter a valid email address.', 'ssbd' );
			}
		} elseif ( 'textarea' === $field['type'] ) {
			$value = sanitize_textarea_field( $raw );
		} else {
			$value = sanitize_text_field( $raw );
		}

		if ( ! empty( $field['required'] ) && '' === trim( (string) $value ) ) {
			$errors[ $key ] = __( 'This field is required.', 'ssbd' );
		}

		if ( 'select' === $field['type'] && '' !== $value && ! array_key_exists( $value, $field['options'] ) ) {
			$errors[ $key ] = __( 'Choose one of the listed options.', 'ssbd' );
		}

		$clean[ $key ] = $value;
	}

	if ( $errors ) {
		return array(
			'ok'      => false,
			'errors'  => $errors,
			'message' => __( 'Please check the highlighted fields.', 'ssbd' ),
		);
	}

	$lines = array();
	foreach ( $fields as $key => $field ) {
		$value = $clean[ $key ];
		if ( '' === $value ) {
			continue;
		}
		if ( 'select' === $field['type'] ) {
			$value = $field['options'][ $value ] ?? $value;
		}
		$lines[] = $field['label'] . ': ' . $value;
	}

	$lines[] = '';
	$lines[] = '---';
	$lines[] = 'Submitted: ' . current_time( 'mysql' );
	$lines[] = 'Page: ' . ( isset( $input['source'] ) ? esc_url_raw( wp_unslash( $input['source'] ) ) : home_url( '/' ) );

	$sent = wp_mail(
		ssbd_inquiry_recipient(),
		sprintf(
			/* translators: %s: sender name */
			__( 'New project inquiry — %s', 'ssbd' ),
			$clean['name']
		),
		implode( "\n", $lines ),
		array(
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . $clean['name'] . ' <' . $clean['email'] . '>',
		)
	);

	/**
	 * Fires after an inquiry is processed — hook a CRM or logger here.
	 *
	 * @param array $clean Sanitized fields.
	 * @param bool  $sent  Whether wp_mail() reported success.
	 */
	do_action( 'ssbd_inquiry_submitted', $clean, $sent );

	if ( ! $sent ) {
		return array(
			'ok'      => false,
			'message' => sprintf(
				/* translators: %s: email address */
				__( 'We could not send that. Please email us at %s.', 'ssbd' ),
				ssbd_site( 'email' )
			),
		);
	}

	return array(
		'ok'      => true,
		'message' => __( 'Inquiry sent. We will reply within one business day.', 'ssbd' ),
	);
}

/**
 * Register the REST endpoint used by the React island.
 */
function ssbd_register_rest_routes() {
	register_rest_route( 'ssbd/v1', '/inquiry', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'permission_callback' => '__return_true',
		'callback'            => 'ssbd_rest_inquiry',
	) );
}
add_action( 'rest_api_init', 'ssbd_register_rest_routes' );

/**
 * REST handler.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function ssbd_rest_inquiry( WP_REST_Request $request ) {
	$result = ssbd_process_inquiry( (array) $request->get_params() );

	return new WP_REST_Response( $result, $result['ok'] ? 200 : 422 );
}

/**
 * No-JS form handler.
 */
function ssbd_handle_inquiry_post() {
	check_admin_referer( 'ssbd_inquiry', 'ssbd_inquiry_nonce' );

	$result   = ssbd_process_inquiry( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification -- verified above.
	$redirect = wp_get_referer() ?: ssbd_page_url( 'contact' );

	$redirect = remove_query_arg( 'inquiry', $redirect );
	$redirect = add_query_arg( 'inquiry', $result['ok'] ? 'sent' : 'error', $redirect );

	wp_safe_redirect( $redirect . '#inquiry' );
	exit;
}
add_action( 'admin_post_nopriv_ssbd_inquiry', 'ssbd_handle_inquiry_post' );
add_action( 'admin_post_ssbd_inquiry', 'ssbd_handle_inquiry_post' );
