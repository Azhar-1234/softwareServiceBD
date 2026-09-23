<?php
/**
 * Template Name: Order
 *
 * Reached as /order/?product=<key> from the Order buttons on the Products
 * page. Without a resolvable product it degrades into a product picker rather
 * than an error, so the page is never a dead end.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

// phpcs:ignore WordPress.Security.NonceVerification -- read-only product selector.
$ssbd_key     = isset( $_GET['product'] ) ? sanitize_title( wp_unslash( $_GET['product'] ) ) : '';
$ssbd_product = $ssbd_key ? ssbd_product_by_key( $ssbd_key ) : null;

get_template_part( 'template-parts/components/page-hero', null, array(
	'eyebrow' => __( 'Order', 'ssbd' ),
	'heading' => $ssbd_product
		? sprintf(
			/* translators: %s: product name */
			__( 'Order %s', 'ssbd' ),
			$ssbd_product['name']
		)
		: __( 'Order a product', 'ssbd' ),
) );
?>

<section class="section section--tight">
	<div class="wrap wrap--narrow">
		<?php if ( $ssbd_product ) : ?>
			<?php get_template_part( 'template-parts/components/order-form', null, array( 'product' => $ssbd_product ) ); ?>
		<?php else : ?>
			<?php
			$ssbd_all = ssbd_products();

			if ( $ssbd_key ) :
				?>
				<p class="form-status form-status--err" role="alert">
					<?php esc_html_e( 'That product is no longer listed. Pick one below.', 'ssbd' ); ?>
				</p>
			<?php endif; ?>

			<p class="section-note" style="margin-top:0">
				<?php esc_html_e( 'Choose what you would like to order.', 'ssbd' ); ?>
			</p>

			<div class="order-picker">
				<?php foreach ( $ssbd_all as $ssbd_item ) : ?>
					<?php $ssbd_price = ssbd_product_price( $ssbd_item ); ?>
					<a class="order-picker-item" href="<?php echo esc_url( ssbd_order_url( $ssbd_item ) ); ?>">
						<span class="order-picker-name"><?php echo esc_html( $ssbd_item['name'] ); ?></span>
						<span class="order-picker-price">
							<?php echo esc_html( $ssbd_price ? $ssbd_price : __( 'Request a quote', 'ssbd' ) ); ?>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
get_template_part( 'template-parts/components/faq', null, array( 'faqs' => ssbd_faqs( 'products' ) ) );

get_footer();
