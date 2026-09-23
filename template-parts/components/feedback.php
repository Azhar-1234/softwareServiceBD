<?php
/**
 * "Was this helpful?" — the feedback mechanism.
 *
 * Two buttons and an optional note. The note is the useful part: it is where
 * a reader says which comparison they came for and did not find.
 *
 * @package ssbd
 *
 * @var string $args['heading']
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignore WordPress.Security.NonceVerification -- read-only display flag.
$ssbd_state = isset( $_GET['feedback'] ) ? sanitize_key( wp_unslash( $_GET['feedback'] ) ) : '';
$ssbd_head  = $args['heading'] ?? __( 'Was this page useful?', 'ssbd' );
?>
<section class="section section--tight" id="feedback" aria-labelledby="feedback-heading">
	<div class="wrap wrap--narrow">
		<div class="feedback">
			<?php if ( 'ok' === $ssbd_state ) : ?>
				<p class="form-status form-status--ok" role="status">
					<?php esc_html_e( 'Thanks — that is genuinely useful. It goes straight into what we write next.', 'ssbd' ); ?>
				</p>
			<?php else : ?>
				<?php if ( 'error' === $ssbd_state ) : ?>
					<p class="form-status form-status--err" role="alert"><?php esc_html_e( 'That did not go through. Please try again.', 'ssbd' ); ?></p>
				<?php endif; ?>

				<h2 id="feedback-heading"><?php echo esc_html( $ssbd_head ); ?></h2>
				<p class="feedback-lede"><?php esc_html_e( 'Tell us what you were looking for and did not find — that is how the next comparison gets chosen.', 'ssbd' ); ?></p>

				<form class="feedback-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="ssbd_feedback">
					<input type="hidden" name="source" value="<?php echo esc_url( ssbd_canonical_url() ); ?>">
					<?php wp_nonce_field( 'ssbd_feedback', 'ssbd_feedback_nonce' ); ?>

					<?php
					/*
					 * Honeypot. Hidden from people, irresistible to bots.
					 *
					 * Deliberately unlabelled. The wrapper is aria-hidden and the input is
					 * out of the tab order, so no assistive tech ever reached the label —
					 * but its text still sat in the page's text content, where extractors,
					 * AI crawlers and anyone reading the rendered text picked it up.
					 */
					?>
					<div aria-hidden="true" style="position:absolute;left:-9999px" tabindex="-1">
						<input id="ssbd-fb-website" type="text" name="website" tabindex="-1" autocomplete="off">
					</div>

					<div class="field">
						<label for="ssbd-feedback-note"><?php esc_html_e( 'Anything missing? (optional)', 'ssbd' ); ?></label>
						<textarea id="ssbd-feedback-note" name="note" rows="2"
							placeholder="<?php esc_attr_e( 'e.g. “I wanted Namecheap vs GoDaddy for .com.bd domains”', 'ssbd' ); ?>"></textarea>
					</div>

					<div class="feedback-actions">
						<button type="submit" name="verdict" value="yes" class="btn btn--sm btn--ghost">
							<span aria-hidden="true">👍</span> <?php esc_html_e( 'Yes, useful', 'ssbd' ); ?>
						</button>
						<button type="submit" name="verdict" value="no" class="btn btn--sm btn--ghost">
							<span aria-hidden="true">👎</span> <?php esc_html_e( 'Not really', 'ssbd' ); ?>
						</button>
					</div>
				</form>
			<?php endif; ?>
		</div>
	</div>
</section>
