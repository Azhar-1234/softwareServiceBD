<?php
/**
 * Category, tag, author and date archives.
 *
 * Built as a destination rather than a dead end. Landing on one category used
 * to leave a reader with a heading, a grid and no way out except the browser's
 * back button, so the page now carries the same topic bar the blog portal uses
 * plus the portal sidebar — two routes to every other category, one at the top
 * and one alongside the list.
 *
 * No post type registers an archive (see inc/post-types.php), so this template
 * only ever runs for post-based archives and the blog furniture always belongs.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ssbd_term        = ssbd_archive_term();
$ssbd_blog_url    = ssbd_blog_url();
$ssbd_description = get_the_archive_description();
$ssbd_total       = (int) $GLOBALS['wp_query']->found_posts;
$ssbd_topics      = ssbd_blog_categories( 12 );

/*
 * The bar is ranked by post count, so a quiet category can fall outside the
 * twelve — which would show a reader every topic except the one they are
 * standing in, and leave nothing marked as current. Append it instead.
 */
if (
	$ssbd_term
	&& 'category' === $ssbd_term->taxonomy
	&& ! in_array( (int) $ssbd_term->term_id, array_map( 'intval', wp_list_pluck( $ssbd_topics, 'term_id' ) ), true )
) {
	$ssbd_topics[] = $ssbd_term;
}
?>

<section class="section--hero blog-masthead">
	<div class="wrap">
		<?php ssbd_breadcrumbs(); ?>

		<div class="blog-masthead-top">
			<div>
				<?php ssbd_eyebrow( ssbd_archive_kind(), true ); ?>
				<h1 class="blog-masthead-title"><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>

				<?php if ( $ssbd_description ) : ?>
					<div class="blog-masthead-lede"><?php echo wp_kses_post( $ssbd_description ); ?></div>
				<?php endif; ?>

				<?php if ( $ssbd_total ) : ?>
					<p class="archive-count">
						<?php
						printf(
							/* translators: %s: number of articles */
							esc_html( _n( '%s article', '%s articles', $ssbd_total, 'ssbd' ) ),
							esc_html( number_format_i18n( $ssbd_total ) )
						);
						?>
					</p>
				<?php endif; ?>
			</div>

			<div class="blog-masthead-search"><?php get_search_form(); ?></div>
		</div>

		<?php
		/*
		 * The same bar the portal carries, and the answer to the question this
		 * page used to leave unanswered: having arrived in one category, where
		 * do you go next? "All" leads back to the blog index, so the bar is
		 * also the way out rather than only a way sideways.
		 */
		?>
		<?php if ( $ssbd_topics ) : ?>
			<nav class="blog-topics" aria-label="<?php esc_attr_e( 'Topics', 'ssbd' ); ?>">
				<a class="blog-topic" href="<?php echo esc_url( $ssbd_blog_url ); ?>">
					<?php esc_html_e( 'All', 'ssbd' ); ?>
				</a>

				<?php foreach ( $ssbd_topics as $ssbd_topic ) : ?>
					<?php $ssbd_is_current = $ssbd_term && (int) $ssbd_term->term_id === (int) $ssbd_topic->term_id; ?>
					<a
						class="blog-topic<?php echo $ssbd_is_current ? ' is-current' : ''; ?>"
						href="<?php echo esc_url( get_category_link( $ssbd_topic ) ); ?>"
						<?php echo $ssbd_is_current ? ' aria-current="page"' : ''; ?>
					>
						<?php echo esc_html( $ssbd_topic->name ); ?>
					</a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
	</div>
</section>

<section class="section section--tight">
	<div class="wrap">
		<div class="blog-columns">
			<div class="blog-main">
				<?php if ( have_posts() ) : ?>
					<div class="story-list">
						<?php
						while ( have_posts() ) {
							the_post();
							get_template_part( 'template-parts/blog/list-item' );
						}
						?>
					</div>

					<?php ssbd_pagination(); ?>

				<?php else : ?>
					<?php
					/*
					 * An empty archive is nearly always a stale link or a
					 * search with no hits, so it offers the two things that
					 * actually help — the way back to everything, and the
					 * topics that do have something in them — rather than
					 * announcing that nothing is here and stopping.
					 */
					?>
					<div class="archive-empty">
						<h2><?php esc_html_e( 'Nothing filed here yet', 'ssbd' ); ?></h2>
						<p><?php esc_html_e( 'This topic has no published articles at the moment. The rest of the blog is a click away.', 'ssbd' ); ?></p>
						<a class="btn btn--primary" href="<?php echo esc_url( $ssbd_blog_url ); ?>">
							<?php esc_html_e( 'Read the latest articles', 'ssbd' ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<?php get_template_part( 'template-parts/blog/sidebar' ); ?>
		</div>
	</div>
</section>

<?php
get_footer();
