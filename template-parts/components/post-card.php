<?php
/**
 * Blog post card used on archives, the blog index and search results.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'card post-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="post-card-thumb" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php the_post_thumbnail( 'ssbd-card', array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '' ) ); ?>
		</a>
	<?php endif; ?>

	<?php
	// The hub a post belongs to — Guides, Comparisons, Reviews, Resources —
	// leads the meta line, so the card says what kind of read it is before the
	// title does. The default "Uncategorized" says nothing, so it is skipped.
	$ssbd_cats = array_values( array_filter(
		(array) get_the_category(),
		static function ( $ssbd_cat ) {
			return 'uncategorized' !== $ssbd_cat->slug;
		}
	) );
	?>
	<div class="entry-meta">
		<?php if ( $ssbd_cats ) : ?>
			<a class="entry-meta-cat" href="<?php echo esc_url( get_category_link( $ssbd_cats[0] ) ); ?>"><?php echo esc_html( $ssbd_cats[0]->name ); ?></a>
		<?php endif; ?>
		<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
		<span><?php
			printf(
				/* translators: %d: minutes */
				esc_html__( '%d min read', 'ssbd' ),
				(int) ssbd_reading_time()
			);
		?></span>
	</div>

	<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
	<p><?php echo esc_html( wp_html_excerpt( wp_strip_all_tags( get_the_excerpt() ), 150, '…' ) ); ?></p>

	<span class="link-cta"><?php esc_html_e( 'Read article', 'ssbd' ); ?> <span class="arrow" aria-hidden="true">→</span></span>
</article>
