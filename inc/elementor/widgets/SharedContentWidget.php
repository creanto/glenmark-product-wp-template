<?php

if (!defined('ABSPATH')) {
    exit;
}

class Glenmark_Shared_Content_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'gln-shared-content';
    }

    public function get_title()
    {
        return __('Sdílený obsah Glenmark', 'gln-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-share';
    }

    public function get_categories()
    {
        return ['glenmark'];
    }

    public function get_keywords()
    {
        return ['glenmark', 'sdílený obsah', 'kontakt', 'adresa', 'legal'];
    }

    protected function register_controls()
    {
        $items = glenmark_shared_content_get_items();
        $item_options = ['' => __('Vyberte sdílený obsah', 'gln-pharma-product')];
        foreach ($items as $key => $item) {
            $item_options[$key] = (string) ($item['title'] ?? $key) . ' (' . $key . ')';
        }

        $this->start_controls_section('section_shared_content', [
            'label' => __('Sdílený obsah', 'gln-pharma-product'),
        ]);

        $this->add_control('item_key', [
            'label' => __('Položka', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT2,
            'options' => $item_options,
            'label_block' => true,
        ]);

        $this->add_control('format', [
            'label' => __('Zobrazení', 'gln-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'auto' => __('Automaticky podle typu', 'gln-pharma-product'),
                'contact' => __('Celý kontakt', 'gln-pharma-product'),
                'address' => __('Adresa', 'gln-pharma-product'),
                'content' => __('HTML obsah / dokument', 'gln-pharma-product'),
                'link' => __('Odkaz', 'gln-pharma-product'),
                'field' => __('Jedno pole', 'gln-pharma-product'),
            ],
            'default' => 'auto',
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
                'link_label' => __('Text odkazu', 'gln-pharma-product'),
                'website_url' => __('URL', 'gln-pharma-product'),
                'content_html' => __('HTML obsah', 'gln-pharma-product'),
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
        if (empty($settings['item_key'])) {
            return;
        }

        $format = isset($settings['format']) ? $settings['format'] : 'auto';
        $field = 'field' === $format && !empty($settings['field']) ? $settings['field'] : '';
        $linked = 'yes' === ($settings['link_field'] ?? '');

        echo glenmark_shared_content_render_key($settings['item_key'], $format, $field, $linked);
    }
}