<?php
/**
 * Header navigation: the services dropdown.
 *
 * The primary menu is an ordinary WordPress menu — a site owner can nest
 * anything under anything in Appearance > Menus and it renders as a dropdown.
 * On top of that, the Services item can fill its own dropdown from the
 * services in wp-admin, so publishing a service puts it in the header without
 * anyone touching the menu. That is a Customizer switch, not a hard rule,
 * because a hand-built menu has to stay hand-built if that is what is wanted.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is the automatic services dropdown switched on?
 *
 * @return bool
 */
function ssbd_auto_service_menu() {
	return (bool) get_theme_mod( 'ssbd_services_dropdown', true );
}

/**
 * Is the automatic comparisons dropdown switched on?
 *
 * @return bool
 */
function ssbd_auto_comparison_menu() {
	return (bool) get_theme_mod( 'ssbd_comparisons_dropdown', true );
}

/**
 * Is the automatic products dropdown switched on?
 *
 * @return bool
 */
function ssbd_auto_product_menu() {
	return (bool) get_theme_mod( 'ssbd_products_dropdown', true );
}

/**
 * The product categories as nav rows, nested one level deep.
 *
 * Product categories are the sub-items under Products in the header, the same
 * way child services are the sub-items under Services. The post type has no
 * permalinks of its own by design, so each row points at the Products page
 * with the category in a query parameter. A parameter rather than a fragment
 * because a fragment never reaches the server: this way PHP reads the category
 * and hands it to the filter island as its opening tab, with no client-side
 * guesswork about when the island is ready.
 *
 * @return array<array{title:string,url:string,depth:int}>
 */
function ssbd_product_nav_rows() {
	$rows = array();
	$base = ssbd_page_url( 'products' );

	foreach ( ssbd_product_category_tree() as $category ) {
		$rows[] = array(
			'title' => $category['label'],
			'url'   => add_query_arg( 'category', $category['key'], $base ),
			'depth' => 0,
		);

		foreach ( $category['children'] as $child ) {
			$rows[] = array(
				'title' => $child['label'],
				'url'   => add_query_arg( 'category', $child['key'], $base ),
				'depth' => 1,
			);
		}
	}

	return $rows;
}

/**
 * The comparison pillars as nav rows, all at one level.
 *
 * @return array<array{title:string,url:string,depth:int}>
 */
function ssbd_comparison_nav_rows() {
	$rows = array();

	foreach ( ssbd_pillars() as $pillar ) {
		$rows[] = array(
			'title' => $pillar['label'],
			'url'   => ssbd_pillar_url( $pillar['key'] ),
			'depth' => 0,
		);
	}

	return $rows;
}

/**
 * The services shown in the header, as a flat list of nav rows.
 *
 * Each row is title, url and depth (0 for a headline service, 1 for a child).
 *
 * @return array<array{title:string,url:string,depth:int}>
 */
function ssbd_service_nav_rows() {
	$rows = array();

	foreach ( ssbd_services() as $service ) {
		$rows[] = array(
			'title' => $service['title'],
			'url'   => ssbd_service_url( $service ),
			'depth' => 0,
		);

		foreach ( $service['children'] as $child ) {
			$rows[] = array(
				'title' => $child['title'],
				'url'   => ssbd_service_url( $child ),
				'depth' => 1,
			);
		}
	}

	return $rows;
}

/**
 * Does this menu item point at a given hub page?
 *
 * @param WP_Post $item Menu item.
 * @param string  $slug Hub page slug.
 * @return bool
 */
function ssbd_is_hub_menu_item( $item, $slug ) {
	$page = get_page_by_path( $slug );

	if ( $page && 'post_type' === $item->type && (int) $item->object_id === $page->ID ) {
		return true;
	}

	return untrailingslashit( (string) $item->url ) === untrailingslashit( ssbd_page_url( $slug ) );
}

/**
 * Hang the services under the Services item of the primary menu.
 *
 * Runs on the resolved menu objects rather than the markup, so the items are
 * real menu items: current-page highlighting, the submenu classes and the
 * mobile panel all behave exactly as they do for hand-added items.
 *
 * @param array    $items Menu items.
 * @param stdClass $args  wp_nav_menu arguments.
 * @return array
 */
function ssbd_inject_service_menu_items( $items, $args ) {
	if ( 'primary' !== ( $args->theme_location ?? '' ) ) {
		return $items;
	}

	// Which hub items fill their own dropdown, and from what.
	$hubs = array();

	if ( ssbd_auto_service_menu() ) {
		$hubs['services'] = 'ssbd_service_nav_rows';
	}
	if ( ssbd_auto_comparison_menu() ) {
		$hubs['comparisons'] = 'ssbd_comparison_nav_rows';
	}
	if ( ssbd_auto_product_menu() ) {
		$hubs['products'] = 'ssbd_product_nav_rows';
	}

	foreach ( $hubs as $slug => $callback ) {
		$items = ssbd_inject_menu_children( $items, $slug, call_user_func( $callback ) );
	}

	return $items;
}
add_filter( 'wp_nav_menu_objects', 'ssbd_inject_service_menu_items', 10, 2 );

/**
 * Hang a set of rows under whichever menu item points at a hub page.
 *
 * @param array  $items Menu items.
 * @param string $slug  Hub page slug.
 * @param array  $rows  Rows from a *_nav_rows() function.
 * @return array
 */
function ssbd_inject_menu_children( $items, $slug, $rows ) {
	if ( ! $rows ) {
		return $items;
	}

	$parent = null;

	foreach ( $items as $item ) {
		if ( ssbd_is_hub_menu_item( $item, $slug ) ) {
			$parent = $item;
			break;
		}
	}

	if ( ! $parent ) {
		return $items;
	}

	// A menu that already nests something under this item is a deliberate
	// one;
	// leave it alone rather than appending a second copy of everything.
	foreach ( $items as $item ) {
		if ( (int) $item->menu_item_parent === (int) $parent->ID ) {
			return $items;
		}
	}

	$out     = array();
	$id      = 0;
	$current = untrailingslashit( ssbd_canonical_url() );

	foreach ( $items as $item ) {
		$out[] = $item;

		if ( (int) $item->ID !== (int) $parent->ID ) {
			continue;
		}

		$level_parents = array( 0 => (int) $parent->ID );

		foreach ( $rows as $row ) {
			// Synthetic IDs live far above any real menu item ID so they can
			// never collide with one — or with the other hub's rows.
			$id++;
			$synthetic = 900000 + ( abs( crc32( $slug ) ) % 900 ) * 100 + $id;

			$child                   = clone $parent;
			$child->ID               = $synthetic;
			$child->db_id            = $synthetic;
			$child->object_id        = $synthetic;
			$child->menu_item_parent = (string) ( $level_parents[ $row['depth'] ] ?? (int) $parent->ID );
			$child->title            = $row['title'];
			$child->url              = $row['url'];
			$child->type             = 'custom';
			$child->object           = 'custom';
			$child->target           = '';
			$child->attr_title       = '';
			$child->description      = '';
			$child->xfn              = '';
			$child->classes          = array( 'menu-item', 'ssbd-service-item' );
			$child->current          = untrailingslashit( $row['url'] ) === $current;

			if ( $child->current ) {
				$child->classes[] = 'current-menu-item';
			}

			$level_parents[ $row['depth'] + 1 ] = $synthetic;

			$out[] = $child;
		}
	}

	// Core stamps menu-item-has-children on before this filter runs, so the
	// items added above have to be marked here or the dropdown never opens.
	$with_children = array();

	foreach ( $out as $item ) {
		if ( $item->menu_item_parent ) {
			$with_children[ (int) $item->menu_item_parent ] = true;
		}
	}

	foreach ( $out as $item ) {
		if ( isset( $with_children[ (int) $item->ID ] ) && ! in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
			$item->classes[] = 'menu-item-has-children';
		}
	}

	return $out;
}

/**
 * The header menu when no menu has been assigned yet.
 *
 * Mirrors what the scaffolded Primary menu contains, so the header looks the
 * same before and after a site owner opens Appearance > Menus — including the
 * services dropdown.
 */
function ssbd_fallback_nav() {
	$pages = array( 'services', 'solutions', 'products', 'portfolio', 'resources', 'comparisons', 'about' );

	$rows = array(
		'services'    => ssbd_auto_service_menu() ? ssbd_service_nav_rows() : array(),
		'comparisons' => ssbd_auto_comparison_menu() ? ssbd_comparison_nav_rows() : array(),
		'products'    => ssbd_auto_product_menu() ? ssbd_product_nav_rows() : array(),
	);

	echo '<ul>';

	foreach ( $pages as $slug ) {
		$has_children = ! empty( $rows[ $slug ] );

		printf(
			'<li class="%s"><a href="%s">%s</a>',
			$has_children ? 'menu-item-has-children' : '',
			esc_url( ssbd_page_url( $slug ) ),
			esc_html( ssbd_page_blueprint()[ $slug ]['title'] )
		);

		if ( $has_children ) {
			ssbd_fallback_service_submenu( $rows[ $slug ] );
		}

		echo '</li>';
	}

	echo '</ul>';
}

/**
 * Render a nested row list for the fallback menu.
 *
 * @param array $rows Rows from any of the *_nav_rows() functions.
 */
function ssbd_fallback_service_submenu( $rows ) {
	echo '<ul class="sub-menu">';

	$open_child_list = false;

	foreach ( $rows as $i => $row ) {
		if ( 0 === $row['depth'] ) {
			if ( $open_child_list ) {
				echo '</ul></li>';
				$open_child_list = false;
			}

			$next     = $rows[ $i + 1 ] ?? null;
			$has_kids = $next && 1 === $next['depth'];

			printf(
				'<li class="%s"><a href="%s">%s</a>',
				$has_kids ? 'menu-item-has-children' : '',
				esc_url( $row['url'] ),
				esc_html( $row['title'] )
			);

			if ( $has_kids ) {
				echo '<ul class="sub-menu">';
				$open_child_list = true;
			} else {
				echo '</li>';
			}

			continue;
		}

		printf( '<li><a href="%s">%s</a></li>', esc_url( $row['url'] ), esc_html( $row['title'] ) );
	}

	if ( $open_child_list ) {
		echo '</ul></li>';
	}

	echo '</ul>';
}
