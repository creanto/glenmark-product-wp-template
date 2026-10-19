<?php

if (!defined('ABSPATH')) {
    exit;
}

class Glenmark_Shared_Info_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'gln-shared-info';
    }

    public function get_title()
    {
        return __('Krátký sdílený údaj', 'gln-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-editor-list-ul';
    }

    public function get_categories()
    {
        return ['glenmark'];
    }

    public function get_keywords()
    {
        return ['glenmark', 'kontakt', 'adresa', 'telefon', 'e-mail', 'sdílený údaj'];
    }

    protected function register_controls()
    {
        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
        $items = glenmark_shared_content_get_items($is_editor);
        $options = ['' => __('Vyberte krátký údaj', 'gln-pharma-product')];

        foreach ($items as $key => $item) {
            if ('contact' !== ($item['type'] ?? '') && 'contact' !== ($item['data']['content_type'] ?? '')) {
                continue;
            }
            $options[$key] = (string) ($item['title'] ?? $key) . ' (' . $key . ')';
        }

        $this->start_controls_section('section_shared_info', [
            'label' => __('Krátký sdílený údaj', 'gln-pharma-product'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
        $this->add_control('item_key', [
            'label' => __('Údaj', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT2,
            'options' => $options,
            'label_block' => true,
        ]);
        $this->add_control('format', [
            'label' => __('Zobrazení', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'contact' => __('Kontakt', 'gln-pharma-product'),
                'address' => __('Adresa', 'gln-pharma-product'),
                'field' => __('Jedno pole', 'gln-pharma-product'),
            ],
            'default' => 'contact',
        ]);
        $this->add_control('field', [
            'label' => __('Pole', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'company_name' => __('Název společnosti', 'gln-pharma-product'),
                'building' => __('Budova', 'gln-pharma-product'),
                'street' => __('Ulice a číslo', 'gln-pharma-product'),
                'postal_code' => __('PSČ', 'gln-pharma-product'),
                'city' => __('Město', 'gln-pharma-product'),
                'country' => __('Země', 'gln-pharma-product'),
                'phone' => __('Telefon', 'gln-pharma-product'),
                'email_primary' => __('Hlavní e-mail', 'gln-pharma-product'),
                'email_secondary' => __('Další e-mail', 'gln-pharma-product'),
                'website_url' => __('URL', 'gln-pharma-product'),
            ],
            'condition' => ['format' => 'field'],
        ]);
        $this->add_control('link_field', [
            'label' => __('Telefon/e-mail jako odkaz', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default' => 'yes',
            'condition' => ['format' => 'field'],
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

        $format = isset($settings['format']) ? sanitize_key($settings['format']) : 'contact';
        $field = 'field' === $format && !empty($settings['field']) ? sanitize_key($settings['field']) : '';
        $linked = 'yes' === ($settings['link_field'] ?? '');

        echo glenmark_shared_content_render_live($key, $format, $field, $linked, 'gln-shared-info');
    }
}