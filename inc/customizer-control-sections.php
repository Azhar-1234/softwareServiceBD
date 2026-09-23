<?php
/**
 * A drag-to-reorder, tick-to-show control for the front page sections.
 *
 * The Customizer has no native sortable control, so this renders a list of
 * checkboxes made sortable with jQuery UI (which WordPress already bundles in
 * admin) and writes the result back as a comma-separated list of enabled keys,
 * in display order.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_Customize_Control' ) ) {
	return;
}

/**
 * Sortable checklist control.
 */
class SSBD_Sections_Control extends WP_Customize_Control {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 'ssbd_sections';

	/**
	 * Every available section: key => label.
	 *
	 * @var array
	 */
	public $choices = array();

	/**
	 * Enqueue the sortable behaviour.
	 */
	public function enqueue() {
		wp_enqueue_script( 'jquery-ui-sortable' );
	}

	/**
	 * Render the control.
	 */
	public function render_content() {
		$stored  = (string) $this->value();
		$enabled = array_filter( array_map( 'trim', explode( ',', $stored ) ) );

		// Anything not explicitly listed is off, but must still be shown so it
		// can be switched back on. Enabled items lead, in their saved order.
		$ordered = array_values( array_intersect( $enabled, array_keys( $this->choices ) ) );
		foreach ( array_keys( $this->choices ) as $key ) {
			if ( ! in_array( $key, $ordered, true ) ) {
				$ordered[] = $key;
			}
		}
		?>
		<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php if ( $this->description ) : ?>
			<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
		<?php endif; ?>

		<ul class="ssbd-sections-list">
			<?php foreach ( $ordered as $key ) : ?>
				<li class="ssbd-section-item" data-key="<?php echo esc_attr( $key ); ?>">
					<span class="ssbd-drag" aria-hidden="true">⣿</span>
					<label>
						<input type="checkbox" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $enabled, true ) ); ?>>
						<?php echo esc_html( $this->choices[ $key ] ); ?>
					</label>
				</li>
			<?php endforeach; ?>
		</ul>

		<input type="hidden" <?php $this->link(); ?> value="<?php echo esc_attr( $stored ); ?>" class="ssbd-sections-value">

		<style>
			.ssbd-sections-list { margin: 10px 0 0; padding: 0; list-style: none; }
			.ssbd-section-item {
				display: flex; align-items: center; gap: 8px;
				margin: 0 0 4px; padding: 8px 10px;
				background: #fff; border: 1px solid #dcdcde; border-radius: 3px;
			}
			.ssbd-section-item label { flex: 1; margin: 0; cursor: pointer; }
			.ssbd-drag { cursor: grab; color: #8c8f94; line-height: 1; }
			.ssbd-section-item.ui-sortable-helper { box-shadow: 0 2px 8px rgba(0,0,0,.16); }
			.ssbd-sections-placeholder { height: 36px; margin: 0 0 4px; border: 1px dashed #c3c4c7; border-radius: 3px; }
		</style>

		<script>
		( function ( $ ) {
			var $control = $( '.ssbd-sections-list' ).last();
			var $field   = $control.nextAll( '.ssbd-sections-value' ).first();

			function sync() {
				var keys = [];
				$control.find( '.ssbd-section-item' ).each( function () {
					var $box = $( this ).find( 'input[type=checkbox]' );
					if ( $box.is( ':checked' ) ) {
						keys.push( $box.val() );
					}
				} );
				// Triggering change is what tells the Customizer the setting is dirty.
				$field.val( keys.join( ',' ) ).trigger( 'change' );
			}

			$control.sortable( {
				handle: '.ssbd-drag',
				placeholder: 'ssbd-sections-placeholder',
				forcePlaceholderSize: true,
				update: sync
			} );

			$control.on( 'change', 'input[type=checkbox]', sync );
		}( jQuery ) );
		</script>
		<?php
	}
}
