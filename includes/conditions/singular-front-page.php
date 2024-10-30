<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Conditions_Front' ) ) {

	class Custom_Popup_Builder_Conditions_Front extends Custom_Popup_Builder_Conditions_Base {

		public function get_id() {
			return 'singular-front-page';
		}

		public function get_label() {
			return __( 'Front Page', 'custom-popup-builder' );
		}

		public function get_group() {
			return 'singular';
		}

		public function check( $arg = '' ) {
			return is_front_page();
		}

	}

}
