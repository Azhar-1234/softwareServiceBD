<?php
/**
 * "Why us" grid on the front page.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

$ssbd_why = ssbd_data( 'about' )['why_us'] ?? array();
?>
<section class="section section--soft" id="why" aria-labelledby="why-heading">
	<div class="wrap">
		<?php
		ssbd_section_head(
			ssbd_section_heading( 'why' ),
			ssbd_section_lede( 'why' ),
			array( 'id' => 'why-heading' )
		);
		?>

		<div class="grid grid--4">
			<?php foreach ( $ssbd_why as $ssbd_item ) : ?>
				<article class="card card--raised">
					<span class="card-icon"><?php ssbd_icon( $ssbd_item['icon'] ); ?></span>
					<h3><?php echo esc_html( $ssbd_item['title'] ); ?></h3>
					<p><?php echo esc_html( $ssbd_item['desc'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
