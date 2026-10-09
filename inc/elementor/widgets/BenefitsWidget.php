<?php

if (!defined('ABSPATH')) {
    exit;
}

class Glenmark_Benefits_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'gln-benefits';
    }

    public function get_title()
    {
        return __('Benefits', 'gln-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-info-box';
    }

    public function get_categories()
    {
        return ['glenmark'];
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Items', 'gln-pharma-product'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'icon',
            [
                'label' => __('Icon / index', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '01',
            ]
        );

        $repeater->add_control(
            'title',
            [
                'label' => __('Title', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Benefit title', 'gln-pharma-product'),
            ]
        );

        $repeater->add_control(
            'text',
            [
                'label' => __('Text', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => __('Describe the benefit.', 'gln-pharma-product'),
            ]
        );

        $this->add_control(
            'items',
            [
                'label' => __('Items', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    ['icon' => '01', 'title' => 'Ranní podpora', 'text' => 'Popis výhody pro uživatele.'],
                    ['icon' => '02', 'title' => 'Pro svaly a nervy', 'text' => 'Popis výhody pro uživatele.'],
                    ['icon' => '03', 'title' => 'Pro každý den', 'text' => 'Popis výhody pro uživatele.'],
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $items = $this->get_settings_for_display('items');

        if (empty($items)) {
            return;
        }

        echo '<div class="gln-grid gln-grid--benefits">';

        foreach ($items as $item) {
            $icon = !empty($item['icon']) ? esc_html($item['icon']) : '•';
            $title = !empty($item['title']) ? esc_html($item['title']) : '';
            $text = !empty($item['text']) ? esc_html($item['text']) : '';

            echo '<div class="gln-benefit">';
            echo '<div class="gln-benefit__icon">' . $icon . '</div>';
            if ($title) {
                echo '<h3 class="gln-benefit__title">' . $title . '</h3>';
            }
            if ($text) {
                echo '<p class="gln-benefit__text">' . $text . '</p>';
            }
            echo '</div>';
        }

        echo '</div>';
    }
}
