<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Assets' ) ) {

	class Custom_Popup_Builder_Assets {

		public $localize_data = [
			'elements_data' => [
				'sections' => [],
				'columns'  => [],
				'widgets'  => [],
			]
		];

		public $editor_localize_data = [];

		private static $instance = null;

		public function __construct() {

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ), 10 );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_popup_edit_assets' ), 11 );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_library_assets' ), 11 );

			add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'editor_scripts' ) );

			add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'editor_styles' ) );

			add_action( 'elementor/preview/enqueue_styles', array( $this, 'preview_styles' ) );
		}

		public function enqueue_styles() {

			wp_enqueue_style(
				'custom-popup-builder-frontend',
				custom_popup_builder()->plugin_url( 'assets/css/custom-popup-builder-frontend.css' ),
				false,
				custom_popup_builder()->get_version()
			);

		}

		public function enqueue_scripts() {

			wp_enqueue_script(
				'custom-anime-js',
				custom_popup_builder()->plugin_url( 'assets/js/lib/anime-js/anime.min.js' ),
				array( 'jquery' ),
				'2.0.2',
				true
			);

			wp_enqueue_script(
				'custom-popup-builder-frontend',
				custom_popup_builder()->plugin_url( 'assets/js/custom-popup-builder-frontend' . $this->suffix() . '.js' ),
				array( 'jquery', 'elementor-frontend' ),
				custom_popup_builder()->get_version(),
				true
			);

			$this->localize_data['version'] = custom_popup_builder()->get_version();
			$this->localize_data['ajax_url'] = esc_url( admin_url( 'admin-ajax.php' ) );

			wp_localize_script(
				'custom-popup-builder-frontend',
				'customPopupData',
				$this->localize_data
			);

		}

		public function enqueue_admin_assets() {

			wp_register_script(
				'custom-axios',
				custom_popup_builder()->plugin_url( 'assets/js/lib/axios/axios.min.js' ),
				[],
				'0.19.0-beta',
				true
			);

			wp_register_script(
				'custom-iview-locale-en-us',
				custom_popup_builder()->plugin_url( 'assets/js/lib/iview/locale/en-US.js' ),
				[],
				'3.2.2',
				true
			);

			wp_register_script(
				'custom-iview',
				custom_popup_builder()->plugin_url( 'assets/js/lib/iview/iview.min.js' ),
				[],
				'3.2.2',
				true
			);

			wp_register_script(
				'custom-popup-builder-tippy',
				custom_popup_builder()->plugin_url( 'assets/js/lib/tippy/tippy.all.min.js' ),
				array(),
				'2.5.4',
				true
			);

			wp_register_script(
				'custom-popup-builder-tippy',
				custom_popup_builder()->plugin_url( 'assets/js/lib/tippy/tippy.all.min.js' ),
				array(),
				'2.5.4',
				true
			);

			wp_register_style(
				'custom-iview',
				custom_popup_builder()->plugin_url( 'assets/css/lib/iview/iview.css' ),
				[],
				'3.2.2'
			);

		}

		public function enqueue_admin_popup_edit_assets() {
			$screen = get_current_screen();

			if ( $screen->id == 'edit-' . custom_popup_builder()->post_type->slug() ) {
				wp_enqueue_style(
					'custom-popup-builder-admin',
					custom_popup_builder()->plugin_url( 'assets/css/custom-popup-builder-admin.css' ),
					[],
					custom_popup_builder()->get_version()
				);

				wp_enqueue_script(
					'custom-popup-builder-admin',
					custom_popup_builder()->plugin_url( 'assets/js/custom-popup-builder-admin' . $this->suffix() . '.js' ),
					[ 'jquery', 'custom-popup-builder-tippy' ],
					custom_popup_builder()->get_version(),
					true
				);
			}
		}

		public function enqueue_admin_library_assets() {
			$screen = get_current_screen();

			if ( $screen->id == custom_popup_builder()->post_type->slug() . '_page_custom-popup-builder-library' ) {

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
						'custom-iview-locale-en-us'
					],
					custom_popup_builder()->get_version(),
					true
				);

				$localize_data['version'] = custom_popup_builder()->get_version();
				$localize_data['requiredPluginData'] = [
					'custom-elements' => [
						'badge' => custom_popup_builder()->plugin_url( 'assets/image/custom-elements-badge.png' ),
						'link'  => 'http://customelements.zemez.io/',
					],
					'custom-blocks'   => [
						'badge' => custom_popup_builder()->plugin_url( 'assets/image/custom-blocks-badge.png' ),
						'link'  => 'http://customblocks.zemez.io/',
					],
					'custom-tricks'   => [
						'badge' => custom_popup_builder()->plugin_url( 'assets/image/custom-tricks-badge.png' ),
						'link'  => 'http://customtricks.zemez.io/',
					],
					'cf7'          => [
						'badge' => custom_popup_builder()->plugin_url( 'assets/image/cf7-badge.png' ),
						'link'  => 'https://wordpress.org/plugins/contact-form-7/',
					],
				];

				$localize_data['libraryPresetsUrl'] = 'https://custompopup.zemez.io/wp-json/croco/v1/presets';
				$localize_data['libraryPresetsCategoryUrl'] = 'https://custompopup.zemez.io/wp-json/croco/v1/presets-categories';

				$localize_data = apply_filters( 'custom-popup-builder/admin/localized-data', $localize_data );

				wp_localize_script(
					'custom-popup-builder-admin',
					'customPopupData',
					$localize_data
				);
			}
		}

		public function editor_styles() {

			$screen = get_current_screen();

			if ( 'custom-popup-builder' !== $screen->post_type ) {
				return;
			}

			wp_enqueue_style(
				'custom-iview',
				custom_popup_builder()->plugin_url( 'assets/css/lib/iview/iview.css' ),
				[],
				'3.2.2'
			);

			wp_enqueue_style(
				'custom-popup-builder-editor',
				custom_popup_builder()->plugin_url( 'assets/css/custom-popup-builder-editor.css' ),
				array(),
				custom_popup_builder()->get_version()
			);

		}

		public function editor_scripts() {

			$screen = get_current_screen();

			if ( 'custom-popup-builder' !== $screen->post_type ) {
				return;
			}

			wp_enqueue_script(
				'custom-axios',
				custom_popup_builder()->plugin_url( 'assets/js/lib/axios/axios.min.js' ),
				[],
				'0.19.0-beta',
				true
			);

			wp_enqueue_script(
				'custom-iview',
				custom_popup_builder()->plugin_url( 'assets/js/lib/iview/iview.min.js' ),
				[],
				'3.2.2',
				true
			);

			wp_enqueue_script(
				'custom-iview-locale-en-us',
				custom_popup_builder()->plugin_url( 'assets/js/lib/iview/locale/en-US.js' ),
				[],
				'3.2.2',
				true
			);

			wp_enqueue_script(
				'custom-anime-js',
				custom_popup_builder()->plugin_url( 'assets/js/lib/anime-js/anime.min.js' ),
				array( 'jquery' ),
				'2.0.2',
				true
			);

			wp_enqueue_script(
				'custom-popup-builder-editor',
				custom_popup_builder()->plugin_url( 'assets/js/custom-popup-builder-editor' . $this->suffix() . '.js' ),
				array(
					'jquery',
					'underscore',
					'backbone-marionette',
				),
				custom_popup_builder()->get_version(),
				true
			);

			$this->editor_localize_data = apply_filters( 'custom-popup-builder/assets/editor_localize_data', [
				'version'          => custom_popup_builder()->get_version(),
				'conditionManager' => custom_popup_builder()->conditions->prepare_data_for_localize(),
			] );

			wp_localize_script( 'custom-popup-builder-editor', 'customPopupData', $this->editor_localize_data );
		}

		public function preview_styles() {

			wp_enqueue_style(
				'custom-popup-builder-preview',
				custom_popup_builder()->plugin_url( 'assets/css/custom-popup-builder-preview.css' ),
				array(),
				custom_popup_builder()->get_version()
			);
		}

		public function suffix() {
			return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		}

		public static function get_instance() {

			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}

}
