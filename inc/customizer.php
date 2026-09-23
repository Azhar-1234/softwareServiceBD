<?php
/**
 * Customizer options for the handful of business facts a site owner is
 * likely to change without touching inc/data/site.php.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register Customizer settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function ssbd_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'ssbd_business', array(
		'title'       => __( 'Business & SEO', 'ssbd' ),
		'priority'    => 30,
		'description' => __( 'Contact details and the share image used in search results and social previews.', 'ssbd' ),
	) );

	$fields = array(
		'ssbd_email' => array(
			'label'    => __( 'Contact email', 'ssbd' ),
			'default'  => '',
			'sanitize' => 'sanitize_email',
			'type'     => 'email',
		),
		'ssbd_phone' => array(
			'label'    => __( 'Phone number', 'ssbd' ),
			'default'  => '',
			'sanitize' => 'sanitize_text_field',
			'type'     => 'text',
		),
		'ssbd_whatsapp' => array(
			'label'    => __( 'WhatsApp link', 'ssbd' ),
			'default'  => '',
			'sanitize' => 'esc_url_raw',
			'type'     => 'url',
		),
		'ssbd_inquiry_recipient' => array(
			'label'       => __( 'Inquiry recipient email', 'ssbd' ),
			'description' => __( 'Where the contact form sends. Defaults to the site admin email.', 'ssbd' ),
			'default'     => '',
			'sanitize'    => 'sanitize_email',
			'type'        => 'email',
		),
	);

	foreach ( $fields as $id => $field ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $field['default'],
			'sanitize_callback' => $field['sanitize'],
			'transport'         => 'refresh',
		) );

		$wp_customize->add_control( $id, array(
			'label'       => $field['label'],
			'description' => $field['description'] ?? '',
			'section'     => 'ssbd_business',
			'type'        => $field['type'],
		) );
	}

	$wp_customize->add_setting( 'ssbd_share_image', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );

	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'ssbd_share_image', array(
		'label'       => __( 'Default share image', 'ssbd' ),
		'description' => __( 'Shown in Google, Facebook, LinkedIn and X previews when a page has no featured image. 1200×630 works everywhere.', 'ssbd' ),
		'section'     => 'ssbd_business',
	) ) );

	$wp_customize->add_setting( 'ssbd_brand_links', array(
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'ssbd_brand_links', array(
		'label'       => __( 'Link the business name to the home page', 'ssbd' ),
		'description' => __( 'Finds the site name written in the text of any page and links it home, so an internal link never depends on an author remembering to add one. Mentions already inside a link, a heading or code are left alone, and the home page never links to itself.', 'ssbd' ),
		'section'     => 'ssbd_business',
		'type'        => 'checkbox',
	) );

	ssbd_customize_front_page( $wp_customize );
	ssbd_customize_services( $wp_customize );
	ssbd_customize_about( $wp_customize );
}

/**
 * The About page section: the photographs beside its opening copy.
 *
 * A media control rather than an image control, so the setting stores an
 * attachment ID. That is what lets the rendered picture carry a srcset, the
 * WebP sizes the theme generates, and the alt text already typed into the
 * Media Library — an image control would store a bare URL and lose all three.
 *
 * Leaving both empty falls back to assets/images/about-1.webp and about-2.webp
 * if the theme ships them; see ssbd_about_photos() in inc/data.php.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function ssbd_customize_about( $wp_customize ) {
	$wp_customize->add_section( 'ssbd_about', array(
		'title'       => __( 'About page', 'ssbd' ),
		'priority'    => 40,
		'description' => __( 'The photographs shown beside the opening paragraphs on the About page. Leave both empty to use the images bundled with the theme. Two pictures sit side by side as portraits; one fills the column on its own.', 'ssbd' ),
	) );

	for ( $i = 1; $i <= SSBD_ABOUT_PHOTOS; $i++ ) {
		$id = 'ssbd_about_photo_' . $i;

		$wp_customize->add_setting( $id, array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		) );

		$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, $id, array(
			/* translators: %d: photo number */
			'label'       => sprintf( __( 'Photo %d', 'ssbd' ), $i ),
			'description' => 1 === $i
				? __( 'Upload a WebP, JPEG or PNG — the theme serves it as WebP either way. Around 720×900 suits the portrait pair.', 'ssbd' )
				: '',
			'section'     => 'ssbd_about',
			'mime_type'   => 'image',
		) ) );
	}
}

/**
 * The Services section: how services behave in the header and on the site.
 *
 * The services themselves — their names, descriptions, nesting and order —
 * are edited under Services in the main admin menu, because they are content,
 * not settings. What lives here is how they are presented.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function ssbd_customize_services( $wp_customize ) {
	$wp_customize->add_section( 'ssbd_services', array(
		'title'       => __( 'Services & Comparisons', 'ssbd' ),
		'priority'    => 26,
		'description' => __( 'Services are edited under Services in the admin menu — each one is its own page, and a service nested under another (Page Attributes > Parent) becomes an item inside its dropdown.', 'ssbd' ),
	) );

	$wp_customize->add_setting( 'ssbd_services_dropdown', array(
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'ssbd_services_dropdown', array(
		'label'       => __( 'Services dropdown in the header', 'ssbd' ),
		'description' => __( 'Fills the Services menu item with every published service, so a new service appears in the header on its own. Turn this off to build the dropdown by hand in Appearance > Menus instead.', 'ssbd' ),
		'section'     => 'ssbd_services',
		'type'        => 'checkbox',
	) );

	$wp_customize->add_setting( 'ssbd_comparisons_dropdown', array(
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'ssbd_comparisons_dropdown', array(
		'label'       => __( 'Comparisons dropdown in the header', 'ssbd' ),
		'description' => __( 'Fills the Comparisons menu item with the three comparison categories — Hosting & Domains, WP Plugins, Business SaaS — each of which has its own page.', 'ssbd' ),
		'section'     => 'ssbd_services',
		'type'        => 'checkbox',
	) );

	$wp_customize->add_setting( 'ssbd_products_dropdown', array(
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'ssbd_products_dropdown', array(
		'label'       => __( 'Products dropdown in the header', 'ssbd' ),
		'description' => __( 'Fills the Products menu item with the product categories that have products in them. A category nested under another (Products > Product categories > Parent) becomes an item inside its dropdown. Each one opens the Products page with that filter already applied.', 'ssbd' ),
		'section'     => 'ssbd_services',
		'type'        => 'checkbox',
	) );

	$wp_customize->add_setting( 'ssbd_services_footer', array(
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'ssbd_services_footer', array(
		'label'       => __( 'List services in the footer', 'ssbd' ),
		'description' => __( 'A plain link list of the headline services, which is what gives every service page a site-wide internal link.', 'ssbd' ),
		'section'     => 'ssbd_services',
		'type'        => 'checkbox',
	) );
}

/**
 * The Front Page panel: hero copy, which sections appear, and their headings.
 *
 * Every field defaults to empty and falls back to the theme's own copy, so a
 * fresh install needs nothing filled in and clearing a field restores the
 * original rather than blanking the page.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function ssbd_customize_front_page( $wp_customize ) {
	require_once SSBD_DIR . '/inc/customizer-control-sections.php';
	require_once SSBD_DIR . '/inc/customizer-control-checklist.php';

	$wp_customize->add_panel( 'ssbd_front_page', array(
		'title'       => __( 'Front Page', 'ssbd' ),
		'priority'    => 25,
		'description' => __( 'Everything on the homepage below the header. Leave a field empty to keep the theme default.', 'ssbd' ),
	) );

	/* ---------- Hero ---------- */

	$wp_customize->add_section( 'ssbd_hero', array(
		'title' => __( 'Hero', 'ssbd' ),
		'panel' => 'ssbd_front_page',
	) );

	$defaults = ssbd_hero_defaults();

	$hero_fields = array(
		'eyebrow'    => array( 'label' => __( 'Eyebrow label', 'ssbd' ), 'type' => 'text' ),
		'headline'   => array(
			'label'       => __( 'Headline — line 1', 'ssbd' ),
			'type'        => 'textarea',
			'description' => __( 'The first and largest line of the H1. Keep the business name in it: this line is the strongest on-page signal for people searching the brand by name.', 'ssbd' ),
		),
		'subhead'    => array(
			'label'       => __( 'Headline — line 2', 'ssbd' ),
			'type'        => 'text',
			'description' => __( 'The small connector line that leads into the rotating keyword below it. A single dash removes it.', 'ssbd' ),
		),
		'lede'       => array(
			'label'       => __( 'Lede paragraph', 'ssbd' ),
			'type'        => 'textarea',
			'description' => __( 'Also used as the homepage meta description and the summary AI answer engines quote, so keep it to 30–55 words and name the business. Supports {name}, {email}, {location}, {hours} and {response}.', 'ssbd' ),
		),
		'cta1_label' => array( 'label' => __( 'Primary button label', 'ssbd' ), 'type' => 'text' ),
		'cta1_url'   => array( 'label' => __( 'Primary button link', 'ssbd' ), 'type' => 'url' ),
		'cta2_label' => array( 'label' => __( 'Secondary button label', 'ssbd' ), 'type' => 'text' ),
		'cta2_url'   => array( 'label' => __( 'Secondary button link', 'ssbd' ), 'type' => 'url' ),
	);

	foreach ( $hero_fields as $field => $spec ) {
		$id = 'ssbd_hero_' . $field;

		$wp_customize->add_setting( $id, array(
			'default'           => '',
			'sanitize_callback' => 'url' === $spec['type'] ? 'ssbd_sanitize_link' : 'sanitize_textarea_field',
			'transport'         => 'postMessage',
		) );

		$wp_customize->add_control( $id, array(
			'label'       => $spec['label'],
			'description' => $spec['description'] ?? '',
			'section'     => 'ssbd_hero',
			'type'        => $spec['type'],
			'input_attrs' => array( 'placeholder' => wp_strip_all_tags( (string) ( $defaults[ $field ] ?? '' ) ) ),
		) );
	}

	$wp_customize->add_setting( 'ssbd_hero_rotator', array(
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
	) );

	$wp_customize->add_control( 'ssbd_hero_rotator', array(
		'label'       => __( 'Cycle the keyword in the headline', 'ssbd' ),
		'description' => __( 'The third line of the H1 rotates through the capability keywords below. All of them stay in the page source either way, so turning this off costs nothing in search — it only stops the movement.', 'ssbd' ),
		'section'     => 'ssbd_hero',
		'type'        => 'checkbox',
	) );

	$wp_customize->add_setting( 'ssbd_hero_orbit', array(
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
	) );

	$wp_customize->add_control( 'ssbd_hero_orbit', array(
		'label'       => __( 'Show the orbit graphic', 'ssbd' ),
		'description' => __( 'Turn off for a full-width hero with no illustration.', 'ssbd' ),
		'section'     => 'ssbd_hero',
		'type'        => 'checkbox',
	) );

	/* ---------- Capability keywords ---------- */

	$wp_customize->add_section( 'ssbd_capabilities', array(
		'title'       => __( 'Capability keywords', 'ssbd' ),
		'panel'       => 'ssbd_front_page',
		'description' => __( 'The service keywords the hero is built from — the rotating third line of the H1, the orbit graphic, the strip below it, and the line under the buttons all read this one list. Leave a slot empty for the theme default; enter a single dash to remove it.', 'ssbd' ),
	) );

	$capability_defaults = array_values( (array) ssbd_site( 'capabilities', array() ) );
	$capability_settings = array( 'ssbd_hero_meta_count' );

	for ( $i = 0; $i < SSBD_CAPABILITY_SLOTS; $i++ ) {
		$id                    = 'ssbd_capability_' . $i;
		$capability_settings[] = $id;

		$wp_customize->add_setting( $id, array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		) );

		$wp_customize->add_control( $id, array(
			/* translators: %d: keyword number */
			'label'       => sprintf( __( 'Keyword %d', 'ssbd' ), $i + 1 ),
			'section'     => 'ssbd_capabilities',
			'type'        => 'text',
			'input_attrs' => array(
				'placeholder' => isset( $capability_defaults[ $i ] )
					? (string) $capability_defaults[ $i ]
					: __( 'Empty — add your own', 'ssbd' ),
			),
		) );
	}

	$wp_customize->add_setting( 'ssbd_hero_meta_count', array(
		'default'           => 4,
		'sanitize_callback' => 'absint',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( 'ssbd_hero_meta_count', array(
		'label'       => __( 'Keywords on the hero line', 'ssbd' ),
		'description' => __( 'How many of the keywords above appear on the line under the buttons. Zero hides that line — the orbit and the strip keep the full list.', 'ssbd' ),
		'section'     => 'ssbd_capabilities',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 0, 'max' => SSBD_CAPABILITY_SLOTS, 'step' => 1 ),
	) );

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial( 'ssbd_hero', array(
			'selector'            => '.home-hero',
			'settings'            => array_merge(
				array_map( static fn( $f ) => 'ssbd_hero_' . $f, array_keys( $hero_fields ) ),
				array( 'ssbd_hero_orbit', 'ssbd_hero_rotator' ),
				$capability_settings
			),
			'container_inclusive' => true,
			'render_callback'     => static function () {
				get_template_part( 'template-parts/sections/home-hero' );
			},
		) );
	}

	/* ---------- Which sections, in what order ---------- */

	$registry = ssbd_front_page_sections();

	$wp_customize->add_section( 'ssbd_front_sections', array(
		'title'       => __( 'Sections', 'ssbd' ),
		'panel'       => 'ssbd_front_page',
		'description' => __( 'Drag to reorder, untick to hide. The hero is always first.', 'ssbd' ),
	) );

	$wp_customize->add_setting( 'ssbd_front_sections', array(
		'default'           => implode( ',', array_keys( $registry ) ),
		'sanitize_callback' => 'ssbd_sanitize_section_order',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( new SSBD_Sections_Control( $wp_customize, 'ssbd_front_sections', array(
		'label'   => __( 'Homepage sections', 'ssbd' ),
		'section' => 'ssbd_front_sections',
		'choices' => wp_list_pluck( $registry, 'label' ),
	) ) );

	/* ---------- Recent work ---------- */

	$wp_customize->add_section( 'ssbd_work', array(
		'title'       => __( 'Recent work & stats', 'ssbd' ),
		'panel'       => 'ssbd_front_page',
		'description' => __( 'The counters above the project grid, and how many projects to show. Projects themselves are edited under Projects in the main menu.', 'ssbd' ),
	) );

	$stat_defaults = (array) ssbd_site( 'stats', array() );

	for ( $i = 0; $i < SSBD_STAT_SLOTS; $i++ ) {
		foreach ( array( 'value', 'label' ) as $part ) {
			$id = 'ssbd_stat_' . $part . '_' . $i;

			$wp_customize->add_setting( $id, array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			) );

			$wp_customize->add_control( $id, array(
				'label'       => 'value' === $part
					/* translators: %d: stat number */
					? sprintf( __( 'Stat %d — big number', 'ssbd' ), $i + 1 )
					/* translators: %d: stat number */
					: sprintf( __( 'Stat %d — caption', 'ssbd' ), $i + 1 ),
				'description' => ( 'label' === $part && $i === SSBD_STAT_SLOTS - 1 )
					? __( 'Empty means "use the theme default". To remove a counter entirely, enter a single dash in either box.', 'ssbd' )
					: '',
				'section'     => 'ssbd_work',
				'type'        => 'text',
				'input_attrs' => array(
					'placeholder' => (string) ( $stat_defaults[ $i ][ $part ] ?? '' ),
				),
			) );
		}
	}

	$wp_customize->add_setting( 'ssbd_work_projects', array(
		'default'           => 3,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'ssbd_work_projects', array(
		'label'       => __( 'Projects to show', 'ssbd' ),
		'description' => __( 'The newest projects by Order. The rest stay on the Portfolio page.', 'ssbd' ),
		'section'     => 'ssbd_work',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 1, 'max' => 12, 'step' => 1 ),
	) );

	/* ---------- Latest insights ---------- */

	$wp_customize->add_section( 'ssbd_insights', array(
		'title'       => __( 'Latest insights', 'ssbd' ),
		'panel'       => 'ssbd_front_page',
		'description' => __( 'The blog cards near the bottom of the homepage. The section hides itself while the blog is empty. Its heading and supporting line are under Section headings.', 'ssbd' ),
	) );

	$wp_customize->add_setting( 'ssbd_home_posts', array(
		'default'           => 3,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'ssbd_home_posts', array(
		'label'       => __( 'Posts to show', 'ssbd' ),
		'description' => __( 'Four fills the row on a wide screen.', 'ssbd' ),
		'section'     => 'ssbd_insights',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 1, 'max' => 6, 'step' => 1 ),
	) );

	$categories = array();

	foreach ( get_categories( array( 'hide_empty' => false ) ) as $category ) {
		$categories[ $category->term_id ] = sprintf(
			/* translators: 1: category name, 2: number of posts in it */
			_n( '%1$s (%2$d post)', '%1$s (%2$d posts)', (int) $category->count, 'ssbd' ),
			$category->name,
			(int) $category->count
		);
	}

	$wp_customize->add_setting( 'ssbd_home_posts_cats', array(
		'default'           => '',
		'sanitize_callback' => 'ssbd_sanitize_category_ids',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( new SSBD_Checklist_Control( $wp_customize, 'ssbd_home_posts_cats', array(
		'label'       => __( 'Show posts from', 'ssbd' ),
		'description' => __( 'Tick the hubs this section draws from — Guides, Comparisons, Reviews, Resources. Child categories come along with their parent. To hold a specific article in place regardless of date, tick "Stick to the top of the blog" on that post; pinned posts fill the slots first.', 'ssbd' ),
		'section'     => 'ssbd_insights',
		'choices'     => $categories,
		'empty_label' => __( 'Nothing ticked means every category.', 'ssbd' ),
	) ) );

	/* ---------- Partners ---------- */

	$wp_customize->add_section( 'ssbd_partners', array(
		'title'       => __( 'Partners', 'ssbd' ),
		'panel'       => 'ssbd_front_page',
		'description' => __( 'The partner logo wall. Partners are your Affiliate Offers — the logo is the offer\'s Featured image, and the name, coupon code and button come from its fields, so edit a partner under Affiliate Offers. This section\'s heading and supporting line are under Section headings.', 'ssbd' ),
	) );

	$wp_customize->add_setting( 'ssbd_partners_count', array(
		'default'           => 6,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'ssbd_partners_count', array(
		'label'       => __( 'Partners on the front page', 'ssbd' ),
		'description' => __( 'How many to show before the "All partners" button. That button only appears when there are more than this. Between 2 and 12.', 'ssbd' ),
		'section'     => 'ssbd_partners',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 2, 'max' => 12, 'step' => 1 ),
	) );

	$wp_customize->add_setting( 'ssbd_partners_columns', array(
		'default'           => 3,
		'sanitize_callback' => 'ssbd_sanitize_partner_columns',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'ssbd_partners_columns', array(
		'label'       => __( 'Cards per row', 'ssbd' ),
		'description' => __( 'On a wide screen. Two makes each card larger; the grid drops to two across on tablets and one on phones either way.', 'ssbd' ),
		'section'     => 'ssbd_partners',
		'type'        => 'select',
		'choices'     => array(
			3 => __( '3 per row', 'ssbd' ),
			2 => __( '2 per row — larger cards', 'ssbd' ),
		),
	) );

	$wp_customize->add_setting( 'ssbd_partners_coupons', array(
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'ssbd_partners_coupons', array(
		'label'       => __( 'Show coupon codes', 'ssbd' ),
		'description' => __( 'Off hides the code on every card without clearing it from the offers themselves.', 'ssbd' ),
		'section'     => 'ssbd_partners',
		'type'        => 'checkbox',
	) );

	/* ---------- Section headings ---------- */

	$wp_customize->add_section( 'ssbd_headings', array(
		'title'       => __( 'Section headings', 'ssbd' ),
		'panel'       => 'ssbd_front_page',
		'description' => __( 'Leave empty for the theme default. Enter a single dash to remove a supporting line entirely.', 'ssbd' ),
	) );

	foreach ( $registry as $key => $section ) {
		foreach ( array( 'heading', 'lede' ) as $part ) {
			if ( 'lede' === $part && '' === $section['lede'] && ! in_array( $key, array( 'posts', 'faq' ), true ) ) {
				continue;
			}

			$id = 'ssbd_' . $part . '_' . $key;

			$wp_customize->add_setting( $id, array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_textarea_field',
				'transport'         => 'refresh',
			) );

			$wp_customize->add_control( $id, array(
				/* translators: 1: section name, 2: "heading" or "supporting line" */
				'label'       => sprintf(
					'%1$s — %2$s',
					$section['label'],
					'heading' === $part ? __( 'heading', 'ssbd' ) : __( 'supporting line', 'ssbd' )
				),
				'section'     => 'ssbd_headings',
				'type'        => 'heading' === $part ? 'text' : 'textarea',
				'input_attrs' => array( 'placeholder' => wp_strip_all_tags( (string) $section[ $part ] ) ),
			) );
		}
	}
}

/**
 * Sanitize the partner column count to one of the offered choices.
 *
 * @param mixed $value Raw value.
 * @return int
 */
function ssbd_sanitize_partner_columns( $value ) {
	return in_array( (int) $value, array( 2, 3 ), true ) ? (int) $value : 3;
}

/**
 * Sanitize the ordered section list against the registry.
 *
 * @param string $value Comma-separated keys.
 * @return string
 */
function ssbd_sanitize_section_order( $value ) {
	$allowed = array_keys( ssbd_front_page_sections() );
	$keys    = array_filter( array_map( 'sanitize_key', explode( ',', (string) $value ) ) );

	return implode( ',', array_values( array_unique( array_intersect( $keys, $allowed ) ) ) );
}

/**
 * Sanitize a comma-separated list of category ids against the ones that exist.
 *
 * @param string $value Raw value.
 * @return string
 */
function ssbd_sanitize_category_ids( $value ) {
	$ids   = array_filter( array_map( 'absint', explode( ',', (string) $value ) ) );
	$valid = array_map( 'intval', get_terms( array(
		'taxonomy'   => 'category',
		'hide_empty' => false,
		'fields'     => 'ids',
	) ) );

	return implode( ',', array_unique( array_intersect( $ids, $valid ) ) );
}

/**
 * Sanitize a link that may be a full URL or an in-page anchor.
 *
 * @param string $value Raw value.
 * @return string
 */
function ssbd_sanitize_link( $value ) {
	$value = trim( (string) $value );

	if ( '' === $value ) {
		return '';
	}

	// #services and the like are legitimate here and esc_url_raw would drop them.
	if ( str_starts_with( $value, '#' ) ) {
		return '#' . sanitize_html_class( ltrim( $value, '#' ) );
	}

	return esc_url_raw( $value );
}
add_action( 'customize_register', 'ssbd_customize_register' );
