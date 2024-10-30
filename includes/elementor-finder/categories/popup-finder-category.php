<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

class Custom_Popup_Builder_Finder_Category extends \Elementor\Core\Common\Modules\Finder\Base_Category {

	public function get_title() {
		return __( 'Custom Popup Settings', 'custom-popup-builder' );
	}

	public function get_category_items( array $options = [] ) {
		return [
			'custom-popup-builder-settings' => [
				'title'    => __( 'Custom Popup Settings', 'custom-popup-builder' ),
				'url'      => custom_popup_builder()->settings->get_settings_page_url(),
				'keywords' => [ 'general', 'popup', 'settings', 'custom', 'mailchimp' ],
			],
		];
	}
}
