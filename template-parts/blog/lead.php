<?php
/**
 * The lead story — the one big card at the top of the portal.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'lead-story' ); ?>>
	<a class="lead-story-thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'ssbd-hero', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async', 'alt' => '' ) ); ?>
		<?php else : ?>
			<span class="thumb-fallback" aria-hidden="true"></span>
		<?php endif; ?>
	</a>

	<div class="lead-story-body">
		<?php get_template_part( 'template-parts/blog/meta' ); ?>
		<h2 class="lead-story-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p class="lead-story-excerpt"><?php echo esc_html( wp_html_excerpt( wp_strip_all_tags( get_the_excerpt() ), 190, '…' ) ); ?></p>
	</div>
</article>
