<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class Custom_Popup_Builder_Control_Search extends Base_Data_Control {

	public function get_type() {
		return 'custom_popup_builder_search';
	}

	protected function get_default_settings() {
		return array(
			'multiple'     => false,
			'query_params' => array(),
		);
	}

	public function content_template() {
		$control_uid = $this->get_control_uid();
		?>
		<div class="elementor-control-field">
			<label for="<?php echo $control_uid; ?>" class="elementor-control-title">{{{ data.label }}}</label>
			<div class="elementor-control-input-wrapper">
				<# var multiple = ( data.multiple ) ? 'multiple' : ''; #>
				<select id="<?php echo $control_uid; ?>" class="elementor-select2" type="select2" {{ multiple }} data-setting="{{ data.name }}">
					<# if ( multiple ) { #>
						<# _.each( data.controlValue, function( value ) {
							#>
						<option value="{{ value }}" selected>{{ data.saved[ value ] }}</option>
						<# } ); #>
					<# } else { #>
						<option value="{{ data.controlValue }}" selected>{{ data.saved[ data.controlValue ] }}</option>
					<# } #>
				</select>
			</div>
		</div>
		<# if ( data.description ) { #>
			<div class="elementor-control-field-description">{{{ data.description }}}</div>
		<# } #>
		<?php
	}
}
