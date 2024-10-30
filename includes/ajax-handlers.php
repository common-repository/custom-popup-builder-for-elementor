<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Ajax_Handlers' ) ) {

	class Custom_Popup_Builder_Ajax_Handlers {

		private static $instance = null;

		public $sys_messages = [];

		private $api_server = 'https://%s.api.mailchimp.com/3.0/';

		public function __construct() {

			$this->sys_messages = [
				'invalid_mail'      => esc_html__( 'Please, provide valid mail', 'custom-popup-builder' ),
				'mailchimp'         => esc_html__( 'Please, set up MailChimp API key and List ID', 'custom-popup-builder' ),
				'internal'          => esc_html__( 'Internal error. Please, try again later', 'custom-popup-builder' ),
				'server_error'      => esc_html__( 'Server error. Please, try again later', 'custom-popup-builder' ),
				'subscribe_success' => esc_html__( 'E-mail %s has been subscribed', 'custom-popup-builder' ),
				'no_data'           => esc_html__( 'No Data Found', 'custom-popup-builder' ),
			];

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				add_action( 'wp_ajax_custom_popup_builder_mailchimp_ajax', [ $this, 'custom_popup_builder_mailchimp_ajax' ] );
				add_action( 'wp_ajax_nopriv_custom_popup_builder_mailchimp_ajax', [ $this, 'custom_popup_builder_mailchimp_ajax' ] );

				add_action( 'wp_ajax_custom_popup_builder_get_content', [ $this, 'custom_popup_builder_get_content' ] );
				add_action( 'wp_ajax_nopriv_custom_popup_builder_get_content', [ $this, 'custom_popup_builder_get_content' ] );
			}
		}

		public function custom_popup_builder_mailchimp_ajax() {
			$data = sanitize_text_field($_POST['data']);
			$data = ( ! empty( $data ) ) ? $data : false;

			if ( ! $data ) {
				wp_send_json_error( array( 'type' => 'error', 'message' => $this->sys_messages['server_error'] ) );
			}

			$api_key = custom_popup_builder()->settings->get( 'apikey' );

			if ( ! $api_key ) {
				wp_send_json( array( 'type' => 'error', 'message' => $this->sys_messages['mailchimp'] ) );
			}

			$list_id = $data['target_list_id'];

			if ( ! $list_id ) {
				wp_send_json( array( 'type' => 'error', 'message' => $this->sys_messages['mailchimp'] ) );
			}

			$mail = $data['email'];

			if ( empty( $mail ) || ! is_email( $mail ) ) {
				wp_send_json( array( 'type' => 'error', 'message' => $this->sys_messages['invalid_mail'] ) );
			}

			$double_opt_in = false;

			$user_lists = custom_popup_builder()->settings->get_user_lists();

			if ( array_key_exists( $list_id, $user_lists ) ) {
				$double_opt_in = $user_lists[ $list_id ][ 'info' ]['double_optin'];
			}

			$args = [
				'email_address' => $mail,
				'status'        => $double_opt_in ? 'pending' : 'subscribed',
			];

			if ( ! empty( $data['additional'] ) ) {

				$additional = $data['additional'];

				foreach ( $additional as $key => $value ) {
					$field_key = strtoupper( $key );

					if ( 'BIRTHDAY' == $field_key ) {
						$date = new DateTime( $value );
						$value = $date->format( 'm/d' );
					}

					$merge_fields[ $field_key ] = $value;
				}

				$args['merge_fields'] = $merge_fields;

			}

			$response = $this->api_call( $api_key, $list_id, $args );

			if ( false === $response ) {
				wp_send_json( array( 'type' => 'error', 'message' => $this->sys_messages['mailchimp'] ) );
			}

			$response = json_decode( $response, true );

			if ( empty( $response ) ) {
				wp_send_json( array( 'type' => 'error', 'message' => $this->sys_messages['internal'] ) );
			}

			if ( isset( $response['status'] ) && 400 == $response['status'] ) {

				$message = esc_html( $response['detail'] );

				if ( is_array( $response['errors'] ) && ! empty( $response['errors'] ) ) {
					foreach ( $response['errors'] as $key => $error ) {
						$message .= sprintf( ' <b>%s</b> %s', $error['field'], $error['message'] );
					}
				}

				wp_send_json( array( 'type' => 'error', 'message' => $message ) );
			}

			$subscribe_success = sprintf( $this->sys_messages['subscribe_success'], $response['email_address'] );

			wp_send_json( array( 'type' => 'success', 'message' => $subscribe_success ) );
		}

		public function api_call( $api_key, $list_id, $args = [] ) {

			$key_data = explode( '-', $api_key );

			if ( empty( $key_data ) || ! isset( $key_data[1] ) ) {
				return false;
			}

			$this->api_server = sprintf( 'https://%s.api.mailchimp.com/3.0/', $key_data[1] );

			$url = esc_url( trailingslashit( $this->api_server . 'lists/' . $list_id . '/members/' ) );

			$data = json_encode( $args );

			$request_args = [
				'method'      => 'POST',
				'timeout'     => 20,
				'headers'     => [
					'Content-Type'  => 'application/json',
					'Authorization' => 'apikey ' . $api_key
				],
				'body'        => $data,
			];

			$request = wp_remote_post( $url, $request_args );

			return wp_remote_retrieve_body( $request );
		}

		public function custom_popup_builder_get_content() {
			$data = sanitize_text_field($_POST['data']);
			$data = ( ! empty( $data ) ) ? $data : false;

			if ( ! $data ) {
				wp_send_json_error( [ 'type' => 'error', 'message' => $this->sys_messages['server_error'] ] );
			}

			$popup_data = apply_filters( 'custom-popup-builder/ajax-request/post-data', $data );

			$content = apply_filters( 'custom-popup-builder/ajax-request/get-elementor-content', false, $popup_data );

			if ( ! $content ) {
				
				$content = $this->get_popup_content( $popup_data );
			}

			if ( empty( $content ) ) {
				wp_send_json( [
					'type'    => 'error',
					'message' => $this->sys_messages['no_data']
				] );
			}

			wp_send_json(
				[
					'type'    => 'success',
					'content' => $content,
				]
			);
		}

		public function get_popup_content( $popup_data ) {

			$popup_id = $popup_data['popup_id'];

			if ( empty( $popup_id ) ) {
				return false;
			}

			$plugin = Elementor\Plugin::instance();

			$content = $plugin->frontend->get_builder_content( $popup_id );

			return $content;
		}

		public static function get_instance() {

			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}
}
