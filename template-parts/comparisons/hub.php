<?php
/**
 * The comparisons hub: all three pillars and every matchup under them.
 *
 * Rendered by the Comparisons page template when no pillar segment is in the
 * URL. Each pillar also has a page of its own — pillar.php beside this file.
 *
 * @package ssbd
 *
 * @var array $args['comparisons']
 */

defined( 'ABSPATH' ) || exit;

$ssbd_comparisons = $args['comparisons'];
$ssbd_pillars     = $ssbd_comparisons['pillars'];
$ssbd_facets      = $ssbd_comparisons['facets'];
$ssbd_published   = ssbd_matchups_published();

// Six across three pillars: enough to show the direction, short enough to
// read as a plan rather than as a backlog of things that were promised.
$ssbd_roadmap     = ssbd_matchups_roadmap( 'all', 6 );

// The side-by-side table is built from real offers once any are published.
$ssbd_compare_cat = ssbd_largest_offer_category();
$ssbd_compare_set = $ssbd_compare_cat ? ssbd_offers( $ssbd_compare_cat, 4 ) : array();
?>

<section class="section section--tight" aria-labelledby="pillars-heading">
	<div class="wrap">
		<?php
		ssbd_section_head(
			__( 'Three pillars, nothing outside them', 'ssbd' ),
			__( 'What your site runs on, what makes it work, and what grows the business. We do not review laptops.', 'ssbd' ),
			array( 'id' => 'pillars-heading' )
		);
		?>

		<div class="grid grid--3">
			<?php foreach ( $ssbd_pillars as $ssbd_pillar ) : ?>
				<?php // The published count, not the planned one — the card links to a page, and the page has to deliver what the card claims. ?>
				<?php $ssbd_count = count( ssbd_matchups_published( $ssbd_pillar['key'] ) ); ?>
				<a class="card card--raised" href="<?php echo esc_url( ssbd_pillar_url( $ssbd_pillar['key'] ) ); ?>">
					<span class="card-icon"><?php ssbd_icon( $ssbd_pillar['icon'] ); ?></span>
					<h3 style="font-size:16px"><?php echo esc_html( $ssbd_pillar['title'] ); ?></h3>
					<p><?php echo esc_html( $ssbd_pillar['desc'] ); ?></p>
					<span class="link-cta">
						<?php
						if ( $ssbd_count ) {
							printf(
								/* translators: %d: number of comparisons */
								esc_html( _n( '%d comparison', '%d comparisons', $ssbd_count, 'ssbd' ) ),
								(int) $ssbd_count
							);
						} else {
							esc_html_e( 'See what we recommend', 'ssbd' );
						}
						?>
						<span class="arrow" aria-hidden="true">→</span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section section--soft" id="all-comparisons" aria-labelledby="matchups-heading">
	<div class="wrap">
		<?php
		ssbd_section_head(
			$ssbd_published ? __( 'Every comparison', 'ssbd' ) : __( 'Comparisons', 'ssbd' ),
			$ssbd_published
				? __( 'Narrow the list to your budget and your level.', 'ssbd' )
				: __( 'The tools we recommend are below. The head-to-head write-ups are in progress.', 'ssbd' ),
			array( 'id' => 'matchups-heading' )
		);
		?>

		<?php // Renders nothing when nothing is published, which is the honest state of the page until the first one lands. ?>
		<?php get_template_part( 'template-parts/comparisons/table', null, array(
			'matchups'    => $ssbd_published,
			'facets'      => $ssbd_facets,
			'pillars'     => $ssbd_pillars,
			'show_pillar' => true,
		) ); ?>

		<?php get_template_part( 'template-parts/comparisons/roadmap', null, array(
			'matchups' => $ssbd_roadmap,
		) ); ?>
	</div>
</section>

<?php if ( $ssbd_compare_set ) : ?>
	<section class="section" aria-labelledby="table-heading">
		<div class="wrap">
			<?php
			ssbd_section_head(
				__( 'Side by side', 'ssbd' ),
				__( 'The same facts for every tool in a category — renewal price included, not just the first-year rate.', 'ssbd' ),
				array( 'id' => 'table-heading' )
			);
			?>
			<?php get_template_part( 'template-parts/components/offer-table', null, array( 'offers' => $ssbd_compare_set ) ); ?>
		</div>
	</section>
<?php endif; ?>

<section class="section section--soft" aria-labelledby="criteria-heading">
	<div class="wrap">
		<?php ssbd_section_head( __( 'How we judge', 'ssbd' ), '', array( 'id' => 'criteria-heading' ) ); ?>
		<div class="grid grid--4">
			<?php foreach ( $ssbd_comparisons['criteria'] as $ssbd_criterion ) : ?>
				<article class="card card--raised">
					<h3 style="font-size:16px"><?php echo esc_html( $ssbd_criterion['title'] ); ?></h3>
					<p><?php echo esc_html( $ssbd_criterion['desc'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>

		<?php // Only disclose when a paid link actually rendered on the page. ?>
		<?php if ( ssbd_page_has_affiliate_links() ) : ?>
			<div style="margin-top:32px"><?php ssbd_affiliate_disclosure(); ?></div>
		<?php endif; ?>
	</div>
</section>
