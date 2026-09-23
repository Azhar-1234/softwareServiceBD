<?php
/**
 * A single partner card.
 *
 * Logo, name, coupon, one button — in that order, and nothing else. A partner
 * wall answers "who do you work with, and is there a discount"; the full
 * verdict, price and pros/cons belong to offer-card.php on a comparison page.
 *
 * The coupon and the button are both optional and simply absent when unset, so
 * a partner with neither still sits square in the grid rather than leaving a
 * hole in it.
 *
 * @package ssbd
 *
 * @var array $args['partner'] Offer data from ssbd_offer_data().
 */

defined( 'ABSPATH' ) || exit;

$ssbd_partner = $args['partner'] ?? array();

if ( empty( $ssbd_partner['name'] ) ) {
	return;
}

$ssbd_coupon = ssbd_partner_coupons_shown() ? trim( (string) ( $ssbd_partner['coupon'] ?? '' ) ) : '';
?>
<article class="partner-card" data-group="<?php echo esc_attr( implode( ' ', (array) ( $ssbd_partner['categories'] ?? array() ) ) ); ?>">

	<?php // The tile stays light in both themes on purpose: a partner's logo is
	// drawn for their own background, usually white, so a dark wordmark dropped
	// straight onto our dark surface would be invisible. ?>
	<div class="partner-logo">
		<?php if ( $ssbd_partner['logo'] ) : ?>
			<img src="<?php echo esc_url( $ssbd_partner['logo'] ); ?>"
				alt="<?php echo esc_attr( $ssbd_partner['name'] ); ?>"
				loading="lazy" decoding="async">
		<?php else : ?>
			<span class="partner-logo-letter" aria-hidden="true"><?php echo esc_html( mb_substr( $ssbd_partner['name'], 0, 1 ) ); ?></span>
		<?php endif; ?>
	</div>

	<h3 class="partner-name"><?php echo esc_html( $ssbd_partner['name'] ); ?></h3>

	<?php if ( $ssbd_coupon ) : ?>
		<div class="partner-coupon">
			<span class="partner-coupon-label"><?php esc_html_e( 'Coupon code', 'ssbd' ); ?></span>

			<span class="partner-coupon-row">
				<code class="partner-coupon-code" data-partner-coupon><?php echo esc_html( $ssbd_coupon ); ?></code>

				<?php
				// Revealed by assets/js/partners.js. Without scripting the code
				// is still on screen to read and select — a copy button that
				// cannot copy is worse than none.
				?>
				<button type="button" class="partner-coupon-copy"
					data-partner-copy="<?php echo esc_attr( $ssbd_coupon ); ?>"
					data-copied="<?php esc_attr_e( 'Copied', 'ssbd' ); ?>"
					aria-label="<?php
						/* translators: %s: coupon code */
						printf( esc_attr__( 'Copy coupon code %s', 'ssbd' ), esc_attr( $ssbd_coupon ) );
					?>" hidden>
					<span data-partner-copy-label aria-live="polite"><?php esc_html_e( 'Copy', 'ssbd' ); ?></span>
				</button>
			</span>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $ssbd_partner['link'] ) ) : ?>
		<div class="partner-action">
			<?php ssbd_offer_button( $ssbd_partner, 'btn btn--sm btn--accent partner-btn' ); ?>
		</div>
	<?php endif; ?>
</article>
