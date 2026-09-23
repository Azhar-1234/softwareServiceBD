<?php
/**
 * Partner collaborations.
 *
 * The partner wall is a presentation of the Offer post type, not a second copy
 * of it: a partner you have an affiliate arrangement with is already an Offer,
 * carrying the logo, the coupon, the button label and the cloaked link. Adding
 * a Partners post type beside it would mean entering Hostinger twice and letting
 * the two drift apart.
 *
 * So these are thin accessors over inc/affiliates.php, and every partner card
 * inherits what the offer already has — click tracking, rel="sponsored" on
 * paid links, and the automatic disclosure notice on any page showing one.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * How many partners the front page shows before "View all".
 *
 * @return int
 */
function ssbd_front_partner_count() {
	$count = (int) get_theme_mod( 'ssbd_partners_count', 6 );

	// A wall of one is not a wall, and past a dozen the homepage becomes the
	// brands page. Clamped rather than validated so a bad stored value still
	// renders something sensible.
	return max( 2, min( 12, $count ) );
}

/**
 * How many partner cards sit in a row on a wide screen.
 *
 * @return int
 */
function ssbd_partner_columns() {
	return 2 === (int) get_theme_mod( 'ssbd_partners_columns', 3 ) ? 2 : 3;
}

/**
 * The grid class for the current column choice.
 *
 * @return string
 */
function ssbd_partner_grid_class() {
	return 2 === ssbd_partner_columns() ? 'partner-grid partner-grid--cols-2' : 'partner-grid';
}

/**
 * Are coupon codes shown on the cards?
 *
 * @return bool
 */
function ssbd_partner_coupons_shown() {
	return (bool) get_theme_mod( 'ssbd_partners_coupons', true );
}

/**
 * Partners, newest ordering first as set in wp-admin.
 *
 * @param string $category Category slug, or 'all'.
 * @param int    $limit    Maximum to return.
 * @return array
 */
function ssbd_partners( $category = 'all', $limit = 100 ) {
	/** Filter the partners shown on the partner wall. */
	return apply_filters( 'ssbd_partners', ssbd_offers( $category, $limit ), $category );
}

/**
 * Filter tabs for the Partners page, always led by an "All" tab.
 *
 * Built from the offer categories that actually have offers in them, so a tab
 * can never lead to an empty grid.
 *
 * @return array<array{key:string,label:string}>
 */
function ssbd_partner_filters() {
	$tabs = array( array( 'key' => 'all', 'label' => __( 'All partners', 'ssbd' ) ) );

	foreach ( ssbd_offer_groups() as $group ) {
		$tabs[] = array( 'key' => $group['slug'], 'label' => $group['title'] );
	}

	// One category is not a choice, so the row of tabs is not worth the space.
	return count( $tabs ) > 2 ? $tabs : array();
}
