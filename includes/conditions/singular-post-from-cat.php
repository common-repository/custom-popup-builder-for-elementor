<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Conditions_Singular_Post_From_Category' ) ) {

	class Custom_Popup_Builder_Conditions_Singular_Post_From_Category extends Custom_Popup_Builder_Conditions_Base {

		public function get_id() {
			return 'singular-post-from-cat';
		}

		public function get_label() {
			return __( 'Posts from Category', 'custom-popup-builder' );
		}

		public function get_group() {
			return 'singular';
		}

		public function ajax_action() {
			return 'custom_popup_builder_search_cats';
		}

		public function get_label_by_value( $value = '' ) {

			$terms = get_terms( array(
				'include'    => $value,
				'taxonomy'   => 'category',
				'hide_empty' => false,
			) );

			$label = '';

			if ( ! empty( $terms ) ) {
				foreach ( $terms as $key => $term ) {
					$label .= $term->name;
				}
			}

			return $label;
		}

		public function check( $arg = '' ) {

			if ( empty( $arg ) ) {
				return false;
			}

			if ( ! is_single() ) {
				return false;
			}

			global $post;

			return in_category( $arg, $post );
		}

	}

}