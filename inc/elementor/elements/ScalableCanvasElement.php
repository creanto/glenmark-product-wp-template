<?php

if (!defined('ABSPATH')) {
    exit;
}

class Glenmark_Scalable_Canvas_Element extends \Elementor\Includes\Elements\Container
{
    public static function get_type()
    {
        return 'gln-hero';
    }

    public function get_name()
    {
        return 'gln-hero';
    }

    public function get_title()
    {
        return esc_html__('Hero', 'gln-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-banner';
    }

    public function get_categories()
    {
        return ['glenmark'];
    }

    protected function get_initial_config()
    {
        $config = parent::get_initial_config();
        $config['categories'] = ['glenmark'];
        $config['show_in_panel'] = true;

        return $config;
    }

    protected function register_controls()
    {
        parent::register_controls();

        $this->start_controls_section(
            'gln_scalable_canvas_section',
            [
                'label' => esc_html__('Hero', 'gln-pharma-product'),
                'tab' => \Elementor\Controls_Manager::TAB_LAYOUT,
            ]
        );

        $this->add_control(
            'gln_canvas_width',
            [
                'label' => esc_html__('Design width', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 1, 'max' => 10000, 'step' => 1]],
                'default' => ['unit' => 'px', 'size' => 1200],
            ]
        );

        $this->add_control(
            'gln_canvas_height',
            [
                'label' => esc_html__('Design height', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 1, 'max' => 10000, 'step' => 1]],
                'default' => ['unit' => 'px', 'size' => 410],
            ]
        );

        $this->add_control(
            'gln_canvas_grow',
            [
                'label' => esc_html__('Grow above design size', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $this->add_control(
            'gln_canvas_overflow',
            [
                'label' => esc_html__('Overflow', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'hidden',
                'options' => [
                    'hidden' => esc_html__('Clip overflowing content', 'gln-pharma-product'),
                    'visible' => esc_html__('Show overflowing content', 'gln-pharma-product'),
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'gln_canvas_background_section',
            [
                'label' => esc_html__('Canvas background', 'gln-pharma-product'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'gln_canvas_background',
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
        $width_setting = $settings['gln_canvas_width'] ?? 1200;
        $height_setting = $settings['gln_canvas_height'] ?? 410;
        $width = max(1, absint(is_array($width_setting) ? ($width_setting['size'] ?? 1200) : $width_setting));
        $height = max(1, absint(is_array($height_setting) ? ($height_setting['size'] ?? 410) : $height_setting));
        $grow = ('yes' === ($settings['gln_canvas_grow'] ?? '')) ? 'yes' : 'no';
        $overflow = ('visible' === ($settings['gln_canvas_overflow'] ?? 'hidden')) ? 'visible' : 'hidden';

        $this->add_render_attribute('_wrapper', [
            'class' => 'gln-scalable-canvas',
            'data-gln-canvas-width' => (string) $width,
            'data-gln-canvas-height' => (string) $height,
            'data-gln-canvas-grow' => $grow,
            'data-gln-canvas-overflow' => $overflow,
            'style' => '--gln-scalable-canvas-design-width:' . $width . 'px;--gln-scalable-canvas-design-height:' . $height . 'px;',
        ]);
    }
}
