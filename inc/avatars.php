<?php
/**
 * Locally drawn initial avatars, in place of Gravatar.
 *
 * Gravatar is the wrong answer here twice over. It is an HTTP request to
 * secure.gravatar.com per commenter, on a site where the whole point of the
 * asset work was to keep third-party origins off the critical path; and
 * almost nobody in this audience has a Gravatar account, so what that request
 * buys is the "mystery person" silhouette — a placeholder that looks like a
 * bug rather than a design.
 *
 * Letting commenters upload their own picture is worse still: an anonymous
 * file upload endpoint is a spam and malware vector that has to be moderated
 * forever, in exchange for an image shown at 44px.
 *
 * So the avatar is drawn from what the commenter already gave us. The first
 * letter of their name, on a colour derived from a hash of their email, is
 * stable (the same person is the same colour on every post), needs no request,
 * no upload, and no storage — and it is what Slack, Notion and Linear do for
 * the same reason.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve whatever get_avatar() was handed into a name and an email.
 *
 * The parameter is deliberately loose in core — a comment, a user, an ID, a
 * post, or a bare email string — so every shape has to be unpicked here.
 *
 * @param mixed $id_or_email The value passed to get_avatar().
 * @return array{name: string, email: string}
 */
function ssbd_avatar_identity( $id_or_email ) {
	$name  = '';
	$email = '';

	if ( $id_or_email instanceof WP_Comment ) {
		$name  = (string) $id_or_email->comment_author;
		$email = (string) $id_or_email->comment_author_email;

		// A logged-in commenter's profile name beats the one typed in the form.
		if ( $id_or_email->user_id ) {
			$user = get_userdata( (int) $id_or_email->user_id );
			if ( $user ) {
				$name  = (string) $user->display_name;
				$email = (string) $user->user_email;
			}
		}
	} elseif ( $id_or_email instanceof WP_User ) {
		$name  = (string) $id_or_email->display_name;
		$email = (string) $id_or_email->user_email;
	} elseif ( $id_or_email instanceof WP_Post ) {
		$user = get_userdata( (int) $id_or_email->post_author );
		if ( $user ) {
			$name  = (string) $user->display_name;
			$email = (string) $user->user_email;
		}
	} elseif ( is_numeric( $id_or_email ) ) {
		$user = get_userdata( (int) $id_or_email );
		if ( $user ) {
			$name  = (string) $user->display_name;
			$email = (string) $user->user_email;
		}
	} elseif ( is_string( $id_or_email ) ) {
		$email = $id_or_email;
		$user  = get_user_by( 'email', $id_or_email );
		if ( $user ) {
			$name = (string) $user->display_name;
		}
	}

	return array(
		'name'  => $name,
		'email' => $email,
	);
}

/**
 * The letter to draw.
 *
 * Multibyte-aware on purpose: a Bengali name should show its own first
 * letter, not a question mark. mb_strtoupper is a no-op for scripts without
 * case, which is the correct behaviour rather than a special case.
 *
 * @param string $name  Display name.
 * @param string $email Fallback when there is no name.
 * @return string One character.
 */
function ssbd_avatar_initial( $name, $email ) {
	$source = trim( $name ) ?: trim( $email );

	// Skip anything that is not a letter or digit, so "@dev" shows D.
	if ( preg_match( '/[\p{L}\p{N}]/u', $source, $matches ) ) {
		return mb_strtoupper( $matches[0], 'UTF-8' );
	}

	return '?';
}

/**
 * A stable hue for one identity.
 *
 * Saturation and lightness are fixed so white text always clears contrast,
 * in either colour theme — only the hue varies, which is enough to tell two
 * commenters apart without any of them landing on an unreadable colour.
 *
 * @param string $email Identity to hash.
 * @param string $name  Used when there is no email.
 * @return int Degrees, 0-359.
 */
function ssbd_avatar_hue( $email, $name ) {
	$seed = strtolower( trim( $email ) ) ?: strtolower( trim( $name ) );

	return (int) ( hexdec( substr( md5( $seed ), 0, 4 ) ) % 360 );
}

/**
 * Replace Gravatar with the locally drawn avatar.
 *
 * Returning a non-null value from this filter short-circuits get_avatar()
 * entirely, so no Gravatar URL is ever built and no request is ever made.
 *
 * @param string|null $avatar      Short-circuit value.
 * @param mixed       $id_or_email Whatever get_avatar() was given.
 * @param array       $args        get_avatar() arguments.
 * @return string|null
 */
function ssbd_local_avatar( $avatar, $id_or_email, $args ) {
	/** Filter whether the theme draws avatars itself instead of using Gravatar. */
	if ( ! apply_filters( 'ssbd_use_local_avatars', true ) ) {
		return $avatar;
	}

	/*
	 * Leave WordPress's own chrome alone. The admin bar and the admin screens
	 * are not this theme's surface to restyle, they are only ever seen by
	 * people who are logged in, and the account item in particular expects an
	 * <img> it can size itself — a substitute element there is a layout bug
	 * waiting to happen rather than a design improvement.
	 */
	if ( is_admin() || doing_action( 'admin_bar_menu' ) ) {
		return $avatar;
	}

	$identity = ssbd_avatar_identity( $id_or_email );
	$size     = (int) ( $args['size'] ?? 44 );
	$initial  = ssbd_avatar_initial( $identity['name'], $identity['email'] );
	$hue      = ssbd_avatar_hue( $identity['email'], $identity['name'] );

	// aria-hidden because the name is always rendered next to it: a screen
	// reader announcing the letter as well would just read it twice.
	return sprintf(
		'<span class="ssbd-avatar" aria-hidden="true" style="--avatar-size:%1$dpx;--avatar-hue:%2$d">%3$s</span>',
		$size,
		$hue,
		esc_html( $initial )
	);
}
add_filter( 'pre_get_avatar', 'ssbd_local_avatar', 10, 3 );
