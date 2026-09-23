<?php
/**
 * Table of contents, built from the article's own H2s and H3s.
 *
 * Rendered by PHP with real anchors, so it works with scripting off — the
 * script only adds the current-section highlight and the mobile collapse.
 *
 * @package ssbd
 *
 * @var array $args['headings'] From ssbd_article_toc().
 */

defined( 'ABSPATH' ) || exit;

$ssbd_headings = $args['headings'] ?? array();

if ( count( $ssbd_headings ) < ssbd_toc_threshold() ) {
	return;
}
?>
<nav class="toc" data-toc aria-labelledby="toc-heading">
	<div class="toc-head">
		<h2 class="toc-title" id="toc-heading"><?php esc_html_e( 'On this page', 'ssbd' ); ?></h2>

		<?php // Revealed by article.js; without it the list simply stays open. ?>
		<button type="button" class="toc-toggle" data-toc-toggle aria-expanded="true" hidden>
			<span class="toc-toggle-mark" aria-hidden="true"></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Toggle table of contents', 'ssbd' ); ?></span>
		</button>
	</div>

	<ol class="toc-list" data-toc-panel>
		<?php foreach ( $ssbd_headings as $ssbd_heading ) : ?>
			<li class="toc-item toc-item--h<?php echo (int) $ssbd_heading['level']; ?>">
				<a href="#<?php echo esc_attr( $ssbd_heading['id'] ); ?>"><?php echo esc_html( $ssbd_heading['text'] ); ?></a>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
