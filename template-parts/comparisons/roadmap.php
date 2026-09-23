<?php
/**
 * What we are writing next.
 *
 * Deliberately not a table. A table with a "Read" column sets the expectation
 * that every row can be read, and the rows that could not used to say "Being
 * written" in that column — fifteen of them, which reads as a site that
 * promised content and did not deliver it. A short list under a heading that
 * says these are planned makes the smaller, true claim instead.
 *
 * Nothing here is a link: there is nothing to link to yet, and a link to a
 * 404 is the problem this block exists to avoid.
 *
 * @package ssbd
 *
 * @var array  $args['matchups'] Planned matchups, from ssbd_matchups_roadmap().
 * @var string $args['heading']  Optional heading override.
 */

defined( 'ABSPATH' ) || exit;

$ssbd_planned = $args['matchups'] ?? array();

if ( ! $ssbd_planned ) {
	return;
}

$ssbd_heading = $args['heading'] ?? __( 'What we are writing next', 'ssbd' );
$ssbd_head_id = 'roadmap-heading';
?>
<div class="roadmap" aria-labelledby="<?php echo esc_attr( $ssbd_head_id ); ?>">
	<h3 class="roadmap-title" id="<?php echo esc_attr( $ssbd_head_id ); ?>"><?php echo esc_html( $ssbd_heading ); ?></h3>

	<ul class="roadmap-list">
		<?php foreach ( $ssbd_planned as $ssbd_item ) : ?>
			<li class="roadmap-item">
				<span class="roadmap-item-title"><?php echo esc_html( $ssbd_item['title'] ); ?></span>
				<span class="roadmap-item-intent"><?php echo esc_html( $ssbd_item['intent'] ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>

	<p class="roadmap-note">
		<?php
		printf(
			/* translators: %s: link to the newsletter sign-up */
			esc_html__( 'These are in the queue, not published yet. %s to get each one as it lands.', 'ssbd' ),
			'<a href="#subscribe">' . esc_html__( 'Join the newsletter', 'ssbd' ) . '</a>'
		);
		?>
	</p>
</div>
