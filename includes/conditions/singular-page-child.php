<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Conditions_Singular_Page_Child' ) ) {

	class Custom_Popup_Builder_Conditions_Singular_Page_Child extends Custom_Popup_Builder_Conditions_Base {

		public function get_id() {
			return 'singular-page-child';
		}

		public function get_label() {
			return __( 'Page, Child of', 'custom-popup-builder' );
		}

		public function get_group() {
			return 'singular';
		}

		public function ajax_action() {
			return 'custom_popup_builder_search_pages';
		}

		public function get_label_by_value( $value = '' ) {

			return get_the_title( $value );
		}

		public function check( $arg = '' ) {

			if ( empty( $arg ) ) {
				return false;
			}

			if ( ! is_page() ) {
				return false;
			}

			global $post;

			return in_array( $post->post_parent, $arg );
		}

	}

}
