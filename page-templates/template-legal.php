<?php
/**
 * Template Name: Legal
 *
 * Privacy policy, terms and affiliate disclosure. Renders the WordPress page
 * content if the page has any, otherwise the default copy shipped in
 * inc/data/legal.php.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( have_posts() ) {
	the_post();
}

$ssbd_slug    = get_post_field( 'post_name', get_the_ID() );
$ssbd_legal   = ssbd_data( 'legal' )[ $ssbd_slug ] ?? null;
$ssbd_content = trim( get_the_content() );
$ssbd_email   = ssbd_site( 'email' );
?>

<section class="section--hero page-hero">
	<div class="wrap wrap--narrow">
		<?php ssbd_breadcrumbs(); ?>
		<div style="margin-top:18px"><?php ssbd_eyebrow( __( 'Legal', 'ssbd' ) ); ?></div>
		<h1><?php the_title(); ?></h1>
		<p style="font-size:13px">
			<?php
			printf(
				/* translators: %s: date */
				esc_html__( 'Last updated: %s', 'ssbd' ),
				esc_html( $ssbd_legal['updated'] ?? get_the_modified_date() )
			);
			?>
		</p>
	</div>
</section>

<section class="section">
	<div class="wrap wrap--narrow entry-content">
		<?php
		if ( $ssbd_content ) {
			the_content();
		} elseif ( $ssbd_legal ) {
			echo '<p>' . esc_html( $ssbd_legal['intro'] ) . '</p>';

			foreach ( $ssbd_legal['sections'] as $ssbd_section ) {
				printf( '<h2>%s</h2>', esc_html( $ssbd_section['title'] ) );

				// {email} is the one placeholder these blocks use.
				$ssbd_body = str_replace(
					'{email}',
					'<a href="mailto:' . esc_attr( $ssbd_email ) . '">' . esc_html( $ssbd_email ) . '</a>',
					esc_html( $ssbd_section['body'] )
				);

				echo '<p>' . wp_kses( $ssbd_body, array( 'a' => array( 'href' => array() ) ) ) . '</p>';
			}
		}
		?>
	</div>
</section>

<?php
get_footer();
