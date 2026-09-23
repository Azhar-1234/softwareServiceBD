<?php
/**
 * Search results.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="section--hero page-hero">
	<div class="wrap">
		<?php ssbd_breadcrumbs(); ?>
		<div style="margin-top:18px"><?php ssbd_eyebrow( __( 'Search', 'ssbd' ), true ); ?></div>
		<h1>
			<?php
			printf(
				/* translators: %s: search query */
				esc_html__( 'Results for “%s”', 'ssbd' ),
				esc_html( get_search_query() )
			);
			?>
		</h1>
		<p>
			<?php
			printf(
				/* translators: %d: result count */
				esc_html( _n( '%d result', '%d results', (int) $GLOBALS['wp_query']->found_posts, 'ssbd' ) ),
				(int) $GLOBALS['wp_query']->found_posts
			);
			?>
		</p>
		<div style="margin-top:24px;max-width:480px"><?php get_search_form(); ?></div>
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
				<h2 style="font-size:20px"><?php esc_html_e( 'No matches', 'ssbd' ); ?></h2>
				<p><?php esc_html_e( 'Try a different term, or tell us what you were looking for.', 'ssbd' ); ?></p>
				<a class="btn btn--sm btn--accent" style="align-self:center" href="<?php echo esc_url( ssbd_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact us', 'ssbd' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
