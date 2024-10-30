<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Conditions_Singular_Post' ) ) {

	class Custom_Popup_Builder_Conditions_Singular_Post extends Custom_Popup_Builder_Conditions_Base {

		public function get_id() {
			return 'singular-post';
		}

		public function get_label() {
			return __( 'Post', 'custom-popup-builder' );
		}

		public function get_group() {
			return 'singular';
		}

		public function ajax_action() {
			return 'custom_popup_builder_search_posts';
		}

		public function get_label_by_value( $value = '' ) {

			return get_the_title( $value );
		}

		public function check( $arg ) {

			if ( empty( $arg ) ) {
				return is_single();
			}

			return is_single( $arg );
		}

	}

}
