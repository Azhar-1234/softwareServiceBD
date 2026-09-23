<?php
/**
 * One row of the Latest column — image left, headline and standfirst right.
 *
 * A list rather than a grid, because a reader scanning "what is new" reads
 * down a column far faster than across a set of tiles.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'story-row' ); ?>>
	<a class="story-row-thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'ssbd-card', array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '' ) ); ?>
		<?php else : ?>
			<span class="thumb-fallback" aria-hidden="true"></span>
		<?php endif; ?>
	</a>

	<div class="story-row-body">
		<?php get_template_part( 'template-parts/blog/meta' ); ?>
		<h3 class="story-row-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p class="story-row-excerpt"><?php echo esc_html( wp_html_excerpt( wp_strip_all_tags( get_the_excerpt() ), 130, '…' ) ); ?></p>
	</div>
</article>
