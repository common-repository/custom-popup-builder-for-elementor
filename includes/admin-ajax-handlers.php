<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Admin_Ajax_Handlers' ) ) {

	class Custom_Popup_Builder_Admin_Ajax_Handlers {

		private static $instance = null;

		public function __construct() {

			$priv_actions = array(
				'custom_popup_builder_search_posts'          => array( $this, 'search_posts' ),
				'custom_popup_builder_search_pages'          => array( $this, 'search_pages' ),
				'custom_popup_builder_search_cats'           => array( $this, 'search_cats' ),
				'custom_popup_builder_search_tags'           => array( $this, 'search_tags' ),
				'custom_popup_builder_search_terms'          => array( $this, 'search_terms' ),
				'custom_popup_builder_search_archive_types'  => array( $this, 'search_archive_types' ),
				'custom_popup_builder_search_post_types'     => array( $this, 'search_post_types' ),
				'custom_popup_builder_search_page_templates' => array( $this, 'search_page_templates' ),
				'custom_popup_builder_save_conditions'       => array( $this, 'popup_save_conditions' ),
			);

			foreach ( $priv_actions as $tag => $callback ) {
				add_action( 'wp_ajax_' . $tag, $callback );
			}
		}

		public function search_pages() {

			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json( array() );
			}
			$query = sanitize_key( $_GET['q'] );
			$query = isset( $query ) ? esc_attr_e( $query ) : '';

			wp_send_json( array(
				'results' => Custom_Popup_Builder_Utils::search_posts_by_type( 'page', $query ),
			) );

		}

		public function search_posts() {

			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json( array() );
			}
			$query = sanitize_key( $_GET['q'] );
			$query     = isset( $query ) ? esc_attr_e( $query ) : '';
			$post_type = sanitize_key( $_GET['preview_post_type'] );
			$post_type = isset( $post_type ) ? esc_attr_e( $post_type ) : 'post';

			wp_send_json( array(
				'results' => Custom_Popup_Builder_Utils::search_posts_by_type( $post_type, $query ),
			) );

		}

		public function search_cats() {

			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json( array() );
			}
			
			$query = sanitize_key( $_GET['q'] );
			$query = isset( $query ) ? esc_attr_e( $query ) : '';

			wp_send_json( array(
				'results' => Custom_Popup_Builder_Utils::search_terms_by_tax( 'category', $query ),
			) );

		}

		public function search_tags() {

			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json( array() );
			}

			$query = sanitize_key( $_GET['q'] );
			$query = isset( $query ) ? esc_attr_e( $query ) : '';

			wp_send_json( array(
				'results' => Custom_Popup_Builder_Utils::search_terms_by_tax( 'post_tag', $query ),
			) );

		}

		public function search_terms() {

			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json( array() );
			}
			$query = sanitize_key( $_GET['q'] );
			$query = isset( $query ) ? esc_attr_e( $query ) : '';
			$tax   = sanitize_key($_GET['conditions_archive-tax_tax']);
			$tax   = isset( $tax ) ? esc_attr_e($tax) : '';
			$tax   = explode( ',', $tax );

			wp_send_json( array(
				'results' => Custom_Popup_Builder_Utils::search_terms_by_tax( $tax, $query ),
			) );

		}

		public function search_archive_types() {

			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json( array() );
			}

			$query = sanitize_key( $_GET['q'] );
			$query = isset( $query ) ? esc_attr_e( $query ) : '';

			$result = [];

			$types = Custom_Popup_Builder_Utils::get_post_types();

			if ( ! empty( $types ) ) {
				foreach ( $types as $type => $label ) {
					$result[] = array(
						'id'   => $type,
						'text' => $label,
					);
				}
			}

			wp_send_json( array(
				'results' => $result,
			) );
		}

		public function search_post_types() {

			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json( array() );
			}

			$query = sanitize_key( $_GET['q'] );
			$query = isset( $query ) ? esc_attr_e( $query ) : '';

			$result = [];

			$types = Custom_Popup_Builder_Utils::get_post_types();

			if ( ! empty( $types ) ) {
				foreach ( $types as $type => $label ) {
					$result[] = array(
						'id'   => $type,
						'text' => $label,
					);
				}
			}

			wp_send_json( array(
				'results' => $result,
			) );
		}

		public function search_page_templates() {

			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json( array() );
			}

			$query = sanitize_key( $_GET['q'] );
			$query = isset( $query ) ? esc_attr_e( $query ) : '';

			$result = [];

			$templates = wp_get_theme()->get_page_templates();

			if ( ! empty( $templates ) ) {
				foreach ( $templates as $template => $label ) {
					$result[] = array(
						'id'   => $template,
						'text' => $label,
					);
				}
			}

			wp_send_json( array(
				'results' => $result,
			) );
		}

		public function popup_save_conditions() {

			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json( [
					'type'    => 'error',
					'message' => [
						'title' => esc_html__( 'Error', 'custom-popup-builder' ),
						'desc'  => esc_html__( 'You have no POWER!!!', 'custom-popup-builder' )
					],
				] );
			}

			$popup_id = ( ! empty( sanitize_text_field($_POST['popup_id'] )) ) ? sanitize_text_field($_POST['popup_id']) : false;

			$conditions = ( ! empty( sanitize_text_field($_POST['conditions']) ) ) ? sanitize_text_field($_POST['conditions']) : [];

			if ( ! $popup_id ) {
				wp_send_json( [
					'type'    => 'error',
					'message' => [
						'title' => esc_html__( 'Error', 'custom-popup-builder' ),
						'desc'  => esc_html__( 'Server Error', 'custom-popup-builder' )
					],
				] );
			}

			custom_popup_builder()->conditions->update_popup_conditions( $popup_id, $conditions );

			wp_send_json( [
				'type'    => 'success',
				'message' => [
					'title' => esc_html__( 'Success', 'custom-popup-builder' ),
					'desc'  => esc_html__( 'Conditions have been saved!', 'custom-popup-builder' ),
				],
			] );

		}
	}
}
