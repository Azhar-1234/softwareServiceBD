<?php
/**
 * Stats + recent work on the front page.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

$ssbd_stats    = ssbd_stats();
$ssbd_projects = array_slice( ssbd_projects(), 0, ssbd_work_project_count() );
?>
<section class="section" id="work" aria-labelledby="work-heading">
	<div class="wrap">
		<?php
		ssbd_section_head(
			ssbd_section_heading( 'work' ),
			ssbd_section_lede( 'work' ),
			array( 'id' => 'work-heading' )
		);
		?>

		<?php if ( $ssbd_stats ) : ?>
		<div class="stats" style="margin-bottom:36px">
			<?php foreach ( $ssbd_stats as $ssbd_stat ) : ?>
				<div class="stat">
					<div class="stat-value"><?php echo esc_html( $ssbd_stat['value'] ); ?></div>
					<div class="stat-label"><?php echo esc_html( $ssbd_stat['label'] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<div class="grid grid--3">
			<?php
			foreach ( $ssbd_projects as $ssbd_project ) {
				get_template_part( 'template-parts/components/project-card', null, array( 'project' => $ssbd_project ) );
			}
			?>
		</div>

		<p style="margin-top:28px;text-align:center">
			<a class="link-cta" href="<?php echo esc_url( ssbd_page_url( 'portfolio' ) ); ?>">
				<?php esc_html_e( 'See the full portfolio', 'ssbd' ); ?> <span class="arrow" aria-hidden="true">→</span>
			</a>
		</p>
	</div>
</section>
