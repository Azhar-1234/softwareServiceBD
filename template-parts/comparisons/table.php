<?php
/**
 * The comparison table: toolbar, table, pagination.
 *
 * A real <table> with column headers, row headers and aria-sort — sortable
 * data belongs in one, and screen readers and answer engines both read it far
 * better than a grid of cards. On narrow screens CSS restacks each row into a
 * labelled block; the markup does not change, so nothing is duplicated.
 *
 * Every row is in the HTML. Search, sort, filter, pagination and export are
 * progressive enhancement from assets/js/facets.js: with JavaScript off the
 * whole table is there and the controls are hidden, because a control that
 * cannot work is worse than none.
 *
 * @package ssbd
 *
 * Every row here is a published comparison. Planned ones are not rows: they
 * render as a roadmap block beside the table (comparisons/roadmap.php), so the
 * Read column never has to say "Being written".
 *
 * @var array $args['matchups']    Published rows, from ssbd_matchups_published().
 * @var array $args['facets']      Facet definitions.
 * @var array $args['pillars']     Pillar list, for the category column and filter.
 * @var bool  $args['show_pillar'] False on a pillar page, where it is a constant.
 */

defined( 'ABSPATH' ) || exit;

$ssbd_rows    = $args['matchups'] ?? array();
$ssbd_facets  = $args['facets'] ?? array();
$ssbd_pillars = $args['pillars'] ?? array();
$ssbd_show    = (bool) ( $args['show_pillar'] ?? true );

if ( ! $ssbd_rows ) {
	return;
}

$ssbd_labels = wp_list_pluck( $ssbd_pillars, 'label', 'key' );
$ssbd_id     = 'comparison-table';
?>
<div class="ctable" data-comparison-table>

	<div class="ctable-toolbar" data-ctable-controls hidden>
		<div class="ctable-search">
			<label class="screen-reader-text" for="ctable-search"><?php esc_html_e( 'Search comparisons', 'ssbd' ); ?></label>
			<input type="search" id="ctable-search" data-ctable-search
				placeholder="<?php esc_attr_e( 'Search — “hosting”, “SEO plugin”, “email”…', 'ssbd' ); ?>"
				autocomplete="off">
		</div>

		<?php if ( $ssbd_show ) : ?>
			<div class="facet-row" data-facet="pillar">
				<span class="facet-label"><?php esc_html_e( 'Category', 'ssbd' ); ?></span>
				<button type="button" class="filter-tab" data-value="any" aria-pressed="true"><?php esc_html_e( 'All', 'ssbd' ); ?></button>
				<?php foreach ( $ssbd_pillars as $ssbd_pillar ) : ?>
					<button type="button" class="filter-tab" data-value="<?php echo esc_attr( $ssbd_pillar['key'] ); ?>" aria-pressed="false">
						<?php echo esc_html( $ssbd_pillar['label'] ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="facet-row" data-facet="price">
			<span class="facet-label"><?php esc_html_e( 'Price', 'ssbd' ); ?></span>
			<button type="button" class="filter-tab" data-value="any" aria-pressed="true"><?php esc_html_e( 'Any', 'ssbd' ); ?></button>
			<?php foreach ( $ssbd_facets['price'] as $ssbd_key => $ssbd_label ) : ?>
				<button type="button" class="filter-tab" data-value="<?php echo esc_attr( $ssbd_key ); ?>" aria-pressed="false"><?php echo esc_html( $ssbd_label ); ?></button>
			<?php endforeach; ?>
		</div>

		<div class="facet-row" data-facet="audience">
			<span class="facet-label"><?php esc_html_e( 'Built for', 'ssbd' ); ?></span>
			<button type="button" class="filter-tab" data-value="any" aria-pressed="true"><?php esc_html_e( 'Anyone', 'ssbd' ); ?></button>
			<?php foreach ( $ssbd_facets['audience'] as $ssbd_key => $ssbd_label ) : ?>
				<button type="button" class="filter-tab" data-value="<?php echo esc_attr( $ssbd_key ); ?>" aria-pressed="false"><?php echo esc_html( $ssbd_label ); ?></button>
			<?php endforeach; ?>
		</div>

		<div class="ctable-status">
			<p class="facet-count" role="status" aria-live="polite">
				<span data-facet-count><?php echo count( $ssbd_rows ); ?></span>
				<?php esc_html_e( 'comparisons', 'ssbd' ); ?>
			</p>

			<div class="ctable-actions">
				<button type="button" class="btn btn--sm btn--ghost" data-ctable-reset hidden><?php esc_html_e( 'Clear filters', 'ssbd' ); ?></button>
				<button type="button" class="btn btn--sm btn--ghost" data-ctable-export><?php esc_html_e( 'Export CSV', 'ssbd' ); ?></button>
			</div>
		</div>
	</div>

	<div class="table-scroll">
		<table class="compare-table ctable-table" id="<?php echo esc_attr( $ssbd_id ); ?>">
			<caption class="screen-reader-text">
				<?php esc_html_e( 'Tool comparisons, with the search each one answers, its category, price band and who it suits.', 'ssbd' ); ?>
			</caption>

			<thead>
				<tr>
					<th scope="col" data-ctable-sort="title" aria-sort="none">
						<button type="button"><?php esc_html_e( 'Comparison', 'ssbd' ); ?><span class="sort-arrow" aria-hidden="true"></span></button>
					</th>
					<?php if ( $ssbd_show ) : ?>
						<th scope="col" data-ctable-sort="pillar" aria-sort="none">
							<button type="button"><?php esc_html_e( 'Category', 'ssbd' ); ?><span class="sort-arrow" aria-hidden="true"></span></button>
						</th>
					<?php endif; ?>
					<th scope="col" data-ctable-sort="price" aria-sort="none">
						<button type="button"><?php esc_html_e( 'Price band', 'ssbd' ); ?><span class="sort-arrow" aria-hidden="true"></span></button>
					</th>
					<th scope="col" data-ctable-sort="audience" aria-sort="none">
						<button type="button"><?php esc_html_e( 'Built for', 'ssbd' ); ?><span class="sort-arrow" aria-hidden="true"></span></button>
					</th>
					<th scope="col"><?php esc_html_e( 'Read', 'ssbd' ); ?></th>
				</tr>
			</thead>

			<tbody data-ctable-body>
				<?php foreach ( $ssbd_rows as $ssbd_row ) : ?>
					<tr data-facet-item
						data-pillar="<?php echo esc_attr( $ssbd_row['pillar'] ); ?>"
						data-price="<?php echo esc_attr( $ssbd_row['price'] ); ?>"
						data-audience="<?php echo esc_attr( $ssbd_row['audience'] ); ?>"
						data-title="<?php echo esc_attr( $ssbd_row['title'] ); ?>">

						<th scope="row" data-label="<?php esc_attr_e( 'Comparison', 'ssbd' ); ?>">
							<a href="<?php echo esc_url( $ssbd_row['url'] ); ?>"><?php echo esc_html( $ssbd_row['title'] ); ?></a>
							<em><?php echo esc_html( $ssbd_row['intent'] ); ?></em>
						</th>

						<?php if ( $ssbd_show ) : ?>
							<td data-label="<?php esc_attr_e( 'Category', 'ssbd' ); ?>">
								<span class="pill"><?php echo esc_html( $ssbd_labels[ $ssbd_row['pillar'] ] ?? '' ); ?></span>
							</td>
						<?php endif; ?>

						<td data-label="<?php esc_attr_e( 'Price band', 'ssbd' ); ?>"><?php echo esc_html( $ssbd_facets['price'][ $ssbd_row['price'] ] ?? '' ); ?></td>
						<td data-label="<?php esc_attr_e( 'Built for', 'ssbd' ); ?>"><?php echo esc_html( $ssbd_facets['audience'][ $ssbd_row['audience'] ] ?? '' ); ?></td>

						<td data-label="<?php esc_attr_e( 'Read', 'ssbd' ); ?>">
							<a class="link-cta" href="<?php echo esc_url( $ssbd_row['url'] ); ?>">
								<?php esc_html_e( 'Read', 'ssbd' ); ?> <span class="arrow" aria-hidden="true">→</span>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<p class="ctable-empty" data-facet-empty hidden role="status">
		<?php esc_html_e( 'Nothing matches that combination yet. Clear a filter, or tell us what you were looking for below.', 'ssbd' ); ?>
	</p>

	<nav class="ctable-pager" data-ctable-pager hidden aria-label="<?php esc_attr_e( 'Comparison table pages', 'ssbd' ); ?>">
		<button type="button" class="btn btn--sm btn--ghost" data-ctable-prev><?php esc_html_e( 'Previous', 'ssbd' ); ?></button>
		<span class="ctable-pageinfo" role="status" aria-live="polite" data-ctable-pageinfo></span>
		<button type="button" class="btn btn--sm btn--ghost" data-ctable-next><?php esc_html_e( 'Next', 'ssbd' ); ?></button>
	</nav>
</div>
