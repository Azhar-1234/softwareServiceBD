<?php
/**
 * Template Name: Partners
 *
 * The full partner wall, behind "All partners" on the front page. Filter tabs come
 * from the offer categories that have offers in them, and are dropped entirely
 * when there is only one category to choose from.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ssbd_partners  = ssbd_partners();
$ssbd_filters = ssbd_partner_filters();

get_template_part( 'template-parts/components/page-hero', null, array(
	'eyebrow' => __( 'Partners', 'ssbd' ),
	'heading' => __( 'Our Partners', 'ssbd' ),
) );
?>

<section class="section section--tight" aria-labelledby="partner-list-heading">
	<div class="wrap">
		<?php
		ssbd_section_head(
			__( 'Every partner', 'ssbd' ),
			__( 'The tools and platforms we build on, host with and recommend — with the discount codes our partners give our readers, where there is one.', 'ssbd' ),
			array( 'id' => 'partner-list-heading' )
		);
		?>

		<?php if ( ! $ssbd_partners ) : ?>
			<p class="section-note"><?php esc_html_e( 'Partners are being added. Check back shortly.', 'ssbd' ); ?></p>
		<?php elseif ( $ssbd_filters ) : ?>
			<?php
			ssbd_island(
				'filter-grid',
				array(
					'tabs'      => $ssbd_filters,
					'attribute' => 'data-group',
					// A brand can sit in several categories, so the card carries
					// a space-separated list and a tab matches any one of them.
					'match'     => 'contains',
				),
				function () use ( $ssbd_filters, $ssbd_partners ) {
					?>
					<div class="filter-tabs" role="group" aria-label="<?php esc_attr_e( 'Filter partners', 'ssbd' ); ?>">
						<?php foreach ( $ssbd_filters as $tab ) : ?>
							<button type="button" class="filter-tab" data-filter="<?php echo esc_attr( $tab['key'] ); ?>" aria-pressed="<?php echo 'all' === $tab['key'] ? 'true' : 'false'; ?>">
								<?php echo esc_html( $tab['label'] ); ?>
							</button>
						<?php endforeach; ?>
					</div>

					<div class="<?php echo esc_attr( ssbd_partner_grid_class() ); ?>" data-filter-target>
						<?php foreach ( $ssbd_partners as $ssbd_partner ) : ?>
							<?php get_template_part( 'template-parts/components/partner-card', null, array( 'brand' => $ssbd_partner ) ); ?>
						<?php endforeach; ?>
					</div>
					<?php
				}
			);
			?>
		<?php else : ?>
			<div class="<?php echo esc_attr( ssbd_partner_grid_class() ); ?>">
				<?php foreach ( $ssbd_partners as $ssbd_partner ) : ?>
					<?php get_template_part( 'template-parts/components/partner-card', null, array( 'brand' => $ssbd_partner ) ); ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( ssbd_page_has_affiliate_links() ) : ?>
			<div class="partner-disclosure"><?php ssbd_affiliate_disclosure(); ?></div>
		<?php endif; ?>
	</div>
</section>

<?php
get_template_part( 'template-parts/components/cta', null, array(
	'heading' => __( 'Want to partner with us?', 'ssbd' ),
	'text'    => __( 'If you run a tool our audience builds on, tell us about it. We only list what we would use ourselves.', 'ssbd' ),
	'button'  => __( 'Get in touch', 'ssbd' ),
) );

get_footer();
