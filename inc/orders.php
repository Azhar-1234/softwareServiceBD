<?php
/**
 * Product orders.
 *
 * An order here is a request to buy, not a payment: the customer picks a
 * product and leaves their details, the order lands in wp-admin under Orders
 * and in your inbox, and you invoice them out of band. There is deliberately
 * no gateway — the products are sold by conversation, and a checkout nobody
 * can complete is worse than a form somebody can.
 *
 * A plain form posting to admin-post, like the other forms in the theme, so it
 * works with JavaScript off.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the order post type.
 *
 * Orders are created by the form, never by hand — hence create_posts is denied
 * the same way page feedback denies it.
 */
function ssbd_register_order_type() {
	register_post_type( 'ssbd_order', array(
		'labels'              => ssbd_cpt_labels( __( 'Order', 'ssbd' ), __( 'Orders', 'ssbd' ) ),
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
		'map_meta_cap'        => true,
		'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
		'supports'            => array( 'title', 'editor' ),
	) );
}
add_action( 'init', 'ssbd_register_order_type' );

/**
 * The order form's fields — one source of truth for the rendered form, the
 * validator, the stored meta and the email body.
 *
 * The product itself is not in here: it comes from the URL and is shown as a
 * locked summary, so the customer cannot land on the form for one product and
 * silently order another.
 *
 * @return array
 */
function ssbd_order_fields() {
	return array(
		'name'     => array( 'label' => __( 'Your name', 'ssbd' ), 'type' => 'text', 'required' => true ),
		'email'    => array( 'label' => __( 'Email address', 'ssbd' ), 'type' => 'email', 'required' => true ),
		'phone'    => array( 'label' => __( 'Phone / WhatsApp', 'ssbd' ), 'type' => 'tel', 'required' => true ),
		'company'  => array( 'label' => __( 'Company', 'ssbd' ), 'type' => 'text', 'required' => false ),
		'quantity' => array(
			'label'    => __( 'Licences / seats', 'ssbd' ),
			'type'     => 'select',
			'required' => false,
			'options'  => array(
				'1'    => '1',
				'2-5'  => '2 – 5',
				'6-20' => '6 – 20',
				'20+'  => '20+',
			),
		),
		'notes'    => array(
			'label'    => __( 'Anything we should know?', 'ssbd' ),
			'type'     => 'textarea',
			'required' => false,
		),
	);
}

/**
 * The order statuses, keyed as stored.
 *
 * @return array<string,string>
 */
function ssbd_order_statuses() {
	return ssbd_meta_fields()['ssbd_order']['order_status']['options'] ?? array();
}

/**
 * Validate and record one order.
 *
 * @param array $input Raw input.
 * @return array{ok:bool, errors?:array<string,string>, message:string}
 */
function ssbd_process_order( $input ) {
	// Honeypot: a field no human sees, so anything in it is a bot. Answered
	// with the success message rather than an error, so the bot learns nothing.
	if ( ! empty( $input['website'] ) ) {
		return array( 'ok' => true, 'message' => __( 'Thanks — we will be in touch.', 'ssbd' ) );
	}

	$key     = sanitize_title( wp_unslash( $input['product'] ?? '' ) );
	$product = $key ? ssbd_product_by_key( $key ) : null;

	if ( ! $product ) {
		return array(
			'ok'      => false,
			'message' => __( 'We could not tell which product that was. Please pick one from the Products page and try again.', 'ssbd' ),
		);
	}

	$fields = ssbd_order_fields();
	$clean  = array();
	$errors = array();

	foreach ( $fields as $field_key => $field ) {
		$raw = isset( $input[ $field_key ] ) ? wp_unslash( $input[ $field_key ] ) : '';

		if ( 'email' === $field['type'] ) {
			$value = sanitize_email( $raw );
			if ( $value && ! is_email( $value ) ) {
				$errors[ $field_key ] = __( 'Enter a valid email address.', 'ssbd' );
			}
		} elseif ( 'textarea' === $field['type'] ) {
			$value = sanitize_textarea_field( $raw );
		} else {
			$value = sanitize_text_field( $raw );
		}

		if ( ! empty( $field['required'] ) && '' === trim( (string) $value ) ) {
			$errors[ $field_key ] = __( 'This field is required.', 'ssbd' );
		}

		if ( 'select' === $field['type'] && '' !== $value && ! array_key_exists( $value, $field['options'] ) ) {
			$errors[ $field_key ] = __( 'Choose one of the listed options.', 'ssbd' );
		}

		$clean[ $field_key ] = $value;
	}

	if ( $errors ) {
		return array(
			'ok'      => false,
			'errors'  => $errors,
			'message' => __( 'Please check the highlighted fields.', 'ssbd' ),
		);
	}

	$price = ssbd_product_price( $product );

	$id = wp_insert_post( array(
		'post_type'    => 'ssbd_order',
		'post_status'  => 'publish',
		'post_title'   => sprintf(
			/* translators: 1: product name, 2: customer name */
			__( '%1$s — %2$s', 'ssbd' ),
			$product['name'],
			$clean['name']
		),
		'post_content' => $clean['notes'],
	) );

	if ( ! $id || is_wp_error( $id ) ) {
		return array(
			'ok'      => false,
			'message' => sprintf(
				/* translators: %s: email address */
				__( 'We could not record that order. Please email us at %s.', 'ssbd' ),
				ssbd_site( 'email' )
			),
		);
	}

	update_post_meta( $id, '_ssbd_product', $key );
	update_post_meta( $id, '_ssbd_product_name', $product['name'] );
	// The price as it stood when the order was placed. Editing the product
	// later must not rewrite what somebody was quoted.
	update_post_meta( $id, '_ssbd_price', $price );
	update_post_meta( $id, '_ssbd_order_status', 'new' );

	foreach ( $clean as $field_key => $value ) {
		if ( '' !== $value && 'notes' !== $field_key ) {
			update_post_meta( $id, '_ssbd_' . $field_key, $value );
		}
	}

	ssbd_notify_order( $id, $product, $clean, $price );

	/**
	 * Fires after an order is recorded — hook a CRM, an invoice or a gateway here.
	 *
	 * @param int   $id      Order post ID.
	 * @param array $product Product ordered.
	 * @param array $clean   Sanitized customer fields.
	 */
	do_action( 'ssbd_order_placed', $id, $product, $clean );

	return array(
		'ok'      => true,
		'message' => __( 'Order received. We will confirm the details and payment by email within one business day.', 'ssbd' ),
	);
}

/**
 * Email the order to the site owner, and a copy to the customer.
 *
 * A failed send is not a failed order: the order is already stored, so the
 * customer is told it worked and the owner still sees it in wp-admin.
 *
 * @param int    $id      Order post ID.
 * @param array  $product Product ordered.
 * @param array  $clean   Sanitized customer fields.
 * @param string $price   Price at time of order.
 */
function ssbd_notify_order( $id, $product, $clean, $price ) {
	$fields = ssbd_order_fields();
	$lines  = array(
		__( 'Product', 'ssbd' ) . ': ' . $product['name'],
		__( 'Price', 'ssbd' ) . ': ' . ( $price ? $price : __( 'Quote requested', 'ssbd' ) ),
		'',
	);

	foreach ( $fields as $key => $field ) {
		$value = $clean[ $key ] ?? '';

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
	$lines[] = __( 'Placed', 'ssbd' ) . ': ' . current_time( 'mysql' );
	$lines[] = __( 'Manage', 'ssbd' ) . ': ' . admin_url( 'post.php?post=' . $id . '&action=edit' );

	wp_mail(
		ssbd_inquiry_recipient(),
		sprintf(
			/* translators: %s: product name */
			__( 'New order — %s', 'ssbd' ),
			$product['name']
		),
		implode( "\n", $lines ),
		array(
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . $clean['name'] . ' <' . $clean['email'] . '>',
		)
	);

	if ( ! apply_filters( 'ssbd_confirm_order_to_customer', true, $id ) ) {
		return;
	}

	$confirmation = array(
		sprintf(
			/* translators: %s: customer name */
			__( 'Hi %s,', 'ssbd' ),
			$clean['name']
		),
		'',
		sprintf(
			/* translators: %s: product name */
			__( 'Thanks for your order for %s. We have it, and we will reply within one business day with the payment details and next steps.', 'ssbd' ),
			$product['name']
		),
		'',
		__( 'Price', 'ssbd' ) . ': ' . ( $price ? $price : __( 'we will quote you', 'ssbd' ) ),
		'',
		// The customer's own way back in, without an account to create. The
		// link is generated on request rather than embedded here, so a
		// forwarded confirmation cannot be used to read someone's orders.
		__( 'You can check the status of this order any time:', 'ssbd' ),
		ssbd_page_url( 'track-order' ),
		'',
		ssbd_site( 'legal_name', get_bloginfo( 'name' ) ),
		home_url( '/' ),
	);

	wp_mail(
		$clean['email'],
		sprintf(
			/* translators: %s: product name */
			__( 'Your order — %s', 'ssbd' ),
			$product['name']
		),
		implode( "\n", $confirmation ),
		array( 'Content-Type: text/plain; charset=UTF-8' )
	);
}

/**
 * No-JS form handler.
 *
 * On failure the customer is sent back to the form they came from with the
 * product still attached, so a mistyped email does not cost them the product
 * they had picked.
 */
function ssbd_handle_order_post() {
	check_admin_referer( 'ssbd_order', 'ssbd_order_nonce' );

	// phpcs:disable WordPress.Security.NonceVerification -- verified above.
	$result  = ssbd_process_order( $_POST );
	$product = sanitize_title( wp_unslash( $_POST['product'] ?? '' ) );
	// phpcs:enable WordPress.Security.NonceVerification

	$redirect = remove_query_arg( array( 'order', 'product' ), ssbd_page_url( 'order' ) );
	$redirect = add_query_arg( 'order', $result['ok'] ? 'ok' : 'error', $redirect );

	if ( $product ) {
		$redirect = add_query_arg( 'product', $product, $redirect );
	}

	wp_safe_redirect( $redirect . '#order' );
	exit;
}
add_action( 'admin_post_nopriv_ssbd_order', 'ssbd_handle_order_post' );
add_action( 'admin_post_ssbd_order', 'ssbd_handle_order_post' );

/**
 * Show the product, customer and status in the admin list.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function ssbd_order_columns( $columns ) {
	return array(
		'cb'           => $columns['cb'] ?? '',
		'title'        => __( 'Order', 'ssbd' ),
		'ssbd_product' => __( 'Product', 'ssbd' ),
		'ssbd_who'     => __( 'Customer', 'ssbd' ),
		'ssbd_status'  => __( 'Status', 'ssbd' ),
		'date'         => __( 'Placed', 'ssbd' ),
	);
}
add_filter( 'manage_ssbd_order_posts_columns', 'ssbd_order_columns' );

/**
 * Fill the custom columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function ssbd_order_column_content( $column, $post_id ) {
	if ( 'ssbd_product' === $column ) {
		$name  = (string) get_post_meta( $post_id, '_ssbd_product_name', true );
		$price = (string) get_post_meta( $post_id, '_ssbd_price', true );

		echo esc_html( $name ?: '—' );

		if ( $price ) {
			echo '<br><small>' . esc_html( $price ) . '</small>';
		}

		return;
	}

	if ( 'ssbd_who' === $column ) {
		$email = (string) get_post_meta( $post_id, '_ssbd_email', true );
		$phone = (string) get_post_meta( $post_id, '_ssbd_phone', true );

		if ( $email ) {
			printf( '<a href="mailto:%1$s">%1$s</a>', esc_attr( $email ) );
		}
		if ( $phone ) {
			echo ( $email ? '<br>' : '' ) . '<small>' . esc_html( $phone ) . '</small>';
		}
		if ( ! $email && ! $phone ) {
			echo '—';
		}

		return;
	}

	if ( 'ssbd_status' === $column ) {
		$status = (string) get_post_meta( $post_id, '_ssbd_order_status', true ) ?: 'new';

		echo esc_html( ssbd_order_statuses()[ $status ] ?? $status );
	}
}
add_action( 'manage_ssbd_order_posts_custom_column', 'ssbd_order_column_content', 10, 2 );

/**
 * Keep the per-product order URLs out of the index.
 *
 * /order/?product=a and /order/?product=b are the same page with a different
 * product name in it. The bare /order/ page stays indexable; the query-string
 * variants would only ever be thin duplicates of it.
 *
 * @param array $directives Robots directives.
 * @return array
 */
function ssbd_order_robots( $directives ) {
	// phpcs:ignore WordPress.Security.NonceVerification -- read-only URL shape check.
	if ( ! is_page_template( 'page-templates/template-order.php' ) || ! isset( $_GET['product'] ) ) {
		return $directives;
	}

	return array_merge(
		array( 'noindex', 'follow' ),
		array_values( array_diff( $directives, array( 'index', 'follow', 'noindex' ) ) )
	);
}
add_filter( 'ssbd_robots_directives', 'ssbd_order_robots' );
