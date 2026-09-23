<?php
/**
 * Reader accounts: themed login, registration, and Sign in with Google.
 *
 * Everything here exists so a reader never sees wp-login.php. That screen is
 * WordPress's own branding on someone else's site, and the moment a visitor is
 * asked to log in to leave a comment it is the worst possible thing to show
 * them. The forms live on ordinary pages with the site header and footer
 * around them instead.
 *
 * Accounts created here are Subscribers, which can read and comment and can do
 * nothing else. Registration and Google sign-in both refuse to grant any other
 * role, whatever WordPress's own "New User Default Role" is set to — that
 * setting governs wp-admin, and a public form must not inherit it.
 *
 * Credentials for Google live under Settings → Login & Accounts, not in the
 * Customizer: a client secret is not an appearance choice, and the Customizer
 * stores its values in a theme_mod that ships with an export.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/** Option holding every account setting. */
const SSBD_ACCOUNTS_OPTION = 'ssbd_accounts';

/** How long a pending Google sign-in stays valid, in seconds. */
const SSBD_OAUTH_TTL = 15 * MINUTE_IN_SECONDS;

/** Email-verification links expire after one day. */
const SSBD_EMAIL_VERIFY_TTL = DAY_IN_SECONDS;

/**
 * Account settings, with defaults filled in.
 *
 * @return array{google_enabled:bool, google_client_id:string, google_client_secret:string, allow_registration:bool, comment_login:bool}
 */
function ssbd_accounts_settings() {
	$saved = get_option( SSBD_ACCOUNTS_OPTION, array() );

	return wp_parse_args( is_array( $saved ) ? $saved : array(), array(
		'google_enabled'       => false,
		'google_client_id'     => '',
		'google_client_secret' => '',
		'allow_registration'   => true,
		'comment_login'        => true,
	) );
}

/**
 * One account setting.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function ssbd_account_setting( $key, $default = null ) {
	$settings = ssbd_accounts_settings();

	return $settings[ $key ] ?? $default;
}

/**
 * Is Sign in with Google configured and switched on?
 *
 * Both halves of the credential are required: a client ID on its own produces
 * a Google error screen, which is worse than not offering the button.
 *
 * @return bool
 */
function ssbd_google_enabled() {
	return (bool) ssbd_account_setting( 'google_enabled' )
		&& '' !== trim( (string) ssbd_account_setting( 'google_client_id' ) )
		&& '' !== trim( (string) ssbd_account_setting( 'google_client_secret' ) );
}

/*
 * WordPress already refuses a comment from a logged-out visitor when
 * comment_registration is on, and wp-comments-post.php enforces it server-side.
 * Filtering the option means the theme's switch drives core's own check rather
 * than the theme re-implementing it and getting it subtly wrong.
 */
add_filter( 'pre_option_comment_registration', function ( $value ) {
	return ssbd_account_setting( 'comment_login' ) ? '1' : $value;
} );

/**
 * Reject comments from accounts whose email has not been verified.
 *
 * @param array<string,mixed> $commentdata Submitted comment data.
 * @return array<string,mixed>
 */
function ssbd_require_verified_commenter( $commentdata ) {
	if ( ! ssbd_account_setting( 'comment_login' ) ) {
		return $commentdata;
	}

	$user_id = get_current_user_id();
	if ( ! $user_id || ! ssbd_user_email_verified( $user_id ) ) {
		wp_die(
			esc_html__( 'Verify your email address before posting a comment.', 'ssbd' ),
			esc_html__( 'Email verification required', 'ssbd' ),
			array( 'response' => 403, 'back_link' => true )
		);
	}

	$rate_key = 'ssbd_comment_rate_' . $user_id;
	if ( (int) get_transient( $rate_key ) >= 5 ) {
		wp_die(
			esc_html__( 'You are commenting too quickly. Please wait ten minutes and try again.', 'ssbd' ),
			esc_html__( 'Please slow down', 'ssbd' ),
			array( 'response' => 429, 'back_link' => true )
		);
	}

	return $commentdata;
}
add_filter( 'preprocess_comment', 'ssbd_require_verified_commenter' );

/** Count accepted submissions for the per-account comment rate limit. */
function ssbd_count_comment_submission() {
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return;
	}

	$key = 'ssbd_comment_rate_' . $user_id;
	set_transient( $key, (int) get_transient( $key ) + 1, 10 * MINUTE_IN_SECONDS );
}
add_action( 'comment_post', 'ssbd_count_comment_submission' );

/**
 * The themed login page, with somewhere to return to afterwards.
 *
 * @param string $redirect Where to land after signing in. Defaults to the
 *                         current URL, so a reader resumes where they were.
 * @return string
 */
function ssbd_login_url( $redirect = '' ) {
	$url = ssbd_page_url( 'login' );

	return $redirect ? add_query_arg( 'redirect_to', rawurlencode( $redirect ), $url ) : $url;
}

/**
 * The themed registration page.
 *
 * @param string $redirect Where to land afterwards.
 * @return string
 */
function ssbd_register_url( $redirect = '' ) {
	$url = ssbd_page_url( 'register' );

	return $redirect ? add_query_arg( 'redirect_to', rawurlencode( $redirect ), $url ) : $url;
}

/**
 * The URL of the page a visitor is on, for round-tripping through a form.
 *
 * @return string
 */
function ssbd_current_url() {
	$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';

	return home_url( esc_url_raw( $path ) );
}

/**
 * Reduce a submitted redirect to somewhere on this site.
 *
 * wp_validate_redirect() is what stops the login form being used as an open
 * redirect: anything off-host falls back to the home page.
 *
 * @param string $raw      Submitted value.
 * @param string $fallback Used when the value is empty or off-site.
 * @return string
 */
function ssbd_safe_redirect_target( $raw, $fallback = '' ) {
	$fallback = $fallback ?: home_url( '/' );
	$raw      = trim( (string) $raw );

	if ( '' === $raw ) {
		return $fallback;
	}

	return wp_validate_redirect( $raw, $fallback );
}

/**
 * Send the visitor back to a form with an error code attached.
 *
 * The code, never the message: a message in a URL is a phishing surface, and
 * the wording belongs with the form that renders it.
 *
 * @param string $page     'login' or 'register'.
 * @param string $code     Error code from ssbd_account_error_message().
 * @param string $redirect The redirect the form was carrying.
 * @param array  $extra    Extra query args to preserve, e.g. a typed email.
 */
function ssbd_account_bounce( $page, $code, $redirect = '', $extra = array() ) {
	$url = 'register' === $page ? ssbd_register_url() : ssbd_login_url();
	$url = add_query_arg( array_merge( array( 'auth_error' => $code ), $extra ), $url );

	if ( $redirect ) {
		$url = add_query_arg( 'redirect_to', rawurlencode( $redirect ), $url );
	}

	wp_safe_redirect( $url );
	exit;
}

/**
 * Wording for an error code.
 *
 * Sign-in failures stay deliberately vague about which half was wrong: saying
 * "no such account" confirms to anyone asking whether an address is registered
 * here, which is exactly the question a credential-stuffing script wants
 * answered.
 *
 * @param string $code Error code.
 * @return string
 */
function ssbd_account_error_message( $code ) {
	$messages = array(
		'empty'        => __( 'Enter your email and password.', 'ssbd' ),
		'invalid'      => __( 'That email and password do not match an account.', 'ssbd' ),
		'email'        => __( 'Enter a valid email address.', 'ssbd' ),
		'exists'       => __( 'An account already exists for that email. Try signing in instead.', 'ssbd' ),
		'name'         => __( 'Enter the name you would like to comment under.', 'ssbd' ),
		'password'     => __( 'Choose a password of at least eight characters.', 'ssbd' ),
		'mismatch'     => __( 'The two passwords do not match.', 'ssbd' ),
		'closed'       => __( 'New accounts are not being accepted at the moment.', 'ssbd' ),
		'google'       => __( 'Google sign-in did not complete. Please try again.', 'ssbd' ),
			'google_email' => __( 'Google did not confirm an email address for that account.', 'ssbd' ),
			'expired'      => __( 'That sign-in attempt timed out. Please try again.', 'ssbd' ),
			'verify_sent'  => __( 'Check your inbox and verify your email before commenting.', 'ssbd' ),
			'verify_mail'  => __( 'We could not send the verification email. Please try again later.', 'ssbd' ),
			'verify_link'  => __( 'That verification link is invalid or has expired.', 'ssbd' ),
			'rate'         => __( 'Too many attempts. Please wait one hour and try again.', 'ssbd' ),
	);

	return $messages[ $code ] ?? __( 'Something went wrong. Please try again.', 'ssbd' );
}

/**
 * Whether a reader has proved ownership of the email on their account.
 *
 * @param int $user_id User ID.
 * @return bool
 */
function ssbd_user_email_verified( $user_id = 0 ) {
	$user_id = $user_id ?: get_current_user_id();
	if ( ! $user_id ) {
		return false;
	}

	return user_can( $user_id, 'moderate_comments' )
		|| '1' === (string) get_user_meta( $user_id, 'ssbd_email_verified', true )
		|| '' !== (string) get_user_meta( $user_id, 'ssbd_google_id', true );
}

/**
 * Send a single-use email-verification link.
 *
 * @param int    $user_id  User ID.
 * @param string $redirect Where to go after verification.
 * @return bool
 */
function ssbd_send_account_verification( $user_id, $redirect = '' ) {
	$user = get_user_by( 'id', $user_id );
	if ( ! $user || ! is_email( $user->user_email ) ) {
		return false;
	}

	$token = wp_generate_password( 48, false, false );
	update_user_meta( $user_id, 'ssbd_email_verify_hash', wp_hash_password( $token ) );
	update_user_meta( $user_id, 'ssbd_email_verify_expires', time() + SSBD_EMAIL_VERIFY_TTL );
	update_user_meta( $user_id, 'ssbd_email_verify_redirect', ssbd_safe_redirect_target( $redirect ) );

	$url = add_query_arg( array(
		'action' => 'ssbd_verify_email',
		'user'   => $user_id,
		'token'  => $token,
	), admin_url( 'admin-post.php' ) );

	return wp_mail(
		$user->user_email,
		__( 'Verify your email address', 'ssbd' ),
		sprintf(
			/* translators: 1: display name, 2: verification URL. */
			__( "Hello %1\$s,\n\nVerify your email address to activate commenting:\n%2\$s\n\nThis link expires in 24 hours. If you did not create this account, ignore this email.", 'ssbd' ),
			$user->display_name,
			$url
		),
		array( 'Content-Type: text/plain; charset=UTF-8' )
	);
}

/** Complete a reader's email-verification round trip. */
function ssbd_verify_account_email() {
	// phpcs:disable WordPress.Security.NonceVerification -- the random, hashed, single-use token is the nonce.
	$user_id = absint( $_GET['user'] ?? 0 );
	$token   = sanitize_text_field( wp_unslash( $_GET['token'] ?? '' ) );
	// phpcs:enable
	$hash    = (string) get_user_meta( $user_id, 'ssbd_email_verify_hash', true );
	$expires = (int) get_user_meta( $user_id, 'ssbd_email_verify_expires', true );

	if ( ! $user_id || ! $token || ! $hash || $expires < time() || ! wp_check_password( $token, $hash ) ) {
		ssbd_account_bounce( 'login', 'verify_link' );
	}

	$redirect = (string) get_user_meta( $user_id, 'ssbd_email_verify_redirect', true );
	update_user_meta( $user_id, 'ssbd_email_verified', '1' );
	delete_user_meta( $user_id, 'ssbd_email_verify_hash' );
	delete_user_meta( $user_id, 'ssbd_email_verify_expires' );
	delete_user_meta( $user_id, 'ssbd_email_verify_redirect' );

	ssbd_sign_in_and_redirect( $user_id, $redirect );
}
add_action( 'admin_post_nopriv_ssbd_verify_email', 'ssbd_verify_account_email' );
add_action( 'admin_post_ssbd_verify_email', 'ssbd_verify_account_email' );

/** Send another verification link for a signed-in reader. */
function ssbd_resend_account_verification() {
	check_admin_referer( 'ssbd_resend_verification' );
	$user_id  = get_current_user_id();
	$redirect = ssbd_safe_redirect_target( wp_unslash( $_POST['redirect_to'] ?? '' ) );

	if ( $user_id && ! ssbd_user_email_verified( $user_id ) ) {
		ssbd_send_account_verification( $user_id, $redirect );
	}

	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_post_ssbd_resend_verification', 'ssbd_resend_account_verification' );

/**
 * Create a reader account.
 *
 * Always a Subscriber, never the site's default role — see the file header.
 *
 * @param string $email    Email address.
 * @param string $name     Display name.
 * @param string $password Plain password, or empty to generate one.
 * @return int|WP_Error User ID.
 */
function ssbd_create_reader( $email, $name, $password = '' ) {
	$email = sanitize_email( $email );

	if ( ! is_email( $email ) ) {
		return new WP_Error( 'email', ssbd_account_error_message( 'email' ) );
	}

	if ( email_exists( $email ) ) {
		return new WP_Error( 'exists', ssbd_account_error_message( 'exists' ) );
	}

	/*
	 * The login is derived from the address and then made unique, because two
	 * people at different domains can share a local part and wp_insert_user()
	 * rejects the second one rather than adjusting it.
	 */
	$base  = sanitize_user( current( explode( '@', $email ) ), true );
	$base  = $base ?: 'reader';
	$login = $base;
	$n     = 1;

	while ( username_exists( $login ) ) {
		$n++;
		$login = $base . $n;
	}

	$user_id = wp_insert_user( array(
		'user_login'   => $login,
		'user_email'   => $email,
		'user_pass'    => $password ?: wp_generate_password( 24 ),
		'display_name' => $name ?: $base,
		'nickname'     => $name ?: $base,
		'role'         => 'subscriber',
	) );

	if ( is_wp_error( $user_id ) ) {
		return $user_id;
	}

	/**
	 * Fires after a reader account is created.
	 *
	 * @param int    $user_id New user ID.
	 * @param string $email   Email address.
	 */
	do_action( 'ssbd_reader_registered', $user_id, $email );

	return $user_id;
}

/**
 * Sign a user in and send them on.
 *
 * @param int    $user_id  User to authenticate.
 * @param string $redirect Where to land.
 */
function ssbd_sign_in_and_redirect( $user_id, $redirect ) {
	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, true );

	$user = get_user_by( 'id', $user_id );

	if ( $user ) {
		do_action( 'wp_login', $user->user_login, $user );
	}

	wp_safe_redirect( ssbd_safe_redirect_target( $redirect ) );
	exit;
}

/* -------------------------------------------------------------------------
 * Form handlers
 * ---------------------------------------------------------------------- */

/**
 * Handle the login form.
 */
function ssbd_handle_login() {
	check_admin_referer( 'ssbd_login', 'ssbd_login_nonce' );

	// phpcs:disable WordPress.Security.NonceVerification -- verified above.
	$redirect = ssbd_safe_redirect_target( wp_unslash( $_POST['redirect_to'] ?? '' ) );
	$login    = sanitize_text_field( wp_unslash( $_POST['log'] ?? '' ) );
	$password = (string) wp_unslash( $_POST['pwd'] ?? '' );
	$remember = ! empty( $_POST['rememberme'] );
	// phpcs:enable

	if ( '' === $login || '' === $password ) {
		ssbd_account_bounce( 'login', 'empty', $redirect );
	}

	$user = wp_signon( array(
		'user_login'    => $login,
		'user_password' => $password,
		'remember'      => $remember,
	), is_ssl() );

	if ( is_wp_error( $user ) ) {
		ssbd_account_bounce( 'login', 'invalid', $redirect );
	}

	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_post_nopriv_ssbd_login', 'ssbd_handle_login' );
add_action( 'admin_post_ssbd_login', 'ssbd_handle_login' );

/**
 * Handle the registration form.
 */
function ssbd_handle_register() {
	check_admin_referer( 'ssbd_register', 'ssbd_register_nonce' );

	// phpcs:disable WordPress.Security.NonceVerification -- verified above.
	$redirect = ssbd_safe_redirect_target( wp_unslash( $_POST['redirect_to'] ?? '' ) );
	$email    = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$name     = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$password = (string) wp_unslash( $_POST['pwd'] ?? '' );
	$confirm  = (string) wp_unslash( $_POST['pwd2'] ?? '' );
	$trap     = trim( (string) wp_unslash( $_POST['website'] ?? '' ) );
	// phpcs:enable

	// Honeypot, as on every other public form in the theme.
	if ( '' !== $trap ) {
		wp_safe_redirect( $redirect );
		exit;
	}

	if ( ! ssbd_account_setting( 'allow_registration' ) ) {
		ssbd_account_bounce( 'register', 'closed', $redirect );
	}

	$keep = array( 'auth_email' => $email, 'auth_name' => $name );

	if ( '' === $name ) {
		ssbd_account_bounce( 'register', 'name', $redirect, $keep );
	}

	if ( ! is_email( $email ) ) {
		ssbd_account_bounce( 'register', 'email', $redirect, $keep );
	}

	if ( strlen( $password ) < 8 ) {
		ssbd_account_bounce( 'register', 'password', $redirect, $keep );
	}

	if ( $password !== $confirm ) {
		ssbd_account_bounce( 'register', 'mismatch', $redirect, $keep );
	}

	$ip       = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? 'unknown' ) );
	$rate_key = 'ssbd_register_rate_' . hash( 'sha256', $ip );
	$attempts = (int) get_transient( $rate_key );
	if ( $attempts >= 3 ) {
		ssbd_account_bounce( 'register', 'rate', $redirect, $keep );
	}
	set_transient( $rate_key, $attempts + 1, HOUR_IN_SECONDS );

	$user_id = ssbd_create_reader( $email, $name, $password );

	if ( is_wp_error( $user_id ) ) {
		ssbd_account_bounce( 'register', $user_id->get_error_code(), $redirect, $keep );
	}

	if ( ! ssbd_send_account_verification( $user_id, $redirect ) ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';
		wp_delete_user( $user_id );
		ssbd_account_bounce( 'register', 'verify_mail', $redirect, $keep );
	}

	ssbd_account_bounce( 'login', 'verify_sent', $redirect, array( 'auth_email' => $email ) );
}
add_action( 'admin_post_nopriv_ssbd_register', 'ssbd_handle_register' );
add_action( 'admin_post_ssbd_register', 'ssbd_handle_register' );

/* -------------------------------------------------------------------------
 * Sign in with Google (OAuth 2.0 / OpenID Connect)
 * ---------------------------------------------------------------------- */

/**
 * The redirect URI Google must be told about.
 *
 * A fixed admin-post URL rather than a pretty permalink, so it never changes
 * with the site's permalink structure — a mismatch here is the single most
 * common reason a Google integration returns redirect_uri_mismatch.
 *
 * @return string
 */
function ssbd_google_redirect_uri() {
	return admin_url( 'admin-post.php?action=ssbd_google_callback' );
}

/**
 * Begin the Google flow.
 */
function ssbd_google_start() {
	if ( ! ssbd_google_enabled() ) {
		wp_safe_redirect( ssbd_login_url() );
		exit;
	}

	// phpcs:ignore WordPress.Security.NonceVerification -- no state to change; the CSRF guard is the state parameter created below.
	$redirect = ssbd_safe_redirect_target( wp_unslash( $_GET['redirect_to'] ?? '' ) );

	/*
	 * The state parameter is the CSRF guard for the round trip. A random key
	 * goes to Google and the value it protects stays here in a transient, so
	 * nothing about the visitor's session travels through Google and a
	 * callback carrying someone else's state cannot be replayed.
	 */
	$key = wp_generate_password( 32, false );
	set_transient( 'ssbd_oauth_' . $key, $redirect, SSBD_OAUTH_TTL );

	$url = add_query_arg( array(
		'client_id'     => rawurlencode( ssbd_account_setting( 'google_client_id' ) ),
		'redirect_uri'  => rawurlencode( ssbd_google_redirect_uri() ),
		'response_type' => 'code',
		'scope'         => rawurlencode( 'openid email profile' ),
		'state'         => $key,
		'prompt'        => 'select_account',
	), 'https://accounts.google.com/o/oauth2/v2/auth' );

	// Not wp_safe_redirect: the whole point is to leave for accounts.google.com.
	wp_redirect( $url );
	exit;
}
add_action( 'admin_post_nopriv_ssbd_google', 'ssbd_google_start' );
add_action( 'admin_post_ssbd_google', 'ssbd_google_start' );

/**
 * Handle Google's callback.
 */
function ssbd_google_callback() {
	if ( ! ssbd_google_enabled() ) {
		wp_safe_redirect( ssbd_login_url() );
		exit;
	}

	// phpcs:disable WordPress.Security.NonceVerification -- the state parameter below is the nonce for this round trip.
	$state = sanitize_text_field( wp_unslash( $_GET['state'] ?? '' ) );
	$code  = sanitize_text_field( wp_unslash( $_GET['code'] ?? '' ) );
	// phpcs:enable

	if ( '' === $state ) {
		ssbd_account_bounce( 'login', 'google' );
	}

	$redirect = get_transient( 'ssbd_oauth_' . $state );

	// Single use: consumed whether or not the rest succeeds, so a callback
	// URL cannot be replayed out of someone's browser history.
	delete_transient( 'ssbd_oauth_' . $state );

	if ( false === $redirect ) {
		ssbd_account_bounce( 'login', 'expired' );
	}

	if ( '' === $code ) {
		// The visitor pressed Cancel on Google's screen. Not an error worth
		// shouting about — put them back where they started.
		wp_safe_redirect( ssbd_safe_redirect_target( $redirect ) );
		exit;
	}

	$token = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
		'timeout' => 15,
		'body'    => array(
			'code'          => $code,
			'client_id'     => ssbd_account_setting( 'google_client_id' ),
			'client_secret' => ssbd_account_setting( 'google_client_secret' ),
			'redirect_uri'  => ssbd_google_redirect_uri(),
			'grant_type'    => 'authorization_code',
		),
	) );

	if ( is_wp_error( $token ) || 200 !== (int) wp_remote_retrieve_response_code( $token ) ) {
		ssbd_account_bounce( 'login', 'google', $redirect );
	}

	$token_body   = json_decode( wp_remote_retrieve_body( $token ), true );
	$access_token = $token_body['access_token'] ?? '';

	if ( ! $access_token ) {
		ssbd_account_bounce( 'login', 'google', $redirect );
	}

	/*
	 * The profile is read from the userinfo endpoint rather than decoded out
	 * of the id_token. Both carry the same claims, but trusting a JWT means
	 * verifying its signature against Google's rotating public keys — this
	 * asks Google directly over TLS and needs no crypto here to be correct.
	 */
	$profile = wp_remote_get( 'https://openidconnect.googleapis.com/v1/userinfo', array(
		'timeout' => 15,
		'headers' => array( 'Authorization' => 'Bearer ' . $access_token ),
	) );

	if ( is_wp_error( $profile ) || 200 !== (int) wp_remote_retrieve_response_code( $profile ) ) {
		ssbd_account_bounce( 'login', 'google', $redirect );
	}

	$data  = json_decode( wp_remote_retrieve_body( $profile ), true );
	$email = sanitize_email( $data['email'] ?? '' );

	// An unverified address would let anyone claim an account by registering
	// the same string at a provider that does not check it.
	if ( ! is_email( $email ) || empty( $data['email_verified'] ) ) {
		ssbd_account_bounce( 'login', 'google_email', $redirect );
	}

	$user = get_user_by( 'email', $email );

	if ( $user ) {
		update_user_meta( $user->ID, 'ssbd_google_id', sanitize_text_field( $data['sub'] ?? '' ) );
		update_user_meta( $user->ID, 'ssbd_email_verified', '1' );
		ssbd_sign_in_and_redirect( $user->ID, $redirect );
	}

	if ( ! ssbd_account_setting( 'allow_registration' ) ) {
		ssbd_account_bounce( 'login', 'closed', $redirect );
	}

	$user_id = ssbd_create_reader( $email, sanitize_text_field( $data['name'] ?? '' ) );

	if ( is_wp_error( $user_id ) ) {
		ssbd_account_bounce( 'login', 'google', $redirect );
	}

	/*
	 * Google's `picture` claim is deliberately not stored. inc/avatars.php
	 * draws every avatar locally from the commenter's initial rather than
	 * fetching one per reader from a third-party host, and a URL nothing
	 * renders is just a field to keep in sync forever.
	 */
	update_user_meta( $user_id, 'ssbd_google_id', sanitize_text_field( $data['sub'] ?? '' ) );
	update_user_meta( $user_id, 'ssbd_email_verified', '1' );

	ssbd_sign_in_and_redirect( $user_id, $redirect );
}
add_action( 'admin_post_nopriv_ssbd_google_callback', 'ssbd_google_callback' );
add_action( 'admin_post_ssbd_google_callback', 'ssbd_google_callback' );

/* -------------------------------------------------------------------------
 * Settings screen
 * ---------------------------------------------------------------------- */

/**
 * Register the settings page under Settings.
 */
function ssbd_accounts_settings_page() {
	add_options_page(
		__( 'Login & Accounts', 'ssbd' ),
		__( 'Login & Accounts', 'ssbd' ),
		'manage_options',
		'ssbd-accounts',
		'ssbd_render_accounts_settings'
	);
}
add_action( 'admin_menu', 'ssbd_accounts_settings_page' );

/**
 * Register the option and its sanitiser.
 */
function ssbd_register_accounts_settings() {
	register_setting( 'ssbd_accounts_group', SSBD_ACCOUNTS_OPTION, array(
		'type'              => 'array',
		'sanitize_callback' => 'ssbd_sanitize_accounts_settings',
		'default'           => array(),
	) );
}
add_action( 'admin_init', 'ssbd_register_accounts_settings' );

/**
 * Sanitise the settings form.
 *
 * The secret is preserved when the field comes back empty, so that a save made
 * from a screen showing a masked value does not silently wipe it.
 *
 * @param mixed $input Raw submission.
 * @return array
 */
function ssbd_sanitize_accounts_settings( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$existing = ssbd_accounts_settings();

	$secret = trim( (string) ( $input['google_client_secret'] ?? '' ) );

	return array(
		'google_enabled'       => ! empty( $input['google_enabled'] ),
		'google_client_id'     => sanitize_text_field( $input['google_client_id'] ?? '' ),
		'google_client_secret' => '' === $secret ? $existing['google_client_secret'] : sanitize_text_field( $secret ),
		'allow_registration'   => ! empty( $input['allow_registration'] ),
		'comment_login'        => ! empty( $input['comment_login'] ),
	);
}

/**
 * Render the settings screen.
 */
function ssbd_render_accounts_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings = ssbd_accounts_settings();
	$has_key  = '' !== $settings['google_client_secret'];
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Login & Accounts', 'ssbd' ); ?></h1>
		<p>
			<?php esc_html_e( 'Readers sign in on the themed Login page rather than wp-login.php, and every account created here is a Subscriber — able to comment, and nothing more.', 'ssbd' ); ?>
		</p>

		<form method="post" action="options.php">
			<?php settings_fields( 'ssbd_accounts_group' ); ?>

			<h2 class="title"><?php esc_html_e( 'Accounts', 'ssbd' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Registration', 'ssbd' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( SSBD_ACCOUNTS_OPTION ); ?>[allow_registration]" value="1" <?php checked( $settings['allow_registration'] ); ?>>
							<?php esc_html_e( 'Let visitors create an account', 'ssbd' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Turning this off hides the sign-up form and stops Google sign-in creating new accounts. People who already have one can still sign in.', 'ssbd' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Comments', 'ssbd' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( SSBD_ACCOUNTS_OPTION ); ?>[comment_login]" value="1" <?php checked( $settings['comment_login'] ); ?>>
							<?php esc_html_e( 'Require an account to comment', 'ssbd' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Visitors who are not signed in see an invitation to sign in instead of the comment box. This drives WordPress\'s own "Users must be registered and logged in to comment" setting.', 'ssbd' ); ?></p>
					</td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Sign in with Google', 'ssbd' ); ?></h2>
			<p>
				<?php esc_html_e( 'Create an OAuth 2.0 Client ID of type "Web application" in the Google Cloud console, then paste the two values below.', 'ssbd' ); ?>
				<a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open Google Cloud credentials', 'ssbd' ); ?></a>
			</p>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Authorised redirect URI', 'ssbd' ); ?></th>
					<td>
						<input type="text" class="large-text code" readonly onfocus="this.select()" value="<?php echo esc_attr( ssbd_google_redirect_uri() ); ?>">
						<p class="description"><?php esc_html_e( 'Copy this into "Authorised redirect URIs" in Google, exactly as shown. A mismatch here is what produces redirect_uri_mismatch.', 'ssbd' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Enable', 'ssbd' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( SSBD_ACCOUNTS_OPTION ); ?>[google_enabled]" value="1" <?php checked( $settings['google_enabled'] ); ?>>
							<?php esc_html_e( 'Show the "Continue with Google" button', 'ssbd' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'The button only appears once both credentials below are filled in.', 'ssbd' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="ssbd-google-id"><?php esc_html_e( 'Client ID', 'ssbd' ); ?></label></th>
					<td>
						<input id="ssbd-google-id" type="text" class="large-text code" name="<?php echo esc_attr( SSBD_ACCOUNTS_OPTION ); ?>[google_client_id]" value="<?php echo esc_attr( $settings['google_client_id'] ); ?>" autocomplete="off" placeholder="000000000000-xxxxxxxx.apps.googleusercontent.com">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="ssbd-google-secret"><?php esc_html_e( 'Client secret', 'ssbd' ); ?></label></th>
					<td>
						<input id="ssbd-google-secret" type="password" class="large-text code" name="<?php echo esc_attr( SSBD_ACCOUNTS_OPTION ); ?>[google_client_secret]" value="" autocomplete="new-password" placeholder="<?php echo esc_attr( $has_key ? __( 'Saved — leave blank to keep it', 'ssbd' ) : __( 'GOCSPX-…', 'ssbd' ) ); ?>">
						<p class="description">
							<?php
							echo $has_key
								? esc_html__( 'A secret is stored. Leave this blank to keep it, or paste a new one to replace it.', 'ssbd' )
								: esc_html__( 'Not set yet.', 'ssbd' );
							?>
						</p>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
