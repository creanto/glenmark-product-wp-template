<?php

if (!defined('ABSPATH')) {
    exit;
}

class Glenmark_Shared_Template_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'gln-shared-template';
    }

    public function get_title()
    {
        return __('Sdílená šablona', 'gln-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-document-file';
    }

    public function get_categories()
    {
        return ['glenmark'];
    }

    public function get_keywords()
    {
        return ['glenmark', 'šablona', 'sdílený obsah', 'HTML'];
    }

    protected function register_controls()
    {
        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
        $items = glenmark_shared_content_get_items($is_editor);
        $options = ['' => __('Vyberte šablonu', 'gln-pharma-product')];

        foreach ($items as $key => $item) {
            if (empty($item['data']['content_html'])) {
                continue;
            }
            $options[$key] = (string) ($item['title'] ?? $key) . ' (' . $key . ')';
        }

        $this->start_controls_section('section_shared_template', [
            'label' => __('Sdílená šablona', 'gln-pharma-product'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
        $this->add_control('item_key', [
            'label' => __('Šablona', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT2,
            'options' => $options,
            'label_block' => true,
        ]);
        $this->end_controls_section();

        $this->start_controls_section('section_shared_template_style', [
            'label' => __('Obsah', 'gln-pharma-product'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);
        $this->add_responsive_control('content_alignment', [
            'label' => __('Zarovnání', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => [
                'left' => ['title' => __('Vlevo', 'gln-pharma-product'), 'icon' => 'eicon-text-align-left'],
                'center' => ['title' => __('Na střed', 'gln-pharma-product'), 'icon' => 'eicon-text-align-center'],
                'right' => ['title' => __('Vpravo', 'gln-pharma-product'), 'icon' => 'eicon-text-align-right'],
                'justify' => ['title' => __('Do bloku', 'gln-pharma-product'), 'icon' => 'eicon-text-align-justify'],
            ],
            'selectors' => [
                '{{WRAPPER}} .gln-shared-template__content' => 'text-align: {{VALUE}};',
            ],
        ]);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
            'name' => 'content_typography',
            'selector' => '{{WRAPPER}} .gln-shared-template__content',
        ]);
        $this->add_control('content_color', [
            'label' => __('Barva textu', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .gln-shared-template__content' => 'color: {{VALUE}};',
            ],
        ]);
        $this->add_group_control(\Elementor\Group_Control_Background::get_type(), [
            'name' => 'content_background',
            'types' => ['classic', 'gradient'],
            'selector' => '{{WRAPPER}} .gln-shared-template__content',
        ]);
        $this->add_responsive_control('content_padding', [
            'label' => __('Vnitřní odsazení', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%', 'em', 'rem'],
            'selectors' => [
                '{{WRAPPER}} .gln-shared-template__content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);
        $this->add_group_control(\Elementor\Group_Control_Border::get_type(), [
            'name' => 'content_border',
            'selector' => '{{WRAPPER}} .gln-shared-template__content',
        ]);
        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $key = isset($settings['item_key']) ? sanitize_text_field((string) $settings['item_key']) : '';
        if ('' === $key) {
            return;
        }

        echo '<div class="gln-shared-template">';
        echo glenmark_shared_content_render_live($key, 'template', '', false, 'gln-shared-template__content');
        echo '</div>';
    }
}