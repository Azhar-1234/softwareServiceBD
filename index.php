<?php
/**
 * Fallback template — also serves the blog index.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ssbd_blog_id = (int) get_option( 'page_for_posts' );
?>

<section class="section--hero page-hero">
	<div class="wrap">
		<?php ssbd_breadcrumbs(); ?>
		<div style="margin-top:18px"><?php ssbd_eyebrow( __( 'Blog', 'ssbd' ), true ); ?></div>
		<h1><?php echo esc_html( $ssbd_blog_id ? get_the_title( $ssbd_blog_id ) : __( 'Articles', 'ssbd' ) ); ?></h1>
		<p><?php esc_html_e( 'Practical writing on web development, custom software, AI automation and digital growth — from projects we actually shipped.', 'ssbd' ); ?></p>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<?php if ( have_posts() ) : ?>
			<div class="grid grid--3">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/components/post-card' );
				}
				?>
			</div>

			<?php ssbd_pagination(); ?>
		<?php else : ?>
			<div class="card" style="max-width:520px;margin-inline:auto;text-align:center">
				<h2 style="font-size:20px"><?php esc_html_e( 'Nothing published yet', 'ssbd' ); ?></h2>
				<p><?php esc_html_e( 'Articles will appear here once the blog goes live.', 'ssbd' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
get_template_part( 'template-parts/components/cta' );

get_footer();
