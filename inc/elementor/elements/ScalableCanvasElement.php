<?php

if (!defined('ABSPATH')) {
    exit;
}

class Webrev_Scalable_Canvas_Element extends \Elementor\Includes\Elements\Container
{
    public static function get_type()
    {
        return 'wr-hero';
    }

    public function get_name()
    {
        return 'wr-hero';
    }

    public function get_title()
    {
        return esc_html__('Hero', 'wr-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-banner';
    }

    public function get_categories()
    {
        return ['webrev'];
    }

    protected function get_initial_config()
    {
        $config = parent::get_initial_config();
        $config['categories'] = ['webrev'];
        $config['show_in_panel'] = true;

        return $config;
    }

    protected function register_controls()
    {
        parent::register_controls();

        $this->start_controls_section(
            'wr_scalable_canvas_section',
            [
                'label' => esc_html__('Hero', 'wr-pharma-product'),
                'tab' => \Elementor\Controls_Manager::TAB_LAYOUT,
            ]
        );

        $this->add_control(
            'wr_canvas_width',
            [
                'label' => esc_html__('Design width', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 1, 'max' => 10000, 'step' => 1]],
                'default' => ['unit' => 'px', 'size' => 1200],
            ]
        );

        $this->add_control(
            'wr_canvas_height',
            [
                'label' => esc_html__('Design height', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 1, 'max' => 10000, 'step' => 1]],
                'default' => ['unit' => 'px', 'size' => 410],
            ]
        );

        $this->add_control(
            'wr_canvas_grow',
            [
                'label' => esc_html__('Grow above design size', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $this->add_control(
            'wr_canvas_overflow',
            [
                'label' => esc_html__('Overflow', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'hidden',
                'options' => [
                    'hidden' => esc_html__('Clip overflowing content', 'wr-pharma-product'),
                    'visible' => esc_html__('Show overflowing content', 'wr-pharma-product'),
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'wr_canvas_background_section',
            [
                'label' => esc_html__('Canvas background', 'wr-pharma-product'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'wr_canvas_background',
                'types' => ['classic', 'gradient', 'video'],
                'selector' => '{{WRAPPER}} > .e-con-inner',
            ]
        );

        $this->end_controls_section();
    }

    protected function add_render_attributes()
    {
        parent::add_render_attributes();

        $settings = $this->get_settings_for_display();
        $width_setting = $settings['wr_canvas_width'] ?? 1200;
        $height_setting = $settings['wr_canvas_height'] ?? 410;
        $width = max(1, absint(is_array($width_setting) ? ($width_setting['size'] ?? 1200) : $width_setting));
        $height = max(1, absint(is_array($height_setting) ? ($height_setting['size'] ?? 410) : $height_setting));
        $grow = ('yes' === ($settings['wr_canvas_grow'] ?? '')) ? 'yes' : 'no';
        $overflow = ('visible' === ($settings['wr_canvas_overflow'] ?? 'hidden')) ? 'visible' : 'hidden';

        $this->add_render_attribute('_wrapper', [
            'class' => 'wr-scalable-canvas',
            'data-wr-canvas-width' => (string) $width,
            'data-wr-canvas-height' => (string) $height,
            'data-wr-canvas-grow' => $grow,
            'data-wr-canvas-overflow' => $overflow,
            'style' => '--wr-scalable-canvas-design-width:' . $width . 'px;--wr-scalable-canvas-design-height:' . $height . 'px;',
        ]);
    }
}
