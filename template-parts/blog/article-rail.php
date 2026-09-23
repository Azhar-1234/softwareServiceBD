<?php
/**
 * The sidebar beside a single article: contents, latest, trending.
 *
 * The rail used to hold the table of contents and nothing else, which meant an
 * article without enough headings had no rail at all and the reading column
 * was left alone in the middle of a wide page. It now always has somewhere to
 * go next, which is what a reader at the end of a news article is looking for.
 *
 * @package ssbd
 *
 * @var array $args['headings'] Heading list from ssbd_article_toc(), or empty.
 */

defined( 'ABSPATH' ) || exit;

$ssbd_headings = $args['headings'] ?? array();
$ssbd_current  = get_the_ID();

/*
 * Record the article being read before either query runs. That keeps it out of
 * its own sidebar, and — because the bookkeeping is shared for the whole
 * request — stops the Latest articles section further down the page from
 * repeating what the rail has already shown.
 */
ssbd_blog_seen( array( $ssbd_current ) );

$ssbd_latest = ssbd_blog_latest( 5 );

/*
 * Trending ranks by its own signal and does not read the shared bookkeeping,
 * so the overlap is removed here instead: without this the four most-commented
 * posts on a small blog are usually the four just listed under Latest, and the
 * rail shows the same headlines twice, one block apart. Nine are requested so
 * four survive after the current article and the five above it are dropped,
 * and the ranking itself is left alone.
 */
$ssbd_trending = array_slice(
	array_values( array_diff( ssbd_blog_trending( 9 ), ssbd_blog_seen() ) ),
	0,
	4
);

// Recorded in turn, so the Latest articles section at the foot of the page
// moves past these rather than reprinting the rail's second block.
ssbd_blog_seen( $ssbd_trending );
?>
<aside class="article-rail" aria-label="<?php esc_attr_e( 'More from the blog', 'ssbd' ); ?>">

	<?php if ( $ssbd_headings ) : ?>
		<?php get_template_part( 'template-parts/blog/toc', null, array( 'headings' => $ssbd_headings ) ); ?>
	<?php endif; ?>

	<?php if ( $ssbd_latest ) : ?>
		<section class="sidebar-block">
			<h2 class="sidebar-title"><?php esc_html_e( 'Latest articles', 'ssbd' ); ?></h2>

			<div class="rail-stories">
				<?php
				ssbd_blog_each( $ssbd_latest, function () {
					get_template_part( 'template-parts/blog/side' );
				} );
				?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $ssbd_trending ) : ?>
		<section class="sidebar-block">
			<h2 class="sidebar-title"><?php esc_html_e( 'Trending now', 'ssbd' ); ?></h2>

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

	<?php ssbd_render_ad_slot( 'sidebar' ); ?>
</aside>
