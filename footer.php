<?php
/**
 * Site footer.
 *
 * Four bands: the sign-up, the link columns, the contact details and the
 * legal line. The services column is generated from the live service list so
 * every service page keeps a site-wide internal link without anyone editing a
 * menu; the Explore column follows the footer menu when one is assigned.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

$ssbd_address  = ssbd_site( 'address', array() );
$ssbd_email    = (string) ssbd_site( 'email' );
$ssbd_phone    = (string) ssbd_site( 'phone' );
$ssbd_services = get_theme_mod( 'ssbd_services_footer', true ) ? ssbd_services() : array();
$ssbd_blog_id  = (int) get_option( 'page_for_posts' );
?>
</main>

<footer class="site-footer">
	<div class="wrap footer-signup">
		<div class="footer-signup-copy">
			<h2><?php esc_html_e( 'Build. Automate. Grow.', 'ssbd' ); ?></h2>
			<p><?php esc_html_e( 'One email a month: what we shipped, and the tools, hosting and AI we actually recommend — with the reasoning, not the hype.', 'ssbd' ); ?></p>
		</div>

		<?php get_template_part( 'template-parts/components/subscribe-form' ); ?>
	</div>

	<div class="wrap footer-top">
		<div class="footer-brand">
			<?php ssbd_brand(); ?>
			<p><?php echo esc_html( ssbd_site( 'description' ) ); ?></p>
			<?php get_template_part( 'template-parts/components/social-links' ); ?>
		</div>

		<?php if ( $ssbd_services ) : ?>
			<div class="footer-col">
				<h3><?php esc_html_e( 'Services', 'ssbd' ); ?></h3>
				<ul>
					<?php foreach ( $ssbd_services as $ssbd_service ) : ?>
						<li><a href="<?php echo esc_url( ssbd_service_url( $ssbd_service ) ); ?>"><?php echo esc_html( $ssbd_service['title'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<div class="footer-col">
			<h3><?php esc_html_e( 'Company', 'ssbd' ); ?></h3>
			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'footer',
					'container'      => false,
					'depth'          => 1,
					'fallback_cb'    => false,
				) );
				?>
			<?php else : ?>
				<?php // Unpublished blueprint pages are skipped, not linked — /products/ sat in this list 404ing on every page of the site. ?>
				<ul>
					<?php foreach ( array( 'about', 'portfolio', 'products', 'solutions', 'contact' ) as $ssbd_slug ) : ?>
						<?php if ( ssbd_page_exists( $ssbd_slug ) ) : ?>
							<li><a href="<?php echo esc_url( ssbd_page_url( $ssbd_slug ) ); ?>"><?php echo esc_html( ssbd_page_blueprint()[ $ssbd_slug ]['title'] ); ?></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="footer-col">
			<h3><?php esc_html_e( 'Resources', 'ssbd' ); ?></h3>
			<ul>
				<?php if ( $ssbd_blog_id ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $ssbd_blog_id ) ); ?>"><?php echo esc_html( get_the_title( $ssbd_blog_id ) ); ?></a></li>
				<?php endif; ?>
				<?php foreach ( array( 'resources', 'tools', 'comparisons' ) as $ssbd_slug ) : ?>
					<?php if ( ssbd_page_exists( $ssbd_slug ) ) : ?>
						<li><a href="<?php echo esc_url( ssbd_page_url( $ssbd_slug ) ); ?>"><?php echo esc_html( ssbd_page_blueprint()[ $ssbd_slug ]['title'] ); ?></a></li>
					<?php endif; ?>
				<?php endforeach; ?>
				<li><a href="<?php echo esc_url( ssbd_jobs_url() ); ?>"><?php esc_html_e( 'International Jobs', 'ssbd' ); ?></a></li>
			</ul>
		</div>

		<div class="footer-col footer-contact">
			<h3><?php esc_html_e( 'Contact', 'ssbd' ); ?></h3>

			<?php // The same facts the LocalBusiness JSON-LD carries, in visible HTML — which is what local rankings reward. ?>
			<address>
				<?php if ( $ssbd_email ) : ?>
					<a href="mailto:<?php echo esc_attr( $ssbd_email ); ?>"><?php echo esc_html( $ssbd_email ); ?></a><br>
				<?php endif; ?>
				<?php if ( $ssbd_phone ) : ?>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $ssbd_phone ) ); ?>"><?php echo esc_html( $ssbd_phone ); ?></a><br>
				<?php endif; ?>
				<span><?php echo esc_html( trim( ( $ssbd_address['locality'] ?? '' ) . ', ' . ( $ssbd_address['region'] ?? '' ), ', ' ) ); ?></span><br>
				<span><?php esc_html_e( 'Bangladesh', 'ssbd' ); ?></span>
			</address>

			<p class="footer-hours"><?php echo esc_html( ssbd_site( 'hours_label' ) ); ?></p>

			<a class="btn btn--sm btn--ghost" href="<?php echo esc_url( ssbd_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Start a project', 'ssbd' ); ?></a>
		</div>
	</div>

	<div class="wrap footer-bottom">
		<span>
			<?php
			printf(
				/* translators: 1: year, 2: business name */
				esc_html__( '© %1$s %2$s. All rights reserved.', 'ssbd' ),
				esc_html( gmdate( 'Y' ) ),
				esc_html( ssbd_site( 'legal_name' ) )
			);
			?>
		</span>

		<?php
		if ( has_nav_menu( 'legal' ) ) {
			wp_nav_menu( array(
				'theme_location' => 'legal',
				'container'      => false,
				'depth'          => 1,
				'fallback_cb'    => false,
			) );
		} else {
			echo '<ul>';
			foreach ( array( 'privacy-policy', 'terms', 'affiliate-disclosure' ) as $ssbd_slug ) {
				// A dead Privacy Policy is worse than a missing one: it is the
				// first link an affiliate or ad-network reviewer clicks.
				if ( ! ssbd_page_exists( $ssbd_slug ) ) {
					continue;
				}

				printf(
					'<li><a href="%s">%s</a></li>',
					esc_url( ssbd_page_url( $ssbd_slug ) ),
					esc_html( ssbd_page_blueprint()[ $ssbd_slug ]['title'] )
				);
			}
			echo '</ul>';
		}
		?>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
