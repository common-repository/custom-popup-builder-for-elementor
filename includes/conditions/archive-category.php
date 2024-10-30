<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Conditions_Archive_Category' ) ) {

	class Custom_Popup_Builder_Conditions_Archive_Category extends Custom_Popup_Builder_Conditions_Base {

		public function get_id() {
			return 'archive-category';
		}

		public function get_label() {
			return __( 'Category Archives', 'custom-popup-builder' );
		}

		public function get_group() {
			return 'archive';
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
				return is_category();
			}

			return is_category( $arg );
		}

	}

}
