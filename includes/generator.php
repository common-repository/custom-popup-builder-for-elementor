<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Generator' ) ) {

	class Custom_Popup_Builder_Generator {

		private static $instance = null;

		public $popup_default_settings = [
			'custom_popup_builder_type'                 => 'default',
			'custom_popup_builder_animation'            => 'fade',
			'custom_popup_builder_open_trigger'         => 'attach',
			'custom_popup_builder_page_load_delay'      => 1,
			'custom_popup_builder_user_inactivity_time' => 3,
			'custom_popup_builder_scrolled_to_value'    => 10,
			'custom_popup_builder_on_date_value'        => '',
			'custom_popup_builder_custom_selector'      => '',
			'custom_popup_builder_show_once'            => false,
			'custom_popup_builder_show_again_delay'     => 'none',
			'custom_popup_builder_use_ajax'             => false,
			'custom_popup_builder_force_ajax'           => false,
			'custom_role_condition'             => [],
			'close_button_icon'              => 'fa fa-times',
		];

		public $popup_id_list = [];

		public $ajax_popup_id_list = [];

		public $depended_scripts = [];

		public $fonts_to_enqueue = [];

		public function __construct() {

			add_action( 'wp_footer', array( $this, 'page_popups_init' ), 9 );

			add_action( 'wp_footer', array( $this, 'page_popups_render' ), 10 );

			add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'page_popups_before_enqueue_scripts' ) );
		}

		public function page_popups_init( $origin_data ) {

			$this->define_page_popups();

			return $origin_data;
		}

		public function define_page_popups() {

			if ( ! custom_popup_builder()->has_elementor() || ! empty( $_GET['elementor-preview'] ) ) {
				return false;
			}

			$condition_popups = custom_popup_builder()->conditions->find_matched_conditions( 'custom-popup-builder' );

			if ( ! empty( $condition_popups ) && is_array( $condition_popups ) ) {
				$this->popup_id_list = array_merge( $this->popup_id_list, $condition_popups );
			}

			$attached_popups = custom_popup_builder()->conditions->get_attached_popups();

			if ( ! empty( $attached_popups ) && is_array( $attached_popups ) ) {
				$this->popup_id_list = array_merge( $this->popup_id_list, $attached_popups );
			}

			if ( ! $this->popup_id_list || empty( $this->popup_id_list ) || ! is_array( $this->popup_id_list ) ) {
				return false;
			}

			$this->popup_id_list = array_unique( $this->popup_id_list );

			$this->define_popups_assets();
		}

		public function define_popups_assets() {

			if ( ! empty( $this->popup_id_list ) ) {

				foreach ( $this->popup_id_list as $key => $popup_id ) {
					$meta_settings = get_post_meta( $popup_id, '_elementor_page_settings', true );

					$popup_settings_main = wp_parse_args( $meta_settings, $this->popup_default_settings );

					if ( filter_var( $popup_settings_main['custom_popup_builder_use_ajax'], FILTER_VALIDATE_BOOLEAN ) ) {
						$document = Elementor\Plugin::$instance->documents->get( $popup_id );

						$elements_data = $document->get_elements_raw_data();

						$this->find_widgets_script_handlers( $elements_data );

						$this->find_popup_fonts( $popup_id );
					}
				}
			}
		}

		public function page_popups_render() {
			if ( ! empty( $this->popup_id_list ) ) {

				foreach ( $this->popup_id_list as $key => $popup_id ) {
					$this->popup_render( $popup_id );
				}

				if ( ! empty( $this->ajax_popup_id_list ) && ! Elementor\Plugin::$instance->frontend->has_elementor_in_page() ) {
					Elementor\Plugin::$instance->frontend->enqueue_styles();
					Elementor\Plugin::$instance->frontend->enqueue_scripts();
				}

				$this->print_fonts_links();
			}
		}

		public function popup_render( $popup_id ) {

			$meta_settings = get_post_meta( $popup_id, '_elementor_page_settings', true );

			$popup_settings_main = wp_parse_args( $meta_settings, $this->popup_default_settings );

			if ( ! $this->is_avaliable_for_user( $popup_settings_main['custom_role_condition'] ) ) {
				return false;
			}

			$close_button_html = '';

			$use_close_button = isset( $popup_settings_main['use_close_button'] ) ? filter_var( $popup_settings_main['use_close_button'], FILTER_VALIDATE_BOOLEAN ) : true;

			if ( isset( $popup_settings_main['close_button_icon'] ) && $use_close_button ) {
				$close_button_icon = $popup_settings_main['close_button_icon'];
				$close_button_html = sprintf( '<div class="custom-popup-builder__close-button"><i class="%s"></i></div>', $close_button_icon );
			}

			$overlay_html = '';

			$use_overlay = isset( $popup_settings_main['use_overlay'] ) ? filter_var( $popup_settings_main['use_overlay'], FILTER_VALIDATE_BOOLEAN ) : true;

			if ( $use_overlay ) {
				$use_ajax = filter_var( $popup_settings_main['custom_popup_builder_use_ajax'], FILTER_VALIDATE_BOOLEAN );

				$overlay_html = sprintf(
					'<div class="custom-popup-builder__overlay">%s</div>',
					$use_ajax ? '<div class="custom-popup-builder-loader"></div>' : ''
				);
			}

			$custom_popup_builder_show_again_delay = Custom_Popup_Builder_Utils::get_milliseconds_by_tag( $popup_settings_main['custom_popup_builder_show_again_delay'] );

			$popup_json = [
				'id'                   => $popup_id,
				'custom-popup-builder-id'         => 'custom-popup-builder-' . $popup_id,
				'type'                 => $popup_settings_main['custom_popup_builder_type'],
				'animation'            => $popup_settings_main['custom_popup_builder_animation'],
				'open-trigger'         => $popup_settings_main['custom_popup_builder_open_trigger'],
				'page-load-delay'      => $popup_settings_main['custom_popup_builder_page_load_delay'],
				'user-inactivity-time' => $popup_settings_main['custom_popup_builder_user_inactivity_time'],
				'scrolled-to'          => $popup_settings_main['custom_popup_builder_scrolled_to_value'],
				'on-date'              => $popup_settings_main['custom_popup_builder_on_date_value'],
				'custom-selector'      => $popup_settings_main['custom_popup_builder_custom_selector'],
				'show-once'            => filter_var( $popup_settings_main['custom_popup_builder_show_once'], FILTER_VALIDATE_BOOLEAN ),
				'show-again-delay'     => $custom_popup_builder_show_again_delay,
				'use-ajax'             => filter_var( $popup_settings_main['custom_popup_builder_use_ajax'], FILTER_VALIDATE_BOOLEAN ),
				'force-ajax'           => filter_var( $popup_settings_main['custom_popup_builder_force_ajax'], FILTER_VALIDATE_BOOLEAN ),
			];

			if ( filter_var( $popup_settings_main['custom_popup_builder_use_ajax'], FILTER_VALIDATE_BOOLEAN ) ) {
				$this->ajax_popup_id_list[] = $popup_id;
			}

			$popup_json_data = htmlspecialchars( json_encode( $popup_json ) );

			include custom_popup_builder()->get_template( 'popup-container.php' );
		}

		public function page_popups_before_enqueue_scripts() {

			$script_depends = $this->get_script_depends();

			$script_depends = array_unique( $script_depends );

			foreach ( $script_depends as $script ) {
				wp_enqueue_script( $script );
			}
		}

		public function find_widgets_script_handlers( $elements_data ) {

			foreach ( $elements_data as $element_data ) {

				if ( 'widget' === $element_data['elType'] ) {
					$widget = Elementor\Plugin::$instance->elements_manager->create_element_instance( $element_data );

					$widget_script_depends = $widget->get_script_depends();

					if ( ! empty( $widget_script_depends ) ) {
						foreach ( $widget_script_depends as $key => $script_handler ) {
							$this->depended_scripts[] = $script_handler;
						}
					}

				} else {
					$element = Elementor\Plugin::$instance->elements_manager->create_element_instance( $element_data );

					$childrens = $element->get_children();

					foreach ( $childrens as $key => $children ) {
						$children_data[$key] = $children->get_raw_data();

						$this->find_widgets_script_handlers( $children_data );
					}
				}
			}
		}

		public function find_popup_fonts( $popup_id ) {

			$post_css = new Elementor\Core\Files\CSS\Post( $popup_id );

			$post_meta = $post_css->get_meta();

			if ( ! isset( $post_meta['fonts'] ) ) {
				return false;
			}

			$google_fonts = $post_meta['fonts'];

			$this->fonts_to_enqueue = array_merge( $this->fonts_to_enqueue, $google_fonts );
		}

		public function print_fonts_links() {

			if ( empty( $this->fonts_to_enqueue ) ) {
				return false;
			}

			$this->fonts_to_enqueue = array_unique( $this->fonts_to_enqueue );

			foreach ( $this->fonts_to_enqueue as &$font ) {
				$font = str_replace( ' ', '+', $font ) . ':100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic';
			}

			$fonts_url = sprintf( 'https://fonts.googleapis.com/css?family=%s', implode( rawurlencode( '|' ), $this->fonts_to_enqueue ) );

			$subsets = [
				'ru_RU' => 'cyrillic',
				'bg_BG' => 'cyrillic',
				'he_IL' => 'hebrew',
				'el'    => 'greek',
				'vi'    => 'vietnamese',
				'uk'    => 'cyrillic',
				'cs_CZ' => 'latin-ext',
				'ro_RO' => 'latin-ext',
				'pl_PL' => 'latin-ext',
			];

			$locale = get_locale();

			if ( isset( $subsets[ $locale ] ) ) {
				$fonts_url .= '&subset=' . $subsets[ $locale ];
			}

			wp_enqueue_style( 'custom-popup-builder-google-fonts', $fonts_url );
		}

		public function get_script_depends() {

			return $this->depended_scripts;
		}

		public function is_avaliable_for_user( $popup_roles ) {

			if ( empty( $popup_roles ) ) {
				return true;
			}

			$user     = wp_get_current_user();
			$is_guest = empty( $user->roles ) ? true : false;

			if ( ! $is_guest ) {
				$user_role = $user->roles[0];
			} else {
				$user_role = 'guest';
			}

			if ( in_array( $user_role, $popup_roles ) ) {
				return true;
			}

			return false;
		}

		public function print_location_content( $template_id = 0 ) {

			$plugin    = Elementor\Plugin::instance();

			$content   = $plugin->frontend->get_builder_content( $template_id, false );

			if ( empty( $_GET['elementor-preview'] ) ) {
				echo $content;
			} else {
				printf(
					'<div class="custom-popup-builder-edit">
						%1$s
						<a href="%2$s" target="_blank" class="custom-popup-builder-edit__link elementor-clickable">
							<div class="custom-popup-builder-edit__link-content"><span class="dashicons dashicons-edit"></span>%3$s</div>
						</a>
					</div>',
					$content,
					Elementor\Utils::get_edit_link( $template_id ),
					esc_html__( 'Edit Popup', 'custom-popup-builder' )
				);
			}

		}

		public static function get_instance() {

			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}

}
