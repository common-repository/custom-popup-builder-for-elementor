<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Conditions_Singular_Page_Template' ) ) {

	class Custom_Popup_Builder_Conditions_Singular_Page_Template extends Custom_Popup_Builder_Conditions_Base {

		public function get_id() {
			return 'singular-page-template';
		}

		public function get_label() {
			return __( 'Page Template', 'custom-popup-builder' );
		}

		public function get_group() {
			return 'singular';
		}

		public function ajax_action() {
			return 'custom_popup_builder_search_page_templates';
		}

		public function get_label_by_value( $value = '' ) {
			$template_label = '';

			$templates = wp_get_theme()->get_page_templates();

			if ( ! empty( $templates ) ) {
				foreach ( $templates as $template => $label ) {

					if ( $template === $value ) {
						$template_label = $template;
					}
				}
			}

			return $template_label;
		}

		public function check( $arg = '' ) {

			if ( empty( $arg ) ) {
				return false;
			}

			if ( ! is_page() ) {
				return false;
			}

			global $post;

			$page_template_slug = get_page_template_slug( $post->ID );

			return $page_template_slug === $arg;
		}

	}

}
