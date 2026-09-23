<?php
/**
 * Single job page.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$ssbd_apply  = ssbd_job_meta( 'apply_url' );
	$ssbd_source = ssbd_job_meta( 'source' );
	$ssbd_company = ssbd_job_meta( 'company' );
	$ssbd_logo    = ssbd_job_meta( 'logo_url' );
	$ssbd_salary  = ssbd_job_salary();
	$ssbd_terms   = wp_get_post_terms( get_the_ID(), 'ssbd_job_category', array( 'fields' => 'ids' ) );
	$ssbd_share   = rawurlencode( get_permalink() );
	?>
	<article <?php post_class( 'job-detail' ); ?> data-job-view data-job-id="<?php echo esc_attr( get_the_ID() ); ?>" data-job-title="<?php echo esc_attr( get_the_title() ); ?>" data-job-url="<?php echo esc_url( get_permalink() ); ?>" data-job-company="<?php echo esc_attr( $ssbd_company ); ?>">
		<header class="section--hero jobs-hero">
			<div class="wrap">
				<a class="job-back-link" href="<?php echo esc_url( ssbd_jobs_url() ); ?>">&larr; <?php esc_html_e( 'Back to international jobs', 'ssbd' ); ?></a>
				<?php if ( $ssbd_logo ) : ?><img class="job-detail-logo" src="<?php echo esc_url( $ssbd_logo ); ?>" alt="" width="72" height="72"><?php endif; ?>
				<h1><?php the_title(); ?></h1>
				<?php if ( $ssbd_company ) : ?><p class="job-detail-company"><?php echo esc_html( $ssbd_company ); ?></p><?php endif; ?>
				<div class="job-meta">
					<span><?php echo esc_html( ssbd_job_meta( 'location' ) ?: __( 'Remote', 'ssbd' ) ); ?></span>
					<span><?php echo esc_html( ssbd_job_employment_type() ); ?></span>
					<?php if ( $ssbd_salary ) : ?><span><?php echo esc_html( $ssbd_salary ); ?></span><?php endif; ?>
					<span><?php printf( esc_html__( 'Posted %s ago', 'ssbd' ), esc_html( human_time_diff( get_post_time( 'U', true ), current_time( 'timestamp', true ) ) ) ); ?></span>
				</div>
				<div class="job-detail-actions">
					<?php if ( $ssbd_apply ) : ?><a class="btn btn--accent" href="<?php echo esc_url( $ssbd_apply ); ?>" rel="nofollow sponsored noopener" target="_blank"><?php esc_html_e( 'Apply now', 'ssbd' ); ?></a><?php endif; ?>
					<button class="btn btn--ghost job-save-detail" type="button" data-save-job="<?php echo esc_attr( get_the_ID() ); ?>" aria-pressed="false"><span aria-hidden="true">♡</span> <span class="job-save-label"><?php esc_html_e( 'Save job', 'ssbd' ); ?></span><span class="screen-reader-text"><?php esc_html_e( 'Save job', 'ssbd' ); ?></span></button>
				</div>
			</div>
		</header>

		<section class="section section--tight">
			<div class="wrap job-detail-layout">
				<div class="entry-content">
					<h2><?php esc_html_e( 'Job Description', 'ssbd' ); ?></h2>
					<?php the_content(); ?>
					<div class="job-share">
						<strong><?php esc_html_e( 'Share this job', 'ssbd' ); ?></strong>
						<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo esc_attr( $ssbd_share ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'LinkedIn', 'ssbd' ); ?></a>
						<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr( $ssbd_share ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Facebook', 'ssbd' ); ?></a>
						<a href="https://wa.me/?text=<?php echo esc_attr( rawurlencode( get_the_title() . ' ' . get_permalink() ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'WhatsApp', 'ssbd' ); ?></a>
						<button type="button" data-copy-job-link="<?php echo esc_url( get_permalink() ); ?>"><?php esc_html_e( 'Copy link', 'ssbd' ); ?></button>
					</div>
				</div>

				<aside class="job-sidebar" aria-label="<?php esc_attr_e( 'Job summary', 'ssbd' ); ?>">
					<h2><?php esc_html_e( 'Job summary', 'ssbd' ); ?></h2>
					<dl>
						<?php foreach ( ssbd_job_facts() as $ssbd_fact ) : ?>
							<dt><?php esc_html_e( 'Detail', 'ssbd' ); ?></dt>
							<dd><?php echo esc_html( $ssbd_fact ); ?></dd>
						<?php endforeach; ?>
						<?php if ( ssbd_job_meta( 'date_posted' ) ) : ?>
							<dt><?php esc_html_e( 'Posted', 'ssbd' ); ?></dt>
							<dd><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( ssbd_job_meta( 'date_posted' ) ) ) ); ?></dd>
						<?php endif; ?>
						<?php if ( ssbd_job_meta( 'valid_through' ) ) : ?>
							<dt><?php esc_html_e( 'Valid through', 'ssbd' ); ?></dt>
							<dd><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( ssbd_job_meta( 'valid_through' ) ) ) ); ?></dd>
						<?php endif; ?>
						<?php if ( $ssbd_source ) : ?>
							<dt><?php esc_html_e( 'Source', 'ssbd' ); ?></dt>
							<dd><?php echo esc_html( $ssbd_source ); ?></dd>
						<?php endif; ?>
						<?php if ( $ssbd_salary ) : ?>
							<dt><?php esc_html_e( 'Salary', 'ssbd' ); ?></dt>
							<dd><?php echo esc_html( $ssbd_salary ); ?></dd>
						<?php endif; ?>
					</dl>
					<?php if ( $ssbd_apply ) : ?>
						<a class="btn btn--primary" href="<?php echo esc_url( $ssbd_apply ); ?>" rel="nofollow sponsored noopener" target="_blank"><?php esc_html_e( 'Apply now', 'ssbd' ); ?></a>
					<?php endif; ?>
				</aside>
			</div>
		</section>

		<?php
		$ssbd_similar = new WP_Query( array(
			'post_type'      => 'ssbd_job',
			'post_status'    => 'publish',
			'posts_per_page' => 3,
			'post__not_in'   => array( get_the_ID() ),
			'tax_query'      => $ssbd_terms ? array( array( 'taxonomy' => 'ssbd_job_category', 'field' => 'term_id', 'terms' => $ssbd_terms ) ) : array(),
		) );
		if ( $ssbd_similar->have_posts() ) :
			?>
			<section class="section section--tight job-similar">
				<div class="wrap">
					<h2><?php esc_html_e( 'Similar Jobs You May Like', 'ssbd' ); ?></h2>
					<div class="job-list">
						<?php while ( $ssbd_similar->have_posts() ) : $ssbd_similar->the_post(); get_template_part( 'template-parts/jobs/card' ); endwhile; ?>
					</div>
				</div>
			</section>
			<?php
			wp_reset_postdata();
		endif;
		?>
		<section class="section section--tight recent-jobs-section" data-recent-jobs hidden>
			<div class="wrap">
				<h2><?php esc_html_e( 'Recently Viewed Jobs', 'ssbd' ); ?></h2>
				<div class="recent-jobs-list"></div>
			</div>
		</section>
	</article>
	<?php
endwhile;

get_footer();
