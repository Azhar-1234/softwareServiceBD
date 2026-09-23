<?php
/**
 * A tick-any-number-of-them control.
 *
 * The Customizer has no native multi-select, and its `select` with `multiple`
 * does not save. This renders a plain checkbox list and writes the result back
 * as a comma-separated list of the ticked values, which is the same shape the
 * sections control uses — see inc/customizer-control-sections.php.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_Customize_Control' ) ) {
	return;
}

/**
 * Checkbox list control.
 */
class SSBD_Checklist_Control extends WP_Customize_Control {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 'ssbd_checklist';

	/**
	 * Every available option: value => label.
	 *
	 * @var array
	 */
	public $choices = array();

	/**
	 * What an empty selection means, shown under the list.
	 *
	 * @var string
	 */
	public $empty_label = '';

	/**
	 * Render the control.
	 */
	public function render_content() {
		$stored   = (string) $this->value();
		$selected = array_filter( array_map( 'trim', explode( ',', $stored ) ) );
		$list_id  = 'ssbd-checklist-' . sanitize_html_class( $this->id );
		?>
		<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php if ( $this->description ) : ?>
			<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
		<?php endif; ?>

		<?php if ( ! $this->choices ) : ?>
			<p class="description"><?php esc_html_e( 'Nothing to choose from yet.', 'ssbd' ); ?></p>
		<?php else : ?>
			<ul class="ssbd-checklist" id="<?php echo esc_attr( $list_id ); ?>">
				<?php foreach ( $this->choices as $value => $label ) : ?>
					<li>
						<label>
							<input type="checkbox" value="<?php echo esc_attr( $value ); ?>" <?php checked( in_array( (string) $value, $selected, true ) ); ?>>
							<?php echo esc_html( $label ); ?>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php if ( $this->empty_label ) : ?>
				<p class="description ssbd-checklist-empty"><?php echo esc_html( $this->empty_label ); ?></p>
			<?php endif; ?>
		<?php endif; ?>

		<input type="hidden" <?php $this->link(); ?> value="<?php echo esc_attr( $stored ); ?>" class="ssbd-checklist-value">

		<style>
			.ssbd-checklist { margin: 10px 0 0; padding: 0; list-style: none; max-height: 260px; overflow-y: auto; }
			.ssbd-checklist li { margin: 0 0 4px; padding: 7px 10px; background: #fff; border: 1px solid #dcdcde; border-radius: 3px; }
			.ssbd-checklist label { display: flex; align-items: center; gap: 8px; margin: 0; cursor: pointer; }
			.ssbd-checklist-empty { margin-top: 8px; }
		</style>

		<script>
		( function ( $ ) {
			var $list  = $( '#<?php echo esc_js( $list_id ); ?>' );
			var $field = $list.nextAll( '.ssbd-checklist-value' ).first();

			$list.on( 'change', 'input[type=checkbox]', function () {
				var values = $list.find( 'input[type=checkbox]:checked' ).map( function () {
					return this.value;
				} ).get();

				// Triggering change is what tells the Customizer the setting is dirty.
				$field.val( values.join( ',' ) ).trigger( 'change' );
			} );
		}( jQuery ) );
		</script>
		<?php
	}
}
