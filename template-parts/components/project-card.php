<?php
/**
 * A single portfolio project card.
 *
 * @package ssbd
 *
 * @var array $args['project']
 */

defined( 'ABSPATH' ) || exit;

$ssbd_project = $args['project'] ?? array();

if ( empty( $ssbd_project['name'] ) ) {
	return;
}

$ssbd_filters = implode( ' ', (array) ( $ssbd_project['filters'] ?? array() ) );
?>
<article class="card project-card" data-filters="<?php echo esc_attr( $ssbd_filters ); ?>">
	<div class="project-thumb">
		<?php if ( ! empty( $ssbd_project['image'] ) ) : ?>
			<?php
			/*
			 * A described image, not a decorative one. The post and blog cards
			 * next to this deliberately carry alt="" because their thumbnail
			 * sits inside an aria-hidden link whose headline already names the
			 * destination — describing it there would make a screen reader
			 * announce the same link twice. This card is different: the
			 * screenshot is the work being shown, it is not inside a link, and
			 * it is the one image on the site with a real chance in image
			 * search. So it gets a description.
			 *
			 * The Media Library wins whenever someone has written alt text
			 * there. The fallback is composed from facts the card already
			 * displays — never invented, so it cannot describe the wrong
			 * thing.
			 */
			$ssbd_alt = trim( (string) get_post_meta( $ssbd_project['image'], '_wp_attachment_image_alt', true ) );

			if ( '' === $ssbd_alt ) {
				$ssbd_alt = trim( implode( ' — ', array_filter( array(
					$ssbd_project['name'] ?? '',
					$ssbd_project['badge'] ?? $ssbd_project['tech'] ?? '',
				) ) ) );
			}

			// wp_get_attachment_image() builds a srcset from every registered
			// size sharing ssbd-card's aspect ratio (see inc/setup.php), so a
			// narrow grid column downloads ssbd-card-sm instead of the full
			// 720x450 file.
			echo wp_get_attachment_image( $ssbd_project['image'], 'ssbd-card', false, array(
				'alt'      => $ssbd_alt,
				'loading'  => 'lazy',
				'decoding' => 'async',
				'sizes'    => '(max-width: 640px) 100vw, 398px',
			) );
			?>
		<?php else : ?>
			<?php echo esc_html( $ssbd_project['name'] ); ?>
		<?php endif; ?>
		<?php if ( ! empty( $ssbd_project['badge'] ) ) : ?>
			<span class="badge badge--light"><?php echo esc_html( $ssbd_project['badge'] ); ?></span>
		<?php endif; ?>
		<?php if ( ! empty( $ssbd_project['tag'] ) ) : ?>
			<span class="badge badge--accent"><?php echo esc_html( $ssbd_project['tag'] ); ?></span>
		<?php endif; ?>
	</div>
	<div class="project-body">
		<h3>
			<?php if ( ! empty( $ssbd_project['url'] ) ) : ?>
				<a href="<?php echo esc_url( $ssbd_project['url'] ); ?>" rel="noopener"><?php echo esc_html( $ssbd_project['name'] ); ?></a>
			<?php else : ?>
				<?php echo esc_html( $ssbd_project['name'] ); ?>
			<?php endif; ?>
		</h3>
		<p><?php echo esc_html( $ssbd_project['desc'] ); ?></p>
		<?php if ( ! empty( $ssbd_project['tech'] ) ) : ?>
			<span class="num"><?php echo esc_html( $ssbd_project['tech'] ); ?></span>
		<?php endif; ?>
	</div>
</article>
