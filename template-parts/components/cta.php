<?php
/**
 * Closing call-to-action band.
 *
 * @package ssbd
 *
 * @var string $args['heading']
 * @var string $args['text']
 * @var string $args['button']
 */

defined( 'ABSPATH' ) || exit;

$ssbd_heading = $args['heading'] ?? __( 'Have a project in mind?', 'ssbd' );
$ssbd_text    = $args['text'] ?? __( 'Tell us what you are trying to build or fix. We will come back with a scope, a timeline and a fixed price.', 'ssbd' );
$ssbd_button  = $args['button'] ?? __( 'Start Your Project', 'ssbd' );
?>
<section class="section">
	<div class="wrap">
		<div class="cta-band">
			<div>
				<h2><?php echo esc_html( $ssbd_heading ); ?></h2>
				<p><?php echo esc_html( $ssbd_text ); ?></p>
			</div>
			<a class="btn btn--primary" href="<?php echo esc_url( ssbd_page_url( 'contact' ) ); ?>"><?php echo esc_html( $ssbd_button ); ?></a>
		</div>
	</div>
</section>
