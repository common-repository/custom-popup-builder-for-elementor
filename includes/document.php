<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Custom_Popup_Builder_Document extends Elementor\Core\Base\Document {

	public function get_name() {
		return 'custom-popup-builder';
	}

	public static function get_title() {
		return __( 'Custom Popup', 'custom-popup-builder' );
	}

	protected function _register_controls() {

		parent::_register_controls();

		$uniq_popup_id = '#' . $this->get_unique_name();

		$roles = Custom_Popup_Builder_Utils::get_roles_list();

		$this->start_controls_section(
			'custom_popup_builder_settings',
			[
				'label' => __( 'Settings', 'custom-popup-builder' ),
				'tab'   => Elementor\Controls_Manager::TAB_SETTINGS,
			]
		);

		$this->add_control(
			'custom_popup_builder_animation',
			[
				'label'   => __( 'Animation', 'custom-popup-builder' ),
				'type'    => Elementor\Controls_Manager::SELECT,
				'default' => 'fade',
				'options' => [
					'fade'           => esc_html__( 'Fade', 'custom-popup-builder' ),
					'zoom-in'        => esc_html__( 'ZoomIn', 'custom-popup-builder' ),
					'zoom-out'       => esc_html__( 'ZoomOut', 'custom-popup-builder' ),
					'rotate'         => esc_html__( 'Rotate', 'custom-popup-builder' ),
					'move-up'        => esc_html__( 'MoveUp', 'custom-popup-builder' ),
					'flip-x'         => esc_html__( 'Horizontal Flip', 'custom-popup-builder' ),
					'flip-y'         => esc_html__( 'Vertical Flip', 'custom-popup-builder' ),
					'bounce-in'      => esc_html__( 'BounceIn', 'custom-popup-builder' ),
					'bounce-out'     => esc_html__( 'BounceOut', 'custom-popup-builder' ),
					'slide-in-up'    => esc_html__( 'SlideInUp', 'custom-popup-builder' ),
					'slide-in-right' => esc_html__( 'SlideInRight', 'custom-popup-builder' ),
					'slide-in-down'  => esc_html__( 'SlideInDown', 'custom-popup-builder' ),
					'slide-in-left'  => esc_html__( 'SlideInLeft', 'custom-popup-builder' ),
				],
			]
		);

		$this->add_control(
			'custom_popup_builder_open_trigger',
			[
				'label'   => __( 'Open event', 'custom-popup-builder' ),
				'type'    => Elementor\Controls_Manager::SELECT,
				'default' => 'attach',
				'options' => [
					'attach'           => esc_html__( 'Not Selected', 'custom-popup-builder' ),
					'page-load'        => esc_html__( 'On page load(s)', 'custom-popup-builder' ),
					'user-inactive'    => esc_html__( 'Inactivity time after(s)', 'custom-popup-builder' ),
					'scroll-trigger'   => esc_html__( 'Page Scrolled(%)', 'custom-popup-builder' ),
					'try-exit-trigger' => esc_html__( 'Try exit', 'custom-popup-builder' ),
					'on-date'          => esc_html__( 'On Date', 'custom-popup-builder' ),
					'custom-selector'  => esc_html__( 'Custom Selector Click', 'custom-popup-builder' ),
				],
			]
		);

		$this->add_control(
			'custom_popup_builder_page_load_delay',
			[
				'label'       => esc_html__( 'Open delay', 'custom-popup-builder' ),
				'type'        => Elementor\Controls_Manager::NUMBER,
				'default'     => 1,
				'min'         => 0,
				'max'         => 60,
				'condition'   => [
					'custom_popup_builder_open_trigger' => 'page-load',
				]
			]
		);

		$this->add_control(
			'custom_popup_builder_user_inactivity_time',
			[
				'label'       => esc_html__( 'User inactivity time', 'custom-popup-builder' ),
				'type'        => Elementor\Controls_Manager::NUMBER,
				'default'     => 1,
				'min'         => 0,
				'max'         => 60,
				'condition'   => [
					'custom_popup_builder_open_trigger' => 'user-inactive',
				]
			]
		);

		$this->add_control(
			'custom_popup_builder_scrolled_to_value',
			[
				'label'       => esc_html__( 'Scroll Page Progress(%)', 'custom-popup-builder' ),
				'type'        => Elementor\Controls_Manager::NUMBER,
				'default'     => 10,
				'min'         => 0,
				'max'         => 100,
				'condition'   => [
					'custom_popup_builder_open_trigger' => 'scroll-trigger',
				]
			]
		);

		$this->add_control(
			'custom_popup_builder_on_date_value',
			[
				'label'       => esc_html__( 'Open Date', 'custom-popup-builder' ),
				'type'        => Elementor\Controls_Manager::DATE_TIME,
				'default'     => date( 'Y-m-d H:i', strtotime( '+1 month' ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ),
				'picker_options' => [
					'enableTime' => true,
				],
				'condition'   => [
					'custom_popup_builder_open_trigger' => 'on-date',
				],
				'description' => sprintf( __( 'Date set according to your timezone: %s.', 'custom-popup-builder' ), Elementor\Utils::get_timezone_string() ),
			]
		);

		$this->add_control(
			'custom_popup_builder_custom_selector',
			array(
				'label'       => esc_html__( 'Custom Selector', 'custom-popup-builder' ),
				'type'        => Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Custom Selector', 'custom-popup-builder' ),
				'default'     => '.custom',
				'condition'   => array(
					'custom_popup_builder_open_trigger' => 'custom-selector',
				),
			)
		);

		$this->add_control(
			'custom_popup_builder_show_once',
			[
				'label'        => esc_html__( 'Show once', 'custom-popup-builder' ),
				'type'         => Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'custom-popup-builder' ),
				'label_off'    => esc_html__( 'No', 'custom-popup-builder' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->add_control(
			'custom_popup_builder_show_again_delay',
			[
				'label'   => __( 'Show Again Delay', 'custom-popup-builder' ),
				'type'    => Elementor\Controls_Manager::SELECT,
				'default' => 'none',
				'options' => [
					'none'      => esc_html__( 'None', 'custom-popup-builder' ),
					'minute'    => esc_html__( 'Minute', 'custom-popup-builder' ),
					'10minutes' => esc_html__( '10 Minutes', 'custom-popup-builder' ),
					'30minutes' => esc_html__( '30 Minutes', 'custom-popup-builder' ),
					'hour'      => esc_html__( '1 Hour', 'custom-popup-builder' ),
					'3hours'    => esc_html__( '3 Hours', 'custom-popup-builder' ),
					'6hours'    => esc_html__( '6 Hours', 'custom-popup-builder' ),
					'12hours'   => esc_html__( '12 Hours', 'custom-popup-builder' ),
					'day'       => esc_html__( 'Day', 'custom-popup-builder' ),
					'3days'     => esc_html__( '3 Days', 'custom-popup-builder' ),
					'week'      => esc_html__( 'Week', 'custom-popup-builder' ),
					'month'     => esc_html__( 'Month', 'custom-popup-builder' ),
				],
				'condition'   => array(
					'custom_popup_builder_show_once' => 'yes',
				),
			]
		);

		$this->add_control(
			'custom_popup_builder_use_ajax',
			[
				'label'        => esc_html__( 'Loading content with Ajax', 'custom-popup-builder' ),
				'type'         => Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'custom-popup-builder' ),
				'label_off'    => esc_html__( 'No', 'custom-popup-builder' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->add_control(
			'custom_popup_builder_force_ajax',
			[
				'label'        => esc_html__( 'Force Loading', 'custom-popup-builder' ),
				'description'        => esc_html__( 'Force Loading every time you open the popup', 'custom-popup-builder' ),
				'type'         => Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'custom-popup-builder' ),
				'label_off'    => esc_html__( 'No', 'custom-popup-builder' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'   => array(
					'custom_popup_builder_use_ajax' => 'yes',
				),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'custom_popup_builder_conditions',
			[
				'label' => __( 'Display Settings', 'custom-popup-builder' ),
				'tab'   => Elementor\Controls_Manager::TAB_SETTINGS,
			]
		);

		custom_popup_builder()->conditions->register_condition_button( $this );

		if ( ! empty( $roles ) ) {
			$this->add_control(
				'custom_role_condition',
				[
					'label'     => __( 'Available For Roles', 'custom-popup-builder' ),
					'type'      => Elementor\Controls_Manager::SELECT2,
					'multiple'  => true,
					'options'   => $roles,
					'separator' => 'before',
				]
			);
		}

		$this->end_controls_section();

		$this->start_controls_section(
			'custom_popup_builder_general_style',
			[
				'label' => __( 'General Styles', 'custom-popup-builder' ),
				'tab'   => Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'custom_popup_builder_z_index',
			[
				'label'       => esc_html__( 'Z-Index', 'custom-popup-builder' ),
				'type'        => Elementor\Controls_Manager::NUMBER,
				'min'         => -1,
				'max'         => 50000,
				'selectors' => [
					$uniq_popup_id => 'z-index: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'custom_popup_builder_container_style',
			[
				'label' => __( 'Popup Container', 'custom-popup-builder' ),
				'tab'   => Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'positions_size',
			array(
				'label' => esc_html__( 'Size', 'custom-popup-builder' ),
				'type'  => Elementor\Controls_Manager::HEADING,
			)
		);

		$this->add_responsive_control(
			'container_width',
			[
				'label' => esc_html__( 'Width', 'custom-popup-builder' ),
				'type'  => Elementor\Controls_Manager::SLIDER,
				'size_units' => [
					'px', '%'
				],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 2000,
					],
					'%' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 500,
					'unit' => 'px',
				],
				'selectors' => [
					$uniq_popup_id . ' .custom-popup-builder__container' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'container_custom_height',
			[
				'label'        => esc_html__( 'Custom Height', 'custom-popup-builder' ),
				'type'         => Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'custom-popup-builder' ),
				'label_off'    => esc_html__( 'No', 'custom-popup-builder' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->add_responsive_control(
			'container_height',
			[
				'label' => esc_html__( 'Height', 'custom-popup-builder' ),
				'type'  => Elementor\Controls_Manager::SLIDER,
				'size_units' => [
					'px', '%'
				],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 1000,
					],
					'%' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 500,
					'unit' => 'px',
				],
				'selectors' => [
					$uniq_popup_id . ' .custom-popup-builder__container' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'container_custom_height' => 'yes',
				],
			]
		);

		$this->add_control(
			'position_heading',
			array(
				'label' => esc_html__( 'Position', 'custom-popup-builder' ),
				'type'  => Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'custom_popup_builder_horizontal_position',
			[
				'label'       => __( 'Horizontal Position', 'custom-popup-builder' ),
				'type'        => Elementor\Controls_Manager::CHOOSE,
				'label_block' => false,
				'default'     => 'center',
				'options' => [
					'flex-start' => [
						'title' => __( 'Left', 'custom-popup-builder' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'custom-popup-builder' ),
						'icon' => 'eicon-h-align-center',
					],
					'flex-end' => [
						'title' => __( 'Right', 'custom-popup-builder' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'selectors'  => [
					$uniq_popup_id . ' .custom-popup-builder__inner' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'custom_popup_builder_vertical_position',
			[
				'label'       => __( 'Vertical Position', 'custom-popup-builder' ),
				'type'        => Elementor\Controls_Manager::CHOOSE,
				'label_block' => false,
				'default'     => 'center',
				'options' => [
					'flex-start' => [
						'title' => __( 'Top', 'custom-popup-builder' ),
						'icon' => 'eicon-v-align-top',
					],
					'center' => [
						'title' => __( 'Middle', 'custom-popup-builder' ),
						'icon' => 'eicon-v-align-middle',
					],
					'flex-end' => [
						'title' => __( 'Bottom', 'custom-popup-builder' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'selectors'  => [
					$uniq_popup_id . ' .custom-popup-builder__inner' => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'custom_popup_builder_content_position',
			[
				'label'       => __( 'Content Position', 'custom-popup-builder' ),
				'type'        => Elementor\Controls_Manager::CHOOSE,
				'label_block' => false,
				'default'     => 'flex-start',
				'options'     => [
					'flex-start' => [
						'title' => __( 'Top', 'custom-popup-builder' ),
						'icon' => 'eicon-v-align-top',
					],
					'center' => [
						'title' => __( 'Middle', 'custom-popup-builder' ),
						'icon' => 'eicon-v-align-middle',
					],
					'flex-end' => [
						'title' => __( 'Bottom', 'custom-popup-builder' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'selectors'  => [
					$uniq_popup_id . ' .custom-popup-builder__container-inner' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'container_background_heading',
			array(
				'label' => esc_html__( 'Container Background', 'custom-popup-builder' ),
				'type'  => Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Elementor\Group_Control_Background::get_type(),
			[
				'name'     => 'container_background',
				'selector' => $uniq_popup_id . ' .custom-popup-builder__container-inner',
			]
		);

		$this->add_control(
			'container_background_overlay_heading',
			array(
				'label' => esc_html__( 'Container Overlay', 'custom-popup-builder' ),
				'type'  => Elementor\Controls_Manager::HEADING,
				'condition' => [
					'container_background_background' => [ 'classic', 'gradient' ],
				],
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Elementor\Group_Control_Background::get_type(),
			[
				'name'      => 'container_background_overlay',
				'selector'  => $uniq_popup_id . ' .custom-popup-builder__container-overlay',
				'condition' => [
					'container_background_background' => [ 'classic', 'gradient' ],
				],
			]
		);

		$this->add_control(
			'container_background_overlay_opacity',
			[
				'label' => __( 'Opacity', 'custom-popup-builder' ),
				'type' => Elementor\Controls_Manager::SLIDER,
				'default' => [
					'size' => .5,
				],
				'range' => [
					'px' => [
						'max' => 1,
						'step' => 0.01,
					],
				],
				'selectors' => [
					$uniq_popup_id . ' .custom-popup-builder__container-overlay' => 'opacity: {{SIZE}};',
				],
				'condition' => [
					'container_background_overlay' => [ 'classic', 'gradient' ],
					'container_background_background' => [ 'classic', 'gradient' ],
				],
			]
		);

		$this->add_group_control(
			Elementor\Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'container_background_overlay_css_filters',
				'selector' => $uniq_popup_id . ' .custom-popup-builder__container-overlay',
				'condition' => [
					'container_background_background' => [ 'classic', 'gradient' ],
				],
			]
		);

		$this->add_control(
			'container_background_overlay_blend_mode',
			[
				'label' => __( 'Blend Mode', 'custom-popup-builder' ),
				'type' => Elementor\Controls_Manager::SELECT,
				'options' => [
					'' => __( 'Normal', 'custom-popup-builder' ),
					'multiply' => 'Multiply',
					'screen' => 'Screen',
					'overlay' => 'Overlay',
					'darken' => 'Darken',
					'lighten' => 'Lighten',
					'color-dodge' => 'Color Dodge',
					'saturation' => 'Saturation',
					'color' => 'Color',
					'luminosity' => 'Luminosity',
				],
				'selectors' => [
					$uniq_popup_id . ' .custom-popup-builder__container-overlay' => 'mix-blend-mode: {{VALUE}}',
				],
				'condition' => [
					'container_background_background' => [ 'classic', 'gradient' ],
				],
				'separator' => 'after',
			]
		);

		$this->add_control(
			'other_heading',
			array(
				'label' => esc_html__( 'Other Styles', 'custom-popup-builder' ),
				'type'  => Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label'      => __( 'Padding', 'custom-popup-builder' ),
				'type'       => Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					$uniq_popup_id . ' .custom-popup-builder__container-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_margin',
			[
				'label'      => __( 'Margin', 'custom-popup-builder' ),
				'type'       => Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					$uniq_popup_id . ' .custom-popup-builder__container-inner' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'container_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'custom-popup-builder' ),
				'type'       => Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					$uniq_popup_id . ' .custom-popup-builder__container-inner'   => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$uniq_popup_id . ' .custom-popup-builder__container-overlay' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Elementor\Group_Control_Border::get_type(),
			[
				'name'        => 'container_border',
				'label'       => esc_html__( 'Border', 'custom-popup-builder' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => $uniq_popup_id . ' .custom-popup-builder__container-inner',
			]
		);

		$this->add_group_control(
			Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'container_shadow',
				'selector' => $uniq_popup_id . ' .custom-popup-builder__container-inner',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'custom_popup_builder_close_button_style',
			[
				'label' => __( 'Close Button', 'custom-popup-builder' ),
				'tab'   => Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'use_close_button',
			[
				'label'        => esc_html__( 'Use Close Button', 'custom-popup-builder' ),
				'type'         => Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'custom-popup-builder' ),
				'label_off'    => esc_html__( 'No', 'custom-popup-builder' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'close_button_icon',
			[
				'label'       => esc_html__( 'Icon', 'custom-popup-builder' ),
				'type'        => Elementor\Controls_Manager::ICON,
				'label_block' => true,
				'file'        => '',
				'default'     => 'fa fa-times',
			]
		);

		$this->add_group_control(
			\Custom_Popup_Builder_Group_Control_Transform_Style::get_type(),
			[
				'name'     => 'close_button_box_transform',
				'label'    => esc_html__( 'Icon Transform', 'custom-popup-builder' ),
				'selector' => $uniq_popup_id . ' .custom-popup-builder__close-button',
			]
		);

		$this->start_controls_tabs( 'close_button_style_tabs' );

		$this->start_controls_tab(
			'close_button_control_normal_tab',
			[
				'label' => esc_html__( 'Normal', 'custom-popup-builder' ),
			]
		);

		$this->add_group_control(
			\Custom_Popup_Builder_Group_Control_Box_Style::get_type(),
			[
				'name'     => 'close_button_box_style_normal',
				'label'    => esc_html__( 'Icon Styles', 'custom-popup-builder' ),
				'selector' => $uniq_popup_id . ' .custom-popup-builder__close-button',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'close_button_control_hover_tab',
			[
				'label' => esc_html__( 'Hover', 'custom-popup-builder' ),
			]
		);

		$this->add_group_control(
			\Custom_Popup_Builder_Group_Control_Box_Style::get_type(),
			[
				'name'     => 'close_button_box_style_hover',
				'label'    => esc_html__( 'Icon Styles', 'custom-popup-builder' ),
				'selector' => $uniq_popup_id . ' .custom-popup-builder__close-button:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'custom_popup_builder_overlay_style',
			[
				'label' => __( 'Popup Overlay', 'custom-popup-builder' ),
				'tab'   => Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'use_overlay',
			[
				'label'        => esc_html__( 'Use Overlay', 'custom-popup-builder' ),
				'type'         => Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'custom-popup-builder' ),
				'label_off'    => esc_html__( 'No', 'custom-popup-builder' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_group_control(
			Elementor\Group_Control_Background::get_type(),
			[
				'name'      => 'overlay_background',
				'selector'  => $uniq_popup_id . ' .custom-popup-builder__overlay',
				'condition' => [
					'use_overlay' => 'yes',
				]
			]
		);

		$this->end_controls_section();

	}
}
