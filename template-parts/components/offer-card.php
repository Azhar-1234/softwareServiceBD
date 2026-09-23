<?php
/**
 * A single affiliate offer card.
 *
 * Laid out as a recommendation card, not a feature list: who it is for, what
 * it costs, the verdict, the trade-off, then one button. The price block is
 * the anchor — it is what a reader on a comparison page is looking for — and
 * the button below it is the affiliate link.
 *
 * An offer with no tracking URL renders without a button, because a
 * recommendation is honest whether or not money is involved. An editor sees a
 * prompt to add the link in its place; visitors see nothing there.
 *
 * @package ssbd
 *
 * @var array  $args['offer']
 * @var string $args['style'] 'card' (default) or 'inline'.
 */

defined( 'ABSPATH' ) || exit;

$ssbd_offer = $args['offer'] ?? array();

if ( empty( $ssbd_offer['name'] ) ) {
	return;
}

$ssbd_inline = 'inline' === ( $args['style'] ?? 'card' );

// Three of each is a summary; the full list belongs in the review itself.
$ssbd_pros = array_slice( $ssbd_offer['pros'], 0, 3 );
$ssbd_cons = array_slice( $ssbd_offer['cons'], 0, 3 );
?>
<div class="card offer-card<?php echo $ssbd_inline ? ' offer-card--inline' : ''; ?>">

	<div class="offer-head">
		<?php if ( $ssbd_offer['logo'] ) : ?>
			<img class="offer-logo" src="<?php echo esc_url( $ssbd_offer['logo'] ); ?>" alt="<?php echo esc_attr( $ssbd_offer['name'] ); ?>" loading="lazy" decoding="async">
		<?php else : ?>
			<span class="offer-logo offer-logo--letter" aria-hidden="true"><?php echo esc_html( mb_substr( $ssbd_offer['name'], 0, 1 ) ); ?></span>
		<?php endif; ?>

		<div class="offer-title">
			<h3><?php echo esc_html( $ssbd_offer['name'] ); ?></h3>
			<?php if ( $ssbd_offer['best_for'] ) : ?>
				<span class="offer-bestfor">
					<?php
					printf(
						/* translators: %s: use case */
						esc_html__( 'Best for %s', 'ssbd' ),
						esc_html( $ssbd_offer['best_for'] )
					);
					?>
				</span>
			<?php endif; ?>
		</div>

		<?php
		/*
		 * The star rating is deliberately not rendered.
		 *
		 * The scores in the seed data were numbers with no derivation behind
		 * them — nothing recorded what was measured, how it was weighted, or
		 * when. A five-point score presented without a methodology is a claim
		 * the site cannot support, it is exactly what Google's reviews
		 * guidance treats as unsubstantiated, and on an affiliate page it is
		 * the specific thing that makes a recommendation look bought.
		 *
		 * What survives is what can be defended: who the provider suits, and
		 * a named list of pros and cons. The _ssbd_rating meta is left intact
		 * so nothing is lost — define a rubric, publish it beside the cards,
		 * and this block can come back.
		 */
		?>
	</div>

	<?php if ( $ssbd_offer['price'] || $ssbd_offer['coupon'] ) : ?>
		<div class="offer-pricing">
			<?php if ( $ssbd_offer['price'] ) : ?>
				<span class="offer-price"><?php echo esc_html( $ssbd_offer['price'] ); ?></span>
			<?php endif; ?>

			<?php if ( $ssbd_offer['coupon'] ) : ?>
				<span class="offer-coupon">
					<?php esc_html_e( 'Code', 'ssbd' ); ?>
					<code><?php echo esc_html( $ssbd_offer['coupon'] ); ?></code>
				</span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( $ssbd_offer['summary'] ) : ?>
		<p class="offer-summary"><?php echo esc_html( $ssbd_offer['summary'] ); ?></p>
	<?php endif; ?>

	<?php if ( $ssbd_pros || $ssbd_cons ) : ?>
		<div class="offer-proscons">
			<?php if ( $ssbd_pros ) : ?>
				<div>
					<h4><?php esc_html_e( 'Pros', 'ssbd' ); ?></h4>
					<ul class="offer-pros">
						<?php foreach ( $ssbd_pros as $ssbd_pro ) : ?>
							<li><?php echo esc_html( $ssbd_pro ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( $ssbd_cons ) : ?>
				<div>
					<h4><?php esc_html_e( 'Cons', 'ssbd' ); ?></h4>
					<ul class="offer-cons">
						<?php foreach ( $ssbd_cons as $ssbd_con ) : ?>
							<li><?php echo esc_html( $ssbd_con ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="offer-foot">
		<?php if ( $ssbd_offer['link'] ) : ?>
			<?php ssbd_offer_button( $ssbd_offer, 'btn btn--accent offer-cta' ); ?>

			<?php if ( $ssbd_offer['is_affiliate'] ) : ?>
				<span class="offer-note"><?php esc_html_e( 'Affiliate link — we may earn a commission at no cost to you.', 'ssbd' ); ?></span>
			<?php endif; ?>

		<?php elseif ( current_user_can( 'edit_posts' ) && ! empty( $ssbd_offer['id'] ) ) : ?>
			<?php // Only an editor sees this: the card is complete for visitors either way. ?>
			<a class="btn btn--ghost offer-cta offer-cta--empty" href="<?php echo esc_url( get_edit_post_link( $ssbd_offer['id'] ) ); ?>">
				<?php esc_html_e( 'Add your affiliate link', 'ssbd' ); ?>
			</a>
			<span class="offer-note"><?php esc_html_e( 'Only you can see this. Paste the tracking URL and the button goes live.', 'ssbd' ); ?></span>
		<?php endif; ?>
	</div>
</div>
