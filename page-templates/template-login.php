<?php
/**
 * Template Name: Login
 *
 * The themed sign-in page. Site header and footer, form in the middle — a
 * reader never leaves the site to authenticate.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignore WordPress.Security.NonceVerification -- read-only, and validated against this host before use.
$ssbd_redirect = ssbd_safe_redirect_target( wp_unslash( $_GET['redirect_to'] ?? '' ) );

// Nobody needs to sign in twice.
if ( is_user_logged_in() ) {
	wp_safe_redirect( $ssbd_redirect );
	exit;
}

get_header();

get_template_part( 'template-parts/account/auth-shell', null, array(
	'mode'     => 'login',
	'redirect' => $ssbd_redirect,
	'form'     => function () use ( $ssbd_redirect ) {
		?>
		<form class="auth-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="ssbd_login">
			<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $ssbd_redirect ); ?>">
			<?php wp_nonce_field( 'ssbd_login', 'ssbd_login_nonce' ); ?>

			<div class="field field--full">
				<label for="ssbd-log"><?php esc_html_e( 'Email or username', 'ssbd' ); ?></label>
				<input id="ssbd-log" name="log" type="text" autocomplete="username" required>
			</div>

			<div class="field field--full">
				<label for="ssbd-pwd"><?php esc_html_e( 'Password', 'ssbd' ); ?></label>
				<input id="ssbd-pwd" name="pwd" type="password" autocomplete="current-password" required>
			</div>

			<div class="auth-row">
				<label class="auth-remember">
					<input type="checkbox" name="rememberme" value="1" checked>
					<span><?php esc_html_e( 'Keep me signed in', 'ssbd' ); ?></span>
				</label>

				<?php // Core's own reset flow: password recovery is not worth reimplementing badly. ?>
				<a class="auth-forgot" href="<?php echo esc_url( wp_lostpassword_url( $ssbd_redirect ) ); ?>">
					<?php esc_html_e( 'Forgot password?', 'ssbd' ); ?>
				</a>
			</div>

			<button type="submit" class="btn btn--primary auth-submit"><?php esc_html_e( 'Sign in', 'ssbd' ); ?></button>
		</form>
		<?php
	},
) );

get_footer();
