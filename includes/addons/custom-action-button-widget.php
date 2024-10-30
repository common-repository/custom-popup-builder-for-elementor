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

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Custom_Action_Button_Widget extends Custom_Popup_Builder_Base {

	public function get_name() {
		return 'custom-popup-builder-action-button';
	}

	public function get_title() {
		return esc_html__( 'Popup Action Button', 'custom-popup-builder' );
	}

	public function get_icon() {
		return 'eicon-button';
	}

	public function get_categories() {
		return array( 'custom-popup-builder' );
	}

	public function get_script_depends() {
		return array();
	}

	protected function _register_controls() {
		$css_scheme = apply_filters(
			'custom-popup-builder/popup-action-button/css-scheme',
			array(
				'button'   => '.custom-popup-builder-action-button',
				'instance' => '.custom-popup-builder-action-button__instance',
				'text'     => '.custom-popup-builder-action-button__text',
				'icon'     => '.custom-popup-builder-action-button__icon',
			)
		);

		$this->start_controls_section(
			'section_settings',
			array(
				'label' => esc_html__( 'Settings', 'custom-popup-builder' ),
			)
		);

		$this->add_control(
			'button_action_type',
			[
				'label'   => __( 'Action Type', 'custom-popup-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'link',
				'options' => [
					'link'             => esc_html__( 'Link', 'custom-popup-builder' ),
					'leave'            => esc_html__( 'Leave Page', 'custom-popup-builder' ),
					'close-popup'      => esc_html__( 'Close Popup', 'custom-popup-builder' ),
					'close-constantly' => esc_html__( 'Close Сonstantly', 'custom-popup-builder' ),
				],
			]
		);

		$this->add_control(
			'button_text',
			array(
				'label'   => esc_html__( 'Button text', 'custom-popup-builder' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Read More', 'custom-popup-builder' ),
			)
		);

		$this->add_control(
			'button_link',
			array(
				'label'       => esc_html__( 'Button Link', 'custom-popup-builder' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'http://your-link.com',
				'default' => array(
					'url' => '#',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_action_button_style',
			array(
				'label'      => esc_html__( 'General', 'custom-popup-builder' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_responsive_control(
			'button_alignment',
			array(
				'label'   => esc_html__( 'Alignment', 'custom-popup-builder' ),
				'type'    => Controls_Manager::CHOOSE,
				'default' => 'center',
				'options' => array(
					'flex-start'    => array(
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
					'{{WRAPPER}} ' . $css_scheme['button'] => 'justify-content: {{VALUE}};',
				),
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
			)
		);

		$this->add_control(
			'button_icon',
			array(
				'label'       => esc_html__( 'Icon', 'custom-popup-builder' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'file'        => '',
				'default'     => 'fa fa-check',
				'condition' => array(
					'add_button_icon' => 'yes',
				),
			)
		);

		$this->add_control(
			'button_icon_position',
			array(
				'label'   => esc_html__( 'Icon Position', 'custom-popup-builder' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'before' => esc_html__( 'Before Text', 'custom-popup-builder' ),
					'after'  => esc_html__( 'After Text', 'custom-popup-builder' ),
				),
				'default'     => 'after',
				'render_type' => 'template',
				'condition' => array(
					'add_button_icon' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'button_icon_margin',
			array(
				'label'      => __( 'Icon Margin', 'custom-popup-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['icon'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition' => array(
					'add_button_icon' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'button_padding',
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
			'button_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'custom-popup-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['instance'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'scheme'   => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} ' . $css_scheme['text'],
			)
		);

		$this->add_control(
			'button_bg_color',
			array(
				'label' => esc_html__( 'Background Color', 'custom-popup-builder' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => array(
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_1,
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['instance'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			\Custom_Popup_Builder_Group_Control_Box_Style::get_type(),
			[
				'name'     => 'button_icon_box',
				'label'    => esc_html__( 'Icon Styles', 'custom-popup-builder' ),
				'selector' => '{{WRAPPER}} ' . $css_scheme['instance'] . ' ' . $css_scheme['icon'],
				'condition' => array(
					'add_button_icon' => 'yes',
				),
			]
		);

		$this->add_control(
			'button_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'custom-popup-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['text'] => 'color: {{VALUE}}',
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
				'selector'    => '{{WRAPPER}} ' . $css_scheme['instance'],
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['instance'],
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
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography_hover',
				'scheme'   => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} ' . $css_scheme['instance'] . ':hover ' . $css_scheme['text'],
			)
		);

		$this->add_control(
			'button_hover_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'custom-popup-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['instance'] . ':hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			\Custom_Popup_Builder_Group_Control_Box_Style::get_type(),
			[
				'name'     => 'button_icon_box_hover',
				'label'    => esc_html__( 'Icon Styles', 'custom-popup-builder' ),
				'selector' => '{{WRAPPER}} ' . $css_scheme['instance'] . ':hover ' . $css_scheme['icon'],
				'condition' => array(
					'add_button_icon' => 'yes',
				),
			]
		);

		$this->add_control(
			'button_hover_color',
			array(
				'label'     => esc_html__( 'Text Color', 'custom-popup-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['instance'] . ':hover ' . $css_scheme['text'] => 'color: {{VALUE}}',
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
				'selector'    => '{{WRAPPER}} ' . $css_scheme['instance'] . ':hover',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_hover_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['instance'] . ':hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

	}

	protected function render() {

		$settings = $this->get_settings();

		$position    = $this->get_settings( 'button_icon_position' );
		$use_icon    = $this->get_settings( 'add_button_icon' );
		$button_icon = $this->get_settings( 'button_icon' );
		$button_text = $this->get_settings( 'button_text' );
		$button_url  = $this->get_settings( 'button_link' );

		if ( empty( $button_url ) ) {
			return false;
		}

		$json_settings = array(
			'action-type'   => $settings['button_action_type'],
		);

		$this->add_render_attribute( 'instance', 'class', array(
			'custom-popup-builder-action-button__instance',
			'custom-popup-builder-action-button--icon-' . $position,
		) );

		$this->add_render_attribute( 'instance', 'data-settings', htmlspecialchars( json_encode( $json_settings ) ) );

		$this->add_render_attribute( 'instance', 'href', $button_url['url'] );

		if ( $button_url['is_external'] ) {
			$this->add_render_attribute( 'instance', 'target', '_blank' );
		}

		if ( ! empty( $button_url['nofollow'] ) ) {
			$this->add_render_attribute( 'instance', 'rel', 'nofollow' );
		}

		?>
		<div class="custom-popup-builder-action-button">
			<a <?php echo $this->get_render_attribute_string( 'instance' ); ?>><?php
				if ( filter_var( $use_icon, FILTER_VALIDATE_BOOLEAN ) ) {
					echo sprintf( '<i class="custom-popup-builder-action-button__icon %s"></i>', $button_icon );
				}
				echo sprintf( '<span class="custom-popup-builder-action-button__text">%s</span>', $button_text );?>
			</a>
		</div><?php
	}
}
