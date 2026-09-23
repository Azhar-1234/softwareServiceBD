<?php
/**
 * Project inquiry form.
 *
 * A real <form> posting to admin-post.php, so it works without JavaScript.
 * The React island intercepts submit and posts to the REST endpoint instead,
 * adding inline validation and a success state without a page reload.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

$ssbd_fields = ssbd_inquiry_fields();
$ssbd_status = isset( $_GET['inquiry'] ) ? sanitize_key( wp_unslash( $_GET['inquiry'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification -- read-only display flag.
?>
<div id="inquiry">
		<?php if ( 'sent' === $ssbd_status ) : ?>
			<p class="form-status form-status--ok" role="status"><?php esc_html_e( 'Email verified and inquiry sent. We will reply within one business day.', 'ssbd' ); ?></p>
		<?php elseif ( 'expired' === $ssbd_status ) : ?>
			<p class="form-status form-status--err" role="alert"><?php esc_html_e( 'That verification link is invalid or expired. Please submit the form again.', 'ssbd' ); ?></p>
	<?php elseif ( 'error' === $ssbd_status ) : ?>
		<p class="form-status form-status--err" role="alert"><?php esc_html_e( 'We could not send that. Please check the form or email us directly.', 'ssbd' ); ?></p>
	<?php endif; ?>

	<?php
	ssbd_island( 'inquiry-form', array(), function () use ( $ssbd_fields ) {
		?>
		<form class="inquiry-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
			<input type="hidden" name="action" value="ssbd_inquiry">
			<input type="hidden" name="source" value="<?php echo esc_url( ssbd_canonical_url() ); ?>">
			<?php wp_nonce_field( 'ssbd_inquiry', 'ssbd_inquiry_nonce' ); ?>

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
			<div class="field" aria-hidden="true" style="position:absolute;left:-9999px" tabindex="-1">
				<input id="ssbd-website" type="text" name="website" tabindex="-1" autocomplete="off">
			</div>

			<div class="form-grid">
				<?php foreach ( $ssbd_fields as $key => $field ) : ?>
					<?php
					$ssbd_id       = 'ssbd-' . $key;
					$ssbd_is_full  = in_array( $field['type'], array( 'textarea' ), true ) || 'service' === $key;
					$ssbd_required = ! empty( $field['required'] );
					?>
					<div class="field<?php echo $ssbd_is_full ? ' field--full' : ''; ?>">
						<label for="<?php echo esc_attr( $ssbd_id ); ?>">
							<?php echo esc_html( $field['label'] ); ?>
							<?php if ( $ssbd_required ) : ?><span aria-hidden="true"> *</span><?php endif; ?>
						</label>

						<?php if ( 'select' === $field['type'] ) : ?>
							<select id="<?php echo esc_attr( $ssbd_id ); ?>" name="<?php echo esc_attr( $key ); ?>"<?php echo $ssbd_required ? ' required' : ''; ?>>
								<?php foreach ( $field['options'] as $value => $label ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						<?php elseif ( 'textarea' === $field['type'] ) : ?>
							<textarea id="<?php echo esc_attr( $ssbd_id ); ?>" name="<?php echo esc_attr( $key ); ?>" rows="6"<?php echo $ssbd_required ? ' required' : ''; ?> placeholder="<?php esc_attr_e( 'What are you trying to build, automate or fix?', 'ssbd' ); ?>"></textarea>
						<?php else : ?>
							<input
								id="<?php echo esc_attr( $ssbd_id ); ?>"
								type="<?php echo esc_attr( $field['type'] ); ?>"
								name="<?php echo esc_attr( $key ); ?>"
								<?php echo $ssbd_required ? 'required' : ''; ?>
								autocomplete="<?php echo esc_attr( array( 'name' => 'name', 'email' => 'email', 'phone' => 'tel', 'company' => 'organization' )[ $key ] ?? 'off' ); ?>">
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div style="margin-top:20px">
				<button type="submit" class="btn btn--accent"><?php esc_html_e( 'Send Project Inquiry', 'ssbd' ); ?> <span aria-hidden="true">→</span></button>
			</div>

			<p class="form-note">
				<?php
				printf(
					/* translators: %s: privacy policy link */
						esc_html__( 'We will email you a confirmation link before delivering this inquiry. See our %s.', 'ssbd' ),
					'<a href="' . esc_url( ssbd_page_url( 'privacy-policy' ) ) . '">' . esc_html__( 'privacy policy', 'ssbd' ) . '</a>'
				);
				?>
			</p>
		</form>
		<?php
	} );
	?>
</div>
