<?php
/**
 * Product order form.
 *
 * A real <form> posting to admin-post.php, so it works without JavaScript —
 * there is no React island behind this one, because an order is the last place
 * to depend on a bundle loading.
 *
 * The product is carried in a hidden field and shown as a locked summary. It is
 * re-resolved server-side from that key before anything is stored, so editing
 * the field only ever produces "we could not tell which product that was".
 *
 * @package ssbd
 *
 * @var array $args['product'] Product being ordered.
 */

defined( 'ABSPATH' ) || exit;

$ssbd_product = $args['product'] ?? null;

if ( ! $ssbd_product ) {
	return;
}

$ssbd_fields = ssbd_order_fields();
$ssbd_key    = ssbd_product_key( $ssbd_product );
$ssbd_price  = ssbd_product_price( $ssbd_product );
$ssbd_status = isset( $_GET['order'] ) ? sanitize_key( wp_unslash( $_GET['order'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification -- read-only display flag.
?>
<div id="order" class="order">
	<?php if ( 'ok' === $ssbd_status ) : ?>
		<p class="form-status form-status--ok" role="status">
			<?php esc_html_e( 'Order received. We will confirm the details and payment by email within one business day.', 'ssbd' ); ?>
		</p>
	<?php elseif ( 'error' === $ssbd_status ) : ?>
		<p class="form-status form-status--err" role="alert">
			<?php esc_html_e( 'We could not place that order. Please check the form below, or email us directly.', 'ssbd' ); ?>
		</p>
	<?php endif; ?>

	<div class="order-summary">
		<span class="eyebrow eyebrow--muted"><?php esc_html_e( 'You are ordering', 'ssbd' ); ?></span>
		<h2 class="order-summary-name"><?php echo esc_html( $ssbd_product['name'] ); ?></h2>

		<?php if ( ! empty( $ssbd_product['desc'] ) ) : ?>
			<p class="order-summary-desc"><?php echo esc_html( $ssbd_product['desc'] ); ?></p>
		<?php endif; ?>

		<p class="order-summary-price">
			<?php if ( $ssbd_price ) : ?>
				<span class="order-price"><?php echo esc_html( $ssbd_price ); ?></span>
			<?php else : ?>
				<span class="order-price order-price--quote"><?php esc_html_e( 'Request a quote', 'ssbd' ); ?></span>
			<?php endif; ?>

			<?php if ( ! empty( $ssbd_product['status'] ) ) : ?>
				<span class="pill"><?php echo esc_html( $ssbd_product['status'] ); ?></span>
			<?php endif; ?>
		</p>

		<p class="order-summary-note">
			<?php esc_html_e( 'Placing an order does not charge you. We reply with the payment details and confirm before anything is due.', 'ssbd' ); ?>
		</p>
	</div>

	<form class="order-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="ssbd_order">
		<input type="hidden" name="product" value="<?php echo esc_attr( $ssbd_key ); ?>">
		<?php wp_nonce_field( 'ssbd_order', 'ssbd_order_nonce' ); ?>

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
			<input id="ssbd-order-website" type="text" name="website" tabindex="-1" autocomplete="off">
		</div>

		<div class="form-grid">
			<?php foreach ( $ssbd_fields as $ssbd_field_key => $ssbd_field ) : ?>
				<?php
				$ssbd_id   = 'ssbd-order-' . $ssbd_field_key;
				$ssbd_full = 'textarea' === $ssbd_field['type'];
				$ssbd_req  = ! empty( $ssbd_field['required'] );
				?>
				<div class="field<?php echo $ssbd_full ? ' field--full' : ''; ?>">
					<label for="<?php echo esc_attr( $ssbd_id ); ?>">
						<?php echo esc_html( $ssbd_field['label'] ); ?>
						<?php if ( $ssbd_req ) : ?><span aria-hidden="true"> *</span><?php endif; ?>
					</label>

					<?php if ( 'select' === $ssbd_field['type'] ) : ?>
						<select id="<?php echo esc_attr( $ssbd_id ); ?>" name="<?php echo esc_attr( $ssbd_field_key ); ?>"<?php echo $ssbd_req ? ' required' : ''; ?>>
							<?php foreach ( $ssbd_field['options'] as $ssbd_value => $ssbd_label ) : ?>
								<option value="<?php echo esc_attr( $ssbd_value ); ?>"><?php echo esc_html( $ssbd_label ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php elseif ( 'textarea' === $ssbd_field['type'] ) : ?>
						<textarea id="<?php echo esc_attr( $ssbd_id ); ?>" name="<?php echo esc_attr( $ssbd_field_key ); ?>" rows="4"<?php echo $ssbd_req ? ' required' : ''; ?>
							placeholder="<?php esc_attr_e( 'Deadline, hosting, customisation, anything else.', 'ssbd' ); ?>"></textarea>
					<?php else : ?>
						<input
							id="<?php echo esc_attr( $ssbd_id ); ?>"
							type="<?php echo esc_attr( $ssbd_field['type'] ); ?>"
							name="<?php echo esc_attr( $ssbd_field_key ); ?>"
							<?php echo $ssbd_req ? 'required' : ''; ?>
							autocomplete="<?php echo esc_attr( array( 'name' => 'name', 'email' => 'email', 'phone' => 'tel', 'company' => 'organization' )[ $ssbd_field_key ] ?? 'off' ); ?>">
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="order-actions">
			<button type="submit" class="btn btn--accent">
				<?php esc_html_e( 'Place order', 'ssbd' ); ?> <span aria-hidden="true">→</span>
			</button>
			<a class="btn btn--sm btn--ghost" href="<?php echo esc_url( ssbd_page_url( 'products' ) ); ?>">
				<?php esc_html_e( 'Back to products', 'ssbd' ); ?>
			</a>
		</div>

		<p class="form-note">
			<?php
			printf(
				/* translators: %s: email address */
				esc_html__( 'Prefer email? Write to %s and mention the product name.', 'ssbd' ),
				esc_html( ssbd_site( 'email' ) )
			);
			?>
		</p>
	</form>
</div>
