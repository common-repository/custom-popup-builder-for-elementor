<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Settings' ) ) {

	class Custom_Popup_Builder_Settings {

		protected $post_type = 'custom-popup-builder';

		private static $instance = null;

		public $key = 'custom-popup-builder-settings';

		public $localize_data = [];

		public $settings = null;

		public function __construct() {

			add_action( 'admin_menu', [ $this, 'register_page' ], 91 );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_settings_assets' ), 11 );

			add_action( 'admin_footer', [ $this, 'render_vue_template' ] );

			add_action( 'wp_ajax_custom_popup_builder_save_settings', [ $this, 'save_settings' ] );

			add_action( 'wp_ajax_get_mailchimp_user_data', [ $this, 'get_mailchimp_user_data' ] );

			add_action( 'wp_ajax_get_mailchimp_lists', [ $this, 'get_mailchimp_lists' ] );

			add_action( 'wp_ajax_get_mailchimp_list_merge_fields', [ $this, 'get_mailchimp_list_merge_fields' ] );

			$this->generate_localize_data();
		}

		public function generate_localize_data() {

			$mailchimp_api_data = get_option( $this->key . '_mailchimp', [] );

			$this->localize_data = [
				'settings' => [
					'apikey' => $this->get( 'apikey', '' ),
				],
				'mailchimpApiData'  => $mailchimp_api_data,
			];
		}

		public function get( $setting, $default = false ) {

			if ( null === $this->settings ) {
				$this->settings = get_option( $this->key, [] );
			}

			return isset( $this->settings[ $setting ] ) ? $this->settings[ $setting ] : $default;

		}

		public function register_page() {
			add_submenu_page(
				'edit.php?post_type=' . $this->slug(),
				__( 'Settings', 'custom-popup-builder' ),
				__( 'Settings', 'custom-popup-builder' ),
				'edit_pages',
				'custom-popup-builder-settings',
				[ $this, 'settings_page_render']
			);
		}

		public function settings_page_render() {
			$crate_action = add_query_arg(
				array(
					'action' => 'custom_popup_builder_save_settings',
				),
				esc_url( admin_url( 'admin.php' ) )
			);

			require custom_popup_builder()->plugin_path( 'templates/vue-templates/settings-page.php' );
		}

		public function enqueue_admin_settings_assets() {
			$screen = get_current_screen();

			if ( $screen->id == custom_popup_builder()->post_type->slug() . '_page_custom-popup-builder-settings' ) {
				wp_enqueue_style(
					'custom-popup-builder-admin',
					custom_popup_builder()->plugin_url( 'assets/css/custom-popup-builder-admin.css' ),
					[ 'custom-iview' ],
					custom_popup_builder()->get_version()
				);

				wp_enqueue_script(
					'custom-popup-builder-admin',
					custom_popup_builder()->plugin_url( 'assets/js/custom-popup-builder-admin' . $this->suffix() . '.js' ),
					[
						'jquery',
						'custom-popup-builder-vue',
						'custom-axios',
						'custom-iview',
					],
					custom_popup_builder()->get_version(),
					true
				);

				$localize_data = apply_filters( 'custom-popup-builder/admin/settings-page/localized-data', $this->localize_data );

				wp_localize_script(
					'custom-popup-builder-admin',
					'customPopupAdminData',
					$localize_data
				);
			}
		}

		public function render_vue_template() {

			$vue_templates = [
				'settings-form',
				'mailchimp-list-item',
			];

			foreach ( glob( custom_popup_builder()->plugin_path( 'templates/vue-templates/' ) . '*.php' ) as $file ) {
				$path_info = pathinfo( $file );
				$template_name = $path_info['filename'];

				if ( in_array( $template_name, $vue_templates ) ) {?>
					<script type="text/x-template" id="<?php echo $template_name; ?>-template"><?php
						require $file; ?>
					</script><?php
				}
			}
		}

		public function save_settings() {
			$data = sanitize_text_field($_POST['data']);
			$data = ( ! empty( $data ) ) ? sanitize_text_field($data) : false;

			if ( ! $data ) {
				wp_send_json( [
					'type' => 'error',
					'title' => __( 'Error', 'custom-popup-builder' ),
					'desc'  => __( 'Server error. Please, try again later', 'custom-popup-builder' ),
				] );
			}

			$current = get_option( $this->key, [] );

			foreach ( $data as $key => $value ) {
				$current[ $key ] = is_array( $value ) ? $value : esc_attr_e( $value );
			}

			update_option( $this->key, $current );

			wp_send_json( [
				'type'  => 'success',
				'title' => __( 'Success', 'custom-popup-builder' ),
				'desc'  => __( 'Settings have been saved!', 'custom-popup-builder' ),
			] );
		}

		public function get_mailchimp_user_data() {

			if ( empty( $_POST['apikey']) ) {
				wp_send_json( [
					'type' => 'error',
					'title' => __( 'Error', 'custom-popup-builder' ),
					'desc'  => __( 'Server error. Please, try again later', 'custom-popup-builder' ),
				] );
			}

			$api_key = sanitize_text_field($_POST['apikey']);

			$key_data = explode( '-', $api_key );

			$api_server = sprintf( 'https://%s.api.mailchimp.com/3.0/', $key_data[1] );

			$url = esc_url( trailingslashit( $api_server ) );

			$request = wp_remote_post( $url, [
				'method'      => 'GET',
				'timeout'     => 20,
				'headers'     => [
					'Content-Type'  => 'application/json',
					'Authorization' => 'apikey ' . $api_key
				],
			] );

			if ( is_wp_error( $request ) ) {
				wp_send_json( [
					'type' => 'error',
					'title' => __( 'MailChimp Error', 'custom-popup-builder' ),
					'desc'  => __( 'Server error. Please, check your apikey status or format', 'custom-popup-builder' ),
				] );
			}

			$request = json_decode( wp_remote_retrieve_body( $request ), true );

			$current = get_option( $this->key . '_mailchimp', [] );

			$current[ $api_key ]['account'] = $request;

			update_option( $this->key . '_mailchimp', $current );

			wp_send_json( [
				'type'     => 'success',
				'title'    => __( 'Success', 'custom-popup-builder' ),
				'desc'     => __( 'Account Data were received', 'custom-popup-builder' ),
				'request'  => $request,
			] );
		}

		public function get_mailchimp_lists() {

			if ( empty( $_POST['apikey']) ) {
				wp_send_json( [
					'type' => 'error',
					'title' => __( 'Error', 'custom-popup-builder' ),
					'desc'  => __( 'Server error. Please, try again later', 'custom-popup-builder' ),
				] );
			}

			$api_key = sanitize_text_field($_POST['apikey']);

			$key_data = explode( '-', $api_key );

			$api_server = sprintf( 'https://%s.api.mailchimp.com/3.0/', $key_data[1] );

			$url = esc_url( trailingslashit( $api_server . 'lists' ) );

			$request = wp_remote_post( $url, [
				'method'      => 'GET',
				'timeout'     => 20,
				'headers'     => [
					'Content-Type'  => 'application/json',
					'Authorization' => 'apikey ' . $api_key
				],
			] );

			if ( is_wp_error( $request ) ) {
				wp_send_json( [
					'type' => 'error',
					'title' => __( 'MailChimp Error', 'custom-popup-builder' ),
					'desc'  => __( 'Server error. Please, check your apikey status or format', 'custom-popup-builder' ),
				] );
			}

			$request = json_decode( wp_remote_retrieve_body( $request ), true );

			$current = get_option( $this->key . '_mailchimp', [] );

			if ( array_key_exists( 'lists', $request ) ) {
				$lists = $request['lists'];
				$temp_lists = [];

				if ( ! empty( $lists ) ) {
					foreach ( $lists as $key => $list_data ) {
						$temp_lists[ $list_data[ 'id' ] ]['info'] = $list_data;
					}

					$current[ $api_key ]['lists'] = $temp_lists;
				}

				update_option( $this->key . '_mailchimp', $current );
			}

			wp_send_json( [
				'type'     => 'success',
				'title'    => __( 'Success', 'custom-popup-builder' ),
				'desc'     => __( 'Lists were received', 'custom-popup-builder' ),
				'request'  => $request,
			] );
		}

		public function get_mailchimp_list_merge_fields() {

			if ( empty( $_POST['apikey'] ) ) {
				wp_send_json( [
					'type' => 'error',
					'title' => __( 'Error', 'custom-popup-builder' ),
					'desc'  => __( 'Server error. Please, try again later', 'custom-popup-builder' ),
				] );
			}

			$api_key = sanitize_text_field($_POST['apikey']);

			$key_data = explode( '-', $api_key );

			$list_id = sanitize_text_field($_POST['listid']);

			$api_server = sprintf( 'https://%s.api.mailchimp.com/3.0/', $key_data[1] );

			$url = esc_url( trailingslashit( $api_server . 'lists/' . $list_id . '/merge-fields' ) );

			$request = wp_remote_post( $url, [
				'method'      => 'GET',
				'timeout'     => 20,
				'headers'     => [
					'Content-Type'  => 'application/json',
					'Authorization' => 'apikey ' . $api_key
				],
			] );

			if ( is_wp_error( $request ) ) {
				wp_send_json( [
					'type' => 'error',
					'title' => __( 'MailChimp Error', 'custom-popup-builder' ),
					'desc'  => __( 'Server error. Please, check your apikey status or format', 'custom-popup-builder' ),
				] );
			}

			$request = json_decode( wp_remote_retrieve_body( $request ), true );

			$current = get_option( $this->key . '_mailchimp', [] );

			if ( array_key_exists( 'merge_fields', $request ) ) {
				$current[ $api_key ]['lists'][ $list_id ]['merge_fields'] = $request['merge_fields'];
				update_option( $this->key . '_mailchimp', $current );
			}

			wp_send_json( [
				'type'     => 'success',
				'title'    => __( 'Success', 'custom-popup-builder' ),
				'desc'     => __( 'Merge Fields were received', 'custom-popup-builder' ),
				'request'  => $request,
			] );
		}

		public function get_user_lists() {
			$current = get_option( custom_popup_builder()->settings->key . '_mailchimp', [] );

			$current_api = $this->get( 'apikey', '' );

			if ( empty( $current_api ) || ! array_key_exists( $current_api, $current ) ) {
				return false;
			}

			$apikey_data = $current[ $current_api ];

			if ( ! array_key_exists( 'lists', $apikey_data ) ) {
				return false;
			}

			$lists = $apikey_data['lists'];

			return $lists;
		}

		public function slug() {
			return $this->post_type;
		}

		public function suffix() {
			return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		}

	
		public function get_settings_page_url() {
			return admin_url( 'edit.php?post_type=' . $this->slug() . '&page=' . $this->slug() . '-settings' );
		}

		public static function get_instance() {
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}
}
