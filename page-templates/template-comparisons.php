<?php
/**
 * Template Name: Comparisons
 *
 * Two views behind one template:
 *
 *   /comparisons/                    the hub — three pillars, every matchup
 *   /comparisons/hosting-domains/    one pillar, in full
 *
 * The pillar segment is routed here by the rewrite in inc/comparisons.php,
 * which also supplies the title, description, canonical and breadcrumb for
 * the pillar views — so the three of them are separate pages to search
 * engines, not three copies of the hub.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ssbd_comparisons = ssbd_data( 'comparisons' );
$ssbd_pillar      = ssbd_current_pillar();

if ( $ssbd_pillar ) {
	get_template_part( 'template-parts/comparisons/pillar', null, array(
		'pillar'      => $ssbd_pillar,
		'comparisons' => $ssbd_comparisons,
	) );
} else {
	get_template_part( 'template-parts/components/page-hero', null, array(
		'eyebrow'    => __( 'Comparisons', 'ssbd' ),
		'heading'    => __( 'Tool comparisons for people building websites', 'ssbd' ),
		'answer_key' => 'comparisons',
	) );

	get_template_part( 'template-parts/comparisons/hub', null, array(
		'comparisons' => $ssbd_comparisons,
	) );
}

get_template_part( 'template-parts/components/feedback' );
get_template_part( 'template-parts/components/faq', null, array( 'faqs' => ssbd_faqs( 'comparisons' ) ) );
get_template_part( 'template-parts/components/cta' );

get_footer();
