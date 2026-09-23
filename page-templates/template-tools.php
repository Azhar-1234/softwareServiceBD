<?php
/**
 * Template Name: Tools
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ssbd_tools = ssbd_data( 'tools' );

// Real published offers win; the shipped groups are the placeholder until
// the first partner programme is approved and its offer published.
$ssbd_groups = ssbd_offer_groups();
$ssbd_live   = (bool) $ssbd_groups;

get_template_part( 'template-parts/components/page-hero', null, array(
	'eyebrow'    => __( 'Tools', 'ssbd' ),
	'heading'    => __( 'The stack we actually run', 'ssbd' ),
	'answer_key' => 'tools',
) );
?>

<section class="section" aria-labelledby="stack-heading">
	<div class="wrap">
		<h2 id="stack-heading" class="screen-reader-text"><?php esc_html_e( 'Recommended tools', 'ssbd' ); ?></h2>

		<div class="grid grid--3">
			<?php foreach ( ( $ssbd_live ? $ssbd_groups : $ssbd_tools['groups'] ) as $ssbd_group ) : ?>
				<section class="card card--raised">
					<div style="display:flex;align-items:baseline;justify-content:space-between;gap:12px">
						<h3><?php echo esc_html( $ssbd_group['title'] ); ?></h3>
						<span class="num"><?php echo esc_html( $ssbd_group['count'] ); ?></span>
					</div>

					<ul class="tool-list">
						<?php foreach ( $ssbd_group['items'] as $ssbd_item ) : ?>
							<li>
								<strong><?php echo esc_html( $ssbd_item['name'] ); ?></strong>
								<span><?php echo esc_html( $ssbd_live ? ( $ssbd_item['summary'] ?: $ssbd_item['best_for'] ) : $ssbd_item['desc'] ); ?></span>

								<?php if ( $ssbd_live && ! empty( $ssbd_item['link'] ) ) : ?>
									<span class="tool-meta">
										<?php if ( $ssbd_item['price'] ) : ?>
											<span class="offer-price" style="font-size:13px"><?php echo esc_html( $ssbd_item['price'] ); ?></span>
										<?php endif; ?>
										<?php ssbd_offer_button( $ssbd_item, 'link-cta' ); ?>
									</span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endforeach; ?>
		</div>

		<?php // Only disclose if a paid link actually rendered above — an offer
		// without a tracking URL is a plain recommendation, and disclosing a
		// commission that does not exist is its own kind of dishonest. ?>
		<?php if ( ssbd_page_has_affiliate_links() ) : ?>
			<div style="margin-top:32px"><?php ssbd_affiliate_disclosure(); ?></div>
		<?php endif; ?>
	</div>
</section>

<section class="section section--soft" aria-labelledby="quick-heading">
	<div class="wrap">
		<?php ssbd_section_head( __( 'Quick comparisons', 'ssbd' ), '', array( 'id' => 'quick-heading' ) ); ?>
		<div class="filter-tabs">
			<?php foreach ( $ssbd_tools['quick_comparisons'] as $ssbd_comparison ) : ?>
				<a class="filter-tab" style="display:grid;place-items:center;text-decoration:none" href="<?php echo esc_url( ssbd_page_url( 'comparisons' ) ); ?>">
					<?php echo esc_html( $ssbd_comparison ); ?>
				</a>
			<?php endforeach; ?>
		</div>

		<?php
		// Carried over from the design source, where it was unconditional. It
		// is now driven by whether a paid link actually rendered, so the page
		// never claims a commission relationship it does not have.
		if ( ssbd_page_has_affiliate_links() ) :
			?>
			<p style="margin-top:28px;text-align:center;font-size:13px;color:var(--muted)">
				<?php
				printf(
					/* translators: %s: affiliate disclosure link */
					esc_html__( 'Some links on this page are affiliate links. Read our %s.', 'ssbd' ),
					'<a href="' . esc_url( ssbd_page_url( 'affiliate-disclosure' ) ) . '">' . esc_html__( 'affiliate disclosure', 'ssbd' ) . '</a>'
				);
				?>
			</p>
		<?php endif; ?>
	</div>
</section>

<?php
get_template_part( 'template-parts/components/cta' );

get_footer();
