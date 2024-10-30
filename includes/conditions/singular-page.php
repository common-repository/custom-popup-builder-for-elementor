<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Conditions_Singular_Page' ) ) {

	class Custom_Popup_Builder_Conditions_Singular_Page extends Custom_Popup_Builder_Conditions_Base {

		public function get_id() {
			return 'singular-page';
		}

		public function get_label() {
			return __( 'Page', 'custom-popup' );
		}

		public function get_group() {
			return 'singular';
		}

		public function ajax_action() {
			return 'custom_popup_search_pages';
		}

		public function get_label_by_value( $value = '' ) {
			return get_the_title( $value );
		}

		public function check( $arg = '' ) {

			if ( empty( $arg ) ) {
				return is_page();
			}

			return is_page( $arg );
		}

	}

}
