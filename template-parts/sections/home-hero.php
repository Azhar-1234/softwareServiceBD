<?php
/**
 * Front page hero, including the orbit graphic.
 *
 * The orbit node positions are computed in PHP rather than JS so the labels
 * — which are real service keywords — are present in the served HTML.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

$ssbd_orbit   = ssbd_capabilities();
$ssbd_count   = max( 1, count( $ssbd_orbit ) );
$ssbd_radius  = 135;
$ssbd_meta    = array_slice( $ssbd_orbit, 0, ssbd_hero_meta_count() );
$ssbd_words   = ssbd_hero_rotator();
$ssbd_subhead = ssbd_hero( 'subhead' );

// Same convention as the section ledes: a single dash means "remove this line".
if ( '-' === trim( $ssbd_subhead ) ) {
	$ssbd_subhead = '';
}
?>
<div class="home-hero">
<section class="hero section--hero" id="top">
	<div class="hero-inner<?php echo get_theme_mod( 'ssbd_hero_orbit', true ) ? '' : ' hero-inner--solo'; ?>">
		<div class="hero-copy">
			<?php if ( ssbd_hero( 'eyebrow' ) ) : ?>
				<?php ssbd_eyebrow( ssbd_hero( 'eyebrow' ), true, 'eyebrow--chip' ); ?>
			<?php endif; ?>

			<?php
			/*
			 * Three lines: the brand, the connector, and the keyword that
			 * changes. All of them are real text in the response — the
			 * rotation is a class the browser moves between siblings that
			 * are already here, so a crawler that never runs assets/js/hero.js
			 * still reads every keyword, and the first one is painted before
			 * any script has loaded. See ssbd_hero_rotator().
			 */
			?>
			<h1 class="hero-title">
				<span class="hero-title__brand"><?php echo esc_html( ssbd_hero( 'headline' ) ); ?></span>

				<?php if ( '' !== $ssbd_subhead ) : ?>
					<span class="hero-title__lead"><?php echo esc_html( $ssbd_subhead ); ?></span>
				<?php endif; ?>

				<?php if ( count( $ssbd_words ) > 1 ) : ?>
					<span class="hero-rotator" data-hero-rotator>
						<?php foreach ( $ssbd_words as $ssbd_i => $ssbd_word ) : ?>
							<span class="hero-rotator__word<?php echo 0 === $ssbd_i ? ' is-active' : ''; ?>"><?php echo esc_html( $ssbd_word ); ?></span>
						<?php endforeach; ?>
					</span>
				<?php elseif ( $ssbd_words ) : ?>
					<span class="hero-title__keyword"><?php echo esc_html( $ssbd_words[0] ); ?></span>
				<?php endif; ?>
			</h1>

			<?php // Shared with the meta description and the WebPage abstract — see inc/data/answers.php. ?>
			<p class="hero-lede"><?php echo esc_html( ssbd_hero( 'lede' ) ); ?></p>

			<div class="hero-actions">
				<?php if ( ssbd_hero( 'cta1_label' ) ) : ?>
					<a class="btn btn--primary" href="<?php echo esc_url( ssbd_hero( 'cta1_url' ) ); ?>"><?php echo esc_html( ssbd_hero( 'cta1_label' ) ); ?></a>
				<?php endif; ?>
				<?php if ( ssbd_hero( 'cta2_label' ) ) : ?>
					<a class="btn btn--ghost" href="<?php echo esc_url( ssbd_hero( 'cta2_url' ) ); ?>"><?php echo esc_html( ssbd_hero( 'cta2_label' ) ); ?></a>
				<?php endif; ?>
			</div>

			<?php if ( $ssbd_meta ) : ?>
				<p class="hero-meta"><?php echo esc_html( implode( ' · ', $ssbd_meta ) ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( get_theme_mod( 'ssbd_hero_orbit', true ) ) : ?>
		<div class="orbit" aria-hidden="true">
			<div class="orbit-ring"></div>
			<div class="orbit-core"><?php esc_html_e( 'Software', 'ssbd' ); ?><br><?php esc_html_e( 'Service BD', 'ssbd' ); ?></div>
			<?php
			foreach ( $ssbd_orbit as $i => $label ) :
				$ssbd_angle = ( $i / $ssbd_count ) * M_PI * 2 - M_PI / 2;
				$ssbd_x     = round( cos( $ssbd_angle ) * $ssbd_radius, 2 );
				$ssbd_y     = round( sin( $ssbd_angle ) * $ssbd_radius, 2 );
				?>
				<div class="orbit-node" style="left:calc(50% + <?php echo esc_attr( $ssbd_x ); ?>px);top:calc(50% + <?php echo esc_attr( $ssbd_y ); ?>px)"><?php echo esc_html( $label ); ?></div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>
</section>

<?php if ( $ssbd_orbit ) : ?>
<div class="capability-strip">
	<ul>
		<?php foreach ( $ssbd_orbit as $ssbd_capability ) : ?>
			<li><span class="dot" aria-hidden="true"></span><?php echo esc_html( $ssbd_capability ); ?></li>
		<?php endforeach; ?>
	</ul>
</div>
<?php endif; ?>
</div>
