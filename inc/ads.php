<?php
/**
 * Ad slots: a handful of positions across the blog and article pages, each
 * off until a site owner turns it on.
 *
 * "Off" here means the slot prints nothing at all — no placeholder box, no
 * reserved blank space — which is the same rule the theme already applies to
 * a missing featured image or an empty FAQ list: a feature nobody has turned
 * on should be invisible, not a visibly broken corner of the page.
 *
 * The code field is deliberately not filtered by wp_kses(): an AdSense unit
 * is a <script> tag, and stripping script tags would silently break the one
 * thing this field exists to hold. That is safe here because only someone
 * who already holds 'edit_theme_options' — the same capability the whole
 * Customizer requires — can set it; it is not a place user input ever
 * reaches.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Every ad position the theme offers, keyed by slot id.
 *
 * @return array<string, array{label: string, description: string}>
 */
function ssbd_ad_slots() {
	$slots = array(
		'article_top' => array(
			'label'       => __( 'Article — below the hero image', 'ssbd' ),
			'description' => __( 'A horizontal banner between the hero image and the article body.', 'ssbd' ),
		),
		'article_bottom' => array(
			'label'       => __( 'Article — after the article', 'ssbd' ),
			'description' => __( 'Below the share row, before the author box.', 'ssbd' ),
		),
		'sidebar' => array(
			'label'       => __( 'Sidebar', 'ssbd' ),
			'description' => __( 'Under the contents box on an article, and in the blog sidebar.', 'ssbd' ),
		),
		'blog_index' => array(
			'label'       => __( 'Blog index — between Featured and Latest', 'ssbd' ),
			'description' => __( 'On /blog/ only, between the featured stories and the latest list.', 'ssbd' ),
		),
	);

	/** Filter the available ad slots. */
	return apply_filters( 'ssbd_ad_slots', $slots );
}

/**
 * Register the Customizer section and, per slot, an enable checkbox and a
 * code field.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function ssbd_customize_ads( $wp_customize ) {
	$wp_customize->add_section( 'ssbd_ads', array(
		'title'       => __( 'Advertising', 'ssbd' ),
		'priority'    => 35,
		'description' => __( 'Off by default. Turn on a position and paste in the ad unit\'s own code — an AdSense snippet, or any other network\'s embed code — and it prints there; leave it off and the position does not exist on the page.', 'ssbd' ),
	) );

	foreach ( ssbd_ad_slots() as $slot => $meta ) {
		$enabled_id = "ssbd_ad_{$slot}_enabled";
		$code_id    = "ssbd_ad_{$slot}_code";

		$wp_customize->add_setting( $enabled_id, array(
			'default'           => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'transport'         => 'refresh',
		) );

		$wp_customize->add_control( $enabled_id, array(
			'label'       => $meta['label'],
			'description' => $meta['description'],
			'section'     => 'ssbd_ads',
			'type'        => 'checkbox',
		) );

		$wp_customize->add_setting( $code_id, array(
			'default'           => '',
			// Deliberately unfiltered — see the file docblock. The Customizer
			// itself already requires edit_theme_options to reach this field.
			'sanitize_callback' => static function ( $value ) {
				return (string) $value;
			},
			'transport'         => 'refresh',
		) );

		$wp_customize->add_control( $code_id, array(
			'label'   => __( 'Ad code', 'ssbd' ),
			'section' => 'ssbd_ads',
			'type'    => 'textarea',
		) );
	}
}
add_action( 'customize_register', 'ssbd_customize_ads' );

/**
 * Print one ad slot, or nothing.
 *
 * @param string $slot Slot key, from ssbd_ad_slots().
 */
function ssbd_render_ad_slot( $slot ) {
	if ( ! isset( ssbd_ad_slots()[ $slot ] ) ) {
		return;
	}

	if ( ! get_theme_mod( "ssbd_ad_{$slot}_enabled", false ) ) {
		return;
	}

	$code = trim( (string) get_theme_mod( "ssbd_ad_{$slot}_code", '' ) );

	if ( '' === $code ) {
		return;
	}

	?>
	<div class="ad-slot" data-ad-slot="<?php echo esc_attr( $slot ); ?>">
		<span class="ad-slot-label"><?php esc_html_e( 'Advertisement', 'ssbd' ); ?></span>
		<?php echo $code; // phpcs:ignore WordPress.Security.EscapeOutput -- trusted, admin-only ad markup; see file docblock. ?>
	</div>
	<?php
}
