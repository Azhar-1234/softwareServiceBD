<?php
/**
 * The account control in the header, beside the theme toggle.
 *
 * Signed out it is a single link to the themed login page, carrying the
 * current URL so the reader comes back to the article they were on rather
 * than the home page.
 *
 * Signed in it is an avatar with a menu under it. The menu is CSS-only —
 * :focus-within plus :hover — because it holds three links and pulling in
 * JavaScript to open a list of three links would be the wrong trade. The
 * markup is a <details> so keyboard and touch both work without either.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

$ssbd_here = ssbd_current_url();

if ( ! is_user_logged_in() ) :
	?>
	<a class="account-btn" href="<?php echo esc_url( ssbd_login_url( $ssbd_here ) ); ?>" aria-label="<?php esc_attr_e( 'Sign in', 'ssbd' ); ?>">
		<span class="account-btn-icon" aria-hidden="true"><?php ssbd_icon( 'user', 18 ); ?></span>
		<span class="account-btn-label"><?php esc_html_e( 'Sign in', 'ssbd' ); ?></span>
	</a>
	<?php
	return;
endif;

$ssbd_user = wp_get_current_user();
?>
<details class="account">
	<summary class="account-btn account-btn--user" aria-label="<?php esc_attr_e( 'Your account', 'ssbd' ); ?>">
		<?php // Sized by the size argument: ssbd_local_avatar() draws a <span>, not an <img>, and ignores a class. ?>
		<?php echo get_avatar( $ssbd_user->ID, 26 ); ?>
		<span class="account-btn-label"><?php echo esc_html( $ssbd_user->display_name ); ?></span>
	</summary>

	<div class="account-menu">
		<div class="account-menu-head">
			<strong><?php echo esc_html( $ssbd_user->display_name ); ?></strong>
			<span><?php echo esc_html( $ssbd_user->user_email ); ?></span>
		</div>

		<?php
		/*
		 * A Subscriber has no wp-admin worth visiting, so the profile link is
		 * only offered to someone who can actually do something there.
		 */
		if ( current_user_can( 'edit_posts' ) ) :
			?>
			<a href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Dashboard', 'ssbd' ); ?></a>
		<?php endif; ?>

		<a href="<?php echo esc_url( get_edit_profile_url( $ssbd_user->ID ) ); ?>"><?php esc_html_e( 'Edit profile', 'ssbd' ); ?></a>
		<a href="<?php echo esc_url( wp_logout_url( $ssbd_here ) ); ?>"><?php esc_html_e( 'Sign out', 'ssbd' ); ?></a>
	</div>
</details>
