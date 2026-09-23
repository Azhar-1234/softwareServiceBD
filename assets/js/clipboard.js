/**
 * Clipboard helper, shared by the partner coupon buttons and the article
 * share row.
 *
 * Exposed on window because the two callers are separate plain scripts rather
 * than modules; both declare this file as a dependency, so it is always first.
 */
(function () {
	'use strict';

	/**
	 * Copy via a throwaway textarea. The only route on a page not served over
	 * HTTPS, where navigator.clipboard does not exist — which includes most
	 * local installs and any plain-http staging site.
	 *
	 * @param {string} text Text to copy.
	 * @return {boolean} Whether the copy took.
	 */
	function legacyCopy(text) {
		var field = document.createElement('textarea');

		field.value = text;
		field.setAttribute('readonly', '');
		field.style.position = 'fixed';
		field.style.opacity = '0';
		document.body.appendChild(field);
		field.select();

		var ok = false;

		try {
			ok = document.execCommand('copy');
		} catch (e) {
			ok = false;
		}

		document.body.removeChild(field);

		return ok;
	}

	/**
	 * Put text on the clipboard, resolving false when that is not possible.
	 *
	 * A rejection from navigator.clipboard falls through to the textarea
	 * rather than giving up: the modern API is refused in more situations than
	 * it is absent — permission denied, an embedded webview, a document
	 * without focus — and the old route still works in most of them.
	 *
	 * @param {string} text Text to copy.
	 * @return {Promise<boolean>}
	 */
	window.ssbdCopy = function (text) {
		if (navigator.clipboard && window.isSecureContext) {
			return navigator.clipboard.writeText(text).then(function () {
				return true;
			}, function () {
				return legacyCopy(text);
			});
		}

		return Promise.resolve(legacyCopy(text));
	};
})();
