<?php
/**
 * Social links.
 *
 * Only the networks with a URL in inc/data/site.php (or the Customizer) are
 * rendered — an empty row of dead icons says less than no row at all.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

// The marks themselves live in ssbd_social_mark(), shared with the article
// share row so a path is only ever written once.
$ssbd_social = (array) ssbd_site( 'social', array() );

$ssbd_links = array(
	'facebook' => array(
		'label' => __( 'Facebook', 'ssbd' ),
		'url'   => $ssbd_social['facebook'] ?? '',
	),
	'linkedin' => array(
		'label' => __( 'LinkedIn', 'ssbd' ),
		'url'   => $ssbd_social['linkedin'] ?? '',
	),
	'youtube'  => array(
		'label' => __( 'YouTube', 'ssbd' ),
		'url'   => $ssbd_social['youtube'] ?? '',
	),
	'pinterest' => array(
		'label' => __( 'Pinterest', 'ssbd' ),
		'url'   => $ssbd_social['pinterest'] ?? '',
	),
	'x'        => array(
		'label' => __( 'X', 'ssbd' ),
		'url'   => $ssbd_social['x'] ?? '',
	),
	'github'   => array(
		'label' => __( 'GitHub', 'ssbd' ),
		'url'   => $ssbd_social['github'] ?? '',
	),
	'whatsapp' => array(
		'label' => __( 'WhatsApp', 'ssbd' ),
		'url'   => (string) ssbd_site( 'whatsapp', '' ),
	),
);

$ssbd_links = array_filter(
	$ssbd_links,
	static function ( $link ) {
		return ! empty( $link['url'] ) && ! str_contains( $link['url'], 'XXXXXX' );
	}
);

if ( ! $ssbd_links ) {
	return;
}
?>
<ul class="social-links">
	<?php foreach ( $ssbd_links as $ssbd_key => $ssbd_link ) : ?>
		<li>
			<a href="<?php echo esc_url( $ssbd_link['url'] ); ?>" rel="noopener" aria-label="<?php echo esc_attr( $ssbd_link['label'] ); ?>">
				<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true" focusable="false"><?php echo ssbd_social_mark( $ssbd_key ); // phpcs:ignore WordPress.Security.EscapingOutput -- static path markup from inc/article.php. ?></svg>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
