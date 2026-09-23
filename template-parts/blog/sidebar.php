<?php
/**
 * Blog sidebar: search, trending, categories, newsletter.
 *
 * Sticky on desktop so it stays beside a long Latest column; on narrow screens
 * CSS drops it below the list, where it reads as a set of ordinary sections.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

$ssbd_trending   = ssbd_blog_trending( 5 );
$ssbd_categories = ssbd_blog_categories( 8 );
?>
<aside class="blog-sidebar" aria-label="<?php esc_attr_e( 'More from the blog', 'ssbd' ); ?>">

	<section class="sidebar-block">
		<h2 class="sidebar-title"><?php esc_html_e( 'Search', 'ssbd' ); ?></h2>
		<?php get_search_form(); ?>
	</section>

	<?php if ( $ssbd_trending ) : ?>
		<section class="sidebar-block">
			<h2 class="sidebar-title"><?php esc_html_e( 'Trending', 'ssbd' ); ?></h2>
			<ol class="trending-list">
				<?php
				ssbd_blog_each( $ssbd_trending, function ( $i ) {
					?>
					<li class="trending-item">
						<span class="trending-rank" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</li>
					<?php
				} );
				?>
			</ol>
		</section>
	<?php endif; ?>

	<?php if ( $ssbd_categories ) : ?>
		<section class="sidebar-block">
			<h2 class="sidebar-title"><?php esc_html_e( 'Topics', 'ssbd' ); ?></h2>
			<ul class="topic-list">
				<?php foreach ( $ssbd_categories as $ssbd_cat ) : ?>
					<li>
						<a href="<?php echo esc_url( get_category_link( $ssbd_cat ) ); ?>">
							<span><?php echo esc_html( $ssbd_cat->name ); ?></span>
							<span class="topic-count"><?php echo esc_html( number_format_i18n( $ssbd_cat->count ) ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<?php // No newsletter block here either: the footer carries one site-wide. ?>

	<?php ssbd_render_ad_slot( 'sidebar' ); ?>
</aside>
