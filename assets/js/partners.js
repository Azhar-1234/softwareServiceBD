/**
 * Partner cards: copy a coupon code to the clipboard.
 *
 * The code itself is always in the markup as selectable text. The copy button
 * ships hidden and is revealed here, so a browser that cannot run this — or
 * cannot reach the clipboard — never shows a button that does nothing.
 */
(function () {
	'use strict';

	var RESET = 2000; // How long the "Copied" state stays, in ms.

	// The clipboard routine lives in clipboard.js, declared as this script's
	// dependency, because the article share row needs exactly the same thing.
	var copy = window.ssbdCopy;

	document.addEventListener('DOMContentLoaded', function () {
		var buttons = document.querySelectorAll('[data-partner-copy]');

		Array.prototype.forEach.call(buttons, function (button) {
			var label = button.querySelector('[data-partner-copy-label]');
			var idle = label ? label.textContent : '';
			var timer = null;

			button.hidden = false;

			button.addEventListener('click', function () {
				copy(button.getAttribute('data-partner-copy')).then(function (ok) {
					if (!label) {
						return;
					}

					// Announced as well as shown: the button's own label is the
					// only feedback, and a sighted-only change tells a screen
					// reader user nothing.
					label.textContent = ok ? button.getAttribute('data-copied') || 'Copied' : 'Press Ctrl+C';
					button.classList.toggle('is-copied', ok);

					window.clearTimeout(timer);
					timer = window.setTimeout(function () {
						label.textContent = idle;
						button.classList.remove('is-copied');
					}, RESET);
				});
			});
		});
	});
})();
