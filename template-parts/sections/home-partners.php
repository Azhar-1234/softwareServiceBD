<?php
/**
 * Partners on the front page.
 *
 * A slice of the wall, not the whole of it: the Customizer sets how many, and
 * "All partners" carries the rest. The section renders nothing at all when
 * there are no offers yet, rather than an empty heading over blank space.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

$ssbd_limit  = ssbd_front_partner_count();
$ssbd_partners = ssbd_partners( 'all', $ssbd_limit + 1 );

if ( ! $ssbd_partners ) {
	return;
}

// One more was fetched than is shown, purely to know whether "View all" has
// anything behind it — a link promising more that leads to the same six is
// worse than no link.
$ssbd_has_more = count( $ssbd_partners ) > $ssbd_limit;
$ssbd_partners   = array_slice( $ssbd_partners, 0, $ssbd_limit );
?>
<section class="section section--soft" aria-labelledby="partners-heading">
	<div class="wrap">
		<?php
		ssbd_section_head(
			ssbd_section_heading( 'partners' ),
			ssbd_section_lede( 'partners' ),
			array( 'id' => 'partners-heading' )
		);
		?>

		<div class="<?php echo esc_attr( ssbd_partner_grid_class() ); ?>">
			<?php foreach ( $ssbd_partners as $ssbd_partner ) : ?>
				<?php get_template_part( 'template-parts/components/partner-card', null, array( 'brand' => $ssbd_partner ) ); ?>
			<?php endforeach; ?>
		</div>

		<?php if ( $ssbd_has_more ) : ?>
			<div class="section-more">
				<a class="btn btn--ghost" href="<?php echo esc_url( ssbd_page_url( 'partners' ) ); ?>">
					<?php esc_html_e( 'All partners', 'ssbd' ); ?> <span aria-hidden="true">→</span>
				</a>
			</div>
		<?php endif; ?>

		<?php // Only discloses when a paid link actually rendered above. ?>
		<?php if ( ssbd_page_has_affiliate_links() ) : ?>
			<div class="partner-disclosure"><?php ssbd_affiliate_disclosure(); ?></div>
		<?php endif; ?>
	</div>
</section>
