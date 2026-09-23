<?php
/**
 * Latest blog posts on the front page.
 *
 * Renders nothing when the blog is empty, so a brand-new site does not show
 * an orphaned heading over an empty grid.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

$ssbd_ids = ssbd_home_post_ids();

if ( ! $ssbd_ids ) {
	return;
}

// The blog page, and whether it holds anything the three cards below do not.
// A "view all" that leads to the same three posts is worse than no button, so
// it only renders when there is genuinely more behind it. wp_count_posts() is
// a cached count, not another query, and it counts the whole blog because that
// is what the button opens — the category filter above only narrows what the
// front page itself lists.
$ssbd_blog_id  = (int) get_option( 'page_for_posts' );
$ssbd_blog_url = $ssbd_blog_id ? get_permalink( $ssbd_blog_id ) : '';
$ssbd_has_more = (int) wp_count_posts( 'post' )->publish > count( $ssbd_ids );

// Resolved in inc/front-page.php so the pinned-first ordering survives here.
$ssbd_latest = new WP_Query( array(
	'post__in'            => $ssbd_ids,
	'orderby'             => 'post__in',
	'posts_per_page'      => count( $ssbd_ids ),
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
) );
?>
<section class="section" aria-labelledby="latest-heading">
	<div class="wrap">
		<?php ssbd_section_head( ssbd_section_heading( 'posts' ), ssbd_section_lede( 'posts' ), array( 'id' => 'latest-heading' ) ); ?>

		<div class="grid grid--3">
			<?php
			while ( $ssbd_latest->have_posts() ) {
				$ssbd_latest->the_post();
				get_template_part( 'template-parts/components/post-card' );
			}
			?>
		</div>

		<?php if ( $ssbd_blog_url && $ssbd_has_more ) : ?>
			<div class="section-more">
				<a class="btn btn--ghost" href="<?php echo esc_url( $ssbd_blog_url ); ?>">
					<?php echo esc_html( ssbd_blog_link_label() ); ?> <span aria-hidden="true">→</span>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
wp_reset_postdata();
