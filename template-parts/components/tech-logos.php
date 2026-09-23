<?php
/**
 * A wall of technology marks.
 *
 * The logos are simplified, drawn-here marks rather than traced brand files:
 * one visual weight, one grid, and they take their colour from CSS, so the
 * row reads as a set instead of a ransom note of pasted PNGs — and there is
 * nothing to download. Each mark is monochrome until hover, when it takes its
 * brand colour; that is the whole animation.
 *
 * Pass the keys you want, in order. Anything unknown is skipped.
 *
 * @package ssbd
 *
 * @var string[] $args['items'] Keys from the list below.
 */

defined( 'ABSPATH' ) || exit;

$ssbd_items = (array) ( $args['items'] ?? array() );

if ( ! $ssbd_items ) {
	return;
}
?>
<ul class="logo-wall">
	<?php foreach ( $ssbd_items as $ssbd_key ) : ?>
		<?php
		switch ( $ssbd_key ) {

			case 'flutter':
				$ssbd_label = __( 'Flutter', 'ssbd' );
				$ssbd_brand = '#42a5f5';
				$ssbd_mark  = '<path d="M13.8 2h7.4L7 16.2 3.3 12.5 13.8 2z"/><path d="M13.8 12.4h7.4l-3.7 3.7 3.7 3.9h-7.4l-3.7-3.9 3.7-3.7z" opacity=".72"/>';
				break;

			case 'react-native':
				$ssbd_label = __( 'React Native', 'ssbd' );
				$ssbd_brand = '#61dafb';
				$ssbd_mark  = '<circle cx="12" cy="12" r="2.1"/>'
					. '<g fill="none" stroke="currentColor" stroke-width="1.1">'
					. '<ellipse cx="12" cy="12" rx="10" ry="3.9"/>'
					. '<ellipse cx="12" cy="12" rx="10" ry="3.9" transform="rotate(60 12 12)"/>'
					. '<ellipse cx="12" cy="12" rx="10" ry="3.9" transform="rotate(120 12 12)"/>'
					. '</g>';
				break;

			case 'android':
				$ssbd_label = __( 'Android', 'ssbd' );
				$ssbd_brand = '#3ddc84';
				$ssbd_mark  = '<path d="M7.4 6.1 6.1 3.9M16.6 6.1l1.3-2.2" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>'
					. '<path d="M4.4 12.4a7.6 7.6 0 0 1 15.2 0H4.4z"/>'
					. '<circle cx="9.1" cy="9.4" r=".95" fill="var(--surface)"/>'
					. '<circle cx="14.9" cy="9.4" r=".95" fill="var(--surface)"/>'
					. '<rect x="4.4" y="13.6" width="15.2" height="6.6" rx="2.4" opacity=".72"/>';
				break;

			case 'ios':
				$ssbd_label = __( 'iOS', 'ssbd' );
				$ssbd_brand = '#a2aaad';
				$ssbd_mark  = '<path d="M16.4 12.5c0-2 1.6-3 1.7-3.1-.9-1.4-2.4-1.6-3-1.6-1.2-.1-2.4.7-3 .7s-1.6-.7-2.6-.7c-1.3 0-2.6.8-3.3 2-1.4 2.4-.4 6 1 8 .7 1 1.5 2.1 2.5 2 1 0 1.4-.6 2.6-.6s1.5.6 2.6.6 1.7-.9 2.4-1.9c.7-1 1-2 1-2 0-.1-2-.8-2-3.4z"/>'
					. '<path d="M14.5 6.1c.5-.7.9-1.6.8-2.5-.8 0-1.8.5-2.4 1.2-.5.6-.9 1.6-.8 2.5.9.1 1.8-.4 2.4-1.2z"/>';
				break;

			case 'laravel':
				$ssbd_label = __( 'Laravel', 'ssbd' );
				$ssbd_brand = '#f05340';
				// A folded "L", after Laravel's origami mark.
				$ssbd_mark  = '<path d="M4 3.4 8 5.7v10.4h11L15 20.3H4V3.4z"/>'
					. '<path d="M8 5.7 4 3.4v16.9l4-2.3V5.7z" opacity=".55"/>';
				break;

			case 'node':
				$ssbd_label = __( 'Node.js', 'ssbd' );
				$ssbd_brand = '#83cd29';
				$ssbd_mark  = '<path d="M12 1.8 21.2 7v10L12 22.2 2.8 17V7L12 1.8z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>'
					. '<path d="M9.2 15.9V8.1h1.7l3.2 4.5V8.1h1.7v7.8h-1.7l-3.2-4.5v4.5H9.2z"/>';
				break;

			case 'wordpress':
				$ssbd_label = __( 'WordPress', 'ssbd' );
				$ssbd_brand = '#21759b';
				$ssbd_mark  = '<path d="M12 2.6A9.4 9.4 0 1 0 21.4 12 9.4 9.4 0 0 0 12 2.6zm0 1.5a7.9 7.9 0 0 1 3.6.9h-.3c-.7 0-1.2.6-1.2 1.2 0 .6.3 1.1.7 1.6.3.4.6 1 .6 1.8 0 .6-.2 1.3-.5 2.2l-.7 2.2-2.4-7.1c.4 0 .8-.1.8-.1.4 0 .3-.6 0-.6 0 0-1.1.1-1.8.1-.7 0-1.8-.1-1.8-.1-.4 0-.4.6 0 .6 0 0 .3 0 .7.1l1 2.8-1.5 4.4-2.5-7.2c.4 0 .8-.1.8-.1.4 0 .3-.6 0-.6 0 0-.8.1-1.4.1A7.9 7.9 0 0 1 12 4.1zM4.9 8.6l4.2 11.5A7.9 7.9 0 0 1 4.9 8.6zm7.3 4.2 2 5.4c0 .1.1.2.1.2a7.9 7.9 0 0 1-4.6-.1l2.5-5.5zm6.4-3.4a7.9 7.9 0 0 1-3 10.1l2.5-7.1c.4-1.1.6-2 .6-2.8v-.2z"/>';
				break;

			case 'woocommerce':
				$ssbd_label = __( 'WooCommerce', 'ssbd' );
				$ssbd_brand = '#7f54b3';
				$ssbd_mark  = '<path d="M3.5 5.5h17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-7.1l-3.4 3-.7-3H3.5a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2z" opacity=".28"/>'
					. '<path d="M5 9.2c.5 0 .8.3.9.8l.6 3.1 1.5-3.3c.2-.4.4-.6.7-.6.4 0 .6.3.7.8l.6 3.1 1.5-3.3c.2-.5.5-.7.9-.6.5.1.6.5.4 1l-2.3 4.7c-.2.4-.4.6-.8.6-.4 0-.6-.3-.7-.8l-.5-2.7-1.3 2.8c-.3.5-.5.7-.9.7-.4 0-.6-.3-.7-.9L4.2 10c-.1-.5.2-.8.8-.8zM14.9 9.2c1.2 0 2 .9 2 2.4 0 1.9-1.1 3.6-2.6 3.6-1.2 0-2-.9-2-2.4 0-1.9 1.1-3.6 2.6-3.6zm4.7 0c1.2 0 2 .9 2 2.4 0 1.9-1.1 3.6-2.6 3.6-1.2 0-2-.9-2-2.4 0-1.9 1.1-3.6 2.6-3.6z"/>';
				break;

			case 'php':
				$ssbd_label = __( 'PHP', 'ssbd' );
				$ssbd_brand = '#777bb3';
				$ssbd_mark  = '<ellipse cx="12" cy="12" rx="10.5" ry="6" fill="none" stroke="currentColor" stroke-width="1.2"/>'
					. '<path d="M5.6 8.9h2.7c1.4 0 2 .7 1.8 1.9-.2 1.3-1.1 2-2.4 2H6.6l-.3 1.7H4.8l.8-5.6zm1.1 1.1-.3 1.7h.8c.7 0 1.1-.3 1.2-.9.1-.6-.2-.8-.9-.8h-.8zM10.8 7.2h1.4l-.3 1.7h1.3c1.3 0 1.8.6 1.6 1.7l-.6 3.9H12.8l.5-3.6c.1-.5 0-.7-.5-.7h-1.1l-.7 4.3H9.6l1.2-7.3zM16.1 8.9h2.7c1.4 0 2 .7 1.8 1.9-.2 1.3-1.1 2-2.4 2h-1.1l-.3 1.7h-1.5l.8-5.6zm1.1 1.1-.3 1.7h.8c.7 0 1.1-.3 1.2-.9.1-.6-.2-.8-.9-.8h-.8z"/>';
				break;

			case 'mysql':
				$ssbd_label = __( 'MySQL', 'ssbd' );
				$ssbd_brand = '#00758f';
				$ssbd_mark  = '<ellipse cx="12" cy="6.2" rx="7.5" ry="3.2"/>'
					. '<path d="M4.5 9.2v3.2c0 1.8 3.4 3.2 7.5 3.2s7.5-1.4 7.5-3.2V9.2c0 1.8-3.4 3.2-7.5 3.2S4.5 11 4.5 9.2z" opacity=".72"/>'
					. '<path d="M4.5 14.6v3.2c0 1.8 3.4 3.2 7.5 3.2s7.5-1.4 7.5-3.2v-3.2c0 1.8-3.4 3.2-7.5 3.2s-7.5-1.4-7.5-3.2z" opacity=".45"/>';
				break;

			case 'javascript':
				$ssbd_label = __( 'JavaScript', 'ssbd' );
				$ssbd_brand = '#f0db4f';
				$ssbd_mark  = '<rect x="2.6" y="2.6" width="18.8" height="18.8" rx="3" fill="none" stroke="currentColor" stroke-width="1.4"/>'
					. '<path d="M11.3 8.4h1.6v5.9c0 1.6-.8 2.4-2.3 2.4-1.3 0-2.1-.6-2.5-1.6l1.3-.8c.2.6.5.9 1 .9.5 0 .9-.3.9-1.1V8.4zM16.5 8.3c1.3 0 2.1.5 2.6 1.4l-1.3.8c-.3-.5-.6-.8-1.3-.8-.5 0-.9.3-.9.8 0 .5.3.8 1.2 1.2l.5.2c1.5.6 2.1 1.3 2.1 2.5 0 1.5-1.1 2.3-2.7 2.3-1.5 0-2.5-.7-3-1.7l1.4-.8c.4.6.8 1 1.6 1 .7 0 1.1-.3 1.1-.8 0-.6-.4-.8-1.3-1.2l-.5-.2c-1.3-.6-2-1.3-2-2.7 0-1.3 1-2.2 2.5-2.2z"/>';
				break;

			case 'ai':
				$ssbd_label = __( 'AI APIs', 'ssbd' );
				$ssbd_brand = '#1e8156';
				$ssbd_mark  = '<path d="M12 2.4 13.9 8l5.7 1.9-5.7 1.9L12 17.4l-1.9-5.6L4.4 9.9 10.1 8 12 2.4z"/>'
					. '<path d="M18.4 15.2l.9 2.4 2.4.9-2.4.9-.9 2.4-.9-2.4-2.4-.9 2.4-.9.9-2.4z" opacity=".72"/>';
				break;

			default:
				continue 2;
		}
		?>
		<li class="logo-tile" style="--brand:<?php echo esc_attr( $ssbd_brand ); ?>">
			<span class="logo-mark" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="30" height="30" fill="currentColor" focusable="false"><?php echo $ssbd_mark; // phpcs:ignore WordPress.Security.EscapingOutput -- static markup defined above. ?></svg>
			</span>
			<span class="logo-name"><?php echo esc_html( $ssbd_label ); ?></span>
		</li>
	<?php endforeach; ?>
</ul>
