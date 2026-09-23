<?php
/**
 * Single post, as an article reading page.
 *
 * The order is deliberate: category, headline, standfirst, byline, image, then
 * the piece itself beside a contents rail. Everything that sells comes after
 * the reading is done — one closing CTA, not a CTA between every section.
 *
 * The newsletter is not repeated here; the footer carries one on every page.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$ssbd_answer = get_post_meta( get_the_ID(), '_ssbd_answer_summary', true );

	/*
	 * Run the content filters once, then pass the result through the heading
	 * pass. the_content() is not used because the contents rail needs the same
	 * rendered HTML the article body gets — filtering it twice would run every
	 * shortcode and embed a second time.
	 */
	$ssbd_article  = ssbd_article_toc( apply_filters( 'the_content', get_the_content() ) );
	$ssbd_headings = $ssbd_article['headings'];
	$ssbd_has_toc  = count( $ssbd_headings ) >= ssbd_toc_threshold();

	/*
	 * The rail carries Latest and Trending as well as the contents, so it earns
	 * its column on almost every article — only a blog holding this one post
	 * has nothing to put there, and that is the single case that still falls
	 * back to a centred one-column read. wp_count_posts() is cached, so this
	 * costs nothing.
	 */
	$ssbd_has_rail = $ssbd_has_toc || (int) wp_count_posts( 'post' )->publish > 1;
	?>
	<article <?php post_class(); ?>>

		<header class="section--hero article-head">
			<div class="wrap article-head-inner">
				<?php ssbd_breadcrumbs(); ?>

				<?php
				$ssbd_categories = array_values( array_filter(
					(array) get_the_category(),
					static function ( $ssbd_cat ) {
						return 'uncategorized' !== $ssbd_cat->slug;
					}
				) );
				?>
				<?php if ( $ssbd_categories ) : ?>
					<a class="article-kicker" href="<?php echo esc_url( get_category_link( $ssbd_categories[0] ) ); ?>">
						<?php echo esc_html( $ssbd_categories[0]->name ); ?>
					</a>
				<?php endif; ?>

				<h1 class="article-title"><?php the_title(); ?></h1>

				<?php if ( $ssbd_answer ) : ?>
					<?php
					/*
					 * The editor's direct-answer summary, mirrored into the Article
					 * JSON-LD as `abstract`. A real standfirst — a separate statement
					 * from the headline rather than a restatement of it.
					 */
					?>
					<p class="article-standfirst"><?php echo esc_html( $ssbd_answer ); ?></p>
				<?php endif; ?>

				<?php $ssbd_author_photo = ssbd_author_photo(); ?>
				<div class="article-byline">
					<?php if ( $ssbd_author_photo ) : ?>
						<img class="article-byline-photo" src="<?php echo esc_url( $ssbd_author_photo ); ?>" alt="" loading="lazy" decoding="async" width="28" height="28">
					<?php endif; ?>
					<span class="article-byline-author"><?php echo esc_html( ssbd_author_name() ); ?></span>

					<div class="entry-meta">
						<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
							<?php
							printf(
								/* translators: %s: publication date */
								esc_html__( 'Published %s', 'ssbd' ),
								esc_html( get_the_date() )
							);
							?>
						</time>

						<?php if ( get_the_modified_date() !== get_the_date() ) : ?>
							<time datetime="<?php echo esc_attr( get_the_modified_date( DATE_W3C ) ); ?>">
								<?php
								printf(
									/* translators: %s: last updated date */
									esc_html__( 'Updated %s', 'ssbd' ),
									esc_html( get_the_modified_date() )
								);
								?>
							</time>
						<?php endif; ?>

						<span><?php
							printf(
								/* translators: %d: minutes */
								esc_html__( '%d min read', 'ssbd' ),
								(int) ssbd_reading_time()
							);
						?></span>
					</div>
				</div>
			</div>
		</header>

		<?php
		/*
		 * A video, when the "Video (instead of the featured image)" field is
		 * set, takes the featured image's place here — the featured image
		 * still exists and still drives the social-share card and search
		 * result, since neither of those can show a video anyway.
		 */
		$ssbd_video = ssbd_article_video_embed();
		?>
		<?php if ( $ssbd_video ) : ?>
			<figure class="article-figure">
				<div class="wrap">
					<?php echo $ssbd_video; // phpcs:ignore WordPress.Security.EscapeOutput -- provider oEmbed markup, not user input. ?>
				</div>
			</figure>
		<?php elseif ( has_post_thumbnail() ) : ?>
			<figure class="article-figure">
				<div class="wrap">
					<?php the_post_thumbnail( 'ssbd-hero', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async' ) ); ?>

					<?php $ssbd_caption = get_the_post_thumbnail_caption(); ?>
					<?php if ( $ssbd_caption ) : ?>
						<figcaption><?php echo esc_html( $ssbd_caption ); ?></figcaption>
					<?php endif; ?>
				</div>
			</figure>
		<?php endif; ?>

		<div class="wrap">
			<?php ssbd_render_ad_slot( 'article_top' ); ?>
		</div>

		<div class="section section--tight">
			<div class="wrap">
				<div class="article-layout<?php echo $ssbd_has_rail ? '' : ' article-layout--full'; ?>">

					<div class="article-body">
						<div class="entry-content">
							<?php
							echo $ssbd_article['content']; // phpcs:ignore WordPress.Security.EscapingOutput -- already through the_content filters.

							wp_link_pages( array(
								'before' => '<nav class="pagination">',
								'after'  => '</nav>',
							) );
							?>
						</div>

						<?php
						/*
						 * The tag pills used to sit here. They were the only
						 * links into the tag archives, and those archives are
						 * withdrawn — see ssbd_retire_tag_archives() in
						 * inc/seo.php. Linking them now would point every
						 * article at forty 410s.
						 */
						?>

						<?php // Only discloses when a paid link actually rendered in the article. ?>
						<?php if ( ssbd_page_has_affiliate_links() ) : ?>
							<div class="article-disclosure"><?php ssbd_affiliate_disclosure(); ?></div>
						<?php endif; ?>

						<?php ssbd_render_ad_slot( 'article_bottom' ); ?>

						<?php get_template_part( 'template-parts/blog/author-box' ); ?>

						<?php get_template_part( 'template-parts/blog/share' ); ?>

						<?php
						/*
						 * The discussion sits here, in the reading column and
						 * directly under Share — where someone who has just
						 * finished the piece already is. It used to be a
						 * full-width section below the FAQ, the related grid
						 * and the Latest list, which is three screens past the
						 * moment anyone wanted to say something.
						 */
						if ( comments_open() || get_comments_number() ) {
							comments_template();
						}
						?>
					</div>

					<?php if ( $ssbd_has_rail ) : ?>
						<?php
						get_template_part( 'template-parts/blog/article-rail', null, array(
							'headings' => $ssbd_has_toc ? $ssbd_headings : array(),
						) );
						?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</article>

	<?php
	// Right after the reading, while the reader may still be confused about
	// something the article covered — not buried after related posts and
	// comments, and not emitted at all when the meta box is empty.
	get_template_part( 'template-parts/components/faq', null, array(
		'faqs' => ssbd_post_faqs(),
	) );

	// Related posts by shared category.
	$ssbd_cat_ids = wp_get_post_categories( get_the_ID() );
	if ( $ssbd_cat_ids ) :
		$ssbd_related = new WP_Query( array(
			'category__in'        => $ssbd_cat_ids,
			'post__not_in'        => array( get_the_ID() ),
			'posts_per_page'      => 3,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		) );

		if ( $ssbd_related->have_posts() ) :
			?>
			<section class="section section--soft" aria-labelledby="related-heading">
				<div class="wrap">
					<?php ssbd_section_head( __( 'You may also like', 'ssbd' ), '', array( 'id' => 'related-heading' ) ); ?>
					<div class="grid grid--3">
						<?php
						while ( $ssbd_related->have_posts() ) {
							$ssbd_related->the_post();
							get_template_part( 'template-parts/components/post-card' );
						}
						?>
					</div>
				</div>
			</section>
			<?php
		endif;
		wp_reset_postdata();
	endif;

	/*
	 * The news-feed close: whatever is newest that this page has not already
	 * used. ssbd_blog_latest() reads the same bookkeeping the rail wrote to, so
	 * nothing here repeats the sidebar, and the section disappears rather than
	 * padding the page out when there is nothing left to show.
	 */
	$ssbd_more = ssbd_blog_latest( 4 );

	if ( $ssbd_more ) :
		?>
		<section class="section section--tight" aria-labelledby="more-heading">
			<div class="wrap">
				<div class="category-block-head">
					<h2 class="category-block-title" id="more-heading"><?php esc_html_e( 'Latest articles', 'ssbd' ); ?></h2>
					<a class="link-cta" href="<?php echo esc_url( ssbd_blog_url() ); ?>">
						<?php esc_html_e( 'All articles', 'ssbd' ); ?> <span class="arrow" aria-hidden="true">&rarr;</span>
					</a>
				</div>

				<div class="story-list">
					<?php
					ssbd_blog_each( $ssbd_more, function () {
						get_template_part( 'template-parts/blog/list-item' );
					} );
					?>
				</div>
			</div>
		</section>
		<?php
	endif;

	// One CTA, after the reading — not between the sections of it.
	get_template_part( 'template-parts/components/cta' );

endwhile;

get_footer();
