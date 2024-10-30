<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Conditions_Base' ) ) {

	abstract class Custom_Popup_Builder_Conditions_Base {

		abstract public function get_id();

		abstract public function get_label();

		abstract public function get_group();

		abstract public function check( $args );

		public function get_childs() {
			return array();
		}

		public function get_controls() {
			return array();
		}

		public function get_avaliable_options() {
			return false;
		}

		public function ajax_action() {
			return false;
		}

		public function verbose_args( $args ) {
			return '';
		}

		public function get_label_by_value( $value ) {
			return '';
		}


	}

}
