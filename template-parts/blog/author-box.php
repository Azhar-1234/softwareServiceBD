<?php
/**
 * Editorial byline box, closing the article.
 *
 * The site's own mark and standing rather than a WordPress user avatar: posts
 * here are published under the company, and a stock Gravatar of an admin
 * account says less than nothing about who stands behind the writing.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

$ssbd_name  = ssbd_site( 'legal_name', get_bloginfo( 'name' ) );
$ssbd_logo  = ssbd_logo_url();
$ssbd_photo = ssbd_author_photo();
?>
<aside class="author-box">
	<div class="author-box-mark" aria-hidden="true">
		<?php if ( $ssbd_photo ) : ?>
			<img src="<?php echo esc_url( $ssbd_photo ); ?>" alt="" loading="lazy" decoding="async">
		<?php elseif ( $ssbd_logo ) : ?>
			<img src="<?php echo esc_url( $ssbd_logo ); ?>" alt="" loading="lazy" decoding="async">
		<?php else : ?>
			<span><?php echo esc_html( mb_substr( $ssbd_name, 0, 1 ) ); ?></span>
		<?php endif; ?>
	</div>

	<div class="author-box-body">
		<span class="author-box-label"><?php esc_html_e( 'Written by', 'ssbd' ); ?></span>
		<?php // Same resolver as the byline above the article, so the two never disagree. ?>
		<h2 class="author-box-name"><?php echo esc_html( ssbd_author_name() ); ?></h2>
	</div>
</aside>
