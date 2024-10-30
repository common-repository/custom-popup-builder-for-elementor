<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Conditions_Archive_All' ) ) {

	class Custom_Popup_Builder_Conditions_Archive_All extends Custom_Popup_Builder_Conditions_Base {

		public function get_id() {
			return 'archive-all';
		}

		public function get_label() {
			return __( 'All Archives', 'custom-popup-builder' );
		}

		public function get_group() {
			return 'archive';
		}

		public function check( $args ) {
			return is_archive();
		}

	}

}
