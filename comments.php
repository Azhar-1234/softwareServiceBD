<?php
/**
 * Comments: a composer, then the thread.
 *
 * Without this file WordPress falls back to wp-includes/theme-compat, which
 * emits unstyled markup from a template deprecated since 3.0.
 *
 * The composer comes first, the way it does everywhere people actually leave
 * comments. WordPress's default order — read the whole thread, then find the
 * form under it — made sense when a page was a document; on an article with
 * forty replies it means scrolling past all of them to say anything.
 *
 * For a signed-in reader the form is one box and one button: avatar, a rounded
 * field, Post. Core's furniture around it — "Logged in as … Log out?", the
 * "Required fields are marked *" note, the list of allowed HTML tags — is all
 * turned off. The avatar already says who is posting, and nobody types HTML
 * into a comment box any more.
 *
 * The form is a plain <form>: comments are one of the few things WordPress
 * already does well without JavaScript, and the threading, moderation and
 * pagination all work as core intends.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

// A password-protected post must not leak its discussion.
if ( post_password_required() ) {
	return;
}

$ssbd_count  = (int) get_comments_number();
$ssbd_member = is_user_logged_in();
$ssbd_gated  = ssbd_account_setting( 'comment_login' ) && ! $ssbd_member;
?>
<section class="comments" id="comments" aria-labelledby="comments-title">

	<h2 class="comments-title" id="comments-title">
		<?php
		if ( $ssbd_count ) {
			printf(
				/* translators: %s: comment count */
				esc_html( _n( '%s comment', '%s comments', $ssbd_count, 'ssbd' ) ),
				esc_html( number_format_i18n( $ssbd_count ) )
			);
		} else {
			esc_html_e( 'Comments', 'ssbd' );
		}
		?>
	</h2>

	<?php
	/*
	 * The gate, in place of core's "You must be logged in to post a comment"
	 * line pointing at wp-login.php — the one screen this theme exists to keep
	 * readers away from. Both links carry this article's URL, so signing in
	 * comes back to the box rather than the home page.
	 */
	if ( ! comments_open() ) :
		?>
		<p class="comments-closed"><?php esc_html_e( 'Comments are closed on this article.', 'ssbd' ); ?></p>
		<?php

	elseif ( $ssbd_gated ) :
		$ssbd_return = get_permalink() . '#respond';
		?>
		<div class="comment-gate">
			<span class="comment-gate-icon" aria-hidden="true"><?php ssbd_icon( 'chat', 22 ); ?></span>

			<div class="comment-gate-body">
				<h3><?php esc_html_e( 'Join the discussion', 'ssbd' ); ?></h3>
				<p><?php esc_html_e( 'Sign in to leave a comment. It keeps the conversation civil and means you never have to type your name and email again.', 'ssbd' ); ?></p>

				<div class="comment-gate-actions">
					<a class="btn btn--primary btn--sm" href="<?php echo esc_url( ssbd_login_url( $ssbd_return ) ); ?>">
						<?php esc_html_e( 'Sign in to comment', 'ssbd' ); ?>
					</a>

					<?php if ( ssbd_account_setting( 'allow_registration' ) ) : ?>
						<a class="btn btn--ghost btn--sm" href="<?php echo esc_url( ssbd_register_url( $ssbd_return ) ); ?>">
							<?php esc_html_e( 'Create an account', 'ssbd' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php

	else :

		$ssbd_commenter = wp_get_current_commenter();
		$ssbd_required  = (bool) get_option( 'require_name_email' );
		$ssbd_mark      = $ssbd_required ? ' <span aria-hidden="true">*</span>' : '';
		$ssbd_attr      = $ssbd_required ? ' required' : '';
		$ssbd_user      = wp_get_current_user();

		/*
		 * Signed in, the box is the whole form: an avatar beside a single
		 * rounded field. The label stays in the markup for a screen reader —
		 * a placeholder is not a label, and it disappears the moment anyone
		 * starts typing.
		 */
		$ssbd_composer = sprintf(
			'<div class="composer">%1$s<div class="composer-field">
			<label class="screen-reader-text" for="comment">%2$s</label>
			<textarea id="comment" name="comment" rows="2" required placeholder="%3$s"></textarea>
			</div></div>',
			$ssbd_member ? get_avatar( $ssbd_user->ID, 36 ) : '',
			esc_html__( 'Your comment', 'ssbd' ),
			esc_attr(
				$ssbd_member
					/* translators: %s: the reader's display name */
					? sprintf( __( 'Write a comment, %s…', 'ssbd' ), $ssbd_user->display_name )
					: __( 'Write a comment…', 'ssbd' )
			)
		);

		comment_form( array(
			// The modifier drives the layout: a member posts one box, a guest
			// still needs a name and an email beside it.
			'class_form'           => $ssbd_member ? 'comment-form comment-form--member' : 'comment-form comment-form--guest',
			'class_submit'         => 'btn btn--accent btn--sm',
			'title_reply'          => __( 'Add a comment', 'ssbd' ),
			/* translators: %s: name of the person being replied to */
			'title_reply_to'       => __( 'Reply to %s', 'ssbd' ),
			'cancel_reply_link'    => __( 'Cancel', 'ssbd' ),
			'label_submit'         => __( 'Post', 'ssbd' ),
			'title_reply_before'   => '<h3 id="reply-title" class="comment-reply-title">',
			'title_reply_after'    => '</h3>',

			// Core's boilerplate, all of it, off. See the file header.
			'comment_notes_before' => '',
			'comment_notes_after'  => '',
			'logged_in_as'         => '',

			'comment_field'        => $ssbd_composer,

			// Only reached when an account is not required to comment.
			'fields'               => array(
				'author' => sprintf(
					'<div class="field"><label for="author">%1$s</label>
					<input id="author" name="author" type="text" value="%2$s" maxlength="245" autocomplete="name"%3$s></div>',
					esc_html__( 'Name', 'ssbd' ) . $ssbd_mark,
					esc_attr( $ssbd_commenter['comment_author'] ),
					$ssbd_attr
				),
				'email'  => sprintf(
					'<div class="field"><label for="email">%1$s</label>
					<input id="email" name="email" type="email" value="%2$s" maxlength="100" autocomplete="email"%3$s></div>',
					esc_html__( 'Email', 'ssbd' ) . $ssbd_mark,
					esc_attr( $ssbd_commenter['comment_author_email'] ),
					$ssbd_attr
				),
				'cookies' => sprintf(
					'<p class="comment-consent"><label for="wp-comment-cookies-consent">
					<input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"%1$s>
					<span>%2$s</span></label></p>',
					empty( $ssbd_commenter['comment_author_email'] ) ? '' : ' checked',
					esc_html__( 'Save my name and email in this browser for next time.', 'ssbd' )
				),
			),
		) );

	endif;
	?>

	<?php if ( have_comments() ) : ?>
		<ol class="comment-list">
			<?php
			wp_list_comments( array(
				'callback'     => 'ssbd_render_comment',
				'end-callback' => 'ssbd_render_comment_end',
				'style'        => 'ol',
				'avatar_size'  => 40,
			) );
			?>
		</ol>

		<?php
		the_comments_pagination( array(
			'prev_text' => __( 'Previous', 'ssbd' ),
			'next_text' => __( 'Next', 'ssbd' ),
			'class'     => 'pagination',
		) );
		?>
	<?php endif; ?>
</section>
