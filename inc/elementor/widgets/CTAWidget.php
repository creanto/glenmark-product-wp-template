<?php

if (!defined('ABSPATH')) {
    exit;
}

class Webrev_CTA_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'wr-cta';
    }

    public function get_title()
    {
        return __('CTA', 'wr-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-call-to-action';
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
                'label' => __('Content', 'wr-pharma-product'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => __('Title', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Najděte produkt přesně pro sebe.', 'wr-pharma-product'),
            ]
        );

        $this->add_control(
            'text',
            [
                'label' => __('Text', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => __('Vyberte produkt, který bude odpovídat vašemu dnešnímu rytmu.', 'wr-pharma-product'),
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('Button text', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Prohlédnout sortiment', 'wr-pharma-product'),
            ]
        );

        $this->add_control(
            'button_url',
            [
                'label' => __('Button URL', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => __('https://example.com', 'wr-pharma-product'),
            ]
        );

        $this->add_control(
            'theme',
            [
                'label' => __('Theme', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'brand',
                'options' => [
                    'brand' => __('Brand', 'wr-pharma-product'),
                    'soft' => __('Soft', 'wr-pharma-product'),
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $title = !empty($settings['title']) ? esc_html($settings['title']) : '';
        $text = !empty($settings['text']) ? esc_html($settings['text']) : '';
        $button_text = !empty($settings['button_text']) ? esc_html($settings['button_text']) : __('Prohlédnout sortiment', 'wr-pharma-product');
        $button_url = !empty($settings['button_url']['url']) ? esc_url($settings['button_url']['url']) : '#';
        $theme = !empty($settings['theme']) ? $settings['theme'] : 'brand';

        echo '<div class="wr-cta wr-cta--' . esc_attr($theme) . '">';
        echo '<div class="wr-cta__content">';
        if ($title) {
            echo '<h2 class="wr-cta__title">' . $title . '</h2>';
        }
        if ($text) {
            echo '<p class="wr-cta__text">' . $text . '</p>';
        }
        echo '</div>';
        echo '<div class="wr-cta__actions"><a href="' . $button_url . '" class="wr-button wr-button--secondary">' . $button_text . '</a></div>';
        echo '</div>';
    }
}
