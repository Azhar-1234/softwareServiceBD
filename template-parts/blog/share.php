<?php
/**
 * Share row.
 *
 * Plain links to each network's share endpoint — no third-party widgets, which
 * would mean loading their scripts and letting them watch every reader.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

$ssbd_url    = get_permalink();
$ssbd_image  = has_post_thumbnail() ? (string) get_the_post_thumbnail_url( null, 'full' ) : '';
$ssbd_shares = ssbd_share_links( $ssbd_url, get_the_title(), $ssbd_image );
?>
<div class="share">
	<span class="share-label"><?php esc_html_e( 'Share this article', 'ssbd' ); ?></span>

	<div class="share-row">
		<?php foreach ( $ssbd_shares as $ssbd_key => $ssbd_share ) : ?>
			<?php $ssbd_mark = ssbd_social_mark( $ssbd_key ); ?>
			<?php if ( $ssbd_mark ) : ?>
				<a class="share-btn" href="<?php echo esc_url( $ssbd_share['url'] ); ?>"
					target="_blank" rel="noopener nofollow"
					aria-label="<?php echo esc_attr( $ssbd_share['label'] ); ?>">
					<svg viewBox="0 0 24 24" width="17" height="17" fill="currentColor" aria-hidden="true" focusable="false"><?php
						echo $ssbd_mark; // phpcs:ignore WordPress.Security.EscapingOutput -- static path markup from inc/article.php.
					?></svg>
				</a>
			<?php endif; ?>
		<?php endforeach; ?>

		<?php // Revealed by article.js: a copy button that cannot copy is worse than none. ?>
		<button type="button" class="share-btn share-btn--copy"
			data-copy-link="<?php echo esc_url( $ssbd_url ); ?>"
			data-copied="<?php esc_attr_e( 'Copied', 'ssbd' ); ?>" hidden>
			<span data-copy-label aria-live="polite"><?php esc_html_e( 'Copy link', 'ssbd' ); ?></span>
		</button>
	</div>
</div>
