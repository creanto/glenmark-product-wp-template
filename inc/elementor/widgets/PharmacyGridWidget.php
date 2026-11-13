<?php

if (!defined('ABSPATH')) {
    exit;
}

class Glenmark_PharmacyGrid_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'gln-pharmacy-grid';
    }

    public function get_title()
    {
        return __('Pharmacies', 'gln-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-image-box';
    }

    public function get_categories()
    {
        return ['glenmark'];
    }

    private function get_products()
    {
        return get_posts([
            'post_type' => 'gln_product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'menu_order title',
            'order' => 'ASC',
        ]);
    }

    protected function register_controls()
    {
        $products = $this->get_products();
        $product_options = [
            '' => __('Automatic (main product or first)', 'gln-pharma-product'),
        ];

        foreach ($products as $product) {
            $product_options[(string) $product->ID] = $product->post_title;
        }

        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'gln-pharma-product'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'product_id',
            [
                'label' => __('Product', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $product_options,
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Pharmacies to show', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 10,
                'min' => 1,
                'max' => 24,
            ]
        );

        $this->add_control(
            'columns',
            [
                'label' => __('Columns (desktop)', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '4',
                'options' => [
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
            ]
        );

        $this->add_control(
            'columns_mobile',
            [
                'label' => __('Columns (mobile)', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '1',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $posts_per_page = (int) ($settings['posts_per_page'] ?? 10);
        $posts_per_page = max(1, $posts_per_page);
        $columns = max(1, min(6, (int) ($settings['columns'] ?? 4)));
        $columns_mobile = max(1, min(6, (int) ($settings['columns_mobile'] ?? 1)));
        $products = $this->get_products();
        $product_id = absint($settings['product_id'] ?? 0);

        if ($product_id && ('gln_product' !== get_post_type($product_id) || 'publish' !== get_post_status($product_id))) {
            $product_id = 0;
        }

        if (!$product_id && !empty($products)) {
            foreach ($products as $product) {
                if (get_field('product_main_product', $product->ID)) {
                    $product_id = (int) $product->ID;
                    break;
                }
            }

            if (!$product_id) {
                $product_id = (int) $products[0]->ID;
            }
        }

        $pharmacies = [];
        $product_pharmacies = $product_id
            ? glenmark_get_product_primary_variant_pharmacies($product_id)
            : [];

        foreach (glenmark_get_pharmacy_sources() as $pharmacy) {
            $url = $product_id
                ? (string) ($product_pharmacies[$pharmacy['variant_field']] ?? '')
                : $pharmacy['url'];
            $url = esc_url_raw($url);

            if ('' === $url) {
                continue;
            }

            $pharmacy['url'] = $url;
            $pharmacies[] = $pharmacy;
        }

        $pharmacies = array_slice($pharmacies, 0, $posts_per_page);

        if (empty($pharmacies)) {
            echo '<div class="gln-empty-state">' . esc_html__('No pharmacies available.', 'gln-pharma-product') . '</div>';
            return;
        }

        echo '<div class="gln-grid gln-grid--pharmacies" style="--gln-pharmacy-columns:' . esc_attr((string) $columns) . ';--gln-pharmacy-columns-mobile:' . esc_attr((string) $columns_mobile) . ';">';

        foreach ($pharmacies as $pharmacy) {
            $url = $pharmacy['url'];
            $title = $pharmacy['label'];

            echo '<article class="gln-pharmacy-card">';

            if ($pharmacy['logo']) {
                echo '<div class="gln-pharmacy-card__logo-wrap">';
                echo '<img class="gln-pharmacy-card__logo" src="' . esc_url($pharmacy['logo']) . '" alt="' . esc_attr($title) . '" loading="lazy">';
                echo '</div>';
            } else {
                echo '<div class="gln-pharmacy-card__logo-wrap gln-pharmacy-card__logo-wrap--placeholder" aria-label="' . esc_attr($title) . '">';
                echo '<span class="gln-pharmacy-card__initials">' . esc_html(substr(preg_replace('/[^A-Za-z0-9]/', '', $title), 0, 2) ?: 'LM') . '</span>';
                echo '</div>';
            }

            echo '<div class="gln-pharmacy-card__action"><a class="gln-button gln-pharmacy-card__button" href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('E-shop', 'gln-pharma-product') . ' ' . esc_html($title) . '</a></div>';

            echo '</article>';
        }

        echo '</div>';
    }
}
