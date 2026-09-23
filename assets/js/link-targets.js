/**
 * Open site navigation links in a separate browser context.
 *
 * The markup comes from many places: menus, templates, Gutenberg content,
 * affiliate cards and plugin output. A single delegated handler keeps the
 * behaviour consistent without rewriting each template.
 */
(function () {
	'use strict';

	var skipProtocols = {
		'mailto:': true,
		'tel:': true,
		'sms:': true,
		'javascript:': true,
	};

	function shouldOpenSeparately(link) {
		if (!link || !link.href) {
			return false;
		}

		if (
			link.hasAttribute('download') ||
			link.target === '_blank' ||
			link.closest('#wpadminbar, [data-ssbd-same-page], .pagination')
		) {
			return false;
		}

		var url;
		try {
			url = new URL(link.href, window.location.href);
		} catch (error) {
			return false;
		}

		if (skipProtocols[url.protocol]) {
			return false;
		}

		return !(url.origin === window.location.origin && url.pathname === window.location.pathname && url.hash);
	}

	function prepareLink(link) {
		if (!shouldOpenSeparately(link)) {
			return;
		}

		link.target = '_blank';
		link.rel = (link.rel ? link.rel + ' ' : '') + 'noopener';
	}

	function prepareExistingLinks() {
		document.querySelectorAll('a[href]').forEach(prepareLink);
	}

	document.addEventListener(
		'click',
		function (event) {
			var link = event.target.closest('a[href]');

			if (!link) {
				return;
			}

			prepareLink(link);
		},
		true
	);

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', prepareExistingLinks);
	} else {
		prepareExistingLinks();
	}
})();
