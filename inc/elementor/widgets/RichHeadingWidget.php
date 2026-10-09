<?php

if (!defined('ABSPATH')) {
    exit;
}

class Glenmark_Rich_Heading_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'gln-rich-heading';
    }

    public function get_title()
    {
        return __('Rich Heading', 'gln-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-t-letter';
    }

    public function get_categories()
    {
        return ['basic', 'glenmark'];
    }

    public function get_keywords()
    {
        return ['heading', 'title', 'text', 'rich', 'nadpis'];
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'section_title',
            [
                'label' => __('Heading', 'gln-pharma-product'),
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => __('Title', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'placeholder' => __('Enter your title', 'gln-pharma-product'),
                'default' => __('Add Your Heading Text Here', 'gln-pharma-product'),
            ]
        );

        $this->add_control(
            'link',
            [
                'label' => __('Link', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::URL,
                'default' => [
                    'url' => '',
                ],
            ]
        );

        $this->add_control(
            'size',
            [
                'label' => __('Size', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'default' => __('Default', 'gln-pharma-product'),
                    'small' => __('Small', 'gln-pharma-product'),
                    'medium' => __('Medium', 'gln-pharma-product'),
                    'large' => __('Large', 'gln-pharma-product'),
                    'xl' => __('XL', 'gln-pharma-product'),
                    'xxl' => __('XXL', 'gln-pharma-product'),
                ],
                'default' => 'default',
                'condition' => [
                    'size!' => 'default',
                ],
            ]
        );

        $this->add_control(
            'header_size',
            [
                'label' => __('HTML Tag', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'div',
                    'span' => 'span',
                    'p' => 'p',
                ],
                'default' => 'h2',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_title_style',
            [
                'label' => __('Heading', 'gln-pharma-product'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'align',
            [
                'label' => __('Alignment', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => __('Start', 'gln-pharma-product'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'gln-pharma-product'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'end' => [
                        'title' => __('End', 'gln-pharma-product'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => __('Justified', 'gln-pharma-product'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}}' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'selector' => '{{WRAPPER}} .elementor-heading-title',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'text_stroke',
                'selector' => '{{WRAPPER}} .elementor-heading-title',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'text_shadow',
                'selector' => '{{WRAPPER}} .elementor-heading-title',
            ]
        );

        $this->add_control(
            'blend_mode',
            [
                'label' => __('Blend Mode', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '' => __('Normal', 'gln-pharma-product'),
                    'multiply' => __('Multiply', 'gln-pharma-product'),
                    'screen' => __('Screen', 'gln-pharma-product'),
                    'overlay' => __('Overlay', 'gln-pharma-product'),
                    'darken' => __('Darken', 'gln-pharma-product'),
                    'lighten' => __('Lighten', 'gln-pharma-product'),
                    'color-dodge' => __('Color Dodge', 'gln-pharma-product'),
                    'saturation' => __('Saturation', 'gln-pharma-product'),
                    'color' => __('Color', 'gln-pharma-product'),
                    'difference' => __('Difference', 'gln-pharma-product'),
                    'exclusion' => __('Exclusion', 'gln-pharma-product'),
                    'hue' => __('Hue', 'gln-pharma-product'),
                    'luminosity' => __('Luminosity', 'gln-pharma-product'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-heading-title' => 'mix-blend-mode: {{VALUE}}',
                ],
            ]
        );

        $this->start_controls_tabs('title_colors');

        $this->start_controls_tab(
            'title_colors_normal',
            [
                'label' => __('Normal', 'gln-pharma-product'),
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Text Color', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-heading-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'title_colors_hover',
            [
                'label' => __('Hover', 'gln-pharma-product'),
            ]
        );

        $this->add_control(
            'title_hover_color',
            [
                'label' => __('Link Color', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-heading-title a:hover, {{WRAPPER}} .elementor-heading-title a:focus' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_hover_color_transition_duration',
            [
                'label' => __('Transition Duration', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['s', 'ms', 'custom'],
                'default' => [
                    'unit' => 's',
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-heading-title a' => 'transition-duration: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        if (empty($settings['title'])) {
            return;
        }

        $this->add_render_attribute('title', 'class', 'elementor-heading-title');
        $this->add_render_attribute('title', 'class', 'elementor-size-' . (!empty($settings['size']) ? $settings['size'] : 'default'));
        $this->add_inline_editing_attributes('title');

        $title = trim(wp_kses_post($settings['title']));
        $title = preg_replace('/^<p>(.*)<\/p>$/s', '$1', $title);

        if (!empty($settings['link']['url'])) {
            $this->add_link_attributes('url', $settings['link']);
            $title = '<a ' . $this->get_render_attribute_string('url') . '>' . $title . '</a>';
        }

        $allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p'];
        $tag = in_array($settings['header_size'] ?? 'h2', $allowed_tags, true) ? $settings['header_size'] : 'h2';
        $title_html = sprintf('<%1$s %2$s>%3$s</%1$s>', $tag, $this->get_render_attribute_string('title'), $title);

        echo $title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}