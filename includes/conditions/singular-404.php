<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Conditions_Singular_404' ) ) {

	class Custom_Popup_Builder_Conditions_Singular_404 extends Custom_Popup_Builder_Conditions_Base {

		public function get_id() {
			return 'singular-404';
		}

		public function get_label() {
			return __( '404 Page', 'custom-popup-builder' );
		}

		public function get_group() {
			return 'singular';
		}

		public function check( $arg = '' ) {
			return is_404();
		}

	}

}
