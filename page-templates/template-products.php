<?php
/**
 * Template Name: Products
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ssbd_products = ssbd_data( 'products' );
$ssbd_items    = ssbd_products();
$ssbd_filters  = ssbd_product_filters();

// The category the header's Products dropdown linked to. Validated against the
// real filter list, so an invented value opens the page on "All" rather than
// on an empty grid.
// phpcs:ignore WordPress.Security.NonceVerification -- read-only view filter.
$ssbd_active = isset( $_GET['category'] ) ? sanitize_title( wp_unslash( $_GET['category'] ) ) : '';

if ( $ssbd_active && ! in_array( $ssbd_active, wp_list_pluck( $ssbd_filters, 'key' ), true ) ) {
	$ssbd_active = '';
}

// SoftwareApplication nodes. Products in development carry no Offer — an
// engine should not be told there is a price when there is not one yet.
ssbd_schema_add( array(
	'@type'           => 'ItemList',
	'@id'             => ssbd_canonical_url() . '#products',
	'name'            => __( 'Software Service BD products', 'ssbd' ),
	'itemListElement' => array_map(
		static function ( $product, $i ) {
			return array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'item'     => array(
					'@type'               => 'SoftwareApplication',
					'name'                => $product['name'],
					'description'         => $product['desc'],
					'applicationCategory' => 'BusinessApplication',
					'applicationSubCategory' => $product['category'],
					'author'              => array( '@id' => ssbd_schema_id( 'organization' ) ),
					'creativeWorkStatus'  => $product['status'],
				),
			);
		},
		$ssbd_items,
		array_keys( $ssbd_items )
	),
) );

get_template_part( 'template-parts/components/page-hero', null, array(
	'eyebrow'    => __( 'Products', 'ssbd' ),
	'heading'    => __( 'Products we are building', 'ssbd' ),
	'answer_key' => 'products',
) );
?>

<section class="section section--tight">
	<div class="wrap">
		<div class="stats">
			<?php foreach ( $ssbd_products['stats'] as $ssbd_stat ) : ?>
				<div class="stat">
					<div class="stat-value"><?php echo esc_html( $ssbd_stat['value'] ); ?></div>
					<div class="stat-label"><?php echo esc_html( $ssbd_stat['label'] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section" aria-labelledby="product-list-heading">
	<div class="wrap">
		<?php ssbd_section_head( __( 'The catalogue', 'ssbd' ), '', array( 'id' => 'product-list-heading' ) ); ?>

		<?php
		ssbd_island(
			'filter-grid',
			array(
				'tabs'      => $ssbd_filters,
				'attribute' => 'data-group',
				// A product can be in several categories, so the card carries a
				// space-separated list and the tab matches any one of them —
				// the same mode the Portfolio grid uses.
				'match'     => 'contains',
				'initial'   => $ssbd_active,
			),
			function () use ( $ssbd_filters, $ssbd_items, $ssbd_active ) {
				?>
				<div class="filter-tabs" role="group" aria-label="<?php esc_attr_e( 'Filter products', 'ssbd' ); ?>">
					<?php foreach ( $ssbd_filters as $tab ) : ?>
						<?php $ssbd_on = $ssbd_active ? $tab['key'] === $ssbd_active : 'all' === $tab['key']; ?>
						<button type="button" class="filter-tab" data-filter="<?php echo esc_attr( $tab['key'] ); ?>" aria-pressed="<?php echo $ssbd_on ? 'true' : 'false'; ?>">
							<?php echo esc_html( $tab['label'] ); ?>
						</button>
					<?php endforeach; ?>
				</div>

				<div class="grid grid--3" data-filter-target>
					<?php foreach ( $ssbd_items as $product ) : ?>
						<?php
						// Every category the product is in, so filtering by a
						// parent category still matches a product filed under
						// one of its sub-categories.
						$ssbd_groups = $product['filters'] ?? array();
						if ( ! empty( $product['filter'] ) ) {
							$ssbd_groups[] = $product['filter'];
						}

						$ssbd_price = ssbd_product_price( $product );
						?>
						<article class="card" data-group="<?php echo esc_attr( implode( ' ', array_unique( $ssbd_groups ) ) ); ?>">
							<span class="pill" style="align-self:flex-start"><?php echo esc_html( $product['category'] ); ?></span>
							<h3><?php echo esc_html( $product['name'] ); ?></h3>
							<p><?php echo esc_html( $product['desc'] ); ?></p>
							<span class="num"><?php echo esc_html( $product['status'] ); ?></span>

							<p class="product-price">
								<?php if ( $ssbd_price ) : ?>
									<span class="order-price"><?php echo esc_html( $ssbd_price ); ?></span>
								<?php else : ?>
									<span class="order-price order-price--quote"><?php esc_html_e( 'Request a quote', 'ssbd' ); ?></span>
								<?php endif; ?>
							</p>

							<div class="product-actions">
								<a class="btn btn--sm btn--accent" href="<?php echo esc_url( ssbd_order_url( $product ) ); ?>">
									<?php esc_html_e( 'Order now', 'ssbd' ); ?>
								</a>
								<a class="link-cta" href="<?php echo esc_url( ssbd_page_url( 'contact' ) ); ?>">
									<?php esc_html_e( 'Ask a question', 'ssbd' ); ?> <span class="arrow" aria-hidden="true">→</span>
								</a>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
				<?php
			}
		);
		?>
	</div>
</section>

<section class="section section--soft" aria-labelledby="benefits-heading">
	<div class="wrap">
		<?php ssbd_section_head( __( 'What our products have in common', 'ssbd' ), '', array( 'id' => 'benefits-heading' ) ); ?>
		<div class="grid grid--4">
			<?php foreach ( $ssbd_products['benefits'] as $ssbd_benefit ) : ?>
				<article class="card card--raised">
					<h3 style="font-size:16px"><?php echo esc_html( $ssbd_benefit['title'] ); ?></h3>
					<p><?php echo esc_html( $ssbd_benefit['desc'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section" aria-labelledby="how-heading">
	<div class="wrap">
		<?php ssbd_section_head( __( 'How to get started', 'ssbd' ), '', array( 'id' => 'how-heading' ) ); ?>
		<div class="process">
			<?php foreach ( $ssbd_products['how_steps'] as $ssbd_step ) : ?>
				<div class="process-step">
					<span class="num"><?php echo esc_html( $ssbd_step['no'] ); ?></span>
					<h3><?php echo esc_html( $ssbd_step['title'] ); ?></h3>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
get_template_part( 'template-parts/components/faq', null, array( 'faqs' => ssbd_faqs( 'products' ) ) );
get_template_part( 'template-parts/components/cta', null, array(
	'heading' => __( 'Need something these products do not do?', 'ssbd' ),
	'text'    => __( 'We build custom versions and bespoke business software. Tell us what is missing.', 'ssbd' ),
	'button'  => __( 'Request a custom build', 'ssbd' ),
) );

get_footer();
