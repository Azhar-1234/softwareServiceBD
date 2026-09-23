<?php
/**
 * A drawn app screen, used inside a phone frame until real screenshots exist.
 *
 * Three variants — dashboard, orders, assistant — so a row of frames looks
 * like a set of applications rather than one repeated wireframe. Every colour
 * is a theme token, so the screens follow light and dark mode, and there is
 * nothing to download.
 *
 * @package ssbd
 *
 * @var int $args['variant'] 0, 1 or 2.
 */

defined( 'ABSPATH' ) || exit;

$ssbd_variant = (int) ( $args['variant'] ?? 0 );
?>
<div class="screen screen--<?php echo esc_attr( $ssbd_variant ); ?>">

	<?php if ( 0 === $ssbd_variant ) : ?>
		<div class="screen-top">
			<span class="screen-title"><?php esc_html_e( 'Overview', 'ssbd' ); ?></span>
			<span class="screen-avatar"></span>
		</div>
		<div class="screen-kpi">
			<strong><?php echo esc_html( '৳ 1,24,000' ); ?></strong>
			<span><?php esc_html_e( 'Revenue · this month', 'ssbd' ); ?></span>
		</div>
		<div class="screen-chart">
			<span style="height:36%"></span><span style="height:54%"></span><span style="height:44%"></span>
			<span style="height:70%"></span><span style="height:58%"></span><span style="height:86%"></span>
		</div>
		<div class="screen-tiles">
			<span></span><span></span>
		</div>

	<?php elseif ( 1 === $ssbd_variant ) : ?>
		<div class="screen-top">
			<span class="screen-title"><?php esc_html_e( 'Orders', 'ssbd' ); ?></span>
			<span class="screen-pill"><?php esc_html_e( 'Today', 'ssbd' ); ?></span>
		</div>
		<ul class="screen-list">
			<li><span class="screen-thumb"></span><span class="screen-lines"><i></i><i class="is-short"></i></span><span class="screen-tag"><?php esc_html_e( 'Paid', 'ssbd' ); ?></span></li>
			<li><span class="screen-thumb"></span><span class="screen-lines"><i></i><i class="is-short"></i></span><span class="screen-tag is-muted"><?php esc_html_e( 'New', 'ssbd' ); ?></span></li>
			<li><span class="screen-thumb"></span><span class="screen-lines"><i></i><i class="is-short"></i></span><span class="screen-tag is-muted"><?php esc_html_e( 'New', 'ssbd' ); ?></span></li>
			<li><span class="screen-thumb"></span><span class="screen-lines"><i></i><i class="is-short"></i></span><span class="screen-tag"><?php esc_html_e( 'Paid', 'ssbd' ); ?></span></li>
		</ul>
		<span class="screen-cta"><?php esc_html_e( 'Checkout', 'ssbd' ); ?></span>

	<?php else : ?>
		<div class="screen-top">
			<span class="screen-title"><?php esc_html_e( 'Assistant', 'ssbd' ); ?></span>
			<span class="screen-dot"></span>
		</div>
		<div class="screen-chat">
			<span class="bubble"><?php esc_html_e( 'Where is order #1042?', 'ssbd' ); ?></span>
			<span class="bubble bubble--ai"><?php esc_html_e( 'Shipped today. Arriving Thursday.', 'ssbd' ); ?></span>
			<span class="bubble"><?php esc_html_e( 'Notify the customer', 'ssbd' ); ?></span>
			<span class="bubble bubble--ai bubble--typing"><i></i><i></i><i></i></span>
		</div>
		<span class="screen-input"><?php esc_html_e( 'Ask anything…', 'ssbd' ); ?></span>
	<?php endif; ?>

</div>
