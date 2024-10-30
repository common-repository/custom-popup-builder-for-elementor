<?php
/**
 * Teamplte type popup
 */

$class_array = [
	'custom-popup-builder',
	'custom-popup-builder--front-mode',
	'custom-popup-builder--hide-state',
	'custom-popup-builder--animation-' . $popup_settings_main['custom_popup_animation'],
];

$class_attr = implode( ' ', $class_array );

?>
<div id="custom-popup-builder-<?php echo $popup_id; ?>" class="<?php echo $class_attr; ?>" data-settings="<?php echo $popup_json_data; ?>">
	<div class="custom-popup-builder__inner">
		<?php echo $overlay_html; ?>
		<div class="custom-popup-builder__container">
			<div class="custom-popup-builder__container-inner">
				<div class="custom-popup-builder__container-overlay"></div>
				<div class="custom-popup-builder__container-content"><?php
					if ( ! filter_var( $popup_settings_main['custom_popup_use_ajax'], FILTER_VALIDATE_BOOLEAN ) ) {
						$this->print_location_content( $popup_id );
					}
				?></div>
			</div>
			<?php echo $close_button_html; ?>
		</div>
	</div>
</div>
