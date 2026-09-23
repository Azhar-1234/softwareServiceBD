<?php
/**
 * A secondary story beside the lead — thumbnail left, headline right.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'side-story' ); ?>>
	<a class="side-story-thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'ssbd-thumb', array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '' ) ); ?>
		<?php else : ?>
			<span class="thumb-fallback" aria-hidden="true"></span>
		<?php endif; ?>
	</a>

	<div class="side-story-body">
		<?php get_template_part( 'template-parts/blog/meta', null, array( 'time' => false ) ); ?>
		<h3 class="side-story-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
	</div>
</article>
