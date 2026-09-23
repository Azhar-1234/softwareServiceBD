<?php
/**
 * Template Name: Services
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ssbd_services = ssbd_services();
$ssbd_about    = ssbd_data( 'about' );

// Each service gets its own Service node so an engine can answer
// "does Software Service BD do X?" without parsing prose.
ssbd_schema_add( array(
	'@type'           => 'ItemList',
	'@id'             => ssbd_canonical_url() . '#services',
	'name'            => __( 'Services offered by Software Service BD', 'ssbd' ),
	'itemListElement' => array_map(
		static function ( $service, $i ) {
			return array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'item'     => array(
					'@type'       => 'Service',
					'name'        => $service['title'],
					'description' => $service['desc'],
					'serviceType' => $service['title'],
					'url'         => ssbd_service_url( $service ),
					'provider'    => array( '@id' => ssbd_schema_id( 'organization' ) ),
					'areaServed'  => ssbd_site( 'areas_served' ),
					'hasOfferCatalog' => array(
						'@type' => 'OfferCatalog',
						'name'  => $service['title'],
						'itemListElement' => array_map(
							static function ( $item ) {
								return array(
									'@type'       => 'Offer',
									'itemOffered' => array( '@type' => 'Service', 'name' => $item ),
								);
							},
							$service['items']
						),
					),
				),
			);
		},
		$ssbd_services,
		array_keys( $ssbd_services )
	),
) );

get_template_part( 'template-parts/components/page-hero', null, array(
	'eyebrow'    => __( 'Services', 'ssbd' ),
	'heading'    => __( 'Software, automation and growth services', 'ssbd' ),
	'answer_key' => 'services',
) );
?>

<section class="section" aria-labelledby="services-list-heading">
	<div class="wrap">
		<h2 id="services-list-heading" class="screen-reader-text"><?php esc_html_e( 'Service list', 'ssbd' ); ?></h2>

		<?php foreach ( $ssbd_services as $ssbd_service ) : ?>
			<article class="service-row" id="<?php echo esc_attr( $ssbd_service['slug'] ); ?>">
				<div>
					<span class="card-icon"><?php ssbd_icon( $ssbd_service['icon'] ); ?></span>
					<span class="num" style="display:block;margin-top:10px"><?php echo esc_html( $ssbd_service['no'] ); ?></span>
				</div>

				<div>
					<h3><a href="<?php echo esc_url( ssbd_service_url( $ssbd_service ) ); ?>"><?php echo esc_html( $ssbd_service['title'] ); ?></a></h3>
					<?php if ( $ssbd_service['desc'] ) : ?>
						<p><?php echo esc_html( $ssbd_service['desc'] ); ?></p>
					<?php endif; ?>

					<?php if ( $ssbd_service['items'] ) : ?>
						<div class="pill-row" style="margin-top:14px">
							<?php foreach ( $ssbd_service['items'] as $ssbd_item ) : ?>
								<span class="pill"><?php echo esc_html( $ssbd_item ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php if ( $ssbd_service['children'] ) : ?>
						<p class="card-children" style="margin-top:14px">
							<?php esc_html_e( 'Also:', 'ssbd' ); ?>
							<?php foreach ( $ssbd_service['children'] as $ssbd_i => $ssbd_child ) : ?>
								<?php echo $ssbd_i ? '<span aria-hidden="true"> · </span>' : ''; ?>
								<a href="<?php echo esc_url( ssbd_service_url( $ssbd_child ) ); ?>"><?php echo esc_html( $ssbd_child['title'] ); ?></a>
							<?php endforeach; ?>
						</p>
					<?php endif; ?>
				</div>

				<a class="btn btn--sm btn--ghost" href="<?php echo esc_url( ssbd_service_url( $ssbd_service ) ); ?>"><?php echo esc_html( $ssbd_service['cta'] ); ?></a>
			</article>
		<?php endforeach; ?>
	</div>
</section>

<section class="section section--soft" aria-labelledby="process-heading">
	<div class="wrap">
		<?php
		ssbd_section_head(
			__( 'How a project runs', 'ssbd' ),
			__( 'Four stages, weekly demos, and a fixed scope agreed before we start.', 'ssbd' ),
			array( 'id' => 'process-heading' )
		);
		?>
		<div class="process">
			<?php foreach ( $ssbd_about['delivery_process'] as $ssbd_step ) : ?>
				<div class="process-step">
					<span class="num"><?php echo esc_html( $ssbd_step['no'] ); ?></span>
					<h3><?php echo esc_html( $ssbd_step['title'] ); ?></h3>
					<p><?php echo esc_html( $ssbd_step['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section" aria-labelledby="capabilities-heading">
	<div class="wrap">
		<?php ssbd_section_head( __( 'What you get either way', 'ssbd' ), '', array( 'id' => 'capabilities-heading' ) ); ?>
		<div class="grid grid--4">
			<?php foreach ( $ssbd_about['capabilities'] as $ssbd_capability ) : ?>
				<article class="card card--raised">
					<span class="card-icon"><?php ssbd_icon( $ssbd_capability['icon'] ); ?></span>
					<h3 style="font-size:16px"><?php echo esc_html( $ssbd_capability['title'] ); ?></h3>
					<p><?php echo esc_html( $ssbd_capability['desc'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		if ( trim( get_the_content() ) ) :
			?>
			<section class="section">
				<div class="wrap wrap--narrow entry-content"><?php the_content(); ?></div>
			</section>
			<?php
		endif;
	endwhile;
endif;

get_template_part( 'template-parts/components/faq', null, array( 'faqs' => ssbd_faqs( 'services' ) ) );
get_template_part( 'template-parts/components/cta' );

get_footer();
