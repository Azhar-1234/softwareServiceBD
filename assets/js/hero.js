/**
 * The rotating keyword in the homepage H1.
 *
 * Every keyword is already in the markup, printed by
 * template-parts/sections/home-hero.php, and the first one is marked active
 * there — so the headline is complete and painted before this file arrives,
 * and a crawler or a browser that never runs it reads all of them. All this
 * does is move two classes; the fade and the slide are CSS transitions.
 */
(function () {
	'use strict';

	var STEP = 2400;  // How long each keyword holds, in ms.
	var EXIT = 450;   // Slightly longer than the CSS transition on .is-leaving.

	/**
	 * Whether the visitor has asked for less motion.
	 *
	 * Read live rather than cached: the preference can change mid-visit, and
	 * a rotator that keeps running because the page was loaded before the
	 * setting flipped is the failure this is meant to avoid.
	 */
	function reduced() {
		return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
	}

	function rotate(box) {
		var words = box.querySelectorAll('.hero-rotator__word');

		if (words.length < 2) {
			return;
		}

		var index = 0;
		var timer = null;

		function step() {
			if (reduced()) {
				return;
			}

			var leaving = words[index];
			index = (index + 1) % words.length;

			leaving.classList.remove('is-active');
			leaving.classList.add('is-leaving');
			words[index].classList.add('is-active');

			// Dropping .is-leaving returns the word to the resting position it
			// enters from. It is already at opacity 0 by then, so the reset is
			// invisible — but it has to happen, or the word would enter from
			// above the next time round.
			window.setTimeout(function () {
				leaving.classList.remove('is-leaving');
			}, EXIT);
		}

		function start() {
			if (timer === null && !reduced()) {
				timer = window.setInterval(step, STEP);
			}
		}

		function stop() {
			window.clearInterval(timer);
			timer = null;
		}

		// Nothing to animate for on a background tab, and browsers throttle the
		// timer there anyway — which would land the page on an arbitrary
		// keyword the moment it came back.
		document.addEventListener('visibilitychange', function () {
			if (document.hidden) {
				stop();
			} else {
				start();
			}
		});

		start();
	}

	document.addEventListener('DOMContentLoaded', function () {
		var boxes = document.querySelectorAll('[data-hero-rotator]');

		Array.prototype.forEach.call(boxes, rotate);
	});
})();
