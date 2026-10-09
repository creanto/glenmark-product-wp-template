<?php

if (!defined('ABSPATH')) {
    exit;
}

class Glenmark_ProductGrid_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'gln-product-grid';
    }

    public function get_title()
    {
        return __('Product Grid', 'gln-pharma-product');
    }

    public function get_icon()
    {
        return 'eicon-posts-grid';
    }

    public function get_categories()
    {
        return ['glenmark'];
    }

    protected function register_controls()
    {
        $categories = get_terms([
            'taxonomy' => 'gln_product_category',
            'hide_empty' => false,
        ]);
        $category_options = [];

        if (!is_wp_error($categories)) {
            foreach ($categories as $category) {
                $category_options[(string) $category->term_id] = $category->name;
            }
        }

        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'gln-pharma-product'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'product_source',
            [
                'label' => __('Products to display', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'all',
                'options' => [
                    'all' => __('All products', 'gln-pharma-product'),
                    'categories' => __('Selected categories', 'gln-pharma-product'),
                ],
            ]
        );

        $this->add_control(
            'categories',
            [
                'label' => __('Product categories', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $category_options,
                'multiple' => true,
                'condition' => [
                    'product_source' => 'categories',
                ],
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Products to show', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 4,
                'min' => 1,
                'max' => 12,
            ]
        );

        $this->add_control(
            'show_excerpt',
            [
                'label' => __('Show excerpt', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_image',
            [
                'label' => __('Show image', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('Button text', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Zobrazit produkt', 'gln-pharma-product'),
            ]
        );

        $columns_options = [
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
            '6' => '6',
        ];

        $this->add_control(
            'columns',
            [
                'label' => __('Columns (desktop)', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '4',
                'options' => $columns_options,
            ]
        );

        $this->add_control(
            'columns_tablet',
            [
                'label' => __('Columns (tablet)', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '2',
                'options' => $columns_options,
            ]
        );

        $this->add_control(
            'columns_mobile',
            [
                'label' => __('Columns (mobile)', 'gln-pharma-product'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '1',
                'options' => $columns_options,
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $posts_per_page = (int) ($settings['posts_per_page'] ?? 4);
        $posts_per_page = max(1, $posts_per_page);
        $show_image = !empty($settings['show_image']);
        $columns = max(1, min(6, (int) ($settings['columns'] ?? 4)));
        $columns_tablet = max(1, min(6, (int) ($settings['columns_tablet'] ?? 2)));
        $columns_mobile = max(1, min(6, (int) ($settings['columns_mobile'] ?? 1)));
        $product_source = $settings['product_source'] ?? 'all';
        $category_ids = array_filter(array_map('absint', (array) ($settings['categories'] ?? [])));

        $query_args = [
            'post_type' => 'gln_product',
            'posts_per_page' => $posts_per_page,
            'post_status' => 'publish',
            'orderby' => 'menu_order title',
            'order' => 'ASC',
        ];

        if ('categories' === $product_source && $category_ids) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'gln_product_category',
                    'field' => 'term_id',
                    'terms' => $category_ids,
                ],
            ];
        }

        $query = new \WP_Query($query_args);

        if (!$query->have_posts()) {
            echo '<div class="gln-empty-state">' . esc_html__('No products available.', 'gln-pharma-product') . '</div>';
            return;
        }

        $grid_style = '--gln-grid-columns:' . esc_attr((string) $columns) . ';'
            . '--gln-grid-columns-tablet:' . esc_attr((string) $columns_tablet) . ';'
            . '--gln-grid-columns-mobile:' . esc_attr((string) $columns_mobile) . ';';

        echo '<div class="gln-grid gln-grid--products" style="' . $grid_style . '">';
        $modal_markup = '';

        $pharmacy_sources = glenmark_get_pharmacy_sources();

        while ($query->have_posts()) {
            $query->the_post();
            $product_id = get_the_ID();
            $packshot_id = get_field('product_packshot', $product_id) ?: get_post_thumbnail_id($product_id);
            $thumbnail_id = get_post_thumbnail_id($product_id) ?: $packshot_id;
            $product_name = get_field('product_name', $product_id) ?: get_the_title($product_id);
            $product_subtitle = get_field('product_subtitle', $product_id) ?: (get_field('product_claim', $product_id) ?: '');
            $product_short_description = get_field('product_short_description', $product_id) ?: '';
            $detail_url = get_permalink($product_id);
            $pharmacy_links = [];
            $info_chip_groups = array_filter([
                ['label' => 'Indikace', 'value' => get_field('product_indication', $product_id)],
                ['label' => 'Účinné látky', 'value' => get_field('product_active_ingredients', $product_id)],
                ['label' => 'Obsažené látky', 'value' => get_field('product_contained_ingredients', $product_id)],
            ], static function ($chip_group) {
                return !empty($chip_group['value']);
            });

            foreach ($pharmacy_sources as $source) {
                $url = (string) (get_field($source['field'], $product_id) ?: '');

                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $pharmacy_links[] = [
                        'label' => $source['label'],
                        'logo' => $source['logo'],
                        'url' => $url,
                    ];
                }
            }

            $modal_id = 'gln-product-buy-modal-' . $product_id;
            $info_modal_id = 'gln-product-info-modal-' . $product_id;

            echo '<article class="gln-card gln-card--product gln-product-carousel-card">';

            if ($show_image) {
                echo '<div class="gln-card__media gln-product-carousel-card__media">';
                if ($packshot_id) {
                    echo '<a href="' . esc_url($detail_url) . '">';
                    echo wp_get_attachment_image($packshot_id, 'medium', false, ['class' => 'gln-card__image gln-product-carousel-card__image']);
                    echo '</a>';
                }
                echo '</div>';
            }

            echo '<div class="gln-card__body gln-product-carousel-card__body">';
            echo '<h3 class="gln-card__title gln-product-carousel-card__title"><a href="' . esc_url($detail_url) . '">' . esc_html($product_name);
            if ($product_subtitle) {
                echo '<span class="gln-product-carousel-card__subtitle">' . esc_html($product_subtitle) . '</span>';
            }

            echo '</a></h3>';
            echo '<div class="gln-product-carousel-card__actions">';
            echo '<a class="gln-product-action-icon gln-product-action-icon--info gln-product-info-modal-trigger" href="#' . esc_attr($info_modal_id) . '" aria-label="' . esc_attr__('Detail produktu', 'gln-pharma-product') . '">';
            echo '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><line x1="12" y1="11" x2="12" y2="16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></line><circle cx="12" cy="8" r="1.2" fill="currentColor"></circle></svg>';
            echo '</a>';

            echo '<div class="gln-product-action-icon-group">';
            echo '<a class="gln-product-action-icon gln-product-action-icon--buy gln-product-buy-modal-trigger" href="#' . esc_attr($modal_id) . '" aria-label="' . esc_attr__('Koupit', 'gln-pharma-product') . '">';
            echo '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 6h2l2.2 10.2a2 2 0 0 0 2 1.6h6.8a2 2 0 0 0 1.9-1.4l1.8-6.4H8.4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path><circle cx="10.5" cy="20" r="1.4" fill="currentColor"></circle><circle cx="17" cy="20" r="1.4" fill="currentColor"></circle></svg>';
            echo '</a>';
            echo '<a class="gln-product-action-icon gln-product-action-icon--detail" href="' . esc_url($detail_url) . '" aria-label="' . esc_attr__('Přejít na detail produktu', 'gln-pharma-product') . '">';
            echo '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M7 12h10" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path><path d="M13 8l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
            echo '</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</article>';

            $modal_markup .= '<div id="' . esc_attr($modal_id) . '" class="gln-product-buy-modal mfp-hide">';
            $modal_markup .= '<div class="gln-product-buy-modal__dialog">';
            $modal_markup .= '<h2 class="gln-product-buy-modal__title">' . esc_html__('Kde koupit', 'gln-pharma-product') . '</h2>';
            $modal_markup .= '<p class="gln-product-buy-modal__product">' . esc_html($product_name) . '</p>';

            if ($pharmacy_links) {
                $modal_markup .= '<div class="gln-product-buy-modal__logos">';

                foreach ($pharmacy_links as $pharmacy_link) {
                    $modal_markup .= '<a class="gln-product-buy-modal__logo-link" href="' . esc_url($pharmacy_link['url']) . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr($pharmacy_link['label']) . '">';

                    if (!empty($pharmacy_link['logo'])) {
                        $modal_markup .= '<img class="gln-product-buy-modal__logo" src="' . esc_url($pharmacy_link['logo']) . '" alt="' . esc_attr($pharmacy_link['label']) . '">';
                    } else {
                        $modal_markup .= '<span class="gln-product-buy-modal__logo-fallback">' . esc_html($pharmacy_link['label']) . '</span>';
                    }

                    $modal_markup .= '</a>';
                }

                $modal_markup .= '</div>';
            } else {
                $modal_markup .= '<p class="gln-product-buy-modal__empty">' . esc_html__('Produkt momentálně není dostupný v žádném e-shopu.', 'gln-pharma-product') . '</p>';
            }

            $modal_markup .= '</div>';
            $modal_markup .= '</div>';

            $modal_markup .= '<div id="' . esc_attr($info_modal_id) . '" class="gln-product-info-modal mfp-hide">';
            $modal_markup .= '<div class="gln-product-info-modal__dialog">';
            $modal_markup .= '<h2 class="gln-product-info-modal__title">' . esc_html($product_name) . '</h2>';

            if ($product_subtitle) {
                $modal_markup .= '<p class="gln-product-info-modal__subtitle">' . esc_html($product_subtitle) . '</p>';
            }

            if ($thumbnail_id) {
                $modal_markup .= '<div class="gln-product-info-modal__media">';
                $modal_markup .= wp_get_attachment_image($thumbnail_id, 'large', false, ['class' => 'gln-product-info-modal__image']);
                $modal_markup .= '</div>';
            }

            if ($info_chip_groups) {
                $modal_markup .= '<div class="gln-product-info-modal__chips">';

                foreach ($info_chip_groups as $chip_group) {
                    $modal_markup .= '<div class="gln-product-info-modal__chip">';
                    $modal_markup .= '<span class="gln-product-info-modal__chip-label">' . esc_html($chip_group['label']) . '</span>';
                    $modal_markup .= '<span class="gln-product-info-modal__chip-value">' . esc_html($chip_group['value']) . '</span>';
                    $modal_markup .= '</div>';
                }

                $modal_markup .= '</div>';
            }

            if ($product_short_description) {
                $modal_markup .= '<div class="gln-product-info-modal__description">' . wp_kses_post($product_short_description) . '</div>';
            }

            $modal_markup .= '<a class="gln-product-info-modal__button" href="' . esc_url($detail_url) . '">' . esc_html($settings['button_text'] ?? __('Detail produktu', 'gln-pharma-product')) . '</a>';
            $modal_markup .= '</div>';
            $modal_markup .= '</div>';
        }

        wp_reset_postdata();

        echo '</div>';
        echo $modal_markup;
    }
}
