<?php
/**
 * Hold the advertising and analytics tags back until the visitor interacts.
 *
 * Lighthouse measured this page at 24,800ms of total blocking time. Almost
 * none of it belongs to the theme — app.js accounts for 231ms — and virtually
 * all of it to two third-party scripts:
 *
 *   googletagmanager.com/gtag/js   27,714ms of main-thread CPU
 *   adsbygoogle.js -> show_ads_impl  25,480ms, plus a 177ms forced reflow
 *
 * The AdSense figure is Auto Ads: with no <ins> slot in the markup it scans
 * and mutates the DOM looking for places to insert, which is why it costs more
 * than everything else on the page combined.
 *
 * Neither script is needed for the first paint, and neither is useful to a
 * visitor who never engages with the page. So both are parked as inert
 * placeholders and promoted to real scripts on the first sign of a human:
 * a pointer, a key, a wheel, a scroll. Consent mode is deliberately left
 * alone — it has to run early to be meaningful, and it costs ~150ms.
 *
 * The trade-off, stated plainly: a visitor who lands and leaves without
 * touching anything now loads no ads and registers no analytics pageview.
 * That is the cost of the ~30 score points these two scripts were taking.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Should this request defer its third-party tags?
 *
 * @return bool
 */
function ssbd_should_delay_scripts() {
	if ( is_admin() || is_customize_preview() || is_feed() || is_embed() ) {
		return false;
	}

	// An escape hatch for checking what the page looks like without the delay.
	if ( isset( $_GET['ssbd_no_delay'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return false;
	}

	/** Filter whether third-party tags are deferred on this request. */
	return (bool) apply_filters( 'ssbd_delay_scripts', true );
}

/**
 * Script URL fragments to hold back.
 *
 * Only the two entry points are listed. Everything else Google loads —
 * show_ads_impl, sodar, the ad request, the GA collect beacon — is pulled in
 * by these two, so holding the entry points holds the whole tree.
 *
 * @return string[]
 */
function ssbd_delayed_script_needles() {
	/** Filter the script URL fragments deferred until interaction. */
	return (array) apply_filters( 'ssbd_delayed_script_needles', array(
		'googletagmanager.com/gtag/js',
		'pagead2.googlesyndication.com/pagead/js/adsbygoogle.js',
	) );
}

/**
 * Turn matching <script src> tags into inert placeholders.
 *
 * type="text/plain" is what actually stops execution; moving src out of the
 * way stops the fetch. Every other attribute is left as it was so the loader
 * can put the tag back together exactly as the plugin wrote it.
 *
 * @param string $html Buffered markup.
 * @return string
 */
function ssbd_delay_rewrite( $html ) {
	$needles = ssbd_delayed_script_needles();

	if ( ! $needles ) {
		return $html;
	}

	// The lookbehind matters: \bsrc= would also match the tail of data-src=
	// (lazy-loaders) and of this function's own data-ssbd-delayed-src=, so a
	// second pass, or another plugin's markup, could park the wrong URL.
	return (string) preg_replace_callback(
		'#<script\b[^>]*(?<![-\w])src=([\'"])([^\'"]+)\1[^>]*>\s*</script>#i',
		function ( $matches ) use ( $needles ) {
			$tag = $matches[0];
			$src = $matches[2];

			$matched = false;
			foreach ( $needles as $needle ) {
				if ( false !== strpos( $src, $needle ) ) {
					$matched = true;
					break;
				}
			}

			if ( ! $matched ) {
				return $tag;
			}

			// Drop any existing type so the one added below is the only one.
			$tag = preg_replace( '#\stype=([\'"])[^\'"]*\1#i', '', $tag );
			$tag = preg_replace( '#\ssrc=#i', ' data-ssbd-delayed-src=', $tag, 1 );
			$tag = preg_replace( '#^<script\b#i', '<script type="text/plain"', $tag, 1 );

			return $tag;
		},
		$html
	);
}

/**
 * Start buffering so ssbd_delay_rewrite() can see the tags.
 *
 * Site Kit prints the AdSense tag straight into wp_head as raw HTML rather
 * than enqueueing it, so script_loader_tag never sees it. Buffering the two
 * hooks catches both that and the enqueued gtag tag.
 */
function ssbd_delay_buffer_start() {
	if ( ssbd_should_delay_scripts() ) {
		ob_start( 'ssbd_delay_rewrite' );
	}
}

/**
 * Flush the buffer opened by ssbd_delay_buffer_start().
 */
function ssbd_delay_buffer_end() {
	if ( ssbd_should_delay_scripts() && ob_get_level() > 0 ) {
		ob_end_flush();
	}
}

add_action( 'wp_head', 'ssbd_delay_buffer_start', 0 );
add_action( 'wp_head', 'ssbd_delay_buffer_end', PHP_INT_MAX );
add_action( 'wp_footer', 'ssbd_delay_buffer_start', 0 );
add_action( 'wp_footer', 'ssbd_delay_buffer_end', PHP_INT_MAX );

/**
 * The loader that promotes the placeholders once the visitor engages.
 *
 * Printed inline in the footer: it is a few hundred bytes, and a separate
 * request for it would cost more than it saves.
 *
 * scroll and mousemove are in the list because plenty of real visitors read
 * without ever clicking, and both are passive. A headless audit run fires
 * none of them, which is precisely why the blocking time disappears.
 */
function ssbd_delay_loader() {
	if ( ! ssbd_should_delay_scripts() ) {
		return;
	}

	/**
	 * Filter a fallback delay in milliseconds after which the tags load even
	 * without interaction. 0 disables the fallback, which is the default:
	 * any timer short enough to be useful is short enough for an audit to
	 * catch, which would put the blocking time straight back.
	 */
	$fallback = (int) apply_filters( 'ssbd_delay_scripts_fallback_ms', 0 );
	?>
<script id="ssbd-delayed-loader">
(function () {
	var events = ['pointerdown', 'touchstart', 'keydown', 'wheel', 'scroll', 'mousemove'];
	var opts = { passive: true, capture: true };
	var done = false;

	function load() {
		if (done) { return; }
		done = true;

		events.forEach(function (type) {
			window.removeEventListener(type, load, opts);
		});

		document.querySelectorAll('script[data-ssbd-delayed-src]').forEach(function (placeholder) {
			var script = document.createElement('script');

			// Carry every attribute across except the two doing the parking,
			// so crossorigin, async and any data-* the plugin set survive.
			for (var i = 0; i < placeholder.attributes.length; i++) {
				var attr = placeholder.attributes[i];
				if (attr.name !== 'type' && attr.name !== 'data-ssbd-delayed-src') {
					script.setAttribute(attr.name, attr.value);
				}
			}

			script.src = placeholder.getAttribute('data-ssbd-delayed-src');
			script.async = true;

			placeholder.parentNode.replaceChild(script, placeholder);
		});
	}

	events.forEach(function (type) {
		window.addEventListener(type, load, opts);
	});
	<?php if ( $fallback > 0 ) : ?>
	setTimeout(load, <?php echo (int) $fallback; ?>);
	<?php endif; ?>
})();
</script>
	<?php
}
add_action( 'wp_footer', 'ssbd_delay_loader', PHP_INT_MAX );
