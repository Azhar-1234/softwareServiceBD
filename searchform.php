<?php
/**
 * Search form.
 *
 * The blog portal renders this twice on one page — once in the masthead, once
 * in the sidebar — so the field id has to be unique per call. A fixed id would
 * give the document duplicate ids and point the second label at the first
 * input, which sends a screen reader to the wrong box.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

$ssbd_search_n  = ( $GLOBALS['ssbd_search_form_count'] = ( $GLOBALS['ssbd_search_form_count'] ?? 0 ) + 1 );
$ssbd_search_id = 1 === $ssbd_search_n ? 'ssbd-search' : 'ssbd-search-' . $ssbd_search_n;
?>
<form role="search" method="get" class="field" action="<?php echo esc_url( home_url( '/' ) ); ?>" style="flex-direction:row;gap:8px">
	<label class="screen-reader-text" for="<?php echo esc_attr( $ssbd_search_id ); ?>"><?php esc_html_e( 'Search for:', 'ssbd' ); ?></label>
	<input id="<?php echo esc_attr( $ssbd_search_id ); ?>" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search articles…', 'ssbd' ); ?>">
	<button type="submit" class="btn btn--sm btn--accent"><?php esc_html_e( 'Search', 'ssbd' ); ?></button>
</form>
