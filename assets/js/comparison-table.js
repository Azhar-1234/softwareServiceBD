/**
 * The comparison table: search, filter, sort, paginate, export.
 *
 * Plain DOM work rather than a second copy of React — the bundle's filter
 * island handles one dimension and no sorting, and this is a few hundred
 * lines less than shipping a table library.
 *
 * Progressive enhancement throughout: every row is in the HTML, the toolbar
 * and pager are hidden until this file runs, and with JavaScript off the
 * whole table simply renders. Sorting writes aria-sort, the result count and
 * page position live in aria-live regions, and every control is a real
 * button, so the keyboard and a screen reader get the same page.
 */
( function () {
	'use strict';

	var PER_PAGE = 8;

	function text( el ) {
		return ( el.textContent || '' ).trim().toLowerCase();
	}

	document.querySelectorAll( '[data-comparison-table]' ).forEach( function ( root ) {
		var body = root.querySelector( '[data-ctable-body]' );

		if ( ! body ) {
			return;
		}

		var rows     = Array.prototype.slice.call( body.querySelectorAll( '[data-facet-item]' ) );
		var controls = root.querySelector( '[data-ctable-controls]' );
		var search   = root.querySelector( '[data-ctable-search]' );
		var empty    = root.querySelector( '[data-facet-empty]' );
		var pager    = root.querySelector( '[data-ctable-pager]' );
		var pageInfo = root.querySelector( '[data-ctable-pageinfo]' );
		var reset    = root.querySelector( '[data-ctable-reset]' );

		var state = { facets: {}, query: '', sort: null, dir: 1, page: 1 };
		var order = rows.slice();

		/* ---------- matching ---------- */

		function matches( row ) {
			var ok = Object.keys( state.facets ).every( function ( key ) {
				var want = state.facets[ key ];
				return want === 'any' || row.getAttribute( 'data-' + key ) === want;
			} );

			if ( ! ok || ! state.query ) {
				return ok;
			}

			return text( row ).indexOf( state.query ) !== -1;
		}

		function filtered() {
			return order.filter( matches );
		}

		function filtersActive() {
			return !! state.query || Object.keys( state.facets ).some( function ( key ) {
				return state.facets[ key ] !== 'any';
			} );
		}

		/* ---------- rendering ---------- */

		function render() {
			var visible = filtered();
			var pages   = Math.max( 1, Math.ceil( visible.length / PER_PAGE ) );

			state.page = Math.min( state.page, pages );

			var start = ( state.page - 1 ) * PER_PAGE;
			var slice = visible.slice( start, start + PER_PAGE );

			// Re-append in sort order; the browser moves rather than clones,
			// so focus and the row objects survive.
			order.forEach( function ( row ) {
				row.hidden = slice.indexOf( row ) === -1;
				body.appendChild( row );
			} );

			if ( empty ) {
				empty.hidden = visible.length > 0;
			}

			root.querySelectorAll( '[data-facet-count]' ).forEach( function ( el ) {
				el.textContent = String( visible.length );
			} );

			if ( reset ) {
				reset.hidden = ! filtersActive();
			}

			if ( pager ) {
				pager.hidden = visible.length <= PER_PAGE;
				pageInfo.textContent = pageLabel( state.page, pages, visible.length );
				pager.querySelector( '[data-ctable-prev]' ).disabled = state.page <= 1;
				pager.querySelector( '[data-ctable-next]' ).disabled = state.page >= pages;
			}
		}

		function pageLabel( page, pages, total ) {
			var first = ( page - 1 ) * PER_PAGE + 1;
			var last  = Math.min( page * PER_PAGE, total );

			return total ? first + '–' + last + ' of ' + total : '0';
		}

		/* ---------- controls ---------- */

		root.querySelectorAll( '[data-facet]' ).forEach( function ( group ) {
			var key = group.getAttribute( 'data-facet' );
			state.facets[ key ] = 'any';

			group.addEventListener( 'click', function ( event ) {
				var button = event.target.closest( 'button[data-value]' );

				if ( ! button ) {
					return;
				}

				state.facets[ key ] = button.getAttribute( 'data-value' );
				state.page = 1;

				group.querySelectorAll( 'button[data-value]' ).forEach( function ( other ) {
					other.setAttribute( 'aria-pressed', String( other === button ) );
				} );

				render();
			} );
		} );

		if ( search ) {
			var timer;
			search.addEventListener( 'input', function () {
				window.clearTimeout( timer );
				timer = window.setTimeout( function () {
					state.query = search.value.trim().toLowerCase();
					state.page  = 1;
					render();
				}, 120 );
			} );
		}

		root.querySelectorAll( '[data-ctable-sort]' ).forEach( function ( th ) {
			var key = th.getAttribute( 'data-ctable-sort' );

			th.querySelector( 'button' ).addEventListener( 'click', function () {
				state.dir  = state.sort === key ? -state.dir : 1;
				state.sort = key;
				state.page = 1;

				order.sort( function ( a, b ) {
					var av = ( a.getAttribute( 'data-' + key ) || '' ).toLowerCase();
					var bv = ( b.getAttribute( 'data-' + key ) || '' ).toLowerCase();

					return av.localeCompare( bv ) * state.dir;
				} );

				root.querySelectorAll( '[data-ctable-sort]' ).forEach( function ( other ) {
					other.setAttribute( 'aria-sort', 'none' );
				} );
				th.setAttribute( 'aria-sort', state.dir === 1 ? 'ascending' : 'descending' );

				render();
			} );
		} );

		if ( pager ) {
			pager.querySelector( '[data-ctable-prev]' ).addEventListener( 'click', function () {
				state.page = Math.max( 1, state.page - 1 );
				render();
			} );

			pager.querySelector( '[data-ctable-next]' ).addEventListener( 'click', function () {
				state.page += 1;
				render();
			} );
		}

		if ( reset ) {
			reset.addEventListener( 'click', function () {
				state.query = '';
				state.page  = 1;

				if ( search ) {
					search.value = '';
				}

				root.querySelectorAll( '[data-facet]' ).forEach( function ( group ) {
					state.facets[ group.getAttribute( 'data-facet' ) ] = 'any';

					group.querySelectorAll( 'button[data-value]' ).forEach( function ( button ) {
						button.setAttribute( 'aria-pressed', String( button.getAttribute( 'data-value' ) === 'any' ) );
					} );
				} );

				render();
			} );
		}

		/* ---------- export ---------- */

		var exportButton = root.querySelector( '[data-ctable-export]' );

		if ( exportButton ) {
			exportButton.addEventListener( 'click', function () {
				var table = root.querySelector( 'table' );
				var head  = Array.prototype.map.call( table.querySelectorAll( 'thead th' ), function ( th ) {
					return ( th.textContent || '' ).trim();
				} );

				// What is exported is what is on screen: the filters are part
				// of the question the reader asked.
				var lines = [ head ].concat( filtered().map( function ( row ) {
					return Array.prototype.map.call( row.children, function ( cell ) {
						return ( cell.textContent || '' ).replace( /\s+/g, ' ' ).trim();
					} );
				} ) );

				var csv = lines.map( function ( cells ) {
					return cells.map( function ( cell ) {
						return '"' + cell.replace( /"/g, '""' ) + '"';
					} ).join( ',' );
				} ).join( '\n' );

				var url  = URL.createObjectURL( new Blob( [ csv ], { type: 'text/csv;charset=utf-8' } ) );
				var link = document.createElement( 'a' );

				link.href = url;
				link.download = 'comparisons.csv';
				document.body.appendChild( link );
				link.click();
				link.remove();
				URL.revokeObjectURL( url );
			} );
		}

		if ( controls ) {
			controls.hidden = false;
		}

		render();
	} );
}() );
