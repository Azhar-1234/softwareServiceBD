<?php
/**
 * Template Name: Solutions
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ssbd_solutions = ssbd_data( 'solutions' );

get_template_part( 'template-parts/components/page-hero', null, array(
	'eyebrow'    => __( 'Solutions', 'ssbd' ),
	'heading'    => __( 'Solutions organised by business outcome', 'ssbd' ),
	'answer_key' => 'solutions',
) );
?>

<section class="section" aria-labelledby="categories-heading">
	<div class="wrap">
		<?php ssbd_section_head( __( 'What do you want to do?', 'ssbd' ), '', array( 'id' => 'categories-heading' ) ); ?>

		<div class="grid grid--3">
			<?php foreach ( $ssbd_solutions['categories'] as $ssbd_category ) : ?>
				<article class="card">
					<span class="card-icon"><?php ssbd_icon( $ssbd_category['icon'] ); ?></span>
					<h3><?php echo esc_html( $ssbd_category['title'] ); ?></h3>
					<p><?php echo esc_html( $ssbd_category['desc'] ); ?></p>
					<div class="pill-row">
						<?php foreach ( $ssbd_category['items'] as $ssbd_item ) : ?>
							<span class="pill"><?php echo esc_html( $ssbd_item ); ?></span>
						<?php endforeach; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section section--soft" aria-labelledby="journey-heading">
	<div class="wrap">
		<?php
		ssbd_section_head(
			__( 'The path most clients follow', 'ssbd' ),
			__( 'You can join at any stage — most businesses start in the middle.', 'ssbd' ),
			array( 'id' => 'journey-heading' )
		);
		?>
		<div class="journey">
			<?php foreach ( $ssbd_solutions['journey'] as $ssbd_i => $ssbd_stage ) : ?>
				<?php if ( $ssbd_i > 0 ) : ?>
					<span class="journey-sep" aria-hidden="true">→</span>
				<?php endif; ?>
				<div class="journey-node">
					<span class="n"><?php echo esc_html( $ssbd_i + 1 ); ?></span>
					<?php echo esc_html( $ssbd_stage ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section" aria-labelledby="who-heading">
	<div class="wrap">
		<?php ssbd_section_head( __( 'Who we build for', 'ssbd' ), '', array( 'id' => 'who-heading' ) ); ?>
		<div class="grid grid--4">
			<?php foreach ( $ssbd_solutions['business_types'] as $ssbd_type ) : ?>
				<article class="card card--raised">
					<h3 style="font-size:16px"><?php echo esc_html( $ssbd_type['title'] ); ?></h3>
					<p><?php echo esc_html( $ssbd_type['desc'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section section--surface section--bordered" aria-labelledby="how-heading">
	<div class="wrap">
		<?php ssbd_section_head( __( 'How we get there', 'ssbd' ), '', array( 'id' => 'how-heading' ) ); ?>
		<div class="process">
			<?php foreach ( $ssbd_solutions['why_steps'] as $ssbd_step ) : ?>
				<div class="process-step">
					<span class="num"><?php echo esc_html( $ssbd_step['no'] ); ?></span>
					<h3><?php echo esc_html( $ssbd_step['title'] ); ?></h3>
					<p><?php echo esc_html( $ssbd_step['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
get_template_part( 'template-parts/components/faq', null, array( 'faqs' => ssbd_faqs( 'solutions' ) ) );
get_template_part( 'template-parts/components/cta' );

get_footer();
