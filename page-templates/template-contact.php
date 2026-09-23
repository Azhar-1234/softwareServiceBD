<?php
/**
 * Template Name: Contact
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ssbd_site    = ssbd_site();
$ssbd_address = $ssbd_site['address'];

$ssbd_steps = array(
	array( 'no' => '01', 'title' => __( 'We receive your inquiry', 'ssbd' ) ),
	array( 'no' => '02', 'title' => __( 'We review your requirements', 'ssbd' ) ),
	array( 'no' => '03', 'title' => __( 'We contact you', 'ssbd' ) ),
	array( 'no' => '04', 'title' => __( 'We discuss scope & solution', 'ssbd' ) ),
	array( 'no' => '05', 'title' => __( 'We provide the next steps', 'ssbd' ) ),
);

get_template_part( 'template-parts/components/page-hero', null, array(
	'eyebrow'    => __( 'Contact', 'ssbd' ),
	'heading'    => __( 'Tell us about your project', 'ssbd' ),
	'answer_key' => 'contact',
) );
?>

<section class="section" aria-labelledby="inquiry-heading">
	<div class="wrap">
		<div class="grid contact-grid">
			<div>
				<h2 id="inquiry-heading"><?php esc_html_e( 'Project inquiry', 'ssbd' ); ?></h2>
				<?php get_template_part( 'template-parts/components/inquiry-form' ); ?>
			</div>

			<aside class="card card--raised contact-card">
				<h2><?php esc_html_e( 'Direct contact', 'ssbd' ); ?></h2>

				<div class="contact-info">
					<div class="contact-info-row">
						<span class="label"><?php esc_html_e( 'Email', 'ssbd' ); ?></span>
						<a class="value" href="mailto:<?php echo esc_attr( $ssbd_site['email'] ); ?>"><?php echo esc_html( $ssbd_site['email'] ); ?></a>
					</div>

					<?php if ( ! empty( $ssbd_site['whatsapp'] ) ) : ?>
						<div class="contact-info-row">
							<span class="label"><?php esc_html_e( 'WhatsApp', 'ssbd' ); ?></span>
							<a class="value" href="<?php echo esc_url( $ssbd_site['whatsapp'] ); ?>" rel="noopener"><?php esc_html_e( 'Chat on WhatsApp →', 'ssbd' ); ?></a>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $ssbd_site['phone'] ) ) : ?>
						<div class="contact-info-row">
							<span class="label"><?php esc_html_e( 'Phone', 'ssbd' ); ?></span>
							<a class="value" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $ssbd_site['phone'] ) ); ?>"><?php echo esc_html( $ssbd_site['phone'] ); ?></a>
						</div>
					<?php endif; ?>

					<?php // Both read from inc/data/site.php so the wording matches the answer block above. ?>
					<div class="contact-info-row">
						<span class="label"><?php esc_html_e( 'Location', 'ssbd' ); ?></span>
						<span class="value"><?php echo esc_html( ssbd_site( 'location_label' ) ); ?></span>
					</div>

					<div class="contact-info-row">
						<span class="label"><?php esc_html_e( 'Hours', 'ssbd' ); ?></span>
						<span class="value"><?php echo esc_html( ssbd_site( 'hours_label' ) ); ?></span>
					</div>

					<div class="contact-info-row">
						<span class="label"><?php esc_html_e( 'Response time', 'ssbd' ); ?></span>
						<span class="value"><?php echo esc_html( ucfirst( ssbd_site( 'response_time' ) ) ); ?></span>
					</div>

					<div class="contact-info-row">
						<span class="label"><?php esc_html_e( 'Serving', 'ssbd' ); ?></span>
						<span class="value value--wide"><?php echo esc_html( implode( ', ', $ssbd_site['areas_served'] ) ); ?></span>
					</div>
				</div>
			</aside>
		</div>
	</div>
</section>

<section class="section section--soft" aria-labelledby="next-heading">
	<div class="wrap">
		<?php ssbd_section_head( __( 'What happens next', 'ssbd' ), '', array( 'id' => 'next-heading' ) ); ?>
		<div class="process">
			<?php foreach ( $ssbd_steps as $ssbd_step ) : ?>
				<div class="process-step">
					<span class="num"><?php echo esc_html( $ssbd_step['no'] ); ?></span>
					<h3><?php echo esc_html( $ssbd_step['title'] ); ?></h3>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
get_template_part( 'template-parts/components/faq', null, array( 'faqs' => ssbd_faqs( 'contact' ) ) );

get_footer();
