<?php
/**
 * The SEO & FAQ workbench: one screen per post for everything an answer
 * engine reads, reachable straight from the posts list.
 *
 * The fields already existed, but they were scattered and two of them were
 * invisible. The meta description and GEO answer live in a meta box that
 * inc/seo.php hides whenever an SEO plugin is active — correct, because the
 * theme stands down from head output then, but it means that on this install
 * (Yoast is active) there was no visible home for the answer summary at all.
 * The FAQ sat in a third box further down the edit screen.
 *
 * So: a "SEO & FAQ" row action next to Edit / Quick Edit / Trash / View, and
 * a screen behind it that holds the keyphrase, the description, the answer
 * summary and the FAQ together, with a checklist that reads the post and says
 * what is missing. Nothing here is a second copy of Gutenberg — it edits meta
 * only, and never touches post content.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * The workbench's admin page slug.
 */
const SSBD_SEO_PAGE = 'ssbd-seo';

/**
 * Where each field is stored.
 *
 * `own` is the theme's key, always written so that nothing is lost if the SEO
 * plugin is later deactivated. `plugin` is the active plugin's key, written in
 * addition when one is running — because that is the copy actually rendered
 * into <head> while the plugin owns head output. Writing only one of the two
 * is how a site ends up with a description that shows in the editor and a
 * different one in the SERP.
 *
 * @return array<string, array{own: string, plugin: string}>
 */
function ssbd_seo_field_keys() {
	if ( defined( 'WPSEO_VERSION' ) ) {
		$plugin = array(
			'title'       => '_yoast_wpseo_title',
			'keyphrase'   => '_yoast_wpseo_focuskw',
			'description' => '_yoast_wpseo_metadesc',
		);
	} elseif ( class_exists( 'RankMath' ) ) {
		$plugin = array(
			'title'       => 'rank_math_title',
			'keyphrase'   => 'rank_math_focus_keyword',
			'description' => 'rank_math_description',
		);
	} else {
		$plugin = array();
	}

	return array(
		/*
		 * The <title> tag. Distinct from the post's own title/H1 on purpose:
		 * a headline written to read well on the page and a title tag written
		 * to read well truncated in a SERP at ~50-60 characters are not always
		 * the same sentence, and this field lets them differ without ever
		 * touching the post's actual heading, slug or content.
		 */
		'title'       => array(
			'own'    => '_ssbd_seo_title',
			'plugin' => $plugin['title'] ?? '',
		),
		'keyphrase'   => array(
			'own'    => '_ssbd_focus_keyphrase',
			'plugin' => $plugin['keyphrase'] ?? '',
		),
		'description' => array(
			'own'    => '_ssbd_meta_description',
			'plugin' => $plugin['description'] ?? '',
		),
		'answer'      => array(
			'own'    => '_ssbd_answer_summary',
			'plugin' => '',
		),
	);
}

/**
 * The active SEO plugin's display name, or '' when the theme owns head output.
 *
 * @return string
 */
function ssbd_seo_plugin_name() {
	if ( defined( 'WPSEO_VERSION' ) ) {
		return __( 'Yoast SEO', 'ssbd' );
	}

	if ( class_exists( 'RankMath' ) ) {
		return __( 'Rank Math', 'ssbd' );
	}

	return '';
}

/**
 * Read a workbench field, preferring whatever the active plugin holds.
 *
 * @param int    $post_id Post ID.
 * @param string $field   Field key.
 * @return string
 */
function ssbd_seo_get_field( $post_id, $field ) {
	$keys = ssbd_seo_field_keys()[ $field ] ?? null;

	if ( ! $keys ) {
		return '';
	}

	if ( $keys['plugin'] ) {
		$value = (string) get_post_meta( $post_id, $keys['plugin'], true );

		if ( '' !== trim( $value ) ) {
			return $value;
		}
	}

	return (string) get_post_meta( $post_id, $keys['own'], true );
}

/**
 * Whether this post type shows the FAQ field.
 *
 * Posts only. The accordion is rendered by single.php, and FAQPage structured
 * data for questions that are not visible on the page is a structured-data
 * violation — so a page, whose templates render their own FAQ list from
 * inc/data/faqs.php, must not get one here.
 *
 * @param string $type Post type.
 * @return bool
 */
function ssbd_seo_supports_faq( $type ) {
	/** Filter the post types whose FAQ is editable in the workbench. */
	return in_array( $type, (array) apply_filters( 'ssbd_seo_faq_types', array( 'post' ) ), true );
}

/**
 * The workbench URL for one post.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ssbd_seo_workbench_url( $post_id = 0 ) {
	$args = array( 'page' => SSBD_SEO_PAGE );

	if ( $post_id ) {
		$args['post'] = (int) $post_id;
	}

	return add_query_arg( $args, admin_url( 'tools.php' ) );
}

/**
 * Add the row action beside Edit / Quick Edit / Trash / View.
 *
 * Rebuilt rather than appended so the link lands after View, where the eye
 * already is, instead of after Trash.
 *
 * @param array   $actions Existing row actions.
 * @param WP_Post $post    Row's post.
 * @return array
 */
function ssbd_seo_row_action( $actions, $post ) {
	if ( ! current_user_can( 'edit_post', $post->ID ) ) {
		return $actions;
	}

	$link = sprintf(
		'<a href="%s" aria-label="%s">%s</a>',
		esc_url( ssbd_seo_workbench_url( $post->ID ) ),
		esc_attr( sprintf( /* translators: %s: post title */ __( 'Edit SEO and FAQ for “%s”', 'ssbd' ), $post->post_title ) ),
		esc_html__( 'SEO & FAQ', 'ssbd' )
	);

	$rebuilt = array();

	foreach ( $actions as $key => $markup ) {
		$rebuilt[ $key ] = $markup;

		if ( 'view' === $key ) {
			$rebuilt['ssbd_seo'] = $link;
		}
	}

	if ( ! isset( $rebuilt['ssbd_seo'] ) ) {
		$rebuilt['ssbd_seo'] = $link;
	}

	return $rebuilt;
}
add_filter( 'post_row_actions', 'ssbd_seo_row_action', 10, 2 );
add_filter( 'page_row_actions', 'ssbd_seo_row_action', 10, 2 );

/**
 * Register the screen under Tools.
 *
 * A real menu entry rather than a hidden page: without a post in the query
 * string it lists what is missing across every post, which is worth reaching
 * on its own.
 */
function ssbd_seo_workbench_menu() {
	add_submenu_page(
		'tools.php',
		__( 'SEO & FAQ', 'ssbd' ),
		__( 'SEO & FAQ', 'ssbd' ),
		'edit_posts',
		SSBD_SEO_PAGE,
		'ssbd_seo_workbench_render'
	);
}
add_action( 'admin_menu', 'ssbd_seo_workbench_menu' );

/**
 * Handle the save, then redirect so a refresh cannot repost.
 */
function ssbd_seo_workbench_save() {
	if ( ! isset( $_POST['ssbd_seo_workbench_nonce'] ) ) {
		return;
	}

	$post_id = isset( $_POST['ssbd_post_id'] ) ? absint( $_POST['ssbd_post_id'] ) : 0;

	if ( ! $post_id
		|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['ssbd_seo_workbench_nonce'] ) ), 'ssbd_seo_workbench_' . $post_id )
		|| ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( esc_html__( 'You are not allowed to edit this post.', 'ssbd' ) );
	}

	$values = array(
		'title'       => isset( $_POST['ssbd_seo_title'] ) ? sanitize_text_field( wp_unslash( $_POST['ssbd_seo_title'] ) ) : '',
		'keyphrase'   => isset( $_POST['ssbd_keyphrase'] ) ? sanitize_text_field( wp_unslash( $_POST['ssbd_keyphrase'] ) ) : '',
		'description' => isset( $_POST['ssbd_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['ssbd_description'] ) ) : '',
		'answer'      => isset( $_POST['ssbd_answer'] ) ? sanitize_textarea_field( wp_unslash( $_POST['ssbd_answer'] ) ) : '',
	);

	foreach ( ssbd_seo_field_keys() as $field => $keys ) {
		foreach ( array( $keys['own'], $keys['plugin'] ) as $meta_key ) {
			if ( ! $meta_key ) {
				continue;
			}

			if ( '' === $values[ $field ] ) {
				delete_post_meta( $post_id, $meta_key );
			} else {
				update_post_meta( $post_id, $meta_key, $values[ $field ] );
			}
		}
	}

	/*
	 * The slug, the featured image and its alt text are post and attachment
	 * data rather than SEO meta, so they are written separately. They belong
	 * on this screen anyway: all three are pieces a search result is built
	 * from, and until now each one lived behind a different screen — which is
	 * why the alt text in particular kept being the thing nobody had set.
	 */
	ssbd_seo_workbench_save_slug( $post_id );
	ssbd_seo_workbench_save_image( $post_id );
	ssbd_seo_workbench_save_jsonld( $post_id );

	$ssbd_faq_unparsed = false;

	if ( ssbd_seo_supports_faq( get_post_type( $post_id ) ) ) {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- parsed and sanitized field by field below.
		$raw   = isset( $_POST['ssbd_faq_text'] ) ? wp_unslash( $_POST['ssbd_faq_text'] ) : '';
		$pairs = ssbd_faq_parse_text( $raw );
		$clean = array();

		// Capped at 20 for the same reason the old repeater was: this is a
		// per-article FAQ, not a knowledge base.
		foreach ( array_slice( $pairs, 0, 20 ) as $pair ) {
			$q = sanitize_text_field( $pair['q'] );
			$a = trim( wp_kses( $pair['a'], ssbd_faq_allowed_html() ) );

			if ( '' !== $q && '' !== $a ) {
				$clean[] = array(
					'q' => $q,
					'a' => $a,
				);
			}
		}

		if ( $clean ) {
			update_post_meta( $post_id, '_ssbd_faqs', wp_json_encode( $clean ) );
		} else {
			delete_post_meta( $post_id, '_ssbd_faqs' );

			/*
			 * Text was typed in, but none of it turned into a question and an
			 * answer — a paste with no "?" anywhere and no blank line between
			 * entries and no "Q:"/"A:" markers gives the parser nothing to
			 * find a boundary on. Saving silently in that state is how this
			 * bug was reported in the first place: the box comes back empty
			 * next load and there is nothing on screen explaining why. This
			 * flag reaches the redirect below and turns into a visible
			 * notice instead.
			 */
			if ( '' !== trim( $raw ) ) {
				$ssbd_faq_unparsed = true;
			}
		}
	}

	$ssbd_redirect_args = array( 'ssbd-saved' => '1' );
	if ( $ssbd_faq_unparsed ) {
		$ssbd_redirect_args['ssbd-faq-unparsed'] = '1';
	}

	wp_safe_redirect( add_query_arg( $ssbd_redirect_args, ssbd_seo_workbench_url( $post_id ) ) );
	exit;
}
add_action( 'admin_init', 'ssbd_seo_workbench_save' );

/**
 * Write the URL slug.
 *
 * @param int $post_id Post ID.
 */
function ssbd_seo_workbench_save_slug( $post_id ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- the caller verified the nonce.
	if ( ! isset( $_POST['ssbd_slug'] ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- as above.
	$requested = sanitize_title( wp_unslash( $_POST['ssbd_slug'] ) );
	$post      = get_post( $post_id );

	/*
	 * An empty box means "leave it alone", not "rebuild it from the title".
	 * WordPress would happily do the latter, but clearing a field by accident
	 * must never silently change the address of something already published.
	 */
	if ( '' === $requested || ! $post || $requested === $post->post_name ) {
		return;
	}

	wp_update_post(
		array(
			'ID'        => $post_id,
			'post_name' => wp_unique_post_slug( $requested, $post_id, $post->post_status, $post->post_type, $post->post_parent ),
		)
	);
}

/**
 * Write the featured image and its alt text.
 *
 * Order matters: the thumbnail is set first so that alt text typed at the
 * same time as choosing a brand-new image lands on that image rather than on
 * whatever used to be there.
 *
 * @param int $post_id Post ID.
 */
function ssbd_seo_workbench_save_image( $post_id ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- the caller verified the nonce.
	if ( isset( $_POST['ssbd_thumb_id'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- as above.
		$thumb_id = absint( $_POST['ssbd_thumb_id'] );

		if ( ! $thumb_id ) {
			delete_post_thumbnail( $post_id );
		} elseif ( 'attachment' === get_post_type( $thumb_id ) ) {
			set_post_thumbnail( $post_id, $thumb_id );
		}
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- as above.
	if ( ! isset( $_POST['ssbd_thumb_alt'] ) ) {
		return;
	}

	$thumb_id = get_post_thumbnail_id( $post_id );

	if ( ! $thumb_id ) {
		return;
	}

	/*
	 * Alt text is attachment meta, not post meta — the same image carries one
	 * description everywhere it is used, so editing it here also fixes it in
	 * every other post that shows the picture.
	 */
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- as above.
	$alt = sanitize_text_field( wp_unslash( $_POST['ssbd_thumb_alt'] ) );

	if ( '' === $alt ) {
		delete_post_meta( $thumb_id, '_wp_attachment_image_alt' );
	} else {
		update_post_meta( $thumb_id, '_wp_attachment_image_alt', $alt );
	}
}

/**
 * Where a post's custom JSON-LD block may be printed.
 *
 * @return array<string, string> Value => label.
 */
function ssbd_seo_jsonld_placements() {
	return array(
		'footer' => __( 'Footer — beside the theme\'s own graph (default)', 'ssbd' ),
		'head'   => __( 'Head — inside <head>, before the page renders', 'ssbd' ),
		'off'    => __( 'Do not output it', 'ssbd' ),
	);
}

/**
 * Write the custom JSON-LD block and where it goes.
 *
 * Invalid JSON is stored rather than rejected. Losing a half-finished block
 * because a bracket is missing is the worse failure of the two — the screen
 * says it is invalid, and the front end simply declines to print it, so
 * nothing broken ever reaches a crawler.
 *
 * @param int $post_id Post ID.
 */
function ssbd_seo_workbench_save_jsonld( $post_id ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- the caller verified the nonce.
	if ( isset( $_POST['ssbd_custom_json_where'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- as above.
		$where = sanitize_key( wp_unslash( $_POST['ssbd_custom_json_where'] ) );

		if ( isset( ssbd_seo_jsonld_placements()[ $where ] ) ) {
			update_post_meta( $post_id, '_ssbd_custom_jsonld_where', $where );
		}
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- as above.
	if ( ! isset( $_POST['ssbd_custom_json'] ) ) {
		return;
	}

	/*
	 * Not sanitize_textarea_field(): that strips the tags and entities out of
	 * a string value, and JSON is not HTML. It is validated by decoding it
	 * instead, and re-encoded from the decoded array on output, which is what
	 * actually guarantees nothing dangerous is printed.
	 */
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- validated by json_decode on output; see above.
	$raw = trim( wp_unslash( $_POST['ssbd_custom_json'] ) );

	if ( '' === $raw ) {
		delete_post_meta( $post_id, '_ssbd_custom_jsonld' );
		return;
	}

	update_post_meta( $post_id, '_ssbd_custom_jsonld', wp_slash( $raw ) );
}

/**
 * Load the media frame, so the featured image can be picked on this screen.
 *
 * @param string $hook Current admin page.
 */
function ssbd_seo_workbench_assets( $hook ) {
	if ( 'tools_page_' . SSBD_SEO_PAGE !== $hook ) {
		return;
	}

	wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'ssbd_seo_workbench_assets' );

/**
 * Does the keyphrase appear in this text?
 *
 * Case- and whitespace-insensitive, and punctuation between the words is
 * ignored, so "web host in 2026" still matches "web host, in 2026".
 *
 * @param string $haystack Text to search.
 * @param string $keyphrase Focus keyphrase.
 * @return bool
 */
function ssbd_seo_contains_keyphrase( $haystack, $keyphrase ) {
	$keyphrase = trim( (string) $keyphrase );

	if ( '' === $keyphrase ) {
		return false;
	}

	$normalise = static function ( $text ) {
		$text = wp_strip_all_tags( (string) $text );
		$text = preg_replace( '/[^\p{L}\p{N}]+/u', ' ', mb_strtolower( $text ) );

		return trim( (string) $text );
	};

	$needle = $normalise( $keyphrase );

	return '' !== $needle && false !== strpos( $normalise( $haystack ), $needle );
}

/**
 * Does the answer summary name what it is about?
 *
 * The whole point of this field is that an engine can lift the passage out of
 * the page and quote it on its own. A summary opening "It costs from BDT
 * 25,000" reads fine directly under the headline and is useless once the
 * headline is gone, because nothing in the sentence says what "it" is. So the
 * opening word is checked for a bare pronoun — the one failure that turns a
 * good summary into an unquotable one.
 *
 * @param string $answer Answer summary.
 * @return bool
 */
function ssbd_seo_answer_names_subject( $answer ) {
	$answer = trim( wp_strip_all_tags( (string) $answer ) );

	if ( '' === $answer ) {
		return false;
	}

	$words = preg_split( '/\s+/u', $answer, 2 );
	$first = mb_strtolower( preg_replace( '/[^\p{L}]/u', '', $words[0] ?? '' ) );

	/*
	 * "We" and "our" are left off deliberately: on a company's own site they
	 * do name a subject, and flagging them would be telling the writer off for
	 * the house voice.
	 */
	$bare = (array) apply_filters(
		'ssbd_seo_bare_pronouns',
		array( 'it', 'this', 'that', 'they', 'these', 'those', 'there', 'he', 'she', 'them' )
	);

	return ! in_array( $first, $bare, true );
}

/**
 * The checklist shown beside the fields.
 *
 * Computed in PHP against the saved post rather than in the browser against a
 * live editor: this screen does not hold the content, so there is nothing to
 * watch, and reading the stored post is both simpler and honest about what is
 * actually published.
 *
 * @param WP_Post $post Post being edited.
 * @return array<int, array{state: string, label: string}>
 */
function ssbd_seo_audit( $post ) {
	$seo_title   = ssbd_seo_get_field( $post->ID, 'title' );
	$keyphrase   = ssbd_seo_get_field( $post->ID, 'keyphrase' );
	$description = ssbd_seo_get_field( $post->ID, 'description' );
	$answer      = ssbd_seo_get_field( $post->ID, 'answer' );
	$faqs        = ssbd_post_faqs( $post->ID );

	$content   = (string) $post->post_content;
	$plain     = wp_strip_all_tags( strip_shortcodes( $content ) );
	$words     = str_word_count( $plain );
	$desc_len  = mb_strlen( $description );
	$ans_words = $answer ? str_word_count( wp_strip_all_tags( $answer ) ) : 0;

	// The opening ~600 characters stand in for "the first paragraph": blocks
	// make the real first <p> awkward to isolate, and the point of the check
	// is only that the phrase appears early.
	$opening = mb_substr( $plain, 0, 600 );

	preg_match_all( '/<h[23][^>]*>(.*?)<\/h[23]>/is', $content, $headings );
	$heading_text = implode( ' ', $headings[1] ?? array() );

	$checks = array();

	$title_len = mb_strlen( $seo_title ? $seo_title : wp_strip_all_tags( get_the_title( $post ) ) . ' - ' . ssbd_site( 'legal_name', get_bloginfo( 'name' ) ) );

	if ( $title_len < 50 || $title_len > 60 ) {
		$checks[] = array(
			'state' => 'warn',
			/* translators: %d: character count */
			'label' => sprintf( __( 'Title tag is %d characters — aim for 50–60. Set the SEO title above to override the default.', 'ssbd' ), $title_len ),
		);
	} else {
		$checks[] = array(
			'state' => 'good',
			/* translators: %d: character count */
			'label' => sprintf( __( 'Title tag length is right (%d characters)', 'ssbd' ), $title_len ),
		);
	}

	$checks[] = array(
		'state' => $keyphrase ? 'good' : 'bad',
		'label' => $keyphrase
			? sprintf( /* translators: %s: keyphrase */ __( 'Focus keyphrase set: “%s”', 'ssbd' ), $keyphrase )
			: __( 'No focus keyphrase set — every check below depends on it', 'ssbd' ),
	);

	if ( $keyphrase ) {
		$checks[] = array(
			'state' => ssbd_seo_contains_keyphrase( $post->post_title, $keyphrase ) ? 'good' : 'warn',
			'label' => __( 'Keyphrase appears in the title', 'ssbd' ),
		);
		$checks[] = array(
			'state' => ssbd_seo_contains_keyphrase( str_replace( '-', ' ', $post->post_name ), $keyphrase ) ? 'good' : 'warn',
			'label' => __( 'Keyphrase appears in the URL slug', 'ssbd' ),
		);
		$checks[] = array(
			'state' => ssbd_seo_contains_keyphrase( $opening, $keyphrase ) ? 'good' : 'warn',
			'label' => __( 'Keyphrase appears in the opening paragraph', 'ssbd' ),
		);
		$checks[] = array(
			'state' => ssbd_seo_contains_keyphrase( $heading_text, $keyphrase ) ? 'good' : 'warn',
			'label' => __( 'Keyphrase appears in at least one subheading', 'ssbd' ),
		);
		$checks[] = array(
			'state' => ssbd_seo_contains_keyphrase( $description, $keyphrase ) ? 'good' : 'warn',
			'label' => __( 'Keyphrase appears in the meta description', 'ssbd' ),
		);
	}

	if ( ! $desc_len ) {
		$desc_state = 'bad';
		$desc_label = __( 'No meta description — Google will invent one from the page', 'ssbd' );
	} elseif ( $desc_len < 120 || $desc_len > 158 ) {
		$desc_state = 'warn';
		/* translators: %d: character count */
		$desc_label = sprintf( __( 'Meta description is %d characters — aim for 120–158', 'ssbd' ), $desc_len );
	} else {
		$desc_state = 'good';
		/* translators: %d: character count */
		$desc_label = sprintf( __( 'Meta description length is right (%d characters)', 'ssbd' ), $desc_len );
	}
	$checks[] = array(
		'state' => $desc_state,
		'label' => $desc_label,
	);

	if ( ! $ans_words ) {
		$ans_state = 'warn';
		$ans_label = __( 'No answer summary — this is the passage an AI engine quotes', 'ssbd' );
	} elseif ( $ans_words < 40 || $ans_words > 60 ) {
		$ans_state = 'warn';
		/* translators: %d: word count */
		$ans_label = sprintf( __( 'Answer summary is %d words — aim for 40–60', 'ssbd' ), $ans_words );
	} else {
		$ans_state = 'good';
		/* translators: %d: word count */
		$ans_label = sprintf( __( 'Answer summary is a quotable length (%d words)', 'ssbd' ), $ans_words );
	}
	$checks[] = array(
		'state' => $ans_state,
		'label' => $ans_label,
	);

	/*
	 * The remaining answer checks only mean anything once something is
	 * written, and repeating "no answer summary" three times would bury the
	 * rest of the list.
	 */
	if ( $ans_words ) {
		if ( $keyphrase ) {
			$checks[] = array(
				'state' => ssbd_seo_contains_keyphrase( $answer, $keyphrase ) ? 'good' : 'warn',
				'label' => __( 'Keyphrase appears in the answer summary', 'ssbd' ),
			);
		}

		$names_subject = ssbd_seo_answer_names_subject( $answer );

		$checks[] = array(
			'state' => $names_subject ? 'good' : 'warn',
			'label' => $names_subject
				? __( 'Answer summary names its subject, so it can be quoted alone', 'ssbd' )
				: __( 'Answer summary opens with a pronoun — quoted on its own it never says what it is about', 'ssbd' ),
		);
	}

	if ( ssbd_seo_supports_faq( $post->post_type ) ) {
		$count = count( $faqs );
		$checks[] = array(
			'state' => $count >= 3 ? 'good' : ( $count ? 'warn' : 'bad' ),
			'label' => $count
				/* translators: %d: number of FAQ entries */
				? sprintf( _n( '%d FAQ question — three or more earns the FAQPage markup its place', '%d FAQ questions, emitting FAQPage structured data', $count, 'ssbd' ), $count )
				: __( 'No FAQ — no FAQPage structured data on this post', 'ssbd' ),
		);
	}

	$slug     = (string) $post->post_name;
	$slug_len = mb_strlen( rawurldecode( $slug ) );

	if ( '' === $slug ) {
		$checks[] = array(
			'state' => 'bad',
			'label' => __( 'No URL slug yet — the post has no readable address', 'ssbd' ),
		);
	} elseif ( $slug_len > 75 ) {
		$checks[] = array(
			'state' => 'warn',
			/* translators: %d: character count */
			'label' => sprintf( __( 'URL slug is %d characters — shorter is easier to read, share and cite', 'ssbd' ), $slug_len ),
		);
	} else {
		$checks[] = array(
			'state' => 'good',
			/* translators: %d: character count */
			'label' => sprintf( __( 'URL slug is a sensible length (%d characters)', 'ssbd' ), $slug_len ),
		);
	}

	$links    = ssbd_seo_link_audit( $post );
	$internal = count( $links['internal'] );
	$external = count( $links['external'] );

	$checks[] = array(
		'state' => $internal >= 3 ? 'good' : ( $internal ? 'warn' : 'bad' ),
		'label' => $internal
			/* translators: %d: number of links */
			? sprintf( _n( '%d internal link — three or more spreads authority through the site', '%d internal links to other pages here', $internal, 'ssbd' ), $internal )
			: __( 'No internal links — nothing here points at the rest of the site', 'ssbd' ),
	);

	$checks[] = array(
		'state' => $external >= 1 ? 'good' : 'warn',
		'label' => $external
			/* translators: %d: number of links */
			? sprintf( _n( '%d external link, citing a source outside the site', '%d external links citing sources', $external, 'ssbd' ), $external )
			: __( 'No external links — citing a source is what makes a claim checkable', 'ssbd' ),
	);

	if ( $links['problems'] ) {
		$checks[] = array(
			'state' => 'warn',
			/* translators: %d: number of problems */
			'label' => sprintf( _n( '%d link problem — see the Links panel', '%d link problems — see the Links panel', count( $links['problems'] ), 'ssbd' ), count( $links['problems'] ) ),
		);
	}

	$custom_json = trim( (string) get_post_meta( $post->ID, '_ssbd_custom_jsonld', true ) );

	if ( '' !== $custom_json ) {
		$json_valid = ssbd_jsonld_is_printable( $custom_json );

		$checks[] = array(
			'state' => $json_valid ? 'good' : 'bad',
			'label' => $json_valid
				? __( 'Custom JSON-LD is valid and being printed', 'ssbd' )
				: __( 'Custom JSON-LD is not valid JSON — nothing is printed from it', 'ssbd' ),
		);
	}

	$thumb_id = get_post_thumbnail_id( $post->ID );
	$thumb_alt = $thumb_id ? trim( (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) ) : '';

	if ( ! $thumb_id ) {
		$checks[] = array(
			'state' => 'warn',
			'label' => __( 'No featured image — the social card falls back to the logo. Choose one in the panel above.', 'ssbd' ),
		);
	} elseif ( '' === $thumb_alt ) {
		$checks[] = array(
			'state' => 'bad',
			'label' => __( 'Featured image has no alt text — set it in the Featured image panel above', 'ssbd' ),
		);
	} else {
		$checks[] = array(
			'state' => 'good',
			'label' => __( 'Featured image set, with alt text', 'ssbd' ),
		);
	}

	$checks[] = array(
		'state' => $words >= 600 ? 'good' : 'warn',
		/* translators: %d: word count */
		'label' => sprintf( __( 'Article is %d words', 'ssbd' ), $words ),
	);

	/** Filter the workbench checklist. */
	return apply_filters( 'ssbd_seo_audit', $checks, $post );
}

/**
 * The screen's stylesheet. Inline because it is a few hundred bytes on one
 * admin page, and a separate request would cost more than it saves.
 */
function ssbd_seo_workbench_styles() {
	?>
	<style>
		.ssbd-wb { max-width: 1180px; }
		.ssbd-wb-cols { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 24px; align-items: start; }
		@media (max-width: 960px) { .ssbd-wb-cols { grid-template-columns: 1fr; } }
		.ssbd-wb-card { background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 16px 18px; margin-bottom: 16px; }
		.ssbd-wb-card h2 { margin: 0 0 4px; font-size: 14px; }
		.ssbd-wb-card .ssbd-wb-hint { margin: 0 0 12px; color: #646970; font-size: 13px; }
		.ssbd-wb-card textarea, .ssbd-wb-card input[type=text] { width: 100%; }
		.ssbd-wb-card textarea { font-family: inherit; }
		.ssbd-wb-count { float: right; font-size: 12px; color: #646970; font-variant-numeric: tabular-nums; }
		.ssbd-wb-count.is-over { color: #b32d2e; font-weight: 600; }
		.ssbd-wb-count.is-ok { color: #007017; }
		.ssbd-wb-checks { margin: 0; padding: 0; list-style: none; }
		.ssbd-wb-checks li { display: flex; gap: 8px; padding: 7px 0; border-bottom: 1px solid #f0f0f1; font-size: 13px; line-height: 1.45; }
		.ssbd-wb-checks li:last-child { border-bottom: 0; }
		.ssbd-wb-dot { flex: none; width: 10px; height: 10px; margin-top: 4px; border-radius: 50%; }
		.ssbd-wb-dot--good { background: #007017; }
		.ssbd-wb-dot--warn { background: #dba617; }
		.ssbd-wb-dot--bad  { background: #b32d2e; }
		.ssbd-wb-faq-preview { margin: 12px 0 0; padding: 12px 14px; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 4px; font-size: 13px; }
		.ssbd-wb-faq-preview ol { margin: 0; padding-left: 20px; }
		.ssbd-wb-faq-preview li { margin-bottom: 8px; }
		.ssbd-wb-faq-preview li:last-child { margin-bottom: 0; }
		.ssbd-wb-faq-preview strong { display: block; }
		.ssbd-wb-status { display: inline-block; width: 9px; height: 9px; border-radius: 50%; }
		.ssbd-wb-thumb { display: flex; gap: 14px; align-items: flex-start; flex-wrap: wrap; }
		.ssbd-wb-thumb img { width: 180px; height: auto; display: block; border: 1px solid #dcdcde; border-radius: 3px; }
		.ssbd-wb-thumb-empty { width: 180px; height: 110px; display: flex; align-items: center; justify-content: center; background: #f6f7f7; border: 1px dashed #c3c4c7; border-radius: 3px; color: #646970; font-size: 12px; }
		.ssbd-wb-links { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 12px; }
		.ssbd-wb-links td { border-bottom: 1px solid #f0f0f1; padding: 6px 8px 6px 0; vertical-align: top; word-break: break-word; }
		.ssbd-wb-links tr:last-child td { border-bottom: 0; }
		.ssbd-wb-links td:first-child { width: 72px; color: #646970; white-space: nowrap; }
		.ssbd-wb-links td:nth-child(2) { width: 30%; }
		[hidden] { display: none !important; }
		.ssbd-wb-card textarea.ssbd-wb-code { font-family: Menlo, Consolas, monospace; font-size: 12px; line-height: 1.5; }
		.ssbd-wb-invalid { margin: 8px 0 0; padding: 8px 10px; background: #fcf0f1; border-left: 4px solid #b32d2e; font-size: 13px; }
	</style>
	<?php
}

/**
 * Live counters for the two length-sensitive fields.
 */
function ssbd_seo_workbench_script() {
	?>
	<script>
	jQuery( function ( $ ) {
		function bind( selector, readout, min, max, unit ) {
			var $field = $( selector );
			var $out   = $( readout );

			if ( ! $field.length ) {
				return;
			}

			function update() {
				var text = $.trim( $field.val() );
				var n    = 'words' === unit
					? ( text ? text.split( /\s+/ ).length : 0 )
					: text.length;

				$out.text( n + ' / ' + min + '–' + max )
					.toggleClass( 'is-ok', n >= min && n <= max )
					.toggleClass( 'is-over', n > max );
			}

			$field.on( 'input', update );
			update();
		}

		bind( '#ssbd_seo_title', '#ssbd_seo_title_count', 50, 60, 'chars' );
		bind( '#ssbd_description', '#ssbd_description_count', 120, 158, 'chars' );
		bind( '#ssbd_answer', '#ssbd_answer_count', 40, 60, 'words' );

		/*
		 * Featured image picker. The choice is only recorded in the hidden
		 * field here — nothing is written until the form is saved, so backing
		 * out of this screen leaves the post exactly as it was.
		 */
		var frame;

		$( '#ssbd_thumb_choose' ).on( 'click', function ( event ) {
			event.preventDefault();

			if ( ! window.wp || ! wp.media ) {
				return;
			}

			if ( frame ) {
				frame.open();
				return;
			}

			frame = wp.media( {
				title: <?php echo wp_json_encode( __( 'Choose a featured image', 'ssbd' ) ); ?>,
				library: { type: 'image' },
				button: { text: <?php echo wp_json_encode( __( 'Use this image', 'ssbd' ) ); ?> },
				multiple: false
			} );

			frame.on( 'select', function () {
				var image = frame.state().get( 'selection' ).first().toJSON();
				var src   = ( image.sizes && image.sizes.medium ) ? image.sizes.medium.url : image.url;

				$( '#ssbd_thumb_id' ).val( image.id );
				$( '#ssbd_thumb_preview' ).attr( 'src', src ).prop( 'hidden', false );
				$( '#ssbd_thumb_empty' ).prop( 'hidden', true );
				$( '#ssbd_thumb_remove' ).prop( 'hidden', false );

				// The image may already carry alt text from the Media Library.
				// Offer it rather than overwrite: a description written for
				// this post beats the generic one.
				if ( image.alt && ! $.trim( $( '#ssbd_thumb_alt' ).val() ) ) {
					$( '#ssbd_thumb_alt' ).val( image.alt );
				}
			} );

			frame.open();
		} );

		$( '#ssbd_thumb_remove' ).on( 'click', function ( event ) {
			event.preventDefault();

			$( '#ssbd_thumb_id' ).val( '0' );
			$( '#ssbd_thumb_preview' ).prop( 'hidden', true );
			$( '#ssbd_thumb_empty' ).prop( 'hidden', false );
			$( this ).prop( 'hidden', true );
		} );
	} );
	</script>
	<?php
}

/**
 * Render the workbench — the per-post form, or the overview list.
 */
function ssbd_seo_workbench_render() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'You are not allowed to view this page.', 'ssbd' ) );
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only navigation.
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
	$post    = $post_id ? get_post( $post_id ) : null;

	ssbd_seo_workbench_styles();

	if ( ! $post ) {
		ssbd_seo_workbench_overview();
		return;
	}

	if ( ! current_user_can( 'edit_post', $post->ID ) ) {
		wp_die( esc_html__( 'You are not allowed to edit this post.', 'ssbd' ) );
	}

	ssbd_seo_workbench_form( $post );
	ssbd_seo_workbench_script();
}

/**
 * The per-post form.
 *
 * @param WP_Post $post Post being edited.
 */
function ssbd_seo_workbench_form( $post ) {
	$seo_title   = ssbd_seo_get_field( $post->ID, 'title' );
	$keyphrase   = ssbd_seo_get_field( $post->ID, 'keyphrase' );
	$description = ssbd_seo_get_field( $post->ID, 'description' );
	$answer      = ssbd_seo_get_field( $post->ID, 'answer' );
	$faqs        = ssbd_post_faqs( $post->ID );
	$owner       = ssbd_seo_plugin_name();
	$thumb_id    = (int) get_post_thumbnail_id( $post->ID );
	$thumb_alt   = $thumb_id ? (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) : '';
	$thumb_src   = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium' ) : '';
	$links       = ssbd_seo_link_audit( $post );
	$custom_json = (string) get_post_meta( $post->ID, '_ssbd_custom_jsonld', true );
	$json_where  = (string) get_post_meta( $post->ID, '_ssbd_custom_jsonld_where', true );
	$json_where  = isset( ssbd_seo_jsonld_placements()[ $json_where ] ) ? $json_where : 'footer';
	$json_broken = ( '' !== trim( $custom_json ) && ! ssbd_jsonld_is_printable( $custom_json ) );

	/*
	 * Reaching every external URL costs a request each, so it happens only
	 * when asked. The nonce is what separates "the editor pressed the button"
	 * from "something else loaded this screen with a query string".
	 */
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- verified on the next line.
	$check_links = isset( $_GET['ssbd-check-links'], $_GET['_wpnonce'] )
		&& wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'ssbd_check_links_' . $post->ID );

	$link_status = $check_links ? ssbd_seo_check_external_links( $links['external'] ) : array();
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only flag.
	$saved         = isset( $_GET['ssbd-saved'] );
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only flag.
	$faq_unparsed  = isset( $_GET['ssbd-faq-unparsed'] );
	?>
	<div class="wrap ssbd-wb">
		<h1><?php esc_html_e( 'SEO & FAQ', 'ssbd' ); ?></h1>

		<p>
			<strong><?php echo esc_html( get_the_title( $post ) ); ?></strong> —
			<a href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>"><?php esc_html_e( 'Edit content', 'ssbd' ); ?></a> ·
			<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>"><?php esc_html_e( 'View', 'ssbd' ); ?></a> ·
			<a href="<?php echo esc_url( ssbd_seo_workbench_url() ); ?>"><?php esc_html_e( 'All posts', 'ssbd' ); ?></a>
		</p>

		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved.', 'ssbd' ); ?></p></div>
		<?php endif; ?>

		<?php if ( $faq_unparsed ) : ?>
			<div class="notice notice-warning is-dismissible">
				<p>
					<?php
					esc_html_e(
						'The FAQ text was saved as typed, but none of it could be split into questions and answers, so the FAQ field below is empty and no FAQPage data was generated. This usually means the paste had no "?" at the end of a question, no blank line between pairs, and no "Q:"/"A:" markers — the parser needs at least one of those to find where one question ends and the next begins. Try adding "Q:" and "A:" in front of each line, which always works regardless of how the rest is formatted.',
						'ssbd'
					);
					?>
				</p>
			</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( ssbd_seo_workbench_url( $post->ID ) ); ?>">
			<?php wp_nonce_field( 'ssbd_seo_workbench_' . $post->ID, 'ssbd_seo_workbench_nonce' ); ?>
			<input type="hidden" name="ssbd_post_id" value="<?php echo esc_attr( $post->ID ); ?>">

			<div class="ssbd-wb-cols">
				<div>
					<div class="ssbd-wb-card">
						<h2>
							<label for="ssbd_seo_title"><?php esc_html_e( 'SEO title', 'ssbd' ); ?></label>
							<span class="ssbd-wb-count" id="ssbd_seo_title_count"></span>
						</h2>
						<p class="ssbd-wb-hint"><?php esc_html_e( 'The <title> tag — the blue link in a search result and the browser tab. Left empty it falls back to the post title plus the site name, which is exactly what made the homepage and blog page titles too short and this case-study post\'s too long: none of the three had ever been given a title of its own.', 'ssbd' ); ?></p>
						<input type="text" id="ssbd_seo_title" name="ssbd_seo_title" value="<?php echo esc_attr( $seo_title ); ?>" placeholder="<?php echo esc_attr( wp_strip_all_tags( get_the_title( $post ) ) ); ?>">
					</div>

					<div class="ssbd-wb-card">
						<h2><label for="ssbd_slug"><?php esc_html_e( 'URL slug', 'ssbd' ); ?></label></h2>
						<p class="ssbd-wb-hint"><?php esc_html_e( 'The last part of the address. Keep it short and keep the keyphrase in it — this is the one field on this screen that changes the page\'s actual URL.', 'ssbd' ); ?></p>
						<input type="text" id="ssbd_slug" name="ssbd_slug" value="<?php echo esc_attr( $post->post_name ); ?>" placeholder="<?php echo esc_attr( sanitize_title( get_the_title( $post ) ) ); ?>">
						<p class="ssbd-wb-hint" style="margin-top:8px">
							<?php esc_html_e( 'Currently:', 'ssbd' ); ?>
							<code><?php echo esc_html( urldecode( (string) get_permalink( $post->ID ) ) ); ?></code>
						</p>
						<?php if ( 'publish' === $post->post_status ) : ?>
							<p class="ssbd-wb-hint">
								<strong><?php esc_html_e( 'This post is published.', 'ssbd' ); ?></strong>
								<?php esc_html_e( 'Changing the slug changes its URL, and every existing link to the old one — from search results, other sites and your own older posts — stops working unless you add a redirect. Leave it alone unless the current slug is genuinely wrong.', 'ssbd' ); ?>
							</p>
						<?php endif; ?>
						<p class="ssbd-wb-hint"><?php esc_html_e( 'Leave the box empty to keep the slug exactly as it is.', 'ssbd' ); ?></p>
					</div>

					<div class="ssbd-wb-card">
						<h2><label for="ssbd_keyphrase"><?php esc_html_e( 'Focus keyphrase', 'ssbd' ); ?></label></h2>
						<p class="ssbd-wb-hint"><?php esc_html_e( 'The one search this article is meant to win. Everything in the checklist is measured against it.', 'ssbd' ); ?></p>
						<input type="text" id="ssbd_keyphrase" name="ssbd_keyphrase" value="<?php echo esc_attr( $keyphrase ); ?>" placeholder="<?php esc_attr_e( 'best budget wordpress hosting', 'ssbd' ); ?>">
					</div>

					<div class="ssbd-wb-card">
						<h2>
							<label for="ssbd_description"><?php esc_html_e( 'Meta description', 'ssbd' ); ?></label>
							<span class="ssbd-wb-count" id="ssbd_description_count"></span>
						</h2>
						<p class="ssbd-wb-hint"><?php esc_html_e( 'The snippet under the title in search results. Write it as a promise the page keeps, and include the keyphrase.', 'ssbd' ); ?></p>
						<textarea id="ssbd_description" name="ssbd_description" rows="3" maxlength="300"><?php echo esc_textarea( $description ); ?></textarea>
					</div>

					<div class="ssbd-wb-card">
						<h2>
							<label for="ssbd_answer"><?php esc_html_e( 'Answer summary (for AI engines)', 'ssbd' ); ?></label>
							<span class="ssbd-wb-count" id="ssbd_answer_count"></span>
						</h2>
						<p class="ssbd-wb-hint"><?php esc_html_e( 'A self-contained answer to this article\'s core question. Rendered near the top of the post and mirrored into JSON-LD, so an AI engine can quote it without the rest of the page. Name the subject — "Shared hosting costs…", not "It costs…".', 'ssbd' ); ?></p>
						<textarea id="ssbd_answer" name="ssbd_answer" rows="4"><?php echo esc_textarea( $answer ); ?></textarea>
					</div>

					<div class="ssbd-wb-card">
						<h2><?php esc_html_e( 'Featured image', 'ssbd' ); ?></h2>
						<p class="ssbd-wb-hint"><?php esc_html_e( 'The picture used on the post, in the social card, and in the article\'s structured data.', 'ssbd' ); ?></p>

						<div class="ssbd-wb-thumb">
							<?php // No src attribute at all when there is no image: src="" resolves to this page and browsers re-request it. ?>
							<img id="ssbd_thumb_preview" alt=""
								<?php echo $thumb_src ? 'src="' . esc_url( $thumb_src ) . '"' : 'hidden'; ?>>
							<div id="ssbd_thumb_empty" class="ssbd-wb-thumb-empty" <?php echo $thumb_src ? 'hidden' : ''; ?>><?php esc_html_e( 'No image', 'ssbd' ); ?></div>

							<div>
								<input type="hidden" id="ssbd_thumb_id" name="ssbd_thumb_id" value="<?php echo esc_attr( $thumb_id ); ?>">
								<p style="margin-top:0">
									<button type="button" class="button" id="ssbd_thumb_choose"><?php esc_html_e( 'Choose image', 'ssbd' ); ?></button>
									<button type="button" class="button" id="ssbd_thumb_remove" <?php echo $thumb_id ? '' : 'hidden'; ?>><?php esc_html_e( 'Remove', 'ssbd' ); ?></button>
								</p>
								<p class="ssbd-wb-hint"><?php esc_html_e( 'The choice is applied when you save this screen.', 'ssbd' ); ?></p>
							</div>
						</div>

						<h2 style="margin-top:14px"><label for="ssbd_thumb_alt"><?php esc_html_e( 'Alt text', 'ssbd' ); ?></label></h2>
						<p class="ssbd-wb-hint"><?php esc_html_e( 'What the image shows, in a sentence, for a reader who cannot see it — and for Google Images, which has nothing else to go on. Describe the picture, do not just repeat the keyphrase. This is stored on the image itself, so it fixes every post that uses it.', 'ssbd' ); ?></p>
						<textarea id="ssbd_thumb_alt" name="ssbd_thumb_alt" rows="2" maxlength="300"><?php echo esc_textarea( $thumb_alt ); ?></textarea>
					</div>

					<?php if ( ssbd_seo_supports_faq( $post->post_type ) ) : ?>
						<div class="ssbd-wb-card">
							<h2><label for="ssbd_faq_text"><?php esc_html_e( 'FAQ', 'ssbd' ); ?></label></h2>
							<p class="ssbd-wb-hint">
								<?php esc_html_e( 'Paste all the questions and answers in one go — no need for one box per question. Any of these work:', 'ssbd' ); ?>
							</p>
							<p class="ssbd-wb-hint">
								<code>Q: … / A: …</code> ·
								<?php esc_html_e( 'question on its own line, answer under it, blank line between pairs', 'ssbd' ); ?> ·
								<?php esc_html_e( 'or just alternating lines where each question ends in a question mark', 'ssbd' ); ?>
							</p>
							<textarea id="ssbd_faq_text" name="ssbd_faq_text" rows="14" placeholder="<?php esc_attr_e( "Q: How long does a website take?\nA: Four to six weeks for a standard business site.\n\nQ: What does it cost?\nA: From BDT 25,000, depending on scope.", 'ssbd' ); ?>"><?php echo esc_textarea( ssbd_faq_to_text( $faqs ) ); ?></textarea>

							<p class="ssbd-wb-hint" style="margin-top:10px">
								<?php esc_html_e( 'On save this becomes the collapsible FAQ at the end of the article and its FAQPage structured data. Numbering and markdown bold are stripped; links, bold and italic survive inside an answer. Maximum 20 questions.', 'ssbd' ); ?>
							</p>

							<?php if ( $faqs ) : ?>
								<div class="ssbd-wb-faq-preview">
									<p style="margin:0 0 8px"><strong><?php echo esc_html( sprintf( /* translators: %d: number of questions */ _n( '%d question saved', '%d questions saved', count( $faqs ), 'ssbd' ), count( $faqs ) ) ); ?></strong></p>
									<ol>
										<?php foreach ( $faqs as $ssbd_faq ) : ?>
											<li>
												<strong><?php echo esc_html( $ssbd_faq['q'] ); ?></strong>
												<?php echo wp_kses( $ssbd_faq['a'], ssbd_faq_allowed_html() ); ?>
											</li>
										<?php endforeach; ?>
									</ol>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<div class="ssbd-wb-card">
						<h2><?php esc_html_e( 'Links', 'ssbd' ); ?></h2>
						<p class="ssbd-wb-hint">
							<?php
							printf(
								/* translators: 1: number of internal links, 2: number of external links */
								esc_html__( 'Read from the saved content: %1$d internal, %2$d external. Internal links pass authority around your own site; external ones cite the sources that make a claim checkable.', 'ssbd' ),
								count( $links['internal'] ),
								count( $links['external'] )
							);
							?>
						</p>

						<?php if ( $links['problems'] ) : ?>
							<ul class="ssbd-wb-checks">
								<?php foreach ( $links['problems'] as $ssbd_problem ) : ?>
									<li>
										<span class="ssbd-wb-dot ssbd-wb-dot--warn"></span>
										<span><?php echo esc_html( $ssbd_problem ); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							<p class="ssbd-wb-hint"><?php esc_html_e( 'No anchor text, rel or destination problems found.', 'ssbd' ); ?></p>
						<?php endif; ?>

						<?php if ( $links['internal'] || $links['external'] ) : ?>
							<table class="ssbd-wb-links">
								<tbody>
									<?php
									$ssbd_rows = array();

									foreach ( array( 'internal', 'external' ) as $ssbd_kind ) {
										foreach ( $links[ $ssbd_kind ] as $ssbd_row ) {
											$ssbd_row['kind'] = $ssbd_kind;
											$ssbd_rows[]      = $ssbd_row;
										}
									}
									?>
									<?php foreach ( $ssbd_rows as $ssbd_link ) : ?>
										<tr>
											<td><?php echo esc_html( 'internal' === $ssbd_link['kind'] ? __( 'internal', 'ssbd' ) : __( 'external', 'ssbd' ) ); ?></td>
											<td><?php echo esc_html( $ssbd_link['anchor'] ? $ssbd_link['anchor'] : __( '(no anchor text)', 'ssbd' ) ); ?></td>
											<td><a href="<?php echo esc_url( $ssbd_link['href'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $ssbd_link['href'] ); ?></a></td>
											<td><?php echo $ssbd_link['nofollow'] ? 'nofollow' : ''; ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						<?php endif; ?>

						<?php if ( $links['external'] ) : ?>
							<p style="margin-bottom:0">
								<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'ssbd-check-links', '1', ssbd_seo_workbench_url( $post->ID ) ), 'ssbd_check_links_' . $post->ID ) ); ?>">
									<?php esc_html_e( 'Check external links now', 'ssbd' ); ?>
								</a>
								<span class="ssbd-wb-hint"><?php esc_html_e( 'Contacts each one and reports what it answers. Results are cached for 12 hours. Unsaved edits above are not included — save first.', 'ssbd' ); ?></span>
							</p>
						<?php endif; ?>

						<?php if ( $link_status ) : ?>
							<table class="ssbd-wb-links">
								<tbody>
									<?php foreach ( $link_status as $ssbd_result ) : ?>
										<tr>
											<td>
												<span class="ssbd-wb-status ssbd-wb-dot--<?php echo $ssbd_result['ok'] ? 'good' : 'bad'; ?>" aria-hidden="true"></span>
												<?php echo esc_html( $ssbd_result['code'] ? (string) $ssbd_result['code'] : __( 'no reply', 'ssbd' ) ); ?>
											</td>
											<td><?php echo esc_html( $ssbd_result['anchor'] ); ?></td>
											<td><a href="<?php echo esc_url( $ssbd_result['href'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $ssbd_result['href'] ); ?></a></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
							<p class="ssbd-wb-hint"><?php esc_html_e( 'Some sites refuse automated requests and answer 403 to this check while working perfectly in a browser — open anything flagged before deleting it.', 'ssbd' ); ?></p>
						<?php endif; ?>
					</div>

					<div class="ssbd-wb-card">
						<h2><label for="ssbd_custom_json"><?php esc_html_e( 'Custom JSON-LD', 'ssbd' ); ?></label></h2>
						<p class="ssbd-wb-hint">
							<?php esc_html_e( 'Extra structured data for this post, printed as its own script tag. The theme already emits the organisation, breadcrumbs, article, image and FAQ — this is for the types it has no opinion about: Product, Review, Event, HowTo, VideoObject, SoftwareApplication.', 'ssbd' ); ?>
						</p>
						<p class="ssbd-wb-hint">
							<?php esc_html_e( 'Paste one complete JSON object, starting with { and including its own "@context" and "@type". Invalid JSON is kept so you do not lose the draft, but it is never printed.', 'ssbd' ); ?>
						</p>

						<textarea id="ssbd_custom_json" name="ssbd_custom_json" class="ssbd-wb-code" rows="10" placeholder="<?php esc_attr_e( '{&#10;  &quot;@context&quot;: &quot;https://schema.org&quot;,&#10;  &quot;@type&quot;: &quot;Product&quot;,&#10;  &quot;name&quot;: &quot;Business website package&quot;&#10;}', 'ssbd' ); ?>"><?php echo esc_textarea( $custom_json ); ?></textarea>

						<?php if ( $json_broken ) : ?>
							<p class="ssbd-wb-invalid">
								<?php esc_html_e( 'This is not valid JSON, so nothing is being printed on the front end. Check the brackets, the commas between entries, and that every key and string is in double quotes.', 'ssbd' ); ?>
							</p>
						<?php elseif ( trim( $custom_json ) ) : ?>
							<p class="ssbd-wb-hint" style="color:#007017">
								<?php esc_html_e( 'Valid JSON — this is being printed.', 'ssbd' ); ?>
							</p>
						<?php endif; ?>

						<h2 style="margin-top:14px"><label for="ssbd_custom_json_where"><?php esc_html_e( 'Where to print it', 'ssbd' ); ?></label></h2>
						<p class="ssbd-wb-hint"><?php esc_html_e( 'Google reads both, so the footer is the better default — it keeps the markup out of the critical path. Choose the head only when something else insists on finding it there.', 'ssbd' ); ?></p>
						<select id="ssbd_custom_json_where" name="ssbd_custom_json_where">
							<?php foreach ( ssbd_seo_jsonld_placements() as $ssbd_value => $ssbd_label ) : ?>
								<option value="<?php echo esc_attr( $ssbd_value ); ?>" <?php selected( $json_where, $ssbd_value ); ?>><?php echo esc_html( $ssbd_label ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<?php submit_button( __( 'Save SEO & FAQ', 'ssbd' ) ); ?>
				</div>

				<div>
					<div class="ssbd-wb-card">
						<h2><?php esc_html_e( 'Checklist', 'ssbd' ); ?></h2>
						<p class="ssbd-wb-hint"><?php esc_html_e( 'Measured against what is saved right now.', 'ssbd' ); ?></p>
						<ul class="ssbd-wb-checks">
							<?php foreach ( ssbd_seo_audit( $post ) as $ssbd_check ) : ?>
								<li>
									<span class="ssbd-wb-dot ssbd-wb-dot--<?php echo esc_attr( $ssbd_check['state'] ); ?>"></span>
									<span><?php echo esc_html( $ssbd_check['label'] ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>

					<?php if ( $owner ) : ?>
						<div class="ssbd-wb-card">
							<h2><?php esc_html_e( 'Where this is stored', 'ssbd' ); ?></h2>
							<p class="ssbd-wb-hint">
								<?php
								printf(
									/* translators: %s: SEO plugin name */
									esc_html__( '%s is active and owns the tags in the page head, so the keyphrase and description are written to its fields as well as the theme\'s. Editing them here or in its own box comes to the same thing.', 'ssbd' ),
									esc_html( $owner )
								);
								?>
							</p>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</form>
	</div>
	<?php
}

/**
 * The overview: every post and page, and what each one is still missing.
 *
 * The point of the column layout is that a gap is visible without opening
 * anything — which is the difference between a checklist someone uses and one
 * they remember to look at.
 */
function ssbd_seo_workbench_overview() {
	$posts = get_posts( array(
		'post_type'        => array( 'post', 'page' ),
		'post_status'      => array( 'publish', 'draft', 'pending', 'future', 'private' ),
		'numberposts'      => 200,
		'orderby'          => 'type title',
		'order'            => 'ASC',
		'suppress_filters' => false,
	) );
	?>
	<div class="wrap ssbd-wb">
		<h1><?php esc_html_e( 'SEO & FAQ', 'ssbd' ); ?></h1>
		<p class="ssbd-wb-hint">
			<?php esc_html_e( 'Every post and page, with what each one still needs. The same screen opens from the “SEO & FAQ” link under any row in Posts or Pages.', 'ssbd' ); ?>
		</p>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Title', 'ssbd' ); ?></th>
					<th scope="col" style="width:80px"><?php esc_html_e( 'Type', 'ssbd' ); ?></th>
					<th scope="col" style="width:90px"><?php esc_html_e( 'Title', 'ssbd' ); ?></th>
					<th scope="col" style="width:90px"><?php esc_html_e( 'Keyphrase', 'ssbd' ); ?></th>
					<th scope="col" style="width:110px"><?php esc_html_e( 'Description', 'ssbd' ); ?></th>
					<th scope="col" style="width:110px"><?php esc_html_e( 'Answer', 'ssbd' ); ?></th>
					<th scope="col" style="width:70px"><?php esc_html_e( 'FAQ', 'ssbd' ); ?></th>
					<th scope="col" style="width:130px"><?php esc_html_e( 'Image', 'ssbd' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! $posts ) : ?>
					<tr><td colspan="8"><?php esc_html_e( 'Nothing published yet.', 'ssbd' ); ?></td></tr>
				<?php endif; ?>

				<?php foreach ( $posts as $ssbd_item ) : ?>
					<?php
					$ssbd_desc  = ssbd_seo_get_field( $ssbd_item->ID, 'description' );
					$ssbd_ans   = ssbd_seo_get_field( $ssbd_item->ID, 'answer' );
					$ssbd_title = ssbd_seo_get_field( $ssbd_item->ID, 'title' );
					$ssbd_title_len = mb_strlen( $ssbd_title ? $ssbd_title : wp_strip_all_tags( get_the_title( $ssbd_item ) ) . ' - ' . ssbd_site( 'legal_name', get_bloginfo( 'name' ) ) );
					$ssbd_key   = ssbd_seo_get_field( $ssbd_item->ID, 'keyphrase' );
					$ssbd_count = ssbd_seo_supports_faq( $ssbd_item->post_type ) ? count( ssbd_post_faqs( $ssbd_item->ID ) ) : null;
					$ssbd_len   = mb_strlen( $ssbd_desc );
					$ssbd_thumb = get_post_thumbnail_id( $ssbd_item->ID );
					$ssbd_alt   = $ssbd_thumb ? trim( (string) get_post_meta( $ssbd_thumb, '_wp_attachment_image_alt', true ) ) : '';
					?>
					<tr>
						<td>
							<strong><a href="<?php echo esc_url( ssbd_seo_workbench_url( $ssbd_item->ID ) ); ?>"><?php echo esc_html( get_the_title( $ssbd_item ) ); ?></a></strong>
							<?php if ( 'publish' !== $ssbd_item->post_status ) : ?>
								<em>— <?php echo esc_html( $ssbd_item->post_status ); ?></em>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $ssbd_item->post_type ); ?></td>
						<td><?php ssbd_seo_cell( ( $ssbd_title_len >= 50 && $ssbd_title_len <= 60 ) ? 'good' : 'warn', sprintf( /* translators: %d: character count */ __( '%d chars', 'ssbd' ), $ssbd_title_len ) ); ?></td>
						<td><?php ssbd_seo_cell( $ssbd_key ? 'good' : 'bad', $ssbd_key ? __( 'Set', 'ssbd' ) : __( 'Missing', 'ssbd' ) ); ?></td>
						<td>
							<?php
							if ( ! $ssbd_len ) {
								ssbd_seo_cell( 'bad', __( 'Missing', 'ssbd' ) );
							} elseif ( $ssbd_len < 120 || $ssbd_len > 158 ) {
								/* translators: %d: character count */
								ssbd_seo_cell( 'warn', sprintf( __( '%d chars', 'ssbd' ), $ssbd_len ) );
							} else {
								/* translators: %d: character count */
								ssbd_seo_cell( 'good', sprintf( __( '%d chars', 'ssbd' ), $ssbd_len ) );
							}
							?>
						</td>
						<td><?php ssbd_seo_cell( $ssbd_ans ? 'good' : 'warn', $ssbd_ans ? __( 'Set', 'ssbd' ) : __( 'Missing', 'ssbd' ) ); ?></td>
						<td>
							<?php
							if ( null === $ssbd_count ) {
								echo '<span aria-hidden="true">—</span>';
							} else {
								ssbd_seo_cell( $ssbd_count >= 3 ? 'good' : ( $ssbd_count ? 'warn' : 'bad' ), (string) $ssbd_count );
							}
							?>
						</td>
						<td>
							<?php
							// An image with no alt text is the one worth flagging: it is
							// the half of the job that gets forgotten, and it is the half
							// Google Images and a screen reader both depend on.
							if ( ! $ssbd_thumb ) {
								ssbd_seo_cell( 'warn', __( 'None', 'ssbd' ) );
							} elseif ( '' === $ssbd_alt ) {
								ssbd_seo_cell( 'bad', __( 'No alt text', 'ssbd' ) );
							} else {
								ssbd_seo_cell( 'good', __( 'Set', 'ssbd' ) );
							}
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * One status cell: a coloured dot and its label.
 *
 * The label carries the meaning; the dot is only there to make the column
 * scannable, which is why it is aria-hidden rather than given a role.
 *
 * @param string $state good | warn | bad.
 * @param string $label Visible text.
 */
function ssbd_seo_cell( $state, $label ) {
	printf(
		'<span class="ssbd-wb-status ssbd-wb-dot--%s" aria-hidden="true"></span> %s',
		esc_attr( $state ),
		esc_html( $label )
	);
}
