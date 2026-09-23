<?php
/**
 * The meta line under a headline: category, date, reading time.
 *
 * One partial rather than four copies, because every card shape on the portal
 * shows the same three facts and they must stay in the same order everywhere.
 *
 * @package ssbd
 *
 * @var bool $args['cat']  Show the category link. Default true.
 * @var bool $args['time'] Show the reading time. Default true.
 */

defined( 'ABSPATH' ) || exit;

$ssbd_show_cat  = $args['cat'] ?? true;
$ssbd_show_time = $args['time'] ?? true;

// The default "Uncategorized" says nothing about the article, so it is skipped
// rather than printed as if it were a section.
$ssbd_cats = array_values( array_filter(
	(array) get_the_category(),
	static function ( $ssbd_cat ) {
		return 'uncategorized' !== $ssbd_cat->slug;
	}
) );
?>
<div class="entry-meta">
	<?php if ( $ssbd_show_cat && $ssbd_cats ) : ?>
		<a class="entry-meta-cat" href="<?php echo esc_url( get_category_link( $ssbd_cats[0] ) ); ?>"><?php echo esc_html( $ssbd_cats[0]->name ); ?></a>
	<?php endif; ?>

	<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>

	<?php if ( $ssbd_show_time ) : ?>
		<span><?php
			printf(
				/* translators: %d: minutes */
				esc_html__( '%d min read', 'ssbd' ),
				(int) ssbd_reading_time()
			);
		?></span>
	<?php endif; ?>
</div>
