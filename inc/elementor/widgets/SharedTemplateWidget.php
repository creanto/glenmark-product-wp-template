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
        $this->add_control('show_heading', [
            'label' => __('Zobrazit nadpis', 'gln-pharma-product'),
            'description' => __('Nad šablonou zobrazí plný název položky jako nadpis.', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default' => '',
        ]);
        $this->add_control('heading_tag', [
            'label' => __('HTML tag nadpisu', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'h1' => 'H1',
                'h2' => 'H2',
                'h3' => 'H3',
                'h4' => 'H4',
                'h5' => 'H5',
                'h6' => 'H6',
            ],
            'default' => 'h1',
            'condition' => ['show_heading' => 'yes'],
        ]);
        $this->add_control('show_version', [
            'label' => __('Zobrazit verzi', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default' => 'yes',
        ]);
        $this->add_control('show_date', [
            'label' => __('Zobrazit datum', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default' => 'yes',
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
                '{{WRAPPER}} .gln-shared-template__content a' => 'color: {{VALUE}};',
                '{{WRAPPER}} .gln-shared-template__content h1, {{WRAPPER}} .gln-shared-template__content h2, {{WRAPPER}} .gln-shared-template__content h3, {{WRAPPER}} .gln-shared-template__content h4, {{WRAPPER}} .gln-shared-template__content h5, {{WRAPPER}} .gln-shared-template__content h6' => 'color: {{VALUE}};',
            ],
        ]);
        $this->add_control('content_link_color', [
            'label' => __('Barva odkazů', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .gln-shared-template__content a' => 'color: {{VALUE}};',
            ],
        ]);
        $this->add_control('content_link_decoration', [
            'label' => __('Dekorace odkazů', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                '' => __('Výchozí', 'gln-pharma-product'),
                'underline' => __('Podtržení', 'gln-pharma-product'),
                'none' => __('Žádná', 'gln-pharma-product'),
                'overline' => __('Nadtržení', 'gln-pharma-product'),
                'line-through' => __('Přeškrtnutí', 'gln-pharma-product'),
            ],
            'selectors' => [
                '{{WRAPPER}} .gln-shared-template__content a' => 'text-decoration: {{VALUE}};',
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

        $template_args = [
            'show_heading' => 'yes' === ($settings['show_heading'] ?? ''),
            'heading_tag' => isset($settings['heading_tag']) ? (string) $settings['heading_tag'] : 'h1',
            'show_version' => 'yes' === ($settings['show_version'] ?? ''),
            'show_date' => 'yes' === ($settings['show_date'] ?? ''),
        ];

        echo '<div class="gln-shared-template">';
        echo glenmark_shared_content_render_live($key, 'template', '', false, 'gln-shared-template__content', $template_args);
        echo '</div>';
    }
}