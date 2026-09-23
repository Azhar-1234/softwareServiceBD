<?php
/**
 * Template Name: Resources
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ssbd_resources = ssbd_data( 'resources' );

get_template_part( 'template-parts/components/page-hero', null, array(
	'eyebrow'    => __( 'Resources', 'ssbd' ),
	'heading'    => __( 'Free tools, guides and honest recommendations', 'ssbd' ),
	'answer_key' => 'resources',
) );
?>

<section class="section" aria-labelledby="tools-heading">
	<div class="wrap">
		<?php
		ssbd_section_head(
			__( 'Free business tools', 'ssbd' ),
			__( 'Quick calculators and checklists for scoping a project before you talk to anyone.', 'ssbd' ),
			array( 'id' => 'tools-heading' )
		);
		?>
		<div class="grid grid--3">
			<?php foreach ( $ssbd_resources['free_tools'] as $ssbd_tool ) : ?>
				<article class="card">
					<span class="card-icon"><?php ssbd_icon( $ssbd_tool['icon'] ); ?></span>
					<h3 style="font-size:16px"><?php echo esc_html( $ssbd_tool['title'] ); ?></h3>
					<p><?php echo esc_html( $ssbd_tool['desc'] ); ?></p>
					<a class="link-cta" href="<?php echo esc_url( ssbd_page_url( 'contact' ) ); ?>">
						<?php echo esc_html( $ssbd_tool['cta'] ); ?> <span class="arrow" aria-hidden="true">→</span>
					</a>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section section--soft" aria-labelledby="papers-heading">
	<div class="wrap">
		<?php
		ssbd_section_head(
			__( 'Guides, whitepapers and case studies', 'ssbd' ),
			__( 'Written from projects we actually delivered, not from keyword research.', 'ssbd' ),
			array( 'id' => 'papers-heading' )
		);
		?>
		<div class="grid grid--3">
			<?php foreach ( $ssbd_resources['whitepapers'] as $ssbd_paper ) : ?>
				<article class="card card--raised">
					<span class="pill" style="align-self:flex-start"><?php echo esc_html( $ssbd_paper['tag'] ); ?></span>
					<h3 style="font-size:16px"><?php echo esc_html( $ssbd_paper['title'] ); ?></h3>
					<p><?php echo esc_html( $ssbd_paper['desc'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
// Real blog posts, if any exist.
$ssbd_posts = new WP_Query( array(
	'posts_per_page'      => 6,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
) );

if ( $ssbd_posts->have_posts() ) :
	?>
	<section class="section" aria-labelledby="blog-heading">
		<div class="wrap">
			<?php ssbd_section_head( __( 'Latest from the blog', 'ssbd' ), '', array( 'id' => 'blog-heading' ) ); ?>
			<div class="grid grid--3">
				<?php
				while ( $ssbd_posts->have_posts() ) {
					$ssbd_posts->the_post();
					get_template_part( 'template-parts/components/post-card' );
				}
				?>
			</div>
		</div>
	</section>
	<?php
	wp_reset_postdata();
endif;
?>

<section class="section section--surface section--bordered" aria-labelledby="stack-heading">
	<div class="wrap">
		<?php
		ssbd_section_head(
			__( 'The stack we recommend', 'ssbd' ),
			__( 'Categories of tools we vet and use on client work.', 'ssbd' ),
			array( 'id' => 'stack-heading' )
		);
		?>
		<div class="grid grid--3">
			<?php foreach ( $ssbd_resources['recommended_groups'] as $ssbd_group ) : ?>
				<article class="card">
					<h3 style="font-size:16px"><?php echo esc_html( $ssbd_group['title'] ); ?></h3>
					<p><?php echo esc_html( $ssbd_group['desc'] ); ?></p>
					<p style="font-size:12.5px;font-style:italic"><?php echo esc_html( $ssbd_group['why'] ); ?></p>
					<a class="link-cta" href="<?php echo esc_url( ssbd_page_url( 'tools' ) ); ?>">
						<?php echo esc_html( $ssbd_group['cta'] ); ?> <span class="arrow" aria-hidden="true">→</span>
					</a>
				</article>
			<?php endforeach; ?>
		</div>

		<p style="margin-top:28px;text-align:center;font-size:13px;color:var(--muted)">
			<?php
			printf(
				/* translators: %s: affiliate disclosure link */
				esc_html__( 'Some links on this page are affiliate links. Read our %s.', 'ssbd' ),
				'<a href="' . esc_url( ssbd_page_url( 'affiliate-disclosure' ) ) . '">' . esc_html__( 'affiliate disclosure', 'ssbd' ) . '</a>'
			);
			?>
		</p>
	</div>
</section>

<?php
get_template_part( 'template-parts/components/faq', null, array( 'faqs' => ssbd_faqs( 'resources' ) ) );
get_template_part( 'template-parts/components/cta' );

get_footer();
