<?php
/**
 * Interior page hero.
 *
 * Breadcrumbs, eyebrow, H1, lede. That is the whole hero, deliberately.
 *
 * The lede comes from inc/data/answers.php, so it is simultaneously the
 * page's opening copy and its GEO answer passage — one statement rather than
 * a headline, a lede, and a bordered card all restating the same idea.
 *
 * @package ssbd
 *
 * An interior page shows its featured image here when one is set, as a real
 * <figure> with the caption underneath. Nothing is rendered when there is no
 * image: a placeholder would be page weight buying nothing, and the schema
 * below only claims a primary image when a visible one exists.
 *
 * @var string $args['eyebrow']
 * @var string $args['heading']
 * @var string $args['lede']       Optional override; normally comes from answers.php.
 * @var string $args['answer_key']
 * @var bool   $args['image']      Set false where the underlying post is shared
 *                                 by several URLs, so they do not all show the
 *                                 same picture.
 */

defined( 'ABSPATH' ) || exit;

$ssbd_eyebrow = $args['eyebrow'] ?? '';
$ssbd_heading = $args['heading'] ?? get_the_title();
$ssbd_answer  = $args['answer_key'] ?? '';
$ssbd_lede    = $ssbd_answer ? ssbd_lede( $ssbd_answer, $args['lede'] ?? '' ) : ( $args['lede'] ?? '' );

$ssbd_show_image = ( $args['image'] ?? true ) && is_singular() && has_post_thumbnail();
$ssbd_caption    = $ssbd_show_image ? get_the_post_thumbnail_caption() : '';
?>
<section class="section--hero page-hero">
	<div class="wrap">
		<?php ssbd_breadcrumbs(); ?>

		<?php if ( $ssbd_eyebrow ) : ?>
			<div class="page-hero-eyebrow"><?php ssbd_eyebrow( $ssbd_eyebrow, true ); ?></div>
		<?php endif; ?>

		<h1><?php echo esc_html( $ssbd_heading ); ?></h1>

		<?php if ( $ssbd_lede ) : ?>
			<p class="lede"><?php echo esc_html( $ssbd_lede ); ?></p>
		<?php endif; ?>

		<?php if ( $ssbd_show_image ) : ?>
			<?php
			/*
			 * Above the fold, so it is almost always the LCP element: eager,
			 * high priority, and never lazy — lazy-loading the LCP image is
			 * the single most common way a good Lighthouse score is lost.
			 * The alt attribute is whatever the Media Library holds; the
			 * theme does not invent one, because a wrong description is
			 * worse for a screen reader than an empty one.
			 */
			?>
			<figure class="page-hero-figure">
				<?php
				the_post_thumbnail( 'ssbd-hero', array(
					'loading'       => 'eager',
					'fetchpriority' => 'high',
					'decoding'      => 'async',
				) );
				?>

				<?php if ( $ssbd_caption ) : ?>
					<figcaption><?php echo wp_kses_post( $ssbd_caption ); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endif; ?>
	</div>
</section>
