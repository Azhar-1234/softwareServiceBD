<?php
/**
 * One category row: a heading, three stories, and a link to the rest.
 *
 * Renders nothing when the category has no posts, so a section can never head
 * an empty strip.
 *
 * @package ssbd
 *
 * @var WP_Term $args['term']
 * @var int     $args['count']
 */

defined( 'ABSPATH' ) || exit;

$ssbd_term = $args['term'] ?? null;

if ( ! $ssbd_term ) {
	return;
}

$ssbd_ids = ssbd_blog_category_posts( $ssbd_term->term_id, (int) ( $args['count'] ?? 3 ) );

if ( ! $ssbd_ids ) {
	return;
}
?>
<section class="category-block" aria-labelledby="cat-<?php echo esc_attr( $ssbd_term->slug ); ?>">
	<div class="category-block-head">
		<h2 class="category-block-title" id="cat-<?php echo esc_attr( $ssbd_term->slug ); ?>">
			<?php echo esc_html( $ssbd_term->name ); ?>
		</h2>
		<a class="link-cta" href="<?php echo esc_url( get_category_link( $ssbd_term ) ); ?>">
			<?php esc_html_e( 'View all', 'ssbd' ); ?> <span class="arrow" aria-hidden="true">→</span>
		</a>
	</div>

	<div class="grid grid--3">
		<?php
		ssbd_blog_each( $ssbd_ids, function () {
			get_template_part( 'template-parts/components/post-card' );
		} );
		?>
	</div>
</section>
