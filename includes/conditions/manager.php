<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Conditions_Manager' ) ) {

	class Custom_Popup_Builder_Conditions_Manager {

		private $_conditions         = [];
		private $_matched_conditions = [];
		private $_processed_childs   = [];
		public  $conditions_key      = 'custom_popup_builder_conditions';
		public  $page_origin_data    = [];
		public  $attached_popups     = [];

		public function __construct() {

			$this->register_conditions();

			add_action( 'elementor/frontend/builder_content_data', array( $this, 'get_builder_content_data' ) );

			add_action( 'wp_trash_post', array( $this, 'remove_post_from_site_conditions' ) );

			add_action( 'elementor/editor/footer', array( $this, 'print_vue_templates' ) );
		}

		public function get_popup_id() {
			return get_the_ID();
		}
		
		
		public function update_popup_conditions( $post_id = false, $conditions = [] ) {

			$type = get_post_meta( $post_id, '_elementor_template_type', true );

			$popup_page_settings = get_post_meta( $post_id, '_elementor_page_settings', true );

			$popup_page_settings['custom_popup_builder_conditions'] = $conditions;

			update_post_meta( $post_id, '_elementor_page_settings', $popup_page_settings );

			$saved = get_option( $this->conditions_key, [] );

			if ( ! isset( $saved[ $type ] ) ) {
				$saved[ $type ] = [];
			}

			$saved[ $type ][ $post_id ] = $conditions;

			update_option( $this->conditions_key, $saved, true );
		}

		public function get_popup_conditions( $post_id = false ) {

			$popup_page_settings = get_post_meta( $post_id, '_elementor_page_settings', true );

			if ( isset( $popup_page_settings['custom_popup_builder_conditions'] ) ) {
				return $popup_page_settings['custom_popup_builder_conditions'];
			}

			$post_conditions = $this->get_post_conditions( $post_id );

			$post_conditions = $this->convert_popup_conditions( $post_conditions );

			return $post_conditions;
		}

		public function convert_popup_conditions( $condition = [] ) {

			if ( ! array_key_exists( 'main', $condition ) ) {
				return $condition;
			}

			$new_condition = [];

			$condition_array_keys = array_keys( $condition );
			$sub_group            = isset( $condition_array_keys[1] ) ? $condition_array_keys[1] : false;
			$sub_group_value      = '';

			if ( $sub_group && isset( $sub_group ) ) {
				$sub_group_key = $condition[ $sub_group ];

				$key_value = ! empty( array_keys( $sub_group_key ) ) ? array_keys( $sub_group_key )[0] : false;
				$sub_group_value = $key_value ? $sub_group_key[ $key_value ] : '';
			}

			if ( ! empty( $sub_group_value ) && is_array( $sub_group_value ) ) {

				foreach ( $sub_group_value as $key => $value ) {
					$new_condition[] = [
						'id'            => uniqid( '_' ),
						'include'       => 'true',
						'group'         => $condition['main'],
						'subGroup'      =>  $sub_group ? $sub_group : '',
						'subGroupValue' => $value,
					];
				}
			} else {
				$sub_group_value = ! is_array( $sub_group_value ) ? $sub_group_value : '';

				$new_condition[] = [
					'id'            => uniqid( '_' ),
					'include'       => 'true',
					'group'         => $condition['main'],
					'subGroup'      =>  $sub_group ? $sub_group : '',
					'subGroupValue' => $sub_group_value,
				];
			}

			return $new_condition;
		}

		public function get_site_conditions() {
			return get_option( $this->conditions_key, [] );
		}

		
		public function get_post_conditions( $post_id ) {

			$group      = '';
			$conditions = get_post_meta( $post_id, '_elementor_page_settings', true );
			$sanitized  = array();

			if ( ! $conditions ) {
				$conditions = [];
			}

			foreach ( $conditions as $condition => $value ) {

				if ( false === strpos( $condition, 'conditions_' ) ) {
					continue;
				}

				if ( 'conditions_top' === $condition ) {
					$group             = $value;
					$sanitized['main'] = $group;
					continue;
				}

				if ( 'conditions_sub_' . $group === $condition ) {
					$sanitized[ $value ] = $this->get_condition_args( $value, $conditions );
					continue;
				}
			}

			return $sanitized;
		}

		public function get_condition_args( $cid, $conditions ) {

			$args   = [];
			$prefix = 'conditions_' . $cid . '_';

			foreach ( $conditions as $condition => $value ) {

				if ( false === strpos( $condition, $prefix ) ) {
					continue;
				}

				$args[ str_replace( $prefix, '', $condition ) ] = $value;
			}

			return $args;
		}

		public function remove_post_from_site_conditions( $post_id = 0 ) {

			$conditions = get_option( $this->conditions_key, [] );
			$conditions = $this->remove_post_from_conditions_array( $post_id, $conditions );

			update_option( $this->conditions_key, $conditions, true );
		}

		public function remove_post_from_conditions_array( $post_id = 0, $conditions = array() ) {

			foreach ( $conditions as $type => $type_conditions ) {
				if ( array_key_exists( $post_id, $type_conditions ) ) {
					unset( $conditions[ $type ][ $post_id ] );
				}
			}

			return $conditions;
		}

		public function register_conditions() {

			$base_path = custom_popup_builder()->plugin_path( 'includes/conditions/' );

			require $base_path . 'base.php';

			$default = array(

				// Singular conditions
				'Custom_Popup_Builder_Conditions_Front'                       => $base_path . 'singular-front-page.php',
				'Custom_Popup_Builder_Conditions_Singular_Post_Type'          => $base_path . 'singular-post-type.php',
				'Custom_Popup_Builder_Conditions_Singular_Post'               => $base_path . 'singular-post.php',
				'Custom_Popup_Builder_Conditions_Singular_Post_From_Category' => $base_path . 'singular-post-from-cat.php',
				'Custom_Popup_Builder_Conditions_Singular_Post_From_Tag'      => $base_path . 'singular-post-from-tag.php',
				'Custom_Popup_Builder_Conditions_Singular_Page'               => $base_path . 'singular-page.php',
				'Custom_Popup_Builder_Conditions_Singular_Page_Child'         => $base_path . 'singular-page-child.php',
				'Custom_Popup_Builder_Conditions_Singular_Page_Template'      => $base_path . 'singular-page-template.php',
				'Custom_Popup_Builder_Conditions_Singular_404'                => $base_path . 'singular-404.php',

				// Archive conditions
				'Custom_Popup_Builder_Conditions_Archive_All'                 => $base_path . 'archive-all.php',
				'Custom_Popup_Builder_Conditions_Archive_Post_Type'           => $base_path . 'archive-post-type.php',
				'Custom_Popup_Builder_Conditions_Archive_Category'            => $base_path . 'archive-category.php',
				'Custom_Popup_Builder_Conditions_Archive_Tag'                 => $base_path . 'archive-tag.php',
			);

			foreach ( $default as $class => $file ) {
				require $file;

				$this->register_condition( $class );
			}

		
			do_action( 'custom-popup-builder/conditions/register', $this );

		}

		public function register_condition( $class ) {
			$instance = new $class;
			$this->_conditions[ $instance->get_id() ] = $instance;
		}

		public function get_condition( $condition_id ) {
			return isset( $this->_conditions[ $condition_id ] ) ? $this->_conditions[ $condition_id ] : false;
		}

		public function register_condition_button( $controls_manager ) {

			if ( ! $controls_manager ) {
				return;
			}

			$controls_manager->add_control(
				'custom_popup_builder_conditions_manager',
				[
					'type'        => 'button',
					'text'        => __( 'Display Conditions', 'custom-popup-builder' ),
					'event'       => 'custom-popup-builder-conditions-manager',
					'button_type' => 'default',
				]
			);
		}

		public function esc_options( $data ) {

			$result = array();

			foreach ( $data as $id => $item ) {
				$result[ $id ] = $item['label'];
			}

			return $result;
		}

		public function esc_child_options( $childs ) {

			$result = array();

			foreach ( $childs as $child ) {
				$instance = $this->get_condition( $child );
				$result[ $child ] = $instance->get_label();
			}

			return $result;
		}

		public function find_matched_conditions( $type ) {

			$conditions = get_option( $this->conditions_key, [] );

			if ( empty( $conditions[ $type ] ) ) {

				return false;
			}

			$popup_id_list = [];

			foreach ( $conditions[ $type ] as $popup_id => $popup_conditions ) {

				$popup_conditions = $this->convert_popup_conditions( $popup_conditions );

				if ( empty( $popup_conditions ) ) {
					continue;
				}

				$check_list = [];
				$include_list = [];

				$popup_id = apply_filters( 'custom-popup-builder/get_conditions/template_id', $popup_id );

				foreach ( $popup_conditions as $key => $condition ) {

					$include = filter_var( $condition['include'], FILTER_VALIDATE_BOOLEAN );

					if ( 'entire' === $condition['group'] ) {
						$check_list['entire'] = true;
						$include_list['entire'] = $include;
						continue;
					}

					$sub_group = $condition['subGroup'];
					$instance = $this->get_condition( $sub_group );

					if ( ! $instance ) {
						continue;
					}

					$include_list[ $sub_group ] = $include;

					$sub_group_value = $condition['subGroupValue'];

					$instance_check = call_user_func( array( $instance, 'check' ), $sub_group_value );

					$check = ( $instance_check && $include ) ? true : false;

					if ( ! $include ) {
						if ( array_key_exists( $sub_group, $check_list ) ) {
							$check_list[ $sub_group ] = false;

							continue;
						}
					}

					$check_list[ $sub_group ] = $instance_check;

				}

				foreach ( $check_list as $check_sub_group => $check ) {


					if ( $check ) {

						if ( ! $include_list[ $check_sub_group ] ) {

							$key = array_search( $popup_id, $popup_id_list );

							if ( isset( $key ) ) {
								unset( $popup_id_list[ $key ] );
							}

							continue;
						}

						$popup_id_list[] = $popup_id;
					}
				}
			}

			if ( ! empty( $popup_id_list ) ) {
				return $popup_id_list;
			}

			return false;
		}

		public function post_conditions_verbose( $post_id = null ) {

			$verbose = '';

			$conditions = $this->get_popup_conditions( $post_id );

			if ( empty( $conditions ) ) {
				return false;
			}

			$verbose = '';

			foreach ( $conditions as $key => $condition ) {
				$include         = filter_var( $condition['include'], FILTER_VALIDATE_BOOLEAN );
				$group           = $condition['group'];
				$sub_group       = $condition['subGroup'];
				$sub_group_value = $condition['subGroupValue'];

				if ( 'entire' === $group ) {
					$verbose .= sprintf( '<div class="custom-popup-builder-conditions-list__item"><span>%1$s</span></div>', __( 'Entire Site', 'custom-popup-builder' ) );

					continue;
				}

				$instance = $this->get_condition( $sub_group );

				$item_class = 'custom-popup-builder-conditions-list__item';

				if ( ! $include ) {
					$item_class .= ' exclude';
				}

				if ( ! empty( $sub_group_value ) ) {
					$label = $instance->get_label_by_value( $sub_group_value );
					$verbose .= sprintf( '<div class="%1$s"><span>%2$s: </span><i>%3$s</i></div>', $item_class, $instance->get_label(), $label );
				} else {
					$verbose .= sprintf( '<div class="%1$s"><span>%2$s</span></div>', $item_class,  $instance->get_label() );
				}
			}

			return $verbose;
		}

		public function get_attached_popups() {
			return ( is_array( $this->attached_popups ) && ! empty( $this->attached_popups ) ) ? $this->attached_popups : false;
		}

		public function get_builder_content_data( $origin_data ) {

			$section_list = $origin_data;

			$this->find_widget_popup_attachment( $section_list );

			return $origin_data;
		}

		public function find_widget_popup_attachment( $sections ) {

			if ( ! empty( $sections ) ) {
				foreach ( $sections as $key => $section ) {
					$this->find_attached_popup_in_section( $section );
				}
			}
		}

		public function find_attached_popup_in_section( $section ) {

			if ( empty( $section ) || ! is_array( $section ) ) {
				return false;
			}

			if ( empty( $section['elements'] ) ) {
				return false;
			}

			foreach ( $section['elements'] as $key => $column ) {
				if ( ! empty( $column['elements'] ) ) {
					foreach ( $column['elements'] as $key => $element ) {
						$element_type = $element['elType'];

						if ( 'widget' === $element_type && ! empty( $element['settings']['custom_attached_popup'] ) ) {
							$this->attached_popups[] = $element['settings']['custom_attached_popup'];
						}

						if ( 'section' === $element_type ) {
							$this->find_attached_popup_in_section( $element );
						}

					}
				}
			}

		}

		public function prepare_data_for_localize() {

			$sorted_conditions = [
				'entire' => [
					'label'   => __( 'Entire Site', 'custom-popup-builder' ),
				],
				'singular' => [
					'label'   => __( 'Singular', 'custom-popup-builder' ),
				],
				'archive' => [
					'label'   => __( 'Archive', 'custom-popup-builder' ),
				]
			];

			foreach ( $this->_conditions as $cid => $instance ) {

				$group  = $instance->get_group();

				if ( ! isset( $sorted_conditions[ $group ] ) ) {
					$sorted_conditions[ $group ] = [];
				}

				$current = array(
					'label'   => $instance->get_label(),
					'action'  => $instance->ajax_action(),
					'options' => $instance->get_avaliable_options(),
				);

				$sorted_conditions[ $group ]['sub-groups'][ $cid ] = $current;

			}

			return [
				'popupId'         => get_the_ID(),
				'popupConditions' => $this->get_popup_conditions( $this->get_popup_id() ),
				'conditionsData'  => $sorted_conditions,
			];
		}

		public function print_vue_templates() {

			foreach ( glob( custom_popup_builder()->plugin_path( 'templates/vue-templates/editor/*.php' ) ) as $file ) {
				$name = basename( $file, '.php' );
				ob_start();
				include $file;
				printf( '<script type="x-template" id="tmpl-custom-popup-builder-%1$s">%2$s</script>', $name, ob_get_clean() );
			}

		}

	}
}
