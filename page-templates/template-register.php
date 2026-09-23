<?php
/**
 * Template Name: Register
 *
 * Sign-up for a reader account. Same frame as the login page, so the two read
 * as one flow rather than two screens someone built at different times.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification -- read-only redisplay of the visitor's own submission.
$ssbd_redirect = ssbd_safe_redirect_target( wp_unslash( $_GET['redirect_to'] ?? '' ) );
$ssbd_email    = sanitize_email( wp_unslash( $_GET['auth_email'] ?? '' ) );
$ssbd_name     = sanitize_text_field( wp_unslash( $_GET['auth_name'] ?? '' ) );
// phpcs:enable

if ( is_user_logged_in() ) {
	wp_safe_redirect( $ssbd_redirect );
	exit;
}

get_header();

if ( ! ssbd_account_setting( 'allow_registration' ) ) :
	?>
	<section class="section auth">
		<div class="wrap">
			<div class="auth-card">
				<h1 class="auth-title"><?php esc_html_e( 'Sign-ups are closed', 'ssbd' ); ?></h1>
				<p class="auth-lede"><?php esc_html_e( 'We are not accepting new accounts at the moment. If you already have one, you can still sign in.', 'ssbd' ); ?></p>
				<a class="btn btn--primary auth-submit" href="<?php echo esc_url( ssbd_login_url( $ssbd_redirect ) ); ?>"><?php esc_html_e( 'Sign in', 'ssbd' ); ?></a>
			</div>
		</div>
	</section>
	<?php
else :

	get_template_part( 'template-parts/account/auth-shell', null, array(
		'mode'     => 'register',
		'redirect' => $ssbd_redirect,
		'form'     => function () use ( $ssbd_redirect, $ssbd_email, $ssbd_name ) {
			?>
			<form class="auth-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ssbd_register">
				<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $ssbd_redirect ); ?>">
				<?php wp_nonce_field( 'ssbd_register', 'ssbd_register_nonce' ); ?>

				<?php // Honeypot. Hidden from people, irresistible to bots. ?>
				<p class="auth-trap" aria-hidden="true">
					<label for="ssbd-website"><?php esc_html_e( 'Leave this empty', 'ssbd' ); ?></label>
					<input id="ssbd-website" name="website" type="text" tabindex="-1" autocomplete="off">
				</p>

				<div class="field field--full">
					<label for="ssbd-name"><?php esc_html_e( 'Display name', 'ssbd' ); ?></label>
					<input id="ssbd-name" name="name" type="text" value="<?php echo esc_attr( $ssbd_name ); ?>" autocomplete="nickname" required>
					<p class="field-note"><?php esc_html_e( 'This is the name shown beside your comments.', 'ssbd' ); ?></p>
				</div>

				<div class="field field--full">
					<label for="ssbd-email"><?php esc_html_e( 'Email', 'ssbd' ); ?></label>
					<input id="ssbd-email" name="email" type="email" value="<?php echo esc_attr( $ssbd_email ); ?>" autocomplete="email" required>
				</div>

				<div class="field field--full">
					<label for="ssbd-pwd"><?php esc_html_e( 'Password', 'ssbd' ); ?></label>
					<input id="ssbd-pwd" name="pwd" type="password" autocomplete="new-password" minlength="8" required>
					<p class="field-note"><?php esc_html_e( 'At least eight characters.', 'ssbd' ); ?></p>
				</div>

				<div class="field field--full">
					<label for="ssbd-pwd2"><?php esc_html_e( 'Confirm password', 'ssbd' ); ?></label>
					<input id="ssbd-pwd2" name="pwd2" type="password" autocomplete="new-password" minlength="8" required>
				</div>

				<button type="submit" class="btn btn--primary auth-submit"><?php esc_html_e( 'Create account', 'ssbd' ); ?></button>

				<p class="auth-fineprint">
					<?php
					printf(
						/* translators: 1: privacy policy link open tag, 2: close tag */
						esc_html__( 'By creating an account you agree to our %1$sprivacy policy%2$s.', 'ssbd' ),
						'<a href="' . esc_url( ssbd_page_url( 'privacy-policy' ) ) . '">',
						'</a>'
					);
					?>
				</p>
			</form>
			<?php
		},
	) );

endif;

get_footer();
