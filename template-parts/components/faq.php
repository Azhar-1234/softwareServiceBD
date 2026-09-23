<?php
/**
 * FAQ accordion.
 *
 * Rendered as native <details>/<summary>, so every answer is in the DOM and
 * readable with JavaScript off. The React island upgrades it to a
 * single-open accordion with animation; the content never changes.
 *
 * Also registers the FAQPage JSON-LD for these exact questions.
 *
 * @package ssbd
 *
 * @var array  $args['faqs']    FAQ pairs.
 * @var string $args['heading'] Section heading.
 */

defined( 'ABSPATH' ) || exit;

$ssbd_faqs = $args['faqs'] ?? array();

if ( empty( $ssbd_faqs ) ) {
	return;
}

$ssbd_heading = $args['heading'] ?? __( 'Frequently asked questions', 'ssbd' );

// The questions are visible on this page, so the markup is legitimate.
ssbd_schema_add( ssbd_schema_faq( $ssbd_faqs ) );
?>
<section class="section section--soft" aria-labelledby="faq-heading">
	<div class="wrap">
		<?php ssbd_section_head( $ssbd_heading, '', array( 'id' => 'faq-heading' ) ); ?>

		<?php
		ssbd_island( 'faq', array( 'count' => count( $ssbd_faqs ) ), function () use ( $ssbd_faqs ) {
			echo '<div class="faq">';
			foreach ( $ssbd_faqs as $i => $faq ) {
				?>
				<details class="faq-item"<?php echo 0 === $i ? ' open' : ''; ?>>
					<summary class="faq-q">
						<span><?php echo esc_html( $faq['q'] ); ?></span>
						<span class="plus" aria-hidden="true">+</span>
					</summary>
					<p class="faq-a"><?php echo wp_kses( $faq['a'], ssbd_faq_allowed_html() ); ?></p>
				</details>
				<?php
			}
			echo '</div>';
		} );
		?>
	</div>
</section>
