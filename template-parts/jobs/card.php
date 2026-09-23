<?php
/**
 * Job list card.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

$ssbd_company = ssbd_job_meta( 'company' );
$ssbd_logo    = ssbd_job_meta( 'logo_url' );
$ssbd_salary  = ssbd_job_salary();
$ssbd_terms   = get_the_terms( get_the_ID(), 'ssbd_job_category' );
$ssbd_initial = $ssbd_company ? strtoupper( substr( $ssbd_company, 0, 1 ) ) : 'J';
?>

<article <?php post_class( 'job-card' ); ?>>
	<div class="job-company-logo" aria-hidden="true">
		<?php if ( $ssbd_logo ) : ?>
			<img src="<?php echo esc_url( $ssbd_logo ); ?>" alt="" loading="lazy" width="52" height="52">
		<?php else : ?>
			<span><?php echo esc_html( $ssbd_initial ); ?></span>
		<?php endif; ?>
	</div>
	<div class="job-card-body">
		<div class="job-card-title-row">
			<div>
				<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				<?php if ( $ssbd_company ) : ?><p class="job-company"><?php echo esc_html( $ssbd_company ); ?></p><?php endif; ?>
			</div>
			<button class="job-save" type="button" data-save-job="<?php echo esc_attr( get_the_ID() ); ?>" aria-pressed="false"><span aria-hidden="true">♡</span><span class="screen-reader-text"><?php esc_html_e( 'Save job', 'ssbd' ); ?></span></button>
		</div>
		<div class="job-meta">
			<span><?php echo esc_html( ssbd_job_meta( 'location' ) ?: __( 'Remote', 'ssbd' ) ); ?></span>
			<span><?php echo esc_html( ssbd_job_employment_type() ); ?></span>
			<?php if ( $ssbd_salary ) : ?><span><?php echo esc_html( $ssbd_salary ); ?></span><?php endif; ?>
			<span><?php printf( esc_html__( '%s ago', 'ssbd' ), esc_html( human_time_diff( get_post_time( 'U', true ), current_time( 'timestamp', true ) ) ) ); ?></span>
		</div>
		<?php if ( has_excerpt() ) : ?><p class="job-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p><?php endif; ?>
		<div class="job-card-footer">
			<div class="job-tags">
				<?php if ( $ssbd_terms && ! is_wp_error( $ssbd_terms ) ) : foreach ( array_slice( $ssbd_terms, 0, 2 ) as $ssbd_term ) : ?>
					<span><?php echo esc_html( $ssbd_term->name ); ?></span>
				<?php endforeach; endif; ?>
			</div>
			<a class="job-view-link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'View job', 'ssbd' ); ?> <span aria-hidden="true">&rarr;</span></a>
		</div>
	</div>
</article>
