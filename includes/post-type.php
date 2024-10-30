<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Post_Type' ) ) {

	class Custom_Popup_Builder_Post_Type {

		private static $instance = null;

		protected $post_type = 'custom-popup-builder';

		protected $meta_key = 'custom-popup-builder-item';

		private $conditions = array();

		public function __construct() {

			self::register_post_type();

			add_filter( 'option_elementor_cpt_support', [ $this, 'set_option_support' ] );

			add_filter( 'default_option_elementor_cpt_support', [ $this, 'set_option_support' ] );

			add_action( 'elementor/documents/register', [ $this, 'register_elementor_document_type' ] );

			add_action( 'wp_insert_post', [ $this, 'set_document_type_on_post_create' ], 10, 2 );

			add_action( 'template_include', [ $this, 'set_post_type_template' ], 9999 );

			add_filter( 'manage_' . $this->slug() . '_posts_columns', [ $this, 'set_post_columns' ] );

			add_action( 'manage_' . $this->slug() . '_posts_custom_column', [ $this, 'post_columns' ], 10, 2 );

			add_action( 'admin_footer', [ $this, 'render_vue_template' ] );

			if ( is_admin() ) {
				add_action( 'admin_menu', [ $this, 'add_popup_sub_page' ], 90 );
			}

		}

		public function set_post_columns( $columns ) {

			unset( $columns['date'] );
			$columns['conditions'] = __( 'Active Conditions', 'custom-popup-builder' );
			$columns['date']       = __( 'Date', 'custom-popup-builder' );

			return $columns;

		}

		public function post_columns( $column, $post_id ) {

			$all_conditions = custom_popup_builder()->conditions->get_site_conditions();

			switch ( $column ) {

				case 'conditions':

					echo '<div class="custom-popup-builder-conditions">';

					if ( isset( $all_conditions[ 'custom-popup-builder' ] ) ) {

						if ( ! empty( $all_conditions[ 'custom-popup-builder' ][ $post_id ] ) ) {

							printf(
								'<div class="custom-popup-builder-conditions-list">%1$s</div>',
								custom_popup_builder()->conditions->post_conditions_verbose( $post_id )
							);
						} else {
							printf(
								'<div class="custom-popup-builder-conditions-undefined"><span class="dashicons dashicons-warning"></span>%1$s</div>',
								__( 'Conditions not selected', 'custom-popup-builder' )
							);

						}
					} else {
						printf(
							'<div class="custom-popup-builder-conditions-undefined"><span class="dashicons dashicons-warning"></span>%1$s</div>',
							__( 'Conditions not selected', 'custom-popup-builder' )
						);
					}

					echo '</div>';

					break;

			}

		}

		public function set_document_type_on_post_create( $post_id, $post ) {

			if ( $post->post_type !== $this->slug() ) {
				return;
			}

			if ( ! class_exists( 'Elementor\Plugin' ) ) {
				return;
			}

			$documents = Elementor\Plugin::instance()->documents;
			$doc_type  = $documents->get_document_type( $this->slug() );

			update_post_meta( $post_id, $doc_type::TYPE_META_KEY, $this->slug() );

		}
		public function register_elementor_document_type( $documents_manager ) {
			require custom_popup_builder()->plugin_path( 'includes/document.php' );

			$documents_manager->register_document_type( $this->slug(), 'Custom_Popup_Builder_Document' );
		}

		public function slug() {
			return $this->post_type;
		}

		public function meta_key() {
			return $this->meta_key;
		}

		public function set_option_support( $value ) {

			if ( empty( $value ) ) {
				$value = array();
			}

			return array_merge( $value, array( $this->slug() ) );
		}

		static public function register_post_type() {

			$labels = array(
				'name'          => esc_html__( 'CustomPopup', 'custom-popup-builder' ),
				'singular_name' => esc_html__( 'Custom Popup', 'custom-popup-builder' ),
				'all_items'     => esc_html__( 'All Popups', 'custom-popup-builder' ),
				'add_new'       => esc_html__( 'Add New Popup', 'custom-popup-builder' ),
				'add_new_item'  => esc_html__( 'Add New Popup', 'custom-popup-builder' ),
				'edit_item'     => esc_html__( 'Edit Popup', 'custom-popup-builder' ),
				'menu_name'     => esc_html__( 'CustomPopup', 'custom-popup-builder' ),
			);

			$supports = apply_filters( 'custom-popup-builder/post-type/register/supports', [ 'title' ] );

			$args = array(
				'labels'              => $labels,
				'hierarchical'        => false,
				'description'         => 'description',
				'taxonomies'          => [],
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => null,
				'menu_icon'           => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgMjAgMjAiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDIwIDIwOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+PHBhdGggZD0iTTE5LDdoLTNWNVYxYzAtMC42LTAuNC0xLTEtMUgxQzAuNCwwLDAsMC40LDAsMXY0djE0YzAsMC42LDAuNCwxLDEsMWgxNGMwLjYsMCwxLTAuNCwxLTF2LTNoM2MwLjYsMCwxLTAuNCwxLTFWOEMyMCw3LjQsMTkuNiw3LDE5LDd6IE02LjUsMkM2LjgsMiw3LDIuMiw3LDIuNVM2LjgsMyw2LjUsM1M2LDIuOCw2LDIuNVM2LjIsMiw2LjUsMnogTTQuNSwyQzQuOCwyLDUsMi4yLDUsMi41UzQuOCwzLDQuNSwzUzQsMi44LDQsMi41UzQuMiwyLDQuNSwyeiBNMi41LDJDMi44LDIsMywyLjIsMywyLjVTMi44LDMsMi41LDNTMiwyLjgsMiwyLjVTMi4yLDIsMi41LDJ6IE0xNCw3SDVDNC40LDcsNCw3LjQsNCw4djdjMCwwLjYsMC40LDEsMSwxaDl2MkgyVjVoMTJWN3ogTTE5LDloLTF2MWgxdjFoLTF2LTFoLTF2MWgtMXYtMWgxVjloLTFWOGgxdjFoMVY4aDFWOXoiLz48L3N2Zz4=',
				'show_in_nav_menus'   => false,
				'publicly_queryable'  => true,
				'exclude_from_search' => true,
				'has_archive'         => false,
				'query_var'           => true,
				'can_export'          => true,
				'rewrite'             => true,
				'capability_type'     => 'post',
				'supports'            => $supports,
			);

			register_post_type( 'custom-popup-builder', $args );

		}

		public function set_post_type_template( $template ) {

			if ( is_singular( $this->slug() ) ) {

				$template = custom_popup_builder()->plugin_path( 'templates/single.php' );

				if ( custom_popup_builder()->elementor()->preview->is_preview_mode() ) {
					$template = custom_popup_builder()->plugin_path( 'templates/editor.php' );
				}

				do_action( 'custom-popup-builder/template-include/found' );

				return $template;
			}

			return $template;
		}

		public function library_page_render() {
			$crate_action = add_query_arg(
				array(
					'action' => 'custom_popup_builder_create_from_preset',
				),
				esc_url( admin_url( 'admin.php' ) )
			);

			require custom_popup_builder()->plugin_path( 'templates/vue-templates/preset-page.php' );
		}

		public function render_vue_template() {

			$vue_templates = [
				'preset-library',
				'preset-filters',
				'preset-list',
				'preset-item',
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

		public function get_library_page_url() {
			return admin_url( 'edit.php?post_type=' . $this->slug() . '&page=' . $this->slug() . '-library' );
		}

		public static function get_instance() {

			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}

}
