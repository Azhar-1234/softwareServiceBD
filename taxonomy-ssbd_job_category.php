<?php
/**
 * Job category archive.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();
$ssbd_term = get_queried_object();
?>

<section class="section--hero jobs-hero">
	<div class="wrap">
		<?php ssbd_breadcrumbs(); ?>
		<?php ssbd_eyebrow( __( 'International Jobs', 'ssbd' ), true ); ?>
		<h1><?php echo esc_html( $ssbd_term->name ); ?></h1>
		<p><?php esc_html_e( 'Browse current international and remote openings in this category.', 'ssbd' ); ?></p>
	</div>
</section>

<section class="section section--tight">
	<div class="wrap">
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
				<h2><?php esc_html_e( 'No jobs here yet', 'ssbd' ); ?></h2>
				<p><?php esc_html_e( 'Try another category or check back after the next import.', 'ssbd' ); ?></p>
				<a class="btn btn--primary" href="<?php echo esc_url( ssbd_jobs_url() ); ?>"><?php esc_html_e( 'All international jobs', 'ssbd' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
