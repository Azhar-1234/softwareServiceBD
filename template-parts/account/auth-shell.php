<?php
/**
 * The frame both auth forms sit in: heading, error, Google button, form, footer link.
 *
 * Shared so the login and register pages cannot drift apart visually, and so
 * the error banner and the Google divider are written once.
 *
 * @package ssbd
 *
 * @var string   $args['mode']     'login' or 'register'.
 * @var string   $args['redirect'] Where to go after signing in.
 * @var callable $args['form']     Renders the fields between the divider and the footer.
 */

defined( 'ABSPATH' ) || exit;

$ssbd_mode     = 'register' === ( $args['mode'] ?? 'login' ) ? 'register' : 'login';
$ssbd_redirect = $args['redirect'] ?? home_url( '/' );
$ssbd_form     = $args['form'] ?? null;

// phpcs:ignore WordPress.Security.NonceVerification -- read-only display of an error code this site put in the URL itself.
$ssbd_error = sanitize_key( wp_unslash( $_GET['auth_error'] ?? '' ) );

$ssbd_google = ssbd_google_enabled();
?>
<section class="section auth">
	<div class="wrap">
		<div class="auth-card">
			<h1 class="auth-title">
				<?php
				echo 'register' === $ssbd_mode
					? esc_html__( 'Create your account', 'ssbd' )
					: esc_html__( 'Welcome back', 'ssbd' );
				?>
			</h1>

			<p class="auth-lede">
				<?php
				echo 'register' === $ssbd_mode
					? esc_html__( 'An account lets you comment on articles. It takes a moment and we never post anything on your behalf.', 'ssbd' )
					: esc_html__( 'Sign in to join the discussion on any article.', 'ssbd' );
				?>
			</p>

			<?php if ( $ssbd_error ) : ?>
				<?php // role="alert" so a screen reader hears the reason without hunting for it. ?>
				<p class="auth-error" role="alert"><?php echo esc_html( ssbd_account_error_message( $ssbd_error ) ); ?></p>
			<?php endif; ?>

			<?php if ( $ssbd_google ) : ?>
				<a class="btn btn--ghost auth-google" href="<?php echo esc_url( add_query_arg( array(
					'action'      => 'ssbd_google',
					'redirect_to' => rawurlencode( $ssbd_redirect ),
				), admin_url( 'admin-post.php' ) ) ); ?>">
					<?php // Google's own mark, inline: the CSP blocks off-CDN images and a PNG would be a request for 24 pixels. ?>
					<svg class="auth-google-mark" width="18" height="18" viewBox="0 0 18 18" aria-hidden="true" focusable="false">
						<path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 01-1.8 2.72v2.26h2.92c1.7-1.57 2.68-3.88 2.68-6.62z"/>
						<path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.92-2.26c-.8.54-1.84.86-3.04.86-2.34 0-4.32-1.58-5.03-3.7H.96v2.33A9 9 0 009 18z"/>
						<path fill="#FBBC05" d="M3.97 10.72a5.41 5.41 0 010-3.44V4.95H.96a9 9 0 000 8.1l3.01-2.33z"/>
						<path fill="#EA4335" d="M9 3.58c1.32 0 2.5.45 3.44 1.35l2.58-2.58C13.46.9 11.43 0 9 0A9 9 0 00.96 4.95l3.01 2.33C4.68 5.16 6.66 3.58 9 3.58z"/>
					</svg>
					<?php esc_html_e( 'Continue with Google', 'ssbd' ); ?>
				</a>

				<div class="auth-divider"><span><?php esc_html_e( 'or', 'ssbd' ); ?></span></div>
			<?php endif; ?>

			<?php if ( is_callable( $ssbd_form ) ) { $ssbd_form(); } ?>

			<p class="auth-alt">
				<?php if ( 'register' === $ssbd_mode ) : ?>
					<?php esc_html_e( 'Already have an account?', 'ssbd' ); ?>
					<a href="<?php echo esc_url( ssbd_login_url( $ssbd_redirect ) ); ?>"><?php esc_html_e( 'Sign in', 'ssbd' ); ?></a>
				<?php elseif ( ssbd_account_setting( 'allow_registration' ) ) : ?>
					<?php esc_html_e( 'No account yet?', 'ssbd' ); ?>
					<a href="<?php echo esc_url( ssbd_register_url( $ssbd_redirect ) ); ?>"><?php esc_html_e( 'Create one', 'ssbd' ); ?></a>
				<?php endif; ?>
			</p>
		</div>
	</div>
</section>
