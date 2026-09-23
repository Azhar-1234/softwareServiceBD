<?php
/**
 * Template Name: Track Order
 *
 * Two states in one page: ask for an email, or — when the URL carries a valid
 * token — list that customer's orders. See inc/order-tracking.php for why
 * there are no accounts behind this.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- the token is itself a signature.
$ssbd_token  = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
$ssbd_email  = $ssbd_token ? ssbd_tracking_email( $ssbd_token ) : '';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display state only.
$ssbd_notice = isset( $_GET['track'] ) ? sanitize_key( wp_unslash( $_GET['track'] ) ) : '';

get_template_part( 'template-parts/components/page-hero', null, array(
	'eyebrow' => __( 'Orders', 'ssbd' ),
	'heading' => get_the_title(),
	'lede'    => $ssbd_email
		? __( 'Every order placed with this email address.', 'ssbd' )
		: __( 'Enter the email address you ordered with and we will send you a link to your orders.', 'ssbd' ),
) );
?>

<section class="section" id="track">
	<div class="wrap wrap--narrow">

		<?php if ( $ssbd_email ) : ?>
			<?php $ssbd_orders = ssbd_orders_for_email( $ssbd_email ); ?>

			<?php if ( $ssbd_orders ) : ?>
				<ul class="order-list">
					<?php foreach ( $ssbd_orders as $ssbd_order ) : ?>
						<?php $ssbd_row = ssbd_order_summary( $ssbd_order ); ?>
						<li class="order-row">
							<div class="order-row-main">
								<h2 class="order-product"><?php echo esc_html( $ssbd_row['product'] ); ?></h2>
								<p class="order-meta">
									<?php
									printf(
										/* translators: %s: date the order was placed */
										esc_html__( 'Placed %s', 'ssbd' ),
										esc_html( $ssbd_row['placed'] )
									);
									?>
									<?php if ( $ssbd_row['price'] ) : ?>
										<span aria-hidden="true"> · </span><?php echo esc_html( $ssbd_row['price'] ); ?>
									<?php endif; ?>
								</p>
								<?php if ( $ssbd_row['notes'] ) : ?>
									<p class="order-notes"><?php echo esc_html( $ssbd_row['notes'] ); ?></p>
								<?php endif; ?>
							</div>

							<span class="order-status order-status--<?php echo esc_attr( $ssbd_row['status'] ); ?>">
								<?php echo esc_html( $ssbd_row['label'] ); ?>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>

				<p class="order-help">
					<?php
					printf(
						/* translators: %s: contact page link */
						esc_html__( 'Something not right? %s and we will sort it out.', 'ssbd' ),
						'<a href="' . esc_url( ssbd_page_url( 'contact' ) ) . '">' . esc_html__( 'Get in touch', 'ssbd' ) . '</a>'
					);
					?>
				</p>
			<?php else : ?>
				<p class="order-empty"><?php esc_html_e( 'No orders are recorded against this address yet.', 'ssbd' ); ?></p>
			<?php endif; ?>

		<?php else : ?>

			<?php if ( $ssbd_token ) : ?>
				<p class="form-status form-status--err" role="alert">
					<?php esc_html_e( 'That link has expired or is not valid. Request a new one below.', 'ssbd' ); ?>
				</p>
			<?php endif; ?>

			<?php if ( 'sent' === $ssbd_notice ) : ?>
				<p class="form-status form-status--ok" role="status">
					<?php esc_html_e( 'If that address has orders with us, the link is on its way. Check your inbox — and your spam folder.', 'ssbd' ); ?>
				</p>
			<?php elseif ( 'invalid' === $ssbd_notice ) : ?>
				<p class="form-status form-status--err" role="alert">
					<?php esc_html_e( 'Enter a valid email address.', 'ssbd' ); ?>
				</p>
			<?php elseif ( 'throttled' === $ssbd_notice ) : ?>
				<p class="form-status form-status--err" role="alert">
					<?php esc_html_e( 'That is a few too many requests. Please wait a few minutes and try again.', 'ssbd' ); ?>
				</p>
			<?php endif; ?>

			<form class="track-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ssbd_track">
				<?php wp_nonce_field( 'ssbd_track', 'ssbd_track_nonce' ); ?>

				<label for="track-email"><?php esc_html_e( 'Email address', 'ssbd' ); ?></label>
				<input type="email" id="track-email" name="email" autocomplete="email" required
					placeholder="<?php esc_attr_e( 'you@company.com', 'ssbd' ); ?>">

				<button type="submit" class="btn btn--primary"><?php esc_html_e( 'Email me my orders', 'ssbd' ); ?></button>
			</form>

			<p class="order-help">
				<?php esc_html_e( 'No account needed. The link we send works for 24 hours and only shows orders placed with that address.', 'ssbd' ); ?>
			</p>

		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
