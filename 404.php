<?php
/**
 * 404.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="section" style="min-height:52vh;display:grid;place-items:center">
	<div class="wrap wrap--narrow" style="text-align:center">
		<span class="num" style="font-size:14px">404</span>
		<h1 style="margin:12px 0 0;font-size:clamp(30px,4vw,46px)"><?php esc_html_e( 'That page does not exist', 'ssbd' ); ?></h1>
		<p style="margin:16px auto 0;max-width:460px;color:var(--muted)"><?php esc_html_e( 'It may have moved when we rebuilt the site. Try one of these instead.', 'ssbd' ); ?></p>

		<div class="filter-tabs" style="margin-top:28px">
			<?php foreach ( array( 'services', 'solutions', 'portfolio', 'resources', 'contact' ) as $ssbd_slug ) : ?>
				<a class="filter-tab" style="display:grid;place-items:center;text-decoration:none" href="<?php echo esc_url( ssbd_page_url( $ssbd_slug ) ); ?>">
					<?php echo esc_html( ssbd_page_blueprint()[ $ssbd_slug ]['title'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>

		<div style="margin-top:32px;max-width:420px;margin-inline:auto"><?php get_search_form(); ?></div>
	</div>
</section>

<?php
get_footer();
