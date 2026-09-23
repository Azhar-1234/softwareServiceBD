<?php
/**
 * Default page template.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<section class="section--hero page-hero">
		<div class="wrap wrap--narrow">
			<?php ssbd_breadcrumbs(); ?>
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( has_post_thumbnail() ) : ?>
		<div class="wrap" style="padding-inline:var(--gutter);margin-top:-24px">
			<?php the_post_thumbnail( 'full', array( 'style' => 'border-radius:var(--radius-lg)', 'loading' => 'eager' ) ); ?>
		</div>
	<?php endif; ?>

	<section class="section">
		<div class="wrap wrap--narrow entry-content">
			<?php
			the_content();

			wp_link_pages( array(
				'before' => '<nav class="pagination">',
				'after'  => '</nav>',
			) );
			?>
		</div>
	</section>
	<?php
endwhile;

get_footer();
