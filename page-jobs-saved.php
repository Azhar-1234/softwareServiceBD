<?php
/**
 * Browser-local saved jobs view.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="section--hero jobs-hero jobs-saved-hero">
	<div class="wrap">
		<?php ssbd_breadcrumbs(); ?>
		<?php ssbd_eyebrow( __( 'International Jobs', 'ssbd' ), true ); ?>
		<h1><?php esc_html_e( 'Saved Jobs', 'ssbd' ); ?></h1>
		<p><?php esc_html_e( 'Jobs you save on this device appear here.', 'ssbd' ); ?></p>
	</div>
</section>

<section class="section section--tight">
	<div class="wrap">
		<div class="saved-jobs-list" data-saved-jobs aria-live="polite">
			<div class="job-skeleton-list" aria-label="<?php esc_attr_e( 'Loading saved jobs', 'ssbd' ); ?>">
				<div class="job-skeleton"></div>
				<div class="job-skeleton"></div>
			</div>
		</div>
		<section class="recent-jobs-section" data-recent-jobs hidden>
			<h2><?php esc_html_e( 'Recently Viewed Jobs', 'ssbd' ); ?></h2>
			<div class="recent-jobs-list"></div>
		</section>
	</div>
</section>

<?php get_footer(); ?>
