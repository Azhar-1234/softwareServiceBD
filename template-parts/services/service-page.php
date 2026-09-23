<?php
/**
 * The shared rich service page.
 *
 * Five sections — hero, what we build, the screens, how we work, the enquiry —
 * driven entirely by a data file: inc/data/<slug>.php. A service with such a
 * file gets this page; one without gets the plain layout in
 * single-ssbd_service.php. A service that needs something else entirely still
 * wins with its own template-parts/services/<slug>.php.
 *
 * That means a new landing page for a service is one data file, and a change
 * to the layout lands on every one of them at once.
 *
 * @package ssbd
 *
 * @var array  $args['service']
 * @var array  $args['page']  Contents of inc/data/<slug>.php.
 */

defined( 'ABSPATH' ) || exit;

$ssbd_service = $args['service'] ?? array();
$ssbd_page    = $args['page'] ?? array();
$ssbd_slug    = $ssbd_service['slug'] ?? '';

if ( ! $ssbd_page ) {
	return;
}

$ssbd_contact = ssbd_page_url( 'contact' );
$ssbd_hero    = $ssbd_page['hero'];
$ssbd_screens = ssbd_service_screens( $ssbd_slug );
$ssbd_labels  = $ssbd_page['showcase']['labels'];
$ssbd_frame   = $ssbd_page['frame'] ?? 'phone';

// Which drawn screen leads. Laravel opens on the dashboard, a marketing site
// opens on the marketing page — the placeholder should match the pitch.
$ssbd_start   = (int) ( $ssbd_page['screen_start'] ?? 0 );

/**
 * One framed screen: a real screenshot when one exists, a drawn one if not.
 *
 * @param int|null $attachment Attachment ID.
 * @param int      $variant    Drawn screen to use (0-2).
 * @param string   $class      Extra classes.
 */
$ssbd_screen = static function ( $attachment, $variant, $class = '' ) use ( $ssbd_frame ) {
	get_template_part( 'template-parts/components/device-frame', null, array(
		'type'       => $ssbd_frame,
		'attachment' => $attachment,
		'variant'    => $variant,
		'class'      => $class,
	) );
};
?>

<section class="section--hero svc-hero" id="top">
	<div class="wrap svc-hero-inner">
		<div class="svc-hero-copy">
			<?php ssbd_breadcrumbs(); ?>

			<div class="page-hero-eyebrow"><?php ssbd_eyebrow( $ssbd_hero['eyebrow'], true, 'eyebrow--chip' ); ?></div>

			<h1><?php echo esc_html( $ssbd_hero['headline'] ); ?></h1>

			<p class="lede"><?php echo esc_html( $ssbd_hero['lede'] ); ?></p>

			<div class="hero-actions">
				<a class="btn btn--primary" href="#inquiry"><?php echo esc_html( $ssbd_page['cta']['button'] ); ?></a>
				<a class="btn btn--ghost" href="<?php echo esc_url( $ssbd_contact ); ?>"><?php esc_html_e( 'Talk to an Expert', 'ssbd' ); ?></a>
			</div>

			<p class="hero-meta"><?php echo esc_html( implode( ' • ', $ssbd_hero['trust'] ) ); ?></p>
		</div>

		<div class="svc-hero-visual" aria-hidden="<?php echo $ssbd_screens ? 'false' : 'true'; ?>">
			<?php
			// Phones sit in a fan of three; a browser window is wide enough to
			// carry the hero on its own, and three of them is a pile.
			if ( 'browser' === $ssbd_frame ) {
				$ssbd_screen( $ssbd_screens[0] ?? null, $ssbd_start, 'frame--lead' );
			} else {
				$ssbd_screen( $ssbd_screens[1] ?? null, ( $ssbd_start + 1 ) % 3, 'frame--aside frame--left' );
				$ssbd_screen( $ssbd_screens[0] ?? null, $ssbd_start, 'frame--lead' );
				$ssbd_screen( $ssbd_screens[2] ?? null, ( $ssbd_start + 2 ) % 3, 'frame--aside frame--right' );
			}
			?>
		</div>
	</div>
</section>

<section class="section" id="what-we-build" aria-labelledby="svc-build-heading">
	<div class="wrap">
		<?php ssbd_section_head( $ssbd_page['build']['heading'], $ssbd_page['build']['lede'], array( 'id' => 'svc-build-heading' ) ); ?>

		<div class="grid grid--4">
			<?php foreach ( $ssbd_page['build']['items'] as $ssbd_item ) : ?>
				<article class="card svc-card">
					<span class="card-icon"><?php ssbd_icon( $ssbd_item['icon'] ); ?></span>
					<h3><?php echo esc_html( $ssbd_item['title'] ); ?></h3>
					<p><?php echo esc_html( $ssbd_item['desc'] ); ?></p>
					<div class="pill-row">
						<?php foreach ( $ssbd_item['pills'] as $ssbd_pill ) : ?>
							<span class="pill"><?php echo esc_html( $ssbd_pill ); ?></span>
						<?php endforeach; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section section--soft showcase" id="screens" aria-labelledby="svc-screens-heading">
	<div class="wrap">
		<?php ssbd_section_head( $ssbd_page['showcase']['heading'], $ssbd_page['showcase']['lede'], array( 'id' => 'svc-screens-heading' ) ); ?>
	</div>

	<?php
	// Phones are narrow enough to line up six across and scroll; browser
	// windows are not, so they wrap in threes instead of running off the edge.
	$ssbd_wide = 'browser' === $ssbd_frame;
	?>
	<div class="showcase-rail<?php echo $ssbd_wide ? ' showcase-rail--wide' : ''; ?>">
		<ul class="showcase-track">
			<?php
			$ssbd_count = $ssbd_screens ? count( $ssbd_screens ) : ( $ssbd_wide ? 3 : 6 );

			for ( $ssbd_i = 0; $ssbd_i < $ssbd_count; $ssbd_i++ ) :
				?>
				<li class="showcase-item">
					<?php $ssbd_screen( $ssbd_screens[ $ssbd_i ] ?? null, ( $ssbd_i + $ssbd_start ) % 3, 'frame--sm' ); ?>
					<?php if ( ! $ssbd_screens ) : ?>
						<span class="showcase-label"><?php echo esc_html( $ssbd_labels[ $ssbd_i ] ?? '' ); ?></span>
					<?php endif; ?>
				</li>
			<?php endfor; ?>
		</ul>
	</div>
</section>

<section class="section" id="process" aria-labelledby="svc-process-heading">
	<div class="wrap">
		<?php ssbd_section_head( $ssbd_page['process']['heading'], $ssbd_page['process']['lede'], array( 'id' => 'svc-process-heading' ) ); ?>

		<ol class="steps">
			<?php foreach ( $ssbd_page['process']['steps'] as $ssbd_step ) : ?>
				<li class="step">
					<span class="step-no"><?php echo esc_html( $ssbd_step['no'] ); ?></span>
					<h3><?php echo esc_html( $ssbd_step['title'] ); ?></h3>
					<p><?php echo esc_html( $ssbd_step['desc'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>

		<?php // Not every service is sold on its stack: hosting and care plans leave this out. ?>
		<?php if ( ! empty( $ssbd_page['process']['stack']['items'] ) ) : ?>
			<div class="stack-row">
				<p class="stack-lede"><?php echo esc_html( $ssbd_page['process']['stack']['lede'] ); ?></p>
				<?php get_template_part( 'template-parts/components/tech-logos', null, array( 'items' => $ssbd_page['process']['stack']['items'] ) ); ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<section class="section section--soft" id="inquiry-section" aria-labelledby="svc-consult-heading">
	<div class="wrap consult">
		<div class="consult-copy">
			<h2 id="svc-consult-heading"><?php echo esc_html( $ssbd_page['consultation']['heading'] ); ?></h2>
			<p class="lede"><?php echo esc_html( $ssbd_page['consultation']['lede'] ); ?></p>

			<ul class="consult-points">
				<?php foreach ( $ssbd_page['consultation']['points'] as $ssbd_point ) : ?>
					<li>
						<strong><?php echo esc_html( $ssbd_point['title'] ); ?></strong>
						<span><?php echo esc_html( $ssbd_point['desc'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php
			// The rest of the catalogue, one line, for the people this page is
			// not the answer for — and for the internal links.
			// Every other headline service, plus this one's own children — so
			// Web Development links down to Laravel and WordPress rather than
			// leaving them reachable only from the header dropdown.
			$ssbd_related = array();

			foreach ( ssbd_services() as $ssbd_item ) {
				if ( $ssbd_item['slug'] === $ssbd_slug ) {
					$ssbd_related = array_merge( $ssbd_related, $ssbd_item['children'] );
					continue;
				}

				$ssbd_related[] = $ssbd_item;
			}
			?>
			<?php if ( $ssbd_related ) : ?>
				<p class="consult-related">
					<?php esc_html_e( 'Need something else?', 'ssbd' ); ?>
					<?php foreach ( $ssbd_related as $ssbd_i => $ssbd_service ) : ?>
						<?php echo $ssbd_i ? '<span aria-hidden="true"> · </span>' : ''; ?>
						<a href="<?php echo esc_url( ssbd_service_url( $ssbd_service ) ); ?>"><?php echo esc_html( $ssbd_service['title'] ); ?></a>
					<?php endforeach; ?>
				</p>
			<?php endif; ?>
		</div>

		<div class="consult-form">
			<?php get_template_part( 'template-parts/components/inquiry-form' ); ?>
		</div>
	</div>
</section>

<?php
get_template_part( 'template-parts/components/faq', null, array(
	'faqs'    => $ssbd_page['faqs'],
	'heading' => $ssbd_page['faq_heading'],
) );

get_template_part( 'template-parts/components/cta', null, array(
	'heading' => $ssbd_page['cta']['heading'],
	'text'    => $ssbd_page['cta']['text'],
	'button'  => $ssbd_page['cta']['button'],
) );
