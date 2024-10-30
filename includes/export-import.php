<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Custom_Export_Import' ) ) {

	class Custom_Export_Import {

		private static $instance = null;

		public function __construct() {
			add_filter( 'post_row_actions', [ $this, 'add_export_import_in_dashboard' ], 10, 2 );

			add_action( 'admin_action_custom_popup_builder_import_preset', array( $this, 'import_popup_preset' ) );

			add_action( 'admin_action_custom_popup_builder_create_from_preset', array( $this, 'create_predesigned_popup' ) );

			add_action( 'admin_init', [ $this, 'popup_export_preset' ] );

			add_action( 'admin_footer', [ $this, 'admin_popup_forms' ] );

		}

		public function add_export_import_in_dashboard( $actions, \WP_Post $post ) {

			if (
				Elementor\User::is_current_user_can_edit( $post->ID ) &&
				Elementor\Plugin::$instance->db->is_built_with_elementor( $post->ID ) &&
				'custom-popup-builder' === get_post_type( $post->ID )
			) {
				$actions['custom_popup_builder_export'] = sprintf(
					'<a id="custom-popup-builder-export-link" href="%1$s">%2$s</a>',
					$this->get_export_link( $post->ID ),
					__( 'Export Popup', 'custom-popup-builder' )
				);
			}

			return $actions;
		}

		public function popup_export_preset() {
		
			if ( ! isset( $_GET['action'] ) ) {
				return;
			}

			if ( 'custom_popup_builder_export_preset' !== $_GET['action'] && ! isset( $_GET['popup_id'] ) ) {
				return;
			}

			$popup_id = sanitize_key($_GET['popup_id']);

			$this->export_template( $popup_id );
		}

		public function get_export_link( $popup_id ) {
			return add_query_arg(
				[
					'action'   => 'custom_popup_builder_export_preset',
					'popup_id' => $popup_id,
				],
				admin_url( 'admin-ajax.php' )
			);
		}

		public function export_template( $popup_id ) {
			$file_data = $this->prepare_popup_export( $popup_id );

			header( 'Pragma: public' );
			header( 'Expires: 0' );
			header( 'Cache-Control: public' );
			header( 'Content-Description: File Transfer' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Disposition: attachment; filename="'. $file_data['name'] . '"' );
			header( 'Content-Transfer-Encoding: binary' );

			session_write_close();

			echo $file_data['content'];

			die();
		}

		public function prepare_popup_export( $popup_id ) {

			$db = Elementor\Plugin::$instance->db;

			$content = $db->get_builder( $popup_id );

			$popup_data = [];

			$popup_data['content'] = $content;

			if ( get_post_meta( $popup_id, '_elementor_page_settings', true ) ) {

				$page_settings = get_post_meta( $popup_id, '_elementor_page_settings', true );

				if ( ! empty( $page_settings ) ) {
					$popup_data['page_settings'] = $page_settings;
				}
			}

			$export_data = [
				'version' => Elementor\DB::DB_VERSION,
				'title'   => get_the_title( $popup_id ),
			];

			$export_data += $popup_data;

			return [
				'name'    => 'custom-popup-builder-' . $popup_id . '-' . date( 'Y-m-d' ) . '.json',
				'content' => wp_json_encode( $export_data ),
			];
		}

		public function import_popup_preset() {

			if ( ! current_user_can( 'import' ) ) {
				wp_die( __( 'You don\'t have permissions to do this', 'custom-popup-builder' ) );
			}

			if ( empty( $_FILES ) ) {
				wp_die( __( 'File not passed', 'custom-popup-builder' ) );
			}

			$file = sanitize_file_name($_FILES['file']);

			if ( 'application/json' !== $file['type'] ) {
				wp_die( __( 'Format not allowed', 'custom-popup-builder' ) );
			}

			$content = file_get_contents( $file['tmp_name'] );
			$content = json_decode( $content, true );

			if ( ! $content ) {
				wp_die( __( 'No data found in file', 'custom-popup-builder' ) );
			}

			$documents = Elementor\Plugin::instance()->documents;
			$doc_type  = $documents->get_document_type( custom_popup_builder()->post_type->slug() );

			$popup_content = $content['content'];

			$popup_content = $this->process_export_import_content( $popup_content, 'on_import' );

			$popup_settings = $content['page_settings'];

			$popup_settings = $this->reset_popup_conditions( $popup_settings );

			$post_data = array(
				'post_type'  => custom_popup_builder()->post_type->slug(),
				'meta_input' => array(
					'_elementor_edit_mode'     => 'builder',
					$doc_type::TYPE_META_KEY   => custom_popup_builder()->post_type->slug(),
					'_elementor_data'          => wp_slash( json_encode( $popup_content ) ),
					'_elementor_page_settings' =>  $popup_settings,
				),
			);

			$post_data['post_title'] = ! empty( $content['title'] ) ? $content['title'] : __( 'New Popup', 'custom-popup-builder' );

			$popup_id = wp_insert_post( $post_data );

			custom_popup_builder()->conditions->update_site_conditions( $popup_id );

			if ( ! $popup_id ) {
				wp_die(
					esc_html__( 'Can\'t create popup. Please try again', 'custom-popup-builder' ),
					esc_html__( 'Error', 'custom-popup-builder' )
				);
			}

			wp_redirect( Elementor\Utils::get_edit_link( $popup_id ) );
			die();
		}

		public function create_predesigned_popup() {

			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_die(
					esc_html__( 'You don\'t have permissions to do this', 'custom-popup-builder' ),
					esc_html__( 'Error', 'custom-popup-builder' )
				);
			}
		    $popup = sanitize_key($_REQUEST['popup']);
			$popup  = isset( $popup ) ? esc_attr_e( $popup ) : false;
			
			$documents = Elementor\Plugin::instance()->documents;
			$doc_type  = $documents->get_document_type( custom_popup_builder()->post_type->slug() );
			$popups = $this->predesigned_popups();
			
			if ( ! isset( $popups[ $popup ] ) ) {
				wp_die(
					esc_html__( 'This template not rigestered', 'custom-popup-builder' ),
					esc_html__( 'Error', 'custom-popup-builder' )
				);
			}

			$data    = $popups[ $popup ];
			$content = $data['content'];

			ob_start();
			include $content;
			$preset_data = ob_get_clean();

			$preset_data = json_decode( $preset_data, true );
			
			$this->create_new_popup_data( $preset_data );

		}

		public function create_new_popup_data( $preset_data ) {

			$documents = Elementor\Plugin::instance()->documents;
			$doc_type  = $documents->get_document_type( custom_popup_builder()->post_type->slug() );

			$popup_content = $preset_data['content'];
			$popup_settings = $preset_data['page_settings'];

			$popup_content = $this->process_export_import_content( $popup_content, 'on_import' );

			$popup_settings = $this->reset_popup_conditions( $popup_settings );
			
			$post_data = [
				'post_title' => ! empty( $preset_data['title'] ) ? $preset_data['title'] : __( 'New Popup', 'custom-popup-builder' ),
				'post_type'  => custom_popup_builder()->post_type->slug(),
				'meta_input' => [
					'_elementor_edit_mode'     => 'builder',
					$doc_type::TYPE_META_KEY   => custom_popup_builder()->post_type->slug(),
					'_elementor_data'          => wp_slash( json_encode( $popup_content ) ),
					'_elementor_page_settings' => $popup_settings,
				],
			];
			
			$popup_id = wp_insert_post( $post_data );

			if ( ! $popup_id ) {
				wp_die(
					esc_html__( 'Can\'t create preset. Please try again', 'custom-popup-builder' ),
					esc_html__( 'Error', 'custom-popup-builder' )
				);
			}
			
			wp_redirect( Elementor\Utils::get_edit_link( $popup_id ) );

			exit();
		}

		protected function process_export_import_content( $content, $method ) {
			return ELementor\Plugin::$instance->db->iterate_data(
				$content, function( $element_data ) use ( $method ) {
					$element = ELementor\Plugin::$instance->elements_manager->create_element_instance( $element_data );

					if ( ! $element ) {
						return null;
					}

					return $this->process_element_export_import_content( $element, $method );
				}
			);
		}

		protected function process_element_export_import_content( $element, $method ) {

			$element_data = $element->get_data();

			if ( method_exists( $element, $method ) ) {
				$element_data = $element->{$method}( $element_data );
			}

			foreach ( $element->get_controls() as $control ) {
				$control_class = ELementor\Plugin::$instance->controls_manager->get_control( $control['type'] );

				if ( ! $control_class ) {
					return $element_data;
				}

				if ( method_exists( $control_class, $method ) ) {
					$element_data['settings'][ $control['name'] ] = $control_class->{$method}( $element->get_settings( $control['name'] ), $control );
				}
			}

			return $element_data;
		}

		public function reset_popup_conditions( $popup_settings ) {

			foreach ( $popup_settings as $condition => $value ) {

				if ( false !== strpos( $condition, 'conditions_' ) ) {
					unset( $popup_settings[ $condition ] );
				}
			}

			return $popup_settings;
		}

		public function predesigned_popups() {

			$base_url = custom_popup_builder()->plugin_url( 'templates/dummy-popups/' );
			$base_dir = custom_popup_builder()->plugin_path( 'templates/dummy-popups/' );

			return apply_filters( 'custom-popup-builder/predesigned-popups', [
				'popup-1' => [
					'title'    => __( 'Classic', 'custom-popup-builder' ),
					'content'  => $base_dir . 'popup-1/preset.json',
					'thumb'    => $base_url . 'popup-1/thumbnail.png',
				],
				'popup-2' => [
					'title'    => __( 'Slide In', 'custom-popup-builder' ),
					'content'  => $base_dir . 'popup-2/preset.json',
					'thumb'    => $base_url . 'popup-2/thumbnail.png',
				],
				'popup-3' => [
					'title'   => __( 'Bar', 'custom-popup-builder' ),
					'content' => $base_dir . 'popup-3/preset.json',
					'thumb'   => $base_url . 'popup-3/thumbnail.png',
				],
				'popup-4' => [
					'title'   => __( 'Bordering', 'custom-popup-builder' ),
					'content' => $base_dir . 'popup-4/preset.json',
					'thumb'   => $base_url . 'popup-4/thumbnail.png',
				],
				'popup-5' => [
					'title'   => __( 'Full View', 'custom-popup-builder' ),
					'content' => $base_dir . 'popup-5/preset.json',
					'thumb'   => $base_url . 'popup-5/thumbnail.png',
				],
				'popup-6' => [
					'title'   => __( 'Full Width', 'custom-popup-builder' ),
					'content' => $base_dir . 'popup-6/preset.json',
					'thumb'   => $base_url . 'popup-6/thumbnail.png',
				],
			] );
		}

		public function admin_popup_forms() {

			global $current_screen;

			if ( 'edit-custom-popup-builder' !== $current_screen->id ) {
				return false;
			}

			$import_action = add_query_arg(
				array(
					'action' => 'custom_popup_builder_import_preset',
				),
				esc_url( admin_url( 'admin.php' ) )
			);

			$crate_action = add_query_arg(
				array(
					'action' => 'custom_popup_builder_create_from_preset',
				),
				esc_url( admin_url( 'admin.php' ) )
			);
			$checked = ' checked';
			?>
			<div id="custom-popup-builder-hidden-area">
				<a id="custom-popup-builder-import-trigger" href="#" class="page-title-action"><?php echo __( 'Import Popup', 'custom-popup-builder' ); ?></a>
				<form id="custom-popup-builder-import-form" method="post" action="<?php echo $import_action ?>" enctype="multipart/form-data">
					<fieldset id="custom-popup-builder-import-form-inputs">
						<input type="file" class="file-button" name="file" accept=".json,application/json,.zip,application/octet-stream,application/zip,application/x-zip,application/x-zip-compressed" required>
						<input type="submit" class="button button-primary" value="<?php echo esc_attr_e__( 'Import Now', 'custom-popup-builder' ); ?>">
					</fieldset>
				</form>
				<a id="custom-popup-builder-create-trigger" href="#" class="page-title-action"><?php echo __( 'Popup Layout', 'custom-popup-builder' ); ?></a>
				<form  id="custom-popup-builder-create-form" class="custom-popup-builder-create-form" method="POST" action="<?php echo $crate_action; ?>" >
					<h3 class="custom-popup-builder-create-form__title"><?php
						esc_html_e( 'Select Layout Preset', 'custom-popup-builder' );
					?></h3>
					<div class="custom-popup-builder-create-form__item-list"><?php
						foreach ( $this->predesigned_popups() as $id => $data ) {
							?>
							<div class="custom-popup-builder-create-form__item">
								<div class="custom-popup-builder-create-form__item-inner">
									<label class="custom-popup-builder-create-form__label">
										<input type="radio" name="popup" value="<?php echo $id; ?>"<?php echo $checked; ?>>
										<span class="custom-popup-builder-create-form__check dashicons dashicons-yes"></span>
										<img src="<?php echo $data['thumb']; ?>" alt="">
									</label>
									<h4 class="custom-popup-builder-create-form__item-title"><?php
										 echo $data['title'];
									?></h4>
								</div>
							</div>
							<?php
							$checked = '';
						}
					?></div>
					<div class="custom-popup-builder-create-form__actions">
						<button type="submit" id="custom_popup_builder_type_submit" class="button button-primary"><?php
							esc_html_e( 'Create Popup', 'custom-popup-builder' );
						?></button>
					</div>
				</form>
			</div><?php
		}

		public static function get_instance() {
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}

}
