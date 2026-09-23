<?php
/**
 * Front page services grid with the Build / Automate / Grow filter.
 *
 * Every service renders server-side regardless of the active filter; the
 * React island only toggles the `hidden` attribute. A crawler therefore
 * always sees every service and its keywords.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

$ssbd_tabs     = array(
	array( 'key' => 'all', 'label' => __( 'All', 'ssbd' ) ),
	array( 'key' => 'build', 'label' => __( 'Build', 'ssbd' ) ),
	array( 'key' => 'automate', 'label' => __( 'Automate', 'ssbd' ) ),
	array( 'key' => 'grow', 'label' => __( 'Grow', 'ssbd' ) ),
	array( 'key' => 'support', 'label' => __( 'Support', 'ssbd' ) ),
);
$ssbd_services = ssbd_services();
?>
<section class="section" id="services" aria-labelledby="services-heading">
	<div class="wrap">
		<?php
		ssbd_section_head(
			ssbd_section_heading( 'services' ),
			ssbd_section_lede( 'services' ),
			array( 'id' => 'services-heading' )
		);
		?>

		<?php
		ssbd_island(
			'filter-grid',
			array(
				'tabs'      => $ssbd_tabs,
				'attribute' => 'data-group',
			),
			function () use ( $ssbd_tabs, $ssbd_services ) {
				?>
				<div class="filter-tabs" role="group" aria-label="<?php esc_attr_e( 'Filter services', 'ssbd' ); ?>">
					<?php foreach ( $ssbd_tabs as $tab ) : ?>
						<button type="button" class="filter-tab" data-filter="<?php echo esc_attr( $tab['key'] ); ?>" aria-pressed="<?php echo 'all' === $tab['key'] ? 'true' : 'false'; ?>">
							<?php echo esc_html( $tab['label'] ); ?>
						</button>
					<?php endforeach; ?>
				</div>

				<div class="grid grid--3" data-filter-target>
					<?php foreach ( $ssbd_services as $service ) : ?>
						<article class="card" data-group="<?php echo esc_attr( $service['group'] ); ?>" id="<?php echo esc_attr( $service['slug'] ); ?>">
							<span class="card-icon"><?php ssbd_icon( $service['icon'] ); ?></span>
							<h3><?php echo esc_html( $service['title'] ); ?></h3>
							<?php if ( $service['short'] ) : ?>
								<p><?php echo esc_html( $service['short'] ); ?></p>
							<?php endif; ?>

							<?php if ( $service['pills'] ) : ?>
								<div class="pill-row">
									<?php foreach ( $service['pills'] as $pill ) : ?>
										<span class="pill"><?php echo esc_html( $pill ); ?></span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
							<?php if ( $service['children'] ) : ?>
								<p class="card-children">
									<?php foreach ( $service['children'] as $child_i => $child ) : ?>
										<?php echo $child_i ? '<span aria-hidden="true"> · </span>' : ''; ?>
										<a href="<?php echo esc_url( ssbd_service_url( $child ) ); ?>"><?php echo esc_html( $child['title'] ); ?></a>
									<?php endforeach; ?>
								</p>
							<?php endif; ?>
							<a class="link-cta" href="<?php echo esc_url( ssbd_service_url( $service ) ); ?>">
								<?php echo esc_html( $service['cta'] ); ?> <span class="arrow" aria-hidden="true">→</span>
							</a>
						</article>
					<?php endforeach; ?>
				</div>
				<?php
			}
		);
		?>
	</div>
</section>
