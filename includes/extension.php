<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Element_Extensions' ) ) {

	class Custom_Popup_Builder_Element_Extensions {

		public $widgets_data = array();

		public $default_widget_settings = [
			'custom_attached_popup'          => '',
			'custom_trigger_type'            => 'click',
			'custom_trigger_custom_selector' => '',
		];

		public $avaliable_widgets = [
			'heading'           => '.elementor-heading-title',
			'button'            => '.elementor-button-link',
			'icon'              => '.elementor-image',
			'image'             => 'img',
			'animated-headline' => '.elementor-headline',
			'flip-box'          => '.elementor-flip-box__button',
			'call-to-action'    => '.elementor-cta__button',
		];

		private static $instance = null;

		public function __construct() {

			add_action( 'elementor/element/common/_section_style/after_section_end', array( $this, 'widget_extensions' ), 10, 2 );

			add_action( 'elementor/frontend/widget/before_render', array( $this, 'widget_before_render' ) );

			add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'enqueue_scripts' ), 9 );
		}

		public function widget_extensions( $obj, $args ) {

			$avaliable_popups = Custom_Popup_Builder_Utils::get_avaliable_popups();

			$obj->start_controls_section(
				'widget_custom_popup_builder',
				[
					'label' => esc_html__( 'Custom Popup', 'custom-popup-builder' ),
					'tab'   => Elementor\Controls_Manager::TAB_ADVANCED,
				]
			);

			if ( empty( $avaliable_popups ) ) {

				$obj->add_control(
					'no_avaliable_popup',
					[
						'label' => false,
						'type'  => Elementor\Controls_Manager::RAW_HTML,
						'raw'   => $this->empty_templates_message(),
					]
				);

				$obj->end_controls_section();

				return;
			}

			$obj->add_control(
				'custom_attached_popup',
				[
					'label'   => __( 'Attached Popup', 'custom-popup-builder' ),
					'type'    => Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => $avaliable_popups,
				]
			);

			$obj->add_control(
				'custom_trigger_type',
				[
					'label'   => __( 'Trigger Type', 'custom-popup-builder' ),
					'type'    => Elementor\Controls_Manager::SELECT,
					'default' => 'click-self',
					'options' => [
						'none'           => __( 'None', 'custom-popup-builder' ),
						'click'          => __( 'Click On Button', 'custom-popup-builder' ),
						'click-self'     => __( 'Click On Widget', 'custom-popup-builder' ),
						'click-selector' => __( 'Click On Custom Selector', 'custom-popup-builder' ),
						'hover'          => __( 'Hover', 'custom-popup-builder' ),
						'scroll-to'      => __( 'Scroll To Widget', 'custom-popup-builder' ),
					],
				]
			);

			$obj->add_control(
				'custom_trigger_custom_selector',
				[
					'label'       => __( 'Custom Selector', 'custom-popup-builder' ),
					'type'        => Elementor\Controls_Manager::TEXT,
					'default'     => '',
					'placeholder' => __( 'Custom Selector', 'custom-popup-builder' ),
					'condition'   => [
						'custom_trigger_type' => 'click-selector',
					]
				]
			);

			$obj->end_controls_section();
		}

		public function widget_before_render( $widget ) {
			$data     = $widget->get_data();
			$settings = $data['settings'];

			$settings = wp_parse_args( $settings, $this->default_widget_settings );

			$widget_settings = array();

			if ( ! empty( $settings['custom_attached_popup'] ) ) {
				$widget_settings['attached-popup']          = 'custom-popup-builder-' . $settings['custom_attached_popup'];
				$widget_settings['trigger-type']            = $settings['custom_trigger_type'];
				$widget_settings['trigger-custom-selector'] = $settings['custom_trigger_custom_selector'];

				$widget->add_render_attribute( '_wrapper', array(
					'class' => 'custom-popup-builder-target',
				) );
			}

			if ( ! empty( $widget_settings ) ) {
				$this->widgets_data[ $data['id'] ] = $widget_settings;
			}
		}

	
		public function empty_templates_message() {
			return '<div id="elementor-widget-template-empty-templates">
				<div class="elementor-widget-template-empty-templates-title">' . esc_html__( 'You Haven’t Created Popup Yet.', 'custom-popup-builder' ) . '</div>
			</div>';
		}

		
		public function enqueue_scripts() {
			custom_popup_builder()->assets->localize_data['elements_data']['widgets'] = $this->widgets_data;
		}

		public static function get_instance() {
		
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}
}
