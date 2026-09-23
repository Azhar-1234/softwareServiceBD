<?php
/**
 * A device frame around one screen.
 *
 * Phone for app services, browser window for web services. Inside it goes a
 * real screenshot when the service has one — uploaded to its "App / interface
 * screenshots" field — and a drawn screen when it does not, so a service page
 * is presentable before there is work to show and improves the moment there
 * is.
 *
 * @package ssbd
 *
 * @var string   $args['type']       'phone' or 'browser'.
 * @var int|null $args['attachment'] Attachment ID, or null for a drawn screen.
 * @var int      $args['variant']    Which drawn screen (0-2).
 * @var string   $args['class']      Extra classes for the frame.
 */

defined( 'ABSPATH' ) || exit;

$ssbd_type    = 'browser' === ( $args['type'] ?? 'phone' ) ? 'browser' : 'phone';
$ssbd_att     = $args['attachment'] ?? null;
$ssbd_variant = (int) ( $args['variant'] ?? 0 );
$ssbd_class   = $args['class'] ?? '';
?>
<div class="<?php echo esc_attr( $ssbd_type . ' ' . $ssbd_class ); ?>">

	<?php if ( 'browser' === $ssbd_type ) : ?>
		<div class="browser-bar" aria-hidden="true">
			<span class="browser-dots"><i></i><i></i><i></i></span>
			<span class="browser-url"></span>
		</div>
	<?php else : ?>
		<span class="phone-notch" aria-hidden="true"></span>
	<?php endif; ?>

	<div class="<?php echo esc_attr( $ssbd_type ); ?>-screen">
		<?php if ( $ssbd_att ) : ?>
			<?php
			echo wp_get_attachment_image( $ssbd_att, 'large', false, array(
				'class'    => $ssbd_type . '-shot',
				'loading'  => 'lazy',
				'decoding' => 'async',
			) );
			?>
		<?php elseif ( 'browser' === $ssbd_type ) : ?>
			<?php get_template_part( 'template-parts/components/browser-screen', null, array( 'variant' => $ssbd_variant ) ); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/components/phone-screen', null, array( 'variant' => $ssbd_variant ) ); ?>
		<?php endif; ?>
	</div>
</div>
