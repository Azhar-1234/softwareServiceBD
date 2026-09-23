<?php
/**
 * Edit screens for the catalogue post types.
 *
 * One meta box per type, driven by a field schema so the render, the save and
 * the read path can never fall out of step.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field definitions per post type.
 *
 * type: text | textarea | select | icon | list | gallery | image | faqtext
 * list fields store a newline-separated string and read back as an array.
 *
 * @return array<string, array<string, array>>
 */
function ssbd_meta_fields() {
	return array(
		'ssbd_project' => array(
			'desc'  => array(
				'label' => __( 'Short description', 'ssbd' ),
				'type'  => 'textarea',
				'help'  => __( 'One or two sentences. Shown on the project card.', 'ssbd' ),
			),
			'tech'  => array(
				'label'       => __( 'Tech stack', 'ssbd' ),
				'type'        => 'text',
				'placeholder' => 'WordPress · WooCommerce',
				'help'        => __( 'Shown in small type under the description.', 'ssbd' ),
			),
			'badge' => array(
				'label'       => __( 'Badge (top left)', 'ssbd' ),
				'type'        => 'text',
				'placeholder' => 'WooCommerce',
			),
			'tag'   => array(
				'label'       => __( 'Tag (top right)', 'ssbd' ),
				'type'        => 'text',
				'placeholder' => 'Live Store',
			),
			'url'   => array(
				'label'       => __( 'Live URL', 'ssbd' ),
				'type'        => 'text',
				'placeholder' => 'https://example.com',
				'help'        => __( 'Optional. Also populates the CreativeWork structured data for this project.', 'ssbd' ),
			),
		),

		'ssbd_service' => array(
			'short' => array(
				'label' => __( 'Short description', 'ssbd' ),
				'type'  => 'textarea',
				'help'  => __( 'Used on the front page card. Keep it to one sentence.', 'ssbd' ),
			),
			'desc'  => array(
				'label' => __( 'Full description', 'ssbd' ),
				'type'  => 'textarea',
				'help'  => __( 'Used on the Services page and in the Service structured data.', 'ssbd' ),
			),
			'group' => array(
				'label'   => __( 'Filter group', 'ssbd' ),
				'type'    => 'select',
				'options' => array(
					'build'    => __( 'Build', 'ssbd' ),
					'automate' => __( 'Automate', 'ssbd' ),
					'grow'     => __( 'Grow', 'ssbd' ),
					'support'  => __( 'Support', 'ssbd' ),
				),
				'help'    => __( 'Which tab this service appears under on the front page. A service nested under another one inherits its place in the header dropdown from the Parent field in Page Attributes.', 'ssbd' ),
			),
			'icon'  => array(
				'label' => __( 'Icon', 'ssbd' ),
				'type'  => 'icon',
			),
			'screens' => array(
				'label' => __( 'App / interface screenshots', 'ssbd' ),
				'type'  => 'gallery',
				'help'  => __( 'Real screens of work you have done. They fill the showcase and the phone mockups on the service page. Tall screenshots (roughly 9:19) look best; the page falls back to drawn placeholder screens when this is empty.', 'ssbd' ),
			),
			'pills' => array(
				'label' => __( 'Technology pills', 'ssbd' ),
				'type'  => 'list',
				'help'  => __( 'One per line. Shown as small rounded labels on the front page card.', 'ssbd' ),
			),
			'items' => array(
				'label' => __( 'What it includes', 'ssbd' ),
				'type'  => 'list',
				'help'  => __( 'One per line. Listed on the Services page.', 'ssbd' ),
			),
			'cta'   => array(
				'label'       => __( 'Link label', 'ssbd' ),
				'type'        => 'text',
				'placeholder' => 'Explore this service',
			),
		),

		'ssbd_product' => array(
			'desc'     => array(
				'label' => __( 'Short description', 'ssbd' ),
				'type'  => 'textarea',
			),
			'category' => array(
				'label'       => __( 'Category label', 'ssbd' ),
				'type'        => 'text',
				'placeholder' => 'WordPress Plugin',
				'help'        => __( 'The badge shown on the card. The checkbox categories on the right drive the filter tabs.', 'ssbd' ),
			),
			'status'   => array(
				'label'       => __( 'Status', 'ssbd' ),
				'type'        => 'text',
				'placeholder' => 'In development',
			),
			'price'    => array(
				'label'       => __( 'Price', 'ssbd' ),
				'type'        => 'text',
				'placeholder' => '৳5,000',
				'help'        => __( 'Free text, so it can hold whatever the product is actually sold in — ৳5,000, $49, from ৳2,500, ৳500/mo. Leave it empty and the card says "Request a quote" instead, which is the right answer for a product still in development.', 'ssbd' ),
			),
		),

		'post' => array(
			'author_name'  => array(
				'label'       => __( 'Byline name', 'ssbd' ),
				'type'        => 'text',
				'placeholder' => 'Software Service BD Editorial Team',
				'help'        => __( 'Optional. Overrides the byline shown above the article and in the "Written by" box at the end — the WordPress user account that owns this post is never shown by name unless you set it as its display name. Leave empty to keep the editorial team byline.', 'ssbd' ),
			),
			'author_photo' => array(
				'label' => __( 'Byline photo', 'ssbd' ),
				'type'  => 'image',
				'help'  => __( 'Optional. Shown as a small circle next to the byline name and in the "Written by" box. Leave empty to show the site mark instead.', 'ssbd' ),
			),
			'video_url'    => array(
				'label'       => __( 'Video (instead of the featured image)', 'ssbd' ),
				'type'        => 'text',
				'placeholder' => 'https://www.youtube.com/watch?v=…',
				'help'        => __( 'Optional. A YouTube or Vimeo link. When set, this embeds at the top of the article in place of the featured image — the featured image is still used for social sharing and search results either way, so set one even when a video is also set.', 'ssbd' ),
			),
			'faqs'         => array(
				'label' => __( 'FAQ (for this article)', 'ssbd' ),
				'type'  => 'faqtext',
				'help'  => __( 'Optional. Paste every question and answer together — "Q: … / A: …", or a question per line with its answer under it. Adds a collapsible FAQ at the end of the article and FAQPage structured data, both from the same list. Phrase each question the way someone would actually type it into Google or an AI assistant, and keep each answer to 2-3 sentences that stand alone so it can be quoted without the rest of the article: avoid "as mentioned above". A link inside an answer is fine.', 'ssbd' ),
			),
		),

		'ssbd_order' => array(
			'order_status' => array(
				'label'   => __( 'Order status', 'ssbd' ),
				'type'    => 'select',
				'options' => array(
					'new'       => __( 'New', 'ssbd' ),
					'contacted' => __( 'Contacted', 'ssbd' ),
					'paid'      => __( 'Paid', 'ssbd' ),
					'delivered' => __( 'Delivered', 'ssbd' ),
					'cancelled' => __( 'Cancelled', 'ssbd' ),
				),
				'help'    => __( 'Your own tracking. Nothing is emailed to the customer when this changes.', 'ssbd' ),
			),
			'note'         => array(
				'label' => __( 'Internal note', 'ssbd' ),
				'type'  => 'textarea',
				'help'  => __( 'Not shown to the customer.', 'ssbd' ),
			),
		),
	);
}

/**
 * Tags allowed inside a per-post FAQ answer.
 *
 * Deliberately small: the help text on the field asks for 2-3 self-contained
 * sentences, and Google's own FAQPage guidance only recognises a handful of
 * inline tags anyway. Used both when saving the field and when faq.php
 * renders it, so neither side can drift from the other.
 *
 * @return array
 */
function ssbd_faq_allowed_html() {
	return array(
		'a'      => array(
			'href'  => true,
			'title' => true,
		),
		'strong' => array(),
		'em'     => array(),
		'br'     => array(),
	);
}

/**
 * Register the meta boxes.
 */
function ssbd_add_catalogue_meta_boxes() {
	foreach ( array_keys( ssbd_meta_fields() ) as $type ) {
		$object = get_post_type_object( $type );

		add_meta_box(
			'ssbd-fields',
			$object ? $object->labels->singular_name . ' ' . __( 'details', 'ssbd' ) : __( 'Details', 'ssbd' ),
			'ssbd_render_catalogue_meta_box',
			$type,
			'normal',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'ssbd_add_catalogue_meta_boxes' );

/**
 * Load the media library on the screens that have a gallery field.
 *
 * @param string $hook Current admin page.
 */
function ssbd_enqueue_catalogue_media( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();

	if ( $screen && isset( ssbd_meta_fields()[ $screen->post_type ] ) ) {
		wp_enqueue_media();
	}
}
add_action( 'admin_enqueue_scripts', 'ssbd_enqueue_catalogue_media' );

/**
 * Render the meta box.
 *
 * @param WP_Post $post Post being edited.
 */
function ssbd_render_catalogue_meta_box( $post ) {
	$fields = ssbd_meta_fields()[ $post->post_type ] ?? array();

	if ( ! $fields ) {
		return;
	}

	wp_nonce_field( 'ssbd_catalogue_save', 'ssbd_catalogue_nonce' );
	?>
	<style>
		.ssbd-field { margin: 0 0 18px; }
		.ssbd-field > label { display: block; font-weight: 600; margin-bottom: 4px; }
		.ssbd-field input[type=text], .ssbd-field textarea, .ssbd-field select { width: 100%; max-width: 620px; }
		.ssbd-field textarea { min-height: 70px; }
		.ssbd-field .description { display: block; margin-top: 4px; }
		.ssbd-gallery-items { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 8px; }
		.ssbd-gallery-item { position: relative; margin: 0; line-height: 0; }
		.ssbd-gallery-item img { width: 84px; height: 84px; object-fit: cover; border-radius: 4px; border: 1px solid #dcdcde; }
		.ssbd-gallery-remove {
			position: absolute; top: -7px; right: -7px; width: 20px; height: 20px;
			border: 0; border-radius: 50%; background: #b32d2e; color: #fff;
			font-size: 13px; line-height: 1; cursor: pointer;
		}
		.ssbd-faq-text { width: 100%; min-height: 220px; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12.5px; line-height: 1.6; }
		.ssbd-faq-parsed { margin-top: 8px; padding: 10px 12px; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 4px; font-size: 12.5px; }
		.ssbd-image-preview { margin-bottom: 8px; }
		.ssbd-image-preview img { width: 84px; height: 84px; object-fit: cover; border-radius: 50%; border: 1px solid #dcdcde; display: block; }
		.ssbd-image-remove { margin-left: 8px; }
	</style>

	<script>
	jQuery( function ( $ ) {
		$( '.ssbd-gallery' ).each( function () {
			var $wrap  = $( this );
			var $input = $wrap.find( 'input[type=hidden]' );
			var $items = $wrap.find( '.ssbd-gallery-items' );
			var frame;

			function sync() {
				var ids = $items.find( '.ssbd-gallery-item' ).map( function () {
					return $( this ).data( 'id' );
				} ).get();
				$input.val( ids.join( ',' ) );
			}

			$wrap.on( 'click', '.ssbd-gallery-remove', function () {
				$( this ).closest( '.ssbd-gallery-item' ).remove();
				sync();
			} );

			$wrap.on( 'click', '.ssbd-gallery-add', function ( e ) {
				e.preventDefault();

				if ( ! frame ) {
					frame = wp.media( {
						title: <?php echo wp_json_encode( __( 'Select app screenshots', 'ssbd' ) ); ?>,
						library: { type: 'image' },
						multiple: 'add'
					} );

					frame.on( 'select', function () {
						frame.state().get( 'selection' ).each( function ( att ) {
							var a = att.toJSON();
							if ( $items.find( '[data-id="' + a.id + '"]' ).length ) {
								return;
							}
							var src = ( a.sizes && a.sizes.thumbnail ) ? a.sizes.thumbnail.url : a.url;
							$items.append(
								'<figure class="ssbd-gallery-item" data-id="' + a.id + '">' +
								'<img src="' + src + '" alt="">' +
								'<button type="button" class="ssbd-gallery-remove">&times;</button>' +
								'</figure>'
							);
						} );
						sync();
					} );
				}

				frame.open();
			} );
		} );

		$( '.ssbd-image-field' ).each( function () {
			var $wrap  = $( this );
			var $input = $wrap.find( 'input[type=hidden]' );
			var $preview = $wrap.find( '.ssbd-image-preview' );
			var frame;

			function render( att ) {
				if ( ! att ) {
					$preview.empty();
					return;
				}
				var src = ( att.sizes && att.sizes.thumbnail ) ? att.sizes.thumbnail.url : att.url;
				$preview.html( '<img src="' + src + '" alt="">' );
			}

			$wrap.on( 'click', '.ssbd-image-remove', function ( e ) {
				e.preventDefault();
				$input.val( '' );
				render( null );
			} );

			$wrap.on( 'click', '.ssbd-image-select', function ( e ) {
				e.preventDefault();

				if ( ! frame ) {
					frame = wp.media( {
						title: <?php echo wp_json_encode( __( 'Select an image', 'ssbd' ) ); ?>,
						library: { type: 'image' },
						multiple: false
					} );

					frame.on( 'select', function () {
						var att = frame.state().get( 'selection' ).first().toJSON();
						$input.val( att.id );
						render( att );
					} );
				}

				frame.open();
			} );
		} );

	} );
	</script>
	<?php
	foreach ( $fields as $key => $field ) {
		$id    = 'ssbd_' . $key;
		$value = get_post_meta( $post->ID, '_ssbd_' . $key, true );
		?>
		<div class="ssbd-field">
			<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['label'] ); ?></label>

			<?php if ( 'textarea' === $field['type'] ) : ?>
				<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" rows="3"><?php echo esc_textarea( $value ); ?></textarea>

			<?php elseif ( 'list' === $field['type'] ) : ?>
				<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" rows="5"><?php echo esc_textarea( $value ); ?></textarea>

			<?php elseif ( 'select' === $field['type'] ) : ?>
				<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>">
					<?php foreach ( $field['options'] as $option => $label ) : ?>
						<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $value, $option ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>

			<?php elseif ( 'gallery' === $field['type'] ) : ?>
				<?php
				$ssbd_ids = array_filter( array_map( 'absint', explode( ',', (string) $value ) ) );
				?>
				<div class="ssbd-gallery" data-target="<?php echo esc_attr( $id ); ?>">
					<div class="ssbd-gallery-items">
						<?php foreach ( $ssbd_ids as $ssbd_att ) : ?>
							<figure class="ssbd-gallery-item" data-id="<?php echo esc_attr( $ssbd_att ); ?>">
								<?php echo wp_get_attachment_image( $ssbd_att, 'thumbnail' ); ?>
								<button type="button" class="ssbd-gallery-remove" aria-label="<?php esc_attr_e( 'Remove image', 'ssbd' ); ?>">&times;</button>
							</figure>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button ssbd-gallery-add"><?php esc_html_e( 'Add images', 'ssbd' ); ?></button>
					<input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $value ); ?>">
				</div>

			<?php elseif ( 'image' === $field['type'] ) : ?>
				<div class="ssbd-image-field" data-target="<?php echo esc_attr( $id ); ?>">
					<div class="ssbd-image-preview">
						<?php if ( $value ) : ?>
							<?php echo wp_get_attachment_image( (int) $value, 'thumbnail' ); ?>
						<?php endif; ?>
					</div>
					<button type="button" class="button ssbd-image-select"><?php esc_html_e( 'Select image', 'ssbd' ); ?></button>
					<button type="button" class="button ssbd-image-remove"><?php esc_html_e( 'Remove', 'ssbd' ); ?></button>
					<input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $value ); ?>">
				</div>

			<?php elseif ( 'faqtext' === $field['type'] ) : ?>
				<?php
				$ssbd_faq_items = json_decode( (string) $value, true );
				$ssbd_faq_items = is_array( $ssbd_faq_items ) ? $ssbd_faq_items : array();
				?>
				<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" class="ssbd-faq-text" placeholder="<?php esc_attr_e( "Q: How long does a website take?\nA: Four to six weeks for a standard business site.\n\nQ: What does it cost?\nA: From BDT 25,000, depending on scope.", 'ssbd' ); ?>"><?php echo esc_textarea( ssbd_faq_to_text( $ssbd_faq_items ) ); ?></textarea>

				<?php if ( $ssbd_faq_items ) : ?>
					<p class="ssbd-faq-parsed">
						<?php
						printf(
							/* translators: %d: number of questions */
							esc_html( _n( '%d question saved and emitting FAQPage structured data.', '%d questions saved and emitting FAQPage structured data.', count( $ssbd_faq_items ), 'ssbd' ) ),
							count( $ssbd_faq_items )
						);
						?>
						<a href="<?php echo esc_url( ssbd_seo_workbench_url( $post->ID ) ); ?>"><?php esc_html_e( 'Open the SEO & FAQ screen', 'ssbd' ); ?></a>
					</p>
				<?php endif; ?>

			<?php elseif ( 'icon' === $field['type'] ) : ?>
				<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>">
					<?php foreach ( array_keys( ssbd_data( 'icons' ) ) as $icon ) : ?>
						<option value="<?php echo esc_attr( $icon ); ?>" <?php selected( $value, $icon ); ?>><?php echo esc_html( $icon ); ?></option>
					<?php endforeach; ?>
				</select>
				<span class="description"><?php esc_html_e( 'Icons are defined in inc/data/icons.php.', 'ssbd' ); ?></span>

			<?php else : ?>
				<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>"
					value="<?php echo esc_attr( $value ); ?>"
					placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>">
			<?php endif; ?>

			<?php if ( ! empty( $field['help'] ) ) : ?>
				<span class="description"><?php echo esc_html( $field['help'] ); ?></span>
			<?php endif; ?>
		</div>
		<?php
	}
	?>
	<p class="description">
		<?php esc_html_e( 'Use the Order field in the Page Attributes box to control where this appears in the list.', 'ssbd' ); ?>
	</p>
	<?php
}

/**
 * Save the meta box.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 */
function ssbd_save_catalogue_meta( $post_id, $post ) {
	if ( ! isset( $_POST['ssbd_catalogue_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['ssbd_catalogue_nonce'] ) ), 'ssbd_catalogue_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = ssbd_meta_fields()[ $post->post_type ] ?? array();

	foreach ( $fields as $key => $field ) {
		$input = 'ssbd_' . $key;

		if ( ! isset( $_POST[ $input ] ) ) {
			continue;
		}

		$raw = wp_unslash( $_POST[ $input ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized per type below.

		if ( 'gallery' === $field['type'] ) {
			// Attachment IDs only: anything else in the field is discarded.
			$ids   = array_filter( array_map( 'absint', explode( ',', (string) $raw ) ) );
			$value = implode( ',', array_unique( $ids ) );
		} elseif ( 'image' === $field['type'] ) {
			$value = (string) absint( $raw );
		} elseif ( 'faqtext' === $field['type'] ) {
			$clean = array();

			// Capped at 20: this is a per-article FAQ, not a knowledge base —
			// a longer list stops being 2-3 sentences someone can scan.
			foreach ( array_slice( ssbd_faq_parse_text( (string) $raw ), 0, 20 ) as $item ) {
				$q = sanitize_text_field( $item['q'] );
				$a = trim( wp_kses( $item['a'], ssbd_faq_allowed_html() ) );

				if ( '' !== $q && '' !== $a ) {
					$clean[] = array(
						'q' => $q,
						'a' => $a,
					);
				}
			}

			$value = $clean ? wp_json_encode( $clean ) : '';
		} elseif ( in_array( $field['type'], array( 'textarea', 'list' ), true ) ) {
			$value = sanitize_textarea_field( $raw );
		} elseif ( in_array( $field['type'], array( 'select', 'icon' ), true ) ) {
			$allowed = 'icon' === $field['type'] ? array_keys( ssbd_data( 'icons' ) ) : array_keys( $field['options'] );
			$value   = in_array( $raw, $allowed, true ) ? $raw : '';
		} else {
			$value = sanitize_text_field( $raw );
		}

		if ( '' === $value ) {
			delete_post_meta( $post_id, '_ssbd_' . $key );
		} else {
			update_post_meta( $post_id, '_ssbd_' . $key, $value );
		}
	}
}
add_action( 'save_post', 'ssbd_save_catalogue_meta', 10, 2 );

/**
 * Show the useful columns in the admin lists.
 *
 * @param array  $columns Existing columns.
 * @param string $type    Post type.
 * @return array
 */
function ssbd_catalogue_columns( $columns, $type ) {
	$new = array();

	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;

		if ( 'title' === $key ) {
			if ( 'ssbd_service' === $type ) {
				$new['ssbd_group'] = __( 'Group', 'ssbd' );
			}
			if ( 'ssbd_product' === $type ) {
				$new['ssbd_status'] = __( 'Status', 'ssbd' );
			}
			if ( 'ssbd_project' === $type ) {
				$new['ssbd_tech'] = __( 'Tech', 'ssbd' );
			}
		}
	}

	$new['ssbd_order'] = __( 'Order', 'ssbd' );

	return $new;
}
foreach ( array( 'ssbd_project', 'ssbd_service', 'ssbd_product' ) as $ssbd_type ) {
	add_filter(
		"manage_{$ssbd_type}_posts_columns",
		static function ( $columns ) use ( $ssbd_type ) {
			return ssbd_catalogue_columns( $columns, $ssbd_type );
		}
	);
}

/**
 * Fill the custom admin columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function ssbd_catalogue_column_content( $column, $post_id ) {
	if ( 'ssbd_order' === $column ) {
		echo (int) get_post_field( 'menu_order', $post_id );
		return;
	}

	if ( str_starts_with( $column, 'ssbd_' ) ) {
		echo esc_html( get_post_meta( $post_id, '_' . $column, true ) );
	}
}
add_action( 'manage_posts_custom_column', 'ssbd_catalogue_column_content', 10, 2 );
