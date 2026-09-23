<?php
/**
 * Jobs archive.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ssbd_categories = ssbd_job_categories();
$ssbd_keyword    = isset( $_GET['keyword'] ) ? sanitize_text_field( wp_unslash( $_GET['keyword'] ) ) : '';
$ssbd_category   = isset( $_GET['category'] ) ? sanitize_title( wp_unslash( $_GET['category'] ) ) : '';
$ssbd_date       = isset( $_GET['date'] ) ? absint( $_GET['date'] ) : 0;
$ssbd_sort       = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'newest';
$ssbd_filtered   = $ssbd_keyword || $ssbd_category || $ssbd_date;
$ssbd_date_names = array( 1 => __( 'Past 24 hours', 'ssbd' ), 3 => __( 'Past 3 days', 'ssbd' ), 7 => __( 'Past 7 days', 'ssbd' ), 14 => __( 'Past 2 weeks', 'ssbd' ), 30 => __( 'Past 30 days', 'ssbd' ) );
?>

<section class="section--hero jobs-hero">
	<div class="wrap">
		<?php ssbd_breadcrumbs(); ?>
		<?php ssbd_eyebrow( __( 'Careers', 'ssbd' ), true ); ?>
		<h1><?php esc_html_e( 'International & Remote Jobs', 'ssbd' ); ?></h1>
		<p><?php esc_html_e( 'Discover verified opportunities from companies worldwide.', 'ssbd' ); ?></p>

		<form class="job-search" id="jobs-filter-form" role="search" method="get" action="<?php echo esc_url( ssbd_jobs_url() ); ?>" data-jobs-filter>
			<label class="job-filter-field job-filter-search" for="job-s">
				<span><?php esc_html_e( 'Keywords', 'ssbd' ); ?></span>
				<input id="job-s" type="search" name="keyword" value="<?php echo esc_attr( $ssbd_keyword ); ?>" placeholder="<?php esc_attr_e( 'Job title or skill', 'ssbd' ); ?>">
			</label>
			<label class="job-filter-field" for="job-category">
				<span><?php esc_html_e( 'Category', 'ssbd' ); ?></span>
				<select id="job-category" name="category">
					<option value=""><?php esc_html_e( 'All categories', 'ssbd' ); ?></option>
					<?php foreach ( $ssbd_categories as $ssbd_filter_category ) : ?>
						<option value="<?php echo esc_attr( $ssbd_filter_category->slug ); ?>" <?php selected( $ssbd_category, $ssbd_filter_category->slug ); ?>><?php echo esc_html( $ssbd_filter_category->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="job-filter-field" for="job-date">
				<span><?php esc_html_e( 'Date posted', 'ssbd' ); ?></span>
				<select id="job-date" name="date">
					<option value=""><?php esc_html_e( 'Any time', 'ssbd' ); ?></option>
					<option value="1" <?php selected( $ssbd_date, 1 ); ?>><?php esc_html_e( 'Past 24 hours', 'ssbd' ); ?></option>
					<option value="3" <?php selected( $ssbd_date, 3 ); ?>><?php esc_html_e( 'Past 3 days', 'ssbd' ); ?></option>
					<option value="7" <?php selected( $ssbd_date, 7 ); ?>><?php esc_html_e( 'Past week', 'ssbd' ); ?></option>
					<option value="14" <?php selected( $ssbd_date, 14 ); ?>><?php esc_html_e( 'Past 2 weeks', 'ssbd' ); ?></option>
					<option value="30" <?php selected( $ssbd_date, 30 ); ?>><?php esc_html_e( 'Past month', 'ssbd' ); ?></option>
				</select>
			</label>
			<div class="job-filter-actions">
				<button class="btn btn--accent" type="submit"><?php esc_html_e( 'Filter jobs', 'ssbd' ); ?></button>
				<?php if ( $ssbd_filtered ) : ?>
					<a class="job-filter-clear" href="<?php echo esc_url( ssbd_jobs_url() ); ?>" data-job-filter-link><?php esc_html_e( 'Clear', 'ssbd' ); ?></a>
				<?php endif; ?>
			</div>
		</form>
		<div class="job-popular" aria-label="<?php esc_attr_e( 'Popular job searches', 'ssbd' ); ?>">
			<span><?php esc_html_e( 'Popular:', 'ssbd' ); ?></span>
			<?php foreach ( array( 'WordPress', 'PHP', 'React', 'AI', 'Remote' ) as $ssbd_popular ) : ?>
				<a href="<?php echo esc_url( add_query_arg( 'keyword', strtolower( $ssbd_popular ), ssbd_jobs_url() ) ); ?>" data-job-filter-link><?php echo esc_html( $ssbd_popular ); ?></a>
			<?php endforeach; ?>
			<a class="job-saved-link" href="<?php echo esc_url( home_url( '/jobs/saved/' ) ); ?>"><?php esc_html_e( 'Saved jobs', 'ssbd' ); ?></a>
		</div>
	</div>
</section>

<section class="section section--tight">
	<div class="wrap">
		<?php if ( $ssbd_categories ) : ?>
			<div class="job-categories" aria-label="<?php esc_attr_e( 'Job categories', 'ssbd' ); ?>">
				<?php foreach ( $ssbd_categories as $ssbd_category_item ) : ?>
					<a class="job-category-card<?php echo $ssbd_category === $ssbd_category_item->slug ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'category', $ssbd_category_item->slug, ssbd_jobs_url() ) ); ?>" data-job-filter-link>
						<span><?php echo esc_html( $ssbd_category_item->name ); ?></span>
						<small>
							<?php
							printf(
								esc_html( _n( '%s open job', '%s open jobs', (int) $ssbd_category_item->count, 'ssbd' ) ),
								esc_html( number_format_i18n( (int) $ssbd_category_item->count ) )
							);
							?>
							<span aria-hidden="true">&rarr;</span>
						</small>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="jobs-results" id="jobs-results" aria-live="polite">
			<div class="jobs-results-head">
				<h2><?php printf( esc_html( _n( '%s job found', '%s jobs found', $wp_query->found_posts, 'ssbd' ) ), esc_html( number_format_i18n( $wp_query->found_posts ) ) ); ?></h2>
				<label for="job-sort"><span><?php esc_html_e( 'Sort by', 'ssbd' ); ?></span>
					<select id="job-sort" name="sort" form="jobs-filter-form" data-job-sort>
						<option value="newest" <?php selected( $ssbd_sort, 'newest' ); ?>><?php esc_html_e( 'Newest', 'ssbd' ); ?></option>
						<option value="relevance" <?php selected( $ssbd_sort, 'relevance' ); ?>><?php esc_html_e( 'Most relevant', 'ssbd' ); ?></option>
					</select>
				</label>
			</div>

			<?php if ( $ssbd_filtered ) : ?>
				<div class="job-active-filters" aria-label="<?php esc_attr_e( 'Active filters', 'ssbd' ); ?>">
					<span><?php esc_html_e( 'Active filters:', 'ssbd' ); ?></span>
					<?php if ( $ssbd_keyword ) : ?><a href="<?php echo esc_url( remove_query_arg( array( 'keyword', 'paged' ) ) ); ?>" data-job-filter-link><?php echo esc_html( $ssbd_keyword ); ?> &times;</a><?php endif; ?>
					<?php if ( $ssbd_category ) : $ssbd_active_term = get_term_by( 'slug', $ssbd_category, 'ssbd_job_category' ); ?><a href="<?php echo esc_url( remove_query_arg( array( 'category', 'paged' ) ) ); ?>" data-job-filter-link><?php echo esc_html( $ssbd_active_term ? $ssbd_active_term->name : $ssbd_category ); ?> &times;</a><?php endif; ?>
					<?php if ( $ssbd_date && isset( $ssbd_date_names[ $ssbd_date ] ) ) : ?><a href="<?php echo esc_url( remove_query_arg( array( 'date', 'paged' ) ) ); ?>" data-job-filter-link><?php echo esc_html( $ssbd_date_names[ $ssbd_date ] ); ?> &times;</a><?php endif; ?>
					<a class="job-clear-all" href="<?php echo esc_url( ssbd_jobs_url() ); ?>" data-job-filter-link><?php esc_html_e( 'Clear all', 'ssbd' ); ?></a>
				</div>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>
				<div class="job-list">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/jobs/card' );
				}
				?>
				</div>

				<?php ssbd_pagination(); ?>
			<?php else : ?>
				<div class="archive-empty">
				<?php if ( $ssbd_filtered ) : ?>
					<h2><?php esc_html_e( 'No matching international jobs', 'ssbd' ); ?></h2>
					<p><?php esc_html_e( 'Try a broader category, date range, or keyword.', 'ssbd' ); ?></p>
					<a class="btn btn--primary" href="<?php echo esc_url( ssbd_jobs_url() ); ?>" data-job-filter-link><?php esc_html_e( 'Clear filters', 'ssbd' ); ?></a>
				<?php else : ?>
					<h2><?php esc_html_e( 'International jobs are being refreshed', 'ssbd' ); ?></h2>
					<p><?php esc_html_e( 'Live international jobs will appear here as soon as the current API update finishes.', 'ssbd' ); ?></p>
				<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php
get_footer();
