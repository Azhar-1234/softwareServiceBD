<?php
/**
 * Template Name: Portfolio
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ssbd_filters = ssbd_project_filters();

get_template_part( 'template-parts/components/page-hero', null, array(
	'eyebrow'    => __( 'Portfolio', 'ssbd' ),
	'heading'    => __( 'Work we have shipped', 'ssbd' ),
	'answer_key' => 'portfolio',
) );
?>

<section class="section" aria-labelledby="portfolio-heading">
	<div class="wrap">
		<h2 id="portfolio-heading" class="screen-reader-text"><?php esc_html_e( 'Projects', 'ssbd' ); ?></h2>

		<?php
		ssbd_island(
			'filter-grid',
			array(
				'tabs'      => $ssbd_filters,
				'attribute' => 'data-filters',
				'match'     => 'contains',
			),
			function () use ( $ssbd_filters ) {
				?>
				<div class="filter-tabs filter-tabs--left" role="group" aria-label="<?php esc_attr_e( 'Filter projects', 'ssbd' ); ?>">
					<?php foreach ( $ssbd_filters as $tab ) : ?>
						<button type="button" class="filter-tab" data-filter="<?php echo esc_attr( $tab['key'] ); ?>" aria-pressed="<?php echo 'all' === $tab['key'] ? 'true' : 'false'; ?>">
							<?php echo esc_html( $tab['label'] ); ?>
						</button>
					<?php endforeach; ?>
				</div>

				<div class="grid grid--3" data-filter-target>
					<?php
					foreach ( ssbd_projects() as $project ) {
						get_template_part( 'template-parts/components/project-card', null, array( 'project' => $project ) );
					}
					?>
				</div>
				<?php
			}
		);
		?>
	</div>
</section>

<?php
get_template_part( 'template-parts/components/cta', null, array(
	'heading' => __( 'Want something like this?', 'ssbd' ),
	'text'    => __( 'Send us the closest example to what you have in mind and we will scope it.', 'ssbd' ),
) );

get_footer();
