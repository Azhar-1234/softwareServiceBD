/**
 * Article page: contents highlighting, the mobile contents toggle, and the
 * copy-link button in the share row.
 *
 * All three are enhancements. The contents list is rendered by PHP with real
 * anchors, so with scripting off it still lists the sections and still jumps
 * to them — this file only adds the "you are here" highlight and the collapse.
 */
(function () {
	'use strict';

	/**
	 * Highlight the contents entry for whichever section is on screen.
	 *
	 * IntersectionObserver rather than a scroll handler: it reports only when a
	 * heading crosses the band, so there is no work on every scroll frame.
	 *
	 * The rootMargin pins the trigger line near the top of the viewport, which
	 * is where a reader considers themselves to "be" in a document.
	 *
	 * @param {Element} toc The contents nav.
	 */
	function bindScrollspy(toc) {
		var links = Array.prototype.slice.call(toc.querySelectorAll('a[href^="#"]'));

		if (!links.length || !('IntersectionObserver' in window)) {
			return;
		}

		var targets = links
			.map(function (link) {
				var id = decodeURIComponent(link.getAttribute('href').slice(1));
				return document.getElementById(id);
			})
			.filter(Boolean);

		if (!targets.length) {
			return;
		}

		var visible = [];

		function setCurrent(id) {
			links.forEach(function (link) {
				var on = decodeURIComponent(link.getAttribute('href').slice(1)) === id;
				link.classList.toggle('is-current', on);
				// aria-current tells a screen reader the same thing the colour
				// tells everybody else.
				if (on) {
					link.setAttribute('aria-current', 'true');
				} else {
					link.removeAttribute('aria-current');
				}
			});
		}

		var observer = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				var i = visible.indexOf(entry.target);

				if (entry.isIntersecting && i === -1) {
					visible.push(entry.target);
				} else if (!entry.isIntersecting && i !== -1) {
					visible.splice(i, 1);
				}
			});

			if (!visible.length) {
				return;
			}

			// Several headings can share the band; the highest one wins, which
			// is the section the reader has most recently entered.
			var top = visible.reduce(function (best, node) {
				return node.getBoundingClientRect().top < best.getBoundingClientRect().top ? node : best;
			});

			setCurrent(top.id);
		}, { rootMargin: '-80px 0px -70% 0px' });

		targets.forEach(function (target) {
			observer.observe(target);
		});

		// No heading sits in the band until the reader reaches the first one, so
		// without this the list would sit blank at the top of every article.
		// A deep link decides the opening state; otherwise it is section one.
		var hash = decodeURIComponent((window.location.hash || '').slice(1));
		var known = links.some(function (link) {
			return decodeURIComponent(link.getAttribute('href').slice(1)) === hash;
		});

		setCurrent(known ? hash : decodeURIComponent(links[0].getAttribute('href').slice(1)));
	}

	/**
	 * The mobile contents disclosure.
	 *
	 * The panel ships open in the markup so it is readable without scripting;
	 * this collapses it only once we know the toggle will work.
	 *
	 * @param {Element} toc The contents nav.
	 */
	function bindToggle(toc) {
		var button = toc.querySelector('[data-toc-toggle]');
		var panel = toc.querySelector('[data-toc-panel]');

		if (!button || !panel) {
			return;
		}

		button.hidden = false;

		function apply(open) {
			button.setAttribute('aria-expanded', String(open));
			toc.classList.toggle('is-open', open);
		}

		// Collapsed to start on the narrow layout the button belongs to; on a
		// wide screen the button is hidden by CSS and the panel stays open.
		apply(false);

		button.addEventListener('click', function () {
			apply(button.getAttribute('aria-expanded') !== 'true');
		});

		panel.addEventListener('click', function (event) {
			if (event.target.closest('a')) {
				apply(false);
			}
		});
	}

	/**
	 * The copy-link button in the share row.
	 *
	 * @param {Element} button The button.
	 */
	function bindCopyLink(button) {
		var label = button.querySelector('[data-copy-label]');
		var idle = label ? label.textContent : '';
		var timer = null;

		button.hidden = false;

		button.addEventListener('click', function () {
			window.ssbdCopy(button.getAttribute('data-copy-link')).then(function (ok) {
				if (!label) {
					return;
				}

				label.textContent = ok
					? button.getAttribute('data-copied') || 'Copied'
					: 'Press Ctrl+C';
				button.classList.toggle('is-copied', ok);

				window.clearTimeout(timer);
				timer = window.setTimeout(function () {
					label.textContent = idle;
					button.classList.remove('is-copied');
				}, 2000);
			});
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		var toc = document.querySelector('[data-toc]');

		if (toc) {
			bindScrollspy(toc);
			bindToggle(toc);
		}

		var copyLink = document.querySelector('[data-copy-link]');

		if (copyLink) {
			bindCopyLink(copyLink);
		}
	});
})();
