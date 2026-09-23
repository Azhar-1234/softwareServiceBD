<?php
/**
 * Template Name: About
 *
 * The page is built to be read top to bottom rather than skimmed as one card
 * grid after another, so the sections deliberately change shape: a narrative
 * column against a photograph, a four-up of what we do, a divided strip of how
 * we work, two wide statements of why, then the process, the stack and who it
 * is for. Copy lives in inc/data/about.php.
 *
 * The pictures beside the intro come from ssbd_about_photos() — the Customizer
 * first, then the files bundled at assets/images/about-1.webp and about-2.webp.
 * With none of them present the intro collapses to a single full-width column
 * rather than leaving the copy in a half-width gutter. The page's featured
 * image is separate and still belongs to the hero, as on every other page.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ssbd_about  = ssbd_data( 'about' );
$ssbd_intro  = $ssbd_about['intro'] ?? array();
$ssbd_photos = ssbd_about_photos();

get_template_part( 'template-parts/components/page-hero', null, array(
	'eyebrow'    => __( 'About', 'ssbd' ),
	'heading'    => __( 'A software company in Bangladesh, built around your growth', 'ssbd' ),
	'answer_key' => 'about',
) );
?>

<?php if ( $ssbd_intro ) : ?>
<section class="section" aria-labelledby="about-intro-heading">
	<div class="wrap">
		<div class="about-intro<?php echo $ssbd_photos ? '' : ' about-intro--solo'; ?>">
			<div class="about-intro-copy">
				<h2 id="about-intro-heading"><?php echo esc_html( $ssbd_intro['heading'] ); ?></h2>

				<?php foreach ( $ssbd_intro['paragraphs'] as $ssbd_paragraph ) : ?>
					<p><?php echo esc_html( $ssbd_paragraph ); ?></p>
				<?php endforeach; ?>

				<div class="about-intro-actions">
					<a class="btn btn--primary" href="<?php echo esc_url( ssbd_page_url( 'contact' ) ); ?>">
						<?php echo esc_html( $ssbd_intro['cta'] ); ?>
					</a>
					<a class="btn btn--ghost" href="<?php echo esc_url( ssbd_page_url( 'portfolio' ) ); ?>">
						<?php esc_html_e( 'See our work', 'ssbd' ); ?>
					</a>
				</div>
			</div>

			<?php if ( $ssbd_photos ) : ?>
				<?php
				/*
				 * A pair gets --duo, which turns the column into two portraits
				 * side by side; one picture fills the column on its own. The
				 * count decides the shape, so nothing has to be configured
				 * twice.
				 *
				 * Attachments are requested at ssbd-card so they arrive as
				 * WebP — the theme converts every generated size (see
				 * inc/setup.php) — and because that size ships a same-ratio
				 * -sm companion, giving the srcset a right-sized candidate for
				 * a narrow viewport instead of every phone pulling 720x450.
				 *
				 * Lazy, deliberately: the hero above carries the page's
				 * featured image and is the LCP element, so eagerly fetching
				 * these would compete with it for the connection.
				 */
				$ssbd_duo = count( $ssbd_photos ) > 1 ? ' about-figures--duo' : '';
				?>
				<div class="about-figures<?php echo esc_attr( $ssbd_duo ); ?>">
					<?php foreach ( $ssbd_photos as $ssbd_photo ) : ?>
						<figure class="about-figure">
							<?php if ( ! empty( $ssbd_photo['id'] ) ) : ?>
								<?php
								echo wp_get_attachment_image( $ssbd_photo['id'], 'ssbd-card', false, array(
									'loading'  => 'lazy',
									'decoding' => 'async',
								) );
								?>
							<?php else : ?>
								<img
									src="<?php echo esc_url( $ssbd_photo['src'] ); ?>"
									alt="<?php echo esc_attr( $ssbd_photo['alt'] ?? '' ); ?>"
									loading="lazy"
									decoding="async"
								>
							<?php endif; ?>
						</figure>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="section section--soft" aria-labelledby="pillars-heading">
	<div class="wrap">
		<?php
		ssbd_section_head(
			__( 'What we actually do', 'ssbd' ),
			__( 'Four kinds of work, and most projects touch more than one of them.', 'ssbd' ),
			array( 'id' => 'pillars-heading' )
		);
		?>
		<div class="grid grid--4">
			<?php foreach ( $ssbd_about['pillars'] as $ssbd_pillar ) : ?>
				<article class="card card--raised">
					<h3><?php echo esc_html( $ssbd_pillar['title'] ); ?></h3>
					<p><?php echo esc_html( $ssbd_pillar['desc'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section" aria-labelledby="values-heading">
	<div class="wrap">
		<?php
		ssbd_section_head(
			__( 'How we work', 'ssbd' ),
			__( 'The parts of a project that decide whether it goes well have very little to do with code.', 'ssbd' ),
			array(
				'id'    => 'values-heading',
				'align' => 'split',
			)
		);
		?>
		<div class="value-row">
			<?php foreach ( $ssbd_about['values'] as $ssbd_value ) : ?>
				<article class="value-cell">
					<span class="card-icon"><?php ssbd_icon( $ssbd_value['icon'] ); ?></span>
					<h3><?php echo esc_html( $ssbd_value['title'] ); ?></h3>
					<p><?php echo esc_html( $ssbd_value['desc'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php if ( ! empty( $ssbd_about['purpose'] ) ) : ?>
<section class="section section--soft" aria-labelledby="purpose-heading">
	<div class="wrap">
		<?php ssbd_section_head( __( 'Why we do it', 'ssbd' ), '', array( 'id' => 'purpose-heading' ) ); ?>
		<div class="purpose">
			<?php foreach ( $ssbd_about['purpose'] as $ssbd_purpose ) : ?>
				<article class="purpose-card">
					<span class="card-icon"><?php ssbd_icon( $ssbd_purpose['icon'] ); ?></span>
					<?php ssbd_eyebrow( $ssbd_purpose['eyebrow'], true ); ?>
					<h3><?php echo esc_html( $ssbd_purpose['title'] ); ?></h3>
					<p><?php echo esc_html( $ssbd_purpose['body'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="section" aria-labelledby="process-heading">
	<div class="wrap">
		<?php
		ssbd_section_head(
			__( 'From first call to launch', 'ssbd' ),
			__( 'The same four steps on every project, whether it is a one-page site or a Laravel application.', 'ssbd' ),
			array(
				'id'    => 'process-heading',
				'align' => 'split',
			)
		);
		?>
		<div class="process">
			<?php foreach ( $ssbd_about['process'] as $ssbd_step ) : ?>
				<div class="process-step">
					<span class="num"><?php echo esc_html( $ssbd_step['no'] ); ?></span>
					<h3><?php echo esc_html( $ssbd_step['title'] ); ?></h3>
					<p><?php echo esc_html( $ssbd_step['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section section--surface section--bordered" aria-labelledby="stack-heading">
	<div class="wrap">
		<?php
		ssbd_section_head(
			__( 'The stack we work in', 'ssbd' ),
			__( 'We stay deliberately narrow so we are genuinely good at what we ship.', 'ssbd' ),
			array( 'id' => 'stack-heading' )
		);
		?>
		<div class="grid grid--2">
			<?php foreach ( $ssbd_about['tech_stack'] as $ssbd_layer ) : ?>
				<article class="card">
					<h3 style="font-size:16px"><?php echo esc_html( $ssbd_layer['title'] ); ?></h3>
					<p><?php echo esc_html( $ssbd_layer['desc'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section" aria-labelledby="audiences-heading">
	<div class="wrap">
		<?php
		ssbd_section_head(
			__( 'Who we work with', 'ssbd' ),
			__( 'Different sizes, same starting question: what is this supposed to do for the business?', 'ssbd' ),
			array( 'id' => 'audiences-heading' )
		);
		?>
		<div class="grid grid--4">
			<?php foreach ( $ssbd_about['audiences'] as $ssbd_audience ) : ?>
				<article class="card card--raised">
					<h3 style="font-size:16px"><?php echo esc_html( $ssbd_audience['title'] ); ?></h3>
					<p><?php echo esc_html( $ssbd_audience['desc'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		if ( trim( get_the_content() ) ) :
			?>
			<section class="section">
				<div class="wrap wrap--narrow entry-content"><?php the_content(); ?></div>
			</section>
			<?php
		endif;
	endwhile;
endif;

get_template_part( 'template-parts/components/faq', null, array( 'faqs' => ssbd_faqs( 'about' ) ) );

get_template_part( 'template-parts/components/cta', null, array(
	'heading' => __( 'Let us build something that works for your business', 'ssbd' ),
	'text'    => __( 'Tell us what you are trying to build, fix or automate. You get a scope, a timeline and a fixed price back — usually within one business day.', 'ssbd' ),
	'button'  => __( 'Start a project', 'ssbd' ),
) );

get_footer();
