<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Conditions_Singular_Post_Type' ) ) {

	class Custom_Popup_Builder_Conditions_Singular_Post_Type extends Custom_Popup_Builder_Conditions_Base {

		public function get_id() {
			return 'singular-post-type';
		}

		public function get_label() {
			return __( 'Post Type', 'custom-popup-builder' );
		}

		public function get_group() {
			return 'singular';
		}

		public function ajax_action() {
			return 'custom_popup_builder_search_post_types';
		}

		public function get_label_by_value( $value = '' ) {

			$obj = get_post_type_object( $value );

			return $obj->labels->singular_name;
		}

		public function check( $arg = '' ) {

			if ( empty( $arg ) ) {
				return is_singular();
			}

			return is_singular( $arg );
		}

	}

}
