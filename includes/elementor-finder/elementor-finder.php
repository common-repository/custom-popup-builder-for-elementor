<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Elementor_Finder' ) ) {

	class Custom_Elementor_Finder {

		private static $instance = null;

		public function __construct() {
			add_action( 'elementor/finder/categories/init', [ $this, 'add_custom_popup_builder_category' ] );
		}

		public function add_custom_popup_builder_category( $categories_manager ) {

			require custom_popup_builder()->plugin_path( 'includes/elementor-finder/categories/popup-finder-category.php' );

			$categories_manager->add_category( 'custom-popup-builder-finder-category', new Custom_Popup_Builder_Finder_Category() );
		}

		public static function get_instance() {
			
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}
}
