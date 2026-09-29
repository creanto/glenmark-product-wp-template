<?php

if (!defined('ABSPATH')) {
    exit;
}

class Webrev_ProductBox_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'wr-product-box';
    }

    public function get_title()
    {
        return __('Product Box', 'wr-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-product-info';
    }

    public function get_categories()
    {
        return ['webrev'];
    }

    protected function register_controls()
    {
        $products = get_posts([
            'post_type' => 'wr_product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);
        $product_options = [
            '' => __('Select a product', 'wr-pharma-product'),
        ];

        foreach ($products as $product) {
            $product_options[$product->ID] = $product->post_title;
        }

        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'wr-pharma-product'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'product_id',
            [
                'label' => __('Product', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $product_options,
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('Button text', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Více o produktu', 'wr-pharma-product'),
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $product_id = isset($settings['product_id']) ? (int) $settings['product_id'] : 0;

        if ($product_id <= 0 || 'wr_product' !== get_post_type($product_id)) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<article class="wr-product-box wr-product-box--empty">';
                echo '<div class="wr-product-box__empty">';
                echo '<h2 class="wr-product-box__title">' . esc_html__('Vyberte produkt', 'wr-pharma-product') . '</h2>';
                echo '<p>' . esc_html__('Po kliknutí na tento box vyberte produkt v levém panelu Elementor.', 'wr-pharma-product') . '</p>';
                echo '</div>';
                echo '</article>';
            }

            return;
        }

        $product_name = get_field('product_name', $product_id) ?: get_the_title($product_id);
        $product_subtitle = get_field('product_subtitle', $product_id) ?: get_field('product_claim', $product_id);
        $product_description = get_field('product_short_description', $product_id);
        $product_image_id = get_post_thumbnail_id($product_id) ?: get_field('product_packshot', $product_id);
        $button_text = !empty($settings['button_text']) ? $settings['button_text'] : __('Více o produktu', 'wr-pharma-product');

        echo '<article class="wr-product-box">';
        echo '<div class="wr-product-box__content">';
        echo '<h2 class="wr-product-box__title">' . esc_html($product_name) . '</h2>';

        if ($product_subtitle) {
            echo '<p class="wr-product-box__subtitle">' . esc_html($product_subtitle) . '</p>';
        }

        if ($product_description) {
            echo '<div class="wr-product-box__description">' . wp_kses_post(wpautop($product_description)) . '</div>';
        }

        echo '<a class="wr-product-box__button" href="' . esc_url(get_permalink($product_id)) . '">' . esc_html($button_text) . '</a>';
        echo '</div>';

        if ($product_image_id) {
            echo '<div class="wr-product-box__media">';
            echo wp_get_attachment_image($product_image_id, 'large', false, ['class' => 'wr-product-box__image']);
            echo '</div>';
        }

        echo '</article>';
    }
}