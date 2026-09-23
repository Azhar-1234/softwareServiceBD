<?php
/**
 * Comparison table built from the offers' feature rows.
 *
 * A real <table> with proper scopes: answer engines and screen readers both
 * parse tabular data far more reliably than a grid of divs.
 *
 * @package ssbd
 *
 * @var array  $args['offers']
 * @var string $args['category'] Offer category, for the canonical row order.
 * @var string $args['caption']  Visible line above the table saying what it compares.
 */

defined( 'ABSPATH' ) || exit;

$ssbd_offers   = $args['offers'] ?? array();
$ssbd_category = $args['category'] ?? '';
$ssbd_caption  = $args['caption'] ?? '';

if ( ! $ssbd_offers ) {
	return;
}

/*
 * The price has a field of its own on every offer, so a "Starting price"
 * feature row would print the same figure twice. The dedicated field wins and
 * the duplicate label is dropped wherever it appears.
 */
$ssbd_price_label = 'starting price';

/*
 * Row order comes from the canonical list for the category, then anything an
 * offer carries that the list did not name.
 *
 * A canonical row survives only if at least one provider has a value for it.
 * The partly-filled ones are worth showing — a dash there means "we have not
 * checked this yet" beside providers where we have, which is information. A
 * row where nobody has a value is fourteen dashes saying nothing, and a table
 * more than half empty reads as broken rather than as honest. Each one
 * reappears on its own the moment a value is entered in wp-admin.
 */
$ssbd_has_value = static function ( $label ) use ( $ssbd_offers ) {
	foreach ( $ssbd_offers as $offer ) {
		if ( '' !== trim( (string) ( $offer['features'][ $label ] ?? '' ) ) ) {
			return true;
		}
	}

	return false;
};

$ssbd_rows = array();

foreach ( ssbd_offer_table_rows( $ssbd_category ) as $ssbd_label ) {
	if ( strtolower( $ssbd_label ) !== $ssbd_price_label && $ssbd_has_value( $ssbd_label ) ) {
		$ssbd_rows[ $ssbd_label ] = true;
	}
}

foreach ( $ssbd_offers as $ssbd_offer ) {
	foreach ( array_keys( $ssbd_offer['features'] ) as $ssbd_label ) {
		if ( strtolower( $ssbd_label ) !== $ssbd_price_label ) {
			$ssbd_rows[ $ssbd_label ] = true;
		}
	}
}

$ssbd_rows = array_keys( $ssbd_rows );
?>

<?php if ( $ssbd_caption ) : ?>
	<p class="table-intro"><?php echo esc_html( $ssbd_caption ); ?></p>
<?php endif; ?>
<div class="table-scroll">
	<table class="compare-table">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Feature', 'ssbd' ); ?></th>
				<?php foreach ( $ssbd_offers as $ssbd_offer ) : ?>
					<th scope="col"><?php echo esc_html( $ssbd_offer['name'] ); ?></th>
				<?php endforeach; ?>
			</tr>
		</thead>

		<tbody>
			<tr>
				<th scope="row"><?php esc_html_e( 'Starting price', 'ssbd' ); ?></th>
				<?php foreach ( $ssbd_offers as $ssbd_offer ) : ?>
					<td><?php echo esc_html( $ssbd_offer['price'] ?: '—' ); ?></td>
				<?php endforeach; ?>
			</tr>

			<?php if ( $ssbd_rows ) : ?>
				<?php foreach ( $ssbd_rows as $ssbd_label ) : ?>
					<tr>
						<th scope="row"><?php echo esc_html( $ssbd_label ); ?></th>
						<?php foreach ( $ssbd_offers as $ssbd_offer ) : ?>
							<?php // An empty stored value counts as unverified, same as a missing one. ?>
							<td><?php echo esc_html( trim( (string) ( $ssbd_offer['features'][ $ssbd_label ] ?? '' ) ) ?: '—' ); ?></td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>

			<tr>
				<th scope="row"><?php esc_html_e( 'Go', 'ssbd' ); ?></th>
				<?php foreach ( $ssbd_offers as $ssbd_offer ) : ?>
					<td><?php ssbd_offer_button( $ssbd_offer, 'btn btn--sm btn--accent' ); ?></td>
				<?php endforeach; ?>
			</tr>
		</tbody>
	</table>
</div>

<?php if ( ssbd_page_has_affiliate_links() ) : ?>
	<?php ssbd_affiliate_disclosure( true ); ?>
<?php endif; ?>
