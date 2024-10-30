<?php

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Scheme_Color;
use Elementor\Scheme_Typography;
use Elementor\Widget_Base;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Custom_Popup_Builder_Mailchimp extends Custom_Popup_Builder_Base {

	public $config = array();

	public function get_name() {
		return 'custom-popup-builder-mailchimp';
	}

	public function get_title() {
		return esc_html__( 'mailchimp', 'custom-popup-builder' );
	}

	public function get_icon() {
		return 'eicon-mailchimp';
	}

	public function get_categories() {
		return array( 'custom-popup-builder' );
	}

	protected function _register_controls() {

		$css_scheme = apply_filters(
			'custom-popup-builder/mailchimp/css-scheme',
			array(
				'instance'         => '.custom-popup-builder-mailchimp',
				'label'            => '.custom-popup-builder-mailchimp__field-label',
				'input'            => '.custom-popup-builder-mailchimp__input',
				'submit_container' => '.custom-popup-builder-mailchimp__submit-container',
				'submit'           => '.custom-popup-builder-mailchimp__submit',
				'submit_icon'      => '.custom-popup-builder-mailchimp__submit-icon',
				'message'          => '.custom-popup-builder-mailchimp__message',
			)
		);

		$avaliable_lists = \Custom_Popup_Builder_Utils::get_avaliable_mailchimp_list();

		if ( ! $avaliable_lists ) {

			$this->start_controls_section(
				'section_subscribe_error',
				array(
					'label' => esc_html__( 'Error', 'custom-popup-builder' ),
				)
			);

			$this->add_control(
				'no_lists',
				array(
					'label' => false,
					'type'  => Controls_Manager::RAW_HTML,
					'raw'   => $this->empty_lists_message(),
				)
			);

			$this->end_controls_section();

			return;
		}

		$this->start_controls_section(
			'section_subscribe_settings',
			array(
				'label' => esc_html__( 'Settings', 'custom-popup-builder' ),
			)
		);

		$this->add_control(
			'target_list_id',
			[
				'label'   => esc_html__( 'List', 'custom-popup-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => key( $avaliable_lists ),
				'options' => $avaliable_lists
			]
		);

		$this->add_control(
			'use_redirect_url',
			[
				'label'        => esc_html__( 'Use Redirect Url?', 'custom-popup-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'custom-popup-builder' ),
				'label_off'    => esc_html__( 'No', 'custom-popup-builder' ),
				'return_value' => 'yes',
				'default'      => 'false',
			]
		);

		$this->add_control(
			'redirect_url',
			array(
				'label'       => esc_html__( 'Redirect Url', 'custom-popup-builder' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Enter Redirect Url', 'custom-popup-builder' ),
				'default'     => '#',
				'condition'   => array(
					'use_redirect_url' => 'yes',
				),
			)
		);

		$this->add_control(
			'close_popup_when_success',
			[
				'label'        => esc_html__( 'Close Popup When Success', 'custom-popup-builder' ),
				'description'  => esc_html__( 'if the widget is in a popup, the successful response status will close the popup.', 'custom-popup-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'custom-popup-builder' ),
				'label_off'    => esc_html__( 'No', 'custom-popup-builder' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_subscribe_fields',
			array(
				'label' => esc_html__( 'Fields', 'custom-popup-builder' ),
			)
		);

		$this->add_control(
			'email_label',
			array(
				'label'       => esc_html__( 'E-Mail Label', 'custom-popup-builder' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'E-Mail', 'custom-popup-builder' ),
			)
		);

		$this->add_control(
			'email_placeholder',
			array(
				'label'       => esc_html__( 'E-Mail Placeholder', 'custom-popup-builder' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Enter E-Mail', 'custom-popup-builder' ),
			)
		);

		$this->add_responsive_control(
			'email_field_size',
			[
				'label'   => __( 'Email Size', 'custom-popup-builder' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'100' => '100%',
					'80'  => '80%',
					'75'  => '75%',
					'66'  => '66%',
					'60'  => '60%',
					'50'  => '50%',
					'40'  => '40%',
					'33'  => '33%',
					'25'  => '25%',
					'20'  => '20%',
				],
				'default' => '100',
			]
		);

		$this->add_control(
			'use_additional_fields',
			array(
				'label'        => esc_html__( 'Use Additional Fields', 'custom-popup-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'custom-popup-builder' ),
				'label_off'    => esc_html__( 'No', 'custom-popup-builder' ),
				'return_value' => 'yes',
				'default'      => 'false',
				'separator'    => 'before',
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'field_merge_tag',
			[
				'label'   => esc_html__( 'Field Merge Tag', 'custom-popup-builder' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			]
		);

		$repeater->add_control(
			'field_merge_label',
			[
				'label'   => esc_html__( 'Label', 'custom-popup-builder' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Label', 'custom-popup-builder' ),
			]
		);

		$repeater->add_control(
			'field_merge_placeholder',
			[
				'label'   => esc_html__( 'Placeholder', 'custom-popup-builder' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Placeholder', 'custom-popup-builder' ),
			]
		);

		$repeater->add_responsive_control(
			'field_merge_width',
			[
				'label'   => __( 'Column Width', 'custom-popup-builder' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'100' => '100%',
					'80'  => '80%',
					'75'  => '75%',
					'66'  => '66%',
					'60'  => '60%',
					'50'  => '50%',
					'40'  => '40%',
					'33'  => '33%',
					'25'  => '25%',
					'20'  => '20%',
				],
				'default' => '100',
			]
		);

		$this->add_control(
			'additional_fields',
			[
				'type'        => Controls_Manager::REPEATER,
				'fields'      => array_values( $repeater->get_controls() ),
				'default'     => [
				],
				'title_field' => '{{{ field_merge_tag }}}',
				'condition' => [
					'use_additional_fields' => 'yes',
				]
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_subscribe_submit',
			array(
				'label' => esc_html__( 'Submit Button', 'custom-popup-builder' ),
			)
		);

		$this->add_control(
			'submit_button_text',
			array(
				'label'       => esc_html__( 'Submit Text', 'custom-popup-builder' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Enter Submit Button Text', 'custom-popup-builder' ),
				'default'     => esc_html__( 'Subscribe', 'custom-popup-builder' ),
			)
		);

		$this->add_responsive_control(
			'button_container_size',
			[
				'label'   => __( 'Button Size', 'custom-popup-builder' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'100' => '100%',
					'80'  => '80%',
					'75'  => '75%',
					'66'  => '66%',
					'60'  => '60%',
					'50'  => '50%',
					'40'  => '40%',
					'33'  => '33%',
					'25'  => '25%',
					'20'  => '20%',
				],
				'default' => '100',
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['submit_container'] => 'width: {{VALUE}}%;',
				),
			]
		);

		$this->add_responsive_control(
			'button_alignment',
			array(
				'label'   => esc_html__( 'Alignment', 'custom-popup-builder' ),
				'type'    => Controls_Manager::CHOOSE,
				'default' => 'center',
				'options' => array(
					'flex-start' => array(
						'title' => esc_html__( 'Left', 'custom-popup-builder' ),
						'icon'  => 'fa fa-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'custom-popup-builder' ),
						'icon'  => 'fa fa-align-center',
					),
					'flex-end' => array(
						'title' => esc_html__( 'Right', 'custom-popup-builder' ),
						'icon'  => 'fa fa-align-right',
					),
					'stretch' => [
						'title' => esc_html__( 'Justified', 'custom-popup-builder' ),
						'icon'  => 'fa fa-align-justify',
					]
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['submit_container']  => 'align-items: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		/**
		 * General Style Section
		 */
		$this->start_controls_section(
			'section_general_style',
			array(
				'label'      => esc_html__( 'General', 'custom-popup-builder' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_control(
			'column_gap',
			[
				'label' => __( 'Columns Gap', 'custom-popup-builder' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 10,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 60,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .custom-popup-builder-mailchimp__field-container' => 'padding-right: calc( {{SIZE}}{{UNIT}}/2 ); padding-left: calc( {{SIZE}}{{UNIT}}/2 );',
					'{{WRAPPER}} .custom-popup-builder-mailchimp__inner' => 'margin-left: calc( -{{SIZE}}{{UNIT}}/2 ); margin-right: calc( -{{SIZE}}{{UNIT}}/2 );',
				],
			]
		);

		$this->add_control(
			'row_gap',
			[
				'label' => __( 'Rows Gap', 'custom-popup-builder' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 10,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 60,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .custom-popup-builder-mailchimp__field-container' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} custom-popup-builder-mailchimp__inner' => 'margin-bottom: -{{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_padding',
			array(
				'label'      => esc_html__( 'Padding', 'custom-popup-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['instance'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'container_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'custom-popup-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['instance'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_container_style' );

		$this->start_controls_tab(
			'tab_container',
			array(
				'label' => esc_html__( 'Normal', 'custom-popup-builder' ),
			)
		);

		$this->add_control(
			'container_bg_color',
			array(
				'label' => esc_html__( 'Background Color', 'custom-popup-builder' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['instance'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'container_border',
				'label'       => esc_html__( 'Border', 'custom-popup-builder' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['instance'],
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'container_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['instance'],
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_container_error',
			array(
				'label' => esc_html__( 'Error', 'custom-popup-builder' ),
			)
		);

		$this->add_control(
			'container_error_bg_color',
			array(
				'label' => esc_html__( 'Background Color', 'custom-popup-builder' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['instance'] . '.custom-popup-builder-mailchimp--response-error' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'container_error_border',
				'label'       => esc_html__( 'Border', 'custom-popup-builder' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['instance'] . '.custom-popup-builder-mailchimp--response-error',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'container_error_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['instance'] . '.custom-popup-builder-mailchimp--response-error',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		/**
		 * Fields Style Section
		 */
		$this->start_controls_section(
			'section_fields_style',
			array(
				'label'      => esc_html__( 'Fields', 'custom-popup-builder' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_control(
			'heading_input_label_style',
			array(
				'label'     => esc_html__( 'Label', 'custom-popup-builder' ),
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'label_color',
			array(
				'label'     => esc_html__( 'Label Color', 'custom-popup-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['label'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'input_label_typography',
				'selector' => '{{WRAPPER}} ' . $css_scheme['label'],
			)
		);

		$this->add_control(
			'label_gap',
			[
				'label' => __( 'Label Gap', 'custom-popup-builder' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 5,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} ' . $css_scheme['label'] => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'heading_input_style',
			array(
				'label'     => esc_html__( 'Input', 'custom-popup-builder' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'input_padding',
			array(
				'label'      => esc_html__( 'Padding', 'custom-popup-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['input'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'input_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'custom-popup-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['input'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'input_typography',
				'selector' => '{{WRAPPER}} ' . $css_scheme['input'],
			)
		);

		$this->start_controls_tabs( 'tabs_input_style' );

		$this->start_controls_tab(
			'tab_input',
			array(
				'label' => esc_html__( 'Normal', 'custom-popup-builder' ),
			)
		);

		$this->add_control(
			'input_bg_color',
			array(
				'label' => esc_html__( 'Background Color', 'custom-popup-builder' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['input'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'input_color',
			array(
				'label'     => esc_html__( 'Text Color', 'custom-popup-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['input'] => 'color: {{VALUE}}',
					'{{WRAPPER}} ' . $css_scheme['input'] . '::-webkit-input-placeholder' => 'color: {{VALUE}}',
					'{{WRAPPER}} ' . $css_scheme['input'] . '::-moz-input-placeholder' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'input_border',
				'label'       => esc_html__( 'Border', 'custom-popup-builder' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['input'],
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'input_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['input'],
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_input_focus',
			array(
				'label' => esc_html__( 'Focus', 'custom-popup-builder' ),
			)
		);

		$this->add_control(
			'input_focus_bg_color',
			array(
				'label' => esc_html__( 'Background Color', 'custom-popup-builder' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['input'] . ':focus' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'input_focus_color',
			array(
				'label'     => esc_html__( 'Text Color', 'custom-popup-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['input'] . ':focus' => 'color: {{VALUE}}',
					'{{WRAPPER}} ' . $css_scheme['input'] . ':focus::-webkit-input-placeholder' => 'color: {{VALUE}}',
					'{{WRAPPER}} ' . $css_scheme['input'] . ':focus::-moz-input-placeholder' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'input_focus_border',
				'label'       => esc_html__( 'Border', 'custom-popup-builder' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['input'] . ':focus',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'input_focus_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['input'] . ':focus',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_input_error',
			array(
				'label' => esc_html__( 'Error', 'custom-popup-builder' ),
			)
		);

		$this->add_control(
			'input_error_bg_color',
			array(
				'label' => esc_html__( 'Background Color', 'custom-popup-builder' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['input'] . '.mail-invalid' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'input_error_color',
			array(
				'label'     => esc_html__( 'Text Color', 'custom-popup-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['input'] . '.mail-invalid' => 'color: {{VALUE}}',
					'{{WRAPPER}} ' . $css_scheme['input'] . '.mail-invalid::-webkit-input-placeholder' => 'color: {{VALUE}}',
					'{{WRAPPER}} ' . $css_scheme['input'] . '.mail-invalid::-moz-input-placeholder' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'input_error_border',
				'label'       => esc_html__( 'Border', 'custom-popup-builder' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['input'] . '.mail-invalid',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'input_error_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['input'] . '.mail-invalid',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		/**
		 * Submit Button Style Section
		 */
		$this->start_controls_section(
			'section_submit_button_style',
			array(
				'label'      => esc_html__( 'Submit Button', 'custom-popup-builder' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_control(
			'add_button_icon',
			array(
				'label'        => esc_html__( 'Add Icon', 'custom-popup-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'custom-popup-builder' ),
				'label_off'    => esc_html__( 'No', 'custom-popup-builder' ),
				'return_value' => 'yes',
				'default'      => 'false',
				'render_type'  => 'template',
			)
		);

		$this->add_control(
			'button_icon',
			array(
				'label'       => esc_html__( 'Icon', 'custom-popup-builder' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'file'        => '',
				'default'     => 'fa fa-send',
				'condition' => array(
					'add_button_icon' => 'yes',
				),
				'render_type' => 'template',
			)
		);

		$this->add_control(
			'button_icon_size',
			array(
				'label' => esc_html__( 'Icon Size', 'custom-popup-builder' ),
				'type' => Controls_Manager::SLIDER,
				'range' => array(
					'px' => array(
						'min' => 7,
						'max' => 90,
					),
				),
				'condition' => array(
					'add_button_icon' => 'yes',
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['submit_icon'] . ':before' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'button_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'custom-popup-builder' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array(
					'add_button_icon' => 'yes',
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['submit_icon'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'button_icon_margin',
			array(
				'label'      => esc_html__( 'Icon Margin', 'custom-popup-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'condition' => array(
					'add_button_icon' => 'yes',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['submit_icon'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'scheme'   => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}}  ' . $css_scheme['submit'],
			)
		);

		$this->add_responsive_control(
			'button_padding',
			array(
				'label'      => esc_html__( 'Padding', 'custom-popup-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['submit'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'button_margin',
			array(
				'label'      => __( 'Margin', 'custom-popup-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['submit'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'button_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'custom-popup-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['submit'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			array(
				'label' => esc_html__( 'Normal', 'custom-popup-builder' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'button_bg',
				'selector' => '{{WRAPPER}} ' . $css_scheme['submit'],
				'fields_options' => array(
					'background' => array(
						'default' => 'classic',
					),
					'color' => array(
						'label'  => _x( 'Background Color', 'Background Control', 'custom-popup-builder' ),
						'scheme' => array(
							'type'  => Scheme_Color::get_type(),
							'value' => Scheme_Color::COLOR_1,
						),
					),
					'color_b' => array(
						'label' => _x( 'Second Background Color', 'Background Control', 'custom-popup-builder' ),
					),
				),
				'exclude' => array(
					'image',
					'position',
					'attachment',
					'attachment_alert',
					'repeat',
					'size',
				),
			)
		);

		$this->add_control(
			'button_color',
			array(
				'label'     => esc_html__( 'Text Color', 'custom-popup-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['submit'] => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'button_border',
				'label'       => esc_html__( 'Border', 'custom-popup-builder' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['submit'],
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['submit'],
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			array(
				'label' => esc_html__( 'Hover', 'custom-popup-builder' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'button_hover_bg',
				'selector' => '{{WRAPPER}} ' . $css_scheme['submit'] . ':hover',
				'fields_options' => array(
					'background' => array(
						'default' => 'classic',
					),
					'color' => array(
						'label' => _x( 'Background Color', 'Background Control', 'custom-popup-builder' ),
					),
					'color_b' => array(
						'label' => _x( 'Second Background Color', 'Background Control', 'custom-popup-builder' ),
					),
				),
				'exclude' => array(
					'image',
					'position',
					'attachment',
					'attachment_alert',
					'repeat',
					'size',
				),
			)
		);

		$this->add_control(
			'button_hover_color',
			array(
				'label'     => esc_html__( 'Text Color', 'custom-popup-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['submit'] . ':hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'button_icon_hover_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'custom-popup-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['submit'] . ':hover ' . $css_scheme['submit_icon'] => 'color: {{VALUE}}',
				),
				'condition' => array(
					'add_button_icon' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'button_hover_border',
				'label'       => esc_html__( 'Border', 'custom-popup-builder' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['submit'] . ':hover',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_hover_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['submit'] . ':hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		/**
		 * Message Style Section
		 */
		$this->start_controls_section(
			'section_message_style',
			array(
				'label'      => esc_html__( 'Message', 'custom-popup-builder' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_control(
			'message_alignment',
			array(
				'label'   => esc_html__( 'Alignment', 'custom-popup-builder' ),
				'type'    => Controls_Manager::CHOOSE,
				'default' => 'center',
				'options' => array(
					'flex-start' => array(
						'title' => esc_html__( 'Left', 'custom-popup-builder' ),
						'icon'  => 'fa fa-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'custom-popup-builder' ),
						'icon'  => 'fa fa-align-center',
					),
					'flex-end' => array(
						'title' => esc_html__( 'Right', 'custom-popup-builder' ),
						'icon'  => 'fa fa-align-right',
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['message'] . ' .custom-popup-builder-mailchimp__message-inner' => 'justify-content: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'message_success_typography',
				'scheme'   => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} ' . $css_scheme['message'] . ' span',
			)
		);

		$this->add_responsive_control(
			'message_success_padding',
			array(
				'label'      => esc_html__( 'Padding', 'custom-popup-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['message'] . ' span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'message_success_margin',
			array(
				'label'      => __( 'Margin', 'custom-popup-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['message'] . ' span' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'message_success_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'custom-popup-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['message'] . ' span' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'message_success_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['message'] . ' span',
			)
		);

		$this->start_controls_tabs( 'tabs_message_style' );

		$this->start_controls_tab(
			'tab_message_success',
			array(
				'label' => esc_html__( 'Success', 'custom-popup-builder' ),
			)
		);

		$this->add_control(
			'message_success_bg_color',
			array(
				'label' => esc_html__( 'Background Color', 'custom-popup-builder' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .custom-popup-builder-mailchimp--response-success ' . $css_scheme['message'] . ' span' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'message_success_color',
			array(
				'label'     => esc_html__( 'Text Color', 'custom-popup-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .custom-popup-builder-mailchimp--response-success ' . $css_scheme['message'] . ' span' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'message_success_border',
				'label'       => esc_html__( 'Border', 'custom-popup-builder' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .custom-popup-builder-mailchimp--response-success ' . $css_scheme['message'] . ' span',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_message_error',
			array(
				'label' => esc_html__( 'Error', 'custom-popup-builder' ),
			)
		);

		$this->add_control(
			'message_error_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'custom-popup-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .custom-popup-builder-mailchimp--response-error ' . $css_scheme['message'] . ' span' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'message_error_color',
			array(
				'label'     => esc_html__( 'Text Color', 'custom-popup-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .custom-popup-builder-mailchimp--response-error ' . $css_scheme['message'] . ' span' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'message_error_border',
				'label'       => esc_html__( 'Border', 'custom-popup-builder' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .custom-popup-builder-mailchimp--response-error ' . $css_scheme['message'] . ' span',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

	}

	/**
	 * [render description]
	 * @return [type] [description]
	 */
	protected function render() {

		$this->__context = 'render';

		$this->__open_wrap();

		$submit_button_text = $this->get_settings( 'submit_button_text' );
		$button_icon        = $this->get_settings( 'button_icon' );
		$use_icon           = $this->get_settings( 'add_button_icon' );
		$widget_id          = $this->get_id();

		$this->add_render_attribute( 'main-container',[
			'class'         => [
				'custom-popup-builder-mailchimp'
			],
			'data-settings' => $this->generate_setting_json(),
		] );

		$icon_html = '';

		if ( filter_var( $use_icon, FILTER_VALIDATE_BOOLEAN ) ) {
			$icon_html = sprintf( '<i class="custom-popup-builder-mailchimp__submit-icon %s"></i>', $button_icon );
		}

		$instance_data = apply_filters( 'custom-popup-builder/mailchimp/input-instance-data', [], $this );

		$instance_data = json_encode( $instance_data );

		$email_id = 'custom-popup-builder-mailchimp-mail-'. $widget_id;

		$this->add_render_attribute( 'email-field-container',
			[
				'class'       => [
					'custom-popup-builder-mailchimp__field-container',
				],
				'data-column'        => $this->get_settings( 'email_field_size' ),
				'data-column-tablet' => $this->get_settings( 'email_field_size_tablet' ),
				'data-column-mobile' => $this->get_settings( 'email_field_size_mobile' ),
			]
		);

		$this->add_render_attribute( 'email-label',
			[
				'for'   => $email_id,
				'class' => [
					'custom-popup-builder-mailchimp__field-label',
				],
			]
		);

		$this->add_render_attribute( 'email-input',
			[
				'id'          => $email_id,
				'class'       => [
					'custom-popup-builder-mailchimp__field',
					'custom-popup-builder-mailchimp__input',
					'custom-popup-builder-mailchimp__mail-field',
				],
				'type'               => 'email',
				'name'               => 'email',
				'placeholder'        => $this->get_settings( 'email_placeholder' ),
				'data-instance-data' => htmlspecialchars( $instance_data ),
			]
		);

		?><div <?php echo $this->get_render_attribute_string( 'main-container' ); ?>>
			<form method="POST" action="#" class="custom-popup-builder-mailchimp__form">
				<div class="custom-popup-builder-mailchimp__inner">
					<div <?php echo $this->get_render_attribute_string( 'email-field-container' ); ?>><?php
						if ( ! empty( $this->get_settings( 'email_label' ) ) ) {
							?><label <?php echo $this->get_render_attribute_string( 'email-label' ); ?>><?php
								echo $this->get_settings( 'email_label' );
							?></label><?php
						}
						?><input <?php echo $this->get_render_attribute_string( 'email-input' ); ?>>
					</div>

					<?php $this->generate_additional_fields(); ?>
					<div class="custom-popup-builder-mailchimp__field-container custom-popup-builder-mailchimp__submit-container">
						<?php echo sprintf( '<a class="custom-popup-builder-mailchimp__submit elementor-button elementor-size-md" href="#">%s<span class="custom-popup-builder-mailchimp__submit-text">%s</span></a>', $icon_html, $submit_button_text ); ?>
					</div>
				</div>
				<div class="custom-popup-builder-mailchimp__message"><div class="custom-popup-builder-mailchimp__message-inner"><span></span></div></div>
			</form>
		</div><?php

		$this->__close_wrap();
	}

	public function generate_setting_json() {
		$module_settings = $this->get_settings();

		$settings = array(
			'redirect'                 => filter_var( $module_settings['use_redirect_url'], FILTER_VALIDATE_BOOLEAN ),
			'redirect_url'             => $module_settings['redirect_url'],
			'target_list_id'           => $module_settings['target_list_id'],
			'close_popup_when_success' => filter_var( $module_settings['close_popup_when_success'], FILTER_VALIDATE_BOOLEAN ),
		);

		$settings = json_encode( $settings );

		return htmlspecialchars( $settings );
	}

	public function get_field_data( $list_id, $field_tag ) {
		$merge_fields = \Custom_Popup_Builder_Utils::get_avaliable_mailchimp_merge_fields( $list_id );

		foreach ( $merge_fields as $key => $field_data ) {
			$tag = $field_data['tag'];

			if ( $tag === $field_tag ) {
				return $field_data;
			}
		}

		return false;
	}

	
	public function generate_field_item( $field_merge_data, $field_data ) {
		$type        = $field_merge_data['type'];
		$tag         = $field_merge_data['tag'];
		$public      = $field_merge_data['public'];
		$name        = strtolower( $field_merge_data['tag'] );
		$field_id    = 'custom-popup-builder-mailchimp-' . $this->get_id() .'-field-' . $field_data['_id'];
		$placeholder = $field_data['field_merge_placeholder'];
		$label       = $field_data['field_merge_label'];

		if ( ! $public ) {
			return;
		}

		$container_attr_tag = $field_id . '-container';

		$this->add_render_attribute( $container_attr_tag,
			[
				'class' => [
					'custom-popup-builder-mailchimp__field-container',
				],
				'data-column'        => $field_data['field_merge_width'],
				'data-column-tablet' => $field_data['field_merge_width_tablet'],
				'data-column-mobile' => $field_data['field_merge_width_mobile'],
			]
		);

		$label_attr_tag = $field_id . '-label';

		$this->add_render_attribute( $label_attr_tag,
			[
				'for'   => $field_id,
				'class' => [
					'custom-popup-builder-mailchimp__field-label',
				],
			]
		);

		$input_attr_tag = $field_id . '-input';

		?><div <?php echo $this->get_render_attribute_string( $container_attr_tag ); ?>><?php

			switch ( $type ) {
				case 'text':
				case 'address':
					$this->add_render_attribute( $input_attr_tag, [
						'id'          => $field_id,
						'class'       => [
							'custom-popup-builder-mailchimp__field',
							'custom-popup-builder-mailchimp__input',
						],
						'type'        => 'text',
						'name'        => $name,
						'placeholder' => $placeholder,
					] );

					if ( ! empty( $label ) ) {
						?><label <?php echo $this->get_render_attribute_string( $label_attr_tag ); ?>><?php echo $label; ?></label><?php
					}?><input <?php echo $this->get_render_attribute_string( $input_attr_tag ); ?>><?php

				break;

				case 'phone':
					$this->add_render_attribute( $input_attr_tag, [
						'id'          => $field_id,
						'class'       => [
							'custom-popup-builder-mailchimp__field',
							'custom-popup-builder-mailchimp__input',
						],
						'type'        => 'tel',
						'name'        => $name,
						'placeholder' => $placeholder,
					] );

					if ( ! empty( $label ) ) {
						?><label <?php echo $this->get_render_attribute_string( $label_attr_tag ); ?>><?php echo $label; ?></label><?php
					}?><input <?php echo $this->get_render_attribute_string( $input_attr_tag ); ?>><?php

				break;

				case 'date':
				case 'birthday':
					$attrs = [
						'id'          => $field_id,
						'class'       => [
							'custom-popup-builder-mailchimp__field',
							'custom-popup-builder-mailchimp__input',
						],
						'type'        => 'text',
						'name'        => $name,
						'placeholder' => $placeholder,
						'data-format' => $field_data['options']['date_format'],
						'onfocus'     => '(this.type="date")',
						'onblur'      => 'if(this.value==""){this.type="text"}',
					];

					$this->add_render_attribute( $input_attr_tag, $attrs );

					if ( ! empty( $label ) ) {
						?><label <?php echo $this->get_render_attribute_string( $label_attr_tag ); ?>><?php echo $label; ?></label><?php
					}?><input <?php echo $this->get_render_attribute_string( $input_attr_tag ); ?>><?php

				break;

				case 'number':
					$this->add_render_attribute( $input_attr_tag, [
						'id'          => $field_id,
						'class'       => [
							'custom-popup-builder-mailchimp__field',
							'custom-popup-builder-mailchimp__input',
						],
						'type'        => 'number',
						'name'        => $name,
						'placeholder' => $placeholder,
					] );

					if ( ! empty( $label ) ) {
						?><label <?php echo $this->get_render_attribute_string( $label_attr_tag ); ?>><?php echo $label; ?></label><?php
					}?><input <?php echo $this->get_render_attribute_string( $input_attr_tag ); ?>><?php

				break;

				case 'radio':
					$choices = $field_data['options']['choices'];

					if ( empty( $choices ) ) {
						return;
					}

					$id = 'custom-popup-builder-mailchimp-' . $this->get_id() .'-'. strtolower( $tag );

					?><fieldset id="<?php echo $id; ?>" class="custom-popup-builder-mailchimp__field"><?php
						foreach ( $choices as $key => $value ) {
							$radio_id = $id . '-' . $key;

							$attrs = [
								'class'       => [
									'custom-popup-builder-mailchimp__radio',
								],
								'type'  => 'radio',
								'name'  => $name,
								'value' => $value,
								'id'    => $radio_id,
							];

							if ( 0 == $key ) {
								$attrs['checked'] = 'checked';
							}

							$this->add_render_attribute( $radio_id, $attrs );
							?>
							<div>
								<input <?php echo $this->get_render_attribute_string( $radio_id ); ?>>
								<label for="<?php echo $radio_id; ?>"><?php echo $value; ?></label>
							</div>
							<?php
						}?></fieldset><?php

				break;

				case 'dropdown':
					$choices = $field_merge_data['options']['choices'];

					if ( empty( $choices ) ) {
						return;
					}

					$this->add_render_attribute( $input_attr_tag, [
						'id'          => $field_id,
						'class'       => [
							'custom-popup-builder-mailchimp__field',
							'custom-popup-builder-mailchimp__input',
						],
						'name'        => $name,
						'placeholder' => $placeholder,
					] );

					if ( ! empty( $label ) ) {
						?><label <?php echo $this->get_render_attribute_string( $label_attr_tag ); ?>><?php echo $label; ?></label><?php
					}?><select <?php echo $this->get_render_attribute_string( $input_attr_tag ); ?>><?php

						foreach ( $choices as $key => $value ) {?>
							<option value="<?php echo $value; ?>"><?php echo $value; ?></option><?php
						}?>

					</select><?php

				break;
			}

		?></div><?php
	}

	public function generate_additional_fields() {
		$module_settings = $this->get_settings();
		$target_list_id  = $module_settings[ 'target_list_id' ];

		$additional_fields = $module_settings['additional_fields'];

		if ( ! filter_var( $module_settings['use_additional_fields'], FILTER_VALIDATE_BOOLEAN ) || empty( $additional_fields ) ) {
			return false;
		}

		$merge_fields = \Custom_Popup_Builder_Utils::get_avaliable_mailchimp_merge_fields( $target_list_id );

		if ( empty( $merge_fields ) ) {
			return false;
		}

		foreach ( $additional_fields as $key => $field_data ) {
			$field_merge_tag = $field_data['field_merge_tag'];

			$field_merge_data = $this->get_field_data( $target_list_id, $field_merge_tag );

			if ( ! $field_merge_data ) {
				continue;
			}

			$this->generate_field_item( $field_merge_data, $field_data );
		}
	}

	public function empty_lists_message() {
		return '<div id="elementor-widget-template-empty-lists">
				<div class="elementor-widget-template-empty-templates-icon"><i class="eicon-nerd"></i></div>
				<div class="elementor-widget-template-empty-templates-title">' . esc_html__( 'MailChimp', 'custom-popup-builder' ) . '</div>
				<div class="elementor-widget-template-empty-templates-footer">' . esc_html__( 'Synchronize your MailChimp account or add a new list', 'custom-popup-builder' ) . '</div>
				</div>';
	}

}
