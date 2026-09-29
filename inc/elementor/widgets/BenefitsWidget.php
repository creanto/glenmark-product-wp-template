<?php

if (!defined('ABSPATH')) {
    exit;
}

class Webrev_Benefits_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'wr-benefits';
    }

    public function get_title()
    {
        return __('Benefits', 'wr-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-info-box';
    }

    public function get_categories()
    {
        return ['webrev'];
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Items', 'wr-pharma-product'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'icon',
            [
                'label' => __('Icon / index', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '01',
            ]
        );

        $repeater->add_control(
            'title',
            [
                'label' => __('Title', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Benefit title', 'wr-pharma-product'),
            ]
        );

        $repeater->add_control(
            'text',
            [
                'label' => __('Text', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => __('Describe the benefit.', 'wr-pharma-product'),
            ]
        );

        $this->add_control(
            'items',
            [
                'label' => __('Items', 'wr-pharma-product'),
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

        echo '<div class="wr-grid wr-grid--benefits">';

        foreach ($items as $item) {
            $icon = !empty($item['icon']) ? esc_html($item['icon']) : '•';
            $title = !empty($item['title']) ? esc_html($item['title']) : '';
            $text = !empty($item['text']) ? esc_html($item['text']) : '';

            echo '<div class="wr-benefit">';
            echo '<div class="wr-benefit__icon">' . $icon . '</div>';
            if ($title) {
                echo '<h3 class="wr-benefit__title">' . $title . '</h3>';
            }
            if ($text) {
                echo '<p class="wr-benefit__text">' . $text . '</p>';
            }
            echo '</div>';
        }

        echo '</div>';
    }
}
