<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Integration' ) ) {

	class Custom_Popup_Builder_Integration {

		private static $instance = null;

		public function __construct() {
			add_action( 'elementor/init', array( $this, 'register_category' ) );

			add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_addons' ), 10 );

			add_action( 'elementor/controls/controls_registered', array( $this, 'add_controls' ), 10 );
		}

		public function register_category() {

			$elements_manager = Elementor\Plugin::instance()->elements_manager;
			$category         = 'custom-popup-builder';

			$elements_manager->add_category(
				$category,
				array(
					'title' => esc_html__( 'Custom Popup', 'custom-popup-builder' ),
					'icon'  => 'font',
				),
				1
			);
		}

		public function register_addons( $widgets_manager ) {

			require custom_popup_builder()->plugin_path( 'includes/base/class-custom-popup-builder-base.php' );

			foreach ( glob( custom_popup_builder()->plugin_path( 'includes/addons/' ) . '*.php' ) as $file ) {
				$this->register_addon( $file, $widgets_manager );
			}

		}

		public function register_addon( $file, $widgets_manager ) {

			$base  = basename( str_replace( '.php', '', $file ) );
			$class = ucwords( str_replace( '-', ' ', $base ) );
			$class = str_replace( ' ', '_', $class );
			$class = sprintf( 'Elementor\%s', $class );

			require $file;

			if ( class_exists( $class ) ) {
				$widgets_manager->register_widget_type( new $class );
			}
		}

		public function add_controls( $controls_manager ) {

			$grouped = array(
				'custom-popup-builder-box-style'       => 'Custom_Popup_Builder_Group_Control_Box_Style',
				'custom-popup-builder-transform-style' => 'Custom_Popup_Builder_Group_Control_Transform_Style',
			);

			foreach ( $grouped as $control_id => $class_name ) {
				if ( $this->include_control( $class_name, true ) ) {
					$controls_manager->add_group_control( $control_id, new $class_name() );
				}
			}

			$controls = array(
				'custom_popup_builder_search' => 'Custom_Popup_Builder_Control_Search',
			);

			foreach ( $controls as $control_id => $class_name ) {
				if ( $this->include_control( $class_name, false ) ) {
					$class_name = 'Elementor\\' . $class_name;
					$controls_manager->register_control( $control_id, new $class_name() );
				}
			}
		}

		public function include_control( $class_name, $grouped = false ) {

			$filename = sprintf(
				'includes/controls/%2$sclass-%1$s.php',
				str_replace( '_', '-', strtolower( $class_name ) ),
				( true === $grouped ? 'groups/' : '' )
			);

			if ( ! file_exists( custom_popup_builder()->plugin_path( $filename ) ) ) {
				return false;
			}

			require custom_popup_builder()->plugin_path( $filename );

			return true;
		}

		public static function get_instance( $shortcodes = array() ) {

			if ( null == self::$instance ) {
				self::$instance = new self( $shortcodes );
			}
			return self::$instance;
		}
	}

}
