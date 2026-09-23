<?php
/**
 * The blog index, as a news portal rather than a list of cards.
 *
 * WordPress picks this ahead of index.php for the Posts page, so index.php
 * stays the plain fallback for everything else.
 *
 * The editorial shape is: one lead story, three beside it, a Latest column
 * with a sidebar, then a row per category. The newsletter is deliberately not
 * repeated here — the footer carries one on every page. Page two and beyond
 * drop all of that and render a straight chronological list — the
 * furniture is an entrance, and repeating it on every page of the archive
 * would put the same lead story above every page of results.
 *
 * Every section hides itself when it has nothing to show, so a blog with three
 * posts looks deliberate rather than broken.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ssbd_blog_id = (int) get_option( 'page_for_posts' );
$ssbd_title   = ssbd_blog_label();
$ssbd_paged   = is_paged();
?>

<section class="section--hero blog-masthead">
	<div class="wrap">
		<?php ssbd_breadcrumbs(); ?>

		<div class="blog-masthead-top">
			<div>
				<?php // Not a name of its own — the H1 below already names the page. ?>
				<?php ssbd_eyebrow( __( 'Latest', 'ssbd' ), true ); ?>
				<h1 class="blog-masthead-title"><?php echo esc_html( $ssbd_title ); ?></h1>
				<p class="blog-masthead-lede">
					<?php esc_html_e( 'Technology, AI, software and digital business — practical writing from the projects we actually ship.', 'ssbd' ); ?>
				</p>
			</div>

			<div class="blog-masthead-search"><?php get_search_form(); ?></div>
		</div>

		<?php
		// The category bar. Doubles as the portal's section nav and is the only
		// place the full topic list appears above the fold.
		$ssbd_nav_cats = ssbd_blog_categories( 10 );
		?>
		<?php if ( $ssbd_nav_cats ) : ?>
			<nav class="blog-topics" aria-label="<?php esc_attr_e( 'Topics', 'ssbd' ); ?>">
				<a class="blog-topic<?php echo is_home() && ! is_category() ? ' is-current' : ''; ?>" href="<?php echo esc_url( $ssbd_blog_id ? get_permalink( $ssbd_blog_id ) : home_url( '/' ) ); ?>">
					<?php esc_html_e( 'All', 'ssbd' ); ?>
				</a>
				<?php foreach ( $ssbd_nav_cats as $ssbd_cat ) : ?>
					<a class="blog-topic" href="<?php echo esc_url( get_category_link( $ssbd_cat ) ); ?>"><?php echo esc_html( $ssbd_cat->name ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
	</div>
</section>

<?php if ( ! have_posts() ) : ?>

	<section class="section">
		<div class="wrap">
			<div class="card" style="max-width:520px;margin-inline:auto;text-align:center">
				<h2 style="font-size:20px"><?php esc_html_e( 'Nothing published yet', 'ssbd' ); ?></h2>
				<p><?php esc_html_e( 'Articles will appear here once the blog goes live.', 'ssbd' ); ?></p>
			</div>
		</div>
	</section>

<?php elseif ( $ssbd_paged ) : ?>

	<?php // Page 2+: the archive, plainly. ?>
	<section class="section">
		<div class="wrap">
			<div class="grid grid--3">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/components/post-card' );
				}
				?>
			</div>

			<?php ssbd_pagination(); ?>
		</div>
	</section>

<?php else : ?>

	<?php
	$ssbd_featured = ssbd_blog_featured( 4 );
	$ssbd_lead     = array_slice( $ssbd_featured, 0, 1 );
	$ssbd_side     = array_slice( $ssbd_featured, 1 );
	$ssbd_latest   = ssbd_blog_latest( 6 );

	// One or two companion stories cannot fill the rail beside a full-height
	// lead, so the grid switches to a roomier pair of columns.
	$ssbd_sparse = $ssbd_side && count( $ssbd_side ) < 3;
	?>

	<?php if ( $ssbd_lead ) : ?>
		<section class="section section--tight" aria-label="<?php esc_attr_e( 'Featured stories', 'ssbd' ); ?>">
			<div class="wrap">
				<div class="featured-grid<?php echo $ssbd_side ? '' : ' featured-grid--solo'; ?><?php echo $ssbd_sparse ? ' featured-grid--sparse' : ''; ?>">
					<?php ssbd_blog_each( $ssbd_lead, function () {
						get_template_part( 'template-parts/blog/lead' );
					} ); ?>

					<?php if ( $ssbd_side ) : ?>
						<div class="featured-side">
							<?php ssbd_blog_each( $ssbd_side, function () {
								get_template_part( 'template-parts/blog/side' );
							} ); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! is_paged() ) : ?>
		<div class="wrap">
			<?php ssbd_render_ad_slot( 'blog_index' ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $ssbd_latest ) : ?>
		<section class="section section--tight" aria-labelledby="latest-news-heading">
			<div class="wrap">
				<div class="blog-columns">
					<div class="blog-main">
						<div class="category-block-head">
							<h2 class="category-block-title" id="latest-news-heading"><?php esc_html_e( 'Latest', 'ssbd' ); ?></h2>
						</div>

						<div class="story-list">
							<?php ssbd_blog_each( $ssbd_latest, function () {
								get_template_part( 'template-parts/blog/list-item' );
							} ); ?>
						</div>

						<?php if ( ssbd_blog_has_archive() ) : ?>
							<div class="section-more">
								<a class="btn btn--ghost" href="<?php echo esc_url( add_query_arg( 'paged', 2, $ssbd_blog_id ? get_permalink( $ssbd_blog_id ) : home_url( '/' ) ) ); ?>">
									<?php esc_html_e( 'More articles', 'ssbd' ); ?> <span aria-hidden="true">→</span>
								</a>
							</div>
						<?php endif; ?>
					</div>

					<?php get_template_part( 'template-parts/blog/sidebar' ); ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	// One row per category. Skipped entirely on a blog that has not been
	// filed into categories yet, where every row would repeat the same posts.
	$ssbd_blocks = ssbd_blog_categories( 4 );
	?>
	<?php if ( $ssbd_blocks ) : ?>
		<section class="section section--soft">
			<div class="wrap">
				<?php foreach ( $ssbd_blocks as $ssbd_block_term ) : ?>
					<?php get_template_part( 'template-parts/blog/category-block', null, array( 'term' => $ssbd_block_term, 'count' => 3 ) ); ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

<?php endif; ?>

<?php
get_footer();
