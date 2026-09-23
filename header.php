<?php
/**
 * Document head and site header.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="https://gmpg.org/xfn/11">
<link rel="icon" type="image/png" href="<?php echo esc_url( SSBD_URI . '/assets/images/logo-icon.png' ); ?>">
<link rel="apple-touch-icon" href="<?php echo esc_url( SSBD_URI . '/assets/images/icon-192.png' ); ?>">
<script>
/* Resolve the colour theme before first paint so the page never flashes light
   then swaps to dark. Mirrors the logic in assets/js/src/theme.js. */
(function () {
	var root = document.documentElement;
	root.classList.remove('no-js');
	try {
		var saved = localStorage.getItem('ssbd-theme');
		var dark = saved ? saved === 'dark'
			: window.matchMedia('(prefers-color-scheme: dark)').matches;
		root.setAttribute('data-theme', dark ? 'dark' : 'light');
	} catch (e) {
		root.setAttribute('data-theme', 'light');
	}
})();
</script>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'ssbd' ); ?></a>

<header class="site-header">
	<?php ssbd_brand(); ?>

	<nav class="site-nav" id="site-nav" aria-label="<?php esc_attr_e( 'Primary', 'ssbd' ); ?>">
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			// Depth is unlimited so nested items render as dropdowns; the
			// services can fill their own — see inc/navigation.php.
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'depth'          => 0,
				'fallback_cb'    => false,
			) );
		} else {
			// Before the site owner builds a menu, link the shipped pages.
			ssbd_fallback_nav();
		}
		?>
	</nav>

	<div class="header-actions">
		<?php
		// React replaces this button; the server-rendered version is inert
		// but present, so the header never reflows when the bundle lands.
		ssbd_island( 'theme-toggle', array(), function () {
			?>
			<button type="button" class="theme-toggle" aria-label="<?php esc_attr_e( 'Toggle day / night theme', 'ssbd' ); ?>">
				<span class="icon" aria-hidden="true">◐</span>
				<span class="label"><?php esc_html_e( 'Night', 'ssbd' ); ?></span>
			</button>
			<?php
		}, array( 'tag' => 'div' ) );
		?>

		<a class="btn btn--sm btn--accent btn--contact" href="<?php echo esc_url( ssbd_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact', 'ssbd' ); ?></a>

		<?php get_template_part( 'template-parts/components/account-menu' ); ?>

		<button type="button" class="nav-toggle" aria-controls="site-nav" aria-expanded="false" aria-label="<?php esc_attr_e( 'Menu', 'ssbd' ); ?>" data-ssbd-nav-toggle>
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
		</button>
	</div>
</header>

<main id="main">
