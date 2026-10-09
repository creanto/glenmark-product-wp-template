<?php

if (!defined('ABSPATH')) {
    exit;
}

class Webrev_Shared_Content_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'wr-shared-content';
    }

    public function get_title()
    {
        return __('Sdílený obsah Glenmark', 'wr-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-share';
    }

    public function get_categories()
    {
        return ['webrev'];
    }

    public function get_keywords()
    {
        return ['glenmark', 'sdílený obsah', 'kontakt', 'adresa', 'legal'];
    }

    protected function register_controls()
    {
        $items = webrev_shared_content_get_items();
        $item_options = ['' => __('Vyberte sdílený obsah', 'wr-pharma-product')];
        foreach ($items as $key => $item) {
            $item_options[$key] = (string) ($item['title'] ?? $key) . ' (' . $key . ')';
        }

        $this->start_controls_section('section_shared_content', [
            'label' => __('Sdílený obsah', 'wr-pharma-product'),
        ]);

        $this->add_control('item_key', [
            'label' => __('Položka', 'wr-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT2,
            'options' => $item_options,
            'label_block' => true,
        ]);

        $this->add_control('format', [
            'label' => __('Zobrazení', 'wr-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'auto' => __('Automaticky podle typu', 'wr-pharma-product'),
                'contact' => __('Celý kontakt', 'wr-pharma-product'),
                'address' => __('Adresa', 'wr-pharma-product'),
                'content' => __('HTML obsah / dokument', 'wr-pharma-product'),
                'link' => __('Odkaz', 'wr-pharma-product'),
                'field' => __('Jedno pole', 'wr-pharma-product'),
            ],
            'default' => 'auto',
        ]);

        $this->add_control('field', [
            'label' => __('Pole', 'wr-pharma-product'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'company_name' => __('Název společnosti', 'wr-pharma-product'),
                'building' => __('Budova', 'wr-pharma-product'),
                'street' => __('Ulice a číslo', 'wr-pharma-product'),
                'postal_code' => __('PSČ', 'wr-pharma-product'),
                'city' => __('Město', 'wr-pharma-product'),
                'country' => __('Země', 'wr-pharma-product'),
                'phone' => __('Telefon', 'wr-pharma-product'),
                'email_primary' => __('Hlavní e-mail', 'wr-pharma-product'),
                'email_secondary' => __('Další e-mail', 'wr-pharma-product'),
                'link_label' => __('Text odkazu', 'wr-pharma-product'),
                'website_url' => __('URL', 'wr-pharma-product'),
                'content_html' => __('HTML obsah', 'wr-pharma-product'),
            ],
            'condition' => ['format' => 'field'],
        ]);

        $this->add_control('link_field', [
            'label' => __('Telefon/e-mail jako odkaz', 'wr-pharma-product'),
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

        echo webrev_shared_content_render_key($settings['item_key'], $format, $field, $linked);
    }
}