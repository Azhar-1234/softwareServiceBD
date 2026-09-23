<?php
/**
 * A single service page.
 *
 * Each service in wp-admin is a real page: /services/web-development/, with
 * its children at /services/web-development/laravel-development/. The Services
 * template stays the hub that lists them all; this is what it links to.
 *
 * Everything above the editor content is generated from the service's fields,
 * so a new service is a usable page the moment it is published — the body is
 * where the site owner adds the detail that makes it rank.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$ssbd_service = ssbd_service( get_post_field( 'post_name' ) );

	if ( ! $ssbd_service ) {
		// Published but not in the resolved tree — render from the post alone.
		$ssbd_service = array(
			'slug'     => get_post_field( 'post_name' ),
			'title'    => get_the_title(),
			'short'    => (string) get_post_meta( get_the_ID(), '_ssbd_short', true ),
			'desc'     => (string) get_post_meta( get_the_ID(), '_ssbd_desc', true ),
			'icon'     => (string) get_post_meta( get_the_ID(), '_ssbd_icon', true ) ?: 'code',
			'pills'    => ssbd_meta_list( get_the_ID(), 'pills' ),
			'items'    => ssbd_meta_list( get_the_ID(), 'items' ),
			'children' => array(),
		);
	}

	$ssbd_children = $ssbd_service['children'] ?? array();
	$ssbd_parent   = wp_get_post_parent_id( get_the_ID() );

	// One Service node per page, so an engine answering "who builds Laravel
	// applications in Bangladesh" has a single unambiguous thing to quote.
	ssbd_schema_add( array(
		'@type'       => 'Service',
		'@id'         => get_permalink() . '#service',
		'name'        => $ssbd_service['title'],
		'description' => $ssbd_service['desc'] ?: $ssbd_service['short'],
		'serviceType' => $ssbd_service['title'],
		'url'         => get_permalink(),
		'provider'    => array( '@id' => ssbd_schema_id( 'organization' ) ),
		'areaServed'  => ssbd_site( 'areas_served' ),
		'hasOfferCatalog' => array(
			'@type'           => 'OfferCatalog',
			'name'            => $ssbd_service['title'],
			'itemListElement' => array_map(
				static function ( $item ) {
					return array(
						'@type'       => 'Offer',
						'itemOffered' => array( '@type' => 'Service', 'name' => $item ),
					);
				},
				$ssbd_service['items']
			),
		),
	) );

	/*
	 * Three levels of page, most specific first:
	 *
	 *   1. template-parts/services/<slug>.php — a design of its own.
	 *   2. inc/data/<slug>.php — the shared rich layout, filled from that file.
	 *   3. neither — the plain layout below, generated from the service fields.
	 *
	 * So a service becomes a full landing page by adding one data file, and the
	 * shared layout can be improved for all of them at once.
	 *
	 * Level 2 is off by default. It reads its hero, "what we build" and
	 * showcase copy entirely from the data file — the Short/Full description,
	 * Items and Pills fields in the service's own wp-admin meta box are read
	 * only for their slug, never shown. That split was invisible from
	 * wp-admin: every field in the meta box looked live, and eight services
	 * (wordpress-development, web-development, app-development,
	 * laravel-development, software-development, ai-integration,
	 * domain-hosting, website-maintenance-support) had a data file quietly
	 * overriding every one of them. The site owner edited a service expecting
	 * the meta box to control the page, as it does for every other service,
	 * and it did not.
	 *
	 * The eight old data files are kept, renamed, under inc/data/_disabled/ —
	 * nothing written there was lost. Every service, old or new, now renders
	 * through the plain layout, which is the one the meta box actually
	 * controls. Filter `ssbd_service_landing_pages` to true to bring this
	 * level back, either to restore a moved file or to build a new one.
	 */
	if ( locate_template( 'template-parts/services/' . $ssbd_service['slug'] . '.php' ) ) {
		get_template_part( 'template-parts/services/' . $ssbd_service['slug'], null, array( 'service' => $ssbd_service ) );

		get_footer();
		return;
	}

	/** Filter whether inc/data/<slug>.php can override a service's own fields. */
	if ( apply_filters( 'ssbd_service_landing_pages', false )
		&& is_readable( SSBD_DIR . '/inc/data/' . $ssbd_service['slug'] . '.php' ) ) {
		get_template_part( 'template-parts/services/service-page', null, array(
			'service' => $ssbd_service,
			'page'    => ssbd_data( $ssbd_service['slug'] ),
		) );

		get_footer();
		return;
	}

	get_template_part( 'template-parts/components/page-hero', null, array(
		'eyebrow' => $ssbd_parent ? get_the_title( $ssbd_parent ) : __( 'Services', 'ssbd' ),
		'heading' => $ssbd_service['title'],
		'lede'    => $ssbd_service['desc'] ?: $ssbd_service['short'],
	) );
	?>

	<?php if ( $ssbd_service['pills'] || $ssbd_service['items'] ) : ?>
		<section class="section section--tight" aria-labelledby="service-covers-heading">
			<div class="wrap">
				<h2 id="service-covers-heading" class="screen-reader-text"><?php esc_html_e( 'What this service covers', 'ssbd' ); ?></h2>

				<?php if ( $ssbd_service['pills'] ) : ?>
					<div class="pill-row">
						<?php foreach ( $ssbd_service['pills'] as $ssbd_pill ) : ?>
							<span class="pill"><?php echo esc_html( $ssbd_pill ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( $ssbd_service['items'] ) : ?>
					<ul class="service-items">
						<?php foreach ( $ssbd_service['items'] as $ssbd_item ) : ?>
							<li><?php echo esc_html( $ssbd_item ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $ssbd_children ) : ?>
		<section class="section section--soft" aria-labelledby="service-children-heading">
			<div class="wrap">
				<?php
				ssbd_section_head(
					/* translators: %s: parent service name */
					sprintf( __( 'Inside %s', 'ssbd' ), $ssbd_service['title'] ),
					'',
					array( 'id' => 'service-children-heading' )
				);
				?>
				<div class="grid grid--3">
					<?php foreach ( $ssbd_children as $ssbd_child ) : ?>
						<a class="card card--raised" href="<?php echo esc_url( ssbd_service_url( $ssbd_child ) ); ?>">
							<span class="card-icon"><?php ssbd_icon( $ssbd_child['icon'] ); ?></span>
							<h3><?php echo esc_html( $ssbd_child['title'] ); ?></h3>
							<p><?php echo esc_html( $ssbd_child['short'] ); ?></p>
							<span class="link-cta"><?php esc_html_e( 'View service', 'ssbd' ); ?> <span class="arrow" aria-hidden="true">→</span></span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( trim( get_the_content() ) ) : ?>
		<section class="section">
			<?php
			/*
			 * Full .wrap width (1240px), not wrap--narrow (760px). Narrow is
			 * right for a blog post's prose — single.php uses it for exactly
			 * that — but this is a marketing page: an editor building a
			 * Media & Text row here (see the "Service: Media Left/Right"
			 * patterns in the block inserter) needs the width to lay an image
			 * beside its text, or the row is squeezed down to a strip with
			 * empty gutters either side, which is the layout fault this
			 * replaced.
			 */
			?>
			<div class="wrap entry-content service-content"><?php the_content(); ?></div>
		</section>
	<?php endif; ?>

	<?php
	get_template_part( 'template-parts/components/faq', null, array( 'faqs' => ssbd_faqs( 'services' ) ) );
	get_template_part( 'template-parts/components/cta', null, array(
		/* translators: %s: service name */
		'heading' => sprintf( __( 'Need %s?', 'ssbd' ), $ssbd_service['title'] ),
	) );

endwhile;

get_footer();
