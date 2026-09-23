<?php
/**
 * Resource teasers on the front page.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

$ssbd_cards = ssbd_data( 'resources' )['front_cards'] ?? array();
?>
<section class="section section--surface section--bordered" aria-labelledby="resources-heading">
	<div class="wrap">
		<?php
		ssbd_section_head(
			ssbd_section_heading( 'resources' ),
			ssbd_section_lede( 'resources' ),
			array( 'id' => 'resources-heading' )
		);
		?>

		<div class="grid grid--4">
			<?php foreach ( $ssbd_cards as $ssbd_card ) : ?>
				<a class="card" href="<?php echo esc_url( ssbd_page_url( $ssbd_card['page'] ) ); ?>">
					<span class="card-icon"><?php ssbd_icon( $ssbd_card['icon'], 19 ); ?></span>
					<h3 style="font-size:15.5px"><?php echo esc_html( $ssbd_card['title'] ); ?></h3>
					<p style="font-size:13px"><?php echo esc_html( $ssbd_card['desc'] ); ?></p>
					<div class="pill-row">
						<?php foreach ( $ssbd_card['badges'] as $ssbd_badge ) : ?>
							<span class="pill"><?php echo esc_html( $ssbd_badge ); ?></span>
						<?php endforeach; ?>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
