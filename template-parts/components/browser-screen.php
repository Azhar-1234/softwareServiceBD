<?php
/**
 * A drawn website screen, used inside a browser frame until real screenshots
 * exist. The counterpart to phone-screen.php.
 *
 * Three variants — marketing site, admin dashboard, storefront — so a row of
 * frames looks like a set of projects rather than one repeated wireframe.
 * Every colour is a theme token, so the screens follow light and dark mode.
 *
 * @package ssbd
 *
 * @var int $args['variant'] 0, 1 or 2.
 */

defined( 'ABSPATH' ) || exit;

$ssbd_variant = (int) ( $args['variant'] ?? 0 );
?>
<div class="wscreen wscreen--<?php echo esc_attr( $ssbd_variant ); ?>">

	<?php if ( 0 === $ssbd_variant ) : ?>
		<?php // Marketing site: nav, headline, buttons, three cards. ?>
		<div class="wscreen-nav">
			<span class="wscreen-logo"></span>
			<span class="wscreen-menu"><i></i><i></i><i></i></span>
			<span class="wscreen-btn"></span>
		</div>
		<div class="wscreen-hero">
			<div class="wscreen-hero-copy">
				<span class="wscreen-h1"></span>
				<span class="wscreen-h1 is-short"></span>
				<span class="wscreen-p"></span>
				<span class="wscreen-p is-short"></span>
				<span class="wscreen-cta"><?php esc_html_e( 'Get started', 'ssbd' ); ?></span>
			</div>
			<div class="wscreen-hero-art"><i></i><i></i></div>
		</div>
		<div class="wscreen-cards"><span></span><span></span><span></span></div>

	<?php elseif ( 1 === $ssbd_variant ) : ?>
		<?php // Admin dashboard: sidebar, stat row, table. ?>
		<div class="wscreen-app">
			<div class="wscreen-side"><i></i><i class="is-on"></i><i></i><i></i><i></i></div>
			<div class="wscreen-main">
				<div class="wscreen-stats">
					<span><b>2,480</b><i></i></span>
					<span><b>৳ 96k</b><i></i></span>
					<span><b>18</b><i></i></span>
				</div>
				<div class="wscreen-table">
					<span class="is-head"></span>
					<span></span><span></span><span></span>
					<span></span><span></span><span></span>
				</div>
			</div>
		</div>

	<?php else : ?>
		<?php // Storefront: search bar and a product grid. ?>
		<div class="wscreen-nav">
			<span class="wscreen-logo"></span>
			<span class="wscreen-search"></span>
			<span class="wscreen-cart"></span>
		</div>
		<div class="wscreen-grid">
			<span><i></i></span><span><i></i></span><span><i></i></span>
			<span><i></i></span><span><i></i></span><span><i></i></span>
		</div>
	<?php endif; ?>

</div>
