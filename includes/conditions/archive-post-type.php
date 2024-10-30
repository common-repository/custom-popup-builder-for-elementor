<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Conditions_Archive_Post_Type' ) ) {

	class Custom_Popup_Builder_Conditions_Archive_Post_Type extends Custom_Popup_Builder_Conditions_Base {

		public function get_id() {
			return 'archive-post-type';
		}

		public function get_label() {
			return __( 'Post Type Archives', 'custom-popup-builder' );
		}

		public function get_group() {
			return 'archive';
		}

		public function ajax_action() {
			return 'custom_popup_builder_search_archive_types';
		}

		public function get_label_by_value( $value = '' ) {

			$obj = get_post_type_object( $value );

			return $obj->labels->singular_name;
		}

		public function check( $arg = '' ) {

			if ( empty( $arg ) ) {
				return is_post_type_archive();
			}

			if ( 'post' === $arg && 'post' === get_post_type() ) {
				return is_archive() || is_home();
			}

			return is_post_type_archive( $arg ) || ( is_tax() && $arg === get_post_type() );
		}

	}

}
