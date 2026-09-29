<?php

if (!defined('ABSPATH')) {
    exit;
}

class Webrev_PharmacyGrid_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'wr-pharmacy-grid';
    }

    public function get_title()
    {
        return __('Pharmacies', 'wr-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-image-box';
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
            'posts_per_page',
            [
                'label' => __('Pharmacies to show', 'wr-pharma-product'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 8,
                'min' => 1,
                'max' => 24,
            ]
        );

        $this->add_control(
            'columns',
            [
                'label' => __('Columns (desktop)', 'wr-pharma-product'),
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
                'label' => __('Columns (mobile)', 'wr-pharma-product'),
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
        $posts_per_page = (int) ($settings['posts_per_page'] ?? 8);
        $posts_per_page = max(1, $posts_per_page);
        $columns = max(1, min(6, (int) ($settings['columns'] ?? 4)));
        $columns_mobile = max(1, min(6, (int) ($settings['columns_mobile'] ?? 1)));
        $query = new \WP_Query([
            'post_type' => 'wr_pharmacy',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        if (!$query->have_posts()) {
            echo '<div class="wr-empty-state">' . esc_html__('No pharmacies available.', 'wr-pharma-product') . '</div>';
            return;
        }

        echo '<div class="wr-grid wr-grid--pharmacies" style="--wr-pharmacy-columns:' . esc_attr((string) $columns) . ';--wr-pharmacy-columns-mobile:' . esc_attr((string) $columns_mobile) . ';">';

        while ($query->have_posts()) {
            $query->the_post();
            $pharmacy_id = get_the_ID();
            $logo_id = get_field('pharmacy_logo', $pharmacy_id) ?: get_post_thumbnail_id($pharmacy_id);
            $url = get_field('pharmacy_url', $pharmacy_id) ?: '';
            $title = get_the_title($pharmacy_id);

            echo '<article class="wr-pharmacy-card">';

            if ($logo_id) {
                echo '<div class="wr-pharmacy-card__logo-wrap">';
                echo wp_get_attachment_image($logo_id, 'medium', false, ['class' => 'wr-pharmacy-card__logo']);
                echo '</div>';
            } else {
                echo '<div class="wr-pharmacy-card__logo-wrap wr-pharmacy-card__logo-wrap--placeholder" aria-label="' . esc_attr($title) . '">';
                echo '<span class="wr-pharmacy-card__initials">' . esc_html(substr(preg_replace('/[^A-Za-z0-9]/', '', $title), 0, 2) ?: 'LM') . '</span>';
                echo '</div>';
            }

            if ($url) {
                echo '<div class="wr-pharmacy-card__action"><a class="wr-button wr-pharmacy-card__button" href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('E-shop', 'wr-pharma-product') . ' ' . esc_html($title) . '</a></div>';
            } else {
                echo '<div class="wr-pharmacy-card__action"><span class="wr-button wr-pharmacy-card__button wr-button--disabled">' . esc_html__('E-shop', 'wr-pharma-product') . ' ' . esc_html($title) . '</span></div>';
            }

            echo '</article>';
        }

        wp_reset_postdata();
        echo '</div>';
    }
}
