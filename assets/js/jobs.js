( function () {
	'use strict';

	var controller = null;
	var keywordTimer = null;
	var savedKey = 'ssbdSavedJobs';
	var recentKey = 'ssbdRecentJobs';

	function jobsUrlFromForm( form ) {
		var url = new URL( form.action, window.location.href );
		var data = new FormData( form );
		data.forEach( function ( value, key ) {
			value = String( value ).trim();
			if ( value && ! ( 'sort' === key && 'newest' === value ) ) {
				url.searchParams.set( key, value );
			}
		} );
		return url;
	}

	function showLoading() {
		var results = document.querySelector( '.jobs-results' );
		if ( ! results ) {
			return;
		}
		results.setAttribute( 'aria-busy', 'true' );
		results.innerHTML = '<div class="job-skeleton-list" aria-label="Loading jobs">' + '<div class="job-skeleton"></div>'.repeat( 4 ) + '</div>';
	}

	function replaceSection( nextDocument, selector ) {
		var current = document.querySelector( selector );
		var next = nextDocument.querySelector( selector );
		if ( current && next ) {
			current.replaceWith( next );
		}
	}

	function loadJobs( url, addHistory ) {
		if ( controller ) {
			controller.abort();
		}
		controller = new AbortController();
		showLoading();

		fetch( url, {
			headers: { 'X-Requested-With': 'XMLHttpRequest' },
			signal: controller.signal
		} ).then( function ( response ) {
			if ( ! response.ok ) {
				throw new Error( 'Job request failed' );
			}
			return response.text();
		} ).then( function ( html ) {
			var nextDocument = new DOMParser().parseFromString( html, 'text/html' );
			replaceSection( nextDocument, '.job-search' );
			replaceSection( nextDocument, '.job-categories' );
			replaceSection( nextDocument, '.jobs-results' );
			if ( addHistory ) {
				window.history.pushState( { jobs: true }, '', url );
			}
			syncSavedButtons();
		} ).catch( function ( error ) {
			if ( 'AbortError' !== error.name ) {
				window.location.assign( url );
			}
		} ).finally( function () {
			controller = null;
		} );
	}

	function readSavedJobs() {
		try {
			return JSON.parse( window.localStorage.getItem( savedKey ) || '[]' ).map( String );
		} catch ( error ) {
			return [];
		}
	}

	function syncSavedButtons() {
		var saved = readSavedJobs();
		document.querySelectorAll( '[data-save-job]' ).forEach( function ( button ) {
			var active = saved.indexOf( button.dataset.saveJob ) !== -1;
			button.classList.toggle( 'is-saved', active );
			button.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
			button.querySelector( '[aria-hidden="true"]' ).textContent = active ? '♥' : '♡';
			button.querySelector( '.screen-reader-text' ).textContent = active ? 'Remove saved job' : 'Save job';
			if ( button.querySelector( '.job-save-label' ) ) {
				button.querySelector( '.job-save-label' ).textContent = active ? 'Saved' : 'Save job';
			}
		} );
	}

	function textElement( tag, className, text ) {
		var element = document.createElement( tag );
		if ( className ) {
			element.className = className;
		}
		element.textContent = text;
		return element;
	}

	function buildSavedCard( job ) {
		var article = document.createElement( 'article' );
		article.className = 'job-card';
		var logo = textElement( 'div', 'job-company-logo', job.company ? job.company.charAt( 0 ).toUpperCase() : 'J' );
		if ( job.logo ) {
			logo.textContent = '';
			var image = document.createElement( 'img' );
			image.src = job.logo;
			image.alt = '';
			image.width = 52;
			image.height = 52;
			image.loading = 'lazy';
			logo.appendChild( image );
		}
		article.appendChild( logo );

		var body = document.createElement( 'div' );
		body.className = 'job-card-body';
		var title = document.createElement( 'h3' );
		var titleLink = document.createElement( 'a' );
		titleLink.href = job.url;
		titleLink.textContent = job.title;
		title.appendChild( titleLink );
		var titleContent = document.createElement( 'div' );
		titleContent.appendChild( title );
		if ( job.company ) { titleContent.appendChild( textElement( 'p', 'job-company', job.company ) ); }
		var titleRow = document.createElement( 'div' );
		titleRow.className = 'job-card-title-row';
		titleRow.appendChild( titleContent );
		var save = document.createElement( 'button' );
		save.className = 'job-save';
		save.type = 'button';
		save.dataset.saveJob = job.id;
		save.setAttribute( 'aria-pressed', 'true' );
		var heart = textElement( 'span', '', '♥' );
		heart.setAttribute( 'aria-hidden', 'true' );
		save.appendChild( heart );
		save.appendChild( textElement( 'span', 'screen-reader-text', 'Remove saved job' ) );
		titleRow.appendChild( save );
		body.appendChild( titleRow );
		var meta = document.createElement( 'div' );
		meta.className = 'job-meta';
		[ job.location, job.type, job.salary, job.posted_ago ].filter( Boolean ).forEach( function ( value ) { meta.appendChild( textElement( 'span', '', value ) ); } );
		body.appendChild( meta );
		if ( job.excerpt ) {
			body.appendChild( textElement( 'p', 'job-excerpt', job.excerpt ) );
		}
		var footer = document.createElement( 'div' );
		footer.className = 'job-card-footer';
		var tags = document.createElement( 'div' );
		tags.className = 'job-tags';
		( job.categories || [] ).slice( 0, 2 ).forEach( function ( category ) { tags.appendChild( textElement( 'span', '', category ) ); } );
		footer.appendChild( tags );
		var view = textElement( 'a', 'job-view-link', 'View job →' );
		view.href = job.url;
		footer.appendChild( view );
		body.appendChild( footer );
		article.appendChild( body );
		return article;
	}

	function loadSavedJobs() {
		var container = document.querySelector( '[data-saved-jobs]' );
		if ( ! container || ! window.ssbdJobs ) {
			return;
		}
		var ids = readSavedJobs();
		container.textContent = '';
		if ( ! ids.length ) {
			var empty = textElement( 'div', 'archive-empty', '' );
			empty.appendChild( textElement( 'h2', '', 'No saved jobs yet' ) );
			empty.appendChild( textElement( 'p', '', 'Save opportunities while browsing and they will appear here.' ) );
			var browse = textElement( 'a', 'btn btn--primary', 'Browse international jobs' );
			browse.href = window.ssbdJobs.jobsUrl;
			empty.appendChild( browse );
			container.appendChild( empty );
			return;
		}
		fetch( window.ssbdJobs.savedEndpoint + '?ids=' + encodeURIComponent( ids.join( ',' ) ) )
			.then( function ( response ) { return response.json(); } )
			.then( function ( jobs ) {
				container.textContent = '';
				var list = document.createElement( 'div' );
				list.className = 'job-list';
				jobs.forEach( function ( job ) { list.appendChild( buildSavedCard( job ) ); } );
				container.appendChild( list );
			} )
			.catch( function () { container.appendChild( textElement( 'p', '', 'Saved jobs could not be loaded right now.' ) ); } );
	}

	function updateRecentlyViewed() {
		var current = document.querySelector( '[data-job-view]' );
		var recent = [];
		try {
			recent = JSON.parse( window.localStorage.getItem( recentKey ) || '[]' );
		} catch ( error ) {
			recent = [];
		}
		if ( ! Array.isArray( recent ) ) {
			recent = [];
		}
		if ( current ) {
			var item = { id: current.dataset.jobId, title: current.dataset.jobTitle, url: current.dataset.jobUrl, company: current.dataset.jobCompany };
			recent = recent.filter( function ( job ) { return String( job.id ) !== String( item.id ); } );
			recent.unshift( item );
			recent = recent.slice( 0, 6 );
			try { window.localStorage.setItem( recentKey, JSON.stringify( recent ) ); } catch ( error ) {}
		}
		document.querySelectorAll( '[data-recent-jobs]' ).forEach( function ( section ) {
			var visible = recent.filter( function ( job ) { return ! current || String( job.id ) !== current.dataset.jobId; } ).slice( 0, 4 );
			if ( ! visible.length ) {
				return;
			}
			var list = section.querySelector( '.recent-jobs-list' );
			list.textContent = '';
			visible.forEach( function ( job ) {
				var link = document.createElement( 'a' );
				link.href = job.url;
				link.appendChild( textElement( 'strong', '', job.title ) );
				if ( job.company ) { link.appendChild( textElement( 'span', '', job.company ) ); }
				list.appendChild( link );
			} );
			section.hidden = false;
		} );
	}

	document.addEventListener( 'submit', function ( event ) {
		var form = event.target.closest( '[data-jobs-filter]' );
		if ( ! form ) {
			return;
		}
		event.preventDefault();
		loadJobs( jobsUrlFromForm( form ).toString(), true );
	} );

	document.addEventListener( 'change', function ( event ) {
		if ( event.target.matches( '[data-job-sort]' ) ) {
			document.querySelector( '[data-jobs-filter]' ).requestSubmit();
		}
	} );

	document.addEventListener( 'input', function ( event ) {
		if ( ! event.target.matches( '[name="keyword"]' ) ) {
			return;
		}
		window.clearTimeout( keywordTimer );
		keywordTimer = window.setTimeout( function () {
			event.target.form.requestSubmit();
		}, 450 );
	} );

	document.addEventListener( 'click', function ( event ) {
		var filterLink = event.target.closest( '[data-job-filter-link], .jobs-results .pagination a' );
		if ( filterLink ) {
			event.preventDefault();
			loadJobs( filterLink.href, true );
			return;
		}

		var saveButton = event.target.closest( '[data-save-job]' );
		if ( saveButton ) {
			var saved = readSavedJobs();
			var id = saveButton.dataset.saveJob;
			saved = saved.indexOf( id ) === -1 ? saved.concat( id ) : saved.filter( function ( savedId ) { return savedId !== id; } );
			try {
				window.localStorage.setItem( savedKey, JSON.stringify( saved ) );
			} catch ( error ) {
				return;
			}
			syncSavedButtons();
			loadSavedJobs();
			return;
		}

		var copyButton = event.target.closest( '[data-copy-job-link]' );
		if ( copyButton && navigator.clipboard ) {
			navigator.clipboard.writeText( copyButton.dataset.copyJobLink ).then( function () {
				copyButton.textContent = 'Copied';
			} );
		}
	} );

	window.addEventListener( 'popstate', function () {
		loadJobs( window.location.href, false );
	} );

	syncSavedButtons();
	loadSavedJobs();
	updateRecentlyViewed();
}() );
