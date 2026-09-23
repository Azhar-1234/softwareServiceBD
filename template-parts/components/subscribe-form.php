<?php
/**
 * Newsletter sign-up.
 *
 * A plain form posting to admin-post, so it works with JavaScript off. The
 * result comes back as a ?subscribe= flag and is announced above the field.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignore WordPress.Security.NonceVerification -- read-only display flag.
$ssbd_state = isset( $_GET['subscribe'] ) ? sanitize_key( wp_unslash( $_GET['subscribe'] ) ) : '';

$ssbd_messages = array(
	'ok'      => __( 'Thanks — you are on the list.', 'ssbd' ),
	'invalid' => __( 'That email address did not look right. Try again?', 'ssbd' ),
	'error'   => __( 'We could not save that. Please try again in a moment.', 'ssbd' ),
);
?>
<div class="subscribe" id="subscribe">
	<?php if ( isset( $ssbd_messages[ $ssbd_state ] ) ) : ?>
		<p class="form-status <?php echo 'ok' === $ssbd_state ? 'form-status--ok' : 'form-status--err'; ?>" role="status">
			<?php echo esc_html( $ssbd_messages[ $ssbd_state ] ); ?>
		</p>
	<?php endif; ?>

	<form class="subscribe-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="ssbd_subscribe">
		<input type="hidden" name="source" value="<?php echo esc_url( ssbd_canonical_url() ); ?>">
		<?php wp_nonce_field( 'ssbd_subscribe', 'ssbd_subscribe_nonce' ); ?>

		<?php
		/*
		 * Honeypot. Hidden from people, irresistible to bots.
		 *
		 * Deliberately unlabelled. The wrapper is aria-hidden and the input is
		 * out of the tab order, so no assistive tech ever reached the label —
		 * but its text still sat in the page's text content, where extractors,
		 * AI crawlers and anyone reading the rendered text picked it up.
		 */
		?>
		<div aria-hidden="true" style="position:absolute;left:-9999px" tabindex="-1">
			<input id="ssbd-sub-website" type="text" name="website" tabindex="-1" autocomplete="off">
		</div>

		<label class="screen-reader-text" for="ssbd-subscribe-email"><?php esc_html_e( 'Email address', 'ssbd' ); ?></label>
		<input
			id="ssbd-subscribe-email"
			type="email"
			name="email"
			required
			autocomplete="email"
			placeholder="<?php esc_attr_e( 'you@company.com', 'ssbd' ); ?>">

		<button type="submit" class="btn btn--accent btn--sm"><?php esc_html_e( 'Subscribe', 'ssbd' ); ?></button>
	</form>

	<p class="subscribe-note">
		<?php esc_html_e( 'One email a month. No spam, and one click to unsubscribe.', 'ssbd' ); ?>
	</p>
</div>
