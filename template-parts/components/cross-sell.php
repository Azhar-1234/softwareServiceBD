<?php
/**
 * Cross-pillar recommendation banner.
 *
 * Someone choosing a host this week needs plugins next week, and a list to
 * email the week after. This is the link between those decisions — the point
 * where a one-purchase reader becomes a three-purchase one.
 *
 * Reusable: pass a banner array, or a pillar key to look one up.
 *
 * @package ssbd
 *
 * @var array  $args['banner'] Banner array (eyebrow, title, text, link, target).
 * @var string $args['url']    Where the button goes. Defaults to the pillar anchor.
 */

defined( 'ABSPATH' ) || exit;

$ssbd_banner = $args['banner'] ?? array();

if ( empty( $ssbd_banner['title'] ) ) {
	return;
}

$ssbd_url = $args['url'] ?? ssbd_pillar_url( $ssbd_banner['target'] ?? '' );
?>
<aside class="cross-sell">
	<div>
		<?php if ( ! empty( $ssbd_banner['eyebrow'] ) ) : ?>
			<span class="cross-sell-eyebrow"><?php echo esc_html( $ssbd_banner['eyebrow'] ); ?></span>
		<?php endif; ?>
		<h3><?php echo esc_html( $ssbd_banner['title'] ); ?></h3>
		<p><?php echo esc_html( $ssbd_banner['text'] ); ?></p>
	</div>

	<a class="btn btn--sm btn--ghost" href="<?php echo esc_url( $ssbd_url ); ?>">
		<?php echo esc_html( $ssbd_banner['link'] ); ?>
	</a>
</aside>
